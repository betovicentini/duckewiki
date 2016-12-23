<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

//Start session
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
//include "functions/MyPhpFunctions.php";
//include_once("functions/class.Numerical.php") ;

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

$progresstable = "temp_exportplantas".substr(session_id(),0,10);
unlink("temp/".$export_filename);
unlink("temp/".$export_filename_metadados);


unset($_SESSION['metadados']);
unset($metadados);
unset($_SESSION['qq']);

$sql = "SELECT 
tr.TraitID as WikiTraitID,
pltb.PlantaID as WikiPlantaID,
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
''  as Stem,
tr.TraitName as VarName,
IF (tr.TraitTipo LIKE '%Categ%',pegacategorias(moni.TraitVariation),
IF (tr.TraitID='".$daptraitid."',mudaunidade(moni.TraitVariation,tr.TraitTipo,moni.TraitUnit,'mm'), moni.TraitVariation)) AS dbhORvar,
IF (tr.TraitID='".$daptraitid."','mm',moni.TraitUnit) as dbhORvar_unit,
moni.DataObs as dbhORvar_date,
Censos.CensoNome
FROM Monitoramento as moni JOIN Traits as tr ON tr.TraitID=moni.TraitID JOIN Plantas AS pltb ON moni.PlantaID=pltb.PlantaID JOIN Censos ON Censos.CensoID=moni.CensoID ";
//$qwhere = " WHERE moni.TraitID=".$daptraitid." AND (moni.CensoID='".$censos[0]."' ";
$qwhere = " WHERE (moni.CensoID='".$censos[0]."' ";
unset($censos[0]);
$censos = array_values($censos);
foreach($censos as $ce) {
	$qwhere .= " OR moni.CensoID='".$ce."'";
}
$qwhere .= ")";

$lixo = 0;
if ($lixo==9872) {
//$qnu = "UPDATE `".$progresstable."` SET percentage=1"; 
//mysql_query($qnu);
//session_write_close();

$sql2 = "DROP TABLE `".$tempsqltb."`";
@mysql_query($sql2,$conn);

$ql = "SELECT MonitoramentoID FROM Monitoramento as moni ".$qwhere;
$resl = mysql_query($ql,$conn);
$nrz = mysql_num_rows($resl);

$stepsize = 1000;
$counter = 0;
//while($counter<=$nrz)  {
while($linha = mysql_fetch_assoc($resl )) {
		if ($counter==0) {
			$qqq = "CREATE TABLE IF NOT EXISTS ".$tempsqltb." (".$sql." WHERE MonitoramentoID=".$linha['MonitoramentoID'].")";
			$res = mysql_query($qqq,$conn);
		} else {
			$qqq = "INSERT INTO ".$tempsqltb." (".$sql." WHERE MonitoramentoID=".$linha['MonitoramentoID'].")";
			$res = mysql_query($qqq,$conn);
		}
		$porc = ($counter/$nrz)*80;
		$operc = floor($porc);
		if ($operc>=100) { $operc =80;}
		$qnu = "UPDATE `".$progresstable."` SET percentage=".$operc; 
		mysql_query($qnu,$conn);
		session_write_close();
		$counter++;
}
//session_write_close();
$sql2 = "ALTER TABLE `".$tempsqltb."`  ADD `tempid` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY  FIRST";
@mysql_query($sql2,$conn);

$qqq = "SELECT * FROM `".$tempsqltb."`";
}

$qqq = $sql.$qwhere;
$olixo=98745;
if ($olixo==98745) {
$res = mysql_query($qqq, $conn);
$nres = mysql_numrows($res);
if ($nres>0) {
	$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
	$count = mysql_num_fields($res);
	$osfields = $count;
	$message=  $nres.";".$osfields;
	$header = '';
	$ctn = $count-1;
	//$ascabecas= array();
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
				//DUPLICATE STEM IF EXISTS
				$otrid = $rsw['WikiTraitID'];
				if ($otrid==$daptraitid) {
					$dbh = $rsw['dbhORvar'];
					$osdbhs = explode(";",$dbh);
					$osdbhs = array_filter($osdbhs);
					arsort($osdbhs);
					$stem = 1;
				} else {
					$osdbh = $rsw['dbhORvar'];
					$stem = "";
				}
				foreach($osdbhs as $dbh) {
					$line = '';
					$ctn = count($rsw);
					$ij =0;
					foreach($rsw as $value) {
						$acaba = mysql_field_name($res, $ij);
						if ($acaba=='dbhORvar') {
							$value = $dbh;
						}
						if ($acaba=='Stem') {
							$value = $stem;
						}
						if(!isset($value) || $value == ""){
							$naval = "";
						    $value = '"' . $naval . '"' . "\t";
						} else{
							$value = preg_replace( "/\r|\n|\t/", " ", $value);
							$value = str_replace('"', "", $value);
							$value = str_replace('  '," ", $value);
							$value = '"' . $value . '"' . "\t";
						}
						$line .= $value;
						$ij++;
					}
					$lin = trim($line)."\n";
					fwrite($fh, $lin);
					$stem++;
				}
				$porc = ($counter/$nres)*18;
				$aperc = floor($porc)+$operc;
				if ($aperc>=100) { $aperc =95;}
				$qnu = "UPDATE `".$progresstable."` SET percentage=".$aperc; 
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
echo $message;
$qnu = "UPDATE `".$progresstable."` SET percentage=100"; 
mysql_query($qnu);
session_write_close();
} 
else {
	echo $qqq."<br >";
	session_write_close();
}

?>