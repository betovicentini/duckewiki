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



if (empty($spnome)) {
	header("location: taxanew-exec.php?famid=$famid&genusid=$genusid&newsp=$newsp&speciesid=$speciesid");
}

//print_r($_POST);
HTMLheaders('');

if ($newsp==2) { //checar se o nome ja nao existe
	$qq = "SELECT * FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE GeneroID='$genusid' AND Especie='$spnome'";
	$res = mysql_query($qq,$conn);
	$rr = mysql_numrows($res);
	if ($rr>0) {
		echo "<br><table align='center' class='erro' cellpadding=\"5\">
				<tr><td>".GetLangVar('erro3')."</td></tr>
				</table>";
		$erro++;		
	}
}
if (($newsp==4 || $newsp==2) && empty($autor)) { //nome publicado precisa de autor
	echo "<br><table align='center' class='erro' cellpadding=\"5\">
				<tr><td>".GetLangVar('erro1')."</td></tr>
				<tr class='tablethinborder'><td>".GetLangVar('nameautor')."</td></tr>
	</table>";
	$erro++;
}
if ($newsp==4 && empty($subvar)) { //se nova infraspecies publicado precisa indicar o tipo
	echo "<br><table align='center' class='erro' cellpadding=\"5\">
				<tr><td>".GetLangVar('erro1')."</td></tr>
				<tr class='tablethinborder'><td>".GetLangVar('nametipo')."</td></tr>
	</table>";
	$erro++;
}
//echo $erro;

if (empty($erro)) {
if ($newsp==5) {
	$subvar = 'morfossp';
}
if (($newsp==5 || $newsp==3) && empty($autor)) { //se morfotipo
	$qq = "SELECT * FROM Users WHERE UserID=".$_SESSION['userid'];
	$rr = mysql_query($qq,$conn);
	$rw = mysql_fetch_assoc($rr);
	$er = substr($rw['FirstName'], 0, 1);
	$autor = $rw['LastName'].", $er.";
}

if ($newsp==4 || $newsp==5) { //se nova infraspecies
		$fieldsaskeyofvaluearray = array(
			'EspecieID' => $speciesid,
			'InfraEspecie' => $spnome,
			'InfraEspecieAutor' => $autor,
			'InfraEspecieNivel' => $subvar,
			'PubRevista' => $pubrevista,
			'PubVolume' => $pubvolume,
			'PubAno' => $pubano,
			'Sinonimos' => $sinonimos,
			'GeoDistribution' => $geodist,
			'Notas' => $notas
			);	
		//get old values
		$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,'InfraEspecieID','Tax_InfraEspecies',$conn);
		if (!$newtrait) {
			$erro2++;
		}			
}
if ($newsp==2 || $newsp==3) {
		$fieldsaskeyofvaluearray = array(
			'GeneroID' => $genusid,
			'Especie' => $spnome,
			'EspecieAutor' => $autor,
			'PubRevista' => $pubrevista,
			'PubVolume' => $pubvolume,
			'PubAno' => $pubano,
			'Sinonimos' => $sinonimos,
			'GeoDistribution' => $geodist,
			'Notas' => $notas
			);
			$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,'EspecieID','Tax_Especies',$conn);
			if (!$newtrait) {
				$erro2++;
			}			
}

} //if empty $erro

if (empty($erro2) && empty($erro)) {
echo "<br><table align='center' class='success' cellpadding=\"5\">
		<tr><td>".GetLangVar('sucesso1')."</td></tr>
		<tr><td>
		<form action=taxanew-form.php method='post'>
			<input type='submit' class='bsubmit' value='".GetLangVar('namenovo')." ".GetLangVar('nametaxa')."'>
		</form>
		</td></tr>
		</table><br>";
} else {
	echo "<br><table align='center' class='erro' cellpadding=\"5\">
		<tr><td>
		<form action=taxanew-exec.php method='post'>
			<input type = 'hidden' name='famid' value='$famid'>
			<input type = 'hidden' name='genusid' value='$genusid'>
			<input type = 'hidden' name='speciesid' value='$speciesid'>
			<input type = 'hidden' name='newsp' value='$newsp'>
			<input type = 'hidden' name='spnome' value='$spnome'>
			<input type = 'hidden' name='autor' value='$autor'>
			<input type = 'hidden' name='pubrevista' value='$pubrevista'>
			<input type = 'hidden' name='pubvolume' value='$pubvolume'>
			<input type = 'hidden' name='pubvolume' value='$pubvolume'>
			<input type = 'hidden' name='pubano' value='$pubano'>
			<input type = 'hidden' name='sinonimos' value='$sinonimos'>
			<input type = 'hidden' name='geodist' value='$geodist'>
			<input type = 'hidden' name='notas' value='$notas'>
			<input type = 'hidden' name='subvar' value='$subvar'>

			<input type='submit' class='bsubmit' value='".GetLangVar('namevoltar')."'>
		</form>
		</td></tr>
		</table><br>";

}

TaxonomySimple($all=true,$conn);
TaxonomySimple($all=false,$conn);

HTMLtrailers();

?>