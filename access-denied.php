<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$menu = FALSE;

$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Acesso Negado';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<table cellpadding='10' align='center' style='font-size:2em; background:yellow; border:  solid 1px black' width='50%'>
<tr><td>Acesso negado. Tente novamente!</td></tr>
</table>";

$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>