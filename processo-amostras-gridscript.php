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

$qq = "SELECT * FROM ".$tbname;
$rr = @mysql_query($qq,$conn);
$nr = @mysql_numrows($rr);
@mysql_free_result($rr);

if (($nr==0 || $update>0)) {
	$qq = "DROP TABLE ".$tbname;
	$rq = @mysql_query($qq,$conn);
	
	$qnu = "UPDATE `temp_progspec".$tbname."` SET percentage=1"; 
	mysql_query($qnu);
	session_write_close();
	
$qq = "(SELECT 
'edit-icon.png' AS EDIT,
procss.EXISTE,";
if ($duplicatesTraitID>0) {
	$qq .= "
traitvaluespecs(".$duplicatesTraitID.", pltb.PlantaID, pltb.EspecimenID,'', 0, 0)+0 as NDuplic,";
}
if ($exsicatatrait>0) {
	$qq .= "
IF ((".$exsicatatrait."+0)>0, IF(checktrait(pltb.EspecimenID, 0, (".$exsicatatrait."+0))='OK','camera.png',
IF(checktrait(pltb.EspecimenID, 0, 351)='OK','camera.png','image_falta.png')),'image_falta.png') as IMGSpec,";
}
$qn = "SELECT 1 FROM NirSpectra LIMIT 1";
$rn = @mysql_query($qn,$conn);
if ($rn) {
	$qq .= "
checknir(pltb.EspecimenID, pltb.PlantaID) as NirSpectra,";
} else {
	$qq .= "
0 as NirSpectra,";
}
if (empty($herbariumsigla)) {
	$herbariumsigla = 'HERB_NO';
}
//if(pltb.INPA_ID>0,pltb.INPA_ID+0,0) as ".$herbariumsigla.",
	$qq .= "
procss.".$herbariumsigla.",
procss.Herbaria,";
	$qq .= "
pltb.EspecimenID, 
pltb.PlantaID, 
pltb.DetID,
acentosPorHTML(colpessoa.Abreviacao) as COLETOR, 
pltb.Number as NUMERO,
if(CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day)<>'0000-00-00',CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day),'FALTANDO') as DATA,
famtb.Familia as FAMILIA,
IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME,
getlatlong(pltb.Longitude,pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 0) as LONGI, 
getlatlong(pltb.Longitude,pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 1) as LATI, 
'mapping.png' AS MAP,
'' as OBS,
IF(pltb.HabitatID>0,'environment_icon.png','') as HABT,";
if ($traitfertid>0) {
	$qq .= "
acentosPorHTML(traitvaluespecs_grid(".$traitfertid.", 0, pltb.EspecimenID,'', 0, 0)) as Fert,";
}
if ($traitsilica>0) {
	$qq .= "
acentosPorHTML(traitvaluespecs_grid(".$traitsilica.", 0, pltb.EspecimenID,'', 0, 0)) as Silica,";
}
if ($habitotraitid>0) {
	$qq .= "
acentosPorHTML(traitvaluespecs_grid(".$habitotraitid.", pltb.PlantaID, pltb.EspecimenID,'', 0, 1)) as HABITO,";
}
$qq .= "IF(projetologo(pltb.ProjetoID)<>'',projetologo(pltb.ProjetoID),'') as PRJ,
acentosPorHTML(IF(projetostring(pltb.ProjetoID,0,0)<>'',projetostring(pltb.ProjetoID,0,0),'NÃO FOI DEFINIDO')) as PROJETOstr";
$qq .= " FROM Especimenes as pltb";
$qq .= "
JOIN ProcessosLIST AS procss ON procss.EspecimenID=pltb.EspecimenID 
LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID  ";
//$qwhere = " WHERE pltb.FiltrosIDS LIKE '%filtroid_".$filtroid.";%' OR pltb.FiltrosIDS LIKE '%filtroid_".$filtroid."'";
$qwhere = " WHERE procss.EspecimenID>0 AND procss.ProcessoID='".$processoid."'";

$qz = 'SELECT COUNT(*) as nrecs FROM Especimenes as pltb JOIN ProcessosLIST AS procss ON procss.EspecimenID=pltb.EspecimenID '.$qwhere;
$rz = mysql_query($qz,$conn);
$rwz = mysql_fetch_assoc($rz);
$nrz = $rwz['nrecs'];

$stepsize = 100;
$nsteps = ceil($nrz/$stepsize);
$qq = $qq.$qwhere;

//echo $qq."<br >";


$step=0;
while ( $step<=$nsteps ) {
	if ($step==0) {
		$st1 = 0;
		$qbase = "CREATE TABLE IF NOT EXISTS ".$tbname;
	} 
	else {
		$st1 = $st1+$stepsize;
		$qbase = "INSERT INTO ".$tbname;
	}
	$qqq = $qbase." ".$qq." LIMIT $st1,$stepsize)";
	//echo $qqq."<br><br><br>";
	$check = mysql_query($qqq,$conn);
	if ($check) {
		$perc = ceil(($step/$nsteps)*100);
		$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
		mysql_query($qnu);
		session_write_close();
	}
	$step = $step+1;
} 
if ($step>$nsteps) {
		$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(EspecimenID)";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE ".$tbname." CHANGE `DATA` `DATA` DATE NULL DEFAULT NULL";
		mysql_query($qq,$conn);
		$sql = "CREATE INDEX COLETOR ON ".$tbname."  (COLETOR)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX NUMERO ON ".$tbname."  (NUMERO)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX ".$herbariumsigla." ON ".$tbname."  (".$herbariumsigla.")";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX NOME ON ".$tbname."  (NOME)";
		mysql_query($sql,$conn);
}
	$perc =100;
	$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
	mysql_query($qnu);
	echo "Concluido";
} 
else {
	$perc =100;
	$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
	mysql_query($qnu);
	echo "Concluido";
}
session_write_close();
?>