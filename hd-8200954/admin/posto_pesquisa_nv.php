<?php
	include "dbconfig.php";
	include "includes/dbconnect-inc.php";
	include 'autentica_admin.php';

	$codigo	= trim (strtolower ($_REQUEST['codigo']));
	$nome	= trim (strtolower ($_REQUEST['nome']));
	$nome = str_replace("'", "", $nome);
	$cnpj	= trim (strtolower ($_REQUEST['cnpj']));
	
	function verificaValorCampo($campo){
		return strlen($campo) > 0 ? $campo : "&nbsp;";
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv='pragma' content='no-cache'>
		<style type="text/css">
			body {
				margin: 0;
				font-family: Arial, Verdana, Times, Sans;
				background: #fff;
			}
		</style>
		<script type="text/javascript" src="../js/jquery-1.4.2.js"></script>
		<script src="../plugins/jquery/jquery.tablesorter.min.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="../css/lupas/lupas.css">
		<script type='text/javascript'>
			//fun��o para fechar a janela caso a telca ESC seja pressionada!
			$(window).keypress(function(e) { 
				if(e.keyCode == 27) { 
					 window.parent.Shadowbox.close();
				}
			});

			$(document).ready(function() {
				$("#gridRelatorio").tablesorter();
			}); 
		</script>
	</head>

	<body>
		<div class="lp_header">
			<a href='javascript:window.parent.Shadowbox.close();' style='border: 0;'>
				<img src='css/modal/excluir.png' alt='Fechar' class='lp_btn_fechar' />
			</a>
		</div>
		<?
			echo "<div class='lp_nova_pesquisa'>";
				echo "<form action='".$_SERVER["PHP_SELF"]."' method='POST' name='nova_pesquisa'>";
					echo "<input type='hidden' name='forma' value='$forma' />";
					echo "<table cellspacing='1' cellpadding='2' border='0'>";
						echo "<tr>";
							echo "<td>
								<label>C�digo do Posto</label>
								<input type='text' name='codigo' value='$codigo' style='width: 150px' maxlength='20' />
							</td>"; 
							echo "<td>
								<label>Nome</label>
								<input type='text' name='nome' value='$nome' style='width: 200px' maxlength='80' />
							</td>"; 
							echo "<td>
								<label>CNPJ</label>
								<input type='text' name='cnpj' value='$cnpj' style='width: 150px' maxlength='20' />
							</td>"; 
							echo "<td colspan='2' class='btn_acao' valign='bottom'><input type='submit' name='btn_acao' value='Pesquisar ' /></td>"; 
						echo "</tr>";
					echo "</table>";
				echo "</form>";
			echo "</div>";

		if (strlen($cnpj) > 2) {			
			/* $cnpj = preg_replace('([^0-9-])', '', $cnpj); */
			$cnpj = preg_replace('/[^0-9]/', '', $cnpj); /* retornar apenas d�gitos, sem h�fem: 01064811000119 */
			echo "<div class='lp_pesquisando_por'>Pesquisando pelo CNPJ: $cnpj</div>";

			$sql = "SELECT  tbl_posto.posto,
							tbl_posto_fabrica.codigo_posto,
							tbl_posto.nome,
							tbl_posto.cnpj,
							tbl_posto.pais,
							tbl_posto_fabrica.contato_cidade,
							tbl_posto_fabrica.contato_estado,
							tbl_posto_fabrica.nome_fantasia
						FROM tbl_posto
							JOIN tbl_posto_fabrica USING (posto)
						WHERE (tbl_posto.cnpj LIKE '%$cnpj%' OR tbl_posto_fabrica.codigo_posto LIKE '%$cnpj%')
							AND tbl_posto_fabrica.fabrica = $login_fabrica
						ORDER BY tbl_posto.nome LIMIT 200;";

		}elseif (strlen($codigo) > 2) {
			echo "<div class='lp_pesquisando_por'>Pesquisando pelo C�digo do Posto: $codigo</div>";
			$sql = "SELECT  tbl_posto.posto,
							tbl_posto_fabrica.codigo_posto,
							tbl_posto.nome,
							tbl_posto.cnpj,
							tbl_posto.pais,
							tbl_posto_fabrica.contato_cidade,
							tbl_posto_fabrica.contato_estado,
							tbl_posto_fabrica.nome_fantasia
						FROM tbl_posto
							JOIN tbl_posto_fabrica USING (posto)
						WHERE tbl_posto_fabrica.codigo_posto ILIKE '%$codigo%'
							AND tbl_posto_fabrica.fabrica = $login_fabrica
						ORDER BY tbl_posto.nome LIMIT 200;";
		}elseif (strlen($nome) > 2) {
			echo "<div class='lp_pesquisando_por'>Pesquisando pelo Nome do Posto: $nome</div>";
			$sql = "SELECT  tbl_posto.posto,
							tbl_posto_fabrica.codigo_posto,
							tbl_posto.nome,
							tbl_posto.cnpj,
							tbl_posto.pais,
							tbl_posto_fabrica.contato_cidade,
							tbl_posto_fabrica.contato_estado,
							tbl_posto_fabrica.nome_fantasia
						FROM tbl_posto
							JOIN tbl_posto_fabrica USING (posto)
						WHERE fn_retira_especiais(tbl_posto.nome) ~* fn_retira_especiais('$nome')
							AND tbl_posto_fabrica.fabrica = $login_fabrica
						ORDER BY tbl_posto.nome LIMIT 200;";
		}else{
			echo "<div class='lp_msg_erro'>Informar toda ou parte da informa��o para realizar a pesquisa!</div>";
			exit;
		}

		$res = pg_query($con, $sql);
		if (pg_numrows ($res) > 0 ) {?>
			<table width='100%' border='0' cellspacing='1' cellspading='0' class='lp_tabela' id='gridRelatorio'>
				<thead>
					<tr>
						<th>C�digo</th>
						<th>CNPJ</th>
						<th>Nome</th>
						<th>Cidade</th>
						<th>Estado</th>
					</tr>
				</thead>
				<tbody>
				<?php
					for ($i = 0 ; $i < pg_num_rows($res); $i++) {
						$posto			= trim(pg_result($res,$i,posto));
						$codigo_posto	=trim(pg_result($res,$i,codigo_posto));
						$nome			= trim(pg_result($res,$i,nome));
						$nome = str_replace("'", " ", $nome);
						$cnpj			= trim(pg_result($res,$i,cnpj));
						$pais			= trim(pg_result($res,$i,pais));
						$cidade			= trim(pg_result($res,$i,contato_cidade));
						$estado			= trim(pg_result($res,$i,contato_estado));
						$nome_fantasia	= trim(pg_result($res,$i,nome_fantasia));
						$nome_fantasia = str_replace("'", " ", $nome_fantasia);
						
						if(pg_num_rows($res) == 1){
							echo "<script type='text/javascript'>";
								echo "window.parent.retorna_posto('$posto','$codigo_posto','$nome','$cnpj','$pais','$cidade','$estado','$nome_fantasia'); window.parent.Shadowbox.close();";
							echo "</script>";
							exit;
						}

						$onclick = "onclick= \"javascript: window.parent.retorna_posto('$posto','$codigo_posto','$nome','$cnpj','$pais','$cidade','$estado','$nome_fantasia'); window.parent.Shadowbox.close();\"";

						$cor = ($i % 2) ? "#F7F5F0" : "#F1F4FA";
						echo "<tr style='background: $cor' $onclick>";
							echo "<td>".verificaValorCampo($codigo_posto)."</td>";
							echo "<td>".verificaValorCampo($cnpj)."</td>";
							echo "<td>".verificaValorCampo($nome)."</td>";
							echo "<td>".verificaValorCampo($cidade)."</td>";
							echo "<td>".verificaValorCampo($estado)."</td>";
						echo "</tr>";
				}
			}else{
		echo "<div class='lp_msg_erro'>Nenhum resultado encontrado</div>";
	}?>
	</body>
</html>
