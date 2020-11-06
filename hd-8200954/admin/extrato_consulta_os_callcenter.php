<?php
include "dbconfig.php";
include "includes/dbconnect-inc.php";
include "funcoes.php";

$admin_privilegios = "financeiro";
include "autentica_admin.php";

if(isset($_POST["inibir_extrato"]) && $login_fabrica == 1){

    $inibir = $_POST["inibir"];
    $extrato = $_POST["extrato"];

    $inibir = ($inibir == "true") ? "baixado = CURRENT_DATE" : "baixado = null";

    $sql = "UPDATE tbl_extrato_extra SET {$inibir} WHERE extrato = {$extrato}";
    $res = pg_query($con, $sql);

    if(strlen(pg_last_error()) > 0){
        $dados = array("erro" => utf8_encode(pg_last_error()));
    }else{
        $dados = array("sucesso" => true);
    }

    exit(json_encode($dados));

}

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if(isset($_POST['aprovarCheck']) AND $_POST['aprovarCheck'] == 'ok'){
//    $nf_mao_obra = $_POST['val'];

    $nf_mao_obra = $_POST['nf_mao_obra'];
    $nf_devolucao = $_POST['nf_devolucao'];
    $entrega_transportadora = $_POST['entrega_transportadora'];
    $extrato = $_POST['extrato'];


    $entrega_transportadora = str_replace (" " , "" , $entrega_transportadora);
    $entrega_transportadora = str_replace ("-" , "" , $entrega_transportadora);
    $entrega_transportadora = str_replace ("/" , "" , $entrega_transportadora);
    $entrega_transportadora = str_replace ("." , "" , $entrega_transportadora);

    if (strlen ($entrega_transportadora) == 6) {
        $entrega_transportadora = "'".substr ($entrega_transportadora,0,4) . "20" . substr ($entrega_transportadora,4,2)."'";
    }

    if (strlen ($entrega_transportadora) > 0) {
        $entrega_transportadora = substr ($entrega_transportadora,0,2) . "/" . substr ($entrega_transportadora,2,2) . "/" . substr ($entrega_transportadora,4,4);
        if (strlen ($entrega_transportadora) < 8) $entrega_transportadora = date ("d/m/Y");
        $entrega_transportadora = "'".substr ($entrega_transportadora,6,4) . "-" . substr ($entrega_transportadora,3,2) . "-" . substr ($entrega_transportadora,0,2)."'";
        } else {
        $entrega_transportadora = 'null';
    }

    $res = pg_query($con,"BEGIN TRANSACTION");

    $sqlAprova = "SELECT tbl_extrato.extrato,
                    tbl_extrato.posto,
                    tbl_posto.nome,
                    tbl_posto.cnpj
                FROM tbl_extrato
                JOIN tbl_posto_fabrica ON tbl_extrato.posto = tbl_posto_fabrica.posto
                JOIN tbl_posto ON tbl_extrato.posto = tbl_posto.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
                WHERE tbl_extrato.extrato = $extrato";
    $resAprova = pg_query($con,$sqlAprova);

    $posto                          = pg_fetch_result($resAprova, 0, posto);
    $posto_nome                     = pg_fetch_result($resAprova, 0, nome);
    $cnpj                           = pg_fetch_result($resAprova, 0, cnpj);


    if(strlen($nf_devolucao) == 0){
        $nf_devolucao = 'null';
    }

    if(strlen($nf_mao_obra) == 0 ){
        $nf_mao_obra = 'null';
    }

    $sql = "UPDATE tbl_extrato_extra SET
                nota_fiscal_mao_de_obra     = '$nf_mao_obra',
                nota_fiscal_devolucao       = '$nf_devolucao',
                data_entrega_transportadora = $entrega_transportadora
            WHERE extrato = $extrato";



    $res = pg_query($con,$sql);

    # Estava comentado , entao descomentei. Pq comentaram?  N�o tem a explicacao.
    # Estou liberando. HD 4846
    //HD 145478 - Gravando quem aprovou o extrato

    $sql = "
    UPDATE
    tbl_extrato_extra

    SET
    admin = $login_admin

    WHERE
    extrato = $extrato
    ";
    $res = pg_query($con, $sql);



    $sql = "SELECT fn_aprova_extrato($posto,$login_fabrica,$extrato)";
    $res = pg_query($con,$sql);
    $msg_erro = pg_errormessage($con);


    if (strlen ($msg_erro) == 0) {
        $res = pg_query ($con,"COMMIT TRANSACTION");

        echo $extrato; exit;
    }else{
        $res = @pg_query ($con,"ROLLBACK TRANSACTION");
        echo "erro;$sql ==== $msg_erro "; exit;
    }

}

function getThead(){

    if($login_fabrica == 86){
        return "<thead>
                   <tr >
                   	   <th> C�digo</th>
                	   <th> Nome do Posto</th>
    		   <th> UF</th>
                	   <th> Extrato</th>
    		   <th> Marca</th>
    		   <th> Data</th>
    		   <th> Qtde OS</th>
    		   <th> Total</th>
    		   <th> Data de Baixa</th>
          	       </tr>
               </thead>";
    }else{
        return "<thead>
                   <tr >
                       <th> C�digo</th>
                       <th> Nome do Posto</th>
                       <th> UF</th>
                       <th> Extrato</th>
                       <th> Data</th>
                       <th> Qtde OS</th>
                       <th> Total Cortesia</th>
                       <th> Total Geral</th>
                       <th> N.F M�o de Obra</th>
                       <th> N.F Remessa</th>
                       <th> Data Coleta</th>
                       <th> Entrega Transportadora</th>
                       <th> Auditado em</th>
                       <th> Auditor</th>
                       <th> Valores Adicionais</th>
                   </tr>
               </thead>";
    }


}

function getTbody($res){
    $tbody = "<tbody>";

    for ($i = 0; $i < pg_num_rows($res); $i++) {

        $result = pg_fetch_object($res, $i);

        if($login_fabrica == 86){
            $tbody .= "<tr>";
    		$tbody	 .= "<td >".$result->codigo_posto ."</td>";
    		$tbody	 .= "<td >".$result->nome."</td>";
    		$tbody	 .= "<td >".$result->estado ."</td>";
    		$tbody	 .= "<td >".$result->extrato ."</td>";
    		$tbody	 .= "<td >".mostraMarcaExtrato($result->extrato) ."</td>";
    		$tbody	 .= "<td >".$result->data_geracao."</td>";
    		$tbody	 .= "<td >".getQtdeOS($result->extrato)."</td>";
    		$tbody	 .= "<td >".$result->total ."</td>";
    		$tbody	 .= "<td >".$result->baixado ."</td>";
            $tbody .= "</tr>";
        }else{

            if(strlen($result->aprovado) == 0){
                $aprovar = "Aprovar";
            }else{
                $aprovar = "Aprovado";
            }

            $tbody .= "<tr>";
            $tbody   .= "<td >".$result->codigo_posto ."</td>";
            $tbody   .= "<td >".$result->nome."</td>";
            $tbody   .= "<td >".$result->estado ."</td>";
            $tbody   .= "<td >".$result->extrato ."</td>";
            $tbody   .= "<td >".$result->data_geracao."</td>";
            $tbody   .= "<td >".getQtdeOS($result->extrato)."</td>";
            $tbody   .= "<td >".$result->total_cortesia."</td>";
            $tbody   .= "<td >".$result->total ."</td>";
            $tbody   .= "<td >".$result->nota_fiscal_mao_de_obra ."</td>";
            $tbody   .= "<td >".$result->nota_fiscal_devolucao ."</td>";
            $tbody   .= "<td >".$result->data_coleta ."</td>";
            $tbody   .= "<td >".$result->data_entrega_transportadora ."</td>";
            $tbody   .= "<td >".$result->aprovado ."</td>";
            $tbody   .= "<td >".$result->nome_completo ."</td>";
            $tbody   .= "<td >".$aprovar ."</td>";
            $tbody .= "</tr>";
        }
    }
    $tbody .= "</tbody>";
    return $tbody;
}

function getTFoot($res){
    return "<tfoot>
               <tr>
                   <td> Total de Registros: ".pg_num_rows($res). "</td>
              </tr>
            </tfoot>";
}
function montaArquivo($fp, $res){
    $tHead = "<table>". getThead();
    $tBody = getTbody($res);
    $tFoot = getTFoot($res);

    fwrite($fp, $tHead.$tBody.$tFoot);
}

/* ver admin/conta_os_ajax.php  */
function getQtdeOS($extrato){
    global $con;

    $sql = "SELECT count(*) as qtde_os FROM tbl_os_extra WHERE extrato = $extrato";
    $res = pg_query($con,$sql);

    return pg_fetch_result($res, 0, "qtde_os");
}
function getCamposGroupThermoSystem(){
    return "  GROUP BY PO.posto ,
              PO.nome ,
              PO.cnpj ,
              PF.contato_estado ,
              PF.contato_email  ,
              PF.credenciamento ,
              PF.codigo_posto ,
              PF.distribuidor ,
              PF.imprime_os ,
              TP.descricao  ,
              EX.extrato ,
              EX.bloqueado ,
              EX.liberado ,
              EX.estoque_menor_20 ,
              EX.aprovado,
              EX.protocolo,
              EX.data_geracao,
              EX.data_geracao,
              EX.total ,
              EX.pecas ,
              EP.baixa_extrato";
}
function getCamposSqlThermosystem(){

    return "
              PO.posto ,
              PO.nome ,
              PO.cnpj ,
              PF.contato_estado as estado ,
              PF.contato_email AS email ,
              PF.credenciamento ,
              PF.codigo_posto ,
              PF.distribuidor ,
              PF.imprime_os ,
              TP.descricao AS tipo_posto ,
              EX.extrato ,
              EX.bloqueado ,
              EX.liberado ,
              EX.estoque_menor_20 ,
              TO_CHAR (EX.aprovado,'dd/mm/yyyy') AS aprovado ,
              LPAD (EX.protocolo,6,'0') AS protocolo ,
              TO_CHAR (EX.data_geracao,'dd/mm/yyyy') AS data_geracao ,
              EX.data_geracao AS xdata_geracao,
              EX.total ,
              EX.pecas ,
              count(tbl_os_extra.os) as qtde,
              EP.baixa_extrato
                ";
}
//HD 205958: Um extrato pode ser modificado at� o momento que for APROVADO pelo admin. Ap�s aprovado
//           n�o poder� mais ser modificado em hip�tese alguma. Acertos dever�o ser feitos com lan�amento
//           de extrato avulso. Verifique as regras definidas neste HD antes de fazer exce��es para as f�bricas
//           SER� LIBERADO AOS POUCOS, POIS OS PROGRAMAS N�O EST�O PARAMETRIZADOS
//           O array abaixo define quais f�bricas est�o enquadradas no processo novo
$fabricas_acerto_extrato = array(43, 45);

//HD 237498: Barrar libera��o de Extrato caso tenha OS em Interven��o de KM
//A funcao abaixo verifica se o extrato tem OS com KM pendente
$intervencao_km_extrato = array(30, 72, 129);
if($login_fabrica == 1){
    function verificaTipoGeracao($extrato){
        global $con;
        $sqlVerificaTipoGeracao = " SELECT obs
                                    FROM tbl_extrato_extra
                                    WHERE extrato = {$extrato} ";
        $resVerificaTipoGeracao = pg_query($con, $sqlVerificaTipoGeracao);
        if(pg_num_rows($resVerificaTipoGeracao) > 0 ){
            $obs = pg_fetch_result($resVerificaTipoGeracao, 0,"obs");

            return $obs;

        }else{
            return "";
        }
    }
}
function verifica_km_pendente_extrato($extrato) {
    global $con;

    //Verifica se a OS em algum momento entrou em interven��o de KM, status 98 | Aguardando aprova��o da KM
    $sql = "
	    SELECT OEX.os,
	    (SELECT status_os 
		FROM tbl_os_status  
		WHERE tbl_os_status.os = OEX.os 
		AND status_os IN(98,99,100,101) 
		ORDER BY data DESC LIMIT 1) AS status_os
	    FROM tbl_os_extra OEX
	    WHERE OEX.extrato=$extrato
	    ";
    $res_km = pg_query($con, $sql);

    if (pg_num_rows($res_km)) {
        //Caso a OS algum dia tenha entrado em interven��o de KM, precisa ser verificado se saiu todas as vezes
        //A OS pode sair da interven��o de KM por um dos status abaixo:
        // 99 | KM Aprovada
        //100 | KM Aprovada com altera��o
        //101 | km Recusada
	    $n_intervencao_km = pg_fetch_all($res_km);
	    
	    $km_pendente = false;

	   foreach($n_intervencao_km AS $key => $value){
		   
		   if($value['status_os'] == 98){
			$km_pendente = true;
		   }
	   }

    }else {
        $km_pendente = false;
    }

    return($km_pendente);
}

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
            $q    = substr(preg_replace('/\D/', '', $q),0, 14);
            $sql .= " AND tbl_posto.cnpj = '$q' ";
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

if($ajax=='conta'){
            if($login_fabrica==45){//HD 39377 12/9/2008
                $sql = "SELECT count(*) as qtde_os
                        FROM tbl_os
                        JOIN tbl_os_extra USING(os)
                        WHERE tbl_os.mao_de_obra notnull
                        and tbl_os.pecas       notnull
                        and ((
                                SELECT tbl_os_status.status_os
                                FROM tbl_os_status
                                WHERE tbl_os_status.os = tbl_os.os
                                ORDER BY tbl_os_status.data DESC LIMIT 1
                                ) IS NULL
                            OR (SELECT tbl_os_status.status_os
                                FROM tbl_os_status WHERE tbl_os_status.os = tbl_os.os
                                ORDER BY tbl_os_status.data DESC LIMIT 1
                                ) NOT IN (15)
                            )
                        and tbl_os_extra.extrato = $extrato";
            }else{
                $sql = "SELECT count(*) as qtde_os FROM tbl_os_extra WHERE extrato = $extrato";
            }
            $rres = pg_query($con,$sql);
            if(pg_num_rows($rres)>0){
                $qtde_os = pg_fetch_result($rres,0,qtde_os);
            }
            echo "ok|$qtde_os";
            exit;
}
// AJAX -> solicita a exporta��o dos extratos
if (strlen($_GET["exportar"])>0){
    //include "../ajax_cabecalho.php";
    //system("/www/cgi-bin/bosch/exporta-extrato.pl",$ret);
    $dados = "$login_fabrica\t$login_admin\t".date("d-m-Y H:m:s");
    exec ("echo '$dados' > /tmp/bosch/exporta/pronto.txt");
    echo "ok|Exporta��o conclu�da com sucesso! Dentro de alguns minutos os arquivos de exporta��o estar�o dispon�veis no sistema.";
    exit;
}
// FIM DO AJAX -> solicita a exporta��o dos extratos


// AJAX -> APROVA O EXTRATO SELECIONADO
// ATEN��O: NESTE ARQUIVO EXISTEM DUAS ROTINAS PARA APROVAR EXTRATO, UMA COM AJAX E OUTRA SEM
//          QUANDO FOR MODIFICAR UMA, VERIFIQUE SE � NECESS�RIO MODIFICAR A OUTRA
if ($_GET["ajax"] == "APROVAR" && strlen($_GET["aprovar"])>0 && strlen($_GET["posto"])>0){

    $posto   = $_GET["posto"];
    $aprovar = $_GET["aprovar"];

    $res = pg_query($con,"BEGIN TRANSACTION");

    if ($login_fabrica == 1) {

        $sql = "SELECT posto
                FROM tbl_tipo_gera_extrato
                WHERE fabrica = $login_fabrica
                AND posto = $posto
                AND envio_online";

        $res = pg_query($con, $sql);

        if (pg_num_rows($res)) {

            $envio_online = true;

            $sql = "INSERT INTO tbl_extrato_status (
                        extrato    ,
                        fabrica    ,
                        obs        ,
                        data       ,
                        pendente   ,
                        pendencia
                    ) VALUES (
                        $aprovar          ,
                        $login_fabrica,
                        'Aguardando NF de servi�os',
                        current_timestamp ,
                        't'         ,
                        't'
                    )";

            $res = pg_query($con,$sql);

        }

    }

    if ($login_fabrica == 20 || $login_fabrica == 14) {
        $nf_mao_de_obra = $_GET["nf_mao_de_obra"];
        if (strlen(trim($nf_mao_de_obra))==0) {
            $nf_mao_de_obra = 'null';
        }

        $nf_devolucao   = $_GET["nf_devolucao"];
        if (strlen(trim($nf_devolucao))==0) {
            $nf_devolucao = 'null';
        }

        $data_entrega_transportadora = $_GET["data_entrega_transportadora"];
        $data_entrega_transportadora = str_replace (" " , "" , $data_entrega_transportadora);
        $data_entrega_transportadora = str_replace ("-" , "" , $data_entrega_transportadora);
        $data_entrega_transportadora = str_replace ("/" , "" , $data_entrega_transportadora);
        $data_entrega_transportadora = str_replace ("." , "" , $data_entrega_transportadora);

        if (strlen ($data_entrega_transportadora) == 6) {
            $data_entrega_transportadora = substr ($data_entrega_transportadora,0,4) . "20" . substr ($data_entrega_transportadora,4,2);
        }

        if (strlen ($data_entrega_transportadora) > 0) {
            $data_entrega_transportadora = substr ($data_entrega_transportadora,0,2) . "/" . substr ($data_entrega_transportadora,2,2) . "/" . substr ($data_entrega_transportadora,4,4);
            if (strlen ($data_entrega_transportadora) < 8) $data_entrega_transportadora = date ("d/m/Y");
            $data_entrega_transportadora = substr ($data_entrega_transportadora,6,4) . "-" . substr ($data_entrega_transportadora,3,2) . "-" . substr ($data_entrega_transportadora,0,2);
            } else {
            $data_entrega_transportadora = 'null';
        }

        $sql = "UPDATE tbl_extrato_extra SET
                    nota_fiscal_mao_de_obra     = '$nf_mao_de_obra',
                    nota_fiscal_devolucao       = '$nf_devolucao',
                    data_entrega_transportadora = '$data_entrega_transportadora'
                WHERE extrato = $aprovar";
        #$res = pg_query($con,$sql);
        # Estava comentado , entao descomentei. Pq comentaram?  N�o tem a explicacao.
        # Estou liberando. HD 4846

        //HD 145478 - Gravando quem aprovou o extrato
        $sql = "
        UPDATE
        tbl_extrato_extra

        SET
        admin = $login_admin

        WHERE
        extrato = $aprovar
        ";
        $res = pg_query($con, $sql);
    }

    $sql = "
    UPDATE
    tbl_extrato_extra

    SET
    admin = $login_admin

    WHERE
    extrato = $aprovar
    ";
    $res = pg_query($con, $sql);


    $sql = "SELECT fn_aprova_extrato($posto,$login_fabrica,$aprovar)";
    $res = pg_query($con,$sql);
    $msg_erro = pg_errormessage($con);

    if (strlen ($msg_erro) == 0) {
        $res = pg_query ($con,"COMMIT TRANSACTION");
        echo "ok;$aprovar";
    }else{
        $res = @pg_query ($con,"ROLLBACK TRANSACTION");
        echo "erro;$sql ==== $msg_erro ";
    }
    exit;
}

// FIM DO AJAX -> APROVA O EXTRATO SELECIONADO

$msg_erro = "";

if (strlen($_POST["btnacao"]) > 0) $btnacao = trim(strtolower($_POST["btnacao"]));
if (strlen($_GET["btnacao"])  > 0) $btnacao = trim(strtolower($_GET["btnacao"]));

if (strlen($_POST["posto"]) > 0) $posto = $_POST["posto"];
if (strlen($_GET["posto"])  > 0) $posto = $_GET["posto"];

if (strlen($_GET["liberar"]) > 0) $liberar = $_GET["liberar"];



if (strlen($liberar) > 0){
    //HD 237498: Barrar libera��o de Extrato caso tenha OS em Interven��o de KM
    if (in_array($login_fabrica, $intervencao_km_extrato)) {
        //Verifica se a OS em algum momento entrou em interven��o de KM, status 98 | Aguardando aprova��o da KM
        $sql = "
		SELECT OEX.os,
		(SELECT status_os 
		FROM tbl_os_status  
		WHERE tbl_os_status.os = OEX.os 
		AND status_os IN(98,99,100,101) 
		ORDER BY data DESC LIMIT 1) AS status_os
		FROM tbl_os_extra OEX
		WHERE OEX.extrato=$extrato
		";
        $res_km = pg_query($con, $sql);

        if (pg_num_rows($res_km)) {
            //Caso a OS algum dia tenha entrado em interven��o de KM, precisa ser verificado se saiu todas as vezes
            //A OS pode sair da interven��o de KM por um dos status abaixo:
            // 99 | KM Aprovada
            //100 | KM Aprovada com altera��o
            //101 | km Recusada

	    $n_intervencao_km = pg_fetch_all($res_km);
	     
	     $km_pendente = false;

	    foreach($n_intervencao_km AS $key => $value){                                                                                     
		    if($value['status_os'] == 98){
			$km_pendente = true;
		    }
	    }

            if ($km_pendente == true) {
                $msg_erro = "Aten��o: existem OS em interven��o neste extrato ($liberar). Para que o extrato seja liberado � necess�rio aprovar ou reprovar todas as interven��es de suas OS antes. Consulte o extrato para maiores detalhes.";
            }
        }
    }

    /*IGOR HD 17677 - 04/06/2008 */
    if($login_fabrica ==11 or $login_fabrica ==25 || ($login_fabrica == 30)){
        $sql="SELECT recalculo_pendente
                from tbl_extrato
                where extrato=$liberar
                and fabrica=$login_fabrica";
        $res = @pg_query($con,$sql);
        $recalculo_pendente=pg_fetch_result($res,0,recalculo_pendente);
        if($recalculo_pendente=='t'){
            if ($login_fabrica == 30) {
                $msg_erro = "O extrato $liberar est� pendente de rec�lculo, recalcular antes de liberar";
                $extrato = $liberar;
            }
            else {
                $msg_erro="Este extrato ser� recalculado de noite e poder� ser liberado amanh�";
            }
        }
    }
    if (strlen($msg_erro)==0){

        $res = pg_query($con,"BEGIN TRANSACTION");

        //HD 205958: N�o pode aprovar nenhum extrato na libera��o, � uma falha no conceito do neg�cio.
        //           antes de atender qualquer solicita��o das f�bricas concernentes a isto, verificar conceitos
        //           definidos neste chamado. Apagadas 3 linhas abaixo, verificar nao_sync caso necess�rio
        if (in_array($login_fabrica, $fabricas_acerto_extrato)) {
        }
        else {
            //HD 205958: Este conceito est� errado, um extrato nunca pode ser aprovado na libera��o. Esta linha
            //           est� aqui provis�riamente enquanto arrumamos os conceitos das f�bricas
            $aprovar_na_liberacao = "aprovado = current_timestamp,";
        }
        $sql = "
        UPDATE
        tbl_extrato

        SET
        liberado = current_date,
        $aprovar_na_liberacao
        admin = $login_admin

        WHERE extrato = $liberar
        "; //Corrigido! HD 44022
        $res = @pg_query($con,$sql);
        $msg_erro = @pg_errormessage($con);

        //Wellington 14/12/2006 - ENVIA EMAIL PARA O POSTO QDO O EXTRATO � LIBERADO
        /*IGOR HD 17677 - 04/06/2008 */
        /*HD 138813 MLG - N�o enviada para alguns postos porque na tbl_posto n�o tem e-mail.
                          Alterado para pegar das duas, de prefer�ncia da tbl_posto_fabrica */
        if (strlen($msg_erro)==0 and in_array($login_fabrica, array(11, 24, 25, 40))) {
            include 'email_comunicado.php'; // Fun��es para enviar e-mail e inserir comunicado para o Posto
            $sql = "
            SELECT
            CASE
                            WHEN contato_email IS NULL THEN tbl_posto.email
                            ELSE contato_email
                        END AS email,
            tbl_posto_fabrica.posto

            FROM
            tbl_posto_fabrica
                        JOIN tbl_extrato USING (posto,fabrica)
                        JOIN tbl_posto ON tbl_extrato.posto = tbl_posto.posto

            WHERE
            extrato = $liberar";

            $res = @pg_query($con,$sql);

            if (@pg_num_rows($res)) {
                //Se tem aviso, pega o valor, tanto se foi por GET como POST...
                $msg_aviso    = $_REQUEST['msg_aviso'];
                $xposto       = trim(pg_fetch_result($res,0,posto));
                $destinatario = trim(pg_fetch_result($res,0,email));
                $assunto      = "SEU EXTRATO (N� $liberar) FOI LIBERADO";
                $mensagem     =  "* O EXTRATO N�".$liberar." EST� LIBERADO NO SITE: www.telecontrol.com.br *<br><br>".$msg_aviso ;

                $r_email    = "<helpdesk@telecontrol.com.br>";
                $remetente  = "TELECONTROL";
		/*
                if ($login_fabrica == 11) {
                    $r_email    = "<sac@lenoxx.com.br>";
                    $remetente  = "LENOXXSOUND";
		}*/
                if ($login_fabrica == 24) {
                    $r_email    = "<suggat@suggar.com.br>";
                    $remetente  = "SUGGAR FINANCEIRO";
                }
                elseif ($login_fabrica == 25) {
                    $r_email    = "<ronaldo@telecontrol.com.br>";
                    $remetente  = "HBFLEX FINANCEIRO";
                }

                $headers = "Return-Path:$r_email \nFrom:".$remetente.
                       " $r_email \nBcc:$r_email \nContent-type: text/html\n";

                enviar_email($r_email, $destinatario, $assunto, $mensagem, $remetente, $headers, true);
                gravar_comunicado("Extrato dispon�vel", $assunto, $mensagem, $xposto, true);
            }
            }

        //wellington liberar
        // Fabio 02/10/2007
        // Alterado por Fabio -> tbl_faturamento.emissao <  '2007-10-21' // HD 600
        // Depois da libera��o, alterar para tbl_faturamento.emissao < current_date - interval'15 day'
        /* LENOXX - SETA EXTRATO DE DEVOLU��O PARA OS FATURAMENTOS */
        /*IGOR HD 17677 - 04/06/2008 */
        if (strlen($liberar) > 0 and strlen($msg_erro)==0 and ($login_fabrica==11 OR $login_fabrica == 25 )) {
            if($login_fabrica == 25 ) {
                $sql = "SELECT TO_CHAR(data_geracao-interval '15 days','YYYY-MM-DD') AS data_limite
                        FROM tbl_extrato
                        WHERE extrato = $liberar;";
            }else{
                $sql = "SELECT TO_CHAR(data_geracao-interval '1 month','YYYY-MM-21') AS data_limite
                        FROM tbl_extrato
                        WHERE extrato = $liberar;";
            }

            $res = pg_query($con,$sql);
            $data_limite_nf = trim(pg_fetch_result($res,0,data_limite));

            $sql = "UPDATE tbl_faturamento SET extrato_devolucao = $liberar
                    WHERE  tbl_faturamento.fabrica = $login_fabrica
                    AND    tbl_faturamento.posto   = $xposto
                    AND    tbl_faturamento.extrato_devolucao IS NULL
                    AND    tbl_faturamento.emissao > '2007-08-30'
                    AND    tbl_faturamento.emissao < '$data_limite_nf'
                    AND    (tbl_faturamento.cfop ILIKE '%59%' OR tbl_faturamento.cfop ILIKE '%69%')
                    ";
            // AND    tbl_faturamento.emissao <  current_date - interval'15 day'
            $res = pg_query($con,$sql);

            $sql = "DELETE FROM tbl_extrato_lgr WHERE extrato = $liberar";
            $res = pg_query($con,$sql);

            $sql = "INSERT INTO tbl_extrato_lgr (extrato, posto, peca, qtde) (
                SELECT tbl_extrato.extrato, tbl_extrato.posto, tbl_faturamento_item.peca, SUM (tbl_faturamento_item.qtde)
                FROM tbl_extrato
                JOIN tbl_faturamento      ON tbl_extrato.extrato         = tbl_faturamento.extrato_devolucao
                JOIN tbl_faturamento_item ON tbl_faturamento.faturamento = tbl_faturamento_item.faturamento
                WHERE tbl_extrato.fabrica = $login_fabrica
                AND   tbl_extrato.extrato = $liberar
                GROUP BY tbl_extrato.extrato, tbl_extrato.posto, tbl_faturamento_item.peca
                ) ;";
            $res = pg_query($con,$sql);
        }

        if (strlen ($msg_erro) == 0) {
            if($login_fabrica == 52){
                $sql = "SELECT
                            tbl_posto_fabrica.contato_nome      AS nome,
                            tbl_posto_fabrica.contato_email     AS email
                        FROM
                            tbl_extrato
                        JOIN
                            tbl_posto_fabrica ON (tbl_posto_fabrica.posto = tbl_extrato.posto)
                        WHERE
                            tbl_extrato.extrato = $liberar
                            AND tbl_posto_fabrica.fabrica = $login_fabrica;";
                $res = pg_query($con, $sql);

                if (@pg_num_rows($res) == 0) {
                    $sql = "SELECT
                                tbl_posto.nome  AS nome,
                                tbl_posto.email AS email
                            FROM
                                tbl_extrato
                            JOIN
                                tbl_posto ON (tbl_posto.posto = tbl_extrato.posto)
                            WHERE
                                tbl_extrato.extrato = $liberar
                                AND tbl_extrato.fabrica = $login_fabrica;";
                    $res = pg_query($con, $sql);
                }

                $email_posto = @pg_fetch_result($res,0,'email');
                $nome_posto = @pg_fetch_result($res,0,'nome');

                $sql   = "SELECT email
                FROM tbl_admin
                WHERE tbl_admin.admin = {$login_admin}";

                $res   = pg_query($con,$sql);
                $email_admin = pg_fetch_result($res,0,'email');

                if($email_posto != ""){
                    $remetente    = $email_admin;
                    $destinatario = $email_posto;
                    $assunto      = "Extrato Fricon $liberar liberado!\n";
                    $mensagem     = "Prezado(a) {$nome_posto},\n";
                    $mensagem    .="<br /><br />O(s) extrato(s) Fricon N� $liberar foi liberado, favor enviar a nota fiscal de presta��o de servi�os para pagamento e informar no corpo da nota os dados banc�rios\n";
                    $mensagem    .="<br /><br />----------\n";
                    $mensagem    .="<br />Qualquer d�vida entrar em contato com a Fricon.";
                    $headers= "From:".$remetente."\nContent-type: text/html\n";

                    mail($destinatario, utf8_encode($assunto), utf8_encode($mensagem), $headers);
                }else{
                    echo "<script language='javascript'>alert('N�o foi possivel encontrar o email do posto, favor atualizar os dados');</script>";
                }
            }

            //$res = @pg_query ($con,"ROLLBACK TRANSACTION");
            $res = pg_query ($con,"COMMIT TRANSACTION");
        }else{
            $res = @pg_query ($con,"ROLLBACK TRANSACTION");
        }
    }
}

if ($btnacao == 'liberar_tudo'){
    if (strlen($_POST["total_postos"]) > 0) $total_postos = $_POST["total_postos"];

    $res = pg_query ($con,"BEGIN TRANSACTION");
    $extrato_km_pendente = array();

    for ($i=0; $i < $total_postos; $i++) {
        $extrato    = $_POST["liberar_".$i];
        $imprime_os = $_POST["imprime_os_".$i];
        $km_pendente = false;

        //HD 237498: Barrar libera��o de Extrato caso tenha OS em Interven��o de KM
        if (in_array($login_fabrica, $intervencao_km_extrato) && $extrato) {
            $km_pendente = verifica_km_pendente_extrato($extrato);
        }
        else {
            $km_pendente = false;
        }

        if ($km_pendente) {
            $extrato_km_pendente[] = $extrato;
        }
        else {
            if (strlen($extrato) > 0 AND strlen($msg_erro) == 0) {
                $sql = "UPDATE tbl_extrato SET liberado = current_date, admin = $login_admin ";

                //HD 205958: N�o pode aprovar nenhum extrato na libera��o, � uma falha no conceito do neg�cio.
                //           antes de atender qualquer solicita��o das f�bricas concernentes a isto, verificar conceitos
                //           definidos neste chamado. Apagadas 3 linhas abaixo, verificar nao_sync caso necess�rio
                if (in_array($login_fabrica, $fabricas_acerto_extrato)) {
                }
                elseif (in_array($login_fabrica,array(6,7,11,30,14,15,24,25,35,43,45,46,50,51,59,66,74,80,52,85,88,94,99,90,91)) or $login_fabrica > 99) {
                    //HD 205958: Este conceito est� errado, um extrato nunca pode ser aprovado na libera��o. Esta linha
                    //           est� aqui provis�riamente enquanto arrumamos os conceitos das f�bricas
                    $sql .= ", aprovado = current_timestamp ";
                }

                $sql .= "WHERE  tbl_extrato.extrato = $extrato
                         and    tbl_extrato.fabrica = $login_fabrica";
                         //echo $sql;
                $res = pg_query($con,$sql);
                $msg_erro = @pg_errormessage($con);

                //Wellington 14/12/2006 - ENVIA EMAIL PARA O POSTO QDO O EXTRATO � LIBERADO
                /*IGOR HD 17677 - 04/06/2008 */
                if (strlen($msg_erro)==0 and ($login_fabrica==11 OR $login_fabrica==25)) {
                    include 'email_comunicado.php'; // Fun��es para enviar e-mail e inserir comunicado para o Posto
                    $sql = "SELECT CASE
                                    WHEN contato_email IS NULL
                                        THEN tbl_posto.email
                                    ELSE contato_email
                                    END AS email, tbl_posto_fabrica.posto FROM tbl_posto_fabrica
                                JOIN tbl_extrato USING (posto,fabrica)
                                JOIN tbl_posto ON tbl_extrato.posto = tbl_posto.posto
                            WHERE extrato = $extrato";
                    $res = pg_query($con,$sql);

        //          Se tem aviso, pega o valor, tanto se foi por GET como POST...
                    $msg_aviso    = (isset($_REQUEST['msg_aviso']))?"AVISO: ".$_REQUEST['msg_aviso']."<BR><BR><BR>":"";
                    $xposto       = trim(pg_fetch_result($res,0,posto));
                    $destinatario = trim(pg_fetch_result($res,0,email));
                    $assunto      = "SEU EXTRATO (N� $extrato) FOI LIBERADO";
                    $mensagem     =  "* O EXTRATO N�".$extrato." EST� LIBERADO NO SITE: www.telecontrol.com.br *<br><br>".$msg_aviso ;

                    $r_email    = "<helpdesk@telecontrol.com.br>";
                    $remetente  = "TELECONTROL";
                    //if ($login_fabrica == 11) {
                    //    $r_email    = "<sac@lenoxx.com.br>";
                    //    $remetente  = "LENOXXSOUND-FINANCEIRO";
                    //}
    //              if ($login_fabrica == 24) {
    //                  $r_email    = "<suggat@suggar.com.br>";
    //                  $remetente  = "SUGGAR FINANCEIRO";
    //              }
                    if ($login_fabrica == 25) {
                        $r_email    = "<ronaldo@telecontrol.com.br>";
                        $remetente  = "HBFLEX FINANCEIRO";
                    }
                    $headers    = "Return-Path:$r_email \nFrom:".$remetente.
                                  " $r_email\nBcc:$r_email \nContent-type: text/html\n";

                    enviar_email($r_email, utf8_encode($destinatario), utf8_encode($assunto), $mensagem, $remetente, $headers, true);
                    gravar_comunicado("Extrato dispon�vel", $assunto, $mensagem, $xposto, true);
                }
            }

            //wellington liberar
            /* LENOXX - SETA EXTRATO DE DEVOLU��O PARA OS FATURAMENTOS */
            /*IGOR HD 17677 - 04/06/2008 */
            if (strlen($extrato) > 0 and strlen($msg_erro)==0 and ($login_fabrica==11 OR $login_fabrica==25)) {

                $sql = "SELECT TO_CHAR(data_geracao-interval '1 month','YYYY-MM-21') AS data_limite
                        FROM tbl_extrato
                        WHERE extrato = $extrato;";
                $res = pg_query($con,$sql);
                $data_limite_nf = trim(pg_fetch_result($res,0,data_limite));

                $sql = "UPDATE tbl_faturamento SET extrato_devolucao = $extrato
                        WHERE  tbl_faturamento.fabrica = $login_fabrica
                        AND    tbl_faturamento.posto   = $xposto
                        AND    tbl_faturamento.extrato_devolucao IS NULL
                        AND    tbl_faturamento.emissao >  '2007-08-30'
                        AND    tbl_faturamento.emissao < '$data_limite_nf'
                        AND    (tbl_faturamento.cfop ILIKE '%59%' OR tbl_faturamento.cfop ILIKE '%69%')
                        ";
                $res = pg_query($con,$sql);

                $sql = "DELETE FROM tbl_extrato_lgr WHERE extrato = $extrato";
                $res = pg_query($con,$sql);

                $sql = "INSERT INTO tbl_extrato_lgr (extrato, posto, peca, qtde) (
                    SELECT tbl_extrato.extrato, tbl_extrato.posto, tbl_faturamento_item.peca, SUM (tbl_faturamento_item.qtde)
                    FROM tbl_extrato
                    JOIN tbl_faturamento      ON tbl_extrato.extrato         = tbl_faturamento.extrato_devolucao
                    JOIN tbl_faturamento_item ON tbl_faturamento.faturamento = tbl_faturamento_item.faturamento
                    WHERE tbl_extrato.fabrica = $login_fabrica
                    AND   tbl_extrato.extrato = $extrato
                    GROUP BY tbl_extrato.extrato, tbl_extrato.posto, tbl_faturamento_item.peca
                    ) ;";
                $res = pg_query($con,$sql);
            }

            //HD 12104
            if($login_fabrica==14 and strlen($imprime_os) > 0){
                $sql =" UPDATE tbl_posto_fabrica set imprime_os ='t'
                            FROM tbl_extrato
                            WHERE tbl_extrato.posto=tbl_posto_fabrica.posto
                            AND extrato=$imprime_os
                            AND tbl_posto_fabrica.fabrica=$login_fabrica ";
                $res=pg_query($con,$sql);
            }
        }
        //HD 237498: Coloquei esta linha porque depois que aprovava tudo sempre mostrava o �ltimo extrato, sozinho, ficando confuso
        if($login_fabrica == 52 AND strlen($msg_erro) == 0 AND strlen($extrato) > 0){
            $sql = "SELECT
                        tbl_posto_fabrica.contato_nome      AS nome,
                        tbl_posto_fabrica.contato_email     AS email
                    FROM
                        tbl_extrato
                    JOIN
                        tbl_posto_fabrica ON (tbl_posto_fabrica.posto = tbl_extrato.posto)
                    WHERE
                        tbl_extrato.extrato = $extrato
                        AND tbl_posto_fabrica.fabrica = $login_fabrica;";
            $res = pg_query($con, $sql);

            if (@pg_num_rows($res) == 0) {
                $sql = "SELECT
                            tbl_posto.nome  AS nome,
                            tbl_posto.email AS email
                        FROM
                            tbl_extrato
                        JOIN
                            tbl_posto ON (tbl_posto.posto = tbl_extrato.posto)
                        WHERE
                            tbl_extrato.extrato = $extrato
                            AND tbl_extrato.fabrica = $login_fabrica;";
                $res = pg_query($con, $sql);
            }

            $email_posto = @pg_fetch_result($res,0,'email');
            $nome_posto = @pg_fetch_result($res,0,'nome');

            $sql   = "SELECT email
            FROM tbl_admin
            WHERE tbl_admin.admin = {$login_admin}";

            $res   = pg_query($con,$sql);
            $email_admin = pg_fetch_result($res,0,'email');

            if($email_posto != ""){
                $remetente    = $email_admin;
                $destinatario = $email_posto;
                $assunto      = "Extrato Fricon $extrato liberado!\n";
                $mensagem     = "Prezado(a) {$extrato},\n";
                $mensagem    .="<br /><br />O(s) extrato(s) Fricon N� $extrato foi liberado, favor enviar a nota fiscal de presta��o de servi�os para pagamento e informar no corpo da nota os dados banc�rios\n";
                $mensagem    .="<br /><br />----------\n";
                $mensagem    .="<br />Qualquer d�vida entrar em contato com a Fricon.";
                $headers=  "From".$remetente."\nContent-type: text/html\n";

                mail($destinatario, utf8_encode($assunto), utf8_encode($mensagem), $headers);
            }else{
                echo "<script language='javascript'>alert('N�o foi possivel encontrar o email do posto, favor atualizar os dados');</script>";
            }
        }
        $btnacao = "";
        $extrato = "";
    }

    if(strlen($msg_erro) == 0){
        //$res = @pg_query ($con,"ROLLBACK TRANSACTION");
        $res = pg_query ($con,"COMMIT TRANSACTION");
    }else
        $res = @pg_query ($con,"ROLLBACK TRANSACTION");


    //HD 237498: Esta mensagem de erro tem que ficar depois do commit/rollback, pois � apenas informativa, n�o deve impedir que a transacao se concretize
    if (count($extrato_km_pendente)) {
        $extrato_km_pendente = implode(", ", $extrato_km_pendente);
        $msg_erro = "ATEN��O: Os extratos a seguir possuem OS em Interven��o de KM sem aprova��o/reprova��o e n�o ser�o liberados at� que seja definida uma posi��o da f�brica em rela��o a esta interven��o.<br>
        Extratos n�o liberados: $extrato_km_pendente";
    }
}

if ($btnacao == "acumular_tudo") {
    if (strlen($_POST["total_postos"]) > 0) $total_postos = $_POST["total_postos"];

    $res = pg_query($con,"BEGIN TRANSACTION");

    for ($i = 0 ; $i < $total_postos ; $i++) {
        $extrato = $_POST["acumular_" . $i];

        if (strlen($extrato) > 0) {
            $xextrato = $extrato;
            $sql = "SELECT fn_acumula_extrato ($login_fabrica, $extrato);";
            $res = pg_query($con,$sql);
            $msg_erro = pg_errormessage($con);

            if ( $login_fabrica == 24 ) {

                $sql = "UPDATE tbl_os_status
                            SET admin = $login_admin
                            WHERE extrato = $extrato";
                $res = pg_query($con,$sql);
                $msg_erro .= pg_errormessage($con);

            }

        }

        if (strlen($msg_erro) > 0) break;
    }

    $destinatario ="";
    if (strlen($msg_erro)==0 AND $login_fabrica==45){ //HD 66773
        if(strlen($xextrato)>0){
            $sql_email = "  SELECT tbl_posto_fabrica.contato_email
                            FROM tbl_extrato
                            JOIN tbl_posto_fabrica USING (posto)
                            WHERE tbl_posto_fabrica.fabrica = $login_fabrica
                            AND   tbl_extrato.extrato       = $xextrato";
            $res_email = pg_query($con, $sql_email);

            if(pg_num_rows($res_email)>0){
                $email_posto = pg_fetch_result($res_email,0,contato_email);
            }
        }
        $mensagem = "At. Respons�vel,<p>As Ordens de Servi�o do extrato " . $xextrato . " foram acumuladas para o pr�ximo m�s.</p>\n";
        $mensagem.= "<p style='color:red'>NKS</p>";

        if(strlen($email_posto)>0){
            $destinatario= "$email_posto";
    //          $remetente   = "helpdesk@telecontrol.com.br";
            $remetente   = "maiara@nksonline.com.br";
            $assunto     = "Extrato $xextrato";
            $mensagem    = "<p style='center'>Nota: Este e-mail � gerado automaticamente. <br>".
                           "**** POR FAVOR N�O RESPONDA ESTA MENSAGEM ****.</p>" . $mensagem;
            $headers     ="From:$remetente\r\nContent-type: text/html\r\ncco:gustavo@telecontrol.com.br";
            if(strlen($mensagem)>0) mail($destinatario, utf8_encode($assunto), utf8_encode($mensagem), $headers);
        }
    }
    else { header('Location: extrato_consulta.php'); }
    if (strlen($msg_erro) == 0) {
        $res = pg_query($con,"COMMIT TRANSACTION");
    }else{
        $res = pg_query($con,"ROLLBACK TRANSACTION");
    }
}

// ATEN��O: NESTE ARQUIVO EXISTEM DUAS ROTINAS PARA APROVAR EXTRATO, UMA COM AJAX E OUTRA SEM
//          QUANDO FOR MODIFICAR UMA, VERIFIQUE SE � NECESS�RIO MODIFICAR A OUTRA
if (strlen($_GET["aprovar"]) > 0) $aprovar = $_GET["aprovar"]; // � o numero do extrato



if (strlen($aprovar) > 0){
    //HD 205958: Acrescentado valida��o com BEGIN, COMMIT, ROLLBACK
    $res = pg_query($con,"BEGIN TRANSACTION");

    $km_pendente = false;

    //HD 237498: Barrar aprova��o de Extrato caso tenha OS em Interven��o de KM
    if (in_array($login_fabrica, $intervencao_km_extrato)) {
        $km_pendente = verifica_km_pendente_extrato($aprovar);
    } else {
        $km_pendente = false;
    }

    if ($km_pendente) {
        $msg_erro = "ATEN��O: O extrato $aprovar possui OS em Interven��o de KM sem aprova��o/reprova��o e n�o ser�o aprovados at� que seja definida uma posi��o da f�brica em rela��o a esta interven��o";
    } else {
        //atualiza campos de notas fiscais
        if ($login_fabrica == 20 || $login_fabrica == 14) {
            $nf_mao_de_obra = $_GET["nf_mao_de_obra"];
            if (strlen(trim($nf_mao_de_obra)) == 0) {
                $nf_mao_de_obra = 'null';
            }

            $nf_devolucao   = $_GET["nf_devolucao"];
            if (strlen(trim($nf_devolucao))==0) {
                $nf_devolucao = 'null';
            }

            $data_entrega_transportadora = $_GET["data_entrega_transportadora"];
            $data_entrega_transportadora = str_replace (" " , "" , $data_entrega_transportadora);
            $data_entrega_transportadora = str_replace ("-" , "" , $data_entrega_transportadora);
            $data_entrega_transportadora = str_replace ("/" , "" , $data_entrega_transportadora);
            $data_entrega_transportadora = str_replace ("." , "" , $data_entrega_transportadora);

            if (strlen ($data_entrega_transportadora) == 6) {
                $data_entrega_transportadora = "'".substr ($data_entrega_transportadora,0,4) . "20" . substr ($data_entrega_transportadora,4,2)."'";
            }

            if (strlen ($data_entrega_transportadora) > 0) {
                $data_entrega_transportadora = substr ($data_entrega_transportadora,0,2) . "/" . substr ($data_entrega_transportadora,2,2) . "/" . substr ($data_entrega_transportadora,4,4);
                if (strlen ($data_entrega_transportadora) < 8) $data_entrega_transportadora = date ("d/m/Y");
                $data_entrega_transportadora = "'".substr ($data_entrega_transportadora,6,4) . "-" . substr ($data_entrega_transportadora,3,2) . "-" . substr ($data_entrega_transportadora,0,2)."'";
                } else {
                $data_entrega_transportadora = 'null';
            }

            $sql = "UPDATE tbl_extrato_extra SET
                        nota_fiscal_mao_de_obra     = '$nf_mao_de_obra',
                        nota_fiscal_devolucao       = '$nf_devolucao',
                        data_entrega_transportadora = $data_entrega_transportadora
                    WHERE extrato = $aprovar";

            $res = pg_query($con,$sql);

            if (pg_errormessage($con)) {
                $msg_erro = "Ocorreu um erro na aprova��o do extrato $aprovar";
            }
            #  HD 4846 - Colocado!

            $sql = "
            UPDATE
            tbl_extrato_extra

            SET
            admin = $login_admin

            WHERE
            extrato = $aprovar
            ";
            $res = pg_query($con, $sql);
            if (pg_errormessage($con)) {
                $msg_erro = "Ocorreu um erro na aprova��o do extrato $aprovar";
            }

        }

        //PARA A INTELBR�S DEIXAR ELE APROVAR EXTRATO, POIS ELES EST�O EM PROCESSO DE TRANSI��O, SEGUNDO A RAMONNA
        $sql = "SELECT fn_aprova_extrato($posto,$login_fabrica,$aprovar)";
        $res = pg_query($con,$sql);

        if (pg_errormessage($con)) {
            $msg_erro = "Ocorreu um erro na aprova��o do extrato $aprovar: " . pg_errormessage($con);
        }

        if (strlen($msg_erro) == 0) {
            $res = pg_query($con,"COMMIT TRANSACTION");
            header("Location: $PHP_SELF?btnacao=filtrar&extrato=$extrato&posto=$posto&data_inicial=$data_inicial&data_final=$data_final&cnpj=$xposto_codigo&razao=$posto_nome");
        } else {
            $res = pg_query($con,"ROLLBACK TRANSACTION");
        }

    }

}

$layout_menu = "financeiro";
$title = "CONSULTA E MANUTEN��O DE EXTRATOS";

if ($login_fabrica == 156) {
    $title .= " - OS ABERTA PELO CALLCENTER";
}

include "cabecalho.php";

?>
<p>

<style type="text/css">
body{ font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: 12px; }
.menu_top {
text-align: center;
font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
font-size: 10px;
font-weight: bold;
border: 1px solid;
background-color: #D9E2EF
}
.table_line {
text-align: left;
font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
font-size: 10px;
font-weight: normal;
border: 0px solid;
background-color: #D9E2EF
}
.table_line2 {
text-align: left;
font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
font-size: 10px;
font-weight: normal;
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
.texto_padrao {
font-size: 12px;
}
#Formulario tbody th{
text-align: left;
font-weight: bold;
}
#Formulario tbody td{
text-align: left;
font-weight: none;
}
.titulo_tabela{
background-color:#596d9b;
font: bold 14px "Arial";
color:#FFFFFF;
text-align:center;
}
.titulo_coluna{
background-color:#596d9b !important;
font: bold 11px "Arial" !important;
color:#FFFFFF !important;
text-align:center !important;
}
.msg_erro{
background-color:#FF0000;
font: bold 16px "Arial";
color:#FFFFFF;
text-align:center;
}
.formulario{
background-color:#D9E2EF;
font:11px Arial;
}
.subtitulo{
color: #7092BE
}
table.tabela tr td{
    font-family: verdana;
    font-size: 11px;
    border:1px solid #ACACAC;
    border-collapse: collapse;
}
.msg_sucesso{
background-color: green;
font: bold 16px "Arial";
color: #FFFFFF;
text-align:center;
}

table.tabela tr th{
color: #FFFFFF !important ;
border:1px solid #ACACAC;
border-collapse: collapse;
}

.ms-parent{
    width: 200px !important; 
}

#ms{
    border-radius: 0px !important;
    height: 15px !important;
}

 .ms-choice {                                                                                                      
          border-radius: 0px !important;
          border-color: #888 !important;
          border-style: solid;
          border-width: 1px !important;
          background-color:#F0F0F0 !important;
          height: 18px !important;
}

<?php if($login_fabrica == 1){ ?>

    table.tablesorter tbody td{
        background-color: transparent !important;
    }

<?php } ?>

</style>

<!--[if lt IE 8]>
<style>
table.tabela{
    empty-cells:show;
    border-collapse:collapse;
    border-spacing: 2px;
}
</style>
<![endif]-->

<?php include "javascript_calendario.php"; ?>

<?php include "../js/js_css.php"; ?>

<?php
    //$plugins = array("jquery_multiselect");
?>

<?php include("plugin_loader.php"); ?>
<link rel="stylesheet" href="css/multiple-select.css" />
<script src="js/jquery.multiple.select.js"></script>
<script>
    $(function() {
        $('#ms').change(function() {
            console.log($(this).val());
        }).multipleSelect({
            width: '100%'
        });
    });

    $(function() {
        $('#estados').change(function() {
            console.log($(this).val());
        }).multipleSelect({
            width: '100%'
        });
    });

</script>

<script type="text/javascript">
    
    $(document).ready(function()
    {

        //$("#regiao").multiSelect();

       // $("#estados").multiSelect();

        <?php if($login_fabrica == 20){?>
        $("#grid_list").tablesorter({
            widgets: ["zebra"],
            headers:{
                0:{
                    sorter: false
                },
                1:{
                    sorter: false
                }
            }
        });

        <?php } ?>

        Shadowbox.init();

        // HD 679624
        $("#acumula_extratos").click(function(e)
        {
            if( confirm("Deseja realmente acumular o(s) extrato(s) para o pr�ximo m�s?") )
            {
                document.Selecionar.btnacao.value="acumular_tudo" ;
                document.Selecionar.submit();
            }
            e.preventDefault();
            return false;
        });

        $(".acumula_extrato").click(function(e)
        {
            if( confirm("Deseja realmente acumular o extrato para o pr�ximo m�s?"))
            {
                $(this).parent().find('input').attr('checked','checked');
                document.Selecionar.btnacao.value="acumular_tudo" ;
                document.Selecionar.submit();
            }
            e.preventDefault();
        });
        $(".date").datepick();
        $("#data_inicial").mask("99/99/9999");
        $("#data_final").mask("99/99/9999");
        $("#posto_codigo").mask("99.999.999/9999-99");
        $(".data_entrega_transportadora").mask("99/99/9999");

        $("input[id^=encontro_contas_]").click(function(){

            var extrato = $(this).attr("rel");

            Shadowbox.open({
                content : "detalhe_encontro_contas.php?extrato="+extrato,
                player  : "iframe",
                title   : "Detalhe encontro de contas",
                width   : 800,
                height  : 250
            });
        });

<?
if($login_fabrica == 1){
?>
        $("#valor_abaixo").css("text-align","right");
        $("#valor_abaixo").maskMoney({
            showSymbol:"",
            symbol:"",
            decimal:",",
            precision:2,
            thousands:".",
            maxlength:10
        });
<?
}
?>
    });

<?php
if($login_fabrica == 151){
?>

function insere_nf_servico(nf_servico, extrato){
    $(".nf-servico-"+extrato).html("<a href='../nota_servico_extrato.php?extrato="+extrato+"' rel='shadowbox; width= 400; height= 250;'>"+nf_servico+"</a>");
    Shadowbox.setup();
}

<?php
}
?>

function pesquisaPosto(campo,tipo)
{
    var campo = campo.value;

    if( jQuery.trim(campo).length > 2 )
    {
        Shadowbox.open({
            content : "posto_pesquisa_nv.php?"+tipo+"="+campo+"&tipo="+tipo,
            player  : "iframe",
            title   : "Pesquisa Posto",
            width   : 800,
            height  : 500
        });
    }else alert("Informar toda ou parte da informa��o para realizar a pesquisa!");
}

function retorna_posto(posto,codigo_posto,nome,cnpj,pais,cidade,estado,nome_fantasia)
{
    gravaDados('codigo_posto_codigo', codigo_posto);
    gravaDados('posto_nome', nome);
    gravaDados('posto_codigo', cnpj);
}

function gravaDados(name, valor)
{
    try {
        $("input[name="+name+"]").val(valor);
    }catch(err){
        return false;
    }
}

function somente_numero(campo)
{
    var digits = "0123456789-./"
    var campo_temp;
    for( var i=0; i<campo.value.length; i++ )
    {
        campo_temp = campo.value.substring(i, i+1);
        if( digits.indexOf(campo_temp)==-1 )
        {
            campo.value = campo.value.substring(0, i);
            break;
        }
    }
}
</script>

<script type="text/javascript">
// HD 22752
function refreshTela(tempo){ window.setTimeout("window.location.href = window.location.href", tempo); }

$(document).ready(function()
{
    function formatItem(row){
        return row[0] + " - " + row[1];
    }

    function formatResult(row){
        return row[0];
    }

    /* Busca pelo C�digo */
    $("#posto_codigo").autocomplete("<?php echo $PHP_SELF.'?busca=codigo'; ?>", {
        minChars: 3,
        delay: 150,
        width: 350,
        matchContains: true,
        formatItem: formatItem,
        formatResult: function(row) {return row[0];}
    });

    $("#posto_codigo").result(function(event, data, formatted){
        $("#posto_nome").val(data[1]);
    });

    /* Busca pelo Nome */
    $("#posto_nome").autocomplete("<?php echo $PHP_SELF.'?busca=nome'; ?>", {
        minChars: 3,
        delay: 150,
        width: 350,
        matchContains: true,
        formatItem: formatItem,
        formatResult: function(row){ return row[1]; }
    });

    $("#posto_nome").result(function(event, data, formatted)
    {
        $("#posto_codigo").val(data[0]); //alert(data[2]);
    });
});
</script>

<script type="text/javascript">
/* ============= Fun��o PESQUISA DE POSTOS ====================
Nome da Fun��o : fnc_pesquisa_posto (cnpj,nome)
        Abre janela com resultado da pesquisa de Postos pela
        C�digo ou CNPJ (cnpj) ou Raz�o Social (nome).
=================================================================*/

function fnc_pesquisa_posto(campo, campo2, tipo)
{
    if( tipo == "nome" ){ var xcampo = campo;  }
    if( tipo == "cnpj" ){ var xcampo = campo2; }

    if( xcampo.value != "" )
    {
        var url        = "";
        url            = "posto_pesquisa.php?campo=" + xcampo.value + "&tipo=" + tipo ;
        janela         = window.open(url, "janela", "toolbar=no, location=no, status=yes, scrollbars=yes, directories=no, width=600, height=300, top=0, left=0");
        janela.retorno = "<?php echo $PHP_SELF; ?>";
        janela.nome    = campo;
        janela.cnpj    = campo2;
        janela.focus();
    }else{
        alert('Preencha toda ou parte da informa��o para realizar a pesquisa!');
    }
}

var checkflag = "false";

function  check(field){

    $("input[name^='liberar_']").each(function(){
        if($(this).is(":checked")){
            $(this).prop("checked",false);
        }else{
            $(this).prop("checked",true);
        }
    });
}


/*function check(field)
{
    alert("teste "+ field);
    console.log(field);
    if( checkflag == "false" )
    {
        for( i=0; i<field.length; i++ )
        {
            field[i].checked = true;
        }
        checkflag = "true";
        return true;
    }
    else
    {
        for( i=0; i<field.length; i++ )
        {
            field[i].checked = false;
        }
        checkflag = "false";
        return true;
    }
}
*/
function AbrirJanelaObs(extrato)
{
    var largura  = 400;
    var tamanho  = 250;
    var lar      = largura / 2;
    var tam      = tamanho / 2;
    var esquerda = (screen.width / 2)  - lar;
    var topo     = (screen.height / 2) - tam;
    var link     = "extrato_status.php?extrato=" + extrato;
    window.open(link, "janela", "toolbar=no, location=no, status=yes, menubar=no, scrollbars=no, directories=no, resizable=no, width=" + largura + ", height=" + tamanho + ", top=" + topo + ", left=" + esquerda + "");
}

function gerarExportacao(but)
{
    if( but.value == 'Exportar Extratos' )
    {
        if( confirm('Deseja realmente prosseguir com a exporta��o?\n\nSer� exportado somente os extratos aprovados e liberados.') )
        {
            but.value='Exportando...';
            exportar();
        }
    }
    else
    {
         alert('Aguarde submiss�o');
    }

}

function retornaExporta(http)
{
    if( http.readyState == 4 )
    {
        if( http.status == 200 )
        {
            results = http.responseText.split("|");

            if( typeof (results[0]) != 'undefined' )
            {
                if( results[0] == 'ok' )
                {
                    alert(results[1]);
                }
                else
                {
                    alert (results[1]);
                }
            }
            else
            {
                alert("N�o existe extratos a serem exportados.");
            }
        }
    }
}

function exportar()
{
    url = "<?= $PHP_SELF ?>?exportar=sim";
    http.open("GET", url , true);
    http.onreadystatechange = function(){ retornaExporta(http); };
    http.send(null);
}
</script>

<script type="text/javascript">
function createRequestObject()
{
    var request_;
    var browser = navigator.appName;
    if( browser == "Microsoft Internet Explorer" )
    {
        request_ = new ActiveXObject("Microsoft.XMLHTTP");
    }
    else
    {
        request_ = new XMLHttpRequest();
    }
    return request_;
}

var http_data = new Array();
var semafaro  = 0;

function aprovaExtrato(extrato , posto, aprovar, novo,adicionar,acumular,resposta)
{
    if( semafaro == 1 )
    {
        alert('Aguarde alguns instantes antes de aprovar outro extrato.');
        return;
    }

    if( confirm('Deseja aprovar este extrato?')==false ){ return; }

    var curDateTime = new Date();
    semafaro  = 1;
    url       = "<?= $PHP_SELF ?>?ajax=APROVAR&aprovar=" + escape(extrato)+ "&posto=" + escape(posto)+"&data="+curDateTime;
    aprovar   = document.getElementById(aprovar);
    novo      = document.getElementById(novo);
    adicionar = document.getElementById(adicionar);
    acumular  = document.getElementById(acumular);
    resposta  = document.getElementById(resposta);

    http_data[curDateTime] = createRequestObject();
    http_data[curDateTime].open('POST',url,true);
    http_data[curDateTime].setRequestHeader("X-Requested-With","XMLHttpRequest");

    http_data[curDateTime].onreadystatechange = function()
    {
        if( http_data[curDateTime].readyState == 4 )
        {
            if( http_data[curDateTime].status == 200 || http_data[curDateTime].status == 304 )
            {
                var response = http_data[curDateTime].responseText.split(";");

                if( response[0]=="ok" )
                {
                    if( aprovar   ) aprovar.src         = '/assist/imagens/pixel.gif';
                    if( novo      ) novo.src            = '/assist/imagens/pixel.gif';
                    if( adicionar ) adicionar.src       = '/assist/imagens/pixel.gif';
                    if( acumular  ) { acumular.disabled = true; acumular.style.visibility = "hidden"; }
                    if( resposta  ) resposta.innerHTML  = "Aprovado";
                }else{
                    alert('Extrato n�o foi aprovado. Tente novamente.');
                }
                semafaro = 0;
            }
        }
    }
    http_data[curDateTime].setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=iso-8859-1");
    http_data[curDateTime].setRequestHeader("Cache-Control", "no-store, no-cache, must-revalidate");
    http_data[curDateTime].setRequestHeader("Cache-Control", "post-check=0, pre-check=0");
    http_data[curDateTime].setRequestHeader("Pragma", "no-cache");
    http_data[curDateTime].send('');
}

function createRequestObject()
{
    var request_;
    var browser = navigator.appName;
    if( browser == "Microsoft Internet Explorer" )
    {
         request_ = new ActiveXObject("Microsoft.XMLHTTP");
    }
    else
    {
         request_ = new XMLHttpRequest();
    }
    return request_;
}

var http_forn  = new Array();
var conta_tudo = 0;

<?php
    /* HD 38185 */
    if( $login_fabrica == 35 or $login_fabrica == 15 ){
        echo " conta_tudo = 1;";
    }
?>

function conta_os(extrato,div,contador)
{
    var extrato = extrato;
    var div     = document.getElementById(div);
    var url     = 'conta_os_ajax.php?extrato=' + extrato + '&cache_bypass=<?= $cache_bypass ?>' ;

    $.ajax({
            type  : "GET",
            url   : "conta_os_ajax.php?extrato=",
            data  : 'extrato=' + extrato + '&cache_bypass=<?= $cache_bypass ?>',
            cache : false,
            beforeSend: function(){
                // enquanto a fun��o esta sendo processada, voc�
                // pode exibir na tela uma
                // msg de carregando
                $(div).html("Espere...");
            },
            success: function(txt){
                // pego o id da div que envolve o select com
                // name="id_modelo" e a substituiu
                // com o texto enviado pelo php, que � um novo
                //select com dados da marca x
                $(div).html(txt);
            },
            error: function(txt){ alert(txt); }
        });
    //  $(div).html(qtde);
}
/*
function conta_os_tudo()
{
    var total = document.getElementById('total_res').value;
    //console.log(total);

    for( i=0; i<total; i++ )
    {
        extrato = document.getElementById('extrato_tudo_'+i).value;
        var div = document.getElementById('qtde_os_'+i);

        $(div).html("Espere...");
        var url  = 'conta_os_ajax.php?extrato=' + extrato + '&cache_bypass=<?= $cache_bypass ?>';
        var qtde = $.ajax({ type  : "GET",
                            url   : url,
                            cache : false,
                            async : false }).responseText;
        $(div).html(qtde);
    }

    contadorOS();
}*/


<?php
if($login_fabrica == 20){
?>
var extrato = "";

function contadorOS(){

    var valor = 0;
    $('div[id^=qtde_os_]').each(function(){
        var valorOS = $(this).text();
        valor += parseInt(valorOS);
    });

    $("#qtdeOS").text('Ordem Servi�o: '+valor);

}

<?
}
?>

function addCommas(nStr)
{
    nStr += '';
    x     = nStr.split('.');
    x1    = x[0];
    x2    = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while( rgx.test(x1) )
    {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

function somarExtratos(selecionar){
    if( selecionar == 'todos' )
    {
        $("input[rel='somatorio']").each(function(){ this.checked = true; });
    }

    var total_extratos = 0;

    $("input[rel='somatorio']:checked").each(function (){
        if( this.checked ){ total_extratos += parseFloat(this.value); }
    });

    total_extratos = total_extratos.toFixed(2);
    $('#total_extratos').html('Soma dos extratos selecionados: <b>R$ '+addCommas(total_extratos)+'</b>');
}

function selecionaTodos(){

    // if( $('#checkAll').attr('checked')==true ){
    //     $('#grid_list input[name*="extrato_"]').each(function(indice){ this.checked = true; });
    // }else{
    //     $('#grid_list input[name*="extrato_"]').each(function(indice){ this.checked = false; });
    // }

    if ($("#checkAll").attr("checked")){
      $('.check').each(
        function(){
            $(this).attr("checked", true);
        }
      );
   }else{
      $('.check').each(
         function(){
            $(this).attr("checked", false);
         }
      );
   }
}

function selecionarExtratos(){

   if ($("#checar").attr("checked")){
      $('.check1').each(
        function(){
            $(this).attr("checked", true);
            if($(this).parents('tr').find('input[name^=aprovado_]').val()){
                $(this).attr("checked", false);
            }
        }
      );
   }else{
      $('.check1').each(
         function(){
            $(this).attr("checked", false);
         }
      );
   }

}



function aprovarTodos(){

    var confirm1 = confirm('Deseja aprovar todos extratos selecionados ?');
      if (confirm1) {
        $('.check1').each(function(){

            if($(this).is(":checked")){

                var nf_mao_obra = $(this).parents('tr').find('input[name^=nota_fiscal_mao_de_obra_]').val();
                var nf_devolucao = $(this).parents('tr').find('input[name^=nota_fiscal_devolucao_]').val();
                var entrega_transportadora = $(this).parents('tr').find('input[name^=data_entrega_transportadora_]').val();
                var extrato = $(this).val();

                $.ajax({

                    url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                    type: "POST",
                    data: {aprovarCheck: 'ok', nf_mao_obra: nf_mao_obra, nf_devolucao: nf_devolucao, entrega_transportadora: entrega_transportadora, extrato: extrato },
                    complete: function(data){
                        var dados = data.responseText;

                        if(dados == extrato){
                            $('.extrato_aprova_'+extrato).hide();
                            $('.extrato_novo_'+extrato).hide();
                            $('.extrato_adicionar_'+extrato).hide();
                            $("label[for='extrato_aprovado_"+extrato+"']").css("display","block").html('Aprovado');

                            //setTimeout("location.reload();", 3000);
                        }else{
                            $('.extrato_aprova_'+extrato).hide();
                            $("label[for='extrato_aprovado_"+extrato+"']").css("display","block").html('N�o aprovado');
                        }
                    }
                });
            }
        });
      } else {
        return false;
      }

    //fun��o each para pegar os selecionados
}

function calcularExtrato()
{
    extrato = "";

    // $('#grid_list input[name^="extrato_"]').each(function(indice){
    $('.extrato_calcula').each(function(indice){
        var value = $(this).val();
        
        if( $(this).is(':checked'))
        {
            if( parseFloat(extrato) > 0 )
            {
                extrato += ","+value;
            }else
                extrato = $(this).val();
        }
    });

    if( parseFloat(extrato) > 0 )
    {
        Shadowbox.open({
            // content : "calculo_extratos.php?extratos="+extrato,
            content : "calculo_extratos.php",
            player  : "iframe",
            title   : "C�lculo de extratos",
            width   : 800,
            height  : 250
        });
    }else{
        alert("Check os extratos para o c�lculo!");
    }
}

function getExtrato(){
    return extrato;
}

<?php
if($login_fabrica == 1){
?>

function inibir_extrato(extrato){

    var inibir = ($("#inibir_extrato_"+extrato).is(":checked")) ? true : false;

    $.ajax({
        url : "<?php echo $_SERVER['PHP_SELF']; ?>",
        type: "POST",
        data: {
            inibir_extrato : true,
            inibir : inibir,
            extrato : extrato
        },
        complete: function(data){

            data = JSON.parse(data.responseText);

            if(data.sucesso){

                if(inibir == true){
                    $("tr.linha_"+extrato).attr({"bgcolor" : "#ffffb2"});
                }else{
                    $("tr.linha_"+extrato).attr({"bgcolor" : "#ffffff"});
                }

            }else{
                alert("Erro ao inibri o Extrato");
            }

        }
    });

}

<?php
}
?>

</script>

<script type='text/javascript'>
 /** add new widget called repeatHeaders  */
    $(function()
    {
        <? if(in_array($login_fabrica, array(50,91,138))){?>
            $("#download_excel").click(function(){
                var extrato             = $("#extrato").val();
                var data_inicial        = $("#data_inicial").val();
                var data_final          = $("#data_final").val();
                var data_baixa_inicio   = $("#data_baixa_inicio").val();
                var data_baixa_fim      = $("#data_baixa_fim").val();
                var posto_codigo        = $("#posto_codigo").val();
                var posto_nome          = $("#posto_nome").val();

                $.ajax({
                    url:"relatorio_consulta_extratos.php",
                    type:"POST",
                    data:{
                        gerar_excel:     1,
                        extrato          :extrato          ,
                        data_inicial     :data_inicial     ,
                        data_final       :data_final       ,
                        data_baixa_inicio:data_baixa_inicio,
                        data_baixa_fim   :data_baixa_fim   ,
                        posto_codigo     :posto_codigo     ,
                        posto_nome       :posto_nome

                    },
                    complete: function(data){
                        console.log(data.responseText);
                        window.open(data.responseText, "_blank");
                    }
                });
            });

	 <? } ?>
        // add new widget called repeatHeaders
        // $.tablesorter.addWidget({
        //     // give the widget a id
        //     id: "repeatHeaders",
        //     // format is called when the on init and when a sorting has finished
        //     format: function(table){
        //         // cache and collect all TH headers
        //         if( !this.headers )
        //         {
        //             var h = this.headers = [];
        //             $("thead th",table).each(function(col){
        //                 h.push("<td colspan='"+$(this).attr('colspan')+"'>" + $(this).text() + "</td>");
        //             });
        //         }

        //         $("tr.repated-header",table).remove(); // remove appended headers by classname.

        //         // loop all tr elements and insert a copy of the "headers"
        //         for( var i=0; i < table.tBodies[0].rows.length; i++ )
        //         {
        //             // insert a copy of the table head every 10th row
        //             if( (i%20) == 0 )
        //             {
        //                 if( i!=0 )
        //                 {
        //                     $("tbody tr:eq(" + i + ")",table).before(
        //                         $("<tr></tr>").addClass("repated-header").html(this.headers.join(""))

        //                     );
        //                 }
        //             }
        //         }
        //     }
        // });
        // $("table").tablesorter({
        //     widgets: ['zebra','repeatHeaders']
        // });
        //conta_os_tudo();
    });
</script>

<?php

if(strlen($btnacao) > 0){

    if($login_fabrica == 1){

        $estados   = $_POST['estados'];
        $regiao    = $_POST['regiao']; 

        $count_regiao = count($regiao);
        $i=1;
        foreach($regiao as $linha){
            $dados .= $linha;

            if($i < $count_regiao){
                $dados .= ", ";
            }
            $i++;        
        }
        $dados = str_replace(', ', "', '", "$dados");
        $dados = "'$dados'";
        

        $count_estados = count($estados);
        $e=1;
        foreach($estados as $linha_estados){
            $dados_estados .= $linha_estados;
            
            if($e < $count_estados){
                $dados_estados .= ", ";
            }
            $e++;
        }
        $dados_estados = str_replace(', ', "', '", "$dados_estados");
        $dados_estados = "'$dados_estados'";

        if($count_estados > 0 and $count_regiao > 0){
            $conteudo = $dados . ", ". $dados_estados;
        }elseif($count_regiao > 0){
            $conteudo = $dados;
        }elseif($count_estados > 0){
            $conteudo = $dados_estados;
        } 
        
        if(strlen(trim($conteudo))>0){
            $where_estado_regiao = " and PF.contato_estado in ($conteudo) ";
        }    

    }

    if (strlen($_GET['data_inicial']) > 0) $data_inicial = $_GET['data_inicial'];
    if (strlen($_POST['data_inicial']) > 0) $data_inicial = $_POST['data_inicial'];

    if (strlen($_GET['data_final']) > 0) $data_final = $_GET['data_final'];
    if (strlen($_POST['data_final']) > 0) $data_final = $_POST['data_final'];

    $posto_nome   = $_POST['posto_nome'];

    if (strlen($_GET['posto_nome']) > 0) $posto_nome = $_GET['posto_nome'];
    if (strlen($_GET['razao']) > 0) $posto_nome = $_GET['razao'];

    $posto_codigo = $_POST['posto_codigo'];

    if (strlen($_GET['posto_codigo']) > 0) $posto_codigo = $_GET['posto_codigo'];
    if (strlen($_GET['cnpj']) > 0) $posto_codigo = $_GET['cnpj'];

    if (strlen($_GET['extrato']) > 0) $extrato = trim($_GET['extrato']);
    if (strlen($_POST['extrato']) > 0) $extrato = trim($_POST['extrato']);

    if (strlen($_GET['extrato_pago']) > 0)  $extrato_pago = $_GET['extrato_pago'];
    if (strlen($_POST['extrato_pago']) > 0) $extrato_pago = $_POST['extrato_pago'];

    // HD 49255
    if (strlen($_GET['liberado']) > 0)  $xliberado = $_GET['liberado'];
    if (strlen($_POST['liberado']) > 0) $xliberado = $_POST['liberado'];

    if (strlen($_GET['aguardando_pagamento']) > 0)     $aguardando_pagamento = $_GET['aguardando_pagamento'];
    if (strlen($_POST['aguardando_pagamento']) > 0)    $aguardando_pagamento = $_POST['aguardando_pagamento'];

    if (strlen($_GET['liberacao']) > 0) $aprovacao = $_GET['liberacao'];
    if (strlen($_POST['liberacao']) > 0) $aprovacao = $_POST['liberacao'];

    //HD 286780
    if (strlen($_POST['estado']) > 0) $estado = $_POST['estado'];
    if (strlen($_GET['estado']) > 0)  $estado = $_GET['estado'];

    if (strlen($_POST['marca']) > 0) $marca_aux = $_POST['marca'];
    if (strlen($_GET['marca']) > 0)  $marca_aux = $_GET['marca'];

    if($login_fabrica == 91){

        if (strlen($_POST['data_baixa_inicio']) > 0){
            $data_baixa_inicio = $_POST['data_baixa_inicio'];
        }
        if (strlen($_POST['data_baixa_fim']) > 0){
            $data_baixa_fim = $_POST['data_baixa_fim'];
        }
    }

    if($login_fabrica == 1){
        $busca_valor_abaixo = $_REQUEST['busca_valor_abaixo'];
        $valor_abaixo       = $_REQUEST['valor_abaixo'];

        $xvalor_abaixo      = str_replace(",",".",$valor_abaixo);
    }

    if(empty($data_inicial) and empty($data_final) and empty($posto_nome) and empty($posto_codigo) and empty($extrato) and empty($marca_aux) and empty($aguardando_pagamento)){

            if($login_fabrica == 42 ){
                if(empty($mes_referencia) && empty($valor_total) && empty($valor_nf_peca) && empty($nf_autorizacao) && empty($nf_peca) && empty($bordero)){
                    $msg_erro = "Informe algum Par�metro para Pesquisa";
                }

            }else if($login_fabrica == 91){

                if(empty($data_baixa_inicio) && empty($data_baixa_fim)){
                    $msg_erro = "Informe algum Par�metro para Pesquisa";
                }else{
                    $msg_erro = "Informe a Data Inicial e a Data Final.";
                }
            }else{
                $msg_erro = "Informe algum Par�metro para Pesquisa";
            }

    }

    if(( $login_fabrica == 86 OR $login_fabrica == 104) AND $btnacao == "filtrar"){
        if(empty($marca_aux) AND empty($posto_codigo) AND empty($extrato)){
            $msg_erro = "Informe uma Empresa";
        }
    }

    //hd-1098022 se for Fricon (52) verifica se foi passado numero da OS para filtro
    if($login_fabrica== 52 ){
        if(!empty($_POST["nroOs"])){
            $nroOs = $_POST["nroOs"];
        }
    }

    if($login_fabrica == 42){


        $bordero                    = (strlen($_POST['bordero']) > 0)                   ?   $_POST['bordero']       : "";
        //$data_bordero             = (strlen($_POST['data_bordero']) > 0)              ?   $_POST['data_bordero']  : "NULL";
        //� referente ao campo Data Envio Financeiro
        //$data_entregue_financeiro = (strlen($_POST['data_entregue_financeiro']) > 0)  ? $_POST['data_entregue_financeiro']    : "NULL";
        //$data_aprovacao               = (strlen($_POST['data_aprovacao']) > 0)            ? $_POST['data_aprovacao']              : "NULL";
        //$data_pagamento               = (strlen($_POST['data_pagamento']) > 0)            ? $_POST['data_pagamento']              : "NULL";
        $mes_referencia             = (strlen($_POST['mes_referencia']) > 0)            ? $_POST['mes_referencia']              : "";
        //referente ao campo Valor NF Servi�os
        $valor_total                = (strlen($_POST['valor_total']) > 0)               ? $_POST['valor_total']                 : "";
        $valor_nf_peca              = (strlen($_POST['valor_nf_peca']) > 0)             ? $_POST['valor_nf_peca']               : "";
        $nf_autorizacao             = (strlen($_POST['nf_autorizacao']) > 0)            ? $_POST['nf_autorizacao']          : "";
        $nf_peca                    = (strlen($_POST['nf_peca']) > 0)                   ? $_POST['nf_peca']                     : "";
        $posto                      = (strlen($_POST['posto']) > 0)                     ? $_POST['posto']                       : "";
    }
//In�cio Valida��o de Datas
    /*if(!$data_inicial OR !$data_final)
        $msg_erro = "Data Inv�lida"; */
    if(!empty($data_inicial) && !empty($data_final)){
        if(strlen($msg_erro)==0){
            $dat = explode ("/", $data_inicial );//tira a barra
                $d = $dat[0];
                $m = $dat[1];
                $y = $dat[2];
                if(!checkdate($m,$d,$y)) $msg_erro = "Data Inv�lida";
        }
        if(strlen($msg_erro)==0){
            $dat = explode ("/", $data_final );//tira a barra
                $d = $dat[0];
                $m = $dat[1];
                $y = $dat[2];
                if(!checkdate($m,$d,$y)) $msg_erro = "Data Inv�lida";
        }
        if(strlen($msg_erro)==0){
            $d_ini = explode ("/", $data_inicial);//tira a barra
            $nova_data_inicial = "$d_ini[2]-$d_ini[1]-$d_ini[0]";//separa as datas $d[2] = ano $d[1] = mes etc...


            $d_fim = explode ("/", $data_final);//tira a barra
            $nova_data_final = "$d_fim[2]-$d_fim[1]-$d_fim[0]";//separa as datas $d[2] = ano $d[1] = mes etc...

            if($nova_data_final < $nova_data_inicial){
                $msg_erro = "Data Inv�lida.";
            }
            //Fim Valida��o de Datas
        }
    }
}

echo "<FORM METHOD='post' id='teste' NAME='frm_extrato' ACTION=\"$PHP_SELF\">";
echo "<TABLE width='700px' align='center' border='0' cellspacing='1' class='formulario' cellpadding='3'>\n";
echo "<input type='hidden' name='btnacao' value=''>";
if(strlen($msg_erro)>0){
    echo "<TR class='msg_erro'><TD colspan='7'>$msg_erro</TD></TR>";
}
echo "<TR class='titulo_tabela'>\n";
echo "  <TD COLSPAN='7' ALIGN='center'>";
echo "      Par�metros de Pesquisa";
echo "  </TD>";
echo "</TR>",
"<tr>
    <td>&nbsp;</td>
</tr>";




echo "<TR align='left'>\n";
echo "<TD width='25'>&nbsp;</TD>";
echo "<TD ALIGN='left'>N� de extrato </TD>";
//hd-1098022: Fricon -> se form Fricon coloca campo Pesquisa OS para filtrar resultados pelo n�mero da OS
if($login_fabrica==52){

        echo "<td align='left'>Pesquisa OS</td>";

}

//hd-1059101 - Makita
if($login_fabrica == 42){
    echo "<td>Filtrar por </td>";

}
echo "<td>Data Inicial </td>";
echo "<td >  Data Final </td>";

echo "</tr>";
echo "<tr align='left'><TD width='25'>&nbsp;</TD>"; //inicio dos campos
echo "<td><input type='text' id='extrato' name='extrato' size='12' value='$extrato' class='frm'>&nbsp;";
echo "  </TD>\n";
if($login_fabrica ==52){

    echo "<td><input type='text' name='nroOs' size='12' value='$nroOs' class='frm'>&nbsp;";
}
if($login_fabrica == 42){

    echo "<td>  <select class='frm' size='1' name='tipoData'>";
    echo    "<option ";
                 if (empty($tipoData)) echo "selected";
    echo    "></option>";

    echo    "<option value='dataBordero'";
                 if ($tipoData=="dataBordero") echo "selected";
    echo            ">Data Border�</option>";

    echo    "<option value='dataEntregueFinanceiro'";
                 if ($tipoData=="dataEntregueFinanceiro") echo "selected";
    echo                    ">Data Entrega Financeiro</option>";

    echo    "<option value='dataAprovacao'";
                if ($tipoData=="dataAprovacao") echo "selected";
    echo                    ">Data Aprova��o</option>";

    echo    "<option value='dataPagamento'";
                if ($tipoData=="dataPagamento") echo "selected";
    echo                    ">Data Pagamento</option>";

    echo    "</td>";

}
echo "  <TD ALIGN='left' width='50'>";

echo "  <input type='text' size='12' maxlength='10' name='data_inicial' id='data_inicial' rel='data' value='$data_inicial' class='frm date' />\n";
echo "  </TD>\n";

echo "  <TD width='100' ALIGN='left'>";

echo "  <INPUT type='text' size='12' maxlength='10'  name='data_final' id='data_final' rel='data' value='$data_final' class='frm date' />\n";
echo "</TD>";
if($login_fabrica == 91){
    echo "</tr><tr>";
    echo "<TD width='25'>&nbsp;</TD>";
    echo "<td width='100' align='left'>  Data da Baixa In�cio </td>";
    echo "<td width='100' align='left'>  Data da Baixa Fim </td>";
}
if($login_fabrica == 91){
    echo "</tr><tr>";
    echo "<TD width='25'>&nbsp;</TD>";
    echo "  <td  width='100' align='left'><input type='text' size='10' maxlength='10'  name='data_baixa_inicio' id='data_baixa_inicio' rel='data' value='$data_baixa_inicio' class='frm date' /></td>";
    echo "  <td  width='100' align='left'><input type='text' size='10' maxlength='10'  name='data_baixa_fim' id='data_baixa_fim' rel='data' value='$data_baixa_fim' class='frm date' /></td>";

}

if($login_fabrica == 6){
echo "  <TD width='20%' nowrap>";

    echo " Liberado <input type='radio' name='liberado' value='liberado'>&nbsp;&nbsp;&nbsp;N�o Liberado <input type='radio' name='liberado' value='nao_liberado' />";
    echo "  </TD>";
}
echo "</TR>\n";


#HD 22758
if ($login_fabrica == 24 || $login_fabrica == 142) {

    echo "<tr>\n";
        echo "<td>&nbsp;</td>";
        echo "<td align='left'>";
            //HD 286780
            echo 'Estado <br />';
            echo '<select name="estado" id="estado" style="width:120px; font-size:9px" class="frm">';
                echo '<option value=""   ' . (strlen($estado) == 0   ? " selected " : '') . ' >TODOS OS ESTADOS</option>';
                echo '<option value="AC" ' . ($estado == "AC" ? " selected " : '') . '>AC - Acre</option>';
                echo '<option value="AL" ' . ($estado == "AL" ? " selected " : '') . '>AL - Alagoas</option>';
                echo '<option value="AM" ' . ($estado == "AM" ? " selected " : '') . '>AM - Amazonas</option>';
                echo '<option value="AP" ' . ($estado == "AP" ? " selected " : '') . '>AP - Amap�</option>';
                echo '<option value="BA" ' . ($estado == "BA" ? " selected " : '') . '>BA - Bahia</option>';
                echo '<option value="CE" ' . ($estado == "CE" ? " selected " : '') . '>CE - Cear�</option>';
                echo '<option value="DF" ' . ($estado == "DF" ? " selected " : '') . '>DF - Distrito Federal</option>';
                echo '<option value="ES" ' . ($estado == "ES" ? " selected " : '') . '>ES - Esp�rito Santo</option>';
                echo '<option value="GO" ' . ($estado == "GO" ? " selected " : '') . '>GO - Goi�s</option>';
                echo '<option value="MA" ' . ($estado == "MA" ? " selected " : '') . '>MA - Maranh�o</option>';
                echo '<option value="MG" ' . ($estado == "MG" ? " selected " : '') . '>MG - Minas Gerais</option>';
                echo '<option value="MS" ' . ($estado == "MS" ? " selected " : '') . '>MS - Mato Grosso do Sul</option>';
                echo '<option value="MT" ' . ($estado == "MT" ? " selected " : '') . '>MT - Mato Grosso</option>';
                echo '<option value="PA" ' . ($estado == "PA" ? " selected " : '') . '>PA - Par�</option>';
                echo '<option value="PB" ' . ($estado == "PB" ? " selected " : '') . '>PB - Para�ba</option>';
                echo '<option value="PE" ' . ($estado == "PE" ? " selected " : '') . '>PE - Pernambuco</option>';
                echo '<option value="PI" ' . ($estado == "PI" ? " selected " : '') . '>PI - Piau�</option>';
                echo '<option value="PR" ' . ($estado == "PR" ? " selected " : '') . '>PR - Paran�</option>';
                echo '<option value="RJ" ' . ($estado == "RJ" ? " selected " : '') . '>RJ - Rio de Janeiro</option>';
                echo '<option value="RN" ' . ($estado == "RN" ? " selected " : '') . '>RN - Rio Grande do Norte</option>';
                echo '<option value="RO" ' . ($estado == "RO" ? " selected " : '') . '>RO - Rond�nia</option>';
                echo '<option value="RR" ' . ($estado == "RR" ? " selected " : '') . '>RR - Roraima</option>';
                echo '<option value="RS" ' . ($estado == "RS" ? " selected " : '') . '>RS - Rio Grande do Sul</option>';
                echo '<option value="SC" ' . ($estado == "SC" ? " selected " : '') . '>SC - Santa Catarina</option>';
                echo '<option value="SE" ' . ($estado == "SE" ? " selected " : '') . '>SE - Sergipe</option>';
                echo '<option value="SP" ' . ($estado == "SP" ? " selected " : '') . '>SP - S�o Paulo</option>';
                echo '<option value="TO" ' . ($estado == "TO" ? " selected " : '') . '>TO - Tocantins</option>';
            echo '</select>';

        echo "</td>";
        echo "<td colspan='2'></td>";
    echo "</tr>\n";

    if ($login_fabrica == 24) {
    echo "<tr>\n";
        echo "<td></td>";
        echo "<td colspan= '2' align='left'>";

            echo "<table align='left'>";
                echo "<tr>\n";
                    echo "<td><input type='checkbox' name='extrato_pago' value='t' ".(($extrato_pago=='t')?"checked":"")."> Extratos pagos <span style='color:#515151;font-size:10px' title='� obrigat�rio digitar a data inicial e a data final' /> (Per�odo obrigat�rio) </span></TD>\n";
                echo "</tr>\n";
                echo "<tr>\n";
                    echo "<TD><input type='checkbox' name='aguardando_pagamento' value='t' ".(($aguardando_pagamento=='t')?"checked":"")." /> Extratos aguardando pagamento <span style='color:#515151;font-size:10px'> (Per�odo opcional) </span></TD>\n";
                echo "</tr>\n";
            echo "</table>";

        echo "</td>\n";
        echo "<td></td>";
    echo "</tr>\n";
    }

}

/**
    hd-1059101  - Makita, campos
*/
if($login_fabrica == 42){
    //labels
    echo "<TR align='left'>";
    echo "<TD width='50'>&nbsp;</TD>";
    echo "<TD>N� NF M.O. </td>",
         "<td > Valor NF M.O. </td>",
         "<td >N� NF Pe�as </td>",
         "<td > Valor NF Pe�as </td>";
    echo "</tr>";
    //campos
    echo "<TR align='left'>";
    echo "<TD width='50'>&nbsp;</TD>";
    echo "<TD><input type='text' name='nf_autorizacao' size='18' value='$nf_autorizacao' class='frm' ></td>",
         '<td>',
             "<input type='text' name='valor_total' size='18' value='$valor_total' class='frm' >",
        '</td>',
         "<td ><input type='text' name='nf_peca' size='18' value='$nf_peca' class='frm' ></td>",
         "<td ><input type='text' name='valor_nf_peca' size='18' value='$valor_nf_peca' class='frm' ></td>";
    echo "</tr>";
    //labels
    echo "<tr align='left'>";
    echo "<td ></td>",
        "<td >Border� </td>",
         "<td >M�s Refer�ncia  </td>",

        "</tr>";
    //campos
    echo "<TR align='left'>";
    echo "<TD width='50'>&nbsp;</TD>";
    echo "<TD><input type='text' name='bordero' size='18' value='$bordero' class='frm' ></td>",
         '<td ><select name="mes_referencia" id="mes_referencia" style="width:120px; font-size:10px" class="frm">',
                    '<option value="" '  . ($mes_referencia  == ""  ? " selected " : '') . '></option>',
                     '<option value="1" '  . ($mes_referencia  == "1"  ? " selected " : '') . '>Janeiro</option>',
                     '<option value="2" '  . ($mes_referencia  == "2"  ? " selected " : '') . '>Fevereiro</option>',
                     '<option value="3" '  . ($mes_referencia  == "3"  ? " selected " : '') . '>Mar�o</option>',
                     '<option value="4" '  . ($mes_referencia  == "4"  ? " selected " : '') . '>Abril</option>',
                     '<option value="5" '  . ($mes_referencia  == "5"  ? " selected " : '') . '>Maio</option>',
                     '<option value="6" '  . ($mes_referencia  == "6"  ? " selected " : '') . '>Junho</option>',
                     '<option value="7" '  . ($mes_referencia  == "7"  ? " selected " : '') . '>Julho</option>',
                     '<option value="8" '  . ($mes_referencia  == "8"  ? " selected " : '') . '>Agosto</option>',
                     '<option value="9" '  . ($mes_referencia  == "9"  ? " selected " : '') . '>Setembro</option>',
                     '<option value="10" ' . ($mes_referencia  == "10" ? " selected " : '') . '>Outubro</option>',
                     '<option value="11" ' . ($mes_referencia  == "11" ? " selected " : '') . '>Novembro</option>',
                     '<option value="12" ' . ($mes_referencia  == "12" ? " selected " : '') . '>Dezembro</option>',
                '</select>',
            "</td>",

    "</tr>";
}
echo "<tr>";
    
    if($login_fabrica == 1){
	    foreach($estadosBrasil as $linha => $indice){
		    $selected = (in_array($linha,$estados)) ? "SELECTED" : "";
	            $estados_brasil .="<option value='$linha' $selected>$indice</option>";
        }

        $sql_regiao = "select descricao, estados_regiao from tbl_regiao where fabrica = 1";
        $res_regiao = pg_query($con, $sql_regiao);
        for($i=0; $i<pg_num_rows($res_regiao); $i++){
            $descricao  = pg_fetch_result($res_regiao, $i, 'descricao');
            $estados    = pg_fetch_result($res_regiao, $i, 'estados_regiao');
	    $selected = (in_array($estados,$regiao)) ? "SELECTED" : "";

            $regioes .= "<option value='$estados' $selected>$descricao</option>";
        }
        echo "<TR align='left'>";
        echo "<TD width='25'>&nbsp;</TD>";
        echo "<TD width='15'>";
        echo "Regi�o</td>";
        echo "<td>Estado</td>";
        echo "<tr><TD width='25'>&nbsp;</TD>
              <td width='15' align='left'> ";
        echo "<select id='ms' class='frm' name='regiao[]'  multiple='multiple'> 
                $regioes
            </select>";
        echo "</td>";
        echo "<td align='left'>";
            echo "<select  name='estados[]' id='estados' class='frm' multiple='multiple' >";
                echo $estados_brasil;
            echo "</select>";
        echo "</td>";
        echo "<TR>\n";
    }


echo "<TR align='left'>";
echo "<TD width='55'>&nbsp;</TD>";
echo "  <TD width='15'>";
echo "CNPJ</td>",
"<td colspan='2'>Raz�o Social</tr><tr align='left'><TD width='25'>&nbsp;</TD><td>";
echo "<input type='text' name='posto_codigo' id='posto_codigo' size='18' value='$posto_codigo' class='frm' onkeypress='javascript:somente_numero(this);' maxlength='18'>&nbsp;
<img src='imagens/lupa.png' border='0' align='absmiddle' style='cursor: pointer;' onclick=\"javascript: pesquisaPosto (document.frm_extrato.posto_codigo, 'cnpj');\" /></td><td colspan='2'>";

echo "<input type='text' name='posto_nome' id='posto_nome' size='30' value='$posto_nome' class='frm'>&nbsp;
<img src='imagens/lupa.png' border='0' align='absmiddle' onclick=\"javascript: pesquisaPosto (document.frm_extrato.posto_nome, 'nome');\" style='cursor: pointer;' />";
echo "  </TD>";
echo "<TR>\n";
    if(in_array($login_fabrica, array(152))){
?>
    </tr>
    <tr align="left">
        <td></td>
        <td>Estado/Regi�o</td>
    </tr>
    <tr align="left">
        <td></td>
        <td colspan="3">
            <select name="regiao_estado" class="frm" >
		<option value="" ></option>
                <?php
                if ($login_fabrica == 152) {
                	$array_regioes = array(
				"AC,AM,RR,PA,AP,MA,TO,PI,CE,RN,PB,PE,AL,SE,BA,SP",
                                "RO,MT,GO,DF,MG",
                                "RJ,ES,MS,PR,SC,RS"
                        );
                }

                if (count($array_regioes) > 0) {
                ?>
                	<optgroup label="Regi�es" >
                        <?php
                        foreach ($array_regioes as $regiao) {
                        	$selected = ($regiao_estado == $regiao) ? "selected" : "";
                                echo "<option value='{$regiao}'  {$selected} >{$regiao}</option>";
                        }
				?>
                        </optgroup>
                        <optgroup label="Estados" >
                        <?php
                 }

                 foreach ($array_estados() as $sigla => $estado_nome) {
                 	$selected = ($regiao_estado == $regiao) ? "selected" : "";

                        echo "<option value='{$sigla}' {$selected} >{$estado_nome}</option>";
                 }

                 if (count($array_regioes) > 0) {
                 ?>
                 	</optgroup>
                 <?php
                 }
                 ?>
            </select>
        </td>
    </tr>
    <tr>
<?php
    }
if ($login_fabrica == 1) {
    $check_online = ($_POST["tipo_envio_nf"] == "online") ? "checked" : "";
    $check_correios = ($_POST["tipo_envio_nf"] == "correios") ? "checked" : "";
    echo "<tr>
            <td>&nbsp;</td>
            <td align='left'>
                <label for='extratos_eletronicos'>Envio NF Online</a>
                <input type='radio' name='tipo_envio_nf' id='extratos_eletronicos' value='online' $check_online />
            </td>
            <td align='left'>
                Valor Abaixo
            </td>
          </tr>";
    echo "<tr>
            <td>&nbsp;</td>
            <td align='left'>
              <label for='extratos_pendentes'>Envio NF Correios</a>
              <input type='radio' name='tipo_envio_nf' id='extratos_pendentes' value='correios' $check_correios />
            </td>
            <td align='left'>
              <input type='checkbox' name='busca_valor_abaixo' id='busca_valor_abaixo' value='sim' />
              <input type='text' name='valor_abaixo' id='valor_abaixo' size='18' value='$valor_abaixo' class='frm valor' />
            </td>
          </tr>";
}

if($login_fabrica == 15) { ?>
    <tr>
        <td colspan='4' align='left'>
            <input type='checkbox' value='t' name='liberacao' <?PHP if  ($liberacao == 't') {?> checked <?PHP }?>>
                Mostrar somente extratos para libera��o.
        </td>
    </tr>
<?php }

if($login_fabrica == 20){
    // MLG 2009-08-04 HD 136625
    $sql = "SELECT pais,nome FROM tbl_pais where america_latina is TRUE;";    $res = pg_query($con,$sql);
    $p_tot = pg_num_rows($res);
    for ($i = 0; $i<$p_tot; $i++) {
        list($p_code,$p_nome) = pg_fetch_row($res, $i);
        $sel_paises .= "\t\t\t\t<option value='$p_code'";
        $sel_paises .= ($pais==$p_code)?" selected":"";
        $sel_paises .= ">$p_nome</option>\n";
    }
?>
    <tr bgcolor="#D9E2EF" >
        <td>&nbsp;</td>
        <td align='left'>Pa�s<br />
            <select name='pais' size='1' class='frm'>
            <option value="BR">Brasil</option>
            <?echo $sel_paises;?>
            </select>
        </td>

	<?php
	// hd-2223746
	if($login_fabrica == 20) { ?>
	<td align='left'>Status de Extratos<br />
		<select name='filtro_tipo_extrato' size='1' class='frm'>
			<option value='TODOS'>Todos</option>
			<option value='APROVADOS'>Aprovados</option>
			<option value='NAO_APROVADOS'>N&atilde;o Aprovados</option>
		</select>
	</td>
	<?php } ?>

    </tr>
<?}

if(in_array($login_fabrica,array(86,104,146))){

    if($login_fabrica == 104){
        $sqlM = "SELECT tbl_marca.marca,tbl_marca.nome FROM tbl_marca WHERE tbl_marca.fabrica = $login_fabrica AND tbl_marca.marca in( 184,189) ORDER BY tbl_marca.nome";
    }else{
        $sqlM = "SELECT tbl_marca.marca,tbl_marca.nome FROM tbl_marca WHERE tbl_marca.fabrica = $login_fabrica ORDER BY tbl_marca.nome";
    }
    $resM = pg_exec($con,$sqlM);

    if(pg_num_rows($resM) > 0){
        echo "<tr>";
        echo "<TD width='50'>&nbsp;</TD>";
        echo "<td align='left'>";
        if($login_fabrica == 104){
            echo "Empresa <br/><select name='marca' class='frm'>";
            echo "<option value=''>Todas as Empresas </option>";
        }else{
            echo "Marca <br/><select name='marca' class='frm'>";
            echo "<option value=''>Todas Marcas</option>";
        }
        for($i = 0; $i < pg_num_rows($resM); $i++){
            $marca = pg_result($resM,$i,'marca');
            $nome_marca = pg_result($resM,$i,'nome');
            $selected = ($nome_marca == $marca_aux) ? "SELECTED" : "";

            echo "<option value='".$nome_marca."' $selected>";
            if($nome_marca == "VONDER"){
                echo "OVD";
            }else{
                echo $nome_marca;
            }

            echo "</option>";
        }
        echo "</select>";
        echo "</td>";
        echo "</tr>";

	if($login_fabrica == 86){

?>

            <tr style="text-align:left;">
		<td width='50' >&nbsp;</td>
		<td width='70'> Extratos em Aberto<input <?=(($_POST["extrato_pago"]=="f") ||( $_GET["extrato_pago"]=="f")) ? "checked" : ""?> type="radio" name="extrato_pago" value="f" /> </td>
		<td width='50'> Extratos Pagos<input <?=($_POST["extrato_pago"]=="t" || $_GET["extrato_pago"]=="t") ? "checked" : ""?> type="radio" name="extrato_pago" value="t"/> </td>
		<td width='200'> Todos<input <?=($_POST["extrato_pago"]=="todos" || $_GET["extrato_pago"]=="todos") ? "checked" : ""?> type="radio" name="extrato_pago" value="todos"/> </td>
	    </tr>

    <?
	}
    }
}

if($login_fabrica == 1){

    $checked_inibido = (isset($_POST["extratos_inibidos"])) ? "checked" : "";

    echo "<td width='50'> &nbsp; </td>";
    echo "<td colspan='4' align='left'>";
        echo "<input type='checkbox' name='extratos_inibidos' value='sim' $checked_inibido /> Extratos Inibidos";
    echo "<td>";
}

echo "<tr><td colspan='4' style='padding: 20px; '><input type='button' style='width:95px; cursor:pointer;' value='Filtrar' onclick=\"javascript: document.frm_extrato.btnacao.value='filtrar' ; document.frm_extrato.submit() ;\" ALT='Filtrar extratos' border='0' ></td></tr>";

echo "</form>";
echo "</TABLE>\n";

if($login_fabrica == 50){

  if(isset($_GET["dashboard"]) && isset($_GET["mes"])){

    if($_GET["dashboard"] == "sim"){

      $mes_get = $_GET["mes"];

      switch($mes_get){
        case "Janeiro": $mes_get   = "01"; break;
        case "Fevereiro": $mes_get = "02"; break;
        case "Mar�o": $mes_get     = "03"; break;
        case "Abril": $mes_get     = "04"; break;
        case "Maio": $mes_get      = "05"; break;
        case "Junho": $mes_get     = "06"; break;
        case "Julho": $mes_get     = "07"; break;
        case "Agosto": $mes_get    = "08"; break;
        case "Setembro": $mes_get  = "09"; break;
        case "Outubro": $mes_get   = "10"; break;
        case "Novembro": $mes_get  = "11"; break;
        case "Dezembro": $mes_get  = "12"; break;
      }

      $mes_atual = date("n");

      $ano_atual = date("Y", strtotime(date("Y-m-d")." -1 month"));

      $_POST["data_inicial"] = "01/".$mes_get."/".$ano_atual;
      $_POST["data_final"] =  date("t/m/Y", strtotime("$ano_atual-$mes_get-01"));
      $btnacao = "Filtrar";

    }

  }

}

// INICIO DA SQL
if ($btnacao AND strlen($msg_erro) == 0) {

    if($login_fabrica == 1 && isset($_POST["extratos_inibidos"])){

        $cond_inibido = " AND EE.baixado notnull ";

    }

    $data_inicial = $_POST['data_inicial'];
    if (strlen($_GET['data_inicial']) > 0) $data_inicial = $_GET['data_inicial'];

    $data_final   = $_POST['data_final'];
    if (strlen($_GET['data_final']) > 0) $data_final = $_GET['data_final'];


    if($login_fabrica == 91){

        $data_baixa_inicio = "";
        $data_baixa_fim = "";

        if (strlen($_POST['data_baixa_inicio']) > 0){
            $data_baixa_inicio = $_POST['data_baixa_inicio'];
            $x_data_baixa_inicio     = substr ($data_baixa_inicio,6,4) . "-" . substr ($data_baixa_inicio,3,2) . "-" . substr ($data_baixa_inicio,0,2);
        }

        if (strlen($_POST['data_baixa_fim']) > 0){
            $data_baixa_fim = $_POST['data_baixa_fim'];
            $x_data_baixa_fim = substr ($data_baixa_fim,6,4) . "-" . substr ($data_baixa_fim,3,2) . "-" . substr ($data_baixa_fim,0,2);
        }



        if(strlen($x_data_baixa_inicio) == 0 && strlen($x_data_baixa_fim) == 0){
            $sqlJoin_data_baixa = "LEFT JOIN tbl_extrato_pagamento EP ON EP.extrato = EX.extrato";
        }else{
            $x_data_inicial = substr ($data_inicial,6,4) . "-" . substr ($data_inicial,3,2) . "-" . substr ($data_inicial,0,2);
            $sqlJoin_data_baixa = " JOIN tbl_extrato_pagamento EP ON EP.extrato = EX.extrato
                                        AND EP.data_pagamento BETWEEN '{$x_data_baixa_inicio} 00:00:00' AND '{$x_data_baixa_fim} 23:59:59' ";
        }

    }


    if($login_fabrica == 1){
        if(strlen($busca_valor_abaixo) > 0){
            $sqlValorBaixa = " AND EX.total <= $xvalor_abaixo";
        }
    }

    $posto_codigo = $_POST['posto_codigo'];
    if (strlen($_GET['posto_codigo']) > 0) $posto_codigo = $_GET['posto_codigo'];
    if (strlen($_GET['cnpj']) > 0) $posto_codigo = $_GET['cnpj'];

    $data_inicial = str_replace (" " , "" , $data_inicial);
    $data_inicial = str_replace ("-" , "" , $data_inicial);
    $data_inicial = str_replace ("/" , "" , $data_inicial);
    $data_inicial = str_replace ("." , "" , $data_inicial);

    $data_final = str_replace (" " , "" , $data_final);
    $data_final = str_replace ("-" , "" , $data_final);
    $data_final = str_replace ("/" , "" , $data_final);
    $data_final = str_replace ("." , "" , $data_final);

    if (strlen ($data_inicial) == 6) $data_inicial = substr ($data_inicial,0,4) . "20" . substr ($data_inicial,4,2);
    if (strlen ($data_final)   == 6) $data_final   = substr ($data_final  ,0,4) . "20" . substr ($data_final  ,4,2);

    $data_inicial = str_replace (" " , "" , $data_inicial);
    $data_inicial = str_replace ("-" , "" , $data_inicial);
    $data_inicial = str_replace ("/" , "" , $data_inicial);
    $data_inicial = str_replace ("." , "" , $data_inicial);

    $data_final = str_replace (" " , "" , $data_final);
    $data_final = str_replace ("-" , "" , $data_final);
    $data_final = str_replace ("/" , "" , $data_final);
    $data_final = str_replace ("." , "" , $data_final);

    if (strlen ($data_inicial) == 6) $data_inicial = substr ($data_inicial,0,4) . "20" . substr ($data_inicial,4,2);
    if (strlen ($data_final)   == 6) $data_final   = substr ($data_final  ,0,4) . "20" . substr ($data_final  ,4,2);

    if (strlen ($data_inicial) > 0) $data_inicial = substr ($data_inicial,0,2) . "/" . substr ($data_inicial,2,2) . "/" . substr ($data_inicial,4,4);
    if (strlen ($data_final)   > 0) $data_final   = substr ($data_final,0,2)   . "/" . substr ($data_final,2,2)   . "/" . substr ($data_final,4,4);

    $pais = $_POST['pais'];
    if (strlen($_GET['pais']) > 0) $pais = $_GET['pais'];

    $cond_extrato = "";

    if (strlen($extrato) > 0) {
        if ($login_fabrica <> 1 AND $login_fabrica <> 19) {
            $cond_extrato = " AND EX.extrato = $extrato";
        } else {
            $cond_extrato = " AND EX.protocolo = '$extrato'";
        }
    }

    //hd-2223746
    if ($login_fabrica == 20) {
    	if ($filtro_tipo_extrato != 'TODOS') {
	    $cond_extrato .= " AND EX.aprovado IS ";
	    $cond_extrato .= ($filtro_tipo_extrato == "APROVADOS") ? "NOT NULL" : "NULL";
	}
    }

if (($login_fabrica == 15 AND $liberacao == 't') or
    ($login_fabrica == 42 && (strlen ($mes_referencia) > 0 || strlen ($bordero) > 0 || strlen ($valor_total) > 0 ||
        strlen ($valor_nf_peca) > 0 || strlen ($nf_peca) > 0 || strlen ($nf_autorizacao) > 0 )) or
        strlen ($posto_codigo) > 0 OR (strlen ($data_inicial) > 0 and strlen ($data_final) > 0) OR strlen($extrato) > 0 OR $aguardando_pagamento == 't') {

    if ($login_fabrica == 1) $add_1 = " AND       EX.aprovado IS NULL ";

    //--== INICIO - Consulta por data ===============================================
    // hd 26685
    if(strlen ($data_inicial) > 0 AND strlen ($data_final) > 0 AND strlen($extrato) == 0){
            $x_data_inicial = substr ($data_inicial,6,4) . "-" . substr ($data_inicial,3,2) . "-" . substr ($data_inicial,0,2);

            $x_data_final = substr ($data_final,6,4) . "-" . substr ($data_final,3,2) . "-" . substr ($data_final,0,2);

    }

    //monta sql de acordo com os campos
    if ($login_fabrica == 42) {


        if(!empty($bordero)){
            $condSqlMakita .= " and EP.duplicata = $bordero ";
        }

        if(!empty($mes_referencia)){
            $condSqlMakita .= " and EP.mes_referencia = '$mes_referencia' ";
        }

        if(!empty($valor_total)){
            $condSqlMakita .= " and EP.valor_total = $valor_total ";
        }

        if(!empty($valor_nf_peca)){
            $condSqlMakita .= " and EP.valor_nf_peca = $valor_nf_peca ";
        }

        if(!empty($nf_autorizacao)){
            $condSqlMakita .= " and EP.nf_autorizacao = '$nf_autorizacao' ";
        }

        if(!empty($nf_peca)){
            $condSqlMakita .= " and EP.nf_peca = '$nf_peca' ";
        }

    }

    //monta sql de acordo com a condi��o do extrato
    if ($login_fabrica == 42 /*AND !empty($_POST['status_extrato'])*/) {
        $status_extrato = (strlen($_POST['status_extrato']) > 0) ? $_POST['status_extrato'] : "";
        switch($status_extrato){
            case "ag_liberacao":
                $condStatusMakita .= " AND EX.liberado IS NULL ";
                $joinStatusMakita = "";
                $sqlStatusMakita = ""; 
            break;

            case "ag_anexo":
                $condStatusMakita .= " AND EX.liberado IS NOT NULL 
                AND EX.data_recebimento_nf IS NULL ";
                $joinStatusMakita = "";
                $sqlStatusMakita = ""; 
            break;

            case "nf_anexada":
                $condStatusMakita .= " AND EX.liberado IS NOT NULL 
                AND EX.data_recebimento_nf IS NOT NULL 
                AND ES.conferido IS NULL  ";
                $joinStatusMakita = " LEFT JOIN tbl_extrato_status ES ON EX.extrato = ES.extrato AND ES.fabrica = {$login_fabrica} ";
                $sqlStatusMakita = " ES.conferido , "; 
            break;
            case "ex_liberado":
                $condStatusMakita .= " AND EX.liberado IS NOT NULL 
                AND EX.data_recebimento_nf IS NOT NULL 
                AND ES.conferido IS NOT NULL  ";
                $joinStatusMakita = " LEFT JOIN tbl_extrato_status ES ON EX.extrato = ES.extrato AND ES.fabrica = {$login_fabrica} ";
                $sqlStatusMakita = " ES.conferido , "; 
            break;
            default:
                $joinStatusMakita = " LEFT JOIN tbl_extrato_status ES ON EX.extrato = ES.extrato AND ES.fabrica = {$login_fabrica} ";
                $sqlStatusMakita = " ES.conferido , ";
            break;

          
        }
    }
    //monta sql de acordo com a data
    if( ($login_fabrica == 42 ) && (!empty($_POST['tipoData']) ) ){
                /**
            verificar valor para montar a sql

        */
        //valida campo vindo do select
        $tipoData = $_POST['tipoData'];

        switch($_POST['tipoData']){
            case "dataBordero":
                $condSqlMakita .= " and EP.data_bordero between '$x_data_inicial' and '$x_data_final' ";
            break;

            case "dataEntregueFinanceiro":
                $condSqlMakita .= " and EP.data_entrega_financeiro between '$x_data_inicial' and '$x_data_final' ";
            break;

            case "dataAprovacao":
                $condSqlMakita .= " and EP.data_aprovacao between '$x_data_inicial' and '$x_data_final' ";
            break;
            case "dataPagamento":
                $condSqlMakita .= " and EP.data_pagamento between '$x_data_inicial' and '$x_data_final' ";
            break;
        }
    }else{
        if (strlen ($x_data_inicial) > 0 AND strlen ($x_data_final) > 0 AND strlen($extrato) == 0) {
            $add_2 = " AND      EX.data_geracao BETWEEN '$x_data_inicial 00:00:00' AND '$x_data_final 23:59:59'";
        }
    }
    //--== FIM - Consulta por data ==================================================

    #HD 22758
    if ($aguardando_pagamento == 't') {

        if($login_fabrica <> 24){
            if (strlen($_GET['data_inicial'])==0 or strlen($_GET['data_final'])==0){
                $data_inicial   = "";
                $data_final     = "";
                $x_data_inicial = "";
                $x_data_final   = "";
                $add_2          = "";
            }
        }

        $add_1 = "  AND       EP.extrato_pagamento IS NULL
                    AND       EX.aprovado       IS NOT NULL ";
    }

    #HD22758 - HD1918351

    if(($login_fabrica == 86 && $extrato_pago != "todos") || ($login_fabrica != 86)){
    	if ($extrato_pago == 't'){
                $add_5 = " AND       EP.data_pagamento IS NOT NULL ";
    	}

    	if($login_fabrica == 86){
    	    if($extrato_pago == "f"){
    		$add_5 = "AND EP.data_pagamento IS NULL";
    	    }
    	}
    }

    if ($login_fabrica == 15 AND $liberacao == 't') {
            $add_6 = " AND liberado IS NULL";
    }

    if($login_fabrica == 6) {
        if($liberado == 'liberado') {
            $add_6 = " AND liberado IS NOT NULL";
        }
        if($liberado == 'nao_liberado') {
            $add_6 = " AND liberado IS NULL";
        }
    }

    if($login_fabrica == 20) {
        $add_7 = " AND liberado_telecontrol IS not null ";
    }

    if (strlen($estado) > 0) {
        $add_8 = " AND PF.contato_estado = '$estado' ";
    }

    if(($login_fabrica == 104 OR $login_fabrica == 86 OR $login_fabrica == 146) AND !empty($marca_aux)){
        $joins = "LEFT JOIN tbl_os_extra          OE ON OE.extrato    = EX.extrato
                  JOIN tbl_os ON OE.os = tbl_os.os AND tbl_os.fabrica = $login_fabrica
                  JOIN tbl_produto ON tbl_os.produto = tbl_produto.produto AND tbl_produto.fabrica_i = $login_fabrica
                  JOIN tbl_marca ON tbl_produto.marca = tbl_marca.marca AND tbl_marca.fabrica = $login_fabrica";
        if ($login_fabrica == 104){
            $add_9 = (strtoupper($marca_aux) == "DWT" AND $login_fabrica == 104) ? " AND tbl_marca.nome = '$marca_aux' " : "  AND tbl_marca.nome <> 'DWT' ";
        }
        if ($login_fabrica == 86){
            $add_9 = (strtoupper($marca_aux) == "FAMASTIL" AND $login_fabrica == 86) ? " AND tbl_marca.nome = '$marca_aux' " : "  AND tbl_marca.nome like 'Taurus%' ";
        }
        if ($login_fabrica == 146) {
            $add_9 = " AND tbl_marca.nome = '{$marca_aux}' ";
        }
    }

    //--== INICIO - Consulta por data ===============================================
    $xposto_codigo = str_replace (" " , "" , $posto_codigo);
    $xposto_codigo = str_replace ("-" , "" , $xposto_codigo);
    $xposto_codigo = str_replace ("/" , "" , $xposto_codigo);
    $xposto_codigo = str_replace ("." , "" , $xposto_codigo);

    if (strlen ($posto_codigo) > 0 OR strlen ($posto_nome) > 0 ){
        $sql = "SELECT posto
                FROM tbl_posto
                JOIN tbl_posto_fabrica USING(posto)
                WHERE fabrica = $login_fabrica ";
        if (strlen ($posto_codigo) > 0 ) $sql .= " AND tbl_posto.cnpj = '$xposto_codigo' ";
        if (strlen ($posto_nome) > 0 )   $sql .= " AND tbl_posto.nome ILIKE '%$posto_nome%' ";

        $res = pg_query ($con,$sql);
        if(pg_num_rows($res) > 0){
            $posto = pg_fetch_result($res,0,0);
            $add_3 = " AND EX.posto = $posto " ;
        }
    }
    //--== FIM - Consulta por Posto ==============================================

    if($login_fabrica == 20) $add_4 = " AND PO.pais = '$pais' ";


//se for Fricon (52) monta condi��o para filtrar tbm pela OS
    if(!empty($nroOs)){
                $sqlOS = " JOIN tbl_os_extra ON EX.extrato = tbl_os_extra.extrato
                            JOIN tbl_os ON tbl_os.os = tbl_os_extra.os and tbl_os.sua_os =  '$nroOs' ";
    }

    if ( isset($_POST['tipo_envio_nf']) ) {
        $tipo_envio_nf = ($_POST['tipo_envio_nf'] == "online") ? "online" : "correios";
        $join_eletronico = "JOIN tbl_tipo_gera_extrato ON PF.posto = tbl_tipo_gera_extrato.posto AND PF.fabrica = tbl_tipo_gera_extrato.fabrica AND tipo_envio_nf like '%$tipo_envio_nf%'";
    }
    if($login_fabrica == 134){
	   $camposSql = getCamposSqlThermoSystem();
    }else{
	$camposSql = "PO.posto                                                 ,
                    PO.nome                                                  ,
                    PO.cnpj                                                  ,
                    PF.contato_estado  as estado                             ,
                    PF.contato_email                                     AS email        ,
                    PF.credenciamento                    ,
                    PF.codigo_posto                                          ,
                    PF.distribuidor                                          ,
                    PF.imprime_os                                            ,
                    TP.descricao                                         AS tipo_posto   ,
                    EX.extrato                                               ,
                    EX.bloqueado                                             ,
                    TO_CHAR (EX.liberado,'dd/mm/yyyy')             AS liberado,
                    EX.estoque_menor_20                                      ,
                    TO_CHAR (EX.aprovado,'dd/mm/yyyy')                   AS aprovado     ,
                    LPAD (EX.protocolo,6,'0')                            AS protocolo    ,
                    TO_CHAR (EX.data_geracao,'dd/mm/yyyy')               AS data_geracao ,
                    EX.data_geracao                                      AS xdata_geracao,
                    EX.total                                                 ,
                    EX.pecas                                                 ,
                    EX.mao_de_obra                                           ,
                    EX.avulso                                             AS avulso       ,
                    EX.recalculo_pendente                                    ,
                    EP.nf_autorizacao                                        ,
                    EP.baixa_extrato,
                    TO_CHAR (EX.previsao_pagamento,'dd/mm/yyyy')          AS previsao_pagamento,
                    TO_CHAR (EX.data_recebimento_nf,'dd/mm/yyyy')         AS data_recebimento_nf,
                    TO_CHAR (EP.data_pagamento,'dd/mm/yyyy')              AS baixado      ,
                    EP.valor_liquido                                         ,
                    EE.nota_fiscal_devolucao                                 ,
                    EE.nota_fiscal_mao_de_obra                               ,
                    to_char(EE.data_coleta,'dd/mm/yyyy')                 AS  data_coleta     ,
                    to_char(EE.data_entrega_transportadora,'dd/mm/yyyy') AS  data_entrega_transportadora,
                    to_char(EE.emissao_mao_de_obra,'dd/mm/yyyy')         AS  emissao_mao_de_obra,
                    count(tbl_os_extra.os) as qtde,
                    tbl_admin.nome_completo " ;
    }
//select que traz os results sets da consulta (a��o do botao Filtrar)
    $sql = "SELECT DISTINCT {$camposSql}

                    INTO    TEMP tmp_extrato_consulta /*hd 39502*/
            FROM      tbl_extrato           EX 
            LEFT JOIN tbl_os_extra ON EX.extrato = tbl_os_extra.extrato AND tbl_os_extra.i_fabrica = $login_fabrica";

            if($login_fabrica== 52){
                $sql .= $sqlOS;
            }
            if($login_fabrica == 42){
                if(!empty($condSqlMakita)){
                    //Na sql normal est� fazendo um left join, mas como � preciso filtrar os resultados, tem que fazer um Inner Join
                    $sqlMakita = "JOIN tbl_extrato_pagamento EP ON EX.extrato    = EP.extrato ".$condSqlMakita;
                }

            }
            $sql .= " JOIN      tbl_posto             PO on PO.posto = EX.posto
            JOIN      tbl_posto_fabrica     PF ON EX.posto      = PF.posto      AND PF.fabrica = $login_fabrica
            JOIN      tbl_tipo_posto        TP ON TP.tipo_posto = PF.tipo_posto AND TP.fabrica = $login_fabrica";

            if($login_fabrica == 42){
                if(!empty($condSqlMakita)){
                    //Na sql normal est� fazendo um left join, mas como � preciso filtrar os resultados, tem que fazer um Inner Join
                    $sqlMakita = " JOIN tbl_extrato_pagamento EP ON EX.extrato    = EP.extrato ".$condSqlMakita;
                    $sql .= $sqlMakita;
                }else{
                    $sql .= " LEFT JOIN tbl_extrato_pagamento EP ON EX.extrato    = EP.extrato ";
                }

            }else if($login_fabrica == 91 ){

                $sql .= $sqlJoin_data_baixa;

            }else{
                $sql .= " LEFT JOIN tbl_extrato_pagamento EP ON EX.extrato    = EP.extrato ";
            }

            $condicao = "";

            if(in_array($login_fabrica, array(152))){
                $regiao_estado = $_POST['regiao_estado'];
				$regiao_estado = str_replace(",", "','",$regiao_estado);
				if (strlen($regiao_estado) > 0) {
					$condicao = " AND PO.estado IN ('$regiao_estado')";
				}
                // � atribu�do novamente o valor original do POST para a vari�vel utilizar no elemento select
                $regiao_estado = $_POST['regiao_estado'];
            }

            $sql .= " LEFT JOIN tbl_extrato_extra     EE ON EX.extrato    = EE.extrato
            LEFT JOIN tbl_admin ON EE.admin = tbl_admin.admin
            $joins
            $join_eletronico
            WHERE     EX.fabrica = $login_fabrica
            ".((!in_array($login_fabrica, array(139))) ? "AND       PF.distribuidor IS NULL" : "")."
            $where_estado_regiao
            $condicao
            $cond_extrato
            $sqlValorBaixa
            $add_1
            $add_2
            $add_3
            $add_4
            $add_5
            $add_6
            $add_7
            $add_8
            $add_9 
            $cond_inibido
            ";
    if($login_fabrica == 134){
        $sql .= getCamposGroupThermoSystem();
    }else{
        $sql .= "  GROUP BY PO.posto,
                    PO.nome,
                    PO.cnpj,
                    PF.contato_estado,
                    PF.contato_email,
                    PF.credenciamento,
                    PF.codigo_posto,
                    PF.distribuidor,
                    PF.imprime_os,
                    TP.descricao,
                    EX.extrato,
                    EX.bloqueado,
                    EX.liberado,
                    EX.estoque_menor_20,
                    EX.aprovado,
                    EX.protocolo,
                    EX.data_geracao,
                    EX.data_geracao,
                    EX.total,
                    EX.pecas,
                    EX.mao_de_obra,
                    EX.avulso,
                    EX.recalculo_pendente,
                    EP.nf_autorizacao,
                    EP.baixa_extrato,
                    EX.previsao_pagamento,
                    EX.data_recebimento_nf,
                    EP.data_pagamento,
                    EP.valor_liquido,
                    EE.nota_fiscal_devolucao,
                    EE.nota_fiscal_mao_de_obra,
                    EE.data_coleta,
                    EE.data_entrega_transportadora,
                    EE.emissao_mao_de_obra,      
                    tbl_admin.nome_completo "; 
    }
    $sql .= ($login_fabrica <> 1) ? " ORDER BY PO.nome, EX.data_geracao" : " ORDER BY PF.codigo_posto, EX.data_geracao";

    $res = pg_query ($con,$sql);

    /* echo nl2br($sql);exit; */
    /* echo $sql; */
    /* hd 39502 */
    if ($login_fabrica==20) {
        $sql = "ALTER table tmp_extrato_consulta add column total_cortesia double precision";
        $res = pg_query ($con,$sql);

        $sql = "SELECT tbl_os_extra.extrato,sum(tbl_os.mao_de_obra) + sum(tbl_os.pecas) AS total
            INTO TEMP tmp_extrato_consulta_aux
                          FROM tbl_os
                          JOIN tbl_os_extra ON tbl_os.os = tbl_os_extra.os AND tbl_os_extra.i_fabrica=$login_fabrica
                          WHERE tbl_os.fabrica = $login_fabrica
                            AND   tbl_os.tipo_atendimento = 16
                AND tbl_os_extra.extrato in ( select extrato from tmp_extrato_consulta )
            GROUP BY tbl_os_extra.extrato;";

        $res = pg_query ($con,$sql);



        $sql = "UPDATE tmp_extrato_consulta SET
                                        total_cortesia = (
                                                SELECT tmp_extrato_consulta_aux.total
                                                FROM tmp_extrato_consulta_aux
                                                WHERE tmp_extrato_consulta_aux.extrato = tmp_extrato_consulta.extrato);";
        $res = pg_query ($con,$sql);


        /*$sql = "UPDATE tmp_extrato_consulta SET
                    total_cortesia = (
                        SELECT sum(tbl_os.mao_de_obra) + sum(tbl_os.pecas)
                        FROM tbl_os
                        JOIN tbl_os_extra USING(os)
                        WHERE extrato = tmp_extrato_consulta.extrato
                        AND   tbl_os.tipo_atendimento = 16
                    )";
        $res = pg_query ($con,$sql);*/
    }

    $sql = "SELECT * FROM tmp_extrato_consulta";

    if ($login_fabrica == 156) {
        $sql = "SELECT * FROM tmp_extrato_consulta WHERE extrato IN (
            SELECT DISTINCT extrato FROM tmp_extrato_consulta
            JOIN tbl_os_extra USING(extrato)
            JOIN tbl_os using(os)
            WHERE hd_chamado IS NOT NULL
        ) OR extrato IN (
            SELECT DISTINCT tmp_extrato_consulta.extrato
            FROM tmp_extrato_consulta
            JOIN tbl_os_extra ON tbl_os_extra.extrato = tmp_extrato_consulta.extrato
              AND tbl_os_extra.i_fabrica = $login_fabrica 
            JOIN tbl_hd_chamado_extra USING(os)
        )";
    }

    $res = pg_query ($con,$sql);

    if($login_fabrica == 86 OR $login_fabrica == 20){

        $data = date("d-m-Y-H:i");
        $fileName = "consulta_extratos".$data.".xls";

        $fp = fopen("/tmp/{$fileName}", "w");

        montaArquivo($fp, $res);
        fclose($fp);
        if (file_exists("/tmp/{$fileName}")) {

            system("mv /tmp/{$fileName} xls/{$fileName}");

            $relatorio_excel = "xls/{$fileName}";
        }
    }

    $qtde_extratos = pg_num_rows ($res);

    if ($qtde_extratos == 0) {
        echo "<center><div style='font-family : arial; color: #000000; font-size: 12px'>N�o Foram Encontrados Resultados para esta Pesquisa</div></center>";
    }
    if (pg_num_rows ($res) > 0) {

        $legenda_avulso="";
        if($login_fabrica == 20 ) {
            $legenda_avulso=" (Tamb�m Identifica Imposto para paises da Am�rica Latina)";
        }

        //HD 237498: Marcando os extratos que possuem OS em interven�ao de KM em aberto
        if (in_array($login_fabrica, $intervencao_km_extrato)) {
            echo "<table width='700px' class='tabela' border='0' cellspacing='0' cellpadding='0' align='center'>";
            echo "<tr>";
            echo "<td align='center' width='16' bgcolor='#FFCC99'>&nbsp;</td>";
            echo "<td align='left'><&nbsp; OS com Interven��o de KM em aberto</td>";
            echo "</tr><br>";
        }

        echo "<br /><table width='700px' border='0' cellspacing='0' cellpadding='0' align='center'>";
        echo "<tr>";
        echo "<td align='center' width='16' bgcolor='#FFE1E1'>&nbsp;</td>";
        echo "<td align='left'>&nbsp; Extrato Avulso $legenda_avulso</td>";
        echo "</tr>";

        if($login_fabrica==6){//hd 3471
            echo "<tr>";
            echo "<td align='center'>&nbsp;</td>";
            echo "<td align='left'>&nbsp; Extrato com varia��o superior a 15%</td>";
            echo "</tr>";
        }
        if($login_fabrica==1){
            echo "<tr>";
                echo "<td align='center' width='16'>&nbsp;</td>";
                echo "<td align='left'>&nbsp; Extrato Bloqueado</td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td align='center' width='16' >&nbsp;</td>";
                echo "<td align='left'>&nbsp; Extrato do Posto com itens de estoque menor que 20s</td>";
            echo "</tr>";
            echo "<tr>";
                echo "<td align='center' width='16' bgcolor='#ffffb2'>&nbsp;</td>";
                echo "<td align='left'>&nbsp; Extrato Inibido</td>";
            echo "</tr>";

        }
        echo "</table> <br />";

        if($login_fabrica == 91){
            $total_valor_liquido = 0;
            $total_total = 0;
        }

        for ($i = 0 ; $i < pg_num_rows ($res) ; $i++) {

            $posto                   = trim(pg_fetch_result($res,$i,posto));
            $codigo_posto            = trim(pg_fetch_result($res,$i,codigo_posto));
            $credenciamento          = trim(pg_fetch_result($res,$i,'credenciamento'));
            $nome                    = trim(pg_fetch_result($res,$i,nome));
            $posto_estado            = trim(pg_fetch_result($res,$i,estado));
            $email                   = trim(pg_fetch_result($res,$i,email));
            $tipo_posto              = trim(pg_fetch_result($res,$i,tipo_posto));
            $extrato                 = trim(pg_fetch_result($res,$i,extrato));
            $data_geracao            = trim(pg_fetch_result($res,$i,data_geracao));
            $qtde_os_ex                 = trim(pg_fetch_result($res,$i,qtde));
            $total                   = trim(pg_fetch_result($res,$i,total));
            $nf_autorizacao          = trim(pg_fetch_result($res,$i,nf_autorizacao));
            $previsao_pagamento      = trim(pg_fetch_result($res,$i,previsao_pagamento));
            $data_recebimento_nf     = trim(pg_fetch_result($res,$i,data_recebimento_nf));
            $baixado                 = trim(pg_fetch_result($res,$i,baixado));
            $baixa_extrato           = trim(pg_fetch_result($res,$i,"baixa_extrato"));
    	    if(strlen($baixa_extrato) > 0){
    		  $baixa_extrato = new DateTime($baixa_extrato);
    		  $baixa_extrato = $baixa_extrato->format("d/m/Y");
    	    }else{
    		  $baixa_extrato = "";
    	    }
            $distribuidor            = trim(pg_fetch_result($res,$i,distribuidor));
            $xtotal                  = round($total);
            $soma_total = $soma_total + $total; //HD 49532
            $total                   = number_format ($total,2,',','.');

            /* hd 39502 */
            if ($login_fabrica == 20) {
                $total_cortesia = trim(pg_fetch_result($res,$i,total_cortesia));
                $total_cortesia = number_format ($total_cortesia,2,',','.');
            }

            $liberado                    = trim(pg_fetch_result($res,$i,liberado));
            $aprovado                    = trim(pg_fetch_result($res,$i,aprovado));
            $estoque_menor_20            = trim(pg_fetch_result($res,$i,estoque_menor_20));
            $protocolo                   = trim(pg_fetch_result($res,$i,protocolo));
            $nota_fiscal_devolucao       = trim(pg_fetch_result($res,$i,nota_fiscal_devolucao));
            $nota_fiscal_mao_de_obra     = trim(pg_fetch_result($res,$i,nota_fiscal_mao_de_obra));
            $data_coleta                 = trim(pg_fetch_result($res,$i,data_coleta));
            $data_entrega_transportadora = trim(pg_fetch_result($res,$i,data_entrega_transportadora));
            $xdata_geracao               = trim(pg_fetch_result($res,$i,xdata_geracao));
            $bloqueado                   = trim(pg_fetch_result($res,$i,bloqueado));
            $recalculo_pendente          = trim(pg_fetch_result($res,$i,recalculo_pendente));

            $pecas              = trim(pg_fetch_result($res,$i,pecas));
            $mao_de_obra        = trim(pg_fetch_result($res,$i,mao_de_obra));
            $avulso             = trim(pg_fetch_result($res,$i,avulso));

            $pecas       = number_format($pecas,2,',','.');
            $mao_de_obra = number_format($mao_de_obra,2,',','.');
            $avulso      = number_format($avulso,2,',','.');

            //HD 145478: Nome do admin que aprovou o extrato
            $auditor = trim(pg_fetch_result($res, $i, 'nome_completo'));

            //HD 12104
            if ($login_fabrica == 14) {
                $imprime_os          = trim(pg_fetch_result($res,$i,imprime_os));
                $emissao_mao_de_obra = trim(pg_fetch_result($res,$i,emissao_mao_de_obra));// HD 209349
            }

            $msg_os_deletadas="";

            if (trim(pg_fetch_result($res,$i,valor_liquido)) <> '') {
                $total_valor_liquido += pg_fetch_result($res,$i,valor_liquido);
                $valor_liquido = number_format (trim(pg_fetch_result($res,$i,valor_liquido)),2,',','.');
            }else{
                $valor_liquido = number_format(0,2,',','.');
            }

            if ($i == 0) {
                echo "<form name='Selecionar' method='post' action='$PHP_SELF'>\n";
                echo "<input type='hidden' name='btnacao' value=''>";

            /*
            if ($login_fabrica == 15 or $login_fabrica == 35) {
                $totalreg=pg_num_rows ($res);
                echo "<a href=\"javascript:conta_os_tudo();\" id='conta_os_$i'>VER TUDO</a>";
            } elseif ($login_fabrica == 20){
                $totalreg=pg_num_rows ($res);
                echo "<a href=\"javascript:conta_os_tudo();\" id='conta_os_$i'>Ver todas OS</a>";
            } else{
                $totalreg=pg_num_rows ($res);
                if ($totalreg > 100) {
                    echo "<button type='button' id='conta_os_$i' onClick=\"javascript:conta_os_tudo();\">Ver todas OS</button>";
                    //echo "<a href=\"javascript:conta_os_tudo();\" id='conta_os_$i'>Ver todas OS</a>";
                }else{?>
                    <script>
                        $(function(){
                            conta_os_tudo();
                        });
                    </script>
                <?php
                }
            }
            */
     
            echo "<input type='hidden' name='total_res' id='total_res' value='$totalreg'>";

            echo "<table width='700px' align='center' border='0' id='grid_list' cellspacing='0' cellpadding='2' class='tabela tablesorter'>\n";

            echo "<thead>";
            echo "<tr class='titulo_coluna'>";
        //  if ($login_fabrica == 14)

                if ($login_fabrica == 24) {
                    echo "<th align='center' class='titulo_coluna' nowrap>Soma <input type='checkbox' onClick=\"somarExtratos('todos')\"></th>";
                }

                if ($login_fabrica == 20) {
                    echo "<th align='center' class='titulo_coluna' nowrap>Soma Extrato<br> por marca<input type='checkbox'id='checkAll' onclick='selecionaTodos();'></th>";
                    echo "<th align='center' class='titulo_coluna' nowrap>Aprovar Todos<br> Selecionados<input type='checkbox' id='checar' name='acaoTodas' value='Aprovar' onclick='selecionarExtratos();'></th>";

                }

                echo "<th align='center' class='titulo_coluna' nowrap style='width:85px;'>C�digo</th>";
                if ($telecontrol_distrib) {
                    echo "<th align='center' class='titulo_coluna'>Lote/NF</th>";
                }
                echo "<th align='center' class='titulo_coluna' nowrap>Nome do Posto</th>\n";
                echo "<th align='center' class='titulo_coluna' nowrap style='width:65px;' >UF</th>\n";
                if ($login_fabrica == 1) echo "<th align='center' class='titulo_coluna'>Credenciamento</th><th align='center' class='titulo_coluna' nowrap>Tipo</th>\n";
                echo ($login_fabrica == 1 OR $login_fabrica == 19) ? "<th align='center' class='titulo_coluna'>Protocolo</th>\n" : "<th align='center' class='titulo_coluna' nowrap style='width:85px;' >Extrato</th>\n";

                if($login_fabrica == 86 OR $login_fabrica == 104){
                    if($login_fabrica == 104){
                        echo "<th align='center' class='titulo_coluna'>Empresa</th>";
                    }else{
                        echo "<th align='center' class='titulo_coluna'>Marca</th>";
                    }
                }

                echo "<th align='center' class='titulo_coluna' nowrap style='width:75px;'>Data</th>\n";
                if($login_fabrica == 129){
                    echo "<th align='center' class='titulo_coluna' nowrap style='width:75px;'>Liberado</th>\n";
                }
                echo "<th align='center' class='titulo_coluna' nowrap style='width:85px;'>Qtde OS</th>\n";

                if ($login_fabrica == 1) {

                    echo "<th align='center' class='titulo_coluna'>Total Pe�a</th>\n";
                    echo "<th align='center' class='titulo_coluna'>Total MO</th>\n";
                    echo "<th align='center' class='titulo_coluna'>Total Avulso</th>\n";
                    echo "<th align='center' class='titulo_coluna'>Total Geral</th>\n";
                    echo "<th align='center' class='titulo_coluna'>Obs.</th>\n";

                } else {

                    //hd 39502
                    if ($login_fabrica == 20) {
                        echo "<th align='center' class='titulo_coluna' nowrap style='width:110px;'>Total cortesia</th>\n";
                        echo "<th align='center' class='titulo_coluna' nowrap style='width:105px;'>Total geral</th>\n";
                    } else {
                        echo "<th align='center' class='titulo_coluna'>Total</th>\n";
                    }

                    if ($login_fabrica == 6) {//hd 3471
                        echo "<th align='center' class='titulo_coluna'><acronym title='M�dia de valor pago nos �ltimos 6 meses' style='cursor: help;'>M�dia</th>\n";

                    }
                    // SONO - 04/09/206 exibir valor_liquido para intelbras //
                    if ($login_fabrica == 14 || $login_fabrica == 91) {
                        echo "<th align='center' class='titulo_coluna' nowrap>Total L�quido</th>\n";
                    }
                }

                if ($login_fabrica == 20) {
                    echo "<th align='center' class='titulo_coluna' nowrap style='width:100px;'>N.F.<br />M. De Obra</th>\n";
                    echo "<th align='center' class='titulo_coluna' nowrap style='width:100px;'>N.F.<br />Remessa</th>\n";
                    echo "<th align='center' class='titulo_coluna' nowrap style='width:85px;'>Data<br />Coleta</th>\n";
                    echo "<th align='center' class='titulo_coluna' nowrap style='width:100px;'>Entrega<br />Transportadora</th>\n";
                }

                if ($login_fabrica == 14) {//HD 209349
                    echo "<th align='center' class='titulo_coluna'>N.F.<br />M. De Obra</th>\n";
                    echo "<th align='center' class='titulo_coluna'>Data<br />Envio NF</th>\n";
                    echo "<th align='center' class='titulo_coluna'>Data<br />Recebimento NF</th>\n";
                }

                if($login_fabrica == 45 or $login_fabrica == 80) echo "<th align='center' class='titulo_coluna' nowrap>Nota Fiscal</th>";
                if($login_fabrica == 151) echo "<th align='center' class='titulo_coluna' nowrap>Nota Fiscal de Servi�o</th>";

                if($login_fabrica == 20) echo "<th align='center' class='titulo_coluna' nowrap style='width:110px;'>Auditado em</th>";
                if($login_fabrica == 20) echo "<th align='center' class='titulo_coluna'>Auditor</th>";
                else                     echo "<th align='center' class='titulo_coluna'><label title='Data de Pagamento'>Data Baixa</th>\n";

                if (in_array($login_fabrica,array(6,7,14, 15, 11 , 24, 25, 35, 40, 50, 43, 51,46, 47, 74, 59, 30,115,116,117)) or ($login_fabrica > 51)) {
                    if ($recalculo_pendente == 't') {
                        echo "<th align='center' class='titulo_coluna'>*Aguardando recalculo</th>\n";
                    } 
                        echo "<th align='center' class='titulo_coluna'>Liberar <input type='checkbox' class='frm' name='marcar' value='tudo' title='Selecione ou desmarque todos' onClick='check(this.form.liberar);'></th>\n";
                    
                    if ($login_fabrica == 11 OR $login_fabrica == 25) echo "<th align='center' class='titulo_coluna' nowrap>Posto sem<br />email</th>\n";
                }

                if ($login_fabrica == 1) {
                    echo "<th class='titulo_coluna'>Tipo envio NF</th>";
                    echo "<th class='titulo_coluna'>Inibir</th>";
                    echo "<th align='center' class='titulo_coluna'>Acumular <input type='checkbox' class='frm' name='marcar' value='tudo' title='Selecione ou desmarque todos' onClick='check(this.form.acumular);'></th>\n";
                }

                echo "<th align='center' class='titulo_coluna' colspan='".($login_fabrica==85?2:3)."'>Valores Adicionais ao Extrato</th>\n";

                if ($login_fabrica == 50 or $login_fabrica == 15) {
                    echo "<th align='center' class='titulo_coluna' >Previsao de Pagamento</th>";
                    echo "<th align='center' class='titulo_coluna'>Data Chegada</th>";
                }

                if ($login_fabrica == 45) {//HD 66773
                    echo "<th align='center' class='titulo_coluna'>Acumular</th>";
                }

                if ($login_fabrica == 24 ) {

                    echo "<th align='center' class='titulo_coluna'>
                                <span title=\"Op��o para acumular v�lida apenas para extratos com valor de at� R$50,00.\">Acumular</span>
                                <input type='checkbox' class='frm' name='marcar' value='tudo' title='Selecione ou desmarque todos' onClick='check(this.form.acumular);'>
                            </th>";

                }

                // hd 12104
                if ($login_fabrica == 14) {
                    echo "<th align='center' class='titulo_coluna'>Liberar 10%</th>";
                }

                if ($login_fabrica == 35) {
                    echo "<th align='center' class='titulo_coluna'>A��es</th>";
                }

                if(in_array($login_fabrica,array(85))):
                ?>
                    <script type="text/javascript">
                        function checkPrintCheckBox(element){
                            if(element.checked){
                                $('input[type=checkbox][extrato].print').each(function(i,e){
					e.checked = true;
				});
                            }
                            else{
                                $('input[type=checkbox][extrato].print').each(function(i,e){
					e.checked = false;
				});
                            }
                        }
                    </script>
                    <th class='titulo_coluna'>Impress�o <input type="checkbox" onclick="checkPrintCheckBox($(this)[0])" /></th>
                <?php
                endif;


                if($login_fabrica == 30){
                ?>
                    <th align="center" class='titulo_coluna'>N� OC</th>
                    <th align="center" class='titulo_coluna'>Data Pagamento</th>
                    <th align="center" class='titulo_coluna'>Valor Pago</th>
                    <th align="center" class='titulo_coluna'>Nota Fiscal</th>
                    <th align="center" class='titulo_coluna'>Valor descontado do Encontro de Contas</th>
                    <th></th>
                <?php
                }

                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
            }

            $cor = ($i % 2 == 0) ? "#F7F5F0" : "#F1F4FA";

            ##### LAN�AMENTO DE EXTRATO AVULSO - IN�CIO #####
            if (strlen($extrato) > 0) {
                $sql = "SELECT count(*) as existe
                        FROM   tbl_extrato_lancamento
                        WHERE  extrato = $extrato
                        and    fabrica = $login_fabrica";
                $res_avulso = pg_query($con,$sql);

                if (@pg_num_rows($res_avulso) > 0) {
                    if (@pg_fetch_result($res_avulso, 0, existe) > 0) $cor = "#FFE1E1";
                }

            }
            ##### LAN�AMENTO DE EXTRATO AVULSO - FIM #####

            //HD 237498: Marcando os extratos que possuem OS em interven�ao de KM em aberto
            if (in_array($login_fabrica, $intervencao_km_extrato)) {
                $km_pendente = verifica_km_pendente_extrato($extrato);

                if ($km_pendente) {
                    $cor = "#FFCC99";
                }
            }

            if ($login_fabrica == 6) {//hd 3471
                $ssql = "SELECT sum(X.total) as total, count(total) as qtde
                        FROM (
                        select posto,
                        total
                        from tbl_extrato
                        where fabrica = $login_fabrica
                        and posto = $posto
                        and data_geracao < '$xdata_geracao'
                        order by extrato
                        desc limit 6) as X";
                $rres = pg_query($con,$ssql);
                if(pg_num_rows($rres)>0){
                    $total_acumulado = pg_fetch_result($rres,0,total);
                    $qtde = pg_fetch_result($rres,0,qtde);
                    if($qtde>0){
                        $total_acumulado = $total_acumulado/$qtde;
                        if($xtotal>round($total_acumulado*1.15)){//hd 3471
                            $cor = "#FFCC99";
                        }
                    }
                }
            }

            if($login_fabrica == 1){

                $sql_inibido = "SELECT baixado FROM tbl_extrato_extra WHERE extrato = {$extrato}";
                $res_inibido = pg_query($con, $sql_inibido);

                $baixado_inibido = pg_fetch_result($res_inibido, 0, "baixado");
                $cor = (strlen($baixado_inibido) > 0) ? "#FFFFB2" : $cor;
                $checked_inibido = (strlen($baixado_inibido) > 0) ? "checked" : "";

            }

            echo "<tr class='linha_$extrato' bgcolor='$cor'>\n";

            if ($login_fabrica == 24) {
                echo "<td align='center' nowrap><input type='checkbox' name='extrato_$i' rel='somatorio' value='$xtotal' onClick='somarExtratos()'></td>\n";
            }

            if ($login_fabrica == 20) {
                echo "<td align='center' nowrap><input type='checkbox' class='check extrato_calcula' name='extrato_$i' value='$extrato'></td>\n";

                echo "<td align='center' nowrap><input type='checkbox' class='check1' name='extrato__$i' value='$extrato'></td>\n";
                echo "<input type='hidden' name='aprovado_$i' value='$aprovado'>";

            }

            echo "<td align='left'>$codigo_posto</td>\n";

            if (strlen($extrato) > 0 and $telecontrol_distrib) {
                $sqllote = "SELECT tbl_distrib_lote_os.os, tbl_distrib_lote.lote,
                            tbl_distrib_lote_os.nota_fiscal_mo
                            FROM tbl_distrib_lote_os
                            JOIN tbl_os_extra USING(os)
                            JOIN tbl_distrib_lote using(distrib_lote)
                        WHERE tbl_os_extra.extrato = $extrato";
                $reslote = pg_query($con,$sqllote);
                if(pg_num_rows($reslote) > 0){
                    $lote = trim(pg_fetch_result($reslote,0,lote));
                    $nota_fiscal_mo = trim(pg_fetch_result($reslote,0,nota_fiscal_mo));
                    echo "<td align='center' nowrap>$lote - $nota_fiscal_mo</td>\n";
                }else{
                    $sqllote = "SELECT tbl_distrib_lote.lote,
                                        tbl_extrato_lancamento.nota_fiscal_mo,
                                        tbl_extrato_lancamento.distrib_lote
                                FROM tbl_extrato_lancamento
                                JOIN tbl_distrib_lote USING(distrib_lote)
                            WHERE tbl_extrato_lancamento.extrato = $extrato";
                    $reslote = pg_query($con,$sqllote);
                    if(pg_num_rows($reslote) > 0){
                        $lote = trim(pg_fetch_result($reslote,0,lote));
                        $nota_fiscal_mo = trim(pg_fetch_result($reslote,0,nota_fiscal_mo));
                        echo "<td align='center' nowrap>$lote - $nota_fiscal_mo</td>\n";
                    }else{
                        echo "<td align='center' nowrap>&nbsp;</td>\n";
                    }
                }
            }

            echo "<td align='left' nowrap>".substr($nome,0,20)."</td>\n";
            echo "<td align='center' nowrap>".$posto_estado."</td>\n";
            if ($login_fabrica == 1) echo "<td>$credenciamento</td><td align='center' nowrap>$tipo_posto</td>\n";
            if($login_fabrica == 20 ){echo "<td align='center'><a href='extrato_os_aprova";
            }else{
                echo "<td align='center' ";
                if($bloqueado == "t" and $login_fabrica == 1){
                    echo " bgcolor='#FF9E5E' ";
                }
                echo "><a href='extrato_consulta_os";
            }
            if ($login_fabrica == 14) echo "_intelbras";
            echo ".php?extrato=$extrato&data_inicial=$data_inicial&data_final=$data_final&cnpj=$xposto_codigo&razao=$posto_nome' target='_blank'>";
            echo ($login_fabrica == 1 OR $login_fabrica == 19 ) ? $protocolo : $extrato;
            echo "</a></td>\n";

            if($login_fabrica == 86 OR $login_fabrica == 104){
                echo "<td align='center'>";
                echo mostraMarcaExtrato($extrato);
                echo    "</td>";
            }

            //IGOR - HD 6924 04/03/2008
            $cor_estoque_menor = "";
            if ($estoque_menor_20 == "t" and $login_fabrica == 1) {
                $cor_estoque_menor = " bgcolor='#CCFF66' ";
            }
            echo "<td align='left' $cor_estoque_menor>$data_geracao</td>\n";

            if($login_fabrica == 129){
                echo "<td align='left' $cor_estoque_menor>$liberado</td>\n";
            }

            echo "<td align='center' nowrap>".$qtde_os_ex."</td>\n";
            // echo "<td align='center' title='Clique aqui para ver a quantidade de OS'><div id='qtde_os_$i'><a href=\"javascript:conta_os($extrato,'qtde_os_$i','".($i+1)."');\" id='conta_os_$i'>VER</a></div><input type='hidden' name='extrato_tudo_$i' id='extrato_tudo_$i' value='$extrato'></td>\n";
            //--== FIM - QTDE de OS no extrato =========================================================

            if ($login_fabrica == 1) {
                $sql =  "SELECT SUM(tbl_os.pecas)       AS total_pecas     ,
                                SUM(tbl_os.mao_de_obra) AS total_maodeobra ,
                                tbl_extrato.avulso      AS total_avulso
                        FROM tbl_os
                        JOIN tbl_os_extra USING (os)
                        JOIN tbl_extrato ON tbl_extrato.extrato = tbl_os_extra.extrato AND tbl_os_extra.i_fabrica = $login_fabrica
                        WHERE tbl_os_extra.extrato = $extrato
                        GROUP BY tbl_extrato.avulso;";
                $resT = pg_query($con,$sql);

                if (pg_num_rows($resT) == 1) {
                    echo "<td align='right' nowrap> " . number_format(pg_fetch_result($resT,0,total_pecas),2,',','.') . "</td>\n";
                    echo "<td align='right' nowrap> " . $mao_de_obra . "</td>\n";
                    echo "<td align='right' nowrap> " . number_format(pg_fetch_result($resT,0,total_avulso),2,',','.') . "</td>\n";
                }else{
                    echo "<td>&nbsp;$pecas</td>\n";
                    echo "<td>&nbsp;$mao_de_obra</td>\n";
                    echo "<td>&nbsp;$avulso</td>\n";
                }
            }

            //hd 39502
            if ($login_fabrica==20) {
                echo "<td align='right' nowrap> $total_cortesia</td>\n";
            }

            //TOTAL EXTRATO
            echo "<td align='right' nowrap> $total</td>\n";

            if ($login_fabrica == 6) {//hd 3471
                echo "<td align='center' nowrap>".number_format($total_acumulado,2,',','.') . "</td>";
            }

            // SONO - 04/09/206 exibir valor_liquido para intelbras //
            if ($login_fabrica == 14 || $login_fabrica == 91 ) {
                echo "<td align='right' nowrap> $valor_liquido</td>\n";
            }

            if ($login_fabrica == 1) echo "<td><a href=\"javascript: AbrirJanelaObs('$extrato');\">OBS.</a></td>\n";

            if ($login_fabrica == 20 || $login_fabrica == 14) {
                echo "<td align='center'><INPUT TYPE='text' NAME='nota_fiscal_mao_de_obra_$i' id='nota_fiscal_mao_de_obra_$i' value='$nota_fiscal_mao_de_obra' size='8' maxlength='16'"; if (strlen($aprovado) > 0 && $login_fabrica != 14) echo " readonly"; echo "></td>";
                if ($login_fabrica == 20) {
                    echo "<td align='center'><INPUT TYPE='text' NAME='nota_fiscal_devolucao_$i' id='nota_fiscal_devolucao_$i' value='$nota_fiscal_devolucao' size='8' maxlength='16'"; if (strlen($aprovado)>0) echo " readonly"; echo "></td>";
                    echo "<td align='center'>$data_coleta</td>"; #HD 219942
                } else {
                    echo "<INPUT TYPE='hidden' NAME='nota_fiscal_devolucao_$i' id='nota_fiscal_devolucao_$i' value='$nota_fiscal_devolucao' size='8' maxlength='16'"; if (strlen($aprovado)>0) echo " readonly"; echo ">";
                }
                if ($login_fabrica == 14) {
                    echo "<td align='center'>$emissao_mao_de_obra</td>"; #HD 209349
                }
                echo "<td align='center'><INPUT size='12' maxlength='10' TYPE='text' NAME='data_entrega_transportadora_$i' class='data_entrega_transportadora' id='data_entrega_transportadora_$i' rel='data2' value='$data_entrega_transportadora'"; if (strlen($aprovado) > 0 && $login_fabrica != 14) echo " disabled"; echo "></td>";
            }

            if ($login_fabrica == 45 or $login_fabrica == 80) echo "<td align='center'>$nf_autorizacao</td>";

            if($login_fabrica == 151){

                if(strlen($nf_autorizacao) == 0){
                    $nf_autorizacao = "<a href='../nota_servico_extrato.php?extrato=$extrato' rel='shadowbox; width= 400; height= 250;'>Informar Nota de Servi�o</a>";
                }else{
                    $nf_autorizacao = "<a href='../nota_servico_extrato.php?extrato=$extrato' rel='shadowbox; width= 400; height= 250;'>$nf_autorizacao</a>";
                }

                echo "<td align='center' class='nf-servico-$extrato' nowrap> $nf_autorizacao </td>\n";

            }

		if ($login_fabrica == 20) {
			echo "<td align='left'>$aprovado</td>";
		}else if($login_fabrica == 134){
			echo "<td align='left'>".$baixa_extrato."</td>";
		}else {
			echo "<td align='left'>$baixado</td>\n";
		}


            //HD 205958: Um extrato pode ser modificado at� o momento que for APROVADO pelo admin. Ap�s aprovado
            //           n�o poder� mais ser modificado em hip�tese alguma. Acertos dever�o ser feitos com lan�amento
            //           de extrato avulso. Verifique as regras definidas neste HD antes de fazer exce��es para as f�bricas
            //           SER� LIBERADO AOS POUCOS, POIS OS PROGRAMAS N�O EST�O PARAMETRIZADOS

            if($login_fabrica == 1){
                $obs = verificaTipoGeracao($extrato);


                $dadosGeracao = json_decode($obs);

                echo "<td align='left' nowrap>";

                 if(isset($dadosGeracao->tipo_de_envio) && strlen($dadosGeracao->tipo_de_envio) > 0){

                     echo "{$dadosGeracao->tipo_de_envio}";
                 }else{

                     $sql = "SELECT tipo_envio_nf
                    FROM tbl_tipo_gera_extrato
                    WHERE fabrica = $login_fabrica
                    AND posto = $posto";
                     $res2 = pg_query($con, $sql);
                     if(pg_num_rows($res2) > 0){
                         echo str_replace("_"," ",pg_fetch_result($res2, 0, 'tipo_envio_nf'));
                     }
                 }
                echo "</td>";

                echo "<td nowrap> <input type='checkbox' value='{$extrato}' id='inibir_extrato_{$extrato}' onClick='inibir_extrato({$extrato})' {$checked_inibido} /> Inibido </td>";

            }

            if($login_fabrica == 45 && strlen($aprovado) > 0 && strlen($baixado) > 0){
                echo "<td colspan='4'>Extrato j� Aprovado</td>";
            }else{

                if (in_array($login_fabrica, $fabricas_acerto_extrato)) {
                    echo "<td align='center' nowrap>";
                    //Extrato n�o aprovado, pode aprovar se j� estiver liberado
                    if (strlen($aprovado) == 0) {
                        if (strlen($liberado) == 0) {
                            if ($recalculo_pendente == 't') {
                                echo "*Aguardando recalculo\n";
                            } else {
                                echo "<a href=\"javascript:window.location = '$PHP_SELF?liberar=$extrato&posto=$posto&data_inicial=$data_inicial&data_final=$data_final&cnpj=$xposto_codigo&razao=$posto_nome&msg_aviso='+document.Selecionar.msg_aviso.value \">Liberar</a>";
    //                          echo " <input type='checkbox' class='frm' name='liberar_$i' id='liberar' value='$extrato'>";
                            }
                        } else {
                            if($login_fabrica == 45 && strlen($baixado) == 0){
                                echo "<a href='$PHP_SELF?aprovar=$extrato&posto=$posto&data_inicial=$data_inicial&data_final=$data_final&cnpj=$xposto_codigo&razao=$posto_nome'><img src='imagens_admin/btn_aprovar_azul.gif' id='img_aprovar_$i' ALT='Aprovar o extrato'></a>";
                            }else{
                                echo "<a href='$PHP_SELF?aprovar=$extrato&posto=$posto&data_inicial=$data_inicial&data_final=$data_final&cnpj=$xposto_codigo&razao=$posto_nome'><img src='imagens_admin/btn_aprovar_azul.gif' id='img_aprovar_$i' ALT='Aprovar o extrato'></a>";
                            }
                        }
                    } else {//Extrato j� aprovado, n�o pode mais modificar
                        if($login_fabrica == 45 && strlen($baixado) == 0){
                            if($recalculo_pendente == 't'){
                                echo "*Aguardando recalculo\n";
                            }else{
                                echo "<a href=\"javascript:window.location = '$PHP_SELF?liberar=$extrato&msg_aviso='+document.Selecionar.msg_aviso.value \">Liberar</a>";
                                echo " <input type='checkbox' class='frm' name='liberar_$i' id='liberar' value='$extrato'>";

                            }
                        }
                    }
                    echo "</td>\n";
                } elseif (in_array($login_fabrica,array(6,7,14,15,11,24,25,35,40,42,50,43,51,46,47,74,59,30,45,115,116,117)) or ($login_fabrica > 51) ) {//HD 205958: Rotina antiga
                    echo "<td align='center' nowrap>";
                    if (strlen($liberado) == 0) {
                        if($recalculo_pendente == 't'){
                            echo "*Aguardando recalculo\n";
                        }else{
                            echo "<a href=\"javascript:window.location = '$PHP_SELF?liberar=$extrato&msg_aviso='+document.Selecionar.msg_aviso.value \">Liberar</a>";
                            echo " <input type='checkbox' class='frm' name='liberar_$i' id='liberar' value='$extrato'>";

                        }
                    }
                    echo "</td>\n";
                }

                if ($login_fabrica == 11 OR $login_fabrica == 25) {
                    echo "<td align='center' nowrap>";
                    if (strlen($email) == 0) {?>
                        <center>
                        <input type='button' value='Imprimir' onclick="javascript: window.open('extrato_consulta_os_print.php?extrato=<? echo $extrato; ?>','printextrato','toolbar=no,location=no,directories=no,status=no,scrollbars=yes,menubar=yes,resizable=yes,width=700,height=480')" ALT='Imprimir' border='0' style='cursor:pointer;' /><?php
                    } else {
                        echo "&nbsp;";
                    }
                    echo "</td>\n";
                }

                if ($login_fabrica == 24) {
                    echo "<td align='center' nowrap>";
                    if (strlen($email) == 0) {?>
                        <center>
                        <input type='button' value='Imprimir' onclick="javascript: window.open('extrato_consulta_os_print.php?extrato=<? echo $extrato; ?>','printextrato','toolbar=no,location=no,directories=no,status=no,scrollbars=yes,menubar=yes,resizable=yes,width=700,height=480')" ALT='Imprimir' border='0' style='cursor:pointer;' /><?php
                    } else {
                        echo "&nbsp;";
                    }
                    echo "</td>\n";
                }

                if ($login_fabrica == 20){ echo "<td nowrap>$auditor</td>"; }

                if (in_array($login_fabrica,array(1,2,8,20,30,40,47,14,42))) {
                    if ($msg_os_deletadas == "") {
                        echo "<td align='center' nowrap>";
                        if (strlen($aprovado) == 0 || $login_fabrica == 14) {
                            if ($login_fabrica == 20 || $login_fabrica == 14) {
                                echo "<a href=\"javascript:if(confirm('Deseja aprovar todas as OS�s deste extrato? '))window.location='$PHP_SELF?aprovar=$extrato&posto=$posto&data_inicial=$data_inicial&data_final=$data_final&cnpj=$xposto_codigo&razao=$posto_nome&nf_mao_de_obra='+document.getElementById('nota_fiscal_mao_de_obra_$i').value+'&nf_devolucao='+document.getElementById('nota_fiscal_devolucao_$i').value+'&data_entrega_transportadora='+document.getElementById('data_entrega_transportadora_$i').value\">";
                                echo "<img class='extrato_aprova_$extrato' src='imagens_admin/btn_aprovar_azul.gif' ALT='Aprovar o extrato'></a>";
                                echo "<label for='extrato_aprovado_$extrato' style='display:none;'>";
                            } else {
                                if ($login_fabrica == 1) {
                                    echo "<a href=\"javascript:aprovaExtrato($extrato,$posto,'img_aprovar_$i','img_novo_$i','img_adicionar_$i','acumular_$i','resposta_$i');\"><img src='imagens_admin/btn_aprovar_azul.gif' id='img_aprovar_$i' ALT='Aprovar o extrato'></a><span id='resposta_$i'></span>";
                                } else {
                                    echo "<a href='$PHP_SELF?aprovar=$extrato&posto=$posto&data_inicial=$data_inicial&data_final=$data_final&cnpj=$xposto_codigo&razao=$posto_nome'><img src='imagens_admin/btn_aprovar_azul.gif' id='img_aprovar_$i' ALT='Aprovar o extrato'></a>";
                                }
                            }
                            if ($login_fabrica <> 20 and $login_fabrica <> 47) {
                                echo "<input type='checkbox' name='acumular_$i' id='acumular' value='$extrato' class='frm'>\n";
                            }
                        }
                        echo "</td>\n";
                    }
                }

                // se o msg_os_deletadas for nulo o extrato n�o foi cancelado. Se n�o for nulo, o Extrato foi cancelado
                if ($msg_os_deletadas == "") {

                    echo "<td style='text-align: center;'>";

                    if($login_fabrica == 45 && strlen($baixado) == 0){
                        echo "<a href='extrato_avulso.php'><img src='imagens/btn_novo_azul.gif' id='img_novo_$i' ALT='Cadastrar um Novo Extrato'></a>";
                    }

                    elseif (strlen($aprovado) == 0 OR $login_fabrica == 30)
                        echo "<a href='extrato_avulso.php'><img class='extrato_novo_$extrato' src='imagens/btn_novo_azul.gif' id='img_novo_$i' ALT='Cadastrar um Novo Extrato'></a>";
                    echo "</td>\n";

                    echo "<td style='text-align: center;'>";

                    if($login_fabrica == 45 && strlen($baixado) == 0){
                        echo "<a href='extrato_avulso.php?extrato=$extrato&posto=$posto'><img src='imagens/btn_adicionar_azul.gif' id='img_adicionar_$i' ALT = 'Lan�ar itens no extrato'></a>";
                    }

                    elseif (strlen($aprovado) == 0 OR $login_fabrica == 8 or $login_fabrica == 104 or $login_fabrica == 105)
                        echo "<a href='extrato_avulso.php?extrato=$extrato&posto=$posto'><img class='extrato_adicionar_$extrato' src='imagens/btn_adicionar_azul.gif' id='img_adicionar_$i' ALT = 'Lan�ar itens no extrato'></a>";
                    echo "</td>\n";
                    if ($login_fabrica == 45 || ( $login_fabrica == 24  && $xtotal <= 50  ) ) {
                        echo "<td nowrap>";
                        if ($login_fabrica == 24 && strlen($aprovado)==0 ) {

                            echo '<a href="#" class="acumula_extrato">Acumular</a>';

                        }
                        echo (strlen($aprovado)==0) ? "<input type='checkbox' name='acumular_$i' id='acumular' value='$extrato' class='frm'>\n" : "&nbsp; ";
                        echo "</td>";
                    }
                    else if ($login_fabrica == 24) {
                        echo '<td>&nbsp;</td>';
                    }
                } else { //s� entra aqui se o extrato foi excluido e a fabrica eh 2-  DYNACON
                    echo "<td colspan='3' align='center'>";
                    echo "<b style='font-size:10px;color:red'>Extrato cancelado!!</b>";
                    echo "</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo         "<td></td>";
                    echo        "<td colspan=9 align='left'> <b style='font-size:12px;font-weight:normal'>$msg_os_deletadas</b> </td>";
                    echo    "</td>";
                }

                if ($login_fabrica == 50 or $login_fabrica == 15) {
                    echo "<td></td><td align='center'>$previsao_pagamento</td>";
                    echo "<td align='center'>$data_recebimento_nf</td>";
                }

                // HD12104
                if ($login_fabrica == 14)   {
                    //echo "<td align='center' nowrap>&nbsp;</td>";
                    echo "<td align='center' nowrap>";
                    echo " <input type='checkbox' class='frm' name='imprime_os_$i' value='$extrato'";
                    if($imprime_os == 't') echo " checked ";
                    echo " >";
                    echo "</td>\n";
                }

                if ($login_fabrica == 35) {
                    echo "<td></td><td align='center'><a href='os_extrato_pecas_retornaveis_cadence.php?extrato=$extrato' target='_blank'><img src='imagens/btn_pecasretornaveis_azul.gif'></a></td>";
                }

            }
            if(in_array($login_fabrica,array(85))):

            ?>
                <td style="text-align: center;"><input class="print" extrato="<?php echo $extrato ?>"  type="checkbox" name="extrato[]" value="<?php echo $extrato ?>" /></td>
            <?php
            endif;

            if($login_fabrica == 30){
                $sqlEncontro = "SELECT  to_char(posto_data_transacao,'DD/MM/YYYY') AS dt_pagamento,
                                        nf_numero_nf,
                                        nf_valor_do_encontro_contas,
                                        encontro_serie,
                                        encontro_titulo_a_pagar,
                                        encontro_parcela,
                                        encontro_valor_liquido,
                                        posto_valor_do_encontro_contas
                                    FROM tbl_encontro_contas
                                    WHERE fabrica = $login_fabrica
                                    AND extrato = $extrato
                                    LIMIT 1";
                $resEncontro = pg_query($con,$sqlEncontro);

                if(pg_num_rows($resEncontro) > 0){
                    $num_oc         = pg_fetch_result($resEncontro, 0, 'encontro_serie');
                    $dt_pagamento   = pg_fetch_result($resEncontro, 0, 'dt_pagamento');
                    $valor_pago     = pg_fetch_result($resEncontro, 0, 'nf_valor_do_encontro_contas');
                    $num_nf         = pg_fetch_result($resEncontro, 0, 'nf_numero_nf');
                    $desconto       = pg_fetch_result($resEncontro, 0, 'posto_valor_do_encontro_contas');
                    $button = "<input type='button' rel='$extrato' value='Encontro Contas' id='encontro_contas_$extrato'>";
                }else{
                    $num_oc = "";
                    $dt_pagamento = "";
                    $valor_pago = "";
                    $num_nf = "";
                    $desconto = "";
                    $button = "&nbsp;";
                }
                ?>
                    <td><?=$num_oc?></td>
                    <td><?=$dt_pagamento?></td>
                    <td><?=number_format($valor_pago,2,',','.')?></td>
                    <td><?=$num_nf?></td>
                    <td><?=number_format($desconto,2,',','.')?></td>
                    <td><?=$button?></td>
                <?php
            }

            echo "</tr>\n";
            flush();
        }

        if ($login_fabrica == 50) { //HD 49532 11/11/2008
            $xsoma_total = number_format($soma_total,2, ",", ".");
            echo "<tr bgcolor='$cor'>\n";
                echo "<td colspan='6' align='right'><B>TOTAL</B></td>\n";
                echo "<td>$xsoma_total</td>\n";
                echo "<td colspan='7' align='right'>&nbsp;</td>\n";
            echo "</tr>\n";
        }
        echo "</tbody>";
        echo "<tfoot>";
        echo "<tr>\n";
        if ($login_fabrica == 11) {
            echo "<td colspan='7'>
                Quando um extrato � liberado, automaticamente � enviado um email para o posto. Se quiser acrescentar uma mensagem digite no campo abaixo.
                <br>
                <INPUT size='60' TYPE='text' NAME='msg_aviso' value=''>
            </td>\n";
        } elseif ($login_fabrica == 24) {
            echo "<td colspan='5'><span id='total_extratos' style='font-size:14px'></span></td>\n";
            echo "<td colspan='2'></td>\n";
        } elseif($login_fabrica == 20){
            echo "<td><input type='button' value='Calcular Extratos' onclick='calcularExtrato(); return false;' /></td>\n";

            echo "<td align='left'>";
                echo "<button type='button' id='aprovar_todos_extratos' onClick='aprovarTodos();'>Aprovar Todos</button>";
            echo "</td>\n";
            echo "<td colspan='17'></td>";

        } else {
            if ($login_fabrica == 14){
                echo "</tr></table><td colspan='7'>&nbsp;<INPUT size='60' TYPE='hidden' NAME='msg_aviso' value=''></td>\n";
            }else if($login_fabrica == 91){
                   echo "<td colspan='6'> <INPUT size='60' TYPE='hidden' NAME='msg_aviso' value=''> </td>\n";

            }else{
                $colspan = (in_array($login_fabrica, array(50,142,145)) || isset($novaTelaOs)) ? 8 : 7;
                echo "<td colspan='{$colspan}'>&nbsp;<INPUT size='60' TYPE='hidden' NAME='msg_aviso' value=''></td>\n";
            }
        }
        if ($login_fabrica == 91){
            echo "<td>".number_format ($soma_total,2,',','.')."</td>";
        }
        if ($login_fabrica == 91){

            echo "<td>".number_format ($total_valor_liquido,2,',','.')."</td>";
        }

        if($login_fabrica == 153){
            echo "<td></td>";   
        }
        if ($login_fabrica == 85 or $login_fabrica == 91) echo '<td>&nbsp;</td>';
        if (in_array($login_fabrica, array(6,7,11,15,24,25,30,35,40,42,50,51,46,47,59,74,52)) or ($login_fabrica > 81)) {
            echo "<td align='center'>";
            echo "<a href='javascript: document.Selecionar.btnacao.value=\"liberar_tudo\" ; document.Selecionar.submit() '>Liberar Selecionados</a>";
            echo "<input type='hidden' name='total_postos' value='$i'>";
            echo "</td>\n";
        }
        if($login_fabrica == 153){
            echo "<td></td>";
        }


        if ($login_fabrica == 14) {
            echo "<table class='formulario'><tr><td align='center'>";
            echo "<a href='javascript: document.Selecionar.btnacao.value=\"liberar_tudo\" ; document.Selecionar.submit() '>Liberar Selecionados/a>";
            echo "<input type='hidden' name='total_postos' value='$i'>";
            echo "</td>\n";
        }

        if ($login_fabrica == 1 or $login_fabrica == 45 or $login_fabrica == 24 ) { //HD 66773
            $colspan = ($login_fabrica == 45 || $login_fabrica == 24) ? 4 : 5;
            echo "<td colspan='$colspan'>&nbsp;</td>\n";
            echo "<td align='center'>";
            if ($login_fabrica != 24 )
                $submit_form = "document.Selecionar.submit()";
            echo "<a href='javascript: document.Selecionar.btnacao.value=\"acumular_tudo\" ; $submit_form  ' id=\"acumula_extratos\">Acumular selecionados</a>";
            echo "<input type='hidden' name='total_postos' value='$i'>";
            echo "</td>\n";
        }
        if($login_fabrica <> 20){
            echo "<td colspan='2'>&nbsp;</td>\n";
        }
        if(in_array($login_fabrica,array(85))):
        ?>
            <script type="text/javascript">
                function imprimirExtratos(){
                    var form = $('form[hidden].print');
                    form.html('');
                    form.append($('input[type=checkbox][extrato].print').clone());
                    form.submit();
                }
            </script>
            <td>
                <a href="#_blank" onclick="imprimirExtratos()" >
                    Imprimir Selecionados
                </a>
            </td>
        <?php
        endif;
        echo "</tr>\n";
        echo "</tfoot>";
        echo "</table>\n";
        echo "</form>\n";

        echo "<p>Extratos: $qtde_extratos</p>";

        if($login_fabrica == 20){
            echo "<p id='qtdeOS'></p>";
        }

        if(in_array($login_fabrica, array(50,91,138))){?>

            <button type="button" id='download_excel' value="t">Gerar Excel</button>

    <? }else if(in_array($login_fabrica, array(86,20))){ ?>

	    <button type="button" id='download_excel' onclick="window.open('<?=$relatorio_excel?>','_blank');">Gerar Excel</button>
<?      }
    }

    if (strlen($msg_os_deletadas ) >0 and $login_fabrica == 2) {
        echo "<br><div name='os_excluidas' style='border:1px solid #00ffff'><h4>OS excluidas</h4>$msg_os_deletadas;</div>";
    }

    if ($login_fabrica == 3) {

        if (strlen($extrato) > 0) {
            $cond_extrato = " AND tbl_extrato.extrato = $extrato ";
        }

        echo "<br /><br />";

        $sql = "SELECT  tbl_posto.posto               ,
                        tbl_posto.nome                ,
                        tbl_posto.cnpj                ,
                        tbl_posto_fabrica.codigo_posto,
                        tbl_posto_fabrica.distribuidor,
                        tbl_extrato.extrato           ,
                        to_char (tbl_extrato.data_geracao,'dd/mm/yyyy') as data_geracao,
                        tbl_extrato.total,
                        (SELECT count (tbl_os.os) FROM tbl_os JOIN tbl_os_extra USING (os) WHERE tbl_os_extra.extrato = tbl_extrato.extrato) AS qtde_os,
                        to_char (tbl_extrato_pagamento.data_pagamento,'dd/mm/yyyy') as baixado
                FROM    tbl_extrato
                JOIN    tbl_posto USING (posto)
                JOIN    tbl_posto_fabrica ON tbl_extrato.posto = tbl_posto_fabrica.posto AND tbl_posto_fabrica.fabrica = $login_fabrica
                left JOIN    tbl_extrato_pagamento ON tbl_extrato.extrato = tbl_extrato_pagamento.extrato
                WHERE   tbl_extrato.fabrica = $login_fabrica
                AND     tbl_posto_fabrica.distribuidor NOTNULL
                $cond_extrato";

        if (strlen ($data_inicial) < 8) $data_inicial = date ("d/m/Y");
            $x_data_inicial = substr ($data_inicial,6,4) . "-" . substr ($data_inicial,3,2) . "-" . substr ($data_inicial,0,2);

        if (strlen ($data_final) < 10) $data_final = date ("d/m/Y");
            $x_data_final = substr ($data_final,6,4) . "-" . substr ($data_final,3,2) . "-" . substr ($data_final,0,2);

        if (strlen ($x_data_inicial) > 0 AND strlen ($x_data_final) > 0)
        $sql .= " AND      tbl_extrato.data_geracao BETWEEN '$x_data_inicial 00:00:00' AND '$x_data_final 23:59:59'";

        $xposto_codigo = str_replace (" " , "" , $posto_codigo);
        $xposto_codigo = str_replace ("-" , "" , $xposto_codigo);
        $xposto_codigo = str_replace ("/" , "" , $xposto_codigo);
        $xposto_codigo = str_replace ("." , "" , $xposto_codigo);

        if (strlen ($posto_codigo) > 0 ) $sql .= " AND tbl_posto.cnpj = '$xposto_codigo' ";
        if (strlen ($posto_nome) > 0 )   $sql .= " AND tbl_posto.nome ILIKE '%$posto_nome%' ";

        $sql .= " GROUP BY tbl_posto.posto ,
                        tbl_posto.nome ,
                        tbl_posto.cnpj ,
                        tbl_posto_fabrica.codigo_posto,
                        tbl_posto_fabrica.distribuidor,
                        tbl_extrato.extrato ,
                        tbl_extrato.liberado ,
                        tbl_extrato.total,
                        tbl_extrato.data_geracao,
                        tbl_extrato_pagamento.data_pagamento
                    ORDER BY tbl_posto.nome, tbl_extrato.data_geracao";
        $res = pg_query ($con,$sql);

        if (pg_num_rows ($res) == 0) {
            echo "<center><font style='font:bold 12px Arial; color:#000;'>'N�o Foram Encontrados Resultados para esta Pesquisa</font></center>";
        }

        if (pg_num_rows ($res) > 0) {
            for ($i = 0 ; $i < pg_num_rows ($res) ; $i++) {

                $posto   = trim(pg_fetch_result($res,$i,posto));
                $codigo_posto   = trim(pg_fetch_result($res,$i,codigo_posto));
                $nome           = trim(pg_fetch_result($res,$i,nome));
                $extrato        = trim(pg_fetch_result($res,$i,extrato));
                $data_geracao   = trim(pg_fetch_result($res,$i,data_geracao));
                $qtde_os        = trim(pg_fetch_result($res,$i,qtde_os));
                $total          = trim(pg_fetch_result($res,$i,total));
                $baixado        = trim(pg_fetch_result($res,$i,baixado));
                $extrato        = trim(pg_fetch_result($res,$i,extrato));
                $distribuidor   = trim(pg_fetch_result($res,$i,distribuidor));
                $total          = number_format ($total,2,',','.');

                if (strlen($distribuidor) > 0) {
                    $sql = "SELECT  tbl_posto.nome                ,
                                    tbl_posto_fabrica.codigo_posto
                            FROM    tbl_posto_fabrica
                            JOIN    tbl_posto ON tbl_posto.posto = tbl_posto_fabrica.posto
                            WHERE   tbl_posto_fabrica.posto   = $distribuidor
                            AND     tbl_posto_fabrica.fabrica = $login_fabrica;";
                    $resx = pg_query ($con,$sql);

                    if (pg_num_rows($resx) > 0) {
                        $distribuidor_codigo = trim(pg_fetch_result($resx,0,codigo_posto));
                        $distribuidor_nome   = trim(pg_fetch_result($resx,0,nome));
                    }
                }

                if ($i == 0) {
                    echo "<table width='700px' class='tabela' align='center' border='1' cellspacing='2'>";
                    echo "<tr class='titulo_coluna'>";
                    echo "<td align='center'>C�digo</td>";
                    echo "<td align='center' nowrap>Nome do Posto</td>";
                    echo "<td align='center'>Extrato</td>";
                    echo "<td align='center'>Data</td>";
                    echo "<td align='center' nowrap>Qtde. OS</td>";
                    echo "<td align='center'>Total</td>";
                    echo "<td align='center' colspan='2'>Extrato Vinculado a um Distribuidor</td>";
                    echo "</tr>";
                }

                echo "<tr>";

                echo "<td align='left'>";
                echo "$codigo_posto</td>";

                echo "<td align='left' nowrap>$nome</td>";
                echo "<td align='center'>$extrato</td>";

                echo "<td align='left'>$data_geracao</td>";
                echo "<td align='center'>$qtde_os</td>";
                echo "<td align='right' nowrap>R$ $total</td>";
                echo "<td align='left' nowrap>$distribuidor_codigo - $distribuidor_nome</td>";
                echo "</tr>";
            }
            echo "</table>";


        }
    }
}

}
?>

<?php
    if(in_array($login_fabrica,array(85))):
?>
    <form target="_blank" class="print" action="extrato_consulta_os_print.php" method="GET" hidden="hidden" style="display:none">
    </form>
<?php
    endif;
?>

<br>
<br>
<br>

<? include "rodape.php"; ?>
