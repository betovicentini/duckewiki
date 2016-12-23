<?php
//este script finaliza a importacao
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
//FAZ A CONEXAO COM O BANCO DE DADOS
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
//////PEGA E LIMPA VARIAVEIS
//$ppost = cleangetpost($_POST,$conn);
//@extract($ppost);
//$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

$txt = "testando";

$vars = json_decode($_GET['asvar'],true);
$pgfilename = $vars['pgfilename'];
$fh = fopen("temp/".$pgfilename, 'w');
fwrite($fh, '0');
fclose($fh);
session_write_close();
flush();

//echopre($vars);
for($i=0;$i<100;$i++) {
	$perc = round(($i/100)*100,0);
	if ($perc<=100) {
		$fh = fopen("temp/".$pgfilename, 'w');
		if ($fh) {		$txt .= $perc."  ".$i."<br >"; }
		fwrite($fh, $perc);
		fclose($fh);
		session_write_close();
		flush();
	}
	sleep(1);
}
sleep(1);
$fh = fopen("temp/".$pgfilename, 'w');
fwrite($fh, '100');
fclose($fh);
session_write_close();
flush();
//echo $txt;
?>
