<?php

set_time_limit(0);

session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);
HTMLheaders($body);


echo"
<div id=\"gridbox\" style=\"width: 100%; height:500px;\"></div>
<div id=\"pagingArea\"></div>
<div style=\"position:relative; left:20px\">
<input type='button' style=\"color:#4E889C;font-weight:bold; padding: 4px;cursor:pointer;\" onclick=\"mygrid.toExcel('dhtmlxconnector/server/generate.php');\" value='Save to Excel'></div>
</div>
<script>
mygrid = new dhtmlXGridObject(\"gridbox\");
mygrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
mygrid.setHeader(\"".$ffields."\");
mygrid.setInitWidths(\"".$colw."\");
mygrid.setSkin(\"modern\");
mygrid.attachHeader(\"".$filtros."\");
mygrid.setColSorting(\"".$filtros2."\");
mygrid.init();
mygrid.adjustColumnSize(".$collist.");
mygrid.enablePaging(true,50,10,'pagingArea',true);
mygrid.setPagingSkin('bricks');
mygrid.loadXML(\"temp/".$fname."\");
</script>";

HTMLtrailers();

?>