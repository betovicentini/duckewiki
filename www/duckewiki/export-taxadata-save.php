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


$restb = explode(";",$resultado);
$nrecs = $restb[0];
$nfields = $restb[1];
//CABECALHO
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Export dados relacionados à nomes taxonômicos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$export_filename = "taxa_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
$export_filename_metadados = "taxa_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_definicoesDAScolunas.csv";

echo "
<br />
<table class='myformtable' cellpadding='5' align='left' width=70%>
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
</tr>";
echo "
<tr>
  <td colspan='2'><hr></td>
</tr>
<tr>
  <td colspan='2' class='tdformnotes'>*Os arquivos são separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td>
</tr>";
}
echo "
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);


?>