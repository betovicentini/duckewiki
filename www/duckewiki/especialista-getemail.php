<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

$rr = "SELECT Email FROM Pessoas WHERE PessoaID='".$_POST['pessoaid']."'";
$rs = mysql_query($rr,$conn);
$rw = mysql_fetch_assoc($rs);
echo $rw['Email'];
?>