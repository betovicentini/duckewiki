<?php
foreach ($createfiles as $parentid) {
	$qq = " SELECT pltb.PathName,pltb.Habitat FROM Habitat as pltb WHERE pltb.HabitatID='".$parentid."'";
	$rz = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rz);
	$mytitle = $row['PathName'];
	$filename = "habitatmap_".$parentid.".json";
	$relativepath = 'temp/';

	$latcenter = array();
	$longcenter = array();

	@unlink("temp/".$filename);
	$habitats = array();
	$qq = " SELECT pltb.HabitatID FROM Habitat as pltb WHERE pltb.ParentID='".$parentid."'";
	$rz = mysql_query($qq,$conn);
	while ($row = mysql_fetch_assoc($rz)) {
		$habitats[] = $row['HabitatID'];
		$qq = "SELECT hab.HabitatID FROM Habitat as hab WHERE hab.ParentID='".$row['HabitatID']."' ORDER BY hab.PathName";
		$rzz = @mysql_query($qq,$conn);
		$nrzz = @mysql_numrows($rzz);
		if ($nrzz>0) {
			while ($rw = mysql_fetch_assoc($rzz)) {
				$habitats[]  = $rw['HabitatID'];
				$qq = "SELECT hab.HabitatID FROM Habitat as hab WHERE hab.ParentID='".$rw['HabitatID']."' ORDER BY hab.PathName";
				$res = mysql_query($qq,$conn);
				$nrr = mysql_numrows($res);
				if ($nrr>0) {
					while ($rew = mysql_fetch_assoc($res)) {
						$habitats[] = $rew['HabitatID'];
					}
				}
			}
		}
	}
	$habitats = array_unique($habitats);
	$qq = "SELECT 
    pltb.HabitatID,
    pltb.PathName,
    localidadestring(pltb.LocalityID,pltb.GPSPointID,0,0,0,NULL,NULL,NULL) as Localidade,
    IF (pltb.LocalityID>0, getlocalityFields(pltb.LocalityID, 'LATITUDE'),IF (pltb.GPSPointID>0, getGPSlocalityFields(pltb.GPSPointID, 'LATITUDE'), NULL)) AS Latitude,
    IF (pltb.LocalityID>0, getlocalityFields(pltb.LocalityID, 'LONGITUDE'),IF (pltb.GPSPointID>0, getGPSlocalityFields(pltb.GPSPointID, 'LONGITUDE'), NULL)) AS Longitude,
    IF (pltb.LocalityID>0, getlocalityFields(pltb.LocalityID, 'ALTITUDE'),IF (pltb.GPSPointID>0, getGPSlocalityFields(pltb.GPSPointID, 'ALTITUDE'), NULL)) AS Altitude
    FROM Habitat as pltb
    WHERE pltb.HabitatTipo='Local' AND (pltb.LocalityID+pltb.GPSPointID)>0 AND (";
	$idx =0;
	foreach ($habitats as $vv) {
		if ($idx==0) {
			$qq .= " pltb.HabitatID='".$vv."'";
		} else {
			$qq .= " OR pltb.HabitatID='".$vv."'";
		}
		$idx++;
	}
	$qq .= ") ORDER BY pltb.PathName";
	$res = mysql_query($qq,$conn);
	$nres = @mysql_numrows($res);

	if ($nres>0) {
		$l=0;
		$texttowrite = "var data = { \"count\": ".$nres.",\n \"mypoints\": [";
		WriteToTXTFile($filename,$texttowrite,$relativepath,$append=FALSE);
		$virg=0;
		$blngarr = array();
		$blatarr = array();
		$intCount = 0;
		$npoints = $nres;
		$titlefont = '1.1em';
		$txtfont = '0.8em';
		while ($rw = mysql_fetch_assoc($res)) {
			$results = $rw;
			$blngarr[] = $rw['Longitude'];
			$blatarr[] = $rw['Latitude'];
			//////////////////
$txt = "<table class='info-table'>
<tr style='font-size: ".$titlefont."'><td>".$mytitle."</td></tr>
<tr><td><hr></td></tr>
<tr style='font-size: ".$txtfont."'><td>".$rw['Localidade']."</td></tr>
<tr><td><hr></td></tr>";
$habitat = describehabitat($rw['HabitatID'],$img=FALSE,$conn);
$txt .= "
<tr style='font-size: ".$txtfont."'><td>".$habitat."</td></tr>";

//imagens
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
$urlbig = $url."/img/originais/";
$url = $url."/img/lowres/";

$quq = "SELECT HabitatVariation FROM Habitat_Variation as trv JOIN Traits as tr USING(TraitID) WHERE tr.TraitTipo='Variavel|Imagem' AND trv.HabitatID=".$rw['HabitatID']." ORDER BY tr.TraitName";
$ruq = mysql_query($quq,$conn);
$nruq = mysql_numrows($ruq);
if ($nruq>0) {
$txt .= "
<tr ><td><hr></td></tr>
<tr><td>
<table style='border: 0;' align='center' cellpadding='10'>";
while ($ruqw = mysql_fetch_assoc($ruq)) {
	$imgs = explode(";",$ruqw['TraitVariation']);
	foreach ($imgs as $vimg) {
		$vimg = $vimg+0;
		$qusq = "SELECT FileName FROM Imagens WHERE ImageID='".$vimg."'";
		//echo $qusq;
		$rusq = mysql_query($qusq,$conn);
		$rusqw = mysql_fetch_assoc($rusq);
		$tutx = "
<tr><td><a href='".$urlbig.$rusqw['FileName']."'><img src='".$url.$rusqw['FileName']."' width=200></a><br /></td></tr>";
		$txt .= $tutx;
	}
}
$txt .= "
</table>
<td>
</tr>
";
}
$txt .= "
</table>";
			$parr = array('InfoHTML' => $txt);
			$results = array_merge((array)$results,(array)$parr);
			/////////////////
			if ($virg>0 && $virg<$npoints) { $tt = " }\n,\n{"; }
			if ($virg==0 || $vig==$npoints) { $tt = "\n{";}
			$j=0;
			$nres = count($results)-1;
			foreach ($results as $kk => $val) {
				if (is_numeric($val)) {
					$tt = $tt." \"".$kk."\": ".$val;
				} else {
					$vv = str_replace("\"","",$val);
					$tt = $tt." \"".$kk."\": \"".$vv."\"";
				}
				if ($j<$nres) {
					$tt = $tt.", ";
				}
			$j++;
			}
			$virg++;
			WriteToTXTFile($filename,$tt,$relativepath,$append=TRUE);
		}

	//map boundaries
	$maxlat = @max($blatarr)+1;
	$maxlong = @max($blngarr)+1;

	$minlat = @min($blatarr)-2;
	$minlong = @min($blngarr)-2;


	$centerlat = ($maxlat+ $minlat)/2;
	$centerlong = ($maxlong+ $minlong)/2;


	$boundaries = array($centerlat, $maxlat, $minlat, $centerlong, $maxlong, $minlong);
	$boundaries = implode("|",$boundaries);
	$texttowrite = "}\n],\n\"boundaries\": \"".$boundaries."\"}";
	WriteToTXTFile($filename,$texttowrite,$relativepath,$append=TRUE);
	$fn = "habitat_plotlistjson.txt";
	if ($ptid==0) {
		$fnn = fopen("temp/".$fn, 'w') or die("nao foi possivel criar o arquivo");
		$ptid++;
	} else {
		$fnn = fopen("temp/".$fn, 'a') or die("nao foi possivel abrir o arquivo");
	}
	$txt = $parentid."\t".$mytitle."\t".$filename."\t".$centerlat."\t".$centerlong."\n";
	fwrite($fnn,$txt);
	$kmlfiles[] = array($parentid,$mytitle,$filename,$centerlat,$centerlong);
}
} 
fclose($fnn);
?>

