<?php 
session_start();
//Check whether the session variable
if(!isset($_SESSION['userid']) || 
	(trim($_SESSION['userid'])=='')) {
		header("location: access-denied.php");
	exit();
} 

require_once "../../../includes/floradbconn.php";

$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
	
	$idtag = strip_tags($_GET['idtag']);
	$idres = strip_tags($_GET['idres']);

?>
<?php

	$searchq		=	strip_tags($_GET['q']);
	$getRecord_sql	=	"(SELECT CONCAT(Genero,' ',Especie) as nome,CONCAT('species_',EspecieID) as spid FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE CONCAT(Genero,' ',Especie) LIKE '".$searchq."%') UNION (SELECT CONCAT(Genero,' ',Especie,' ',InfraEspecieNivel,' ',InfraEspecie) as nome,CONCAT('subspecies_',EspecieID) as spid FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE CONCAT(Genero,' ',Especie,' ',InfraEspecie) LIKE '".$searchq."%') ORDER BY nome";
	$getRecord	=mysql_query($getRecord_sql,$conn);
	if(strlen($searchq)>0){
			echo '<ul>';
			while ($row = mysql_fetch_array($getRecord)) {
				echo "<li><a href=\"javascript:substitui('".$row['nome']."','".$idtag."','".$idres."');substituiid('".$row['spid']."','nomeid');\">".$row['nome']."</a></li>";
			} 	
			echo '</ul>';
?>
<?php } ?>