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


//variáveis GET do checklist com o taxa para selecionar habitats
$detid =0;
$detid = $famid+$genid+$specid+$infspecid+$detid;

if (!empty($tableref) && $idd>0) {
	$habitatdelocal =1;
}



$none=0;
if (($detid+$habitatdelocal)>0) {
	//prepara o query
	$qq1 = "(SELECT pltb.HabitatID,hab.PathName,hab.Habitat,habpar.Habitat as ClassName,
	localidadestring(pltb.GazetteerID,pltb.GPSPointID,0,0,0,NULL,NULL,NULL) as LOCALIDADE,
	localidade_path(pltb.GazetteerID,pltb.GPSPointID,0,0,0) as LOCALPATH
	";
	$qq1from = " FROM Plantas AS pltb JOIN Identidade as iddet USING(DetID) JOIN Habitat as hab ON hab.HabitatID=pltb.HabitatID JOIN Habitat as habpar ON hab.ParentID=habpar.HabitatID";
	$qq1where = " WHERE";
	if ($detid>0) {
	if ($infspecid>0) {
		$qq1where .= " iddet.InfraEspecieID=".$infspecid;
	} else {
		if ($specid>0) {
			$qq1where .= " iddet.EspecieID=".$specid;
		} else {
			if ($genid>0) {
				$qq1where .= " iddet.GeneroID=".$genid;
			} 
			else {
				$qq1where .= " iddet.FamiliaID=".$famid;
			}
		}
	}
	} else {
		if ($habitatdelocal==1) {
			$qq1where .= " isvalidlocalandsub(pltb.GazetteerID, pltb.GPSPointID,".$idd.", '".$tableref."')>0";
		}
	}
$qq1where .= " AND hab.HabitatTipo='local'";
	$qq1 = $qq1.$qq1from.$qq1where.")";
	$qq2 = "(SELECT spectb.HabitatID,habsp.PathName,habsp.Habitat,habparsp.Habitat as ClassName,localidadestring(spectb.GazetteerID,spectb.GPSPointID,0,0,0,NULL,NULL,NULL) as LOCALIDADE, localidade_path(spectb.GazetteerID,spectb.GPSPointID,0,0,0) as LOCALPATH";
	$qq2from = " FROM Especimenes AS spectb JOIN Identidade as iddetsp USING(DetID) JOIN Habitat as habsp ON habsp.HabitatID=spectb.HabitatID JOIN Habitat as habparsp ON habsp.ParentID=habparsp.HabitatID";
	$qq2where = " WHERE";
	if ($detid>0) {
	if ($infspecid>0) {
		$qq2where .= " iddetsp.InfraEspecieID=".$infspecid;
	} else {
		if ($specid>0) {
			$qq2where .= " iddetsp.EspecieID=".$specid;
		} else {
			if ($genid>0) {
				$qq2where .= " iddetsp.GeneroID=".$genid;
			} 
			else {
				$qq2where .= " iddetsp.FamiliaID=".$famid;
			}
		}
	}
	} else {
		if ($habitatdelocal==1) {
			$qq2where .= " isvalidlocalandsub(spectb.GazetteerID, spectb.GPSPointID,".$idd.", '".$tableref."')>0";
		}
	}
	
	$qq2where .= " AND habsp.HabitatTipo='local'";
	$qq2 = $qq2.$qq2from.$qq2where.")";
	$qqall = $qq1." UNION ".$qq2;
	$qq = "SELECT DISTINCT lastb.HabitatID,lastb.PathName,lastb.Habitat,lastb.ClassName,lastb.LOCALIDADE, lastb.LOCALPATH, gethabitat_geocoor(lastb.HabitatID,'LONGITUDE') as Longitude,gethabitat_geocoor(lastb.HabitatID,'LATITUDE') as Latitude FROM (SELECT DISTINCT jtb.HabitatID,jtb.PathName,jtb.Habitat,jtb.ClassName,jtb.LOCALIDADE,jtb.LOCALPATH FROM (".$qqall.") AS jtb) AS lastb  WHERE (ABS(gethabitat_geocoor(lastb.HabitatID,'LONGITUDE'))+ABS(gethabitat_geocoor(lastb.HabitatID,'LATITUDE')))>0 ORDER BY lastb.PathName,lastb.LOCALPATH";
	//echo $qq."<br>";

	$qqt = "SELECT COUNT(*) as nsteps FROM (SELECT DISTINCT jointb.* FROM ((SELECT pltb.HabitatID,hab.PathName,hab.Habitat,habpar.Habitat as ClassName ".$qq1from.$qq1where.") UNION (SELECT spectb.HabitatID,habsp.PathName,habsp.Habitat,habparsp.Habitat as ClassName ".$qq2from.$qq2where.")) as jointb) as lastb";
	$rz = mysql_query($qqt,$conn);
	$rwz = mysql_fetch_assoc($rz);
	$nrz = $rwz['nsteps'];
	$stepsize = 100;
	$nsteps = @ceil($nrz/$stepsize);

	if ($nrz>0) {
		$none = 1;
	} else {
		$none = 0;
	}
}
if ($none>0) {
	$taxa = getaxanamenoautor($infspecid,$specid,$genid,$famid,$conn);
	$taxa = str_replace("  "," ",$taxa);
	$taxa = str_replace(" ","_",$taxa);
	//$export_filename = "temp_habitat_".substr(session_id(),0,5).".kml";

$step=0;
$porhabitat = 'tambemnuncavaiteralgoassim';
$habitatidx = 0;
$runinids = array();
while($step<=$nsteps) {
	if ($step==0) {
		$st1 = 0;
	} 
	else {
		$st1 = $st1+$stepsize+1;
	}
	$qqq = $qq." LIMIT $st1,$stepsize";
	$res = mysql_query($qqq,$conn);
	if ($step==0) {
		$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
		$hh = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<kml xmlns=\"http://www.opengis.net/kml/2.2\">
<Document>
		<Style id=\"".$parentid."\">
	  		<IconStyle>
		    <Icon>
	    	  <href>http://www.yourwebsite.com/your_preferred_icon.png</href>
	    	</Icon>    
		  </IconStyle>
		</Style>
  <name>Habitat_".$taxa."</name>
  <description>INSTITUTO NACIONAL DE PESQUISAS DA AMAZÔNIA (INPA), Manaus, Brasil. \nArquivo gerado por ".$url." ".$_SESSION['userfirstname']." ".$_SESSION['userlastname']." em ".$_SESSION['sessiondate']."</description>
  ";
			fwrite($fh, $hh);
		} 
		else {
			$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
		}
		$tbwidth = 300;
		$titlefont = "1em";
		$txtfont = "0.8em";
		$ffidx = $habitatidx;
		$currclass = $porhabitat;

		while ($rsw = mysql_fetch_assoc($res)){
		$txt = '';
		///group markers by class
		//get current class name
		$newclass = trim($rsw['PathName']);
		//if currentclass is different from previouslcass and this is not the first time, close folder
		if ($currclass!=$newclass && $ffidx>0) {
			$txt .= "
</Folder>";
		} 
		//if current class is different from previous class, open folder for current class
		if ($currclass!=$newclass) {
			$txt .= "
<Folder>
<name>".htmlspecialchars($rsw['PathName'], ENT_QUOTES, "UTF-8")."</name>
  <open>0</open>";
  			//replace previous class with current class
			$currclass = $newclass;
		}
		$ffidx++;

if (!in_array($rsw['HabitatID'],$runinids)) {
		///list current placemark for local habitat values
//<title>".$rsw['LOCALIDADE']."</title>
		$txt .= "
	<Placemark>
	  <name>".$rsw['HabitatID']."</name>
	  <classe>".htmlspecialchars($rsw['PathName'], ENT_QUOTES, "UTF-8")."</classe>
	  <localpath>".htmlspecialchars($rsw['LOCALPATH'], ENT_QUOTES, "UTF-8")."</localpath>
	  <visibility>1</visibility>
	  <description>
	<![CDATA[
	<table width='".$tbwidth."'>
	<tr style='font-size: ".$titlefont."'><td>".$rsw['LOCALIDADE']." [id:".$rsw['HabitatID']."]</td></tr>
	<tr><td><hr></td></tr>";
	$habitat = describehabitat($rsw['HabitatID'],$img=FALSE,$conn);
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

	$quq = "SELECT HabitatVariation FROM Habitat_Variation as trv JOIN Traits as tr USING(TraitID) WHERE tr.TraitTipo='Variavel|Imagem' AND trv.HabitatID=".$rsw['HabitatID']." ORDER BY tr.TraitName";
	$ruq = mysql_query($quq,$conn);
	$nruq = mysql_numrows($ruq);
	if ($nruq>0) {
	$txt .= "
	<tr ><td><hr></td></tr>
	<tr><td>
	<table style='border: 0;' align='center' cellpadding='10'>";
	while ($ruqw = mysql_fetch_assoc($ruq)) {
		$imgs = explode(";",$ruqw['HabitatVariation']);
		foreach ($imgs as $vimg) {
			$vimg = $vimg+0;
			$qusq = "SELECT FileName FROM Imagens WHERE ImageID=".$vimg;
			$rusq = mysql_query($qusq,$conn);
			$rusqw = mysql_fetch_assoc($rusq);
			$tutx = "
	<tr><td><a href=\"".$urlbig.$rusqw['FileName']."\"><img src=\"".$url.$rusqw['FileName']."\" width=200></a><br /></td></tr>";
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
	</table>
	]]>
	  </description>
	  <styleUrl>#".$parentid."</styleUrl>
	  <Point>
	<coordinates>".$rsw['Longitude'].",".$rsw['Latitude'].",0</coordinates>
	  </Point>
	</Placemark>";
		fwrite($fh, $txt);
		$ll = $rsw['Longitude']+0;
		if (abs($ll)>0) {
			if (!isset($lati)) {
				$lati = $rsw['Latitude'];
			} else {
				$lati = $lati.";".$rsw['Latitude'];
			}
			if (!isset($llong)) {
				$llong = $rsw['Longitude'];
			} else {
				$llong = $llong.";".$rsw['Longitude'];
			}
		}
	}
	$runinids[] = $rsw['HabitatID'];


	}
	/////////////
	fclose($fh);
	$porhabitat = $currclass;
	$habitatidx = $ffidx;
		
	$st1 = $st1-1;
	$step = $step+1;
	$perc = ceil(($step/$nsteps)*100);
	if ($perc<100) {
		$qnu = "UPDATE `temp_plothabitat_".substr(session_id(),0,5)."` SET percentage=".$perc; 
		mysql_query($qnu, $conn);
		session_write_close();
	}
} 


$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
$txt = "
</Folder>
</Document>
</kml>";
fwrite($fh,$txt);
fclose($fh);

$ll = explode(";",$lati);
$llo = explode(";",$llong);

$fim = array();
$fim[] = $ll;
$fim[] = $llo;
$fim[] = $export_filename;

//echopre($fim);
//$yy = array_sum($ll)/count($ll);
//$xx = array_sum($llo)/count($llo);
	$fh = fopen("temp/temp".$export_filename, 'w');
	$stringData = "<?php
";
	$stringData .= "\$lati = \"".$lati."\";
";
	$stringData .= "\$llongi = \"".$llong."\";
";
	$stringData .= " 
?>";
	fwrite($fh, $stringData);
	fclose($fh);
	//$fim = $lati."|".$llong."|".$export_filename;
	//$_SESSION['temp_habitatplot'] = serialize($fim);
	$qnu = "UPDATE `temp_plothabitat_".substr(session_id(),0,5)."` SET percentage=100"; 
	mysql_query($qnu, $conn);
	session_write_close();
	echo 'OK';
	session_write_close();
} else {
	echo "NADA";
	session_write_close();
}

?>
