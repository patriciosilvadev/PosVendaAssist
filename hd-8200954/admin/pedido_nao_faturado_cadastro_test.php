<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'autentica_admin.php';
include 'funcoes.php';

// somente Ibratele
if($login_fabrica <> 15 AND $login_fabrica <> 8 AND $login_fabrica <> 51 AND $login_fabrica <> 59 AND ($login_fabrica <> 65) AND ($login_fabrica <> 43) AND ($login_fabrica <> 80) AND ($login_fabrica <> 88)){
	header("Location: pedido_cadastro.php");
	exit;
}

$btn_acao = trim (strtolower ($_POST['btn_acao']));

$msg_erro = "";

if (strlen($_GET["cancelar"])>0 AND strlen($_GET["pedido"])>0) {

	//$res = pg_query ($con,"BEGIN TRANSACTION");

	$pedido   = trim($_GET["pedido"]);
	$motivo   = trim($_GET["motivo"]);
	$cancelar = trim($_GET["cancelar"]); 
	$qtde_cancelar = trim($_GET["qtde_cancelar"]);
	
	if(strlen($motivo)==0) $msg_erro = "Por favor informe o motivo de cancelamento <br>";
	else                   $aux_motivo = "'$motivo'";
	//Cancela todo o pedido quando ele � distribuidor

	if(strlen($qtde_cancelar)==0) $msg_erro .= "Por favor informe a quantidade a cancelar";
	
		if (strlen($msg_erro)==0) {
			$sql = "SELECT  PI.pedido_item,
				(PI.qtde - PI.qtde_faturada - PI.qtde_faturada_distribuidor) as qtde       ,
				PC.peca      ,
				PC.referencia,
				PC.descricao ,
				OP.os        ,
				PE.posto     ,
				PE.distribuidor
			FROM    tbl_pedido       PE
			JOIN    tbl_pedido_item  PI ON PI.pedido     = PE.pedido
			JOIN    tbl_peca         PC ON PC.peca       = PI.peca
			LEFT JOIN tbl_os_item    OI ON OI.peca       = PI.peca       AND OI.pedido = PE.pedido
			LEFT JOIN tbl_os_produto OP ON OP.os_produto = OI.os_produto
			WHERE   PI.pedido      = $pedido
			AND     PI.pedido_item = $cancelar
			AND     PE.fabrica     = $login_fabrica";
			if ($login_fabrica <> 2 and $login_fabrica <> 80) {
				$sql .= " AND     PE.exportado   IS NULL ";
			}
			// echo nl2br($sql);
			$res = pg_query ($con,$sql);
			if (pg_num_rows ($res) > 0) {
				$peca         = pg_fetch_result ($res,peca);
				$referencia   = pg_fetch_result ($res,referencia);
				$descricao    = pg_fetch_result ($res,descricao);
				$qtde         = pg_fetch_result ($res,qtde);
				$os           = pg_fetch_result ($res,os);
				$posto        = pg_fetch_result ($res,posto);
				$distribuidor = pg_fetch_result ($res,distribuidor);
			
				if($qtde_cancelar > $qtde) {
					$msg_erro .= "Quantidade a cancelar maior que quantidade pendente";
				}

				if(strlen($msg_erro)==0){
					if(strlen($os)==0) $os ="null";
						//Verifica se j� foi faturada
						$sql  = "SELECT trim(tbl_faturamento.nota_fiscal) As nota_fiscal ,
										tbl_faturamento.faturamento,
										tbl_faturamento.conhecimento
								FROM    tbl_faturamento
								JOIN    tbl_faturamento_item USING (faturamento)
								WHERE   tbl_faturamento.posto        = $posto
								AND     tbl_faturamento_item.pedido  = $pedido
								AND     tbl_faturamento.pedido       = $pedido
								AND     tbl_faturamento_item.peca    = $peca;";
						$resY = pg_query ($con,$sql);
						if (pg_num_rows ($resY) > 0) {
							$msg_erro  .= "A pe�a $referencia - $descricao do pedido $pedido j� est� faturada com a nota fiscal". pg_fetch_result ($resY,nota_fiscal);
						}else{
							$sql = "UPDATE tbl_pedido_item SET qtde_cancelada = $qtde_cancelar WHERE pedido_item = $cancelar;";
							
							$res = pg_query ($con,$sql);

							$sql = "INSERT INTO tbl_pedido_cancelado (
									pedido ,
									posto  ,
									fabrica,
									os     ,
									peca   ,
									qtde   ,
									motivo ,
									data
								)VALUES(
									$pedido,
									$posto,
									$login_fabrica,
									$os,
									$peca,	
									$qtde_cancelar,
									$aux_motivo,
									current_date
								);";
							$res = @pg_query ($con,$sql);
						}
				}
			}
		}
}

if (strlen($_GET['pedido']) > 0)  $pedido = trim($_GET['pedido']);
if (strlen($_POST['pedido']) > 0) $pedido = trim($_POST['pedido']);

if ($btn_acao == "gravar") {
	if (strlen($_POST['emissao']) > 0)
		$xemissao = "'". formata_data($_POST['emissao']) ."'";
	else
		$msg_erro = "Digite a data de emiss�o";
	
	if (strlen($_POST['saida']) > 0)
		$xsaida = "'". formata_data($_POST['saida']) ."'";
	else
		$msg_erro = "Digite a data de sa�da";

	if (strlen($_POST['previsao_chegada']) > 0)
		$xprevisao_chegada = "'". formata_data($_POST['previsao_chegada']) ."'";
	else
		$xprevisao_chegada = 'null';
	
	if (strlen($_POST['transportadora']) > 0)
		$xtransportadora = "'". $_POST['transportadora'] ."'";
	else
		$xtransportadora = 'null';
		//$msg_erro = "Selecione a transportadora."; // Retirada a obrigatoriedade a pedido da Ibratele, Lauro - 15/09/2005
	
	if (strlen($_POST['cfop']) > 0)
		$xcfop = "'". $_POST['cfop'] ."'";
	else
		$xcfop = 'null';
	
	if (strlen($_POST['total_nota']) > 0)
		$xtotal_nota = "'". str_replace(',','.',$_POST['total_nota']) ."'";
	else
		$msg_erro = "Digite o total da Nota Fiscal.";
	
	if (strlen($_POST['nota_fiscal']) > 0)
		$xnota_fiscal = "'". $_POST['nota_fiscal'] ."'";
	else
		$msg_erro = "Digite o n�mero da nota fiscal.";

	if (strlen ($msg_erro) == 0) {
		$res = pg_exec ($con,"BEGIN TRANSACTION");
		
		if (strlen ($faturamento) == 0) {
			#-------------- insere pedido ------------
			$sql = "INSERT INTO tbl_faturamento (
						fabrica          ,
						emissao          ,
						saida            ,
						transportadora   ,
						pedido           ,
						posto            ,
						previsao_chegada ,
						total_nota       ,
						cfop             ,
						nota_fiscal      
					) VALUES (
						$login_fabrica     ,
						$xemissao          ,
						$xsaida            ,
						$xtransportadora   ,
						$pedido            ,
						$posto             ,
						$xprevisao_chegada ,
						$xtotal_nota       ,
						$xcfop             ,
						$xnota_fiscal      
					)";
		}else {
			$sql = "UPDATE tbl_faturamento SET
						fabrica          = $login_fabrica,
						emissao          = $xemissao,
						saida            = $xsaida,
						transportadora   = $xtransportadora,
						pedido           = $pedido,
						posto            = $posto,
						previsao_chegada = $xprevisao_chegada,
						total_nota       = $xtotal_nota,
						cfop             = $xcfop,
						nota_fiscal      = $xnota_fiscal
					WHERE faturamento    = $faturamento
					AND   fabrica        = $login_fabrica";
		}
		//$msg_erro = "$sql";
		echo nl2br($sql);
		$res = pg_exec ($con,$sql);
		$msg_erro = pg_errormessage($con);

		if (strlen($msg_erro) == 0 and strlen($faturamento) == 0) {
			$res = pg_exec ($con,"SELECT CURRVAL ('seq_faturamento')");
			$faturamento = pg_result ($res,0,0);
			$msg_erro    = pg_errormessage($con);
		}
		if (strlen($msg_erro) == 0) {
				$qtde_item = $_POST['qtde_item'];
			for ($i = 0 ; $i < $qtde_item ; $i++) {
				$peca             = $_POST['peca_'.$i];
				$preco            = str_replace(',','.',$_POST['preco_'.$i]);
				$qtde             = $_POST['qtde_'.$i];
				$qtde_faturamento = $_POST['qtde_faturamento_'.$i];
				$pendente         = $_POST['pendente_'.$i];
				$pedido_item      = $_POST['item'.$i];
				if ($pendente == '0') {
					$xpendente = $qtde - $qtde_faturamento;
				}else{
					$xpendente = $pendente - $qtde_faturamento;
				}
				if ($pendente > 0) {
					if(strlen($qtde_faturamento) > 0 AND strlen($peca) > 0 AND strlen($msg_erro) == 0) {
						if($login_fabrica <> 51 AND $login_fabrica <> 59 AND $login_fabrica <> 65 AND $login_fabrica <> 43 AND $login_fabrica <> 80 AND $login_fabrica <> 88) {
							$sql = "INSERT INTO tbl_faturamento_item (
										faturamento,
										peca       ,
										preco      ,
										qtde       ,
										pendente   
									) VALUES (
										$faturamento     ,
										$peca            ,
										$preco           ,
										$qtde_faturamento,
										$xpendente        
									)";
						} else {
							$sql = "INSERT INTO tbl_faturamento_item (
										faturamento,
										peca       ,
										preco      ,
										qtde       ,
										pendente   ,
										pedido     ,
										pedido_item
									) VALUES (
										$faturamento     ,
										$peca            ,
										$preco           ,
										$qtde_faturamento,
										$xpendente       ,
										$pedido          ,
										$pedido_item
									)";
						}
						$res = pg_exec ($con,$sql);
						echo nl2br($sql);
						$msg_erro = pg_errormessage($con);

						if($login_fabrica == 51 OR $login_fabrica == 59 OR $login_fabrica == 65 OR $login_fabrica == 43 OR $login_fabrica == 80 OR $login_fabrica == 88){
							$sql = "SELECT fn_atualiza_pedido_item($peca,$pedido,$pedido_item,$qtde_faturamento);";
							$res = pg_exec ($con,$sql);
							$msg_erro .= pg_errormessage($con);

							$sql = "SELECT fn_atualiza_status_pedido($login_fabrica, $pedido);";
							$res = pg_exec ($con,$sql);
							$msg_erro .= pg_errormessage($con);
						}

						if (strlen($msg_erro) > 0) $linha_erro = $i;
						
					}
					if (strlen($msg_erro) > 0) $linha_erro = $i;
				}
			}
		}
	}
	if (strlen ($msg_erro) == 0 AND ($login_fabrica <> 51 AND $login_fabrica <> 59 AND $login_fabrica <> 65) AND ($login_fabrica <> 43) AND ($login_fabrica <> 80)) {
		$sql = "SELECT fn_posicao_pedido($login_fabrica)";
		$res = pg_exec ($con,$sql);
		$msg_erro = pg_errormessage($con);
	}

	if (strlen ($msg_erro) == 0) {
		$res = pg_exec ($con,"COMMIT TRANSACTION");
		//header ("Location: pedido_parametros.php");
		header ("Location: pedido_nao_faturado_consulta.php");
		exit;
	}else{
		$res = pg_exec ($con,"ROLLBACK TRANSACTION");
	}

}

#------------ Le Pedido da Base de dados ------------#
if (strlen ($pedido) > 0) {
	$sql = "SELECT  tbl_posto.posto          ,
					tbl_posto.cnpj           ,
					tbl_posto.nome           ,
					tbl_pedido.condicao      ,
					tbl_pedido.tabela        ,
					tbl_pedido.obs           ,
					tbl_pedido.tipo_pedido   ,
					tbl_pedido.tipo_frete    ,
					tbl_pedido.pedido_cliente,
					tbl_pedido.validade      ,
					tbl_pedido.entrega       ,
					tbl_pedido.linha         ,
					tbl_pedido.transportadora,
					tbl_linha.nome            AS nome_linha,
					tbl_tipo_pedido.descricao AS nome_tipo_pedido,
					tbl_tabela.descricao      AS nome_tabela,
					tbl_condicao.descricao    AS nome_condicao
			FROM    tbl_pedido
			JOIN    tbl_posto            USING (posto)
			LEFT JOIN    tbl_linha       USING (linha)
			LEFT JOIN    tbl_tipo_pedido USING (tipo_pedido)
			LEFT JOIN    tbl_tabela      USING (tabela)
			LEFT JOIN    tbl_condicao    USING (condicao)
			WHERE   tbl_pedido.pedido  = $pedido
			AND     tbl_pedido.fabrica = $login_fabrica";
	$res = pg_exec ($con,$sql);
	
	if (pg_numrows ($res) > 0) {
		$posto            = trim(pg_result ($res,0,posto));
		$cnpj             = trim(pg_result ($res,0,cnpj));
		$cnpj             = substr($cnpj,0,2) .".". substr($cnpj,2,3) .".". substr($cnpj,5,3) ."/". substr($cnpj,8,4) ."-". substr($cnpj,12,2);
		$nome             = trim(pg_result ($res,0,nome));
		$obs              = trim(pg_result ($res,0,obs));
		$tipo_frete       = trim(pg_result ($res,0,tipo_frete));
		$pedido_cliente   = trim(pg_result ($res,0,pedido_cliente));
		$validade         = trim(pg_result ($res,0,validade));
		$entrega          = trim(pg_result ($res,0,entrega));
		$nome_linha       = trim(pg_result ($res,0,nome_linha));
		$nome_tipo_pedido = trim(pg_result ($res,0,nome_tipo_pedido));
		$nome_tabela      = trim(pg_result ($res,0,nome_tabela));
		$nome_condicao    = trim(pg_result ($res,0,nome_condicao));
		$transportadora   = trim(pg_result ($res,0,transportadora));
#		$referencia       = trim(pg_result ($res,0,referencia));
#		$descricao        = trim(pg_result ($res,0,descricao));
	}

}


#---------------- Recarrega Form em caso de erro -------------
if (strlen ($msg_erro) > 0) {
	$pedido         = $_POST['pedido'];
	$cnpj           = $_POST['cnpj'];
	$nome           = $_POST['nome'];
	$condicao       = $_POST['condicao'];
	$tipo_frete     = $_POST['tipo_frete'];
	$tipo_pedido    = $_POST['tipo_pedido'];
	$pedido_cliente = $_POST['pedido_cliente'];
	$validade       = $_POST['validade'];
	$entrega        = $_POST['entrega'];
	$tabela         = $_POST['tabela'];
	$cnpj           = $_POST['cnpj'];
	$obs            = $_POST['obs'];
	$linha          = $_POST['linha'];
}

$layout_menu = "callcenter";
$title       = "Cadastro de Faturamento de Pedidos de Pe�as";

include "cabecalho.php";

?>

<script language="JavaScript">
function fnc_pesquisa_transportadora (xcampo, tipo)
{
	if (xcampo.value != "") {
		var url = "";
		url = "pesquisa_transportadora.php?campo=" + xcampo.value + "&tipo=" + tipo ;
		janela = window.open(url, "janela", "toolbar=no, location=yes, status=yes, scrollbars=yes, directories=no, width=500, height=400, top=0, left=0");
		janela.transportadora = document.frm_pedido.transportadora;
		janela.nome           = document.frm_pedido.transportadora_nome;
		janela.cnpj           = document.frm_pedido.transportadora_cnpj;
		janela.focus();
	}
}
</script>

<style type="text/css">

.menu_top {
	text-align: center;
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: x-small;
	font-weight: bold;
	border: 1px solid;
	color:#ffffff;
	background-color: #596D9B
}

.table {
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: x-small;
	text-align: center;
	border: 1px solid #d9e2ef;
}

.table_line {
	text-align: left;
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: normal;
	border: 0px solid;
}

.table_line2 {
	text-align: left;
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: normal;
	background-color: #CED7e7;
}

</style>

<? 
if (strlen ($msg_erro) > 0) {
?>
<table class="table" width="730" border="0" cellpadding="0" cellspacing="0" >
<tr>
<!-- class="menu_top" -->
	<td valign="middle" align="center" class='error'>
		<? echo $msg_erro ?>
	</td>
</tr>
</table>
<? } 
//echo $msg_debug ;
?>


<!-- ------------- Formul�rio ----------------- -->
<form name="frm_pedido" method="post" action="<? echo $PHP_SELF ?>">
<input type="hidden" name="pedido" value="<? echo $pedido ?>">
<input type="hidden" name="posto" value="<? echo $posto ?>">
<? if($login_fabrica == 51 AND $login_fabrica == 59 AND $login_fabrica == 65 AND $login_fabrica == 43){
	//echo "<input type='hidden' name='faturamento' value='$faturamento'>";
}?>
<table class="table" width='600' align='center' border='0' cellspacing='3' cellpadding='3'>
<tr class="menu_top">
	<td align='center'>
		<font face='arial, verdana, times' color='#ffffff'><b>
		C�digo ou CNPJ
		</b></font>
	</td>
	<td align='center'>
		<font face='arial, verdana, times' color='#ffffff'><b>
		Raz�o Social
		</b></font>
	</td>
</tr>

<tr class="table_line">
	<td align='center'>
		<? echo $cnpj ?>&nbsp;
	</td>
	<td align='center'>
		<? echo $nome ?>&nbsp;
	</td>
</tr>
</table>

<!--
<table class="table" width='600' align='center' border='0' cellspacing='3' cellpadding='3'>
<tr class="menu_top">
	<td align='center'>
		<b>
		Linha
		</b>
	</td>
	<td align='center'>
		<b>
		Refer�ncia do Produto
		</b>
	</td>
	<td align='center'>
		<b>
		Descri��o do Produto
		</b>
	</td>
</tr>

<tr class="table_line">
	<td align='center'>
		<? echo $nome_linha; ?>&nbsp;
	</td>
	<td align='center'>
		<? echo $referencia ?>&nbsp;
	</td>
	<td align='center'>
		<? echo $descricao ?>&nbsp;
	</td>
</tr>
</table>
-->

<table class="table" width='600' align='center' border='0' cellspacing='3' cellpadding='3'>
<tr class="menu_top">
	<td align='center'>
		<b>
			Tipo do Pedido
		</b>
	</td>
	<td align='center'>
		<b>
			Tabela de Pre�os
		</b>
	</td>
	<td align='center'>
		<b>
			Condi��o de Pagamento
		</b>
	</td>
	<td align='center'>
		<b>
			Tipo de Frete
		</b>
	</td>
</tr>

<tr class="table_line">
	<td align='center'>
		<? echo $nome_tipo_pedido; ?>&nbsp;
	</td>
	<td align='center'>
		<? echo $nome_tabela; ?>&nbsp;
	</td>
	<td align='center'>
		<? echo $nome_condicao; ?>&nbsp;
	</td>
	<td align='center'>
		<? echo $tipo_frete; ?>&nbsp;
	</td>
</tr>
</table>

<table class="table" width='600' align='center' border='0' cellspacing='3' cellpadding='3'>
<tr class="menu_top">
	<td align='center'>
		<b>
		Pedido Cliente
		</b>
	</td>
	<td align='center'>
		<b>
		Validade
		</b>
	</td>
	<td align='center'>
		<b>
		Entrega
		</b>
	</td>
</tr>

<tr class="table_line">
	<td align='center'>
		<? echo $pedido_cliente ?>&nbsp;
	</td>
	<td align='center'>
		<? echo $validade ?>&nbsp;
	</td>
	<td align='center'>
		<? echo $entrega ?>&nbsp;
	</td>
</tr>
</table>

<table class="table" width='600' align='center' border='0' cellspacing='3' cellpadding='3'>
<tr class="menu_top">
	<td align='center'>
		<b>
		Mensagem
		</b>
	</td>
</tr>
<tr>
	<td>
		<? echo $obs; ?>&nbsp;
	</td>
</tr>
</table>

<BR>
<HR width="600">
<BR>

<table class="table" width='600' align='center' border='0' cellspacing='3' cellpadding='3'>
<tr class="menu_top">
	<td align='center' width='33%'>
		<b>
		Emiss�o
		</b>
	</td>
	<td align='center' width='33%'>
		<b>
		Sa�da
		</b>
	</td>
	<td align='center' width='34%'>
		<b>
		Previs�o de chegada
		</b>
	</td>
</tr>
<tr>
	<td>
		<INPUT TYPE="text" NAME="emissao" value="<? echo $emissao ?>" size="10" maxlength="10">
	</td>
	<td>
		<INPUT TYPE="text" NAME="saida" value="<? echo $saida ?>" size="10" maxlength="10">
	</td>
	<td>
		<INPUT TYPE="text" NAME="previsao_chegada" value="<? echo $previsao_chegada ?>" size="10" maxlength="10">
	</td>
</tr>
</table>

<table class="table" width='600' align='center' border='0' cellspacing='3' cellpadding='3'>
<tr class="menu_top">
	<td align='center'>
		<b>
		CFOP
		</b>
	</td>
	<td align='center'>
		<b>
		Transportadora
		</b>
	</td>
</tr>
<tr>
	<td>
		<INPUT TYPE="text" NAME="cfop" value="<? echo $cfop ?>" size="10">
	</td>
	<td>
<?
	echo "		<input type='hidden' name='transportadora' value=''>";
	echo "		<input type='hidden' name='transportadora_codigo' value='$transportadora_codigo'>";
	echo "		CNPJ <input type='text' name='transportadora_cnpj' size='14' maxlength='10' value='$transportadora_cnpj' class='textbox' >&nbsp;<img src='../imagens/btn_buscar5.gif' border='0' align='absmiddle' onclick=\"javascript: fnc_pesquisa_transportadora (document.frm_pedido.transportadora_cnpj,'cnpj')\" style='cursor:pointer;'>";
	echo "		Nome <input type='text' name='transportadora_nome' size='30' maxlength='50' value='$transportadora_nome' class='textbox' >&nbsp;<img src='../imagens/btn_buscar5.gif' border='0' align='absmiddle' onclick=\"javascript: fnc_pesquisa_transportadora (document.frm_pedido.transportadora_nome,'nome')\" style='cursor:pointer;'>";

/*
	$sql = "SELECT	tbl_transportadora.transportadora        ,
					tbl_transportadora.cnpj                  ,
					tbl_transportadora.nome                  ,
					tbl_transportadora_fabrica.codigo_interno
			FROM	tbl_transportadora
			JOIN	tbl_transportadora_fabrica USING(transportadora)
			WHERE	tbl_transportadora_fabrica.fabrica = $login_fabrica
			AND		tbl_transportadora_fabrica.ativo  = 't' ";
	$res = pg_exec ($con,$sql);

	if (pg_numrows ($res) > 0) {
		echo "		<select name='transportadora'>";
		echo "			<option selected></option>";
		for ($i = 0 ; $i < pg_numrows ($res) ; $i++) {
			echo "<option value='".pg_result($res,$i,transportadora)."' ";
			if ($transportadora == pg_result($res,$i,transportadora) ) echo " selected ";
			echo ">";
			echo pg_result($res,$i,codigo_interno) ." - ".pg_result($res,$i,nome);
			echo "</option>\n";
		}
		echo "		</select>";
	}else{
		echo "&nbsp;";
	}
*/
?>
	</td>
</tr>
</table>

<table class="table" width='600' align='center' border='0' cellspacing='3' cellpadding='3'>
<tr class="menu_top">
	<td align='center'>
		<b>
		Nota Fiscal
		</b>
	</td>
	<td align='center'>
		<b>
		Total Nota Fiscal
		</b>
	</td>
</tr>
<tr>
	<td>
		<INPUT TYPE="text" NAME="nota_fiscal" value="<? echo $nota_fiscal; ?>" size="12">
	</td>
	<td>
		<INPUT TYPE="text" NAME="total_nota" value="<? echo $total_nota; ?>" size="12" style="text-align:right">
	</td>
</tr>
</table>

<p>
		<table width="600" border="0" cellspacing="3" cellpadding="0" align='center'>
		<tr height="20" class="menu_top">
			<td colspan='8'>Se o item n�o foi atendido pela nota fiscal, favor deixar a quantidade faturada sem preenchimento.</td>
		</tr>

		<tr height="20" class="menu_top">
			<td align='center'>Refer�ncia Componente</td>
			<td align='center'>Descri��o Componente</td>
			<td align='center'>Qtde</td>
			<td align='center'>Qtde Cancelada</td>
			<td align='center'>Pendente</td>
			<td align='center'>Qtde NF</td>
			<td align='center'>Valor</td>
			<td align='center'>A��o	</td>
		</tr>
		
<?
		if (strlen($pedido) > 0) {
			$sql = "SELECT      tbl_peca.peca,
								tbl_pedido_item.pedido_item
					FROM        tbl_pedido_item
					JOIN        tbl_peca   USING (peca)
					JOIN        tbl_pedido USING (pedido)
					WHERE       tbl_pedido_item.pedido = $pedido
					ORDER BY    tbl_pedido_item.pedido_item;";
			$ped = pg_exec ($con,$sql);
			$qtde_item = @pg_numrows($ped);
		}

		if (strlen($pedido) > 0) {
			echo "<input class='frm' type='hidden' name='qtde_item' value='$qtde_item'>";
			
			$botao = 0;

			for ($i = 0 ; $i < $qtde_item ; $i++) {
				if (strlen($pedido) > 0) {
					if (@pg_numrows($ped) > 0) {
						$peca        = trim(@pg_result($ped,$i,peca));
						$pedido_item = trim(@pg_result($ped,$i,pedido_item));
						$qtde_faturamento = $_POST['qtde_faturamento_'.$i];
					}
/*
					$sql = "SELECT  tbl_pedido_item.pedido_item,
									tbl_pedido_item.qtde       ,
									tbl_pedido_item.preco      ,
									tbl_peca.referencia        ,
									tbl_peca.descricao
							FROM    tbl_pedido_item
							JOIN    tbl_peca USING (peca)
							WHERE   tbl_pedido_item.pedido = $pedido
							AND     tbl_pedido_item.peca   = $peca";

					$sql = "SELECT  tbl_pedido_item.pedido_item                    ,
									tbl_pedido_item.qtde                           ,
									tbl_pedido_item.preco                          ,
									tbl_peca.referencia                            ,
									tbl_peca.descricao                             ,
									tbl_faturamento.nota_fiscal                    ,
									tbl_faturamento_item.pendente  AS pendente     ,
									sum(tbl_faturamento_item.qtde) AS qtde_faturada
							FROM    tbl_pedido_item
							JOIN    tbl_peca USING (peca)
							LEFT JOIN  tbl_faturamento_item
									ON tbl_faturamento_item.peca = tbl_pedido_item.peca
							WHERE   tbl_pedido_item.pedido = $pedido
							AND     tbl_pedido_item.peca   = $peca
							GROUP BY tbl_pedido_item.pedido_item   ,
									tbl_pedido_item.qtde           ,
									tbl_pedido_item.preco          ,
									tbl_peca.referencia            ,
									tbl_peca.descricao             ,
									tbl_faturamento.nota_fiscal    ,
									tbl_faturamento_item.pendente";
*/

					$sql = "SELECT  tbl_pedido_item.pedido_item                    ,
									tbl_pedido_item.qtde                           ,
									tbl_pedido_item.qtde_cancelada                 ,
									tbl_pedido_item.preco                          ,
									tbl_peca.referencia                            ,
									tbl_peca.descricao                             ,
									tbl_faturamento.nota_fiscal                    ,
									tbl_faturamento_item.pendente  AS pendente     ,
									tbl_faturamento_item.qtde      AS qtde_faturada
							FROM    tbl_pedido_item
							JOIN    tbl_peca USING (peca)
							LEFT JOIN  tbl_faturamento_item
									ON tbl_faturamento_item.peca = tbl_pedido_item.peca
									AND tbl_faturamento_item.pedido_item = tbl_pedido_item.pedido_item
							LEFT JOIN  tbl_faturamento 
									ON tbl_faturamento.faturamento = tbl_faturamento_item.faturamento
							WHERE   tbl_pedido_item.pedido = $pedido
							AND     tbl_pedido_item.peca   = $peca
							AND     tbl_pedido_item.pedido_item = $pedido_item";
					$aux_ped = pg_exec ($con,$sql);
					
					if (pg_numrows($aux_ped) > 0) {
						$nota_fiscal     = trim(pg_result($aux_ped,0,nota_fiscal));
						$item            = trim(pg_result($aux_ped,0,pedido_item));
						$peca_referencia = trim(pg_result($aux_ped,0,referencia));
						$peca_descricao  = trim(pg_result($aux_ped,0,descricao));
						$qtde            = trim(pg_result($aux_ped,0,qtde));
						$qtde_cancelada  = trim(pg_result($aux_ped,0,qtde_cancelada));
						$preco           = trim(pg_result($aux_ped,0,preco));
						$pendente        = trim(pg_result($aux_ped,0,pendente));
						$qtde_faturada   = trim(pg_result($aux_ped,0,qtde_faturada));
					}
					
					if (strlen($pendente) > 0)
						$xpendente = $pendente;
					else
						$xpendente = $qtde - $qtde_faturada - $qtde_cancelada;
				}
				echo $linha_erro;

				echo "<input type='hidden' name='item$i' value='$item'>\n";
				$cor = ($i % 2 ) ? '#F1F4FA' : "#CCCCCC";
				echo "<tr ";
				if ($linha_erro == $i and strlen ($msg_erro) > 0) {
					echo "bgcolor='#ffcccc'";
				}else {
					echo "bgcolor='$cor'";
				}
				echo ">\n";
				echo "<td align='left'><font size='-1'>$peca_referencia</td>\n";
				echo "<td align='left' nowrap><font size='-1'>$peca_descricao</td>\n";
				echo "<td align='center'>\n";
				echo $qtde;
				echo "<input type='hidden' name='peca_$i' size=5 value='$peca'>\n";
				echo "<input type='hidden' name='qtde_$i' size=5 value='$qtde'>\n";
				echo "<input type='hidden' name='pendente_$i' size=5 value='$xpendente'>\n";
				echo "<input type='hidden' name='qtde_faturada_$i' size=5 value='$qtde_faturada'>\n";
				echo "<input type='hidden' name='preco_$i' size='5' value='$preco'>\n";
				echo "</td>\n";
				echo "<td align='center'>$qtde_cancelada</td>\n";
				if ($qtde == $qtde_faturada) {
					echo "<td align='center'>0</td>\n";
				}else{
					echo "<td align='center'>$xpendente</td>\n";
				}
				
				if ($pendente == 0 AND strlen($pendente) > 0){
					echo "<td align='center'>$qtde</td>\n";
					echo "<td align='right' style='padding-right:10px;'>".number_format($preco,'2',',','.')."</td>\n";
					$botao++;
				}else{
					if ($xpendente>0) {
						echo "<td align='center'><input class='frm' type='text' name='qtde_faturamento_$i' size=5 value='$qtde_faturamento'></td>\n";
						echo "<td align='center'><input type='text' class='frm' name='preco_$i' size='5' value='".number_format($preco,'2',',','.')."' style='text-align:right;'></td>\n";
					}
					else {
						echo "<td align='center' colspan='3'>item cancelado total</d>";
					}
				}
					if ($xpendente>0) {
						echo "<td align='left' nowrap'><font size='-1'> ";
						echo "Qtde Cancelar <input type='text' size='5' name='qtde_a_cancelar_$i' class='frm'> <br><br>";
						echo "Motivo: <input type='text' name='motivo_$i' class='frm'>";
						echo "<a href='javascript: if(confirm(\"Deseja cancelar este item do pedido: $peca_descricao?\")) window.location = \"$PHP_SELF?cancelar=$item&pedido=$pedido&os=$os&motivo=\"+document.frm_pedido.motivo_$i.value+\"&qtde_cancelar=\"+document.frm_pedido.qtde_a_cancelar_$i.value'>";
						echo " <img src='imagens/btn_x.gif'><font size='1'>Cancelar</font></a>";
						echo "</td>";
					}

				echo "</tr>\n";
			}
		}
?>
		</table>
	</td>
	<td><img height="1" width="16" src="imagens/spacer.gif"></td>
</tr>
<BR>
<tr>
	<td height="27" valign="middle" align="center" colspan="3" bgcolor="#FFFFFF">
<?
	if ($qtde_item <> $botao){
?>
		<input type='hidden' name='btn_acao' value=''>
		<img src="imagens_admin/btn_gravar.gif" onclick="javascript: if (document.frm_pedido.btn_acao.value == '' ) { document.frm_pedido.btn_acao.value='gravar' ; document.frm_pedido.submit() } else { alert ('Aguarde submiss�o') }" ALT="Gravar formul�rio" border='0' style="cursor:pointer;">
<?
	}else{
?>
		<img src="imagens_admin/btn_voltar.gif" onclick="javascript: history.back();" ALT="Voltar" border='0' style="cursor:pointer;">
<?
	}
?>
	</td>
</tr>

</form>

</table>

<p>

<? include "rodape.php"; 
/*
$sql = "DELETE FROM tbl_faturamento_item WHERE faturamento = 1084";
$ped = pg_exec ($con,$sql);

$sql = "DELETE FROM tbl_faturamento WHERE faturamento = 1084";
$ped = pg_exec ($con,$sql);
*/
?>