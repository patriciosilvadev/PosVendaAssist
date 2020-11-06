<?

header("Expires: 0");
header("Cache-Control: no-cache, public, must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache, public");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");                // Data no passado
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");   // Sempre modificado
header("Cache-Control: no-store, no-cache, must-revalidate");    // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");   // HTTP/1.0

#######################################################################
function getmicrotime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

function TempoExec($pagina, $sql, $time_start, $time_end){
	$time = $time_end - $time_start;
	$time = str_replace ('.',',',$time);
	$sql  = str_replace ('\t',' ',$sql);
	$fp = fopen ("/home/telecontrol/tmp/postgres.log","a");
	fputs ($fp,$pagina);
	fputs ($fp,"#");
	fputs ($fp,$sql);
	fputs ($fp,"#");
	fputs ($fp,$time);
	fputs ($fp,"\n");
	fclose ($fp);
}

$micro_time_start = getmicrotime();

########################################################################


//--=========== AQUI COME�A O NOVO MENU - RAPHAEL GIOVANINI V ===========--\\
?>
<html>
<head>
<title><?=$title;?></title>
<link type="text/css" rel="stylesheet" href="css/estilo.css">
<link type="text/css" rel="stylesheet" href="css/css.css">
<link type="text/css" rel="stylesheet" href="SpryMenuBarHorizontal.css">

<script type="text/javascript" src="jquery/jquery-latest.pack.js"></script>
<script type="text/javascript" src="jquery/thickbox.js"></script>
<link rel="stylesheet" href="jquery/thickbox.css" type="text/css" media="screen" />
<script src="jquery/jquery.history_remote.pack.js" type="text/javascript"></script>
<script src="jquery/jquery.tabs.pack.js" type="text/javascript"></script>

<link rel="stylesheet" href="jquery/jquery.tabs.css" type="text/css" media="print, projection, screen">
<!-- Additional IE/Win specific style sheet (Conditional Comments) -->
<!--[if lte IE 7]>
<link rel="stylesheet" href="jquery/jquery.tabs-ie.css" type="text/css" media="projection, screen">
<![endif]-->

<script src="jquery/jquery.form.js" type="text/javascript" language="javascript"></script>

<script src="jquery/jquery.corner.js" type="text/javascript" language="javascript"></script>

<script src="jquery/jquery.shadow.js" type="text/javascript" language="javascript"></script>

<script src="jquery/jquery.autocomplete.js" type="text/javascript" language="javascript"></script>
<link rel="stylesheet" type="text/css" href="jquery/jquery.autocomplete.css" />

<script src="jquery/jquery.MultiFile.js" type="text/javascript" language="javascript"></script>        
<script src="jquery/jquery.MetaData.js" type="text/javascript" language="javascript"></script>
<!-- <script src="jquery/jtip.js" type="text/javascript"></script> -->
<!--<script type="text/javascript" src="jquery/jquery-tooltipdemo.js"></script>-->
<!--<script type="text/javascript" src="jquery/jquery.atalho.js"></script>-->
<script src="jquery/jquery.maskedinput.js" type="text/javascript"></script>

<!-- retirei Fabio
<script src="jquery/jquery.dimensions.js" type="text/javascript"></script>
<script src="jquery/jquery.cluetip.js" type="text/javascript"></script>
-->

<!-- Scripts para a ToolTip -->
<link rel="stylesheet" href="jquery/jquery.tooltip.css" />
<script src="jquery/jquery.bgiframe.js"          type="text/javascript"></script>
<script src="jquery/jquery.dimensions.tootip.js" type="text/javascript"></script>
<script src="jquery/chili-1.7.pack.js"           type="text/javascript"></script>
<script src="jquery/jquery.tooltip.js"           type="text/javascript"></script>

<script type="text/javascript">
	$(function() {
		$("input[@rel='ajuda1'],a[@rel='ajuda1'],a.ajuda,a.ajuda1,img[@rel='ajuda']").Tooltip({
			track: true,
			delay: 0,
			showURL: false,
			opacity: 0.85,
			fixPNG: true,
			showBody: " - "
		});

		$("input[@rel='ajuda2'],a[@rel='ajuda2'],img[@rel='ajuda2']").Tooltip({
			track: true,
			delay: 0,
			showURL: false,
			opacity: 0.85,
			fixPNG: true,
			showBody: " - ",
			extraClass: "pretty fancy",
			top: -15,
			left: 5
		});
	});
</script>

<script language='javascript' src='SpryMenuBar.js'></script>

<!-- Scripts para o Focus dos Campos -->
<script type="text/javascript" src="jquery/jquery.focusfields.pack.js"></script>
<script type="text/javascript" src="jquery/parsesamples.js"></script>
<script type="text/javascript">
$(
	function(){
		parseSamples();
		$("input.Caixa, textarea.Caixa, input.CaixaValor,").focusFields();
	}
)
</script>
</head>
<body>

<table width='100%' border='0' cellspacing='0' align='center' cellpadding='0'>
	<tr height='25' >
		<td bgcolor='#B90000' width='160' align='center'><font color='#FFFFFF' size='1'><? echo "<b>$login_loja_nome</b> ";?></font></td>
		<td  bgcolor='#4F94CD' align='left' >&nbsp;</td>
		<td  bgcolor='#4F94CD' align='right' ><font color='#FFFFFF' size='1'>&nbsp;<?echo "Usu�rio: <A HREF='alterar_dados_empregado.php' style='color: #ffffff;'><b>$login_empregado_nome</b></A> | Email: <b>$login_empregado_email</b> | ";?> 
		<a href='index.php' style='color:#FFFFFF'><b>INICIAL</b></a> | <a href='http://www.telecontrol.com.br' style='color:#FFFFFF'><b>SAIR</b></font></a>&nbsp;&nbsp;</td>
	</tr>
</table>
<table width='100%' border='0' cellspacing='0' align='center' cellpadding='0' bgcolor='#eeeeff'>
	<tr>
		<td><?
		//ALTERADO GUSTAVO HD 5746
		//cadastro,vendas,compras,financeiro,crm,gerencial,franqueados,relatorios,e-commerce,marketing,consultoria.
		/*################### PERMISS�O DE ACESSO AO MENU ###################*/
		?>
		<ul id="MenuBar1" class="MenuBarHorizontal">
		<?
		
		if (strlen ($login_empresa) == 0) {
			if (strlen($login_empregado)==0){
				header ("Location: ../index.php");
				exit;
			}
		}

		$sql = "SELECT 
					privilegios
				FROM tbl_empregado
				WHERE empregado = $login_empregado and empresa = $login_empresa";
		$res = pg_exec ($con,$sql);
		if(pg_numrows($res) > 0){
			$login_privilegios = pg_result ($res,0,privilegios);
		}

		$acesso = explode(",", $login_privilegios);

		if($login_privilegios == '*'){
			$acesso = array();
			$acesso[] = "cadastros"  ;
			$acesso[] = "vendas"     ;
			$acesso[] = "compra"     ;
			$acesso[] = "financeiro" ;
			$acesso[] = "CRM"        ;
			$acesso[] = "gerencial"  ;
			$acesso[] = "franqueados";
			$acesso[] = "relatorios" ;
			$acesso[] = "ecommerce"  ;
			$acesso[] = "marketing"  ;
		}//FIM
		
		for($x=0; $x < count($acesso); $x++){
			//MENU CADASTRO
			if ($acesso[$x] == "cadastros"){?>
				<li>
					<div align="center"><a class="MenuBarItemSubmenu" href="#">Cadastros</a></div>
					<ul>
						<li><a href="cadastro.php?tipo=cliente"><u>C</u>liente</a></li>
						<li><a href="cadastro.php?tipo=fornecedor">Fornecedor</a></li>
						<li><a href="cadastro.php?tipo=colaborador">Colaborador</a></li>
						<li><a href="cadastro_plano_conta.php">Plano de contas</a></li>
						<li><a href="cadastro_caixa_banco.php">Caixa/Banco</a></li>
						<li><a href="cadastro_linha.php">Linha</a></li>
						<li><a href="cadastro_familia.php">Familia</a></li>
						<li><a href="cadastro_marca.php">Marca</a></li>
						<li><a href="cadastro_modelo.php">Modelo</a></li>
						<li><a href="cadastro_produto.php">Produto</a></li>
						<li><a href="cadastro_servico.php">Servi�os</a></li>
						<li><a href="cadastro_tabela.php">Tabela de Pre�o</a></li>
						<li><a href="cadastro_condicoes_pagamento.php">Condi��o de Pagamento</a></li>
						<li><a href="cadastro_carta.php">Cartas</a></li>
					</ul>
				</li>
			<?}//FIM
			//MENU VENDAS
			 if ($acesso[$x] == "vendas"){?>
				<li>
					<div align="center"><a class="MenuBarItemSubmenu" href="#">Vendas</a></div>
					<ul>
						<li><a href="orcamento_cadastro.php?tipo_orcamento=orca_venda"><u>O</u>r�amento de Venda </a></li>
						<li><a href="orcamento_cadastro.php?tipo_orcamento=venda"><u>V</u>enda</b></a></li>
						<li><a href="orcamento_cadastro.php?tipo_orcamento=troca"><u>T</u>roca de Produto</b></a></li>
						<li><a href="venda_consulta.php">Consulta</a></li>
					</ul>
				</li>
				<li>
					<div align="center"><a class="MenuBarItemSubmenu" href="#">Fora Garantia</a></div>
					<ul>
						<li><a href="orcamento_cadastro.php?tipo_orcamento=fora_garantia">Or�amento</a></li>
						<li><a href="orcamento_tecnica.php">Consulta</a></li>
					</ul>
				</li>
				<li><div align='center'><a class="MenuBarItemSubmenu" href="#">Garantia</a></div>
					<?
					if (count($fabricas_atendidas)==0){
						echo '<ul><li><a href="#">Nenhuma Fabricante</a></ul>';
					}else{
						echo "<ul>";
						for ($i=0; $i<count($fabricas_atendidas);$i++){
							echo "<li>";
							echo "<div align='left'><a class='MenuBarItemSubmenu' href='#'>".$fabricas_atendidas[$i]["nome"]."</a></div>";
							echo "<ul>";
								echo "<li><a href='os_cadastro_ajax.php?fabrica=".$fabricas_atendidas[$i]["fabrica"]."'>Abrir OS</a></li>";
								echo "<li><a href='os_consulta_lite.php?fabrica=".$fabricas_atendidas[$i]["fabrica"]."'>Consulta OS</a></li>";
							echo "</ul>";
							echo "</li>";
						}
						echo "</ul>";
					}
					?>
				</li>
				<?}//FIM
				//MENU COMPRA
				 if ($acesso[$x] == "compra"){?>
					<li><div align="center"><a class="MenuBarItemSubmenu" href="#">Compra</a></div>
						<ul>
							<li><a href="requisicao.php">Requisi��o</a></li>
							<li><a href="requisicao_lista.php"> Gerar cota��o</a></li>
							<? /*
							<li><div align="left"><a class="MenuBarItemSubmenu" href="#">Fornecedor</a></div>
								<ul>
									<li><a href="fornecedor_lista_cotacao.php">Cota��o</a></li>
									<li><a href="fornecedor_inf_compra.php">Permiss�o de cota��o</a></li>
								</ul>*/
							?>
							</li>
							<li><a href="cotacao_consultar_mapa.php">Cota��o</a></li>
							<li><a href="cotacao_consultar_pedido.php">Recebimento</a></li>
							<li><a href="nf_entrada.php">Nota Fiscal</a></li>
							<li><a href="estoque.php">Estoque</a></li>
							<li><a href="formacao_preco.php">Forma��o de Pre�o</a></li>
							
						</ul>
					</li>
				<?}//FIM
				//MENU FINANCEIRO
				 if ($acesso[$x] == "financeiro"){?>
					<li><div align="center"><a class="MenuBarItemSubmenu" href="#">Financeiro</a></div>
						<ul>
							<li><a href="cadastro.php#tab2Cadastrar">An�lise de Cr�dito</a></li>
							<li><a href="financeiro_caixa.php">Caixa</a></li>
							<li><div align="left"><a class="MenuBarItemSubmenu" href="#">Contas a Receber</a></div>
								<ul>
									<li><a href="contas_receber.php">Contas a Receber</a></li>
									<li><a href="contas_receber_new.php">Contas a Receber - Vers�o BETA</a></li>
									<li><a href="financeiro_cliente.php">Situa��o do cliente</a></li>
									<li><a href="remessa.php">Remessa Banc�ria</a></li>
									<li><a href="retorno.php">Processa Retorno Banc�ria</a></li>
									<li><a href="retorno_consulta.php">Consulta Retorno Banc�rio</a></li>
								</ul>
							</li>
							<li><div align="left"><a class="MenuBarItemSubmenu" href="#">Contas a Pagar</a></div>
								<ul>
									<!-- <li><a href="contas_pagar.php">Contas a Pagar</a></li> -->
									<li><a href="contas_pagar_new.php">Contas a Pagar</a></li>
									<li><a href="financeiro_fornecedor.php">Situa��o do fornecedor</a></li>
								</ul>
							</li>
							<li><a href="financeiro_caixa_banco.php">Caixa/Banco</a></li>
						</ul>
					</li>
					<?}//FIM
					//MENU CRM
					 if ($acesso[$x] == "CRM"){?>
					<li><div align="center"><a class="MenuBarItemSubmenu" href="#">CRM</a></div>
						<ul>
							<li><a href="crm_orcamento.php?tipo_orcamento=fora_garantia">Or�amento de Servi�o</a></li>
							<li><a href="crm_orcamento.php?tipo_orcamento=venda">Or�amento de Venda</a></li>
							<!-- <li><a href="#">Satisfa��o (indisponivel)</a></li> -->
						</ul>
					</li>
					<?}
					 if ($acesso[$x] == "gerencial"){?>
						<li><div align="center"><a class="MenuBarItemSubmenu" href="#">Gerencial</a></div>
						<ul>
							<li><a href="gerencial_caixa.php">Fluxo de Caixa</a></li>
							<li><a href="caixa_orcamento.php">Or�amento </a></li>
							<!-- <li><a href="#">Plano Contas (indisponivel)</a></li> -->
							<li><a href="gerencial_conta_banco.php">Livro di�rio </a></li>
							<!-- <li><a href="#">CRM (indisponivel)</a></li> -->
							<li><a href="_empresa_configuracao.php">Configura��es de Empresa</a></li>
							<li><a href="admin_senha.php">Permiss�es de acesso</a></li>
						</ul>
					</li>
				<?}//FIM
				//MENU FRANQUEADOS
				 if ($acesso[$x] == "franqueados" and 1==2){?>
					<li><div align="center"><a class="MenuBarItemSubmenu" href="#">Franqueados</a></div>
						<ul>
						<!--<li><a href="#">Or�amento (indisponivel)</a></li> 
							<li><a href="#">Plano Contas (indisponivel)</a></li>
							<li><a href="gerencial_conta_banco.php">Livro di�rio </a></li>
							<li><a href="#">CRM (indisponivel)</a></li> -->
						</ul>
					</li>
				<?}//FIM
				//MENU RELATORIOS
				 if ($acesso[$x] == "relatorios"){?>
					<li><div align="center"><a class="MenuBarItemSubmenu" href="#">Relat�rios</a></div>
						<ul>
							<li><a href="contas_pagar_relatorio.php">Contas a Pagar</a></li>
							<!-- <li><a href="#">Or�amento (indisponivel)</a></li>
							<li><a href="#">Plano Contas (indisponivel)</a></li>
							<li><a href="gerencial_conta_banco.php">Livro di�rio </a></li>
							<li><a href="#">CRM (indisponivel)</a></li> -->
						</ul>
					</li>
				<?}//FIM
				//MENU ECOMMERCE
				 if ($acesso[$x] == "ecommerce" and 1==2){?>
					<li><div align="center"><a class="MenuBarItemSubmenu" href="#">E-commerce</a></div>
						<ul>
							<!-- <li><a href="#">Or�amento (indisponivel)</a></li> -->
							<!-- <li><a href="#">Plano Contas (indisponivel)</a></li> -->
							<!-- <li><a href="gerencial_conta_banco.php">Livro di�rio </a></li> -->
							<!-- <li><a href="#">CRM (indisponivel)</a></li> -->
						</ul>
					</li>
				<?}//FIM
				// MENU MARKETING
				 if ($acesso[$x] == "marketing" and 1==2){?>
					<li><div align="center"><a class="MenuBarItemSubmenu" href="#">Marketing</a></div>
						<ul>
							<!-- <li><a href="#">Or�amento (indisponivel)</a></li> -->
							<!-- <li><a href="#">Plano Contas (indisponivel)</a></li> -->
							<!-- <li><a href="gerencial_conta_banco.php">Livro di�rio </a></li> -->
							<!-- <li><a href="#">CRM (indisponivel)</a></li> -->
						</ul>
					</li>
				<?}//FIM
				//MENU ACESSO MASTER
			}//FIM (FOR)?>
		</ul>
		</td>
	</tr>
</table>
<br>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1",{imgDown:"../imagens/SpryMenuBarDownHover.gif", imgRight:"..imagens/SpryMenuBarRightHover.gif"});
 </script>

<?

//echo $login_empregado_nome;

 echo "<center>";
//<font color='red' style='font-size:20;'>Aviso: O sistema estava funcionando. Pelas altera��es na forma de acesso, e nos dados gravados como a rela��o 'fabrica x posto' para: 'empresa x loja'. Ser� necess�rio realizar todas altera��es novamente!</font>";

#------------- Programa Restrito ------------------#
$sql = "SELECT * FROM tbl_empregado WHERE empregado = $login_empregado AND privilegios NOT ILIKE '%*%' ";
$res = pg_exec ($con,$sql);

if (pg_numrows($res) > 0) {
	$sql = "SELECT *
			FROM   tbl_erp_programa_restrito
			WHERE  tbl_erp_programa_restrito.programa = '$PHP_SELF'
			AND    tbl_erp_programa_restrito.fabrica = $login_empresa";
$res = pg_exec ($con,$sql);

if (pg_numrows($res) > 0) {
		$sql = "SELECT *
				FROM   tbl_erp_programa_restrito
				JOIN   tbl_empregado USING (empregado)
				WHERE  tbl_erp_programa_restrito.programa  = '$PHP_SELF'
				AND    tbl_erp_programa_restrito.empregado = $login_empregado
				AND    tbl_erp_programa_restrito.fabrica   = $login_empresa";
		$res = pg_exec ($con,$sql);
		if (pg_numrows ($res) == 0) {
			echo "<script>"; 
				echo "window.location.href = 'menu_inicial.php?msg_erro=Voc� n�o tem permiss�o para acessar a tela.'";
			echo "</script>";

			}
		}
}
//echo "restricao \n $sql";
//include "monitora.php";
?>

