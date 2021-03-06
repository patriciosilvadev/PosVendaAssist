<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'autentica_usuario.php';

$fabrica = trim($_GET["fabrica"]);
$tipo = trim($_GET["tipo"]);
$referencia = trim ($_GET['referencia']);
$descricao  = trim ($_GET['descricao']);
$busca      = trim ($_GET['busca']);
$media      = trim ($_GET['media']);
$previsao   = trim ($_GET['previsao']);


$msg_erro = array();
$data_final=date('Y-m-d');

if (strlen($fabrica) > 0) {
	$fabrica = intval($fabrica);
	$sql = "SELECT nome FROM tbl_fabrica WHERE fabrica=$fabrica";
	$res = pg_query($con, $sql);

	if (pg_num_rows($res)) {
		$fabrica_nome = pg_fetch_result($res, 0, nome);

		if (strlen($referencia) > 0) {
			$sql = "SELECT peca, descricao FROM tbl_peca WHERE referencia='$referencia' AND fabrica=$fabrica";
			$res = pg_query($con, $sql);

			if (pg_num_rows($res)) {
				$peca = pg_fetch_result($res, 0, peca);
				$descricao = pg_fetch_result($res, 0, descricao);
			}
			else {
				$msg_erro[] = "Refer�ncia da pe�a informada inv�lida";
			}
		}
	}
}
elseif (isset($_GET["fabrica"])) {
	$msg_erro[] = "Informe a f�brica para gerar o relat�rio";
}

if (strlen($tipo) > 0) {
	if ($tipo != "peca" && $tipo != "produto") {
		$tipo = "";
	}
}

if(isset($_GET['btn_acao'])) {
	if(empty($media)) {
		$msg_erro[]="Por favor, informe o n�mero de meses para a m�dia";
	}

	if(empty($previsao)) {
		$msg_erro[]="Por favor, informe o n�mero de meses para a previs�o";
	}

	$sql = " SELECT min(emissao) FROM tbl_faturamento WHERE fabrica = $fabrica";
	$res = pg_query($con,$sql);
	$data_inicial = pg_fetch_result($res,0,0);


}


$msg_erro = implode("<br>", $msg_erro);


if ($_GET["formato"] == "xls" && strlen($msg_erro) == 0 && strlen($data_inicial) > 0 && strlen($data_final) > 0 && strlen($fabrica) > 0) {
	ob_start();
}
else {
	?>

	<html>
	<head>
	<title>Previs�o de Estoque</title>
	<link type="text/css" rel="stylesheet" href="css/css.css">
	<style>
	.numero {
		text-align: right;
		padding-right: 5px;
	}
	</style>
	</head>

	<body>

	<? include 'menu.php';
//		echo "Programa em manuten��o";exit;
	?>
	
	<? include "javascript_calendario_new.php"; ?>
	<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
	<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
	<script type='text/javascript' src='js/jquery.bgiframe.min.js'></script>
	<script type='text/javascript' src='js/dimensions.js'></script>
	<script type="text/javascript" src="js/thickbox.js"></script>
	<link rel="stylesheet" href="js/thickbox.css" type="text/css" media="screen" />

	<script language="JavaScript">
	$(function() {
		$('.mostra_esconde').hide();
		$("input[rel=numero]").keypress(function(e) {   
			var c = String.fromCharCode(e.which);   
			var allowed = '1234567890';
			if ((e.keyCode != 9 && e.keyCode != 8) && allowed.indexOf(c) < 0) return false;
		});

		function formatItem(row) {
			return row[0] + " - " + row[1] + " - " + row[2];
		}

		function formatResult(row) {
			return row[0];
		}

		$("#descricao").autocomplete("<?echo 'peca_consulta_ajax.php?busca=nome'; ?>", {
			minChars: 3,
			delay: 150,
			width: 350,
			matchContains: true,
			formatItem: formatItem,
			formatResult: function(row) {return row[1];}
		});

		$("#descricao").result(function(event, data, formatted) {
			$("#referencia").val(data[1]) ;
			$("#descricao").val(data[2]) ;
		});

	});

	function mostraEsconde(){
		$('.mostra_esconde').toggle();

		if ($('#mostra').text() == 'Esconder Campos(Meses)'){
			$('#mostra').text('Mostrar Campos(Meses)');
		}else{
			$('#mostra').text('Esconder Campos(Meses)');
		}
	}

	</script>

	<center><h1>Previs�o de Estoque</h1></center>

	<p>
		<?

		if (strlen($msg_erro) > 0) {
			echo "<div style='border: 1px solid #DD0000; background-color: #FFDDDD; color: #DD0000; font-size: 11pt; margin-bottom: 10px; padding: 5px;'>$msg_erro</div>";
		}

		?>
	<center>
	<form name='frm_estoque' action='<? echo $PHP_SELF ?>' method='get'>
	<table>

		<tr>
			<td align='right'>M�dia(meses)</td>
			<td><input type='text' size='3' name='media' rel='numero' value='<?=$media?>' class='frm'></td>
			<td align='right'>Previs�o(meses)</td>
			<td><input type='text' size='11' name='previsao' rel='numero'  id='previsao' class="frm"  value="<? echo $_GET["previsao"]; ?>"></td>
		</tr>
		<tr>
			<td align='right'>F�brica</td>
			<td align='left'>
			<?
			echo "<select style='width:150px;' name='fabrica' id='fabrica' class='frm'>";
				$sql = "SELECT fabrica,nome FROM tbl_fabrica WHERE fabrica IN (51,81) ORDER BY nome";
				$res = pg_exec($con,$sql);
				if(pg_numrows($res)>0){
					for($x = 0; $x < pg_numrows($res);$x++) {
						$aux_fabrica = pg_fetch_result($res,$x,fabrica);
						$aux_nome    = pg_fetch_result($res,$x,nome);
						echo "<option value='$aux_fabrica'" ;if($fabrica==$aux_fabrica) echo "selected"; echo ">$aux_nome</option>";
					}
				}
			echo "</select>";
			?>
			</td>
			<td align='right'>Tipo</td>
			<td>
			<select name="tipo" id="tipo" class='frm'>
				<option value="">Tudo</option>
				<option <? if ($tipo == "peca") echo "selected"; ?> value="peca">Pe�a</option>
				<option <? if ($tipo == "produto") echo "selected"; ?> value="produto">Produto</option>
			</select>
			</td>
		</tr>
		<tr>
			<td align='right'>Refer�ncia da Pe�a</td>
			<td><input type='text' size='10' name='referencia' id='referencia' class="frm" value="<? echo $referencia; ?>"></td>
			<td align='right'>Descri��o da Pe�a</td>
			<td><input type='text' size='20' name='descricao'   id='descricao' class="frm" value="<? echo $descricao; ?>"></td>
		</tr>
		<tr>
			<td align='center' colspan='4'><input type='checkbox' name='formato' id='formato' value='xls' <? if ($_GET["formato"]) echo "checked"; ?>> Gerar relat�rio para Excel (XLS)</td>
		</tr>
		<tr>
			<td align='center' colspan='4'><input type='submit' name='btn_acao' id='btn_acao' value='Pesquisar'></td>
		</tr>
	</table>
	<br>
	</form>

	<?
}	//ELSE de if ($_GET["formato"] == "xls")

if (strlen($msg_erro) == 0 && strlen($data_inicial) > 0 && strlen($data_final) > 0 && strlen($fabrica) > 0) {
	if (strlen($peca) > 0) {
		$sql_peca = "AND tbl_peca.peca=$peca";
	}

	switch($tipo) {
		case "peca":
			$sql_produto_acabado = "AND tbl_peca.produto_acabado IS NOT TRUE";
		break;

		case "produto":
			$sql_produto_acabado = "AND tbl_peca.produto_acabado IS TRUE";
		break;
		
		default:
			$sql_produto_acabado = "";
	}

	$sql = "
	SELECT
	tbl_peca.peca,
	tbl_peca.referencia,
	tbl_peca.descricao,
	(SELECT preco FROM tbl_tabela_item JOIN tbl_tabela USING(tabela) WHERE fabrica=$fabrica AND peca=tbl_peca.peca) AS preco,
	tbl_posto_estoque_localizacao.localizacao
	
	FROM
	tbl_faturamento
	JOIN tbl_faturamento_item ON tbl_faturamento.faturamento=tbl_faturamento_item.faturamento
	JOIN tbl_peca ON tbl_faturamento_item.peca=tbl_peca.peca
	JOIN tbl_posto_estoque_localizacao ON tbl_peca.peca=tbl_posto_estoque_localizacao.peca
										  AND tbl_posto_estoque_localizacao.posto=$login_posto
	/*Os depara desta data em espec�ficos foram feitos para a BestWay para a migra��o das refer�ncias da JM para refer�ncias da Telecontrol*/
	LEFT JOIN tbl_depara ON tbl_peca.peca=tbl_depara.peca_de
			  AND tbl_depara.digitacao='2010-02-13 11:33:20.127964'::timestamp
			  AND tbl_depara.fabrica=81
	
	WHERE
	tbl_peca.fabrica=$fabrica
	AND tbl_faturamento.fabrica IN (10, $fabrica)
	AND tbl_faturamento.emissao BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59'
	AND tbl_depara.peca_de IS NULL
	$sql_peca
	$sql_produto_acabado

	GROUP BY
	tbl_peca.peca,
	tbl_peca.referencia,
	tbl_peca.descricao,
	tbl_posto_estoque_localizacao.localizacao

	ORDER BY
	localizacao ASC 
	";
	$res_pecas = pg_query($con, $sql);

	$parts = explode("-", $data_final);
	$ano_final = intval($parts[0]);
	$mes_final = intval($parts[1]);
	
	echo "<p><a href='javascript:mostraEsconde()' id='mostra'>Mostrar Campos(Meses)</a></p>";

	echo "<br><table align='center' border='0' cellspacing='1' cellpaddin='1'>";

	echo "<tr bgcolor='#0099CC' style='color:#ffffff ; font-weight:bold ; font-size:13px' align='center'>";
	echo "<td nowrap>Localiza��o</td>";
	echo "<td nowrap>Refer�ncia</td>";
	echo "<td nowrap>Pe�a</td>";

	if ($fabrica == 81) {
		echo "<td style='width:70px;'>Entrada Total BestWay</td>";
		echo "<td style='width:70px;'>Entrada Total JM</td>";
	}
	else {
		echo "<td style='width:70px;'>Entrada Total</td>";
	}
	echo "<td style='width:70px;'>Sa�da Total</td>";

	for($m=$media-1;$m>=0;$m--) {
		# HD 285329
		$mes = $mes_final - $m;
		
		$ano = $ano_final;

		if ($mes <= 0) {
			$mes = 12 + $mes;
			$ano = $ano_final - 1;
		}
		
		if($m == $media -1 ) {
			$data_inicial_meses = $ano."-" . substr("0" . $mes, -2) . "-01 00:00:00";
		}
		

		if($m == 0) {
			$data_final_meses = $ano."-" . substr("0" . ($mes+1), -2) . "-01 00:00:00";
		}

		$mes = substr("0".$mes, -2) . "/" . $ano;

		echo "<td style='width:70px;' class='mostra_esconde'>Sa�da $mes</td>";

	}

	echo "<td style='width:70px;'>M�dia $media Meses</td>";
	echo "<td style='width:70px;'>Previs�o $previsao Meses</td>";
	echo "<td style='width:70px;'>Estoque Atual</td>";
	echo "<td style='width:70px;'>Valor Total</td>";
	echo "<td style='width:70px;'>Pedido</td>";
	if ($fabrica == 81) {
		echo "<td style='width:70px;'>Devolucao BestWay</td>";
		echo "<td style='width:70px;'>Valor Devolucao BestWay</td>";
		echo "<td style='width:70px;'>Devolucao JM</td>";
		echo "<td style='width:70px;'>Valor Devolucao JM</td>";
	}
	else {
		echo "<td style='width:70px;'>Devolucao</td>";
	}
		
	echo "</tr>";

	$total_valor = 0;
	$total_estoque = 0;
	$total_pedido = 0;
	$total_devolucao = 0;
	$total_devolucao_valor = 0;

	for ($i = 0 ; $i < pg_numrows($res_pecas) ; $i++) {
		if($i % 12 == 0 and $i > 0) {
			echo "<tr bgcolor='#0099CC' style='color:#ffffff ; font-weight:bold ; font-size:13px' align='center'>";
			echo "<td nowrap>Localiza��o</td>";
			echo "<td nowrap>Refer�ncia</td>";
			echo "<td nowrap>Pe�a</td>";

			if ($fabrica == 81) {
				echo "<td style='width:70px;'>Entrada Total BestWay</td>";
				echo "<td style='width:70px;'>Entrada Total JM</td>";
			}
			else {
				echo "<td style='width:70px;'>Entrada Total</td>";
			}
			echo "<td style='width:70px;'>Sa�da Total</td>";

			for($m=$media-1;$m>=0;$m--) {
				# HD 285329
				$mes = $mes_final - $m;
				
				$ano = $ano_final;

				if ($mes <= 0) {
					$mes = 12 + $mes;
					$ano = $ano_final - 1;
				}
				
				if($m == $media -1 ) {
					$data_inicial_meses = $ano."-" . substr("0" . $mes, -2) . "-01 00:00:00";
				}
				

				if($m == 0) {
					$data_final_meses = $ano."-" . substr("0" . ($mes+1), -2) . "-01 00:00:00";
				}

				$mes = substr("0".$mes, -2) . "/" . $ano;

				echo "<td style='width:70px;' class='mostra_esconde'>Sa�da $mes</td>";

			}

			echo "<td style='width:70px;'>M�dia $media Meses</td>";
			echo "<td style='width:70px;'>Previs�o $previsao Meses</td>";
			echo "<td style='width:70px;'>Estoque Atual</td>";
			echo "<td style='width:70px;'>Valor Total</td>";
			echo "<td style='width:70px;'>Pedido</td>";
			if ($fabrica == 81) {
				echo "<td style='width:70px;'>Devolucao BestWay</td>";
				echo "<td style='width:70px;'>Valor Devolucao BestWay</td>";
				echo "<td style='width:70px;'>Devolucao JM</td>";
				echo "<td style='width:70px;'>Valor Devolucao JM</td>";
			}
			else {
				echo "<td style='width:70px;'>Devolucao</td>";
			}
				
			echo "</tr>";
		}

		$cor = "cccccc";
		if ($i % 2 == 0) $cor = '#eeeeee';

		$peca_atual = pg_fetch_result($res_pecas, $i, peca);
		$preco = pg_fetch_result($res_pecas, $i, preco);
		
		//Os depara desta data em espec�ficos foram feitos para a BestWay para a migra��o das refer�ncias da JM para refer�ncias da Telecontrol
		if ($fabrica == 81) {
			$select_depara_acerto_salton = "UNION SELECT peca_de FROM tbl_depara WHERE peca_para=$peca_atual AND tbl_depara.digitacao='2010-02-13 11:33:20.127964'::timestamp AND tbl_depara.fabrica=81";
		}

		echo "<tr bgcolor='$cor' style='font-size:11px'>";

		echo "<td align='left'>";
		echo pg_fetch_result($res_pecas,$i,localizacao);
		echo "</td>";

		echo "<td align='left'>";
		echo pg_fetch_result($res_pecas,$i,referencia);
		echo "</td>";

		echo "<td align='left'>";
		echo pg_fetch_result($res_pecas,$i,descricao);
		echo "</td>";
		
		switch($fabrica) {
			case 81:
				$sql = "
				SELECT
				SUM (tbl_faturamento_item.qtde_estoque) AS entrada_total_salton

				FROM
				tbl_faturamento
				JOIN tbl_faturamento_item USING (faturamento)
				JOIN tbl_peca ON tbl_peca.peca = tbl_faturamento_item.peca

				WHERE 
				tbl_faturamento.posto=$login_posto
				AND tbl_peca.peca IN (SELECT peca_de FROM tbl_depara WHERE peca_para=$peca_atual AND tbl_depara.digitacao='2010-02-13 11:33:20.127964'::timestamp AND tbl_depara.fabrica=81 UNION SELECT $peca_atual)
				AND tbl_faturamento.cancelada IS NULL
				AND tbl_faturamento.emissao BETWEEN '$data_inicial 00:00:00'::timestamp AND '$data_final 23:59:59'::timestamp
				AND (distribuidor<>59773 /* JM */ OR (distribuidor IS NULL AND tbl_faturamento.fabrica=81))
				";
				$res = pg_query($con, $sql);
				$entrada_total_salton = pg_fetch_result($res, 0, entrada_total_salton);

				$sql = "
				SELECT
				SUM (tbl_faturamento_item.qtde_estoque) AS entrada_total_jm

				FROM
				tbl_faturamento
				JOIN tbl_faturamento_item USING (faturamento)
				JOIN tbl_peca ON tbl_peca.peca = tbl_faturamento_item.peca

				WHERE 
				tbl_faturamento.posto=$login_posto
				AND tbl_peca.peca IN (SELECT peca_de FROM tbl_depara WHERE peca_para=$peca_atual AND tbl_depara.digitacao='2010-02-13 11:33:20.127964'::timestamp AND tbl_depara.fabrica=81)
				AND tbl_faturamento.cancelada IS NULL
				AND tbl_faturamento.emissao BETWEEN '$data_inicial 00:00:00'::timestamp AND '$data_final 23:59:59'::timestamp
				";
				$sql = "
				SELECT
				SUM (tbl_faturamento_item.qtde_estoque) AS entrada_total_jm

				FROM
				tbl_faturamento
				JOIN tbl_faturamento_item USING (faturamento)
				JOIN tbl_peca ON tbl_peca.peca = tbl_faturamento_item.peca

				WHERE 
				tbl_faturamento.posto=$login_posto
				AND tbl_peca.peca IN (SELECT peca_de FROM tbl_depara WHERE peca_para=$peca_atual AND tbl_depara.digitacao='2010-02-13 11:33:20.127964'::timestamp AND tbl_depara.fabrica=81 UNION SELECT $peca_atual)
				AND tbl_faturamento.cancelada IS NULL
				AND tbl_faturamento.emissao BETWEEN '$data_inicial 00:00:00'::timestamp AND '$data_final 23:59:59'::timestamp
				AND (distribuidor=59773 /* JM */ OR (distribuidor IS NULL AND tbl_faturamento.fabrica=10))
				";
				$res = pg_query($con, $sql);
				$entrada_total_jm = pg_fetch_result($res, 0, entrada_total_jm);
				
				echo "<td class='numero'>";
				if ($entrada_total_salton) {
					echo $entrada_total_salton;
				}
				else {
					echo "0";
				}
				echo "</td>";

				echo "<td class='numero'>";
				if ($entrada_total_jm) {
					echo $entrada_total_jm;
				}
				else {
					echo "0";
				}
				echo "</td>";
			break;

			default:
				$sql = "
				SELECT
				SUM (tbl_faturamento_item.qtde_estoque) AS entrada_total

				FROM
				tbl_faturamento
				JOIN tbl_faturamento_item USING (faturamento)
				JOIN tbl_peca ON tbl_peca.peca = tbl_faturamento_item.peca

				WHERE 
				tbl_faturamento.posto=$login_posto
				AND tbl_peca.peca IN (SELECT $peca_atual $select_depara_acerto_salton)
				AND tbl_faturamento.cancelada IS NULL
				AND tbl_faturamento.emissao BETWEEN '$data_inicial 00:00:00'::timestamp AND '$data_final 23:59:59'::timestamp
				";
				$res = pg_query($con, $sql);

				echo "<td class='numero'>";
				if (pg_fetch_result($res, 0, entrada_total)) {
					echo pg_fetch_result($res, 0, entrada_total);
				}
				else {
					echo "0";
				}
				echo "</td>";
		}
	
		$sql = "
		SELECT
		SUM(tbl_faturamento_item.qtde) AS saida_total

		FROM
		tbl_faturamento
		JOIN tbl_faturamento_item USING (faturamento)
		JOIN tbl_peca ON tbl_peca.peca=tbl_faturamento_item.peca
		JOIN tbl_tipo_pedido ON tbl_faturamento.tipo_pedido=tbl_tipo_pedido.tipo_pedido

		WHERE
		tbl_faturamento.distribuidor = $login_posto
		AND tbl_peca.peca IN (SELECT $peca_atual $select_depara_acerto_salton)
		AND tbl_faturamento.emissao BETWEEN '$data_inicial 00:00:00'::timestamp AND '$data_final 23:59:59'::timestamp
		";
		$res = pg_query($con, $sql);

		echo "<td class='numero'>";

		if (pg_fetch_result($res, 0, saida_total)) {
			echo pg_fetch_result($res, 0, saida_total);
		}
		else {
			echo "0";
		}
		echo "</td>";
	
		
		$total = 0;
		for($m = 0; $m < $media; $m++) {
			# HD 285329
			$sqlm = "  select to_char('$data_inicial_meses'::date + interval '$m months','YYYY-MM'),to_char('$data_inicial_meses'::date + interval '$m months','YYYY-MM-DD 00:00:00');";

			$resm = pg_query($con,$sqlm);
			$ano_mes_v = pg_fetch_result($resm,0,0);
			$ano_v = substr($ano_mes_v,0,4);
			$mes_v = substr($ano_mes_v,5,2);
			$data_inicial_v = date("Y-m-01 00:00:00", mktime(0, 0, 0, $mes_v, 2, $ano_v));
			$data_final_v  = date("Y-m-t 23:59:59", mktime(0, 0, 0, $mes_v, 2, $ano_v));
			

			$sql = "
				SELECT
				SUM(tbl_faturamento_item.qtde) AS saida_total_mensal,
				TO_CHAR(emissao, 'YYYY-MM') AS mes

				FROM
				tbl_faturamento
				JOIN tbl_faturamento_item USING (faturamento)
				JOIN tbl_peca ON tbl_peca.peca=tbl_faturamento_item.peca
				JOIN tbl_tipo_pedido ON tbl_faturamento.tipo_pedido=tbl_tipo_pedido.tipo_pedido

				WHERE
				tbl_faturamento.distribuidor = $login_posto
				AND tbl_peca.peca IN (SELECT $peca_atual $select_depara_acerto_salton)
				AND tbl_faturamento.emissao
					BETWEEN
					'$data_inicial_v'::timestamp AND
					'$data_final_v'::timestamp - INTERVAL '1 SECOND'

				GROUP BY
				TO_CHAR(emissao, 'YYYY-MM')

				ORDER BY
				TO_CHAR(emissao, 'YYYY-MM')
				";

			$res = pg_query($con, $sql);

			echo "<td class='numero mostra_esconde'>";
			if(pg_num_rows($res) > 0){
				if(pg_fetch_result($res, 0, mes) == $ano_mes_v) {
					echo pg_fetch_result($res, 0, saida_total_mensal);
					$total += pg_fetch_result($res, 0, saida_total_mensal);
				}else{
					echo 0;
				}
			}else{
				echo 0;
			}

			echo "</td>";
		}
		echo "<td class='numero'>";
		echo number_format($total/$media, 2, ",", ".");
		echo "</td>";

		echo "<td class='numero'>";
		echo number_format(ceil($previsao*$total/$media), 0, ",", ".");
		echo "</td>";

		$sql = "SELECT qtde FROM tbl_posto_estoque WHERE posto=$login_posto AND peca=$peca_atual";
		$res = pg_query($con, $sql);

		echo "<td class='numero'>";
		if (pg_num_rows($res)) {
			echo number_format(pg_fetch_result($res, 0, qtde), 0, ",", ".");
			$estoque = pg_fetch_result($res, 0, qtde);
		}
		else {
			echo "0";
			$estoque = 0;
		}
		echo "</td>";
		
		$total_valor += floatval(number_format(($preco*$estoque), 2, ".", ""));
		$total_estoque += $estoque;

		echo "<td class='numero' title='Valor Unit�rio: " . number_format($preco, 2, ",", ".") . "'>";
		echo number_format(($preco*$estoque), 2, ",", ".");
		echo "</td>";
	
		$previsao2 = ceil($previsao*$total/$media) - $estoque;

		if ($previsao2 > 0) {
			echo "<td class='numero'>";
			echo number_format($previsao2, 0, ",", ".");
			echo "</td>";
			$total_pedido += $previsao2;
		}
		else {
			echo "<td class='numero'>";
			echo "0";
			echo "</td>";
		}

		if ($previsao2 < 0) {
			$previsao2 = $previsao2*(-1);
			echo "<td class='numero'>";

			if ($fabrica == 81) {
				$sql = "
				SELECT
				SUM (tbl_faturamento_item.qtde_estoque) AS entrada_total_geral_jm

				FROM
				tbl_faturamento
				JOIN tbl_faturamento_item USING (faturamento)
				JOIN tbl_peca ON tbl_peca.peca = tbl_faturamento_item.peca

				WHERE 
				tbl_faturamento.posto=$login_posto
				AND tbl_peca.peca IN (SELECT peca_de FROM tbl_depara WHERE peca_para=$peca_atual AND tbl_depara.digitacao='2010-02-13 11:33:20.127964'::timestamp AND tbl_depara.fabrica=81)
				AND tbl_faturamento.cancelada IS NULL
				";
				$res = pg_query($con, $sql);
				$entrada_total_geral_jm = pg_fetch_result($res, 0, entrada_total_geral_jm);
				$entrada_total_geral_salton = $estoque - $entrada_total_geral_jm;
				if ($entrada_total_geral_salton < 0) {
					$entrada_total_geral_salton = 0;
				}

				if ($previsao2 <= $entrada_total_geral_salton) {
					$previsao_salton = $previsao2;
					$previsao_jm = 0;
				}
				else {
					$previsao_salton = $entrada_total_geral_salton;
					$previsao_jm = $previsao2 - $previsao_salton;
				}

				echo number_format($previsao_salton, 0, ",", ".");
				echo "</td>";
				echo "<td class='numero'>";
				echo number_format(($preco*$previsao_salton), 2, ",", ".");
				echo "</td>";
				echo "<td class='numero'>";
				echo number_format($previsao_jm, 0, ",", ".");
				echo "</td>";
				echo "<td class='numero'>";
				echo number_format(($preco*$previsao_jm), 2, ",", ".");

				$total_devolucao_salton += $previsao_salton;
				$total_devolucao_jm += $previsao_jm;
				
				$total_devolucao_valor_salton += floatval(number_format(($preco*$previsao_salton), 2, ".", ""));
				$total_devolucao_valor_jm += floatval(number_format(($preco*$previsao_jm), 2, ".", ""));
			}
			else {
				echo number_format($previsao2, 0, ",", ".");
				$total_devolucao += $previsao2;
				$total_devolucao_valor += floatval(number_format(($preco*$previsao2), 2, ".", ""));
			}
			echo "</td>";
		}
		else {
			if ($fabrica == 81) {
				echo "<td class='numero'>";
				echo "0";
				echo "</td>";
				echo "<td class='numero'>";
				echo "0";
				echo "</td>";
				echo "<td class='numero'>";
				echo "0";
				echo "</td>";
			}

			echo "<td class='numero'>";
			echo "0";
			echo "</td>";
		}

		echo "</tr>";
	}

	echo "<tr bgcolor='#0099CC' style='color:#ffffff ; font-weight:bold ; font-size:13px' align='center'>";
	echo "<td nowrap>TOTAIS</td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td colspan='$media' class='mostra_esconde'></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td class='numero'>$total_estoque</td>";
	echo "<td class='numero'>" . number_format($total_valor, 2, ',', '.') . "</td>";
	echo "<td class='numero'>$total_pedido</td>";
	if ($fabrica == 81) {
		echo "<td></td>";
		echo "<td class='numero' title='Total de pe�as: $total_devolucao_salton'>" . number_format($total_devolucao_valor_salton, 2, ',', '.') . "</td>";
		echo "<td></td>";
		echo "<td class='numero' title='Total de pe�as: $total_devolucao_jm'>" . number_format($total_devolucao_valor_jm, 2, ',', '.') . "</td>";
	}
	else {
		echo "<td class='numero' title='Total de pe�as: $total_devolucao'>" . number_format($total_devolucao_valor, 2, ',', '.') . "</td>";
	}
	echo "</tr>";

	echo "</table>";
}

if (isset($_POST['btn_acao']) AND (strlen ($descricao) < 3 AND strlen ($referencia) < 3 AND strlen ($localizacao) < 3)) {
	echo "<br><br><center><b class='vermelho'>DIGITE NO M�NIMO 3 CARACTERES PARA A BUSCA!</center></b>";
}

if ($_GET["formato"] == "xls" && strlen($msg_erro) == 0) {
	//Redireciona a saida da tela, que estava em buffer, para a variável
	$hora = time();
	$xls = "xls/estoque_previsao_".$login_posto."_data_".$hora.".xls";

	$saida = ob_get_clean();

	$arquivo = fopen($xls, "w");
	fwrite($arquivo, $saida);
	fclose($arquivo);

	header("location:$xls");
}

include "rodape.php";

?>
