<?php

$ip        = getenv ("REMOTE_ADDR");
$programa  = $_SERVER["SCRIPT_FILENAME"] ;

require_once "/etc/telecontrol_bc_teste.cfg";
// error_reporting (E_ERROR2); ---> E_ERROR2 ???
if (isset($_SERVER['SERVER_ADDR']))
{
	$server_addr = $_SERVER['SERVER_ADDR'];
}else{
	$server_addr = "0.0.0.0";
}


#if ($server_addr == "201.62.87.10") {
#if ($server_addr == "54.207.55.71") {
#if ($server_addr == "54.232.125.171") {
if ($server_addr == "191.5.166.42") {
	error_reporting(E_ALL & ~E_NOTICE);
} else {
	error_reporting(E_ERROR);
}

/*
echo "<CENTER><h1>ATEN��O</h1>";
echo "<h3>O sistema passar� por manuten��o t�cnica</h3";
echo "<h3>Dentro de algumas horas ser� restabelecido</h3";
echo "<h3> </h3";
echo "<p><h3>Agradecemos a compreens�o!</h3>";
exit;
*/

?>
