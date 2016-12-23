<?php
require_once("../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php");
require_once("../../../includes/duckewiki_clean.php");
include "../functions/MyPhpFunctions.php";    
function myUpdate($action){
    $status = $action->get_value('Marcado');
    $idsp = $action->get_id();    
    $ru = mysql_query("SELECT Marcado FROM `checklist_speclistUserLists` WHERE EspecimenID=".$idsp."  AND UserID='70'");  
    $nru = mysql_numrows($ru);
    if ($nru!=$status) {
       if ($status==1) {    
     $qinn = "INSERT INTO  `checklist_speclistUserLists` (`Marcado`,`EspecimenID`,`UserID`) VALUES ('1' ,'".$idsp."','70')";  
     }  else {    
     $qinn = "DELETE FROM  `checklist_speclistUserLists`  WHERE EspecimenID='".$idsp."' AND UserID='70'";  
     }  
     $ru = mysql_query($qinn);
   }          
   $action->success();
}
function custom_format_spec($data){
  $pltag = $data->get_value("COLETOR")." ".$data->get_value("NUMERO");
  $thedetid = $data->get_value("DetID");
  $thespecimenid = $data->get_value("EspecimenID");
  $mark = $data->get_value("Marcado");
  $recid = $data->get_id();    
    $ru = mysql_query("SELECT Marcado FROM `checklist_speclistUserLists` WHERE EspecimenID=".$recid."  AND UserID='70'");    
    $ruw = mysql_fetch_assoc($ru);
    $data->set_value("Marcado", $ruw['Marcado']);
    if ($data->get_value("IMG")=="camera.png") {
      $imagen="<img style='cursor:pointer;' src='icons/".$data->get_value("IMG")."' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/showimage_taxa.php?ispopup=1&especimenid=".$thespecimenid."',700,400,'Ver imagens');\" onmouseover=\"Tip('Ver imagens da amostra # ".$pltag."');\" >";
      $data->set_value("IMG",$imagen);
    } else {
      $imagen= '';
      $data->set_value("IMG",$imagen);
    }
    if ($data->get_value("HABT")=="environment_icon.png") {
      $imagen="<img style='cursor:pointer;' src='icons/".$data->get_value("HABT")."' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/showhabitat.php?ispopup=1&especimenid=".$data->get_value("EspecimenID")."',500,400,'Ver imagens');\"  onmouseover=\"Tip('Sobre o habitat da amostra # ".$pltag."');\">";
      $data->set_value("HABT",$imagen);
    } else {
    $imagen= '';
      //"<img style='cursor:pointer;' src='icons/".$data->get_value("HABT")."' height='20' onclick=\"javascript:alert('Não há informação sobre habitat para esta amostra!');\" onmouseover=\"Tip('Não há informação sobre habitat para esta amostra');\">";
      $data->set_value("HABT",$imagen);
    }
    $imagen="<img style='cursor:pointer;' src='icons/edit-notes.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/showspecimen.php?ispopup=1&especimenid=".$data->get_value("EspecimenID")."',400,400,'Notas');\" onmouseover=\"Tip('Notas da amostra # ".$pltag."');\" >";
    $imgg ="<img style='cursor:pointer;' src='icons/label-icon.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/singlelabel-exec.php?ispopup=1&specimenid=".$data->get_value("EspecimenID")."',300,100,'Imprimindo Etiqueta');\"  onmouseover=\"Tip('Etiquetas em PDF da amostra # ".$pltag."');\" >";
    $imagen = $imagen."&nbsp;".$imgg;
    $data->set_value("OBS",$imagen);
    $imagen="<img style='cursor:pointer;' src='icons/".$data->get_value("EDIT")."' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/especimenes_dataform.php?ispopup=1&especimenid=".$data->get_value("EspecimenID")."',1000,600,'Editar registro');\" onmouseover=\"Tip('Editar o especímene # ".$pltag."');\" >";
    $imagen2="<img style='cursor:pointer;' src='icons/rednameicon.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/taxonomia-popup.php?updatechecklist=1&ispopup=1&saveit=true&detid=".$data->get_value("DetID")."&especimenid=".$data->get_value("EspecimenID")."',800,400,'Editar Identificação');\" onmouseover=\"Tip('Editar Identificação da amostra # ".$pltag."');\" >";
    $imgg3 ="<img style='cursor:pointer;' src='icons/nota-icon.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/traits_coletorvariacao.php?apagavarsess=1&saveit=1&formid=89&especimenid=".$data->get_value("EspecimenID")."',800,800,'Editando notas');\"  onmouseover=\"Tip('Edita notas da amostra # ".$pltag."');\" >";
    $imgg4 ="<img style='cursor:pointer;' src='icons/samples.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/adiciona_processo.php?onome=".$pltag."&especimenid=".$data->get_value("EspecimenID")."',700,400,'Adicionando ao processo');\"  onmouseover=\"Tip('Adiciona a amostra # ".$pltag." a um processo');\" >";
    
     $ruu = mysql_query("SELECT TraitID FROM Traits WHERE ParentID='115' AND LOWER(TraitName) LIKE '%silica%'");
     $ruuw = mysql_fetch_assoc($ruu);
     $silicavar = $ruuw['TraitID'];
     $rnn = mysql_query("SELECT * FROM Traits_variation WHERE TraitID='115' AND EspecimenID=".$data->get_value("EspecimenID")." AND (TraitVariation LIKE '".$silicavar."%'  OR TraitVariation LIKE 
     '%;".$silicavar."%' )");
     $nrnn = mysql_numrows($rnn);
     if ($nrnn==0) {
     $imgg5 ="<img style='cursor:pointer;' src='icons/dna.png' height='20' onclick=\"javascript:amostrasilica(".$data->get_value("EspecimenID").",0);\"  onmouseover=\"Tip('Marca que tem amostra em silica');\" >";
    } else {
     $imgg5 ="<img style='cursor:pointer;' src='icons/dna_ok.png' height='20' onclick=\"javascript:alert('Já tem amostra de sílica marcada para esta coleta');\"  onmouseover=\"Tip('Já tem amostra de sílica marcada para esta coleta');\" >";
    }
    $imagen = $imagen."&nbsp;".$imagen2."&nbsp;".$imgg3."&nbsp;".$imgg4."&nbsp;".$imgg5;
    $data->set_value("EDIT",$imagen);
    $llat = ABS(("LATITUDE"));
    $llong = ABS($data->get_value("LONGITUDE"));
    $llcord = $llat+$llong;
    if ($llcord>0) {
      $imagen="<img style='cursor:pointer;' src='icons/".$data->get_value("MAP")."' height='18' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/mapasKML.php?ispopup=1&specimenid=".$data->get_value("EspecimenID")."',600,500,'Notas');\" onmouseover=\"Tip('Mapear a amostra # ".$pltag."');\" >";
    } else {
      $imagen= '';
      //"<img style='cursor:pointer;' src='icons/question-red.png' height='18' onclick=\"javascript:alert('Latitude & Longitude Faltando');\" onmouseover=\"Tip('Latitude e Longitude faltando para amostra # ".$pltag."');\"  >";
    }
    $data->set_value("MAP",$imagen);
    $nir = $data->get_value("NIRSpectra");
    if ($nir>0) {
          $imagen=  "<sup>  ".$nir."</sup>&nbsp;<img style='  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;' src='icons/nirspectra.png' height='16' onmouseover=\"Tip('Existem $nir espectros associados esse ESPECIMENE');\" title=''>";
     } else {
     $imagen=  "";
     }
      $data->set_value("NIRSpectra",$imagen);
    
    
}
function removeoperators($data){
  $val = str_replace('>','',$data);
  $val = str_replace('<','',$val);
  $val = str_replace('=','',$val);  
  return $val;
}
function custom_filter($filter_by){

    $index1 = $filter_by->index('PlantaTag');
    $index2 = $filter_by->index('INPA');
    $index3 = $filter_by->index('NUMERO');
    $index4 = $filter_by->index('DAPmm');
    $index5 = $filter_by->index('ALTURA');
    $index6 = $filter_by->index('NIRSpectra');
$idxss = array($index1,$index2,$index3,$index4,$index5,$index6);
   foreach ($idxss as $idx) {
    if ($idx!==false) {
      $vv =  $filter_by->rules[$idx]["value"];
      if (substr($vv,0,1)=='>') {
        $filter_by->rules[$idx]["operation"]=">";
        //$val = str_replace('>','',$vv);
      }
      if (substr($vv,0,1)=='<') {
        $filter_by->rules[$idx]["operation"]="<";
        //$val = str_replace('<','',$vv);
      }
      if (substr($vv,0,1)=='=') {
        $filter_by->rules[$idx]["operation"]="=";
        //$val = str_replace('=','',$vv);
      }
      $filter_by->rules[$idx]["value"] = removeoperators($filter_by->rules[$idx]["value"]);
    }
  }
}
$grid = new GridConnector($res);
$grid ->event->attach("beforeRender","custom_format_spec");
$grid ->event->attach("beforeFilter","custom_filter");
$grid ->dynamic_loading(100);
$grid->event->attach("beforeUpdate","myUpdate");
$grid ->render_sql("SELECT 0 as Marcado,tb.* FROM checklist_speclist as tb   ORDER BY tb.DATA DESC","EspecimenID","Marcado,EspecimenID,DetID,EDIT,COLETOR,NUMERO,PlantaTag,PlantaID,DATA,INPA,HERBARIA,FAMILIA,NOME,NOME_AUTOR,DETBY,DETYY,MORFOTIPO,PAIS,ESTADO,MUNICIPIO,LOCAL,LOCALSIMPLES,DAPmm,ALTURA,HABITO,FERTILIDADE,LONGITUDE,LATITUDE,ALTITUDE,DUPS,MAP,OBS,HABT,HABT_CLASSE,IMG,NIRSpectra,PROJETO");
?>