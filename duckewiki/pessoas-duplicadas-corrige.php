<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include "pessoas-duplicadas-funcao.php";


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
//$ppost = cleangetpost($_POST,$conn);
//extract($ppost);
//$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
extract($gget);
$txt = '';
foreach($gget as $kk => $vv) {
	$kz = explode("_",$kk);
	if ($kz[0]=='pessoa') {
		$id1 = $kz[1]+0;
		$id2 = $vv+0;
		if ($id1>0 && $id2>0 && $id1!=$id2) {
				$txt .= "\nkey:".$kk."  value:".$vv;
				$fez = mergepessoa($id1,$id2, $conn);
				if ($fez==1) {
					$txt = "     corrigiu!";
				} else {
					$txt .= "   ERRO!";
				}
		}
	}
}
echo $txt;
session_write_close();
?>