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

$qq = "DROP TABLE ".$tbname;
@mysql_query($qq);
//$qq = "DROP TABLE  temp_lixo".$tbname;
//@mysql_query($qq);

//SE NAO FOR UM FILTRO POR LOCALIDADE, ENTAO GERA O CHECKLIST A PARTIR DOS DADOS
$perc = 1;

//TaxonomySimple($all=true,$conn);
TaxonomySimple($all=false,$conn);


$qnu = "UPDATE `temp_progspp".$tbname."` SET percentage=".$perc; 
mysql_query($qnu, $conn);
session_write_close();
	
$qq = "CREATE TABLE IF NOT EXISTS ".$tbname." (
TempID INT(10) unsigned NOT NULL auto_increment,
DetNivel CHAR(20),
FamiliaID INT(10) ,
GeneroID INT(10) ,
EspecieID INT(10) ,
InfraEspecieID INT(10) ,
NOME CHAR(255),
NOME_AUTOR CHAR(255),
MORFOTIPO CHAR(100),
ESPECIMENES INT(10) ,
PLANTAS INT(10),
PLOTS INT(10),
FAMILIA CHAR(100),
HABT INT(10),
IMG INT(10),
EDIT CHAR(255),
MAP CHAR(255),
OBS CHAR(255),
DetID INT(10),
NIRSpectra INT(10),
SILICA INT(10),
FLORES INT(10),
FRUTOS INT(10),
VEG_CHARS INT(10),
FERT_CHARS INT(10),
FOLHA_IMG INT(10),
FLOR_IMG INT(10),
FRUTO_IMG INT(10),
EXSICATA_IMG INT(10),
PRIMARY KEY (TempID)) CHARACTER SET utf8";
echo $qq."<br>";
@mysql_query($qq,$conn);
$qq = "ALTER TABLE ".$tbname."  ENGINE = InnoDB";
@mysql_query($qq,$conn);

if (empty($tableref) || !isset($tableref)) {
	//TaxonomySimple($all=false,$conn);
	$qzu = "SELECT COUNT(*) AS nspecies FROM  `TaxonomySimpleSearch`";
	$rzu = mysql_query($qzu, $conn);
	$rzw = mysql_fetch_assoc($rzu);
	$nspecs = $rzw['nspecies'];
	$stepsize = 1;
	$nsteps = ceil(($nspecs)/$stepsize);
	$qbase = "INSERT INTO ".$tbname." ( DetNivel, FamiliaID, GeneroID, EspecieID, InfraEspecieID, NOME, NOME_AUTOR, MORFOTIPO,ESPECIMENES, PLANTAS, PLOTS, FAMILIA, HABT, IMG, EDIT, MAP, OBS,NIRSpectra, SILICA,FLORES, FRUTOS, VEG_CHARS, FERT_CHARS, FOLHA_IMG, FLOR_IMG, FRUTO_IMG, EXSICATA_IMG) ";
    $qq = "(SELECT 
IF (SUBSTRING(nomeid,1,5)='famid','Familia', IF (SUBSTRING(nomeid,1,5)='genus','Genero', IF (SUBSTRING(nomeid,1,5)='infsp','InfraEspecie', IF (SUBSTRING(nomeid,1,5)='speci','Especie','')))) as DetNivel,
FamiliaID,
GeneroID,
EspecieID,
InfraEspecieID,
acentosPorHTML(getnamewithautorone(id, nomeid, 1,0)),
acentosPorHTML(getnamewithautorone(id, nomeid, 1,1)),
emorfotipo(0, EspecieID, InfraEspecieID),
countspecs(id, nomeid) ,
countplantas(id, nomeid) ,
countplantasplots(id, nomeid),
Familia,
checklocalhabitat(InfraEspecieID, EspecieID, GeneroID, FamiliaID),
checktaxaimg(FamiliaID, GeneroID, EspecieID, InfraEspecieID,0),
gettropicosnamelong(FamiliaID, GeneroID, EspecieID, InfraEspecieID,0) ,
'mapping.png',
 'edit-notes.png',
checktaxanir(FamiliaID, GeneroID, EspecieID, InfraEspecieID) as NIRSpectra,
countsilica(".$traitsilica.",FamiliaID, GeneroID, EspecieID, InfraEspecieID) as SILICA,
(countfert(100,FamiliaID, GeneroID, EspecieID, InfraEspecieID)+countfert(103,FamiliaID, GeneroID, EspecieID, InfraEspecieID)) as FLORES,
(countfert(101,FamiliaID, GeneroID, EspecieID, InfraEspecieID)+countfert(564,FamiliaID, GeneroID, EspecieID, InfraEspecieID)) as FRUTOS,
0 AS VEG_CHARS,
0 AS FERT_CHARS,
0 AS FOLHA_IMG,
0 AS FLOR_IMG,
0 AS FRUTO_IMG,
0 AS EXSICATA_IMG
 FROM `TaxonomySimpleSearch`";

	$step=0;
	while ( $step<=$nsteps ) {
		if ($step==0) {
			$st1 = 0;
		} 
		else {
			$st1 = $st1+$stepsize;
		}
		$qqq = $qbase." ".$qq." LIMIT $st1,$stepsize)";
		echo $qqq."<br />";
		$check = mysql_query($qqq,$conn);
		if ($check) {
			mysql_query($sql,$conn);
			$perc = ceil(($step/($nsteps+1))*100);
			$qnu = "UPDATE `temp_progspp".$tbname."` SET percentage=".$perc; 
			mysql_query($qnu, $conn);
			session_write_close();
		}
		$step = $step+1;
   }
} 
else {
$qsql = "INSERT INTO ".$tbname." ( FamiliaID, GeneroID, EspecieID, InfraEspecieID, NOME, NOME_AUTOR, MORFOTIPO, ESPECIMENES, PLANTAS, PLOTS, FAMILIA, MAP, OBS,DetID,NIRSpectra, SILICA,FLORES, FRUTOS, VEG_CHARS, FERT_CHARS, FOLHA_IMG, FLOR_IMG, FRUTO_IMG, EXSICATA_IMG)  (SELECT 
iddet.FamiliaID,
iddet.GeneroID,
iddet.EspecieID,
iddet.InfraEspecieID,
'',
'',
emorfotipo(0, iddet.EspecieID, iddet.InfraEspecieID),
IF(COUNT(DISTINCT especs.EspecimenID)>0,COUNT(DISTINCT especs.EspecimenID),0) AS ESPECIMENES,
IF(COUNT(DISTINCT pl.PlantaID)>0,COUNT(DISTINCT pl.PlantaID),0) AS PLANTAS,
IF(COUNT(DISTINCT getiftreeplots(pl.GazetteerID))>0,(COUNT(DISTINCT getiftreeplots(pl.GazetteerID))-1),0) AS PLOTS,
famtb.Familia,
'mapping.png' AS MAP,
'edit-notes.png' as OBS,
iddet.DetID,
checktaxanir(FamiliaID, GeneroID, EspecieID, InfraEspecieID) as NIRSpectra,
countsilica(".$traitsilica.",FamiliaID, GeneroID, EspecieID, InfraEspecieID) as SILICA,
(countfert(100,FamiliaID, GeneroID, EspecieID, InfraEspecieID)+countfert(103,FamiliaID, GeneroID, EspecieID, InfraEspecieID)) as FLORES,
(countfert(101,FamiliaID, GeneroID, EspecieID, InfraEspecieID)+countfert(564,FamiliaID, GeneroID, EspecieID, InfraEspecieID)) as FRUTOS
0 AS VEG_CHARS,
0 AS FERT_CHARS,
0 AS FOLHA_IMG,
0 AS FLOR_IMG,
0 AS FRUTO_IMG,
0 AS EXSICATA_IMG
FROM Identidade as iddet 
LEFT JOIN Especimenes as especs ON especs.DetID=iddet.DetID 
LEFT JOIN Plantas as pl ON pl.DetID=iddet.DetID
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID
WHERE (isvalidlocal(especs.GazetteerID, especs.GPSPointID, ".$idd.", '".$tableref."')>0 OR isvalidlocal(pl.GazetteerID, pl.GPSPointID, ".$idd.", '".$tableref."')>0)  AND iddet.EspecieID>0 GROUP BY CONCAT(famtb.Familia,gettaxonname(iddet.DetID,1,0))
)";

//echo $qsql."<br />";
mysql_query($qsql,$conn);
$perc = 40;
$qnu = "UPDATE `temp_progspp".$tbname."` SET percentage=".$perc; 
mysql_query($qnu, $conn);
session_write_close();
$sql = "UPDATE ".$tbname." SET HABT=checklocalhabitat(InfraEspecieID,EspecieID,GeneroID,FamiliaID)";
mysql_query($sql,$conn);
$perc = 55;
$qnu = "UPDATE `temp_progspp".$tbname."` SET percentage=".$perc; 
mysql_query($qnu, $conn);
session_write_close();
$sql = "UPDATE ".$tbname." SET NOME=acentosPorHTML(gettaxonname(DetID,1,0))";
mysql_query($sql,$conn);
$sql = "UPDATE ".$tbname." SET NOME_AUTOR=acentosPorHTML(gettaxonname(DetID,1,1))";
mysql_query($sql,$conn);
$perc = 70;
$qnu = "UPDATE `temp_progspp".$tbname."` SET percentage=".$perc; 
mysql_query($qnu, $conn);
session_write_close();

$sql = "UPDATE ".$tbname." SET IMG=checktaxaimg(FamiliaID,GeneroID,EspecieID,InfraEspecieID,0)";
mysql_query($sql,$conn);
$perc = 85;
$qnu = "UPDATE `temp_progspp".$tbname."` SET percentage=".$perc; 
mysql_query($qnu, $conn);
session_write_close();
$sql = "UPDATE ".$tbname." SET EDIT=gettropicosname(DetID)";
mysql_query($sql,$conn);
$perc = 95;
$qnu = "UPDATE `temp_progspp".$tbname."` SET percentage=".$perc; 
mysql_query($qnu, $conn);
session_write_close();

$sql = "UPDATE ".$tbname." SET VEG_CHARS=checkformtaxastatus(CONCAT(Familia,'_VEGCHARS' SEPARATOR ''), FamiliaID, GeneroID, EspecieID, InfraEspecieID, 3)";
mysql_query($sql,$conn);

$sql = "UPDATE ".$tbname." SET FERT_CHARS=checkformtaxastatus(CONCAT(Familia,'_FERTCHARS' SEPARATOR ''), FamiliaID, GeneroID, EspecieID, InfraEspecieID, 3)";
mysql_query($sql,$conn);

$sql = "UPDATE ".$tbname." SET FOLHA_IMG=checktaxaimg(FamiliaID, GeneroID, EspecieID, InfraEspecieID,".$folhaimgtraitid.")";
mysql_query($sql,$conn);

$sql = "UPDATE ".$tbname." SET FLOR_IMG=checktaxaimg(FamiliaID, GeneroID, EspecieID, InfraEspecieID,".$florimgtraitid.")";
mysql_query($sql,$conn);

$sql = "UPDATE ".$tbname." SET FRUTO_IMG=checktaxaimg(FamiliaID, GeneroID, EspecieID, InfraEspecieID,".$frutoimgtraitid.")";
mysql_query($sql,$conn);

$sql = "UPDATE ".$tbname." SET EXSICATA_IMG=checktaxaimg(FamiliaID, GeneroID, EspecieID, InfraEspecieID,".$exsicatatrait.")";
mysql_query($sql,$conn);

}
//$check = mysql_query($qq,$conn);
$sql = "CREATE INDEX FAMILIA ON ".$tbname."  (FAMILIA)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX Nome_Cientifico ON ".$tbname."  (NOME)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX N_Especimenes ON ".$tbname."  (ESPECIMENES)";
mysql_query($sql,$conn);
$sql = "CREATE INDEX N_Plantas ON ".$tbname."  (PLANTAS)";
mysql_query($sql,$conn);
if (!empty($tableref)) {
$perc = 100;
$qnu = "UPDATE `temp_progspp".$tbname."` SET percentage=".$perc; 
mysql_query($qnu, $conn);
session_write_close();
}

echo "Concluido";
session_write_close();

?>