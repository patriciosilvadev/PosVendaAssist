<?php
include "dbconfig.php";
include "includes/dbconnect-inc.php";
$admin_privilegios = "auditoria";
include "autentica_admin.php";
include __DIR__.'/funcoes.php';

$numero_nf = "";
$status_nf = "";
$condicao = "";
$faturamento = $_GET['faturamento'];
$nota_fiscal = $_GET['nf'];
$serie       = $_GET['serie'];

if(filter_input(INPUT_POST, "btn_acao")){
    $btn = $_POST['btn_acao'];
}

$sql = "SELECT tbl_faturamento_item.faturamento_item,
            tbl_peca.referencia,
            tbl_peca.descricao,
            tbl_faturamento_item.qtde,
            tbl_faturamento_item.qtde_quebrada,
            tbl_faturamento_item.obs_conferencia
        FROM tbl_faturamento_item
        INNER JOIN tbl_peca ON tbl_peca.peca = tbl_faturamento_item.peca 
            AND tbl_peca.fabrica = {$login_fabrica}
        WHERE tbl_faturamento_item.faturamento = {$faturamento}";
$res = pg_query($con,$sql);

?>
<style type="text/css">
.div_conferencia {
    overflow-y: scroll;
    height: 385px;
}
.table td{
    text-align: center;
}
.table {
    width: 850px;
    margin: 0 auto;
}
td.qtde_peca{
    width: 100px;
}
</style>
<link href="bootstrap/css/bootstrap.css" type="text/css" rel="stylesheet" />
<link href="bootstrap/css/extra.css" type="text/css" rel="stylesheet" />
<link href="css/tc_css.css" type="text/css" rel="stylesheet" />
<link href="plugins/posvenda_jquery_ui/css/posvenda_ui/jquery-ui-1.10.3.custom.css" type="text/css" rel="stylesheet" >
<link href="bootstrap/css/ajuste.css" type="text/css" rel="stylesheet" />

<script src="plugins/posvenda_jquery_ui/js/jquery-1.9.1.js"></script>
<script src="plugins/posvenda_jquery_ui/js/jquery-ui-1.10.3.custom.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>
<form id="fm_conferencia" action="<?=$PHP_SELF?>" method="POST" class="form-search form-inline" >
    <div id="mensagem_erro"></div>
    <div class="div_conferencia" style="margin: 5px; padding-right: 20px;">
        <div id="mensagem_conferencia">
            <div class='container tc_container'>
                <div class="row-fluid">
                    <br/>
                    <div class="span3" >
                        <div class="control-group" >
                            <label class="control-label" for="nf_shadow" >N� Nota Fiscal</label>

                            <div class="controls controls-row" >
                                <input type="text" id="nf_shadow" readOnly class="span6" value="<?=$nota_fiscal?>"/>
                            </div>
                        </div>
                    </div>
                    
                    <div class="span4" >
                        <div class="control-group" >
                            <label class="control-label" for="serie_shadow" >S�rie</label>

                            <div class="controls controls-row" >
                                <input type="text" name="serie_shadow" id="serie_shadow" readOnly class="span6" value="<?=$serie?>" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br/>
            <table id="nota_peca" class='table table-striped table-bordered table-large' >
                <thead>
                    <tr class='titulo_coluna'>
                        <th>Refer�ncia</th>
                        <th>Descri��o</th>
                        <th>Quantidade Faturada</th>
                        <th>Quantidade Recebida</th>
                        <th>Observa��o</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if(pg_num_rows($res) > 0){
                    $count = pg_num_rows($res);

                    for($i = 0; $i < $count; $i++){
                        $faturamento_item = pg_fetch_result($res, $i, "faturamento_item");
                        $referencia       = pg_fetch_result($res, $i, "referencia");
                        $descricao_peca   = pg_fetch_result($res, $i, "descricao");
                        $qtde_peca        = pg_fetch_result($res, $i, "qtde");
                        $qtde_quebrada    = pg_fetch_result($res, $i, "qtde_quebrada");
                        $observacao       = pg_fetch_result($res, $i, "obs_conferencia");
                        $qtde_recebida = $qtde_peca - $qtde_quebrada;
                        ?>
                        <tr>
                            <td><?=$referencia?></td>
                            <td><?=$descricao_peca?></td>
                            <td class="qtde_peca tac"><?=$qtde_peca?></td>
                            <td class="tac"><?=$qtde_recebida?></td>
                            <td class="tac"><?=utf8_decode($observacao)?></td>
                        </tr>
                    <?php 
                    }
                } ?>
                </tbody>
            </table>
        </div>
    </div>
</form>