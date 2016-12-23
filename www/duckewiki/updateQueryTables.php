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


//CABECALH
$ispopup=1;
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'Updata Query Table';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
//echopre($ppost);

//DESABILITA CHAVES EXTERNAS
//$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
//mysql_query($forkeyoff,$conn);
//$qupd = "UPDATE Gazetteer SET PathName=upgazpath(GazetteerID)";
//mysql_query($qupd,$conn);
//HABILITA CHAVE INTERNA
//$forkeyoff = "SET FOREIGN_KEY_CHECKS=1";
//mysql_query($forkeyoff,$conn);

if (!isset($enviado)) {
echo "
<form action='updateQueryTables.php' method='post' >
<input type='hidden'  name=\"enviado\" value=1 >

<input type='checkbox'  name=\"taxonomyall\" value=1>Taxonomy All
<br>
<input type='checkbox'  name=\"taxonomysearch\" value=1 >Taxonomy Search
<br>
<input type='checkbox'  name=\"localityall\" value=1 >Locality All
<br>
<input type='checkbox'  name=\"localitysearch\" value=1 >Locality Search
<br>
<input type='submit'  value='Enviar'>
</form>
";
} else {
	if ($taxonomyall==1) {
		TaxonomySimple($all=true,$conn);
	}
	if ($taxonomysearch==1) {
		TaxonomySimple($all=false,$conn);
	}
	if ($localityall==1) {
		LocalitySimple($gspoints=false,$all=TRUE,$conn);
	}
	if ($localitysearch==1) {
		LocalitySimple($gspoints=false,$all=FALSE,$conn);
	}
	echo "
<form action='updateQueryTables.php' method='post' >
<input type='submit'  value='Outra tabela'>
</form>
";
}


//echo "Concluido";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>