<?php
$areaAdminCliente = preg_match('/\/admin_cliente\//',$_SERVER['PHP_SELF']) > 0 ? true : false;
define('ADMCLI_BACK', ($areaAdminCliente == true)?'../admin/':'');
define('ASSCLI_BACK', '../');

if ($areaAdminCliente == true) {
    include_once "../dbconfig.php";
    include_once "../includes/dbconnect-inc.php";
    include 'autentica_admin.php';
    include_once '../funcoes.php';
} else {
    include 'dbconfig.php';
    include 'includes/dbconnect-inc.php';
    $admin_privilegios="gerencia,call_center";
    include 'autentica_admin.php';
}

$aExibirFiltroAtendente = array ( 1, 5, 11, 24, 30, 50, 52, 59, 172); // fabricas que podem ver o filtro de atendente

$bypass = md5(time());
$q = strtolower($_GET["term"]);
if (isset($_GET["term"])){
	$tipo_busca = $_GET["tipo_busca"];
		if ($tipo_busca=="geral"){
				$y = trim (strtoupper ($q));
				$palavras = explode(' ',$y);
				$count = count($palavras);
				$sql_and = "";
				for($i=0 ; $i < $count ; $i++){
					if(strlen(trim($palavras[$i]))>3){
						$cnpj_pesquisa = trim($palavras[$i]);
						$cnpj_pesquisa = str_replace (' ','',$cnpj_pesquisa);
						$cnpj_pesquisa = str_replace ('-','',$cnpj_pesquisa);
						$cnpj_pesquisa = str_replace ('\'','',$cnpj_pesquisa);
						$cnpj_pesquisa = str_replace ('.','',$cnpj_pesquisa);
						$cnpj_pesquisa = str_replace ('/','',$cnpj_pesquisa);
						$cnpj_pesquisa = str_replace ('\\','',$cnpj_pesquisa);
						if(preg_match("/\d/",$palavras[$i])) {
							$sql_and .= " AND ( tbl_hd_chamado_extra.cpf ILIKE '%".trim($palavras[$i])."%' OR tbl_hd_chamado_extra.fone ILIKE '%".trim($palavras[$i])."%' OR tbl_hd_chamado_extra.nota_fiscal ILIKE '%".trim($palavras[$i])."%' OR tbl_hd_chamado_extra.serie ILIKE '%".trim($palavras[$i])."%' OR tbl_os.sua_os ILIKE'%".trim($palavras[$i])."%' OR tbl_hd_chamado_extra.cep ILIKE '%".$cnpj_pesquisa."%')";
						}else{
							$sql_and .= " AND (tbl_hd_chamado_extra.nome ILIKE '%".trim($palavras[$i])."%')";
						}
					}
				}

				$sql = "SELECT      tbl_hd_chamado.hd_chamado,
									tbl_hd_chamado_extra.serie,
									tbl_hd_chamado_extra.nota_fiscal,
									tbl_hd_chamado_extra.nome,
									tbl_hd_chamado_extra.cpf,
									tbl_os.sua_os,
									tbl_hd_chamado_extra.cep,
									tbl_hd_chamado_extra.fone
						FROM        tbl_hd_chamado JOIN tbl_hd_chamado_extra using(hd_chamado)
						LEFT JOIN tbl_os USING(os,fabrica)
						WHERE       tbl_hd_chamado.fabrica = $login_fabrica

						$sql_and limit 10";

				$res = pg_exec($con,$sql);
				//echo nl2br($sql);
				if (pg_numrows ($res) > 0) {
					for ($i=0; $i<pg_numrows ($res); $i++ ){
						$hd_chamado        = trim(pg_result($res,$i,hd_chamado));
						$nome              = trim(pg_result($res,$i,nome));
						$serie             = trim(pg_result($res,$i,serie));
						$cpf               = trim(pg_result($res,$i,cpf));
						$nota_fiscal       = trim(pg_result($res,$i,nota_fiscal));
						$fone              = trim(pg_result($res,$i,fone));
						$cep               = trim(pg_result($res,$i,cep));
						$sua_os            = trim(pg_result($res,$i,sua_os));

						$array = array("chamado" => $hd_chamado, "nome" => $nome, "serie" => $serie, "cpf" => $cpf, "nf" => $nota_fiscal, "fone" => $fone, "cep" => $cep, "os" => $sua_os);
						$array_json[$i] = json_encode($array);
					}

					echo json_encode($array_json);
				}
		}
		exit;
}

$btn_acao = trim (strtolower ($_POST['btn_acao']));

$msg_erro = "";

if ($btn_acao == "gravar") {
}

$layout_menu = "callcenter";
$title = "RELA��O DE CALL-CENTER";

include "cabecalho.php";
$meses = array(1 => "Janeiro", "Fevereiro", "Mar�o", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");

if (isset($_GET["q"])){
	$busca      = $_GET["busca"];
	$tipo_busca = $_GET["tipo_busca"];

	if (strlen($q)>2){
		if ($tipo_busca=="cliente_admin"){
			$y = trim (strtoupper ($q));
			$condicao = explode(';',$y);
			$palavras = explode(' ',$condicao[0]);
			$cidade = $condicao[1];
			$count = count($palavras);
			$sql_and = "";
			for($i=0 ; $i < $count ; $i++){
				if(strlen(trim($palavras[$i]))>0){
					$cnpj_pesquisa = trim($palavras[$i]);
					$cnpj_pesquisa = str_replace (' ','',$cnpj_pesquisa);
					$cnpj_pesquisa = str_replace ('-','',$cnpj_pesquisa);
					$cnpj_pesquisa = str_replace ('\'','',$cnpj_pesquisa);
					$cnpj_pesquisa = str_replace ('.','',$cnpj_pesquisa);
					$cnpj_pesquisa = str_replace ('/','',$cnpj_pesquisa);
					$cnpj_pesquisa = str_replace ('\\','',$cnpj_pesquisa);
					$sql_and .= " AND (tbl_cliente_admin.nome ILIKE '%".trim($palavras[$i])."%'
								 	  OR  tbl_cliente_admin.cnpj ILIKE '%$cnpj_pesquisa%' OR tbl_cliente_admin.cidade ILIKE '%".trim($palavras[$i])."%')";
					if (strlen($cidade)>0) {
						$sql_and .= " AND tbl_cliente_admin.cidade ILIKE '%".trim($cidade)."%'";
					}
				}
			}

			$sql = "SELECT      tbl_cliente_admin.cliente_admin,
								tbl_cliente_admin.nome,
								tbl_cliente_admin.codigo,
								tbl_cliente_admin.cnpj,
								tbl_cliente_admin.cidade
					FROM        tbl_cliente_admin
					WHERE       tbl_cliente_admin.fabrica = $login_fabrica
					AND   (tbl_hd_chamado.titulo isnull or tbl_hd_chamado.titulo !~* 'help-desk')
					$sql_and limit 30";

			$res = pg_exec($con,$sql);
			if (pg_numrows ($res) > 0) {
				for ($i=0; $i<pg_numrows ($res); $i++ ){
					$cliente_admin      = trim(pg_result($res,$i,cliente_admin));
					$nome               = trim(pg_result($res,$i,nome));
					$codigo             = trim(pg_result($res,$i,codigo));
					$cnpj               = trim(pg_result($res,$i,cnpj));
					$cidade             = trim(pg_result($res,$i,cidade));

					echo "$cliente_admin|$cnpj|$codigo|$nome|$cidade ";
					echo "\n";
				}
			}
		}
	}
exit;
}

?>

<link rel="stylesheet" type="text/css" href="js/jquery-ui-1.8rc3.custom.css">
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

.table_line {
	text-align: left;
	font-family: Arial, Verdana, Geneva, Helvetica, sans-serif;
	font-size: 11px;
	font-weight: normal;
	border: 0px solid;
	background-color: #D9E2EF
}

.table_line2 {
	text-align: left;
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: normal;
}

.titulo_tabela{
	background-color:#596d9b;
	font: bold 14px "Arial";
	color:#FFFFFF;
	text-align:center;
}

.msg_erro{
	background-color:#FF0000;
	font: bold 16px "Arial";
	color:#FFFFFF;
	text-align:center;
}

.titulo_coluna{
	background-color:#596d9b;
	font: bold 11px "Arial";
	color:#FFFFFF;
	text-align:center;
}

</style>

<? include ADMCLI_BACK."javascript_pesquisas.php" ?>

<script type="text/javascript">
	
	// ========= Fun��o PESQUISA DE REVENDA POR NOME OU CNPJ ========= //

function fnc_pesquisa_revenda(campo, tipo) {
    var campo = campo.value;

    if (jQuery.trim(campo).length > 2){
        Shadowbox.open({
            content:"pesquisa_revenda_nv.php?"+tipo+"="+campo+"&tipo="+tipo,
            player: "iframe",
            title:  ('<?=traduz("Pesquisa Revenda")?>'),
            width:  800,
            height: 500
        });
    }else
    alert('<?=traduz("Informar toda ou parte da informa��o para realizar a pesquisa!")?>');
}

function retorna_revenda(nome, cnpj){
	console.log(nome);
	console.log(cnpj);
	$("#revenda_cnpj").val(cnpj);
	$("#revenda_nome").val(nome);
}

</script>


<link rel="stylesheet" type="text/css" href="<?=ADMCLI_BACK?>plugins/jquery/datepick/telecontrol.datepick.css" media="all">
<link rel="stylesheet" type="text/css" href="<?=ADMCLI_BACK?>../plugins/shadowbox/shadowbox.css" media="all">
<link  rel="stylesheet"  type="text/css"  href="<?=ADMCLI_BACK?>js/jquery.tabs.css" media="print,  projection,  screen">
<link rel="stylesheet" type="text/css" href="<?=ADMCLI_BACK?>js/jquery-ui-1.8.23.custom/development-bundle/themes/base/jquery.ui.all.css" media="all">



<script src="<?=ADMCLI_BACK?>js/jquery-ui-1.8.23.custom/js/jquery-1.8.0.min.js"></script>
<script src="<?=ADMCLI_BACK?>js/jquery-ui-1.8.23.custom/js/jquery-ui-1.8.23.custom.min.js"></script>
<script src="<?=ADMCLI_BACK?>js/jquery-ui-1.8.23.custom/development-bundle/ui/minified/jquery.ui.core.min.js"></script>
<script src="<?=ADMCLI_BACK?>js/jquery-ui-1.8.23.custom/development-bundle/ui/minified/jquery.ui.widget.min.js"></script>
<script src="<?=ADMCLI_BACK?>js/jquery-ui-1.8.23.custom/development-bundle/ui/minified/jquery.ui.position.min.js"></script>
<script src="<?=ADMCLI_BACK?>js/jquery-ui-1.8.23.custom/development-bundle/ui/minified/jquery.ui.autocomplete.min.js"></script>
<script src="<?=ADMCLI_BACK?>js/jquery-ui-1.8.23.custom/development-bundle/external/jquery.bgiframe-2.1.2.js"></script>
<script src='<?=ADMCLI_BACK?>ajax.js'></script>
<script src='<?=ADMCLI_BACK?>ajax_cep.js'></script>
<script src="<?=ASSCLI_BACK?>plugins/shadowbox/shadowbox.js"></script>
<script src="<?=ASSCLI_BACK?>plugins/jquery/datepick/jquery.datepick.js"></script>
<script src="<?=ASSCLI_BACK?>plugins/jquery/datepick/jquery.datepick-pt-BR.js"></script>
<script src="<?=ADMCLI_BACK?>js/jquery.tabs.pack.js"></script>
<script type="text/javascript" src="<?=ADMCLI_BACK?>js/jquery.mask.js"></script>
<script src="<?=ADMCLI_BACK?>js/ui.dropdownchecklist-1.4-min.js"></script>

<?php if ($login_fabrica == 50) { ?>
	<link rel="stylesheet" href="css/multiple-select.css" />
	<script src="js/jquery.multiple.select.js"></script>
<? } ?>

<script>

function fnc_pesquisa_cliente_admin(campo, campo2, tipo) {
	if (tipo == "codigo" ) {
		var xcampo = campo;
	}

	if (tipo == "nome" ) {
		var xcampo = campo2;
	}

	if (xcampo.value != "") {
		var url = "";
		url = "cliente_admin_pesquisa.php?campo=" + xcampo.value + "&tipo=" + tipo ;
		janela = window.open(url, "janela", "toolbar=no, location=no, status=yes, scrollbars=yes, directories=no, width=600, height=400, top=18, left=0");
		janela.codigo_cliente_admin  = campo;
		janela.nome    = campo2;
		janela.focus();
	}
}

function SomenteNumero(e){
	var tecla=(window.event)?event.keyCode:e.which;
	if((tecla > 47 && tecla < 58)) return true;
	else{
		if (tecla != 8) return false;
		else return true;
	}
}


function formatCliente(row) {
	return "Chamado: "+row[0] + " Cliente: " + row[1] + "-" + row[2]+ " Fone: "+row[5]+" Os: "+row[6]+" Nota Fiscal: "+row[4]+" S�rie: "+row[3]+" Cep: "+row[7];
}


$(document).ready(function() {

	Shadowbox.init();

	$('#data_inicial').datepick({startDate:'01/01/2000'});
	$('#data_final').datepick({startDate:'01/01/2000'});
	$("#data_inicial").mask("99/99/9999");
	$("#data_final").mask("99/99/9999");
	$("#cep").mask("99.999-999");

	<?php if ($login_fabrica == 50) { ?>
		$("#hd_motivo_ligacao").multipleSelect();
	<? } ?>

	$('#fone').each(function()
    {
        /* Carrega a m�scara default do post/get conforme o valor que j� vier no value */
        /* Para adicionar mais DDD's  =>  $(this).val().match(/^\(11|21\) 9/i) */
        if( $(this).val().match(/^\(1\d\) 9/i) )
        {
            $(this).mask('(00) 00000-0000', $(this).val()); /* 9� D�gito */
        }
        else
        {
            $(this).mask('(00) 0000-0000',  $(this).val()); /* M�scara default */
        }
    });

    $('#fone').keypress(function()
    {
        if( $(this).val().match(/^\(1\d\) 9/i) )
        {
            $(this).mask('(00) 00000-0000'); /* 9� D�gito */
        }
        else
        {
            $(this).mask('(00) 0000-0000');  /* M�scara default */
        }
    });

	$("#geral").autocomplete({
		// URL
		source: "callcenter_parametros_interativo.php?tipo_busca=geral&busca=geral",
		minLength: 3,
		delay: 300,
		// Posi��o que vai aparecer a div com os resultados
		position: { my : "center top", at: "center bottom" },
		// Fun��o de quando seleciona o Item do Resultado
		select: function (event, ui) {
			// Passa o Resultado para JSON
			var result = toJSON(ui.item.value);
			// Grava o Resultado no Campo
			$(this).val(result.chamado);

			VerifChecks(result);
			// Precisa do Return false para matar a fun��o select, se n�o matar ele vai jogar no campo todo o value do Objeto JSON
			return false;
		}
	}).data("autocomplete")._renderItem = function (ul, item) {
		var result = toJSON(item.label);
		// A variavel text voc� define o que vai aparecer no resultado
		var text = "<b>Chamado:</b> "+result.chamado+", <b>Cliente:</b> "+result.nome+" - "+result.cpf+", <b>Fone:</b> "+result.fone+", <b>OS:</b> "+result.os+", <b>NF:</b> "+result.nf+", <b>S�rie:</b> "+result.serie+", <b>CEP:</b> "+result.cep;

		return $("<li></li>").data("item.autocomplete", item).append("<a>"+text+"</a>").appendTo(ul);
	};

    $("#familia").dropdownchecklist();
    $("#posto_estado").dropdownchecklist();

});

function VerifChecks (result) {
	$("#callcenter").val('') ;
	$("#chk_opt15").attr('checked',false);

	$("#nome_consumidor").val('');
	$("#chk_opt9").attr('checked',false);

	$("#numero_os").val('') ;
	$("#chk_opt13").attr('checked',false);

	$("#cpf_consumidor").val('') ;
	$("#chk_opt10").attr('checked',false);

	$("#numero_serie").val('') ;
	$("#chk_opt8").attr('checked',false);

	$("#fone").val('') ;
	$("#chk_opt16").attr('checked',false);

	$("#cep").val('') ;
	$("#chk_opt17").attr('checked',false);

	$("#nota_fiscal").val('') ;
	$("#chk_opt14").attr('checked',false);

	$("#marca").val('') ;
	$("#chk_marca").attr('checked',false);

	if (result.chamado.length>0){
		$("#callcenter").val(result.chamado) ;
		$("#chk_opt15").attr('checked',true);
	}

	if (result.cpf.length>0){
		$("#cpf_consumidor").val(result.cpf);
		$("#chk_opt10").attr('checked',true);
	}

	if (result.nome.length>0){
		$("#nome_consumidor").val(result.nome);
		$("#chk_opt9").attr('checked',true);
	}

	if (result.nf.length>0){
		$("#nota_fiscal").val(result.nf);
		$("#chk_opt14").attr('checked',true);
	}

	if (result.serie.length>0){
		$("#numero_serie").val(result.serie) ;
		$("#chk_opt8").attr('checked',true);
	}

	if (result.fone.length>0){
		$("#fone").val(result.fone) ;
		$("#chk_opt16").attr('checked',true);
	}

	if (result.os.length>0){
		$("#numero_os").val(result.os) ;
		$("#chk_opt13").attr('checked',true);
	}

	if (result.cep.length>0){
		$("#cep").val(result.cep) ;
		$("#chk_opt17").attr('checked',true);
	}

	if (result.chamado.length>0) {
		$("#marca").val(result.marca) ;
		$("#chk_marca").attr('checked',true);
	}
}


</script>

<br>

<FORM name="frm_pesquisa" METHOD="post" ACTION="callcenter_consulta_lite_interativo.php?bypass=<?=$bypass?>">
<TABLE width="700" align="center" border="0" cellspacing="0" cellpadding="2">
<TR bgcolor="#596d9b" style="font:bold 14px Arial; color:#FFFFFF;">
	<TD colspan="5"><?=traduz('Pesquisa por Intervalo entre Datas')?></TD>
</TR>
<tr><td colspan="5" class="table_line">&nbsp;</td></tr>
<TR>
	<TD class="table_line" style="width: 10px">&nbsp;</TD>
	<TD class="table_line" width="300"><INPUT TYPE="checkbox" NAME="chk_opt1" value="1">&nbsp; <?=traduz('Atendimentos lan�ados hoje')?></TD>
	<TD class="table_line" colspan=2><INPUT TYPE="checkbox" NAME="chk_opt2" value="1">&nbsp; <?=traduz('Atendimentos lan�ados ontem')?></TD>
	<TD class="table_line" style="width: 10px">&nbsp;</TD>
</TR>
<TR>
	<TD class="table_line" style="width: 10px">&nbsp;</TD>
	<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt3" value="1">&nbsp; <?=traduz('Atendimentos lan�ados nesta semana')?></TD>
	<TD class="table_line" colspan=2 ><INPUT TYPE="checkbox" NAME="chk_opt4" value="1">&nbsp; <?=traduz('Atendimentos lan�ados neste m�s')?></TD>
	<TD class="table_line" style="width: 10px">&nbsp;</TD>
</TR>
<? if ($login_fabrica == 52) {?>
<TR>
	<TD class="table_line" style="width: 10px">&nbsp;</TD>
	<TD class="table_line" colspan=4><INPUT TYPE="checkbox" NAME="chk_opt18" value="1">&nbsp; <?=traduz('Pr�-OSs')?></TD>
</TR>
<?}?>
<tr><td colspan="5" class="table_line">&nbsp;</td></tr>
<TR>
	<TD colspan="5" class="table_line"><center><input type="button" style="background:url(imagens_admin/btn_pesquisar_400.gif); width:400px;cursor:pointer;" value="&nbsp;" onClick="document.frm_pesquisa.submit();" alt="Preencha as op��es e clique aqui para pesquisar"></center></TD>
</TR>
<TR>
	<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
</TR>
<TR>
	<td width="19" class="table_line" style="text-align: left;">&nbsp;</td>
	<TD rowspan="2" width="180" class="table_line">
		<INPUT TYPE="radio" NAME="data_abertura_fechamento" value="abertura"> <?=traduz('Data abertura')?><br/>
		<INPUT TYPE="radio" NAME="data_abertura_fechamento" value="fechamento"> <?=traduz('Data fechamento')?>
	</TD>

	<TD class="table_line"><?=traduz('Data Inicial')?></TD>
	<TD class="table_line" align='left'><?=traduz('Data Final')?></TD>
	<TD class="table_line" align='left' >&nbsp;</TD>
</TR>
<TR>
	<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
		<td class="table_line">
			<input type="text" name="data_inicial" id="data_inicial" size="12" maxlength="10" class='frm' value="<? if (strlen($data_inicial) > 0) echo $data_inicial;  ?>" >

			<!--
			<img border="0" src="imagens/btn_lupa.gif" align="absmiddle" onclick="javascript:showCal('DataInicial')" style="cursor: hand;" alt="Clique aqui para abrir o calend�rio">
			-->
		</td>
		<td class="table_line">
			<input type="text" name="data_final" id="data_final" size="12" maxlength="10" class='frm' value="<? if (strlen($data_final) > 0) echo $data_final;?>" >

			<!-- <img border="0" src="imagens/btn_lupa.gif" align="absmiddle" onclick="javascript:showCal('DataFinal')" style="cursor: hand;" alt="Clique aqui para abrir o calend�rio"> -->
		</td>
		<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
</TR>
<TR>
	<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
</TR>
<? if (in_array($login_fabrica, array(52, 85, 156)) && $areaAdminCliente != true ) { ?>
<TR>
	<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD rowspan="2" width="180" class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt19" value="1" class='frm'> <?=traduz('Cliente Admin')?></TD>
	<TD width="180" class="table_line"><?=traduz('C�digo do Cliente Admin')?></TD>
	<TD width="180" class="table_line"><?=traduz('Nome do Cliente Admin')?></TD>
	<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
</TR>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD class="table_line" align="left" nowrap><INPUT TYPE="text" NAME="codigo_cliente_admin" SIZE="8" class='frm'> <IMG src="imagens/lupa.png" style="cursor:pointer " align='absmiddle' alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript:
	fnc_pesquisa_cliente_admin (document.frm_pesquisa.codigo_cliente_admin,document.frm_pesquisa.cliente_nome_admin,'codigo')"></TD>

	<TD width="151" class="table_line" style="text-align: left;" nowrap><INPUT TYPE="text" NAME="cliente_nome_admin" size="15" class='frm'> <IMG src="imagens/lupa.png" style="cursor:pointer" align='absmiddle' alt="Clique aqui para pesquisas postos pelo nome" onclick="javascript: fnc_pesquisa_cliente_admin (document.frm_pesquisa.codigo_cliente_admin,document.frm_pesquisa.cliente_nome_admin,'nome')"></TD>
	<TD width="19" class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<TR>
	<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
</TR>

<? } ?>

<? if($areaAdminCliente != true){?>
<TR>
	<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD rowspan="2" width="180" class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt6" value="1"> <?php echo ($login_fabrica == 189) ? traduz("Revenda/Representante") : traduz("Posto");?></TD>
	<TD width="180" class="table_line">C�digo do <?php echo ($login_fabrica == 189) ? traduz("Revenda/Representante") : traduz("Posto");?></TD>
	<TD width="180" class="table_line">Nome do <?php echo ($login_fabrica == 189) ? traduz("Revenda/Representante") : traduz("Posto");?></TD>
	<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
</TR>

<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD class="table_line" align="left" nowrap><INPUT TYPE="text" NAME="codigo_posto" SIZE="8" <? if ($login_fabrica == 5) { ?> onblur="javascript: fnc_pesquisa_posto (document.frm_pesquisa.codigo_posto,document.frm_pesquisa.nome_posto,'codigo')" <? } ?> class='frm'><IMG src="imagens/lupa.png" style="cursor:pointer " align='absmiddle' alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript: fnc_pesquisa_posto (document.frm_pesquisa.codigo_posto,document.frm_pesquisa.nome_posto,'codigo')"></TD>
	<TD width="151" class="table_line" style="text-align: left;" nowrap><INPUT TYPE="text" NAME="nome_posto" size="15" <? if ($login_fabrica == 5) { ?> onblur="javascript: fnc_pesquisa_posto (document.frm_pesquisa.codigo_posto,document.frm_pesquisa.nome_posto,'nome')" <? } ?> class='frm'> <IMG src="imagens/lupa.png" style="cursor:pointer" align='absmiddle' alt="Clique aqui para pesquisas postos pelo nome" onclick="javascript: fnc_pesquisa_posto (document.frm_pesquisa.codigo_posto,document.frm_pesquisa.nome_posto,'nome')"></TD>
	<TD width="19" class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>


<?php if($login_fabrica == 30){ ?>
<TR>
	<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
</TR>
<tr valign='top'>
	<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD rowspan="2" width="180" class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt32" value="1"> <?=traduz('Revenda')?></TD>
	<td class="table_line">
        <font size="1" face="Geneva, Arial, Helvetica, san-serif"><?=traduz('CNPJ Revenda')?></font>
        <br>
        <input class="frm" type="<?=($login_fabrica == 15 ? 'hidden' : 'text') ?>" name="revenda_cnpj" size="20" maxlength="18" id="revenda_cnpj" value="<? echo $revenda_cnpj ?>" onblur="this.className='frm'; displayText('&nbsp;');" onfocus="this.className='frm-on'; displayText('&nbsp;Insira o n�mero no Cadastro Nacional de Pessoa Jur�dica.'); ">&nbsp;<? if($login_fabrica != 15) { ?><img src='imagens/lupa.png' border='0' align='absmiddle' onclick='javascript: fnc_pesquisa_revenda (document.frm_pesquisa.revenda_cnpj, "cnpj")' style='cursor: pointer' /> <? } ?>
    </td>
    <td class="table_line">
        <font size="1" face="Geneva, Arial, Helvetica, san-serif"><?=traduz('Nome Revenda')?></font>
        <br>
        <input class="frm" type="text" name="revenda_nome" id="revenda_nome" size="20" maxlength="50" value="<? echo $revenda_nome ?>" onkeyup="somenteMaiusculaSemAcento(this)" <? if($login_fabrica==50){?>onChange="javascript: this.value=this.value.toUpperCase();"<?}?> onblur="this.className='frm'; displayText('&nbsp;');" onfocus="this.className='frm-on'; displayText('&nbsp;Digite o nome da REVENDA onde foi adquirido o produto.');">&nbsp;<img src='imagens/lupa.png' border='0' align='absmiddle' onclick='javascript: <? echo ($login_fabrica == 15) ? 'pesquisaRevendaLatina':'fnc_pesquisa_revenda';?> (document.frm_pesquisa.revenda_nome, "nome")' style='cursor: pointer' >
    </td>
    
    <td class="table_line"></td>
</tr>
<?php } ?>

<? } ?>
<TR>
	<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
</TR>
<TR>
	<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD rowspan="2" width="180" class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt7" value="1"><?php echo ($login_fabrica) ? traduz("Produto") : traduz("Aparelho")?></TD>
	<TD width="100" class="table_line"><?=traduz('Refer�ncia')?></TD>
	<TD width="180" class="table_line"><?=traduz('Descri��o')?></TD>
	<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
</TR>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD class="table_line" align="left"><INPUT TYPE="text" NAME="produto_referencia" SIZE="8" <? if ($login_fabrica == 5) { ?> onblur="javascript: fnc_pesquisa_produto (document.frm_pesquisa.produto_referencia,document.frm_pesquisa.produto_nome,'referencia')" <? } ?> class='frm'><IMG src="imagens/lupa.png" style="cursor:pointer " align='absmiddle' alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript: fnc_pesquisa_produto (document.frm_pesquisa.produto_referencia,document.frm_pesquisa.produto_nome,'referencia')"></TD>
	<TD class="table_line" style="text-align: left;"><INPUT TYPE="text" NAME="produto_nome" size="15" <? if ($login_fabrica == 5) { ?> onblur="javascript: fnc_pesquisa_produto (document.frm_pesquisa.produto_referencia,document.frm_pesquisa.produto_nome,'descricao')" <? } ?> class='frm'><IMG src="imagens/lupa.png" style="cursor:pointer " align='absmiddle' alt="Clique aqui para pesquisas pela refer�ncia do aparelho." onclick="javascript: fnc_pesquisa_produto (document.frm_pesquisa.produto_referencia,document.frm_pesquisa.produto_nome,'descricao')"></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<TR>
	<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
</TR>
<?php if (in_array($login_fabrica, array(85))) { ?>
	<script>
		function fnc_pesquisa_tecnico_esporadico (tipo, valor) {
            var url = "pesquisa_tecnico_esporadico.php?tipo=" + tipo + "&valor=" + valor + "&fabrica=" + <?=$login_fabrica;?>;

            Shadowbox.open({
                content :   url,
                player  :   "iframe",
                title   :   "Pesquisa",
                width   :   800,
                height  :   500
            });
        }

        function retorna_tecnico_esporadico (tecnico_id, codigo, nome) {
            $("#tecnico_esporadico_id").val(tecnico_id);
            $("#codigo_tecnico_esporadico").val(codigo);
            $("#tecnico_esporadico").val(nome);
        }
	</script>
	<TR>
		<TD style="display: none;"?><input type="hidden" name="tecnico_esporadico_id" id="tecnico_esporadico_id" value="<?=$tecnico_esporadico_id;?>"></TD>
		<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
		<TD rowspan="2" width="180" class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt852" value="1"><?=traduz('T�cnico Espor�dico')?></TD>
		<TD width="100" class="table_line"><?=traduz('C�digo')?></TD>
		<TD width="180" class="table_line"><?=traduz('Nome')?></TD>
		<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
	</TR>
	<TR>
		<TD class="table_line" style="text-align: center;">&nbsp;</TD>
		<TD class="table_line" align="left"><INPUT TYPE="text" NAME="codigo_tecnico_esporadico" ID="codigo_tecnico_esporadico" SIZE="8" class='frm'><IMG src="imagens/lupa.png" style="cursor:pointer " align='absmiddle' alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript: fnc_pesquisa_tecnico_esporadico('codigo',document.getElementById('codigo_tecnico_esporadico').value);"></TD>
		<TD class="table_line" style="text-align: left;"><INPUT TYPE="text" NAME="tecnico_esporadico" ID="tecnico_esporadico" size="15" class='frm'><IMG src="imagens/lupa.png" style="cursor:pointer " align='absmiddle' alt="Clique aqui para pesquisas pela refer�ncia do aparelho." onclick="javascript: fnc_pesquisa_tecnico_esporadico('nome',document.getElementById('tecnico_esporadico').value);"></TD>
		<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	</TR>
	<TR>
		<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
	</TR>
<?php }

//altera��o chamado
if ($login_fabrica == 137 or $login_fabrica == 35) {
?>
<TR>
	<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD rowspan="2" width="180" class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt66" value="1"> <?=traduz('Linha')?></TD>
	<TD width="180" class="table_line"><?=traduz('Linha')?></TD>
	<TD colspan="2" width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
</TR>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD class="table_line" align="left" nowrap>
		<SELECT TYPE="text" name='linha_prod' size='1' class='frm'>
		<option value=''></option>
		<?
		$sql = "SELECT linha, nome from tbl_linha where fabrica = $login_fabrica and ativo = true order by nome";
                $res = pg_query($con,$sql);

                if(pg_num_rows($res)>0){
                    for($i=0;pg_num_rows($res)>$i;$i++){
                        $xlinha = pg_fetch_result($res,$i,linha);
                        $xnome = pg_fetch_result($res,$i,nome);
?>
                    <option value="<?echo $xlinha;?>" <? //HD 73808 if ($xmarca == $marca) echo " SELECTED "; ?>><?echo $xnome;?></option>
<?
                    }
                }
                echo "</SELECT>";
        ?>
	</TD>
	<TD colspan="2" width="19" class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<TR>
	<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
</TR>

<TR>
	<TD width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD rowspan="2" width="180" class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt67" value="1"> <?=traduz('Fam�lia')?></TD>
	<TD width="180" class="table_line"><?=traduz('Fam�lia')?></TD>
	<TD colspan="2" width="19" class="table_line" style="text-align: left;">&nbsp;</TD>
</TR>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD class="table_line" align="left" nowrap>
		<SELECT TYPE="text" name='familia_prod' size='1' class='frm'>
		<option value=''></option>
		<?
		$sql = "SELECT familia, descricao from tbl_familia where fabrica = $login_fabrica and ativo = true order by descricao";
                $res = pg_query($con,$sql);
                if(pg_num_rows($res)>0){
                    for($i=0;pg_num_rows($res)>$i;$i++){
                        $xfamilia = pg_fetch_result($res,$i,familia);
                        $xdescricao = pg_fetch_result($res,$i,descricao);
                        ?>
                        <option value="<?echo $xfamilia;?>" <? //HD 73808 if ($xmarca == $marca) echo " SELECTED "; ?>><?echo $xdescricao;?></option>
                        <?
                    }
                }
                echo "</SELECT>";
		?>

	</TD>
	<TD colspan="2" width="19" class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<TR>
	<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
</TR>
<?
}
//fim chamado
?>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD  class="table_line"><label for="geral"><?=traduz('Busca Geral')?></label></TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<INPUT TYPE="text" NAME="geral" ID="geral" size="30" class='frm' />
	</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<TR>
	<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
</TR>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD  class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt15" ID="chk_opt15" value="1"> <?=traduz('N�mero do Atendimento')?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="callcenter" ID="callcenter" size="17" class='frm'></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php
	
	if (in_array($login_fabrica, [184,200])) { ?>

		<TR>
			<TD class="table_line" style="text-align: center;">&nbsp;</TD>
			<TD  class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt91" ID="chk_opt91" value="1">Pedido de Venda</TD>
			<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="pedido_venda" ID="callcenter" size="17" class='frm'></TD>
			<TD class="table_line" style="text-align: center;">&nbsp;</TD>
		</TR>
		<TR>
			<TD class="table_line" style="text-align: center;">&nbsp;</TD>
			<TD  class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt92" ID="chk_opt92" value="1">NF de Venda</TD>
			<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="nf_venda" ID="callcenter" size="17" class='frm'></TD>
			<TD class="table_line" style="text-align: center;">&nbsp;</TD>
		</TR>

	<?php
	}

	if( $login_fabrica == 90 ):
?>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD  class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt90" ID="chk_opt90" value="1"> <?=traduz('N�mero IBBL')?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="numero_ibbl" ID="numero_ibbl" size="17" class='frm'></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php
	endif;
?>
<!-- < hd-6010107 -->
<TR>
       <TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
</TR>
<?php if ($login_fabrica == 177) { ?>
       <TR>
               <TD class="table_line" style="text-align: left;">&nbsp;</TD>
               <TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt29" ID="chk_opt29" value="1">Origem</TD>
               <TD class="table_line" style="text-align: left;" colspan="2">
                       <select name="origem" id='origem' style='width:196px; font-size:11px' class='frm'>
                               <option value=""></option>
                               <?php
                                       $sql = "SELECT hd_chamado_origem, descricao     
                                               FROM tbl_hd_chamado_origem WHERE fabrica = {$login_fabrica}
                                               ORDER BY descricao";
                                       $resOrigem = pg_query($con,$sql);

                                       if(pg_num_rows($resOrigem) > 0){
                                               while ($objeto_origem_callcenter = pg_fetch_object($resOrigem)) {

                                                       if ($objeto_origem_callcenter->descricao == $origem_callcenter) {
                                                               $selected = "selected='selected'";
                                                       } else {
                                                               $selected = "";
                                                       }  ?>
                                                       <option value="<?=$objeto_origem_callcenter->descricao?>" <?=$selected?>><?=$objeto_origem_callcenter->descricao?></option>   
                                        <?php  }
                                       } ?>
                       </select>
               </TD>
               <TD class="table_line" style="text-align: center;">&nbsp;</TD>
       </TR>
<?php } ?> 
<!-- hd-6010107 > -->
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD  class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt8" ID="chk_opt8" value="1"><?php echo ($login_fabrica == 160 or $replica_einhell)? traduz(" N� Lote"): traduz(" N�mero de s�rie")?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="numero_serie" ID="numero_serie" size="17" class='frm'></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php if($login_fabrica == 160 or $replica_einhell){?>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD  class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt22" ID="chk_opt22" value="1"> <?=traduz('N�mero do Processo')?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="numero_processo" ID="numero_processo" size="17" class='frm'></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD  class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt40" ID="chk_opt40" value="1"> Vers�o do Produto</TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="versao" ID="versao" size="17" class='frm' maxlength="10"></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php
}
if($login_fabrica != 85){
?>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD  class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt14" ID="chk_opt14" value="1"> N�mero da nota fiscal</TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="nota_fiscal" ID="nota_fiscal" size="17" maxlength='10' onkeypress='return SomenteNumero(event)' class='frm'></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php
}
if($login_fabrica == 161){ ?>
	<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt31" ID="chk_opt31" value="1"> Lote</TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<input type="text" class='frm' name="lote" size="17" id="lote" value="<?=$lote?>" >
	</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php }
?>
<?php if (in_array($login_fabrica, array(169,170))){ ?>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD  class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt30" ID="chk_opt30" value="true">Jornada</TD>
	<TD class="table_line" style="text-align: left;" colspan="2"></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php } ?>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<!-- HD 216395: Mudar todas as buscas de nome para LIKE com % apenas no final. A funcao function mostrarMensagemBuscaNomes() est� definida no js/assist.js -->
	<TD  class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt9" ID="chk_opt9" value="1">  <?php echo ($login_fabrica == 189) ? " Nome do Cliente": "Nome do Consumidor";?>
		<?=$login_fabrica == 85 ? " / Raz�o Social": "";?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="nome_consumidor" ID="nome_consumidor" size="17" class='frm'> <img src='imagens/help.png' title='Clique aqui para ajuda na busca deste campo' onclick='mostrarMensagemBuscaNomes()'><!-- IMG src="imagens/lupa.png" style="cursor:pointer " align='absmiddle' alt="Clique aqui para pesquisas pelo nome do consumidor." onclick="javascript: fnc_pesquisa_consumidor (document.frm_pesquisa.nome_consumidor,'nome')"--></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?
if($login_fabrica == 85 && $areaAdminCliente != true){
?>
<TR>
    <TD class="table_line" style="text-align: center;">&nbsp;</TD>
    <TD  class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt85" ID="chk_opt85" value="1"> Nome Fantasia</TD>
    <TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="nome_fantasia" ID="nome_fantasia" size="17" class='frm'> </TD>
    <TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?
}

if($login_fabrica == 162 || $login_fabrica == 164){
?>
<TR>
    <TD class="table_line" style="text-align: center;">&nbsp;</TD>
    <TD  class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt86" ID="chk_opt86" value="1"> N�mero de Postagem</TD>
    <TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="numero_postagem" ID="numero_postagem" size="17" class='frm'> </TD>
    <TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?
}
if($login_fabrica != 85){
?>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt10" ID="chk_opt10" value="1"> CPF/CNPJ do <?php echo ($login_fabrica == 189) ? "Cliente" : "Consumidor";?></TD>
	<TD class="table_line" align="left" colspan="2"><INPUT TYPE="text" NAME="cpf_consumidor" ID="cpf_consumidor" size="17" onkeypress='return SomenteNumero(event)' class='frm'><!-- IMG src="imagens/lupa.png" style="cursor:pointer " align='absmiddle' alt="Clique aqui para pesquisar um consumidor pelo seu CPF" onclick="javascript: fnc_tamanho_minimo(document.frm_pesquisa.codigo_posto,3); fnc_pesquisa_posto (document.frm_pesquisa.codigo_posto,document.frm_pesquisa.nome_posto,'codigo')" --></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?
}
if($login_fabrica != 86 &&  $areaAdminCliente != true){ $onkeypress = ""?>
<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt13" ID="chk_opt13" value="1" > N�mero da OS</TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="numero_os" ID="numero_os" size="17" class='frm'></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<? } ?>

<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt16" ID="chk_opt16" value="1"> Telefone do <?php echo ($login_fabrica == 189) ? "Cliente" : "Consumidor";?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="fone" ID="fone" size="17" class='frm'></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php if (!in_array($login_fabrica, [85,180,181,182])){ ?>
<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt17" ID="chk_opt17" value="1"> CEP do <?php echo ($login_fabrica == 189) ? "Cliente" : "Consumidor";?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="cep" ID="cep" size="17" class='frm'></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php
}

if($login_fabrica == 125){
?>
<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt25" ID="chk_opt25" value="1"> N�mero do pedido</TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="pedido" ID="pedido" size="17" class='frm'></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php
}

//J� existe um filtro por regi�o para a f�brica 5
if (!in_array($login_fabrica, [5,80,180,181,182])) {
?>
<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt24" ID="chk_opt24" value="1"> Estado do <?php echo ($login_fabrica == 189) ? "Cliente" : "Consumidor";?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<select name="consumidor_estado" id='consumidor_estado' style='width:131px; font-size:11px' class='frm'>
			<? $ArrayEstados = array('','AC','AL','AM','AP',
										'BA','CE','DF','ES',
										'GO','MA','MG','MS',
										'MT','PA','PB','PE',
										'PI','PR','RJ','RN',
										'RO','RR','RS','SC',
										'SE','SP','TO'
									);
			for ($i=0; $i<=27; $i++){
				echo"<option value='".$ArrayEstados[$i]."'";
				if ($consumidor_estado == $ArrayEstados[$i]) echo " selected";
				echo ">".$ArrayEstados[$i]."</option>\n";
			}?>
		</select>
	</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?
}
if($login_fabrica == 80): ?>
<tr>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt24" ID="chk_opt24" value="1"> Estado do <?php echo ($login_fabrica == 189) ? "Cliente" : "Consumidor";?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<select name="consumidor_estado" id='consumidor_estado' style='width:131px; font-size:11px' class='frm'>
			<option value=""></option>
		</select>
	</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</tr>
<tr>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt93" ID="chk_opt93" value="1"> Cidade do <?php echo ($login_fabrica == 189) ? "Cliente" : "Consumidor";?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<select name="consumidor_cidade" id='consumidor_cidade' style='width:131px; font-size:11px' class='frm'>
		<option value=""></option>
		</select>
		</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</tr>
<tr>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt94" ID="chk_opt94" value="1"> Status da OS</TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<select name="status_os" id='status_os' style='width:131px; font-size:11px' class='frm'>
		<option value=""></option>
		<option value="todos">Todos</option>
		<?php 
			$sql_status = "SELECT status_checkpoint, descricao from tbl_status_checkpoint 
							WHERE status_checkpoint =0 OR status_checkpoint = 1 OR status_checkpoint = 2 OR status_checkpoint = 3 OR status_checkpoint = 4 OR status_checkpoint = 9";
			$res_status = pg_query($con,$sql_status);
			if(pg_num_rows($res_status) > 0){
				for($i=0; $i < pg_num_rows($res_status); $i++){
					$status = pg_fetch_result($res_status, $i, 'status_checkpoint');
					$descricao = pg_fetch_result($res_status, $i, 'descricao');
					echo "<option value='$status'>$descricao</option>";
				}
			}
		?>
		</select>
		</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</tr>
<script type="text/javascript">	
	$(document).ready(function () {
	
		$.getJSON('cidadeporestado_80.json', function (data) {
			data = 
			[
				{"sigla": "AC","nome": "Acre","cidades": ["Acrel�ndia","Assis Brasil","Brasil�ia","Bujari","Capixaba","Cruzeiro do Sul","Epitaciol�ndia","Feij�",
				"Jord�o","M�ncio Lima","Manoel Urbano","Marechal Thaumaturgo","Pl�cido de Castro","Porto Acre","Porto Walter","Rio Branco",
				"Rodrigues Alves","Santa Rosa do Purus","Sena Madureira","Senador Guiomard","Tarauac�","Xapuri"]},
				{"sigla": "AL","nome": "Alagoas","cidades": ["�gua Branca","Anadia","Arapiraca","Atalaia","Barra de Santo Ant�nio","Barra de S�o Miguel","Batalha","Bel�m","Belo Monte",
				"Boca da Mata","Branquinha","Cacimbinhas","Cajueiro","Campestre","Campo Alegre","Campo Grande","Canapi","Capela","Carneiros",
				"Ch� Preta","Coit� do N�ia","Col�nia Leopoldina","Coqueiro Seco","Coruripe","Cra�bas","Delmiro Gouveia","Dois Riachos",
				"Estrela de Alagoas","Feira Grande","Feliz Deserto","Flexeiras","Girau do Ponciano","Ibateguara","Igaci","Igreja Nova","Inhapi",
				"Jacar� dos Homens","Jacu�pe","Japaratinga","Jaramataia","Jequi� da Praia","Joaquim Gomes","Jundi�","Junqueiro","Lagoa da Canoa",
				"Limoeiro de Anadia","Macei�","Major Isidoro","Mar Vermelho","Maragogi","Maravilha","Marechal Deodoro","Maribondo","Mata Grande",
				"Matriz de Camaragibe","Messias","Minador do Negr�o","Monteir�polis","Murici","Novo Lino","Olho d'�gua das Flores","Olho d'�gua do Casado",
				"Olho d'�gua Grande","Oliven�a","Ouro Branco","Palestina","Palmeira dos �ndios","P�o de A��car","Pariconha","Paripueira",
				"Passo de Camaragibe","Paulo Jacinto","Penedo","Pia�abu�u","Pilar","Pindoba","Piranhas","Po�o das Trincheiras","Porto Calvo","Porto de Pedras",
				"Porto Real do Col�gio","Quebrangulo","Rio Largo","Roteiro","Santa Luzia do Norte","Santana do Ipanema","Santana do Munda�",
				"S�o Br�s","S�o Jos� da Laje","S�o Jos� da Tapera","S�o Lu�s do Quitunde","S�o Miguel dos Campos","S�o Miguel dos Milagres","S�o Sebasti�o",
				"Satuba","Senador Rui Palmeira","Tanque d'Arca","Taquarana","Teot�nio Vilela","Traipu","Uni�o dos Palmares","Vi�osa"]},
				{"sigla": "AM","nome": "Amazonas","cidades": ["Alvar�es","Amatur�","Anam�","Anori","Apu�","Atalaia do Norte","Autazes","Barcelos","Barreirinha",
				"Benjamin Constant","Beruri","Boa Vista do Ramos","Boca do Acre","Borba","Caapiranga","Canutama","Carauari","Careiro","Careiro da V�rzea","Coari",
				"Codaj�s","Eirunep�","Envira","Fonte Boa","Guajar�","Humait�","Ipixuna","Iranduba","Itacoatiara","Itamarati","Itapiranga","Japur�","Juru�",
				"Juta�","L�brea","Manacapuru","Manaquiri","Manaus","Manicor�","Mara�","Mau�s","Nhamund�","Nova Olinda do Norte","Novo Air�o","Novo Aripuan�","Parintins","Pauini","Presidente Figueiredo",
				"Rio Preto da Eva","Santa Isabel do Rio Negro","Santo Ant�nio do I��","S�o Gabriel da Cachoeira","S�o Paulo de Oliven�a","S�o Sebasti�o do Uatum�","Silves","Tabatinga","Tapau�",
				"Tef�","Tonantins","Uarini","Urucar�","Urucurituba"]},
				{"sigla": "AP","nome": "Amap�","cidades": ["Amap�","Cal�oene","Cutias","Ferreira Gomes","Itaubal","Laranjal do Jari","Macap�","Mazag�o",
				"Oiapoque","Pedra Branca do Amapari","Porto Grande","Pracu�ba","Santana","Serra do Navio","Tartarugalzinho","Vit�ria do Jari"]},
				{"sigla": "BA","nome": "Bahia","cidades": ["Aba�ra","Abar�","Acajutiba","Adustina","�gua Fria","Aiquara","Alagoinhas","Alcoba�a","Almadina","Amargosa",
				"Am�lia Rodrigues","Am�rica Dourada","Anag�","Andara�","Andorinha","Angical","Anguera","Antas","Ant�nio Cardoso","Ant�nio Gon�alves","Apor�",
				"Apuarema","Ara�as","Aracatu","Araci","Aramari","Arataca","Aratu�pe","Aurelino Leal","Baian�polis","Baixa Grande","Banza�","Barra","Barra da Estiva",
				"Barra do Cho�a","Barra do Mendes","Barra do Rocha","Barreiras","Barro Alto","Barrocas","Barro Preto","Belmonte","Belo Campo","Biritinga","Boa Nova","Boa Vista do Tupim","Bom Jesus da Lapa","Bom Jesus da Serra","Boninal","Bonito",
				"Boquira","Botupor�","Brej�es","Brejol�ndia","Brotas de Maca�bas","Brumado","Buerarema","Buritirama","Caatiba","Cabaceiras do Paragua�u","Cachoeira","Cacul�","Ca�m","Caetanos","Caetit�",
				"Cafarnaum","Cairu","Caldeir�o Grande","Camacan","Cama�ari","Camamu","Campo Alegre de Lourdes","Campo Formoso","Can�polis","Canarana","Canavieiras",
				"Candeal","Candeias","Candiba","C�ndido Sales","Cansan��o","Canudos","Capela do Alto Alegre","Capim Grosso","Cara�bas","Caravelas","Cardeal da Silva","Carinhanha","Casa Nova","Castro Alves","Catol�ndia","Catu","Caturama",
				"Central","Chorroch�","C�cero Dantas","Cip�","Coaraci","Cocos","Concei��o da Feira","Concei��o do Almeida","Concei��o do Coit�","Concei��o do Jacu�pe","Conde","Conde�ba","Contendas do Sincor�",
				"Cora��o de Maria","Cordeiros","Coribe","Coronel Jo�o S�","Correntina","Cotegipe","Cravol�ndia","Cris�polis","Crist�polis","Cruz das Almas","Cura��","D�rio Meira","Dias d'�vila","Dom Bas�lio","Dom Macedo Costa","El�sio Medrado",
				"Encruzilhada","Entre Rios","�rico Cardoso","Esplanada","Euclides da Cunha","Eun�polis","F�tima","Feira da Mata","Feira de Santana","Filad�lfia","Firmino Alves","Floresta Azul","Formosa do Rio Preto","Gandu","Gavi�o",
				"Gentio do Ouro","Gl�ria","Gongogi","Governador Mangabeira","Guajeru","Guanambi","Guaratinga","Heli�polis","Ia�u","Ibiassuc�","Ibicara�","Ibicoara","Ibicu�","Ibipeba","Ibipitanga","Ibiquera",
				"Ibirapitanga","Ibirapu�","Ibirataia","Ibitiara","Ibitit�","Ibotirama","Ichu","Igapor�","Igrapi�na","Igua�","Ilh�us","Inhambupe","Ipecaet�","Ipia�","Ipir�","Ipupiara",
				"Irajuba","Iramaia","Iraquara","Irar�","Irec�","Itabela","Itaberaba","Itabuna","Itacar�","Itaet�","Itagi","Itagib�","Itagimirim","Itagua�u da Bahia","Itaju do Col�nia","Itaju�pe","Itamaraju","Itamari","Itamb�","Itanagra",
				"Itanh�m","Itaparica","Itap�","Itapebi","Itapetinga","Itapicuru","Itapitanga","Itaquara","Itarantim","Itatim","Itiru�u","Iti�ba","Itoror�","Itua�u","Ituber�","Iui�","Jaborandi","Jacaraci","Jacobina",
				"Jaguaquara","Jaguarari","Jaguaripe","Janda�ra","Jequi�","Jeremoabo","Jiquiri��","Jita�na","Jo�o Dourado","Juazeiro","Jucuru�u","Jussara","Jussari","Jussiape","Lafaiete Coutinho","Lagoa Real","Laje","Lajed�o","Lajedinho","Lajedo do Tabocal",
				"Lamar�o","Lap�o","Lauro de Freitas","Len��is","Lic�nio de Almeida","Livramento de Nossa Senhora","Lu�s Eduardo Magalh�es","Macajuba","Macarani","Maca�bas","Macurur�","Madre de Deus","Maetinga","Maiquinique","Mairi","Malhada","Malhada de Pedras","Manoel Vitorino","Mansid�o","Marac�s","Maragogipe","Mara�",
				"Marcion�lio Souza","Mascote","Mata de S�o Jo�o","Matina","Medeiros Neto","Miguel Calmon","Milagres","Mirangaba","Mirante","Monte Santo","Morpar�","Morro do Chap�u","Mortugaba","Mucug�","Mucuri","Mulungu do Morro","Mundo Novo","Muniz Ferreira","Muqu�m de S�o Francisco","Muritiba","Mutu�pe",
				"Nazar�","Nilo Pe�anha","Nordestina","Nova Cana�","Nova F�tima","Nova Ibi�","Nova Itarana","Nova Reden��o","Nova Soure","Nova Vi�osa","Novo Horizonte","Novo Triunfo","Olindina","Oliveira dos Brejinhos","Ouri�angas","Ourol�ndia","Palmas de Monte Alto","Palmeiras","Paramirim","Paratinga",
				"Paripiranga","Pau Brasil","Paulo Afonso","P� de Serra","Pedr�o","Pedro Alexandre","Piat�","Pil�o Arcado","Pinda�","Pindoba�u","Pintadas","Pira� do Norte","Pirip�","Piritiba","Planaltino","Planalto","Po��es","Pojuca","Ponto Novo","Porto Seguro","Potiragu�",
				"Prado","Presidente Dutra","Presidente J�nio Quadros","Presidente Tancredo Neves","Queimadas","Quijingue","Quixabeira","Rafael Jambeiro","Remanso","Retirol�ndia","Riach�o das Neves","Riach�o do Jacu�pe","Riacho de Santana","Ribeira do Amparo","Ribeira do Pombal","Ribeir�o do Largo","Rio de Contas","Rio do Ant�nio","Rio do Pires","Rio Real","Rodelas",
				"Ruy Barbosa","Salinas da Margarida","Salvador","Santa B�rbara","Santa Br�gida","Santa Cruz Cabr�lia","Santa Cruz da Vit�ria","Santa In�s","Santa Luzia","Santa Maria da Vit�ria","Santa Rita de C�ssia","Santa Teresinha","Santaluz","Santana","Santan�polis","Santo Amaro","Santo Ant�nio de Jesus","Santo Est�v�o","S�o Desid�rio","S�o Domingos",
				"S�o Felipe","S�o F�lix","S�o F�lix do Coribe","S�o Francisco do Conde","S�o Gabriel","S�o Gon�alo dos Campos","S�o Jos� da Vit�ria","S�o Jos� do Jacu�pe","S�o Miguel das Matas","S�o Sebasti�o do Pass�","Sapea�u","S�tiro Dias","Saubara","Sa�de","Seabra","Sebasti�o Laranjeiras","Senhor do Bonfim","Sento S�","Serra do Ramalho",
				"Serra Dourada","Serra Preta","Serrinha","Serrol�ndia","Sim�es Filho","S�tio do Mato","S�tio do Quinto","Sobradinho","Souto Soares","Tabocas do Brejo Velho","Tanha�u","Tanque Novo","Tanquinho","Tapero�","Tapiramut�","Teixeira de Freitas","Teodoro Sampaio","Teofil�ndia",
				"Teol�ndia","Terra Nova","Tremedal","Tucano","Uau�","Uba�ra","Ubaitaba","Ubat�","Uiba�","Umburanas","Una","Urandi","Uru�uca","Utinga","Valen�a","Valente","V�rzea da Ro�a","V�rzea do Po�o","V�rzea Nova","Varzedo","Vera Cruz","Vereda","Vit�ria da Conquista","Wagner","Wanderley","Wenceslau Guimar�es","Xique-Xique"]},
				{"sigla": "CE","nome": "Cear�","cidades": ["Abaiara","Acarap�","Acara�","Acopiara","Aiuaba","Alc�ntaras","Altaneira","Alto Santo","Amontada","Antonina do Norte","Apuiar�s","Aquiraz","Aracati","Aracoiaba","Ararend�","Araripe","Aratuba","Arneiroz","Assar�",
				"Aurora","Baixio","Banabui�","Barbalha","Barreira","Barro","Barroquinha","Baturit�","Beberibe","Bela Cruz","Boa Viagem","Brejo Santo","Camocim","Campos Sales","Canind�","Capistrano","Caridade","Carir�","Cariria�u","Cari�s","Carnaubal","Cascavel",
				"Catarina","Catunda","Caucaia","Cedro","Chaval","Chor�","Chorozinho","Corea�","Crate�s","Crato","Croat�","Cruz","Deputado Irapuan Pinheiro","Erer�","Eus�bio","Farias Brito","Forquilha","Fortaleza","Fortim","Frecheirinha","General Sampaio","Gra�a","Granja","Granjeiro","Groa�ras","Guai�ba","Guaraciaba do Norte","Guaramiranga",
				"Hidrol�ndia","Horizonte","Ibaretama","Ibiapina","Ibicuitinga","Icapu�","Ic�","Iguatu","Independ�ncia","Ipaporanga","Ipaumirim","Ipu","Ipueiras","Iracema","Irau�uba","Itai�aba","Itaitinga","Itapag�","Itapipoca","Itapi�na","Itarema","Itatira","Jaguaretama","Jaguaribara","Jaguaribe","Jaguaruana","Jardim","Jati","Jijoca de Jericoaroara",
				"Juazeiro do Norte","Juc�s","Lavras da Mangabeira","Limoeiro do Norte","Madalena","Maracana�","Maranguape","Marco","Martin�pole","Massap�","Mauriti","Meruoca","Milagres","Milh�","Mira�ma","Miss�o Velha","Momba�a","Monsenhor Tabosa","Morada Nova","Mora�jo","Morrinhos","Mucambo","Mulungu","Nova Olinda","Nova Russas","Novo Oriente","Ocara","Or�s","Pacajus","Pacatuba",
				"Pacoti","Pacuj�","Palhano","Palm�cia","Paracuru","Paraipaba","Parambu","Paramoti","Pedra Branca","Penaforte","Pentecoste","Pereiro","Pindoretama","Piquet Carneiro","Pires Ferreira","Poranga","Porteiras","Potengi","Potiretama","Quiterian�polis","Quixad�","Quixel�","Quixeramobim","Quixer�","Reden��o","Reriutaba",
				"Russas","Saboeiro","Salitre","Santa Quit�ria","Santana do Acara�","Santana do Cariri","S�o Benedito","S�o Gon�alo do Amarante","S�o Jo�o do Jaguaribe","S�o Lu�s do Curu","Senador Pompeu","Senador S�","Sobral","Solon�pole","Tabuleiro do Norte","Tamboril","Tarrafas","Tau�","Teju�uoca","Tiangu�","Trairi","Tururu","Ubajara","Umari","Umirim","Uruburetama","Uruoca","Varjota",
				"V�rzea Alegre","Vi�osa do Cear�"]},
				{"sigla": "DF","nome": "Distrito Federal","cidades": ["Bras�lia"]},
				{"sigla": "ES","nome": "Esp�rito Santo","cidades": ["Afonso Cl�udio","�gua Doce do Norte","�guia Branca","Alegre","Alfredo Chaves","Alto Rio Novo","Anchieta","Apiac�","Aracruz","Atilio Vivacqua","Baixo Guandu","Barra de S�o Francisco","Boa Esperan�a","Bom Jesus do Norte","Brejetuba","Cachoeiro de Itapemirim","Cariacica","Castelo","Colatina","Concei��o da Barra","Concei��o do Castelo","Divino de S�o Louren�o","Domingos Martins","Dores do Rio Preto",
				"Ecoporanga","Fund�o","Governador Lindenberg","Gua�u�","Guarapari","Ibatiba","Ibira�u","Ibitirama","Iconha","Irupi","Itagua�u","Itapemirim","Itarana","I�na","Jaguar�","Jer�nimo Monteiro","Jo�o Neiva","Laranja da Terra","Linhares","Manten�polis","Marataizes","Marechal Floriano","Maril�ndia",
				"Mimoso do Sul","Montanha","Mucurici","Muniz Freire","Muqui","Nova Ven�cia","Pancas","Pedro Can�rio","Pinheiros","Pi�ma","Ponto Belo","Presidente Kennedy","Rio Bananal","Rio Novo do Sul","Santa Leopoldina","Santa Maria de Jetib�","Santa Teresa","S�o Domingos do Norte","S�o Gabriel da Palha","S�o Jos� do Cal�ado","S�o Mateus","S�o Roque do Cana�","Serra",
				"Sooretama","Vargem Alta","Venda Nova do Imigrante","Viana","Vila Pav�o","Vila Val�rio","Vila Velha","Vit�ria"]},
				{"sigla": "GO","nome": "Goi�s","cidades": ["Abadia de Goi�s","Abadi�nia","Acre�na","Adel�ndia","�gua Fria de Goi�s","�gua Limpa","�guas Lindas de Goi�s","Alex�nia","Alo�ndia","Alto Horizonte","Alto Para�so de Goi�s","Alvorada do Norte","Amaralina","Americano do Brasil","Amorin�polis","An�polis",
				"Anhanguera","Anicuns","Aparecida de Goi�nia","Aparecida do Rio Doce","Apor�","Ara�u","Aragar�as","Aragoi�nia","Araguapaz","Aren�polis","Aruan�","Auril�ndia","Avelin�polis","Baliza","Barro Alto","Bela Vista de Goi�s","Bom Jardim de Goi�s","Bom Jesus de Goi�s","Bonfin�polis","Bon�polis","Brazabrantes","Brit�nia","Buriti Alegre","Buriti de Goi�s","Buritin�polis","Cabeceiras","Cachoeira Alta","Cachoeira de Goi�s","Cachoeira Dourada",
				"Ca�u","Caiap�nia","Caldas Novas","Caldazinha","Campestre de Goi�s","Campina�u","Campinorte","Campo Alegre de Goi�s","Campos Limpo de Goi�s","Campos Belos","Campos Verdes","Carmo do Rio Verde","Castel�ndia","Catal�o","Catura�","Cavalcante","Ceres","Cezarina","Chapad�o do C�u","Cidade Ocidental","Cocalzinho de Goi�s","Colinas do Sul","C�rrego do Ouro","Corumb� de Goi�s","Corumba�ba","Cristalina","Cristian�polis","Crix�s","Crom�nia",
				"Cumari","Damian�polis","Damol�ndia","Davin�polis","Diorama","Divin�polis de Goi�s","Doverl�ndia","Edealina","Ed�ia","Estrela do Norte","Faina","Fazenda Nova","Firmin�polis","Flores de Goi�s","Formosa","Formoso","Gameleira de Goi�s","Goian�polis","Goiandira","Goian�sia","Goi�nia","Goianira","Goi�s","Goiatuba","Gouvel�ndia",
				"Guap�","Guara�ta","Guarani de Goi�s","Guarinos","Heitora�","Hidrol�ndia","Hidrolina","Iaciara","Inaciol�ndia","Indiara","Inhumas","Ipameri","Ipiranga de Goi�s","Ipor�","Israel�ndia","Itabera�","Itaguari","Itaguaru","Itaj�","Itapaci","Itapirapu�","Itapuranga","Itarum�","Itau�u","Itumbiara",
				"Ivol�ndia","Jandaia","Jaragu�","Jata�","Jaupaci","Jes�polis","Jovi�nia","Jussara","Lagoa Santa","Leopoldo de Bulh�es","Luzi�nia","Mairipotaba","Mamba�","Mara Rosa","Marzag�o","Matrinch�","Mauril�ndia","Mimoso de Goi�s",
				"Mina�u","Mineiros","Moipor�","Monte Alegre de Goi�s","Montes Claros de Goi�s","Montividiu","Montividiu do Norte","Morrinhos","Morro Agudo de Goi�s","Moss�medes","Mozarl�ndia","Mundo Novo","Mutun�polis","Naz�rio","Ner�polis","Niquel�ndia","Nova Am�rica","Nova Aurora","Nova Crix�s","Nova Gl�ria","Nova Igua�u de Goi�s","Nova Roma","Nova Veneza","Novo Brasil","Novo Gama",
				"Novo Planalto","Orizona","Ouro Verde de Goi�s","Ouvidor","Padre Bernardo","Palestina de Goi�s","Palmeiras de Goi�s","Palmelo","Palmin�polis","Panam�","Paranaiguara","Para�na","Perol�ndia","Petrolina de Goi�s","Pilar de Goi�s","Piracanjuba","Piranhas","Piren�polis","Pires do Rio","Planaltina","Pontalina","Porangatu",
				"Porteir�o","Portel�ndia","Posse","Professor Jamil","Quirin�polis","Rialma","Rian�polis","Rio Quente","Rio Verde","Rubiataba","Sanclerl�ndia","Santa B�rbara de Goi�s","Santa Cruz de Goi�s","Santa F� de Goi�s","Santa Helena de Goi�s","Santa Isabel","Santa Rita do Araguaia","Santa Rita do Novo Destino",
				"Santa Rosa de Goi�s","Santa Tereza de Goi�s","Santa Terezinha de Goi�s","Santo Ant�nio da Barra","Santo Ant�nio de Goi�s","Santo Ant�nio do Descoberto","S�o Domingos","S�o Francisco de Goi�s","S�o Jo�o d'Alian�a","S�o Jo�o da Para�na","S�o Lu�s de Montes Belos","S�o Lu�z do Norte","S�o Miguel do Araguaia","S�o Miguel do Passa Quatro","S�o Patr�cio","S�o Sim�o","Senador Canedo","Serran�polis","Silv�nia","Simol�ndia","S�tio d'Abadia",
				"Taquaral de Goi�s","Teresina de Goi�s","Terez�polis de Goi�s","Tr�s Ranchos","Trindade","Trombas","Turv�nia","Turvel�ndia","Uirapuru","Urua�u","Uruana","Uruta�","Valpara�so de Goi�s","Varj�o","Vian�polis","Vicentin�polis","Vila Boa","Vila Prop�cio"]},
				{"sigla": "MA","nome": "Maranh�o","cidades": ["A�ail�ndia","Afonso Cunha","�gua Doce do Maranh�o","Alc�ntara","Aldeias Altas","Altamira do Maranh�o","Alto Alegre do Maranh�o","Alto Alegre do Pindar�","Alto Parna�ba","Amap� do Maranh�o","Amarante do Maranh�o","Anajatuba","Anapurus","Apicum-A�u","Araguan�","Araioses",
				"Arame","Arari","Axix�","Bacabal","Bacabeira","Bacuri","Bacurituba","Balsas","Bar�o de Graja�","Barra do Corda","Barreirinhas","Bela Vista do Maranh�o","Bel�gua","Benedito Leite","Bequim�o",
				"Bernardo do Mearim","Boa Vista do Gurupi","Bom Jardim","Bom Jesus das Selvas","Bom Lugar","Brejo","Brejo de Areia","Buriti","Buriti Bravo","Buriticupu","Buritirana","Cachoeira Grande","Cajapi�","Cajari","Campestre do Maranh�o","C�ndido Mendes","Cantanhede","Capinzal do Norte",
				"Carolina","Carutapera","Caxias","Cedral","Central do Maranh�o","Centro do Guilherme","Centro Novo do Maranh�o","Chapadinha","Cidel�ndia","Cod�","Coelho Neto","Colinas","Concei��o do Lago-A�u","Coroat�","Cururupu","Davin�polis","Dom Pedro","Duque Bacelar","Esperantin�polis","Estreito",
				"Feira Nova do Maranh�o","Fernando Falc�o","Formosa da Serra Negra","Fortaleza dos Nogueiras","Fortuna","Godofredo Viana","Gon�alves Dias","Governador Archer","Governador Edison Lob�o","Governador Eug�nio Barros","Governador Luiz Rocha","Governador Newton Bello","Governador Nunes Freire","Gra�a Aranha","Graja�","Guimar�es","Humberto de Campos","Icatu","Igarap� do Meio","Igarap� Grande","Imperatriz","Itaipava do Graja�","Itapecuru Mirim","Itinga do Maranh�o",
				"Jatob�","Jenipapo dos Vieiras","Jo�o Lisboa","Josel�ndia","Junco do Maranh�o","Lago da Pedra","Lago do Junco","Lago dos Rodrigues","Lago Verde","Lagoa do Mato","Lagoa Grande do Maranh�o","Lajeado Novo","Lima Campos","Loreto","Lu�s Domingues","Magalh�es de Almeida","Maraca�um�","Maraj� do Sena","Maranh�ozinho","Mata Roma",
				"Matinha","Mat�es","Mat�es do Norte","Milagres do Maranh�o","Mirador","Miranda do Norte","Mirinzal","Mon��o","Montes Altos","Morros","Nina Rodrigues","Nova Colinas","Nova Iorque","Nova Olinda do Maranh�o","Olho d'�gua das Cunh�s","Olinda Nova do Maranh�o","Pa�o do Lumiar","Palmeir�ndia","Paraibano","Parnarama","Passagem Franca","Pastos Bons","Paulino Neves","Paulo Ramos","Pedreiras",
				"Pedro do Ros�rio","Penalva","Peri Mirim","Peritor�","Pindar� Mirim","Pinheiro","Pio XII","Pirapemas","Po��o de Pedras","Porto Franco","Porto Rico do Maranh�o","Presidente Dutra","Presidente Juscelino","Presidente M�dici","Presidente Sarney","Presidente Vargas","Primeira Cruz","Raposa","Riach�o","Ribamar Fiquene","Ros�rio","Samba�ba",
				"Santa Filomena do Maranh�o","Santa Helena","Santa In�s","Santa Luzia","Santa Luzia do Paru�","Santa Quit�ria do Maranh�o","Santa Rita","Santana do Maranh�o","Santo Amaro do Maranh�o","Santo Ant�nio dos Lopes","S�o Benedito do Rio Preto","S�o Bento","S�o Bernardo","S�o Domingos do Azeit�o","S�o Domingos do Maranh�o","S�o F�lix de Balsas","S�o Francisco do Brej�o","S�o Francisco do Maranh�o","S�o Jo�o Batista","S�o Jo�o do Car�","S�o Jo�o do Para�so","S�o Jo�o do Soter","S�o Jo�o dos Patos","S�o Jos� de Ribamar",
				"S�o Jos� dos Bas�lios","S�o Lu�s","S�o Lu�s Gonzaga do Maranh�o","S�o Mateus do Maranh�o","S�o Pedro da �gua Branca","S�o Pedro dos Crentes","S�o Raimundo das Mangabeiras","S�o Raimundo do Doca Bezerra","S�o Roberto","S�o Vicente Ferrer","Satubinha","Senador Alexandre Costa","Senador La Rocque","Serrano do Maranh�o","S�tio Novo","Sucupira do Norte","Sucupira do Riach�o","Tasso Fragoso","Timbiras","Timon",
				"Trizidela do Vale","Tufil�ndia","Tuntum","Turia�u","Turil�ndia","Tut�ia","Urbano Santos","Vargem Grande","Viana","Vila Nova dos Mart�rios","Vit�ria do Mearim","Vitorino Freire","Z� Doca"]},
				{"sigla": "MG","nome": "Minas Gerais","cidades": ["Abadia dos Dourados","Abaet�","Abre Campo","Acaiaca","A�ucena","�gua Boa","�gua Comprida","Aguanil","�guas Formosas","�guas Vermelhas","Aimor�s","Aiuruoca","Alagoa","Albertina","Al�m Para�ba","Alfenas","Alfredo Vasconcelos","Almenara","Alpercata","Alpin�polis","Alterosa","Alto Capara�","Alto Jequitib�","Alto Rio Doce","Alvarenga",
				"Alvin�polis","Alvorada de Minas","Amparo do Serra","Andradas","Andrel�ndia","Angel�ndia","Ant�nio Carlos","Ant�nio Dias","Ant�nio Prado de Minas","Ara�a�","Aracitaba","Ara�ua�","Araguari","Arantina","Araponga","Arapor�","Arapu�","Ara�jos","Arax�","Arceburgo","Arcos","Areado","Argirita","Aricanduva","Arinos","Astolfo Dutra",
				"Atal�ia","Augusto de Lima","Baependi","Baldim","Bambu�","Bandeira","Bandeira do Sul","Bar�o de Cocais","Bar�o de Monte Alto","Barbacena","Barra Longa","Barroso","Bela Vista de Minas","Belmiro Braga","Belo Horizonte","Belo Oriente","Belo Vale","Berilo","Berizal","Bert�polis","Betim","Bias Fortes","Bicas","Biquinhas","Boa Esperan�a",
				"Bocaina de Minas","Bocai�va","Bom Despacho","Bom Jardim de Minas","Bom Jesus da Penha","Bom Jesus do Amparo","Bom Jesus do Galho","Bom Repouso","Bom Sucesso","Bonfim","Bonfin�polis de Minas","Bonito de Minas","Borda da Mata","Botelhos","Botumirim","Br�s Pires","Brasil�ndia de Minas","Bras�lia de Minas","Bras�polis","Bra�nas","Brumadinho","Bueno Brand�o","Buen�polis",
				"Bugre","Buritis","Buritizeiro","Cabeceira Grande","Cabo Verde","Cachoeira da Prata","Cachoeira de Minas","Cachoeira de Paje�","Cachoeira Dourada","Caetan�polis","Caet�","Caiana","Cajuri","Caldas","Camacho","Camanducaia","Cambu�","Cambuquira","Campan�rio","Campanha","Campestre","Campina Verde","Campo Azul","Campo Belo",
				"Campo do Meio","Campo Florido","Campos Altos","Campos Gerais","Cana Verde","Cana�","Can�polis","Candeias","Cantagalo","Capara�","Capela Nova","Capelinha","Capetinga","Capim Branco","Capin�polis","Capit�o Andrade","Capit�o En�as","Capit�lio","Caputira","Cara�","Carana�ba","Caranda�","Carangola","Caratinga","Carbonita",
				"Carea�u","Carlos Chagas","Carm�sia","Carmo da Cachoeira","Carmo da Mata","Carmo de Minas","Carmo do Cajuru","Carmo do Parana�ba","Carmo do Rio Claro","Carm�polis de Minas","Carneirinho","Carrancas","Carvalh�polis","Carvalhos","Casa Grande","Cascalho Rico","C�ssia","Cataguases","Catas Altas","Catas Altas da Noruega","Catuji",
				"Catuti","Caxambu","Cedro do Abaet�","Central de Minas","Centralina","Ch�cara","Chal�","Chapada do Norte","Chapada Ga�cha","Chiador","Cipot�nea","Claraval","Claro dos Po��es","Cl�udio","Coimbra","Coluna","Comendador Gomes","Comercinho","Concei��o da Aparecida","Concei��o da Barra de Minas","Concei��o das Alagoas","Concei��o das Pedras","Concei��o de Ipanema","Concei��o do Mato Dentro","Concei��o do Par�","Concei��o do Rio Verde","Concei��o dos Ouros","C�nego Marinho","Confins","Congonhal","Congonhas","Congonhas do Norte","Conquista","Conselheiro Lafaiete","Conselheiro Pena","Consola��o","Contagem","Coqueiral","Cora��o de Jesus","Cordisburgo","Cordisl�ndia","Corinto","Coroaci","Coromandel","Coronel Fabriciano",
				"Coronel Murta","Coronel Pacheco","Coronel Xavier Chaves","C�rrego Danta","C�rrego do Bom Jesus","C�rrego Fundo","C�rrego Novo","Couto de Magalh�es de Minas","Cris�lita","Cristais","Crist�lia","Cristiano Otoni","Cristina","Crucil�ndia","Cruzeiro da Fortaleza","Cruz�lia","Cuparaque","Curral de Dentro","Curvelo","Datas","Delfim Moreira",
				"Delfin�polis","Delta","Descoberto","Desterro de Entre Rios","Desterro do Melo","Diamantina","Diogo de Vasconcelos","Dion�sio","Divin�sia","Divino","Divino das Laranjeiras","Divinol�ndia de Minas","Divin�polis","Divisa Alegre","Divisa Nova","Divis�polis","Dom Bosco","Dom Cavati","Dom Joaquim","Dom Silv�rio","Dom Vi�oso","Dona Euz�bia","Dores de Campos","Dores de Guanh�es","Dores do Indai�","Dores do Turvo","Dores�polis","Douradoquara","Durand�","El�i Mendes","Engenheiro Caldas","Engenheiro Navarro","Entre Folhas","Entre Rios de Minas","Erv�lia","Esmeraldas","Espera Feliz","Espinosa","Esp�rito Santo do Dourado","Estiva","Estrela Dalva","Estrela do Indai�","Estrela do Sul","Eugen�polis","Ewbank da C�mara","Extrema","Fama","Faria Lemos","Fel�cio dos Santos","Felisburgo","Felixl�ndia","Fernandes Tourinho","Ferros","Fervedouro","Florestal","Formiga","Formoso","Fortaleza de Minas","Fortuna de Minas","Francisco Badar�","Francisco Dumont","Francisco S�","Francisc�polis","Frei Gaspar","Frei Inoc�ncio","Frei Lagonegro","Fronteira","Fronteira dos Vales","Fruta de Leite","Frutal","Funil�ndia","Galil�ia","Gameleiras","Glaucil�ndia","Goiabeira","Goian�","Gon�alves","Gonzaga","Gouveia","Governador Valadares","Gr�o Mogol","Grupiara","Guanh�es","Guap�","Guaraciaba","Guaraciama","Guaran�sia","Guarani","Guarar�","Guarda-Mor","Guaxup�","Guidoval","Guimar�nia","Guiricema","Gurinhat�","Heliodora","Iapu","Ibertioga","Ibi�","Ibia�","Ibiracatu","Ibiraci","Ibirit�","Ibiti�ra de Minas","Ibituruna","Icara� de Minas","Igarap�","Igaratinga","Iguatama","Ijaci","Ilic�nea","Imb� de Minas","Inconfidentes","Indaiabira","Indian�polis","Inga�","Inhapim","Inha�ma","Inimutaba","Ipaba","Ipanema","Ipatinga","Ipia�u","Ipui�na","Ira� de Minas","Itabira","Itabirinha de Mantena","Itabirito","Itacambira","Itacarambi","Itaguara","Itaip�","Itajub�","Itamarandiba","Itamarati de Minas","Itambacuri","Itamb� do Mato Dentro","Itamogi","Itamonte","Itanhandu","Itanhomi","Itaobim","Itapagipe","Itapecerica","Itapeva","Itatiaiu�u","Ita� de Minas","Ita�na","Itaverava","Itinga","Itueta","Ituiutaba","Itumirim","Iturama","Itutinga","Jaboticatubas","Jacinto","Jacu�","Jacutinga","Jaguara�u","Ja�ba","Jampruca","Jana�ba","Janu�ria","Japara�ba","Japonvar","Jeceaba","Jenipapo de Minas","Jequeri","Jequita�","Jequitib�","Jequitinhonha","Jesu�nia","Joa�ma","Joan�sia","Jo�o Monlevade","Jo�o Pinheiro","Joaquim Fel�cio","Jord�nia","Jos� Gon�alves de Minas","Jos� Raydan","Josen�polis","Juatuba","Juiz de Fora","Juramento","Juruaia","Juven�lia","Ladainha","Lagamar","Lagoa da Prata","Lagoa dos Patos","Lagoa Dourada","Lagoa Formosa","Lagoa Grande","Lagoa Santa","Lajinha","Lambari","Lamim","Laranjal","Lassance","Lavras","Leandro Ferreira",
				"Leme do Prado","Leopoldina","Liberdade","Lima Duarte","Limeira do Oeste","Lontra","Luisburgo","Luisl�ndia","Lumin�rias","Luz","Machacalis","Machado","Madre de Deus de Minas","Malacacheta","Mamonas","Manga","Manhua�u","Manhumirim","Mantena","Mar de Espanha","Maravilhas","Maria da F�","Mariana","Marilac","M�rio Campos","Marip� de Minas","Marli�ria","Marmel�polis","Martinho Campos","Martins Soares","Mata Verde","Materl�ndia","Mateus Leme","Mathias Lobato","Matias Barbosa","Matias Cardoso","Matip�","Mato Verde","Matozinhos","Matutina","Medeiros","Medina","Mendes Pimentel","Merc�s","Mesquita","Minas Novas","Minduri","Mirabela","Miradouro","Mira�","Mirav�nia","Moeda","Moema","Monjolos","Monsenhor Paulo","Montalv�nia","Monte Alegre de Minas","Monte Azul","Monte Belo",
				"Monte Carmelo","Monte Formoso","Monte Santo de Minas","Monte Si�o","Montes Claros","Montezuma","Morada Nova de Minas","Morro da Gar�a","Morro do Pilar","Munhoz","Muria�","Mutum","Muzambinho","Nacip Raydan","Nanuque","Naque","Natal�ndia","Nat�rcia","Nazareno","Nepomuceno","Ninheira","Nova Bel�m","Nova Era","Nova Lima","Nova M�dica","Nova Ponte","Nova Porteirinha","Nova Resende","Nova Serrana","Nova Uni�o","Novo Cruzeiro","Novo Oriente de Minas","Novorizonte","Olaria",
				"Olhos-d'�gua","Ol�mpio Noronha","Oliveira","Oliveira Fortes","On�a de Pitangui","Orat�rios","Oriz�nia","Ouro Branco","Ouro Fino","Ouro Preto","Ouro Verde de Minas","Padre Carvalho","Padre Para�so","Pai Pedro","Paineiras","Pains","Paiva","Palma","Palm�polis","Papagaios","Par� de Minas","Paracatu","Paragua�u","Parais�polis","Paraopeba","Passa Quatro","Passa Tempo","Passa-Vinte","Passab�m","Passos","Patis","Patos de Minas",
				"Patroc�nio","Patroc�nio do Muria�","Paula C�ndido","Paulistas","Pav�o","Pe�anha","Pedra Azul","Pedra Bonita","Pedra do Anta","Pedra do Indai�","Pedra Dourada","Pedralva","Pedras de Maria da Cruz","Pedrin�polis","Pedro Leopoldo","Pedro Teixeira","Pequeri","Pequi","Perdig�o","Perdizes","Perd�es","Periquito","Pescador","Piau","Piedade de Caratinga","Piedade de Ponte Nova","Piedade do Rio Grande","Piedade dos Gerais","Pimenta","Pingo-d'�gua","Pint�polis","Piracema","Pirajuba",
				"Piranga","Pirangu�u","Piranguinho","Pirapetinga","Pirapora","Pira�ba","Pitangui","Piumhi","Planura","Po�o Fundo","Po�os de Caldas","Pocrane","Pomp�u","Ponte Nova","Ponto Chique","Ponto dos Volantes","Porteirinha","Porto Firme","Pot�","Pouso Alegre","Pouso Alto","Prados","Prata","Prat�polis","Pratinha","Presidente Bernardes","Presidente Juscelino","Presidente Kubitschek","Presidente Oleg�rio","Prudente de Morais","Quartel Geral","Queluzito","Raposos",
				"Raul Soares","Recreio","Reduto","Resende Costa","Resplendor","Ressaquinha","Riachinho","Riacho dos Machados","Ribeir�o das Neves","Ribeir�o Vermelho","Rio Acima","Rio Casca","Rio do Prado","Rio Doce","Rio Espera","Rio Manso","Rio Novo","Rio Parana�ba","Rio Pardo de Minas","Rio Piracicaba","Rio Pomba","Rio Preto","Rio Vermelho","Rit�polis","Rochedo de Minas","Rodeiro","Romaria","Ros�rio da Limeira","Rubelita","Rubim","Sabar�","Sabin�polis","Sacramento",
				"Salinas","Salto da Divisa","Santa B�rbara","Santa B�rbara do Leste","Santa B�rbara do Monte Verde","Santa B�rbara do Tug�rio","Santa Cruz de Minas","Santa Cruz de Salinas","Santa Cruz do Escalvado","Santa Efig�nia de Minas","Santa F� de Minas","Santa Helena de Minas","Santa Juliana","Santa Luzia","Santa Margarida","Santa Maria de Itabira","Santa Maria do Salto","Santa Maria do Sua�u�","Santa Rita de Caldas","Santa Rita de Ibitipoca","Santa Rita de Jacutinga","Santa Rita de Minas","Santa Rita do Itueto","Santa Rita do Sapuca�","Santa Rosa da Serra","Santa Vit�ria","Santana da Vargem","Santana de Cataguases","Santana de Pirapama","Santana do Deserto","Santana do Garamb�u","Santana do Jacar�","Santana do Manhua�u",
				"Santana do Para�so","Santana do Riacho","Santana dos Montes","Santo Ant�nio do Amparo","Santo Ant�nio do Aventureiro","Santo Ant�nio do Grama","Santo Ant�nio do Itamb�","Santo Ant�nio do Jacinto","Santo Ant�nio do Monte","Santo Ant�nio do Retiro","Santo Ant�nio do Rio Abaixo","Santo Hip�lito","Santos Dumont","S�o Bento Abade","S�o Br�s do Sua�u�","S�o Domingos das Dores","S�o Domingos do Prata","S�o F�lix de Minas","S�o Francisco","S�o Francisco de Paula","S�o Francisco de Sales","S�o Francisco do Gl�ria","S�o Geraldo","S�o Geraldo da Piedade","S�o Geraldo do Baixio","S�o Gon�alo do Abaet�","S�o Gon�alo do Par�","S�o Gon�alo do Rio Abaixo","S�o Gon�alo do Rio Preto","S�o Gon�alo do Sapuca�","S�o Gotardo","S�o Jo�o Batista do Gl�ria","S�o Jo�o da Lagoa","S�o Jo�o da Mata",
				"S�o Jo�o da Ponte","S�o Jo�o das Miss�es","S�o Jo�o del Rei","S�o Jo�o do Manhua�u","S�o Jo�o do Manteninha","S�o Jo�o do Oriente","S�o Jo�o do Pacu�","S�o Jo�o do Para�so","S�o Jo�o Evangelista","S�o Jo�o Nepomuceno","S�o Joaquim de Bicas","S�o Jos� da Barra","S�o Jos� da Lapa","S�o Jos� da Safira","S�o Jos� da Varginha","S�o Jos� do Alegre","S�o Jos� do Divino","S�o Jos� do Goiabal","S�o Jos� do Jacuri","S�o Jos� do Mantimento","S�o Louren�o","S�o Miguel do Anta","S�o Pedro da Uni�o","S�o Pedro do Sua�u�","S�o Pedro dos Ferros","S�o Rom�o",
				"S�o Roque de Minas","S�o Sebasti�o da Bela Vista","S�o Sebasti�o da Vargem Alegre","S�o Sebasti�o do Anta","S�o Sebasti�o do Maranh�o","S�o Sebasti�o do Oeste","S�o Sebasti�o do Para�so","S�o Sebasti�o do Rio Preto","S�o Sebasti�o do Rio Verde","S�o Thom� das Letras","S�o Tiago","S�o Tom�s de Aquino","S�o Vicente de Minas","Sapuca�-Mirim","Sardo�","Sarzedo","Sem-Peixe","Senador Amaral","Senador Cortes","Senador Firmino","Senador Jos� Bento","Senador Modestino Gon�alves","Senhora de Oliveira","Senhora do Porto","Senhora dos Rem�dios","Sericita","Seritinga","Serra Azul de Minas",
				"Serra da Saudade","Serra do Salitre","Serra dos Aimor�s","Serrania","Serran�polis de Minas","Serranos","Serro","Sete Lagoas","Setubinha","Silveir�nia","Silvian�polis","Sim�o Pereira","Simon�sia","Sobr�lia","Soledade de Minas","Tabuleiro","Taiobeiras","Taparuba","Tapira","Tapira�","Taquara�u de Minas","Tarumirim","Teixeiras","Te�filo Otoni","Tim�teo","Tiradentes","Tiros","Tocantins","Tocos do Moji","Toledo","Tombos","Tr�s Cora��es","Tr�s Marias","Tr�s Pontas","Tumiritinga","Tupaciguara","Turmalina","Turvol�ndia","Ub�","Uba�","Ubaporanga","Uberaba","Uberl�ndia","Umburatiba","Una�","Uni�o de Minas","Uruana de Minas","Uruc�nia","Urucuia","Vargem Alegre","Vargem Bonita","Vargem Grande do Rio Pardo","Varginha","Varj�o de Minas","V�rzea da Palma","Varzel�ndia",
				"Vazante","Verdel�ndia","Veredinha","Ver�ssimo","Vermelho Novo","Vespasiano","Vi�osa","Vieiras","Virgem da Lapa","Virg�nia","Virgin�polis","Virgol�ndia","Visconde do Rio Branco","Volta Grande","Wenceslau Braz"]},
				{"sigla": "MS","nome": "Mato Grosso do Sul","cidades": ["�gua Clara","Alcin�polis","Amamba�","Anast�cio","Anauril�ndia","Ang�lica","Ant�nio Jo�o","Aparecida do Taboado","Aquidauana","Aral Moreira","Bandeirantes","Bataguassu","Bataipor�","Bela Vista","Bodoquena","Bonito","Brasil�ndia","Caarap�","Camapu�","Campo Grande","Caracol","Cassil�ndia","Chapad�o do Sul","Corguinho","Coronel Sapucaia","Corumb�","Costa Rica","Coxim","Deod�polis","Dois Irm�os do Buriti","Douradina","Dourados","Eldorado","F�tima do Sul","Gl�ria de Dourados","Guia Lopes da Laguna",
				"Iguatemi","Inoc�ncia","Itapor�","Itaquira�","Ivinhema","Japor�","Jaraguari","Jardim","Jate�","Juti","Lad�rio","Laguna Carap�","Maracaju","Miranda","Mundo Novo","Navira�","Nioaque","Nova Alvorada do Sul","Nova Andradina","Novo Horizonte do Sul","Parana�ba","Paranhos","Pedro Gomes","Ponta Por�","Porto Murtinho","Ribas do Rio Pardo","Rio Brilhante","Rio Negro","Rio Verde de Mato Grosso",
				"Rochedo","Santa Rita do Pardo","S�o Gabriel do Oeste","Selv�ria","Sete Quedas","Sidrol�ndia","Sonora","Tacuru","Taquarussu","Terenos","Tr�s Lagoas","Vicentina"]},
				{"sigla": "MT","nome": "Mato Grosso","cidades": ["Acorizal","�gua Boa","Alta Floresta","Alto Araguaia","Alto Boa Vista","Alto Gar�as","Alto Paraguai","Alto Taquari","Apiac�s","Araguaiana","Araguainha","Araputanga","Aren�polis","Aripuan�","Bar�o de Melga�o","Barra do Bugres","Barra do Gar�as","Bom Jesus do Araguaia","Brasnorte","C�ceres","Campin�polis","Campo Novo do Parecis","Campo Verde","Campos de J�lio","Canabrava do Norte","Canarana","Carlinda",
				"Castanheira","Chapada dos Guimar�es","Cl�udia","Cocalinho","Col�der","Colniza","Comodoro","Confresa","Conquista d'Oeste","Cotrigua�u","Curvel�ndia","Cuiab�","Denise","Diamantino","Dom Aquino","Feliz Natal","Figueir�polis d'Oeste","Ga�cha do Norte","General Carneiro","Gl�ria d'Oeste","Guarant� do Norte","Guiratinga","Indiava�","Ita�ba","Itiquira","Jaciara","Jangada","Jauru","Juara",
				"Ju�na","Juruena","Juscimeira","Lambari d'Oeste","Lucas do Rio Verde","Luci�ra","Marcel�ndia","Matup�","Mirassol d'Oeste","Nobres","Nortel�ndia","Nossa Senhora do Livramento","Nova Bandeirantes","Nova Brasil�ndia","Nova Can�a do Norte","Nova Guarita","Nova Lacerda","Nova Maril�ndia","Nova Maring�","Nova Monte Verde","Nova Mutum","Nova Nazar�","Nova Ol�mpia","Nova Santa Helena","Nova Ubirat�","Nova Xavantina","Novo Horizonte do Norte","Novo Mundo","Novo Santo Ant�nio","Novo S�o Joaquim","Parana�ta","Paranatinga",
				"Pedra Preta","Peixoto de Azevedo","Planalto da Serra","Pocon�","Pontal do Araguaia","Ponte Branca","Pontes e Lacerda","Porto Alegre do Norte","Porto dos Ga�chos","Porto Esperidi�o","Porto Estrela","Poxor�o","Primavera do Leste","Quer�ncia","Reserva do Caba�al","Ribeir�o Cascalheira","Ribeir�ozinho","Rio Branco","Rondol�ndia","Rondon�polis","Ros�rio Oeste","Salto do C�u","Santa Carmem","Santa Cruz do Xingu","Santa Rita do Trivelato","Santa Terezinha","Santo Afonso",
				"Santo Ant�nio do Leste","Santo Ant�nio do Leverger","S�o F�lix do Araguaia","S�o Jos� do Povo","S�o Jos� do Rio Claro","S�o Jos� do Xingu","S�o Jos� dos Quatro Marcos","S�o Pedro da Cipa","Sapezal","Serra Nova Dourada","Sinop","Sorriso","Tabapor�","Tangar� da Serra","Tapurah","Terra Nova do Norte","Tesouro","Torixor�u","Uni�o do Sul","Vale de S�o Domingos","V�rzea Grande","Vera","Vila Bela da Sant�ssima Trindade","Vila Rica"]},
				{"sigla": "PA","nome": "Par�","cidades": ["Abaetetuba","Abel Figueiredo","Acar�","Afu�","�gua Azul do Norte","Alenquer","Almeirim","Altamira","Anaj�s","Ananindeua","Anapu","Augusto Corr�a","Aurora do Par�","Aveiro","Bagre","Bai�o","Bannach","Barcarena","Bel�m","Belterra","Benevides","Bom Jesus do Tocantins","Bonito","Bragan�a","Brasil Novo","Brejo Grande do Araguaia","Breu Branco","Breves","Bujaru",
				"Cachoeira do Arari","Cachoeira do Piri�","Camet�","Cana� dos Caraj�s","Capanema","Capit�o Po�o","Castanhal","Chaves","Colares","Concei��o do Araguaia","Conc�rdia do Par�","Cumaru do Norte","Curion�polis","Curralinho","Curu�","Curu��","Dom Eliseu","Eldorado dos Caraj�s","Faro","Floresta do Araguaia","Garraf�o do Norte","Goian�sia do Par�","Gurup�","Igarap�-A�u","Igarap�-Miri","Inhangapi","Ipixuna do Par�","Irituia","Itaituba","Itupiranga","Jacareacanga",
				"Jacund�","Juruti","Limoeiro do Ajuru","M�e do Rio","Magalh�es Barata","Marab�","Maracan�","Marapanim","Marituba","Medicil�ndia","Melga�o","Mocajuba","Moju","Monte Alegre","Muan�","Nova Esperan�a do Piri�","Nova Ipixuna","Nova Timboteua","Novo Progresso","Novo Repartimento","�bidos","Oeiras do Par�","Oriximin�","Our�m","Ouril�ndia do Norte","Pacaj�","Palestina do Par�","Paragominas","Parauapebas",
				"Pau d'Arco","Peixe-Boi","Pi�arra","Placas","Ponta de Pedras","Portel","Porto de Moz","Prainha","Primavera","Quatipuru","Reden��o","Rio Maria","Rondon do Par�","Rur�polis","Salin�polis","Salvaterra","Santa B�rbara do Par�","Santa Cruz do Arari","Santa Isabel do Par�","Santa Luzia do Par�","Santa Maria das Barreiras","Santa Maria do Par�","Santana do Araguaia","Santar�m","Santar�m Novo",
				"Santo Ant�nio do Tau�","S�o Caetano de Odivela","S�o Domingos do Araguaia","S�o Domingos do Capim","S�o F�lix do Xingu","S�o Francisco do Par�","S�o Geraldo do Araguaia","S�o Jo�o da Ponta","S�o Jo�o de Pirabas","S�o Jo�o do Araguaia","S�o Miguel do Guam�","S�o Sebasti�o da Boa Vista","Sapucaia","Senador Jos� Porf�rio","Soure","Tail�ndia","Terra Alta","Terra Santa","Tom�-A�u","Tracuateua","Trair�o","Tucum�","Tucuru�","Ulian�polis","Uruar�","Vigia","Viseu","Vit�ria do Xingu","Xinguara"]},
				{"sigla": "PB","nome": "Para�ba","cidades": ["�gua Branca","Aguiar","Alagoa Grande","Alagoa Nova","Alagoinha","Alcantil","Algod�o de Janda�ra","Alhandra","Amparo","Aparecida","Ara�agi","Arara","Araruna","Areia","Areia de Bara�nas","Areial","Aroeiras","Assun��o","Ba�a da Trai��o","Bananeiras","Bara�na","Barra de Santa Rosa","Barra de Santana","Barra de S�o Miguel","Bayeux","Bel�m","Bel�m do Brejo do Cruz","Bernardino Batista",
				"Boa Ventura","Boa Vista","Bom Jesus","Bom Sucesso","Bonito de Santa F�","Boqueir�o","Borborema","Brejo do Cruz","Brejo dos Santos","Caapor�","Cabaceiras","Cabedelo","Cachoeira dos �ndios","Cacimba de Areia","Cacimba de Dentro","Cacimbas","Cai�ara","Cajazeiras","Cajazeirinhas","Caldas Brand�o","Camala�","Campina Grande","Campo de Santana","Capim",
				"Cara�bas","Carrapateira","Casserengue","Catingueira","Catol� do Rocha","Caturit�","Concei��o","Condado","Conde","Congo","Coremas","Coxixola","Cruz do Esp�rito Santo","Cubati","Cuit�","Cuit� de Mamanguape","Cuitegi","Curral de Cima","Curral Velho","Dami�o","Desterro","Diamante","Dona In�s","Duas Estradas","Emas","Esperan�a","Fagundes","Frei Martinho","Gado Bravo","Guarabira","Gurinh�m","Gurj�o","Ibiara","Igaracy","Imaculada","Ing�",
				"Itabaiana","Itaporanga","Itapororoca","Itatuba","Jacara�","Jeric�","Jo�o Pessoa","Juarez T�vora","Juazeirinho","Junco do Serid�","Juripiranga","Juru","Lagoa","Lagoa de Dentro","Lagoa Seca","Lastro","Livramento","Logradouro","Lucena","M�e d'�gua","Malta","Mamanguape","Mana�ra","Marca��o","Mari","Mariz�polis","Massaranduba","Mataraca","Matinhas","Mato Grosso","Matur�ia","Mogeiro","Montadas","Monte Horebe",
				"Monteiro","Mulungu","Natuba","Nazarezinho","Nova Floresta","Nova Olinda","Nova Palmeira","Olho d'�gua","Olivedos","Ouro Velho","Parari","Passagem","Patos","Paulista","Pedra Branca","Pedra Lavrada","Pedras de Fogo","Pedro R�gis","Pianc�","Picu�","Pilar","Pil�es","Pil�ezinhos","Pirpirituba","Pitimbu","Pocinhos","Po�o Dantas","Po�o de Jos� de Moura","Pombal","Prata","Princesa Isabel",
				"Puxinan�","Queimadas","Quixab�","Rem�gio","Riach�o","Riach�o do Bacamarte","Riach�o do Po�o","Riacho de Santo Ant�nio","Riacho dos Cavalos","Rio Tinto","Salgadinho","Salgado de S�o F�lix","Santa Cec�lia","Santa Cruz","Santa Helena","Santa In�s","Santa Luzia","Santa Rita","Santa Teresinha","Santana de Mangueira","Santana dos Garrotes","Santar�m","Santo Andr�","S�o Bentinho","S�o Bento","S�o Domingos de Pombal","S�o Domingos do Cariri","S�o Francisco","S�o Jo�o do Cariri","S�o Jo�o do Rio do Peixe","S�o Jo�o do Tigre","S�o Jos� da Lagoa Tapada",
				"S�o Jos� de Caiana","S�o Jos� de Espinharas","S�o Jos� de Piranhas","S�o Jos� de Princesa","S�o Jos� do Bonfim","S�o Jos� do Brejo do Cruz","S�o Jos� do Sabugi","S�o Jos� dos Cordeiros","S�o Jos� dos Ramos","S�o Mamede","S�o Miguel de Taipu","S�o Sebasti�o de Lagoa de Ro�a","S�o Sebasti�o do Umbuzeiro","Sap�","Serid�","Serra Branca","Serra da Raiz","Serra Grande","Serra Redonda","Serraria","Sert�ozinho","Sobrado","Sol�nea","Soledade","Soss�go","Sousa","Sum�","Tapero�","Tavares","Teixeira",
				"Ten�rio","Triunfo","Uira�na","Umbuzeiro","V�rzea","Vieir�polis","Vista Serrana","Zabel�"]},
				{"sigla": "PE","nome": "Pernambuco","cidades": ["Abreu e Lima","Afogados da Ingazeira","Afr�nio","Agrestina","�gua Preta","�guas Belas","Alagoinha","Alian�a","Altinho","Amaraji","Angelim","Ara�oiaba","Araripina","Arcoverde","Barra de Guabiraba","Barreiros","Bel�m de Maria","Bel�m de S�o Francisco","Belo Jardim","Bet�nia","Bezerros","Bodoc�","Bom Conselho","Bom Jardim","Bonito","Brej�o","Brejinho",
				"Brejo da Madre de Deus","Buenos Aires","Bu�que","Cabo de Santo Agostinho","Cabrob�","Cachoeirinha","Caet�s","Cal�ado","Calumbi","Camaragibe","Camocim de S�o F�lix","Camutanga","Canhotinho","Capoeiras","Carna�ba","Carnaubeira da Penha","Carpina","Caruaru","Casinhas","Catende","Cedro","Ch� de Alegria","Ch� Grande","Condado","Correntes","Cort�s","Cumaru","Cupira",
				"Cust�dia","Dormentes","Escada","Exu","Feira Nova","Fernando de Noronha","Ferreiros","Flores","Floresta","Frei Miguelinho","Gameleira","Garanhuns","Gl�ria do Goit�","Goiana","Granito","Gravat�","Iati","Ibimirim","Ibirajuba","Igarassu","Iguaraci","Inaj�","Ingazeira","Ipojuca","Ipubi","Itacuruba","Ita�ba","Itamarac�","Itamb�",
				"Itapetim","Itapissuma","Itaquitinga","Jaboat�o dos Guararapes","Jaqueira","Jata�ba","Jatob�","Jo�o Alfredo","Joaquim Nabuco","Jucati","Jupi","Jurema","Lagoa do Carro","Lagoa do Itaenga","Lagoa do Ouro","Lagoa dos Gatos","Lagoa Grande","Lajedo","Limoeiro","Macaparana","Machados","Manari","Maraial","Mirandiba","Moreil�ndia","Moreno","Nazar� da Mata","Olinda","Orob�","Oroc�","Ouricuri","Palmares","Palmeirina","Panelas","Paranatama","Parnamirim",
				"Passira","Paudalho","Paulista","Pedra","Pesqueira","Petrol�ndia","Petrolina","Po��o","Pombos","Primavera","Quipap�","Quixaba","Recife","Riacho das Almas","Ribeir�o","Rio Formoso","Sair�","Salgadinho","Salgueiro","Salo�","Sanhar�","Santa Cruz","Santa Cruz da Baixa Verde","Santa Cruz do Capibaribe","Santa Filomena","Santa Maria da Boa Vista","Santa Maria do Cambuc�","Santa Terezinha","S�o Benedito do Sul","S�o Bento do Una","S�o Caitano",
				"S�o Jo�o","S�o Joaquim do Monte","S�o Jos� da Coroa Grande","S�o Jos� do Belmonte","S�o Jos� do Egito","S�o Louren�o da Mata","S�o Vicente Ferrer","Serra Talhada","Serrita","Sert�nia","Sirinha�m","Solid�o","Surubim","Tabira","Tacaimb�","Tacaratu","Tamandar�","Taquaritinga do Norte","Terezinha","Terra Nova","Timba�ba","Toritama","Tracunha�m","Trindade","Triunfo","Tupanatinga","Tuparetama","Venturosa","Verdejante","Vertente do L�rio","Vertentes",
				"Vic�ncia","Vit�ria de Santo Ant�o","Xex�u"]},
				{"sigla": "PI","nome": "Piau�","cidades": ["Acau�","Agricol�ndia","�gua Branca","Alagoinha do Piau�","Alegrete do Piau�","Alto Long�","Altos","Alvorada do Gurgu�ia","Amarante","Angical do Piau�","An�sio de Abreu","Ant�nio Almeida","Aroazes","Arraial","Assun��o do Piau�","Avelino Lopes","Baixa Grande do Ribeiro","Barra d'Alc�ntara","Barras","Barreiras do Piau�","Barro Duro","Batalha","Bela Vista do Piau�","Bel�m do Piau�","Beneditinos","Bertol�nia","Bet�nia do Piau�","Boa Hora",
				"Bocaina","Bom Jesus","Bom Princ�pio do Piau�","Bonfim do Piau�","Boqueir�o do Piau�","Brasileira","Brejo do Piau�","Buriti dos Lopes","Buriti dos Montes","Cabeceiras do Piau�","Cajazeiras do Piau�","Cajueiro da Praia","Caldeir�o Grande do Piau�","Campinas do Piau�","Campo Alegre do Fidalgo","Campo Grande do Piau�","Campo Largo do Piau�","Campo Maior","Canavieira","Canto do Buriti","Capit�o de Campos","Capit�o Gerv�sio Oliveira","Caracol","Cara�bas do Piau�","Caridade do Piau�","Castelo do Piau�","Caxing�",
				"Cocal","Cocal de Telha","Cocal dos Alves","Coivaras","Col�nia do Gurgu�ia","Col�nia do Piau�","Concei��o do Canind�","Coronel Jos� Dias","Corrente","Cristal�ndia do Piau�","Cristino Castro","Curimat�","Currais","Curral Novo do Piau�","Curralinhos","Demerval Lob�o","Dirceu Arcoverde","Dom Expedito Lopes","Dom Inoc�ncio","Domingos Mour�o","Elesb�o Veloso","Eliseu Martins","Esperantina","Fartura do Piau�","Flores do Piau�","Floresta do Piau�","Floriano","Francin�polis","Francisco Ayres","Francisco Macedo","Francisco Santos","Fronteiras","Geminiano",
				"Gilbu�s","Guadalupe","Guaribas","Hugo Napole�o","Ilha Grande","Inhuma","Ipiranga do Piau�","Isa�as Coelho","Itain�polis","Itaueira","Jacobina do Piau�","Jaic�s","Jardim do Mulato","Jatob� do Piau�","Jerumenha","Jo�o Costa","Joaquim Pires","Joca Marques","Jos� de Freitas","Juazeiro do Piau�","J�lio Borges","Jurema","Lagoa Alegre","Lagoa de S�o Francisco","Lagoa do Barro do Piau�","Lagoa do Piau�","Lagoa do S�tio","Lagoinha do Piau�","Landri Sales",
				"Lu�s Correia","Luzil�ndia","Madeiro","Manoel Em�dio","Marcol�ndia","Marcos Parente","Massap� do Piau�","Matias Ol�mpio","Miguel Alves","Miguel Le�o","Milton Brand�o","Monsenhor Gil","Monsenhor Hip�lito","Monte Alegre do Piau�","Morro Cabe�a no Tempo","Morro do Chap�u do Piau�","Murici dos Portelas","Nazar� do Piau�","Nossa Senhora de Nazar�","Nossa Senhora dos Rem�dios","Nova Santa Rita","Novo Oriente do Piau�","Novo Santo Ant�nio","Oeiras","Olho d'�gua do Piau�","Padre Marcos","Paes Landim","Paje� do Piau�","Palmeira do Piau�","Palmeirais","Paquet�",
				"Parnagu�","Parna�ba","Passagem Franca do Piau�","Patos do Piau�","Pau d'Arco do Piau�","Paulistana","Pavussu","Pedro II","Pedro Laurentino","Picos","Pimenteiras","Pio IX","Piracuruca","Piripiri","Porto","Porto Alegre do Piau�","Prata do Piau�","Queimada Nova","Reden��o do Gurgu�ia","Regenera��o","Riacho Frio","Ribeira do Piau�","Ribeiro Gon�alves","Rio Grande do Piau�","Santa Cruz do Piau�","Santa Cruz dos Milagres","Santa Filomena",
				"Santa Luz","Santa Rosa do Piau�","Santana do Piau�","Santo Ant�nio de Lisboa","Santo Ant�nio dos Milagres","Santo In�cio do Piau�","S�o Braz do Piau�","S�o F�lix do Piau�","S�o Francisco de Assis do Piau�","S�o Francisco do Piau�","S�o Gon�alo do Gurgu�ia","S�o Gon�alo do Piau�","S�o Jo�o da Canabrava","S�o Jo�o da Fronteira","S�o Jo�o da Serra","S�o Jo�o da Varjota","S�o Jo�o do Arraial","S�o Jo�o do Piau�","S�o Jos� do Divino","S�o Jos� do Peixe","S�o Jos� do Piau�","S�o Juli�o","S�o Louren�o do Piau�","S�o Luis do Piau�","S�o Miguel da Baixa Grande","S�o Miguel do Fidalgo","S�o Miguel do Tapuio","S�o Pedro do Piau�","S�o Raimundo Nonato","Sebasti�o Barros","Sebasti�o Leal",
				"Sigefredo Pacheco","Sim�es","Simpl�cio Mendes","Socorro do Piau�","Sussuapara","Tamboril do Piau�","Tanque do Piau�","Teresina","Uni�o","Uru�u�","Valen�a do Piau�","V�rzea Branca","V�rzea Grande","Vera Mendes","Vila Nova do Piau�","Wall Ferraz"]},
				{"sigla": "PR","nome": "Paran�","cidades": ["Abati�","Adrian�polis","Agudos do Sul","Almirante Tamandar�","Altamira do Paran�","Alto Paran�","Alto Piquiri","Alt�nia","Alvorada do Sul","Amapor�","Amp�re","Anahy","Andir�","�ngulo","Antonina","Ant�nio Olinto","Apucarana","Arapongas","Arapoti","Arapu�","Araruna","Arauc�ria","Ariranha do Iva�","Assa�","Assis Chateaubriand","Astorga","Atalaia",
				"Balsa Nova","Bandeirantes","Barbosa Ferraz","Barra do Jacar�","Barrac�o","Bela Vista da Caroba","Bela Vista do Para�so","Bituruna","Boa Esperan�a","Boa Esperan�a do Igua�u","Boa Ventura de S�o Roque","Boa Vista da Aparecida","Bocai�va do Sul","Bom Jesus do Sul","Bom Sucesso","Bom Sucesso do Sul","Borraz�polis","Braganey","Brasil�ndia do Sul","Cafeara","Cafel�ndia","Cafezal do Sul","Calif�rnia","Cambar�","Camb�","Cambira","Campina da Lagoa","Campina do Sim�o","Campina Grande do Sul",
				"Campo Bonito","Campo do Tenente","Campo Largo","Campo Magro","Campo Mour�o","C�ndido de Abreu","Cand�i","Cantagalo","Capanema","Capit�o Le�nidas Marques","Carambe�","Carl�polis","Cascavel","Castro","Catanduvas","Centen�rio do Sul","Cerro Azul","C�u Azul","Chopinzinho","Cianorte","Cidade Ga�cha","Clevel�ndia","Colombo","Colorado","Congonhinhas","Conselheiro Mairinck","Contenda","Corb�lia",
				"Corn�lio Proc�pio","Coronel Domingos Soares","Coronel Vivida","Corumbata� do Sul","Cruz Machado","Cruzeiro do Igua�u","Cruzeiro do Oeste","Cruzeiro do Sul","Cruzmaltina","Curitiba","Curi�va","Diamante d'Oeste","Diamante do Norte","Diamante do Sul","Dois Vizinhos","Douradina","Doutor Camargo","Doutor Ulysses","En�as Marques","Engenheiro Beltr�o","Entre Rios do Oeste","Esperan�a Nova","Espig�o Alto do Igua�u","Farol","Faxinal","Fazenda Rio Grande","F�nix","Fernandes Pinheiro","Figueira","Flor da Serra do Sul",
				"Flora�","Floresta","Florest�polis","Fl�rida","Formosa do Oeste","Foz do Igua�u","Foz do Jord�o","Francisco Alves","Francisco Beltr�o","General Carneiro","Godoy Moreira","Goioer�","Goioxim","Grandes Rios","Gua�ra","Guaira��","Guamiranga","Guapirama","Guaporema","Guaraci","Guarania�u","Guarapuava","Guaraque�aba","Guaratuba","Hon�rio Serpa","Ibaiti","Ibema","Ibipor�","Icara�ma",
				"Iguara�u","Iguatu","Imba�","Imbituva","In�cio Martins","Inaj�","Indian�polis","Ipiranga","Ipor�","Iracema do Oeste","Irati","Iretama","Itaguaj�","Itaipul�ndia","Itambarac�","Itamb�","Itapejara d'Oeste","Itaperu�u","Ita�na do Sul","Iva�","Ivaipor�","Ivat�","Ivatuba","Jaboti","Jacarezinho","Jaguapit�","Jaguaria�va","Jandaia do Sul","Jani�polis","Japira","Japur�","Jardim Alegre",
				"Jardim Olinda","Jataizinho","Jesu�tas","Joaquim T�vora","Jundia� do Sul","Juranda","Jussara","Kalor�","Lapa","Laranjal","Laranjeiras do Sul","Le�polis","Lidian�polis","Lindoeste","Loanda","Lobato","Londrina","Luiziana","Lunardelli","Lupion�polis","Mallet","Mambor�","Mandagua�u","Mandaguari","Mandirituba","Manfrin�polis","Mangueirinha",
				"Manoel Ribas","Marechal C�ndido Rondon","Maria Helena","Marialva","Maril�ndia do Sul","Marilena","Mariluz","Maring�","Mari�polis","Marip�","Marmeleiro","Marquinho","Marumbi","Matel�ndia","Matinhos","Mato Rico","Mau� da Serra","Medianeira","Mercedes","Mirador","Miraselva","Missal","Moreira Sales","Morretes","Munhoz de Melo",
				"Nossa Senhora das Gra�as","Nova Alian�a do Iva�","Nova Am�rica da Colina","Nova Aurora","Nova Cantu","Nova Esperan�a","Nova Esperan�a do Sudoeste","Nova F�tima","Nova Laranjeiras","Nova Londrina","Nova Ol�mpia","Nova Prata do Igua�u","Nova Santa B�rbara","Nova Santa Rosa","Nova Tebas","Novo Itacolomi","Ortigueira","Ourizona","Ouro Verde do Oeste","Pai�andu","Palmas","Palmeira","Palmital","Palotina","Para�so do Norte","Paranacity","Paranagu�","Paranapoema",
				"Paranava�","Pato Bragado","Pato Branco","Paula Freitas","Paulo Frontin","Peabiru","Perobal","P�rola","P�rola d'Oeste","Pi�n","Pinhais","Pinhal de S�o Bento","Pinhal�o","Pinh�o","Pira� do Sul","Piraquara","Pitanga","Pitangueiras","Planaltina do Paran�","Planalto","Ponta Grossa","Pontal do Paran�","Porecatu","Porto Amazonas",
				"Porto Barreiro","Porto Rico","Porto Vit�ria","Prado Ferreira","Pranchita","Presidente Castelo Branco","Primeiro de Maio","Prudent�polis","Quarto Centen�rio","Quatigu�","Quatro Barras","Quatro Pontes","Quedas do Igua�u","Quer�ncia do Norte","Quinta do Sol","Quitandinha","Ramil�ndia","Rancho Alegre","Rancho Alegre d'Oeste","Realeza","Rebou�as","Renascen�a","Reserva","Reserva do Igua�u","Ribeir�o Claro","Ribeir�o do Pinhal","Rio Azul","Rio Bom","Rio Bonito do Igua�u","Rio Branco do Iva�",
				"Rio Branco do Sul","Rio Negro","Rol�ndia","Roncador","Rondon","Ros�rio do Iva�","Sab�udia","Salgado Filho","Salto do Itarar�","Salto do Lontra","Santa Am�lia","Santa Cec�lia do Pav�o","Santa Cruz Monte Castelo","Santa F�","Santa Helena","Santa In�s","Santa Isabel do Iva�","Santa Izabel do Oeste","Santa L�cia","Santa Maria do Oeste","Santa Mariana","Santa M�nica","Santa Tereza do Oeste","Santa Terezinha de Itaipu","Santana do Itarar�",
				"Santo Ant�nio da Platina","Santo Ant�nio do Caiu�","Santo Ant�nio do Para�so","Santo Ant�nio do Sudoeste","Santo In�cio","S�o Carlos do Iva�","S�o Jer�nimo da Serra","S�o Jo�o","S�o Jo�o do Caiu�","S�o Jo�o do Iva�","S�o Jo�o do Triunfo","S�o Jorge d'Oeste","S�o Jorge do Iva�","S�o Jorge do Patroc�nio","S�o Jos� da Boa Vista","S�o Jos� das Palmeiras","S�o Jos� dos Pinhais","S�o Manoel do Paran�","S�o Mateus do Sul","S�o Miguel do Igua�u","S�o Pedro do Igua�u","S�o Pedro do Iva�","S�o Pedro do Paran�","S�o Sebasti�o da Amoreira","S�o Tom�","Sapopema","Sarandi","Saudade do Igua�u",
				"Seng�s","Serran�polis do Igua�u","Sertaneja","Sertan�polis","Siqueira Campos","Sulina","Tamarana","Tamboara","Tapejara","Tapira","Teixeira Soares","Tel�maco Borba","Terra Boa","Terra Rica","Terra Roxa","Tibagi","Tijucas do Sul","Toledo","Tomazina","Tr�s Barras do Paran�","Tunas do Paran�","Tuneiras do Oeste","Tup�ssi","Turvo","Ubirat�","Umuarama","Uni�o da Vit�ria","Uniflor","Ura�",
				"Ventania","Vera Cruz do Oeste","Ver�","Vila Alta","Virmond","Vitorino","Wenceslau Braz","Xambr�"]},
				{"sigla": "RJ","nome": "Rio de Janeiro","cidades": ["Angra dos Reis","Aperib�","Araruama","Areal","Arma��o de B�zios","Arraial do Cabo","Barra do Pira�","Barra Mansa","Belford Roxo","Bom Jardim","Bom Jesus do Itabapoana","Cabo Frio","Cachoeiras de Macacu","Cambuci","Campos dos Goytacazes","Cantagalo","Carapebus","Cardoso Moreira","Carmo","Casimiro de Abreu","Comendador Levy Gasparian","Concei��o de Macabu","Cordeiro","Duas Barras","Duque de Caxias",
				"Engenheiro Paulo de Frontin","Guapimirim","Iguaba Grande","Itabora�","Itagua�","Italva","Itaocara","Itaperuna","Itatiaia","Japeri","Laje do Muria�","Maca�","Macuco","Mag�","Mangaratiba","Maric�","Mendes","Mesquita","Miguel Pereira","Miracema","Natividade","Nil�polis","Niter�i","Nova Friburgo","Nova Igua�u",
				"Paracambi","Para�ba do Sul","Parati","Paty do Alferes","Petr�polis","Pinheiral","Pira�","Porci�ncula","Porto Real","Quatis","Queimados","Quissam�","Resende","Rio Bonito","Rio Claro","Rio das Flores","Rio das Ostras","Rio de Janeiro","Santa Maria Madalena","Santo Ant�nio de P�dua","S�o Fid�lis","S�o Francisco de Itabapoana",
				"S�o Gon�alo","S�o Jo�o da Barra","S�o Jo�o de Meriti","S�o Jos� de Ub�","S�o Jos� do Vale do Rio Preto","S�o Pedro da Aldeia","S�o Sebasti�o do Alto","Sapucaia","Saquarema","Serop�dica","Silva Jardim","Sumidouro","Tangu�","Teres�polis","Trajano de Morais","Tr�s Rios","Valen�a","Varre-Sai","Vassouras","Volta Redonda"]},
				{"sigla": "RN","nome": "Rio Grande do Norte","cidades": ["Acari","A�u","Afonso Bezerra","�gua Nova","Alexandria","Almino Afonso","Alto do Rodrigues","Angicos","Ant�nio Martins","Apodi","Areia Branca","Ar�s","Augusto Severo","Ba�a Formosa","Bara�na","Barcelona","Bento Fernandes","Bod�","Bom Jesus","Brejinho","Cai�ara do Norte","Cai�ara do Rio do Vento","Caic�","Campo Redondo","Canguaretama","Cara�bas","Carna�ba dos Dantas","Carnaubais","Cear�-Mirim","Cerro Cor�","Coronel Ezequiel","Coronel Jo�o Pessoa","Cruzeta",
				"Currais Novos","Doutor Severiano","Encanto","Equador","Esp�rito Santo","Extremoz","Felipe Guerra","Fernando Pedroza","Flor�nia","Francisco Dantas","Frutuoso Gomes","Galinhos","Goianinha","Governador Dix-Sept Rosado","Grossos","Guamar�","Ielmo Marinho","Ipangua�u","Ipueira","Itaj�","Ita�","Ja�an�","Janda�ra","Jandu�s","Janu�rio Cicco","Japi","Jardim de Angicos","Jardim de Piranhas","Jardim do Serid�","Jo�o C�mara","Jo�o Dias","Jos� da Penha",
				"Jucurutu","Jundi�","Lagoa d'Anta","Lagoa de Pedras","Lagoa de Velhos","Lagoa Nova","Lagoa Salgada","Lajes","Lajes Pintadas","Lucr�cia","Lu�s Gomes","Maca�ba","Macau","Major Sales","Marcelino Vieira","Martins","Maxaranguape","Messias Targino","Montanhas","Monte Alegre","Monte das Gameleiras","Mossor�","Natal","N�sia Floresta","Nova Cruz","Olho-d'�gua do Borges","Ouro Branco","Paran�","Para�","Parazinho",
				"Parelhas","Parnamirim","Passa e Fica","Passagem","Patu","Pau dos Ferros","Pedra Grande","Pedra Preta","Pedro Avelino","Pedro Velho","Pend�ncias","Pil�es","Po�o Branco","Portalegre","Porto do Mangue","Presidente Juscelino","Pureza","Rafael Fernandes","Rafael Godeiro","Riacho da Cruz","Riacho de Santana","Riachuelo","Rio do Fogo","Rodolfo Fernandes","Ruy Barbosa","Santa Cruz","Santa Maria","Santana do Matos","Santana do Serid�","Santo Ant�nio","S�o Bento do Norte","S�o Bento do Trair�","S�o Fernando","S�o Francisco do Oeste","S�o Gon�alo do Amarante",
				"S�o Jo�o do Sabugi","S�o Jos� de Mipibu","S�o Jos� do Campestre","S�o Jos� do Serid�","S�o Miguel","S�o Miguel de Touros","S�o Paulo do Potengi","S�o Pedro","S�o Rafael","S�o Tom�","S�o Vicente","Senador El�i de Souza","Senador Georgino Avelino","Serra de S�o Bento","Serra do Mel","Serra Negra do Norte","Serrinha","Serrinha dos Pintos","Severiano Melo","S�tio Novo","Taboleiro Grande","Taipu","Tangar�","Tenente Ananias","Tenente Laurentino Cruz","Tibau","Tibau do Sul","Timba�ba dos Batistas","Touros",
				"Triunfo Potiguar","Umarizal","Upanema","V�rzea","Venha-Ver","Vera Cruz","Vi�osa","Vila Flor"]},
				{"sigla": "RO","nome": "Rond�nia","cidades": ["Alta Floresta d'Oeste","Alto Alegre do Parecis","Alto Para�so","Alvorada d'Oeste","Ariquemes","Buritis","Cabixi","Cacaul�ndia","Cacoal","Campo Novo de Rond�nia","Candeias do Jamari","Castanheiras","Cerejeiras","Chupinguaia","Colorado do Oeste","Corumbiara","Costa Marques","Cujubim","Espig�o d'Oeste","Governador Jorge Teixeira","Guajar�-Mirim","Itapu� do Oeste","Jaru","Ji-Paran�","Machadinho d'Oeste","Ministro Andreazza","Mirante da Serra","Monte Negro",
				"Nova Brasil�ndia d'Oeste","Nova Mamor�","Nova Uni�o","Novo Horizonte do Oeste","Ouro Preto do Oeste","Parecis","Pimenta Bueno","Pimenteiras do Oeste","Porto Velho","Presidente M�dici","Primavera de Rond�nia","Rio Crespo","Rolim de Moura","Santa Luzia d'Oeste","S�o Felipe d'Oeste","S�o Francisco do Guapor�","S�o Miguel do Guapor�","Seringueiras","Teixeir�polis","Theobroma","Urup�","Vale do Anari","Vale do Para�so","Vilhena"]},
				{"sigla": "RR","nome": "Roraima","cidades": ["Alto Alegre","Amajari","Boa Vista","Bonfim","Cant�","Caracara�","Caroebe","Iracema","Mucaja�","Normandia","Pacaraima","Rorain�polis","S�o Jo�o da Baliza","S�o Luiz","Uiramut�"]},
				{"sigla": "RS","nome": "Rio Grande do Sul","cidades": ["Acegu�","�gua Santa","Agudo","Ajuricaba","Alecrim","Alegrete","Alegria","Almirante Tamandar� do Sul","Alpestre","Alto Alegre","Alto Feliz","Alvorada","Amaral Ferrador","Ametista do Sul","Andr� da Rocha","Anta Gorda","Ant�nio Prado","Arambar�","Araric�","Aratiba","Arroio do Meio","Arroio do Padre","Arroio do Sal",
				"Arroio do Tigre","Arroio dos Ratos","Arroio Grande","Arvorezinha","Augusto Pestana","�urea","Bag�","Balne�rio Pinhal","Bar�o","Bar�o de Cotegipe","Bar�o do Triunfo","Barra do Guarita","Barra do Quara�","Barra do Ribeiro","Barra do Rio Azul","Barra Funda","Barrac�o","Barros Cassal","Benjamin Constan do Sul","Bento Gon�alves","Boa Vista das Miss�es","Boa Vista do Buric�","Boa Vista do Cadeado","Boa Vista do Incra","Boa Vista do Sul","Bom Jesus",
				"Bom Princ�pio","Bom Progresso","Bom Retiro do Sul","Boqueir�o do Le�o","Bossoroca","Bozano","Braga","Brochier","Buti�","Ca�apava do Sul","Cacequi","Cachoeira do Sul","Cachoeirinha","Cacique Doble","Caibat�","Cai�ara","Camaqu�","Camargo","Cambar� do Sul","Campestre da Serra","Campina das Miss�es","Campinas do Sul","Campo Bom","Campo Novo","Campos Borges","Candel�ria","C�ndido God�i","Candiota","Canela",
				"Cangu�u","Canoas","Canudos do Vale","Cap�o Bonito do Sul","Cap�o da Canoa","Cap�o do Cip�","Cap�o do Le�o","Capela de Santana","Capit�o","Capivari do Sul","Cara�","Carazinho","Carlos Barbosa","Carlos Gomes","Casca","Caseiros","Catu�pe","Caxias do Sul","Centen�rio","Cerrito","Cerro Branco","Cerro Grande","Cerro Grande do Sul","Cerro Largo","Chapada","Charqueadas","Charrua","Chiapeta","Chu�","Chuvisca","Cidreira",
				"Cir�aco","Colinas","Colorado","Condor","Constantina","Coqueiro Baixo","Coqueiros do Sul","Coronel Barros","Coronel Bicaco","Coronel Pilar","Cotipor�","Coxilha","Crissiumal","Cristal","Cristal do Sul","Cruz Alta","Cruzaltense","Cruzeiro do Sul","David Canabarro","Derrubadas","Dezesseis de Novembro","Dilermando de Aguiar","Dois Irm�os","Dois Irm�os das Miss�es","Dois Lajeados","Dom Feliciano","Dom Pedrito","Dom Pedro de Alc�ntara","Dona Francisca","Doutor Maur�cio Cardoso","Doutor Ricardo","Eldorado do Sul",
				"Encantado","Encruzilhada do Sul","Engenho Velho","Entre Rios do Sul","Entre-Iju�s","Erebango","Erechim","Ernestina","Erval Grande","Erval Seco","Esmeralda","Esperan�a do Sul","Espumoso","Esta��o","Est�ncia Velha","Esteio","Estrela","Estrela Velha","Eug�nio de Castro","Fagundes Varela","Farroupilha","Faxinal do Soturno","Faxinalzinho","Fazenda Vilanova","Feliz","Flores da Cunha","Floriano Peixoto","Fontoura Xavier","Formigueiro","Forquetinha","Fortaleza dos Valos","Frederico Westphalen",
				"Garibaldi","Garruchos","Gaurama","General C�mara","Gentil","Get�lio Vargas","Giru�","Glorinha","Gramado","Gramado dos Loureiros","Gramado Xavier","Gravata�","Guabiju","Gua�ba","Guapor�","Guarani das Miss�es","Harmonia","Herval","Herveiras","Horizontina","Hulha Negra","Humait�","Ibarama","Ibia��","Ibiraiaras","Ibirapuit�","Ibirub�","Igrejinha",
				"Iju�","Il�polis","Imb�","Imigrante","Independ�ncia","Inhacor�","Ip�","Ipiranga do Sul","Ira�","Itaara","Itacurubi","Itapuca","Itaqui","Itati","Itatiba do Sul","Ivor�","Ivoti","Jaboticaba","Jacuizinho","Jacutinga","Jaguar�o","Jaguari","Jaquirana","Jari","J�ia","J�lio de Castilhos","Lagoa Bonita do Sul","Lagoa dos Tr�s Cantos","Lagoa Vermelha","Lago�o",
				"Lajeado","Lajeado do Bugre","Lavras do Sul","Liberato Salzano","Lindolfo Collor","Linha Nova","Ma�ambara","Machadinho","Mampituba","Manoel Viana","Maquin�","Marat�","Marau","Marcelino Ramos","Mariana Pimentel","Mariano Moro","Marques de Souza","Mata","Mato Castelhano","Mato Leit�o","Mato Queimado","Maximiliano de Almeida","Minas do Le�o","Miragua�","Montauri","Monte Alegre dos Campos","Monte Belo do Sul","Montenegro","Morma�o",
				"Morrinhos do Sul","Morro Redondo","Morro Reuter","Mostardas","Mu�um","Muitos Cap�es","Muliterno","N�o-Me-Toque","Nicolau Vergueiro","Nonoai","Nova Alvorada","Nova Ara��","Nova Bassano","Nova Boa Vista","Nova Br�scia","Nova Candel�ria","Nova Esperan�a do Sul","Nova Hartz","Nova P�dua","Nova Palma","Nova Petr�polis","Nova Prata","Nova Ramada","Nova Roma do Sul","Nova Santa Rita","Novo Barreiro","Novo Cabrais","Novo Hamburgo","Novo Machado","Novo Tiradentes",
				"Novo Xingu","Os�rio","Paim Filho","Palmares do Sul","Palmeira das Miss�es","Palmitinho","Panambi","P�ntano Grande","Para�","Para�so do Sul","Pareci Novo","Parob�","Passa Sete","Passo do Sobrado","Passo Fundo","Paulo Bento","Paverama","Pedras Altas","Pedro Os�rio","Peju�ara","Pelotas","Picada Caf�","Pinhal","Pinhal da Serra","Pinhal Grande","Pinheirinho do Vale","Pinheiro Machado","Pirap�","Piratini","Planalto","Po�o das Antas","Pont�o","Ponte Preta","Port�o","Porto Alegre",
				"Porto Lucena","Porto Mau�","Porto Vera Cruz","Porto Xavier","Pouso Novo","Presidente Lucena","Progresso","Prot�sio Alves","Putinga","Quara�","Quatro Irm�os","Quevedos","Quinze de Novembro","Redentora","Relvado","Restinga Seca","Rio dos �ndios","Rio Grande","Rio Pardo","Riozinho","Roca Sales","Rodeio Bonito","Rolador","Rolante","Ronda Alta","Rondinha","Roque Gonzales","Ros�rio do Sul","Sagrada Fam�lia","Saldanha Marinho","Salto do Jacu�","Salvador das Miss�es",
				"Salvador do Sul","Sananduva","Santa B�rbara do Sul","Santa Cec�lia do Sul","Santa Clara do Sul","Santa Cruz do Sul","Santa Margarida do Sul","Santa Maria","Santa Maria do Herval","Santa Rosa","Santa Tereza","Santa Vit�ria do Palmar","Santana da Boa Vista","Santana do Livramento","Santiago","Santo �ngelo","Santo Ant�nio da Patrulha","Santo Ant�nio das Miss�es","Santo Ant�nio do Palma","Santo Ant�nio do Planalto","Santo Augusto","Santo Cristo","Santo Expedito do Sul","S�o Borja","S�o Domingos do Sul","S�o Francisco de Assis","S�o Francisco de Paula","S�o Gabriel","S�o Jer�nimo","S�o Jo�o da Urtiga","S�o Jo�o do Pol�sine","S�o Jorge",
				"S�o Jos� das Miss�es","S�o Jos� do Herval","S�o Jos� do Hort�ncio","S�o Jos� do Inhacor�","S�o Jos� do Norte","S�o Jos� do Ouro","S�o Jos� do Sul","S�o Jos� dos Ausentes","S�o Leopoldo","S�o Louren�o do Sul","S�o Luiz Gonzaga","S�o Marcos","S�o Martinho","S�o Martinho da Serra","S�o Miguel das Miss�es","S�o Nicolau","S�o Paulo das Miss�es","S�o Pedro da Serra","S�o Pedro das Miss�es","S�o Pedro do Buti�","S�o Pedro do Sul","S�o Sebasti�o do Ca�","S�o Sep�","S�o Valentim","S�o Valentim do Sul","S�o Val�rio do Sul","S�o Vendelino","S�o Vicente do Sul","Sapiranga","Sapucaia do Sul","Sarandi",
				"Seberi","Sede Nova","Segredo","Selbach","Senador Salgado Filho","Sentinela do Sul","Serafina Corr�a","S�rio","Sert�o","Sert�o Santana","Sete de Setembro","Severiano de Almeida","Silveira Martins","Sinimbu","Sobradinho","Soledade","Taba�","Tapejara","Tapera","Tapes","Taquara","Taquari","Taquaru�u do Sul","Tavares","Tenente Portela","Terra de Areia","Teut�nia","Tio Hugo","Tiradentes do Sul","Toropi","Torres","Tramanda�",
				"Travesseiro","Tr�s Arroios","Tr�s Cachoeiras","Tr�s Coroas","Tr�s de Maio","Tr�s Forquilhas","Tr�s Palmeiras","Tr�s Passos","Trindade do Sul","Triunfo","Tucunduva","Tunas","Tupanci do Sul","Tupanciret�","Tupandi","Tuparendi","Turu�u","Ubiretama","Uni�o da Serra","Unistalda","Uruguaiana","Vacaria","Vale do Sol","Vale Real","Vale Verde","Vanini","Ven�ncio Aires","Vera Cruz","Veran�polis","Vespasiano Correa","Viadutos","Viam�o","Vicente Dutra",
				"Victor Graeff","Vila Flores","Vila L�ngaro","Vila Maria","Vila Nova do Sul","Vista Alegre","Vista Alegre do Prata","Vista Ga�cha","Vit�ria das Miss�es","Westf�lia","Xangri-l�"]},
				{"sigla": "SC","nome": "Santa Catarina","cidades": ["Abdon Batista","Abelardo Luz","Agrol�ndia","Agron�mica","�gua Doce","�guas de Chapec�","�guas Frias","�guas Mornas","Alfredo Wagner","Alto Bela Vista","Anchieta","Angelina","Anita Garibaldi","Anit�polis","Ant�nio Carlos","Api�na","Arabut�","Araquari","Ararangu�","Armaz�m","Arroio Trinta","Arvoredo","Ascurra","Atalanta","Aurora","Balne�rio Arroio do Silva","Balne�rio Barra do Sul","Balne�rio Cambori�","Balne�rio Gaivota","Bandeirante",
				"Barra Bonita","Barra Velha","Bela Vista do Toldo","Belmonte","Benedito Novo","Bigua�u","Blumenau","Bocaina do Sul","Bom Jardim da Serra","Bom Jesus","Bom Jesus do Oeste","Bom Retiro","Bombinhas","Botuver�","Bra�o do Norte","Bra�o do Trombudo","Brun�polis","Brusque","Ca�ador","Caibi","Calmon","Cambori�","Campo Alegre","Campo Belo do Sul","Campo Er�","Campos Novos","Canelinha","Canoinhas","Cap�o Alto",
				"Capinzal","Capivari de Baixo","Catanduvas","Caxambu do Sul","Celso Ramos","Cerro Negro","Chapad�o do Lageado","Chapec�","Cocal do Sul","Conc�rdia","Cordilheira Alta","Coronel Freitas","Coronel Martins","Correia Pinto","Corup�","Crici�ma","Cunha Por�","Cunhata�","Curitibanos","Descanso","Dion�sio Cerqueira","Dona Emma","Doutor Pedrinho","Entre Rios","Ermo","Erval Velho","Faxinal dos Guedes","Flor do Sert�o","Florian�polis","Formosa do Sul","Forquilhinha","Fraiburgo",
				"Frei Rog�rio","Galv�o","Garopaba","Garuva","Gaspar","Governador Celso Ramos","Gr�o Par�","Gravatal","Guabiruba","Guaraciaba","Guaramirim","Guaruj� do Sul","Guatamb�","Herval d'Oeste","Ibiam","Ibicar�","Ibirama","I�ara","Ilhota","Imaru�","Imbituba","Imbuia","Indaial","Iomer�","Ipira","Ipor� do Oeste","Ipua�u","Ipumirim","Iraceminha","Irani","Irati","Irine�polis",
				"It�","Itai�polis","Itaja�","Itapema","Itapiranga","Itapo�","Ituporanga","Jabor�","Jacinto Machado","Jaguaruna","Jaragu� do Sul","Jardin�polis","Joa�aba","Joinville","Jos� Boiteux","Jupi�","Lacerd�polis","Lages","Laguna","Lajeado Grande","Laurentino","Lauro Muller","Lebon R�gis","Leoberto Leal","Lind�ia do Sul","Lontras","Luiz Alves","Luzerna","Macieira","Mafra","Major Gercino","Major Vieira",
				"Maracaj�","Maravilha","Marema","Massaranduba","Matos Costa","Meleiro","Mirim Doce","Modelo","Monda�","Monte Carlo","Monte Castelo","Morro da Fuma�a","Morro Grande","Navegantes","Nova Erechim","Nova Itaberaba","Nova Trento","Nova Veneza","Novo Horizonte","Orleans","Otac�lio Costa","Ouro","Ouro Verde","Paial","Painel","Palho�a","Palma Sola","Palmeira","Palmitos","Papanduva","Para�so","Passo de Torres",
				"Passos Maia","Paulo Lopes","Pedras Grandes","Penha","Peritiba","Petrol�ndia","Pi�arras","Pinhalzinho","Pinheiro Preto","Piratuba","Planalto Alegre","Pomerode","Ponte Alta","Ponte Alta do Norte","Ponte Serrada","Porto Belo","Porto Uni�o","Pouso Redondo","Praia Grande","Presidente Castelo Branco","Presidente Get�lio","Presidente Nereu","Princesa","Quilombo","Rancho Queimado","Rio das Antas","Rio do Campo","Rio do Oeste","Rio do Sul","Rio dos Cedros","Rio Fortuna","Rio Negrinho","Rio Rufino","Riqueza",
				"Rodeio","Romel�ndia","Salete","Saltinho","Salto Veloso","Sang�o","Santa Cec�lia","Santa Helena","Santa Rosa de Lima","Santa Rosa do Sul","Santa Terezinha","Santa Terezinha do Progresso","Santiago do Sul","Santo Amaro da Imperatriz","S�o Bento do Sul","S�o Bernardino","S�o Bonif�cio","S�o Carlos","S�o Cristov�o do Sul","S�o Domingos","S�o Francisco do Sul","S�o Jo�o Batista","S�o Jo�o do Itaperi�","S�o Jo�o do Oeste","S�o Jo�o do Sul","S�o Joaquim","S�o Jos�","S�o Jos� do Cedro","S�o Jos� do Cerrito","S�o Louren�o do Oeste",
				"S�o Ludgero","S�o Martinho","S�o Miguel da Boa Vista","S�o Miguel do Oeste","S�o Pedro de Alc�ntara","Saudades","Schroeder","Seara","Serra Alta","Sider�polis","Sombrio","Sul Brasil","Tai�","Tangar�","Tigrinhos","Tijucas","Timb� do Sul","Timb�","Timb� Grande","Tr�s Barras","Treviso","Treze de Maio","Treze T�lias","Trombudo Central","Tubar�o","Tun�polis","Turvo","Uni�o do Oeste",
				"Urubici","Urupema","Urussanga","Varge�o","Vargem","Vargem Bonita","Vidal Ramos","Videira","Vitor Meireles","Witmarsum","Xanxer�","Xavantina","Xaxim","Zort�a"]},
				{"sigla": "SE","nome": "Sergipe","cidades": ["Amparo de S�o Francisco","Aquidab�","Aracaju","Arau�","Areia Branca","Barra dos Coqueiros","Boquim","Brejo Grande","Campo do Brito","Canhoba","Canind� de S�o Francisco","Capela","Carira","Carm�polis","Cedro de S�o Jo�o","Cristin�polis","Cumbe","Divina Pastora","Est�ncia","Feira Nova","Frei Paulo","Gararu","General Maynard","Gracho Cardoso","Ilha das Flores","Indiaroba","Itabaiana",
				"Itabaianinha","Itabi","Itaporanga d'Ajuda","Japaratuba","Japoat�","Lagarto","Laranjeiras","Macambira","Malhada dos Bois","Malhador","Maruim","Moita Bonita","Monte Alegre de Sergipe","Muribeca","Ne�polis","Nossa Senhora Aparecida","Nossa Senhora da Gl�ria","Nossa Senhora das Dores","Nossa Senhora de Lourdes","Nossa Senhora do Socorro","Pacatuba","Pedra Mole","Pedrinhas","Pinh�o","Pirambu","Po�o Redondo","Po�o Verde","Porto da Folha",
				"Propri�","Riach�o do Dantas","Riachuelo","Ribeir�polis","Ros�rio do Catete","Salgado","Santa Luzia do Itanhy","Santa Rosa de Lima","Santana do S�o Francisco","Santo Amaro das Brotas","S�o Crist�v�o","S�o Domingos","S�o Francisco","S�o Miguel do Aleixo","Sim�o Dias","Siriri","Telha","Tobias Barreto","Tomar do Geru","Umba�ba"]},
				{"sigla": "SP","nome": "S�o Paulo","cidades": ["Adamantina","Adolfo","Agua�","�guas da Prata","�guas de Lind�ia","�guas de Santa B�rbara","�guas de S�o Pedro","Agudos","Alambari","Alfredo Marcondes","Altair","Altin�polis","Alto Alegre","Alum�nio","�lvares Florence","�lvares Machado","�lvaro de Carvalho","Alvinl�ndia","Americana","Am�rico Brasiliense","Am�rico de Campos","Amparo","Anal�ndia","Andradina","Angatuba","Anhembi","Anhumas","Aparecida","Aparecida d'Oeste","Apia�","Ara�ariguama","Ara�atuba","Ara�oiaba da Serra",
				"Aramina","Arandu","Arape�","Araraquara","Araras","Arco-�ris","Arealva","Areias","Arei�polis","Ariranha","Artur Nogueira","Aruj�","Asp�sia","Assis","Atibaia","Auriflama","Ava�","Avanhandava","Avar�","Bady Bassitt","Balbinos","B�lsamo","Bananal","Bar�o de Antonina","Barbosa","Bariri","Barra Bonita","Barra do Chap�u","Barra do Turvo","Barretos","Barrinha","Barueri",
				"Bastos","Batatais","Bauru","Bebedouro","Bento de Abreu","Bernardino de Campos","Bertioga","Bilac","Birigui","Biritiba-Mirim","Boa Esperan�a do Sul","Bocaina","Bofete","Boituva","Bom Jesus dos Perd�es","Bom Sucesso de Itarar�","Bor�","Borac�ia","Borborema","Borebi","Botucatu","Bragan�a Paulista","Bra�na",
				"Brejo Alegre","Brodowski","Brotas","Buri","Buritama","Buritizal","Cabr�lia Paulista","Cabre�va","Ca�apava","Cachoeira Paulista","Caconde","Cafel�ndia","Caiabu","Caieiras","Caiu�","Cajamar","Cajati","Cajobi","Cajuru","Campina do Monte Alegre","Campinas","Campo Limpo Paulista","Campos do Jord�o","Campos Novos Paulista","Canan�ia","Canas","C�ndido Mota","C�ndido Rodrigues","Canitar","Cap�o Bonito","Capela do Alto","Capivari","Caraguatatuba","Carapicu�ba","Cardoso",
				"Casa Branca","C�ssia dos Coqueiros","Castilho","Catanduva","Catigu�","Cedral","Cerqueira C�sar","Cerquilho","Ces�rio Lange","Charqueada","Chavantes","Clementina","Colina","Col�mbia","Conchal","Conchas","Cordeir�polis","Coroados","Coronel Macedo","Corumbata�","Cosm�polis","Cosmorama","Cotia","Cravinhos","Cristais Paulista","Cruz�lia","Cruzeiro","Cubat�o","Cunha","Descalvado","Diadema","Dirce Reis","Divinol�ndia","Dobrada","Dois C�rregos","Dolcin�polis",
				"Dourado","Dracena","Duartina","Dumont","Echapor�","Eldorado","Elias Fausto","Elisi�rio","Emba�ba","Embu","Embu-Gua�u","Emilian�polis","Engenheiro Coelho","Esp�rito Santo do Pinhal","Esp�rito Santo do Turvo","Estiva Gerbi","Estrela d'Oeste","Estrela do Norte","Euclides da Cunha Paulista","Fartura","Fernando Prestes","Fernand�polis","Fern�o",
				"Ferraz de Vasconcelos","Flora Rica","Floreal","Flor�nia","Fl�rida Paulista","Franca","Francisco Morato","Franco da Rocha","Gabriel Monteiro","G�lia","Gar�a","Gast�o Vidigal","Gavi�o Peixoto","General Salgado","Getulina","Glic�rio","Guai�ara","Guaimb�","Gua�ra","Guapia�u","Guapiara","Guar�","Guara�a�","Guaraci","Guarani d'Oeste","Guarant�","Guararapes","Guararema","Guaratinguet�","Guare�","Guariba","Guaruj�",
				"Guarulhos","Guatapar�","Guzol�ndia","Hercul�ndia","Holambra","Hortol�ndia","Iacanga","Iacri","Iaras","Ibat�","Ibir�","Ibirarema","Ibitinga","Ibi�na","Ic�m","Iep�","Igara�u do Tiet�","Igarapava","Igarat�","Iguape","Ilha Comprida","Ilha Solteira","Ilhabela","Indaiatuba","Indiana","Indiapor�","In�bia Paulista","Ipau�u","Iper�","Ipe�na","Ipigu�","Iporanga",
				"Ipu�","Iracem�polis","Irapu�","Irapuru","Itaber�","Ita�","Itajobi","Itaju","Itanha�m","Ita�ca","Itapecerica da Serra","Itapetininga","Itapeva","Itapevi","Itapira","Itapirapu� Paulista","It�polis","Itaporanga","Itapu�","Itapura","Itaquaquecetuba","Itarar�","Itariri","Itatiba","Itatinga","Itirapina","Itirapu�","Itobi","Itu","Itupeva","Ituverava","Jaborandi","Jaboticabal","Jacare�","Jaci",
				"Jacupiranga","Jaguari�na","Jales","Jambeiro","Jandira","Jardin�polis","Jarinu","Ja�","Jeriquara","Joan�polis","Jo�o Ramalho","Jos� Bonif�cio","J�lio Mesquita","Jumirim","Jundia�","Junqueir�polis","Juqui�","Juquitiba","Lagoinha","Laranjal Paulista","Lav�nia","Lavrinhas","Leme","Len��is Paulista","Limeira","Lind�ia","Lins","Lorena","Lourdes","Louveira","Luc�lia","Lucian�polis","Lu�s Ant�nio","Luizi�nia",
				"Lup�rcio","Lut�cia","Macatuba","Macaubal","Maced�nia","Magda","Mairinque","Mairipor�","Manduri","Marab� Paulista","Maraca�","Marapoama","Mari�polis","Mar�lia","Marin�polis","Martin�polis","Mat�o","Mau�","Mendon�a","Meridiano","Mes�polis","Miguel�polis",
				"Mineiros do Tiet�","Mira Estrela","Miracatu","Mirand�polis","Mirante do Paranapanema","Mirassol","Mirassol�ndia","Mococa","Mogi das Cruzes","Mogi-Gua�u","Mogi-Mirim","Mombuca","Mon��es","Mongagu�","Monte Alegre do Sul","Monte Alto","Monte Apraz�vel","Monte Azul Paulista","Monte Castelo","Monte Mor","Monteiro Lobato","Morro Agudo","Morungaba","Motuca","Murutinga do Sul","Nantes","Narandiba","Natividade da Serra","Nazar� Paulista","Neves Paulista","Nhandeara","Nipo�",
				"Nova Alian�a","Nova Campina","Nova Cana� Paulista","Nova Castilho","Nova Europa","Nova Granada","Nova Guataporanga","Nova Independ�ncia","Nova Luzit�nia","Nova Odessa","Novais","Novo Horizonte","Nuporanga","Ocau�u","�leo","Ol�mpia","Onda Verde","Oriente","Orindi�va","Orl�ndia","Osasco","Oscar Bressane","Osvaldo Cruz","Ourinhos","Ouro Verde","Ouroeste","Pacaembu",
				"Palestina","Palmares Paulista","Palmeira d'Oeste","Palmital","Panorama","Paragua�u Paulista","Paraibuna","Para�so","Paranapanema","Paranapu�","Parapu�","Pardinho","Pariquera-A�u","Parisi","Patroc�nio Paulista","Paulic�ia","Paul�nia","Paulist�nia","Paulo de Faria","Pederneiras","Pedra Bela","Pedran�polis","Pedregulho","Pedreira","Pedrinhas Paulista","Pedro de Toledo","Pen�polis","Pereira Barreto","Pereiras","Peru�be","Piacatu","Piedade","Pilar do Sul",
				"Pindamonhangaba","Pindorama","Pinhalzinho","Piquerobi","Piquete","Piracaia","Piracicaba","Piraju","Piraju�","Pirangi","Pirapora do Bom Jesus","Pirapozinho","Pirassununga","Piratininga","Pitangueiras","Planalto","Platina","Po�","Poloni","Pomp�ia","Ponga�","Pontal","Pontalinda","Pontes Gestal","Populina","Porangaba","Porto Feliz","Porto Ferreira","Potim","Potirendaba","Pracinha","Prad�polis","Praia Grande",
				"Prat�nia","Presidente Alves","Presidente Bernardes","Presidente Epit�cio","Presidente Prudente","Presidente Venceslau","Promiss�o","Quadra","Quat�","Queiroz","Queluz","Quintana","Rafard","Rancharia","Reden��o da Serra","Regente Feij�","Regin�polis","Registro","Restinga","Ribeira","Ribeir�o Bonito","Ribeir�o Branco","Ribeir�o Corrente","Ribeir�o do Sul","Ribeir�o dos �ndios","Ribeir�o Grande","Ribeir�o Pires","Ribeir�o Preto","Rifaina",
				"Rinc�o","Rin�polis","Rio Claro","Rio das Pedras","Rio Grande da Serra","Riol�ndia","Riversul","Rosana","Roseira","Rubi�cea","Rubin�ia","Sabino","Sagres","Sales","Sales Oliveira","Sales�polis","Salmour�o","Saltinho","Salto","Salto de Pirapora","Salto Grande","Sandovalina","Santa Ad�lia","Santa Albertina","Santa B�rbara d'Oeste","Santa Branca","Santa Clara d'Oeste","Santa Cruz da Concei��o","Santa Cruz da Esperan�a","Santa Cruz das Palmeiras","Santa Cruz do Rio Pardo","Santa Ernestina","Santa F� do Sul","Santa Gertrudes","Santa Isabel","Santa L�cia","Santa Maria da Serra",
				"Santa Mercedes","Santa Rita d'Oeste","Santa Rita do Passa Quatro","Santa Rosa de Viterbo","Santa Salete","Santana da Ponte Pensa","Santana de Parna�ba","Santo Anast�cio","Santo Andr�","Santo Ant�nio da Alegria","Santo Ant�nio de Posse","Santo Ant�nio do Aracangu�","Santo Ant�nio do Jardim","Santo Ant�nio do Pinhal","Santo Expedito","Sant�polis do Aguape�","Santos","S�o Bento do Sapuca�","S�o Bernardo do Campo",
				"S�o Caetano do Sul","S�o Carlos","S�o Francisco","S�o Jo�o da Boa Vista","S�o Jo�o das Duas Pontes","S�o Jo�o de Iracema","S�o Jo�o do Pau d'Alho","S�o Joaquim da Barra","S�o Jos� da Bela Vista","S�o Jos� do Barreiro","S�o Jos� do Rio Pardo","S�o Jos� do Rio Preto","S�o Jos� dos Campos","S�o Louren�o da Serra","S�o Lu�s do Paraitinga","S�o Manuel","S�o Miguel Arcanjo","S�o Paulo","S�o Pedro","S�o Pedro do Turvo","S�o Roque","S�o Sebasti�o","S�o Sebasti�o da Grama","S�o Sim�o",
				"S�o Vicente","Sarapu�","Sarutai�","Sebastian�polis do Sul","Serra Azul","Serra Negra","Serrana","Sert�ozinho","Sete Barras","Sever�nia","Silveiras","Socorro","Sorocaba","Sud Mennucci","Sumar�","Suzan�polis","Suzano","Tabapu�","Tabatinga","Tabo�o da Serra","Taciba","Tagua�","Taia�u",
				"Tai�va","Tamba�","Tanabi","Tapira�","Tapiratiba","Taquaral","Taquaritinga","Taquarituba","Taquariva�","Tarabai","Tarum�","Tatu�","Taubat�","Tejup�","Teodoro Sampaio","Terra Roxa","Tiet�","Timburi","Torre de Pedra","Torrinha","Trabiju","Trememb�","Tr�s Fronteiras",
				"Tuiuti","Tup�","Tupi Paulista","Turi�ba","Turmalina","Ubarana","Ubatuba","Ubirajara","Uchoa","Uni�o Paulista","Ur�nia","Uru","Urup�s","Valentim Gentil","Valinhos","Valpara�so","Vargem","Vargem Grande do Sul","Vargem Grande Paulista","V�rzea Paulista","Vera Cruz","Vinhedo","Viradouro",
				"Vista Alegre do Alto","Vit�ria Brasil","Votorantim","Votuporanga","Zacarias"]},
				{"sigla": "TO","nome": "Tocantins","cidades": ["Abreul�ndia","Aguiarn�polis","Alian�a do Tocantins","Almas","Alvorada","Anan�s","Angico","Aparecida do Rio Negro","Aragominas","Araguacema","Aragua�u","Aragua�na","Araguan�","Araguatins","Arapoema","Arraias","Augustin�polis","Aurora do Tocantins","Axix� do Tocantins","Baba�ul�ndia",
				"Bandeirantes do Tocantins","Barra do Ouro","Barrol�ndia","Bernardo Say�o","Bom Jesus do Tocantins","Brasil�ndia do Tocantins","Brejinho de Nazar�","Buriti do Tocantins","Cachoeirinha","Campos Lindos","Cariri do Tocantins","Carmol�ndia","Carrasco Bonito","Caseara","Centen�rio","Chapada da Natividade","Chapada de Areia","Colinas do Tocantins","Colm�ia","Combinado","Concei��o do Tocantins","Couto de Magalh�es",
				"Cristal�ndia","Crix�s do Tocantins","Darcin�polis","Dian�polis","Divin�polis do Tocantins","Dois Irm�os do Tocantins","Duer�","Esperantina","F�tima","Figueir�polis","Filad�lfia","Formoso do Araguaia","Fortaleza do Taboc�o","Goianorte","Goiatins","Guara�","Gurupi","Ipueiras","Itacaj�","Itaguatins","Itapiratins","Itapor� do Tocantins","Ja� do Tocantins","Juarina","Lagoa da Confus�o","Lagoa do Tocantins",
				"Lajeado","Lavandeira","Lizarda","Luzin�polis","Marian�polis do Tocantins","Mateiros","Mauril�ndia do Tocantins","Miracema do Tocantins","Miranorte","Monte do Carmo","Monte Santo do Tocantins","Muricil�ndia","Natividade","Nazar�","Nova Olinda","Nova Rosal�ndia","Novo Acordo","Novo Alegre","Novo Jardim","Oliveira de F�tima","Palmas","Palmeirante","Palmeiras do Tocantins","Palmeir�polis","Para�so do Tocantins","Paran�","Pau d'Arco","Pedro Afonso","Peixe","Pequizeiro","Pindorama do Tocantins","Piraqu�",
				"Pium","Ponte Alta do Bom Jesus","Ponte Alta do Tocantins","Porto Alegre do Tocantins","Porto Nacional","Praia Norte","Presidente Kennedy","Pugmil","Recursol�ndia","Riachinho","Rio da Concei��o","Rio dos Bois","Rio Sono","Sampaio","Sandol�ndia","Santa F� do Araguaia","Santa Maria do Tocantins","Santa Rita do Tocantins","Santa Rosa do Tocantins","Santa Tereza do Tocantins","Santa Terezinha Tocantins","S�o Bento do Tocantins","S�o F�lix do Tocantins","S�o Miguel do Tocantins","S�o Salvador do Tocantins","S�o Sebasti�o do Tocantins","S�o Val�rio da Natividade",
				"Silvan�polis","S�tio Novo do Tocantins","Sucupira","Taguatinga","Taipas do Tocantins","Talism�","Tocant�nia","Tocantin�polis","Tupirama","Tupiratins","Wanderl�ndia","Xambio�"]}
			];
			var items = [];
			var options = '<option value=""></option>';	

			$.each(data, function (key, val) {
				options += '<option value="' + val.sigla + '">' + val.nome + '</option>';
			});					
			$("#consumidor_estado").html(options);				
			
			$("#consumidor_estado").change(function () {				
			
				var options_cidades = '<option value=""></option>';
				var str = "";					
				
				$("#consumidor_estado option:selected").each(function () {
					str += $(this).text();
				});
				
				$.each(data, function (key, val) {
					if(val.nome == str) {							
						$.each(val.cidades, function (key_city, val_city) {
							options_cidades += '<option value="' + val_city + '">' + val_city + '</option>';
						});							
					}
				});
				$("#consumidor_cidade").html(options_cidades);
			}).change();		
		});
	});
</script>	
<?php endif;?>

<?php
if ($login_fabrica == 52) { ?>
<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt87" ID="chk_opt87" value="1"> Pa�s do Consumidor</TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<select name="consumidor_pais" id='consumidor_pais' style='width:131px; font-size:11px' class='frm'>
			<option value=""></option>
			<?php 
				$aux_sql = "SELECT pais, nome FROM tbl_pais";
				$aux_res = pg_query($con, $aux_sql);
				$aux_row = pg_num_rows($aux_res);

				for ($wz = 0; $wz < $aux_row; $wz++) { 
					$aux_pais = pg_fetch_result($aux_res, $wz, 'pais');
					$aux_nome = pg_fetch_result($aux_res, $wz, 'nome');

					?> <option value="<?=$aux_pais;?>"><?=$aux_nome;?></option> <?
				}
			?>
		</select>
	</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<? }

if($login_fabrica == 157){
		if(strlen($_POST['chk_opt_codigo_postagem'])>0  ){
		$chk_opt_codigo_postagem = $_POST['chk_opt_codigo_postagem'];
	}
?>
<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt_postagem" ID="chk_opt_postagem" value="1"> Codigo de postagem</TD>
	<TD class="table_line" style="text-align: left;" colspan="2"><INPUT TYPE="text" NAME="chk_opt_codigo_postagem" ID="chk_opt_codigo_postagem" size="30" class='frm'></TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php
}
if ($moduloProvidencia || $classificacaoHD || in_array($login_fabrica, array(52))) {
?>
<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt27" ID="chk_opt27" value="1"> <?php echo ($login_fabrica == 189) ? "Registro Ref. a" : "Classifica��o do Atendimento";?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<select name="hd_classificacao" id='hd_classificacao' style='width:131px; font-size:11px' class='frm'>
			<option value=""></option>
			<?php
				$hd_ativo = 'AND ativo IS TRUE';
				if ($login_fabrica == 30) {
					$hd_ativo = "";
				}
				$sql = "SELECT hd_classificacao,descricao FROM tbl_hd_classificacao WHERE fabrica = {$login_fabrica} {$hd_ativo} ORDER BY descricao";
				$res = pg_query($con,$sql);

				if(pg_num_rows($res) > 0){

					for ($i=0; $i < pg_num_rows($res); $i++) {
						$hd_classificacao = pg_fetch_result($res, $i, 'hd_classificacao');
						$classificacao = pg_fetch_result($res, $i, 'descricao');

						echo "<option value='{$hd_classificacao}'>{$classificacao}</option>";
					}

				}
			?>
		</select>
	</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php
if (!in_array($login_fabrica, array(174))) {
?>
<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt28" ID="chk_opt28" value="1"><?php echo ($login_fabrica == 189) ? "A��o" : "Provid�ncia";?></TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<select name="providencia" id='providencia' style='width:331px; font-size:11px' class='frm'>
			<option value=""></option>
			<?php
				$sql = "SELECT hd_motivo_ligacao, descricao
					FROM tbl_hd_motivo_ligacao WHERE fabrica = {$login_fabrica} ORDER BY descricao";
				$resProvidencia = pg_query($con,$sql);

				if(pg_num_rows($resProvidencia) > 0){
					while($objeto_providencia = pg_fetch_object($resProvidencia)){
						if($objeto_providencia->hd_motivo_ligacao == $providencia){
							$selected = "selected='selected'";
						}else{
							$selected = "";
						}
						?>
						<option value="<?=$objeto_providencia->hd_motivo_ligacao?>" <?=$selected?>><?=$objeto_providencia->descricao?></option>
						<?php
					}
				}
			?>
		</select>
	</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?php
}
	if(in_array($login_fabrica, array(169,170)) || $usaOrigemCadastro){
?>
	<TR>
		<TD class="table_line" style="text-align: left;">&nbsp;</TD>
		<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt29" ID="chk_opt29" value="1"> <?php echo ($login_fabrica == 189) ? "Depto. Gerador da RRC" : "Origem";?></TD>
		<TD class="table_line" style="text-align: left;" colspan="2">
			<select name="origem" id='origem' style='width:331px; font-size:11px' class='frm'>
				<option value=""></option>
				<?php
					$sql = "SELECT hd_chamado_origem, descricao
								FROM tbl_hd_chamado_origem WHERE fabrica = {$login_fabrica} ORDER BY descricao";
					$resOrigem = pg_query($con,$sql);

					if(pg_num_rows($resOrigem) > 0){
						while($objeto_origem_callcenter = pg_fetch_object($resOrigem)){
							if($objeto_origem_callcenter->descricao == $origem_callcenter){
								$selected = "selected='selected'";
							}else{
								$selected = "";
							}
							?>
							<option value="<?=$objeto_origem_callcenter->descricao?>" <?=$selected?>><?=$objeto_origem_callcenter->descricao?></option>
							<?php
						}
					}
				?>
			</select>
		</TD>
		<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	</TR>

<?php
	} 

	if (in_array($login_fabrica, [186])) {
	?>
		<TR>
			<TD class="table_line" style="text-align: left;">&nbsp;</TD>
			<TD class="table_line" ><INPUT TYPE="checkbox" NAME="opt_email" ID="opt_email" value="1">E-mail</TD>
			<TD class="table_line" style="text-align: left;" colspan="2">
				<INPUT TYPE="text" NAME="email_callcenter" ID="email_callcenter" size="17" class='frm'>
			</TD>
			<TD class="table_line" style="text-align: center;">&nbsp;</TD>
		</TR>
	<?php
	}

	if (in_array($login_fabrica, [169,170])) { ?>
		<TR>
			<TD class="table_line" style="text-align: left;">&nbsp;</TD>
			<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt32" ID="chk_opt32" value="1">Provid�ncia N�vel 3</TD>
			<TD class="table_line" style="text-align: left;" colspan="2">
				<select name="providencia_nivel_3" id='providencia_nivel_3' style='width:331px; font-size:11px' class='frm'>
					<option value=""></option>
					<?php
						$sqlProvidencia3 = "SELECT hd_providencia, descricao
											FROM tbl_hd_providencia WHERE fabrica = {$login_fabrica}
											AND ativo IS TRUE
											ORDER BY descricao DESC";
						$resProvidencia3 = pg_query($con,$sqlProvidencia3);

						if(pg_num_rows($resProvidencia3) > 0){
							while($dadosProv = pg_fetch_object($resProvidencia3)){
								
								$selected = ($dadosProv->hd_providencia == $_POST['providencia_nivel_3']) ? "selected" : "";

								?>
								<option value="<?=$dadosProv->hd_providencia?>" <?=$selected?>>
									<?= $dadosProv->descricao ?>
								</option>
								<?php
							}
						}
					?>
				</select>
			</TD>
			<TD class="table_line" style="text-align: center;">&nbsp;</TD>
		</TR>
		<TR>
			<TD class="table_line" style="text-align: left;">&nbsp;</TD>
			<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt33" ID="chk_opt33" value="1">Motivo Contato</TD>
			<TD class="table_line" style="text-align: left;" colspan="2">
				<select name="motivo_contato" id='motivo_contato' style='width:331px; font-size:11px' class='frm'>
					<option value=""></option>
					<?php
						$sqlMotivoContato = "SELECT motivo_contato, descricao
											FROM tbl_motivo_contato WHERE fabrica = {$login_fabrica}
											AND ativo IS TRUE
											ORDER BY descricao DESC";
						$resMotivoContato = pg_query($con,$sqlMotivoContato);

						if(pg_num_rows($resMotivoContato) > 0){
							while($dadosContato = pg_fetch_object($resMotivoContato)){
								
								$selected = ($dadosContato->motivo_contato == $_POST['motivo_contato']) ? "selected" : "";

								?>
								<option value="<?=$dadosContato->motivo_contato?>" <?=$selected?>>
									<?= $dadosContato->descricao ?>
								</option>
								<?php
							}
						}
					?>
				</select>
			</TD>
			<TD class="table_line" style="text-align: center;">&nbsp;</TD>
		</TR>
<?php
	}

	if($login_fabrica == 162){//HD-3352176
?>
	<TR>
		<TD class="table_line" style="text-align: left;">&nbsp;</TD>
		<TD class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt162" ID="chk_opt162" value="1">Motivos da Transfer�ncia</TD>
		<TD class="table_line" style="text-align: left;" colspan="2">
			<select  class="input frm" name="motivo_transferencia" id="motivo_transferencia">
                <option value=""></option>
                <?php
                    $sql = "SELECT hd_situacao,descricao,ativo
                                FROM tbl_hd_situacao
                                WHERE fabrica = $login_fabrica
                                ORDER BY descricao";
                    $res = pg_query($con,$sql);

                    foreach (pg_fetch_all($res) as $key) {
                        $selected_motivo_transferencia = ( isset($motivo_transferencia) and ($motivo_transferencia == $key['hd_situacao']) ) ? "SELECTED" : '' ;
                ?>
                    <option value="<?php echo $key['hd_situacao']?>" <?php echo $selected_motivo_transferencia ?> >
                        <?php echo $key['descricao']?>
                    </option>
                <?php
                }
                ?>
            </select>
		</TD>
		<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	</TR>

<?php
	}
}

if (in_array($login_fabrica, $aExibirFiltroAtendente)){
	# HD 58801
	echo "<tr>";
	echo "<td class='table_line' style='text-align: left;'>&nbsp;</td>";
	echo "<td class='table_line'><input type='checkbox' name='por_atendente' value='1'> Atendente</td>";
	echo "<td class='table_line' colspan='2'>";
	echo "<select name='atendente' class='input frm' style='font-size:12px;width:131px;' class='frm' >";
	echo "<option value=''></option>";
	$sqlAdm = "SELECT admin, login, nome_completo
			FROM tbl_admin
			WHERE fabrica = $login_fabrica
			AND ativo is true
			AND (privilegios like '%call_center%' or privilegios like '*')
			ORDER BY nome_completo, login";
	$resAdm = pg_exec($con,$sqlAdm);
	if ( is_resource($resAdm) && pg_numrows($resAdm) > 0){
		$nome_completo_limit = 20;
		while ( $row_atendente = pg_fetch_assoc($resAdm) ) {
			$nome_completo = $nome = ( empty($row_atendente['nome_completo']) ) ? $row_atendente['login'] : $row_atendente['nome_completo'];
			if (strlen($nome) >= $nome_completo_limit) {
				$nome = substr($nome, 0, $nome_completo_limit-3).'...';
			}
			?>
			<option value="<?php echo $row_atendente['admin']; ?>"><?php echo $nome; ?></option>
			<?php
		}
	}
	echo "</select>";
	echo "</td>";
	echo "<TD class='table_line' style='text-align: center;'>&nbsp;</TD>";
	echo "</tr>";
}

if (in_array($login_fabrica,array(30,81,162))) { //hd_chamado=2902269

    $origemOptions = array(
        "Telefone"  => "Telefone",
        "Email"     => "Email"
    );
    if ($login_fabrica == 30) {
        $origemOptions['Consumidor.gov']    = 'Consumidor.gov' ;
        $origemOptions['Facebook']          = 'Facebook';
        $origemOptions['fale']     			= 'Site Esmaltec';
        $origemOptions['Instagram']         = 'Instagram';
        $origemOptions['Novos Canais']      = 'Novos Canais';
        $origemOptions['Demonstradoras']    = 'Demonstradoras';
        $origemOptions['Sac M�dia']         = 'Sac M�dia';
        $origemOptions['Twitter']           = 'Twitter';
        $origemOptions['P. Autorizado']     = 'P. Autorizado';
    }
    if ($login_fabrica == 162) {
        $origemOptions['Chat']      = "Chat";
        $origemOptions['CIP']       = "CIP";
        $origemOptions['Juizado']   = "Juizado";
        $origemOptions['Procon']    = "Procon";
        $origemOptions['Midias Sociais'] = "Midias Sociais"; //HD-3352176
    }

    if ($login_fabrica == 81) {
    	$sql = "SELECT descricao
				FROM tbl_hd_chamado_origem 
				WHERE fabrica = $login_fabrica";
		$resOrigem = pg_query($con,$sql);
		$origemOptions = [];

		while ($fetch = pg_fetch_assoc($resOrigem)) {
			$origemOptions[$fetch['descricao']] = $fetch['descricao'];
		}
    }
?>
    <tr>
        <td class='table_line' style='text-align: left;'>&nbsp;</td>
        <td class='table_line'><input type='checkbox' name='por_origem' value='1'> Origem</td>
        <td class='table_line' colspan='3'>
            <select name='origem' class='input frm' style='font-size:12px;width:131px;' class='frm' >
                <option value=''></option>
<?php
    foreach ($origemOptions as $key => $value) {
?>
                <option value='<?=$key?>'><?=$value?></option>
<?php
    }
?>
            </select>
        </td>
    </tr>
<?php

}

if ($login_fabrica == 85 && $areaAdminCliente != true) {
?>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line"><INPUT TYPE="checkbox" NAME="chk_cnpj_revenda" ID="chk_cnpj_revenda" value="1"> CNPJ da Revenda</TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<INPUT TYPE="text" NAME="cnpj_revenda" ID="cnpj_revenda" size="17" onkeypress='return SomenteNumero(event)' class='frm'>
	</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
<?php
}

if ($login_fabrica == 52){
 ?>
<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line"><INPUT TYPE="checkbox" NAME="chk_posto_estado" ID="chk_posto_estado" value="1"> Estado do Posto</TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<select name="posto_estado[]" multiple='multiple' id='posto_estado' style='width:131px; font-size:11px' class='frm'>
			<? $ArrayEstados = array('','AC','AL','AM','AP',
										'BA','CE','DF','ES',
										'GO','MA','MG','MS',
										'MT','PA','PB','PE',
										'PI','PR','RJ','RN',
										'RO','RR','RS','SC',
										'SE','SP','TO'
									);
			for ($i=0; $i<=27; $i++){
				echo"<option value='".$ArrayEstados[$i]."'";
				if ($posto_estado == $ArrayEstados[$i]) echo " selected";
				echo ">".$ArrayEstados[$i]."</option>\n";
			}?>
		</select>
	</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<TR>
	<TD class="table_line" style="text-align: left;">&nbsp;</TD>
	<TD class="table_line"><INPUT TYPE="checkbox" NAME="chk_familia" ID="chk_familia" value="1"> Fam�lia</TD>
	<TD class="table_line" style="text-align: left;" colspan="2">
		<select name="familia[]" id='familia' multiple='multiple' style='width:131px; font-size:11px' class='frm'>
			<?
			$sql = "SELECT familia,descricao
				FROM tbl_familia
				WHERE fabrica = $login_fabrica
				AND ativo
				ORDER BY descricao";
			$res = pg_query($con,$sql);
			for ($i=0; $i<pg_num_rows($res); $i++){
				echo"<option value='".pg_fetch_result($res,$i,0)."'";
				echo ">".pg_fetch_result($res,$i,1)."</option>\n";
			}?>
		</select>
	</TD>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</TR>
<?
}

if ($login_fabrica == 52 ) {?>
<TD class="table_line" style="text-align: left;">&nbsp;</TD>
<TD class="table_line"><INPUT TYPE="checkbox" NAME="chk_marca" ID="chk_marca" value="1"> Marca</TD>
<TD class="table_line" style="text-align: left;" colspan="2">
	<select name='marca' class='input frm' style='font-size:12px;width:131px;' class='frm' >
	<option value=''></option>
<?
	$sql_fricon = "SELECT marca, nome
					FROM tbl_marca
					WHERE tbl_marca.fabrica = $login_fabrica
					ORDER BY tbl_marca.nome ";

				$res_fricon = pg_query($con, $sql_fricon);
				for ($i=0; $i<pg_num_rows($res_fricon); $i++){
				echo"<option value='".pg_fetch_result($res_fricon,$i,0)."'";
				echo ">".pg_fetch_result($res_fricon,$i,1)."</option>\n";
			}?>
		</select>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
</td>
<?
}

if($login_fabrica == 24){
?>
	<tr>
		<td class='table_line' style='text-align: left;'>&nbsp;</td>
		<td class='table_line'><input type='checkbox' name='por_intervensor' value='1'> Interventor</td>
		<td class='table_line' colspan='2'>
			<select name='intervensor' class='input frm' style='font-size:12px;width:131px;' class='frm' >
				<option value=''></option>
				<?php
					$sqlAdm = "SELECT admin, login, nome_completo
							FROM tbl_admin
							WHERE fabrica = $login_fabrica
							AND ativo is true
							AND intervensor IS TRUE
							AND (privilegios like '%call_center%' or privilegios like '*')
							ORDER BY nome_completo, login";
					$resAdm = pg_exec($con,$sqlAdm);

					if ( is_resource($resAdm) && pg_numrows($resAdm) > 0){
						$nome_completo_limit = 20;
						while ( $row_atendente = pg_fetch_assoc($resAdm) ) {
							$nome_completo = $nome = ( empty($row_atendente['nome_completo']) ) ? $row_atendente['login'] : $row_atendente['nome_completo'];
							if (strlen($nome) >= $nome_completo_limit) {
								$nome = substr($nome, 0, $nome_completo_limit-3).'...';
							}
							?>
							<option value="<?php echo $row_atendente['admin']; ?>"><?php echo $nome; ?></option>
							<?php
						}
					}
				?>
			</select>
		</td>
		<td class='table_line' style='text-align: center;'>&nbsp;</td>
	</tr>
<?
}
?>

<?php if ($login_fabrica == 5) { // HD 59746 (augusto) ?>
<tr>
	<td class="table_line"> &nbsp; </td>
	<td class="table_line">
		<input type="checkbox" id="providencia_chk" name="providencia_chk" value="1" />
		<label for="providencia_chk">Provid�ncia</label>
	</td>
	<td class="table_line" colspan="2">
		<?php
			$sql = "SELECT hd_situacao, descricao
					FROM tbl_hd_situacao
					WHERE fabrica = %s
					ORDER BY descricao";
			$sql       = sprintf($sql,pg_escape_string($login_fabrica));
			$res       = pg_exec($con,$sql);
			$rows      = (int) pg_numrows($res);
			$situacoes = array();
			if ( $rows > 0 ) {
				while ($row = pg_fetch_assoc($res)) {
					$situacoes[$row['hd_situacao']] = $row['descricao'];
				}
			}
		?>
		<select name="providencia" id="providencia" style="width: 140px;">
			<option value=""></option>
			<?php foreach($situacoes as $id=>$descr): ?>
				<option value="<?php echo $id; ?>"><?php echo utf8_decode($descr); ?></option>
			<?php endforeach; ?>
		</select>
	</td>
	<td class="table_line"> &nbsp; </td>
</tr>
<tr>
	<td class="table_line"> &nbsp; </td>
	<td class="table_line">
		<input type="checkbox" id="providencia_data_chk" name="providencia_data_chk" value="1" />
		<label for="providencia_data_chk">Data da Provid�ncia</label>
	</td>
	<td class="table_line" colspan="2">
		<input type="text" name="providencia_data" id="providencia_data" class="mask_date" size="10" maxlength="10" />
	</td>
	<td class="table_line"> &nbsp; </td>
</tr>
<tr>
	<td class="table_line"> &nbsp; </td>
	<td class="table_line">
		<input type="checkbox" id="regiao_chk" name="regiao_chk" value="1" />
		<label for="regiao_chk">Regi�o</label>
	</td>
	<td class="table_line" colspan="2">
		<select name="regiao" id="regiao" style="width: 140px;">
			<option value=""></option>
			<option value="SUL">Sul</option>
			<option value="SP">S�o Paulo - Capital</option>
			<option value="SP-interior">S�o Paulo - Interior</option>
			<option value="RJ">Rio de Janeiro</option>
			<option value="MG">Minas Gerais</option>
			<option value="PE">Pernambuco</option>
			<option value="BA">Bahia</option>
			<option value="BR-NEES">Nordeste + E.S.</option>
			<option value="BR-NCO">Norte + C.O.</option>
		</select>
	</td>
	<td class="table_line"> &nbsp; </td>
</tr>
<?php } 
if($login_fabrica == 35){ ?>
<TR>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<TD  class="table_line" ><INPUT TYPE="checkbox" NAME="chk_opt20" ID="chk_opt20" value="1"> N�mero do Atendimento Callcenter</TD>
	<td class="table_line" style="text-align: left;" colspan="2"><input type="text" name="_atendimento_callcenter" id="_atendimento_callcenter" size="17"></td>
	<TD class="table_line" >&nbsp;</TD>
</TR>
<?}
if(in_array($login_fabrica,array(74,50))) {
		if($login_fabrica == 50){
            $titulo_campo = "Tipo de Atendimento:";
        }else{
            $titulo_campo = "Classe do atendimento:";
        }
	?>
<tr>
	<TD class="table_line" style="text-align: center;">&nbsp;</TD>
	<td class="table_line"><INPUT TYPE="checkbox" NAME="chk_opt21" ID="chk_opt21" value="1"><?=$titulo_campo?></td>
	<td class="table_line" colspan='2'>
	<?php if ($login_fabrica == 50) { ?>
			<select name='hd_motivo_ligacao[]' id='hd_motivo_ligacao' class='frm' multiple='multiple'>
		<? } else { ?>
			<select name='hd_motivo_ligacao' id='hd_motivo_ligacao' class='frm'>
			<option value=''></option>
		<? } ?>
		<?php
			$sqlLigacao = "SELECT hd_motivo_ligacao, descricao FROM tbl_hd_motivo_ligacao WHERE fabrica = $login_fabrica AND ativo IS TRUE $disabled; ";

			$resLigacao = pg_query($con,$sqlLigacao);
				for ($i = 0; $i < pg_num_rows($resLigacao); $i++) {
					$hd_motivo_ligacao_aux = pg_result($resLigacao,$i,'hd_motivo_ligacao');
					$motivo_ligacao    = pg_result($resLigacao,$i,'descricao');
					echo " <option value='".$hd_motivo_ligacao_aux."' ".($hd_motivo_ligacao_aux == $hd_motivo_ligacao ? "selected='selected'" : '').">$motivo_ligacao</option>";

				}?>

			</select>
	</td>
	<TD class="table_line" >&nbsp;</TD>
</tr>
<? } ?>

<TR>
	<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
</TR>
<TR>
	<TD class="table_line" width='90'>&nbsp;</TD>
	<TD colspan="<?php echo (in_array($login_fabrica, array(152,180,181,182))) ? "":"4";?>" class="table_line"><b><?=traduz("Condi��o do Atendimento")?></b></TD>
	<?php if (in_array($login_fabrica, array(152,180,181,182))) {?>
	<TD class="table_line"><b><?=traduz("Tipo de Atendimento")?></b></TD>
	<TD colspan="2" class="table_line"><b><?=traduz("D�vida")?></b></TD>
	<?php }?>
</TR>



<?php
//HD 244202: Colocar os status: Todos, Abertos, Pendentes, Resolvidos, Cancelados
//Conceitos: Aberto ( sem nenhum tratamento recebido atrav�s do fale conosco ou aberto durante o atendimento) Pendente ( que foram mudados pelo operador manualmente,solu��o pendente em outro setor ) Resolvido ( solucionado ou fechado) e cancelados
//HD 409490 - Altera��o dos STATUS para receber da tbl_hd_status

$sql_status = " SELECT status FROM tbl_hd_status where fabrica=$login_fabrica ";
$res_status = pg_query($con,$sql_status);

if (pg_num_rows($res_status)>0){
?>
	<TR>
		<TD class="table_line" style="text-align: left;">&nbsp;</TD>
		<TD  class="table_line" colspan='<?php echo (in_array($login_fabrica, array(152,180,181,182))) ? "":"4";?>'>
			<? if($login_fabrica == 74){ ?>
				<input type="checkbox" name="situacao[]" value="TODOS"><?=traduz('Todos')?> <br />
				<input type="checkbox" name="situacao[]" value="nao_reolvidos"><?=traduz('N�o Resolvidos')?>
			<? } else{ ?>
				<input type="radio" name="situacao" value="TODOS"  checked><?=traduz('Todos')?>
			<? } ?>
		<?
		for ($i = 0; $i < pg_num_rows($res_status); $i++)
		{
			//$hd_status = utf8_decode(pg_result($res_status,$i,0));
			$hd_status = pg_result($res_status,$i,0);

			if($areaAdminCliente == true && !in_array($hd_status,array('Resolvido','Aberto'))){
				continue;
			}
		?>
			<br />
			<? if($login_fabrica == 74){ ?>
				<input type="checkbox" name="situacao[]" value="<?=$hd_status?>"><?=$hd_status?>
			<? } else{ ?>
				<input type="radio" name="situacao" value="<?=$hd_status?>"><?=$hd_status?>
			<? } ?>
		<?
		}
		?>
		</TD>

		<?php if (in_array($login_fabrica, array(152,180,181,182))) {?>
		<TD class="table_line">
			<input type="radio" name="tipo_atendimento_consumidor" value="TODOS"><?=traduz('Todos')?> <br />
			<input type="radio" name="tipo_atendimento_consumidor" value="R"><?=traduz('Revenda')?><br />
			<input type="radio" name="tipo_atendimento_consumidor" value="C"><?=traduz('Cliente Final')?><br />
			<input type="radio" name="tipo_atendimento_consumidor" value="S"><?=traduz('SAE')?><br />
			<input type="radio" name="tipo_atendimento_consumidor" value="W"><?=traduz('WhatsApp')?><br />
		</TD>
		<TD colspan="2" class="table_line">
			<input type="radio" name="duvida_consumidor" value="TODOS"><?=traduz('Todos')?> <br />
			<input type="radio" name="duvida_consumidor" value="T�cnica"><?=traduz('T�cnica')?><br />
			<input type="radio" name="duvida_consumidor" value="Comercial"><?=traduz('Comercial')?><br />
			<input type="radio" name="duvida_consumidor" value="Reclama��o"><?=traduz('Reclama��o')?><br />
		</TD>
		<?php }?>
	</TR>
<?
}else{
?>
	<TR>
		<TD class="table_line" style="text-align: left;">&nbsp;</TD>
		<TD  class="table_line" colspan='4'><input type="radio" name="situacao" value="TODOS"  checked><?=traduz('Todos')?></TD>
	</TR>
<?
}



if($login_fabrica == 24){
?>
	<tr>
		<TD class="table_line" style="text-align: left;">&nbsp;</TD>
		<TD  class="table_line" colspan='4'><input type="radio" name="situacao" value="com_intervencao"><?=traduz('Atendimentos que necessitaram de interven��o')?>
	</tr>
	<tr>
		<TD class="table_line" style="text-align: left;">&nbsp;</TD>
		<TD  class="table_line" colspan='4'><input type="radio" name="situacao" value="nescessita_intervencao"><?=traduz('Atendimentos que precisam de interven��o')?>
	</tr>
<?
}
?>

<?php if($login_fabrica == 30){ ?>
	<TR>
		<TD colspan="5" class="table_line"><hr color='#eeeeee'></TD>
	</TR>

	<tr>
		<TD class="table_line" width='90'>&nbsp;</TD>
		<TD colspan="<?php echo (in_array($login_fabrica, array(152,180,181,182))) ? "":"4";?>" class="table_line"><b><?=traduz('Tipo de Atendimento')?></b></TD>
	</tr>

	<TR>
		<TD class="table_line" style="text-align: left;">&nbsp;</TD>
		<TD  class="table_line" colspan='4'><input type="radio" name="tipo_atendimento_consumidor" value="R"><?=traduz('Revenda')?><br /></TD>
	</TR>
	<TR>
		<TD class="table_line" style="text-align: left;">&nbsp;</TD>
		<TD  class="table_line" colspan='4'><input type="radio" name="tipo_atendimento_consumidor" value="C"><?=traduz('Consumidor')?><br /></TD>
	</TR>


	
				
<?php } ?>


<tr><td colspan="5" class="table_line">&nbsp;</td></tr>
<TR>
	<TD colspan="5" class="table_line" align="center"><center><input type="button" style="background:url(imagens_admin/btn_pesquisar_400.gif); width:400px;cursor:pointer;" value="&nbsp;" onClick="document.frm_pesquisa.submit();" alt="Preencha as op��es e clique aqui para pesquisar"></center></TD>
</TR>
</TABLE>
</FORM>
<BR>

<? include "rodape.php" ?>
