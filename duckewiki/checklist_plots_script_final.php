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


//INICIA O CONTEUDO//
//$tbname = "checklist_plots";
//unset($_SESSION['plothervars']);
//unset($_SESSION['qq']);
//unset($qq);

if ($update==1) {

$qq = "DROP TABLE ".$tbname;
@mysql_query($qq,$conn);


$qq = "CREATE TABLE IF NOT EXISTS  ".$tbname." ( nomeid CHAR(100),  Pais CHAR(200),  MajorArea CHAR(255),  MinorArea CHAR(255),  Localidade CHAR(255), LocalSimples CHAR(255), Latitude  CHAR(20),  Longitude  CHAR(20),  Altitude CHAR(10), Parcela CHAR(255), idd INT(10),  tableref CHAR(50),  HABT INT(10)) CHARACTER SET utf8"; 
mysql_query($qq,$conn);

$qq = "(SELECT 
loc.nomeid as nomeid,
(crt.Country) as Pais,
'' as MajorArea,
'' as MinorArea,
'' as Localidade,
'' as LocalSimples,
loc.lat as Latitude,
loc.logitude AS Longitude,
loc.alt AS Altitude,
'' as Parcela,
((REPLACE(loc.nomeid,'paisid_',''))+0) as idd,
'Country' as tableref,
checkhabitat_localidade(((REPLACE(loc.nomeid,'paisid_',''))+0),'Country') as HABT
FROM LocalitySimpleSearch as loc JOIN Country as crt ON  ((REPLACE(loc.nomeid,'paisid_',''))+0)=crt.CountryID
WHERE loc.nomeid LIKE 'pais%')";

$qq2 = "(SELECT 
loc.nomeid as nomeid,
(crt.Country) as Pais,
(prov.Province) as MajorArea,
'' as MinorArea,
'' as Localidade,
'' as LocalSimples,
loc.lat as Latitude,
loc.logitude AS Longitude,
loc.alt AS Altitude,
'' as Parcela,
((REPLACE(loc.nomeid,'provinceid_',''))+0) as idd,
'Province' as tableref,
checkhabitat_localidade(((REPLACE(loc.nomeid,'provinceid_',''))+0),'Province') as HABT
FROM LocalitySimpleSearch as loc JOIN Province as prov ON ((REPLACE(loc.nomeid,'provinceid_',''))+0)=prov.ProvinceID
JOIN Country as crt ON prov.CountryID=crt.CountryID WHERE loc.nomeid LIKE 'provin%')";

$qq3 = "(SELECT 
loc.nomeid as nomeid,
(crt.Country) as Pais,
(prov.Province) as MajorArea,
(muni.Municipio) as MinorArea,
'' as Localidade,
'' as LocalSimples,
loc.lat as Latitude,
loc.logitude AS Longitude,
loc.alt AS Altitude,
'' as Parcela,
((REPLACE(loc.nomeid,'municipioid_',''))+0) as idd,
'Municipio' as tableref,
checkhabitat_localidade(((REPLACE(loc.nomeid,'municipioid_',''))+0),'Municipio') as HABT
FROM LocalitySimpleSearch as loc JOIN Municipio as muni ON ((REPLACE(loc.nomeid,'municipioid_',''))+0)=muni.MunicipioID JOIN Province as prov ON muni.ProvinceID=prov.ProvinceID
JOIN Country as crt ON prov.CountryID=crt.CountryID WHERE loc.nomeid LIKE 'mun%')";

$qq4 = "SELECT 
loc.nomeid as nomeid,
(crt.Country) as Pais,
(prov.Province) as MajorArea,
(muni.Municipio) as MinorArea,
(gaz.PathName) as Localidade,
(gaz.Gazetteer) as LocalSimples,
loc.lat as Latitude,
loc.logitude AS Longitude,
loc.alt AS Altitude,
IF ((gaz.DimX+gaz.DimY)>0,CONCAT(gaz.DimX,'x',gaz.DimY),'') as Parcela,
((REPLACE(loc.nomeid,'gazetteerid_',''))+0) as idd,
'Gazetteer' as tableref,
checkhabitat_localidade(((REPLACE(loc.nomeid,'gazetteerid_',''))+0),'Gazetteer') as HABT
FROM LocalitySimpleSearch as loc JOIN Gazetteer as gaz ON ((REPLACE(loc.nomeid,'gazetteerid_',''))+0)=gaz.GazetteerID JOIN Municipio as muni ON muni.MunicipioID=gaz.MunicipioID JOIN Province as prov ON muni.ProvinceID=prov.ProvinceID
JOIN Country as crt ON prov.CountryID=crt.CountryID WHERE loc.nomeid LIKE 'gaz%' AND loc.nome NOT LIKE '%quadra%'";

$sqls = array();

$sql1 = "INSERT INTO ".$tbname." ".$qq;
$sqls[] = $sql1;
$sql2 = "INSERT INTO ".$tbname." ".$qq2;
$sqls[] = $sql2;
$sql3 = "INSERT INTO ".$tbname." ".$qq3;
$sqls[] = $sql3;

$qu = "UPDATE `temp_progplot` SET percentage=1"; 
mysql_query($qu);
session_write_close();

$nsteps = count($sqls)-1;
$steper = 10/$nsteps;
$perc =1;
foreach( $sqls as $qq) {
	$check   = mysql_query($qq,$conn);
	//echo $qq."<br>";
	$perc = $perc+$steper;
	if ($check ) {
		$qu = "UPDATE `temp_progplot` SET percentage='".$perc."'"; 
		mysql_query($qu);
		session_write_close();
	} 
}

$que ="SELECT COUNT(*) AS nrecs FROM LocalitySimpleSearch as loc WHERE loc.nomeid LIKE 'gaz%'";
$rque = mysql_query($que,$conn);
$rquew = mysql_fetch_assoc($rque);
$nrque = $rquew['nrecs'];
$steper = 40/$nrque;
for ($st=0;$st<=($nrque-1);$st++) {
	$sql4 = "INSERT INTO ".$tbname." (".$qq4." LIMIT ".$st.",1)";
	//echo $sql4."<br>";
	$check   = mysql_query($sql4,$conn);
	$perc = $perc+$steper;
	if ($check ) {
		$qu = "UPDATE `temp_progplot` SET percentage='".$perc."'"; 
		mysql_query($qu);
		session_write_close();
	} 
}

//$sql2 = "INSERT INTO ".$tbname." ".$qq4;
//$sqls[] = $sql2;


//$qqq = "CREATE TABLE IF NOT EXISTS ".$tbname." (SELECT * FROM (".$qq." UNION ".$qq2." UNION ".$qq3." UNION ".$qq4.") AS thistb)";
//$sqls[] = $qqq;
//$check = mysql_query($qqq,$conn);

$sqls = array();

$sql = "ALTER TABLE ".$tbname." ADD TempID INT(10) unsigned NOT NULL auto_increment PRIMARY KEY";
$sqls[] = $sql;
//mysql_query($sql,$conn);
$sql = "ALTER TABLE  `".$tbname."` CHANGE  `tableref`  `tableref` CHAR( 9 ) NULL DEFAULT  ''";
$sqls[] = $sql;
//mysql_query($sql,$conn);
$sql = "ALTER TABLE  `".$tbname."` CHANGE  `idd`  `idd` INT( 10 ) NULL DEFAULT  0";
$sqls[] = $sql;
//mysql_query($sql,$conn);
$sql = "CREATE INDEX Pais ON ".$tbname."  (Pais)";
$sqls[] = $sql;
//mysql_query($sql,$conn);
$sql = "CREATE INDEX MajorArea ON ".$tbname."  (MajorArea)";
$sqls[] = $sql;
//mysql_query($sql,$conn);
$sql = "CREATE INDEX MinorArea ON ".$tbname."  (MinorArea)";
$sqls[] = $sql;
//mysql_query($sql,$conn);
$sql = "CREATE INDEX Localidade ON ".$tbname."  (Localidade)";
$sqls[] = $sql;
//mysql_query($sql,$conn);
$sql = "CREATE INDEX LocalSimples ON ".$tbname."  (LocalSimples)";
$sqls[] = $sql;
//mysql_query($sql,$conn);
$sql = "CREATE INDEX idd ON ".$tbname."  (idd)";
$sqls[] = $sql;
//mysql_query($sql,$conn);
$sql = "CREATE INDEX tableref ON ".$tbname."  (tableref)";
$sqls[] = $sql;
//mysql_query($sql,$conn);


//$sql = "ALTER TABLE ".$tbname." ADD HABT INT(10)";
//mysql_query($sql,$conn);
$sql = "ALTER TABLE ".$tbname." ADD NSPP INT(10)";
$sqls[] = $sql;
//mysql_query($sql,$conn);
$sql = "ALTER TABLE ".$tbname." ADD NPLANTAS INT(10)";
$sqls[] = $sql;
//mysql_query($sql,$conn);
$sql = "ALTER TABLE ".$tbname." ADD NSPECS INT(10)";
$sqls[] = $sql;
//mysql_query($sql,$conn);
//$sql = "ALTER TABLE ".$tbname." ADD PLOTDATA INT(10) DEFAULT 0";
//mysql_query($sql,$conn);

$nsteps = count($sqls)-1;
$steper = 5/$nsteps;
foreach( $sqls as $qq) {
	$check   = mysql_query($qq,$conn);
	$perc = $perc+$steper;
	if ($check ) {
		$qu = "UPDATE `temp_progplot` SET percentage='".$perc."'"; 
		mysql_query($qu);
		session_write_close();
	} 
}



$qz = "SELECT * FROM ".$tbname;
$rz = mysql_query($qz);
$nrz = mysql_numrows($rz);
$prc = 35/$nrz;
while ($rzw = @mysql_fetch_assoc($rz)) {
	$idd = $rzw['idd'];
	$ref = $rzw['tableref'];
	$qu = "UPDATE ".$tbname." set NSPP=checkspeciesbylocal(".$idd.",'".$ref."') WHERE TempID='".$rzw['TempID']."'";
	//echo $qu."<br />";
	mysql_query($qu);
	$qu = "UPDATE ".$tbname." set NPLANTAS=countplantsbylocal(".$idd.",'".$ref."') WHERE TempID='".$rzw['TempID']."'";
	mysql_query($qu);
	//echo $qu."<br />";
	$qu = "UPDATE ".$tbname." set NSPECS=countspecsbylocal(".$idd.",'".$ref."') WHERE TempID='".$rzw['TempID']."'";
	mysql_query($qu);
	//echo $qu."<br /><br />";
	$oldpe = $perc;
	$perc = $perc+$prc;
	if (round($perc)>$oldpe && round($perc)<100) {
		$qnu = "UPDATE `temp_progplot` SET percentage=".$perc; 
		mysql_query($qnu);
		session_write_close();
	}
}

$qz = "ALTER TABLE `".$tbname."`
        ORDER BY `NSPP` DESC, `LocalSimples` ASC";
mysql_query($qz,$conn);        
$qnu = "UPDATE `temp_progplot` SET percentage='100'"; 
mysql_query($qnu,$conn);     
echo "Concluido";
session_write_close();
} 
else {
	$qnu = "UPDATE `temp_progplot` SET percentage='100'"; 
	mysql_query($qnu,$conn);     
	echo "Concluido";
	session_write_close();
}
//COMECA O RODAPE//
//$which_java = array(//"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
//FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>