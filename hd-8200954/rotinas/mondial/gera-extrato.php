<?php

error_reporting(E_ALL);

try {

	include dirname(__FILE__) . '/../../dbconfig.php';
	include dirname(__FILE__) . '/../../includes/dbconnect-inc.php';
	require dirname(__FILE__) . '/../funcoes.php';
    include dirname(__FILE__) . '/../../classes/Posvenda/Extrato.php';
    include dirname(__FILE__) . '/../../os_cadastro_unico/fabricas/151/classes/ExtratoMondial.php';

    /*
     * Defini��es
     */
	$fabrica 		= 151;
	$dia_mes     	= date('d');
	$dia_extrato 	= date('Y-m-d H:i:s');

    /*
     * Cron Class
     */
	$phpCron = new PHPCron($fabrica, __FILE__);
	$phpCron->inicio();

    /*
     * Log Class
     */
    $logClass = new Log2();
    $logClass->adicionaLog(array("titulo" => "Log erro Gera��o de Extrato - Mondial")); // Titulo
	$logClass->adicionaEmail("william.brandino@telecontrol.com.br");

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

    if (empty($os_posto)) {
        exit;
    }

    /**
     * Utiliza LGR
     */
    $usa_lgr = false;

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
             * Regras Extrato Mondial
             */
            $classRegrasExtratro = new RegrasExtrato($classExtrato, $fabrica);
            // $classRegrasExtratro->run();

            /*
             * Regras Mondial
             */
            $status_regra = $classRegrasExtratro->verificaRegraPosto($posto);

            if ($status_regra != false) {

                $status_regra = $classRegrasExtratro->recalcularExtrato($status_regra, $qtde, $posto);

            }

            // throw new Exception($status_regra);

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
            if ($usa_lgr == true) {
                $classExtrato->verificaLGR($extrato, $posto, $data_15);
            }

            /*
             * Commit
             */
            $classExtrato->_model->getPDO()->commit();

        } catch (Exception $e) {

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
    if (!empty($msg_erro)) {

        $logClass->adicionaLog($msg_erro);

        if ($logClass->enviaEmails() == "200") {
          echo "Log de erro enviado com Sucesso!";
        } else {
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

