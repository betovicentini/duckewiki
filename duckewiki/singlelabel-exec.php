<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);


if (empty($etitype)) { $etitype='EspecimenesIDS';}
if ($etitype =='EspecimenesIDS') { 
	$tbname = 'Especimenes';
	$tbname2 = 'Plantas';
	$primid = 'EspecimenID';
} else {
	$tbname = 'Plantas';
	$tbname2 = 'Especimenes';
	$primid = 'PlantaID';
}
$temptable = "temp_lab".substr(session_id(),0,10)."_".$specimenid;
//$formnotes =0;

$qd = "DROP TABLE IF EXISTS ".$temptable;
@mysql_query($qd,$conn);

$qd = "SET lc_time_names = 'pt_BR'";
@mysql_query($qd,$conn);

$qq = "CREATE TABLE ".$temptable." (TempID INT(10) NOT NULL AUTO_INCREMENT, PRIMARY KEY (TempID))";

if ($etitype =='EspecimenesIDS') { 
	$qq .= " SELECT 
maintb.EspecimenID as wikid, 
colpessoa.Abreviacao as coletor, 
CONCAT(IF(maintb.Prefixo IS NULL OR maintb.Prefixo='','',CONCAT(maintb.Prefixo,'-')),maintb.Number,IF(maintb.Sufix IS NULL OR maintb.Sufix='','',CONCAT('-',maintb.Sufix))) as numcol, 
DATE_FORMAT(concat(IF(maintb.Ano>0,maintb.Ano,1),'-',IF(maintb.Mes>0,maintb.Mes,1),'-',IF(maintb.Day>0,maintb.Day,1)),'%d-%b-%Y') as datacol, 
addcolldescr(maintb.AddColIDS) as addcol, 
localidadestring2(maintb.GazetteerID,maintb.GPSPointID,maintb.MunicipioID,maintb.ProvinceID,maintb.CountryID,maintb.Latitude,maintb.Longitude,maintb.Altitude,1) as locality,
INPA_ID as herbnum,
maintb.Herbaria as herbarios,
plantatag(maintb.PlantaID) as tagnum";
	if ($duplicatesTraitID>0) {
			$qq .= ", nduplicates(".$duplicatesTraitID.",EspecimenID,'Especimenes') as ndups";
	} else {
			$qq .= ", 1 as ndups";
	}
	$qq .= ", labeldescricao(maintb.EspecimenID+0,maintb.PlantaID+0,".$formnotes.",TRUE,FALSE) as descricao";

} 
else {
		$qq .= "SELECT maintb.PlantaID as wikid, 
		plantatag(maintb.PlantaID) as tagnum,
		'' as coletor,
		'' as numcol,
		IF(maintb.TaggedDate>0,DATE_FORMAT(maintb.TaggedDate,'%d-%b-%Y'),'') as datacol,
		addcolldescr(maintb.TaggedBy) as addcol,
localidadestring2(maintb.GazetteerID,maintb.GPSPointID,0,0,0,maintb.Latitude+0,maintb.Longitude+0,maintb.Altitude+0,1) as locality,
		'' as herbnum,
		'' as herbarios";
		if ($duplicatesTraitID>0) {
			$qq .= ", nduplicates(".$duplicatesTraitID.",PlantaID,'Plantas') as ndups";
		} else {
			$qq .= ", 1 as ndups";
		}
		$qq .= ", labeldescricao(0,maintb.PlantaID+0,".$formnotes.",TRUE,FALSE) as descricao";
}
	$qq .=", famtb.Familia as familia";
	$qq .=",  IF(iddet.InfraEspecieID>0 AND infsptb.Morfotipo=0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,' </i> ',sptb.EspecieAutor,' <i>',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,'</i> ',infsptb.InfraEspecieAutor),IF(iddet.EspecieID>0  AND sptb.Morfotipo=0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,'</i> ',sptb.EspecieAutor), IF(iddet.GeneroID>0 AND gentb.Genero<>'Indet',CONCAT('<i>',gentb.Genero,'<i>'),''))) as detnome";
	$qq .= ", CONCAT(detpessoa.Abreviacao,' [',DATE_FORMAT(iddet.DetDate,'%d-%b-%Y'),']') as detdetby";
	if ($formidhabitat>0) {
		$qq .= ", habitatstring(maintb.HabitatID, ".$formidhabitat.", TRUE,FALSE)  as habitat";
	}
	if ($daptraitid>0) {
		if ($tbname=='Especimenes') {
	$qq .=", traitvaluespecs(".$daptraitid.",maintb.PlantaID,maintb.EspecimenID,'mm',0,1) as DAPmm";
	    } else {
	$qq .=", traitvaluespecs(".$daptraitid.",maintb.PlantaID,0,'mm',0,1) as DAPmm";
	    }
	}
	if ($alturatraitid>0) {
		if ($tbname=='Especimenes') {
			$qq .=", traitvaluespecs(".$alturatraitid.",maintb.PlantaID,maintb.EspecimenID,'m',0,1) as ALTURAm";
	    } else {
	$qq .=", traitvaluespecs(".$alturatraitid.",maintb.PlantaID,0,'mm',0,1) as ALTURAm";
	    }
	}
	$qq .= ", vernaculars(maintb.VernacularIDS) as vernacular";
	$qq .= ", projetostring(maintb.ProjetoID,TRUE,TRUE) as projeto";
	$qq .= ", projetologo(maintb.ProjetoID) as logofile";
	$qq .= ", projetourl(maintb.ProjetoID) as prjurl";
	$qq .= ", 'HERBÁRIO' as herbariosinpa";
	$qq .= " FROM ".$tbname."  as maintb";
	if ($etitype =='EspecimenesIDS') { 
		$qq .= " LEFT JOIN Pessoas as colpessoa ON maintb.ColetorID=colpessoa.PessoaID";
	}
	$qq .= " LEFT JOIN Identidade as iddet ON maintb.DetID=iddet.DetID";
	$qq .= " LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
	LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
	LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
	LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID 
	LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID ";
	$qq .= " WHERE maintb.".$primid."=".$specimenid;
	$criou = mysql_query($qq,$conn);

	//echo $qq."<br />";

if ($criou) {
	echo "
<form name='myform' action='singlelabel-pdf.php' method='post'>
  <input type='hidden' name='temptable' value='".$temptable."'>
  <input type='hidden' name='apenasuma' value='1'>
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',1);</script>
</form>";

}
?>
