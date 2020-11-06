<?

include "autentica_validade_senha.php";

#header("Expires: 0");
#header("Cache-Control: no-cache, public, must-revalidate, post-check=0, pre-check=0");
#header("Pragma: no-cache, public");

/*$sql = "SELECT tbl_fabrica.multimarca,
				tbl_fabrica.acrescimo_tabela_base
		FROM   tbl_fabrica
		WHERE  tbl_fabrica.fabrica = $login_fabrica
		AND    tbl_fabrica.multimarca is true
		AND    tbl_fabrica.acrescimo_tabela_base is true;";
$res = pg_exec ($con,$sql);

if (pg_numrows ($res) > 0){
	$multimarca            = trim(pg_result($res,0,multimarca));
	$acrescimo_tabela_base = trim(pg_result($res,0,acrescimo_tabela_base));
}*/

function getmicrotime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

function TempoExec($pagina, $sql, $time_start, $time_end){
	if (1 == 1){
		$time = $time_end - $time_start;
		$time = str_replace ('.',',',$time);
		$sql  = str_replace ('\t',' ',$sql);
#		$fp = fopen ("/home/telecontrol/tmp/postgres.log","a");
#		fputs ($fp,$pagina);
#		fputs ($fp,"#");
#		fputs ($fp,$sql);
#		fputs ($fp,"#");
#		fputs ($fp,$time);
#		fputs ($fp,"\n");
#		fclose ($fp);
	}
}

$micro_time_start = getmicrotime();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<!-- AQUI COME�A O HTML DO MENU -->

<head>

	<title><? echo $title ?></title>

	<meta http-equiv="content-Type"  content="text/html; charset=iso-8859-1">
	<meta http-equiv="Expires"       content="0">
	<meta http-equiv="Pragma"        content="no-cache, public">
	<meta http-equiv="Cache-control" content="no-cache, public, must-revalidate, post-check=0, pre-check=0">
	<meta name      ="Author"        content="Telecontrol Networking Ltda">
	<meta name      ="Generator"     content="na m�o...">
	<meta name      ="Description"   content="Sistema de gerenciamento para Postos de Assist�ncia T�cnica e Fabricantes.">
	<meta name      ="KeyWords"      content="Servicio t�cnico, Servicios, manutenci�n, Internet, webdesign, orcamiento, comercial, joias, call center">

	<link type="text/css" rel="stylesheet" href="css/css.css">
</head>

<script>
/*****************************************************************
Nome da Fun��o : displayText
		Apresenta em um campo as informa��es de ajuda de onde 
		o cursor estiver posicionado.
******************************************************************/
	function displayText( sText ) {
		document.getElementById("displayArea").innerHTML = sText;
	}

</script>

<script language="javascript" src="js/assist.js"></script>



<body bgcolor='#ffffff' marginwidth='2' marginheight='2' topmargin='2' leftmargin='2' >


<?


if($login_pais=='AR') $bandeira = 'bandeira-argentina.gif';
if($login_pais=='CO') $bandeira = 'bandeira-colombia.gif' ;
if($login_pais=='UY') $bandeira = 'bandeira-uruguay.gif'  ;
if($login_pais=='MX') $bandeira = 'bandeira-mexico.gif'   ;
if($login_pais=='CL') $bandeira = 'bandeira-chile.gif'    ;
if($login_pais=='VE') $bandeira = 'bandeira-venezuela.gif';

if ($sem_menu == false OR strlen ($sem_menu) == 0 ) {
	echo "<table border='0' cellpadding='0' cellspacing='0' background='/assist/imagens/fundo-cabecalho.png'  align = 'center'>";
	echo "<tr>";
	echo "<td width='100'><img src='/assist/imagens/pixel.gif' width='30' height='1'></td>";
	echo "<td width='100%' align='center' valign='top'>";


	switch ($layout_menu) {
	case "gerencia":
		echo "<img src='imagens_admin/btn_gerencia.gif' usemap='#menu_map'>";
		break;
	case "cadastro":
		echo "<img src='imagens_admin/btn_cadastro.gif' usemap='#menu_map'>";
		break;
	case "tecnica":
		echo "<img src='imagens_admin/btn_tecnica.gif' usemap='#menu_map'>";
		break;
	case "financeiro":
		echo "<img src='imagens_admin/btn_financeiro.gif' usemap='#menu_map'>";
		break;
	case "auditoria":
		echo "<img src='imagens_admin/btn_auditoria.gif' usemap='#menu_map'>";
		break;
	default:
		echo "<img src='imagens_admin/btn_gerencia.gif' usemap='#menu_map'>";
		break;
	}
	#echo "<img src='../imagens/$bandeira'>";
	echo "</td>";

	if ($login_fabrica == "10") $prefixo = 'adm_';

/*	$sql  = "SELECT COUNT(*) FROM (
				SELECT tbl_hd_chamado.admin , (SELECT admin FROM tbl_hd_chamado_item WHERE hd_chamado = tbl_hd_chamado.hd_chamado ORDER BY hd_chamado_item DESC LIMIT 1) AS admin_item 
				FROM tbl_hd_chamado WHERE admin = $login_admin
			) As help WHERE admin <> admin_item ";
*/
	$sql = "SELECT COUNT (*) FROM (
				SELECT tbl_hd_chamado.hd_chamado, tbl_hd_chamado.status ,tbl_hd_chamado.admin , 
					(SELECT tbl_hd_chamado_item.admin 
					FROM tbl_hd_chamado_item 
					JOIN tbl_hd_chamado using(hd_chamado)
					WHERE tbl_hd_chamado_item.hd_chamado = tbl_hd_chamado.hd_chamado 
					ORDER BY hd_chamado_item DESC LIMIT 1) AS admin_item 
				FROM tbl_hd_chamado 
				WHERE admin = $login_admin and upper(status) <> 'RESOLVIDO'
			) As help WHERE admin <> admin_item";
	$sql = "SELECT count(*) 
			FROM tbl_hd_chamado 
			WHERE admin = $login_admin 
			AND (
				(exigir_resposta is TRUE and status<>'Resolvido')
				OR 
				(resolvido is null and status='Resolvido')
				) 
			AND status<>'Cancelado';";
	$resX = pg_exec ($con,$sql);
	$qtde_help = pg_result ($resX,0,0);
	if ($qtde_help == 0 OR strlen ($qtde_help) == 0) {
		echo "<td width='100' align='center' valign='top'><a href='/assist/helpdesk/".$prefixo."chamado_detalhe.php' target='_blank'><img src='/assist/helpdesk/imagem/help.png' width='30' alt='Sistema de HelpDesk TELECONTROL' border='0'></a></td>";
	}else{
		if ($qtde_help == 1) { 
			$msg_help = "Usted tiene $qtde_help llamado pendiente, aguardando su respuesta" ; 
		}else{
			$msg_help = "Usted tiene $qtde_help llamados pendientes, aguardando su respuesta" ;
		}
		echo "<td width='100' align='center' valign='top'><a href='/assist/helpdesk/".$prefixo."chamado_lista.php' target='_blank'><img src='/assist/helpdesk/imagem/help-vermelho.gif' width='30' alt='$msg_help' border='0'></a></td>";
	}

	echo "</tr>";
	echo "</table>";

	echo "<table border='0' cellpadding='0' cellspacing='0' background='/assist/imagens/submenu_fundo_cinza.gif'  align = 'center'>";
	echo "<tr>";
	echo "<td width='100'><img src='/assist/imagens/pixel.gif' width='30' height='1'></td>";
	echo "<td width='100%' align='center'>";

	switch ($layout_menu) {
	case "gerencia":
		include 'submenu_gerencia.php';
		break;
	case "cadastro":
		include 'submenu_cadastro.php';
		break;
	case "tecnica":
		include 'submenu_tecnica.php';
		break;
	case "financeiro":
		include 'submenu_financeiro.php';
		break;
	case "auditoria":
		include 'submenu_auditoria.php';
		break;
	default:
		include 'submenu_gerencia.php';
		break;
	}
	echo "</td>";

	echo "<td width='100'><img src='/assist/imagens/pixel.gif' width='30' height='1'></td>";

	
	echo "</table>";

	echo "
	<map name='menu_map'>
	<area shape='rect' coords='014,0,090,24' href='menu_gerencia.php'>
	<area shape='rect' coords='100,0,176,24' href='menu_cadastro.php'>
	<area shape='rect' coords='190,0,263,24' href='menu_tecnica.php'>
	<area shape='rect' coords='276,0,353,24' href='menu_financeiro.php'>
	<area shape='rect' coords='362,0,439,24' href='menu_auditoria.php'>
	<area shape='rect' coords='450,0,527,24' href='http://www.bosch.com.br/assist'>
	</map>";

}

?>
<!------------------AQUI COME�A O SUB MENU ---------------------!-->
<TABLE width="700px" border="0" cellspacing="0" cellpadding="0" bgcolor='#D9E2EF'  align = 'center'>
<TR>
<TD width='10'><IMG src="imagens/corner_se_laranja.gif"></TD>
<TD style='font-size: 14px; font-weight: bold; font-family: arial;'> <? echo "$title" ?> </TD>
<TD width='10'><IMG src="imagens/corner_sd_laranja.gif"></TD>
</TR>
</TABLE>


<TABLE width="700px" border="2" align="center" cellpadding='0' cellspacing='0' bordercolor='#d9e2ef'>
<tr>
	<td>
		<?
			echo "<a href='$login_fabrica_site' target='_new'>";
			if ($login_login == 'suggar') {
			    echo "<img src='/assist/logos/suggar.jpg' alt='$login_fabrica_site' border='0' height='40'>";
			}elseif ($login_login == 'tulio') {
			    echo "<img src='/assist/logos/telecontrol.jpg' alt='$login_fabrica_site' border='0' height='40'>";
			}else{
			    echo "<img src='/assist/logos/$login_fabrica_logo' alt='$login_fabrica_site' border='0' height='40'>";
			}
			echo "</a>";
		?>
	</td>
<?
function escreveData($data) { 
	$vardia = substr($data,8,2);
	$varmes = substr($data,5,2);
	$varano = substr($data,0,4);

	$convertedia = date ("w", mktime (0,0,0,$varmes,$vardia,$varano)); 

	$diaSemana = array("Domingo", "Lunes", "Martes", "Mi�rcoles", "Jueves", "Viernes", "S�bado"); 

	$mes = array(1=>"enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"); 

	if ($varmes < 10) $varmes = substr($varmes,1,1);

	return $diaSemana[$convertedia] . ", " . $vardia  . " de " . $mes[$varmes] . " de " . $varano; 
} 
// Utilizar da seguinte maneira 
//echo escreveData("2005-12-02"); 
?> 
	<td style='font-size: 14px; font-weight: bold; font-family: arial;'>
	<? 
		$data = date("Y-m-d");
		echo escreveData($data);
		echo date(" - H:i");
		echo " / Usu�rio: ".ucfirst($login_login);
	?>
	</td>
	<td>
	<? 
	 //----INICIO HELP----//
	 $local = $PHP_SELF;

		$sql = "SELECT * from tbl_help";
		$res = pg_exec ($con,$sql);

		if (@pg_numrows($res) >= 0) {
			for ($i = 0 ; $i < pg_numrows ($res) ; $i++) {
				$programa       = pg_result($res,$i,programa);
				$help           = pg_result($res,$i,help);
			
				$pos = strpos($local, $programa);
				if ($pos == true) {
//				echo"$programa<BR>";
				echo"
				<SCRIPT LANGUAGE='JavaScript'>
				function ajuda()
				{
				window.open('help.php?programa=$programa', 'ouverture', 'toolbar=no, status=yes, scrollbars=yes, resizable=no, width=400, height=500');
				}
				//-->
				</SCRIPT>";
				echo"<A HREF='#' ONCLICK='ajuda()'>
				<img src='imagens/help.jpg' alt='Click aqu� para obtener ayuda'>";

				echo "</A>";
				}
			}
		}
	//----FIM HELP----//
	?>
	
	</td>
</tr>
<tr>
	<td colspan=3><div class="frm-on-os" id="displayArea">&nbsp;</div></td>
</tr>

<?
	if(strlen($msg_validade_cadastro)>0){
	echo "<tr>";
	echo "<td align='center' colspan=3>$msg_validade_cadastro</td>";
	echo "</tr>";

} ?>
</TABLE>

<?
if ($login_login <> 'suggar' and $login_login <> 'rui') {
?>
<table width='500' align='center' border='0'>
<tr>
	<td valign='middle'>
		<a href='/assist/helpdesk/<?=$prefixo?>chamado_lista.php' target='_blank'>
		<img src='/assist/helpdesk/imagem/help.png' border='0' valign='center'>
		</a>
	</td>

	<td valign='middle'>
		<font face='arial' color='#666666'>Utilice el nuevo sistema de Help-Desk para administrar sus llamados junto a Telecontrol</font><br>
		<?		if ($qtde_help == 1) { 
					echo  "<FONT SIZE='1' COLOR='#FF0000'>Usted tiene $qtde_help llamado pendiente, aguardando su respuesta</FONT>" ; 
				}elseIF($qtde_help > 0){
					echo "<FONT SIZE='1' COLOR='#FF0000'>Usted tiene $qtde_help chamados pendientes, aguardando su respuesta</FONT>" ;
				}
		?>
	</td>
	
	</tr>

</table>

<?
}
?>
 




<?
#------------- Programa Restrito ------------------#
$sql = "SELECT * FROM tbl_admin WHERE admin = $login_admin AND privilegios NOT ILIKE '%*%' ";
$res = pg_exec ($con,$sql);
if (pg_numrows ($res) > 0) {
	$sql = "SELECT *
			FROM   tbl_programa_restrito
			WHERE  tbl_programa_restrito.programa = '$PHP_SELF'
			AND    tbl_programa_restrito.fabrica = $login_fabrica";
	$res = pg_exec ($con,$sql);
	
	if (pg_numrows ($res) > 0) {
		$sql = "SELECT *
				FROM   tbl_programa_restrito
				JOIN   tbl_admin USING (admin)
				WHERE  tbl_programa_restrito.programa = '$PHP_SELF'
				AND    tbl_programa_restrito.admin    = $login_admin
				AND    tbl_programa_restrito.fabrica  = $login_fabrica ";
		$res = pg_exec ($con,$sql);
		
		if (pg_numrows ($res) == 0) {
			echo "<p><hr><center><h1>Sin permiso para acceder esse programa.</h1></center><p><hr>";
			exit;
		}
	}
}
//echo "<!-- restricao \n $sql -->";

?>
