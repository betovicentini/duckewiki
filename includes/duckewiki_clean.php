<?php
///////ALTERAR AQUI PARA SUA CONFIGURACAO/////////////////////
$host = 'localhost';
$user = 'root';
$pws = 'av123';
$dbname = 'duckewiki';
/////////////////////////////////////////////////////////////////////////////////////////////
$res = mysql_connect($host,$user,$pws);
mysql_select_db($dbname);
mysql_set_charset('utf8',$res);
?>