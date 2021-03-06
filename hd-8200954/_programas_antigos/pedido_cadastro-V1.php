<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'autentica_usuario.php';

if($login_fabrica == 1){
	header("Location: pedido_blackedecker_cadastro.php");
	exit;
}

if($login_fabrica == 5){
	$layout_menu = 'pedido';
	$title       = "Cadastro de Pedidos de Pe�as";
	include "cabecalho.php";
	echo "<H4>CADASTRO DE PEDIDO TEMPORARIAMENTE SUSPENSO.</H4>";
	include "rodape.php"; 
	exit;
}

$btn_acao = strtolower ($_POST['btn_acao']);

$msg_erro = "";
$msg_debug = "";
$qtde_item = 30;

if ($btn_acao == "gravar"){
	$pedido         = $_POST['pedido'];
	$condicao       = $_POST['condicao'];
	$tipo_pedido    = $_POST['tipo_pedido'];
	$pedido_cliente = $_POST['pedido_cliente'];
	$transportadora = $_POST['transportadora'];
	$linha          = $_POST['linha'];
	
	if (strlen($condicao) == 0) {
		$aux_condicao = "null";
	}else{
		$aux_condicao = $condicao ;
	}

	if (strlen($pedido_cliente) == 0) {
		$aux_pedido_cliente = "null";
	}else{
		$aux_pedido_cliente = "'". $pedido_cliente ."'";
	}

	if (strlen($transportadora) == 0) {
		$aux_transportadora = "null";
	}else{
		$aux_transportadora = $transportadora ;
	}

	if (strlen($tipo_pedido) <> 0) {
		$aux_tipo_pedido = "'". $tipo_pedido ."'";
	}else{
		$sql = "SELECT	tipo_pedido
				FROM	tbl_tipo_pedido
				WHERE	descricao IN ('Faturado','Venda')
				AND		fabrica = $login_fabrica";
		$res = pg_exec ($con,$sql);
		$aux_tipo_pedido = "'". pg_result($res,0,tipo_pedido) ."'";
	}

	if (strlen($linha) == 0) {
		$aux_linha = "null";
	}else{
		$aux_linha = $linha ;
	}

	
	$res = pg_exec ($con,"BEGIN TRANSACTION");

	if (strlen ($pedido) == 0) {
		
		#-------------- insere pedido ------------
		$sql = "INSERT INTO tbl_pedido (
					posto          ,
					fabrica        ,
					condicao       ,
					pedido_cliente ,
					transportadora ,
					linha          ,
					tipo_pedido    
				) VALUES (
					$login_posto        ,
					$login_fabrica      ,
					$aux_condicao       ,
					$aux_pedido_cliente ,
					$aux_transportadora ,
					$aux_linha          ,
					$aux_tipo_pedido    
				)";
		$res = @pg_exec ($con,$sql);
		$msg_erro = pg_errormessage($con);
		if (strlen($msg_erro) == 0){
			$res = @pg_exec ($con,"SELECT CURRVAL ('seq_pedido')");
			$pedido  = @pg_result ($res,0,0);
		}
	}else{
		$sql = "UPDATE tbl_pedido SET
					condicao       = $aux_condicao       ,
					pedido_cliente = $aux_pedido_cliente ,
					transportadora = $aux_transportadora ,
					linha          = $aux_linha          ,
					tipo_pedido    = $aux_tipo_pedido    
				WHERE pedido  = $pedido
				AND   posto   = $login_posto
				AND   fabrica = $login_fabrica";
		$res = @pg_exec ($con,$sql);
		$msg_erro = pg_errormessage($con);
	}

	if (strlen ($msg_erro) == 0) {
//$msg_debug .= " $i ) <b>CURRVAL Pedido </b>".$sql." - [ $pedido ]<br><br>";
		
		$nacional  = 0;
		$importado = 0;
			
		for ($i = 0 ; $i < $qtde_item ; $i++) {
			$pedido_item     = trim($_POST['pedido_item_'     . $i]);
			$peca_referencia = trim($_POST['peca_referencia_' . $i]);
			$qtde            = trim($_POST['qtde_'            . $i]);
			$preco           = trim($_POST['preco_'           . $i]);
			
			if (strlen ($peca_referencia) > 0 AND ( strlen($qtde) == 0 OR $qtde < 1 ) ) {
				$msg_erro = "N�o foi digitada a quantidade para a Pe�a $peca_referencia.";
				$linha_erro = $i;
				break;
			}
			
			if (strlen ($pedido_item) > 0 AND strlen ($peca_referencia) == 0) {
				// delete
				$sql = "DELETE	FROM	tbl_pedido_item
						WHERE	pedido_item = $pedido_item 
						AND		pedido = $pedido";
				$res = pg_exec ($con,$sql);
			}
			
			if (strlen ($peca_referencia) > 0) {
				$peca_referencia = trim (strtoupper ($peca_referencia));
				$peca_referencia = str_replace ("-","",$peca_referencia);
				$peca_referencia = str_replace (".","",$peca_referencia);
				$peca_referencia = str_replace ("/","",$peca_referencia);
				//$peca_referencia = str_replace (" ","",$peca_referencia);
				
				$sql = "SELECT  tbl_peca.peca   ,
								tbl_peca.origem 
						FROM    tbl_peca
						WHERE   tbl_peca.referencia_pesquisa = '$peca_referencia'
						AND     tbl_peca.fabrica             = $login_fabrica";
				$res = pg_exec ($con,$sql);
				
				if (pg_numrows ($res) == 0) {
					$msg_erro = "Pe�a $peca_referencia n�o cadastrada";
					$linha_erro = $i;
					break;
				}else{
					$peca   = pg_result ($res,0,peca);
					$origem = trim(pg_result ($res,0,origem));
				}

				if ($origem == "NAC" or $origem == "1") {
					$nacional = $nacional + 1;
				}
				
				if ($origem == "IMP" or $origem == "2") {
					$importado = $importado + 1;
				}

				if ($nacional > 0 and $importado > 0 and $login_fabrica <> 3 and $login_fabrica <> 5 and $login_fabrica <> 8) {
					$msg_erro = "N�o � permitido realizar um pedido com pe�a Nacional e Importada";
					$linha_erro = $i;
					break;
				}

				if (strlen ($msg_erro) == 0 AND strlen($peca) > 0) {
					if (strlen($pedido_item) == 0){
						$sql = "INSERT INTO tbl_pedido_item (
									pedido ,
									peca   ,
									qtde   
								) VALUES (
									$pedido ,
									$peca   ,
									$qtde   
								)";
					}else{
						$sql = "UPDATE tbl_pedido_item SET
									peca = $peca,
									qtde = $qtde
								WHERE pedido_item = $pedido_item";
					}
					$res = @pg_exec ($con,$sql);
					$msg_erro = pg_errormessage($con);
					
					if (strlen($msg_erro) == 0 AND strlen($pedido_item) == 0) {
						$res         = pg_exec ($con,"SELECT CURRVAL ('seq_pedido_item')");
						$pedido_item = pg_result ($res,0,0);
						$msg_erro = pg_errormessage($con);
					}
					
					if (strlen($msg_erro) == 0) {
						$sql = "SELECT fn_valida_pedido_item ($pedido,$peca,$login_fabrica)";
						$res = @pg_exec ($con,$sql);
						$msg_erro = pg_errormessage($con);
					}
					
					if (strlen ($msg_erro) > 0) {
						break ;
					}
				}
			}
		}
	}
	
	if (strlen ($msg_erro) == 0) {
		$sql = "SELECT fn_pedido_finaliza ($pedido,$login_fabrica)";
		$res = @pg_exec ($con,$sql);
		$msg_erro = pg_errormessage($con);
	}

	if (strlen ($msg_erro) == 0) {
		$res = pg_exec ($con,"COMMIT TRANSACTION");
		
		header ("Location: pedido_finalizado.php?pedido=$pedido&loc=1");
		exit;
	}else{
		$res = pg_exec ($con,"ROLLBACK TRANSACTION");
	}

}

#------------ Le Pedido da Base de dados ------------#
$pedido = $_GET['pedido'];

if (strlen ($pedido) > 0) {
	$sql = "SELECT	TO_CHAR(tbl_pedido.data, 'DD/MM/YYYY')    AS data                 ,
					tbl_pedido.tipo_frete                                             ,
					tbl_pedido.transportadora                                         ,
					tbl_transportadora.cnpj                   AS transportadora_cnpj  ,
					tbl_transportadora.nome                   AS transportadora_nome  ,
					tbl_transportadora_fabrica.codigo_interno AS transportadora_codigo,
					tbl_pedido.pedido_cliente                                         ,
					tbl_pedido.tipo_pedido                                            ,
					tbl_pedido.produto                                                ,
					tbl_produto.referencia                    AS produto_referencia   ,
					tbl_produto.descricao                     AS produto_descricao    ,
					tbl_pedido.linha                                                  ,
					tbl_pedido.condicao                                               ,
					tbl_pedido.exportado                                              
			FROM	tbl_pedido
			LEFT JOIN tbl_transportadora USING (transportadora)
			left JOIN	tbl_transportadora_fabrica ON tbl_transportadora_fabrica.fabrica = $login_fabrica
			LEFT JOIN tbl_produto        USING (produto)
			WHERE	tbl_pedido.pedido   = $pedido
			AND		tbl_pedido.posto    = $login_posto
			AND		tbl_pedido.fabrica  = $login_fabrica ";
	$res = pg_exec ($con,$sql);
	
	if (pg_numrows ($res) > 0) {
		$data                  = trim(pg_result ($res,0,data));
		$transportadora        = trim(pg_result ($res,0,transportadora));
		$transportadora_cnpj   = trim(pg_result ($res,0,transportadora_cnpj));
		$transportadora_codigo = trim(pg_result ($res,0,transportadora_codigo));
		$transportadora_nome   = trim(pg_result ($res,0,transportadora_nome));
		$pedido_cliente        = trim(pg_result ($res,0,pedido_cliente));
		$tipo_pedido           = trim(pg_result ($res,0,tipo_pedido));
		$produto               = trim(pg_result ($res,0,produto));
		$produto_referencia    = trim(pg_result ($res,0,produto_referencia));
		$produto_descricao     = trim(pg_result ($res,0,produto_descricao));
		$linha                 = trim(pg_result ($res,0,linha));
		$condicao              = trim(pg_result ($res,0,condicao));
		$exportado             = trim(pg_result ($res,0,exportado));
	}
}


#---------------- Recarrega Form em caso de erro -------------
if (strlen ($msg_erro) > 0) {
	$pedido         = $_POST['pedido'];
	$condicao       = $_POST['condicao'];
	$tipo_pedido    = $_POST['tipo_pedido'];
	$pedido_cliente = $_POST['pedido_cliente'];
	$transportadora = $_POST['transportadora'];
	$linha          = $_POST['linha'];
}

$title       = "Cadastro de Pedidos de Pe�as";
$layout_menu = 'pedido';

include "cabecalho.php";

?>

<SCRIPT LANGUAGE="JavaScript">
function exibeTipo(){
	f = document.frm_pedido;
	if(f.linha.value == 3){
		f.tipo_pedido.disabled = false;
	}else{
		f.tipo_pedido.selectedIndex = 0;
		f.tipo_pedido.disabled = true;
	}
}

/* FUN��O PARA INTELBRAS POIS TEM POSI��O PARA SER PESQUISADA */
function fnc_pesquisa_peca_lista_intel (produto_referencia, peca_referencia, peca_descricao, peca_posicao, tipo) {
	var url = "";
	if (tipo == "tudo") {
		url = "peca_pesquisa_lista.php?produto=" + produto_referencia + "&descricao=" + peca_referencia.value + "&tipo=" + tipo;
	}

	if (tipo == "referencia") {
		url = "peca_pesquisa_lista.php?produto=" + produto_referencia + "&peca=" + peca_referencia.value + "&tipo=" + tipo;
	}

	if (tipo == "descricao") {
		url = "peca_pesquisa_lista.php?produto=" + produto_referencia + "&descricao=" + peca_descricao.value + "&tipo=" + tipo;
	}
	if (peca_referencia.value.length >= 4 || peca_descricao.value.length >= 4) {
		janela = window.open(url, "janela", "toolbar=no, location=yes, status=yes, scrollbars=yes, directories=no, width=501, height=400, top=18, left=0");
		janela.produto		= produto_referencia;
		janela.referencia	= peca_referencia;
		janela.descricao	= peca_descricao;
		janela.posicao		= peca_posicao;
		janela.focus();
	}else{
		alert("Digite pelo menos 4 caracteres!");
	}
}
</SCRIPT>

<? include "javascript_pesquisas.php" ?>

<? 
if (strlen ($msg_erro) > 0) {
	if (strpos ($msg_erro,"Cannot insert a duplicate key into unique index tbl_os_sua_os") > 0) $msg_erro = "Esta ordem de servi�o j� foi cadastrada";
?>

<table width="730" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#CCCCCC">
<tr>
	<td valign="middle" align="center" class='error'>
		<? echo $msg_erro ?>
	</td>
</tr>
</table>
<p>
<?
//	echo $msg_debug;
}
?>

<?

$sql = "SELECT  tbl_condicao.*
		FROM    tbl_condicao
		JOIN    tbl_posto_condicao USING (condicao)
		WHERE   tbl_posto_condicao.posto = $login_posto
		AND     tbl_condicao.fabrica     = $login_fabrica
		AND     tbl_condicao.visivel IS TRUE
		AND     tbl_condicao.descricao ilike '%garantia%'
		ORDER BY lpad(trim(tbl_condicao.codigo_condicao), 10,0) ";
$res = pg_exec ($con,$sql);

if (pg_numrows ($res) > 0) {
	$frase = "PREENCHA SEU PEDIDO DE COMPRA/GARANTIA";
}else{
	$frase = "PREENCHA SEU PEDIDO DE COMPRA";
}
?>

<br>

<table width="100%" border="0" cellspacing="5" cellpadding="0">
<tr>
	<td nowrap align='center'>
		<font size="2" face="Geneva, Arial, Helvetica, san-serif" color="#FF0000"><b><? echo $frase; ?></b>.</font>
	</td>
</tr>
</table>

<table width="100%" border="0" cellspacing="5" cellpadding="0">
<tr>
	<? 	if ($login_fabrica == 3) { ?>
	<td align='center'>
		<font size="2" face="Geneva, Arial, Helvetica, san-serif" color='#990000'><b>Aten��o Linha �udio e V�deo:</b> Pedidos de pe�as para linha de �udio e v�deo feitos nesta tela devem ser para <br> uso em consertos fora da garantia, e gerar�o fatura e duplicata.<br>Pedidos para conserto em garantia ser�o gerados automaticamente pela Ordem de Servi�o.<br>Leia o Manual e a Circular na primeira p�gina.</font>
		<br><br>
		<font size="2" face="Geneva, Arial, Helvetica, san-serif" color='#990000'><b>*** Pedidos realizados no valor abaixo de R$30,00 n�o ser�o faturados ***</b></font>
		<br><br>
	</td>
	<? }else{ ?>
	<td nowrap align='center'>
		<font size="2" face="Geneva, Arial, Helvetica, san-serif"><b>Aten��o:</b> Pedidos a prazo depender�o de an�lise do departamento de cr�dito.</font>
	</td>
	<? } ?>
</tr>
<tr>
	<td nowrap align='center'>
		<font size="2" face="Geneva, Arial, Helvetica, san-serif">Para efetuar um pedido por modelo do produto, informe a refer�ncia <br> ou descri��o e clique na lupa, ou simplesmente clique na lupa.</font>
	</td>
</tr>
</table>

<br>

<table width="600" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#FFFFFF">

<!-- ------------- Formul�rio ----------------- -->
<form name="frm_pedido" method="post" action="<? echo $PHP_SELF ?>">
<input class="frm" type="hidden" name="pedido" value="<? echo $pedido; ?>">
<input class="frm" type="hidden" name="voltagem" value="<? echo $voltagem; ?>">

<tr>
	<td align='center'>
		<font size="2" face="Geneva, Arial, Helvetica, san-serif"><b>Pedido do Cliente</b></font>
		<br>
		<input class="frm" type="text" name="pedido_cliente" size="15" maxlength="20" value="<? echo $pedido_cliente ?>">
	</td>

	<td valign="top" align="center" nowrap>
		<?
		$res = pg_exec ("SELECT pedido_escolhe_condicao FROM tbl_fabrica WHERE fabrica = $login_fabrica");

		if (pg_result ($res,0,0) == 'f') {
			echo "<input type='hidden' name='condicao' value=''>";
		}else{
			echo "<font size='2' face='Geneva, Arial, Helvetica, san-serif'><b>Condi��o Pagamento</b></font>";
			echo "<br>";

			echo "<select size='1' name='condicao' class='frm'>";
			$sql = "SELECT   tbl_condicao.*
					FROM     tbl_condicao
					JOIN     tbl_posto_condicao USING (condicao)
					WHERE    tbl_posto_condicao.posto = $login_posto
					AND      tbl_condicao.fabrica     = $login_fabrica
					AND      tbl_condicao.visivel       IS TRUE
					AND      tbl_posto_condicao.visivel IS TRUE
					ORDER BY lpad(trim(tbl_condicao.codigo_condicao), 10,0) ";
			$res = pg_exec ($con,$sql);

			if (pg_numrows ($res) == 0) {
				$sql = "SELECT   tbl_condicao.*
						FROM     tbl_condicao
						WHERE    tbl_condicao.fabrica = $login_fabrica
						AND      tbl_condicao.visivel IS TRUE
						ORDER BY lpad(trim(tbl_condicao.codigo_condicao), 10,0) ";
				$res = pg_exec ($con,$sql);
			}
			
			for ($i = 0 ; $i < pg_numrows ($res) ; $i++ ) {
				echo "<option value='" . pg_result ($res,$i,condicao) . "'";
				if (pg_result ($res,$i,condicao) == $condicao) echo " selected";
				echo ">" . pg_result ($res,$i,descricao) . "</option>";
			}
			
			echo "</select>";
		}
		?>

	</td>


	<td valign="top" align="center" nowrap>
		<?
		// se posto pode escolher tipo_pedido
		echo "<td nowrap align='center'>";
		echo "<font size='2' face='Geneva, Arial, Helvetica, san-serif'><b>Tipo de Pedido</b></font>";
		echo "<br>";
		
		$sql = "SELECT   *
				FROM     tbl_posto_fabrica
				WHERE    tbl_posto_fabrica.posto   = $login_posto
				AND      tbl_posto_fabrica.fabrica = $login_fabrica
				AND      tbl_posto_fabrica.pedido_em_garantia IS TRUE;";
		$res = pg_exec ($con,$sql);
		
		if (pg_numrows($res) > 0) {
			echo "<select size='1' name='tipo_pedido' class='frm'>";
			$sql = "SELECT   *
					FROM     tbl_tipo_pedido
					WHERE    fabrica = $login_fabrica
					ORDER BY tipo_pedido ";
			$res = pg_exec ($con,$sql);
			
			for ($i = 0 ; $i < pg_numrows ($res) ; $i++ ) {
				echo "<option value='" . pg_result($res,$i,tipo_pedido) . "'";
				if (pg_result ($res,$i,tipo_pedido) == $tipo_pedido) echo " selected";
				echo ">" . pg_result($res,$i,descricao) . "</option>";
			}
			
			echo "</select>";
		}else{
			echo "<select size='1' name='tipo_pedido' ";
			if ($login_fabrica == 3) echo "disabled";
			echo ">";
			$sql = "SELECT   *
					FROM     tbl_tipo_pedido
					WHERE    (tbl_tipo_pedido.descricao ILIKE '%Faturado%'
					       OR tbl_tipo_pedido.descricao ILIKE '%Venda%')
					AND      tbl_tipo_pedido.fabrica = $login_fabrica
					ORDER BY tipo_pedido;";
			$res = pg_exec ($con,$sql);
			
			for ($i = 0 ; $i < pg_numrows ($res) ; $i++ ) {
				echo "<option value='" . pg_result($res,$i,tipo_pedido) . "'";
				if (pg_result ($res,$i,tipo_pedido) == $tipo_pedido) echo " selected";
				echo ">" . pg_result($res,$i,descricao) . "</option>";
			}
			
			echo "</select>";
		}
		?>

	</td>
</tr>
</table>




<table width="600" border="0" cellspacing="1" cellpadding="5" align='center'>
<tr height="20" bgcolor="#ffffff">
		<?

		#-------------------- Transportadora -------------------

		$sql = "SELECT	tbl_transportadora.transportadora        ,
						tbl_transportadora.cnpj                  ,
						tbl_transportadora.nome                  ,
						tbl_transportadora_fabrica.codigo_interno
				FROM	tbl_transportadora
				JOIN	tbl_transportadora_fabrica USING(transportadora)
				JOIN	tbl_fabrica USING(fabrica)
				WHERE	tbl_transportadora_fabrica.fabrica        = $login_fabrica
				AND		tbl_transportadora_fabrica.ativo          = 't' 
				AND		tbl_fabrica.pedido_escolhe_transportadora = 't'";
		$res = pg_exec ($con,$sql);

		if (pg_numrows ($res) > 0) {
		?>
			<td align='center'>
				<font size="2" face="Geneva, Arial, Helvetica, san-serif">Transportadora</font>
				<br>
				<?
				if (pg_numrows ($res) <= 20) {

					echo "<select name='transportadora'>";
					echo "<option selected></option>";
					for ($i = 0 ; $i < pg_numrows ($res) ; $i++) {
						echo "<option value='".pg_result($res,$i,transportadora)."' ";
						if ($transportadora == pg_result($res,$i,transportadora) ) echo " selected ";
						echo ">";
						echo pg_result($res,$i,codigo_interno) ." - ".pg_result($res,$i,nome);
						echo "</option>\n";
					}
					echo "		</select>";
				}else{

					echo "<input type='hidden' name='transportadora' value='' value='$transportadora'>";
					echo "<input type='hidden' name='transportadora_cnpj' value='$transportadora_cnpj'>";

#					echo "<input type='text' name='transportadora_cnpj' size='20' maxlength='18' value='$transportadora_cnpj' class='textbox' >&nbsp;<img src='imagens/btn_buscar5.gif' border='0' align='absmiddle' onclick=\"javascript: fnc_pesquisa_transportadora (document.frm_pedido.transportadora_cnpj,'cnpj')\" style='cursor:pointer;'>";

					echo "<input type='text' name='transportadora_codigo' size='5' maxlength='10' value='$transportadora_codigo' class='textbox' onblur='javascript: lupa_transportadora_codigo.click()'>&nbsp;<img id='lupa_transportadora_codigo' src='imagens/btn_buscar5.gif' border='0' align='absmiddle' onclick=\"javascript: fnc_pesquisa_transportadora (document.frm_pedido.transportadora_codigo,'codigo')\" style='cursor:pointer;'>";

					echo "&nbsp;&nbsp;&nbsp;";

//					echo "<input type='text' name='transportadora_nome' size='35' maxlength='50' value='$transportadora_nome' class='textbox' onblur='javascript: lupa_transportadora_nome.click()'>&nbsp;<img id='lupa_transportadora_nome' src='imagens/btn_buscar5.gif' border='0' align='absmiddle' onclick=\"javascript: fnc_pesquisa_transportadora (document.frm_pedido.transportadora_nome,'nome')\" style='cursor:pointer;'>";
					echo "<input type='text' name='transportadora_nome' size='35' maxlength='50' value='$transportadora_nome' class='textbox' >&nbsp;<img id='lupa_transportadora_nome' src='imagens/btn_buscar5.gif' border='0' align='absmiddle' onclick=\"javascript: fnc_pesquisa_transportadora (document.frm_pedido.transportadora_nome,'nome')\" style='cursor:pointer;'>";

				}
				?>
			</td>
		<?
		}
		?>

		
		<?

		#-------------------- Linha do pedido -------------------

		$sql = "SELECT	tbl_linha.linha            ,
						tbl_linha.nome
				FROM	tbl_linha
				JOIN	tbl_fabrica USING(fabrica)
				JOIN	tbl_posto_linha ON tbl_posto_linha.posto = $login_posto AND tbl_posto_linha.linha = tbl_linha.linha
				WHERE	tbl_fabrica.linha_pedido is true 
				AND     tbl_linha.fabrica = $login_fabrica";
		$res = pg_exec ($con,$sql);

		if (pg_numrows ($res) > 0) {
		?>
			<td align='center'>
				<font size="2" face="Geneva, Arial, Helvetica, san-serif">Linha</font>
				<br>
				<?
				echo "<select name='linha' class='frm' ";
				if ($login_fabrica == 3) echo " onChange='exibeTipo()'";
				echo ">";
				echo "<option selected></option>";
				for ($i = 0 ; $i < pg_numrows ($res) ; $i++) {
					echo "<option value='".pg_result($res,$i,linha)."' ";
					if ($linha == pg_result($res,$i,linha) ) echo " selected";
					echo ">";
					echo pg_result($res,$i,nome);
					echo "</option>\n";
				}
				echo "</select>";
				?>
			</td>
		<?
		}
		?>
</tr>
</table>

<table width="400" border="0" cellspacing="5" cellpadding="0" align='center'>
<tr height="20" bgcolor="#bbbbbb">
	<td align='center'>
		<font size="2" face="Geneva, Arial, Helvetica, san-serif">Refer�ncia Produto</font>
	</td>
	<td align='center'>
		<font size="2" face="Geneva, Arial, Helvetica, san-serif">Descri��o Produto</font>
	</td>
</tr>

<tr height="20">
	<td align='center'>
		<input class="frm" type="text" name="produto_referencia" size="15" maxlength="20" value="<? echo $produto_referencia ?>">&nbsp;<img src='imagens/btn_buscar5.gif' style="cursor:pointer" border='0' alt="Clique para pesquisar pela refer�ncia do produto" align='absmiddle' onclick="javascript: fnc_pesquisa_produto (document.frm_pedido.produto_referencia,document.frm_pedido.produto_descricao,'referencia')">
	</td>
	<td align='center'>
		<input class="frm" type="text" name="produto_descricao" size="30" value="<? echo $produto_descricao ?>">&nbsp;<img src='imagens/btn_buscar5.gif' style="cursor:pointer" border='0' align='absmiddle' alt="Clique para pesquisar pela descri��o do produto" onclick="javascript: fnc_pesquisa_produto (document.frm_pedido.produto_referencia,document.frm_pedido.produto_descricao,'descricao')">
	</td>
</tr>
</table>

<p>

<table border="0" cellspacing="5" cellpadding="0" align="center">
<tr height="20" bgcolor="#bbbbbb">
	<td align='center'><font size="2" face="Geneva, Arial, Helvetica, san-serif">Refer�ncia Componente</font></td>
	<td align='center'><font size="2" face="Geneva, Arial, Helvetica, san-serif">Descri��o Componente</font></td>
	<td align='center'><font size="2" face="Geneva, Arial, Helvetica, san-serif">Qtde</font></td>
	<? if ($login_fabrica != 14) { ?>
	<td align='center'><font size="2" face="Geneva, Arial, Helvetica, san-serif">Pre�o</font></td>
	<? } ?>
</tr>

<?
for ($i = 0 ; $i < $qtde_item ; $i++) {

	if (strlen($pedido) > 0 AND strlen ($msg_erro) == 0){
		$sql = "SELECT  tbl_pedido_item.pedido_item,
						tbl_peca.referencia        ,
						tbl_peca.descricao         ,
						tbl_pedido_item.qtde       ,
						tbl_pedido_item.preco      
				FROM  tbl_pedido
				JOIN  tbl_pedido_item USING (pedido)
				JOIN  tbl_peca        USING (peca)
				WHERE tbl_pedido_item.pedido = $pedido
				AND   tbl_pedido.posto   = $login_posto
				AND   tbl_pedido.fabrica = $login_fabrica
				ORDER BY tbl_pedido_item.pedido_item";
		$res = pg_exec ($con,$sql);

		if (pg_numrows($res) > 0) {
			$pedido_item     = trim(@pg_result($res,$i,pedido_item));
			$peca_referencia = trim(@pg_result($res,$i,referencia));
			$peca_descricao  = trim(@pg_result($res,$i,descricao));
			$qtde            = trim(@pg_result($res,$i,qtde));
			$preco           = trim(@pg_result($res,$i,preco));
			if (strlen($preco) > 0) $preco = number_format($preco,2,',','.');
		}else{
			$pedido_item     = $_POST["pedido_item_"     . $i];
			$peca_referencia = $_POST["peca_referencia_" . $i];
			$peca_descricao  = $_POST["peca_descricao_"  . $i];
			$qtde            = $_POST["qtde_"            . $i];
			$preco           = $_POST["preco_"           . $i];
		}
	}else{
		$pedido_item     = $_POST["pedido_item_"     . $i];
		$peca_referencia = $_POST["peca_referencia_" . $i];
		$peca_descricao  = $_POST["peca_descricao_"  . $i];
		$qtde            = $_POST["qtde_"            . $i];
		$preco           = $_POST["preco_"           . $i];
	}

	$peca_referencia = trim ($peca_referencia);

	#--------------- Valida Pe�as em DE-PARA -----------------#
	$tem_obs = false;
	$linha_obs = "";

	$sql = "SELECT para FROM tbl_depara WHERE de = '$peca_referencia' AND fabrica = $login_fabrica";
	$resX = pg_exec ($con,$sql);
	if (pg_numrows ($resX) > 0) {
		$linha_obs = "Pe�a original " . $peca_referencia . " mudou para o c�digo acima <br>&nbsp;";
		$peca_referencia = pg_result ($resX,0,0);
		$tem_obs = true;
	}

	#--------------- Valida Pe�as Fora de Linha -----------------#
	$sql = "SELECT * FROM tbl_peca_fora_linha WHERE referencia = '$peca_referencia' AND fabrica = $login_fabrica";
	$resX = pg_exec ($con,$sql);
	if (pg_numrows ($resX) > 0) {
		$linha_obs .= "Pe�a acima est� fora de linha <br>&nbsp;";
		$tem_obs = true;
	}


	
	
	if (strlen ($peca_referencia) > 0) {
		$sql = "SELECT descricao FROM tbl_peca WHERE referencia = '$peca_referencia' AND fabrica = $login_fabrica";
		$resX = pg_exec ($con,$sql);
		if (pg_numrows ($resX) > 0) {
			$peca_descricao = pg_result ($resX,0,0);
		}
	}

	$peca_descricao = trim ($peca_descricao);



	$cor="";
	if ($linha_erro == $i and strlen ($msg_erro) > 0) $cor='#ffcccc';
	if ($linha_erro == $i and strlen ($msg_erro) > 0) $cor='#ffcccc';
	if ($tem_obs) $cor='#FFCC33';
?>
	<tr bgcolor="<? echo $cor ?>">
		<td align='left'>
			<input type="hidden" name="pedido_item_<? echo $i ?>" size="15" value="<? echo $pedido_item; ?>">
			<input class="frm" type="text" name="peca_referencia_<? echo $i ?>" size="15" value="<? echo $peca_referencia; ?>"><img src='imagens/btn_buscar5.gif' style="cursor: pointer;" alt="Clique para pesquisar por refer�ncia do componente" border='0' hspace='5' align='absmiddle' <? if ($login_fabrica == 14) { ?> onclick="javascript: fnc_pesquisa_peca_lista_intel (document.frm_pedido.produto_referencia.value , document.frm_pedido.peca_referencia_<?echo $i?> , document.frm_pedido.peca_descricao_<?echo $i?> , document.frm_pedido.posicao, 'referencia')" <? }else{ ?> onclick="javascript: fnc_pesquisa_peca_lista (window.document.frm_pedido.produto_referencia.value, window.document.frm_pedido.peca_referencia_<? echo $i ?>,window.document.frm_pedido.peca_descricao_<? echo $i ?>,window.document.frm_pedido.preco_<? echo $i ?>,window.document.frm_pedido.voltagem,'referencia')" <? } ?>>
		</td>
		<td align='left'>
			<input type="hidden" name="posicao">
			<input class="frm" type="text" name="peca_descricao_<? echo $i ?>" size="30" value="<? echo $peca_descricao ?>"><img src='imagens/btn_buscar5.gif' style="cursor: pointer;" alt="Clique para pesquisar por descri��o do componente" border='0' hspace='5' align='absmiddle' <? if ($login_fabrica == 14) { ?> onclick="javascript: fnc_pesquisa_peca_lista_intel (document.frm_pedido.produto_referencia.value , document.frm_pedido.peca_referencia_<?echo $i?> , document.frm_pedido.peca_descricao_<?echo $i?> , document.frm_pedido.posicao, 'descricao')" <? }else{ ?> onclick="javascript: fnc_pesquisa_peca_lista (window.document.frm_pedido.produto_referencia.value, window.document.frm_pedido.peca_referencia_<? echo $i ?>,window.document.frm_pedido.peca_descricao_<? echo $i ?>,window.document.frm_pedido.preco_<? echo $i ?>,window.document.frm_pedido.voltagem,'descricao')" <? } ?>>
		</td>
		<td align='center'><input class="frm" type="text" name="qtde_<? echo $i ?>" size="5" maxlength='5' value="<? echo $qtde ?>"></td>
		<? if ($login_fabrica != 14) { ?>
		<td align='center'><input class="frm" type="text" name="preco_<? echo $i ?>" size="10"  value="<? echo $preco ?>" readonly style='text-align:right'></td>
		<? } ?>
	</tr>

	<?
	if ($tem_obs) {
		echo "<tr bgcolor='#FFCC33' style='font-size:12px'>";
		echo "<td colspan='4'>$linha_obs</td>";
		echo "</tr>";
	}
	?>

<?
}
?>

</table>

<center>

<input type="hidden" name="btn_acao" value="">
<img src='imagens/btn_gravar.gif' onclick="javascript: if (document.frm_pedido.btn_acao.value == '' ) { document.frm_pedido.btn_acao.value='gravar' ; document.frm_pedido.submit() } else { alert ('Aguarde submiss�o') }" ALT="Gravar pedido" border='0' style='cursor: pointer'>


</form>


<p>

<!--
<div id="lupa">
<table width="200" border="0" cellspacing="3" bgcolor="#cccccc">
<tr>
	<td valign="middle" bgcolor="#ffffff" style="PADDING: 10px">
	<H3>
		Para pesquisar uma pe�a, digite parte da descri��o ou da refer�ncia, e clique neste bot�o. Na janela de pesquisa, clique sobre a pe�a que est� procurando, ou refa�a a busca com outra parte da descri��o da pe�a.
	</H3>
	</td>
</tr>
</table>
</div>
-->

<? 
	include "rodape.php"; 
?>