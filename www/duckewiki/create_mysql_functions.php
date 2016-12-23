<?php
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
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$body = '';
$title = 'Create MysqlFunctions';
FazHeader($title,$body,$which_css,$which_java,$menu);

$uuuuserid = $_SESSION['userid'];
$tbn = "tempipni_".$uuuuserid;

$myDirectory = opendir("functions/mysql_functions");
$dirArray = array();
while($entryName = readdir($myDirectory)) {
	$enn = trim(str_replace(".","",$entryName));
	if (!empty($enn)) {
		$funcname = str_replace(".sql","",$entryName);
		if ($funcname=='prepgaz') {
			$type = 'PROCEDURE';
		} else {
			$type = 'FUNCTION';
		}
		//echo $funcname."<br />";
		$qq = "DROP ".$type." IF EXISTS ".$funcname;
		//echo $enn."</br>".$qq;
		mysql_query($qq,$conn) or die( mysql_error() );
		$qq = file_get_contents("functions/mysql_functions/".$entryName);
		$rr = mysql_query($qq,$conn) or die( mysql_error() );
		if ($rr) {
			//echo " -  created again !<br />";
			$dirArray[] = $entryName;
		}
	}
}

$nz = count($dirArray);
echo "<p class='success' style='width: 200px;'>$nz funções criadas</p><br />";
		
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>

