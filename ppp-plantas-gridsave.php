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
$qqz = "DROP TABLE `temp_ppp_plantas`";
@mysql_query($qqz,$conn);

$tbname = 'temp_ppp_mortas';


//SAVE RESULTS IN A FILE
$headd = array(
"PlantaID",
"TAGtxt",
"TAG",
"STATUS",
"SILICA",
"LOCALSIMPLES",
"LOCAL",
"FAMILIA",
"NOME");
$colw = array(
"PlantaID" => 70,
"TAGtxt" => 70,
"TAG" => 90,
"STATUS" => 90,
"SILICA" => 90,
"LOCALSIMPLES" => 120,
"LOCAL" => 150,
"FAMILIA" => 100,
"NOME" => 200
);
$colvalid = $colw;
foreach ($colvalid as $kk => $vv) {
	$colvalid[$kk] = $nv;
}

$coltipos = array(
"PlantaID" =>'ro',
"TAGtxt" => 'ro',
"TAG" => 'ro',
"STATUS" => 'clist',
"SILICA" => 'clist',
"LOCALSIMPLES" => 'ro',
"LOCAL" => 'ro',
"FAMILIA" => 'ro',
"NOME" => 'ro'
);

$colalign = array(
"PlantaID" =>'center',
"TAGtxt" => 'center',
"TAG" => 'right',
"STATUS" => 'center',
"SILICA" => 'center',
"LOCALSIMPLES" => 'left',
"LOCAL" => 'left',
"FAMILIA" => 'left',
"NOME" => 'left'
);

$noupdatefor = array(
"PlantaID",
"TAGtxt",
"TAG",
"LOCALSIMPLES ",
"LOCAL",
"FAMILIA",
"NOME"
);

$listvisible = $headd;
$filt = $headd;
$filt2 = $headd;
//$coltipos = $headd;
$nofilter = array();
$imgfields = array();
$numericfilter = array("TAG");

$i=1;
$colidx = array();
$collist = array();
$hidemenu = array("PlantaID", "LOCAL","TAGtxt");
$hidefields = array("PlantaID", "LOCAL","TAGtxt");

foreach ($headd as $kk => $vv) {
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

$fnn = $tbname."_ppp.php";
$qq = "SELECT count(*) as nrecs FROM checklist_pllist";
$rr = @mysql_query($qq,$conn);
$row = @mysql_fetch_assoc($rr);
$nrecs = $row['nrecs'];

$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);

$fh = fopen("temp/".$fnn, 'w');
$stringData = "<?php
session_start();
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");
include \"../functions/MyPhpFunctions.php\";
function myUpdate(\$action){
        //mysql_query(\"UPDATE `".$tbname."` SET SILICA='{\$action->get_value('SILICA')}'  WHERE PlantaID='{\$action->get_id()}'\");
        \$ru = mysql_query(\"SELECT EspecimenID FROM Especimenes WHERE PlantaID='{\$action->get_id()}'  ORDER BY Ano, Mes,Day DESC LIMIT 0,1\");
        \$nru = mysql_numrows(\$ru);
        if (\$nru>0) {
          \$ruw = mysql_fetch_assoc(\$ru);
          \$specid = \$ruw['EspecimenID']+0;
          \$nd = updatetraits_grid(".$traitsilica.",\$action->get_value('SILICA'),\$specid,0,'', '".$dbname."');
        } else {
          \$nd = updatetraits_grid(".$traitsilica.",\$action->get_value('SILICA'),0,\$action->get_id(),'', '".$dbname."');
        }        
        //mysql_query(\"UPDATE `".$tbname."` SET STATUS='{\$action->get_value('STATUS')}'  WHERE PlantaID='{\$action->get_id()}'\");
        \$nd = update_moni(".$statustraitid.",\$action->get_value('STATUS'),\$action->get_id(),'".$dbname."','');
        \$action->success();
}
function custom_format_pl(\$data){
  \$pltag = \$data->get_value(\"TAGtxt\");
  \$data->set_value(\"TAG\",\$pltag);

  \$pltid = \$data->get_value(\"PlantaID\");

  \$ru = mysql_query(\"SELECT traitvalueplantas(".$statustraitid.",\".\$pltid.\",'',0,0) AS status\");
  \$ruw = mysql_fetch_assoc(\$ru);
  \$data->set_value(\"STATUS\", \$ruw['status']);

  \$ru = mysql_query(\"SELECT traitvalueplantas(".$traitsilica.",\".\$pltid.\",'',0,0) AS silica\");
  \$ruw = mysql_fetch_assoc(\$ru);
  \$data->set_value(\"SILICA\", \$ruw['silica']);

}
function removeoperators(\$data){
  \$val = str_replace('>','',\$data);
  \$val = str_replace('<','',\$val);
  \$val = str_replace('=','',\$val);  
  return \$val;
}
function custom_filter(\$filter_by){
   \$index = \$filter_by->index('TAG');
   \$index3 = \$filter_by->index('PlantaID');
   \$idxss = array(\$index,\$index3);
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
\$grid ->event->attach(\"beforeRender\",\"custom_format_pl\");
\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
\$grid ->dynamic_loading(100);
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid ->render_sql(\"SELECT PlantaID,TAGtxt ,TAG, '' AS STATUS, '' AS SILICA, LOCALSIMPLES, LOCAL, FAMILIA, NOME FROM checklist_pllist\",\"PlantaID\",\"".$hdd."\");
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
  <form name='myform' action='ppp-plantas-grid.php' method='post'>";
  foreach ($arrofpass as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";

?>