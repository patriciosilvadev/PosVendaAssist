<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'autentica_admin.php';


if(strlen($_POST['codigo_posto'])>0) $codigo_posto = trim($_POST['codigo_posto']);
else                                 $codigo_posto = trim($_GET['codigo_posto']);

if(strlen($_POST['data_inicial'])>0) $data_inicial = trim($_POST['data_inicial']);
else                                 $data_inicial = trim($_GET['data_inicial']);

if(strlen($_POST['data_final'])>0) $data_final = trim($_POST['data_final']);
else                               $data_final = trim($_GET['data_final']);

if(strlen($_POST['acao'])>0) $acao = trim ($_POST['acao']);
else                         $acao = trim ($_GET['acao']);

if(strlen($_POST['pais'])>0) $pais = trim ($_POST['pais']);
else                         $pais = trim ($_GET['pais']);

if($acao=="PESQUISAR"){
	if(strlen($data_inicial)>0){
		$x_data_inicial = substr ($data_inicial,6,4) . "-" . substr ($data_inicial,3,2) . "-" . substr ($data_inicial,0,2);
	}

	if(strlen($data_final)>0){
		$x_data_final = substr ($data_final,6,4) . "-" . substr ($data_final,3,2) . "-" . substr ($data_final,0,2);
	}

	if(empty($data_inicial) || empty($data_final)) {
		$msg_erro = 'Data Inv�lida';
	}
	else {
	
		if($data_inicial > $data_final)
			$msg_erro = 'Data Inv�lida';
		else {
			list($d, $m, $y) = explode("/", $data_inicial);
	        if(!checkdate($m,$d,$y)) 
		        $msg_erro = "Data Inv�lida";
			list($d, $m, $y) = explode("/", $data_final);
			if(!checkdate($m,$d,$y)) 
				$msg_erro = "Data Inv�lida";
		}

	}

	if(strlen($pais)>0){
		$cond_pais = " tbl_posto.pais = '$pais'";
	}else{
		$cond_pais = " 1 = 1 ";
	}

	if(strlen($codigo_posto)>0){
		$sqlP = "SELECT tbl_posto_fabrica.posto
				 FROM tbl_posto_fabrica
				 WHERE tbl_posto_fabrica.codigo_posto = '$codigo_posto'
				 AND   tbl_posto_fabrica.fabrica      = $login_fabrica";
		$resP = pg_exec($con,$sqlP);

		if(pg_numrows($resP)>0){
			$posto = pg_result($resP,0,posto);

			$cond_posto = " tbl_os_orcamento.posto = $posto ";
		}
	}else{
		$cond_posto = " 1 = 1 ";
	}
}

$title = "RELAT�RIO OS FORA DA GARANTIA - SINT�TICO";
include "cabecalho.php";

?>

<? include "javascript_pesquisas.php"; ?>

<style>
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

	P{
		text-align: center;
		font-family: Arial;
		font-size: 12px;
		font-weight: bold;
		color: #000000;
	}

	.Conteudo{
		font-family: Arial;
		font-size: 11px;
		font-weight: normal;
	}
	
	.ConteudoBranco{
		font-family: Arial;
		font-size: 11px;
		color:#FFFFFF;
		font-weight: normal;
	}
	
	.Mes{
		font-size: 8px;
	}

	td{
		font-family: Arial;
		font-size: 11px;
		font-weight: normal;
	}

	
.msg_erro{
    background-color:#FF0000;
    font: bold 16px "Arial";
    color:#FFFFFF;
    text-align:center;
}
</style>

<? include "javascript_calendario.php";?>

<script language="javascript" src="js/cal2.js"></script>
<script language="javascript" src="js/cal_conf2.js"></script>

<script type="text/javascript" charset="utf-8">
	$(document).ready(function(){
		$('#data_inicial').datePicker({startDate : '01/01/2000'});
		$('#data_final').datePicker({startDate : '01/01/2000'});
		$("#data_inicial").maskedinput("99/99/9999");
		$("#data_final").maskedinput("99/99/9999");
	});
</script>

<?
	if(strlen($msg_erro)>0){
		echo "<div class='msg_erro' style='width:700px; margin:auto;'>";
			echo $msg_erro;
		echo "</div>";
	}
?>

	<form name="frm_pesquisa" method="POST" action="<?echo $PHP_SELF?>">
	<input type="hidden" name="acao">
	<table width="700px" align="center" border="0" cellspacing="1" cellpadding="0" style="background:#D9E2EF;">
		<tr>
			<td colspan="4" class="titulo_tabela" height="20">
				Par�metros de Pesquisa
			</td>
		</tr>

		<tr class="Conteudo">
			<td width='10%'>
			</td>
			<td width='40%' align='left' >
				Data Inicial
			</td>
			<td width='40%' align='left' >
				Data Final
			</td>
			<td width='10%'>
			</td>
		</tr>
		<tr class="Conteudo">
			<td width='10%'>
			</td>
			<td width='40%' align='left'>
				<input type='text' name='data_inicial' id='data_inicial' rel='data' size='11' value='<? echo $data_inicial ?>' class='frm'>
			</td>
			<td width='40%' align='left'>
				<input type='text' name='data_final' id='data_final' rel='data' size='11' value='<? echo $data_final ?>' class='frm'>
			</td>
			<td width='10%'>
			</td>
		</tr>

		<tr class="Conteudo">
			<td width='10%'>
			</td>
			<td width='40%' align='left' >
				C�digo Posto
			</td>
			<td width='40%' align='left' >
				Nome Posto
			</td>
			<td width='10%'>
			</td>
		</tr>
		<tr class="Conteudo">
			<td width='10%'>
			</td>
			<td width='40%' align='left'>
				<input type='text' name='codigo_posto' id='codigo_posto' size='15' value='<? echo $codigo_posto ?>' class='frm'>
				<img border="0" src="imagens/lupa.png" style="cursor: pointer;" align="absmiddle" alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript: fnc_pesquisa_posto (document.frm_pesquisa.codigo_posto, document.frm_pesquisa.posto_nome, 'codigo')">
			</td>
			<td width='40%' align='left'>
				<input type='text' name='posto_nome' id='posto_nome' size='25' value='<? echo $posto_nome ?>' class='frm'>
				<img border="0" src="imagens/lupa.png" style="cursor: pointer;" align="absmiddle" alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript: fnc_pesquisa_posto (document.frm_pesquisa.codigo_posto, document.frm_pesquisa.posto_nome, 'nome')">
			</td>
			<td width='10%'>
			</td>
		</tr>
		
		<tr class="Conteudo">
			<td width='10%'>
			</td>
			<td width='40%' align='left' >
				Pa�s
			</td>
			<td width='40%' align='left' >
			&nbsp;
			</td>
			<td width='10%'>
			</td>
		</tr>

		<tr class="Conteudo">
			<td width='10%'>
			</td>
			<td width='40%' align='left'>
				<SELECT NAME="pais" class='frm'>
					<OPTION VALUE=""></OPTION>
					<?
						$sqlP = "SELECT pais,nome FROM tbl_pais";
						$resP = pg_exec($con, $sqlP);

						if(pg_numrows($resP)>0){
							for($x=0; $x<pg_numrows($resP); $x++){
								$xpais = pg_result($resP,$x,pais);
								$xnome = pg_result($resP,$x,nome);
								
								echo "<OPTION VALUE='$xpais'";
								if($pais==$xpais) echo "selected";
								echo ">$xnome</OPTION>";
							}
						}
					?>
				</SELECT>
			</td>
			<td width='40%' align='left'>
			&nbsp;
			</td>
			<td width='10%'>
			</td>
		</tr>
		<tr>
			<td colspan="4" align="center">
				<BR>
				<input type="button" style="background:url(imagens/btn_pesquisar_400.gif);width:400px; height:20px; cursor:pointer;" value="" onclick="document.frm_pesquisa.acao.value='PESQUISAR'; document.frm_pesquisa.submit();" />
				
			</td>
		</tr>
		<tr><td colspan="4"> </td></tr>
	</table>
	</form>

<?

if($acao=="PESQUISAR" AND strlen($msg_erro)==0){
	if($login_fabrica == 20){
		$cond_conserto_horas = " 1 = 1 ";
	}else{
		$cond_conserto_horas = " tbl_os_orcamento.conserto IS NOT NULL AND   tbl_os_orcamento.horas_aguardando_retirada IS NOT NULL ";
	}	
	
	$sqlT = "SELECT tbl_os_orcamento.posto                                                           ,
					tbl_posto.nome                                                                   ,
					tbl_posto.pais                                                                   ,
					tbl_os_orcamento.os_orcamento                                                    ,
					CASE WHEN tbl_os_orcamento.horas_aguardando_orcamento > 0
					THEN tbl_os_orcamento.horas_aguardando_orcamento ELSE '0' END AS horas_aguardando_orcamento,
					CASE WHEN tbl_os_orcamento.horas_aguardando_conserto > 0
					THEN tbl_os_orcamento.horas_aguardando_conserto ELSE '0' END AS horas_aguardando_conserto
			INTO TEMP tmp_orcamento_horas_total
			FROM tbl_os_orcamento
			JOIN tbl_posto ON tbl_posto.posto = tbl_os_orcamento.posto
			WHERE tbl_os_orcamento.abertura BETWEEN '$x_data_inicial 00:00:00' AND '$x_data_final 23:59:59'
			AND $cond_conserto_horas
			AND $cond_posto
			AND $cond_pais
			AND tbl_os_orcamento.fabrica = $login_fabrica;

			SELECT tmp_orcamento_horas_total.posto, COUNT(tmp_orcamento_horas_total.os_orcamento) AS total_os
			INTO TEMP tmp_orcamento_horas_total_total
			FROM tmp_orcamento_horas_total
			GROUP BY tmp_orcamento_horas_total.posto;

			SELECT tmp_orcamento_horas_total.posto, tmp_orcamento_horas_total.os_orcamento,
			SUM(tmp_orcamento_horas_total.horas_aguardando_orcamento + tmp_orcamento_horas_total.horas_aguardando_conserto)
			INTO TEMP tmp_orcamento_horas_menor_16
			FROM tmp_orcamento_horas_total
			GROUP BY tmp_orcamento_horas_total.posto, tmp_orcamento_horas_total.os_orcamento
			HAVING SUM(tmp_orcamento_horas_total.horas_aguardando_orcamento + tmp_orcamento_horas_total.horas_aguardando_conserto) <= 1200;

			SELECT posto, COUNT(*) AS menor_dezesseis INTO TEMP tmp_orcamento_horas_menor_1616 FROM tmp_orcamento_horas_menor_16 GROUP BY posto;

			SELECT tmp_orcamento_horas_total.posto, tmp_orcamento_horas_total.os_orcamento,
			SUM(tmp_orcamento_horas_total.horas_aguardando_orcamento + tmp_orcamento_horas_total.horas_aguardando_conserto)
			INTO TEMP tmp_orcamento_horas_maior_16
			FROM tmp_orcamento_horas_total
			GROUP BY tmp_orcamento_horas_total.posto, tmp_orcamento_horas_total.os_orcamento
			HAVING SUM(tmp_orcamento_horas_total.horas_aguardando_orcamento + tmp_orcamento_horas_total.horas_aguardando_conserto) > 1200;

			SELECT posto, COUNT(*) AS maior_dezesseis  INTO TEMP tmp_orcamento_horas_maior_1616 FROM tmp_orcamento_horas_maior_16 GROUP BY posto;

			";
	#echo nl2br($sqlT); 
	$resT     = @pg_exec($con,$sqlT);
	$msg_erro = pg_errormessage($con);

	$sql = "SELECT tmp_orcamento_horas_total.posto                           ,
			tmp_orcamento_horas_total.pais                                   ,
			tmp_orcamento_horas_total_total.total_os                         ,
			tmp_orcamento_horas_menor_1616.menor_dezesseis        ,
			tmp_orcamento_horas_maior_1616.maior_dezesseis
			FROM tmp_orcamento_horas_total
			JOIN tmp_orcamento_horas_total_total USING(posto)
			LEFT JOIN tmp_orcamento_horas_menor_1616 USING(posto)
			LEFT JOIN tmp_orcamento_horas_maior_1616 USING(posto)
			GROUP BY tmp_orcamento_horas_total.posto                         ,
			tmp_orcamento_horas_total.pais                                   ,
			tmp_orcamento_horas_total.nome                                   ,
			tmp_orcamento_horas_total_total.total_os                         ,
			tmp_orcamento_horas_menor_1616.menor_dezesseis        ,
			tmp_orcamento_horas_maior_1616.maior_dezesseis
			ORDER BY tmp_orcamento_horas_total.nome ASC";
	//echo nl2br($sql); exit;
	$resxls   = @pg_exec($con,$sql);
	$msg_erro = pg_errormessage($con);

	if(@pg_numrows($resxls)>0){
		##### PAGINA��O - IN�CIO #####
		$sqlCount  = "SELECT count(*) FROM (";
		$sqlCount .= $sql;
		$sqlCount .= ") AS count";

		require "_class_paginacao.php";

		// definicoes de variaveis
		$max_links = 11;				// m�ximo de links � serem exibidos
		$max_res   = 50;				// m�ximo de resultados � serem exibidos por tela ou pagina
		$mult_pag  = new Mult_Pag();	// cria um novo objeto navbar
		$mult_pag->num_pesq_pag = $max_res; // define o n�mero de pesquisas (detalhada ou n�o) por p�gina

		$res = $mult_pag->executar($sql, $sqlCount, $con, "otimizada", "pgsql");
		##### PAGINA��O - FIM #####
	}
	################# GERA XLS ################################
	if(@pg_numrows($resxls)>0){

	flush();
	$data = date ("d/m/Y H:i:s");

	$arquivo_nome     = "consulta-os-fora-garantia-sintetico-$login_fabrica.xls";
	$path             = "xls/";
	$path_tmp         = "/tmp/";

	$arquivo_completo     = $path.$arquivo_nome;
	$arquivo_completo_tmp = $path_tmp.$arquivo_nome;

	echo `rm $arquivo_completo_tmp `;
	echo `rm $arquivo_completo `;

	$fp = fopen ($arquivo_completo_tmp,"w");
	fputs ($fp,"<table border='1' cellpadding='4' cellspacing='0' style='border-collapse: collapse' bordercolor='#000000'  align='center' width='700'>");
	fputs ($fp,"<tr>");
	fputs ($fp,"<td><b>Posto</b></td>");
	fputs ($fp,"<td><b>Pa�s</b></td>");
	fputs ($fp,"<td><b>Total OS</b></td>");
	fputs ($fp,"<td><b>Menor 20h</b></td>");
	fputs ($fp,"<td><b>% Menor 20h</b></td>");
	fputs ($fp,"<td><b>Maior 20h</b></td>");
	fputs ($fp,"<td><b>% Maior 20h</b></td>");
	fputs ($fp,"</TR>");

	for($x =0;$x<pg_num_rows($resxls);$x++) {
		$posto           = pg_result($resxls,$x,posto);
		$pais            = pg_result($resxls,$x,pais);
		$total_os        = pg_result($resxls,$x,total_os);
		$menor_dezesseis = pg_result($resxls,$x,menor_dezesseis);
		$maior_dezesseis = pg_result($resxls,$x,maior_dezesseis);

		$per_menor_dezesseis = ($menor_dezesseis/$total_os) * 100;
		$per_maior_dezesseis = ($maior_dezesseis/$total_os) * 100;

		$per_menor_dezesseis = number_format($per_menor_dezesseis,2,".",",");
		$per_maior_dezesseis = number_format($per_maior_dezesseis,2,".",",");

		if(strlen($total_os)==0)        $total_os = 0;
		if(strlen($menor_dezesseis)==0) $menor_dezesseis = 0;
		if(strlen($maior_dezesseis)==0) $maior_dezesseis = 0;

		if(strlen($posto)>0){
			$sqlN = "SELECT tbl_posto_fabrica.codigo_posto,
							tbl_posto.nome
					 FROM tbl_posto
					 JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_posto.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
					 WHERE tbl_posto.posto = $posto";
			$resN = pg_exec($con,$sqlN);

			if(pg_numrows($resN)>0){
				$codigo_posto = pg_result($resN,0,codigo_posto);
				$posto_nome   = pg_result($resN,0,nome);
			}
		}

		fputs ($fp,"<tr class='Conteudo' align='left'>");
		fputs ($fp,"<td nowrap>" . $codigo_posto ." - ". $posto_nome . "</td>");
		fputs ($fp,"<td nowrap align='center'>" . $pais . "</td>");
		fputs ($fp,"<td nowrap align='center'>" . $total_os . "</td>");
		fputs ($fp,"<td nowrap align='center'>" . $menor_dezesseis . "</td>");
		fputs ($fp,"<td nowrap align='center'>" . $per_menor_dezesseis . "</td>");
		fputs ($fp,"<td nowrap align='center'>" . $maior_dezesseis . "</td>");
		fputs ($fp,"<td nowrap align='center'>" . $per_maior_dezesseis . "</td>");
		fputs($fp,"</tr>");
	}
	fputs ($fp, " </TABLE>");


	echo ` cp $arquivo_completo_tmp $path `;
	$data = date("Y-m-d").".".date("H-i-s");

	echo `htmldoc --webpage --size 297x210mm --fontsize 11 --left 8mm -f $arquivo_completo $arquivo_completo_tmp `;
	$resposta .= "<br>";
	$resposta .="<table width='600' border='0' cellspacing='2' cellpadding='2' align='center'>";
	$resposta .="<tr>";
	$resposta .= "<td align='center'<font face='Arial, Verdana, Times, Sans' size='2' color='#000000'>Clique aqui para fazer o </font><a href='xls/$arquivo_nome' target='_blank'><font face='Arial, Verdana, Times, Sans' size='2' color='#0000FF'>Download em EXCEL</font></a>.</td>";
	$resposta .= "</tr>";
	$resposta .= "</table>";
	echo $resposta;
	}
	################# GERA XLS FIM ############################

	if(@pg_numrows($res)>0){
	echo "<br>";
	fputs ($fp,"<td><b>Posto</b></td>");
	fputs ($fp,"<td><b>Pa�s</b></td>");
	fputs ($fp,"<td><b>Total OS</b></td>");
	fputs ($fp,"<td><b>Menor 20h</b></td>");
	fputs ($fp,"<td><b>% Menor 20h</b></td>");
	fputs ($fp,"<td><b>Maior 20h</b></td>");
	fputs ($fp,"<td><b>% Maior 20h</b></td>");

	echo "<table width='750' border='0' cellpadding='1' cellspacing='1' align='center' class='tabela'>";
		echo "<tr class='titulo_coluna'>";
			echo "<td nowrap align='left'>Posto</td>";
			echo "<td nowrap>Pa�s</td>";
			echo "<td nowrap>Total OS</td>";
			echo "<td nowrap>Menor 20h</td>";
			echo "<td nowrap>% Menor 20h</td>";
			echo "<td nowrap>Maior 20h</td>";
			echo "<td nowrap>% Maior 20h</td>";
		echo "</tr>";

		for($i=0; $i<pg_numrows($res); $i++){
			$posto                      = pg_result($res,$i,posto);
			$pais                       = pg_result($res,$i,pais);
			$total_os                   = pg_result($res,$i,total_os);
			$menor_dezesseis = pg_result($res,$i,menor_dezesseis);
			$maior_dezesseis            = pg_result($res,$i,maior_dezesseis);

			$per_menor_dezesseis = ($menor_dezesseis/$total_os) * 100;
			$per_maior_dezesseis = ($maior_dezesseis/$total_os) * 100;

			$per_menor_dezesseis = number_format($per_menor_dezesseis,2,".",",");
			$per_maior_dezesseis = number_format($per_maior_dezesseis,2,".",",");

			if(strlen($total_os)==0)        $total_os = 0;
			if(strlen($menor_dezesseis)==0) $menor_dezesseis = 0;
			if(strlen($maior_dezesseis)==0) $maior_dezesseis = 0;

			$cor = ($i % 2) ? "#F7F5F0" : "#F1F4FA";

			if(strlen($posto)>0){
				$sqlN = "SELECT tbl_posto_fabrica.codigo_posto,
								tbl_posto.nome
						 FROM tbl_posto
						 JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_posto.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
						 WHERE tbl_posto.posto = $posto";
				$resN = pg_exec($con,$sqlN);

				if(pg_numrows($resN)>0){
					$codigo_posto = pg_result($resN,0,codigo_posto);
					$posto_nome   = pg_result($resN,0,nome);
				}
			}

			echo "<tr bgcolor='$cor'>";
				echo "<td nowrap align='left'>$codigo_posto - $posto_nome</td>";
				echo "<td nowrap align='center'>$pais</td>";
				echo "<td nowrap align='center'>$total_os</td>";
				echo "<td nowrap align='center'>$menor_dezesseis</td>";
				echo "<td nowrap align='center'>$per_menor_dezesseis</td>";
				echo "<td nowrap align='center'>$maior_dezesseis</td>";
				echo "<td nowrap align='center'>$per_maior_dezesseis</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "<br>";

	##### PAGINA��O - IN�CIO #####
	echo "<br>";
	echo "<div>";
	if($pagina < $max_links) $paginacao = pagina + 1;
	else                     $paginacao = pagina;

	// pega todos os links e define que 'Pr�xima' e 'Anterior' ser�o exibidos como texto plano
	@$todos_links		= $mult_pag->Construir_Links("strings", "sim");

	// fun��o que limita a quantidade de links no rodape
	$links_limitados	= $mult_pag->Mostrar_Parte($todos_links, $coluna, $max_links);

	for ($n = 0; $n < count($links_limitados); $n++) {
		echo "<font color='#DDDDDD'>".$links_limitados[$n]."</font>&nbsp;&nbsp;";
	}

	echo "</div>";

	$resultado_inicial = ($pagina * $max_res) + 1;
	$resultado_final   = $max_res + ( $pagina * $max_res);
	$registros         = $mult_pag->Retorna_Resultado();

	$valor_pagina   = $pagina + 1;
	$numero_paginas = intval(($registros / $max_res) + 1);

	if ($valor_pagina == $numero_paginas) $resultado_final = $registros;

	if ($registros > 0){
		echo "<br>";
		echo "<div>";
		echo "Resultados de <b>$resultado_inicial</b> a <b>$resultado_final</b> do total de <b>$registros</b> registros.";
		echo "<font color='#cccccc' size='1'>";
		echo " (P�gina <b>$valor_pagina</b> de <b>$numero_paginas</b>)";
		echo "</font>";
		echo "</div>";
	}
	##### PAGINA��O - FIM #####
	}else{
		echo "<P>N�o foram Encontrados Resultados para esta Pesquisa</P>";
	}
}

?>


<? include "rodape.php" ?>