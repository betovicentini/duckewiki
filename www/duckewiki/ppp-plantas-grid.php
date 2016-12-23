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

//PEGA OS VALORES PARA MOSTRAR O GRID
if (count($ppost)==0 && count($gget)==0 && isset($_SESSION['arrtopass'])) {
	$aarr = unserialize($_SESSION['arrtopass']);
	@extract($aarr);
}

//CABECALHO

$menu = FALSE;

$title = 'Mostrando dados para a tabela '.$tbname;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >",
"<link rel=\"stylesheet\" type=\"text/css\" href=\"dhtmlxconnector/dhtmlxTabbar/dhtmlxtabbar.css\">",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn_bricks.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxCalendar/codebase/dhtmlxcalendar.css'>",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_skyblue.css'>"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxcommon.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgridcell.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxCalendar/codebase/dhtmlxcalendar.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor_debug.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_export.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_ssc.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_mcol.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>"

);

FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
$clw = explode(",",$colw);
$lstvs = explode(",",$listvisible);
foreach ($clw as $kk => $vv) {
	if ($lstvs[$kk]!='true') {
		$clw[] =  0;
	}
}

$fertxt = "";
if ($statustraitid>0) {
  $cabecas = explode(",",$ffields);
  $az = array_search('STATUS',$cabecas);

  $qtf = "SELECT * FROM Traits WHERE ParentID=".$statustraitid;
  $restf = mysql_query($qtf,$conn);
  $nz = mysql_numrows($restf)-1;
  $i = 0;
  $fertxt = "mygrid.registerCList(".$az.", [";
  while ($rowtf = mysql_fetch_assoc($restf)) {
      $vals = mb_strtolower($rowtf['TraitName']);
      $fertxt .=  "\"".$vals."\"";
      if ($i<$nz) {
	      $fertxt .= ", ";
      }
      $I++;
  }
     $fertxt .= "]);";
}

$silicaxt = "";
if ($traitsilica>0) {
  $cabecas = explode(",",$ffields);
  $az = array_search('SILICA',$cabecas);

  $qtf = "SELECT * FROM Traits WHERE ParentID=".$traitsilica;
  $restf = mysql_query($qtf,$conn);
  $nz = mysql_numrows($restf)-1;
  $i = 0;
  $silicaxt = "mygrid.registerCList(".$az.", [";
  while ($rowtf = mysql_fetch_assoc($restf)) {
      $vals = mb_strtolower($rowtf['TraitName']);
      $silicaxt .=  "\"".$vals."\"";
      if ($i<$nz) {
	      $silicaxt .= ", ";
      }
  }
     $silicaxt .= "]);";
}


$divwith = array_sum($clw);
//if ($divwith>1200) {
	$divwith = 850;
//}
if ($nrecs<50) {
	$divheight = $nrecs*28.5;
	if ($divheight<150) {
	  $divheight = 200;
	}
	if ($divheight>400) {
		$divheight = 400;
	}
} else {
	$divheight = 400;
}
echo"
<table>";
echo "
<tr>
  <td>
    <div id=\"gridbox\" style=\"width:".$divwith."px; height:".$divheight."px;\"></div>
  </td>
</tr>
<tr>
<td colspan='100%'><div id=\"pagingArea\"></div></td>
</tr>";

echo "
</table>
<script>
mygrid = new dhtmlXGridObject(\"gridbox\");
mygrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
mygrid.setIconPath(\"icons/\");
mygrid.setHeader(\"".$ffields."\");
mygrid.setInitWidths(\"".$colw."\");
mygrid.setColAlign(\"".$colalign."\");
mygrid.setSkin(\"dhx_skyblue\");
mygrid.attachHeader(\"".$filtros."\");
mygrid.setColSorting(\"".$filtros2."\");
mygrid.enableColumnMove(true);
mygrid.setColumnsVisibility(\"".$listvisible."\");
//mygrid.setColValidators(\"".$colvalid."\");
mygrid.setColTypes(\"".$coltipos."\");
".$silicaxt."
". $fertxt."
mygrid.init();
mygrid.adjustColumnSize(".$collist.");
mygrid.enableHeaderMenu(\"".$hidemenu."\");
mygrid.enablePaging(true,50,10,'pagingArea',true);
mygrid.setPagingSkin('bricks');
mygrid.loadXML(\"temp/".$fname."\");
dp = new dataProcessor(\"temp/".$fname."\");
dp.init(mygrid);
</script>";

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>