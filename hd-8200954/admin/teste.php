<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';

$admin_privilegios = "call_center";

$sql = "SELECT  tbl_admin.admin                             ,
				tbl_admin.fabrica                           ,
				tbl_admin.login                             ,
				tbl_admin.senha                             ,
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
		WHERE   tbl_admin.admin   = 453
		AND     tbl_admin.fabrica = 11";
$res = @pg_exec ($con,$sql);

$login_admin                       = trim (pg_result ($res,0,admin));
$login_login                       = trim (pg_result ($res,0,login));
$login_fabrica                     = trim (pg_result ($res,0,fabrica));
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

if (strlen ($admin_privilegios) > 0) {
	$admin_autorizado = 0;
	$array_privilegios = split (",",$admin_privilegios);

	for ($i = 0 ; $i < count($array_privilegios) ; $i++) {
		$cabecalho_privilegio = $array_privilegios[$i];

		echo $cabecalho_privilegio . "<br>";

		echo $login_privilegios;

		if (strpos ($login_privilegios , trim($cabecalho_privilegio)) !== false) {
			$admin_autorizado = 1;
		}
	}

	if (strpos ($login_privilegios,"*") !== false) {
		$admin_autorizado = 1;
	}

	if ($admin_autorizado == 0) {
		$title = "MENU GERÊNCIA";
		$layout_menu = "gerencia";
		include 'cabecalho.php';
		echo "<p><hr><center><h1>Sem permissão para acessar este programa</h1></center><p><hr>";
		exit;
	}
}


?>