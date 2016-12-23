<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css'>",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
newheader($title,$body,$which_css,$which_java,$menu);

//////variaveis deste formulario
if (!empty($infraspid)) {
		$toedit = GetLangVar('nameinfraspecies'); 
} else {
	if (!empty($speciesid)) {
			$toedit = GetLangVar('namespecies');
		} else {
			if (!empty($genusid)) {
				$toedit = GetLangVar('namegenus');
			} else {	
				if (!empty($famid)) {
					$toedit = GetLangVar('namefamily');
				} else {
					header("location: taxa-form.php");
					exit();		
				}
			}
		}
} 

//SELECIONA VALORES ANTIGOS
if (!isset($editing)) {
if ($toedit==GetLangVar('namespecies')) {
	$qq = "SELECT Tax_Especies.*,Tax_Generos.Genero,Tax_Generos.FamiliaID FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE EspecieID='$speciesid'";
}
if ($toedit==GetLangVar('nameinfraspecies')) {
	$qq = "SELECT Tax_InfraEspecies.*,Tax_Especies.GeneroID,Tax_Especies.EspecieID,Tax_Generos.Genero,Tax_Generos.FamiliaID FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE InfraEspecieID='$infraspid'";
}

if ($toedit==GetLangVar('namegenus')) {
	$qq = "SELECT * FROM Tax_Generos WHERE GeneroID='$genusid'";
}

if ($toedit==GetLangVar('namefamily')) {
	$qq = "SELECT * FROM Tax_Familias WHERE FamiliaID='$famid'";
}

$query = mysql_query($qq,$conn);
$row = mysql_fetch_assoc($query);
$famid = $row['FamiliaID'];
$genusid = $row['GeneroID'];
$genus = $row['Genero'];
$nomevalido = $row['Valid'];

if ($toedit==GetLangVar('namespecies')) {
	$spnome = $row['Especie'];
	$autor = $row['EspecieAutor'];
	
	$qq = "SELECT *  FROM Vernacular WHERE TaxonomyIDS LIKE '%especie|".$speciesid.";%' OR `TaxonomyIDS` LIKE '%especie|".$speciesid."'";
	$rrr = mysql_query($qq,$conn);
	$nrr = mysql_numrows($rrr);
	if ($nrr>0) {
		while ($rrw = mysql_fetch_assoc($rrr)) {	
			if (empty($vernacularvalue)) {$vernacularvalue = $rrw['VernacularID'];} else {
				$vernacularvalue = $vernacularvalue.";".$rrw['VernacularID'];
			}
			
		}
	}	
}
if ($toedit==GetLangVar('nameinfraspecies')) {
	$spnome = $row['InfraEspecie'];
	$autor = $row['InfraEspecieAutor'];
	$subvar = $row['InfraEspecieNivel'];
	$qq = "SELECT *  FROM Vernacular WHERE TaxonomyIDS LIKE '%infraespecie|".$infraspid.";%' OR `TaxonomyIDS` LIKE '%infraespecie|".$infraspid."'";
	$rrr = mysql_query($qq,$conn);
	$nrr = mysql_numrows($rrr);
	if ($nrr>0) {
		while ($rrw = mysql_fetch_assoc($rrr)) {	
			if (empty($vernacularvalue)) {$vernacularvalue = $rrw['VernacularID'];} else {
				$vernacularvalue = $vernacularvalue.";".$rrw['VernacularID'];
			}
			
		}
	}	
}
if ($toedit==GetLangVar('namegenus')) {
	$spnome = $row['Genero'];
	$autor = $row['GeneroAutor'];

	$qq = "SELECT *  FROM Vernacular WHERE TaxonomyIDS LIKE '%genero|".$genusid.";%' OR `TaxonomyIDS` LIKE '%genero|".$genusid."'";
	$rrr = mysql_query($qq,$conn);
	$nrr = mysql_numrows($rrr);
	if ($nrr>0) {
		while ($rrw = mysql_fetch_assoc($rrr)) {	
			if (empty($vernacularvalue)) {$vernacularvalue = $rrw['VernacularID'];} else {
				$vernacularvalue = $vernacularvalue.";".$rrw['VernacularID'];
			}
			
		}
	}	
}
if ($toedit==GetLangVar('namefamily')) {
	$spnome = $row['Familia'];
	$autor = $row['FamiliaAutor'];
	$qq = "SELECT *  FROM Vernacular WHERE TaxonomyIDS LIKE '%familia|".$famid.";%' OR `TaxonomyIDS` LIKE '%familia|".$famid."'";
	$rrr = mysql_query($qq,$conn);
	$nrr = mysql_numrows($rrr);
	if ($nrr>0) {
		while ($rrw = mysql_fetch_assoc($rrr)) {	
			if (empty($vernacularvalue)) {$vernacularvalue = $rrw['VernacularID'];} else {
				$vernacularvalue = $vernacularvalue.";".$rrw['VernacularID'];
			}
			
		}
	}	
}
$basionym = $row['Basionym'];
$basionymautor = $row['BasionymAutor'];
$pubrevista = $row['PubRevista'];
$pubvolume = $row['PubVolume'];
$pubano = $row['PubAno'];
$sinonimos = $row['Sinonimos'];
$geodist = $row['GeoDistribution'];
$notas = $row['Notas'];
} //if editing

$specieslist = describetaxacomposition($sinonimos,$conn,$includeheadings=TRUE);

if (!empty($vernacularvalue)) {
	$vernaculartxt = describevernacular($vernacularvalue,$conn);
}

echo "<br>
<form name=specieslistform action=taxaregister-exec.php method='post'>
	<input type='hidden' name='famid' value='$famid'>
	<input type='hidden' name='speciesid' value='$speciesid'>
	<input type='hidden' name='infraspid' value='$infraspid'>
	<input type='hidden' name='genusid' value='$genusid'>
	<input type='hidden' name='toedit' value='$toedit'>
	<input type='hidden' name='editing' value='1'>

<table class='myformtable' align='left' width='70%'>
<thead>
<tr >
<td colspan=4 class='tabhead'>".GetLangVar('nameeditar')."&nbsp;".mb_strtolower($toedit)." &nbsp;<i>$spnome</i></td>
</tr></thead><tbody>";
if ($toedit!=GetLangVar('namefamily')) {
echo "<tr>";
if ($toedit==GetLangVar('namegenus')) {
	echo "
	<td class='tdsmallbold' align='right'>".GetLangVar('namefamily')." </td>
	<td colspan=3><table><tr><td>
	<select name='famid' >";
	$rr = getfamilies($famid,$conn,$showinvalid=TRUE);
	$row = $rr[0];
	$famid = $rr[1];
	echo "<option selected value=".$row['FamiliaID'].">".$row['Familia']."</option>";
	$rrr = getfamilies('',$conn,$showinvalid=TRUE);
	while ($row = mysql_fetch_assoc($rrr)) {
		echo "<option value=".$row['FamiliaID'].">".$row['Familia']."</option>";
	}
} 
if ($toedit==GetLangVar('namespecies')) {
	echo "
	<td class='tdsmallbold' align='right'>".GetLangVar('namegenus')."</td>
	<td colspan=3><table><tr><td>
	<select name='genusid' >";
	$rr = getgenera($genusid,$famid,$conn,$showinvalid=TRUE);
	$row = mysql_fetch_assoc($rr);
	echo "<option selected value=".$row['GeneroID'].">".$row['Genero']."</option>";
	$rrr = getgenera('',$famid,$conn,$showinvalid=TRUE);
	while ($row = mysql_fetch_assoc($rrr)) {
		echo "<option value=".$row['GeneroID'].">".$row['Genero']."</option>";
	}
	echo "</select>";
} 


if ($toedit==GetLangVar('nameinfraspecies')) {
	echo "
	<td class='selectedval' align='right'><i>$genus</i></td>
	<td colspan=3><table><tr><td>
	<select class='selectedval' name='speciesid' >";
	$rr = getspecies($speciesid,$genusid,$conn,$showinvalid=TRUE);
	$row = mysql_fetch_assoc($rr);
	echo "<option selected value=".$row['EspecieID']." >".$row['Especie']." ".$row['EspecieAutor']."</option>";
	$rrr = getspecies('',$genusid,$conn,$showinvalid=TRUE);
	while ($row = mysql_fetch_assoc($rrr)) {
		echo "<option value=".$row['EspecieID'].">".$row['Especie']." ".$row['EspecieAutor']."</option>";
	}
	echo "</select>
	</td></tr></table></td>
	</tr><tr>
	<td class='tdsmallbold' align='right'>".GetLangVar('nametipo')."</td>";
	$qq = "SELECT DISTINCT InfraEspecieNivel FROM Tax_InfraEspecies ORDER BY InfraEspecieNivel";
	$qqq = mysql_query($qq,$conn);
	echo "<td colspan=3><table><tr><td>
	<select name='subvar' >";
		echo "<option selected value=$subvar>$subvar</option>";
		while ($rw = mysql_fetch_assoc($qqq)) {
			echo "<option value=".$rw['InfraEspecieNivel'].">".$rw['InfraEspecieNivel']."</option>";
		}
	echo "
	</select></td>";
} 
echo "</tr></table>
</td>
</tr>";
}
echo "
<tr>
<td class='tdsmallbold' align='right'>".GetLangVar('namenome')."</td>
<td colspan=3><table><tr><td>
<input name='spnome' type='text' value='$spnome' size='30' class='selectedval'></td>
<td class='tdsmallbold' align='right'>".GetLangVar('nameautor')."</td>";



echo "<td><input type='text' value='$autor' name='autor' size='15' ></td>
</tr></table></td>
</tr>";
echo "<tr>
<td class='tdsmallbold' align='right'>".GetLangVar('messageestenomee')."</td>
<td colspan=3 class='tdformnotes' >&nbsp;&nbsp;
<input type='radio' " ;
if ($nomevalido==1) { echo "checked";}
echo " name='nomevalido' value=1 >".mb_strtolower(GetLangVar('namevalido'))."&nbsp;&nbsp;
<input type='radio' "; 
if ($nomevalido==0) { echo "checked";}
echo " name='nomevalido' value=0 >".mb_strtolower(GetLangVar('nameinvalido'))."
</td>
</tr>";
if ($toedit!=GetLangVar('namefamily')) {
echo "<tr>
<td class='tdsmallbold' align='right'>".GetLangVar('namebasionym')."</td>
<td colspan=3><table><tr><td>
<input name='basionym' type='text' value='$basionym' size='30' ></td>
<td class='tdsmallbold' align='right'>".GetLangVar('nameautor')."</td>
<td><input name='basionymautor' type='text' value='$basionymautor' size='15' ></td>
</tr></table></td>
</tr>
<tr>
<td class='tdsmallbold' align='right'>".GetLangVar('namejournal')."</td>
<td colspan=3><table><tr><td>
<input name='pubrevista' type='text' value='$pubrevista' size='58' ></td>
</tr></table></td>
</tr><tr>
<td class='tdsmallbold' align='right'>".GetLangVar('namevolume')."</td>
<td colspan=3><table><tr>
<td><input name='pubvolume' type='text' value='$pubvolume' size='30' ></td>
<td class='tdsmallbold' align='right'>".GetLangVar('nameano')."</td>
<td><input name='pubano' type='text' value='$pubano' size='15'></td>
</tr></table></td>
</tr>";
}

	

//////////
echo "<tr>
<td class='tdsmallbold' align='right'>".GetLangVar('namesinonimos')."</td>
<td colspan=3>
<table><tr>	
	<input type='hidden' id='specieslistids' name='sinonimos' value='$sinonimos'>";
	if (empty($specieslist)) {
		echo "<td><textarea rows=2 cols=50 id='specieslist' name='specieslist' readonly>$specieslist</textarea></td>";
	} else {
		echo "<td class='tdsmalldescription'>$specieslist
			 	<input type='hidden' id='specieslist' name='specieslist' value='$specieslist'></td>";
	}
echo 
	"<td>
	<input type='button' value='<<' class='bsubmit' ";
		$myurl ="selectspeciespopup.php?formname=specieslistform&elementname=specieslistids&destlistlist=".$sinonimos;
		if (!empty($genusid) && empty($speciesid)) 
			{$myurl = $myurl."&famid=".$famid;} 
		elseif (!empty($speciesid)) 
			{$myurl = $myurl."&famid=".$famid."&genusid=".$genusid;} 
		echo "	onclick = \"javascript:small_window('$myurl',500,400,'SelectSpecies');\">
	</td>
</tr>
</table>



<!---<table><tr>
<td><input name='sinonimos' type='text' value='$sinonimos' size='58'></td>
</tr></table>--->

</td>
</tr>";
//if ($toedit==GetLangVar('nameinfraspecies') || $toedit==GetLangVar('namespecies')) {
echo "<tr>
	<td class='tdsmallbold' align='right'>".GetLangVar('namegeodistribution')."</td>
	<td colspan=3><table><tr><td>
	<textarea name='geodist' cols=50 rows=2 >$geodist</textarea></td>
	</tr></table></td>
	</tr>";
//dados de nome vulgar
echo	"<input type='hidden' id='vernacularvalue' name='vernacularvalue' value='$vernacularvalue'>
		<td class='tdsmallboldright'>".GetLangVar('namevernacular')."</td>
		<td colspan=3 ><table><tr>";
		if (empty($vernaculartxt)) {
			echo "<td class='tdformnotes' >
				<textarea rows=1 cols=50% id='vernaculartxt' name='vernaculartxt' readonly>$vernaculartxt</textarea>";
		} else {
			echo "<td class='tdformnotes' >
				<textarea rows=1 cols=50% id='vernaculartxt' name='vernaculartxt' readonly>$vernaculartxt</textarea>
				";
		}
		echo "</td><td><input type=button value=\"+\" class='bsubmit' ";
		$myurl ="vernacular-popup.php?formname=specieslistform&tempelement=vernaculartxt&elementname=vernacularvalue&getvernacularids=$vernacularvalue"; 		
		echo "	onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\">
		</td></tr></table></td></tr>"; 
//}
echo 
"<tr>
<td class='tdsmallbold' align='right'>".GetLangVar('nameobs')."</td>
<td colspan=3><table><tr><td>
<textarea name='notas' cols=50 rows=3>$notas</textarea></td>
</tr></table></td>
</tr>
<tr><td colspan=4>
<table align='center'><tr><td>
<td align='center'>
	<input type = 'submit' class='bsubmit' value='".GetLangVar('nameenviar')."'>
</td>
<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
</form>
<form action=taxa-form.php method='post'>
	<input type='hidden' name='famid' value='$famid'>
	<input type='hidden' name='speciesid' value='$speciesid'>
	<input type='hidden' name='infraspid' value='$infraspid'>
	<input type='hidden' name='genusid' value='$genusid'>
<td align='center'>
	<input type = 'submit' class='breset' value='".GetLangVar('namevoltar')."'></td>
</td>
</form>
</tr></table></td></tr>
</tbody>
</table>
";

$which_java = array("<script type='text/javascript' src='javascript/popupform.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
newfooter($which_java,$calendar=FALSE,$footer=$menu);
?>