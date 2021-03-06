<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';

$numero_pedido = $_GET['pedido'];
$status        = $_GET['status'];
$promocoes     = $_GET['promocoes'];

if (strlen($cook_fabrica)==0 AND strlen($cook_login_unico)>0){
	include 'login_unico_autentica_usuario.php';
	$login_fabrica = 10;
}elseif (strlen($cook_fabrica)==0 AND strlen($cook_login_simples)>0){
	include 'login_simples_autentica_usuario.php';
}else{
	include 'autentica_usuario.php';
}

session_name("carrinho");
session_start();

#$indice = $_SESSION[cesta][numero];

$layout_menu = 'pedido';
$title="BEM-VINDO a loja virtual";

// HD15225 // Samuel colocou para validar somente para fabrica brit�nia em 10/07/2008
if($login_fabrica==3){
	$sql = "SELECT pedido_faturado 
			FROM tbl_posto_fabrica 
			WHERE posto = $login_posto 
				AND fabrica = $login_fabrica";
	$res = pg_exec ($con,$sql);
	if (pg_result ($res,0,0) == 'f') {
		include "cabecalho.php";
		echo "<H4>CADASTRO DE PEDIDOS FATURADOS BLOQUEADO</H4>";
		include "rodape.php";
		exit;
	}
}
if (strlen($cook_fabrica)==0 AND ( strlen($cook_login_unico)>0 OR strlen($cook_login_simples)>0)) {
	include "login_unico_cabecalho.php";
}else{
	include "cabecalho.php";
}

/*	Fun��o para redimensionar imagens
	Recebe o tamanho w X h da imagem,
	Devolve uma string com o c�digo HTML
	para adicionar no tag IMG:
		width='xx' height='xx'				*/
	function ReDimImg($w, $h, $limite) {
		if(($w<$limite and $h<$limite) or
			($w>$limite or $h>$limite)) {
			return "WIDTH='".$w."' HEIGHT='".$h."'";
		} else {
			if ($w > $h) {
				$h = intval(($limite / $w) * $h);
				$w = $limite;
			}else{
				$w = intval(($limite / $h) * $w);
				$h = $limite;
			}
		}
		return "WIDTH='".$w."' HEIGHT='".$h."'";
	}

?>

<DIV style='border: 1px solid #D3BE96; background-color: #FCF0D8;
			position:absolute;top:0;right:5px;opacity:.9;
			overflow:auto;z-index:1;' id='mensagem'>
	Carregando dados...
</DIV>

<script language='javascript'>
	function checarNumero(campo){
		var num = campo.value.replace(",",".");
		campo.value = parseInt(num);
		if (campo.value=='NaN') {
			campo.value='';
		}
	}
</script>

<style type="text/css">
	ul#intro,ul#intro li{list-style-type:none;margin:0;padding:0}
	ul#intro{width:100%;overflow:hidden;margin-bottom:10px}
	ul#intro li{float:left;width:98%;margin-right:10px;padding: 5px 5;}
	li#produto{background: #CEDFF0}
	ul#intro li#more{margin-right:0;background: #7D63A9}
	ul#intro p,ul#intro h3{margin:0;padding: 0 10px}

	ul#intro2,ul#intro2 li{list-style-type:none;margin:0;padding:0}
	ul#intro2{width:100%;overflow:hidden;margin-bottom:10px}
	ul#intro2 li{float:left;width:98%;margin-right:10px;padding: 5px 5;}
	li#infor{text-align:left;background-image: url('imagens/barra_dg_azul_tc_30.jpg');color:#FFFFFF;color:#FFFFFF;}
	ul#intro2 li#more{margin-right:0;background: #7D63A9}
	ul#intro p,ul#intro2 h3{margin:0;padding: 0 10px}

	ul#intro3,ul#intro3 li{list-style-type:none;margin:0;padding:0}
	ul#intro3{width:100%;overflow:hidden;margin-bottom:10px}
	ul#intro3 li{float:left;width:98%;margin-right:10px;padding: 5px 5;}
	li#maisprod{background: #FFBA75}
	ul#intro3 li#more{margin-right:0;background: #7D63A9}
	ul#intro p,ul#intro3 h3{margin:0;padding: 0 10px}

	/* Estilos para os elementos de cada produto - MLG */
	.linkproduto:link, .linkproduto:visited {
		width:150px;
		text-decoration: none;
		color: #5F5F5F;
		font-weight: bold;
		<?php if ($login_fabrica == 3) echo "font-size: 10px;\n";?>
	}

	.descrproduto {
		font-family: arial, freesans, garuda, helvetica, verdana, sans-serif;
		padding: 5px;
		font-size: 9px;
		min-width: 760px;
	}

	.preco {
		color: #f41c1c;
/*  29/1/2009 MLG - HD 65922 - ...mas deixando o pre�o com o mesmo tamanho	*/
		font-size: <?php if ($login_fabrica == 3) {echo "1em";}else{echo "0.9em";}?>;
	}

	.promo {
		position: absolute;
		float: right;
	}

	.categ {
		color: #FFFFFF;
		text-decoration: none;
	}
	.categ:hover {
		text-decoration: underline;
	}
</style>
<script type="text/javascript" src="js/niftycube.js"></script>
<script type="text/javascript" src="js/niftyLayout.js"></script>

<?


$sql = "SELECT posto, capital_interior
		FROM tbl_posto
		WHERE posto = $login_posto";

$res = pg_exec ($con,$sql);
if(pg_numrows($res)>0){
	$posto            = trim(pg_result ($res,0,posto));
	$capital_interior = trim(pg_result ($res,0,capital_interior));
}

$sql = "SELECT valor_pedido_minimo, valor_pedido_minimo_capital
		FROM tbl_fabrica
		WHERE fabrica = $login_fabrica";
$res = pg_exec ($con,$sql);
$valor_pedido_minimo         = trim(pg_result($res,valor_pedido_minimo));
$valor_pedido_minimo_capital = trim(pg_result($res,valor_pedido_minimo_capital));

$valor_pedido_minimo         = number_format($valor_pedido_minimo,2,".",",");
$valor_pedido_minimo_capital = number_format($valor_pedido_minimo_capital,2,".",",");

if($capital_interior=="CAPITAL"){
	$msg="O valor m�nimo de faturamento � de R$ $valor_pedido_minimo_capital..";
}

if($capital_interior=="INTERIOR"){
	$msg="O valor m�nimo de faturamento � de R$ $valor_pedido_minimo.";
}


include 'lv_menu.php';
# AVISO
echo "<BR>";
# BUSCA POR PE�A
echo "<table width='98%' border='0' align='center' cellpadding='2' cellspacing='2'>\n";
echo "<tr>\n";
echo "<td width='170' valign='top'>\n";
/*MENU*/

include "lv_menu_lateral.php";

echo "</td>\n";
echo "<td valign='top' align='right'>\n";
//echo "	<center><img src='imagens/liquidacao2.png' border='0'></center>";
echo "<table width='95%' border='0' align='center' cellpadding='0' cellspacing='0'>\n";

	$busca      = trim($_POST["busca"]);
	$tipo       = trim($_POST["tipo"]);
	$categoria  = trim($_GET["categoria"]);
	$categoria_tipo  = trim($_GET["categoria_tipo"]);

	if(strlen($busca) ==0){
		$busca = trim($_GET["busca"]);
	}

	if(strlen($tipo) ==0){
		$tipo = trim($_GET["tipo"]);
	}

	if (strlen($msg_erro) == 0 and strlen($busca)>0) {
		$buscas = strtoupper($busca);
		$pesquisa = "   AND (tbl_peca.descricao     ILIKE '%$buscas%' or tbl_peca.referencia    ILIKE '%$busca%')";
		$pesquisa_telecontrol = $pesquisa;
	}

	$join_pesquisa = "";

	if(strlen($categoria)>0){
		if($categoria_tipo == "familia"){
			$join_pesquisa = " JOIN tbl_lista_basica on tbl_lista_basica.peca = tbl_peca.peca
								JOIN tbl_produto on tbl_produto.produto = tbl_lista_basica.produto ";
			$pesquisa = "AND tbl_produto.familia = $categoria ";
			$pesquisa_telecontrol = "AND tbl_peca.familia_peca = $categoria ";
		}
		if($categoria_tipo == "linha"){
			$join_pesquisa = " JOIN tbl_lista_basica on tbl_lista_basica.peca = tbl_peca.peca
								JOIN tbl_produto on tbl_produto.produto = tbl_lista_basica.produto ";
			$pesquisa = " AND tbl_produto.linha = $categoria ";
			$pesquisa_telecontrol = "AND tbl_peca.linha_peca = $categoria ";
			if($login_fabrica ==1){
				$join_pesquisa = " ";
				$pesquisa = "AND tbl_peca.linha_peca = $categoria ";
			}
		}
		if($categoria_tipo == "produto"){
			$pesquisa = " AND tbl_produto.produto = $categoria ";
		}

	}else{
		if ($login_fabrica == 3 AND 1==2) { // HD 60180
			$sql = "SELECT distinct peca
					INTO TEMP peca_linha_lv_$login_posto
					FROM tbl_lista_basica 
					JOIN tbl_produto USING(produto)
					WHERE fabrica = $login_fabrica
					AND   tbl_produto.ativo IS TRUE
					AND   linha  IN (SELECT linha FROM tbl_posto_fabrica JOIN tbl_posto_linha USING (posto) WHERE tbl_posto_linha.posto = $login_posto AND fabrica = $login_fabrica);
					
					CREATE INDEX peca_linha_lv_peca_$login_posto ON peca_linha_lv_$login_posto(peca);";
			$res = pg_exec($con,$sql);
			$join_pesquisa = " JOIN peca_linha_lv_$login_posto USING(peca) ";
		}
	}
if($login_fabrica == 1 OR $login_fabrica==10){
	$somente_site = " AND X.promocao_site IS TRUE " ;
}

if($login_fabrica==10 AND strlen($promocoes)>0){ //HD 47948
	$promocoes = " AND X.liquidacao IS TRUE " ;
}

if (strlen($msg_erro) == 0) {
	if (strlen($pesquisa)== 0){
		$pesquisa = " AND   tbl_peca.promocao_site IS TRUE ";
	}

	// PRODUTO ACABADO
	//$produto_acabado= " AND   tbl_peca.produto_acabado IS NOT TRUE ";

	//pega produtos
/*segundo o samuel, deve-se fazer compra independente da linha*/

$sql = "SELECT	X.peca,
				X.referencia    ,
				X.descricao     ,
				X.preco_sugerido,
				X.ipi  ,
				X.promocao_site ,
				X.multiplo_site  ,
				X.qtde_minima_site ,
				X.qtde_max_site  ,
				X.qtde_disponivel_site ,
				X.posicao_site,
				X.preco_anterior,
				X.frete_gratis,
				X.liquidacao
		FROM
		(
		SELECT DISTINCT tbl_peca.peca,
				tbl_peca.referencia    ,
				tbl_peca.descricao     ,
				tbl_peca.preco_sugerido,
				tbl_peca.ipi  ,
				tbl_peca.promocao_site ,
				tbl_peca.multiplo_site  ,
				tbl_peca.qtde_minima_site ,
				tbl_peca.qtde_max_site  ,
				tbl_peca.qtde_disponivel_site ,
				tbl_peca.posicao_site,
				tbl_peca.preco_anterior,
				tbl_peca.fabrica     ,
				tbl_peca.frete_gratis,
				tbl_peca.liquidacao
			FROM tbl_peca
			$join_pesquisa
			WHERE tbl_peca.fabrica = $login_fabrica
			AND tbl_peca.ativo is not false
			$produto_acabado
			AND tbl_peca.peca NOT IN (SELECT peca FROM tbl_peca_fora_linha WHERE fabrica = $login_fabrica AND peca is not null)
			AND tbl_peca.peca NOT IN (SELECT DISTINCT peca_de FROM tbl_depara WHERE fabrica = $login_fabrica)
			$pesquisa
		UNION
			SELECT DISTINCT tbl_peca.peca,
							tbl_peca.referencia    ,
							tbl_peca.descricao     ,
							tbl_peca.preco_sugerido,
							tbl_peca.ipi  ,
							tbl_peca.promocao_site ,
							tbl_peca.multiplo_site  ,
							tbl_peca.qtde_minima_site ,
							tbl_peca.qtde_max_site  ,
							tbl_peca.qtde_disponivel_site ,
							tbl_peca.posicao_site,
							tbl_peca.preco_anterior,
							tbl_peca.fabrica            ,
							tbl_peca.frete_gratis,
							tbl_peca.liquidacao
			FROM tbl_peca
			JOIN tbl_linha    ON tbl_linha.linha     = tbl_peca.linha_peca
			JOIN tbl_familia  ON tbl_familia.familia = tbl_peca.familia_peca
			WHERE 1=1
			AND tbl_peca.fabrica = $login_fabrica
			AND tbl_peca.promocao_site IS TRUE
			$pesquisa_telecontrol
		) as X
		WHERE X.fabrica = $login_fabrica
		$somente_site
		$promocoes
		ORDER BY X.promocao_site,X.posicao_site
		";
/*	if($ip == '200.246.170.72'){
		echo nl2br($sql);
	}*/

	// ##### PAGINACAO ##### //
	$sqlCount  = "SELECT count(*) FROM (";
	$sqlCount .= $sql;
	$sqlCount .= ") AS count";

	require "_class_paginacao.php";

	// definicoes de variaveis
	$max_links = 11;				// m?ximo de links ? serem exibidos
	$max_res   = 20;					// m?ximo de resultados ? serem exibidos por tela ou pagina
	$mult_pag  = new Mult_Pag();	// cria um novo objeto navbar
	$mult_pag->num_pesq_pag = $max_res; // define o n?mero de pesquisas (detalhada ou n?o) por p?gina

	$res = $mult_pag->executar($sql, $sqlCount, $con, "otimizada", "pgsql");

	// ##### PAGINACAO ##### //


	echo "<tr>\n";
	echo "<td align='center'>\n";
	echo "<ul id='intro2'>\n";
	echo "<li id='infor' style='padding-left: 8px; font-size: ";
	if ($login_fabrica==3) {echo "12pt";}else{echo "1.3em";}
	echo "; font-weight: bold;'>\n";
	if (strlen($busca)==0 AND strlen($categoria)==0){
		echo "Lista Completa";
	}else{
		if($login_fabrica == 10) {
			if($categoria_tipo =='linha') {
				$categ=" AND tbl_linha.linha = $categoria ";
			}elseif($categoria_tipo='familia'){
				$categ=" AND tbl_familia.familia = $categoria ";
			}
			$sql4 = "SELECT tbl_linha.nome        AS linha_descricao  ,
								tbl_linha.linha                           ,
								tbl_familia.descricao AS familia_descricao,
								tbl_familia.familia
						FROM      tbl_peca
						JOIN      tbl_linha   ON tbl_peca.linha_peca   = tbl_linha.linha
						LEFT JOIN tbl_familia ON tbl_peca.familia_peca = tbl_familia.familia
						WHERE tbl_peca.fabrica = $login_fabrica
						AND tbl_peca.promocao_site IS TRUE
						$pesquisa_telecontrol
						$categ ";
			$res4 = @pg_exec ($con,$sql4);
			if(@pg_numrows($res4) > 0){
				$linha_peca        = trim(pg_result ($res4,0,linha));
				$familia_peca      = trim(pg_result ($res4,0,familia));
				$linha_peca_desc   = ucfirst(trim(pg_result ($res4,0,linha_descricao)));
				$familia_peca_desc = ucfirst(trim(pg_result ($res4,0,familia_descricao)));
				if($categoria_tipo =='linha') {
					echo "<a href='$PHP_SELF?categoria=$linha_peca&categoria_tipo=linha' class='categ'><font color='#FFFFFF' >$linha_peca_desc</font></a>";
				}elseif($categoria_tipo=='familia'){
					echo "<a href='$PHP_SELF?categoria=$linha_peca&categoria_tipo=linha' class='categ'><font color='#FFFFFF' >$linha_peca_desc</a> > </font> <a href='$PHP_SELF?categoria=$familia_peca&categoria_tipo=familia' class='categ'><font color='#FFFFFF' >$familia_peca_desc</font></a>";
				}
			}
		}else{
			echo "Produtos";
		}
	}
	echo "</li>\n</ul>\n";
	echo "</td>\n";
	echo "</tr>\n";


	if(pg_numrows($res) == 0){
		echo "<tr>\n";
		echo "<td align='center'>\n";
				echo "<div align='center'><FONT SIZE='2' COLOR='#FF0000'><b>Nenhuma pe�a encontrada!</b></FONT></div>\n";
		echo "</td>\n";
		echo "</tr>\n";
	}else{
		echo "<tr>\n";
		echo "<td align='top'>\n";

		echo "<table width='98%' border='0' align='top' cellpadding='7' cellspacing='7'>\n";

		for ($i = 0 ; $i < pg_numrows($res); $i++){
			$peca                 = trim(pg_result ($res,$i,peca));
			$referencia           = trim(pg_result ($res,$i,referencia));
			$preco_sugerido       = trim(pg_result ($res,$i,preco_sugerido));
			$ipi                  = trim(pg_result ($res,$i,ipi));
			$descricao            = trim(pg_result ($res,$i,descricao));
			#if (strlen($descricao)>30) $descricao   = substr($descricao,0,30);
			$promocao_site        = trim(pg_result ($res,$i,promocao_site));
			$multiplo_site        = trim(pg_result ($res,$i,multiplo_site));
			$qtde_minima_site     = trim(pg_result ($res,$i,qtde_minima_site));
			$qtde_max_site        = trim(pg_result ($res,$i,qtde_max_site));
			$qtde_disponivel_site = trim(pg_result ($res,$i,qtde_disponivel_site));
			$preco_anterior       = trim(pg_result ($res,$i,preco_anterior)); #HD 13429
			$frete_gratis         = trim(pg_result ($res,$i,frete_gratis)); #HD 40674
			$liquidacao           = trim(pg_result ($res,$i,liquidacao));

			$sql2 = "SELECT preco
					FROM tbl_tabela_item
					WHERE peca = $peca
					AND   tabela IN (
						SELECT tbl_tabela.tabela
						FROM tbl_posto_linha
						JOIN tbl_tabela       USING(tabela)
						JOIN tbl_posto        USING(posto)
						JOIN tbl_linha        USING(linha)
						WHERE tbl_posto.posto       = $login_posto
						AND   tbl_linha.fabrica     = $login_fabrica
						AND   tbl_posto_linha.linha IN (
							SELECT DISTINCT tbl_produto.linha
							FROM tbl_produto
							JOIN tbl_lista_basica USING(produto)
							JOIN tbl_peca USING(peca)
							WHERE peca = $peca
						)
					)";
			$res2 = pg_exec ($con,$sql2);
			if(pg_numrows($res2)==0) $preco       = 0;
			else                     $preco       = trim(pg_result ($res2,0,preco));

			if($login_fabrica<>3 AND $login_fabrica<>35){
				$sql2 = "SELECT (preco / 0.82) as preco
						FROM tbl_tabela_item
						WHERE peca  = $peca
						AND   tabela IN (
							SELECT tbl_tabela.tabela
							FROM tbl_tabela
							WHERE fabrica = $login_fabrica
						)";
				$res2 = pg_exec ($con,$sql2);
				if(pg_numrows($res2)==0) {
					$preco = 0;
				}else{
					$preco = trim(pg_result ($res2,0,preco));
				}
			}
			if($login_fabrica==1){
				$sql2 = "SELECT TRUNC((preco / $picms)::NUMERIC,2) as preco
						FROM tbl_tabela_item
						WHERE peca  = $peca
						AND   tabela IN (
							SELECT tbl_tabela.tabela
							FROM tbl_tabela
							WHERE fabrica = $login_fabrica
						)";
				$res2 = pg_exec ($con,$sql2);
				if(pg_numrows($res2)==0) {
					$preco = 0;
				}else{
					$preco = trim(pg_result ($res2,0,preco));
				}
			}

			if (strlen($login_unico)>0 AND $login_fabrica==10 ){
				$sql2 = "SELECT preco
					FROM tbl_tabela_item
					WHERE peca  = $peca
					AND   tabela IN (
						SELECT tbl_tabela.tabela
						FROM tbl_tabela
						WHERE fabrica = $login_fabrica
					)";
				$res2 = pg_exec ($con,$sql2);
				if(pg_numrows($res2)==0) $preco = 0;
				else                     $preco = trim(pg_result ($res2,0,preco));
			}

			$preco_formatado = number_format($preco, 2, ',', '');
			$preco_formatado = str_replace(".",",",$preco_formatado);

			if($i%2==0) $cor='#F4EBD7';
			else        $cor='#EFEFEF';

			if($i==0){
				echo "<tr>";
				$X=0;
			}
			echo "<td width='150' align='center' style='vertical-align: top;'><div style='height: 22px;'>";

			if($frete_gratis=='t'){
				echo "<INPUT TYPE='image' SRC='imagens/fretegratis.jpeg'>";
			}

			echo "</div><form action='lv_carrinho.php?acao=adicionar' method='post' name='frmcarrinho_$i'>\n";
				/*if( $preco > 0)*/
				$saida == "";
				if($login_fabrica == 3 OR $login_fabrica == 10) {
					$diretorio = "imagens_pecas/pequena/";
				}else{
					$diretorio = "imagens_pecas/$login_fabrica/pequena/";
				}

				$sqlOrdem = "SELECT  peca_item_foto, caminho, caminho_thumb, descricao
						FROM tbl_peca_item_foto
						WHERE peca = $peca
						AND ordem IS NOT NULL";
				$resOrdem = pg_exec ($con,$sqlOrdem);

				if (pg_numrows($resOrdem) > 0){
					$sqlMostraFoto = "SELECT  peca_item_foto, caminho, caminho_thumb, descricao
							FROM tbl_peca_item_foto
							WHERE peca = $peca
							AND ordem = 1";
					$resMostraFoto = pg_exec ($con,$sqlMostraFoto);
					if (pg_numrows($resMostraFoto) == 1){
						$caminho        = trim(pg_result($resMostraFoto,0,caminho));
						$caminho_thum   = trim(pg_result($resMostraFoto,0,caminho_thumb));
						$filename_final = str_replace("/www/assist/www/imagens_pecas/pequena/",'',$caminho_thum);
					}
				}else{
					if ($dh = opendir($diretorio)) {
						$contador=2;
						$filename_final = "";
						$Xreferencia = str_replace(" ", "_",$referencia);
						if($login_fabrica ==10) { // por causa de strpos,coloquei isso - Paulo
							$Xreferencia = $Xreferencia.'-';
						}
					#Antes os contadores eram: 1�contador = 0, 2�contador = 1, 3�contador++
					#Para mostrar a primeira pe�a cadastrada. HD40674
						while (false !== ($filename = readdir($dh))) {
							if($contador == 0) break;
							if (strpos($filename,$Xreferencia) !== false){
								$contador--;
								$po = strlen($Xreferencia);
								if(substr($filename, 0,$po)==$Xreferencia){
									$filename_final = $filename;
								}
							}
						}
					}
				}
				$tamanho = '90';
				if(strlen($filename_final)>0){
					list($width, $height) = getimagesize("$diretorio$filename_final");
					$limita = ReDimImg($width, $height, $tamanho);
					// echo "<!-- W: $width H: $height -->";
					echo "<center>
						  <div style='height: ".($tamanho + 10)."px;
										vertical-align: center;clear:none;'>
						  <a href='lv_detalhe.php?cod_produto=$peca'>
						  <img src='$diretorio$filename_final' border='0' $limita </center>\n";
					echo "<input type='hidden' name='peca_imagem' value='$filename_final' >\n";
					//if($login_fabrica==1){$sql3 = "UPDATE tbl_peca set posicao_site=1 where peca = $peca";
					//$res3 = pg_exec ($con,$sql3);
					//}
				}else{
					echo "<center>
						   <div style='height: ".$tamanho."px;
										'vertical-align: middle;'>
							<img src='imagens_pecas/semimagem.jpg'
							border='0' style='height: 90px;clear:both;'>
						  </center>\n";
				}
				if ($liquidacao == 't' AND $login_fabrica==10) {
					echo "<img src='imagens/promocao.gif'
							style='position: absolute; float: right;'>\n";
				}
				if (($promocao_site == 't' OR $qtde_disponivel_site > 0) AND ($login_fabrica==3) ) {
					echo "<img src='imagens/promocao.gif'
							style='position: relative;
								   margin-right: 5px;
								   top: -15px;
								   float: right;z-index:1'>\n";
				}

				/*if( $preco > 0)*/  echo "</div></a>\n";

					echo "<input type='hidden' name='btn_comprar' id='btn_comprar' ".
						 "value='' class='botao'>";
					echo "<img src='imagens/bt_comprar_pq2.gif' ".
						 "onclick=\"javascript: if (document.getElementById('btn_comprar').value == '' ) { document.getElementById('btn_comprar').value='Comprar'; document.frmcarrinho_$i.submit() } else { alert ('Aguarde submiss�o') }\" ".
						 "style='cursor: pointer;clear:both;'>";

				/*if( $preco > 0 )*/ echo "<BR><a href='lv_detalhe.php?cod_produto=$peca' class='linkproduto'>\n";
				if($login_fabrica == 10) { // HD 40671
					echo "<span class='descrproduto'>$descricao</span>";
				}else{
					echo "$referencia - $descricao";
				}
				if( $preco > 0 ){
					#HD 13429
					if (strlen($preco_anterior)>0 AND $preco_anterior>0){
						$preco_anterior = number_format($preco_anterior, 2, ',', '');
						$preco_anterior = str_replace(".",",",$preco_anterior);
						echo "<br>";
						echo "<span class='preco' style='font-size: 0.8em;color: #5F5F5F;'>DE: <b>R$ $preco_anterior</b></span>";
						echo "<br>";
						echo "<span class='preco'>POR: <b>R$ $preco_formatado</b></span>";
					}else{
						echo "<br><span class='preco'><b>R$ $preco_formatado</b></span>\n";
					}
				}else{
					echo "<br><b style='color: #EF8B03;'>Indispon�vel</b></font>\n";
				}
				/*if( $preco > 0 ){*/
					echo "</a>\n";
				/*}*/

				echo "<input type='hidden' name='cod_produto'	value='$peca'>\n";
				echo "<input type='hidden' name='valor'         value='$preco'>\n";
				echo "<input type='hidden' name='ipi'           value='$ipi'>\n";
				echo "<input type='hidden' name='descricao'     value='$descricao'>\n";
				echo "<input type='hidden' name='linha'         value='$linha'>\n";
				echo "<input type='hidden' name='qtde'          value='$qtde'>\n";
				echo "<input type='hidden' name='qtde_maxi'     value='$qtde_max_site'>\n";
				echo "<input type='hidden' name='qtde_min'      value='$qtde_minima_site'>\n";
				echo "<input type='hidden' name='qtde_disp'     value='$qtde_disponivel_site'>\n";

				if($preco > 0){
					#	echo "Qtde:";
					if (strlen($qtde_max_site)==0){
						$qtde_max_site = 500;
					}
					if($multiplo_site > 1){
						echo "<select name='qtde' class='Caixa'>\n";
						for($z=1;$z<=20;$z++){
							$aux = $z * $multiplo_site;
							if(($aux>=$qtde_minima_site) AND (strlen($qtde_max_site)>0 AND $aux<=$qtde_max_site))echo "<option value='$aux'>$aux</option>\n";
						}
						echo "</select>\n";
					}
//  29/1/2009 MLG - HD 65922: campo qtde. tamb�m se n�o tem m�ltiplos, para Brit�nia
					else {
						if ($login_fabrica==3) {
							echo "<INPUT TYPE='text' value='1' SIZE='2' MAXLENGTH='3' name='qtde' class='Caixa'>\n";
						}
					}
//  MLG - Fim
					if($login_fabrica==10){
						//HD 49344 - definir qtde inicial 1
						echo "<INPUT TYPE='hidden' NAME='qtde' value = '1'>";
					}
						/* else{
						echo "<input type='text' size='2' maxlength='3' name='qtde' value='";
						if(strlen($qtde_minima_site)==0){ $qtde_minima_site= "1"; echo $qtde_minima_site;}
						else { $qtde_minima_site= "$qtde_minima_site"; }
						echo "'";
						if (strlen($qtde_disponivel_site)>0){
							echo " onblur='javascript: checarNumero(this);
							if (this.value < $qtde_minima_site || this.value==\"\") {
								alert(\"Quantidade abaixo da m�nima permitida. A quantidade m�nima para compra desta pe�a � de $qtde_minima_site!\");
								this.value=\"$qtde_minima_site\";
							}
							if (this.value > $qtde_max_site || this.value==\"\") {
								alert(\"Quantidade acima da m�xima permitida. A quantidade m�xima para compra desta pe�a � de $qtde_max_site!\");
								this.value=\"$qtde_minima_site\";
							}'";
						}else{
							echo "onblur='javascript: checarNumero(this);'";
						}
						echo ">\n";
					} */
					echo "</form>";
					echo "</td>";
				}
			if($X==3){
				echo "</tr>";
				echo "<tr><td>&nbsp;</td></tr>";
				$X=0;
			}else{
				$X++;
			}
		}
		echo "</table>";
		echo "</td>";
		echo "</tr>\n";
	}
	echo "<script>document.getElementById('mensagem').style.visibility = 'hidden';</script>";

echo "</table>\n";
### P? PAGINACAO###
echo "<BR>";
	echo "<table border='0' align='center' width='100%'>\n";
	echo "<tr>\n";
	echo "<td align='center'>\n";

	// ##### PAGINACAO ##### //

	// links da paginacao
	echo "<br>";

	if($pagina < $max_links){
		$paginacao = pagina + 1;
	}else{
		$paginacao = pagina;
	}

	// paginacao com restricao de links da paginacao

	// pega todos os links e define que 'Pr?xima' e 'Anterior' ser?o exibidos como texto plano
	$todos_links	= $mult_pag->Construir_Links("strings", "sim");

	// fun??o que limita a quantidade de links no rodape
	$links_limitados	= $mult_pag->Mostrar_Parte($todos_links, $coluna, $max_links);

	for ($n = 0; $n < count($links_limitados); $n++) {
		echo "<font color='#DDDDDD' size='1'>".$links_limitados[$n]."</font> ";
	}

	$resultado_inicial = ($pagina * $max_res) + 1;
	$resultado_final   = $max_res + ( $pagina * $max_res);
	$registros         = $mult_pag->Retorna_Resultado();

	$valor_pagina   = $pagina + 1;
	$numero_paginas = intval(($registros / $max_res) + 1);

	if ($valor_pagina == $numero_paginas) $resultado_final = $registros;

	if ($registros > 0){
		echo "<br>";
		echo "<font size='2'  color='#363636'>Resultados de <b>$resultado_inicial</b> a <b>$resultado_final</b> do total de <b>$registros</b> registros.</font>";
		echo "<font color='#cccccc' size='1'>";
		echo " (P�gina <b>$valor_pagina</b> de <b>$numero_paginas</b>)";
		echo "</font>\n";
		echo "</div>\n";
	}
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	// ##### PAGINACAO ##### //
}
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";

if (strlen($cook_fabrica)==0 AND ( strlen($cook_login_unico)>0 OR strlen($cook_login_simples)>0)) {
	include "login_unico_rodape.php";
}else{
	include "rodape.php";
}
?>