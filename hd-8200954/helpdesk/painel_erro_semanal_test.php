<?php
	include 'dbconfig.php';
	include 'includes/dbconnect-inc.php';
	
	// usando para pesquisa em Ajax
	$consulta_dados = @$_POST['consulta_dados'];
	if($consulta_dados == "busca_dados_ajax"){
		function verificaValor($valor){
			if($valor > 0)
				return $valor;
			else
				return 0;
		}

		$novos = "";
		$resolvidos = "";
		$semana = "";
		for ($x = 0 ; $x < 7 ; $x++){
			$date = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d") - $x, date("Y")));

			$sql = "	SELECT 
							COUNT(hd_chamado) 
						FROM 
							tbl_hd_chamado 
						WHERE 
							tipo_chamado = 5 
							AND status <> 'Cancelado' 
							AND data BETWEEN '$date 00:00:00' AND '$date 23:59:59';";
			$res = pg_query($con, $sql);
			
			$novos .= pg_fetch_result($res,0)."|";
			$total_hd_novos += pg_fetch_result($res,0);
			
			if($x == 0)
				$hoje_novo = pg_fetch_result($res,0);


			$sql = "	SELECT 
							COUNT(hd_chamado) 
						FROM 
							tbl_hd_chamado 
						WHERE 
							tipo_chamado = 5 
							AND status <> 'Cancelado' 
							AND data_resolvido BETWEEN '$date 00:00:00' AND '$date 23:59:59';";
			$res = pg_query($con, $sql);
			
			$resolvidos .= pg_fetch_result($res,0)."|";
			$total_hd_resolvidos += pg_fetch_result($res,0);
			
			if($x == 0)
				$hoje_resolvido = pg_fetch_result($res,0);
			
			//Pega o dia da semana no banco de dados
			$sql = "
						SELECT 
						  CASE EXTRACT(DOW FROM DATE '$date')
								WHEN 0 THEN 'Domingo'
						      WHEN 1 THEN 'Segunda'
						      WHEN 2 THEN 'Ter�a'
						      WHEN 3 THEN 'Quarta'
						      WHEN 4 THEN 'Quinta'
						      WHEN 5 THEN 'Sexta'
						      WHEN 6 THEN 'S�bado'
						    ELSE 'Semana'
						  END;
			";
			$res = pg_query($con, $sql);
			$semana .= pg_fetch_result($res,0)."|";

		}
		
		$hoje_total = $hoje_resolvido - $hoje_novo;
		$retorno = $novos.$total_hd_novos."|".$resolvidos.$total_hd_resolvidos."|".$semana;
		
		echo $retorno.$hoje_novo."|".$hoje_resolvido."|".$hoje_total;
		exit;				
	}

	if($consulta_dados == "galeria"){

		$url = "http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=949751b331dc1243b750eeb6e8bea493&tags=madagascar%20movie&per_page=5&page=1&format=php_serial";
		$rsp = file_get_contents($url);
		$rsp_obj = unserialize($rsp);

		if ($rsp_obj['stat'] == 'ok'){
			$total = Count($rsp_obj['photos']['photo']);

			foreach($rsp_obj['photos']['photo'] as $album){ 
				$url = "http://farm{$album['farm']}.static.flickr.com/{$album['server']}/{$album['id']}_{$album['secret']}_b.jpg";
				
				echo "<li><img src='$url' /></li>";
			}
		}
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>HD - Painel de Erros</title>
		<script src="http://code.jquery.com/jquery-latest.js"></script>
		<script type="text/javascript" src="../plugins/jquery/slideshow/jquery.cycle.all.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){ 
				
				$('#galeria').css('display','none');
			});

			$(window).load(function() {
				atualizaDados();
				atualizaGaleria();
			});

			var posicao = 0;
			var galeria = 0;

			function atualizaDados(){
				$.ajax({
					url: "<?php echo $PHP_SELF;?>",
					type: "POST",
					data: "consulta_dados=busca_dados_ajax",
					success: function(retorno){
						$('#galeria').css('display','none');
						$('#panelErros').css('display','block');

						dados = retorno.split('|');
						
						//novos
						$('#hd_0').html(dados[0]);
						$('#hd_1').html(dados[1]);
						$('#hd_2').html(dados[2]);
						$('#hd_3').html(dados[3]);
						$('#hd_4').html(dados[4]);
						$('#hd_5').html(dados[5]);
						$('#hd_6').html(dados[6]);
						$('#hd_total').html(dados[7]);
						
						//resolvidos
						$('#hd_0_r').html(dados[8]);
						$('#hd_1_r').html(dados[9]);
						$('#hd_2_r').html(dados[10]);
						$('#hd_3_r').html(dados[11]);
						$('#hd_4_r').html(dados[12]);
						$('#hd_5_r').html(dados[13]);
						$('#hd_6_r').html(dados[14]);
						$('#hd_total_r').html(dados[15]);
						
						//dia da semana
						$('#sm_0').html(dados[16]);
						$('#sm_1').html(dados[17]);
						$('#sm_2').html(dados[18]);
						$('#sm_3').html(dados[19]);
						$('#sm_4').html(dados[20]);
						$('#sm_5').html(dados[21]);
						$('#sm_6').html(dados[22]);
						
						//HD hoje
						$('#hj_novos').html(dados[23]);
						$('#hj_resolvidos').html(dados[24]);
						$('#hj_total').html(dados[25]);

						//PlaySound("sound1");

						if(posicao == 1){
							posicao = 0;

							$("#hj_resolvidos").css("background","#93D69A url(imagem/escudos/escudoPalmeiras.png) top right no-repeat");
							$("#hj_novos").css("background","#F49C9C url(imagem/escudos/escudoSaoPaulo.png) top left no-repeat");

							/*
							if(dados[23] > dados[24]){
								$("#hj_novos").css("background","#F49C9C url(imagem/escudos/escudoPalmeiras.png) top left no-repeat");
								$("#hj_resolvidos").css("background","#93D69A url(imagem/escudos/escudoSaoPaulo.png) top right no-repeat");
							}
							*/
						}else{

							$("#hj_resolvidos").css("background","#93D69A url(imagem/logo/logoLinux.png) top right no-repeat");
							$("#hj_novos").css("background","#F49C9C url(imagem/logo/logoMicrosoft.png) top left no-repeat");

							posicao = 1;
						}

						if(galeria < 5){
							galeria += 1;
							if(galeria == 2){
								$("#galeria").css('display','none');
								$('#panelErros').css('display','block');
							}

						}else{
							galeria = 0;
							$("#galeria").css('display','block');
							$('#panelErros').css('display','none');
							atualizaGaleria();
						}
					}
				});
				setTimeout("atualizaDados()",10000); 
			}

			function atualizaGaleria(){
				$.ajax({
					url: "<?php echo $PHP_SELF;?>",
					type: "POST",
					data: "consulta_dados=galeria",
					success: function(retorno){
						$('#slideshow').html(retorno);

						$('#slideshow').cycle({ 
							fx: 'all' 
						});
					}
				});


			}
		</script>
		<style type="text/css">
			*{
				font-family: Verdana,Arial,sans-serif;
			}
			body, html{
				padding: 0;
				margin: 0;
				background: #FCFCFC;

			}

			h1{
				font-size: 24px; 
				color: #F00;
				margin: 40px;
				font-weight: normal;
				margin: 5px;
				padding: 0;
			}

			.painel{
				margin: 5px 40px;
				border: 1px solid #999;
				padding: 1px;
				background: #FFF;

			}

			.titlePainel {
				color: #666;
				font-size: 16px;
				padding: 0 10px;
				padding: 10px;
				text-align:  right;
				background: #CCC;
			}

			.totalHD{
				font-size: 200px;
				color: #F00;
				text-align: center;
				font-weight: bold;
			}
			
			.descricaoHD{
				font-size: 60px;
				color: #F00;
				text-align: center;
			}
			table{
				width: 98%;
				margin: 10px auto;
				background: #A51515
			}
			
			table thead th{
				width: 13%;
			}
			
			table tbody td{
				font-size: 100px;
				color: #A51515;
				text-align: center;
				font-weight: bold;
				background: #FCFCFC;
			}
			
			#hj_novos, #hj_resolvidos, #hj_total{
				font-size: 200px;
				color: #A51515;
				text-align: center;
				font-weight: bold;
				background: #FCFCFC;
			}

			#hj_resolvidos{
				background: #93D69A url(imagem/logo/logoLinux.png) top right no-repeat;
			}
			
			#hj_novos{
				background: #F49C9C url(imagem/logo/logoMicrosoft.png) top left no-repeat;
			}

			table tbody th{
				background: #A51515;
				color: #FFF;
				font-size: 16px;
				text-align: left;
				padding: 5px 10px;
			}
			
			table thead th{
				background: #CCC;
				color: #A51515;
			}
			
			table caption{
				font-size: 18px;
				text-align: right;
				padding: 10px;
				border: 1px solid #A51515;
				border-bottom: none;
				color: #fff;
				background: #A51515;
				font-weight: bold;
			}
			
			#hd_total, #hd_total_r, #hj_total{
				background: #93D69A;
			}
			
			#hd_total{
				background: #F49C9C;
			}
			
			#panelErros{
				z-index: 0;
			}

			#galeria{
				z-index: 100;
				width: 100%;
				height: 100%;
				background: #000 url('http://i1.peperonity.info/c/4DD967/538119/ssc3/home/084/aawarapan1/178_1848_tv_chuvisco.gif_320_320_256_9223372036854775000_0_1_0.gif');
				
				text-align:center;
				margin: 0;
				padding: 0;
			}

			#galeria ul, #galeria li{
				margin: 0 auto;
				padding: 0;
				text-align: center !important;
				list-style: none;
			}

			#galeria li img{
				margin: auto;
			}
		</style>
	</head>

	<body>
		<div id='galeria'>
			<ul id='slideshow'>
			
			</ul>
		</div>
		<div id='panelErros'>
			<table cellpadding="2" cellspacing="1" border="0">
				<caption>Painel de Erros: "Erro no Programa"</caption>
				<thead>
					<tr>
						<th colspan="2">Hoje</th>
					</tr>
				</thead>
				<tbody>
					<tr> 
						<th style="width: 50%">Chamados Novos</th>
						<th style="width: 50%">Chamados Resolvidos</th>
						<!-- <th style="width: 33%">Total</th> -->
					</tr>
					<tr>
						<td id='hj_novos' >&nbsp;</td>
						<td id='hj_resolvidos' >&nbsp;</td>
						<!-- <td id='hj_total' >&nbsp;</td> -->
					</tr>
				</tbody>
			</table>
			
			<table cellpadding="2" cellspacing="1" border="0">
			<thead>
					<tr>
						<!-- <th id='sm_0'>Semana</th> -->
						<th id='sm_6'>Semana</th>
						<th id='sm_5'>Semana</th>
						<th id='sm_4'>Semana</th>
						<th id='sm_3'>Semana</th>
						<th id='sm_2'>Semana</th>
						<th id='sm_1'>Semana</th>
						<th>Total</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th colspan='7'>Chamados Novos</th>
					</tr>
					<tr>
						<!-- <td id='hd_0'>&nbsp;</td> -->
						<td id='hd_6'>&nbsp;</td>
						<td id='hd_5'>&nbsp;</td>
						<td id='hd_4'>&nbsp;</td>
						<td id='hd_3'>&nbsp;</td>
						<td id='hd_2'>&nbsp;</td>
						<td id='hd_1'>&nbsp;</td>
						<td id='hd_total'>&nbsp;</td>
					</tr>
					<tr>
						<th colspan="7">Chamados Resolvidos</th>
					</tr>
					<tr>
						<!-- <td id='hd_0_r'>&nbsp;</td> -->
						<td id='hd_6_r'>&nbsp;</td>
						<td id='hd_5_r'>&nbsp;</td>
						<td id='hd_4_r'>&nbsp;</td>
						<td id='hd_3_r'>&nbsp;</td>
						<td id='hd_2_r'>&nbsp;</td>
						<td id='hd_1_r'>&nbsp;</td>
						<td id='hd_total_r'>&nbsp;</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<!-- <embed src="http://www.phon.ucl.ac.uk/home/mark/audio/success.wav" autostart="true" width="0" height="0" id="sound1" enablejavascript="true"> -->
	</body>
</html>