<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'autentica_usuario.php';

if ($login_fabrica_nome <> "Dynacom" AND $login_fabrica_nome <> "Tectoy") {
	header ("Location: os_cadastro.php");
	exit;
}

if (strlen($_GET['os']) > 0) {
	$os = $_GET['os'];
}elseif (strlen($_POST['os']) > 0) {
	$os = $_POST['os'];
}else{
	// redireciona para cadastro qdo OS inexistente
	header ("Location: os_cadastro.php");
	exit;
}

//if (strlen (trim ($os)) == 0 ) $os = $_POST['os'];

$sql = "SELECT fabrica FROM tbl_os WHERE os = '$os'";
$res = pg_exec ($con,$sql) ;
//$ResFabrica = pg_result($res,0,fabrica);
$ResFabrica = pg_fetch_array($res);
if ( $ResFabrica['fabrica'] != $login_fabrica ) {
	header ("Location: os_cadastro.php");
	exit;
}

include 'funcoes.php';

$btn_acao = strtolower ($_POST['btn_acao']);

$msg_erro = "";

if ($btn_acao == "gravar") {
	$defeito_reclamado = $_POST ['defeito_reclamado'];
	
	$res = pg_exec ($con,"UPDATE tbl_os SET defeito_reclamado = $defeito_reclamado WHERE os = $os");
	$res = pg_exec ($con,"DELETE FROM tbl_os_produto WHERE os = $os");
	
	$qtde_item = $_POST['qtde_item'];
	
	for ($i = 0 ; $i < $qtde_item ; $i++) {
		$produto    = $_POST['produto_'     . $i];
		$serie      = $_POST['serie_'       . $i];
		$serigrafia = $_POST['serigrafia_'  . $i];
		$peca       = $_POST['peca_'        . $i];
		$defeito    = $_POST['defeito_'     . $i];
		
		if (strlen ($produto) > 0 and strlen($peca) > 0) {
			$produto = strtoupper ($produto);
			
			$sql = "SELECT tbl_produto.produto
					FROM   tbl_produto
					JOIN   tbl_linha USING (linha)
					WHERE  tbl_produto.referencia = '$produto'
					AND    tbl_linha.fabrica = $login_fabrica;";
			$res = pg_exec ($con,$sql);
			if (pg_numrows ($res) == 0) {
				$msg_erro = "Produto $produto n�o cadastrado";
				$linha_erro = $i;
			}else{
				$produto = pg_result ($res,0,produto);
			}
			
			if (strlen ($msg_erro) == 0) {
				$sql = "INSERT INTO tbl_os_produto (
							os     ,
							produto,
							serie
						)VALUES(
							$os     ,
							$produto,
							'$serie'
					);";
				$res = @pg_exec ($con,$sql);
				$msg_erro = pg_errormessage($con);
				if (strlen ($msg_erro) > 0) {
					break ;
				}else{
					$res = pg_exec ($con,"SELECT CURRVAL ('seq_os_produto')");
					$os_produto  = pg_result ($res,0,0);
					
					$peca = strtoupper ($peca);
					
					if (strlen($peca) > 0) {
						$sql = "SELECT tbl_peca.peca
								FROM   tbl_peca
								WHERE  trim(tbl_peca.referencia) = '$peca'
								AND    tbl_peca.fabrica          = $login_fabrica;";
						$res = pg_exec ($con,$sql);
						
						if (pg_numrows ($res) == 0) {
							$msg_erro = "Pe�a $peca n�o cadastrada";
							$linha_erro = $i;
						}else{
							$peca = pg_result ($res,0,peca);
						}
						
						if (strlen ($msg_erro) == 0) {
							$sql = "INSERT INTO tbl_os_item (
										os_produto,
										peca      ,
										qtde      ,
										defeito   ,
										serigrafia
									)VALUES(
										$os_produto,
										$peca      ,
										1          ,
										'$defeito' ,
										'$serigrafia'
								);";
							$res = @pg_exec ($con,$sql);
							$msg_erro = pg_errormessage($con);
							
							if (strlen ($msg_erro) > 0) {
								break ;
							}
						}
					}
				}
			}
		}
	}
	
	if (strlen ($msg_erro) == 0) {
		$res      = pg_exec ($con,"SELECT fn_valida_os_item($os, $login_fabrica)");
		$msg_erro = pg_errormessage($con);
	}
	
	if (strlen ($msg_erro) == 0) {
		$res      = pg_exec ($con,"SELECT fn_finaliza_os($os, $login_fabrica)");
		$msg_erro = pg_errormessage($con);
	}
	
	if (strlen ($msg_erro) == 0) {
		$res = pg_exec ($con,"COMMIT TRANSACTION");
		header ("Location: os_finalizada_dynacom.php?os=$os");
		exit;
	}else{
		$res = pg_exec ($con,"ROLLBACK TRANSACTION");
	}
}


#------------ Le OS da Base de dados ------------#
/*
if (strlen ($os) > 0) {
	$sql = "SELECT * FROM tbl_os WHERE oid = $os AND posto = $login_posto";
	$res = pg_exec ($con,$sql);

	if (pg_numrows ($res) == 1) {
		$sua_os				= pg_result ($res,0,sua_os);
		$data_abertura		= pg_result ($res,0,data_abertura);
		$data_fechamento	= pg_result ($res,0,data_fechamento);
		$consumidor_nome	= pg_result ($res,0,consumidor_nome);
		$consumidor_cidade	= pg_result ($res,0,consumidor_cidade);
		$consumidor_fone	= pg_result ($res,0,consumidor_fone);
		$consumidor_estado	= pg_result ($res,0,consumidor_estado);
		$revenda_cnpj		= pg_result ($res,0,revenda_cnpj);
		$revenda_nome		= pg_result ($res,0,revenda_nome);
		$nota_fiscal		= pg_result ($res,0,nota_fiscal);
		$data_nf			= pg_result ($res,0,data_nf);
	}
}
*/

#---------------- Recarrega Form em caso de erro -------------
if (strlen ($msg_erro) > 0) {
	$os                = $_POST['os'];
	$defeito_reclamado = $_POST['defeito_reclamado'];
}

$title = "Telecontrol-Assist�ncia T�cnica - Ordem de Servi�o";
$body_onload = "javascript: document.frm_os.defeito_reclamado.focus()";

$layout_menu = 'os';
include "cabecalho.php";



?>


<!-- AQUI COME�A O SUB MENU - �REA DE CABECALHO DOS RELAT�RIOS E DOS FORMUL�RIOS -->
<div id="subBanner"><h1>�tens da Ordem de Servi�o</h1></div>


<?
#----------------- Le dados da OS --------------
$os = $_GET['os'];
// if (strlen (trim ($os)) == 0 ) $os = $_POST['os'];
if (strlen (trim ($os)) == 0 ){
	header ("Location: os_cadastro.php");
	exit;
}

$sql = "SELECT  tbl_os.*,
				tbl_produto.referencia,
				tbl_produto.descricao ,
				tbl_produto.linha
		FROM    tbl_os
		JOIN    tbl_produto USING (produto)
		WHERE   tbl_os.os = $os";
$res = pg_exec ($con,$sql) ;

if(pg_numrows($res) == 0){
	header ("Location: os_cadastro.php");
	exit;
}

$linha              = pg_result ($res,0,linha);
$produto_os         = pg_result ($res,0,produto);
$produto_referencia = pg_result ($res,0,referencia);
$produto_descricao  = pg_result ($res,0,descricao);
$produto_serie      = pg_result ($res,0,serie);
?>


<script language="JavaScript">
function fnc_pesquisa_peca_lista (codigo, descricao, produto, preco, seq) {
	var url = "";
	if (codigo != "") {
		url = "pesquisa_peca_lista.php?peca=" + codigo.value + "&produto=" + produto.value + "&seq=" + seq + "&retorno=<?echo $PHP_SELF?>&faturado=sim";
		janela = window.open(url,"janela","toolbar=no,location=no,status=no,scrollbars=yes,directories=no,width=500,height=400,top=18,left=0");
		janela.peca = codigo;
		janela.descricao = descricao;
		janela.preco = preco;
		janela.focus();
	}
}
function fnc_pesquisa_produto (codigo, descricao) {
	var url = "";
	if (codigo != "") {
		url = "pesquisa_produto.php?produto=" + codigo.value ;
		janela = window.open(url,"janela","toolbar=no,location=no,status=no,scrollbars=yes,directories=no,width=500,height=400,top=18,left=0");
		janela.codigo = codigo;
		janela.descricao = descricao;
		janela.focus();
	}
}
</script>

<p>

<? 
if (strlen ($msg_erro) > 0) {
	if (strpos ($msg_erro,"Cannot insert a duplicate key into unique index tbl_os_sua_os") > 0) $msg_erro = "Esta ordem de servi�o j� foi cadastrada";

?>
<table width="600" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffCCCC">
<tr>
	<td height="27" valign="middle" align="center">
		<b><font face="Arial, Helvetica, sans-serif" color="#FF3333">
		<? echo $msg_erro ?>
		</font></b>
	</td>
</tr>
</table>
<? } ?>

<table width="600" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff">

<tr>

	<td><img height="1" width="20" src="imagens/spacer.gif"></td>

	<td valign="top" align="center">


		<!-- ------------- Formul�rio ----------------- -->

		<form name="frm_os" method="post" action="<? echo $PHP_SELF ?>">
		<input type="hidden" name="os" value="<?echo $os?>">

		<p>

		<table width="100%" border="0" cellspacing="5" cellpadding="0">
		<tr>
			<td nowrap>
				<font size="1" face="Geneva, Arial, Helvetica, san-serif">Produto</font>
				<br>
				<font size="2" face="Geneva, Arial, Helvetica, san-serif">
				<b><? echo $produto_referencia . " - " . $produto_descricao?></b>
				</font>
			</td>
			<td nowrap>
				<font size="1" face="Geneva, Arial, Helvetica, san-serif">N. S�rie</font>
				<br>
				<font size="2" face="Geneva, Arial, Helvetica, san-serif">
				<b><? echo $produto_serie ?></b>
				</font>
			</td>
		</tr>
		</table>

		<table width="100%" border="0" cellspacing="5" cellpadding="0">
		<tr>
			<td nowrap>
				<font size="1" face="Geneva, Arial, Helvetica, san-serif">Defeito Reclamado</font>
				<br>
				<select name="defeito_reclamado" size="1">
				<?
				$sql = "SELECT *
						FROM   tbl_defeito_reclamado
						JOIN   tbl_linha USING (linha)
						WHERE  tbl_defeito_reclamado.linha = $linha
						AND    tbl_linha.fabrica           = $login_fabrica;";
				$res = pg_exec ($con,$sql) ;
				
				for ($i = 0 ; $i < pg_numrows ($res) ; $i++ ) {
					echo "<option ";
					if ($defeito_reclamado == pg_result ($res,$i,defeito_reclamado) ) echo " selected ";
					echo " value='" . pg_result ($res,$i,defeito_reclamado) . "'>" ;
					echo pg_result ($res,$i,descricao) ;
					echo "</option>";
				}
				?>
				</select>
			</td>
		</tr>
		</table>
		
		
		<table width="100%" border="0" cellspacing="5" cellpadding="0">
		<tr height="20" bgcolor="#666666">
			<td align='center'><font size="1" face="Geneva, Arial, Helvetica, san-serif" color='#ffffff'><b>Equipamento</b></font></td>
			<td align='center'><font size="1" face="Geneva, Arial, Helvetica, san-serif" color='#ffffff'><b>N. S�rie</b></font></td>
			<td align='center'><font size="1" face="Geneva, Arial, Helvetica, san-serif" color='#ffffff'><b>Serigrafia</b></font></td>
			<td align='center'><font size="1" face="Geneva, Arial, Helvetica, san-serif" color='#ffffff'><b>Componente</b></font></td>
			<td align='center'><font size="1" face="Geneva, Arial, Helvetica, san-serif" color='#ffffff'><b>Defeito</b></font></td>
			<td align='center'><font size="1" face="Geneva, Arial, Helvetica, san-serif" color='#ffffff'><b>Pre�o</b></font></td>
		</tr>
		
		<?
		$qtde_item = 5;
		echo "<input class='frm' type='hidden' name='qtde_item' value='$qtde_item'>";
		
		for ($i = 0 ; $i < $qtde_item ; $i++) {
			if (strlen ($msg_erro) > 0) {
				$produto    = $_POST["produto_"     . $i];
				$serie      = $_POST["serie_"       . $i];
				$serigrafia = $_POST["serigrafia_"  . $i];
				$peca       = $_POST["peca_"        . $i];
				$defeito    = $_POST["defeito_"     . $i];
				$preco      = $_POST["preco_"       . $i];
			}
		?>
		<tr>
			<input type='hidden' name='descricao'>
			
			<td align='center'>
				<!--<input class='frm' type="text" name="produto_<? echo $i ?>"    size="9" value="<? echo $produto ?>"><img src='imagens/lupa.gif' border='0' align='absmiddle' onclick='javascript: fnc_pesquisa_produto (document.frm_os.produto_<? echo $i ?> , document.frm_os.descricao)'>-->
				<select class='frm' size="1" name="produto_<? echo $i ?>">
				<?
				$sql = "SELECT  tbl_produto.referencia
						FROM    tbl_produto
						WHERE   tbl_produto.produto IN (
								SELECT tbl_subproduto.produto_filho
								FROM   tbl_subproduto
								JOIN   tbl_produto ON tbl_produto.produto = tbl_subproduto.produto_pai
								JOIN   tbl_linha   USING (linha)
								WHERE  tbl_subproduto.produto_pai = $produto_os
								AND     tbl_linha.fabrica         = $login_fabrica
						)
						ORDER BY tbl_produto.referencia";
				$res = pg_exec ($con,$sql) ;
				
				if (pg_numrows($res) > 0) {
					echo "<option value='$produto_referencia'>". substr($produto_referencia,0,25) ."</option>";
					
					for ($x = 0 ; $x < pg_numrows ($res) ; $x++ ) {
						echo "<option ";
						if ($produto == pg_result ($res,$x,referencia)) echo " selected ";
						echo " value='" . pg_result ($res,$x,referencia) . "'>" ;
						echo substr(pg_result ($res,$x,referencia),0,25) ;
						echo "</option>";
					}
				}else{
					$sql = "SELECT  tbl_produto.referencia,
									tbl_produto.produto  ,
									tbl_produto.descricao
							FROM    tbl_produto
							JOIN    tbl_linha   USING (linha)
							WHERE   tbl_produto.linha   = $linha
							AND     tbl_produto.produto = $produto_os
							AND     tbl_linha.fabrica   = $login_fabrica;";
					$res = pg_exec ($con,$sql) ;
					
					if (pg_numrows($res) > 0) {
						for ($x = 0 ; $x < pg_numrows ($res) ; $x++ ) {
							echo "<option ";
							if ($produto == pg_result ($res,$x,referencia)) echo " selected ";
							echo " value='" . pg_result ($res,$x,referencia) . "'>" ;
							echo substr(pg_result ($res,$x,descricao),0,25) ;
							echo "</option>";
						}
					}
				}
				?>
				</select>
			</td>
			<td align='center'><input class='frm' type="text" name="serie_<? echo $i ?>"      size="9" value="<? echo $serie ?>"></td>
			<td align='center'><input class='frm' type="text" name="serigrafia_<? echo $i ?>"     size="9" value="<? echo $serigrafia ?>"></td>
			<td align='center'><input class='frm' type="text" name="peca_<? echo $i ?>"       size="15" value="<? echo $peca ?>">&nbsp;<a href="#"><img src='imagens/btn_buscar5.gif' border='0' align='absmiddle' onclick='javascript: fnc_pesquisa_peca_lista (document.frm_os.peca_<? echo $i ?> , document.frm_os.descricao , document.frm_os.produto_<? echo $i ?>, document.frm_os.preco_<? echo $i ?>, <? echo $i ?>)' alt="Clique para efetuar a pesquisa"><a></td>
			<td align='center'>
				<select class='frm' size="1" name="defeito_<? echo $i ?>">
				<?
				$sql = "SELECT *
						FROM   tbl_defeito
						WHERE  tbl_defeito.fabrica = $login_fabrica;";
				$res = pg_exec ($con,$sql) ;
				
				for ($x = 0 ; $x < pg_numrows ($res) ; $x++ ) {
					echo "<option ";
					if ($defeito == pg_result ($res,$x,defeito)) echo " selected ";
					echo " value='" . pg_result ($res,$x,defeito) . "'>" ;
					echo pg_result ($res,$x,descricao) ;
					echo "</option>";
				}
				?>
				</select>
			</td>
			<td align='center'><input class='frm' type="text" name="preco_<? echo $i ?>"       size="9" value="<? echo $preco ?>" disabled></td>
		</tr>
		<?
		}
		?>

		</table>


	</td>

	<td><img height="1" width="16" src="imagens/spacer.gif"></td>
</tr>

<tr>
	<td height="27" valign="middle" align="center" colspan="3" bgcolor="#FFFFFF">
		<input type="submit" name="btn_acao" value="Gravar">
	</td>
</tr>


</form>


</table>

<p><p>

<? 
	//include "includes-php/f-rodape.php"; 
?>

<? include "rodape.php"; ?>