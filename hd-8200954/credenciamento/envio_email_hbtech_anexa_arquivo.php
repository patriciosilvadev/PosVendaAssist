<?include "/www/assist/www/dbconfig.php";include "/www/assist/www/includes/dbconnect-inc.php";$fabrica = 25;?><html> <head> <title>Enviando texto</title> <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"> <style type="text/css"> <!-- .email { text-transform: lowercase; } .texto { color: #0000FF } .style1 {color: #FF0000} --> </style> </head> <body onLoad="document.email.nome.focus();"> <form action='<?=$PHP_SELF?>' method="post" enctype="multipart/form-data" name="email"> <tr> <td><div align="right" class="texto">Anexo</div></td> <td><input name="arquivo" type="file"></td> </tr> <tr> <td>&nbsp;</td> <td><input type="submit" name="btn_acao" value="Enviar"></td> </tr> </table> </form> </body> </html> Anexar arquivo primeiro para mandar email o contrato de HBflex para os postos.<?php if($_POST['btn_acao']=='Enviar'){	$sql = "SELECT DISTINCT tbl_posto.posto, cnpj, contato_email as email, nome				FROM tbl_posto 				JOIN tbl_posto_fabrica USING(posto)				WHERE email in ('calza@brturbo.com.br')				and fabrica=25; ";$res = pg_exec($con,$sql);echo "<table border='1'>";if(pg_numrows($res) > 0){for($i = 0; $i < pg_numrows($res); $i++){	$email_posto  = pg_result($res, $i, email);	$cnpj         = pg_result($res, $i, cnpj);	$posto        = pg_result($res, $i, posto);	$nome_posto   = pg_result($res, $i, nome);$fabrica=25;	$mensagem = '';	$id = $posto;	$key = md5($fabrica);$nome = 'HBTech'; $email_from = 'suporte@hbflex.com'; 	$nome_para = $_POST["nome_para"]; 	$email = 'paulo@telecontrol.com.br'; 	$mensagem .="<html><head></head><body>";	$mensagem  .= "<img src='http://www.telecontrol.com.br/assist/credenciamento/hbtech/superior.jpg'>";	$mensagem  .= "<table><tr><td><p align='left'>A Rede Autorizada</p><br></td></tr>";	$mensagem  .= "<tr><td width='500'><p align='justify'>A HBFLEX agradece a sua confian�a depositada nesta nova marca. Juntos abriremos novas oportunidades e consolidaremos nossos					neg�cios. Neste momento � hora de firmar compromisso e mostrar ao mercado as nossas for�as. Atendendo aos modernos padr�es de					qualidade e exig�ncias da grande Rede Varejista precisamos formalizar o acordo que garantir� a crescente comercializa��o dos nossos					produtos e Servi�os.";	$mensagem  .= "<br><br>";	$mensagem  .= "A HBFLEX convida-o para ler o contrato de presta��o de servi�o. Durante sua an�lise, nos colocamos a disposi��o para sanar d�vidas					que possam surgir. <u>Deve ser impresso em duas vias , reconhecido firma em cart�rio e encaminha-las atrav�s de carta registrada para o					endere�o abaixo</u>. Enviar c�pia do Contrato Social da Empresa (com a �ltima altera��o, se houver). Se a pessoa que assinar o contrato n�o 					constar no Contrato Social , ser� necess�rio a c�pia da Procura��o P�blica. A HBFLEX lhe devolver� umas das vias devidamente assinada. ";	$mensagem  .= "<br><br>";	$mensagem  .= "HBFLEX<br>					&nbsp;Av. MArqu�s de S�o Vicente, 121 , Bloco B Cj. 401<br>					&nbsp;CEP 01139 - 001<br>					&nbsp;S�o Paulo - SP </p>";	$mensagem  .= "<br>";	$mensagem  .= "<b>A HBFLEX n�o poder� enviar pe�as em garantia e nem realizar pagamentos a sua empresa antes do retorno deste contrato.</b>";	$mensagem  .= "<br><br>";	$mensagem  .= "<a href=\"http://www.telecontrol.com.br/assist/credenciamento/contrato/contrato_html.php?id=$id&key=$key\"><u><b>Clique aqui para acessar o contrato.</b></u></a><br>";	$mensagem  .= "<br><FONT COLOR='#9B9B9B'><b>Aten��o!</b> Se voc� n�o conseguir clicar no atalho acima, acesse este endere�o: www.telecontrol.com.br/assist/credenciamento/contrato/contrato_html.php?id=$id&key=$key</FONT>";	$mensagem  .= "<br><br>";	$mensagem  .= "d�vidas: suporte@hbflex.com";	$mensagem  .= "<img src='http://www.telecontrol.com.br/assist/credenciamento/hbtech/inferior.jpg'></td></tr></table></body></html>";	$assunto   = "AUTO CADASTRAMENTO - HBTECH";$arquivo = isset($_FILES["arquivo"]) ? $_FILES["arquivo"] : FALSE; if(file_exists($arquivo["tmp_name"]) and !empty($arquivo)){ $fp = fopen($_FILES["arquivo"]["tmp_name"],"rb"); $anexo = fread($fp,filesize($_FILES["arquivo"]["tmp_name"])); $anexo = base64_encode($anexo); fclose($fp); $anexo = chunk_split($anexo); $boundary = "XYZ-" . date("dmYis") . "-ZYX"; $mens = "--$boundary\n"; $mens .= "Content-Transfer-Encoding: 8bits\n"; $mens .= "Content-Type: text/html; charset=\"ISO-8859-1\"\n\n"; //plain $mens .= "$mensagem\n"; $mens .= "--$boundary\n"; $mens .= "Content-Type: ".$arquivo["type"]."\n"; $mens .= "Content-Disposition: attachment; filename=\"".$arquivo["name"]."\"\n"; $mens .= "Content-Transfer-Encoding: base64\n\n"; $mens .= "$anexo\n"; $mens .= "--$boundary--\r\n"; $headers = "MIME-Version: 1.0\n"; $headers .= "From: \"$nome\" <$email_from>\r\n"; $headers .= "Content-type: multipart/mixed; boundary=\"$boundary\"\r\n"; $headers .= "Bcc: <sergio@telecontrol.com.br>\r\n";  //envio o email com o anexo }else{$boundary = "XYZ-" . date("dmYis") . "-ZYX"; $mens = "--$boundary\n"; $mens .= "Content-Transfer-Encoding: 8bits\n"; $mens .= "Content-Type: text/html; charset=\"ISO-8859-1\"\n\n"; //plain $mens .= "$mensagem\n"; $mens .= "--$boundary\n"; $mens .= "--$boundary--\r\n"; $headers = "MIME-Version: 1.0\n"; $headers .= "From: \"$nome\" <$email_from>\r\n"; $headers .= "Content-type: multipart/mixed; boundary=\"$boundary\"\r\n"; //mandar copia oculta//$headers .= "Bcc: <paulo@telecontrol.com.br>\r\n";  //envio o email com o anexo } if(mail($nome_posto.'<paulo@telecontrol.com.br>',$assunto,$mens,$headers)){	$msg="";	$msg .="Email enviado para $email_posto!"; }}}if(strlen($msg) > 0) {	echo $msg;}}?>