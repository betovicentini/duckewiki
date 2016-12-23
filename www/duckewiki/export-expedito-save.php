<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Expedito Exportação';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$export_filename = "exportExpeditodata_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
$export_gpsdata = "exportExpeditoGPSdata_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
$export_meta2 = "exportExpeditoGPSdata_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_EXPLAINCOLS.csv";
$export_meta = "exportExpeditodata_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_EXPLAINCOLS.csv";
echo "
<br />
<table class='myformtable' cellpadding='7' align='center' width='50%'>
<thead>
<tr><td colspan='100%'>Resultados</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
    <td>1.</td>
    <td><a href=\"download.php?file=temp/".$export_filename."\">Baixar dados método expedito</a></td>
    <td><a href=\"download.php?file=temp/".$export_meta."\">Explicação das colunas</a></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
    <td>2.</td>
    <td><a href=\"download.php?file=temp/".$export_gpsdata."\">Dados de GPS dos pontos exportados</a></td>
    <td><a href=\"download.php?file=temp/".$export_meta2."\">Explicação das colunas</a></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' class='tdformnotes'>*Os arquivos são separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td>
</tr>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>