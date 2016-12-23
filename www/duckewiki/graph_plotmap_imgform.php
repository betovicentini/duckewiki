<?php
session_start();
//Check whether the session variable
include "functions/databaseSettings_small.php";
include $relativepathtoroot.$databaseconnection;
include "functions/MyPhpFunctions.php";
require_once ("javascript/jpgraph/src/jpgraph.php");
require_once ("javascript/jpgraph/src/jpgraph_scatter.php");

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
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);
$qq = "SELECT Imagens.FileName FROM Gazetteer LEFT JOIN Imagens  USING(GazetteerID) WHERE GazetteerID='".$gazetteerid."'";
$res = mysql_query($qq,$conn);
$nr = mysql_numrows($res);
//echo $qq."<br>";
if ($nr>1) {
echo "
<table cellpadding='5'>
  <tr>
    <td>
    <form>
      <select name='imgfile' onchange=\"changemapimage(this.value,'".$containerid."');\" >
        <option value=''>Mude o mapa de fundo</option>";
		while ($row = mysql_fetch_assoc($res)) {
			echo "
        <option $cl value=\"".$row['FileName']."\">".$row['FileName']."</option>";
		}
echo "
      </select>
    </form>
  </td>
</tr>
</table>";
}

?>
