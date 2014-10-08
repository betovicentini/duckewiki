<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

//Start session
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

$uuid = cleanQuery($_SESSION['userid'],$conn);

$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' >");
$which_java = array();
$title ='Log Importação Imagens';
$body='';
FazHeader($title,$body,$which_css,$which_java,$menu);

$qu = "SELECT * FROM temp_imgimport_".$uuid;
if ($linkposterior!=1) {
	$qu .= " WHERE (EspecimenID=0 OR EspecimenID IS NULL) AND (PlantaID=0 OR PlantaID IS NULL)";
	$txt = 'Não foi encontra a referência!';
} else {
	$txt = 'Houve um erro!';
}
echo "As seguintes imagens não foram importadas porque <b>".$txt."</b>:<br >";
$rn = mysql_query($qu, $conn);
while ($kw = mysql_fetch_assoc($rn)) {
	echo $kw['FileName']."<br />";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>