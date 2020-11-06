<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
$admin_privilegios="financeiro";
include 'autentica_admin.php';

$data_inicial = $_REQUEST['data_inicial'];
$data_final   = $_REQUEST['data_final'];
$produto      = $_REQUEST['produto'];
$estado       = $_REQUEST['estado'];
$familia      = $_REQUEST['familia'];
$posto        = $_REQUEST['posto'];

if($login_fabrica == 24){
    $matriz_filial = $_REQUEST['matriz_filial'];
    if(strlen($matriz_filial)>0){
        $cond_matriz_filial = " AND substr(tbl_os.serie,length(tbl_os.serie) - 1, 2) = '$matriz_filial' ";
    }
}

$sql = "SELECT descricao FROM tbl_familia WHERE familia = $familia";

$res = pg_query($con,$sql);
$descricao_produto = pg_fetch_result($res,0,descricao);

$aux_data_inicial = substr($data_inicial,8,2) . "/" . substr($data_inicial,5,2) . "/" . substr($data_inicial,0,4);
$aux_data_final   = substr($data_final,8,2)   . "/" . substr($data_final,5,2)   . "/" . substr($data_final,0,4);

$title = "RELAT�RIO DE QUEBRA DE REFER�NCIA �NICA";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE><? echo $title; ?></TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">

<style>
.Titulo {
	text-align: center;
	font-family: Arial;
	font-size: 10px;
	font-weight: bold;
	color: #FFFFFF;
	background-color: #485989;
}
.Conteudo {
	font-family: Arial;
	font-size: 10px;
	font-weight: normal;
}
.ConteudoBranco {
	font-family: Arial;
	font-size: 9px;
	color:#FFFFFF;
	font-weight: normal;
}
.Mes{
	font-size: 9px;
}
.Caixa{
	BORDER-RIGHT: #6699CC 1px solid; 
	BORDER-TOP: #6699CC 1px solid; 
	FONT: 8pt Arial ;
	BORDER-LEFT: #6699CC 1px solid; 
	BORDER-BOTTOM: #6699CC 1px solid; 
	BACKGROUND-COLOR: #FFFFFF;
}
.Exibe{
	font-family: Arial, Helvetica, sans-serif;
	font-size:11px;
	font-weight: none;
	color: #000000;
	text-align: center;
}
.Erro{
	BORDER-RIGHT: #990000 1px solid; 
	BORDER-TOP: #990000 1px solid; 
	FONT: 10pt Arial ;
	COLOR: #ffffff;
	BORDER-LEFT: #990000 1px solid; 
	BORDER-BOTTOM: #990000 1px solid; 
	BACKGROUND-COLOR: #FF0000;
}
</style>


</HEAD>

<BODY>
<?

echo "<div align='center'><b>$title </b>";
echo "<span class='Conteudo'><br>De $aux_data_inicial at� $aux_data_final</B>";
echo " Fam�lia: <b>$descricao_produto </b></span></div><br />";

$cond_1 = " 1=1 ";
$cond_2 = " 1=1 ";
$cond_3 = " 1=1 ";
$cond_4 = " 1=1 ";

if (strlen ($estado)  > 0) $cond_2    = " tbl_posto.estado = '$estado' ";
if (strlen ($posto)   > 0) $cond_3    = " tbl_posto.posto  = $posto ";

    $sql = "
            SELECT
                COUNT(*) AS qtde,
                SUM(tbl_os.pecas) as pecas,
                SUM(tbl_os.mao_de_obra) as mao_de_obra,
                tbl_produto.referencia_fabrica as referencia,
                tbl_produto.familia
                INTO TEMP tmp_os_familia_{$familia}
            FROM tbl_os
                JOIN tbl_os_extra   ON tbl_os.os = tbl_os_extra.os and tbl_os_extra.i_fabrica = $login_fabrica
                JOIN tbl_posto      ON tbl_os.posto = tbl_posto.posto
                JOIN tbl_extrato    ON tbl_extrato.extrato = tbl_os_extra.extrato AND tbl_extrato.fabrica = $login_fabrica
                JOIN tbl_produto    ON tbl_produto.produto = tbl_os.produto AND tbl_produto.fabrica_i = $login_fabrica
            WHERE (tbl_os.status_os_ultimo NOT IN (13,15) OR tbl_os.status_os_ultimo IS NULL)
                AND tbl_os.excluida IS NOT TRUE
                AND tbl_os.fabrica = 24
                AND tbl_extrato.data_geracao BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59'
                AND tbl_posto.pais='BR'
				AND $cond_2
				AND $cond_3
				AND $cond_4
                $cond_matriz_filial
            GROUP BY tbl_produto.referencia_fabrica, tbl_produto.familia
            ORDER BY pecas DESC , qtde DESC;
            
            SELECT * FROM tmp_os_familia_{$familia} WHERE familia = {$familia};";


$res = pg_query($con, $sql);
//echo nl2br($sql)."<br>";

$sql = "SELECT SUM(mao_de_obra) AS total_mao_de_obra FROM tmp_os_familia_{$familia} WHERE familia = {$familia};";
$res_mao_de_obra = pg_query($con, $sql);

if(pg_num_rows($res_mao_de_obra) > 0){
	$total_mao_de_obra = pg_fetch_result($res_mao_de_obra,0,'total_mao_de_obra');
	
	echo  "<table border='1' cellpadding='2' cellspacing='0' style='border-collapse: collapse' bordercolor='#d2e4fc'  align='center' width='500'>";
        echo  "<tr class='Titulo' height='25'>";
            echo  "<td colspan='2' ><b>CUSTO TOTAL COM A REFER�NCIA �NICA</b></td>";
        echo "</tr>";
	echo "<tr bgcolor='#F1F4FA' class='Exibe'>
            <td align='center'>TOTAL DE M�O DE OBRA</td>
            <td align='right'><b>R$ ".number_format($total_mao_de_obra,2,",",".")."</td>
          </tr>";
}

if(pg_num_rows($res) > 0){
    $total_mao_de_obra = null;

	for ($x = 0; $x < pg_num_rows($res); $x++) {
	        $mao_de_obra      = pg_fetch_result($res,$x,'mao_de_obra');
	        $total_ocorrencia += pg_fetch_result($res,$x,'qtde');
        	$total_mao_de_obra += $mao_de_obra;
	}

	$total_final = $total_mao_de_obra + $total_pecas;

	echo "<tr bgcolor='#F1F4FA' class='Exibe'><td align='center'>TOTAL DE CUSTO DE PE�AS</td> <td align='right'><b>R$ ".number_format($total_pecas,2,",",".")."</td></tr>";
	echo "<tr bgcolor='#d9e2ef' class='Exibe'><td align='center'>TOTAL</td><td align='right'><b>R$ ".number_format($total_final,2,",",".")."</td></tr>";
	echo "</table><br>";

	echo  "<table border='1' cellpadding='2' cellspacing='0' style='border-collapse: collapse' bordercolor='#d2e4fc'  align='center' width='600'>";
        echo  "<tr class='Titulo'  height='25'>";
            echo  "<td>Refer�ncia �nica</td>";
            echo  "<td>Ocorr�ncia</td>";
            echo  "<td>R$ M.Obra</td>";
            echo  "<td>R$ Pe�as</td>";
            echo  "<td>R$ Total</td>";
            echo  "<td>% M�o de Obra</td>";
            echo  "<td>% Ocorrencia</td>";
        echo  "</tr>";


	
        for($i=0; $i < pg_num_rows($res); $i++){
            $referencia     = pg_fetch_result($res,$i,'referencia');
            $qtde           = pg_fetch_result($res,$i,'qtde');
            $mao_de_obra    = pg_fetch_result($res,$i,'mao_de_obra');

            $total = $mao_de_obra + $pecas;

            if ($total_mao_de_obra > 0) {
                $porcentagem = (($mao_de_obra * 100) / $total_mao_de_obra);
            }

            if ($total_ocorrencia > 0) {
                $porcentagem_ocorrencia = (($qtde * 100) / $total_ocorrencia);
            }
            $cor = ($cor == "#F1F4FA") ? "#F7F5F0" : "#F1F4FA";
            echo "<tr class='Conteudo' bgcolor='$cor'>";
                if($login_fabrica == 24){
                    echo "<td><a href='relatorio_field_call_rate_familia_produto_custo.php?familia=$familia&data_inicial=$data_inicial&data_final=$data_final&estado={$estado}&referencia={$referencia}&matriz_filial=$matriz_filial' target='_blank' style='text-decoration: none; font-weight: bold; color: #000;'>$referencia</a></td>";
                }else{
                    echo "<td><a href='relatorio_field_call_rate_familia_produto_custo.php?familia=$familia&data_inicial=$data_inicial&data_final=$data_final&estado={$estado}&referencia={$referencia}' target='_blank' style='text-decoration: none; font-weight: bold; color: #000;'>$referencia</a></td>";
                }
                

                echo "<td align='left'>$qtde</td>";
                echo "<td align='right' width='75'>R$ ". number_format($mao_de_obra,2,",",".") ."</td>";
                echo "<td align='right' width='75'>R$ ". number_format($pecas,2,",",".") ."</td>";
                echo "<td align='right'>R$ ". number_format($total,2,",",".") ."</td>";
                echo "<td align='right'>". number_format($porcentagem,2,",",".") ." %</td>";
                echo "<td align='right'>". number_format($porcentagem_ocorrencia,2,",",".") ." %</td>";
            echo "</tr>";
        }
	echo "</table>";
} ?>
</body>
</html>
