<?include 'dbconfig.php';include 'includes/dbconnect-inc.php';include "autentica_usuario.php";if ($acao == "gravar" AND $ajax == "sim") {	if (strlen($msg_erro) == 0) {		$res = pg_exec ($con,"BEGIN TRANSACTION");		if(strlen($msg_erro)==0){			$qtde_item = 100;			for ($i = 0 ; $i < $qtde_item ; $i++) {								$produto_rg_item    = trim($_POST['produto_rg_item_' . $i]);				$produto_rg         = trim($_POST['produto_rg_' . $i]);				$rg                 = trim($_POST['rg_' . $i]);				$codigo_barra       = trim($_POST['codigo_barra_' . $i]);				$produto            = trim($_POST['produto_' . $i]);				$serie              = trim($_POST['serie_' . $i]);				$defeito_reclamado  = trim($_POST['defeito_reclamado_' . $i]);				$defeito_constatado = trim($_POST['defeito_constatado_' . $i]);				$observacao         = trim($_POST['observacao_' . $i]);				if(strlen($produto_rg_item)==0) continue;				if(strlen($msg_erro)>0)         break;				$x = $i+1;				if(strlen($produto)==0)           $msg_erro .= "Informe o produto na linha $x<br>";				if(strlen($defeito_constatado)==0) $defeito_constatado .= "null";				if(strlen($msg_erro)==0){					$sql =	"UPDATE tbl_produto_rg_item SET								serie              = '$serie'             ,								defeito_reclamado  = '$defeito_reclamado' ,								defeito_constatado = $defeito_constatado ,								produto            = $produto             ,								observacao         = '$observacao'							WHERE produto_rg_item  = $produto_rg_item ; ";					$res = @pg_exec($con,$sql);					$msg_erro .= pg_errormessage($con);					if(strlen($msg_erro)==0){							$sql = "SELECT fn_atualiza_lote('$rg',$produto_rg,$login_posto);";							$res = @pg_exec($con,$sql);							$msg_erro .= pg_errormessage($con);												}					if(strlen($msg_erro)>0) $msg_erro = "$sql";				}else $msg_erro_linha = $i;			}		}	}	if (strlen($msg_erro) == 0) {		$res = pg_exec($con,"COMMIT TRANSACTION");		echo "ok|Gravado com Sucesso|$produto_rg";	}else{		$res = pg_exec($con,"ROLLBACK TRANSACTION");		echo "1|$msg_erro|$msg_erro_linha";	}	exit;}$aba=2;include "cabecalho.php";?><script type="text/javascript" src="js/bibliotecaAJAX.js"></script><script language='javascript'>function fn_alertar (texto) {	if (texto.length > 0){		if (texto.indexOf('<erro>') >= 0){			texto = texto.substring (texto.indexOf('>')+1,texto.length);			texto = texto.substring (0,texto.indexOf('</'));			alert (texto);		}	}}</script><?if(isset($ok)) $msg = "Informa��es da Planilha foram gravadas com sucesso";if(strlen($tecnico)>0){?><div id="m_1" class="modbox">	<h2 class="modtitle">		<div id="m_1_h">			<span id="m_1_title" class="modtitle_text"><font color="#005599">Retorno de Planilha de Produto</font></span>		</div>	</h2>	<div id="m_1_b" class="modboxin">		<div id="ftl_1_0" class="uftl" style='text-align:justify'>		<form name="frm_os" method="post" action="<? echo $PHP_SELF ?>">		<input type='hidden' name='produto_rg' id='produto_rg' value='<?=$produto_rg?>'>		<table class='TabelaRevenda' cellspacing='3' cellpadding='3' width='98%'>		<thead>		<tr bgcolor='#596D9B' style='color:#FFFFFF;'>		<td ><b>RG</b></td>		<td ><b>PRODUTO</b></td>		<td ><b>N. S�RIE</b></td>		<td ><b>DEFEITO RECLAMADO</b></td>		<td ><b>PE�AS</b></td>		</tr>		</thead>		<tbody>			<?			$sql = "SELECT 							RI.produto_rg                                                ,							RI.produto_rg_item                                           ,							RI.rg                                                        ,							RI.codigo_barra                                              ,							RI.serie                                                     ,							RI.defeito_reclamado                                         ,							RI.observacao                                                ,							PR.produto                                                   ,							PR.referencia                           AS produto_referencia,							PR.descricao                            AS produto_descricao ,							PR.linha                                                     ,							PR.familia                                                   ,							DC.defeito_constatado                   AS defeito_constatado,							DC.descricao                            AS defeito_descricao ,							TO_CHAR(RI.data_digitacao,'dd/mm/YYYY') AS data			FROM       tbl_produto_rg                  RG			JOIN       tbl_produto_rg_item             RI USING(produto_rg)			JOIN       tbl_produto                     PR USING(produto)			LEFT JOIN  tbl_defeito_constatado     DC ON DC.defeito_constatado = RI.defeito_constatado			WHERE RI.tecnico = $tecnico			ORDER BY   produto_descricao";			$res = pg_exec($con,$sql);			for($i=0;$i<pg_numrows($res);$i++) {				$produto_rg         = pg_result($res,$i,produto_rg);				$produto_rg_item    = pg_result($res,$i,produto_rg_item);				$rg                 = pg_result($res,$i,rg);				$codigo_barra       = pg_result($res,$i,codigo_barra);				$produto            = pg_result($res,$i,produto);				$referencia         = pg_result($res,$i,produto_referencia);				$descricao          = pg_result($res,$i,produto_descricao);				$serie              = pg_result($res,$i,serie);				$defeito_reclamado  = pg_result($res,$i,defeito_reclamado);				$defeito_constatado = pg_result($res,$i,defeito_constatado);				$defeito_descricao  = pg_result($res,$i,defeito_descricao);				$observacao         = pg_result($res,$i,observacao);				$data               = pg_result($res,$i,data);				$linha              = pg_result($res,$i,linha);				$familia            = pg_result($res,$i,familia);				$x = $i+1;				if(strlen($produto)==0 and strlen($codigo_barra)>0){					$sql2 = "SELECT produto,referencia,descricao							FROM   tbl_produto							JOIN   tbl_linha   USING(linha)							WHERE  codigo_barra = '$codigo_barra'							AND    fabrica      = 45							";					$res2            = @pg_exec($con,$sql2);					$produto         = @pg_result($res2,0,produto);					$referencia      = @pg_result($res2,0,referencia);					$descricao       = @pg_result($res2,0,descricao);				}else{					$read = " READONLY";				}				if($cor=='#FFFFFF') {					$cor = "#ddddff";				}else{					$cor = "#FFFFFF";				}				echo "<tr bgcolor='$cor' valign='top' onmouseover=\"this.style.cursor='pointer' ; this.bgColor='#cccccc'\" onmouseout=\"this.bgColor='$cor'\" onclick=\"div_lanca_pecaCarrega($produto_rg_item) ; div_lanca_peca.style.position='absolute' ; div_lanca_peca.style.left='300px' ; div_lanca_peca.style.top=(this.offsetTop+200) + 'px' ; div_lanca_peca.style.display='block' ; div_lanca_peca_rg.innerText='$rg' ; frm_lanca_peca.produto_rg_item.value='$produto_rg_item' ; frm_lanca_peca.linha.value=$i ; frm_lanca_peca.serie.focus() \">\n";				echo "<td height='40'><input type='hidden' name='produto_rg_item_$i' id='produto_rg_item_$i' value='$produto_rg_item'><input type='hidden' name='rg_$i' id='rg_$i' value='$rg'><input type='hidden' name='produto_rg_$i' id='produto_rg_$i' value='$produto_rg'>&nbsp;&nbsp;$rg</td>\n";				echo "<td title='$referencia - $descricao' >\n";				echo "<input type='hidden' name='produto_$i' id='produto_$i' value='$produto'>\n";				echo "<input type='hidden' name='produto_linha_$i' id='produto_linha_$i' value='$linha'>\n";				echo "<input type='hidden' name='produto_familia_$i' id='produto_familia_$i' value='$familia'>\n";				echo "$descricao</td>\n";				echo "<td><span id='serie_$i'>$serie</span></td>\n";				echo "<td><span id='defeito_reclamado_$i'>$defeito_reclamado</span></td>\n";				$sql = "SELECT tbl_peca.descricao FROM tbl_peca JOIN tbl_produto_rg_peca USING (peca) WHERE tbl_produto_rg_peca.produto_rg_item = $produto_rg_item ORDER BY tbl_produto_rg_peca.produto_rg_peca";				$resP = pg_exec ($con,$sql);				$pecas = "";				for ($p = 0 ; $p < pg_numrows ($resP) ; $p++) {					$pecas .= pg_result ($resP,$p,descricao) . "<br>";				}				echo "<td><span id='pecas_$i'>$pecas</span></td>\n";#				echo "<td><!-- <select name='defeito_constatado_$i' id='defeito_constatado_$i' class='Caixa' style='width: 220px;' onfocus='listaConstatado(document.frm_os.produto_linha_$i.value, document.frm_os.produto_familia_$i.value,document.frm_os.defeito_reclamado_$i.value,this);' >\n";#				if(strlen($defeito_constatado)>0) echo "<option  id='opcoes2' value='$defeito_constatado'>$defeito_descricao</option>\n";#				else                              echo "<option  id='opcoes2' value=''></option>\n";#				echo "</select>\n";#				echo " --> </td>\n";				echo "<td>";				echo "<!--";				echo "<input type='hidden' name='peca_$i' id='peca_$i' rel='peca'>";				echo "<input type='text'   name='peca_descricao_$i'  id='peca_descricao_$i' rel='descricao' size='40' class='Caixa'><br>";				echo "<input type='radio'  name='tipo_$i' id='tipo_$i' value='estoque'> Pe�a do estoque&nbsp;&nbsp;&nbsp;";				echo "<input type='radio'  name='tipo_$i' id='tipo_$i' value='aguardando'> Aguardando Pe�a";				echo "<div id='rg_linha_$i' onclick=\"div_lanca_peca.style.position='absolute' ; div_lanca_peca.style.left='300px' ; div_lanca_peca.style.top=(this.offsetParent.offsetTop+200) + 'px' ; div_lanca_peca.style.display='block' ; div_lanca_peca_rg.innerText='$rg' \">lan�ar</div>";				echo "-->";				echo "</td>\n";				echo "</tr>\n";			}			echo "</tbody>\n";			echo "<tfoot>\n";			echo "<tr>\n";			echo "<td colspan='9'>\n";			echo "<table style=' border:#B63434 1px solid; background-color: #EED5D2' align='center' width='100%' border='0' height='40'>\n";			echo "<tr>\n";			echo "<td valign='middle' align='LEFT' class='Label' >\n";			echo "</td>\n";			echo "</tr>\n";			echo "<tr><td width='50' valign='middle'  align='LEFT' colspan='4'><input type='button' name='btn_acao'  value='Gravar' onClick=\"if (this.value!='Gravar'){ alert('Aguarde');}else {this.value='Gravando...'; gravar(this.form,'sim','$PHP_SELF','nao');}\" style=\"width: 150px;\"></td>\n";						echo "<td width='300'><div id='saida' style='display:inline;'></div></td>\n";			echo "</tr>\n";			echo "</table>\n";			?>			</td>		</tr>		</tfoot>		</table>			<div id='erro' style='visibility:hidden;opacity:0.85' class='Erro'></div>			<br><center><a href='planilha_print.php?cc=<?=$cc?>' target='_blank'>[imprimir]</a></center>		</form>		</div>	</div></div><?}else{?><div id="m_1" class="modbox">	<h2 class="modtitle">		<div id="m_1_h">			<span id="m_1_title" class="modtitle_text"><font color="#005f9d">Digita��o da Planilha analisada pelo t�cnico</font></span>		</div>	</h2>	<div id="m_1_b" class="modboxin">		<div id="ftl_1_0" class="uftl" style='text-align:justify'>		<?			if(strlen($tecnico)>0) $sql_add .= " AND RI.tecnico    = $tecnico ";			$sql = "SELECT  (select count(RI.produto_rg_item) from tbl_produto_rg_item RI where RI.tecnico = LU.login_unico AND   RI.data_devolucao IS NULL)              AS total              ,							LU.login_unico          AS tecnico            ,							LU.nome                 AS tecnico_nome					FROM    tbl_login_unico     LU					WHERE LU.posto = $login_posto					AND   LU.tecnico IS TRUE					AND   LU.ativo IS TRUE					ORDER BY LU.nome";			$res = pg_exec($con,$sql);			$total = pg_numrows($res);			echo "<input type='hidden' name='qtde_item' id='qtde_item' value='$total'>";			if(pg_numrows($res)>0){				echo "<table align='center'  border='0' cellpadding='5' cellspacing='0'  width='300' class='TabelaRevenda' >\n";				echo "<thead>\n";				echo "<tr height='30'>\n";				echo "<td ><b>T�cnico</b></td>\n";				echo "<td ><b>Qtde produtos</b></td>\n";				echo "</tr>\n";				echo "</thead>\n";				echo "<tbody>\n";				for($i=0;$i<pg_numrows($res);$i++) {					$total           = pg_result($res,$i,total);					$tecnico         = pg_result($res,$i,tecnico);					$tecnico_nome    = pg_result($res,$i,tecnico_nome);					$x = $i+1;					if($cor<>'#FFFFFF') $cor = '#FFFFFF';					else                $cor = '#e6eef7';					echo "<tr bgcolor='$cor' height='30' onmouseover=\"this.style.cursor = 'pointer' ; this.bgColor='#cccccc'\" onmouseout=\"this.bgColor='$cor'\" onclick=\"window.location.href='planilha_retorno.php?tecnico=$tecnico' \">\n";					echo "<td>$tecnico_nome</td>\n";					echo "<td>$total</td>\n";					echo "</tr>\n";				}				echo "</tr>\n";				echo "</tbody>\n";				echo "</table>\n";			}else echo "<center>Nenhum produto est� em an�lise</center>";		?>		</div>	</div></div><?}?><!-- --------------------- DIV para Lan�amento de Pe�as ------------------- --><script language='javascript'>function div_lanca_pecaLimpa() {	document.getElementById('produto_rg_item').value = "";	document.getElementById('linha').value = "";	document.getElementById('serie').value = "";	document.getElementById('defeito_reclamado').value = "";	document.getElementById('peca_1').value = "";	document.getElementById('peca_2').value = "";	document.getElementById('peca_3').value = "";	document.getElementById('peca_4').value = "";	document.getElementById('peca_5').value = "";	document.getElementById('peca_6').value = "";	document.getElementById('peca_7').value = "";	document.getElementById('peca_8').value = "";	document.getElementById('peca_9').value = "";	document.getElementById('peca_10').value = "";}function div_lanca_pecaFecha() {	div_lanca_pecaLimpa();	div_lanca_peca.style.display='none';}function div_lanca_pecaCarrega (produto_rg_item) {	requisicaoHTTP ('GET','div_lanca_peca_carrega_ajax.php?produto_rg_item=' + produto_rg_item , true , 'div_lanca_pecaCarregaCampos');}function div_lanca_pecaCarregaCampos (campos) {	campos = campos.substring (campos.indexOf('>')+1,campos.length);	campos = campos.substring (0,campos.indexOf('</'));	campos_array = campos.split("|");	document.getElementById('serie').value = campos_array[0] ;	document.getElementById('serie').defaultValue = campos_array[0] ;	document.getElementById('defeito_reclamado').value = campos_array[1] ;	document.getElementById('defeito_reclamado').defaultValue = campos_array[1] ;	document.getElementById('peca_1').value = campos_array[2] ;	document.getElementById('peca_1').defaultValue = campos_array[2] ;	document.getElementById('peca_2').value = campos_array[3] ;	document.getElementById('peca_2').defaultValue = campos_array[3] ;	document.getElementById('peca_3').value = campos_array[4] ;	document.getElementById('peca_3').defaultValue = campos_array[4] ;	document.getElementById('peca_4').value = campos_array[5] ;	document.getElementById('peca_4').defaultValue = campos_array[5] ;	document.getElementById('peca_5').value = campos_array[6] ;	document.getElementById('peca_5').defaultValue = campos_array[6] ;	document.getElementById('peca_6').value = campos_array[7] ;	document.getElementById('peca_6').defaultValue = campos_array[7] ;	document.getElementById('peca_7').value = campos_array[8] ;	document.getElementById('peca_7').defaultValue = campos_array[8] ;	document.getElementById('peca_8').value = campos_array[9] ;	document.getElementById('peca_8').defaultValue = campos_array[9] ;	document.getElementById('peca_9').value = campos_array[10] ;	document.getElementById('peca_9').defaultValue = campos_array[10] ;	document.getElementById('peca_10').value = campos_array[11] ;	document.getElementById('peca_10').defaultValue = campos_array[11] ;}</script><script language="JavaScript">function autocompletar(campo1,campo2, produto_rg_item) {	$('#'+campo1).autocomplete("pesquisa.php?tipo=lista-basica&produto_rg_item=" + produto_rg_item, {		minChars: 3,		delay: 150,		width: 350,		scroll: true,		scrollHeight: 500,		matchContains: false,		highlightItem: true,		formatItem: function (row)   {return row[4]},		formatResult: function(row)  {return row[2];}	});	$('#'+campo1).result(function(event, data, formatted) {		$('#'+campo2).val(data[0])    ;		$('#'+campo1).focus() ;	});}</script><script language='javascript'>function lanca_peca_retorna_form (texto) {	var div_ok = false ;	if (texto.length > 0){		if (texto.indexOf('<erro>') >= 0){			texto = texto.substring (texto.indexOf('>')+1,texto.length);			texto = texto.substring (0,texto.indexOf('</'));			alert (texto);		}else{			div_ok = true;		}	}else{		div_ok = true;	}	if (div_ok) {		var linha = frm_lanca_peca.linha.value ;		span_serie = 'serie_' + linha;		document.getElementById(span_serie).innerText = document.frm_lanca_peca.serie.value;		span_defeito_reclamado = 'defeito_reclamado_' + linha;		document.getElementById(span_defeito_reclamado).innerText = document.frm_lanca_peca.defeito_reclamado.value;		var pecas = "";		for (var i = 1 ; i < 20 ; i++) {			linha_peca = 'frm_lanca_peca.peca_' + i;			if (typeof (eval (linha_peca)) != "undefined") {				var descricao = eval (linha_peca).value ;				descricao = descricao.split(" - ");				descricao = descricao[1];				if (typeof (descricao) != "undefined"){					pecas = pecas + descricao + "<br>";				}			}		}		span_pecas = 'pecas_' + linha;		document.getElementById(span_pecas).innerHTML = pecas;		div_lanca_pecaFecha();	}}</script><div id="div_lanca_peca" style="display:none ; border:solid 1px #330099 ; width:400px ; height:350px ; background-color:#EEEEFF ; padding: 5px " onkeypress="if(event.keyCode==27){div_lanca_pecaFecha()}">	<div id="div_lanca_peca_fecha" style="float:right ; align:center ; width:20px ; background-color:#FFFFFF " onclick="div_lanca_pecaFecha()" onmouseover="this.style.cursor='pointer'"><center><b>X</b></center></div>	<div id="div_lanca_peca_titulo" style="width:270px ">		<font size='+1'>		An�lise do RG <span id="div_lanca_peca_rg">&nbsp;</span>		</font>	</div>	<form name='frm_lanca_peca'>	<input type='hidden' name='produto_rg_item' id='produto_rg_item'>	<input type='hidden' name='linha' id='linha'>	<hr><br>	N. S�rie <input type="text" id='serie' name="serie" size="10" value="" onblur="requisicaoHTTP('GET','serie_cadastra_ajax.php?serie=' + this.value + '&produto_rg_item=' + frm_lanca_peca.produto_rg_item.value ,true,'fn_alertar')" >	<br> 	Defeito Reclamado <input type="text" name="defeito_reclamado" size="20" value="" onblur="requisicaoHTTP('GET','defeito_reclamado_grava_ajax.php?defeito_reclamado=' + this.value + '&produto_rg_item=' + frm_lanca_peca.produto_rg_item.value ,true,'fn_alertar')" >	<br> 	<!--	Defeito Constatado <input disabled type="text" name="defeito_constatado" size="20">	<br>	-->	Pe�a 1 <input type="text"   id="peca_1"    name="peca_1" size="35" onfocus="autocompletar('peca_1','id_peca_1',frm_lanca_peca.produto_rg_item.value)" >	       <input type="hidden" id="id_peca_1" name="id_peca_1">	<br> 	Pe�a 2 <input type="text"   id="peca_2"    name="peca_2" size="35" onfocus="autocompletar('peca_2','id_peca_2',frm_lanca_peca.produto_rg_item.value)" >	       <input type="hidden" id="id_peca_2" name="id_peca_2">	<br> 	Pe�a 3 <input type="text"   id="peca_3"    name="peca_3" size="35" onfocus="autocompletar('peca_3','id_peca_3',frm_lanca_peca.produto_rg_item.value)" >	       <input type="hidden" id="id_peca_3" name="id_peca_3">	<br> 	Pe�a 4 <input type="text"   id="peca_4"    name="peca_4" size="35" onfocus="autocompletar('peca_4','id_peca_4',frm_lanca_peca.produto_rg_item.value)" >	       <input type="hidden" id="id_peca_4" name="id_peca_4">	<br> 	Pe�a 5 <input type="text"   id="peca_5"    name="peca_5" size="35" onfocus="autocompletar('peca_5','id_peca_5',frm_lanca_peca.produto_rg_item.value)" >           <input type="hidden" id="id_peca_5" name="id_peca_5">	<br> 	Pe�a 6 <input type="text"   id="peca_6"    name="peca_6" size="35" onfocus="autocompletar('peca_6','id_peca_6',frm_lanca_peca.produto_rg_item.value)" >           <input type="hidden" id="id_peca_6" name="id_peca_6">	<br> 	Pe�a 7 <input type="text"   id="peca_7"    name="peca_7" size="35" onfocus="autocompletar('peca_7','id_peca_7',frm_lanca_peca.produto_rg_item.value)" >	       <input type="hidden" id="id_peca_7" name="id_peca_7">	<br> 	Pe�a 8 <input type="text"   id="peca_8"    name="peca_8" size="35" onfocus="autocompletar('peca_8','id_peca_8',frm_lanca_peca.produto_rg_item.value)" >	       <input type="hidden" id="id_peca_8" name="id_peca_8">	<br> 	Pe�a 9 <input type="text"   id="peca_9"    name="peca_9" size="35" onfocus="autocompletar('peca_9','id_peca_9',frm_lanca_peca.produto_rg_item.value)" >	       <input type="hidden" id="id_peca_9" name="id_peca_9">	<br> 	Pe�a 10 <input type="text"   id="peca_10"    name="peca_10" size="35" onfocus="autocompletar('peca_10','id_peca_10',frm_lanca_peca.produto_rg_item.value)" >	        <input type="hidden" id="id_peca_10" name="id_peca_10">	<br> 	<br>	<input type="button" id="btn_gravar" name="btn_gravar" value="Gravar" onclick="requisicaoHTTP('POST','div_lanca_peca_grava_ajax.php',true,'lanca_peca_retorna_form',false,document.frm_lanca_peca)">	</form></div><?include "rodape.php";?>