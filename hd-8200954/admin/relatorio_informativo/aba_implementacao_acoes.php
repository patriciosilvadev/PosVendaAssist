<input type="hidden" name="ri_acoes_corretivas[id]" value="<?= $dadosRequisicao["ri_acoes_corretivas"]["id"] ?>" />
<div align='center' class='form-horizontal tc_formulario'>
    <div class="row row-relatorio">
        <div class='titulo_tabela' style="margin-bottom: 30px;">Implementação Permanente das Ações Corretivas</div>
        <div class="col-sm-10 col-sm-offset-1">
            <div class="alert alert-info">
                <h5>Verificar a implementação das ações corretivas</h5>
            </div>
        </div>
        <div class="col-sm-7">
            <textarea name="ri_acoes_corretivas[implementacao_permanente]" class="textarea_ckeditor" rows="6" cols="110" style='font-size:10px;'><?= $dadosRequisicao["ri_acoes_corretivas"]["implementacao_permanente"] ?></textarea>
        </div>
        <div class="col-sm-5">
            <?php
            include "../box_uploader.php";
            ?>
        </div>
    </div>
    <br />
    <div class="row row-relatorio">
        <div class="col-sm-5 col-sm-offset-1">
            <div class="form-group">
                <label class="col-sm-2 control-label">Responsável</label>
                <div class="col-sm-8">
                    <select class="form-control obrigatorio" name="ri_acoes_corretivas[implementacao_permanente_admin]" style="width: 210px;">
                        <option value="">Selecione o Responsável</option>
                        <?php
                        $sqlAdminAnalise = "SELECT admin, nome_completo
                                            FROM tbl_admin
                                            WHERE fabrica = {$login_fabrica}
                                            AND json_field('analise_ri', parametros_adicionais) = 't'
                                            AND ativo";
                        $resAdminAnalise = pg_query($con, $sqlAdminAnalise);

                        while ($dadosAdm = pg_fetch_object($resAdminAnalise)) {

                            $selected = $dadosRequisicao["ri_acoes_corretivas"]["implementacao_permanente_admin"] == $dadosAdm->admin ? "selected" : "";

                        ?>
                            <option value="<?= $dadosAdm->admin ?>" <?= $selected ?>><?= $dadosAdm->nome_completo ?></option>
                        <?php
                        } ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="col-sm-2 control-label">Data</label>
                <div class="col-sm-5">
                    <input class="form-control obrigatorio" type="text" style="width: 140px;" name="ri_acoes_corretivas[implementacao_permanente_data]" id="data_implementacao" maxlength="10" value="<?= mostra_data($dadosRequisicao["ri_acoes_corretivas"]["implementacao_permanente_data"]) ?>" />
                </div>
            </div>
        </div>
    </div>
    <div class="row row-relatorio">
        <div class="col-sm-12" style="text-align: center;">
            <hr />
            <br />
            <input data-aba="<?= $codigoAba ?>" type="button" class="btn-submit btn btn-default btn-lg" value="<?= !empty($dadosRequisicao['ri_acoes_corretivas']['implementacao_permanente']) ? 'Alterar' : 'Gravar' ?>" style="width: 150px;" />
        </div>
    </div>
	<br /><br />
</div>
