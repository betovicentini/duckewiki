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

$nome = ucfirst(strtolower($nome));

PopupHeader($title,$body);

$lingua2=trim($lingua2);
if (!empty($lingua2) && $lingua2!=0 && $lingua2!=GetLangVar('nameselect')) {
	$lingua=$lingua2;
}
if (empty($nome)) {
		echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
			<tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
			if (empty($nome)) {
				echo "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenome')."</td></tr>";
			}
			echo " </table><br>";
			$erro++;
} else {
	//checar se o coletor ja esta cadastrado
	$qq = "SELECT * FROM Vernacular WHERE Vernacular='$nome'";	
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0 && $_SESSION['editando']!=1) {
			echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
			<tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro3')."</td></tr>";
			echo " </table><br>";
			$erro++;
	} else {
		$arrayofvalues = array(
			'Vernacular' => $nome,
			'Language' => $lingua,
			'Definition' => $definicao,
			'Notes' => $obs,
			'Reference' => $referencia
			);
		if ($_SESSION['editando']) {
				//compara valores antigos
				$changed = CompareOldWithNewValues('Vernacular','VernacularID',$vernacularid,$arrayofvalues,$conn);
				if ($changed>0 && !empty($changed)) { //se mudou atualiza
					CreateorUpdateTableofChanges($vernacularid,'VernacularID','Vernacular',$conn);
					$updatespecid = UpdateTable($vernacularid,$arrayofvalues,'VernacularID','Vernacular',$conn);
					if (!$updatespecid) {
						$erro++;
					} else {
						echo "<p class='success'>".GetLangVar('sucesso1')."</p>";
						unset($_SESSION['editando']);
					}
				} else { //nao mudou nada
					unset($_SESSION['editando']);
				}
		} else { //se novo
			$newspec = InsertIntoTable($arrayofvalues,'VernacularID','Vernacular',$conn);
			if (!$newspec) {
				echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
				<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td>
				<input type=button value=".GetLangVar('namefechar')." onClick=\"window.close();\"> 
				</tr>
				</table><br>
				";
				$erro++;
			} else {
				unset($_SESSION['editando']);
				echo "<table cellpadding=\"1\" width='50%' align='center' class='success'>
				<tr><td>".GetLangVar('sucesso1')."</td></tr>
				<tr><td><input type=button value=".GetLangVar('namefechar')." onClick=\"window.close();\"> 
				</table>
				";
			}
		}
	}
} 

PopupTrailers();


?>