<?include 'dbconfig.php';include 'includes/dbconnect-inc.php';include "login_unico_autentica_usuario.php";$q = strtolower($_GET["q"]);if (isset($_GET["q"])){	$tipo_busca = $_GET["busca"];	if (strlen($q)>2){		$sql = "			SELECT  PR.produto   ,					PR.descricao ,					PR.referencia,					FA.nome      			FROM tbl_produto PR			JOIN tbl_linha   LI USING(linha)			JOIN tbl_fabrica FA USING(fabrica)			WHERE  fabrica=45			";		if ($tipo_busca == "codigo") $sql .= " AND UPPER(PR.referencia) LIKE UPPER('%$q%') ";		else                         $sql .= " AND UPPER(PR.descricao)  LIKE UPPER('%$q%') ";		$res = pg_exec($con,$sql);		if (pg_numrows ($res) > 0) {			for ($i=0; $i<pg_numrows ($res); $i++ ){				$produto    = trim(pg_result($res,$i,produto));				$descricao  = trim(pg_result($res,$i,descricao));				$referencia = trim(pg_result($res,$i,referencia));				$fabrica    = trim(pg_result($res,$i,nome));				if($cor<>'#FFFFFF') $cor = "#FFFFFF";				else                $cor = "#EEEEEE";				echo "$produto|$referencia|$descricao|$fabrica|<span style='font-size:10px;font-family:verdana;'>$referencia - $descricao<br>Fabricante: $fabrica</div>\n";			}		}	}	exit;}if ($acao == "gravar" AND $ajax == "sim") {	$produto_rg    = $_POST["produto_rg"];	$revenda_posto = $_POST["revenda_posto"];	if(strlen($revenda_posto)==0) $msg_erro = "Selecione a revenda!";	if(strlen($msg_erro)==0){		$sql = "SELECT revenda,fabrica FROM tbl_revenda_posto WHERE revenda_posto = $revenda_posto";		$res = pg_exec($con,$sql);		$revenda = pg_result($res,0,revenda);		$fabrica = pg_result($res,0,fabrica);	}	if (strlen($msg_erro) == 0) {		$res = pg_exec ($con,"BEGIN TRANSACTION");		$sql = "UPDATE tbl_produto_rg SET 					revenda = $revenda ,					fabrica = $fabrica 				WHERE produto_rg = $produto_rg;";		$res = @pg_exec($con,$sql);		$msg_erro .= pg_errormessage($con);		if(strlen($msg_erro)==0){			$qtde_item = 100;			for ($i = 0 ; $i < $qtde_item ; $i++) {								$produto_rg_item   = trim($_POST['produto_rg_item_' . $i]);				$codigo_barra      = trim($_POST['codigo_barra_' . $i]);				$produto           = trim($_POST['produto_' . $i]);				$serie             = trim($_POST['serie_' . $i]);				$defeito_reclamado = trim($_POST['defeito_reclamado_' . $i]);				if(strlen($produto_rg_item)==0) continue;				if(strlen($msg_erro)>0)         break;				$x = $i+1;								if(strlen($produto)==0)           $msg_erro .= "Informe o produto na linha $x<br>";				if(strlen($serie)==0)             $msg_erro .= "Informe a s�rie do produto na linha $x<br>";				if(strlen($defeito_reclamado)==0) $msg_erro .= "Informe o defeito reclamado do produto na linha $x<br>";				if(strlen($msg_erro)==0){					$sql =	"UPDATE tbl_produto_rg_item SET								codigo_barra      = '$codigo_barra'     ,								serie             = '$serie'            ,								defeito_reclamado = '$defeito_reclamado',								produto           = $produto            ,								fabrica           = $fabrica            ,								data_conferencia  = CURRENT_TIMESTAMP							WHERE produto_rg_item = $produto_rg_item ; ";					$res = @pg_exec($con,$sql);					$msg_erro .= pg_errormessage($con);				}else $msg_erro_linha = $i;			}			if(strlen($msg_erro)==0){				$sql = "SELECT fn_gera_lote($produto_rg,$cook_posto);";				$res = @pg_exec($con,$sql);				$msg_erro .= pg_errormessage($con);			}		}	}	if (strlen($msg_erro) == 0) {		$res = pg_exec($con,"COMMIT TRANSACTION");		echo "ok|Gravado com Sucesso|$produto_rg";	}else{		$res = pg_exec($con,"ROLLBACK TRANSACTION");		echo "1|$msg_erro|$msg_erro_linha";	}	exit;}$aba=2;include "login_unico_cabecalho.php";?><script>function createRequestObject(){	var request_;	var browser = navigator.appName;	if(browser == "Microsoft Internet Explorer"){		 request_ = new ActiveXObject("Microsoft.XMLHTTP");	}else{		 request_ = new XMLHttpRequest();	}	return request_;}var http_forn = new Array();function gravar(formulatio,redireciona,pagina,janela) {	var acao = 'gravar';	url = "<?=$PHP_SELF?>?ajax=sim&acao="+acao;	parametros = "";	for( var i = 0 ; i < formulatio.length; i++ ){		if (formulatio.elements[i].type !='button'){			//alert(formulatio.elements[i].name+' = '+formulatio.elements[i].value);			if(formulatio.elements[i].type=='radio' || formulatio.elements[i].type=='checkbox'){								if(formulatio.elements[i].checked == true){//					alert(formulatio.elements[i].value);					parametros = parametros+"&"+formulatio.elements[i].name+"="+escape(formulatio.elements[i].value);				}			}else{				parametros = parametros+"&"+formulatio.elements[i].name+"="+escape(formulatio.elements[i].value);			}		}	}	var com       = document.getElementById('erro');	var saida     = document.getElementById('saida');	com.innerHTML = "&nbsp;&nbsp;Aguarde...&nbsp;&nbsp;<br><img src='imagens/carregar2.gif' >";	saida.innerHTML = "&nbsp;&nbsp;Aguarde...&nbsp;&nbsp;<br><img src='imagens/carregar2.gif' >";	var curDateTime = new Date();	http_forn[curDateTime] = createRequestObject();	http_forn[curDateTime].open('POST',url,true);		http_forn[curDateTime].setRequestHeader("Content-type", "application/x-www-form-urlencoded");	http_forn[curDateTime].setRequestHeader("CharSet", "ISO-8859-1");	http_forn[curDateTime].setRequestHeader("Content-length", url.length);	http_forn[curDateTime].setRequestHeader("Connection", "close");	http_forn[curDateTime].onreadystatechange = function(){		if (http_forn[curDateTime].readyState == 4){			if (http_forn[curDateTime].status == 200 || http_forn[curDateTime].status == 304){			//alert(http_forn[curDateTime].responseText);			var response = http_forn[curDateTime].responseText.split("|");				if (response[0]=="debug"){					alert(http_forn[curDateTime].responseText);				}				if (response[0]=="ok"){					com.style.visibility = "hidden";					com.innerHTML = response[1];					saida.innerHTML = response[1];					if (document.getElementById('btn_continuar')){						document.getElementById('btn_continuar').style.display='inline';					}					formulatio.btn_acao.value='Gravar';					for( var i = 0 ; i < formulatio.length; i++ ){						if (formulatio.elements[i].type !='button'){							if(formulatio.elements[i].type=='radio' || formulatio.elements[i].type=='checkbox'){								if(formulatio.elements[i].checked == true){									formulatio.elements[i].checked=false;								}							}else{								formulatio.elements[i].value='';							}						}					}					window.location="login_unico_rg_lote.php?produto_rg="+response[2];				}else{					formulatio.btn_acao.value='Gravar';				}				if (response[0]=="1"){					com.style.visibility = "visible";					saida.innerHTML = "<font color='#990000'>Ocorreu um erro, verifique!</font>";					com.innerHTML = response[1];					formulatio.btn_acao.value='Gravar';				}			}		}	}	http_forn[curDateTime].send(parametros);}</script><? include "admin/javascript_calendario.php"; //adicionado por Fabio 27-09-2007 ?><script type='text/javascript' src='js/jquery.autocomplete.js'></script><link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" /><script type='text/javascript' src='js/jquery.bgiframe.min.js'></script><script type='text/javascript' src='js/dimensions.js'></script><script type="text/javascript" src="js/thickbox.js"></script><link rel="stylesheet" href="js/thickbox.css" type="text/css" media="screen" /><script language="JavaScript">$().ready(function() {	function formatItem(row) {		return row[4];	}		function formatResult(row) {		return row[0];	}		/* Busca pelo C�digo */	$("input[@rel='referencia']").autocomplete("<?echo 'login_unico_rg_conferencia.php?busca=codigo'; ?>", {		minChars: 3,		delay: 150,		width: 350,		matchContains: true,		formatItem: formatItem,		formatResult: function(row) {return row[1];}	});	$("input[@rel='referencia']").result(function(event, data, formatted) {		$("input[@name=produto_"+$(this).attr("alt")+"]").val(data[0])   ;		$("input[@name=descricao_"+$(this).attr("alt")+"]").val(data[2]) ;		$("input[@name=serie_"+$(this).attr("alt")+"]").focus()          ;		//$("input[@name=fabrica_"+$(this).attr("alt")+"]").html(data[3]);	});	/* Busca pelo Nome */	$("input[@rel='descricao']").autocomplete("<?echo 'login_unico_rg_conferencia.php?busca=nome'; ?>", {		minChars: 3,		delay: 150,		width: 350,		matchContains: true,		highlightItem: false,		formatItem: formatItem,		formatResult: function(row) {return row[2];}	});	$("input[@rel='descricao']").result(function(event, data, formatted) {		$("input[@name=produto_"+$(this).attr("alt")+"]").val(data[0])    ;		$("input[@name=referencia_"+$(this).attr("alt")+"]").val(data[1]) ;		$("input[@name=serie_"+$(this).attr("alt")+"]").focus()           ;		//$("input[@name=fabrica_"+$(this).attr("alt")+"]").html(data[3]);	});});</script><?if(strlen($produto_rg)>0){?><h2><font color='#DDDDDD'><b>1 Recebimento&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font color="#005f9d">2 Conferencia</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 3 Gerar Lote</font></b></h2><table width='98%' align='center'>	<tr><td align='center'><center><div id="m_1" class="modbox">	<h2 class="modtitle">		<div id="m_1_h">			<a class="mtlink" id="m_1_url" href="#">			<span id="m_1_title" class="modtitle_text"><font color="#005f9d">Confer�ncia de Lote de Produto</font></span>			</a>		</div>	</h2>	<div id="m_1_b" class="modboxin">		<div id="ftl_1_0" class="uftl" style='text-align:justify'>		<form name="frm_os" method="post" action="<? echo $PHP_SELF ?>">		<input type='hidden' name='produto_rg' id='produto_rg' value='<?=$produto_rg?>'>		<?		$sql = "SELECT  RP.revenda_posto                  ,						RE.revenda                        ,						RE.cnpj            AS revenda_cnpj,						RE.nome            AS revenda_nome,						FA.fabrica                        ,						FA.nome            AS fabrica_nome				FROM tbl_revenda_posto RP				JOIN tbl_revenda       RE USING(revenda)				JOIN tbl_fabrica       FA USING(fabrica)				WHERE posto = $cook_posto				ORDER BY revenda_nome";		$res = pg_exec($con,$sql);		echo "<table align='center' style='font-family:verdana,sans;font-size:10px;'>";		echo "<tr>";		echo "<td>";		echo "Selecione a revenda:<br> ";		for($i=0;$i < pg_numrows($res);$i++){			$revenda_posto = pg_result($res,$i,revenda_posto);			$revenda       = pg_result($res,$i,revenda);			$revenda_cnpj  = pg_result($res,$i,revenda_cnpj);			$revenda_nome  = pg_result($res,$i,revenda_nome);			$revenda       = pg_result($res,$i,fabrica);			$fabrica_nome  = pg_result($res,$i,fabrica_nome);			echo "<input type='radio' name='revenda_posto' id='revenda_posto' value='$revenda_posto'> $revenda_nome - CNPJ: $revenda_cnpj - Fabricante: $fabrica_nome<br>";		}		echo "</td>";		echo "</tr>";		echo "</table>";		?>		<table style=' border:#485989 1px solid; background-color: #e6eef7;font-size:12px' align='center'  border='0' id='tbl_integridade' cellspacing='3' cellpadding='3' >		<thead>		<tr bgcolor='#596D9B' style='color:#FFFFFF;'>		<td width='5'><b>#</b></td>		<td width='200'><b>RG</b></td>		<td width='150'><b>C�digo Barra</b></td>		<td width='130'><b>Refer�ncia do Produto</b></td>		<td width='150'><b>Descri��o do Produto</b></td>		<td width='70'><b>S�rie</b></td>		<td width='150'><b>Defeito Reclamado</b></td>		</tr>		</thead>		<tbody>			<?			$sql = "SELECT * 					FROM tbl_produto_rg_item					WHERE produto_rg = $produto_rg";			$res = pg_exec($con,$sql);			for($i=0;$i<pg_numrows($res);$i++) {				$produto_rg_item = pg_result($res,$i,produto_rg_item);				$rg              = pg_result($res,$i,rg);				$x = $i+1;				echo "<tr>";				echo "<td>$x</td>";				echo "<td><input type='hidden' name='produto_rg_item_$i' id='produto_rg_item_$i' value='$produto_rg_item'>&nbsp;&nbsp;$rg</td>";				echo "<td><input type='text' name='codigo_barra_$i' id='codigo_barra_$i' value='$codigo_barra' class='Caixa'></td>";				echo "<td><input type='hidden' name='produto_$i' id='produto_$i' value='$produto' class='Caixa'><input type='text' name='referencia_$i' id='referencia_$i' value='$referencia'  rel='referencia' alt='$i' class='Caixa' size='15'></td>";				echo "<td><input type='text' name='descricao_$i' id='descricao_$i' rel='descricao' alt='$i' value='$descricao' class='Caixa' size='20'></td>";				echo "<td><input type='text' name='serie_$i' id='serie_$i'  value='$serie' class='Caixa' size='10'></td>";				echo "<td><input type='text' name='defeito_reclamado_$i' id='defeito_reclamado_$i'  value='$defeito_reclamado' class='Caixa' size='25'></td>";				echo "<td><div name='fabrica_$i' id='fabrica_$i'></div></td>";				echo "</tr>";			}			echo "</tr>";			echo "</tbody>";			echo "<tfoot>";			echo "<tr>";			echo "<td colspan='7'>";			echo "<table style=' border:#B63434 1px solid; background-color: #EED5D2' align='center' width='100%' border='0'height='40'>";			echo "<tr>";			echo "<td valign='middle' align='LEFT' class='Label' >";			echo "</td>";			echo "<tr><td width='50' valign='middle'  align='LEFT' colspan='4'><input type='button' name='btn_acao'  value='Gravar' onClick=\"if (this.value!='Gravar'){ alert('Aguarde');}else {this.value='Gravando...'; gravar(this.form,'sim','$PHP_SELF','nao');}\" style=\"width: 150px;\"></td>";						echo "<td width='300'><div id='saida' style='display:inline;'></div></td>";			echo "</tr>";			echo "</table>";			?>			</td>		</tr>		</tfoot>		</table><div id='erro' style='visibility:hidden;opacity:0.85' class='Erro'></div>		</div>	</div></div></td></tr></table><?}else{?><table width='98%' align='center'>	<tr><td align='center'><center><div id="m_1" class="modbox">	<h2 class="modtitle">		<div id="m_1_h">			<a class="mtlink" id="m_1_url" href="#">			<span id="m_1_title" class="modtitle_text"><font color="#005f9d">RG de Produtos recebidos mas que n�o foram conferidos</font></span>			</a>		</div>	</h2>	<div id="m_1_b" class="modboxin">		<div id="ftl_1_0" class="uftl" style='text-align:justify'>			<?			$sql = "SELECT  RG.produto_rg,							RI.rg       ,							TO_CHAR(RI.data_digitacao,'dd/mm/yyyy') AS data					FROM tbl_produto_rg      RG					JOIN tbl_produto_rg_item RI USING(produto_rg)					WHERE posto = $login_posto					AND   RI.explodido     IS FALSE					AND   data_conferencia IS NULL					ORDER BY RG.produto_rg,							 RI.rg        ,							 RI.data_digitacao";			$res = pg_exec($con,$sql);			if(pg_numrows($res)>0){				echo "<table style=' border:#485989 1px solid; background-color: #e6eef7;font-size:12px' align='center'  border='0' id='tbl_integridade' cellspacing='3' cellpadding='3' width='500'>				<thead>				<tr bgcolor='#596D9B' style='color:#FFFFFF;'>				<td><b>Lote</b></td>				<td><b>RG</b></td>				<td><b>Data digita��o</b></td>				</tr>				</thead>				<tbody>";							for($i=0;$i<pg_numrows($res);$i++) {				$produto_rg    = pg_result($res,$i,produto_rg);				$rg       = pg_result($res,$i,rg);				$data                 = pg_result($res,$i,data);				if($cor<>'#FFFFFF') $cor = '#FFFFFF';				else                $cor = '';				echo "<tr bgcolor='$cor'>";				echo "<td><a href='login_unico_rg_conferencia.php?produto_rg=$produto_rg'>$produto_rg</td>";				echo "<td>$rg</td>";				echo "<td>$data&nbsp;</td>";				echo "<td>$sua_os</a></td>";				echo "</tr>";			}			echo "</tr>";			echo "</tbody>";			echo "</table>";			}			?>		</div>	</div></div></td></tr></table><?}include "login_unico_rodape.php";?>