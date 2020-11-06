<?
include "dbconfig.php";
include "includes/dbconnect-inc.php";
include "autentica_usuario.php";

if($login_fabrica <> 1) {
	include("menu_os.php");
	exit;
}

include "funcoes.php";

$erro = "";

if (strlen($_POST["acao"]) > 0 ) $acao = strtoupper($_POST["acao"]);

if (strlen($acao) > 0) {
	$x_data_inicial = trim($_POST["data_inicial"]);
	$x_data_inicial = fnc_formata_data_pg($x_data_inicial);
	$x_data_final   = trim($_POST["data_final"]);
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
		$x_data_final = str_replace("'", "", $x_data_final);
		$dia_final = substr($x_data_final, 8, 2);
		$mes_final = substr($x_data_final, 5, 2);
		$ano_final = substr($x_data_final, 0, 4);
		$data_final = $dia_final . "/" . $mes_final . "/" . $ano_final;
	}else{
		$erro .= " Informe a Data Inicial para realizar a pesquisa. ";
	}
}

$layout_menu = "os";
$title = "Rela��o de Status da Ordem de Servi�o";

include "cabecalho.php";
?>

<style type="text/css">
.Titulo {
	text-align: center;
	font-family: Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif;
	font-size: 12 px;
	font-weight: bold;
	color: #FFFFFF;
	background-color: #596D9B
}
.Conteudo {
	font-family: Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif;
	font-size: 12 px;
	font-weight: normal;
}
</style>

<script language="javascript" src="js/cal2.js"></script>
<script language="javascript" src="js/cal_conf2.js"></script>

<br>

<? if (strlen($erro) > 0) { ?>
<table width="600" border="0" cellspacing="0" cellpadding="2" align="center" class="Error">
	<tr>
		<td><?echo $erro?></td>
	</tr>
</table>
<br>
<? } ?>

<form name="frm_relatorio" method="post" action="<?echo $PHP_SELF?>">
<input type="hidden" name="acao">
<table width="400" border="0" cellspacing="0" cellpadding="2" align="center">
	<tr class="Titulo">
		<td colspan="4"><b>PESQUISE ENTRE DATAS</b></td>
	</tr>
	<tr class="Conteudo" bgcolor="#D9E2EF">
		<td width="10">&nbsp;</td>
		<td align='left'>Data Inicial</td>
		<td align='left'>Data Final</td>
		<td width="10">&nbsp;</td>
	</tr>
	<tr class="Conteudo" bgcolor="#D9E2EF">
		<td width="10">&nbsp;</td>
		<td>
			<input type="text" name="data_inicial" size="12" maxlength="10" value="<? if (strlen($data_inicial) > 0) echo $data_inicial; else echo "dd/mm/aaaa"; ?>" onclick="javascript: if (this.value == 'dd/mm/aaaa') this.value='';">
			&nbsp;
			<img border="0" src="imagens/btn_lupa.gif" align="absmiddle" onclick="javascript:showCal('DataInicial')" style="cursor: hand;" alt="Clique aqui para abrir o calend�rio">
		</td>
		<td>
			<input type="text" name="data_final" size="12" maxlength="10" value="<? if (strlen($data_final) > 0) echo $data_final; else echo "dd/mm/aaaa"; ?>" onclick="javascript: if (this.value == 'dd/mm/aaaa') this.value='';">
			&nbsp;
			<img border="0" src="imagens/btn_lupa.gif" align="absmiddle" onclick="javascript:showCal('DataFinal')" style="cursor: hand;" alt="Clique aqui para abrir o calend�rio">
		</td>
		<td width="10">&nbsp;</td>
	</tr>
	<tr bgcolor="#D9E2EF">
		<td colspan="4"><img border="0" src="imagens/btn_pesquisar_400.gif" onClick="document.frm_relatorio.acao.value='PESQUISAR'; document.frm_relatorio.submit();" style="cursor: hand;" alt="Preencha as op��es e clique aqui para pesquisar"></td>
	</tr>
</table>

<br>

<?
if (strlen($acao) > 0 && strlen($erro) == 0) {

	##### OS FINALIZADAS #####

	$sql =	"SELECT tbl_posto_fabrica.codigo_posto                                   ,
					tbl_os.os                                                        ,
					tbl_os.sua_os                                                    ,
					TO_CHAR(tbl_os.data_digitacao,'DD/MM/YYYY')    AS data_digitacao ,
					tbl_os.pecas                                                     ,
					tbl_os.mao_de_obra                                               ,
					TO_CHAR(tbl_extrato.aprovado,'DD/MM/YYYY')     AS aprovado       ,
					TO_CHAR(tbl_extrato.data_geracao,'DD/MM/YYYY') AS data_geracao   
			FROM tbl_os
			JOIN tbl_os_extra USING (os)
			JOIN tbl_posto    USING (posto)
			JOIN tbl_posto_fabrica  ON  tbl_posto_fabrica.posto   = tbl_posto.posto
									AND tbl_posto_fabrica.fabrica = $login_fabrica
			LEFT JOIN tbl_extrato ON tbl_extrato.extrato = tbl_os_extra.extrato
			WHERE tbl_os.data_digitacao::date BETWEEN '$x_data_inicial' AND '$x_data_final'
			AND tbl_os.finalizada NOTNULL
			AND tbl_os.posto   = $login_posto
			AND tbl_os.fabrica = $login_fabrica;";
	$res = pg_exec($con,$sql);

	if (pg_numrows($res) > 0) {
		echo "<table width='600' border='1' cellpadding='2' cellspacing='0' style='border-collapse: collapse' bordercolor='#000000'>";
		echo "<tr class='Titulo'>";
		echo "<td colspan='7'>RELA��O DE OS</td>";
		echo "</tr>";
		echo "<tr class='Titulo'>";
		echo "<td>OS</td>";
		echo "<td>DIGITA��O</td>";
		echo "<td>PE�AS</td>";
		echo "<td>M�O-DE-OBRA</td>";
		echo "<td>TOTAL</td>";
		echo "<td>PROTOCOLO</td>";
		echo "<td>STATUS</td>";
		echo "</tr>";

		for ($i = 0 ; $i < pg_numrows($res) ; $i++) {
			$codigo_posto   = trim(pg_result($res,$i,codigo_posto));
			$os             = trim(pg_result($res,$i,os));
			$sua_os         = trim(pg_result($res,$i,sua_os));
			$data_digitacao = trim(pg_result($res,$i,data_digitacao));
			$pecas    = trim(pg_result($res,$i,pecas));
			$mao_de_obra    = trim(pg_result($res,$i,mao_de_obra));
			$total          = $custo_pecas + $mao_de_obra;
			$aprovado       = trim(pg_result($res,$i,aprovado));
			$data_geracao   = trim(pg_result($res,$i,data_geracao));

			$cor = ($i % 2 == 0) ? "#F1F4FA" : "#F7F5F0" ;

			echo "<tr class='Conteudo' bgcolor='$cor'>";
			echo "<td>" . $codigo_posto . $sua_os . "</td>";
			echo "<td align='center'>" . $data_digitacao . "</td>";
			echo "<td align='right'>" . number_format($pecas,2,",",".") . "</td>";
			echo "<td align='right'>" . number_format($mao_de_obra,2,",",".") . "</td>";
			echo "<td align='right'>" . number_format($total,2,",",".") . "</td>";
			echo "<td align='center'>" . $os . "</td>";
			echo "<td align='center'>";
			if (strlen($data_geracao) > 0 AND strlen($aprovado) == 0)       echo "Em aprova��o";
			elseif (strlen($data_geracao) == 0 AND strlen($aprovado) == 0)  echo "Em aprova��o";
			elseif (strlen($aprovado) > 0)                                  echo "Aprovada";
			echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "<br>";
	}

	##### OS N�O FINALIZADAS #####

	$sql =	"SELECT tbl_posto_fabrica.codigo_posto                                   ,
					tbl_os.os                                                        ,
					tbl_os.sua_os                                                    ,
					TO_CHAR(tbl_os.data_digitacao,'DD/MM/YYYY')    AS data_digitacao ,
					tbl_os.pecas                                                     ,
					tbl_os.mao_de_obra                                               ,
					TO_CHAR(tbl_extrato.aprovado,'DD/MM/YYYY')     AS aprovado       ,
					TO_CHAR(tbl_extrato.data_geracao,'DD/MM/YYYY') AS data_geracao   
			FROM tbl_os
			JOIN tbl_os_extra USING (os)
			JOIN tbl_posto    USING (posto)
			JOIN tbl_posto_fabrica  ON  tbl_posto_fabrica.posto   = tbl_posto.posto
									AND tbl_posto_fabrica.fabrica = $login_fabrica
			LEFT JOIN tbl_extrato ON tbl_extrato.extrato = tbl_os_extra.extrato
			WHERE tbl_os.data_digitacao::date BETWEEN '$x_data_inicial' AND '$x_data_final'
			AND tbl_os.finalizada ISNULL
			AND tbl_os.posto   = $login_posto
			AND tbl_os.fabrica = $login_fabrica;";
	$res = pg_exec($con,$sql);

	if (pg_numrows($res) > 0) {
		echo "<table width='600' border='1' cellpadding='2' cellspacing='0' style='border-collapse: collapse' bordercolor='#000000'>";
		echo "<tr class='Titulo'>";
		echo "<td colspan='7'>RELA��O DE OS N�O FINALIZADAS</td>";
		echo "</tr>";
		echo "<tr class='Titulo'>";
		echo "<td>OS</td>";
		echo "<td>DIGITA��O</td>";
		echo "<td>PE�AS</td>";
		echo "<td>M�O-DE-OBRA</td>";
		echo "<td>TOTAL</td>";
		echo "<td>PROTOCOLO</td>";
		echo "<td>STATUS</td>";
		echo "</tr>";

		for ($i = 0 ; $i < pg_numrows($res) ; $i++) {
			$codigo_posto   = trim(pg_result($res,$i,codigo_posto));
			$os             = trim(pg_result($res,$i,os));
			$sua_os         = trim(pg_result($res,$i,sua_os));
			$data_digitacao = trim(pg_result($res,$i,data_digitacao));
			$pecas    = trim(pg_result($res,$i,pecas));
			$mao_de_obra    = trim(pg_result($res,$i,mao_de_obra));
			$total          = $custo_pecas + $mao_de_obra;
			$aprovado       = trim(pg_result($res,$i,aprovado));
			$data_geracao   = trim(pg_result($res,$i,data_geracao));

			$cor = ($i % 2 == 0) ? "#F1F4FA" : "#F7F5F0" ;

			echo "<tr class='Conteudo' bgcolor='$cor'>";
			echo "<td>" . $codigo_posto . $sua_os . "</td>";
			echo "<td align='center'>" . $data_digitacao . "</td>";
			echo "<td align='right'>" . number_format($pecas,2,",",".") . "</td>";
			echo "<td align='right'>" . number_format($mao_de_obra,2,",",".") . "</td>";
			echo "<td align='right'>" . number_format($total,2,",",".") . "</td>";
			echo "<td align='center'>" . $os . "</td>";
			echo "<td align='center'>";
			echo "N�o finalizada";
			//if (strlen($aprovado) == 0 && strlen($data_geracao) == 0) echo "Em aprova��o";
			//else                                                      echo "Aprovada";
			echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "<br>";
	}
}

include "rodape.php"; 
?>