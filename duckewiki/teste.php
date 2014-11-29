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

HTMLheaders('');

echo "<table align='center'>
		<tr>
			<td>
				<img src='icons/list-add.png' height=15 ";
					$myurl ="familia-popup.php?familiafieldid=familiaid"; 		
						echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Familia');\">
			</td>
			<td>
				<select id='familiaid' name='familiaid'>";
				echo "<option class='optselectdowlight' value=''>".GetLangVar('nameselect')."</option>";
				$qq = "SELECT * FROM Tax_Familias";
				$rrr = mysql_query($qq,$conn);
				while ($row = mysql_fetch_assoc($rrr)) {
					echo "<option value=".$row['FamiliaID'].">".$row['Familia']."</option>";
				}
		echo "</select>
		</td>
		</tr>
	</table>";

HTMLtrailers();

?>

