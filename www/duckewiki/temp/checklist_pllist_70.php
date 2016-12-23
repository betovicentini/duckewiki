<?php
require_once("../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php");
require_once("../../../includes/duckewiki_clean.php");    
function myUpdate($action){
    $status = $action->get_value('Marcado');
    $idsp = $action->get_id();    
    $ru = mysql_query("SELECT Marcado FROM `checklist_pllistUserLists` WHERE PlantaID=".$idsp."  AND UserID='70'");  
    $nru = mysql_numrows($ru);
    if ($nru!=$status) {
       if ($status==1) {    
     $qinn = "INSERT INTO  `checklist_pllistUserLists` (`Marcado`,`PlantaID`,`UserID`) VALUES ('1' ,'".$idsp."','70')";  
     }  else {    
     $qinn = "DELETE FROM  `checklist_pllistUserLists`  WHERE PlantaID='".$idsp."' AND UserID='70'";  
     }  
     $ru = mysql_query($qinn);
   }          
   $action->success();
}
function custom_format_pl($data){
$pltag = $data->get_value("TAGtxt");
$data->set_value("TAG",$pltag);

  $mark = $data->get_value("Marcado");
  $recid = $data->get_id();    
    $ru = mysql_query("SELECT Marcado FROM `checklist_pllistUserLists` WHERE PlantaID=".$recid."  AND UserID='70'");    
    $ruw = mysql_fetch_assoc($ru);
    $data->set_value("Marcado", $ruw['Marcado']);
if ($data->get_value("IMG")=="camera.png") {
    $imagen="<img style='cursor:pointer;' src='icons/".$data->get_value("IMG")."' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/showimage_taxa.php?plantaid=".$data->get_value("PlantaID")."',600,400,'Ver imagens');\" onmouseover=\"Tip('Ver imagens da planta # ".$pltag."');\"  title=''>";
    $data->set_value("IMG",$imagen);
    } else {
    $imagen= ''; 
      //"<img style='cursor:pointer;' src='icons/".$data->get_value("IMG")."' height='20' onclick=\"javascript:alert('Não ha imagens!');\" title='' >";
      $data->set_value("IMG",$imagen);
    }

  if (($data->get_value("ESPECIMENES"))>0) {
       $imagen= "<img style='cursor:pointer;' src='icons/specimen-icon.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/checklist_specimens_save.php?plantaid=".$data->get_value("PlantaID")."',950,500,'Especimenes');\" onmouseover=\"Tip('Visualizar amostras da planta # ".$pltag."');\" title=''><sup>  ".$data->get_value("ESPECIMENES")."</sup>";
   } else {
        $imagen = " ";
   }
   $data->set_value("ESPECIMENES",$imagen);

  //NAO EXECUTA ISSO O VALOR DE PLOT SE REFERE 
  if (($data->get_value("PLOT"))>0) {
    //$imagen="<img style='cursor:pointer;' src='icons/icon_plot.png' height='20' onmouseover=\"Tip('Visualizar a planta # ".$pltag." na parcela');\" onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki',1000,800,'Mapas de parcelas');\" title=''><sup>  ".$data->get_value("PLOTS")."</sup>";
  } else {
    //$imagen = " ";
  }
  //$data->set_value("PLOT",$imagen);
      
  if ($data->get_value("HABT")=="environment_icon.png") {
    $imagen="<img style='cursor:pointer;' src='icons/".$data->get_value("HABT")."' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/showhabitat.php?plantaid=".$data->get_value("PlantaID")."',500,400,'Ver imagens');\" onmouseover=\"Tip('Sobre o habitat da planta # ".$pltag."');\" title=''>";
    $data->set_value("HABT",$imagen);
    } else {
    //$imagen="<img style='cursor:pointer;' src='icons/".$data->get_value("HABT")."' height='20' onclick=\"javascript:alert('Não há informação sobre habitat para esta planta!');\" onmouseover=\"Tip('Não há informação sobre habitat para esta planta');\" title=''>";
    //$data->set_value("HABT",$imagen);
    }
   
   //$pj = $data->get_value("OBS");
   //if (!empty($pj)) {
     $imagen="<img style='cursor:pointer;' src='icons/edit-notes.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/showplanta.php?plantaid=".$data->get_value("PlantaID")."',400,400,'Notas');\" onmouseover=\"Tip('Notas da planta # ".$pltag."');\" title=''>";
   //} else {
     //$imagen='';
   //}
    $imgg ="<img style='cursor:pointer;' src='icons/label-icon.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/singlelabel-exec.php?etitype=PlantasIDS&specimenid=".$data->get_value("PlantaID")."',300,100,'Etiqueta em PDF');\" onmouseover=\"Tip('Etiquetas em PDF da planta # ".$pltag."');\" title='' >";
    $imagen = $imagen."&nbsp;".$imgg;
    $data->set_value("OBS",$imagen);    
   $imagen= "<img style='cursor:pointer;' src='icons/".$data->get_value("EDIT")."' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/plantas_dataform.php?ispopup=1&submeteu=editando&plantaid=".$data->get_value("PlantaID")."',1000,400,'Editando o registro');\" onmouseover=\"Tip('Editar o registro da planta # ".$pltag."');\" title=''>";
   $imgg2 = "<img style='cursor:pointer;' src='icons/monitoramento.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/traits_coletormonitoramento.php?ispopup=1&plantatag=".$data->get_value("TAG")."&plantaid=".$data->get_value("PlantaID")."&submeteu=1',1000,400,'Editando o registro');\" onmouseover=\"Tip('Ver/Editar variáveis de monitoramento da planta # ".$pltag."');\" title=''>";
   
   $pltid = $data->get_value("PlantaID");
   $imgg3 = "<img style='cursor:pointer;' src='icons/specimen-icon.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/especimenes_dataform.php?ispopup=1&submeteu=nova&plantaid=".$pltid."',1000,400,'Nova amostra de planta');\" onmouseover=\"Tip('Novo especímene da planta # ".$pltag."');\" title=''>";
   $imgg4 = "<img style='cursor:pointer;' src='icons/rednameicon.png' height='17' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/taxonomia-popup.php?updatechecklist=1&ispopup=1&saveit=true&detid=".$data->get_value("DetID")."&plantaid=".$data->get_value("PlantaID")."',800,400,'Editar Identificação');\" onmouseover=\"Tip('Editar Identificação da planta # ".$pltag."');\" title='' >";
    $ruu = mysql_query("SELECT TraitID FROM Traits WHERE ParentID='115' AND LOWER(TraitName) LIKE '%silica%'");
     $ruuw = mysql_fetch_assoc($ruu);
     $silicavar = $ruuw['TraitID'];
     $rnn = mysql_query("SELECT * FROM Traits_variation WHERE TraitID='115' AND PlantaID=".$data->get_value("PlantaID")." AND (TraitVariation LIKE '".$silicavar."%'  OR TraitVariation LIKE 
     '%;".$silicavar."%' )");
     $nrnn = mysql_numrows($rnn);
     if ($nrnn==0) {
     $imgg5 ="<img style='cursor:pointer;' src='icons/dna.png' height='20' onclick=\"javascript:amostrasilica(0,".$data->get_value("PlantaID").");\"  onmouseover=\"Tip('Marca que tem amostra em silica');\" >";
    } else {
     $imgg5 ="<img style='cursor:pointer;' src='icons/dna_ok.png' height='20' onclick=\"javascript:alert('Já tem amostra de sílica marcada para esta coleta');\"  onmouseover=\"Tip('Já tem amostra de sílica marcada para esta planta');\" >";
    }
   $imagen = $imagen."&nbsp;".$imgg2."&nbsp;&nbsp;".$imgg3."&nbsp;&nbsp;".$imgg4."&nbsp;&nbsp;".$imgg5;
    $data->set_value("EDIT",$imagen);
  $llat = ABS(("LATITUDE"));
  $llong = ABS($data->get_value("LONGITUDE"));
  $llcord = $llat+$llong;
  if ($llcord>0) {
   $imagen="<img style='cursor:pointer;' src='icons/".$data->get_value("MAP")."' height='18' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/mapasKML_plantas.php?plantaid=".$data->get_value("PlantaID")."',600,500,'Notas');\" onmouseover=\"Tip('Mapear a planta # ".$pltag."');\" title=''>";
  } else {
   $imagen= '';
   //"<img style='cursor:pointer;' src='icons/question-red.png' height='18' onclick=\"javascript:alert('Latitude & Longitude Faltando');\" onmouseover=\"Tip('Latitude e Longitude faltando para planta # ".$pltag."');\" title=''>";
  }
    $data->set_value("MAP",$imagen);
    $nir = $data->get_value("NIRSpectra");
    if ($nir>0) {
          $imagen=  "<img style='  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;' src='icons/nirspectra.png' height='16' onmouseover=\"Tip('Existem $nir espectros associados essa PLANTA');\" title=''>&nbsp;<sup>  ".$nir."</sup>";
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

    $index1 = $filter_by->index('TAG');
    $index2 = $filter_by->index('LONGITUDE');
    $index3 = $filter_by->index('LATITUDE');
    $index4 = $filter_by->index('ALTITUDE');
    $index5 = $filter_by->index('DAPmm');
    $index6 = $filter_by->index('ALTURA');
    $index7 = $filter_by->index('NIRSpectra');
$idxss = array($index1,$index2,$index3,$index4,$index5,$index6,$index7);
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
$grid ->event->attach("beforeRender","custom_format_pl");
$grid ->event->attach("beforeFilter","custom_filter");
$grid ->dynamic_loading(100);
$grid->event->attach("beforeUpdate","myUpdate");
$grid ->render_sql("SELECT 0 as Marcado,tb.* FROM `checklist_pllist` as tb ","PlantaID","Marcado,PlantaID,DetID,EDIT,TAGtxt,TAG,FAMILIA,NOME,NOME_AUTOR,DETBY,DETYY,MORFOTIPO,PAIS,ESTADO,MUNICIPIO,LOCAL,LOCALSIMPLES,LONGITUDE,LATITUDE,ALTITUDE,DAPmm,ALTURA,HABITO,STATUS,ESPECIMENES,PLOT,DUPS,MAP,OBS,HABT,IMG,NIRSpectra,PROJETO");
?>