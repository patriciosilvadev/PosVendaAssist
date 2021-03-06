<?php

try {

    /*
    * Includes
    */

    include dirname(__FILE__) . '/../../dbconfig.php';
    include dirname(__FILE__) . '/../../includes/dbconnect-inc.php';
    require dirname(__FILE__) . '/../funcoes.php';

	if ($_serverEnvironment == "production") {
                define("ENV", "prod");
        } else {
                define("ENV", "dev");
        }

    include dirname(__FILE__) . '/../../classes/Posvenda/Fabrica.php';
    include dirname(__FILE__) . '/../../classes/Posvenda/Os.php';
    include dirname(__FILE__) . '/../../classes/Posvenda/Pedido.php';

    /*
    * Defini��o
    */
    date_default_timezone_set('America/Sao_Paulo');
    $fabrica = 143;
    $data = date('d-m-Y');

    /*if ($_serverEnvironment == "production") {
        define("ENV", "prod");
    } else {
        define("ENV"), "dev";
    }*/

    define("ENV", "prod");

    /* 
    * Log 
    */
    $logClass = new Log2();
    $logClass->adicionaLog(array("titulo" => "Log erro - Gera��o de Wacker Neuson")); // Titulo
    if (ENV == 'prod' ) {
        $logClass->adicionaEmail("vanilde.sartorelli@wackerneuson.com");
        $logClass->adicionaEmail("helpdesk@telecontrol.com.br");
    } else {
        $logClass->adicionaEmail("guilherme.curcio@telecontrol.com.br");
    }

    /* 
    * Cron 
    */
    $phpCron = new PHPCron($fabrica, __FILE__);
    $phpCron->inicio();

    /* 
    * Class F�brica 
    */
    $fabricaClass = new \Posvenda\Fabrica($fabrica);

    /*
    * Resgata o nome da Fabrica
    */
    $fabrica_nome = $fabricaClass->getNome();

    /*
    * Resgata as OSs em Garantia
    */
    $osClass = new \Posvenda\Os($fabrica);
    $os_garantia = $osClass->getOsGarantia();

    if(empty($os_garantia)){
		$phpCron->termino();
        exit;
    }

    /*
    * Mensagem de Erro
    */
    $msg_erro = array();

    $pedidoClass = new \Posvenda\Pedido($fabrica);

    /*
    * Resgata a condi��o da Fabrica
    */
    $condicao = $pedidoClass->getCondicaoGarantia();

    /*
    * Resgata o tipo de pedido da Fabrica
    */
    $tipo_pedido = $pedidoClass->getTipoPedidoGarantia();

    for ($i = 0; $i < count($os_garantia); $i++) {
        try {
            $os    = $os_garantia[$i]["os"];
            $posto = $os_garantia[$i]["posto"];

            if (ENV == "prod" && $posto == 6359) {
                continue;
            }

            /**
             * Pega as pe�as da OS
             */
            $osClass = new \Posvenda\Os($fabrica, $os);

            $pecas = $osClass->getPecasPedidoGarantia();

            if (!count($pecas)) {
                continue;
            } else {
                /*
                * Begin
                */
                $pedidoClass->_model->getPDO()->beginTransaction();

                $dados = array(
                    "posto"         => $posto,
                    "tipo_pedido"   => $tipo_pedido,
                    "condicao"      => $condicao,
                    "status_pedido" => '1',
                    "fabrica"       => $fabrica,
                    "finalizado"       => "'".date("Y-m-d H:i:s")."'"
                );
                
                /*
                * Grava o Pedido
                */
                $pedidoClass->grava($dados);

                $pedido = $pedidoClass->getPedido();

                foreach ($pecas as $key => $peca) {
				    unset($dadosItens);
                    /*
                    * Insere o Pedido Item
                    */
                    $dadosItens[] = array(
                        "pedido"            => (int)$pedido,
                        "peca"              => $peca["peca"],
                        "qtde"              => $peca["qtde"],
                        "qtde_faturada"     => 0,
                        "qtde_cancelada"    => 0,
                        "preco"             => $pedidoClass->getPrecoPecaGarantia($peca["peca"], $os)
                    );

                    $pedidoClass->gravaItem($dadosItens, $pedido);

                    /*
                    * Resgata o Pedido Item
                    */
                    $pedido_item = $pedidoClass->getPedidoItem();

                    /*
                    * Atualiza os Pedidos Item na OS Item
                    */
                    $pedidoClass->atualizaOsItemPedidoItem($peca["os_item"], $pedido, $pedido_item, $fabrica);
                }

                $pedidoClass->finaliza($pedido);

                /*
                * Commit
                */
                $pedidoClass->_model->getPDO()->commit();
            }
        } catch(Exception $e) {
            $pedidoClass->_model->getPDO()->rollBack();

            $msg_erro[] = $e->getMessage();

            continue;
        }
    }

    if(!empty($msg_erro)){

        $logClass->adicionaLog(implode("<br />", $msg_erro));

        if($logClass->enviaEmails() == "200"){
          echo "Log de erro enviado com Sucesso!";
        }else{
          $logClass->enviaEmails();
        }

        $fp = fopen("tmp/{$fabrica_nome}/pedidos/log-erro.text", "a");
        fwrite($fp, "Data Log: " . date("d/m/Y") . "\n");
        fwrite($fp, implode("\n", $msg_erro));
        fclose($fp);

    }

    $phpCron->termino();

} catch (Exception $e) {
    echo $e->getMessage();
}
