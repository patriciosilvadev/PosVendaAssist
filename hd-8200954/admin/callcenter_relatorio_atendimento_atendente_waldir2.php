<?
//CHAMADO:		134895
//PROGRAMADOR:	EBANO LOPES
//SOLICITANTE:	11 - LENOXX

/**
 * Corrigido problema na query, que filtrava chamados do Help-desk e do callcenter sem nenhuma diferen�a.
 * N�o contar intera��es da abertura de chamado.
 * N�o contar intera��es de mudan�a de status.
 * HD 155210 - Augusto Pascutti (2009-09-23)
 */

include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'includes/funcoes.php';
include 'autentica_admin.php';
include '../helpdesk.inc.php';

$admin_privilegios="callcenter";
$layout_menu = "callcenter";
$title = "RELAT�RIO DE ATENDIMENTOS POR ATENDENTE";

include "cabecalho.php";
include "javascript_pesquisas.php";
include "javascript_calendario.php";

$btn_acao = $_POST['btn_acao'];

if ($btn_acao=="Pesquisar")
{
	$xdata_inicial	= implode("-", array_reverse(explode("/", $_POST["data_inicial"]))) . " 00:00:00";
	$xdata_final	= implode("-", array_reverse(explode("/", $_POST["data_final"]))) . " 23:59:59";

    //VALIDANDO AS DATAS
    $sql = "SELECT '$xdata_inicial'::timestamp, '$xdata_final'::timestamp";
    @$res = pg_query($sql);
    if (!$res)
    {
		$msg_erro = "Data Inv�lida";
		$btn_acao = "";
	}
	if($xdata_inicial > $xdata_final)
		$msg_erro = "Data Inv�lida";

}

?>

<!-- ******************************** JAVASCRIPT ******************************** -->

<script type="text/javascript" charset="utf-8">
	$(function(){
		$('#data_inicial').datePicker({startDate:'01/01/2000'});
		$('#data_final').datePicker({startDate:'01/01/2000'});
		$("#data_inicial").maskedinput("99/99/9999");
		$("#data_final").maskedinput("99/99/9999");
	});
</script>

<script language='javascript' src='../ajax.js'></script>


<!-- ******************************** FIM JAVASCRIPT ******************************** -->

<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />

<style>
.contitulo {
	text-align: center;
	font-weight: bold;
	color: #FFFFFF;
	background-color: #485989;
}

.linha0 {
	background-color: #F1F4FA;
}

.linha1 {
	background-color: #E6EEF7;
}

.Titulo {
	text-align: center;
	font-family: Arial;
	font-size: 10px;
	font-weight: bold;
	color: #FFFFFF;
	background-color: #485989;
}
.Conteudo {
	font-family: Arial;
	font-size: 10px;
	font-weight: normal;
}
.ConteudoBranco {
	font-family: Arial;
	font-size: 9px;
	color:#FFFFFF;
	font-weight: normal;
}
.Mes{
	font-size: 9px;
}
.Caixa{
	BORDER-RIGHT: #6699CC 1px solid;
	BORDER-TOP: #6699CC 1px solid;
	FONT: 8pt Arial ;
	BORDER-LEFT: #6699CC 1px solid;
	BORDER-BOTTOM: #6699CC 1px solid;
	BACKGROUND-COLOR: #FFFFFF;
}
.Exibe{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 8 px;
	font-weight: none;
	color: #000000;
	text-align: center;
}
.Erro{
	BORDER-RIGHT: #990000 1px solid;
	BORDER-TOP: #990000 1px solid;
	FONT: 10pt Arial ;
	COLOR: #ffffff;
	BORDER-LEFT: #990000 1px solid;
	BORDER-BOTTOM: #990000 1px solid;
	BACKGROUND-COLOR: #FF0000;
}
.Carregando{
	TEXT-ALIGN: center;
	BORDER-RIGHT: #aaa 1px solid;
	BORDER-TOP: #aaa 1px solid;
	FONT: 10pt Arial ;
	COLOR: #000000;
	BORDER-LEFT: #aaa 1px solid;
	BORDER-BOTTOM: #aaa 1px solid;
	BACKGROUND-COLOR: #FFFFFF;
	margin-left:20px;
	margin-right:20px;
}

.relerro{
	color: #FF0000;
	font-size: 11pt;
	padding: 20px;
	background-color: #F7F7F7;
	text-align: center;
}

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
	font: bold 16px "Arial";
	color:#FFFFFF;
	text-align:center;
}

.formulario{
	background-color:#D9E2EF;
	font:11px Arial;
}

.subtitulo{

	background-color: #7092BE;
	font:bold 11px Arial;
	color: #FFFFFF;
}

table.tabela tr td{
	font-family: verdana;
	font-size: 11px;
	border-collapse: collapse;
	border:1px solid #596d9b;
}
</style>


<FORM name="frm_relatorio" METHOD="POST" ACTION="<? echo $PHP_SELF ?>">

<table width='700' class='formulario' border='0' cellpadding='5' cellspacing='1' align='center'>
	<? if(strlen($msg_erro)>0){ ?>
		<tr class='msg_erro'><td><? echo $msg_erro; ?></td></tr>
	<? } ?>
	<tr class='titulo_tabela'>
		<td>Par�metros de Pesquisa</td>
	</tr>

	<tr>
		<td valign='bottom'>
			<table width='100%' border='0' cellspacing='1' cellpadding='2' class='formulario'>
				<tr>
					<td width="10">&nbsp;</td>
					<td align='right'><font size='2'>Data Inicial</td>
					<td align='left'>
						<input type="text" id="data_inicial" name="data_inicial" size="12" maxlength="7" class="frm" value="<?=$data_inicial?>">
					</td>
					<td width="10">&nbsp;</td>
					<td align='right'><font size='2'>Data Final</td>
					<td align='left'>
						<input type="text" id="data_final" name="data_final" size="12" maxlength="7" class="frm" value="<?=$data_final?>">
					</td>
					<td width="10">&nbsp;</td>
				</tr>
			</table><br>
			<input type='submit' value='Pesquisar' id='btn_acao' name='btn_acao'>
		</td>
	</tr>
</table>
</FORM>

<?

if (strlen ($btn_acao) > 0 and strlen($msg_erro)==0) {
	
	if (strlen($xdata_inicial) > 0 AND strlen($xdata_final) > 0){
		$sql = "
			SELECT
				tbl_admin.admin,
				tbl_admin.login AS nome_usuario,
				tbl_admin.nome_completo,
				COUNT(hd_chamado_item) AS interacoes
			
			FROM tbl_hd_chamado	
			INNER JOIN tbl_hd_chamado_extra USING (hd_chamado)
			LEFT JOIN tbl_hd_chamado_item USING (hd_chamado)
			INNER JOIN tbl_admin ON ( tbl_hd_chamado.admin = tbl_admin.admin )
			
			WHERE	1=1
			AND tbl_admin.fabrica = $login_fabrica
			AND tbl_hd_chamado.fabrica_responsavel = $login_fabrica
			AND (tbl_hd_chamado.data BETWEEN '$xdata_inicial' AND '$xdata_final' OR tbl_hd_chamado_item.data  BETWEEN '$xdata_inicial' AND '$xdata_final' )
			GROUP BY tbl_admin.admin, tbl_admin.login, tbl_admin.nome_completo
			ORDER BY tbl_admin.nome_completo
			";
		$res = pg_exec($con, $sql);
		$aStatusInteracoes = array('Analise','Aberto','Cancelado','Resolvido');

		if(pg_num_rows($res) > 0){
?>
		<table align="center" class="tabela" cellpadding="2" cellspacing='1'>
		<tr class="titulo_coluna">
			<td width="80px" rowspan="2">Login</td>
			<td width="175px" rowspan="2">Nome Completo</td>
			<td width="90px" rowspan="2" title="Chamados em que o usu�rio � atendente">Chamados</td>
			<td width="90px" colspan="<?php echo count($aStatusInteracoes)+1; ?>">Intera��es</td>
		</tr>
		<tr class="titulo_coluna">
			<?php foreach($aStatusInteracoes as $sStatus): ?>
				<td> <?php echo $sStatus; ?> </td>
			<?php endforeach; ?>
			<td>Total</td>
		</tr>
<?php
		for($i = 0; $i < pg_num_rows($res); $i++)
		{
			$xadmin			= pg_result($res, $i, 'admin');
			$nome_usuario	= pg_result($res, $i, 'nome_usuario');
			$nome_completo	= pg_result($res, $i, 'nome_completo');
			$interacoes		= pg_result($res, $i, 'interacoes');
			$sql = "SELECT COUNT(tbl_hd_chamado.hd_chamado) AS chamados
					FROM tbl_hd_chamado
					WHERE 1=1
					AND tbl_hd_chamado.fabrica_responsavel = $login_fabrica
					AND tbl_hd_chamado.data between '$xdata_inicial' and '$xdata_final'
					AND admin = $xadmin";
			$res_chamados 	= pg_query($con, $sql);
			$chamados	 	= pg_result($res_chamados, 0, 0);
			$aInteracoes    = array();
			foreach ($aStatusInteracoes as $xstatus) {
				$sql = "SELECT COUNT(tbl_hd_chamado_item.hd_chamado_item) as interacoes
						FROM tbl_hd_chamado
						JOIN tbl_hd_chamado_extra USING (hd_chamado)
						JOIN tbl_hd_chamado_item USING (hd_chamado)
						WHERE 1=1
						AND tbl_hd_chamado_item.data between '$xdata_inicial' and '$xdata_final'
						AND tbl_hd_chamado.fabrica_responsavel = {$login_fabrica}
						AND tbl_hd_chamado_item.status_item  ILIKE '{$xstatus}'
						AND tbl_hd_chamado_item.admin = {$xadmin}
						AND tbl_hd_chamado_item.interno is FALSE";
				$res2= pg_query($con,$sql);
				$aInteracoes[$xstatus] = 0;
				if ( is_resource($res2) && pg_num_rows($res2) > 0 ) {
					$aInteracoes[$xstatus] = pg_result($res2,0,0);
				}
			}
			$linha_css 		   = "linha" . $i % 2;
			
			// Lenoxx HD 155210 - Augusto
			$interacoes_total = $interacoes;
			if ( $login_fabrica == 11) { 
				// Nao exibir a interacao de abertura do chamado como interacao valida
				$interacoes_total = $interacoes_aberto;
			}
			$xtotal = 0;
?>
			<tr class="<?php echo $linha_css; ?>"> 
				<td><?php echo $nome_usuario; ?></td>
				<td><?php echo $nome_completo; ?></td>
				<td><?php echo $chamados; ?></td>
				<?php foreach ($aInteracoes as $xinteracao): ?>
					<?php $xtotal += $xinteracao; ?>
					<td><?php echo ($xinteracao>0)?$xinteracao:'&nbsp;'; ?></td>
				<?php endforeach; ?>
				<td><?php echo $xtotal; ?></td>
			</tr>
<?php
		}
		}
		else{
			echo "<center>Nenhum Resultado Encontrado</center>";
		}
	}

	
}

?>

<? include "rodape.php" ?>
