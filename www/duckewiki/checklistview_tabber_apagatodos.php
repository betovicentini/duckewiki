<?php
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
require_once($relativepathtoroot.$databaseconnection_clean);

$uuid = $_POST['uuid'];
$tbname =  $_POST['table'];
$sesid = substr(session_id(),0,10);

if ($uuid>0) {
$rr = mysql_query("DELETE FROM `".$tbname."UserLists` WHERE UserID=".$uuid);
} else {
$rr = mysql_query("DELETE FROM `".$tbname."UserLists` WHERE SessionID=".$sesid);
}
if ($rr) {
$txt =  "Os registros foram DESMARCADOS!";
}
echo $txt;
//echo $ttt;

?>