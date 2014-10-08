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

if ($toedit==GetLangVar('nameinfraspecies')) {
		$fieldsaskeyofvaluearray = array(
			'EspecieID' => $speciesid,
			'InfraEspecie' => $spnome,
			'InfraEspecieAutor' => $autor,
			'InfraEspecieNivel' => $subvar,
			'Basionym' => $basionym,
			'BasionymAutor' => $basionymautor,
			'PubRevista' => $pubrevista,
			'PubVolume' => $pubvolume,
			'PubAno' => $pubano,
			'Sinonimos' => $sinonimos,
			'GeoDistribution' => $geodist,
			'Notas' => $notas,
			'Valid' => $nomevalido
			);	
		//get old values	
		$qq = "SELECT EspecieID,InfraEspecie,InfraEspecieAutor,InfraEspecieNivel,Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,GeoDistribution,Notas
				FROM Tax_InfraEspecies WHERE InfraEspecieID='$infraspid'";
		$qu = mysql_query($qq,$conn);
		$old = mysql_fetch_assoc($qu);
		if ($old['Valid']!=$nomevalido && $nomevalido==0) {
			//check to see if any specimem has this value if so, then do not allow invalidation
			$qq = "SELECT * FROM Identidade WHERE InfraEspecieID='$infraspid'";
			$det = mysql_query($qq,$conn);
			$hasdet = mysql_numrows($det);
		}
		if (!$hasdet>0) {
			if ($old==$fieldsaskeyofvaluearray) {
					$verupdate = updatevernacular($vernacularvalue,'infraespecie',$infraspid,$conn);	
			} else {							
				CreateorUpdateTableofChanges($infraspid,'InfraEspecieID','Tax_InfraEspecies',$conn);
				$newtrait = UpdateTable($infraspid,$fieldsaskeyofvaluearray,'InfraEspecieID','Tax_InfraEspecies',$conn);
				if (!$newtrait) {
					$erro++;
				} else {
					$verupdate = updatevernacular($vernacularvalue,'infraespecie',$infraspid,$conn);	
				}		
			}
		} else {
			$invalidationfailed++;
			$erro++;
		}
}
if ($toedit==GetLangVar('namespecies')) {
		$fieldsaskeyofvaluearray = array(
			'GeneroID' => $genusid,
			'Especie' => $spnome,
			'EspecieAutor' => $autor,
			'Basionym' => $basionym,
			'BasionymAutor' => $basionymautor,
			'PubRevista' => $pubrevista,
			'PubVolume' => $pubvolume,
			'PubAno' => $pubano,
			'Sinonimos' => $sinonimos,
			'GeoDistribution' => $geodist,
			'Notas' => $notas,
			'Valid' => $nomevalido
			);
		//get old values	
		$qq = "SELECT GeneroID,Especie,EspecieAutor,Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,GeoDistribution,Notas
				FROM Tax_Especies WHERE EspecieID='$speciesid'";
		$qu = mysql_query($qq,$conn);
		$old = mysql_fetch_assoc($qu);
		if ($old['Valid']!=$nomevalido && $nomevalido==0) {
			//check to see if any specimem has this value if so, then do not allow invalidation
			$qq = "SELECT * FROM Identidade WHERE EspecieID='$speciesid'";
			$det = mysql_query($qq,$conn);
			$hasdet = mysql_numrows($det);
		}
		if (!$hasdet>0) {
			if ($old==$fieldsaskeyofvaluearray) {
					$verupdate = updatevernacular($vernacularvalue,'especie',$speciesid,$conn);	
			} else {
				//echo "they are different";
				CreateorUpdateTableofChanges($speciesid,'EspecieID','Tax_Especies',$conn);
				$newtrait = UpdateTable($speciesid,$fieldsaskeyofvaluearray,'EspecieID','Tax_Especies',$conn);
				if (!$newtrait) {
					$erro++;
				} else {
					$verupdate = updatevernacular($vernacularvalue,'especie',$speciesid,$conn);	
				}
			} 
		} else {
			$invalidationfailed++;
			$erro++;
		}
	
}
if ($toedit==GetLangVar('namegenus')) {
		$fieldsaskeyofvaluearray = array(
			'FamiliaID' => $famid,
			'Genero' => $spnome,
			'GeneroAutor' => $autor,
			'Basionym' => $basionym,
			'BasionymAutor' => $basionymautor,
			'PubRevista' => $pubrevista,
			'PubVolume' => $pubvolume,
			'PubAno' => $pubano,
			'Sinonimos' => $sinonimos,
			'Notas' => $notas,
			'Valid' => $nomevalido
			);
			
		//get old values	
		$qq = "SELECT FamiliaID,Genero,GeneroAutor,Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,Notas
				FROM Tax_Generos WHERE GeneroID='$genusid'";
		$qu = mysql_query($qq,$conn);
		$old = mysql_fetch_assoc($qu);
		if ($old['Valid']!=$nomevalido && $nomevalido==0) {
			//check to see if any specimem has this value if so, then do not allow invalidation
			$qq = "SELECT * FROM Identidade WHERE GeneroID='$genusid'";
			$det = mysql_query($qq,$conn);
			$hasdet = mysql_numrows($det);
		}
		if (!$hasdet>0) {
			if ($old==$fieldsaskeyofvaluearray) {
					$verupdate = updatevernacular($vernacularvalue,'genero',$genusid,$conn);	
			} else {
				//echo "they are different";
				CreateorUpdateTableofChanges($genusid,'GeneroID','Tax_Generos',$conn);
				$newtrait = UpdateTable($genusid,$fieldsaskeyofvaluearray,'GeneroID','Tax_Generos',$conn);
				if (!$newtrait) {
					$erro++;
				} else {
					$verupdate = updatevernacular($vernacularvalue,'genero',$genusid,$conn);	
				}			
			}
		} else {
			$invalidationfailed++;
			$erro++;
		}
}
if ($toedit==GetLangVar('namefamily')) {
		$fieldsaskeyofvaluearray = array(
			'Familia' => $spnome,
			'FamiliaAutor' => $autor,
			'Sinonimos' => $sinonimos,
			'Notas' => $notas,
			'Valid' => $nomevalido
			);
		
		//get old values	
		$qq = "SELECT Familia,FamiliaAutor,Sinonimos,Notas
				FROM Tax_Familias WHERE FamiliaID='$famid'";
		$qu = mysql_query($qq,$conn);
		$old = mysql_fetch_assoc($qu);
		if ($old['Valid']!=$nomevalido && $nomevalido==0) {
			//check to see if any specimem has this value if so, then do not allow invalidation
			$qq = "SELECT * FROM Identidade WHERE FamiliaID='$famid'";
			$det = mysql_query($qq,$conn);
			$hasdet = mysql_numrows($det);
		}
		if (!$hasdet>0) {
			if ($old==$fieldsaskeyofvaluearray) {
					$verupdate = updatevernacular($vernacularvalue,'familia',$famid,$conn);	
			} else {
				//echo "they are different";
				CreateorUpdateTableofChanges($famid,'FamiliaID','Tax_Familias',$conn);
				$newtrait = UpdateTable($famid,$fieldsaskeyofvaluearray,'FamiliaID','Tax_Familias',$conn);
				if (!$newtrait) {
					$erro++;
				} else {
					$verupdate = updatevernacular($vernacularvalue,'familia',$famid,$conn);	
				}				
			}
		} else {
			$invalidationfailed++;
			$erro++;
		}
}

HTMLheaders('');

if ($invalidationfailed>0) {
	echo "<br><table align='center' class='erro' cellpadding=\"5\">
				<tr><td>".GetLangVar('messageinvalidationfailed')."</td></tr>
				</table>
			<br>";
} else {
	if (empty($erro) || $vernupdate) {
		echo "<br><table align='center' class='success' cellpadding=\"5\">
					<tr><td>".GetLangVar('sucesso1')."</td></tr>
					<tr><td>
					<form action=taxa-form.php method='post'>
						<input type = 'hidden' name='famid' value='$famid'>
						<input type = 'hidden' name='genusid' value='$genusid'>
						<input type = 'hidden' name='speciesid' value='$speciesid'>
						<input type = 'hidden' name='infraspid' value='$infraspid'>
					
						<input type='submit' class='bsubmit' value='".GetLangVar('namevoltar')."'>
					</form>
					</td></tr>
					</table>
				<br>";
				TaxonomySimple($all=true,$conn);
				TaxonomySimple($all=false,$conn);
	} else {
		echo "<br><table align='center' class='erro' cellpadding=\"5\">
					<tr><td>".GetLangVar('messagenochange')."</td></tr>
					</table>
				<br>";
	
	}
}


HTMLtrailers();

?>