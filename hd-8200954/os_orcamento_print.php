<?
	include "dbconfig.php";
	include "includes/dbconnect-inc.php";
	include "autentica_usuario.php";
	include "funcoes.php";

	$title = traduz("confirmacao.ordem.servico.orcamento", $con, $cook_idioma);

	$os_orcamento = trim($_GET['os_orcamento']);
	if(strlen($os_orcamento) > 0){
		$sql = "SELECT 
				tbl_os_orcamento.os_orcamento		,
				tbl_os_orcamento.consumidor_nome	,
				tbl_os_orcamento.consumidor_fone	,
				tbl_os_orcamento.consumidor_email	,
				tbl_produto.referencia				,
				tbl_produto.descricao				,
				tbl_os_orcamento.abertura			,
				tbl_os_orcamento.orcamento_envio		,
				tbl_os_orcamento.orcamento_aprovacao	,
				tbl_os_orcamento.orcamento_aprovado	,
				tbl_os_orcamento.conserto			,
				tbl_os_orcamento.data_digitacao		
			FROM 
				tbl_os_orcamento 
				JOIN tbl_produto ON (tbl_produto.produto = tbl_os_orcamento.produto)
			WHERE 
				tbl_os_orcamento.posto = $login_posto
				AND tbl_os_orcamento.fabrica = $login_fabrica
				AND tbl_os_orcamento.os_orcamento = $os_orcamento;";

		$res = pg_exec($con, $sql);
		if(pg_numrows($res) == 1){
			$consumidor_nome	= pg_fetch_result($res,0,consumidor_nome);
			$produto_referencia	= pg_fetch_result($res,0,referencia);
			$produto_descricao	= pg_fetch_result($res,0,descricao);
			$abertura			= mostra_data_hora(pg_fetch_result($res,0,abertura));
			$orcamento_envio	= mostra_data_hora(pg_fetch_result($res,0,orcamento_envio));
			$orcamento_aprovacao	= mostra_data_hora(pg_fetch_result($res,0,orcamento_aprovacao));
			$orcamento_aprovado	= pg_fetch_result($res,0,orcamento_aprovado);
			$conserto			= mostra_data_hora(pg_fetch_result($res,0,conserto));
			$consumidor_nome	= pg_fetch_result($res,0,consumidor_nome);
			$consumidor_fone		= pg_fetch_result($res,0,consumidor_fone);
			$consumidor_email	= pg_fetch_result($res,0,consumidor_email);
			$data_digitacao		= mostra_data_hora(pg_fetch_result($res,0,data_digitacao));
		}else{
			echo "<script>window.location.href='os_orcamento_consulta.php';</script>";
			exit;
		}
	}else{
		echo "<script>window.location.href='os_orcamento_consulta.php';</script>";
		exit;
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">

	<head>
		<link rel="stylesheet" type="text/css" href="css/lupas/lupas.css" media='all'>
		<title><?php echo $title;?></title>
		<style type="text/css" media='all'>
			.lp_tabela th{
				text-align: left;
				background: #CCC;
				color: #000;
				text-align: right;
				padding-right: 5px;
			}

			.lp_tabela th.title{
				background: #666;
				text-transform:uppercase;
				text-align: left;
				color: #FFF;
			}

			.lp_tabela{
				background: #000;
			}

			.lp_tabela td{
				background: #FCFCFC;
			}

			.os_orcamento{
				font-size: 28px;
				text-align: center;
				font-weight: 800;
				color: #000;
			}
		</style>
	</head>

	<body onload='javascript: window.print();'>
		<br>
		<table border='1' cellspacing='1' cellspading='0' style='width: 700px' class='lp_tabela'>
			<tr class='title'>
				<th colspan='6' style='text-align: center' class='title'><?php echo $title;?></th>
			</tr>
			<tr>
				<td rowspan='5' colspan='2' width='200px'><div class='os_orcamento' ><?php echo $os_orcamento;?><div></td>
			</tr>
			<tr>
				<th colspan='4' class='title'><?php echo traduz("datas.da.os", $con, $cook_idioma);?></th>
			</tr>
			<tr>
				<th><?php echo traduz("entrada", $con, $cook_idioma);?></th>
				<td><?php echo $abertura;?></td>
				<th><?php echo traduz("orcamento", $con, $cook_idioma);?></th>
				<td><?php echo $orcamento_envio;?></td>
			</tr>
			<tr>
				<th><?php echo traduz("aprovacao", $con, $cook_idioma);?></th>
				<td><?php echo $orcamento_aprovacao;?></td>
				<th><?php echo traduz("conserto", $con, $cook_idioma);?></th>
				<td><?php echo $conserto;?></td>
			</tr>
			<tr>
				<th><?php echo traduz("digitacao", $con, $cook_idioma);?></th>
				<td colspan='5'><?php echo $data_digitacao;?></td>
			</tr>
			<tr class='title'>
				<th colspan='6' style='text-align: center' class='title'><?php echo traduz("informacoes.do.produto", $con, $cook_idioma);?></th>
			</tr>
			<tr>
				<th><?php echo traduz("referencia.do.produto", $con, $cook_idioma);?></th>
				<td><?php echo $produto_referencia;?></td>
				<th><?php echo traduz("descricao.do.produto", $con, $cook_idioma);?></th>
				<td colspan='3'><?php echo $produto_descricao;?></td>
			</tr>
			<tr class='title'>
				<th colspan='6' style='text-align: center' class='title'><?php echo traduz("informacoes.sobre.consumidor", $con, $cook_idioma);?></th>
			</tr>
			<tr>
				<th><?php echo traduz("nome.consumidor", $con, $cook_idioma);?></th>
				<td colspan='5'><?php echo $consumidor_nome;?></td>
			</tr>
			<tr>
				<th><?php echo traduz("fone", $con, $cook_idioma);?></th>
				<td><?php echo $consumidor_fone;?></td>
				<th><?php echo traduz("email", $con, $cook_idioma);?></th>
				<td colspan='3'><?php echo $consumidor_email;?></td>
			</tr>
			<tr>
				<td colspan='6' style='text-align: center; padding: 40px 15px;'>
					<br><br>
					<div style='border-top: 1px solid #000; text-align: left;'><?php echo $consumidor_nome;?></div>
				</td>
			</tr>
		</table>

		<div style='margin: 30px 0'><hr  /></div>

		<table border='1' cellspacing='1' cellspading='0' style='width: 700px' class='lp_tabela'>
			<tr class='title'>
				<th colspan='6' style='text-align: center' class='title'><?php echo $title;?></th>
			</tr>
			<tr>
				<td rowspan='5' colspan='2' width='200px'><div class='os_orcamento' ><?php echo $os_orcamento;?><div></td>
			</tr>
			<tr>
				<th colspan='4' class='title'><?php echo traduz("datas.da.os", $con, $cook_idioma);?></th>
			</tr>
			<tr>
				<th><?php echo traduz("entrada", $con, $cook_idioma);?></th>
				<td><?php echo $abertura;?></td>
				<th><?php echo traduz("orcamento", $con, $cook_idioma);?></th>
				<td><?php echo $orcamento_envio;?></td>
			</tr>
			<tr>
				<th><?php echo traduz("aprovacao", $con, $cook_idioma);?></th>
				<td><?php echo $orcamento_aprovacao;?></td>
				<th><?php echo traduz("conserto", $con, $cook_idioma);?></th>
				<td><?php echo $conserto;?></td>
			</tr>
			<tr>
				<th><?php echo traduz("digitacao", $con, $cook_idioma);?></th>
				<td colspan='5'><?php echo $data_digitacao;?></td>
			</tr>
			<tr class='title'>
				<th colspan='6' style='text-align: center' class='title'><?php echo traduz("informacoes.do.produto", $con, $cook_idioma);?></th>
			</tr>
			<tr>
				<th><?php echo traduz("referencia.do.produto", $con, $cook_idioma);?></th>
				<td><?php echo $produto_referencia;?></td>
				<th><?php echo traduz("descricao.do.produto", $con, $cook_idioma);?></th>
				<td colspan='3'><?php echo $produto_descricao;?></td>
			</tr>
			<tr class='title'>
				<th colspan='6' style='text-align: center' class='title'><?php echo traduz("informacoes.sobre.consumidor", $con, $cook_idioma);?></th>
			</tr>
			<tr>
				<th><?php echo traduz("nome.consumidor", $con, $cook_idioma);?></th>
				<td colspan='5'><?php echo $consumidor_nome;?></td>
			</tr>
			<tr>
				<th><?php echo traduz("fone", $con, $cook_idioma);?></th>
				<td><?php echo $consumidor_fone;?></td>
				<th><?php echo traduz("email", $con, $cook_idioma);?></th>
				<td colspan='3'><?php echo $consumidor_email;?></td>
			</tr>
			<tr>
				<td colspan='6' style='text-align: center; padding: 40px 15px;'>
					<br><br>
					<div style='border-top: 1px solid #000; text-align: left;'><?php echo $consumidor_nome;?></div>
				</td>
			</tr>
		</table>
	</body>