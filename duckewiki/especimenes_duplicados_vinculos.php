<?php
//Start session
//////////////ARQUIVO NAO TERMINADO////////////////
//////////////2013_11_04//////////////////////
/////////////TEMPORARIO///////////////////




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

//echopre($ppost);
//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Unifica variáveis de amostras duplicadas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echopre($gget);

//ESPECIMES APAGAR
if (!isset($iniciado)) {
	$todelspecs = explode("|",$todelspecids);
	$allspecs = explode("_",$theids);
	if (count($todelspecs)==count($allspecs)) {
		echo "Não pode apagar todos os registros<br />";
	} 
	else {
		$tokeepspecs = array_diff($allspecs,$todelspecs);
		//echopre($tokeepspecs);
		if (count($tokeepspecs)>1) {
			echo "Não pode manter mais de um registro, se está eliminando duplicados pode manter apenas 1<br />";
		} else {
			$iniciado=1;
			$idx = $uuid."_spdup";
		}
	}
} 
if ($iniciado==1) {
	//COMPARA OS TRAITS DA TABELA Traits_variation DAS AMOSTRAS A SEREM APAGADAS COM O DA AMOSTRA QUE FICA
	//FAZ PARA CADA AMOSTRA A SER APAGADA A COMPARAÇÃO
	//$_SESSION[$idx] = serialize(array('tokeepspecs' => $tokeepspecs, 'todelspecs' => $todelspecs));
	$idx = count($todelspecs)-1;
	if ($idx>=0) {
			$runspecid = $todelspecs[$idx];
			$qn = "SELECT * FROM Traits_variation WHERE EspecimenID='".$runspecid."'";
			$rn = mysql_query($qn,$conn);l
			$nrn = mysql_numrows($rn);
			if ($nrn==0) {
				unset($todelspecs[$idx]);
				$todelspecs = array_values($todelspecs);
			} else {
				$qn = "SELECT * FROM Traits_variation JOIN Traits USING(TraitID) WHERE EspecimenID='".$runspecid."' LIMIT 0,1";
				$rn = mysql_query($qn,$conn);l
				
				///pega os valores 


			}
	}



	foreach ($todelspecs as $idd) {
		$qn = "SELECT * FROM Traits_variation WHERE EspecimenID='".$idd."'";
		$rn = mysql_query($qn,$conn);l
		$nrn = mysql_numrows($rn);
		if ($nrn>0) {
			
			
			
		}
	
	
	
	}

}


//PEGA TODOS OS ESPECIMENES DA SERIE
//$getids = explode("_",$theids);
//echopre($getids);

//echo "<input type='button'  onclick=\"javascript:getvaluefrommain('todeletespecs', 'testeid');\"  value='testando'  />";



$which_java = array(
"<script type='text/javascript' src='javascript/myjavascript_teste.js'></script>"
//"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>
