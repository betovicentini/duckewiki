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

header("location: taxa-form.php");



//variaveis deste formulario
if (!empty($infraspid)) {
		$todelete = GetLangVar('nameinfraspecies'); 
} else {
	if (!empty($speciesid)) {
			$todelete = GetLangVar('namespecies');
		} else {
			if (!empty($genusid)) {
				$todelete = GetLangVar('namegenus');
			} else {	
				if (!empty($famid)) {
					$todelete = GetLangVar('namefamily');
				} else {
					header("location: taxa-form.php");
					exit();		
				}
			}
		}
} 

HTMLheaders('');

if ($todelete==GetLangVar('nameinfraspecies')) {
		$qq = "SELECT * FROM Identidade WHERE InfraEspecieID='$infraspid'";
		$query = @mysql_query($qq,$conn);
		$nq = @mysql_numrows($query);

		$qq = "SELECT * FROM Traits_variation WHERE InfraEspecieID='$infraspid'";
		$query = @mysql_query($qq,$conn);
		$nq = $nq+@mysql_numrows($query);		

		$qq = "SELECT * FROM Habitat WHERE Especiesids LIKE 'infraspecies|$infraspid'";
		$query = @mysql_query($qq,$conn);
		$nq = $nq+@mysql_numrows($query);		

		if ($nq>0) {
			echo "<br><table align='center' class='erro' cellpadding=\"5\">
				<tr><td>".GetLangVar('erro17')."</td></tr>
				</table>";
				$erro++;					
		} else {
			//$qq = "DELETE FROM Tax_InfraEspecies WHERE InfraEspecieID='$infraspid'";
			//$query = @mysql_query($qq,$conn);
			if (!$query) {
				$erro++;
			}
		}
}

//if ($todelete==GetLangVar('namespecies')) {
//		$qq = "SELECT * FROM Identidade USING(DetID) WHERE EspecieID='$speciesid'";
//		$query = @mysql_query($qq,$conn);
//		$nq = @mysql_numrows($query);
//		$qq = "SELECT * FROM Traits_variation WHERE EspecieID='$speciesid'";
//		$query = @mysql_query($qq,$conn);
//		$nq = $nq+@mysql_numrows($query);		
//		$qq = "SELECT * FROM Habitat WHERE Especiesids LIKE 'especie|$speciesid'";
//		$query = @mysql_query($qq,$conn);
//		$nq = $nq+@mysql_numrows($query);	
//		if ($nq>0) {
//			echo "<br><table align='center' class='erro' cellpadding=\"5\">
//				<tr><td>".GetLangVar('erro17')."</td></tr>
//				</table>";
//				$erro++;					
//		} else {
//			$qq = "DELETE FROM Tax_InfraEspecies WHERE EspecieID='$speciesid'";
//			$query = @mysql_query($qq,$conn);
//			$qq = "DELETE FROM Tax_Especies WHERE EspecieID='$speciesid'";
//			$query = @mysql_query($qq,$conn);
//			if (!$query) {
//				$erro++;
//			}
//		}
//}
//
//if ($todelete==GetLangVar('namegenus')) {
//		$qq = "SELECT * FROM Identidade WHERE GeneroID='$genusid'";
//		$query = @mysql_query($qq,$conn);		
//		$nq = @mysql_numrows($query);
//		$qq = "SELECT * FROM Traits_variation WHERE GeneroID='$genusid'";
//		$query = @mysql_query($qq,$conn);
//		$nq = $nq+@mysql_numrows($query);		
//		$qq = "SELECT * FROM Habitat WHERE Especiesids LIKE 'genero|$genusid'";
//		$query = @mysql_query($qq,$conn);
//		$nq = $nq+@mysql_numrows($query);	
//		if ($nq>0) {
//			echo "<br><table align='center' class='erro' cellpadding=\"5\">
//				<tr><td>".GetLangVar('erro17')."</td></tr>
//				</table>";
//				$erro++;					
//		} else {
//			$qq = "DELETE FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) WHERE GeneroID='$genusid'";
//			$query = @mysql_query($qq,$conn);
//			$qq = "DELETE FROM Tax_Especies WHERE GeneroID='$genusid'";
//			$query = @mysql_query($qq,$conn);
//			$qq = "DELETE FROM Tax_Generos WHERE GeneroID='$genusid'";
//			$query = @mysql_query($qq,$conn);
//			if (!$query) {
//				$erro++;
//			}
//		}
//}
//
//if ($todelete==GetLangVar('namefamily')) {
//		$qq = "SELECT * FROM Identidade USING(DetID) WHERE FamiliaID='$famid'";
//		$query = @mysql_query($qq,$conn);
//		$nq = @mysql_numrows($query);
//		$qq = "SELECT * FROM Traits_variation WHERE FamiliaID='$famid'";
//		$query = @mysql_query($qq,$conn);
//		$nq = $nq+@mysql_numrows($query);		
//		$qq = "SELECT * FROM Habitat WHERE Especiesids LIKE 'familia|$genusid'";
//		$query = @mysql_query($qq,$conn);
//		$nq = $nq+@mysql_numrows($query);	
//
//		if ($nq>0) {
//			echo "<br><table align='center' class='erro' cellpadding=\"5\">
//				<tr><td>".GetLangVar('erro17')."</td></tr>
//				</table>";
//				$erro++;					
//		} else {
//			$qq = "DELETE FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE FamiliaID='$famid'";
//			$query = @mysql_query($qq,$conn);
//			$qq = "DELETE FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE FamiliaID='$famid'";
//			$query = @mysql_query($qq,$conn);
//			$qq = "DELETE FROM Tax_Generos WHERE FamiliaID='$famid'";
//			$query = @mysql_query($qq,$conn);
//			$qq = "DELETE FROM Tax_Familias WHERE FamiliaID='$famid'";
//			$query = @mysql_query($qq,$conn);
//			if (!$query) {
//				$erro++;
//			}
//		}
//}
//
//if (empty($erro)) {
//		echo "<br><table align='center' class='success' cellpadding=\"5\">
//				<tr><td>".GetLangVar('sucesso2')."</td></tr>
//				</table>";
//}
HTMLtrailers();


?>