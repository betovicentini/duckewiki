<?php
require_once("../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php");
require_once("../../../includes/duckewiki_clean.php");    
function myUpdate($action){
    $status = $action->get_value('Marcado');
    $idsp = $action->get_id();    
    $ru = mysql_query("SELECT Marcado FROM `checklist_allUserLists` WHERE TempID=".$idsp."  AND SessionID='3dfcjpvejl'");  
    $nru = mysql_numrows($ru);
    if ($nru!=$status) {
       if ($status==1) {  
     $qinn = "INSERT INTO  `checklist_allUserLists` (`Marcado`,`TempID`,`SessionID`) VALUES ('1' ,'".$idsp."','3dfcjpvejl')";  
     }  else {  
     $qinn = "DELETE FROM  `checklist_allUserLists`  WHERE TempID='".$idsp."' AND SessionID='3dfcjpvejl'";  
     }  
     $ru = mysql_query($qinn);
   }          
   $action->success();
}
function custom_format_list($data){
    $famid = ($data->get_value("FamiliaID"))+0;
    $genid = ($data->get_value("GeneroID"))+0;
    $specid = ($data->get_value("EspecieID"))+0;
    $infspecid = ($data->get_value("InfraEspecieID"))+0;
    
    $nomesciid = '';
    if ($infspecid>0) {
        $nomesciid = 'infspid_'.$infraspecid;
    } else {
        if ($specid>0) {
            $nomesciid = 'speciesid_'.$specid;
        } else {
            if ($genid>0) {
                $nomesciid = 'genusid_'.$genid;
            } else {
                if ($famid>0) { $nomesciid = 'famid_'.$famid; }
            }
        }
    }
    $mark = $data->get_value("Marcado");
    $recid = $data->get_id();    
    $ru = mysql_query("SELECT Marcado FROM `checklist_allUserLists` WHERE TempID=".$recid."  AND SessionID='3dfcjpvejl'");    
    $ruw = mysql_fetch_assoc($ru);
    $data->set_value("Marcado", $ruw['Marcado']);
    if (($data->get_value("ESPECIMENES"))>0) {
      $imagen= "<sup>".$data->get_value("ESPECIMENES")."</sup><img style='cursor:pointer;' src='icons/specimen-icon.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/checklist_specimens_save.php?tbname=checklist_speclist&famid=".$famid."&genid=".$genid."&specid=".$specid."&infspecid=".$infspecid."',950,500,'Visualizar amostras');\" onmouseover=\"Tip('Visualizar amostras');\" />";
	 } else {
	 	 	$imagen = " ";
	 }
    $data->set_value("ESPECIMENES",$imagen);

///////////////
$famnome = $data->get_value("FAMILIA");
$ruv = mysql_query("SELECT checkformtaxastatus('".$famnome."_VEGCHARS', ".$famid.", ".$genid.", ".$specid.", ".$infspecid.", 3) AS vegechars");
//$rutxt = "SELECT checkformtaxastatus('".$famnome."_VEGCHARS', ".$famid.", ".$genid.", ".$specid.", ".$infspecid.", 3) AS vegechars";
$ruwv = mysql_fetch_assoc($ruv);
$nvegc = $ruwv['vegechars'];
if ($nvegc>0) {
if ($nvegc<=0.33) {
$cimg = "icons/redcircle.png";
}
if ($nvegc>0.33 && $nvegc<=0.66) {
$cimg = "icons/orangecircle.png";
}
if ($nvegc>0.66) {
$cimg = "icons/greencircle.png";
}
$vegcimg = "<sup>".$nvegc."</sup><img style='cursor:pointer;' src='".$cimg."' height='20'  onmouseover=\"Tip('Proporção de Caracteres vegetativos Mínimos para o taxon');\" />";
} else {
  $vegcimg = "";
}
$data->set_value("VEG_CHARS", $vegcimg);

$ruv = mysql_query("SELECT checkformtaxastatus('".$famnome."_FERTCHARS'), ".$famid.", ".$genid.", ".$specid.", ".$infspecid.", 3) AS fertchars");
$ruwv = mysql_fetch_assoc($ruv);
$nfertc = $ruwv['fertchars'];
if ($nfertc>0) {
if ($nfertc<=0.33) {
$cimg = "icons/redcircle.png";
}
if ($nfertc>0.33 && $nvegc<=0.66) {
$cimg = "icons/orangecircle.png";
}
if ($nfertc>0.66) {
$cimg = "icons/greencircle.png";
}
$fertcimg = "<sup>".$nfertc."</sup><img style='cursor:pointer;' src='".$cimg."' height='20'  onmouseover=\"Tip('Proporção de Caracteres reprodutivos mínimos para o taxon');\" />";
} else {
$fertcimg = "";
}
$data->set_value("FERT_CHARS", $fertcimg);



$ruv =mysql_query("SELECT checktaxaimg(".$famid.", ".$genid.", ".$specid.", ".$infspecid.",351) AS folimg");
$ruwv = mysql_fetch_assoc($ruv);
$nfolimg = $ruwv['folimg'];
if ($nfolimg>0) {
$cimg = "icons/greencircle.png";
$folimg = "<sup>".$nfolimg."</sup>&nbsp;<img style='cursor:pointer;' src='".$cimg."' height='20'  onmouseover=\"Tip('Tem pelo menos 1 imagem de folha fresca para o taxon');\"  alt=''  title='' >";
} else {
$folimg =$nfolimg;
}
$data->set_value("FOLHA_IMG", $folimg);

$ruv =mysql_query("SELECT checktaxaimg(".$famid.", ".$genid.", ".$specid.", ".$infspecid.",353) AS florimg");
$ruwv = mysql_fetch_assoc($ruv);
$nflorimg = $ruwv['florimg'];
if ($nflorimg>0) {
$cimg = "icons/greencircle.png";
$florimg = "<sup>".$nflorimg."</sup><img style='cursor:pointer;' src='".$cimg."' height='20'  onmouseover=\"Tip('Tem pelo menos 1 imagem de flores para o taxon');\" />";
} else {
$florimg =$nflorimg;
}
$data->set_value("FLOR_IMG", $florimg);

$ruv =mysql_query("SELECT checktaxaimg(".$famid.", ".$genid.", ".$specid.", ".$infspecid.",354) AS frutoimg");
$ruwv = mysql_fetch_assoc($ruv);
$nfrutoimg = $ruwv['frutoimg'];
if ($nfrutoimg>0) {
$cimg = "icons/greencircle.png";
$frutoimg = "<sup>".$nfrutoimg."</sup><img style='cursor:pointer;' src='".$cimg."' height='20'  onmouseover=\"Tip('Tem pelo menos 1 imagem de frutos para o taxon');\" />";
} else {
$frutoimg =$nfrutoimg;
}
$data->set_value("FRUTO_IMG", $frutoimg);

$ruv =mysql_query("SELECT checktaxaimg(".$famid.", ".$genid.", ".$specid.", ".$infspecid.",350) AS exsicataimg");
$ruwv = mysql_fetch_assoc($ruv);
$nexsicataimg = $ruwv['exsicataimg'];
if ($nexsicataimg>0) {
$cimg = "icons/greencircle.png";
$exsicataimg = "<sup>".$nexsicataimg."</sup><img style='cursor:pointer;' src='".$cimg."' height='20'  onmouseover=\"Tip('Tem pelo menos 1 imagem de exsicatas para o taxon');\" />";
} else {
$exsicataimg =$nexsicataimg;
}
$data->set_value("EXSICATA_IMG", $exsicataimg);
$plnumb = $data->get_value("PLANTAS");
 if ($plnumb>0) {
$imagen= "<sup>  ".$data->get_value("PLANTAS")."</sup><img style='cursor:pointer;' src='icons/tree-icon.png' height='20'  onmouseover=\"Tip('Este taxon tem árvores marcadas \
 mas você não tem permissão para ver esses dados');\" alt=\"\" />";
} else {
  $imagen = " ";
}
$data->set_value("PLANTAS",$imagen);
if (($data->get_value("PLOTS"))>0) {
		$nomee = $data->get_value("NOME");
		$imagen="<sup>  ".$data->get_value("PLOTS")."</sup><img style='cursor:pointer;' src='icons/icon_plot.png' height='20' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/plantasINplots-popup.php?titulo=".$nomee."&ispopup=1&famid=".$famid."&genid=".$genid."&specid=".$specid."&infspecid=".$infspecid."',1000,800,'Mapas de parcelas');\" onmouseover=\"Tip('Visualizar parcelas com plantas desse taxon');\" />";
	} else {
		$imagen = " ";
	}
	$data->set_value("PLOTS",$imagen);
	
	$tropicos = ($data->get_value("EDIT"));
	$imagen = " ";
	$imagen2 = "";
	$imagen3 = "";
	unset($nameedit);
	if (!empty($tropicos)) {
		$imagen="<img style='cursor:pointer;' src='icons/mobot.png' height='18' onclick=\"javascript:small_window('http://www.tropicos.org/NameSearch.aspx?name=".$tropicos."',1000,800,'Tropicos');\" onmouseover=\"Tip('Ver registro do nome ".$tropicos." em tropicos.org');\" />";
	}
	$nameedit=  ($data->get_value("NOME"));
	$data->set_value("EDIT",$imagen);

	$idds = $famid+$genid+$specid+$infspecid;
	$myhabitat = ($data->get_value("HABT"))+0;
    if ($myhabitat>0) {
	 	 $imagen="<img style='cursor:pointer;' src='icons/environment_icon.png' height='17' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/plothabitat_createkml_byspecies_form.php?famid=".$famid."&genid=".$genid."&specid=".$specid."&infspecid=".$infspecid."&ispopup=1',700,500,'Habitats');\" onmouseover=\"Tip('Mapear os habitats');\">";
	} else {
		$imagen = " ";
	}
    $data->set_value("HABT",$imagen);
        

    $idds = $famid+$genid+$specid+$infspecid;
    if ($idds>0) {
	 $imagen="<img style='cursor:pointer;' src='icons/".$data->get_value("MAP")."' height='17' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/mapasKML.php?famid=".$famid."&genid=".$genid."&specid=".$specid."&infspecid=".$infspecid."',800,500,'Mapa');\" onmouseover=\"Tip('Ver em mapa');\">";
	 $imagen2="<img style='cursor:pointer;' src='icons/map-download.png' height='18' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/mapasKML.php?download=1&famid=".$famid."&genid=".$genid."&specid=".$specid."&infspecid=".$infspecid."',200,200,'Download map');\" onmouseover=\"Tip('Baixar arquivo KML');\" >";
	 $imagen = $imagen."&nbsp;".$imagen2;
	} else {
	 $imagen="<img style='cursor:pointer;' src='icons/question-red.png' height='18' title='Não dá para mapear, faltam coordenadas' onmouseover=\"Tip('Não dá para mapear, faltam coordenadas');\">";
	}
    $data->set_value("MAP",$imagen);
    
    
    $imgs = ($data->get_value("IMG"))+0;
    if ($imgs>0) {
	 $imagen="<img style='cursor:pointer;' src='icons/camera.png' height='18' onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/showimage_taxa.php?famid=".$famid."&genid=".$genid."&specid=".$specid."&infspecid=".$infspecid."',1000,600,'Imagens de Taxa');\"  onmouseover=\"Tip('Visualizar imagens para esse taxon');\" />";
	} else {
		$imagen = " ";
	}
    $data->set_value("IMG",$imagen);
    
    $nir = $data->get_value("NIRSpectra");
    if ($nir>0) {
      $imagen=  "<sup>  ".$nir."</sup>&nbsp;<img style='  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;' src='icons/nirspectra.png' height='16' onmouseover=\"Tip('Existem $nir espectros associados as plantas ou especimenes de ".$nomee.". Clique para ver!');\" onclick=\"javascript:small_window('http://localhost/duckewiki/www/duckewiki/export-nir-data-form.php?taxnome=".$nomee."&famid=".$famid."&genid=".$genid."&specid=".$specid."&infspecid=".$infspecid."&checklist=1&antaris=1',800,600,'Exporta dados NIR');\"   alt=''  title=''>";
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

    $index1 = $filter_by->index('ESPECIMENES');
    $index2 = $filter_by->index('PLOTS');
    $index3 = $filter_by->index('PLANTAS');
    $index4 = $filter_by->index('NIRSpectra');
    $index5 = $filter_by->index('SILICA');
    $index6 = $filter_by->index('FLORES');
    $index7 = $filter_by->index('FRUTOS');
    $index8 = $filter_by->index('VEG_CHARS');
    $index9 = $filter_by->index('FERT_CHARS');
    $index10 = $filter_by->index('FOLHA_IMG');
    $index11 = $filter_by->index('FLOR_IMG');
    $index12 = $filter_by->index('FRUTO_IMG');
    $index13 = $filter_by->index('EXSICATA_IMG');
$idxss = array($index1,$index2,$index3,$index4,$index5,$index6,$index7,$index8,$index9,$index10,$index11,$index12,$index13);
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
$grid ->dynamic_loading(100);
$grid->event->attach("beforeUpdate","myUpdate");
$grid ->render_sql("SELECT 0 as Marcado, tb.* FROM `checklist_all` as tb","TempID","Marcado,EDIT,DetID,GeneroID,FamiliaID,InfraEspecieID,EspecieID,DetNivel,FAMILIA,NOME,NOME_AUTOR,MORFOTIPO,ESPECIMENES,PLANTAS,PLOTS,MAP,OBS,HABT,IMG,NIRSpectra,SILICA,FLORES,FRUTOS,VEG_CHARS,FERT_CHARS,FOLHA_IMG,FLOR_IMG,FRUTO_IMG,EXSICATA_IMG");
?>