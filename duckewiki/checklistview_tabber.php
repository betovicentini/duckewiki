<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

$today = $_SESSION['sessiondate'];

$uuid = cleanQuery($_SESSION['userid'],$conn);
$acceslevel = cleanQuery($_SESSION['accesslevel'],$conn);

$species = 0;
$specimens = 0;
$plantas = 0;
$plots = 0;
$exportspecies = 0;
$exportspecimens = 0;
$exportplantas = 0;
$exportplots = 0;
if (($uuid+0)==0) {
if ($listsarepublic['species'] == 'on') {
  $species = 1;
} 
if ($listsarepublic['speciesdownload'] == 'on') {
  $exportspecies = 1;
} 
if ($listsarepublic['specimenes'] == 'on') {
$specimens = 1;
}
if ($listsarepublic['especimenesdownload'] == 'on') {
  $exportspecimens = 1;
} 
if ($listsarepublic['plantas'] == 'on') {
$plantas = 1;
}
if ($listsarepublic['plantasdownload'] == 'on') {
  $exportplantas = 1;
} 
if ($listsarepublic['plots'] == 'on') {
$plots=1;
}
if ($listsarepublic['plotsdownload'] == 'on') {
  $exportplots = 1;
} 
} else {
$species = 1;
$specimens = 1;
$plantas = 1;
$plots = 1;
if ($acceslevel!='visitor') {
$exportspecies = 1;
$exportspecimens = 1;
$exportplantas = 1;
$exportplots = 1;
}
}

//CHECA SE O USUARIO TEM PERMISSAO
if(($species+$specimens+$plantas+$plots)==0) {
	header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//EXTRAI A URL 
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);

$title = $sitetitle;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel=\"stylesheet\" type=\"text/css\" href=\"dhtmlxconnector/dhtmlxTabbar/dhtmlxtabbar.css\">",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn_bricks.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxCalendar/codebase/dhtmlxcalendar.css'>",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_skyblue.css'>"
);
$which_java = array(
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxcommon.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxTabbar/dhtmlxtabbar.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxTabbar/dhtmlxcontainer.js'></script>",
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
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>",
"<script type='text/javascript'>

function exportarCSV(gridobject, coltoexport) {
         var tot = gridobject.getRowsNum();
         if (tot>10000) {
         	alert('Tem limite de no máximo 10000 registros para isso e seu filtro tem '+tot+' registros. Refinar sua busca antes de exportar');
         } else {
         var cco = document.getElementById('counter');
         cco.innerHTML = 'Processando '+tot +' registros, aguarde...';
         setTimeout(function(){ 
         gridobject.setSerializableColumns(coltoexport);
         gridobject.enableCSVHeader(true);
         gridobject.setCSVDelimiter('\t');
         var csv = gridobject.serializeToCSV(true);
         var w = window.open('exportGRIDasCSV.php', name='_blank',specs='scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=600,height=500');
         w.document.write('<pre>'+csv+'</pre>');
         cco.innerHTML = '';
         w.focus(); 
         },0);
         } 
}

function salvarmarcados(gridobject, tablename,usertbname ) {
         //var tt = gridobject.getAllItemIds();
         //var divbar = document.getElementById('passing_ids');
         //divbar.innerHTML  = tt;
         small_window('checklist_filtro.php?tbname='+tablename+'&usertbname='+usertbname,600,500,'Salvar filtro');
}      
      
function marcarfiltrados(excluir, gridobject, tablename,usertbname) {
         var tot = gridobject.getRowsNum();
         var tt = gridobject.getAllItemIds();
         var ttt = tt.split(',');
         var lentt = ttt.length;         
         if (lentt<tot) {
            alert('Precisa navegar pelas páginas do grid para carregar os '+tot+' registros. Por enquanto apenas ' + lentt + ' registros foram carregados');
         } else {
         var divbar = document.getElementById('pgbar'+tablename);
         divbar.innerHTML = 'Processando '+lentt +' registros, aguarde...';
         var cco = document.getElementById('counter');
         cco.innerHTML = 'Processando '+lentt +' registros, aguarde...';
         setTimeout(function(){ 
         var conta = 0;
         var cco = document.getElementById('counter');
         /*
          ttt = Array(gridobject.getRowsNum());
          var notff = 0;
          for (var i=0; i<gridobject.getRowsNum(); i++){
		         var idd = gridobject.getRowId(i);
		         if (idd>0) {
		           ttt[i] = idd;
		         } else {
			         notff++;
		         }
		 }
		 tt = ttt.join(',');
		 alert(notff+ 'ainda não estavam carregados');
		 var lentt = ttt.length;
         */
         if (excluir) {
           var oque = 'desmarcados';
           var status = 0;
          }  else {
            var oque = 'marcados';
            var status = 1;
          }
         var loader = dhtmlxAjax.postSync(\"checklistview_tabber_rectagging.php\", encodeURI('tempids='+tt+'&table='+tablename+'&status='+status+'&usertbname='+usertbname+'&uuid=".$uuid."'));
        var res = loader.xmlDoc.responseText;
         for (var i=0; i<lentt; i++){
              var valor = gridobject.cells2(i, 0).getValue();
              if (valor==0 & excluir==false) {
                 gridobject.cells2(i, 0).setValue(1);
                 conta++;
              }
              if (valor==1 & excluir==true) {
                 gridobject.cells2(i, 0).setValue(0);
                 conta++;
              }
              var newValue = (Math.round(i/lentt)*100);
              cco.innerHTML = 'Processando '+i+' de um total de '+lentt +' registros ('+newValue+'%), aguarde...';
          }
          /*
          if ((tot-conta)>0) {
              var dif = (tot-conta);
              var txt = 'Foram  ' + oque + '  ' +conta + ' registros de um total de ' + tot + ' (sendo que ' + dif + ' já estavam '+ oque + ')';
          } else {
              var txt = 'Foram ' + oque + '  ' + conta + ' registros de um total de ' + tot ;
          }
          divbar.innerHTML = txt;
          document.getElementById('counter').innerHTML = txt;   
          */
          res = res;
          alert(res);
          divbar.innerHTML = '';
          cco.innerHTML = '';
         }, 0);
         }
}
function amostrasilica(especid, pltid) {
         setTimeout(function(){ 
            var loader = dhtmlxAjax.postSync(\"amostrasilica.php\", encodeURI('especimenid='+especid+'&plantaid='+pltid+'&uuid=".$uuid."&hoje=".$today."'));
            var res = loader.xmlDoc.responseText;
            alert(res);
         }, 0);
}
function desmarcartodos(gridobject, tablename) {
         var tot = gridobject.getRowsNum();
         var tt = gridobject.getAllItemIds();
         var ttt = tt.split(',');
         var lentt = ttt.length;         
         var divbar = document.getElementById('pgbar'+tablename);
         divbar.innerHTML = 'Desmarcando todos os registros, aguarde...';
         var cco = document.getElementById('counter');
         cco.innerHTML = 'Desmarcando todos registros, aguarde...';
         setTimeout(function(){ 
         var conta = 0;
         var cco = document.getElementById('counter');
         var status = 0;
         var loader = dhtmlxAjax.postSync(\"checklistview_tabber_apagatodos.php\", encodeURI('table='+tablename+'&status='+status+'&uuid=".$uuid."'));
        var res = loader.xmlDoc.responseText;
         for (var i=0; i<lentt; i++){
              var valor = gridobject.cells2(i, 0).getValue();
              gridobject.cells2(i, 0).setValue(0);
          }
          alert(res);
          divbar.innerHTML = '';
          cco.innerHTML = '';
         }, 0);
}
</script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>"
);
FazHeader($title,$body,$which_css,$which_java,TRUE);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
$quais = ReturnAcessList($acclevel);
$_SESSION['introtext'] = $introtext;
omenudeicons($quais, $vertical=FALSE, $position='left' , $iconwidth='30', $iconheight='30' );
//CAIXA DE DIALOGO PARA PEDIR NOME 
//echo "<div id='passing_ids'></div>";
if (($blockacess+0)==0) {
	//$checklistarray = unserialize($_SESSION['checklistarray']);
	$divwith = 1000;
	$divheight = 400;
	$tbwidth = $divwith+10;
	$tbheight = 500;
echo "
<span id='counter' style=\"padding: 1px; color:  red; font-size: 1.8em;\"></span>
<div style=\"position:absolute; top:0%; left:5%; width: 40%; padding: 20px; background-color:orange; color:  black; visibility: hidden;\" id=\"progressalert\"  ></div>
<div id=\"a_tabbar\" style=\"position: absolute; top: 175px;  width:".$tbwidth."px; height:".$tbheight."px;\">";
if ($species==1) {
   $taxonlist = unserialize($_SESSION['checklist_species']);
echo "
<table id=\"paging_container\">
    <tr>
      <td>    
         <div id=\"gridbox\" style=\"width:".$divwith."px; height:".$divheight."px;\"></div>
      </td>
   </tr>
   <tr>
      <td id=\"pagingArea\"></td>
   </tr>
    <tr>
      <td>   ";
if ($exportspecies==1) {      
echo "   <input type='button' onclick=\"marcarfiltrados(false,mygrid, 'checklist_all','".$taxonlist['usertbname']."');\" onmouseover=\"Tip('Marcar/selecionar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1em; padding: 2px; cursor:pointer;\"  value='Marcar filtrados' />
&nbsp;
<input  type='button' onclick=\"desmarcartodos(mygrid, 'checklist_all');\" onmouseover=\"Tip('Desmarcar todos os registros marcados nesta planilha!');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Desmarcar todos' />
&nbsp;
<input  type='button'  onclick=\"exportarCSV(mygrid,'".$taxonlist['exportcols']."');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Exportar Filtrados'  onmouseover=\"Tip('Exportar os registros que estão filtrados - DICA: marcar os que deseja e filtrar por Marcado=1');\" />
<span id='pgbarchecklist_all' ></span>    
";
}
echo "   
      </td>
   </tr>
</table>";
}
if ($specimens==1) {
$specimenlist = unserialize($_SESSION['checklist_specimens']);
echo "
<table id=\"paging_container2\">
    <tr>
      <td>
         <div id=\"gridbox2\" style=\"width:".$divwith."px; height:".$divheight."px;\"></div>
      </td>
   </tr>
   <tr>
      <td id=\"pagingArea2\"></td>
   </tr>
    <tr>
      <td>    ";
if ($exportspecimens==1) {      
echo "       
   <input type='button' onclick=\"marcarfiltrados(false,myspecgrid, 'checklist_speclist','".$specimenlist['usertbname']."');\" onmouseover=\"Tip('Marcar/selecionar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Marcar filtrados' />
&nbsp;
<input  type='button' onclick=\"desmarcartodos(myspecgrid, 'checklist_speclist');\" onmouseover=\"Tip('Desmarcar todos os registros marcados nesta planilha!');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Desmarcar todos' />
&nbsp;";
if ($uuid>0) {      
echo "       
<input  id='myspecgrid_button' type='button'  onclick=\"salvarmarcados(myspecgrid, 'checklist_speclist','".$specimenlist['usertbname']."');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Filtro - salvar marcados' />
&nbsp;
";
}
echo "       
<input  type='button'  onclick=\"exportarCSV(myspecgrid,'".$specimenlist['exportcols']."');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Exportar Filtrados'  onmouseover=\"Tip('Exportar os registros que estão filtrados - DICA: marcar os que deseja e filtrar por Marcado=1');\" />
<span id='pgbarchecklist_speclist' ></span>    
";
}
//onclick=\"salvarmarcados(myspecgrid, 'checklist_speclist');\" onmouseover=\"Tip('Salvar os registros marcados como um filtro');\" 
echo "
      </td>
   </tr>
</table>";
}
if ($plantas==1) {

$plantaslist = unserialize($_SESSION['checklist_plantas']);
echo "
<table id=\"paging_container3\">
    <tr>
      <td>
         <div id=\"gridbox3\" style=\"width:".$divwith."px; height:".$divheight."px;\"></div>
      </td>
   </tr>
   <tr>
      <td id=\"pagingArea3\"></td>
   </tr>
 <tr>
      <td>      ";
if ($exportplantas==1) {      
echo "       
   <input type='button' onclick=\"marcarfiltrados(false,myplgrid, 'checklist_pllist','".$plantaslist['usertbname']."');\" onmouseover=\"Tip('Marcar/selecionar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Marcar filtrados' />
&nbsp;
<input  type='button' onclick=\"desmarcartodos(myplgrid, 'checklist_pllist');\" onmouseover=\"Tip('Desmarcar todos os registros marcados nesta planilha!');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Desmarcar todos' />
&nbsp;
";
if ($uuid>0) {
echo "     
<input  id='myplgrid_button' type='button'  onclick=\"salvarmarcados(myplgrid, 'checklist_pllist','".$plantaslist['usertbname']."');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Filtro - salvar marcados' />
&nbsp;
 ";
 }
echo"
<input  type='button'  onclick=\"exportarCSV(myplgrid,'".$plantaslist['exportcols']."');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Exportar Filtrados' onmouseover=\"Tip('Exportar os registros que estão filtrados - DICA: marcar os que deseja e filtrar por Marcado=1');\" />
<span id='pgbarchecklist_pllist' ></span> ";
}
echo"
      </td>
   </tr>   
</table>";
}
if ($plots==1) {
$plotlist = unserialize($_SESSION['checklist_plots']);
echo "
<table id=\"paging_container4\">
    <tr>
      <td>
         <div id=\"gridbox4\" style=\"width:".$divwith."px; height:".$divheight."px;\"></div>
      </td>
   </tr>
   <tr>
      <td id=\"pagingArea4\"></td>
   </tr>
    <tr>
      <td>    ";
if ($exportplots==1) {      
echo "       
   <input type='button' onclick=\"marcarfiltrados(false,myplotgrid, 'checklist_plots','".$plotlist['usertbname']."');\" onmouseover=\"Tip('Marcar/selecionar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Marcar filtrados' />
&nbsp;
<input  type='button' onclick=\"desmarcartodos(myplotgrid, 'checklist_plots');\" onmouseover=\"Tip('Desmarcar todos os registros marcados nesta planilha!');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Desmarcar todos' />
&nbsp;
<input  type='button'  onclick=\"exportarCSV(myplotgrid,'".$plotlist['exportcols']."');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Exportar Filtrados' onmouseover=\"Tip('Exportar os registros que estão filtrados');\"/>
<span id='pgbarchecklist_plots' ></span>";
}
echo "
      </td>
   </tr>   
</table>";
}

echo "
</div>
<script type='text/javascript' >
tabbar = new dhtmlXTabBar(\"a_tabbar\", \"top\");
tabbar.setSkin('silver');
tabbar.setImagePath(\"dhtmlxconnector/dhtmlxTabbar/imgs/\");
";
if ($species==1) {
echo "
tabbar.addTab(\"a1\", \"Checklist\", \"100px\");
tabbar.setContent(\"a1\",\"paging_container\");
";
}
if ($specimens==1) {
echo "
tabbar.addTab(\"a2\", \"Especímenes\", \"100px\");
tabbar.setContent(\"a2\",\"paging_container2\");
";
}
if ($plantas==1) {
echo "
tabbar.addTab(\"a3\", \"Plantas\", \"100px\");
tabbar.setContent(\"a3\",\"paging_container3\");
";
}
if ($plots==1) {
echo "
tabbar.addTab(\"a4\", \"Localidades & Plots\", \"120px\");
tabbar.setContent(\"a4\",\"paging_container4\");
";
}

if ($species==1) {

$ff = explode(",",$taxonlist['ffields']);
$hh = explode(",",$taxonlist['headertxt']);
$newh = array();
foreach ($ff as $kk => $vv) {
    $tz = $hh[$kk]."  Clique para ordenar!"; 
    $nn = "<span style='cursor:help;' onmouseover=\\\"javascript:Tip('".$tz."');\\\">".$vv."</span>";
    $newh[] = $nn;
}
$newheader = implode(",",$newh);
echo "
tabbar.setTabActive(\"a1\");
mygrid =  new dhtmlXGridObject(\"gridbox\");
mygrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
mygrid.setIconPath(\"icons/\");
mygrid.setHeader(\"".$newheader."\");
mygrid.setInitWidths(\"".$taxonlist['colw']."\");
mygrid.setColAlign(\"".$taxonlist['colalign']."\");
mygrid.setSkin(\"light\");
mygrid.attachHeader(\"".$taxonlist['filtros']."\");
mygrid.setColSorting(\"".$taxonlist['filtros2']."\");
mygrid.enableColumnMove(false);
mygrid.setColumnsVisibility(\"".$taxonlist['listvisible']."\");
mygrid.setColTypes(\"".$taxonlist['coltipos']."\");
//mygrid.enableMultiline(true);
//mygrid.attachEvent(\"onCheckbox\", doOnCheck);
//mygrid.attachEvent(\"onMouseOver\", function(id,ind){
//this.cells(id,ind).cell.onmouseover = 'The index of this column is '+ind;
//return false;
//});
mygrid.init();
//mygrid.enableCSVAutoID(true);
//mygrid.adjustColumnSize(".$taxonlist['collist'].");
mygrid.enableHeaderMenu(\"".$taxonlist['hidemenu']."\");
mygrid.enablePaging(true,300,10,'pagingArea',true);
mygrid.setPagingSkin('bricks');
mygrid.loadXML(\"temp/".$taxonlist['fname']."\");
dp = new dataProcessor(\"temp/".$taxonlist['fname']."\");
dp.init(mygrid);
";
}

if ($specimens==1) {
$ff = explode(",",$specimenlist['ffields']);
$hh = explode(",",$specimenlist['headertxt']);
$newh = array();
foreach ($ff as $kk => $vv) {
    $tz = $hh[$kk]." Clique para ordenar!"; 
    $nn = "<span style='cursor:help;' onmouseover=\\\"javascript:Tip('".$tz."');\\\">".$vv."</span>";
    $newh[] = $nn;
}
$newheader = implode(",",$newh);
echo "
myspecgrid = new dhtmlXGridObject(\"gridbox2\");
myspecgrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
myspecgrid.setHeader(\"".$newheader."\");
myspecgrid.setInitWidths(\"".$specimenlist['colw']."\");
myspecgrid.setColAlign(\"".$specimenlist['colalign']."\");
myspecgrid.setSkin(\"light\");
myspecgrid.attachHeader(\"".$specimenlist['filtros']."\");
myspecgrid.setColSorting(\"".$specimenlist['filtros2']."\");
//myspecgrid.enableMultiline(true);
myspecgrid.enableColumnMove(false);
myspecgrid.setColumnsVisibility(\"".$specimenlist['listvisible']."\");
myspecgrid.setColTypes(\"".$specimenlist['coltipos']."\");
myspecgrid.init();
myspecgrid.enableCSVAutoID(true);
myspecgrid.adjustColumnSize(".$specimenlist['collist'].");
myspecgrid.enableHeaderMenu(\"".$specimenlist['hidemenu']."\");
myspecgrid.enablePaging(true,500,10,'pagingArea2',true);
myspecgrid.setPagingSkin('bricks');
myspecgrid.loadXML(\"temp/".$specimenlist['fname']."\");
dpspec = new dataProcessor(\"temp/".$specimenlist['fname']."\");
dpspec.init(myspecgrid);
";
}

if ($plantas==1) {

$ff = explode(",",$plantaslist['ffields']);
$printtt = count($ff)."  number ffields";
$hh = explode(",",$plantaslist['headertxt']);
//$printtt .= "<br >".count($hh)."  number headertxt";

$newh = array();
foreach ($ff as $kk => $vv) {
    $tz = $hh[$kk]."  Clique para ordenar!"; 
    $nn = "<span style='cursor:help;' onmouseover=\\\"javascript:Tip('".$tz."');\\\">".$vv."</span>";
    $newh[] = $nn;
}
$plnewheader = implode(",",$newh);
//$printtt .= "<br >".count($newh)."  number ffields";
echo "
myplgrid = new dhtmlXGridObject(\"gridbox3\");
myplgrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
myplgrid.setHeader(\"".$plnewheader."\");
myplgrid.setInitWidths(\"".$plantaslist['colw']."\");
myplgrid.setSkin(\"light\");
myplgrid.attachHeader(\"".$plantaslist['filtros']."\");
myplgrid.setColSorting(\"".$plantaslist['filtros2']."\");
myplgrid.setColAlign(\"".$plantaslist['colalign']."\");
myplgrid.enableColumnMove(true);
myplgrid.setColumnsVisibility(\"".$plantaslist['listvisible']."\");
myplgrid.setColTypes(\"".$plantaslist['coltipos']."\");
myplgrid.init();
myplgrid.enableCSVAutoID(true);
myplgrid.adjustColumnSize(".$plantaslist['collist'].");
myplgrid.enableHeaderMenu(\"".$plantaslist['hidemenu']."\");
myplgrid.enablePaging(true,500,10,'pagingArea3',true);
myplgrid.setPagingSkin('bricks');
myplgrid.loadXML(\"temp/".$plantaslist['fname']."\");
dppll = new dataProcessor(\"temp/".$plantaslist['fname']."\");
dppll.init(myplgrid);
";
}

if ($plots==1) {
$ff = explode(",",$plotlist['ffields']);
$hh = explode(",",$plotlist['headertxt']);
$newh = array();
foreach ($ff as $kk => $vv) {
    $tz = $hh[$kk]."  Clique para ordenar!";  
    $nn = "<span style='cursor:help;' onmouseover=\\\"javascript:Tip('".$tz."');\\\">".$vv."</span>";
    $newh[] = $nn;
}
$plotnewheader = implode(",",$newh);
echo "
myplotgrid = new dhtmlXGridObject(\"gridbox4\");
myplotgrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
myplotgrid.setHeader(\"".$plotnewheader."\");
myplotgrid.setInitWidths(\"".$plotlist['colw']."\");
myplotgrid.setSkin(\"light\");
myplotgrid.attachHeader(\"".$plotlist['filtros']."\");
myplotgrid.setColSorting(\"".$plotlist['filtros2']."\");
myplotgrid.setColAlign(\"".$plotlist['colalign']."\");
myplotgrid.enableColumnMove(true);
myplotgrid.setColumnsVisibility(\"".$plotlist['listvisible']."\");
myplotgrid.setColTypes(\"".$plotlist['coltipos']."\");
myplotgrid.init();
myplotgrid.enableCSVAutoID(true);
myplotgrid.adjustColumnSize(".$plotlist['collist'].");
myplotgrid.enableHeaderMenu(\"".$plotlist['hidemenu']."\");
myplotgrid.enablePaging(true,500,10,'pagingArea4',true);
myplotgrid.setPagingSkin('bricks');
myplotgrid.loadXML(\"temp/".$plotlist['fname']."\");
dpplot = new dataProcessor(\"temp/".$plotlist['fname']."\");
dpplot.init(myplotgrid);
";
}
echo "
</script>";
}

//border:1px solid #cccccc;  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; 
//$stilo =" cursor: pointer;";
//echo "
//<div style='position: absolute; top: 150px; right: 30px;'>
// <img style=\"".$stilo."\"  height='80px' src='icons/inpa_principal.jpg' onmouseover = \"javascript: Tip('Instituto Nacional de Pesquisas da Amazônia') ;\"  onclick = \"javascript: window.open('http://www.inpa.gov.br','_blank' );\" />
//  <br />
//    <br />
//   <img style=\"".$stilo."\"  height='30px' src='icons/ctfs_logo.png' onmouseover = \"javascript: Tip('CTFS - Center for Tropical Forest Science') ;\"  onclick = \"javascript: window.open('http://www.forestgeo.si.edu','_blank' );\" />
//</div>";
//echo $printtt;
//echopre($plantaslist);

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);

FazFooter($which_java,$calendar=FALSE,$footer=TRUE);
?>