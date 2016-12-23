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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'GPS Salvar';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (empty($updatetracks)  || !isset($updatetracks)) {
$erro=0;
$qq = "SELECT checkcoordenadas('".$Latitude."', 'LATITUDE') as teste";
$rs = mysql_query($qq,$conn);
$rss = mysql_fetch_assoc($rs);
if (is_null($rss['teste'])) {
echo "
<br />
<table align='center' class='erro'>
  <tr><td>Latitude não tem valor de latitude</td></tr>
</table>
<br />";
$erro++;
}

$qq = "SELECT checkcoordenadas('".$Longitude."', 'LONGITUDE') as teste";
$rs = mysql_query($qq,$conn);
$rss = mysql_fetch_assoc($rs);
if (is_null($rss['teste'])) {
echo "
<br />
<table align='center' class='erro'>
  <tr><td>Longitude não tem valor de longitude</td></tr>
</table>
<br />";
$erro++;
}

if (!empty($DateOriginal)) {
$qq = "SELECT date_check('".$DateOriginal."') as teste";
$rs = mysql_query($qq,$conn);
$rss = mysql_fetch_assoc($rs);
$rsz = trim($rss['teste']);
if (empty($rsz)) {
echo "
<br />
<table align='center' class='erro'>
<tr><td>DateOriginal não tem valor de data</td></tr>
</table>
<br />";
$erro++;
}
}
if (!empty($TimeOriginal)) {
$qq = "SELECT checktime('".$TimeOriginal."') as teste";
$rs = mysql_query($qq,$conn);
$rss = mysql_fetch_assoc($rs);
if ($rss['teste']==0) {
echo "
<br />
<table align='center' class='erro'>
  <tr><td>TimeOriginal não tem valor de TimeOriginal</td></tr>
</table>
<br />";
$erro++;
}
}
if (!empty($Altitude)) {
$qq = "SELECT IsNumeric('".$Altitude."') as teste";
$rs = mysql_query($qq,$conn);
$rss = mysql_fetch_assoc($rs);
if ($rss['teste']==0) {
echo "
<br />
<table align='center' class='erro'>
  <tr><td>Altitude não tem valor de Altitude</td></tr>
</table>
<br />";
$erro++;
}
}

//check new values
$colunasgps = array("Name", "TrackName", "Notas", "DateOriginal", "TimeOriginal", "Latitude", "Longitude", "Altitude", "GPSMapDatum", "GazetteerID");

$colobr = array("Name", "Latitude", "Longitude", "GazetteerID");
$isthere=0;
foreach ($colobr as $vv) {
		$valold = $orginal_recvals[$vv];
		$valnew = $ppost[$vv];
		if (empty($valnew)) {
			$isthere++;
		}
}

if ($isthere>0) {
echo "
<br />
<table align='center' class='erro'>
  <tr><td>Campos Name, Latitude, Longitude, Localidade são obrigatorios</td></tr>
</table>
<br />";
$erro++;
}


if ($erro==0) {
	//se editando
	$orginal_recvals = array();
	$update=0;
	$gaznew=0;
	if ($gpspointid>0) {
		$qq = "SELECT * FROM GPS_DATA WHERE PointID='".$gpspointid."'";
		$rs = mysql_query($qq,$conn);
		$orginal_recvals = mysql_fetch_assoc($rs);
		foreach ($colunasgps as $vv) {
			$valold = $orginal_recvals[$vv];
			$valnew = $ppost[$vv];
			if ($valold!=$valnew) {
				$update++;
				if ($vv=='GazetteerID') {
					$gaznew++;
				}
			}
		}
	}
	$arrayofvalues = array();
	foreach ($colunasgps as $vv) {
		$valold = $orginal_recvals[$vv];
		$valnew = $ppost[$vv];
		$arrayofvalues = array_merge((array)$arrayofvalues,(array)array($vv => $valnew));
	}
	$success=0;
	$hastracks=0;
	if ($update==0) {
		$arrayofvalues = array_merge((array)$arrayofvalues,(array)array('Type' => 'Waypoint'));
		$newgpsid = InsertIntoTable($arrayofvalues,'PointID','GPS_DATA',$conn);
		if ($newgpsid) {
			$success++;
		}
	} else {
		CreateorUpdateTableofChanges($gpspointid,'PointID','GPS_DATA',$conn);
		$newupdate = UpdateTable($gpspointid,$arrayofvalues,'PointID','GPS_DATA',$conn);
		if ($newupdate && $gaznew>0) {
			$qq = "SELECT COUNT(*) as tracks FROM GPS_DATA WHERE GazetteerID='".$orginal_recvals['GazetteerID']."' AND DateOriginal='".$orginal_recvals['DateOriginal']."' AND FileName='".$orginal_recvals['FileName']."' AND Type='Trackpoint'";
			$rs = mysql_query($qq,$conn);
			$trr = mysql_fetch_assoc($rs);
			$success++;
			if ($trr['tracks']>0) {
				$hastracks = $trr['tracks'];
			}
		}
	}

	if ($success>0) {
		echo "
<br />
<table align='center' class='success' cellpadding='7'>
  <tr><td>O ponto foi cadastrado com sucesso!</td></tr>
</table>
<br />";
	}
	if ($hastracks>0 && $success>0) {
		echo "
<br />
<form action='gps-ponto-save.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
 <input type='hidden' name='updatetracks' value='1' />
 <input type='hidden' name='hastracks' value='".$hastracks."' />
 <input type='hidden' name='OLDGazetteerID' value='".$orginal_recvals['GazetteerID']."' />
 <input type='hidden' name='OLDDateOriginal' value='".$orginal_recvals['DateOriginal']."' />
 <input type='hidden' name='OLDFileName' value='".$orginal_recvals['FileName']."' />
 <input type='hidden' name='NEWGazetteerID' value='".$ppost['GazetteerID']."' />
<table align='center' class='erro' cellpadding='7'>
<tr><td>Como a localidade mudou, posso atualizar Trackpoints que foram importados junto com o ponto modificado. Existem $hastracks trackpoints para a mesma data e mesma localidade que o ponto.</td></tr>
<tr><td><input type='submit' value='Atualizar localidade para esses trackpoints!' class='bsubmit' /></td></tr>
</table>
</form>
<br />";
	}
} else  {
echo "
<br />
<form action='gps-ponto-exec.php' method='post'>";
foreach ($ppost as $kk => $vv) {
echo "
<input type='hidden' name='".$kk."' value='".$vv."'>";
}
echo "
<table align='center' cellpadding='7'>
<tr><td><input type='submit' value='Voltar' class='bsubmit' /></td></tr>
</table>
</form>
<br />";
}

} else {
	$qq = "UPDATE GPS_DATA SET GazetteerID='".$ppost['NEWGazetteerID']."' WHERE GazetteerID='".$ppost['OLDGazetteerID']."' AND DateOriginal='".$ppost['OLDDateOriginal']."' AND FileName='".$ppost['OLDFileName']."' AND Type='Trackpoint'";
	$rs = mysql_query($qq,$conn);
	if ($rs) {
	echo "
<br />
<form >
  <input type='hidden' name='ispopup' value='".$ispopup."' />
<table align='center' cellpadding='7'>
<tr><td>$hastracks  registros de trackpoints atualizados para localidade</td></tr>
<tr><td><input type='button' value='Fechar' class='bsubmit' onclick=\"javascript: window.close();\"/></td></tr>
</table>
</form>
<br />";
	}
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>