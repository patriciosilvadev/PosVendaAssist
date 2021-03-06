<?php
	define('APP','Importa Pedido Cancelado - makita'); // Nome da rotina, para ser enviado por e-mail
	define('ENV','producao'); // Alterar para produ��o ou algo assim

	try {		
		include dirname(__FILE__) . '/../../dbconfig.php';
		include dirname(__FILE__) . '/../../includes/dbconnect-inc.php';
		require dirname(__FILE__) . '/../funcoes.php';

		$fabrica     = 42;
		$data 		 = date('d-m-Y');
		
		$phpCron = new PHPCron($fabrica, __FILE__); 
		$phpCron->inicio();

		$vet['fabrica'] = 'makita';
		$vet['tipo']    = 'importa-pedido';
		$vet['dest']    = ENV == 'testes' ? 'ronald.santos@telecontrol.com.br' : 'helpdesk@telecontrol.com.br';
		$vet['log']     = 1;

		$file = 'telecontrol-pedido-cancelado.txt';

		$dir 	= '/www/cgi-bin/makita/entrada';
		#$dir = 'entrada';

		$fp = fopen( $dir . '/' . $file, "r+" );

		if ( !is_resource($fp) ) {	
			throw new Exception ('Arquivo ' . $dir . '/' . $file . ' nao Encontrado !');
		}

		$sql = "DROP TABLE IF EXISTS ".$vet['fabrica']."_cancelado;

				CREATE TABLE ".$vet['fabrica']."_cancelado 
				(cnpj text, txt_pedido text, txt_pedido_item text, txt_peca text, txt_data text, motivo text, txt_qtde text);

				COPY ".$vet['fabrica']. "_cancelado FROM stdin";

		$res = pg_query($con,$sql);
		$msg_erro = pg_errormessage($con);

		while (!feof($fp)) {
			//remove quebra de linha do arquivo, para nao dar problema no pg_put_line - HD 824912
			$buffer[] = preg_replace( '/\n|\r/', '', fgets($fp) );
		        $msg_erro .= pg_errormessage($con);
	        }
		$i = 0;
		foreach($buffer as $linha) {
			// Verifica se nao eh a ultima linha e insere quebra de linha padrao do server
			if ( count($buffer) - ($i +1) > 0 ) {
				$linha .= PHP_EOL;
			}
			pg_put_line($con, $linha);
			$i++;
		}

	    if ( !empty($msg_erro) ) {
			throw new Exception($msg_erro);
		}

	    pg_put_line($con, "\\." . PHP_EOL);
		$msg_erro .= pg_last_error($con);
  		pg_end_copy($con);
		$msg_erro .= pg_last_error($con);


		if ( !empty($msg_erro) ) {
        	throw new Exception($msg_erro);
		}
	    
	    fclose($fp);
		
		$sql = "UPDATE ".$vet['fabrica']."_cancelado SET 
				cnpj        	= trim (cnpj)       		,
				txt_pedido  	= trim (txt_pedido) 		,
				txt_peca    	= trim (txt_peca)   		,
				txt_pedido_item = trim(txt_pedido_item)		,
				txt_data    	= trim (txt_data)   		,
				motivo      	= trim (motivo)     		,
				txt_qtde    	= trim (txt_qtde);

				UPDATE ".$vet['fabrica']."_cancelado SET txt_data = substr(txt_data,1,4) || '-' || substr(txt_data,5,2) || '-' || substr(txt_data,7,2);

				ALTER TABLE ".$vet['fabrica']."_cancelado ADD COLUMN qtde FLOAT;
				ALTER TABLE ".$vet['fabrica']."_cancelado ADD COLUMN pedido INT4;
				ALTER TABLE ".$vet['fabrica']."_cancelado ADD COLUMN pedido_item INT4;
				ALTER TABLE ".$vet['fabrica']."_cancelado ADD COLUMN peca INT4;
				ALTER TABLE ".$vet['fabrica']."_cancelado ADD COLUMN data DATE;
				ALTER TABLE ".$vet['fabrica']."_cancelado ADD COLUMN posto INT4;


				UPDATE ".$vet['fabrica']."_cancelado SET data = txt_data::date, qtde = txt_qtde::numeric;";

		$res = pg_query($con,$sql);
		$msg_erro = pg_last_error($con);
			
		if ( !empty($msg_erro) ) {
			throw new Exception($msg_erro);
		}

		$sql = "UPDATE ".$vet['fabrica']."_cancelado SET posto = tbl_posto.posto
				FROM   tbl_posto 
				JOIN tbl_posto_fabrica ON tbl_posto.posto = tbl_posto_fabrica.posto AND tbl_posto_fabrica.fabrica = $fabrica
				WHERE  ".$vet['fabrica']."_cancelado.cnpj = tbl_posto.cnpj";

		$res = pg_query($con,$sql);
		$msg_erro = pg_last_error($con);

		if ( !empty($msg_erro) ) {
			throw new Exception($msg_erro);
		}

		$sql = "DELETE FROM ".$vet['fabrica']."_cancelado where txt_pedido ~ E'\\\D';
		UPDATE ".$vet['fabrica']."_cancelado SET pedido = tbl_pedido.pedido
				FROM   tbl_pedido
				WHERE ".$vet['fabrica']."_cancelado.txt_pedido::integer = tbl_pedido.pedido
				AND   (".$vet['fabrica']."_cancelado.posto     = tbl_pedido.posto OR ".$vet['fabrica']."_cancelado.posto = tbl_pedido.distribuidor)
				AND   tbl_pedido.fabrica = $fabrica 
				AND   length(trim(txt_pedido)) > 0";
		
		$res = pg_query($con,$sql);
		$msg_erro = pg_last_error($con);

		if ( !empty($msg_erro) ) {
			throw new Exception($msg_erro);
		}

		$sql = "UPDATE ".$vet['fabrica']."_cancelado SET pedido_item = tbl_pedido_item.pedido_item
				FROM   tbl_pedido_item
				WHERE ".$vet['fabrica']."_cancelado.txt_pedido_item::integer = tbl_pedido_item.pedido_item
				AND length(trim(txt_pedido_item)) > 0";
		
		$res = pg_query($con,$sql);
		$msg_erro = pg_last_error($con);

		if ( !empty($msg_erro) ) {
			throw new Exception($msg_erro);
		}

		$sql = "UPDATE ".$vet['fabrica']."_cancelado SET peca = tbl_peca.peca
				FROM tbl_peca
				WHERE ".$vet['fabrica']."_cancelado.txt_peca = tbl_peca.referencia
				AND   tbl_peca.fabrica = $fabrica";

		$res = pg_query($con,$sql);
		$msg_erro = pg_errormessage($con);

		if ( !empty($msg_erro) ) {
			throw new Exception($msg_erro);
		}

		$sql = "UPDATE ".$vet['fabrica']."_cancelado SET pedido_item = tbl_pedido_item.pedido_item
				FROM   tbl_pedido_item
				WHERE ".$vet['fabrica']."_cancelado.peca = tbl_pedido_item.peca 
				AND ".$vet['fabrica']."_cancelado.pedido = tbl_pedido_item.pedido
				AND length(trim(txt_pedido_item)) = 0";
		
		$res = pg_query($con,$sql);
		$msg_erro = pg_last_error($con);

		if ( !empty($msg_erro) ) {
			throw new Exception($msg_erro);
		}


		$sql = "DELETE FROM ".$vet['fabrica']."_cancelado WHERE txt_qtde IS NULL;

				DROP TABLE IF EXISTS ".$vet['fabrica']."_cancelado_sem_posto;

				SELECT * INTO ".$vet['fabrica']."_cancelado_sem_posto FROM ".$vet['fabrica']."_cancelado WHERE posto IS NULL;

				DELETE FROM ".$vet['fabrica']."_cancelado WHERE posto IS NULL;

				DROP TABLE IF EXISTS  ".$vet['fabrica']."_cancelado_sem_peca;

				SELECT * INTO ".$vet['fabrica']."_cancelado_sem_peca FROM ".$vet['fabrica']."_cancelado WHERE peca IS NULL;

				DELETE FROM ".$vet['fabrica']."_cancelado WHERE peca IS NULL;

				DROP TABLE IF EXISTS  ".$vet['fabrica']."_cancelado_sem_pedido;

				SELECT * INTO ".$vet['fabrica']."_cancelado_sem_pedido FROM ".$vet['fabrica']."_cancelado WHERE pedido IS NULL;

				DELETE FROM ".$vet['fabrica']."_cancelado WHERE pedido IS NULL;";

		$res = pg_query($con,$sql);
		$msg_erro = pg_errormessage($con);

		if ( !empty($msg_erro) ) {
			throw new Exception($msg_erro);
		}

		$sql = "DELETE FROM ".$vet['fabrica']."_cancelado USING tbl_pedido_cancelado
				WHERE ".$vet['fabrica']."_cancelado.pedido = tbl_pedido_cancelado.pedido 
				AND  ".$vet['fabrica']."_cancelado.pedido_item = tbl_pedido_cancelado.pedido_item 
				AND ".$vet['fabrica']."_cancelado.data = tbl_pedido_cancelado.data 
				AND tbl_pedido_cancelado.fabrica = $fabrica;
				BEGIN TRANSACTION;";

		$res = pg_query($con,$sql);
		$msg_erro = pg_errormessage($con);

		if ( !empty($msg_erro) ) {
			throw new Exception($msg_erro);
		}

		$sql = "INSERT INTO tbl_pedido_cancelado (fabrica, data, posto, pedido, peca, qtde, motivo, pedido_item) 
				(SELECT distinct $fabrica,data, posto, pedido, peca, qtde, motivo, pedido_item FROM ".$vet['fabrica']."_cancelado )";

		$res = pg_query($con,$sql);
		$msg_erro = pg_errormessage($con);

		if ( !empty($msg_erro) ) {
			throw new Exception($msg_erro);
		}

		$sql = "SELECT 
				fn_atualiza_pedido_item_cancelado (	tbl_pedido_item.peca, 
													tbl_pedido_item.pedido, 
													tbl_pedido_item.pedido_item, 
													((tbl_pedido_item.qtde_cancelada + ".$vet['fabrica']."_cancelado.qtde)::integer))
				FROM ".$vet['fabrica']."_cancelado
				JOIN tbl_pedido_item ON tbl_pedido_item.pedido = ".$vet['fabrica']."_cancelado.pedido AND tbl_pedido_item.peca   = ".$vet['fabrica']."_cancelado.peca AND tbl_pedido_item.pedido_item = " . $vet['fabrica'] . "_cancelado.pedido_item;
				
				SELECT fn_atualiza_status_pedido($fabrica, ".$vet['fabrica']."_cancelado.pedido ) FROM ".$vet['fabrica']."_cancelado;";

		$res = pg_query($con,$sql);
		$msg_erro = pg_errormessage($con);

		if ( !empty($msg_erro) ) {
			pg_query($con,"ROLLBACK");
			throw new Exception($msg_erro);
		}

		pg_query($con,"COMMIT");
		Log::log2($vet, APP . ' - Executado com Sucesso - ' . date('d-m-Y H:i:s') . PHP_EOL);

		system ("mv $dir/$file /tmp/".$vet['fabrica']."/telecontrol-pedido-cancelado-$data.txt");
		
		$phpCron->termino();

	}
	catch (Exception $e) {
		$msg = 'Script: '.__FILE__.'<br />Erro na linha ' . $e->getLine() . ':<br />' . $e->getMessage();
		Log::envia_email($vet,APP, $msg );

	}
