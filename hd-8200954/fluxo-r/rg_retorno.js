function createRequestObject(){	var request_;	var browser = navigator.appName;	if(browser == "Microsoft Internet Explorer"){		 request_ = new ActiveXObject("Microsoft.XMLHTTP");	}else{		 request_ = new XMLHttpRequest();	}	return request_;}var http_forn = new Array();function retorno() {	/*Verificacao para existencia de componente - HD 22891 */	rg = document.getElementById('rg').value;	url = "rg_retorno_ajax.php?ajax=sim&acao=retorno&rg="+rg;	document.getElementById('erro').style.visibility = "hidden";	document.getElementById('erro').style.position   = "absolute";	var curDateTime = new Date();	http_forn[curDateTime] = createRequestObject();	http_forn[curDateTime].open('GET',url,true);	http_forn[curDateTime].onreadystatechange = function(){		if (http_forn[curDateTime].readyState == 4){			if (http_forn[curDateTime].status == 200 || http_forn[curDateTime].status == 304){				var response = http_forn[curDateTime].responseText.split("|");				if (response[0]=="ok"){					document.getElementById('saida').style.visibility = "visible";					document.getElementById('saida').style.position   = "static";					document.getElementById('saida').innerHTML        = response[1];					MostraInfo();				}else{					document.getElementById('erro').style.visibility = "visible";					document.getElementById('erro').style.position   = "static";					document.getElementById('erro').innerHTML        = response[1];				}				document.getElementById('rg').value = "";			}		}	}	http_forn[curDateTime].send(null);}function MostraInfo(x) {	var saida  = document.getElementById('saida');	url = "rg_retorno_ajax.php?ajax=sim&acao=mostrar&x="+x;	var curDateTime = new Date();	http_forn[curDateTime] = createRequestObject();	http_forn[curDateTime].open('GET',url,true);	http_forn[curDateTime].onreadystatechange = function(){		if (http_forn[curDateTime].readyState == 4){			if (http_forn[curDateTime].status == 200 || http_forn[curDateTime].status == 304){				var response = http_forn[curDateTime].responseText.split("|");				if (response[0]=="ok"){					saida.innerHTML       = response[1];					saida.style.visibility = "visible";					saida.style.position   = "static";				}			}		}	}	http_forn[curDateTime].send(null);}