<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'autentica_usuario.php';

$extrato = $_GET['extrato'];

if ($login_fabrica <> 2 OR strlen($extrato) == 0){
	header("Location: os_extrato.php");
	exit;
}

$title = "DADOS PARA EMISS�O DE NOTA FISCAL";

$layout_menu = "os";
include "cabecalho.php";

?>

<style type='text/css'>
body {
	text-align: center;

		}

.cabecalho {
	background-color: #D9E2EF;
	color: black;
	border: 2px SOLID WHITE;
	font-weight: normal;
	font-size: 10px;
	text-align: left;
}

.descricao {
	padding: 5px;
	color: black;
	font-size: 11px;
	font-weight: bold;
	text-align: justify;
}


/*========================== MENU ===================================*/

a:link.menu {
	padding: 3px;
	display:block;
	font: normal small Verdana, Geneva, Arial, Helvetica, sans-serif;
	color: navy;
	font-size: 12px;
	font-weight: bold;
	text-align: left;
	text-decoration: none;
}

a:visited.menu {
	padding: 3px;
	display:block;
	font: normal small Verdana, Geneva, Arial, Helvetica, sans-serif;
	color: navy;
	font-size: 12px;
	font-weight: bold;
	text-align: left;
	text-decoration: none;
}

a:hover.menu {
	padding: 3px;
	display:block;
	font: normal small Verdana, Geneva, Arial, Helvetica, sans-serif;
	color: black;
	font-size: 12px;
	font-weight: bold;
	text-align: left;
	text-decoration: none;
	background-color: #ced7e7;
}

</style>

<SCRIPT>
	displayText("<center><br><font color='#ff0000'>EMITIR NOTA FISCAL CONFORME MODELO ABAIXO E ENVIAR JUNTAMENTE COM AS PE�AS.</font><br><br></center>");
</SCRIPT>
<br />

<!-- //##################################### -->

<table width='700' border='1' cellpadding='0' cellspacing='2' bordercolor='#D9E2EF'>
<TR class='cabecalho'>
	<TD COLSPAN='3'><B>DADOS DA EMPRESA DESTINAT�RIA</B></TD>
</TR>
<TR class='cabecalho'>
	<TD>RAZ�O SOCIAL</TD>
	<TD>CNPJ</TD>
	<TD>IE</TD>
</TR>
<TR class='descricao'>
	<TD>Prodtel Com�rcio Ltda</TD>
	<TD>04.789.310/0001-98</TD>
	<TD>116.594.848.117</TD>
</TR>
<TR class='cabecalho'>
	<TD>ENDERE�O</TD>
	<TD>CEP</TD>
	<TD>BAIRRO</TD>
</TR>
<TR class='descricao'>
	<TD>Rua Forte do Rio Branco,762 </TD>
	<TD>08340-140</TD>
	<TD>Pq. Industrial S�o Louren�o</TD>
</TR>
<TR class='cabecalho'>
	<TD>MUNICIPIO</TD>
	<TD>ESTADO</TD>
	<TD>TELEFONE</TD>
</TR>
<TR class='descricao'>
	<TD>S�o Paulo</TD>
	<TD>SP</TD>
	<TD>(11) 6117-2336</TD>
</TR>
</TABLE>
<BR>
<table width='700' border='1' cellpadding='0' cellspacing='2' bordercolor='#D9E2EF'>
<TR class='cabecalho'>
	<TD COLSPAN='2'><B>DADOS IMPORTANTES PARA A NOTA</B></TD>
</TR>
<TR class='cabecalho'>
	<TD>NATUREZA DA OPERA��O</TD>
	<TD>CFOP</TD>
</TR>
<TR class='descricao'>
	<TD>DEVOLU��O DE REPOSI��O</TD>
	<TD>5949 ( dentro de S�o Paulo ) 6949 ( fora de S�o Paulo )</TD>
</TR>
<TR class='cabecalho'>
	<TD colspan=2>ICMS</TD>
</TR>
<TR class='descricao'>
	<TD colspan=2>Se n�o for isento, preencher conforme aliquota interestadual.</TD>
</TR>
</TABLE>
<BR>

<table width='700' border='1' cellpadding='0' cellspacing='2' bordercolor='#D9E2EF'>
<TR class='cabecalho'>
	<TD COLSPAN='5'><B>DADOS DOS ITENS DA NOTA FISCAL</B></TD>
</TR>
<?
// ITENS
if (strlen ($extrato) > 0) {
// altera��o aqui
	if	($login_fabrica == 2) {
			$sql = "SELECT	tbl_peca.referencia    ,
							tbl_peca.descricao     ,
							(SELECT preco FROM tbl_tabela_item WHERE peca = tbl_os_item.peca AND tabela = tbl_posto_linha.tabela) AS preco ,
							tbl_os_item.qtde                                               ,
							to_char(tbl_extrato.data_geracao, 'DD/MM/YYYY') AS data_geracao
					FROM    tbl_os
					JOIN    tbl_os_extra             ON tbl_os.os                                = tbl_os_extra.os
					JOIN    tbl_produto              ON tbl_os.produto                           = tbl_produto.produto
					JOIN    tbl_os_produto           ON tbl_os.os                                = tbl_os_produto.os
					JOIN    tbl_os_item              ON tbl_os_produto.os_produto                = tbl_os_item.os_produto
					JOIN    tbl_servico_realizado    ON tbl_servico_realizado.servico_realizado  = tbl_os_item.servico_realizado
					JOIN    tbl_peca                 ON tbl_os_item.peca                         = tbl_peca.peca
					JOIN    tbl_extrato              ON tbl_extrato.extrato                      = tbl_os_extra.extrato
					JOIN    tbl_posto_linha          ON tbl_posto_linha.posto = tbl_os.posto AND (tbl_posto_linha.linha = tbl_produto.linha OR tbl_posto_linha.familia = tbl_produto.familia)
					WHERE   tbl_os_extra.extrato = $extrato
					AND     tbl_extrato.fabrica  = $login_fabrica
					AND     tbl_os_item.liberacao_pedido    IS NOT FALSE
					AND     tbl_peca.devolucao_obrigatoria      IS TRUE
					AND     tbl_servico_realizado.gera_pedido   IS TRUE
					AND     tbl_servico_realizado.troca_de_peca IS TRUE
					ORDER BY tbl_os_item.preco;";
// termino da alteracao
		} else {
		$sql = "SELECT	tbl_peca.referencia,
					tbl_peca.descricao ,
					SUM (tbl_os_item.qtde) AS qtde,
					SUM (tbl_os_item.preco) AS preco
			FROM    tbl_os
			JOIN    tbl_os_extra          ON tbl_os.os                               = tbl_os_extra.os
			JOIN    tbl_produto           ON tbl_os.produto                          = tbl_produto.produto
			JOIN    tbl_os_produto        ON tbl_os.os                               = tbl_os_produto.os
			JOIN    tbl_os_item           ON tbl_os_produto.os_produto               = tbl_os_item.os_produto
			JOIN    tbl_servico_realizado ON tbl_servico_realizado.servico_realizado = tbl_os_item.servico_realizado
			JOIN    tbl_peca              ON tbl_os_item.peca                        = tbl_peca.peca
			JOIN    tbl_extrato           ON tbl_extrato.extrato                     = tbl_os_extra.extrato
			WHERE   tbl_os_extra.extrato = $extrato
			AND     tbl_extrato.fabrica  = $login_fabrica
			AND     tbl_os_item.liberacao_pedido    IS NOT FALSE
			AND     tbl_peca.devolucao_obrigatoria      IS TRUE
			AND     tbl_servico_realizado.gera_pedido   IS TRUE
			AND     tbl_servico_realizado.troca_de_peca IS TRUE
			GROUP BY tbl_peca.referencia, tbl_peca.descricao, tbl_os_item.preco
			ORDER BY SUM (tbl_os_item.qtde);";
		}
//	echo $sql;
	$res = pg_exec ($con,$sql);

	if (pg_numrows ($res) > 0) {

		echo "<TR class='cabecalho'>";
		echo "<TD>DESCRI��O</TD>";
		echo "<TD>QTDE</TD>";
		echo "<TD>UNIT�RIO</TD>";
		echo "<TD>ICMS</TD>";
		echo "<TD>TOTAL</TD>";
		echo "</TR>";

		$total_nota = 0;

		for ($i=0; $i< pg_numrows ($res); $i++){
			$referencia = pg_result ($res,$i,referencia);
			$descricao  = pg_result ($res,$i,descricao);
			$qtde       = pg_result ($res,$i,qtde);
			$preco      = pg_result ($res,$i,preco);
			$icms       = 0;
			$total_peca = $qtde * $preco;

			echo "<TR class='descricao'>";
			echo "<TD>$descricao&nbsp;</TD>";
			echo "<TD>$qtde&nbsp;</TD>";
			echo "<TD>$preco&nbsp;</TD>";
			echo "<TD>$icms&nbsp;</TD>";
			echo "<TD>$total_peca&nbsp;</TD>";
			echo "</TR>";

			$total_nota = $total_nota + $total_peca;

		}

		echo "<TR class='descricao'>";
		echo "<TD colspan=4>Total da Nota Fiscal</TD>";
		echo "<TD>$total_nota &nbsp;</TD>";
		echo "</TR>";

	}else{
		echo "<TR class='descricao'>";
		echo "<TD colspan=5><center>Extrato sem itens para emiss�o de Nota Fiscal</center></TD>";
		echo "</TR>";
	}
}
?>
</TABLE>

<br>

<? include "rodape.php";?>