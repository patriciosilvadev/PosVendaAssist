<?php
include '../dbconfig.php';
include '../includes/dbconnect-inc.php';
include 'autentica_usuario_empresa.php';
include '../funcoes.php';
include_once("class_VALIDATE.php");

/*print_r($_GET);
print_r($_POST);*/

if(strlen($_GET["tipo"]) > 0) $tipo = $_GET["tipo"];
else                          $tipo = $_POST["tipo"];

if(strlen($_GET["pessoa"]) > 0) $pessoa = $_GET["pessoa"];
else                            $pessoa = $_POST["pessoa"];

if(strlen($_GET["verificaDoc"]) > 0) $Vdoc = $_GET["verificaDoc"];
else                                 $Vdoc = $_POST["verificaDoc"];

if (strlen($Vdoc)>0){
	$doc  = $_POST["doc"];
	$validate = new VALIDATE;

	if ($Vdoc=='F' AND strlen($doc)>0){
		if(!$validate->cpf($doc)) $msg_erro .= "CPF inv�lido!";
	}

	if ($Vdoc=='J' AND strlen($doc)>0){
		if(!$validate->cnpj($doc)) $msg_erro .= "CNPJ inv�lido!";
	}

	if (strlen($msg_erro)>0){
		echo $msg_erro;
	}
	exit;
}

$btn_acao = trim($_GET["btn_acao"]);
if (strlen($btn_acao)==0){
	$btn_acao = trim($_POST["btn_acao"]);
}

if ($btn_acao == "Gravar") {

	$pessoa           = trim($_POST['pessoa']);
	$nome             = trim($_POST['nome']);
	$tipo_pessoa      = trim($_POST['tipo_pessoa']);
#	$estrangeiro      = trim($_POST['estrangeiro']);
	$cnpj             = trim($_POST['cnpj']);
	$endereco         = trim($_POST['endereco']);
	$numero           = trim($_POST['numero']);
	$complemento      = trim($_POST['complemento']);
	$bairro           = trim($_POST['bairro']);
	$cidade           = trim($_POST['cidade']);
	$estado           = trim($_POST['estado']);
	$pais             = trim($_POST['pais']);
	$cep              = trim($_POST['cep']);
	$fone_residencial = trim($_POST['fone_residencial']);
	$fone_comercial   = trim($_POST['fone_comercial']);
	$cel              = trim($_POST['cel']);
	$fax              = trim($_POST['fax']);
	$email            = trim($_POST['email']);
	$nome_fantasia    = trim($_POST['nome_fantasia']);
	$ie               = trim($_POST['ie']);
	//PARA COMPRADOR ACESSAR ACESSAR PAGINA DE FORNECEDOR
	$login            = trim($_POST['login']);
	$url              = trim($_POST['url']);
	$senha_fornecedor = trim($_POST['senha_fornecedor']);	
	
	$cobranca_cep              = trim($_POST['cobranca_cep']);
	$cobranca_endereco         = trim($_POST['cobranca_endereco']);
	$cobranca_numero           = trim($_POST['cobranca_numero']);
	$cobranca_complemento      = trim($_POST['cobranca_complemento']);
	$cobranca_bairro           = trim($_POST['cobranca_bairro']);
	$cobranca_cidade           = trim($_POST['cobranca_cidade']);
	$cobranca_estado           = trim($_POST['cobranca_estado']);

	$entrega_cep              = trim($_POST['entrega_cep']);
	$entrega_endereco         = trim($_POST['entrega_endereco']);
	$entrega_numero           = trim($_POST['entrega_numero']);
	$entrega_complemento      = trim($_POST['entrega_complemento']);
	$entrega_bairro           = trim($_POST['entrega_bairro']);
	$entrega_cidade           = trim($_POST['entrega_cidade']);
	$entrega_estado           = trim($_POST['entrega_estado']);
	
	$ativo            = trim($_POST['ativo']);
	$pessoa_banco_ck    = trim($_POST['pessoa_banco_ck']);


	$pessoa_banco   = trim($_POST["pessoa_banco"]);
	$banco          = trim($_POST["banco"]);
	$agencia        = trim($_POST["agencia"]);
	$conta          = trim($_POST["conta"]);
	$tipo_conta     = trim($_POST["tipo_conta"]);
	$favorecido     = trim($_POST["favorecido"]);
	$observacao     = trim($_POST["observacao"]);

	
#	if(strlen($nome)  == 0 ) $msg_erro .= "Digite o nome<br>";
#	if(strlen($cnpj)  == 0 ) $msg_erro .= "Digite o CNPJ/CPF<br>";
#	if($tipo == 'fornecedor'){
#		if(strlen($email) == 0 ) $msg_erro .= "Digite o email<br>";
#	}
	$validate = new VALIDATE;

#	if (strlen($tipo_pessoa)==0){
#		$msg_erro .= "Selecione o tipo: F�sica ou Jur�dica";
#	}

	if ($tipo_pessoa=='F' AND strlen($cnpj)>0){
		if(!$validate->cpf($cnpj)) $msg_erro .= "CPF inv�lido!";
	}

	if ($tipo_pessoa=='J' AND strlen($cnpj)>0){
		if(!$validate->cnpj($cnpj)) $msg_erro .= "CNPJ inv�lido!";
	}

	if($tipo=="colaborador"){
		if(strlen($senha) == 0 ) $msg_erro .= "Por favor digite sua senha<br>";
		else                     $xsenha   = "'".$senha."'";
	}

	if($tipo=="fornecedor"){
		if(strlen($senha) == 0 ) $msg_erro .= "Por favor digite sua senha<br>";
		else                     $xsenha   = "'".$senha."'";
	}

	//INFORMA��ES GERAIS
	if(strlen($nome)                 > 0) $xnome                 = "'".$nome."'";
	else                                  $xnome                 = "null";
	if(strlen($cnpj)                 > 0) $xcnpj                 = "'".$cnpj."'";
	else                                  $xcnpj                 = "null";
	if(strlen($endereco)             > 0) $xendereco             = "'".$endereco."'";
	else                                  $xendereco             = "null";
	if(strlen($numero)               > 0) $xnumero               = "'".$numero."'";
	else                                  $xnumero               = "null";
	if(strlen($complemento)          > 0) $xcomplemento          = "'".$complemento."'";
	else                                  $xcomplemento          = "null";
	if(strlen($bairro)               > 0) $xbairro               = "'".$bairro."'";
	else                                  $xbairro               = "null";
	if(strlen($cidade)               > 0) $xcidade               = "'".$cidade."'";
	else                                  $xcidade               = "null";
	if(strlen($estado)               > 0) $xestado               = "'".$estado."'";
	else                                  $xestado               = "null";
	if(strlen($pais)                 > 0) $xpais                 = "'".$pais."'";
	else                                  $xpais                 = "null";
	if(strlen($cep)                  > 0) $xcep                  = "'".$cep."'";
	else                                  $xcep                  = "null";
/*	if ($estrangeiro=="SIM")              $xpais                 = "NULL";
	else                                  $xpais                 = "'BR'";
*/

	//ENDERE�OS DE COBRAN�A
	if(strlen($cobranca_cep)        > 0) $xcobranca_cep           = "'".$cobranca_cep."'";
	else                                 $xcobranca_cep           = "null";
	if(strlen($cobranca_endereco)   > 0) $xcobranca_endereco      = "'".$cobranca_endereco."'";
	else                                 $xcobranca_endereco      = "null";
	if(strlen($cobranca_numero)     > 0) $xcobranca_numero        = "'".$cobranca_numero."'";
	else                                 $xcobranca_numero        = "null";
	if(strlen($cobranca_complemento)> 0) $xcobranca_complemento   = "'".$cobranca_complemento."'";
	else                                 $xcobranca_complemento   = "null";
	if(strlen($cobranca_bairro)     > 0) $xcobranca_bairro        = "'".$cobranca_bairro."'";
	else                                 $xcobranca_bairro        = "null";
	if(strlen($cobranca_cidade)     > 0) $xcobranca_cidade        = "'".$cobranca_cidade."'";
	else                                 $xcobranca_cidade        = "null";
	if(strlen($cobranca_estado)     > 0) $xcobranca_estado        = "'".$cobranca_estado."'";
	else                                 $xcobranca_estado        = "null";

	//ENDERE�OS DE ENTREGA
	if(strlen($entrega_cep)       > 0)  $xentrega_cep           = "'".$entrega_cep."'";
	else                                $xentrega_cep           = "null";
	if(strlen($entrega_endereco)   > 0) $xentrega_endereco      = "'".$entrega_endereco."'";
	else                                $xentrega_endereco      = "null";
	if(strlen($entrega_numero)     > 0) $xentrega_numero        = "'".$entrega_numero."'";
	else                                $xentrega_numero        = "null";
	if(strlen($entrega_complemento)> 0) $xentrega_complemento   = "'".$entrega_complemento."'";
	else                                $xentrega_complemento   = "null";
	if(strlen($entrega_bairro)     > 0) $xentrega_bairro        = "'".$entrega_bairro."'";
	else                                $xentrega_bairro        = "null";
	if(strlen($entrega_cidade)     > 0) $xentrega_cidade        = "'".$entrega_cidade."'";
	else                                $xentrega_cidade        = "null";
	if(strlen($entrega_estado)     > 0) $xentrega_estado        = "'".$entrega_estado."'";
	else                                $xentrega_estado        = "null";

	//INFORMA��ES DE CONTATO
	if(strlen($fone_residencial)     > 0) $xfone_residencial     = "'".$fone_residencial."'";
	else                                  $xfone_residencial     = "null";
	if(strlen($fone_comercial)       > 0) $xfone_comercial       = "'".$fone_comercial."'";
	else                                  $xfone_comercial       = "null";
	if(strlen($cel)                  > 0) $xcel                  = "'".$cel."'";
	else                                  $xcel                  = "null";
	if(strlen($fax)                  > 0) $xfax                  = "'".$fax."'";
	else                                  $xfax                  = "null";
	if(strlen($email)                > 0) $xemail                = "'".$email."'";
	else                                  $xemail                = "null";

	//INFORMA��ES PARA fornecedor
	if(strlen($nome_fantasia)        > 0) $xnome_fantasia        = "'".$nome_fantasia."'";
	else                                  $xnome_fantasia        = "null";
	if(strlen($ie)                   > 0) $xie                   = "'".$ie."'";
	else                                  $xie                   = "null";
	if(strlen($login)                > 0) $xlogin                = "'".$login."'";
	else                                  $xlogin                = "null";
	if(strlen($senha_fornecedor)     > 0) $xsenha_fornecedor     = "'".$senha_fornecedor."'";
	else                                  $xsenha_fornecedor     = "null";
	if(strlen($url)                  > 0) $xurl                  = "'".$url."'";
	else                                  $xurl                   = "null";
	if(strlen($pessoa_banco_ck)      > 0) $xpessoa_banco_ck      = "TRUE";
	else                                  $xpessoa_banco_ck      = "FALSE";

	//INFORMA��ES DE EMPREGADO
	if(strlen($comissao_venda)       > 0) $xcomissao_venda       = "'".$comissao_venda."'";
	else                                  $xcomissao_venda       = "null";
	if(strlen($comissao_mao_de_obra) > 0) $xcomissao_mao_de_obra = "'".$comissao_mao_de_obra."'";
	else                                  $xcomissao_mao_de_obra = "null";
	if(strlen($desconto_limite)      > 0) $xdesconto_limite      = "'".$desconto_limite."'";
	else                                  $xdesconto_limite      = "null";
	if(strlen($ativo)                > 0) $xativo                = "TRUE";
	else                                  $xativo                = "FALSE";


	//DADOS BANCARIOS
	if(strlen($pessoa)               > 0) $xpessoa  = "'".$pessoa."'";
	else                                  $xpessoa  = "null";
	if(strlen($pessoa_banco)         > 0) $xpessoa_banco  = $pessoa_banco;
	else                                  $xpessoa_banco  = "null";
	if(strlen($banco)                > 0) $xbanco         = "'".$banco."'";
	else                                  $xbanco         = "null";
	if(strlen($agencia)              > 0) $xagencia       = "'".$agencia."'";
	else                                  $xagencia       = "null";
	if(strlen($conta)                > 0) $xconta         = "'".$conta."'";
	else                                  $xconta         = "null";
	if(strlen($tipo_conta)           > 0) $xtipo_conta    = "'".$tipo_conta."'";
	else                                  $xtipo_conta    = "null";
	if(strlen($favorecido)           > 0) $xfavorecido    = "'".$favorecido."'";
	else                                  $xfavorecido    = "null";
	if(strlen($observacao)           > 0) $xobservacao    = "'".$observacao."'";
	else                                  $xobservacao    = "null";


	$xcep = str_replace(".", "",$xcep);
	$xcep = str_replace("-", "",$xcep);
	$xcep = str_replace("/", "",$xcep);

	$xcnpj = str_replace(".", "",$xcnpj);
	$xcnpj = str_replace("-", "",$xcnpj);
	$xcnpj = str_replace("/", "",$xcnpj);


	if($login_empresa == 49){
		$posto = $_POST['posto'];
		if(strlen($cnpj) > 0 AND strlen($nome) == 0){
			$sql = "SELECT posto 
						FROM tbl_posto 
						JOIN tbl_posto_fabrica using(posto)
						WHERE fabrica = $login_empresa
						AND   tbl_posto.cnpj = '$cnpj' LIMIT 1; ";
			$res = pg_exec($con,$sql);
			if(pg_numrows($res) > 0){
				$posto = pg_result($res,0,0);
			}else{
				$sql = "SELECT posto 
						FROM tbl_posto 
						WHERE tbl_posto.cnpj = '$cnpj' LIMIT 1; ";
				$res = pg_exec($con,$sql);
				if(pg_numrows($res) > 0){
					$posto = pg_result($res,0,0);
					$msg_erro = "Posto N�o gravado. Complete as informa��es";
				}else{
					$msg_erro = "Posto n�o encontrado na base de dados";
				}
			}
		}
		if(strlen($posto) > 0 AND strlen($nome) > 0 AND strlen($msg_erro) == 0){
			$sql = "SELECT posto 
						FROM tbl_posto 
						JOIN tbl_posto_fabrica using(posto)
						WHERE fabrica = $login_empresa
						AND   tbl_posto.cnpj = '$cnpj' LIMIT 1; ";
			$res = pg_exec($con,$sql);
			if(pg_numrows($res) == 0){
				$msg_erro = "Gravado";
				$sql = "INSERT INTO tbl_posto_fabrica ( posto           ,
											fabrica         ,
											codigo_posto    ,
											senha           ,
											tipo_posto      ,
											login_provisorio,
											data_alteracao
									) VALUES (
											$posto             ,
											'$login_empresa'   ,
											'$cnpj'            ,
											'*'                ,
											'175'              ,
											't'                ,
											current_timestamp
							); ";
				$res = pg_exec($con, $sql);
			}else{
				$msg_erro = "Atualizado";
/*				$sql = "UPDATE tbl_posto_fabrica SET
							codigo_posto            = $codigo_posto           ,
							senha                   = $senha                  ,
							tipo_posto              = $tipo_posto             ,
							obs                     = $obs                    ,
							contato_endereco        = $endereco               ,
							contato_numero          = $numero                 ,
							contato_complemento     = $complemento            
						WHERE tbl_posto_fabrica.posto   = $posto
						AND   tbl_posto_fabrica.fabrica = $login_fabrica; ";
//				$res = pg_exec($con,$sql);
*/
			}
		}
		if(strlen($posto) == 0 AND strlen($nome) > 0 AND strlen($msg_erro) == 0){
			$msg_erro = "Verifica se tem novo PA. Caso n�o tenha grava novo posto";
		}
		//echo "$sql";

		if(strlen($posto) > 0){
			$sql = "SELECT tbl_posto.posto       ,
							tbl_posto.nome       ,
							tbl_posto.fantasia   ,
							tbl_posto.cidade     ,
							tbl_posto.numero     ,
							tbl_posto.endereco   ,
							tbl_posto.complemento,
							tbl_posto.bairro     ,
							tbl_posto.fone       ,
							tbl_posto.fax        ,
							tbl_posto.cep        ,
							tbl_posto.estado     
						FROM tbl_posto 
						WHERE  tbl_posto.cnpj = '$cnpj' LIMIT 1;";
			$res = pg_exec($con,$sql);
			if(pg_numrows($res) > 0){

				$posto             = pg_result($res,0,posto);
				$nome              = pg_result($res,0,nome);
				$nome_fantasia     = pg_result($res,0,fantasia);
				$endereco          = pg_result($res,0,endereco);
				$numero            = pg_result($res,0,numero);
				$cidade            = pg_result($res,0,cidade);
				$complemento       = pg_result($res,0,complemento);
				$bairro            = pg_result($res,0,bairro);
				$fone_residencial  = pg_result($res,0,fone);
				$fax               = pg_result($res,0,fax);
				$cep               = pg_result($res,0,cep);
				$estado            = pg_result($res,0,estado);
			}
		}
	}else{


	if (strlen ($msg_erro) == 0){
		$res = pg_exec ($con,"BEGIN TRANSACTION");

		//--=== Cadastro de Principal ============================================================================
		if (strlen($pessoa)==0){
			$sql = "SELECT pessoa 
				FROM tbl_pessoa 
				WHERE cnpj = $cnpj 
				AND empresa = $login_empresa";
			
			$res = pg_exec ($con,$sql);

			if(pg_numrows($res)>0){
				$pessoa = trim(pg_result($res,0,pessoa));
				$pessoa = $pessoa;
				$msg_erro = "CPF/CNPJ j� cadastrado. Clique em gravar para adiciona-lo como $tipo";
				$btn_acao = "alterar";
			}else{
				$sql = "INSERT INTO tbl_pessoa (
						nome             ,
						cnpj             ,
						endereco         ,
						numero           ,
						complemento      ,
						bairro           ,
						cidade           ,
						estado           ,
						pais             ,
						cep              ,
						fone_residencial ,
						fone_comercial   ,
						cel              ,
						fax              ,
						email            ,
						nome_fantasia    ,
						ie               ,
						empresa          ,
						tipo             ";
						if($tipo=='fornecedor'){
							$sql .= ", senha";
						}
						$sql.="
						)VALUES (
						$xnome            ,
						$xcnpj            ,
						$xendereco        ,
						$xnumero          ,
						$xcomplemento     ,
						$xbairro          ,
						$xcidade          ,
						$xestado          ,
						$xpais            ,
						$xcep             ,
						$xfone_residencial,
						$xfone_comercial  ,
						$xcel             ,
						$xfax             ,
						$xemail           ,
						$xnome_fantasia   ,
						$xie              ,
						$login_empresa    ,
						'$tipo_pessoa'    ";
						if($tipo=='fornecedor'){
							$sql .= ", $xsenha";
						}
						$sql.=")";
				$res = @pg_exec ($con,$sql);
				$msg_erro = pg_errormessage($con);
				$res    = pg_exec ($con,"SELECT CURRVAL ('tbl_pessoa_pessoa_seq')");
				$pessoa = pg_result ($res,0,0);
			}
		}else{
			$sql = "UPDATE tbl_pessoa SET
					nome             = $xnome,
					cnpj             = $xcnpj,
					endereco         = $xendereco,
					numero           = $xnumero,
					complemento      = $xcomplemento,
					bairro           = $xbairro,
					cidade           = $xcidade,
					estado           = $xestado,
					pais             = $xpais  ,
					cep              = $xcep   ,
					fone_residencial = $xfone_residencial,
					fone_comercial   = $xfone_comercial ,
					cel              = $xcel   ,
					fax              = $xfax   ,
					email            = $xemail,
					nome_fantasia    = $xnome_fantasia,
					ie               = $xie,
					tipo             = '$tipo_pessoa'";
					if($tipo=='fornecedor'){
						$sql .= ", senha = $xsenha";
					}
					$sql .="WHERE pessoa = $pessoa ";
			$res = @pg_exec ($con,$sql);
			$msg_erro .= pg_errormessage($con);
		}

	/**************************************************************************
	************************* insert dados bancarios **************************/
		if(strlen($msg_erro)==0){

			$sqlB = "SELECT nome FROM tbl_banco WHERE codigo = '$banco'";
			$resB = pg_exec($con,$sqlB);
			if (pg_numrows($resB) == 1) $xnomebanco = "'" . trim(pg_result($resB,0,0)) . "'";
			else                        $xnomebanco = "null";

			if(strlen($pessoa) == 0 OR strlen($pessoa_banco) == 0){
				$sql = "INSERT INTO tbl_pessoa_banco (
							pessoa    ,
							banco     ,
							nome      ,
							agencia   ,
							conta     ,
							favorecido,
							tipo_conta,
							observacao
					)VALUES(
						$xpessoa      ,
						$xbanco       ,
						$xnomebanco   ,
						$xagencia     ,
						$xconta       ,
						$xfavorecido  ,
						$xtipo_conta  ,
						$xobservacao
					)";
					$res = pg_exec ($con,$sql);

					$res    = pg_exec ($con,"select currval('tbl_pessoa_banco_pessoa_banco_seq') as pessoa_banco");
					$pessoa_banco = pg_result ($res,0,0);
					$msg_erro = pg_errormessage($con);
					
					if(strlen($msg_erro) == 0){
						$pessoa_banco = trim(pg_result($res,0,pessoa_banco));
					}
				}else{
					if(strlen($pessoa_banco) > 0){
						$sql = "UPDATE tbl_pessoa_banco SET
									banco      = $xbanco      ,
									nome       = $xnomebanco  ,
									agencia    = $xagencia    ,
									conta      = $xconta      ,
									favorecido = $xfavorecido ,
									tipo_conta = $xtipo_conta ,
									observacao = $xobservacao
								WHERE pessoa_banco = $pessoa_banco
								AND   pessoa       = $pessoa";
						$res = pg_exec ($con,$sql);
						$msg_erro = pg_errormessage($con);
					}
				}
		}

		if(strlen($_GET["pessoa"])       >0) $pessoa       = $_GET["pessoa"];

		if(strlen($pessoa)>0){
			$sql = "SELECT nome FROM tbl_pessoa WHERE pessoa = $pessoa";
			$res = pg_exec($sql);
			if(pg_numrows($res)>0){
				$pessoa_nome = pg_result($res,0,0);
			}
		}

		//--=== Cadastro de fornecedor =========================================================================
		if($tipo == "fornecedor" ){

			if($xpessoa_banco_ck=='TRUE'){
				$xpessoa_banco_ck = $pessoa_banco;
			}else{
				$xpessoa_banco_ck = $pessoa_banco_f;
			}

			if(strlen($pessoa) == 0 or strlen($xpessoa_banco_ck) > 0){
				$sql = "INSERT INTO tbl_pessoa_fornecedor (
						pessoa       ,
						empresa      ,
						login        ,
						senha        ,
						url          ,
						pessoa_banco ,
						ativo
					)VALUES(
						$pessoa            ,
						$login_empresa     ,
						$xlogin            ,
						$xsenha_fornecedor ,
						$xurl              ,
						$xpessoa_banco_ck  ,
						$xativo
					)";
					//	echo $sql;
				$res = pg_exec ($con,$sql);
				$msg_erro .= pg_errormessage($con);
			}else{
					$sql = "UPDATE tbl_pessoa_fornecedor SET
								login         = $xlogin           ,
								senha         = $xsenha_fornecedor,
								url           = $xurl             ,";
					if(strlen($xpessoa_banco_ck) > 0){

						$sql .= "pessoa_banco  = $xpessoa_banco_ck,";
					}
						$sql .= " ativo = $xativo
							WHERE empresa = $login_empresa AND pessoa = $pessoa";
					$res = pg_exec ($con,$sql);
					$msg_erro = pg_errormessage($con);
				}
			}

		//--=== Cadastro de Cliente ==============================================================================
		if($tipo == "cliente"){
			$sql = "SELECT pessoa FROM tbl_pessoa_cliente WHERE pessoa = $pessoa";
			$res = pg_exec ($con,$sql);
			if(pg_numrows($res) == 0){
				$sql  = "INSERT INTO tbl_pessoa_cliente (
						pessoa  ,
						empresa ,
						ativo
					)VALUES(
						$pessoa       ,
						$login_empresa,
						$xativo
					)";
				$res = pg_exec ($con,$sql);
			}
		}

		//--=== Cadastro de Empregado ============================================================================
		if($tipo == "colaborador"){
			$sql = "SELECT empregado FROM tbl_empregado WHERE pessoa = $pessoa";
			$res = pg_exec ($con,$sql);
			if(pg_numrows($res) > 0) $empregado = trim(pg_result($res,0,empregado));

			if(strlen($empregado) == 0 ){
				$sql = "INSERT INTO tbl_empregado(
						senha                 ,
						comissao_venda        ,
						comissao_mao_de_obra  ,
						desconto_limite       ,
						ativo                 ,
						loja                  ,
						pessoa                ,
						privilegios           ,
						empresa
					)VALUES(
						$xsenha                ,
						$xcomissao_venda       ,
						$xcomissao_mao_de_obra ,
						$xdesconto_limite      ,
						$xativo                ,
						$login_loja            ,
						$pessoa                ,
						'*'                    ,
						$login_empresa
					)";
			}else{
				$sql = "UPDATE tbl_empregado SET
						senha                = $xsenha               ,
						comissao_venda       = $xcomissao_venda      ,
						comissao_mao_de_obra = $xcomissao_mao_de_obra,
						desconto_limite      = $xdesconto_limite     ,
						ativo                = $xativo               ,
						loja                 = $login_loja
					WHERE empregado       = $empregado
					AND   pessoa          = $pessoa
					AND   empresa         = $login_empresa";
			}
			$res = pg_exec ($con,$sql);
			$msg_erro .= pg_errormessage($con);
		}

		//--=== ENDERECO DE ENTREGA ====================================================
		$sql = "SELECT pessoa_endereco FROM tbl_pessoa_endereco WHERE pessoa = $pessoa AND tipo='E'";
		$res = pg_exec ($con,$sql);
		if(pg_numrows($res) > 0) $pessoa_endereco = trim(pg_result($res,0,pessoa_endereco));
			if(strlen($pessoa_endereco) == 0 ){
			$sql = "INSERT INTO tbl_pessoa_endereco (
						pessoa        ,
						cep           ,
						endereco      ,
						numero        ,
						complemento   ,
						bairro        ,
						cidade        ,
						estado        ,
						tipo          
					)VALUES(
						$pessoa,
						$xentrega_cep          ,
						$xentrega_endereco     ,
						$xentrega_numero       ,
						$xentrega_complemento  ,
						$xentrega_bairro       ,
						$xentrega_cidade       ,
						$xentrega_estado       ,
						'E'                   
					)";
		}else{
			$sql = "UPDATE tbl_pessoa_endereco
					SET
					cep           = $xentrega_cep        ,
					endereco      = $xentrega_endereco   ,
					numero        = $xentrega_numero     ,
					complemento   = $xentrega_complemento,
					bairro        = $xentrega_bairro     ,
					cidade        = $xentrega_cidade     ,
					estado        = $xentrega_estado
					WHERE pessoa_endereco  = $pessoa_endereco";
		}
		$res = pg_exec ($con,$sql);
		$msg_erro .= pg_errormessage($con);

		//--=== ENDERECO DE COBRAN�A ====================================================
		$sql = "SELECT pessoa_endereco FROM tbl_pessoa_endereco WHERE pessoa = $pessoa AND tipo='C'";
		$res = pg_exec ($con,$sql);
		if(pg_numrows($res) > 0) $pessoa_endereco = trim(pg_result($res,0,pessoa_endereco));
			if(strlen($pessoa_endereco) == 0 ){
			$sql = "INSERT INTO tbl_pessoa_endereco (
						pessoa        ,
						cep           ,
						endereco      ,
						numero        ,
						complemento   ,
						bairro        ,
						cidade        ,
						estado        ,
						tipo          
					)VALUES(
						$pessoa,
						$xcobranca_cep          ,
						$xcobranca_endereco     ,
						$xcobranca_numero       ,
						$xcobranca_complemento  ,
						$xcobranca_bairro       ,
						$xcobranca_cidade       ,
						$xcobranca_estado       ,
						'C'                   
					)";
		}else{
			$sql = "UPDATE tbl_pessoa_endereco
					SET
					cep           = $xcobranca_cep        ,
					endereco      = $xcobranca_endereco   ,
					numero        = $xcobranca_numero     ,
					complemento   = $xcobranca_complemento,
					bairro        = $xcobranca_bairro     ,
					cidade        = $xcobranca_cidade     ,
					estado        = $xentrega_estado
					WHERE pessoa_endereco  = $pessoa_endereco";
		}
		$res = pg_exec ($con,$sql);
		$msg_erro .= pg_errormessage($con);


		$sql = "DELETE FROM tbl_pessoa_referencia WHERE pessoa=$pessoa";
//		$res = pg_exec ($con,$sql);
/*
		$numero_referencias = 20;
		for ($i=0;$i<$numero_referencias;$i++){
			$ref_nome = trim($_POST["pessoa_referencia_nome_$i"]);
			$ref_tel  = trim($_POST["pessoa_referencia_tel_$i"]);
			$ref_tipo = trim($_POST["pessoa_referencia_tipo_$i"]);
			
			if (!isset($_POST["pessoa_referencia_nome_$i"])) continue;
			if (strlen($ref_nome)==0) continue;

			$ref_nome = "'$ref_nome'";
			$ref_tel  = "'$ref_tel'";
			$ref_tipo = "'$ref_tipo'";

			$sql = "INSERT INTO tbl_pessoa_referencia (
					pessoa,
					nome,
					telefone,
					tipo
					) VALUES (
					$pessoa,
					$ref_nome,
					$ref_tel,
					$ref_tipo
					)";
//			$res = pg_exec ($con,$sql);
			$msg_erro .= pg_errormessage($con);
		}*/
	}
	}
	/*********************Insert Comprovante***********************************
	***************************************************************************/
		if (strlen($pessoa)>0){
			//$sql = "DELETE FROM tbl_comprovante WHERE pessoa=$pessoa";
			//$res = pg_exec ($con,$sql);
		}

		$numero_comprovante = 0;
		for ($i=0;$i<$numero_comprovante;$i++){
			$comp_tipo = trim($_POST["pessoa_comprovante_tipo_$i"]);
			$comp_desc = trim($_POST["pessoa_comprovante_desc_$i"]);
			$comp_valor= trim($_POST["pessoa_comprovante_valor_$i"]);
			$comp_xrg  = trim($_POST["pessoa_comprovante_xrg_$i"]);
			$comp_xcpf = trim($_POST["pessoa_comprovante_xcpf_$i"]);
			$comp_xres = trim($_POST["pessoa_comprovante_xres_$i"]);
			$comp_data = trim($_POST["pessoa_comprovante_data_$i"]);

			if (!isset($_POST["pessoa_comprovante_tipo_$i"])) continue;
			if (strlen($comp_tipo)==0) continue;

			$comp_tipo  = "'$comp_tipo'";
			$comp_desc = "'$comp_desc'";
			$comp_valor = "'$comp_valor'";
			/*$comp_xrg = "'$comp_xrg'";
			$comp_xcpf = "'$comp_xcpf'";
			$comp_xres = "'$comp_xres'";*/
			$comp_data = "'$comp_data'";

			if ($comp_xrg=='true'){
				$comp_xrg="'t'";
			}
			else{
				$comp_xrg="'f'";
			}

			if ($comp_xcpf=='true'){
				$comp_xcpf="'t'";
			}
			else{
				$comp_xcpf="'f'";
			}

			if ($comp_xres=='true'){
				$comp_xres="'t'";
			}
			else{
				$comp_xres="'f'";
			}

			$sql = "INSERT INTO tbl_comprovante (
					pessoa,
					tipo,
					descricao,
					valor,
					xerox_rg,
					xerox_cpf,
					xerox_residencia,
					data_digitacao
					) VALUES (
					$pessoa,
					$comp_tipo,
					$comp_desc,
					$comp_valor,
					$comp_xrg,
					$comp_xcpf,
					$comp_xres,
					current_date
					)";
			$res = pg_exec ($con,$sql); 

			$msg_erro .= pg_errormessage($con);
		}

		if (strlen ($msg_erro) == 0) {
			$res = pg_exec ($con,"COMMIT TRANSACTION");
			$msg = "$tipo gravado com sucesso!";
			//header ("Location: $PHP_SELF?tipo=$tipo&ok=1");
			
		}else{
			$res = pg_exec ($con,"ROLLBACK TRANSACTION");
		}
	}
	/**********************fim insert comprovante******************************
	***************************************************************************/



	/********** insert fornecedor-permiss�o linha/familia/modelo/marca ********
	***************************************************************************/
		if(strlen($_POST["enviar"])>0 and strlen($pessoa)>0) {
			$linha	 = $_POST["linha"  ];
			$familia = $_POST["familia"];
			$modelo	 = $_POST["modelo" ];
			$marca	 = $_POST["marca"  ];
			$pessoa	 = $_POST["pessoa" ];

		//LINHA
		if(strlen($linha)>0) {
			$sql= " SELECT linha, nome
					FROM tbl_fornecedor_linha 
					JOIN tbl_linha  using(linha) 
					WHERE tbl_fornecedor_linha.empresa= $login_empresa
						and pessoa_fornecedor=$pessoa
						and linha = $linha;";
			$res= pg_exec($con, $sql);

			if(pg_numrows($res)>0){
				echo "ja est� cadastrado";
			}else{
				$sql= " INSERT INTO tbl_fornecedor_linha (empresa, pessoa_fornecedor, linha)
						VALUES($login_empresa, $pessoa, $linha);";
						//echo "sql: $sql";
				$res= pg_exec($con, $sql);
			}
		}

		//FAMILIA
		if(strlen($familia)>0) {
			$sql= " SELECT familia, descricao
					FROM tbl_fornecedor_familia 
					JOIN tbl_familia  using(familia) 
					WHERE tbl_fornecedor_familia.empresa = $login_empresa
						and pessoa_fornecedor=$pessoa
						and familia = $familia;";
			$res= pg_exec($con, $sql);

			if(pg_numrows($res)>0){
				echo "ja est� cadastrado";
			}else{
				$sql= " INSERT INTO tbl_fornecedor_familia (empresa, pessoa_fornecedor, familia)
						VALUES($login_empresa, $pessoa, $familia);";
				$res= pg_exec($con, $sql);
			}
		}

		//MODELO
		if(strlen($modelo)>0) {
			$sql= " SELECT modelo, nome
					FROM tbl_fornecedor_modelo 
					JOIN tbl_modelo  using(modelo) 
					WHERE tbl_fornecedor_modelo.empresa = $login_empresa
						and pessoa_fornecedor=$pessoa
						and modelo = $modelo;";
			$res= pg_exec($con, $sql);

			if(pg_numrows($res)>0){
				echo "ja est� cadastrado";
			}else{
				$sql= " INSERT INTO tbl_fornecedor_modelo (empresa, pessoa_fornecedor, modelo)
						VALUES($login_empresa, $pessoa, $modelo);";
				$res= pg_exec($con, $sql);
			}
		}

		//MARCA
		if(strlen($marca)>0) {
			$sql= " SELECT marca, nome
					FROM tbl_fornecedor_marca 
					JOIN tbl_marca  using(marca) 
					WHERE tbl_fornecedor_marca.empresa = $login_empresa
						and pessoa_fornecedor=$pessoa
						and marca = $marca;";

			$res= pg_exec($con, $sql);

			if(pg_numrows($res)>0){
				echo "ja est� cadastrado";
			}else{
				$sql= " INSERT INTO tbl_fornecedor_marca (empresa, pessoa_fornecedor, marca)
						VALUES($login_empresa, $pessoa, $marca);";
				$res= pg_exec($con, $sql);
			}
	}
	}
	/******************** fim insert fornecedor-permiss�o *********************
	***************************************************************************/

## VERIFICAO DE CNPJ E CPF

	if ($tipo=="fornecedor"){
		$CPF_CNPJ = "CNPJ";
	}elseif ($tipo=="cliente"){
		$CPF_CNPJ = "CPF";
	}else{
		$CPF_CNPJ = "CPF";
	}

## PESQUISA SIMPLES

# campo padr�o para pesquisa
$campo_pesquisa = "nome";

if ($btn_acao=='pesquisar_simples'){
	
	$busca_por        = trim($_POST['busca_por']);
	$campo_pesquisa   = trim($_POST['campo_pesquisa']);

	$sql_adicional_join = "";

	if    ($tipo == "cliente")     $sql_adicional_join = " JOIN tbl_pessoa_cliente USING(pessoa) ";
	elseif($tipo == "fornecedor")  $sql_adicional_join = " JOIN tbl_pessoa_fornecedor USING(pessoa) ";
	else                           $sql_adicional_join = " JOIN tbl_empregado USING(pessoa) ";
	
	$sql_adicional = "";

	if (strlen($busca_por)==0) $msg_erro  .= "Preencha o campo para a pesquisa";
	
	if ($campo_pesquisa=='codigo')
		$sql_adicional .= "AND UPPER(tbl_pessoa.pessoa) like UPPER('%$busca_por%')";

	if ($campo_pesquisa=='nome')
		$sql_adicional .= "AND UPPER(tbl_pessoa.nome) like UPPER('%$busca_por%')";

	if ($campo_pesquisa=='cpf')
		$sql_adicional .= "AND tbl_pessoa.cnpj like UPPER('%$busca_por%')";

	if ($campo_pesquisa=='endereco')
		$sql_adicional .= "AND UPPER(tbl_pessoa.endereco) like UPPER('%$busca_por%')";

	if ($campo_pesquisa=='telefone')
		$sql_adicional .= "AND ( tbl_pessoa.fone_residencial like '%$busca_por%' OR tbl_pessoa.fone_comercial like '%$busca_por%' OR tbl_pessoa.cel like '%$busca_por%' OR tbl_pessoa.fax like '%$busca_por%' )";

	if ($campo_pesquisa=='email')
		$sql_adicional .= "AND UPPER(tbl_pessoa.email) like '%$busca_por%'";

}

## PESQUISA SIMPLES
if ($btn_acao=='pesquisar'){

	$nome_busca            = trim($_POST["nome_busca"]);
	$cnpj_busca            = trim($_POST["cnpj_busca"]);
	$email_busca           = trim($_POST["email_busca"]);

	$sql_adicional = "";
	if(strlen($nome_busca) > 0) $sql_adicional .= " AND UPPER(tbl_pessoa.nome) ILIKE UPPER('%$nome_busca%') ";
	if(strlen($cnpj_busca) > 0) $sql_adicional .= " AND UPPER(tbl_pessoa.cnpj) ILIKE UPPER('%$cnpj_busca%') ";
	if(strlen($email_busca)> 0) $sql_adicional .= " AND UPPER(tbl_pessoa.email) ILIKE UPPER('%$email_busca%') ";

	$sql_adicional_join = "";
	if    ($tipo == "cliente")     $$sql_adicional_join = " JOIN tbl_pessoa_cliente USING(pessoa) ";
	elseif($tipo == "fornecedor")  $sql_adicional_join = " JOIN tbl_pessoa_fornecedor USING(pessoa) ";
	else                           $sql_adicional_join = " JOIN tbl_empregado USING(pessoa) ";
	
}

if (strlen($pessoa)>0) {
	$sql = "SELECT  tbl_pessoa.pessoa  ,
			tbl_pessoa.nome            ,
			tbl_pessoa.cnpj            ,
			tbl_pessoa.ie              ,
			tbl_pessoa.nome_fantasia   ,
			tbl_pessoa.endereco        ,
			tbl_pessoa.numero          ,
			tbl_pessoa.complemento     ,
			tbl_pessoa.bairro          ,
			tbl_pessoa.cidade          ,
			tbl_pessoa.estado          ,
			tbl_pessoa.pais            ,
			tbl_pessoa.cep             ,
			tbl_pessoa.fone_residencial,
			tbl_pessoa.fone_comercial  ,
			tbl_pessoa.cel             ,
			tbl_pessoa.fax             ,
			tbl_pessoa.email           ,
			tbl_pessoa.tipo            ,
			tbl_pessoa.senha
		FROM tbl_pessoa
		WHERE tbl_pessoa.empresa = $login_empresa
		AND pessoa = $pessoa";



	$res = pg_exec ($con,$sql) ;
	$pessoa           = trim(pg_result($res,0,pessoa));
	$nome             = trim(pg_result($res,0,nome));
	$cnpj             = trim(pg_result($res,0,cnpj));
	$endereco         = trim(pg_result($res,0,endereco));
	$numero           = trim(pg_result($res,0,numero));
	$complemento      = trim(pg_result($res,0,complemento));
	$bairro           = trim(pg_result($res,0,bairro));
	$cidade           = trim(pg_result($res,0,cidade));
	$estado           = trim(pg_result($res,0,estado));
	$pais             = trim(pg_result($res,0,pais));
	$cep              = trim(pg_result($res,0,cep));
	$fone_residencial = trim(pg_result($res,0,fone_residencial));
	$fone_comercial   = trim(pg_result($res,0,fone_comercial));
	$cel              = trim(pg_result($res,0,cel));
	$fax              = trim(pg_result($res,0,fax));
	$email            = trim(pg_result($res,0,email));
	$ie               = trim(pg_result($res,0,ie));
	$nome_fantasia    = trim(pg_result($res,0,nome_fantasia));
	$tipo_pessoa      = trim(pg_result($res,0,tipo));
	$senha            = trim(pg_result($res,0,senha));

	if ($tipo_pessoa=="F"){
		$CPF_CNPJ = "CPF";
	}elseif ($tipo_pessoa=="J"){
		$CPF_CNPJ = "CNPJ";
	}else{
		if ($tipo=='cliente'){
			$CPF_CNPJ = "CPF";
		}else{
			$CPF_CNPJ = "CNPJ";
		}
	}

	if($tipo == "colaborador"){
		$sql = "SELECT senha                ,
				comissao_venda      ,
				comissao_mao_de_obra,
				desconto_limite     ,
				ativo 
			FROM  tbl_empregado
			WHERE pessoa = $pessoa";
		$res = pg_exec ($con,$sql) ;
		if(pg_numrows($res)>0){
			$desconto_limite      = trim(pg_result($res,0,desconto_limite));
			$comissao_venda       = trim(pg_result($res,0,comissao_venda));
			$comissao_mao_de_obra = trim(pg_result($res,0,comissao_mao_de_obra));
			$senha                = trim(pg_result($res,0,senha));
			$ativo                = trim(pg_result($res,0,ativo));
		}
	}

### Pega endereco de cobran�a
	$sql = "SELECT 
				cep          ,
				endereco     ,
				numero       ,
				complemento  ,
				bairro       ,
				cidade       ,
				estado       ,
				contato      
			FROM  tbl_pessoa_endereco
			WHERE pessoa = $pessoa AND tipo='C'";
	$res = pg_exec ($con,$sql) ;
	if (pg_numrows($res)>0){
		$cobranca_cep         = trim(pg_result($res,0,cep));
		$cobranca_endereco    = trim(pg_result($res,0,endereco));
		$cobranca_numero      = trim(pg_result($res,0,numero));
		$cobranca_complemento = trim(pg_result($res,0,complemento));
		$cobranca_bairro      = trim(pg_result($res,0,bairro));
		$cobranca_cidade      = trim(pg_result($res,0,cidade));
		$cobranca_estado      = trim(pg_result($res,0,estado));
		$cobranca_contato     = trim(pg_result($res,0,contato));
	}

### Pega endereco de entrega
	$sql = "SELECT 
				cep          ,
				endereco     ,
				numero       ,
				complemento  ,
				bairro       ,
				cidade       ,
				estado       ,
				contato      
			FROM  tbl_pessoa_endereco
			WHERE pessoa = $pessoa AND tipo='E'";
	$res = pg_exec ($con,$sql) ;
	if (pg_numrows($res)>0){
		$entrega_cep         = trim(pg_result($res,0,cep));
		$entrega_endereco    = trim(pg_result($res,0,endereco));
		$entrega_numero      = trim(pg_result($res,0,numero));
		$entrega_complemento = trim(pg_result($res,0,complemento));
		$entrega_bairro      = trim(pg_result($res,0,bairro));
		$entrega_cidade	      = trim(pg_result($res,0,cidade));
		$entrega_estado      = trim(pg_result($res,0,estado));
		$entrega_contato     = trim(pg_result($res,0,contato));
	}


### REFERENCIAS
	$sql = "SELECT 
				nome,
				telefone,
				tipo
			FROM  tbl_pessoa_referencia
			WHERE pessoa = $pessoa";
/*	$res = pg_exec ($con,$sql) ;
	$array_referencias = array();
	if (pg_numrows($res)>0){
		for ($i=0;$i<pg_numrows($res);$i++){
			$ref_entregnome_cep  = trim(pg_result($res,$i,nome));
			$ref_telefone        = trim(pg_result($res,$i,telefone));
			$ref_tipo            = trim(pg_result($res,$i,tipo));
			$ref_aux = $ref_entregnome_cep."|".$ref_telefone."|".$ref_tipo;
			array_push($array_referencias,explode("|",$ref_aux));
		}
	}*/
}
/*
if(strlen($pessoa)>0){
### COMPROVANTE
	$sql = "SELECT 
				tipo,
				descricao,
				valor,
				xerox_rg,
				xerox_cpf,
				xerox_residencia,
				data_digitacao
			FROM  tbl_comprovante
			WHERE pessoa = $pessoa";
				echo $sql;
	$res = pg_exec ($con,$sql) ;
	$array_comprovante = array();
	if (pg_numrows($res)>0){
		for ($i=0;$i<pg_numrows($res);$i++){
			$comp_tipo            = trim(pg_result($res,$i,tipo));
			$comp_descricao       = trim(pg_result($res,$i,descricao));
			$comp_valor           = trim(pg_result($res,$i,valor));
			$comp_xrg             = trim(pg_result($res,$i,xerox_rg));
			$comp_xcpf            = trim(pg_result($res,$i,xerox_cpf));
			$comp_xres            = trim(pg_result($res,$i,xerox_residencia));
			$comp_data            = trim(pg_result($res,$i,data_digitacao));
			$comp_aux = $comp_tipo."|".$comp_descricao."|".$comp_valor."|".$comp_xrg."|".$comp_xcpf."|".$comp_xres."|".$comp_data;
			array_push($array_comprovante,explode("|",$comp_aux));

		}
	}
}
*/
// select dados bancarios
if(strlen($pessoa_banco)>0){
$sql = "SELECT  pessoa_banco,
					banco       ,
					nome        ,
					agencia     ,
					conta       ,
					tipo_conta  ,
					favorecido  ,
					observacao
			FROM tbl_pessoa_banco 
			WHERE pessoa = $pessoa AND pessoa_banco = $pessoa_banco";
	$res = pg_exec ($con,$sql);

	if (pg_numrows($res) > 0) {
		for($i=0; $i < pg_numrows($res); $i++){
			$pessoa_banco_b    = trim(pg_result($res,$i,pessoa_banco));
			$banco             = trim(pg_result($res,$i,banco));
			$nome_banco        = trim(pg_result($res,$i,nome));
			$agencia           = trim(pg_result($res,$i,agencia));
			$conta             = trim(pg_result($res,$i,conta));
			$tipo_conta        = trim(pg_result($res,$i,tipo_conta));
			$favorecido        = trim(pg_result($res,$i,favorecido));
			$observacao        = trim(pg_result($res,$i,observacao));
		}
	}
}

// select pessoa fornecedor
if(strlen($pessoa)>0){
$sql = "SELECT  login            ,
				senha            ,
				url              ,
				pessoa_banco     ,
				ativo
			FROM tbl_pessoa_fornecedor 
			WHERE pessoa = $pessoa";
	$res = pg_exec ($con,$sql);
	if (pg_numrows($res) > 0) {
		$login             = trim(pg_result($res,0,login));
		$senha_fornecedor  = trim(pg_result($res,0,senha));
		$url               = trim(pg_result($res,0,url));
		$ativo             = trim(pg_result($res,0,ativo));
		$pessoa_banco_f    = trim(pg_result($res,0,pessoa_banco));
	}
}

include "menu.php";
//ACESSO RESTRITO AO USUARIO
if (strpos ($login_privilegios,'cadastros') === false AND strpos ($login_privilegios,'*') === false ) {
		echo "<script>"; 
			echo "window.location.href = 'menu_inicial.php?msg_erro=Voc� n�o tem permiss�o para acessar a tela.'";
		echo "</script>";
	exit;
}

?>



<? include "javascript_pesquisas.php" ?>

<script language="JavaScript">

function checarNumero(campo){
	var num = campo.value.replace(",",".");
	campo.value = parseFloat(num).toFixed(2);
	if (campo.value=='NaN') {
		campo.value='';
	}
}
function limpar_form(formu){
	for( var i = 0 ; i < formu.length; i++ ){
		if (formu.elements[i].type !='button' && formu.elements[i].type !='submit'){
			if(formu.elements[i].type=='checkbox'){
				formu.elements[i].checked=false;
			}else{
				formu.elements[i].value='';
			}
		}
	}
}
</script>

<!--========================= AJAX ==================================.-->
<? include "javascript_pesquisas.php" ?>

<style>
a{
	font-family: Verdana;
	font-size: 10px;
	font-weight: bold;
	color:#3399FF;
}
.Label{
	font-family: Verdana;
	font-size: 10px;
}
.tabela{
	font-family: Verdana;
	font-size: 12px;
}
.tabela_reduzida{
	font-size: 10px;
}


.Titulo_Tabela{
	font-family: Verdana;
	font-size: 12px;
	font-weight: bold;
	color:#FFF;
}
.Titulo_Colunas{
	font-family: Verdana;
	font-size: 12px;
	font-weight: bold;
}
.Erro{
	font-family: Verdana;
	font-size: 12px;
	color:#FFF;
	border:#485989 1px solid; background-color: #990000;
}

img{
	border:0;
}


caption{
	BACKGROUND-COLOR: #FFF;
	font-size:12px;
	font-weight:bold;
	text-align:center;
}
.Titulo_Tabela_Menor{
	background-color:#FFF0D2;
	border-bottom:1px solid #FFDE9B;
	font-weight:bold;
	font-size:10px;
}
tr.linha td {
	border-bottom: 1px solid #EDEDE9; 
	border-top: none; 
	border-right: none; 
	border-left: none; 
}
.ok{
	font-family: Verdana;
	font-size: 12px;
	color:blue;
	border:#39AED5 1px solid; background-color: #B0DFEE;
}

</style>
<script language='javascript' src='../ajax.js'></script>
<script language='javascript' src='../ajax_cep.js'></script>

<script type="text/javascript">
	$(function() {
		$('#container-Principal').tabs( {} );
		$('#container-Dentro').tabs( {} );
		$('#container-Dentro').disableTab(4);
		$('#container-Dentro').disableTab(5);
	});
</script>
<script type="text/javascript">
$(document).ready(
	function()
	{
		//$("a").ToolTipDemo("#FDFAC4", "#645C00");
		//$("#busca_por").focus();
	}
	);
</script>

<script type="text/javascript">
	jQuery(function($){
		$("#fone_residencial").maskedinput("(99) 9999-9999");
		$("#fone_comercial").maskedinput("(99) 9999-9999");
		$("#cel").maskedinput("(99) 9999-9999");
		$("#fax").maskedinput("(99) 9999-9999");
		$("#ref_pe_tel").maskedinput("(99) 9999-9999");
		$("#instalacao").maskedinput("99/99/9999");
		$("#data_inicio").maskedinput("99/99/9999");
/*		$("#cel").maskedinput("99-9999999");
		$("#ssn").maskedinput("999-99-9999");
		$("#product").maskedinput("a*-999-a999",{placeholder:" ",completed:function(input){alert("You typed the following: "+input.val());}});
	*/
	});
</script>

<script type="text/javascript">
	function adicionaLinhaReferencia() {

		if(document.getElementById('ref_pe_tipo').value=="") { alert('Selecione tipo');   return false}
		if(document.getElementById('ref_pe_nome').value=="") { alert('Informe o nome');   return false}
		if(document.getElementById('ref_pe_tel').value=="")           { alert('Informe telefone'); return false}

		var tbl = document.getElementById('tbl_referencias');
		var lastRow = tbl.rows.length;
		var iteration = lastRow;

		var linha = document.createElement('tr');
		linha.style.cssText = 'color: #000000; text-align: left; font-size:10px';

		var celula = criaCelula(document.getElementById('ref_pe_nome').value);
		celula.style.cssText = 'text-align: left; color: #000000;font-size:10px';

		var el = document.createElement('input');
		el.setAttribute('type', 'hidden');
		el.setAttribute('name', 'pessoa_referencia_nome_' + iteration);
		el.setAttribute('id', 'pessoa_referencia_nome_' + iteration);
		el.setAttribute('value',document.getElementById('ref_pe_nome').value);
		celula.appendChild(el);

		var el = document.createElement('input');
		el.setAttribute('type', 'hidden');
		el.setAttribute('name', 'pessoa_referencia_tel_' + iteration);
		el.setAttribute('id', 'pessoa_referencia_tel_' + iteration);
		el.setAttribute('value',document.getElementById('ref_pe_tel').value);
		celula.appendChild(el);

		var el = document.createElement('input');
		el.setAttribute('type', 'hidden');
		el.setAttribute('name', 'pessoa_referencia_tipo_' + iteration);
		el.setAttribute('id', 'pessoa_referencia_tipo_' + iteration);
		el.setAttribute('value',document.getElementById('ref_pe_tipo').value);
		celula.appendChild(el);

		linha.appendChild(celula);

		// coluna 2 - TELEFONE
		celula = criaCelula(document.getElementById('ref_pe_tel').value);
		celula.style.cssText = 'text-align: left; color: #000000;font-size:10px';
		linha.appendChild(celula);

		// coluna 3 - TIPO
		var celula = criaCelula(document.getElementById('ref_pe_tipo').value);
		celula.style.cssText = 'text-align: center; color: #000000;font-size:10px';
		linha.appendChild(celula);

		// coluna 4 - A��es
		var celula = document.createElement('td');
		celula.style.cssText = 'text-align: right; color: #000000;font-size:10px';

		var el = document.createElement('input');
		el.setAttribute('type', 'button');
		el.setAttribute('value','Excluir');
		el.onclick=function(){removerReferencia(this);};
		celula.appendChild(el);
		linha.appendChild(celula);

		// finaliza linha da tabela
		var tbody = document.createElement('TBODY');
		tbody.appendChild(linha);
		/*linha.style.cssText = 'color: #404e2a;';*/
		tbl.appendChild(tbody);


		//limpa form de add mao de obra
		document.getElementById('ref_pe_nome').value='';
		document.getElementById('ref_pe_tel').value='';
		document.getElementById('ref_pe_tipo').selectedIndex=0;
		document.getElementById('ref_pe_nome').focus();

	}

function removerReferencia(iidd){
	var tbl = document.getElementById('tbl_referencias');
	var oRow = iidd.parentElement.parentElement;
	tbl.deleteRow(oRow.rowIndex);
}

//***************Comprovante******************
	function adicionaLinhaComprovante() {

		if(document.getElementById('comp_pe_tipo').value=="")     { alert('Informe o Tipo');   return false}
		if(document.getElementById('comp_pe_desc').value=="")      { alert('Informe Descri��o'); return false}
		if(document.getElementById('comp_pe_valor').value=="")     { alert('Informe Valor'); return false}


		var tbl = document.getElementById('tbl_comprovante');
		var lastRow = tbl.rows.length;
		var iteration = lastRow;

		var linha = document.createElement('tr');
		linha.style.cssText = 'color: #000000; text-align: left; font-size:10px';

		var celula = criaCelula(document.getElementById('comp_pe_tipo').value);
		celula.style.cssText = 'text-align: left; color: #000000;font-size:10px';


		var el = document.createElement('input');
		el.setAttribute('type', 'hidden');
		el.setAttribute('name', 'pessoa_comprovante_tipo_' + iteration);
		el.setAttribute('id', 'pessoa_comprovante_tipo_' + iteration);
		el.setAttribute('value',document.getElementById('comp_pe_tipo').value);
		celula.appendChild(el);

		var el = document.createElement('input');
		el.setAttribute('type', 'hidden');
		el.setAttribute('name', 'pessoa_comprovante_desc_' + iteration);
		el.setAttribute('id', 'pessoa_comprovante_desc_' + iteration);
		el.setAttribute('value',document.getElementById('comp_pe_desc').value);
		celula.appendChild(el);

		var el = document.createElement('input');
		el.setAttribute('type', 'hidden');
		el.setAttribute('name', 'pessoa_comprovante_valor_' + iteration);
		el.setAttribute('id', 'pessoa_comprovante_valor_' + iteration);
		el.setAttribute('value',document.getElementById('comp_pe_valor').value);
		celula.appendChild(el);

		var el = document.createElement('input');
		el.setAttribute('type', 'hidden');
		el.setAttribute('name', 'pessoa_comprovante_xrg_' + iteration);
		el.setAttribute('id', 'pessoa_comprovante_xrg_' + iteration);
		el.setAttribute('value',document.getElementById('comp_pe_xrg').checked);
		el.setAttribute('checked',document.getElementById('comp_pe_xrg').checked);
		celula.appendChild(el);

		var el = document.createElement('input');
		el.setAttribute('type', 'hidden');
		el.setAttribute('name', 'pessoa_comprovante_xcpf_' + iteration);
		el.setAttribute('id', 'pessoa_comprovante_xcpf_' + iteration);
		el.setAttribute('value',document.getElementById('comp_pe_xcpf').checked);
		el.setAttribute('checked',document.getElementById('comp_pe_xcpf').checked);
		celula.appendChild(el);

		var el = document.createElement('input');
		el.setAttribute('type', 'hidden');
		el.setAttribute('name', 'pessoa_comprovante_xres_' + iteration);
		el.setAttribute('id', 'pessoa_comprovante_xres_' + iteration);
		el.setAttribute('value',document.getElementById('comp_pe_xres').checked);
		el.setAttribute('checked',document.getElementById('comp_pe_xres').checked);
		celula.appendChild(el);


		linha.appendChild(celula);

		// coluna 3 - Descri��o
		var celula = criaCelula(document.getElementById('comp_pe_desc').value);
		celula.style.cssText = 'text-align: center; color: #000000;font-size:10px';
		linha.appendChild(celula);

		// coluna 4 - Valor
		var celula = criaCelula(document.getElementById('comp_pe_valor').value);
		celula.style.cssText = 'text-align: center; color: #000000;font-size:10px';
		linha.appendChild(celula);

		var xRG  = '';
		var xCPF = '';
		var xRES = '';
		if (document.getElementById('comp_pe_xrg').checked)  xRG  = 'X';
		if (document.getElementById('comp_pe_xcpf').checked) xCPF = 'X';
		if (document.getElementById('comp_pe_xres').checked) xRES = 'X';

		// coluna 5 - Xerox Rg
		var celula = criaCelula(xRG);
		celula.style.cssText = 'text-align: center; color: #000000;font-size:10px';
		linha.appendChild(celula);

		// coluna 6 - Xerox CPF
		var celula = criaCelula(xCPF);
		celula.style.cssText = 'text-align: center; color: #000000;font-size:10px';
		linha.appendChild(celula);

		// coluna 7 - Xerox Residencia
		var celula = criaCelula(xRES);
		celula.style.cssText = 'text-align: center; color: #000000;font-size:10px';
		linha.appendChild(celula);


		hoje = new Date();
		dia = hoje.getDate();
		dias = hoje.getDay();
		mes = hoje.getMonth();
		ano = hoje.getYear();
		if (dia < 10)
			dia = "0" + dia
		if (ano < 2000)
			ano = "19" + ano

		// coluna 8 - Data Digita��o
		var celula = criaCelula(dia+'/'+mes+'/'+ano);
		celula.style.cssText = 'text-align: center; color: #000000;font-size:10px';
		linha.appendChild(celula);


		// coluna 9 - A��es
		var celula = document.createElement('td');
		celula.style.cssText = 'text-align: right; color: #000000;font-size:10px';

		var el = document.createElement('input');
		el.setAttribute('type', 'button');
		el.setAttribute('value','Excluir');
		el.onclick=function(){removerComprovante(this);};
		celula.appendChild(el);
		linha.appendChild(celula);

		// finaliza linha da tabela
		var tbody = document.createElement('TBODY');
		tbody.appendChild(linha);
		/*linha.style.cssText = 'color: #404e2a;';*/
		tbl.appendChild(tbody);


		//limpa form de add mao de obra
		document.getElementById('comp_pe_tipo').value='';
		document.getElementById('comp_pe_desc').value='';
		document.getElementById('comp_pe_valor').value='';
		document.getElementById('comp_pe_tipo').focus();

	}

function removerComprovante(iidd){
	var tbl = document.getElementById('tbl_comprovante');
	var oRow = iidd.parentElement.parentElement;
	tbl.deleteRow(oRow.rowIndex);
}
//****************Fim Comprovante*********************

	function criaCelula(texto) {
		var celula = document.createElement('td');
		var textoNode = document.createTextNode(texto);
		celula.appendChild(textoNode);
		return celula;
	}


function checaTipoPessoa (){
	var tipo_pessoa='';
	for (i=0;i<document.frm_cadastro.tipo_pessoa.length;i++){
		if (document.frm_cadastro.tipo_pessoa[i].checked==true){
			tipo_pessoa=document.frm_cadastro.tipo_pessoa[i].value;
			break;
		}
	}
	if (tipo_pessoa==''){
		alert('Selecione o tipo: f�sica ou jur�dica');
		return;
	}
}
function checarCPF(campo){

	var tipo_pessoa='';
	for (i=0;i<document.frm_cadastro.tipo_pessoa.length;i++){
		if (document.frm_cadastro.tipo_pessoa[i].checked==true){
			tipo_pessoa=document.frm_cadastro.tipo_pessoa[i].value;
			break;
		}
	}
	if (tipo_pessoa==''){
		//alert('Selecione o tipo: f�sica ou jur�dica');
		return;
	}

	$.ajax({
		type: "POST",
		url: "<? echo $PHP_SELF ?>",
		data: "verificaDoc="+tipo_pessoa+"&tipo=<? echo $tipo ?>&doc="+campo.value,
		success: function(msg){
			if (msg.length>0){
				alert(msg);
			}
		}
	});

}

</script>
<? 

if (strlen($msg_erro)>0)             echo "<div class='error'>$msg_erro</div>";
if (strlen($ok)>0 OR strlen($msg)>0) echo "<div class='ok'>$msg</div>";


?>
<table style=' border:#485989 1px solid; background-color: #e6eef7' align='center' width='680' border='0' class='tabela'>
		<tr height='20' bgcolor='#7392BF'>
			<td class='Titulo_Tabela' align='center' colspan='6'>Cadastro de <?=$tipo?></td>
		</tr>
		<tr height='10'>
			<td  align='center' colspan='6'></td>
		</tr>
		<tr>
			<td class='Label'>
				<div id="container-Principal">
					<ul>
						<li><a href="#tab0Procurar"><span><img src='imagens/lupa.png' align=absmiddle> Busca</span></a></li>
						<li><a href="#tab1Procurar"><span><img src='imagens/lupa.png' align=absmiddle> Busca Avan�ada</span></a></li>
						<li><a href="#tab2Cadastrar"><span><img src='imagens/document-txt-blue-new.png' align=absmiddle> Cadastro</span></a></li>
					</ul>
					<div id="tab0Procurar">
						<form name="frm_procura_simples" id="frm_procura_simples"  method="post" action="<? echo $PHP_SELF ?>">
						<input type='hidden' value='<?=$tipo?>' name='tipo'>
						<table align='left' width='100%' border='0' class='tabela'>
								<tr>
									<td class='Label'>Buscar por: </td>
									<td align='left' ><input class="Caixa" type="text" name="busca_por" id='busca_por' size="50" maxlength="80" value="<? echo $busca_por ?>"></td>
								</tr>
								<tr>
									<td class='Label'>Campo</td>
									<td colspan='4'>
											<select class='Caixa' name='campo_pesquisa'>
												<option value='codigo' <? if ($campo_pesquisa=='codigo') echo "SELECTED"; ?>>C�digo</option>
												<option value='nome' <? if ($campo_pesquisa=='nome') echo "SELECTED";?>>Nome</option>
												<option value='cpf' <? if ($campo_pesquisa=='cpf') echo "SELECTED";?>><?=$CPF_CNPJ?></option>
												<option value='telefone' <? if ($campo_pesquisa=='telefone') echo "SELECTED";?>>Telefone</option>
												<option value='endereco' <? if ($campo_pesquisa=='endereco') echo "SELECTED";?>>Endere�o</option>
												<option value='email' <? if ($campo_pesquisa=='email') echo "SELECTED";?>>E-Mail</option>
											</select>
									</td>
								</tr>
								<tr>
									<td colspan='6' align='center'>
										<br>
										<input name='btn_acao' type='hidden' value='pesquisar_simples'>
										<input name='pesquisar' type='submit' class='botao' value='Pesquisar'>
									</td>
								</tr>
						</table>
						</form>
					</div>
					<div id="tab1Procurar">

						<form name="frm_procura" method="post" action="<? echo $PHP_SELF ?>">
						<input type='hidden' value='<?=$tipo?>' name='tipo'>
						<table align='left' width='100%' border='0' class='tabela'>
								<tr>
									<td class='Label'>Nome</td>
									<td align='left' colspan='2'><input class="Caixa" type="text" name="nome_busca" id="nome_busca" size="50" maxlength="80" value="<? echo $nome_busca ?>" ></td>
								</tr>
								<tr>
									<td class='Label'><?=$CPF_CNPJ?></td>
									<td colspan='4'><input class="Caixa" type="text" name="cnpj_busca" size="14" maxlength="14" value="<? echo $cnpj_busca ?>"></td>
								</tr>
								<tr>
									<td class='Label'>Email</td>
									<td align='left' colspan='2'><input class="Caixa" type="text" name="email_busca" size="50" maxlength="80" value="<? echo $email_busca ?>" ></td>
								</tr>
								<tr>
									<td colspan='6' align='center'>
										<br>
										<input name='btn_acao' type='hidden' value='pesquisar'>
										<input name='pesquisar' type='submit' class='botao' value='Pesquisar'>
									</td>
								</tr>
						</table>
						</form>
					</div>
					<div id="tab2Cadastrar">
						<p>
						<!-- <a href='<? echo $PHP_SELF ?>?tipo=<?=$tipo?>&btn_acao=cadastrar'><img src='imagens/edit2.png' align='absmiddle'> Cadastar um novo <?=$tipo?></a> -->


									<form name="frm_cadastro" method="post" action="<? echo $PHP_SELF ?>#tab2Cadastrar">
									<input  type="hidden" name="pessoa" value="<? echo $pessoa ?>">
									<input  type="hidden" name="tipo" value="<? echo $tipo ?>">

									<table style='background-color: #e6eef7' align='center' width='710' border='0' class='tabela'>
											<tr>
												<td class='Label'>Nome</td>
												<td colspan='6'><input class="Caixa" type="text" name="nome" size="50" maxlength="60" value="<? echo $nome ?>">
													<INPUT TYPE="hidden" name='posto' value='<?echo $posto;?>'>
												</td>
												
											</tr>
											<tr>
												<td class='Label' valign='top'>Nome Fantasia</td>
												<td align='left' colspan='6'>
													<input class="Caixa" type="text" name="nome_fantasia" size="50" maxlength="60" value="<? echo $nome_fantasia ?>" ><span class='text_curto'> <a href='#' title='Nome fantasia da Empresa<br> Usado apenas quando for pessoa jur�dica' class='ajuda'>?</a></span></td>
												
											</tr>
											<tr>
												<td class='Label' valign='top'>Tipo</td>
												<td align='left'>
													F�sica <input type="radio" name="tipo_pessoa" value='F' <? if ($tipo_pessoa=='F') echo "CHECKED";?> > &nbsp;&nbsp; Jur�dico<input type="radio" name="tipo_pessoa" value='J' <? if ($tipo_pessoa=='J') echo "CHECKED";?>>
												</td>
									<!--			<td class='Label' valign='top'>Estrangeiro</td>
												<td align='left'>
													<input type="checkbox" name="estrangeiro" value='SIM' >
									-->
												</td>
											</tr>
											<tr>
												<td class='Label'><?=$CPF_CNPJ?></td>
												<td align='left' >
													<input class="Caixa" type="text" name="cnpj"   size="15" maxlength="14" value="<? echo $cnpj ?>"  <? // onfocus='checaTipoPessoa()' onblur='checarCPF(this);'?>>
												<a href="http://www.sintegra.gov.br/new_bv.html?TB_iframe=true&height=500&width=700" title="Consulta Sintegra - Selecione o estado" class="thickbox">Consultar Sintegra</a>	
												</td>
												<td class='Label'><? if ($tipo=='cliente') echo "RG"; else echo "IE"?></td>
												<td align='left' colspan='2'>
													<input class="Caixa" type="text" name="ie"   size="15" maxlength="14" value="<? echo $ie ?>" ></td>
											</tr>


											<tr>
												<td class='Label'>CEP</td>
												<td align='left' ><input class="Caixa" type="text" name="cep"   size="10" maxlength="10" value="<? echo $cep ?>" onblur="buscaCEP(this.value, document.frm_cadastro.endereco, document.frm_cadastro.bairro, document.frm_cadastro.cidade, document.frm_cadastro.estado) ;"> <a href="busca_cep.htm?TB_iframe=true&height=400&width=600" title="Consulta Localidade" class="thickbox">Consulta Localidade</a></td>
											</tr>

											<tr>
												<td class='Label'>Endere�o</td>
												<td align='left' >
													<input class="Caixa" type="text" name="endereco"   size="40" maxlength="50" value="<? echo $endereco ?>" ></td>

												<td class='Label'>N�mero</td>
												<td align='left' >
													<input class="Caixa" type="text" name="numero"   size="5" maxlength="10" value="<? echo $numero ?>"></td>
											</tr>
											<tr>
												<td class='Label'>Complemento</td>
												<td align='left' >
													<input class="Caixa" type="text" name="complemento"   size="5" maxlength="20" value="<? echo $complemento ?>" ></td>

												<td class='Label'>Bairro</td>
												<td align='left' colspan='2'>
													<input class="Caixa" type="text" name="bairro"   size="20" maxlength="40" value="<? echo $bairro ?>" ></td>
											</tr>
											<tr>
												<td class='Label'>Cidade</td>
												<td align='left' >
													<input class="Caixa" type="text" name="cidade"   size="40" maxlength="40" value="<? echo $cidade ?>" ></td>

												<td class='Label'>Estado</td>
												<td align='left' >
													<input class="Caixa" type="text" name="estado"   size="2" maxlength="2" value="<? echo $estado ?>" ></td>
											</tr>
											<tr>
												<td class='Label'>Pa�s</td>
												<td align='left' colspan='4'>
													<select name='pais' class='Caixa'>
														<?
														$sql = "SELECT * FROM tbl_pais;";
														$res = pg_exec($con,$sql);
														for ($i=0; $i<pg_numrows($res); $i++){
															$xpais = pg_result($res,$i,pais);
															$xnome = pg_result($res,$i,nome);
															echo "<option value='$xpais'";
															if($xpais == $pais) echo " SELECTED ";
															echo ">$xnome</option>";
														}
														?>
													</select>
												</td>
											</tr>

											<? if($tipo == 'fornecedor'){?>
											<tr>
												<td class='Titulo_Colunas' colspan='5'><BR>Informa��es site do fornecedor</td>
											</tr>
											<tr>
												<td class='Label'>P�gina Internet</td>
												<td align='left' >
													<input class="Caixa" type="text" name="url"   size="40" maxlength="40" value="<? echo $url;?>" >
												</td>
												<td class='Label'>Login</td>
												<td class='Label' align='left'>
													<input class="Caixa" name="login" size="10" maxlength="20" value="<? echo $login;?>" >
												</td>
												<td class='Label'>Senha</td>
												<td class='Label' align='left'>
													<input class="Caixa" name="senha_fornecedor" size="10" maxlength="20" value="<? echo $senha_fornecedor ?>" ><a href='#' title='Site, login e senha para o comprador acessar o site do fornecedor.' class='ajuda'>?</a>
												</td>
											</tr>
											<? } ?>
											
											<tr>
												<td colspan='7'><br>
													<div id="container-Dentro">
														<ul>
															<li><a href="#tab1Contato"><span><img src='imagens/mail-blue.png' align=absmiddle> Contato</span></a></li>
															<li><a href="#tab2Entrega"><span><img src='imagens/mail-gold.png' align=absmiddle> End. Entrega</span></a></li>
															<li><a href="#tab3Cobranca"><span><img src='imagens/mail-gold.png' align=absmiddle> End. Cobran�a</span></a></li>
															<? if($tipo=='cliente'){## MENU SOMENTE PARA CLIENTE?>
															<li><a href="#tab4Referencia"><span><img src='imagens/people-alt2.png' align=absmiddle> Refer�ncias</span></a></li>
															<? }?>
															<li><a href="#tab5Comprovante"><span><img src='imagens/people-alt2.png' align=absmiddle> Comprovante</span></a></li>
															<? if($tipo=='fornecedor'){## MENU SOMENTE PARA FORNECEDORES?>
															<li><a href="#tab6Fornecedor"><span><img src='imagens/people-alt2.png' align=absmiddle> Permiss�o</span></a></li>
															<? }?>
															<li><a href="#tab7Banco"><span><img src='imagens/people-alt2.png' align=absmiddle> Banco</span></a></li>
															<? if($tipo=="colaborador"){ ## MENU SOMENTE PARA COLABORADORES?>
															<li><a href="#tab5Vendas"><span><img src='imagens/lupa.png' align=absmiddle> Venda</span></a></li>
															<? } ?>
															<?if($login_empresa == 49){?>
															<li><a href="#tab8Voip"><span><img src='imagens/people-alt2.png' align=absmiddle> Voip</span></a></li>
															<?}?>
														</ul>
														<div id="tab1Contato">
															<p>
															<table width='100%' border='0'>

																<tr>
																	<td class='Label'>Telefone Residencial *</td>
																	<td class='Label' align='left' >
																		<input class="Caixa" type="text" name="fone_residencial" id="fone_residencial" size="14" maxlength="30" value="<? echo $fone_residencial ?>" ></td>
																	<td class='Label'>Telefone Comercial *</td>
																	<td class='Label' align='left' >
																		<input class="Caixa" type="text" name="fone_comercial" id="fone_comercial"   size="14" maxlength="30" value="<? echo $fone_comercial ?>" >
																	</td>
																</tr>
																<tr>
																	<td class='Label'>Celular *</td>
																	<td class='Label' align='left'>
																		<input class="Caixa" type="text" name="cel" id="cel"   size="14" maxlength="30" value="<? echo $cel ?>" ></td>
																	<td class='Label'>Fax *</td>
																	<td class='Label' align='left'>
																		<input class="Caixa" type="text" name="fax" id="fax"   size="14" maxlength="30" value="<? echo $fax ?>" >
																	</td>
																</tr>

																
																
																<tr>
																</tr>
																<!-- **** -->
																<tr>
																	<td class='miudinho' align='left' colspan='6'>(*) Se informado o n�mero, o DDD � obrigat�rio</td>
																</tr>
																<? if($tipo<>"cliente"){?> 
																<tr>
																<td colspan='6'>
																<table>
																<tr height='20' >
																	<td class='Titulo_Colunas' colspan='6'>
																		<br>Informa��es de acesso
																	</td>
																</tr>
																<tr>
																	<td class='Label' valign='top'>Email</td>
																	<td class='Label' align='left' >
																		<input class="Caixa" type="text" name="email" size="40" maxlength="50" value="<? echo $email ?>" >
																		<a href='#' title='utilizado para login no sistema' class='ajuda'>?</a></span>
																	</td>
																	<td>&nbsp;</td>
																	<td class='Label'>Senha</td>
																	<td class='Label' align='left' colspan='2'>
																		<input class="Caixa" type="password" name="senha"   size="10" maxlength="20" value="<? echo $senha ?>" >
																	</td>
																	</tr>
																	</table>
																	</td>
																	</tr>

																<? } ?>
															</table>
															</p>
														</div>
														<div id="tab2Entrega">
															<p>
															<table width='100%'>
																	<tr>
																		<td class='Label'>CEP</td>
																		<td align='left' ><input class="Caixa" type="text" name="entrega_cep"   size="10" maxlength="10" value="<? echo $entrega_cep ?>" onblur="buscaCEP(this.value, document.frm_cadastro.entrega_endereco, document.frm_cadastro.entrega_bairro, document.frm_cadastro.entrega_cidade, document.frm_cadastro.entrega_estado) ;"> <a href="busca_cep.htm?TB_iframe=true&height=400&width=600" title="Consulta Localidade" class="thickbox">Consulta Localidade</a></td>
																	</tr>
																	<tr>
																		<td class='Label'>Endere�o</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="entrega_endereco"   size="40" maxlength="50" value="<? echo $entrega_endereco ?>" ></td>

																		<td class='Label'>N�mero</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="entrega_numero"   size="5" maxlength="10" value="<? echo $entrega_numero ?>"></td>
																	</tr>
																	<tr>
																		<td class='Label'>Complemento</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="entrega_complemento"   size="5" maxlength="20" value="<? echo $entrega_complemento ?>" ></td>

																		<td class='Label'>Bairro</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="entrega_bairro"   size="20" maxlength="40" value="<? echo $entrega_bairro ?>" ></td>
																	</tr>
																	<tr>
																		<td class='Label'>Cidade</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="entrega_cidade"   size="40" maxlength="40" value="<? echo $entrega_cidade ?>" ></td>

																		<td class='Label'>Estado</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="entrega_estado"   size="2" maxlength="2" value="<? echo $entrega_estado ?>" ></td>
																	</tr>
															</table>
															</p>
														</div>
														<div id="tab3Cobranca">
															<p>
															<table width='100%'>
																	<tr>
																		<td class='Label'>CEP</td>
																		<td align='left' ><input class="Caixa" type="text" name="cobranca_cep"   size="10" maxlength="10" value="<? echo $cobranca_cep ?>" onblur="buscaCEP(this.value, document.frm_cadastro.cobranca_endereco, document.frm_cadastro.cobranca_bairro, document.frm_cadastro.cobranca_cidade, document.frm_cadastro.cobranca_estado) ;"> <a href="busca_cep.htm?TB_iframe=true&height=400&width=600" title="Consulta Localidade" class="thickbox">Consulta Localidade</a></td>
																	</tr>
																	<tr>
																		<td class='Label'>Endere�o</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="cobranca_endereco"   size="40" maxlength="50" value="<? echo $cobranca_endereco ?>" ></td>

																		<td class='Label'>N�mero</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="cobranca_numero"   size="5" maxlength="10" value="<? echo $cobranca_numero ?>"></td>
																	</tr>
																	<tr>
																		<td class='Label'>Complemento</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="cobranca_complemento"   size="5" maxlength="20" value="<? echo $cobranca_complemento ?>" ></td>

																		<td class='Label'>Bairro</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="cobranca_bairro"   size="20" maxlength="40" value="<? echo $cobranca_bairro ?>" ></td>
																	</tr>
																	<tr>
																		<td class='Label'>Cidade</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="cobranca_cidade"   size="40" maxlength="40" value="<? echo $cobranca_cidade ?>" ></td>

																		<td class='Label'>Estado</td>
																		<td align='left' >
																			<input class="Caixa" type="text" name="cobranca_estado"   size="2" maxlength="2" value="<? echo $cobranca_estado ?>" ></td>
																	</tr>
															</table>
															</p>
														</div>
														<? if($tipo=='cliente'){?>
														<div id="tab4Referencia">
															<p>
															<table  width='100%'>
																	<tr>
																		<td class='Label'  align='left'>Nome <input class="Caixa" type="text" name="ref_pe_nome"   size="30" maxlength="100" value=""></td>
																		<td class='Label'  align='left'>Telefone <input class="Caixa" type="text" name="ref_pe_tel" id="ref_pe_tel"   size="12" maxlength="30" value=""></td>
																		<td class='Label'  align='left'>Tipo
																		<select class="Caixa" type="text" name="ref_pe_tipo" >
																			<option value='PESSOAL' selected>Pessoal</option>
																			<option value='BANCARIA'>Banc�ria</option>
																			<option value='COMERCIAL'>Comercial</option>
																			<option value='CONJUGUE'>C�njugue</option>
																			<option value='PAIS'>Pais</option>
																		</select>
																		</td>
																		<td class='Label'  align='left'><input type='button' name='btn_adicionar_ref_pe' value='Adicionar Refer�ncia' onClick='javascript:adicionaLinhaReferencia()' ></td>
																	</tr>
															</table>
															<br>
															<table width='100%' id='tbl_referencias' cellspacing='0' cellpadding='0'>
																<thead>
																	<tr><td colspan='4'><a href="http://www.acim.org.br/?TB_iframe=true&height=500&width=700" title="An�lise de Cr�dito - Entre com o login e a senha" class="thickbox">An�lise de Cr�dito</a></td></tr>
																	<tr>
																		<td class='Titulo_Tabela_Menor' width='50%'>Nome</td>
																		<td class='Titulo_Tabela_Menor' width='30%'>Telefone</td>
																		<td class='Titulo_Tabela_Menor' width='10%'>Tipo</td>
																		<td class='Titulo_Tabela_Menor' width='10%'></td>
																	</tr>
																</thead>
																<tbody>
																<?php
																		$aux_cont = count($array_referencias);
																		if ($aux_cont){
																			for ($w=0;$w<$aux_cont;$w++){
																				$aux_ref = $array_referencias[$w];
																				$ref_nome     = $aux_ref[0];
																				$ref_telefone = $aux_ref[1];
																				$ref_tipo     = $aux_ref[2];

																				if($w%2==0) $cor = '#FFFAEA';
																				else        $cor = '#FFFFFF';
																	
																				echo "<tr bgcolor='$cor' class='linha'>
																					<input type='hidden' name='pessoa_referencia_nome_$w' id='pessoa_referencia_nome_$w' value='$ref_nome'>
																					<input type='hidden' name='pessoa_referencia_tel_$w'  id='pessoa_referencia_tel_$w'  value='$ref_telefone'>
																					<input type='hidden' name='pessoa_referencia_tipo_$w' id='pessoa_referencia_tipo_$w' value='$ref_tipo'>
																					<td class='Label'>$ref_nome</td>
																					<td class='Label' align='left' > $ref_telefone</td>
																					<td class='Label' align='center'>$ref_tipo</td>
																					<td class='Label' align='right' > <input type='button' onclick='javascript:removerReferencia(this)' value='Excluir'</td>
																				</tr>";
																			}
																		}else{
																			//echo "<tr class='Label'><td colspan='4' align='center'>Nenhuma refer�ncia cadastrada</td></tr>";
																		}
																?>
																</tbody>
															</table>
															</p>
														</div>
														<?}?>
<!-- ****************************ABA COMPROVANTE************************************************
**************************************************************************************************-->
														<div id="tab5Comprovante">
															<p>
															<table  width='100%'>
																	<tr>
																	<td class='Label'  align='left'>Tipo
																		<input class="Caixa" type="text" name="comp_pe_tipo" id="comp_pe_tipo"   size="12" maxlength="30" value="">
																		</td>
																		<td class='Label'  align='left'>Descri��o <input class="Caixa" type="text" name="comp_pe_desc" size="30" maxlength="100" value=""></td>
																		<td class='Label'  align='left'>Valor <input class="Caixa" type="text" name="comp_pe_valor" id="comp_pe_valor"   size="10" maxlength="15" value=""></td>
																	   <td class='Label'>RG
																		<input type="checkbox" name="comp_pe_xrg" value='t' <?if($comp_pe_xrg=='t')echo "checked";?>>
																		</td>
																		<td class='Label'>CPF
																		<input type="checkbox" name="comp_pe_xcpf"  value='t' <?if($comp_pe_xcpf=='t')echo "checked";?>> 
																		</td>
																		<td class='Label'>Res
																		<input type="checkbox" name="comp_pe_xres" value='t' <?if($comp_pe_xres=='t')echo "checked";?>> 
																		</td>

																		<!-- botao -->
																		<td class='Label'  align='left'><input type='button' name='btn_acao_comp_pe' value='Adicionar Comprovante' onClick='javascript:adicionaLinhaComprovante()' ></td>
																	</tr>
															</table>
															<br>
															<table width='100%' id='tbl_comprovante' cellspacing='0' cellpadding='0'>
																<thead>
																	<tr><td colspan='8'>Comprovante de Renda</td></tr>
																	<tr>
																		<td class='Titulo_Tabela_Menor' width='15%'>Tipo</td>
																		<td class='Titulo_Tabela_Menor' width='30%'>Descri��o</td>
																		<td class='Titulo_Tabela_Menor' width='10%'>Valor</td>
																		<td class='Titulo_Tabela_Menor' width='5%'>RG</td>
																		<td class='Titulo_Tabela_Menor' width='5%'>CPF</td>
																		<td class='Titulo_Tabela_Menor' width='5%'>Res</td>
																		<td class='Titulo_Tabela_Menor' width='20%'>Data Digita��o</td>
																		<td class='Titulo_Tabela_Menor' width='10%'></td>

																	</tr>
																</thead>
																<tbody>
																<?php
																		$aux_cont = count($array_comprovante);
																		if ($aux_cont){
																			for ($w=0;$w<$aux_cont;$w++){
																				$aux_comp = $array_comprovante[$w];
																				$comp_tipo    = $aux_comp[0];
																				$comp_desc    = $aux_comp[1];
																				$comp_valor   = $aux_comp[2];
																				$comp_xrg     = $aux_comp[3];
																				$comp_xcpf    = $aux_comp[4];
																				$comp_xres    = $aux_comp[5];
																				$comp_data    = $aux_comp[6];
																				if($w%2==0) $cor = '#FFFAEA';
																				else        $cor = '#FFFFFF';
																	
																				if($comp_xrg=='t'){$comp_xrg = 'x';}else $comp_xrg = "";
																				if($comp_xcpf=='t'){$comp_xcpf ='x';}else $comp_xcpf = "";
																				if($comp_xres=='t'){$comp_xres = 'x';}else $comp_xres = "";

																				
																				echo "<tr bgcolor='$cor' class='linha'>
																					
																					<input type='hidden' name='pessoa_comprovante_tipo_$w'  id='pessoa_comprovante_tipo_$w'  value='$comp_tipo'>

																					<input type='hidden' name='pessoa_comprovante_desc_$w' id='pessoa_comprovante_desc_$w' value='$comp_desc'>


																					<input type='hidden' name='pessoa_comprovante_valor_$w' id='pessoa_comprovante_valor_$w' value='$comp_valor'>

																					<input type='hidden' name='pessoa_comprovante_xrg_$w' id='pessoa_comprovante_xrg_$w' value='$comp_xrg'>

																					<input type='hidden' name='pessoa_comprovante_xcpf_$w' id='pessoa_comprovante_xcpf_$w' value='$comp_xcpf'>

																					<input type='hidden' name='pessoa_comprovante_xres_$w' id='pessoa_comprovante_xres_$w' value='$comp_xres'>

																					<input type='hidden' name='pessoa_comprovante_data_$w' id='pessoa_comprovante_data_$w' value='$comp_data'>

																					
																					<td class='Label' align='left' > $comp_tipo</td>

																					<td class='Label' >$comp_desc</td> 

																					<td class='Label' align='left'>$comp_valor</td> 

																					<td class='Label' align='center'>$comp_xrg</td> 

																					<td class='Label' align='center'>$comp_xcpf</td> 

																					<td class='Label' align='center'>$comp_xres</td> 

																					<td class='Label' align='center'>$comp_data</td> 

																					<td class='Label' align='right' > <input type='button' onclick='javascript:removerComprovante(this)' value='Excluir'</td>
																				</tr>";
																			}
																		}else{
																			//echo "<tr class='Label'><td colspan='4' align='center'>Nenhuma refer�ncia cadastrada</td></tr>";
																		}
																?>
																</tbody>
															</table>
															</p>
														</div>



<!-- *****************************FIM ABA COMPROVANTE***********************************************
**************************************************************************************************-->

<!-- ****************************ABA  FORNECEDOR************************************************
**************************************************************************************************-->
														<?
															if($tipo=='fornecedor'){
														?>
														<div id="tab6Fornecedor">
															<p>
															<table width='100%'>
															<FORM ACTION='<? $PHP_SELF; ?>' METHOD='POST'>
															  <tr bgcolor='#596D9B'>
																<td nowrap colspan='5' class='menu_top' align='center'  background='imagens/azul.gif'><font size='3'>Tipo de produtos fornecidos pelo Fornecedor </font></td>
															  </tr>
															  <tr bgcolor='#fafafa'>
																<td nowrap colspan='2' align='right'>
																	<input type='hidden' name='requisicao' value='nova'>
																</td>
															  </tr>
															  <tr bgcolor='#fafafa'>
																<td nowrap width='100' align='center'>LINHA</td>
																<td nowrap width='150' align='center'>FAM�LIA</td>
																<td nowrap width='150' align='center'>MODELO</td>
																<td nowrap width='150' align='center'>MARCA</td>
																<td nowrap align='center'>&nbsp;</td>
															  </tr>
															  <tr bgcolor='#fafafa'>
															  <?
																#################### INFORMA��ES DE LINHA ##################
																echo "<td nowrap width='100' align='center'>";

																$sql= " SELECT * 
																		FROM tbl_linha
																		WHERE fabrica = $login_empresa
																		ORDER BY nome ";

																$res= pg_exec($con, $sql);

																if(@pg_numrows($res)>0){

																	echo "<select name='linha'>";
																	echo "<option value=''>Selecionar";
																	for ( $i = 0 ; $i < @pg_numrows ($res) ; $i++ ) {

																		$linha= trim(pg_result($res,$i,linha));	
																		$nome = trim(pg_result($res,$i,nome));	
																		echo "<option value='$linha'>$nome";
																	}
																	echo "</select>";
																}else{
																	//echo "nenhum fornecedor encontrado-$sql";
																}
																echo "</td>";

																################### INFORMA��ES DE FAMILIA ####################
																echo "<td nowrap width='150' align='center'>";

																$sql= " SELECT * 
																		FROM tbl_familia
																		WHERE fabrica = $login_empresa 
																		ORDER BY descricao ";

																$res= pg_exec($con, $sql);

																if(@pg_numrows($res)>0){

																	echo "<select name='familia'>";
																	echo "<option value=''>Selecionar";
																	for ( $i = 0 ; $i < @pg_numrows ($res) ; $i++ ) {

																		$familia= trim(pg_result($res,$i,familia));	
																		$descricao = trim(pg_result($res,$i,descricao));	
																		echo "<option value='$familia'>$descricao";
																	}
																	echo "</select>";
																}else{
																	//echo "nenhum fornecedor encontrado-$sql";
																}
																echo "</td>";

																#################### INFORMA��ES DE MODELO ##################
																echo "<td nowrap width='150' align='center'>";

																$sql= " SELECT * 
																		FROM tbl_modelo
																		WHERE fabrica = $login_empresa 
																		ORDER BY nome ";

																$res= pg_exec($con, $sql);

																if(@pg_numrows($res)>0){

																	echo "<select name='modelo'>";
																	echo "<option value=''>Selecionar";
																	for ( $i = 0 ; $i < @pg_numrows ($res) ; $i++ ) {

																		$modelo= trim(pg_result($res,$i,modelo));	
																		$nome = trim(pg_result($res,$i,nome));	
																		echo "<option value='$modelo'>$nome";
																	}
																	echo "</select>";
																}else{
																	//echo "nenhum fornecedor encontrado-$sql";
																}
																echo "</td>";

																#################### INFORMA��ES DE MARCA ##################
																echo "<td nowrap width='150' align='center'>";

																$sql= " SELECT * 
																		FROM tbl_marca
																		ORDER BY nome ";

																$res= pg_exec($con, $sql);

																if(@pg_numrows($res)>0){

																	echo "<select name='marca'>";
																	echo "<option value=''>Selecionar";
																	for ( $i = 0 ; $i < @pg_numrows ($res) ; $i++ ) {

																		$marca= trim(pg_result($res,$i,marca));	
																		$nome = trim(pg_result($res,$i,nome));	
																		echo "<option value='$marca'>$nome";
																	}
																	echo "</select>";
																}else{
																	//echo "nenhum fornecedor encontrado-$sql";
																}
																echo "</td>";
															?>
																<td nowrap colspan='1' align='center'>
																	<input type='submit' name='enviar' value='ok'>
																</td>
															  </tr>
															<? if(strlen($pessoa) > 0){?>
															  <tr bgcolor='#596D9B'' class ='menu_top'>
																<td nowrap colspan='5' align='center'>Dados dos Produtos Cadastrados para os Fornecedores</td>
															  </tr>
															  <tr bgcolor='#fafafa'>
															  <?
																#################### INFORMA��ES DE LINHA ##################
																echo "<td nowrap width='150' align='center'>";

																$sql= " SELECT linha, nome
																		FROM tbl_fornecedor_linha 
																		JOIN tbl_linha  using(linha) 
																		WHERE tbl_fornecedor_linha.empresa = $login_empresa
																			and pessoa_fornecedor=$pessoa;";
																			//echo "sql: $sql";

																$res= pg_exec($con, $sql);

																if(@pg_numrows($res)>0){
																	echo "<select size='4' name='fornecedor_linha'>";
																	for ( $i = 0 ; $i < @pg_numrows ($res) ; $i++ ) {
																		$linha= trim(pg_result($res,$i,linha));	
																		$nome = trim(pg_result($res,$i,nome));	
																		echo "<option value='$linha'>$nome";
																	}
																	echo "</select>";
																}else{
																	//echo "nenhum fornecedor encontrado-$sql";
																}
																echo "</td>";

																################### INFORMA��ES DE FAMILIA ####################
																echo "<td nowrap width='150' align='center'>";

																$sql= " SELECT familia, descricao
																		FROM tbl_fornecedor_familia
																		JOIN tbl_familia  using(familia) 
																		WHERE tbl_fornecedor_familia.empresa = $login_empresa
																			and pessoa_fornecedor=$pessoa;";
																$res= pg_exec($con, $sql);

																if(@pg_numrows($res)>0){

																	echo "<select size='4' name='familia'>";
															 
																	for ( $i = 0 ; $i < @pg_numrows ($res) ; $i++ ) {

																		$familia= trim(pg_result($res,$i,familia));	
																		$descricao = trim(pg_result($res,$i,descricao));	
																		echo "<option value='$familia'>$descricao";
																	}
																	echo "</select>";
																}else{
																	//echo "nenhum fornecedor encontrado-$sql";
																}
																echo "</td>";

																#################### INFORMA��ES DE MODELO ##################
																echo "<td nowrap width='150' align='center'>";

																$sql= " SELECT modelo, nome
																		FROM tbl_fornecedor_modelo 
																		JOIN tbl_modelo  using(modelo) 
																		WHERE tbl_fornecedor_modelo.empresa = $login_empresa
																			and pessoa_fornecedor=$pessoa;";

																$res= pg_exec($con, $sql);

																if(@pg_numrows($res)>0){

																	echo "<select size='4' name='modelo'>";
															 
																	for ( $i = 0 ; $i < @pg_numrows ($res) ; $i++ ) {

																		$modelo	= trim(pg_result($res,$i,modelo));	
																		$nome	= trim(pg_result($res,$i,nome));	
																		echo "<option value='$modelo'>$nome";
																	}
																	echo "</select>";
																}else{
																	//echo "nenhum fornecedor encontrado-$sql";
																}
																echo "</td>";

																#################### INFORMA��ES DE MARCA ##################
																echo "<td nowrap width='150' align='center'>";

																$sql= " SELECT marca, nome
																		FROM tbl_fornecedor_marca 
																		JOIN tbl_marca  using(marca) 
																		WHERE tbl_fornecedor_marca.empresa = $login_empresa
																			and pessoa_fornecedor=$pessoa;";

																$res= pg_exec($con, $sql);

																if(@pg_numrows($res)>0){

																	echo "<select size='4' name='marca'>";
															 
																	for ( $i = 0 ; $i < @pg_numrows ($res) ; $i++ ) {

																		$marca= trim(pg_result($res,$i,marca));	
																		$nome = trim(pg_result($res,$i,nome));	
																		echo "<option value='$marca'>$nome";
																	}
																	echo "</select>";
																}else{
																	//echo "nenhum fornecedor encontrado-$sql";
																}
																echo "</td>";

															?>
																<td nowrap align='right'> </td>
															  </tr>
															 <?}?>
														  	</table>
															</p>
														</div>
														<?}?>
<!--*****************************FIM ABA FORNECEDOR***********************************************
************************************************************************************************-->

<!--************************************* ABA BANCO ***********************************************
************************************************************************************************-->
														<div id="tab7Banco">
															<p>
															<?												

																echo "<br><table border='0' cellpadding='2' cellspacing='0' class='HD' align='center' width='400'>";
																/*---hidden---*/
																echo "<input type='hidden' name='pessoa' value='$pessoa'>";
																echo "<input type='hidden' name='pessoa_banco' value='$pessoa_banco_b'>";
																/*------------*/
																echo "<tr height='20'>";
																echo "<td  colspan='6' align='center'><b>Cadastro de Dados Bancarios</b></td>";
																echo "</tr>";
																echo "<tr bgcolor='#FFFFFF' class='Conteudo'>";
																echo "<td>Banco</td>";
																echo "<td align='left' colspan='5'>";

																$sqlB =	"SELECT codigo, nome
																		FROM tbl_banco
																		ORDER BY codigo";
																$resB = pg_exec($con,$sqlB);

																if (pg_numrows($resB) > 0) {
																	echo "<select name='banco' size='1' class='Caixa'>";
																	echo "<option value=''></option>";
																	for ($x = 0 ; $x < pg_numrows($resB) ; $x++) {
																		$aux_banco     = pg_result($resB,$x,codigo);
																		$aux_banconome = pg_result($resB,$x,nome);
																		echo "<option value='" . $aux_banco . "'";
																		if ($banco == $aux_banco) echo " selected";
																		echo ">" . $aux_banco . " - " . $aux_banconome . " $aux_banco</option>";
																	}
																	echo "</select>";
																}

																echo "</td>";
																echo "</tr>";

																echo "<tr bgcolor='#FFFFFF' class='Conteudo'>";
																echo "<td>Ag�ncia</td>";
																echo "<td align='left'><input type='text' size='10' name='agencia' class='Caixa' value='$agencia'></td>";
																echo "<td>Conta</td>";
																echo "<td align='left'><input type='text' size='10' name='conta' class='Caixa' value='$conta'></td>";
																echo "<td width='100'>Tipo Conta</td>";
																echo "<td align='left'>";
																echo "<select name='tipo_conta' class='Caixa'>";
																echo "<option selected></option>";
																echo "<option value='Conta conjunta' ";
																if ($tipo_conta == 'Conta conjunta')   echo "selected";
																echo ">Conta conjunta</option>";
																echo "<option value='Conta corrente' ";
																if ($tipo_conta == 'Conta corrente')   echo "selected"; 
																echo ">Conta corrente</option>";
																echo "<option value='Conta individual' ";
																if ($tipo_conta == 'Conta individual') echo "selected"; 
																echo ">Conta individual</option>";
																echo "<option value='Conta jur�dica' ";
																if ($tipo_conta == 'Conta jur�dica')   echo "selected"; 
																echo ">Conta jur�dica</option>";
																echo "<option value='Conta poupan�a' ";
																if ($tipo_conta == 'Conta poupan�a')   echo "selected"; 
																echo ">Conta poupan�a</option>";
																echo "</select>";
																echo "</td>";
																echo "</tr>";
																echo "<tr bgcolor='#FFFFFF' class='Conteudo'>";
																echo "<td>Favorecido</td>";
																echo "<td align='left' colspan='5'><input type='text' size='40' name='favorecido' class='Caixa' value='$favorecido'></td>";
																echo "</tr>";
																echo "<tr bgcolor='#FFFFFF' class='Conteudo'>";
																echo "<td>Observa��o</td>";
																echo "<td align='left' colspan='5'>";
																echo "<textarea class='Caixa' name='observacao' rows='3' cols='50'>$observacao</textarea>";
																echo "</td>";
																echo "</tr>";
																echo "<tr>";
																echo"<td align='left' ></td>";
																if ($tipo=='fornecedor') {
																	echo"<td class='Label' colspan='5'><input type='checkbox' name='pessoa_banco_ck' value='t' >Conta principal</td>";
																}
																echo "</tr>";
																echo "</table>";

															##### LEGENDAS - IN�CIO #####
															if ($tipo=='fornecedor') {
																echo "<br>";
																echo "<div align='left' style='position: relative; left: 150'>";
																echo "<table border='0' cellspacing='0' cellpadding='0'>";
																echo "<tr height='18'>";
																echo "<td width='18' bgcolor='#FFB0B0'>&nbsp;</td>";
																echo "<td align='left'><font size='1'><b>&nbsp; Conta principal</b></font></td>";
																echo "</tr>";
																echo "</table>";
																echo "</div>";
																echo "<BR>";
															}
															##### LEGENDAS - FIM  ######

															if(strlen($pessoa) > 0){
															$sql = " SELECT 
																		tbl_pessoa_banco.pessoa_banco,
																		banco , 
																		nome ,
																		agencia ,
																		conta , 
																		tipo_conta ,
																		tbl_pessoa_fornecedor.pessoa_banco AS pessoa_banco_for
																	FROM tbl_pessoa_banco 
																	JOIN tbl_pessoa_fornecedor ON tbl_pessoa_fornecedor.pessoa = tbl_pessoa_banco.pessoa
																	WHERE tbl_pessoa_banco.pessoa = $pessoa ORDER BY banco::numeric";
															$res = pg_exec ($con,$sql);
															

															if (@pg_numrows($res) > 0) {
																$pessoa_banco_for  = pg_result($res,0,pessoa_banco_for); 
																echo "<BR>";
																echo "<P align='center'><font size='2'><b>Dados Bancarios - D�bito/Cr�ditos Lan�ados";
																echo "<table border='0' cellpadding='2' cellspacing='0' class='HD' align='center' width='550'>";
																echo "<TR height='20' bgcolor='#DDDDDD' align='center'>";
																echo "<TD width='100'><b>Banco</b></TD>";
																echo "<TD width='100' ><b>Agencia</b></TD>";
																echo "<TD width='100' ><b>Conta</b></TD>";
																echo "<TD width='100' ><b>Tipo Conta</b></TD>";
																echo "</TR>";


																for ($i=0; $i<pg_numrows($res); $i++){

																	$x=$i+1;

																	$pessoa_banco      = pg_result($res,$i,pessoa_banco);
																	$banco             = pg_result($res,$i,banco);
																	$nome_banco        = pg_result($res,$i,nome);
																	$agencia           = pg_result($res,$i,agencia); 
																	$conta             = pg_result($res,$i,conta); 
																	$tipo_conta        = pg_result($res,$i,tipo_conta); 

																	
																	if($i%2==0) $cor = '#ffffff';
																	if($pessoa_banco_for == $pessoa_banco and $tipo=='fornecedor')	$cor = '#ffb0b0';
																	else									$cor = '#eeeeee';

																	echo "<TR bgcolor='$cor'class='Conteudo'>";
																	echo "<TD align='left'><a href='$PHP_SELF?pessoa=$pessoa&pessoa_banco=$pessoa_banco&tipo=$tipo#ta2Cadastrar'>$banco - $nome_banco</a></TD>";
																	echo "<TD align='center'nowrap>$agencia</TD>";
																	echo "<TD align='center'nowrap>$conta</TD>";
																	echo "<TD align='center'nowrap>$tipo_conta</TD>";
																	echo "</TR>";
																}
															echo " </TABLE>";
															}else{
																echo "<b><center>Nenhuma conta de banco lan�ada</center></b>";
															}
															}
															?>
														</p>
														</div>

<!-- ********************************* FIM ABA BANCO ***********************************************
************************************************************************************************-->

<!--************************************* ABA VOIP ***********************************************
************************************************************************************************-->
														<? if($login_empresa == 49){ ?>
														<div id="tab8Voip">
														<p align='center'>O Tulio pediu para desenvolver isto depois.</p>
														<?
															/*<p>

														<table width='100%' border="0">
															<tr>
																<td  class='Label'>DDI</td>
																<td><INPUT TYPE="text" class="Caixa" NAME="ddi" size="3" maxlength="3"></td>
																<td  class='Label'>DDD</td>
																<td><INPUT TYPE="text" class="Caixa" NAME="ddd" size="3" maxlength="2"></td>
																<td  class='Label'>Telefone</td>
																<td><INPUT TYPE="text" class="Caixa" NAME="telefone" size="8" maxlength="8"></td>
															</tr>
															<tr>
																<td  class='Label'>Qtde Ramais</td>
																<td><INPUT TYPE="text" class="Caixa" NAME="qtde_ramais" size="2" maxlength="2"></td>
																<td  class='Label'>Valor Mensal</td>
																<td><INPUT TYPE="text" class="Caixa" NAME="valor_mensal" size="8" maxlength="8"></td>
																<td  class='Label'>Instala��o</td>
																<td><INPUT TYPE="text" class="Caixa" NAME="instalacao" id="instalacao" size="10" maxlength="10"></td>
															</tr>
															<tr>
																<td  class='Label'>Data Inicio</td>
																<td><INPUT TYPE="text" class="Caixa" NAME="data_inicio" id="data_inicio" size="10" maxlength="10"></td>
																<td  class='Label'>Vencimento Mensal</td>
																<td><INPUT TYPE="text" class="Caixa" NAME="vencimento_mensal" size="2" maxlength="2"></td>
															</tr>

														</table>

														</p>
														*/ ?>
														</div>
														<?}?>

<!-- ********************************* FIM ABA VOIP***********************************************
************************************************************************************************-->



														<? if($tipo=="colaborador"){ ## MENU SOMENTE PARA COLABORADORES?>
														<div id="tab5Vendas">
															<p>
															<table>
																<tr>
																	<td class='Label'>Comiss�o Venda</td>
																	<td align='left' colspan='4'>
																		<input class="Caixa" type="text" name="comissao_venda"   size="10" maxlength="20" value="<? echo $comissao_venda ?>" onblur="javascript:checarNumero(this)"></td>
																</tr>
																<tr>
																	<td class='Label'>Comiss�o Servi�o</td>
																	<td align='left' colspan='4'>
																		<input class="Caixa" type="text" name="comissao_mao_de_obra"   size="10" maxlength="20" value="<? echo $comissao_mao_de_obra ?>" onblur="javascript:checarNumero(this)"></td>
																</tr>

																<tr>
																	<td class='Label' nowrap>Limite de Desconto</td>
																	<td align='left' colspan='4'>
																		<input class="Caixa" type="text" name="desconto_limite"   size="10" maxlength="20" value="<? echo $desconto_limite ?>"  onblur="javascript:checarNumero(this)"></td>
																</tr>
															</table>
															</p>
														</div>
														<?}?>
												</td>
											</tr>
											<tr>
												<td class='Label'>Ativo</td>
												<td align='left' colspan='4'>
													<input type="checkbox" name="ativo" value='t' <? if ($ativo=='t')echo "CHECKED"; if(strlen($ativo)==0) echo "CHECKED";?>>
													</td>
											</tr>
									<?if(strlen($pessoa)>0){?>
											<tr>
												<td align='center' colspan='4'>
													<a href="cadastro_documentos.php?pessoa=<?=$pessoa?>&KeepThis=true&TB_iframe=true&height=400&width=600" title="Cadastro de arquivos de <?=$tipo?>" class="thickbox">Lan�ar Arquivos</a>&nbsp;&nbsp;
													<!-- <a href="cadastro_banco.php?pessoa=<?=$pessoa?>&KeepThis=true&TB_iframe=true&height=400&width=600&pessoa=<?=$pessoa?>" title="Cadastro de contas bac�rias de <?=$tipo?>" class="thickbox">Banco</a>&nbsp;&nbsp; -->
													<a href="cadastro_credito.php?pessoa=<?=$pessoa?>&KeepThis=true&TB_iframe=true&height=400&width=600&pessoa=<?=$pessoa?>" title="Lan�amento de cr�dito para <?=$tipo?>" class="thickbox">Cr�dito</a> 
												</td>
											</tr>
											<tr>
												<td align='center' colspan='4'>&nbsp;</td>
											</tr>
									<?}?>
											<tr>
												<td class='Label' colspan='5' align='center'>
													<input type="submit" name="btn_acao"  value='Gravar' class='botao'>
													<input type="button" name="btn_limpar" onclick='limpar_form(this.form)'  value='Limpar' class='botao'>
													<input class="botao" type="button" name="btn_cancelar" onclick='javascript:window.location="cadastro.php?tipo=<?=$tipo?>"'  value='Cancelar' >
												</td>
											</tr>
									</table>
									</form>
						</p>
					</div>
			</td>
		</tr>
		<tr height='20'>
			<td  align='center' colspan='6'></td>
		</tr>
</table>
<?

if ($btn_acao=='pesquisar' || $btn_acao=='pesquisar_simples'){

	if(strlen($msg_erro)==0){
				$sql = "SELECT
					tbl_pessoa.pessoa       ,
					tbl_pessoa.nome         ,
					tbl_pessoa.cnpj         ,
					tbl_pessoa.email        
				FROM tbl_pessoa
				$sql_adicional_join
				WHERE tbl_pessoa.empresa = $login_empresa
				$sql_adicional
				ORDER BY nome ASC";
			//echo nl2br($sql );
			$res= pg_exec ($con,$sql) ;
		
			if (pg_numrows($res) > 0) {
				echo "<br>";
				echo "<input type='hidden' name='qtde_item' value='$qtde_item'>";
				echo "<table style=' border:#485989 1px solid; background-color: #e6eef7' align='center' width='650' border='0' class='tabela_reduzida'>";
				echo "<caption>";
				echo "$msg - Rela��o de $tipo cadastrados";
				echo "</caption>";
				echo "<tr height='20' bgcolor='#7392BF'>";

				if ($tipo=='fornecedor') $aux_tipo = "CNPJ"; else $aux_tipo = "CPF";
				echo "<td align='center' class='Titulo_Tabela'><b>C�digo</b></td>";
				echo "<td align='left'   class='Titulo_Tabela'><b>Descri��o</b></td>";
				echo "<td align='left'   class='Titulo_Tabela'><b> $aux_tipo </b></td>";
				echo "<td align='left'   class='Titulo_Tabela'><b>Email</b></td>";
				echo "<td align='center' class='Titulo_Tabela'><b>A��es</b></td>";
				echo "</tr>";	

				for ($k = 0; $k <pg_numrows($res) ; $k++) {
					$nome         = trim(pg_result($res,$k,nome));
					$pessoa       = trim(pg_result($res,$k,pessoa));
					$cnpj         = trim(pg_result($res,$k,cnpj));
					$email        = trim(pg_result($res,$k,email));

					if($k%2==0)$cor = '#D9E8FF';
					else               $cor = '#FFFFFF';
		
					echo "<tr bgcolor='$cor' class='linha'>";
					echo "<td align='center'>$pessoa</td>";
					echo "<td align='left'  >$nome</td>";
					echo "<td align='left'  >$cnpj</td>";
					echo "<td align='left'  >$email</td>";

					echo "<td align='center'><a href='$PHP_SELF?btn_acao=alterar&pessoa=$pessoa&tipo=$tipo#tab2Cadastrar'><img src='imagens/pencil.png'> Alterar</a>";
					echo "</td>";
					echo "</tr>";
				}
				echo "</table>";
			}else{
				echo "<br><br><p>Nenhum $tipo encontrado.</p>";
			}
	}else{
		echo "<br><p>Preencha os campos acima e fa�a uma busca!</p>";
	}
}
 include "rodape.php";
 ?>
