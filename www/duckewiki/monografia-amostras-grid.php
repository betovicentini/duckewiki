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

//echopre($aarr);
//echopre($ppost);
//echopre($gget);

//CABECALHO
$menu = FALSE;

$title = 'Amostras Monografia';
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
            var el = self.opener.window.document.getElementById('especimenestxt');
            var curres = document.getElementById('counter').innerHTML;
            el.innerHTML = curres + ' especimenes selecionados ';
            window.close();
      }
      function tempAlert(msg,duration) {
        var el = document.createElement(\"div\");
        el.setAttribute(\"style\",\"position:absolute;top:50%;left:50%; width: 40%; padding: 10px; background-color:orange; color:  black;\");
        el.innerHTML = msg;
        setTimeout(function(){
            el.parentNode.removeChild(el);
        },duration);
        document.body.appendChild(el);
    };

function marcarfiltrados(excluir) {
         var conta = 0;
         var tot = mygrid.getRowsNum();
         var divbar = document.getElementById('pgbar');
         divbar.innerHTML = 'Processando '+tot +' registros, aguarde...';
         tempAlert( 'Processando '+tot +' registros, aguarde...', 10);
         setTimeout(function(){ 
         var tt = mygrid.getAllItemIds();
         for (var i=0; i<mygrid.getRowsNum(); i++){
              var valor = mygrid.cells2(i, 0).getValue();
              if (valor==0 & excluir==false) {
                 mygrid.cells2(i, 0).setValue(1);
                 conta++;
              }
              if (valor==1 & excluir==true) {
                 mygrid.cells2(i, 0).setValue(0);
                 conta++;
              }
              var newValue = (Math.round(conta/tot)*100);
          }
        if (excluir) {
        var oque = 'desmarcados';
        var status = 0;
        }  else {
        var oque = 'marcados';
        var status = 1;
        }
        if ((tot-conta)>0) {
        var dif = (tot-conta);
        var txt = 'Foram  ' + oque + '  ' +conta + ' registros de um total de ' + tot + ' (sendo que ' + dif + ' já estavam '+ oque + ')';
        } else {
        var txt = 'Foram ' + oque + '  ' + conta + ' registros de um total de ' + tot ;
        }
        divbar.innerHTML = txt;
        var loader = dhtmlxAjax.postSync(\"temp/".$processfname."\", encodeURI('especimenes='+tt+'&monografiaid=".$monografiaid."&varincluido='+status) );
        var res = loader.xmlDoc.responseText;
        document.getElementById('counter').innerHTML = res;
        res = txt + '  CONCLUIDO '+ res;
        //alert(res); 
        divbar.innerHTML = res;
         }, 0);
    }
</script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>"

);
FazHeader($title,$body,$which_css,$which_java,$menu);
//               //mygrid.cells(id,0).setValue(true);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";

$qq = "SELECT * FROM `".$tbname."` WHERE EspecimenID>0 AND Incluido>0 AND Incluido IS NOT NULL AND MonografiaID=".$monografiaid;
$rr =  mysql_query($qq,$conn);
$nrr = mysql_numrows($rr);
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
echo"
A Monografia contém <span id='counter'>".$nrr."</span> amostras
<br />
<input type='button' onclick=\"fecharwin()\" style=\"cursor:pointer;\"  value='Fechar' />
&nbsp;
<input type='button' onclick=\"marcarfiltrados(false)\" onmouseover=\"Tip('Incluir/selecionar para a monografia os registros que estão filtrados por qualquer coluna!');\" style=\"cursor:pointer;\"  value='Incluir filtrados' />
&nbsp;
<input  type='button' onclick=\"marcarfiltrados(true)\" onmouseover=\"Tip('Excluir/desmarcar da monografia os registros que estão filtrados por qualquer coluna!');\" style=\"cursor:pointer;\"  value='Excluir filtrados' />
&nbsp;
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
<input type='button' onclick=\"fecharwin()\" style=\"cursor:pointer;\"  value='Fechar' />
&nbsp;
<input type='button' onclick=\"marcarfiltrados(false)\" onmouseover=\"Tip('Incluir/selecionar para a monografia os registros que estão filtrados por qualquer coluna!');\" style=\"cursor:pointer;\"  value='Incluir filtrados' />
&nbsp;
<input  type='button' onclick=\"marcarfiltrados(true)\" onmouseover=\"Tip('Excluir/desmarcar da monografia os registros que estão filtrados por qualquer coluna!');\" style=\"cursor:pointer;\"  value='Excluir filtrados' />
&nbsp;
<input type='button' style=\"cursor:pointer;\"  onclick=\"javascript:updateOutput();\" value='Exportar TXT' onmouseover=\"Tip('Exporta como TXT separado por TAB<br />');\" />
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
mygrid = new dhtmlXGridObject(\"gridbox\");
mygrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
mygrid.setIconPath(\"icons/\");
mygrid.setHeader(\"".$ffields."\");
mygrid.setInitWidths(\"".$colw."\");
mygrid.setColAlign(\"".$colalign."\");
mygrid.setSkin(\"dhx_skyblue\");
mygrid.attachHeader(\"".$filtros."\");
//mygrid.attachHeader(\"".$filtros3."\");
mygrid.setColSorting(\"".$filtros2."\");
mygrid.enableColumnMove(false);
mygrid.setColumnsVisibility(\"".$listvisible."\");
mygrid.setColTypes(\"".$coltipos."\");
mygrid.enableMultiline(true);
mygrid.attachEvent(\"onCheckbox\", doOnCheck);
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