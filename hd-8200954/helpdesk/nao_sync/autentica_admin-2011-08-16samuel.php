<?
$cook_fabrica    = $HTTP_COOKIE_VARS['cook_fabrica'];
$cook_admin      = $HTTP_COOKIE_VARS['cook_admin'];
$cook_admin_es   = $HTTP_COOKIE_VARS['cook_admin_es'];

#  20/2/2009 MLG - Adicionado para a Olivier Joias, n�o funciona o setcookie desde outro domain...
if (strlen ($cook_admin) == 0 and $_GET['user']=='1599' and $_GET['f']=='78' and $_GET['admin']=='olivier') {
	setcookie('cook_fabrica',$_GET['f']  , time() + 28800, "/");
	setcookie('cook_admin', $_GET['user'], time() + 28800, "/");
	header ("Location: $PHP_SELF");
	exit;
}#	fim

if (strlen ($cook_admin) == 0 and $_GET['user']=='3950' and $_GET['f']=='107' and $_GET['admin']=='orbis') {
	setcookie('cook_fabrica',$_GET['f']  , time() + 28800, "/");
	setcookie('cook_admin', $_GET['user'], time() + 28800, "/");
	header ("Location: $PHP_SELF");
	exit;
}#	fim

if(strlen($cook_admin_es)>0)$cook_admin = $cook_admin_es;

if (strlen ($cook_admin) == 0) {
	/* HD 20640 */
	$cook_bosch = $HTTP_COOKIE_VARS['cook_bosch'];
	if (strlen($cook_bosch)>0){
		header ("Location: ../bosch.php");
	}else{
		header ("Location: ../index.php");
	}
	exit;
}
//if ($ip <> "201.0.9.216" and $cook_fabrica == 3) {
//	header ("Location: ../index.php");
//	exit;
//}

$sql = "SELECT 	tbl_admin.admin                             ,
				tbl_admin.fabrica                           ,
				tbl_admin.login                             ,
				tbl_admin.senha                             ,
				tbl_admin.privilegios                       ,
				tbl_admin.pais                              ,
				tbl_fabrica.nome as fabrica_nome            ,
				tbl_fabrica.logo AS fabrica_logo            ,
				tbl_fabrica.site AS fabrica_site            ,
				tbl_fabrica.multimarca                      ,
				tbl_fabrica.acrescimo_tabela_base           ,
				tbl_fabrica.acrescimo_financeiro            ,
				tbl_fabrica.pedir_causa_defeito_os_item     ,
				tbl_fabrica.pedir_defeito_constatado_os_item,
				tbl_fabrica.pedir_solucao_os_item
		FROM    tbl_admin
		JOIN    tbl_fabrica ON tbl_fabrica.fabrica = tbl_admin.fabrica
		WHERE   tbl_admin.admin   = $cook_admin
		AND     tbl_admin.fabrica = $cook_fabrica";
$res = @pg_exec ($con,$sql);
//if ($ip == "187.39.215.9") {
//	echo $sql;
//	exit;
//}

if (@pg_numrows ($res) == 0) {
	/* HD 20640 */
	$cook_bosch = $HTTP_COOKIE_VARS['cook_bosch'];
	if (strlen($cook_bosch)>0){
		header ("Location: ../bosch.php");
	}else{
		header ("Location: ../index.php");
	}
	exit;
}


global $login_admin;
global $login_login;
global $login_fabrica;
global $login_pais;
global $login_privilegios;
global $login_fabrica_nome;
global $login_fabrica_logo;
global $login_fabrica_site;
global $multimarca;
global $acrescimo_tabela_base;
global $acrescimo_financeiro;
global $pedir_causa_defeito_os_item;
global $pedir_defeito_constatado_os_item;
global $pedir_solucao_os_item;
global $sistema_lingua;

$login_admin                       = trim (pg_result ($res,0,admin));
$login_login                       = trim (pg_result ($res,0,login));
$login_fabrica                     = trim (pg_result ($res,0,fabrica));
$login_pais                        = trim (pg_result ($res,0,pais));
$login_privilegios                 = trim (pg_result ($res,0,privilegios));
$login_fabrica_nome                = trim (pg_result ($res,0,fabrica_nome));
$login_fabrica_logo                = trim (pg_result ($res,0,fabrica_logo));
$login_fabrica_site                = trim (pg_result ($res,0,fabrica_site));
$multimarca                        = trim (pg_result ($res,0,multimarca));
$acrescimo_tabela_base             = trim (pg_result ($res,0,acrescimo_tabela_base));
$acrescimo_financeiro              = trim (pg_result ($res,0,acrescimo_financeiro));
$pedir_causa_defeito_os_item       = trim (pg_result ($res,0,pedir_causa_defeito_os_item));
$pedir_defeito_constatado_os_item  = trim (pg_result ($res,0,pedir_defeito_constatado_os_item));
$pedir_solucao_os_item             = trim (pg_result ($res,0,pedir_solucao_os_item));



if($login_pais<>'BR' and (strlen($login_pais)==2 or $login_fabrica==20)) $sistema_lingua = 'ES';
if($login_fabrica<>'20'){
	$sistema_lingua = '';
	$login_pais     = '';
}



if (strlen ($admin_privilegios) > 0) {
	$admin_autorizado = 0;
	$array_privilegios = split (",",$admin_privilegios);
	for ($i = 0 ; $i < count($array_privilegios) ; $i++) {
		$cabecalho_privilegio = $array_privilegios[$i];
		if (strpos ($login_privilegios , trim($cabecalho_privilegio)) !== false) {
			$admin_autorizado = 1;
		}
	}

	if (strpos ($login_privilegios,"*") !== false) {
		$admin_autorizado = 1;
	}

	if ($admin_autorizado == 0) {
		$title = "MENU GER�NCIA";
		$layout_menu = "gerencia";
		include 'cabecalho.php';
		echo "<p><hr><center><h1>Sem permiss�o para acessar este programa</h1></center><p><hr>";
		exit;
	}
}




$sql = "/* PROGRAMA $PHP_SELF  #   FABRICA $login_fabrica   #  ADMIN $login_admin */";
$resX = @pg_exec ($con,$sql);



?>
