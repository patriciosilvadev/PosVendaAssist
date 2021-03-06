<?
include "dbconfig.php";
include "includes/dbconnect-inc.php";

$admin_privilegios="financeiro";
include "autentica_admin.php";

// AJAX -> solicita a exporta��o dos extratos
if (strlen($_GET["exportar"])>0){
	//include "../ajax_cabecalho.php";
	//system("/www/cgi-bin/bosch/exporta-extrato.pl",$ret);
	$dados = "$login_fabrica\t$login_admin\t".date("d-m-Y H:m:s");
	exec ("echo '$dados' > /tmp/bosch/exporta/pronto.txt");
	echo "ok|Exporta��o conclu�da com sucesso! Dentro de alguns minutos os arquivos de exporta��o estar�o dispon�veis no sistema.";
	exit;
}
// FIM DO AJAX -> solicita a exporta��o dos extratos

$msg_erro = "";

if (strlen($_POST["btnacao"]) > 0) $btnacao = trim(strtolower($_POST["btnacao"]));
if (strlen($_GET["btnacao"])  > 0) $btnacao = trim(strtolower($_GET["btnacao"]));

if (strlen($_POST["posto"]) > 0) $posto = $_POST["posto"];
if (strlen($_GET["posto"])  > 0) $posto = $_GET["posto"];

if (strlen($_GET["liberar"]) > 0) $liberar = $_GET["liberar"];

if (strlen($liberar) > 0){
	$sql = "UPDATE tbl_extrato SET liberado = current_date";
			if($login_fabrica == 6 OR $login_fabrica == 11 OR $login_fabrica == 24 OR $login_fabrica == 14){ $sql .= ", aprovado = current_date";}
				$sql .= " WHERE extrato = $liberar";
	$res = @pg_exec($con,$sql);
	$msg_erro = @pg_errormessage($con);

	//Wellington 14/12/2006 - ENVIA EMAIL PARA O POSTO QDO O EXTRATO � LIBERADO
	if (strlen($msg_erro)==0 and $login_fabrica==11) {
		$sql = "SELECT email, posto FROM tbl_posto 
				JOIN tbl_extrato USING(posto) 
				WHERE extrato = $liberar";
		$res = @pg_exec($con,$sql);

		$xemail = trim(pg_result($res,0,email));
		$xposto = trim(pg_result($res,0,posto));
		if (strlen($xemail) > 0) {
			if (strlen($_GET["msg_aviso"])  > 0) $msg_aviso = "AVISO: ".$_GET["msg_aviso"]."<BR><BR><BR>";
			elseif (strlen($_POST["msg_aviso"])  > 0) $msg_aviso = "AVISO: ".$_POST["msg_aviso"]."<BR><BR><BR>";
			$remetente    = "LENOXXSOUND FINANCEIRO <luiz@lenoxxsound.com.br>"; 
			$destinatario = $xemail; 
			$assunto      = "SEU EXTRATO FOI LIBERADO"; 
			$mensagem     =  "* O EXTRATO N�".$liberar." EST� LIBERADO NO SITE: www.telecontrol.com.br *<br><br>".$msg_aviso ; 
			$headers="Return-Path: <luiz@lenoxxsound.com.br>\nFrom:".$remetente."\nBcc:luiz@lenoxxsound.com.br,wellington@telecontrol.com.br,igor@telecontrol.com.br \nContent-type: text/html\n"; 
			
			if ( @mail($destinatario,$assunto,$mensagem,$headers) ) {
			}else{
				$remetente    = "MERCURIO FINANCEIRO <wellington@telecontrol.com.br>"; 
				$destinatario = "wellington@telecontrol.com.br"; 
				$assunto      = "EMAIL N�O ENVIADO (SEU EXTRATO FOI LIBERADO)"; 
				$mensagem     = "* N�O ENVIADO PARA O POSTO ".$xemail." *"; 
				$headers="Return-Path: <wellington@telecontrol.com.br>\nFrom: ".$remetente."\nContent-type: text/html\n"; 
				
				@mail($destinatario,$assunto,$mensagem,$headers);
			}
		}
	}

	//Samuel 02/01/2007 - ENVIA EMAIL PARA O POSTO QDO O EXTRATO � LIBERADO
	if (strlen($msg_erro)==0 and $login_fabrica==24) {
		$sql = "SELECT email, posto FROM tbl_posto 
				JOIN tbl_extrato USING(posto) 
				WHERE extrato = $liberar";
		$res = @pg_exec($con,$sql);

		$xemail = trim(pg_result($res,0,email));
//		$xposto = trim(pg_resulst($res,0,posto));
		$xposto = trim(pg_result($res,0,posto));
		if (strlen($xemail) > 0) {
			$remetente    = "SUGGAR FINANCEIRO <suggat@suggar.com.br>"; 
			$destinatario = $xemail; 
			$assunto      = "SEU EXTRATO FOI LIBERADO"; 
			$mensagem     = "* O EXTRATO N�".$liberar." EST� LIBERADO NO SITE: www.telecontrol.com.br *"; 
			$headers="Return-Path: <suggat@suggar.com.br>\nFrom:".$remetente."\nBcc:marilene@suggar.com.br,helpdesk@telecontrol.com.br \nContent-type: text/html\n"; 
			
			if ( @mail($destinatario,$assunto,$mensagem,$headers) ) {
			}else{
				$remetente    = "MERCURIO FINANCEIRO <helpdesk@telecontrol.com.br>"; 
				$destinatario = "helpdesk@telecontrol.com.br"; 
				$assunto      = "EMAIL N�O ENVIADO (SEU EXTRATO FOI LIBERADO)"; 
				$mensagem     = "* N�O ENVIADO PARA O POSTO ".$xemail." *"; 
				$headers="Return-Path: <helpdesk@telecontrol.com.br>\nFrom: ".$remetente."\nContent-type: text/html\n"; 
				
				@mail($destinatario,$assunto,$mensagem,$headers);
			}
		}
	}

	//wellington liberar
	/* LENOXX - SETA EXTRATO DE DEVOLU��O PARA OS FATURAMENTOS */
	if (strlen($msg_erro)==0 and $login_fabrica==11 and 1==2) {
		$sql = "UPDATE tbl_faturamento SET extrato_devolucao = $liberar
				WHERE  tbl_faturamento.fabrica = $login_fabrica
				AND    tbl_faturamento.posto   = $xposto
				AND    tbl_faturamento.extrato_devolucao IS NULL
				AND    tbl_faturamento.emissao <  current_date - interval'15 day'
				AND    tbl_faturamento.emissao >  '2007-01-30'";
		$res = pg_exec($con,$sql);
	}
}

/*
//alterado takashi 06/07/2006 a pedido da angelica
if ($btnacao == 'liberar_tudo'){
	if (strlen($_POST["total_postos"]) > 0) $total_postos = $_POST["total_postos"];
	
	for ($i=0; $i < $total_postos; $i++) {
		$extrato = $_POST["liberar_".$i];
		if (strlen($extrato) > 0) {
			$sql = "UPDATE tbl_extrato SET liberado = current_date
					WHERE  tbl_extrato.extrato = $extrato
					and    tbl_extrato.fabrica = $login_fabrica";
			$res = @pg_exec($con,$sql);
			$msg_erro = @pg_errormessage($con);
		}
	}
}

*/
//takashi 06/07/2006
//angelica e tectoy com problemas de libera��o de extratos, quer que qdo extrato liberado, j� seja aprovado
//coloquei um if fabrica 6 para setar aprovado com a data tambem
if ($btnacao == 'liberar_tudo'){
	if (strlen($_POST["total_postos"]) > 0) $total_postos = $_POST["total_postos"];
	
	$sql = "begin";
	$res = @pg_exec($con,$sql);

	for ($i=0; $i < $total_postos; $i++) {
		$extrato = $_POST["liberar_".$i];
		if (strlen($extrato) > 0 AND strlen($msg_erro) == 0) {
			$sql = "UPDATE tbl_extrato SET liberado = current_date ";
			if($login_fabrica == 6 OR $login_fabrica == 11 OR $login_fabrica == 24 OR $login_fabrica == 14){ 
				$sql .= ", aprovado = current_date ";
			}
			
			$sql .= "WHERE  tbl_extrato.extrato = $extrato
					 and    tbl_extrato.fabrica = $login_fabrica";
			$res = @pg_exec($con,$sql);
			$msg_erro = @pg_errormessage($con);

			//Wellington 14/12/2006 - ENVIA EMAIL PARA O POSTO QDO O EXTRATO � LIBERADO
			if (strlen($msg_erro)==0 and $login_fabrica==11) {
				$sql = "SELECT email, posto FROM tbl_posto 
						JOIN tbl_extrato USING(posto) 
						WHERE extrato = $extrato";
				$res = @pg_exec($con,$sql);

				$xemail = trim(pg_result($res,0,email));
				$xposto = trim(pg_result($res,0,posto));
				if (strlen($xemail) > 0) {
					if (strlen($_GET["msg_aviso"])  > 0) $msg_aviso = "AVISO: ".$_GET["msg_aviso"]."<BR>";
					elseif (strlen($_POST["msg_aviso"])  > 0) $msg_aviso = "AVISO: ".$_POST["msg_aviso"]."<BR><BR><BR>";
					$remetente    = "LENOXXSOUND FINANCEIRO <luiz@lenoxxsound.com.br>"; 
					$destinatario = $xemail; 
					$assunto      = "SEU EXTRATO FOI LIBERADO"; 
					$mensagem     = "* O EXTRATO N�".$extrato." EST� LIBERADO NO SITE: www.telecontrol.com.br * <br><br>".$msg_aviso ; 
					$headers="Return-Path: <luiz@lenoxxsound.com.br>\nFrom:".$remetente."\nBcc:luiz@lenoxxsound.com.br,wellington@telecontrol.com.br,igor@telecontrol.com.br \nContent-type: text/html\n"; 
					
					if ( @mail($destinatario,$assunto,$mensagem,$headers) ) {
					}else{
						$remetente    = "MERCURIO FINANCEIRO <wellington@telecontrol.com.br>"; 
						$destinatario = "wellington@telecontrol.com.br"; 
						$assunto      = "EMAIL N�O ENVIADO (SEU EXTRATO FOI LIBERADO)"; 
						$mensagem     = "* N�O ENVIADO PARA O POSTO ".$xemail." *"; 
						$headers="Return-Path: <wellington@telecontrol.com.br>\nFrom: ".$remetente."\nContent-type: text/html\n"; 
						
						@mail($destinatario,$assunto,$mensagem,$headers);
					}
				}
			}
		}

		//wellington liberar
		/* LENOXX - SETA EXTRATO DE DEVOLU��O PARA OS FATURAMENTOS */
		if (strlen($extrato) > 0 and strlen($msg_erro)==0 and $login_fabrica==11 and 1==2) {
			$sql = "UPDATE tbl_faturamento SET extrato_devolucao = $extrato
					WHERE  tbl_faturamento.fabrica = $login_fabrica
					AND    tbl_faturamento.posto   = $xposto
					AND    tbl_faturamento.extrato_devolucao IS NULL
					AND    tbl_faturamento.emissao <  current_date - interval'15 day'
					AND    tbl_faturamento.emissao >  '2007-01-30'";
			$res = pg_exec($con,$sql);
		}
	}

	if (strlen($msg_erro) == 0)
		$sql = "commit";
	else
		$sql = "rollback";
	$res = @pg_exec($con,$sql);

}
//takashi 06/07/2006


if ($btnacao == "acumular_tudo") {
	if (strlen($_POST["total_postos"]) > 0) $total_postos = $_POST["total_postos"];

	$res = pg_exec($con,"BEGIN TRANSACTION");

	for ($i = 0 ; $i < $total_postos ; $i++) {
		$extrato = $_POST["acumular_" . $i];

		if (strlen($extrato) > 0) {
			$sql = "SELECT fn_acumula_extrato ($login_fabrica, $extrato);";
			$res = pg_exec($con,$sql);
			$msg_erro = pg_errormessage($con);
		}
		if (strlen($msg_erro) > 0) break;
	}

	if (strlen($msg_erro) == 0) {
		$res = pg_exec($con,"COMMIT TRANSACTION");
	}else{
		$res = pg_exec($con,"ROLLBACK TRANSACTION");
	}
}

if (strlen($_GET["aprovar"]) > 0) $aprovar = $_GET["aprovar"]; // � o numero do extrato

if (strlen($aprovar) > 0){
	//if ($login_fabrica == 1){

	//atualiza campos de notas fiscais
	if ($login_fabrica==20) {
		$nf_mao_de_obra = $_GET["nf_mao_de_obra"];
		if (strlen(trim($nf_mao_de_obra))==0) {
			$nf_mao_de_obra = 'null';
		}
	
		$nf_devolucao   = $_GET["nf_devolucao"];
		if (strlen(trim($nf_devolucao))==0) {
			$nf_devolucao = 'null';
		}

		$sql = "UPDATE tbl_extrato_extra 
				SET nota_fiscal_mao_de_obra = '$nf_mao_de_obra',
				nota_fiscal_devolucao       = '$nf_devolucao'
				WHERE extrato = $aprovar";
		$res = pg_exec($con,$sql);
	}

		$sql = "SELECT fn_aprova_extrato($posto,$login_fabrica,$aprovar)";
		$res = pg_exec($con,$sql);
		$msg_erro = pg_errormessage($con);
		//Raphael HD-1260 Retirar libera��o
		/*if($login_fabrica==20){
			//A OS QUANDO � APROVADA PARA A BOSCH ELA � AUTOMATICAMENTE LIBERADA TAMB�M
			$sql = "UPDATE tbl_extrato set liberado = aprovado 
					WHERE  posto   = $posto 
					AND    fabrica = $login_fabrica
					AND    extrato = $aprovar";
			$res = pg_exec($con,$sql);
			$msg_erro = pg_errormessage($con);
		}*/

	//}
}

$layout_menu = "financeiro";
$title = "Consulta e Manuten��o de Extratos";

include "cabecalho.php";

?>

<p>

<style type="text/css">

.menu_top {
	text-align: center;
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: 10PX	;
	font-weight: bold;
	border: 1px solid;
;
	background-color: #D9E2EF
}

.table_line {
	text-align: left;
	font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
	font-size: 10px;
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
.quadro{
	border: 1px solid #596D9B;
	width:450px;
	height:50px;
	padding:10px;
	
}

.botao {
		border-top: 1px solid #333;
	        border-left: 1px solid #333;
	        border-bottom: 1px solid #333;
	        border-right: 1px solid #333;
	        font-size: 13px;
	        margin-bottom: 10px;
	        color: #0E0659;
		font-weight: bolder;
}

.texto_padrao {
	        font-size: 12px;
}
</style>
<script language='javascript' src='../ajax.js'></script>

<script language="JavaScript">

/* ============= Fun��o PESQUISA DE POSTOS ====================
Nome da Fun��o : fnc_pesquisa_posto (cnpj,nome)
		Abre janela com resultado da pesquisa de Postos pela
		C�digo ou CNPJ (cnpj) ou Raz�o Social (nome).
=================================================================*/

function fnc_pesquisa_posto (campo, campo2, tipo) {
	if (tipo == "nome" ) {
		var xcampo = campo;
	}

	if (tipo == "cnpj" ) {
		var xcampo = campo2;
	}

	if (xcampo.value != "") {
		var url = "";
		url = "posto_pesquisa.php?campo=" + xcampo.value + "&tipo=" + tipo ;
		janela = window.open(url, "janela", "toolbar=no, location=no, status=yes, scrollbars=yes, directories=no, width=600, height=300, top=0, left=0");
		janela.retorno = "<? echo $PHP_SELF ?>";
		janela.nome	= campo;
		janela.cnpj	= campo2;
		janela.focus();
	}
}

var checkflag = "false";
function check(field) {
    if (checkflag == "false") {
        for (i = 0; i < field.length; i++) {
            field[i].checked = true;
        }
        checkflag = "true";
        return true;
    }
    else {
        for (i = 0; i < field.length; i++) {
            field[i].checked = false;
        }
        checkflag = "false";
        return true;
    }
}

function AbrirJanelaObs (extrato) {
	var largura  = 400;
	var tamanho  = 250;
	var lar      = largura / 2;
	var tam      = tamanho / 2;
	var esquerda = (screen.width / 2) - lar;
	var topo     = (screen.height / 2) - tam;
	var link = "extrato_status.php?extrato=" + extrato;
	window.open(link, "janela", "toolbar=no, location=no, status=yes, menubar=no, scrollbars=no, directories=no, resizable=no, width=" + largura + ", height=" + tamanho + ", top=" + topo + ", left=" + esquerda + "");
}
function gerarExportacao(but){
	 if (but.value == 'Exportar Extratos' ) {
		if (confirm('Deseja realmente prosseguir com a exporta��o?\n\nSer� exportado somente os extratos aprovados e liberados.')){
			but.value='Exportando...';
			exportar();
		}
	} else {
		 alert ('Aguarde submiss�o');
	}

}

function retornaExporta(http) {
	if (http.readyState == 4) {
		if (http.status == 200) {
			results = http.responseText.split("|");
			if (typeof (results[0]) != 'undefined') {
				if (results[0] == 'ok') {
					alert(results[1]);
				}else{
					alert (results[1]);
				}
			}else{
				alert ("N�o existe extratos a serem exportados.");
			}
		}
	}
}

function exportar() {
	url = "<?= $PHP_SELF ?>?exportar=sim";
	http.open("GET", url , true);
	http.onreadystatechange = function () { retornaExporta(http) ; } ;
	http.send(null);
}
</script>

<script language="javascript" src="js/cal2.js"></script>
<script language="javascript" src="js/cal_conf2.js"></script>
<?
if (strlen($msg_erro) > 0) {
	echo "<table width='600' align='center' border='0' cellspacing='1' cellpadding='1' class='error'>\n";
	echo "<tr>";
	echo "<td>$msg_erro</td>";
	echo "</tr>";
	echo "</table>\n";
}


$data_inicial = $_POST['data_inicial'];
if (strlen($_GET['data_inicial']) > 0) $data_inicial = $_GET['data_inicial'];

$data_final   = $_POST['data_final'];
if (strlen($_GET['data_final']) > 0) $data_final = $_GET['data_final'];

$posto_nome   = $_POST['posto_nome'];
if (strlen($_GET['posto_nome']) > 0) $posto_nome = $_GET['posto_nome'];
if (strlen($_GET['razao']) > 0) $posto_nome = $_GET['razao'];

$posto_codigo = $_POST['posto_codigo'];
if (strlen($_GET['posto_codigo']) > 0) $posto_codigo = $_GET['posto_codigo'];
if (strlen($_GET['cnpj']) > 0) $posto_codigo = $_GET['cnpj'];

echo "<TABLE width='600' align='center' border='0' cellspacing='3' cellpadding='2'>\n";
echo "<FORM METHOD='GET' NAME='frm_extrato' ACTION=\"$PHP_SELF\">";
echo "<input type='hidden' name='btnacao' value=''>";

echo "<TR class='menu_top'>\n";
echo "	<TD COLSPAN='2' ALIGN='center'>";
echo "		Consultar postos com extratos fechados entre";
echo "	</TD>";
echo "<TR>\n";

echo "<TR>\n";
echo "	<TD ALIGN='center'>";
echo "	Data Inicial ";
echo "	<INPUT size='12' maxlength='10' TYPE='text' NAME='data_inicial' value='$data_inicial' class='frm'>&nbsp;<IMG src=\"imagens_admin/btn_lupa.gif\" align='absmiddle' onclick=\"javascript:showCal('dataPesquisaInicial_Extrato')\" style='cursor:pointer' alt='Clique aqui para abrir o calend�rio'>\n";
echo "	</TD>\n";

echo "	<TD ALIGN='center'>";
echo "	Data Final ";
echo "	<INPUT size='12' maxlength='10' TYPE='text' NAME='data_final' value='$data_final' class='frm'>&nbsp;<IMG src=\"imagens_admin/btn_lupa.gif\" align='absmiddle' onclick=\"javascript:showCal('dataPesquisaFinal_Extrato')\" style='cursor:pointer' alt='Clique aqui para abrir o calend�rio'>\n";
echo "</TD>\n";
echo "</TR>\n";

echo "<TR class='menu_top'>\n";
echo "	<TD COLSPAN='2' ALIGN='center'>";
echo "		Somente extratos do posto";
echo "	</TD>";
echo "<TR>\n";

echo "<TR >\n";
echo "	<TD COLSPAN='2' ALIGN='center' nowrap>";
echo "CNPJ";
echo "		<input type='text' name='posto_codigo' size='18' value='$posto_codigo' class='frm'>&nbsp;<img src='imagens/btn_buscar5.gif' border='0' align='absmiddle' style='cursor: pointer;' onclick=\"javascript: fnc_pesquisa_posto (document.frm_extrato.posto_nome,document.frm_extrato.posto_codigo,'cnpj')\">";

echo "&nbsp;&nbsp;Raz�o Social ";
echo "		<input type='text' name='posto_nome' size='45' value='$posto_nome' class='frm'>&nbsp;<img src='imagens/btn_buscar5.gif' border='0' align='absmiddle' onclick=\"javascript: fnc_pesquisa_posto (document.frm_extrato.posto_nome,document.frm_extrato.posto_codigo,'nome')\" style='cursor: pointer;'>";
echo "	</TD>";
echo "<TR>\n";

if($login_fabrica == 20){
// MLG 2009-08-04 HD 136625
    $sql = 'SELECT pais,nome FROM tbl_pais';
    $res = pg_query($con,$sql);
    $p_tot = pg_num_rows($res);
    for ($i; $i<$p_tot; $i++) {
        list($p_code,$p_nome) = pg_fetch_row($res, $i);
    	$sel_paises .= "\t\t\t\t<option value='$p_code'";
        $sel_paises .= ($pais==$p_code)?" selected":"";
        $sel_paises .= ">$p_nome</option>\n";
    }
?>
	<tr class="Conteudo" bgcolor="#D9E2EF" >
		<td colspan='4' align='center'>Pa�s
			<select name='pais' size='1' class='frm'>
			 <option></option>
            <?echo $sel_paises;?>
			</select>
		</td>
	</tr>
<?}

echo "</TABLE>\n";

echo "<br><img src=\"imagens_admin/btn_filtrar.gif\" onclick=\"javascript: document.frm_extrato.btnacao.value='filtrar' ; document.frm_extrato.submit() \" ALT=\"Filtrar extratos\" border='0' style=\"cursor:pointer;\">\n";

echo "</form>";


// INICIO DA SQL
$data_inicial = $_POST['data_inicial'];
if (strlen($_GET['data_inicial']) > 0) $data_inicial = $_GET['data_inicial'];
$data_final   = $_POST['data_final'];
if (strlen($_GET['data_final']) > 0) $data_final = $_GET['data_final'];
$posto_codigo = $_POST['posto_codigo'];
if (strlen($_GET['posto_codigo']) > 0) $posto_codigo = $_GET['posto_codigo'];
if (strlen($_GET['cnpj']) > 0) $posto_codigo = $_GET['cnpj'];


$data_inicial = str_replace (" " , "" , $data_inicial);
$data_inicial = str_replace ("-" , "" , $data_inicial);
$data_inicial = str_replace ("/" , "" , $data_inicial);
$data_inicial = str_replace ("." , "" , $data_inicial);


$data_final = str_replace (" " , "" , $data_final);
$data_final = str_replace ("-" , "" , $data_final);
$data_final = str_replace ("/" , "" , $data_final);
$data_final = str_replace ("." , "" , $data_final);


if (strlen ($data_inicial) == 6) $data_inicial = substr ($data_inicial,0,4) . "20" . substr ($data_inicial,4,2);
if (strlen ($data_final)   == 6) $data_final   = substr ($data_final  ,0,4) . "20" . substr ($data_final  ,4,2);

if (strlen ($data_inicial) > 0) $data_inicial = substr ($data_inicial,0,2) . "/" . substr ($data_inicial,2,2) . "/" . substr ($data_inicial,4,4);
if (strlen ($data_final)   > 0) $data_final   = substr ($data_final,0,2)   . "/" . substr ($data_final,2,2)   . "/" . substr ($data_final,4,4);


$pais = $_POST['pais'];
if (strlen($_GET['pais']) > 0) $pais = $_GET['pais'];


if (strlen ($posto_codigo) > 0 OR (strlen ($data_inicial) > 0 and strlen ($data_final) > 0) ) {
	$sql = "SELECT  tbl_posto.posto                                                        ,
					tbl_posto.nome                                                         ,
					tbl_posto.cnpj                                                         ,
					tbl_posto.email                                                        ,
					tbl_posto_fabrica.codigo_posto                                         ,
					tbl_posto_fabrica.distribuidor                                         ,
					tbl_tipo_posto.descricao AS tipo_posto                                 ,
					tbl_extrato.extrato                                                    ,
					tbl_extrato.liberado                                                   ,
					to_char (tbl_extrato.aprovado,'dd/mm/yyyy') as aprovado ,
					lpad (tbl_extrato.protocolo,5,'0') AS protocolo                        ,
					to_char (tbl_extrato.data_geracao,'dd/mm/yyyy') as data_geracao        ,
					tbl_extrato.total                                                      ,
					count (tbl_os.os) AS qtde_os                                           ,
					to_char (tbl_extrato_pagamento.data_pagamento,'dd/mm/yyyy') as baixado ,
					tbl_extrato_pagamento.valor_liquido                                    ,
					tbl_extrato_extra.nota_fiscal_devolucao                                ,
					tbl_extrato_extra.nota_fiscal_mao_de_obra
			FROM      tbl_extrato
			JOIN      tbl_posto USING (posto)
			JOIN      tbl_posto_fabrica     ON  tbl_extrato.posto         = tbl_posto_fabrica.posto
											AND tbl_posto_fabrica.fabrica = $login_fabrica
			JOIN      tbl_tipo_posto        ON  tbl_tipo_posto.tipo_posto = tbl_posto_fabrica.tipo_posto
											AND tbl_tipo_posto.fabrica    = $login_fabrica
			LEFT JOIN tbl_os_extra          ON  tbl_os_extra.extrato      = tbl_extrato.extrato
			LEFT JOIN tbl_os                ON  tbl_os.os                 = tbl_os_extra.os
											AND tbl_os.posto              = tbl_extrato.posto
											AND tbl_os.fabrica            = tbl_extrato.fabrica
			LEFT JOIN tbl_extrato_pagamento ON tbl_extrato.extrato        = tbl_extrato_pagamento.extrato
			LEFT JOIN tbl_extrato_extra on tbl_extrato.extrato = tbl_extrato_extra.extrato
			WHERE     tbl_extrato.fabrica = $login_fabrica
			AND       tbl_posto_fabrica.distribuidor IS NULL ";

	if ($login_fabrica == 1) $sql .= " AND       tbl_extrato.aprovado IS NULL ";

	if (strlen ($data_inicial) < 8) $data_inicial = date ("d/m/Y");
		$x_data_inicial = substr ($data_inicial,6,4) . "-" . substr ($data_inicial,3,2) . "-" . substr ($data_inicial,0,2);

	if (strlen ($data_final) < 10) $data_final = date ("d/m/Y");
		$x_data_final = substr ($data_final,6,4) . "-" . substr ($data_final,3,2) . "-" . substr ($data_final,0,2);

	if (strlen ($x_data_inicial) > 0 AND strlen ($x_data_final) > 0)
		$sql .= " AND      tbl_extrato.data_geracao BETWEEN '$x_data_inicial 00:00:00' AND '$x_data_final 23:59:59'";

	$xposto_codigo = str_replace (" " , "" , $posto_codigo);
	$xposto_codigo = str_replace ("-" , "" , $xposto_codigo);
	$xposto_codigo = str_replace ("/" , "" , $xposto_codigo);
	$xposto_codigo = str_replace ("." , "" , $xposto_codigo);

	if (strlen ($posto_codigo) > 0 ) $sql .= " AND tbl_posto.cnpj = '$xposto_codigo' ";
	if (strlen ($posto_nome) > 0 )   $sql .= " AND tbl_posto.nome ILIKE '%$posto_nome%' ";
	if($login_fabrica == 20) $sql .= " AND tbl_posto.pais = '$pais' ";
	$sql .= " GROUP BY tbl_posto.posto ,
					tbl_posto.nome ,
					tbl_posto.cnpj ,
					tbl_posto.email,
					tbl_posto_fabrica.codigo_posto ,
					tbl_posto_fabrica.distribuidor ,
					tbl_tipo_posto.descricao       ,
					tbl_extrato.extrato ,
					tbl_extrato.liberado ,
					tbl_extrato.total,
					tbl_extrato.aprovado,
					lpad (tbl_extrato.protocolo,5,'0'),
					tbl_extrato.data_geracao,
					tbl_extrato_pagamento.data_pagamento,
					tbl_extrato_pagamento.valor_liquido,
					tbl_extrato_extra.nota_fiscal_devolucao,
					tbl_extrato_extra.nota_fiscal_mao_de_obra";
	if ($login_fabrica <> 1) $sql .= " ORDER BY tbl_posto.nome, tbl_extrato.data_geracao";
	else                     $sql .= " ORDER BY tbl_posto_fabrica.codigo_posto, tbl_extrato.data_geracao";

//if ($ip == "201.92.127.116") { echo $sql;flush();}
//echo $sql;
//if($login_fabrica==20) echo nl2br($sql);
	$res = pg_exec ($con,$sql);

	if (pg_numrows ($res) == 0) {
		echo "<center><h2>Nenhum extrato encontrado</h2></center>";
	}
if($ip=="201.43.144.156") echo "$sql";
	if (pg_numrows ($res) > 0) {

		echo "<table width='700' height=16 border='0' cellspacing='0' cellpadding='0' align='center'>";
		echo "<tr>";
		echo "<td align='center' width='16' bgcolor='#FFE1E1'>&nbsp;</td>";
		echo "<td align='left'><font size=1><b>&nbsp; Extrato Avulso</b></font></td>";
		echo "</tr>";
		echo "</table>";

		for ($i = 0 ; $i < pg_numrows ($res) ; $i++) {
			$posto                   = trim(pg_result($res,$i,posto));
			$codigo_posto            = trim(pg_result($res,$i,codigo_posto));
			$nome                    = trim(pg_result($res,$i,nome));
			$email                   = trim(pg_result($res,$i,email));
			$tipo_posto              = trim(pg_result($res,$i,tipo_posto));
			$extrato                 = trim(pg_result($res,$i,extrato));
			$data_geracao            = trim(pg_result($res,$i,data_geracao));
			$qtde_os                 = trim(pg_result($res,$i,qtde_os));
			$total                   = trim(pg_result($res,$i,total));
			$baixado                 = trim(pg_result($res,$i,baixado));
			$extrato                 = trim(pg_result($res,$i,extrato));
			$distribuidor            = trim(pg_result($res,$i,distribuidor));
			$total	                 = number_format ($total,2,',','.');
			$liberado                = trim(pg_result($res,$i,liberado));
			$aprovado                = trim(pg_result($res,$i,aprovado));
			$protocolo               = trim(pg_result($res,$i,protocolo));
			$nota_fiscal_devolucao   = trim(pg_result($res,$i,nota_fiscal_devolucao));
			$nota_fiscal_mao_de_obra = trim(pg_result($res,$i,nota_fiscal_mao_de_obra));


			// VERIFICA SE O  EXTRATO ESTA VAZIO E NAO TEM OS NA TBL_EXTRA, ENTAO MOSTRA AS OS E OS MOTIVOS DO CANCELAMENTO
			///////////////////////////////////////////////////////////////////////////
			$msg_os_deletadas="";
			if ($login_fabrica==2 and 1==2){
				/*hd 3163 Dynacom n�o estava conseguindo abrir os extratos, todo o extrato o sistema fazia esse select na tbl_os_status, que � muito lento e nao consiguia exibir as informa��es, retirei por que efetuei teste com extratos sem OSs e n�o mudou nada, se alguem reclamar volte... takashi 20/07/07*/
				$query = "SELECT tbl_os_status.observacao as obs,
								tbl_os.sua_os AS ooss
						FROM tbl_os_status join tbl_os USING(os)
						WHERE extrato = $extrato	
						AND (	SELECT count(*)	FROM tbl_os_extra WHERE  extrato = $extrato)=0";
				$res_deletado = pg_exec($con,$query);
				if (@pg_numrows($res_deletado) > 0) {
					for ($j = 0 ; $j < pg_numrows ($res_deletado) ; $j++) {
						$obs = pg_result($res_deletado,$j,obs);
						$os_del = pg_result($res_deletado,$j,ooss);
						$msg_os_deletadas ="<b>Extrato:</b> $extrato - <b>OS:</b> $os_del  - <b>Motivo:</b> $obs<br>";
					}
				}
			}
			//////////////////////////////////////////////////////////////////////////

			if (trim(pg_result($res,$i,valor_liquido)) <> '') {
				$valor_liquido = number_format (trim(pg_result($res,$i,valor_liquido)),2,',','.');
			}else{
				$valor_liquido = number_format (trim(pg_result($res,$i,total)),2,',','.');
			}

			if ($i == 0) {
				echo "<form name='Selecionar' method='post' action='$PHP_SELF'>\n";
				echo "<input type='hidden' name='btnacao' value=''>";
				echo "<table width='700' align='center' border='0' cellspacing='2'>\n";
				echo "<tr class = 'menu_top'>\n";
				echo "<td align='center'>C�digo</td>\n";
				echo "<td align='center' nowrap>Nome do Posto</td>\n";
				if ($login_fabrica == 1) echo "<td align='center' nowrap>Tipo</td>\n";
				if ($login_fabrica == 1 OR $login_fabrica == 19) {
				echo "<td align='center'>Protocolo</td>\n";
				} else {
				echo "<td align='center'>Extrato</td>\n";
				}
				echo "<td align='center'>Data</td>\n";
				echo "<td align='center' nowrap>Qtde. OS</td>\n";
				if ($login_fabrica == 1 ) {
					echo "<td align='center'>Total Pe�a</td>\n";
					echo "<td align='center'>Total MO</td>\n";
					echo "<td align='center'>Total Avulso</td>\n";
					echo "<td align='center'>Total Geral</td>\n";
					echo "<td align='center'>Obs.</td>\n";
				}else{
					echo "<td align='center'>Total</td>\n";
					
					// SONO - 04/09/206 exibir valor_liquido para intelbras //
					if ($login_fabrica == 14) {
						echo "<td align='center' nowrap>Total L�quido</td>\n";
					}
				}

				if ($login_fabrica==20) {
					echo "<td align='center'>N.F.<BR>M. De Obra</td>\n";
					echo "<td align='center'>N.F.<BR>Remessa</td>\n";
				}

				if($login_fabrica == 20) echo "<td align='center'>Auditado em</td>";
				else                     echo "<td align='center'>Baixado em</td>\n";
				if ($login_fabrica == 6 OR $login_fabrica == 14 OR $login_fabrica == 15 OR $login_fabrica == 11 OR $login_fabrica == 24) {
					echo "<td align='center'>Liberar <input type='checkbox' class='frm' name='marcar' value='tudo' title='Selecione ou desmarque todos' onClick='check(this.form.liberar);'></td>\n";
					if ($login_fabrica == 11) echo "<td align='center' nowrap>Posto sem<br>email</td>\n";
				}
				if ($login_fabrica == 1) {
					echo "<td align='center'>Acumular <input type='checkbox' class='frm' name='marcar' value='tudo' title='Selecione ou desmarque todos' onClick='check(this.form.acumular);'></td>\n";
				}
				echo "<td align='center' colspan='3' nowrap>Valores Adicionais ao Extrato</td>\n";
				echo "</tr>\n";
			}

			$cor = ($i % 2 == 0) ? "#F7F5F0" : "#F1F4FA";

			##### LAN�AMENTO DE EXTRATO AVULSO - IN�CIO #####
			if (strlen($extrato) > 0) {
				$sql = "SELECT count(*) as existe
						FROM   tbl_extrato_lancamento
						WHERE  extrato = $extrato
						and    fabrica = $login_fabrica";
				$res_avulso = pg_exec($con,$sql);

				if (@pg_numrows($res_avulso) > 0) {
					if (@pg_result($res_avulso, 0, existe) > 0) $cor = "#FFE1E1";
				}

			}
			##### LAN�AMENTO DE EXTRATO AVULSO - FIM #####

			echo "<tr bgcolor='$cor'>\n";

			echo "<td align='left'>$codigo_posto</td>\n";
			echo "<td align='left' nowrap>".substr($nome,0,20)."</td>\n";
			if ($login_fabrica == 1) echo "<td align='center' nowrap>$tipo_posto</td>\n";
			if($login_fabrica == 20)echo "<td align='center'><a href='extrato_os_aprova";
			else echo "<td align='center'><a href='extrato_consulta_os";
			if ($login_fabrica == 14) echo "_intelbras";
			echo ".php?extrato=$extrato&data_inicial=$data_inicial&data_final=$data_final&cnpj=$xposto_codigo&razao=$posto_nome' target='_blank'>";
			if ($login_fabrica == 1 OR $login_fabrica == 19 ) echo $protocolo;
			else                     echo $extrato;
			echo "</a></td>\n";
			echo "<td align='left'>$data_geracao</td>\n";
			echo "<td align='center'>$qtde_os</td>\n";
			if ($login_fabrica == 1) {
				$sql =	"SELECT SUM(tbl_os.pecas)       AS total_pecas     ,
								SUM(tbl_os.mao_de_obra) AS total_maodeobra ,
								tbl_extrato.avulso      AS total_avulso
						FROM tbl_os
						JOIN tbl_os_extra USING (os)
						JOIN tbl_extrato ON tbl_extrato.extrato = tbl_os_extra.extrato
						WHERE tbl_os_extra.extrato = $extrato
						GROUP BY tbl_extrato.avulso;";
				$resT = pg_exec($con,$sql);

				if (pg_numrows($resT) == 1) {
					echo "<td align='right' nowrap> " . number_format(pg_result($resT,0,total_pecas),2,',','.') . "</td>\n";
					echo "<td align='right' nowrap> " . number_format(pg_result($resT,0,total_maodeobra),2,',','.') . "</td>\n";
					echo "<td align='right' nowrap> " . number_format(pg_result($resT,0,total_avulso),2,',','.') . "</td>\n";
				}else{
					echo "<td>&nbsp;</td>\n";
					echo "<td>&nbsp;</td>\n";
					echo "<td>&nbsp;</td>\n";
				}
			}
			echo "<td align='right' nowrap> $total</td>\n";

			// SONO - 04/09/206 exibir valor_liquido para intelbras //
			if ($login_fabrica == 14) {
				echo "<td align='right' nowrap> $valor_liquido</td>\n";
			}	

			if ($login_fabrica == 1 ) echo "<td><a href=\"javascript: AbrirJanelaObs('$extrato');\">OBS.</a></td>\n";
			
			if ($login_fabrica==20) {
				echo "<td align='center'><INPUT TYPE='text' NAME='nota_fiscal_mao_de_obra_$i' id='nota_fiscal_mao_de_obra_$i' value='$nota_fiscal_mao_de_obra' size='8' maxlength='16'"; if (strlen($aprovado)>0) echo " readonly"; echo "></td>";
				echo "<td align='center'><INPUT TYPE='text' NAME='nota_fiscal_devolucao_$i' id='nota_fiscal_devolucao_$i' value='$nota_fiscal_devolucao' size='8' maxlength='16'"; if (strlen($aprovado)>0) echo " readonly"; echo "></td>";
			}

			if($login_fabrica==20) echo "<td align='left'>$aprovado</td>";
			else                   echo "<td align='left'>$baixado</td>\n";
			
			if ($login_fabrica == 6 OR $login_fabrica == 14 OR $login_fabrica == 15 OR $login_fabrica == 11 OR $login_fabrica == 24) {
				echo "<td align='center' nowrap>";
				if (strlen($liberado) == 0) {
					echo "<a href=\"javascript:window.location = '$PHP_SELF?liberar=$extrato&msg_aviso='+document.Selecionar.msg_aviso.value \">Liberar</a>";
					echo " <input type='checkbox' class='frm' name='liberar_$i' id='liberar' value='$extrato'>";
				}
				echo "</td>\n";
			}

			if ($login_fabrica == 11) {
				echo "<td align='center' nowrap>";
				if (strlen($email) == 0) {
					?>
					<center>
					<img src='imagens/btn_imprimir.gif' onclick="javascript: window.open('extrato_consulta_os_print.php?extrato=<? echo $extrato; ?>','printextrato','toolbar=no,location=no,directories=no,status=no,scrollbars=yes,menubar=yes,resizable=yes,width=700,height=480')" ALT='Imprimir' border='0' style='cursor:pointer;'>
					<?
				} else {
					echo "&nbsp;";
				}
				echo "</td>\n";
			}
			if ($login_fabrica == 24) {
				echo "<td align='center' nowrap>";
				if (strlen($email) == 0) {
					?>
					<center>
					<img src='imagens/btn_imprimir.gif' onclick="javascript: window.open('extrato_consulta_os_print.php?extrato=<? echo $extrato; ?>','printextrato','toolbar=no,location=no,directories=no,status=no,scrollbars=yes,menubar=yes,resizable=yes,width=700,height=480')" ALT='Imprimir' border='0' style='cursor:pointer;'>
					<?
				} else {
					echo "&nbsp;";
				}
				echo "</td>\n";
			}

			if ($login_fabrica == 1 OR $login_fabrica == 2 OR $login_fabrica == 8 OR $login_fabrica==20) {
				if ($msg_os_deletadas==""){
					echo "<td align='center' nowrap>";
					if (strlen($aprovado) == 0){
						if ($login_fabrica==20) {
							echo "<a href=\"javascript:if(confirm('Deseja aprovar todas as OS�s deste extrato? '))window.location='$PHP_SELF?aprovar=$extrato&posto=$posto&data_inicial=$data_inicial&data_final=$data_final&cnpj=$xposto_codigo&razao=$posto_nome&nf_mao_de_obra='+document.getElementById('nota_fiscal_mao_de_obra_$i').value+'&nf_devolucao='+document.getElementById('nota_fiscal_devolucao_$i').value\">";
							echo "<img src='imagens_admin/btn_aprovar_azul.gif' ALT='Aprovar o extrato'></a>";
						} else {
							echo "<a href='$PHP_SELF?aprovar=$extrato&posto=$posto&data_inicial=$data_inicial&data_final=$data_final&cnpj=$xposto_codigo&razao=$posto_nome'><img src='imagens_admin/btn_aprovar_azul.gif' ALT='Aprovar o extrato'></a>";
						}
						if($login_fabrica<>20)
						echo "<input type='checkbox' name='acumular_$i' id='acumular' value='$extrato' class='frm'>\n";
					}
					echo "</td>\n";
				}
			}

			// se o msg_os_deletadas for nulo o extrato n�o foi cancelado. Se n�o for nulo, o Extrato foi cancelado
			if ($msg_os_deletadas==""){
				echo "<td>";
				if (strlen($aprovado) == 0 OR $login_fabrica == 8)
					echo "<a href='extrato_avulso.php'><img src='imagens/btn_novo_azul.gif' ALT='Cadastrar um Novo Extrato'></a>";
				echo "</td>\n";
	
				echo "<td>";
				if (strlen($aprovado) == 0 OR $login_fabrica == 8)
					echo "<a href='extrato_avulso.php?extrato=$extrato&posto=$posto'><img src='imagens/btn_adicionar_azul.gif' ALT = 'Lan�ar itens no extrato'></a>";
				echo "</td>\n";
			}
			else{ //s� entra aqui se o extrato foi excluido e a fabrica eh 2-  DYNACON
				echo "<td colspan='3' align='center'>";
				echo "<b style='font-size:10px;color:red'>Extrato cancelado!!</b>";
				echo "</td>";	
				echo "</tr>";
				echo "<tr>";
				echo		 "<td></td>";
				echo 		"<td colspan=9 align='left'> <b style='font-size:12px;font-weight:normal'>$msg_os_deletadas</b> </td>";
				echo 	"</td>";
			}

			echo "</tr>\n";
		}
		echo "<tr>\n";
		if($login_fabrica == 11) {
			//IGOR HD 2075 CAMPO DE ENVIO DE MENSAGEM A POSTOS
			echo "<td colspan='7'>
				Quando um extrato � liberado, automaticamente � enviado um email para o posto. Se quiser acrescentar uma mensagem digite no campo abaixo.
				<br>
				<INPUT size='60' TYPE='text' NAME='msg_aviso' value=''>
			</td>\n";
		}else{
			echo "<td colspan='7'>&nbsp;<INPUT size='60' TYPE='hidden' NAME='msg_aviso' value=''></td>\n";
		}
		if ($login_fabrica == 6 OR $login_fabrica == 14 OR $login_fabrica == 15 OR $login_fabrica == 11 OR $login_fabrica==20 OR $login_fabrica == 24) {
			echo "<td align='center'>";
			echo "<a href='javascript: document.Selecionar.btnacao.value=\"liberar_tudo\" ; document.Selecionar.submit() '><font size='2'>Liberar Selecionados</font></a>";
			echo "<input type='hidden' name='total_postos' value='$i'>";
			echo "</td>\n";
		}

		if ($login_fabrica == 1 ) {
			echo "<td colspan='5'>&nbsp;</td>\n";
			echo "<td align='center'>";
			echo "<a href='javascript: document.Selecionar.btnacao.value=\"acumular_tudo\" ; document.Selecionar.submit() '>Acumular selecionados</a>";
			echo "<input type='hidden' name='total_postos' value='$i'>";
			echo "</td>\n";
		}
		echo "<td colspan='2'>&nbsp;</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</form>\n";


		//retirado a pedido de Andre chamado 2317
		if ($login_fabrica==20 and 1==2){
			echo "<br><center><div class='quadro'><input type='button' name='btn_exportar'' class='botao' value='Exportar Extratos' onclick=\"javascript:gerarExportacao(this)\"><br>S� ser�o exportados os Extratos que foram <B>Aprovados e Liberados</b></div></center>";
		}

	}

	if (strlen($msg_os_deletadas)>0 and$login_fabrica==2){
		echo "<br><div name='os_excluidas' style='border:1px solid #00ffff'><h4>OS excluidas</h4>$msg_os_deletadas;</div>";
	}

	if ($login_fabrica == 3) {
		############################## DISTRIBUIDORES
	
		echo "<br><br>";
	
		$sql = "SELECT  tbl_posto.posto               ,
						tbl_posto.nome                ,
						tbl_posto.cnpj                ,
						tbl_posto_fabrica.codigo_posto,
						tbl_posto_fabrica.distribuidor,
						tbl_extrato.extrato           ,
						to_char (tbl_extrato.data_geracao,'dd/mm/yyyy') as data_geracao,
						tbl_extrato.total,
						(SELECT count (tbl_os.os) FROM tbl_os JOIN tbl_os_extra USING (os) WHERE tbl_os_extra.extrato = tbl_extrato.extrato) AS qtde_os,
						to_char (tbl_extrato_pagamento.data_pagamento,'dd/mm/yyyy') as baixado
				FROM    tbl_extrato
				JOIN    tbl_posto USING (posto)
				JOIN    tbl_posto_fabrica ON tbl_extrato.posto = tbl_posto_fabrica.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
				left JOIN    tbl_extrato_pagamento ON tbl_extrato.extrato = tbl_extrato_pagamento.extrato
				WHERE   tbl_extrato.fabrica = $login_fabrica
				AND     tbl_posto_fabrica.distribuidor NOTNULL ";
	
		if (strlen ($data_inicial) < 8) $data_inicial = date ("d/m/Y");
			$x_data_inicial = substr ($data_inicial,6,4) . "-" . substr ($data_inicial,3,2) . "-" . substr ($data_inicial,0,2);
	
		if (strlen ($data_final) < 10) $data_final = date ("d/m/Y");
			$x_data_final = substr ($data_final,6,4) . "-" . substr ($data_final,3,2) . "-" . substr ($data_final,0,2);
	
		if (strlen ($x_data_inicial) > 0 AND strlen ($x_data_final) > 0)
		$sql .= " AND      tbl_extrato.data_geracao BETWEEN '$x_data_inicial 00:00:00' AND '$x_data_final 23:59:59'";
	
		$xposto_codigo = str_replace (" " , "" , $posto_codigo);
		$xposto_codigo = str_replace ("-" , "" , $xposto_codigo);
		$xposto_codigo = str_replace ("/" , "" , $xposto_codigo);
		$xposto_codigo = str_replace ("." , "" , $xposto_codigo);
	
		if (strlen ($posto_codigo) > 0 ) $sql .= " AND tbl_posto.cnpj = '$xposto_codigo' ";
		if (strlen ($posto_nome) > 0 ) $sql .= " AND tbl_posto.nome ILIKE '%$posto_nome%' ";
	
		$sql .= " GROUP BY tbl_posto.posto ,
						tbl_posto.nome ,
						tbl_posto.cnpj ,
						tbl_posto_fabrica.codigo_posto,
						tbl_posto_fabrica.distribuidor,
						tbl_extrato.extrato ,
						tbl_extrato.liberado ,
						tbl_extrato.total,
						tbl_extrato.data_geracao,
						tbl_extrato_pagamento.data_pagamento
					ORDER BY tbl_posto.nome, tbl_extrato.data_geracao";
		$res = pg_exec ($con,$sql);
	
		if (pg_numrows ($res) == 0) {
			echo "<center><h2>Nenhum extrato encontrado</h2></center>";
		}
	
		if (pg_numrows ($res) > 0) {
			for ($i = 0 ; $i < pg_numrows ($res) ; $i++) {
				$posto   = trim(pg_result($res,$i,posto));
				$codigo_posto   = trim(pg_result($res,$i,codigo_posto));
				$nome           = trim(pg_result($res,$i,nome));
				$extrato        = trim(pg_result($res,$i,extrato));
				$data_geracao   = trim(pg_result($res,$i,data_geracao));
				$qtde_os        = trim(pg_result($res,$i,qtde_os));
				$total          = trim(pg_result($res,$i,total));
				$baixado        = trim(pg_result($res,$i,baixado));
				$extrato        = trim(pg_result($res,$i,extrato));
				$distribuidor   = trim(pg_result($res,$i,distribuidor));
				$total	        = number_format ($total,2,',','.');
	
				if (strlen($distribuidor) > 0) {
					$sql = "SELECT  tbl_posto.nome                ,
									tbl_posto_fabrica.codigo_posto
							FROM    tbl_posto_fabrica
							JOIN    tbl_posto ON tbl_posto.posto = tbl_posto_fabrica.posto
							WHERE   tbl_posto_fabrica.posto   = $distribuidor
							AND     tbl_posto_fabrica.fabrica = $login_fabrica;";
					$resx = pg_exec ($con,$sql);
	
					if (pg_numrows($resx) > 0) {
						$distribuidor_codigo = trim(pg_result($resx,0,codigo_posto));
						$distribuidor_nome   = trim(pg_result($resx,0,nome));
					}
				}
	
				if ($i == 0) {
					echo "<table width='700' align='center' border='1' cellspacing='2'>";
					echo "<tr class = 'menu_top'>";
					echo "<td align='center'>C�digo</td>";
					echo "<td align='center' nowrap>Nome do Posto</td>";
					echo "<td align='center'>Extrato</td>";
					echo "<td align='center'>Data</td>";
					echo "<td align='center' nowrap>Qtde. OS</td>";
					echo "<td align='center'>Total</td>";
					echo "<td align='center' colspan='2'>Extrato Vinculado a um Distribuidor</td>";
					echo "</tr>";
				}
	
				echo "<tr>";
	
				echo "<td align='left'>$codigo_posto</td>";
				echo "<td align='left' nowrap>$nome</td>";
				echo "<td align='center'>$extrato</td>";
	
				echo "<td align='left'>$data_geracao</td>";
				echo "<td align='center'>$qtde_os</td>";
				echo "<td align='right' nowrap>R$ $total</td>";
				echo "<td align='left' nowrap><font face='verdana' color='#FF0000' size='-2'>$distribuidor_codigo - $distribuidor_nome</font></td>";
				echo "</tr>";
			}
			echo "</table>";
		}
	}
}
?>

<br>

<? include "rodape.php"; ?>
