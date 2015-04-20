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

//CABECALHO
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}

//apaga arquivo de progresso
$qqz = "DROP TABLE `temp_".$tbname."`";
@mysql_query($qqz,$conn);


//SAVE RESULTS IN A FILE

if (empty($herbariumsigla)) {
	$herbariumsigla = 'HERB_NO';
}

$headd = array("EDIT",
"EXISTE",
"NDuplic",
$herbariumsigla,
"Herbaria",
"IMGSpec",
"NirSpectra",
"Silica",
"Fert",
"DATA",
"EspecimenID ",
"PlantaID ",
"DetID",
"COLETOR",
"NUMERO",
"FAMILIA",
"NOME",
"LONGI",
"LATI",
"OBS",
"MAP",
"HABT",
"HABITO",
"PRJ",
"PROJETOstr");




$colw = array("EDIT" => 70,
"EXISTE" => 30,
"NDuplic" => 55,
$herbariumsigla => 50,
"Herbaria" => 80,
"IMGSpec" => 70,
"NirSpectra" => 70,
"Silica" => 90,
"Fert" => 60,
"DATA" => 70,
"EspecimenID " => 0,
"PlantaID " => 0,
"DetID" => 0,
"COLETOR" => 80,
"NUMERO" => 60,
"FAMILIA" => 80,
"NOME" => 150,
"LONGI" => 60,
"LATI" => 60,
"OBS" => 50,
"MAP" => 50,
"HABT" => 50,
"HABITO" => 65,
"PRJ" => 0,
"PROJETOstr" => 80);

$colvalid = $colw;
foreach ($colvalid as $kk => $vv) {
	if ($kk=='NDuplic') {
		$nv = 'ValidInteger';
	} else {
		$nv = '';
	}
	$colvalid[$kk] = $nv;
}

$coltipos = array("EDIT" =>'ro',
"EXISTE" => 'ch',
"NDuplic" => 'ed',
$herbariumsigla => 'ro',
"Herbaria" => 'clist',
"IMGSpec" => 'ro',
"NirSpectra" => 'ro',
"Silica" => 'clist',
"Fert" => 'clist',
"DATA" => 'dhxCalendar',
"EspecimenID " => 'ro',
"PlantaID " => 'ro',
"DetID" => 'ro',
"COLETOR" => 'ro',
"NUMERO" => 'ro',
"FAMILIA" => 'ro',
"NOME" => 'ro',
"LONGI" => 'ro',
"LATI" => 'ro',
"OBS" => 'ro',
"MAP" => 'ro',
"HABT" => 'ro',
"HABITO" => 'ro',
"PRJ" =>'ro',
"PROJETOstr" => 'ro');

$colalign = array("EDIT" =>'center',
"EXISTE" => 'center',
"NDuplic" => 'center',
$herbariumsigla => 'right',
"Herbaria" => 'left',
"IMGSpec" => 'center',
"NirSpectra" => 'center',
"Silica" => 'left',
"Fert" => 'left',
"DATA" => 'center',
"EspecimenID " => 'center',
"PlantaID " => 'center',
"DetID" => 'center',
"COLETOR" => 'left',
"NUMERO" => 'right',
"FAMILIA" => 'left',
"NOME" => 'left',
"LONGI" => 'right',
"LATI" => 'right',
"OBS" => 'center',
"MAP" => 'center',
"HABT" => 'center',
"HABITO" => 'left',
"PRJ" =>'center',
"PROJETOstr" => 'left');

	$noupdatefor = array("EDIT",
"IMGSpec",
$herbariumsigla,
"EspecimenID ",
"PlantaID ",
"DetID",
"COLETOR",
"NUMERO",
"FAMILIA",
"NOME",
"LONGI",
"LATI",
"OBS",
"MAP",
"HABT",
"HABITO",
"PRJ",
"PROJETOstr");

	$listvisible = $headd;
	$filt = $headd;
	$filt2 = $headd;
	//$coltipos = $headd;
	$nofilter = array("PRJ", "EDIT", "HABT", "MAP","OBS" );
	$imgfields = array("OBS", "IMGSpec", "PRJ", "EDIT", "HABT", "MAP" );
	$numericfilter = array("NDuplic","NUMERO");
	if(!isset($uuid) || (trim($uuid)=='') || $acclevel=='visitor') {
		$hidefields = array("EDIT");
	} else {
		$hidefields = array("DetID");
	}
	$i=1;
	$ncl = count($headd)-count($imgfields)-count($hidefields);
	$nimg = count($imgfields);
	$nimg = $nimg*50;
	$cl = floor((900-$nimg)/$ncl);
	$colidx = array();
	$collist = array();
	$hidemenu = array();
	//mygrid.setColAlign("right,left,left,right,center,left,center,center");
	//mygrid.setColTypes("dyn,edtxt,ed,price,ch,co,ra,ro");
	foreach ($headd as $kk => $vv) {
		$qqr = "SELECT * FROM ".$tbname." PROCEDURE ANALYSE() WHERE Field_name LIKE '%".$tbname.".".$vv."%'";
		$rr = @mysql_query($qqr,$conn);
		$row = @mysql_fetch_assoc($rr);
		if (!in_array($vv,$nofilter)) {
			if (in_array($vv,$numericfilter)) {
				$filt[$kk] = '#connector_text_filter';
				$filt2[$kk] = "connector";
			} else {
				$filt[$kk] = "#connector_text_filter";
				$filt2[$kk] = "connector";
			}
		} else {
				$filt[$kk] = '';
				$filt2[$kk] = "connector";
		}
		if (!in_array($vv,$imgfields) &&  !in_array($vv,$noupdatefor)) {

		} else {
			$colidx[] = ($i-1);
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
	$fnn = $tbname.".php";
	$qq = "SELECT count(*) as nrecs FROM ".$tbname;
	$rr = @mysql_query($qq,$conn);
	$row = @mysql_fetch_assoc($rr);
	$nrecs = $row['nrecs'];
	
		$url = $_SERVER['HTTP_REFERER'];
	$uu = explode("/",$url);
	$nu = count($uu)-1;
	unset($uu[$nu]);
	$url = implode("/",$uu);
	
	$traitsilica = 115;

	
	//$arrsses = '';
	$fh = fopen("temp/".$fnn, 'w');
	$stringData = "<?php
session_start();
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");
include \"../functions/MyPhpFunctions.php\";
function myUpdate(\$action){
        mysql_query(\"UPDATE `".$tbname."` SET EXISTE='{\$action->get_value('EXISTE')}'  WHERE EspecimenID='{\$action->get_id()}'\");
        mysql_query(\"UPDATE `ProcessosLIST` SET EXISTE='{\$action->get_value('EXISTE')}'  WHERE EspecimenID='{\$action->get_id()}'  AND ProcessoID='".$processoid."' \");
        \$val = \$action->get_value('NDuplic');
        \$val = \$val+0;
        \$erro=0;
        if (\$val>0) {
            mysql_query(\"UPDATE `".$tbname."` SET NDuplic='{\$action->get_value('NDuplic')}'  WHERE EspecimenID='{\$action->get_id()}'\");
            mysql_query(\"UPDATE `ProcessosLIST` SET NDuplic='{\$action->get_value('NDuplic')}'  WHERE EspecimenID='{\$action->get_id()}'\");
            \$nd = updatetraits_grid(".$duplicatesTraitID.",\$action->get_value('NDuplic'),\$action->get_id(),0, '', '".$dbname."');
        } else {
           \$erro=1;
        }
        mysql_query(\"UPDATE `".$tbname."` SET Herbaria='{\$action->get_value('Herbaria')}'  WHERE EspecimenID='{\$action->get_id()}'\");
        mysql_query(\"UPDATE `ProcessosLIST` SET Herbaria='{\$action->get_value('Herbaria')}'  WHERE EspecimenID='{\$action->get_id()}'  AND ProcessoID='".$processoid."' \");
        mysql_query(\"UPDATE `Especimenes` SET Herbaria='{\$action->get_value('Herbaria')}'  WHERE EspecimenID='{\$action->get_id()}'  \");

        mysql_query(\"UPDATE `".$tbname."` SET Fert='{\$action->get_value('Fert')}'  WHERE EspecimenID='{\$action->get_id()}'\");
        \$nd = updatetraits_grid(".$traitfertid.",\$action->get_value('Fert'),\$action->get_id(),0, '', '".$dbname."');
        if (!\$nd) {
        \$action->fail();
        } else {
        \$action->success();
        }
        
        
        mysql_query(\"UPDATE `".$tbname."` SET Silica='{\$action->get_value('Silica')}'  WHERE EspecimenID='{\$action->get_id()}'\");
        \$nd = updatetraits_grid(".$traitsilica.",\$action->get_value('Silica'),\$action->get_id(),0, '', '".$dbname."');
        //\$action->success();
        
        
}
function custom_format_spec(\$data){
	\$pltag = \$data->get_value(\"COLETOR\").\" \".\$data->get_value(\"NUMERO\");
	\$thedetid = \$data->get_value(\"DetID\");
	\$thespecimenid = \$data->get_value(\"EspecimenID\");

    if (\$data->get_value(\"IMGSpec\")==\"camera.png\") {
      \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"IMGSpec\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/showimage_taxa.php?ispopup=1&especimenid=\".\$thespecimenid.\"',700,400,'Ver imagens');\\\" onmouseover=\\\"Tip('Ver imagens da amostra # \".\$pltag.\"');\\\" >\";
      \$data->set_value(\"IMGSpec\",\$imagen);
    } else {
      \$imagen= 'FALTA';
      \$data->set_value(\"IMGSpec\",\$imagen);
    }

    \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"EDIT\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/especimenes_dataform.php?ispopup=1&especimenid=\".\$data->get_value(\"EspecimenID\").\"',1000,400,'Editar registro');\\\" onmouseover=\\\"Tip('Editar o especímene # \".\$pltag.\"');\\\" >\";
    \$imagen2=\"<img style='cursor:pointer;' src='icons/rednameicon.png' height='20' onclick=\\\"javascript:small_window('".$url."/taxonomia-popup.php?updatechecklist=1&ispopup=1&saveit=true&detid=\".\$data->get_value(\"DetID\").\"&especimenid=\".\$data->get_value(\"EspecimenID\").\"',800,400,'Editar Identificação');\\\" onmouseover=\\\"Tip('Editar Identificação da amostra # \".\$pltag.\"');\\\" >\";
    \$imgg3 =\"<img style='cursor:pointer;' src='icons/nota-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/traits_coletorvariacao.php?apagavarsess=1&saveit=1&formid=".$formnotes."&especimenid=\".\$data->get_value(\"EspecimenID\").\"',800,800,'Editando notas');\\\"  onmouseover=\\\"Tip('Edita notas da amostra # \".\$pltag.\"');\\\" >\";
    \$imagen = \$imagen.\"&nbsp;\".\$imagen2.\"&nbsp;\".\$imgg3;
    \$data->set_value(\"EDIT\",\$imagen);

    if (\$data->get_value(\"HABT\")==\"environment_icon.png\") {
      \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"HABT\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/showhabitat.php?ispopup=1&especimenid=\".\$data->get_value(\"EspecimenID\").\"',500,400,'Ver imagens');\\\"  onmouseover=\\\"Tip('Sobre o habitat da amostra # \".\$pltag.\"');\\\">\";
      \$data->set_value(\"HABT\",\$imagen);
    } else {
      \$imagen= '';
      //\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"HABT\").\"' height='20' onclick=\\\"javascript:alert('Não há informação sobre habitat para esta amostra!');\\\" onmouseover=\\\"Tip('Não há informação sobre habitat para esta amostra');\\\">\";
      \$data->set_value(\"HABT\",\$imagen);
    }
    \$imagen=\"<img style='cursor:pointer;' src='icons/edit-notes.png' height='20' onclick=\\\"javascript:small_window('".$url."/showspecimen.php?ispopup=1&especimenid=\".\$data->get_value(\"EspecimenID\").\"',400,400,'Notas');\\\" onmouseover=\\\"Tip('Notas da amostra # \".\$pltag.\"');\\\" >\";
    \$imgg =\"<img style='cursor:pointer;' src='icons/label-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/singlelabel-exec.php?ispopup=1&specimenid=\".\$data->get_value(\"EspecimenID\").\"',300,100,'Imprimindo Etiqueta');\\\"  onmouseover=\\\"Tip('Etiquetas em PDF da amostra # \".\$pltag.\"');\\\" >\";
    \$imagen = \$imagen.\"&nbsp;\".\$imgg;
    \$data->set_value(\"OBS\",\$imagen);

    \$llat = ABS($data->get_value(\"LATI\"));
    \$llong = ABS(\$data->get_value(\"LONGI\"));
    \$llcord = \$llat+\$llong;
    if (\$llcord>0) {
      \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"MAP\").\"' height='18' onclick=\\\"javascript:small_window('".$url."/mapasKML.php?ispopup=1&specimenid=\".\$data->get_value(\"EspecimenID\").\"',600,500,'Notas');\\\" onmouseover=\\\"Tip('Mapear a amostra # \".\$pltag.\"');\\\" >\";
    } else {
      \$imagen= '';
      //\"<img style='cursor:pointer;' src='icons/question-red.png' height='18' onclick=\\\"javascript:alert('Latitude & Longitude Faltando');\\\" onmouseover=\\\"Tip('Latitude e Longitude faltando para amostra # \".\$pltag.\"');\\\"  >\";
    }
    \$data->set_value(\"MAP\",\$imagen);
    
    \$pj = \$data->get_value(\"PRJ\");
    if (!empty(\$pj)) {
      \$imagen=\"<img style='cursor:pointer;' src='\".\$data->get_value(\"PRJ\").\"' height='20' onclick=\\\"javascript:alert('\".\$data->get_value(\"PROJETOstr\").\"');\\\" onmouseover=\\\"Tip('\".\$data->get_value(\"PROJETOstr\").\"');\\\" >\";
      \$data->set_value(\"PRJ\",\$imagen);
    }
}
function removeoperators(\$data){
  \$val = str_replace('>','',\$data);
  \$val = str_replace('<','',\$val);
  \$val = str_replace('=','',\$val);  
  return \$val;
}
function custom_filter(\$filter_by){
   \$index = \$filter_by->index('NDuplic');
   \$index3 = \$filter_by->index('NUMERO');
   \$index4 = \$filter_by->index('".$herbariumsigla."');
   \$index5= \$filter_by->index('NirSpectra');
   \$idxss = array(\$index,\$index3,\$index4);
   foreach (\$idxss as \$idx) {
		if (\$idx!==false) {
			\$vv =  \$filter_by->rules[\$idx][\"value\"];
			if (substr(\$vv,0,1)=='>') {
				\$filter_by->rules[\$idx][\"operation\"]=\">\";
				\$val = str_replace('>','',\$vv);
			}
			if (substr(\$vv,0,1)=='<') {
				\$filter_by->rules[\$idx][\"operation\"]=\"<\";
				\$val = str_replace('<','',\$vv);
			}
			if (substr(\$vv,0,1)=='=') {
				\$filter_by->rules[\$idx][\"operation\"]=\"=\";
				\$val = str_replace('=','',\$vv);
			}
			\$filter_by->rules[\$idx][\"value\"] = removeoperators(\$filter_by->rules[\$idx][\"value\"]);
		}
	}
}
\$grid = new GridConnector(\$res);
\$grid ->event->attach(\"beforeRender\",\"custom_format_spec\");
\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
\$grid ->dynamic_loading(100);
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid ->render_table(\"".$tbname."\",\"EspecimenID\",\"".$hdd."\");
?>";
//\$grid->set_options(\"Fert\",".$options.");
fwrite($fh, $stringData);
fclose($fh);

//\
//attach("beforeProcessing","custom_fields");
$arrofpass = array(
'ffields'   => $hdd,
'filtros'  => $ffilt,   
'filtros2'  => $ffilt2,   
'collist'  => $collist,  
'colw'  => $colw,   
'coltipos'  => $coltipos,   
'listvisible'  => $listvisible,   
'ispopup'  => $ispopup,   
'nrecs' => $nrecs,
'tbname' => $tbname,
'fname' => $fnn,
'colidx' => $colidx,
'colalign' => $colalign,
'hidemenu' => $hidemenu,
'colvalid'  => implode(",",$colvalid),
'processoid' => $processoid
);
$_SESSION['arrtopass'] = serialize($arrofpass);
echo "
  <form name='myform' action='processo-amostras-grid.php' method='post'>";
  foreach ($arrofpass as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";

?>