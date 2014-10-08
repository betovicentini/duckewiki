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


//TABELA ESPECIALISTAS
$tbname = 'Especialistas';

$qqr = "SELECT 1 FROM ".$tbname." WHERE 1";
$rr = @mysql_query($qqr,$conn);
if (!$rr) {
echo "
  <form name='myform' action='especialista-prep.php' method='post'>";
  foreach ($gget as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
  foreach ($ppost as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";
}
else {
$headd = array(
'EspecialistaID',
'Especialista',
'EspecialistaTXT',
'FamiliaID',
'Familias',
'Generos',
'Herbarium',
'Email');

$colw = array(
'EspecialistaID' => 0,
'Especialista' => 300,
'EspecialistaTXT' => 0,
'FamiliaID' => 300,
'Familias' => 0,
'Generos' => 100,
'Herbarium' => 100,
'Email' => 300);

$colvalid = array(
'EspecialistaID' => '',
'Especialista' => '',
'EspecialistaTXT' => '',
'FamiliaID' => '',
'Familias' => '',
'Generos' => '',
'Herbarium' => '',
'Email' => '');

$coltipos = array(
'EspecialistaID' =>  'ro',
'Especialista' =>  'co',
'EspecialistaTXT' => 'ro',
'FamiliaID' =>  'co',
'Familias' =>  'ro',
'Generos' => 'ro',
'Herbarium' =>  'ed',
'Email' => 'ed');

$colalign = array(
'EspecialistaID' =>  'center',
'Especialista' =>  'left',
'EspecialistaTXT' => 'left',
'FamiliaID' =>  'left',
'Familias' =>  'left',
'Generos' => 'center',
'Herbarium' =>  'center',
'Email' => 'left');

$noupdatefor = array(
'EspecialistaID' ,
'EspecialistaTXT',
'FamiliaID',
'Familias' ,
'Generos' );

$listvisible = $headd;
$filt = $headd;
$filt2 = $headd;

//$nofilter = array("EspecialistaID", "FamiliaID");
$nofilter = array();
$imgfields = array();
$numericfilter = array("EspecialistaID");
$hidefields = array("EspecialistaID", "Familias", "AddedBy", "AddedDate",'EspecialistaTXT');

$i=1;
$ncl = count($headd)-count($imgfields)-count($hidefields);
$nimg = count($imgfields);
$nimg = $nimg*50;
$cl = floor((900-$nimg)/$ncl);
$colidx = array();
$collist = array();
$hidemenu = array();
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
			$colidx[] = ($i-1);
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
	
	//$kk = array_search('Especialista',$headd);
	//$filt[$kk] = '#connector_select_filter';
	
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
	$fnn = $tbname."_".$processoid."_".$uuid.".php";
	
	$tbb = $tbname."_".$processoid."_".$uuid;
	
	if ($processoid>0) {
	$qf = "DROP TABLE $tbb";
	@mysql_query($qf,$conn);
	$qff = "CREATE TABLE $tbb SELECT DISTINCT FAMILIA FROM processo_".$processoid;
	@mysql_query($qff,$conn);
	}
	
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
\$processoid = ".($processoid+0).";
function custom_format_spec(\$data){
";
$idxgeneros = array_search('Generos',$headd);
$stringData .= "
    \$famid = \$data->get_value(\"FamiliaID\");
    \$espid = \$data->get_value(\"Especialista\");
    \$spid = \$data->get_value(\"EspecialistaID\");
    \$rr =  mysql_query(\"SELECT Genero FROM Tax_Generos WHERE FamiliaID='\".\$famid.\"'  AND EspecialistaID='\".\$spid.\"'\");
    \$nr = mysql_numrows(\$rr);
    if (\$nr>0) {
       \$gens = array();
       while (\$rw = mysql_fetch_assoc(\$rr)) {
           \$gens[] = \$rw['Genero'];
       }
       \$gens = implode(\";\", \$gens);
    } else {
       \$gens = \"\";
    }
    if (\$famid>0 && \$espid>0) {
    \$imagen=   \$gens.\"<span style='padding: 5px;' ><img style='cursor:pointer;' src='icons/genero.png' height='14' onclick=\\\"javascript:small_window('".$url."/especialista-generos.php?rowid=\".\$data->get_index().\"&especialistaid=\".\$spid.\"&famid=\".\$famid.\"&clidx=".$idxgeneros."',700,400,'Indicar gêneros');\\\" onmouseover=\\\"Tip('Indicar gêneros do especialista, quando necessário');\\\" ></span>\";
      \$data->set_value(\"Generos\",\$imagen);
    }  else {
     if (\$famid>0) {
      \$data->set_value(\"Generos\",'Defina especialista');
      } else {
      \$data->set_value(\"Generos\",'Defina familia e especialista');
      }
    }
}
function myUpdate(\$action){
        \$val = \$action->get_value('Especialista');
        \$rr =  mysql_query(\"SELECT Abreviacao,Email FROM Pessoas WHERE PessoaID='{\$action->get_value('Especialista')}'\");
        \$rw = mysql_fetch_assoc(\$rr);
        if (!empty(\$val) && \$val>0) {
          mysql_query(\"UPDATE `".$tbname."` SET Especialista='{\$action->get_value('Especialista')}'  WHERE EspecialistaID='{\$action->get_id()}'\");
          mysql_query(\"UPDATE `".$tbname."` SET EspecialistaTXT='\".\$rw['Abreviacao'].\"'  WHERE EspecialistaID='{\$action->get_id()}'\");
          mysql_query(\"UPDATE `".$tbname."` SET Email='\".\$rw['Email'].\"'  WHERE EspecialistaID='{\$action->get_id()}'\");
        }
        \$herb = \$action->get_value('Herbarium');
        if (!empty(\$herb)) {
          mysql_query(\"UPDATE `".$tbname."` SET Herbarium='{\$action->get_value('Herbarium')}'  WHERE EspecialistaID='{\$action->get_id()}'\");
        }
        \$email = trim(\$action->get_value('Email'));
        if (!empty(\$email) && \$email!=\$rw['Email']) {
          mysql_query(\"UPDATE `".$tbname."` SET Email='\".\$email.\"'  WHERE EspecialistaID='{\$action->get_id()}'\");
          mysql_query(\"UPDATE `Pessoas` SET Email='\".\$email.\"'   WHERE PessoaID='{\$action->get_value('Especialista')}' \");
        }
        \$ff = trim(\$action->get_value('FamiliaID'));
        if (!empty(\$ff)) {
          mysql_query(\"UPDATE `".$tbname."` SET FamiliaID='{\$action->get_value('FamiliaID')}'  WHERE EspecialistaID='{\$action->get_id()}'\");
          \$rr =  mysql_query(\"SELECT Familias FROM `".$tbname."` WHERE FamiliaID='{\$action->get_value('FamiliaID')}' AND Familias IS NOT NULL LIMIT 0,1\");
          \$rw = mysql_fetch_assoc(\$rr);
           mysql_query(\"UPDATE `".$tbname."` SET Familias='\".\$rw['Familias'].\"'  WHERE EspecialistaID='{\$action->get_id()}'\");
        }
        \$action->success();
}
function custom_filter(\$filter_by){
   \$index = \$filter_by->index('FamiliaID');
   \$index2 = \$filter_by->index('Familias');
   \$seaval =  \$filter_by->rules[\$index][\"value\"];
   if (!empty(\$seaval)) {
   \$filter_by->rules[\$index2][\"value\"]  =  \$filter_by->rules[\$index][\"value\"];
   \$filter_by->rules[\$index][\"value\"] = NULL;
   }
   \$index3 = \$filter_by->index('Especialista');
   \$index4 = \$filter_by->index('EspecialistaTXT');
   \$seaval2 =  \$filter_by->rules[\$index3][\"value\"];
   if (!empty(\$seaval2)) {
      \$filter_by->rules[\$index4][\"value\"]  =  \$filter_by->rules[\$index3][\"value\"];
      \$filter_by->rules[\$index3][\"value\"] = NULL;
   }
}
\$options = new OptionsConnector(\$res, \"MySQL\");
\$options->render_sql(\"SELECT PessoaID as value, Abreviacao as label FROM Pessoas ORDER BY Abreviacao ASC\",\"PessoaID\",\"PessoaID(value), Abreviacao(label)\");
\$options2 = new OptionsConnector(\$res, \"MySQL\");
\$options2->render_sql(\"SELECT FamiliaID as value, Familias as label FROM Especialistas WHERE FamiliaID>0 ORDER BY Familias ASC\",\"FamiliaID\",\"FamiliaID(value), Familias(label)\");
\$grid = new GridConnector(\$res);
\$grid ->event->attach(\"beforeRender\",\"custom_format_spec\");
\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
\$grid ->dynamic_loading(".$nrecs.");
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid->set_options(\"Especialista\",\$options);
\$grid->set_options(\"FamiliaID\",\$options2);
if (\$processoid>0) {
\$grid ->render_sql(\"SELECT  prf.* FROM Especialistas AS prf   JOIN `".$tbb."` as prc ON prc.FAMILIA=prf.Familias\",\"EspecialistaID\",\"".$hdd."\");
} else {
\$grid ->render_table(\"".$tbname."\",\"EspecialistaID\",\"".$hdd."\");
}
?>";
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
'colvalid'  => implode(",",$colvalid),
'processoid' => $processoid
);
$_SESSION['arrtopass'] = serialize($arrofpass);
echo "
  <form name='myform' action='especialista-grid.php' method='post'>";
  foreach ($arrofpass as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";

}

?>