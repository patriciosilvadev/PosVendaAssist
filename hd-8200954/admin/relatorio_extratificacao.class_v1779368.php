<?php
/**
 *
 * relatorio_extratificacao.class.php
 *
 * Relat�rio de Extratifica��o - desenvolvido para Colormaq conforme HD 819523
 *   - atualizado conforme HD 1779368
 *
 * @author  Francisco Ambrozio
 * @version v1779368
 *
 *
 * class relatorioExtratificacao - Classe principal do programa
 *
 *  REGRAS:
 *
 *   - o relat�rio � extra�do de 24 meses retroativo ao m�s atual
 *   - o admin seleciona familia, meses e �ndice
 *   - de acordo com o n�mero de meses selecionado conta as OSs de cada m�s
 *   - taxa de falha: total de OSs (meses) / total produ��o
 *   - popula��o: soma de N meses anteriores
 *
 */

class relatorioExtratificacao
{
	private $login_fabrica;
	private $login_admin;
	private $meses;
	private $familia;
	private $index_irc;
	private $data_inicial;
	private $data_final;
	private $pecas = array();
    private $fornecedores = array();
    private $produtos = array();
    private $datas = array();
	private $msg_erro;
	private $result_view;
	private $sem_peca;
    private $flag_peca = false;
    private $posto;

	private $arr_os = array();
	private $arr_os_anterior = array();
	private $arr_os_15M = array();
	private $arr_total = array();
	private $arr_total_anterior = array();
	private $arr_total_15M = array();
	private $arr_falha = array();
	private $arr_falha_anterior = array();

	private $populacao_1M = array();
	private $populacao_4M = array();
	private $populacao_6M = array();
	private $populacao_15M = array();

	private $os_1M = array();
	private $os_4M = array();
	private $os_6M = array();
	private $os_15M = array();

	private $irc_1M = array();
	private $irc_4M = array();
	private $irc_6M = array();
	private $irc_15M = array();

	private $cfe = array();
	private $cfe_per_unit_prod = array();
	private $cfe_per_unit_fat = array();
	private $cfe_per_unit_15 = array();
	private $faturados = array();

	private $pop_tmp = array(1 => 0, 4 => 0, 6 => 0, 15 => 0);
	
	private $temp_tbls = array(
            'os_serie' => '',
            'numero_serie1' => '',
            'numero_serie2' => '',
            'numero_serie' => ''
        );

	public function __construct()
	{
		$this->bootstrap();
	}

	/**
	 * getters
	 */
	public function getMeses()
	{
		return $this->meses;
	}

	public function getFamilia()
	{
		return $this->familia;
	}

	public function getSemPeca()
	{
		return $this->sem_peca;
	}

	public function getIndexIRC()
	{
		return $this->index_irc;
	}

	public function getDataInicial()
	{
		return $this->data_inicial;
	}

	public function getDataFinal()
	{
		return $this->data_final;
	}

    public function getFornecedores()
    {
        return $this->fornecedores;
    }

    public function getProdutos()
    {
        return $this->produtos;
    }

    public function getPecas()
    {
        return $this->pecas;
    }

    public function getPostos()
    {
        return $this->posto;
    }

	public function getMsgErro()
	{
		return $this->msg_erro;
	}

	public function getResultView()
	{
		return $this->result_view;
	}

	/**
	 * setters
	 */
	private function setMeses()
	{
		if (!empty($_POST["meses"])) {
			$this->meses = $_POST["meses"];
		}
        return $this;
	}

	private function setFamilia()
	{
		if (!empty($_POST["familia"])) {
			$this->familia = $_POST["familia"];
		}
        return $this;
	}

	private function setIndexIRC()
	{
		if (!empty($_POST["index_irc"])) {
			$this->index_irc = $_POST["index_irc"];
		} else {
			$this->index_irc = 1;
		}
        return $this;
	}

	private function setDataInicial()
	{
		if (!empty($_POST["ano_pesquisa"])) {
			$ano = $_POST["ano_pesquisa"];
		}

		if (!empty($_POST["mes_pesquisa"])) {
			$mes = $_POST["mes_pesquisa"];
		}

		if (!empty($ano) and !empty($mes)) {
			$this->data_inicial = $ano . '-' . $mes . '-01';
		} else {
			date_default_timezone_set('America/Sao_Paulo');
			$this->data_inicial = date('Y-m-01');
		}
        return $this;
	}

	private function setDataFinal()
	{
		$this->is_con();
		$data_inicial = $this->getDataInicial();
		$query = pg_query("select to_char(('$data_inicial'::date + interval '1 month') - interval '1 day', 'YYYY-MM-DD')::date");
		$this->data_final = pg_fetch_result($query, 0, 0);
        return $this;

	}

	private function setPeca01()
	{
		if (!empty($_POST['peca01'])) {
			$this->is_con();
			$arr_peca01 = explode('-', $_POST['peca01']);
			$referencia = trim($arr_peca01[0]);

			$sql = "SELECT peca FROM tbl_peca WHERE referencia = '$referencia' AND fabrica = $this->login_fabrica";
			$query = pg_query($sql);

			if (pg_num_rows($query) == 0) {
				$this->msg_erro = 'Pe�a 1 inv�lida.';
				return false;
			}

			$this->pecas[] = pg_fetch_result($query, 0, 'peca');
		}
        return $this;
	}

	private function setPeca02()
	{
		if (!empty($_POST['peca02'])) {
			$this->is_con();
			$arr_peca02 = explode('-', $_POST['peca02']);
			$referencia = trim($arr_peca02[0]);

			$sql = "SELECT peca FROM tbl_peca WHERE referencia = '$referencia' AND fabrica = $this->login_fabrica";
			$query = pg_query($sql);

			if (pg_num_rows($query) == 0) {
				$this->msg_erro = 'Pe�a 2 inv�lida.';
				return false;
			}

			$this->pecas[] = pg_fetch_result($query, 0, 'peca');
		}
        return $this;
	}

    public function setPecas($pecas = array())
    {
        if (!empty($pecas)) {
            $this->pecas = $pecas;
        }
        elseif (!empty($_POST['peca'])) {
            $this->pecas = $_POST['peca'];
            $this->flag_peca = true;
        }
	foreach($this->pecas as $key => $value){
		if(empty($value)) {
			unset($this->pecas[$key]);
		}
	}
        return $this;
    }

    private function setPosto()
    {
        if (!empty($_POST['posto'])) {
        	$this->posto = $_POST['posto'];

            //$arr_posto = explode('-', $_POST['posto']);

            /*if (!empty($arr_posto[0])) {
                $this->is_con();
                $codigo_posto = trim($arr_posto[0]);
                $sql = "SELECT tbl_posto.posto FROM tbl_posto
                        JOIN tbl_posto_fabrica ON tbl_posto.posto = tbl_posto_fabrica.posto
                        AND tbl_posto_fabrica.fabrica = {$this->login_fabrica}
                        WHERE codigo_posto = '{$codigo_posto}'";
                $query = pg_query($sql);

                if (pg_num_rows($query) == 1) {
                    $this->posto = pg_fetch_result($query, 0, 'posto');
                }
            }*/
        }

        return $this;
    }

	private function setCFE($value)
	{
		$this->cfe[] = $value;
    }

    public function setFornecedores($fornecedores = null)
    {
        if (!empty($_POST['fornecedor'])) {
            $this->fornecedores = $_POST['fornecedor'];
        }
        elseif (!empty($fornecedores)) {
            $this->fornecedores = $fornecedores;
        }

        return $this;
    }

    private function setProdutos()
    {
        if (!empty($_POST['produto'])) {
            $this->produtos = $_POST['produto'];
        }

        return $this;
    }

    private function setSemPeca()
    {
        if (!empty($_POST['sem_peca'])) {
            $this->sem_peca = $_POST['sem_peca'];
        }

        return $this;
    }

    /**
     * @param array $data Array 'dia|mes|ano' => 'valor'
     */
    public function setDatas($datas = null)
    {

        if (!empty($datas)) {
            $this->datas = $datas;
            return $this;
        }

        $datas = array(
            'dia_01',
            'mes_01',
            'ano_01',
            'dia_02',
            'mes_02',
            'ano_02',
            'dia_03',
            'mes_03',
            'ano_03',
        );

        foreach ($datas as $d) {
            if (!empty($_POST[$d])) {
                list($tipo, $i) = explode('_', $d);
                $this->datas[(int) $i][$tipo] = $_POST[$d];
            }
        }

        return $this;
    }

    /**
     * Monta a condi��o de fornecedores
     * @return boolean|string
     */
    public function montaCondFornecedores()
    {
        if (empty($this->fornecedores)) {
            return false;
        }

        $cond = " AND (tbl_ns_fornecedor.nome_fornecedor = '";
        $cond.= implode("' OR tbl_ns_fornecedor.nome_fornecedor = '", $this->fornecedores);
        $cond.= "')";

        return $cond;
    }

    private function montaCondProdutos()
    {
    	if (empty($this->produtos)) {
            return false;
        }
        
        if ($this->login_fabrica == '50') {
            $tbl = '';
        } else {
            $tbl = 'tbl_produto.';
        }

        $cond = " AND {$tbl}produto IN(";
        $cond.= implode(",", $this->produtos);
        $cond.= ")";

        return $cond;
    }

    /**
     * Monta as condi��es com as datas as serem pesquisada
     * @return boolean|array
     */
    public function montaCondDatas()
    {

        if (empty($this->datas)) {
            return false;
        }

        $cond = array();
        $count = count($this->datas);

        if ($count == 1) {
            $cond[] = $this->subArrayData($this->datas);
        } else {
            $datas = $this->matrizData();

            foreach ($datas as $data) {
                $param[1] = $data;
                $cond[] = $this->subArrayData($param);
            }
        }

        return $cond;
    }

    private function matrizData()
    {
        $datas = array();
        $dias = array();
        $meses = array();
        $anos = array();
        $i = 0;

        foreach ($this->datas as $val) {
            if (array_key_exists('dia', $val)) {
                $dias[] = $val['dia'];
            } else {
                $dias[] = '00';
            }

            if (array_key_exists('mes', $val)) {
                $meses[] = $val['mes'];
            } else {
                $meses[] = '00';
            }

            if (array_key_exists('ano', $val)) {
                $anos[] = $val['ano'];
            } else {
                $anos[] = '00';
            }
        }


        foreach ($dias as $d) {
            foreach ($meses as $m) {
                foreach ($anos as $a) {
                    if ($d <> "00") {
                        $datas[$i]['dia'] = $d;
                    }
                    if ($m <> "00") {
                        $datas[$i]['mes'] = $m;
                    }
                    if ($a <> "00") {
                        $datas[$i]['ano'] = $a;
                    }

                    if (count($datas[$i]) > 1) {
                        $i++;
                    }
                }
            }
        }

        return $datas;

    }

    private function subArrayData(array $data)
    {
        $return = '';

        if (array_key_exists('dia', $data[1]) and array_key_exists('mes', $data[1]) and array_key_exists('ano', $data[1])) {
            $strData = $data[1]['ano'] . '-' . $data[1]['mes'] . '-' . $data[1]['dia'];
            $return = " tbl_ns_fornecedor.data_fabricacao = '$strData' ";
        }
        elseif (array_key_exists('dia', $data[1]) and array_key_exists('mes', $data[1])) {
            $return = " ((SELECT EXTRACT(DAY FROM tbl_ns_fornecedor.data_fabricacao)) = '{$data[1]['dia']}' and (SELECT EXTRACT(MONTH FROM tbl_ns_fornecedor.data_fabricacao)) = '{$data[1]['mes']}') ";
        }
        elseif (array_key_exists('dia', $data[1]) and array_key_exists('ano', $data[1])) {
            $return = " ((SELECT EXTRACT(DAY FROM tbl_ns_fornecedor.data_fabricacao)) = '{$data[1]['dia']}' and (SELECT EXTRACT(YEAR FROM tbl_ns_fornecedor.data_fabricacao)) = '{$data[1]['ano']}') ";
        }
        elseif (array_key_exists('mes', $data[1]) and array_key_exists('ano', $data[1])) {
            $return = " ((SELECT EXTRACT(MONTH FROM tbl_ns_fornecedor.data_fabricacao)) = '{$data[1]['mes']}' and (SELECT EXTRACT(YEAR FROM tbl_ns_fornecedor.data_fabricacao)) = '{$data[1]['ano']}') ";
        }

        return $return;

    }

	private function setFaturados()
	{
		$sql1 = "
				select count(tbl_numero_serie.serie) as total,
				extract (month from data_venda) as mes,
				extract (year from data_venda) as ano
				into temp tf1
				from tbl_numero_serie
				join tbl_produto using(produto)
				where tbl_produto.familia = $this->familia
				and fabrica = $this->login_fabrica
				and fabrica_i = $this->login_fabrica
				and data_venda between to_char(('$this->data_inicial'::date - interval '23 month'), 'YYYY-MM-DD')::date and '$this->data_final'
				group by mes,ano order by ano,mes
				";

		$sql2 = "
				select 0 as total,
				extract(month from to_char(('$this->data_inicial'::date - interval '23 month'), 'YYYY-MM-DD')::date + s * interval '1 month') as mes,
				extract(year from to_char(('$this->data_inicial'::date - interval '23 month'), 'YYYY-MM-DD')::date + s * interval '1 month') as ano
				into temp tf2
				from generate_series(0, 23) as s
				";

		$sql3 = "CREATE TEMP TABLE rf as SELECT mes, ano from tf1 union select mes, ano from tf2 order by ano, mes;
					ALTER TABLE rf add total text;";

		$query = pg_query($sql1);
		$query = pg_query($sql2);
		$query = pg_query($sql3);

		$begin = pg_query("BEGIN");
		$sql_updates = "update rf set total = tf1.total from tf1 where rf.mes = tf1.mes and rf.ano = tf1.ano;
						update rf set total = 0 where total is null;";
		$query = pg_query($sql_updates);
		if (!pg_last_error()) {
			$commit = pg_query("COMMIT");
		} else {
			$rollback = pg_query("ROLLBACK");
			return '0';
		}

		$query = pg_query("SELECT total from rf order by ano, mes");
		while ($fetch = pg_fetch_assoc($query)) {
			$this->faturados[] = $fetch['total'];
		}

	}

	private function setCFEPerUnit15()
	{
		if (empty($this->cfe)) {
			return false;
		}

		if (empty($this->meses)) {
			return false;
		}

		if (empty($this->arr_os_15M)) {
			return false;
		}

		if (empty($this->arr_total_15M)) {
			return false;
		}

		foreach ($this->arr_os_15M as $key => $value) {
			$tot = $this->arr_total_15M[$key];
			$producao[$value['mes']] = $tot;
		}

		array_pop($producao);
		$producao = array_values($producao);
		$count_p = count($producao);
		$count_c = count($this->cfe);
		$diffk = $count_p - $count_c;

		foreach ($this->cfe as $idx => $value) {
			$producao_sum = 0;
			$start = $idx + $diffk;
			$step = 0;

			for ($i = $start; $i >= 0; --$i) {
				if ($step == 15) {
					break;
				} else {
					$step++;
				}
				$producao_sum+= $producao[$i];
			}

			$resdiv = bcdiv($this->cfe[$idx], $producao_sum, 2);

			if (empty($resdiv)) {
				$resdiv = 0;
			}

			$this->cfe_per_unit_15[] = $resdiv;
		}

	}

	private function setArrOS15M()
	{
		$month = $this->setIndexesArray15();
		$tmp_arr_os_15M = array();

		if (!empty($this->arr_os)) {
			$tmp_arr_os_15M = $this->arr_os;
		}

		$arr_result = $this->producao($month);

		foreach ($arr_result as $k => $fetch) {
			$mes = $fetch["mes"];
			$ano = $fetch["ano"];

			$this->arr_os[$k] = array("mes" => sprintf("%02d", $mes) . '/' . $ano, "os" => array());
			$this->popOS($ano, $mes, $k, false);
		}

		$this->arr_os_15M = $this->arr_os;
		$this->arr_os = $tmp_arr_os_15M;
		unset($tmp_arr_os_15M);

	}

	private function setArrTotal15M()
	{
		$month = $this->setIndexesArray15();
		$arr_result = $this->producao($month);

		foreach ($arr_result as $k => $fetch) {
			$this->arr_total_15M[$k] = $fetch['total'];
		}
	}

	private function setIndexesArray15()
	{
		$data_obj = new DateTime($this->data_inicial);
		$sub = $data_obj->sub(new DateInterval('P38M'));
		$data_obj2 = new DateTime($sub->format('Y-m-d'));
		$data_corte = new DateTime('2010-01-01');

		if ($data_obj2 < $data_corte) {
			$date1 = date(strtotime($data_corte->format('Y-m-d')));
			$date2 = date(strtotime($this->data_inicial));

			$difference = $date2 - $date1;
			$months = floor($difference / 86400 / 30 );

			return (string) $months;
		} else {
			return '39';
		}
	}

	private function setLoginFabrica()
	{
		global $login_fabrica;

		if (empty($login_fabrica)) {
			echo '<meta http-equiv="Refresh" content="0 ; url=http://www.telecontrol.com.br" />';
			exit;
		}

		$this->login_fabrica = $login_fabrica;
        return $this;
	}

	private function setLoginAdmin()
	{
		global $login_admin;


		$this->login_admin = $login_admin;
		return $this;
	}


	/**
	 * Inicializa os atributos necess�rios para a execu��o do programa
	 */
	private function bootstrap()
	{
		$this->msg_erro = '';
		$this->result_view = '';

		$this->setLoginFabrica()
		->setLoginAdmin()
             ->setMeses()
             ->setFamilia()
             ->setIndexIRC()
             ->setDataInicial()
             ->setDataFinal()
             ->setPeca01()
             ->setPeca02()
             ->setPecas()
             ->setPosto()
             ->setFornecedores()
             ->setProdutos()
             ->setSemPeca()
             ->setDatas();

	}

	/**
	 * Verifica se existe conex�o com o banco de dados
	 */
	private function is_con()
	{
		global $con;
		if (!is_resource($con)) {
			echo 'ERRO: conex�o com banco de dados!';
			exit;
		}
	}

	/**
	 * Verifica se est� submetendo dados
	 */
	private function isRequest()
	{
		if (!empty($_POST['btn_acao'])) {
			if ($_POST['btn_acao'] == "Consultar" and $this->validate()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Valida��o de campos obrigat�rios
	 */
	private function validate()
	{
		$campos = array();

		if (empty($_POST['ano_pesquisa'])) {
			$campos[] = 'Ano';
		}

		if (empty($_POST['mes_pesquisa'])) {
			$campos[] = 'M�s';
		}

		if (empty($_POST['familia'])) {
			$campos[] = 'Fam�lia';
		}

		if (empty($_POST['meses'])) {
			$campos[] = 'Qtde Meses';
		}

		if (!empty($campos)) {
			$this->msg_erro = 'Verifique os seguintes campos: ' . implode(', ', $campos);
			return false;
		}

        if ((!empty($this->fornecedores) or !empty($this->datas)) and empty($this->pecas)) {
            $this->msg_erro = 'Para pesquisar por fornecedor/datas � preciso selecionar pela menos uma pe�a';
            return false;
        }
        elseif (!empty($this->datas)) {
            foreach ($this->datas as $valid) {
                if (count($valid) < 2) {
                    $this->msg_erro = 'Para pesquisar por datas � necess�rio selecionar pelo menos 2 par�metros de dia, m�s ou ano.';
                    return false;
                }
            }
        }

		if (!empty($this->msg_erro)) {
			return false;
		}

		return true;

	}

	private function preparaTabelaTemp()
	{
		$this->is_con();

		$login_admin = $this->login_admin;

        if ($this->login_fabrica == '50') {
            $tbl = '';
        } else {
            $tbl = 'tbl_os.';
        }

		if($this->sem_peca){
			$condSemPeca = ' AND '. $tbl . 'defeito_constatado = ' . $this->sem_peca;
		}

		$condPosto = '';

		if (!empty($this->posto)) {
		    $condPosto = ' AND ' . $tbl . 'posto in(' . implode(',',$this->posto).')';
		}

		$condProdutos = $this->montaCondProdutos();

		if (!empty($this->pecas)) {
		    $cond = implode(', ', $this->pecas);
		    $condFornecedores = $this->montaCondFornecedores();
		    $condProdutos = $this->montaCondProdutos();
		    $condDatas = $this->montaCondDatas();

		    $cond_datas = '';

		    if (!empty($condDatas)) {
                $cond_datas = ' AND (';
                $cond_datas.= implode('OR', $condDatas);
                $cond_datas.= ')';
		    }

			$prepare = pg_prepare("tmp_peca_serie","select os,os_produto,peca into temp peca_serie_$login_admin from tbl_os_item join tbl_os_produto using(os_produto) where fabrica_i = $1 and peca in ($cond)" );
			if (!empty($condFornecedores) or !empty($condDatas)) {
				$prepare = pg_prepare("tmp_fornecedor_serie","select numero_serie into temp fornecedor_serie_$login_admin from tbl_ns_fornecedor  WHERE fabrica = $1 AND peca in ($cond) $condFornecedores $cond_datas" );
			}
		}
		
		$return = array(
                'os_serie' => false,
                'numero_serie1' => false,
                'numero_serie2' => false,
            );
		
		if ($this->login_fabrica == '50') {
            $this->temp_tbls = array(
                'os_serie' => 'colormaq_os_serie_' . $this->familia,
                'numero_serie1' => 'colormaq_numero_serie1_' . $this->familia,
                'numero_serie2' => 'colormaq_numero_serie2_' . $this->familia,
                'numero_serie' => 'colormaq_numero_serie_' . $this->familia,
            );

            if (!empty($condProdutos) or !empty($condSemPeca) or !empty($condPosto)) {
                $sql = "SELECT os, 
                               data_abertura, 
                               serie, 
                               produto 
                            INTO TEMP os_serie_$login_admin 
                            FROM {$this->temp_tbls['os_serie']} 
                            WHERE 1=1 
                            $condProdutos $condSemPeca $condPosto";
                $qry = pg_query($sql);
                $this->temp_tbls['os_serie'] = 'os_serie_' . $login_admin;
            }

            if (!empty($condProdutos)) {
                $sql = "SELECT numero_serie, 
                               serie, 
                               produto, 
                               data_fabricacao 
                            INTO TEMP numero_serie1_$login_admin 
                            FROM {$this->temp_tbls['numero_serie1']} 
                            WHERE 1=1 
                            $condProdutos";
                $qry = pg_query($sql);
                $this->temp_tbls['numero_serie1'] = 'numero_serie1_' . $login_admin;

                $sql = "SELECT numero_serie, 
                               substr(serie,1,length(serie) -1) as serie, 
                               produto, 
                               data_fabricacao 
                            INTO TEMP numero_serie2_$login_admin 
                            FROM {$this->temp_tbls['numero_serie2']} 
                            WHERE 1=1 $condProdutos";
                $qry = pg_query($sql);
                $this->temp_tbls['numero_serie2'] = 'numero_serie2_' . $login_admin;
                
                $this->temp_tbls['numero_serie'] = 'numero_serie_' . $login_admin;
            }
		} else {
            $prepare = pg_prepare("tmp_os_serie","SELECT os,data_abertura, serie, tbl_os.produto,fabrica INTO TEMP os_serie_$login_admin from tbl_os join tbl_produto on tbl_os.produto = tbl_produto.produto where fabrica = $1 and familia = $2 and fabrica_i = $1 $condProdutos $condSemPeca $condPosto");
            $prepare = pg_prepare("tmp_numero_serie1","SELECT numero_serie, serie, produto,data_fabricacao INTO TEMP numero_serie1_$login_admin from tbl_numero_serie join tbl_produto using(produto) where fabrica = $1 and familia = $2 and fabrica_i = $1 $condProdutos;  " );
            $prepare = pg_prepare("tmp_numero_serie2"," SELECT numero_serie, substr(serie,1,length(serie) -1) as serie, produto,data_fabricacao INTO TEMP numero_serie2_$login_admin from tbl_numero_serie join tbl_produto using(produto) where fabrica = $1 and familia = $2 and fabrica_i = $1 and data_fabricacao between '2013-07-25' and '2013-09-13' $condProdutos;" );

            $return = array(
                'os_serie' => true,
                'numero_serie1' => true,
                'numero_serie2' => true,
            );

            $this->temp_tbls = array(
                'os_serie' => 'os_serie_' . $login_admin,
                'numero_serie1' => 'numero_serie1_' . $login_admin,
                'numero_serie2' => 'numero_serie2_' . $login_admin,
                'numero_serie' => 'numero_serie_' . $login_admin,
            );
        }

        return $return;

	}

	/**
	 * Prepare das consultas que ser�o executadas para trazer
	 *   o n�mero de OSs m�s a m�s
	 */
	private function prepareStatements()
	{
		$this->is_con();

		$login_admin = $this->login_admin;
		$prepare = pg_prepare("ultimo_dia_do_mes", "select to_char(($1::date + interval '1 month') - interval '1 day', 'YYYY-MM-DD')::date as ultimo_dia");

		if($this->sem_peca){
			$condSemPeca = " AND tbl_os.defeito_constatado = ".$this->sem_peca;
		}

			$condPosto = '';

		if (!empty($this->posto)) {
		    $condPosto = 'AND tbl_os.posto in(' . implode(',',$this->posto).')';
		}

		$condProdutos = $this->montaCondProdutos();

		if (empty($this->pecas)) {
			$prepare = pg_prepare("total_os", "select count(distinct(os)) as total from {$this->temp_tbls['os_serie']} join {$this->temp_tbls['numero_serie']} USING(serie,produto) where data_fabricacao between $1 and $2 and data_abertura between $3 and $4 ");
			//die("select count(os) as total from {$this->temp_tbls['os_serie']} join {$this->temp_tbls['numero_serie']} USING(serie,produto) where data_fabricacao between $1 and $2 and data_abertura between $3 and $4");
		} else {
		    $cond = implode(', ', $this->pecas);
		    $condFornecedores = $this->montaCondFornecedores();
		    $condProdutos = $this->montaCondProdutos();
		    $condDatas = $this->montaCondDatas();

		    if (!empty($condFornecedores) or !empty($condDatas)) {
                $joinNSFornecedor = " JOIN fornecedor_serie_$login_admin ON fornecedor_serie_$login_admin.numero_serie = {$this->temp_tbls['numero_serie']}.numero_serie  ";
		    } else {
                $joinNSFornecedor = '';
		    }

		    $cond_datas = '';

		    if (!empty($condDatas)) {
                $cond_datas = ' AND (';
                $cond_datas.= implode('OR', $condDatas);
                $cond_datas.= ')';
		    }
		    
		   

		    $prepare = pg_prepare("total_os", "select count(distinct({$this->temp_tbls['os_serie']}.os)) as total from {$this->temp_tbls['os_serie']} join {$this->temp_tbls['numero_serie']} using(serie,produto) JOIN peca_serie_$login_admin USING(os) $joinNSFornecedor where data_fabricacao between $1 and $2 and data_abertura between $3 and $4 ");
		    
		     //die("select count(distinct(os_serie_$login_admin.os)) as total from {$this->temp_tbls['os_serie']} join {$this->temp_tbls['numero_serie']} using(serie,produto) JOIN peca_serie_$login_admin USING(os) $joinNSFornecedor where data_fabricacao between $1 and $2 and data_abertura between $3 and $4 ");

		}

	}

	/**
	 * Executa consultas previamente preparadas
	 *
	 * @param string $query nome da prepared statement
	 * @param array $params par�metros da consulta
	 *
	 * @return array resultado da query ou string '0' se nada
	 *
	 */
	private function executePreparedStatements($query, $params)
	{
		if (!is_array($params)) {
			echo 'Erro: tipo n�o suportado.';
			exit;
		}
		$x = pg_execute($query, $params);

		if (pg_num_rows($x)) {
			return pg_fetch_all($x);
		} else {
			return '0';
		}

	}

	/**
	 *
	 * @param array $array Array a ser reduzido
	 * @param int $indices N�mero de �ndices a serem mantidos
	 * @param int $ordem se 0, retira os elementos do in�cio do array
	 *     qualquer outro valor, retira do final
	 *
	 */
	private function arrayReduce($array, $indices, $ordem = 1)
	{
		if (!is_array($array)) {
			return false;
		}

		if (!is_int($indices)) {
			return $array;
		}

		if (!is_int($ordem)) {
			$ordem = 1;
		}


		if ($ordem == 0) {
			$php_function = 'array_shift';
		} else {
			$php_function = 'array_pop';
		}

		$count = count($array);
		$end = $count - $indices;

		if ($end < 0) {
			return false;
		}

		for ($i = 0; $i < $end; $i++) {
			$php_function($array);
		}

		return $array;

	}

	/**
	 * Obt�m o n�mero de produtos produzidos nos �ltimos X meses
	 *   usando como base a tbl_custo_falha
	 *
	 * @param string $month par�metro usado no select - padrao '23'
	 * @return array resultado ou string '0' se nada
	 *
	 */
	private function producao($month = '23')
	{
		$this->is_con();

		if (empty($this->familia)) {
			die('Erro interno.');
		}

		$sql1 = "
				select count(tbl_numero_serie.serie) as total,
				extract (month from data_fabricacao) as mes,
				extract (year from data_fabricacao) as ano
				into temp t1
				from tbl_numero_serie
				join tbl_produto using(produto)
				where tbl_produto.familia = $this->familia
				and fabrica = $this->login_fabrica
				and fabrica_i = $this->login_fabrica
				and data_fabricacao between to_char(('$this->data_inicial'::date - interval '$month month'), 'YYYY-MM-DD')::date and '$this->data_final'
				group by mes,ano order by ano,mes
				";

		$sql2 = "
				select 0 as total,
				extract(month from to_char(('$this->data_inicial'::date - interval '$month month'), 'YYYY-MM-DD')::date + s * interval '1 month') as mes,
				extract(year from to_char(('$this->data_inicial'::date - interval '$month month'), 'YYYY-MM-DD')::date + s * interval '1 month') as ano,
				0 as cfe
				into temp t2
				from generate_series(0, $month) as s
				";

		$sql4 = "SELECT qtde_produto_produzido as total, cf.mes, cf.ano, cf.cfe
				 INTO temp t1 from tbl_custo_falha cf
				 JOIN t2 ON t2.mes = cf.mes AND t2.ano = cf.ano
				 WHERE cf.fabrica = $this->login_fabrica and cf.familia = $this->familia";


		$sql3 = "CREATE TEMP TABLE r as SELECT mes, ano from t1 union select mes, ano from t2 order by ano, mes;
					ALTER TABLE r add total text;
					ALTER TABLE r add cfe text";

		$query = pg_query($sql2);
		$query = pg_query($sql4);
		$query = pg_query($sql3);

		$begin = pg_query("BEGIN");
		$sql_updates = "update r set total = t1.total, cfe = t1.cfe from t1 where r.mes = t1.mes and r.ano = t1.ano;
						update r set total = 0 where total is null;
						update r set cfe = 0 where cfe is null";
		$query = pg_query($sql_updates);
		if (!pg_last_error()) {
			$commit = pg_query("COMMIT");
		} else {
			$rollback = pg_query("ROLLBACK");
			return '0';
		}

		$query = pg_query("SELECT * from r order by ano, mes");
		$drop = pg_query("DROP TABLE t1; DROP TABLE t2; DROP TABLE r");

		if (pg_num_rows($query) > 0) {
			return pg_fetch_all($query);
		} else {
			return '0';
		}
	}

	/**
	 * Popula arrays populacao
	 *
	 * @param int $m qtde de meses [1, 4, 6 ou 15]
	 * @param int $curr ind�ce atual de $this->arr_total_15M
	 *
	 */
	private function populacao($m, $curr)
	{
		$possiveis = array(1, 4, 6, 15);

		if (!in_array($m, $possiveis)) {
			echo 'Erro: par�metro inv�lido - ' , $m , '!';
			exit;
		}

		$count = count($this->arr_total_15M);
		$curr = ($count - 24) + $curr;

		$mpopulacao = 'populacao_' . $m . 'M';

		$this->pop_tmp[$m] = 0;

		if ($m == 1) {
			$this->pop_tmp[$m] = $this->arr_total_15M[$curr - 1];
		} else {

			$ctl = 0;
			$s = $curr - 1;
			for ($i = $s; $i >= 0; $i--) {

				if ($ctl == $m) {
					break;
				}

				$this->pop_tmp[$m]+= $this->arr_total_15M[$i];

				$ctl++;

			}

		}

		array_push($this->$mpopulacao, $this->pop_tmp[$m]);

	}

	/**
	 * Monta popula��o de OSs lan�adas nos meses anteriores
	 *
	 *   Quando 1M pega a "soma" do m�s anterior, nos outros casos, quando (k + 1) >= N
	 *     retrocede N meses - em cada m�s que retrocede, aumenta um �ndice at�
	 *     chegar no �ndice N.
	 *
	 * @param int $m qtde de meses [1, 4, 6 ou 15]
	 * @param int $curr index atual do array $this->arr_os_15M
	 *
	 */
	private function populacaoPopOS($m, $curr)
	{
		$possiveis = array(1, 4, 6, 15);

		if (!in_array($m, $possiveis)) {
			echo 'Erro: par�metro inv�lido - ' , $m , '!';
			exit;
		}

		$count = count($this->arr_os_15M);
		$curr = ($count - 24) + $curr;

		$os = 'os_' . $m . 'M';

		$this->pop_tmp[$m] = 0;

		if ($m == 1) {
			$this->pop_tmp[$m] = $this->arr_os_15M[$curr - 1]["os"][0];
		} else {
			/**
			 *
			 * volta um indice no array - soma um valor
			 * volta +um indice - soma um valor +1
			 * para cada indice que retrocede soma +1
			 *
			 */
			$ctl = 0;
			$s = $curr - 1;
			for ($i = $s; $i >= 0; $i--) {

				if ($ctl == $m) {
					break;
				}

				for ($j = 0; $j <= $ctl; $j++) {
					$this->pop_tmp[$m]+= $this->arr_os_15M[$i]["os"][$j];
				}

				$ctl++;

			}
		}

		array_push($this->$os, $this->pop_tmp[$m]);

	}

	/**
	 * Quantas OS foram lan�adas nos N meses ap�s a fabrica��o do produto
	 *
	 * @param int $ano
	 * @param int $mes
	 * @param int $idx index atual do array $this->arr_os
	 *
	 */
	private function popOS($ano, $mes, $idx, $break = true)
	{
		if (empty($this->meses)) {
			return 0;
		}

		$mes_loop = $mes + 1;
		$ano_loop = $ano;

		for ($i = 1; $i <= $this->meses; $i++) {
			$data_fabricacao_inicio = $ano . '-' . sprintf("%02d", $mes) . '-01';
			$x = $this->executePreparedStatements("ultimo_dia_do_mes", array($data_fabricacao_inicio));
			$data_fabricacao_final = $x[0]["ultimo_dia"];


			if ($mes_loop > 12) {
				$ano_loop+= 1;
				$mes_loop = 1;
			}

			if ($i == 1) {
				$period = 'P2M';
			} else {
				$period = 'P1M';
			}

			$data_abertura_inicio = $ano_loop . '-' . sprintf("%02d", $mes_loop) . '-01';

			$data1 = new DateTime($data_abertura_inicio);
			$data2 = new DateTime($this->data_inicial);
			$data2->add(new DateInterval($period));

			if ($data1 == $data2) {
				break;
			}
			elseif ($idx == 23 and $i == 2 and true === $break) {
				break;
			}

			$x = $this->executePreparedStatements("ultimo_dia_do_mes", array($data_abertura_inicio));
			$data_abertura_final = $x[0]["ultimo_dia"];

			$x_os = $this->executePreparedStatements("total_os", array( $data_fabricacao_inicio, $data_fabricacao_final, $data_abertura_inicio, $data_abertura_final));
			    if (is_array($x_os)) {
				    $total_os = $x_os[0]["total"];
			    } else {
				$total_os = $x_os;
			    }

			array_push($this->arr_os[$idx]["os"], $total_os);

			$mes_loop++;

		}

	}

	/**
	 * Preenche os arrays populacao_{1,4,6,15}M e os_{1,4,6,15}M
	 */
	private function irc($m, $idx)
	{
		$possiveis = array(1, 4, 6, 15);

		if (!in_array($m, $possiveis)) {
			echo 'Erro: par�metro inv�lido - ' , $m , '!';
			exit;
		}

		$irc = 'irc_' . $m . 'M';
		$populacao = 'populacao_' . $m . 'M';
		$os = 'os_' . $m . 'M';

		switch ($m) {
			case 1:
				$populacao = $this->populacao_1M[$idx];
				$os = $this->os_1M[$idx];
				break;
			case 4:
				$populacao = $this->populacao_4M[$idx];
				$os = $this->os_4M[$idx];
				break;
			case 6:
				$populacao = $this->populacao_6M[$idx];
				$os = $this->os_6M[$idx];
				break;
			case 15:
				$populacao = $this->populacao_15M[$idx];
				$os = $this->os_15M[$idx];
				break;
		}

		$res_div = ($os / $populacao) * 100;
		$res_mult = bcmul($res_div, $this->index_irc, 2);

		array_push($this->$irc, $res_mult);
	}

	/**
	 * Monta a extratifica��o dos dados
	 *
	 * @param array resultado obtido de $this->producao()
	 *
	 */
	private function extratifica($resultado)
	{
		if (!is_array($resultado)) {
			echo 'Erro: par�metro inv�lido!';
			exit;
		}

		$login_admin = $this->login_admin;

		$temps = $this->preparaTabelaTemp();
		
		if (true === $temps['os_serie']) {
            $this->executePreparedStatements('tmp_os_serie',array($this->login_fabrica,$this->familia));
        }

        if (true === $temps['numero_serie1']) {
            $this->executePreparedStatements('tmp_numero_serie1',array($this->login_fabrica,$this->familia));
        }

        if (true === $temps['numero_serie2']) {
            $this->executePreparedStatements('tmp_numero_serie2',array($this->login_fabrica,$this->familia));
        }

        if (($this->temp_tbls['numero_serie1'] == "numero_serie1_$login_admin") and ($this->temp_tbls['numero_serie2'] == "numero_serie2_$login_admin")) {
            $sql = "SELECT numero_serie,serie, produto,data_fabricacao INTO TEMP numero_serie_$login_admin FROM (select * from numero_serie1_$login_admin UNION select * from numero_serie2_$login_admin) x";
            $res = pg_query($sql);
        }

		if (!empty($this->pecas)) {
			$this->executePreparedStatements('tmp_peca_serie',array($this->login_fabrica));
			$condFornecedores = $this->montaCondFornecedores();
		    	$condDatas = $this->montaCondDatas();
		    	if (!empty($condFornecedores) or !empty($condDatas)) {
				$this->executePreparedStatements('tmp_fornecedor_serie',array($this->login_fabrica));
				//echo pg_last_error();
			}
		}

		$this->prepareStatements();
		$this->setArrOS15M();
		$this->setArrTotal15M();

		$apopulacao = array(1, 4, 6, 15);

		foreach ($resultado as $k => $fetch) {
			$mes = $fetch["mes"];
			$ano = $fetch["ano"];
			$total = $fetch["total"];
			$cfe = $fetch["cfe"];

			$this->arr_total[$k] = $total;
			$this->arr_os[$k] = array("mes" => sprintf("%02d", $mes) . '/' . $ano, "os" => array());
			$this->setCFE($cfe);

			$this->popOS($ano, $mes, $k);

			foreach ($apopulacao as $v) {
				$this->populacao($v, $k);
				$this->populacaoPopOS($v, $k);
				$this->irc($v, $k);
			}

		}

		$this->setFaturados();

		foreach ($this->cfe as $k => $cfe) {
			$div = bcdiv($cfe, $this->arr_total[$k], 2);
			$div_fat = bcdiv($cfe, $this->faturados[$k], 2);
			$this->cfe_per_unit_prod[] = (!empty($div)) ? $div : '0.00';
			$this->cfe_per_unit_fat[] = (!empty($div_fat)) ? $div_fat : '0.00';
		}

	}

	/**
	 * Gera��o dos gr�ficos
	 */
	private function geraGraficoTaxaFalha($meses, $taxa_falha, $oss, $titulo)
	{
        $titulo = preg_replace("/[^a-zA-Z ]/", "", strtr($titulo, "����������������������������", "aaaaeeiooouucnAAAAEEIOOOUUCN"));

		$script = '
		<script src="js/highcharts.js"></script>
		<script src="js/exporting.js"></script>
		<div id="taxa_falha" style="min-width: 1500px; height: 400px; margin-top: 30px; display: none;"></div>
		';

		$script.= "
		<script>
			$(function () {
				var chart;
				$(document).ready(function() {
					chart = new Highcharts.Chart({
						chart: {
							renderTo: 'taxa_falha',
							zoomType: 'xy'
						},
						title: {
							text: 'Taxa Falha - OS (%) - $titulo'
						},
						subtitle: {
							text: 'Producao/Mes'
						},
						exporting: {
							width: 1500,
							sourceWidth: 1500
						},
						credits: {
							enabled: false
						},
						xAxis: [{
							categories: $meses
						}],
						yAxis: [{ // Primary yAxis
							min: 0,
							labels: {
								formatter: function() {
									return this.value +'%';
								},
								style: {
									color: '#89A54E'
								}
							},
							title: {
								text: 'Taxa Falha',
								style: {
									color: '#89A54E'
								}
							}
						}, { // Secondary yAxis
							title: {
								text: 'OSs',
								style: {
									color: '#4572A7'
								}
							},
							labels: {
								formatter: function() {
									return this.value;
								},
								style: {
									color: '#4572A7'
								}
							},
							opposite: true
						}],
						tooltip: {
							formatter: function() {
								return ''+
									this.x +': '+ this.y +
									(this.series.name == 'Taxa Falha' ? '%' : '');
							}
						},
						legend: {
							backgroundColor: '#FFFFFF',
							reversed: true
						},
						series: [{
							name: 'OS\'s',
							color: '#4572A7',
							type: 'column',
							yAxis: 1,
							data: [$oss]

						}, {
							name: 'Taxa Falha',
							color: '#C00000',
							type: 'line',
							data: [$taxa_falha]
						}]
					});
				});

			});
		</script>
		";

		return $script;

	}

	private function geraGraficoTaxaFalhaComparativo($meses, $oss_anterior, $oss_atual, $tx_falha_anterior, $tx_falha_atual, $titulo = '', $idx = '0')
	{
        $titulo = preg_replace("/[^a-zA-Z ]/", "", strtr($titulo, "����������������������������", "aaaaeeiooouucnAAAAEEIOOOUUCN"));

		$script = '<div id="tx_falha_comparativo_' . $idx . '" style="min-width: 1500px; height: 400px; margin-top: 30px;; display: none;"></div>';

		$script.= "
		<script>
			$(function () {
				var chart;
				$(document).ready(function() {
					chart = new Highcharts.Chart({
						chart: {
							renderTo: 'tx_falha_comparativo_$idx',
							zoomType: 'xy'
						},
						title: {
							text: 'Taxa Falha - OS (%) - $titulo - Comparativo'
						},
						subtitle: {
							text: 'Producao/Mes'
						},
						exporting: {
							width: 1500,
							sourceWidth: 1500
						},
						credits: {
							enabled: false
						},
						xAxis: [{
							categories: $meses
						}],
						yAxis: [{ // Primary yAxis
							min: 0,
							labels: {
								formatter: function() {
									return this.value +'%';
								},
								style: {
									color: '#89A54E'
								}
							},
							title: {
								text: 'Taxa Falha',
								style: {
									color: '#89A54E'
								}
							}
						}, { // Secondary yAxis
							title: {
								text: 'OSs',
								style: {
									color: '#4572A7'
								}
							},
							labels: {
								formatter: function() {
									return this.value;
								},
								style: {
									color: '#4572A7'
								}
							},
							opposite: true
						}],
						tooltip: {
							formatter: function() {
								var unit = {
			                        'OS\'s - Anterior': '',
			                        'OS\'s - Atual': '',
			                        'Taxa Falha - Anterior': '%',
			                        'Taxa Falha - Atual' : '%'
			                    }[this.series.name];

			                    return ''+
			                        this.x +': '+ this.y +' '+ unit;
							}
						},
						legend: {
							backgroundColor: '#FFFFFF',
							reversed: true
						},
						series: [{
							name: 'OS\'s - Anterior',
							color: '#808080',
							type: 'column',
							yAxis: 1,
							data: [$oss_anterior]

						}, {
							name: 'OS\'s - Atual',
							color: '#4572A7',
							type: 'column',
							yAxis: 1,
							data: [$oss_atual]
						}, {
							name: 'Taxa Falha - Anterior',
							color: '#000000',
							type: 'line',
							data: [$tx_falha_anterior]
						}, {
							name: 'Taxa Falha - Atual',
							color: '#C00000',
							type: 'line',
							data: [$tx_falha_atual]
						}]
					});
				});

			});
		</script>
		";

		return $script;

	}

	private function geraGraficoIRC($meses, $tx_falha, $irc_1, $irc_4, $irc_6, $irc_15, $titulo = '', $idx = '0')
	{
        $titulo = preg_replace("/[^a-zA-Z ]/", "", strtr($titulo, "����������������������������", "aaaaeeiooouucnAAAAEEIOOOUUCN"));

		$script = '<div id="irc_' . $idx . '" style="min-width: 1500px; height: 400px; margin-top: 30px;; display: none;"></div>';

		$script.= "
		<script>
			$(function () {
				var chart;
				$(document).ready(function() {
					chart = new Highcharts.Chart({
						chart: {
							renderTo: 'irc_$idx',
							zoomType: 'xy'
						},
						title: {
							text: 'IRC (%) - $titulo'
						},
						subtitle: {
							text: 'Producao/Mes'
						},
						exporting: {
							width: 1500,
							sourceWidth: 1500
						},
						credits: {
							enabled: false
						},
						xAxis: [{
							categories: $meses
						}],
						yAxis: [{ // Primary yAxis
							min: 0,
							labels: {
								formatter: function() {
									return this.value +'%';
								},
								style: {
									color: '#89A54E'
								}
							},
							title: {
								text: 'IRC',
								style: {
									color: '#89A54E'
								}
							}
						}, { // Secondary yAxis
							title: {
								text: 'Taxa Falha',
								style: {
									color: '#4572A7'
								}
							},
							labels: {
								formatter: function() {
									return this.value +'%';
								},
								style: {
									color: '#4572A7'
								}
							},
							opposite: true
						}],
						tooltip: {
							formatter: function() {
                                return ''+
                                    this.x +': '+ this.y + '%';
                            }
						},
						legend: {
							backgroundColor: '#FFFFFF',
							reversed: true
						},
						series: [{
							name: 'Taxa Falha - OS\'s (%)',
							color: '#4572A7',
							type: 'column',
							yAxis: 1,
							data: [$tx_falha]

						}, {
							name: 'IRC 1M',
							color: '#808000',
							type: 'line',
							data: [$irc_1]
						}, {
							name: 'IRC 4M',
							color: '#000000',
							type: 'line',
							data: [$irc_4]
						}, {
							name: 'IRC 6M',
							color: '#604A7B',
							type: 'line',
							data: [$irc_6]
						}, {
							name: 'IRC 15M',
							color: '#C00000',
							type: 'line',
							data: [$irc_15]
						}]
					});
				});

			});
		</script>
		";

		return $script;

	}

	private function geraGraficoIRC15Mes($meses, $arr_oss, $irc_1, $irc_4, $irc_6, $irc_15, $titulo = '')
	{
        $titulo = preg_replace("/[^a-zA-Z ]/", "", strtr($titulo, "����������������������������", "aaaaeeiooouucnAAAAEEIOOOUUCN"));

		$script = '<div id="irc_15_mes" style="min-width: 1500px; height: 400px; margin-top: 30px;; display: none;"></div>';

		$data = '';

		$arr_mes_os = array();
		foreach ($arr_oss as $k => $v) {
			$work = $v['os'];
			foreach ($work as $j => $x) {
				$arr_mes_os[$j][] = $x;
			}
		}

		$i = 15;
		$arr_mes_os = array_reverse($arr_mes_os, true);
		foreach ($arr_mes_os as $k => $v) {
			$data.= '{ name: \'' . $i . 'M\', data: [' . implode(", ", $v) . '] }, ';
			$i--;
		}

		$script.= "
		<script>
			$(function () {
			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'irc_15_mes',
						type: 'column'
					},
					title: {
						text: 'IRC(%) - 15 meses - $titulo'
					},
					exporting: {
						width: 1500,
						sourceWidth: 1500
					},
					credits: {
						enabled: false
					},
					colors: [
						'#ADC683',
						'#FFC000',
						'#808080',
						'#95B3D7',
						'#B3A2C7',
						'#C3D69B',
						'#D99694',
						'#948A54',
						'#A6A6A6',
						'#C27637',
						'#3B879C',
						'#654E7F',
						'#7A9346',
						'#973F3C',
						'#3E6595'
					],
					xAxis: {
						categories: $meses
					},
					yAxis: [{
						min: 0,
						title: {
							text: 'OS\'s'
						}
					}, {
						title: {
							text: 'IRC',
							style: {
								color: '#4572A7'
							}
						},
						opposite: true
					}],
					legend: {
						backgroundColor: '#FFFFFF',
						reversed: true
					},
					tooltip: {
						formatter: function() {
							return ''+
								this.series.name +': '+ this.y +'';
						}
					},
					plotOptions: {
						column: {
							stacking: 'normal'
						}
					},
					series: [
						$data
						{
							name: 'IRC 1M',
							color: '#808000',
							type: 'line',
							yAxis: 1,
							data: [$irc_1]
						}, {
							name: 'IRC 4M',
							color: '#000000',
							type: 'line',
							yAxis: 1,
							data: [$irc_4]
						}, {
							name: 'IRC 6M',
							color: '#604A7B',
							type: 'line',
							yAxis: 1,
							data: [$irc_6]
						}, {
							name: 'IRC 15M',
							color: '#C00000',
							type: 'line',
							yAxis: 1,
							data: [$irc_15]
						}
					]
				});
			});

		});
		</script>
		";

		return $script;

	}

	private function geraGraficoCFEParqueInstalado($meses, $cfe, $cfe_per_unit, $titulo, $idx = '0')
	{
        $titulo = preg_replace("/[^a-zA-Z ]/", "", strtr($titulo, "����������������������������", "aaaaeeiooouucnAAAAEEIOOOUUCN"));

		$script = '<div id="gr_cfe_' . $idx . '" style="min-width: 1500px; height: 400px; margin-top: 30px; display: none;"></div>';

		$script.= "
		<script>
			$(function () {
				var chart;
				$(document).ready(function() {
					chart = new Highcharts.Chart({
						chart: {
							renderTo: 'gr_cfe_$idx',
							zoomType: 'xy'
						},
						title: {
							text: 'CFE $titulo'
						},
						subtitle: {
							text: 'Producao/Mes'
						},
						exporting: {
							width: 1500,
							sourceWidth: 1500
						},
						credits: {
							enabled: false
						},
						xAxis: [{
							categories: $meses
						}],
						yAxis: [{ // Primary yAxis
							min: 0,
							labels: {
								formatter: function() {
									return 'R$' + this.value;
								},
								style: {
									color: '#89A54E'
								}
							},
							title: {
								text: 'CFE Per Unit',
								style: {
									color: '#89A54E'
								}
							}
						}, { // Secondary yAxis
							title: {
								text: 'CFE',
								style: {
									color: '#4572A7'
								}
							},
							labels: {
								formatter: function() {
									return 'R$ ' + this.value;
								},
								style: {
									color: '#4572A7'
								}
							},
							opposite: true
						}],
						tooltip: {
							formatter: function() {
								return '' + this.series.name +': R$ '+ Highcharts.numberFormat(this.y, 2, ',', '.') +'';
							}
						},
						legend: {
							backgroundColor: '#FFFFFF',
							reversed: true
						},
						series: [{
							name: 'CFE',
							color: '#4572A7',
							type: 'column',
							yAxis: 1,
							data: [$cfe]

						}, {
							name: 'CFE Per Unit',
							color: '#C00000',
							type: 'line',
							data: [$cfe_per_unit]
						}]
					});
				});

			});
		</script>
		";

		return $script;

	}

	/**
	 * Monta a tabela que exibe o resultado do relat�rio
	 */
	private function montaResultado()
	{

		$arr_single = array();

		foreach ($this->arr_os as $key => $value) {
			$tot = $this->arr_total[$key];
			$sum = array_sum($value['os']);
			$count = count($value['os']);
			$producao[$value['mes']] = $tot;
			$oss[$value['mes']] = $sum;
			$oss_count[$value['mes']] = $count;
			$this->arr_falha[$value['mes']] =  bcmul($sum / $tot, 100, 2);
		}

		foreach ($this->arr_os_anterior as $key => $value) {
			$tot = $this->arr_total_anterior[$key];
			$sum = array_sum($value['os']);
			$oss_anterior[$value['mes']] = $sum;
			$this->arr_falha_anterior[$value['mes']] =  bcmul($sum / $tot, 100, 2);
		}

		$this->arr_os_anterior = $oss_anterior;

		$count = count($this->arr_os[0]['os']);

		for ($i = 0; $i < $count; $i++) {
			foreach ($this->arr_os as $key => $value) {
				$arr_tmp[$value['mes']] = $value['os'][$i];
			}
			$arr_single[] = $arr_tmp;
		}

		$meses = range(0, $this->meses - 1);

		$arr_meses = array_keys($producao);

		unset($arr_tmp);

		$this->result_view = '<table class="tabela" cellspacing="1" align="center">
								<tr class="titulo_coluna">
									<th>M�s</th>';

		foreach ($producao as $key => $value) {
			$this->result_view.= '<th>' . $key . '</th>';
		}

		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">Produ��o</td>';
			foreach ($producao as $key => $value) {
				$this->result_view.= '<td>' . $value . '</td>';
			}
		$this->result_view.= '</tr>';

		$adicional_pareto_params = array();

		if (!empty($this->pecas) and false === $this->flag_peca) {
            $adicional_pareto_params = $this->pecas;
        } else {
            $adicional_pareto_params = array('0', '0');
        }

        if (!empty($this->fornecedores)) {
            $adicional_pareto_params[] = '\'' . implode('|', $this->fornecedores) . '\'';
        } else {
            $adicional_pareto_params[] = "''";
        }

        $datas = '';

        if (!empty($this->datas)) {

            $datas.= "'";

            foreach ($this->datas as $key => $value) {
                $datas.= $key . '*';

                foreach ($value as $idx => $val) {
                    $datas.= $idx . ':' . $val . ';';
                }

                $datas.= '|';
            }

            $datas.= "'";

            $adicional_pareto_params[] = $datas;
        } else {
            $adicional_pareto_params[] = "''";
        }

        if (!empty($this->produtos)) {
            $adicional_pareto_params[] = '\'' . implode('|', $this->produtos) . '\'';
        } else {
            $adicional_pareto_params[] = "''";
        }

        if (!empty($this->pecas) and true === $this->flag_peca) {
            $adicional_pareto_params[] = '\'' . implode('|', $this->pecas) . '\'';
        } else {
            $adicional_pareto_params[] = "''";
        }

        if (!empty($this->posto)) {
            $adicional_pareto_params[] = '\'' . implode('|', $this->posto) . '\'';
        }

		if (empty($adicional_pareto_params)) {
			$adicional_pareto_params[] = "''";
		}

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">OS\'s</td>';
			foreach ($oss as $key => $value) {
				$param_data_year  = substr($key, 3, 4);
				$param_data_month = substr($key, 0 , 2);

				$next_month = $param_data_month + 1;

				if ($next_month == 13) {
					$next_month = '01';
					$next_year  = $param_data_year + 1;
				} else {
					$next_month = sprintf('%02d', $next_month);
					$next_year  = $param_data_year;
				}

				$param_data_fb = $param_data_year . '-' . $param_data_month;
				$param_data_ab = $next_year . '-' . $next_month;
				$meses_pareto = (int) $oss_count[$key];

				$this->result_view.= '<td style="cursor: pointer" onClick="pareto(\'' . $param_data_fb . '\', \'' . $param_data_ab . '\', \'' . $meses_pareto . '\', \'' . $this->familia . '\', ' . implode(', ', $adicional_pareto_params) . ')">' . $value . '</td>';
			}
		$this->result_view.= '</tr>';

		$seq_meses = array(1 => '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

		foreach ($meses as $key => $value) {
			$curr = $value + 1;
			$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">';
			$this->result_view.= $curr;
			$this->result_view.= 'M</td>';

			foreach ($arr_single[$key] as $x => $y) {
				$arr_abertura = explode('/', $x);
				$mes_fabricacao = (int) $arr_abertura[0] + $curr;
				$ano_fabricacao = $arr_abertura[1];
				if ($mes_fabricacao > 12) {
					$div = (int) ($mes_fabricacao / 12);
					$res = $mes_fabricacao % 12;
					if ($res == 0) {
						$res = 12;
						$div = $div - 1;
					}
					$mes_fabricacao = $seq_meses[$res];
					$ano_fabricacao = $ano_fabricacao + $div;
				}
				$abertura = $arr_abertura[1] . '-' . $arr_abertura[0];
				$fabricacao = $ano_fabricacao . '-' . sprintf('%02d', $mes_fabricacao);

				/**
				 * A l�gica que faz a atribui��o de valores de $abertura e $fabricacao est� invertida.
				 * Foi mais f�cil alterar a ordem em que estas vari�veis s�o passadas para o JS do que reescrever a l�gica. :)
				 */
				$abrePareto = '';
				if (!empty($y)) {
					$abrePareto = ' style="cursor: pointer" onClick="pareto(\'' . $abertura . '\', \'' . $fabricacao . '\', \'1\', \'' . $this->familia . '\', ' . implode(', ', $adicional_pareto_params) . ')"';
				}

				$this->result_view.= '<td' . $abrePareto . '>';
				$this->result_view.=  $y;
				$this->result_view.= '</td>';
			}
			$this->result_view.= '</tr>';
		}

		$this->result_view.= '<tr><td colspan="26">&nbsp;</td></tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">Taxa Falha - OS\'s (%)</td>';
			foreach ($this->arr_falha as $falha) {
				$this->result_view.= '<td>' . str_replace('.', ',', $falha) . '%</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr><td colspan="26">&nbsp;</td></tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna" nowrap>Popula��o - 1M</td>';
			foreach ($this->populacao_1M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna" nowrap>Popula��o - 4M</td>';
			foreach ($this->populacao_4M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna" nowrap>Popula��o - 6M</td>';
			foreach ($this->populacao_6M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna" nowrap>Popula��o - 15M</td>';
			foreach ($this->populacao_15M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr><td colspan="26">&nbsp;</td></tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">OS\'s - 1M</td>';
			foreach ($this->os_1M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">OS\'s - 4M</td>';
			foreach ($this->os_4M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">OS\'s - 6M</td>';
			foreach ($this->os_6M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna" nowrap>OS\'s - 15M</td>';
			foreach ($this->os_15M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr><td colspan="26">&nbsp;</td></tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">' . number_format($this->index_irc, 2, ',', '') . '</td>';
			$this->result_view.= '<td colspan="25">&nbsp;</td>';
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna" nowrap>Popula��o - 1M</td>';
			foreach ($this->populacao_1M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';
		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">OS\'s - 1M</td>';
			foreach ($this->os_1M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';
		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">IRC - 1M</td>';
			foreach ($this->irc_1M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna" nowrap>Popula��o - 4M</td>';
			foreach ($this->populacao_4M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';
		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">OS\'s - 4M</td>';
			foreach ($this->os_4M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';
		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">IRC - 4M</td>';
			foreach ($this->irc_4M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna" nowrap>Popula��o - 6M</td>';
			foreach ($this->populacao_6M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';
		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">OS\'s - 6M</td>';
			foreach ($this->os_6M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';
		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">IRC - 6M</td>';
			foreach ($this->irc_6M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna" nowrap>Popula��o - 15M</td>';
			foreach ($this->populacao_15M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';
		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">OS\'s - 15M</td>';
			foreach ($this->os_15M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';
		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">IRC - 15M</td>';
			foreach ($this->irc_15M as $populacao) {
				$this->result_view.= '<td>' . $populacao . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr><td colspan="26">&nbsp;</td></tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">CFE</td>';
			foreach ($this->cfe as $cfe) {
				$this->result_view.= '<td nowrap>R$ ' . number_format($cfe, 2, ',', '.') . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">CFE per Unit (Produ��o)</td>';
			foreach ($this->cfe_per_unit_prod as $cfe) {
				$this->result_view.= '<td nowrap>R$ ' . number_format($cfe, 2, ',', '.') . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">Produtos Faturados</td>';
			foreach ($this->faturados as $faturados) {
				$this->result_view.= '<td nowrap>' . $faturados . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">CFE per Unit (Faturamento)</td>';
			foreach ($this->cfe_per_unit_fat as $cfe) {
				$this->result_view.= '<td nowrap>R$ ' . number_format($cfe, 2, ',', '.') . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->setCFEPerUnit15();
		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">CFE per Unit (Parque Instalado - ' . $this->meses . 'M)</td>';
			foreach ($this->cfe_per_unit_15 as $cfe) {
				$this->result_view.= '<td nowrap>R$ ';
				if ($cfe == '-') {
					$this->result_view.= $cfe;
				} else {
					$this->result_view.= number_format($cfe, 2, ',', '.');
				}
				$this->result_view.= '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr><td colspan="26">&nbsp;</td></tr>';

		$this->result_view.= '<tr class="titulo_coluna">
									<th>M�s</th>';

		foreach ($producao as $key => $value) {
			$this->result_view.= '<th>' . $key . '</th>';
		}

		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">Produtos Faturados</td>';
			foreach ($this->faturados as $faturados) {
				$this->result_view.= '<td nowrap>' . $faturados . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr><td colspan="26">&nbsp;</td></tr>';

		array_shift($this->arr_os_anterior);
		array_push($this->arr_os_anterior, 0);
		array_shift($this->arr_falha_anterior);
		array_push($this->arr_falha_anterior, 0);
		$arr_diferenca_os = array();

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">Taxa Falha - OS\'s (%) - Atual</td>';
			foreach ($this->arr_falha as $falha) {
				$this->result_view.= '<td>' . str_replace('.', ',', $falha) . '%</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">OS\'s - Atual</td>';
			foreach ($oss as $key => $value) {
				$this->result_view.= '<td>' . $value . '</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">Taxa Falha - OS\'s (%) - Anterior</td>';
			foreach ($this->arr_falha_anterior as $falha) {
				$this->result_view.= '<td>' . str_replace('.', ',', $falha) . '%</td>';
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">OS\'s - Anterior</td>';
			foreach ($this->arr_os_anterior as $key => $value) {
				$this->result_view.= '<td>' . $value . '</td>';
				$arr_diferenca_os[] = $oss[$key] - $value;
			}
		$this->result_view.= '</tr>';

		$this->result_view.= '<tr>';
			$this->result_view.= '<td class="titulo_coluna">Diferen�a OS\'s</td>';
			foreach ($arr_diferenca_os as $value) {
				$this->result_view.= '<td>' . $value . '</td>';
			}
		$this->result_view.= '</tr>';


		$this->result_view.= '</table><br/>';

		$this->geraExcel();
		$this->pegaProdutos($this->produtos);

		$query_desc_familia = pg_query("SELECT descricao from tbl_familia where familia = $this->familia");
		$familia_descricao = pg_fetch_result($query_desc_familia, 0, 'descricao');

		$this->result_view.= $this->geraGraficoTaxaFalha(json_encode($arr_meses), implode(", ", $this->arr_falha), implode(", ", $oss), $familia_descricao);

		$this->result_view.= $this->geraGraficoTaxaFalhaComparativo(
																	json_encode($arr_meses),
																	implode(", ", $this->arr_os_anterior),
																	implode(", ", $oss),
																	implode(", ", $this->arr_falha_anterior),
																	implode(", ", $this->arr_falha),
																	$familia_descricao
																);

		$n_meses = (int) $this->meses;
		$arr_meses_15 = $this->arrayReduce($arr_meses, $n_meses, 0);
		$arr_os_anterior_comp15 = $this->arrayReduce($this->arr_os_anterior, $n_meses, 0);
		$arr_os_comp15 = $this->arrayReduce($oss, $n_meses, 0);
		$arr_falha_anterior_comp15 = $this->arrayReduce($this->arr_falha_anterior, $n_meses, 0);
		$arr_falha_comp15 = $this->arrayReduce($this->arr_falha, $n_meses, 0);

		$this->result_view.= $this->geraGraficoTaxaFalhaComparativo(
																	json_encode($arr_meses_15),
																	implode(", ", $arr_os_anterior_comp15),
																	implode(", ", $arr_os_comp15),
																	implode(", ", $arr_falha_anterior_comp15),
																	implode(", ", $arr_falha_comp15),
																	"$this->meses Meses - $familia_descricao",
																	1
																);

		$this->result_view.= $this->geraGraficoIRC(
													json_encode($arr_meses),
													implode(", ", $this->arr_falha),
													implode(", ", $this->irc_1M),
													implode(", ", $this->irc_4M),
													implode(", ", $this->irc_6M),
													implode(", ", $this->irc_15M),
													$familia_descricao
												);

		$arr_falha_reduced = $this->arrayReduce($this->arr_falha, $n_meses, 0);
		$arr_irc_1M15 = $this->arrayReduce($this->irc_1M, $n_meses, 0);
		$arr_irc_4M15 = $this->arrayReduce($this->irc_4M, $n_meses, 0);
		$arr_irc_6M15 = $this->arrayReduce($this->irc_6M, $n_meses, 0);
		$arr_irc_15M15 = $this->arrayReduce($this->irc_15M, $n_meses, 0);

		$this->result_view.= $this->geraGraficoIRC(
													json_encode($arr_meses_15),
													implode(", ", $arr_falha_reduced),
													implode(", ", $arr_irc_1M15),
													implode(", ", $arr_irc_4M15),
													implode(", ", $arr_irc_6M15),
													implode(", ", $arr_irc_15M15),
													"$this->meses Meses - $familia_descricao",
													1
												);

		$arr_os_15M = $this->arrayReduce($this->arr_os, $n_meses, 0);
		$this->result_view.= $this->geraGraficoIRC15Mes(
														json_encode($arr_meses_15),
														$arr_os_15M,
														implode(", ", $arr_irc_1M15),
														implode(", ", $arr_irc_4M15),
														implode(", ", $arr_irc_6M15),
														implode(", ", $arr_irc_15M15),
														$familia_descricao
													);

		$this->cfe_per_unit_15[0] = "0";
		$this->result_view.= $this->geraGraficoCFEParqueInstalado(
																json_encode($arr_meses),
																implode(", ", $this->cfe),
																implode(", ", $this->cfe_per_unit_15),
																"(Parque Instalado - $this->meses M) - $familia_descricao"
															);

		$this->result_view.= $this->geraGraficoCFEParqueInstalado(
																json_encode($arr_meses),
																implode(", ", $this->cfe),
																implode(", ", $this->cfe_per_unit_prod),
																"(Produ��o) - $familia_descricao",
																1
															);

		$this->result_view.= $this->geraGraficoCFEParqueInstalado(
																json_encode($arr_meses),
																implode(", ", $this->cfe),
																implode(", ", $this->cfe_per_unit_fat),
																"(Faturamento) - $familia_descricao",
																2
															);

	}

	private function geraExcel()
	{
		if (!empty($this->result_view)) {
			$destino = dirname(__FILE__) . '/xls';
			date_default_timezone_set('America/Sao_Paulo');
			$data = date('YmdGis');
			$arq_nome = 'relatorio_extratificacao-' . $this->login_fabrica . $data . '.xls';
			$file = $destino . '/' . $arq_nome ;
			$f = fopen($file, 'w');
			fwrite($f, $this->result_view);
			fclose($f);

			$this->result_view.= '<div align="center">';
			$this->result_view.= '<input type="button" value="Download do arquivo Excel" onClick="download(\'xls/' . $arq_nome . '\')" />';
			$this->result_view.= '<input type="button" value="Gr�fico Taxa Falha - OS" onClick="mostraRelatorio(\'tx_os\')" />';
			$this->result_view.= '<input type="button" value="Gr�fico Taxa Falha - OS - Comparativo" onClick="mostraRelatorio(\'tx_os_comp\')" />';
			$this->result_view.= '<input type="button" value="Gr�fico Taxa Falha - OS - ' . $this->meses . ' Meses - Comparativo" onClick="mostraRelatorio(\'tx_os_comp_15\')" />';
			$this->result_view.= '<input type="button" value="Gr�fico IRC" onClick="mostraRelatorio(\'irc\')" />';
			$this->result_view.= '<input type="button" value="Gr�fico IRC ' . $this->meses . '" onClick="mostraRelatorio(\'irc_15\')" />';
			$this->result_view.= '<input type="button" value="Gr�fico IRC ' . $this->meses . ' M�s" onClick="mostraRelatorio(\'irc_15_mes\')" />';
			$this->result_view.= '<input type="button" value="CFE - Parque Instalado" onClick="mostraRelatorio(\'cfe_parq\')" />';
			$this->result_view.= '<input type="button" value="CFE - Produ��o" onClick="mostraRelatorio(\'cfe_prod\')" />';
			$this->result_view.= '<input type="button" value="CFE - Faturamento" onClick="mostraRelatorio(\'cfe_fat\')" />';
			$this->result_view.= '</div>';

		}

	}

	private function geraTabelaProdutos($produtos){
		if(count($produtos) > 0){

			$this->result_view.= "<table align='center' class='tabela'>
						<tr class='titulo_coluna'><th>Refer�ncia</th><th>Descri��o</th></tr>";
			foreach ($produtos as $produto) {
				$this->result_view.= "<tr>
										<td>{$produto['referencia']}</td>
										<td align='left'>{$produto['descricao']}</td>
									  </tr>";
			}
			$this->result_view.= "</table>";
		}
	}

	/**
	* Pega a refer�ncia e descri��o dos produtos para montar tabela informando os Produtos selecionados
	*/
	private function pegaProdutos($produtos){
		if(count($produtos) > 0){
			$produtos = implode(',', $produtos);

			$sql = "SELECT referencia,descricao FROM tbl_produto WHERE produto IN($produtos)";
			$query_produto = pg_query($sql);

			if (pg_num_rows($query_produto) > 0) {
				$produtos = pg_fetch_all($query_produto);
				$this->geraTabelaProdutos($produtos);
			} else {
				return '0';
			}
		}
	}

	/**
	 * run - executa o programa - �nico m�todo acess�vel via interface
	 */
	public function run()
	{
		$result = '0';

		if ($this->isRequest()) {
			$result = $this->producao();
		}

		if ($result <> '0') {
			$this->extratifica($result);

			if (!empty($this->arr_os)) {
				$tmp_data_inicial = $this->data_inicial;
				$tmp_data_final = $this->data_final;
				$tmp_arr_os = $this->arr_os;

				$date_i = new DateTime($this->data_inicial);
				$date_f = new DateTime($this->data_final);
				$date_i->sub(new DateInterval('P1M'));
				$date_f->sub(new DateInterval('P1M'));
				$this->data_inicial = $date_i->format('Y-m-d');
				$this->data_final = $date_f->format('Y-m-d');
				$result_anterior = $this->producao();

				if ($result_anterior <> '0') {
					$this->arr_os = array();
					foreach ($result_anterior as $k => $fetch) {
						$mes = $fetch["mes"];
						$ano = $fetch["ano"];
						$total = $fetch["total"];
						$cfe = $fetch["cfe"];

						$this->arr_total_anterior[$k] = $total;
						$this->arr_os[$k] = array("mes" => sprintf("%02d", $mes) . '/' . $ano, "os" => array());
						$this->popOS($ano, $mes, $k);
					}
				}

				$this->data_inicial = $tmp_data_inicial;
				$this->data_final = $tmp_data_final;
				$this->arr_os_anterior = $this->arr_os;
				$this->arr_os = $tmp_arr_os;
				unset($tmp_arr_os);

				$this->montaResultado();

			}
		}
	}

}
