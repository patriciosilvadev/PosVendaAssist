<?
$cookie_login = get_cookie_login($_COOKIE['sess']);
$cook_fabrica = $cookie_login['cook_fabrica'];
$cook_admin   = $cookie_login['cook_admin_es'];

if (strlen ($cook_admin) == 0) {
	header ("Location: ../index.php");
	exit;
}


//if ($ip <> "201.0.9.216" and $cook_fabrica == 3) {
//	header ("Location: ../index.php");
//	exit;
//}


$sql = "SELECT  tbl_admin.admin                             ,
				tbl_admin.fabrica                           ,
				tbl_admin.login                             ,
				tbl_admin.senha                             ,
				tbl_admin.pais                              ,
				tbl_admin.privilegios                       ,
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

if (@pg_numrows ($res) == 0) {
	/* HD 20640 */
	$cook_bosch = $cookie_login['cook_bosch'];
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
global $login_idioma;
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


$sistema_lingua = 'BR';
if( $login_pais <> 'BR' and strlen ($login_pais) == 2 ) $sistema_lingua = 'ES';

$login_idioma = $sistema_lingua ;


if (strlen ($admin_privilegios) > 0) {
	$admin_autorizado = 0;
	$array_privilegios = explode (",",$admin_privilegios);
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
		$title = "MENU GESTI�N";
		$layout_menu = "gerencia";
		include 'cabecalho.php';
		echo "<p><hr><center><h1>Sin autorizaci�n para acceder a este programa</h1></center><p><hr>";
		exit;
	}
}



$sql = "/* PROGRAMA $PHP_SELF  #   FABRICA $login_fabrica   #  ADMIN $login_admin */";
$resX = @pg_exec ($con,$sql);

$S3_sdk_OK = file_exists('/aws-amazon/sdk/sdk.class.php');
include_once '../class/aws/s3_config.php';
//
// C�digo que verifica o campo parametros_adicionais na tbl_f�brica e que monta as vari�veis de verifica��o
$sql = "SELECT parametros_adicionais FROM tbl_fabrica WHERE fabrica = $login_fabrica AND parametros_adicionais IS NOT NULL ";
$res = pg_query($con,$sql);

if (pg_num_rows($res) > 0) {
    $parametros_adicionais = json_decode(pg_fetch_result($res, 0, 'parametros_adicionais'), true); // true para retornar ARRAY e n�o OBJETO
    extract($parametros_adicionais); // igual o foreach, mais eficiente (processo interno do PHP)
}

/**
 * 31/08/2015 MLG
 * F�bricas que t�m habilitado o HD Posto
 * Movido aos autentica_*, pois � usando em pelo menos 6 scripts.
 * Se alterar, tem que alterar no autentica_usuario e no autentica_admin
 **/
$fabrica_hd_posto = array(1,3,11,42,151);
$fabrica_at_regiao = array(1, 151); // F�bricas que t�m atendentes para diferentes regi�es (cidade ou UF)

