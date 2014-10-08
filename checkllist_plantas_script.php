<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

//Start session
session_start();

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

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}

if ($idd>0 && !empty($tableref)) {
			$qq = "DROP TABLE ".$tbname;
			@mysql_query($qq,$conn);
			$qq = "CREATE TABLE ".$tbname." (SELECT * FROM checklist_pllist WHERE isvalidlocal(GazetteerID,GPSPointID, ".$idd.", '".$tableref."'))"; 
			mysql_query($qq,$conn);
			$update=0;
}

$qq = "SELECT * FROM ".$tbname;
$rr = @mysql_query($qq,$conn);
$nr = @mysql_numrows($rr);
@mysql_free_result($rr);

if (($nr==0 || $update>0)) {
	$qnu = "UPDATE `temp_prog".$tbname."` SET percentage='1'"; 
	mysql_query($qnu);
	session_write_close();

	unset($_SESSION['plothervars']);
	unset($_SESSION['qq']);
	unset($qq);
	$qq = "DROP TABLE ".$tbname;
	$rq = @mysql_query($qq,$conn);

//IF(ABS(pltb.Longitude)>0,pltb.Longitude+0,IF(pltb.GPSPointID>0,gpspt.Longitude+0,IF(ABS(gaz.Longitude)>0,gaz.Longitude+0,NULL))) as LONGITUDE, 
//IF(ABS(pltb.Longitude)>0,pltb.Latitude+0,IF(pltb.GPSPointID>0,gpspt.Latitude+0,IF(ABS(gaz.Longitude)>0,gaz.Latitude+0,NULL))) as LATITUDE, 
//IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE,
//$nn = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
$qq = "(SELECT 
pltb.PlantaID, 
pltb.DetID,
'edit-icon.png' AS EDIT,
plantatag(pltb.PlantaID) as TAGtxt,
STRIP_NON_DIGIT(pltb.PlantaTag) as TAG,
famtb.Familia as FAMILIA,
acentosPorHTML(gettaxonname(pltb.DetID,1,0)) as NOME,
acentosPorHTML(gettaxonname(pltb.DetID,1,1)) as NOME_AUTOR,
emorfotipo(pltb.DetID,0,0) as MORFOTIPO,
(IF(pltb.GPSPointID>0,countrygps.Country,IF(pltb.GazetteerID>0,countrygaz.Country,' '))) as PAIS, 
(IF(pltb.GPSPointID>0,provigps.Province,IF(pltb.GazetteerID>0,provgaz.Province,' '))) as ESTADO, 
(IF(pltb.GPSPointID>0,munigps.Municipio,IF(pltb.GazetteerID>0,muni.Municipio,' '))) as MUNICIPIO, 
(IF(pltb.GPSPointID>0,gazgps.PathName,IF(pltb.GazetteerID>0,gaz.PathName,' '))) as LOCAL,
(IF(pltb.GPSPointID>0,gazgps.Gazetteer,IF(pltb.GazetteerID>0,gaz.Gazetteer,' '))) as LOCALSIMPLES,
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, muni.MunicipioID, 0, 0, 0) as LONGITUDE,
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, muni.MunicipioID, 0, 0, 1) as LATITUDE,
IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE,
";

//IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME,


//acentosPorHTML(IF(pltb.GPSPointID>0,CONCAT(gazgps.GazetteerTIPOtxt,' ',gazgps.Gazetteer),IF(pltb.GazetteerID>0,CONCAT(gaz.GazetteerTIPOtxt,' ',gaz.Gazetteer),' '))) as LOCALSIMPLES,
//pltb.GPSPointID,
//pltb.GazetteerID,
if ($daptraitid>0) {
	$qq .= "
(traitvalueplantas(".$daptraitid.", pltb.PlantaID, 'mm', 0, 1)+0) AS DAPmm,";
}
if ($alturatraitid>0) {
	$qq .= "
(traitvalueplantas(".$alturatraitid.", pltb.PlantaID, 'm', 0, 1))+0 AS ALTURA,";

}
if ($habitotraitid>0) {
	$qq .= "
(traitvalueplantas(".$habitotraitid.", pltb.PlantaID, '', 0, 0)) AS HABITO,";
}
if ($statustraitid>0) {
	$qq .= "
traitvalueplantas(".$statustraitid.",pltb.PlantaID, '', 0,0 ) AS STATUS,";
}
$qq .= "
'mapping.png' AS MAP,
IF((gaz.DimX+gaz.DimY)>0,pltb.GazetteerID,'') AS PLOT,
checkplantaspecimens(pltb.PlantaID) AS ESPECIMENES,
'' as OBS,
IF(pltb.HabitatID>0,'environment_icon.png','') as HABT,
IF(projetologo(pltb.ProjetoID)<>'',projetologo(pltb.ProjetoID),'') as PRJ,
(IF(projetostring(pltb.ProjetoID,0,0)<>'',projetostring(pltb.ProjetoID,0,0),'NÃO FOI DEFINIDO')) AS PROJETOstr,
if (checkimgs(0, pltb.PlantaID)>0,'camera.png','') as IMG,
traitvalueplantas(".$duplicatesTraitID.", pltb.PlantaID, '', 0, 0) as DUPS,
checknir(0,pltb.PlantaID) as NIRSpectra,
pltb.GazetteerID,
pltb.GPSPointID";
//checktrait(0, pltb.PlantaID,".$exsicatatrait.") as EXSICATA_IMG";
if ($quickview>0 && !empty($quicktbname)) {
	$qq .= " FROM ".$quicktbname." as filtertab JOIN Plantas as pltb USING(PlantaID)"; 
} else {
	$qq .= " FROM Plantas as pltb";
}
$qq .= "
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID  
LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID  
LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID  
LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID  
LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID  
LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID  
LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID 
LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID  
LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID";
$qwhere = '';
if ($filtro>0) {
	$qwhere = " WHERE pltb.FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR pltb.FiltrosIDS LIKE '%filtroid_".$filtro."')";
} else {
	if ($detid>0) {
			if ($infspecid>0) {
				$qwhere = " WHERE iddet.InfraEspecieID=".$infspecid;
			} else {
				if ($specid>0) {
					$qwhere = " WHERE iddet.EspecieID=".$specid;
				} else {
					if ($genid>0) {
						$qwhere = " WHERE iddet.GeneroID=".$genid;
					} 
					else {
						$qwhere = " WHERE iddet.FamiliaID=".$famid;
					}
				}
			}		
	}
	if ($quickview>0 && !empty($quicktbname)) {
		$qwhere=" WHERE filtertab.PlantaID>0";
	}
	if ($specimenid>0) {
		$qqz = "SELECT PlantaID FROM Especimenes WHERE EspecimenID=".$specimenid;
		$rr = @mysql_query($qqz);
		$nrr = @mysql_fetch_assoc($rr);
		$pltid = $nrr['PlantaID'];
		$qwhere=" WHERE pltb.PlantaID=".$pltid;
		mysql_free_result($rr);
		
	}

}
$qz = 'SELECT COUNT(*) as nrecs';
if ($quickview>0 && !empty($quicktbname)) {
	$qz .= " FROM ".$quicktbname." as filtertab JOIN Plantas as pltb USING(PlantaID)"; 
} 
else {
	$qz .= " FROM Plantas as pltb";
}
$qz .= "
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID ".$qwhere;
$rz = mysql_query($qz,$conn);
$rwz = mysql_fetch_assoc($rz);
$nrz = $rwz['nrecs'];
mysql_free_result($rz);
//$_SESSION['exportnresult'] = $nrz;
$stepsize = 2000;
$nsteps = ceil($nrz/$stepsize);
$qq = $qq.$qwhere;
$step=0;
//echo $step.'  '.$nsteps;
while ( $step<=$nsteps ) {
	if ($step==0) {
		$st1 = 0;
		$qbase = "CREATE TABLE IF NOT EXISTS ".$tbname;
	} 
	else {
		//$qq = $_SESSION['qq'];
		//$plothervars = unserialize($_SESSION['plothervars']);
		$st1 = $st1+$stepsize;
		$qbase = "INSERT INTO ".$tbname;
	}
	$qqq = $qbase." ".$qq." LIMIT $st1,$stepsize)";
	//echo $qqq."<br />";
	$check = mysql_query($qqq,$conn);
	if ($check) {
		$perc = ceil(($step/$nsteps)*100);
		$qnu = "UPDATE `temp_prog".$tbname."` SET percentage=".$perc; 
		mysql_query($qnu);
		session_write_close();
	}
	$step = $step+1;
} 


if ($step>$nsteps) {
	$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(PlantaID)";
	$rq = mysql_query($qq,$conn);
	//mysql_free_result($rq);
	//$sql = "CREATE INDEX PlantaID ON ".$tbname."  (PlantaID)";
	//$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX FAMILIA ON ".$tbname."  (FAMILIA)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX NOME ON ".$tbname."  (NOME)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX NOME ON ".$tbname."  (NOME_AUTOR)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX PAIS ON ".$tbname."  (PAIS)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX ESTADO ON ".$tbname."  (ESTADO)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX MUNICIPIO ON ".$tbname."  (MUNICIPIO)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX LOCAL ON ".$tbname."  (LOCAL)";
	$rq = mysql_query($sql,$conn);
	$sql = "CREATE INDEX LOCALSIMPLES ON ".$tbname."  (LOCALSIMPLES)";
	$rq = mysql_query($sql,$conn);
	if ($daptraitid>0) {
		$sql = "CREATE INDEX DAPmm ON ".$tbname."  (DAPmm)";
		$rq = mysql_query($sql,$conn);
	}
	//$sql = "CREATE INDEX GazetteerID ON ".$tbname."  (GazetteerID)";
	//$rq = mysql_query($sql,$conn);
	//$sql = "CREATE INDEX GPSPointID ON ".$tbname."  (GPSPointID)";
	//$rq = mysql_query($sql,$conn);
	//unset($_SESSION['qq']);
	//unset($_SESSION['exportnresult']);
}
$qnu = "UPDATE `temp_prog".$tbname."` SET percentage='100'"; 
mysql_query($qnu);
echo "Concluido";
session_write_close();
} 
else {
	echo 'houve erro geral';
	$qnu = "UPDATE `temp_prog".$tbname."` SET percentage='100'"; 
	mysql_query($qnu);
	echo "Concluido";
}
session_write_close();

?>
