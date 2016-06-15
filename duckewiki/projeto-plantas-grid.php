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

//CABECALHO
$menu = FALSE;
$title = 'Plantas  Projeto';
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
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgridcell.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxCalendar/codebase/dhtmlxcalendar.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_export.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_ssc.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_mcol.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js'></script>",
"<script type='text/javascript'>
  function filtrarmarcados(marcados) {
     mygrid.clearAll();
     if (marcados==1) {
       document.getElementById('pgbar').innerHTML = 'Filtrando aguarde!';
       mygrid.loadXML(\"temp/".$fname."?marcado=1\");
       document.getElementById('pgbar').innerHTML = '';
    } else {
       document.getElementById('pgbar').innerHTML = '';
       mygrid.loadXML(\"temp/".$fname."\");
    }
  }
function fecharwin() {
  var el = self.opener.window.document.getElementById('plantastxt');
  var curres = document.getElementById('counter').innerHTML;
  el.innerHTML = curres + ' plantas selecionadas ';
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
}
     function marcarfiltrados(excluir) {
       var tot = mygrid.getRowsNum();
         var tt = mygrid.getAllItemIds();
         var ttt = tt.split(',');
         var lentt = ttt.length;         
         if (lentt<tot) {
            alert('Precisa navegar pelas páginas do grid para carregar os '+tot+' registros. Por enquanto apenas ' + lentt + ' registros foram carregados');
         } else {
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
        var loader = dhtmlxAjax.postSync(\"projeto-plantas-process.php\", encodeURI('plantas='+tt+'&projetoid=".$projetoid."&status='+status+'&uuid=".$uuid."') );
        var res = loader.xmlDoc.responseText;
        document.getElementById('counter').innerHTML = res;
        res = txt + '  '+ res;
        alert(res);
         }, 0);
         }
    }
    function desmarcartodos(gridobject) {
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
         var loader = dhtmlxAjax.postSync(\"projeto-amostras-process.php\", encodeURI('projetoid=".$projetoid."&status='+status+'&uuid=".$uuid."&apagatodos=1') );
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
FazHeader($title,$body,$which_css,$which_java,$menu);
//               //mygrid.cells(id,0).setValue(true);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";

$qq = "SELECT * FROM `ProjetosEspecs` WHERE PlantaID>0 AND ProjetoID=".$projetoid;
@$rr =  mysql_query($qq,$conn);
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
O Projeto contém <span id='counter'>".$nrr."</span> plantas
<br />
&nbsp;
<input  type='button' onclick=\"javascript:filtrarmarcados(1);\" onmouseover=\"Tip('Mostrar registros marcados');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value=\"Ver marcados\" />
&nbsp;
<input  type='button' onclick=\"javascript:filtrarmarcados(0);\" onmouseover=\"Tip('Mostrar todos os registros');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value=\"Ver todos\" />
&nbsp;
<input type='button' onclick=\"fecharwin();\"  onmouseover=\"Tip('Fecha a janela');\"  style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Fechar' />
&nbsp;
<input type='button'  onmouseover=\"Tip('Marca como do projeto plantas que estão em um filtro');\"  style=\"color:#33C4FF; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Importar de filtro' ";
$myurl ="projeto-fromfiltro.php?saoplantas=1&projetoid=".$projetoid;
echo " onclick = \"javascript:small_window('".$myurl."',500,300,'Importa amostras de um filtro');\" />
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
&nbsp;
<input type='button' onclick=\"marcarfiltrados(false)\" onmouseover=\"Tip('Incluir/selecionar para a projeto os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Incluir filtrados' />
&nbsp;
<input  type='button' onclick=\"marcarfiltrados(true)\" onmouseover=\"Tip('Excluir/desmarcar do projeto os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Excluir filtrados' />
&nbsp;
<input  type='button' onclick=\"desmarcartodos(mygrid);\" onmouseover=\"Tip('Desmarcar todos os registros marcados nesta planilha!');\" style=\"color:#4E889C; font-size: 1em;   padding: 2px; cursor:pointer;\"  value='Desmarcar todos' />
&nbsp;
<input type='button' onclick=\"fecharwin()\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Fechar' />
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
mygrid.enablePaging(true,500,10,'pagingArea',true);
mygrid.setPagingSkin('bricks');
mygrid.loadXML(\"temp/".$fname."\");
dp = new dataProcessor(\"temp/".$fname."\");
dp.init(mygrid);
</script>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>