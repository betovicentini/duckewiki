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
if(!isset($uuid) ||  (trim($uuid)=='')) {
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
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Prepara imagens de processos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
//PEGA LISTA DE HERBÁRIA
$qn = "SELECT GROUP_CONCAT(spp.Herbaria SEPARATOR ';')  as nn FROM Especimenes AS spp JOIN ProcessosLIST as prcc ON prcc.EspecimenID=spp.EspecimenID AND prcc.EXISTE=1 AND isvalidprocesso(prcc.ProcessoID,'".$processoid."')>0";
$rn = mysql_query($qn,$conn);
$rw = mysql_fetch_assoc($rn);
$herb= str_replace(",",";",$rw['nn']);
$rwa = explode(";",$herb);
$herb= array_unique($rwa);
asort($herb);
$herb= array_values($herb);
if (empty($message)) {
	$message = "Amostras de plantas da <b>Flora do PDBFF</b> (Projeto Dinâmica Biológica de Fragmentos Florestais) do Instituto Nacional de Pesquisas da Amazônia (INPA), Manaus, Brasil. Maioria é voucher de árvores/arvoretas/lianas em parcelas permanentes de Floresta de Terra Firme.<br /><br />Samples for the <b>Flora of BDFFP</b> (Biological Dynamics of Forest Fragments Project) from the National Institute for Amazonia Research (INPA), Manaus, Brazil. Most are trees/treelets/lianas from permanent plots.<br /><br /><b>Agradecemos o envio de identificações para <u>plantasPDBFF@gmail.com</u>!<br /><br />We appreciate if new determinations are sent to <u>plantasPDBFF@gmail.com</u>!</b>";
} 
$_SESSION['printmessage'] = $message;
echo "
<span style=\"color:#4E889C; font-size: 0.9em; font-weight:bold; padding: 4px;\">Escreva uma mensagem para aparecer no topo de cada lista</span>
<form action='processo-exportvarios-herbaria.php' method='post'>
<input type='hidden' value='".$processoid."' name='processoid' >
<textarea cols=60 rows=6 name='message'>".$message."</textarea>
<br />
<input type=\"submit\" style=\"cursor:pointer;\" value='Save' >
<form>
";
echo "
<br />
<br />
<span style=\"color:#4E889C; font-size: 0.9em; font-weight:bold; padding: 4px;\">Herbários nos processos escolhidos:</span>
<table cellpadding='4px'>";
foreach($herb as $hh) {
echo "
<tr><td>".$hh."</td><td>
<input type=\"button\" style=\"cursor:pointer;\" value='Imprime Lista' onclick=\"javascript:small_window('processo-exportvarios-herbaria-printlista.php?processoid=".$processoid."&herbario=".$hh."');\" onmouseover=\"Tip('Imprimir a lista para o pacote do herbário');\" ></td></tr>";
}
echo "
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>