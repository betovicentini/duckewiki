<?php
//Start session
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
//include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

$qqn = "SELECT * FROM `temp_filtro_".substr(session_id(),0,5)."`";
$rqn = mysql_query($qqn,$conn);
$rzn = mysql_fetch_assoc($rqn);
$progresso = $rzn['percentage'];
echo $progresso;
session_write_close();
?>
