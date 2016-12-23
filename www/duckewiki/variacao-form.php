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



if ($linkto=='tree') { $namevar = GetLangVar('nametree');}
if ($linkto=='taxa') {$namevar = GetLangVar('nametaxa');}
if ($linkto=='coletas') {$namevar = GetLangVar('nameexsicata');}

//adjust tree number list when this is the case
if ($linkto=='tree') {
	if (!isset($plantaid)) { 
		$firslist = 1;
	} elseif (substr($plantaid,0,4)=='more') {
		$plset = explode("_",$plantaid);
		//echopre($plset);
		$ffrom = $plset[1];
		$plto = $plset[2];
	}
	if ($firslist==1) {
			$qq = "SELECT * FROM Plantas ORDER BY PlantaTag+0 ASC LIMIT 1";
			$rwr = mysql_query($qq,$conn);
			$plnums = mysql_fetch_assoc($rwr);
			$plmin = $plnums['PlantaTag']+0;
			$qq = "SELECT * FROM Plantas ORDER BY PlantaTag+0 DESC LIMIT 1";
			$rrw = mysql_query($qq,$conn);
			$plnums = mysql_fetch_assoc($rrw);
			$plmax = $plnums['PlantaTag']+0;
			$rrang = $plmax-$plmin;
			$plnumsstep = floor($rrang/200);
			$plnumsarr = range($plmin,$plmax,$plnumsstep);
			$plantasarray = serialize($plnumsarr);
			$plto = $plnumsarr[0];
			$ffrom = $plnumsarr[1];
		} else {
			$plnumsarr = unserialize($plantasarray);
	}
}

HTMLheaders('');
echo "
<br>
<table align='left' class='myformtable' cellpadding='7'>
<thead>
  <tr><td colspan=100%>".GetLangVar('messageentrarvariacao')."&nbsp;<i>$namevar</i></td></tr>
</thead>
<tbody>
<tr>
  <td>
    <table>
      <tr>
        <td class='bold'>".GetLangVar('nameformulario')."</td>
<form action='variacao-form.php' method='post'  >
        <td >
          <input type='hidden' name='plotid' value='$plotid'>
          <input type='hidden' name='linkto' value='$linkto'>
          <input type='hidden' name='famid' value='$famid'>
          <input type='hidden' name='genusid' value='$genusid'>
          <input type='hidden' name='speciesid' value='$speciesid'>
          <input type='hidden' name='infraspid' value='$infraspid'>
          <input type='hidden' name='especimenid' value='$especimenid'>
          <input type='hidden' name='option1' value='$option1'>
          <input type='hidden' name='plantaid' value='$plantaid'>
          <input type='hidden' name='plantasarray' value='".$plantasarray."'>
          <input type='hidden' name='ffrom' value='".$ffrom."'>
          <input type='hidden' name='plto' value='".$plto."'>
          <select name='formid' onchange='this.form.submit();'>";
				if (!empty($formid)) {
					$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
					$rr = mysql_query($qq,$conn);
					$row= mysql_fetch_assoc($rr);
					echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
				} else {
					echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
				}
				//formularios usuario
				$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName ASC";
				$rr = mysql_query($qq,$conn);
				while ($row= mysql_fetch_assoc($rr)) {
					echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
				}
			echo "
          </select>
        </td>
</form>
      </tr>
    </table>
  </td>
  <td>";

if ($linkto=='coletas') {
	$qq = "SELECT * FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE EspecimenID=".$especimenid;
	$rro = @mysql_query($qq,$conn);
	$rwo= @mysql_fetch_assoc($rro);
	$specname = $rwo['Abreviacao']." ".$rwo['Number'];
echo "
    <table>
      <tr>
        <td class='bold'>".GetLangVar('namecolecao')."</td>
<form action='variacao-form.php' method='post'  >
          <input type='hidden' name='linkto' value='$linkto'>
          <input type='hidden' name='formid' value='$formid'>
          <input type='hidden' name='option1' value='1'>
          <td class='tdformnotes'>"; autosuggestfieldval('search-specimen.php','specname',$specname,'specnameres','especimenid',true); echo "</td>
          <td><input type=submit value='".GetLangVar('nameenviar')."' class='bsubmit'></td> 
</form>
        </tr>
      </table>
      ";
}

if ($linkto=='tree') {
//echopre($_POST);
echo "
<table><tr>
<td class='bold'>".GetLangVar('nametaggedplant')."</td>
<form action='variacao-form.php' method='post'  >

<td>
		<input type='hidden' name='linkto' value='$linkto'>
		<input type='hidden' name='formid' value='$formid'>
		<input type='hidden' name='option1' value='1'>
		<select name='plantaid' onchange='this.form.submit();'>";
			if ($plantaid>0 && substr($plantaid,0,4)!='more') {
			$qq = "SELECT * FROM Plantas WHERE PlantaID='$plantaid'";
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
	
				$jbinexsitu = $row['InSituExSitu'];
						if ($jbinexsitu=='Exsitu') {
							$jbtext = "JB-X";
						} elseif ($jbinexsitu=='Insitu') {
							$jbtext = "JB-N";
						} else {
							$jbtext ='';
						}

					echo "<option selected value='".$row['PlantaID']."'>".$jbtext." ".$row['PlantaTag']."</option>";
				} else {
					echo "<option value=''>".GetLangVar('nameselect')."</option>";
				}
				echo "<option value=''>--------</option>";
				//formularios usuario
				
				
				if (!isset($firslist)) {
				$qq = "SELECT * FROM Plantas WHERE (PlantaTag+0)>=".$ffrom." AND (PlantaTag+0)<=".$plto." ORDER BY PlantaTag+0 ASC";
					$rr = mysql_query($qq,$conn);
					$npls = mysql_numrows($rr);
					$npl=1;
					if ($npls>0) {
						while ($row= mysql_fetch_assoc($rr)) {				
							$jbinexsitu = $row['InSituExSitu'];
							if ($jbinexsitu=='Exsitu') {
								$jbtext = "JB-X";
							} elseif ($jbinexsitu=='Insitu') {
								$jbtext = "JB-N";
							} else {
								$jbtext ='';
							}
							echo "<option value='".$row['PlantaID']."'>&nbsp;&nbsp;&nbsp;".$jbtext." ".$row['PlantaTag']."</option>";
						}
					}
					echo "<option value=''>--------</option>";

				} 
				
				$ngs = count($plnumsarr)-2;
				if ($ngs>0) {
					for ($i=0;$i<=$ngs;$i++) {
						$iii=$i+1;
						$plma = $plnumsarr[$iii];
						$plm = $plnumsarr[$i];
						echo "<option value='more_".$plm."_".$plma."'>".$plm." to ".$plma."</option>";
					}
				}
				echo "
		</select>
</td>";
echo "<input type='hidden' name='plantasarray' value='".$plantasarray."'>";
echo "<input type='hidden' name='ffrom' value='".$ffrom."'>";
echo "<input type='hidden' name='plto' value='".$plto."'>";

echo "</form>
</tr>
</table>
";
//echopre($plantasarray);
}

if ($linkto=='taxa') {
//taxonomia
echo "
<table><tr>
				<td class='bold'>".GetLangVar('nametaxa')."</td>
<form action='variacao-form.php' method='post'  >
				<input type='hidden' name='linkto' value='$linkto'>
				<input type='hidden' name='formid' value='$formid'>
				<input type='hidden' name='option1' value='1'>
				
				<td>
				<select name='famid' onchange='this.form.submit();'>";
				if (empty($famid)) {
					echo "<option>".GetLangVar('namefamily')."</option>";
				} else {
					$rr = getfamilies($famid,$conn,$showinvalid=TRUE);
					$row = $rr[0];
					echo "<option selected value=".$row['FamiliaID'].">".$row['Familia']."</option>";
				}
				$rrr = getfamilies('',$conn,$showinvalid=FALSE);
				while ($row = mysql_fetch_assoc($rrr)) {
					echo "<option value=".$row['FamiliaID'].">".$row['Familia']."</option>";
				}
echo "</select>
	</td></form>
	<form action='variacao-form.php' method='post'  >
		<input type='hidden' name='linkto' value='$linkto'>
		<input type='hidden' name='formid' value='$formid'>
		<input type='hidden' name='famid' value='$famid'>
		<input type='hidden' name='option1' value='1'>
	
	<td>
	<select name='genusid' onchange='this.form.submit();'>";
			$genusid = trim($genusid);
			if (empty($genusid)) {
				echo "<option>".GetLangVar('namegenus')."</option>";
			} else {
				$rr = getgenera($genusid,$famid,$conn,$showinvalid=TRUE);
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['GeneroID'].">".$row['Genero']."</option>";
			}
			$rrr = getgenera('',$famid,$conn,$showinvalid=FALSE);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value=".$row['GeneroID'].">".$row['Genero']."</option>";
			}
echo "</select>
	</td></form>
	<form action='variacao-form.php' method='post'  >
		<input type='hidden' name='formid' value='$formid'>
		<input type='hidden' name='famid' value='$famid'>
		<input type='hidden' name='genusid' value='$genusid'>
		<input type='hidden' name='option1' value='1'>
		<input type='hidden' name='linkto' value='$linkto'>
	<td align='right'>
		<select name='speciesid' onchange='this.form.submit();'>";
			$speciesid = trim($speciesid);
			if (empty($speciesid)) {
				echo "<option>".GetLangVar('namespecies')."</option>";
			} else {
				$rr = getspecies($speciesid,$genusid,$conn,$showinvalid=TRUE);
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['EspecieID'].">".$row['Especie']."</option>";
			}
			$rrr = getspecies('',$genusid,$conn,$showinvalid=FALSE);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value=".$row['EspecieID'].">".$row['Especie']."</option>";
			}
echo "</select>
	</td></form>
	<form action='variacao-form.php' method='post'  >
		<input type='hidden' name='linkto' value='$linkto'>
		<input type='hidden' name='formid' value='$formid'>
		<input type='hidden' name='famid' value='$famid'>
		<input type='hidden' name='genusid' value='$genusid'>
		<input type='hidden' name='speciesid' value='$speciesid'>
		<input type='hidden' name='option1' value='1'>

	<td >
		<select name='infraspid' onchange='this.form.submit();'>";
			if (!empty($infraspid)) {
				$rr = getinfraspecies($infraspid,$speciesid,$conn,$showinvalid=TRUE);
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['InfraEspecieID'].">".$row['InfraEspecieNivel']." ".$row['InfraEspecie']."</option>";
			}
			if (empty($speciesid)) {
				echo "<option>".GetLangVar('nameinfraspecies')."</option>";
			} else {
				$rrr = getinfraspecies('',$speciesid,$conn,$showinvalid=FALSE);
				$nr = mysql_numrows($rrr);
				if ($nr>0) {
					echo "<option>".GetLangVar('nameselect')."</option>";
					while ($row = mysql_fetch_assoc($rrr)) {
						echo "<option value=".$row['InfraEspecieID'].">".$row['InfraEspecieNivel']." ".$row['InfraEspecie']."</option>";
					}	
				} else {
					echo "<option value='missing'>".GetLangVar('namemissing')."</option>";
				}	
			}		
echo "</select>
		</form>
</td>
</tr>
</table>";
}

echo "</td></tr>";

//IF FORMULARIO E LINK SELECIONADOS
if (!empty($formid) && $option1=='1' && is_numeric($formid) && substr($plantaid,0,4)!='more') {
	
	echo "
	<thead>
	<tr class='subhead'>
	<td colspan=100%>
		<table cellpadding='2' align='center'>
		<tr>
		<td >".GetLangVar('messageentrandodadospara')."&nbsp;&nbsp;&nbsp;&nbsp;</td>";
			$oldvals = EnteringVarFor($especimenid,$plantaid,$infraspid,$speciesid,$genusid,$famid,$conn);
			@extract($oldvals);
	echo "</tr></table>
	</td>
	</tr>
	</thead>
	<tbody>";
	$actiontofile = 'variacao-exec.php';
	$actionfilereset = 'variacao-form.php';
	echo "<tr>
		<td  colspan=100% align='center' >
		<form id='varform2' method='POST' enctype='multipart/form-data' action='".$actiontofile."'>
				<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
				<input type='hidden' name='famid' value='$famid'>
				<input type='hidden' name='genusid' value='$genusid'>
				<input type='hidden' name='formid' value='$formid'>
				<input type='hidden' name='speciesid' value='$speciesid'>
				<input type='hidden' name='infraspid' value='$infraspid'>
				<input type='hidden' name='especimenid' value='$especimenid'>
				<input type='hidden' name='plantaid' value='$plantaid'>
				<input type='hidden' name='linkto' value='$linkto'>
				<input type='hidden' name='option1' value='2'>
				<input type='hidden' name='dataobs' value='$dataobs'>";
				echo "<input type='hidden' name='plantasarray' value='".serialize($plnumsarr)."'>";
				echo "<input type='hidden' name='ffrom' value='".$ffrom."'>";
				echo "<input type='hidden' name='plto' value='".$plto."'>";
				include "variacao-form2.php";
	
	echo "</td></tr>"; //fecha tabela para conteudo do formulario
	echo "<tr><td  colspan='100%' >
		<table align='center'>
			<tr><td align='center' ><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' ></td>
	</form>
		<form action='$actionfilereset' method='post' >
				<input type='hidden' name='famid' value='$famid'>
				<input type='hidden' name='genusid' value='$genusid'>
				<input type='hidden' name='formid' value='$formid'>
				<input type='hidden' name='speciesid' value='$speciesid'>
				<input type='hidden' name='infraspid' value='$infraspid'>
				<input type='hidden' name='especimenid' value='$especimenid'>
				<input type='hidden' name='plantaid' value='$plantaid'>
				<input type='hidden' name='linkto' value='$linkto'>
			<td align='left'><input type='submit' value='".GetLangVar('namereset')."' class='bblue' ></td></tr>
		</table></td></tr>
	</form>
<tr><td  colspan='100%' class='tdformnotes'><b>".GetLangVar('nameobs')."</b>: ".GetLangVar('messagemultiplevalues')."</td></tr>";			
	
}

echo "</tbody></table>"; //fecha tabela do formulario

HTMLtrailers();
//PopupTrailers();

?>

