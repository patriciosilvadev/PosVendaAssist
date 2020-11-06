<?
include_once 'dbconfig.php';
include_once 'includes/dbconnect-inc.php';
include_once 'ajax_cabecalho.php';

if (!function_exists('converte_data')) {
	function converte_data($date) {
		$date = explode("-", preg_replace('/\//', '-', $date));
		$date2 = ''.$date[2].'-'.$date[1].'-'.$date[0];
		if (sizeof($date)==3)
			return $date2;
		else return false;
	}
}

//--==== Cadastrar um t�cnico no treinamento =================================
include 'autentica_usuario.php';

if($_GET['ajax']=='sim' AND $_GET['acao']=='gravar') {
	$qtde         = trim($_POST['qtde']);
	$lote_revenda = trim($_POST['lote_revenda']);

	$sql1 =	"UPDATE tbl_lote_revenda SET
				conferencia = TRUE,
				data_recebido = current_timestamp
			WHERE tbl_lote_revenda.lote_revenda = $lote_revenda";
	$res = @pg_exec($con,$sql1);
	$msg_erro = pg_errormessage($con);

	if (strlen($msg_erro) == 0) {
		$qtde = $qtde+3;

		for ($i = 0 ; $i < $qtde ; $i++) {

			$lote_revenda_item  = trim($_POST['lote_revenda_item_' . $i]);
			$produto_qtde       = trim($_POST['conferencia_qtde_'  . $i]);

			if (strlen ($produto_qtde) == 0) $produto_qtde = 1;
			if (strlen($msg_erro) == 0 AND strlen($lote_revenda_item)>0) {
				$sql2 =	"UPDATE tbl_lote_revenda_item SET
							conferencia_qtde = '$produto_qtde',
							conferencia_data = current_date
						WHERE tbl_lote_revenda_item.lote_revenda_item = $lote_revenda_item";
				$res = @pg_exec($con,$sql2);
				$msg_erro = pg_errormessage($con);
				$aux = $sql2;
			}
//			$teste = "$sql1 $sql2";
		}
	}

	$qtde_item    = trim($_POST['qtde_item']);
//$teste .= "<br> $msg_erro este $qtde_item";
	$qtde_item = $qtde_item+4;
	for ($i = 0 ; $i < $qtde_item ; $i++) {

		$produto_referencia = trim($_POST['referencia_produto_' . $i]);
		$produto_descricao  = trim($_POST['descricao_produto_'  . $i]);
		$produto_qtde       = trim($_POST['produto_qtde_'       . $i]);

		if (strlen($msg_erro) == 0) {
			if (strlen($produto_referencia) > 0 ) {
				$xproduto_referencia = strtoupper($produto_referencia);
				$xproduto_referencia = str_replace("-","",$xproduto_referencia);
				$xproduto_referencia = str_replace(".","",$xproduto_referencia);
				$xproduto_referencia = str_replace("/","",$xproduto_referencia);
				$xproduto_referencia = str_replace(" ","",$xproduto_referencia);

				if (strlen($xproduto_referencia)==0 ) continue;

				if (strlen ($produto_qtde) == 0) $produto_qtde = 1;
				if ($produto_qtde==0)            $msg_erro .= "Informe a quantidade do produto!<br>";

				$sql =" SELECT tbl_produto.produto
						FROM    tbl_produto
						JOIN    tbl_linha USING(linha)
						WHERE   tbl_linha.fabrica = $login_fabrica
						AND tbl_produto.referencia = '$xproduto_referencia' ";
				$res = @pg_exec($con,$sql);

				if (@pg_numrows($res) == 1) {
					$produto = pg_result($res,0,produto);
				}else{
					$msg_erro .= "<br>Pe�a $produto_referencia n�o cadastrada.";
					$linha_erro = $i;
				}
//				$teste .= "<br>aqui2";
				if (strlen($msg_erro) == 0) {
					$sql =	"INSERT INTO tbl_lote_revenda_item (
								lote_revenda    ,
								produto         ,
								qtde            ,
								conferencia_qtde,
								conferencia_data
							) VALUES (
								$lote_revenda,
								$produto        ,
								0               ,
								$produto_qtde   ,
								current_date
							)";
					$res = @pg_exec($con,$sql);
//					$teste .= "<br>$sql";
					$msg_erro .= pg_errormessage($con);
					if (strlen ($msg_erro) > 0) {
						$linha_erro = $i;
						break;
					}
				}
			}
		}
	}

	$resposta .= "\n <br><FONT COLOR='#FF0000'>LOTE J� CONFERIDO, N�O � POSS�VEL LAN�AR PRODUTOS</font><br>\n";
	$resposta .= "\n<P></P>\n<div id='saida' style='display:inline;'></div>\n<div id='erro' style='display:inline;'></div>\n";
	$resposta .= "<input type='button' name='btn_explodir'  value='Explodir' onClick=\"if (this.value!='Explodir'){ alert('Aguarde');}else {this.value='Executando...'; window.location = '$PHP_SELF?explodir=$lote_revenda'}\" style=\"width: 150px;\" class='Explodir'>";


	echo "ok|Conferencia gravado com Sucesso $resposta";
	exit;
}

if($_GET['ajax']=='sim' AND $_GET['acao']=='gravar_produto') {
	$lote_revenda = trim($_POST['lote_revenda']);

	echo "ok|Produtos gravados com Sucesso";
	exit;
}

/* Explodir Lote em OS Revenda */
if(strlen($_GET['explodir'])>0) {

	$lote_revenda = trim($_GET['explodir']);

	$res = pg_exec ($con,"BEGIN TRANSACTION");

	$sql1 = "SELECT
				tbl_lote_revenda_item.lote_revenda_item,
				tbl_lote_revenda_item.conferencia_qtde ,
				tbl_produto.produto
			FROM      tbl_lote_revenda_item
			JOIN      tbl_produto            USING(produto)
			WHERE   tbl_lote_revenda_item.lote_revenda = $lote_revenda ";

	$res1 = @pg_exec ($con,$sql1);
	$msg_erro .= pg_errormessage($con);


	$data = date ("H:i:s");


	$arquivo_nome     = "explode-$data-$login_fabrica.txt";
	$path             = "/tmp/";
	$path_tmp         = "/tmp/";

	$arquivo_completo     = $path.$arquivo_nome;
	$arquivo_completo_tmp = $path_tmp.$arquivo_nome;

	echo `rm -f $arquivo_completo_tmp `;
	echo `rm -f $arquivo_completo `;

//	$fp = fopen ($arquivo_completo_tmp,"w");

	if (pg_numrows($res1) > 0) {
		for ( $i = 0 ; $i < pg_numrows($res1) ; $i++ ){
			$lote_revenda_item  = trim(pg_result($res1,$i,lote_revenda_item));
			$produto            = trim(pg_result($res1,$i,produto));
			$conferencia_qtde   = trim(pg_result($res1,$i,conferencia_qtde));
			//$cont .= "<br>$conferencia_qtde<br>";
			$sql = "INSERT INTO tbl_os_revenda (
						data_abertura,
						revenda,
						posto  ,
						nota_fiscal,
						data_nf,
						fabrica    ,
						lote_revenda
					)SELECT current_date,
							revenda,
							posto,
							nota_fiscal,
							data_nf,
							fabrica,
							lote_revenda
						FROM tbl_lote_revenda
						WHERE lote_revenda = $lote_revenda";
			$res = @pg_exec ($con,$sql);
			$msg_erro .= pg_errormessage($con);
//			fputs ($fp,"$sql\n");
			if (strlen($msg_erro) == 0) {
				$res = @pg_exec($con,"SELECT CURRVAL ('seq_os_revenda')");
				$os_revenda = pg_result($res,0,0);
				$msg_erro .= pg_errormessage($con);
//				fputs ($fp,"$os_revenda\n");
			}
//			fputs ($fp,"Conferencia qtde: $conferencia_qtde\n");
			for( $j=0 ; $j < $conferencia_qtde ; $j++ ){
				$sql = "INSERT INTO tbl_os_revenda_item (
							os_revenda,
							produto,
							nota_fiscal
						)VALUES(
							$os_revenda,
							$produto   ,
							(SELECT nota_fiscal FROM tbl_lote_revenda WHERE lote_revenda = $lote_revenda)
						)";
//				fputs ($fp,"$sql\n");
				$res = @pg_exec ($con,$sql);
				$msg_erro .= pg_errormessage($con);

			}
//			fputs ($fp,"$sql\n----------------------\n");
		}
		$sql = "UPDATE tbl_lote_revenda SET data_explodido = CURRENT_TIMESTAMP WHERE lote_revenda = $lote_revenda";
		$res = @pg_exec ($con,$sql);
		$msg_erro .= pg_errormessage($con);
//		fputs ($fp,"$sql\n----------------------\n");
	}
//	fclose ($fp);
	if (strlen($msg_erro) == 0) {
		$res = pg_exec($con,"COMMIT TRANSACTION");
		$msg = "Lote explodido com Sucesso";
		header ("Location: $PHP_SELF?");
	}else{
		$res = pg_exec($con,"ROLLBACK TRANSACTION");

	}

}

//--==== Ver Lotes da Revenda ================================================
if($_GET['ajax']=='sim' AND $_GET['acao']=='ver') {
	$conferencia = $_GET['conferencia'];
		if($conferencia !='nao_mostra'){
		if ( $conferencia=='recebido') {
			$sql_add1 = " AND data_recebido IS NULL";
			$situacao = "Listagem de Notas n�o recebidas";
		}
		if ( $conferencia=='explodido') {
			$sql_add1 = " AND data_explodido IS NOT NULL";
			$situacao = "Listagem de Notas que j� foram explodidas em OS";
		}
		if ( $conferencia=='nao_explodido') {
			$sql_add1 = "and data_explodido is null and data_recebido is not null ";
			$situacao = "Listagem de Notas que ainda n�o explodidas em OS";
		}

		if ( $conferencia=='areceber') {
			$sql_add1 = "AND data_recebido IS NULL";
			$situacao = "Listagem da legenda";
		}

		if ( $conferencia=='faltaexplodir') {
			$sql_add1 = "AND data_recebido IS NOT NULL AND data_explodido IS NULL";
			$situacao = "Listagem da legenda";
		}
		if ( $conferencia=='lexplodido') {
			$sql_add1 = " AND data_explodido IS NOT NULL";
			$situacao = "Listagem da legenda";
		}
		if ( $conferencia=='parcdevolvido') {
			$join_add = "JOIN tbl_os_revenda using(lote_revenda)
				join tbl_os_revenda_item using(os_revenda)
				join tbl_os on tbl_os.os=tbl_os_revenda_item.os_lote and tbl_os.posto=$login_posto
				join tbl_lote_revenda_item on tbl_lote_revenda.lote_revenda=tbl_lote_revenda_item.lote_revenda";
			$sql_add1 = "and (tbl_lote_revenda_item.qtde - conferencia_qtde) >0
				and data_nf_saida is not null
							AND (
							(
							/*SUBSELECT CORRESPONDE � QTDE_ITENS DA LEGENDA*/
							SELECT count(*) AS QTDE1
							FROM tbl_os
							JOIN tbl_os_revenda_item ON tbl_os.os = tbl_os_revenda_item.os_lote
							JOIN tbl_os_revenda      USING(os_revenda)
							WHERE tbl_os_revenda.lote_revenda   =tbl_lote_revenda.lote_revenda
							)
							-
							(
							/*SUBSELECT CORRESPONDE � ITEM_DEVOLVIDO DA LEGENDA*/
								SELECT count(*)
								FROM tbl_os
								JOIN tbl_os_revenda_item ON tbl_os.os = tbl_os_revenda_item.os_lote
								JOIN tbl_os_revenda      USING(os_revenda)
								WHERE tbl_os_revenda.lote_revenda   = tbl_lote_revenda.lote_revenda
								AND data_nf_saida IS NOT NULL
							))>0
				";
			$situacao = "Listagem da legenda";
		}

		if ( $conferencia=='devolvido') {
			$sql_add1 = "
						AND data_recebido is not null
						AND data_explodido is not null
						AND (
							(
							/*SUBSELECT CORRESPONDE � QTDE_ITENS DA LEGENDA*/
							SELECT count(*) AS QTDE1
							FROM tbl_os
							JOIN tbl_os_revenda_item ON tbl_os.os = tbl_os_revenda_item.os_lote
							JOIN tbl_os_revenda      USING(os_revenda)
							WHERE tbl_os_revenda.lote_revenda   =tbl_lote_revenda.lote_revenda
							)
							-
							(
							/*SUBSELECT CORRESPONDE � ITEM_DEVOLVIDO DA LEGENDA*/
								SELECT count(*)
								FROM tbl_os
								JOIN tbl_os_revenda_item ON tbl_os.os = tbl_os_revenda_item.os_lote
								JOIN tbl_os_revenda      USING(os_revenda)
								WHERE tbl_os_revenda.lote_revenda   = tbl_lote_revenda.lote_revenda
								AND data_nf_saida IS NOT NULL
							))=0
						AND
							(
							/*SUBSELECT CORRESPONDE � QTDE_ITENS DA LEGENDA*/
								SELECT count(*) AS QTDE1
								FROM tbl_os
								JOIN tbl_os_revenda_item ON tbl_os.os = tbl_os_revenda_item.os_lote
								JOIN tbl_os_revenda      USING(os_revenda)
								WHERE tbl_os_revenda.lote_revenda   =tbl_lote_revenda.lote_revenda
							)<> 0
						AND
							(
							/*SUBSELECT CORRESPONDE � ITEM_DEVOLVIDO DA LEGENDA*/
								SELECT count(*)
								FROM tbl_os
								JOIN tbl_os_revenda_item ON tbl_os.os = tbl_os_revenda_item.os_lote
								JOIN tbl_os_revenda      USING(os_revenda)
								WHERE tbl_os_revenda.lote_revenda   = tbl_lote_revenda.lote_revenda
								AND data_nf_saida IS NOT NULL
							)<> 0
						";
			$situacao = "Listagem da legenda";
		}

		if ( $conferencia=='orcamento') {
			$sql_add1 = "
							AND (
								SELECT count(*)
								FROM tbl_os
								JOIN tbl_os_revenda_item ON tbl_os.os = tbl_os_revenda_item.os_lote
								JOIN tbl_os_revenda      USING(os_revenda)
								JOIN tbl_orcamento       ON tbl_orcamento.os = tbl_os.os
								WHERE lote_revenda   =   tbl_LOTE_REVENDA.lote_revenda
							)>0 ";
			$situacao = "Listagem da legenda";
		}
		$sql =" SELECT  distinct tbl_lote_revenda.lote_revenda                                           ,
						tbl_lote_revenda.lote                                                   ,
						TO_CHAR(tbl_lote_revenda.data_digitacao,'dd/mm/YY')    AS data_digitacao,
						TO_CHAR(tbl_lote_revenda.data_recebido ,'dd/mm/YY')    AS data_recebido ,
						TO_CHAR(tbl_lote_revenda.data_explodido,'dd/mm/YY')    AS data_explodido,
						tbl_lote_revenda.nota_fiscal                                            ,
						TO_CHAR(tbl_lote_revenda.data_nf,'dd/mm/YY')           AS data_nf       ,
						tbl_revenda.cnpj                                       AS revenda_cnpj  ,
						tbl_revenda.nome                                       AS revenda_nome
				FROM tbl_lote_revenda
				JOIN tbl_revenda       USING(revenda)
				$join_add
				WHERE tbl_lote_revenda.fabrica = $login_fabrica
				AND   tbl_lote_revenda.posto   = $login_posto
				$sql_add1
				ORDER BY data_digitacao ";
		//echo $sql;
		$res = pg_exec ($con,$sql);
		if(pg_numrows($res) == 0){
			$sql =" SELECT  tbl_lote_revenda.lote_revenda                                           ,
							tbl_lote_revenda.lote                                                   ,
							TO_CHAR(tbl_lote_revenda.data_digitacao,'dd/mm/YY')    AS data_digitacao,
							TO_CHAR(tbl_lote_revenda.data_recebido ,'dd/mm/YY')    AS data_recebido ,
							TO_CHAR(tbl_lote_revenda.data_explodido,'dd/mm/YY')    AS data_explodido,
							tbl_lote_revenda.nota_fiscal                                            ,
							TO_CHAR(tbl_lote_revenda.data_nf,'dd/mm/YY')           AS data_nf       ,
							tbl_revenda.cnpj                                       AS revenda_cnpj  ,
							tbl_revenda.nome                                       AS revenda_nome
					FROM tbl_lote_revenda
					LEFT JOIN tbl_revenda       USING(revenda)
					$join_add
					WHERE tbl_lote_revenda.fabrica = $login_fabrica
					AND   tbl_lote_revenda.posto   = $login_posto
					$sql_add1
					ORDER BY data_digitacao ";
		//echo $sql;
			$res = pg_exec ($con,$sql);

		}

	}
		$resposta  .=  "<table align='center' width='750' border='0' CELLSPANCING='0'>";
		$resposta  .=  "<TR>";

		$resposta  .=  "<TD align='left'>";
		$resposta  .=  "<table style=' border:#485989 1px solid; background-color: #e6eef7'   border='0' CELLSPANCING='0'>";
		$resposta  .=  "<TR class='Conteudo'  height='25'>";
		$resposta  .=  "<TD align='left'><b>Sistema de Revendas</b></TD>";
		$resposta  .=  "</TR>";
		/*
		$resposta  .=  "<TR class='Conteudo'  height='15'>";
		$resposta  .=  "<TD style='text-align:justify'>
			Caro colega,<p>

			A TELECONTROL est�o desenvolvendo um meio de informa��o centralizada para gerenciamento do fluxo de produtos entre a REVENDA <-> REDE AUTORIZADA & REVENDA <-> F�BRICA.<br>
			Trata-se de um sistema via web onde a revenda estar� informando pelo site todos as remessas para conserto, troca e devolu��o enviadas � Rede e � F�brica. A grande vantagem � a informa��o acess�vel e on-line para todos.<br>


			Em breve, quando este sistema estiver completo ser� poss�vel:<br>

			<li> consultar o andamento por Produto, por Nota Fiscal, por Data e por Lote.<br>

			<li> administra��o das pend�ncias e diverg�ncia nas remessas.<br>

			<li> controle eficaz dos prazos.<br>

			<li> importar a rela��o de produtos comercializados entre a BRIT�NIA e a REVENDA com os c�digos internos de ambas.<br>

			<li> solicita��es de coletas e confirma��o de recebimento.<p>
			Informa��o precisa e em tempo real � vital para gerenciar recursos de nossas empresas.

						</TD>";
		$resposta  .=  "</TR>";
		*/
		$resposta  .= "</table>";
		$resposta  .=  "</td>";
		$resposta  .=  "</TR>";
		$resposta  .=  "<tr>";
		$resposta  .=  "<td>";

		$resposta  .=  "<table style=' border:#485989 1px solid; background-color: #e6eef7'   border='0' CELLSPANCING='0'>";
		$resposta  .=  "<TR class='Conteudo'  height='25'>";
		$resposta  .=  "<TD align='left'><b>Legenda</b></TD>";
		$resposta  .=  "</TR>";
		$resposta  .=  "<TR class='Conteudo'  height='15'>";
		$resposta  .=  "<TD align='left'><img src='admin/imagens_admin/status_vermelho.gif' title='� receber'> <a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=areceber\");'style='font-size:10px;'>� receber</a></TD>";
		$resposta  .=  "<TD align='left'><img src='admin/imagens_admin/status_amarelo.gif' title='recebido, falta explodir'> <a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=faltaexplodir\");'style='font-size:10px;'>Recebido, falta explodir</a></TD>";
		$resposta  .=  "<TD align='left' valign='top'><img src='admin/imagens_admin/status_verde.gif' title='explodido'> <a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=lexplodido\");'style='font-size:10px;'>Explodido</a></TD>";
		$resposta  .=  "<TD align='left' valign='top'><img src='admin/imagens_admin/status_azul_bb.gif' title='Parcialmente Devolvido'> <a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=parcdevolvido\");'style='font-size:10px;'>Parc. Devolvido</a></TD>";
		$resposta  .=  "<TD align='left' valign='top'><img src='admin/imagens_admin/status_cinza.gif' title='Lote Devolvido'> <a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=devolvido\");'style='font-size:10px;'>Lote Devolvido</a></TD>";
		$resposta  .=  "<TD align='left' valign='top'><img src='admin/imagens_admin/status_rosa.gif' title='Or�amento'> <a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=orcamento\");'style='font-size:10px;'>Or�amento</a></TD>";
		$resposta  .=  "</TR>";
		$resposta  .= "</table>";
		$resposta  .= "<br><b>$situacao</b><br>";
		$resposta  .= "<a href='javascript:mostrar_treinamento(\"dados\",\"\");' style='font-size:10px;'>Ver todos as notas fiscais</a>&nbsp;&nbsp;&nbsp;";
		$resposta  .= "<a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=explodido\");'style='font-size:10px;'>Ver notas fiscais explodida em OS</a>&nbsp;&nbsp;&nbsp;";
		$resposta  .= "<a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=nao_explodido\");'style='font-size:10px;'>Ver notas fiscais n�o explodida em OS</a>&nbsp;&nbsp;&nbsp;";
		$resposta  .= "<a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=recebido\");'style='font-size:10px;'>Ver notas fiscais a receber</a>";

		$resposta  .=  "</td>";
		$resposta  .=  "</tr>";
		$resposta .= "</table>";

	if (pg_numrows($res) > 0 ) {
		if(strlen($data_recebido)>0) $situacao = "";
		else                         $situacao = "";
		if(strlen($data_explodido)>0)$situacao = "";
		$resposta  .=  "<br><table border='1' cellpadding='2' cellspacing='0' style='border-collapse: collapse' bordercolor='#d2e4fc'  align='center' width='750'>";
		$resposta  .=  "<TR class='Titulo'  height='25'>";
		$resposta  .=  "<TD background='admin/imagens_admin/azul.gif'><b>Revenda</b></TD>";
		$resposta  .=  "<TD background='admin/imagens_admin/azul.gif'><b>C�digo Lote</b></TD>";
		$resposta  .=  "<TD background='admin/imagens_admin/azul.gif'><b>Lote OS</b></TD>";
		$resposta  .=  "<TD background='admin/imagens_admin/azul.gif'><b>Nota Fiscal</b></TD>";
		$resposta  .=  "<TD background='admin/imagens_admin/azul.gif'><b>Data NF</b></TD>";
		$resposta  .=  "<TD background='admin/imagens_admin/azul.gif'><b>Recebido</b></TD>";
		$resposta  .=  "<TD background='admin/imagens_admin/azul.gif'><b>Explodido</b></TD>";
		$resposta  .=  "<TD background='admin/imagens_admin/azul.gif'><b>Situa��o</b></TD>";
		$resposta  .=  "</TR>";

		for ($i=0; $i<pg_numrows($res); $i++){

			$lote_revenda   = trim(pg_result($res,$i,lote_revenda));
			$revenda_cnpj   = trim(pg_result($res,$i,revenda_cnpj));
			$revenda_nome   = trim(pg_result($res,$i,revenda_nome));
			$data_digitacao = trim(pg_result($res,$i,data_digitacao));
			$data_recebido  = trim(pg_result($res,$i,data_recebido));
			$data_explodido = trim(pg_result($res,$i,data_explodido));
			$nota_fiscal    = trim(pg_result($res,$i,nota_fiscal));
			$data_nf        = trim(pg_result($res,$i,data_nf));
			$lote           = trim(pg_result($res,$i,lote));
			//if($cor=="#6699CC") {$cor = '#6699CC';
			//} else {               $cor = '#6699CC';}

			if(strlen($data_recebido)>0) $situacao = "<img src='admin/imagens_admin/status_amarelo.gif' title='recebido, falta explodir'> ";
			else                         $situacao = "<img src='admin/imagens_admin/status_vermelho.gif' title='� receber'>";
			if(strlen($data_explodido)>0)$situacao = "<img src='admin/imagens_admin/status_verde.gif' title='explodido'>";

			if (strlen($lote_revenda) > 0 ){
				$sql2 = "SELECT count(*)
						FROM tbl_os
						JOIN tbl_os_revenda_item ON tbl_os.os = tbl_os_revenda_item.os_lote
						JOIN tbl_os_revenda      USING(os_revenda)
						WHERE lote_revenda   =   $lote_revenda ";
				$res2 = pg_exec ($con,$sql2) ;
				$qtde_itens = trim(pg_result($res2,0,0));

				$sql2 = "SELECT count(*)
						FROM tbl_os
						JOIN tbl_os_revenda_item ON tbl_os.os = tbl_os_revenda_item.os_lote
						JOIN tbl_os_revenda      USING(os_revenda)
						WHERE lote_revenda   =   $lote_revenda
						AND data_nf_saida IS NOT NULL";
				$res2 = pg_exec ($con,$sql2) ;
				$item_devolvido = trim(pg_result($res2,0,0));
				if($item_devolvido>0){
					$situacao = "<img src='admin/imagens_admin/status_azul_bb.gif' title='Parcialmente Devolvido'>";
					if( $conferencia=='lexplodido'){
						continue;
					}
				}

				$sql2 = "SELECT count(*)
					FROM tbl_os
					JOIN tbl_os_revenda_item ON tbl_os.os = tbl_os_revenda_item.os_lote
					JOIN tbl_os_revenda      USING(os_revenda)
					JOIN tbl_orcamento       ON tbl_orcamento.os = tbl_os.os
					WHERE lote_revenda   =   $lote_revenda";
				$res2 = @pg_exec ($con,$sql2) ;
				$oa = trim(@pg_result($res2,0,0));
				if($oa>0)$situacao = "<img src='admin/imagens_admin/status_rosa.gif' title='Or�amento Aprovado'>";

				$sql2 = "SELECT count(conferencia_qtde)
					FROM tbl_lote_revenda_item
					WHERE lote_revenda   =   $lote_revenda
						";
				$res2 = pg_exec ($con,$sql2) ;
				$item_devolvido2 = trim(pg_result($res2,0,0));

				if($qtde_itens-$item_devolvido == 0 AND strlen($data_recebido)>0 AND strlen($data_explodido)>0 and ($qtde_itens<>0 AND $item_devolvido<>0)){
					$situacao = "<img src='admin/imagens_admin/status_cinza.gif' title='Devolvido'>";
					if( $conferencia=='lexplodido'){
					continue;
					}
				}
			}

			$resposta  .=  "<TR class='Conteudo'>";
			$resposta  .=  "<TD align='left'nowrap title='$revenda_cnpj'><a href='javascript: revenda_formulario($lote_revenda)'> $revenda_nome</a></TD>";
			$resposta  .=  "<TD align='left'>$lote</a></TD>";
			$resposta  .=  "<TD align='left'>$lote_revenda</TD>";
			$resposta  .=  "<TD align='left'>$nota_fiscal</TD>";
			$resposta  .=  "<TD align='center'>$data_nf</TD>";
			$resposta  .=  "<TD align='center'>$qtde_itens</TD>";
			$resposta  .=  "<TD align='center'>$item_devolvido</TD>";
			$resposta  .=  "<TD align='center'>$situacao</TD>";
			$resposta  .=  "</TR>";

			$total = $total_os + $total;

		}
		$resposta .= " </TABLE>";
	}elseif(pg_numrows($res) ==0 and $conferencia =='nao_mostra' AND $login_fabrica == 3){

		$resposta  .=  "<table align='center' width='750' border='0' CELLSPANCING='0'>";
		$resposta  .=  "<TR>";
		$resposta  .=  "<Td align='center'>";
		$resposta  .=  "<b>Selecione uma legenda ou alguns link de Notas Fiscais</b>";
		$resposta  .=  "</Td>";
		$resposta  .=  "</Tr>";
		$resposta  .=  "</Table>";

	} else {
		$resposta  .=  "<table align='center' width='750' border='0' CELLSPANCING='0'>";
		$resposta  .=  "<TR>";
		$resposta  .=  "<Td align='center'>";
		$resposta  .=  "<b>Nenhum resultado encontrado</b>";
		$resposta  .=  "</Td>";
		$resposta  .=  "</Tr>";
		$resposta  .=  "<TR>";
		$resposta  .=  "<Td align='center'>";
		$resposta  .= "<font><a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=recebido\");'style='font-size:20px;'>Ver outro lote</a></font>";
		$resposta  .=  "</Td>";
		$resposta  .=  "</Tr>";
		$resposta  .=  "</Table>";
	}

	echo "ok|".$resposta;
	exit;

}

//--=== Detalhes da OS Lote===============================================
if($_GET['ajax']=='sim' AND $_GET['acao']=='detalhes_os') {
	$os_revenda = $_GET["os_revenda"];

	$sql = "SELECT  tbl_os_revenda.os_revenda       ,
			tbl_os_revenda.sua_os                   ,
			tbl_os_revenda_item.os_lote             ,
			tbl_os_revenda_item.produto             ,
			tbl_produto.referencia                  ,
			tbl_produto.descricao                   ,
			tbl_os.os                               ,
			tbl_os.sua_os               AS os_sua_os,
			tbl_os.finalizada                       ,
			tbl_os.excluida                         ,
			TO_CHAR(tbl_os.data_nf_saida,'DD/MM/YYYY') AS data_nf_saida ,
			tbl_os.nota_fiscal_saida
		FROM tbl_os_revenda
		JOIN tbl_os_revenda_item USING (os_revenda)
		JOIN tbl_produto         USING (produto)
		LEFT JOIN tbl_os ON tbl_os_revenda_item.os_lote = tbl_os.os
		WHERE tbl_os_revenda.fabrica    = $login_fabrica
		AND   tbl_os_revenda.os_revenda = $os_revenda
		ORDER BY os_revenda";
	$res = @pg_exec ($con,$sql);

	if (@pg_numrows($res) > 0) {
		$resposta .= "<table style=' border:#485989 1px solid; background-color: #e6eef7' align='center' width='680' border='0' >";

		$resposta .=  "<tr align='center' bgcolor='#BCCBE0' class='Conteudo'>\n";
		$resposta .=  "<td height='20'><b>OS</b></td>\n";
		$resposta .=  "<td height='20'><b>Produto</b></td>\n";
		$resposta .=  "<td height='20'><b>Situa��o</b></td>\n";
		$resposta .=  "<td height='20' colspan='2'><b>Nota Fiscal de Devolu��o</b></td>\n";
		$resposta .=  "</tr>\n";

		for ($i=0; $i<@pg_numrows($res); $i++){
			$os                = pg_result($res,$i,os);
			$os_revenda        = pg_result($res,$i,os_revenda);
			$sua_os            = pg_result($res,$i,sua_os);
			$os_lote           = pg_result($res,$i,os_lote);
			$os_sua_os         = pg_result($res,$i,os_sua_os);
			$referencia        = pg_result($res,$i,referencia);
			$descricao         = pg_result($res,$i,descricao);
			$finalizada        = pg_result($res,$i,finalizada);
			$data_nf_saida     = pg_result($res,$i,data_nf_saida);
			$excluida          = pg_result($res,$i,excluida);
			$nota_fiscal_saida = pg_result($res,$i,nota_fiscal_saida);

			$total = 0;
			if(strlen($os)>0){
				$sql2 = "SELECT count(*) FROM tbl_os_item JOIN tbl_os_produto USING(os_produto) WHERE os = $os";
				$res2 = pg_exec ($con,$sql2);
				$total = pg_result($res2,0,0);
			}
			$situacao = "";
			if(strlen($os)>0)        $situacao .= "<li> Gerou OS</li>";
			else                     $situacao .= "<li> <font color='#FF0000'>N�o gerou OS</font></li>";
			if(strlen($finalizada)>0)$situacao .= "<Li> OS finalizada</li>";
			else                     $situacao .= "<Li> OS n�o foi finalizada</li>";
			if($total==0)            $situacao .= "<li> Nenhuma pe�a lan�ada</li>";
			else                     $situacao .= "<li> Foram lan�adas $total pe�a(s)</li>";

			if($excluida=='t'){
				$sql2 = "SELECT fn_atualiza_excluida($os,$login_fabrica);";
				$res2 = pg_exec ($con,$sql2);
			}

			$resposta .= "<tr id='$i' bgcolor='#ffffff' style='font-color:#000000 ; align:left ; font-size:10px ' >\n";
			$resposta .= "<td align='left'width='80'>";
			$resposta .= "<a href='os_item_new.php?os=$os' target='_blank'>$os_sua_os</a>";
			$resposta .= "</a></td>\n";
			$resposta .= "<td align='left'>";
			$resposta .= "$referencia - $descricao";
			$resposta .= "</td>\n";
			$resposta .= "<td align='left'>";
			$resposta .= "$situacao";
			$resposta .= "</a></td>\n";
			if(strlen($os_sua_os)>0){
				$resposta .= "<td align='left'>";
				$resposta .= "N�mero<br><input name='nf_$i' type='text' class='Caixa' size='10' value='$nota_fiscal_saida' maxlength='6'>";
				$resposta .= "<br>Data<br><input name='data_nf_$i' id='data_nf_$i' type='text' class='Caixa' value='$data_nf_saida' size='10' onkeyup=\"formata_data2(this,event)\"></td>";
				//onKeyUp=\"formata_data('data_nf_$i')\"
				$resposta .= "<td align='center'><input type='button' name='gravar_nota_$i' id='gravar_nota_$i' value='Gravar Nota' onClick=\"if (this.value=='Gravando...'){ alert('Aguarde');}else {this.value='Gravando...'; gravar_aprovar('$os','$i',data_nf_$i,nf_$i,'gravar_nota_$i');}\" ></td>\n";
			}else{
				$resposta .= "<td align='left' colspan='2'> � necess�rio gerar OS</td>";
			}

		}
		$resposta .= "</table>";
	}


	echo "ok|".$resposta;
	exit;
}

if($_GET['ajax']=='sim' AND $_GET['acao']=='gravar_nota') {

	$nf           = $_GET["nf"];
	$data_nf      = $_GET["data_nf"];
	$numero_os    = $_GET["numero_os"];
	$lote_revenda = $_GET["lote_revenda"];


	if(strlen($nf)==0)      $msg_erro  = "Preencha a nota fiscal";
	if(strlen($data_nf)==0) $msg_erro .= "\nPreencha a data da nota fiscal";
	$data_nf = converte_data($data_nf);

	for ($i=0;$i<$numero_os;$i++){
		$os = $_POST["os_$i"];
		if (strlen($os)==0) continue;

		$sql="";
		if(strlen($msg_erro)==0){

			$sql =" SELECT
						data_nf_saida     ,
						nota_fiscal_saida ,
						data_fechamento   ,
						finalizada
					FROM    tbl_os
					WHERE os      = $os
					AND   fabrica = $login_fabrica";

			$res = @pg_exec($con,$sql);

			if (@pg_numrows($res) == 1) {
				$data_nf_saida    = pg_result($res,0,data_nf_saida);
				$nota_fiscal_saida= pg_result($res,0,nota_fiscal_saida);
				$data_fechamento  = pg_result($res,0,data_fechamento);
				$finalizada       = pg_result($res,0,finalizada);


				if(strlen($data_nf_saida) ==0 AND  strlen($nota_fiscal_saida)== 0 AND strlen($data_fechamento) ==0 AND  strlen($finalizada)== 0 ) {

					$sql = "UPDATE tbl_os
							SET data_nf_saida     = '$data_nf',
								nota_fiscal_saida = '$nf',
								data_fechamento   = '$data_nf',
								finalizada        = current_timestamp
							WHERE os      = $os
							AND   fabrica = $login_fabrica";

					$res = @pg_exec ($con,$sql);
					$msg_erro .= pg_errormessage($con);
				}else{

					if(strlen($data_nf_saida) ==0 AND  strlen($nota_fiscal_saida)== 0 ) {
						$sql = "
							UPDATE tbl_os
								SET data_nf_saida     = '$data_nf',
								nota_fiscal_saida = '$nf'
							WHERE os      = $os
								AND   fabrica = $login_fabrica";

						$res = @pg_exec ($con,$sql);
						$msg_erro .= pg_errormessage($con);
					}else{
						$msg_erro .= "Igor, verificar este erro";

					}
				}
			}
		}
	}

	if(strlen($msg_erro)==0) echo "ok|Gravado com sucesso";
	else                     echo "1| $msg_erro";

	exit;
}
//--==== Formul�rio ==========================================================
if($_GET['ajax']=='sim' AND $_GET['acao']=='formulario') {
	$lote_revenda  = trim($_GET["lote_revenda"]) ;

	$sql =" SELECT  tbl_lote_revenda.lote_revenda                                           ,
					tbl_lote_revenda.lote                                                   ,
					TO_CHAR(tbl_lote_revenda.data_digitacao,'dd/mm/YYYY')  AS data_digitacao,
					TO_CHAR(tbl_lote_revenda.data_recebido ,'dd/mm/YYYY')  AS data_recebido ,
					TO_CHAR(tbl_lote_revenda.data_explodido,'dd/mm/YYYY')  AS data_explodido,
					tbl_lote_revenda.nota_fiscal                                            ,
					TO_CHAR(tbl_lote_revenda.data_nf,'dd/mm/YYYY')         AS data_nf       ,
					tbl_revenda.cnpj                                       AS revenda_cnpj  ,
					tbl_revenda.nome                                       AS revenda_nome  ,
					tbl_lote_revenda.conferencia
			FROM tbl_lote_revenda
			LEFT JOIN tbl_revenda       USING(revenda)
			WHERE tbl_lote_revenda.fabrica      = $login_fabrica
			AND   tbl_lote_revenda.posto        = $login_posto
			AND   tbl_lote_revenda.lote_revenda = $lote_revenda
			ORDER BY data_digitacao";

	$res = pg_exec ($con,$sql);

	$lote_revenda   = pg_result ($res,0,lote_revenda);
	$lote           = pg_result ($res,0,lote);
	$data           = pg_result ($res,0,data_digitacao);
	$data_recebido  = pg_result ($res,0,data_recebido);
	$data_explodido = pg_result ($res,0,data_explodido);
	$nota_fiscal    = pg_result ($res,0,nota_fiscal);
	$data_nf        = pg_result ($res,0,data_nf);
	$revenda_cnpj   = pg_result ($res,0,revenda_cnpj);
	$revenda_nome   = pg_result ($res,0,revenda_nome);
	$conferencia    = pg_result ($res,0,conferencia);

	$resposta .= "<FORM name='frm_os' METHOD='POST' ACTION='$PHP_SELF '>";
	$resposta .= "<table style=' border:#485989 1px solid; background-color: #e6eef7' align='center' width='700' border='0' CELLSPANCING='0'>\n";
	$resposta .=  "<thead>\n";
	$resposta .=  "<tr align='left'>\n";
	$resposta .=  "<td bgcolor='#BCCBE0' colspan='3' style='font-size:18px'>\n";
	$resposta .=  "<b>&nbsp;<b>Recebimento do Lote C�digo $lote </b><br>\n";
	$resposta .=  "</td>\n";
	$resposta .=  "<td align='center' style='background-color:#e6eef7 ' class='Conteudo' onmouseover=\"this.style.backgroundColor='#FFFFDD';this.style.cursor='hand';\" onmouseout=\"this.style.backgroundColor='#e6eef7';\"><a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=recebido\");'><font color='#CC9900'>Voltar</a></td>\n";
	$resposta .=  "</tr>\n";
	$resposta .=  "<tr align='left'>\n";
	$resposta .=  "<td colspan='4' style='font-size:18px'>\n";

	$resposta .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='100%' >\n";
	$resposta .= "<tr>\n";
	$resposta .= "<td>Revenda <br> <b> $revenda_cnpj - $revenda_nome </b> </td>\n";
	$resposta .= "<td>Data <br> <b> $data </b> </td>\n";
	$resposta .= "<td>Nota Fiscal <br> <b> $nota_fiscal </b> </td>\n";
	$resposta .= "<td>Data NF <br> <b> $data_nf </b> </td>\n";
	$resposta .= "</tr>\n";
	$resposta .= "<tr>\n";
	$resposta .= "<td>Recebido  <br> <b> ";
	if(strlen($data_recebido)==0) $resposta .= "N�o foi recebido";
	else                          $resposta .= $data_recebido;
	$resposta .= "</b> </td>\n";
	$resposta .= "<td>Explodido <br> <b>";
	if(strlen($data_explodido)==0) $resposta .= "N�o foi explodido";
	else                           $resposta .= $data_explodido;
	$resposta .= "</b> </td>\n";
	$resposta .= "<td colspan='2'>Lote OS<br> <b> $lote_revenda </b> </td>\n";
	$resposta .= "</tr>\n";
	$resposta .= "</table>\n";

	$resposta .=  "</td>\n";
	$resposta .=  "</tr>\n";

	$sql = "SELECT
				tbl_lote_revenda_item.lote_revenda_item                            ,
				tbl_lote_revenda_item.qtde                                         ,
				tbl_lote_revenda_item.conferencia_qtde                             ,
				tbl_produto.produto                                                ,
				tbl_produto.referencia                                             ,
				tbl_produto.descricao
			FROM      tbl_lote_revenda_item
			JOIN      tbl_produto            USING(produto)
			WHERE   tbl_lote_revenda_item.lote_revenda = $lote_revenda
			ORDER BY tbl_lote_revenda_item.lote_revenda_item;";

	$res2 = pg_exec ($con,$sql);
	$qtde = pg_numrows($res2);
	if ($qtde > 0) {

		$resposta  .= "<input type='hidden' name='qtde' id='qtde' value='$qtde'>";
		$resposta .=  "<tr align='center' bgcolor='#BCCBE0'class='Conteudo'>\n";
		$resposta .= "<input type='hidden' name='lote_revenda' value='$lote_revenda' >\n";
		$resposta .=  "<td width='80' height='20'><b>C�digo</b></td>\n";
		$resposta .=  "<td><b>Descri��o</b></td>\n";
		$resposta .=  "<td width='100'><b>Qtde Enviada <acronym title='Este campo informa a quantidade de produtos que foram enviados pela revenda'>[?]</acronym></b></td>\n";
		$resposta .=  "<td width='100'><b>Qtde Recebida <acronym title='Este campo � necess�rio informar a quantidade real recebida'>[?]</acronym></b></td>\n";
		$resposta .=  "</tr>\n";

		for ($i=0; $i<pg_numrows($res2); $i++){

			$lote_revenda_item  = trim(pg_result($res2,$i,lote_revenda_item));
			$qtde               = trim(pg_result($res2,$i,qtde));
			$produto            = trim(pg_result($res2,$i,produto));
			$referencia         = trim(pg_result($res2,$i,referencia));
			$descricao          = trim(pg_result($res2,$i,descricao));
			$conferencia_qtde   = trim(pg_result($res2,$i,conferencia_qtde));

			$resposta .= "<tr bgcolor='#ffffff' style='font-color:#000000 ; align:left ; font-size:10px ' >\n";
			$resposta .= "<td align='left'>";
			$resposta .= "$referencia";
			$resposta .= "<input type='hidden' name='lote_revenda_item_$i' value='$lote_revenda_item'>\n";
			$resposta .= "</td>\n";
			$resposta .= "<td align='left'>$descricao</td>\n";
			$resposta .= "<td align='center'>$qtde</td>\n";

			$resposta .= "<td align='center' bgcolor='#FAE7A5'>\n<input style='text-align:right' type='text' size='4' maxlength='4' name='conferencia_qtde_$i' value=";
			if(strlen($conferencia_qtde)==0) $resposta .= "'$qtde'";
			else                             $resposta .= "'$conferencia_qtde' READONLY";
			$resposta .= " >\n</td>\n";
			$resposta .= "</tr>";
		}
		$resposta .= "<tr bgcolor='#ffffff' style='font-color:#000000;align:left;font-size:10px ' colspan='4' >\n";
		$resposta .= "<td align='center' colspan='4'>";
		if($conferencia<>'t' OR strlen($conferencia)==0) {

			$resposta .= "<table style=' border:#485989 1px solid; background-color: #e6eef7' align='center' width='700' border='0' >";

			$resposta .= "<tr bgcolor='#BCCBE0' class='Conteudo'>";
			$resposta .= "<td align='left' colspan='4' height='20'><b>Produto recebidos que n�o constam no Lote</b></td>";
			$resposta .= "</tr>";

			$resposta .= "<input type='hidden' name='qtde_item' id='qtde_item' value='$qtde_item'>";
			$resposta .= "<input type='hidden' name='add_peca'           id='add_peca'>\n";

			$resposta .= "<tr><td colspan='4'>";

				$resposta .= "<table>\n";
				$resposta .= "<tr>\n";
				$resposta .= "<td align='center' class='Conteudo' width='200'>C�digo <input class='Caixa' type='text' name='add_referencia' id='add_referencia' size='8' value='' onblur=\" fnc_pesquisa_produto (window.document.frm_os.add_referencia, window.document.frm_os.add_peca_descricao, 'referencia',window.document.frm_os.voltagem.value); \">\n";
				$resposta .= "<img src='imagens/btn_lupa_novo.gif' border='0' align='absmiddle'
				onclick=\" fnc_pesquisa_produto (window.document.frm_os.add_referencia, window.document.frm_os.add_peca_descricao, 'referencia',window.document.frm_os.voltagem.value); \" alt='Clique para efetuar a pesquisa' style='cursor:pointer;'>\n";
				$resposta .= "</td>\n";
				$resposta .= "<td align='center' class='Conteudo' nowrap>Descri��o <input class='Caixa' type='text' name='add_peca_descricao' id='add_peca_descricao' size='40' value='' >\n";
				$resposta .= "<img src='imagens/btn_lupa_novo.gif' border='0' align='absmiddle' onclick=\" fnc_pesquisa_produto (window.document.frm_os.add_referencia, window.document.frm_os.add_peca_descricao, 'descricao',window.document.frm_os.voltagem.value); \" alt='Clique para efetuar a pesquisa' style='cursor:pointer;'>\n";
				$resposta .= "</td>\n";
				$resposta .= "<td align='center' class='Conteudo' nowrap>Qtde <input class='Caixa' type='text' name='add_qtde' id='add_qtde' size='2' maxlength='4' value='' >\n &nbsp;<input class='Caixa' type='hidden' name='voltagem' id='voltagem' size='2' maxlength='4' value='' >";
				$resposta .= "</td>\n";
				$resposta .= "<td class='Conteudo' nowrap><input name='gravar_peca' id='gravar_peca' type='button' value='Adicionar' onClick='javascript:adiconarPecaTbl()'></td>\n";
				$resposta .= "</tr>\n";
				$resposta .= "</table>\n";

			$resposta .= "</td>\n";
			$resposta .= "</tr>\n";
			$resposta .= "</table><br>\n";

			$resposta .= "<table style=' border:#485989 1px solid; background-color: #e6eef7' align='center' width='700' border='0' id='tbl_pecas'>\n";
			$resposta .= "<thead>\n";
			$resposta .= "<tr height='20' bgcolor='#BCCBE0'>\n";
			$resposta .= "<td align='center' class='Conteudo'><b>C�digo</b></td>\n";
			$resposta .= "<td align='center' class='Conteudo'><b>Descri��o</b></td>\n";
			$resposta .= "<td align='center' class='Conteudo'><b>Qtde</b></td>\n";
			$resposta .= "<td align='center' class='Conteudo'><b>A��es</b></td>\n";
			$resposta .= "</tr>\n";
			$resposta .= "</thead>\n";
			//$resposta .= "<tbody></tbody>\n";
			$resposta .= "<tfoot>\n";
			$resposta .= "<tr height='12' bgcolor='#BCCBE0'>\n";
			$resposta .= "<td align='center' class='Conteudo' colspan='2'><b>Total</b></td>\n";
			$resposta .= "<td align='center' class='Conteudo'><span style='font-weight:bold' id='valor_total_itens'>0</span></td>\n";
			$resposta .= "<td align='center' class='Conteudo' ></td>\n";
			$resposta .= "</tr>\n";
			$resposta .= "</tfoot>";
			$resposta .= "</table>\n\n";
			$resposta .= "<!--ponto-->\n";
		}
		$resposta .= "</td>";
		$resposta .= "</tr>";

		$resposta .= "<tr bgcolor='#ffffff' style='font-color:#000000;align:left;font-size:10px ' colspan='4' >\n";
		$resposta .= "<td align='center' colspan='4'>";
		if($conferencia<>'t' OR strlen($conferencia)==0) {
			$resposta .= "<input type='button' name='btn_acao'  value='Gravar Conferencia' onClick=\"if (this.value!='Gravar Conferencia'){ alert('Aguarde');}else {this.value='Gravando...'; gravar(this.form,'sim','$PHP_SELF','nao');}\" style=\"width: 150px;\">";
		}else{
			$resposta .= "Recebido: $data_recebido";
		}
		$resposta .= "</td>";
		$resposta .= "</tr>";
		$resposta  .= "</table>";

		$resposta .= "</table>";


		if($conferencia<>'t' OR strlen($conferencia)==0) {
			$resposta  .= "<br><center><a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=recebido\");'>VER OUTROS LOTES</a></center><br>";
			$resposta .= "<P></P><div id='saida' style='display:inline;'></div><div id='erro' style='display:inline;'></div>";
		}else{
			$resposta .= "<br><FONT COLOR='#FF0000'>LOTE J� CONFERIDO, N�O � POSS�VEL LAN�AR PRODUTOS</font><br>";
			$resposta .= "<P></P><div id='saida' style='display:inline;'></div><div id='erro' style='display:inline;'></div>";

			if(strlen($data_explodido)==0){
				$resposta .= "<input type='button' name='btn_explodir'  value='Explodir' onClick=\"if (this.value!='Explodir'){ alert('Aguarde');}else {this.value='Executando...'; window.location = '$PHP_SELF?explodir=$lote_revenda';}\" style=\"width: 150px;\" class='Explodir'>";
			}else{
				$resposta .= "<FONT COLOR='#FF0000'>LOTE J� EXPLODIDO</font><br>";
				$resposta  .= "<br><center><a href='javascript:mostrar_treinamento(\"dados\",\"&conferencia=recebido\");'>VER OUTROS LOTES</a></center><br>";
				$sql = "SELECT DISTINCT
						tbl_os_revenda.os_revenda              ,
						tbl_os_revenda.sua_os                  ,
						tbl_os_revenda_item.os_lote            ,
						tbl_os.os
					FROM tbl_os_revenda
					JOIN tbl_os_revenda_item USING (os_revenda)
					LEFT JOIN tbl_os ON tbl_os_revenda_item.os_lote = tbl_os.os
					WHERE tbl_os_revenda.fabrica      = $login_fabrica
					AND   tbl_os_revenda.lote_revenda = $lote_revenda
					AND   tbl_os.os IS NULL
					ORDER BY os_revenda";
				$res = pg_exec ($con,$sql);

				if (pg_numrows($res) > 0) {
					$resposta .= "<table style=' border:#485989 1px solid; background-color: #e6eef7' align='center' width='700' border='0' >";

					$resposta .=  "<tr align='center' class='Conteudo'>\n";
					$resposta .=  "<td height='20' colspan='3'><b>N�o geraram OS</b></td>\n";
					$resposta .=  "</tr>\n";

					$resposta .=  "<tr align='center' bgcolor='#BCCBE0' class='Conteudo'>\n";
					$resposta .=  "<td height='20'><b>OS Revenda</b></td>\n";
					$resposta .=  "<td height='20'><b>Situa��o</b></td>\n";
					$resposta .=  "<td><b>A��es</b></td>\n";
					$resposta .=  "</tr>\n";

					for ($i=0; $i<pg_numrows($res); $i++){
						$os           = pg_result($res,$i,os);
						$os_revenda   = pg_result($res,$i,os_revenda);
						$sua_os       = pg_result($res,$i,sua_os);
						$os_lote      = pg_result($res,$i,os_lote);

						$sql2 = "SELECT count(*) FROM tbl_os_revenda_item WHERE os_revenda = $os_revenda";
						$res2 = pg_exec ($con,$sql2);
						$total = pg_result($res2,0,0);
						/*if($total>5  AND $total< 10)$total=10;
						if($total>10 AND $total< 20)$total=20;
						if($total>20 AND $total< 30)$total=30;
						if($total>30 AND $total< 40)$total=40;
*/
						if(strlen($os)>0)        $situacao = "Gerou OS";
						else                     $situacao = "N�o gerou OS";
						if(strlen($finalizada)>0)$situacao = "OS finalizada";

						$resposta .= "<tr bgcolor='#ffffff' style='font-color:#000000 ; align:left ; font-size:10px ' >\n";
						$resposta .= "<td align='left' style='cursor: hand;cursor:pointer' onclick=\"javascript:MostraEsconde('dados_$i','$os_revenda','visualizar_$i','$cor');\"><img src='imagens/mais.gif' id='visualizar_$i' style='cursor: pointer' align='absmiddle'>";
						$resposta .= "$sua_os</a>";
						$resposta .= "</td>\n";
						$resposta .= "<td align='left'>";
						$resposta .= "$situacao";
						$resposta .= "</a></td>\n";

						$resposta .= "<td align='center'><a href='os_revenda.php?os_revenda=$os_revenda&qtde_linhas=$total&lote=sim' target='_blank'>Verficar Os Revenda</a></td>\n";

						$resposta .=  "<tr heigth='1' class='Conteudo' bgcolor='$cor'><td colspan='4'>";
						$resposta .=  "<DIV class='exibe' id='dados_$i' value='1' align='center'></DIV>";
						$resposta .=  "</td></tr>";
						$resposta .=  "</tr>\n";
					}
					$resposta .= "</table>";
				}


				$sql = "SELECT  tbl_os_revenda.os_revenda              ,
						tbl_os_revenda.sua_os                  ,
						tbl_os_revenda_item.os_lote            ,
						tbl_os_revenda_item.produto            ,
						tbl_produto.referencia                 ,
						tbl_produto.descricao                  ,
						tbl_os.os                              ,
						tbl_os.sua_os               AS os_sua_os,
						tbl_os.finalizada                       ,
						tbl_os.solucao_os                       ,
						tbl_os.excluida                         ,
						TO_CHAR(tbl_os.data_nf_saida,'DD/MM/YYYY') AS data_nf_saida ,
						tbl_os.nota_fiscal_saida
					FROM tbl_os_revenda
					JOIN tbl_os_revenda_item USING (os_revenda)
					JOIN tbl_produto         USING (produto)
					JOIN tbl_os ON tbl_os_revenda_item.os_lote = tbl_os.os
					WHERE tbl_os_revenda.fabrica    = $login_fabrica
					AND   tbl_os_revenda.lote_revenda = $lote_revenda
					ORDER BY os_revenda";
				$res = @pg_exec ($con,$sql);

				if (@pg_numrows($res) > 0) {
					$resposta .= "<br>";
					$resposta .= "<table style=' border:#485989 1px solid; background-color: #e6eef7' align='center' width='700' border='0' >";

					$resposta .=  "<tr align='center' class='Conteudo'>\n";
					$resposta .=  "<td height='20' colspan='6'><b>Devolu��o</b></td>\n";
					$resposta .=  "</tr>\n";

					$resposta .=  "<tr align='center' bgcolor='#BCCBE0' class='Conteudo'>\n";
					$resposta .=  "<td height='20' rowspan='2'></td>\n";
					$resposta .=  "<td height='20' rowspan='2'><b>OS</b></td>\n";
					$resposta .=  "<td height='20' rowspan='2'><b>Produto</b></td>\n";
					$resposta .=  "<td height='20' rowspan='2'><b>Situa��o</b></td>\n";
					$resposta .=  "<td height='20' colspan='2'><b>Nota Fiscal de Devolu��o</b></td>\n";
					$resposta .=  "</tr>\n";

					$resposta .=  "<tr align='center' bgcolor='#BCCBE0' class='Conteudo'>\n";
					$resposta .=  "<td>N�mero</td>\n";
					$resposta .=  "<td>Data</td>\n";
					$resposta .=  "</tr>\n";

					for ($i=0; $i<@pg_numrows($res); $i++){
						$os                = pg_result($res,$i,os);
						$os_revenda        = pg_result($res,$i,os_revenda);
						$sua_os            = pg_result($res,$i,sua_os);
						$solucao_os        = pg_result($res,$i,solucao_os);
						$os_lote           = pg_result($res,$i,os_lote);
						$os_sua_os         = pg_result($res,$i,os_sua_os);
						$referencia        = pg_result($res,$i,referencia);
						$descricao         = pg_result($res,$i,descricao);
						$finalizada        = pg_result($res,$i,finalizada);
						$data_nf_saida     = pg_result($res,$i,data_nf_saida);
						$excluida          = pg_result($res,$i,excluida);
						$nota_fiscal_saida = pg_result($res,$i,nota_fiscal_saida);

						$total = 0;
						if(strlen($os)>0){
							$sql2 = "SELECT count(*)
									FROM tbl_os_item
									JOIN tbl_os_produto USING(os_produto)
									WHERE os = $os";
							$res2 = pg_exec ($con,$sql2);
							$total = pg_result($res2,0,0);
						}
						$situacao = "";
						//if(strlen($os)>0)        $situacao .= "<li> Gerou OS</li>";
						//else                     $situacao .= "<li> <font color='#FF0000'>N�o gerou OS</font></li>";
						if(strlen($finalizada)>0)$situacao .= "<Li> OS finalizada</li>";
						else                     $situacao .= "<Li> OS n�o foi finalizada</li>";
						if (strlen($solucao_os)==0)$situacao.="<li> OS sem solu��o</li>";
						if($total==0)            $situacao .= "<li> Nenhuma pe�a lan�ada</li>";
						else                     $situacao .= "<li> Foram lan�adas $total pe�a(s)</li>";

						$botao_desabilitado = "";
						$cor_de_fundo       = "#ffffff";
						$cor_da_letra       = "#000000";
						$hint_os            = "";
						# se j� digitou, nao permite mudar a nota fiscal
						if (strlen($nota_fiscal_saida)>0 AND strlen($finalizada)>0){

							$botao_desabilitado = " DISABLED ";
							$cor_de_fundo       = "#EFEFEF";
							$cor_da_letra       = "#6A6A6A";
							if (strlen($solucao_os)==0){
								$hint_os = "title='OS sem solu��o. Abra a OS e preencha!'";
							}
						}


						if (strlen($nota_fiscal_saida)>0){
							$link = "os_press.php";
						}else{
							$link = "os_item_new.php";
						}
						if($excluida=='t'){
							$sql2 = "SELECT fn_atualiza_excluida($os,$login_fabrica);";
							$res2 = @pg_exec ($con,$sql2);
						}

						$resposta .= "<tr id='$i' bgcolor='$cor_de_fundo' style='color:$cor_da_letra ; align:left ; font-size:10px ' $hint_os>\n";
						$resposta .= "<td><input type='checkbox' name='os_$i' value='$os'  $botao_desabilitado></td>\n";
						$resposta .= "<td align='left'width='80'>";
						$resposta .= "<a href='$link?os=$os' target='_blank'>$os_sua_os</a>";
						$resposta .= "</a></td>\n";
						$resposta .= "<td align='left'>";
						$resposta .= "$referencia - $descricao";
						$resposta .= "</td>\n";
						$resposta .= "<td align='left'>";
						$resposta .= "$situacao";
						$resposta .= "</a></td>\n";
						$resposta .= "<td align='center'>$nota_fiscal_saida</td>";
						$resposta .= "<td align='center'>$data_nf_saida</td>\n";
					}

					$resposta .=  "<tr align='center' bgcolor='#BCCBE0' class='Conteudo'>\n";
					$resposta .=  "<td height='20'><input type='checkbox' name='selecionar_tudo' onclick='javascript:selecionarToggle(this)' title='Selecionar Todas'><input type='hidden' name='numero_de_os' id='numero_de_os' value='$i'></td>\n";
					$resposta .=  "<td height='20' colspan='3'>";
					$resposta .=  "<span>Com as OS selecionadas, devolver com o</span> ";
					$resposta .=  "<b>N�mero da Nota</b>
					<input name='numero_nf' id='numero_nf' type='text' class='Caixa' size='10' value='' maxlength='6'>\n";
					$resposta .= "&nbsp;&nbsp;&nbsp;&nbsp;";
					$resposta .=  "<b>Data</b> <input name='data_nf' id='data_nf' type='text' class='Caixa' size='11' value='' maxlength='10'></td>\n";
					$resposta .=  "<td height='20' colspan='2'>
						<input type='button' name='gravar_nota' id='gravar_nota' value='Gravar Nota'
						onClick=\"
						if (this.value=='Gravando...'){
							alert('Aguarde');
						}else {
							this.value='Gravando...';
							gravar_aprovar('$lote_revenda');
						}\" ></td>\n";
					$resposta .=  "</tr>\n";

					$resposta .= "</table>";
				}
			}
		}

	}
	$resposta .= "</form>";
	$resposta .= "<div id='erro' name='erro'></div>";
	echo "ok|?".$resposta;
	exit;

}

$layout_menu = "os";
$title = "Conferencia de Lote de Revenda";

include "cabecalho.php";

?>
<style>
.Titulo {
	text-align: center;
	font-family: Verdana;
	font-size: 12px;
	font-weight: bold;
	color: #FFFFFF;
	background-color: #485989;
}
.Titulo2 {
	text-align: center;
	font-family: Verdana;
	font-size: 12px;
	font-weight: bold;
	color: #FFFFFF;
	background-color: #485989;
}
.Conteudo {
	font-family: Arial;
	font-size: 8pt;
	font-weight: normal;
}
.Conteudo2 {
	font-family: Arial;
	font-size: 10pt;
}

.Caixa{
	BORDER-RIGHT: #6699CC 1px solid;
	BORDER-TOP: #6699CC 1px solid;
	FONT: 8pt Arial ;
	BORDER-LEFT: #6699CC 1px solid;
	BORDER-BOTTOM: #6699CC 1px solid;
	BACKGROUND-COLOR: #FFFFFF;
}
.Caixa2{
	BORDER-RIGHT: #6699CC 1px solid;
	BORDER-TOP: #6699CC 1px solid;
	FONT: 7pt Arial ;
	BORDER-LEFT: #6699CC 1px solid;
	BORDER-BOTTOM: #6699CC 1px solid;
	BACKGROUND-COLOR: #FFFFFF;
}
.Explodir{
	FONT: 12pt Arial ;
	font-weight: bold;
	color: #000099;
	width: 180px; height: 60px;
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
acronym {
  color:#F8F0D6;
  cursor: help;
  /*border-bottom: 1px dashed #F8F0D6;*/
}
table{
	min-width: 760px;
}
</style>

<script language="javascript" src="js/cal2.js"></script>
<script language="javascript" src="js/cal_conf2.js"></script>
<script language="javascript" src="js/jquery-1.1.2.pack.js"></script>
<script language='javascript' src='ajax.js'></script>
<script language='javascript'>
function formata_data2(x,tecla){
	separador="/"; // Voc� pode definir o separador. Ex: "/" ou "-" ou "."
	tecla=tecla.keyCode; // Identifica a tecla, caso seja backspace
	valor=x.value.split(''); // Pega o valor do campo e transforma cada caractere em uma string
	formatado=""; // Vari�vel para carregar formata��o temporariamente
	i=0; // vari�vel de controle
	while(i<valor.length){ // Loop para cada caractere do campo
		caractere=valor[i]; // Seleciona um caractere para ser formatado
		numeros=/^\d+$/; // Variavel contendo n�meros positivos
			// Verifica se � n�mero ou "barra"
		if(numeros.test(caractere) || caractere==separador){ formatado+=String(caractere);}
			// Verifica se precisa de barra, se a tecla for backspace, ent�o a barra n�o � adicionada
			// Se for para adicionar barra, aumenta ++ vari�vel de controle "i" para pular a barra adicionada
		if((formatado.length==2 || formatado.length==5) && tecla!=8){formatado+=separador; i++;}

		i++; // Se houver, passa para o pr�ximo caractere
	}
	x.value=formatado; // Atribui o valor formatado ao campo
}

function createRequestObject(){
	var request_;
	var browser = navigator.appName;
	if(browser == "Microsoft Internet Explorer"){
		 request_ = new ActiveXObject("Microsoft.XMLHTTP");
	}else{
		 request_ = new XMLHttpRequest();
	}
	return request_;
}

var http_forn = new Array();

function gravar(formulatio,redireciona,pagina,janela) {
	var acao = 'gravar';
	url = "<?=$PHP_SELF?>?ajax=sim&acao="+acao;
	parametros = "";
	for( var i = 0 ; i < formulatio.length; i++ ){
		if (formulatio.elements[i].type !='button'){
			if(formulatio.elements[i].type=='radio' || formulatio.elements[i].type=='checkbox'){
				if(formulatio.elements[i].checked == true){
					parametros = parametros+"&"+formulatio.elements[i].name+"="+escape(formulatio.elements[i].value);
				}
			}else{
				parametros = parametros+"&"+formulatio.elements[i].name+"="+escape(formulatio.elements[i].value);
			}
		}
	}

	var com       = document.getElementById('erro');
	var saida     = document.getElementById('saida');

	com.innerHTML = "&nbsp;&nbsp;Aguarde...&nbsp;&nbsp;<br><img src='../imagens/carregar2.gif' >";
	saida.innerHTML = "&nbsp;&nbsp;Aguarde...&nbsp;&nbsp;<br><img src='../imagens/carregar2.gif' >";

	var curDateTime = new Date();
	http_forn[curDateTime] = createRequestObject();
	http_forn[curDateTime].open('POST',url,true);

	http_forn[curDateTime].setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http_forn[curDateTime].setRequestHeader("CharSet", "ISO-8859-1");
	http_forn[curDateTime].setRequestHeader("Content-length", url.length);
	http_forn[curDateTime].setRequestHeader("Connection", "close");

	http_forn[curDateTime].onreadystatechange = function(){
		if (http_forn[curDateTime].readyState == 4){
			if (http_forn[curDateTime].status == 200 || http_forn[curDateTime].status == 304){

			var response = http_forn[curDateTime].responseText.split("|");
				if (response[0]=="debug"){
					alert(http_forn[curDateTime].responseText);
				}
				if (response[0]=="ok"){
					com.style.visibility = "hidden";
					com.innerHTML = response[1];
					saida.innerHTML = response[1];
					formulatio.btn_acao.value='Gravar Conferencia';
					formulatio.btn_acao.style.visibility='hidden';
					$("input[@rel='btn_excluir']").css("display","none");
					$("#gravar_peca").css("display","none");
				}else{
					formulatio.btn_acao.value='Gravar Conferencia';
				}
				if (response[0]=="1"){
					com.style.visibility = "visible";
					saida.innerHTML = "<font color='#990000'>Ocorreu um erro, verifique!</font>";
					alert('Erro: verifique as informa��es preenchidas!');
					com.innerHTML = response[1];
					formulatio.btn_acao.value='Gravar Conferencia';
				}
			}
		}
	}
	http_forn[curDateTime].send(parametros);
}



function explodir(formulatio,id) {
	var acao = 'explodir';
	url = "<?=$PHP_SELF?>?ajax=sim&acao="+acao+"&lote_revenda="+escape(id);

	var com       = document.getElementById('erro');
	var saida     = document.getElementById('saida');

	com.innerHTML = "&nbsp;&nbsp;Aguarde...&nbsp;&nbsp;<br><img src='imagens/carregar2.gif' >";
	saida.innerHTML = "&nbsp;&nbsp;Aguarde...&nbsp;&nbsp;<br><img src='imagens/carregar2.gif' >";

	var curDateTime = new Date();
	http_forn[curDateTime] = createRequestObject();
	http_forn[curDateTime].open('GET',url,true);

	http_forn[curDateTime].onreadystatechange = function(){
		if (http_forn[curDateTime].readyState == 4)
		{
			if (http_forn[curDateTime].status == 200 || http_forn[curDateTime].status == 304)
			{
				var response = http_forn[curDateTime].responseText.split("|");
				if (response[0]=="ok"){
					com.style.visibility = "hidden";
					com.innerHTML = response[2];
					saida.innerHTML = response[2];
					revenda_formulario(id);
					//formulatio.btn_explodir.value='Gravar';
					//formulatio.style.visibility='hidden';

				}
				if (response[0]=="0"){
					// posto ja cadastrado
					alert(response[1]);
				}
				if (response[0]=="1"){
					// dados incompletos
					com.style.visibility = "visible";
					saida.innerHTML = "<font color='#990000'>Ocorreu um erro, verifique!</font>" + response[1];

					alert('Erro: verifique as informa��es preenchidas!');
					com.innerHTML = response[1];
					//formulatio.btn_explodir.value='Gravar';

				}
			}
		}
	}
	http_forn[curDateTime].send(null);
}

function mostrar_treinamento(componente,complemento) {
	var com = document.getElementById(componente);
	var acao='ver';

	com.innerHTML   ="Carregando<br><img src='imagens/carregar2.gif'>";
	url = "<?=$PHP_SELF?>?ajax=sim&acao="+acao+complemento;

	var curDateTime = new Date();
	http_forn[curDateTime] = createRequestObject();
	http_forn[curDateTime].open('GET',url,true);

	http_forn[curDateTime].onreadystatechange = function(){
		if (http_forn[curDateTime].readyState == 4)
		{
			if (http_forn[curDateTime].status == 200 || http_forn[curDateTime].status == 304)
			{
				var response = http_forn[curDateTime].responseText.split("|");
				if (response[0]=="ok"){
					com.innerHTML   = response[1];
				}
				if (response[0]=="0"){
					// posto ja cadastrado
					alert(response[1]);
				}
				if (response[0]=="1"){
					// dados incompletos
					alert("Campos incompletos:\n\n"+response[1]);
				}
			}
		}
	}
	http_forn[curDateTime].send(null);
}



function revenda_formulario(treinamento) {

	var acao='formulario';

	url = "<?=$PHP_SELF?>?ajax=sim&acao="+acao+"&lote_revenda="+treinamento;

	var com = document.getElementById('dados');
	com.innerHTML   ="Carregando<br><img src='imagens/carregar2.gif'>";

	var curDateTime = new Date();
	http_forn[curDateTime] = createRequestObject();
	http_forn[curDateTime].open('GET',url,true);

	http_forn[curDateTime].onreadystatechange = function(){
		if (http_forn[curDateTime].readyState == 4)
		{
			if (http_forn[curDateTime].status == 200 || http_forn[curDateTime].status == 304)
			{
				var response = http_forn[curDateTime].responseText.split("|?");
				if (response[0]=="ok"){

					com.innerHTML   = response[1];

				}
			}
		}
	}
	http_forn[curDateTime].send(null);
}

function gravar_aprovar(lote_revenda) {

	var botao = document.getElementById('gravar_nota');
	var data  = document.getElementById('data_nf');
	var numero= document.getElementById('numero_nf');
	var n_os  = document.getElementById('numero_de_os');


	if (data.value==''){
		alert('Digite a data da nota fiscal!');
		botao.value='Gravar Nota';
		return false;
	}
	if (numero.value==''){
		alert('Digite o n�mero da nota fiscal!');
		botao.value='Gravar Nota';
		return false;
	}

	var acao='gravar_nota';

	formulatio = document.frm_os;
	parametros = "";
	for( var i = 0 ; i < formulatio.length; i++ ){
		if (formulatio.elements[i].type =='checkbox'){
			if(formulatio.elements[i].checked == true && formulatio.elements[i].disabled == false){
				parametros = parametros+"&"+formulatio.elements[i].name+"="+escape(formulatio.elements[i].value);
			}
		}
	}

	url = "<?=$PHP_SELF?>?ajax=sim&acao="+acao+"&data_nf="+data.value+"&nf="+numero.value+"&numero_os="+n_os.value+"&lote_revenda="+lote_revenda;

	var curDateTime = new Date();
	http_forn[curDateTime] = createRequestObject();
	http_forn[curDateTime].open('POST',url,true);

	http_forn[curDateTime].setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http_forn[curDateTime].setRequestHeader("CharSet", "ISO-8859-1");
	http_forn[curDateTime].setRequestHeader("Content-length", url.length);
	http_forn[curDateTime].setRequestHeader("Connection", "close");

	http_forn[curDateTime].onreadystatechange = function(){
		if (http_forn[curDateTime].readyState == 4)
		{
			if (http_forn[curDateTime].status == 200 || http_forn[curDateTime].status == 304)
			{
				var response = http_forn[curDateTime].responseText.split("|");
				if (response[0]=="ok"){
					alert(response[1]);
 					//l.style.background = '#D7FFE1';
					botao.value='Sucesso';
					botao.disabled='true';
					revenda_formulario(lote_revenda);
				}
				if (response[0]=="1"){
					botao.value='Gravar Nota';
					alert(response[1]);
				}
			}
		}
	}
	http_forn[curDateTime].send(parametros);
}

function retornaDados (http , componente ) {
	com = document.getElementById(componente);

	com.innerHTML   ="Carregando<br><img src='imagens/carregar2.gif'>";
	if (http.readyState == 4) {
		if (http.status == 200) {
			results = http.responseText.split("|");
			if (typeof (results[0]) != 'undefined') {
				if (results[0] == 'ok') {
					com.innerHTML   = results[1];

					//mostrar_interacao(results[1],'interacao_'+results[1]);
				}else{
					alert ('Erro ao abrir lote da revenda' );
					alert(results[0]);
				}
			}
		}
	}
}

function pegaDados (id,dados,cor) {
	url = "<?=$PHP_SELF?>?ajax=sim&acao=detalhes_os&os_revenda=" + escape(id)+"&cor="+escape(cor) ;

	http.open("GET", url , true);
	http.onreadystatechange = function () { retornaDados (http , dados) ; } ;
	http.send(null);
}

function MostraEsconde(dados,id,imagem,cor)
{
	if (document.getElementById)
	{
		// this is the way the standards work
		var style2 = document.getElementById(dados);
		var img    = document.getElementById(imagem);
		if (style2.style.display){
			style2.style.display = "";
			style2.innerHTML   ="";
			img.src='imagens/mais.gif';

			}
		else{
			style2.style.display = "block";
			img.src='imagens/menos.gif';
			pegaDados(id,dados,cor);
		}

	}
}

function adiconarPecaTbl() {

	if (document.getElementById('add_qtde').value==''){
		alert('Informe a quantidade');
		return false;
	}

	var tbl = document.getElementById('tbl_pecas');
	var lastRow = tbl.rows.length;
	var iteration = lastRow;

	// inicio da tabela
	var linha = document.createElement('tr');
	linha.style.cssText = 'color: #000000; text-align: left; font-size:10px';

	// coluna 1 - codigo do item
	var celula = criaCelula(document.getElementById('add_referencia').value);
	celula.style.cssText = 'text-align: center; color: #000000;font-size:10px';

	var el = document.createElement('input');
	el.setAttribute('type', 'hidden');
	el.setAttribute('name', 'lote_revenda_' + iteration);
	el.setAttribute('id', 'lote_revenda_' + iteration);
	el.setAttribute('value','');
	celula.appendChild(el);

	var el = document.createElement('input');
	el.setAttribute('type', 'hidden');
	el.setAttribute('name', 'produto_' + iteration);
	el.setAttribute('id', 'produto_' + iteration);
	el.setAttribute('value',document.getElementById('add_peca').value);
	celula.appendChild(el);

	var el = document.createElement('input');
	el.setAttribute('type', 'hidden');
	el.setAttribute('name', 'referencia_produto_' + iteration);
	el.setAttribute('id', 'referencia_produto_' + iteration);
	el.setAttribute('value',document.getElementById('add_referencia').value);
	celula.appendChild(el);

	var el = document.createElement('input');
	el.setAttribute('type', 'hidden');
	el.setAttribute('name', 'descricao_produto_' + iteration);
	el.setAttribute('id', 'descricao_produto_' + iteration);
	el.setAttribute('value',document.getElementById('add_peca_descricao').value);
	celula.appendChild(el);

	var el = document.createElement('input');
	el.setAttribute('type', 'hidden');
	el.setAttribute('name', 'produto_qtde_' + iteration);
	el.setAttribute('id', 'produto_qtde_' + iteration);
	el.setAttribute('value',document.getElementById('add_qtde').value);
	celula.appendChild(el);

	linha.appendChild(celula);


	// coluna 2 DESCRI��O
	var celula = criaCelula(document.getElementById('add_peca_descricao').value);
	celula.style.cssText = 'text-align: center; color: #000000;font-size:10px';
	linha.appendChild(celula);

	// coluna 3 QTDE
	var qtde = document.getElementById('add_qtde').value;
	var celula = criaCelula(qtde);
	celula.style.cssText = 'text-align: center; color: #000000;font-size:10px';
	linha.appendChild(celula);


	var total_valor_peca = parseInt(qtde);


	// coluna 9 - a��es
	var celula = document.createElement('td');
	celula.style.cssText = 'text-align: center; color: #000000;font-size:10px';
	var el = document.createElement('input');
	el.setAttribute('type', 'button');
	el.setAttribute('value','Excluir');
	el.setAttribute('rel','btn_excluir');
	el.onclick=function(){removerPeca(this,total_valor_peca);};
	celula.appendChild(el);

	// fim da linha
	linha.appendChild(celula);
	var tbody = document.createElement('TBODY');
	tbody.appendChild(linha);
	//linha.style.cssText = 'color: #404e2a;';
	tbl.appendChild(tbody);

	// incrementa a qtde
	document.getElementById('qtde_item').value++;

	//limpa form de add mao de obra
	document.getElementById('add_referencia').value='';
	document.getElementById('add_peca_descricao').value='';
	document.getElementById('add_qtde').value='';


	// atualiza os totalizador
	var aux_valor = document.getElementById('valor_total_itens').innerHTML;
	aux_valor = parseFloat(aux_valor) + parseFloat(total_valor_peca);
	document.getElementById('valor_total_itens').innerHTML = parseInt(aux_valor);

	document.getElementById('add_referencia').focus();
}

function removerPeca(iidd,valor){
//	var tbl = document.getElementById('tbl_pecas');
//	var lastRow = tbl.rows.length;
//	if (lastRow > 2){
//		tbl.deleteRow(iidd.title);
//		document.getElementById('qtde_item').value--;
//	}
	var tbl = document.getElementById('tbl_pecas');
	var oRow = iidd.parentElement.parentElement;
	tbl.deleteRow(oRow.rowIndex);
	document.getElementById('qtde_item').value--;

	var aux_valor = document.getElementById('valor_total_itens').innerHTML;
	aux_valor = parseFloat(aux_valor) - parseFloat(valor);
	document.getElementById('valor_total_itens').innerHTML = parseInt(aux_valor);


}

function criaCelula(texto) {
	var celula = document.createElement('td');
	var textoNode = document.createTextNode(texto);
	celula.appendChild(textoNode);
	return celula;
}

function formata_data(campo_data){
	var mycnpj = '';
	mycnpj = mycnpj + campo_data;
	myrecord = campo;
	mycnpj = document.getElementById(campo);

	if (mycnpj.length == 2){
		mycnpj = mycnpj + '/';
		mycnpj.value = mycnpj;
	}
	if (mycnpj.length == 5){
		mycnpj = mycnpj + '/';
		mycnpj.value = mycnpj;
	}
}


function checarNumero(campo){
	var num = campo.value.replace(",",".");
	campo.value = parseFloat(num).toFixed(2);
	if (campo.value=='NaN') {
		campo.value='';
	}
}

function fnc_pesquisa_produto (campo, campo2, tipo, voltagem) {
	if (tipo == "referencia" ) {
		var xcampo = campo;
	}

	if (tipo == "descricao" ) {
		var xcampo = campo2;
	}

	if (xcampo.value != "") {
		var url = "";
		url = "produto_pesquisa_2.php?campo=" + xcampo.value + "&tipo=" + tipo + "&proximo=t";
		janela = window.open(url, "janela", "toolbar=no, location=no, status=yes, scrollbars=yes, directories=no, width=600, height=400, top=18, left=0");
		janela.referencia   = campo;
		janela.descricao    = campo2;
		janela.voltagem = voltagem;
		if (voltagem != "") {
			janela.voltagem = voltagem;
		}
		janela.proximo      = document.frm_os.add_qtde;
		janela.focus();
	}
}

function selecionarToggle(chk){
	f = document.frm_os;

	for (i=0; i<f.length; i++){
		if (f.elements[i].type == "checkbox"  && f.elements[i].disabled == false){
			if (chk.checked) {
				f.elements[i].checked = true;
			}else{
				f.elements[i].checked = false;
			}

		}
	}
}

</script>

<?
echo $msg;
echo $msg_erro;
echo "<div id='dados'></div>";
if ($login_fabrica == 3) {
echo "<script language='javascript'>mostrar_treinamento('dados','&conferencia=nao_mostra');</script>";
} else {
echo "<script language='javascript'>mostrar_treinamento('dados','&conferencia=recebido');</script>";
}
?>
<br clear=both>
<?php include "rodape.php"; ?>
