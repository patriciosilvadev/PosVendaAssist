<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include "login_unico_autentica_usuario.php";



$btn_acao = trim ($_POST['btn_acao']);
if (strlen ($btn_acao) > 0) {

	$res = pg_exec ($con,"BEGIN TRANSACTION");

	#-------------- Confirma confer�ncia atual ----------#
	$qtde_item = $_POST['qtde_item'];
	for ($i = 0 ; $i < $qtde_item ; $i++) {
		$referencia  = trim ($_POST['referencia_'  . $i]);
		$localizacao = trim ($_POST['localizacao_' . $i]);

		$localizacao = strtoupper (trim ($localizacao));

		if (strlen ($localizacao) == 0) {
			$localizacao = "null";
		}else{
			$localizacao = "'" . $localizacao . "'";
		}

		if (strlen ($referencia) < 6) {
			$referencia = "000000" . $referencia;
			$referencia = substr ($referencia,strlen ($referencia)-6);
		}

		$sql = "UPDATE tbl_posto_estoque_localizacao SET localizacao = $localizacao FROM tbl_peca WHERE tbl_posto_estoque_localizacao.posto = $login_posto AND tbl_posto_estoque_localizacao.peca = tbl_peca.peca AND tbl_peca.referencia = '$referencia' ";
		$res = pg_exec ($con,$sql);

	}

	$res = pg_exec ($con,"COMMIT TRANSACTION");

	echo "<center><h2>Processadas mudan�as</h2></center>";

}


$aba = 3;
include 'estoque_cabecalho.php';
?>


<div id='dest'>Mudar Localiza��o de Pe�a</div>

<p>

<table width='300' align='center'>
<tr bgcolor='#FF9933' style='color:#ffffff ; font-weight:bold'>
	<td align='center'>Pe�a</td>
	<td align='center'>Localiza��o</td>
</tr>


<?

echo "<form method='post' action='$PHP_SELF' name='frm_localizacao'>";

for ($i = 0 ; $i < 10 ; $i++) {
	$referencia       = $_POST['referencia_'  . $i] ;
	$localizacao      = $_POST['localizacao_' . $i] ;

	if (strlen ($referencia) > 0) {
		$ sql = "SELECT tbl_posto_estoque_localizacao.localizacao FROM tbl_posto_estoque_localizacao JOIN tbl_peca USING (peca) WHERE tbl_posto_estoque_localizacao.posto = $login_posto AND tbl_peca.referencia = '$referencia'";
		$res = pg_exec ($con,$sql);
		$localizacao = @pg_result ($res,0,0);
	}
	
	$cor = "#FFFBF0";
	if ($i % 2 == 0) $cor = "#FFEECC";

	echo "<tr style='font-size: 12px' bgcolor='$cor'>\n";
	echo "<td align='center' nowrap><input type='text' name='referencia_$i'  value='$referencia' size='10'  maxlength='10'></td>\n";
	echo "<td align='center' nowrap><input type='text' name='localizacao_$i' value='$localizacao' size='10' maxlength='15'></td>\n";
	echo "</tr>\n";
}


echo "<tr>";
echo "<td colspan='5' align='center'>";
echo "<input type='hidden' name='qtde_item' value='$i'>";

echo "<input type='submit' name='btn_acao' value='Mudar !'>";

echo "</form>";
echo "</td>";
echo "</tr>";


echo "</table>\n";

?>

<p>

<? include "login_unico_rodape.php"; ?>

