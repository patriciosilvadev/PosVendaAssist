<?php

error_reporting(E_ALL);

try {

	include dirname(__FILE__) . '/../../dbconfig.php';
	include dirname(__FILE__) . '/../../includes/dbconnect-inc.php';
	require dirname(__FILE__) . '/../funcoes.php';
    include dirname(__FILE__) . '/../../classes/Posvenda/Extrato.php';
    include dirname(__FILE__) . '/../../os_cadastro_unico/fabricas/94/classes/ExtratoEverest.php';

	/*
	* Defini��es
	*/
    $fabrica      = 94;
    $dia_mes      = date('d');
    $dia_extrato  = date('Y-m-d H:i:s');
    $fabrica_nome = "everest";

	#$dia_mes     = "27";
	#$dia_extrato = "2014-08-27 23:59:00";

	/*
	* Cron Class
	*/
	$phpCron = new PHPCron($fabrica, __FILE__);
	$phpCron->inicio();

	/*
	* Log Class
	*/
    $logClass = new Log2();
    $logClass->adicionaLog(array("titulo" => "Log erro Gera��o de Extrato - Everest")); // Titulo
	$logClass->adicionaEmail("rafael.macedo@telecontrol.com.br");

	/*
	* Extrato Class
	*/
    $classExtrato = new Extrato($fabrica);

    /*
	* Resgata o per�odo dos 15 dias
    */
    $data_15 = $classExtrato->getPeriodoDias(14, $dia_extrato);

    /*
    * Resgata a quantidade de OS por Posto
    */
    $os_posto = $classExtrato->getOsPosto($dia_extrato, $fabrica);

    if(empty($os_posto)){
    	exit;
    }

    /**
    * Utiliza LGR
    */
    $extratoEverest = new ExtratoEverest($classExtrato);

    $resultado_lgr = $extratoEverest->getValorMinimoLGR();

    if($resultado_lgr["success"]){
        $valor_minimo_lgr = $resultado_lgr["valor_minimo_lgr"];
        $usa_lgr          = true;
        unset($resultado_lgr);
    }

    /*
    * Mensagem de Erro
    */
    $msg_erro = "";
    $msg_erro_arq = "";

	for ($i = 0; $i < count($os_posto); $i++) {

		$posto 			= $os_posto[$i]["posto"];
		$nome 			= $os_posto[$i]["nome"];
		$codigo_posto 	= $os_posto[$i]["codigo_posto"];
		$qtde  			= $os_posto[$i]["qtde"];
		try {
            /*
            * Begin
            */
            $classExtrato->_model->getPDO()->beginTransaction();

			/*
            * Insere o Extrato para o Posto
            */
            $classExtrato->insereExtratoPosto($fabrica, $posto, $dia_extrato, $mao_de_obra = 0, $pecas = 0, $total = 0, $avulso = 0);

            /*
            * Resgata o numero do Extrato
            */
            $extrato = $classExtrato->getExtrato();

            /*
            * Insere lan�amentos avulsos para o Posto
            */
            $classExtrato->atualizaAvulsosPosto($fabrica, $posto, $extrato);

            /*
            * Relaciona as OSs com o Extrato
            */
            $classExtrato->relacionaExtratoOS($fabrica, $posto, $extrato, $dia_extrato);

            /*
            * Atualiza os valores avulso dos postos
            */
            $classExtrato->atualizaValoresAvulsos($fabrica);

            /*
            * Calcula o Extrato
            */
           
        	$total_extrato = $classExtrato->calcula($extrato);  
            /**
            * Verifica LGR
            */
            if($usa_lgr == true && $extratoEverest->verificarTotalPeca($extrato,$posto, $data_15, $valor_minimo_lgr)){
                $classExtrato->verificaLGR($extrato, $posto, $data_15);
            }

            /*
            * Libera o extrato
            */
            //$classExtrato->liberaExtrato($extrato);

			/*
            * Commit
            */
            // $classExtrato->_model->getPDO()->rollBack();
            $classExtrato->_model->getPDO()->commit();

		} catch (Exception $e){

			$msg_erro .= $e->getMessage()."<br />";
            $msg_erro_arq .= $msg_erro . " - SQL: " . $classExtrato->getErro();

            /*
            * Rollback
            */
            $classExtrato->_model->getPDO()->rollBack();

		}

	}

	/*
	* Erro
	*/
	if(!empty($msg_erro)){

        $logClass->adicionaLog($msg_erro);

        if($logClass->enviaEmails() == "200"){
          echo "Log de erro enviado com Sucesso!";
        }else{
          echo $logClass->enviaEmails();
        }

        $fp = fopen("tmp/{$fabrica_nome}/pedidos/log-erro.text", "a");
        fwrite($fp, "Data Log: " . date("d/m/Y") . "\n");
        fwrite($fp, $msg_erro_arq . "\n \n");
        fclose($fp);

    }
	/*
	* Cron T�rmino
	*/
	$phpCron->termino();

} catch (Exception $e) {
	echo $e->getMessage();
}

