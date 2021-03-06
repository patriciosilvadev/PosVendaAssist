<?php
include "dbconfig.php";
include "includes/dbconnect-inc.php";
$admin_privilegios="gerencia";
include 'autentica_admin.php';
include 'funcoes.php';

if ($_POST["btn_acao"] == "liberar_os") {

	$os_item = $_POST["os_item"];

	$sql = "
		SELECT servico_realizado, descricao
		FROM tbl_servico_realizado
		WHERE fabrica = $login_fabrica
		AND descricao ilike '%Direta%'";
	$res = pg_query($con, $sql);
	
	if (pg_num_rows($res) > 0) {
		$servico_realizado = pg_fetch_result($res, 0, 'servico_realizado');
		
		$sql_update = "UPDATE tbl_os_item SET servico_realizado = $servico_realizado WHERE os_item = $os_item AND fabrica_i = $login_fabrica";
		$res_update = pg_query($con, $sql_update);

		if (!pg_last_error()) {
			echo json_encode(array("retorno" => utf8_encode("success"),"os" => utf8_encode($os)));
		} else {
			echo json_encode(array("retorno" => utf8_encode("Erro ao liberar OS.")));
		}
	}else{
		echo json_encode(array("retorno" => utf8_encode("Servi�o n�o encotrado.")));
	}
	exit;
}

$busca_os = $_POST['busca_os'];
if(strlen($busca_os)>0){
	$peca	= $_GET['peca'];
	$tabela	= $_GET['tabela'];

	if($login_fabrica == 163){ //HD-3510131
		$join_163 = " JOIN tbl_tipo_atendimento ON tbl_tipo_atendimento.tipo_atendimento = tbl_os.tipo_atendimento AND tbl_tipo_atendimento.fabrica = $login_fabrica ";
		$cond_163 = " AND tbl_tipo_atendimento.fora_garantia IS NOT TRUE ";
	}

	$sql = "SELECT tbl_os.os, tbl_os.sua_os,
			tbl_os.pecas ,
			to_char (tbl_os.data_digitacao,'DD/MM/YYYY') AS data_digitacao ,
			to_char (tbl_os.data_abertura,'DD/MM/YYYY') AS data_abertura ,
			tbl_peca.referencia AS peca_referencia ,
			tbl_peca.descricao AS peca_descricao ,
			tbl_posto_fabrica.codigo_posto ,
			tbl_posto.nome AS nome_posto
			FROM tbl_os
			JOIN tbl_posto USING(posto)
			JOIN tbl_posto_fabrica ON tbl_posto.posto = tbl_posto_fabrica.posto AND
			tbl_posto_fabrica.fabrica = $login_fabrica
			JOIN tbl_produto ON tbl_os.produto = tbl_produto.produto AND tbl_produto.fabrica_i = tbl_os.fabrica
			JOIN tbl_posto_linha ON tbl_posto_linha.posto = tbl_posto.posto
			AND tbl_posto_linha.linha = tbl_produto.linha AND tbl_posto_linha.tabela = $tabela
			JOIN tbl_os_produto USING(os)
			$join_163
			JOIN tbl_os_item ON tbl_os_produto.os_produto = tbl_os_item.os_produto AND
			tbl_os_item.fabrica_i = $login_fabrica
			JOIN tbl_peca ON tbl_os_item.peca = tbl_peca.peca
			WHERE tbl_os.fabrica = $login_fabrica
			AND tbl_os.excluida IS NOT TRUE
			$cond_163
			AND tbl_peca.peca = $peca
			order by tbl_os.os desc";
			$res    = pg_query($con,$sql);
			if(pg_numrows($res) > 0){
			?>
				<table class="tablesorter" width="700" >
						<tr>
							<th><?php echo traduz("OS"); ?></th>
							<th><?php echo traduz("Data de Abertura"); ?></th>
							<th><?php echo traduz("Data de Digita��o"); ?></th>
							<th><?php echo traduz("Posto"); ?></th>
						</tr>
						<?php
						for ($i = 0 ; $i < pg_numrows($res) ; $i++) {
							$sua_os				= "";
							$pecas				= "";
							$data_digitacao		= "";
							$data_abertura		= "";
							$peca_referencia	= "";
							$peca_descricao		= "";
							$codigo_posto		= "";
							$nome_posto			= "";

							$sua_os				= trim(pg_fetch_result($res,$i,sua_os));
							$pecas				= trim(pg_fetch_result($res,$i,pecas));
							$data_digitacao		= trim(pg_fetch_result($res,$i,data_digitacao));
							$data_abertura		= trim(pg_fetch_result($res,$i,data_abertura));
							$peca_referencia	= trim(pg_fetch_result($res,$i,peca_referencia));
							$peca_descricao		= trim(pg_fetch_result($res,$i,peca_descricao));
							$codigo_posto		= trim(pg_fetch_result($res,$i,codigo_posto));
							$nome_posto			= trim(pg_fetch_result($res,$i,nome_posto));

							$cor	 = ($i % 2 == 0) ? "#F1F4FA" : "#F7F5F0";
							?>
								<tr>
									<td width="100" style="background:<?php echo $cor;?>;"><a href="os_press.php?os=<?php echo $sua_os;?>" target='_blank'><?php echo $sua_os;?></a></td>
									<td width="150" style="background:<?php echo $cor;?>;"><?php echo $data_digitacao;?></td>
									<td width="150" style="background:<?php echo $cor;?>;"><?php echo $data_abertura;?></td>
									<td width="300" style="background:<?php echo $cor;?>;"><?php echo $codigo_posto." - ".$nome_posto;?></td>
								</tr>

							<?php
						}
			?>
			</table>
			<?php
			}else{
				?>
				<tr>
					<td width="700" style="background:<?php echo $cor;?>;"><?php echo traduz("Nenhum resultado encontrado");?></td>
				</tr>
				<?php
			}
	exit;
}

if($_POST){
	$tabela = $_POST['tabela_preco'];
	if (strlen($tabela) == 0 && strlen($_GET["tabela"] > 0)) {
		$tabela = $_GET["tabela"];
	}
}
$title     = traduz("RELAT�RIO DE PE�AS EM OS E SEM PRE�O");

$layout_menu = "gerencia";
include 'cabecalho_new.php';
?>

<form  class="form-search form-inline tc_formulario" method='post' action=''>
	<div class="titulo_tabela"><?php echo traduz("Par�metros de Pesquisa");?></div>
	<br />
	<div class="container tc_container">
		<div class="row-fluid">
			<div class="span4"></div>
			<div class="span4">
				<div class='control-group <?=(in_array("data", $msg_erro["campos"])) ? "error" : ""?>'>
					<div class="controls controls-row">
						<div class="span7 input-append">
							<label class="control-label" for="tabela"><?php echo traduz("Tabela de Pre�o");?> &nbsp;&nbsp;&nbsp;&nbsp; </label>
							<select name='tabela_preco' class='frm'>
<?php
									if($login_fabrica == 35){
										$condT = " AND tabela_garantia IS TRUE ";
									}

									$sql = "SELECT tabela,sigla_tabela,descricao
									          FROM tbl_tabela
										  WHERE fabrica = $login_fabrica
										  $condT
									           AND ativa IS TRUE;";
									$res   = pg_exec($con,$sql);
									$total = pg_numrows($res);

									for($i = 0; $i < $total; $i++)
									{
										$tabela_codigo = pg_result($res,$i,tabela);
										$sigla_tabela  = pg_result($res,$i,sigla_tabela);
										$tabela_nome   = pg_result($res,$i,descricao);
									?>
										<option value="<?php echo $tabela_codigo; ?>" <?php if($tabela_codigo == $tabela) echo 'selected'; ?>><?php echo $sigla_tabela ." - ". $tabela_nome; ?></option>
								<?php }?>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<center>
		<input type='submit' value='<?=traduz('Pesquisar')?>' class='btn'>
	</center>
	<br />
</form>
<script language="JavaScript">

	$(document).on('click','button.btn_liberar_peca', function(){
		if (confirm('Deseja liberar os para fechamento ?')) {
			var btn = $(this);
	        var text = $(this).text();
	        var os_item = $(btn).data('os_item');
	        
	        $(btn).prop({disabled: true}).text("Aguarde...");
	        $.ajax({
	            method: "POST",
	            url: "<?=$_SERVER['PHP_SELF']?>",
	            data: { btn_acao: 'liberar_os', os_item: os_item},
	            timeout: 8000
	        }).fail(function(){
	        	alert("N�o foi poss�vel liberar OS, tempo limite esgotado!");
	        }).done(function(data) {
	            data = JSON.parse(data);
	            if (data.retorno == "success") {
	                $(btn).text("Pe�a liberada");
	                setTimeout(function(){
	                	$("#"+os_item).remove();
	                }, 1000);
	            }else{
	                $(btn).prop({disabled: false}).text(text);
				}
	        });
	    }else{
	    	return false;
	    }
    });
	// $().ready(function() {
	// 	$('.busca_dados').click(function(){
	// 		var peca			= $(this).attr('rel');
	// 		var tabela			= $(this).attr('alt');
	// 		var div_resultado	= "div_conteudo"+peca;
	// 	    var tab_resultaddo  = "table_conteudo"+peca;
	// 	    var	abre_div        = "abre_div"+peca;

	// 		var verif			= $("#"+abre_div).attr('alt');
	// 		$("#"+tab_resultaddo).hide('fast');

	// 		if(verif == '0') {
	// 			$("#"+abre_div).html('-');
	// 			$("#"+abre_div).attr('alt', '1');
	// 			$("#"+tab_resultaddo).show('fast');
	// 		}else{
	// 			$("#"+abre_div).html('+');
	// 			$("#"+abre_div).attr('alt', '0');
	// 			$("#"+tab_resultaddo).hide('fast');
	// 			return false;
	// 		}

	// 		if(peca){
	// 			$("#"+tab_resultaddo).show('fast');
	// 			$("#"+div_resultado).html('&nbsp;&nbsp;CARREGANDO OS DADOS AGUARDE...').load('<?=$PHP_SELF?>?peca='+peca+"&tabela="+tabela,{'busca_os':'busca_os'},function(response, status, xhr) {
	// 				//alert(response);
	// 				//alert(status);
	// 				//alert(xhr);
	// 				if(status == "success"){

	// 					if(verif == '0'){
	// 						$("#"+abre_div).html('-');
	// 						$("#"+abre_div).attr('alt', '1');
	// 						$("#"+tab_resultaddo).show('fast');
	// 					}else{
	// 						$("#"+abre_div).html('+');
	// 						$("#"+abre_div).attr('alt', '0');
	// 						$("#"+tab_resultaddo).hide('fast');
	// 					}

	// 				}else{
	// 					$("#"+tab_resultaddo).hide('fast');
	// 				}
	// 			});

	// 		}else{
	// 			return false;
	// 		}
	// 	});

	// 	$.tablesorter.defaults.widgets = ['zebra'];
	// 	$('.relatorio').tablesorter();
	// });
	function exibe_os(posicao) {
		var acao = $(this).data("acao");
		if (acao == "show") {
			$('.exibe_os_'+posicao).hide("slow");
			$(this).data("acao", "hide");
		} else {
			$('.exibe_os_'+posicao).show("slow");
			$(this).data("acao", "show");
		}

	}
</script>
<?php
	if(strlen($tabela) > 0){

		if($login_fabrica == 72)
		{
			$sql = "SELECT DISTINCT tbl_peca.referencia,
					tbl_peca.descricao ,
					tbl_peca.peca
					FROM tbl_os_item
					JOIN tbl_os_produto ON tbl_os_item.os_produto = tbl_os_produto.os_produto
					JOIN tbl_os 		ON tbl_os_produto.os = tbl_os.os AND tbl_os.fabrica = $login_fabrica
					JOIN tbl_posto USING(posto)
					JOIN tbl_posto_fabrica ON tbl_posto.posto = tbl_posto_fabrica.posto
					AND tbl_posto_fabrica.fabrica = $login_fabrica
					JOIN tbl_produto
					ON tbl_os.produto = tbl_produto.produto
					AND tbl_produto.fabrica_i = tbl_os.fabrica
					JOIN tbl_peca ON tbl_os_item.peca = tbl_peca.peca
					AND tbl_peca.fabrica = $login_fabrica
					JOIN tbl_servico_realizado
					ON tbl_os_item.servico_realizado=tbl_servico_realizado.servico_realizado
					AND gera_pedido IS TRUE
					AND tbl_servico_realizado.fabrica=$login_fabrica
					LEFT JOIN tbl_tabela_item
					ON tbl_peca.peca = tbl_tabela_item.peca
					AND tbl_tabela_item.tabela=$tabela
					JOIN tbl_posto_linha
					ON tbl_posto_linha.posto = tbl_posto.posto
					AND tbl_posto_linha.linha = tbl_produto.linha
					AND tbl_posto_linha.tabela = $tabela
					WHERE tbl_os_item.pedido_item IS NULL
					AND tbl_tabela_item.preco IS NULL
					AND tbl_os_item.fabrica_i = $login_fabrica ";
		}else if($login_fabrica == 35){
			$sql = "SELECT DISTINCT tbl_peca.referencia,
				tbl_peca.descricao ,
				tbl_peca.peca,
				array_to_string(array_agg(tbl_os.os),',') AS oss
				FROM tbl_os_item
				JOIN tbl_os_produto ON tbl_os_produto.os_produto = tbl_os_item.os_produto
				JOIN tbl_os ON tbl_os_produto.os = tbl_os.os AND tbl_os.fabrica = $login_fabrica AND tbl_os.finalizada IS NULL AND tbl_os.excluida IS NOT TRUE
				JOIN tbl_peca ON tbl_os_item.peca = tbl_peca.peca AND tbl_peca.fabrica = $login_fabrica
				JOIN tbl_servico_realizado ON tbl_os_item.servico_realizado=tbl_servico_realizado.servico_realizado AND gera_pedido IS TRUE AND tbl_servico_realizado.fabrica=$login_fabrica
				LEFT JOIN tbl_tabela_item ON tbl_peca.peca = tbl_tabela_item.peca AND tbl_tabela_item.tabela=$tabela
				WHERE tbl_os_item.pedido_item IS NULL
				AND tbl_tabela_item.preco IS NULL
				GROUP BY tbl_peca.referencia,
				tbl_peca.descricao ,
				tbl_peca.peca";
		}
		else
		{
			if($login_fabrica == 163){//HD-3510131
				$join_163 = "
							JOIN tbl_os_produto ON tbl_os_produto.os_produto = tbl_os_item.os_produto
							JOIN tbl_os ON tbl_os.os = tbl_os_produto.os AND tbl_os.fabrica = $login_fabrica
							JOIN tbl_tipo_atendimento ON tbl_tipo_atendimento.tipo_atendimento = tbl_os.tipo_atendimento AND tbl_tipo_atendimento.fabrica = $login_fabrica ";
				$cond_163 = " AND tbl_tipo_atendimento.fora_garantia IS NOT TRUE ";
			}else if ($login_fabrica == 178){
				$campo_os = ", tbl_os.os, tbl_os.sua_os, tbl_os_item.os_item";
				$join_os = "JOIN tbl_os_produto ON tbl_os_produto.os_produto = tbl_os_item.os_produto
							JOIN tbl_os ON tbl_os.os = tbl_os_produto.os AND tbl_os.fabrica = $login_fabrica";
			}
			$sql = "SELECT DISTINCT tbl_peca.referencia, tbl_peca.referencia_fabrica,
						   tbl_peca.descricao ,
						   tbl_peca.peca
						   $campo_os
						FROM tbl_os_item
						JOIN tbl_peca ON tbl_os_item.peca = tbl_peca.peca AND tbl_peca.fabrica = $login_fabrica
						JOIN tbl_servico_realizado ON tbl_os_item.servico_realizado=tbl_servico_realizado.servico_realizado AND gera_pedido IS TRUE AND tbl_servico_realizado.fabrica=$login_fabrica
						$join_163
						$join_os
						LEFT JOIN tbl_tabela_item ON tbl_peca.peca = tbl_tabela_item.peca AND tbl_tabela_item.tabela=$tabela
						WHERE tbl_os_item.pedido_item IS NULL
						$cond_163
						AND tbl_tabela_item.preco IS NULL
						";
		}
		$res = pg_exec($con,$sql);
		$total = pg_numrows($res);

		if($total > 0){
?>
			<table class='table table-striped table-bordered table-hover table-fixed' width="700">
			<thead>
			<tr class="titulo_coluna">
				<?php if ($login_fabrica == 171) { ?>
					<th width="250" nowrap><?php echo traduz("Refer�ncia F�brica"); ?></th>
				<?php }?>

				<th width="150" nowrap><?=traduz('Refer�ncia Pe�a')?></th>
				<th width="350" nowrap><?=traduz('Descri��o Pe�a')?></th>
				<?php if ($login_fabrica == 178){ ?>
				<th width="200"><?=traduz('OS')?></th>
				<th width="350"><?=traduz('A��o')?></th>
				<?php } ?>

			</tr>
			</thead>

		<?php
			for($i = 0; $i < $total; $i++)
			{
				$referencia_fabrica = pg_result($res,$i,referencia_fabrica);
				$codigo_peca = pg_result($res,$i,referencia);
				$codigo_nome = pg_result($res,$i,descricao);
				$id_peca	 = pg_result($res,$i,peca);
				$sua_os 	 = pg_fetch_result($res, $i, sua_os);
				$os 		 = pg_fetch_result($res, $i, os);
				$os_item  	 = pg_fetch_result($res, $i, os_item);

				$cor = ($i % 2) ? "#F7F5F0" : "#F1F4FA";

				if ($login_fabrica == 35) {

					$listaOs = pg_result($res,$i,oss);

					$listaOs = explode(", ",$listaOs);
					$oss = array();

					foreach($listaOs AS $key => $value){

						$oss[] = "<a href='os_press.php?os=$value' target='_blank'>$value</a>";
					}

					echo '
					<tr bgcolor="'.$cor.'" onclick="exibe_os('.$i.');" style="cursor:pointer;" data-acao="hide">
						<td>'.$codigo_peca.'</td>
						<td>'.$codigo_nome.'</td>
					</tr>
					<tr style="display:none;" class="exibe_os_'.$i.'">
						<td colspan="2"><b>OSs:</b> '.implode(",",$oss).'</td>
					</tr>
					';
				} elseif($login_fabrica == 72) {
		?>
					</table>

					<table class='table table-striped table-bordered table-hover table-fixed'>
					<thead>
						<tr bgcolor='<?php echo $cor; ?>' class="titulo_coluna" >
							<td width="20" nowrap><span id="abre_div<?php echo $id_peca;?>" alt="0">+</span></td>
							<td width="230" nowrap>
								<a href="javascript:void(0)" class="busca_dados" rel="<?php echo $id_peca;?>" alt="<?php echo $tabela;?>">
									<?php echo $codigo_peca;?>
								</a>
							</td>
							<td width="450" nowrap>
								<a href="javascript:void(0)" class="busca_dados" rel="<?php echo $id_peca;?>" alt="<?php echo $tabela;?>">
									<?php echo $codigo_nome;?>
								</a>
							</td>
						</tr>
					</thead>
					</table>

					<table  class='table table-striped table-bordered table-hover table-fixed' id="table_conteudo<?php echo $id_peca;?>" style="display:none;">
						<tr>
							<td>
								<div id="div_conteudo<?php echo $id_peca;?>" class="div_resultado_conteudo"></div>
							</td>
						</tr>
					</table>
			<?php
				}
				else
				{
			?>
					<tr bgcolor='<?php echo $cor; ?>' id="<?=$os_item?>">
					<?php 
						if ($login_fabrica == 171) {
							echo "<td><a href='preco_cadastro.php?peca=".$id_peca."' target='_blank'>
						".$referencia_fabrica."</a></td>";
						}
					?>
						<td><a href='preco_cadastro.php?peca=<?=$id_peca?>' target='_blank'><?php echo $codigo_peca;?></a></td>
						<td><a href='preco_cadastro.php?peca=<?=$id_peca?>' target='_blank'><?php echo $codigo_nome;?></a></td>
						<?php if ($login_fabrica == 178){ ?>
						<td class="tac"><a href='os_press.php?os=<?=$os?>' target='_blank'><?=$sua_os?></a></td>
						<td class='tac'> <button class="btn_liberar_peca btn btn-small btn-primary" data-os_item="<?=$os_item?>">Liberar Pe�a Faturamento</button></td>
						<?php } ?>
					</tr>
			<?php
				}
			}
			?>
			</table>
		<?php
		}
		else{
			echo "	<div class='alert'>
						<h4>". traduz("Nenhum resultado encontrado") ."</h4>
					</div>";
		}
	}
	include "rodape.php";
?>
