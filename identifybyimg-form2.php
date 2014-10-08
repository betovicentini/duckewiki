<?php

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

echo "<br>
<table class='myformtable' align='center' cellpadding=\"6\">
<thead>
<tr >
<td colspan=100%>".GetLangVar('identifybyimages')."</td>
</tr>
</thead>
<form method='post' name='finalform' action='identifybyimg-exec2.php'>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
echo "<tr bgcolor = $bgcolor>
<td colspan=100%>
	<table><tr>
	<td class='tdsmallbold'>".GetLangVar('nameselect')." ".GetLangVar('namefiltro')."&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('filtroid_help'));
	echo	" onclick=\"javascript:alert('$help');\"></td>
	<td><select name='filtro'>";
		if (!empty($filtro)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "<option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
		}
			echo "<option value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "<option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}	

	echo "</select></td>
	</tr></table></td>
	</tr>
	<tr>
	<td align='center' colspan='100%'>
				<input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' >
			</td></tr>
	";

echo "</tbody></table>";
	
	
HTMLtrailers();

?>