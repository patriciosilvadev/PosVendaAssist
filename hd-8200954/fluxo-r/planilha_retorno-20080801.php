<?include 'dbconfig.php';include 'includes/dbconnect-inc.php';include "autentica_usuario.php";if ($acao == "gravar" AND $ajax == "sim") {	if (strlen($msg_erro) == 0) {		$res = pg_exec ($con,"BEGIN TRANSACTION");		if(strlen($msg_erro)==0){			$qtde_item = 100;			for ($i = 0 ; $i < $qtde_item ; $i++) {								$produto_rg_item    = trim($_POST['produto_rg_item_' . $i]);				$produto_rg         = trim($_POST['produto_rg_' . $i]);				$rg                 = trim($_POST['rg_' . $i]);				$codigo_barra       = trim($_POST['codigo_barra_' . $i]);				$produto            = trim($_POST['produto_' . $i]);				$serie              = trim($_POST['serie_' . $i]);				$defeito_reclamado  = trim($_POST['defeito_reclamado_' . $i]);				$defeito_constatado = trim($_POST['defeito_constatado_' . $i]);				$observacao         = trim($_POST['observacao_' . $i]);				if(strlen($produto_rg_item)==0) continue;				if(strlen($msg_erro)>0)         break;				$x = $i+1;				if(strlen($produto)==0)           $msg_erro .= "Informe o produto na linha $x<br>";				if(strlen($defeito_constatado)==0) $defeito_constatado .= "null";				if(strlen($msg_erro)==0){					$sql =	"UPDATE tbl_produto_rg_item SET								serie              = '$serie'             ,								defeito_reclamado  = '$defeito_reclamado' ,								defeito_constatado = $defeito_constatado ,								produto            = $produto             ,								observacao         = '$observacao'							WHERE produto_rg_item  = $produto_rg_item ; ";					$res = @pg_exec($con,$sql);					$msg_erro .= pg_errormessage($con);					if(strlen($msg_erro)==0){							$sql = "SELECT fn_atualiza_lote('$rg',$produto_rg,$login_posto);";							$res = @pg_exec($con,$sql);							$msg_erro .= pg_errormessage($con);												}					if(strlen($msg_erro)>0) $msg_erro = "$sql";				}else $msg_erro_linha = $i;			}		}	}	if (strlen($msg_erro) == 0) {		$res = pg_exec($con,"COMMIT TRANSACTION");		echo "ok|Gravado com Sucesso|$produto_rg";	}else{		$res = pg_exec($con,"ROLLBACK TRANSACTION");		echo "1|$msg_erro|$msg_erro_linha";	}	exit;}$aba=3;include "cabecalho.php";?><script>function createRequestObject(){	var request_;	var browser = navigator.appName;	if(browser == "Microsoft Internet Explorer"){		 request_ = new ActiveXObject("Microsoft.XMLHTTP");	}else{		 request_ = new XMLHttpRequest();	}	return request_;}var http_forn = new Array();function gravar(formulatio,redireciona,pagina,janela) {	var acao = 'gravar';	url = "<?=$PHP_SELF?>?ajax=sim&acao="+acao;	parametros = "";	for( var i = 0 ; i < formulatio.length; i++ ){		if (formulatio.elements[i].type !='button'){			if(formulatio.elements[i].type=='radio' || formulatio.elements[i].type=='checkbox'){				if(formulatio.elements[i].checked == true){					parametros = parametros+"&"+formulatio.elements[i].name+"="+escape(formulatio.elements[i].value);				}			}else{				parametros = parametros+"&"+formulatio.elements[i].name+"="+escape(formulatio.elements[i].value);			}		}	}	var com       = document.getElementById('erro');	var saida     = document.getElementById('saida');	com.innerHTML = "&nbsp;&nbsp;Aguarde...&nbsp;&nbsp;<br><img src='imagens/carregar2.gif' >";	saida.innerHTML = "&nbsp;&nbsp;Aguarde...&nbsp;&nbsp;<br><img src='imagens/carregar2.gif' >";	var curDateTime = new Date();	http_forn[curDateTime] = createRequestObject();	http_forn[curDateTime].open('POST',url,true);		http_forn[curDateTime].setRequestHeader("Content-type", "application/x-www-form-urlencoded");	http_forn[curDateTime].setRequestHeader("CharSet", "ISO-8859-1");	http_forn[curDateTime].setRequestHeader("Content-length", url.length);	http_forn[curDateTime].setRequestHeader("Connection", "close");	http_forn[curDateTime].onreadystatechange = function(){		if (http_forn[curDateTime].readyState == 4){			if (http_forn[curDateTime].status == 200 || http_forn[curDateTime].status == 304){			var response = http_forn[curDateTime].responseText.split("|");				if (response[0]=="debug"){					alert(http_forn[curDateTime].responseText);				}				if (response[0]=="ok"){					com.style.visibility = "hidden";					com.innerHTML = response[1];					saida.innerHTML = response[1];					if (document.getElementById('btn_continuar')){						document.getElementById('btn_continuar').style.display='inline';					}					formulatio.btn_acao.value='Gravar';					for( var i = 0 ; i < formulatio.length; i++ ){						if (formulatio.elements[i].type !='button'){							if(formulatio.elements[i].type=='radio' || formulatio.elements[i].type=='checkbox'){								if(formulatio.elements[i].checked == true){									formulatio.elements[i].checked=false;								}							}else{								formulatio.elements[i].value='';							}						}					}					window.location="planilha_retorno.php?ok";				}else{					formulatio.btn_acao.value='Gravar';				}				if (response[0]=="1"){					com.style.visibility = "visible";					saida.innerHTML = "<font color='#990000'>Ocorreu um erro, verifique!</font>";					com.innerHTML = response[1];					formulatio.btn_acao.value='Gravar';				}			}		}	}	http_forn[curDateTime].send(parametros);}</script><script type="text/javascript" src="admin/js/jquery-latest.pack.js"></script><script language="JavaScript">function listaConstatado(linha,familia, defeito_reclamado,defeito_constatado) {	try {ajax = new ActiveXObject("Microsoft.XMLHTTP");}	catch(e) { try {ajax = new ActiveXObject("Msxml2.XMLHTTP");}		catch(ex) { 			try {ajax = new XMLHttpRequest();}			catch(exc) {				alert("Esse browser n�o tem recursos para uso do Ajax"); ajax = null;			}		}	}	if(ajax) {		defeito_constatado.options.length = 1;		idOpcao  = document.getElementById("opcoes2");		ajax.open("GET","ajax_defeito_constatado.php?fabrica=45&defeito_reclamado="+defeito_reclamado+"&produto_familia="+familia+"&produto_linha="+linha);		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");		ajax.onreadystatechange = function() {			if(ajax.readyState == 1) {idOpcao.innerHTML = "Carregando...!";}			if(ajax.readyState == 4 ) {				if(ajax.responseXML) {					montaComboConstatado(ajax.responseXML,defeito_constatado);				}				else {					idOpcao.innerHTML = "Selecione o defeito reclamado";				}			}		}		ajax.send(null);	}}function montaComboConstatado(obj,defeito_constatado){	var dataArray   = obj.getElementsByTagName("produto");	if(dataArray.length > 0) {		for(var i = 0 ; i < dataArray.length ; i++) {     //percorre o arquivo XML paara extrair os dados			var item = dataArray[i];			var codigo    =  item.getElementsByTagName("codigo")[0].firstChild.nodeValue;			var nome =  item.getElementsByTagName("nome")[0].firstChild.nodeValue;			idOpcao.innerHTML = "Selecione o defeito";			var novo = document.createElement("option");			novo.setAttribute("id", "opcoes2");			novo.value = codigo;			novo.text  = nome  ;			defeito_constatado.options.add(novo);//adiciona		}	} else { 		idOpcao.innerHTML = "Selecione o defeito";	}}</script><?if(isset($ok)) $msg = "Informa��es da Planilha foram gravadas com sucesso";if(strlen($cc)>0){?><div id="m_1" class="modbox">	<h2 class="modtitle">		<div id="m_1_h">			<a class="mtlink" id="m_1_url" href="#">			<span id="m_1_title" class="modtitle_text"><font color="#005f9d">Retorno de Planilha de Produto</font></span>			</a>		</div>	</h2>	<div id="m_1_b" class="modboxin">		<div id="ftl_1_0" class="uftl" style='text-align:justify'>		<form name="frm_os" method="post" action="<? echo $PHP_SELF ?>">		<input type='hidden' name='produto_rg' id='produto_rg' value='<?=$produto_rg?>'>		<table style=' border:#485989 1px solid; background-color: #e6eef7;font-size:12px' align='center'  border='0' id='tbl_integridade' cellspacing='3' cellpadding='3' width='98%'>		<thead>		<tr bgcolor='#596D9B' style='color:#FFFFFF;'>		<td width='5'><b>#</b></td>		<td ><b>RG</b></td>		<td ><b>Descri��o do Produto</b></td>		<td ><b>S�rie</b></td>		<td ><b>Defeito Reclamado</b></td>		<td ><b>Defeito Constatado</b></td>		<td ><b>Observa��o</b></td>		<td ><b>A��es</b></td>		</tr>		</thead>		<tbody>			<?			$sql = "SELECT 							RI.produto_rg                                                ,							RI.produto_rg_item                                           ,							RI.rg                                                        ,							RI.codigo_barra                                              ,							RI.serie                                                     ,							RI.defeito_reclamado                                         ,							RI.observacao                                                ,							PR.produto                                                   ,							PR.referencia                           AS produto_referencia,							PR.descricao                            AS produto_descricao ,							PR.linha                                                     ,							PR.familia                                                   ,							DC.defeito_constatado                   AS defeito_constatado,							DC.descricao                            AS defeito_descricao ,							TO_CHAR(RI.data_digitacao,'dd/mm/YYYY') AS data			FROM       tbl_produto_rg                  RG			JOIN       tbl_produto_rg_item             RI USING(produto_rg)			JOIN       tbl_conta_corrente_tecnico_item CI ON RI.produto_rg_item        = CI.produto_rg_item			JOIN       tbl_conta_corrente_tecnico      CC ON CC.conta_corrente_tecnico = CI.conta_corrente_tecnico			JOIN       tbl_produto         PR USING(produto)			LEFT JOIN  tbl_defeito_constatado     DC ON DC.defeito_constatado = RI.defeito_constatado			WHERE CC.conta_corrente_tecnico = $cc			ORDER BY   CI.conta_corrente_tecnico_item";			$res = pg_exec($con,$sql);			for($i=0;$i<pg_numrows($res);$i++) {				$produto_rg         = pg_result($res,$i,produto_rg);				$produto_rg_item    = pg_result($res,$i,produto_rg_item);				$rg                 = pg_result($res,$i,rg);				$codigo_barra       = pg_result($res,$i,codigo_barra);				$produto            = pg_result($res,$i,produto);				$referencia         = pg_result($res,$i,produto_referencia);				$descricao          = pg_result($res,$i,produto_descricao);				$serie              = pg_result($res,$i,serie);				$defeito_reclamado  = pg_result($res,$i,defeito_reclamado);				$defeito_constatado = pg_result($res,$i,defeito_constatado);				$defeito_descricao  = pg_result($res,$i,defeito_descricao);				$observacao         = pg_result($res,$i,observacao);				$data               = pg_result($res,$i,data);				$linha              = pg_result($res,$i,linha);				$familia            = pg_result($res,$i,familia);				$x = $i+1;				if(strlen($produto)==0 and strlen($codigo_barra)>0){					$sql2 = "SELECT produto,referencia,descricao							FROM   tbl_produto							JOIN   tbl_linha   USING(linha)							WHERE  codigo_barra = '$codigo_barra'							AND    fabrica      = 45							";					$res2 = @pg_exec($con,$sql2);					$produto         = @pg_result($res2,0,produto);					$referencia      = @pg_result($res2,0,referencia);					$descricao       = @pg_result($res2,0,descricao);				}else{					$read = " READONLY";				}				echo "<tr>\n";				echo "<td>$x</td>\n";				echo "<td><input type='hidden' name='produto_rg_item_$i' id='produto_rg_item_$i' value='$produto_rg_item'><input type='hidden' name='rg_$i' id='rg_$i' value='$rg'><input type='hidden' name='produto_rg_$i' id='produto_rg_$i' value='$produto_rg'>&nbsp;&nbsp;$rg</td>\n";				echo "<td title='$referencia - $descricao'>\n";				echo "<input type='hidden' name='produto_$i' id='produto_$i' value='$produto'>\n";				echo "<input type='hidden' name='produto_linha_$i' id='produto_linha_$i' value='$linha'>\n";				echo "<input type='hidden' name='produto_familia_$i' id='produto_familia_$i' value='$familia'>\n";				echo "$descricao</td>\n";				echo "<td><input type='text' name='serie_$i' id='serie_$i'  value='$serie' class='Caixa' size='10'></td>\n";				echo "<td><input type='text' name='defeito_reclamado_$i' id='defeito_reclamado_$i'  value='$defeito_reclamado' class='Caixa' size='25'></td>\n";				echo "<td><select name='defeito_constatado_$i' id='defeito_constatado_$i' class='Caixa' style='width: 220px;' onfocus='listaConstatado(document.frm_os.produto_linha_$i.value, document.frm_os.produto_familia_$i.value,document.frm_os.defeito_reclamado_$i.value,this);' >\n";				if(strlen($defeito_constatado)>0) echo "<option  id='opcoes2' value='$defeito_constatado'>$defeito_descricao</option>\n";				else                              echo "<option  id='opcoes2' value=''></option>\n";				echo "</select>\n";				echo "</td>\n";				echo "<td><input type='text' name='observacao_$i' id='observacao_$i'  value='$observacao' class='Caixa' size='25'></td>\n";				echo "<td><a href='planilha_item?id=$produto_rg_item'>Lan�ar Pe�as</a></td>\n";				echo "</tr>\n";			}			echo "</tbody>\n";			echo "<tfoot>\n";			echo "<tr>\n";			echo "<td colspan='9'>\n";			echo "<table style=' border:#B63434 1px solid; background-color: #EED5D2' align='center' width='100%' border='0' height='40'>\n";			echo "<tr>\n";			echo "<td valign='middle' align='LEFT' class='Label' >\n";			echo "</td>\n";			echo "</tr>\n";			echo "<tr><td width='50' valign='middle'  align='LEFT' colspan='4'><input type='button' name='btn_acao'  value='Gravar' onClick=\"if (this.value!='Gravar'){ alert('Aguarde');}else {this.value='Gravando...'; gravar(this.form,'sim','$PHP_SELF','nao');}\" style=\"width: 150px;\"></td>\n";						echo "<td width='300'><div id='saida' style='display:inline;'></div></td>\n";			echo "</tr>\n";			echo "</table>\n";			?>			</td>		</tr>		</tfoot>		</table>			<div id='erro' style='visibility:hidden;opacity:0.85' class='Erro'></div>			<br><center><a href='planilha_print.php?cc=<?=$cc?>' target='_blank'>[imprimir]</a></center>		</form>		</div>	</div></div><?}else{?><div id="m_1" class="modbox">	<h2 class="modtitle">		<div id="m_1_h">			<a class="mtlink" id="m_1_url" href="#">			<span id="m_1_title" class="modtitle_text"><font color="#005f9d">Planilhas analisadas pelos seguintes t�cnicos</font></span>			</a>		</div>	</h2>	<div id="m_1_b" class="modboxin">		<div id="ftl_1_0" class="uftl" style='text-align:justify'>			<?			$sql = "SELECT  count(*)       AS total  ,							CC.conta_corrente_tecnico,							LU.login_unico           ,							LU.nome					FROM tbl_produto_rg             RG					JOIN tbl_produto_rg_item        RI USING(produto_rg)					JOIN       tbl_conta_corrente_tecnico_item CI ON RI.produto_rg_item        = CI.produto_rg_item					JOIN       tbl_conta_corrente_tecnico      CC ON CC.conta_corrente_tecnico = CI.conta_corrente_tecnico					JOIN tbl_login_unico                       LU USING(login_unico)					WHERE RG.posto = $login_posto					AND   CI.data_devolucao IS NULL					GROUP BY CC.conta_corrente_tecnico,							 LU.login_unico           ,							 LU.nome					ORDER BY total";			$res = pg_exec($con,$sql);			if(pg_numrows($res)>0){				echo "<table style=' border:#485989 1px solid; background-color: #e6eef7;font-size:12px' align='center'  border='0' id='tbl_integridade' cellspacing='3' cellpadding='3' width='700'>				<thead>				<tr bgcolor='#596D9B' style='color:#FFFFFF;'>				<td><b>Conta Corrente</b></td>				<td><b>T�cnico</b></td>				<td><b>Total de Produtos</b></td>				<td><b>A��es</b></td>				</tr>				</thead>				<tbody>";				for($i=0;$i<pg_numrows($res);$i++) {					$tecnico      = pg_result($res,$i,login_unico);					$nome         = pg_result($res,$i,nome);					$total        = pg_result($res,$i,total);					$conta_corrente_tecnico        = pg_result($res,$i,conta_corrente_tecnico);					if($cor<>'#FFFFFF') $cor = '#FFFFFF';					else                $cor = '#e6eef7';					echo "<tr bgcolor='$cor'>";					echo "<td><a href='planilha_retorno.php?cc=$conta_corrente_tecnico'>$conta_corrente_tecnico</a></td>\n";					echo "<td>$nome</td>\n";					echo "<td>$total</td>\n";					echo "<td align='center'>\n";					echo "<a href='planilha_print.php?cc=$conta_corrente_tecnico' target='_blank'><img src='imagens/icone_imprimir.png' title='Imprimir Planilha'></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";					echo "<a href='planilha_retorno.php?cc=$conta_corrente_tecnico' title='Baixa Planilha'><img src='imagens/icone_planilha.gif'></a>";					echo "</td>\n";					echo "</tr>\n";				}			echo "</tbody>";			echo "</table>";			}			?>		</div>	</div></div><?}include "rodape.php";?>