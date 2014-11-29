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
if (isset($_SESSION['arrtopass'])) {
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

$qq = "SELECT DISTINCT(PlantaID) FROM `Monitoramento` WHERE CensoID=".$censoid."  OR (CensoID IS NULL)";
$rr =  mysql_query($qq,$conn);
$nrr = mysql_numrows($rr);




$title = 'Plantas do Censo';
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

        
$hh = explode(",",$ffields);
$nf = count($hh);

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
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_export.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js'></script>",
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
         w.document.write('<pre>'+csv+'</pre>');
         w.focus(); 
      };
      function fecharwin() {
            var el = self.opener.window.document.getElementById('nplantastxt');
            var curres = document.getElementById('counter').innerHTML;
            el.innerHTML = curres + ' plantas selecionados ';
            window.close();
      };
      function tempAlert(msg,duration) {
        var el = document.createElement(\"div\");
        el.setAttribute(\"style\",\"position:absolute;top:50%;left:50%; width: 40%; padding: 10px; background-color:orange; color:  black;\");
        el.innerHTML = msg;
        setTimeout(function(){
            el.parentNode.removeChild(el);
        },duration);
        document.body.appendChild(el);
    };
</script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>"
);

//$body = "onload=message()";

FazHeader($title,$body,$which_css,$which_java,$menu);
//               //mygrid.cells(id,0).setValue(true);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
$qq = "SELECT DISTINCT(PlantaID) FROM `Monitoramento` WHERE CensoID=".$censoid;
$rr =  mysql_query($qq,$conn);
$nrr = mysql_numrows($rr);
$rr =  mysql_query("SELECT * FROM `Monitoramento` WHERE CensoID='".$censoid."'",$conn);
$nmed = mysql_numrows($rr);
$divwith = 1000;
$divheight = 400;
echo"
<span id='counter'>O Censo contém ".$nmed." medições  de ".$nrr." árvores</span> . 
<span id='message'  style='color: red; font-size: 1.5em; font-weight:bold; padding: 4px;' ></span>
<br />
<input type='button' onclick=\"fecharwin()\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Fechar' />
&nbsp;
<input type='button' onclick=\"marcarfiltrados(false)\" onmouseover=\"Tip('Incluir/selecionar para o CENSO registros filtrados');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Incluir filtrados' />
&nbsp;
<input  type='button' onclick=\"marcarfiltrados(true)\" onmouseover=\"Tip('Excluir/desmarcar do CENSO os registros que estão filtrados');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Excluir filtrados' />
&nbsp;
<input type='button' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  onclick=\"javascript:applyfilter();\" value='Filtrar' );\"  onmouseover=\"Tip('Filtrar registros segundo busca indicada na caixa embaixo de cada coluna.<br >No caso de múltiplas colunas irá usar o argumento AND.<br> Simbolos de &gt; &lt; e = podem especificar buscas numéricas.<br >O símbolo ! antes de uma palavra irá retornar diferente dessa palavra.<br >O símbolo = antes de palavra ou número retorn valores identicos.');\"/>
<span id='pgbar' ></span>
<table>";
$stilo =" border:1px solid #cccccc;  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;";
echo "
<tr>
  <td>
    <div id=\"gridbox\" style=\"width:".$divwith."px; height:".$divheight."px;\"></div>
  </td>
</tr>
<tr>
<td ><div id=\"pagingArea\"></div></td>
</tr>
<tr>
<td >
<input type='button' onclick=\"fecharwin()\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Fechar' />
&nbsp;
<input type='button' onclick=\"marcarfiltrados(false)\" onmouseover=\"Tip('Incluir/selecionar para o CENSO os registros que estão filtrados');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Incluir filtrados' />
&nbsp;
<input  type='button' onclick=\"marcarfiltrados(true)\" onmouseover=\"Tip('Excluir/desmarcar do CENSO os registros que estão filtrados');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Excluir filtrados' />
&nbsp;
<input type='button' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  onclick=\"javascript:updateOutput();\" value='Exportar TXT' onmouseover=\"Tip('Exporta como TXT separado por TAB. Irá exportar os registros que foram carregados. FIXME<br />');\" />
</td>
</tr>    
</table>
<script>
function doOnCheck(rowId, cellInd, state) {
var curres = document.getElementById('counter').innerHTML;
if (state==1) {
curres++;
document.getElementById('pgbar').innerHTML = 'Foi adicionado 1 registro';
} else {
curres = curres-1;
document.getElementById('pgbar').innerHTML = 'Foi removido 1 registro';
}
document.getElementById('counter').innerHTML = curres;
return true;
}
function showLoading(fl) {
    var span = document.getElementById(\"message\");
    if (!span) {
        return;
    }
    if (!mygrid._serialise) {
        span.innerHTML = \"<i>Carregando, aguarde....</i>\";
        return;
    }
    if (fl === true) {
      span.innerHTML = \"Carregando, aguarde...\";
    }
    else {
      span.innerHTML = \"\"; 
    }
}
function sortGridOnServer(columnIndex,sortType,sortDirection){
       mygrid.clearAll();
       mygrid.loadXML(\"temp/".$fname."?orderby=\"+columnIndex+\"&amp;direct=\"+sortDirection);
       mygrid.setSortImgState(true,columnIndex,sortDirection);
       return false;
}
function applyfilter() {";

$hh = explode(",",$ffields);
$nf = count($hh);
echo "
mygrid.clearAll();
var url = Array();
var h =0;
for(var i = 0; i < ".$nf."; i++) { 
  var filtervalue =  mygrid.getFilterElement(i).value;
  if (filtervalue != null && filtervalue!='') {
     url[h] = 'colidx_'+i+'='+filtervalue;
     h = h+1;
  }
}
showLoading(true);
var resvv = url.join('&');
mygrid.loadXML(\"temp/".$fname."?filtrando=1&\"+resvv);
};
function donothing() {
  return false;
};
function marcarfiltrados(excluir) {
     var conta = 0;
     var tot = mygrid.getRowsNum();
     var divbar = document.getElementById('pgbar');
     divbar.innerHTML = 'Processando '+tot +' registros, aguarde...';
     setTimeout(function(){ 
        var url = Array();
        var h =0;
        for(var i = 0; i < ".$nf."; i++) { 
            var filtervalue =  mygrid.getFilterElement(i).value;
            if (filtervalue != null && filtervalue!='') {
                url[h] = 'colidx_'+i+'='+filtervalue;
                h = h+1;
            }
        }
        if (url.length>0) {
          var resvv = url.join('&');
          resvv = 'filtrando=1&'+resvv
        } else {
          var resvv = '';
        }
        //divbar.innerHTML = resvv;
        if (excluir) {
          status=0;
        } else {
          status=1;
        }
        var loader = dhtmlxAjax.postSync(\"censo-plantas-table-process.php\", encodeURI('censoid=".$censoid."&status='+status+'&uuid=".$uuid."&'+resvv) );
        var res = loader.xmlDoc.responseText;
        //alert(resvv);
        document.getElementById('counter').innerHTML = res;
        applyfilter();
        //res = txt + '  '+ res;
        //alert(res);
     }, 0);
};
mygrid = new dhtmlXGridObject(\"gridbox\");
mygrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
//mygrid.setIconPath(\"icons/\");
mygrid.setHeader(\"".$ffields."\");
mygrid.setInitWidths(\"".$colw."\");
mygrid.setColAlign(\"".$colalign."\");
mygrid.setColSorting(\"".$filtros2."\");
mygrid.setColTypes(\"".$coltipos."\");
mygrid.attachEvent(\"onBeforeSorting\", sortGridOnServer);
mygrid.attachEvent(\"onFilterStart\", donothing);
mygrid.setSkin(\"dhx_skyblue\");
mygrid.attachHeader(\"".$filtros."\");
mygrid.enableColumnMove(false);
mygrid.setColumnsVisibility(\"".$listvisible."\");
mygrid.enableMultiline(true);
mygrid.attachEvent(\"onCheckbox\", doOnCheck);
mygrid.init();
mygrid.enableHeaderMenu(\"".$hidemenu."\");
mygrid.enablePaging(true,100,10,'pagingArea',true);
mygrid.setPagingSkin('bricks');
mygrid.attachEvent(\"onXLE\", showLoading);
mygrid.attachEvent(\"onXLS\", function() { showLoading(true); });
mygrid.loadXML(\"temp/".$fname."\");
dp = new dataProcessor(\"temp/".$fname."\");
dp.init(mygrid);
</script>";
//mygrid.adjustColumnSize(".$collist.");
////mygrid.attachHeader(\"".$filtros3."\");
//if (mygrid.setColspan);
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>