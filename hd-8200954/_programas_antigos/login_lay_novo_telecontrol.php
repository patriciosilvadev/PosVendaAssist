<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'autentica_usuario.php';
include 'cabecalho_login_lay_novo.php';
?>

<style type="text/css">
input {
	BORDER-RIGHT: #000000 1px solid; 
	BORDER-TOP: #000000 1px solid; 
	FONT-WEIGHT: bold; 
	FONT-SIZE: 8pt; 
	BORDER-LEFT: #000000 1px solid; 
	BORDER-BOTTOM: #000000 1px solid; 
	FONT-FAMILY: Verdana; 
	BACKGROUND-COLOR: #FFFFFF
}
a:link {
	color: #000000;

	text-decoration: none;
}

a:visited {
	color: #000000;

	text-decoration: none;
}

a:hover {
	color: #000000;
	text-decoration: none;
}

a:active {
	color: #000000;
	font-weight: bold;
	text-decoration: none;
}
</style>
<?
if (strlen ($btn_buscar) > 0) {

if($_POST['tipo_busca'])          { $tipo_busca      = trim ($_POST['tipo_busca']);}
if($_POST['busca'])          { $busca      = trim ($_POST['busca']);}
if($tipo_busca=='os'){

}
if($tipo_busca=='pedido'){}
if($tipo_busca=='nf'){}
if($tipo_busca=='comunicado'){}
if($tipo_busca=='preco'){}

}


?>



<?
echo "<table width='680' border='0' align='center' cellpadding='4' cellspacing='4' style='font-family: verdana; font-size: 12px'>";
echo "<tr>";
echo "<td width='160' align='center'> ";
//--logo fabrica -->
echo "<IMG SRC='admin/imagens_admin/britania.jpg' ALT='Bem Vindo!!!' width='150' height='49'><BR>Bem-vindo posto<BR> <B>######## ### #### ##</b> </td>";
echo "<td width='520'  valign='bottom'>";


//-- ########BUSCA TELECONTROL########## -->
echo "	<FORM METHOD='POST' ACTION='$PHP_SELF'>";
	echo "<table width='500' border='0' cellpadding='0' align='right' cellspacing='1' bgcolor='#666666'>";
	echo "<tr>";
	echo "<td>";
		echo "<table width='510' height='60' border='0' align='center' cellpadding='0' cellspacing='0' style='font-family: verdana; font-size: 11px; background-color: #dfdfdf'>";
		echo "<tr bgcolor='#D2D2D2'>";
		echo "<td height='25' colspan='2' background='admin/imagens_admin/cinza.gif' align='right'>";
		
		
		echo "<input type='radio' name='tipo_busca' value='os'>O.S | <input type='radio' name='tipo_busca' value='pedido'>Pedido | <input type='radio' name='tipo_busca' value='nf'>Nota Fiscal | <input type='radio' name='tipo_busca' value='comunicado'>Comunicado | <input type='radio' name='tipo_busca' value='preco'>Pre�o de Pe�a&nbsp;&nbsp;&nbsp;";
		
		echo "</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td width='200' height='35' align='center' valign='middle'> ";
		echo "<img src='admin/imagens_admin/btn_lupa.gif' width='20' height='18'>Busca Telecontrol </td>";
		echo "<td width='300' align='right' valign='middle' > N�mero: &nbsp;"; 
		echo "<input type='text' size='20' maxlength='20' name='busca' value=''> <input type='submit' name='btn_buscar' value='Buscar'>&nbsp;&nbsp;&nbsp;";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
//-- ########BUSCA TELECONTROL FIM########## -->

echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td width='160' valign='top'>";
//--######## ESQUERDO ########## -->

 	echo "<table width='160' border='0' align='center' cellpadding='5' cellspacing='1' style='font-family: verdana; font-size: 11px; background-color: #71AFBA'>";
	echo "<tr>";
	echo "<td width='160' height='25' background='admin/imagens_admin/agua.gif'><font size='2' color='#FFFFFF'><B>Produtos</b></font>";
	echo "</td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td bgcolor='#ffffff' align='center'><B>Televis�o 20</b><BR>
	Tv Sony, tela plana, st�reo. R$2000,00<BR>
	<hr width='95%' size='1'><B>Televis�o 20</b><BR>
	Tv Sony, tela plana, st�reo. R$2000,00<BR>
	<hr width='95%' size='1'><B>Televis�o 20</b><BR>
	Tv Sony, tela plana, st�reo. R$2000,00<BR>
	<hr width='95%' size='1'><B>Televis�o 20</b><BR>
	Tv Sony, tela plana, st�reo. R$2000,00<BR><BR>";
	echo "</td>";
	echo "</tr>";
	
	echo "</table>";
 	echo "<BR>";
 
 
	echo "<table width='160' border='0' align='center' cellpadding='5' cellspacing='1' style='font-family: verdana; font-size: 11px; background-color: #71AFBA'>";
	echo "<tr>";
	echo "<td width='160' height='25' background='admin/imagens_admin/agua.gif'><font size='2' color='#FFFFFF'><B>Telecontrol</b></font>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td bgcolor='#EBFCFF'>Aqui os Postos Autorizados Britania podem efetuar o lan�amento de Ordens de Servi�o em garantia, conferir seu extrato financeiro, visualizar e imprimir vistas explodidas, contatar a empresa atrav�s do Fale Conosco, ficar a par de lan�amentos de produtos e promo��es entre outros recursos de grande utilidade para agilizar todo o processo de controle de Ordens de Servi�o.<BR><BR>A Telecontrol desenvolve sistemas totalmente destinados � Internet, com isto voc� tem acesso �s informa��es de sua empresa de qualquer lugar, podendo tomar decis�es gerenciais com total seguran�a.<br><BR><BR>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	//echo "<BR>";
	/*
	echo "<table width='160' border='0' align='center' cellpadding='5' cellspacing='1' style='font-family: verdana; font-size: 12px; background-color: #CD4444'>";
	echo "<tr>";
	echo "<td width='150' height='25' background='admin/imagens_admin/vermelho.gif'><font color='#FFFFFF'><B>Acesso R�pido</B></font></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td height='160' align='center' valign='top' bgcolor='#F0CECE'>";
		echo "<table width='155' border='0' align='center' cellpadding='0' cellspacing='1' bgcolor='#F0CECE' style='font-family: verdana; font-size: 10px' >";
		echo "<tr width='150'>";
		echo "<td width='75' valign='top' bgcolor='#F0CECE'> ";
		echo "<B>Cadastrar</B><BR>";
		echo "<a href='#'>Ordem Servi�o</a><BR>";
		echo "<a href='#'>Pedidos de Pe�a</a><BR>";
		echo "<a href='#'>O.S. Revenda</a><BR>";
		echo "<a href='#'>Consumidor</a><BR>";
		echo "<a href='#'>Revenda</a><BR>";
		echo "<a href='#'>Status OS</a><BR> ";
		echo "</td>";
		echo "<td width='75' valign='top' bgcolor='#F0CECE'> ";
		echo "<B>Consultar</b><BR>";
		echo "<a href='#'>Ordem Servi�o</a><BR>";
		echo "<a href='#'>Pedidos de Pe�a</a><BR>";
		echo "<a href='#'>Pend�ncia de Pe�a</a><BR>";
		echo "<a href='#'>O.S. Revenda</a><BR>";
		echo "<a href='#'>Nota Fiscal</a><BR> ";
		echo "</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td align='center' colspan='2'  bgcolor='#F0CECE'> ";
		echo "<B><br><BR><a href='#'>Fechar Ordem Servi�o</a></B></td>";
		echo "</tr>";
	echo "</table>";
	echo "</td>";
	echo "</tr>";
	echo "</table>"; */
echo "</td>";
echo "<td width='520' valign='top'>";
//--######## DIREITO ########## -->

	 
	 
echo "<table width='510' border='0' align='center' cellpadding='0' cellspacing='1'  style='font-family: verdana; font-size: 10px; background-color: #C6C7B1'>";
echo "<tr>";
echo "<td width='500' height='51' align='center' background='admin/imagens_admin/novidades.gif'>";
echo "<a href='$PHP_SELF?tipo=Boletim'>Boletim</a> | <a href='$PHP_SELF?tipo=Comunicado'>Comunicado</a> | <a href='$PHP_SELF?tipo=Descritivo t�cnico'>Descritivo t�cnico</a> | <a href='$PHP_SELF?tipo=Esquema El�trico'>Esquema El�trico</a> | <a href='$PHP_SELF?tipo=Foto'>Foto</a> | <a href='$PHP_SELF?tipo=informativo'>Informativo</a> <BR>  <a href='$PHP_SELF?tipo=Lan�amentos'>Lan�amentos</a> |  <a href='$PHP_SELF?tipo=Manual'>Manual</a> |  <a href='$PHP_SELF?tipo=Orienta��o de Servi�o'>Orienta��o</a> |  <a href='$PHP_SELF?tipo=Procedimento'>Procedimento</a> | <a href='$PHP_SELF?tipo=Promocao'>Promo��o</a> |  <a href='$PHP_SELF?tipo=Vista Explodida'>Vista Explodida</a>"; 

echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td bgcolor='#F0F0E4'>";
	echo "<table width='510' border='0' align='center' cellpadding='4' cellspacing='4' style='font-family: verdana; font-size: 10px; background-color: #F0F0E4'>";
	echo "<tr>";
	echo "<td colspan='3' align='center'>";
		echo "<font size='3'><B>Novo layout do Sistema Telecontrol</B><BR> Confira as novidades do Sistema</font> ";
		echo "</td>";
	echo "</tr>";
	echo "<tr>";  
	 echo "<td  width='166' valign='top'>";
	if($_GET['tipo'])          { $tipo      = trim ($_GET['tipo']);}
	
	$sql = "SELECT	tbl_comunicado.comunicado, 
					tbl_comunicado.descricao , 
					tbl_comunicado.mensagem  , 
					tbl_comunicado.tipo      ,
					tbl_produto.produto      , 
					tbl_produto.referencia   , 
					tbl_produto.descricao AS descricao_produto        , 
					to_char (tbl_comunicado.data,'dd/mm/yyyy') AS data 
			FROM	tbl_comunicado 
			LEFT JOIN tbl_produto USING (produto) 
			LEFT JOIN tbl_linha on tbl_linha.linha = tbl_produto.linha 
			WHERE	tbl_comunicado.fabrica = $login_fabrica";
			if (strlen ($tipo) > 0){$sql .=" AND	tbl_comunicado.tipo = '$tipo' ";}
			$sql .=" ORDER BY tbl_comunicado.data DESC LIMIT 9";
//			echo "$sql";
	$res = pg_exec ($con,$sql);
	for ($x = 0 ; $x < pg_numrows($res) ; $x++){
	
	$comunicado_msg            = trim(pg_result($res,$x,mensagem));
	$Xcomunicado               = trim(pg_result($res,$x,comunicado));
	$comunicado_descricao      = trim(pg_result($res,$x,descricao));
	$descricao_produto         = trim(pg_result($res,$x, descricao_produto));
	$comunicado_tipo           = trim(pg_result($res,$x,tipo));
	$comunicado_data           = trim(pg_result($res,$x,data));
	$produto_produto           = trim(pg_result($res,$x,produto));
	$gif = "comunicados/$Xcomunicado.gif";
	$jpg = "comunicados/$Xcomunicado.jpg";
	$pdf = "comunicados/$Xcomunicado.pdf";
	$doc = "comunicados/$Xcomunicado.doc";
	$rtf = "comunicados/$Xcomunicado.rtf";
	$xls = "comunicados/$Xcomunicado.xls";
	$ppt = "comunicados/$Xcomunicado.ppt";
		
	echo "<font size='1'><B>$comunicado_tipo</B> $comunicado_data</font><BR>";
		if (file_exists($gif) == true) echo "<a href='comunicados/$Xcomunicado.gif' target='_blank'>	$comunicado_descricao<BR>$descricao_produto<BR></a>";
		if (file_exists($jpg) == true) echo "<a href='comunicados/$Xcomunicado.jpg' target='_blank'>	$comunicado_descricao<BR>$descricao_produto<BR></a>";
		if (file_exists($doc) == true) echo "<a href='comunicados/$Xcomunicado.doc' target='_blank'>	$comunicado_descricao<BR>$descricao_produto<BR></a>";
		if (file_exists($rtf) == true) echo "<a href='comunicados/$Xcomunicado.rtf' target='_blank'>$comunicado_descricao<BR>$descricao_produto<BR></a>";
		if (file_exists($xls) == true) echo "<a href='comunicados/$Xcomunicado.xls' target='_blank'>$comunicado_descricao<BR>$descricao_produto<BR></a>";
		if (file_exists($ppt) == true) echo "<a href='comunicados/$Xcomunicado.ppt' target='_blank'></a>";
		if (file_exists($pdf) == true) {
			echo "<a href='comunicados/$Xcomunicado.pdf' target='_blank'>$comunicado_descricao<BR>$descricao_produto<BR></a>";
	}
		echo "<hr width='98%' size='1'>";
		if(($x=='2')||($x=='5')){
		echo "</td>";		
		echo "<td width='166' valign='top'>";
		}
	}
	
	
	/*
	
	
	echo "<tr>";
		echo "<td  width='166' valign='top'>
		<font size='1'><B>Boletim</B> 00/00/0000</font><BR>
		Batedeira com problemas<BR>
		BAT PEROLA BRANCA 220V BAU com problemas, por...<BR> 
		<hr width='98%' size='1'>
		<font size='1'><B>Boletim</B> 00/00/0000</font><BR>
		Batedeira com problemas<BR>
		BAT PEROLA BRANCA 220V BAU com problemas, por...<BR>
 		<hr width='98%' size='1'>
		<font size='1'><B>Boletim</B> 00/00/0000</font><BR>
		Batedeira com problemas<BR>
		BAT PEROLA BRANCA 220V BAU com problemas, por...<BR> ";
		echo "</td>";
		
		echo "<td width='166' valign='top'>
 		<font size='1'><B>Lan�amento</B>00/00/0000</font><BR>
		Ar condicionado<BR>
		Verificar o lan�amento do novo Ar Condicionado..<BR> 
		<hr width='98%' size='1'>
		<font size='1'><B>Lan�amento</B> 00/00/0000</font><BR>
		TV tela plana<BR>
		Verificar o lan�amento do novo TV..<BR> 
		<hr width='98%' size='1'>
		<font size='1'><B>Lan�amento</B> 00/00/0000</font><BR>
		Aquecedor<BR>
		Verificar o lan�amento do novo Aquecedor..<BR> ";
		echo "</td>";
		
		echo "<td width='166' valign='top'> 
		<font size='1'><B>Comunicado</B> 00/00/0000</font><BR>
		Ar condicionado<BR>
		Verificar o lan�amento do novo Ar Condicionado..<BR> 
		<hr width='98%' size='1'>
		<font size='1'><B>Comunicado</B> 00/00/0000</font><BR>
		TV tela plana<BR>
		Verificar o lan�amento do novo TV..<BR> 
		<hr width='98%' size='1'>
		<font size='1'><B>Comunicado</B> 00/00/0000</font><BR>
		Aquecedor<BR>
		Verificar o lan�amento do novo Aquecedor..<BR> ";
		echo "</td>";*/
	echo "</tr>";
	echo "</table>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "<br>";
echo "<table width='510' border='0' align='center' cellpadding='0' cellspacing='0'>";
echo "<tr>";
echo "<td width='360' valign='top' style='font-family: verdana; font-size: 12px'>";
//----######## DIREITO ESQUERDO########## -->

	echo "<table  width='350' border='0' cellpadding='5' cellspacing='1' style='font-family: verdana; font-size: 12px; background-color: #728D5A' align='left'> ";
	echo "<tr>";
		echo "<td height='25' background='admin/imagens_admin/verde.gif'>";
		echo "<font color='#FFFFFF'><B>Fechamento de Extrato</B></font>";
		echo "</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td height='35' bgcolor='#DDEFC7'>Seu pr�ximo extrato ser�
		fechado no dia <B>22/01/2006</b>! Existem <B><font color='#990000'>99</font></b> OS Abertas e <B><font color='#009900'>99</font></b> OS Fechadas
		do periodo de 00/01/2006 at� 02/07/2006. <a href='#'>Clique
		aqui para fechar as OS abertas.</a>";
		echo "</td>";
	echo "</tr>";
	echo "</table>";
echo "<BR>";
echo "<BR>";

	
echo "</td>";
echo "<td rowspan='3' width='150' valign='top'> ";
//--######## DIREITO DIREITO - AJUDA########## -->
	echo "<table style='font-family: verdana; font-size: 10px; background-color: #EBE062' width='150' border='0' align='center' cellpadding='5' cellspacing='1' >";
	echo "<tr>";
	echo "<td background='admin/imagens_admin/amarelo.gif'><font color='#FFFFFF'><font size='2'><B>Ajuda do Sistema</B></font>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align='center' bgcolor='#ffffff'> ";
		//-- ###MANUAIS#### -->
	echo "<font color='#FF0000'><b>Obtenha mais informa��es sobre o novo sistema</b></font>
	<BR><a href='pdf/sistema.pdf' target='_blank'>PDF</a> 
	<a href='pdf/sistema.doc' target='_blank'>DOC</a> 
	<a href='pdf/sistema.htm' target='_blank'>HTML</a>
	<BR><BR>
	<font color='#FF0000'><b>Consulte o manual feito especialmente para voc�!</b></font><BR>
	<a href='pdf/ajuda.pdf' target='_blank'>PDF</a>
	<a href='pdf/ajuda.doc' target='_blank'>DOC</a>
	<a href='pdf/ajuda.htm' target='_blank'>HTML</a>
	<BR><BR>
	<font color='#FF0000'><b>Para valorizar ainda mais o seu servi�o, estamos aumentando o valor das taxas de m�o-de-obra</b></font>
	<BR><a href='#doisreais'>saiba mais</a>
	<BR><BR>
	<font color='#FF0000'><b>Circular Manual do Sistema</b></font><BR>
	<a href='http://www.telecontrol.com.br/assist/pdf/sistema.pdf' target='_blank'>PDF</a>
	<a href='http://www.telecontrol.com.br/assist/pdf/sistema.doc' target='_blank'>DOC</a>
	<a href='http://www.telecontrol.com.br/assist/pdf/sistema.htm' target='_blank'>HTML</a>
	<BR><BR>
	<font color='#FF0000'><b>Manual Ajuda do Sistema</b><font size='1'></font><BR>
	<a href='http://www.telecontrol.com.br/assist/pdf/ajuda.pdf' target='_blank'>PDF</a> 
	<a href='http://www.telecontrol.com.br/assist/pdf/ajuda.doc' target='_blank'>DOC</a> 
	<a href='http://www.telecontrol.com.br/assist/pdf/ajuda.htm' target='_blank'>HTML</a> <BR><BR><BR>";
	echo "</td>";
	//-- ###MANUAIS#### -->
	echo "</tr>";
	echo "</table>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>";

	echo "<table width='350' style='font-family: verdana; font-size: 12px; background-color: #DB6510' align='left' cellpadding='5' cellspacing='1' >";
	echo "<tr>";
		echo "<td height='25' background='admin/imagens_admin/laranja.gif'><font color='#FFFFFF'><B>Informa��es atualizadas</B></font>";
		echo "</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td bgcolor='#E4B998'>Mantenha as informa��es sobre seu posto atualizadas!! <a href='http://www.telecontrol.com.br/assist/posto_cadastro.php'>Clique aqui</a> para adicionar ou alterar alguma informa��o.";
		echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "<BR>";
	echo "<BR>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td  valign='bottom'>";



echo "<table width='350' border='0' align='left' cellpadding='5' cellspacing='1' style='font-family: verdana; font-size: 12px; background-color: #CD4444'>";
	echo "<tr>";
	echo "<td width='350' height='25' background='admin/imagens_admin/vermelho.gif'><font color='#FFFFFF'><B>Acesso R�pido</B></font></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td bgcolor='#F0CECE'>";
		echo "<table width='95%' border='0' align='center' cellpadding='5' cellspacing='0' style='font-family: verdana; font-size: 12px'>";
		echo "<tr>";
		echo "<td width='50%' valign='top' bgcolor='#F0CECE'> ";
		echo "<B>Cadastrar</B><BR>";
		echo "<a href='http://www.telecontrol.com.br/assist/os_cadastro.php' target='_blank'>Ordem Servi�o</a><BR>";
		echo "<a href='http://www.telecontrol.com.br/assist/pedido_cadastro.php' target='_blank'>Pedidos de Pe�a</a><BR>";
		echo "<a href='http://www.telecontrol.com.br/assist/os_revenda.php' target='_blank'>O.S. Revenda</a><BR>";
		echo "<a href='http://www.telecontrol.com.br/assist/consumidor_cadastro.php' target='_blank'>Consumidor</a><BR>";
		echo "<a href='http://www.telecontrol.com.br/assist/revenda_cadastro.php' target='_blank'>Revenda</a><BR>";
		echo "<a href='http://www.telecontrol.com.br/assist/os_relatorio.php' target='_blank'>Status OS</a><BR> ";
		echo "</td>";
		echo "<td width='50%' valign='top' bgcolor='#F0CECE'> ";
		echo "<B>Consultar</b><BR>";
		echo "<a href='http://www.telecontrol.com.br/assist/os_consulta_lite.php' target='_blank'>Ordem Servi�o</a><BR>";
		echo "<a href='http://www.telecontrol.com.br/assist/pedido_relacao.php' target='_blank'>Pedidos de Pe�a</a><BR>";
		echo "<a href='http://www.telecontrol.com.br/assist/menu_pedido.php#pendencia_relacao.php' target='_blank'>Pend�ncia de Pe�a</a><BR>";
		echo "<a href='http://www.telecontrol.com.br/assist/os_revenda_consulta_lite.php' target='_blank'>O.S. Revenda</a><BR>";
		echo "<a href='http://www.telecontrol.com.br/assist/nf_relacao_britania.php' target='_blank'>Nota Fiscal</a><BR> ";
		echo "</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td colspan='2' align='center' colspan='2'  bgcolor='#F0CECE'> ";
		echo "<B><a href='http://www.telecontrol.com.br/assist/os_fechamento.php' target='_blank'>Fechar Ordem Servi�o</a></B>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
	echo "</td>";
	echo "</tr>";
	echo "</table>"; 
echo "<BR>";




/*
echo "<table width='350' style='font-family: verdana; font-size: 12px; background-color: #485989' border='0' align='left' cellpadding='5' cellspacing='1'>";
	echo "<tr>";
	echo "<td height='25' background='admin/imagens_admin/azul.gif'><font color='#FFFFFF'><B>E-mail atualizado</B></font>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td bgcolor='#DBE5F5'>Por favor confirme seu E-MAIL no campo abaixo.<br>
		Ap�s receber um e-mail da Telecontrol, clique no link e confirme seu e-mail.<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;E-mail:&nbsp;&nbsp;
		<input type='text' size='20' maxlength='20' name='email' value=''>
		&nbsp;&nbsp;
		<input type='submit' name='btn_email' value='Confirmar'>";
	echo "</td>";
	echo "</tr>";
echo "</table>";*/

echo "</td>";
echo "</tr>";


echo "</table>";
echo "</td>";
echo "</tr>";
echo "</table>";
include "rodape_lay_novo.php";
 

?>
