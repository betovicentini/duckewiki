<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link href='css/jquery-ui.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$body='';
$title = 'Script Teste Executa';
FazHeader($title,$body,$which_css,$which_java,$menu);

$fhh =  "dadosMorfologicos_9.csv";

fclose($fhh);


//$qq = "SELECT *  FROM `Imagens` WHERE `AddedDate` = '2014-07-02' AND `TraitID` = 351";
//$res = mysql_query($qq,$conn);
//while ($row = mysql_fetch_assoc($res)) {
//	$trid = 351;
//	$imgid = $row['ImageID'];
//	$qq = "SELECT * FROM Traits_variation WHERE `AddedDate`='2014-07-02' AND TraitID=351";
//	$rr = mysql_query($qq,$conn);
//	$encontrei=0;
//	while ($rw = mysql_fetch_assoc($rr)) {
//		$vv = $rw['TraitVariation'];
//		$rn = explode(";",$vv);
//		//echopre($rn);
//		if (in_array($imgid,$rn)) {
//			$qq = "UPDATE Traits_variation SET TraitID=350 WHERE TraitVariationID='".$rw['TraitVariationID']."'";
//			$r1 = mysql_query($qq,$conn);			
//			$qq = "UPDATE Imagens SET TraitID=350 WHERE ImageID='".$imgid."'";
//			$r2 = mysql_query($qq,$conn);
//			if ($r1 && $r2) {
//				$atualizado='ok';
//			} else {
//				$atualizado ='FALHOU';
//			}
//			$encontrei++;
//		}		
//	}
//	echo 'Encontrei '.$encontrei."  E ".$atualizado."<br>";
//	session_write_close();
//}
//
//
//
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>