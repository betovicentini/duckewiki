<?php
//este script checa images importadas ao banco de dados mas que nao foram relacionadas com nada e permite criar uma relacao, buscando relacoes que tem a mesma data
//permite ligar com uma amostra coletada, com uma planta marcada ou com um habitat
//precisa modificar o script para fazer outros tipos de relacao que nao foram ainda implementados
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

if ($acclevel!='admin') {
$qq = "SELECT * FROM Imagens WHERE checkunlinkedimgs(ImageID)=0 AND AddedBy='".$uuid."' AND (TraitID IS NULL) LIMIT 0,1";
} else {
$qq = "SELECT * FROM Imagens WHERE checkunlinkedimgs(ImageID)=0 AND (TraitID IS NULL) LIMIT 0,1";
}
$res = mysql_query($qq,$conn);
$nres = mysql_numrows($res);
if ($nres) {
	$rw = mysql_fetch_assoc($res);
	$imgid = $rw['ImageID'];
	$filename = $row['FileName'];
	$imgdate = $row['DateOriginal'];
	echo $imgid."__".$filename."__".$imgdate;
} else {
	echo 0;
}
?>