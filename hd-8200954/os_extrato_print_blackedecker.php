<?php
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';

$admin_privilegios="financeiro";
include 'autentica_admin.php';

if ($login_fabrica <> 1) {
	header ("Location: menu_financeiro.php");
	exit;
}

if (strlen(trim($_GET["extrato"])) > 0) $extrato = trim($_GET["extrato"]);

$sql = "SELECT  tbl_posto_fabrica.tipo_posto            ,
				tbl_posto_fabrica.posto                 ,
				tbl_posto_fabrica.reembolso_peca_estoque
		FROM    tbl_posto_fabrica
		JOIN    tbl_extrato ON tbl_extrato.posto = tbl_posto_fabrica.posto
		WHERE   tbl_extrato.extrato       = $extrato
		AND     tbl_posto_fabrica.fabrica = $login_fabrica;";
$res = pg_exec($con,$sql);

if (pg_numrows($res) == 1) {
	$posto                  = trim(pg_result($res,0,posto));
	$tipo_posto             = trim(pg_result($res,0,tipo_posto));
	$reembolso_peca_estoque = trim(pg_result($res,0,reembolso_peca_estoque));
}


$layout_menu = "financeiro";
$title = "Black & Decker - Detalhe Extrato - Ordem de Servi�o";
?>

<html>

<head>
<title><? echo $title ?></title>
<meta http-equiv="content-Type"  content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires"       content="0">
<meta http-equiv="Pragma"        content="no-cache, public">
<meta http-equiv="Cache-control" content="no-cache, public, must-revalidate, post-check=0, pre-check=0">
<link type="text/css" rel="stylesheet" href="css/css_press.css">

<style>
/*******************************
 ELEMENTOS DE COR FONTE EXTRATO 
*******************************/
.TdBold   {font-weight: bold;}
.TdNormal {font-weight: normal;}
</style>

</head>

<body>
<TABLE width="600px" border="0" cellspacing="1" cellpadding="0">
<TR>
	<TD><IMG SRC="logos/cabecalho_print_<? echo strtolower ($login_fabrica_nome) ?>.gif" ALT="ORDEM DE SERVI�O"></TD>
</TR>
</TABLE>

<br>

<?
if (strlen($extrato) > 0) {
	$data_atual = date("d/m/Y");

	$sql = "SELECT  to_char(min(tbl_os.data_fechamento),'DD/MM/YYYY') AS inicio,
					to_char(max(tbl_os.data_fechamento),'DD/MM/YYYY') AS final
			FROM    tbl_os
			JOIN    tbl_os_extra USING (os)
			WHERE   tbl_os_extra.extrato = $extrato;";
	$res = pg_exec($con,$sql);
	
	if (pg_numrows($res) > 0) {
		$inicio_extrato = trim(pg_result($res,0,'inicio'));
		$final_extrato  = trim(pg_result($res,0,'final'));
	}

	if (strlen($inicio_extrato) == 0 AND strlen($final_extrato) == 0) {
		$sql = "SELECT  to_char(min(tbl_extrato.data_geracao),'DD/MM/YYYY') AS inicio,
						to_char(max(tbl_extrato.data_geracao),'DD/MM/YYYY') AS final
				FROM    tbl_extrato
				WHERE   tbl_extrato.extrato = $extrato";
		$res = pg_exec ($con,$sql);

		if (pg_numrows($res) > 0) {
			$inicio_extrato = trim(pg_result($res,0,'inicio'));
			$final_extrato  = trim(pg_result($res,0,'final'));
		}
	}

	$sql = "SELECT  tbl_posto_fabrica.codigo_posto                          ,
					tbl_posto.posto                                         ,
					tbl_posto.nome                                          ,
					tbl_posto.endereco                                      ,
					tbl_posto.cidade                                        ,
					tbl_posto.estado                                        ,
					tbl_posto.cep                                           ,
					tbl_posto.fone                                          ,
					tbl_posto.fax                                           ,
					tbl_posto.contato                                       ,
					tbl_posto.email                                         ,
					tbl_posto.cnpj                                          ,
					tbl_posto.ie                                            ,
					tbl_posto_fabrica.banco                                 ,
					tbl_posto_fabrica.agencia                               ,
					tbl_posto_fabrica.conta                                 ,
					tbl_extrato.protocolo                                   ,
					to_char(tbl_extrato.data_geracao, 'DD/MM/YYYY') AS data 
			FROM    tbl_posto
			JOIN    tbl_posto_fabrica ON  tbl_posto.posto           = tbl_posto_fabrica.posto
									  AND tbl_posto_fabrica.fabrica = $login_fabrica
			JOIN    tbl_extrato ON tbl_extrato.posto = tbl_posto.posto
			WHERE   tbl_extrato.extrato = $extrato;";
	$res = pg_exec ($con,$sql);

	if (pg_numrows($res) > 0) {
		$codigo        = trim(pg_result($res,0,codigo_posto));
		$posto         = trim(pg_result($res,0,posto));
		$nome          = trim(pg_result($res,0,nome));
		$endereco      = trim(pg_result($res,0,endereco));
		$cidade        = trim(pg_result($res,0,cidade));
		$estado        = trim(pg_result($res,0,estado));
		$cep           = substr(pg_result($res,0,cep),0,2) .".". substr(pg_result($res,0,cep),2,3) ."-". substr(pg_result($res,0,cep),5,3);
		$fone          = trim(pg_result($res,0,fone));
		$fax           = trim(pg_result($res,0,fax));
		$contato       = trim(pg_result($res,0,contato));
		$email         = trim(pg_result($res,0,email));
		$cnpj          = trim(pg_result($res,0,cnpj));
		$ie            = trim(pg_result($res,0,ie));
		$banco         = trim(pg_result($res,0,banco));
		$agencia       = trim(pg_result($res,0,agencia));
		$conta         = trim(pg_result($res,0,conta));
		$data_extrato  = trim(pg_result($res,0,data));
		$protocolo     = trim(pg_result($res,0,protocolo));
		
		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' width='100%' align='left' colspan='2'>\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'><b>BLACK & DECKER DO BRASIL LTDA</b></font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' width='50%' align='left'>\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'>Rod. BR 050 S/N KM 167-LOTE 5 QVI - DI II</font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' width='50%' align='left'>\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'>Uberaba - MG - 38064-750</font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' width='50%' align='left'>\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'>Inscri��o CNPJ: 53.296.273/0001-91</font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' width='50%' align='left'>\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'>Inscri��o Estadual: 701.948.711.00-98</font>\n";
		echo "</td>\n";

		echo "</tr>\n";
		echo "</table>\n";
		
		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' width='100%' align='left'>\n";
		echo "<hr>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "</table>\n";
		
		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'><b>NOTA DE CR�DITO $data_extrato</b></font>\n";
		echo "</td>\n";

		echo "<td bgcolor='#FFFFFF' nowrap align='right' >\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'><b>$protocolo</b></font>\n";
		echo "</td>\n";

		echo "</tr>\n";
		echo "</table>\n";
		
		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' width='100%' align='left'>\n";
		echo "<hr>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "</table>\n";
		
		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='70' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Per�odo:</b></font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='100' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$inicio_extrato</font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='40' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>At�:</b></font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='120' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$final_extrato</font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='40' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Data:</b></font>\n";
		echo "</td>\n";

		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='230' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$data_atual</font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "</table>\n";
		
		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='70' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>C�digo:</b></font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='530' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$codigo</font>\n";
		echo "</td>\n";

		echo "</tr>\n";
		echo "</table>\n";

		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='70' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Posto:</b></font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left' nowrap>\n";
		echo "<img src='imagens/pixel.gif' width='530' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$nome</font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "</table>\n";
		
		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='70' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Endere�o:</b></font>\n";
		echo "</td>\n";

		echo "<td bgcolor='#FFFFFF' align='left' nowrap>\n";
		echo "<img src='imagens/pixel.gif' width='530' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$endereco</font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "</table>\n";
		
		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left' width='70'>\n";
		echo "<img src='imagens/pixel.gif' width='70' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Cidade:</b></font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='530' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$cidade - $estado - $cep</font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "</table>\n";
		
		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";

		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='70' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Telefone:</b></font>\n";
		echo "</td>\n";

		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='100' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$fone</font>\n";
		echo "</td>\n";

		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='40' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>&nbsp;&nbsp;&nbsp;Fax:</b></font>\n";
		echo "</td>\n";

		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='100' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$fax</font>\n";
		echo "</td>\n";

		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='40' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>&nbsp;&nbsp;&nbsp;E-mail:</b></font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='240' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$email</font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "</table>\n";
		
		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='70' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>CNPJ:</b></font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='130' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$cnpj</font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='30' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>IE:</b></font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left'>\n";
		echo "<img src='imagens/pixel.gif' width='370' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$ie</font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "</table>\n";


		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left' nowrap>\n";
		echo "<img src='imagens/pixel.gif' width='70' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Banco:</b></font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left' nowrap>\n";
		echo "<img src='imagens/pixel.gif' width='530' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$banco</font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "<tr>\n";

		echo "<td bgcolor='#FFFFFF' align='left' nowrap>\n";
		echo "<img src='imagens/pixel.gif' width='70' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Ag�ncia:</b></font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left' nowrap>\n";
		echo "<img src='imagens/pixel.gif' width='530' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$agencia</font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "<tr>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left' nowrap>\n";
		echo "<img src='imagens/pixel.gif' width='70' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Conta:</b></font>\n";
		echo "</td>\n";
		
		echo "<td bgcolor='#FFFFFF' align='left' nowrap>\n";
		echo "<img src='imagens/pixel.gif' width='530' height='1'><br><font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$conta</font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "</table>\n";
	}
	
	
	$sql = "SELECT  tbl_extrato.total
			FROM    tbl_extrato
			WHERE   tbl_extrato.extrato = $extrato";
	$res = pg_exec ($con,$sql);
	
	if (pg_numrows($res) > 0) {
		$total_GE = pg_result($res,0,total);
		
		$sql = "SELECT SUM (tbl_os_item.qtde * tbl_os_item.custo_peca) 
				FROM tbl_os_item
				JOIN tbl_os_produto USING (os_produto)
				JOIN tbl_os_extra   ON tbl_os_produto.os = tbl_os_extra.os
				JOIN tbl_servico_realizado ON tbl_servico_realizado.servico_realizado = tbl_os_item.servico_realizado
				WHERE   tbl_os_extra.extrato = $extrato
				AND     tbl_servico_realizado.troca_de_peca IS TRUE
				AND     tbl_servico_realizado.gera_pedido IS FALSE; ";
		$res = pg_exec ($con,$sql);
		if (pg_numrows($res) > 0) {
			$total_PC   = pg_result($res,0,0);
		}
		
		$sql = "SELECT  sum(tbl_os.mao_de_obra) AS total_MO
				FROM    tbl_os
				JOIN    tbl_os_extra USING (os)
				WHERE   tbl_os_extra.extrato = $extrato";

		$res = pg_exec ($con,$sql);
		if (pg_numrows($res) > 0) {
			$total_MO   = pg_result($res,0,total_MO);
		}
		
		$sql = "SELECT  tbl_extrato.avulso AS total_DP_S FROM tbl_extrato WHERE extrato = $extrato";
		$res = pg_exec ($con,$sql);
		if (pg_numrows($res) > 0) {
			$total_DP_S = pg_result($res,0,total_DP_S);
		}
		
		$total_PC = $total_PC + $total_RE;
	}
		

	// DESPESAS AVULSAS
	$sql = "SELECT  sum(valor) AS total_Avulso
			FROM    tbl_extrato_lancamento
			WHERE   extrato = $extrato
			AND     lancamento not in (40,41,42)";
	$res = pg_exec ($con,$sql);
	
	if (pg_numrows($res) > 0) {
		$total_AV = pg_result($res,0,total_Avulso);
	}


	if (strlen($total_MO) == 0) {
		### PARA CASOS APENAS DE ALUGUEL ###
		$total_MO = $total_AV;
		
		echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";

		#N�o est� executando nada aqui (Sono)
/*
		echo "<td width='100%' align='left'>\n";
		if (strlen($total_SD) > 0) {
			echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>OUTROS SERVI�OS PRESTADOS</b></font>\n";
		}else{
			$total_DP_S = $total_SD;
			echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>OUTRAS DESPESAS</b></font>\n";
		}
		echo "</td>\n";
*/
		echo "</tr>\n";
		echo "</table>\n";
	}
	$total_geral = $total_GE;
	
	echo "<table border='0' cellpadding='2' cellspacing='0' width='600' align='center'>\n";
	echo "<tr>\n";
	
	echo "<td width='100%' align='left'>\n";
	echo "<hr>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "</table>\n";
	
	echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
	echo "<tr>\n";
	
	echo "<td width='120' align='left'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Per�odo Inicial:</b></font>\n";
	echo "</td>\n";
	
	echo "<td width='150' align='left'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$inicio_extrato</font>\n";
	echo "</td>\n";
	
	echo "<td width='250' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Sub Total Pe�a OS:&nbsp;</b></font>\n";
	echo "</td>\n";
	
	echo "<td width='50' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>". number_format($total_PC,2,",",".") ."</font>\n";
	echo "</td>\n";
	
	echo "<td width='10%' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'></font>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "</table>\n";


	$sql = "SELECT valor 
			FROM tbl_extrato_lancamento 
			WHERE extrato = $extrato 
			AND lancamento = 47";

	$resX = pg_exec ($con,$sql);
	$taxa_adm = 0 ;
	if (pg_numrows ($resX) > 0) $taxa_adm = pg_result ($resX,0,0);
	
	echo "<table border='0' cellpadding='2' cellspacing='0' width='600' align='center'>\n";
	echo "<tr>\n";
	
	echo "<td width='120' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'></font>\n";
	echo "</td>\n";
	
	echo "<td width='150' align='left'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'></font>\n";
	echo "</td>\n";
	
	echo "<td width='250' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Taxa Administrativa:&nbsp;</b></font>\n";
	echo "</td>\n";
	
	echo "<td width='50' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>". number_format($taxa_adm,2,",",".") ."</font>\n";
	echo "</td>\n";
	
	echo "<td width='10%' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'></font>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "</table>\n";


	$sql = "SELECT valores_adicionais
			FROM tbl_os_extra 
			JOIN tbl_os using(os)
			WHERE tbl_os_extra.extrato = $extrato
			AND tipo_atendimento in (17,18);";

	$res = pg_exec($con,$sql);
	if(pg_numrows($res) > 0){ 
		$total_troca_faturada = pg_result($res,0,valores_adicionais); 
		$total_TF = $total_TF + $total_troca_faturada;
	}

	#---------- Total de Pe�as SEDEX ------------

	$sql = "SELECT  sum(valor) AS total_S_PC
			FROM    tbl_extrato_lancamento
			WHERE   tbl_extrato_lancamento.extrato = $extrato
			AND     tbl_extrato_lancamento.lancamento in (41,42)";
	$res = pg_exec ($con,$sql);
	
	if (pg_numrows($res) > 0) {
		$total_S_PC = pg_result($res,0,total_S_PC);
		$total_S_PC = $total_S_PC + $total_troca_faturada;
	}

	echo "<table border='0' cellpadding='2' cellspacing='0' width='600' align='center'>\n";
	echo "<tr>\n";
	
	echo "<td width='120' align='left'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Per�odo Final:</b></font>\n";
	echo "</td>\n";
	
	echo "<td width='150' align='left'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>$final_extrato</font>\n";
	echo "</td>\n";
	
	echo "<td width='250' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Sub Total Pe�a SEDEX:&nbsp;</b></font>\n";
	echo "</td>\n";
	
	echo "<td width='50' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>". number_format($total_S_PC,2,",",".") ."</font>\n";
	echo "</td>\n";
	
	echo "<td width='10%' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'></font>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "</table>\n";

	echo "<table border='0' cellpadding='2' cellspacing='0' width='600' align='center'>\n";
	echo "<tr>\n";
	
	echo "<td width='120' align='center'>\n";
	echo "</td>\n";
	
	echo "<td width='150' align='left'>\n";
	echo "</td>\n";
	
	echo "<td width='250' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Sub Total M�o-de-obra OS:&nbsp;</b></font>\n";
	echo "</td>\n";
	
	echo "<td width='50' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>". number_format($total_MO,2,",",".") ."</font>\n";
	echo "</td>\n";
	
	echo "<td width='10%' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'></font>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "</table>\n";

	# DESPESAS SEDEX
	$sql = "SELECT  sum(valor) AS total_DP_S
			FROM    tbl_extrato_lancamento
			WHERE   tbl_extrato_lancamento.extrato = $extrato
			AND     tbl_extrato_lancamento.lancamento = 40";
	$res = pg_exec ($con,$sql);
	
	if (pg_numrows($res) > 0) {
		$total_DP_S = pg_result($res,0,total_DP_S);
	}

	echo "<table border='0' cellpadding='2' cellspacing='0' width='600' align='center'>\n";
	echo "<tr>\n";
	
	echo "<td width='120' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'></font>\n";
	echo "</td>\n";
	
	echo "<td width='150' align='left'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'></font>\n";
	echo "</td>\n";
	
	echo "<td width='250' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Sub Total Despesas SEDEX:&nbsp;</b></font>\n";
	echo "</td>\n";
	
	echo "<td width='50' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>". number_format($total_DP_S,2,",",".") ."</font>\n";
	echo "</td>\n";
	
	echo "<td width='10%' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'></font>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "</table>\n";


	if (strlen($total_TF) > 0 AND $total_TF <> 0) {
		$xtotal = $xtotal - $total_TF;
		
		echo "<table border='0' cellpadding='2' cellspacing='0' width='600' align='center'>\n";
		echo "<tr>\n";
		
		echo "<td width='120' align='center'>\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'></font>\n";
		echo "</td>\n";
		
		echo "<td width='150' align='left'>\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'></font>\n";
		echo "</td>\n";
		
		echo "<td width='250' align='right'>\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>Abatimento de Troca Faturada:&nbsp;</b></font>\n";
		echo "</td>\n";
		
		echo "<td width='50' align='right'>\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>". number_format($total_TF,2,",",".") ."</font>\n";
		echo "</td>\n";
		
		echo "<td width='10%' align='right'>\n";
		echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'></font>\n";
		echo "</td>\n";
		
		echo "</tr>\n";
		echo "</table>\n";
	}


	echo "<table border='0' cellpadding='2' cellspacing='0' width='600' align='center'>\n";
	echo "<tr>\n";
	
	echo "<td width='120' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'></font>\n";
	echo "</td>\n";
	
	echo "<td width='150' align='left'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'></font>\n";
	echo "</td>\n";
	
	echo "<td width='250' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>TOTAL GERAL:&nbsp;</b></font>\n";
	echo "</td>\n";
	
	echo "<td width='50' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>". number_format($total_geral,2,",",".") ."</font>\n";
	echo "</td>\n";
	
	echo "<td width='10%' align='right'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='2'></font>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "</table>\n";
	
	echo "<table border='0' cellpadding='0' cellspacing='0' width='600' align='center'>\n";
	echo "<tr>\n";
	
	echo "<td width='100%' align='left'>\n";
	echo "<hr>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "</table>\n";

//////////////////
	echo "<table border='0' cellpadding='0' cellspacing='0' width='650' align='center'>\n";
	echo "<tr>\n";
	
	echo "<td bgcolor='#FFFFFF' width='120' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'><b>APROVA��ES</b></font>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "</table>\n";
	
	echo "<br><br>";
	
	echo "<table border='0' cellpadding='2' cellspacing='2' width='650' align='center'>\n";
	echo "<tr>\n";
	
	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>__________________________</font>\n";
	echo "</td>\n";
	
	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>__________________________</font>\n";
	echo "</td>\n";
	
	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>__________________________</font>\n";
	echo "</td>\n";
	
	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>__________________________</font>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "<tr>\n";
	
	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>Emitente</font>\n";
	echo "</td>\n";
	
	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>Ger. Assist. T�cnica</font>\n";
	echo "</td>\n";

	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>Cr�dito e Cobran�a</font>\n";
	echo "</td>\n";

	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>Contas a pagar</font>\n";
	echo "</td>\n";

	echo "</tr>\n";
	echo "</table>\n";
	
	echo "<br><br>";
	
	echo "<table border='0' cellpadding='2' cellspacing='2' width='650' align='center'>\n";
	echo "<tr>\n";
	
	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>Data: ______/______/______</font>\n";
	echo "</td>\n";
	
	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>Data: ______/______/______</font>\n";
	echo "</td>\n";
	
	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>Data: ______/______/______</font>\n";
	echo "</td>\n";
	
	echo "<td bgcolor='#FFFFFF' width='25%' align='center'>\n";
	echo "<font face='Verdana, Arial, Helvetica, sans' color='#000000' size='-2'>Data: ______/______/______</font>\n";
	echo "</td>\n";
	
	echo "</tr>\n";
	echo "</table>\n";
}
///////////////////
?>
<br>

</body>

</html>
<SCRIPT LANGUAGE="JavaScript">
<!--
//window.print();
//-->
</SCRIPT>
