<?php
/**
 *
 * importa-peca.php
 *
 * Importa��o de pe�as YANMAR
 */

error_reporting(E_ALL ^ E_NOTICE);

define('ENV', 'producao');
// define('ENV', 'teste');
define('DEV_EMAIL', 'william.brandino@telecontrol.com.br');

try {

// 	include dirname(__FILE__) . '/../../dbconfig_bc_teste.php';
	include dirname(__FILE__) . '/../../dbconfig.php';
	include dirname(__FILE__) . '/../../includes/dbconnect-inc.php';

	//yanmar
	$fabrica = 148;
	$fabrica_nome = 'yanmar';

	function strtim($var)
	{
		if (!empty($var)) {
			$var = trim($var);
			$var = str_replace("'", "", $var);
			$var = str_replace("\"", "", $var);
			$var = str_replace(",", ".", $var);
		}

		return $var;
	}

	$diretorio_origem = '/home/yanmar/yanmar-telecontrol';
	$arquivo_origem = 'telecontrol-peca.txt';

	$ftp = '/tmp/' . $fabrica_nome . '/';

	if (ENV == 'teste') {
		//$ftp = dirname(__FILE__) . '/../' . $fabrica_nome;
	}

	date_default_timezone_set('America/Sao_Paulo');
	$now = date('Ymd_His');

	$log_dir = '/tmp/' . $fabrica_nome .'/logs';
// 	$log_dir = dirname(__FILE__) . $diretorio_origem.'/logs';
	$arq_log = $log_dir . '/importa-peca-' . $now . '.log';
	$err_log = $log_dir . '/importa-peca-err-' . $now . '.log';

	if (!is_dir($log_dir)) {
		if (!mkdir($log_dir, 0777, true)) {
			throw new Exception("ERRO: N�o foi poss�vel criar logs. Falha ao criar diret�rio: $log_dir");
		}
	}

	$arquivo = $diretorio_origem . '/' . $arquivo_origem;


 	if (ENV == 'teste') {
 		$arquivo = '../' . $fabrica_nome . '/' . $diretorio_origem . '/' .$arquivo_origem;
 	}

	if (file_exists($arquivo) and (filesize($arquivo) > 0)) {
		$conteudo = file_get_contents($arquivo);
		$conteudo = explode("\n", $conteudo);

		$nlog = fopen($arq_log, "w");
		$elog = fopen($err_log, "w");
		foreach ($conteudo as $linha) {
			if (!empty($linha)) {
				list ($referencia, $descricao, $unidade, $familia_peca,$origem_arq, $ipi, $ncm) = explode (";",$linha);
				$original = array($referencia, $descricao, $unidade, $familia_peca, $origem_arq, $ipi, $ncm);

				$referencia = strtim($referencia);
				$descricao = strtim($descricao);
				$unidade = strtim($unidade);
				$familia_peca = strtim($familia_peca);
				$origem_arq = 'NAC';
				$ipi = strtim($ipi);
				$ncm = strtim($ncm);

				$descricao = substr($descricao, 0, 50);

				$sql_peca = "SELECT tbl_peca.peca FROM   tbl_peca
								WHERE  tbl_peca.referencia = '$referencia'
								AND    tbl_peca.fabrica    = $fabrica";
				$query_peca = pg_query($con, $sql_peca);

				if (pg_num_rows($query_peca) == 0) {
					$sql = "INSERT INTO tbl_peca (
									fabrica,
									referencia,
									descricao,
									unidade,
									origem,
									ipi,
									ncm
								) VALUES (
									$fabrica,
									'$referencia',
									(E'$descricao'),
									'$unidade',
									'$origem_arq',
									$ipi,
									'$ncm'
								)";
				} else {
					$peca = pg_fetch_result($query_peca, 0, 'peca');
					$sql = "UPDATE tbl_peca SET
									descricao = (E'$descricao'),
									unidade   = '$unidade',
									origem    = '$origem_arq',
									ipi       = $ipi,
									ncm       = '$ncm'
								WHERE tbl_peca.peca = $peca";
				}

				$query = pg_query($con, $sql);

				if (pg_last_error()) {
					array_push($original, 'erro 1');
					$log = implode(";", $original);
					fwrite($nlog, $log . "\n");

					$erro = "==============================\n\n";
					$erro.= $sql . "\n\n";
					$erro.= pg_last_error();
					$erro.= "\n\n";
					fwrite($elog, $erro);
				} else {
					array_push($original, 'ok');
					$log = implode(";", $original);
					fwrite($nlog, $log . "\n");
				}

			}
		}

		fclose($nlog);
		fclose($elog);

		if (filesize($arq_log) > 0) {
			$data_arq_enviar = date('dmy');
			$cmds = "cd $log_dir && cp importa-peca-$now.log peca$data_arq_enviar.txt && zip -r peca$data_arq_enviar.zip peca$data_arq_enviar.txt 1>/dev/null";
			system("$cmds", $retorno);

			$joga_ftp = "cd $log_dir && cp peca$data_arq_enviar.txt $ftp/$fabrica_nome-pecas$data_arq_enviar.ret";
			system("$joga_ftp");

			if ($retorno == 0) {

				require_once dirname(__FILE__) . '/../../class/email/mailer/class.phpmailer.php';

				$assunto = ucfirst($fabrica_nome) . utf8_decode(': Importa��o de pe�as ') . date('d/m/Y');

				$mail = new PHPMailer();
				$mail->IsHTML(true);
				$mail->From = 'helpdesk@telecontrol.com.br';
				$mail->FromName = 'Telecontrol';

				if (ENV == 'producao') {
					$mail->AddAddress('gilberto_saito@yanmar.com');
					$mail->AddAddress('amarildo@yanmar.com.br');
				} else {
					$mail->AddAddress(DEV_EMAIL);
				}

				$mail->Subject = $assunto;
				$mail->Body = "Segue anexo arquivo de pe�as importado na rotina...<br/><br/>";
				$mail->AddAttachment($log_dir . '/peca' . $data_arq_enviar . '.zip', 'peca' . $data_arq_enviar . '.zip');

				if (!$mail->Send()) {
					echo 'Erro ao enviar email: ' , $mail->ErrorInfo;
				} else {
					unlink($log_dir . '/peca' . $data_arq_enviar . '.txt');
					unlink($log_dir . '/peca' . $data_arq_enviar . '.zip');
				}

			} else {
				echo 'Erro ao compactar arquivo de log: ' , $retorno;
			}
		}

		if (filesize($err_log) > 0) {
			system("cd $log_dir && zip -r importa-peca-err-$now.zip importa-peca-err-$now.log 1>/dev/null", $retorno);

			if ($retorno == 0) {

				require_once dirname(__FILE__) . '/../../class/email/mailer/class.phpmailer.php';

				$assunto = ucfirst($fabrica_nome) . utf8_decode(': Erros na importa��o de pe�as ') . date('d/m/Y');

				$mail = new PHPMailer();
				$mail->IsHTML(true);
				$mail->From = 'helpdesk@telecontrol.com.br';
				$mail->FromName = 'Telecontrol';

				if (ENV == 'producao') {
					#$mail->AddAddress('gilberto_saito@yanmar.com');
					#$mail->AddAddress('amarildo@yanmar.com.br');
				} else {
					$mail->AddAddress(DEV_EMAIL);
				}

				$mail->Subject = $assunto;
				$mail->Body = "Segue anexo log de erro na importa��o de pe�as...<br/><br/>";
				$mail->AddAttachment($log_dir . '/importa-peca-err-' . $now . '.zip', 'importa-peca-err-' . $now . '.zip');

				if (!$mail->Send()) {
					echo 'Erro ao enviar email: ' , $mail->ErrorInfo;
				} else {
					unlink($log_dir . '/importa-peca-err-' . $now . '.zip');
				}

			} else {
				echo 'Erro ao compactar arquivo de log de erros: ' , $retorno;
			}
		}

		$data_arq_process = date('Ymd');
		system("mv $arquivo /tmp/$fabrica_nome/telecontrol-peca-$data_arq_process.txt");

	}

} catch (Exception $e) {
	echo $e->getMessage();
}
