<?include 'dbconfig.php';include 'includes/dbconnect-inc.php';include "autentica_admin.php";if ($_GET["extrato"]){		$extrato = $_GET["extrato"];	if ($login_fabrica == 45) {		$sql = "SELECT count(*) as qtde_os							FROM tbl_os							JOIN tbl_os_extra USING(os)							WHERE tbl_os.mao_de_obra notnull							and tbl_os.pecas       notnull							and ((									SELECT tbl_os_status.status_os									FROM tbl_os_status									WHERE tbl_os_status.os = tbl_os.os									ORDER BY tbl_os_status.data DESC LIMIT 1									) IS NULL								OR (SELECT tbl_os_status.status_os									FROM tbl_os_status WHERE tbl_os_status.os = tbl_os.os									ORDER BY tbl_os_status.data DESC LIMIT 1									) NOT IN (15)								)							and tbl_os_extra.extrato = $extrato";	}	else {		$sql = "SELECT count(*) as qtde_os FROM tbl_os_extra WHERE extrato = $extrato";	}							$res = pg_exec($con,$sql);			if(pg_numrows($res)>0){							$qtde_os = pg_result($res,0,qtde_os);						}			echo "$qtde_os";}	?>