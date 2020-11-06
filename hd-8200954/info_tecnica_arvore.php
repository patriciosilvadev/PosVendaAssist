<?php

include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'autentica_usuario.php';
include 'funcoes.php';

$title = "Menu Assistencia T�cnica";
$layout_menu = 'tecnica';

$fabrica_comunicado = $login_fabrica == 168 ? 151 : $login_fabrica;

if(in_array($login_fabrica,array(161))){
	header("Location: info_tecnica_arvore_new.php");
	exit;
}

//--==================== TIPO POSTO ====================--\\
$sql = "SELECT tbl_posto_fabrica.codigo_posto,
               tbl_posto_fabrica.tipo_posto
          FROM tbl_posto
     LEFT JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_posto.posto
         WHERE tbl_posto_fabrica.fabrica = $login_fabrica
           AND tbl_posto.posto   = $login_posto ";

$res = pg_exec($con,$sql);

if (pg_numrows($res) > 0) {
	$tipo_posto = trim(pg_result($res,0,'tipo_posto'));
}

//--==================== ESQUEMAS EL�TRICOS ====================--\\

$sql = "SELECT  COUNT(comunicado) AS total_esquemas
        FROM    tbl_comunicado
        WHERE   ativo IS NOT FALSE
        AND     (
                    tipo = 'Esquema El�trico'
                OR  tipo = 'Esquema Eletrico'
                OR  tipo = 'Manual de instru��es'
                OR  tipo = 'Manual de instrucoes'
                )
        AND     (
                    tbl_comunicado.tipo_posto = $tipo_posto
                OR  tbl_comunicado.tipo_posto IS NULL
                )
        AND     fabrica                    = $fabrica_comunicado";

$res = pg_exec ($con,$sql);
$total_esquemas = 0;

if (pg_numrows($res) > 0) {

	$total_esquemas = trim(pg_result($res,0,'total_esquemas'));

}

//--==================== MANUAIS T�CNICOS ====================--\\
$sql = "SELECT COUNT(comunicado) AS total_manuais
		FROM tbl_comunicado
		WHERE ativo IS NOT FALSE
		AND (tipo = 'Manual T�cnico' OR tipo = 'Manual Tecnico')
		AND (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
		AND fabrica    = $fabrica_comunicado";

$res = pg_exec ($con,$sql);

if (pg_numrows($res) > 0) {
	$total_manuais  = trim(pg_result($res,0,'total_manuais'));
}

//--==================== ALTERA��ES T�CNICAS ====================--\\
$sql = "SELECT COUNT(comunicado) AS total_alteracoes
		FROM tbl_comunicado
		WHERE ativo IS NOT FALSE
		AND tipo = 'Altera��es T�cnicas'
		AND (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
		AND fabrica    = $fabrica_comunicado";

$res = pg_exec ($con,$sql);

if (pg_numrows($res) > 0) {
	$total_alteracoes  = trim(pg_result($res,0,'total_alteracoes'));
}



//--===============================================================--\\

$tipo_comunicado = $_GET['tipo_comunicado'];
include "cabecalho.php";
if ($S3_sdk_OK) {
	include_once S3CLASS;
	$s3 = new anexaS3('ve', (int) $fabrica_comunicado);
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
<script src="js/jquery-1.6.2.js" ></script>
<script src="js/jquery.blockUI_2.39.js" ></script>
<script type="text/javascript" src="plugins/shadowbox/shadowbox.js"></script>
<link type="text/css" href="plugins/shadowbox/shadowbox.css" rel="stylesheet" media="all">

<?php
	if($login_fabrica == 143){

		echo traduz("<p align='center'>
			<a href='http://products.wackerneuson.com/SpareParts28/wacker_direct.jsp?command=machinesearch&extRegId=w5&extlangId=en&urlAccess=true' target='_blank'>Clique aqui para Verificar Lista B�sica</a>
		</p>");

		include('rodape.php');

		exit;

	}
?>

<script>



$(window).load(function () {
    $("a[name=prod_ve]").click(function () {
        var comunicado = $(this).attr("rel");
        
        var tipo = $('#'+comunicado).val();
        $.ajaxSetup({
            async: true
        });

        $.blockUI({ message: "Aguarde..." });

        $.get(
            "verifica_s3_comunicado.php",
            {tipo: tipo, comunicado: comunicado, fabrica:"<?=$fabrica_comunicado?>"},
            function(data) {
                if (data.length > 0) {
                    Shadowbox.init();
                    //var imagem = new Image();
                    //imagem.onload = function() {
                    //    var height = this.height;
                    //    var width = this.width;

                    //    var pwidth = (width * 15) / 100;
                    //    var pheight = (height * 15) / 100;

                    //       width = width + pwidth;
                    //       height = height + pheight;

                    //       Shadowbox.open({
                    //            content :   data,
                    //            player  :   "iframe",
                    //            title   :   "Vista Explodida",
                    //            width   :   width,
                    //            height  :   height
                    //        });
                    // }
                    // imagem.src = data;

                     Shadowbox.open({
                         content: data,
                         player:  'iframe',
                         title:   ('<?=traduz('Vista Explodida')?>'),
                     });
                } else {
                    alert('<?=traduz('Arquivo n�o encontrado!')?>');
                }

                $.unblockUI();
            });
        });
    });

    var popupBlockerChecker = {
        check: function(popup_window) {
            var _scope = this;

            if (popup_window) {
                if (/chrome/.test(navigator.userAgent.toLowerCase())) {
                    setTimeout(function() {
                        _scope._is_popup_blocked(_scope, popup_window);
                    }, 500);
                }else{
                    popup_window.onload = function() {
                        _scope._is_popup_blocked(_scope, popup_window);
                    };
                }
            }else{
                _scope._displayMsg();
            }
        },
        _is_popup_blocked: function(scope, popup_window){
            if ((popup_window.screenX > 0) == false) {
                scope._displayMsg();
            }
        },
        _displayMsg: function() {
            Shadowbox.init();

            Shadowbox.open({
                content: "popup_bloqueado.php",
                player:  "iframe",
                title:   "POPUP BLOQUEADO",
                width:   800,
                height:  600
            });
        }
    };
</script>

<?php if ($login_fabrica == 157) { ?>
	<div style="color: red;">
		<h1>
			Para acessar as Vistas Explodidas, clique no link abaixo:<br />
			<a href="https://wap.ind.br/mais/vistas-explodidas-wap/" target="_blank">
				<b style="text-decoration:underline;">
					www.wap.ind.br/mais/vistas-explodidas-wap/
				</b>
			</a>
		</h1>
	</div>
<?php } ?>

<?
include "verifica_adobe.php";

	$sqlPostoLinha = "
						AND (tbl_comunicado.linha IN
								(
									SELECT tbl_linha.linha
									FROM tbl_posto_linha
									JOIN tbl_linha ON tbl_linha.linha = tbl_posto_linha.linha
									WHERE fabrica =$fabrica_comunicado
										AND tbl_linha.ativo IS TRUE
										AND posto = $login_posto
								)
								OR (
										tbl_comunicado.produto IS NULL AND
										tbl_comunicado.comunicado IN (
											SELECT tbl_comunicado_produto.comunicado
											FROM tbl_comunicado_produto
											JOIN tbl_produto ON tbl_comunicado_produto.produto = tbl_produto.produto
											JOIN tbl_posto_linha on tbl_posto_linha.linha = tbl_produto.linha
											WHERE fabrica_i =$fabrica_comunicado AND
												  tbl_posto_linha.posto = $login_posto

										)

								)
								OR
								    (
									tbl_comunicado.linha IS NULL AND
									tbl_comunicado.produto in
										(
											SELECT tbl_produto.produto
										 	FROM tbl_produto
											JOIN tbl_linha ON tbl_linha.linha = tbl_produto.linha AND tbl_linha.ativo IS TRUE
											JOIN tbl_posto_linha ON tbl_produto.linha = tbl_posto_linha.linha
											WHERE fabrica_i = $fabrica_comunicado AND
										 	posto = $login_posto
										)
									)

								 OR (tbl_comunicado.linha IS NULL AND tbl_comunicado.produto IS NULL AND
								 		tbl_comunicado.comunicado IN (
											SELECT tbl_comunicado_produto.comunicado
											FROM tbl_comunicado_produto
											JOIN tbl_produto ON tbl_comunicado_produto.produto = tbl_produto.produto
											JOIN tbl_linha ON tbl_linha.linha = tbl_produto.linha AND tbl_linha.ativo IS TRUE
											JOIN tbl_posto_linha on tbl_posto_linha.linha = tbl_produto.linha
											WHERE fabrica_i =$fabrica_comunicado AND tbl_posto_linha.posto = $login_posto

											)

									)
							)";
	if(in_array($login_fabrica,array(1,15,42,148))) $sqlPostoLinha = "";
?>

<? if(($login_fabrica == 15 || $login_fabrica == 91 )  AND $tipo_comunicado == "video"){ ?>
	<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
		<tr bgcolor = '#efefef'>
			<td rowspan='3' width='20' valign='top'><img src='imagens/marca25.gif'></td>
			<td class="chapeu" colspan='2' ><?=traduz('V&iacute;deos de Treinamento')?></td>
		</tr>
		<tr bgcolor = '#fafafa'>
			<td colspan='2' height='5'></td>
		</tr>
		<tr bgcolor = '#fafafa'>
			<td valign='top' class='menu' align="left">
		<?php
			$sql = "SELECT DISTINCT tbl_familia.familia                                  ,
									tbl_familia.descricao
					FROM    tbl_comunicado
					JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
					JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
					LEFT JOIN tbl_familia ON tbl_produto.familia = tbl_familia.familia
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					AND     tbl_comunicado.tipo = 'V�deo'
					AND     tbl_comunicado.produto IS NOT NULL
				UNION
				SELECT DISTINCT null::int4 AS familia                                      ,
								null::text AS descricao
					FROM    tbl_comunicado
					JOIN    tbl_linha ON tbl_comunicado.linha   = tbl_linha.linha
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					AND     tbl_comunicado.tipo = 'V�deo'
					AND     tbl_comunicado.produto IS NULL
					AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
					AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)
				UNION
				SELECT DISTINCT tbl_familia.familia                                  ,
									tbl_familia.descricao
					FROM    tbl_comunicado
					JOIN    tbl_comunicado_produto ON tbl_comunicado_produto.comunicado = tbl_comunicado.comunicado
					JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado_produto.produto
					JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
					LEFT JOIN tbl_familia ON tbl_produto.familia = tbl_familia.familia
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					AND     tbl_comunicado.tipo = 'V�deo'
					AND     tbl_comunicado.produto IS NULL
				ORDER BY descricao";
			$res = pg_query($con,$sql);
			if(pg_numrows($res) > 0){
				for($x = 0; $x < pg_numrows($res); $x++){
					$familia = pg_result($res,$x,'familia');
					$familia_descricao  = pg_result($res,$x,'descricao');

					echo "<a href='info_tecnica_visualiza.php?tipo=video&familia=$familia'>-� $familia_descricao</a> <br>";
				}
			} else{
				echo traduz("� Nenhum Cadastrado");
			}
		?>
			</td>
			<td rowspan='2'class='detalhes' width='150'><?=traduz('Escolha a fam�lia que deseja consultar.')?></td>
		</tr>
	</table>
<? } else {
if (in_array($login_fabrica, [167, 203])) {
	include('info_tecnica_arvore_brother.php');
}
if($login_fabrica == 153){ ?>
<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
	<?php
	if($login_fabrica <> 87){
	?>
	<tr bgcolor = '#fafafa'>
		<td rowspan='4' width='20' valign='top'><img src='imagens/marca25.gif'></td>
		<td  class="chapeu" colspan='2'><?php
			if ($login_fabrica == 6) {
				echo "<a href='lista_basica_consulta.php'>LISTA BASICA</a>";
			} else {
				echo "Atualiza��o de Software";
			}?>
		</td>
	</tr>
	<?php
	}
	?>
	<tr bgcolor = '#fafafa'>
		<td colspan='2' height='5' nowrap ><?php
			if ($login_fabrica == 1) {
				echo "<br /><center><a href='#DeWalt'><img src='logos/dewalt.jpg' align='absmiddle' hspace='5' border='0'></a>";
				echo "<a href='#Eletro'><img src='logos/blackedecker.jpg' align='absmiddle' hspace='5' border='0'></a>";
				echo "<a href='#Ferramentas Black & Decker'><img src='http://www.blackdecker.com.br/imagens/logobd_eletro.gif' align='absmiddle' hspace='5' border='0'></a>";
				echo "<a href='#Porter Cable'><img src='logos/PorterCable.jpg' align='absmiddle' hspace='5' border='0'></a></center>";
			}?>
		</td>
	</tr>
	<tr bgcolor = '#fafafa'>
		<td valign='top' class='menu'><?php

			$sql = "SELECT DISTINCT tbl_familia.familia                                  ,
									tbl_familia.descricao                                ,
									tbl_linha.linha                                      ,
									tbl_linha.nome
					FROM    tbl_comunicado
					JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
					JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
					LEFT JOIN tbl_familia ON tbl_produto.familia = tbl_familia.familia
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					AND     tbl_comunicado.tipo = 'Atualiza��o de Software'
					AND     tbl_comunicado.produto IS NOT NULL
					".$sqlPostoLinha."
				UNION
				SELECT DISTINCT null::int4 AS familia                                      ,
								null::text AS descricao                                    ,
								tbl_linha.linha                                      ,
								tbl_linha.nome
					FROM    tbl_comunicado
					JOIN    tbl_linha ON tbl_comunicado.linha   = tbl_linha.linha
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					AND     tbl_comunicado.tipo = 'Atualiza��o de Software'
					AND     tbl_comunicado.produto IS NULL
					AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
					AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)
					".$sqlPostoLinha."
				UNION
					SELECT DISTINCT tbl_familia.familia                                  ,
									tbl_familia.descricao                                ,
									tbl_linha.linha                                      ,
									tbl_linha.nome
					FROM    tbl_comunicado
					JOIN    tbl_comunicado_produto ON tbl_comunicado_produto.comunicado = tbl_comunicado.comunicado
					JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado_produto.produto
					JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
					LEFT JOIN tbl_familia ON tbl_produto.familia = tbl_familia.familia
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					AND     tbl_comunicado.tipo = 'Atualiza��o de Software'
					AND     tbl_comunicado.produto IS NULL
					".$sqlPostoLinha."
				ORDER BY nome, descricao";

			$res = pg_exec ($con,$sql);

			if (pg_numrows($res) > 0) {

				$linha_anterior = "";
				echo "<dl>";

				for ($i = 0; $i < pg_numrows($res); $i++) {

					$descricao = trim(pg_result($res,$i,'descricao'));
					$familia   = trim(pg_result($res,$i,'familia'));
					$nome      = trim(pg_result($res,$i,'nome'));
					$linha     = trim(pg_result($res,$i,'linha'));

					if ($linha_anterior <> $linha) {
						echo "<br /><dt>&nbsp;&nbsp;<b><a name='$nome'>�</a></b> ";
						/* NATANAEL LIGOU QUE O LINK N�O ESTAVA APARECENDO PARA OS OUTROS POSTOS, COLOQUEI O OR PARA QUE OS DEMAIS POSTOS
						   ACESSEM A VERS�O ANTERIOR - RETIRAR QUANDO EFETIVAR (GUSTAVO) */
						if (($login_fabrica <> 19 OR $nome <> "Metais") OR ($login_fabrica == 19 )) {
							echo "<a href='info_tecnica_visualiza.php?tipo=Atualiza��o+de+Software&linha=$linha'>";
						}
						echo "$nome";
						echo "</a><br /></dt>";
					}

					if (!in_array($login_fabrica, array(19,129))) {
						echo "<dd>&nbsp;&nbsp;<b>�</b> <a href='info_tecnica_visualiza.php?tipo=Atualiza��o+de+Software&linha=$linha&familia=$familia'>$descricao</a><br /></dd>";
					}

					$linha_anterior = $linha;
				}
			}

			if ($nome == "Metais" AND $login_fabrica == 19 and $login_posto == 6359) {
				echo "<div>";
					echo "<br />";
					echo "<dd>>>&nbsp;<a href='vista_explodida_lista_produto_metais_lorenzetti.php'>";
					echo "Produtos";
					echo "</a></dd><br />";
					echo "<dd>>>&nbsp;<a href='vista_explodida_lista_peca_metais_lorenzetti.php'>";
					echo "Pe�as Reposi��o";
					echo "</a></dd>";
				echo "</div>";
			}?>

			<br />

		</td>
		<?
		if($login_fabrica <> 87){
		?>
		<td rowspan='2'class='detalhes' width='150'><?=traduz('Escolha a ')?><?php if ($login_fabrica == 129) { echo 'linha'; }else{ echo 'fam�lia';} ?><?=traduz(' que deseja consultar.')?></td>
		<?php
		}
		?>
	</tr>
</table>
<?php }

if ($login_fabrica != 1) {
 ?>

<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
	<?php
	if($login_fabrica <> 87){
	?>
	<tr bgcolor = '#efefef'>
		<td rowspan='4' width='20' valign='top'><img src='imagens/marca25.gif'></td>
		<td  class="chapeu" colspan='2'><?php
			if ($login_fabrica == 6) {
				echo traduz("<a href='lista_basica_consulta.php'>LISTA BASICA</a>");
			} else {
				echo traduz("Vista Explodida");
			}?>
		</td>
	</tr>
	<?php
	}
	?>
	<tr bgcolor = '#efefef'>
		<td colspan='2' height='5' nowrap ><?php
			if ($login_fabrica == 1) {
				echo "<br /><center><a href='#DeWalt'><img src='logos/dewalt.jpg' align='absmiddle' hspace='5' border='0'></a>";
				echo "<a href='#Eletro'><img src='logos/blackedecker.jpg' align='absmiddle' hspace='5' border='0'></a>";
				echo "<a href='#Ferramentas Black & Decker'><img src='http://www.blackdecker.com.br/imagens/logobd_eletro.gif' align='absmiddle' hspace='5' border='0'></a>";
				echo "<a href='#Porter Cable'><img src='logos/PorterCable.jpg' align='absmiddle' hspace='5' border='0'></a></center>";
			}?>
		</td>
	</tr>
	<tr bgcolor = '#efefef'>
		<td valign='top' class='menu'><?php
		$tipo_comunicado = "Vista Explodida";
		if(in_array($login_fabrica,[180,181,182])){
			$tipo_comunicado = "Despiece";
		}

			$sql = "SELECT DISTINCT tbl_familia.familia                                  ,
									tbl_familia.descricao                                ,
									tbl_linha.linha                                      ,
									tbl_linha.nome
					FROM    tbl_comunicado
					JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
					JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
					LEFT JOIN tbl_familia ON tbl_produto.familia = tbl_familia.familia
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					AND     tbl_comunicado.tipo = '$tipo_comunicado'
					AND     tbl_comunicado.produto IS NOT NULL
					".$sqlPostoLinha."
				UNION
				SELECT DISTINCT null::int4 AS familia                                      ,
								null::text AS descricao                                    ,
								tbl_linha.linha                                      ,
								tbl_linha.nome
					FROM    tbl_comunicado
					JOIN    tbl_linha ON tbl_comunicado.linha   = tbl_linha.linha
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					AND     tbl_comunicado.tipo = '$tipo_comunicado'
					AND     tbl_comunicado.produto IS NULL
					AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
					AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)
					".$sqlPostoLinha."
				UNION
					SELECT DISTINCT tbl_familia.familia                                  ,
									tbl_familia.descricao                                ,
									tbl_linha.linha                                      ,
									tbl_linha.nome
					FROM    tbl_comunicado
					JOIN    tbl_comunicado_produto ON tbl_comunicado_produto.comunicado = tbl_comunicado.comunicado
					JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado_produto.produto
					JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
					LEFT JOIN tbl_familia ON tbl_produto.familia = tbl_familia.familia
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					AND     tbl_comunicado.tipo = '$tipo_comunicado'
					AND     tbl_comunicado.produto IS NULL
					".$sqlPostoLinha."
				ORDER BY nome, descricao";

			$res = pg_exec ($con,$sql);

			if (pg_numrows($res) > 0) {

				$linha_anterior = "";
				echo "<dl>";

				for ($i = 0; $i < pg_numrows($res); $i++) {

					$descricao = trim(pg_result($res,$i,'descricao'));
					$familia   = trim(pg_result($res,$i,'familia'));
					$nome      = trim(pg_result($res,$i,'nome'));
					$linha     = trim(pg_result($res,$i,'linha'));

					if ($linha_anterior <> $linha) {
						echo "<br /><dt>&nbsp;&nbsp;<b><a name='$nome'>�</a></b> ";
						/* NATANAEL LIGOU QUE O LINK N�O ESTAVA APARECENDO PARA OS OUTROS POSTOS, COLOQUEI O OR PARA QUE OS DEMAIS POSTOS
						   ACESSEM A VERS�O ANTERIOR - RETIRAR QUANDO EFETIVAR (GUSTAVO) */
						if (($login_fabrica <> 19 OR $nome <> "Metais") OR ($login_fabrica == 19 )) {
							echo "<a href='info_tecnica_visualiza.php?tipo=".traduz('Vista+Explodida')."&linha=$linha'>";
						}
						echo "$nome";
						echo "</a><br /></dt>";
					}

					if (!in_array($login_fabrica, array(19,129))) {
						echo "<dd>&nbsp;&nbsp;<b>�</b> <a href='info_tecnica_visualiza.php?tipo=".traduz('Vista+Explodida')."&linha=$linha&familia=$familia'>$descricao</a><br /></dd>";
					}

					$linha_anterior = $linha;

				}

			}

			if ($nome == "Metais" AND $login_fabrica == 19 and $login_posto == 6359) {
				echo "<div>";
					echo "<br />";
					echo "<dd>>>&nbsp;<a href='vista_explodida_lista_produto_metais_lorenzetti.php'>";
					echo "Produtos";
					echo "</a></dd><br />";
					echo "<dd>>>&nbsp;<a href='vista_explodida_lista_peca_metais_lorenzetti.php'>";
					echo "Pe�as Reposi��o";
					echo "</a></dd>";
				echo "</div>";
			}?>

			<br />

		</td>
		<?
		if($login_fabrica <> 87){
		?>
		<td rowspan='2'class='detalhes' width='150'><?=traduz('Escolha a ')?><?php if ($login_fabrica == 129) { echo 'linha'; }else{ echo 'fam�lia';} ?><?=traduz(' que deseja consultar.')?></td>
		<?php
		}
		?>
	</tr>
</table><?php
}

if (in_array($login_fabrica,array(1,35,45))) {
	if ($login_fabrica == 1) {
		$lista_basica_vista = traduz('Vista Explodida');
		$lista_basica_vista_materiais = traduz('Vista Explodida de materiais');
	}else{
		$lista_basica_vista = traduz('Lista b�sica');
		$lista_basica_vista_materiais = traduz('Lista b�sica de materiais');
	}
	?>
	<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>

		<?php
		if ($login_fabrica == 1) {?>
			<tr bgcolor = '#fafafa'>
				<td colspan='3' height='5'>
					<br />
					<center>
						<a href='#black'><img src='logos/logo_black_info_arvore.png' align='absmiddle' hspace='5' border='0' width='670'></a>
					</center>
				</td>
			</tr>
			<tr bgcolor = '#fafafa'><td colspan='3' height='5'></td></tr>
			<tr bgcolor = '#fafafa'>
				<td rowspan='4' width='20' valign='top'><img src='imagens/marca25.gif'></td>
				<td  class="chapeu" colspan='2' ><?=$lista_basica_vista?></td>
			</tr>
			<tr bgcolor = '#fafafa'><td colspan='2' height='5'></td></tr>
		<?php
		}else{?>
			<tr bgcolor = '#fafafa'>
				<td rowspan='4' width='20' valign='top'><img src='imagens/marca25.gif'></td>
				<td  class="chapeu" colspan='2' ><?=$lista_basica_vista?></td>
			</tr>
			<tr bgcolor = '#fafafa'><td colspan='2' height='5'></td></tr>
		<?php
		}?>
		<tr bgcolor = '#fafafa'>
			<td valign='top' class='menu'>
				<?="<br /><a href='lbm_consulta.php' target='_blank'>$lista_basica_vista_materiais</a><br /><br />";?>
			</td>
			<td rowspan='2'class='detalhes' width='150'>&nbsp;</td>
		</tr>
	</table>
<?php
    if($login_fabrica == 1){
?>
    <table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
        <tr bgcolor = '#fafafa'>
            <td rowspan='3' width='20' valign='top'><img src='imagens/marca25.gif'></td>
            <td  class="chapeu" colspan='2' ><?=traduz('Produtos de troca direta')?></td>
        </tr>
        <tr bgcolor = '#fafafa'><td colspan='2' height='5'></td></tr>
        <tr bgcolor = '#fafafa'>
            <td valign='top' class='menu'>
                <?=traduz("<br /><a href='produto_troca_direta.php' target='_blank'>Clique aqui caso o produto n�o tenha vista explodida</a><br /><br />");?>
            </td>
            <td rowspan='2'class='detalhes' width='150'>&nbsp;</td>
        </tr>
    </table>
<?
    }
}

if (in_array($login_fabrica, array(152,180,181,182))) {

	$vet_tipo[] = traduz('Roteiros de Teste');
	$vet_tipo[] = traduz('Roteiros de Entrega T�cnica');
	$vet_tipo[] = traduz('Manuais de Servi�o');
	$vet_tipo[] = traduz('Manuais de Instru��o');
	$vet_tipo[] = traduz('Documenta��o Padr�o / Procedimentos');


	$tot_tipo = count($vet_tipo);//n�o usar count dentro de FOR, pois perde performance

	for ($x = 0; $x < $tot_tipo; $x++) {?>

		<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
			<tr bgcolor='#fafafa'>
				<td rowspan='3' width='20' valign='top'><img src='imagens/marca25.gif'></td>
				<td class="chapeu" colspan='2'><?=$vet_tipo[$x]?></td>
			</tr>
			<tr bgcolor = '#fafafa'><td colspan='2' height='5'></td></tr>
			<tr bgcolor = '#fafafa'>
				<td valign='top' class='menu'><?php

					$sql = "SELECT tbl_comunicado.comunicado,
								   tbl_comunicado.descricao ,
								   tbl_comunicado.mensagem  ,
								   CASE WHEN tbl_comunicado.produto IS NULL THEN prod.produto    ELSE tbl_produto.produto    END AS produto,
								   CASE WHEN tbl_comunicado.produto IS NULL THEN prod.referencia ELSE tbl_produto.referencia END AS referencia,
								   CASE WHEN tbl_comunicado.produto IS NULL THEN prod.descricao  ELSE tbl_produto.descricao  END AS descricao_produto,
								   to_char (tbl_comunicado.data,'dd/mm/yyyy') AS data,
								   tbl_comunicado.extensao
							  FROM tbl_comunicado
						 LEFT JOIN tbl_comunicado_produto ON tbl_comunicado_produto.comunicado = tbl_comunicado.comunicado
						 LEFT JOIN tbl_produto            ON tbl_produto.produto               = tbl_comunicado.produto
						 LEFT JOIN tbl_produto prod       ON prod.produto                      = tbl_comunicado_produto.produto
							 WHERE tbl_comunicado.fabrica     = $login_fabrica
							   AND (tbl_comunicado.tipo_posto = $tipo_posto  OR tbl_comunicado.tipo_posto IS NULL)
							   AND (tbl_comunicado.posto      = $login_posto OR tbl_comunicado.posto      IS NULL)
							   AND tbl_comunicado.ativo IS TRUE
							   AND tbl_comunicado.tipo = '".$vet_tipo[$x]."'
							 ORDER BY tbl_produto.descricao DESC, tbl_produto.referencia ";
							 
					$res = pg_exec ($con,$sql);

					if (pg_numrows ($res) > 0) {

						$file_types = array("gif", "jpg", "pdf", "doc", "rtf", "xls", "ppt", "zip");

						$linha_anterior = "";
						$total = pg_numrows($res);
						echo "<dl>";

						for ($i = 0; $i < $total; $i++) {

							$Xcomunicado           = trim(pg_result($res,$i,'comunicado'));
							$produto               = trim(pg_result($res,$i,'produto'));
							$referencia            = trim(pg_result($res,$i,'referencia'));
							$descricao             = trim(pg_result($res,$i,'descricao_produto'));
							$comunicado_descricao  = trim(pg_result($res,$i,'descricao'));
							$extensao              = pg_fetch_result($res, $i, "extensao");

							echo "<br /><dd>&nbsp;&nbsp;<b>-�</b> ";

							echo traduz("<input type='hidden' value='Vista Explodida' id='$Xcomunicado'>");
							
							if (is_object($s3)) {
								$link_i = "<a href='JavaScript:void(0);' name='prod_ve' rel='$Xcomunicado'>";
								$link_f = "</a>";
							} else {
								foreach ($file_types as $type) {
	                                if (file_exists("comunicados/$Xcomunicado.$type")) {
	                                	$link_i = "<a href='comunicados/$Xcomunicado.$type' target='_blank'>";
	                                	$link_f = "</a>";
	                                }
	                            }
							}

							echo $link_i;

							if (strlen($referencia) > 0) echo "$referencia - ";

							if (strlen ($descricao) > 0) {
								echo $descricao;
							} else {
								echo $comunicado_descricao;
							}

							echo $link_f;

							echo"</a></dd>";
						}

						echo "<br />";

					} else {

						echo traduz("<br /><dt>&nbsp;&nbsp;<b>�</b> Nenhum Cadastrado<br /></dt>");

					}?>
					<br />
				</td>
				<? 	if($vet_tipo[$x] != 'Documenta��o Padr�o / Procedimentos') { ?>
				<td rowspan='2'class='detalhes' width='150'><?=traduz('Escolha o produto que deseja consultar.')?></td>
				<? } ?>
			</tr>
		</table><?php

	}

}

if (!in_array($login_fabrica, array(152,180,181,182))) {
    if ($login_fabrica == 148) {
        $whereManual = " AND     tipo ILIKE 'Manual de instru%'";
    } else {
        $whereManual = " AND    tipo ILIKE 'Esquema El%'";

    }
?>

<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
	<tr bgcolor = '#fafafa'>
		<td rowspan='3' width='20' valign='top'><img src='imagens/marca25.gif'></td>
		<td  class="chapeu" colspan='2' >
			<?php 
				if ($login_fabrica == 178){
					echo "Material T�cnico";
				}else if ($login_fabrica == 148){
					echo "Manual de Instru��es";
				}else{
					echo "Esquema El�trico";
				}
			?>
		</td>
	</tr>
	<tr bgcolor = '#fafafa'><td colspan='2' height='5'></td></tr>
	<tr bgcolor = '#fafafa'>
		<td valign='top' class='menu'><?php

			if ($total_esquemas > 50) {

				$sql = "
                    SELECT  DISTINCT
                            tbl_familia.familia                              ,
                            tbl_familia.descricao                                ,
                            tbl_linha.linha                                      ,
                            tbl_linha.nome                                         ,
                            tbl_comunicado.tipo
					FROM    tbl_comunicado
					JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
					JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
               LEFT JOIN    tbl_familia ON tbl_produto.familia = tbl_familia.familia
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					$whereManual
					AND     tbl_comunicado.produto IS NOT NULL
					".$sqlPostoLinha."
				UNION
                    SELECT  DISTINCT
                            null::int4 AS familia                                   ,
                            null::text AS descricao                                 ,
                            tbl_linha.linha                                         ,
                            tbl_linha.nome                                          ,
                            tbl_comunicado.tipo
					FROM    tbl_comunicado
					JOIN    tbl_linha ON tbl_comunicado.linha   = tbl_linha.linha
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					$whereManual
					AND     tbl_comunicado.produto IS NULL
					AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
					AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)
					".$sqlPostoLinha."
				UNION
                    SELECT  DISTINCT
                            tbl_familia.familia                                  ,
                            tbl_familia.descricao                                ,
                            tbl_linha.linha                                      ,
                            tbl_linha.nome                                      ,
                            tbl_comunicado.tipo
					FROM    tbl_comunicado
					JOIN    tbl_comunicado_produto ON tbl_comunicado_produto.comunicado = tbl_comunicado.comunicado
					JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado_produto.produto
					JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
               LEFT JOIN    tbl_familia ON tbl_produto.familia = tbl_familia.familia
					WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
					AND     tbl_comunicado.ativo IS NOT FALSE
					$whereManual
					AND     tbl_comunicado.produto IS NULL
					".$sqlPostoLinha."
              ORDER BY      nome,
                            descricao";

				$res = pg_exec ($con,$sql);

				if (pg_numrows($res) > 0) {

					$linha_anterior = "";
					echo "<dl>";

					for ($i = 0; $i < pg_numrows($res); $i++) {

						$descricao = trim(pg_result($res,$i,'descricao'));
						$familia   = trim(pg_result($res,$i,'familia'));
						$nome      = trim(pg_result($res,$i,'nome'));
						$linha     = trim(pg_result($res,$i,'linha'));
						$tipo      = trim(pg_result($res,$i,'tipo'));

                        $tipoHref = ($tipo == "Manual de instru��es" || $tipo == "Manual de instrucoes") ? "Manual+de+instru��es" : "Esquema+El�trico";

						if ($linha_anterior <> $linha) {
							echo "<br /><dt>&nbsp;&nbsp;<b>�</b> <a href='info_tecnica_visualiza.php?tipo=$tipoHref&linha=$linha'>$nome</a><br /></dt>";
						}

						echo "<dd>&nbsp;&nbsp;<b>�</b> <a href='info_tecnica_visualiza.php?tipo=$tipoHref&linha=$linha&familia=$familia'>$descricao</a><br /></dd>";
						$linha_anterior = $linha;

					}

				} else {

					echo traduz("<br /><dt>&nbsp;&nbsp;<b>�</b> Nenhum Cadastrado<br /></dt>");

				}

			} else {
				$sql = "
                        SELECT  tbl_comunicado.comunicado,
                                tbl_comunicado.descricao,
                                tbl_comunicado.mensagem,
                                tbl_comunicado.tipo,
                                CASE WHEN tbl_comunicado.produto IS  NULL THEN prod.produto    ELSE tbl_produto.produto    END AS produto,
                                CASE WHEN tbl_comunicado.produto IS  NULL THEN prod.referencia ELSE tbl_produto.referencia END AS referencia,
                                CASE WHEN tbl_comunicado.produto IS  NULL THEN prod.descricao  ELSE tbl_produto.descricao  END AS descricao_produto,
                                TO_CHAR(tbl_comunicado.data,'dd/mm/yyyy') AS data,
                                tbl_comunicado.extensao,
                                tbl_comunicado.versao
                        FROM    tbl_comunicado
                   LEFT JOIN    tbl_comunicado_produto ON tbl_comunicado_produto.comunicado =  tbl_comunicado.comunicado
                   LEFT JOIN    tbl_produto            ON tbl_produto.produto               =  tbl_comunicado.produto
                   LEFT JOIN    tbl_produto prod       ON prod.produto                      =  tbl_comunicado_produto.produto
                        WHERE   tbl_comunicado.fabrica     =  $fabrica_comunicado
                        AND     (
                                    tbl_comunicado.tipo_posto   =  $tipo_posto
                                OR  tbl_comunicado.tipo_posto IS NULL
                                )
                        AND     (
                                    tbl_comunicado.posto    =  $login_posto
                                OR  tbl_comunicado.posto     IS NULL
                                )
                        AND     tbl_comunicado.ativo        IS NOT FALSE
                        $whereManual
                        AND     (
                                    tbl_comunicado.tipo_posto   =  $tipo_posto
                                OR  tbl_comunicado.tipo_posto IS NULL
                                )
                        AND     (
                                    tbl_comunicado.posto    IS NULL
                                OR  tbl_comunicado.posto    =  $login_posto
                                )
                                ".$sqlPostoLinha."
                  ORDER BY tbl_produto.descricao DESC, tbl_produto.referencia " ;

                $res = pg_exec ($con,$sql);

				if (pg_numrows ($res) > 0) {

					$file_types = array("gif", "jpg", "pdf", "doc", "rtf", "xls", "ppt", "zip");

					$linha_anterior = "";
					echo "<dl>";

					for ($i = 0; $i < pg_numrows($res); $i++) {

						$Xcomunicado           = trim(pg_fetch_result($res,$i,'comunicado'));
						$produto               = trim(pg_fetch_result($res,$i,'produto'));
						$referencia            = trim(pg_fetch_result($res,$i,'referencia'));
						$descricao             = trim(pg_fetch_result($res,$i,'descricao_produto'));
						$comunicado_descricao  = trim(pg_fetch_result($res,$i,'descricao'));
						$tipo                  = trim(pg_fetch_result($res, $i, 'tipo'));
						$extensao              = pg_fetch_result($res, $i, "extensao");
						$versao 			   = pg_fetch_result($res, $i, "versao");
						echo "<br /><dd>&nbsp;&nbsp;<b>-�</b> ";

						echo "<input type='hidden' value='Vista Explodida' id='$Xcomunicado'>";
						if (is_object($s3)) {
							if (!empty($extensao)) {
                                $link_i = "<a href='JavaScript:void(0);' name='prod_ve' rel='$Xcomunicado'>";
                                $link_f = "</a>";
                            }
						} else {
							foreach ($file_types as $type) {
                                if (file_exists("comunicados/$Xcomunicado.$type")) {
                                	$link_i =  "<a href='comunicados/$Xcomunicado.$type' target='_blank'>";
                                	$link_f = "</a>";
                                }
                            }
						}

						echo $link_i;

						if (strlen($referencia) > 0) echo "$referencia - ";

						if (strlen ($descricao) > 0) {
							echo $descricao;
							if ($login_fabrica == 175 AND !empty($versao)){
								echo " - Ordem de produ��o: $versao";
							}
						} else {
							echo $comunicado_descricao;
						}

						echo $link_f;

						echo"</dd>";
					}

					echo "<br />";

				} else {

					echo traduz("<br /><dt>&nbsp;&nbsp;<b>�</b> Nenhum Cadastrado<br /></dt>");

				}

			}?>
			<br />
		</td>
		<td rowspan='2' class='detalhes' width='150'><?=traduz('Escolha o produto que deseja consultar.')?></td>
	</tr>
</table><?php



if ($login_fabrica <> 3) {  //HD 18182 17700?>
	<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
		<tr bgcolor = '#efefef'>
			<td rowspan='3' width='20' valign='top'><img src='imagens/marca25.gif'></td>
			<td  class="chapeu" colspan='2' ><?=traduz('Altera��es T�cnicas')?></td>
		</tr>
		<tr bgcolor = '#efefef'><td colspan='2' height='5'></td></tr>
		<tr bgcolor = '#efefef'>
			<td valign='top' class='menu'><?php

			if ($total_alteracoes > 50) {

				$sql = "SELECT DISTINCT tbl_familia.familia                                  ,
										tbl_familia.descricao                                ,
										tbl_linha.linha                                      ,
										tbl_linha.nome
						FROM    tbl_comunicado
						JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
						JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
						LEFT JOIN    tbl_familia ON tbl_produto.familia = tbl_familia.familia
						WHERE   tbl_linha.fabrica    = $fabrica_comunicado
						AND     tbl_comunicado.ativo IS NOT FALSE
						AND     tbl_comunicado.tipo = 'Altera��es T�cnicas'
						AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
						AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)
						".$sqlPostoLinha."
					UNION
						SELECT DISTINCT tbl_familia.familia                                  ,
										tbl_familia.descricao                                ,
										tbl_linha.linha                                      ,
										tbl_linha.nome
						FROM    tbl_comunicado
						JOIN    tbl_comunicado_produto ON tbl_comunicado_produto.comunicado = tbl_comunicado.comunicado
						JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado_produto.produto
						JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
						LEFT JOIN    tbl_familia ON tbl_produto.familia = tbl_familia.familia
						WHERE   tbl_linha.fabrica    = $fabrica_comunicado
						AND     tbl_comunicado.ativo IS NOT FALSE
						AND     tbl_comunicado.tipo = 'Altera��es T�cnicas'
						AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
						AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)
						".$sqlPostoLinha."
					ORDER BY nome, descricao";

				$res = pg_exec($con,$sql);

				if (pg_numrows($res) > 0) {

					$linha_anterior = "";
					echo "<dl>";

					for ($i = 0 ; $i < pg_numrows($res); $i++) {

						$descricao  = trim(pg_result($res,$i,'descricao'));
						$familia    = trim(pg_result($res,$i,'familia'));
						$nome       = trim(pg_result($res,$i,'nome'));
						$linha      = trim(pg_result($res,$i,'linha'));

						if ($linha_anterior <> $linha) {
							echo "<br /><dt>&nbsp;&nbsp;<b>�</b> <a href='info_tecnica_visualiza.php?tipo=Altera��es T�cnicas&linha=$linha'>$nome</a><br /></dt>";
						}

						echo "<dd>&nbsp;&nbsp;<b>�</b> <a href='info_tecnica_visualiza.php?tipo=Altera��es T�cnicas&linha=$linha&familia=$familia'>$descricao</a><br /></dd>";
						$linha_anterior = $linha;

					}

				} else {

					echo traduz("<br /><dt>&nbsp;&nbsp;<b>�</b> Nenhum Cadastrado<br /></dt>");

				}

			} else {

				$sql = "SELECT	tbl_comunicado.comunicado,
								tbl_comunicado.descricao ,
								tbl_comunicado.mensagem  ,
								CASE WHEN tbl_comunicado.produto IS NULL THEN prod.produto ELSE tbl_produto.produto END AS produto,
								CASE WHEN tbl_comunicado.produto IS NULL THEN prod.referencia ELSE tbl_produto.referencia END AS referencia,
								CASE WHEN tbl_comunicado.produto IS NULL THEN prod.descricao ELSE tbl_produto.descricao END AS descricao_produto,
								to_char (tbl_comunicado.data,'dd/mm/yyyy') AS data,
								tbl_comunicado.extensao,
								tbl_comunicado.versao
						FROM	tbl_comunicado
						LEFT JOIN tbl_comunicado_produto ON tbl_comunicado_produto.comunicado = tbl_comunicado.comunicado
						LEFT JOIN tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
						LEFT JOIN tbl_produto prod ON prod.produto = tbl_comunicado_produto.produto
						WHERE	tbl_comunicado.fabrica = $fabrica_comunicado
						AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
						AND     ((tbl_comunicado.posto           = $login_posto) OR (tbl_comunicado.posto           IS NULL))
						AND    tbl_comunicado.ativo IS NOT FALSE
						AND	tbl_comunicado.tipo = 'Altera��es T�cnicas'
						AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
						AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)
						".$sqlPostoLinha."
						ORDER BY tbl_produto.descricao DESC, tbl_produto.referencia " ;

				$res = pg_exec($con,$sql);

				if (pg_numrows($res) > 0) {

					$file_types = array("gif", "jpg", "pdf", "doc", "rtf", "xls", "ppt", "zip");

					$linha_anterior = "";
					echo "<dl>";

					for ($i = 0 ; $i < pg_numrows($res); $i++) {

						$Xcomunicado          = trim(pg_result($res,$i,'comunicado'));
						$produto              = trim(pg_result($res,$i,'produto'));
						$referencia           = trim(pg_result($res,$i,'referencia'));
						$descricao            = trim(pg_result($res,$i,'descricao_produto'));
						$comunicado_descricao = trim(pg_result($res,$i,'descricao'));
						$extensao             = pg_fetch_result($res,$i,"extensao");
						$versao 			  = pg_fetch_result($res,$i,'versao');
						echo "<br /><dd>&nbsp;&nbsp;<b>-�</b> ";

						echo "<input type='hidden' value='Vista Explodida' id='$Xcomunicado'>";
						if (is_object($s3)) {
							if (!empty($extensao)) {
                            	$link_i = "<a href='JavaScript:void(0);' name='prod_ve' rel='$Xcomunicado'>";
                            	$link_f = "</a>";
                          	}
						} else {
								foreach ($file_types as $type) {
	                                if (file_exists("comunicados/$Xcomunicado.$type")) {
	                                	$link_i = "<a href='comunicados/$Xcomunicado.$type' target='_blank'>";
	                            		$link_f = "</a>";
	                            	}
	                            }
						}

						echo $link_i;

						if(strlen($referencia)>0) echo "$referencia - ";

						if (strlen ($descricao) > 0) {
							echo $descricao;
							if ($login_fabrica == 175){
								echo traduz("- Ordem de produ��o: $versao");
							}
						} else {
							echo $comunicado_descricao;
						}

						echo $link_f;

						echo"</dd>";

					}

					echo "<br />";

				} else {

					echo traduz('<br /><dt>&nbsp;&nbsp;<b>�</b> Nenhum Cadastrado<br /></dt>');

				}
			}?>
			<br />
			</td>
			<td rowspan='2'class='detalhes' width='150'><?=traduz('Escolha a fam�lia que deseja consultar.')?></td>
		</tr>
	</table>

	<table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
		<tr bgcolor = '#fafafa'>
			<td rowspan='3' width='20' valign='top'><img src='imagens/marca25.gif'></td>
			<td class="chapeu" colspan='2' ><?=traduz('Manual T�cnico')?></td>
		</tr>
		<tr bgcolor = '#fafafa'>
			<td colspan='2' height='5'></td>
		</tr>
		<tr bgcolor = '#fafafa'>
			<td valign='top' class='menu'><?php

				if ($total_manual > 50) {//alterado Gustavo HD 8213

					$sql = "SELECT DISTINCT tbl_familia.familia                                  ,
											tbl_familia.descricao                                ,
											tbl_linha.linha                                      ,
											tbl_linha.nome
							FROM    tbl_comunicado
							JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
							JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
							LEFT JOIN    tbl_familia ON tbl_produto.familia = tbl_familia.familia
							WHERE   tbl_linha.fabrica    = $fabrica_comunicado
							AND     tbl_comunicado.ativo IS NOT FALSE
							AND     (tbl_comunicado.tipo = 'Manual T�cnico' OR tbl_comunicado.tipo = 'Manual Tecnico')
							AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
							AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)
							".$sqlPostoLinha."
						UNION
							SELECT DISTINCT tbl_familia.familia                                  ,
											tbl_familia.descricao                                ,
											tbl_linha.linha                                      ,
											tbl_linha.nome
							FROM    tbl_comunicado
							JOIN    tbl_comunicado_produto ON tbl_comunicado_produto.comunicado = tbl_comunicado.comunicado
							JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado_produto.produto
							JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
							LEFT JOIN    tbl_familia ON tbl_produto.familia = tbl_familia.familia
							WHERE   tbl_linha.fabrica    = $fabrica_comunicado
							AND     tbl_comunicado.ativo IS NOT FALSE
							AND     (tbl_comunicado.tipo = 'Manual T�cnico' OR tbl_comunicado.tipo = 'Manual Tecnico')
							AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
							AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)
							".$sqlPostoLinha."
						ORDER BY nome, descricao";

					$res = pg_exec ($con,$sql);

					if (pg_numrows($res) > 0) {

						$linha_anterior = "";
						echo "<dl>";

						for ($i = 0 ; $i < pg_numrows($res); $i++) {

							$descricao = trim(pg_result($res,$i,'descricao'));
							$familia   = trim(pg_result($res,$i,'familia'));
							$nome      = trim(pg_result($res,$i,'nome'));
							$linha     = trim(pg_result($res,$i,'linha'));

							if ($linha_anterior <> $linha) {
								echo "<br /><dt>&nbsp;&nbsp;<b>�</b> <a href='info_tecnica_visualiza.php?tipo=Manual T�cnico&linha=$linha'>$nome</a><br /></dt>";
							}

							echo "<dd>&nbsp;&nbsp;<b>�</b> <a href='info_tecnica_visualiza.php?tipo=Manual T�cnico&linha=$linha&familia=$familia'>$descricao</a><br /></dd>";
							$linha_anterior = $linha;

						}

					} else {

						echo traduz('<br /><dt>&nbsp;&nbsp;<b>�</b> Nenhum Cadastrado<br /></dt>');

					}

				} else {

					$sql = "SELECT	tbl_comunicado.comunicado,
									tbl_comunicado.descricao ,
									tbl_comunicado.mensagem  ,
									CASE WHEN tbl_comunicado.produto IS NULL THEN prod.produto ELSE tbl_produto.produto END AS produto,
									CASE WHEN tbl_comunicado.produto IS NULL THEN prod.referencia ELSE tbl_produto.referencia END AS referencia,
									CASE WHEN tbl_comunicado.produto IS NULL THEN prod.descricao ELSE tbl_produto.descricao END AS descricao_produto,
									to_char (tbl_comunicado.data,'dd/mm/yyyy') AS data,
									tbl_comunicado.extensao,
									tbl_comunicado.versao
							FROM	tbl_comunicado
							LEFT JOIN tbl_comunicado_produto ON tbl_comunicado_produto.comunicado = tbl_comunicado.comunicado
							LEFT JOIN tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
							LEFT JOIN tbl_produto prod ON prod.produto = tbl_comunicado_produto.produto
							WHERE	tbl_comunicado.fabrica = $fabrica_comunicado
							AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
							AND     ((tbl_comunicado.posto           = $login_posto) OR (tbl_comunicado.posto           IS NULL))
							AND    tbl_comunicado.ativo IS NOT FALSE
							AND     (tbl_comunicado.tipo = 'Manual T�cnico' OR tbl_comunicado.tipo = 'Manual Tecnico')
							AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
							AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)
							".$sqlPostoLinha."
							ORDER BY tbl_produto.descricao DESC, tbl_produto.referencia " ;

					$res = pg_exec ($con,$sql);

					if (pg_numrows ($res) > 0) {

						$file_types = array("gif", "jpg", "pdf", "doc", "rtf", "xls", "ppt", "zip");

						$linha_anterior = "";
						echo "<dl>";

						for ($i = 0; $i < pg_numrows($res); $i++) {
							$Xcomunicado           = trim(pg_result($res,$i,'comunicado'));
							$produto               = trim(pg_result($res,$i,'produto'));
							$referencia            = trim(pg_result($res,$i,'referencia'));
							$descricao             = trim(pg_result($res,$i,'descricao_produto'));
							$comunicado_descricao  = trim(pg_result($res,$i,'descricao'));
							$extensao              = pg_fetch_result($res,$i,"extensao");
							$versao 			   = pg_fetch_result($res,$i,"versao");

							echo "<br /><dd>&nbsp;&nbsp;<b>-�</b> ";
							echo "<input type='hidden' value='Vista Explodida' id='$Xcomunicado'>";
							if (is_object($s3)) {
								if (!empty($extensao)) {
	                            	$link_i = "<a href='JavaScript:void(0);' name='prod_ve' rel='$Xcomunicado'>";
	                            	$link_f = "</a>";
	                          	}
							} else {
								foreach ($file_types as $type) {
	                                if (file_exists("comunicados/$Xcomunicado.$type")) {
	                                	$link_i = "<a href='comunicados/$Xcomunicado.$type' target='_blank'>";
	                                	$link_f = "</a>";
	                                }
	                            }
							}

							echo $link_i;

							if (strlen($referencia) > 0) echo "$referencia - ";

							if (strlen ($descricao) > 0) {
								echo $descricao;
								if ($login_fabrica == 175){
									echo "- Ordem de produ��o: $versao";
								}
							} else {
								echo $comunicado_descricao;
							}

							echo $link_f;

							echo"</dd>";

						}

						echo "<br />";

					} else {

						echo traduz('<br /><dt>&nbsp;&nbsp;<b>�</b> Nenhum Cadastrado<br /></dt>');

					}

				}?>
				<br />
			</td>
			<td rowspan='2'class='detalhes' width='150'><?=traduz('Escolha o produto que deseja consultar.')?></td>
		</tr>
		<?php
		if ($login_fabrica != 175) {
		?>
			<tr bgcolor='#D9E2EF'>
				<td colspan='3'><img src='imagens/spacer.gif' height='3'></td>
			</tr>
		<?php
		}
		?>
	</table><?php
	if ($login_fabrica == 175) {
		?>
	   
	   <table width="700" border="0" cellspacing="0" cellpadding="0" align='center'>
		   <tr bgcolor = '#efefef'>
			   <td rowspan='3' width='20' valign='top'><img src='imagens/marca25.gif'></td>
			   <td  class="chapeu" colspan='2'><?=traduz('Procedimentos')?></td>
		   </tr>
		   <tr bgcolor = '#efefef'>
			   <td colspan='2' height='5' nowrap ></td>
		   </tr>
		   <tr bgcolor = '#efefef'>
			   <td valign='top' class='menu'><?php
	   
				   $sql = "SELECT DISTINCT tbl_familia.familia                                  ,
										   tbl_familia.descricao                                ,
										   tbl_linha.linha                                      ,
										   tbl_linha.nome
						   FROM    tbl_comunicado
						   JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado.produto
						   JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
						   LEFT JOIN tbl_familia ON tbl_produto.familia = tbl_familia.familia
						   WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
						   AND     tbl_comunicado.ativo IS NOT FALSE
						   AND     tbl_comunicado.tipo = 'Procedimentos'
						   AND     tbl_comunicado.produto IS NOT NULL
						   ".$sqlPostoLinha."
					   UNION
					   SELECT DISTINCT null::int4 AS familia                                      ,
									   null::text AS descricao                                    ,
									   tbl_linha.linha                                      ,
									   tbl_linha.nome
						   FROM    tbl_comunicado
						   JOIN    tbl_linha ON tbl_comunicado.linha   = tbl_linha.linha
						   WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
						   AND     tbl_comunicado.ativo IS NOT FALSE
						   AND     tbl_comunicado.tipo = 'Procedimentos'
						   AND     tbl_comunicado.produto IS NULL
						   AND    (tbl_comunicado.tipo_posto = $tipo_posto  OR  tbl_comunicado.tipo_posto IS NULL)
						   AND    (tbl_comunicado.posto IS NULL OR tbl_comunicado.posto = $login_posto)
						   ".$sqlPostoLinha."
					   UNION
						   SELECT DISTINCT tbl_familia.familia                                  ,
										   tbl_familia.descricao                                ,
										   tbl_linha.linha                                      ,
										   tbl_linha.nome
						   FROM    tbl_comunicado
						   JOIN    tbl_comunicado_produto ON tbl_comunicado_produto.comunicado = tbl_comunicado.comunicado
						   JOIN    tbl_produto ON tbl_produto.produto = tbl_comunicado_produto.produto
						   JOIN    tbl_linha   ON tbl_produto.linha   = tbl_linha.linha
						   LEFT JOIN tbl_familia ON tbl_produto.familia = tbl_familia.familia
						   WHERE   tbl_comunicado.fabrica    = $fabrica_comunicado
						   AND     tbl_comunicado.ativo IS NOT FALSE
						   AND     tbl_comunicado.tipo = 'Procedimentos'
						   AND     tbl_comunicado.produto IS NULL
						   ".$sqlPostoLinha."
					   ORDER BY nome, descricao";
	   
				   $res = pg_exec ($con,$sql);
	   
				   if (pg_numrows($res) > 0) {
	   
					   $linha_anterior = "";
					   echo "<dl>";
	   
					   for ($i = 0; $i < pg_numrows($res); $i++) {
	   
						   $descricao = trim(pg_result($res,$i,'descricao'));
						   $familia   = trim(pg_result($res,$i,'familia'));
						   $nome      = trim(pg_result($res,$i,'nome'));
						   $linha     = trim(pg_result($res,$i,'linha'));
	   
						   if ($linha_anterior <> $linha) {
							   echo "<br /><dt>&nbsp;&nbsp;<b><a name='$nome'>�</a></b> ";
							   echo "<a href='info_tecnica_visualiza.php?tipo=Procedimentos&linha=$linha'>";
							   echo "$nome";
							   echo "</a><br /></dt>";
						   }
	   
							echo "<dd>&nbsp;&nbsp;<b>�</b> <a href='info_tecnica_visualiza.php?tipo=Procedimentos&linha=$linha&familia=$familia'>$descricao</a><br /></dd>";
	   
						   $linha_anterior = $linha;
	   
					   }
	   
				   }
				   ?>
				   <br />
			   </td>
			   <td rowspan='2'class='detalhes' width='150'><?=traduz('Escolha a fam�lia que deseja consultar.')?></td>
		   </tr>
		   <tr bgcolor='#D9E2EF'>
				<td colspan='3'><img src='imagens/spacer.gif' height='3'></td>
			</tr>
	   </table><?php
	   }
}
}
}
include('rodape.php');
?>
