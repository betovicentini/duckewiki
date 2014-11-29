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
$curdate = $_SESSION['sessiondate'];

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


if ($monografiaid>0) {
$tbname = 'MonografiaEspecs';

//CRIA TABELA PARA RELACAO MONOGRAFIA-ESPECIMENES SE AINDA NAO EXISTIR
$qc = "CREATE TABLE IF NOT EXISTS `MonografiaEspecs` (
  `MonografiaID` int(10) NOT NULL,
  `EspecimenID` int(10) NOT NULL,
  `Incluido`  boolean NOT NULL DEFAULT FALSE,
  `AddedBy` int(10) NOT NULL,
  `AddedDate` date NOT NULL,
  KEY `EspecimenID` (`EspecimenID`),
  KEY `MonografiaID` (`MonografiaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
@mysql_query($qc);

//CRIA A TABELA TEMPORARIA DE FILTRAGEM E MARCACAO DOS REGISTROS PARA INCLUIR NA MONOGRAFIA
$temptb = 'temp_monografia_'.$monografiaid.'_'.$uuid;
$qc = "DROP TABLE ".$temptb;
@mysql_query($qc);
$qc = "CREATE TABLE ".$temptb." SELECT getincluido(EspecimenID, ".$monografiaid.") as Incluido, orgtb.* FROM `checklist_speclist` AS orgtb";
@mysql_query($qc);
$qq = "ALTER TABLE ".$temptb." ADD PRIMARY KEY(EspecimenID)";
@mysql_query($qq,$conn);
$sql = "CREATE INDEX COLETOR ON ".$temptb."  (COLETOR)";
@mysql_query($sql,$conn);
$sql = "CREATE INDEX NUMERO ON ".$temptb."  (NUMERO)";
@mysql_query($sql,$conn);
$sql = "CREATE INDEX ".$herbariumsigla." ON ".$temptb."  (".$herbariumsigla.")";
@mysql_query($sql,$conn);
$sql = "CREATE INDEX NOME ON ".$temptb."  (NOME)";
@mysql_query($sql,$conn);
$sql = "CREATE INDEX PAIS ON ".$temptb."  (PAIS)";
@mysql_query($sql,$conn);
$sql = "CREATE INDEX ESTADO ON ".$temptb."  (ESTADO)";
@mysql_query($sql,$conn);
$sql = "CREATE INDEX MUNICIPIO ON ".$temptb."  (MUNICIPIO)";
@mysql_query($sql,$conn);
$sql = "CREATE INDEX LOCAL ON ".$temptb."  (LOCAL)";
@mysql_query($sql,$conn);
$sql = "CREATE INDEX LOCALSIMPLES ON ".$temptb."  (LOCALSIMPLES)";
@mysql_query($sql,$conn);

//COMECA A PREPARACAO DO DHTMLX GRID
$headd = array(
"Incluido",
"EspecimenID",
"DetID",
"EDIT",
"COLETOR",
"NUMERO",
"PlantaTag",
"PlantaID",
"DATA", 
$herbariumsigla, 
"FAMILIA", 
"NOME", 
"PAIS", 
"ESTADO", 
"MUNICIPIO", 
"LOCAL", 
"LOCALSIMPLES",
"LONGITUDE",
"LATITUDE",
"ALTITUDE",
"DUPS",
"MAP",
"OBS",
"HABT",
"IMG",
"PRJ",
"PROJETOstr"
);

$colw = array(
"Incluido" => 80,
"EspecimenID" => 0,
"DetID" => 0,
"EDIT" => 0,
"COLETOR" => 80,
"NUMERO" => 70,
"PlantaTag" => 0,
"PlantaID" => 0,
"DATA" => 70,
$herbariumsigla => 50,
"FAMILIA" => 80,
"NOME" => 200,
"PAIS" => 40,
"ESTADO" => 60,
"MUNICIPIO" => 50,
"LOCAL" => 90,
"LOCALSIMPLES" => 90,
"LONGITUDE" => 0,
"LATITUDE" => 0,
"ALTITUDE" => 0,
"DUPS" => 0,
"MAP" => 45,
"OBS" => 50,
"HABT" => 0,
"IMG" => 40,
"PRJ" => 40,
"PROJETOstr" => 0
);

$colvalid = array();
$coltipos = array();
$$colalign = array();
//DEFINE  AS COLUNAS 
foreach($headd as $kk => $vv) {
	//NAO ME LEMBRO O QUE FAZ
	$colvalid[$kk] = '';
	//DEFINE COLUNAS COMO LEITURA APENAS
	$coltipos[$kk] = 'ro';
	//#DEFINE O ALINHAMENTO 
	$colalign[$kk]  = 'left';
}
//MUDA A PRIMEIRA COLUNA, Incluido PARA EDITAVEL CHECKBOX
$coltipos[0] = 'ch';
$colalign[0]  = 'center';

//DEFINE MAIS ALGUNS ATRIBUTOS PARA O GRID
//COPIA O CABECALHO EM VISIVEL, FILTROS
$listvisible = $headd;
$filt = $headd;
$filt2 = $headd;
//DEFINE FILTRO, IMAGEM E FILTROS NUMERICOS
$nofilter = array("OBS", "IMG", "PRJ", "EDIT", "HABT","MAP");
$imgfields = array("OBS", "IMG", "PRJ", "EDIT", "HABT","MAP");
$numericfilter = array("DAPmm","ALTURA");
//DEFINE COLUNAS PARA ESCONDER
if(!isset($uuid) || (trim($uuid)=='') || $acclevel=='visitor') {
	$hidefields = array("EspecimenID","PROJETOstr", "LONGITUDE", "LATITUDE", "ALTITUDE", "DUPS","EDIT","DetID","GazetteerID","GPSPointID","PlantaTag","PlantaID");
	}
	else {
	$hidefields = array("EspecimenID", "PROJETOstr", "LONGITUDE", "LATITUDE", "ALTITUDE", "DUPS","DetID","GazetteerID","GPSPointID","PlantaTag","PlantaID");
}
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
	//$filt[0] = '#master_checkbox';
	$filt2[0] ='';
	$filt3 = $colvalid;
	$filt3[0] = '#master_checkbox';
	$hdd = implode(",",$headd);
	$ffilt = implode(",",$filt);
	$ffilt2 = implode(",",$filt2);
	$ffilt3 = implode(",",$filt3);
	$collist = implode(",",$collist);
	$colw = implode(",",$colw);
	$coltipos = implode(",",$coltipos);
	$listvisible = implode(",",$listvisible);
	$colidx = implode(",",$colidx);
	$colalign = implode(",",$colalign);
	$hidemenu = implode(",",$hidemenu);
	
	
	$fnn = $temptb."_".$uuid.".php";
	$fnn2 = $temptb."_".$uuid."_process.php";

	$qq = "SELECT count(*) as nrecs FROM `".$temptb."`";
	$rr = @mysql_query($qq,$conn);
	$row = @mysql_fetch_assoc($rr);
	$nrecs = $row['nrecs'];
	
	$url = $_SERVER['HTTP_REFERER'];
	$uu = explode("/",$url);
	$nu = count($uu)-1;
	unset($uu[$nu]);
	$url = implode("/",$uu);
	
	//CRIAR O PHP QUE CONTEM APENAS O CODIGO PARA MARCAR OU DESMARCAR REGISTROS DE UM FILTRO GRANDE
$fh2 = fopen("temp/".$fnn2, 'w');
$stringData = "<?php
session_start();
require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");
include \"../functions/MyPhpFunctions.php\";
\$especimenid = \$_GET['especimenid'];
\$varincluido = \$_GET['varincluido'];
\$rr =  mysql_query(\"SELECT * FROM `".$tbname."` WHERE EspecimenID=\$especimenid  AND MonografiaID=".$monografiaid." \");
\$nrr = mysql_numrows(\$rr);
if (\$nrr>0) {
  \$rrw = mysql_fetch_assoc(\$rr);
  \$oldIncluido = \$rrw['Incluido'];
  if (\$oldIncluido!=\$varincluido) {
                     CreateorUpdateTableofChanges(\$especimenid,'EspecimenID','".$tbname."',\$res);
                     mysql_query(\"UPDATE  `".$tbname."` SET `Incluido`=\$varincluido  WHERE `EspecimenID`=\$especimenid  AND `MonografiaID`=".$monografiaid." \");
              }
} else {
  if (\$varincluido==1) {
            mysql_query(\"INSERT INTO  `".$tbname."` (`Incluido`,`EspecimenID`,`MonografiaID`,`AddedBy`,`AddedDate`) VALUES ('1' ,\".\$especimenid.\",'".$monografiaid."', '".$uuid."', '".$curdate."')\");
  }
}
mysql_query(\"UPDATE  `".$temptb."` SET `Incluido`=\$varincluido  WHERE `EspecimenID`=\$especimenid\");
echo \"done\";
?>";
fwrite($fh2, $stringData);
fclose($fh2);

	
	$fh = fopen("temp/".$fnn, 'w');
	$stringData = "<?php
session_start();
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");
include \"../functions/MyPhpFunctions.php\";
\$uuid = \$_SESSION['userid'];
function myUpdate(\$action,\$res){
         \$varincluido = \$action->get_value('Incluido');
         \$especimenid = \$action->get_id();
         \$rr =  mysql_query(\"SELECT * FROM `".$tbname."` WHERE EspecimenID=\$especimenid  AND MonografiaID=".$monografiaid." \");
         \$nrr = mysql_numrows(\$rr);
         if (\$nrr>0) {
              \$rrw = mysql_fetch_assoc(\$rr);
              \$oldIncluido = \$rrw['Incluido'];
              if (\$oldIncluido!=\$varincluido) {
                     CreateorUpdateTableofChanges(\$especimenid,'EspecimenID','".$tbname."',\$res);
                     mysql_query(\"UPDATE  `".$tbname."` SET `Incluido`=\$varincluido  WHERE `EspecimenID`=\$especimenid  AND `MonografiaID`=".$monografiaid." \");
              }
        } else {
          if (\$varincluido==1) {
            mysql_query(\"INSERT INTO  `".$tbname."` (`Incluido`,`EspecimenID`,`MonografiaID`,`AddedBy`,`AddedDate`) VALUES ('1' ,\".\$especimenid.\",'".$monografiaid."', '".$uuid."', '".$curdate."')\");
           }
        }
        mysql_query(\"UPDATE  `".$temptb."` SET `Incluido`=\$varincluido  WHERE `EspecimenID`=\$especimenid\");
        \$action->success();
}
function custom_format_spec(\$data){
    \$pltag = \$data->get_value(\"COLETOR\").\" \".\$data->get_value(\"NUMERO\");
    \$thedetid = \$data->get_value(\"DetID\");
    \$thespecimenid = \$data->get_value(\"EspecimenID\");
    if (\$data->get_value(\"IMG\")==\"camera.png\") {
      \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"IMG\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/showimage_taxa.php?ispopup=1&especimenid=\".\$thespecimenid.\"',700,400,'Ver imagens');\\\" onmouseover=\\\"Tip('Ver imagens da amostra # \".\$pltag.\"');\\\" >\";
      \$data->set_value(\"IMG\",\$imagen);
    } else {
      \$imagen= '';
      \$data->set_value(\"IMG\",\$imagen);
    }
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

    \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"EDIT\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/especimenes_dataform.php?ispopup=1&especimenid=\".\$data->get_value(\"EspecimenID\").\"',1000,400,'Editar registro');\\\" onmouseover=\\\"Tip('Editar o especímene # \".\$pltag.\"');\\\" >\";
    \$imagen2=\"<img style='cursor:pointer;' src='icons/rednameicon.png' height='20' onclick=\\\"javascript:small_window('".$url."/taxonomia-popup.php?updatechecklist=1&ispopup=1&saveit=true&detid=\".\$data->get_value(\"DetID\").\"&especimenid=\".\$data->get_value(\"EspecimenID\").\"',800,400,'Editar Identificação');\\\" onmouseover=\\\"Tip('Editar Identificação da amostra # \".\$pltag.\"');\\\" >\";
    \$imgg3 =\"<img style='cursor:pointer;' src='icons/nota-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/traits_coletorvariacao.php?apagavarsess=1&saveit=1&formid=".$formnotes."&especimenid=\".\$data->get_value(\"EspecimenID\").\"',800,800,'Editando notas');\\\"  onmouseover=\\\"Tip('Edita notas da amostra # \".\$pltag.\"');\\\" >\";
    \$imagen = \$imagen.\"&nbsp;\".\$imagen2.\"&nbsp;\".\$imgg3;
    \$data->set_value(\"EDIT\",\$imagen);

    \$llat = ABS($data->get_value(\"LATITUDE\"));
    \$llong = ABS(\$data->get_value(\"LONGITUDE\"));
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
  \$val = str_replace('!','',\$val);  
  return \$val+0;
}
function custom_filter(\$filter_by){
   \$index = \$filter_by->index('DAPmm');
   \$index5 = \$filter_by->index('NUMERO');
   \$index3 = \$filter_by->index('ALTURA');
   \$index4 = \$filter_by->index('PlantaTag');
   \$index2 = \$filter_by->index('Incluido');
   \$idxss = array(\$index,\$index3,\$index4,\$index2,\$index5);
   foreach (\$idxss as \$idx) {
		if (\$idx!==false) {
			\$vv =  \$filter_by->rules[\$idx][\"value\"];
			if (substr(\$vv,0,2)=='!=') {
				\$filter_by->rules[\$idx][\"operation\"]=\"!=\";
			} else {

			if (substr(\$vv,0,1)=='>') {
				\$filter_by->rules[\$idx][\"operation\"]=\">\";
			}
			if (substr(\$vv,0,1)=='<') {
				\$filter_by->rules[\$idx][\"operation\"]=\"<\";
			}
			if (substr(\$vv,0,1)=='=') {
				\$filter_by->rules[\$idx][\"operation\"]=\"=\";
			}
			}
			\$filter_by->rules[\$idx][\"value\"] = removeoperators(\$filter_by->rules[\$idx][\"value\"]);
		}
	}
}
\$grid = new GridConnector(\$res);
\$grid ->event->attach(\"beforeRender\",\"custom_format_spec\");
\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
\$grid ->dynamic_loading(".($nrecs+1).");
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid ->render_sql(\"SELECT * FROM `".$temptb."`\",\"EspecimenID\",\"".$hdd."\");
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
'filtros3' => $ffilt3,
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
'monografiaid' => $monografiaid,
'temptb' => $temptb
);
$_SESSION['arrtopass'] = serialize($arrofpass);
echo "
  <form name='myform' action='monografia-amostras-grid.php' method='post'>";
  foreach ($arrofpass as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";
} 
else {
echo "<br />MonografiaID não foi definido<br />";
}


?>