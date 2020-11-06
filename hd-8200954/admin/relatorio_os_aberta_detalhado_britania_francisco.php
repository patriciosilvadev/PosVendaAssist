<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
$admin_privilegios="auditoria";

$gera_automatico = trim($_GET["gera_automatico"]);

if ($gera_automatico != 'automatico'){
	include "autentica_admin.php";
}
#include "gera_relatorio_pararelo_include.php";

include 'funcoes.php';

$layout_menu = "auditoria";
$title       = "RELAT�RIO DE OSs EM ABERTO";
include "cabecalho.php";
include "javascript_pesquisas.php";

?>

<script language="JavaScript" src="js/cal2.js"></script>
<script language="JavaScript" src="js/cal_conf2.js"></script>

<script language="JavaScript">
function fnc_pesquisa_posto2 (campo, campo2, tipo) {
	if (tipo == "codigo" ) {
		var xcampo = campo;
	}

	if (tipo == "nome" ) {
		var xcampo = campo2;
	}

	if (xcampo.value != "") {
		var url = "";
		url = "posto_pesquisa_2.php?campo=" + xcampo.value + "&tipo=" + tipo + "&proximo=";
		janela = window.open(url, "janela", "toolbar=no, location=no, status=yes, scrollbars=yes, directories=no, width=600, height=400, top=18, left=0");
		janela.codigo  = campo;
		janela.nome    = campo2;
		janela.focus();
	}
	else
		alert('Preencha toda ou parte da informa��o para fazer a pesquisa!');
}

function fnc_pesquisa_produto (campo, campo2, tipo) {
	if (tipo == "referencia" ) {
		var xcampo = campo;
	}

	if (tipo == "descricao" ) {
		var xcampo = campo2;
	}


	if (xcampo.value != "") {
		var url = "";
		url = "produto_pesquisa_2.php?campo=" + xcampo.value + "&tipo=" + tipo ;
		janela = window.open(url, "janela", "toolbar=no, location=no, status=yes, scrollbars=yes, directories=no, width=500, height=400, top=18, left=0");
		janela.referencia	= campo;
		janela.descricao	= campo2;
		janela.focus();
	}
	else
		alert('Preencha toda ou parte da informa��o para fazer a pesquisa!');
}



</script>

<style type="text/css">
.titulo_tabela{
	background-color:#596d9b;
	font: bold 14px "Arial";
	color:#FFFFFF;
	text-align:center;
}
.titulo_coluna{
	background-color:#596d9b;
	font: bold 11px "Arial";
	color:#FFFFFF;
	text-align:center;
}
.msg_erro{
	background-color:#FF0000;
	font: bold 14px "Arial";
	color:#FFFFFF;
	text-align:center;
}
.formulario{
	background-color:#D9E2EF;
	font:11px Arial;
	text-align:left;
}
table.tabela tr td{
	font-family: verdana;
	font-size: 11px;
	border-collapse: collapse;
	border:1px solid #596d9b;
}
.texto_avulso{
	font: 14px Arial; color: rgb(89, 109, 155);
	background-color: #d9e2ef;
	text-align: center;
	width:700px;
	margin: 0 auto;
	border-collapse: collapse;
	border:1px solid #596d9b;
}
.espaco {padding-left:140px;}
</style>

<?

if (strlen($_POST["btn_acao"]) > 0 ) $btn_acao = trim($_POST["btn_acao"]);
if (strlen($_GET["btn_acao"]) > 0 )  $btn_acao = trim($_GET["btn_acao"]);

if(strlen($btn_acao) > 0){
	if (strlen($_POST["posto_codigo"]) > 0 ) $posto_codigo = trim($_POST["posto_codigo"]);
	if (strlen($_GET["posto_codigo"]) > 0 )  $posto_codigo = trim($_GET["posto_codigo"]);

	if (strlen($_POST["posto_nome"]) > 0 ) $posto_nome = trim($_POST["posto_nome"]);
	if (strlen($_GET["posto_nome"]) > 0 )  $posto_nome = trim($_GET["posto_nome"]);

	if (strlen($_POST["linha"]) > 0 ) $linha = trim($_POST["linha"]);
	if (strlen($_GET["linha"]) > 0 )  $linha = trim($_GET["linha"]);

	if (strlen($_POST["qtde_dias"]) > 0 ) $qtde_dias = trim($_POST["qtde_dias"]);
        if (strlen($_GET["qtde_dias"]) > 0 )  $qtde_dias = trim($_GET["qtde_dias"]);

	if(strlen($qtde_dias) == 0) {
		$msg_erro = "� obrigat�rio o preenchimento da quantidade de dias";
	}

	$intervencao = $_GET['intervencao'];
	$excluidas   = $_GET['excluidas'];
	if ( empty($excluidas) ) {
		$excluidas = $_POST['excluidas'];
	}

	if ( empty($intervencao) ) {
		$intervencao = $_POST['intervencao'];
	}

	if (strlen(trim($_POST["cancelada_90_dias"])) > 0) $cancelada_90_dias = trim($_POST["cancelada_90_dias"]);
	if (strlen(trim($_GET["cancelada_90_dias"])) > 0)  $cancelada_90_dias = trim($_GET["cancelada_90_dias"]);

	if (strlen(trim($_POST["cancelada_45_dias"])) > 0) $cancelada_45_dias = trim($_POST["cancelada_45_dias"]);
	if (strlen(trim($_GET["cancelada_45_dias"])) > 0)  $cancelada_45_dias = trim($_GET["cancelada_45_dias"]);
}
?>

<?
if (strlen($btn_acao) > 0 && strlen($msg_erro) == 0) {
	#include "gera_relatorio_pararelo.php";
}

if ($gera_automatico != 'automatico' and strlen($msg_erro)==0){
	#include "gera_relatorio_pararelo_verifica.php";
}
?>

<?if (strlen($msg_erro) > 0) {?>
<table width='700' align='center' border='0' cellspacing='1' cellpadding='0'>
	<tr>
		<td class="msg_erro"><?echo $msg_erro?></td>
	</tr>
</table>
<?}?>

<form name='frm_os_posto' action='<? echo $PHP_SELF ?>' method="POST">
<input type="hidden" name='btn_acao' value="">

<table width='700' class='formulario' border='0' cellpadding='0' cellspacing='1' align='center'>
	<tr>
		<td class='titulo_tabela' colspan="2">Par�metros de Pesquisa</td>
	</tr>
	<tr>
		<td align='left' colspan="2" class="espaco">
			Linha<br />
			<?
			$sql = "SELECT  *
					FROM    tbl_linha
					WHERE   tbl_linha.fabrica = $login_fabrica
					ORDER BY tbl_linha.nome;";
			$res = pg_exec ($con,$sql);
			if (pg_numrows($res) > 0) {
				echo "<select name='linha' class='frm'>\n";
				echo "<option value=''>ESCOLHA</option>\n";
				for ($x = 0 ; $x < pg_numrows($res) ; $x++){
					$aux_linha = trim(pg_result($res,$x,linha));
					$aux_nome  = trim(pg_result($res,$x,nome));
					echo "<option value='$aux_linha'";
					if ($linha == $aux_linha){
						echo " SELECTED ";
						$mostraMsgLinha = "<br> da LINHA $aux_nome";
					}
					echo ">$aux_nome</option>\n";
				}
				echo "</select>\n&nbsp;";
			}
		?>
		</td>
	</tr>
	<tr>
		<td style="width:150px;" class="espaco">
			Cod. Posto<br />
			<input class="frm" type="text" name="posto_codigo" size="13" value="<? echo $posto_codigo ?>" <? if ($login_fabrica == 5) { ?> onblur="fnc_pesquisa_posto2 (document.frm_os_posto.posto_codigo,document.frm_os_posto.posto_nome,'codigo')" <? } ?>>&nbsp;<img src='imagens/lupa.png' border='0' align='absmiddle' style="cursor:pointer" onclick="javascript: fnc_pesquisa_posto2 (document.frm_os_posto.posto_codigo,document.frm_os_posto.posto_nome,'codigo')">
		</td>
		<td>
			Nome do Posto<br />
			<input class="frm" type="text" name="posto_nome" size="30" value="<? echo $posto_nome ?>" <? if ($login_fabrica == 5) { ?> onblur="fnc_pesquisa_posto2 (document.frm_os_posto.posto_codigo,document.frm_os_posto.posto_nome,'nome')" <? } ?>>&nbsp;<img src='imagens/lupa.png' style="cursor:pointer" border='0' align='absmiddle' onclick="javascript: fnc_pesquisa_posto2 (document.frm_os_posto.posto_codigo,document.frm_os_posto.posto_nome,'nome')" style="cursor:pointer;">
		</td>

	</tr>
	<tr>
		<td align='left' class="espaco">
			Dias em Aberto<br />
			<input class="frm" type="text" name="qtde_dias" size="13" maxlength="4" value="<? echo $qtde_dias ?>">
		</td>
	</tr>
	<tr>
		<td colspan='2' class="espaco">
			<fieldset style="width:300px;">
				<legend>Situa��o das Pe�as</legend>
				<table>
					<tr>
						<td>
						<input type='radio' name='situacao' value='c'<?if($situacao=='c')echo "checked";?>> Com Pe�a
						</td>
						<td>
						<input type='radio' name='situacao' value='s'<?if($situacao=='s')echo "checked";?>> Sem Pe�a
						</td>
						<td>
						<input type='radio' name='situacao' value='a'<?if($situacao=='a')echo "checked";?>> Ambos
						</td>
					</tr>
				</table>
				</fieldset>
				<fieldset style="width:300px;">
					<legend>Situa��o dos Consertos</legend>
					<table>
					<tr>
						<td>
							<INPUT TYPE="radio" NAME="conserto" value="t"<?if($conserto=='t')echo "checked";?>>Todas
						</td>
						<td>
							<INPUT TYPE="radio" NAME="conserto" value="c"<?if($conserto=='c')echo "checked";?>>Consertadas
						</td>
						<td>
							<INPUT TYPE="radio" NAME="conserto" value="n"<?if($conserto=='n')echo "checked";?>>N�o Consertadas
						</td>
					</tr>
				</table>
				</fieldset>
				<input type='checkbox' name='intervencao' value='t' <?if($intervencao=='t')echo "checked";?>> OSs que n�o est�o em interven��o<br />
				<input type='checkbox' name='excluidas' value='t' <?if($excluidas=='t')echo "checked";?>> Desconsiderar OSs exclu�das <br />
				<input type='checkbox' name='cancelada_90_dias' value='t' <? if ($cancelada_90_dias == 't') echo "checked";?> /> Desconsiderar OSs Canceladas (OS aberta a mais 90 dias - Cancelada)<br />
				<input type='checkbox' name='cancelada_45_dias' value='t' <?if ($cancelada_45_dias == 't') echo "checked";?>> Desconsiderar OSs Canceladas (OS aberta a mais 45 dias - Cancelada)

		</td>
	</tr>
	<tr>
		<td colspan="2" align="center" style="padding:10px 0 10px;">
			<input type="button" value="Pesquisar" style="cursor:pointer" onclick="javascript: if (document.frm_os_posto.btn_acao.value == '' ) { document.frm_os_posto.btn_acao.value='continuar' ; document.frm_os_posto.submit() } else { alert ('Aguarde submiss�o') }" />
		</td>
	</tr>
</table>

</form>

<br>

<?
if (strlen($btn_acao) > 0 AND strlen($msg_erro) == 0){

	if (strlen($posto_codigo) > 0){
		$sqlPosto =	"SELECT posto
					FROM tbl_posto_fabrica
					WHERE codigo_posto = '$posto_codigo'
					AND fabrica = $login_fabrica";
		$res = pg_exec($con,$sqlPosto);
		if (pg_numrows($res) == 1){
			$posto = pg_result($res,0,0);
			$sql_posto = " AND   tbl_os.posto = $posto ";
		}
	}

	if (strlen($situacao) > 0) {
		if($situacao=='s') $cond_sem_peca = " AND temp_os_aberta.os_produto IS NULL ";
		if($situacao=='c') $cond_sem_peca = " AND temp_os_aberta.os_produto IS NOT NULL ";
		if($situacao=='a') $cond_sem_peca = " AND (temp_os_aberta.os_produto IS NULL OR temp_os_aberta.os_produto IS NOT NULL) ";
	}
	else {
		$cond_sem_peca = "";
	}

	if (strlen($conserto) > 0) {
		if ($conserto=='t') $cond_data_conserto = "AND 1=1 ";
		if ($conserto=='c') $cond_data_conserto = "AND temp_os_aberta.data_conserto is not null ";
		if ($conserto=='n') $cond_data_conserto = "AND temp_os_aberta.data_conserto is null ";
	} else {
		$cond_data_conserto = "";
	}

	if (strlen($intervencao) > 0 AND $login_fabrica == 3) {
//		$join_intervencao = "LEFT JOIN tbl_os_status using(os)";
		$temp_intervencao = " AND tbl_os.os NOT IN (SELECT os FROM tbl_os_status WHERE tbl_os_status.fabrica_status=$login_fabrica AND tbl_os.os = tbl_os_status.os AND status_os IN (62,64,65)) ";
	}

	$temp_cancelada_90_dias = " ";
	if (strlen($cancelada_90_dias) > 0 AND $login_fabrica == 3) {
		$sql_cancelada_90_dias = "SELECT os
					  INTO TEMP tmp_canceladas_90_dias
					  FROM tbl_os_status
					  WHERE status_os = 126 and fabrica_status=$login_fabrica;

					  CREATE INDEX tmp_canceladas_90_dias_os ON tmp_canceladas_90_dias(os);";

		$temp_cancelada_90_dias = "
						AND tbl_os.os NOT IN ( SELECT os
								FROM tmp_canceladas_90_dias) ";
	}

	$temp_cancelada_45_dias = " ";
	if (strlen($cancelada_45_dias) > 0 AND $login_fabrica == 3) {
		$sql_cancelada_45_dias="SELECT os
					INTO TEMP tmp_canceladas_45_dias
					FROM tbl_os_status
					WHERE status_os = 143 and fabrica_status=$login_fabrica;

					CREATE INDEX tmp_canceladas_45_dias_os ON tmp_canceladas_45_dias(os);";


		$temp_cancelada_45_dias = "
						AND tbl_os.os NOT IN ( SELECT os
								FROM tmp_canceladas_45_dias ) ";
	}

	if ($excluidas) {
		$sql_excluida = " AND temp_os_aberta.os NOT IN (SELECT os FROM tbl_os_excluida WHERE tbl_os_excluida.fabrica=$login_fabrica AND os=temp_os_aberta.os)";
		$sql_excluida .= " AND temp_os_aberta.excluida <> 't'";
	}

	$sql = "
			$sql_cancelada_90_dias

			$sql_cancelada_45_dias

			SELECT	tbl_os.os                                                               ,
				tbl_os.sua_os                                                           ,
				tbl_os.consumidor_nome                                                  ,
				tbl_os.consumidor_fone                                                  ,
				tbl_os.serie                                                            ,
				tbl_os.pecas                                                            ,
				tbl_os.mao_de_obra                                                      ,
				tbl_os.nota_fiscal                                                      ,
				to_char (tbl_os.data_digitacao,'DD/MM/YYYY')      AS data_digitacao     ,
				to_char (tbl_os.data_abertura,'DD/MM/YYYY')       AS data_abertura      ,
				to_char (tbl_os.data_fechamento,'DD/MM/YYYY')     AS data_fechamento    ,
				to_char (tbl_os.data_conserto,'DD/MM/YYYY')       AS data_conserto      ,
				to_char (tbl_os.finalizada,'DD/MM/YYYY')          AS data_finalizada    ,
				to_char (tbl_os.data_nf,'DD/MM/YYYY')             AS data_nf            ,
				current_date - data_abertura                      AS dias_uso           ,
				tbl_os_produto.os_produto,
			        tbl_produto.referencia,
                                tbl_produto.descricao,
				tbl_os.posto,
				tbl_os.excluida,
				tbl_os.defeito_constatado,
				tbl_os.fabrica
			INTO TEMP TABLE temp_os_aberta
			FROM tbl_os
			JOIN tbl_produto ON tbl_produto.produto = tbl_os.produto and tbl_produto.fabrica_i = tbl_os.fabrica
			LEFT JOIN tbl_os_produto USING(os)
			$join_intervencao
			WHERE tbl_os.fabrica = $login_fabrica
			AND   tbl_os.finalizada IS NULL
			AND   tbl_os.data_fechamento IS NULL
			AND   tbl_os.os_fechada IS NOT TRUE
			AND   tbl_os.posto <> 6359
			$sql_posto
			AND   data_abertura < current_date - INTERVAL '$qtde_dias days'
			$temp_intervencao
			$temp_cancelada_90_dias
			$temp_cancelada_45_dias
			";

			if (strlen($linha) > 0)             $sql .= " AND tbl_produto.linha = $linha ";


			$sql .= "; CREATE INDEX temp_os_aberta_os ON temp_os_aberta(os);
			CREATE INDEX temp_os_aberta_fabrica ON temp_os_aberta(fabrica);

			SELECT  temp_os_aberta.referencia                         AS produto_referencia ,
				temp_os_aberta.descricao                          AS produto_descricao  ,
				tbl_defeito_constatado.descricao                  AS defeito_constatado ,
				tbl_posto.posto                                                         ,
				tbl_posto_fabrica.codigo_posto                                          ,
				tbl_posto.nome AS nome_posto                                            ,
				tbl_posto.pais AS posto_pais                              ,
				temp_os_aberta.*
			FROM    temp_os_aberta
			JOIN    tbl_posto         ON  temp_os_aberta.posto  = tbl_posto.posto
			JOIN    tbl_posto_fabrica ON  tbl_posto.posto = tbl_posto_fabrica.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
			LEFT JOIN tbl_defeito_constatado  ON  temp_os_aberta.defeito_constatado    = tbl_defeito_constatado.defeito_constatado
			WHERE temp_os_aberta.fabrica = $login_fabrica
			$cond_sem_peca
			$cond_data_conserto
			$sql_excluida
			";

	$sql .= " ORDER BY dias_uso,temp_os_aberta.sua_os;";
	#echo nl2br($sql);
	#exit;
	$res = pg_exec($con,$sql);
	$numero_registros = pg_numrows($res);


	$conteudo = "";
	$data = date("Y-m-d").".".date("H-i-s");

	$arquivo_nome     = "relatorio-os-aberta-$login_fabrica.$login_admin.xls";
	$path             = "/www/assist/www/admin/xls/";
	$path_tmp         = "/tmp/";

	$arquivo_completo     = $path.$arquivo_nome;
	$arquivo_completo_tmp = $path_tmp.$arquivo_nome;

	$fp = fopen ($arquivo_completo_tmp,"w");

	fputs ($fp,"<html>");
	fputs ($fp,"<body>");

	echo "<p id='id_download' style='display:none'><a href='xls/$arquivo_nome'><img src='../imagens/excel.gif'><br/>Fazer download do arquivo em  XLS </a></p>";

	if (pg_numrows($res) > 0) {

		if($login_fabrica==20){
			for ($i = 0 ; $i < pg_numrows($res) ; $i++) {
				$sua_os      = pg_result($res,$i,sua_os);
				$pecas       = pg_result($res,$i,pecas);
				$mao_de_obra = pg_result($res,$i,mao_de_obra);
				$vet_pecas[$sua_os]= $pecas;
				$vet_mao_de_obra[$sua_os]= $mao_de_obra;
			}
		}

		$conteudo .=  "<table width='700' class='tabela' cellspacing='1' cellpadding='0'>";
		$conteudo .=  "<tr class='titulo_coluna'>";
		$conteudo .=  "<td nowrap>OS</td>";
		$conteudo .=  "<td nowrap>Abertura</td>";
		$conteudo .=  "<td nowrap>Digita��o</td>";
		$conteudo .=  "<td nowrap>Conserto</td>";
		$conteudo .=  "<td nowrap>Consumidor</td>";
		$conteudo .=  "<td nowrap>Descri��o Produto</td>";
		$conteudo .=  "<td nowrap>Refer�ncia Produto</td>";
		$conteudo .=  "<td nowrap>Raz�o Social</td>";
		$conteudo .=  "<td nowrap>C�digo Posto</td>";
		$conteudo .=  "<td nowrap>Data �ltimo</td>";
		$conteudo .=  "<td nowrap>�ltimo Pedido</td>";
		$conteudo .=  "<td nowrap>Status �ltimo Pedido</td>";
		$conteudo .=  "<td nowrap>Qtde Pe�as</td>";
		$conteudo .=  "<td nowrap>Dias em Aberto</td>";
		$conteudo .=  "</tr>";

		/**
		 * @since HD 802851 - alterado para n�o mais exibir o resultado na tela, apenas download
		 */
		#echo $conteudo;
		fputs ($fp,$conteudo);

		for ($i = 0 ; $i < pg_numrows($res) ; $i++) {
			$os                 = pg_result($res,$i,os);
			$sua_os             = pg_result($res,$i,sua_os);
			$consumidor_nome    = pg_result($res,$i,consumidor_nome);
			$consumidor_fone    = pg_result($res,$i,consumidor_fone);
			$serie              = pg_result($res,$i,serie);
			$nota_fiscal        = pg_result($res,$i,nota_fiscal);
			$data_digitacao     = pg_result($res,$i,data_digitacao);
			$data_abertura      = pg_result($res,$i,data_abertura);
			$data_fechamento    = pg_result($res,$i,data_fechamento);
			$data_conserto      = pg_result($res,$i,data_conserto);
			$data_finalizada    = pg_result($res,$i,data_finalizada);
			$data_nf            = pg_result($res,$i,data_nf);
			$dias_uso           = pg_result($res,$i,dias_uso);
			$produto_referencia = pg_result($res,$i,produto_referencia);
			$produto_descricao  = pg_result($res,$i,produto_descricao);
			$posto              = pg_result($res,$i,posto);
			$codigo_posto       = pg_result($res,$i,codigo_posto);
			$nome_posto         = pg_result($res,$i,nome_posto);
			$defeito_constatado	= pg_result($res,$i,defeito_constatado);
			$posto_pais         = pg_result($res,$i,posto_pais);

			$cor = ($i % 2 == 0) ? '#F1F4FA' : '#F7F5F0';

			#-------- Desconsidera ou marca em vermelho OS Excluidas ------
			$sql = "SELECT * FROM tbl_os_excluida WHERE fabrica = $login_fabrica and os = $os";
			$resX = pg_query ($con,$sql);
			$dica = "";
			if (pg_num_rows ($resX) > 0) {
				$cor = "#FF3300";
				$dica = "OS exclu�da";
				if ($excluidas == 't') {
					continue ;
				}
			}

			if ($login_fabrica == 1) $sua_os = $codigo_posto.$sua_os;

			$fat_emissao = "";
			$fat_nf      = "";
			$data_ult_ped = "";
			$pedido_ult = "" ;

			if(strlen($os)>0) {

				$sql_dat_ult = "SELECT	to_char(data,'DD/MM/YYYY'),
							tbl_pedido.pedido,
							tbl_status_pedido.descricao
						from tbl_pedido
						join tbl_os_item using(pedido)
						join tbl_os_produto using(os_produto)
						join tbl_os using(os)
						join tbl_status_pedido using(status_pedido)
						join tbl_pedido_item ON tbl_pedido.pedido = tbl_pedido_item.pedido
						where tbl_os.fabrica=$login_fabrica
						and tbl_pedido.fabrica = tbl_os.fabrica
						and os = $os order by tbl_pedido.data desc limit 1";
				//echo nl2br($sql_dat_ult);
				$res_dat_ult = pg_exec($con,$sql_dat_ult);
				if (pg_num_rows($res_dat_ult)>0) {
					$data_ult_ped = pg_result($res_dat_ult,0,0);
					$pedido_ult = pg_result($res_dat_ult,0,1);
					$status_ult = pg_result($res_dat_ult,0,2);
				}


				$sqlpecas = "SELECT COUNT(*) from tbl_os join tbl_os_produto using(os) join tbl_os_item using(os_produto) where tbl_os.fabrica = $login_fabrica and os = $os";
				$respecas = pg_exec($con,$sqlpecas);

				$qtdepecas = pg_result($respecas,0,0);
				/*
				$sql2 = "SELECT TO_CHAR(emissao,'dd/mm/YYYY') AS emissao,
								nota_fiscal
						FROM tbl_faturamento_item
						JOIN (
						SELECT faturamento,emissao,nota_fiscal
						FROM tbl_faturamento
						WHERE tbl_faturamento.posto = $posto
						AND tbl_faturamento.fabrica = 3
						AND tbl_faturamento.conferencia IS NULL
						AND tbl_faturamento.cancelada  IS NULL
						AND tbl_faturamento.distribuidor IS NULL
						) fat ON tbl_faturamento_item.faturamento = fat.faturamento
						WHERE tbl_faturamento_item.peca   = $peca
						AND   tbl_faturamento_item.pedido = $pedido
						AND   tbl_faturamento_item.os     = $os
						;";

				$res2 = pg_exec($con,$sql2);
				if(pg_numrows($res2)>0){
					$fat_emissao = pg_result($res2,0,0);
					$fat_nf      = pg_result($res2,0,1);
				}else{
					$fat_emissao = "Pendente";
					$fat_nf      = "Pendente";
				}
				*/

			}

			$conteudo = "";
			$conteudo .=  "<tr class='Conteudo' bgcolor='$cor' title='$dica'>";
			$conteudo .=  "<td nowrap align='center'><a target='_blank' href='http://posvenda.telecontrol.com.br/assist/admin/os_press.php?os=$os' title='Clique para visualizar a OS em uma nova tela'>$sua_os</a></td>";
			$conteudo .=  "<td nowrap align='center'>$data_abertura</td>";
			$conteudo .=  "<td nowrap align='center'>$data_digitacao</td>";
			$conteudo .=  "<td nowrap align='center'>$data_conserto</td>";
			$conteudo .=  "<td nowrap align='left'>$consumidor_nome</td>";
			$conteudo .=  "<td nowrap align='left'>$produto_descricao</td>";
			$conteudo .=  "<td nowrap align='center'>$produto_referencia</td>";
			$conteudo .=  "<td nowrap align='left'>$nome_posto</td>";
			$conteudo .=  "<td nowrap align='center'>$codigo_posto</td>";

			$conteudo .=  "<td nowrap align='center'>$data_ult_ped</td>";
			$conteudo .=  "<td nowrap align='center'>$pedido_ult</td>";
			$conteudo .=  "<td nowrap align='center'>$status_ult</td>";
			$conteudo .=  "<td nowrap align='center'>$qtdepecas</td>";

			$conteudo .=  "<td nowrap align='center'>$dias_uso</td>";

			$conteudo .= "</tr>";

			#echo $conteudo;
			fputs ($fp,$conteudo);
		}

		$conteudo  = "<tr>";
		$conteudo  = "<td nowrap align='left'>";
		$conteudo  = "</table>";
		$conteudo .= "<BR><CENTER>".$numero_registros." Registros encontrados</CENTER>";
		#echo $conteudo;

		fputs ($fp,$conteudo);
		fputs ($fp,"</body>");
		fputs ($fp,"</html>");
		fclose ($fp);
		flush();
		echo ` cp $arquivo_completo_tmp $path `;

		echo "<script language='javascript'>";
		echo "document.getElementById('id_download').style.display='block';";
		echo "</script>";
		flush();
		echo "<br>";
	}else {
		echo "<p style='font-size:13px;'>N�o foram encontrados resultados para esta pesquisa!</p>";
	}
}

echo "<br>";

include "rodape.php";
?>
