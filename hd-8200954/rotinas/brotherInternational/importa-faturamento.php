<?php
/**
 *
 * importa-faturamento.php
 *
 * Importa��o de pedidos de pecas
 *
 * @author  Ronald Santos
 * @version 2018.02.08
 *
*/

define('ENV','producao');  // producao Alterar para produ��o ou algo assim

try {

	include dirname(__FILE__) . '/../../dbconfig.php';
	include dirname(__FILE__) . '/../../includes/dbconnect-inc.php';
	include dirname(__FILE__) . '/../funcoes.php';
	include dirname(__FILE__) . '/../../class/email/mailer/class.phpmailer.php';

    $data['login_fabrica'] 	= 203;
    $data['fabrica'] 		= 'brotherInternational';
    $data['arquivo_log'] 	= 'importa-faturamento';
    $data['tipo'] 		= 'importa-faturamento';
    $data['log'] 		= 2;
    $data['arquivos'] 		= "/tmp";
    $data['data_sistema'] 	= Date('Y-m-d');
    $logs 					= array();
    $logs_erro				= array();
    $logs_cliente			= array();
    $erro 					= false;

    if (ENV == 'producao' ) {
	    $data['dest'] 		= 'helpdesk@telecontrol.com.br';
	    $data['dest_cliente']  	= 'helpdesk@telecontrol.com.br';
	    $data['origem']		= "/home/brotherInternational/brotherInternational-telecontrol/";
	    $data['file']		= 'faturamento.txt';
	    $data['file2']		= 'faturamento-item.txt';
    } else {
	    $data['dest'] 		= 'ronald.santos@telecontrol.com.br';
	    $data['dest_cliente'] 	= 'ronald.santos@telecontrol.com.br';
	    $data['origem']		=  "/home/ronald/public_html/PosVendaAssist/rotinas/brotherInternational/";
	    $data['file']		= 'faturamento.txt';
	    $data['file2']		= 'faturamento-item.txt';
    }

    extract($data);

	define('APP', 'Importa Faturamento - '.$fabrica);

    $arquivo_err = "{$arquivos}/{$fabrica}/{$arquivo_log}-{$data_sistema}.err";
    $arquivo_log = "{$arquivos}/{$fabrica}/{$arquivo_log}-{$data_sistema}.log";
    system ("mkdir {$arquivos}/{$fabrica}/ 2> /dev/null ; chmod 0777 {$arquivos}/{$fabrica}/" );


    if(file_exists($origem.$file)){

	  $sql = "DROP TABLE IF EXISTS brotherInternational_nf;";
	  $res = pg_query($con,$sql);
	  $msg_erro .= pg_errormessage($con);


	  $sql = "CREATE TABLE brotherInternational_nf (
				  txt_cnpj           text,
				  txt_nota_fiscal    text,
				  txt_serie          text,
				  txt_emissao        text,
				  txt_cfop           text,
				  txt_total          text,
				  txt_ipi            text,
				  txt_icms           text,
				  txt_transp         text,
				  txt_natureza       text
			  )";
	  $res = pg_query($con,$sql);
	  $msg_erro .= pg_errormessage($con);

	  $linhas = file_get_contents($origem.$file);
	  $linhas = explode("\n",$linhas);

	  $erro = $msg_erro;
	  
	foreach($linhas AS $linha){

		$msg_erro = "";

			list($txt_cnpj, $txt_nota_fiscal, $txt_serie, $txt_emissao, $txt_cfop, $txt_total, $txt_ipi, $txt_icms, $txt_transp, $txt_natureza) = explode("|",$linha);
		if(!empty($txt_cnpj)){

				$txt_cnpj = trim($txt_cnpj);
				$txt_nota_fiscal = trim($txt_nota_fiscal);
				$txt_serie = trim($txt_serie);
				$txt_emissao = trim($txt_emissao);
				$txt_cfop = trim($txt_cfop);
				$txt_total = trim($txt_total);
				$txt_ipi = trim($txt_ipi);
				$txt_icms = trim($txt_icms);
				$txt_transp = trim($txt_transp);
				$txt_natureza = trim($txt_natureza);

				$txt_cnpj = str_replace('.','',$txt_cnpj);
				$txt_cnpj = str_replace('/','',$txt_cnpj);
				$txt_cnpj = str_replace('-','',$txt_cnpj);

				$txt_natureza = str_replace("\r","",$txt_natureza);

				$res = pg_query($con,"BEGIN");
				$sql = "INSERT INTO brotherInternational_nf ( txt_cnpj                 ,
								    txt_nota_fiscal         ,
								    txt_serie               ,
								    txt_emissao             ,
								    txt_cfop                ,
								    txt_total               ,
								    txt_ipi                 ,
								    txt_icms                ,
								    txt_transp              ,
								    txt_natureza               
								  ) VALUES (
								      '$txt_cnpj'                ,
								      '$txt_nota_fiscal'         ,
								      '$txt_serie'               ,
								      '$txt_emissao'             ,
								      '$txt_cfop'                ,
								      '$txt_total'               ,
								      '$txt_ipi'                 ,
								      '$txt_icms'                ,
								      '".pg_escape_string($txt_transp)."'              ,
								      '$txt_natureza'       
								  );";
				$res = pg_query($con,$sql);
				$msg_erro = pg_errormessage($con);

				if(!empty($msg_erro)){
					$res = pg_query($con,"ROLLBACK");
					$erro .= $msg_erro;
				} else {
					$res = pg_query($con,"COMMIT");
				}
			}

	  }

	  $msg_erro = $erro;

	  $sql = "UPDATE brotherInternational_nf SET
				txt_cnpj        = trim (txt_cnpj)                      ,
				txt_nota_fiscal = trim(txt_nota_fiscal) 			   ,
				txt_serie       = trim (txt_serie)                     ,
				txt_emissao     = trim (txt_emissao)                   ,
				txt_transp      = trim (txt_transp)                    ,
				txt_cfop        = trim (txt_cfop)                      ,
				txt_total       = trim (txt_total)                     ,
				txt_ipi         = trim (txt_ipi)                       ,
				txt_icms        = trim (txt_icms)                      ,
				txt_natureza    = trim (txt_natureza)                  ;";
	$res = pg_query($con,$sql);

	$sql = "ALTER TABLE brotherInternational_nf ADD COLUMN total FLOAT";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf ADD COLUMN emissao DATE";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf ADD COLUMN saida DATE";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf ADD COLUMN posto INT4";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf ADD COLUMN pedido INT4";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "UPDATE brotherInternational_nf SET
				emissao     = TO_DATE(txt_emissao,'YYYY-MM-DD'),
				saida       = TO_DATE(txt_emissao,'YYYY-MM-DD'),
				total       = REPLACE(txt_total,',','.')::numeric
			WHERE txt_emissao <> ''
			AND   txt_total <> ''";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);
	
	$sql = "UPDATE brotherInternational_nf SET posto = tbl_posto.posto
			FROM tbl_posto,tbl_posto_fabrica
			WHERE trim(brotherInternational_nf.txt_cnpj) = tbl_posto.cnpj
			AND tbl_posto.posto = tbl_posto_fabrica.posto
			AND tbl_posto_fabrica.fabrica = $login_fabrica
			";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	#------------ IDENTIFICAR POSTOS NAO ENCONTRADOS PELO CNPJ --------------#
	$sql = "DROP TABLE IF EXISTS brotherInternational_nf_sem_posto";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "SELECT * INTO brotherInternational_nf_sem_posto FROM brotherInternational_nf WHERE posto IS NULL";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "DELETE FROM brotherInternational_nf
			WHERE posto IS NULL";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "DROP TABLE IF EXISTS brotherInternational_nf_item;";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "CREATE TABLE brotherInternational_nf_item (
				txt_cnpj        text,
				txt_nota_fiscal text,
				txt_serie       text,
				txt_referencia  text,
				txt_pedido      text,
				txt_pedido_item text,
				txt_qtde        text,
				txt_unitario    text,
				txt_aliq_ipi    text,
				txt_aliq_icms   text,
				txt_valor_ipi   text,
				txt_valor_icms  text,
				txt_base_ipi    text,
				txt_base_icms   text
			)";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$linhas_item = file_get_contents($origem.$file2);
	$linhas_item = explode("\n",$linhas_item);

	$erro = $msg_erro;

	foreach($linhas_item AS $linha_item){

	      list($txt_cnpj, $txt_nota_fiscal, $txt_serie, $txt_referencia, $txt_pedido, $txt_pedido_item, $txt_qtde, $txt_unitario, $txt_aliq_ipi, $txt_aliq_icms, $txt_valor_ipi, $txt_valor_icms, $txt_base_ipi, $txt_base_icms) = explode ('|',$linha_item);
	      if(strlen(trim($txt_cnpj)) > 0 AND trim($txt_pedido_item) != "?"){
		      	$txt_cnpj = trim($txt_cnpj);
			$txt_nota_fiscal = trim($txt_nota_fiscal);
		 	$txt_serie = trim($txt_serie); 
                	$txt_referencia = trim($txt_referencia);
			$txt_pedido = trim($txt_pedido); 
			$txt_pedido_item = trim($txt_pedido_item); 
			$txt_qtde = trim($txt_qtde); 
			$txt_unitario = trim($txt_unitario); 
			$txt_aliq_ipi = trim($txt_aliq_ipi); 
			$txt_aliq_icms = trim($txt_aliq_icms); 
			$txt_valor_ipi = trim($txt_valor_ipi); 
			$txt_valor_icms = trim($txt_valor_icms); 
			$txt_base_ipi = trim($txt_base_ipi); 
			$txt_base_icms = trim($txt_base_icms);

			$txt_cnpj = str_replace('.','',$txt_cnpj);
			$txt_cnpj = str_replace('/','',$txt_cnpj);
			$txt_cnpj = str_replace('-','',$txt_cnpj);
			$txt_base_icms = str_replace("\r","",$txt_base_icms);

            		$res = pg_query($con,"BEGIN");
	                $sql = "INSERT INTO brotherInternational_nf_item    (
                                                        txt_cnpj        ,
														txt_nota_fiscal ,
														txt_serie       ,
														txt_referencia  ,
														txt_pedido      ,
														txt_pedido_item ,
														txt_qtde        ,
														txt_unitario    ,
														txt_aliq_ipi    ,
														txt_aliq_icms   ,
														txt_valor_ipi   ,
														txt_valor_icms  ,
														txt_base_ipi    ,
														txt_base_icms    
                                                    ) VALUES (
                                                        '$txt_cnpj', 
														'$txt_nota_fiscal', 
														'$txt_serie', 
														'$txt_referencia', 
														'$txt_pedido', 
														'$txt_pedido_item', 
														'$txt_qtde', 
														'$txt_unitario', 
														'$txt_aliq_ipi', 
														'$txt_aliq_icms', 
														'$txt_valor_ipi', 
														'$txt_valor_icms', 
														'$txt_base_ipi', 
														'$txt_base_icms'
													);";

		      $res = pg_query($con,$sql);
		      $msg_erro .= pg_errormessage($con);

		      if(!empty($msg_erro)){
			      $res = pg_query($con,"ROLLBACK");
			      $erro .= $msg_erro;
		      } else {
			      $res = pg_query($con,"COMMIT");
		      }
	      }

	}

	$msg_erro = $erro;
	$msg_erro;

	$sql = "UPDATE brotherInternational_nf_item SET
				txt_cnpj 		= trim(txt_cnpj), 
				txt_nota_fiscal = trim(txt_nota_fiscal) , 
				txt_serie 		= trim(txt_serie) , 
				txt_referencia 	= trim(txt_referencia) , 
				txt_pedido 		= trim(txt_pedido) , 
				txt_pedido_item = trim(txt_pedido_item) , 
				txt_qtde 		= trim(txt_qtde) , 
				txt_unitario 	= trim(txt_unitario) , 
				txt_aliq_ipi 	= trim(txt_aliq_ipi) , 
				txt_aliq_icms 	= trim(txt_aliq_icms) , 
				txt_valor_ipi 	= trim(txt_valor_ipi) , 
				txt_valor_icms 	= trim(txt_valor_icms) ,  
				txt_base_ipi 	= trim(txt_base_ipi) , 
				txt_base_icms 	= trim(txt_base_icms) ";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN posto INT4";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN peca INT4";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN qtde FLOAT";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN pedido INT4";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN pedido_item INT4";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN unitario FLOAT";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN aliq_ipi FLOAT";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN valor_ipi FLOAT";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN valor_icms FLOAT";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN aliq_icms FLOAT";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN base_icms FLOAT";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN base_ipi FLOAT";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN valor_subs_trib FLOAT";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN base_subs_trib FLOAT";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN os_item INT4";
    $res = pg_query($con,$sql);
    $msg_erro .= pg_errormessage($con);

    $sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN devolucao boolean";
    $res = pg_query($con,$sql);
    $msg_erro .= pg_errormessage($con);


	$sql = "UPDATE brotherInternational_nf_item SET
				qtde       		= txt_qtde::numeric                        ,
				unitario   		= REPLACE(case when length(txt_unitario)    = 0 then '0' else txt_unitario end   ,',','.')::numeric   ,
				aliq_ipi   		= REPLACE(case when length(txt_aliq_ipi )   = 0 then '0' else txt_aliq_ipi end   ,',','.')::numeric   ,
				aliq_icms  		= REPLACE(case when length(txt_aliq_icms )  = 0 then '0' else txt_aliq_icms end   ,',','.')::numeric   ,
				valor_ipi  		= REPLACE(case when length(txt_valor_ipi )  = 0 then '0' else txt_valor_ipi end  ,',','.')::numeric  ,
				valor_icms 		= REPLACE(case when length(txt_valor_icms ) = 0 then '0' else txt_valor_icms end ,',','.')::numeric ,
				base_ipi   		= REPLACE(case when length(txt_base_ipi ) = 0 then '0' else txt_base_ipi end ,',','.')::numeric ,
				base_icms  		= REPLACE(case when length(txt_base_icms )  = 0 then '0' else txt_base_icms end  ,',','.')::numeric";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "UPDATE brotherInternational_nf_item SET posto = (
				SELECT tbl_posto.posto
				FROM tbl_posto
				JOIN tbl_posto_fabrica ON tbl_posto.posto = tbl_posto_fabrica.posto
									AND   tbl_posto_fabrica.fabrica = $login_fabrica
				WHERE brotherInternational_nf_item.txt_cnpj = tbl_posto.cnpj
			)";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "UPDATE brotherInternational_nf_item
			SET pedido = tbl_pedido_item.pedido,
				pedido_item = tbl_pedido_item.pedido_item
			FROM tbl_pedido_item, tbl_pedido
			WHERE brotherInternational_nf_item.txt_pedido::integer = tbl_pedido_item.pedido
			AND   brotherInternational_nf_item.txt_pedido_item::integer = tbl_pedido_item.pedido_item
			AND tbl_pedido_item.pedido = tbl_pedido.pedido
			AND tbl_pedido.fabrica = $login_fabrica";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "UPDATE brotherInternational_nf_item
                    SET os_item = tbl_os_item.os_item,
                    devolucao = tbl_os_item.peca_obrigatoria
                    FROM tbl_os_item
                    WHERE brotherInternational_nf_item.pedido = tbl_os_item.pedido
                    AND   brotherInternational_nf_item.pedido_item = tbl_os_item.pedido_item
                    AND tbl_os_item.fabrica_i = $login_fabrica";
    $res = pg_query($con,$sql);
    $msg_erro .= pg_errormessage($con);

	$sql = "UPDATE brotherInternational_nf_item
			SET peca = tbl_peca.peca
			FROM  tbl_peca
			WHERE brotherInternational_nf_item.txt_referencia = tbl_peca.referencia
			AND tbl_peca.fabrica = $login_fabrica";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "UPDATE brotherInternational_nf
				SET pedido = brotherInternational_nf_item.pedido
			FROM brotherInternational_nf_item
			WHERE trim(brotherInternational_nf.txt_nota_fiscal) = trim(brotherInternational_nf_item.txt_nota_fiscal)
			AND  brotherInternational_nf.posto = brotherInternational_nf_item.posto";
	$res = pg_query($con,$sql);
$msg_erro .= pg_errormessage($con);

	#------------ Desconsidera Notas ja Importadas ------------------
	$sql = "DELETE FROM brotherInternational_nf_item
			WHERE pedido is null;";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "DELETE FROM brotherInternational_nf
			USING tbl_faturamento
			WHERE txt_nota_fiscal         = tbl_faturamento.nota_fiscal
			AND   txt_serie               = tbl_faturamento.serie
			AND   tbl_faturamento.fabrica = $login_fabrica";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "DELETE FROM brotherInternational_nf_item
			USING tbl_faturamento
			WHERE txt_nota_fiscal         = tbl_faturamento.nota_fiscal
			AND   txt_serie               = tbl_faturamento.serie
			AND   tbl_faturamento.fabrica = $login_fabrica";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	#------------ NFs sem Itens --------------#
	$sql = "DROP TABLE IF EXISTS brotherInternational_nf_sem_itens";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "SELECT brotherInternational_nf.*
			INTO brotherInternational_nf_sem_itens
			FROM brotherInternational_nf
			LEFT JOIN brotherInternational_nf_item ON trim(brotherInternational_nf.txt_nota_fiscal) = trim(brotherInternational_nf_item.txt_nota_fiscal)
			WHERE brotherInternational_nf_item.txt_nota_fiscal IS NULL";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "DELETE FROM brotherInternational_nf
			USING brotherInternational_nf_sem_itens
			WHERE brotherInternational_nf.txt_nota_fiscal = brotherInternational_nf_sem_itens.txt_nota_fiscal
			AND   brotherInternational_nf.txt_serie       = brotherInternational_nf_sem_itens.txt_serie";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	#----------------- Importa REALMENTE ------------
	$sql = "INSERT INTO tbl_faturamento (
				fabrica     ,
				emissao     ,
				saida       ,
				transp      ,
				posto       ,
				total_nota  ,
				cfop        ,
				nota_fiscal ,
				serie       ,
				tipo_pedido,
				natureza
			)
				SELECT  $login_fabrica,
						brotherInternational_nf.emissao         ,
						brotherInternational_nf.saida         ,
						substring(brotherInternational_nf.txt_transp, 1,30),
						brotherInternational_nf.posto           ,
						brotherInternational_nf.total           ,
						brotherInternational_nf.txt_cfop        ,
						brotherInternational_nf.txt_nota_fiscal ,
						brotherInternational_nf.txt_serie       ,
						(select tipo_pedido from tbl_pedido where pedido = brotherInternational_nf.pedido),
						brotherInternational_nf.txt_natureza
				FROM brotherInternational_nf
				LEFT JOIN tbl_faturamento ON  brotherInternational_nf.txt_nota_fiscal   = tbl_faturamento.nota_fiscal
										 AND  brotherInternational_nf.txt_serie         = tbl_faturamento.serie
										 AND  tbl_faturamento.fabrica      = $login_fabrica
										 AND  tbl_faturamento.distribuidor IS NULL
				WHERE tbl_faturamento.faturamento IS NULL
			";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN faturamento INT4";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "ALTER TABLE brotherInternational_nf_item ADD COLUMN cfop text";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "UPDATE brotherInternational_nf_item
			SET faturamento = tbl_faturamento.faturamento, cfop = tbl_faturamento.cfop
			FROM tbl_faturamento
			WHERE tbl_faturamento.fabrica     = $login_fabrica
			AND   tbl_faturamento.nota_fiscal = brotherInternational_nf_item.txt_nota_fiscal
			AND   tbl_faturamento.serie       = brotherInternational_nf_item.txt_serie";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	#------ Tratar itens sem nota ------

	$sql = "DELETE FROM brotherInternational_nf_item
			WHERE faturamento IS NULL";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);


	$sql = "DROP TABLE IF EXISTS brotherInternational_nf_item_sem_peca ";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);


	$sql = "SELECT * INTO brotherInternational_nf_item_sem_peca
			FROM brotherInternational_nf_item
				WHERE peca IS NULL" ;
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);


	$sql = "DELETE FROM brotherInternational_nf_item
			WHERE peca IS NULL" ;
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);


	$sql = "SELECT  DISTINCT faturamento,
							pedido     ,
							pedido_item,
							peca       ,
							qtde as qtde_fat,
							cfop       ,
							unitario   ,
							txt_referencia,
							aliq_ipi   ,
							aliq_icms  ,
							valor_ipi  ,
							valor_icms ,
							valor_subs_trib,
							base_ipi   ,
							base_icms  ,
							base_subs_trib,
							devolucao,
							os_item
			FROM brotherInternational_nf_item;";
	$res = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);


	for($x = 0; $x < pg_numrows($res); $x++){
		$pedido          = pg_fetch_result($res,$x,'pedido');
		$pedido_item     = pg_fetch_result($res,$x,'pedido_item');
		$faturamento     = pg_fetch_result($res,$x,'faturamento');
		$peca            = pg_fetch_result($res,$x,'peca');
		$cfop            = pg_fetch_result($res,$x,'cfop');
		$qtde_fat        = pg_fetch_result($res,$x,'qtde_fat');
		$unitario        = pg_fetch_result($res,$x,'unitario');		
		$txt_referencia  = pg_fetch_result($res,$x,'txt_referencia');
		$aliq_ipi  		 = pg_fetch_result($res,$x,'aliq_ipi'); 
		$aliq_icms 		 = pg_fetch_result($res,$x,'aliq_icms'); 
		$valor_ipi  	 = pg_fetch_result($res,$x,'valor_ipi');
		$valor_icms 	 = pg_fetch_result($res,$x,'valor_icms');
		$valor_subs_trib = pg_fetch_result($res,$x,'valor_subs_trib');
		$base_ipi   	 = pg_fetch_result($res,$x,'base_ipi');
		$base_icms  	 = pg_fetch_result($res,$x,'base_icms');
		$base_subs_trib  = pg_fetch_result($res,$x,'base_subs_trib');
		$devolucao_obrig = (pg_fetch_result($res,$x,'devolucao') == 't') ? 't' : 'f';
		$os_item         = pg_fetch_result($res,$x,'os_item');

		$os_item = (empty($os_item)) ? "null" : $os_item;

		$sql = "INSERT INTO tbl_faturamento_item (
					faturamento,
					pedido     ,
					pedido_item,
					peca       ,
					qtde       ,
					preco      ,
					cfop       ,
					aliq_ipi   ,
					aliq_icms  ,
					valor_ipi  ,
					valor_icms ,
					base_ipi   ,
					base_icms  ,
					os_item    ,
					devolucao_obrig
				)
				VALUES(
					$faturamento,
					$pedido     ,
					$pedido_item,
					$peca       ,
					$qtde_fat   ,
					$unitario   ,
					'$cfop'     ,
					$aliq_ipi   ,
					$aliq_icms  ,
					$valor_ipi  ,
					$valor_icms ,
					$base_ipi   ,
					$base_icms  ,
					$os_item    ,
					'$devolucao_obrig'
				)";
		$res2 = pg_query($con,$sql);
		$msg_erro .= pg_errormessage($con);
		

		$sql = "SELECT  qtde as qtde_pedido,
						pedido_item,
						tipo_pedido,
						posto
				FROM tbl_pedido
				JOIN tbl_pedido_item ON tbl_pedido.pedido = tbl_pedido_item.pedido
				WHERE tbl_pedido.pedido = $pedido
					AND tbl_pedido.fabrica = $login_fabrica
					AND peca = $peca
					AND qtde > qtde_faturada
				LIMIT 1;";
		$res2 = pg_query($con,$sql);
		$msg_erro .= pg_errormessage($con);

		if(pg_numrows($res2) > 0){
			$pedido_item = pg_result($res2,0,'pedido_item');
			$qtde_pedido = pg_result($res2,0,'qtde_pedido');
			$tipo_pedido = pg_result($res2,0,'tipo_pedido');
			$posto_pedido = pg_result($res2,0,'posto');

			$sql = "UPDATE tbl_pedido_item
					SET qtde_faturada =  (qtde_faturada + $qtde_fat)
					WHERE pedido_item = $pedido_item;";
			$res3 = pg_query($con,$sql);
			$msg_erro .= pg_errormessage($con);
		}else{
			$msg_erro .= " N�o foi encontrado o item para atualizar: \n";
			$msg_erro .= " Pedido: $pedido - Pe�a: $txt_referencia - Qtd.Fat: $qtde_fat \n";
			$msg_erro .= "\n\n\n";
		}

		$sql = "UPDATE tbl_faturamento_item
				SET aliq_icms     = round((valor_icms / (preco*qtde))*100)
				WHERE faturamento = $faturamento ";
		$res3 = pg_query($con,$sql);
		$msg_erro .= pg_errormessage($con);
	}

	$sql = "SELECT fn_atualiza_pedido_recebido_fabrica(pedido,$login_fabrica,current_date)
			FROM  brotherInternational_nf_item
			";
	$res3 = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	$sql = "SELECT fn_atualiza_status_pedido($login_fabrica,pedido)
				FROM brotherInternational_nf_item;";
	$res3 = pg_query($con,$sql);
	$msg_erro .= pg_errormessage($con);

	}

	if (!empty($msg_erro)) {
		$msg_erro .= "\n\n".$log_erro;
		$fp = fopen("/tmp/brotherInternational/faturamento.err","w");
		fwrite($fp,$msg_erro);
		fclose($fp);
		$msg = 'Script: '.__FILE__.'<br />' . $msg_erro;
		Log::envia_email($data, APP, $msg);

	} else {
		$fp = fopen("/tmp/brotherInternational/faturamento.err","w");
		fwrite($fp,$log_erro);
		fclose($fp);

		system("mv $origem$file /tmp/brotherInternational/posvenda/faturamento".date('Y-m-d-H-i').".txt");
		system("mv $origem$file2 /tmp/brotherInternational/posvenda/faturamento_item".date('Y-m-d-H-i').".txt");

		Log::log2($data, APP . ' - Executado com Sucesso - ' . date('Y-m-d-H-i'));

	}

} catch (Exception $e) {
	$e->getMessage();
    $msg = "Arquivo: ".__FILE__."\r\n<br />Linha: " . $e->getLine() . "\r\n<br />Descri��o do erro: " . $e->getMessage() ."<hr /><br /><br />". implode("<br /><br />", $logs);

    Log::envia_email($data,Date('d/m/Y H:i:s')." - BROTHER INTERNATIONAL - Importa faturamento (importa-faturamento.php)", $msg);
}?>
