<?php
//Start session
set_time_limit(0);
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);


$uuid = $_SESSION['userid'];
$lastname = $_SESSION['userlastname'];
$aclevel = $_SESSION['accesslevel'];

$body = '';

$qq = "SELECT DISTINCT hab.HabitatID,hab.PathName,hab.Habitat FROM Habitat as hab WHERE hab.HabitatTipo='Class' ORDER BY hab.PathName";
$rz = mysql_query($qq,$conn);
$nr = mysql_numrows($rz);
$createfiles = array();
$kmlfiles = array();

if (file_exists("temp/habitat_plotlistjson.txt") && $updatefiles==FALSE) {
	$fop = @fopen("temp/habitat_plotlistjson.txt", 'r');
	$i=0;
	while (($data = fgetcsv($fop, 10000, "\t")) !== FALSE) {
			$kmlfiles[] = $data;
	}
	//echopre($kmlfiles);
}
while ($row = mysql_fetch_assoc($rz)) {
	$parid = $rz['HabitatID'];
	$fn = "habitatmap_".$parid.".json";
	if (!file_exists("temp/".$fn) || $updatefiles==TRUE) {
		$createfiles[] = $row['HabitatID'];
	}
}
if (count($createfiles)>0 || $updatefiles==TRUE) {
	if (count($kmlfiles)>0) {
		$ptid=1;
	} else {
		$ptid=0;
	}
	include "plothabitat_createjson.php";
} 
$which_css = array(
"<meta name=\"viewport\" content=\"initial-scale=1.0, user-scalable=no\" />",
"<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\"/>",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/geral.css\" />",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/cssmenu.css\" />",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/mapwindow.css\" />",
"<link href=\"http://code.google.com/apis/maps/documentation/javascript/examples/default.css\" rel=\"stylesheet\" type=\"text/css\" />"
);
$which_java = array(
"<script type=\"text/javascript\" src=\"css/cssmenuCore.js\"></script>",
"<script type=\"text/javascript\" src=\"css/cssmenuAddOns.js\"></script>",
"<script type=\"text/javascript\" src=\"css/cssmenuAddOnsItemBullet.js\"></script>",
"<script type=\"text/javascript\" src=\"http://www.google.com/jsapi\"></script>",
"<script type=\"text/javascript\" src=\"javascript/markerclusterer.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/jquery-latest.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/habitat_test.js\"></script>");

$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
if (!empty($kmlfiles[0][2])) {
$fn = "temp/".$kmlfiles[0][2];
//$body = " onload=\"habitatTeste.changedata(;\" ";
}
$body = '';
FazHeader('Plotando Habitats',$body,$which_css,$which_java,TRUE);
//<div id=\"map_canvas\" style=\"margin-left: 3%; margin-right: 3%; margin-bottom: 3%; margin-top: 3%; width: 70%; height: 93%;\"></div>

echo "
<div style=\"float:left; height: 500px; width:1000px; border:0px\">
  <div id=\"panel\" style=\"box-shadow: 0px 5px 5px 2px #888888; height: 500px; width:330px; border:0.1em solid grey;\">
    <table align='center'>
      <tr><td align='center'>Classes de Habitat</td></tr>
      <tr><td align='center'><div style=\"font-size: 0.8em; text-align: left; overflow: auto; height: 460px; width:300px; margin: 3px 3px 3px 3px; border:0.1em solid grey;\">";
foreach ($kmlfiles as $vv) {
	echo "
<div>
<input type='checkbox' id='jsonfile_".$vv[0]."' value='".$url."/temp/habitatmap_".$vv[0].".json?mycallback=?' onselect=\"javascript: habitatTeste.changedata('jsonfile_".$vv[0]."');\">&nbsp;".$vv[1]."</div>";
}
echo "
  </div></td></tr>
  </table>
  </div>
  <div id='mymap'>
    <div id=\"map_canvas\" style=\"box-shadow: 0px 5px 5px 2px #888888; margin-left: 3%; width: 600px; height:510px; float:left; border: 1px solid grey;\"></div>
  </div>
</div>

<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>