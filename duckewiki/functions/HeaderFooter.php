<?php
include 'databaseSettings.php';
require_once htmlspecialchars($relativepathtoroot.$databaseconnection, ENT_QUOTES);
$linecolor1='lightyellow';
$linecolor2= '#EEE9E9';
$bgi=1;

/////EXPLICA O CONTEUDO PARA $which_java & $which_css arrays///
//LAYOUT GERAL - muito desorganizado bagunçado! :(
////"<link href='css/geral.css' rel='stylesheet' type='text/css' >"
//nessário para MENU + funcao  no arquivo ../includes/nomedasuabase.php
////"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >",
////"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
////"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
////"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>

//para usar o AUTOSUGGEST em campos de busca 
////"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
////"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"

//para usar o TABBER com hidden no click, como no formulario de busca
////"<link rel='stylesheet' type='text/css' media='screen' href='javascript/tabber/tabber.css' >",
////"<script type='text/javascript' src='javascript/jquery-latest.js'></script>",
////"<script type='text/javascript'> $(document).ready(function(){ $('.toggle_container').hide(); $('h2.trigger').click(function(){ $(this).toggleClass('active').next().slideToggle('slow'); }); });</script>",

//BATCH UPLOAD IMAGENS - para a funcionalidade do drag-&-drop upload de arquivos de várias imagens simultaneamente
////"<link rel='stylesheet' type='text/css' href='javascript/fileuploader.css' >"
////"<script type='text/javascript' src='javascript/jquery-latest.js'></script>"

//UPLOAD IMAGENS FIELD - para subir + de uma imagem individualmente em campos
////"<link rel='stylesheet' type='text/css' media='screen' href='css/Stickman.MultiUpload.css' >"
//mootools precisa ser chamado antes de Stickman.MultiUpload.js, nesta orderm:
///"<script type='text/javascript' src='javascript/mootools.js'></script>"
////"<script type='text/javascript' src='javascript/Stickman.MultiUpload.js'></script>"


//ZOOM DE IMAGENS - para a funcionalidade do zoom em imagens
////<link rel='stylesheet' href='magiczoomplus/magiczoomplus/magiczoomplus.css' type='text/css' media='screen' >
////<script src='magiczoomplus/magiczoomplus/magiczoomplus.js' type='text/javascript'></script>


////DHTMLx - para funcionalidade dos grid e tabs
//"<link rel=\"stylesheet\" type=\"text/css\" href=\"dhtmlxconnector/dhtmlxTabbar/dhtmlxtabbar.css\">",
//"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.css' >",
//"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn_bricks.css' >",
//"<link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.css' >"
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxcommon.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxTabbar/dhtmlxtabbar.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxTabbar/dhtmlxcontainer.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgridcell.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_export.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_ssc.js'></script>",
//"<script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_mcol.js'></script>"

///SORTABLE TABLE - old, must be replaced with DHTMLx grid
//"<script type=\"text/javascript\" src=\"javascript/sorttable/common.js\"></script>",
//"<script type=\"text/javascript\" src=\"javascript/sorttable/css.js\"></script>",
//"<script type=\"text/javascript\" src=\"javascript/sorttable/standardista-table-sorting.js\"></script>"
  

//NOVO cabeçalho que usar arrays de input para css e javascripts (reduzir loading the scripts desnecessarios)
function FazHeader($title,$body,$which_css,$which_java,$menu) {
include "functions/databaseSettings.php";
if (empty($title)) {
	$title = GetLangVar('title');
}
DEFINE('_ISO','charset=UTF-8');
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"pt-br\" xml:lang=\"en\">
<head>
  <meta name=\"title\" content=\"".$metatitle."\" />
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset='UTF-8'\" />
";
if (!empty($metagooglesite)) {  
echo "  <meta name=\"google-site-verification\" content=\"".$metagooglesite."\" />
";
}
echo "  <meta name=\"url\" content=\"".$metaurl."\" />
  <meta name=\"robots\" content=\"all\" />
  <meta name=\"language\" content=\"pt-br\" />
  <meta name=\"description\" content=\"".$metadesc."\" />
  <meta name=\"keywords\" content=\"".$metakeyw."\" />
  <meta name=\"autor\" content=\"Alberto Vicentini - INPA\" />
  <meta name=\"company\" content=\"".$metacompany."\" /> 
  <meta http-equiv=\"imagetoolbar\" content=\"no\" />
  <title>".$title."</title>
  ";
foreach ($which_css as $vv) {
echo "
  ".$vv;
}  
foreach ($which_java as $vv) {
echo "
  ".$vv;
}  
echo " 

</head>
<body ".$body.">";

//funcao Menu definida em ../includes/nomedasuabase.php
if ($menu) {
if (($_SESSION['userid']+0)==0) {
echo "
<div style='position: absolute; top: 5px; left: 5px; cursor: pointer;'  onclick = \"javascript: self.location='index.php?ispopup=1';\" >
<img  src=\"icons/".$sitelogo."\"  height='100' />&nbsp;<span style='position: relative; top: 20px; vertical-align: top; font-size: 3em; color: #8B0000;' >
".$sitetitle."</span>
</div>
";
} else {
echo "
<div style='position: absolute; top: 5px; left: 5px; cursor: pointer;' onclick = \"javascript: self.location='index.php?ispopup=1';\" >
<img  src=\"icons/".$sitelogo."\"  height='70' />&nbsp;<span style='position: relative; top: 5px; vertical-align: top; font-size: 1.5em; color: #8B0000;' >
".$sitetitle."</span>
</div>
";
}
} 
//echo "
//<div>";
//if (!isset($_SESSION['userid'])) {
//	echo "
//<h1>$title</h1>";
//} 
//else {
//	echo "
//<h3>".GetLangVar('title')."</h3>";
//}
//Menu($title);
//
echo "
<div id='container'>
";


}


function FazFooter($which_java,$calendar=FALSE,$footer=TRUE) {

if ($footer) {
	echo "
</div>
<div>
";
	if ($_SESSION['userid']>0) {
	echo "
<h2><i>".GetLangVar('nameautenticado')." ".$_SESSION['userfirstname']." ".$_SESSION['userlastname']." (".$_SESSION['sessiondate'].")</i></h2>";
	}  else {
	echo "
<h2>&nbsp;</h2>";
	}
	$stilo =" cursor: pointer;";
	echo "
<table align='center'>
   <tr>
     <td align='center'>
        <img style='border: 0px; height: 100px; cursor: pointer;'  src='icons/fapeam.png' alt='Inpa'  onclick = \"javascript: window.open(
  'http://http://www.fapeam.am.gov.br/','_blank' );\" />&nbsp;&nbsp;&nbsp;
     </td>
     <td align='center'>
        <img style='border: 0px; height: 80px; cursor: pointer;'  src='icons/inpa_gov.png' alt='Inpa'  onclick = \"javascript: window.open(
  'http://www.inpa.gov.br','_blank' );\" />&nbsp;&nbsp;&nbsp;
     </td>
     <td align='center'>
     <img style=\"".$stilo." vertical-align:text-bottom;\"  height='50px' src='icons/ctfs_logo.png' onmouseover = \"javascript: Tip('CTFS - Center for Tropical Forest Science') ;\"  onclick = \"javascript: window.open('http://www.forestgeo.si.edu','_blank' );\" />
     </td>
   </tr>
 </table>";
} else {
	echo "
<!--////////////////-->
<!--FINAL DO CONTETUDO-->
<!--INICIO RODAPÉ -->
<!--////////////////-->
<div>
";
}
if ($calendar) { 
	echo "
<iframe name='gToday:normal:agenda.js' id='gToday:normal:agenda.js' src='calendar/ipopeng.htm' scrolling='no' frameborder='0' style='visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;'></iframe>";
}
foreach ($which_java as $vv) {
echo "
  ".$vv;
}  
echo "
</div>
</body>
</html>
";
}


//old header still in use!
function HTMLheaders($body) {
$title = GetLangVar('title');
echo "
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
<html lang='pt'>
<head>
  <title>$title</title>
  <meta name='title' content='".$metatitle."' >
  <meta http-equiv='Content-Type' content=\"text/html; charset='UTF-8'\">
  <meta name='google-site-verification' content='sBY5TPzoJ09tOWC6GGacC8LouyYCSS8ObhOybnFn84k' >
  <meta name='url' content='".$metaurl."' >
  <meta name='robots' content='all' />
  <meta name='language' content='pt-br' />
  <meta name='description' content='".$metadesc."'>
  <meta name='keywords' content='".$metakeyw."' >
  <meta name='autor' content='Alberto Vicentini - INPA' >
  <meta name='company' content='".$metacompany."' >
  <meta name='revisit-after' content='30' >
  <meta http-equiv='imagetoolbar' content='no' >
  <link href='css/geral.css' rel='stylesheet' type='text/css' >
  <link rel='stylesheet' type='text/css' media='screen' href='css/Stickman.MultiUpload.css' >
  <link rel='stylesheet' type='text/css' href='css/cssmenu.css' >
  <link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >
  <link rel='stylesheet' type='text/css' media='screen' href='javascript/tabber/tabber.css' >
  <link rel=\"stylesheet\" type=\"text/css\" href=\"dhtmlxconnector/dhtmlxTabbar/dhtmlxtabbar.css\">
  <link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.css' >
  <link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn_bricks.css' >
  <link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.css' >
  <link rel='stylesheet' type='text/css' href='javascript/fileuploader.css' >
  <link rel='stylesheet' href='magiczoomplus/magiczoomplus/magiczoomplus.css' type='text/css' media='screen' >
  <script type='text/javascript' src='javascript/ajax_framework.js'></script>
  <script type='text/javascript' src='javascript/jquery-latest.js'></script>
  <script type='text/javascript'>
   $(document).ready(function(){
     $('.toggle_container').hide();
     $('h2.trigger').click(function(){
       $(this).toggleClass('active').next().slideToggle('slow');
     });
    });
  </script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxcommon.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxTabbar/dhtmlxtabbar.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxTabbar/dhtmlxcontainer.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgridcell.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_export.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_ssc.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_mcol.js'></script>

  <script type='text/javascript' src='javascript/sorttable/common.js'></script>
  <script type='text/javascript' src='javascript/sorttable/css.js'></script>
  <script type='text/javascript' src='javascript/sorttable/standardista-table-sorting.js'></script>
  <script type='text/javascript' src='javascript/mootools.js'></script>
  <script type='text/javascript' src='javascript/teste.js'></script>
  <script type='text/javascript' src='javascript/Stickman.MultiUpload.js'></script>
  <script type='text/javascript' src='css/cssmenuCore.js'></script>
  <script type='text/javascript' src='css/cssmenuAddOns.js'></script>
  <script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>
  <script src='magiczoomplus/magiczoomplus/magiczoomplus.js' type='text/javascript'></script>
</head>";
if (!empty($body)) {
	echo "
<body ".$body.">
<div>";
} else {
	echo "
<body >
<div>";
}
if (!isset($_SESSION['userid'])) {
	echo "
<h1>$title</h1>";
} else {
	echo "
<h3>".GetLangVar('title')."</h3>";
}
Menu($title);
echo "
<!--////////////////-->
<!--FIM DO CABECALHO-->
<!--////////////////-->
</div>
<div id='container'>
<br>";
}

function HTMLtrailers()
{
echo "
<br/>
</div>
<div>
<!--////////////////-->
<!--INICIO DO RODAPÉ-->
<!--////////////////-->
";
if ($_SESSION['userid']>0) {
echo "
<h2><i>".GetLangVar('nameautenticado')." ".$_SESSION['userfirstname']." ".$_SESSION['userlastname']." (".$_SESSION['sessiondate'].")</i></h2>";
}  else {
echo "
<h2><i>".GetLangVar('namenaoautenticado')."</i></h2>";
}
echo "
<table align='center'>
   <tr>
     <td align='center'>
      <a href='http://www.inpa.gov.br'>
        <img style='border: 0px; height: 80px;'  src='icons/inpa_cpbo.png' alt='Inpa'/>
      </a>
     </td>
   </tr>
 </table>
<iframe name='gToday:normal:agenda.js' id='gToday:normal:agenda.js' src='calendar/ipopeng.htm' scrolling='no' frameborder='0' style='visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;'></iframe>
<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->
<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>
<script type='text/javascript' src='javascript/myjavascripts.js'></script>
</div>
</body>
</html>";
mysql_close();
}


function PopupHeader($title,$body) {
include "functions/databaseSettings.php";
//require_once htmlspecialchars($relativepathtoroot.$databaseconnection, ENT_QUOTES);
DEFINE('_ISO','charset=UTF-8');
echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">
<head>
  <meta name='title' content='".$metatitle."' >
  <meta http-equiv='Content-Type' content=\"text/html; charset='UTF-8'\">
  <meta name='google-site-verification' content='sBY5TPzoJ09tOWC6GGacC8LouyYCSS8ObhOybnFn84k' >
  <meta name='url' content='".$metaurl."' >
  <meta name='robots' content='all' />
  <meta name='language' content='pt-br' />
  <meta name='description' content='".$metadesc."'>
  <meta name='keywords' content='".$metakeyw."' >
  <meta name='autor' content='Alberto Vicentini - INPA' >
  <meta name='company' content='".$metacompany."' >  
  <meta http-equiv=\"imagetoolbar\" content=\"no\" />
  <title>".$title."</title>
  <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/geral.css\" />
  <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/Stickman.MultiUpload.css\" />
  <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/autosuggest.css\" />
  <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"magiczoomplus/magiczoomplus/magiczoomplus.css\" />
  <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"javascript/fileuploader.css\">
  ";

if ($body=='DHTML') {
echo "
  <link rel='stylesheet' type='text/css' href=\"dhtmlxconnector/dhtmlxTabbar/dhtmlxtabbar.css\">
  <link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.css' >
  <link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn_bricks.css' >
  <link rel='stylesheet' type='text/css' href='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.css' >
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxcommon.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxTabbar/dhtmlxtabbar.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxTabbar/dhtmlxcontainer.js'></script>  
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgridcell.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_pgn.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxConnector_php/codebase/connector.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/dhtmlxgrid_export.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_ssc.js'></script>
  <script type='text/javascript' src='dhtmlxconnector/dhtmlxGrid/codebase/ext/dhtmlxgrid_mcol.js'></script>";
  $body ='';
} 
echo 
"
  <script type=\"text/javascript\" src=\"javascript/ajax_framework.js\"></script>
  <script type=\"text/javascript\" src=\"javascript/sorttable/common.js\"></script>
  <script type=\"text/javascript\" src=\"javascript/sorttable/css.js\"></script>
  <script type=\"text/javascript\" src=\"javascript/sorttable/standardista-table-sorting.js\"></script>
  <script type=\"text/javascript\" src=\"javascript/mootools.js\"></script>
  <script type='text/javascript' src='javascript/teste.js'></script>
  <script type=\"text/javascript\" src=\"javascript/Stickman.MultiUpload.js\"></script>
  <script type=\"text/javascript\" src=\"magiczoomplus/magiczoomplus/magiczoomplus.js\"></script>
</head>
<body $body>";
}

function PopupTrailers() {
echo "
  <br>
  <script type=\"text/javascript\" src=\"javascript/myjavascripts.js\"></script>
  <iframe name=\"gToday:normal:agenda.js\" id=\"gToday:normal:agenda.js\" src=\"calendar/ipopeng.htm\" scrolling=\"no\" frameborder=\"0\" style=\"visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;\">
  </iframe>
</body>
</html>";
}

function ImgHeader($title,$body) {
include "functions/databaseSettings.php";
//require_once htmlspecialchars($relativepathtoroot.$databaseconnection, ENT_QUOTES);
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
	\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">
<head>
  <meta name='title' content='".$metatitle."' >
  <meta http-equiv='Content-Type' content=\"text/html; charset='UTF-8'\">";
if (!empty($metagooglesite)) {  
echo "
  <meta name='google-site-verification' content='".$metagooglesite."' >";
}
echo "  
  <meta name='url' content='".$metaurl."' >
  <meta name='robots' content='all' />
  <meta name='language' content='pt-br' />
  <meta name='description' content='".$metadesc."'>
  <meta name='keywords' content='".$metakeyw."' >
  <meta name='autor' content='Alberto Vicentini - INPA' >
  <meta name='company' content='".$metacompany."' >  
<meta http-equiv=\"imagetoolbar\" content=\"no\" />
<link rel='stylesheet' href='magiczoomplus/magiczoomplus/magiczoomplus.css' type='text/css' media='screen' />
<script src='magiczoomplus/magiczoomplus/magiczoomplus.js' type='text/javascript'></script>
<script type='text/javascript' src='javascript/sorttable/common.js'></script>
<script type='text/javascript' src='javascript/sorttable/css.js'></script>
<script type='text/javascript' src='javascript/sorttable/standardista-table-sorting.js'></script>
<title>$title</title>
<link href='css/geral.css' rel='stylesheet' type='text/css' />
</head>";
	if (!empty($body)) {
		echo "<body ".$body.">";
		} else {
		echo "<body bgcolor='#f0f0f0'>";
	}
}

function HTMLheadersMap($filtername) 
	{
include "functions/databaseSettings.php";
require_once $relativepathtoroot.$databaseconnection;
DEFINE('_ISO','charset=UTF-8');
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
	\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">
<head>
  <meta name='title' content='".$metatitle."' />
  <meta http-equiv='Content-Type' content=\"text/html; charset='UTF-8'\" />";
if (!empty($metagooglesite)) {  
echo "
  <meta name='google-site-verification' content='".$metagooglesite."' />";
}
echo "  
  <meta name='url' content='".$metaurl."' />
  <meta name='robots' content='all' />
  <meta name='language' content='pt-br' />
  <meta name='description' content='".$metadesc."' />
  <meta name='keywords' content='".$metakeyw."' />
  <meta name='autor' content='Alberto Vicentini - INPA' />
  <meta name='company' content='".$metacompany."' /> 
<title>Mapeando Amostras do Banco</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/mapwindow.css\"/>
<link href='css/geral.css' rel='stylesheet' type='text/css' />
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/cssmenu.css\" />
<script type=\"text/javascript\" src=\"css/cssmenuCore.js\"></script>
<script type=\"text/javascript\" src=\"css/cssmenuAddOns.js\"></script>
<script type=\"text/javascript\" src=\"css/cssmenuAddOnsItemBullet.js\"></script>
<script type=\"text/javascript\" src=\"http://www.google.com/jsapi\"></script>
<script type=\"text/javascript\" src=\"javascript/markerclusterer.js\"></script>
<script type=\"text/javascript\" src=\"javascript/speed_test.js\"></script>
<script type=\"text/javascript\" src=\"".$filtername."\"></script>
<script type=\"text/javascript\">
      google.load('maps', '3', {
      	other_params: 'sensor=false'
      });
      
      google.setOnLoadCallback(speedTest.init);

    </script>
<script type=\"text/javascript\" src=\"javascript/mootools.js\"></script>
</head> <body>";
if (!isset($_SESSION['userid'])) {
echo "<h1>$title</h1>";
} else {
	echo "<h3>".GetLangVar('title')."</h3>";
}
	Menu($title);
	echo "<br/><div id='container'>";

}


function HTMLtrailersMaps()
{
echo "</div>
<script type=\"text/javascript\" src=\"javascript/myjavascripts.js\"></script>
</body>
</html>";
}


function HTMLtrailersSimpler()
{
echo "</div>
<script type=\"text/javascript\" src=\"javascript/myjavascripts.js\"></script>
  </body>
</html>";
}


?>