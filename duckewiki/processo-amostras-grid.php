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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}

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
"<script type='text/javascript'>
      function updateOutput() {
         mygrid.enableCSVHeader(true);
         mygrid.setCSVDelimiter('\t');
         var csv = mygrid.serializeToCSV(true);
         var w = window.open('exportGRIDasCSV.php', name='_blank',specs='scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=600,height=500');
         w.document.write('<textarea cols=500 rows=300>'+csv+'</textarea><br /><b>A primeira coluna não tem título</b>');
         w.focus(); 
      }
</script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>"

);
//         w.document.write('<textarea cols=100 rows=10>'+csv+'</textarea>');

//        w.document.write('<html><body>'+csv+'</body></html>');
//function(){  var csv=mygrid.serializeToCSV(); }
//          = csv;

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
if ($traitfertid>0) {
  $cabecas = explode(",",$ffields);
  $az = array_search('Fert',$cabecas);

  $qtf = "SELECT * FROM Traits WHERE ParentID=".$traitfertid;
  $restf = mysql_query($qtf,$conn);
  $nz = mysql_numrows($restf)-1;
  $i = 0;
  $fertxt = "mygrid.registerCList(".$az.", [";
  while ($rowtf = mysql_fetch_assoc($restf)) {
      $fertxt .=  "\"".$rowtf['TraitName']."\"";
      if ($i<$nz) {
	      $fertxt .= ", ";
      }
      $I++;
  }
     $fertxt .= "]);";
}

 $silicaxt = "";
// $traitsilica = 115;

if ($traitsilica>0) {
  $cabecas = explode(",",$ffields);
  $az = array_search('Silica',$cabecas);

  $qtf = "SELECT * FROM Traits WHERE ParentID=".$traitsilica;
  $restf = mysql_query($qtf,$conn);
  $nz = mysql_numrows($restf)-1;
  $i = 0;
  $silicaxt = "mygrid.registerCList(".$az.", [";
  while ($rowtf = mysql_fetch_assoc($restf)) {
      $silicaxt .=  "\"".$rowtf['TraitName']."\"";
      if ($i<$nz) {
	      $silicaxt .= ", ";
      }
  }
     $silicaxt .= "]);";
}



 $herbtxt = "";
if ($processoid>0) {
   $cabecas = explode(",",$ffields);
   $az = array_search('Herbaria',$cabecas);
  
	$qq = "SELECT Herbaria FROM ProcessosEspecs WHERE ProcessoID=".$processoid;
	$re = mysql_query($qq,$conn);
    $rwe = mysql_fetch_assoc($re);
	$herbaria = $rwe['Herbaria'];
	$herb = explode(";",$herbaria);
	$herbcl = array();
	 foreach ($herb as $hh) {
	 	$herbcl[] = trim($hh);
	 }
	
	$qn = "SELECT DISTINCT Herbaria FROM ".$tbname." WHERE Herbaria IS NOT NULL";
	$re = mysql_query($qq,$conn);
    while ($rwe = mysql_fetch_assoc($re)) {
    		$hb = explode(";",$rwe['Herbaria']);
    		foreach ($hb as $vv) {
    		$herbcl[] = trim($vv);
    		}
    }
	$herb = array_unique($herbcl);
     $nz = count($herb)-1;
     $i = 0;
  $herbtxt = "mygrid.registerCList(".$az.", [";
  foreach ($herb as $hh) {
      $hh = trim($hh);
      $herbtxt .=  "\"".$hh."\"";
      if ($i<$nz) {
	      $herbtxt .= ", ";
      }
  }
     $herbtxt .= "]);";
}

$divwith = array_sum($clw);
if ($divwith>1200) {
	$divwith = 1000;
}
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
if($uuid>0 && $acclevel!='visitor') {
	echo "
<tr>
  <td align='left'>
    <div style=\"position: relative\">
      <input type='button' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\" 
      onclick=\"mygrid.toExcel('dhtmlxconnector/server/generate.php');\" value='Exportar XLS' onmouseover=\"Tip('Exporta como planilha XLS');\" />
&nbsp;
<input type='button' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  onclick=\"javascript:updateOutput();\" value='Exportar TXT' onmouseover=\"Tip('Exporta como TXT separado por TAB<br />Rápido, mas precisa visualizar todas as páginas antes de clicar aqui!');\" />
    </div>
  </td>
</tr>";
//javascript:updateOutput();\" 
}
$tz = explode(",",$ffields);
$result = array();
foreach($tz as $kk => $vv) {
	$nvv = "<div >".$vv."</div>";
	$result[] = $nvv;
}
//$ffields = implode(",",$result);

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
".$herbtxt."
mygrid.init();
mygrid.adjustColumnSize(".$collist.");
mygrid.enableHeaderMenu(\"".$hidemenu."\");
mygrid.enablePaging(true,50,10,'pagingArea',true);
mygrid.setPagingSkin('bricks');
mygrid.loadXML(\"temp/".$fname."\");
dp = new dataProcessor(\"temp/".$fname."\");
dp.init(mygrid);
</script>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>