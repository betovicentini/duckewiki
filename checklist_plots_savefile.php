<?php
//Start session
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//INICIA O CONTEUDO//
$tbname = "checklist_plots";
$uuid = cleanQuery($_SESSION['userid'],$conn);
if ($uuid>0) {
	$newfilename = $tbname."_".$uuid;
} else {
	$newfilename = $tbname."_".substr(session_id(),0,10);
}
$spectbname = 'checklist_speclist';
$plantastbname = 'checklist_pllist';

//USER COPY - PARA PODER MARCAR REGISTROS
//if ($uuid>0) {
//$newtbname = 'tempPlots_'.$uuid;
//$newtbname2 = 'tempPlantas_'.$uuid;
//$newtbname3 = 'tempSpec_'.$uuid;
//
//$qq = "SELECT date_format(CREATE_TIME,'%Y-%m-%d') AS data1, date_format(CURRENT_DATE(),'%Y-%m-%d') AS data2, 
//DATEDIFF(CREATE_TIME,CURRENT_DATE()) AS DIFFS FROM information_schema.tables WHERE table_schema = '".$dbname."' AND table_name = '".$newtbname."'";
//$res = mysql_query($qq,$conn);
//$rww = mysql_fetch_assoc($res);
//$nrr = mysql_numrows($res);
//$newtbsql = "CREATE TABLE ".$newtbname." SELECT list.Marcado,tb.* FROM `".$tbname."` as tb LEFT JOIN (SELECT * FROM `".$tbname."UserLists` WHERE UserID=".$uuid.") AS list  ON list.nomeid=tb.nomeid";
//} 
//else {
//$newtbname = 'tempPlots_'.substr(session_id(),0,10);
//$newtbname2 = 'tempPlantas_'.substr(session_id(),0,10);
//$newtbname3 = 'tempSpec_'.substr(session_id(),0,10);
//
//$nrr = 0;
//$newtbsql = "CREATE TABLE ".$newtbname." SELECT 0 as Marcado,tb.* FROM `".$tbname."` as tb";
//}



////echo $nrr."  este é o valor";
////SE A TABELA TEM 10 DIAS, ATUALIZA A TABELA DO USUARIO
//if ($rww['DIFFS']>10 || $nrr==0) {
//	//RETRIEVE TAGS AND  PERSONAL TABLE
//	$qq = "DROP TABLE ".$newtbname;
//	mysql_query($qq,$conn);
//
//	mysql_query($newtbsql,$conn);
//	$qq = "ALTER TABLE `".$newtbname."`  ENGINE = InnoDB";
//	mysql_query($qq,$conn);
//	$qq = "ALTER TABLE ".$newtbname." ADD PRIMARY KEY (nomeid)";
//	mysql_query($qq,$conn);
//}
//
$numericfilters = array();
//$numericfilters[] = "Marcado";
$numericfilters[]  = 'NSPP'; 
$numericfilters[]  = 'NPLANTAS'; 
$numericfilters[]  = 'NSPECS'; 

//CABEÇALHO DA TABELA
$headd = array("Marcado", "nomeid", "Pais", "MajorArea", "MinorArea", "Localidade", "LocalSimples", "Latitude", "Longitude", "Altitude", "Parcela", "idd", "tableref", "TempID", "HABT", "NSPP", "NPLANTAS", "NSPECS");

$headexplan = array("Marcar ou desmarcar o registro","Identificador da localidade","Pais da localidade","Estado ou divisão política de primeira ordem num país","Município ou divisão política de segunda ordem num país","Localidade completa - todo o caminho hierárquico dentro de MinorArea","Localidade mais específica - aquela à qual as informaçõe da linha se refere", "Latitude em décimos de grau se houver", "Longitude em décimos de grau se houver","Altitude em metros", "Caso a localidade seja uma parcela, haverá um link para visualizar ou para baixar os dados das plantas  na parcela", "O identificador da localidade","A tabela do identificador", "O identificador desta planilha", "Visualizar num mapa os habitats associados à localidade", "Número de ESPÉCIES na localidade","Número de plantas marcadas na localidade","Número de ESPECÍMENES coletados na localidade");


$exportcols = array("false","false","true","true","true","true","true", "true","true", "true","true","false","false","false","false","true","true","true");
$colw = array(
"Marcado" => 70,
"nomeid" => 0,
 "Pais" => 60,
 "MajorArea" => 80,
 "MinorArea" => 80,
 "Localidade" => 210,
 "LocalSimples" => 150,
 "Latitude" => 0,
 "Longitude" => 0,
 "Altitude" => 0,
 "Parcela" => 110,
 "idd" => 0,
 "tableref" => 0,
 "TempID" => 0,
 "HABT" => 50,
 "NSPP" => 60,
 "NPLANTAS" => 80,
 "NSPECS" => 60);

//copia cabecalho para gerar ARRAYS PARA ATRIBUIR FORMATO (atribuido adiante)
$listvisible = $headd;
$filt = $headd; //define colunas com filtro
$filt2 = $headd;
$coltipos = $headd; //define tipo das colunas

//FAZ UM LOOP PARA CADA COLUNA E DEFINE OS ARRAYS DE FORMATO
	//COLUNAS SEM FILTRO
	//$nofilter = array("MAP", "OBS", "HABT","IMG");
	$nofilter = array("Marcado");
	//COLUNAS QUE SAO IMAGENS
	///$numericfilter = array("NPLANTAS", "NSPECS","NSPP");
	$imgfields = array("Parcela", "HABT", "NPLANTAS", "NSPECS","NSPP");
	//COLUNAS QUE NAO DEVEM APARECER
	$hidefields = array("nomeid", "idd", "tableref","TempID","Latitude", "Longitude", "Altitude");
	$i=1;
	$colidx = array();
	$collist = array();
	$coltipos = array();
	$colalign = $headd;
	$hidemenu = array();
	//mygrid.setColAlign("right,left,left,right,center,left,center,center");
	//mygrid.setColTypes("dyn,edtxt,ed,price,ch,co,ra,ro");
	foreach ($headd as $kk => $vv) {
		$qqr = "SELECT 0 as Marcado, tb.*  FROM ".$tbname." as tb  PROCEDURE ANALYSE() WHERE Field_name LIKE '%".$tbname.".".$vv."%'";
		$rr = @mysql_query($qqr,$conn);
		$row = @mysql_fetch_assoc($rr);
		if (!in_array($vv,$nofilter)) {
			if (in_array($vv,$numericfilter)) {
				$filt[$kk] = '#connector_text_filter';
				$colalign[$kk] = "right";
				$filt2[$kk] = "connector";
			} else {
				$filt[$kk] = "#connector_text_filter";
				$colalign[$kk] = "left";
				$filt2[$kk] = "connector";
			}
		} else {
				$filt[$kk] = '';
				$filt2[$kk] = "connector";
				$colalign[$kk] = "left";
		}
		if (!in_array($vv,$imgfields)) {
			if (in_array($vv,$numericfilter)) {
				$coltipos[$kk] = "rotxt";
			} else {
				$coltipos[$kk] = "rotxt";
			}
		} else {
			$coltipos[$kk] = 'ro';
			if (empty($colalign[$kk])) {
				$colalign[$kk] = "center";
			}
			if ($vv=='EDIT') {
			} else {
				$colidx[] = ($i-1);
			}
		}
		if (!in_array($vv,$hidefields)) {
			$listvisible[$kk] = 'false';
			$hidemenu[] = 'false';
		} else {
			$listvisible[$kk] = 'true';
			$hidemenu[] = 'true';
		}
		$collist[] = $i;
		$i++;
	}
	
//MUDA A PRIMEIRA COLUNA, Incluido PARA EDITAVEL CHECKBOX
$coltipos[0] = 'ch';
$colalign[0]  = 'center';

//IMPLODE ARRAY GERANDO STRINGS COM VALORES SEPARADOS POR virgula
	$hdd = implode(",",$headd);
	$ffilt = implode(",",$filt);
	$ffilt2 = implode(",",$filt2);
	$collist = implode(",",$collist);
	$colw = implode(",",$colw);
	$coltipos = implode(",",$coltipos);
	$listvisible = implode(",",$listvisible);
	$colidx = implode(",",$colidx);
	$colalign = implode(",",$colalign);
	$hidemenu = implode(",",$hidemenu);
	$exportcols = implode(",",$exportcols);
	$headexplan= implode(",",$headexplan);

//$qq = "DROP TABLE `checklist_plots_lixo`";
//mysql_query($qq,$conn);

//CONTA O NUMERO DE REGISTROS PARA DYNAMIC LOADING DO GRID
$qq = "SELECT count(*) as nrecs FROM ".$tbname;
$rr = @mysql_query($qq,$conn);
$row = @mysql_fetch_assoc($rr);
$nrecs = $row['nrecs'];

//EXTRAI A URL 
$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);

//NOME DO ARQUIVO QUE EXECUTA O GRID
//IF ($uuid>0) {
$fnn = $newfilename.".php";
//} else {
//$fnn = $newtbname.".php";
//}
$fh = fopen("temp/".$fnn, 'w');
$stringData = "<?php
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");";
$stringData .= "    
function myUpdate(\$action){
    \$status = \$action->get_value('Marcado');
    \$idsp = \$action->get_id();";
    if ($uuid>0) {
$stringData .= "    
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_plotsUserLists` WHERE TempID=\".\$idsp.\"  AND UserID='".$uuid."'\");";
} else {
$stringData .= "    
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_plotsUserLists` WHERE TempID=\".\$idsp.\"  AND SessionID='".$sesid."'\");";
}
$stringData .= "  
    \$nru = mysql_numrows(\$ru);
    if (\$nru!=\$status) {
       if (\$status==1) {";
       if ($uuid>0) {
$stringData .= "    
     \$qinn = \"INSERT INTO  `checklist_plotsUserLists` (`Marcado`,`TempID`,`UserID`) VALUES ('1' ,'\".\$idsp.\"','".$uuid."')\";";
} else {
$stringData .= "  
     \$qinn = \"INSERT INTO  `checklist_plotsUserLists` (`Marcado`,`TempID`,`SessionID`) VALUES ('1' ,'\".\$idsp.\"','".$sesid."')\";";
}
$stringData .= "  
     }  else {";
       if ($uuid>0) {
$stringData .= "    
     \$qinn = \"DELETE FROM  `checklist_plotsUserLists`  WHERE TempID='\".\$idsp.\"' AND UserID='".$uuid."'\";";
} else {
$stringData .= "  
     \$qinn = \"DELETE FROM  `checklist_plotsUserLists`  WHERE TempID='\".\$idsp.\"' AND SessionID='".$sesid."'\";";
}
$stringData .= "  
     }  
     \$ru = mysql_query(\$qinn);
   }          
   \$action->success();
}
function custom_format_list(\$data)
{

  \$mark = \$data->get_value(\"Marcado\");
  \$recid = \$data->get_id();";
if ($uuid>0) {
$stringData .= "    
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_plotsUserLists` WHERE TempID=\".\$recid.\"  AND UserID='".$uuid."'\");";
} else {
$stringData .= "    
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_plotsUserLists` WHERE TempID=\".\$recid.\"  AND SessionID='".$sesid."'\");";
}
$stringData .= "    
    \$ruw = mysql_fetch_assoc(\$ru);
    \$data->set_value(\"Marcado\", \$ruw['Marcado']);

\$idd = \$data->get_value(\"idd\");
\$tableref = \$data->get_value(\"tableref\");
if ((\$data->get_value(\"HABT\"))>0) {
  \$imagen=\"<img style='cursor:pointer;' src='icons/environment_icon.png' height='17' onclick=\\\"javascript:small_window('".$url."/plothabitat_createkml_byspecies_form.php?tableref=\".\$tableref.\"&idd=\".\$idd.\"&ispopup=1',700,500,'Habitats');\\\" onmouseover=\\\"Tip('Ver habitats');\\\" />\";
} else {
  \$imagen = \" \";
}
\$data->set_value(\"HABT\",\$imagen);

if ((\$data->get_value(\"NSPP\"))>0) {
  \$imagen=\"<img style='cursor:pointer;' src='icons/flowers.png' height='17' onclick=\\\"javascript:small_window('".$url."/checklist_species_form.php?tableref=\".\$tableref.\"&idd=\".\$idd.\"&ispopup=1&update=0;',700,500,'Ver espécies');\\\" onmouseover=\\\"Tip('Ver espécies');\\\" \><sup>  \".\$data->get_value(\"NSPP\").\"</sup>\";
} else {
  \$imagen = \" \";
}
\$data->set_value(\"NSPP\",\$imagen);

if ((\$data->get_value(\"NPLANTAS\"))>0) {
  \$imagen=\"<img style='cursor:pointer;' src='icons/tree-icon.png' height='17' onclick=\\\"javascript:small_window('".$url."/checkllist_plantas_save.php?tbname=".$spectbname."&tableref=\".\$tableref.\"&idd=\".\$idd.\"&ispopup=1',700,500,'Ver plantas');\\\" onmouseover=\\\"Tip('Ver plantas');\\\" \><sup>  \".\$data->get_value(\"NPLANTAS\").\"</sup>\";
} else {
  \$imagen = \" \";
}
\$data->set_value(\"NPLANTAS\",\$imagen);
if ((\$data->get_value(\"NSPECS\"))>0) {
   \$imagen= \"<img style='cursor:pointer;' src='icons/specimen-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/checklist_specimens_save.php?tbname=".$plantastbname."&tableref=\".\$tableref.\"&idd=\".\$idd.\"&ispopup=1',950,500,'Especimenes');\\\"  onmouseover=\\\"Tip('Visualizar amostras');\\\" /><sup>  \".\$data->get_value(\"NSPECS\").\"</sup>\";
} else {
  \$imagen = \" \";
}
\$data->set_value(\"NSPECS\",\$imagen);  

\$plot = \$data->get_value(\"Parcela\");
\$plotdim = explode(\"x\",\$plot);
\$plotm2 = (\$plotdim[1]+0)*(\$plotdim[1]+0);
\$plotm2 = \"<small>\".\$plotm2.\"m<sup>2</sup><small>\";
if (!empty(\$plot)) {
  \$imagen=\"<img style='cursor:pointer;' src='icons/icon_plot.png' height='20' onclick=\\\"javascript:small_window('".$url."/speciesINplots-popup.php?gazetteerid=\".\$idd.\"&ispopup=1',1000,800,'Mapas de parcelas');\\\" onmouseover=\\\"Tip('Visualizar plantas na parcela');\\\" />\";
  \$img2= \"<img style='cursor:pointer;' src='icons/download.png' height='20' onclick=\\\"javascript:small_window('".$url."/export-plotdata-save.php?gazetteerid=\".\$idd.\"&ispopup=1',900,500,'Baixar dados da parcela');\\\" onmouseover=\\\"Tip('Baixar dados da parcela');\\\" />\";
  \$imagen = \$imagen.\"&nbsp;\".\$img2.\"&nbsp;\".\$plot;
} else {
  \$imagen = \" \";
}
\$data->set_value(\"Parcela\",\$imagen);
}
function removeoperators(\$data){
  \$val = str_replace('>','',\$data);
  \$val = str_replace('<','',\$val);
  \$val = str_replace('=','',\$val);  
  return \$val;
}
function custom_filter(\$filter_by){
";
if (count($numericfilters)>0) {
$i=1;
$idxx = '
$idxss = array('; 
foreach ($numericfilters as $nuvar) {
	$stringData .= "
    \$index".$i." = \$filter_by->index('".$nuvar."');";
	if ($i==1) {
    	$idxx .= "\$index".$i;
	} else {
    	$idxx .= ",\$index".$i;
	}
	$i++;
}
$idxx .= ");";
	$stringData .= $idxx;
} else {
	$stringData .= '\$idxss = array();';
}
$stringData .= "
   foreach (\$idxss as \$idx) {
    if (\$idx!==false) {
      \$vv =  \$filter_by->rules[\$idx][\"value\"];
      if (substr(\$vv,0,1)=='>') {
        \$filter_by->rules[\$idx][\"operation\"]=\">\";
        //\$val = str_replace('>','',\$vv);
      }
      if (substr(\$vv,0,1)=='<') {
        \$filter_by->rules[\$idx][\"operation\"]=\"<\";
        //\$val = str_replace('<','',\$vv);
      }
      if (substr(\$vv,0,1)=='=') {
        \$filter_by->rules[\$idx][\"operation\"]=\"=\";
        //\$val = str_replace('=','',\$vv);
      }
      \$filter_by->rules[\$idx][\"value\"] = removeoperators(\$filter_by->rules[\$idx][\"value\"]);
    }
  }
}";

//".($nrecs+1)."
//////CONECTA O GRID AOS DADOS USANDO MYSQL E APLICANDO OS FORMATOS DEFINIDOS
$stringData .= "
\$grid = new GridConnector(\$res);
\$grid ->event->attach(\"beforeRender\",\"custom_format_list\");
\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
\$grid ->dynamic_loading(200);
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid ->render_sql(\"SELECT 0 as Marcado, tb.* FROM `".$tbname."` as tb ORDER BY tb.NSPP DESC,tb.Localidade ASC\",\"TempID\",\"".$hdd."\");
?>";
//\$grid ->render_sql(\"SELECT * FROM `".$newtbname."` ORDER BY NSPP DESC,Localidade ASC\",\"nomeid\",\"".$hdd."\");

//\$grid ->render_sql(\"SELECT list.Marcado,tb.* FROM `".$tbname."` as tb LEFT JOIN (SELECT * FROM `".$tbname."UserLists` WHERE UserID=".$uuid.") AS list ON  list.nomeid=tb.nomeid ORDER BY NSPP DESC,Localidade ASC\",\"TempID\",\"".$hdd."\");

fwrite($fh, $stringData);
fclose($fh);

$qq = "CREATE TABLE IF NOT EXISTS `".$tbname."UserLists` (
Marcado TINYINT(1),
ListID INT(10) unsigned NOT NULL auto_increment,
TempID INT(10),
UserID INT(10),
SessionID CHAR(255),
PRIMARY KEY (ListID)) CHARACTER SET utf8 ENGINE = InnoDB";
@mysql_query($qq,$conn);


$arrofpass = array(
'ffields'   => $hdd,
'filtros'  => $ffilt,   
'filtros2'  => $ffilt2,   
'collist'  => $collist,  
'colw'  => $colw,   
'coltipos'  => $coltipos,   
'listvisible'  => $listvisible,   
'ispopup'  => 1,   
'nrecs' => $nrecs,
'tbname' => $tbname,
'fname' => $fnn,
'colidx' => $colidx,
'colalign' => $colalign,
'hidemenu' => $hidemenu,
'usertbname' => $newtbname,
'exportcols' => $exportcols,
'headertxt' => $headexplan
);

//$_SESSION['arrtopass'] = serialize($arrofpass);
//header("location: checklist_view_generic.php");
//$_SESSION['checklistarray']['plots'] = serialize($arrofpass);
//$_SESSION['checklist_plots'] = serialize($arrofpass);
//$_SESSION['arrofpass']  = serialize($arrofpass);
//header("location: checklist_view_generic.php");
//header("location: checklistview_tabber.php");
if ($seepop==1) {
$_SESSION['arrofpass'] = serialize($arrofpass);
//echopre($arrofpass);
header("location: checklist_view_generic.php");
} else {
$_SESSION['checklist_plots'] = serialize($arrofpass);
}
echo "CONCLUIDO";
?>