<?
include "dbconfig.php";
include "includes/dbconnect-inc.php";

include "autentica_admin.php";
$admin_privilegios="financeiro";

$sql = "SELECT posto_fabrica
        FROM tbl_fabrica
        WHERE fabrica = $login_fabrica ";
$res2 = pg_query ($con,$sql);
$posto_da_fabrica = pg_fetch_result ($res2,0,0);
//$posto_da_fabrica = '6359';

# Pesquisa pelo AutoComplete AJAX
$q = strtolower($_GET["q"]);
if (isset($_GET["q"])){
    $tipo_busca = $_GET["busca"];

    if (strlen($q)>2){
        $sql = "SELECT tbl_posto.cnpj, tbl_posto.nome, tbl_posto_fabrica.codigo_posto
                FROM tbl_posto
                JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_posto.posto
                WHERE tbl_posto_fabrica.fabrica = $login_fabrica ";

        if ($tipo_busca == "codigo"){
            $sql .= " AND tbl_posto_fabrica.codigo_posto = '$q' ";
        }else{
            $sql .= " AND UPPER(tbl_posto.nome) like UPPER('%$q%') ";
        }

        $res = pg_query($con,$sql);
        if (pg_num_rows ($res) > 0) {
            for ($i=0; $i<pg_num_rows ($res); $i++ ){
                $cnpj = trim(pg_fetch_result($res,$i,cnpj));
                $nome = trim(pg_fetch_result($res,$i,nome));
                $codigo_posto = trim(pg_fetch_result($res,$i,codigo_posto));
                echo "$cnpj|$nome|$codigo_posto";
                echo "\n";
            }
        }
    }
    exit;
}
if($_GET['ajax']=='sim') {
   $nota_fiscal    = $_GET['nota'];
   $nota_antiga    = $_GET['nota_antiga'];
   $login_posto    = $_GET['login_posto'];
   $sql = "SELECT nota_fiscal from tbl_faturamento  WHERE fabrica = $login_fabrica and distribuidor = $login_posto and nota_fiscal='$nota_fiscal'";
   $res = pg_query($con,$sql);
   if((pg_num_rows($res) > 0 )OR(pg_last_error($con) > 0)){
       echo "Erro";
   }
   else{
      $sql =  "UPDATE tbl_faturamento set nota_fiscal = $nota_fiscal  where fabrica = $login_fabrica and nota_fiscal = '$nota_antiga' " ;
      $res = pg_query($con,$sql);
      if (pg_last_error($con) > 0 ){
              echo "Erro";
      }else{
              echo "ok";
      }
   }
   exit;
}

function converte_data($date){
    $date = explode("/", $date);
    $date2 = $date[2].'-'.$date[1].'-'.$date[0];
    if (sizeof($date)==3)
        return $date2;
    else return false;
}

$msg_erro = "";

if (strlen($_POST["btnacao"]) > 0) $btnacao = trim(strtolower($_POST["btnacao"]));
if (strlen($_GET["btnacao"])  > 0) $btnacao = trim(strtolower($_GET["btnacao"]));

if (strlen($_POST["posto"]) > 0) $posto = $_POST["posto"];
if (strlen($_GET["posto"])  > 0) $posto = $_GET["posto"];

if (strlen($_POST["filtroTodos"]) > 0) $filtroTodos = $_POST["filtroTodos"];
if (strlen($_GET["filtroTodos"])  > 0) $filtroTodos = $_GET["filtroTodos"];

if (strlen($_POST["filtroObrigatorio"]) > 0) $filtroObrigatorio = $_POST["filtroObrigatorio"];
if (strlen($_GET["filtroObrigatorio"])  > 0) $filtroObrigatorio = $_GET["filtroObrigatorio"];

if (strlen($_POST["filtroNaoObrigatorio"]) > 0) $filtroNaoObrigatorio = $_POST["filtroNaoObrigatorio"];
if (strlen($_GET["filtroNaoObrigatorio"])  > 0) $filtroNaoObrigatorio = $_GET["filtroNaoObrigatorio"];

if (strlen($_POST["data_inicial"]) > 0) $data_inicial = $_POST["data_inicial"];
if (strlen($_GET["data_inicial"])  > 0) $data_inicial = $_GET["data_inicial"];

if (strlen($_POST["data_final"]) > 0) $data_final = $_POST["data_final"];
if (strlen($_GET["data_final"])  > 0) $data_final = $_GET["data_final"];

if (strlen($_POST["filtroRecebidos"]) > 0) $filtroRecebidos = $_POST["filtroRecebidos"];
if (strlen($_GET["filtroRecebidos"])  > 0) $filtroRecebidos = $_GET["filtroRecebidos"];

if (strlen($_POST["nota_devolucao"]) > 0) $nota_devolucao = $_POST["nota_devolucao"];
if (strlen($_GET["nota_devolucao"])  > 0) $nota_devolucao = $_GET["nota_devolucao"];

if (strlen($_POST["posto_devolucao"]) > 0) $posto_devolucao = $_POST["posto_devolucao"];
if (strlen($_GET["posto_devolucao"])  > 0) $posto_devolucao = $_GET["posto_devolucao"];

if (strlen($_POST["nf"]) > 0) $nf = $_POST["nf"];
if (strlen($_GET["nf"])  > 0) $nf = $_GET["nf"];

if (strlen($_POST["data_nf_envio"]) > 0) $data_nf_envio = $_POST["data_nf_envio"];
if (strlen($_GET["data_nf_envio"])  > 0) $data_nf_envio = $_GET["data_nf_envio"];

if (strlen($_POST["posto_devendo"]) > 0) $posto_devendo = $_POST["posto_devendo"];
if (strlen($_GET["posto_devendo"])  > 0) $posto_devendo = $_GET["posto_devendo"];

if (strlen($_GET["devolucao"])  > 0) $devolucao = $_GET["devolucao"];

if (strlen($_GET["pendencias_postos"])  > 0) $pendencias_postos = $_GET["pendencias_postos"];

$posto_nome   = $_POST['posto_nome'];
if (strlen($_GET['posto_nome']) > 0) $posto_nome = $_GET['posto_nome'];
//if (strlen($_GET['razao']) > 0) $posto_nome = $_GET['razao'];

$posto_codigo = $_POST['posto_codigo'];
if (strlen($_GET['posto_codigo']) > 0) $posto_codigo = $_GET['posto_codigo'];


$layout_menu = "financeiro";
$title = "Consulta Notas de Devolu��o";

$agrupar = "true";

/**
 * Enviar os e-mails das notas de devolucao para os postos da Colormaq
 * HD 107532
 *
 * @author Augusto Pascutti <augusto.hp@gmail.com>
 */
if ( $login_fabrica == 50 && isset($_POST['enviar_emails']) ) {

    /**
     * Arquivo contento os dados do e-mail
     */
    define('COLORMAQ_ARQUIVO_EMAIL_DEVOLUCAO','/var/www/assist/www/admin/documentos/colormaq_email_devolucao.txt');

    /**
     * Retorna o email de nf de decolucao da colormaq.
     *
     * @return array('email_de'=>'string',
     *               'assunto'=>'string',
     *               'mensagem'=>'string')
     */
    function colormaq_retorna_email_devolucao() {
        global $login_fabrica;

        if ( $login_fabrica != 50 || ! file_exists(COLORMAQ_ARQUIVO_EMAIL_DEVOLUCAO) ) { return array(); }
        $handler  = fopen(COLORMAQ_ARQUIVO_EMAIL_DEVOLUCAO,'r');
        if ( ! is_resource($handler) ) { return array(); }
        $conteudo = fread($handler, filesize(COLORMAQ_ARQUIVO_EMAIL_DEVOLUCAO));
        fclose($handler);
        if ( empty($conteudo) ) { return array(); }
        $conteudo = unserialize($conteudo);
        if ( is_array($conteudo) ) {
            return $conteudo;
        }
        return array();
    }

    /**
     * Retorna o email e o assunto formatado com os dados informados para fun��o.
     *
     * @param string $nota_fiscal
     * @param string $extrato
     * @param string $emissao
     * @param string $posto
     * @return array('assunto'=>'string'
     *               'mensagem'=>'string')
     */
    function colormaq_retorna_email_formatado($nota_fiscal, $extrato, $emissao, $posto) {
        $dados = colormaq_retorna_email_devolucao();

        if ( count($dados) <= 0 ) { return array(); }
        $assunto  = $dados['assunto'];
        $mensagem = $dados['mensagem'];

        $replace  = array('__NF__'=>'nota_fiscal',
                          '__EXTRATO__'=>'extrato',
                          '__DATA_EMISSAO__'=>'emissao',
                          '__POSTO__'=>'posto');
        foreach ($replace as $search=>$replace) {
            $assunto  = str_replace($search, $$replace, $assunto);
            $mensagem = str_replace($search, $$replace, $mensagem);
        }
        return array('assunto'=>$assunto,
                     'mensagem'=>$mensagem);
    }

    $erros_envio_email   = array();
    $sucesso_envio_email = 0;
    foreach ( $_POST['nf_enviar_emails'] as $nf_id ) {
        // consultar dados da nf e enviar o email
        $nf  = pg_escape_string($nf_id);
        $sql = "SELECT f.nota_fiscal, f.total_nota, f.extrato_devolucao, to_char(f.emissao,'dd/mm/yyyy') as emissao,
                       pf.codigo_posto as posto, p.nome, p.email
                FROM tbl_faturamento f
                INNER JOIN tbl_posto p ON (f.distribuidor = p.posto)
                INNER JOIN tbl_posto_fabrica pf ON (p.posto = pf.posto AND f.fabrica = pf.fabrica)
                WHERE f.fabrica = {$login_fabrica}
                AND f.nota_fiscal = '{$nf}'";
        $res = pg_query($con,$sql);
        $rows= pg_num_rows($res);
        if ( $rows <= 0 ) { return false; }
        $nota_fiscal = pg_fetch_result($res,0,'nota_fiscal');
        $extrato     = pg_fetch_result($res,0,'extrato_devolucao');
        $emissao     = pg_fetch_result($res,0,'emissao');
        $posto       = pg_fetch_result($res,0,'posto');
        $posto_nome  = pg_fetch_result($res,0,'nome');
        $posto       = "{$posto} - {$posto_nome}";
        $email       = pg_fetch_result($res,0,'email');
        $email_dados = colormaq_retorna_email_devolucao();
        $dados       = colormaq_retorna_email_formatado($nota_fiscal,$extrato,$emissao,$posto);
        $header      = "Content-type: text/plain; enconding=iso-8859-1 \r\n";
        $header      = "From: {$email_dados['email_de']}\r\n";
        if ( ! mail($email, utf8_encode($dados['assunto']), utf8_encode($dados['mensagem']), $header) ) {
            $erros_envio_email[] = $nf_id;
        } else {
            $sucesso_envio_email++;
        }
    }
}
// fim HD 107532

if (isset($_POST["btn_acao"])){
    $btn_acao = trim($_POST["btn_acao"]);

    if ($btn_acao == "gravar"){

        $qtde_linhas = trim($_POST['qtde_notas']);
        $acao        = trim($_POST['acao']);

        if (strlen($qtde_linhas)>0 AND strlen($acao)>0 ){

            $res = @pg_query($con,"BEGIN TRANSACTION");

            for ($i=0 ; $i < $qtde_linhas ; $i++){

                $faturamento = trim($_POST['faturamento_'.$i]);

                if (strlen($faturamento)==0) continue;

                if ($acao == "confirmar"){
                    $sql = "UPDATE tbl_faturamento
                                SET conferencia = CURRENT_TIMESTAMP,
                                cancelada = NULL,
                                devolucao_concluida = 't'
                            WHERE faturamento = $faturamento
                            AND fabrica = $login_fabrica
                            AND distribuidor IS NOT NULL";
                    $res = pg_query($con,$sql);
                    $msg_erro .= pg_errormessage($con);

                    $sql = "UPDATE tbl_faturamento_item
                            SET qtde_inspecionada = qtde
                            WHERE faturamento = $faturamento";
                    $res = pg_query($con,$sql);
                    $msg_erro .= pg_errormessage($con);
                    echo $msg_erro; exit;
                }

                if ($acao == "nao_recebida"){

                    $sql = "SELECT distribuidor,nota_fiscal,emissao,extrato_devolucao
                            FROM tbl_faturamento
                            WHERE faturamento = $faturamento
                            AND fabrica = $login_fabrica";
                    $res = pg_query($con,$sql);


                    if (pg_num_rows($res)>0){
                        $posto       = pg_fetch_result($res,0,'distribuidor'); # distribuidor � o posto
                        $nota_fiscal = pg_fetch_result($res,0,'nota_fiscal');
                        $emissao     = pg_fetch_result($res,0,'emissao');
                        $extrato     = pg_fetch_result($res,0,'extrato_devolucao');
                    }else{
                        continue;
                    }

                    $sql = "UPDATE tbl_faturamento
                            SET
                                conferencia = CURRENT_TIMESTAMP,
                                cancelada   = CURRENT_DATE,
                                devolucao_concluida = 'f'
                            WHERE faturamento = $faturamento
                            AND fabrica = $login_fabrica
                            AND distribuidor IS NOT NULL";
                    $res = pg_query($con,$sql);
                    $msg_erro .= pg_errormessage($con);
                    if (1==2){#nao precisa disso - Fabio
                        $sql = "INSERT INTO tbl_lgr_cancelado
                                (posto,nota_fiscal,data_cancelamento,data_nf,usuario,fabrica,foi_cancelado)
                                VALUES
                                ($posto,$nota_fiscal,CURRENT_DATE,'$emissao',$login_admin,$login_fabrica,'t')";
                        //echo "<hr>".nl2br($sql);
                        $res = pg_query($con,$sql);
                        $msg_erro .= pg_errormessage($con);

                        $sql3="SELECT peca,qtde FROM tbl_faturamento_item WHERE faturamento = $faturamento";
                        $res2 = pg_query($con,$sql3);


                        for ($j=0; $j< pg_num_rows($res2); $j++){
                            $peca = pg_fetch_result($res2,$j,peca);
                            $qtde = pg_fetch_result($res2,$j,qtde);

                            $sqlAtualiza = "UPDATE tbl_extrato_lgr
                                            SET qtde_nf = qtde_nf - $qtde
                                            WHERE peca  = $peca
                                            AND extrato = $extrato";
                            $resA = pg_query($con,$sqlAtualiza);
                            $msg_erro .= pg_errormessage($con);
                        }
                    }
                }
            }

            if (strlen($msg_erro)>0){
                $res = @pg_query ($con,"ROLLBACK TRANSACTION");
            }
            else {
                $res = @pg_query ($con,"COMMIT TRANSACTION");
                $msg = "Gravado com sucesso!";
            }
        }
    }
}

$verPopup    = trim($_GET['pop_up']);
$xNotaFiscal = trim($_GET['nota_fiscal']);

#########################################################################################################
if ($verPopup == "sim"){

  if(in_array($login_fabrica, array(35,114))){
    $faturamento_observacao = ", obs ";
  }else{
      $faturamento_observacao = "" ;
  }
    $faturamento = $xNotaFiscal;
    $btn_acao = trim($_POST['btn_acao']);

    if (strlen($faturamento)==0){
        echo "Nenhuma nota.";
        //echo "<script languague='javascript'>this.close();</script>";
        exit;
    }


    $sql = "SELECT  faturamento,
            extrato_devolucao,
            TO_CHAR(emissao,'DD/MM/YYYY') as emissao,
            TO_CHAR(tbl_faturamento.conferencia,'DD/MM/YYYY HH24:MI') AS conferencia,
            TO_CHAR(tbl_faturamento.cancelada,'DD/MM/YYYY') AS cancelada,
            devolucao_concluida,
            nota_fiscal,
            distribuidor,
            fabrica,
            posto,
            movimento,
            cfop
                        {$faturamento_observacao}
        FROM tbl_faturamento
        WHERE distribuidor IS NOT NULL
        AND fabrica        = $login_fabrica
        AND faturamento    = $faturamento";
        //echo nl2br($sql);exit;
    $resD = pg_query ($con,$sql);
    $qtde_for=pg_num_rows ($resD);

    if ($qtde_for == 0) {
        echo "Nenhuma nota encontrada.";
        echo "<script languague='javascript'>this.close();</script>";
        exit;
    }

    $nota_fiscal = pg_fetch_result($resD,0,'nota_fiscal');
    $posto       = pg_fetch_result($resD,0,'distribuidor'); // distrib eh posto
    if($btn_acao == "gravar_observacao"){
        $faturamento = trim($_POST['faturamento']);
        if(strlen($_POST["observacao"]) > 0){
            $obs = $_POST["observacao"];
        }else{
    $obs = '';
        }
            $updateObservacao = "UPDATE tbl_faturamento set obs = '{$obs}' WHERE tbl_faturamento.faturamento = {$faturamento}";

        $res = pg_query($con, $updateObservacao);

    }
    if ($btn_acao=="gravar_conferencia"){

        $resX = pg_query ($con,"BEGIN TRANSACTION");

        $qtde_linhas = trim($_POST['qtde_linhas']);
        $faturamento = trim($_POST['faturamento']);

        if (strlen($qtde_linhas)==0){
            $qtde_linhas = 0;
        }

        $qtde_nao_devolvidas = 0;

        for ($i = 0; $i < $qtde_linhas ; $i++) {

            $peca       = trim($_POST["peca_$i"]);
            $preco      = trim($_POST["preco_$i"]);
            $icms       = trim($_POST["icms_$i"]);
            $ipi        = trim($_POST["ipi_$i"]);
            $qtde_total = trim($_POST["qtde_$i"]);
            $qtde_total_inspecionada = trim($_POST["qtde_insp_$i"]);
            if($login_fabrica == 50){
                $faturamento_item = trim($_POST["fat_item_$i"]);
                $campo_fat_item_colormaq = " AND tbl_faturamento_item.faturamento_item = $faturamento_item ";
            }

            if ($login_fabrica <> '35') {
                $cond_impostos = "
                        AND tbl_faturamento_item.aliq_ipi  = $ipi
                        AND tbl_faturamento_item.aliq_icms = $icms";
            }

            $sql = "UPDATE tbl_faturamento_item SET
                        qtde_inspecionada = NULL
                    FROM tbl_faturamento
                    WHERE tbl_faturamento.faturamento = tbl_faturamento_item.faturamento
                        AND tbl_faturamento.fabrica        = $login_fabrica
                        AND tbl_faturamento.faturamento    = $faturamento
                        $campo_fat_item_colormaq
                        AND tbl_faturamento_item.peca      = $peca
                        AND tbl_faturamento_item.preco     = $preco
                        $cond_impostos";
            $res = pg_query ($con,$sql);

            $msg_erro .= pg_errormessage($con);

            if (strlen($qtde_total_inspecionada)==0 OR strlen($peca)==0 OR strlen($qtde_total)==0){
                continue;
            }

            if ($qtde_total_inspecionada > $qtde_total){
                $qtde_total_inspecionada = $qtde_total;
            }

            $qtde_total_inspecionada_aux = $qtde_total_inspecionada;

            $qtde_nao_devolvidas += $qtde_total - $qtde_total_inspecionada;

            #Se digitou a quantidade inspecionada
            if ( $qtde_total_inspecionada >0 ){
                $sql = "SELECT
                            tbl_faturamento_item.faturamento,
                            tbl_faturamento_item.faturamento_item,
                            tbl_faturamento_item.peca,
                            tbl_faturamento_item.qtde,
                            tbl_faturamento_item.qtde_inspecionada
                        FROM tbl_faturamento
                        JOIN tbl_faturamento_item USING(faturamento)
                        WHERE tbl_faturamento.fabrica       = $login_fabrica
                            AND tbl_faturamento.faturamento = $faturamento
                            $campo_fat_item_colormaq
                            AND tbl_faturamento_item.qtde -
                                CASE WHEN tbl_faturamento_item.qtde_inspecionada IS NULL
                                    THEN 0
                                    ELSE tbl_faturamento_item.qtde_inspecionada
                                END
                                > 0
                            AND tbl_faturamento_item.peca  = $peca
                            AND tbl_faturamento_item.preco = $preco
                            $cond_impostos
                        ORDER BY tbl_faturamento.emissao ASC";
                $res = pg_query ($con,$sql);

                for ( $j=0; $j< pg_num_rows($res); $j++ ){

                    if ( $qtde_total_inspecionada_aux <= 0 ){
                        break;
                    }

                    $faturamento_item   = pg_fetch_result ($res,$j,'faturamento_item');
                    $peca               = pg_fetch_result ($res,$j,'peca');
                    $qtde               = pg_fetch_result ($res,$j,'qtde');
                    $qtde_inspecionada  = pg_fetch_result ($res,$j,'qtde_inspecionada');

                    if (strlen($qtde_inspecionada)==0){
                        $qtde_inspecionada = 0;
                    }

                    $qtde = $qtde - $qtde_inspecionada;

                    if ( $qtde  - $qtde_total_inspecionada_aux < 0 ){
                        $qtde_atualizar = $qtde;
                    }else {
                        $qtde_atualizar = $qtde_total_inspecionada_aux;
                    }

                    $sql2 = "UPDATE tbl_faturamento_item
                            SET qtde_inspecionada = (CASE WHEN tbl_faturamento_item.qtde_inspecionada IS NULL THEN 0 ELSE tbl_faturamento_item.qtde_inspecionada END) + $qtde_atualizar
                            WHERE faturamento = $faturamento
                            AND faturamento_item = $faturamento_item
                            AND peca = $peca";

                    $res2 = pg_query ($con,$sql2);
                    $msg_erro .= pg_errormessage($con);

                    $qtde_total_inspecionada_aux = $qtde_total_inspecionada_aux - $qtde_atualizar;

                    if (strlen($msg_erro)>0){
                        break;
                    }
                }
            }
            #Se digitou ZERO - n�o inspecionou
            if ( $qtde_total_inspecionada == 0 ){
                $sql = "UPDATE tbl_faturamento_item SET
                            qtde_inspecionada = 0
                        FROM tbl_faturamento
                        WHERE tbl_faturamento.faturamento = tbl_faturamento_item.faturamento
                            AND tbl_faturamento.fabrica     = $login_fabrica
                            AND tbl_faturamento.faturamento = $faturamento
                            AND tbl_faturamento_item.peca   = $peca
                            AND tbl_faturamento_item.preco  = $preco
                            $cond_impostos
                            ";
                $res = pg_query ($con,$sql);
                $msg_erro .= pg_errormessage($con);
            }
        }
        # Marca como conferida toda nota que o usuario clicar em Gravar
        $sql = "UPDATE tbl_faturamento
                SET conferencia = CURRENT_TIMESTAMP
                WHERE faturamento = $faturamento
                AND fabrica = $login_fabrica";
        $res = pg_query ($con,$sql);
        $msg_erro .= pg_errormessage($con);

        # Verifica se falta alguma pe�a para devolver desta nota
        $sql = "SELECT SUM( tbl_faturamento_item.qtde - CASE WHEN tbl_faturamento_item.qtde_inspecionada IS NULL THEN 0 ELSE tbl_faturamento_item.qtde_inspecionada END )
                FROM tbl_faturamento
                JOIN tbl_faturamento_item ON tbl_faturamento_item.faturamento = tbl_faturamento.faturamento
                WHERE tbl_faturamento.fabrica   = $login_fabrica
                AND tbl_faturamento.faturamento = $faturamento
                AND (tbl_faturamento_item.qtde - CASE WHEN tbl_faturamento_item.qtde_inspecionada IS NULL THEN 0 ELSE tbl_faturamento_item.qtde_inspecionada END)>0";
                //echo nl2br($sql);
        $res = pg_query ($con,$sql);
        $qtde_faltante = trim(pg_fetch_result($res,0,0));

        # Se n�o tiver mais pe�as para devolver, grava como conclu�da
        if ($qtde_faltante == 0 OR strlen($qtde_faltante)==0 or pg_num_rows($res) == 0 ){
            $sql = "UPDATE tbl_faturamento
                    SET devolucao_concluida = 't'
                    WHERE faturamento = $faturamento
                    AND fabrica = $login_fabrica";
            $res = pg_query ($con,$sql);
            $msg_erro .= pg_errormessage($con);
        }else{

            $mensagem_comunicado = "Foi acusado o recebimento parcial de sua NF. n� $nota_fiscal, favor entrar em contato urgente para sua regulariza��o";

            if ($login_fabrica == 11){
                $mensagem_comunicado = "O Fabricante acusou o recebimento parcial de sua NF. n� $nota_fiscal, favor entrar em contato urgente c/ a Taiz TEL:071 3379-1997, para sua regulariza��o";
            }

            # Envia um comunicado para o PA
            $sqlc = "INSERT INTO tbl_comunicado (
                        descricao              ,
                        mensagem               ,
                        tipo                   ,
                        fabrica                ,
                        obrigatorio_os_produto ,
                        obrigatorio_site       ,
                        posto                  ,
                        ativo
                    ) VALUES (
                        'Nota Fiscal de Devolu��o - LGR',
                        '$mensagem_comunicado',
                        'LGR',
                        $login_fabrica,
                        'f',
                        't',
                        $posto,
                        't'
                    );";
            $resc = pg_query($con,$sqlc);
            $msg_erro .= pg_errormessage($con);
        }


        if($login_fabrica == 50){

            $sql = "SELECT tbl_faturamento.extrato_devolucao FROM tbl_faturamento WHERE faturamento = $faturamento AND extrato_devolucao IS NOT NULL";
            $res = pg_query($con,$sql);

            if(pg_num_rows($res) == 0){
                $sql = "SELECT tbl_faturamento_item.extrato_devolucao FROM tbl_faturamento_item WHERE faturamento = $faturamento AND extrato_devolucao IS NOT NULL";
                $res = pg_query($con,$sql);
            }
        }else{
            $sql = "SELECT tbl_faturamento_item.extrato_devolucao FROM tbl_faturamento_item WHERE faturamento = $faturamento AND extrato_devolucao NOTNULL" ;
            $res = pg_query($con,$sql);
        }

        if(pg_num_rows($res) > 0){
            $aux_extrato = pg_result($res,0,0);
                $sql = "UPDATE tbl_extrato
                        SET admin_lgr = NULL
                        WHERE extrato = $aux_extrato
                        AND fabrica = $login_fabrica";
                $res = pg_query ($con,$sql);
                $msg_erro .= pg_errormessage($con);
        }

        if ($login_fabrica == 50) {
            $sql_temp = "SELECT tbl_os.os, sum(tbl_os_item.qtde) as qtde_item, sum(tbl_faturamento_item.qtde_inspecionada) as inspecionada
            INTO TEMP colormaq_LGR_$login_admin
            FROM tbl_os
            INNER JOIN tbl_os_produto ON tbl_os_produto.os = tbl_os.os
            INNER JOIN tbl_os_item On tbl_os_item.os_produto = tbl_os_produto.os_produto
            INNER JOIN tbl_faturamento_item ON tbl_faturamento_item.os = tbl_os.os AND tbl_faturamento_item.peca = tbl_os_item.peca
            WHERE tbl_faturamento_item.faturamento = $faturamento
            AND tbl_os_item.peca_obrigatoria = 't'
            GROUP BY tbl_os.os;

            SELECT os FROM colormaq_LGR_$login_admin WHERE qtde_item = inspecionada";
            $res_temp = pg_query($con, $sql_temp);
            for($i = 0; $i<pg_num_rows($res_temp); $i++){
                $os = pg_fetch_result($res_temp, $i, os);

                $sql_campo_extra = "UPDATE tbl_os_campo_extra SET os_bloqueada = false WHERE os= $os";
                $res_campo_extra = pg_query($sql_campo_extra);
            }
        }

        if (strlen($msg_erro) == 0) {
            $resX = pg_query ($con,"COMMIT TRANSACTION");
            $msg = "Confer�ncia gravada com Sucesso!";
        }else{
            $resX = pg_query ($con,"ROLLBACK TRANSACTION");

        }
        if ($login_fabrica==50){ // HD 62366 03/03/2006
            echo "<script language='javascript'>window.opener = window;window.close('#');</script>";
        }else{
            echo "<script language='javascript'>opener.window.location = opener.window.location; </script>";
        }
    }


    if ($btn_acao=="liberar_provisorio"){
        $aux_extrato = trim($_POST['extrato']);
        if (strlen($aux_extrato)>0){
            $sql = "UPDATE tbl_extrato
                    SET admin_lgr = $login_admin
                    WHERE extrato = $aux_extrato
                    AND fabrica = $login_fabrica";
            $res = pg_query ($con,$sql);
            $msg_erro .= pg_errormessage($con);
        }
    }

    if ($btn_acao=="bloquear_provisorio"){
        $aux_extrato = trim($_POST['extrato']);
        if (strlen($aux_extrato)>0){
            $sql = "UPDATE tbl_extrato
                    SET admin_lgr = NULL
                    WHERE extrato = $aux_extrato
                    AND fabrica = $login_fabrica";
            $res = pg_query ($con,$sql);
            $msg_erro .= pg_errormessage($con);
        }
    }

    $faturamento         = trim (pg_fetch_result ($resD,0,'faturamento'));
    $distribuidor        = trim (pg_fetch_result ($resD,0,'distribuidor'));
    $posto               = trim (pg_fetch_result ($resD,0,'posto'));
    $emissao             = trim (pg_fetch_result ($resD,0,'emissao'));
    $nota_fiscal         = trim (pg_fetch_result ($resD,0,'nota_fiscal'));
    $extrato_devolucao   = trim (pg_fetch_result ($resD,0,'extrato_devolucao'));
    $cfop                = trim (pg_fetch_result ($resD,0,'cfop'));
    $conferencia         = trim (pg_fetch_result ($resD,0,'conferencia'));
    $devolucao_concluida = trim (pg_fetch_result ($resD,0,'devolucao_concluida'));
    $movimento           = trim (pg_fetch_result ($resD,0,'movimento'));
    $cancelada           = trim (pg_fetch_result ($resD,0,'cancelada'));
    $login_posto         = trim (pg_fetch_result ($resD,0,'distribuidor'));
    $fabrica             = trim (pg_fetch_result ($resD,0,'fabrica'));

        if($login_fabrica == 35 or $login_fabrica == 114 ){
           if(strlen($_POST["observacao"]) > 0){
               $obs = $_POST["observacao"];
           }else{
               $obs = pg_fetch_result($resD, 0, "obs");
           }
        }

    $devolucao = ($movimento =='NAO_RETOR.') ? 'N�O RETORN�VEL':"RETORN�VEL";

    if (strlen ($posto) > 0) {
        $sql = "SELECT  PO.cnpj      ,
                PO.ie        ,
                PO.fone      ,
                PO.nome      ,
                PF.contato_endereco    AS endereco,
                PF.contato_numero      AS numero,
                PF.contato_complemento AS complemento,
                PF.contato_bairro      AS bairro,
                PF.contato_cidade      AS cidade,
                PF.contato_estado      AS estado,
                PF.contato_cep         AS cep,
                PF.contato_email       AS email
            FROM tbl_posto         PO
            JOIN tbl_posto_fabrica PF ON PO.posto = PF.posto
            WHERE PO.posto   = $posto
            AND   PF.fabrica = $login_fabrica";
        //$sql  = "SELECT * FROM tbl_posto WHERE posto = $posto";

        if($login_fabrica == 43) {
            $sql = "SELECT cnpj  ,
                    ie           ,
                    fone         ,
                    nome         ,
                    endereco     ,
                    numero       ,
                    complemento  ,
                    bairro       ,
                    cidade       ,
                    estado       ,
                    cep          ,
                    email
                FROM tbl_posto
                WHERE posto   = $posto ";
        }

        $resX = pg_query ($con,$sql);

        if(pg_num_rows($resX) > 0) {
            $estado   = pg_fetch_result ($resX,0,'estado');
            $razao    = pg_fetch_result ($resX,0,'nome');
            $endereco = trim (pg_fetch_result ($resX,0,'endereco')) . " " . trim (pg_fetch_result ($resX,0,'numero')) . " " . trim(pg_fetch_result($resX,0,'complemento'));
            $cidade   = pg_fetch_result ($resX,0,'cidade');
            $estado   = pg_fetch_result ($resX,0,'estado');
            $cep      = pg_fetch_result ($resX,0,'cep');
            $fone     = pg_fetch_result ($resX,0,'fone');
            $cnpj     = pg_fetch_result ($resX,0,'cnpj');
            $ie       = pg_fetch_result ($resX,0,'ie');
        }
    }

    $cabecalho  = "";

    if (in_array($login_fabrica, array(153))) {

        $cabecalho_produto  = "<style>font-size:14px;font-family:'Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif';</style>";

        $cabecalho_produto  .= "<form name='conferencia' action='$PHP_SELF?pop_up=sim&nota_fiscal=$xNotaFiscal' method='POST'>";

        $cabecalho_produto .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";

        $cabecalho_produto .= "<caption style='font-size:14px;padding:3px;color:#FFF;background-color:#596D9B'>\n";
        $cabecalho_produto .= "<b>CONFER�NCIA</b>\n";
        $cabecalho_produto .= "</caption>\n";

        $cabecalho_produto .= "<tr align='left'  height='16'>\n";
        $cabecalho_produto .= "<td bgcolor='#E3E4E6' colspan='3' style='font-size:18px'>\n";
        $cabecalho_produto .= "<b> RETORN�VEL - PRODUTOS </b><br>\n";
        $cabecalho_produto .= "</td>\n";
        $cabecalho_produto .= "</tr>\n";

        $cabecalho_produto .= "<tr>\n";
        $cabecalho_produto .= "<td>Natureza <br> <b>Devolu��o de Garantia</b> </td>\n";
        $cabecalho_produto .= "<td>CFOP <br> <b>$cfop</b> </td>\n";
        $cabecalho_produto .= "<td>Emissao <br> <b>$emissao</b> </td>\n";
        $cabecalho_produto .= "</tr>\n";
        $cabecalho_produto .= "</table>\n";

        $cnpj = substr ($cnpj,0,2) . "." . substr ($cnpj,2,3) . "." . substr ($cnpj,5,3) . "/" . substr ($cnpj,8,4) . "-" . substr ($cnpj,12,2);
        $cabecalho_produto .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
        $cabecalho_produto .= "<tr>\n";
        $cabecalho_produto .= "<td>Raz�o Social <br> <b>$razao</b> </td>\n";
        $cabecalho_produto .= "<td>CNPJ <br> <b>$cnpj</b> </td>\n";
        $cabecalho_produto .= "<td>Inscri��o Estadual <br> <b>$ie</b> </td>\n";
        $cabecalho_produto .= "</tr>\n";
        $cabecalho_produto .= "</table>\n";

        $cep = substr ($cep,0,2) . "." . substr ($cep,2,3) . "-" . substr ($cep,5,3) ;
        $cabecalho_produto .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
        $cabecalho_produto .= "<tr>\n";
        $cabecalho_produto .= "<td>Endere�o <br> <b>$endereco </b> </td>\n";
        $cabecalho_produto .= "<td>Cidade <br> <b>$cidade</b> </td>\n";
        $cabecalho_produto .= "<td>Estado <br> <b>$estado</b> </td>\n";
        $cabecalho_produto .= "<td>CEP <br> <b>$cep</b> </td>\n";
        $cabecalho_produto .= "</tr>\n";
        $cabecalho_produto .= "</table>\n";

        $cabecalho  = "<style>font-size:14px;font-family:'Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif';</style>";

        $cabecalho  .= "<form name='conferencia' action='$PHP_SELF?pop_up=sim&nota_fiscal=$xNotaFiscal' method='POST'>";

        $cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";

        $cabecalho .= "<caption style='font-size:14px;padding:3px;color:#FFF;background-color:#596D9B'>\n";
        $cabecalho .= "<b>CONFER�NCIA</b>\n";
        $cabecalho .= "</caption>\n";

        $cabecalho .= "<tr align='left'  height='16'>\n";
        $cabecalho .= "<td bgcolor='#E3E4E6' colspan='3' style='font-size:18px'>\n";
        $cabecalho .= "<b> RETORN�VEL - PE�AS </b><br>\n";
        $cabecalho .= "</td>\n";
        $cabecalho .= "</tr>\n";

        $cabecalho .= "<tr>\n";
        $cabecalho .= "<td>Natureza <br> <b>Devolu��o de Garantia</b> </td>\n";
        $cabecalho .= "<td>CFOP <br> <b>$cfop</b> </td>\n";
        $cabecalho .= "<td>Emissao <br> <b>$emissao</b> </td>\n";
        $cabecalho .= "</tr>\n";
        $cabecalho .= "</table>\n";

        $cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
        $cabecalho .= "<tr>\n";
        $cabecalho .= "<td>Raz�o Social <br> <b>$razao</b> </td>\n";
        $cabecalho .= "<td>CNPJ <br> <b>$cnpj</b> </td>\n";
        $cabecalho .= "<td>Inscri��o Estadual <br> <b>$ie</b> </td>\n";
        $cabecalho .= "</tr>\n";
        $cabecalho .= "</table>\n";

        $cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
        $cabecalho .= "<tr>\n";
        $cabecalho .= "<td>Endere�o <br> <b>$endereco </b> </td>\n";
        $cabecalho .= "<td>Cidade <br> <b>$cidade</b> </td>\n";
        $cabecalho .= "<td>Estado <br> <b>$estado</b> </td>\n";
        $cabecalho .= "<td>CEP <br> <b>$cep</b> </td>\n";
        $cabecalho .= "</tr>\n";
        $cabecalho .= "</table>\n";

    } else {

        $cabecalho  = "<style>font-size:14px;font-family:'Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif';</style>";

        if ($login_fabrica == 114) { // HD - 2238440
            $cabecalho .= "<table border='0' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px; margin-bottom:2px;' width='650' >\n";
            $cabecalho .= "<tr height='10'>";
          $cabecalho .= "<td width='14' height='10' bgcolor='#ff9900' class=''>&nbsp;</td>";
          $cabecalho .= "<td align='left' class=''> <strong> Produto divergente da NFe </strong></td>";
            $cabecalho .= "</tr>";
            $cabecalho .= "</table>";
        }
        $cabecalho  .= "<form name='conferencia' action='$PHP_SELF?pop_up=sim&nota_fiscal=$xNotaFiscal' method='POST'>";

        $cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";

        $cabecalho .= "<caption style='font-size:14px;padding:3px;color:#FFF;background-color:#596D9B'>\n";
        $cabecalho .= "<b>CONFER�NCIA</b>\n";
        $cabecalho .= "</caption>\n";

        $cabecalho .= "<tr align='left'  height='16'>\n";
        $cabecalho .= "<td bgcolor='#E3E4E6' colspan='3' style='font-size:18px'>\n";
        $cabecalho .= "<b>&nbsp;$produto_acabado<br>$devolucao </b><br>\n";
        $cabecalho .= "</td>\n";
        $cabecalho .= "</tr>\n";

        $cabecalho .= "<tr>\n";
        $cabecalho .= "<td>Natureza <br> <b>Devolu��o de Garantia</b> </td>\n";
        $cabecalho .= "<td>CFOP <br> <b>$cfop</b> </td>\n";
        $cabecalho .= "<td>Emissao <br> <b>$emissao</b> </td>\n";
        $cabecalho .= "</tr>\n";
        $cabecalho .= "</table>\n";

        $cnpj = substr ($cnpj,0,2) . "." . substr ($cnpj,2,3) . "." . substr ($cnpj,5,3) . "/" . substr ($cnpj,8,4) . "-" . substr ($cnpj,12,2);
        $cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
        $cabecalho .= "<tr>\n";
        $cabecalho .= "<td>Raz�o Social <br> <b>$razao</b> </td>\n";
        $cabecalho .= "<td>CNPJ <br> <b>$cnpj</b> </td>\n";
        $cabecalho .= "<td>Inscri��o Estadual <br> <b>$ie</b> </td>\n";
        $cabecalho .= "</tr>\n";
        $cabecalho .= "</table>\n";

        $cep = substr ($cep,0,2) . "." . substr ($cep,2,3) . "-" . substr ($cep,5,3) ;
        $cabecalho .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
        $cabecalho .= "<tr>\n";
        $cabecalho .= "<td>Endere�o <br> <b>$endereco </b> </td>\n";
        $cabecalho .= "<td>Cidade <br> <b>$cidade</b> </td>\n";
        $cabecalho .= "<td>Estado <br> <b>$estado</b> </td>\n";
        $cabecalho .= "<td>CEP <br> <b>$cep</b> </td>\n";
        $cabecalho .= "</tr>\n";
        $cabecalho .= "</table>\n";

    }

    $topo ="";
    $topo .= "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' id='tbl_pecas_$i'>\n";

    $topo .=  "<thead>\n";
    $topo .=  "<tr align='center'>\n";
    if($login_fabrica == 50){
        $topo .=  "<td><b>OS</b></td>\n";
    }
    $topo .=  "<td><b>C�digo</b></td>\n";
    $topo .=  "<td><b>Descri��o</b></td>\n";
    if(in_array($login_fabrica, array(114,153))){
        $topo .=  "<td><b>Ref. OS</b></td>\n";
    }
    $topo .=  "<td><b>Qtde a devolver</b></td>\n";
    $topo .=  "<td><b>Qtde devolvida</b></td>\n";
    $topo .=  "<td><b>Pre�o</b></td>\n";
    $topo .=  "<td><b>Total</b></td>\n";
    $topo .=  "<td><b>% ICMS</b></td>\n";
    $topo .=  "<td><b>% IPI</b></td>\n";
    $topo .=  "<td><b>Qtde. Insp.</b></td>\n";
    $topo .=  "</tr>\n";
    $topo .=  "</thead>\n";

    if($login_fabrica == 50){
        $campos_colormaq = " tbl_faturamento_item.os, tbl_faturamento_item.faturamento_item, ";
    }

    if(in_array($login_fabrica, array(153))){

        $sql = "SELECT
            tbl_faturamento_item.peca,
            tbl_peca.referencia,
            tbl_peca.descricao,
            tbl_peca.ipi,
            CASE WHEN tbl_peca.produto_acabado IS TRUE THEN 'TRUE' ELSE 'NOT TRUE' END AS produto_acabado,
            tbl_peca.devolucao_obrigatoria,
            tbl_faturamento_item.aliq_icms,
            tbl_faturamento_item.aliq_ipi,
            tbl_faturamento_item.preco,
            SUM (tbl_faturamento_item.qtde) as qtde,
            SUM (tbl_faturamento_item.qtde_inspecionada) as qtde_inspecionada,
            SUM (tbl_faturamento_item.qtde * tbl_faturamento_item.preco) as total,
            SUM (tbl_faturamento_item.base_icms) AS base_icms,
            SUM (tbl_faturamento_item.valor_icms) AS valor_icms,
            SUM (tbl_faturamento_item.base_ipi) AS base_ipi,
            SUM (tbl_faturamento_item.valor_ipi) AS valor_ipi
            FROM tbl_faturamento
            JOIN tbl_faturamento_item USING (faturamento)
            JOIN tbl_peca ON tbl_faturamento_item.peca = tbl_peca.peca AND tbl_peca.produto_acabado IS NOT TRUE
            WHERE tbl_faturamento.fabrica = $login_fabrica
            AND   tbl_faturamento.faturamento  = $faturamento
            GROUP BY
                tbl_faturamento_item.peca,
                tbl_peca.referencia,
                tbl_peca.descricao,
                tbl_peca.devolucao_obrigatoria,
                tbl_peca.produto_acabado,
                tbl_peca.ipi,
                tbl_faturamento_item.aliq_icms,
                tbl_faturamento_item.aliq_ipi,
                tbl_faturamento_item.preco
            ORDER BY tbl_peca.referencia";
        $resX = pg_query ($con,$sql);

        $sql2 = "SELECT
            tbl_faturamento_item.peca,
            tbl_peca.referencia,
            tbl_peca.descricao,
            tbl_peca.ipi,
            CASE WHEN tbl_peca.produto_acabado IS TRUE THEN 'TRUE' ELSE 'NOT TRUE' END AS produto_acabado,
            tbl_peca.devolucao_obrigatoria,
            tbl_faturamento_item.aliq_icms,
            tbl_faturamento_item.aliq_ipi,
            tbl_faturamento_item.preco,
            SUM (tbl_faturamento_item.qtde) as qtde,
            SUM (tbl_faturamento_item.qtde_inspecionada) as qtde_inspecionada,
            SUM (tbl_faturamento_item.qtde * tbl_faturamento_item.preco) as total,
            SUM (tbl_faturamento_item.base_icms) AS base_icms,
            SUM (tbl_faturamento_item.valor_icms) AS valor_icms,
            SUM (tbl_faturamento_item.base_ipi) AS base_ipi,
            SUM (tbl_faturamento_item.valor_ipi) AS valor_ipi
            FROM tbl_faturamento
            JOIN tbl_faturamento_item USING (faturamento)
            JOIN tbl_peca ON tbl_faturamento_item.peca = tbl_peca.peca AND tbl_peca.produto_acabado IS TRUE
            WHERE tbl_faturamento.fabrica = $login_fabrica
            AND   tbl_faturamento.faturamento  = $faturamento
            GROUP BY
                tbl_faturamento_item.peca,
                tbl_peca.referencia,
                tbl_peca.descricao,
                tbl_peca.devolucao_obrigatoria,
                tbl_peca.produto_acabado,
                tbl_peca.ipi,
                tbl_faturamento_item.aliq_icms,
                tbl_faturamento_item.aliq_ipi,
                tbl_faturamento_item.preco
            ORDER BY tbl_peca.referencia";
        $resX2 = pg_query ($con,$sql2);

    }else{

        $sql = "SELECT
            tbl_faturamento_item.peca,
            tbl_peca.referencia,
            tbl_peca.descricao,
            tbl_peca.ipi,
            $campos_colormaq
            CASE WHEN tbl_peca.produto_acabado IS TRUE THEN 'TRUE' ELSE 'NOT TRUE' END AS produto_acabado,
            tbl_peca.devolucao_obrigatoria,
            tbl_faturamento_item.aliq_icms,
            tbl_faturamento_item.aliq_ipi,
            tbl_faturamento_item.preco,
            SUM (tbl_faturamento_item.qtde) as qtde,
            SUM (tbl_faturamento_item.qtde_inspecionada) as qtde_inspecionada,
            SUM (tbl_faturamento_item.qtde * tbl_faturamento_item.preco) as total,
            SUM (tbl_faturamento_item.base_icms) AS base_icms,
            SUM (tbl_faturamento_item.valor_icms) AS valor_icms,
            SUM (tbl_faturamento_item.base_ipi) AS base_ipi,
            SUM (tbl_faturamento_item.valor_ipi) AS valor_ipi
            FROM tbl_faturamento
            JOIN tbl_faturamento_item USING (faturamento)
            JOIN tbl_peca             ON tbl_faturamento_item.peca = tbl_peca.peca
            WHERE tbl_faturamento.fabrica = $login_fabrica
            AND   tbl_faturamento.faturamento  = $faturamento
            GROUP BY
            tbl_faturamento_item.peca,
                tbl_peca.referencia,
                tbl_peca.descricao,
                tbl_peca.devolucao_obrigatoria,
                tbl_peca.produto_acabado,
                tbl_peca.ipi,
                $campos_colormaq
                tbl_faturamento_item.aliq_icms,
                tbl_faturamento_item.aliq_ipi,
                tbl_faturamento_item.preco
            ORDER BY tbl_peca.referencia";
        $resX = pg_query ($con,$sql);

    }

    $notas_fiscais=array();
    $qtde_peca=0;

    // if (pg_num_rows ($resX)==0) continue;

    if(!in_array($login_fabrica, array(153))){
        echo $cabecalho;
        echo $topo;
    }
    ?>
    <script language='javascript' src='ajax.js'></script>
    <script type="text/javascript" src="js/bibliotecaAJAX.js"></script>
    <script language="JavaScript">
        function alteraNumeroNota(nota,nota_antiga,login_posto) {
            if (nota.length>0) {
                url = "<? echo $PHP_SELF;?>?ajax=sim&nota="+nota+"&nota_antiga="+nota_antiga+"&login_posto="+login_posto;
                requisicaoHTTP('GET',url, true , 'respostas');
            } else {
                alert('Digite o N<FA>mero da Nota Fiscal');
            }
        }
        function respostas(campos) {
            if (campos == 'ok') {
                document.getElementById('div_msg').style.display = 'block';
                document.getElementById('div_msg').innerHTML = 'Nota Gravada com Sucesso';
            }
            if (campos == 'Erro')   {
                document.getElementById('div_msg').style.display = 'block';
                document.getElementById('div_msg').innerHTML = 'Nota j� Cadastrada no Sistema';
            }
        }
    </script>
    <?php


    $total_base_icms  = 0;
    $total_valor_icms = 0;
    $total_base_ipi   = 0;
    $total_valor_ipi  = 0;
    $total_nota       = 0;
    $aliq_final       = 0;

    if(in_array($login_fabrica, array(153))){

        $cont_prod = 0;

        if(pg_num_rows ($resX2) > 0){

            echo $cabecalho_produto;
            echo $topo;

            for ($x = 0 ; $x < pg_num_rows ($resX2) ; $x++) {

                $peca                       = pg_fetch_result ($resX2,$x,'peca');
                $peca_referencia            = pg_fetch_result ($resX2,$x,'referencia');
                $peca_descricao             = pg_fetch_result ($resX2,$x,'descricao');
                $ipi                        = pg_fetch_result ($resX2,$x,'ipi');
                $peca_produto_acabado       = pg_fetch_result ($resX2,$x,'produto_acabado');
                $peca_devolucao_obrigatoria = pg_fetch_result ($resX2,$x,'devolucao_obrigatoria');
                $aliq_icms                  = pg_fetch_result ($resX2,$x,'aliq_icms');
                $aliq_ipi                   = pg_fetch_result ($resX2,$x,'aliq_ipi');
                $peca_preco                 = pg_fetch_result ($resX2,$x,'preco');
                $peca_preco_orig            = pg_fetch_result ($resX2,$x,'preco');
                $base_icms                  = pg_fetch_result ($resX2,$x,'base_icms');
                $valor_icms                 = pg_fetch_result ($resX2,$x,'valor_icms');
                $base_ipi                   = pg_fetch_result ($resX2,$x,'base_ipi');
                $valor_ipi                  = pg_fetch_result ($resX2,$x,'valor_ipi');
                $total                      = pg_fetch_result ($resX2,$x,'total');
                $qtde                       = pg_fetch_result ($resX2,$x,'qtde');
                $qtde_inspecionada          = pg_fetch_result ($resX2,$x,'qtde_inspecionada');

                if ($qtde==0)
                    $peca_preco = $peca_preco;
                else
                    $peca_preco = $total / $qtde;

                $total_item = $peca_preco * $qtde;

                if (strlen ($aliq_icms)  == 0) $aliq_icms = 0;

                if ($aliq_icms == 0){
                    $base_icms  = 0;
                    $valor_icms = 0;
                }
                else{
                    $base_icms  = $total_item;
                    $valor_icms = $total_item * $aliq_icms / 100;
                }

                if (strlen($aliq_ipi)==0) $aliq_ipi = 0;

                if ($aliq_ipi==0) {
                    $base_ipi  =0;
                    $valor_ipi =0;
                } else {
                    $base_ipi  =$total_item;
                    $valor_ipi = $total_item*$aliq_ipi/100;
                }

                $total_base_icms  += $base_icms;
                $total_valor_icms += $valor_icms;
                $total_base_ipi   += $base_ipi;
                $total_valor_ipi  += $valor_ipi;
                $total_nota       += $total_item;

                $sqlextrato = "select case when tbl_faturamento_item.extrato_devolucao notnull then tbl_faturamento_item.extrato_devolucao else tbl_faturamento.extrato_devolucao end from tbl_faturamento_item join tbl_faturamento using(faturamento) where faturamento = $faturamento and distribuidor is not null and fabrica = $login_fabrica limit 1";
                $resextrato  = pg_query($con,$sqlextrato);

                $extrato_devolucao = pg_fetch_result($resextrato,0,0);

                if(strlen(trim($extrato_devolucao)) > 0){
                    $sqlqtde = "SELECT sum(tbl_faturamento_item.qtde) from tbl_extrato join tbl_faturamento_item on tbl_faturamento_item.extrato_devolucao = tbl_extrato.extrato and nota_fiscal_origem notnull where extrato = $extrato_devolucao and tbl_faturamento_item.peca = $peca and preco='$peca_preco_orig'";
                    $resqtde = pg_query($con,$sqlqtde);

                    if(pg_num_rows($resqtde) > 0){
                        $qtdeori = pg_fetch_result($resqtde,0,0);
                    }
                }

                if(in_array($login_fabrica, array(114,153))){ // HD - 2238440

                	$cond_nota_origem = ($login_fabrica == 153) ? "" : " AND tbl_faturamento_item.nota_fiscal_origem isnull ";

                    $sqlOS = "SELECT tbl_faturamento_item.os AS numero_os
                        FROM tbl_faturamento_item
                        JOIN tbl_os ON tbl_os.os = tbl_faturamento_item.os AND tbl_os.fabrica = $login_fabrica
                        JOIN tbl_peca ON tbl_peca.peca = tbl_faturamento_item.peca
                        WHERE extrato_devolucao = $extrato_devolucao
                        AND tbl_peca.peca = $peca
						AND nota_fiscal_origem notnull
                        {$cond_nota_origem}
                        AND tbl_faturamento_item.os notnull";
                    $resOS = pg_query($con, $sqlOS);

                    if(pg_num_rows($resOS) > 0 ){
                        $cont = pg_num_rows($resOS);

                        for ($i=0; $i < $cont ; $i++) {
                            $osNumero = pg_fetch_result($resOS, $i, 'numero_os');

                            $sqlPeca = "SELECT
                                    tbl_produto.referencia
                                FROM tbl_produto
                                JOIN tbl_os ON tbl_os.produto = tbl_produto.produto
                                WHERE tbl_os.os = $osNumero
                                    AND tbl_os.fabrica = $login_fabrica";
                            $resPeca = pg_query($con, $sqlPeca);

                            if(pg_num_rows($resPeca) > 0){
                                $ref_pecaOS = pg_fetch_result($resPeca, 0, 'referencia');
                            }

                            if(in_array($login_fabrica, array(114))){
                                if($ref_pecaOS <> $peca_referencia){
                                    $color = "#ff9900";
                                //  $color2 = "#ff9900";
                                }else{
                                    $color = "#ffffff";
                                //  $color2 = "#E2E7E4";
                                }
                            }
                        }
                    }
                }
                echo "<tr bgcolor='".$color."' style='font-color:#000000 ; align:left ; font-size:10px ' >\n";
                if($login_fabrica == 50){
                    echo "<td align='left'>";
                    echo "$os";
                    echo "</td>\n";
                }
                echo "<td align='left'>";
                echo "$peca_referencia";
                echo "</td>\n";
                echo "<td align='left'>$peca_descricao</td>\n";

                if(in_array($login_fabrica, array(114,153))){ // HD - 2238440

                    echo "<td align='left' bgcolor='".$color."'>";
                    if(strlen($cont) > 0){

                        for ($i=0; $i < $cont ; $i++) {
                            $numero_os = pg_fetch_result($resOS, $i, 'numero_os');
                            echo $numero_os.'<br />';
                        }
                    }
                    echo "</td>\n";
                }

                if($login_fabrica == 50){
                    echo "<td align='center' bgcolor='#E2E7E4'>$qtde</td>\n";
                }else{
                    echo "<td align='center' bgcolor='#E2E7E4'>$qtdeori</td>\n";
                }
                echo "<td align='center' bgcolor='#E2E7E4'>$qtde</td>\n";
                echo "<td align='right' nowrap>" . number_format ($peca_preco,2,",",".") . "</td>\n";
                echo "<td align='right' nowrap>" . number_format ($total_item,2,",",".") . "</td>\n";
                echo "<td align='right'>$aliq_icms</td>\n";
                echo "<td align='right'>$aliq_ipi</td>\n";
                if ($qtde==$qtde_inspecionada){
                    $cor_qtde = "#CDFED0";
                }else{
                    $cor_qtde = "#E2E7E4";
                }
                echo "<td align='center' bgcolor='$cor_qtde'>
                        <input type='text'   name='qtde_$x'      id='qtde_$x'      value='$qtde_inspecionada' size='2' maxlength='4'>
                        <input type='hidden' name='peca_$x'      value='$peca'>
                        <input type='hidden' name='preco_$x'     value='$peca_preco'>
                        <input type='hidden' name='icms_$x'      value='$aliq_icms'>
                        <input type='hidden' name='ipi_$x'       value='$aliq_ipi'>
                        <input type='hidden' name='qtde_insp_$x' id='qtde_insp_$x' value='$qtde'>
                        <input type='hidden' name='fat_item_$x' id='fat_item_$x' value='$faturamento_item'></td>\n";

                echo "</tr>\n";
                flush();

            }

            echo "</table>";

        }

        $cont_prod += $x;

        if(pg_num_rows($resX) > 0){

            echo "<br />";

            echo $cabecalho;
            echo $topo;

            for ($x = 0; $x < pg_num_rows ($resX) ; $x++) {

                $peca                       = pg_fetch_result ($resX,$x,'peca');
                $peca_referencia            = pg_fetch_result ($resX,$x,'referencia');
                $peca_descricao             = pg_fetch_result ($resX,$x,'descricao');
                $ipi                        = pg_fetch_result ($resX,$x,'ipi');
                $peca_produto_acabado       = pg_fetch_result ($resX,$x,'produto_acabado');
                $peca_devolucao_obrigatoria = pg_fetch_result ($resX,$x,'devolucao_obrigatoria');
                $aliq_icms                  = pg_fetch_result ($resX,$x,'aliq_icms');
                $aliq_ipi                   = pg_fetch_result ($resX,$x,'aliq_ipi');
                $peca_preco                 = pg_fetch_result ($resX,$x,'preco');
                $peca_preco_orig            = pg_fetch_result ($resX,$x,'preco');
                $base_icms                  = pg_fetch_result ($resX,$x,'base_icms');
                $valor_icms                 = pg_fetch_result ($resX,$x,'valor_icms');
                $base_ipi                   = pg_fetch_result ($resX,$x,'base_ipi');
                $valor_ipi                  = pg_fetch_result ($resX,$x,'valor_ipi');
                $total                      = pg_fetch_result ($resX,$x,'total');
                $qtde                       = pg_fetch_result ($resX,$x,'qtde');
                $qtde_inspecionada          = pg_fetch_result ($resX,$x,'qtde_inspecionada');

                if ($qtde==0)
                    $peca_preco = $peca_preco;
                else
                    $peca_preco = $total / $qtde;

                $total_item = $peca_preco * $qtde;

                if (strlen ($aliq_icms)  == 0) $aliq_icms = 0;

                if ($aliq_icms == 0){
                    $base_icms  = 0;
                    $valor_icms = 0;
                }
                else{
                    $base_icms  = $total_item;
                    $valor_icms = $total_item * $aliq_icms / 100;
                }

                if (strlen($aliq_ipi)==0) $aliq_ipi = 0;

                if ($aliq_ipi==0) {
                    $base_ipi  =0;
                    $valor_ipi =0;
                } else {
                    $base_ipi  =$total_item;
                    $valor_ipi = $total_item*$aliq_ipi/100;
                }

                $total_base_icms  += $base_icms;
                $total_valor_icms += $valor_icms;
                $total_base_ipi   += $base_ipi;
                $total_valor_ipi  += $valor_ipi;
                $total_nota       += $total_item;

                $sqlextrato = "select case when tbl_faturamento_item.extrato_devolucao notnull then tbl_faturamento_item.extrato_devolucao else tbl_faturamento.extrato_devolucao end from tbl_faturamento_item join tbl_faturamento using(faturamento) where faturamento = $faturamento and distribuidor is not null and fabrica = $login_fabrica limit 1";
                $resextrato  = pg_query($con,$sqlextrato);

                $extrato_devolucao = pg_fetch_result($resextrato,0,0);

                if(strlen(trim($extrato_devolucao)) > 0){
                    $sqlqtde = "SELECT sum(tbl_faturamento_item.qtde) from tbl_extrato join tbl_faturamento_item on tbl_faturamento_item.extrato_devolucao = tbl_extrato.extrato and nota_fiscal_origem notnull where extrato = $extrato_devolucao and tbl_faturamento_item.peca = $peca and preco='$peca_preco_orig'";
                    $resqtde = pg_query($con,$sqlqtde);

                    if(pg_num_rows($resqtde) > 0){
                        $qtdeori = pg_fetch_result($resqtde,0,0);
                    }
                }

                if(in_array($login_fabrica, array(114,153))){ // HD - 2238440

                    $sqlOS = "SELECT tbl_faturamento_item.os AS numero_os
                        FROM tbl_faturamento_item
                        JOIN tbl_os ON tbl_os.os = tbl_faturamento_item.os AND tbl_os.fabrica = $login_fabrica
                        JOIN tbl_peca ON tbl_peca.peca = tbl_faturamento_item.peca
                        WHERE extrato_devolucao = $extrato_devolucao
                        AND tbl_peca.peca = $peca
                        AND tbl_faturamento_item.nota_fiscal_origem isnull
                        AND tbl_faturamento_item.os notnull";
                    $resOS = pg_query($con, $sqlOS);

                    if(pg_num_rows($resOS) > 0 ){
                        $cont = pg_num_rows($resOS);

                        for ($i=0; $i < $cont ; $i++) {
                            $osNumero = pg_fetch_result($resOS, $i, 'numero_os');

                            $sqlPeca = "SELECT
                                    tbl_produto.referencia
                                FROM tbl_produto
                                JOIN tbl_os ON tbl_os.produto = tbl_produto.produto
                                WHERE tbl_os.os = $osNumero
                                    AND tbl_os.fabrica = $login_fabrica";
                            $resPeca = pg_query($con, $sqlPeca);

                            if(pg_num_rows($resPeca) > 0){
                                $ref_pecaOS = pg_fetch_result($resPeca, 0, 'referencia');
                            }

                            if(in_array($login_fabrica, array(114))){
                                if($ref_pecaOS <> $peca_referencia){
                                    $color = "#ff9900";
                                //  $color2 = "#ff9900";
                                }else{
                                    $color = "#ffffff";
                                //  $color2 = "#E2E7E4";
                                }
                            }
                        }
                    }
                }
                echo "<tr bgcolor='".$color."' style='font-color:#000000 ; align:left ; font-size:10px ' >\n";
                if($login_fabrica == 50){
                    echo "<td align='left'>";
                    echo "$os";
                    echo "</td>\n";
                }
                echo "<td align='left'>";
                echo "$peca_referencia";
                echo "</td>\n";
                echo "<td align='left'>$peca_descricao</td>\n";

                if(in_array($login_fabrica, array(114,153))){ // HD - 2238440

                    echo "<td align='left' bgcolor='".$color."'>";
                    if(strlen($cont) > 0){

                        for ($i=0; $i < $cont ; $i++) {
                            $numero_os = pg_fetch_result($resOS, $i, 'numero_os');
                            echo $numero_os.'<br />';
                        }
                    }
                    echo "</td>\n";
                }

                if($login_fabrica == 50){
                    echo "<td align='center' bgcolor='#E2E7E4'>$qtde</td>\n";
                }else{
                    echo "<td align='center' bgcolor='#E2E7E4'>$qtdeori</td>\n";
                }
                echo "<td align='center' bgcolor='#E2E7E4'>$qtde</td>\n";
                echo "<td align='right' nowrap>" . number_format ($peca_preco,2,",",".") . "</td>\n";
                echo "<td align='right' nowrap>" . number_format ($total_item,2,",",".") . "</td>\n";
                echo "<td align='right'>$aliq_icms</td>\n";
                echo "<td align='right'>$aliq_ipi</td>\n";
                if ($qtde==$qtde_inspecionada){
                    $cor_qtde = "#CDFED0";
                }else{
                    $cor_qtde = "#E2E7E4";
                }
                echo "<td align='center' bgcolor='$cor_qtde'>
                        <input type='text'   name='qtde_$cont_prod'      id='qtde_$cont_prod'      value='$qtde_inspecionada' size='2' maxlength='4'>
                        <input type='hidden' name='peca_$cont_prod'      value='$peca'>
                        <input type='hidden' name='preco_$cont_prod'     value='$peca_preco'>
                        <input type='hidden' name='icms_$cont_prod'      value='$aliq_icms'>
                        <input type='hidden' name='ipi_$cont_prod'       value='$aliq_ipi'>
                        <input type='hidden' name='qtde_insp_$cont_prod' id='qtde_insp_$cont_prod' value='$qtde'>
                        <input type='hidden' name='fat_item_$cont_prod' id='fat_item_$cont_prod' value='$faturamento_item'></td>\n";

                echo "</tr>\n";
				$cont_prod ++;
                flush();
            }

            echo "</table> <br /> ";

        }

    }else{

        for ($x = 0 ; $x < pg_num_rows ($resX) ; $x++) {

            $peca                = pg_fetch_result ($resX,$x,'peca');
            $peca_referencia     = pg_fetch_result ($resX,$x,'referencia');
            $peca_descricao      = pg_fetch_result ($resX,$x,'descricao');
            $ipi                 = pg_fetch_result ($resX,$x,'ipi');
            $peca_produto_acabado= pg_fetch_result ($resX,$x,'produto_acabado');
            $peca_devolucao_obrigatoria = pg_fetch_result ($resX,$x,'devolucao_obrigatoria');
            $aliq_icms           = pg_fetch_result ($resX,$x,'aliq_icms');
            $aliq_ipi            = pg_fetch_result ($resX,$x,'aliq_ipi');
            $peca_preco          = pg_fetch_result ($resX,$x,'preco');
            $peca_preco_orig     = pg_fetch_result ($resX,$x,'preco');
            $base_icms           = pg_fetch_result ($resX,$x,'base_icms');
            $valor_icms          = pg_fetch_result ($resX,$x,'valor_icms');
            $base_ipi            = pg_fetch_result ($resX,$x,'base_ipi');
            $valor_ipi           = pg_fetch_result ($resX,$x,'valor_ipi');
            if($login_fabrica == 50){
                $os           = pg_fetch_result ($resX,$x,"os");
                $faturamento_item           = pg_fetch_result ($resX,$x,"faturamento_item");
            }

            $total               = pg_fetch_result ($resX,$x,'total');
            $qtde                = pg_fetch_result ($resX,$x,'qtde');
            $qtde_inspecionada   = pg_fetch_result ($resX,$x,'qtde_inspecionada');

            if($login_fabrica == 35) { # HD 1831641
                $base_icms           = 0 ;
                $valor_icms          = 0 ;
                $aliq_icms           = 0 ;
                $base_ipi            = 0 ;
                $aliq_ipi            = 0 ;
                $valor_ipi           = 0 ;
            }

            if ($qtde==0)
                $peca_preco       =  $peca_preco;
            else
                $peca_preco       =  $total / $qtde;

            $total_item  = $peca_preco * $qtde;

            if (strlen ($aliq_icms)  == 0) $aliq_icms = 0;

            if ($aliq_icms==0){
                $base_icms=0;
                $valor_icms=0;
            }
            else{
                $base_icms  = $total_item;
                $valor_icms = $total_item * $aliq_icms / 100;
            }

            if (strlen($aliq_ipi)==0) $aliq_ipi=0;

            if ($aliq_ipi==0)   {
                $base_ipi=0;
                $valor_ipi=0;
            }
            else {
                $base_ipi=$total_item;
                $valor_ipi = $total_item*$aliq_ipi/100;
            }

            $total_base_icms  += $base_icms;
            $total_valor_icms += $valor_icms;
            $total_base_ipi   += $base_ipi;
            $total_valor_ipi  += $valor_ipi;
            $total_nota       += $total_item;

            $sqlextrato = "select case when tbl_faturamento_item.extrato_devolucao notnull then tbl_faturamento_item.extrato_devolucao else tbl_faturamento.extrato_devolucao end from tbl_faturamento_item join tbl_faturamento using(faturamento) where faturamento = $faturamento and distribuidor is not null and fabrica = $login_fabrica limit 1";
            $resextrato  = pg_query($con,$sqlextrato);

            $extrato_devolucao = pg_fetch_result($resextrato,0,0);

            if(strlen(trim($extrato_devolucao)) > 0){
                $sqlqtde = "SELECT sum(tbl_faturamento_item.qtde) from tbl_extrato join tbl_faturamento_item on tbl_faturamento_item.extrato_devolucao = tbl_extrato.extrato and nota_fiscal_origem notnull where extrato = $extrato_devolucao and tbl_faturamento_item.peca = $peca and preco='$peca_preco_orig'";
                $resqtde = pg_query($con,$sqlqtde);

                if(pg_num_rows($resqtde) > 0){
                    $qtdeori = pg_fetch_result($resqtde,0,0);
                }
            }

            if(in_array($login_fabrica, array(114,153))){ // HD - 2238440

                $sqlOS = "SELECT tbl_faturamento_item.os AS numero_os
                    FROM tbl_faturamento_item
                    JOIN tbl_os ON tbl_os.os = tbl_faturamento_item.os AND tbl_os.fabrica = $login_fabrica
                    JOIN tbl_peca ON tbl_peca.peca = tbl_faturamento_item.peca
                    WHERE extrato_devolucao = $extrato_devolucao
                    AND tbl_peca.peca = $peca
                    AND tbl_faturamento_item.nota_fiscal_origem isnull
                    AND tbl_faturamento_item.os notnull";
                $resOS = pg_query($con, $sqlOS);

                if(pg_num_rows($resOS) > 0 ){
                    $cont = pg_num_rows($resOS);

                    for ($i=0; $i < $cont ; $i++) {
                        $osNumero = pg_fetch_result($resOS, $i, 'numero_os');

                        $sqlPeca = "SELECT
                                tbl_produto.referencia
                            FROM tbl_produto
                            JOIN tbl_os ON tbl_os.produto = tbl_produto.produto
                            WHERE tbl_os.os = $osNumero
                                AND tbl_os.fabrica = $login_fabrica";
                        $resPeca = pg_query($con, $sqlPeca);

                        if(pg_num_rows($resPeca) > 0){
                            $ref_pecaOS = pg_fetch_result($resPeca, 0, 'referencia');
                        }

                        if(in_array($login_fabrica, array(114))){
                            if($ref_pecaOS <> $peca_referencia){
                                $color = "#ff9900";
                            //  $color2 = "#ff9900";
                            }else{
                                $color = "#ffffff";
                            //  $color2 = "#E2E7E4";
                            }
                        }
                    }
                }
            }
            echo "<tr bgcolor='".$color."' style='font-color:#000000 ; align:left ; font-size:10px ' >\n";
            if($login_fabrica == 50){
                echo "<td align='left'>";
                echo "$os";
                echo "</td>\n";
            }
            echo "<td align='left'>";
            echo "$peca_referencia";
            echo "</td>\n";
            echo "<td align='left'>$peca_descricao</td>\n";

            if(in_array($login_fabrica, array(114,153))){ // HD - 2238440

                echo "<td align='left' bgcolor='".$color."'>";
                if(strlen($cont) > 0){

                    for ($i=0; $i < $cont ; $i++) {
                        $numero_os = pg_fetch_result($resOS, $i, 'numero_os');
                        echo $numero_os.'<br />';
                    }
                }
                echo "</td>\n";
            }

            if($login_fabrica == 50){
                echo "<td align='center' bgcolor='#E2E7E4'>$qtde</td>\n";
            }else{
                echo "<td align='center' bgcolor='#E2E7E4'>$qtdeori</td>\n";
            }
            echo "<td align='center' bgcolor='#E2E7E4'>$qtde</td>\n";
            echo "<td align='right' nowrap>" . number_format ($peca_preco,2,",",".") . "</td>\n";
            echo "<td align='right' nowrap>" . number_format ($total_item,2,",",".") . "</td>\n";
            echo "<td align='right'>$aliq_icms</td>\n";
            echo "<td align='right'>$aliq_ipi</td>\n";
            if ($qtde==$qtde_inspecionada){
                $cor_qtde = "#CDFED0";
            }else{
                $cor_qtde = "#E2E7E4";
            }
            echo "<td align='center' bgcolor='$cor_qtde'>
                    <input type='text'   name='qtde_$x'      id='qtde_$x'      value='$qtde_inspecionada' size='2' maxlength='4'>
                    <input type='hidden' name='peca_$x'      value='$peca'>
                    <input type='hidden' name='preco_$x'     value='$peca_preco'>
                    <input type='hidden' name='icms_$x'      value='$aliq_icms'>
                    <input type='hidden' name='ipi_$x'       value='$aliq_ipi'>
                    <input type='hidden' name='qtde_insp_$x' id='qtde_insp_$x' value='$qtde'>
                    <input type='hidden' name='fat_item_$x' id='fat_item_$x' value='$faturamento_item'></td>\n";

            echo "</tr>\n";
            flush();
        }

    }

    echo "</table>\n";

    echo "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >";
    echo "<tr>";
    echo "<td>Base ICMS <br> <b> " . number_format ($total_base_icms,2,",",".") . " </b> </td>";
    echo "<td>Valor ICMS <br> <b> " . number_format ($total_valor_icms,2,",",".") . " </b> </td>";
    echo "<td>Base IPI <br> <b> " . number_format ($total_base_ipi,2,",",".") . " </b> </td>";
    echo "<td>Valor IPI <br> <b> " . number_format ($total_valor_ipi,2,",",".") . " </b> </td>";
    echo "<td>Total da Nota <br> <b> " . number_format ($total_nota+$total_valor_ipi,2,",",".") . " </b> </td>";
    echo "</tr>";
    echo "</table>";

    echo (in_array($login_fabrica, array(153))) ? "<br />" : "";

    echo "<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px' width='650' >\n";
    echo "<tr>\n";
    if ($telecontrol_distrib){
       echo "<td><center><IMG SRC='imagens/setona_h.gif' WIDTH='53' HEIGHT='29' BORDER='0' align='absmiddle'>N�mero da Nota: <input type='text' name='ressarcimento_nota_fiscal' size='10' maxlength='6' value='$nota_fiscal' onblur='alteraNumeroNota(this.value,$nota_fiscal,$login_posto)'></center></td>";
       echo "<tr>
                <td align='center'><div id='div_msg' style='display:none; border: 1px solid #949494;background-color: #F1F0E7;width:180px;'></div>
            </td>
            </tr>";
    }else{
       echo "<td><h4><center>N�<BA> da NF de Devolu��o: $nota_fiscal</center></h4></td>\n";
    }
    echo "</tr>";

    if ($login_fabrica == 50 or $login_fabrica == 114) { // HD 62342

        $sql_nf = "SELECT tbl_faturamento_item.nota_fiscal_origem
                    FROM tbl_faturamento_item
                    JOIN tbl_faturamento      USING (faturamento)
                    WHERE tbl_faturamento.fabrica = $login_fabrica
                    AND   tbl_faturamento.posto   = $posto
                    AND   tbl_faturamento.faturamento=$faturamento
                    ORDER BY tbl_faturamento.nota_fiscal";
        $resNF = pg_query ($con,$sql_nf);
        //echo $sql_nf;exit;
        for ($y = 0 ; $y < pg_num_rows ($resNF) ; $y++) {
            array_push($notas_fiscais,pg_fetch_result ($resNF,$y,'nota_fiscal_origem'));
        }
        $notas_fiscais = array_unique($notas_fiscais);
        asort($notas_fiscais);
        if (count($notas_fiscais)>0){
            echo "<tr>";
            echo "<td><h4><center>Referente as NFs. " . implode(", ",$notas_fiscais) . "</center></h4></td>\n";
            echo "</tr>";
        }

    }

    if (strlen($conferencia)>0 AND strlen($cancelada)==0){
        echo "<tr>\n";
        echo "<td><center>";
        echo "<h5 style='color:#0000FF'>Nota Fiscal conferida em $conferencia</h5>";
        if ($devolucao_concluida!='t'){
            echo "<h5 style='color:#7A7C85'>Devolu��o Parcial</h5>";
        }else{
            echo "<h5 style='color:#007900'>Devolu��o Completa</h5>";
        }
        echo "</center></td>";
        echo "</tr>";
    }elseif(strlen($cancelada)>0){
        echo "<tr>\n";
        echo "<td><center><h5 style='color:#FF0000'>Nota Fiscal cancelada em $cancelada</h5></center></td>";
        echo "</tr>";
    }

	if($login_fabrica == 153 and $cont_prod > 0 ) $x =$cont_prod;
    echo "  <input type='hidden' name='qtde_linhas' value='$x'>
            <input type='hidden' name='faturamento' value='$faturamento'>
            <input type='hidden' name='btn_acao'    value=''>";
    if ($login_admin != 861) {
    if(strlen($cancelada)==0 AND $devolucao_concluida!='t'){
        if ($login_fabrica==50){ // HD 62366 03/03/2006
            echo "<tr>\n";
            echo "<td><h4><center>";
            echo "<input type='button' name='preencher_qtde' value='Preenchimento Autom�tico' onClick=\"javascript:
                for (i=0;i<$x;i++){
                    document.getElementById('qtde_'+i).value = document.getElementById('qtde_insp_'+i).value;
                }
            \"></center></h4></td>\n";
            echo "</tr>";
        }
        echo "<tr>\n";
        echo "<td><h4><center>";
        echo "<input type='button' name='gravar' value='Confer�ncia' onClick=\"javascript:
        if (this.form.btn_acao.value == ''){
            this.form.btn_acao.value = 'gravar_conferencia';
            this.form.submit();
        }else{
            alert('Aguarde submiss�o');
        }
        \"></center></h4></td>\n";
        echo "</tr>";
    }
    } else {
        echo "";
    }


        if ($devolucao_concluida!='t' AND strlen($cancelada)==0){
                $sql = "SELECT tbl_extrato.extrato, tbl_extrato.admin_lgr
                    FROM tbl_faturamento_item
                    JOIN tbl_faturamento USING(faturamento)
                    JOIN tbl_extrato ON (tbl_extrato.extrato = tbl_faturamento_item.extrato_devolucao or tbl_extrato.extrato = tbl_faturamento.extrato_devolucao)
                    WHERE tbl_faturamento.faturamento = $faturamento
                    AND tbl_extrato.fabrica = $login_fabrica";
            $resX = pg_query ($con,$sql);
            //echo nl2br($sql);

            if (pg_num_rows ($resX)>0){
                $extrato    = pg_fetch_result ($resX,0,'extrato');
                $admin_lgr  = pg_fetch_result ($resX,0,'admin_lgr');
                if ($login_admin != 861) {
                    if (strlen($admin_lgr)==0){
                        echo "<tr><td>&nbsp;</td></tr>\n";
                        echo "<tr style='background-color:#D9E8FF'>\n";
                        echo "<td><h5 style='color:#39842F'><center>
                        <input type='hidden' name='extrato' value='$extrato'>
                        <input type='button' name='gravar' value='Liberar Provisoriamente' onClick=\"javascript:
                        if (this.form.btn_acao.value == ''){
                            this.form.btn_acao.value = 'liberar_provisorio';
                            this.form.submit();
                        }else{
                            alert('Aguarde submiss�o');
                        }
                        \"></center></h5><b>* Liberando provisoriamente, este Posto Autorizado poder� visualizar o pr�ximo extrato mesmo sem a confirma��o de recebimento das Notas de Devolu��o do m�s anterior.<br>** N�o � necess�rio liberar em todas as Notas de   Devolu��o.</b></td>\n";
                        echo "</tr>";
                    }else{
                        echo "<tr><td>&nbsp;</td></tr>\n";
                        echo "<tr style='background-color:#E4FFD9'>\n";
                        echo "<td><center>
                        <h3>Extrato $extrato liberado provisoriamente</h3>
                        <h5 style='color:#39842F'>
                        <input type='hidden' name='extrato' value='$extrato'>
                        <input type='button' name='gravar' value='Bloquear Visualiza��o do Extrato'         onClick=\"javascript:
                        if (this.form.btn_acao.value == ''){
                        this.form.btn_acao.value = 'bloquear_provisorio';
                        this.form.submit();
                        }else{
                        alert('Aguarde submiss�o');
                        }
                        \"></center></h45><b>* Bloqueando, o Posto Autorizado n�o poder� visualizar o pr�ximo extrato sem a confirma��o do recebimento das Notas de Devolu��o       referente a este extrato.</b></td>\n";
                        echo "</tr>";
                    }
                }
            }
        }


    echo "</table>";
  if(in_array($login_fabrica, array(35, 114))){  echo "<br />"?>
<table border='1' cellspacing='0' cellpadding='3' border-color='#000' style='border-collapse:collapse;font-size:12px;text-align:center' width='650' >
  <tr>
    <td>
      <caption style='font-size:14px;padding:3px;color:#FFF;background-color:#596D9B'>
    <b>OBSERVA��O</b>

      </caption>
    </td>
  </tr>
  <tr>

    <td>
      <textarea name='observacao' style='margin: 0 auto' rows='4' cols='50'><?=$obs?> </textarea><br /><br />
      <input type='button' name='gravar' value='Gravar  Observa��o' onClick="javascript:
         if (this.form.btn_acao.value == ''){
         this.form.btn_acao.value = 'gravar_observacao';
      this.form.submit();
         }else{
         alert('Aguarde submiss�o');
         }
         "> <br /> <br />
    </td>
  </tr>
</table>

<?  echo $tableObservacao; }


    echo "</form>";

    exit;
}
########################################################################################################





include "cabecalho.php";



?>
<?php include "../js/js_css.php";?>
<script src="js/jquery.bgiframe.js"          type="text/javascript"></script>
<script src="js/jquery.dimensions.tootip.js" type="text/javascript"></script>
<script src="js/chili-1.7.pack.js"           type="text/javascript"></script>
<script src="js/jquery.tooltip.min.js"           type="text/javascript"></script>
<script src="../plugins/shadowbox/shadowbox.js" type="text/javascript"></script>
<link rel="stylesheet" href="js/jquery.tooltip.css" />

<script type="text/javascript">
    $(function() {
        $("#ajuda").tooltip({
            track: true,
            delay: 0,
            showURL: false,
            opacity: 0.85,
            showBody: " - ",
            extraClass: "mensagem"
        });
    });
    Shadowbox.init({
        skipSetup: true,
        modal: true
    });
    function solicitaPostagemPosto(posto,total,faturamento) {
        Shadowbox.open({
            content : "../solicitacao_postagem_positron.php?posto="+ posto+"&total="+total+"&faturamento="+faturamento+"&admin=true",
            player : "iframe",
            title : "Autoriza��o de Postagem",
            width : 900,
            height : 500
        });
    }
</script>


<style type="text/css">

.Tabela{
    border:1px solid #596D9B;
    background-color:#596D9B;
    }
.menu_top {
    text-align: center;
    font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
    font-size: 10PX ;
    font-weight: bold;
    border: 1px solid;
    background-color: #D9E2EF
}

.table_line {
    font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-weight: normal;
    border: 0px solid;
    background-color: #D9E2EF
}

.table_line2 {
    font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-weight: normal;
    border: 0px solid;
    background-color: #FFFFFF
}
.quadro{
    border: 1px solid #596D9B;
    width:450px;
    height:50px;
    padding:10px;

}
.botao {
        border-top: 1px solid #333;
            border-left: 1px solid #333;
            border-bottom: 1px solid #333;
            border-right: 1px solid #333;
            font-size: 13px;
            margin-bottom: 10px;
            color: #0E0659;
        font-weight: bolder;
}
.Titulo {
    text-align: center;
    font-family: Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif;
    font-size: 12px;
    font-weight: bold;
    color: #FFFFFF;
    background-color: #596D9B;
}
.Conteudo {
    font-family: Verdana, Tahoma, Geneva, Arial, Helvetica, sans-serif;
    font-size: 10px;
    font-weight: normal;
}
.inpu{
    border:1px solid #666;
    font-size:12px;
}
.butt{
    border:1px solid #666;
    background-color:#ccc;
    font-size:12px;
}

.mensagem {
    width: 600px;
    margin: 0 auto;
    margin-top: 20px;
    margin-bottom: 20px;
    text-align: center;
    padding: 10px 5px;
    font-size: 10pt;
}

.msg-info {
    border: 1px solid #596D9B;
    background-color: #E6EEF7;
}

</style>
<script language='javascript' src='../ajax.js'></script>
<? include "javascript_pesquisas.php"; ?>
<script language="JavaScript">

/* ============= Fun��o PESQUISA DE POSTOS ====================
Nome da Fun��o : fnc_pesquisa_posto (cnpj,nome)
        Abre janela com resultado da pesquisa de Postos pela
        C�digo ou CNPJ (cnpj) ou Raz�o Social (nome).
=================================================================*/


var checkflag = "false";
function check(field) {
    if (checkflag == "false") {
        for (i = 0; i < field.length; i++) {
            field[i].checked = true;
        }
        checkflag = "true";
        return true;
    }
    else {
        for (i = 0; i < field.length; i++) {
            field[i].checked = false;
        }
        checkflag = "false";
        return true;
    }
}

function AbrirJanelaObs (extrato) {
    var largura  = 500;
    var tamanho  = 300;
    var lar      = largura / 2;
    var tam      = tamanho / 2;
    var esquerda = (screen.width / 2) - lar;
    var topo     = (screen.height / 2) - tam;
    var link = "extrato_status.php?extrato=" + extrato;
    window.open(link, "janela", "toolbar=no, location=no, status=yes, menubar=no, scrollbars=no, directories=no, resizable=no, width=" + largura + ", height=" + tamanho + ", top=" + topo + ", left=" + esquerda + "");
}

function verNota(nota_fiscal){
    var largura  = 700;
    var tamanho  = 400;
    var lar      = largura / 2;
    var tam      = tamanho / 2;
    var esquerda = (screen.width / 2) - lar;
    var topo     = (screen.height / 2) - tam;
    var link = "<?=$PHP_SELF?>?pop_up=sim&nota_fiscal=" + nota_fiscal;
    window.open(link, "janela", "toolbar=no, location=no, status=yes, menubar=no, scrollbars=yes, directories=no, resizable=yes, width=" + largura + ", height=" + tamanho + ", top=" + topo + ", left=" + esquerda + "");
}


function verOs(extrato){
    $('tr[rel=mostra_'+extrato+']').toggle();
}
function alteraNumeroNota(nota,nota_antiga) {
    if (nota.length>0) {
        url = "<? echo $PHP_SELF;?>?ajax=sim&nota="+nota+"&nota_antiga="+nota_antiga;
        requisicaoHTTP('GET',url, true , 'respostas');
    } else {
        alert('Digite o N<FA>mero da Nota Fiscal');
    }
}

</script>


<script type="text/javascript" charset="utf-8">
    $(function(){
        $('#datai').datepick({startDate:'01/01/2000'});
        $('#dataf').datepick({startDate:'01/01/2000'});
        $('#datai').mask("99/99/9999");
        $('#dataf').mask("99/99/9999");
    });
</script>


<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />

<script language="JavaScript">
$().ready(function() {

    function formatItem(row) {
        return row[2] + " - " + row[1];
    }

    function formatResult(row) {
        return row[0];
    }

    /* Busca pelo C�digo */
    $("#posto_codigo").autocomplete("<?echo $PHP_SELF.'?busca=codigo'; ?>", {
        minChars: 3,
        delay: 150,
        width: 350,
        matchContains: true,
        formatItem: formatItem,
        formatResult: function(row) {return row[2];}
    });

    $("#posto_codigo").result(function(event, data, formatted) {
        $("#posto_nome").val(data[1]) ;
    });

    /* Busca pelo Nome */
    $("#posto_nome").autocomplete("<?echo $PHP_SELF.'?busca=nome'; ?>", {
        minChars: 3,
        delay: 150,
        width: 350,
        matchContains: true,
        formatItem: formatItem,
        formatResult: function(row) {return row[1];}
    });

    $("#posto_nome").result(function(event, data, formatted) {
        $("#posto_codigo").val(data[2]) ;
        //alert(data[2]);
    });

    /**
     * Inver sele��o dos checkbox de envio de e-mail
     * HD 107532
     *
     * @author Augusto Pascutti <augusto.pascutti@telecontrol.com.br>
     */
    $('#inverter_chk_email').change(function() {
        var chk_status = $(this).attr('checked');
        $('.checkable').attr('checked',chk_status);
    });
    // fim HD 107532
});
</script>
<?

if (strlen($msg_erro) > 0) {
    echo "<table width='600' align='center' border='0' cellspacing='1' cellpadding='1' class='error'>\n";
    echo "<tr>";
    echo "<td>$msg_erro</td>";
    echo "</tr>";
    echo "</table>\n";
}

if(strlen($msg)>0){
    echo "<center><b style='font-size:12px;border:1px solid #999;padding:10px;background-color:#dfdfdf'>$msg</b></center><br>";
}

echo "<FORM METHOD='POST' NAME='frm_extrato' ACTION=\"$PHP_SELF\">";
?>

<input type='hidden' name='btnacao'>
<table class='Tabela' width='700' cellspacing='0'  cellpadding='0' bgcolor='#596D9B' align='center'>

    <tr >
        <td class="Titulo" height='20'>Controle de Devolu��o de Pe�as</td>
    </tr>
    <tr>
        <td bgcolor='#F3F8FE'>
            <TABLE width='100%' align='center' border='0' cellspacing='0' cellpadding='2'>
                    <tr class="Conteudo" bgcolor="#D9E2EF" align='left'>
                        <td align='right'><br>C�digo Posto&nbsp;</td>
                        <td><br><input type="text" name="posto_codigo" id="posto_codigo" size="16" value="<? echo $posto_codigo ?>" class="frm">
                            <img border="0" src="imagens/lupa.png" style="cursor: hand;" align="absmiddle" alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript: fnc_pesquisa_posto (document.frm_extrato.posto_codigo, document.frm_extrato.posto_nome, 'codigo')"></td>
                    </tr>
                    <tr class="Conteudo" bgcolor="#D9E2EF" align='left'>
                        <td align='right'>Nome do Posto&nbsp;</td>
                        <td>
                            <input type="text" name="posto_nome" id="posto_nome" size="30"  value="<?echo $posto_nome?>" class="frm">
                            <img border="0" src="imagens/lupa.png" style="cursor: hand;" align="absmiddle" alt="Clique aqui para pesquisar postos pelo c�digo" onclick="javascript: fnc_pesquisa_posto (document.frm_extrato.posto_codigo, document.frm_extrato.posto_nome, 'nome')">
                        </td>
                    </tr>
                    <tr class="Conteudo" bgcolor="#D9E2EF" align='left'>
                        <td align='right'>Nota de Devolu��o &nbsp;</td>
                        <td><input type="text" name="nota_devolucao" size="10"  value="<?echo $nota_devolucao?>" class="frm">
                        </td>
                    </tr>
                    <tr class="Conteudo" bgcolor="#D9E2EF" align='left'>
                        <td align='right'>Data do Envio &nbsp;</td>
                        <td><input type="text" name="data_inicial" id="datai" size="12"  value="<?php echo $data_inicial?>" class="frm"> &nbsp;&nbsp;&nbsp;&nbsp;at�&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="text" name="data_final" id="dataf" size="12"  value="<?php echo $data_final?>" class="frm">
                        </td>
                    </tr>
                    <tr class="Conteudo" bgcolor="#D9E2EF">
                        <td align='right'>
                            <input type='checkbox' name='filtroObrigatorio' value='checked' <? echo $filtroObrigatorio; ?> />
                        <td align='left'>
                            Notas de Retorno Obrigat�rio
                        </TD>
                    </tr>
                    <tr class="Conteudo" bgcolor="#D9E2EF">
                        <td align='right'>
                            <input type='checkbox' name='filtroNaoObrigatorio' value='checked' <? echo $filtroNaoObrigatorio; ?> />
                        <td align='left'>
                            Notas de Retorno N�o Obrigat�rio
                        </TD>
                    </tr>
                    <tr class="Conteudo" bgcolor="#D9E2EF">
                        <td align='right'>
                            <input type='checkbox' name='filtroTodos' value='checked' <? echo $filtroTodos; ?> />
                        <td align='left'>
                            Notas Com Recebimento Pendente
                        </TD>
                    </tr>
                    <tr class="Conteudo" bgcolor="#D9E2EF">
                        <td align='right'>
                            <input type='checkbox' name='filtroRecebidos' value='checked' <? echo $filtroRecebidos; ?> />
                        <td align='left'>
                            Notas Recebidas
                        </TD>
                    </tr>
                    <tr><td colspan='2' bgcolor="#D9E2EF" align='center'><img src="imagens_admin/btn_filtrar.gif" onclick="javascript: document.frm_extrato.btnacao.value='filtrar' ; document.frm_extrato.submit() " ALT="Filtrar" border='0' style="cursor:pointer;"><br></td></tr>

                    <?
                    if ($login_fabrica==2){
                        echo "<tr><td colspan='2' bgcolor='#D9E2EF' align='center'><a href='$PHP_SELF?posto_devendo=todos'>Clique aqui para ver todos posto com envio pendentes</a></td></tr>";
                    }
                    ?>



            </TABLE>
        </td>
    </tr>
    <tr><td bgcolor='#D9E2EF'>
        </td>
    </tr>

</table>
</form>


<?

    if ($btnacao=='filtrar' && (strlen($filtroRecebidos)>0 || strlen($filtroTodos)>0 || strlen($posto_codigo)>0  || strlen($nota_devolucao)>0 || !empty($filtroNaoObrigatorio) || !empty($filtroObrigatorio))){

        if (strlen($posto_codigo)>0){
            $sql_adicional = " AND tbl_posto_fabrica.codigo_posto = '$posto_codigo' ";
        }
        if (strlen($filtroTodos)>0 || strlen($filtroRecebidos)>0){
            if (strlen($filtroTodos)>0){
                $sql_adicional_2 = " AND tbl_faturamento.conferencia IS NULL ";
            }
            if (strlen($filtroRecebidos)>0){
                $sql_adicional_2 = " AND tbl_faturamento.conferencia IS NOT NULL ";
            }
            if (strlen($filtroTodos)>0 && strlen($filtroRecebidos)>0){
                $sql_adicional_2 = "";
            }
        }

        if (strlen($filtroObrigatorio)>0 AND strlen($filtroNaoObrigatorio)==0){
            if($login_fabrica <> 91 and $login_fabrica <> '35') {
                $sql_adicional_5  = " AND tbl_faturamento.movimento = 'RETORNAVEL' ";
            }

        }

        if (strlen($filtroObrigatorio)==0 AND strlen($filtroNaoObrigatorio)>0){
            $sql_adicional_5  = " AND tbl_faturamento.movimento = 'NAO_RETOR.' ";
        }

        if (strlen($data_inicial)>0 AND strlen($data_final)>0){
            $tmp_data_inicial = converte_data($data_inicial);
            $tmp_data_final   = converte_data($data_final);
            $sql_adicional_3  = " AND tbl_faturamento.emissao BETWEEN '$tmp_data_inicial' AND '$tmp_data_final' ";
        }

        if ((strlen($data_inicial)==0 AND strlen($data_final)>0) OR (strlen($data_inicial)>0 AND strlen($data_final)==0)){
            $msg_erro .= "Preencha as duas datas";
        }

        if (strlen($nota_devolucao)>0){
            $sql_adicional_4 = " AND tbl_faturamento.nota_fiscal like '%$nota_devolucao' ";
        }

        if (strlen($filtroObrigatorio)>0 AND strlen($filtroNaoObrigatorio)>0){
            if($login_fabrica <> 91 and $login_fabrica <> '35') {
                $sql_adicional_6  = " AND (tbl_faturamento.movimento = 'NAO_RETOR.' OR tbl_faturamento.movimento = 'RETORNAVEL') ";
            }
        }

        $campo_extrato_devolucao = ($login_fabrica == 50 and $controla_estoque != "t") ? "tbl_faturamento.extrato_devolucao" : "tbl_faturamento_item.extrato_devolucao";

        if($login_fabrica == 35){
            $join_postagem = "LEFT JOIN tbl_faturamento_correio ON tbl_faturamento.faturamento = tbl_faturamento_correio.faturamento and tbl_faturamento_correio.fabrica = $login_fabrica ";
                $campos_postagem = " tbl_faturamento_correio.numero_postagem,
                        tbl_faturamento_correio.conhecimento, ";
        }

        if($login_fabrica != 50){
            $join_extrato = "JOIN tbl_extrato ON tbl_extrato.extrato = tbl_faturamento_item.extrato_devolucao OR tbl_extrato.extrato = tbl_faturamento.extrato_devolucao";
            $campo_extrato = ", tbl_extrato.admin_lgr";
            $extrato_devolucao = "";
        }else{
            $extrato_devolucao = "";
        }

         $sql = "SELECT DISTINCT tbl_faturamento.faturamento,
                        to_char(tbl_faturamento.emissao,'DD/MM/YYYY') AS emissao,
                        to_char(tbl_faturamento.conferencia,'DD/MM/YYYY') AS conferencia,
                        to_char(tbl_faturamento.cancelada,'DD/MM/YYYY') AS cancelada,
                        tbl_faturamento.devolucao_concluida,
                        $campos_postagem
                        tbl_faturamento.nota_fiscal,
                        $campo_extrato_devolucao,
                        tbl_faturamento.total_nota            AS total_nota,
                        tbl_faturamento.valor_ipi             AS valor_ipi,
                        tbl_posto.nome                        AS nome_posto,
                        tbl_posto.posto                       AS posto,
                        tbl_posto_fabrica.codigo_posto        AS codigo_posto
                        $campo_extrato
                FROM tbl_faturamento_item
                JOIN tbl_faturamento USING(faturamento)
                JOIN tbl_posto ON tbl_faturamento.distribuidor = tbl_posto.posto
                JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_posto.posto
                $join_extrato
                $join_postagem
                WHERE tbl_faturamento.distribuidor IS NOT NULL
                AND tbl_faturamento.posto notnull and tbl_faturamento.posto <> tbl_posto.posto
                AND tbl_faturamento.fabrica  = $login_fabrica
                AND tbl_posto_fabrica.fabrica = $login_fabrica
                $sql_adicional
                $sql_adicional_2
                $sql_adicional_3
                $sql_adicional_4
                $sql_adicional_5
                $sql_adicional_6
                ORDER BY extrato_devolucao,faturamento DESC
            ";
        $res_notas = pg_query ($con,$sql);
        $qtde_notas=pg_num_rows($res_notas);


        $data = date("d-m-Y-H:i");
        $fileName = "extrato-posto-devolucao-$login_fabrica.$data.xls";
        $file = fopen("/tmp/{$fileName}", "w");

        $thead .=  "Status;"."Posto;"."Extrato;". "Nota Fiscal;". utf8_encode("Data Emiss�o").";Data Conferencia;". "Valor Nota;". "Cancelada;"."\r\n";
        fwrite($file, $thead);

        ?>

        <?php if ($login_fabrica == 50): // HD 107532 ?>
            <div class="mensagem msg-info">
                <p> Para enviar e-mail cobrando uma Nota Fiscal a um Posto, selecione a nota fiscal
                desejada e clique no bot�o <em>"Enviar E-mails"</em>. </p>
            </div>
        <?php endif; // fim HD 107532 ?>

        <?php
        ##### LEGENDAS - IN�CIO #####
        echo "<br>";
        echo "<center>";
        echo "<div >";
        echo "<table border='0' cellspacing='0' cellpadding='0' style='text-align:center;'>";
        echo "<tr height='18'>";
        echo "<td width='18' ><img src='imagens/status_verde.gif' width='10' valign='absmiddle'/></td>";
        echo "<td align='left'><font size='1'><b>&nbsp;";
        echo "Nota conferida";
        echo "</a></b></font></td><BR>";
        echo "</tr>";
        echo "<tr height='18'>";
        echo "<td width='18'><img src='imagens/status_vermelho.gif' width='10' valign='absmiddle'/></td>";
        echo "<td align='left'><font size='1'><b>&nbsp;";
        echo "Nota cancelada";
        echo "</a></b></font></td>";
        echo "</tr>";
        echo "<tr height='18'>";
        echo "<td width='18'><img src='imagens/status_amarelo.gif' width='10' valign='absmiddle'/></td>";
        echo "<td align='left'><font size='1'><b>&nbsp;";
        echo "Confer�ncia parcial da NF";
        echo "</a></b></font></td>";
        echo "</tr>";
        echo "<tr height='18'>";
        echo "<td width='18'><img src='imagens/status_cinza' width='10' valign='absmiddle'/></td>";
        echo "<td align='left'><font size='1'><b>&nbsp;";
        echo "Aguardando Confer�ncia";
        echo "</a></b></font></td>";
        echo "</table>";
        echo "</div>";
        echo "</center>";
        echo "<BR>";

        $lista  = "";

        $lista .= "<form method='post' name='frm_notas' action='$PHP_SELF'>";

        $lista .=  "<center><table border='0' cellpadding='4' cellspacing='0'  width='600px'>";

        $lista .=  "<tr ><td class='Titulo' colspan='100%'>Notas Fiscais de Devolu��o</td></tr>";

        $lista .=  "<tr class='menu_top' height='20'>";
        // HD 107532
        if ( $login_fabrica == 50 ) {
            $lista .= "<td><input type=\"checkbox\" id=\"inverter_chk_email\" /></td>";
        }
        // fim HD 107532
        $lista .=  "<td></td>";
//      $lista .=  "<td></td>";
        $lista .=  "<td>POSTO</td>";
        $lista .=  "<td>EXTRATO</td>";
        $lista .=  "<td>NOTA<br>FISCAL</td>";
        if($login_fabrica == 81) {
            $lista .="<td>OS</td>";
        }
        $lista .=  "<td>DATA<br>EMISS�O</td>";
        $lista .=  "<td>DATA<br>CONFERENCIA</td>";
        $lista .=  "<td>VALOR<br>NOTA</td>";
        $lista .=  "<td>CANCELADA</td>";
        if($login_fabrica == 35){
            $lista .=  "<td>SOLICITACAO<br>POSTAGEM</td>";
            $lista .=  "<td>RASTREIO</td>";
        }
        if ( $login_fabrica == 50 ) {
            $lista .=  "<td>POSTAGEM</td>";
        }
        $lista .=  "</tr>";
        $extrato_anterior = "";
        for ($i=0;$i<$qtde_notas;$i++){
            $faturamento            = pg_fetch_result($res_notas,$i,'faturamento');
            $emissao                = pg_fetch_result($res_notas,$i,'emissao');
            $conferencia            = pg_fetch_result($res_notas,$i,'conferencia');

            if($login_fabrica == 35){
                $numero_postagem     = pg_fetch_result($res_notas,$i,'numero_postagem');
                $rastreio            = pg_fetch_result($res_notas,$i,'conhecimento');
            }

            $cancelada              = pg_fetch_result($res_notas,$i,'cancelada');
            $devolucao_concluida    = pg_fetch_result($res_notas,$i,'devolucao_concluida');
            $nota_fiscal            = pg_fetch_result($res_notas,$i,'nota_fiscal');
            $nf_total_nota          = pg_fetch_result($res_notas,$i,'total_nota');
            $nf_valor_ipi           = pg_fetch_result($res_notas,$i,'valor_ipi');
            $nf_total_nota          = number_format($nf_total_nota,2,",",".");
            $extrato_devolucao      = pg_fetch_result($res_notas,$i,'extrato_devolucao');
            $nome_posto_1           = pg_fetch_result($res_notas,$i,'nome_posto');
            $cod_posto              = pg_fetch_result($res_notas,$i,'posto');
            $codigo_posto_1         = pg_fetch_result($res_notas,$i,'codigo_posto');
            $admin_lgr              = pg_fetch_result($res_notas,$i,'admin_lgr');


            if (strlen($conferencia)>0 AND $devolucao_concluida=='t' AND strlen($cancelada)==0){
                $status = "Conferida";
            }elseif(strlen(trim($conferencia))>0){
                $status = "Conferida Parcial";
            }elseif(strlen(trim($cancelada))>0){
                $status = "Cancelada";
            }else{
                $status = utf8_encode("N�o Conferida");
            }

            $tbody .= "$status;"."$codigo_posto_1 - ".utf8_encode("$nome_posto_1"). ";$extrato_devolucao" .";$nota_fiscal".";$emissao".";$conferencia".";$nf_total_nota".";\r\n";

            $cor = ($i%2==0) ? '#E9E9E9' : '#ffffff';

            if (strlen($admin_lgr)>0){
                $admin_lgr = "*";
            }

            $lista .= "<tr class='Conteudo' height='20' bgcolor='$cor' align='left' id='$extrato_devolucao'  >";

            /**
             * Exibir checkbox para marcar as NFs que o usu�rio deseja cobrar do posto.
             * Somente para Colormaq
             * HD 107532
             *
             * @author Augusto Pascutti <augusto.pascutti@telecontrol.com.br>
             */
            if ( $login_fabrica == 50 ) {
                $lista .= '<td> <input type="checkbox" name="nf_enviar_emails[]" class="checkable" value="'.$nota_fiscal.'" /> </td>';
            }
            // fim HD 107532
            if (strlen($conferencia)>0 AND $devolucao_concluida=='t' AND strlen($cancelada)==0){
                $lista .= "<td nowrap  align='center'><img src='imagens/status_verde.gif' alt='NF conferida em $conferencia'></td>";
            }elseif (strlen($cancelada)>0){
                $lista .= "<td nowrap  align='center'><img src='imagens/status_vermelho.gif' alt='NF cancelada em $cancelada'></td>";
            }elseif (strlen($conferencia)>0){
                    $lista .= "<td nowrap  align='center'><img src='imagens_admin/status_amarelo.gif' alt='Confer�ncia parcial da NF'></td>";
            }else{
                    $lista .= "<td nowrap  align='center'><img src='imagens/status_cinza.gif' alt='Aguardando confer�ncia'></td>";
            }

            $lista .= "<td nowrap  align='left' title='$codigo_posto_1 - $nome_posto_1'>$codigo_posto_1 - $nome_posto_1</td>";

            if (strlen($admin_lgr)>0){
                $lista .= "<td nowrap align='center'><span id='ajuda' title='Extrato liberado provisoriamente. O PA poder� visualizar o pr�ximo extrato sem a confirma��o de recebimento das notas de devolu��o desse m�s.'>$extrato_devolucao*</span></td>";
            }else{
                $lista .= "<td nowrap  align='center' title='Esta nota � referente ao extrato $extrato_devolucao'>$extrato_devolucao </td>";
            }

            $lista .= "<td nowrap align='center'><a href='javascript:verNota($faturamento)'> $nota_fiscal</a></td>";
            if($login_fabrica == 81) {
                $lista .= "<td nowrap align='center'><a href='javascript:verOs($extrato_devolucao)'>VER OS</a></td>";
            }
            $lista .= "<td nowrap  align='center' title='Nota emitida em $emissao'>$emissao</td>";
            $lista .= "<td nowrap  align='center' title='Conferida em $conferencia'>$conferencia</td>";
            
            $lista .= "<td nowrap  align='right'  title='Valor da Nota: R$ $nf_total_nota'>$nf_total_nota</td>";
            $lista .= "<td nowrap  align='center' title='Se estiver data, esta nota foi cancelada'>$cancelada</td>";
            if($login_fabrica == 35){
                $lista .= "<td nowrap  align='center' title='N�mero de Postagem $numero_postagem'>$numero_postagem</td>";
                $lista .= "<td nowrap  align='center' title='C�digo de Rastreio: $rastreio'>$rastreio</td>";
            }
            if ($login_fabrica == 50) {
                $total_colormaq = str_replace(',', '.', $nf_total_nota);
                $lista .= "<td><input type='button' name='btnacao_solicita_autorizacao' onclick='javascript: solicitaPostagemPosto({$cod_posto},{$total_colormaq},{$faturamento});' value='Consultar'></td>";
            }
            $lista .= "</tr>";
            if($login_fabrica == 81 ) {
                if(isset($extrato_devolucao) and $extrato_devolucao != $extrato_anterior) {
                    $sqlx = " SELECT tbl_os.os,tbl_os.sua_os,tbl_produto.referencia,tbl_produto.descricao
                            FROM tbl_faturamento_item
                            JOIN tbl_os USING(os)
                            JOIN tbl_produto USING(produto)
                            WHERE tbl_faturamento_item.extrato_devolucao = $extrato_devolucao";
                    $resx = pg_query($con,$sqlx);
                    if(pg_num_rows($resx) > 0){
                        for($j =0;$j<pg_num_rows($resx);$j++) {
                            $os         = pg_fetch_result($resx,$j,os);
                            $sua_os     = pg_fetch_result($resx,$j,sua_os);
                            $referencia = pg_fetch_result($resx,$j,referencia);
                            $descricao  = pg_fetch_result($resx,$j,descricao);
                            $lista.= "<tr rel='mostra_$extrato_devolucao' style='display:none'><td class='Conteudo'>OS:</td><td class='Conteudo'><a href='os_press.php?os=$os'>$sua_os</a></td><td nowrap colspan='3' class='Conteudo'>$referencia - $descricao</td></tr>";
                        }
                    }else{
                        $lista .="<tr rel='mostra_$extrato_devolucao' style='display:none'><td></td><td class='Conteudo' nowrap>Nenhuma OS encontrada</td></tr>";
                    }
                }

                if($devolucao_concluida=='t') {
                    $lista .="<tr style='background-color:#99FFFF' class='Conteudo'>";
                    $lista .= "<td nowrap></td><td nowrap colspan='100%'><a href='extrato_os_scrap.php?extrato_devolucao=$extrato_devolucao' target='_blank'>Clique aqui para cadastrar OS de Scrap</a></td>";
                    $lista .="</tr>";
                }
            }
            $extrato_anterior = $extrato_devolucao;
        }
        fwrite($file, $tbody);
        if (file_exists("/tmp/{$fileName}")) {
            system("mv /tmp/{$fileName} xls/{$fileName}");

//          echo "xls/{$fileName}";
        }

        $lista .=  "<tr align='left' >";
        $lista .=  "<td bgColor='#6B7EAB' colspan='100%' align='left'>&nbsp;";
        if ($login_fabrica <> 50){
            $lista .=  "<font style='font-size:12px;color:#FFFFFF'><img src='imagens/help1.gif' width='24' height='16' align='absmiddle' border='0' > <span id='ajuda' title='Se o extrato estiver como liberado provisoriamente, o PA poder� visualizar o pr�ximo extrato mesmo sem a F�brica ter confirmado o recebimento da Nota Fiscal de Devolu��o'>(*) Extrato liberado provisoriamente</span> </font>";
        }
        $lista .=  "</td>";
        $lista .=  "</tr>";

        $lista .=  "</table>";
        $lista .= "</form>";

        if ($qtde_notas==0){
            echo "<center><h2 style='font-size:12px;background-color:#D9E2EF;color:black;width:550px'>Nenhum registro encontrado</h2></center>";
        }else {
            //echo "<hr><br><b style='font-size:14px'>Postos Que Enviaram Pe�as Sem Confirma��o de Recebimento da F�brica</b><br>";
            echo "<br><br>";
            echo $lista;
            echo "<br>";
        }
    }

    // HD 107532
    if ( $login_fabrica == 50 && $qtde_notas > 0 ) {
        ?>
        <input type="submit" name="enviar_emails" id="enviar_emails" value="Enviar E-mails" />
        <?php
        echo "<br><br><br /> <a href='xls/extrato-posto-devolucao-$login_fabrica.$data.xls' target='_blank'><img src='imagens/excel.png' height='20px' width='20px' align='absmiddle'>&nbsp;&nbsp;Gerar Arquivo Excel</a> <br />";
    } else if ( isset($sucesso_envio_email) || isset($erros_envio_email) ) {
        ?>
        <div class="mensagem msg-info">
            <?php if ( isset($sucesso_envio_email) ): ?>
            <p>
                Foram enviados <?php echo $sucesso_envio_email; ?> e-mails.
            </p>
            <?php endif; ?>
            <?php if ( isset($erros_envio_email) && count($erros_envio_email) > 0 ): ?>
            <p>
                As seguintes notas fiscais n�o tiveram o e-mail enviado ao posto:
                <ul>
                    <?php foreach ($erros_envio_email as $nf): ?>
                        <li> <?php echo $nf; ?> </li>
                    <?php endforeach; ?>
                </ul>
            </p>
            <?php endif; ?>
        </div>
        <?php
        echo "<br><br><br /> <a href='xls/extrato-posto-devolucao-$login_fabrica.$data.xls' target='_blank'><img src='imagens/excel.png' height='20px' width='20px' align='absmiddle'>&nbsp;&nbsp;Gerar Arquivo Excel</a> <br />";
    }

    // fim 107532

######### FIM ##################################################

echo "<br>";

?>

<br>

<? include "rodape.php"; ?>