<?php 
session_start();
include "../functions/databaseSettings.php";
require_once "../".$relativepathtoroot.$databaseconnection;

$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
	
	$idtag = strip_tags($_GET['idtag']);
	$idres = strip_tags($_GET['idres']);
	$nomeid = strip_tags($_GET['nomeid']);
	
?>
<?php

	$searchq		=	strip_tags($_GET['q']);
	$searchq		=	strtolower($searchq);
	$getRecord_sql	=	"SELECT * FROM TaxonomySimpleSearch WHERE LOWER(nome)  LIKE '%".$searchq."%'";
	$getRecord	= @mysql_query($getRecord_sql,$conn);
	$ngetRecord	= @mysql_numrows($getRecord);
	if($ngetRecord>0){
echo "<ul>
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
</ul>";
	}
?>