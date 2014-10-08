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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$menu=FALSE;
$title = 'Referências Bibliográficas';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
//"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >",
"<link rel=\"stylesheet\" type=\"text/css\" href=\"dhtmlxconnector/dhtmlxTabbar/dhtmlxtabbar.css\">",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn_bricks.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxCalendar/codebase/dhtmlxcalendar.css'>",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_skyblue.css'>"
);
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
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
      function sendbibtextref(reftxt,refid) {
          var ids = document.getElementById('resultado').value;
          var txt = document.getElementById('resultadotxt').value;
         // if (ids!='') {
               var el = self.opener.window.document.getElementById(refid);
               el.value = ids;
               var tt = self.opener.window.document.getElementById(reftxt);
               tt.innerHTML = txt;
               window.close();
         // } else {
             //alert('Não há nada selecionado para enviar');
          //}
      }
</script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>"

);
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
$divwith = 1000;
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
$stilo =" border:1px solid #cccccc;  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;";
echo"<table cellpadding='7'>";
if (!empty($bibtex_txt)) {
echo "<tr>
  <td>
    <span style='font-size: 1em; color: red;'>Selecionando referências</span><input type='button' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\" 
 onmouseover=\"Tip('Enviar referencias selecionadas');\" onclick = \"javascript:sendbibtextref('".$bibtex_txt."','".$bibtex_id."');\"  value='Enviar selecionados' />
  </td>
</tr>";
}
echo "<tr>
  <td>
    <div id=\"gridbox\" style=\"width:".$divwith."px; height:".$divheight."px;\"></div>
  </td>
</tr>
<tr>
<td ><div id=\"pagingArea\"></div></td>
</tr>
<tr>
<td >
<input type='button' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\" 
 onmouseover=\"Tip('Importar Referências');\" onclick = \"javascript:small_window('import-bibtex.php?ispopup=0',600,400,'Importar Referências Bibliográficas');\"  value='Importar BibTex' />
&nbsp;
<input type='button' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\" 
  onmouseover=\"Tip('Exportar Referências');\" onclick = \"javascript:small_window('bibtex_export.php?ispopup=0',600,400,'Exportar Referências Bibliográficas');\" value='Exportar BibTex' />
</td>
</tr>
<tr>
<td ><input type='hidden'  id='resultado'  value=''   /></td>
</tr>
<tr>
<td ><input type='hidden' id='resultadotxt'  value=''  /></td>
</tr>";
echo "
</table>
<script>
function doOnCheck(rowId, cellInd, state) {
    var curval = document.getElementById('resultado').value;
    var vvv = curval.split(';');
    var curvaltxt = document.getElementById('resultadotxt').value;
    var vvvtxt = curvaltxt.split(';');
    if (!state) {
      resval = Array();
      resvaltxt = Array();
      var idx =0;
      for (var i = 0; i < vvv.length; i++) {
        if  (vvv[i] != rowId) {
          resval[idx] = vvv[i];
          resvaltxt[idx] = vvvtxt[i];
          idx++;
        }
      }
      vvv = resval;
      vvvtxt = resvaltxt;
    } else {
      var coun = vvv.length;
      var rInd = mygrid.getRowIndex(rowId);
      if (coun>0 & vvv[0]!='') {
        vvv[coun] = rowId;
        /* EDITAR PARA PEGAR OUTRA COLUNA 3 = A bibkey */
        vvvtxt[coun] = mygrid.cellByIndex(rInd, 3).getValue();
      } else {
        vvv[0] = rowId;
        vvvtxt[0] = mygrid.cellByIndex(rInd, 3).getValue();
      }
    }
    resvv = vvv.join(';');
    resvvtxt = vvvtxt.join(';');
    //alert('User clicked on checkbox or radiobutton on row ' + rowId + ' and cell with index ' + cellInd + '.State changed to ' + state + '. And the Id value is ');
    document.getElementById('resultado').value = resvv;
    document.getElementById('resultadotxt').value = resvvtxt;
    return true;
}
mygrid = new dhtmlXGridObject(\"gridbox\");
mygrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
mygrid.setIconPath(\"icons/\");
mygrid.setHeader(\"".$ffields."\");
mygrid.setInitWidths(\"".$colw."\");
mygrid.setColAlign(\"".$colalign."\");
mygrid.setSkin(\"dhx_skyblue\");
mygrid.attachHeader(\"".$filtros."\");
mygrid.setColSorting(\"".$filtros2."\");
mygrid.enableColumnMove(false);
mygrid.setColumnsVisibility(\"".$listvisible."\");
mygrid.setColTypes(\"".$coltipos."\");
mygrid.attachEvent(\"onCheckbox\", doOnCheck);
mygrid.enableMultiline(true);
mygrid.init();
mygrid.adjustColumnSize(".$collist.");
mygrid.enableHeaderMenu(\"".$hidemenu."\");
mygrid.enablePaging(true,50,10,'pagingArea',true);
mygrid.setPagingSkin('bricks');
mygrid.loadXML(\"temp/".$fname."\");
dp = new dataProcessor(\"temp/".$fname."\");
dp.init(mygrid);
</script>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>