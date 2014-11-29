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
$title = 'GPS Editar';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//se editando
$orginal_recvals = array();
if ($final==1) {
	$qq = "SELECT * FROM GPS_DATA WHERE PointID='".$gpspointid."'";
	$rs = mysql_query($qq,$conn);
	$orginal_recvals = mysql_fetch_assoc($rs);
}


$colunasgps = array("Name", "TrackName", "Notas", "Type", "DateTimeOriginal", "DateOriginal", "TimeOriginal", "Latitude", "Longitude", "Altitude", "GPSMapDatum", "GPSName", "GazetteerID", "FileName", "ExpeditoID", "AddedBy", "AddedDate");
$coltypes = array("text", "none", "textarea", "none", "none", "text", "text", "text", "text", "text", "text", "none", "gazid", "readonly", "none", "none", "none");

echo "
<br />
<form name='coletaform' action='gps-ponto-save.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
<table class='myformtable' align='center' cellpadding='5' >
<thead>";
if ($final==1) {
echo "
<input type='hidden' name='gpspointid' value='".$gpspointid."' />
<tr><td colspan='100%'>Editando ponto de GPS</td></tr>
<tr class='subhead'><td colspan='100%'>$gpspt</td></tr>";
} else {
echo "
<tr><td colspan='100%'>Novo ponto de GPS</td></tr>";
}
echo "
</thead>
<tbody>
";
foreach ($colunasgps as $kk => $vv) {
	$val = $orginal_recvals[$vv];
	$ctp = $coltypes[$kk];
	if ($ctp!='none') {
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
		echo "
<tr bgcolor = '".$bgcolor."'>";
	}
	if ($ctp=='text' || $ctp=='readonly') {
		if ($ctp=='readonly') {
			$ch = $ctp;
		} else {
			$ch = '';
		}
		echo "
  <td class='tdsmallbold'>$vv</td>
  <td><input type='text' size='40' value='".$val."' name='".$vv."' $ch /></td>
</tr>";
	}
	if ($ctp=='textarea') {
		echo "
  <td class='tdsmallbold'>$vv</td>
  <td><textarea class='tdformnotes' name='".$vv."' >".$val."</textarea></td>
</tr>";
	}
	if ($ctp=='gazid') {
		echo "
  <td class='tdsmallbold'>Pertence à localidade</td>
  <td>";
		if ($val>0) {
		$localidadeid = 'gazetteerid_'.$val;
		$locality = strip_tags(getlocalidade($localidadeid,$conn));
		} else {
			$locality = 'Digite para buscar localidade, municipio, provincia, pais';
		}
		autosuggestfieldval3('search-gazetteer.php','locality',$locality,'localres',$vv,$val,true,60);
		echo "
  </td>
</tr>";
	}
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center' >
      <tr>
        <td align='center' ><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /> </td>
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>