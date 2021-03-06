<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';

$admin_privilegios="call_center";
include 'autentica_admin.php';
include 'funcoes.php';

$qtde_itens = 30;

if (strlen($_POST['os']) > 0) $os = trim($_POST['os']);
else $os = trim($_GET['os']);

$os_produto = trim($_POST['os_produto']);

$troca_faturada = trim($_POST['troca_faturada']);

$btn_acao = $_POST['btn_acao'];

$msg_erro = "";

if ($btn_acao == "gravar") {

	##### N�O � TROCA FATURADA #####
	if (strlen($troca_faturada) == 0) {

		for ($i = 0 ; $i < $qtde_itens ; $i++) {
			$qtde = trim($_POST["peca_qtde_".$i]);
			$peca_referencia = trim($_POST["peca_referencia_".$i]);

			if (strlen($peca_referencia) > 0 AND strlen($qtde) > 0) $msg_erro_pq = "QP";
			if (strlen($peca_referencia) > 0 AND strlen($qtde) == 0) $msg_erro_q = "Q";

		}

		//if ($msg_erro_pq <> "QP" AND $tipo_os_cortesia <> 'Promotor') $msg_erro .= " Digite a pe�a e a quantidade.";
		//if ($msg_erro_q == "Q") $msg_erro .= " Digite a quantidade da pe�a.";

		if (strlen($msg_erro) == 0) {

			$res = pg_exec ($con,"BEGIN TRANSACTION");

			$produto_referencia = trim($_POST['produto_referencia']);
			$produto_referencia = str_replace("-","",$produto_referencia);
			$produto_referencia = str_replace(" ","",$produto_referencia);
			$produto_referencia = str_replace("/","",$produto_referencia);
			$produto_referencia = str_replace(".","",$produto_referencia);

			$produto_voltagem   = trim($_POST['produto_voltagem']);

			if (strlen($produto_referencia) > 0) {
				$sql =	"SELECT tbl_produto.produto
						FROM tbl_produto
						JOIN tbl_linha USING (linha)
						WHERE UPPER(trim(tbl_produto.referencia_pesquisa)) = UPPER(trim('$produto_referencia'))
						AND UPPER(trim(tbl_produto.voltagem)) = UPPER(trim('$produto_voltagem'))
						AND tbl_linha.fabrica = $login_fabrica;";
				$res      = pg_exec($con,$sql);
				if (pg_numrows($res) > 0) $produto = pg_result($res,0,produto);
			}

			if (strlen($os_produto) > 0) {
				for ($i = 0 ; $i < $qtde_itens ; $i++) {
					$peca_referencia = trim($_POST["peca_referencia_".$i]);
					$peca_referencia = str_replace("." , "" , $peca_referencia);
					$peca_referencia = str_replace("-" , "" , $peca_referencia);
					$peca_referencia = str_replace("/" , "" , $peca_referencia);
					$peca_referencia = str_replace(" " , "" , $peca_referencia);
					$qtde            = trim($_POST["peca_qtde_".$i]);
					$defeito         = trim($_POST["defeito_".$i]);
					$servico         = trim($_POST["servico_".$i]);

					if (strlen($peca_referencia) > 0) {
						$sql =	"SELECT tbl_peca.peca
								FROM  tbl_peca
								JOIN  tbl_lista_basica USING (peca)
								WHERE UPPER(tbl_peca.referencia_pesquisa) = UPPER('$peca_referencia')
								AND   tbl_lista_basica.produto   = $produto
								AND   tbl_lista_basica.fabrica   = $login_fabrica";
						$res = pg_exec ($con,$sql);

						if (pg_numrows($res) > 0) {
							$peca = pg_result ($res,0,0);
						}else{
							$msg_erro .= " Pe�a ".trim($_POST["peca_referencia_".$i])." n�o est� na lista b�sica do produto ".trim($_POST["produto_referencia"]).".";
						}
					}

					if (strlen($msg_erro) > 0) break;
				}
			}

			if (strlen($msg_erro) == 0) {
				for ($i = 0 ; $i < $qtde_itens ; $i++) {
					$os_item         = trim($_POST["os_item_".$i]);
					$peca_referencia = trim($_POST["peca_referencia_".$i]);
					$peca_referencia = str_replace("." , "" , $peca_referencia);
					$peca_referencia = str_replace("-" , "" , $peca_referencia);
					$peca_referencia = str_replace("/" , "" , $peca_referencia);
					$peca_referencia = str_replace(" " , "" , $peca_referencia);
					$qtde            = trim($_POST["peca_qtde_".$i]);
					$defeito         = trim($_POST["defeito_".$i]);
					$servico         = trim($_POST["servico_".$i]);

					if (strlen($defeito) == 0) $defeito = 'null';
					if (strlen($servico) == 0) $servico = 'null';

					if (strlen($peca_referencia) > 0) {
						$sql =	"SELECT tbl_peca.peca
								FROM  tbl_peca
								JOIN  tbl_lista_basica USING (peca)
								WHERE UPPER(trim(tbl_peca.referencia_pesquisa)) = UPPER(trim('$peca_referencia'))
								AND   tbl_lista_basica.produto   = $produto
								AND   tbl_lista_basica.fabrica   = $login_fabrica";
						$res = pg_exec ($con,$sql);

						if (pg_numrows($res) > 0) {
							$peca = pg_result($res,0,0);
						}else{
							$msg_erro .= " Pe�a $peca_referencia n�o cadastrada.";
						}
					}

					if (strlen($msg_erro) > 0) break;

					if (strlen($os_item) > 0 AND strlen($peca_referencia) == 0 AND strlen($msg_erro) == 0) {
						$sql = "DELETE FROM tbl_os_item
								WHERE os_item = $os_item
								AND   os_produto = $os_produto";
						$res = pg_exec($con,$sql);
						$msg_erro = pg_errormessage($con);
						$msg_erro = substr($msg_erro,6);
					}

					if (strlen($msg_erro) > 0) break;

					if (strlen($peca) > 0 AND strlen($qtde) > 0 AND strlen($msg_erro) == 0) {
						if (strlen($os_item) == 0) {
							$sql =	"INSERT INTO tbl_os_item (
											os_produto        ,
											peca              ,
											qtde              ,
											defeito           ,
											servico_realizado
										) VALUES (
											$os_produto ,
											$peca       ,
											$qtde       ,
											$defeito    ,
											$servico
										);";
						}else{
							$sql =	"UPDATE tbl_os_item SET
											peca              = $peca    ,
											qtde              = $qtde    ,
											defeito           = $defeito ,
											servico_realizado = $servico
									WHERE os_item    = $os_item
									AND   os_produto = $os_produto";
						}

						$res = pg_exec($con,$sql);
						$msg_erro = pg_errormessage($con);
						$msg_erro = substr($msg_erro,6);
					}

					if (strlen($msg_erro) > 0) break;

					$os_item = '';
					$peca_referencia = '';
					$peca = '';
					$qtde = '';
					$defeito = '';
					$servico = '';
				}
			}

			if (strlen ($msg_erro) == 0) {
				if (strlen($os) == 0) {
					$res = pg_exec ($con,"SELECT CURRVAL ('seq_os')");
					$os  = pg_result ($res,0,0);
				}
				$res      = pg_exec ($con,"SELECT fn_valida_os($os, $login_fabrica)");
				$msg_erro = pg_errormessage($con);
				$msg_erro = substr($msg_erro,6);
			}

			$x_solucao_os = $_POST['solucao_os'];
			if (strlen ($msg_erro) == 0 and strlen($x_solucao_os) > 0) {
				$sql = "UPDATE tbl_os SET solucao_os = $x_solucao_os
						WHERE  tbl_os.os    = $os";
				$res = @pg_exec($con,$sql);
				$msg_erro = pg_errormessage($con);
			}
			
			$x_defeito_constatado = $_POST['defeito_constatado'];
			if (strlen ($msg_erro) == 0 and strlen($x_defeito_constatado) > 0) {
				$sql = "UPDATE tbl_os SET defeito_constatado = $x_defeito_constatado
						WHERE  tbl_os.os    = $os";
				$res = @pg_exec($con,$sql);
				$msg_erro = pg_errormessage($con);
			}
			
			if (strlen ($msg_erro) == 0) {
				$res = pg_exec($con,"COMMIT TRANSACTION");
				header ("Location: os_finalizada.php?os=$os");
				exit;
			}else{
				$res = pg_exec($con,"ROLLBACK TRANSACTION");
			}
		}

	}else{
		##### � TROCA FATURADA #####

		$x_motivo_troca = trim ($_POST['motivo_troca']);
		if (strlen($x_motivo_troca) == 0) $x_motivo_troca = "null";

		$resX = pg_exec ($con,"BEGIN TRANSACTION");

		$sql =	"UPDATE tbl_os SET
						motivo_troca  = $x_motivo_troca
				WHERE  tbl_os.os      = $os
				and    tbl_os.fabrica = $login_fabrica;";
		$res = @pg_exec ($con,$sql);

		if (strlen (pg_errormessage($con)) > 0) {
			$res = pg_exec($con,"ROLLBACK TRANSACTION");
			$msg_erro = pg_errormessage ($con);
		}

		if (strlen($msg_erro) == 0) {
				$resX = pg_exec ($con,"COMMIT TRANSACTION");
				header ("Location: os_finalizada.php?os=$os");
				exit;
		}
	}
}

if (strlen($_GET['os']) > 0) {
	$sql =	"SELECT tbl_os.os                                                   ,
					tbl_os.sua_os                                               ,
					tbl_os.posto                                                ,
					to_char(tbl_os.data_abertura,'DD/MM/YYYY') AS data_abertura ,
					tbl_os.fabrica                                              ,
					tbl_os.admin                                                ,
					tbl_os.produto                                              ,
					tbl_os.serie                                                ,
					tbl_os.codigo_fabricacao                                    ,
					tbl_os.consumidor_nome                                      ,
					tbl_os.consumidor_cpf                                       ,
					tbl_os.nota_fiscal                                          ,
					to_char(tbl_os.data_nf,'DD/MM/YYYY')       AS data_nf       ,
					tbl_os.tipo_os_cortesia                                     ,
					tbl_os.troca_faturada                                       ,
					tbl_os.motivo_troca                                         ,
					tbl_os.solucao_os                                           ,
					tbl_os.defeito_constatado                                   ,
					tbl_os_produto.os_produto                                   ,
					tbl_os_produto.versao                                       ,
					tbl_produto.referencia                                      ,
					tbl_produto.descricao                                       ,
					tbl_produto.voltagem                                        ,
					tbl_posto_fabrica.codigo_posto                              ,
					tbl_posto_fabrica.reembolso_peca_estoque                    
			FROM	tbl_os
			JOIN	tbl_os_produto USING (os)
			JOIN	tbl_produto ON tbl_os.produto  = tbl_produto.produto
			JOIN	tbl_posto   ON tbl_posto.posto = tbl_os.posto
			JOIN	tbl_posto_fabrica	ON  tbl_posto.posto           = tbl_posto_fabrica.posto
										AND tbl_posto_fabrica.fabrica = $login_fabrica
			WHERE	tbl_os.os      = $os
			AND		tbl_os.fabrica = $login_fabrica";
	$res = pg_exec($con,$sql);

	if (pg_numrows($res) > 0) {
		$os                 = pg_result($res,0,os);
		$sua_os             = pg_result($res,0,sua_os);
		$sua_os             = substr($sua_os, strlen($sua_os)-5, strlen($sua_os));
		$posto              = pg_result($res,0,posto);
		$data_abertura      = pg_result($res,0,data_abertura);
		$fabrica            = pg_result($res,0,fabrica);
		$admin              = pg_result($res,0,admin);
		$produto            = pg_result($res,0,produto);
		$produto_serie      = pg_result($res,0,serie);
		$codigo_fabricacao  = pg_result($res,0,codigo_fabricacao);
		$consumidor_nome    = pg_result($res,0,consumidor_nome);
		$consumidor_cpf     = pg_result($res,0,consumidor_cpf);
		$nota_fiscal        = pg_result($res,0,nota_fiscal);
		$data_nf            = pg_result($res,0,data_nf);
		$os_produto         = pg_result($res,0,os_produto);
		$tipo_os_cortesia   = pg_result($res,0,tipo_os_cortesia);
		$troca_faturada     = pg_result($res,0,troca_faturada);
		$motivo_troca       = pg_result($res,0,motivo_troca);
		$solucao_os         = pg_result($res,0,solucao_os);
		$defeito_constatado = pg_result($res,0,defeito_constatado);
		$produto_referencia = pg_result($res,0,referencia);
		$produto_descricao  = pg_result($res,0,descricao);
		$produto_voltagem   = pg_result($res,0,voltagem);
		$posto_codigo       = pg_result($res,0,codigo_posto);
		$produto_type       = pg_result($res,0,versao);
		$login_reembolso_peca_estoque = trim (pg_result ($res,0,reembolso_peca_estoque));
	}
}

if (strlen($msg_erro) > 0) {
	$motivo_troca = trim($_POST["motivo_troca"]);
}

$title = "Cadastro de Ordem de Servi�o do Tipo Cortesia - ADMIN";
$layout_menu = 'callcenter';
include "cabecalho.php";
?>

<script>
function fnc_pesquisa_peca_lista (produto_referencia, peca_referencia, peca_descricao, peca_preco, tipo) {
	var url = "";

	if (tipo == "referencia") {
		url = "peca_pesquisa_lista.php?produto=" + produto_referencia + "&peca=" + peca_referencia.value + "&voltagem=" + document.frm_os.produto_voltagem.value + "&tipo=" + tipo ;
	}

	if (tipo == "descricao") {
		url = "peca_pesquisa_lista.php?produto=" + produto_referencia + "&descricao=" + peca_descricao.value + "&voltagem=" + document.frm_os.produto_voltagem.value + "&tipo=" + tipo ;
	}

	if (peca_referencia.value.length >= 4 || peca_descricao.value.length >= 4) {
		janela = window.open(url, "janela", "toolbar=no, location=yes, status=yes, scrollbars=yes, directories=no, width=502, height=400, top=18, left=0");
		janela.produto		= produto_referencia;
		janela.referencia	= peca_referencia;
		janela.descricao	= peca_descricao;
		janela.preco		= peca_preco;
		janela.focus();
	}else{
		alert("Digite pelo menos 4 caracteres!");
	}
}
</script>

<? if (strlen ($msg_erro) > 0) { ?>
<br>
<table border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff" width = '730'>
<tr>
	<td valign="middle" align="center" class='error'>
<?
	// Retira palavra ERROR:
	if (strpos($msg_erro,"ERROR: ") !== false) {
		$msg_erro = substr($msg_erro, 6);
	}
	echo "Foi detectado o seguinte erro:<br>".$msg_erro;
?>
	</td>
</tr>
</table>
<? } ?>

<form name="frm_os" method="post" action="<? echo $PHP_SELF ?>">
<input type="hidden" name="os" value="<? echo $os; ?>">
<input type="hidden" name="os_produto" value="<? echo $os_produto; ?>">
<input type="hidden" name="produto_referencia" value="<? echo $produto_referencia; ?>">
<input type="hidden" name="produto_voltagem" value="<? echo $produto_voltagem; ?>">
<table border="0" cellpadding="2" cellspacing="0" align="center" width="750">
	<tr valign="top" align="left">
<? if (strlen($os) > 0) { ?>
		<td>
			<input type="hidden" name="sua_os" value="<? echo $sua_os; ?>">
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">OS Fabricante</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $posto_codigo.$sua_os; ?></B></font>
		</td>
<? } ?>
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">C�digo do Posto</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $posto_codigo ?></B></font>
		</td>
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">Data de Abertura</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? if (strlen($data_abertura) == 0) $data_abertura = date("d/m/Y"); echo $data_abertura; ?></B></font>
		</td>
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">Tipo da OS cortesia</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $tipo_os_cortesia; ?></B></font>
		</td>
	</tr>
</table>

<br>

<table border="0" cellpadding="2" cellspacing="0" align="center" width="750">
	<tr valign="top" align="left">
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">Produto</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $produto_referencia." - ".$produto_descricao; ?></B></font>
		</td>
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">Voltagem do Produto</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $produto_voltagem ?></B></font>
		</td>
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">Tipo</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $produto_type; ?></B></font>
		</td>
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">N� de S�rie</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $produto_serie ?></B></font>
		</td>
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">C�digo fabrica��o</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $codigo_fabricacao ?></B></font>
		</td>
	</tr>
</table>

<br>

<table border="0" cellpadding="2" cellspacing="0" align="center" width="750">
	<tr valign="top" align="left">
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">Nome Consumidor</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $consumidor_nome ?></B></font>
		</td>
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">CPF/CNPJ Consumidor</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $consumidor_cpf ?></B></font>
		</td>
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">Nota Fiscal</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $nota_fiscal ?></B></font>
		</td>
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">Data Compra</font>
			<br>
			<font size="2" face="Geneva, Arial, Helvetica, san-serif"><B><? echo $data_nf ?></B></font>
		</td>
	</tr>
</table>


<table align="center" width="750" border="0" cellspacing="5" cellpadding="0">
<tr>
	<td align="left" nowrap>
		<font size="1" face="Geneva, Arial, Helvetica, san-serif">Solu��o</font>
		<br>
		<select name="solucao_os" size="1" class="frm">
			<option value=""></option>
		<?
		$sql = "SELECT *
				FROM   tbl_servico_realizado
				WHERE  tbl_servico_realizado.fabrica = $login_fabrica ";

		if ($login_pede_peca_garantia == 't' AND $login_fabrica <> 1) {
			$sql .= "AND tbl_servico_realizado.descricao NOT ILIKE 'troca%' ";
		}

		if ($login_fabrica == 1) {
			if ($login_reembolso_peca_estoque == 't') {
				$sql .= "AND (tbl_servico_realizado.descricao NOT ILIKE 'troca%' ";
				$sql .= "OR tbl_servico_realizado.descricao ILIKE 'subst%') ";
				if (strlen($linha) > 0) $sql .= " AND (tbl_servico_realizado.linha = '$linha' OR tbl_servico_realizado.linha is null) ";
			}else{
				$sql .= "AND (tbl_servico_realizado.descricao ILIKE 'troca%' ";
				$sql .= "OR tbl_servico_realizado.descricao NOT ILIKE 'subst%') ";
				if (strlen($linha) > 0) $sql .= " AND (tbl_servico_realizado.linha = '$linha' OR tbl_servico_realizado.linha is null) ";
			}
		}

		$sql .= " AND tbl_servico_realizado.ativo IS TRUE ORDER BY descricao ";
		$res = pg_exec ($con,$sql) ;

		if (pg_numrows($res) == 0) {
			$sql = "SELECT *
					FROM   tbl_servico_realizado
					WHERE  tbl_servico_realizado.fabrica = $login_fabrica ";

			if ($login_pede_peca_garantia == 't' AND $login_fabrica <> 1) {
				$sql .= "AND tbl_servico_realizado.descricao NOT ILIKE 'troca%' ";
			}

			if ($login_fabrica == 1) {
				if ($login_reembolso_peca_estoque == 't') {
					$sql .= "AND (tbl_servico_realizado.descricao NOT ILIKE 'troca%' ";
					$sql .= "OR tbl_servico_realizado.descricao ILIKE 'subst%') ";
				}else{
					$sql .= "AND (tbl_servico_realizado.descricao ILIKE 'troca%' ";
					$sql .= "OR tbl_servico_realizado.descricao NOT ILIKE 'subst%') ";
				}
			}

			$sql .=	" AND tbl_servico_realizado.linha IS NULL
					AND tbl_servico_realizado.ativo IS TRUE ORDER BY descricao ";
			$res = pg_exec ($con,$sql) ;
		}

		for ($x = 0 ; $x < pg_numrows($res) ; $x++ ) {
			echo "<option ";
			if ($solucao_os == pg_result ($res,$x,servico_realizado)) echo " selected ";
			echo " value='" . pg_result ($res,$x,servico_realizado) . "'>" ;
			echo pg_result ($res,$x,descricao) ;
			if (pg_result ($res,$x,gera_pedido) == 't' AND $login_fabrica == 6) echo " - GERA PEDIDO DE PE�A ";
			echo "</option>";
		}
		?>
		</select>
	</td>
</tr>
</table>
<table align="center" width="750" border="0" cellspacing="5" cellpadding="0">
<tr>
	<td align="left" nowrap>
		<font size="1" face="Geneva, Arial, Helvetica, san-serif">Defeito Constatado</font>
		<br>
		<select name="defeito_constatado" size="1" class="frm">
			<option value=""></option>
			<?
			$sql = "SELECT defeito_constatado_por_familia, defeito_constatado_por_linha FROM tbl_fabrica WHERE fabrica = $login_fabrica";
# if ($ip == '201.0.9.216') echo "<br>".nl2br($sql)."<br>";
				$res = pg_exec ($con,$sql);
				$defeito_constatado_por_familia = pg_result ($res,0,0) ;
				$defeito_constatado_por_linha   = pg_result ($res,0,1) ;

				if ($defeito_constatado_por_familia == 't') {
					$sql = "SELECT familia FROM tbl_produto WHERE produto = $produto";
# if ($ip == '201.0.9.216') echo "<br>".nl2br($sql)."<br>";
					$res = pg_exec ($con,$sql);
					$familia = pg_result ($res,0,0) ;

					if ($login_fabrica == 1){

						$sql = "SELECT tbl_defeito_constatado.* FROM tbl_familia  JOIN   tbl_familia_defeito_constatado USING(familia) JOIN   tbl_defeito_constatado USING(defeito_constatado) ";
						if ($linha == 198) $sql .= " JOIN tbl_produto_defeito_constatado USING(defeito_constatado) ";
						$sql .= " WHERE  tbl_defeito_constatado.fabrica = $login_fabrica AND tbl_familia_defeito_constatado.familia = $familia";
						if ($consumidor_revenda == 'C' AND $login_fabrica == 1) $sql .= " AND tbl_defeito_constatado.codigo <> 1 ";
						if ($linha == 198) $sql .= " AND tbl_produto_defeito_constatado.produto = $produto ";
						$sql .= " ORDER BY tbl_defeito_constatado.descricao";
					}else{
						$sql = "SELECT tbl_defeito_constatado.*
								FROM   tbl_familia
								JOIN   tbl_familia_defeito_constatado USING(familia)
								JOIN   tbl_defeito_constatado         USING(defeito_constatado)
								WHERE  tbl_defeito_constatado.fabrica         = $login_fabrica
								AND    tbl_familia_defeito_constatado.familia = $familia";
						if ($consumidor_revenda == 'C' AND $login_fabrica == 1) $sql .= " AND tbl_defeito_constatado.codigo <> 1 ";
						$sql .= " ORDER BY tbl_defeito_constatado.descricao";
					}
				}else{

					if ($defeito_constatado_por_linha == 't') {
						$sql   = "SELECT linha FROM tbl_produto WHERE produto = $produto";
						$res   = pg_exec ($con,$sql);
						$linha = pg_result ($res,0,0) ;

						$sql = "SELECT tbl_defeito_constatado.*
								FROM   tbl_defeito_constatado
								JOIN   tbl_linha USING(linha)
								WHERE  tbl_defeito_constatado.fabrica         = $login_fabrica
								AND    tbl_linha.linha = $linha";
						if ($consumidor_revenda == 'C' AND $login_fabrica == 1) $sql .= " AND tbl_defeito_constatado.codigo <> 1 ";
						$sql .= " ORDER BY tbl_defeito_constatado.descricao";
					}else{
						$sql = "SELECT tbl_defeito_constatado.*
							FROM   tbl_defeito_constatado
							WHERE  tbl_defeito_constatado.fabrica = $login_fabrica";
						if ($consumidor_revenda == 'C' AND $login_fabrica == 1) $sql .= " AND tbl_defeito_constatado.codigo <> 1 ";
						$sql .= " ORDER BY tbl_defeito_constatado.descricao";
					}
				}

				$res = pg_exec ($con,$sql) ;
				for ($i = 0 ; $i < pg_numrows ($res) ; $i++ ) {
					echo "<option ";
					if ($defeito_constatado == pg_result ($res,$i,defeito_constatado) ) echo " selected ";
					echo " value='" . pg_result ($res,$i,defeito_constatado) . "'>" ;
					echo pg_result ($res,$i,codigo) ." - ". pg_result ($res,$i,descricao) ;
					echo "</option>";
				}
				?>
		</select>
	</td>
</tr>
</table>

<br>

<? if (strlen($troca_faturada) == 0) { ?>

<table border="0" cellpadding="2" cellspacing="2" align="center">
	<tr bgcolor="#CCCCCC">
		<td align="center"><font size="2" face="Geneva, Arial, Helvetica, san-serif"><b>C�digo</b></font> <a href="http://www.telecontrol.com.br/assist/admin/peca_consulta_por_produto.php?produto=<?echo $produto?>" target="_black"><font size="1" face="Geneva, Arial, Helvetica, san-serif"><b>Lista B�sica</b></font></a></td>
		<td align="center"><font size="2" face="Geneva, Arial, Helvetica, san-serif"><b>Descri��o</b></font></td>
		<td align="center"><font size="2" face="Geneva, Arial, Helvetica, san-serif"><b>Qtde</b></font></td>
		<td align="center"><font size="2" face="Geneva, Arial, Helvetica, san-serif"><b>Defeito</b></font></td>
		<td align="center"><font size="2" face="Geneva, Arial, Helvetica, san-serif"><b>Servi�o</b></font></td>
	</tr>
<?
if (strlen($_GET['os']) > 0) {
	$sql =	"SELECT tbl_os_item.os_item                              ,
					tbl_os_item.peca                                 ,
					tbl_os_item.qtde                                 ,
					tbl_os_item.defeito                              ,
					tbl_os_item.servico_realizado                    ,
					tbl_peca.referencia           AS peca_referencia ,
					tbl_peca.descricao            AS peca_descricao
			FROM  tbl_os_item
			JOIN  tbl_peca       ON tbl_os_item.peca       = tbl_peca.peca
			JOIN  tbl_os_produto ON tbl_os_item.os_produto = tbl_os_produto.os_produto
			JOIN  tbl_os         ON tbl_os_produto.os      = tbl_os.os
			WHERE tbl_os_produto.produto    = $produto
			AND   tbl_os_produto.os_produto = $os_produto
			AND   tbl_os.fabrica            = $login_fabrica
			ORDER BY tbl_peca.referencia";
# echo nl2br($sql);
	$res = @pg_exec($con,$sql);
	$num_linhas = @pg_numrows($res);
}

for ($i = 0 ; $i < $qtde_itens ; $i++) {

	$os_item         = '';
	$peca            = '';
	$peca_referencia = '';
	$peca_descricao  = '';
	$peca_qtde       = '';
	$defeito         = '';
	$servico         = '';

	if ($i < $num_linhas) {
		$os_item         = @pg_result($res,$i,os_item);
		$peca            = @pg_result($res,$i,peca);
		$peca_referencia = @pg_result($res,$i,peca_referencia);
		$peca_descricao  = @pg_result($res,$i,peca_descricao);
		$peca_qtde       = @pg_result($res,$i,qtde);
		$defeito         = @pg_result($res,$i,defeito);
		$servico         = @pg_result($res,$i,servico_realizado);
	}

	if (strlen($msg_erro) > 0) {
		$os_item         = trim($_POST["os_item_".$i]);
		$peca            = trim($_POST["peca_".$i]);
		$peca_referencia = trim($_POST["peca_referencia_".$i]);
		$peca_descricao  = trim($_POST["peca_descricao_".$i]);
		$peca_qtde       = trim($_POST["peca_qtde_".$i]);
		$defeito         = trim($_POST["defeito_".$i]);
		$servico         = trim($_POST["servico_".$i]);
	}
?>
	<tr>
		<td>
			<input type="hidden" name="os_item_<? echo $i ?>" value="<? echo $os_item ?>">
			<input type="hidden" name="peca_<? echo $i ?>" value="<? echo $peca ?>">
			<input type="hidden" name="produto">
			<input type="hidden" name="preco">
			<input class="frm" type="text" name="peca_referencia_<? echo $i ?>" size="15" value="<? echo $peca_referencia ?>">
			<img src='imagens/btn_buscar5.gif' border='0' align='absmiddle' onclick="javascript: fnc_pesquisa_peca_lista (document.frm_os.produto_referencia.value , document.frm_os.peca_referencia_<? echo $i ?> , document.frm_os.peca_descricao_<? echo $i ?>, document.frm_os.preco , 'referencia')" alt="Clique para efetuar a pesquisa" style='cursor:pointer;'>
		</td>
		<td>
			<input class="frm" type="text" name="peca_descricao_<? echo $i ?>" size="25" value="<? echo $peca_descricao ?>">
			<img src='imagens/btn_buscar5.gif' border='0' align='absmiddle' onclick="javascript: fnc_pesquisa_peca_lista (document.frm_os.produto_referencia.value , document.frm_os.peca_referencia_<? echo $i ?> , document.frm_os.peca_descricao_<? echo $i ?>, document.frm_os.preco , 'descricao')" alt="Clique para efetuar a pesquisa" style='cursor:pointer;'>
		</td>
		<td>
			<input class="frm" type="text" name="peca_qtde_<? echo $i ?>" size="3" value="<? echo $peca_qtde ?>">
		</td>
		<td>
			<select class='frm' size='1' name='defeito_<? echo $i ?>'>
				<option></option>
				<?
				$sqlD = "SELECT *
						FROM   tbl_defeito
						WHERE  tbl_defeito.fabrica = $login_fabrica;";
				$resD = pg_exec ($con,$sqlD) ;
				for ($x = 0 ; $x < pg_numrows($resD) ; $x++ ) {
					echo "<option ";
					if ($defeito == pg_result($resD,$x,defeito)) echo " selected ";
					echo " value='" . pg_result($resD,$x,defeito) . "'>" ;
					echo pg_result($resD,$x,descricao) ;
					echo "</option>";
				}
				?>
			</select>
		</td>
		<td>
			<select class='frm' size='1' name='servico_<? echo $i ?>'>
				<option></option>
<?
				$sqlS = "SELECT *
						FROM   tbl_servico_realizado
						WHERE  tbl_servico_realizado.fabrica = $login_fabrica ";

				if (strlen($linha) > 0) {
					$sqlS .= " AND tbl_servico_realizado.linha = '$linha' ";
				}

				if ($login_pede_peca_garantia == 't') {
					$sqlS .= "AND tbl_servico_realizado.descricao NOT ILIKE 'troca%' ";
				}

				if ($login_fabrica == 1) {
					if ($login_reembolso_peca_estoque == 't') {
						$sqlS .= "AND (tbl_servico_realizado.descricao NOT ILIKE 'troca%' ";
						$sqlS .= "OR tbl_servico_realizado.descricao ILIKE '%pedido%') ";
					}else{
						$sqlS .= "AND (tbl_servico_realizado.descricao ILIKE 'troca%' ";
						$sqlS .= "OR tbl_servico_realizado.descricao NOT ILIKE '%pedido%') ";
					}
				}

				$sqlS .= "AND tbl_servico_realizado.ativo = 't' ORDER BY descricao ";
				$resS = pg_exec ($con,$sqlS) ;

				for ($x = 0 ; $x < pg_numrows($resS) ; $x++ ) {
					echo "<option ";
					if ($servico == pg_result($resS,$x,servico_realizado)) echo " selected ";
					echo " value='" . pg_result($resS,$x,servico_realizado) . "'>" ;
					echo pg_result($resS,$x,descricao) ;
					echo "</option>";
				}
				?>
		</td>
	</tr>
<?
}
?>
</table>

<? }else{ ?>
<input type="hidden" name="troca_faturada" value="<?echo $troca_faturada?>">
<table border="0" cellpadding="2" cellspacing="0" align="center" width="750">
	<tr valign="top" align="left">
		<td>
			<font size="1" face="Geneva, Arial, Helvetica, san-serif">Motivo Troca</font>
			<br>
				<select name="motivo_troca" size="1" class="frm">
					<option value=""></option>
					<?
					$sql = "SELECT tbl_defeito_constatado.*
							FROM   tbl_defeito_constatado
							WHERE  tbl_defeito_constatado.fabrica = $login_fabrica";
					if ($consumidor_revenda == 'C' AND $login_fabrica == 1) $sql .= " AND tbl_defeito_constatado.codigo <> 1 ";
					$sql .= " ORDER BY tbl_defeito_constatado.descricao";

					$res = pg_exec ($con,$sql) ;
					for ($i = 0 ; $i < pg_numrows ($res) ; $i++ ) {
						echo "<option ";
						if ($motivo_troca == pg_result ($res,$i,defeito_constatado) ) echo " selected ";
						echo " value='" . pg_result ($res,$i,defeito_constatado) . "'>" ;
						echo pg_result ($res,$i,codigo) ." - ". pg_result ($res,$i,descricao) ;
						echo "</option>\n";
					}
					?>
			</select>
		</td>
	</tr>
</table>
<? } ?>

<br>

<input type="hidden" name="btn_acao" value="">

<center><img border="0" src="imagens_admin/btn_gravar.gif" onclick="javascript: if (document.frm_os.btn_acao.value =='') { document.frm_os.btn_acao.value='gravar'; document.frm_os.submit() }else{ alert('Aguarde submiss�o') }" ALT="Gravar" style="cursor:pointer;"></center>

</form>


<? include "rodape.php";?>
