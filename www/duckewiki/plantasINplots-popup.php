<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
if (!isset($_SESSION['dbname']) && isset($_GET['sessionvars'])) {
	$sss = explode("-",$_GET['sessionvars']);
	foreach($sss as $vv) {
		$vvv = explode("^",$vv);
		$_SESSION[$vvv[0]] = $vvv[1];
	}
	unset($_GET['sessionvars']);
}
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

$detid =0;
$detid = $famid+$genid+$specid+$infspecid+$detid;
if ($detid>0) {
	if ($infspecid>0) {
				$qindet = " WHERE iddet.InfraEspecieID=".$infspecid;
				$splist = 'infspid_'.$infspecid;
			} 
			else {
				if ($specid>0) {
					$qindet = " WHERE iddet.EspecieID=".$specid;
					$splist = 'specid_'.$specid;
				} 
				else {
					if ($genid>0) {
						$qindet = " WHERE iddet.GeneroID=".$genid;
						$splist = 'genid_'.$genid;
					} 
					else {
						$qindet = " WHERE iddet.FamiliaID=".$famid;
						$splist = 'famid_'.$famid;
					}
				}
	}
	//$qu = "SELECT DISTINCT gaz.GazetteerID, gaz.GazetteerTIPOtxt, gaz.Gazetteer, gaz.PathName, gaz.ParentID FROM Plantas as pl JOIN Identidade as iddet USING(DetID) JOIN Gazetteer as gaz USING(GazetteerID) ".$qindet." AND getiftreeplots(pl.GazetteerID)>0 ";
	$qu = "SELECT DISTINCT gaz.GazetteerID, gaz.Gazetteer, gaz.PathName, gaz.ParentID FROM Plantas as pl JOIN Identidade as iddet USING(DetID) JOIN Gazetteer as gaz USING(GazetteerID) ".$qindet." AND getiftreeplots(pl.GazetteerID)>0 ";
} 
elseif ($plantaid>0) {
	//$qu = "SELECT gaz.GazetteerID, gaz.GazetteerTIPOtxt, gaz.Gazetteer, gaz.PathName, gaz.ParentID FROM Plantas as pl JOIN Identidade as iddet USING(DetID) JOIN Gazetteer as gaz USING(GazetteerID) WHERE PlantaID='".$plantaid."' AND getiftreeplots(pl.GazetteerID)>0 ";
	$qu = "SELECT gaz.GazetteerID, gaz.Gazetteer, gaz.PathName, gaz.ParentID FROM Plantas as pl JOIN Identidade as iddet USING(DetID) JOIN Gazetteer as gaz USING(GazetteerID) WHERE PlantaID='".$plantaid."' AND getiftreeplots(pl.GazetteerID)>0 ";
}

$rz = mysql_query($qu,$conn);
$parids = array();
while ($row = mysql_fetch_assoc($rz)) {
	$tn = $row['ParentID'];
	$qt = "SELECT * FROM Gazetteer WHERE getiftreeplots(GazetteerID)>0 AND GazetteerID='".$tn."'";
	$nt = mysql_query($qt,$conn);
	$ntn = mysql_numrows($nt);
	if ($ntn>0) {
		$parids[] = $tn;
	}
}
	$parids = array_unique($parids);
	if (count($parids)>0) {
	$qt = "SELECT * FROM Gazetteer WHERE GazetteerID=".$parids[0];
	$rzz = mysql_query($qt,$conn);
	$rw = mysql_fetch_assoc($rzz);
	$gzid = $rw['GazetteerID'];
} 
	else {
		$quq = $qu." LIMIT 0,1";
		$rzz = mysql_query($quq,$conn);
		$rw = mysql_fetch_assoc($rzz);
		$gzid = $rw['GazetteerID'];
	}



if ($gzid>0) {
	$body = "";
}

$menu = FALSE;
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array(
"<script type='text/javascript' src='javascript/my_mapimg_functions.js'></script>",
"<script type='text/javascript'>
	changemap('".$splist."','".$gzid."','mappopcontainer');
	changemapimgform('".$gzid."','bottompanelpop','mappopcontainer');
</script>"
);
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<div id=\"topanelpop\">
<table cellpadding='3'>
  <tr>
    <td style=\"color: #990000; font: italic 1em verdana;\">$titulo</td>
  </tr>
  <tr>
    <td>
      <select name='gazfilter' onchange=\"changemap('".$splist."',this.value,'mappopcontainer');changemapimgform(this.value,'bottompanelpop','mappopcontainer'); reloadImg('spectbid'); reloadImg('dhbdivimgid');\" >
        <option value='' >Selecione uma parcela</option>";
        $pid = 0;
		foreach ($parids as $vv) {
			$qt = "SELECT * FROM Gazetteer WHERE GazetteerID=".$vv;
			$rzz = mysql_query($qt,$conn);
			$rw = mysql_fetch_assoc($rzz);
    		//$tn = $rw['GazetteerTIPOtxt']." ".$rw['Gazetteer'];
    		$tn = $rw['Gazetteer'];
			$tz = str_replace($tn,'',$rw['PathName']);
			if ($pid==0) { $cl = 'selected="selected"'; } else { $cl='';}
			$pid++;
			echo "
        <option ".$cl." value=\"".$rw['GazetteerID']."\" >".$rw['Gazetteer']." [".$tz."]</option>";
        //<option $cl value=\"".$rw['GazetteerID']."\">".$rw['GazetteerTIPOtxt']." ".$rw['Gazetteer']." [".$tz."]</option>";
		}    
		$rz = mysql_query($qu,$conn);
		$i = 1;
		while ($row = mysql_fetch_assoc($rz)) {
			//$tn = $row['GazetteerTIPOtxt']." ".$row['Gazetteer'];
			$tn = $row['Gazetteer'];
			$tz = str_replace($tn,'',$row['PathName']);
			if ($i==1 && $pid==0) { $cl = 'selected="selected"'; } else { $cl='';}
			$i++;
			echo "
        <option ".$cl." value=\"".$row['GazetteerID']."\">".$row['Gazetteer']." [".$tz."]</option>";
        //<option $cl value=\"".$row['GazetteerID']."\">".$row['GazetteerTIPOtxt']." ".$row['Gazetteer']." [".$tz."]</option>";
		}
echo "
      </select>
  </td>
</tr>
</table>
</div>

<div id='mapleftpanel'>
  <div id=\"mappopcontainer\"></div>
  <div id=\"bottompanelpop\"></div>
</div>

<div id='maprightpanel'>
  <div id=\"mappoprigthlowerpanel\">
    <img id='spectbid' src=\"graph_species_table.php\"  alt=\"\" />
  </div>
  <div id=\"mappoprigthpanel\">
    <img id='dhbdivimgid' src=\"graph_species_plotDBHs.php\"  alt=\"\" />
    <input type='button' value='Update DBHs' onclick=\"reloadImg('dhbdivimgid'); reloadImg('spectbid');\" />
  </div>
</div>

<script type='text/javascript'>
	reloadImg('dhbdivimgid');
	reloadImg('spectbid');
</script>
";

$which_java = array("<script type=\"text/javascript\" src=\"javascript/myjavascripts.js\" ></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>