<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");
//ini_set("mysql.implicit_flush","On");
//Start session
session_start();
//ob_implicit_flush(true);
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);


//echopre($_SESSION);
$tbname = "temp_filtroPERFORM_".substr(session_id(),0,10);

$qq = "DROP TABLE ".$tbname;
mysql_query($qq,$conn);
$qq = "CREATE TABLE IF NOT EXISTS ".$tbname." (
			tempid INT(10) unsigned NOT NULL auto_increment,
			EspecimenID INT(10),
			PlantaID INT(10),
			RefSelected VARCHAR(10),
			PRIMARY KEY (tempid)) CHARACTER SET utf8";
//echo $qq."<br>";
mysql_query($qq,$conn);

$runcriteria = unserialize($_SESSION['searchcriteria']);
//echopre($runcriteria);
if (count($runcriteria)>0) {
unset($runcriteria['final']);
unset($runcriteria['regexp']);
unset($runcriteria['ispopup']);
unset($runcriteria['traitid']);

$runcri = array_filter($runcriteria);
unset($_SESSION['searchcriteriarun']);
$nsteps = count($runcri)-1;
$prepared=1;
$_SESSION['searchcriteriarun'] = $runcri;
$step=0;

while ($step<=$nsteps) {
		$kkk = array_keys($_SESSION['searchcriteriarun']);
		$thek = $kkk[$step];
		$thev = $_SESSION['searchcriteriarun'][$thek];
		$$thek = $thev;
		//BUSCA POR TAXONOMIA
		if (!empty($taxsearch) || !empty($nomesciid)) {
		if (!empty($taxsearch)) {
			$nomesciid = $taxsearch;
		}
		$taxid = explode("_",$nomesciid);
		//echopre($taxid);
		if ($taxid[0]=='famid') { $taxcol ='FamiliaID';}
		if ($taxid[0]=='genusid') { $taxcol = 'GeneroID';  }
		if ($taxid[0]=='speciesid') { $taxcol = 'EspecieID';  }
		if ($taxid[0]=='infspid') { $taxcol = 'InfraEspecieID';  }
		$taxi = $taxid[1];
	
	    $qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT EspecimenID,PlantaID,'taxa' FROM Especimenes JOIN Identidade USING(DetID) WHERE ".$taxcol."='".$taxi."')";
	    mysql_query($qq,$conn);
	
		//echo $qq."<br />";
	    $qq = "INSERT INTO ".$tbname." (PlantaID,RefSelected) (SELECT PlantaID,'taxa' FROM Plantas JOIN Identidade USING(DetID) WHERE ".$taxcol."='".$taxi."')";
		mysql_query($qq,$conn);
		//echo $qq."<br />";
	} 
	
		//BUSCA MORFOTIPOS E IDENTERMINADOS
		if ($nodets==1) {
				$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT EspecimenID,PlantaID,'taxa' FROM Especimenes WHERE DetID=0 OR DetID IS NULL)";
				//echo $qq."<br >";
				mysql_query($qq,$conn);
				$qq = "INSERT INTO ".$tbname." (PlantaID,RefSelected) (SELECT PlantaID,'taxa' FROM Plantas WHERE DetID=0 OR DetID IS NULL)";
				mysql_query($qq,$conn);
				//echo $qq."<br >";
		}
		if ($morfodets==1) {
				$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT EspecimenID,PlantaID,'taxa' FROM Especimenes JOIN Identidade as idd USING(DetID) LEFT JOIN Tax_Especies sp ON idd.EspecieID=sp.EspecieID LEFT JOIN Tax_InfraEspecies as infsp ON infsp.InfraEspecieID=idd.InfraEspecieID WHERE sp.Morfotipo=1 OR infsp.	InfraEspecieNivel LIKE '%morfossp%')";
				mysql_query($qq,$conn);
				echo $qq."<br >";
				$qq = "INSERT INTO ".$tbname." (PlantaID,RefSelected) (SELECT PlantaID,'taxa' FROM Plantas JOIN Identidade as idd USING(DetID) LEFT JOIN Tax_Especies sp ON idd.EspecieID=sp.EspecieID LEFT JOIN Tax_InfraEspecies as infsp ON infsp.InfraEspecieID=idd.InfraEspecieID WHERE sp.Morfotipo=1 OR infsp.	InfraEspecieNivel LIKE '%morfossp%')";
				mysql_query($qq,$conn);
				echo $qq."<br >";
		}
		//BUSCA POR LOCALIDADE
		if (!empty($gazsearch) || !empty($localid)) {
			if (!empty($gazsearch)) {
				$localid = $gazsearch;
			}
			$loctp = explode("_",$localid);
			$locid = $loctp[1];
			if ($loctp[0]=='municipioid') { $theref = 'Municipio';}
			if ($loctp[0]=='paisid') { $theref = 'Country';}
			if ($loctp[0]=='gazetteerid') { $theref = 'Gazetteer';}
			if ($loctp[0]=='provinceid') { $theref = 'Province';}
	
			$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT spec.EspecimenID,spec.PlantaID,'local' FROM Especimenes as spec WHERE isvalidlocal(spec.GazetteerID,spec.GPSPointID,".$locid.",'".$theref."')>0)";
			//echopre($gget);
			//echo $qq."<br />";
			mysql_query($qq,$conn);
			
			$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT 0,spec.PlantaID,'local' FROM Plantas as spec WHERE isvalidlocal(spec.GazetteerID,spec.GPSPointID,".$locid.",'".$theref."')>0)";
			//echo $qq."<br />";
			mysql_query($qq,$conn);
	}
		//BUSCA POR COORDENADAS GEOGRAFICAS
		if (abs($latno)>=0 && abs($longno)>0 && abs($latse)>=0 && abs($longse)>0) {
		$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT EspecimenID,PlantaID,'coord' FROM Especimenes WHERE getlatlong(Latitude,Longitude,GPSPointID,GazetteerID,MunicipioID,ProvinceID,CountryID,1)<=(".$latno.") AND getlatlong(Latitude,Longitude,GPSPointID,GazetteerID,MunicipioID,ProvinceID,CountryID,0)>=(".$longno.") AND getlatlong(Latitude,Longitude,GPSPointID,GazetteerID,MunicipioID,ProvinceID,CountryID,1)<=(".$latno.")>=(".$latse.") AND getlatlong(Latitude,Longitude,GPSPointID,GazetteerID,MunicipioID,ProvinceID,CountryID,0)<=(".$longse."))";
		//echo $qq."<br />";
		mysql_query($qq,$conn);
		$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT 0,PlantaID,'coord' FROM Plantas WHERE getlatlong(Latitude,Longitude,GPSPointID,GazetteerID,0,0,0,1)<=(".$latno.") AND getlatlong(Latitude,Longitude,GPSPointID,GazetteerID,0,0,0,0)>=(".$longno.") AND getlatlong(Latitude,Longitude,GPSPointID,GazetteerID,0,0,0,1)<=(".$latno.")>=(".$latse.") AND getlatlong(Latitude,Longitude,GPSPointID,GazetteerID,0,0,0,0)<=(".$longse.")";
		//echo $qq."<br />";
		mysql_query($qq,$conn);
	}
		//BUSCA POR COLETORES
		if (count($coletoresids)>0) {
			if (empty($colnumfrom)) {
				foreach ($coletoresids as $cole) {
					$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT EspecimenID,PlantaID,'coletor' FROM Especimenes WHERE ColetorID='".$cole."')";
					mysql_query($qq,$conn);
				}
			} else {
				if (empty($colnumto)) { $colnumto = $colnumfrom;}
				foreach ($coletoresids as $cole) {
					$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT EspecimenID,PlantaID,'coletornum' FROM Especimenes WHERE ColetorID='".$cole."' AND (Number+0)>=".$colnumfrom." AND (Number+0)<=".$colnumto.")";
					echo $qq."<br />";
					mysql_query($qq,$conn);
				}
			}
	}
		//BUSCA POR DATA
		if (!empty($anofrom) || !empty($anoto) || !empty($mesfrom) || !empty($mesto) || !empty($diafrom) || !empty($diato)) {
			$qw = '';
			if (!empty($anofrom)) {
				$anof = $anofrom;
			} else {
				$qu = 'SELECT DISTINCT Ano FROM Especimenes ORDER BY Ano LIMIT 0,1';
				$ru = mysql_query($qu,$conn);
				$rw = mysql_fetch_assoc($ru);
				$anof = $ru['Ano'];
			}
			if (!empty($mesfrom)) {
				$mesf = $mesfrom;
			} else {
				$mesf = 1;
			}
			if (!empty($diafrom)) {
				$diaf = $diafrom;
			} else {
				$diaf = 1;
			}
			if (!empty($anoto)) {
				$anot = $anoto;
			} else {
				$anot = $anof;
			}
			if (!empty($mesto)) {
				$mest = $mesto;
			} else {
				$mest = 12;
			}
			if (!empty($diato)) {
				$ddt = $diato;
			} else {
				$mm = array(1,3,5,7,8,10,12);
				if (in_array($mesf,$mm)) {
					$ddt = 31;
				} else {
					if ($mesf!=2) {
						$ddt = 30;
					} else {
						$ddt = 29;
					}
				} 
			}
			$coldatarange1 = $anof."-".$mesf."-".$diaf;
			$coldatarange2 = $anot."-".$mest."-".$diat;
			$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT EspecimenID,PlantaID,'data' FROM Especimenes WHERE 
			date_format(str_to_date(CONCAT(Ano,'-',Mes,'-',Day),'%Y-%m-%d'), '%Y-%m-%d')>=date_format(str_to_date('".$coldatarange1."','%Y-%m-%d'), '%Y-%m-%d') AND date_format(str_to_date(CONCAT(Ano,'-',Mes,'-',Day),'%Y-%m-%d'), '%Y-%m-%d')<=date_format(str_to_date('".$coldatarange2."','%Y-%m-%d'), '%Y-%m-%d'))";
			mysql_query($qq,$conn);
		}
		//BUSCA POR HABITAT
		if ($habitatid>0) {
			$qq = "SELECT * FROM Habitat WHERE HabitatID='".$habitatid."'";
			$res = mysql_query($qq,$conn);
			$row = mysql_fetch_assoc($res);
			$classname = $row['Habitat'];
			$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT EspecimenID,PlantaID,'habitat'  FROM Especimenes JOIN Habitat USING(HabitatID) WHERE Habitat.PathName LIKE '%".$classname."%')";
			mysql_query($qq,$conn);
			$qq = "INSERT INTO ".$tbname." (PlantaID,RefSelected) (SELECT PlantaID,'habitat'  FROM Plantas JOIN Habitat USING(HabitatID) WHERE Habitat.PathName LIKE '%".$classname."%')";
			mysql_query($qq,$conn);
	} 
	if ($herbariumnum>0) {
			if ($herbariumnum==1) {
				$qwh = "  WHERE  INPA_ID>0";
			} else {
				$qwh = "  WHERE  INPA_ID=0 OR INPA_ID IS NULL";
			}
			$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (SELECT EspecimenID,PlantaID,'herbariumnum'  FROM Especimenes ".$qwh.")";
			mysql_query($qq,$conn);
	} 
		//BUSCA POR VARIAVEIS DE FORMULARIOS
		if (substr($thek,0,9)=='traitvar_') {
		$tz = explode("_",$thek);
		$trid = $tz[1];
		$val= $thev;
		$qu = "SELECT * FROM Traits WHERE TraitID='".$trid."'";
		$ru = mysql_query($qu,$conn);
		$ruw = mysql_fetch_assoc($ru);

		if (@in_array('none',$val) || @in_array('empty',$val)) {
			$qsel = "SELECT PlantaID,'trait_".$trid."'FROM Plantas WHERE checkifnotrait(".$trid.",PlantaID,0)=0";
			$qsel2 = "SELECT EspecimenID,PlantaID,'trait_".$trid."' FROM Especimenes WHERE checkifnotrait(".$trid.",EspecimenID,0)=0";
		} 
		else {
			if ($ruw['TraitTipo']=='Variavel|Quantitativo') {
				if (!empty($val[0]) && !empty($val[1])) {
					$qsel = "SELECT PlantaID,'trait_".$trid."' FROM `Monitoramento` WHERE TraitID='".$trid."' AND SPLIT_STR_MIN(TraitVariation,';')>=".$val[0]." AND
SPLIT_STR_MAX(TraitVariation,';')<=".$val[1]."";
					$qsel2 = "SELECT EspecimenID,PlantaID,'trait_".$trid."' FROM `Traits_variation` WHERE TraitID='".$trid."' AND SPLIT_STR_MIN(TraitVariation,';')>=".$val[0]." AND
SPLIT_STR_MAX(TraitVariation,';')<=".$val[1]."";
				}
			}
			if ($ruw['TraitTipo']=='Variavel|Texto') {
				$qsel = "SELECT PlantaID,'trait_".$trid."' FROM `Monitoramento` WHERE TraitID='".$trid."' AND TraitVariation LIKE '%".$val."%'";
				$qsel2 = "SELECT EspecimenID,PlantaID,'trait_".$trid."' FROM `Traits_variation` WHERE TraitID='".$trid."' AND TraitVariation LIKE '%".$val."%'";
			}
			if ($ruw['TraitTipo']=='Variavel|Imagem') {
				$qsel = "SELECT PlantaID,'trait_".$trid."' FROM `Monitoramento` WHERE TraitID='".$trid."'";
				$qsel2 = "SELECT EspecimenID,PlantaID,'trait_".$trid."' FROM `Traits_variation` WHERE TraitID='".$trid."'";
			}
			if ($ruw['TraitTipo']=='Variavel|Categoria') {
				if (!is_array($val)) {
					$trarrs = explode(";",$val);
				} else {
					$trarrs = $val;
				}
				$iq = 0;
				$qww = '';
				foreach ($trarrs as $stateid) {
					if (($stateid+0)>0) {
						if ($iq==0) {
							$qww = " (TraitVariation LIKE '%;".$stateid."' OR TraitVariation LIKE '".$stateid.";%' OR TraitVariation LIKE '".$stateid."')";
						} else {
							$qww = " OR (TraitVariation LIKE '%;".$stateid."' OR TraitVariation LIKE '".$stateid.";%' OR TraitVariation LIKE '".$stateid."')";
						}
						$iq++;
					}
				}
				$qsel = "SELECT PlantaID,'trait_".$trid."'  FROM `Monitoramento` WHERE TraitID='".$trid."' AND (".$qww.")";
				$qsel2 = "SELECT EspecimenID,PlantaID,'trait_".$trid."' FROM `Traits_variation` WHERE TraitID='".$trid."' AND (".$qww.")";
			}
		}
		$qq = "INSERT INTO ".$tbname." (EspecimenID,PlantaID,RefSelected) (".$qsel2.")";
		mysql_query($qq,$conn);
		//echo $qq."<br>";
		flush();
		$qq = "INSERT INTO ".$tbname." (PlantaID,RefSelected) (".$qsel.")";
		//echo $qq."<br>";
		mysql_query($qq,$conn);
		flush();
		}
		
		$step=$step+1;
		$perc = (ceil(($step/$nsteps*100))*80)/100;
		if ($perc<100) {
			$qnu = "UPDATE `temp_filtro_".substr(session_id(),0,5)."` SET percentage=".$perc; 
			mysql_query($qnu, $conn);
			session_write_close();
	}
}
$step = $step+1;
if ($step>$nsteps) {
	$tbname2 = $tbname."_tmp";
	$qu = "SELECT Count(*) as nrecs FROM ".$tbname;
	$ruq = mysql_query($qu,$conn);
	//echo $qu;
	$rww = mysql_fetch_assoc($ruq);
	extract($rww);
	if ($nrecs>0) {
	$qu = "SELECT DISTINCT RefSelected FROM ".$tbname;
	$ruq = mysql_query($qu,$conn);
	$ncriteria = mysql_numrows($ruq);
	$qq = "DROP TABLE ".$tbname2;
	mysql_query($qq,$conn);
	$qq = "CREATE TABLE IF NOT EXISTS ".$tbname2." (
				tempid INT(10) unsigned NOT NULL auto_increment,
				EspecimenID INT(10),
				PlantaID INT(10),
				Ntimes INT(10),
				NCriteria INT(10),
				PRIMARY KEY (tempid)) CHARACTER SET utf8";
	mysql_query($qq,$conn);
	$qq = "INSERT INTO ".$tbname2." (EspecimenID,PlantaID,Ntimes,NCriteria)  SELECT 0 as EspecimenID,PlantaID,Count(DISTINCT RefSelected) as Ntimes, '".$ncriteria."' FROM ".$tbname." GROUP BY PlantaID";
	echo $qq."<br />";
	mysql_query($qq,$conn);
	$qq = "INSERT INTO ".$tbname2." (EspecimenID,PlantaID,Ntimes,NCriteria)  SELECT EspecimenID,PlantaID,Count(DISTINCT RefSelected) as Ntimes, '".$ncriteria."' FROM ".$tbname." GROUP BY EspecimenID";
	mysql_query($qq,$conn);
	echo $qq."<br />";
	$tbname5 = $tbname."_pl";
	$qq = "DROP TABLE ".$tbname5;
	@mysql_query($qq,$conn);
	//$qq = "CREATE TABLE ".$tbname5." SELECT * FROM ".$tbname2." WHERE Ntimes=NCriteria AND Ntimes>0 AND (PlantaID+EspecimenID)>0";
	$qq = "CREATE TABLE ".$tbname5." SELECT * FROM ".$tbname2." WHERE Ntimes=NCriteria AND Ntimes>0 AND ((PlantaID>0 AND PlantaID IS NOT NULL) OR (EspecimenID>0 AND EspecimenID IS NOT NULL))";
	$perc = $perc+10;
	if ($perc<100) {
			$qnu = "UPDATE `temp_filtro_".substr(session_id(),0,5)."` SET percentage=".$perc; 
			mysql_query($qnu, $conn);
			session_write_close();
	}

	@mysql_query($qq,$conn);
	$qq = "DROP TABLE ".$tbname;
	//mysql_query($qq,$conn);
	$qq = "DROP TABLE ".$tbname2;
	//mysql_query($qq,$conn);

	//$tbname2 = $tbname2a;

	$sql = "CREATE INDEX EspecimenID ON ".$tbname5."  (EspecimenID)";
	mysql_query($sql,$conn);
	$sql = "CREATE INDEX PlantaID ON ".$tbname5."  (PlantaID)";
	mysql_query($sql,$conn);
	//$step=0;
	//$tbname3 = $tbname."_sp";
	//$tbname4 = $tbname."_tree";
	$fim = $tbname5;
	echo $fim;
	$qnu = "UPDATE `temp_filtro_".substr(session_id(),0,5)."` SET percentage=100"; 
	mysql_query($qnu, $conn);
	session_write_close();

}
	else {
		$fim = "NADA";
		echo $fim;
		session_write_close();
	}
}

} else {
		$fim = "NADA";
		echo $fim;
			session_write_close();
}


?>