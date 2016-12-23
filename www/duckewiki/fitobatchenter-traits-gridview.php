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
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>",
"<script type='text/javascript'>
      function limpaEXISTE() {
        $.post('fitobatchenter-demarcaexiste.php', { tbname: '".$tbname."'}, function(data) {
        if (data=='Concluido') {
                mygrid.uncheckAll();
        }
        });
      }
</script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxcommon.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgridcell.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_export.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxCalendar/codebase/dhtmlxcalendar.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor_debug.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_ssc.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_mcol.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_validation.js'></script>",
"<script type='text/javascript'>
      function exportGRIDasCSV() {
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

//APARENTLY connector.js must be one of the last entries.
//,
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js'></script>",
//"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_skyblue.css'>"
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
if ($trclasses>0) {
	$trcl = unserialize($_SESSION['traitclasses']);
	//echopre($trcl);
}
$txtclasses = array();
$cabecas = explode(",",$ffields);
#$idx=1;
if (count($trcl)>0) {
foreach ($trcl as $kk => $vv) {
	$idx = array_search($kk,$cabecas);
  #$idx = $idx+1;
  $vvv = explode(",",$vv);
  $chartxt = "mygrid.registerCList(".$idx.", [";
  $i=1;
  #$idx++;
  foreach ($vvv as $state) {
      if ($i==1) {
          $chartxt .=  "\"".$state."\"";
      } else {
          $chartxt .=  ", \"".$state."\"";      
      }
      $i++;
  }
    $chartxt .= "]);";
    $txtclasses[] = $chartxt;
}
}

if ($sampletype!='especimenes') {
 $fertxt = "";
if ($traitfertid>0) {
  $cabecas = explode(",",$ffields);
  $az = array_search('TEMP_FERT',$cabecas);

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
	  $txtclasses[] = $fertxt;
}

//echopre($fertxt);
//echopre($txtclasses);
$divwith = array_sum($clw)+100;
if ($divwith>1200) {
	$divwith = 1000;
} elseif ($divwith<800) {
	$divwith = 800;
}
if ($nrecs<50) {
	$divheight = $nrecs*28.5;
	if ($divheight<150) {
	  $divheight = 200;
	}
	if ($divheight>600) {
		$divheight = 600;
	}
} else {
	$divheight = 600;
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
<td ><div id=\"pagingArea\"></div></td>
</tr>";
if($uuid>0 && $acclevel!='visitor') {
//ESSA EXPORTACAO NAO ESTA FUNCIONANDO... PRECISA ENTENDER PORQUE!
echo "
<tr>
  <td align='left'>
    <div style=\"position: relative\">
<input type='button' style=\"cursor:pointer;\" 
      onclick=\"mygrid.toExcel('dhtmlxconnector/dhtmlxGrid/codebase/grid-excel-php/generate.php');\" value='Exportar XLS' onmouseover=\"Tip('Exporta como planilha XLS');\" />
&nbsp;
<input type='button' style=\"cursor:pointer;\"  onclick=\"javascript:exportGRIDasCSV();\" value='Exportar TXT' onmouseover=\"Tip('Exporta como TXT separado por TAB<br />Rápido, mas precisa visualizar todas as páginas antes de clicar aqui!');\" />

";
if ($sampletype!='especimenes') {
echo "&nbsp;
<input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Gerar Amostras'  onclick = \"javascript:small_window('fitobatchenter-plantas-criaamostras.php?tbname=".$tbname."',700,500,'Gerar amostras para plantas marcadas');\" >
&nbsp;
<input  type='button'  style=\"cursor:pointer;\"   value='Exporta Tabela NIR'  onclick = \"javascript:small_window('export-nir-spreadsheet.php?wikiid=1&sampletype=plantas&tbname=".$tbname."&ispopup=1',650,300,'Exporta planilha NIR');\" >
&nbsp;
<input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Cria PROCESSO'  onclick = \"javascript:small_window('processo-amostras-form.php?final=1&tbname=".$tbname."&ispopup=1',650,300,'Cria PROCESSO');\" >
";

} else {
echo "
&nbsp;
<input  type='button'  style=\"cursor:pointer;\"   value='Exporta Tabela NIR'  onclick = \"javascript:small_window('export-nir-spreadsheet.php?wikiid=1&sampletype=specimens&tbname=".$tbname."&ispopup=1',650,300,'Exporta planilha NIR');\" >
";
}

echo "
&nbsp;
<input  type='button'  style=\"cursor:pointer;\"   value='Desmarca EXISTE'  onclick = \"javascript:limpaEXISTE();\" >
    </div>
  </td>
</tr>";
/////////////////javascript:small_window('fitobatchenter-demarcaexiste.php?tbname=".$tbname."&ispopup=1',650,300,'Desmarca os registros marcados como EXISTE');\" >

}
echo "
</table>
<script>
dhtmlxValidation.isWikiNum = function(vv) {
  var found = true;
  if (vv.value !== null) {
    var allvv = vv.split(\";\");
    var len = allvv.length;
    for(var count = 0; count < len+1; count++) {
      var curvv = allvv[count];
      if (curvv) {
        var n=curvv.match(\",\");
        if (n) {
          found = false;
          break;
        } else {
          if (curvv >=0 || curvv < 0) {
          } else {
            found = false;
            break;
          }
        }
      }
    }
  }
  return found;
}
mygrid = new dhtmlXGridObject(\"gridbox\");
mygrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
mygrid.setIconPath(\"icons/\");
mygrid.setHeader(\"".$ffields."\");
mygrid.setInitWidths(\"".$colw."\");
mygrid.setColAlign(\"".$colalign."\");
mygrid.setSkin(\"light\");
mygrid.attachHeader(\"".$filtros."\");
mygrid.setColSorting(\"".$filtros2."\");
mygrid.enableColumnMove(true);
mygrid.setColumnsVisibility(\"".$listvisible."\");
mygrid.enableValidation(\"".$colvalidtorf."\");
mygrid.setColValidators(\"".$colvalid."\");
mygrid.setColTypes(\"".$coltipos."\");";
foreach ($txtclasses as $clslists) {
	echo "
".$clslists;
}
echo "
mygrid.attachEvent(\"onValidationError\", function(id, ind, value) {
    mygrid.setCellTextStyle(id, ind, \"background-color:red;\");
    mygrid.cells(id, ind).setValue('');
    //document.getElementById('message').style.visibility= 'visible';
    //document.getElementById('message').innerHTML = 'ERRO: o valor entrado não é numérico!';
    alert('ERRO: o valor entrado não é numérico! Ou há letras ou vírgula como decimal em algum dos números informados');
    return false;
});
mygrid.attachEvent(\"onValidationCorrect\", function(id, ind, value) {
    mygrid.setCellTextStyle(id, ind, \"\");
    //document.getElementById('message').innerHTML = \"\";
    //document.getElementById('message').style.visibility= 'hidden';
    return false;
});
mygrid.init();
mygrid.adjustColumnSize(".$collist.");
mygrid.enableHeaderMenu(\"".$hidemenu."\");
mygrid.enablePaging(true,50,10,'pagingArea',true);
mygrid.setPagingSkin('bricks');
mygrid.loadXML(\"temp/".$fname."\");
dp = new dataProcessor(\"temp/".$fname."\");
dp.init(mygrid);
</script>";
//dp.enableUTFencoding(false);

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>