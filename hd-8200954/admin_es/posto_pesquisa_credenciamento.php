<?php
include "dbconfig.php";
include "includes/dbconnect-inc.php";
include 'autentica_admin.php';

include 'cabecalho_pop_postos.php';
?>

<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> Pesquisa Postos... </title>
<meta name="Author" content="">
<meta name="Keywords" content="">
<meta name="Description" content="">
<meta http-equiv=pragma content=no-cache>


	<link href="css/estilo_cad_prod.css" rel="stylesheet" type="text/css" />
	<link href="css/posicionamento.css" rel="stylesheet" type="text/css" />

</head>

<body onblur="setTimeout('window.close()',2500);" topmargin='0' leftmargin='0' marginwidth='0' marginheight='0'>

<br>

<?
$tipo = trim (strtolower ($_GET['tipo']));
if ($tipo == "nome") {
	$nome = trim (strtoupper($_GET["campo"]));
	
	echo "<font face='Arial, Verdana, Times, Sans' size='2'>Buscando por <b>nombre del servicio:</b> <i>$nome</i></font>";
	echo "<p>";
	
	$sql = "SELECT   tbl_posto.*, tbl_posto_fabrica.codigo_posto
			FROM     tbl_posto
			JOIN     tbl_posto_fabrica USING (posto)
			WHERE    tbl_posto.nome ilike '%$nome%'
			AND      tbl_posto_fabrica.fabrica = $login_fabrica
			AND      tbl_posto.pais = '$login_pais'
			ORDER BY tbl_posto.nome";
	$res = pg_exec ($con,$sql);
	
	if (@pg_numrows ($res) == 0) {
		echo "<h1>Posto '$nome' n�o encontrado</h1>";
		echo "<script language='javascript'>";
		echo "setTimeout('window.close()',2500);";
		echo "</script>";
		exit;
	}
}


if ($tipo == "codigo") {
	$codigo_posto = trim (strtoupper($_GET["campo"]));
	$codigo_posto = str_replace (".","",$codigo_posto);
	$codigo_posto = str_replace (",","",$codigo_posto);
	$codigo_posto = str_replace ("-","",$codigo_posto);
	$codigo_posto = str_replace ("/","",$codigo_posto);

	echo "<font face='Arial, Verdana, Times, Sans' size='2'>Buscando por <b>Identificaci�n del servicio:</b> <i>$codigo_posto</i></font>";
	echo "<p>";
	
	$sql = "SELECT   tbl_posto.*, tbl_posto_fabrica.codigo_posto
			FROM     tbl_posto
			JOIN     tbl_posto_fabrica USING (posto)
			WHERE    tbl_posto_fabrica.codigo_posto like '%$codigo_posto%'
			AND      tbl_posto_fabrica.fabrica = $login_fabrica
			AND      tbl_posto.pais = '$login_pais'
			ORDER BY tbl_posto.nome";

	$res = pg_exec ($con,$sql);
	
	if (@pg_numrows ($res) == 0) {
		echo "<h1>Servicio '$codigo_posto' no encuentrado</h1>";
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
	
	echo "<table width='100%' border='0'>\n";
	
	for ( $i = 0 ; $i < pg_numrows ($res) ; $i++ ) {
		$codigo_posto=trim(pg_result($res,$i,codigo_posto));
		$posto      = trim(pg_result($res,$i,posto));
		$nome       = trim(pg_result($res,$i,nome));
		$cnpj       = trim(pg_result($res,$i,cnpj));
		$cidade     = trim(pg_result($res,$i,cidade));
		$estado     = trim(pg_result($res,$i,estado));
		
		$nome = str_replace ('"','',$nome);



		echo "<tr>\n";
		
		echo "<td>\n";
		echo "<font face='Arial, Verdana, Times, Sans' size='-1' color='#000000'>$cnpj</font>\n";
		echo "</td>\n";
		
		echo "<td>\n";
		echo "<a href=\"javascript: nome.value = '$nome' ; codigo.value = '$codigo_posto' ; posto.value = '$posto' ; this.close() ; \" >";
		echo "<font face='Arial, Verdana, Times, Sans' size='-1' color='#0000FF'>$nome</font>\n";
		echo "</a>\n";
		echo "</td>\n";
		
		echo "<td>\n";
		echo "<font face='Arial, Verdana, Times, Sans' size='-1' color='#000000'>$cidade</font>\n";
		echo "</td>\n";
		
		echo "<td>\n";
		echo "<font face='Arial, Verdana, Times, Sans' size='-1' color='#000000'>$estado</font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
	}
	echo "</table>\n";
?>

</body>
</html>
