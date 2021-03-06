<?php
	//Pagina desenvolvida para Bosch Security 96 - ESandre
	include 'dbconfig.php';
	include 'includes/dbconnect-inc.php';
	include 'autentica_usuario.php';
	
	$laudo = $_REQUEST['laudo'];

	if(strlen($laudo) == 0){
		echo "<h2>Laudo Inv�lido!</h2>";
		exit;
	}
	
	$sql = "SELECT os, texto, tipo_laudo FROM tbl_laudo WHERE laudo = $laudo;";
	$res = pg_exec($con,$sql);
	$os = pg_result($res,0,os);
	$texto_laudo = pg_result($res,0,texto);
	$tipo_laudo = pg_result($res,0,tipo_laudo);
	
	//verifica se este Laudo � do posto e da fabrica!
	$sql = "SELECT 
			tbl_os.os, 
			tbl_os.posto, 
			tbl_os.consumidor_nome, 
			tbl_os.sua_os,
			tbl_solucao.descricao
		FROM 
			tbl_os 
			LEFT JOIN tbl_solucao ON tbl_os.solucao_os = tbl_solucao.solucao
		WHERE 
			tbl_os.os = $os 
			AND tbl_os.posto = $login_posto 
			AND tbl_os.fabrica = $login_fabrica;";
	$res = pg_exec($con,$sql);
	
	if(pg_num_rows($res) == 0){
		echo "<h2>Voc� n�o possui acesso a este Laudo!</h2>";
		exit;
	}else{
		$posto = pg_result($res,0,posto);
		$consumidor_nome = pg_result($res,0,consumidor_nome);
		$sua_os = pg_result($res,0,sua_os);
		$solucao = pg_result($res,0,descricao);
	}
	
	$dir = "laudo/$login_fabrica/pdf/";
	if(!file_exists($dir))
		mkdir($dir, '777');
	
	if(file_exists($dir.$laudo.'.pdf')){
		$file = "laudo/$login_fabrica/pdf/$laudo.pdf";
		header("Location: $file");
		exit;
	}
	
	$sql = "UPDATE tbl_laudo SET data_impressao=NOW() WHERE laudo=$laudo AND data_impressao IS NULL RETURNING DATE(data_impressao) AS data_impressao; ";
	$res = pg_exec($con,$sql);
	$data_impresao = @pg_result($res,0,data_impressao);
	
 	$sql_produto = "SELECT 
					tbl_hd_chamado_item.serie AS serie												, 
					tbl_hd_chamado_item.defeito_reclamado_descricao  AS defeito_descricao,
					tbl_produto.referencia_fabrica AS referencia						, 
					tbl_produto.descricao AS descricao
				FROM 
					tbl_hd_chamado_item
					JOIN tbl_produto ON (tbl_produto.produto = tbl_hd_chamado_item.produto)  
				WHERE 
					tbl_hd_chamado_item.os = $os
					AND tbl_produto.fabrica_i = $login_fabrica;";
	$res_produto = pg_exec($con,$sql_produto);
	
	$serie = pg_result($res_produto,0,serie);
	$defeito_descricao = pg_result($res_produto,0,defeito_descricao);
	$referencia = pg_result($res_produto,0,referencia);
	$descricao = pg_result($res_produto,0,descricao);
	
	$orcamento = "<title>Laudo Assist�ncia T�cnica</title>";
 	$orcamento .="<style type='text/css' media='all'>
	*{
		font-family: Verdana, Tahoma, Helvetica, Arial;
	}

	div, p, table{
		font-size: 14px;
		width: 650px;
		margin: 15px auto;
		text-align: justify;
	}

	p{
		text-indent: 60px
	}


	body{
		text-align: center;
	}
	</style>";

	function buscaMes($mes= NULL){
		if($mes == NULL)
			$mes = Date('m');
		
		switch($mes){
			case 01  : $mes = "Janeiro"; break;
			case 02  : $mes = "Fevereiro"; break;
			case 03  : $mes = "Mar�o"; break;
			case 04  : $mes = "Abril"; break;
			case 05  : $mes = "Maio"; break;
			case 06  : $mes = "Junho"; break;
			case 07  : $mes = "Julho"; break;
			case 08  : $mes = "Agosto"; break;
			case 09  : $mes = "Setembro"; break;
			case 10  : $mes = "Outubro"; break;
			case 11  : $mes = "Novembro"; break;
			case 12  : $mes = "Dezembro"; break;
		}
		return $mes;
	}

	$orcamento .= "<table cellpadding='0' cellspacing='0' border='0' width='100%' align='center' style='margin: 0; margin: 0 auto;'>";
		$orcamento .="<tr>";
			$orcamento .="<td align='right' valign='top'>";		
				$orcamento .="<a href='http://www.bosch.com.br' target='_blank' title='Bosch' style='border: none;'><img src='imagens/96/20110413LogoBosch.jpg' width='200px; border: none'></a>";
			$orcamento .="</td>";
		$orcamento .="</tr>";
	$orcamento .="</table>";
	
	function DataBR($data){
		$data = explode('-',$data);

		return $data[2].'/'.$data[1].'/'.$data[0];
	}

	$orcamento .=	"<div style='padding: 0 30px; text-align: right'>Impresso em: ".Date('d/m/Y - H:i:s')."</div>";
	$orcamento .=	"<div>Campinas, ".Date('d')." de ".buscaMes()." de ".Date('Y')."</div>";
	
	$orcamento .=	"<div>
					A/C Sr. Rodrigo Gon�alves<br>
					Empresa: $consumidor_nome<br>
					Equipamento: $referencia - $descricao<br>
					S�rie: $serie<br>
					Solu��o: $solucao<br>
				</div>";

	$orcamento .="	<p>Informamos que o equipamento enviado para conserto � nossa assist�ncia t�cnica, foi catalogado respectivamente sob a Ordem de Servi�o n.� <b>$sua_os</b></p>";

	$orcamento .="	<p>$texto_laudo</p>";

	$imagem = "laudo/$login_fabrica/imagens/$laudo.jpg";
	$orcamento .="	<div style='text-align: center; margin: 15px auto;'>
					<img src='$imagem' alt='Bosch' />
				</div>";

	if(strlen($tipo_laudo) > 0){
		$sql = "SELECT texto FROM tbl_tipo_laudo WHERE fabrica = $login_fabrica AND tipo_laudo = $tipo_laudo;";
		$res = pg_exec ($con,$sql);
		if (pg_numrows($res) > 0){
			$texto = explode("<br>",pg_result($res,0,texto));
			foreach ($texto as $linha) 
				$orcamento .= "<p>".$linha."</p>";
		}
	}


	$sql = " SELECT nome_completo, email FROM tbl_admin WHERE admin = 3373 AND fabrica = $login_fabrica;";
	$res = pg_query($con,$sql);
	$nome_completo = pg_result($res,0,nome_completo);
	$email = strtolower(pg_result($res,0,email));

	$orcamento .="	<div style='margin-top: 10px;'>
					Sauda��es,

					<p style='padding: 0 20px; text-indent: 0'>
					<b>$nome_completo</b><br>
					Suporte T�cnico<br>
					Robert Bosch Ltda.<br>
					Sistemas de Seguran�a (ST/PD)<br>
					$email<br>
					</p>
				</div>";	

	
	require_once("pdf/dompdf/dompdf_config.inc.php");
	$dompdf = new DOMPDF();
	$dompdf->load_html($orcamento);
	$dompdf->set_paper("A4");
	$dompdf->render();
	
	$pdf = $dompdf->output();
	file_put_contents("$dir$laudo.pdf", $pdf);
	$file = "laudo/$login_fabrica/pdf/$laudo.pdf";
	header("location:$file");
	//$dompdf->stream("$laudo.pdf");
	
	echo $orcamento;
	?>