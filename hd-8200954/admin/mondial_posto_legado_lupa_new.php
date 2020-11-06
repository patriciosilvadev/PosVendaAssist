<?php
include "dbconfig.php";
include "includes/dbconnect-inc.php";

if (preg_match("/\/admin\//", $_SERVER["PHP_SELF"])) {
    include 'autentica_admin.php';
} else {
    include 'autentica_usuario.php';
}

$parametro = $_REQUEST["parametro"];
$valor     = trim($_REQUEST["valor"]);

if ($parametro == 'descricao') {
    $cond .= ($login_fabrica == 203) ? " AND tbl_posto.nome ilike '%$valor%' " : " posto ilike '%$valor%' ";
}
?>
<!DOCTYPE html />
<html>
    <head>
        <meta http-equiv=pragma content=no-cache>
        <link href="bootstrap/css/bootstrap.css" type="text/css" rel="stylesheet" media="screen" />
        <link href="bootstrap/css/extra.css" type="text/css" rel="stylesheet" media="screen" />
        <link href="css/tc_css.css" type="text/css" rel="stylesheet" media="screen" />
        <link href="bootstrap/css/ajuste.css" type="text/css" rel="stylesheet" media="screen" />
        <link href="plugins/dataTable.css" type="text/css" rel="stylesheet" />
        <script src="plugins/posvenda_jquery_ui/js/jquery-1.9.1.js"></script>
        <script src="bootstrap/js/bootstrap.js"></script>
        <script src="plugins/dataTable.js"></script>
        <script src="plugins/resize.js"></script>
        <script src="plugins/shadowbox_lupa/lupa.js"></script>
        <script>
            $(function () {
                $.dataTableLupa();
            });
        </script>
    </head>
    <body>
        <div id="container_lupa" style="overflow-y:auto;">
            <div id="topo">
                <img class="espaco" src="imagens/logo_new_telecontrol.png">
                <img class="lupa_img pull-right" src="imagens/lupa_new.png">
            </div>
            <br /><hr />
            <form action="<?=$_SERVER['PHP_SELF']?>" method='POST' >
                <div class="row-fluid">
                    <div class="span1"></div>
                    <div class="span4">
                        <select name="parametro"  >
                            <option value="descricao"  <?=($parametro == "descricao")  ? "SELECTED" : ""?> >Descri��o</option>
                        </select>
                    </div>
                    <div class="span4">
                        <input type="text" name="valor" class="span12" value="<?=$valor?>" />
                    </div>
                    <div class="span2">
                        <button type="button" class="btn pull-right" onclick="$(this).parents('form').submit();">Pesquisar</button>
                    </div>
                    <div class="span1"></div>
                </div>
            </form>
            <?php
                if ($login_fabrica == 203) {
                    $sql = "SELECT DISTINCT  tbl_posto.nome AS posto
                        FROM tbl_os
                        LEFT JOIN tbl_posto_fabrica ON tbl_posto_fabrica.posto = tbl_os.posto AND tbl_posto_fabrica.fabrica = 167
                        LEFT JOIN tbl_posto ON tbl_posto.posto = tbl_posto_fabrica.posto
                        WHERE tbl_os.fabrica = 167
                        {$cond}";
                        //echo $sql;die;
                } else {
                $sql = "SELECT DISTINCT  posto
                    FROM tbl_mondial_os
                    WHERE
                    {$cond}";
                    //echo $sql;die;
                }
                $res = pg_query($con, $sql);
                $rows = pg_num_rows($res);

                if ($rows > 0) {
            ?>
            <div id="border_table">
                <table id="resultados" class="table table-striped table-bordered table-hover table-lupa" >
                    <thead>
                        <tr class='titulo_coluna'>
                            <th>Nome</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        for ($i = 0 ; $i < $rows; $i++) {
                            $posto = pg_fetch_result($res, $i, 'posto');
                            $r = array("nome" => trim($posto));
                            echo "
                            <tr onclick='window.parent.retorna_posto(".json_encode($r)."); window.parent.Shadowbox.close();' >
                                <td class='cursor_lupa'>".utf8_decode($posto)."</td>
                            </tr>";
                        }
                    ?>
                    </tbody>
                </table>
                <?php   
                    } else {
                        echo '<div class="alert alert_shadobox"><h4>Nenhum resultado encontrado</h4></div>';
                    }
                ?>
            </div>
        </div>
    </body>
</html>

