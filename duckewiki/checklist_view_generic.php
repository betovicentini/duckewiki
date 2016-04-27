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
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
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
if (count($ppost)==0 && count($gget)==0 && isset($_SESSION['arrofpass'])) {
	$aarr = unserialize($_SESSION['arrofpass']);
	@extract($aarr);
	//echopre($aarr);
}
$exportdata = 0;
if (($uuid+0)==0) {
	if ($listsarepublic['speciesdownload'] == 'on' && $tbname='checklist_all' ) {
		  $exportdata = 1;
	} 
	if ($listsarepublic['especimenesdownload'] == 'on' && $tbname='checklist_speclist' ) {
		$exportdata = 1;
	} 
	if ($listsarepublic['plantasdownload'] == 'on' && $tbname='checklist_pllist' ) {
	  $exportdata = 1;
	} 
	if ($listsarepublic['plots'] == 'on' && $tbname='checklist_plots' ) {
		$plots=1;
	}
} 
else {
	if ($acclevel!='visitor') {
		$exportdata = 1;
	}
}
//echo $exportdata."<br />";
//echopre($_SESSION);
//CABECALHO
$title = 'Mostrando dados para a tabela '.$tbname;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel=\"stylesheet\" type=\"text/css\" href=\"dhtmlxconnector/dhtmlxTabbar/dhtmlxtabbar.css\">",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn_bricks.css' >",
"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxcommon.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgridcell.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor_debug.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_export.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_ssc.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_mcol.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js'></script>",
"<script type='text/javascript'>
function aguarde() {
         var cco = document.getElementById('counter');
         cco.innerHTML = 'AGUARDE .. FILTRANDO OS DADOS!!!';
         setTimeout(function(){cco.innerHTML = '';},15000)
}
function exportarCSV(gridobject, coltoexport) {
         var tot = gridobject.getRowsNum();
         if (tot>10000) {
         	alert('Tem limite de no máximo 10000 registros para isso e seu filtro tem '+tot+' registros. Refinar sua busca antes de exportar');
         } else {
         var cco = document.getElementById('counter');
         cco.innerHTML = 'Exportando '+tot +' registros, aguarde...';
         gridobject.setSerializableColumns(coltoexport);
         gridobject.enableCSVHeader(true);
         gridobject.setCSVDelimiter('\t');
         var csv = gridobject.serializeToCSV(true);
         var w = window.open('exportGRIDasCSV.php', name='_blank',specs='scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=600,height=500');
         w.document.write('<pre>'+csv+'</pre>');
         w.focus(); 
         var cco = document.getElementById('counter');
         cco.innerHTML = 'Exportação Concluída!';
         } 
}
function marcarfiltrados(excluir, gridobject, tablename,usertbname) {
         var tot = gridobject.getRowsNum();
         if (tot>20000) {
         	alert('Tem limite de no máximo 20000 registros para isso e seu filtro tem '+tot+' registros. Refinar sua busca antes de marcar');
         } else {
         var divbar = document.getElementById('pgbar'+tablename);
         divbar.innerHTML = 'Processando '+tot +' registros, aguarde...';
         var cco = document.getElementById('counter');
         cco.innerHTML = 'Processando '+tot +' registros, aguarde...';
         setTimeout(function(){ 
         var conta = 0;
         var cco = document.getElementById('counter');
         var tt = gridobject.getAllItemIds();
         if (excluir) {
           var oque = 'desmarcados';
           var status = 0;
          }  else {
            var oque = 'marcados';
            var status = 1;
          }
         //var loader = dhtmlxAjax.postSync(\"checklistview_tabber_rectagging.php\", encodeURI('tempids='+tt+'&table='+tablename+'&status='+status+'&usertbname='+usertbname+'&uuid=".$uuid."'));
        //var res = loader.xmlDoc.responseText;
         for (var i=0; i<gridobject.getRowsNum(); i++){
              var valor = gridobject.cells2(i, 0).getValue();
              if (valor==0 & excluir==false) {
                 gridobject.cells2(i, 0).setValue(1);
                 conta++;
              }
              if (valor==1 & excluir==true) {
                 gridobject.cells2(i, 0).setValue(0);
                 conta++;
              }
              var newValue = (Math.round(i/tot)*100);
              cco.innerHTML = 'Processando '+i+' de um total de '+tot +' registros ('+newValue+'%), aguarde...';
          }
          if ((tot-conta)>0) {
              var dif = (tot-conta);
              var txt = 'Foram  ' + oque + '  ' +conta + ' registros de um total de ' + tot + ' (sendo que ' + dif + ' já estavam '+ oque + ')';
          } else {
              var txt = 'Foram ' + oque + '  ' + conta + ' registros de um total de ' + tot ;
          }
          divbar.innerHTML = txt;
          document.getElementById('counter').innerHTML = txt;        
          //res = txt + '  Foram atualizados  '+ res + ' registros!';
          alert(txt);
          divbar.innerHTML = '';
          cco.innerHTML = '';
         }, 0);
         }
}
</script>"
);
//         w.document.write('<textarea cols=100 rows=10>'+csv+'</textarea>');

//        w.document.write('<html><body>'+csv+'</body></html>');
//function(){  var csv=mygrid.serializeToCSV(); }
//          = csv;
$body = ' onload="javascript: aguarde();" ';
$menu = FALSE;
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
$clw = explode(",",$colw);
$lstvs = explode(",",$listvisible);
foreach ($clw as $kk => $vv) {
	if ($lstvs[$kk]!='true') {
		$clw[] =  0;
	}
}
$divwith = array_sum($clw);
if ($divwith>1000) {
	$divwith = 900;
}
if ($nrecs<50) {
	$divheight = $nrecs*28.5;
	if ($divheight<150) {
	  $divheight = 400;
	}
	if ($divheight>400) {
		$divheight = 600;
	}
} else {
	$divheight = 600;
}
echo"
<table>";
if ($uuid>0 && $exportdata>0 && !empty($exportcols) ) {
	echo "
<tr>
  <td align='left'>
<input type='button' onclick=\"marcarfiltrados(false,mygrid, '".$tbname."','".$usertbname."');\" onmouseover=\"Tip('Marcar/selecionar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Marcar filtrados' />
&nbsp;  
<input  type='button' onclick=\"marcarfiltrados(true,mygrid,'".$tbname."','".$usertbname."');\" onmouseover=\"Tip('Excluir/desmarcar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Desmarcar filtrados' />
&nbsp;
<input  type='button'  onclick=\"exportarCSV(mygrid,'".$exportcols."');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Exportar'  onmouseover=\"Tip('Exportar os registros que estão filtrados - DICA: marcar os que deseja e filtrar por Marcado=1');\" />
<span id='counter' ></span> 
  </td>
</tr>";
//javascript:updateOutput();\" 
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
if ($uuid>0 && $exportdata>0  && !empty($exportcols) ) {
	echo "
<tr>
  <td align='left'>
<input type='button' onclick=\"marcarfiltrados(false,mygrid, '".$tbname."','".$usertbname."');\" onmouseover=\"Tip('Marcar/selecionar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Marcar filtrados' />
&nbsp;  
<input  type='button' onclick=\"marcarfiltrados(true,mygrid,'".$tbname."','".$usertbname."');\" onmouseover=\"Tip('Excluir/desmarcar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Desmarcar filtrados' />
&nbsp;
<input  type='button'  onclick=\"exportarCSV(mygrid,'".$exportcols."');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Exportar'  onmouseover=\"Tip('Exportar os registros que estão filtrados - DICA: marcar os que deseja e filtrar por Marcado=1');\" />
<span id='pgbar".$tbname."' ></span> 
  </td>
</tr>";
//javascript:updateOutput();\" 
}

$ff = explode(",",$ffields);
$hh = explode(",",$headertxt);
if (count($hh)>0) {
$newh = array();
foreach ($ff as $kk => $vv) {
    $tz = $hh[$kk]; 
    $nn = "<span style='cursor:help;' onmouseover=\\\"javascript:Tip('".$tz."<br ><span style=\\\'font-size: 0.75em;\\\'>Clique para ordenar!</span>');\\\">".$vv."</span>";
    $newh[] = $nn;
}
$newheader = implode(",",$newh);
} else {
$newheader = $ffields;
}
echo "
</table>
<script>
mygrid = new dhtmlXGridObject(\"gridbox\");
mygrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
mygrid.setIconPath(\"icons/\");
mygrid.setHeader(\"".$newheader."\");
mygrid.setInitWidths(\"".$colw."\");
mygrid.setColAlign(\"".$colalign."\");
mygrid.setSkin(\"light\");
mygrid.attachHeader(\"".$filtros."\");
mygrid.setColSorting(\"".$filtros2."\");
mygrid.enableColumnMove(true);
mygrid.setColumnsVisibility(\"".$listvisible."\");
mygrid.setColTypes(\"".$coltipos."\");
mygrid.init();
mygrid.adjustColumnSize(".$collist.");
mygrid.enableHeaderMenu(\"".$hidemenu."\");
mygrid.enablePaging(true,50,10,'pagingArea',true);
mygrid.setPagingSkin('bricks');
mygrid.loadXML(\"temp/".$fname."\");
</script>";

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>