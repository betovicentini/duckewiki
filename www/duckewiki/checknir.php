<?php
//Start session
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//variaveis
//$inpanum = string
//$all = [0,1]

if ($all==1) {
	$qq = "SELECT DISTINCT INPA_ID FROM NirSpectra AS nir LEFT JOIN Especimenes as specs ON specs.EspecimenID=nir.EspecimenID AND (specs.INPA_ID IS NOT NULL)";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0) {
		$fh = fopen("temp/nirlist_".$uii.".csv", 'w') or die("nao foi possivel gerar o arquivo");
		$count = mysql_num_fields($res);
		while($rsw = mysql_fetch_row($res)){
			$line = '';
			$nff  = count($rsw);
			$nii = 1;
			foreach($rsw as $value){
				if(!isset($value) || $value == ""){
					if ($nii<$nff) {
						$value = "  \t";
					} else {
						$value = '  ';
					}
				} else {
					//important to escape any quotes to preserve them in the data.
					$value = str_replace('"', '""', $value);
					//needed to encapsulate data in quotes because some data might be multi line.
					//the good news is that numbers remain numbers in Excel even though quoted.
					if ($nii<$nff) {
						$value = $value."\t";
					} else {
						//$value = '"' . $value . '"';
					}
				}
				$nii++;
				$line .= $value;
			}
			$lin = trim($line)."\n";
			fwrite($fh, $lin);
		}
		fclose($fh);
	} 
	header("location: temp/nirlist_".$uii.".csv");
}
if (!empty($inpanum)) {
	$qq = "SELECT INPA_ID FROM NirSpectra AS nir LEFT JOIN Especimenes as specs ON specs.EspecimenID=nir.EspecimenID WHERE specs.INPA_ID='".$inpanum."'";
	//echo $qq."<br />";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
	$which_java = array();
	$title = '';
	$body = '';
	//FazHeader($title,$body,$which_css,$which_java,$menu);
	if ($nres>0) {
		echo "teste";
	} else {
		echo 0;
	}
	$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
	//FazFooter($which_java,$calendar=FALSE,$footer=$menu);
}

?>