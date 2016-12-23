<?php 
session_start();
//Check whether the session variable
if(!isset($_SESSION['userid']) || 
	(trim($_SESSION['userid'])=='')) {
		header("location: access-denied.php");
	exit();
} 

include "../functions/databaseSettings.php";
require_once "../".$relativepathtoroot.$databaseconnection;



$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
	
	$idtag = strip_tags($_GET['idtag']);
	$idres = strip_tags($_GET['idres']);

?>
<?php
echo "
<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">
<head>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
</head>";

	$searchq		=	strip_tags($_GET['q']);
	$getRecord_sql	=	'SELECT * FROM Vernacular WHERE Vernacular LIKE "'.$searchq.'%"';
	//echo $getRecord_sql;
	$getRecord	=mysql_query($getRecord_sql,$conn);
	if(strlen($searchq)>0){
			echo '<ul>';
			while ($row = mysql_fetch_array($getRecord)) {
				echo "<li><a href=\"javascript:substitui('".$row['Vernacular']."','".$idtag."','".$idres."');\">".$row['Vernacular']."</a></li>";
			} 	
			echo '</ul>';
echo "</html>";
?>
<?php } ?>