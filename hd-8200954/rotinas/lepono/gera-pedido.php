<?php

try {
    error_reporting(0);
    /*
    * Includes
    */

    include dirname(__FILE__) . '/../../dbconfig.php';
    include dirname(__FILE__) . '/../../includes/dbconnect-inc.php';
    require dirname(__FILE__) . '/../funcoes.php';

    include dirname(__FILE__) . '/../../classes/Posvenda/Fabrica.php';
    include dirname(__FILE__) . '/../../classes/Posvenda/Os.php';
    include dirname(__FILE__) . '/../../classes/Posvenda/Pedido.php';

    /*
    * Defini��o
    */
    date_default_timezone_set('America/Sao_Paulo');
    $fabrica = 184;
    $data = date('d-m-Y');

    $env = "producao";
    #$env = "teste";

    /*
    * A variavel $param defire se o pedido vai ser por posto ou OS, sendo o padr�o pedido por posto
    */

    $param = "posto"; /* posto | os */

    /*
    * Log
    */
    $logClass = new Log2();
    $logClass->adicionaLog(array("titulo" => "Log erro - Gera��o de Pedidos Lepono")); // Titulo
    if ($env == 'producao' ) {
        $logClass->adicionaEmail("helpdesk@telecontrol.com.br");
    } else {
        $logClass->adicionaEmail("felipe.marttos@telecontrol.com.br");
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
    $os_garantia = $osClass->getOsGarantia($param);

    if(empty($os_garantia)){
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
    * Resgata a condi��o da Fabrica
    */
    $tipo_pedido = $pedidoClass->getTipoPedidoGarantia();

    for ($i = 0; $i < count($os_garantia); $i++) {

        try {

            $posto = $os_garantia[$i]["posto"];

            if($pedidoClass->verificaPostoInterno($posto) == true){
                continue;
            }

            if($param == "posto"){
                $os_pedido_posto = $osClass->getOsPosto($posto);
            }
            
            if (count($os_pedido_posto) == 0) {
                continue;
            }
            /*
            * Begin
            */
            $pedidoClass->_model->getPDO()->beginTransaction();
            $dados = array(
                "posto"         => $posto,
                "tipo_pedido"   => $tipo_pedido,
                "condicao"      => "'".$condicao."'",
                "fabrica"       => $fabrica,
                "status_pedido" => 1,
                "finalizado"    => "'".date("Y-m-d H:i:s")."'"
            );

            /*
            * Grava o Pedido
            */
            $pedidoClass->grava($dados);

            $pedido = $pedidoClass->getPedido();

            /* Pedido por Posto */
            if($param == "posto"){

                for ($j = 0; $j < count($os_pedido_posto); $j++) {

                    $os = $os_pedido_posto[$j]["os"];
                    $dadosItens = array();

                    /**
                     * Pega as pe�as da OS
                     */
                    $osClass = new \Posvenda\Os($fabrica, $os);

                    $pecas = $osClass->getPecasPedidoGarantia();

                    foreach ($pecas as $key => $peca) {

                        unset($dadosItens);

                        /*
                        * Insere o Pedido Item
                        */
                        $preco = $pedidoClass->getPrecoPecaGarantia($peca["peca"], $os);

                        $dadosItens[] = array(
                            "pedido"            => (int)$pedido,
                            "peca"              => $peca["peca"],
                            "qtde"              => $peca["qtde"],
                            "qtde_faturada"     => 0,
                            "qtde_cancelada"    => 0,
                            "preco"             => $preco,
                            "total_item"        => $preco * $peca["qtde"]
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

                }

            /* Pedido por OS */
            }else if(empty($param) or $param == "os"){

                $os = $os_garantia[$i]["os"];
                $dadosItens = array();

                /**
                 * Pega as pe�as da OS
                 */
                $osClass = new \Posvenda\Os($fabrica, $os);

                $pecas = $osClass->getPecasPedidoGarantia();

                foreach ($pecas as $key => $peca) {

                    unset($dadosItens);

                    /*
                    * Insere o Pedido Item
                    */
                    $preco = $pedidoClass->getPrecoPecaGarantia($peca["peca"], $os);
                    $dadosItens[] = array(
                        "pedido"            => (int)$pedido,
                        "peca"              => $peca["peca"],
                        "qtde"              => $peca["qtde"],
                        "qtde_faturada"     => 0,
                        "qtde_cancelada"    => 0,
                        "preco"             => $preco,
                        "total_item"        => $preco * $peca["qtde"]
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

            }

            $pedidoClass->finaliza($pedido);

            /*
            * Commit
            */
            $pedidoClass->_model->getPDO()->commit();

        } catch(Exception $e) {
            $pedidoClass->_model->getPDO()->rollBack();

            $msg_erro[] = $e->getMessage();

            continue;
        }
    }

    if(!empty($msg_erro)){

        $logClass->adicionaLog(implode("<br />", $msg_erro));
        $logClass->enviaEmails();

        $fp = fopen("tmp/{$fabrica_nome}/pedidos/log-erro.text", "a");
        fwrite($fp, "Data Log: " . date("d/m/Y") . "\n");
        fwrite($fp, implode("\n", $msg_erro));
        fclose($fp);

    }

    $phpCron->termino();

} catch (Exception $e) {
    echo $e->getMessage();
}
