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


//$perc =1;
//echo $perc." PORCENTAGEM <br />";
//$qnu = "UPDATE `temp_exportGPX.".substr(session_id(),0,10)."` SET percentage=".$perc; 
//mysql_query($qnu);
//session_write_close();


//DEFINE NOME DO ARQUIVO A SER GERADO NA PASTA temp/
$export_filename = "dadosGPX_".$filtroid."_".substr(session_id(),0,10).".gpx";


//DEFINE O QUERY
$qwheresp =   " JOIN FiltrosSpecs as fl ON pltb.EspecimenID=fl.EspecimenID WHERE fl.FiltroID=".$filtro;
$qwherepl =   " JOIN FiltrosSpecs as fl ON pltb.PlantaID=fl.PlantaID WHERE fl.FiltroID=".$filtro;

//DEFINE OS QUERIES DEPENDENDO DA OPCAO
$qqspec = "
SELECT 
'especimen'  as TYPE,
pltb.EspecimenID AS WikiID, 
IF(pltb.PlantaID>0,plspectb.PlantaTag,'') as PlantaTag,
colpessoa.Sobrenome as COLLECTOR, 
pltb.Number as NUMBER,
IF (pltb.Ano>0,CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day),'SemData') as DATA_COLETA, 
getidentidade(pltb.DetID, 0, 0, 0,0, 1) as NOME,
famtb.Familia as FAMILY, 
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 1,5)  as LATITUDE,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,5)  as LONGITUDE
FROM Especimenes AS pltb 
LEFT JOIN Plantas as plspectb ON pltb.PlantaID=plspectb.PlantaID 
LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID 
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID";

$qqpl = "
SELECT 
'planta'  as TYPE,
pltb.PlantaID AS WikiID, 
pltb.PlantaTag as PlantaTag,
'' as COLLECTOR, 
'' as NUMBER,
'' as DATA_COLETA, 
getidentidade(pltb.DetID, 0, 0, 0,0, 1) as NOME,
famtb.Familia as FAMILY, 
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, 0, 0,0, 1,5)  as LATITUDE,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, 0, 0,0, 0,5)  as LONGITUDE
FROM Plantas AS pltb
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID";

//ESPECIMENES
if ($oque==1) { 
	$sql = $qqspec.$qwheresp;
	$tocount = "SELECT * FROM Especimenes as pltb ".$qwheresp;
}
//PLANTAS
if ($oque==2) {  
	$sql = $qqpl.$qwherepl;
	$tocount = "SELECT * FROM Plantas as pltb ".$qwherepl;
}
//AMBOS
if ($oque==3) {  
	$sql = "SELECT * FROM ((".$qqspec.$qwheresp.") UNION (".$qqpl.$qwherepl.")) as newtb";
	$tocount = $sql;
}

//CONTA O NUMERO DE REGISTROS
$rz = mysql_query($tocount,$conn);
$nregistros = mysql_numrows($rz);

//CRIA TABELA TEMPORARIA
$qqz = "DROP TABLE `temp_exportGPXdt.".substr(session_id(),0,10)."`";
mysql_query($qqz,$conn);
$qqz = "CREATE TABLE `temp_exportGPXdt.".substr(session_id(),0,10)."` ".$sql;
$foi = mysql_query($qqz,$conn);

if ($foi)  {
$string = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<gpx version=\"1.0\">";
$idx = 1;
$sql = "SELECT * FROM `temp_exportGPXdt.".substr(session_id(),0,10)."` ORDER BY NOME";
$res = mysql_query($sql,$conn);
$nregistros = mysql_numrows($res);
while($row = mysql_fetch_assoc($res)) {
	if ($row['FAMILY']==$row['NOME']) {
		$taxa = $row['FAMILY']; 
	} else {
		$taxa = strtoupper(substr($row['FAMILY'],0,4))." ".$row['NOME'];
	}
if ($row['TYPE']=='especimen') {
	$nome = $row['COLLECTOR']." ".$row['NUMBER']." ".$taxa;
} else {
	$nome = $row['PlantaTag']." ".$taxa;
}
$string .= "
<wpt lat=\"".$row['LATITUDE']."\" lon=\"".$row['LONGITUDE']."\">
<name>".$nome."</name>
</wpt>
";
	$perc = ($idx/$nregistros)*100;
	$qnu = "UPDATE `temp_exportGPX.".substr(session_id(),0,10)."` SET percentage=".$perc; 
	mysql_query($qnu,$conn);
	session_write_close();
	$idx = $idx+1;
}
$string .= "
</gpx>";
	$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
	fwrite($fh, $string);
	fclose($fh);
	$message = 'CONCLUIDO';
} 
else {
	$message = 'NADA ENCONTRADO PARA EXPORTAR';
}
$qnu = "UPDATE `temp_exportGPX.".substr(session_id(),0,10)."` SET percentage=100"; 
//echo $qnu."<br >";
mysql_query($qnu,$conn);
echo $message;
session_write_close();
?>