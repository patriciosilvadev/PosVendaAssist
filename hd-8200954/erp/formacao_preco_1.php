<?
include '../dbconfig.php';
include '../includes/dbconnect-inc.php';
include 'autentica_usuario_empresa.php';
include 'menu.php';



$sql = "SELECT * 
		FROM tbl_loja_dados 
		WHERE empresa = $login_empresa
		AND   loja    = $login_loja";

$res = pg_exec($sql);

if(@pg_numrows($res)==1){
	$empresa                  = pg_result($res,0,empresa);
	$despesas_administrativas = pg_result($res,0,despesas_administrativas);
	$marketing                = pg_result($res,0,marketing);
	$perdas                   = pg_result($res,0,perdas);
	$comissao_venda           = pg_result($res,0,comissao_venda);
	$regime_tributario        = pg_result($res,0,regime_tributario);
	$simples_federal          = pg_result($res,0,simples_federal);
	$simples_estadual         = pg_result($res,0,simples_estadual);
	$pis                      = pg_result($res,0,pis);
	$cofins                   = pg_result($res,0,cofins);
	$irpj                     = pg_result($res,0,irpj);
	$contribuicao_social      = pg_result($res,0,contribuicao_social);
	$iss                      = pg_result($res,0,iss);
}else{
	echo "Op��o n�o dispon�vel, por favor preencha os dados da empresa!";
}


$btn_acao  = $_POST['btn_acao'];
if(strlen($btn_acao)>0){
	$linha     = $_POST['linha'];
	$tabelas         = $_POST['coluna'];
	$qtde_peca = $_POST['qtde_peca'];
//	echo "pegar linha $linha";
//	echo "<BR>qtdade tabelas $tabelas";
	$cotacao_item = $_POST['cotacao_item_'.$linha];
//	echo "<BR>cotacao: $cotacao_item";
	$sql = " SELECT peca 
			FROM tbl_cotacao_item 
			WHERE cotacao_item = $cotacao_item 
			AND tbl_cotacao_item.formacao_preco IS NULL";
	$res = pg_exec($con,$sql);
	if(pg_numrows($res)>0){
		$peca = pg_result($res,0,0);
		
		$res = pg_exec ($con,"BEGIN TRANSACTION");

		for($y=0; $y < $tabelas; $y++){
			$cod_tabela                = $_POST['tabela_'                   .$linha.'_'.$y];
			$percentual_administrativo = $_POST['percentual_administrativo_'.$linha.'_'.$y];
			$percentual_comissao       = $_POST['percentual_comissao_'      .$linha.'_'.$y];
			$percentual_marketing      = $_POST['percentual_marketing_'     .$linha.'_'.$y];
			$percentual_perdas         = $_POST['percentual_perdas_'        .$linha.'_'.$y];
			$percentual_lucro          = $_POST['percentual_lucro_'         .$linha.'_'.$y];
			$preco_sugerido            = $_POST['preco_sugerido_'           .$linha.'_'.$y];

			if(strlen($percentual_administrativo)==0)$percentual_administrativo = 0;
			if(strlen($percentual_comissao)      ==0)$percentual_comissao       = 0;
			if(strlen($percentual_marketing)     ==0)$percentual_marketing      = 0;
			if(strlen($percentual_perdas)        ==0)$percentual_perdas         = 0;
			if(strlen($percentual_lucro)         ==0)$percentual_lucro          = 0;
			if(strlen($preco_sugerido)           ==0)$preco_sugerido            = 0;

			$sql = "SELECT	tabela_item_erp,
							tabela, preco, 
							peca, 
							data_vigencia  , 
							termino_vigencia ,
							percentual_marketing, 
							percentual_lucro ,
							percentual_vendas, 
							percentual_administrativo,
							percentual_comissao,
							percentual_perdas
					FROM tbl_tabela_item_erp 
					WHERE tabela = $cod_tabela
					AND peca = $peca
					AND termino_vigencia is null
					ORDER BY tabela_item_erp desc
					LIMIT 1;";
			$res = pg_exec($con,$sql);
			echo $sql;
			$msg_erro .= pg_errormessage($con);    
			if(strlen($msg_erro)==0){
				if(pg_num_rows($res)>0){
					$tabela_item_erp = pg_result($res,0,tabela_item_erp);
					$sql = "UPDATE tbl_tabela_item_erp set termino_vigencia = current_timestamp
							where tabela = $cod_tabela
							AND peca = $peca
							AND tabela_item_erp = $tabela_item_erp";
//					$res = pg_exec($con,$sql);
					echo $sql;
					$msg_erro .= pg_errormessage($con);    
				}
			}
			if(strlen($msg_erro)==0){
				$sql = "INSERT INTO tbl_tabela_item_erp(
									tabela                  , 
									preco                   , 
									peca                    , 
									data_vigencia           , 
									percentual_marketing    , 
									percentual_lucro        ,
									percentual_administrativo,
									percentual_comissao     ,
									percentual_perdas
								)values(
									$cod_tabela,
									$preco_sugerido,
									$peca,
									current_timestamp,
									$percentual_marketing,
									$percentual_lucro,
									$percentual_administrativo,
									$percentual_comissao      , 
									$percentual_perdas        
								)";
//				$res = pg_exec($con,$sql);
				echo $sql;
				$msg_erro .= pg_errormessage($con);    
			}
			
		/*	echo "<BR>tabela: $cod_tabela";
			echo " adm: $percentual_administrativo  - ";
			echo " comissao: $percentual_comissao   - ";
			echo " mkt: $percentual_marketing       - ";
			echo " perda: $percentual_perdas        - ";
			echo " lucro: $percentual_lucro         - ";
			echo " sugerido: $preco_sugerido        - ";
*/

		}
		if(strlen($msg_erro)==0){//atualiza cotacao
			$sql = "UPDATE tbl_cotacao_item set formacao_preco = current_timestamp
					where cotacao_item = $cotacao_item 
					AND tbl_cotacao_item.formacao_preco IS NULL";	
//			$res = pg_exec($con,$sql);
			echo $sql;
			$msg_erro .= pg_errormessage($con);
			$msg_erro = "1";
		}
		if(strlen($msg_erro)==0){
			$res = pg_exec ($con,"COMMIT TRANSACTION");
		}else{
			$res = pg_exec ($con,"ROLLBACK TRANSACTION");
		}

	}
/*tabela_$i_$x
percentual_administrativo_$i_$x*/
}


?>
<script language='javascript'>
function checarNumero(campo){
	var num = campo.value.replace(",",".");
	campo.value = parseFloat(num).toFixed(2);
	if (campo.value=='NaN') {
		campo.value='';
	}
}
function recalcular(valor1,valor2,c,valor_porcentagem){

	var total1 = parseFloat(document.getElementById(valor1).value);
	var total2 = parseFloat(document.getElementById(valor2).value);
	var coefic = parseFloat(document.getElementById(c).value);

	valor_total_geral = ( ( (parseFloat(total1) * parseFloat(coefic)) - parseFloat(total2) ) / parseFloat(total2) ) * 100;

	if (valor_total_geral=='NaN') {
		valor_total_geral =0;
	}else{
		valor_total_geral = parseFloat(valor_total_geral).toFixed(2);
	}

	document.getElementById(valor_porcentagem).value =  Math.round(valor_total_geral,2);

}

function porcentagem(ipi,icms,custo_real,percentual_administrativo,percentual_comissao,percentual_marketing,percentual_perdas,percentual_lucro,/*percentual_vendas,*/preco){

	var xipi                       = parseFloat(document.getElementById(ipi).value);
	var xicms                      = parseFloat(document.getElementById(icms).value);
	var xpercentual_administrativo = parseFloat(document.getElementById(percentual_administrativo).value);
	var xpercentual_comissao       = parseFloat(document.getElementById(percentual_comissao).value);
	var xpercentual_marketing      = parseFloat(document.getElementById(percentual_marketing).value);
	var xpercentual_perdas         = parseFloat(document.getElementById(percentual_perdas).value);
	var xpercentual_lucro          = parseFloat(document.getElementById(percentual_lucro).value);
/*	var xpercentual_vendas         = parseFloat(document.getElementById(percentual_vendas).value);*/
	var xpreco                     = parseFloat(document.getElementById(preco).value);
	var xcusto_real                = parseFloat(document.getElementById(custo_real).value);

	var xpis                       = parseFloat(document.getElementById('pis').value);
	var xcofins                    = parseFloat(document.getElementById('cofins').value);
	var xirpj                      = parseFloat(document.getElementById('irpj').value);
	var xcontribuicao_social       = parseFloat(document.getElementById('contribuicao_social').value);

	if(xipi                  =='NaN')      xipi   = 0;
	if(xicms                 =='NaN')      xicms  = 0;
	if(xpercentual_administrativo =='NaN') xpercentual_administrativo = 0;
	if(xpercentual_comissao  =='NaN')      xpercentual_comissao  = 0;
	if(xpercentual_marketing =='NaN')      xpercentual_marketing = 0;
	if(xpercentual_perdas    =='NaN')      xpercentual_perdas    = 0;
	if(xpercentual_lucro     =='NaN')      xpercentual_lucro     = 0;
/*	if(xpercentual_vendas    =='NaN')      xpercentual_vendas    = 0;*/
	if(xpreco                =='NaN')      xpreco                = 0;
	if(xcusto_real           =='NaN')      xcusto_real           = 0;

	if(xpis                   =='NaN')      xpis                 = 0;
	if(xcofins                =='NaN')      xcofins              = 0;
	if(xirpj                  =='NaN')      xirpj                = 0;
	if(xcontribuicao_social   =='NaN')      xcontribuicao_social = 0;

	vpercentual_administrativo = xcusto_real * (xpercentual_administrativo/100);
	vpercentual_comissao       = xcusto_real * (xpercentual_comissao      /100);
	vpercentual_marketing      = xcusto_real * (xpercentual_marketing     /100);
	vpercentual_perdas         = xcusto_real * (xpercentual_perdas        /100);
/*	vpercentual_vendas         = xcusto_real * (xpercentual_vendas        /100);*/
	vpercentual_lucro          = xcusto_real * (xpercentual_lucro         /100);
	margem_contribuicao  = xcusto_real + vpercentual_administrativo + vpercentual_comissao + vpercentual_marketing + vpercentual_perdas + vpercentual_lucro;

	vicms_venda          = margem_contribuicao * (xicms/100);
	vpis                 = margem_contribuicao * (xpis/100);
	vcofins              = margem_contribuicao * (xcofins/100);
	virpj                = margem_contribuicao * (xirpj/100);
	vcontribuicao_social = margem_contribuicao * (xcontribuicao_social/100);
	somatorio            = margem_contribuicao + vicms_venda + vpis + vcofins + virpj + vcontribuicao_social;

	vinversa  =  100 - (xicms + xpis + xcofins + xirpj + xcontribuicao_social);
	vsugerido = margem_contribuicao / (vinversa/100);


	document.getElementById(preco).value = parseFloat(vsugerido).toFixed(3);
/*	document.getElementById('x'+ preco).value = parseFloat(vsugerido).toFixed(3);*/

}
function porcentagem_negativa(percentual_lucro,preco_sugerido){
	var xpercentual_lucro     = parseFloat(document.getElementById(percentual_lucro).value);
	var xpreco_sugerido       = parseFloat(document.getElementById(preco_sugerido).value);
	/*var xpreco_sugerido_anterior= parseFloat(document.getElementById('x'+preco_sugerido).value);

	x = (xpreco_sugerido / xpreco_sugerido_anterior)*100;*/

}

</script>
<style>

.Label{
	font-family: Verdana;
	font-size: 10px;
}
.tabela{
	font-family: Verdana;
	font-size: 12px;
	
}
.Titulo_Tabela{
	font-family: Verdana;
	font-size: 12px;
	font-weight: bold;
	color:#FFF;
}
.Titulo_Colunas{
	font-family: Verdana;
	font-size: 12px;
	font-weight: bold;
}



caption{
	BACKGROUND-COLOR: #FFF;
	font-size:12px;
	font-weight:bold;
	text-align:center;
}

</style>

<table style=' border:#485989 1px solid; background-color: #e6eef7' align='center' width='400' border='0' class='tabela'>
	<tr height='20' bgcolor='#7392BF'>
		<td class='Titulo_Tabela' align='center' colspan='6'>FORMA��O DE PRE�O</td>
	</tr>
	<tr height='10'>
		<td  align='center' colspan='6'></td>
	</tr>
	<tr>
	<td class='Label'>
	<div id="container-Principal">
		<div id="tab1Procurar">
			<form name="frm_procura" method="post" action="<? echo $PHP_SELF ?>">
			<table align='left' width='100%' border='0' class='tabela' cellpadding='2' cellspacing='5' >
					<tr>
						<td width='10px'>&nbsp;</td>
						<td class='Label' align='center'>Data Inicial</td>
						<td align='left'  align='center' width='10px'>
						<input class="Caixa" type="text" name="data_inicial" size="10" maxlength="10" value="<? echo $data_inicial; ?>" >
						<? 	echo "<img src='../imagens/btn_lupa_novo.gif' border='0' align='absmiddle' onclick='javascript: fnc_pesquisa_peca_empresa (document.frm_os.marca, document.frm_os.add_referencia, document.frm_os.add_peca_descricao , document.frm_os.add_preco, document.frm_os.peca_estoque,document.frm_os.peca_qtde_entrega,document.frm_os.peca_data_previsao, \"descricao\" )' alt='Clique para efetuar a pesquisa' style='cursor:pointer;'>\n"; ?>
						</td>
						<td class='Label' align='center'>Data Final</td>
						<td align='left'><input class="Caixa" type="text" name="data_final" size="10" maxlength="10" value="<? echo $data_final; ?>"></td>
						<td width='10px'>&nbsp;</td>
					</tr>
					<tr><td width='10px'>&nbsp;</td>

						<td class='Label' align='center' colspan='4'>Fam�lia

						<select name='familia'>
						<option value=''></option>
						<?	
							$sql = "SELECT familia,descricao
									FROM tbl_familia
									WHERE fabrica = $login_empresa
									ORDER BY descricao ASC";
							$res = pg_exec($con,$sql);
							if(pg_numrows($res)>0){
								for($i=0;pg_numrows($res)>$i;$i++){
									$familia = pg_result($res,$i,familia);
									$descricao = pg_result($res,$i,descricao);
									echo "<option value='$familia'>$descricao</option>";
								}
							}
	
						?>
						</select>
						</td>
						<td width='10px'>&nbsp;</td>
					</tr>
				<tr>
						<td colspan='6' align='center'>
							<br>
							<input name='btn_pesquisar' type='hidden'>
							<input name='pesquisar' type='button' class='botao' 
							onclick="document.frm_procura.btn_pesquisar.value='pesquisar';document.frm_procura.submit();" value='Pesquisar'>
						</td>
					</tr>
			</table>
			</form>
		</div>
	</div>
	</td>
	</tr>
	<tr height='20'>
		<td  align='center' colspan='6'></td>
	</tr>
</table>



<?
if(strlen($msg_erro)>0){
echo "<table border='0' width='100%' cellpadding='0' cellspacing='2' align='center'  bgcolor='#FF3300'>";
echo "<tr>";
echo "<td align='center'>$msg_erro</td>";
echo "</tr>";
echo "</table>";

}

$btn_pesquisar = $_POST['btn_pesquisar'];
if(strlen($btn_pesquisar)>0){
$familia        = $_POST['familia'];
$data_inicial   = trim($_POST['data_inicial']);
$data_final     = trim($_POST['data_final']);
if(strlen($data_inicial)>0){
	$data_inicial = str_replace("/","",$data_inicial);
	$data_inicial = str_replace("-","",$data_inicial);
	$data_inicial = str_replace(" ","",$data_inicial);
	if(strlen($data_inicial) > 0){	
		$data_inicial = "'".substr ($data_inicial,4,4) . "-" . substr ($data_inicial,2,2) . "-" . substr ($data_inicial,0,2)." 00:00:00'";
	}
}
if(strlen($data_final)>0){
	$data_final = str_replace("/","",$data_final);
	$data_final = str_replace("-","",$data_final);
	$data_final = str_replace(" ","",$data_final);
	if(strlen($data_final) > 0){	
		$data_final = "'".substr ($data_final,4,4) . "-" . substr ($data_final,2,2) . "-" . substr ($data_final,0,2)." 23:59:59'";
	}
}
$cond_1 = " 1=1 ";
if(strlen($data_inicial)>0 and strlen($data_final)>0){
 $cond_1 = " tbl_pedido.data between $data_inicial and $data_final";
}
$cond_2 = " 1=1 ";
if(strlen($familia)>0){
$cond_2 = " tbl_peca_item.familia = $familia";
}
		$sql = "
				SELECT	tbl_cotacao_item.cotacao_item                   ,
						tbl_peca.descricao as peca_descricao            ,
						tbl_peca.peca                                   ,
						tbl_peca.referencia as peca_referencia          ,
						tbl_pedido.pedido                               ,
						tbl_pedido.data    as data_pedido               ,
						tbl_pedido_item.preco     as preco_compra       ,
						tbl_pedido_item.qtde      as qtde_compra        ,
						tbl_estoque.qtde          as qtde_estoque       ,
						tbl_estoque_extra.quantidade_entregar           ,
						tbl_peca.ipi                                    ,
						tbl_pedido_item.icms                            ,
						tbl_peca_item.valor_custo_medio
				FROM tbl_cotacao_fornecedor_item
				JOIN tbl_cotacao_fornecedor on tbl_cotacao_fornecedor.cotacao_fornecedor = tbl_cotacao_fornecedor_item.cotacao_fornecedor
				JOIN tbl_pedido             on tbl_pedido.cotacao_fornecedor             = tbl_cotacao_fornecedor.cotacao_fornecedor and tbl_pedido.cotacao_fornecedor is not null
				JOIN tbl_pedido_item        on tbl_pedido.pedido                         = tbl_pedido_item.pedido and tbl_pedido_item.peca = tbl_cotacao_fornecedor_item.peca
				JOIN tbl_pessoa_fornecedor  on tbl_pessoa_fornecedor.pessoa              = tbl_cotacao_fornecedor.pessoa_fornecedor
				JOIN tbl_cotacao            on tbl_cotacao.cotacao                       = tbl_cotacao_fornecedor.cotacao
				JOIN tbl_cotacao_item       on (tbl_cotacao_item.cotacao                 = tbl_cotacao.cotacao
										AND tbl_cotacao_item.peca                        = tbl_cotacao_fornecedor_item.peca)
				JOIN tbl_peca               on tbl_peca.peca                             = tbl_cotacao_item.peca
				JOIN tbl_peca_item          on tbl_peca.peca                             = tbl_peca_item.peca
				JOIN tbl_estoque            on tbl_estoque.peca                          = tbl_peca.peca
				JOIN tbl_estoque_extra      on tbl_estoque_extra.peca                    = tbl_estoque.peca
				WHERE tbl_cotacao.empresa           = $login_empresa
				AND   $cond_1
				and   $cond_2
				AND   tbl_cotacao_fornecedor.status = 'cotada' 
				AND   tbl_cotacao_item.status       = 'comprado'
				AND   tbl_cotacao_item.formacao_preco IS NULL
				ORDER BY	tbl_peca.descricao, 
							tbl_pedido.data";
//echo $sql;
		$res = @pg_exec ($con,$sql) ;

	if (@pg_numrows($res) > 0) {
		echo "<br>";
		echo "<form name='frm' method='post' action='$PHP_SELF'>";
		/*echo "<input type='hidden' name='qtde_item' value='$qtde_item'>";*/
		echo "<input id='pis' name='pis' value='$pis' type='hidden'>";
		echo "<input id='cofins' name='cofins' value='$cofins' type='hidden'>";
		echo "<input id='irpj' name='irpj' value='$irpj' type='hidden'>";
		echo "<input id='contribuicao_social' name='contribuicao_social' value='$contribuicao_social' type='hidden'>";
		echo "<table border='1' cellpadding='5' cellspacing='2' style='border-collapse: collapse' bordercolor='#052756' align='center'>";
		echo "<tr class='Titulo_Tabela' bgcolor='#7392BF'>";
		echo "<td colspan='11' align='center'>Forma��o de Pre�o do Produto</td>";

		$xsql = "SELECT	tbl_tabela.tabela,
						tbl_tabela.sigla_tabela,
						tbl_tabela.descricao
				FROM tbl_tabela
				WHERE tbl_tabela.fabrica = $login_empresa
				AND tbl_tabela.ativa is true
				ORDER BY sigla_tabela";
		$xres = pg_exec($con,$xsql);
		
		if(pg_numrows($xres)>0){
			for($x=0;pg_numrows($xres)>$x;$x++){
				$tabela       = pg_result($xres,$x,tabela);
				$sigla_tabela = pg_result($xres,$x,sigla_tabela);
				$descricao    = pg_result($xres,$x,descricao);
				$cor = array("#9999CC","#489D15","#49188f","#0042A6","#A9AC6F");
				echo "<td colspan='7' class='Titulo_Tabela' bgcolor='$cor[$x]' align='center'>$sigla_tabela - $descricao</td>";
			}
		//"#FFEAC0","#DDF8CC","#E3D6F8","#E1EDFF","#99CCCC"
		}
		echo "</tr>";

		echo "<tr height='20' bgcolor='#7392BF' class='Titulo_Tabela_Pequeno'>";
		echo "<td align='center' >Refer�ncia</td>";
		echo "<td align='center' >Descri��o</td>";
		echo "<td align='center' >Estoque</td>";
		echo "<td align='center' >Qtde<br> Entregar</td>";
		echo "<td align='center' >Pedido</td>";
		echo "<td align='center' >Qtde</td>";
		echo "<td align='center' >Pre�o<BR>NF</td>";
		echo "<td align='center' >% ICMS</td>";
		echo "<td align='center' >IPI</td>";
		echo "<td align='center' ><a href='#' title='Pre�o de compra - ICMS'  class='text_curto'>";
		echo "<span> ";
		echo "<font color='#FFFFFF'>Custo<br>Real</FONT></a></span>";
		echo "</td>"; //Pre�o de compra - icms
		echo "<td align='center' >M�dia</td>";
		
	/*colocar aqui*/
		$xsql = "SELECT	tbl_tabela.tabela,
						tbl_tabela.sigla_tabela,
						tbl_tabela.descricao
				FROM tbl_tabela
				WHERE tbl_tabela.fabrica = $login_empresa
				AND tbl_tabela.ativa is true
				ORDER BY sigla_tabela";
		$xres = pg_exec($con,$xsql);
		
		if(pg_numrows($xres)>0){
			for($x=0;pg_numrows($xres)>$x;$x++){
				$tabela       = pg_result($xres,$x,tabela);
				$sigla_tabela = pg_result($xres,$x,sigla_tabela);
				$descricao    = pg_result($xres,$x,descricao);
				$cor = array("#9999CC","#489D15","#49188f","#0042A6","#A9AC6F");
	//			$cor = array("#FFEAC0","#DDF8CC","#E3D6F8","#E1EDFF","#99CCCC");
				
				echo "<td align='center' bgcolor='$cor[$x]'>% Desp. Adm </td>";
				echo "<td align='center' bgcolor='$cor[$x]'>% Comiss�o </td>";
				echo "<td align='center' bgcolor='$cor[$x]'>% Marketing </td>";
				echo "<td align='center' bgcolor='$cor[$x]'>% Perda </td>";
				echo "<td align='center' bgcolor='$cor[$x]'>% Lucro </td>";
			/*	echo "<td align='center' bgcolor='$cor[$x]'>% Vendas </td>";*/
				echo "<td align='center' bgcolor='$cor[$x]'>Sugerido</td>";
				echo "<td align='center' bgcolor='$cor[$x]'>Atual</td>";
			}
		}

		
		echo "<td align='center' >A��o</td>";
		echo "</tr>";	

		for ($i = 0; $i <pg_numrows($res) ; $i++) {

			$peca            = trim(pg_result($res,$i,peca));
			$peca_referencia = trim(pg_result($res,$i,peca_referencia));
			$peca_descricao  = trim(pg_result($res,$i,peca_descricao));
			$peca_descricao  = substr($peca_descricao,0,20);
			$pedido          = trim(pg_result($res,$i,pedido));
			$preco_compra    = trim(pg_result($res,$i,preco_compra));
			$preco_compra    = number_format($preco_compra,3,'.','.');
			$qtde_compra     = trim(pg_result($res,$i,qtde_compra));
			$estoque         = trim(pg_result($res,$i,qtde_estoque));
			$qtde_entregar   = trim(pg_result($res,$i,quantidade_entregar));
			$icms            = trim(pg_result($res,$i,icms));
			$ipi             = trim(pg_result($res,$i,ipi));
			$cotacao_item    = trim(pg_result($res,$i,cotacao_item));
			$valor_custo_medio = trim(pg_result($res,$i,valor_custo_medio));
			$valor_custo_medio = number_format($valor_custo_medio,3,'.','.');
			$preco_custo       = number_format($preco_compra -($preco_compra *($icms/100)),3,'.','.');




			if($i%2==0)$cor = '#ECF3FF';
			else       $cor = '#FFFFFF';

			echo "<tr bgcolor='$cor' class='Conteudo'>";
			echo "<td align='center'> $peca_referencia</td>";
			echo "<td align='left' nowrap >$peca_descricao</td>";
			echo "<td align='center'>$estoque</td>";
			echo "<td align='center'>$qtde_entregar</td>";
			echo "<td align='left'  >$pedido</td>";
			echo "<td align='center'>$qtde_compra</td>";
			echo "<td align='center' >$preco_compra</td>";

			echo "<td align='center'>$icms</td>";
			echo "<td align='right'>$ipi</td>";
			echo "<td align='right'>$preco_custo";
			echo "<input id='custo_real_$i' name='custo_real_$i' value='$preco_custo' type='hidden'>";
			echo "<input id='icms_$i' name='icms_$i' value='$icms' type='hidden'>";
			echo "<input id='ipi_$i' name='ipi_$i' value='$ipi' type='hidden'>";
			echo "<input id='cotacao_item_$i' name='cotacao_item_$i' value='$cotacao_item' type='hidden'>";
			echo "</td>";
			echo "<td align='right' >$valor_custo_medio</td>";
			
			/*colocar aqui*/
			/* pe�as */
				$xsql = "SELECT	tbl_tabela.tabela       ,
								tbl_tabela.sigla_tabela ,
								tbl_tabela.descricao
						FROM tbl_tabela
						WHERE tbl_tabela.fabrica = $login_empresa
						AND tbl_tabela.ativa is true
						ORDER BY sigla_tabela";
				$xres = pg_exec($con,$xsql);
				if(pg_numrows($xres)>0){
					for($x=0;pg_numrows($xres)>$x;$x++){
						$tabela       = pg_result($xres,$x,tabela);
						$sigla_tabela = pg_result($xres,$x,sigla_tabela);
						$descricao    = pg_result($xres,$x,descricao);
						$cor = array("#E4E4F1","#DDF8CC","#E3D6F8","#E1EDFF","#DBDCC2");

						$xxsql = "SELECT	peca                                                    ,
											tabela                                                  ,
											CASE WHEN  preco  is null then '0'
											ELSE preco end as preco                                 ,
											data_vigencia                                           ,
											termino_vigencia                                        ,
											CASE WHEN  percentual_marketing  is null then '0'
											ELSE percentual_marketing end as percentual_marketing   ,
											CASE WHEN  percentual_lucro  is null then '0'
											ELSE percentual_lucro end as percentual_lucro           ,
											CASE WHEN  percentual_vendas  is null then '0'
											ELSE percentual_vendas end as percentual_vendas         ,
											CASE WHEN  percentual_administrativo  is null then '0'
											ELSE percentual_administrativo end as percentual_administrativo,
											CASE WHEN  percentual_comissao  is null then '0'
											ELSE percentual_comissao end as percentual_comissao      ,
											CASE WHEN  percentual_perdas  is null then '0'
											ELSE percentual_perdas end as percentual_perdas
								FROM tbl_tabela_item_erp
								WHERE tbl_tabela_item_erp.tabela = $tabela
								AND   tbl_tabela_item_erp.peca   = $peca
								and   tbl_tabela_item_erp.termino_vigencia is null
								ORDER BY tbl_tabela_item_erp.data_vigencia desc
								LIMIT 1";

							$xxres = pg_exec($con,$xxsql);
							if(pg_numrows($xxres)>0){

								for($w=0;pg_numrows($xxres)>$w;$w++){
									$percentual_marketing      = pg_result($xxres,$w,percentual_marketing);
									$percentual_lucro          = pg_result($xxres,$w,percentual_lucro);
									$percentual_vendas         = pg_result($xxres,$w,percentual_vendas);
									$percentual_administrativo = pg_result($xxres,$w,percentual_administrativo);
									$percentual_comissao       = pg_result($xxres,$w,percentual_comissao);
									$percentual_perdas         = pg_result($xxres,$w,percentual_perdas);
									$preco                     = pg_result($xxres,$w,preco);

									
									/*calculo do preco sugerido //ATENCAO SE ALTERAR REGRA DE CALCULO, TEM QUE ALTERAR O JAVASCRIPT*/

									$vpercentual_administrativo = $preco_custo * ($percentual_marketing /100);
									$vpercentual_comissao       = $preco_custo * ($percentual_comissao  /100);
									$vpercentual_marketing      = $preco_custo * ($percentual_marketing /100);
									$vpercentual_perdas         = $preco_custo * ($percentual_perdas    /100);
									$vpercentual_lucro          = $preco_custo * ($percentual_lucro     /100);

									$margem_contribuicao  = $preco_custo + $vpercentual_administrativo + $vpercentual_comissao + $vpercentual_marketing + $vpercentual_perdas + $vpercentual_lucro;

									$vicms_venda          = $margem_contribuicao * ($icms/100);
									$vpis                 = $margem_contribuicao * ($pis/100);
									$vcofins              = $margem_contribuicao * ($cofins/100);
									$virpj                = $margem_contribuicao * ($irpj/100);
									$vcontribuicao_social = $margem_contribuicao * ($contribuicao_social/100);
									$somatorio            = $margem_contribuicao + $vicms_venda + $vpis + $vcofins + $virpj + $vcontribuicao_social;

									$vinversa  =  100 - ($icms + $pis + $cofins + $irpj + $contribuicao_social);
									$preco_sugerido = $margem_contribuicao / ($vinversa/100);
									$preco_sugerido = number_format($preco_sugerido,3,'.','.');
									/*calculo do preco sugerido*/

									echo "<input id='peca_$i'   name='peca_$i'    value='$peca'    type='hidden'>";//mudar para hidden
									

									echo "<td align='center' bgcolor='$cor[$x]'>";//adm
									echo "<input id='tabela_$i"."_"."$x' name='tabela_$i"."_"."$x'  value='$tabela'  type='hidden'>";//mudar para hidden
									echo "<input type='text' name='percentual_administrativo_$i"."_"."$x'
											id='percentual_administrativo_$i"."_"."$x' 
											value='$percentual_administrativo' 
											onblur='
											checarNumero(this);
											porcentagem(\"ipi_$i\",\"icms_$i\",\"custo_real_$i\",
												\"percentual_administrativo_$i"."_"."$x\",
												\"percentual_comissao_$i"."_"."$x\",
												\"percentual_marketing_$i"."_"."$x\",
												\"percentual_perdas_$i"."_"."$x\",
												\"percentual_lucro_$i"."_"."$x\",
												\"preco_sugerido_$i"."_"."$x\"
											)' 
											size='3' style='text-align:right'>";

									//echo "$percentual_administrativo &nbsp;";
									echo "</td>";
									echo "<td align='center' bgcolor='$cor[$x]'>";//comissao
									echo "<input type='text' name='percentual_comissao_$i"."_"."$x'
											value='$percentual_comissao' 
											id='percentual_comissao_$i"."_"."$x' 
											onblur='
											checarNumero(this);
											porcentagem(\"ipi_$i\",\"icms_$i\",\"custo_real_$i\",
												\"percentual_administrativo_$i"."_"."$x\",
												\"percentual_comissao_$i"."_"."$x\",
												\"percentual_marketing_$i"."_"."$x\",
												\"percentual_perdas_$i"."_"."$x\",
												\"percentual_lucro_$i"."_"."$x\",
											/*	\"percentual_vendas_$i"."_"."$x\",*/
												\"preco_sugerido_$i"."_"."$x\"
											)'   
											size='3' style='text-align:right'>";
									echo "</td>";
									echo "<td align='center' bgcolor='$cor[$x]'>";//mkt
									echo "<input type='text' name='percentual_marketing_$i"."_"."$x'
										value='$percentual_marketing' 
										id='percentual_marketing_$i"."_"."$x' 
										onblur='
											checarNumero(this);
											porcentagem(\"ipi_$i\",\"icms_$i\",\"custo_real_$i\",
												\"percentual_administrativo_$i"."_"."$x\",
												\"percentual_comissao_$i"."_"."$x\",
												\"percentual_marketing_$i"."_"."$x\",
												\"percentual_perdas_$i"."_"."$x\",
												\"percentual_lucro_$i"."_"."$x\",
												\"preco_sugerido_$i"."_"."$x\"
											)' 
										size='3' style='text-align:right'>";
									/*echo "$percentual_marketing &nbsp;";*/
									echo "</td>";
									echo "<td align='center' bgcolor='$cor[$x]'>";//perdas
									echo "<input type='text' name='percentual_perdas_$i"."_"."$x'
										value='$percentual_perdas' 
										id='percentual_perdas_$i"."_"."$x' 
										onblur='
											checarNumero(this);
											porcentagem(\"ipi_$i\",\"icms_$i\",\"custo_real_$i\",
												\"percentual_administrativo_$i"."_"."$x\",
												\"percentual_comissao_$i"."_"."$x\",
												\"percentual_marketing_$i"."_"."$x\",
												\"percentual_perdas_$i"."_"."$x\",
												\"percentual_lucro_$i"."_"."$x\",
												\"preco_sugerido_$i"."_"."$x\"
											)' 
										size='3' style='text-align:right'>";
									/*echo "$percentual_perdas &nbsp;";*/
									echo "</td>";
									echo "<td align='center' bgcolor='$cor[$x]'>";//lucro
									echo "<input type='text' name='percentual_lucro_$i"."_"."$x'
										value='$percentual_lucro' 
										id='percentual_lucro_$i"."_"."$x' 
										onblur='
											checarNumero(this);
											porcentagem(\"ipi_$i\",\"icms_$i\",\"custo_real_$i\",
												\"percentual_administrativo_$i"."_"."$x\",
												\"percentual_comissao_$i"."_"."$x\",
												\"percentual_marketing_$i"."_"."$x\",
												\"percentual_perdas_$i"."_"."$x\",
												\"percentual_lucro_$i"."_"."$x\",
												\"preco_sugerido_$i"."_"."$x\"
											)' 
										size='3' style='text-align:right'>";
								//echo "$percentual_lucro&nbsp;";
									echo "</td>";

									echo "<td align='center' bgcolor='$cor[$x]'>";//sugerido
									echo "<input type='text' name='preco_sugerido_$i"."_"."$x'
										value='$preco_sugerido' 
										id='preco_sugerido_$i"."_"."$x' 
										onblur='
											checarNumero(this);
											porcentagem_negativa(\"percentual_lucro_$i"."_"."$x\",
												\"preco_sugerido_$i"."_"."$x\"
											)' 
										size='3' style='text-align:right'>";
									echo "</td>";
									echo "<td align='center' bgcolor='$cor[$x]'>";//preco atual
									echo "<input type='text' name='preco_$i"."_"."$x'
										value='$preco' 
										id='preco_$i"."_"."$x' 
										size='3' style='text-align:right'>";
									echo "</td>";
								}
							
							}else{
									echo "<td align='center' bgcolor='$cor[$x]'>";
									echo "<input id='tabela_$i"."_"."$x' name='tabela_$i"."_"."$x'  value='$tabela'  type='hidden'>";//mudar para hidden
									echo "<input type='text' name='percentual_administrativo_$i"."_"."$x'
											id='percentual_administrativo_$i"."_"."$x' 
											value='0' 
											onblur='
											checarNumero(this);
											porcentagem(\"ipi_$i\",\"icms_$i\",\"custo_real_$i\",
												\"percentual_administrativo_$i"."_"."$x\",
												\"percentual_comissao_$i"."_"."$x\",
												\"percentual_marketing_$i"."_"."$x\",
												\"percentual_perdas_$i"."_"."$x\",
												\"percentual_lucro_$i"."_"."$x\",
												\"preco_sugerido_$i"."_"."$x\"
											)' 
											size='3' style='text-align:right'>";


									//echo "$percentual_administrativo &nbsp;";
									echo "</td>";
									echo "<td align='center' bgcolor='$cor[$x]'>";//comissao
									echo "<input type='text' name='percentual_comissao_$i"."_"."$x'
											value='0' 
											id='percentual_comissao_$i"."_"."$x' 
											onblur='
											checarNumero(this);
											porcentagem(\"ipi_$i\",\"icms_$i\",\"custo_real_$i\",
												\"percentual_administrativo_$i"."_"."$x\",
												\"percentual_comissao_$i"."_"."$x\",
												\"percentual_marketing_$i"."_"."$x\",
												\"percentual_perdas_$i"."_"."$x\",
												\"percentual_lucro_$i"."_"."$x\",
												\"preco_sugerido_$i"."_"."$x\"
											)'   
											size='3' style='text-align:right'>";
									echo "</td>";
									echo "<td align='center' bgcolor='$cor[$x]'>";//mkt
									echo "<input type='text' name='percentual_marketing_$i"."_"."$x'
										value='0' 
										id='percentual_marketing_$i"."_"."$x' 
										onblur='
											checarNumero(this);
											porcentagem(\"ipi_$i\",\"icms_$i\",\"custo_real_$i\",
												\"percentual_administrativo_$i"."_"."$x\",
												\"percentual_comissao_$i"."_"."$x\",
												\"percentual_marketing_$i"."_"."$x\",
												\"percentual_perdas_$i"."_"."$x\",
												\"percentual_lucro_$i"."_"."$x\",
												\"preco_sugerido_$i"."_"."$x\"
											)' 
										size='3' style='text-align:right'>";
									/*echo "$percentual_marketing &nbsp;";*/
									echo "</td>";
									echo "<td align='center' bgcolor='$cor[$x]'>";//perdas
									echo "<input type='text' name='percentual_perdas_$i"."_"."$x'
										value='0' 
										id='percentual_perdas_$i"."_"."$x' 
										onblur='
											checarNumero(this);
											porcentagem(\"ipi_$i\",\"icms_$i\",\"custo_real_$i\",
												\"percentual_administrativo_$i"."_"."$x\",
												\"percentual_comissao_$i"."_"."$x\",
												\"percentual_marketing_$i"."_"."$x\",
												\"percentual_perdas_$i"."_"."$x\",
												\"percentual_lucro_$i"."_"."$x\",
												\"preco_sugerido_$i"."_"."$x\"
											)' 
										size='3' style='text-align:right'>";
									/*echo "$percentual_perdas &nbsp;";*/
									echo "</td>";
									echo "<td align='center' bgcolor='$cor[$x]'>";//lucro
									echo "<input type='text' name='percentual_lucro_$i"."_"."$x'
										value='0' 
										id='percentual_lucro_$i"."_"."$x' 
										onblur='
											checarNumero(this);
											porcentagem(\"ipi_$i\",\"icms_$i\",\"custo_real_$i\",
												\"percentual_administrativo_$i"."_"."$x\",
												\"percentual_comissao_$i"."_"."$x\",
												\"percentual_marketing_$i"."_"."$x\",
												\"percentual_perdas_$i"."_"."$x\",
												\"percentual_lucro_$i"."_"."$x\",
												\"preco_sugerido_$i"."_"."$x\"
											)' 
										size='3' style='text-align:right'>";
								//echo "$percentual_lucro&nbsp;";
									echo "</td>";						
									echo "<td align='center' bgcolor='$cor[$x]'>";//sugerido
									echo "<input type='text' name='preco_sugerido_$i"."_"."$x'
										value='0' 
										id='preco_sugerido_$i"."_"."$x' 
										onblur='
											checarNumero(this);
											porcentagem_negativa(\"percentual_lucro_$i"."_"."$x\",
												\"preco_sugerido_$i"."_"."$x\"
											)' 
										size='3' style='text-align:right'>";
									echo "</td>";
									echo "<td align='center' bgcolor='$cor[$x]'>";//preco atual
									echo "<input type='text' name='preco_$i"."_"."$x'
										value='$valor_custo_medio' 
										id='preco_$i"."_"."$x' 
										size='3' style='text-align:right'>";
									echo "</td>";


							}
					}
				}

			echo "<td align='right'>";
			echo "<input type='button' value='Gravar'
			onclick=\"javascript:if(document.frm.btn_acao.value!=''){
				alert('Aguarde Submiss�o'); 
			}else{
				document.frm.btn_acao.value='Gravar';
				document.frm.linha.value='$i';
				document.frm.submit();
			}\">";
			echo "</td>";
			echo "</tr>";


		}
		echo "</table>";
			echo "<input type='hidden' name='linha'  value=''>";
			echo "<input type='hidden' name='coluna'  value='$x'>";
			echo "<input type='hidden' name='btn_acao'  value=''>";
			echo "<input id='qtde_peca'  name='qtde_peca'  value='$i'  type='hidden'>"; //mudar para hidden
			echo "</form>";
	}else{
		echo "<br><p>Nenhuma produto encontrado</p>";
	}
}
include 'rodape.php'
?>