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

$qnu = "UPDATE `temp_exportnirdata.".substr(session_id(),0,10)."` SET percentage=1"; 
mysql_query($qnu);
session_write_close();

$sql1 = "(SELECT 
spec.SpectrumID AS SPECTRUM_ID,
'' AS WikiEspecimenID, 
pltb.PlantaID AS WikiPlantaID, 
pltb.PlantaTag as PlantaTAG, 
'' AS COLLECTOR,
'' AS NUMBER,
getidentidade(pltb.DetID,1,0,1,0,0) AS FAMILIA, getidentidade(pltb.DetID,1,0,0,1,0) AS GENERO, 
getidentidade(pltb.DetID,1,0,0,0,1) AS NOME,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'COUNTRY')  as COUNTRY,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'MINORAREA')  as MINORAREA, 
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'MAJORAREA')  as MAJORAREA,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'GAZETTEER')  as GAZETTEER,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'GAZETTEER_SPEC')  as GAZETTEER_SPEC,
getlatlong(pltb.Latitude,pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID,0, 0, 0, 1) AS LATITUDE,
getlatlong(pltb.Latitude,pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID,0, 0, 0, 0) AS LONGITUDE,
spec.Folha,
spec.Face,
spec.FileName
FROM NirSpectra AS spec JOIN Plantas as pltb ON pltb.PlantaID= spec.PlantaID 
JOIN FiltrosSpecs as fl ON pltb.PlantaID=fl.PlantaID WHERE fl.FiltroID=".$filtro.")"; 

$sql2 = 
"(SELECT 
spec.SpectrumID AS SPECTRUM_ID,
pltb.EspecimenID AS WikiEspecimenID, 
'' AS WikiPlantaID, 
'' AS PlantaTAG,
colpessoa.Abreviacao as COLLECTOR,
pltb.Number as NUMBER,
getidentidade(pltb.DetID,1,0,1,0,0) AS FAMILIA, getidentidade(pltb.DetID,1,0,0,1,0) AS GENERO, 
getidentidade(pltb.DetID,1,0,0,0,1) AS NOME,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'COUNTRY')  as COUNTRY,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'MINORAREA')  as MINORAREA, 
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'MAJORAREA')  as MAJORAREA,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'GAZETTEER')  as GAZETTEER,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'GAZETTEER_SPEC')  as GAZETTEER_SPEC,
getlatlong(pltb.Latitude,pltb.Longitude,  pltb.GPSPointID, pltb.GazetteerID,0, 0, 0, 1) AS LATITUDE,
getlatlong(pltb.Latitude,pltb.Longitude,  pltb.GPSPointID, pltb.GazetteerID,0, 0, 0, 0) AS LONGITUDE,
spec.Folha,
spec.Face,
spec.FileName
FROM NirSpectra AS spec JOIN Especimenes as pltb ON pltb.EspecimenID=spec.EspecimenID JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID JOIN FiltrosSpecs as fl ON pltb.EspecimenID=fl.EspecimenID WHERE fl.FiltroID=".$filtro.")"; 

$uuuuserid = $_SESSION['userid'];
$temptab = "temp_nir_export".$uuuuserid;

$qz = "DROP TABLE ".$temptab;
mysql_query($qz,$conn);

$qz = "CREATE TABLE ".$temptab." (SELECT DISTINCT newtb.* FROM (".$sql1."  UNION ".$sql2.") as newtb)";
$res = mysql_query($qz,$conn);

//$qnu = "UPDATE `temp_exportnirdata.".substr(session_id(),0,10)."` SET percentage=30"; 
//mysql_query($qnu);
//session_write_close();
		
$qz = "SELECT * FROM ".$temptab;
$res = mysql_query($qz,$conn);
$nrecs = mysql_numrows($res);

if ($nrecs>0) {

$totalsteps = $nrecs*2;

$step=0;
unset($firstheader);
while ($row = mysql_fetch_assoc($res)) {
		$fn = $row['FileName'];
		$tbn ="uploads/nir/";
		$fnn = $tbn.$fn;
		///adicionado para ter dados em desenvolvimento
		$fexiste = file_exists($fnn);
		if (!$fexiste) {
			$fnn = $tbn."nir_sample.csv";
		}
		/////
		$fop = @fopen($fnn, 'r');
		$nirdata = array();
		$hhed = array();
		while (($data = fgetcsv($fop, 10000, "\t")) !== FALSE) {
					$vv = explode(",",$data[0]);
					$wlen =  round($vv[0],2);
					$valor = round($vv[1],30);
					$wlen = "X".$wlen;
					$hhed[] = $wlen;
					$nirdata[$wlen] = $valor;
		}
		//se é o primeiro passo adiciona as colunas para os WaveNumbers
		if ($step==0) {
				$firstheader = $hhed;
				foreach($hhed as $kk) {
						$qq = "ALTER TABLE `".$temptab."` ADD COLUMN `".$kk."` DOUBLE DEFAULT 0";
						@mysql_query($qq,$conn);
				}
		} 
		else {
			// caso contrario checa se o WaveNumber já foi adicionado, senao adiciona.
			$newhhed = array_diff($hhed,$firstheader);
			if (count($newhhed)>0) {
				foreach($newhhed as $kk) {
						$qq = "ALTER TABLE `".$temptab."` ADD COLUMN `".$kk."` DOUBLE DEFAULT 0";
						@mysql_query($qq,$conn);
				}
			}
		}
	//adiciona os valores absorbancia 
	foreach($nirdata as $kk => $vv) {
			$qq = "UPDATE `".$temptab."`  SET `".$kk."`=".$vv."  WHERE SPECTRUM_ID=".$row['SPECTRUM_ID'];
			//echo $qq."<br />";
			@mysql_query($qq,$conn);
	}
	$perc = ceil(($step/$totalsteps)*99);
	$qnu = "UPDATE `temp_exportnirdata.".substr(session_id(),0,10)."` SET percentage=".$perc; 
	mysql_query($qnu);
	session_write_close();
	$step++;
}


$export_filename = "temp_nir_export".$uuuuserid.".csv";
$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");

$qz = "SELECT * FROM ".$temptab;
$res = mysql_query($qz,$conn);


$count = mysql_num_fields($res);
$header = '';
for ($i = 0; $i < $count; $i++){
	$ffil = mysql_field_name($res, $i);
	if ($i<($count-1)) {
	$header .=  '"' . $ffil . '"' . "\t";
	} else {
	$header .=  '"' . $ffil . '"';
	}
}
$header .= "\n";
fwrite($fh, $header);
while($rsw = mysql_fetch_assoc($res)){
			$line = '';
			$nff  = count($rsw);
			$nii = 1;
			foreach($rsw as $value){
				if(!isset($value) || $value == ""){
					$value = "\t";
				} 
				else {
					//important to escape any quotes to preserve them in the data.
					$value = str_replace('"', '""', $value);
					if ($nii<$nff) {
						$value = '"' . $value . '"' . "\t";
					} else {
						$value = '"' . $value . '"';
					}
				}
				$nii++;
				$line .= $value;
			}
			$lin = trim($line)."\n";
			fwrite($fh, $lin);
		$perc = floor(($step/$totalsteps)*99);
		$qnu = "UPDATE `temp_exportnirdata.".substr(session_id(),0,10)."` SET percentage=".$perc; 
		mysql_query($qnu);
		session_write_close();
		$step++;
}
		fclose($fh);
		$qnu = "UPDATE `temp_exportnirdata.".substr(session_id(),0,10)."` SET percentage=100"; 
		mysql_query($qnu);
		$message=  "100% CONCLUÍDO";
		echo $message;
		session_write_close();
} 
else {  // SE NAO HOUVER CENSO
	$message=  "NAO HA DADOS";
	echo $message;
	session_write_close();
}	

?>