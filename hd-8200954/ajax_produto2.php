<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';
include 'autentica_usuario.php';
header("Expires: 0");
header("Cache-Control: no-cache, public, must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache, public");
//RECEBE PARaMETRO
// $produto_referencia = $_POST["produto_referencia"];
$produto_referencia = $_GET["produto_referencia"]; 
//pegar o login fabrica
$sql="SELECT familia, 
			fabrica, 
			produto, 
			linha 
		FROM tbl_produto 
		JOIN tbl_linha USING(linha) 
		WHERE upper(referencia)=upper('$produto_referencia') 
		AND tbl_linha.fabrica = $login_fabrica LIMIT 1";
if($login_fabrica==24 and 1==2){
	$sql="SELECT familia,
				fabrica,
				produto,
				linha
			FROM tbl_produto 
			JOIN tbl_linha USING(linha) 
			WHERE referencia like '$produto_referencia' LIMIT 1";
}
$res = pg_exec ($con,$sql);
$familia        = pg_result ($res,0,'familia') ;
$linha          = pg_result ($res,0,'linha') ;
$login_fabrica  = pg_result ($res,0,'fabrica') ;
$cod_produto    = pg_result ($res,0,'produto') ;
//echo "familia: $sql";


#TELA NOVA A PARTIR DAQUI ----------------


//PROCURA POR LINHA E FAMILIA
	//Valida��es Latinatec - Linhas sem integridade
	if($login_fabrica == 15){
		if($linha == 319) $linha = 315;
		if($linha == 382) $linha = 317;
		if($linha == 401) $linha = 307;
		if($linha == 390) $linha = 317;
	}
	$sql = "SELECT 	DISTINCT(tbl_diagnostico.defeito_reclamado), 
					tbl_defeito_reclamado.descricao 
			FROM tbl_diagnostico 
			JOIN tbl_defeito_reclamado on tbl_defeito_reclamado.defeito_reclamado = tbl_diagnostico.defeito_reclamado
			WHERE tbl_diagnostico.fabrica=$login_fabrica ";
	if(strlen($familia)>0){ $sql .=" AND tbl_diagnostico.familia=$familia ";}
	if(strlen($linha)>0){$sql .=" AND tbl_diagnostico.linha=$linha ";}
	$sql .= " and tbl_defeito_reclamado.ativo='t' and tbl_diagnostico.ativo='t' ORDER BY tbl_defeito_reclamado.descricao";

	$resD = pg_exec ($con,$sql) ;
	$row = pg_numrows ($resD); 

echo "<BR>$sql"; 
$row = pg_numrows ($resD);
if($row) {
   //XML
   $xml  = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
   $xml .= "<produtos>\n";
   //PERCORRE ARRAY
   for($i=0; $i<$row; $i++) {
      $defeito_reclamado    = pg_result($resD, $i, 'defeito_reclamado');
	  $descricao = pg_result($resD, $i, 'descricao');
	  $xml .= "<produto>\n";
      $xml .= "<codigo>".$defeito_reclamado."</codigo>\n";
	  $xml .= "<nome>".$descricao."</nome>\n";
      $xml .= "</produto>\n";
   }//FECHA FOR
   $xml.= "</produtos>\n";
   //CABE�ALHO
   Header("Content-type: application/xml; charset=iso-8859-1"); 
}//FECHA IF (row)
echo $xml;
?>
