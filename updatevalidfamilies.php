<?php

session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
include_once("functions/class.Numerical.php") ;

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

HTMLheaders($body);

echo "<table class='sortable autostripe' align='center' width='99%' cellspacing=0>
				<thead>
					<tr>
						<th align='center'>Nome</th>					
						<th align='center'>Replaceby</th>					
						<th align='center'>Notas</th>					
					</tr>
				</thead>
				<tbody>";
				
				$qq = "SELECT *  FROM tax_familias WHERE Valid=0";
				$res = mysql_query($qq,$conn);
				
				//para cada familia invalida
				while ($row = mysql_fetch_assoc($res)) {
					$famid = $row['FamiliaID'];
					$familia = $row['Familia'];
					$qq = "SELECT *  FROM Tax_Familias WHERE Sinonimos LIKE '%familia|".$famid.";%' OR `Sinonimos` LIKE '%familia|".$famid."'";
					$rr = mysql_query($qq,$conn);
					$nrr = mysql_numrows($rr);
					$rw = mysql_fetch_assoc($rr);
					$validid = $rw['FamiliaID'];
					$validfam = $rw['Familia'];					
					if ($nrr==0) {
						$qq = "UPDATE Tax_Familias SET Valid=1 WHERE FamiliaID='$famid'";
						$vali = mysql_query($qq,$conn);
						if ($vali) {$obs = 'validou';} else {$obs = 'errou';}
					} else {$obs='';}
	
					$qq = "SELECT *  FROM Tax_Generos WHERE FamiliaID='$famid'";
					$rrr = mysql_query($qq,$conn);
					$nw = mysql_numrows($rrr);
					if ($nw>0) {
						$qq = "UPDATE Tax_Generos SET FamiliaID='$validid' WHERE FamiliaID='$famid'";
						$vali = mysql_query($qq,$conn);
						if ($vali) {$obs = 'corrigiu';} else {$obs = 'nao corrigiu';}
					}
					echo "<tr><td>".$familia."</td><td>".$validfam."</td><td>$obs</td></tr>";

}

echo "</tbody></table>";
HTMLtrailers();

?>

