imagem/cabecalho_tk.php                                                                             0000664 0003726 0003721 00000027456 10467106272 017457  0                                                                                                    ustar   takashi                         telecontrol                     0000000 0000000                                                                                                                                                                        <?
header("Expires: 0");
header("Cache-Control: no-cache, public, must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache, public");
// Data no passado
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
// Sempre modificado
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

// HTTP/1.0
header("Pragma: no-cache");


#echo "<h1>O site sair� do ar em 5 minutos para manuten��o</h1>";
#echo "<h1>e retornar� dentro de meia hora</h1>";
#echo "<h1>Por favor, finalize seu trabalho.</h1>";
#echo "<p><font size='+3' color='#ff0000'>Site Fora do ar em 1 minuto</font>";
 
if (trim ($login_fabrica) == 3 AND $PHP_SELF <> "/assist/perguntas_britania.php" AND $_SERVER['REMOTE_ADDR'] <> '200.198.99.102' AND $_SERVER['REMOTE_ADDR'] <> '201.0.9.216') {

	$sqlX = "SELECT tbl_linha.linha
			FROM   tbl_linha
			JOIN   tbl_posto_linha   using (linha)
			JOIN   tbl_posto_fabrica using (posto)
			WHERE  tbl_posto_fabrica.fabrica = $login_fabrica
			AND    tbl_posto_linha.posto     = $login_posto
			AND    tbl_linha.linha = 3;";
	$res = @pg_exec($con,$sqlX);

	if (@pg_numrows($res) > 0) {
		$sqlX = "SELECT ja_chegaram
				FROM   britania_fama
				WHERE  posto     = $login_posto";
		$res = @pg_exec($con,$sqlX);
		if (strlen(@pg_result($res,0,ja_chegaram)) == 0) {
			header("Location: perguntas_britania.php");
			exit;
		}
	}

}

//////////////////////////////////////////////////////////
function getmicrotime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

function TempoExec($pagina, $sql, $time_start, $time_end){
	$time = $time_end - $time_start;
	$time = str_replace ('.',',',$time);
	$sql  = str_replace ('\t',' ',$sql);
	$fp = fopen ("/home/telecontrol/tmp/postgres.log","a");
	fputs ($fp,$pagina);
	fputs ($fp,"#");
	fputs ($fp,$sql);
	fputs ($fp,"#");
	fputs ($fp,$time);
	fputs ($fp,"\n");
	fclose ($fp);
}
//////////////////////////////////////////////////////////

$micro_time_start = getmicrotime();
?>

<html>

<head>

	<title><? echo $title ?></title>

	<meta http-equiv="content-Type"  content="text/html; charset=iso-8859-1">
	<meta http-equiv="Expires"       content="0">
	<meta http-equiv="Pragma"        content="no-cache, public">
	<meta http-equiv="Cache-control" content="no-cache, public, must-revalidate, post-check=0, pre-check=0">
	<meta name      ="Author"        content="Telecontrol Networking Ltda">
	<meta name      ="Generator"     content="na m�o...">
	<meta name      ="Description"   content="Sistema de gerenciamento para Postos de Assist�ncia T�cnica e Fabricantes.">
	<meta name      ="KeyWords"      content="Assist�ncia T�cnica, Postos, Manuten��o, Internet, Webdesign, Or�amento, Comercial, J�ias, Callcenter">

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

<body>

<!--================== MENU DO SISTEMA ASSIST =======================-->
<!-- PAR�METRO A SER PASSADO $layout_menu  "passa a op��o em destaque-->

<div id="menu"> 
	<p>
	<?
		/*
	switch ($layout_menu) {

/*--================== $layout_menu = os =======================-*/
	/*case "os":
                if ($login_fabrica == 1){
			echo "<img src='imagens/btn_os_bd.gif' usemap='#menu_map'>";
                }else if(($login_fabrica==20)OR ($login_fabrica==19)){
                 echo "<img src='imagens/btn_os-bosch.gif' usemap='#menu_map'>";
                }else{
                echo "<img src='imagens/btn_os.gif' usemap='#menu_map'>";
                }
		include 'submenu_os.php';
		break;

/*--================== $layout_menu = preco ====================-*/
	/*case "preco":
                if ($login_fabrica == 1){
			echo "<img src='imagens/btn_preco_bd.gif' usemap='#menu_map'>";
                }else{
			echo "<img src='imagens/btn_preco.gif' usemap='#menu_map'>";
                }
		include 'submenu_preco.php';
		break;

/*--================== $layout_menu = pedido ===================-*/
/*	case "pedido":
                if ($login_fabrica == 1){
			echo "<img src='imagens/btn_pedidos_bd.gif' usemap='#menu_map'>";
                }else{
        		echo "<img src='imagens/btn_pedidos.gif' usemap='#menu_map'>";
                }
                include 'submenu_pedido.php';
		break;

/*--================== $layout_menu = tecnica ===================-*/
 /*	case "tecnica":
                if ($login_fabrica == 1){
			echo "<img src='imagens/btn_tecnica_bd.gif' usemap='#menu_map'>";
                }else if(($login_fabrica==20)OR ($login_fabrica==19)){
                        echo "<img src='imagens/btn_tecnica-bosch.gif' usemap='#menu_map'>";
                }else{
			echo "<img src='imagens/btn_tecnica.gif' usemap='#menu_map'>";
                }
                include 'submenu_tecnica.php';
		break;

/*--================== $layout_menu = cadastro =================-*/
	/*case "cadastro":
                if ($login_fabrica == 1){
			echo "<img src='imagens/btn_cadastro_bd.gif' usemap='#menu_map'>";
                }else if(($login_fabrica==20)OR ($login_fabrica==19)){
                         echo "<img src='imagens/btn_cadastro-bosch.gif' usemap='#menu_map'>";
                }else{
			echo "<img src='imagens/btn_cadastro.gif' usemap='#menu_map'>";
                }
                include 'submenu_cadastro.php';
		break;

/*--================== $layout_menu = procedimento =======================-*/
/*	case "procedimento":
                if ($login_fabrica == 1){
			echo "<img src='imagens/btn_procedimento_bd.gif' usemap='#menu_map'>";
                }else{
			echo "<img src='imagens/btn_tecnica.gif' usemap='#menu_map'>";
                }
                include 'submenu_tecnica.php';
		break;

/*--================== $layout_menu = padrao =======================-*/
/*	default:
                if ($login_fabrica == 1){
			echo "<img src='imagens/btn_os_bd.gif' usemap='#menu_map'>";
                }else if(($login_fabrica==20)OR ($login_fabrica==19)){
                        echo "<img src='imagens/btn_os-bosch.gif' usemap='#menu_map'>";
                }else{
			echo "<img src='imagens/btn_os.gif' usemap='#menu_map'>";
                }
                break;
	}
	
	?>

<!--============== MAPA DE IMAGEM DA BARRA DE MENU ============-->
                <?
                if (($login_fabrica==20)OR ($login_fabrica==19)){ ?>
                <map name="menu_map">
                <area shape="rect" coords="014,0,090,24" href="menu_os.php">
                <area shape="rect" coords="100,0,176,24" href="menu_tecnica.php">
                <area shape="rect" coords="190,0,263,24" href="menu_cadastro.php">
                <area shape="rect" coords="541,0,622,24" href="http://www.telecontrol.com.br/assist">
                </map>
                <?    }else{ ?>
                <map name="menu_map">
                <area shape="rect" coords="014,0,090,24" href="menu_os.php">
                <area shape="rect" coords="100,0,176,24" href="menu_preco.php">
                <area shape="rect" coords="190,0,263,24" href="menu_pedido.php">
                <area shape="rect" coords="276,0,353,24" href="menu_tecnica.php">
                <area shape='rect' coords='362,0,439,24' href='menu_cadastro.php'>
                <? if ($login_fabrica == 1){ ?>
                <area shape="rect" coords="450,0,527,24" href="procedimento_mostra.php"><? } ?>
                <area shape="rect" coords="541,0,622,24" href="http://www.telecontrol.com.br/assist">
                </map>
		<?  } */?>
                
                
                
</div>
<TABLE width="750px" border="1" cellspacing="0" cellpadding="0" align="center">
<tr>
<td>a</td>
<td>b</td>
<td>c</td>
<td>d</td>
<td>e</td>
<td>f</td>
<td>g</td>
<td>h</td>
</tr>
</table>
		
		
		

<!------------------AQUI COME�A O SUB MENU ---------------------!-->
<TABLE width="700px" border="0" cellspacing="0" cellpadding="0" bgcolor='#D9E2EF' align="center">
<TR> 
  <TD width='10'><IMG src="imagens/corner_se_laranja.gif"></TD>
  <TD style='font-size: 14px; font-weight: bold; font-family: arial;'> <? echo "$title" ?> </TD>
  <TD width='10'><IMG src="imagens/corner_sd_laranja.gif"></TD>
</TR>
</TABLE>


<TABLE width="700px" border="2"  cellpadding='0' cellspacing='0' bordercolor='#d9e2ef' align="center">
<tr>
<?
function escreveData($data) { 
	$vardia = substr($data,8,2);
	$varmes = substr($data,5,2);
	$varano = substr($data,0,4);

	$convertedia = date ("w", mktime (0,0,0,$varmes,$vardia,$varano)); 

	$diaSemana = array("Domingo", "Segunda-feira", "Ter�a-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "S�bado"); 

	$mes = array(1=>"janeiro", "fevereiro", "mar�o", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"); 

	if ($varmes < 10) $varmes = substr($varmes,1,1);

	return $diaSemana[$convertedia] . ", " . $vardia  . " de " . $mes[$varmes] . " de " . $varano; 
} 
// Utilizar da seguinte maneira 
//echo escreveData("2005-12-02"); 
?> 
	<td style='padding: 5px; font-size: 12px; font-weight: normal; font-family: arial; text-align: center;'>
	<? 
		$data = date("Y-m-d");
		echo escreveData($data);
		echo date(" - H:i");
		echo " / Posto: " . $login_codigo_posto . "-" . ucfirst($login_nome);
		
		if($login_fabrica == 3 and $login_bloqueio_pedido == 't'){
			echo "<p>";
			
			echo "<font face='verdana' size='2' color='FF0000'><b>Existem t�tulos pendentes de seu posto autorizado junto ao Distribuidor.
			<br>
			N�o ser� poss�vel efetuar novo pedido faturado das linhas de eletro e branca.
			<br><br>
			Para regularizar a situa��o solicitamos um contato urgente com a TELECONTROL:
			<br>
			(14) 3413-6588 / (14) 3413-6589 / distribuidor@telecontrol.com.br
			<br>
			Entrar em contato com o departamento de cobran�as ou <br>
			efetue o dep�sito em conta corrente no <br><BR>
			Banco Bradesco<BR>
			Ag�ncia 2155-5<br>
			C/C 17427-0<br><br>
			e encaminhe um fax (14 3413-6588) com o comprovante.</b>
			<br><br>
			<b>Para visualizar os t�tulos <a href='posicao_financeira_telecontrol.php'>clique aqui</a></b>
			</font>";
			
			echo "<p>";
		}
		
	?>
	</td>
</tr>

<?
if ($login_fabrica == 3 and date("Y-m-d") < '2005-10-01') {
	echo "<tr bgcolor='#BED2D8'><td align='center'><b>Informativo de leitura obrigat�ria.</b><br><font size='-1'>Novo procedimento para envio de Ordens de Servi�o e Nota fiscal de M�o-de-Obra</font><br><a href='pdf/britania_informativo_001.pdf'>Ler Informativo</a></td></tr>";
}

if (1==2) {
	$sqlX = "SELECT COUNT(*) FROM tbl_opiniao_posto WHERE tbl_opiniao_posto.fabrica = $login_fabrica AND tbl_opiniao_posto.ativo IS TRUE ";
	$res = @pg_exec ($con,$sqlX);
	$tem_pesquisa = @pg_result ($res,0,0) ;

	$sqlX = "SELECT COUNT(*) FROM tbl_opiniao_posto JOIN tbl_opiniao_posto_pergunta USING (opiniao_posto) JOIN tbl_opiniao_posto_resposta USING (opiniao_posto_pergunta) WHERE tbl_opiniao_posto.fabrica = $login_fabrica AND tbl_opiniao_posto.ativo IS TRUE AND tbl_opiniao_posto_resposta.posto = $login_posto";
	$res = @pg_exec ($con,$sqlX);

	if (@pg_result ($res,0,0) == 0 AND $tem_pesquisa) {
		echo "<tr>";
		echo "<td bgcolor='#FF6633' style='padding: 5px; font-size: 12px; font-weight: normal; font-family: arial,verdana; text-align: center;'>";
		echo "<b>Atenc�o !</b> Voc� foi convidado a participar de uma pesquisa. <br>Antes de prosseguir utilizando o site, voc� deve completar a pesquisa. <br> <a href='opiniao_posto.php'>Clique aqui</a> para preencher o formul�rio";
		echo "</td>";
		echo "</tr>";

		if (strpos ($PHP_SELF,'opiniao_posto.php') === false) exit;
	}
}

?>

<tr>
	<td><div class="frm-on-os" id="displayArea">&nbsp;</div></td>
</tr>
</TABLE>
                                                                                                                                                                                                                  imagem/index.php                                                                                    0000664 0003724 0003721 00000030216 10470355344 016141  0                                                                                                    ustar   raphael                         telecontrol                     0000000 0000000                                                                                                                                                                        <?
include '../dbconfig.php';
include '../includes/dbconnect-inc.php';
include '../autentica_usuario.php';
$cor ="#485989";
$cor2="#9BC4FF";

$link_os          = "../menu_os.php"                                 ;
$link_pedido      = "../menu_pedido.php"                            ;
$link_extrato     = "../os_extrato.php"                             ;
$link_cadastro    = "../menu_cadastro.php"                          ;
$link_preco       = "../tabela_precos.php"                          ;
$link_vista       = "vistas.php"                                 ;
$link_informativo = "../comunicado_mostra.php?tipo=Informativo"     ;
$link_comunicado  = "../comunicado_mostra.php?tipo=Comunicado"      ;
$link_forum       = "../forum.php"                                  ;
$link_pesquisa    = "../menu_os.php"                                ;
$link_requisitos  = "javascript:;' onclick=\"window.open('http://www.telecontrol.com.br/assist/configuracao.php','janela','toolbar=no,location=yes,status=yes,scrollbars=yes,directories=no,width=450,height=400,top=18,left=0')\""                                ;
$link_sair        = "http://www.telecontrol.com.br/assist/"      ;


//$logo = 'lorenzetti.gif';
if($login_fabrica==3)  $logo = 'britania.gif';
if($login_fabrica==19) $logo = 'lorenzetti.gif';

?>
<script language="JavaScript1.2">
function high(which2){
theobject=which2
highlighting=setInterval("highlightit(theobject)",50)
}
function low(which2){
clearInterval(highlighting)
which2.filters.alpha.opacity=40
}
function highlightit(cur2){
if (cur2.filters.alpha.opacity<100)
cur2.filters.alpha.opacity+=5
else if (window.highlighting)
clearInterval(highlighting)
}
</script>
<style>
body {
	text-align: center;
	font-family:Arial;
	margin: 0px,0px,0px,0px;
	padding:  0px,0px,0px,0px;
}

a.conteudo{
	color: #FFFFFF;
	font-family: Arial;
	FONT-SIZE: 8pt; 
	font-weight: bold;
	text-decoration: none;
	text-align: center;
}
a.conteudo:visited {
	color: #FFFFFF;
	FONT-SIZE: 8pt;
	font-weight: bold;
	text-decoration: none;
	text-align: center;
}

a.conteudo:hover {
	color: #FFFFCC;
	FONT-SIZE: 8pt;
	font-weight: bold;
	text-decoration: none;
	text-align: center;
}

a.conteudo:active {
	color: #FFFFFF;
	FONT-SIZE: 8pt;
	font-weight: bold;
	text-decoration: none;
	text-align: center;
}

.Tabela{
	border:1px solid #d2e4fc;
/*	background-color:<?=$cor;?>;*/
}
.rodape{
	color: #FFFFFF;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 9px;
	background-color: #FF9900;
	font-weight: bold;
}
img{
border:0px;
}
.mensagem {
	color: #0099FF;
	font-size: 13px;
	font-weight: bold;
}
.fundo {
background-position: bottom left ;
background-image: url('../logos/telecontrol2.jpg') ;
background-repeat: no-repeat ;
width: 152px ;
height: 80px ;}


</style>

<body><center>
<table width='100%' height='100%' border='0' cellpadding='0' cellspacing='0'>
	<tr>
		<td align='center' valign='top'>
<?		
echo "<TABLE width='750px' border='0' cellspacing='0' cellpadding='0' align='center'>";
		echo "<tr>";
		echo "<td><a href='$PHP_SELF?menu=os'><img src='../imagens/aba/os";if ($layout_menu == "os"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
		echo "<td><a href='$PHP_SELF?menu=produtos'><img src='../imagens/aba/produtos";if ($layout_menu == "produtos"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
		echo "<td><a href='$PHP_SELF?menu=lancamentos'><img src='../imagens/aba/info_tecnico";if ($layout_menu == "lancamentos"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
		echo "<td><a href='$PHP_SELF?menu=lancamentos'><img src='../imagens/aba/lancamentos";if ($layout_menu == "lancamentos"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
		echo "<td><a href='$PHP_SELF?menu=lancamentos'><img src='../imagens/aba/lancamentos";if ($layout_menu == "lancamentos"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
		echo "<td><a href='$PHP_SELF?menu=promocoes'><img src='../imagens/aba/promocoes";if ($layout_menu == "promocoes"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
		echo "<td><a href='$PHP_SELF?menu=outros'><img src='../imagens/aba/outros";if ($layout_menu == "outros"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
		echo "<td><img src='../imagens/aba/sair.gif' border='0'></td>";
		echo "</tr>";
echo "</table>";
echo "<TABLE width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>";
echo "<tr>";
echo "<td background='http://www.telecontrol.com.br/assist/imagens/submenu_fundo_cinza.gif' colspan='8'>&nbsp;</td>";
echo "</tr>";
echo "</table>";

?>

<table width="745" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
	<tr><td colspan='4'>
		<table width="745" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
			<tr align='center'>
					<td  class='mensagem' width='180' align='center'><div id="container"><h2><IMG SRC="../logos/<?=$logo?>"; ALT="Bem-Vindo!!!"></h2></div></td>
					<td class='mensagem'>Mensagem que voc� deseja para o posto</td>
					<td width='180' align='center'><IMG SRC="../logos/telecontrol2.jpg" ALT="Bem-Vindo!!!"></td>
			</tr>

			</table>
	</td></tr>
	<tr>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_os;?>' class='conteudo'><img src="imagem/os.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_os;?>' class='conteudo'>ORDEM DE SERVI�O</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_pedido;?>' class='conteudo'><img src="imagem/pedido.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_pedido;?>' class='conteudo'>PEDIDO</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_extrato;?>' class='conteudo'><img src="imagem/extrato.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_extrato;?>' class='conteudo'>EXTRATO</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_cadastro;?>' class='conteudo'><img src="imagem/cadastro.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_cadastro;?>' class='conteudo'>CADASTRO</a></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_preco;?>' class='conteudo'><img src="imagem/preco.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_preco;?>' class='conteudo'>TABELA DE PRE�O</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_vista;?>' class='conteudo'><img src="imagem/vista.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_vista;?>' class='conteudo'>VISTA EXPLODIDA</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_informativo;?>' class='conteudo'><img src="imagem/informativo.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_informativo;?>' class='conteudo'>INFORMATIVO T�CNICO</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_comunicado;?>' class='conteudo'><img src="imagem/comunicado.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_comunicado;?>' class='conteudo'>COMUNICADO ADMINISTRATIVO</a></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_forum;?>' class='conteudo'><img src="imagem/forum.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_forum;?>' class='conteudo'>FORUM</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_pesquisa;?>' class='conteudo'><img src="imagem/pesquisa.gif" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_pesquisa;?>' class='conteudo'>PESQUISA DE SATISFA��O</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_requisitos;?>' class='conteudo'><img src="imagem/requisitos.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_requisitos;?>' class='conteudo'>REQUISITO DO SISTEMA</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_sair;?>' class='conteudo'><img src="imagem/sair.gif" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_sair;?>' class='conteudo'>SAIR</a></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr><td colspan='4'><br></td></tr>
</table>
		</td>
	</tr>
	<tr>
		<td height='5'class='rodape'><b>&nbsp;&nbsp;Telecontrol Networking Ltda - <? echo date("Y"); ?> - www.telecontrol.com.br - Deus � o Provedor</b></td>
	</tr>
</table>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                                  imagem/menu_inicial.php                                                                             0000664 0003724 0003721 00000030227 10470377174 017475  0                                                                                                    ustar   raphael                         telecontrol                     0000000 0000000                                                                                                                                                                        <?
include '../dbconfig.php';
include '../includes/dbconnect-inc.php';
include '../autentica_usuario.php';
$cor ="#485989";
$cor2="#9BC4FF";

$link_os          = "../menu_os.php"                                 ;
$link_pedido      = "../menu_pedido.php"                            ;
$link_extrato     = "../os_extrato.php"                             ;
$link_cadastro    = "../menu_cadastro.php"                          ;
$link_preco       = "../tabela_precos.php"                          ;
$link_vista       = "vistas.php"                                 ;
$link_informativo = "../comunicado_mostra.php?tipo=Informativo"     ;
$link_comunicado  = "../comunicado_mostra.php?tipo=Comunicado"      ;
$link_forum       = "../forum.php"                                  ;
$link_pesquisa    = "../menu_os.php"                                ;
$link_requisitos  = "javascript:;' onclick=\"window.open('http://www.telecontrol.com.br/assist/configuracao.php','janela','toolbar=no,location=yes,status=yes,scrollbars=yes,directories=no,width=450,height=400,top=18,left=0')\""                                ;
$link_sair        = "http://www.telecontrol.com.br/assist/"      ;


//$logo = 'lorenzetti.gif';
if($login_fabrica==3)  $logo = 'britania.gif';
if($login_fabrica==19) $logo = 'lorenzetti.gif';

?>
<script language="JavaScript1.2">
function high(which2){
theobject=which2
highlighting=setInterval("highlightit(theobject)",50)
}
function low(which2){
clearInterval(highlighting)
which2.filters.alpha.opacity=40
}
function highlightit(cur2){
if (cur2.filters.alpha.opacity<100)
cur2.filters.alpha.opacity+=5
else if (window.highlighting)
clearInterval(highlighting)
}
</script>
<style>
body {
	text-align: center;
	font-family:Arial;
	margin: 0px,0px,0px,0px;
	padding:  0px,0px,0px,0px;
}

a.conteudo{
	color: #FFFFFF;
	font-family: Arial;
	FONT-SIZE: 8pt; 
	font-weight: bold;
	text-decoration: none;
	text-align: center;
}
a.conteudo:visited {
	color: #FFFFFF;
	FONT-SIZE: 8pt;
	font-weight: bold;
	text-decoration: none;
	text-align: center;
}

a.conteudo:hover {
	color: #FFFFCC;
	FONT-SIZE: 8pt;
	font-weight: bold;
	text-decoration: none;
	text-align: center;
}

a.conteudo:active {
	color: #FFFFFF;
	FONT-SIZE: 8pt;
	font-weight: bold;
	text-decoration: none;
	text-align: center;
}

.Tabela{
	border:1px solid #d2e4fc;
/*	background-color:<?=$cor;?>;*/
}
.rodape{
	color: #FFFFFF;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 9px;
	background-color: #FF9900;
	font-weight: bold;
}
img{
border:0px;
}
.mensagem {
	color: #0099FF;
	font-size: 13px;
	font-weight: bold;
}
.fundo {
background-position: bottom left ;
background-image: url('../logos/telecontrol2.jpg') ;
background-repeat: no-repeat ;
width: 152px ;
height: 80px ;}


</style>

<body><center>
<table width='100%' height='100%' border='0' cellpadding='0' cellspacing='0'>
	<tr>
		<td align='center' valign='top'>
<?		
echo "<table width='750px' border='0' cellspacing='0' cellpadding='0' align='center'>";
echo "<tr>";

//aba OS
echo "		<td><a href='menu_os.php'><img src='imagens/aba/os";
if ($layout_menu == "os") echo "_ativo";
echo ".gif' border='0'></a>";

//aba PEDIDO
if($login_fabrica <>19){
	echo "<a href='menu_pedido.php'><img src='imagens/aba/pedidos";
	if ($layout_menu == "pedido") echo "_ativo";
	echo ".gif' border='0'></a>";
}

//aba INFORMA��ES T�CNICAS
echo "<a href='menu_tecnica'><img src='imagens/aba/info_tecnico";
if ($layout_menu == "tecnica") echo "_ativo";
echo ".gif' border='0'></a>";

// aba CADASTRO
echo "<a href='menu_cadastro'><img src='imagens/aba/cadastro";
if ($layout_menu == "cadastro") echo "_ativo";
echo ".gif' border='0'></a>";

//aba TABELA DE PRE�O
echo "<a href='menu_preco'><img src='imagens/aba/tabela_preco";
if ($layout_menu == "preco") echo "_ativo";
echo ".gif' border='0'></a>";

//echo "<a href='menu_tecnica'><img src='imagens/aba/lancamentos";if ($layout_menu == "lancamentos"){ echo "_ativo";}echo ".gif' border='0'></a>";
//echo "<td><a href='menu_tecnica'><img src='imagens/aba/outros";if ($layout_menu == "outros"){ echo "_ativo";}echo ".gif' border='0'></a></td>";

//aba SAIR
echo "<a href='http://www.telecontrol.com.br/assist'><img src='imagens/aba/sair.gif' border='0'></a></td>";

echo "</tr>";
echo "</table>";


echo "<TABLE width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>";
echo "<tr>";
echo "<td background='http://www.telecontrol.com.br/assist/imagens/submenu_fundo_cinza.gif' colspan='8'>&nbsp;</td>";
echo "</tr>";
echo "</table>";

?>

<table width="745" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
	<tr><td colspan='4'>
		<table width="745" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
			<tr align='center'>
					<td  class='mensagem' width='180' align='center'><div id="container"><h2><IMG SRC="logos/<?=$logo?>"; ALT="Bem-Vindo!!!"></h2></div></td>
					<td class='mensagem'>Seja bem vindo ao sistema Assit Telecontrol!</td>
					<td width='180' align='center'><IMG SRC="../logos/telecontrol2.jpg" ALT="Bem-Vindo!!!"></td>
			</tr>

			</table>
	</td></tr>
	<tr>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_os;?>' class='conteudo'><img src="imagem/os.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_os;?>' class='conteudo'>ORDEM DE SERVI�O</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_pedido;?>' class='conteudo'><img src="imagem/pedido.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_pedido;?>' class='conteudo'>PEDIDO</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_extrato;?>' class='conteudo'><img src="imagem/extrato.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_extrato;?>' class='conteudo'>EXTRATO</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_cadastro;?>' class='conteudo'><img src="imagem/cadastro.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_cadastro;?>' class='conteudo'>CADASTRO</a></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_preco;?>' class='conteudo'><img src="imagem/preco.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_preco;?>' class='conteudo'>TABELA DE PRE�O</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_vista;?>' class='conteudo'><img src="imagem/vista.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_vista;?>' class='conteudo'>VISTA EXPLODIDA</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_informativo;?>' class='conteudo'><img src="imagem/informativo.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_informativo;?>' class='conteudo'>INFORMATIVO T�CNICO</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_comunicado;?>' class='conteudo'><img src="imagem/comunicado.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_comunicado;?>' class='conteudo'>COMUNICADO ADMINISTRATIVO</a></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_forum;?>' class='conteudo'><img src="imagem/forum.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_forum;?>' class='conteudo'>FORUM</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_pesquisa;?>' class='conteudo'><img src="imagem/pesquisa.gif" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_pesquisa;?>' class='conteudo'>PESQUISA DE SATISFA��O</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_requisitos;?>' class='conteudo'><img src="imagem/requisitos.jpg" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_requisitos;?>' class='conteudo'>REQUISITO DO SISTEMA</a></td>
				</tr>
			</table>
		</td>
		<td width='185'><br>
			<table width="185" border="0" cellspacing="2" cellpadding="0" class='tabela' align='center'>
				<tr align='center'>
					<td width='185'><a href='<?=$link_sair;?>' class='conteudo'><img src="imagem/sair.gif" style="filter:alpha(opacity=40)" onMouseover="high(this)" onMouseout="low(this)"></a></td>
				</tr>
				<tr bgcolor='<?=$cor;?>' align='center' onmouseover="this.bgColor='<?=$cor2;?>'" onmouseout="this.bgColor='<?=$cor?>'" >
					<td width='185'><a href='<?=$link_sair;?>' class='conteudo'>SAIR</a></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr><td colspan='4'><br></td></tr>
</table>
		</td>
	</tr>
	<tr>
		<td height='5'class='rodape'><b>&nbsp;&nbsp;Telecontrol Networking Ltda - <? echo date("Y"); ?> - www.telecontrol.com.br - Deus � o Provedor</b></td>
	</tr>
</table>
</body>
</html>
                                                                                                                                                                                                                                                                                                                                                                         imagem/menu.php                                                                                     0000664 0003724 0003721 00000014064 10470356643 016004  0                                                                                                    ustar   raphael                         telecontrol                     0000000 0000000                                                                                                                                                                        <html>
<head>
<title><?=$title;?></title>
<style>
body {
	text-align: center;
	font-family:Arial;
	margin: 0px,0px,0px,0px;
	padding:  0px,0px,0px,0px;
}
A:link, A:visited { 
	TEXT-DECORATION: none;  color: #727272;
}

A:hover{
	color:#247BF0;
}
img{
border:0px;
}
</style>
</head>
<body>
<?
echo "<table height='2'><tr><td></td></tr></table>";
echo "<TABLE width='750px' border='0' cellspacing='0' cellpadding='0' 		align='center'>";
echo "<tr>";

echo "<td><a href='menu_os.php'><img src='../imagens/aba/os";if ($layout_menu == "os"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
echo "<td><a href='$PHP_SELF?menu=produtos'><img src='../imagens/aba/pedidos";if ($layout_menu == "pedido"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
echo "<td><a href='menu_tecnica'><img src='../imagens/aba/info_tecnico";if ($layout_menu == "tecnica"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
echo "<td><a href='cadastro'><img src='../imagens/aba/cadastro";if ($layout_menu == "lancamentos"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
echo "<td><a href='menu_tecnica'><img src='../imagens/aba/tabela_preco";if ($layout_menu == "preco"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
echo "<td><a href='menu_tecnica'><img src='../imagens/aba/promocoes";if ($layout_menu == "promocoes"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
echo "<td><a href='menu_tecnica'><img src='../imagens/aba/outros";if ($layout_menu == "outros"){ echo "_ativo";}echo ".gif' border='0'></a></td>";
echo "<td><img src='../imagens/aba/sair.gif' border='0'></td>";

echo "</tr>";
echo "</table>";
echo "<TABLE width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>";
echo "<tr>";
echo "<td background='http://www.telecontrol.com.br/assist/imagens/submenu_fundo_cinza.gif' colspan='8'>";

switch ($layout_menu) {
	case "os":
		include '../submenu_os.php';
		break;
	case "pedido":
		include '../submenu_pedido.php';
		break;
	case "cadastro":
		include '../submenu_cadastro.php';
		break;
	case "tecnica":
		include '../submenu_tecnica.php';
		break;
	case "preco":
		include '../submenu_preco.php';
		break;
	default:
		include '../submenu_os.php';
		break;
	}

echo"</td>";
echo "</tr>";
echo "</table>";
echo "<table height='2'><tr><td></td></tr></table>";

?>

<TABLE width="700px" border="0" cellspacing="0" cellpadding="0" bgcolor='#D9E2EF' align="center">
	<TR> 
		<TD width='10'><IMG src="../imagens/corner_se_laranja.gif"></TD>
		<TD style='font-size: 14px; font-weight: bold; font-family: arial;'> <? echo "$title" ?> </TD>
		<TD width='10'><IMG src="../imagens/corner_sd_laranja.gif"></TD>
	</TR>
</TABLE>
<TABLE width="700px" border="2"  cellpadding='0' cellspacing='0' bordercolor='#d9e2ef' align="center">
	<tr>
		<?
		function escreveData($data) { 
			$vardia = substr($data,8,2);
			$varmes = substr($data,5,2);
			$varano = substr($data,0,4);

			$convertedia = date ("w", mktime (0,0,0,$varmes,$vardia,$varano)); 

			$diaSemana = array("Domingo", "Segunda-feira", "Ter�a-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "S�bado"); 

			$mes = array(1=>"janeiro", "fevereiro", "mar�o", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"); 

			if ($varmes < 10) $varmes = substr($varmes,1,1);

			return $diaSemana[$convertedia] . ", " . $vardia  . " de " . $mes[$varmes] . " de " . $varano; 
		} 
		// Utilizar da seguinte maneira 
		//echo escreveData("2005-12-02"); 
		?> 
		<td style='padding: 5px; font-size: 12px; font-weight: normal; font-family: arial; text-align: center;'>
			<? 
				$data = date("Y-m-d");
				echo escreveData($data);
				echo date(" - H:i");
				echo " / Posto: " . $login_codigo_posto . "-" . ucfirst($login_nome);
				
				if($login_fabrica == 3 and $login_bloqueio_pedido == 't'){
					echo "<p>";
					
					echo "<font face='verdana' size='2' color='FF0000'><b>Existem t�tulos pendentes de seu posto autorizado junto ao Distribuidor.
					<br>
					N�o ser� poss�vel efetuar novo pedido faturado das linhas de eletro e branca.
					<br><br>
					Para regularizar a situa��o solicitamos um contato urgente com a TELECONTROL:
					<br>
					(14) 3413-6588 / (14) 3413-6589 / distribuidor@telecontrol.com.br
					<br>
					Entrar em contato com o departamento de cobran�as ou <br>
					efetue o dep�sito em conta corrente no <br><BR>
					Banco Bradesco<BR>
					Ag�ncia 2155-5<br>
					C/C 17427-0<br><br>
					e encaminhe um fax (14 3413-6588) com o comprovante.</b>
					<br><br>
					<b>Para visualizar os t�tulos <a href='posicao_financeira_telecontrol.php'>clique aqui</a></b>
					</font>";
					
					echo "<p>";
				}
				
			?>
			</td>
		</tr>

<?
if ($login_fabrica == 3 and date("Y-m-d") < '2005-10-01') {
	echo "<tr bgcolor='#BED2D8'><td align='center'><b>Informativo de leitura obrigat�ria.</b><br><font size='-1'>Novo procedimento para envio de Ordens de Servi�o e Nota fiscal de M�o-de-Obra</font><br><a href='pdf/britania_informativo_001.pdf'>Ler Informativo</a></td></tr>";
}

if (1==2) {
	$sqlX = "SELECT COUNT(*) FROM tbl_opiniao_posto WHERE tbl_opiniao_posto.fabrica = $login_fabrica AND tbl_opiniao_posto.ativo IS TRUE ";
	$res = @pg_exec ($con,$sqlX);
	$tem_pesquisa = @pg_result ($res,0,0) ;

	$sqlX = "SELECT COUNT(*) FROM tbl_opiniao_posto JOIN tbl_opiniao_posto_pergunta USING (opiniao_posto) JOIN tbl_opiniao_posto_resposta USING (opiniao_posto_pergunta) WHERE tbl_opiniao_posto.fabrica = $login_fabrica AND tbl_opiniao_posto.ativo IS TRUE AND tbl_opiniao_posto_resposta.posto = $login_posto";
	$res = @pg_exec ($con,$sqlX);

	if (@pg_result ($res,0,0) == 0 AND $tem_pesquisa) {
		echo "<tr>";
		echo "<td bgcolor='#FF6633' style='padding: 5px; font-size: 12px; font-weight: normal; font-family: arial,verdana; text-align: center;'>";
		echo "<b>Atenc�o !</b> Voc� foi convidado a participar de uma pesquisa. <br>Antes de prosseguir utilizando o site, voc� deve completar a pesquisa. <br> <a href='opiniao_posto.php'>Clique aqui</a> para preencher o formul�rio";
		echo "</td>";
		echo "</tr>";

		if (strpos ($PHP_SELF,'opiniao_posto.php') === false) exit;
	}
}

?>
		<tr>
			<td><div class="frm-on-os" id="displayArea">&nbsp;</div></td>
		</tr>
</TABLE>                                                                                                                                                                                                                                                                                                                                                                                                                                                                            imagem/produto_visualiza.php                                                                        0000664 0003724 0003721 00000012511 10470400426 020603  0                                                                                                    ustar   raphael                         telecontrol                     0000000 0000000                                                                                                                                                                        <?
include '../dbconfig.php';
include '../includes/dbconnect-inc.php';
include '../autentica_usuario.php';
$tipo = urldecode ($tipo);
$title='$tipo';
include "cabecalho.php";
?>

<style>
.titulo {
	font-family: Arial;
	font-size: 9pt;
	text-align: center;
	font-weight: bold;
	color: #FFFFFF;
	background: #408BF2;
}
.titulo2 {
	font-family: Arial;
	font-size: 12pt;
	text-align: center;
	font-weight: bold;
	color: #FFFFFF;
	background: #408BF2;
}

.conteudo {
	font-family: Arial;
	FONT-SIZE: 8pt; 
	text-align: left;
}
.Tabela{
	border:1px solid #408BF2;
	
}
img{
	border: 0px;
}
</style>
<?

$tipo       = $_GET ['tipo'];
$familia    = $_GET ['familia'];
$linha      = $_GET ['linha'];

# SELECIONA A FAM�LIA DO POSTO
$sql = "SELECT familia FROM tbl_posto_linha WHERE posto = $login_posto";
$res = pg_exec ($con,$sql);

$familia_posto = '';

for ($i=0; $i<pg_numrows($res); $i++){
	if(strlen(pg_result ($res,$i,0))){
		$familia_posto .= pg_result ($res,$i,0);
		$familia_posto .= ", ";
		}
}

# SELECECIONA O TIPO DE COMUNICADO DO POSTO
$sql2 = "SELECT tbl_posto_fabrica.codigo_posto        ,
				tbl_posto_fabrica.tipo_posto       
		FROM	tbl_posto
		LEFT JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_posto.posto
		WHERE   tbl_posto_fabrica.fabrica = $login_fabrica
		AND     tbl_posto.posto   = $login_posto ";

$res2 = pg_exec ($con,$sql2);

if (pg_numrows ($res2) > 0) {
	$tipo_posto            = trim(pg_result($res2,0,tipo_posto));
}


#SELECIONA O COMUNICADO
if (strlen ($tipo) > 0 AND strlen ($comunicado) == 0) {
	$tipo = urldecode ($tipo);

	$sql = "SELECT	tbl_comunicado.comunicado, 
					tbl_comunicado.descricao , 
					tbl_comunicado.mensagem  , 
					tbl_produto.produto      , 
					tbl_produto.referencia   , 
					tbl_produto.descricao AS descricao_produto        , 
					to_char (tbl_comunicado.data,'dd/mm/yyyy') AS data 
			FROM	tbl_comunicado 
			LEFT JOIN tbl_produto USING (produto) 
			WHERE	tbl_comunicado.fabrica = $login_fabrica
			AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
			AND     ((tbl_comunicado.posto           = $login_posto) OR (tbl_comunicado.posto           IS NULL))
			AND    tbl_comunicado.ativo IS TRUE ";

			if($tipo == 'zero'){ 
				$tipo = "Sem T�tulo";
				$sql .= "AND	tbl_comunicado.tipo IS NULL "; 
			}else{
				$sql .= "AND	tbl_comunicado.tipo = '$tipo' ";
			}
	if ($linha)   $sql .= "AND (tbl_produto.linha = $linha OR tbl_comunicado.linha = $linha) ";
	if ($familia) $sql .= "AND tbl_produto.familia = $familia ";

	$sql .= "ORDER BY tbl_produto.descricao DESC, tbl_produto.referencia " ;
//echo nl2br($sql);
	$res = pg_exec ($con,$sql);
	if (pg_numrows ($res) > 0) {	
		echo "<table width='600' align='center' border='0' cellspacing='1' cellpadding='1' class='Tabela'>";
		echo "<tr class='titulo2'>";
		echo "<td colspan='4'>$tipo</td>";
		echo "</tr>";
	
		echo "<tr bgcolor='#ffffff'>";
		echo "<td align='center' colspan='4'><font color='#000000' size='0'><b>Se voc� n�o possui o Acrobat Reader&reg;, <a href='http://www.adobe.com/products/acrobat/readstep2.html' target='_blank'>instale agora</a>.</b></font></td>";
		echo "</tr>";
	
		echo "<tr bgcolor='#ffffff'>";
		echo "<td align='center' colspan='4'><b>Voc� est� em ";
		
		$sql1="SELECT nome FROM tbl_linha WHERE linha=$linha";
		$res1 = pg_exec ($con,$sql1);
		echo trim(pg_result($res1,0,nome));
	
		if(strlen($familia)>0){
			$sql2="SELECT descricao FROM tbl_familia WHERE familia=$familia";
			$res2 = pg_exec ($con,$sql2);
			echo " - ".trim(pg_result($res2,0,descricao));
		}
		echo"</b></td>";
		echo "</tr>";
		
		echo "<tr class='titulo'>";
		echo "<td>Refer�ncia</td>";
		echo "<td>Produto</td>";
		echo "</tr>";
		
		$total = pg_numrows ($res);
	
		for ($i=0; $i<$total; $i++) {
			$Xcomunicado           = trim(pg_result($res,$i,comunicado));
			$produto               = trim(pg_result ($res,$i,produto));
			$referencia            = trim(pg_result ($res,$i,referencia));
			$descricao             = trim(pg_result ($res,$i,descricao_produto));
			$comunicado_descricao  = trim(pg_result ($res,$i,descricao));
	
			$cor = "#ffffff";
			if ($i % 2 == 0) $cor = '#eeeeff';
	
			echo "<tr bgcolor='$cor' class='conteudo'>\n";
			echo "<td nowrap>$referencia </td>";
			echo "<td nowrap height='20'>";
	
			$gif = "../comunicados/$Xcomunicado.gif";
			$jpg = "../comunicados/$Xcomunicado.jpg";
			$pdf = "../comunicados/$Xcomunicado.pdf";
			$doc = "../comunicados/$Xcomunicado.doc";
			$rtf = "../comunicados/$Xcomunicado.rtf";
			$xls = "../comunicados/$Xcomunicado.xls";
			$ppt = "../comunicados/$Xcomunicado.ppt";
			$zip = "../comunicados/$Xcomunicado.zip";
		
			if (file_exists($rtf) == true) echo "<a href='../comunicados/$Xcomunicado.rtf' target='_blank'>";
			if (file_exists($xls) == true) echo "<a href='../comunicados/$Xcomunicado.xls' target='_blank'>";
			if (file_exists($pdf) == true) echo "<a href='../comunicados/$Xcomunicado.pdf' target='_blank'>";
			if (file_exists($ppt) == true) echo "<a href='../comunicados/$Xcomunicado.ppt' target='_blank'>";
			if (file_exists($zip) == true) echo "<a href='../comunicados/$Xcomunicado.zip' target='_blank'>";

			if (strlen ($descricao) > 0) {
				echo $descricao;
			}else{
				echo $comunicado_descricao;
			}
			echo "</a>";
			echo "</td>\n";
			echo "</tr>\n";
		}
		echo "</form>\n";
		echo "</table>\n";
	
		echo "<hr>";
	}else{
	echo "<center>Nenhum $tipo cadastrado</center>";
	}
}





?>                                                                                                                                                                                       imagem/vistas.php                                                                                   0000664 0003724 0003721 00000016547 10470344374 016357  0                                                                                                    ustar   raphael                         telecontrol                     0000000 0000000                                                                                                                                                                        <?
include '../dbconfig.php';
include '../includes/dbconnect-inc.php';
include '../autentica_usuario.php';
$title = "Menu Assistencia T�cnica";
$layout_menu = 'tecnica';
?>

<style>
body{
	margin: 0px;
	padding: 0px;
	color: #727272;
	font-weight: normal;
	background-color: #FFFFFF;
	font-size: 12px;
	font-family: Arial, Helvetica, sans-serif;
}
td {	
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
}

img{
	border: none;
}

A:link, A:visited { TEXT-DECORATION: none;  color: #727272;}

A:hover { TEXT-DECORATION: underline;color: #33CCFF; }
.fundo {
	background-image: url(http://img.terra.com.br/i/terramagazine/fundo.jpg);
	background-repeat: repeat-x;
}
.chapeu {
	color: #0099FF;
	padding: 2px;
	margin-bottom: 4px;
	margin-top: 10px;
	background-image: url(http://img.terra.com.br/i/terramagazine/tracejado3.gif);
	background-repeat: repeat-x;
	background-position: bottom;
	font-size: 13px;
	font-weight: bold;
}

.menu {
	font-size: 11px;
}

hr{ 
	height: 1px;
	margin: 15px 0;
	padding: 0;
	border: 0 none;
	background: #ccc;
}

a:link.menu {
	padding: 3px;
	display:block;
	font: normal small Verdana, Geneva, Arial, Helvetica, sans-serif;
	color: navy;
	font-size: 13px;
	font-weight: bold;
	text-align: left;
	text-decoration: none;
}

a:visited.menu {
	padding: 3px;
	display:block;
	font: normal small Verdana, Geneva, Arial, Helvetica, sans-serif;
	color: navy;
	font-size: 13px;
	font-weight: bold;
	text-align: left;
	text-decoration: none;
}

a:hover.menu {
	padding: 3px;
	display:block;
	font: normal small Verdana, Geneva, Arial, Helvetica, sans-serif;
	color: black;
	font-size: 13px;
	font-weight: bold;
	text-align: left;
	text-decoration: none;
	background-color: #ced7e7;
}
.rodape{
	color: #FFFFFF;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 9px;
	background-color: #FF9900;
	font-weight: bold;
}
.detalhes{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #333399;
}

</style>
<?
include "menu.php";
?>

<!-- ================================================================== -->
<!-- 
<tr bgcolor='#fafafa'>
	<td width='25'><img src='imagens/marca25.gif'></td>
	<td nowrap width='260'><a href='http://www.telecontrol.com.br/assist/comunicado_mostra.php?tipo=Esquema+El%E9trico' class='menu'>Produtos</a></td>
	<td nowrap class='descricao'>Guia do usu�rio / caracteristicas t�cnicas dos produtos</td>
</tr>
 -->
<!-- ================================================================== -->

<!--


-->

<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
	<tr bgcolor = '#efefef'>
		<td rowspan='3' width='20' valign='top'><img src='../imagens/marca25.gif'></td>
		<td  class="chapeu" colspan='2' >Vista Explodida</td>
	</tr>
	<tr bgcolor = '#efefef'><td colspan='2' height='5'></td></tr>
	<tr bgcolor = '#efefef'>
		<td valign='top' class='menu'>
<?
$sql = "SELECT DISTINCT tbl_familia.familia                                  ,
						tbl_familia.descricao                                ,
						tbl_linha.linha                                      ,
						tbl_linha.nome                                       
		FROM    tbl_comunicado 
		JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
		JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha       
		LEFT JOIN tbl_familia ON tbl_produto.familia = tbl_familia.familia 
		WHERE   tbl_linha.fabrica    = $login_fabrica
		AND     tbl_comunicado.ativo IS TRUE
		AND     tbl_comunicado.tipo = 'Vista Explodida'
		AND     tbl_comunicado.produto IS NOT NULL
	UNION
	SELECT DISTINCT null::int4 AS familia                                      ,
					null::text AS descricao                                    ,
					tbl_linha.linha                                      ,
					tbl_linha.nome                                       
		FROM    tbl_comunicado 
		JOIN    tbl_linha ON tbl_comunicado.linha   = tbl_linha.linha
		WHERE   tbl_linha.fabrica    = $login_fabrica
		AND     tbl_comunicado.ativo IS TRUE
		AND     tbl_comunicado.tipo = 'Vista Explodida'
		AND     tbl_comunicado.produto IS NULL
	ORDER BY nome, descricao";
//echo nl2br($sql);
$res = pg_exec ($con,$sql);

if (pg_numrows($res) > 0) {
	$linha_anterior = "";
	echo "<dl>";
	for ($i = 0 ; $i < pg_numrows($res) ; $i++) {
		$descricao  = trim(pg_result ($res,$i,descricao));
		$familia    = trim(pg_result ($res,$i,familia))  ;
		$nome       = trim(pg_result ($res,$i,nome))     ;
		$linha      = trim(pg_result ($res,$i,linha))    ;
//		echo "<br>Linha Atual: $linha";
//		echo "<br>Linha Anterior: $linha_anterior";

		if($linha_anterior <> $linha) {
						echo "<br><dt>&nbsp;&nbsp;<b>�</b> <a href='produto_visualiza.php?tipo=Vista+Explodida&linha=$linha'>$nome Linha</a><br></dt>";
		}
		if($login_fabrica<>19){
			echo "<dd>&nbsp;&nbsp;<b>�</b> <a href='produto_visualiza.php?tipo=Vista+Explodida&linha=$linha&familia=$familia'>$descricao</a><br></dd>";
		}
		$linha_anterior = $linha;
//		echo "Linha Aterior recebe: $linha_anterior";
	}
}

?>
<br>
		</td>
		<td rowspan='2'class='detalhes' width='350'>Escolha ao lado a fam�lia do produto que deseja consultar a Vista Explodida.</td>
	</tr>
</table>
<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
	<tr bgcolor = '#fafafa'>
		<td rowspan='3' width='20' valign='top'><img src='../imagens/marca25.gif'></td>
		<td  class="chapeu" colspan='2' >Esquema El�trico</td>
	</tr>
	<tr bgcolor = '#fafafa'><td colspan='2' height='5'></td></tr>
	<tr bgcolor = '#fafafa'>
		<td valign='top' class='menu'>
<?
if($login_fabrica===19){
	$sql = "SELECT DISTINCT tbl_familia.familia                                  ,
							tbl_familia.descricao                                ,
							tbl_linha.linha                                      ,
							tbl_linha.nome                                       
			FROM    tbl_comunicado 
			JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
			JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha       
			LEFT JOIN    tbl_familia ON tbl_produto.familia = tbl_familia.familia
			WHERE   tbl_linha.fabrica    = $login_fabrica
			AND     tbl_comunicado.ativo IS TRUE
			AND     tbl_comunicado.tipo = 'Esquema El�trico'
		ORDER BY tbl_linha.nome, tbl_familia.descricao";
	//echo nl2br($sql);
	$res = pg_exec ($con,$sql);
	
	if (pg_numrows($res) > 0) {
		$linha_anterior = "";
		echo "<dl>";
		for ($i = 0 ; $i < pg_numrows($res) ; $i++) {
			$descricao  = trim(pg_result ($res,$i,descricao));
			$familia    = trim(pg_result ($res,$i,familia))  ;
			$nome       = trim(pg_result ($res,$i,nome))     ;
			$linha      = trim(pg_result ($res,$i,linha))    ;
	//		echo "<br>Linha Atual: $linha";
	//		echo "<br>Linha Anterior: $linha_anterior";
	
			if($linha_anterior <> $linha) {
							echo "<br><dt>&nbsp;&nbsp;<b>�</b> <a href='produto_visualiza.php?tipo=Esquema El�trico&linha=$linha'>$nome</a><br></dt>";
			}
			echo "<dd>&nbsp;&nbsp;<b>�</b> <a href='produto_visualiza.php?tipo=Vista+Explodida&linha=$linha&familia=$familia'>$descricao</a><br></dd>";
			$linha_anterior = $linha;
	//		echo "Linha Aterior recebe: $linha_anterior";
		}
	}
}else{
	echo "<br><dt>&nbsp;&nbsp;<b>�</b> <a href='produto_visualiza.php?tipo=Esquema El�trico'>Esquema El�trico</a><br></dt>";
}
?>
<br>
		</td>
		<td rowspan='2'class='detalhes' width='350'>Escolha ao lado a fam�lia do produto que deseja consultar o Esquema El�trico.</td>
	</tr>
	<tr bgcolor='#D9E2EF'>
	<td colspan='3'><img src='../imagens/spacer.gif' height='3'></td>
</tr>
</table><br>


</body>
</html>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         