<?php
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';

$admin_privilegios='cadastros';
include 'autentica_admin.php';

include 'funcoes.php';
include '../class/AuditorLog.php';

if (strlen($_POST["btnacao"]) > 0) {
	$btnacao = trim($_POST["btnacao"]);
}

if (strlen($_GET["item"]) > 0) {
	$item_codigo = trim($_GET["item"]);
}

if ($btnacao == "deletar" and strlen($item_codigo) > 0 ) {
	$res = pg_exec ($con,"BEGIN TRANSACTION");

	$sql = "DELETE FROM tbl_esmaltec_item_servico
			WHERE  tbl_esmaltec_item_servico.esmaltec_item_servico = $item_codigo";
	$res = pg_exec ($con,$sql);

	$msg_erro = pg_errormessage($con);

	if (strlen ($msg_erro) == 0) {
		###CONCLUI OPERA��O DE INCLUS�O/EXLUS�O/ALTERA��O E SUBMETE
		$res = pg_exec ($con,"COMMIT TRANSACTION");
		
		header ("Location: $PHP_SELF?msg=Exclu�do com Sucesso");
		exit;
	}else{
		###ABORTA OPERA��O DE INCLUS�O/EXLUS�O/ALTERA��O E RECARREGA CAMPOS
		
		$codigo       = $_POST["codigo_item"];
		$descricao    = $_POST["descricao_item"];
		$ativo        = $_POST["ativo"];
		$valor        = $_POST["valor_item"];
		
		$res = pg_exec ($con,"ROLLBACK TRANSACTION");
	}
}

if ($btnacao == "gravar") {
	
	if (strlen($_POST["codigo_item"]) > 0) {
		$codigo = "'". trim($_POST["codigo_item"]) ."'";
	}else{
		$msg_erro .= "Informe o C�digo do Item <br />";
	}

	if (strlen($_POST["descricao_item"]) > 0) {
		$descricao = "'". trim($_POST["descricao_item"]) ."'";
	}else{
		$msg_erro .= "Informe a Descri��o do Item <br />";
	}
	
	if (strlen($_POST["valor_item"]) > 0) {
		$valor = "'". trim($_POST["valor_item"]) ."'";
	}else{
		$msg_erro .= "Informe o Valor do Item";
	}
	
	if (strlen($_POST["ativo"]) > 0) {
		$ativo = 't';
	}else{
		$ativo = 'f';
	}

	if($login_fabrica == 30){
		$auditorLogInserir = new AuditorLog("insert");
		$auditorLogAlterar = new AuditorLog();
		$valor = str_replace(array('.', ','), array('', '.'), $valor);
	}

	if (strlen($msg_erro) == 0) {
		$res = pg_exec ($con,"BEGIN TRANSACTION");
		
		if (strlen($item_codigo) == 0) {
			###INSERE NOVO REGISTRO
			$sql = "INSERT INTO tbl_esmaltec_item_servico (
						codigo  ,
						descricao    ,
						ativo  ,
						valor
					) VALUES (
						$codigo,
						$descricao,
						'$ativo',
						$valor
					) RETURNING esmaltec_item_servico;";

			$res = pg_exec($con,$sql);
			$msg_erro = pg_errormessage($con);

			if($login_fabrica == 30){
				$aux_esmaltec_item_servico = pg_fetch_result($res, 0, esmaltec_item_servico);	

				$sqlAuditorInserir = "SELECT codigo, descricao, ativo, valor FROM tbl_esmaltec_item_servico WHERE esmaltec_item_servico = {$aux_esmaltec_item_servico}";

				$auditorLogInserir->retornaDadosSelect($sqlAuditorInserir);	

				$auditorLogInserir->enviarLog('insert', 'tbl_esmaltec_item_servico',"{$login_fabrica}*{$aux_esmaltec_item_servico}");
			}

			$msg = "Gravado com Sucesso!";

		}else{
			###ALTERA REGISTRO
			if($login_fabrica == 30){
				$sqlAuditorLogAlterar = "SELECT codigo, descricao, ativo, valor FROM tbl_esmaltec_item_servico WHERE esmaltec_item_servico = {$item_codigo}";

				$auditorLogAlterar->retornaDadosSelect($sqlAuditorLogAlterar);
			}

			$sql = "UPDATE tbl_esmaltec_item_servico SET
					codigo      = $codigo,
					descricao   = $descricao,
					ativo       = '$ativo',
					valor       = $valor
			WHERE  tbl_esmaltec_item_servico.esmaltec_item_servico = $item_codigo";

			$res = pg_exec($con,$sql);
			$msg_erro = pg_errormessage($con);

			if($login_fabrica == 30){
				$sqlAuditorLogAlterar = "SELECT codigo, descricao, ativo, valor FROM tbl_esmaltec_item_servico WHERE esmaltec_item_servico = {$item_codigo}";
				
				$auditorLogAlterar->retornaDadosSelect($sqlAuditorLogAlterar);

				$auditorLogAlterar->enviarLog('update', 'tbl_esmaltec_item_servico',"{$login_fabrica}*{$item_codigo}");
			}

			$msg = "Atualizado com Sucesso!";
		}
	}

	if (strlen ($msg_erro) == 0) {
		$res = pg_exec ($con,"COMMIT TRANSACTION");
		
		header ("Location: $PHP_SELF?msg=".$msg."");
		exit;
	}else{
		$codigo       = $_POST["codigo_item"];
		$descricao    = $_POST["descricao_item"];
		$ativo        = $_POST["ativo"];
		$valor        = $_POST["valor_item"];
		
		$res = pg_exec ($con,"ROLLBACK TRANSACTION");
	}
}

###CARREGA REGISTRO
if (strlen($item_codigo) > 0) {

	$sql = "SELECT  tbl_esmaltec_item_servico.codigo    ,
					tbl_esmaltec_item_servico.descricao  ,
					tbl_esmaltec_item_servico.ativo   ,
					tbl_esmaltec_item_servico.valor
			FROM    tbl_esmaltec_item_servico
			WHERE  tbl_esmaltec_item_servico.esmaltec_item_servico = $item_codigo";

	$res = pg_exec ($con,$sql);

	if (pg_numrows($res) > 0) {
		$codigo    = trim(pg_result($res,0,codigo));
		$descricao = trim(pg_result($res,0,descricao));
		$ativo     = trim(pg_result($res,0,ativo));
		$valor     = trim(pg_result($res,0,valor));		
	}
}

$msg = $_GET['msg'];
$layout_menu = 'cadastro';
$title = 'CADASTRO DE ITEM DE SERVI�O';
include 'cabecalho_new.php';

$plugins = array(
	"datepicker",
	"mask",
	"price_format",
	"shadowbox",	
	"dataTable"
);

include("plugin_loader.php");
	
?>

<style type='text/css'>
.titulo_tabela{
	background-color:#596d9b;
	font: bold 14px "Arial";
	color:#FFFFFF;
	text-align:center;
}


.titulo_coluna{
	background-color:#596d9b;
	font: bold 11px "Arial";
	color:#FFFFFF;
	text-align:center;
}



.msg_erro{
	background-color:#FF0000;
	font: bold 16px "Arial";
	color:#FFFFFF;
	text-align:center;
}

.sucesso{
    background-color:#008000;
    font: bold 14px "Arial";
    color:#FFFFFF;
    text-align:center;
}


.formulario{
	background-color:#D9E2EF;
	font:11px Arial;
	text-align:left;
}

.subtitulo{

	background-color: #7092BE;
	font:bold 11px Arial;
	color: #FFFFFF;
}

table.tabela tr td{
	font-family: verdana;
	font-size: 11px;
	border-collapse: collapse;
	border:1px solid #596d9b;
}

.texto_avulso{
	font: 14px Arial; color: rgb(89, 109, 155);
	background-color: #d9e2ef;
	text-align: center;
	width:700px;
	margin: 0 auto;
}

.espaco{
	padding:0 0 0 50px;
}
</style>

<script type='text/javascript' src='js/jquery.alphanumeric.js'></script>
<script type='text/javascript'>	
	$(function(){
        Shadowbox.init();
		$('#valor_item').numeric({allow:".,"});

	    $('#valor_item').priceFormat({
            prefix: '',
            thousandsSeparator: '.',
            centsSeparator: ',',
            centsLimit: parseInt(2)
        });

	});
</script>
<?php
if (strlen($msg_erro) > 0) {
?>
    <div class="alert alert-error">
		<h4><?php echo $msg_erro; ?></h4>
    </div>
<?php
} if (strlen($msg) > 0) {
?>
    <div class="alert alert-success">
		<h4><?php echo $msg; ?></h4>
    </div>
<? }	
	$labelBtnGravar = strlen($item_codigo) > 0 ? "Alterar" : "Gravar";
	$checkAtivoDisable = in_array($item_codigo, array(35, 36)) ? "DISABLED" : "ENABLED";
?>

<form name="frm_cad_item" method="post" action="<? $PHP_SELF ?>" align='center' class='form-search form-inline tc_formulario'>	
		<div class='titulo_tabela '>Cadastro</div>
		<br/>
		<div class='row-fluid'>
			<div class='span2'></div>
			<div class='span4'>
				<div class='control-group'>
					<label class='control-label' for='codigo'>C�digo</label>
					<div class='controls controls-row'>
						<div class='span4'>							
							<input type='text' name='codigo_item' id='codigo_item' size='12' class='frm' value='<? echo $codigo; ?>'  maxlength='25'>
						</div>
					</div>
				</div>
			</div>
			<div class='span4'>
				<div class='control-group'>
					<label class='control-label' for='descricao'>Descri��o</label>
					<div class='controls controls-row'>
						<div class='span4'>							
							<input type='text' name='descricao_item' id='descricao_item' size='50' class='frm' value='<? echo $descricao; ?>' maxlength='100'>
						</div>
					</div>
				</div>
			</div>
			<div class='span2'></div>
		</div>
		<div class='row-fluid'>
			<div class='span2'></div>
			<div class='span4'>		
				<div class='control-group'>
					<label class='control-label' for='valor'>Valor</label>
					<div class='controls controls-row'>
						<div class='span4'>
							<? if($login_fabrica == 30) { $valor = number_format($valor, 2, ',', '.'); } ?>		
							<input type='text' name='valor_item' id='valor_item' size='10' class='frm' value='<? echo $valor; ?>'>
						</div>
					</div>
				</div>
			</div>
			<div class='span4'>
				<div class='control-group'>
					<label class='control-label' for='valor'>Ativo</label>
					<div class='controls controls-row'>
						<div class='span4'>							
							<input type='checkbox' value='t' name='ativo' <?=$checkAtivoDisable?> class='frm' <? if($ativo == 't') echo 'checked';?>>
						</div>
					</div>
				</div>
			</div>			
			<div class='span2'></div>
		</div>				
		<input type='hidden' name='btnacao' value=''>
		<input type='hidden' name='item_codigo' value='<? echo $item_codigo; ?>'>
		<p><br/>
			<input class='btn' type="button" value="<?=$labelBtnGravar?>" ONCLICK="javascript: if (document.frm_cad_item.btnacao.value == '' ) { document.frm_cad_item.btnacao.value='gravar' ; document.frm_cad_item.submit() } else { alert ('Aguarde submiss�o') }" ALT="Gravar formul�rio" border='0'>
			<input class='btn' type="button" value="Limpar" ONCLICK="javascript: if (document.frm_cad_item.btnacao.value == '' ) { document.frm_cad_item.btnacao.value='reset' ; window.location='<? echo $PHP_SELF ?>' } else { alert ('Aguarde submiss�o') }" ALT="Limpar campos" border='0' >						
		</p><br/>
</form>
<br>

<?php
if (strlen ($item_codigo) == 0) {
	$sql = "SELECT  tbl_esmaltec_item_servico.esmaltec_item_servico,
					tbl_esmaltec_item_servico.codigo    ,
					tbl_esmaltec_item_servico.descricao  ,
					tbl_esmaltec_item_servico.ativo   ,
					tbl_esmaltec_item_servico.valor
			FROM    tbl_esmaltec_item_servico ORDER BY descricao";

	$res = pg_exec ($con,$sql);
	$total = pg_numrows($res);
	if ($total > 0) { ?>
		<table  align='center' width='700' border='0' cellpadding='2' cellspacing='1' class='table table-striped table-bordered table-hover table-large'>
			<thead>
				<tr class='titulo_coluna'>
					<td nowrap>C�digo</td>
					<td nowrap>Descri��o</td>
					<td nowrap align='right'>Valor</td>
					<td nowrap>Status</td>				
					<? if($login_fabrica == 30) { ?>
						<td nowrap>Auditoria</td>
					<? } ?>
				</tr>
			</thead>
			<tbody>
		<?
		for ($x = 0 ; $x < $total; $x++){
			$codigo_item          = trim(pg_result($res,$x,esmaltec_item_servico));
			$descricao            = trim(pg_result($res,$x,descricao));
			$codigo               = trim(pg_result($res,$x,codigo));
			$ativo                = trim(pg_result($res,$x,ativo));
			$valor                = trim(pg_result($res,$x,valor));

			$cor = ($x % 2 == 0) ? "#F7F5F0" : "#F1F4FA";
		
			echo "<tr bgcolor='$cor'>";
			echo "<td class='tac' nowrap><a href='$PHP_SELF?item=$codigo_item'>$codigo</a></td>";
			echo "<td class='tac' nowrap align='left'><a href='$PHP_SELF?item=$codigo_item'>$descricao</a></td>";
			if($login_fabrica == 30){
				$valor = 'R$ ' . number_format($valor, 2, ',', '.');
			}
			echo "<td class='tac' nowrap align='right'>$valor</td>";
			if($ativo == 't'){
				echo "<td class='tac' nowrap>Ativo</td>";
			}
			else{
				echo "<td class='tac' nowrap>Inativo</td>";
			}
			
			if($login_fabrica == 30){
				echo "<td class='tac' nowrap>
						<a rel='shadowbox' href='relatorio_log_alteracao_new.php?parametro=tbl_esmaltec_item_servico&id=$codigo_item' class='link-log' name='btnAuditorLog'>Visualizar Log</a>
					  </td>";
			}

			echo "</tr>";
		}
		echo "</tbody></table>";
	}
}

echo "<br>";

include "rodape.php";
?>
