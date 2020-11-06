<?php
/**
 *
 * gera-pedido.php
 *
 * Geração de pedidos de pecas com base na OS
 *
 * @author  Ronald Santos
 * @version 2012.11.22
 *
*/

error_reporting(E_ALL ^ E_NOTICE);
define('ENV','producao');  // producao Alterar para produção ou algo assim

try {

	include dirname(__FILE__) . '/../../dbconfig.php';
	include dirname(__FILE__) . '/../../includes/dbconnect-inc.php';
	require dirname(__FILE__) . '/../funcoes.php';
	include dirname(__FILE__) . '/../../class/email/mailer/class.phpmailer.php';

    $data['login_fabrica'] 	= 117;
    $data['fabrica_nome'] 	= 'elgin';
    $data['arquivo_log'] 	= 'gera-pedido-os';
    $data['log'] 			= 2;
    $data['arquivos'] 		= "/tmp";
    $data['data_sistema'] 	= Date('Y-m-d');
    $logs 					= array();
    $logs_erro				= array();
    $logs_cliente			= array();
    $erro 					= false;
	
	$fabrica = "117";
	$phpCron = new PHPCron($fabrica, __FILE__); 
	$phpCron->inicio();

    if (ENV == 'producao' ) {
		$data['dest'] 		= 'helpdesk@telecontrol.com.br';
    } else {
    	$data['dest'] 			= 'william.brandino@telecontrol.com.br';
    }

    extract($data);

    $arquivo_err = "{$arquivos}/{$fabrica_nome}/{$arquivo_log}-{$data_sistema}.txt";
    $arquivo_log = "{$arquivos}/{$fabrica_nome}/{$arquivo_log}-{$data_sistema}.log";
    system ("mkdir {$arquivos}/{$fabrica_nome}/ 2> /dev/null ; chmod 777 {$arquivos}/{$fabrica_nome}/" );    

    $sql = "SET DateStyle TO 'SQL,EUROPEAN';";
    $res = pg_query($con, $sql);
    if(pg_last_error($con)){
    	$logs_erro[] = $sql;
    	$logs[] = pg_last_error($con);
    	$erro   = true;
    }

    $sql = "SELECT to_char(current_date, 'd')::integer;";
    $res = pg_query($con, $sql);
    if(pg_last_error($con)){
    	$logs[] = $msg_erro = Date("Y-m-d H:i:s")." - Erro SQL: 'dia da semana'";
    	$logs_erro[] = $sql;
    	$logs[] = pg_last_error($con);
    	$erro   = true;
    	throw new Exception ($msg_erro);
    }else{
    	$dia_semana = pg_fetch_result($res, 0);
    }

    $sql = "SELECT tbl_os.os,
	    tbl_os.sua_os,
	    tbl_os.posto,
	    tbl_os.produto
	    INTO TEMP tmp_oss_aptas
	    FROM tbl_os
	    JOIN tbl_os_produto ON tbl_os.os = tbl_os_produto.os 
	JOIN tbl_os_item ON tbl_os_item.os_produto = tbl_os_produto.os_produto
	JOIN tbl_servico_realizado ON tbl_os_item.servico_realizado = tbl_servico_realizado.servico_realizado 
	AND tbl_servico_realizado.gera_pedido IS TRUE
	WHERE tbl_os.fabrica = $fabrica
	AND tbl_os.excluida IS NOT TRUE
	AND tbl_os.validada    IS NOT NULL
	AND tbl_os.troca_garantia       IS NULL
	AND tbl_os.troca_garantia_admin IS NULL
	AND tbl_os_item.pedido IS NULL
	AND tbl_servico_realizado.fabrica = $fabrica 
	AND tbl_os_item.fabrica_i = $fabrica";
    $res = pg_query($con,$sql);
    // ####################################################
	// INTERVENCAO KM REINCIDENTE
	// ####################################################
	$sql = "SELECT interv_reinc.os
			  INTO TEMP tmp_interv_reinc
			  FROM (
					SELECT ultima_reinc.os, (
							SELECT status_os
							  FROM tbl_os_status
							 JOIN tmp_oss_aptas ON tbl_os_status.os = tmp_oss_aptas.os
							 WHERE tbl_os_status.os             = ultima_reinc.os
							   AND tbl_os_status.fabrica_status = $fabrica
							   AND status_os IN ( 19,  67, 155, 139)
					 ORDER BY os_status DESC LIMIT 1) AS ultimo_reinc_status
					  FROM (
							SELECT DISTINCT tbl_os_status.os
							  FROM tbl_os_status
							 JOIN tmp_oss_aptas ON tbl_os_status.os = tmp_oss_aptas.os
							 WHERE tbl_os_status.fabrica_status = $fabrica
							   AND status_os IN ( 19,  67, 155, 139)
					  ) ultima_reinc) interv_reinc
			 WHERE interv_reinc.ultimo_reinc_status IN ( 67);";
    $res = pg_query($con, $sql);

	// ####################################################
	// INTERVENCAO NUMERO DE SERIE
	// ####################################################
	$sql = "SELECT interv_serie.os
			  INTO TEMP tmp_interv_serie
			  FROM (
					SELECT ultima_serie.os, (
							SELECT status_os
							  FROM tbl_os_status
							 JOIN tmp_oss_aptas ON tbl_os_status.os = tmp_oss_aptas.os
							 WHERE tbl_os_status.os             = ultima_serie.os
							   AND tbl_os_status.fabrica_status = $fabrica
							   AND status_os IN (62,64,81,155)
					 ORDER BY os_status DESC LIMIT 1) AS ultimo_serie_status
					  FROM (
							SELECT DISTINCT tbl_os_status.os
							  FROM tbl_os_status
							 JOIN tmp_oss_aptas ON tbl_os_status.os = tmp_oss_aptas.os
							 WHERE tbl_os_status.fabrica_status = $fabrica
							   AND status_os IN (62,64,81,155)
					  ) ultima_serie) interv_serie
			 WHERE interv_serie.ultimo_serie_status IN (62,81);";
    $res = pg_query($con, $sql);

    // ####################################################
	// INTERVENCAO LGI
	// ####################################################
    $sql = "  SELECT interv_lgi.os
				INTO TEMP tmp_interv_lgi
				FROM (
					  SELECT ultima_lgi.os, (
							  SELECT status_os
								FROM tbl_os_status
							   JOIN tmp_oss_aptas ON tbl_os_status.os = tmp_oss_aptas.os
							   WHERE tbl_os_status.os = ultima_lgi.os
								 AND tbl_os_status.fabrica_status = $fabrica
								 AND status_os IN (102,103,104)
					   ORDER BY os_status DESC LIMIT 1) AS ultimo_lgi_status
						FROM (
							  SELECT DISTINCT tbl_os_status.os
								FROM tbl_os_status
							   JOIN tmp_oss_aptas ON tbl_os_status.os = tmp_oss_aptas.os
							   WHERE tbl_os_status.fabrica_status = $fabrica
				 AND status_os IN (102,103,104)) ultima_lgi) interv_lgi
			   WHERE interv_lgi.ultimo_lgi_status IN (102,104);";
    $res = pg_query($con, $sql);

    $sql = "SELECT  
						tbl_os.posto        ,
						tbl_produto.linha   ,
						tbl_os_item.peca    ,
						tbl_os_item.os_item ,
						tbl_os_item.qtde    ,
						tbl_os.sua_os       
						INTO TEMP tmp_pedido_elgin
				FROM    tbl_os_item
				JOIN    tbl_servico_realizado ON tbl_servico_realizado.servico_realizado = tbl_os_item.servico_realizado AND tbl_servico_realizado.fabrica = $login_fabrica
				JOIN    tbl_os_produto USING (os_produto)
				JOIN    tbl_os                ON tbl_os.os = tbl_os_produto.os AND tbl_os.fabrica = $login_fabrica
				JOIN    tbl_posto      USING (posto)
				JOIN    tbl_produto          ON tbl_os.produto          = tbl_produto.produto and tbl_produto.fabrica_i = $login_fabrica
				JOIN    tbl_posto_fabrica    ON tbl_posto_fabrica.posto = tbl_os.posto AND tbl_posto_fabrica.fabrica = tbl_os.fabrica
				WHERE   tbl_os_item.pedido IS NULL
				AND     tbl_os.validada    IS NOT NULL
				AND     tbl_os.excluida    IS NOT TRUE
				AND     tbl_os_item.fabrica_i  = $login_fabrica
				AND     tbl_os.troca_garantia       IS NULL
				AND     tbl_os.troca_garantia_admin IS NULL
				AND     tbl_os.os NOT IN ( SELECT os FROM tmp_interv_reinc )
				AND     tbl_os.os NOT IN ( SELECT os FROM tmp_interv_serie )
				AND     tbl_os.os NOT IN ( SELECT os FROM tmp_interv_lgi )
				AND    (tbl_posto_fabrica.credenciamento = 'CREDENCIADO'
					OR  tbl_posto_fabrica.credenciamento = 'EM DESCREDENCIAMENTO')
				AND     tbl_servico_realizado.gera_pedido
				AND     tbl_servico_realizado.peca_estoque IS NOT TRUE;
				
				SELECT DISTINCT posto,linha from tmp_pedido_elgin ;

				";
    $resP = pg_query($con, $sql);
   
	if(pg_last_error($con)){
    	$logs[] = $msg_erro = Date("Y-m-d H:i:s")." - Erro SQL: 'Posto'";
    	$logs_erro[] = $sql;
    	$logs[] = pg_last_error($con);
    	$erro   = true;
    	throw new Exception ($msg_erro);
	}
    #Garantia 
	$sql = "select condicao from tbl_condicao where fabrica = ".$login_fabrica." and lower(descricao) = 'garantia';";
	$resultG = pg_query($con, $sql);
	if(pg_last_error($con)){
		$logs[] = $msg_erro = Date("Y-m-d H:i:s")." - Erro SQL: 'Condição Pagamento'";
		$logs_erro[] = $sql;
		$logs[] = pg_last_error($con);
		$erro = "*";
	}else{
		$condicao = pg_result($resultG,0,'condicao');
	}

	#Tipo_pedido
	$sql = "select tipo_pedido from tbl_tipo_pedido where fabrica = ".$login_fabrica." and lower(descricao) = 'garantia';";
	$resultP = pg_query($con, $sql);
	if(pg_last_error($con)){
		$logs[] = $msg_erro = Date("Y-m-d H:i:s")." - Erro SQL: 'Tipo Pedido'";
		$logs_erro[] = $sql;
		$logs[] = pg_last_error($con);
		$erro = "*";
	}else{
		$tipo_pedido = pg_result($resultP,0,'tipo_pedido');
	}

	for ($i=0; $i < pg_num_rows($resP); $i++) { 

		$posto  = pg_result($resP,$i,'posto');
		$linha  = pg_result($resP,$i,'linha');

		$erro = " ";
		$res = pg_query($con, "BEGIN TRANSACTION");

		#Tabela
		$sql = "select tabela from tbl_posto_linha where linha = $linha and posto = $posto ;";
		$resultP = pg_query($con, $sql);
		if(pg_num_rows($resultP) > 0) {
			if(pg_last_error($con)){
				$logs_erro[] = $sql."<br>".pg_last_error($con);
				$erro = '*';
			}else{
				$tabela = pg_result($resultP,0,'tabela');
			}
		}else{
			$logs_erro[] = 'Posto sem tabela cadastrada';
			$erro = '*';
		}


		$sql = "INSERT INTO tbl_pedido (
					posto        ,
					fabrica      ,
					condicao     ,
					tipo_pedido  ,
					status_pedido,
					tabela
				) VALUES (
					$posto      ,
					$login_fabrica    ,
					$condicao   ,
					$tipo_pedido,
					1           ,
					$tabela
				) RETURNING pedido;";
		$resultP = pg_query($con, $sql);
		if(pg_last_error($con)){
			$logs[] = $msg_erro = Date("Y-m-d H:i:s")." - Erro SQL: 'Pedido'";
			$logs_erro[] = $sql;
			$logs[] = pg_last_error($con);
			$erro = "*";
		}else{

			$pedido = pg_result($resultP,0,0);

			$sql_item = "SELECT  
						peca    ,
						qtde,
						os_item
							from 
						tmp_pedido_elgin 
						WHERE posto = $posto 
						AND linha   = $linha";

			$result2 = pg_query($con,$sql_item);

			for ($x=0; $x < pg_num_rows($result2); $x++) {
				$peca = pg_result($result2,$x,'peca');
				$qtde = pg_result($result2,$x,'qtde');
				$os_item = pg_result($result2,$x,'os_item');

				$sql = "INSERT INTO tbl_pedido_item (
						pedido,
						peca  ,
						qtde  ,
						qtde_faturada,
						qtde_cancelada
					) VALUES (
						$pedido,
						$peca  ,
						$qtde  ,
						0      ,
						0      ) RETURNING pedido_item";
				$resultX = pg_query($con,$sql);
				if(pg_last_error($con)){
					$logs[] = $msg_erro = Date("Y-m-d H:i:s")." - Erro SQL: 'Pedido Item'";
					$logs_erro[] = $sql;
					$logs[] = pg_last_error($con);
					$erro = "*";
				}else{
					$pedido_item = pg_result($resultX,0,0);

					$sql = "SELECT fn_atualiza_os_item_pedido_item($os_item,$pedido,$pedido_item,$login_fabrica)";
					$resultX = pg_query($con,$sql);
					if(pg_last_error($con)){
						$logs[] = $msg_erro = Date("Y-m-d H:i:s")." - Erro SQL: 'Atualiza OS Item'";
						$logs_erro[] = $sql;
						$logs[] = pg_last_error($con);
						$erro = "*";
					}
				}
			}

			$sql = "SELECT fn_pedido_finaliza($pedido,$login_fabrica)";
			$resultX = pg_query($con,$sql);
			if(pg_last_error($con)){
                		$erro_cliente = pg_last_error($con);
				$erro_cliente = preg_replace('/ERROR: /','',$erro_cliente);
				$erro_cliente = preg_replace('/CONTEXT:  .+\nPL.+/','',$erro_cliente);

				$logs[] = $erro_cliente; 
				$logs_erro[] = $erro_cliente;
				$logs[] = pg_last_error($con);
				$erro = "*";
			}
			
			if ($erro == "*") {
				$resultX = pg_query($con,"ROLLBACK TRANSACTION");
			}else{
				$sql_posto = "
				  SELECT 
					  tbl_posto.nome, 
					  tbl_posto_fabrica.codigo_posto
				  FROM tbl_posto
					  JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_posto.posto AND tbl_posto_fabrica.fabrica = {$login_fabrica} 
				  WHERE 
					  tbl_posto.posto = {$posto}
				  LIMIT 1";
				$res_posto = pg_query($con, $sql_posto);
				$codigo_posto = pg_fetch_result($res_posto, 0, 'codigo_posto');
				$nome_posto   = pg_fetch_result($res_posto, 0, 'nome');

				$logs[] = "SUCESSO => Posto: '{$codigo_posto} - {$nome_posto}' - Pedido {$pedido} gerado com sucesso!";

				$resultX = pg_query($con,"COMMIT TRANSACTION");
			}
		}
	}

    if(count($logs) > 0){
    	$file_log = fopen($arquivo_log,"w+");
        	fputs($file_log,implode("\r\n", $logs));
        fclose ($file_log);
    }

    //envia email para HelpDESK
    if($erro){
	    if(count($logs_erro) > 0){
	    	$file_log = fopen($arquivo_err,"w+");
	        	if(count($logs_erro) > 0){
	        		fputs($file_log,implode("\r\n", $logs_erro));
	        	}
	        fclose ($file_log);

		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->IsHTML();
		$mail->AddReplyTo("helpdesk@telecontrol.com.br", "Suporte Telecontrol");
		$mail->Subject = Date('d/m/Y')." - Erro ao gerar pedido ";
		$mail->Body = "Erro ao gerar pedido";
		$mail->AddAddress("helpdesk@telecontrol.com.br"); // HD-1762747
		if(file_exists($arquivo_err) AND filesize($arquivo_err) > 0)
		$mail->AddAttachment($arquivo_err);
		$mail->Send();
    	}
    }

    $phpCron->termino();

} catch (Exception $e) {
	$e->getMessage();
    $msg = "Arquivo: ".__FILE__."\r\n<br />linha: " . $e->getLine() . "\r\n<br />Descrição do erro: " . $e->getMessage() ."<hr /><br /><br />". implode("<br /><br />", $logs);

    Log::envia_email($data,Date('d/m/Y H:i:s')." - ELGIN - Erro na geração de pedido(gera-pedido.php)", $msg);
}?>