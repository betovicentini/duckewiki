<?php
require_once("../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php");
require_once("../../../includes/duckewiki_clean.php");    
function myUpdate($action){
    $status = $action->get_value('Marcado');
    $idsp = $action->get_id();    
    $ru = mysql_query("SELECT Marcado FROM `checklist_plotsUserLists` WHERE TempID=".$idsp."  AND UserID='70'");  
    $nru = mysql_numrows($ru);
    if ($nru!=$status) {
       if ($status==1) {    
     $qinn = "INSERT INTO  `checklist_plotsUserLists` (`Marcado`,`TempID`,`UserID`) VALUES ('1' ,'".$idsp."','70')";  
     }  else {    
     $qinn = "DELETE FROM  `checklist_plotsUserLists`  WHERE TempID='".$idsp."' AND UserID='70'";  
     }  
     $ru = mysql_query($qinn);
   }          
   $action->success();
}
function custom_format_list($data)
{

  $mark = $data->get_value("Marcado");
  $recid = $data->get_id();    
    $ru = mysql_query("SELECT Marcado FROM `checklist_plotsUserLists` WHERE TempID=".$recid."  AND UserID='70'");    
    $ruw = mysql_fetch_assoc($ru);
    $data->set_value("Marcado", $ruw['Marcado']);

$idd = $data->get_value("idd");
$tableref = $data->get_value("tableref");
if (($data->get_value("HABT"))>0) {
  $imagen="<img style='cursor:pointer;' src='icons/environment_icon.png' height='17' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/plothabitat_createkml_byspecies_form.php?tableref=".$tableref."&idd=".$idd."&ispopup=1',700,500,'Habitats');\" onmouseover=\"Tip('Ver habitats');\" />";
} else {
  $imagen = " ";
}
$data->set_value("HABT",$imagen);

if (($data->get_value("NSPP"))>0) {
  $imagen="<img style='cursor:pointer;' src='icons/flowers.png' height='17' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/checklist_species_form.php?tableref=".$tableref."&idd=".$idd."&ispopup=1&update=0;',700,500,'Ver espécies');\" onmouseover=\"Tip('Ver espécies');\" \><sup>  ".$data->get_value("NSPP")."</sup>";
} else {
  $imagen = " ";
}
$data->set_value("NSPP",$imagen);

$plnumb = $data->get_value("NPLANTAS");
 if ($plnumb>0) {
$imagen="<img style='cursor:pointer;' src='icons/tree-icon.png' height='17' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/checkllist_plantas_save.php?tbname=checklist_speclist&tableref=".$tableref."&idd=".$idd."&ispopup=1',700,500,'Ver plantas');\" onmouseover=\"Tip('Ver plantas');\" \><sup>  ".$plnumb."</sup>";
} else {
  $imagen = " ";
}
$data->set_value("NPLANTAS",$imagen);
if (($data->get_value("NSPECS"))>0) {
   $imagen= "<img style='cursor:pointer;' src='icons/specimen-icon.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/checklist_specimens_save.php?tbname=checklist_pllist&tableref=".$tableref."&idd=".$idd."&ispopup=1',950,500,'Especimenes');\"  onmouseover=\"Tip('Visualizar amostras');\" /><sup>  ".$data->get_value("NSPECS")."</sup>";
} else {
  $imagen = " ";
}
$data->set_value("NSPECS",$imagen);  

$plot = $data->get_value("Parcela");
$plotdim = explode("x",$plot);
$plotm2 = ($plotdim[1]+0)*($plotdim[1]+0);
$plotm2 = "<small>".$plotm2."m<sup>2</sup><small>";
if (!empty($plot)) {
  $imagen="<img style='cursor:pointer;' src='icons/icon_plot.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/speciesINplots-popup.php?gazetteerid=".$idd."&ispopup=1',1000,800,'Mapas de parcelas');\" onmouseover=\"Tip('Visualizar plantas na parcela');\" />";
} else {
  $imagen = " ";
  $plot = " ";
}
if ($tableref=="Gazetteer") { 
$img2= "<img style='cursor:pointer;' src='icons/download.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/export-plotdata-save.php?tableref=".$tableref."&idd=".$idd."',900,500,'Baixar dados da parcela');\" onmouseover=\"Tip('Baixar dados de parcelas para essa localidade');\" />";
} else {
$img2 = "";
}
$imagen = $imagen."&nbsp;".$img2."&nbsp;".$plot;
$data->set_value("Parcela",$imagen);
}
function removeoperators($data){
  $val = str_replace('>','',$data);
  $val = str_replace('<','',$val);
  $val = str_replace('=','',$val);  
  return $val;
}
function custom_filter($filter_by){
$idxss = array();
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
$grid ->event->attach("beforeRender","custom_format_list");
$grid ->event->attach("beforeFilter","custom_filter");
$grid ->dynamic_loading(200);
$grid->event->attach("beforeUpdate","myUpdate");
$grid ->render_sql("SELECT 0 as Marcado, tb.* FROM `checklist_plots` as tb ORDER BY tb.NSPP DESC,tb.Localidade ASC","TempID","Marcado,nomeid,Pais,MajorArea,MinorArea,Localidade,LocalSimples,Latitude,Longitude,Altitude,Parcela,idd,tableref,TempID,HABT,NSPP,NPLANTAS,NSPECS");
?>