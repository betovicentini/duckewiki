<?php
//Start session
set_time_limit(0);
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
//$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);


$uuid = $_SESSION['userid'];
$lastname = $_SESSION['userlastname'];
$aclevel = $_SESSION['accesslevel'];

//echopre($ppost);

HTMLheaders('');

if ($final>0) {
	if ($final==1) {
	$qq = "SELECT * FROM (SELECT PlantaTag, COUNT(*) as ni FROM Plantas GROUP BY PlantaTag) as tb WHERE tb.ni>1 LIMIT $skip,1";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	//$qq = "SELECT PlantaTag,PlantaID,GazetteerTIPOtxt,Gazetteer,DetID FROM Plantas JOIN Gazetteer USING(GazetteerID) WHERE PlantaTag='".$row['PlantaTag']."'";
	$qq = "SELECT PlantaTag,PlantaID,Gazetteer,DetID FROM Plantas JOIN Gazetteer USING(GazetteerID) WHERE PlantaTag='".$row['PlantaTag']."'";	
	$rs = mysql_query($qq,$conn);
	while ($rw = mysql_fetch_assoc($rs)) {
		if ($changetag[$rw['PlantaID']]!=$row['PlantaTag']) {
			$newtag = trim($changetag[$rw['PlantaID']]);
			
			$arrayofvalues = array(
			'PlantaTag' => $newtag);
			CreateorUpdateTableofChanges($rw['PlantaID'],'PlantaID','Plantas',$conn);
			$updatespecid = UpdateTable($rw['PlantaID'],$arrayofvalues,'PlantaID','Plantas',$conn);
			if ($updatespecid>0) {
				echo "Tag corrigir<br>";
			} 
		}
		if ($todelete[$rw['PlantaID']]==1) {
			//checar se tem variáveis de monitoramento
			$qq = "SELECT * FROM Monitoramento WHERE PlantaID='".$rw['PlantaID']."'";
			$rss1 = mysql_query($qq,$conn);
			$nrss1 = mysql_numrows($rss1);

			$qq = "SELECT * FROM Traits_variation WHERE PlantaID='".$rw['PlantaID']."'";
			$rss2 = mysql_query($qq,$conn);
			$nrss2 = mysql_numrows($rss2);

			$ntt = $nrss1+$nrss2;
			if ($ntt==0) {
				CreateorUpdateTableofChanges($rw['PlantaID'],'PlantaID','Plantas',$conn);
				$qq = "DELETE FROM Plantas WHERE PlantaID='".$rw['PlantaID']."'";
				$delres = mysql_query($qq,$conn);
				if ($delres) {
					echo "Registro apagado<br>";
				} 
			} else {
				$moni=0;
				if ($nrss1>0) {
					while ($rw1 = mysql_fetch_assoc($rss1)) {
						CreateorUpdateTableofChanges($rw1['MonitoramentoID'],'MonitoramentoID','Monitoramento',$conn);
						$qq = "DELETE FROM Monitoramento WHERE MonitoramentoID='".$rw1['MonitoramentoID']."'";
						$delres = mysql_query($qq,$conn);
						if ($delres) {
							$moni++;
						} 
					}
				
				}
				$esta =0;
				if ($nrss2>0) {
					while ($rw2 = mysql_fetch_assoc($rss2)) {
						CreateorUpdateTableofChanges($rw2['TraitVariationID'],'TraitVariationID','Traits_variation',$conn);
						$qq = "DELETE FROM Traits_variation WHERE TraitVariationID='".$rw2['TraitVariationID']."'";
						$delres = mysql_query($qq,$conn);
						if ($delres) {
							$esta++;
						} 
					}
				
				}
				if ($esta==$nrss2 && $moni==$nrss1) {
					CreateorUpdateTableofChanges($rw['PlantaID'],'PlantaID','Plantas',$conn);
					$qq = "DELETE FROM Plantas WHERE PlantaID='".$rw['PlantaID']."'";
					$delres = mysql_query($qq,$conn);
					if ($delres) {
						echo "Registro apagado +  $moni variáveis de monitoramento + $esta variáveis estáticas<br>";
					}	 
				} else {
					echo "Registro NÃO apagado +  $moni $nrss1 variáveis de monitoramento + $esta $nrss2 variáveis estáticas<br>";
				}
			}			
		}
	}
  } else {
  	$skip = $skip+1;
  }
} 
else {
	$skip=0;
}

$qq = "SELECT * FROM (SELECT PlantaTag, COUNT(*) as ni FROM Plantas GROUP BY PlantaTag) as tb WHERE tb.ni>1 LIMIT $skip,1";

$res = mysql_query($qq,$conn);
$row = mysql_fetch_assoc($res);
//$qq = "SELECT Plantas.AddedDate,Plantas.GazetteerID,PlantaTag,PlantaID,GazetteerTIPOtxt,Gazetteer,DetID, monitoramentostring(PlantaID,0,1,1) as monidesc FROM Plantas JOIN Gazetteer USING(GazetteerID) WHERE PlantaTag='".$row['PlantaTag']."'";
$qq = "SELECT Plantas.AddedDate,Plantas.GazetteerID,PlantaTag,PlantaID,Gazetteer,DetID, monitoramentostring(PlantaID,0,1,1) as monidesc FROM Plantas JOIN Gazetteer USING(GazetteerID) WHERE PlantaTag='".$row['PlantaTag']."'";
//echo $qq."<br>";
$rs = @mysql_query($qq,$conn);
$Nrw = @mysql_numrows($rs);
$gaz = array();
echo "
<br>
<table class='myformtable' align='center' cellpadding='4' width=90%>
<thead>
  <tr ><td colspan='100%'>Plantas com números duplicados</td>
</tr>
<tr class='subhead'>	
		<td>Apagar</td>
 		<td >Tag</td>
 		<td> Local</td>
 		<td >Tags próximos</td>
 		<td >Tags no local</td>
 		<td >Determinação</td>
 		<td >Monitoramento</td>
 		<td >Data Alteração Wiki</td>
</tr>
</thead>
<tbody>

<form name='finalform' action='checkDupTags.php' method='post'>
  <input type=hidden name='final' value=''>
  <input type=hidden name='skip' value='".$skip."'>";
if ($Nrw>0)   {
while ($rw = mysql_fetch_assoc($rs)) {
	$detset = getdetsetvar($rw['DetID'],$conn);
	$detset = serialize($detset);
	$dettext = describetaxa($detset,$conn);
	//$gaz = $rw['GazetteerTIPOtxt']." ".$rw['Gazetteer']." ".$rw['GazetteerID'];
	$gaz = $rw['Gazetteer']." ".$rw['GazetteerID'];
	$plme = ($rw['PlantaTag']+0)-10;
	$plma = ($rw['PlantaTag']+0)+10;
	
	$qz = "SELECT PlantaTag FROM `Plantas` WHERE  `GazetteerID` =".$rw['GazetteerID']." AND PlantaTag+0>".$plme." AND PlantaTag+0<".$plma;
	$rzs = mysql_query($qz,$conn);
	$closetags = array();
	while ($rwz = mysql_fetch_assoc($rzs)) {
		$pll = $rwz['PlantaTag']+0;
		if ($pll!=($rw['PlantaTag']+0)) {
			$closetags[] = $rwz['PlantaTag']+0;
		}
	}
	$ntags = implode("; ",$closetags);	
	
	$qz = "SELECT PlantaTag FROM `Plantas` WHERE  `GazetteerID` =".$rw['GazetteerID'];
	$rzs = mysql_query($qz,$conn);
	$gaztags = array();
	while ($rwz = mysql_fetch_assoc($rzs)) {
		$pll = $rwz['PlantaTag']+0;
		if ($pll!=($rw['PlantaTag']+0)) {
			$gaztags[] = $rwz['PlantaTag']+0;
		}
	}
	$gazm = max($gaztags);
	$gazmi = min($gaztags);

	$gaztags = $gazmi."-".$gazm;	
	
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
		<td><input type='checkbox' name=\"todelete[".$rw['PlantaID']."]\" value=1></td>
 		<td class='tdsmallbold'>
 			<table>
 				<tr><td>".$rw['PlantaTag']."</td></tr>
 				<tr><td><input type='text' size=4  name=\"changetag[".$rw['PlantaID']."]\" value='".$rw['PlantaTag']."'></td></tr>
 			</table>	
 		</td>
 		<td class='tdformnotes'>".$gaz."</td>
 		<td class='tdformnotes'>".$ntags."</td>
 		<td class='tdformnotes'>".$gaztags."</td>
 		<td class='tdformnotes'>".$dettext."</td>
 		<td class='tdformnotes'>".$rw['monidesc']."</td>
 		<td class='tdformnotes'>".$rw['AddedDate']."</td>
 		</tr>";
}
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;}
echo "
<tr bgcolor = $bgcolor>
<td colspan=100% align='center'>
<table>
 <table align='center' >
      <tr>
        <td align='center' ><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' onclick=\"javascript:document.finalform.final.value=1\"></td>
        <td align='left'><input type='submit' value='Pular este' class='bblue' onclick=\"javascript:document.finalform.final.value=2\"></td>
	</tr>
 </table>
</td>
</tr>
</form>
</tbody>
</table>";


HTMLtrailers();
?>