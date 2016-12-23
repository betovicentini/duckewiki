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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}


//TABELA ESPECIALISTAS
$tbname = 'BiblioRefs';

$headd = array(
'Tag',
'Edit',
'BibID',
'BibKey',
'Type',
'Year',
'FirstAuthor',
'Authors',
'Title',
'Journal',
'BookTitle'
);

$colw = array(
'Tag' => 50,
'Edit'  => 80,
'BibID' => 0,
'BibKey' => 100,
'Type' => 80,
'Year' => 60,
'FirstAuthor' => 80,
'Authors' => 100,
'Title' => 300,
'Journal' => 200,
'BookTitle' => 200
);

$colvalid = array(
'Tag' => '',
'Edit'   => '',
'BibID'  => '',
'BibKey'  => '',
'Type' => '',
'Year' => '',
'FirstAuthor'  => '',
'Authors' => '',
'Title' => '',
'Journal'  => '',
'BookTitle' => ''
);

$coltipos = array(
'Tag' => 'ch',
'Edit'   => 'ro',
'BibID'  => 'ro',
'BibKey'  => 'ro',
'Type' => 'ro',
'Year' => 'ro',
'FirstAuthor'  => 'ro',
'Authors' => 'ro',
'Title' => 'ro',
'Journal'  => 'ro',
'BookTitle' => 'ro'
);

$colalign = array(
'Tag' => 'center',
'Edit'   => 'left',
'BibID'  => 'center',
'BibKey' => 'left',
'Type' => 'left',
'Year'  => 'center',
'FirstAuthor'  => 'left',
'Authors'  => 'left',
'Title' => 'left',
'Journal'  => 'left',
'BookTitle' => 'left'
);


$listvisible = $headd;
$filt = $headd;
$filt2 = $headd;

$nofilter = array('Edit');
$imgfields = array();
$numericfilter = array("BibID", "Tag", "Year");
$hidefields = array("BibID","Authors");
$i=1;
$ncl = count($headd)-count($imgfields)-count($hidefields);
$nimg = count($imgfields);
$nimg = $nimg*50;
$cl = floor((900-$nimg)/$ncl);
$colidx = array();
$collist = array();
$hidemenu = array();
foreach ($headd as $kk => $vv) {
		//$qqr = "SELECT * FROM ".$tbname." PROCEDURE ANALYSE() WHERE Field_name LIKE '%".$tbname.".".$vv."%'";
		//$rr = @mysql_query($qqr,$conn);
		//$row = @mysql_fetch_assoc($rr);
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
		$colidx[] = ($i-1);
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
	$fnn = 'temp_'.$tbname."_".$uuid.".php";

	$qq = "SELECT count(*) as nrecs FROM ".$tbname;
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
        \$action->success();
}
function custom_format_list(\$data)
{
\$imagen=\"<img style='cursor:pointer;' src='icons/bibtex.png' height='25' onclick=\\\"javascript:small_window('".$url."/bibtex_edit.php?bibid=\".\$data->get_id().\"&ispopup=1;',700,500,'Editar BibTex');\\\" onmouseover=\\\"Tip('Editar \".\$data->get_value(\"BibKey\").\"  ');\\\" \>\";
\$data->set_value('Edit',\$imagen);
}
function removeoperators(\$data){
  \$val = str_replace('>','',\$data);
  \$val = str_replace('<','',\$val);
  \$val = str_replace('=','',\$val);  
  return \$val;
}
function custom_filter(\$filter_by){
   \$index = \$filter_by->index('Year');
   \$index2 = \$filter_by->index('Tag');
   \$index3 = \$filter_by->index('BibID');
   \$idxss = array(\$index,\$index2,\$index3);
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
\$grid ->event->attach(\"beforeRender\",\"custom_format_list\");
\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
\$grid ->dynamic_loading(100);
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid ->render_sql(\"SELECT checkbib(
'".$bibids."', ';', BibID
) as Tag,'' as Edit,prf.* FROM BiblioRefs AS prf\",\"BibID\",\"".$hdd."\");
?>";
//\$grid ->render_table(\"".$tbname."\",\"BiblioRefs\",\"".$hdd."\");
//\$grid->set_options(\"Fert\",".$options.");
fwrite($fh, $stringData);
fclose($fh);

//echo $colidx;
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
'colvalid'  => implode(",",$colvalid)
);
//$_SESSION['arrtopass'] = serialize($arrofpass);
if ($nrecs>0) {
echo "
  <form name='myform' action='bibtext-grid.php' method='post'>";
  foreach ($gget as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
  foreach ($arrofpass as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";
} else {

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link href='css/jquery-ui.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$body='';
$title = 'Script Teste Executa';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
    Não há referências na base.<br /> <input type='button' style=\"cursor:pointer;\" 
 onmouseover=\"Tip('Importar Referências');\" onclick = \"javascript:small_window('import-bibtex.php?ispopup=0',800,400,'Importar Referências Bibliográficas');\"  value='Importar BibTex' />";
 $which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
}


?>