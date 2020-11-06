<?php
include "dbconfig.php";
include "includes/dbconnect-inc.php";
include 'autentica_usuario.php';
include_once "../class/tdocs.class.php";
$tDocs = new TDocs($con, $login_fabrica);

#include 'cabecalho_pop_pecas.php';
header("Expires: 0");
header("Cache-Control: no-cache, public, must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache, public");

	$caminho = "imagens_pecas";
	if($login_fabrica<>3 and $login_fabrica<>10 ){
	$caminho = $caminho."/".$login_fabrica;

	}


$ajax = $_GET['ajax'];
if(strlen($ajax)>0){
$arquivo = $_GET['arquivo'];
	echo "<table align='center'>";
	echo "<tr>";
	echo "<td align='right'><a href=\"javascript:escondePeca();\"><FONT size='1' color='#FFFFFF'><B>FECHAR</B></font></a></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align='center'>";
	echo "<a href=\"javascript:escondePeca();\">";

	$idpeca = $_GET['idpeca'];

	$xpecas  = $tDocs->getDocumentsByRef($idpeca, "peca");
	if (!empty($xpecas->attachListInfo)) {

		$a = 1;
		foreach ($xpecas->attachListInfo as $kFoto => $vFoto) {
		    $fotoPeca = $vFoto["link"];
		    if ($a == 1){break;}
		}
		echo "<img src='$fotoPeca' border='0'>";
	} else {
		echo "<img src='$caminho/media/$arquivo' border='0'>";
	}

	echo "</a>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	exit;

}


$exibe_mensagem = 't';
if (strpos($_GET['exibe'],'pedido') !== false) $exibe_mensagem = 'f';

# verifica se posto pode ver pecas de itens de aparencia
$sql = "SELECT   tbl_posto_fabrica.item_aparencia,
	         tbl_posto_fabrica.tabela
	FROM     tbl_posto
	JOIN     tbl_posto_fabrica USING(posto)
	WHERE    tbl_posto.posto           = $login_posto
	AND      tbl_posto_fabrica.fabrica IN (".implode(",", $fabricas).") ";
$res = pg_exec ($con,$sql);

if (pg_numrows ($res) > 0) {
	$item_aparencia = pg_result($res,0,item_aparencia);
	$tabela         = pg_result($res,0,tabela);
}

/*Modificado por Fernando
Pedido de Leandro da Tectoy por E-mail. Modifica��o foi feita para que os postos
que n�o podem fazer pedido em garantia (OS) de pe�as, cadastradas como item aparencia, possa
fazer pedido faturado atrav�s da tela "pedido_cadastro.php".
*/
##### INICIO ######
if($login_fabrica == 6){
	$faz_pedido = $_GET['exibe'];
	
	if(ereg("pedido_cadastro.php", $faz_pedido)){
		$item_aparencia = 't';
	}

	if(ereg("os_item_new.php", $faz_pedido)){
			$libera_bloqueado = 't';
	}
}
if($login_fabrica == 3){
	$faz_pedido = $_GET['exibe'];
	if(ereg("os_item_new.php", $faz_pedido)){
			$libera_bloqueado = 't';
	}
}
##### FIM ######

?>

<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title> Pesquisa Pe�as pela Lista B�sica ... </title>
<meta name="Author" content="">
<meta name="Keywords" content="">
<meta name="Description" content="">
<meta http-equiv=pragma content=no-cache>
<link href="css/posicionamento.css" rel="stylesheet" type="text/css" />
</head>
<style>.Div{
	BORDER-RIGHT:     #6699CC 1px solid; 
	BORDER-TOP:       #6699CC 1px solid; 
	BORDER-LEFT:      #6699CC 1px solid; 
	BORDER-BOTTOM:    #6699CC 1px solid; 
	FONT:             10pt Arial ;
	COLOR:            #000;
	BACKGROUND-COLOR: #FfFfFF;
}</style>
<script>
function onoff(id) {
var el = document.getElementById(id);
el.style.display = (el.style.display=="") ? "none" : "";
}
function createRequestObject(){
	var request_;
	var browser = navigator.appName;
	if(browser == "Microsoft Internet Explorer"){
		 request_ = new ActiveXObject("Microsoft.XMLHTTP");
	}else{
		 request_ = new XMLHttpRequest();
	}
	return request_;
}
	
function escondePeca(){
	if (document.getElementById('div_peca')){
		var style2 = document.getElementById('div_peca'); 
		if (style2==false) return; 
		if (style2.style.display=="block"){
			style2.style.display = "none";
		}else{
			style2.style.display = "block";
		}
	}
}
function mostraPeca(arquivo, peca) {
//alert(arquivo);
var el = document.getElementById('div_peca');
	el.style.display = (el.style.display=="") ? "none" : "";
	imprimePeca(arquivo, peca);

}
var http3 = new Array();
function imprimePeca(arquivo, peca){

	var curDateTime = new Date();
	http3[curDateTime] = createRequestObject();

	url = "peca_pesquisa_lista_new.php?ajax=true&idpeca="+peca+"&arquivo="+ arquivo;
	http3[curDateTime].open('get',url);
	var campo = document.getElementById('div_peca');
	Page.getPageCenterX();
	campo.style.top = (Page.top + Page.height/2)-160;
	campo.style.left = Page.width/2-220;
	http3[curDateTime].onreadystatechange = function(){
		if(http3[curDateTime].readyState == 1) {
			campo.innerHTML = "<font size='1' face='verdana'>Aguarde..</font>";
		}
		if (http3[curDateTime].readyState == 4){
			if (http3[curDateTime].status == 200 || http3[curDateTime].status == 304){

				var results = http3[curDateTime].responseText;
				campo.innerHTML   = results;
			}else {
				campo.innerHTML = "Erro";
			}
		}
	}
	http3[curDateTime].send(null);

}
var Page = new Object();
Page.width;
Page.height;
Page.top;

Page.loadOut = function (){
	document.getElementById('div_peca').innerHTML ='';	
}
Page.getPageCenterX = function (){
	var fWidth;
	var fHeight;		
	//For old IE browsers 
	if(document.all) { 
		fWidth = document.body.clientWidth; 
		fHeight = document.body.clientHeight; 
	} 
	//For DOM1 browsers 
	else if(document.getElementById &&!document.all){ 
			fWidth = innerWidth; 
			fHeight = innerHeight; 
		} 
		else if(document.getElementById) { 
				fWidth = innerWidth; 
				fHeight = innerHeight; 		
			} 
			//For Opera 
			else if (is.op) { 
					fWidth = innerWidth; 
					fHeight = innerHeight; 		
				} 
				//For old Netscape 
				else if (document.layers) { 
						fWidth = window.innerWidth; 
						fHeight = window.innerHeight; 		
					}
	Page.width = fWidth;
	Page.height = fHeight;
	Page.top = window.document.body.scrollTop;
}
</script>
<body leftmargin="0" >
<!--onblur="setTimeout('window.close()',10000);"-->
<br>

<img src="../imagens/pesquisa_pecas.gif">

<?
$tipo = trim (strtolower ($_GET['tipo']));


$produto = $_GET['produto'];
if ($login_fabrica == 11) $produto = "";

if (strlen ($produto) > 0) {
	$produto_referencia = trim($_GET['produto']);
	$produto_referencia = str_replace(".","",$produto_referencia);
	$produto_referencia = str_replace(",","",$produto_referencia);
	$produto_referencia = str_replace("-","",$produto_referencia);
	$produto_referencia = str_replace("/","",$produto_referencia);
	$produto_referencia = str_replace(" ","",$produto_referencia);

	$voltagem = trim(strtoupper($_GET["voltagem"]));

	$sql = "SELECT tbl_produto.produto, tbl_produto.descricao
			FROM   tbl_produto
			JOIN   tbl_linha USING (linha)
			WHERE  UPPER(tbl_produto.referencia_pesquisa) = UPPER('$produto_referencia') ";

	if (strlen($voltagem) > 0 AND $login_fabrica == "1" ) $sql .= " AND UPPER(tbl_produto.voltagem) = UPPER('$voltagem') ";

	$sql .= "AND    tbl_linha.fabrica IN (".implode(",", $fabricas).") ";

	if($login_fabrica <> 3)	$sql .= "AND    tbl_produto.ativo IS TRUE";

//if ($ip == '201.13.179.45') echo ($sql); echo ($produto_referencia); echo ($produto);

	$res = pg_exec ($con,$sql);

	if (pg_numrows($res) > 0) {
		$produto_descricao = pg_result ($res,0,descricao);
		$produto = pg_result ($res,0,produto);
	}
}
$cond_produto =" 1=1 ";
if($login_fabrica <> 3 ) $cond_produto = " tbl_produto.ativo IS TRUE " ;

if ($tipo == "tudo") {
	$descricao = trim(strtoupper($_GET["descricao"]));

	echo "<h4>Pesquisando toda a lista b�sica do produto: <br><i>$produto_referencia - $produto_descricao</i></h4>";

	echo "<br><br>";

	$res = pg_exec ($con,"SELECT COUNT(*) FROM tbl_lista_basica WHERE tbl_lista_basica.fabrica = $login_fabrica");
	$qtde = pg_result($res,0,0);



	if ($qtde > 0 AND strlen($produto) > 0) {
		$sql =	"SELECT z.peca                                ,
						z.referencia       AS peca_referencia ,
						z.descricao        AS peca_descricao  ,
						z.bloqueada_garantia                  ,
						z.type                                ,
						z.posicao                             ,
						z.peca_fora_linha                     ,
						z.de                                  ,
						z.para                                ,
						z.peca_para                           ,
						tbl_peca.descricao AS para_descricao  ,
						z.libera_garantia
				FROM (
						SELECT  y.peca               ,
								y.referencia         ,
								y.descricao          ,
								y.bloqueada_garantia ,
								y.type               ,
								y.posicao            ,
								y.peca_fora_linha    ,
								tbl_depara.de        ,
								tbl_depara.para      ,
								tbl_depara.peca_para,
								y.libera_garantia
						FROM (
								SELECT  x.peca                                      ,
										x.referencia                                ,
										x.descricao                                 ,
										x.bloqueada_garantia                        ,
										x.type                                      ,
										x.posicao                                   ,
										tbl_peca_fora_linha.peca AS peca_fora_linha,
										tbl_peca_fora_linha.libera_garantia
								FROM (
										SELECT  tbl_peca.peca            ,
												tbl_peca.referencia      ,
												tbl_peca.descricao       ,
												tbl_peca.bloqueada_garantia,
												tbl_lista_basica.type    ,
												tbl_lista_basica.posicao
										FROM tbl_peca
										JOIN tbl_lista_basica USING (peca)
										JOIN tbl_produto      USING (produto) ";
										if($login_fabrica == 20 AND $login_pais <>'BR') $sql .= "JOIN tbl_tabela_item ON tbl_tabela_item.peca = tbl_peca.peca AND tabela = $tabela ";
										$sql .= " WHERE tbl_peca.fabrica = $login_fabrica
										AND   tbl_produto.produto = $produto
										AND   tbl_peca.ativo IS TRUE
										AND $cond_produto 
										AND   tbl_peca.produto_acabado IS NOT TRUE"; 
										if ($login_fabrica == 14) $sql .= " AND tbl_lista_basica.ativo IS NOT FALSE";
										if (strlen($descricao) > 0) $sql .= " AND ( UPPER(TRIM(tbl_peca.descricao)) ILIKE UPPER(TRIM('%$descricao%')) OR UPPER(TRIM(tbl_peca.referencia)) ILIKE UPPER(TRIM('%$descricao%')) )";
										if ($item_aparencia == 'f') $sql .= " AND tbl_peca.item_aparencia IS FALSE";
										$sql .= "					) AS x
								LEFT JOIN tbl_peca_fora_linha ON tbl_peca_fora_linha.peca = x.peca
							) AS y
						LEFT JOIN tbl_depara ON tbl_depara.peca_de = y.peca
					) AS z
				LEFT JOIN tbl_peca ON tbl_peca.peca = z.peca_para
				ORDER BY z.descricao";
	}else{
		$sql = "SELECT z.peca                                ,
						z.referencia       AS peca_referencia ,
						z.descricao        AS peca_descricao  ,
						z.bloqueada_garantia                  ,
						z.peca_fora_linha                     ,
						z.de                                  ,
						z.para                                ,
						z.peca_para                           ,
						tbl_peca.descricao AS para_descricao  ,
						z.libera_garantia
				FROM (
						SELECT  y.peca               ,
								y.referencia         ,
								y.descricao          ,
								y.bloqueada_garantia ,
								y.peca_fora_linha    ,
								tbl_depara.de        ,
								tbl_depara.para      ,
								tbl_depara.peca_para,
								y.libera_garantia
						FROM (
								SELECT  x.peca                                      ,
										x.referencia                                ,
										x.descricao                                 ,
										x.bloqueada_garantia                        ,
										tbl_peca_fora_linha.peca AS peca_fora_linha ,
										tbl_peca_fora_linha.libera_garantia
								FROM (
										SELECT  tbl_peca.peca       ,
												tbl_peca.referencia ,
												tbl_peca.descricao  ,
												tbl_peca.bloqueada_garantia
										FROM tbl_peca ";
										if($login_fabrica == 20 AND $login_pais <>'BR') $sql .= "JOIN tbl_tabela_item ON tbl_tabela_item.peca = tbl_peca.peca AND tabela = $tabela ";
										$sql .= " WHERE fabrica = $login_fabrica
										AND ativo IS TRUE";
		if (strlen($descricao) > 0) $sql .= " AND ( UPPER(TRIM(descricao)) ILIKE UPPER(TRIM('%$descricao%')) OR UPPER(TRIM(referencia)) ILIKE UPPER(TRIM('%$descricao%')) )";
		if ($item_aparencia == 'f') $sql .= " AND item_aparencia IS FALSE";
		$sql .= "					) AS x
								LEFT JOIN tbl_peca_fora_linha ON tbl_peca_fora_linha.peca = x.peca
							) AS y
						LEFT JOIN tbl_depara ON tbl_depara.peca_de = y.peca
					) AS z
				LEFT JOIN tbl_peca ON tbl_peca.peca = z.peca_para
				ORDER BY z.descricao";
	}
if ($ip="200.228.76.93"){ echo "$sql"; exit;}
	$res = pg_exec ($con,$sql);
	if (@pg_numrows ($res) == 0) {
		echo "<h1>Nenhuma lista b�sica de pe�as encontrada para este produto</h1>";
		echo "<script language='javascript'>";
		echo "setTimeout('window.close()',10000);";
		echo "</script>";
		exit;
	}
}
	
 	echo "<div id='div_peca' style='display:none; Position:absolute; border: 1px solid #949494;background-color: #b8b7af;width:410px; heigth:400px'>";

 	echo "</div>";
if ($tipo == "descricao") {
	$descricao = trim(strtoupper($_GET["descricao"]));

	echo "<h4>Pesquisando por <b>descri��o da pe�a</b>: <i>$descricao</i></h4>";
	echo "<p>";

	$res = pg_exec ($con,"SELECT COUNT(*) FROM tbl_lista_basica WHERE tbl_lista_basica.fabrica = $login_fabrica");
	$qtde = pg_result ($res,0,0);



	if ($qtde > 0 AND strlen($produto) > 0 ) {
		$sql =	"SELECT z.peca                                ,
						z.referencia       AS peca_referencia ,
						z.descricao        AS peca_descricao  ,
						z.bloqueada_garantia                  ,
						z.type                                ,
						z.posicao                             ,
						z.peca_fora_linha                     ,
						z.de                                  ,
						z.para                                ,
						z.peca_para                           ,
						tbl_peca.descricao AS para_descricao  ,
						z.libera_garantia
				FROM (
						SELECT  y.peca               ,
								y.referencia         ,
								y.descricao          ,
								y.bloqueada_garantia ,
								y.type               ,
								y.posicao            ,
								y.peca_fora_linha    ,
								tbl_depara.de        ,
								tbl_depara.para      ,
								tbl_depara.peca_para ,
								y.libera_garantia
						FROM (
								SELECT  x.peca                                      ,
										x.referencia                                ,
										x.descricao                                 ,
										x.bloqueada_garantia                        ,
										x.type                                      ,
										x.posicao                                   ,
										tbl_peca_fora_linha.peca AS peca_fora_linha,
										tbl_peca_fora_linha.libera_garantia
								FROM (
										SELECT  tbl_peca.peca              ,
												tbl_peca.referencia        ,
												tbl_peca.descricao         ,
												tbl_peca.bloqueada_garantia,
												tbl_lista_basica.type      ,
												tbl_lista_basica.posicao
										FROM tbl_peca
										JOIN tbl_lista_basica USING (peca)
										JOIN tbl_produto      USING (produto) ";
										if($login_fabrica == 20 AND $login_pais <>'BR') $sql .= "JOIN tbl_tabela_item ON tbl_tabela_item.peca = tbl_peca.peca AND tabela = $tabela ";
										$sql .= " WHERE tbl_peca.fabrica = $login_fabrica
										AND   tbl_produto.produto = $produto
										AND   tbl_peca.ativo IS TRUE
										AND   $cond_produto ";
										if ($login_fabrica == 14) $sql .= " AND tbl_lista_basica.ativo IS NOT FALSE";
										if (strlen($descricao) > 0) $sql .= " AND UPPER(TRIM(tbl_peca.descricao)) ILIKE UPPER(TRIM('%$descricao%'))";
										if ($item_aparencia == 'f') $sql .= " AND tbl_peca.item_aparencia IS FALSE";
										$sql .= "					) AS x
								LEFT JOIN tbl_peca_fora_linha ON tbl_peca_fora_linha.peca = x.peca
							) AS y
						LEFT JOIN tbl_depara ON tbl_depara.peca_de = y.peca
					) AS z
				LEFT JOIN tbl_peca ON tbl_peca.peca = z.peca_para
				ORDER BY z.descricao";
	}else{
		$sql =	"SELECT z.peca                                ,
						z.referencia       AS peca_referencia ,
						z.descricao        AS peca_descricao  ,
						z.bloqueada_garantia                  ,
						z.peca_fora_linha                     ,
						z.de                                  ,
						z.para                                ,
						z.peca_para                           ,
						tbl_peca.descricao AS para_descricao ,
						z.libera_garantia
				FROM (
						SELECT  y.peca               ,
								y.referencia         ,
								y.descricao          ,
								y.bloqueada_garantia ,
								y.peca_fora_linha    ,
								tbl_depara.de        ,
								tbl_depara.para      ,
								tbl_depara.peca_para ,
								y.libera_garantia
						FROM (
								SELECT  x.peca                                      ,
										x.referencia                                ,
										x.descricao                                 ,
										x.bloqueada_garantia                        ,
										tbl_peca_fora_linha.peca AS peca_fora_linha,
										tbl_peca_fora_linha.libera_garantia
								FROM (
										SELECT  tbl_peca.peca       ,
												tbl_peca.referencia ,
												tbl_peca.descricao  ,
												tbl_peca.bloqueada_garantia
										FROM tbl_peca ";
										if($login_fabrica == 20 AND $login_pais <>'BR') $sql .= "JOIN tbl_tabela_item ON tbl_tabela_item.peca = tbl_peca.peca AND tabela = $tabela ";
										$sql .= " WHERE fabrica = $login_fabrica
										AND ativo IS TRUE";
		if (strlen($descricao) > 0) $sql .= " AND UPPER(TRIM(descricao)) ILIKE UPPER(TRIM('%$descricao%'))";
		if ($item_aparencia == 'f') $sql .= " AND item_aparencia IS FALSE";
		$sql .= "					) AS x
								LEFT JOIN tbl_peca_fora_linha ON tbl_peca_fora_linha.peca = x.peca
							) AS y
						LEFT JOIN tbl_depara ON tbl_depara.peca_de = y.peca
					) AS z
				LEFT JOIN tbl_peca ON tbl_peca.peca = z.peca_para
				ORDER BY z.descricao";
	}
	$res = pg_exec($con,$sql);

//if ($ip == '201.13.179.45') echo nl2br($sql);
	if (@pg_numrows($res) == 0) {
		if ($login_fabrica == 1) {
			echo "<h2>Item '$descricao' n�o existe <br> para o produto $produto_referencia, <br> consulte a vista explodida atualizada <br> e verifique o c�digo correto</h2>";
		}else{
			if($sistema_lingua == "ES") echo "Pieza '$descricao' no encuentrada <br>para el producto $produto_referencia";
			else                        echo "<h1>Pe�a '$descricao' n�o encontrada<br>para o produto $produto_referencia</h1>";
		}
		echo "<script language='javascript'>";
		echo "setTimeout('window.close()',10000);";
		echo "</script>";
		exit;
	}
}

if ($tipo == "referencia") {
	$referencia = trim(strtoupper($_GET["peca"]));
	$referencia = str_replace(".","",$referencia);
	$referencia = str_replace(",","",$referencia);
	$referencia = str_replace("-","",$referencia);
	$referencia = str_replace("/","",$referencia);
	$referencia = str_replace(" ","",$referencia);

	echo "<BR><font face='Arial, Verdana, Times, Sans' size='2'>Pesquisando por <b>refer�ncia da pe�a</b>: <i>$referencia</i></font>";
	echo "<br><br>";

	$res = pg_exec ($con,"SELECT COUNT(*) FROM tbl_lista_basica WHERE tbl_lista_basica.fabrica = $login_fabrica");
	$qtde = pg_result ($res,0,0);

	if ($qtde > 0 and strlen($produto) > 0) {
//if ($ip == '201.0.9.216') echo "Xii<br>";
		$sql =	"SELECT z.peca                                ,
						z.referencia       AS peca_referencia ,
						z.descricao        AS peca_descricao  ,
						z.bloqueada_garantia                  ,
						z.type                                ,
						z.posicao                             ,
						z.peca_fora_linha                     ,
						z.de                                  ,
						z.para                                ,
						z.peca_para                           ,
						tbl_peca.descricao AS para_descricao  ,
						z.libera_garantia
				FROM (
						SELECT  y.peca               ,
								y.referencia         ,
								y.descricao          ,
								y.bloqueada_garantia ,
								y.type               ,
								y.posicao            ,
								y.peca_fora_linha    ,
								tbl_depara.de        ,
								tbl_depara.para      ,
								tbl_depara.peca_para,
								y.libera_garantia
						FROM (
								SELECT  x.peca                                      ,
										x.referencia                                ,
										x.descricao                                 ,
										x.bloqueada_garantia                        ,
										x.type                                      ,
										x.posicao                                   ,
										tbl_peca_fora_linha.peca AS peca_fora_linha,
										tbl_peca_fora_linha.libera_garantia
								FROM (
										SELECT  tbl_peca.peca              ,
												tbl_peca.referencia        ,
												tbl_peca.descricao         ,
												tbl_peca.bloqueada_garantia,
												tbl_lista_basica.type      ,
												tbl_lista_basica.posicao
										FROM tbl_peca
										JOIN tbl_lista_basica USING (peca)
										JOIN tbl_produto      USING (produto) ";
										if($login_fabrica == 20 AND $login_pais <>'BR') $sql .= "JOIN tbl_tabela_item ON tbl_tabela_item.peca = tbl_peca.peca AND tabela = $tabela ";
										$sql .= " WHERE tbl_peca.fabrica = $login_fabrica
										AND   tbl_produto.produto = $produto
										AND   tbl_peca.ativo IS TRUE
										AND   $cond_produto";
										if ($login_fabrica == 14) $sql .= " AND tbl_lista_basica.ativo IS NOT FALSE";
										if (strlen($referencia) > 0) $sql .= " AND UPPER(TRIM(tbl_peca.referencia_pesquisa)) ILIKE UPPER(TRIM('%$referencia%'))";
										if ($item_aparencia == 'f') $sql .= " AND tbl_peca.item_aparencia IS FALSE";
										$sql .= "					) AS x
								LEFT JOIN tbl_peca_fora_linha ON tbl_peca_fora_linha.peca = x.peca
							) AS y
						LEFT JOIN tbl_depara ON tbl_depara.peca_de = y.peca
					) AS z
				LEFT JOIN tbl_peca ON tbl_peca.peca = z.peca_para
				ORDER BY z.descricao";
	}else{
		$sql =	"SELECT z.peca                                ,
						z.referencia       AS peca_referencia ,
						z.descricao        AS peca_descricao  ,
						z.bloqueada_garantia                  ,
						z.peca_fora_linha                     ,
						z.de                                  ,
						z.para                                ,
						z.peca_para                           ,
						tbl_peca.descricao AS para_descricao  ,
						z.libera_garantia
				FROM (
						SELECT  y.peca               ,
								y.referencia         ,
								y.descricao          ,
								y.bloqueada_garantia ,
								y.peca_fora_linha    ,
								tbl_depara.de        ,
								tbl_depara.para      ,
								tbl_depara.peca_para ,
								y.libera_garantia
						FROM (
								SELECT  x.peca                                      ,
										x.referencia                                ,
										x.descricao                                 ,
										x.bloqueada_garantia                        ,
										tbl_peca_fora_linha.peca AS peca_fora_linha,
										tbl_peca_fora_linha.libera_garantia
								FROM (
										SELECT  tbl_peca.peca              ,
												tbl_peca.referencia        ,
												tbl_peca.descricao         ,
												tbl_peca.bloqueada_garantia
										FROM tbl_peca ";
										if($login_fabrica == 20 AND $login_pais <>'BR') $sql .= "JOIN tbl_tabela_item using(peca) AND tabela = $tabela ";
										$sql .= " WHERE fabrica = $login_fabrica
										AND ativo IS TRUE";
		if (strlen($referencia) > 0) $sql .= " AND UPPER(TRIM(referencia_pesquisa)) ILIKE UPPER(TRIM('%$referencia%'))";
		if ($item_aparencia == 'f') $sql .= " AND item_aparencia IS FALSE";
		$sql .= "					) AS x
								LEFT JOIN tbl_peca_fora_linha ON tbl_peca_fora_linha.peca = x.peca
							) AS y
						LEFT JOIN tbl_depara ON tbl_depara.peca_de = y.peca
					) AS z
				LEFT JOIN tbl_peca ON tbl_peca.peca = z.peca_para
				ORDER BY z.descricao";
	}

	$res = @pg_exec($con,$sql);
	//if ($ip == '200.228.76.93') echo $sql;

	if (@pg_numrows($res) == 0) {
		if ($login_fabrica == 1) {
			echo "<h2>Item '$referencia' n�o existe <br> para o produto $produto_referencia, <br> consulte a vista explodida atualizada <br> e verifique o c�digo correto</h2>";
		}else{
			echo "<h1>Pe�a '$referencia' n�o encontrada<br>para o produto $produto_referencia</h1>";
		}
		echo "<script language='JavaScript'>";
		echo "setTimeout('window.close()',10000);";
		echo "</script>";
		exit;
	}


}

echo "<script language='JavaScript'>\n";
echo "<!--\n";
echo "this.focus();\n";
echo "// -->\n";
echo "</script>\n";

$contador = 999;

for ( $i = 0 ; $i < pg_numrows($res) ; $i++ ) {
	$peca            = trim(@pg_result($res,$i,peca));
	$peca_referencia = trim(@pg_result($res,$i,peca_referencia));
	$peca_descricao  = trim(@pg_result($res,$i,peca_descricao));
	$peca_descricao  = str_replace ('"','',$peca_descricao);
	$peca_descricao  = str_replace ("'","",$peca_descricao);
	$type            = trim(@pg_result($res,$i,type));
	$posicao         = trim(@pg_result($res,$i,posicao));
	$peca_fora_linha = trim(@pg_result($res,$i,peca_fora_linha));
	$peca_para       = trim(@pg_result($res,$i,peca_para));
	$para            = trim(@pg_result($res,$i,para));
	$para_descricao  = trim(@pg_result($res,$i,para_descricao));
	$bloqueada_garantia  = trim(@pg_result($res,$i,bloqueada_garantia));
	$libera_garantia  = trim(@pg_result($res,$i,libera_garantia));
	//--=== Tradu��o para outras linguas ============================= Raphael HD:1212
	$sql_idioma = "SELECT * FROM tbl_peca_idioma WHERE peca = $peca AND upper(idioma) = '$sistema_lingua'";

	$res_idioma = @pg_exec($con,$sql_idioma);
	if (@pg_numrows($res_idioma) >0) {
		$peca_descricao  = trim(@pg_result($res_idioma,0,descricao));
	}
	//--=== Tradu��o para outras linguas ===================================================================

/*	if ($login_fabrica == 3 && getenv("REMOTE_ADDR") == "201.0.9.216") {
		$x_referencia_pesquisa = str_replace(".","",$peca_referencia);
		$x_referencia_pesquisa = str_replace("-","",$x_referencia_pesquisa);
		$x_referencia_pesquisa = str_replace(" ","",$x_referencia_pesquisa);
		$x_sql =	"SELECT COUNT(tbl_produto.produto)
					FROM tbl_produto
					JOIN tbl_linha USING (linha)
					WHERE tbl_linha.fabrica = $login_fabrica
					AND   tbl_produto.referencia_pesquisa = '$x_referencia_pesquisa';";
		$x_res = pg_exec($con,$x_sql);
		echo nl2br($x_sql)."<br>".pg_numrows($x_res);
		$produto_peca = false;
		if (pg_numrows($x_res) == 1) {
			$produto_peca = true;
		}
		
		if (isset($produto_peca)) {
		exit;
		}
	}*/

	$resT = pg_exec($con,"SELECT tabela FROM tbl_tabela WHERE fabrica = $login_fabrica AND tbl_tabela.ativa IS TRUE");

	/* IGOR - HD 9985 - 27-12-2007 - PARA MONDIAL*/
	if($login_fabrica == 5){
		$resT = pg_exec($con,"SELECT tabela 
							FROM tbl_tabela WHERE fabrica = $login_fabrica 
								AND tbl_tabela.ativa IS TRUE
								AND tbl_tabela.tabela = 23");
	}
	if (pg_numrows($resT) == 1) {
		$tabela = pg_result ($resT,0,0);
		if (strlen($para) > 0) {
			$sqlT = "SELECT preco FROM tbl_tabela_item WHERE tabela = $tabela AND peca = $peca_para";
		}else{
			$sqlT = "SELECT preco FROM tbl_tabela_item WHERE tabela = $tabela AND peca = $peca";
		}
		$resT = pg_exec($con,$sqlT);
		if (pg_numrows($resT) == 1) {
			$preco = number_format (pg_result($resT,0,0),2,",",".");
		}else{
			$preco = "";
		}
	}else{
		$preco = "";
	}


	if ($contador > 50) {
		$contador = 0 ;
		echo "</table><table width='100%' border='1'>\n";
		flush();
	}
	$contador++;



	$cor = '#ffffff';
	if (strlen($peca_fora_linha) > 0) $cor = '#FFEEEE';


	echo "<tr bgcolor='$cor'>\n";

	if ($login_fabrica == 14) {
		echo "<td nowrap>";
		echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#000000'>$posicao</font>";
		echo "</td>\n";
	}


	if ($login_fabrica == 3) {
		$sql = "SELECT tbl_linha.codigo_linha FROM tbl_linha WHERE linha = (SELECT tbl_produto.linha FROM tbl_produto JOIN tbl_lista_basica ON tbl_produto.produto = tbl_lista_basica.produto WHERE tbl_lista_basica.peca = $peca LIMIT 1)";
		$resX = pg_exec ($con,$sql);
		$codigo_linha = @pg_result ($resX,0,0);

		if (strlen ($codigo_linha) == 0) $codigo_linha = "&nbsp;";

		echo "<td nowrap>";
		echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#999999'>$codigo_linha</font>";
		echo "</td>\n";
	}

	echo "<td nowrap>";
	echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#000000'>$peca_referencia</font>";
	echo "</td>\n";

	echo "<td nowrap>";
	
	//if ((strlen($para) >0) OR (strlen($peca_fora_linha) > 0 AND  !($bloqueada_garantia == 't' AND $exibe_mensagem == 't' AND $login_fabrica == 3))) {
	//if (strlen($peca_fora_linha) > 0 OR strlen($para) > 0 OR ($bloqueada_garantia == 't' AND $exibe_mensagem == 't' AND $login_fabrica == 3)) {

	if (strlen($peca_fora_linha) > 0 OR strlen($para) > 0) {
		if (strlen($para) > 0) {
			echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#000000'>$peca_descricao</font>";
		}
		if (strlen($peca_fora_linha) > 0) {
			if($libera_garantia<>"t"){
				echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#000000'>$peca_descricao</font>";
			}else{
				if($libera_bloqueado=='t'){
					echo "<a href=\"javascript: referencia.value='$peca_referencia'; descricao.value='$peca_descricao';";
					if ($login_fabrica == 14) {
						echo " posicao.value='$posicao';";
					}else{
						echo " preco.value='$preco';";
					}
					echo " this.close();\"><font face='Arial, Verdana, Times, Sans' size='1' color='#0000FF'>$peca_descricao</font></a>";
				}else{
					echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#000000'>$peca_descricao</font>";
				}
			}
		}
	}else{
		echo "<a href=\"javascript: referencia.value='$peca_referencia'; descricao.value='$peca_descricao';";
		if ($login_fabrica == 14) {
			echo " posicao.value='$posicao';";
		}else{
			echo " preco.value='$preco';";
		}
		echo " this.close();\"><font face='Arial, Verdana, Times, Sans' size='1' color='#0000FF'>$peca_descricao</font></a>";
	}
	echo "</td>\n";

	if ($login_fabrica == 1) {
		echo "<td nowrap>";
		echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#000000'>$type</font>";
		echo "</td>\n";
	}
	
	$sqlX =	"SELECT referencia, to_char(tbl_peca.previsao_entrega,'DD/MM/YYYY') AS previsao_entrega
			FROM tbl_peca
			WHERE UPPER(referencia_pesquisa) = UPPER('$peca_referencia')
			AND   fabrica = $login_fabrica
			AND   previsao_entrega NOTNULL;";
	$resX = pg_exec($con,$sqlX);
//if ($ip == '201.0.9.216') echo " $sqlX <br>";

	if (pg_numrows($resX) == 0) {
		echo "<td nowrap>";
		if (strlen($peca_fora_linha) > 0) {
			echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#000000'><b>";
			if ($login_fabrica == 1) echo "� obsoleta,<br>n�o � mais fornecida";
			else                     echo "Fora de linha";
			echo "</b></font>";
		}else{
			if (strlen($para) > 0) {
				echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#000000'><b>Mudou Para:</b></font>";
				echo " <a href=\"javascript: referencia.value='$para'; descricao.value='$para_descricao'; preco.value='$preco'; this.close();\"><font face='Arial, Verdana, Times, Sans' size='1' color='#0000FF'>$para</font></a>";
			}else{
				echo "&nbsp;";
			}
		}
		echo "</td>\n";


	echo "<td nowrap align='right'>";
/*if ($handle = opendir('imagens_pecas/pequena/.')) {
			while (false !== ($file = readdir($handle))) {
				$contador++;
				if($contador == 1) break;
				$posicao = strpos($file, $peca_referencia);
				if ($file != "." && $file != ".." ) {
					?>
					<a href="#" onclick="onoff('teste<? echo $contador; ?>')">
					<img src="../<?echo $caminho; ?>/pequena/<? echo $file;?>">
					</a>
					<div id="teste<? echo $contador;?>" style="display:none">
					<img src="../<?echo $caminho; ?>/media/<? echo $file;?>">
					</div><br> 
					<?
				}				
			}
	closedir($handle);
}		*/
	$xpecas  = $tDocs->getDocumentsByRef($peca, "peca");
	if (!empty($xpecas->attachListInfo)) {

		$a = 1;
		foreach ($xpecas->attachListInfo as $kFoto => $vFoto) {
		    $fotoPeca = $vFoto["link"];
		    if ($a == 1){break;}
		}
		echo "<a href=\"javascript:mostraPeca('$fotoPeca','$peca')\">";
		echo "<img src='$fotoPeca' border='0'>";
		echo "</a>";
	} else {
		if ($dh = opendir("../".$caminho."/pequena/")) {
			$contador=0;
			while (false !== ($filename = readdir($dh))) {
				
				if($contador == 1) break;
				if (strpos($filename,$peca_referencia) !== false){
					$contador++;
					//$peca_referencia = ntval($peca_referencia);
					$po = strlen($peca_referencia);
					if(substr($filename, 0,$po)==$peca_referencia){
						//echo "<a href=imagens_pecas/media/$filename target='blank'>";
		/*				echo "<a href=\"#\" onclick=\"onoff('$peca_referencia')\">";
						echo "<img src='imagens_pecas/pequena/$filename' border='0'>";
						echo "</a>";
						echo "<div id='$peca_referencia' style='display:none; border: 1px solid #949494;background-color: #b8b7af;width:300px;'>";
						echo "<img src='imagens_pecas/media/$filename'>";
						echo "</div>";*/
						echo "<a href=\"javascript:mostraPeca('$filename','$peca')\">";
						echo "<img src='../$caminho/pequena/$filename' border='0'>";
						echo "</a>";

					}
					
				}
			}
		}
	}


	echo "</td>\n";

	//--=== Raphael HD: 1244 ==========================================
	if($login_fabrica == 3 AND $peca == '526199' ){
		/*echo "<td nowrap>";
		echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#000000'><img src='admin/imagens_admin/justificativa.gif' onMouseOver=\"javascript:var com = document.getElementById('justificativa');com.style.visibility = 'visible';\" onMouseOut=\"javascript:var com = document.getElementById('justificativa');com.style.visibility = 'hidden';\"><div id='justificativa' style='visibility : hidden; position:absolute; width:201px; left: 250px;opacity:.75;' class='Div'  ><img src='imagens_pecas/526199.gif' width='200'height='150'></div></font>";
		echo "</td>\n";*/
		echo "</tr>";
		echo "<tr>";
		echo "<td colspan='4'align='center'><img src='imagens_pecas/526199.gif' class='Div' >";
		//echo "<div id='justificativa' style='visibility : hidden; position:absolute; width:400px; left: 0px;opacity:.85;' class='Div' ><img src='imagens_pecas/526199.gif' onClick=\"javascript:var com = document.getElementById('justificativa');com.style.visibility = 'hidden';\"></div>";
		echo "</td>\n";

	}
		
	}else{
		echo "</tr>\n";
		echo "<tr>\n";
		$peca_previsao    = pg_result($resX,0,0);
		$previsao_entrega = pg_result($resX,0,1);
		
		$data_atual         = date("Ymd");
		$x_previsao_entrega = substr($previsao_entrega,6,4) . substr($previsao_entrega,3,2) . substr($previsao_entrega,0,2);
		echo "<td colspan='2'>\n";
		if ($data_atual < $x_previsao_entrega) {
		echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#000000'><b>";
//		echo "N�o h� previs�o de chegada da Pe�a C�digo $peca_previsao.<br>Favor encaminhar e-mail para <a href='mailto:assistenciatecnica@britania.com.br'>assistenciatecnica@britania.com.br</a>, informando o n�mero da Ordem de Servi�o e o c�digo do Posto Autorizado.<br>Somente ser�o aceitas requisi��es via email! N�O utilizar o 0800.";
		echo "Esta pe�a estar� dispon�vel em $previsao_entrega";
		echo "<br>";
		echo "Para as pe�as com prazo de fornecimento superior a 25 dias, a f�brica tomar� as medidas necess�rias para atendimento do consumidor";
		echo "</b></font>";
		}
		echo "</td>\n";
	}

	echo "</tr>\n";

	if ($exibe_mensagem == 't' AND $bloqueada_garantia == 't' and $login_fabrica == 3){
		echo "<tr>\n";
		echo "<td colspan='5'>\n";
		echo "<font face='Arial, Verdana, Times, Sans' size='1' color='#000000'><b>";
		//echo "A pe�a $referencia necessita de autoriza��o da Brit�nia para atendimento em garantia. Para libera��o desta pe�a, favor enviar e-mail para <a href=\"mailto:assistenciatecnica@britania.com.br\">assistenciatecnica@britania.com.br</A>, informando a OS e a justificativa.";
		echo "A pe�a $referencia necessita de autoriza��o da Brit�nia para atendimento em garantia.";
		echo "</b></font>";
		echo "</td>\n";
		echo "</tr>\n";
	}
			/* takashi alterou 05-04-2007 hd1819*/
	if (@pg_numrows ($res) == 1 and $login_fabrica==24) {
	
		echo "<script language='JavaScript'>\n";
		echo "referencia.value='$peca_referencia';";
		echo " descricao.value='$peca_descricao';";
		echo "this.close();";
		echo "</script>\n";
	}
}

echo "</table>\n";
?>

</body>
</html>
