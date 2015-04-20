<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$ispopup = 1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
FazHeader($title,$body,$which_css,$which_java,$menu);

$showinvalid=True;

$level = GetLangVar('namefamily');

$destlevel= trim($ppost['destleveltag']);
if ($destlevel=='familia') {
	$famid=trim($ppost['destid']);
	$rr = getfamilies($famid,$conn,$showinvalid);
	$famid = $rr[1];
	//echo $famid;
	if (empty($famid)) {
		$level = GetLangVar('namefamily');
	} else {
		$level = GetLangVar('namegenus');
	}
} else {
	if ($destlevel=='genero') {
		$genusid=$ppost['destid'];
		$qq = "SELECT * FROM Tax_Generos WHERE GeneroID='$genusid'";
		$rr = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($rr);
		$famid = $row['FamiliaID'];
			$level = GetLangVar('namespecies');


	} elseif ($destlevel=='especie') {
		$speciesid=$ppost['destid'];
		$qq = "SELECT * FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE EspecieID='$speciesid'";
		$rr = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($rr);
		$genusid = $row['GeneroID'];
		$famid = $row['FamiliaID'];
		$level = GetLangVar('nameinfraspecies');
	}
}

echo "<br />
<table align='center' class='tableform'>
<tr><td colspan='3'>
<table  align='center' border=0 cellpadding=\"5\" cellspacing=\"0\" width='100%'>
<tr class='tabhead'>
<td colspan='3'>
".GetLangVar('message_filtrartaxa')."
</td></tr>
<tr>
<form name='famform' action=selectspeciespopup.php method='post'>
	<input type='hidden' name='destlevelvalue' value='familia' />
	<input type='hidden' name='formname' value='$formname' /> 
	<input type='hidden' name='elementname' value='$elementname' /> 
	<input type='hidden' name='tempelement' value='$tempelement' /> 

	<td >
		<select name='famid' onchange=\"javascript:selectaxa('listform','famform','destform','destlistlist','famid','destid','destlevelvalue','destleveltag');\">";
			if (empty($famid)) {
				echo "<option>".GetLangVar('namefamily')."</option>";
			} else {
				$rr = getfamilies($famid,$conn,$showinvalid);
				$row = $rr[0];
				$famid = $rr[1];
				echo "<option selected value=".$row['FamiliaID'].">".$row['Familia']."</option>";
			}
			echo "<option value=''>---</option>";
				echo "<option value=''>".GetLangVar('nameselect')." ".GetLangVar('namefamily')."</option>";
			echo "<option value=''>---</option>";
			$rrr = getfamilies('',$conn,$showinvalid);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value=".$row['FamiliaID'].">".$row['Familia']."</option>";
			}
echo "</select>
</td>
</form>
<form name='genusform' action=selectspeciespopup.php method='post'>
	    <input type='hidden' name='destlevelvalue' value='genero' />
		<input type='hidden' name='famid' value='$famid' /> 
		<input type='hidden' name='formname' value='$formname' /> 
		<input type='hidden' name='elementname' value='$elementname' /> 
		<input type='hidden' name='tempelement' value='$tempelement' /> 

	<td >
		<select name='genusid' onchange=\"javascript:selectaxa('listform','genusform','destform','destlistlist','genusid','destid','destlevelvalue','destleveltag');\">";
			if (empty($genusid)) {
				echo "<option>".GetLangVar('namegenus')."</option>";
			} else {
				$rr = getgenera($genusid,$famid,$conn,$showinvalid);
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['GeneroID'].">".$row['Genero']."</option>";
			}
			$rrr = getgenera('',$famid,$conn,$showinvalid);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value=".$row['GeneroID'].">".$row['Genero']."</option>";
			}
echo "</select>
</td>
</form>
<form name='speciesform' action=selectspeciespopup.php method='post'>
	<input type='hidden' name='famid' value='$famid' /> 
	<input type='hidden' name='genusid' value='$genusid' /> 
	<input type='hidden' name='formname' value='$formname' /> 
	<input type='hidden' name='elementname' value='$elementname' />
	<input type='hidden' name='tempelement' value='$tempelement' /> 

	<input type='hidden' name='destlevelvalue' value='especie' />
	<td>
		<select name='speciesid' onchange=\"javascript:selectaxa('listform','speciesform','destform','destlistlist','speciesid','destid','destlevelvalue','destleveltag');\">";
			if (empty($speciesid)) {
				echo "<option>".GetLangVar('namespecies')."</option>";
			} else {
				$rr = getspecies($speciesid,$genusid,$conn,$showinvalid);
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['EspecieID'].">".$row['Especie']."</option>";
			}
			$rrr = getspecies('',$genusid,$conn,$showinvalid);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value=".$row['EspecieID'].">".$row['Especie']."</option>";
			}
echo "</select>
	</td>
</form>
</tr>
</table>
</td></tr>
<tr><td>
<table align='center' cellpadding=\"5\" width='100%'>
<tr class='tabhead'><td colspan='3'>
".GetLangVar('nameselect')." ".GetLangVar('nametaxa')."
</td></tr>
<tr class='tabsubhead'>
<td width=150>$level ".GetLangVar('namedisponivel')."</td>
<td width=20>&nbsp;</td>
<td width=150>".GetLangVar('nameselecionado')."</td>
</tr>
<tr>
<form method='post' name='listform'>
	<input type='hidden' name='formname' value='$formname' /> 
	<input type='hidden' name='elementname' value='$elementname' />
	<input type='hidden' name='tempelement' value='$tempelement' /> 

<td>
<select name=srcList multiple size=10>";
if (empty($famid)) {
	$rrr = getfamilies('',$conn,$showinvalid);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value='familia|".$row['FamiliaID']."'>".$row['Familia']."</option>";
	}
}



if (empty($genusid) && !empty($famid)) {
	$rrr = getgenera('',$famid,$conn,$showinvalid);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value='genero|".$row['GeneroID']."'>".$row['Genero']."</option>";
	}
}
if (empty($speciesid) && !empty($genusid)) {
	$rrr = getspecies('',$genusid,$conn,$showinvalid);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value='especie|".$row['EspecieID']."'>".$row['Genero']." ".$row['Especie']."</option>";
	}
}
if (empty($infraspid) && !empty($speciesid)) {
				$rrr = getinfraspecies('',$speciesid,$conn,$showinvalid);
				$nr = mysql_numrows($rrr);
				if ($nr>0) {
					while ($row = mysql_fetch_assoc($rrr)) {
						echo "<option value='infraspecies|".$row['InfraEspecieID']."'>".$row['Genero']." ".$row['Especie']." ".$row['InfraEspecieNivel']." ".$row['InfraEspecie']."</option>";
					}
				} else {
					echo "<option>".GetLangVar('namemissing')."</option>";
				}
}
echo "</select>
</td>
<td width='30' align='center'>
<input type='button' value=' >> ' class='breset' onClick=\"javascript:addSrcToDestList('listform');\" />
<br /><br />
<input type='button' value=' << ' class='breset' onclick=\"javascript:deleteFromDestList('listform');\" />
</td>
<td>
	<select name=destList multiple size=10>";
	if (!empty($destlistlist)) {
			$arraylist = explode(";",$destlistlist);
			foreach ($arraylist as $key => $value) {
				$dado = explode("|",$value);
				if (trim($dado[0])=='familia') {
					$rr = getfamilies($dado[1],$conn,$showinvalid);
					$row = $rr[2];
					echo "<option selected value='familia|".$row['FamiliaID']."'>".$row['Familia']."</option>";
				}
				if (trim($dado[0])=='genero') {
					$rr = getgenera($dado[1],$famid,$conn,$showinvalid);
					$row = mysql_fetch_assoc($rr);
					echo "<option selected value='genero|".$row['GeneroID']."'>".$row['Genero']."</option>";
				}
				if (trim($dado[0])=='especie') {
					$rr = getspecies($dado[1],$genusid,$conn,$showinvalid);
					$row = mysql_fetch_assoc($rr);
					echo "<option selected value='especie|".$row['EspecieID']."'>".$row['Genero']." ".$row['Especie']."</option>";
				}
				if (trim($dado[0])=='infraspecies') {
					$rr = getinfraspecies($dado[1],$speciesid,$conn,$showinvalid);
					$row = mysql_fetch_assoc($rr);
						echo "<option value='infraspecies|".$row['InfraEspecieID']."'>".$row['Genero']." ".$row['Especie']." ".$row['InfraEspecieNivel']." ".$row['InfraEspecie']."</option>";
				}
			}
	}

echo "</select>
</td>
</tr>
<tr>
<td colspan='3' align='center'><br />
<input type='button' value=".GetLangVar('nameenviar')." class='bsubmit' 
onclick =\"javascript:
MyArray('listform','".$formname."','".$elementname."','specieslist');\" />
</td>
</form>
<!---
	<form method='post' action=selectspeciespopup.php method='post'>
	<input type='hidden' name='formname' value='$formname' /> 
	<input type='hidden' name='elementname' value='$elementname' />  
	<input type='hidden' name='tempelement' value='$tempelement' /> 

	<td colspan=1 align='right'><br />
	<input type='submit' value=".GetLangVar('namereset')." class='breset' onClick =\"javascript:cleanDestList('listform');\" />
	</td>
--->
</tr>
</table>
</form>
</td>
</tr>
</table>
<form name='destform' method='post' action='selectspeciespopup.php'>
	<input type='hidden' name='destlistlist' value='$destlistlist' /> 
	<input type='hidden' name='destid' value='$destid' />
	<input type='hidden' name='destleveltag' value='$destleveltag' />
	<input type='hidden' name='formname' value='$formname' /> 
	<input type='hidden' name='elementname' value='$elementname' /> 
	<input type='hidden' name='tempelement' value='$tempelement' /> 

</form>
";

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>

