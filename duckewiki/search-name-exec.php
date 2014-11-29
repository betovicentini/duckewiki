<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

HTMLheaders('');


$nomes = explode(" ",$nomesearch);

//genero ou familia

if (count($nomes)==1) {
	$nn = trim($nomes[0]);
	$qq = "SELECT * FROM Tax_Familias WHERE Familia='".$nn."'";
	//echo $qq;
	$res = mysql_query($qq,$conn);
	$nr = mysql_numrows($res);
	if ($nr>0) {
		$what = "Familia";
		echo "<form name='myform' action=search-name-fam.php method='post'>
				<input type = 'hidden' name='nn' value='$nn'>
				<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
			</form>";	
	} else {
		$what = "Genero";
		echo "<form name='myform' action=search-name-gen.php method='post'>
				<input type = 'hidden' name='nn' value='$nn'>
				<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
			</form>";	
	}
}
//especie
if (count($nomes)==2) {
	$gen = $nomes[0];
	$nn = $nomes[1];
	$what =  "Especie";
	echo "<form name='myform' action=search-name-sp.php method='post'>
				<input type = 'hidden' name='nn' value='$nn'>
				<input type = 'hidden' name='gen' value='$gen'>

				<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
			</form>";	
}
//infraespecie
if (count($nomes)==3) {
	$what =  "Infraespecie";
	$gen = $nomes[0];
	$sp = $nomes[1];
	$nn = $nomes[2];
	echo "<form name='myform' action=search-name-infsp.php method='post'>
				<input type = 'hidden' name='nn' value='$nn'>
				<input type = 'hidden' name='gen' value='$gen'>
				<input type = 'hidden' name='sp' value='$sp'>
				<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
			</form>";	

}

HTMLtrailers();

?>