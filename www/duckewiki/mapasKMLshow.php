<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
//if(!isset($uuid) || 
//	(trim($uuid)=='')) {
//		header("location: access-denied.php");
//	exit();
//} 
$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);


$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);
echo "
<!DOCTYPE html>
<html>
<head>
<meta name=\"viewport\" content=\"initial-scale=1.0, user-scalable=no\" />
<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\"/>
<title>Mape</title>
<link href=\"http://code.google.com/apis/maps/documentation/javascript/examples/default.css\" rel=\"stylesheet\" type=\"text/css\" />
<script type=\"text/javascript\" src=\"http://maps.google.com/maps/api/js?sensor=false\"></script>
<script type=\"text/javascript\">
function initialize() { 
  var myOptions = {
	center: new google.maps.LatLng(".$latcenter.", ".$longcenter."),
  	zoom: 5,
    mapTypeId: google.maps.MapTypeId.TERRAIN
  }

  var map = new google.maps.Map(document.getElementById(\"map_canvas\"), myOptions);
  var ctaLayer = new google.maps.KmlLayer('".$url."/temp/".$filename."',{preserveViewport:true});
  ctaLayer.setMap(map);
}
</script>
</head>
<body onload=\"initialize()\">
  <div id=\"map_canvas\" style=\"box-shadow: 5px 5px 2px #888888; margin-left: 3%; margin-right: 3%; margin-bottom: 3%; margin-top: 3%; width: 93%; height: 93%;\"></div>
</body>
</html>";
?>