<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'includes/funcoes.php';

$admin_privilegios="gerencia";
include 'autentica_admin.php';

$layout_menu = "gerencia";
$title = "RELAT�RIO DE QUEBRA: POSTOS NOS �LTIMOS 12 MESES";

?>

<style type="text/css">
.Titulo {
	text-align: center;
	font-family: Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: bold;
	color: #FFFFFF;
	background-color: #485989;
}
.Conteudo {
	font-family: Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: normal;
}
.Mes{
	font-size: 9px;
}
</style>

<?


include "cabecalho.php";

$data_serv  = mktime (date("H"), date("i"), date("s"),date("m")-12  , date("d"), date("Y"));
$data_inicial = strftime ("%Y-%m-%d", $data_serv);

$xdata_inicial = $data_inicial .' 00:00:00';
$xdata_final = date("Y-m-d 23:59:59");

if (strlen($_GET['familia']) > 0){
	$produto = trim($_GET['produto']);
}

//--==== Otimiza��o para rodar o relat�rio Anual =============================================
echo "<TABLE  border='0' cellspacing='0' cellpadding='2' align='center' name='relatorio' id='relatorio'  style=' border:#485989 1px solid; background-color: #e6eef7 '>";
echo "<tr>";
echo "<td colspan='12' class='Titulo'>PROCESSANDO OS SEGUINTES MESES</td>";
echo "</tr>";
echo "<tr>";
for($x=0;$x<12;$x++){
	$data_serv  = mktime (date("H"), date("i"), date("s"),date("m")-12 +$x  , date("d"), date("Y"));
	$xMES = strftime ("%m/%Y", $data_serv);
	echo "<td width='40' height='40' class='Conteudo' bgcolor='#FFFFDD' id='mes_$x'>$xMES</td>";
}
echo "</tr></table>";
flush();
for($x=0;$x<12;$x++){
	$data_serv  = mktime (date("H"), date("i"), date("s"),date("m")-12 +$x  , date("d"), date("Y"));
	$data_inicial = strftime ("%Y-%m-01", $data_serv);
	$xdata_inicial = $data_inicial .' 00:00:00';

	$sql = "SELECT ('$data_inicial'::DATE + INTERVAL'1 MONTH'- INTERVAL'1 day')::DATE || ' 23:59:59';";
	$res = pg_exec($con,$sql);
	$xdata_final = pg_result($res,0,0);


	$aux = $login_admin;
	if($x==0){
	$sql = "SELECT tbl_os_extra.os , tbl_extrato.data_geracao
		INTO TEMP tmp_rqpo_$aux
		FROM tbl_os_extra
		JOIN tbl_extrato USING (extrato)
		WHERE tbl_extrato.fabrica = $login_fabrica
		AND   tbl_extrato.data_geracao BETWEEN '$xdata_inicial' AND '$xdata_final'
		AND   tbl_extrato.liberado IS NOT NULL;
	
		CREATE INDEX tmp_rqpo_OS_$aux ON tmp_rqpo_$aux(os);";
	}else{
		$sql = "INSERT INTO tmp_rqpo_$aux (os,data_geracao) 
			SELECT tbl_os_extra.os , tbl_extrato.data_geracao
			FROM tbl_os_extra
			JOIN tbl_extrato USING (extrato)
			WHERE tbl_extrato.fabrica = $login_fabrica
			AND   tbl_extrato.data_geracao BETWEEN '$xdata_inicial' AND '$xdata_final'
			AND   tbl_extrato.liberado IS NOT NULL;";
	}
	$res = pg_exec($con,$sql);
	echo "<script language='javascript'>document.getElementById('mes_$x').style.background = '#D7FFE1'</script>";
	flush();
}
//--========================================================================================


$sql = "SELECT tbl_posto.nome,tbl_posto.posto,to_char(fcr.data_geracao,'YYYY-MM')As data_geracao, COUNT(*) AS qtde
		FROM tbl_os
		JOIN tmp_rqpo_$aux fcr ON tbl_os.os = fcr.os
		JOIN tbl_posto         ON tbl_os.posto = tbl_posto.posto
		JOIN tbl_produto       ON tbl_produto.produto = tbl_os.produto
		WHERE tbl_os.excluida IS NOT TRUE
		AND tbl_produto.produto = $produto
		GROUP BY tbl_posto.nome,tbl_posto.posto, to_char(data_geracao,'YYYY-MM')
		ORDER BY tbl_posto.nome,tbl_posto.posto,data_geracao";
//echo nl2br($sql);exit;
$res = pg_exec($con,$sql);

if (pg_numrows($res) > 0) {
	
	$sql2= "SELECT  tbl_linha.nome                             ,
					tbl_familia.descricao AS familia_descricao   ,
					tbl_produto.descricao AS produto_descricao 
			FROM  tbl_produto
			JOIN  tbl_familia ON tbl_familia.familia = tbl_produto.familia
			JOIN  tbl_linha   ON tbl_linha.linha     = tbl_produto.linha
			WHERE tbl_produto.produto = $produto";

	$res2 = pg_exec($con,$sql2);
	$nome                      = trim(pg_result($res2,0,nome));
	$familia_descricao         = trim(pg_result($res2,0,familia_descricao));
	$produto_descricao         = trim(pg_result($res2,0,produto_descricao));

	echo "<table border='2' cellpadding='2' cellspacing='0' style='border-collapse: collapse' bordercolor='#d2e4fc' width='700' align='center'>";
	echo "<tr class='Titulo'>";
	echo "<td colspan='14' align='left'>� $nome � $familia_descricao � $produto_descricao</td>";
	echo "</tr>";
	echo "<tr class='Titulo'>";
	echo "<td width='150' rowspan='2'>Posto</td>";
	echo "<td colspan='12'>Meses</td>";
	echo "<td rowspan='2' class='Mes'>Total Ano</td>";
	echo "</tr><tr class='Titulo'>";
	for($x=0;$x<12;$x++){

		$data_serv  = mktime (date("H"), date("i"), date("s"),date("m")- 12 + $x , date("d"), date("Y"));
		$mes[$x] = strftime ("%m/%Y", $data_serv);
		
		echo "<td class='Mes'>$mes[$x]</td>";

	}
	echo "</tr>";

	$familia=0;
	$x=0;
	$y=0;
	//zerando todos arrays
	$posto_total=0;
	$qtde_mes =  array();

	for ($i=0; $i<pg_numrows($res); $i++){
		$nome         = trim(pg_result($res,$i,nome));
		$posto           = trim(pg_result($res,$i,posto));
		if($posto_anterior<>$posto){
			$qtde_mes[$posto_total][0]  = 0;
			$qtde_mes[$posto_total][1]  = 0;
			$qtde_mes[$posto_total][2]  = 0;
			$qtde_mes[$posto_total][3]  = 0;
			$qtde_mes[$posto_total][4]  = 0;
			$qtde_mes[$posto_total][5]  = 0;
			$qtde_mes[$posto_total][6]  = 0;
			$qtde_mes[$posto_total][7]  = 0;
			$qtde_mes[$posto_total][8]  = 0;
			$qtde_mes[$posto_total][9]  = 0;
			$qtde_mes[$posto_total][10] = 0;
			$qtde_mes[$posto_total][11] = 0;
			$qtde_mes[$posto_total][12] = $nome;
			$x=0;
			$posto_anterior=$posto;
			$posto_total = $posto_total+1;
		}
	}
	for ($i=0; $i<pg_numrows($res); $i++){

		$posto         = trim(pg_result($res,$i,posto));
		$nome       = trim(pg_result($res,$i,nome));
		$data_geracao = trim(pg_result($res,$i,data_geracao));
		$qtde            = trim(pg_result($res,$i,qtde));

		$xdata_geracao = explode('-',$data_geracao);
		$data_geracao = $xdata_geracao[1].'/'.$xdata_geracao[0];

		if($posto_anterior<>$posto){

//IMPRIME O VETOR COM OS DOZE MESES E LOGO AP�S PULA LINHA E ESCREVE O NOVO posto
			if($i<>0 AND $posto_anterior<>$posto ){
				
				for($a=0;$a<12;$a++){			//imprime os doze meses
					echo "<td bgcolor='$cor' title='".$mes[$a]."'>";
					if ($qtde_mes[$y][$a]>0)
						echo "<font color='#000000'><b>".$qtde_mes[$y][$a];
					else echo "<font color='#999999'> ";

					echo "</td>";
					$total_ano = $total_ano + $qtde_mes[$y][$a];
					if($a==11) {
						echo "<td bgcolor='$cor' >$total_ano</td>";
						echo "</tr>";
					}	// se for o ultimo mes quebra a linha
				}

				$y=$y+1;						// usado para indica��o de posto
			}

			$total_ano = 0;
			if($cor=="#F1F4FA")$cor = '#F7F5F0';
			else               $cor = '#F1F4FA';

			echo "<tr class='Conteudo'align='center'>";
			echo "<td bgcolor='$cor' width='150' height='40' class='Mes'>$nome</td>";

			$x=0; //ZERA OS MESES
			$posto_anterior=$posto; 
		}

		while($data_geracao<>$mes[$x]){ //repete o lup at� que o mes seja igual e anda um mes.
			$x=$x+1;
		};

		if($data_geracao==$mes[$x]){
			$qtde_mes[$y][$x] = $qtde;
		}

		$x=$x+1; //ap�s armazenar o valor no mes correspondente anda 1 mes no vetor
		if($i==(pg_numrows($res)-1)){
			for($a=0;$a<12;$a++){			//imprime os doze meses
				echo "<td bgcolor='$cor' title='".$mes[$a]."'>";
				if ($qtde_mes[$y][$a]>0)
					echo "<font color='#000000'><b>".$qtde_mes[$y][$a];
				else echo "<font color='#999999'> ";

				echo "</td>";
				$total_ano = $total_ano + $qtde_mes[$y][$a];
				if($a==11) {
					echo "<td bgcolor='$cor' >$total_ano</td>";
					echo "</tr>";
				}	// se for o ultimo mes quebra a linha
			}
		}
	}
	echo "</table>";
/*	for($i=0; $i<$posto_total ; $i++){
		for($j=0; $j<13 ; $j++)echo $qtde_mes[$i][$j]." - ";
	echo "<br><br>";
	}
*/
}
echo " <a href='javascript:history.back()'>Voltar</a>";
echo '<br>';
include 'rodape.php';
?>