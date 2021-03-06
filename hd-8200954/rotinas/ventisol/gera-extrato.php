<?php

try {

	include dirname(__FILE__) . '/../../dbconfig.php';
	include dirname(__FILE__) . '/../../includes/dbconnect-inc.php';
	require dirname(__FILE__) . '/../funcoes.php';

	$bug         = '';
	$fabrica     = 139;
	$dia_mes     = date('d');
	#$dia_mes     = "28";
	$dia_extrato = date('Y-m-d H:i:s');
	#$dia_extrato = "2012-07-28 02:00:00";
	#$data_15 = "2014-11-05";

	$phpCron = new PHPCron($fabrica, __FILE__);
	$phpCron->inicio();

	$vet['fabrica'] = 'ventisol';
	$vet['tipo']    = 'extrato';
	$vet['dest']    = 'ronald.santos@telecontrol.com.br';
	$vet['log']     = 2;



	$sql9 = "SELECT ('$dia_extrato'::date - INTERVAL '1 month' + INTERVAL '14 days')::date";
	$res9 = pg_query($con,$sql9);
	$data_15 = pg_fetch_result($res9, 0, 0);

	$sql = "   SELECT DISTINCT tbl_os.os
			   INTO TEMP tmp_auditorias
			   FROM tbl_auditoria_os
			   JOIN tbl_os ON tbl_os.os = tbl_auditoria_os.os AND tbl_os.fabrica = $fabrica
			   WHERE tbl_auditoria_os.liberada IS NULL
			   AND (tbl_auditoria_os.cancelada IS NOT NULL OR tbl_auditoria_os.reprovada IS NOT NULL)
			   AND tbl_os.excluida IS NOT TRUE";
	$res = pg_query($sql);

	$sql = "SELECT  posto, COUNT(*) AS qtde
			FROM tbl_os
			JOIN tbl_os_extra USING (os)
			WHERE tbl_os.fabrica = $fabrica
			AND   tbl_os_extra.extrato IS NULL
			AND   tbl_os.excluida      IS NOT TRUE
			AND   tbl_os.posto <> 6359
			AND   tbl_os.finalizada    <= '$dia_extrato'
			AND   tbl_os.finalizada::date <= current_date
			AND   tbl_os.os NOT IN ( SELECT os FROM tmp_auditorias  )
            GROUP BY posto
			ORDER BY posto ";
	$res      = pg_query($con, $sql);
	$msg_erro = pg_last_error($con);

	if (pg_num_rows($res) > 0 && strlen($msg_erro) == 0) {

		for ($i = 0; $i < pg_num_rows($res); $i++) {

			$msg_erro = "";
			$posto = pg_result($res, $i, 'posto');
			$qtde  = pg_result($res, $i, 'qtde');

			$resP = pg_query($con,"BEGIN TRANSACTION");

			$sql2 = "INSERT INTO tbl_extrato (fabrica, posto, data_geracao,mao_de_obra, pecas, total,avulso) VALUES ($fabrica, $posto,'$dia_extrato', 0, 0, 0, 0);";
			$res2 = pg_query($con, $sql2);

			$msg_erro .= pg_last_error($con);

			$sql3      = "SELECT CURRVAL ('seq_extrato');";
			$res3      = pg_query($con, $sql3);
			$extrato   = pg_result($res3, 0, 0);

			$msg_erro .= pg_last_error($con);

			$sql4 = "UPDATE tbl_extrato_lancamento SET extrato = $extrato
				WHERE tbl_extrato_lancamento.fabrica = $fabrica
				AND   tbl_extrato_lancamento.extrato IS NULL
				AND   tbl_extrato_lancamento.posto = $posto; ";
			$res4 = pg_query($con, $sql4);

			$msg_erro .= pg_last_error($con);

			$sql4 = "UPDATE tbl_os_extra SET extrato = $extrato
						FROM  tbl_os
						WHERE tbl_os.posto   = $posto
						AND   tbl_os.fabrica = $fabrica
						AND   tbl_os.os      = tbl_os_extra.os
						AND   tbl_os_extra.extrato IS NULL
						AND   tbl_os.excluida      IS NOT TRUE
						AND   tbl_os.finalizada    <= '$dia_extrato'
						AND   tbl_os.finalizada::date <= current_date";
			$res4      = pg_query($con, $sql4);
			$msg_erro .= pg_last_error($con);

			$sql6      = "SELECT fn_calcula_extrato ($fabrica, $extrato)";
			$res6      = pg_query($con, $sql6);
			$msg_erro .= pg_last_error($con);


			$sqlLGR = "UPDATE tbl_faturamento_item SET
					extrato_devolucao = $extrato
					FROM tbl_os_item,tbl_faturamento,tbl_extrato, tbl_peca
					WHERE tbl_os_item.os_item = tbl_faturamento_item.os_item
					AND tbl_faturamento.posto = tbl_extrato.posto
					AND tbl_faturamento.fabrica = tbl_extrato.fabrica
					AND tbl_faturamento.faturamento = tbl_faturamento_item.faturamento
					AND tbl_faturamento.fabrica = $fabrica
					AND tbl_faturamento.emissao >='2010-01-01'
					AND tbl_faturamento.emissao <='$data_15'
					AND tbl_faturamento.cancelada IS NULL
					AND tbl_faturamento_item.extrato_devolucao IS NULL
					AND tbl_peca.peca = tbl_os_item.peca
					AND tbl_os_item.peca_obrigatoria
					AND tbl_peca.aguarda_inspecao IS NOT TRUE
					AND (tbl_faturamento.cfop ILIKE '59%' OR tbl_faturamento.cfop ILIKE '69%')
					AND tbl_extrato.extrato = $extrato";
			$resLGR = pg_query($con,$sqlLGR);
			$msg_erro .= pg_last_error($con);

			$sqlLGR = "UPDATE tbl_faturamento SET
					extrato_devolucao = $extrato
					FROM tbl_faturamento_item
					WHERE tbl_faturamento.faturamento = tbl_faturamento_item.faturamento
					AND tbl_faturamento.posto = $posto
					AND tbl_faturamento.fabrica = $fabrica
					AND tbl_faturamento.emissao >='2010-01-01'
					AND tbl_faturamento.emissao <='$data_15'
					AND tbl_faturamento_item.extrato_devolucao = $extrato";
			$resLGR = pg_query($con,$sqlLGR);
			$msg_erro .= pg_last_error($con);


			$sqlLGR2 = "INSERT INTO tbl_extrato_lgr (extrato, posto, peca, qtde)
				SELECT
				tbl_extrato.extrato,
				tbl_extrato.posto,
				tbl_faturamento_item.peca,
				SUM (tbl_faturamento_item.qtde)
				FROM tbl_extrato
				JOIN tbl_faturamento_item ON tbl_extrato.extrato = tbl_faturamento_item.extrato_devolucao
				WHERE tbl_extrato.fabrica = $fabrica
				AND tbl_extrato.extrato = $extrato
				GROUP BY tbl_extrato.extrato,
				tbl_extrato.posto,
				tbl_faturamento_item.peca";
			$resLGR = pg_query($con,$sqlLGR2);
			$msg_erro .= pg_last_error($con);

			if (strlen($msg_erro) > 0) {

				$resP = pg_query('ROLLBACK;');
				$bug .= $msg_erro;

				Log::log2($vet, $msg_erro);

			} else {

				$resP = pg_query('COMMIT;');

			}

		}

	}

	if (strlen($bug) > 0) {

		Log::envia_email($vet, 'Log - Extrato Ventisol', $bug);

	}

	$phpCron->termino();

} catch (Exception $e) {

	Log::envia_email($data,Date('d/m/Y H:i:s')." - Ventisol - Erro na geração de extrato(gera-extrato.php)", $e->getMessage());

}

