<?php
include '../../dbconfig.php';
include '../../includes/bconnect-inc.php';
include '../../helpdesk/mlg_funciones.php';
$sqlInsertLog = "INSERT INTO tbl_log_conexao(programa) VALUES ('$PHP_SELF')";
$resInsertLog = pg_query($con, $sqlInsertLog);

$fabrica = 80;

$sql_linhas = "SELECT linha, nome FROM tbl_linha WHERE fabrica = $fabrica";
$res_linhas = pg_query($con, $sql_linhas);
if (is_resource($res_linhas)) {
    $a_tot_linhas = pg_fetch_all($res_linhas);
    foreach ($a_tot_linhas as $trash => $row_linha) {
        extract($row_linha);
    	$linhas[$linha] = $nome;
    }
    unset($a_tot_linhas,$linha,$nome);
} else {
    $msg_erro[]= 'Erro ao acessar o Sistema Telecontrol.';
}

//	AJAX
if ($_GET['action']=='cidades') {
	$estado = $_GET['estado'];
	$linha  = $_GET['linha'];
	if ($estado == "") exit("<OPTION SELECTED>Sem resultados</OPTION>");

	if(strlen($estado) > 0) {
		$tot_i = false;
		echo $debug = $_REQUEST['debug'];
		$sql_cidades =	"SELECT  LOWER(mlg_cidade)||'#('||count(mlg_cidade)||')' AS cidade
							FROM (SELECT tbl_posto_fabrica.posto,tipo_posto,UPPER(TRIM(TRANSLATE(contato_cidade,'������������������������������������������',
																							  'aaaaaeeeeiiiioooouuucAAAAAEEEEIIIIOOOOUUUC')))
														AS mlg_cidade,
										contato_estado	AS mlg_estado
							FROM tbl_posto_fabrica
							JOIN tbl_posto_linha ON tbl_posto_linha.posto = tbl_posto_fabrica.posto AND tbl_posto_linha.linha = $linha
								WHERE credenciamento<>'DESCREDENCIADO'
									AND tbl_posto_fabrica.posto NOT IN(6359,20462)
									AND tbl_posto_fabrica.credenciamento <> 'DESCREDENCIADO'
									AND tbl_posto_fabrica.tipo_posto <> 163
									AND contato_estado='$estado' AND fabrica=$fabrica) mlg_posto
							GROUP BY mlg_posto.mlg_cidade ORDER BY cidade ASC";
		$res_cidades = pg_query($con,$sql_cidades);
        if (is_resource($res_cidades)) {
    		$tot_i       = pg_num_rows($res_cidades);
            if ($tot_i == 0) exit("<OPTION SELECTED>Sem resultados</OPTION>");

    		$cidades     = pg_fetch_all($res_cidades);
            if ($tot_i) echo "<option></option>";
    		if ($debug) pre_echo($cidades, "$tot_i postos");
            foreach($cidades as $info_cidade) {
                list($cidade_i,$cidade_c) =preg_split('/#/',htmlentities($info_cidade['cidade']));
                $sel      = (strtoupper($cidade) == strtoupper($cidade_i))?" SELECTED":"";
    			echo "\t\t\t<OPTION value='$cidade_i'$sel>".ucwords($cidade_i." ".$cidade_c)."</OPTION>\n";
            }
        } else {
    		if ($debug) pre_echo($sql_cidades, "Resultado: $tot_i registro(s)");
            exit('KO|Erro ao acessar o Sistema Telecontrol.');
        }
	}
	exit;
}

if ($_GET['action']=='postos') {
	$estado = $_GET['estado'];
	if (isset($_GET['cidade'])) $cidade=strtoupper(utf8_decode($_GET['cidade']));
	if ($estado == "" or $cidade=="") exit("Erro na consulta!");

	$sql = "SELECT
				tbl_posto.posto,
				tbl_posto_fabrica.codigo_posto,
				TRIM(tbl_posto_fabrica.contato_endereco)	AS endereco,
				tbl_posto_fabrica.contato_numero			AS numero,
                TRIM(tbl_posto.nome)						AS nome,
				LOWER(TRIM(TRANSLATE(tbl_posto_fabrica.contato_cidade,'���������������������',
																'���������������������')))
															AS cidade,
				tbl_posto_fabrica.contato_estado			AS estado,
				tbl_posto_fabrica.contato_bairro			AS bairro,
				tbl_posto_fabrica.contato_cep				AS cep,
				tbl_posto_fabrica.nome_fantasia,
                tbl_posto.latitude,
                tbl_posto.longitude,
                TRIM(tbl_posto_fabrica.contato_email)		AS email,
				tbl_posto_fabrica.contato_fone_comercial	AS fone,
				ARRAY(SELECT DISTINCT tbl_linha.nome
							FROM tbl_produto
							RIGHT JOIN tbl_posto_linha ON tbl_produto.linha = tbl_posto_linha.linha
							JOIN tbl_linha		 ON tbl_linha.linha = tbl_produto.linha
						WHERE tbl_produto.ativo IS TRUE
						 AND tbl_posto_linha.posto = tbl_posto.posto
						 AND tbl_linha.fabrica = $fabrica)	AS linhas_posto
			FROM  tbl_posto
			JOIN  tbl_posto_fabrica USING (posto)
			JOIN  tbl_fabrica       USING (fabrica)
			JOIN  tbl_posto_linha ON tbl_posto_linha.posto = tbl_posto_fabrica.posto AND tbl_posto_linha.linha = $linha
			WHERE tbl_posto_fabrica.fabrica = $fabrica
			  AND tbl_posto_fabrica.contato_estado ILIKE '$estado'
			  AND UPPER(TRIM(TRANSLATE(contato_cidade,'������������������������������������������',
													  'aaaaaeeeeiiiioooouuucAAAAAEEEEIIIIOOOOUUUC')))
						ILIKE '%".tira_acentos($cidade)."%'
			AND tbl_posto.posto not in(6359,20462)
			AND tbl_posto_fabrica.credenciamento <> 'DESCREDENCIADO'
			AND tbl_posto_fabrica.tipo_posto <> 163
			AND tbl_posto_fabrica.divulgar_consumidor IS TRUE
			ORDER BY tbl_posto_fabrica.contato_bairro, tbl_posto.nome";
		$res = pg_query ($con,$sql);
		$total_postos = ($tem_mapa=pg_num_rows($res));
		$cidade = pg_fetch_result($res, $total_postos-1, cidade);

		echo "<table cellspacing='1' align='center' id='postos'>\n";
		echo "<caption>Rela&ccedil;&atilde;o de Postos ";
		echo ($cidade<>"")?"da cidade de <span class='nome_cidade'>".change_case($cidade,'l')."</span> ":"";
		echo ($estado=='DF')?"no Distrito Federal":"no estado de {$estados[$estado]}";
		echo "</caption>";

		if($total_postos > 0){?>
        <thead>
            <tr align='center' class='bold'>
				<th style="width:250px" width='250'>Nome do Posto</th>
				<th style="width:300px" width='300'>Endere�o</th>
				<th style="width: 88px" width= '88'>Telefone</th>
				<th style="width: 32px" width= '32'>E-Mail</th>
				<th style="width: 25px" width= '25'>Mapa</th>
				<th style="width: 70px" width= '70'>Atende...</th>
            </tr>
        </thead>
<?
			for ($i = 0 ; $i < $total_postos ; $i++) {
                $row = pg_fetch_array($res, $i);
                foreach ($row as $campo => $valor) {
                    $$campo = trim($valor);
                }
				$end_completo = $bairro  . " - " . $endereco . ", " . $numero;
				$end_mapa     = "$endereco, $numero, $cep, $cidade, $estado, Brasil";
// 				if (is_numeric($longitude) and is_numeric($latitude)) { // lat e long est�o ao contr�rio no banco
				$link_mapa = "<a title='Localizar no mapa' href='http://maps.google.com/maps?f=q&source=s_q&hl=pt-BR&q=$end_mapa&ie=windows-1252' target='_blank'>".
							 "<img src='http://www.google.com/options/icons/maps.gif' width='16'></a>";
// 				}

				echo "\t\t<tr>";
				$posto_nome = iif((strlen($nome_fantasia)>0),$nome_fantasia,$nome);
				$tooltip .= " title='".iif(($posto_nome==$nome_fantasia),"$posto_nome ($nome)",
										iif((strlen($posto_nome)>=25),"$posto_nome"),'')."'";
				echo "\t\t\t<td$tooltip>$posto_nome</td>";
				unset($tooltip);
				$tooltip = (strlen($end_completo)>=35)?" title='$end_completo'":"";
				echo "\t\t\t<td$tooltip>$end_completo</td>";
				echo "\t\t\t<td align='right'>$fone</td>";
				echo "\t\t\t<td title='$email'>";
                if (strlen($email)>5 and is_email($email)) {
                	echo "<a href='mailto:".strtolower($email)."'><img src='/mapa_rede/imagens/email_envelope.jpg'></a>";
                } else {
					echo "<img src='/mlg/imagens/cross.png'>";
				}
				echo "</td>";
				echo "\t\t\t<td>$link_mapa</td>";
				echo "\t\t\t<td>\n\t\t\t\t<select id='$posto' name='linhas_$posto'>";
				foreach ($linhas as $linha) {
                	echo "\t\t\t\t\t<option>".$linhas[$linha]."</option>\n";
                }
				echo "\t\t\t\t</select>\n\t\t\t</td>";
				echo "\t\t</tr>";
				unset ($end_mapa, $link_mapa, $end_completo, $posto_nome, $email, $tooltip);
			}
		}else{
			echo "\t<tr><td class='fontenormal'> Nenhuma Assist�ncia T�cnica encontrada.</td></tr>";
		}
		echo "</table>\n<br>";
	exit;
}
//  FIM AJAX
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/Conteudo.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Mapa da Rede Autorizada - Amvox - A Marca do seu lar</title>
<!-- InstanceEndEditable -->
<link href="https://www.amvox.com.br/novo/css/estilo-internas.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<style type="text/css">
	#mapabr {position:relative;text-align:center;float:right;width:540px}
	#mapabr img {border: 0 solid transparent}

	form {
		margin: 10px 5px;
		padding: 1em 1ex;
	}
    form fieldset {
        border-top: 1px solid white;
        margin: 0 auto;
    }
	form legend {
		font-weight:bold;
		font-size: 14px;
        padding-bottom: 1.6em;
	}
	#sel_cidade, #tblres {display: none;}
	area {cursor: pointer}
	a img {border: 0 solid transparent;}
	label, select {z-index:10;width: 155px;background-color:black;color:white}
	button {margin-left: 55px;width: 100px;}

    #tblres {
        width: 540px;
        margin: 10px auto;
        position:relative;
    }
	#postos {
		position: relative;
        margin: 0 auto;
		table-layout: fixed;
	    background-color: transparent;
		padding: 0;
		font-size: 12px;
		white-space: nowrap;
		font-family: Tahoma, Geneva, Trebuchet MS, Arial;
		border-collapse: separate;
		border-spacing: 1px;
	}
	#postos tr {
		height: 26px;
	}
	#postos tr td {
		background: #eaeaea;
	/*	font-weight: bold;*/
		padding: 4px 2px;
		border:	1px solid #666;
		border-right:  1px solid #eee;
		border-bottom: 1px solid #eee;
	    color: #102d65;
		width: 120px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		-o-text-overflow: ellipsis;
		text-transform: uppercase;
        text-align: left;
		cursor: default;
        background-color:rgba(255,255,255,0.6);
	}
	#postos th {width: 250px}
	#postos th+th {width: 300px}
	#postos th+th+th {width:  88px}
	#postos th+th+th+th {width:  32px}
	#postos th+th+th+th+th {width:  25px}
	#postos th+th+th+th+th+th {width:  70px}
	#postos td+td {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		-o-text-overflow: ellipsis;
		width: 200px;
	}
	#postos td+td+td {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		-o-text-overflow: ellipsis;
		text-align: right;
		width:  82px;
	}
	#postos td+td+td+td {
		width:  28px;
	}
	#postos td+td+td+td+td {
		text-align: center;
		width:  24px;
	}
	#postos caption {
		font: normal bold 1.2em "Trebuchet MS",Helvetica,Arial,sans-serif;
		padding-bottom: 4px;
		border-bottom: 1px solid white;
		text-align: center;
	}
    #postos td > select {width: 64px}
	.nome_cidade {text-transform:capitalize;text-decoration:underline; font: inherit}
	/* content styles */
	.highlight {
		background-color: #fffebb;
	}
	.branco {
		font-weight: bold;
		color: white;
	}
	.azul {color:#102d65}
	.fundo_vermelho {background-color: #A10F15}
	.cinza {#666}
	.bold {
		font-weight: bold;
	}
    </style>

	<script type="text/javascript">
	var php_self = window.location.pathname;
	$(function() {
//  Adiciona um evento onClick para cada 'area' que vai alterar o valor do SELECT 'estado'
		$('map area').click(function() {
			$('#estado').val($(this).attr('name'));
			$('#estado').change();
		});
		$('#sel_cidade').hide('fast');

//		Quando muda o valor do select 'estado' requisita as cidades onde tem postos autorizados e os
//		insere no select 'cidades'
		$('#estado').change(function() {
		    var estado = $('#estado').val();
		    if (estado == '') {
				$('#sel_cidade').fadeOut(500);
				$('#tblres').html('').fadeOut(400);
				return false;
			}
			$.get(php_self, {'action': 'cidades','estado': estado, 'linha': $('#linha').val()},
			  function(data){
				if (data.indexOf('Sem resultados') < 0) {
					$('#sel_cidade').fadeIn(500);
				    $('#cidade').html(data).val('').removeAttr('disabled');
				} else {
				    $('#cidade').html(data).val('Sem resultados').attr('disabled','disabled');
				}
				$('#tblres').html('').fadeOut(400);
			});
		});
		$('#cidade').change(function() {
			$('#tblres').fadeOut('fast');
		    var estado = $('#estado').val();
			var cidade = $('#cidade').val();
			$.get(php_self, {'action': 'postos','estado': estado,'cidade': cidade, 'linha': $('#linha').val()},
			  function(data){
			    if (data.indexOf('Nenhuma') < 0) {
					if ($('#mapabr fieldset > img').width() > 250) {
						$('#mapabr fieldset > img').animate({
							width: 150,
							marginRight: '+=125'
							}, function() {
							$(this).bind('mouseover', function() {
								$(this).animate({width: 276,marginRight: '-=125'});
								$('#mapabr fieldset').animate({height: 300});
								$(this).unbind('mouseover');
							});
						});
					}
					$('#mapabr fieldset').animate({height: 175});
					$('#tblres').html(data).fadeIn('normal');
				}
			  });
		});
		$('#linha').change(function() {
			$('#tblres').fadeOut('fast');
		    var estado = $('#estado').val();
			var cidade = $('#cidade').val();
			if (cidade != '' && estado != '') {
				$('#cidade').change();
				return false;
			}
		});
		$('button').click(function () {
			$('#cidade').change();
			return false;
		});
	});
    </script>
</head>

<body>
    <div id="corpo">
        <div id="topo">
            <a href="index.php"><img src="https://www.amvox.com.br/novo/imagens/amvox.png" width="213" height="49"
                alt="Amvox" title="Amvox" class="logo"/>
            </a>
            <ul id="menu1-top">
                <li><a href="https://www.amvox.com.br/novo/lista.php?QID=15">Novidades</a></li>
                <li><a href="https://www.amvox.com.br/novo/representantes.php">Representantes</a></li>
                <li><a href="#">Assist&ecirc;ncia T&ecirc;cnica</a></li>
                <li><a href="#">Acesso Restrito</a></li>
                <li><a href="https://www.amvox.com.br/novo/lista.php?QID=18">Sobre a Amvox</a></li>
                <li><a href="https://www.amvox.com.br/novo/contato.php">Contato</a></li>
            </ul>
            <ul id="menu2-top">
                        <li><a href="https://www.amvox.com.br/novo/produtos.php?CAT=2">Para sua Sala</a></li>
                            <li><a href="https://www.amvox.com.br/novo/produtos.php?CAT=3">Para sua Cozinha</a></li>
                            <li><a href="https://www.amvox.com.br/novo/produtos.php?CAT=4">Para sua Beleza </a></li>
                        </ul>
            <form name="busca" method="get" action="busca.php" onsubmit="return valida(this)">
            	<input type="text" name="texto" rel="required" />
                <input type="submit" value="" />
            </form>
            <h1><span>A MARCA DO SEU LAR</span></h1>
        </div>
		<div id='mapabr'>
			<form>
				<fieldset>
					<legend>&nbsp;Mapa da Rede&nbsp;</legend>
					<img src='../imagens/mapa_vermelho.png' alt='Mapa do Brasil' title='Selecione o Estado'
						usemap='#Map2' style='float:left;' />
					<div id='sel_linha'>
					<label for="linha">Selecione a fam�lia de produtos:</label><br />
					<select name="linha" id="linha">
					<?
					foreach ($linhas as $linha=>$nome) {
                    	echo str_repeat("\t", 8)."<option value='$linha'>$nome</option>\n";
                    }
					?>
					</select>
					</div><br />
					<label for="estado">Selecione o Estado:</label><br />
					<select title="Selecione o Estado" name="estado" id="estado" tabindex="1">
						<option></option>
					<?
					foreach ($estados as $uf=>$nome) {
                    	echo str_repeat("\t", 8)."<option value='$uf'>$nome</option>\n";
                    }
					?>
                    </select><br />
					<div id='sel_cidade'>
						<label for="cidade">Selecione a cidade:</label><br />
						<select name="cidade" id="cidade" tabindex="2">
						</select>
					</div><br />
					<button type="submit" tabindex="3">Pesquisar</button>
				</fieldset>
			</form>
<!--	<div style='position: absolute;bottom:2em;right:2.5em;text-align:right'>
			Se a sua cidade n�o se encontra na rela��o,<br>pode fazer a pesquisa no <a href="http://www.telecontrol.com.br/mapa_rede.php?fabrica=80" target='_blank'> <i>site</i> da <b>Telecontrol</b></a>.
		</div>
-->
		<div id='tblres'></div>
		</div>
        <div class="rodape">
       	  <div class="cont-rodape-amvox">
       	    	<img src="https://www.amvox.com.br/novo/imagens/amvox.jpg" width="61" height="13" alt="AMVOX" />
                <h3>A MARCA DO SEU LAR</h3>
                <p>2010 - Todos os direitos Reservados.</p>
            </div>
            <a href="https://www.olhando.com.br" target="_blank">
                <img class="olhando-rodape" src="https://www.amvox.com.br/novo/imagens/olhando.jpg" width="51" height="7"
                        alt="Olhando Comunica��o" longdesc="https://www.olhando.com.br" />
            </a>
        </div>
    	<map name="Map2" id="Map2">
    		<area shape="poly" name="RS" coords="122,238,142,221,164,232,148,262">
    		<area shape="poly" name="SC" coords="143,214,172,215,169,235,143,219">
    		<area shape="poly" name="PR" coords="138,202,148,191,166,192,175,207,171,214,139,213">
    		<area shape="poly" name="SP" coords="152,187,162,173,182,174,186,187,188,194,197,190,197,198,177,206,168,190">

    		<area shape="poly" name="MS" coords="136,195,156,171,138,159,124,159,117,182">
    		<area shape="poly" name="MT" coords="117,151,143,151,160,127,160,106,120,105,111,101,98,102,107,117,100,131,102,142">
    		<area shape="poly" name="RO" coords="93,126,98,118,94,113,86,105,86,100,80,93,73,102,67,108,67,116,77,121">
    		<area shape="poly" name="AC" coords="50,106,10,91,13,101,23,104,29,104,30,112,44,113">
    		<area shape="poly" name="AM" coords="11,87,53,101,74,88,105,91,117,55,103,43,89,50,76,43,77,30,62,37,43,30,40,38,33,75,21,75,13,82">
    		<area shape="poly" name="RR" coords="74,13,74,18,82,25,84,41,93,40,102,31,96,21,97,9,90,11">
    		<area shape="poly" name="PA" coords="112,33,114,40,127,50,117,82,121,95,162,99,174,77,173,68,193,48,172,54,158,55,145,45,133,25">
    		<area shape="poly" name="AP" coords="145,25,153,23,157,13,164,29,153,41">
    		<area shape="poly" name="MA" coords="196,50,185,72,194,90,212,82,215,59">

    		<area shape="poly" name="TO" coords="179,83,165,120,189,128,185,101">
    		<area shape="poly" name="GO" coords="159,166,148,157,165,131,188,136,170,151">
    		<area shape="poly" name="PI" coords="201,92,216,86,223,64,228,85,219,98,207,99,206,107,199,107">
    		<area shape="poly" name="RJ" coords="206,201,202,190,214,189,218,181,226,187">
    		<area shape="poly" name="MG" coords="171,164,190,162,192,145,205,140,217,146,224,154,217,169,212,183,193,183,185,170">
    		<area shape="poly" name="ES" coords="236,167,228,162,221,177,226,183">
    		<area shape="poly" name="BA" coords="198,113,196,134,213,133,230,139,235,146,231,157,235,160,240,142,241,127,249,124,243,113,243,105,234,106,225,107,215,107,207,115">
    		<area shape="poly" name="CE" coords="230,59,235,86,241,86,252,70,239,61">
    		<area shape="poly" name="SE" coords="250,108,248,113,251,118,257,113,252,109">

    		<area shape="poly" name="AL" coords="266,102,258,104,251,102,260,110,266,104">
    		<area shape="poly" name="PE" coords="269,94,269,99,262,99,256,101,251,98,246,98,239,96,234,100,231,95,234,92,243,93,251,94,255,96">
    		<area shape="poly" name="PB" coords="269,85,262,85,257,88,253,85,248,87,257,90,263,91,268,89">
    		<area shape="poly" name="RN" coords="256,73,249,81,256,80,257,83,270,82,265,76">
    		<area shape="poly" name="DF" coords="168,162,171,153,183,149,182,161">
    	</map>
    </div>
</body>
<!-- InstanceEnd --></html>
