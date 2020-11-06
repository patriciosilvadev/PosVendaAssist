<?
include "dbconfig.php";
include "includes/dbconnect-inc.php";
$admin_privilegios="financeiro";
include "autentica_admin.php";


$msg = "";

$btn_acao = $_POST['btn_acao'];
if (strlen($btn_acao) > 0) {
	$extrato = $_POST['extrato'];
	$posto = $_POST['posto'];
	$posto = $_GET['posto'];

	$res = pg_exec ($con,"BEGIN TRANSACTION");

	$sql = "SELECT * FROM tbl_extrato_devolucao WHERE extrato = $extrato";
	$res = pg_exec ($con,$sql);

	for ($i = 0 ; $i < pg_numrows ($res) ; $i++) {
		$extrato_devolucao = pg_result ($res,$i,extrato_devolucao);

		$nota_fiscal = trim($_POST['nota_fiscal_' . $extrato_devolucao]);
		$total_nota  = trim($_POST['total_nota_'  . $extrato_devolucao]);
		$base_icms   = trim($_POST['base_icms_'   . $extrato_devolucao]);
		$valor_icms  = trim($_POST['valor_icms_'  . $extrato_devolucao]);
		
		if (strlen($nota_fiscal) == 0) {
			$msg = " Favor informar o n�mero de todas as Notas de Devolu��o.";
			$res = pg_exec ($con,"ROLLBACK TRANSACTION");
		}
	
		$nota_fiscal = str_replace(".","",$nota_fiscal);
		$nota_fiscal = str_replace(",","",$nota_fiscal);
		$nota_fiscal = str_replace("-","",$nota_fiscal);

		$nota_fiscal = "000000" . $nota_fiscal;
		$nota_fiscal = substr ($nota_fiscal,strlen ($nota_fiscal)-6);

		if (strlen ($msg) == 0) {
			$sql =	"UPDATE tbl_extrato_devolucao SET
					nota_fiscal             = '$nota_fiscal'      ,
					total_nota              = $total_nota         ,
					base_icms               = $base_icms          ,
					valor_icms              = $valor_icms         
				WHERE extrato_devolucao = $extrato_devolucao";
#			echo nl2br($sql);
			$resX = @pg_exec ($con,$sql);
			$msg = pg_errormessage($con);
		}
	}

	if (strlen($msg) == 0) {
		$resX = pg_exec ($con,"COMMIT TRANSACTION");
		header ("Location: $PHP_SELF?extrato=$extrato");
		exit;
	}else{
		$resX = pg_exec ($con,"ROLLBACK TRANSACTION");
	}
}



$sql = "SELECT posto
		FROM tbl_extrato
		WHERE extrato = $extrato
		AND fabrica = $login_fabrica";
$res = pg_exec ($con,$sql);
if (pg_numrows ($res)==0){
	$msg_erro .= "Nenhum posto encontrado para este extrato!";
}else{
	$posto = pg_result ($res,0,posto);
}

$login_posto = $posto;

$msg_erro = "";

$layout_menu = "financeiro";
$title = "Consulta e Manuten��o de Extratos do Posto";

include "cabecalho.php";
?>

<br>
<center>
<?
	echo "<table width='550' align='center'>";
	echo "<tr><td>";
	echo "<b>Conforme determina a legisla��o local</b><p>";

	echo "Para toda nota fiscal de pe�as enviadas em garantia deve haver nota fiscal de devolu��o de todas as pe�as nos mesmos valores, quantidades e com os mesmos destaques de impostos obrigatoriamente.";
	echo "<br>";
	echo "O valor da m�o-de-obra ser� exibido somente ap�s confirma��o da Nota Fiscal de Devolu��o.";
	echo "<br>";
	echo "TODAS as pe�as de �udio e V�deo devem retornar junto com esta Nota fiscal.";
	echo "<br>";
	echo "As pe�as das linhas de eletroport�teis e branca devem ficar no posto por 90 dias para inspe��o ou de acordo com os procedimentos definidos por seu DISTRIBUIDOR.";
	echo "<br>";

	echo "</td></tr></table>";

?>


<? if (strlen($msg) > 0) { 
	echo "<table class='Tabela' width='700' cellspacing='0'  cellpadding='0' align='center'>";
	echo "<tr >";
	echo "<td bgcolor='FFFFFF' width='60'><img src='imagens/proibido2.jpg' align='middle'></td><td  class='Erro' bgcolor='FFFFFF' align='left'> $msg</td>";
	echo "</tr>";
	echo "</table><br>";
} ?>


<?

if (strlen ($extrato) == 0) $extrato = trim($_GET['extrato']);
if (strlen ($somente_consulta) == 0) $somente_consulta = trim($_GET['somente_consulta']);

$sql  = "SELECT COUNT(*) FROM tbl_extrato_devolucao WHERE extrato = $extrato AND nota_fiscal IS NULL";
$resY = pg_exec ($con,$sql);
$qtde = pg_result ($resY,0,0);
if ($qtde > 0) {
	$sql  = "SELECT COUNT(*) FROM tbl_extrato_devolucao WHERE extrato = $extrato AND nota_fiscal IS NOT NULL";
	$resY = pg_exec ($con,$sql);
	$qtde = pg_result ($resY,0,0);
	if ($qtde > 0) {
		$sql = "DELETE FROM tbl_extrato_devolucao WHERE extrato = $extrato";
		$resY = pg_exec ($con,$sql);
	}
}



$sql = "SELECT  to_char (data_geracao,'DD/MM/YYYY') AS data ,
				to_char (data_geracao,'YYYY-MM-DD') AS periodo ,
				tbl_posto.nome ,
				tbl_posto_fabrica.codigo_posto
		FROM tbl_extrato
		JOIN tbl_posto ON tbl_extrato.posto = tbl_posto.posto
		JOIN tbl_posto_fabrica ON tbl_posto.posto = tbl_posto_fabrica.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
		WHERE tbl_extrato.extrato = $extrato ";
$res = pg_exec ($con,$sql);
$data = pg_result ($res,0,data);
$periodo = pg_result ($res,0,periodo);
$nome = pg_result ($res,0,nome);
$codigo = pg_result ($res,0,codigo_posto);

echo "<font size='+1' face='arial'>Data do Extrato $data </font>";
echo "<br>";
echo "<font size='+0' face='arial'>$codigo - $nome</font>";

?>

<p>
<table width='550' align='center' border='0' style='font-size:12px'>
<tr>
<?
if(strlen($somente_consulta)> 0){
	echo "<td align='center' width='33%'><a href='extrato_posto_mao_obra_consulta.php?extrato=$extrato&posto=$posto&somente_consulta=$somente_consulta'>Ver M�o-de-Obra</a></td>";
}else{
	echo "<td align='center' width='33%'><a href='extrato_posto_mao_obra.php?extrato=$extrato&posto=$posto'>Ver M�o-de-Obra</a></td>";
}


?>
<td align='center' width='33%'><a href='extrato_posto_britania.php?somente_consulta=sim'>Ver outro extrato</a></td>
</tr>
</table>


<p>

<?

	$array_nf_canceladas = array();
	$sql="SELECT	trim(nota_fiscal) as nota_fiscal,
					to_char(data_nf,'DD/MM/YYYY') as data_nf
			FROM tbl_lgr_cancelado
			WHERE	fabrica = $login_fabrica
			AND     posto   = $login_posto
			AND foi_cancelado IS TRUE";
	$res_nf_canceladas = pg_exec ($con,$sql);
	$qtde_notas_canceladas = pg_numrows ($res_nf_canceladas);
	if ($qtde_notas_canceladas>0){
		for($i=0;$i<$qtde_notas_canceladas;$i++) {
			$nf_cancelada = pg_result ($res_nf_canceladas,$i,nota_fiscal);
			$data_nf      = pg_result ($res_nf_canceladas,$i,data_nf);
			
			$sql2="SELECT faturamento
					FROM tbl_faturamento
					WHERE fabrica             = $login_fabrica
					AND distribuidor           = $login_posto
					AND extrato_devolucao      = $extrato
					AND posto                  = 13996
					AND LPAD(nota_fiscal::text,7,'0')  = LPAD(trim('$nf_cancelada')::text,7,'0')
					AND cancelada IS NOT NULL";
			$res_nota = pg_exec ($con,$sql2);
			$notasss = pg_numrows ($res_nota);
			if ($notasss>0){
				array_push($array_nf_canceladas,$nf_cancelada);
			}else{
				if ($extrato==156369){
					if ($nf_cancelada=="0027373" OR $nf_cancelada=="0027374"){
						continue;
					}
				}
				if ($extrato==165591){
					if ($nf_cancelada=="0027155"){
						continue;
					}
				}
				if ($login_posto==595 AND ($extrato == 165591 OR $extrato==156369)){
					array_push($array_nf_canceladas,"$nf_cancelada");
				}
				if ($login_posto==13951 AND $extrato==147564){
					array_push($array_nf_canceladas,"$nf_cancelada");
				}
				if ($login_posto==1537 AND $extrato==156705){
					array_push($array_nf_canceladas,"$nf_cancelada");
				}
			}
		}
	}
	if (count($array_nf_canceladas)>0){
		if (count($array_nf_canceladas)>1){
			echo "<h3 style='border:1px solid #F7CB48;background-color:#FCF2CD;color:black;font-size:12px;width:600px;text-align:center;padding:4px;'><b>As notas:</b><br>".implode(",<br>",$array_nf_canceladas)." <br>foram <b>canceladas</b> e dever�o ser preenchidas novamente! <br></h3>";
		}else{
			echo "<h3 style='border:1px solid #F7CB48;background-color:#FCF2CD;color:black;font-size:12px;width:600px;text-align:center;padding:4px;'><b>A nota</b> ".implode(", ",$array_nf_canceladas)." foi <b>cancelada</b> e dever� ser preenchida novamente! <br></h3>";
		}
	}

?>

<form name='frm_nota_fiscal' method='POST' action='<? echo $PHP_SELF ?>?'>
<input type='hidden' name='btn_acao' value='cancelar_notas'>
<input type='hidden' name='extrato' value='<? echo $extrato; ?>'>
<input type='hidden' name='posto' value='<? echo $posto; ?>'>
<? 

$sql = "SELECT * FROM tbl_posto WHERE posto = $login_posto";
$resX = pg_exec ($con,$sql);
$estado_origem = pg_result ($resX,0,estado);

$sql = "SELECT  faturamento,
		extrato_devolucao,
		nota_fiscal,
		distribuidor,
		posto
	FROM tbl_faturamento
	WHERE posto in (13996,4311)
	AND distribuidor      = $login_posto
	AND fabrica           = $login_fabrica
	AND extrato_devolucao = $extrato
	AND cancelada IS NULL
	ORDER BY faturamento ASC";
$res = pg_exec ($con,$sql);
$qtde_for=pg_numrows ($res);

if ($qtde_for > 0 OR 1==1) {

	$contador=0;
	for ($i=0; $i < $qtde_for; $i++) {
		
		$contador++;
		$faturamento_nota    = trim (pg_result ($res,$i,faturamento));
		$distribuidor        = trim (pg_result ($res,$i,distribuidor));
		$posto               = trim (pg_result ($res,$i,posto));
		$nota_fiscal         = trim (pg_result ($res,$i,nota_fiscal));
		$extrato_devolucao	 = trim (pg_result ($res,$i,extrato_devolucao));
		$distribuidor        = "";
		$produto_acabado     = "";

		$sql_topo = "SELECT  
					CASE WHEN tbl_peca.produto_acabado IS TRUE THEN 'TRUE' ELSE 'NOT TRUE' END AS produto_acabado,
					tbl_peca.devolucao_obrigatoria
				FROM tbl_faturamento
				JOIN tbl_faturamento_item USING(faturamento)
				JOIN tbl_peca USING(peca)
				WHERE tbl_faturamento.posto           = $posto
				AND tbl_faturamento.distribuidor      = $login_posto
				AND tbl_faturamento.fabrica           = $login_fabrica
				AND tbl_faturamento.extrato_devolucao = $extrato_devolucao
				AND tbl_faturamento.faturamento       = $faturamento_nota 
				LIMIT 1";
		$res_topo = pg_exec ($con,$sql_topo);
		$produto_acabado = pg_result ($res_topo,0,produto_acabado);
		$devolucao_obrigatoria = pg_result ($res_topo,0,devolucao_obrigatoria);

		$pecas_produtos = "PE�AS";
		$devolucao = " RETORNO OBRIGAT�RIO ";

		if ($posto=='4311'){
			$posto_desc = "Devolu��o para a TELECONTROL - ";
		}else{
			$posto_desc="";
		}

		if ($devolucao_obrigatoria=='f') $devolucao = " N�O RETORN�VEIS ";		
		if ($devolucao_obrigatoria=='f') $pecas_produtos = "$posto_desc PE�AS";

		if ($produto_acabado == "TRUE"){
			$pecas_produtos = "$posto_desc PRODUTOS";
			 $devolucao = " RETORNO OBRIGAT�RIO ";
		}

		if ($posto=='13996'){ #BRITANIA
				$razao    = "BRITANIA ELETRODOMESTICOS LTDA";
				$endereco = "Rua Dona Francisca, 8300 - Mod.4 e 5 - Bloco A";
				$cidade   = "Joinville";
				$estado   = "SC";
				$cep      = "89239270";
				$fone     = "(41) 2102-7700";
				$cnpj     = "76492701000742";
				$ie       = "254.861.652";
		}
		if ($posto=='4311'){ #TELECONTROL
				$razao    = "TELECONTROL NETWORKING LTDA";
				$endereco = "AV. CARLOS ARTENCIO 420 ";
				$cidade   = "Mar�lia";
				$estado   = "SP";
				$cep      = "17.519-255 ";
				$fone     = "(14) 3433-6588";
				$cnpj     = "04716427000141 ";
				$ie       = "438.200.748-116";
		}

		$cabecalho  = "";
		$cabecalho  = "<br><br>\n";
		$cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";

		$cabecalho .= "<tr align='left'  height='16'>\n";
		$cabecalho .= "<td bgcolor='#E3E4E6' colspan='3' style='font-size:18px'>\n";
		$cabecalho .= "<b>&nbsp;<b>$pecas_produtos - $devolucao </b><br>\n";
		$cabecalho .= "</td>\n";
		$cabecalho .= "</tr>\n";

		$cabecalho .= "<tr>\n";
		$cabecalho .= "<td>Natureza <br> <b>Devolu��o de Garantia</b> </td>\n";
		$cabecalho .= "<td>CFOP <br> <b>$cfop</b> </td>\n";
		$cabecalho .= "<td>Emissao <br> <b>$data</b> </td>\n";
		$cabecalho .= "</tr>\n";
		$cabecalho .= "</table>\n";

		$cnpj = substr ($cnpj,0,2) . "." . substr ($cnpj,2,3) . "." . substr ($cnpj,5,3) . "/" . substr ($cnpj,8,4) . "-" . substr ($cnpj,12,2);
		$cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
		$cabecalho .= "<tr>\n";
		$cabecalho .= "<td>Raz�o Social <br> <b>$razao</b> </td>\n";
		$cabecalho .= "<td>CNPJ <br> <b>$cnpj</b> </td>\n";
		$cabecalho .= "<td>Inscri��o Estadual <br> <b>$ie</b> </td>\n";
		$cabecalho .= "</tr>\n";
		$cabecalho .= "</table>\n";

		$cep = substr ($cep,0,2) . "." . substr ($cep,2,3) . "-" . substr ($cep,5,3) ;
		$cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
		$cabecalho .= "<tr>\n";
		$cabecalho .= "<td>Endere�o <br> <b>$endereco </b> </td>\n";
		$cabecalho .= "<td>Cidade <br> <b>$cidade</b> </td>\n";
		$cabecalho .= "<td>Estado <br> <b>$estado</b> </td>\n";
		$cabecalho .= "<td>CEP <br> <b>$cep</b> </td>\n";
		$cabecalho .= "</tr>\n";
		$cabecalho .= "</table>\n";

		$topo ="";
		$topo .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' id='tbl_pecas_$i'>\n";
		$topo .=  "<thead>\n";
		if ($numero_linhas==5000 AND  $jah_digitado==0){
//			$topo .=  "<tr align='left'>\n";
//			$topo .=  "<td bgcolor='#E3E4E6' colspan='4' style='font-size:18px'>\n";
//			$topo .=  "<b>&nbsp;<b>$pecas_produtos - $devolucao </b><br>\n";
//			$topo .=  "</td>\n";
//			$topo .=  "</tr>\n";
		}
		$topo .=  "<tr align='center'>\n";
		$topo .=  "<td><b>C�digo</b></td>\n";
		$topo .=  "<td><b>Descri��o</b></td>\n";
		$topo .=  "<td><b>Qtde.</b></td>\n";

			$topo .=  "<td><b>Pre�o</b></td>\n";
			$topo .=  "<td><b>Total</b></td>\n";
			$topo .=  "<td><b>% ICMS</b></td>\n";
			$topo .=  "<td><b>% IPI</b></td>\n";

		$topo .=  "</tr>\n";
		$topo .=  "</thead>\n";

		$sql = "SELECT  
				tbl_peca.peca, 
				tbl_peca.referencia, 
				tbl_peca.descricao, 
				tbl_peca.ipi, 
				CASE WHEN tbl_peca.produto_acabado IS TRUE THEN 'TRUE' ELSE 'NOT TRUE' END AS produto_acabado,
				tbl_peca.devolucao_obrigatoria,
				tbl_faturamento_item.aliq_icms,
				tbl_faturamento_item.aliq_ipi,
				tbl_faturamento_item.preco,
				SUM (tbl_faturamento_item.qtde) as qtde,
				SUM (tbl_faturamento_item.qtde * tbl_faturamento_item.preco) as total,
				SUM (tbl_faturamento_item.base_icms) AS base_icms, 
				SUM (tbl_faturamento_item.valor_icms) AS valor_icms,
				SUM (tbl_faturamento_item.base_ipi) AS base_ipi,
				SUM (tbl_faturamento_item.valor_ipi) AS valor_ipi
				FROM tbl_faturamento
				JOIN tbl_faturamento_item USING (faturamento)
				JOIN tbl_peca             USING (peca)
				WHERE tbl_faturamento.fabrica = $login_fabrica
					AND   tbl_faturamento.extrato_devolucao = $extrato
					AND   tbl_faturamento.faturamento=$faturamento_nota
					AND   tbl_faturamento.posto=$posto
					AND   tbl_faturamento.distribuidor=$login_posto
				GROUP BY
					tbl_peca.peca, 
					tbl_peca.referencia, 
					tbl_peca.descricao,
					tbl_peca.devolucao_obrigatoria, 
					tbl_peca.produto_acabado, 
					tbl_peca.ipi,
					tbl_faturamento_item.aliq_icms,
					tbl_faturamento_item.aliq_ipi,
					tbl_faturamento_item.preco
				ORDER BY tbl_peca.referencia";
	echo nl2br($sql);
		$resX = pg_exec ($con,$sql);

		$notas_fiscais=array();
		$qtde_peca=0;

		if (pg_numrows ($resX)==0) continue;

		echo $cabecalho;
		echo $topo;

		$total_base_icms  = 0;
		$total_valor_icms = 0;
		$total_base_ipi   = 0;
		$total_valor_ipi  = 0;
		$total_nota       = 0;
		$aliq_final       = 0;

		for ($x = 0 ; $x < pg_numrows ($resX) ; $x++) {
			
			$peca                = pg_result ($resX,$x,peca);
			$peca_referencia     = pg_result ($resX,$x,referencia);
			$peca_descricao      = pg_result ($resX,$x,descricao);
			$ipi                 = pg_result ($resX,$x,ipi);
			$peca_produto_acabado= pg_result ($resX,$x,produto_acabado);
			$peca_devolucao_obrigatoria = pg_result ($resX,$x,devolucao_obrigatoria);
			$aliq_icms           = pg_result ($resX,$x,aliq_icms);
			$aliq_ipi            = pg_result ($resX,$x,aliq_ipi);
			$peca_preco          = pg_result ($resX,$x,preco);

			$base_icms           = pg_result ($resX,$x,base_icms);
			$valor_icms          = pg_result ($resX,$x,valor_icms);
			$base_ipi            = pg_result ($resX,$x,base_ipi);
			$valor_ipi           = pg_result ($resX,$x,valor_ipi);

			$total               = pg_result ($resX,$x,total);
			$qtde                = pg_result ($resX,$x,qtde);


			if ($qtde==0)
				$peca_preco       =  $peca_preco;
			else
				$peca_preco       =  $total / $qtde;
			
			$total_item  = $peca_preco * $qtde;

//			$nota_fiscal_item = pg_result ($resX,$x,nota_fiscal);
//			$faturamento = pg_result ($resX,$x,faturamento);

			if (strlen ($aliq_icms)  == 0) $aliq_icms = 0;

			if ($aliq_icms==0){
				$base_icms=0;
				$valor_icms=0;
			}
			else{
				$base_icms  = $total_item;
				$valor_icms = $total_item * $aliq_icms / 100;
			}

			if (strlen($aliq_ipi)==0) $aliq_ipi=0;

			if ($aliq_ipi==0) 	{
				$base_ipi=0;
				$valor_ipi=0;
			}
			else {
				$base_ipi=$total_item;
				$valor_ipi = $total_item*$aliq_ipi/100;
			}

//			if ($base_icms > $total_item) $base_icms = $total_item;
//			if ($aliq_final == 0) $aliq_final = $aliq_icms;
//			if ($aliq_final <> $aliq_icms) $aliq_final = -1;

			$total_base_icms  += $base_icms;
			$total_valor_icms += $valor_icms;
			$total_base_ipi   += $base_ipi;
			$total_valor_ipi  += $valor_ipi;
			$total_nota       += $total_item;

			echo "<tr bgcolor='#ffffff' style='font-color:#000000 ; align:left ; font-size:10px ' >\n";
			echo "<td align='left'>";
			echo "$peca_referencia";
			echo "</td>\n";
			echo "<td align='left'>$peca_descricao</td>\n";

			echo "<td align='center'>$qtde</td>\n";
			echo "<td align='right' nowrap>" . number_format ($peca_preco,2,",",".") . "</td>\n";
			echo "<td align='right' nowrap>" . number_format ($total_item,2,",",".") . "</td>\n";
			echo "<td align='right'>$aliq_icms</td>\n";
			echo "<td align='right'>$aliq_ipi</td>\n";

			echo "</tr>\n";
			flush();
		}

		$sql_nf = " SELECT tbl_faturamento_item.nota_fiscal_origem
					FROM tbl_faturamento_item 
					JOIN tbl_faturamento      USING (faturamento)
					WHERE tbl_faturamento.fabrica           = $login_fabrica
					AND   tbl_faturamento.distribuidor      = $login_posto
					AND   tbl_faturamento.posto             = $posto
					AND   tbl_faturamento.extrato_devolucao = $extrato
					ORDER BY tbl_faturamento.nota_fiscal";
		$resNF = pg_exec ($con,$sql_nf);
		for ($y = 0 ; $y < pg_numrows ($resNF) ; $y++) {
			array_push($notas_fiscais,pg_result ($resNF,$y,nota_fiscal_origem));
		}
		$notas_fiscais = array_unique($notas_fiscais);
		#asort($notas_fiscais);

		if (count($notas_fiscais)>0){
			echo "<tfoot>";
			echo "<tr>";
			echo "<td colspan='8'> Referente a suas NFs. " . implode(", ",$notas_fiscais) . "</td>";
			echo "</tr>";
			echo "</tfoot>";
		}

		echo "</table>\n";


		echo "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >";
		echo "<tr>";
		echo "<td>Base ICMS <br> <b> " . number_format ($total_base_icms,2,",",".") . " </b> </td>";
		echo "<td>Valor ICMS <br> <b> " . number_format ($total_valor_icms,2,",",".") . " </b> </td>";
		echo "<td>Base IPI <br> <b> " . number_format ($total_base_ipi,2,",",".") . " </b> </td>";
		echo "<td>Valor IPI <br> <b> " . number_format ($total_valor_ipi,2,",",".") . " </b> </td>";
		echo "<td>Total da Nota <br> <b> " . number_format ($total_nota+$total_valor_ipi,2,",",".") . " </b> </td>";
		echo "</tr>";
		echo "</table>";

		echo "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
		echo "<tr>\n";
		echo "<td><h1><center>Nota de Devolu��o $nota_fiscal</center></h1></td>\n";
		echo "</tr>";
		echo "</table>";
	
		$total_base_icms  = 0;
		$total_valor_icms = 0;
		$total_base_ipi   = 0;
		$total_valor_ipi  = 0;
		$total_nota       = 0;

	}

################################################
## PE�AS RETORNAVEIS DA TELECONTROL
################################################

if ($posto<>"4311" AND 1==2){
			$sql = "SELECT  tbl_faturamento.faturamento, 
							tbl_peca.peca, 
							tbl_peca.referencia, 
							tbl_peca.descricao, 
							tbl_peca.ipi, 
							tbl_faturamento_item.aliq_icms,
							tbl_faturamento_item.aliq_ipi,
							SUM (tbl_faturamento_item.qtde) AS qtde, 
							SUM (tbl_faturamento_item.qtde * tbl_faturamento_item.preco ) AS total_item, 
							SUM (tbl_faturamento_item.base_icms) AS base_icms, 
							SUM (tbl_faturamento_item.valor_icms) AS valor_icms,
							SUM (tbl_faturamento_item.valor_ipi) AS valor_ipi,
							SUM (tbl_faturamento_item.base_ipi) AS base_ipi
					FROM tbl_peca
					JOIN tbl_faturamento_item USING (peca)
					JOIN tbl_faturamento      USING (faturamento)
					WHERE tbl_faturamento.fabrica = $login_fabrica
					AND   tbl_faturamento.posto   = $login_posto
					AND   tbl_faturamento.extrato_devolucao = $extrato
					AND   (tbl_faturamento.cfop ILIKE '59%' OR tbl_faturamento.cfop ILIKE '69%')
					AND   tbl_faturamento.distribuidor=4311
					AND   tbl_faturamento_item.aliq_icms > 0
					AND   tbl_faturamento.emissao > '2005-10-01'
					GROUP BY tbl_faturamento.faturamento, tbl_peca.peca, tbl_peca.referencia, tbl_peca.descricao, tbl_peca.ipi, tbl_faturamento_item.aliq_icms, tbl_faturamento_item.aliq_ipi
					ORDER BY tbl_peca.referencia ";

			$resX = pg_exec ($con,$sql);
			$total_base_icms  = 0;
			$total_valor_icms = 0;
			$total_base_ipi   = 0;
			$total_valor_ipi  = 0;
			$total_nota       = 0;
			$aliq_final       = 0;

			$distribuidor=4311;
			$notas_fiscais=0;

			if ( pg_numrows ($resX)>0){

				if (strlen ($distribuidor) > 0) {
					$sql_2  = "SELECT * FROM tbl_posto WHERE posto = $distribuidor";
					$resY = pg_exec ($con,$sql_2);

					$estado   = pg_result ($resY,0,estado);
					$razao    = pg_result ($resY,0,nome);
					$endereco = trim (pg_result ($resY,0,endereco)) . " " . trim (pg_result ($resY,0,numero));
					$cidade   = pg_result ($resY,0,cidade);
					$estado   = pg_result ($resY,0,estado);
					$cep      = pg_result ($resY,0,cep);
					$fone     = pg_result ($resY,0,fone);
					$cnpj     = pg_result ($resY,0,cnpj);
					$ie       = pg_result ($resY,0,ie);

					$condicao_1 = " tbl_faturamento.distribuidor = $distribuidor ";
					$condicao_2 = " tbl_peca.produto_acabado IS $produto_acabado ";
				}

				$cabecalho  = "<br><br>\n";
				$cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";

				$cabecalho .= "<tr align='left'  height='16'>\n";
				$cabecalho .= "<td bgcolor='#E3E4E6' colspan='3' style='font-size:18px'>\n";
				$cabecalho .= "<b>DEVOLU��O TELECONTROL&nbsp;.</b><br>\n";
				$cabecalho .= "</td>\n";
				$cabecalho .= "</tr>\n";

				$cabecalho .= "<tr>\n";
				$cabecalho .= "<td>Natureza <br> <b>Devolu��o de Garantia</b> </td>\n";
				$cabecalho .= "<td>CFOP <br> <b>$cfop</b> </td>\n";
				$cabecalho .= "<td>Emissao <br> <b>$data</b> </td>\n";
				$cabecalho .= "</tr>\n";
				$cabecalho .= "</table>\n";

				$cnpj = substr ($cnpj,0,2) . "." . substr ($cnpj,2,3) . "." . substr ($cnpj,5,3) . "/" . substr ($cnpj,8,4) . "-" . substr ($cnpj,12,2);
				$cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
				$cabecalho .= "<tr>\n";
				$cabecalho .= "<td>Raz�o Social <br> <b>$razao</b> </td>\n";
				$cabecalho .= "<td>CNPJ <br> <b>$cnpj</b> </td>\n";
				$cabecalho .= "<td>Inscri��o Estadual <br> <b>$ie</b> </td>\n";
				$cabecalho .= "</tr>\n";
				$cabecalho .= "</table>\n";

				$cep = substr ($cep,0,2) . "." . substr ($cep,2,3) . "-" . substr ($cep,5,3) ;
				$cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
				$cabecalho .= "<tr>\n";
				$cabecalho .= "<td>Endere�o <br> <b>$endereco </b> </td>\n";
				$cabecalho .= "<td>Cidade <br> <b>$cidade</b> </td>\n";
				$cabecalho .= "<td>Estado <br> <b>$estado</b> </td>\n";
				$cabecalho .= "<td>CEP <br> <b>$cep</b> </td>\n";
				$cabecalho .= "</tr>\n";
				$cabecalho .= "</table>\n";

				$topo ="";
				$topo .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' id='tbl_pecas_$i'>\n";
				$topo .=  "<thead>\n";

				$topo .=  "<tr align='center'>\n";
				$topo .=  "<td><b>C�digo</b></td>\n";
				$topo .=  "<td><b>Descri��o</b></td>\n";
				$topo .=  "<td><b>Qtde.</b></td>\n";
				$topo .=  "<td><b>Pre�o</b></td>\n";
				$topo .=  "<td><b>Total</b></td>\n";
				$topo .=  "<td><b>% ICMS</b></td>\n";
				$topo .=  "<td><b>% IPI</b></td>\n";
				$topo .=  "</tr>\n";
				$topo .=  "</thead>\n";

				echo $cabecalho;
				echo $topo;

				for ($x = 0 ; $x < pg_numrows ($resX) ; $x++) {
					
					$peca        = pg_result ($resX,$x,peca);
					$qtde        = pg_result ($resX,$x,qtde);
					$total_item  = pg_result ($resX,$x,total_item);
					$base_icms   = pg_result ($resX,$x,base_icms);
					$valor_icms  = pg_result ($resX,$x,valor_icms);
					$aliq_icms   = pg_result ($resX,$x,aliq_icms);
					$base_ipi   = pg_result ($resX,$x,base_ipi);
					$aliq_ipi   = pg_result ($resX,$x,aliq_ipi);
					$valor_ipi   = pg_result ($resX,$x,valor_ipi);
					$ipi = pg_result ($resX,$x,ipi);
					$preco       = round ($total_item / $qtde,2);
					$total_item  = $preco * $qtde;
					$faturamento = pg_result ($resX,$x,faturamento);

					if (strlen ($base_icms)  == 0) $base_icms = $total_item ;
					if (strlen ($valor_icms) == 0) $valor_icms = round ($total_item * $aliq_icms / 100,2);


					if (strlen($aliq_ipi)==0) $aliq_ipi=0;
					if ($aliq_ipi==0) 	{
						$base_ipi=0;
						$valor_ipi=0;
					}
					else {
						$base_ipi=$total_item;
						$valor_ipi = $total_item*$aliq_ipi/100;
					}
					
					if ($base_icms > $total_item) $base_icms = $total_item;
					if ($aliq_final == 0) $aliq_final = $aliq_icms;
					if ($aliq_final <> $aliq_icms) $aliq_final = -1;

					echo "<tr bgcolor='#ffffff' style='font-color:#000000 ; align:left ; font-size:10px ' >";
					echo "<td align='left'>" . pg_result ($resX,$x,referencia) . "</td>";
					echo "<td align='left'>" . pg_result ($resX,$x,descricao) . "</td>";
					echo "<td align='right'>" . pg_result ($resX,$x,qtde) . "</td>";
					echo "<td align='right' nowrap>" . number_format ($preco,2,",",".") . "</td>";
					echo "<td align='right' nowrap>" . number_format ($total_item,2,",",".") . "</td>";
					echo "<td align='right'>" . $aliq_icms . "</td>";
					echo "<td align='right'>" . $aliq_ipi. "</td>";
					echo "</tr>";

					$total_base_icms  += $base_icms;
					$total_valor_icms += $valor_icms;
					$total_base_ipi  += $base_ipi;
					$total_valor_ipi += $valor_ipi;
					$total_nota       += $total_item;
				}

				$sql_nf = "SELECT DISTINCT tbl_faturamento.nota_fiscal
						FROM tbl_faturamento_item 
						JOIN tbl_faturamento      USING (faturamento)
						JOIN tbl_peca ON tbl_faturamento_item.peca = tbl_peca.peca
						WHERE tbl_faturamento.fabrica = $login_fabrica
						AND   tbl_faturamento.posto   = $login_posto
						AND   (tbl_faturamento.cfop ILIKE '59%' OR tbl_faturamento.cfop ILIKE '69%')
						AND   tbl_faturamento.extrato_devolucao = $extrato
						AND   tbl_faturamento.distribuidor=4311
						AND   tbl_faturamento_item.aliq_icms > 0
						AND   tbl_faturamento.emissao > '2005-10-01'
						ORDER BY tbl_faturamento.nota_fiscal ";
				$resZ = pg_exec ($con,$sql_nf);
				$notas_fiscais    = array();
				for ($x = 0 ; $x < pg_numrows ($resZ) ; $x++) {
					array_push($notas_fiscais,pg_result ($resZ,$x,nota_fiscal));
				}
				if (count($notas_fiscais)>0){
					echo "<tfoot>";
					echo "<tr>";
					echo "<td colspan='8'> Referente a suas NFs. " . implode(", ",$notas_fiscais) . "</td>";
					echo "</tr>";
					echo "</tfoot>";
				}			
				//$total_valor_icms = $total_base_icms * $aliq_final / 100;
				$total_geral=$total_nota+$total_valor_ipi;
				echo "</table>\n";
				echo "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
				echo "<tr>\n";
				echo "<td>Base ICMS <br> <b> " . number_format ($total_base_icms,2,",",".") . " </b> </td>\n";
				echo "<td>Valor ICMS <br> <b> " . number_format ($total_valor_icms,2,",",".") . " </b> </td>\n";
				echo "<td>Base IPI <br> <b> " . number_format ($total_base_ipi,2,",",".") . " </b> </td>\n";
				echo "<td>Valor IPI <br> <b> " . number_format ($total_valor_ipi,2,",",".") . " </b> </td>\n";
				echo "<td>Total da Nota <br> <b> " . number_format ($total_geral,2,",",".") . " </b> </td>\n";
				echo "</tr>\n";

				echo "</table>\n";
				if (strlen ($nota_fiscal)==0 AND 1==2) {
					echo "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >";
					echo "<tr>";
					echo "<td>";
					echo "\n<br>";
//						echo "<input type='hidden' name='id_nota_$numero_nota-linha' value='$linha'>\n";
					echo "<center>";
					echo "<b>Preencha este Nota de Devolu��o e informe o n�mero da Nota Fiscal</b><br>Este n�mero n�o poder� ser alterado<br>";
					echo "<br><IMG SRC='imagens/setona_h.gif' WIDTH='53' HEIGHT='29' BORDER='0' align='absmiddle'>N�mero da Nota: <input type='text' name='nota_fiscal_$numero_nota' size='10' maxlength='6' value='$nota_fiscal'>";
					echo "<br><br>";
					echo "</td>";
					echo "</tr>";
					echo "</table>";
					$numero_nota++;
				}else{
					if (strlen ($nota_fiscal) >0 AND 1==2){
						echo "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
						echo "<tr>\n";
						echo "<td><h1><center>Nota de Devolu��o $nota_fiscal</center></h1></td>\n";
						echo "</tr>";
						echo "</table>";
					}
				}
			}
}
########################################################
### PE�AS COM RESSARCIMENTO
########################################################


	$sql = "SELECT  tbl_os.os                                                         ,
			tbl_os.sua_os                                                     ,
			TO_CHAR(tbl_os.data_fechamento,'DD/MM/YYYY') AS data_ressarcimento,
			tbl_produto.referencia                       AS produto_referencia,
			tbl_produto.descricao                        AS produto_descricao ,
			tbl_admin.login
		FROM tbl_os
		JOIN tbl_os_produto USING (os)
		JOIN tbl_os_item    USING(os_produto)
		JOIN tbl_os_extra   USING(os)
		LEFT JOIN tbl_admin      ON tbl_os.troca_garantia_admin   = tbl_admin.admin
		LEFT JOIN tbl_produto    ON tbl_os.produto = tbl_produto.produto
		WHERE tbl_os_extra.extrato = $extrato
		AND  tbl_os.fabrica        = $login_fabrica
		AND  tbl_os.posto          = $login_posto
		AND  tbl_os.ressarcimento  IS TRUE
		AND  tbl_os.troca_garantia IS TRUE";

	$sql = "SELECT  
				tbl_os.os                                                         ,
				tbl_os.sua_os                                                     ,
				TO_CHAR(tbl_os.data_fechamento,'DD/MM/YYYY') AS data_ressarcimento,
				tbl_produto.referencia                       AS produto_referencia,
				tbl_produto.descricao                        AS produto_descricao ,
				tbl_admin.login
			FROM ( SELECT os FROM tbl_os_extra WHERE extrato = $extrato ) x
			JOIN  tbl_os             ON x.os           = tbl_os.os
			JOIN tbl_os_produto      ON tbl_os.os      = tbl_os_produto.os
			LEFT JOIN tbl_admin      ON tbl_os.troca_garantia_admin   = tbl_admin.admin
			LEFT JOIN tbl_produto    ON tbl_os.produto = tbl_produto.produto
			WHERE tbl_os.fabrica        = $login_fabrica
			AND   tbl_os.posto          = $login_posto
			AND   tbl_os.ressarcimento  IS TRUE
			AND   tbl_os.troca_garantia IS TRUE
			";

	if (strlen($nota_fiscal)>0){

		$resX = pg_exec ($con,$sql);

		if(pg_numrows($resX)>0){

			echo "<br><table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >";

			echo "<tr align='left'  height='16'>\n";
			echo "<td bgcolor='#E3E4E6' colspan='3' style='font-size:18px'>\n";
			echo "<b>&nbsp;<b>PE�AS COM RESSARCIMENTO - DEVOLU��O OBRIGAT�RIA </b><br>\n";
			echo "</td>\n";
			echo "</tr>\n";

			echo "<tr>";
			echo "<td>Natureza <br> <b>Simples Remessa</b> </td>";
			echo "<td>CFOP <br> <b>$cfop</b> </td>";
			echo "<td>Emissao <br> <b>$data</b> </td>";
			echo "</tr>";
			echo "</table>";
		
			echo "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >";
			echo "<tr>";
			echo "<td>Raz�o Social <br> <b>$razao</b> </td>";
			echo "<td>CNPJ <br> <b>$cnpj</b> </td>";
			echo "<td>Inscri��o Estadual <br> <b>$ie</b> </td>";
			echo "</tr>";
			echo "</table>";
		
		
			echo "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >";
			echo "<tr>";
			echo "<td>Endere�o <br> <b>$endereco </b> </td>";
			echo "<td>Cidade <br> <b>$cidade</b> </td>";
			echo "<td>Estado <br> <b>$estado</b> </td>";
			echo "<td>CEP <br> <b>$cep</b> </td>";
			echo "</tr>";
			echo "</table>";

			echo "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >";
			echo "<tr align='center'>";
			echo "<td><b>C�digo</b></td>";
			echo "<td><b>Descri��o</b></td>";
			echo "<td><b>Ressarcimento</b></td>";
			echo "<td><b>Responsavel</b></td>";
			echo "<td><b>OS</b></td>";
			echo "</tr>";
		
			for ($x = 0 ; $x < pg_numrows ($resX) ; $x++) {
		
				$sua_os             = pg_result ($resX,$x,sua_os);
				$produto_referencia = pg_result ($resX,$x,produto_referencia);
				$produto_descricao  = pg_result ($resX,$x,produto_descricao);
				$data_ressarcimento = pg_result ($resX,$x,data_ressarcimento);
				$quem_trocou        = pg_result ($resX,$x,login);
		
				echo "<tr bgcolor='#ffffff' style='font-color:#000000 ; align:left ; font-size:10px ' >";
				echo "<td align='left'>$produto_referencia</td>";
				echo "<td align='left'>$produto_descricao</td>";
				echo "<td align='left'>$data_ressarcimento</td>";
				echo "<td align='right'>$quem_trocou</td>";
				echo "<td align='right'>$sua_os</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
	}

	//echo "<input type='hidden' name='qtde_notas' value='$contador'>";
	//echo "<br><br><center><input type='button' value='Cancelar Todas as Notas' onclick=\"javascript: if ('Deseja cancelar todas as notas deste extrato? Ap�s este procedimento, as notas canceladas n�o poder�o ser recuperadas!')this.form.submit();\"></center>";
	echo "</form>";
}else{

	echo "<h1>Posto autorizado ainda n�o preencheu as notas de devolu��o.<br>Para consultar as notas, logue como Este Posto e acesse seu extrato.</h1>";
	//$res = pg_exec ($con,$sql);

}
?>
<p><p>

<? include "rodape.php"; ?>
