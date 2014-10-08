<?php
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


$qqn = "SELECT * FROM `temp_ppp_plantas`";
$rqn = mysql_query($qqn,$conn);
$rzn = mysql_fetch_assoc($rqn);
$progresso = $rzn['percentage'];
echo $progresso;
session_write_close();
?>
