<?php
set_time_limit(0);
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



$body='';

HTMLheaders($body);

echo "<br>
<table align='center'>
<td>
Dados baixados...Agora aguarde upload ao banco de dados!
</td>
</tr></table><br>";
$numfam = 0;
$numgen = 0;
$numsp = 0;
$numsubsp = 0;

$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

$family = $_POST['family'];
$genus = $_POST['genus']; 
$userid = $_SESSION['userid'];
$sessiondate = $_SESSION['sessiondate'];

//Cria tabela vazia para armazenar dados do IPNI
$qq = "DROP TABLE temp";
mysql_query($qq,$conn);
$qq = "SELECT * FROM `IPNIextended` WHERE Need='1'";
$rr = mysql_query($qq,$conn);
$nn = mysql_numrows($rr);
$tt = "CREATE TABLE IF NOT EXISTS temp (";
$i=0;
$validFields = array();
$colnomes = array();
while ($row = mysql_fetch_assoc($rr)) {
	$validFields[$i] = $row['ArrayIndex'];
	$colnomes[$i] = $row['NewColnames'];  
	if ($i!==($nn-1)) {
		$tt = $tt.$row['NewColnames']." ".$row['ColType'].", ";
	} else {
		$tt = $tt.$row['NewColnames']." ".$row['ColType'].")";
	}
	$i++;
}
mysql_query($tt,$conn);

//Pega dados ja importados do IPNI e insere na tabela acima
if (empty($genus)) {
	$filename = $family.".txt";
} else {
	$filename = $genus.".txt";
}
$relativepath = 'uploads/';
$fop = fopen($relativepath.$filename, 'r');

$nn = count($validFields);
$j=0;
while (($data = fgetcsv($fop, 0, "%%")) !== FALSE) {
	if ($j>0) {
		$spinsert = "INSERT INTO temp (";
		for ($i = 0; $i < $nn; $i++) {
			//echo $colnomes[$i]." = ".$data[$validFields[$i]]."<br>";
			if ($i!==($nn-1)) {
				$spinsert = $spinsert.$colnomes[$i].", ";
			} else {
				$spinsert = $spinsert.$colnomes[$i].")";
			}
		}
		$spinsert = $spinsert." VALUES (";
		for ($i = 0; $i < $nn; $i++) {
			if ($i!==($nn-1)) {
				$spinsert = $spinsert."'".$data[$validFields[$i]]."',";
			} else {
				$spinsert = $spinsert."'".$data[$validFields[$i]]."')";
			}
		} 
	}
	mysql_query($spinsert,$conn);
	$j++;
}

if (!empty($genus)) {
	$qq = "SELECT DISTINCT Family FROM temp";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	$family = $row['Family'];
} 


//agora separa os dados nas tabelas correspondentes
//pega o ID da familia ou insere se nao existir
$qq = "SELECT * FROM Tax_Familias WHERE Familia='$family'";
$rr = mysql_query($qq,$conn);
$nn = mysql_numrows($rr);
if ($nn==1) {
	$row = mysql_fetch_assoc($rr);
	$familiaID = $row['FamiliaID'];
	$numfam++;
} elseif ($nn==0) {
	$qq = "INSERT INTO Tax_Familias (Familia,AddedBy,AddedDate) VALUES ('$family','$userid','$sessiondate')";
	mysql_query($qq,$conn);
	$qq = "SELECT * FROM Tax_Familias WHERE Familia='$family'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$familiaID = $row['FamiliaID'];
	$numfam++;
}

//seleciona os generos que contem especies
$qq = "SELECT DISTINCT Genus FROM temp WHERE Rank='spec.'";
$rr = mysql_query($qq,$conn);
while ($row = mysql_fetch_assoc($rr)) {
	$gen = $row['Genus'];
	$qq = "SELECT * FROM Tax_Generos WHERE Genero='$gen'";
	$res = mysql_query($qq,$conn);
	$nn = mysql_numrows($res);
	if ($nn==0) {
		$qq = "SELECT * FROM temp WHERE Genus='$gen' AND Rank='gen.' ORDER BY PubYEAR ASC";
		$resul = mysql_query($qq,$conn);
		$numgen = mysql_numrows($resul);
		if ($numgen>1) {
			$qq = "SELECT * FROM temp WHERE Genus='$gen' AND Rank='gen.' AND PubYEAR>0";
			$result = mysql_query($qq,$conn);
			$nnn = mysql_numrows($result);
			if ($nnn>1) {
				//echo "Registro para o genero ".$gen." duplicados:<br>";
				//while ($rrr = mysql_fetch_assoc($result)) {				
					//echo $rrr['PublishingAuthor']." ".$rrr['PubYEAR']." ".$rrr['Collation']."<br>";
				//}
				$qq = "SELECT * FROM temp WHERE Genus='$gen' AND Rank='gen.' AND PubYEAR>0 ORDER BY PubYEAR ASC LIMIT 1";
				$result = mysql_query($qq,$conn);
				$genres = mysql_fetch_assoc($result);
			} else {
				$genres = mysql_fetch_assoc($result);
			}
		} else {
		$genres = mysql_fetch_assoc($resul);		
		}
	if ($genres!==NULL) {
		$basionymAut = $genres['BasionymAuthor'];
		$basionym = $genres['Basionym'];
		$pubautor = $genres['PublishingAuthor'];
		$publication = $genres['Publication'];
		$collation = $genres['Collation'];
		$PubYEAR  = $genres['PubYEAR'];
		$Synonym  = $genres['Synonym'];
		$IpniID  = $genres['IpniID'];		
		$qq = "INSERT INTO Tax_Generos (Genero,FamiliaID,GeneroAutor,Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,IpniID,AddedBy,AddedDate) 
		VALUES ('$gen','$familiaID','$pubautor','$basionym','$basionymAut','$publication','$collation','$PubYEAR','$Synonym','$IpniID',
		'$userid','$sessiondate')";
		mysql_query($qq,$conn);
		$numgen++;
	}
	$genres=NULL;
	} else {
		$genjanodb++;
	}
	
}

//seleciona as especies 
$qq = "SELECT DISTINCT Genus,Species FROM temp WHERE Species>''";
$rr = mysql_query($qq,$conn);
while ($row = mysql_fetch_assoc($rr)) {
	$gen = $row['Genus'];
	$spec = $row['Species'];
	//get genusid
		$qq = "SELECT * FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE Genero='$gen' AND Familia='$family'";	
		$res = mysql_query($qq,$conn);
		$rrw = mysql_fetch_assoc($res);
		$genid = $rrw['GeneroID'];
	
	//checar se ja esta registrado
		$qq = "SELECT * FROM Tax_Especies WHERE Especie='$spec' AND GeneroID='$genid'";
		$rrr = mysql_query($qq,$conn);
		$numspec = mysql_numrows($rrr);
		if ($numspec==0) {
			//checar registros duplicados
			$qq = "SELECT * FROM temp WHERE Species='$spec' AND Reference>'' AND Genus='$gen'";
			$rrr = mysql_query($qq,$conn);
			$numinf = mysql_numrows($rrr);
			//se estiver duplicado seleciona aquele com data de publicacao
			if ($numinf>1) {
				/////////////////				
				$qq = "SELECT * FROM temp WHERE Species='$spec' AND Genus='$gen' AND Reference>'' AND PubYEAR>0";
				$result = mysql_query($qq,$conn);
				$nnn = mysql_numrows($result);
				if ($nnn>1) {
					$erro = array();
					$h = 0;
					while ($rrr = mysql_fetch_assoc($result)) {
						$erro[$h] = $rrr['PublishingAuthor']."_".$rrr['PubYEAR'];
						$h++;
					}
					$erro = array_unique($erro);
					$cerr = count($erro);
					if ($cerr>1) {
						//echo "Registro para a especie ".$gen." ".$spec." duplicados:<br>";
						$qq = "SELECT * FROM temp WHERE Species='$spec' AND Genus='$gen' AND Reference>'' AND PubYEAR>0 ORDER BY PubYEAR ASC LIMIT 1";
						$re = mysql_query($qq,$conn);
						$genres=mysql_fetch_assoc($re);
					} elseif ($cerr==1) {
						$qq = "SELECT * FROM temp WHERE Species='$spec' AND Genus='$gen' AND Reference>'' AND PubYEAR>0 LIMIT 1";
						$novores = mysql_query($qq,$conn);
						$genres = mysql_fetch_assoc($novores);
					}
				} else {
					$genres = mysql_fetch_assoc($result);
				}
				//////////////////
			} elseif ($numinf==1) {
				$genres = mysql_fetch_assoc($rrr);
			}
			if ($genres!==NULL) {
				$basionymAut = $genres['BasionymAuthor'];
				$basionym = $genres['Basionym'];
				$pubautor = $genres['PublishingAuthor'];
				$specautor = $genres['SpeciesAuthor'];
				if (!empty($specautor)) {
					$pubautor= $specautor;
				}
				$publication = $genres['Publication'];
				$collation = $genres['Collation'];
				$PubYEAR  = $genres['PubYEAR'];
				$geodist = $genres['GeoDistribution'];
				$Synonym  = $genres['Synonym'];
				$Synonym = str_replace($family, "", $Synonym);
				$IpniID  = $genres['IpniID'];
				$qq = "INSERT INTO Tax_Especies (Especie,GeneroID,EspecieAutor,Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,IpniID,GeoDistribution,AddedBy,AddedDate) 
				VALUES ('$spec','$genid','$pubautor','$basionym','$basionymAut','$publication','$collation','$PubYEAR','$Synonym','$IpniID', '$geodist',
				'$userid','$sessiondate')";
				//echo $qq."<br>";
				mysql_query($qq,$conn);
				$numsp++;
			}
		} else {
				$sppjanodb++;
		}//endif se ja registrado
		$genres=NULL;
		
}



//categorias infraespecificas
$qq = "SELECT DISTINCT Genus,Species,InfraSpecies FROM temp WHERE InfraSpecies>'' AND Reference>''";
$rr = mysql_query($qq,$conn);
$ninf = mysql_numrows($rr);
while ($row = mysql_fetch_assoc($rr)) {
	$gen = $row['Genus'];
	$spec = $row['Species'];
	$infraspec = $row['InfraSpecies'];
	//get especieID
		$qq = "SELECT * FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE Especie='$spec' AND Genero='$gen' AND Familia='$family'";	
		$res = mysql_query($qq,$conn);
		$rrw = mysql_fetch_assoc($res);
		$specid = $rrw['EspecieID'];
	//checar se o registro ja existe nao duplica
		$qq = "SELECT * FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE InfraEspecie='$infraspec'  AND Especie='$spec' AND Genero='$gen'";	
		$res = mysql_query($qq,$conn);
		$numinfraspec = mysql_numrows($res);
	if ($numinfraspec==0) {	
	//checar registros duplicados
	$qq = "SELECT * FROM temp WHERE InfraSpecies='$infraspec' AND Reference>'' AND Genus='$gen' AND Species='$spec'";
	//echo $qq."<br>";
	$rrr = mysql_query($qq,$conn);
	$numinf = mysql_numrows($rrr);
	if ($numinf>1) {
		$qq = "SELECT * FROM temp WHERE InfraSpecies='$infraspec' AND Reference>'' AND Genus='$gen' AND Species='$spec' AND PubYEAR>0 ORDER BY PubYEAR ASC LIMIT 1";
		$rrrr = mysql_query($qq,$conn);
		$numsubspecies = mysql_numrows($rrrr);
		if ($numsubspecies==1) {
			$genres = mysql_fetch_assoc($rrrr);
			//echo $gen." ".$spec." ".$infraspec." duplicated NOT IMPORTED!<br>";	
		} else {$genres= NULL;}
	} elseif ($numinf==1) {
		$genres = mysql_fetch_assoc($rrr);
	}
	if ($genres!==NULL) {
			$rank = $genres['Rank'];
			$basionymAut = $genres['BasionymAuthor'];
			$basionym = $genres['Basionym'];
			$pubautor = $genres['PublishingAuthor'];
			$publication = $genres['Publication'];
			$collation = $genres['Collation'];
			$PubYEAR  = $genres['PubYEAR'];
			$geodist = $genres['GeoDistribution'];
			$Synonym  = $genres['Synonym'];
			$Synonym = str_replace($family, "", $Synonym);
			$IpniID  = $genres['IpniID'];
			$qq = "INSERT INTO Tax_InfraEspecies (InfraEspecie,EspecieID,InfraEspecieNivel,InfraEspecieAutor,Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,IpniID,GeoDistribution,AddedBy,AddedDate) 
			VALUES ('$infraspec','$specid','$rank','$pubautor','$basionym','$basionymAut','$publication','$collation','$PubYEAR','$Synonym','$IpniID', '$geodist',
			'$userid','$sessiondate')";
			//echo $qq."<br>";
			mysql_query($qq,$conn);
			$numsubsp++;
	}
	} else {
		$infsppjanodb++;
	} //end if ja registrado
	$genres=NULL;
}	

$qq = "DROP TABLE temp";
mysql_query($qq,$conn);

if ($numsp>0) {
echo "<p class='success'>
		".GetLangVar('sucesso1')."<br>
		".GetLangVar('namefamily')." = <b>$numfam</b>!<br>
		".GetLangVar('namegenus')." = <b>$numgen</b>!";
		if ($genjanodb>0) {
		 	echo " ($genjanodb ".GetLangVar('jacadastrado').") ";
		 }
		echo "<br>
		".GetLangVar('namespecies')." = <b>$numsp</b>! ";
		if ($sppjanodb>0) {
		 	echo " ($sppjanodb ".GetLangVar('jacadastrado').") ";
		 }
		echo "<br>
		".GetLangVar('nameinfraspecies')." = <b>$numsubsp</b>! ";
		if ($infsppjanodb>0) {
		 	echo " ($infsppjanodb ".GetLangVar('jacadastrado').") ";
		 }
		echo "<br></p>
		<table align='center'>
	 <form action=novoIPNItaxa-form.php method='post'>
		<tr><td align='center'><input type = 'submit' class='bsubmit' value='".GetLangVar('namenovo')." ".GetLangVar('nametaxa')."'></td></tr>
	 </form>
		</table>";
} else {

echo "<p class='erro'>
		".strtoupper(GetLangVar('jacadastrado'))."<br>
		".GetLangVar('namefamily')." = <b>$numfam</b>! <br>
		".GetLangVar('namegenus')." = <b>$numgen</b>! ";
		if ($genjanodb>0) {
		 	echo " ($genjanodb ".GetLangVar('jacadastrado').") ";
		 }
		echo "<br>
		".GetLangVar('namespecies')." = <b>$numsp</b>! ";
		if ($sppjanodb>0) {
		 	echo " ($sppjanodb ".GetLangVar('jacadastrado').") ";
		 }
		echo "<br>
		".GetLangVar('nameinfraspecies')." = <b>$numsubsp</b>! ";
		if ($infsppjanodb>0) {
		 	echo " ($infsppjanodb ".GetLangVar('jacadastrado').") ";
		 }
		echo "<br></p>
		<table align='center'>
	 <form action=novoIPNItaxa-form.php method='post'>
		<tr><td align='center'><input type = 'submit' class='bsubmit' value='".GetLangVar('namenovo')." ".GetLangVar('nametaxa')."'></td></tr>
	 </form>
		</table>";


}

TaxonomySimple($all=true,$conn);
TaxonomySimple($all=false,$conn);


HTMLtrailers();

?>