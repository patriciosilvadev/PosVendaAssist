<?php
include "dbconfig.php";
include "includes/dbconnect-inc.php";
include 'autentica_admin.php';

include 'cabecalho_pop_pecas.php';
?>

<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> Pesquisa Pe�as... </title>
<meta name="Author" content="">
<meta name="Keywords" content="">
<meta name="Description" content="">
<meta http-equiv=pragma content=no-cache>

	<link href="css/estilo_cad_prod.css" rel="stylesheet" type="text/css" />
	<link href="css/posicionamento.css" rel="stylesheet" type="text/css" />

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

table.tabela tr td{
	font-family: verdana;
	font: bold 11px "Arial";
	border-collapse: collapse;
	border:1px solid #596d9b;
}


</style>
</head>

<body style="margin: 0px 0px 0px 0px;" onblur="setTimeout('window.close()',2500);">

<br>

<?
$tipo = trim (strtolower ($_GET['tipo']));
if ($tipo == "descricao") {

	$descricao = trim (strtoupper($_GET["campo"]));
	//echo "<font face='Arial, Verdana, Times, Sans' size='2'>Pesquisando por <b>descri��o da pe�a</b>: <i>$descricao</i></font>";
	//echo "<p>";
	
	$sql = "SELECT  tbl_peca.peca,
					tbl_peca.referencia,
					tbl_peca.descricao,
					tbl_peca.ipi,
					tbl_peca.origem,
					tbl_peca.estoque,
					tbl_peca.unidade,
					tbl_peca.ativo
			FROM     tbl_peca
			JOIN     tbl_fabrica ON tbl_fabrica.fabrica = tbl_peca.fabrica
			WHERE    tbl_peca.descricao ilike '%$descricao%'
			AND      tbl_peca.fabrica = $login_fabrica
			ORDER BY tbl_peca.descricao;";
	$res = pg_exec ($con,$sql);

	
	if (@pg_numrows ($res) == 0) {
		echo "<h1>Pe�a '$descricao' n�o encontrada</h1>";
		echo "<script language='javascript'>";
		echo "setTimeout('window.close()',2500);";
		echo "</script>";
		exit;
	}
}

if ($tipo == "referencia") {

	$referencia = trim (strtoupper($_GET["campo"]));
	$referencia = str_replace (".","",$referencia);
	$referencia = str_replace ("-","",$referencia);
	$referencia = str_replace ("/","",$referencia);
	$referencia = str_replace (" ","",$referencia);

	//echo "<font face='Arial, Verdana, Times, Sans' size='2'>Pesquisando por <b>refer�ncia da pe�a</b>: <i>$referencia</i></font>";
	//echo "<p>";

	//where tbl_peca.referencia_pesquisa ilike '%$referencia%'
	$sql = "SELECT  tbl_peca.peca,
					tbl_peca.referencia,
					tbl_peca.descricao,
					tbl_peca.ipi,
					tbl_peca.origem,
					tbl_peca.estoque,
					tbl_peca.unidade,
					tbl_peca.ativo
			FROM     tbl_peca
			JOIN     tbl_fabrica ON tbl_fabrica.fabrica = tbl_peca.fabrica
			WHERE    tbl_peca.referencia_pesquisa ilike '%$referencia%'
			AND      tbl_peca.fabrica = $login_fabrica
			ORDER BY tbl_peca.descricao;";
	$res = pg_exec ($con,$sql);

	if (@pg_numrows ($res) == 0) {
		echo "<h1>Pe�a '$referencia' n�o encontrada</h1>";
		echo "<script language='javascript'>";
		echo "setTimeout('window.close()',2500);";
		echo "</script>";
		exit;
	}
}

echo "<script language='JavaScript'>\n";
echo "<!--\n";
echo "this.focus();\n";
echo "// -->\n";
echo "</script>\n";

echo "<table width='100%' border='0' class='tabela' cellspacing='1'>\n";
if($tipo=="descricao")
		echo "<tr class='titulo_tabela'><td colspan='7'><font style='font-size:14px;'>Pesquisando por <b>descri��o da pe�a</b>: $descricao</b>: $nome</font></td></tr>";
	if($tipo=="referencia")
		echo "<tr class='titulo_tabela'><td colspan='7'><font style='font-size:14px;'>Pesquisando por <b>refer�ncia da pe�a</b>: $referencia</font></td></tr>";

echo "<tr class='titulo_coluna'><td>C�digo</td><td>Nome</td><td>IPI</td><td>Origem</td><td>Estoque</td><td>Unidade</td><td>&nbsp;</td>";
//if ($ip == '192.168.0.66') echo $sql."<BR>";

for ( $i = 0 ; $i < pg_numrows ($res) ; $i++ ) {
	$peca       = trim(pg_result($res,$i,peca));
	$referencia = trim(pg_result($res,$i,referencia));
	$descricao  = trim(pg_result($res,$i,descricao));
	//$ipi        = trim(pg_result($res,$i,ipi));
	//$origem     = trim(pg_result($res,$i,origem));
	//$estoque    = trim(pg_result($res,$i,estoque));
	//$unidade    = trim(pg_result($res,$i,unidade));
if ($login_fabrica == 20) {
	$ativo      = trim(pg_result($res,$i,ativo));
	
	if ($ativo == 't') {
		$ativo = 'Ativo';
	}
	else {
		$ativo = 'N�o Ativo';
	}

}
	$descricao = str_replace ('"','',$descricao);
	//$referencia = substr ($referencia,0,2) . "." . substr ($referencia,2,2) . "." . substr ($referencia,4,2) . "-" . substr ($referencia,6,1);

	if($i%2==0) $cor = "#F7F5F0"; else $cor = "#F1F4FA";
		
	echo "<tr bgcolor='$cor'>\n";
	
	echo "<td>\n";
	if ($_GET['forma'] == 'reload') {
		echo "<a href=\"javascript: opener.document.location = retorno + '?peca=$peca' ; this.close() ;\" > " ;
	}else{
		echo "<a href=\"javascript: descricao.value = '$descricao' ; referencia.value = '$referencia' ; this.close() ; \" >";
	}
	echo "$referencia\n";
	echo "</td>\n";
	
	echo "<td>\n";
	if ($_GET['forma'] == 'reload') {
		echo "<a href=\"javascript: opener.document.location = retorno + '?peca=$peca' ; this.close() ;\" > " ;
	}else{
		echo "<a href=\"javascript: descricao.value = '$descricao' ; referencia.value = '$referencia' ; this.close() ; \" >";
	}
	
	echo "$descricao\n";
	echo "</a>\n";
	echo "</td>\n";

	echo "<td>\n";
	echo "$ipi\n";
	echo "</td>\n";
	
	echo "<td>\n";
	echo "$origem\n";
	echo "</td>\n";

	echo "<td>\n";
	echo "$estoque\n";
	echo "</td>\n";

	echo "<td>\n";
	echo "$unidade\n";
	echo "</td>\n";

	echo "<td>\n";
	echo "$ativo\n";
	echo "</td>\n";
	
	echo "</tr>\n";
}
echo "</table>\n";

?>

</body>
</html>
