<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

//Este script importa o arquivo CSV ou TXT selecionado para uma tabela temporaria mysql
//Depois sao perguntados quais colunas indicam amostras coletadas ou plantas marcadas
//Ultima atualizacao: 25 jun 2011 - AV
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

//CABECALHO
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Importar Pessoas passo 02';
$body = '';


###define as colunas parentid ou municipioid, dependendo do dado
$vars = unserialize($_SESSION['destvararray']);
@extract($vars);


//CHECA PESSOAS JA CADASTRADAS
$cln = $tbprefix."PessoaID";
$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln." CHAR(40) DEFAULT NULL";
@mysql_query($qq,$conn);

$qq = "SELECT ImportID,`".$abreviacao."`,`".$prenome."`,`".$sobrenome."` FROM `".$tbname."`";
$rq = @mysql_query($qq,$conn);
$rqn0 = @mysql_numrows($rq);
$n=0;
while($row = mysql_fetch_assoc($rq)) {
	$qq = "UPDATE `".$tbname."` SET `".$cln."`=checarpessoaimport(`".$abreviacao."`,`".$prenome."`,`".$sobrenome."`) WHERE ImportID=".$row['ImportID'];
	@mysql_query($qq,$conn);
	$perc = ($n/$rqn0)*99;
	$qnu = "UPDATE `temp_".substr(session_id(),0,10)."` SET percentage=".$perc; 
	mysql_query($qnu);
	session_write_close();
	$n=$n+1;
}
$qnu = "UPDATE `temp_".substr(session_id(),0,10)."` SET percentage=100"; 
mysql_query($qnu);
$message=  "100% CONCLUÍDO";
echo $message;
session_write_close();

?>
