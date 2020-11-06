<?
#------ OS SEM PE�A
#------ OS EM ABERTO
#========== os_aberto.php


include "dbconfig.php";
include "includes/dbconnect-inc.php";

include "funcoes.php";

$admin_privilegios = "gerencia,call_center";
include "autentica_admin.php";


$erro = "";

if (strlen($_POST["acao"]) > 0 ) $acao = strtoupper($_POST["acao"]);
if (strlen($_GET["acao"]) > 0 )  $acao = strtoupper($_GET["acao"]);

if (strlen($acao) > 0 && $acao == "PESQUISAR") {
	if (strlen(trim($_POST["data_inicial"])) > 0) $x_data_inicial = trim($_POST["data_inicial"]);
	if (strlen(trim($_GET["data_inicial"])) > 0)  $x_data_inicial = trim($_GET["data_inicial"]);
	
	$aux_data_inicial = str_replace("/","",$x_data_inicial);
	$aux_data_inicial = str_replace("-","",$aux_data_inicial);
	$aux_data_inicial = str_replace(".","",$aux_data_inicial);
	$aux_data_inicial = fnc_so_numeros($aux_data_inicial);
	
	if (strlen($aux_data_inicial) < 8) $erro = "Data inicial em formato inv�lido";
	
	if (strlen($erro) == 0){
		$x_data_inicial = fnc_formata_data_pg($x_data_inicial);
		
		if (strlen(trim($_POST["data_final"])) > 0) $x_data_final   = trim($_POST["data_final"]);
		if (strlen(trim($_GET["data_final"])) > 0) $x_data_final = trim($_GET["data_final"]);
		
		$x_data_final   = fnc_formata_data_pg($x_data_final);
		
		if (strlen($x_data_inicial) > 0 && $x_data_inicial != "null") {
			$x_data_inicial = str_replace("'", "", $x_data_inicial);
			$dia_inicial = substr($x_data_inicial, 8, 2);
			$mes_inicial = substr($x_data_inicial, 5, 2);
			$ano_inicial = substr($x_data_inicial, 0, 4);
			$data_inicial = $dia_inicial . "/" . $mes_inicial . "/" . $ano_inicial;
		}else{
			$erro .= " Informe a Data Inicial para realizar a pesquisa. ";
		}
		
		if (strlen($x_data_final) > 0 && $x_data_final != "null") {

			$aux_data_final = str_replace("/","",$x_data_final);
			$aux_data_final = str_replace("-","",$aux_data_final);
			$aux_data_final = str_replace(".","",$aux_data_final);
			$aux_data_final = fnc_so_numeros($aux_data_final);
			
			if (strlen($aux_data_final) < 8) $erro = "Data final em formato inv�lido";
			
			if (strlen($erro) == 0){
				$x_data_final = str_replace("'", "", $x_data_final);
				$dia_final = substr($x_data_final, 8, 2);
				$mes_final = substr($x_data_final, 5, 2);
				$ano_final = substr($x_data_final, 0, 4);
				$data_final = $dia_final . "/" . $mes_final . "/" . $ano_final;
			}
		}else{
			$erro .= " Informe a Data Inicial para realizar a pesquisa. ";
		}
		
		if (strlen(trim($_POST["status"])) > 0) $status = trim($_POST["status"]);
		if (strlen(trim($_GET["status"])) > 0)  $status = trim($_GET["status"]);
		
		$link_status = "http://" . $HTTP_HOST . $REQUEST_URI . "?data_inicial=" . $_POST["data_inicial"] . "&data_final=" . $_POST["data_final"] . "&acao=PESQUISAR";
		setcookie("LinkStatus", $link_status);
	}
}

$layout_menu = "os";
$title = "Rela��o de Status da Ordem de Servi�o";

include "cabecalho.php";


#--------- TULIO 19/04 - Acertar SQL , Restringir a no maximo 1 mes - Colocar mais parametros para restringir
// somente Fabiola
//if ($ip <> '12.148.189.25' AND $ip <> '201.0.9.216'){
//	echo "<h1>Programa em Manuten��o</h1>";
//	exit;
//}
?>

<style type="text/css">
.Titulo {
	text-align: center;
	font-family: Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif;
	font-size: 10 px;
	font-weight: bold;
	color: #FFFFFF;
	background-color: #596D9B
}
.Conteudo {
	font-family: Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif;
	font-weight: normal;
	font-size: 10px;
	background-color:#eeeeee
}
</style>

<script language="javascript" src="js/cal2.js"></script>
<script language="javascript" src="js/cal_conf2.js"></script>

<script language="javascript">

function Abre(posto,nome){
	janela = window.open("os_atrasada.php?posto=" + posto + '&nome=' + nome , 'Acoes','width=700,height=500,scrollbars=yes');
	janela.focus();
}

</script>


<br>

<? if (strlen($erro) > 0) { ?>
<table width="600" border="0" cellspacing="0" cellpadding="2" align="center" class="Error">
	<tr>
		<td><?echo $erro?></td>
	</tr>
</table>
<br>
<? } 


#------- TULIO Mostar em destaque OS SEM PE�AS ou AGUARDANDO PE�AS 
$dias = 10;
if ($login_fabrica == 6) $dias = 10; 

$sql = "SELECT  tbl_posto_fabrica.codigo_posto ,
				tbl_posto.posto                ,
				tbl_posto.nome                 ,
				tbl_posto.estado               ,
				tbl_posto.fone                 ,
				tbl_posto.email                ,
				antigas.qtde                   ,
				(SELECT TO_CHAR (tbl_os.data_abertura,'DD/MM/YYYY') FROM tbl_os 
					WHERE tbl_os.posto = tbl_posto.posto 
					AND tbl_os.fabrica = $login_fabrica 
					AND tbl_os.excluida IS NOT TRUE
					AND tbl_os.data_fechamento IS NULL 
					AND tbl_os.data_abertura BETWEEN CURRENT_DATE - INTERVAL '3 months' AND CURRENT_DATE - INTERVAL '$dias days'
					ORDER BY data_abertura LIMIT 1) AS mais_antiga ,
				(SELECT tbl_os.data_abertura FROM tbl_os 
					WHERE tbl_os.posto = tbl_posto.posto 
					AND tbl_os.fabrica = $login_fabrica 
					AND tbl_os.excluida IS NOT TRUE
					AND tbl_os.data_fechamento IS NULL 
					AND tbl_os.data_abertura BETWEEN CURRENT_DATE - INTERVAL '3 months' AND CURRENT_DATE - INTERVAL '$dias days'
					ORDER BY data_abertura LIMIT 1) AS mais_antiga_data
		FROM tbl_posto
		JOIN tbl_posto_fabrica ON tbl_posto.posto = tbl_posto_fabrica.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
		JOIN (SELECT posto , COUNT(*) AS qtde
					FROM tbl_os 
					WHERE tbl_os.fabrica = $login_fabrica
					AND   tbl_os.excluida IS NOT TRUE
					AND tbl_os.data_fechamento IS NULL 
					AND tbl_os.data_abertura BETWEEN CURRENT_DATE - INTERVAL '3 months' AND CURRENT_DATE - INTERVAL '$dias days'
					GROUP BY tbl_os.posto
		) antigas ON tbl_posto.posto = antigas.posto
		ORDER BY mais_antiga_data, tbl_posto.nome";


//IGOR - HD 11468 - PARA A TECTOY FOI CRIADO UM PL PARA GERAR OS DADOS DE MADRUGADA tectoy/gera-relatorio-os-aberta.pl
if ($login_fabrica == 6) {
	$sql = "
		/*FAZ JOIN COM A TABELA TEMPORARIA QUE RODA DE MADRUGADA*/
		SELECT tbl_os.os,
		 tbl_os.posto , 
		 tbl_os.data_abertura
		into temp table tmp_os_aberta_tectoy_final
		FROM tbl_os 
		join tmp_os_aberta_tectoy using(os)
		WHERE tbl_os.fabrica = $login_fabrica
		 AND   tbl_os.excluida IS NOT TRUE
		 AND tbl_os.data_fechamento IS NULL 
		 AND tbl_os.data_abertura BETWEEN CURRENT_DATE - INTERVAL '3 months' AND CURRENT_DATE - INTERVAL '9 days' ;

		/*RODA A CONSULTA DOS DADOS PARA IMPRIMIR NA TELA*/
		SELECT  tbl_posto_fabrica.codigo_posto ,
			  tbl_posto.posto                ,
			  tbl_posto.nome                 ,
			  tbl_posto.estado               ,
			  tbl_posto.fone                 ,
			  tbl_posto.email                ,
			  antigas.qtde                   ,
			  (
			   SELECT TO_CHAR (tmp_os_aberta_tectoy_final.data_abertura,'DD/MM/YYYY') 
			   FROM tmp_os_aberta_tectoy_final
			   WHERE tmp_os_aberta_tectoy_final.posto = tbl_posto.posto 
			   ORDER BY data_abertura 
			   LIMIT 1
			  ) AS mais_antiga ,
			  (
			   SELECT tmp_os_aberta_tectoy_final.data_abertura 
			   FROM tmp_os_aberta_tectoy_final 
			   WHERE tmp_os_aberta_tectoy_final.posto = tbl_posto.posto 
			   ORDER BY data_abertura 
			   LIMIT 1
			  ) AS mais_antiga_data
		FROM tbl_posto
		JOIN tbl_posto_fabrica ON tbl_posto.posto = tbl_posto_fabrica.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
		JOIN (
			 SELECT posto , 
			  COUNT(*) AS qtde
			 FROM tmp_os_aberta_tectoy_final
			 GROUP BY tmp_os_aberta_tectoy_final.posto
		) antigas ON tbl_posto.posto = antigas.posto
		ORDER BY mais_antiga_data, tbl_posto.nome";
}

	$res = pg_exec ($con,$sql);

	echo "<table width='600' border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#000000'>";
	echo "<TR class='Titulo'>";
	echo	"<TD colspan='8' nowrap><br>&nbsp;OS�s ABERTAS NOS �LTIMOS 3 MESES COM DATA DE ABERTURA MAIOR QUE $dias DIAS&nbsp;<br>&nbsp;</TD>";
	echo "</TR>";

	echo "<TR class='Conteudo' >";
	echo "<TD><B>POSTO</B></TD>";
	echo "<TD><B>NOME</B></TD>";
	echo "<TD><B>&nbsp;ESTADO&nbsp;</B></TD>";
	echo "<TD><B>FONE</B></TD>";
	echo "<TD><B>EMAIL</B></TD>";
	echo "<TD><B>&nbsp;QTDE&nbsp;</B></TD>";
	echo "<TD nowrap><B>&nbsp; OS MAIS ANTIGA &nbsp;</B></TD>";
	echo "</TR>";


		for ($i = 0 ; $i < pg_numrows ($res) ; $i++) {
	$posto=pg_result($res,$i,posto);
	$nome_posto = trim(pg_result ($res,$i,nome));
	echo "<tr class='Conteudo' height='15' align='left' style='cursor: hand;'><a href='javascript:Abre(\"$posto\",\"$nome_posto\");'>";
	echo "<td>&nbsp;";
	echo pg_result ($res,$i,codigo_posto);
	echo "&nbsp;</td>";

	echo "<td>&nbsp;";
	echo pg_result ($res,$i,nome);
	echo "</td>";

	echo "<td>&nbsp;";
	echo pg_result ($res,$i,estado);
	echo "</td>";

	echo "<td>&nbsp;";
	echo pg_result ($res,$i,fone);
	echo "</td>";

	echo "<td>&nbsp;";
	echo pg_result ($res,$i,email);
	echo "</td>";

	echo "<td>&nbsp;";
	echo pg_result ($res,$i,qtde);
	echo "</td>";

	echo "<td>&nbsp;";
	echo pg_result ($res,$i,mais_antiga);
	echo "</td>";

	echo "</a></TR>";

}
echo "</table><br>";



exit;

/*
$sql = "SELECT  tbl_posto_fabrica.codigo_posto ,
				tbl_posto.nome                 ,
				tbl_posto.estado               ,
				tbl_posto.fone                 ,
				tbl_posto.email                ,
				tbl_os.sua_os                  ,
				TO_CHAR (tbl_os.data_abertura,'DD/MM/YYYY') AS data_abertura ,
				tbl_produto.referencia         ,
				tbl_produto.descricao          
		FROM tbl_posto
		JOIN tbl_posto_fabrica ON tbl_posto.posto = tbl_posto_fabrica.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
		JOIN tbl_os ON tbl_posto.posto = tbl_os.posto
		JOIN tbl_produto ON tbl_os.produto = tbl_produto.produto
		WHERE tbl_os.fabrica = $login_fabrica
		AND   tbl_os.data_abertura <= CURRENT_DATE - INTERVAL '20 days'
		AND   tbl_os.data_fechamento IS NULL
		AND   tbl_os.excluida IS NOT TRUE
		AND   tbl_os.data_abertura > CURRENT_DATE - INTERVAL '3 months'
		ORDER BY tbl_posto.nome, tbl_os.data_abertura";

$res = pg_exec ($con,$sql);

echo "<table style='font-family: verdana ; font-size: 10px' >";
for ($i = 0 ; $i < pg_numrows ($res) ; $i++) {
	echo "<tr>";
	echo "<td>";
	echo pg_result ($res,$i,codigo_posto);
	echo "</td>";

	echo "<td>";
	echo pg_result ($res,$i,nome);
	echo "</td>";

	echo "<td>";
	echo pg_result ($res,$i,estado);
	echo "</td>";

	echo "<td>";
	echo pg_result ($res,$i,fone);
	echo "</td>";

	echo "<td>";
	echo pg_result ($res,$i,email);
	echo "</td>";

	echo "<td>";
	echo pg_result ($res,$i,sua_os);
	echo "</td>";

	echo "<td>";
	echo pg_result ($res,$i,data_abertura);
	echo "</td>";

	echo "<td>";
	echo pg_result ($res,$i,referencia);
	echo "</td>";


	echo "<td>";
	echo pg_result ($res,$i,descricao);
	echo "</td>";

}
echo "</table>";


*/

include "rodape.php";
?>
