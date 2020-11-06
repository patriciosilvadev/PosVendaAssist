<?php
	include 'dbconfig.php';
	include 'includes/dbconnect-inc.php';
	include 'autentica_usuario.php';

	$msg_erro = "";
	$msg_debug = "";
	$fabrica = 1;
	#$pesquisa = "atualizacao_cadastral";

	function validaEmail($email) {
		$er = "/^(([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}){0,1}$/";
	    if (preg_match($er, $email)){
			return true;
	    } else {
			return false;
	    }
	}

	$sqlX = "SELECT pesquisa
	      	FROM tbl_pesquisa
			WHERE fabrica = $fabrica
			AND categoria = 'atualizacao_cadastral'
	       	AND ativo";
	$resX = pg_query($con, $sqlX);
	if(pg_num_rows($resX) > 0) {
		$pesquisa = pg_fetch_result($resX, 0, 'pesquisa');
	}

	$title = "Pesquisa" ;

	if ($_GET['ajax'] && $_GET['gravaPerguntas'] ){

		$erro          = array();
		$qtde_perg     = $_GET['qtde_perg'];
		$arrayCheckbox = array();

		$res = pg_query($con,'BEGIN');

		for ($i=0; $i < $qtde_perg; $i++) {

			$pergunta = $_GET['perg_'.$i];
			$tipo_resposta = $_GET['hidden_'.$i];

			$resposta = (isset($_GET['perg_opt'.$pergunta])) ? utf8_decode(trim($_GET['perg_opt'.$pergunta])) : '';

			if (in_array($tipo_resposta, array('text','range','textarea','date'))) {
				$txt_resposta = $resposta;
				$resposta = 'null';
			}

			if (!empty($resposta) and $resposta != 'null') {

				$sqlItens = "SELECT tbl_tipo_resposta_item.descricao FROM tbl_tipo_resposta_item where tipo_resposta_item = $resposta";
				$resItens = pg_query($con,$sqlItens);

				if (pg_num_rows($resItens)>0) {
					$txt_resposta = pg_fetch_result($resItens,0,0);
				}else{
					$txt_resposta = $resposta;
				}
			}

			if(strlen(trim($txt_resposta)) == 0){
				if($pergunta != 580){
					if(count($erro) > 0){
						continue;
					}else{
						$erro[] = "Favor preencher todas as respostas obrigat�rias";
					}
				}
			}

			if($pergunta == 578 AND strlen(trim($txt_resposta)) > 0){
				if (validaEmail($txt_resposta) === false) {
					$erro[] = "O e-mail inserido � invalido!";
				}
			}

			if(!count($erro)){
				$sql = "INSERT INTO tbl_resposta (
							pergunta,
							txt_resposta,
							tipo_resposta_item,
							pesquisa,
							data_input,
							posto
						)VALUES(
							$pergunta,
							'$txt_resposta',
							$resposta,
							'$pesquisa',
							current_timestamp,
							$login_posto
						)
						";
				$res = pg_query($con,$sql);
				if (pg_last_error($con)){
					$erro[] = pg_last_error($con) ;
				}
			}
		}
		if (count($erro)>0){
			$erro = implode('<br>', $erro);
			if(strpos($erro, 'syntax erro') > 0 ){
				$erro = "Erro ao gravar pesquisa";
			}
			$res = pg_query($con,'ROLLBACK TRANSACTION');
		}else{
			$res = pg_query($con,'COMMIT TRANSACTION');
		}

		if ($erro){
			echo "1|$erro";
		}else{
			echo "0|Sucesso";
		}

		exit;

	}

	include_once 'funcoes.php';
?>
<script type="text/javascript" src="plugins/posvenda_jquery_ui/js/jquery-1.9.1.js"></script>
<script type="text/javascript" src="js/jquery.maskedinput2.js"></script>
<script type="text/javascript" src="admin/js/jquery.mask.js"></script>
<script type="text/javascript" src="plugins/jquery/datepick/jquery.datepick.js"></script>
<script type="text/javascript" src="plugins/jquery/datepick/jquery.datepick-pt-BR.js"></script>
<script type="text/javascript" src="plugins/jquery.form.js"></script>
<link rel="stylesheet" type="text/css" href="plugins/jquery/datepick/telecontrol.datepick.css" />
<script type='text/javascript' src='js/FancyZoom.js'></script>
<script type='text/javascript' src='js/FancyZoomHTML.js'></script>

<style>
	table.tabela tr td{
	    font-family: verdana;
	    font-size: 13px;
	    border-collapse: collapse;
	    border:1px solid #596d9b;
	}

	table.tabela tr{
	    background-color: #F3F3F3;
	}

	table.tabela tr:nth-child(2n+1) {
	  background-color: #E4E9FF;
	}

	.white {
	  background-color: white;
	}

	.titulo_tabela {
	    background-color: #596D9B;
	    color: #FFFFFF;
	    font: bold 14px "Arial";
	    text-align: center;
	}
	.titulo_tabela2 {
	    background-color: #596D9B;
	    color: #FFFFFF;
	}
</style>

<script>

$(function(){
	var phoneMask = function(){
		if($(this).val().match(/^\(0/)){
    		$(this).val('(');
    		return;
    	}

    	if($(this).val().match(/^\([1-9][0-9]\) *[0-8]/)){
    		$(this).mask('(00) 0000-0000');
    		console.debug('telefone');
    	}else{
			$(this).mask('(00) 00000-0000');
			console.debug('celular');
    	}
    	$(this).keyup(phoneMask);
    };

    $("input[name='perg_opt579']").keyup(phoneMask);
    $("input[name='perg_opt580']").keyup(phoneMask);

	//$(".date").datepick({startDate:"01/01/1930"});
	//$(".date").maskedinput("99/99/9999");
	$(".sys_tabs").hide();
	$("#browsers").hide();
	$("#info").hide();
	$("img[src='helpdesk/imagem/help.png']").parent("a").hide();
	$.ajax({
		type: "POST",
		url: "upload_opiniao_posto_blackedecker.php",
		data: {
			get_foto:"true",
			posto:"<?=$login_posto?>"
		},
		beforeSend: function(){
			$("#img_fachada").attr("src","admin/imagens/ajax-loader.gif");
			$('#img_fachada').show();

			$("#img_balcao").attr("src","admin/imagens/ajax-loader.gif");
			$('#img_balcao').show();

			$("#img_oficina").attr("src","admin/imagens/ajax-loader.gif");
			$('#img_oficina').show();

			$("#img_estoque").attr("src","admin/imagens/ajax-loader.gif");
			$('#img_estoque').show();
		},
		complete: function(r) {
			resp = $.parseJSON(r.responseText);
        	if(resp.success=="false"){
        		alert(resp.msg)
        	}else{
        		if( (resp.thumbs.foto_fachada.length > 0) && (resp.files.foto_fachada.length > 0)){
	       			$('#img_fachada').attr('src',resp.thumbs.foto_fachada);
	       			$('#src_img_fachada').attr('href',resp.files.foto_fachada);
	       			$('#img_fachada').show();
        		}else{
        			var html = "<a id='src_img_fachada' href='#'> <img id='img_fachada' src='' alt='Sem Fotos'> </a>"
        			$('#div_fachada').html(html);
        		}
        		if( resp.thumbs.foto_balcao.length > 0 && (resp.files.foto_balcao.length > 0)){
	       			$('#img_balcao').attr('src',resp.thumbs.foto_balcao);
	       			$('#src_img_balcao').attr('href',resp.files.foto_balcao);
	       			$('#img_balcao').show();
        		}else{
        			var html = "<a id='src_img_balcao' href='#'> <img id='img_balcao' src='' alt='Sem Fotos'> </a>"
        			$('#div_balcao').html(html);
        		}
        		if( resp.thumbs.foto_oficina.length > 0 && (resp.files.foto_oficina.length > 0)){
	       			$('#img_oficina').attr('src',resp.thumbs.foto_oficina);
	       			$('#src_img_oficina').attr('href',resp.files.foto_oficina);
	       			$('#img_oficina').show();
        		}else{
        			var html = "<a id='src_img_oficina' href='#'> <img id='img_oficina' src='' alt='Sem Fotos'> </a>"
        			$('#div_oficina').html(html);
        		}
        		if( resp.thumbs.foto_estoque.length > 0 && (resp.files.foto_estoque.length > 0)){
	       			$('#img_estoque').attr('src',resp.thumbs.foto_estoque);
	       			$('#src_img_estoque').attr('href',resp.files.foto_estoque);
	       			$('#img_estoque').show();
        		}else{
        			var html = "<a id='src_img_estoque' href='#'> <img id='img_estoque' src='' alt='Sem Fotos'> </a>"
        			$('#div_estoque').html(html);
        		}
        		setupZoom();
        	}
		}
	});
	$("#frm_fotos_posto").ajaxForm({
	    beforeSend: function(){
	    	if( (($("#foto_fachada").val().length > 0) && ($("#img_fachada").attr("src").length == 0) )
	    		|| (($("#foto_fachada").val().length > 0 ) && ($("#img_fachada").attr("src").length > 0))	){
				$("#img_fachada").attr("src","admin/imagens/ajax-loader.gif");
				$('#img_fachada').show();
	    	}

	    	if( ( ($("#foto_balcao").val().length > 0) && ($("#img_balcao").attr("src").length == 0))
	    		|| (($("#foto_balcao").val().length > 0) && ($("#img_balcao").attr("src").length > 0))) {
				$("#img_balcao").attr("src","admin/imagens/ajax-loader.gif");
				$('#img_balcao').show();
	    	}

	    	if( (($("#foto_oficina").val().length > 0) && ($("#img_oficina").attr("src").length == 0))
	    		|| (($("#foto_oficina").val().length > 0) && ($("#img_oficina").attr("src").length > 0)) ){
				$("#img_oficina").attr("src","admin/imagens/ajax-loader.gif");
				$('#img_oficina').show();
			}
			if( (($("#foto_estoque").val().length >0) && ($("#img_estoque").attr("src").length == 0))
				|| (($("#foto_estoque").val().length >0) && ($("#img_estoque").attr("src").length > 0)) ){
				$("#img_estoque").attr("src","admin/imagens/ajax-loader.gif");
				$('#img_estoque').show();
			}
		},
        complete: function(data) {
        	data = $.parseJSON(data.responseText);

        	if(data.success=="false"){
        		alert(data.msg)
				$('#img_fachada').attr('src',"");
				$('#src_img_fachada').attr('href',"#");
				$('#img_balcao').attr('src',"");
				$('#src_img_balcao').attr('href',"#");
				$('#img_oficina').attr('src',"");
				$('#src_img_oficina').attr('href',"#");
				$('#img_estoque').attr('src',"");
				$('#src_img_estoque').attr('href',"#");
        	}else{
        		if( (data.thumbs.foto_fachada.length > 0) && (data.files.foto_fachada.length > 0)){
        			var html = "<a id='src_img_fachada' href='"+data.files.foto_fachada+"'> <img id='img_fachada' style='display:none;' src='"+data.thumbs.foto_fachada+"' alt='Sem Fotos'> </a>"
        			$('#div_fachada').html(html);
	       			$('#img_fachada').show();
        		}

        		if( (data.thumbs.foto_balcao.length > 0) && (data.files.foto_balcao.length > 0)){
					var html = "<a id='src_img_balcao' href='"+data.files.foto_balcao+"'> <img id='img_balcao' style='display:none;' src='"+data.thumbs.foto_balcao+"' alt='Sem Fotos'> </a>"
        			$('#div_balcao').html(html);
	       			$('#img_balcao').show();
        		}

        		if( (data.thumbs.foto_oficina.length > 0) && (data.files.foto_oficina.length > 0)){
					var html = "<a id='src_img_oficina' href='"+data.files.foto_oficina+"'> <img id='img_oficina' style='display:none;' src='"+data.thumbs.foto_oficina+"' alt='Sem Fotos'> </a>"
        			$('#div_oficina').html(html);
	       			$('#img_oficina').show();
        		}

        		if( (data.thumbs.foto_estoque.length > 0) && (data.files.foto_estoque.length > 0)){
					var html = "<a id='src_img_estoque' href='"+data.files.foto_estoque+"'> <img id='img_estoque' style='display:none;' src='"+data.thumbs.foto_estoque+"' alt='Sem Fotos'> </a>"
        			$('#div_estoque').html(html);
	       			$('#img_estoque').show();
        		}
        		setupZoom();
        	}
        }
    });

	$(document).on("click",'.btn_grava_pesquisa',function(){
		var curDateTime = new Date();
		var relBtn = $(this).attr('rel');
		var dados = '';
		dados = 'ajax=true&gravaPerguntas=true&pesquisa='+relBtn+'&'+$('.table_perguntas_pesquisa').find('input').serialize()+'&'+$('.table_perguntas_pesquisa').find('textarea').serialize()
		$.ajax({
			type: "GET",
			url: "<?=$PHP_SELF?>",
			data: dados,
			beforeSend: function(){
				$('input[name=pesquisa]').attr('disabled',true);
				$('.btn_grava_pesquisa').hide();
				$('.td_btn_gravar_pergunta').show();
				$('.td_btn_gravar_pergunta').html("&nbsp;&nbsp;Gravando...&nbsp;&nbsp;<br><img src='imagens/loading_bar.gif'> ");
				//$('.divTranspBlock').show();
			},
			complete: function(http) {
				results = http.responseText;
				results = results.split('|');
				if (results[0] == 1){
					$('div.errorPergunta').html(results[1]);
					$('div.errorPergunta').show();
					$('.td_btn_gravar_pergunta').hide();
					$('input[name=pesquisa]').attr('disabled',false);
					$('.divTranspBlock').hide();
					$('.btn_grava_pesquisa').show();
				}else{
					$('div.errorPergunta').hide();
					$('.divTranspBlock').hide();
					$(".table_perguntas_pesquisa").find("input[type!=file][type!=submit]").attr({ "disabled": "disabled" });
					$('.td_btn_gravar_pergunta').hide();
					window.location = 'menu_inicial.php';
				}
			}
		});
	});
});
</script>

<?include "cabecalho.php";?>

<div class='errorPergunta' style='width: 700px; background-color:#F92F2F;color:#FFF;font:bold 14px Arial'></div>

<div class='divTranspBlock' style='margin-top:57px;margin-left:378px;display:none;background-color:#000;position:absolute; z-index:1;width:900px;height:295px;opacity:0.65;-moz-opacity: 0.65;filter: alpha(opacity=65);'>
</div>

<div id="div_pesquisa" style="width:100%;border:#CC3300 1px solid; background-color: #F4E6D7;font-size:10px;display:none">
</div>

<div id="div_pesquisa" style="width:100%;border:#CC3300 1px solid; background-color: #F4E6D7;font-size:10px;display:none" class='agradecimentosPesquisa' style='display:none'>
	<p>
		Obrigado por participar da pesquisa. Daqui 3 segundos voc� ser� direcionado para menu inicial
	</p>
</div>

<?php
	/*PEGA O TEXTO DE AJUDA na tbl_pesquisa.texto_ajuda*/
	$sql = "SELECT tbl_pesquisa.texto_ajuda FROM tbl_pesquisa WHERE pesquisa = $pesquisa and fabrica=$fabrica";
	$res = pg_query($con,$sql);
	$texto_ajuda = (pg_num_rows($res) > 0) ? pg_fetch_result($res,0,'texto_ajuda') : '' ;

	$sql = "SELECT  tbl_pesquisa_pergunta.ordem,
			tbl_pergunta.pergunta,
			tbl_pergunta.descricao,
			tbl_pergunta.tipo_resposta,
			tbl_tipo_resposta.tipo_descricao,
			tbl_pesquisa.pesquisa,
			tbl_tipo_pergunta.descricao as tipo_pergunta_descricao,
			tbl_tipo_pergunta.tipo_pergunta
		FROM tbl_pesquisa_pergunta
		INNER JOIN tbl_pergunta using(pergunta)
		INNER JOIN tbl_pesquisa using(pesquisa)
		INNER JOIN tbl_tipo_pergunta using(tipo_pergunta)
		LEFT JOIN tbl_tipo_resposta on (tbl_pergunta.tipo_resposta = tbl_tipo_resposta.tipo_resposta)
		WHERE tbl_pesquisa.fabrica = $fabrica
	       	AND 	tbl_pesquisa.pesquisa = $pesquisa
		and tbl_pergunta.ativo is true ORDER BY tbl_pesquisa_pergunta.ordem";
	$res = pg_query($con,$sql);
	$html_pesquisa .= '<br/><table width="700px" class="tabela table_perguntas_pesquisa" border="0" cellpadding="2" cellspacing="2" style="margin:auto;font-size:10px" >';

	if (pg_num_rows($res)>0) {
		$i = 0;
		$respostasPergunta = array();
		//percorre o array da consulta principal 1� vez para jogar as respostas em um array
		foreach (pg_fetch_all($res) as $key) {
			$sql = "SELECT pergunta, txt_resposta,tipo_resposta_item
				FROM tbl_resposta
				WHERE pergunta = {$key['pergunta']}
				AND pesquisa = {$pesquisa}
				AND posto = $login_posto
				ORDER BY pergunta";
			$resRespostas = pg_query($con,$sql);
			if (pg_num_rows($resRespostas)>0) {
				foreach (pg_fetch_all($resRespostas) as $keyRespostas) {
					if (!empty($keyRespostas['tipo_resposta_item'])) {
						$respostasPergunta[$key['pesquisa']][$key['pergunta']]['respostas'][] = $keyRespostas['tipo_resposta_item'];
					}else{
						$respostasPergunta[$key['pesquisa']][$key['pergunta']]['respostas'][] = $keyRespostas['txt_resposta'];
					}
				}
			}
		}

		//percorre a segunda vez para montar o formul�rio
		foreach (pg_fetch_all($res) as $key) {
			$cor = ($i % 2) ? "#E4E9FF" : "#F3F3F3";
			if((empty($tipo_pergunta) or $tipo_pergunta <> $key['tipo_pergunta']) and !empty($key['tipo_pergunta'])){
				$html_pesquisa .="  <tr bgcolor='$cor' style='display:none;'>
										<td class=\"titulo_tabela\" colspan='100%'>
										 	".$key['tipo_pergunta_descricao']." - Pesquisa de Posto <br/>
										 	<input type='hidden' name='perg_".$i."' value='".$key['pergunta']."' placeholder=''>
										 	<input type='hidden' name='hidden_$i' value='".$key['tipo_descricao']."' >
										 </td>
									</tr>
									<tr>
										<td class='titulo_tabela2' colspan='100%'>
										Prezado parceiro,<br/>
											Para mantermos seu cadastro atualizado e melhorar nosso meio de comunica��o,
											pedimos que reserve 1 minuto para preencher os dados abaixo.<br/><br/>
											Obs: Campos obrigat�rios em vermelho.
										</td>
									</tr>
									";
			}
			//Obs: (Perguntas com <span style='color:red;'>*</span> preenchimento obrigat�rio).
			$html_pesquisa .= "	<tr bgcolor='$cor'>
									<td style='text-align:center;' >
										<label > ".$key['ordem']." </label>
									</td>";
			$html_pesquisa .= "		<td  align='left' nowrap  style='text-align:justify;padding: 0px 10px 0px 10px' >
										<input type='hidden' name='perg_".$i."' value='".$key['pergunta']."' placeholder=''>
										<input type='hidden' name='hidden_$i' value='".$key['tipo_descricao']."' >";
			//							if($key['pergunta'] != 561){
			//$html_pesquisa .= "			<span style='color:red;'>* </span>";
			//							}
			$html_pesquisa .= 			$key['descricao'];
										// if($key['pergunta'] == 562){
										// 	$html_pesquisa .= " (Pessoa respons�vel)";
										// }
			$html_pesquisa .= "		</td>";
			if (!empty($key['tipo_resposta'])) {
				$sql = "SELECT tbl_tipo_resposta_item.descricao,
						tbl_tipo_resposta.label_inicio,
						tbl_tipo_resposta.label_fim,
						tbl_tipo_resposta.label_intervalo,
						tbl_tipo_resposta.tipo_descricao,
						tbl_tipo_resposta_item.tipo_resposta_item
					FROM tbl_tipo_resposta
					LEFT JOIN    tbl_tipo_resposta_item using(tipo_resposta)
					WHERE tbl_tipo_resposta.tipo_resposta = ".$key['tipo_resposta']."
					AND tbl_tipo_resposta.fabrica = $fabrica
					ORDER BY tbl_tipo_resposta_item.ordem ";
				$res = pg_query($con,$sql);
				if (pg_num_rows($res)>0) {
					for ($x=0; $x < pg_num_rows($res); $x++) {
						if (!empty($respostasPergunta)) {
						}
						$item_tipo_resposta_desc  = pg_fetch_result($res, $x, 'descricao');
						$item_tipo_resposta_tipo   = pg_fetch_result($res, $x, 'tipo_descricao');
						$item_tipo_resposta_label_inicio = pg_fetch_result($res, $x, 'label_inicio');
						$item_tipo_resposta_label_fim    = pg_fetch_result($res, $x, 'label_fim');
						$item_tipo_resposta_label_intervalo    = pg_fetch_result($res, $x, 'label_intervalo');
						$tipo_resposta_item_id    = pg_fetch_result($res, $x, 'tipo_resposta_item');

						if (in_array($item_tipo_resposta_tipo, array('checkbox','radio'))) {
							$colspan = "";
							$width = "";
						}else{
							$colspan = "50%";
						}

						$html_pesquisa .= '<td align="center" nowrap colspan="'.$colspan.'" >';
						if ($item_tipo_resposta_tipo == 'radio' or $item_tipo_resposta_tipo == 'checkbox') {
							$value_resposta = $tipo_resposta_item_id;
						}else{
							$value_resposta = $item_tipo_resposta_desc;
						}
						switch ($item_tipo_resposta_tipo) {
							case 'text':
								unset($place);
								if($key['pergunta'] == 581){
									$place = 'placeholder=" Pessoa respons�vel"';
								}
								if($key['pergunta'] == 580){
									$style = 'style="width:400px;"';
								}else{
									$style = 'style="width:400px; background-color: #ffdddd;"';
								}
								$item_tipo_resposta_desc = $key['txt_resposta'];
								$disabled_resposta = "disabled='DISABLED'";
								$value_resposta = $item_tipo_resposta_desc;
								if (is_array($respostasPergunta) and !empty($respostasPergunta)){
									if (!empty($respostasPergunta[$key['pesquisa']][$key['pergunta']]['respostas'])) {
										$value_resposta = $respostasPergunta[$key['pesquisa']][$key['pergunta']]['respostas'][0];
									}
								}
								$html_pesquisa .= ' <input type="'.$item_tipo_resposta_tipo.'"'.$place.$style.'name="perg_opt'.$key['pergunta'].'"  class="frm" value="'.$value_resposta.'" '.$disabled.' />';
								break;
						}
						$html_pesquisa .= '</td>';
						unset($checked_radio);
					}
				}
			}else{
				$html_pesquisa .= "<td colspan='3'>&nbsp; </td>";
			}

			$html_pesquisa .= "
					</tr>";
			$i++;
			$tipo_pergunta = $key['tipo_pergunta'];
		}
	}
	if (is_array($respostasPergunta) and empty($respostasPergunta)) {
		$html_pesquisa .= '<tr><td colspan="100%" style="text-align:center">
									<input type="hidden" name="qtde_perg" value="'.$i.'">
									<input type="button" value="Gravar" class="btn_grava_pesquisa" rel="'.$pesquisa.'">
									<div class="td_btn_gravar_pergunta"></div>
							    </td>
							</tr>';
	}
	$html_pesquisa .= "</table>";
	echo $html_pesquisa;
include "rodape.php";?>
