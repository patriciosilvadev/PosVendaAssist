<?include 'dbconfig.php';include 'includes/dbconnect-inc.php';include "autentica_usuario.php";if(strlen($explodir)>0) {	$sql = "BEGIN;";	$res = @pg_exec($con,$sql);	$sql = "SELECT produto_rg_item			FROM   tbl_produto_rg      RG			JOIN   tbl_produto_rg_item RI USING(produto_rg)			WHERE  RG.produto_rg = $explodir			AND    produto IS NULL";	$res      = @pg_exec($con,$sql);	$msg_erro = pg_errormessage($con);	if(@pg_numrows($res)==0){		$sql     =	"UPDATE tbl_produto SET					data_conferencia  = CURRENT_TIMESTAMP					WHERE produto_rg = $explodir; ";		$res      = @pg_exec($con,$sql);		$msg_erro .= pg_errormessage($con);		$sql =	"UPDATE tbl_produto_rg_item SET					fabrica           = 45               ,					data_conferencia  = CURRENT_TIMESTAMP				WHERE produto_rg = $explodir 				AND   data_conferencia IS NULL; ";		$res = @pg_exec($con,$sql);		$msg_erro .= pg_errormessage($con);		$sql = "SELECT fn_gera_lote($explodir,$cook_posto);";		//echo $sql;		$res = @pg_exec($con,$sql);		$msg_erro .= pg_errormessage($con);		if(strlen($msg_erro)==0){			$sql = "COMMIT;";			$res = @pg_exec($con,$sql);			header("Location: rg_conferencia.php");			exit;		}else{			$sql = "ROLLBACK;";			$res = @pg_exec($con,$sql);			$msg_erro .= "N�o � poss�vel gerar a OS! Favor verificar todos os produtos! ";			echo "<FONT COLOR='RED' >$msg_erro</FONT>";		}	}else{		$msg_erro .= "N�o � poss�vel gerar a OS enquanto todos os produtos n�o forem informados";		echo "<FONT COLOR='RED' >$msg_erro</FONT>";	}}$aba=2;include "cabecalho.php";?><script type="text/javascript" src="js/bibliotecaAJAX.js"></script><script type="text/javascript" src="rg_conferencia.js"></script><script type="text/javascript">function trataDados(){	var info = ajax.responseText;	if (info) {		lote_selecionado.innerHTML = info;	}}</script><script language="JavaScript">function autocompletar(campo1,campo2) {	/* Busca pelo Nome */	$('#'+campo1).autocomplete("pesquisa.php?tipo=produto&busca=tudo", {		minChars: 3,		delay: 150,		width: 350,		matchContains: false,		highlightItem: true,		formatItem: function (row)   {return row[4]},		formatResult: function(row)  {return row[2];}	});	$('#'+campo1).result(function(event, data, formatted) {		$('#'+campo2).val(data[0])    ;		$('#'+campo1).focus() ;	});}</script><center><div id="exibir_lotes" class="modbox" style="display:none" onmouseover="this.style.cursor = 'hand'" onclick="relacao_lotes.style.display='block' ; lote_selecionado.style.display='none' ; this.style.display='none' ; lote_selecionado.innerText='' ; ">	&nbsp;<br>&nbsp;	<font color='#005599' class="modtitle">	Clique aqui para exibir rela��o de lotes	</font>	&nbsp;<br>&nbsp;</div></center><div id="relacao_lotes">		<table class='TabelaRevenda' align='center'  border='0' cellspacing='3' cellpadding='3'>		<thead>		<tr>			<td width='100'><b>Lote</b></td>			<td width='150'><b>Data Entrada</b></td>			<td width='100'><b>Qtde. Lote</b></td>			<td width='100'><b>Qtde. a Vincular</b></td>		</tr>		</thead>		<tbody>		<?		$sql = "SELECT	tbl_produto_rg.produto_rg, 						TO_CHAR (tbl_produto_rg.data_digitacao,'dd/mm/yyyy') AS data_digitacao, 						(SELECT COUNT(*) FROM tbl_produto_rg_item WHERE tbl_produto_rg_item.produto_rg = tbl_produto_rg.produto_rg) AS qtde , 						(SELECT COUNT(*) FROM tbl_produto_rg_item WHERE tbl_produto_rg_item.produto_rg = tbl_produto_rg.produto_rg AND tbl_produto_rg_item.produto IS NULL) AS qtde_vincular 						FROM tbl_produto_rg 						WHERE tbl_produto_rg.posto = $login_posto 						AND   tbl_produto_rg.data_conferencia       IS NULL 						AND   tbl_produto_rg.data_digitacao_termino IS NOT NULL 						ORDER BY tbl_produto_rg.produto_rg";		$res = pg_exec ($con,$sql);		for ($i = 0 ; $i < pg_numrows ($res) ; $i++) {			$produto_rg     = pg_result ($res,$i,'produto_rg');			$data_digitacao = pg_result ($res,$i,'data_digitacao');			$qtde           = pg_result ($res,$i,'qtde');			$qtde_vincular  = pg_result ($res,$i,'qtde_vincular');			if($cor<>'#FFFFFF') $cor = '#FFFFFF';			else                $cor = '#e6eef7';			echo "<tr bgcolor='$cor' onclick=\"exibir_lotes.style.display='block' ; relacao_lotes.style.display='none' ; lote_selecionado.style.display='block' ; requisicaoHTTP('GET','lote_selecionado.php?produto_rg=$produto_rg',true) ; \" onmouseover=\"this.bgColor='#cccccc' ; this.style.cursor = 'hand' ; \" onmouseout=\"this.bgColor='$cor'\">";			echo "<td>" . $produto_rg . "</td>";			echo "<td>" . $data_digitacao . "</td>";			echo "<td>" . $qtde . "</td>";			echo "<td>" . $qtde_vincular . "</td>";			echo "</tr>";		}	?>	</tbody>	</table></div><div id="lote_selecionado" style="display:none" ></div>	<?include "rodape.php";?>