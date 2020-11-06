<?
include '../dbconfig.php';
include '../includes/dbconnect-inc.php';
include '../includes/funcoes.php';

$admin_privilegios="gerencia";
include 'autentica_admin.php';
include "../monitora.php";


$meses = array(1 => "Janeiro", "Fevereiro", "Mar�o", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");

if ($btn_finalizar == 1) {
	
	if(strlen($_POST["mostra_peca"]) > 0)
		$mostra_peca = trim($_POST["mostra_peca"]);

	if(strlen($_POST["classificacao"]) > 0) $classificacao = trim($_POST["classificacao"]);
	
	if(strlen($_POST["linha"]) > 0) $linha = trim($_POST["linha"]);

	if(strlen($_POST["estado"]) > 0){
		$estado = trim($_POST["estado"]);
		$mostraMsgEstado = "<br>no ESTADO $estado";
	}

	if($login_fabrica == 20 and $pais !='BR'){
		if(strlen($_POST["pais"]) > 0) $pais = trim($_POST["pais"]);
	}
	$tipo_os = trim($_POST['tipo_os']);

	$codigo_posto = "";
	if(strlen($_POST["codigo_posto"]) > 0) $codigo_posto = trim($_POST["codigo_posto"]);

	$exceto_posto = $_POST["exceto_posto"];

	$produto_referencia = trim($_POST['produto_referencia']);
	$produto_descricao  = trim($_POST['produto_descricao']) ;
	$multiplo           = trim($_POST['radio_qtde_produtos']);

	if(strlen($produto_referencia)>0 and strlen($produto_descricao)>0){
		$sql = "SELECT produto
				from tbl_produto
				join tbl_familia using(familia)
				where tbl_familia.fabrica = $login_fabrica
				and tbl_produto.referencia = '$produto_referencia'";
		$res = pg_exec($con,$sql);
		if(pg_numrows($res)>0){
			$produto = pg_result($res,0,produto);
		}
	}

	if (strlen($erro) == 0) {
		$fnc = @pg_exec($con,"SELECT fnc_formata_data('$data_inicial')");
		if (strlen ( pg_errormessage ($con) ) > 0) $erro = pg_errormessage ($con) ;
		if (strlen($erro) == 0)                    $aux_data_inicial = @pg_result ($fnc,0,0);
		else									   $erro = "Data Inv�lida";
	}
	if (strlen($erro) == 0) {
		$fnc = @pg_exec($con,"SELECT fnc_formata_data('$data_final')");
		if (strlen ( pg_errormessage ($con) ) > 0) $erro = pg_errormessage ($con) ;
		if (strlen($erro) == 0)                    $aux_data_final = @pg_result ($fnc,0,0);
		else									   $erro = "Data Inv�lida";
	}

	$replicar      = $_POST['PickList'];

	if (count($replicar)>0 and $multiplo == 'muitos'){ // HD 123856
		$array_produto = array();
		$produto_lista = array();
		for ($i=0;$i<count($replicar);$i++){
			$p = trim($replicar[$i]);
			if (strlen($p) > 0) {
				$sql = "SELECT  tbl_produto.produto,
								tbl_produto.referencia,
								tbl_produto.descricao
					from tbl_produto
					join tbl_familia using(familia)
					where tbl_familia.fabrica = $login_fabrica
					and tbl_produto.referencia = '$p'";
				$res = pg_exec($con,$sql);
				if(pg_numrows($res)>0){
					$multi_produto    = trim(pg_result($res,0,produto));
					$multi_referencia = trim(pg_result($res,0,referencia));
					$multi_descricao  = trim(pg_result($res,0,descricao));
					array_push($array_produto,$multi_produto);
					array_push($produto_lista,array($multi_produto,$multi_referencia,$multi_descricao));
				}
			}
		}
		$lista_produtos = implode($array_produto,",");
	}

	if (strlen($erro) == 0) $listar = "ok";
	
	if(!empty($exceto_posto)) {
		$checked = " CHECKED ";
	}

	if (strlen($erro) > 0) {
		$data_inicial       = trim($_POST["data_inicial_01"]);
		$data_final         = trim($_POST["data_final_01"]);
		$linha              = trim($_POST["linha"]);
		$estado             = trim($_POST["estado"]);
		$tipo_pesquisa      = trim($_POST["tipo_pesquisa"]);
		$pais               = trim($_POST["pais"]);
		$origem             = trim($_POST["origem"]);
		$criterio           = trim($_POST["criterio"]);
		$produto_referencia = trim($_POST['produto_referencia']); // HD 2003 TAKASHI
		$produto_descricao  = trim($_POST['produto_descricao']) ; // HD 2003 TAKASHI
		$tipo_os            = trim($_POST['tipo_os']);
		$exceto_posto       = $_POST["exceto_posto"];

		//$msg_erro  = "<b>Foi(foram) detectado(s) o(s) seguinte(s) erro(s): </b><br>";
		$msg_erro = $erro;
	}
}

$layout_menu = "gerencia";
$title = "RELAT�RIO - FIELD CALL-RATE : LINHA DE PE�AS";

include "../cabecalho.php";

?>

<script language="JavaScript">

function AbrePeca(peca,data_inicial,data_final,linha,estado,posto,produto,pais,marca,tipo_data,aux_data_inicial,aux_data_final,exceto_posto){
	janela = window.open("fcr_pecas_item.php?peca=" + peca + "&data_inicial=" + data_inicial + "&data_final=" + data_final + "&linha=" + linha + "&estado=" + estado +"&posto=" + posto +"&produto="+ produto + "&pais=" + pais +"&marca=" + marca + "&tipo_data=" + tipo_data +"&aux_data_inicial="+aux_data_inicial+"&aux_data_final="+aux_data_final+"&exceto_posto="+exceto_posto,"peca",'resizable=1,scrollbars=yes,width=750,height=550,top=0,left=0');
	janela.focus();
}

</script>

<style type="text/css">


/*****************************
ELEMENTOS DE POSICIONAMENTO
*****************************/

#container {
  border: 0px;
  padding:0px 0px 0px 0px;
  margin:0px 0px 0px 0px;
  background-color: white;
}
#logo{
	BORDER-RIGHT: 1px ;
	BORDER-TOP: 1px ;
	BORDER-LEFT: 1px ;
	BORDER-BOTTOM: 1px ;
	position: absolute;
	right: 10px;
	z-index: 5;
}

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

.formulario{
	background-color:#D9E2EF;
	font:11px Arial;
	text-align:left;
}

.subtitulo{

color: #7092BE
}

table.tabela tr td{
font-family: verdana;
font-size: 11px;
border-collapse: collapse;
border:1px solid #596d9b;
}

.espaco{
    padding-left: 120px;
}
</style>


<?
include "../javascript_pesquisas.php";
include "../javascript_calendario.php";
?>

<script type="text/javascript" charset="utf-8">
	$(function()
	{
		$('#data_inicial').datePicker({startDate:'01/01/2000'});
		$('#data_final').datePicker({startDate:'01/01/2000'});
		$("#data_inicial").maskedinput("99/99/9999");
		$("#data_final").maskedinput("99/99/9999");
	});
</script>

<link rel="stylesheet" href="../js/blue/style.css" type="text/css" id="" media="print, projection, screen" />
<!--
<script language="javascript" src="js/cal2.js"></script>
<script language="javascript" src="js/cal_conf2.js"></script>
-->

<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="js/chili-1.8b.js"></script>
<script type="text/javascript" src="js/docs.js"></script>
<script>
// add new widget called repeatHeaders
	$(function() {
		// add new widget called repeatHeaders
		$.tablesorter.addWidget({
			// give the widget a id
			id: "repeatHeaders",
			// format is called when the on init and when a sorting has finished
			format: function(table) {
				// cache and collect all TH headers
				if(!this.headers) {
					var h = this.headers = [];
					$("thead th",table).each(function() {
						h.push(
							"<th>" + $(this).text() + "</th>"
						);

					});
				}

				// remove appended headers by classname.
				$("tr.repated-header",table).remove();

				// loop all tr elements and insert a copy of the "headers"
				for(var i=0; i < table.tBodies[0].rows.length; i++) {
					// insert a copy of the table head every 10th row
					if((i%20) == 0) {
						if(i!=0){
						$("tbody tr:eq(" + i + ")",table).before(
							$("<tr></tr>").addClass("repated-header").html(this.headers.join(""))

						);
					}}
				}

			}
		});

		// call the tablesorter plugin and assign widgets with id "zebra" (Default widget in the core) and the newly created "repeatHeaders"
		$("table").tablesorter({
			widgets: ['zebra','repeatHeaders']
		});

	});

//#(document).ready(function(){
//	$.tablesorter.defaults.widgets = ['zebra'];
//	$("#relatorio").tablesorter();

//});


</script>

<script type="text/javascript" charset="utf-8">
	jQuery.fn.slideFadeToggle = function(speed, easing, callback) {
		return this.animate({opacity: 'toggle', height: 'toggle'}, speed, easing, callback);
	}

	function toogleProd(radio){
		var obj = document.getElementsByName('radio_qtde_produtos');
		if (obj[0].checked){
			$('#id_um').show("");
			$('#id_multi').hide("");
		}
		if (obj[1].checked){
			$('#id_um').hide("");
			$('#id_multi').show("");
		}
	}

	var singleSelect = true;  
	var sortSelect = true; 
	var sortPick = true; 


	function initIt() {
	  var pickList = document.getElementById("PickList");
	  var pickOptions = pickList.options;
	  pickOptions[0] = null; 
	}

	function addIt() {
		if ($('#produto_referencia_multi').val()=='')
			return false;
		if ($('#produto_descricao_multi').val()=='')
			return false;

		var pickList = document.getElementById("PickList");
		var pickOptions = pickList.options;
		var pickOLength = pickOptions.length;
		pickOptions[pickOLength] = new Option($('#produto_referencia_multi').val()+" - "+ $('#produto_descricao_multi').val());
		pickOptions[pickOLength].value = $('#produto_referencia_multi').val();

		$('#produto_referencia_multi').val("");
		$('#produto_descricao_multi').val("");

		if (sortPick) {
			var tempText;
			var tempValue;
			while (pickOLength > 0 && pickOptions[pickOLength].value < pickOptions[pickOLength-1].value) {
				tempText = pickOptions[pickOLength-1].text;
				tempValue = pickOptions[pickOLength-1].value;
				pickOptions[pickOLength-1].text = pickOptions[pickOLength].text;
				pickOptions[pickOLength-1].value = pickOptions[pickOLength].value;
				pickOptions[pickOLength].text = tempText;
				pickOptions[pickOLength].value = tempValue;
				pickOLength = pickOLength - 1;
			}
		}

		pickOLength = pickOptions.length;
		$('#produto_referencia_multi').focus();
	}
	function delIt() {
	  var pickList = document.getElementById("PickList");
	  var pickIndex = pickList.selectedIndex;
	  var pickOptions = pickList.options;
	  while (pickIndex > -1) {
		pickOptions[pickIndex] = null;
		pickIndex = pickList.selectedIndex;
	  }
	}

	function selIt(btn) {
		var pickList = document.getElementById("PickList");
		var pickOptions = pickList.options;
		var pickOLength = pickOptions.length;
		for (var i = 0; i < pickOLength; i++) {
			pickOptions[i].selected = true;
		}
		
	}
</script>
<DIV ID="container" style="width: 100%; ">

<!-- =========== PESQUISA POR INTERVALO ENTRE DATAS ============ -->
<FORM name="frm_pesquisa" METHOD="POST" ACTION="<? echo $PHP_SELF ?>">

<?
if (1==2){ /* Tulio solicito que fosse retirado a mensagem. Resolvi retirar tudo! - Fabio - 03/10/2008 */
	echo "<div style='background-color:#FCDB8F;width:600px;margin:0 auto;text-align:center;padding:2px 10px 2px 10px;font-size:12px'>";
	echo "<p style='text-align:left;padding:0px;'><b>ATEN��O: </b>Este relat�rio de BI considera toda  OS que est� finalizada, sendo poss�vel fazer a pesquisa com os dados abaixo. Caso queira utilizar o antigo relat�rio <a href='../relatorio_field_call_rate_pecas_defeitos.php'>clique aqui.</a> </p>";
	echo "<p style='text-align:left'>TELECONTROL</p>";
	echo "</div>";
}
?>

<TABLE width="700" align="center" border="0" cellspacing="0" cellpadding="0" class="formulario" id='Formulario'>
	<? if(strlen($msg_erro) > 0){ ?>
		<tr class="msg_erro">
			<td colspan="4">
					<? echo $msg_erro ?>

			</td>
		</tr>
	<? } ?>
	<tr class="titulo_tabela">
	    <td colspan="2">Par�metros de Pesquisa</td>
	</tr>
	    <tr>
	        <td width='50%'>&nbsp;</td>
	        <td width='*'>&nbsp;</td>
	    </tr>
	<tbody>
	<TR>
		<TD class='espaco'>
		    Data Inicial<br>
		    <INPUT size="15" maxlength="10" class="frm" TYPE="text" NAME="data_inicial" id="data_inicial" value="<? if (strlen($data_inicial) > 0) echo $data_inicial; ?>" >
		</TD>
		<TD>
		    Data Final<br>
		    <INPUT size="15" maxlength="10" class="frm" TYPE="text" NAME="data_final" id="data_final" value="<? if (strlen($data_final) > 0) echo $data_final; ?>" >
		</TD>
	</TR>
	<tr><td colspan="2">&nbsp;</td></tr>
	<TR> 
		<td colspan="2" class='espaco'>
		<fieldset style="width:420px;">
			<legend>Data de Refer�ncia</legend>
			<input type='radio' name='tipo_data' value='data_fechamento'<?if($tipo_data=="data_fechamento" or $tipo_data=="") echo "CHECKED";?>> Fechamento
			<input type='radio' name='tipo_data' value='data_finalizada'<?if($tipo_data=="data_finalizada") echo "CHECKED";?>> Finalizada
			<input type='radio' name='tipo_data' value='extrato_geracao'<?if($tipo_data=="extrato_geracao") echo "CHECKED";?>> Gera��o de Extrato
			<input type='radio' name='tipo_data' value='extrato_aprovacao'<?if($tipo_data=="extrato_aprovacao") echo "CHECKED";?>> Aprova��o do Extrato
			<?if($login_fabrica==20){?>
			<input type='radio' name='tipo_data' value='extrato_exportacao'<?if($tipo_data=="extrato_exportacao") echo "CHECKED";?>> Data pagamento
			<?}?>
		</fieldset>	
		</td>
	</TR>

	<tr><td colspan="2" class='espaco'>&nbsp;</td></tr>
		<?
		#123856
		if($login_fabrica==50){
			if (count($lista_produtos)>0){
				$display_um_produto    = "display:none";
				$display_multi_produto = "";
				$display_um            = "";
				$display_multi         = " CHECKED ";
			}else{
				$display_um_produto    = "";
				$display_multi_produto = "display:none";
				$display_um            = " CHECKED ";
				$display_multi         = "";
			}
		?>
		<TR>
			<td colspan="2" class='espaco'>SELECIONE &nbsp;&nbsp;&nbsp; Um produto
				<input type="radio" name="radio_qtde_produtos" value='um'  <?=$display_um?>  onClick='javascript:toogleProd(this)'>
				&nbsp;&nbsp;&nbsp;&nbsp;
				V�rios Produtos
				<input type="radio" name="radio_qtde_produtos" value='muitos' <?=$display_multi?> onClick='javascript:toogleProd(this)'>
			</td>

		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>
		<TR>
			<TH colspan='2' nowrap align="left" class='espaco'>
				<div id='id_um' style='<?echo $display_um_produto;?>'>
					<b>Ref. Produto</b><br><input class="frm" type="text" name="produto_referencia" id="produto_referencia" size="15" maxlength="20" value="<? echo $produto_referencia ?>" > &nbsp;<img src='imagens/lupa.png' border='0' align='absmiddle' style="cursor:pointer" onclick="javascript: fnc_pesquisa_produto (document.frm_pesquisa.produto_referencia, document.frm_pesquisa.produto_descricao,'referencia')">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<b>Descri��o Produto</b>
					<input class="frm" type="text" name="produto_descricao"  id="produto_descricao" size="15" value="<? echo $produto_descricao ?>" >&nbsp;<img src='imagens/lupa.png'  style="cursor:pointer" border='0' align='absmiddle' onclick="javascript: fnc_pesquisa_produto (document.frm_pesquisa.produto_referencia, document.frm_pesquisa.produto_descricao,'descricao')">
				</div>
				<div id='id_multi' style='<?echo $display_multi_produto;?>'>
					Ref. Produto&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class="frm" type="text" name="produto_referencia_multi" id="produto_referencia_multi" size="15" maxlength="20" value="" > &nbsp;<img src='imagens/lupa.png' border='0' align='absmiddle' style="cursor:pointer" onclick="javascript: fnc_pesquisa_produto (document.frm_pesquisa.produto_referencia_multi, document.frm_pesquisa.produto_descricao_multi,'referencia')">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<b>Descri��o Produto</b>
					<input class="frm" type="text" name="produto_descricao_multi"  id="produto_descricao_multi" size="15" value="" >&nbsp;<img src='imagens/lupa.png'  style="cursor:pointer" border='0' align='absmiddle' onclick="javascript: fnc_pesquisa_produto (document.frm_pesquisa.produto_referencia_multi, document.frm_pesquisa.produto_descricao_multi,'descricao')"><input type='button' name='adicionar_produto' id='adicionar_produto' value='Adicionar' class='frm' OnClick="addIt();" alt="Adicionar Produto" title="Adicionar Produto">
					<br>
						<font color='grey' size=1>(Selecione o produto e clique em Adicionar)</font><br>
					<center>
						<select multiple=true SIZE='4' style="width:80%" ID="PickList" NAME="PickList[]">
							<?
							if (count($produto_lista)>0){
								for ($i=0; $i<count($produto_lista); $i++){
									$linha_prod = $produto_lista[$i];
									echo "<option value='".$linha_prod[1]."'>".$linha_prod[1]." - ".$linha_prod[2]."</option>";
								}
							}
							?>
						</select><input type='button' name='remover_produto' id='remover_produto' value='Remover' class='frm'OnClick="delIt();" alt="Retirar Produto" title="Retirar Produto">
					</center>
				</div>
			</TH>
		</TR>
	<?
	}else{
	?>
		<TR>
			<td class='espaco'>
			    Ref. Produto<br>
			    <input class="frm" type="text" name="produto_referencia" id="produto_referencia" size="15" maxlength="20" value="<? echo $produto_referencia ?>" >
			    <img src='imagens/lupa.png' border='0' align='absmiddle' style="cursor:pointer" onclick="javascript: fnc_pesquisa_produto (document.frm_pesquisa.produto_referencia, document.frm_pesquisa.produto_descricao,'referencia')">
			</td>
			<td>
			    Descri��o Produto<br>
			    <input class="frm" type="text" name="produto_descricao"  id="produto_descricao" size="30" value="<? echo $produto_descricao ?>" >
			    <img src='imagens/lupa.png'  style="cursor:pointer" border='0' align='absmiddle' onclick="javascript: fnc_pesquisa_produto (document.frm_pesquisa.produto_referencia, document.frm_pesquisa.produto_descricao,'descricao')">
			</td>
		</TR>
	<?
	}
	?>

	<tr><td colspan="2" class='espaco'>&nbsp;</td></tr>

	<tr><td colspan="2">&nbsp;</td></tr>

	<TR>
		<TD class='espaco'>
		    C�d. Posto<br>
            <input type="text" name="codigo_posto" size="15" <? if ($login_fabrica == 5) { ?> onblur="javascript: fnc_pesquisa_posto (document.frm_consulta.codigo_posto, document.frm_pesquisa.posto_nome, 'codigo');" <? } ?> value="<? echo $codigo_posto ?>" class="frm">
			<img border="0" src="imagens/lupa.png" style="cursor: hand;" align="absmiddle" alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript: fnc_pesquisa_posto (document.frm_pesquisa.codigo_posto, document.frm_pesquisa.posto_nome, 'codigo')">
		</TD>
		<TD>
		    Nome Posto
		    <? if ($login_fabrica == 40) { ?>
		        (Exceto este Posto
		        <input type='checkbox' name='exceto_posto' value='exceto_posto' <?=$checked?>>)
		    <?}?><br>
            <input type="text" name="posto_nome" size="30" <? if ($login_fabrica == 5) { ?> onblur="javascript: fnc_pesquisa_posto (document.frm_pesquisa.codigo_posto, document.frm_pesquisa.posto_nome, 'nome');" <? } ?> value="<?echo $posto_nome?>" class="frm">
			<img border="0" src="imagens/lupa.png" style="cursor: hand;" align="absmiddle" alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript: fnc_pesquisa_posto (document.frm_pesquisa.codigo_posto, document.frm_pesquisa.posto_nome, 'nome')">
		</TD>
	</TR>

	<tr><td colspan="2">&nbsp;</td></tr>

	<TR>
		<TD colspan="0" class='espaco' style="float:left;">
		    Por Regi�o<br>
		    <select name="estado" class='frm'>
				<option value=""   <? if (strlen($estado) == 0)    echo " selected "; ?>>TODOS OS ESTADOS</option>
				<option value="AC" <? if ($estado == "AC") echo " selected "; ?>>AC - Acre</option>
				<option value="AL" <? if ($estado == "AL") echo " selected "; ?>>AL - Alagoas</option>
				<option value="AM" <? if ($estado == "AM") echo " selected "; ?>>AM - Amazonas</option>
				<option value="AP" <? if ($estado == "AP") echo " selected "; ?>>AP - Amap�</option>
				<option value="BA" <? if ($estado == "BA") echo " selected "; ?>>BA - Bahia</option>
				<option value="CE" <? if ($estado == "CE") echo " selected "; ?>>CE - Cear�</option>
				<option value="DF" <? if ($estado == "DF") echo " selected "; ?>>DF - Distrito Federal</option>
				<option value="ES" <? if ($estado == "ES") echo " selected "; ?>>ES - Esp�rito Santo</option>
				<option value="GO" <? if ($estado == "GO") echo " selected "; ?>>GO - Goi�s</option>
				<option value="MA" <? if ($estado == "MA") echo " selected "; ?>>MA - Maranh�o</option>
				<option value="MG" <? if ($estado == "MG") echo " selected "; ?>>MG - Minas Gerais</option>
				<option value="MS" <? if ($estado == "MS") echo " selected "; ?>>MS - Mato Grosso do Sul</option>
				<option value="MT" <? if ($estado == "MT") echo " selected "; ?>>MT - Mato Grosso</option>
				<option value="PA" <? if ($estado == "PA") echo " selected "; ?>>PA - Par�</option>
				<option value="PB" <? if ($estado == "PB") echo " selected "; ?>>PB - Para�ba</option>
				<option value="PE" <? if ($estado == "PE") echo " selected "; ?>>PE - Pernambuco</option>
				<option value="PI" <? if ($estado == "PI") echo " selected "; ?>>PI - Piau�</option>
				<option value="PR" <? if ($estado == "PR") echo " selected "; ?>>PR - Paran�</option>
				<option value="RJ" <? if ($estado == "RJ") echo " selected "; ?>>RJ - Rio de Janeiro</option>
				<option value="RN" <? if ($estado == "RN") echo " selected "; ?>>RN - Rio Grande do Norte</option>
				<option value="RO" <? if ($estado == "RO") echo " selected "; ?>>RO - Rond�nia</option>
				<option value="RR" <? if ($estado == "RR") echo " selected "; ?>>RR - Roraima</option>
				<option value="RS" <? if ($estado == "RS") echo " selected "; ?>>RS - Rio Grande do Sul</option>
				<option value="SC" <? if ($estado == "SC") echo " selected "; ?>>SC - Santa Catarina</option>
				<option value="SE" <? if ($estado == "SE") echo " selected "; ?>>SE - Sergipe</option>
				<option value="SP" <? if ($estado == "SP") echo " selected "; ?>>SP - S�o Paulo</option>
				<option value="TO" <? if ($estado == "TO") echo " selected "; ?>>TO - Tocantins</option>
			</select>
		</TD>


		<td colspan="0" class=''>
			Pa�s<br>
			<?
			$sql = "SELECT  *
					FROM    tbl_pais
					$w
					ORDER BY tbl_pais.nome;";
			$res = pg_exec ($con,$sql);

			if (pg_numrows($res) > 0) {
				echo "<select name='pais' class='frm' style='width: 200px'>\n";
				if(strlen($pais) == 0 ) $pais = 'BR';

				for ($x = 0 ; $x < pg_numrows($res) ; $x++){
					$aux_pais  = trim(pg_result($res,$x,pais));
					$aux_nome  = trim(pg_result($res,$x,nome));

					echo "<option value='$aux_pais'";
					if ($pais == $aux_pais){
						echo " SELECTED ";
						$mostraMsgPais = "<br> do PA�S $aux_nome";
					}
					echo ">$aux_nome</option>\n";
				}
				echo "</select>\n";
			}
			?>
		</td>

	</TR>

	<?if ($login_fabrica==14){?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<TR>
		<td colspan="2" class='espaco'>
		    Linha * &nbsp;&nbsp;
		<?
		##### IN�CIO LINHA #####
		$sql = "SELECT  *
				FROM    tbl_linha
				WHERE   tbl_linha.fabrica = $login_fabrica
				ORDER BY tbl_linha.nome;";
		$res = pg_exec ($con,$sql);

		if (pg_numrows($res) > 0) {
			echo "<select class='frm' style='width: 200px;' name='linha'>\n";
			echo "<option value=''>ESCOLHA</option>\n";

			for ($x = 0 ; $x < pg_numrows($res) ; $x++){
				$aux_linha = trim(pg_result($res,$x,linha));
				$aux_nome  = trim(pg_result($res,$x,nome));

				echo "<option value='$aux_linha'"; if ($linha == $aux_linha) echo " SELECTED "; echo ">$aux_nome</option>\n";
			}
			echo "</select>\n";
		}
		##### FIM LINHA #####
		?>
		</td>
	</TR>
	<?}?>
<? if ($login_fabrica == 7) { ?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<TR>
		<TD colspan="2" class='espaco'>Classifica��o de OS&nbsp;&nbsp;<br>
			<?
			$sql = "SELECT  *
					FROM    tbl_classificacao_os
					WHERE   fabrica = $login_fabrica
					AND ativo is true;";
			$res = pg_exec ($con,$sql);

			if (pg_numrows($res) > 0) {
				echo "<select name='classificacao' class='frm'>\n";
				echo "<option></option>";
				for ($x = 0 ; $x < pg_numrows($res) ; $x++){
					$aux_classificacao   = trim(pg_result($res,$x,classificacao_os));
					$aux_descricao = trim(pg_result($res,$x,descricao));

					echo "<option value='$aux_classificacao'";
					if ($classificacao == $aux_classificacao){
						echo " SELECTED ";
						$mostraMsgLinha .= "<br> da CLASSIFICA��O $aux_descricao";
					}
					echo ">$aux_descricao</option>\n";
				}
				echo "</select>\n&nbsp;";
			}
		?>
		</TD>
	</TR>
<? }?>
	
	<tr><td colspan="2">&nbsp;</td></tr>

	<tr>
		<TD colspan="2" align="left" class='espaco'>
			<fieldset style="width:420px;">
				<legend>Tipo Arquivo para Download</legend>				
				<input type='radio' name='formato_arquivo' value='XLS' <?if($formato_arquivo=='XLS')echo "checked";?>> XLS
				&nbsp;&nbsp;&nbsp;
				<input type='radio' name='formato_arquivo' value='CSV' <?if($formato_arquivo!='XLS')echo "checked";?>> CSV	
			</fieldset>
		</TD>
	</tr>
	
	<tr><td colspan="2">&nbsp;</td></tr>

	<tr>
		<td colspan='2' align='left' class='espaco'>
			<input type='checkbox' name='mostra_peca' value='mostra_peca' <? if($login_fabrica == 30){ echo "checked";}?>> Mostrar custo de pe�a mesmo se pedido estiver pendente!
		</td>
	</TR>

	</TBODY>
	<TFOOT>
	
	<tr><td colspan="2" >&nbsp;</td></tr>

	<TR>
		<input type='hidden' name='btn_finalizar' value='0'>
		<TD colspan="2" align="center"><input type="button" style="cursor:pointer;" value=" Pesquisar " onclick="javascript: if ( document.frm_pesquisa.btn_finalizar.value == '0' ) { document.frm_pesquisa.btn_finalizar.value='1'; document.frm_pesquisa.submit() ; } else { alert ('Aguarde submiss�o da OS...'); }" alt='Clique AQUI para pesquisar'>
		</TD>
	</TR>
	</TFOOT>
</TABLE>

</FORM>
</DIV>

<?

if ($listar == "ok") {
	if(strlen($codigo_posto)>0){
		$sql = "SELECT  posto
				FROM    tbl_posto_fabrica
				WHERE   fabrica      = $login_fabrica
				AND     codigo_posto = '$codigo_posto';";
		$res = pg_exec ($con,$sql);
		if (pg_numrows($res) > 0) $posto = trim(pg_result($res,0,posto));
	}

	if (strlen ($linha)    > 0) $cond_1 = " AND   BI.linha   = $linha ";
	if (strlen ($estado)   > 0) $cond_2 = " AND   BI.estado  = '$estado' ";
	if (strlen ($posto)    > 0) $cond_3 = " AND   BI.posto   = $posto ";
	if (strlen ($posto) > 0 AND !empty($exceto_posto)) {
		$cond_3 = " AND   NOT (BI.posto   = $posto) ";
	}
	if (strlen ($produto)  > 0) $cond_4 = " AND   BI.produto = $produto "; // HD 2003 TAKASHI
	if (strlen ($pais)     > 0) $cond_6 = " AND   BI.pais    = '$pais' ";
	if (strlen ($marca)    > 0) $cond_7 = " AND   BI.marca   = $marca ";
	if (strlen ($familia)  > 0) $cond_8 = " AND   BI.familia  = $familia ";
	if (strlen ($lista_produtos)  > 0) {
		$cond_10 = " AND   BI.produto in ( $lista_produtos) ";
		$cond_4 = "";
	}

	if (strlen($tipo_data) == 0 or $tipo_data=="data_fechamento") $tipo_data = 'data_fechamento';
	if (strlen($aux_data_inicial)>0 AND strlen($aux_data_final)>0){
		$cond_9 = "AND   BI.$tipo_data BETWEEN '$aux_data_inicial 00:00:00' AND '$aux_data_final 23:59:59'";
	}

	if($login_fabrica == 20 and $pais !='BR'){
		$produto_descricao   ="tbl_produto_idioma.descricao ";
		$join_produto_idioma =" LEFT JOIN tbl_produto_idioma ON tbl_produto.produto = tbl_produto_idioma.produto and tbl_produto_idioma.idioma = 'ES' ";
	}else{
		$produto_descricao   ="tbl_produto.descricao ";
		$join_produto_idioma =" ";
	}

	if ($login_fabrica == 7 and strlen($classificacao)>0) {

		$sql_tmp = "select count(*) as qtde, bi_os_item.peca
						INTO TEMP tmp_qtde_$login_admin
						from bi_os BI
						JOIN bi_os_item using(os)
						JOIN tbl_peca PE ON PE.peca = bi_os_item.peca AND PE.fabrica = $login_fabrica
						where BI.fabrica = $login_fabrica
						AND BI.excluida IS NOT TRUE
						$cond_1 $cond_2 $cond_3 $cond_4 $cond_5 $cond_6 $cond_7 $cond_8 $cond_9 $cond_10
						and classificacao_os = $classificacao
						GROUP BY bi_os_item.peca";

		$res_tmp = pg_exec($con,$sql_tmp);
		$join_classificacao = "JOIN      tmp_qtde_$login_admin ON tmp_qtde_$login_admin.peca = BI.peca";
		$campo_classificacao = "tmp_qtde_$login_admin.qtde as classificacao,";
		$group_classificacao = "tmp_qtde_$login_admin.qtde           ,";

	}


	$sql = "SELECT  PE.peca                                    ,
					PE.referencia                              ,
					PE.descricao                               ,
					$campo_classificacao
					SUM(BI.preco)                AS total_preco,
					SUM(BI.custo_peca * BI.qtde) AS total_cp   ,
					SUM(BI.qtde)                 AS qtde_pecas
		FROM      bi_os_item BI
		JOIN      tbl_peca    PE ON PE.peca    = BI.peca AND PE.fabrica = $login_fabrica
		$join_classificacao
		WHERE BI.fabrica = $login_fabrica
		 $cond_1 $cond_2 $cond_3 $cond_4 $cond_5 $cond_6 $cond_7 $cond_8 $cond_9 $cond_10
		GROUP BY    PE.peca                              ,
					PE.referencia                        ,
					$group_classificacao
					PE.descricao
		ORDER BY qtde_pecas DESC ";
	echo nl2br($sql); exit;
	$res = pg_exec ($con,$sql);

	if (pg_numrows($res) > 0) {
		$total = 0;
		echo "<br>";

		echo "<b>Resultado de pesquisa entre os dias $data_inicial e $data_final $mostraMsgLinha $mostraMsgEstado $mostraMsgPais </b>";

		echo "<br><br>";

		$data = date("Y-m-d").".".date("H-i-s");

		$arquivo_nome     = "bi-os-$login_fabrica.$login_admin.".$formato_arquivo;
		$path             = "/www/assist/www/admin/xls/";
		$path_tmp         = "/tmp/";

		$arquivo_completo     = $path.$arquivo_nome;
		$arquivo_completo_tmp = $path_tmp.$arquivo_nome;

		$fp = fopen ($arquivo_completo_tmp,"w");

		if ($formato_arquivo!='CSV'){
			fputs ($fp,"<html>");
			fputs ($fp,"<body>");
		}
		if ($login_fabrica==50) { // HD 41116
			echo "<span id='logo'><img src='../imagens_admin/colormaq_.gif' border='0' width='160' height='55'></span>";
		}
		/*echo "<p id='id_download' style='display:none'><a href='../xls/$arquivo_nome' target='_blank'><img src='/assist/imagens/excel.gif'><br><font color='#3300CC'><div style='background:white;width:130px;border:solid 1px #596d9b;cursor:pointer;'>Download em  ".strtoupper($formato_arquivo)."</div></font></a></p>";*/
		echo "<p id='id_download' style='display:none'><a href='../xls/$arquivo_nome' target='_blank'><img src='/assist/imagens/excel.gif'></a></p>";
		$caminho_download = "../xls/".$arquivo_nome;
		?>



		<input type='button' name='' value='Download em <?php echo strtoupper($formato_arquivo);?>' onclick="window.open('<?php echo $caminho_download;?>');"/><br><br>

		




		<?php
		$nome_post = "";
		$sql_busca = "SELECT   tbl_posto.*, tbl_posto_fabrica.codigo_posto
					FROM     tbl_posto
					JOIN     tbl_posto_fabrica USING (posto)
					WHERE    tbl_posto_fabrica.codigo_posto ilike '%$codigo_posto%'
					AND      tbl_posto_fabrica.fabrica = $login_fabrica
					ORDER BY tbl_posto.nome";
		$resultado = pg_exec($con,$sql_busca);
		for ($f=0; $f<pg_numrows($resultado); $f++){
			$nome_post  = trim(pg_result($resultado,$f,nome));
		}

		
		if($codigo_posto !='' || $posto_nome !=''){
		?>
		<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center' class='formulario'>
			<TR class='titulo_coluna'>
				<td class="titulo_tabela">Nome do Posto<br></td>
			</tr>
			<TR class='titulo_coluna'>
				<td  class="titulo_coluna"><?php echo $nome_post;?></td>
			</tr>
		</table>
		<?php
		}

		$conteudo .="<TABLE width='700' border='0' cellspacing='0' cellpadding='0' align='center'  name='relatorio' id='relatorio' class='tablesorter tabela' style='margin-top:0px;'>";
		$conteudo .="<thead>";
		$conteudo .="<TR class='titulo_coluna'>";
		$conteudo .="<th width='100' style='background-color:#596d9b;font: bold 11px 'Arial';color:#FFFFFF;text-align:center;padding-right: 15px'>Refer�ncia</th>";
		$conteudo .="<th style='background-color:#596d9b;text-align:center;padding-right: 15px'>Pe�a</th>";
		if ($login_fabrica == 7 and strlen($classificacao)>0) {
			$conteudo .="<td style='background-color:#596d9b;text-align:center;padding-right: 15px'>Classifica��o</th>";
		}
		$conteudo .="<th width='100' style='background-color:#596d9b;text-align:center;padding-right:35px' >Qtde. Pe�as</th>";
		$conteudo .="<th width='50' style='background-color:#596d9b;font: bold 11px 'Arial';color:#FFFFFF;text-align:center;padding-right: 15px'>%</th>";
		if ($mostra_peca=='mostra_peca'){
			$conteudo .="<th width='50' style='background-color:#596d9b;font: bold 11px 'Arial';color:#FFFFFF;text-align:center;padding-right: 15px'>Custo</th>";
		}

		$conteudo .="</TR>";
		$conteudo .="</thead>";
		$conteudo .="<tbody>";
		
		echo $cabecalho;
		echo $conteudo;
		if ($formato_arquivo=='CSV'){
			$conteudo = "";
			$conteudo .= "REFER�NCIA;PE�A;QTDE. PE�AS;%;CUSTO\n";
		}
		fputs ($fp,$conteudo);
		$total_ocorrencia == 0;
		for ($x = 0; $x < pg_numrows($res); $x++) {
			$total_ocorrencia = $total_ocorrencia + pg_result($res,$x,qtde_pecas);
		}
		for ($i=0; $i<pg_numrows($res); $i++){
			$conteudo = "";
			$referencia   = trim(pg_result($res,$i,referencia));
			$descricao    = trim(pg_result($res,$i,descricao));
			if($login_fabrica == 20 and $pais !='BR' and strlen($descricao)==0){
				$descricao    = "<font color = 'red'>Tradu��o n�o cadastrada.</font>";
			}
			$peca         = trim(pg_result($res,$i,peca));
			//$valor_peca   = trim(pg_result($res,$i,total_preco));
			/* O valor da pe�a nao est� setado, entao pegar no CUSTO_PECA - HD 43710 42363 */
			$valor_peca   = trim(pg_result($res,$i,total_cp));

			$qtde_pecas   = trim(pg_result($res,$i,qtde_pecas));

			if ($total_ocorrencia > 0) $porcentagem = (($qtde_pecas * 100) / $total_ocorrencia);

			if($ativo == 'f'){$ativo = "<B>*</B>"; }else{$ativo= '';}

			$total_peca  += $valor_peca;

			if ($login_fabrica == 7 and strlen($classificacao)>0) {
				$classificacao = pg_result($res,$i,classificacao);
			}

			$porcentagem = number_format($porcentagem,2,",",".");
			$valor_peca  = number_format($valor_peca,2,",",".");

			$conteudo .="<TR>";
			$conteudo .="<TD align='left' nowrap>";

			$conteudo .="<a href='javascript:AbrePeca(\"$peca\",\"$data_inicial\",\"$data_final\",\"$linha\",\"$estado\",\"$posto\",\"$produto\",\"$pais\",\"$marca\",\"$tipo_data\",\"$aux_data_inicial\",\"$aux_data_final\",\"$exceto_posto\");'>";
			$conteudo .="$referencia</TD>";
			$conteudo .="<TD align='left' nowrap>$descricao</TD>";
			
			if ($login_fabrica == 7 and strlen($classificacao)>0) {
				$conteudo .="<TD align='center' nowrap>$classificacao</TD>";
			}

			$conteudo .="<TD align='center' nowrap>$qtde_pecas</TD>";
			$conteudo .="<TD align='right' nowrap title=''>$porcentagem</TD>";
			
			if ($mostra_peca=='mostra_peca'){
				
				$conteudo .="<TD align='center' nowrap>$valor_peca</TD>";
			}
			
			$conteudo .="</TR>";
			echo $conteudo;
			
			if ($formato_arquivo=='CSV'){
				$conteudo = "";
				$conteudo .= $referencia.";".$descricao.";".$qtde_pecas.";".$porcentagem.";".$valor_peca.";\n";
			}
			fputs ($fp,$conteudo);
		}
		$conteudo = "";
		$total_ocorrencia  = number_format($total_ocorrencia,0,",",".");
		$total_peca        = number_format($total_peca,2,",",".");
		$conteudo .="</tbody>";

		$conteudo .= "<tfoot>";
		$conteudo .= "<tr class='titulo_coluna'><td colspan='2'>TOTAL</td>";
		$conteudo .="<td colspan='2' align='center'>$total_ocorrencia</td>";
		if ($mostra_peca=='mostra_peca'){
			$conteudo .="<td align='right'>$total_peca</td>";
		}
		$conteudo .="</tr>";
		$conteudo .= "</tfoot>";
		$conteudo .=" </TABLE>";
		
		
		echo $conteudo;
		if ($formato_arquivo == 'CSV'){
			$conteudo = "";
			$conteudo .= "total: ;".$total.";".$total_peca.";\n";
		}
		fputs ($fp,$conteudo);

		if ($formato_arquivo!='CSV'){
			fputs ($fp,"</body>");
			fputs ($fp,"</html>");
		}
		fclose ($fp);
		flush();
		echo ` cp $arquivo_completo_tmp $path `;
		echo "<script language='javascript'>";
		echo "document.getElementById('id_download').style.display='block';";
		echo "</script>";
		echo "<br>";

	}else{
		echo "<br>";
		echo "<b>Nenhum resultado encontrado entre $data_inicial e $data_final $mostraMsgLinha $mostraMsgEstado $mostraMsgPais</b>";
	}
}

flush();

?>

<p>

<? include "../rodape.php" ?>
