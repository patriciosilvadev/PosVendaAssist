<?
include 'dbconfig.php';
include 'includes/dbconnect-inc.php';


$fabrica   = "25" ;
$arquivos  = "/tmp";
$mensagem  = "";


$sql    = "SELECT upper(nome)      ,
					endereco       ,
					numero         ,
					complemento    ,
					cidade         ,
					estado         ,
					SUBSTR (tbl_posto.cep,1,2) || '.' || SUBSTR (tbl_posto.cep,3,3) || '-' || SUBSTR (tbl_posto.cep,6,3) AS cep ,
					SUBSTR (tbl_posto.cnpj,1,2) || '.' || SUBSTR (tbl_posto.cnpj,3,3) || '.' || SUBSTR (tbl_posto.cnpj,6,3) || '/' || SUBSTR (tbl_posto.cnpj,9,4) || '-' || SUBSTR (tbl_posto.cnpj,12,2) AS cnpj     ,
					posto          ,
					to_char(current_date,'DD/MM/YYYY') as data_contrato
				FROM tbl_posto JOIN tbl_posto_fabrica using(posto)
				WHERE fabrica = 25 AND posto = 4311;";
$result = $conn-> exec ($sql);


$posto_nome = pg_result($res,0,nome);
$endereco   = pg_result($res,0,endereco);
$numero   = pg_result($res,0,numero);
$complemento   = pg_result($res,0,complemento);
$cidade   = pg_result($res,0,cidade);
$estado   = pg_result($res,0,estado);
$cep   = pg_result($res,0,cep);
$cnpj   = pg_result($res,0,cnpj);
$posto   = pg_result($res,0,posto);
$data_contrato = pg_result($res,0,data_contrato);


$conteudo = "
	<html xmlns:o='urn:schemas-microsoft-com:office:office'
	xmlns:w='urn:schemas-microsoft-com:office:word'
	xmlns:st1='urn:schemas-microsoft-com:office:smarttags'
	xmlns='http://www.w3.org/TR/REC-html40'>

	<head>
	<meta http-equiv=Content-Type content='text/html; charset=windows-1252'>
	<meta name=ProgId content=Word.Document>
	<meta name=Generator content='Microsoft Word 11'>
	<meta name=Originator content='Microsoft Word 11'>
	<link rel=File-List
	href='Contrato%20Credenciamento%20Postos_arquivos/filelist.xml'>
	<link rel=Preview href='Contrato%20Credenciamento%20Postos_arquivos/preview.wmf'>
	<title>CONTRATO DE CREDENCIAMENTO DE ASSIST�NCIA T�CNICA</title>
	<o:SmartTagType namespaceuri='urn:schemas-microsoft-com:office:smarttags'
	 name='PersonName'/>
	<!--[if gte mso 9]><xml>
	 <o:DocumentProperties>
	  <o:Author>Lu�s Rodolfo Creuz</o:Author>
	  <o:LastAuthor>T�lio Oliveira</o:LastAuthor>
	  <o:Revision>2</o:Revision>
	  <o:TotalTime>2</o:TotalTime>
	  <o:LastPrinted>2113-01-01T03:00:00Z</o:LastPrinted>
	  <o:Created>2007-12-03T19:34:00Z</o:Created>
	  <o:LastSaved>2007-12-03T19:34:00Z</o:LastSaved>
	  <o:Pages>1</o:Pages>
	  <o:Words>3941</o:Words>
	  <o:Characters>21282</o:Characters>
	  <o:Company>Telecontrol</o:Company>
	  <o:Lines>177</o:Lines>
	  <o:Paragraphs>50</o:Paragraphs>
	  <o:CharactersWithSpaces>25173</o:CharactersWithSpaces>
	  <o:Version>11.5606</o:Version>
	 </o:DocumentProperties>
	</xml><![endif]--><!--[if gte mso 9]><xml>
	 <w:WordDocument>
	  <w:PunctuationKerning/>
	  <w:DrawingGridHorizontalSpacing>0 pt</w:DrawingGridHorizontalSpacing>
	  <w:DrawingGridVerticalSpacing>0 pt</w:DrawingGridVerticalSpacing>
	  <w:DisplayHorizontalDrawingGridEvery>0</w:DisplayHorizontalDrawingGridEvery>
	  <w:DisplayVerticalDrawingGridEvery>0</w:DisplayVerticalDrawingGridEvery>
	  <w:UseMarginsForDrawingGridOrigin/>
	  <w:ValidateAgainstSchemas/>
	  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
	  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
	  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
	  <w:DrawingGridHorizontalOrigin>0 pt</w:DrawingGridHorizontalOrigin>
	  <w:DrawingGridVerticalOrigin>0 pt</w:DrawingGridVerticalOrigin>
	  <w:Compatibility>
	   <w:SpaceForUL/>
	   <w:BalanceSingleByteDoubleByteWidth/>
	   <w:DoNotLeaveBackslashAlone/>
	   <w:ULTrailSpace/>
	   <w:DoNotExpandShiftReturn/>
	   <w:AdjustLineHeightInTable/>
	   <w:SelectEntireFieldWithStartOrEnd/>
	   <w:UseWord2002TableStyleRules/>
	  </w:Compatibility>
	  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
	 </w:WordDocument>
	</xml><![endif]--><!--[if gte mso 9]><xml>
	 <w:LatentStyles DefLockedState='false' LatentStyleCount='156'>
	 </w:LatentStyles>
	</xml><![endif]--><!--[if !mso]><object
	 classid='clsid:38481807-CA0E-42D2-BF39-B33AF135CC4D' id=ieooui></object>
	<style>
	st1\:*{behavior:url(\#ieooui) }
	</style>
	<![endif]-->
	<style>
	<!--
	 /* Font Definitions */
	 @font-face
		{font-family:'New York';
		panose-1:2 4 5 3 6 5 6 2 3 4;
		mso-font-charset:0;
		mso-generic-font-family:roman;
		mso-font-format:other;
		mso-font-pitch:variable;
		mso-font-signature:3 0 0 0 1 0;}
	@font-face
		{font-family:Tahoma;
		panose-1:2 11 6 4 3 5 4 4 2 4;
		mso-font-charset:0;
		mso-generic-font-family:swiss;
		mso-font-pitch:variable;
		mso-font-signature:1627421319 -2147483648 8 0 66047 0;}
	@font-face
		{font-family:Verdana;
		panose-1:2 11 6 4 3 5 4 4 2 4;
		mso-font-charset:0;
		mso-generic-font-family:swiss;
		mso-font-pitch:variable;
		mso-font-signature:536871559 0 0 0 415 0;}
	@font-face
		{font-family:'DejaVu Sans';
		mso-font-charset:0;
		mso-generic-font-family:auto;
		mso-font-pitch:variable;
		mso-font-signature:0 0 0 0 0 0;}
	@font-face
		{font-family:'Lucida Sans Unicode';
		panose-1:2 11 6 2 3 5 4 2 2 4;
		mso-font-charset:0;
		mso-generic-font-family:swiss;
		mso-font-pitch:variable;
		mso-font-signature:-2147476737 14699 0 0 63 0;}
	@font-face
		{font-family:StarSymbol;
		mso-font-alt:'Arial Unicode MS';
		mso-font-charset:128;
		mso-generic-font-family:auto;
		mso-font-pitch:auto;
		mso-font-signature:0 0 0 0 0 0;}
	@font-face
		{font-family:'\@StarSymbol';
		mso-font-charset:128;
		mso-generic-font-family:auto;
		mso-font-pitch:auto;
		mso-font-signature:0 0 0 0 0 0;}
	 /* Style Definitions */
	 p.MsoNormal, li.MsoNormal, div.MsoNormal
		{mso-style-parent:;
		margin:0cm;
		margin-bottom:.0001pt;
		mso-pagination:widow-orphan;
		mso-hyphenate:none;
		font-size:12.0pt;
		font-family:'Times New Roman';
		mso-fareast-font-family:'Times New Roman';
		mso-fareast-language:AR-SA;}
	h1
		{mso-style-next:Normal;
		margin:0cm;
		margin-bottom:.0001pt;
		text-indent:0cm;
		mso-pagination:widow-orphan;
		page-break-after:avoid;
		mso-outline-level:1;
		mso-list:l0 level1 lfo1;
		mso-hyphenate:none;
		tab-stops:list 0cm;
		font-size:12.0pt;
		font-family:'Times New Roman';
		mso-font-kerning:0pt;
		mso-fareast-language:AR-SA;
		font-weight:bold;
		mso-bidi-font-weight:normal;
		font-style:italic;
		mso-bidi-font-style:normal;}
	h3
		{mso-style-next:Normal;
		margin-top:12.0pt;
		margin-right:0cm;
		margin-bottom:3.0pt;
		margin-left:0cm;
		text-indent:0cm;
		mso-pagination:widow-orphan;
		page-break-after:avoid;
		mso-outline-level:3;
		mso-list:l0 level3 lfo1;
		mso-hyphenate:none;
		tab-stops:list 0cm;
		font-size:13.0pt;
		font-family:Arial;
		mso-fareast-language:AR-SA;
		font-weight:bold;}
	p.MsoHeader, li.MsoHeader, div.MsoHeader
		{margin:0cm;
		margin-bottom:.0001pt;
		mso-pagination:widow-orphan;
		mso-hyphenate:none;
		tab-stops:center 212.6pt right 425.2pt;
		font-size:12.0pt;
		font-family:'Times New Roman';
		mso-fareast-font-family:'Times New Roman';
		mso-fareast-language:AR-SA;}
	p.MsoFooter, li.MsoFooter, div.MsoFooter
		{margin:0cm;
		margin-bottom:.0001pt;
		mso-pagination:widow-orphan;
		mso-hyphenate:none;
		tab-stops:center 212.6pt right 425.2pt;
		font-size:12.0pt;
		font-family:'Times New Roman';
		mso-fareast-font-family:'Times New Roman';
		mso-fareast-language:AR-SA;}
	p.MsoList, li.MsoList, div.MsoList
		{mso-style-parent:'Corpo de texto';
		margin-top:0cm;
		margin-right:0cm;
		margin-bottom:6.0pt;
		margin-left:0cm;
		mso-pagination:widow-orphan;
		mso-hyphenate:none;
		font-size:12.0pt;
		font-family:'Times New Roman';
		mso-fareast-font-family:'Times New Roman';
		mso-bidi-font-family:Tahoma;
		mso-fareast-language:AR-SA;}
	p.MsoBodyText, li.MsoBodyText, div.MsoBodyText
		{margin-top:0cm;
		margin-right:0cm;
		margin-bottom:6.0pt;
		margin-left:0cm;
		mso-pagination:widow-orphan;
		mso-hyphenate:none;
		font-size:12.0pt;
		font-family:'Times New Roman';
		mso-fareast-font-family:'Times New Roman';
		mso-fareast-language:AR-SA;}
	p.MsoBodyTextIndent, li.MsoBodyTextIndent, div.MsoBodyTextIndent
		{margin-top:0cm;
		margin-right:0cm;
		margin-bottom:0cm;
		margin-left:35.4pt;
		margin-bottom:.0001pt;
		text-align:justify;
		mso-pagination:widow-orphan;
		mso-hyphenate:none;
		font-size:12.0pt;
		font-family:Verdana;
		mso-fareast-font-family:'Times New Roman';
		mso-bidi-font-family:'Times New Roman';
		mso-fareast-language:AR-SA;}
	a:link, span.MsoHyperlink
		{mso-style-parent:;
		color:navy;
		text-decoration:underline;
		text-underline:single;}
	a:visited, span.MsoHyperlinkFollowed
		{color:purple;
		text-decoration:underline;
		text-underline:single;}
	p
		{margin-top:14.0pt;
		margin-right:0cm;
		margin-bottom:14.0pt;
		margin-left:0cm;
		mso-pagination:widow-orphan;
		mso-hyphenate:none;
		font-size:12.0pt;
		font-family:'Times New Roman';
		mso-fareast-font-family:'Times New Roman';
		mso-fareast-language:AR-SA;}
	span.Absatz-Standardschriftart
		{mso-style-name:Absatz-Standardschriftart;
		mso-style-parent:;}
	span.WW-Absatz-Standardschriftart
		{mso-style-name:WW-Absatz-Standardschriftart;
		mso-style-parent:;}
	span.WW-Absatz-Standardschriftart1
		{mso-style-name:WW-Absatz-Standardschriftart1;
		mso-style-parent:;}
	span.Fontepargpadro1
		{mso-style-name:'Fonte par�g\. padr�o1';
		mso-style-parent:;}
	span.NumberingSymbols
		{mso-style-name:'Numbering Symbols';
		mso-style-parent:;}
	span.Bullets
		{mso-style-name:Bullets;
		mso-style-parent:;
		mso-ansi-font-size:9.0pt;
		mso-bidi-font-size:9.0pt;
		font-family:StarSymbol;
		mso-ascii-font-family:StarSymbol;
		mso-fareast-font-family:StarSymbol;
		mso-hansi-font-family:StarSymbol;
		mso-bidi-font-family:StarSymbol;}
	p.Heading, li.Heading, div.Heading
		{mso-style-name:Heading;
		mso-style-next:'Corpo de texto';
		margin-top:12.0pt;
		margin-right:0cm;
		margin-bottom:6.0pt;
		margin-left:0cm;
		mso-pagination:widow-orphan;
		page-break-after:avoid;
		mso-hyphenate:none;
		font-size:14.0pt;
		font-family:Arial;
		mso-fareast-font-family:'Lucida Sans Unicode';
		mso-bidi-font-family:Tahoma;
		mso-fareast-language:AR-SA;}
	p.Caption, li.Caption, div.Caption
		{mso-style-name:Caption;
		margin-top:6.0pt;
		margin-right:0cm;
		margin-bottom:6.0pt;
		margin-left:0cm;
		mso-pagination:widow-orphan no-line-numbers;
		mso-hyphenate:none;
		font-size:12.0pt;
		font-family:'Times New Roman';
		mso-fareast-font-family:'Times New Roman';
		mso-bidi-font-family:Tahoma;
		mso-fareast-language:AR-SA;
		font-style:italic;}
	p.Index, li.Index, div.Index
		{mso-style-name:Index;
		margin:0cm;
		margin-bottom:.0001pt;
		mso-pagination:widow-orphan no-line-numbers;
		mso-hyphenate:none;
		font-size:12.0pt;
		font-family:'Times New Roman';
		mso-fareast-font-family:'Times New Roman';
		mso-bidi-font-family:Tahoma;
		mso-fareast-language:AR-SA;}
	p.TableContents, li.TableContents, div.TableContents
		{mso-style-name:'Table Contents';
		margin:0cm;
		margin-bottom:.0001pt;
		mso-pagination:widow-orphan no-line-numbers;
		mso-hyphenate:none;
		font-size:12.0pt;
		font-family:'Times New Roman';
		mso-fareast-font-family:'Times New Roman';
		mso-fareast-language:AR-SA;}
	p.TableHeading, li.TableHeading, div.TableHeading
		{mso-style-name:'Table Heading';
		mso-style-parent:'Table Contents';
		margin:0cm;
		margin-bottom:.0001pt;
		text-align:center;
		mso-pagination:widow-orphan no-line-numbers;
		mso-hyphenate:none;
		font-size:12.0pt;
		font-family:'Times New Roman';
		mso-fareast-font-family:'Times New Roman';
		mso-fareast-language:AR-SA;
		font-weight:bold;}
	 /* Page Definitions */
	 @page
		{mso-footnote-position:beneath-text;}
	@page Section1
		{size:595.25pt 841.85pt;
		margin:70.85pt 3.0cm 70.85pt 3.0cm;
		mso-header-margin:36.0pt;
		mso-footer-margin:36.0pt;
		mso-paper-source:0;}
	div.Section1
		{page:Section1;
		mso-footnote-position:beneath-text;}
	 /* List Definitions */
	 @list l0
		{mso-list-id:1;
		mso-list-template-ids:1;}
	@list l0:level1
		{mso-level-number-format:none;
		mso-level-suffix:none;
		mso-level-text:;
		mso-level-tab-stop:0cm;
		mso-level-number-position:left;
		margin-left:0cm;
		text-indent:0cm;}
	@list l0:level2
		{mso-level-number-format:none;
		mso-level-suffix:none;
		mso-level-text:;
		mso-level-tab-stop:0cm;
		mso-level-number-position:left;
		margin-left:0cm;
		text-indent:0cm;}
	@list l0:level3
		{mso-level-number-format:none;
		mso-level-suffix:none;
		mso-level-text:;
		mso-level-tab-stop:0cm;
		mso-level-number-position:left;
		margin-left:0cm;
		text-indent:0cm;}
	@list l0:level4
		{mso-level-number-format:none;
		mso-level-suffix:none;
		mso-level-text:;
		mso-level-tab-stop:0cm;
		mso-level-number-position:left;
		margin-left:0cm;
		text-indent:0cm;}
	@list l0:level5
		{mso-level-number-format:none;
		mso-level-suffix:none;
		mso-level-text:;
		mso-level-tab-stop:0cm;
		mso-level-number-position:left;
		margin-left:0cm;
		text-indent:0cm;}
	@list l0:level6
		{mso-level-number-format:none;
		mso-level-suffix:none;
		mso-level-text:;
		mso-level-tab-stop:0cm;
		mso-level-number-position:left;
		margin-left:0cm;
		text-indent:0cm;}
	@list l0:level7
		{mso-level-number-format:none;
		mso-level-suffix:none;
		mso-level-text:;
		mso-level-tab-stop:0cm;
		mso-level-number-position:left;
		margin-left:0cm;
		text-indent:0cm;}
	@list l0:level8
		{mso-level-number-format:none;
		mso-level-suffix:none;
		mso-level-text:;
		mso-level-tab-stop:0cm;
		mso-level-number-position:left;
		margin-left:0cm;
		text-indent:0cm;}
	@list l0:level9
		{mso-level-number-format:none;
		mso-level-suffix:none;
		mso-level-text:;
		mso-level-tab-stop:0cm;
		mso-level-number-position:left;
		margin-left:0cm;
		text-indent:0cm;}
	ol
		{margin-bottom:0cm;}
	ul
		{margin-bottom:0cm;}
	-->
	</style>
	<!--[if gte mso 10]>
	<style>
	 /* Style Definitions */
	 table.MsoNormalTable
		{mso-style-name:'Tabela normal';
		mso-tstyle-rowband-size:0;
		mso-tstyle-colband-size:0;
		mso-style-noshow:yes;
		mso-style-parent:;
		mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
		mso-para-margin:0cm;
		mso-para-margin-bottom:.0001pt;
		mso-pagination:widow-orphan;
		font-size:10.0pt;
		font-family:'Times New Roman';
		mso-ansi-language:#0400;
		mso-fareast-language:#0400;
		mso-bidi-language:#0400;}
	</style>
	<![endif]-->
	</head>

	<body lang=PT-BR link=navy vlink=purple style='tab-interval:35.4pt'>

	<div class=Section1>

	<p class=MsoNormal align=center style='text-align:center;mso-line-height-alt:
	10.0pt'><b>CONTRATO DE CREDENCIAMENTO DE ASSIST�NCIA T�CNICA<o:p></o:p></b></p>

	<p class=MsoNormal style='mso-line-height-alt:10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'>Pelo
	presente instrumento particular,</p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b>HB
	ASSIST�NCIA T�CNICA LTDA</b>., sociedade empresarial com escrit�rio
	administrativo na Av. Yojiro Takaoka, 4.384 - Loja 17 - Conj. 2083 - Alphaville
	- Santana de Parna�ba, SP, CEP 06.541-038, inscrita no CNPJ sob n�
	08.326.458/0001-47, neste ato representada por seu diretor ao final assinado,
	doravante denominada<span style='mso-spacerun:yes'>�
	</span>&quot;HB-TECH&quot;, e</p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b>$posto_nome.</b>, sociedade empresarial com sede na $endereco,
	$numero $complemento, na cidade de $cidade, $estado, CEP $cep, inscrita no CNPJ sob n�
	$cnpj, neste ato representada por seu administrador, ao final
	assinado, doravante denominada &quot;AUTORIZADA&quot;,</p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b
	style='mso-bidi-font-weight:normal'><span style='mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>Considerando que:<o:p></o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='margin-left:35.45pt;text-align:justify;mso-line-height-alt:
	10.0pt;mso-pagination:none'><b><span style='mso-fareast-language:\#00FF;
	mso-bidi-language:#00FF'>(i) </span></b><span style='mso-fareast-language:\#00FF;
	mso-bidi-language:#00FF'><span style='mso-tab-count:1'>������ </span>a HB TECH
	desenvolveu uma metodologia comercial e novo neg�cio atrav�s da marca HBFLEX,
	ou HBTECH, dentre outras poss�veis, sob a qual vender� produtos
	eletro-eletr�nicos (doravante denominados produtos);<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><b><span style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'>(ii)
	</span></b><span style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'><span
	style='mso-tab-count:1'>����� </span>sem preju�zo de outros produtos poss�veis,
	integram a presente contrata��o: i. DVD Players; ii. DVR Players; iii. MP4; iv.
	Maquinas de Lavar Roupas residenciais; v. Notebooks; vi. Desktops; vii. Ar
	Condicionados Splits; viii. TVs LCD; e ix. Monitores LC;<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><b style='mso-bidi-font-weight:normal'><span style='mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'>(iii) </span></b><span style='mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'>a AUTORIZADA possui a tecnologia e o <i>know how</i>
	de manuten��o e assist�ncia t�cnica dos referidos produtos;<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'>t�m
	entre si, justo e contratado, o seguinte:</p>

	<p class=MsoNormal style='mso-line-height-alt:10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>1- OBJETIVO<o:p></o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>1.1. O objetivo do presente contrato � a presta��o,
	pela AUTORIZADA, em sua sede social, do servi�o de assist�ncia t�cnica aos
	produtos comercializados pela HB-TECH, cuja rela��o consta na tabela de m�o de
	obra, fornecida em anexo e faz parte integrante deste contrato.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>1.2. Os servi�os que ser�o prestados pela AUTORIZADA,
	junto aos clientes usu�rios dos produtos comercializados atrav�s da HB-TECH
	consistem em manuten��o corretiva e preventiva, seja atrav�s de repara��es a
	domicilio cujos custos ser�o por conta do consumidor, ou em sua oficina, quando
	os custos ser�o cobertos pela HB-TECH atrav�s de taxas de garantia.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>1.3. A HB-TECH</span><span style='mso-fareast-font-family:
	'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'> fornecer� � AUTORIZADA todos os elementos necess�rios
	e indispens�veis � boa presta��o dos servi�os em alus�o, desde que sejam de sua
	responsabilidade, especialmente no tocante � qualifica��es e especifica��es
	t�cnicas dos produtos, quando for o caso, tudo previamente autorizado (p.ex.
	desenhos t�cnicos, pe�as de reposi��o para produtos em garantia, treinamento,
	quando necess�rios, dentre outras hip�teses) .<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>2- DA EXECU��O DOS SERVI�OS DURANTE A GARANTIA<o:p></o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>2.1. O prazo e condi��es de garantia dos produtos
	comercializados pela HB-TECH, s�o especificados no certificado de garantia,
	cujo in�cio � contado a partir da data emiss�o da nota fiscal de compra do
	produto pelo primeiro usu�rio.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>2.2. Se no per�odo de garantia os equipamentos
	apresentarem defeitos de fabrica��o, a AUTORIZADA providenciar� o reparo
	utilizando exclusivamente pe�as originais sem qualquer �nus.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>2.3. Para atendimento <st1:PersonName
	ProductID='em garantia a AUTORIZADA' w:st='on'>em garantia a AUTORIZADA</st1:PersonName>
	exigir�, do cliente usu�rio, a apresenta��o da NOTA FISCAL DE COMPRA.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>2.4. A ordem de servi�o utilizada pela AUTORIZADA para
	consumidores, dever� ser individual e conter:<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>- N�MERO DE S�RIE<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>- DATA DA CHEGADA NA AUTORIZADA<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>- N�MERO DA NOTA FISCAL<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>- DATA DA COMPRA<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>- NOME DO CLIENTE<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>- NOME DO REVENDEDOR � TELEFONE.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>- COMPONENTES TROCADOS<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>- ENDERE�O COMPLETO DO CLIENTE<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>- MODELO DO EQUIPAMENTO<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>- DATA DA RETIRADA DO APARELHO<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>-DEFEITO CONSTATADO DE ACORDO COM TABELA FORNECIDA
	PARA TAL.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>3- PRE�O E CONDI��ES DE PAGAMENTO<o:p></o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>3.1. Para consertos efetuados em aparelhos no per�odo
	de garantia, a HB-TECH pagar� � AUTORIZADA os valores de taxas de acordo com a
	tabela fornecida em anexo, a qual faz parte integrante deste contrato.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>3.2. O pagamento dos servi�os prestados em garantia
	ser� efetuado da seguinte forma:<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>A) A AUTORIZADA dever� encaminhar at� o dia 07 (sete)
	de cada m�s subseq�ente ao atendimento: <o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'>(i) Ordens de servi�o individuais devidamente
	preenchidas (item 4.7), ACOMPANHADAS DAS RESPECTIVAS C�PIAS DA N.F. DE VENDA AO
	CONSUMIDOR.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'>(ii) Ordens de servi�o coletivas devidamente
	preenchidas ACOMPANHADAS DAS RESPECTIVAS C�PIAS DAS NOTAS FISCAIS DE ENTRADA E SA�DA.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:36.0pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>B) Depois de efetuado o c�lculo pela HB-TECH, ser�
	solicitada a Nota fiscal de servi�os, (original) emitida contra:<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'>HB ASSIST�NCIA T�CNICA LTDA.</p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'>Av. Yojiro Takaoka, 4.384 - Loja 17 - Conj. 2083 - Alphaville � </p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'>Santana de Parna�ba, SP, CEP 06.541-038</p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'>CNPJ 08.326.458/0001-47</p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>E DEVER� ENVIAR A MESMA PARA O ENDERE�O ABAIXO:<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'>HB ASSIST�NCIA T�CNICA LTDA.</p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'>Av. Yojiro Takaoka, 4.384 - Loja 17 - Conj. 2083 - Alphaville � </p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'>Santana de Parna�ba, SP, CEP 06.541-038</p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>C) A nota fiscal dever� estar na filial HB-TECH at� o
	�ltimo �til dia do m�s em curso e discriminar no corpo da mesma o seguinte:
	�SERVI�OS PRESTADOS <st1:PersonName
	ProductID='EM APARELHOS DE SUA COMERCIALIZA��O' w:st='on'>EM APARELHOS DE SUA
	 COMERCIALIZA��O</st1:PersonName>, SOB GARANTIA DURANTE O M�S DE� (AS NOTAS
	FISCAIS RECEBIDAS AP�S 90 (NOVENTA) DIAS N�O SER�O PAGAS).<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>D) De posse da documenta��o a HB-TECH far� confer�ncia
	para averiguar poss�veis distor��es:<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'>(i) Pagamento das taxas de garantia ser�
	efetuado no quinto dia �til do m�s subseq�ente, para as NF recebidas at� o
	�ltimo dia �til do m�s anterior, em forma de cr�dito em conta corrente da
	pessoa jur�dica. Qualquer altera��o na conta corrente do servi�o autorizado deve
	ser comunicado previamente � HB-TECH.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'>(ii) HB-TECH reserva-se o direito de efetuar
	dedu��es de d�bitos pendentes, duplicatas, despesas banc�rias e de protesto referentes
	a t�tulos n�o quitados, ordens de servi�o irregulares, pe�as trocadas em
	garantia e n�o devolvidas no prazo m�ximo de 60 (sessenta) dias, sem pr�via
	consulta ou permiss�o da AUTORIZADA.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:36.0pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>E) Valores inferiores a R\$ 20,00 (vinte Reais), ser�o
	acumulados at� o pr�ximo cr�dito e assim sucessivamente, at� que o valor
	acumulado ultrapasse o disposto nesta cl�usula.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>(i) Apenas ser�o aceitas ordens de servi�o do mesmo
	cliente cujo prazo entre atendimentos, para o mesmo defeito, for superior a 60
	(sessenta) dias, ap�s a retirada do produto.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'>(ii) Ordens de servi�o incompletas n�o ser�o
	aceitas.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='margin-left:35.4pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'>(iii) A HB-TECH n�o se responsabiliza por
	atrasos de pagamento cuja causa seja de responsabilidade da AUTORIZADA.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:18.0pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>F) O PRAZO M�XIMO QUE A AUTORIZADA PODER� RETER AS
	ORDENS DE SERVI�O, AP�S A SA�DA DO PRODUTO, DE SUA EMPRESA, SER� DE 90 DIAS,
	EXCETUANDO-SE O M�S DESSA SA�DA. AP�S ESSE PRAZO, AS ORDENS DE SERVI�O NELE
	ENQUADRADAS PERDER�O O DIREITO AO CR�DITO DE TAXAS DE GARANTIA.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>G) A AUTORIZADA enviar�, quando solicitado, os
	componentes substitu�dos em garantia, devidamente identificados com as
	etiquetas fornecidas pela HB-TECH, para que seja efetuada a inspe��o do
	controle de qualidade e a devida reposi��o quando for o caso. O frete desta
	opera��o ser� por conta da HB-TECH.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>H) Os comprovantes de pagamento de sedex, quando
	antecipados pela AUTORIZADA, dever�o ser enviados � HB-TECH, juntamente com o
	movimento de O. S., em prazo n�o superior a 90 dias da data da emiss�o do
	mesmo. Comprovantes recebidos ap�s o per�odo retro citado n�o ser�o
	reembolsados.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>4 - DURA��O DO CONTRATO<o:p></o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>4.1. A validade do presente contrato � por tempo
	indeterminado e poder� ser rescindido por qualquer das partes, mediante um
	aviso pr�vio de 30 (trinta) dias, por escrito e protocolado. A autorizada
	obriga-se, neste prazo do aviso, a dar continuidade aos atendimentos dos
	produtos em seu poder.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>4.2. O cancelamento deste contrato com fulcro na
	cl�usula anterior n�o dar� direito a nenhuma das partes a indeniza��o, cr�dito
	ou reembolso, seja a que t�tulo, forma ou hip�tese for.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>4.3. O contrato ser� imediatamente rescindido caso
	seja constatada e comprovada irregularidade na cobran�a dos servi�os e pe�as
	prestados em equipamentos sob garantia da HB-TECH, transfer�ncia da empresa
	para novos s�cios, mudan�a de endere�o para �rea fora do interesse da HB-TECH,
	concordata, fal�ncia, liquida��o judicial ou extrajudicial.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:18.0pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>4.4. Observada qualquer situa��o prevista nesta
	cl�usula, o representante indicado pela HB-TECH ter� plena autonomia para
	interceder junto � AUTORIZADA, no sentido de recolher incontinenti, as
	documenta��es, materiais, luminosos e tudo aquilo que de qualquer forma, for de
	origem, relacionar ou pertencer ao patrim�nio da HB-TECH e em perfeito estado
	de conserva��o e uso, sob pena de submeter a ent�o AUTORIZADA ao processo de
	indeniza��o na forma da lei.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>4.5. No caso de rescis�o contratual, a AUTORIZADA se
	obriga a devolver � HB-TECH toda documenta��o t�cnica e administrativa cedida
	para seu uso enquanto CREDENCIADA.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>4.6. Fica expressamente estipulado que este contrato
	n�o cria, sob hip�tese alguma, vinculo empregat�cio, direitos ou obriga��es
	previdenci�rias ou secund�rias entre as partes, ficando a cargo exclusivo da
	AUTORIZADA todos impostos taxas e encargos de qualquer natureza, incidentes
	sobre suas atividades.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:18.0pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>5 - �REA DE ATUA��O DA AUTORIZADA<o:p></o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>5.1. A presta��o de servi�os ser� exercida pela
	AUTORIZADA na �rea que lhe for destinada, cujos limites poder�o ser modificados
	com o tempo, desde que tal medida se fa�a necess�ria para melhorar o
	atendimento aos consumidores de aparelhos comercializados pela HB-TECH.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>6 � MARCAS E PROPRIEDADE INDUSTRIAL<o:p></o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'>6.1.
	As marcas, s�mbolos, nomes, identifica��o visual e direitos autorais que s�o de
	titularidade exclusiva da HB-TECH dever�o ser preservados, sendo que a
	AUTORIZADA reconhece e aceita a propriedade das mesmas, comprometendo-se e
	obrigando-se a preservar todas as suas caracter�sticas e reputa��o.</p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'>6.2. A reputa��o das marcas e produtos da
	HB-TECH dever�o ser preservadas, <u>constituindo-se infra��o grav�ssima ao
	presente contrato, bem como � legisla��o de propriedade industrial e penal
	brasileira vigente</u>, a ofensa � integridade, qualidade, conformidade,
	estabilidade e reputa��o, dentre outros quesitos, por parte da AUTORIZADA, seus
	s�cios e/ou funcion�rios e colaboradores.<o:p></o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'>6.2.1. Considera-se, igualmente, como
	infra��es nos termos do item 6.2. acima, difama��es e outras pr�ticas
	envolvendo marcas e produtos da HB-TECH por parte<span
	style='mso-spacerun:yes'>� </span>da AUTORIZADA, seus s�cios e/ou funcion�rios
	e colaboradores, seja perante outras autorizadas, outros fabricantes,
	representantes e inclusive, o p�blico consumidor. <o:p></o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'>6.2.2. Nestes termos do item 6.2.1. A
	HB-TECH poder� ter consultores de campo e auditores para averiguar e apurar
	eventuais irregularidades, enviando aos postos autorizados profissionais com ou
	sem identifica��o, que ser�o posteriormente alocados como testemunhas para
	todos os efeitos civis e criminais.<o:p></o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'>6.3. <span style='mso-tab-count:1'>���� </span>Os
	sinais distintivos da HB-TECH n�o poder�o ser livremente utilizados pela
	AUTORIZADA, mas t�o somente no que diga respeito, estritamente, ao desempenho
	de suas atividades aqui ajustadas. <o:p></o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'>6.4. As marcas, desenhos ou quaisquer
	sinais distintivos n�o poder�o sofrer qualquer altera��o da AUTORIZADA,
	inclusive quanto a cores, propor��es dos tra�os, sonoridade etc.<o:p></o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman'>6.5. � vedado o uso de qualquer sinal
	distintivo ou refer�ncia ao nome da HB-TECH quando n�o expressamente autorizado
	ou determinado por esta �ltima. <o:p></o:p></span></p>

	<h3 style='margin:0cm;margin-bottom:.0001pt;text-align:justify;mso-line-height-alt:
	10.0pt;mso-list:none;tab-stops:0cm'><span style='font-size:12.0pt;font-family:
	Times New Roman;mso-bidi-font-family:Arial;font-weight:normal'><o:p>&nbsp;</o:p></span></h3>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'>6.6.
	Al�m das obriga��es j� assumidas, a AUTORIZADA se compromete e se obriga,
	durante o prazo do presente Contrato, e mesmo ap�s seu t�rmino ou rescis�o, a:
	(i) n�o utilizar, manusear ou possuir de qualquer forma, direta ou
	indiretamente, a marca, ou qualquer outro termo, express�o ou s�mbolo com o
	mesmo significado, que seja semelhante, ou que possa confundir o consumidor com
	as marcas da HBTECH; (ii) n�o utilizar a marca como parte da raz�o social de
	qualquer empresa que detenha qualquer participa��o, atualmente ou no futuro,
	ainda que como nome fantasia, no Cadastro Nacional de Pessoas Jur�dicas � CNPJ
	� do Minist�rio da Fazenda � Secretaria da Receita Federal; (iii)<span
	style='mso-tab-count:1'>������� </span>n�o registrar ou tentar registrar marca
	id�ntica ou semelhante, quer direta ou indiretamente, seja no Brasil ou <st1:PersonName
	ProductID='em qualquer outro Pa�s' w:st='on'>em qualquer outro Pa�s</st1:PersonName>
	ou territ�rio.</p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt;
	tab-stops:0cm'><span style='font-family: Times New Roman'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt;
	tab-stops:0cm'><span style='font-family: Times New Roman;mso-bidi-font-family:
	Arial'>6.7.<b> </b><span style='mso-bidi-font-weight:bold'><span
	style='mso-tab-count:1'></span></span></span><span style='font-family:
	Times New Roman'>Igualmente integram as obriga��es assumidas pela AUTORIZADA
	todas as obriga��es de sigilo, confidencialidade, n�o transmiss�o, cess�o ou
	outras formas de prote��o da tecnologia, <i>know-how</i>, desenvolvimentos, </span><span
	style='font-family: Times New Roman ;mso-fareast-language:#00FF;mso-bidi-language:
	#00FF'>desenhos t�cnicos, dados t�cnicos da HB-TECH. Nestas obriga��es
	incluem-se todas as prote��es da legisla��o brasileira vigente, especialmente
	as da Lei de Propriedade Industrial.<o:p></o:p></span></p>

	<p class=MsoBodyTextIndent style='margin-left:0cm;mso-line-height-alt:10.0pt'><span
	style='font-family: Times New Roman;mso-bidi-font-family:'DejaVu Sans';
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoBodyText style='margin-bottom:0cm;margin-bottom:.0001pt;text-align:
	justify;mso-line-height-alt:10.0pt'>6.8. <span style='mso-tab-count:1'>���� </span><span
	style='mso-bidi-font-family:Arial;mso-bidi-font-weight:bold'>Qualquer
	transgress�o das normas aqui estabelecidas acarretar� � AUTORIZAD</span><span
	style='mso-bidi-font-family:Arial'>A e seus s�cios<span style='mso-bidi-font-weight:
	bold'>, n�o obstante a responsabilidade de seus funcion�rios, al�m da rescis�o
	deste instrumento e pagamento de perdas e danos, as san��es previstas na
	legisla��o especial de marcas e patentes, e legisla��o penal vigente.<o:p></o:p></span></span></p>

	<p class=MsoBodyText style='margin-bottom:0cm;margin-bottom:.0001pt;text-align:
	justify;mso-line-height-alt:10.0pt'><b><o:p>&nbsp;</o:p></b></p>

	<p class=MsoBodyText style='margin-bottom:0cm;margin-bottom:.0001pt;text-align:
	justify;mso-line-height-alt:10.0pt'><b><o:p>&nbsp;</o:p></b></p>

	<p class=MsoBodyText style='margin-bottom:0cm;margin-bottom:.0001pt;text-align:
	justify;mso-line-height-alt:10.0pt'><b>7 - SIGILO E N�O-CONCORR�NCIA<o:p></o:p></b></p>

	<p class=MsoBodyText style='margin-bottom:0cm;margin-bottom:.0001pt;text-align:
	justify;mso-line-height-alt:10.0pt'>7.1.<b> </b><span style='mso-tab-count:
	1'>���� </span>Obriga-se a AUTORIZADA a manter sigilo quanto ao conte�do dos
	manuais,<span style='mso-spacerun:yes'>� </span>treinamentos, tecnologia ou de
	quaisquer outras informa��es que vier a receber da HB-TECH, ou que tomar
	conhecimento, em virtude da presente contrata��o, devendo no caso de t�rmino ou
	rescis�o da mesma, ser efetuada inspe��o e invent�rio sob supervis�o da HBTECH
	e/ou empresa parceira ou indicada para tal, ficando a AUTORIZADA, neste caso,
	obrigado a devolver imediatamente todo o material recebido e em seu poder.</p>

	<p class=MsoBodyText style='margin-bottom:0cm;margin-bottom:.0001pt;mso-line-height-alt:
	10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'>7.1.1.<b> </b>S�o
	consideradas confidenciais, para fins desta cl�usula, todas e quaisquer informa��es
	que digam respeito aos neg�cios, desenhos t�cnicos, treinamentos, estrat�gia de
	neg�cios, f�rmulas, marcas, registros, dados comerciais, financeiros e
	estrat�gicos, bem como todos e quaisquer dados relativos �s atividades externas
	e internas das partes, sobre os produtos e marcas, informa��es estas
	fornecidas, a respeito das quais as partes venham a tomar conhecimento em
	virtude do presente contrato.<o:p></o:p></span></p>

	<p class=MsoBodyText style='margin-bottom:0cm;margin-bottom:.0001pt;text-align:
	justify;mso-line-height-alt:10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoBodyText style='margin-bottom:0cm;margin-bottom:.0001pt;text-align:
	justify;mso-line-height-alt:10.0pt'>7.2. <span style='mso-tab-count:1'>���� </span>A
	AUTORIZADA,<b> </b>seus s�cios, diretores, prepostos, colaboradores ou
	empregados, n�o poder�o fazer ou permitir que se fa�am c�pias dos manuais,
	sistema informatizado, material promocional ou qualquer outra informa��o
	caracterizada como confidencial fornecida pela HB-TECH. Qualquer comprovada
	viola��o ao sigilo ora pactuado, a qualquer tempo, por parte da AUTORIZADA,<b> </b>seus
	s�cios, diretores, prepostos, colaboradores, ou empregados, acarretar� o
	pagamento da indeniza��o prevista neste instrumento, sem preju�zo das demais
	disposi��es legais ou contratuais cab�veis.</p>

	<p class=MsoBodyText style='margin-bottom:0cm;margin-bottom:.0001pt;mso-line-height-alt:
	10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoBodyText style='margin-bottom:0cm;margin-bottom:.0001pt;text-align:
	justify;mso-line-height-alt:10.0pt'>7.3. <span style='mso-bidi-font-family:
	Tahoma'>Considerando as negocia��es efetuadas entre as partes, na fase
	pr�-contratual, � motivo de rescis�o imediata do presente contrato, com o
	imediato fechamento da �unidade autorizada�, qualquer viola��o de sigilo deste
	contrato e da negocia��o efetuada, tendo em vista princ�pios de probidade e de
	boa-f�. Qualquer vazamento de informa��o ser� compreendido como ato de
	irresponsabilidade e m�-f�, acarretando os efeitos da responsabilidade por
	quebra de obriga��es contratuais e falta grave de viola��o de dever de sigilo,
	rescindindo este contrato, independentemente, da cobran�a de quaisquer
	indeniza��es por perdas e danos.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:Tahoma'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='margin-right:.75pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:
	Tahoma;mso-fareast-language:#00FF;mso-bidi-language:#00FF'>7.4. A AUTORIZADA, </span>seus
	s�cios, diretores, prepostos, colaboradores ou empregados <span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'>considerando este contrato,
	a negocia��o realizada e o disposto no item k) anterior, obrigam-se a: (i) n�o
	copiar, reproduzir, transferir, ceder, divulgar ou transmitir as informa��es
	confidenciais e dados da presente negocia��o, seja a que t�tulo for; (ii)
	abster-se de falar, comentar, expor ou induzir observa��es ou assuntos que
	possam fazer refer�ncia aos neg�cios da franquia, fora do �mbito do
	desenvolvimento de suas atividades envolvendo os neg�cios da empresa,
	incluindo-se conversas externas �s depend�ncias da </span><span
	style='mso-bidi-font-family:Tahoma'>�unidade autorizada�</span><span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'>, escrit�rios de advogados
	da HB-TECH e/ou da AUTORIZADA, tais como elevadores, escadas, halls, banheiros,
	restaurantes, bares, festas, dentre outros; (iii) abster-se de tratar de
	assuntos da Franquia com terceiros, amigos ou parceiros de outros neg�cios, em
	quaisquer locais privados e/ou p�blicos quando n�o na consecu��o de suas
	atividades, dentre eles sagu�es de aeroportos, rodovi�rias ou no interior de
	transportes p�blicos; (iv)<span style='mso-spacerun:yes'>� </span>n�o entregar
	por qualquer meio, dentre eles, fax, <i style='mso-bidi-font-style:normal'>email</i>,
	correio, qualquer material referente aos neg�cios da franquia, salvo com expressa
	autoriza��o por escrito da HB-TECH, com qualquer tipo de processo ou informa��o
	dos referidos neg�cios.<o:p></o:p></span></p>

	<p class=MsoBodyText style='margin-bottom:0cm;margin-bottom:.0001pt;text-align:
	justify;mso-line-height-alt:10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'>8 - RESPONSABILIDADES<o:p></o:p></span></b></p>

	<p class=MsoNormal style='margin-right:2.15pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'New York';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>8.1.<b> </b>A AUTORIZADA assume integral
	responsabilidade pelo pagamento das remunera��es devidas a seus funcion�rios,
	pelo recolhimento de todas as contribui��es e tributos incidentes, bem como
	pelo cumprimento da legisla��o social, trabalhista, previdenci�ria e
	securit�ria aplic�vel. Igualmente, a HB-TECH assume integral responsabilidade
	pelo pagamento das remunera��es devidas a seus funcion�rios, pelo recolhimento
	de todas as contribui��es e tributos incidentes, bem como pelo cumprimento da
	legisla��o social, trabalhista, previdenci�ria e securit�ria aplic�vel.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'>8.2.<b> </b>As
	partes responder�o, individualmente, por reivindica��es de seus funcion�rios
	que sejam indevidamente endere�ados � outra. A parte que der causa �
	reivindica��o dever�<span style='mso-spacerun:yes'>� </span>assumir ao a��es de
	defesa necess�rias, e, em �ltima inst�ncia, indenizar� a parte reclamada das
	eventuais condena��es que lhe venham a ser imputadas, inclusive das despesas e
	honor�rios advocat�cios.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:35.45pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'>8.3.<b> </b>�
	expressamente vedado �s partes, sem que para tanto esteja previamente
	autorizada por escrito, contrair em nome da outra qualquer tipo empr�stimo ou
	assumir em seu nome qualquer obriga��o que implique na outorga de garantias.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:35.45pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'>8.4.<b> </b>As
	partes n�o assumem qualquer v�nculo, exceto aqueles expressamente acordados
	atrav�s do presente instrumento, obrigando-se ao cumprimento da legisla��o
	social, trabalhista, previdenci�ria e securit�ria aplic�vel.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'>8.5. As obriga��es e
	responsabilidades aqui assumidas pelas partes tem in�cio a partir da data da
	assinatura do presente instrumento, n�o se responsabilizando reciprocamente, em
	hip�tese alguma por erros, dolo, e qualquer outro motivo que possa recair sobre
	a administra��o das partes contratantes.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-language:#00FF;mso-bidi-language:#00FF'>8.6.<b> </b><span
	style='mso-tab-count:1'>���� </span>Em caso de quaisquer infra��es ao presente
	contrato, que possam implicar em perda de cr�dito,<span
	style='mso-spacerun:yes'>� </span>ou de alguma forma atingir a imagem da
	HB-TECH junto ao p�blico consumidor, a AUTORIZADA</span>,<b> </b>seus s�cios,
	diretores, prepostos, colaboradores ou empregados,<span style='mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'> poder� ser responsabilizada por meio de
	procedimento judicial pr�prio, inclusive podendo ser condenada em perdas e
	danos.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'>8.7.<b> </b><span
	style='mso-tab-count:1'>���� </span>Em caso de a��es propostas por
	consumidores, que reste provada a culpa ou dolo da AUTORIZADA, </span>seus
	s�cios, diretores, prepostos, colaboradores ou empregados, <span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'>esta concorda desde j� que
	dever� assumir e integrar o polo passivo das a��es judiciais que venham a ser
	demandadas contra a HB-TECH, isentando a mesma, e ressarcindo quaisquer valores
	que ela venha a ser condenada a pagar e/ou tenha pago.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9- DISPOSI��ES GERAIS<o:p></o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.1. A AUTORIZADA, ap�s a regular aprova��o de seu
	credenciamento, passar� � condi��o de CREDENCIADA para presta��o de servi�os de
	assist�ncia t�cnica aos produtos comercializados pela HB-TECH.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.2. A AUTORIZADA declara neste ato, estar ciente que
	dever� manter, por sua conta e risco, seguro contra roubo e inc�ndio cujo valor
	da ap�lice seja suficiente para cobrir sinistro que possa ocorrer em seu
	estabelecimento, envolvendo patrim�nio pr�prio e/ou de terceiros. Caso n�o o
	fa�a assume total responsabilidade e responder� civil e criminalmente pela
	omiss�o, perante terceiros e a HB-TECH.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.3. A AUTORIZADA - Declara conhecer e se compromete a
	cumprir o disposto no C�digo de Defesa do Consumidor e assume a
	responsabilidade de �in vigilando� por seus funcion�rios para esta finalidade.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.4. A AUTORIZADA responder� por seus atos, caso
	terceiros prejudicados vierem a reclamar diretamente � HB-TECH. Esta exercer� o
	direito de regresso acrescido de custas, honor�rios advocat�cios, al�m de
	perdas e danos incidentes, inclusive danos punitivos.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.5. A HB-TECH fornecer� apoio t�cnico/administrativo,
	al�m de documenta��o e treinamento. Fica estabelecido para a AUTORIZADA o
	compromisso de sigilo referente � documenta��o recebida, ficando reservado
	�nica e exclusivamente � AUTORIZADA o uso da documenta��o t�cnica. Caso seja
	comprovada a quebra do sigilo ou a utiliza��o dos componentes fornecidos em
	garantia em outros equipamentos, n�o comercializados pela HB-TECH, esta ter� o
	direito de tomar as provid�ncias legais, podendo exigir repara��o por perdas e
	danos que vier a sofrer.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.6. Toda correspond�ncia (documenta��o, notas
	fiscais, comunicados, etc.) dever� ser enviada para o endere�o especificado no
	pre�mbulo deste contrato.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.7. Caso a AUTORIZADA tenha necessidade de enviar �
	HB-TECH placas, m�dulos ou equipamentos para conserto, dever� obter uma senha
	com o inspetor ou t�cnico de plant�o. O aparelho dever� estar acompanhado de
	nota fiscal de remessa para conserto, e da ficha t�cnica e em especial da c�pia
	da O.S., devidamente preenchidas.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.8. Os componentes solicitados para uma determinada
	O. S. s� poder�o ser usados para ela e dever�o constar na mesma. A aus�ncia
	dessa O. S. na HB-TECH, decorrido o prazo descrito no item 3.2 - 2 E, dar�
	direito � HB-TECH de fatur�-los contra a AUTORIZADA.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.9. A HB-TECH fornecer� � AUTORIZADA, tabela de
	pre�os de componentes com valores � vista.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.10. A HB-TECH fornecer�, como antecipa��o, os
	componentes para atender aparelhos na garantia, comercializados por ela, desde
	que seja mencionado, em pedido pr�prio, o n�mero da respectiva O.S.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.11. O atendimento descrito no item anterior ser�
	suspenso quando a AUTORIZADA, por falta de devolu��o de componentes defeituosos,
	ou causas correlatas, acumular um valor superior ao seu limite de cr�dito.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.12. Os Pedidos de venda ser�o atendidos, com
	desconto de 20% e frete por conta do comprador. Os itens que n�o estiverem
	dispon�veis em estoque ser�o cancelados. Este desconto � v�lido especificamente
	para os pedidos de venda, n�o sendo aplic�vel ao valor de pe�as n�o devolvidas.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.13. Os d�bitos n�o quitados no vencimento, ser�o
	descontados do primeiro movimento de ORDENS DE SERVI�O, ap�s esse vencimento,
	acrescidos de juros de mercado proporcionalmente aos dias de atraso. A HB-TECH
	poder� optar por outra forma de cobran�a que melhor lhe convier.<o:p></o:p></span></p>

	<p class=MsoNormal style='margin-left:18.0pt;text-align:justify;mso-line-height-alt:
	10.0pt'><span style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:
	#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>9.14. </span><span style='mso-bidi-font-family:'New York';
	letter-spacing:-.15pt;mso-fareast-language:#00FF;mso-bidi-language:#00FF'>As
	partes declaram ter recebido o presente instrumento com anteced�ncia necess�ria
	para a correta e atenta leitura e compreens�o de todos os seus termos, direitos
	e obriga��es, bem como foram prestados mutuamente todos os esclarecimentos
	necess�rios e obrigat�rios, e a inda que entendem, reconhecem e concordam com
	os termos e condi��es aqui ajustadas, ficando assim caracterizada a probidade e
	boa-f� de todas as partes contratantes.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt;
	tab-stops:0cm'><span style='mso-bidi-font-family:'New York';letter-spacing:
	-.15pt;mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt;
	tab-stops:0cm'><span style='mso-bidi-font-family:'New York';letter-spacing:
	-.15pt;mso-fareast-language:#00FF;mso-bidi-language:#00FF'>9.15.<span
	style='mso-tab-count:1'>��� </span>A eventual declara��o judicial de nulidade
	ou inefic�cia de qualquer das disposi��es deste contrato n�o prejudicar� a
	validade e efic�cia das demais cl�usulas, que ser�o integralmente cumpridas,
	obrigando-se as partes a envidar seus melhores esfor�os de modo a validamente
	alcan�arem os mesmos efeitos da disposi��o que tiver sido anulada ou tiver se
	tornado ineficaz.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt;
	tab-stops:0cm'><span style='mso-bidi-font-family:'New York';letter-spacing:
	-.15pt;mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt;
	tab-stops:0cm'><span style='mso-bidi-font-family:'New York';letter-spacing:
	-.15pt;mso-fareast-language:#00FF;mso-bidi-language:#00FF'>9.16. <span
	style='mso-tab-count:1'>�� </span>O n�o exerc�cio ou a ren�ncia, por qualquer
	das partes, de direito, termo ou disposi��o previstos ou assegurados neste
	contrato, n�o significar� altera��o ou nova��o de suas disposi��es e condi��es,
	nem prejudicar� ou restringir� os direitos de tal parte, n�o impedindo o
	exerc�cio do mesmo direito em �poca subseq�ente ou em id�ntica ou an�loga
	ocorr�ncia posterior, nem isentando as demais partes do integral cumprimento de
	suas obriga��es conforme aqui previstas.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt;
	tab-stops:0cm'><span style='mso-bidi-font-family:'New York';letter-spacing:
	-.15pt;mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt;
	tab-stops:0cm'><span style='mso-bidi-font-family:'New York';letter-spacing:
	-.15pt;mso-fareast-language:#00FF;mso-bidi-language:#00FF'>9.17. <span
	style='mso-tab-count:1'>�� </span>Este contrato cont�m o acordo integral e
	final das partes, com respeito �s mat�rias aqui tratadas, substituindo todos os
	entendimentos verbais e/ou escrito entre elas, com respeito �s opera��es aqui
	contempladas. Nenhuma altera��o ou modifica��o deste contrato tornar-se-�
	efetiva, saldo se for por escrito e assinada pelas partes.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'New York';mso-fareast-language:#00FF;mso-bidi-language:
	#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'New York';mso-fareast-language:#00FF;mso-bidi-language:
	#00FF'>9.18. <span style='mso-tab-count:1'>�� </span>Este contrato obriga e beneficia
	as partes signat�rias e seus respectivos sucessores e representantes a qualquer
	t�tulo. A AUTORIZADA n�o pode transferir ou ceder qualquer dos direitos ou
	obriga��es aqui estabelecidas sem o pr�vio consentimento por escrito da
	HB-TECH.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'New York';mso-fareast-language:#00FF;mso-bidi-language:
	#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'New York';mso-fareast-language:#00FF;mso-bidi-language:
	#00FF'>9.19. <span style='mso-tab-count:1'>�� </span>Este contrato � celebrado
	com a inten��o �nica e exclusiva de benef�cio das partes signat�rias e seus
	respectivos sucessores e representantes, e nenhuma outra pessoa ou entidade
	deve ter qualquer direito de se basear neste contrato para reivindicar ou adquirir
	qualquer benef�cio aqui previsto.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'New York';mso-fareast-language:#00FF;mso-bidi-language:
	#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'New York';mso-fareast-language:#00FF;mso-bidi-language:
	#00FF'>9.20. <span style='mso-tab-count:1'>�� </span>As disposi��es constantes
	no pre�mbulo deste contrato constituem parte integrante e insepar�vel do mesmo
	para todo os fins de direito, devendo subsidiar e orientar, seja na esfera
	judicial ou extrajudicial, qualquer diverg�ncia ou porventura venha a existir
	com rela��o ao aqui pactuado.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>7 - FORO<o:p></o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>Estando de pleno acordo com todas as cl�usulas e
	condi��es aqui expostas, elegem as partes contratantes o Foro da Comarca da
	Cidade de S�o Paulo, para dirimir e resolver toda e qualquer quest�o,
	proveniente do presente contrato, com expressa renuncia de qualquer outro, por
	mais privilegiado que seja. E por estarem assim contratados, firmam o presente
	em duas vias do mesmo teor e para um s� efeito, na presen�a de duas testemunhas.
	S�o Paulo.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify'><span style='mso-fareast-font-family:
	'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'>E, por estarem assim justas e acertadas, firmam o
	presente instrumento, em duas vias de igual teor e forma, juntamente com as
	testemunhas abaixo indicadas.<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify'><span style='mso-fareast-font-family:
	'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal align=center style='text-align:center;mso-line-height-alt:
	10.0pt'><span style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:
	Tahoma;mso-fareast-language:#00FF;mso-bidi-language:#00FF'>S�o Paulo, $data_contrato <o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0
	 style='margin-left:2.75pt;border-collapse:collapse;mso-padding-alt:2.75pt 2.75pt 2.75pt 2.75pt'>
	 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
	  <td width=265 valign=top style='width:198.8pt;padding:2.75pt 2.75pt 2.75pt 2.75pt'>
	  <p class=MsoNormal align=center style='text-align:center;mso-line-height-alt:
	  10.0pt;layout-grid-mode:char'>HB ASSIST�NCIA T�CNICA LTDA.</p>
	  </td>
	  <td width=302 valign=top style='width:226.3pt;padding:2.75pt 2.75pt 2.75pt 2.75pt'>
	  <p class=MsoNormal align=center style='text-align:center;mso-line-height-alt:
	  10.0pt;layout-grid-mode:char'>$posto_nome.</p>
	  </td>
	 </tr>
	</table>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><o:p>&nbsp;</o:p></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-bidi-font-family:'DejaVu Sans';mso-fareast-language:#00FF;
	mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><b
	style='mso-bidi-font-weight:normal'><span style='mso-fareast-font-family:'Lucida Sans Unicode';
	mso-bidi-font-family:Tahoma;mso-fareast-language:#00FF;mso-bidi-language:#00FF'>Testemunhas:<o:p></o:p></span></b></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'><o:p>&nbsp;</o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'>________________________________
	<span style='mso-tab-count:1'>����� </span>_______________________________<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'>Nome: <span style='mso-tab-count:
	6'>������������������               �����������������������������  </span>Nome:<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'>RG: <span style='mso-tab-count:
	6'>��������������������������������������������������������������� </span>RG:<o:p></o:p></span></p>

	<p class=MsoNormal style='text-align:justify;mso-line-height-alt:10.0pt'><span
	style='mso-fareast-font-family:'Lucida Sans Unicode';mso-bidi-font-family:Tahoma;
	mso-fareast-language:#00FF;mso-bidi-language:#00FF'>CPF: <span
	style='mso-tab-count:6'>������������������������������������������������������������� </span>CPF:<o:p></o:p></span></p>

	</div>

	</body>

	</html>
	";

if(strlen($msg_erro) == 0){
	$abrir = fopen("/www/assist/www/credenciamento/contrato_hbtech.htm", "w+");
	if (!fwrite($abrir, $conteudo)) {
		$msg_erro = "Erro escrevendo no arquivo ($filename)";
	}
	fclose($abrir); 
}

echo "Contrato criado";

exit 0;
