<?php
//Start session
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
if (!empty($_POST['detset'])) {
	$detset = $_POST['detset'];
	unset($_POST['detset']);
}
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
if (!empty($_GET['detset'])) {
	$detset = $_GET['detset'];
	unset($_GET['detset']);
}
$gget = cleangetpost($_GET,$conn);
@extract($gget);


//prep the data
if ($filtro>0 && !isset($changed)) { 
		$qq = "(SELECT Especimenes.EspecimenID,NULL as PlantaID,TraitVariation,Traits.TraitName,Especimenes.DetID,CONCAT(Abreviacao,' ',Number) as ImgRef,NULL as ImgRef2,TraitTipo FROM Especimenes JOIN Identidade USING(DetID) JOIN Traits_variation ON Especimenes.EspecimenID=Traits_variation.EspecimenID JOIN Traits ON Traits.TraitID=Traits_variation.TraitID JOIN Pessoas ON Especimenes.ColetorID=Pessoas.PessoaID WHERE TraitTipo LIKE '%Imagem' AND (Especimenes.FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR Especimenes.FiltrosIDS LIKE '%filtroid_".$filtro."'))";

		$qq = $qq." UNION (SELECT NULL as EspecimenID,Plantas.PlantaID,TraitVariation,Traits.TraitName,Plantas.DetID,PlantaTag as ImgRef1,InSituExSitu as ImgRef2,TraitTipo FROM Plantas JOIN Identidade USING(DetID) JOIN Traits_variation ON Plantas.PlantaID=Traits_variation.PlantaID JOIN Traits ON Traits.TraitID=Traits_variation.TraitID WHERE TraitTipo LIKE '%Imagem' AND (Plantas.FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR Plantas.FiltrosIDS LIKE '%filtroid_".$filtro."' ))";

		$res = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		if ($nres>0) {
			$imagens1=1;
			$uid = $_SESSION['userid'];
			
			$tbname = "temp_idbyimage_".$uid; 
			
			$qq = "DROP TABLE ".$tbname;
			@mysql_query($qq,$conn);

			
			$qq = "CREATE TABLE ".$tbname." (
				TempID INT(10) unsigned NOT NULL auto_increment,
				PlantaID INT(10),
				EspecimenID INT(10),
				DetID INT(10),
				TraitName VARCHAR(100),
				TaxaNome VARCHAR(100),
				ImageID INT(10),
				ImgRef CHAR(100),
				AddedBy INT(10), 
				AddedDate DATE,
				PRIMARY KEY (TempID))";
			mysql_query($qq,$conn);
		
		
			while ($row = mysql_fetch_assoc($res)) {
					$especimenid = $row['EspecimenID'];
					$plantaid = $row['PlantaID'];
					$imgids = explode(";",$row['TraitVariation']);
					$tid = $row['TraitName'];
					$detid = $row['DetID']+0;
					$ref = $row['ImgRef'];
					$ref2 = $row['ImgRef2'];
					if ($plantaid>0) {
						//taxonomy
						$tgn = sprintf("%05s",$ref+0);
						$pp='Tag: ';
						if ($ref2=='Insitu') { $pp = $pp."JB-N-";}				
						if ($ref2=='Exsitu') { $pp = $pp."JB-X-";}
						$ref = $pp.$tgn;
					}
					if ($detid>0) {
						$nomenoautor = getdetnoautor($detid,$conn);
					}

					if (count($imgids)>0) {
						foreach ($imgids as $img) {
							$img = $img+0;
							if ($img>0) {
								$arrayofvalues = array(
								'PlantaID' => $plantaid,
								'EspecimenID' => $especimenid,
								'DetID' => $detid,
								'TraitName' => $tid,
								'TaxaNome' => $nomenoautor,
								'ImgRef' => $ref,
								'ImageID' => $img+0);
							$newdetid = InsertIntoTable($arrayofvalues,'TempID',$tbname,$conn);
							
							}
						}
					} 
			}
			$qq  = "ALTER TABLE ".$tbname." ORDER BY TaxaNome,ImgRef ASC";
			mysql_query($qq,$conn);
		} 
		mysql_free_result($res);	
} elseif (!isset($filtro)) {	
	header("location: identifybyimg-form.php");
} else { 
	$uid = $_SESSION['userid'];
	$tbname = "temp_idbyimage_".$uid; 	
	//atualiza tabela temporaria se identificacao mudou
	if ($changed==1 && $detid<>$olddetid) {
		if ($detid>0) {
			$oldspecid = $oldspecid+0;
			$oldplid = $oldplid+0;			
			if ($oldspecid>0 && $oldplid==0) {
				$qu = "EspecimenID='".$oldspecid."'";				
			}
			if ($oldplid>0 && $oldspecid==0) {
				$qu = "PlantaID='".$oldplid."'";				
			}
			$qq = "UPDATE ".$tbname." SET DetID='".$detid."' WHERE ".$qu;
			mysql_query($qq,$conn);
		}
	}
	$qq = "SELECT * FROM ".$tbname." WHERE ImgRef='".$tempid."' LIMIT 0,1";
	$rs = mysql_query($qq,$conn);
	$imgrw = mysql_fetch_assoc($rs);
	$specid = $imgrw['EspecimenID']+0;
	$plid = $imgrw['PlantaID']+0;
	/////
}


HTMLheaders($body);

$lixo=1;
if ($lixo==1) {
	$dirsmall = 'img/thumbnails/';
	$dirmedium = 'img/lowres/';
	$dirlarge = 'img/copias_baixa_resolucao/';
	$diroriginal = 'img/originais/';
	
	$uid = $_SESSION['userid'];	
	$tbname = "temp_idbyimage_".$uid; 		

	unset($qu);	
	if ($specid>0) {
		$qu = " WHERE EspecimenID ='".$specid."'";
	}
	if ($plid>0) {
		$qu = " WHERE PlantaID ='".$plid."'";
	}
	if (empty($qu)) {
		$qu = "LIMIT 0,1";
	}
	$qq = "SELECT * FROM ".$tbname." JOIN Imagens ON ".$tbname.".ImageID=Imagens.ImageID";
	
	echo "Checar no mysql isso para ver as imagens do filtro";
	echo "<br>".$qq;
	echo "<br>Para as identificações usar a coluna EspecimenID que é a WikiEspecimenID dos dados exportados";
	



}

HTMLtrailers();

?>