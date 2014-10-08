<?php
session_start();
include "functions/HeaderFooter.php";
require_once($relativepathtoroot.$databaseconnection_clean);

$parid = $_POST['parid'];
$input = strtolower($_POST['input']);
$inserido = 0;
$rr = @mysql_query("SELECT TraitName as label FROM `Traits` WHERE TraitID=".$parid."  AND LOWER(TraitName) LIKE '%".$input."%'", $res);
if ($rr) {
	$inserido++;
}
echo $inserido;
?>
