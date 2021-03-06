<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'autentica_usuario.php';
$title = "Menu Assistencia Técnica";
$layout_menu = 'tecnica';

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



//--==================== ALTERAÇÕES TÉCNICAS ====================--\\
$sql = "SELECT count(comunicado)AS total_atualizacoes 
		FROM tbl_comunicado 
		WHERE ativo IS NOT FALSE
		AND tipo = 'Atualização de Software'
		AND (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
		AND fabrica    = $login_fabrica";
$res = pg_exec ($con,$sql);
if (pg_numrows($res) > 0) {
	$total_atualizacoes  = trim(pg_result ($res,0,total_atualizacoes));
}

//--===============================================================--\\

include "cabecalho.php";
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



<?if($login_fabrica == 3){?>

<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
	<tr bgcolor = '#efefef'>
		<td rowspan='3' width='20' valign='top'><img src='imagens/marca25.gif'></td>
		<td  class="chapeu" colspan='2' >Atualização de Software</td>
	</tr>
	<tr bgcolor = '#efefef'><td colspan='2' height='5' class='detalhes'>Escolha abaixo a família do produto que deseja consultar as Atualizações de Software.</td></tr>
	<tr bgcolor = '#efefef'>
		<td valign='top' class='menu' colspan='3'>
<?

if ($S3_sdk_OK) {
	include_once S3CLASS;
	$s3 = new anexaS3('ve', (int) $login_fabrica);
}

if($total_atualizacoes > 50){
	$sql = "SELECT DISTINCT tbl_familia.familia                                  ,
							tbl_familia.descricao                                ,
							tbl_linha.linha                                      ,
							tbl_linha.nome                                       
			FROM    tbl_comunicado 
			JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
			JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha       
			LEFT JOIN    tbl_familia ON tbl_produto.familia = tbl_familia.familia
			WHERE   tbl_linha.fabrica    = $login_fabrica
			AND     tbl_comunicado.ativo IS NOT FALSE
			AND     tbl_comunicado.tipo = 'Atualização de Software'
			AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
			AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)

		ORDER BY tbl_linha.nome, tbl_familia.descricao";

	$res = pg_exec ($con,$sql);
	
	if (pg_numrows($res) > 0) {
		$linha_anterior = "";
		echo "<dl>";
		for ($i = 0 ; $i < pg_numrows($res) ; $i++) {
			$descricao  = trim(pg_result ($res,$i,descricao));
			$familia    = trim(pg_result ($res,$i,familia))  ;
			$nome       = trim(pg_result ($res,$i,nome))     ;
			$linha      = trim(pg_result ($res,$i,linha))    ;
	
			if($linha_anterior <> $linha) {
				echo "<br><dt>&nbsp;&nbsp;<b>»</b> <a href='info_tecnica_visualiza.php?tipo=Atualização de Software&linha=$linha'>$nome</a><br></dt>";
			}
			echo "<dd>&nbsp;&nbsp;<b>»</b> <a href='info_tecnica_visualiza.php?tipo=Atualização de Software&linha=$linha&familia=$familia'>$descricao</a><br></dd>";
			$linha_anterior = $linha;

		}
	}else{ echo "<br><dt>&nbsp;&nbsp;<b>»</b> Nenhum Cadastrado<br></dt>";}

}else{

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
			AND    tbl_comunicado.ativo IS NOT FALSE
			AND	tbl_comunicado.tipo = 'Atualização de Software' 
			AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
			AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)

			ORDER BY tbl_produto.descricao DESC, tbl_produto.referencia " ;
//	echo $sql;
	$res = pg_exec ($con,$sql);
	if (pg_numrows ($res) > 0) {
		$linha_anterior = "";
		//echo "<dl>";
		for ($i = 0 ; $i < pg_numrows($res) ; $i++) {
			$Xcomunicado           = trim(pg_result($res,$i,comunicado));
			$produto               = trim(pg_result ($res,$i,produto));
			$referencia            = trim(pg_result ($res,$i,referencia));
			$descricao             = trim(pg_result ($res,$i,descricao_produto));
			$comunicado_descricao  = trim(pg_result ($res,$i,descricao));

			if ($S3_online) {
				$tipo_s3 = in_array($comunicado_tipo, explode(',', utf8_decode(anexaS3::TIPOS_VE))) ? 've' : 'co';
				if ($s3->tipo_anexo != $tipo_s3)
					$s3->set_tipo_anexoS3($tipo_s3);
				$s3->temAnexos($Xcomunicado);
				if ($s3->temAnexo)
					echo "<br><b>-»</b>\n<a href=''" . $s3->url . "' target='_blank'>";
			} else {
				$gif = "comunicados/$Xcomunicado.gif";
				$jpg = "comunicados/$Xcomunicado.jpg";
				$pdf = "comunicados/$Xcomunicado.pdf";
				$doc = "comunicados/$Xcomunicado.doc";
				$rtf = "comunicados/$Xcomunicado.rtf";
				$xls = "comunicados/$Xcomunicado.xls";
				$ppt = "comunicados/$Xcomunicado.ppt";
				$zip = "comunicados/$Xcomunicado.zip";

				//echo "<br><dd>&nbsp;&nbsp;<b>-»</b> ";
				echo "<br><b>-»</b> ";
				if (file_exists($gif) == true) echo "<a href='comunicados/$Xcomunicado.gif' target='_blank'>$";
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
				if (strlen ($comunicado_descricao) > 0){
					echo " - $comunicado_descricao";
				}
			}else{
				echo $comunicado_descricao;
			}

			echo "</a><br>";
		}
		echo "<br>";
	}else{ echo "<br><b>»</b> Nenhum Cadastrado<br>";}
}
?>
<br>
		</td>
	</tr>
	<tr bgcolor='#D9E2EF'>
	<td colspan='3'><img src='imagens/spacer.gif' height='3'></td>
</tr>
</table><br>


<?}?>



</body>
</html>
