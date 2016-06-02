<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

//Start session
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include_once("functions/class.Numerical.php") ;

//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);


$dd = @unserialize($_SESSION['destvararray']);
//@extract($dd);
$censos = $dd['censos'];

//echopre($dd);

##DEFINE ARQUIVOS
$export_filename = "plantas_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
$tempsqltb = "temp_quickcenso".substr(session_id(),0,10);
$export_filename_metadados = "plantas_export_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_definicoesDAScolunas.csv";

unset($_SESSION['metadados']);
unset($metadados);
unset($_SESSION['qq']);

$sql = "SELECT 
pltb.PlantaTag as TreeTag, 
getidentidade(pltb.DetID,1,0,1,0,0) AS Family, 
getidentidade(pltb.DetID,1,0,0,1,0) AS Genus, 
getidentidade(pltb.DetID,1,0,0,0,1) AS Species,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARGAZ_SPEC')  as Plot_Name,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARDIMX')  as Plot_DIMx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'PARDIMY')  as Plot_DIMy,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'GAZETTEER_SPEC')  as Quadrat,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'DIMX')  as Quadrat_DIMx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'DIMY')  as Quadrat_DIMy,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'STARTX')  as Quadrat_Startx,
parcelafiels(pltb.GazetteerID, pltb.GPSPointID, 'STARTY')  as Quadrat_Starty,
pltb.X as Tree_X, 
pltb.Y as Tree_Y, 
moni.TraitUnit as DBH_unit,
moni.TraitVariation as DBH,
moni.DataObs as DBH_date,
Censos.CensoNome
FROM Monitoramento as moni JOIN Plantas AS pltb ON moni.PlantaID=pltb.PlantaID JOIN Censos ON Censos.CensoID=moni.CensoID WHERE moni.TraitID=".$daptraitid;

$qwhere = " AND (moni.CensoID='".$censos[0]."' ";
unset($censos[0]);
$censos = array_values($censos);
foreach($censos as $ce) {
	$qwhere .= " OR moni.CensoID='".$ce."'";
}
$qwhere .= ")";



unlink("temp/".$export_filename);
unlink("temp/".$export_filename_metadados);

$qnu = "UPDATE `".$progesstable."` SET percentage=1"; 
mysql_query($qnu);
session_write_close();

$sql2 = "DROP TABLE `".$tempsqltb."`";
@mysql_query($sql2,$conn);


$sql = "CREATE TABLE `".$tempsqltb."` ".$sql.$qwhere;
//echo $sql."<br />";
//$lixao==9437265;
//if ($lixao==9437265) {
$ores = mysql_query($sql,$conn);

//session_write_close();
if ($ores) {
$sql2 = "ALTER TABLE `".$tempsqltb."`  ADD `tempid` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY  FIRST";
mysql_query($sql2,$conn);

$qqq = "SELECT * FROM `".$tempsqltb."`";
//echo $qqq."<br />";
//echo $export_filename."<br />";
$res = mysql_query($qqq, $conn);
$nres = mysql_numrows($res);
if ($res>0) {
	$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
	$count = mysql_num_fields($res);
	$osfields = $count;
	$_SESSION['exportnfields'] = $count;
	$_SESSION['exportnresult'] = $nres;
	$message=  $nres.";".$osfields;
	echo $message;
	$header = '';
	$ctn = $count-1;
	for ($i = 0; $i<=$ctn; $i++){
		if ($i<($ctn)) {
			$header .=  '"'. mysql_field_name($res, $i).'"'."\t";
		} else {
			$header .=  '"'. mysql_field_name($res, $i).'"';
		}
	}
	$header .= "\n";
	fwrite($fh, $header);
	$counter = 1;
	while($rsw = mysql_fetch_assoc($res)){
				$line = '';
				foreach($rsw as $value){
					if(!isset($value) || $value == ""){
						$value = "\t";
					} else{
						//important to escape any quotes to preserve them in the data.
						$value = str_replace('"', '""', $value);
						//needed to encapsulate data in quotes because some data might be multi line.
						//the good news is that numbers remain numbers in Excel even though quoted.
						$value = '"' . $value . '"' . "\t";
					}
					$line .= $value;
				}
				$lin = trim($line)."\n";
				fwrite($fh, $lin);
				$porc = ($counter/$nres)*99;
				$perc = floor($porc);
				$qnu = "UPDATE `temp_exportplantas".substr(session_id(),0,10)."` SET percentage=".$perc; 
				mysql_query($qnu);
				session_write_close();
				$counter++;
	}
	fclose($fh);


###GERA METADADOS
//$fh = fopen("temp/".$export_filename_metadados, 'w') or die("nao foi possivel gerar o arquivo");
//$stringData = "COLUNA\tDEFINICAO"; 
//foreach ($metadados as $kk => $vv) {
//	$stringData = $stringData."\n".$vv[0]."\t".$vv[1];
//}
//fwrite($fh, $stringData);
//fclose($fh);


}

$qnu = "UPDATE `temp_exportplantas".substr(session_id(),0,10)."` SET percentage=100"; 
mysql_query($qnu);
session_write_close();
} else {
	//echo "erro".$sql;
}

?>