<?

include 'dbconfig.php';
include 'dbconnect-inc.php';
include 'configuracao.php';

$title="Seja bem Vindo a Loja Tecnoplus!";

include "topo.php";

$categoria=$_GET['cat'];

echo "<table width='750' border='0' align='center' cellpadding='0' cellspacing='0' style='font-family: verdana; font-size: 12px'>";
echo "<tr>";
	echo "<td width='182' valign='top'>";
	include "menu.php";
	echo "<BR>";
	echo "</td>";
	echo "<td width='568' align='right' valign='top'>";
	echo "<BR>";
			echo "<table width='555' border='0' align='center' cellpadding='0' cellspacing='0' style='font-family: verdana; font-size: 10px'>";
			echo "<tr width='555' height='40'>";
			echo "<td  width='13' height='40'><IMG SRC='corpo_dir1.jpg' width='13'  height='40'>";
			echo "</td>";
			$sql="select tbl_familia.familia, tbl_familia.descricao FROM tbl_familia where fabrica=27 AND tbl_familia.familia=$categoria";
			$res = pg_exec ($con,$sql);
			$nome_categoria = trim(pg_result ($res,0,descricao));
			echo "<td width='529' height='40' colspan='3' align='left' background='corpo_dir2.jpg' align='center'>&nbsp;&nbsp;<font size='2'><B>Voc� est� em >></B> $nome_categoria</font>";
			echo "</td>";
			echo "<td  width='13' height='40'><IMG SRC='corpo_dir3.jpg' width='13'  height='40'>";
			echo "</td>";
			echo "</tr>";
			//produtos linha a cima

 			//pega produtos
			$sqlx = "SELECT tbl_peca.peca               ,
						tbl_peca.estoque                ,
						tbl_peca.referencia             ,
						tbl_peca.descricao              ,
						tbl_peca.garantia_diferenciada  ,
						tbl_peca_item.peca              ,
						tbl_peca_item.familia           ,
						tbl_tabela_item_erp.peca        ,
						tbl_tabela_item_erp.preco
						from tbl_peca
						JOIN tbl_peca_item ON tbl_peca_item.peca = tbl_peca.peca
						left JOIN tbl_tabela_item_erp ON tbl_tabela_item_erp.peca=tbl_peca.peca
						where tbl_peca_item.familia=$categoria AND tbl_peca.fabrica=$login_empresa;";

			$xres = pg_exec ($con,$sqlx);
			//echo "$sqlx";
			echo "<tr>";
			echo "<td>";
			echo "</td>";
			if(strlen(pg_numrows($xres)>0)){
				for ($i = 0 ; $i < pg_numrows($xres); $i++){
					$peca                   = trim(pg_result ($xres,$i,peca));
					$referencia             = trim(pg_result ($xres,$i,referencia));
					$garantia_diferenciada  = trim(pg_result ($xres,$i,garantia_diferenciada));
					$estoque                = trim(pg_result ($xres,$i,estoque));
					$descricao              = trim(pg_result ($xres,$i,descricao));
					$preco_compra           = trim(pg_result ($xres,$i,preco));
				
				$preco_compra = number_format($preco_compra, 2, ',', '');

				echo "<td width='176' align='center' ><BR><a href='detalhe.php?cod_produto=$peca'>";
				
				$sqlfoto="select tbl_peca_item_foto.peca, caminho, tbl_peca.peca FROM tbl_peca_item_foto JOIN tbl_peca ON tbl_peca.peca = tbl_peca_item_foto.peca where tbl_peca_item_foto.peca = $peca";
				$xxres = pg_exec ($con,$sqlfoto);
				//echo $sql;

				if(strlen(pg_numrows($xxres)>0)){
					$caminho = trim(pg_result ($xxres,0,caminho));
				}else{
					$caminho = "produtos/552497-semimagem.jpg";
				}
				$caminho = str_replace("/www/assist/www/erp/","",$caminho);
				if($caminho <>"produtos/552497-semimagem.jpg"){$caminho = "../".$caminho;}
					
//				echo $caminho;

				echo "<IMG SRC='$caminho' width='80' border='0'><BR>";

					echo "<BR>$descricao</a>";
					echo"<BR>Disponibilidade de Estoque: $estoque";
					echo"<BR>Tempo de Garantia: $garantia_diferenciada MES(ES)";
					echo"<BR><B>Valor: R$ $preco_compra</B><BR>";

					echo "</td>";
					$coluna++;
					if ($coluna == 3) {
						echo "<td></td></tr>";
						echo"<tr><td></td></tr>";
						echo"<tr><td></td>";
						$coluna = 0;
					}
				}
				echo "</tr>";
			}else{
				echo "<td colspan='5' align='center'><b>NENHUM PRODUTO DISPON�VEL NESSA CATEGORIA</b></td>";
			}
			echo "<td>";
			echo "</td>";
			echo "</tr>";

			//produtos linha de baixo
			echo "<tr height='34'>";
			echo "<td width='13' height='34'><IMG SRC='corpo_dir4.jpg' width='13' height='34'>";
			echo "</td>";
			echo "<td width='529' colspan='3' background='corpo_dir5.jpg'>";
			echo "</td>";
			echo "<td width='13' height='34'><IMG SRC='corpo_dir6.jpg' width='13' height='34'>";
			echo "</td>";
			echo "</tr>";
			echo "</table>";
		echo "</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td colspan='2' height='60' bgcolor='#f3f2f1' align='center'>
		&nbsp;<a href='index.php'>Home</a>&nbsp;&nbsp;&nbsp;|
		&nbsp;&nbsp;&nbsp;<a href='empresa.php'>Quem Somos</a>&nbsp;&nbsp;&nbsp;|
		&nbsp;&nbsp;&nbsp;<a href='cadatro.php'>Cadastro</A>&nbsp;&nbsp;&nbsp;|
		&nbsp;&nbsp;&nbsp;<a href='promocao.php'>Destaque</A>&nbsp;&nbsp;&nbsp;|
		&nbsp;&nbsp;&nbsp;<a href='#'>Fale Conosco</a><BR>
		Tecnoplus 2007 -  Todos os direitos Reservados<BR>
		Sistema Telecontrol";
		echo "</td>";
	echo "</tr>";
echo "</table>";

?>