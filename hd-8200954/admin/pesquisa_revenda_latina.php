<?
	include "dbconfig.php";
	include "includes/dbconnect-inc.php";
	include 'autentica_admin.php';

	function verificaValorCampo($campo){
		return strlen($campo) > 0 ? $campo : "&nbsp;";
	}

	$forma 	   = trim($_REQUEST['forma']);
	$tipo      = trim($_REQUEST['tipo']);
	$$tipo     = strtoupper(trim($_REQUEST[$tipo]));
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
		<script type="text/javascript" src="js/jquery-1.4.2.js"></script>
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
			$label = ($tipo == 'nome' ? 'Nome Fantasia / Raz�o Social' : ($tipo == 'cnpj' ? 'CNPJ' : ''));
			echo "<div class='lp_nova_pesquisa'>";
				echo "<form action='".$_SERVER["PHP_SELF"]."' method='POST' name='nova_pesquisa'>";
					echo "<input type='hidden' name='forma' value='$forma' />";
					echo "<input type='hidden' name='tipo'  value='$tipo' />";
					echo "<table cellspacing='1' cellpadding='2' border='0'>";
						echo "<tr>";
							echo "<td>
								<label>$label</label>
								<input type='text' name='descricao' value='$descricao' style='width: 370px' maxlength='80' />
							</td>";
							echo "<td colspan='2' class='btn_acao' valign='bottom'><input type='submit' name='btn_acao' value='Pesquisar Novamente' /></td>";
						echo "</tr>";
					echo "</table>";
				echo "</form>";
			echo "</div>";

	if ($tipo == 'nome') {
		$nome = $$tipo;
		echo "<div class='lp_pesquisando_por'>Pesquisando pelo Nome Fantasia / Raz�o Social: $descricao</div>";

		$sql = "SELECT DISTINCT lpad(tbl_revenda_fabrica.cnpj, 14, '0') AS cnpj ,
				tbl_revenda_fabrica.ie                         ,
				CASE
					WHEN UPPER(contato_razao_social) LIKE UPPER('%$nome%') THEN
						tbl_revenda_fabrica.contato_razao_social
					ELSE
						tbl_revenda_fabrica.contato_nome_fantasia
				END AS nome_revenda                            ,
				tbl_revenda_fabrica.contato_razao_social       ,
				tbl_revenda_fabrica.contato_nome_fantasia      ,
				tbl_revenda_fabrica.revenda                    ,
				tbl_revenda_fabrica.cidade                     ,
				tbl_revenda_fabrica.contato_fone               ,
				tbl_revenda_fabrica.contato_fax                ,
				tbl_revenda_fabrica.contato_endereco           ,
				tbl_revenda_fabrica.contato_numero             ,
				tbl_revenda_fabrica.contato_complemento        ,
				tbl_revenda_fabrica.contato_bairro             ,
				tbl_revenda_fabrica.contato_cep                ,
				tbl_revenda_fabrica.contato_email              ,
				tbl_revenda_fabrica.contato_nome AS contato    ,
				tbl_cidade.nome         AS nome_cidade ,
				tbl_cidade.estado
			FROM tbl_revenda_fabrica
				LEFT JOIN tbl_cidade USING (cidade)
			WHERE tbl_revenda_fabrica.fabrica = $login_fabrica
			AND	(tbl_revenda_fabrica.contato_razao_social ILIKE '%$nome%' OR tbl_revenda_fabrica.contato_nome_fantasia ILIKE '%$nome%')

			ORDER BY    tbl_cidade.estado,tbl_cidade.nome,tbl_revenda_fabrica.contato_bairro";

	}elseif($tipo == 'cnpj'){
		$cnpj = $$tipo;
		$cnpj = preg_replace('/\D/', '', trim($cnpj));
		echo "<div class='lp_pesquisando_por'>Pesquisando pelo CNPJ: $cnpj</div>";

		$cnpj = strtoupper ($cnpj);
		$cnpj = str_replace ("-","",$cnpj);
		$cnpj = str_replace (".","",$cnpj);
		$cnpj = str_replace ("/","",$cnpj);
		$cnpj = str_replace (" ","",$cnpj);

		$sql = "SELECT DISTINCT lpad(tbl_revenda_fabrica.cnpj, 14, '0') AS cnpj ,
				tbl_revenda_fabrica.ie                         ,
				1 AS nome_revenda                              ,
				tbl_revenda_fabrica.contato_razao_social       ,
				tbl_revenda_fabrica.contato_nome_fantasia      ,
				tbl_revenda_fabrica.revenda                    ,
				tbl_revenda_fabrica.cidade                     ,
				tbl_revenda_fabrica.contato_fone               ,
				tbl_revenda_fabrica.contato_fax                ,
				tbl_revenda_fabrica.contato_endereco           ,
				tbl_revenda_fabrica.contato_numero             ,
				tbl_revenda_fabrica.contato_complemento        ,
				tbl_revenda_fabrica.contato_bairro             ,
				tbl_revenda_fabrica.contato_cep                ,
				tbl_revenda_fabrica.contato_email              ,
				tbl_revenda_fabrica.contato_cidade             ,
				tbl_revenda_fabrica.contato_nome AS contato    ,
				tbl_cidade.nome         AS nome_cidade         ,
				tbl_cidade.estado
			FROM tbl_revenda_fabrica
				LEFT JOIN tbl_cidade USING (cidade)
			WHERE tbl_revenda_fabrica.fabrica = $login_fabrica
			AND	tbl_revenda_fabrica.cnpj ILIKE '%$cnpj%'

			ORDER BY tbl_revenda_fabrica.contato_razao_social";
	}else{
		echo "<div class='lp_msg_erro'>Informar toda ou parte da informa��o para realizar a pesquisa!</div>";
		exit;
	}

	$res = pg_query($con, $sql);
	if (pg_numrows ($res) > 0 ) {?>
		<table width='100%' border='0' cellspacing='1' cellspading='0' class='lp_tabela' id='gridRelatorio'>
			<thead>
				<tr>
					<th>Raz�o Social</th>
					<th>Nome Fantasia</th>
				</tr>
			</thead>
			<tbody>
				<?

				for ($i = 0 ; $i < pg_num_rows($res); $i++) {
					$revenda    = trim(pg_result($res,$i,revenda));
					$descricao		= trim(pg_result($res,$i,contato_razao_social));
					$descricao_fantasia	= trim(pg_result($res,$i,contato_nome_fantasia));
					$descricao_revenda	= trim(pg_result($res,$i,nome_revenda));
					$cnpj		= trim(pg_result($res,$i,cnpj));
					$cidade		= trim(pg_result($res,$i,nome_cidade));
					$fone		= trim(pg_result($res,$i,contato_fone));
					$fax		= trim(pg_result($res,$i,contato_fax));
					$endereco	= trim(pg_result($res,$i,contato_endereco));
					$numero		= trim(pg_result($res,$i,contato_numero));
					$complemento	= trim(pg_result($res,$i,contato_complemento));
					$bairro		= trim(pg_result($res,$i,contato_bairro));
					$cep		= trim(pg_result($res,$i,contato_cep));
					$estado		= trim(pg_result($res,$i,estado));
					$email		= trim(pg_result($res,$i,contato_email));
					$ie  		= trim(pg_result($res,$i,ie));
					$contato  		= trim(pg_result($res,$i,contato));
					$cnpj_raiz  = trim(substr($cnpj,0,8));

					$descricao_revenda = ($descricao_revenda == 1) ? $descricao : $descricao_revenda;

					if(pg_num_rows($res) == 1){

						echo "<script type='text/javascript'>";
							echo "window.parent.retorna_revenda('$revenda','$descricao_revenda','$descricao_fantasia','$cnpj','$ie','$cidade','$fone','$fax','$contato','$endereco','$numero','$complemento','$bairro','$cep','$estado','$email','$cnpj_raiz'); window.parent.Shadowbox.close();";
						echo "</script>";

					}

					$onclick = "onclick= \"javascript: window.parent.retorna_revenda('$revenda','$descricao_revenda','$descricao_fantasia','$cnpj','$ie','$cidade','$fone','$fax','$contato','$endereco','$numero','$complemento','$bairro','$cep','$estado','$email','$cnpj_raiz'); window.parent.Shadowbox.close();\"";

					$cor = ($i % 2) ? "#F7F5F0" : "#F1F4FA";
					echo "<tr style='background: $cor' $onclick>";
						echo "<td>".verificaValorCampo($descricao)."</td>";
						echo "<td>".verificaValorCampo($descricao_fantasia)."</td>";
					echo "</tr>";
				}
			echo "</tbody>";
		echo "</table>";

	}else{
		echo "<div class='lp_msg_erro'>Nehum resultado encontrado</div>";
	}?>
	</body>
</html>