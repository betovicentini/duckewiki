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
$ispopup=1;
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
$title = 'Export especímenes';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$export_filename_metadados = "especimenes_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_definicoesDAScolunas.csv";
$export_filename = "especimenes_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
$nrecs = $_SESSION['exportnresult']+0;
$nfields = $_SESSION['exportnfields']+0;
echo "
<br />
<table class='myformtable' cellpadding='5' align='center' width=70%>
<thead>
<tr><td colspan='2'>Resultados</td></tr>
</thead>
<tbody>";
if ($qu) {
echo "
<tr><td><b>Nenhum registro foi encontrado!</b></td></tr>";
} else {
echo "
<tr>
  <td><b>$nrecs</b> registros foram preparados</td>
    <td><a href=\"download.php?file=temp/".$export_filename."\">Baixar dados</a></td>
</tr>
 <tr>
   <td>A tabela gerada contém <b>$nfields</b> colunas</td>
   <td><a href=\"download.php?file=temp/".$export_filename_metadados."\">Baixar definição colunas dados</a></td>
</tr>
<tr>
  <td colspan='100%'><hr></td>
</tr>
<tr>
  <td colspan='100%' class='tdformnotes'>*Os arquivos são separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td>
</tr>";
}
echo "
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);


?>