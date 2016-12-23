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

	$searchq		=	strip_tags($_GET['q']);
	$getRecord_sql	=	"(SELECT Familia as nome FROM Tax_Familias WHERE Familia LIKE '".$searchq."%') UNION (SELECT Genero as nome FROM Tax_Generos WHERE Genero LIKE '".$searchq."%') UNION (SELECT CONCAT(Genero,' ',Especie) as nome FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE CONCAT(Genero,' ',Especie) LIKE '".$searchq."%') UNION (SELECT CONCAT(Genero,' ',Especie,' ',InfraEspecie) as nome FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE CONCAT(Genero,' ',Especie,' ',InfraEspecie) LIKE '".$searchq."%') ORDER BY nome";
	$getRecord	=mysql_query($getRecord_sql,$conn);
	if(strlen($searchq)>0){
			echo '<ul>';
			while ($row = mysql_fetch_array($getRecord)) {
				echo "<li><a href=\"javascript:substitui('".$row['nome']."','".$idtag."','".$idres."');\">".$row['nome']."</a></li>";
			} 	
			echo '</ul>';
?>
<?php } ?>