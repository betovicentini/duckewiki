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

//EXTRAI A URL 
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);

if ($tbname!='temp_TraitsStates_'.$uuid) {
$title = 'Mostrando Traits';
//$clcolidx = 5;
} else {
$title = 'Mostrando Estados de Caráter';
//$clcolidx = 4;
}
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
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>",
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
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_validation.js'></script>",
"<script type='text/javascript'>
      function updateOutput() {
         mygrid.enableCSVHeader(true);
         mygrid.setCSVDelimiter('\t');
         var csv = mygrid.serializeToCSV(true);
         var w = window.open('exportGRIDasCSV.php', name='_blank',specs='scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=600,height=500');
         w.document.write('<pre>'+csv+'</pre>');
         w.focus(); 
      }
function ChangeTaxa(rwid,colid,trid,val) { 
var txt = '<img style=\"cursor:pointer;\" src=\"icons/diversity.png\" height=\"20\" onclick=\"javascript:    small_window(\'traits_definition_taxonomy.php?rowid='+rwid+'&traitid='+trid+'&clidx='+colid+'\',800,500,\'Indicando taxa associados com a variável\');\"   onmouseover=\"Tip(\'Associar TAXA com a variável\');\" >';
       //var el =  document.getElementById('counter');
        //el.innerHTML = val+ '<br >'+ txt;
        var tt = val+ '<br >'+ txt;
        mygrid.cells2(rwid,colid).setValue(tt);
} 
function salvarmarcados(gridobject, tablename) {
         small_window('traits_definition_saveForm.php?tbname='+tablename,500,500,'Salvar Formulário');
}      
      
function marcarfiltrados(excluir, gridobject, tablename) {
         var tot = gridobject.getRowsNum();
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
         var loader = dhtmlxAjax.postSync(\"traits_definition_rectagging.php\", encodeURI('tempids='+tt+'&table='+tablename+'&status='+status+'&uuid=".$uuid."'));
         var res = loader.xmlDoc.responseText;
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
          res = txt + '  Inserido e atualizados: '+ res;
          alert(res);
          divbar.innerHTML = '';
          cco.innerHTML = '';
         }, 0);
}
</script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js'></script>",
"<script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>"
//APARENTLY connector.js must be one of the last entries.
//,
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js'></script>",
//"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_skyblue.css'>"
);

//SPECIAL FILTERING


$which_java[] = "<script type='text/javascript'>

</script>";


FazHeader($title,$body,$which_css,$which_java,FALSE);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";

$clw = explode(",",$colw);
$divwith = array_sum($clw)+100;
if ($divwith>1200) {
	$divwith = 1000;
} elseif ($divwith<800) {
	$divwith = 1000;
}
if ($nrecs<50) {
	$divheight = $nrecs*28.5;
	if ($divheight<350) {
	  $divheight = 350;
	}
	if ($divheight>600) {
		$divheight = 600;
	}
} else {
	$divheight = 600;
}
echo "
<span id='counter'></span>
<div style=\"position:absolute;top:0%;left:5%; width: 40%; padding: 20px; background-color:orange; color:  black; visibility: hidden;\" id=\"progressalert\"  ></div>";
echo"
<table>";
if ($tbname!='temp_TraitsStates_'.$uuid) {
echo "
<tr>
      <td>    
   <input type='button' onclick=\"marcarfiltrados(false,mygrid, '".$tbname."');\" onmouseover=\"Tip('Marcar/selecionar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Marcar filtrados' />
&nbsp;
<input  type='button' onclick=\"marcarfiltrados(true,mygrid,'".$tbname."');\" onmouseover=\"Tip('Excluir/desmarcar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Desmarcar filtrados' />
&nbsp;
<input  id='myspecgrid_button' type='button'  onclick=\"salvarmarcados(mygrid,'".$tbname."');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Formulário - salvar marcados' />
&nbsp;
&nbsp;
&nbsp;
&nbsp;
&nbsp;
<input type='button' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  onclick=\"javascript:updateOutput();\" value='Exportar TXT' onmouseover=\"Tip('Exporta como TXT separado por TAB!');\" />
<span id='pgbar".$tbname."' ></span>    
      </td>
   </tr>";
}   else {
echo "
<tr>
      <td>    
<input type='button' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  onclick=\"javascript:updateOutput();\" value='Exportar TXT' onmouseover=\"Tip('Exporta como TXT separado por TAB!');\" />
<span id='pgbar".$tbname."' ></span>    
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
if ($tbname!='temp_TraitsStates_'.$uuid) {
echo "
<tr>
      <td>    
   <input type='button' onclick=\"marcarfiltrados(false,mygrid, '".$tbname."');\" onmouseover=\"Tip('Marcar/selecionar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Marcar filtrados' />
&nbsp;
<input  type='button' onclick=\"marcarfiltrados(true,mygrid,'".$tbname."');\" onmouseover=\"Tip('Excluir/desmarcar os registros que estão filtrados por qualquer coluna!');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Desmarcar filtrados' />
&nbsp;
<input  id='myspecgrid_button' type='button'  onclick=\"salvarmarcados(mygrid,'".$tbname."');\" style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Formulário - salvar marcados' />
&nbsp;
&nbsp;
&nbsp;
&nbsp;
&nbsp;
<input type='button' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  onclick=\"javascript:updateOutput();\" value='Exportar TXT' onmouseover=\"Tip('Exporta como TXT separado por TAB!');\" />
<span id='pgbar".$tbname."' ></span>    
      </td>
   </tr>";
}   else {
echo "
<tr>
      <td>    
<input type='button' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  onclick=\"javascript:updateOutput();\" value='Exportar TXT' onmouseover=\"Tip('Exporta como TXT separado por TAB!');\" />
<span id='pgbar".$tbname."' ></span>    
      </td>
   </tr>";
}
echo "
</table>
<script>
mygrid = new dhtmlXGridObject(\"gridbox\");
mygrid.setImagePath(\"dhtmlxconnector/dhtmlxGrid/codebase/imgs/\");
mygrid.setIconPath(\"icons/\");
mygrid.setHeader(\"".$ffields."\");
mygrid.setInitWidths(\"".$colw."\");
mygrid.setColAlign(\"".$colalign."\");
mygrid.setSkin(\"light\");
mygrid.attachHeader(\"".$filtros."\");
mygrid.setColSorting(\"".$filtros2."\");

mygrid.enableColumnMove(false);
mygrid.setColumnsVisibility(\"".$listvisible."\");
mygrid.setColTypes(\"".$coltipos."\");
mygrid.enableValidation(\"".$colvalidtorf."\");
//mygrid.setColValidators(\"".$colvalid."\");
mygrid.enableMultiline(true);
";
//echo "
//mygrid.attachEvent(\"onFilterStart\",function(ind,data){
//    var input = mygrid.getFilterElement(".$clcolidx.").value;
//    if (input) {
//          var val=mygrid.cells(ind,".$clcolidx.").getValue();
//          var loader = dhtmlxAjax.postSync(\"traits_definition_checkopt.php\", encodeURI('parid='+val+'&input='+input));
//          var res = loader.xmlDoc.responseText;
//          if (res>0) {
//                 return true
//          } else {
//                return false
//          }
//      //document.getElementById('counter').innerHTML = val;    
//      //alert(val);
//    }
//});";


//$qn = "SELECT TraitID FROM TraitsEditDefinitions WHERE TraitTipo<>'Variavel|Quantitativo'";
//$rn = mysql_query($qn,$conn);
//while ($rw  = mysql_fetch_assoc($rn)) {
//echo "
//mygrid.cells(".$rw['TraitID'].",10).setAttribute(\"validate\",\"Empty\");
//";
//}

//echo "
//mygrid.attachEvent(\"onEditCell\", function(stage,rId,cInd,nValue,oValue){
//var tipo = mygrid.cells(rId,4).getValue();
//if (stage==0 && tipo != 'Variavel|Quantitativo' && cInd==10) {
//alert('A variavel e '+ tipo + ' Portanto não faz sentido salvar uma unidade para ela. Deixe vazio');
////mygrid.setCellTextStyle(rId, cInd, \"background-color:red;\");
//}
//});
//";
//
//
//echo "
//mygrid.attachEvent(\"onValidationError\", function(id, ind, value) {
//    mygrid.setCellTextStyle(id, ind, \"background-color:red;\");
//    mygrid.cells(id, ind).setValue('');
//    alert('ERRO: a variável é categórica ou texto');
//    mygrid.setCellTextStyle(id, ind, \"background-color:white;\");
//    return false;
//});
//";
echo "
mygrid.init();
//mygrid.adjustColumnSize(".$collist.");
mygrid.enableHeaderMenu(\"".$hidemenu."\");
mygrid.enablePaging(true,300,10,'pagingArea',true);
mygrid.setPagingSkin('bricks');
mygrid.loadXML(\"temp/".$fname."\");
dp = new dataProcessor(\"temp/".$fname."\");
dp.init(mygrid);
</script>";
//dp.enableUTFencoding(false);

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=FALSE);
?>