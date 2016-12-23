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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'GPS Ponto Batch Locality';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($_SESSION["GPSPOINTIDS"])) {
	if (count($gpspointids)>0 && $GazetteerID>0) {
		$gpsid = $gpspointids[0];
		unset($gpspointids[0]);
		$vvv = array_values($gpspointids);
		$_SESSION["GPSPOINTIDS"] = serialize($vvv);
	}
} else {
	if ($updatetracks>0 && $final==1) {
		$qq = "UPDATE GPS_DATA SET GazetteerID='".$ppost['GazetteerID']."' WHERE GazetteerID='".$ppost['OLDGazetteerID']."' AND DateOriginal='".$ppost['OLDDateOriginal']."' AND 	FileName='".$ppost['OLDFileName']."' AND Type='Trackpoint'";
		$rs = mysql_query($qq,$conn);
		if ($rs) {
		echo "
<br />
<table align='center' cellpadding='7'>
<tr><td>Localidade atualizada para $hastracks registros de trackpoints associados ao ponto</td></tr>
</table>
<br />";
		}
	}
	$vvv = $_SESSION["GPSPOINTIDS"];
	$gpspointids = unserialize($vvv);
	$gpsid = $gpspointids[0];
	unset($gpspointids[0]);
	$vvv = array_values($gpspointids);
	$_SESSION["GPSPOINTIDS"] = serialize($vvv);
}
if ($gpsid>0 && $GazetteerID>0) {
	$success=0;
	$arrayofvalues = array('GazetteerID' => $GazetteerID);
	$qq = "SELECT * FROM GPS_DATA WHERE PointID='".$gpsid."'";
	$rs = mysql_query($qq,$conn);
	$orginal_recvals = mysql_fetch_assoc($rs);
	
	CreateorUpdateTableofChanges($gpsid,'PointID','GPS_DATA',$conn);
	$newupdate = UpdateTable($gpsid,$arrayofvalues,'PointID','GPS_DATA',$conn);
	if ($newupdate) {
		$qq = "SELECT COUNT(*) as tracks FROM GPS_DATA WHERE GazetteerID='".$orginal_recvals['GazetteerID']."' AND DateOriginal='".$orginal_recvals['DateOriginal']."' AND FileName='".$orginal_recvals['FileName']."' AND Type='Trackpoint'";
		$rs = mysql_query($qq,$conn);
		$trr = mysql_fetch_assoc($rs);
		$success++;
		if ($trr['tracks']>0) {
			$hastracks = $trr['tracks'];
		}
	}
	if ($success>0) {
	echo "
<br />
<table align='center' class='success' cellpadding='7'>
<tr><td>O ponto ".$orginal_recvals['Name']."  foi cadastrado com sucesso!</td></tr>
</table>
<br />";
	}
	if ($hastracks>0 && $success>0) {
$localidadeid = 'gazetteerid_'.$val;
$locality = strip_tags(getlocalidade($localidadeid,$conn));	
	
		echo "
<br />
<form name='coletaform' action='gps-ponto-batchlocality-exec.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
<input type='hidden' name='updatetracks' value='1' />
<input type='hidden' name='hastracks' value='".$hastracks."' />
<input type='hidden' name='OLDGazetteerID' value='".$orginal_recvals['GazetteerID']."' />
<input type='hidden' name='OLDDateOriginal' value='".$orginal_recvals['DateOriginal']."' />
<input type='hidden' name='OLDFileName' value='".$orginal_recvals['FileName']."' />
<input type='hidden' name='GazetteerID' value='".$GazetteerID."' />
<table align='center' class='erro' cellpadding='7'>
<tr><td>Existem $hastracks trackpoints para a mesma data, mesma localidade e mesmo arquivo que o ponto <b>".$orginal_recvals['Name']."</b>.</td></tr>
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value='' />
        <td align='center' ><input type='submit' value='Atualizar' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\" /> </td>
        <td align='center' ><input type='submit' value='Não Atualizar - Próximo Ponto' class='bblue' onclick=\"javascript:document.coletaform.final.value=2\" /> </td>
      </tr>
    </table>
  </td>
</tr>
</table>
</form>
<br />";
	} 
	elseif ($success>0) {
	echo "
<form name='myform' action='gps-ponto-batchlocality-exec.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='GazetteerID' value='".$GazetteerID."' />
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
<!---<tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
--->
</form>";
	}
} else {
	echo "
<form name='myform2' action='gps-ponto-batchlocality.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
<script language=\"JavaScript\">setTimeout('document.myform2.submit()',0.0001);</script>

<!---<tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
--->
</form>";


}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>