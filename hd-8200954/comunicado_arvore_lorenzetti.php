<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'autentica_usuario.php';

//--==================== TIPO POSTO ====================--\\
$sql = "SELECT tbl_posto_fabrica.codigo_posto        ,
				tbl_posto_fabrica.tipo_posto       
		FROM	tbl_posto
		LEFT JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_posto.posto
		WHERE   tbl_posto_fabrica.fabrica = $login_fabrica
		AND     tbl_posto.posto   = $login_posto ";
$res= pg_exec ($con,$sql);
if (pg_numrows ($res) > 0) {
	$tipo_posto            = trim(pg_result($res,0,tipo_posto));
}

include "cabecalho.php";

if ($S3_sdk_OK) {
	include_once S3CLASS;

	$s3 = new anexaS3('ve', (int) $login_fabrica);
	$S3_online = is_object($s3);
}
?>
<style>

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

<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
	<tr bgcolor = '#fafafa'>
		<td rowspan='3' width='20' valign='top'><img src='imagens/marca25.gif'></td>
		<td  class="chapeu" colspan='2' >Comunicados</td>
	</tr>
	<tr bgcolor = '#fafafa'><td colspan='2' height='5'></td></tr>
	<tr bgcolor = '#fafafa'>
		<td valign='top' class='menu'>
<?

$sql = "
		SELECT tbl_comunicado.comunicado,
			   tbl_comunicado.descricao,
			   tbl_comunicado.mensagem,
			   tbl_produto.produto,
			   tbl_produto.referencia,
			   tbl_produto.descricao                     AS descricao_produto,
			   TO_CHAR(tbl_comunicado.data,'dd/mm/yyyy') AS data
		  FROM tbl_comunicado
	 LEFT JOIN tbl_produto USING (produto)
		 WHERE tbl_comunicado.fabrica     = $login_fabrica
		   AND (tbl_comunicado.tipo_posto =  $tipo_posto   OR tbl_comunicado.tipo_posto IS NULL)
		   AND ((tbl_comunicado.posto     =  $login_posto) OR (tbl_comunicado.posto     IS NULL))
		   AND tbl_comunicado.ativo       IS TRUE
		   AND (tbl_comunicado.tipo       =  'Comunicado'  OR tbl_comunicado.tipo       =  'Comunicado Inicial')
		   AND (tbl_comunicado.tipo_posto =  $tipo_posto   OR tbl_comunicado.tipo_posto IS NULL)
		   AND (tbl_comunicado.posto      IS NULL          OR tbl_comunicado.posto      =  $login_posto)
		 ORDER BY tbl_comunicado.data DESC" ;
//	hd 3032		ORDER BY tbl_comunicado.descricao,tbl_produto.descricao DESC, tbl_produto.referencia " ;
//	echo $sql;
$res = pg_exec ($con,$sql);
if (pg_numrows ($res) > 0) {
	$linha_anterior = "";
	echo "<dl>";
	for ($i = 0 ; $i < pg_numrows($res) ; $i++) {
		$Xcomunicado           = trim(pg_result($res,$i,comunicado));
		$produto               = trim(pg_result ($res,$i,produto));
		$referencia            = trim(pg_result ($res,$i,referencia));
		$descricao             = trim(pg_result ($res,$i,descricao_produto));
		$comunicado_descricao  = trim(pg_result ($res,$i,descricao));

		echo "<br><dd>&nbsp;&nbsp;<b>-�</b> ";
		if ($S3_online and $s3->temAnexos($comunicado)) {
			$tipo_s3 = in_array($tipo_comunicado, explode(',', utf8_decode(anexaS3::TIPOS_VE))) ? 've' : 'co';

			if ($tipo_s3 != $s3->tipo_anexo)
				$s3->set_tipo_anexoS3($tipo_s3);

			echo "<a href='" . $s3->url . "' target='_blank'>";

		} else {
			$gif = "comunicados/$Xcomunicado.gif";
			$jpg = "comunicados/$Xcomunicado.jpg";
			$pdf = "comunicados/$Xcomunicado.pdf";
			$doc = "comunicados/$Xcomunicado.doc";
			$rtf = "comunicados/$Xcomunicado.rtf";
			$xls = "comunicados/$Xcomunicado.xls";
			$ppt = "comunicados/$Xcomunicado.ppt";
			$zip = "comunicados/$Xcomunicado.zip";

			if (file_exists($gif) == true) echo "<a href='comunicados/$Xcomunicado.gif' target='_blank'>";
			if (file_exists($jpg) == true) echo "<a href='comunicados/$Xcomunicado.jpg' target='_blank'>";
			if (file_exists($cod) == true) echo "<a href='comunicados/$Xcomunicado.cod' target='_blank'>";
			if (file_exists($xls) == true) echo "<a href='comunicados/$Xcomunicado.xls' target='_blank'>";
			if (file_exists($rtf) == true) echo "<a href='comunicados/$Xcomunicado.rtf' target='_blank'>";
			if (file_exists($xls) == true) echo "<a href='comunicados/$Xcomunicado.xls' target='_blank'>";
			if (file_exists($pdf) == true) echo "<a href='comunicados/$Xcomunicado.pdf' target='_blank'>";
			if (file_exists($ppt) == true) echo "<a href='comunicados/$Xcomunicado.ppt' target='_blank'>";
			if (file_exists($zip) == true) echo "<a href='comunicados/$Xcomunicado.zip' target='_blank'>";
		}
		
		if(strlen($referencia)>0) echo "$referencia - ";
		
		if (strlen ($descricao) > 0) {
			echo $descricao;
		}else{
			if(strlen($comunicado_descricao)==0){
				echo "Comunicado Sem t�tulo";
			}else{
				echo $comunicado_descricao;
			}
		}

		echo"</a></dd>";
	}
	echo "<br>";
}else{ 
	echo "<br><dt>&nbsp;&nbsp;<b>�</b> Nenhum Cadastrado<br></dt>";
}?>
<br>
		</td>
		<td rowspan='2'class='detalhes' width='1'></td>
	</tr>
	<tr bgcolor='#D9E2EF'>
	<td colspan='3'><img src='imagens/spacer.gif' height='3'></td>
</tr>
</table><br>
</body>
</html>
