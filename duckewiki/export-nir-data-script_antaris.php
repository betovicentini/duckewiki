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

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

$qnu = "UPDATE `temp_exportnirdata.".substr(session_id(),0,10)."` SET percentage=1"; 
mysql_query($qnu);
session_write_close();

if ($checklist==1) {
	$vars = unserialize($_SESSION['exportnir'.substr(session_id(),0,10)] );
//echopre($vars);
	$wheresql = ' tagtaxanir('.($vars['famid']+0).' ,'.($vars['genid']+0).' ,'.($vars['specid']+0).' ,'.($vars['infspecid']+0).' , idd.FamiliaID ,idd.GeneroID ,idd.EspecieID ,idd.InfraEspecieID)>0 ';
} else {
	$wheresql = " pltb.FiltrosIDS LIKE '%filtroid_".$filtroid.";%'  OR pltb.FiltrosIDS LIKE '%filtroid_".$filtroid."' ";
}

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
getlatlong(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID,0, 0, 0, 1) AS LATITUDE,
getlatlong(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID,0, 0, 0, 0) AS LONGITUDE,
spec.Folha,
spec.Face,
spec.FileName
FROM NirSpectra AS spec JOIN Plantas as pltb ON pltb.PlantaID= spec.PlantaID LEFT JOIN Identidade AS idd ON idd.DetID=pltb.DetID
WHERE ".$wheresql .")";

//echo $sql1."<br >";



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
getlatlong(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID,0, 0, 0, 1) AS LATITUDE,
getlatlong(pltb.Latitude, pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID,0, 0, 0, 0) AS LONGITUDE,
spec.Folha,
spec.Face,
spec.FileName
FROM NirSpectra AS spec JOIN Especimenes as pltb ON pltb.EspecimenID=spec.EspecimenID JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID JOIN Identidade AS idd ON idd.DetID=pltb.DetID
WHERE ".$wheresql .")";

//echo $sql1."<br >";

$temptab = "temp_nir_export".substr(session_id(),0,10);
$qz = "DROP TABLE ".$temptab;
mysql_query($qz,$conn);

$qz = "CREATE TABLE ".$temptab." (SELECT DISTINCT newtb.* FROM (".$sql1."  UNION ".$sql2.") as newtb)";
$res = mysql_query($qz,$conn);

$qz = "SELECT * FROM ".$temptab;
//echo $qz;
$res = mysql_query($qz,$conn);
$nrecs = mysql_numrows($res);

if ($nrecs>0) {
//PREPARA O CABEÇALHO
$qz = "SELECT * FROM ".$temptab."  LIMIT 0,1";
$res = mysql_query($qz,$conn);
$count = mysql_num_fields($res);
$header = '';
for ($i = 0; $i < $count; $i++){
	$ffil = mysql_field_name($res, $i);
	$header .=  '"' . $ffil . '"' . "\t";
}
$row = mysql_fetch_assoc($res);
$fn = $row['FileName'];
$tbn ="uploads/nir/";
$fnn = $tbn.$fn;
$fop = @fopen($fnn, 'r');
$hhed = array();
while (($data = fgetcsv($fop, 10000, "\t")) !== FALSE) {
	$vv = explode(",",$data[0]);
	$wlen =  round($vv[0],2);
	$wlen = "X".$wlen;
	$hhed[] = $wlen;
}
fclose($fnn);
$i = 1;
$ni = count($hhed);
foreach ($hhed as $cab) {
	if ($i<$ni) {
		$header .=  '"' . $cab . '"' . "\t";
	} else {
		$header .=  '"' . $cab . '"';
	}
	$i++;
}
$header .= "\n";

$export_filename = "temp_nir_export".substr(session_id(),0,10).".csv";
$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
fwrite($fh, $header);

//AGORA ACRESCENTA OS DADOS
$qz = "SELECT * FROM ".$temptab;
$res = mysql_query($qz,$conn);
$nrecs = mysql_numrows($res);
$step=0;
while ($row = mysql_fetch_assoc($res)) {
		$fn = $row['FileName'];
		$tbn ="uploads/nir/";
		$fnn = $tbn.$fn;
		$fop = @fopen($fnn, 'r');
		$nirdata = array();
		while (($data = fgetcsv($fop, 10000, "\t")) !== FALSE) {
					$vv = explode(",",$data[0]);
					$valor = round($vv[1],30);
					$wlen =  round($vv[0],2);
					$wlen = "X".$wlen;
					$nirdata[$wlen] = $valor;
		}
		fclose($fnn);
		//JUNTA OS DADOS DAS AMOSTRAS COM OS DADOS DE ABSOBANCIA DO ARQUIVO
		$todosvalores = array_merge((array)$row,(array)$nirdata);
		
		//SALVA OS VALORES NO ARQUIVO
		$line = '';
		$nff  = count($todosvalores);
		$nii = 1;
		foreach($todosvalores as $value){
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
		$perc = floor(($step/$nrecs)*99);
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