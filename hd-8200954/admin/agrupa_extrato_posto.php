<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
$admin_privilegios="financeiro";
include "autentica_admin.php";

$extrato = (trim ($_POST['extrato']));
$posto = (trim ($_POST['posto']));

$somente_consulta= trim ($_GET['somente_consulta']);
if(strlen($somente_consulta)==0){
	$somente_consulta= trim ($_POST['somente_consulta']);
}

if (strlen($_POST["btnacao"]) > 0) $btnacao = trim(strtolower($_POST["btnacao"]));
if (strlen($_GET["btnacao"])  > 0) $btnacao = trim(strtolower($_GET["btnacao"]));

if (strlen($_POST["codigo_posto"]) > 0) $codigo_posto = $_POST["codigo_posto"];
if (strlen($_GET["codigo_posto"])  > 0) $codigo_posto = $_GET["codigo_posto"];

$nome   = $_POST['nome'];
if (strlen($_GET['nome']) > 0) $nome = $_GET['nome'];

$msg_erro = "";

$layout_menu = "financeiro";
$title = "Consulta e Manuten��o de Extratos do Posto";

$excluir= trim ($_GET['excluir']);
if ($excluir=="ok"){
	$extrato = $_GET["extrato"];
	$codigo = $_GET["codigo"];
	
	$sql= "delete from tbl_extrato_agrupado where extrato = $extrato and codigo = '$codigo'";
	$res = pg_query ($con,$sql);

	$sql="select agrupado from tbl_extrato_agrupado where codigo='$codigo'";
	$res = pg_query ($con,$sql);

	$qtd=substr($codigo,6,2);
	$fim=substr($codigo,8);
	$qtd=$qtd-1;
	if(strlen($qtd)==1){
		$qtd = '0'.$qtd;
	}

	for ($i = 0 ; $i < pg_num_rows($res) ; $i++){
		$agrupado = trim(pg_fetch_result($res,$i,agrupado));
		if ($i==0){
			$sql2 = "SELECT  to_char(tbl_extrato.data_geracao, 'DDMMYY') AS data_geracao
						FROM    tbl_extrato
						JOIN tbl_extrato_agrupado using (extrato)
						WHERE   tbl_extrato_agrupado.agrupado = $agrupado";
			$res2 = pg_query ($con,$sql2);
			$data = trim(pg_fetch_result($res2,0,data_geracao));
		}
		$novo_codigo = $data.$qtd.$fim;
		$sqlx = "update tbl_extrato_agrupado set codigo = '$novo_codigo' where agrupado = $agrupado";
		$resx = pg_query ($con,$sqlx);
	}
}

$btn_acao     = $_POST["btn_acao"];

if ($btn_acao=="gravar"){
	$soma = 0 ;
	$sql = "SELECT  tbl_extrato.extrato                                                ,
					to_char(tbl_extrato.data_geracao, 'DDMMYY') AS data_geracao            
			FROM    tbl_extrato
			WHERE   tbl_extrato.posto = '$posto'
			AND     tbl_extrato.fabrica = '$login_fabrica'
			AND     tbl_extrato.data_geracao >= '2005-03-30'
			ORDER   BY  to_char(tbl_extrato.data_geracao, 'YY-MM-DD') DESC limit 24";
	$res = pg_query ($con,$sql);
	for ($x = 0 ; $x < pg_num_rows($res) ; $x++){
		$extrato = trim(pg_fetch_result($res,$x,extrato));
		
		$selecionado     = $_POST["$extrato"];
		
		if(strlen($data)==0 and $selecionado == true){
			$data = trim(pg_fetch_result($res,$x,data_geracao));
		}

		if ($selecionado == true){
		$soma = $soma+1;
		}
	}		
	$qtd=$soma;

	if(strlen($qtd)==1){
		$qtd = '0'.$qtd;
	}

	for ($x = 0 ; $x < pg_num_rows($res) ; $x++){
		$extrato = trim(pg_fetch_result($res,$x,extrato));

		$selecionado     = $_POST["$extrato"];

		$codigo = $data.$qtd.$codigo_posto;

		if ($selecionado == true){
			$sqlx .= "insert into tbl_extrato_agrupado (extrato,codigo) values ($extrato,'$codigo'); ";

		}
	}

	$res2 = pg_query ($con,$sqlx);

	$sql3 = "SELECT DISTINCT to_char(data_geracao,'MM/YYYY') as data_geracao,
					to_char(data_conferencia,'DD/MM/YYYY') as data_conferencia
			FROM tbl_extrato
			JOIN tbl_extrato_conferencia USING(extrato)
			JOIN tbl_extrato_agrupado using(extrato)
			WHERE tbl_extrato_agrupado.codigo = '$codigo'
			AND   cancelada IS NOT TRUE
			ORDER BY data_conferencia";
	$res3 = pg_query ($con,$sql3);
	for ($x3 = 0 ; $x3 < pg_num_rows($res3) ; $x3++){
		$data_geracao = trim(pg_fetch_result($res3,$x3,data_geracao));
		$data_conferencia = trim(pg_fetch_result($res3,$x3,data_conferencia));

		$total_data .= ($x3 ==0) ? $data_geracao : ",".$data_geracao;
	}	

	

	$aux_descricao = "Extrato Conferido";
	$aux_mensagem  = "Posto Autorizado,<br><br> 
						Seu lote referente(s) ao(s) extrato(s)  \"$total_data\" foi conferido em $data_conferencia, no campo descri��o dos servi�os  colocar somente o seguinte c�digo: $codigo.
						<br/>
						Em casos em que ocorram diverg�ncias ser�o apontadas em cada extrato, o posto ter� como regularizar em 30 dias, Caso n�o sejam corrigidas as diverg�ncias as pe�as enviadas em garantia ser�o debitadas.
						<br/>
						Qualquer d�vida, enviar e-mail para: <u>auditoria.at@britania.com.br</u>
						<br/><br/>
						Atenciosamente
						<br/>
						Britania & Philco
						<br/>
						Dpto de Contas a Pagar - AT";
	$aux_tipo      = "Comunicado";
	$aux_obrigatorio_os_produto = "'f'";
	$aux_obrigatorio_site = "'t'";
	$aux_ativo = "'t'";
	$remetente_email = "auditoria.at@britania.com.br";


	$sql = "INSERT INTO tbl_comunicado (
		descricao              ,
		mensagem               ,
		tipo                   ,
		fabrica                ,
		obrigatorio_site       ,
		posto                  ,
		ativo                  
		) VALUES (
		'$aux_descricao'            ,
		'$aux_mensagem'             ,
		'$aux_tipo'                 ,
		$login_fabrica              ,
		$aux_obrigatorio_site       ,
		$posto                      ,
		$aux_ativo                  
	);";
	$res = @pg_query ($con,$sql);
}

include "cabecalho.php";
?>
<style>
.Tabela{
	border:1px solid #596D9B;
	background-color:#596D9B;
	}

.Titulo {
	text-align: center;
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-weight: bold;
	color:#ffffff;
	border: 1px solid;	
	background-color: #596D9B;
}
.Conteudo {
	font-family: Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: normal;
}
</style>
<? include "javascript_pesquisas.php"; ?>
<script language="javascript" src="js/cal2.js"></script>
<script language="javascript" src="js/cal_conf2.js"></script>
<script language="javascript" src="js/assist.js"></script>

<p>
<center>

<table class='Tabela' border='0' width='500' cellspacing='0'  cellpadding='0' bgcolor='#596D9B' align='center'>
	
	<tr >
		<td class="Titulo" >Consulta de Extrato de Postos</td>
	</tr>
	<tr>
		<td bgcolor='#F3F8FE'>
			<TABLE width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
				<FORM METHOD='GET' NAME='frm_extrato' ACTION="<?=$PHP_SELF?>">
					<tr><td colspan='2' bgcolor="#D9E2EF">&nbsp;</td></tr>
					<tr class="Conteudo" bgcolor="#D9E2EF" align='left'>
						<td align='right'>Posto&nbsp;</td>
						<td><input type="text" name="codigo_posto" size="8" value="<? echo $codigo_posto ?>" class="frm">
							<img border="0" src="imagens_admin/btn_lupa.gif" style="cursor: hand;" align="absmiddle" alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript: fnc_pesquisa_posto (document.frm_extrato.codigo_posto, document.frm_extrato.nome, 'codigo')"></td>
					</tr>
					<tr class="Conteudo" bgcolor="#D9E2EF" align='left'>
						<td align='right'>Nome do Posto&nbsp;</td>
						<td>
							<input type="text" name="nome" size="30" value="<?echo $nome?>" class="frm">
							<img border="0" src="imagens_admin/btn_lupa.gif" style="cursor: hand;" align="absmiddle" alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript: fnc_pesquisa_posto (document.frm_extrato.codigo_posto, document.frm_extrato.nome, 'nome')">
						</td>
					</tr>
					<tr><td colspan='2' bgcolor="#D9E2EF">&nbsp;
					<?
						if(strlen($somente_consulta)> 0){
							echo "<INPUT TYPE='hidden' name='somente_consulta'value='sim' >";
						}
					?></td></tr>

					<tr><td colspan='2' bgcolor="#D9E2EF" align='center'><INPUT TYPE="submit" name='btnacao'value="Pesquisar" ></td></tr>
				</form>
			</TABLE>
		</td>
	</tr>
	<tr><td bgcolor='#D9E2EF'>
<?
if (strlen ($codigo_posto) > 0 ) {

	echo "&nbsp;</td></tr>";
	echo "<tr><td bgcolor='#D9E2EF'>";

	$sql = "SELECT tbl_posto.posto FROM tbl_posto
			JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto =  tbl_posto.posto
			WHERE tbl_posto_fabrica.codigo_posto = '$codigo_posto' 
			AND fabrica = $login_fabrica";
	$res = pg_query ($con,$sql);
	if (pg_num_rows($res)>0){
		$posto = trim(pg_fetch_result($res,0,posto));
	}
	if (strlen($posto)>0){
		$sql = "SELECT  DISTINCT tbl_extrato.extrato                                                ,
						date_trunc('day',tbl_extrato.data_geracao)      AS data_extrato    ,
						to_char(tbl_extrato.data_geracao, 'DD/MM/YY') AS data            ,
						to_char(tbl_extrato.data_geracao, 'YY-MM-DD') AS periodo,
						tbl_extrato_agrupado.codigo as codigo
				FROM    tbl_extrato
				JOIN    tbl_extrato_conferencia USING(extrato)
				left join tbl_extrato_agrupado using(extrato)
				WHERE   tbl_extrato.posto = '$posto'
				AND     tbl_extrato.fabrica = '$login_fabrica'
				AND     tbl_extrato.data_geracao >= '2005-03-30'
				AND     tbl_extrato_conferencia.cancelada IS NOT TRUE
				ORDER   BY  to_char(tbl_extrato.data_geracao, 'YY-MM-DD') DESC limit 24";
		$res = pg_query ($con,$sql);
	
		if (pg_num_rows($res) > 0) {
			echo "<form name='frm_gravar' method='post' action='$PHP_SELF'>";
			echo "<input type='hidden' name='posto' value='$posto'><table width='500' cellspacing='2'  cellpadding='1' bgcolor='#EAECF2' align='center'><tr class='Titulo'><td></td><td>Data</td><td>Agrupado</td><td>Excluir</td></tr>";

			for ($x = 0 ; $x < pg_num_rows($res) ; $x++){
				$aux_extrato = trim(pg_fetch_result($res,$x,extrato));
				$aux_data    = trim(pg_fetch_result($res,$x,data));
				$aux_extr    = trim(pg_fetch_result($res,$x,data_extrato));
				$aux_peri    = trim(pg_fetch_result($res,$x,periodo));
				$codigo = trim(pg_fetch_result($res,$x,codigo));

				$cor = ($x % 2 == 0) ? '#F7F5F0' : '#F1F4FA';

				echo "<tr align='center' bgcolor='$cor'><td><INPUT TYPE='checkbox' NAME='$aux_extrato' ";
				if(strlen($codigo)>0){
					echo "disabled";
				}
				echo "></td><td>$aux_data</td>";
				if(strlen($codigo)>0){
					echo "<td>$codigo</td><td><a href='$PHP_SELF?codigo_posto=$codigo_posto&nome=$nome&excluir=ok&extrato=$aux_extrato&codigo=$codigo'>excluir</a></td></tr>";
				}else{
					echo "<td>&nbsp;</td><td>&nbsp;</td></tr>";
				}
			
			}
			echo "<input type='hidden' name='btn_acao' value=''>";
			echo "<INPUT TYPE='hidden' name='somente_consulta' value='sim' >";
			echo "<INPUT TYPE='hidden' name='codigo_posto' value='$codigo_posto' >";
			echo "<INPUT TYPE='hidden' name='nome' value='$nome' >";
			echo "</form>";
		}
?>
				</table>
			</td>
		</tr>
		<tr><td bgcolor='#D9E2EF'><center><img src='imagens/btn_gravar.gif' onclick="javascript: document.frm_gravar.btn_acao.value='gravar'; document.frm_gravar.submit()"></center></td></tr>
<?
	}
}
?>
</table>
<p><p>

<? include "rodape.php"; ?>
