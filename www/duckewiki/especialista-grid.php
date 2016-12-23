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

$title = 'Especialistas Botânicos';
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
     function MudaGenero(rwid,colid,especiaLid,famid,val) { 
        var txt =  '<span style=\'padding: 5px;\' ><img style=\'cursor:pointer;\' src=\'icons/genero.png\' height=\'14\' onclick=\"javascript:small_window(\'especialista-generos.php?rowid='+rwid+'&especialistaid='+especiaLid+'&famid='+famid+'&clidx='+colid+'\',700,400,\'Indicar gêneros\');\" onmouseover=\"Tip(\'Indicar gêneros do especialista, quando necessário\');\" ></span>';
        var tt = val+ '<br >'+ txt;
        mygrid.cells2(rwid,colid).setValue(tt);
     }  
</script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>"

);

FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
$clw = explode(",",$colw);
$lstvs = explode(",",$listvisible);
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
if($uuid>0 && $acclevel!='visitor') {
//<input type='button' style=\"cursor:pointer;\"  
if ($processoid>0) {
	$htxt = 'Lista dos especialistas das famílias  incluídas no processo em edição!';
} else {
	$htxt = 'Lista dos especialistas das famílias para as quais existem amostras na base!';
}
echo "
<tr>
  <td align='left'>
    <div style=\"position: relative; font-size: 1em; color: #990012\">
    <img src=\"icons/especialistas.png\" height='30' style=\"border:1px solid #cccccc;  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;\"  onmouseover=\"Tip('Cadastrar um novo especialista');\" onclick = \"javascript:small_window('novapessoa-form.php?submitted=novo&ispopup=1',800,500,'Pessoas');\"  />
&nbsp;".$htxt."</div>
  </td>
</tr>";
}
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
	echo "
<tr>
  <td align='left'>
    <div style=\"position: relative\">
  <input type='button' style=\"cursor:pointer;\"  onmouseover=\"Tip('Cadastrar um novo especialista');\" onclick = \"javascript:small_window('novapessoa-form.php?submitted=novo&ispopup=1',800,500,'Pessoas');\" value='Novo especialista'  />
";
if (!$processoid>0) {
echo "
&nbsp;
<input type='button' style=\"cursor:pointer;\"  onclick=\"javascript:add_r();\" value='Adicionar linha' onmouseover=\"Tip('Adicionar nova linha à tabela (será colocada na segunda posição)');\" />";
}
//echo "&nbsp;<input type='button' style=\"cursor:pointer;\"  onclick=\"javascript:updateOutput();\" value='Exportar TXT' onmouseover=\"Tip('Exporta como TXT separado por TAB');\" />
echo "
&nbsp;
  <input type='button' style=\"cursor:pointer;\"  onmouseover=\"Tip('Exportar planilha com especialistas');\" onclick = \"javascript:small_window('export-especialistas.php',700,500,'Especialistas');\" value='Exportar especialistas'  />
&nbsp;
  <input type='button' style=\"cursor:pointer;\"  onmouseover=\"Tip('Importar planilha com especialistas');\" onclick = \"javascript:small_window('import-especialistas-form.php',700,500,'Importar Especialistas');\" value='Importar especialistas'  />
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
function add_r() {
var nid = mygrid.getUID();
//var tt = '';
mygrid.addRow(nid,[,,,,,,] ,1);
var rowID=mygrid.getRowId(1);
mygrid.cells(rowID,5).setValue('Defina família e especialista');
}
function doOnCellEdit(stage,rId,cInd,nValue,oValue) {
    if (nValue!=oValue & stage==2 & cInd==3) {
        //pega familia id
        var vv2 = mygrid.cells(rId,3).getValue(); 
        //pega especialista id
        var vv3 = mygrid.cells(rId,0).getValue(); 
        var rowIndex=mygrid.getRowIndex(rId);
        if (vv2>0 & vv3>0) {
          var tt =  '<span style=\'padding: 5px;\' ><img style=\'cursor:pointer;\' src=\'icons/genero.png\' height=\'14\' onclick=\\\"javascript:small_window(\'especialista-generos.php?rowid='+rowIndex+'&clidx=5&especialistaid='+vv3+'&famid='+vv2+'\',700,400,\'Indicar gêneros\');\\\" onmouseover=\\\"Tip(\'Indicar gêneros do especialista, quando necessário\');\\\" ></span>';
           var oldv = mygrid.cells(rId,5).getValue(); 
           if (oldv=='Defina família e especialista' | oldv=='Defina especialista') {
              mygrid.cells(rId,5).setValue(tt);
           }
        }
    } else {
      if (cInd==1 & stage==2 & nValue>0) {
        var pess = mygrid.cells(rId,1).getValue(); 
        var loader = dhtmlxAjax.postSync(\"especialista-getemail.php\", encodeURI('pessoaid='+pess) );
        var res = loader.xmlDoc.responseText;
        mygrid.cells(rId,7).setValue(res);
         //mygrid.clearAll();
         //mygrid.loadXML(\"temp/".$fname."\");
      }
    }
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
mygrid.attachEvent(\"onEditCell\",doOnCellEdit);
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