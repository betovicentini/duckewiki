<?php
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
include "functions/ImportData.php";
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


$family = ucfirst(strtolower(trim($_POST['family']))); 
$genus = ucfirst(strtolower(trim($_POST['genus']))); 
$proxy = $_POST['proxy'];

if (empty($family) && empty($genus)) {
	header("location: novoIPNItaxa-form.php");
	exit();
}

$body='';

HTMLheaders($body);

//echo $proxy;
//echo $family."<br".$genus;
//checar se dados ja nao foram importados antes
//if (empty($genus)) {
//	$query = "SELECT * FROM Tax_Familias WHERE Familia='$family'";
//	$result=mysql_query($query,$conn);
//	$nres = mysql_numrows($result);
//} else {
//	$query = "SELECT * FROM Tax_Generos WHERE Genero='$genus'";
//	$result=mysql_query($query,$conn);
//	$nres = mysql_numrows($result);
//}

$proxy = FALSE;

if ($nres>0) {
	echo "<p class='erro'>".GetLangVar('erro4')."</p>";	
} else {
	if (empty($genus)) {
		$ipnurl = "http://www.ipni.org/ipni/advPlantNameSearch.do?find_family=$family&find_genus=&find_species=&find_infrafamily=&find_infragenus=&find_infraspecies=&find_authorAbbrev=&find_includePublicationAuthors=on&find_includePublicationAuthors=off&find_includeBasionymAuthors=on&find_includeBasionymAuthors=off&find_publicationTitle=&find_isAPNIRecord=on&find_isAPNIRecord=false&find_isGCIRecord=on&find_isGCIRecord=false&find_isIKRecord=on&find_isIKRecord=false&find_rankToReturn=all&output_format=delimited-extended&find_sortByFamily=on&find_sortByFamily=off&query_type=by_query&back_page=plantsearch";
		$filename = $family.".txt";
	} else {
		$ipnurl = "http://www.ipni.org/ipni/advPlantNameSearch.do?find_family=&find_genus=$genus&find_species=&find_infrafamily=&find_infragenus=&find_infraspecies=&find_authorAbbrev=&find_includePublicationAuthors=on&find_includePublicationAuthors=off&find_includeBasionymAuthors=on&find_includeBasionymAuthors=off&find_publicationTitle=&find_isAPNIRecord=on&find_isAPNIRecord=false&find_isGCIRecord=on&find_isGCIRecord=false&find_isIKRecord=on&find_isIKRecord=false&find_rankToReturn=all&output_format=delimited-extended&find_sortByFamily=on&find_sortByFamily=off&query_type=by_query&back_page=plantsearch";
		$filename = $genus.".txt";	
	}
	//echo $ipnurl;
	$res = curl_get_file_contents($ipnurl,$proxy);
	if ($res) {
		$relativepath = 'uploads/';
		$texttowrite = $res;
		WriteToTXTFile($filename,$texttowrite,$relativepath);
		echo "<form name='myform' action=novoIPNItaxa-store.php method='post'>
				<input type = 'hidden' name='family' value='$family'>
				<input type = 'hidden' name='genus' value='$genus'>
				<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
			</form>";	
	} else {
		 echo "
		<table align='center' class='erro' cellpadding='4'>
		<tr><td>".GetLangVar('erro5')."</td></tr>
	 <form action=novoIPNItaxa-form.php method='post'>
		<tr><td align='center'><input type = 'submit' class='bsubmit' value='".GetLangVar('namevoltar')."'></td></tr>
	 </form>
		</table>";
	}
} //endif else $nres>0

HTMLtrailers();

?>