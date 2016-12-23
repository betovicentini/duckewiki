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
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);


//CABECALHO
$ispopup=1;
$menu = FALSE;
$title = '';
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$body='';
$title ='Mudanças de Determinação ';

//PARA IMAGE BYSPECIES
if (isset($getplspid) && !empty($getplspid)) {
	//GET PLANT AND SPECIES ID
	if ($getplspid!='again') {
echo 
"<form id='getplspidform' action='detchangespopup.php' method='post'>";
unset($ppost['getplspid']);
foreach ($ppost as $kk => $vv) {
	echo " <input type='hidden' name='".$kk."' value='".$vv."' />";
}
echo "<input type='hidden' name='getplspid' value='again' />
  <input type='hidden' id='thisgetspecimenid' name='especimenid' value='' />
  <input type='hidden' id='thisgetplantaid' name='plantaid' value='' />
  <script language=\"JavaScript\">
  setTimeout(
    function() {
      var sourcefield = opener.document.getElementById('pl_".$getplspid."').value;
      var sourcefield2 = opener.document.getElementById('sp_".$getplspid."').value;
      document.getElementById('thisgetplantaid').value= sourcefield;
      document.getElementById('thisgetspecimenid').value= sourcefield2;
      document.getElementById('getplspidform').submit();
    }
    ,0.0001);
  </script>
</form>
";
	} 
	else {
		if ($especimenid>0) {
			$qu = "SELECT DetID FROM Especimenes WHERE EspecimenID='".$especimenid."'";
		}
		if ($plantaid>0) {
			$qu = "SELECT DetID FROM Plantas WHERE PlantaID='".$plantaid."'";
		}
		$rs = mysql_query($qu,$conn);
		$rw = mysql_fetch_assoc($rs);
		$detid = $rw['DetID'];
	}
} 
if (!isset($getplspid) || $getplspid=='again') {
FazHeader($title,$body,$which_css,$which_java,$menu);
if ($plantaid>0) {
	$qq = "SELECT DetID,PlantaID,PlantaTag,Gazetteer FROM Plantas JOIN Gazetteer USING(GazetteerID) WHERE PlantaID='".$plantaid."'";
} elseif ($especimenid>0) {
	$qq = "SELECT * FROM Especimenes WHERE EspecimenID='".$especimenid."'";
}
	
$qr = mysql_query($qq,$conn);
$rw = mysql_fetch_assoc($qr);
$detid = $rw['DetID'];
$detarr = getdetsetvar($detid,$conn);
$detset = serialize($detarr);
$dettext = describetaxa($detset,$conn);
echo "
<br />
<table class='myformtable' align='left' cellpadding='5'>
<thead>
<tr>
  <td colspan='2'>";
if ($plantaid>0) {
	$tgn = sprintf("%05s", $rw['PlantaTag']);
	$tgn = $tgn." - ".$rw['Gazetteer'];
	echo "Determinações da planta ".$tgn;
} elseif ($especimenid>0) {
	$psid = $rw['ColetorID'];
	$rr = getpessoa($psid,$abb=TRUE,$conn);
	$num = $rw['Number'];
	$ru = mysql_fetch_assoc($rr);
	$tgn = $ru['Abreviacao']." ".$num;
	echo "Determinações da coleta ".$tgn;
}
echo "</td>
</tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>Atual</td>
  <td>".$dettext."</td>
</tr>";

if ($plantaid>0) {
	$qq = "SELECT DISTINCT DetID FROM ChangePlantas WHERE PlantaID='".$plantaid."' AND DetID>0 ORDER BY ChangedDate DESC";
} elseif ($especimenid>0) {
	$qq = "SELECT DISTINCT DetID FROM ChangeEspecimenes WHERE EspecimenID='".$especimenid."' AND DetID>0 ORDER BY ChangedDate DESC";
}
$qr = mysql_query($qq,$conn);
$nqr = mysql_numrows($qr);
if ($nqr>0) {
	$i=0;
	while ($row = mysql_fetch_assoc($qr)) {
			$did = $row['DetID'];
			if ($did!=$detid && $did>0) {
				if ($i==0) {
					echo "
<tr class='trsubhead'><td colspan='2' align='center'>Determinações anteriores</td></tr>";
				}
				$i++;
				$detarr = getdetsetvar($did,$conn);
				$detset = serialize($detarr);
				$dettext = describetaxa($detset,$conn);
				if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
					echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>Anterior</td>
  <td>".$dettext."</td>
</tr>";
			}
	}
	if ($i==0) {
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
					echo "
<tr bgcolor = '".$bgcolor."'><td class='tdsmallboldright'>Anterior</td><td>não há</td></tr>";
	}
}

echo "
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
}
?>