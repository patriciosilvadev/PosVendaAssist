		<?php
		$mes_ext = array ("Janeiro","Favereiro","Mar�o","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro");
		$dia_ext = array ("Domingo","Segunda","Ter�a","Quarta","Quinta","Sexta","S�bado");

		$dia = substr ($data,6);
		$mes = substr ($data,4,2);
		$ano = substr ($data,0,4);
		
		if (checkdate ($mes,$dia,$ano)) {
		
			$dataaux = mktime (0,0,0,$mes,$dia,$ano);
	
			echo $dia_ext[(date ("w",$dataaux))];
			echo ", ";
			echo date ("d",$dataaux);
			echo " de " ;
			echo $mes_ext [(date ("m",$dataaux)-1)];
			echo " de " ;
			echo date ("Y",$dataaux);
			echo ".";
		}
		?>