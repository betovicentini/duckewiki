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
	$nomeid = strip_tags($_GET['nomeid']);
	
?>
<?php
$searchq = strip_tags($_GET['q']);
$searchq = mb_strtolower($searchq);
$getRecord_sql = "SELECT * FROM TaxonomySimple WHERE LOWER(nome)  LIKE '%".$searchq."%'";
//$getRecord_sql	=	"(SELECT Familia as nome FROM Tax_Familias WHERE Familia LIKE '".$searchq."%') UNION (SELECT CONCAT(Genero,' [',Familia,']') as nome FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE Genero LIKE '".$searchq."%') UNION (SELECT CONCAT(Genero,' ',Especie,' [',Familia,']') as nome FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE CONCAT(Genero,' ',Especie) LIKE '".$searchq."%') UNION (SELECT CONCAT(Genero,' ',Especie,' ',InfraEspecie,' [',Familia,']') as nome FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID)  JOIN Tax_Familias USING(FamiliaID) WHERE CONCAT(Genero,' ',Especie,' ',InfraEspecie) LIKE '".$searchq."%') ORDER BY nome";
$getRecord = @mysql_query($getRecord_sql,$conn);
$ngetRecord = @mysql_numrows($getRecord);
if($ngetRecord>0){
echo "
<ul>
<li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">-----------------</a></li>";
			while ($row = mysql_fetch_array($getRecord)) {
				if ($row['nome']!=$row['Familia']) {
					$tgn = $row['nome']." [".$row['Familia']."]";
				} else {
					$tgn = strtoupper($row['nome']);
				}
				echo "<li><a href=\"javascript:substitui('".($row['nome'])."','".$idtag."','".$idres."', '".$row['nomeid']."', '".$nomeid."');\">".$tgn."</a></li>";
			} 
			echo '</ul>';
	} elseif (strlen($searchq)>0) {
echo "
<ul>
<li><a href=\"javascript:substitui('','".$idtag."','".$idres."', '0', '".$nomeid."');\">".GetLangVar('naoencontrado')."</a></li>
</ul>
";
	}
?>