<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';

include 'autentica_usuario.php';
include 'funcoes.php';

$msg_erro = "";

$btn_acao = trim (strtolower ($_POST['btn_acao']));

if (strlen($_GET['os_metal']) > 0)  $os_metal = trim($_GET['os_metal']);
if (strlen($_POST['os_metal']) > 0) $os_metal = trim($_POST['os_metal']);

# executa funcao de explosao
if ($btn_acao == "explodir"){

	if (strlen($os_metal)>0) {

		$res = pg_exec($con,"BEGIN TRANSACTION");

		$sql = "SELECT fn_explode_os_revenda($os_metal,$login_fabrica)";
		$res = pg_exec ($con,$sql);
		$msg_erro = pg_errormessage($con);

		if (strpos($msg_erro,"CONTEXT:")) {
			$x = explode('CONTEXT:',$msg_erro);
			$msg_erro = $x[0];
		}

		if( strpos($msg_erro,'data_nf_superior_data_abertura') ) {
			$msg_erro="A data de nota fiscal n�o pode ser maior que a data de abertura. Por favor, clique em bot�o Alterar para fazer a corre��o.";
		}

		if (strlen($msg_erro) == 0){
			$res = pg_exec ($con,"COMMIT TRANSACTION");

			$sql = "SELECT  sua_os, posto
					FROM	tbl_os_revenda
					WHERE	os_revenda = $os_metal
					AND		fabrica = $login_fabrica";
			$res = pg_exec($con, $sql);
			$os_numero = @pg_result($res,0,sua_os);
			$posto     = @pg_result($res,0,posto);

			header("Location: os_metal_explodida.php?os_numero=$os_numero&posto=$posto");
			exit;
		}else{
			$res = pg_exec ($con,"ROLLBACK TRANSACTION");
		}
	}
}

if(strlen($os_metal) > 0){
	$sql = "SELECT  tbl_os_revenda.os_revenda,
					tbl_os_revenda.consumidor_revenda,
					TO_CHAR(tbl_os_revenda.data_abertura,'DD/MM/YYYY') AS data_abertura,
					TO_CHAR(tbl_os_revenda.digitacao,'DD/MM/YYYY')     AS data_digitacao,
					tbl_os_revenda.posto,
					tbl_os_revenda.sua_os,
					tbl_os_revenda.consumidor_nome,
					tbl_os_revenda.qtde_km,
					tbl_os_revenda.valor_por_km,
					tbl_os_revenda.quem_abriu_chamado,
					tbl_os_revenda.taxa_visita,
					tbl_os_revenda.hora_tecnica,
					tbl_os_revenda.cobrar_percurso,
					tbl_os_revenda.visita_por_km,
					tbl_os_revenda.diaria,
					tbl_os_revenda.obs,
					tbl_os_revenda.contrato,
					tbl_os_revenda.nota_fiscal,
					TO_CHAR(tbl_os_revenda.data_nf,'DD/MM/YYYY') AS data_nf,
					tbl_os_revenda.consumidor_nome                 ,
					tbl_os_revenda.consumidor_cnpj                 ,
					tbl_os_revenda.consumidor_fone                 ,
					tbl_os_revenda.consumidor_endereco             ,
					tbl_os_revenda.consumidor_numero               ,
					tbl_os_revenda.consumidor_complemento          ,
					tbl_os_revenda.consumidor_bairro               ,
					tbl_os_revenda.consumidor_cep                  ,
					tbl_os_revenda.consumidor_cidade               ,
					tbl_os_revenda.consumidor_estado               ,
					tbl_revenda.revenda              AS revenda,
					tbl_revenda.nome                 AS revenda_nome,
					tbl_revenda.cnpj                 AS revenda_cnpj,
					tbl_revenda.fone                 AS revenda_fone,
					tbl_revenda.endereco             AS revenda_endereco,
					tbl_revenda.numero               AS revenda_numero,
					tbl_revenda.complemento          AS revenda_complemento,
					tbl_revenda.bairro               AS revenda_bairro,
					tbl_revenda.cep                  AS revenda_cep,
					CR.nome                          AS revenda_cidade,
					CR.estado                        AS revenda_estado,
					tbl_posto_fabrica.codigo_posto,
					tbl_posto.posto,
					tbl_posto.nome,
					tbl_posto.cnpj,
					tbl_posto_fabrica.contato_cidade AS posto_cidade,
					tbl_posto_fabrica.contato_estado AS posto_estado
			FROM	tbl_os_revenda
			JOIN	tbl_posto USING(posto)
			JOIN	tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_posto.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
			LEFT JOIN tbl_revenda   ON tbl_revenda.revenda = tbl_os_revenda.revenda
			LEFT JOIN tbl_cidade CR ON CR.cidade           = tbl_revenda.cidade
			WHERE	tbl_os_revenda.fabrica    = $login_fabrica
			AND		tbl_os_revenda.os_revenda = $os_metal";
#	echo nl2br($sql);
	$res = pg_exec($con, $sql);
	if (pg_numrows($res) > 0) {
		$os_metal            = trim(pg_result($res,0,os_revenda));
		$sua_os              = trim(pg_result($res,0,sua_os));
		$consumidor_revenda  = trim(pg_result($res,0,consumidor_revenda));
		$data_abertura       = trim(pg_result($res,0,data_abertura));
		$data_digitacao      = trim(pg_result($res,0,data_digitacao));
		$posto               = trim(pg_result($res,0,posto));
		$consumidor_nome     = trim(pg_result($res,0,consumidor_nome));
		$qtde_km             = trim(pg_result($res,0,qtde_km));
		$valor_por_km        = trim(pg_result($res,0,valor_por_km));
		$quem_abriu_chamado  = trim(pg_result($res,0,quem_abriu_chamado));
		$taxa_visita         = trim(pg_result($res,0,taxa_visita));
		$hora_tecnica        = trim(pg_result($res,0,hora_tecnica));
		$cobrar_percurso     = trim(pg_result($res,0,cobrar_percurso));
		$visita_por_km       = trim(pg_result($res,0,visita_por_km));
		$diaria              = trim(pg_result($res,0,diaria));
		$obs                 = trim(pg_result($res,0,obs));
		$contrato            = trim(pg_result($res,0,contrato));
		$nota_fiscal         = trim(pg_result($res,0,nota_fiscal));
		$data_nf             = trim(pg_result($res,0,data_nf));

		$consumidor_nome        = trim(pg_result($res,0,consumidor_nome));
		$consumidor_cnpj        = trim(pg_result($res,0,consumidor_cnpj));
		$consumidor_fone        = trim(pg_result($res,0,consumidor_fone));
		$consumidor_endereco    = trim(pg_result($res,0,consumidor_endereco));
		$consumidor_numero      = trim(pg_result($res,0,consumidor_numero));
		$consumidor_complemento = trim(pg_result($res,0,consumidor_complemento));
		$consumidor_bairro      = trim(pg_result($res,0,consumidor_bairro));
		$consumidor_cep         = trim(pg_result($res,0,consumidor_cep));
		$consumidor_cidade      = trim(pg_result($res,0,consumidor_cidade));
		$consumidor_estado      = trim(pg_result($res,0,consumidor_estado));
		$revenda             = trim(pg_result($res,0,revenda));
		$revenda_nome        = trim(pg_result($res,0,revenda_nome));
		$revenda_cnpj        = trim(pg_result($res,0,revenda_cnpj));
		$revenda_fone        = trim(pg_result($res,0,revenda_fone));
		$revenda_endereco    = trim(pg_result($res,0,revenda_endereco));
		$revenda_numero      = trim(pg_result($res,0,revenda_numero));
		$revenda_complemento = trim(pg_result($res,0,revenda_complemento));
		$revenda_bairro      = trim(pg_result($res,0,revenda_bairro));
		$revenda_cep         = trim(pg_result($res,0,revenda_cep));
		$revenda_cidade      = trim(pg_result($res,0,revenda_cidade));
		$revenda_estado      = trim(pg_result($res,0,revenda_estado));
		$posto_codigo        = trim(pg_result($res,0,codigo_posto));
		$posto_nome          = trim(pg_result($res,0,nome));
		$posto_cidade        = trim(pg_result($res,0,posto_cidade));
		$posto_estado        = trim(pg_result($res,0,posto_estado));

		$taxa_visita   = number_format($taxa_visita,2,",",".");
		$hora_tecnica  = number_format($hora_tecnica,2,",",".");
		$visita_por_km = number_format($visita_por_km,2,",",".");
		$diaria        = number_format($diaria,2,",",".");
	}else{
		header('Location: os_cadastro_metais_sanitario_new.php');
		exit;
	}
}

$title			= "Cadastro de Ordem de Servi�o - Metais Sanitarios"; 
$layout_menu	= "callcenter";

include "cabecalho.php";

?>

<style type="text/css">

.menu_top {
	text-align: center;
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: x-small;
	font-weight: bold;
	border: 1px solid;
	color:#ffffff;
	background-color: #596D9B
}

.table {
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: x-small;
	text-align: center;
	border: 1px solid #d9e2ef;
}

.table_line {
	text-align: left;
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: normal;
	border: 0px solid;
}

.table_line2 {
	text-align: left;
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: normal;
	background-color: #CED7e7;
}

</style>

<? 
if (strlen ($msg_erro) > 0) {
?>
<table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff">
<tr>
	<td height="27" valign="middle" align="center">
		<b><font face="Arial, Helvetica, sans-serif" color="#FF3333">
		<? echo $msg_erro ?>
		</font></b>
	</td>
</tr>
</table>
<?
}
?>

<br>

<form name="frm_os" method="post" action="<? echo $PHP_SELF ?>">
<input type='hidden' name='os_metal' value='<? echo $os_metal; ?>'>

<table width="650" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff" class="table">
	<tr>
		<td valign="top" align="left">

			<table width="100%" border="0" cellspacing="3" cellpadding="2">
				<tr class="menu_top">
					<td nowrap>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">OS Fabricante</font>
					</td>
					<td>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">Nome Cliente</font>
					</td>
					<td>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">CPF/CNPJ Cliente</font>
					</td>
					<td>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">KM Cliente</font>
					</td>
					<td>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">Valor por km</font>
					</td>
				</tr>

				<tr class="table_line">
					<td nowrap align='center'>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">
						<? echo $posto_codigo; echo $sua_os; ?>
						</font>
					</td>
					<td align='center'><?=$consumidor_nome?></td>
					<td align='center'><?=$consumidor_cnpj?>
					</td>
					<td align='center'><?=$qtde_km?></td>
					<td align='center'><?=$valor_por_km?></td>
				</tr>
			</table>
			<table width="100%" border="0" cellspacing="3" cellpadding="2">
				<tr class="menu_top">
	
					<td>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">Nome da Revenda</font>
					</td>
					<td>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">CNPJ</font>
					</td>
					<td>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">Fone</font>
					</td>
					<td>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">E-Mail</font>
					</td>
				</tr>

				<tr class="table_line">

					<td align='center'><?=$revenda_nome?></td>
					<td align='center'><?=$revenda_cnpj?></td>
					<td align='center'><?=$revenda_fone?></td>
					<td align='center'><?=$revenda_email?></td>
				</tr>
			</table>

<!--
			<table width="100%" border="0" cellspacing="3" cellpadding="2">
				<tr class="menu_top">
					<td>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">C�digo do posto</font>
					</td>
					<td>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">Nome do posto</font>
					</td>
				</tr>
				<tr>
					<td align='center'><?=$posto_codigo?></td>
					<td align='center'><?=$posto_nome?></td>
				</tr>
			</table>
-->


			<table width="100%" border="0" cellspacing="3" cellpadding="2">
				<tr class="menu_top">
					<td>
						<font size="1" face="Geneva, Arial, Helvetica, san-serif">Observa��es</font>
					</td>
				</tr>
				<tr class="table_line">
					<td align='center'><? echo $obs ?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>


<table width="550" border="0" cellpadding="2" cellspacing="3" align="center" bgcolor="#ffffff">
	<TR>
		<TD colspan="4"><br></TD>
	</TR>
	<tr class="menu_top">
		<td align="center"><font size="1" face="Geneva, Arial, Helvetica, san-serif">Produto</font></td>
		<td align="center"><font size="1" face="Geneva, Arial, Helvetica, san-serif">Descri��o do produto</font></td>
		<td align="center" nowrap><font size="1" face="Geneva, Arial, Helvetica, san-serif">N�mero de s�rie</font></td>
		<td align="center"><font size="1" face="Geneva, Arial, Helvetica, san-serif">Capacidade</font></td>
		<!--<td align="center" nowrap><font size="1" face="Geneva, Arial, Helvetica, san-serif">Peso Padr�o </font></td>-->
		<td align="center" nowrap><font size="1" face="Geneva, Arial, Helvetica, san-serif">Cert. Conf</font></td>
		<td align="center" nowrap><font size="1" face="Geneva, Arial, Helvetica, san-serif">Defeito Reclamado</font></td>
	</tr>
<?
if ($os_metal){
	// seleciona do banco de dados
	$sql = "SELECT  tbl_os_revenda_item.os_revenda_item            ,
					tbl_os_revenda_item.produto                    ,
					tbl_os_revenda_item.serie                      ,
					tbl_os_revenda_item.capacidade                 ,
					tbl_os_revenda_item.regulagem_peso_padrao      ,
					tbl_os_revenda_item.certificado_conformidade   ,
					tbl_os_revenda_item.defeito_reclamado          ,
					tbl_os_revenda_item.defeito_reclamado_descricao,
					tbl_produto.referencia                         ,
					tbl_produto.descricao
			FROM	tbl_os_revenda
			JOIN	tbl_os_revenda_item USING(os_revenda)
			JOIN	tbl_produto         USING (produto)
			LEFT JOIN	tbl_defeito_reclamado USING(defeito_reclamado)
			WHERE	tbl_os_revenda.fabrica         = $login_fabrica
			AND		tbl_os_revenda_item.os_revenda = $os_metal 
			ORDER BY tbl_os_revenda_item.os_revenda_item ASC ";
	$res = pg_exec($con, $sql);
	$qtde_item_os = pg_numrows($res);

	for ($i=0; $i<$qtde_item_os; $i++) {
		$os_revenda_item             = pg_result($res,$i,os_revenda_item);
		$produto_referencia          = pg_result($res,$i,referencia);
		$produto_descricao           = pg_result($res,$i,descricao);
		$produto_serie               = pg_result($res,$i,serie);
		$produto_capacidade          = pg_result($res,$i,capacidade);
		$regulagem_peso_padrao       = pg_result($res,$i,regulagem_peso_padrao);
		$certificado_conformidade    = pg_result($res,$i,certificado_conformidade);
		$defeito_reclamado           = pg_result($res,$i,defeito_reclamado);
		$defeito_reclamado_descricao = pg_result($res,$i,defeito_reclamado_descricao);
	?>
		<tr class="table_line">
			<td align="center">
				<font size="1" face="Geneva, Arial, Helvetica, san-serif"><? echo $produto_referencia ?></font>
			</td>
			<td align="left" nowrap>
				<font size="1" face="Geneva, Arial, Helvetica, san-serif"> <? echo $produto_descricao; ?></font>
			</td>
			<td align="center">
				<font size="1" face="Geneva, Arial, Helvetica, san-serif"><? echo $produto_serie ?></font>
			</td>
			<td align="center" nowrap>
				<font size="1" face="Geneva, Arial, Helvetica, san-serif"><? echo $produto_capacidade ?></font>
			</td>
			<!--<td align="center">
				<font size="1" face="Geneva, Arial, Helvetica, san-serif"><? echo $regulagem_peso_padrao ?></font>-->
			</td>
			<td align="center">
				<font size="1" face="Geneva, Arial, Helvetica, san-serif"><? echo $certificado_conformidade ?></font>
			</td>
			<td align="center">
				<font size="1" face="Geneva, Arial, Helvetica, san-serif"><? echo $defeito_reclamado_descricao ?></font>
			</td>
		</tr>
<?	}
}
?>
</table>

<br>

<input type='hidden' name='btn_acao' value=''>

<center>
<img src='imagens/btn_alterarcinza.gif'  onclick="javascript: document.location='os_cadastro_metais_sanitario_new.php?os_metal=<? echo $os_metal; ?>'" ALT="Alterar" border='0' style="cursor:pointer;">
<img src='imagens/btn_explodir.gif' onclick="javascript: if (document.frm_os.btn_acao.value == '' ) { document.frm_os.btn_acao.value='explodir' ; document.frm_os.submit() } else { alert ('Aguarde submiss�o') }" ALT="Explodir" border='0' style="cursor:pointer;">
<img src='imagens/btn_imprimir.gif' onclick="javascript: window.open('os_print_metal.php?os_revenda=<? echo $os_metal; ?>','os_metal');" ALT="Imprimir" border='0' style="cursor:pointer;">
</center>

<br>


</form>
<br>

<? include 'rodape.php'; ?>