<?php
//Start session
set_time_limit(0);
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
require_once 'TreeParser_class.php';

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 
$newicktree = $_POST['newicktree'];

$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);


$uuid = $_SESSION['userid'];
$lastname = $_SESSION['userlastname'];
$aclevel = $_SESSION['accesslevel'];

$body = '';
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
"<script type=\"text/javascript\" src=\"css/cssmenuAddOnsItemBullet.js\"></script>");
$body = "";
newheader('Plotando Habitats',$body,$which_css,$which_java,TRUE);

#create a json file from a newwick file
echo "
<table align='left' class='myformtable' cellpadding=\"5\" width='50%'>
<thead>
<tr>
<td colspan='100%' class='tabhead' >".GetLangVar('nameimportar')." Tree file in NewWick Format</td>
</tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
<td style='color: #990000; font-weight:bold' >".GetLangVar('namefile')."</td>
<td>
<form enctype='multipart/form-data' action='treejson_make.php' method='post'>
  <input type='hidden' name='imported' value='1'>
  <input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
  <textarea name='newwicktree' cols=80>".$newicktree."</textarea>
</td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td colspan='100%' align='center'><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit'>
</form>
</td>
</tr>
</tbody>
</table>";
if ($imported==1) {
	$tree = str_replace(" ","",$newwicktree);
	$tree = str_replace("("," ( ",$tree);
	$tree = str_replace(")"," ) ",$tree);
	$tree = str_replace(", "," , ",$tree);
	for($i=1;$i<=10; $i++) {
		$tree = str_replace("  ","",$tree);
	}
	//$string = create_tree($tree);
	//echo "<br>".$tree."<br>";
	$tree = $tree;
	$p = new ParensParser();
	$result = $p->parse($tree);
	//echopre($result);
	$txt = 'var json ';
	$child = count($result);
	function test_print($item, $key) {
    	echo $key." holds ".$item."<br>";
    	if ($key=='0') {
    		
    	}
	}
	$curnode = 0;
	$arr = array("JSON_HEX_QUOT", "JSON_HEX_TAG", "JSON_HEX_AMP", "JSON_HEX_APOS", "JSON_NUMERIC_CHECK", "JSON_PRETTY_PRINT", "JSON_UNESCAPED_SLASHES", "JSON_FORCE_OBJECT", "JSON_UNESCAPED_UNICODE");
	$a = $result;
	$a = json_encode($result);
	echo $a;

}	
	
HTMLtrailers();
?>