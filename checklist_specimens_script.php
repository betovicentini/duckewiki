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
			$qq = "CREATE TABLE ".$tbname." (SELECT * FROM checklist_speclist WHERE isvalidlocal(GazetteerID,GPSPointID, ".$idd.", '".$tableref."'))"; 
			mysql_query($qq,$conn);
			$update=0;
}

$qq = "SELECT * FROM ".$tbname;
$rr = @mysql_query($qq,$conn);
$nr = @mysql_numrows($rr);
@mysql_free_result($rr);

//echo $update."  aqui";
if (($nr==0 || $update>0)) {
//echo $update."  aqui2";
	$qnu = "UPDATE `temp_progspec".$tbname."` SET percentage=1"; 
	mysql_query($qnu);
	session_write_close();
		
	unset($_SESSION['plothervars']);
	unset($_SESSION['qq']);
	unset($qq);
	$qq = "DROP TABLE ".$tbname;
	$rq = @mysql_query($qq,$conn);
$qq = "(SELECT 
pltb.GazetteerID,
pltb.GPSPointID,
pltb.EspecimenID, 
pltb.PlantaID, 
thepl.PlantaTag,
pltb.DetID,
(colpessoa.Abreviacao) as COLETOR, 
pltb.Number as NUMERO,
if(CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day)<>'0000-00-00',CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day),'FALTA') as DATA,
if(pltb.INPA_ID>0,pltb.INPA_ID+0,NULL) as ".$herbariumsigla.",
famtb.Familia as FAMILIA,
acentosPorHTML(gettaxonname(pltb.DetID,1,0)) as NOME,
acentosPorHTML(gettaxonname(pltb.DetID,1,1)) as NOME_AUTOR,
emorfotipo(pltb.DetID,0,0) as MORFOTIPO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'COUNTRY') as PAIS,  
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'MAJORAREA') as ESTADO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'MINORAREA') as MUNICIPIO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'GAZETTEER') as LOCAL,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'GAZETTEER_SPEC') as LOCALSIMPLES, 
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 0) as LONGITUDE,
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 1) as LATITUDE,
IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as ALTITUDE,
'edit-icon.png' AS EDIT,
'mapping.png' AS MAP,
'' as OBS,
IF(pltb.HabitatID>0,'environment_icon.png','') as HABT,
if (checkimgs(pltb.EspecimenID, pltb.PlantaID)>0,'camera.png','') as IMG,
checknir(pltb.EspecimenID,pltb.PlantaID) as NIRSpectra,";
//IF(ABS(pltb.Longitude)>0,pltb.Longitude+0,IF(pltb.GPSPointID>0,gpspt.Longitude+0,IF(ABS(gaz.Longitude)>0,gaz.Longitude+0,NULL))) as LONGITUDE, 
//IF(ABS(pltb.Longitude)>0,pltb.Latitude+0,IF(pltb.GPSPointID>0,gpspt.Latitude+0,IF(ABS(gaz.Longitude)>0,gaz.Latitude+0,NULL))) as LATITUDE, 
//acentosPorHTML(IF(pltb.GPSPointID>0,CONCAT(gazgps.GazetteerTIPOtxt,' ',gazgps.Gazetteer),IF(pltb.GazetteerID>0,CONCAT(gaz.GazetteerTIPOtxt,' ',gaz.Gazetteer),' '))) as LOCALSIMPLES,
//IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME,
if ($duplicatesTraitID>0) {
	$qq .= "
traitvaluespecs(".$duplicatesTraitID.", pltb.PlantaID, pltb.EspecimenID,'', 0, 0)+0 as DUPS,";
}
//pltb.GPSPointID,
//pltb.GazetteerID,
if ($daptraitid>0) {
	$qq .= "
traitvaluespecs(".$daptraitid.", pltb.PlantaID, pltb.EspecimenID,'mm', 0, 1) as DAPmm,";
}
if ($alturatraitid>0) {
	$qq .= "
traitvaluespecs(".$alturatraitid.", pltb.PlantaID, pltb.EspecimenID,'mm', 0, 1) as ALTURA,";
}
if ($habitotraitid>0) {
	$qq .= "
(traitvaluespecs(".$habitotraitid.", pltb.PlantaID, pltb.EspecimenID,'', 0, 1)) as HABITO,";
}
if ($traitfertid>0) {
	$qq .= "
(traitvaluespecs(".$traitfertid.", 0, pltb.EspecimenID,'', 0, 1)) as FERTILIDADE,";
}
$qq .= "IF(projetologo(pltb.ProjetoID)<>'',projetologo(pltb.ProjetoID),'') as PRJ,
(IF(projetostring(pltb.ProjetoID,0,0)<>'',projetostring(pltb.ProjetoID,0,0),'NÃO FOI DEFINIDO')) as PROJETOstr";
//,checktrait(pltb.EspecimenID, pltb.PlantaID,".$exsicatatrait.") as EXSICATA_IMG";
if ($quickview>0 && !empty($quicktbname)) {
	$qq .= " FROM ".$quicktbname." as filtertab JOIN Especimenes as pltb USING(EspecimenID)"; 
} else {
	$qq .= " FROM Especimenes as pltb";
}
$qq .= "
LEFT JOIN Plantas as thepl ON thepl.PlantaID=pltb.PlantaID
LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID
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

if ($filtro>0) {
	$qwhere = " WHERE pltb.FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR pltb.FiltrosIDS LIKE '%filtroid_".$filtro."')";
} 
else {
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
		$qwhere =" WHERE filtertab.EspecimenID>0";
	}
	if ($plantaid>0) {
		$qwhere=" WHERE pltb.PlantaID=".$plantaid;
	}
}

$qz = 'SELECT COUNT(*) as nrecs';
if ($quickview>0 && !empty($quicktbname)) {
	$qz .= " FROM ".$quicktbname." as filtertab JOIN Especimenes as pltb USING(EspecimenID)"; 
} 
else {
	$qz .= " FROM Especimenes as pltb";
}
$qz .= "
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID ".$qwhere;


$rz = mysql_query($qz,$conn);
$rwz = mysql_fetch_assoc($rz);
$nrz = $rwz['nrecs'];
//mysql_free_result($rz);
//$_SESSION['exportnresult'] = $nrz;
$stepsize = 100;
$nsteps = ceil($nrz/$stepsize);
$qq = $qq.$qwhere;
//$_SESSION['qq'] = $qq;
$step=0;
//echo $qq."<br />";
$lixo = 10230;
if ($lixo==10230) {
while ( $step<=$nsteps ) {
//if ($prepared==1 && $step<=$nsteps && $update>0) {
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
	$qqq = $qbase." ".$qq."  LIMIT $st1,$stepsize)";
	//echo $qqq."<br />";
	$check = mysql_query($qqq,$conn);
	if ($check) {
		$perc = ceil(($step/$nsteps)*100);
		$qnu = "UPDATE `temp_progspec".$tbname."` SET percentage=".$perc; 
		mysql_query($qnu);
		//echo $perc."<br />";
		session_write_close();
	}
	$step = $step+1;
} 
if ($step>$nsteps) {
		$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(EspecimenID)";
		mysql_query($qq,$conn);
		$sql = "CREATE INDEX COLETOR ON ".$tbname."  (COLETOR)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX NUMERO ON ".$tbname."  (NUMERO)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX ".$herbariumsigla." ON ".$tbname."  (".$herbariumsigla.")";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX NOME ON ".$tbname."  (NOME)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX NOMEA ON ".$tbname."  (NOME_AUTOR)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX PAIS ON ".$tbname."  (PAIS)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX ESTADO ON ".$tbname."  (ESTADO)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX MUNICIPIO ON ".$tbname."  (MUNICIPIO)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX LOCAL ON ".$tbname."  (LOCAL)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX LOCALSIMPLES ON ".$tbname."  (LOCALSIMPLES)";
		mysql_query($sql,$conn);
		unset($_SESSION['qq']);
		unset($_SESSION['exportnresult']);
}
$qnu = "UPDATE `temp_progspec".$tbname."` SET percentage='100'"; 
mysql_query($qnu);
echo "Concluido";
}
//session_write_close();
} 
else {
	$qnu = "UPDATE `temp_progspec".$tbname."` SET percentage='100'"; 
	mysql_query($qnu);
	echo "Concluido";
}
session_write_close();

?>
