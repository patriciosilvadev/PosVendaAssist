<?php

error_reporting(E_ALL ^ E_NOTICE);

$fabrica_nome = "masteraudio";

define('APP', 'Exporta Pedido - '.$fabrica_nome);

try {

    include dirname(__FILE__) . '/../../dbconfig.php';
    include dirname(__FILE__) . '/../../includes/dbconnect-inc.php';
    include dirname(__FILE__) . '/../funcoes.php';
    include dirname(__FILE__) . '/../../class/log/log.class.php';

    $login_fabrica = 126;
    $fabrica_nome  = 'masteraudio';
    
    $phpCron = new PHPCron($login_fabrica, __FILE__); 
    $phpCron->inicio();

    $vet['fabrica'] = $fabrica_nome;
    $vet['tipo']    = 'exporta-pedido';
    $vet['dest']    = array('helpdesk@telecontrol.com.br');
    $vet['log']     = 1;

     /* Log */
    $log = new Log();

    $log->adicionaLog(array("titulo" => "Log erro Exporta��o Pedidos Master �udio")); // Titulo
    $log->adicionaEmail("helpdesk@telecontrol.com.br");

    $sql = "SELECT TO_CHAR(tbl_pedido.data,'YYYYMMDD')      AS emissao,
                   tbl_posto.cnpj                           AS cnpj,
                   LPAD(tbl_pedido.pedido::text,9,'0')      AS pedido,
                   UPPER(tbl_tipo_pedido.codigo)            AS tipo_pedido ,
                   LPAD(tbl_condicao.codigo_condicao,5,' ') AS condicao,
                   SUBSTR(tbl_condicao.frete,1,1)           AS tipo_frete
              FROM tbl_pedido
              JOIN tbl_posto_fabrica ON tbl_pedido.posto    = tbl_posto_fabrica.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
              JOIN tbl_posto         ON tbl_posto.posto     = tbl_pedido.posto
              JOIN tbl_tipo_pedido   USING (tipo_pedido)
              JOIN tbl_tabela        ON tbl_pedido.tabela   = tbl_tabela.tabela
              JOIN tbl_condicao      ON tbl_pedido.condicao = tbl_condicao.condicao
             WHERE tbl_pedido.fabrica          = $login_fabrica
               AND tbl_pedido.recebido_fabrica IS NULL
               AND tbl_pedido.posto            <> 6359
               AND tbl_pedido.status_pedido    <> 14
               AND tbl_pedido.finalizado       NOTNULL
               AND (tbl_pedido.exportado IS NULL OR (tbl_pedido.exportado NOTNULL AND tbl_pedido.recebido_fabrica IS NULL))";
    $res      = pg_query($con, $sql);
    $numrows  = pg_num_rows($res);
    $msg_erro = pg_errormessage($con);
    $data     = date('Y-m-d');

    if (!empty($msg_erro)) {
        $log->adicionaLog("Erro ao exportar Pedidos");
        $log->adicionaLog("linha");
        throw new Exception($msg_erro);
    }

    if ($numrows) {
        $dir = "/tmp/$fabrica_nome/pedidos";

        if (!is_dir($dir)) {

            if (!mkdir($dir)) {
                $log->adicionaLog("Erro ao criar diret�rio do fabricante.");
                $log->adicionaLog("linha");
                throw new Exception('Erro ao criar diret�rio do fabricante.'."\n");
            }

            if (!chmod($dir, 0777)) {
                $log->adicionaLog("Erro ao dar permiss�o ao diret�rio.");
                $log->adicionaLog("linha");
                throw new Exception('Erro ao dar permiss�o ao diret�rio.'."\n");
            }

        }

        $file = $dir.'/pedido.txt';
        $fp   = fopen($file, 'w');

        if (!is_resource($fp)) {
            $log->adicionaLog("Erro ao criar arquivo de exporta��o.");
            $log->adicionaLog("linha");
            throw new Exception('Erro ao criar arquivo de exporta��o.'."\n");

        }

        for ($i = 0; $i < $numrows; $i++) {

            $pedido      = pg_fetch_result($res, $i, 'pedido');
            $emissao     = pg_fetch_result($res, $i, 'emissao');
            $cnpj        = pg_fetch_result($res, $i, 'cnpj');
            $tipo_pedido = pg_fetch_result($res, $i, 'tipo_pedido');
            $condicao    = pg_fetch_result($res, $i, 'condicao');
            $tipo_frete  = pg_fetch_result($res, $i, 'tipo_frete');

            /**
             *
             *  Layout:
             *
             *  Emissao | CNPJ | Tipo Pedido | Pedido | Condicao | Tipo Frete
             *
             */

            fwrite($fp, '01');
            fwrite($fp, $emissao);
            fwrite($fp, $cnpj);
            fwrite($fp, $tipo_pedido);
            fwrite($fp, $pedido);
            fwrite($fp, $condicao);
            fwrite($fp, $tipo_frete);
            fwrite($fp, "\r\n");

            $sql_pecas = "SELECT LPAD(tbl_pedido_item.pedido_item::text,9,'0')         AS pedido_item,
                                 LPAD(tbl_pedido.pedido::text,9,'0')                   AS pedidoX,
                                 LPAD(tbl_peca.referencia,10,'0')                      AS peca_referencia,
                                 tbl_pedido_item.qtde - tbl_pedido_item.qtde_cancelada AS qtde2,
                                 REPLACE(REPLACE(LPAD(TO_CHAR(tbl_pedido_item.qtde - tbl_pedido_item.qtde_cancelada,'99999.99'),10,'0'),'.',','),' ','0') AS qtde ,
                                 REPLACE(LPAD(TRIM(TO_CHAR(tbl_pedido_item.preco,'999999.99')),10,'0'),'.',',') AS preco ,
                                 LPAD(tbl_os_produto.os::text,9,'0')                   AS os,
         LPAD(tbl_os_item.os_item::text,9,'0')                 AS os_item,
         tbl_os_produto.produto, 
         tbl_peca.peca, 
        (SELECT LPAD(tbl_marca.codigo_marca::text,2,'00') as marca
                                    FROM tbl_lista_basica
                                    JOIN tbl_produto ON tbl_lista_basica.produto = tbl_produto.produto AND fabrica_i = $login_fabrica
                                    JOIN tbl_marca   ON tbl_produto.marca        = tbl_marca.marca
                                   WHERE tbl_lista_basica.fabrica = $login_fabrica
           and tbl_lista_basica.peca = tbl_peca.peca
           and tbl_lista_basica.produto = tbl_os_produto.produto  LIMIT 1) AS MARCA
                            FROM tbl_pedido
                            JOIN tbl_pedido_item USING(pedido)
                            JOIN tbl_peca        USING(peca)
                       LEFT JOIN tbl_os_item     ON tbl_os_item.pedido_item = tbl_pedido_item.pedido_item
                       LEFT JOIN tbl_os_produto  USING(os_produto)
                           WHERE tbl_pedido.pedido = $pedido";


            $res_pecas = pg_query($con, $sql_pecas);
            $tot_pecas = pg_num_rows($res_pecas);
            $msg_erro  = pg_errormessage($con);

            if (!empty($msg_erro)) {
                $log->adicionaLog("Erro ao exportar o pedido {$pedido}.");
            }

            for ($x = 0; $x < $tot_pecas; $x++) {

                $peca_referencia = trim(pg_fetch_result($res_pecas, $x, 'peca_referencia'));
                $pedido_item     = trim(pg_fetch_result($res_pecas, $x, 'pedido_item'));
                $os_item         = trim(pg_fetch_result($res_pecas, $x, 'os_item'));
                $qtde2           = trim(pg_fetch_result($res_pecas, $x, 'qtde2'));
                $qtde            = trim(pg_fetch_result($res_pecas, $x, 'qtde'));
                $preco           = trim(pg_fetch_result($res_pecas, $x, 'preco'));
                $os              = trim(pg_fetch_result($res_pecas, $x, 'os'));
                $marca           = trim(pg_fetch_result($res_pecas, $x, 'marca'));

                if (empty($os_item)) {
                    $os_item = "000000000";
                }

                if (empty($os)) {
                    $os = "000000000";
                }

                fwrite($fp, '02');
                fwrite($fp, $peca_referencia);
                fwrite($fp, $qtde);
                fwrite($fp, $pedido);
                fwrite($fp, $pedido_item);
                fwrite($fp, $os);
                fwrite($fp, $os_item);
                fwrite($fp, $preco);
                fwrite($fp, $marca);
                fwrite($fp, "\r\n");

                $sql_up = "UPDATE tbl_pedido
                              SET exportado     = CURRENT_TIMESTAMP,
                                  status_pedido = 9

                            WHERE pedido        = $pedido
                              AND fabrica       = $login_fabrica
                              AND exportado     IS NULL ";

                $res_up   = pg_query($con, $sql_up);
                $msg_erro = pg_errormessage($con);

                if (!empty($msg_erro)) {
                    $log->adicionaLog("Erro ao exportar o item {$pedido_item} do pedido pedido {$pedido}.");
                }

            }

        }

        if (!empty($msg_erro)) {
            /*
            $msg = 'Script: '.__FILE__.'<br />' . $msg_erro;
            Log::envia_email($vet, APP, $msg);
            */
            if($log->enviaEmails() == "200"){
              echo "Log de erro enviado com Sucesso!";
            }else{
              echo $log->enviaEmails();
            }

        } else {

            Log::log2($vet, APP . ' - Executado com Sucesso - ' . date('d-m-Y H:i:s'));

        }

        fclose($fp);

        if (file_exists($file) and (filesize($file) > 0)) {

      date_default_timezone_set('America/Sao_Paulo');
      $data_arquivo = date('dmy');

      copy($file, $dir . '/pedido_' . $data_arquivo . '.txt');

        }

    }
	
	$phpCron->termino();

} catch (Exception $e) {

    $msg = 'Script: '.__FILE__.'<br />Erro na linha ' . $e->getLine() . ':<br />' . $e->getMessage();
    Log::envia_email($vet, APP, $msg);

}

