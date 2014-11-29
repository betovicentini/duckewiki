<?php
//Start session
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
@extract($ppost);

$gget = cleangetpost($_GET,$conn);
@extract($gget);

HTMLheaders($body);

$bgi=1;
echo "<br>
<table class='myformtable' align='center' cellpadding='4' >
<thead>
<tr ><td colspan=100%>";echo GetLangVar('nameeditar')." expedito</td></tr>
</thead>
<tbody>
<form action='expedito-exec.php' method=post>";

$qq = "SELECT * FROM MetodoExpedito ORDER BY DataColeta";
$res = @mysql_query($qq,$conn);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td class='tdsmallboldright'>Ponto de amostragem</td>
  <td class='tdformnotes' >
    <select name='expeditoid' onchange='this.form.submit()'>
      <option value=''>".GetLangVar('nameselect')."</option>
      <option value=''>------------</option>
      <option value='criar'>Criar um novo ponto</option>
      <option value=''>------------</option>";

	$qq = "SELECT exp.ExpeditoID, exp.DataColeta, IF(gps.Name<>'' OR gps.Name IS NOT NULL,CONCAT('Ponto ',gps.Name,' [',gaz.PathName,' ',exp.DataColeta,']'), '') as optnome FROM MetodoExpedito as exp LEFT JOIN GPS_DATA as gps ON exp.GPSpointID=gps.PointID  LEFT JOIN Gazetteer AS gaz ON gps.GazetteerID=gaz.GazetteerID ORDER BY gaz.PathName,exp.DataColeta,gps.Name";
	$rrr = mysql_query($qq,$conn);
		while ($row = mysql_fetch_assoc($rrr)) {
			echo "
      <option value=".$row['ExpeditoID'].">".$row['DataColeta']." - ".$row['optnome']."</option>";
		}
	echo "
    </select>
    </td>
</tr>
</form>
\</tr>
</table>
<br>";

//

HTMLtrailers();

?>
