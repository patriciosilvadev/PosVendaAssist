<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';

$admin_privilegios="financeiro";
include 'autentica_admin.php';

$layout_menu = "financeiro";
$title = "Reporte de Valores de Extractos";

include 'cabecalho.php';

?>

<style type="text/css">
.Titulo {
	text-align: center;
	font-family: Arial;
	font-size: 9px;
	font-weight: bold;
	color: #FFFFFF;
	background-color: #485989;
}
.Conteudo {
	font-family: Arial;
	font-size: 9px;
	font-weight: normal;
}
.Mes{
	font-size: 8px;
}
</style>


<?

//--=== RESULTADO DA PESQUISA ====================================================--\\

$data_inicial = $_POST['data_inicial'];
$data_final   = $_POST['data_final']  ;

if (strlen($_GET['data_inicial']) > 0) $data_inicial = $_GET['data_inicial'];
if (strlen($_GET['data_final']) > 0)   $data_final   = $_GET['data_final']  ;

$data_inicial = str_replace (" " , "" , $data_inicial);
$data_inicial = str_replace ("-" , "" , $data_inicial);
$data_inicial = str_replace ("/" , "" , $data_inicial);
$data_inicial = str_replace ("." , "" , $data_inicial);

$data_final   = str_replace (" " , "" , $data_final)  ;
$data_final   = str_replace ("-" , "" , $data_final)  ;
$data_final   = str_replace ("/" , "" , $data_final)  ;
$data_final   = str_replace ("." , "" , $data_final)  ;

if (strlen ($data_inicial) == 6) $data_inicial = substr ($data_inicial,0,4) . "20" . substr ($data_inicial,4,2);
if (strlen ($data_final)   == 6) $data_final   = substr ($data_final  ,0,4) . "20" . substr ($data_final  ,4,2);

if (strlen ($data_inicial) > 0)  $data_inicial = substr ($data_inicial,0,2) . "/" . substr ($data_inicial,2,2) . "/" . substr ($data_inicial,4,4);
if (strlen ($data_final)   > 0)  $data_final   = substr ($data_final,0,2)   . "/" . substr ($data_final,2,2)   . "/" . substr ($data_final,4,4);

/*nao agrupado  takashi 21-12 HD 916*/
if(strlen($data_inicial)>0 AND strlen($data_final)>0 AND $agrupar<>'sim'){

	$sql = "SELECT  tbl_posto.nome                                              ,
					tbl_posto.estado                                            ,
					tbl_posto_fabrica.codigo_posto                                      ,
					tbl_posto_fabrica.reembolso_peca_estoque                                , 
					TO_CHAR(tbl_extrato.data_geracao,'DD/MM/YYYY')    AS data_geracao   ,
					tbl_extrato.extrato                                                 ,
					tbl_extrato.protocolo                                               ,
					tbl_extrato.mao_de_obra                                             ,
					tbl_extrato.pecas                                                   ,
					tbl_extrato.avulso                                                  ,
					tbl_extrato.total                                                   ,
					( SELECT count(tbl_os.os) 
						FROM tbl_os
						JOIN tbl_os_extra ON tbl_os_extra.os = tbl_os.os
						WHERE tbl_os_extra.extrato = tbl_extrato.extrato
					)                                                 AS total_os
				FROM tbl_extrato
				JOIN tbl_extrato_extra ON tbl_extrato_extra.extrato = tbl_extrato.extrato	
				JOIN tbl_posto         ON tbl_posto.posto           = tbl_extrato.posto
				JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto   = tbl_extrato.posto 
							AND tbl_posto_fabrica.fabrica = $login_fabrica
				WHERE tbl_extrato.fabrica = $login_fabrica 
						AND tbl_posto.pais='$login_pais' ";

	if (strlen ($data_inicial) < 8) $data_inicial = date ("d/m/Y");
		$x_data_inicial = substr ($data_inicial,6,4) . "-" . substr ($data_inicial,3,2) . "-" . substr ($data_inicial,0,2);
	
	if (strlen ($data_final) < 8) $data_final = date ("d/m/Y");
		$x_data_final = substr ($data_final,6,4) . "-" . substr ($data_final,3,2) . "-" . substr ($data_final,0,2);
	
	if (strlen ($x_data_inicial) > 0 AND strlen ($x_data_final) > 0){
		$sql .= " AND tbl_extrato.data_geracao BETWEEN '$x_data_inicial 00:00:00' AND '$x_data_final 23:59:59'";
	}

	$sql .= " ORDER BY tbl_posto.nome";

	$res = pg_exec ($con,$sql);

	if (pg_numrows($res) > 0) {

		flush();
	
		echo "<br><br>";
		echo "<table width='600' border='0' cellspacing='2' cellpadding='2' align='center'>";
		echo "<tr>";
		echo "<td align='center'><font face='Arial, Verdana, Times, Sans' size='2' color='#000000'>Aguarde, processando arquivo PostScript</font></td>";
		echo "</tr>";
		echo "</table>";
	
		flush();
	
		$data = date ("dmY");

		echo `rm /tmp/assist/relatorio_pagamento_posto-$login_fabrica.xls`;

		$fp = fopen ("/tmp/assist/relatorio_pagamento_posto-$login_fabrica.html","w");

		fputs ($fp,"<html>");
		fputs ($fp,"<head>");
		fputs ($fp,"<title>Reporte de Valores de Extractos - $data");
		fputs ($fp,"</title>");
		fputs ($fp,"<meta name='Author' content='TELECONTROL NETWORKING LTDA'>");
		fputs ($fp,"</head>");
		fputs ($fp,"<body>");


		fputs ($fp,"<br><table border='1' cellpadding='2' cellspacing='0' style='border-collapse: collapse' bordercolor='#d2e4fc' align='center'>");
		fputs ($fp,"<tr class='Titulo'>");
		fputs ($fp,"<td >C�DIGO</td>");
		fputs ($fp,"<td >NOMBRE DEL SERVICIO</td>");
		fputs ($fp,"<td >PROVINCIA</td>");
		fputs ($fp,"<td >Pedido<BR>Garantia</td>");
		fputs ($fp,"<td >EXTRACTO</td>");
		fputs ($fp,"<td >GENERACI�N</td>");
		fputs ($fp,"<td align='center'>M.O</td>");
		fputs ($fp,"<td >REPUESTO</td>");
		fputs ($fp,"<td >AVULSO</td>");
		fputs ($fp,"<td >TOTAL</td>");
		fputs ($fp,"<td >TOTAL<br>OS</td>");
		fputs ($fp,"</tr>");

		for ($i=0; $i<pg_numrows($res); $i++){

			$nome                    = trim(pg_result($res,$i,nome))          ;
			$estado                  = trim(pg_result($res,$i,estado))        ;
			$codigo_posto            = trim(pg_result($res,$i,codigo_posto))  ;
			$extrato                 = trim(pg_result($res,$i,extrato))       ;
			$protocolo               = trim(pg_result($res,$i,protocolo))     ;
			$data_geracao            = trim(pg_result($res,$i,data_geracao))  ;
			$mao_de_obra             = trim(pg_result($res,$i,mao_de_obra))   ;
			$pecas                   = trim(pg_result($res,$i,pecas))         ;
			$avulso                  = trim(pg_result($res,$i,avulso))        ;
			$total                   = trim(pg_result($res,$i,total))         ;
			$total_os                = trim(pg_result($res,$i,total_os))      ;
			$pedido_em_garantia      = trim(pg_result($res,$i,reembolso_peca_estoque))      ;
			if($pedido_em_garantia=='t'){$pedido_em_garantia="S�";}else{$pedido_em_garantia="No";}

			if($cor=="#F1F4FA")$cor = '#F7F5F0';
			else               $cor = '#F1F4FA';

			$pecas       = number_format ($pecas,2,",",".")      ;
			$mao_de_obra = number_format ($mao_de_obra,2,",",".");
			$avulso      = number_format ($avulso,2,",",".")     ;
			$total       = number_format ($total,2,",",".")      ;
		
			fputs ($fp,"<tr class='Conteudo'>");
			fputs ($fp,"<td bgcolor='$cor' >$codigo_posto</td>");
			fputs ($fp,"<td bgcolor='$cor' align='left' title='nome'>".substr($nome,0,20)."</td>");
			fputs ($fp,"<td bgcolor='$cor' align='center'>$estado</td>");
			fputs ($fp,"<td bgcolor='$cor' align='center'>$pedido_em_garantia</td>");
			fputs ($fp,"<td bgcolor='$cor' >");
			fputs ($fp,$extrato);
			fputs ($fp,"</td>");
		
			fputs ($fp,"<td bgcolor='$cor' >$data_geracao</td>");
			fputs ($fp,"<td bgcolor='$cor' align='right'>$ $mao_de_obra</td>");
			fputs ($fp,"<td bgcolor='$cor' align='right'>$ $pecas</td>");
			fputs ($fp,"<td bgcolor='$cor' align='right'>$ $avulso</td>");
			fputs ($fp,"<td bgcolor='$cor' align='right'>$ $total</td>");
			fputs ($fp,"<td bgcolor='$cor' align='center'>$total_os</td>");
			fputs ($fp,"</tr>");
		}

		fputs ($fp,"</table>");
		fputs ($fp,"</body>");
		fputs ($fp,"</html>");
		fclose ($fp);

		echo `htmldoc --webpage --size 297x210mm --fontsize 11 --left 8mm -f /www/assist/www/admin_es/xls/relatorio_pagamento_posto-$login_fabrica.$data.xls /tmp/assist/relatorio_pagamento_posto-$login_fabrica.html`;
		
		echo"<table  border='0' cellspacing='2' cellpadding='2' align='center'>";
		echo"<tr>";
		echo "<td><img src='imagens/excell.gif'></td><td align='center'<font face='Arial, Verdana, Times, Sans' size='2' color='#000000'>Haga um click para hacer </font><a href='xls/relatorio_pagamento_posto-$login_fabrica.$data.xls'><font face='Arial, Verdana, Times, Sans' size='2' color='#0000FF'>download del archivo en Excel</font></a>.<br><font face='Arial, Verdana, Times, Sans' size='2' color='#000000'>Puedes ver, imprimir y guardar la tabla para buscas off-line.</font></td>";
		echo "</tr>";
		echo "</table>";
	}
}
/*sem agrupar  takashi 21-12 HD 916*/

/*AGRUPADO takashi 21-12 HD 916*/
if(strlen($data_inicial)>0 AND strlen($data_final)>0 AND $agrupar=='sim'){

$sql = "SELECT 	X.posto, 
				X.nome, 
				X.estado, 
				X.tipo_posto,
				X.reembolso_peca_estoque       ,
				sum(X.mao_de_obra) as mao, 
				sum(X.pecas) as pecas, 
				sum(X.avulso) as avulso, 
				sum(X.total) as total, 
				sum(X.total_os) as total_os 
			FROM (SELECT tbl_posto_fabrica.codigo_posto as posto,
						tbl_posto_fabrica.reembolso_peca_estoque            ,
						tbl_posto.nome as nome,
						tbl_posto.estado as estado,
						tbl_tipo_posto.descricao as tipo_posto,
						tbl_extrato.mao_de_obra as mao_de_obra,
						tbl_extrato.pecas as pecas,
						tbl_extrato.avulso as avulso,
						tbl_extrato.total as total,
						(select count(tbl_os.os) from tbl_os join tbl_os_extra on tbl_os_extra.os= tbl_os.os where tbl_os_extra.extrato= tbl_extrato.extrato) as total_os
				FROM tbl_extrato
				JOIN tbl_extrato_extra ON tbl_extrato_extra.extrato = tbl_extrato.extrato 
				JOIN tbl_posto ON tbl_posto.posto = tbl_extrato.posto 
				JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_extrato.posto 
				AND tbl_posto_fabrica.fabrica = $login_fabrica 
				JOIN tbl_tipo_posto on tbl_posto_fabrica.tipo_posto=tbl_tipo_posto.tipo_posto
				WHERE   tbl_extrato.fabrica = $login_fabrica 
					AND tbl_posto.pais='$login_pais' ";



	if (strlen ($data_inicial) < 8) $data_inicial = date ("d/m/Y");
		$x_data_inicial = substr ($data_inicial,6,4) . "-" . substr ($data_inicial,3,2) . "-" . substr ($data_inicial,0,2);
	
	if (strlen ($data_final) < 8) $data_final = date ("d/m/Y");
		$x_data_final = substr ($data_final,6,4) . "-" . substr ($data_final,3,2) . "-" . substr ($data_final,0,2);
	
	if (strlen ($x_data_inicial) > 0 AND strlen ($x_data_final) > 0) {
		$sql .= " AND tbl_extrato.data_geracao BETWEEN '$x_data_inicial 00:00:00' AND '$x_data_final 23:59:59' order by tbl_posto.nome) as X";
	}

	$sql .= " GROUP BY posto, nome, estado, tipo_posto, reembolso_peca_estoque
			order by nome";
	
	$res = pg_exec ($con,$sql);

if (pg_numrows($res) > 0) {

	flush();
	
	echo "<br><br>";
	echo "<table width='600' border='0' cellspacing='2' cellpadding='2' align='center'>";
	echo "<tr>";
	echo "<td align='center'><font face='Arial, Verdana, Times, Sans' size='2' color='#000000'>Aguarde, procesando archivo PostScript</font></td>";
	echo "</tr>";
	echo "</table>";
	
	flush();
	
	$data = date ("dmY");

	echo `rm /tmp/assist/relatorio_pagamento_posto_agrupado-$login_fabrica.xls`;

	$fp = fopen ("/tmp/assist/relatorio_pagamento_posto_agrupado-$login_fabrica.html","w");

	fputs ($fp,"<html>");
	fputs ($fp,"<head>");
	fputs ($fp,"<title>Reporte de Valores de Extractos - $data");
	fputs ($fp,"</title>");
	fputs ($fp,"<meta name='Author' content='TELECONTROL NETWORKING LTDA'>");
	fputs ($fp,"</head>");
	fputs ($fp,"<body>");


	fputs ($fp,"<br><table border='1' cellpadding='2' cellspacing='0' style='border-collapse: collapse' bordercolor='#d2e4fc' align='center'>");
	fputs ($fp,"<tr class='Titulo'>");
	fputs ($fp,"<td >C�DIGO</td>");
	fputs ($fp,"<td >NOMBRE DEL SERVICIO</td>");
	fputs ($fp,"<td >PROVINCIA</td>");
	fputs ($fp,"<td >TIPO SERVICIO</td>");
	fputs ($fp,"<td >Pedido<BR>Garantia</td>");
	fputs ($fp,"<td align='center'>M.O</td>");
	fputs ($fp,"<td >REPUESTO</td>");
	fputs ($fp,"<td >AVULSO</td>");
	fputs ($fp,"<td >TOTAL</td>");
	fputs ($fp,"<td >TOTAL<br>OS</td>");
	fputs ($fp,"</tr>");



	for ($i=0; $i<pg_numrows($res); $i++){

			$nome                    = trim(pg_result($res,$i,nome))          ;
			$estado                  = trim(pg_result($res,$i,estado))        ;
			$codigo_posto            = trim(pg_result($res,$i,posto))         ;
			$mao_de_obra             = trim(pg_result($res,$i,mao))           ;
			$pecas                   = trim(pg_result($res,$i,pecas))         ;
			$avulso                  = trim(pg_result($res,$i,avulso))        ;
			$total                   = trim(pg_result($res,$i,total))         ;
			$total_os                = trim(pg_result($res,$i,total_os))      ;
			$tipo_posto                = trim(pg_result($res,$i,tipo_posto))  ;
			$pedido_em_garantia        = trim(pg_result($res,$i,reembolso_peca_estoque))  ;
			if($pedido_em_garantia=='t'){$pedido_em_garantia="S�";}else{$pedido_em_garantia="No";}

		if($cor=="#F1F4FA")$cor = '#F7F5F0';
		else               $cor = '#F1F4FA';

		$pecas       = number_format ($pecas,2,",",".")      ;
		$mao_de_obra = number_format ($mao_de_obra,2,",",".");
		$avulso      = number_format ($avulso,2,",",".")     ;
		$total       = number_format ($total,2,",",".")      ;
		
		

		fputs ($fp,"<tr class='Conteudo'>");
		fputs ($fp,"<td bgcolor='$cor' >$codigo_posto</td>");
		fputs ($fp,"<td bgcolor='$cor' align='left' title='nome'>".substr($nome,0,20)."</td>");	
		fputs ($fp,"<td bgcolor='$cor' align='center'>$estado</td>");
		fputs ($fp,"<td bgcolor='$cor' align='center'>$tipo_posto</td>");
		fputs ($fp,"<td bgcolor='$cor' align='center'>$pedido_em_garantia</td>");
		fputs ($fp,"<td bgcolor='$cor' align='right'>$ $mao_de_obra</td>");
		fputs ($fp,"<td bgcolor='$cor' align='right'>$ $pecas</td>");
		fputs ($fp,"<td bgcolor='$cor' align='right'>$ $avulso</td>");
		fputs ($fp,"<td bgcolor='$cor' align='right'>$ $total</td>");
		fputs ($fp,"<td bgcolor='$cor' align='center'>$total_os</td>");
		fputs ($fp,"</tr>");
	}
	fputs ($fp,"</table>");
	fputs ($fp,"</body>");
	fputs ($fp,"</html>");
	fclose ($fp);

	echo `htmldoc --webpage --size 297x210mm --fontsize 11 --left 8mm -f /www/assist/www/admin_es/xls/relatorio_pagamento_posto_agrupado-$login_fabrica.$data.xls /tmp/assist/relatorio_pagamento_posto_agrupado-$login_fabrica.html`;
	
	echo"<table  border='0' cellspacing='2' cellpadding='2' align='center'>";
	echo"<tr>";
	echo "<td><img src='imagens/excell.gif'></td><td align='center'<font face='Arial, Verdana, Times, Sans' size='2' color='#000000'>Haga um click para hacer </font><a href='xls/relatorio_pagamento_posto_agrupado-$login_fabrica.$data.xls'><font face='Arial, Verdana, Times, Sans' size='2' color='#0000FF'> download del archivo en Excel</font></a>.<br><font face='Arial, Verdana, Times, Sans' size='2' color='#000000'>Puedes ver, imprimir y guardar la tabla para buscas off-line.</font></td>";
	echo "</tr>";
	echo "</table>";
	}
	
}
/*agrupado takashi 21-12 HD 916*/






?>
