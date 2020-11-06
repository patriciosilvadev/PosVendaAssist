<?
include "dbconfig.php";
include "includes/dbconnect-inc.php";
include "autentica_usuario.php";
?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Pesquisa N� S�rie.. </title>
<meta http-equiv=pragma content=no-cache>
<style type="text/css">


table.tabela tr td{
    font-family: verdana;
    font-size: 11px;
    border-collapse: collapse;
    border:1px solid #596d9b;
}


.titulo_coluna{
    background-color:#596d9b;
    font: bold 11px "Arial";
    color:#FFFFFF;
    text-align:center;
}

</style>

<script type="text/javascript">

function fn_envia_serie(tipo,referencia,descricao,voltagem){
	
	
	if (confirm('Aten��o! Mais de um produto com o mesmo n�mero de s�rie. Verifique se o produto selecionado est� correto.')) {

		if (tipo == 'referencia' || tipo == 'descricao') {
			
			opener.produto_referencia.value = referencia;
			opener.produto_descricao.value  = descricao;
			opener.produto_voltagem.value   = voltagem ;

		}

		this.close();

	}

}

</script>
</head>

<body style="margin: 0px 0px 0px 0px;">
<img src="imagens/pesquisa_revenda<? if($sistema_lingua == "ES") echo "_es"; ?>.gif">

<?
//produto_serie
if (strlen($HTTP_GET_VARS["produto_serie"]) > 5) {
	if ($login_fabrica <> 43) {
		$produto_serie = strtoupper (trim ($HTTP_GET_VARS["produto_serie"]));
	} else {
		$produto_serie = trim ($HTTP_GET_VARS["produto_serie"]);
	}
	echo "<br><font face='Arial, Verdana, Times, Sans' size='2'>";
	echo "Pesquisando por <b>Serie do Produto</b>: ";
	echo "<i>$produto_serie</i></font>";
	echo "<p>";

	
	$sql = "SELECT 
				cnpj,
				referencia_produto,
				to_char(data_venda, 'dd/mm/yyyy') as data_venda,
				to_char(data_fabricacao, 'dd/mm/yyyy') as data_fabricacao
			FROM tbl_numero_serie  
			WHERE fabrica = $login_fabrica
				AND serie = trim('$produto_serie')";
	$res = pg_exec ($con,$sql);

	if (pg_numrows ($res) == 0) {
		echo "<h1>N� S�rie '$produto_serie' n�o encontrado. Favor preencher as informa��es de produto e de revenda manualmente. </h1>";
		echo "<script language='javascript'>";
		echo "setTimeout('window.close()',2500);";
		echo "</script>";
		exit;
	}
	if ($login_fabrica <> 50) {
	
		$sqlx = "SELECT tmp_serie1.serie   ,
						tmp_serie1.referencia_produto ,
						tmp_serie2.referencia_produto
				FROM tbl_numero_serie tmp_serie1
				JOIN tbl_numero_serie tmp_serie2 ON tmp_serie2.serie = tmp_serie1.serie AND tmp_serie2.fabrica = $login_fabrica AND tmp_serie2.referencia_produto <> tmp_serie1.referencia_produto
				WHERE tmp_serie1.fabrica = $login_fabrica
				AND   tmp_serie1.serie = trim('$produto_serie')";
	
	}else{ //HD 731643
	
		$sqlx ="
			SELECT  distinct(tmp_serie1.numero_serie),
					tmp_serie1.serie   ,
					tmp_serie1.referencia_produto as referencia_produto1,
					tbl_revenda.nome                ,
					tbl_revenda.revenda             ,
					tbl_revenda.cnpj                ,
					tbl_revenda.cidade              ,
					tbl_revenda.fone                ,
					tbl_revenda.endereco            ,
					tbl_revenda.numero              ,
					tbl_revenda.complemento         ,
					tbl_revenda.bairro              ,
					tbl_revenda.cep                 ,
					tbl_revenda.email               ,
					tbl_cidade.nome AS nome_cidade  ,
					tbl_cidade.estado

					FROM tbl_numero_serie tmp_serie1
					
					JOIN tbl_numero_serie tmp_serie2 ON (tmp_serie2.serie = tmp_serie1.serie AND tmp_serie2.fabrica = $login_fabrica AND tmp_serie2.referencia_produto <> tmp_serie1.referencia_produto)
					JOIN tbl_revenda on (tmp_serie1.cnpj = tbl_revenda.cnpj) 
					JOIN tbl_cidade  on (tbl_revenda.cidade = tbl_cidade.cidade)
					JOIN tbl_estado  on (tbl_cidade.estado  = tbl_estado.estado)
					
					WHERE tmp_serie1.fabrica = $login_fabrica 
					AND   tmp_serie1.serie = trim('$produto_serie');
		";
		
	}
	$resx = pg_exec ($con,$sqlx);

	if (pg_numrows ($resx) > 0 and $login_fabrica <> 50) {
		echo "<script language='javascript'>";
		echo "alert('Aten��o! Mais de um produto com o mesmo n�mero de s�rie. Verifique se o produto selecionado est� correto.')";
		echo "</script>";
	}


}else{
	echo "<h1>Digite ao menos 6 digitos para o n�mero de s�rie.</h1>";
	echo "<script language='javascript'>";
	echo "setTimeout('window.close()',2500);";
	echo "</script>";
	exit;
}

if (pg_numrows ($res) > 0 ) {
	
		$cnpj_revenda       = trim(pg_result($res,0,cnpj));
		$referencia_produto = trim(pg_result($res,0,referencia_produto));
		$data_venda         = trim(pg_result($res,0,data_venda));
		$data_fabricacao    = trim(pg_result($res,0,data_fabricacao));


		$referencia_produto = str_replace (".","",$referencia_produto);
		$referencia_produto = str_replace (",","",$referencia_produto);
		$referencia_produto = str_replace ("-","",$referencia_produto);
		$referencia_produto = str_replace ("/","",$referencia_produto);

		$sql = "
				SELECT   *
				FROM     tbl_produto
				JOIN     tbl_linha ON tbl_produto.linha = tbl_linha.linha and tbl_linha.fabrica = $login_fabrica
				JOIN     tbl_familia ON tbl_familia.familia = tbl_produto.familia and tbl_familia.fabrica = $login_fabrica
				WHERE    tbl_produto.referencia_pesquisa = '$referencia_produto'
				AND      tbl_linha.ativo IS TRUE
				AND      tbl_familia.ativo IS TRUE
				AND      tbl_produto.ativo IS TRUE
				AND      tbl_produto.produto_principal ";

		$res_produto = pg_exec ($con,$sql);

		if (pg_numrows ($res_produto) == 0) {
			echo "<h1>A s�rie foi encontrada, mas o produto '$referencia' n�o est� cadastrado na Telecontrol, entrar em contato com a F�brica.</h1>";
			echo "<script language='javascript'>";
			echo "setTimeout('window.close()',2500);";
			echo "</script>";
			exit;
		}
		
		$produto    = trim(pg_result($res_produto,0,produto));
		$descricao  = trim(pg_result($res_produto,0,descricao));
		$voltagem   = trim(pg_result($res_produto,0,voltagem));
		$referencia = trim(pg_result($res_produto,0,referencia));
		$descricao = str_replace ('"','',$descricao);
		$descricao = str_replace ("'","",$descricao);
		/*echo "<script language='JavaScript'>\n";
		echo "referencia.value = '$referencia' ;";
		echo "descricao.value = '$descricao' ;";
		echo "voltagem.value = '$voltagem';";
		echo "descricao.focus();";
		//echo "this.close();";
		echo "</script>\n";*/

		$sql = "SELECT      tbl_revenda.nome              ,
							tbl_revenda.revenda           ,
							tbl_revenda.cnpj              ,
							tbl_revenda.cidade            ,
							tbl_revenda.fone              ,
							tbl_revenda.endereco          ,
							tbl_revenda.numero            ,
							tbl_revenda.complemento       ,
							tbl_revenda.bairro            ,
							tbl_revenda.cep               ,
							tbl_revenda.email             ,
							tbl_cidade.nome AS nome_cidade,
							tbl_cidade.estado              
				FROM        tbl_revenda
				LEFT JOIN   tbl_cidade USING (cidade)
				LEFT JOIN   tbl_estado using(estado)
				WHERE       tbl_revenda.cnpj ='$cnpj_revenda' ";

		$res_revenda = pg_exec ($con,$sql);

		if (pg_numrows ($res_revenda) == 0) {
			echo "<h1>Revenda n�o encontrada para a s�rie: '$produto_serie'.</h1>";
			echo "<script language='javascript'>";
			echo "setTimeout('window.close()',2500);";
			echo "</script>";
		
			exit;
		}

		echo "<script language='JavaScript'>";
		echo "<!--\n";
		echo "this.focus();\n";
		echo "// -->\n";
		echo "</script>\n";
	
		$revenda    = trim(pg_result($res_revenda,0,revenda));
		$nome       = trim(pg_result($res_revenda,0,nome));
		$cnpj       = trim(pg_result($res_revenda,0,cnpj));
		$bairro     = trim(pg_result($res_revenda,0,bairro));
		$cidade     = trim(pg_result($res_revenda,0,nome_cidade));
		
	if ($login_fabrica == 50 and pg_numrows ($resx) > 0){ //HD 731643
		?>
		<table class="tabela" cellspacing="1" cellpadding="1" width="100%">
			<tr class='titulo_coluna'>
				<td>Referencia</td>
				<td>Descricao</td>
				<td>CNPJ</td>
				<td>Revenda</td>
			</tr>
			<?
	
			for ($i = 0; $i < pg_num_rows($resx); $i++)
			{
	
				$cor = ($i % 2) ? "#F7F5F0" : "#F1F4FA";
		
				$revenda     = trim(pg_result($resx,$i,'revenda'));
				$nome        = trim(pg_result($resx,$i,'nome'));
				$cnpj        = trim(pg_result($resx,$i,'cnpj'));
				$bairro      = trim(pg_result($resx,$i,'bairro'));
				$cidade      = trim(pg_result($resx,$i,'nome_cidade'));
				$fone        = trim(pg_result($resx,$i,'fone'));
				$endereco	 = trim(pg_result($resx,$i,'endereco'));
				$numero 	 = trim(pg_result($resx,$i,'numero'));
				$complemento = trim(pg_result($resx,$i,'complemento'));
				$bairro      = trim(pg_result($resx,$i,'bairro'));
				$cep         = trim(pg_result($resx,$i,'cep'));
				$cidade      = trim(pg_result($resx,$i,'nome_cidade'));
				$estado      = trim(pg_result($resx,$i,'estado'));
				$referencia_produto = trim(pg_result($resx,$i,'referencia_produto1'));
				
				$sql_produto = "
					SELECT   *
					FROM     tbl_produto
					JOIN     tbl_linha ON tbl_produto.linha = tbl_linha.linha and tbl_linha.fabrica = $login_fabrica
					JOIN     tbl_familia ON tbl_familia.familia = tbl_produto.familia and tbl_familia.fabrica = $login_fabrica
					WHERE    tbl_produto.referencia = '$referencia_produto'
					AND      tbl_linha.ativo IS TRUE
					AND      tbl_familia.ativo IS TRUE
					AND      tbl_produto.ativo IS TRUE
					AND      tbl_produto.produto_principal ";

				$res_produto = pg_query ($con,$sql_produto);

				if (pg_num_rows($res_produto)>0){
					
					for ($x = 0; $x < pg_num_rows($res_produto); $x++)
					{	
					
						$produto    = trim(pg_result($res_produto,$x,'produto'));
						$descricao  = trim(pg_result($res_produto,$x,'descricao'));
						$voltagem   = trim(pg_result($res_produto,$x,'voltagem'));
						$referencia = trim(pg_result($res_produto,$x,'referencia'));
						$descricao = str_replace ('"','',$descricao);
						$descricao = str_replace ("'","",$descricao);
						
						$msg_confirma = "if (confirm('Aten��o! Mais de um produto com o mesmo n�mero de s�rie.\\n Verifique se o produto selecionado est� correto.')){";
						?>
						<tr bgcolor="<?=$cor?>">
			
							<td>
								<a href="javascript: void(0)" onclick="fn_envia_serie('referencia', '<?=$referencia?>','<?=$descricao?>','<?=$voltagem?>') " >
								<?
								echo "$referencia";
								echo "</a>";
								?>
							</td>
			
							<td>
								<a href="javascript: <?=$msg_confirma?> produto_referencia.value = '<?=$referencia?>'; produto_descricao.value = '<?=$descricao?>' ; produto_voltagem.value = '<?=$voltagem?>' ;  this.close(); } " >
								<?
								echo "$descricao";
								?>
								</a>
								
							</td>
			
							<td>
								<?
								echo "<a href=\"javascript: $msg_confirma nome.value = '" . $nome . "' ; cnpj.value = '" . $cnpj . "' ; cidade.value = '" . $cidade . "' ; fone.value = '" . $fone . "' ; endereco.value = '" . $endereco . "' ; numero.value = '" . $numero . "' ; complemento.value = '" . pg_result ($res_revenda,0,complemento) . "' ; bairro.value = '" . $bairro . "' ; cep.value = '" . $cep . "' ; estado.value = '" . $estado . "'; email.value = '" . $email . "' ; 
								txt_nome.value = '" . $nome . "' ;	txt_cnpj.value = '" . $cnpj . "' ; txt_cidade.value = '" . $cidade . "' ; txt_fone.value = '" . $fone . "' ; txt_endereco.value = '" . $endereco . "' ; txt_numero.value = '" . $numero . "' ; txt_complemento.value = '" . $complemento . "' ; txt_bairro.value = '" . $bairro . "' ; txt_cep.value = '" . $cep . "' ; txt_estado.value = '" . $estado . "'; txt_data_venda.value = '" . $data_venda . "'; 
								produto_referencia.value = '" . $referencia . "'; produto_descricao.value = '" . $descricao . "' ; produto_voltagem.value = '" . $voltagem . "' ; data_fabricacao.value = '" . $data_fabricacao. "' ; ";
								if ($_GET['revenda_fixo']){
									echo " if (revenda_fixo) { revenda_fixo.style.display='block'; } ";
								}
								echo " this.close(); } \">\n";
								echo "$cnpj";
								echo "</a>";
								?>
				
							</td>
			
							<td>
								<?
								echo $nome;
								?>
							</td>
			
						</tr>
						<?
					}
					
				}else{
					continue;
				}
			
			}
		?>
		</table>
		<?
	}else if (pg_num_rows($resx)==0){
	
		echo "<table width='100%' border='0'>\n";	
			echo "<tr>";	
	
			echo "<td>";
			if ($login_fabrica <> 43) {
				echo "<font face='Arial, Verdana, Times, Sans' size='-2' color='#000000'>$cnpj</font>";
			}
			else {
				echo "<font face='Arial, Verdana, Times, Sans' size='-2' color='#000000'>$referencia</font>\n";
			}
			echo "</td>\n";
	
			echo "<td>\n";
			if ($_GET['forma'] == 'reload') {
				echo "<a href=\"javascript: opener.document.location = retorno + '?revenda=$revenda' ; this.close() ;\" > " ;
			}else{
				if ($login_fabrica <> 43) {
					echo "<a href=\"javascript: nome.value='" . pg_result ($res_revenda,0,nome) . "' ; cnpj.value = '" . pg_result ($res_revenda,0,cnpj) . "' ; cidade.value='" . $cidade . "' ; fone.value='" . pg_result ($res_revenda,0,fone) . "' ; endereco.value='" . pg_result ($res_revenda,0,endereco) . "' ; numero.value='" . pg_result ($res_revenda,0,numero) . "' ; complemento.value='" . pg_result ($res_revenda,0,complemento) . "' ; bairro.value='" . pg_result ($res_revenda,0,bairro) . "' ; cep.value='" . pg_result ($res_revenda,0,cep) . "' ; estado.value='" . pg_result ($res_revenda,0,estado) . "'; email.value='" . pg_result ($res_revenda,0,email) . "' ; 
					txt_nome.value='" . pg_result ($res_revenda,0,nome) . "' ;
					txt_cnpj.value = '" . pg_result ($res_revenda,0,cnpj) . "' ; txt_cidade.value='" . $cidade . "' ; txt_fone.value='" . pg_result ($res_revenda,0,fone) . "' ; txt_endereco.value='" . pg_result ($res_revenda,0,endereco) . "' ; txt_numero.value='" . pg_result ($res_revenda,0,numero) . "' ; txt_complemento.value='" . pg_result ($res_revenda,0,complemento) . "' ; txt_bairro.value='" . pg_result ($res_revenda,0,bairro) . "' ; txt_cep.value='" . pg_result ($res_revenda,0,cep) . "' ; txt_estado.value='" . pg_result ($res_revenda,0,estado) . "'; txt_data_venda.value='" . $data_venda . "'; 
					produto_referencia.value='" . pg_result ($res_produto,0,referencia) . "'; produto_descricao.value='" . pg_result ($res_produto,0,descricao) . "' ; produto_voltagem.value='" . pg_result ($res_produto,0,voltagem) . "' ; data_fabricacao.value='" . $data_fabricacao. "' ; ";
					if ($_GET['revenda_fixo']){
						echo " if (revenda_fixo) { revenda_fixo.style.display='block'; } ";
					}
					echo " this.close(); \">\n";
				} else {
		
				echo "<a href=\"javascript: produto_referencia.value='" . pg_result ($res_produto,0,referencia) . "'; produto_descricao.value='" . pg_result ($res_produto,0,descricao) . "' ; produto_voltagem.value='" . pg_result ($res_produto,0,voltagem) . "' ; ";
				echo " this.close(); \">\n";
				}
			}
			if ($login_fabrica <> 43) {
				echo "<font face='Arial, Verdana, Times, Sans' size='-2' color='#0000FF'>$nome</font>\n";
			}
			else {
				echo "<font face='Arial, Verdana, Times, Sans' size='-2' color='#0000FF'>$descricao</font>\n";
			}
			echo "</a>\n";
			echo "</td>\n";	
			echo "<td>\n";
			if ($login_fabrica <> 43) {
			echo "<font face='Arial, Verdana, Times, Sans' size='-2' color='#000000'>$bairro</font>\n";
			echo "</td>\n";
			echo "<td>\n";
			echo "<font face='Arial, Verdana, Times, Sans' size='-2' color='#000000'>$cidade</font>\n";
			echo "</td>\n";
			}
			echo "</tr>\n";
			
		echo "</table>\n";
		
	}
}
?>
</body>
</html>
