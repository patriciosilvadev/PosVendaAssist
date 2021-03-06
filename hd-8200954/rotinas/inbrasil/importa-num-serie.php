<?php
/*
 * @description Importa numero de série - inbrasil - HD 250208
 * @author Brayan L. Rastelli
 */

define('APP','Importa N�mero de S�rie - Inbrasil'); // Nome da rotina, para ser enviado por e-mail
define('ENV','dev'); // Em produção alterar para producao e em desenvolvimento para dev

try {

	include dirname(__FILE__) . '/../../dbconfig.php';
	include dirname(__FILE__) . '/../../includes/dbconnect-inc.php';
	require dirname(__FILE__) . '/../funcoes.php';

	$fabrica = 150;
	
	$phpCron = new PHPCron($fabrica, __FILE__); 
	$phpCron->inicio();

	$vet['fabrica'] = 'inbrasil';
	$vet['tipo']    = 'importa-numero-serie';
	$vet['dest']    = ENV == 'dev' ? 'william.lopes@telecontrol.com.br' : 'helpdesk@telecontrol.com.br';
	$vet['log']     = 1;

	$arq = "/tmp/".$vet['fabrica']."/num_serie.txt";

	if ( !file_exists($arq) ) {
		throw new Exception("Arquivo $arq n�o foi encontrado.");
	}

	$fp = fopen($arq, 'r');

	if (!is_resource($fp))
		throw new Exception("Falha ao abrir arquivo de numero de s�rie: $arq");

	$sql = "DROP TABLE IF EXISTS {$vet['fabrica']}_numero_serie;
			CREATE TABLE {$vet['fabrica']}_numero_serie 
			(
				referencia      text,
				serie           text,			
				fabricacao      text
				);
			";

	$res = pg_query($con,$sql);
	$msg_erro = pg_errormessage($con);


	$arquivo = file_get_contents($arq);

	$linha = explode("\n", $arquivo);


	foreach($linha as $key => $l){

		$serie = "";
		$data_venda = "";
		
		list($serie, $referencia, $fabricacao) = explode("\t", $l);

		$sql = "
			INSERT INTO {$vet['fabrica']}_numero_serie 
				(referencia, serie, fabricacao) 
						VALUES 
						('$referencia', '$serie', '$fabricacao')";
		$res = pg_query($con, $sql);

		$cont++;
	}

	//RETIRA ESPA�OS

	 $sql = "UPDATE {$vet['fabrica']}_numero_serie SET
					serie = trim(serie),
					referencia = trim(referencia),
					fabricacao = trim(fabricacao) ";

	$res = pg_query($con,$sql);
	$msg_erro = pg_errormessage($con);
	if (!empty($msg_erro)){
		throw new Exception($msg_erro);		
	}


	//CRIA NOVO CAMPO "numero_serie" e atualiza ele com os numeros de s�rie da tbl_numero_serie quando tiver produto igual

	$sql = "ALTER TABLE {$vet['fabrica']}_numero_serie ADD COLUMN numero_serie integer"; 

	$res = pg_query($con,$sql);
	$msg_erro = pg_errormessage($con);
	if (!empty($msg_erro))
		throw new Exception($msg_erro);	

	$sql = "UPDATE {$vet['fabrica']}_numero_serie 
			SET numero_serie = tbl_numero_serie.numero_serie 
			FROM tbl_numero_serie 
			WHERE tbl_numero_serie.fabrica = $fabrica 
			AND tbl_numero_serie.serie = {$vet['fabrica']}_numero_serie.serie 
			AND tbl_numero_serie.referencia_produto = {$vet['fabrica']}_numero_serie.referencia ";

	$res = pg_query($con,$sql);

	$msg_erro = pg_last_error();

	if (!empty($msg_erro))
		throw new Exception($msg_erro);	

	//CRIA novos campos "produto" e "data_fabricao"

	$sql = "ALTER TABLE {$vet['fabrica']}_numero_serie ADD COLUMN produto integer;
			ALTER TABLE {$vet['fabrica']}_numero_serie ADD COLUMN data_fabricacao date; ";

	$res = pg_query($con,$sql);
	$msg_erro = pg_errormessage($con);
	if (!empty($msg_erro))
		throw new Exception($msg_erro);	

	//Atualiza campos produto e data_fabricacao
	$sql = "UPDATE {$vet['fabrica']}_numero_serie SET produto = tbl_produto.produto
			FROM tbl_produto
			JOIN tbl_linha USING(linha)
			WHERE trim({$vet['fabrica']}_numero_serie.referencia) = tbl_produto.referencia
			AND   fabrica = $fabrica; ";

	$res = pg_query($con,$sql);
	$msg_erro = pg_errormessage($con);
	if (!empty($msg_erro))
		throw new Exception($msg_erro);	

	$sql = "UPDATE {$vet['fabrica']}_numero_serie SET data_fabricacao = fabricacao::date
			WHERE length(fabricacao) > 0;
			";

	$res = pg_query($con,$sql);
	$msg_erro = pg_errormessage($con);

	if (!empty($msg_erro))
		throw new Exception($msg_erro);

	// Insert na tbl_numero_serie

	$sql = "INSERT INTO tbl_numero_serie(
		fabrica,
		serie,
		referencia_produto,
		data_fabricacao,
		produto
		) 
		SELECT
		$fabrica,
		serie,
		referencia,
		data_fabricacao::date,
		produto
		t
		FROM {$vet['fabrica']}_numero_serie 
		WHERE numero_serie is null";

	$res = pg_query($con,$sql);

	$msg_erro = pg_errormessage($con);

	if (!empty($msg_erro))
		throw new Exception($msg_erro);

	//UPDATE tbl_numero_serie

	$sql = "UPDATE tbl_numero_serie 

			SET serie = {$vet['fabrica']}_numero_serie.serie,
			referencia_produto =  {$vet['fabrica']}_numero_serie.referencia,
			data_fabricacao =  {$vet['fabrica']}_numero_serie.data_fabricacao::date,
			ordem =  {$vet['fabrica']}_numero_serie.ativo 

			FROM {$vet['fabrica']}_numero_serie 
			WHERE tbl_numero_serie.fabrica = $fabrica
			AND tbl_numero_serie.numero_serie = {$vet['fabrica']}_numero_serie.numero_serie
			AND {$vet['fabrica']}_numero_serie.numero_serie is not null
		";

	$res = pg_query($con,$sql);

	$msg_erro = pg_errormessage($con);

	if (!empty($msg_erro))
		throw new Exception($msg_erro);

	$arq_new = 'num-serie-' . (date('Y-m-d')) . '.txt'; 

	system("mv $arq /tmp/inbrasil/$arq_new");

	$phpCron->termino();

	}
catch (Exception $e) {

	$msg = 'Script: '.__FILE__.'<br />Erro na linha ' . $e->getLine() . ':<br />' . $e->getMessage();
	Log::envia_email($vet,APP, $msg );

}
