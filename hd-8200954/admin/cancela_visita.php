<?php
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
$admin_privilegios="cadastros";
include 'autentica_admin.php';
include 'funcoes.php';

if ($_REQUEST["visita"]) {
    $roteiro_posto = $_REQUEST["visita"];
    if (verificaVisita($roteiro_posto)) {
        $erro = false;
        $retorno  = getResultado($roteiro_posto);
        $tecnico  = $retorno["tecnico"];
        $roteiro  = $retorno["roteiro"];
    } else {
        $erro = true;
    }
} else {
    $erro = true;
}

if ($_POST) {
    $msg_erro = array();
    $msg_sucesso = array();

    $descricao = $_POST["descricao"];
 

    if (strlen($descricao) == 0) {
        $msg_erro["campos"][] = "descricao";
        $msg_erro["msg"][] = "Campo Motivo � obrigat�rio";
    }

    $dados = array();
    if (count($msg_erro["msg"]) == 0 ) {

        $retorno  = getResultado($roteiro_posto);

        $dados["descricao"]             = $descricao;
        $dados["roteiro_posto"]         = $roteiro_posto;
        $dados["roteiro"]               = $roteiro;

       $retorno = cancelaAgenda($dados);
        
        if (!$retorno["erro"]) {
            $msg_sucesso["msg"][] = $retorno["msg"];
            $checkin   = "";
            $checkout  = "";
            $descricao = "";
            echo "<meta http-equiv=refresh content=\"3;URL=listagem_roteiros.php\">";
        } else {
            $msg_erro["msg"][] = $retorno["msg"];
        }
    }
}

function getLegendaTipoContato($sigla) {
    $arr =  array("CL" => "Cliente","RV" => "Revenda","PA" => "Posto Autorizado");
    return $arr[$sigla];
}

function getLegendaTipoVisita($sigla) {
    $legenda = array("VT" => "Visita T�cnica","VC" => "Visita Comercial","VA" => "Visita Administrativa","CM" => "Cl�nica Makita","FE" => "Feira/Evento","TN" => "Treinamento");
    return $legenda[$sigla];
}


function cancelaAgenda($dados = array()) {
    global $login_fabrica, $con;

    if (empty($dados)) {
        return array("erro" => true, "msg" => "Dados da visita, n�o enviado");
    }

    $sqlUp = "UPDATE tbl_roteiro_posto 
                     SET status = 'CC', 
                         motivo_reagendamento='".$dados['descricao']."', data_update='".date('Y-m-d H:i:s')."' 
                   WHERE roteiro_posto=".$dados['roteiro_posto'];
    $resUp = pg_query($con, $sqlUp);
    if (pg_last_error($resUp)) {
        return array("erro" => true, "msg" => "Erro ao cancelar a visita");
    }
    return array("erro" => false, "msg" => "Visita cancelada com sucesso");
    
}

function getResultado($roteiro_posto) {
    global $login_fabrica, $con;

    $sql = "SELECT 
                   tbl_roteiro_tecnico.tecnico,
                   tbl_roteiro.data_termino,
                   tbl_roteiro.roteiro,
                   tbl_roteiro.status_roteiro,
                   tbl_roteiro_posto.codigo,
                   tbl_roteiro_posto.data_visita,
                   tbl_roteiro_posto.status,
                   tbl_roteiro_posto.roteiro_posto,
                   tbl_roteiro_posto.tipo_de_visita,
                   tbl_roteiro_posto.tipo_de_local,
                   CASE WHEN tbl_roteiro_posto.tipo_de_local = 'PA' THEN
                       tbl_posto.nome
                   WHEN tbl_roteiro_posto.tipo_de_local = 'CL' THEN
                       tbl_cliente.nome
                   WHEN tbl_roteiro_posto.tipo_de_local = 'RV' THEN       
                       tbl_revenda.nome
                   END  AS nome_contato,
                   tbl_roteiro.admin
                 FROM tbl_roteiro
                 JOIN tbl_roteiro_posto ON tbl_roteiro.roteiro = tbl_roteiro_posto.roteiro
                 JOIN tbl_roteiro_tecnico ON tbl_roteiro.roteiro = tbl_roteiro_tecnico.roteiro
             LEFT JOIN tbl_cliente ON tbl_cliente.cpf = tbl_roteiro_posto.codigo 
             LEFT JOIN tbl_revenda ON tbl_revenda.cnpj = tbl_roteiro_posto.codigo 
             LEFT JOIN tbl_posto ON tbl_posto.cnpj = tbl_roteiro_posto.codigo 
             LEFT JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_posto.posto  AND tbl_posto_fabrica.fabrica = {$login_fabrica}

                WHERE tbl_roteiro.fabrica = {$login_fabrica} 
                  AND tbl_roteiro_posto.roteiro_posto = {$roteiro_posto}
                ";
    $res = pg_query($con, $sql);
    
    if (pg_last_error()) {
        return array();
    }

    return pg_fetch_array($res);
    
}
function geraDataNormal($data) {
    $vetor = explode('-', $data);
    $dataTratada = $vetor[2] . '/' . $vetor[1] . '/' . $vetor[0];
    return $dataTratada;
}

function verificaVisita($roteiro_posto) {
    global $login_fabrica, $con;

    $sql = "SELECT tbl_roteiro_posto.roteiro_posto
                 FROM tbl_roteiro
                 JOIN tbl_roteiro_posto ON tbl_roteiro.roteiro = tbl_roteiro_posto.roteiro
                 JOIN tbl_roteiro_visita ON tbl_roteiro_visita.roteiro_posto = tbl_roteiro_posto.roteiro_posto
                WHERE tbl_roteiro.fabrica = {$login_fabrica} 
                  AND tbl_roteiro_posto.roteiro_posto = {$roteiro_posto}
                ";
    $res = pg_query($con, $sql);
    if (pg_num_rows($res) > 0) {
        return false;
    }
    return true;
}

function getTecnicos($tecnico = null) {
    global $con,$login_fabrica;
    $cond = "";
    if (strlen($tecnico) > 0) {
        $cond = " AND tbl_tecnico.tecnico = {$tecnico}";
    }
    $sql = "SELECT  tecnico, nome
              FROM tbl_tecnico
             WHERE tbl_tecnico.ativo IS TRUE
               AND tipo_tecnico = 'TF'
               AND tbl_tecnico.fabrica = {$login_fabrica} {$cond} ORDER BY nome ASC";

    $res = pg_query($con, $sql);
    if (strlen($tecnico) > 0) {
        return pg_fetch_object($res);
    }
    return pg_fetch_all($res);
}



$layout_menu = "tecnica";
$title = "Cancelar Visita";
include 'cabecalho_new.php';

$plugins = array(
    "datepicker",
    "mask",
    "shadowbox",
);

include("plugin_loader.php");
?>
<style>
    .icon-edit {
        background-position: -95px -75px;
    }
    .icon-remove {
        background-position: -312px -3px;
    }
    .icon-search {
        background-position: -48px -1px;
    }
</style>
<script language="javascript">
    var hora = new Date();
    var engana = hora.getTime();

    $(function() {

        Shadowbox.init();
        $("#data_atual_visita").datepicker({dateFormat: "dd/mm/yy" }).mask("99/99/9999");

        $("#btn_acao").click(function() {
            $("form").submit();
        });

    });
</script>
<?php if ($erro == true) {?>
    <div class="alert alert-error">
        <h4>Nenhuma visita encontrada</h4>
    </div>
<?php exit;}?>
<?php if (count($msg_erro["msg"]) > 0) {?>
    <div class="alert alert-error">
        <h4><?=implode("<br />", $msg_erro["msg"])?></h4>
    </div>
<?php }?>
<?php if (count($msg_erro["msg"]) == 0 && count($msg_sucesso["msg"]) > 0) {?>
    <div class="alert alert-success">
        <h4><?=implode("<br />", $msg_sucesso["msg"])?></h4>
    </div>
<?php }?>
    <div class="row">
        <b class="obrigatorio pull-right">  * Campos obrigat�rios </b>
    </div>
    <form name='frm_relatorio' METHOD='POST' ACTION='cancela_visita.php?visita=<?php echo $roteiro_posto;?>' align='center' class='form-search form-inline tc_formulario' >
        <input type="hidden" name="pesquisa" value="true">
        <input type="hidden" name="data_termino"  id="data_termino" value="<?php echo $retorno["data_termino"];?>">
        <input type="hidden" name="aceita"  id="aceita" value="false">
        <div class='titulo_tabela '>Cancelamento de Visita</div>
        <br/>

        <div class="row-fluid">
            <div class="span2"></div>
            <div class="span5">
                <div class='control-group <?=(in_array("tecnico", $msg_erro["campos"])) ? "error" : ""?>'>
                    <label class='control-label' for='tecnico'>Respons�vel pela Visita</label>
                    <div class="controls controls-row">
                        <div class="span12">
                            <select name="tecnico" disabled id="tecnico" class="span12">
                                <option value="">Selecione ...</option>
                                <?php foreach (getTecnicos() as $key => $rows) {?>
                                    <option <?php echo ($tecnico == $rows["tecnico"]) ? "selected" : "";?> value="<?php echo $rows["tecnico"];?>"><?php echo $rows["nome"];?> </option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="span3">
                <div class='control-group <?=(in_array("tipo_visita", $msg_erro["campos"])) ? "error" : ""?>'>
                    <label class='control-label' for='tipo_visita'>Tipo Visita</label>
                    <div class="controls controls-row">
                        <div class="span12">
                            <select name="tipo_visita" disabled id="tipo_visita" class="span12">
                               <option selected value=""><?php echo getLegendaTipoVisita($retorno["tipo_de_visita"]);?> </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class='span2'></div>
        </div>
        <div class='row-fluid'>
            <div class='span2'></div>
             <div class="span3">
                <div class='control-group '>
                    <label class='control-label' for='tipo_visita'>Tipo Contato</label>
                    <div class="controls controls-row">
                        <div class="span12">
                            <select name="tipo_de_local" disabled id="tipo_de_local" class="span12">
                               <option selected value=""><?php echo getLegendaTipoContato($retorno["tipo_de_local"]);?> </option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
             <div class="span5">
                <div class='control-group'>
                    <label class='control-label' for='tipo_visita'>Nome / Raz�o Social</label>
                    <div class="controls controls-row">
                        <div class="span12">
                            <input type="text" value="<?php echo $retorno["nome_contato"];?> " name="tipo_visita" disabled id="tipo_visita" class="span12">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='row-fluid'>
            <div class='span2'></div>
            <div class='span4'>
                <div class='input-append control-group <?=(in_array("data_atual_visita", $msg_erro["campos"])) ? "error" : ""?>''>
                    <label class='control-label' for='data_atual_visita'>Data atual da Visita</label><br />
                    <input size="12" type="text" disabled name="data_atual_visita" id="data_atual_visita" value="<?=geraDataNormal($retorno["data_visita"]);?>" class="span12" >
                    <span class="add-on">
                        <i class="icon-calendar"></i>
                    </span>
                </div>
            </div>
            <div class='span2'></div>
        </div>
        <div class="row-fluid">
            <div class="span2"></div>
            <div class="span8">
                <div class='control-group <?=(in_array("descricao", $msg_erro["campos"])) ? "error" : ""?>'>
                    <label class='control-label' for='tecnico'>Motivo do Cancelamento</label>
                    <h5 class='asteristico'>*</h5>
                    <div class="controls controls-row">
                        <div class="span12">
                            <textarea name="descricao" id="descricao" class="span12"  rows="10"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class='span2'></div>
        </div><br />
        
        <p><br/>
            <button class='btn btn-success' id="btn_acao" type="button">Gravar</button>
            <input type='hidden' id="btn_click" name='btn_acao' value='' />
        </p><br/>
    </form> <br />
  </div>
</div> 
<?php include 'rodape.php';?>
