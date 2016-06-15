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
$menu = FALSE;

if ($projetoid>0) {

//CRIA TABELA PARA RELACAO PROJETO-ESPECIMENES SE AINDA NAO EXISTIR
$qc = "CREATE TABLE IF NOT EXISTS `ProjetosEspecs` (
  `ProjetoID` int(10) NOT NULL,
  `EspecimenID` int(10) NOT NULL,
  `PlantaID` int(10) NOT NULL,
  `AddedBy` int(10) NOT NULL,
  `AddedDate` date NOT NULL,
  KEY `EspecimenID` (`EspecimenID`),
  KEY `PlantaID`(`PlantaID`),
  KEY `ProjetoID` (`ProjetoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
@mysql_query($qc);

//CRIA TABELA PARA RELACAO PROJETO-ESPECIMENES SE AINDA NAO EXISTIR
$qc = "ALTER TABLE `ProjetosEspecs` ADD COLUMN  `PlantaID` int(10) NOT NULL";
@mysql_query($qc);


//GENERAL TABLE
//if (empty($tbname)) { $tbname = 'checklist_speclist'; }
$tbname = 'checklist_pllist';
$newfilename = 'temp_ProjetoPlantas_'.$projetoid;
$qwhere = ''; 

//COMECA A PREPARA O ARQUIVO QUE MOSTRA O GRID
$numericfilter = array();
//$numericfilter[] = "Marcado";
$numericfilter[] = "TAG";

//SAVE RESULTS IN A FILE
//"LONGITUDE", "LATITUDE", "ALTITUDE"
$headd = array("Marcado","PlantaID","DetID","EDIT","TAGtxt","TAG","FAMILIA", "NOME", "NOME_AUTOR","DETBY","DETYY","MORFOTIPO", "PAIS", "ESTADO", "MUNICIPIO", "LOCAL","LOCALSIMPLES","LONGITUDE", "LATITUDE", "ALTITUDE");
$headexplan = array("Marcar ou desmarcar o registro", "Identificador da planta em Plantas","Identificador da determinação em Identidade","Links para edição do registro e dados associados","Código da placa da árvore  caracteres e números","Número da placa da árvore - apenas numérico","Familia", "Nome da identificação da planta sem autor","Nome da identificação da planta com autor", "Quem identificou","Ano de identificação", "Se o nome é um morfotipo spp indica no nivel de espécie e infspp no nível de infraespécie", "Pais  do local", "Estado do local", "Municipio do local", "Localidade completa","Localidade mais especifica", "Longitude em décimos de grau","Latitude em décimos de grau", "Altitude em metros");
$exportcols = array("false","true","false","false","true","true","true", "true","true","true","true","true","true", "true","true","true","true","true","true","true");

$colw = array(
"Marcado" => 70,
"PlantaID" => 0,
"DetID" => 0,
"EDIT" => 110,
"TAGtxt" => 0,
"TAG" => 50,
"FAMILIA" => 80,
"NOME" => 150,
"NOME_AUTOR" => 150,
"DETBY" => 0,
"DETYY" => 0,
"MORFOTIPO" => 100,
"PAIS" => 40,
"ESTADO" => 60,
"MUNICIPIO" => 50,
"LOCAL" => 120,
"LOCALSIMPLES" => 100,
"LONGITUDE" => 0,
"LATITUDE" => 0,
"ALTITUDE" => 0
);
$numericfilter[] = "LONGITUDE";
$numericfilter[] = "LATITUDE";
$numericfilter[] = "ALTITUDE";


if ($daptraitid>0) {
	$headd[] = 'DAPmm';
	$numericfilter[] = "DAPmm";
	$colw = array_merge((array)$colw,(array)array('DAPmm' => 60));
	$exportcols[] = "true";
	$headexplan[] = 'Máximo dos DAPs associados à planta em mm';
}
if ($alturatraitid>0) {
	$headd[] = 'ALTURA';
	$numericfilter[] = "ALTURA";
	$colw = array_merge((array)$colw,(array)array('ALTURA' => 65));
	$exportcols[] = "true";
	$headexplan[] = 'Máximo das alturas associadas à planta em metros';
}
if ($habitotraitid>0) {
	$headd[] = 'HABITO';
	$colw = array_merge((array)$colw,(array)array('HABITO' => 65));
	$exportcols[] = "true";
	$headexplan[] = 'Hábito da planta';
}
if ($habitotraitid>0) {
	$headd[] = 'STATUS';
	$colw = array_merge((array)$colw,(array)array('STATUS' => 65));
	$exportcols[] = "true";
	$headexplan[] = 'Se a planta está viva ou morta';
}
$headd = array_merge((array)$headd,(array)array("ESPECIMENES","PLOT", "DUPS","MAP","OBS","HABT","IMG","NIRSpectra","PROJETOstr"));

$headexplan = array_merge((array)$headexplan,(array)array("Número de ESPECIMENES associados à planta","GazetteerID do plot em que a planta se encontra", "DUPS","Visualizar a PLANTA num mapa","Visualizar ou baixar em PDF etiqueta para a planta incluindo todas as observações associadas à ela","Descreve o hábitat da planta","Visualiza imagens associadas à planta","Visualiza dados NIR associados à planta","Nome do projeto de Pesquisa à qual a planta está associada"));

$exportcols = array_merge((array)$exportcols,(array)array("true","true", "true","false","false","false","true","true","false","true"));

$colw = array_merge((array)$colw,(array)array("ESPECIMENES" => 40,
"PLOT" => 0,
"DUPS" => 0,
"MAP" => 45,
"OBS" => 50,
"HABT" => 40,
"IMG" => 40,
"NIRSpectra" => 70,
"PROJETOstr" => 0
//,"EXSICATA_IMG" => 0
//,
//"GazetteerID" => 0,
//"GPSPointID" => 0,
));
$listvisible = $headd;
$filt = $headd;
$filt2 = $headd;
$nofilter = array("Marcado", "OBS", "IMG", "EDIT", "HABT","MAP","LONGITUDE","LATITUDE","ALTITUDE");
$imgfields = array("OBS", "IMG", "EDIT", "HABT","MAP","ESPECIMENES","NIRSpectra");

if(!isset($uuid) || (trim($uuid)=='') || $acceslevel=='visitor' || $uuid==0) {
	$hidefields = array("PlantaID", "TAGtxt", "DetID","PROJETOstr", "LONGITUDE", "LATITUDE", "ALTITUDE", "PLOT","DUPS","EDIT","GazetteerID","GPSPointID","NOME_AUTOR","MORFOTIPO","DETBY","DETYY");
} 
else {
	$hidefields = array("PlantaID", "TAGtxt", "DetID","PROJETOstr", "DUPS","GazetteerID","GPSPointID","PLOT","NOME_AUTOR","MORFOTIPO","DETBY","DETYY","LONGITUDE", "LATITUDE", "ALTITUDE");
}
$i=0;
$ncl = count($headd)-count($imgfields)-count($hidefields);
$nimg = count($imgfields);
$nimg = $nimg*50;
$cl = floor((900-$nimg)/$ncl);
$colidx = array();
$collist = array();
$coltipos = array();
$colalign = $headd;
$hidemenu = array();
$numericfilter[]  = 'NIRSpectra'; 

foreach ($headd as $kk => $vv) {
	$qqr = "SELECT 0 as Marcado,* FROM ".$tbname." as tb PROCEDURE ANALYSE() WHERE Field_name LIKE '%".$tbname.".".$vv."%'";
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
		//$colw[$kk] = $cl;
	} else {
		$coltipos[$kk] = 'ro';
		$colalign[$kk] = "center";
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

	//CONTA O NUMERO DE REGISTROS PARA DYNAMIC LOADING DO GRID
	$qq = "SELECT count(*) as nrecs FROM ".$tbname.$qwhere;
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
	$fnn = $newfilename.".php";
	$fh = fopen("temp/".$fnn, 'w');
	$stringData = "<?php
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");
include \"../functions/MyPhpFunctions.php\";";
$stringData .= "    
\$qmarcado = (\$_GET['marcado']+0);
if (\$qmarcado==1) {
	\$qwhere = \" WHERE plantaInprojeto(tb.PlantaID, ".$projetoid.")=1 \"; 
}

function myUpdate(\$action){
    \$status = \$action->get_value('Marcado');
    \$idsp = \$action->get_id();";
$stringData .= "    
    \$ru = mysql_query(\"SELECT * FROM `ProjetosEspecs` WHERE PlantaID=\".\$idsp.\"  AND ProjetoID='".$projetoid."'\");";
$stringData .= "  
    \$nru = mysql_numrows(\$ru);
    if (\$nru!=\$status) {
       if (\$status==1) {";
$stringData .= "    
     \$qinn = \"INSERT INTO  `ProjetosEspecs` (`PlantaID`,`ProjetoID`) VALUES ('\".\$idsp.\"','".$projetoid."')\";";
$stringData .= "  
     }  else {";
$stringData .= "    
     \$qinn = \"DELETE FROM  `ProjetosEspecs`  WHERE PlantaID='\".\$idsp.\"' AND ProjetoID='".$projetoid."'\";";
$stringData .= "  
     }  
     \$ru = mysql_query(\$qinn);
   }          
   \$action->success();
}
function custom_format_pl(\$data){
\$pltag = \$data->get_value(\"TAGtxt\");
\$data->set_value(\"TAG\",\$pltag);
  \$thedetid = \$data->get_value(\"DetID\");
  \$theplantaidid = \$data->get_value(\"PlantaID\");
  \$recid = \$data->get_id();";
$stringData .= "    
    \$ru = mysql_query(\"SELECT * FROM `ProjetosEspecs` WHERE PlantaID=\".\$recid.\"  AND ProjetoID='".$projetoid."'\");";
$stringData .= "    
    \$nruw = mysql_numrows(\$ru);
    \$data->set_value(\"Marcado\", \$nruw);
if (\$data->get_value(\"IMG\")==\"camera.png\") {
    \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"IMG\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/showimage_taxa.php?plantaid=\".\$data->get_value(\"PlantaID\").\"',600,400,'Ver imagens');\\\" onmouseover=\\\"Tip('Ver imagens da planta # \".\$pltag.\"');\\\"  title=''>\";
    \$data->set_value(\"IMG\",\$imagen);
    } else {
      \$imagen= '';
      \$data->set_value(\"IMG\",\$imagen);
    }
  if ((\$data->get_value(\"ESPECIMENES\"))>0) {
       \$imagen= \"<img style='cursor:pointer;' src='icons/specimen-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/checklist_specimens_save.php?plantaid=\".\$data->get_value(\"PlantaID\").\"',950,500,'Especimenes');\\\" onmouseover=\\\"Tip('Visualizar amostras da planta # \".\$pltag.\"');\\\" title=''><sup>  \".\$data->get_value(\"ESPECIMENES\").\"</sup>\";
   } else {
        \$imagen = \" \";
   }
   \$data->set_value(\"ESPECIMENES\",\$imagen);
  if (\$data->get_value(\"HABT\")==\"environment_icon.png\") {
    \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"HABT\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/showhabitat.php?plantaid=\".\$data->get_value(\"PlantaID\").\"',500,400,'Ver imagens');\\\" onmouseover=\\\"Tip('Sobre o habitat da planta # \".\$pltag.\"');\\\" title=''>\";
    \$data->set_value(\"HABT\",\$imagen);
    } else {
    //\$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"HABT\").\"' height='20' onclick=\\\"javascript:alert('Não há informação sobre habitat para esta planta!');\\\" onmouseover=\\\"Tip('Não há informação sobre habitat para esta planta');\\\" title=''>\";
    //\$data->set_value(\"HABT\",\$imagen);
    }
    \$imagen=\"<img style='cursor:pointer;' src='icons/edit-notes.png' height='20' onclick=\\\"javascript:small_window('".$url."/showplanta.php?plantaid=\".\$data->get_value(\"PlantaID\").\"',400,400,'Notas');\\\" onmouseover=\\\"Tip('Notas da planta # \".\$pltag.\"');\\\" title=''>\";
    \$imgg =\"<img style='cursor:pointer;' src='icons/label-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/singlelabel-exec.php?etitype=PlantasIDS&specimenid=\".\$data->get_value(\"PlantaID\").\"',300,100,'Etiqueta em PDF');\\\" onmouseover=\\\"Tip('Etiquetas em PDF da planta # \".\$pltag.\"');\\\" title='' >\";
    \$imagen = \$imagen.\"&nbsp;\".\$imgg;
    \$data->set_value(\"OBS\",\$imagen);";
if ($uuid>0 && $acceslevel!='visitor') {
$stringData .= "    
   \$imagen= \"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"EDIT\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/plantas_dataform.php?ispopup=1&submeteu=editando&plantaid=\".\$data->get_value(\"PlantaID\").\"',1000,400,'Editando o registro');\\\" onmouseover=\\\"Tip('Editar o registro da planta # \".\$pltag.\"');\\\" title=''>\";
   \$imgg2 = \"<img style='cursor:pointer;' src='icons/monitoramento.png' height='20' onclick=\\\"javascript:small_window('".$url."/traits_coletormonitoramento.php?ispopup=1&plantatag=\".\$data->get_value(\"TAG\").\"&plantaid=\".\$data->get_value(\"PlantaID\").\"&submeteu=1',1000,400,'Editando o registro');\\\" onmouseover=\\\"Tip('Ver/Editar variáveis de monitoramento da planta # \".\$pltag.\"');\\\" title=''>\";
   
   \$pltid = \$data->get_value(\"PlantaID\");
   \$imgg3 = \"<img style='cursor:pointer;' src='icons/specimen-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/especimenes_dataform.php?ispopup=1&submeteu=nova&plantaid=\".\$pltid.\"',1000,400,'Nova amostra de planta');\\\" onmouseover=\\\"Tip('Novo especímene da planta # \".\$pltag.\"');\\\" title=''>\";
   \$imgg4 = \"<img style='cursor:pointer;' src='icons/rednameicon.png' height='17' onclick=\\\"javascript:small_window('".$url."/taxonomia-popup.php?updatechecklist=1&ispopup=1&saveit=true&detid=\".\$data->get_value(\"DetID\").\"&plantaid=\".\$data->get_value(\"PlantaID\").\"',800,400,'Editar Identificação');\\\" onmouseover=\\\"Tip('Editar Identificação da planta # \".\$pltag.\"');\\\" title='' >\";
    \$ruu = mysql_query(\"SELECT TraitID FROM Traits WHERE ParentID='".$traitsilica."' AND LOWER(TraitName) LIKE '%silica%'\");
     \$ruuw = mysql_fetch_assoc(\$ruu);
     \$silicavar = \$ruuw['TraitID'];
     \$rnn = mysql_query(\"SELECT * FROM Traits_variation WHERE TraitID='".$traitsilica."' AND PlantaID=\".\$data->get_value(\"PlantaID\").\" AND (TraitVariation LIKE '\".\$silicavar.\"%'  OR TraitVariation LIKE 
     '%;\".\$silicavar.\"%' )\");
     \$nrnn = mysql_numrows(\$rnn);
     if (\$nrnn==0) {
     \$imgg5 =\"<img style='cursor:pointer;' src='icons/dna.png' height='20' onclick=\\\"javascript:amostrasilica(0,\".\$data->get_value(\"PlantaID\").\");\\\"  onmouseover=\\\"Tip('Marca que tem amostra em silica');\\\" >\";
    } else {
     \$imgg5 =\"<img style='cursor:pointer;' src='icons/dna_ok.png' height='20' onclick=\\\"javascript:alert('Já tem amostra de sílica marcada para esta coleta');\\\"  onmouseover=\\\"Tip('Já tem amostra de sílica marcada para esta planta');\\\" >\";
    }
   \$imagen = \$imagen.\"&nbsp;\".\$imgg2.\"&nbsp;&nbsp;\".\$imgg3.\"&nbsp;&nbsp;\".\$imgg4.\"&nbsp;&nbsp;\".\$imgg5;
    \$data->set_value(\"EDIT\",\$imagen);";
} else {
$stringData .= "
    \$data->set_value(\"EDIT\",'');";
}
$stringData .= "
  \$llat = ABS($data->get_value(\"LATITUDE\"));
  \$llong = ABS(\$data->get_value(\"LONGITUDE\"));
  \$llcord = \$llat+\$llong;
  if (\$llcord>0) {
   \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"MAP\").\"' height='18' onclick=\\\"javascript:small_window('".$url."/mapasKML_plantas.php?plantaid=\".\$data->get_value(\"PlantaID\").\"',600,500,'Notas');\\\" onmouseover=\\\"Tip('Mapear a planta # \".\$pltag.\"');\\\" title=''>\";
  } else {
   \$imagen= '';
   //\"<img style='cursor:pointer;' src='icons/question-red.png' height='18' onclick=\\\"javascript:alert('Latitude & Longitude Faltando');\\\" onmouseover=\\\"Tip('Latitude e Longitude faltando para planta # \".\$pltag.\"');\\\" title=''>\";
  }
    \$data->set_value(\"MAP\",\$imagen);
    \$nir = \$data->get_value(\"NIRSpectra\");
    if (\$nir>0) {
          \$imagen=  \"<sup>  \".\$nir.\"</sup>&nbsp;<img style='  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;' src='icons/nirspectra.png' height='16' onmouseover=\\\"Tip('Existem \$nir espectros associados essa PLANTA');\\\" title=''>&nbsp;<sup>  \".\$nir.\"</sup>\";
     } else {
     \$imagen=  \"\";
     }
      \$data->set_value(\"NIRSpectra\",\$imagen);
    
    
}
function removeoperators(\$data){
  \$val = str_replace('>','',\$data);
  \$val = str_replace('<','',\$val);
  \$val = str_replace('=','',\$val);  
  return \$val;
}
function custom_filter(\$filter_by){
";
if (count($numericfilter)>0) {
$i=1;
$idxx = '
$idxss = array('; 
foreach ($numericfilter as $nuvar) {
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


//////CONECTA O GRID AOS DADOS USANDO MYSQL E APLICANDO OS FORMATOS DEFINIDOS
$stringData .= "
\$grid = new GridConnector(\$res);
\$grid ->event->attach(\"beforeRender\",\"custom_format_pl\");
\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
\$grid ->dynamic_loading(100);
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid ->render_sql(\"SELECT 0 as Marcado,tb.* FROM `".$tbname."` as tb ".$qwhere."\",\"PlantaID\",\"".$hdd."\");
?>";
//\$grid ->render_sql(\"SELECT * FROM `".$newtbname."`\",\"PlantaID\",\"".$hdd."\");
//\$grid ->render_sql(\"SELECT list.Marcado,tb.* FROM `".$newtbname."` as tb LEFT JOIN (SELECT * FROM `".$tbname."UserLists` WHERE UserID=".$uuid.") AS list ON  list.PlantaID=tb.PlantaID\",\"PlantaID\",\"".$hdd."\");

//\$grid ->dynamic_loading(".$nrecs.");
//\$grid ->dynamic_loading(100);
//\$grid ->render_table(\"".$tbname."\",\"PlantaID\",\"".$hdd."\")
fwrite($fh, $stringData);
fclose($fh);

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
'usertbname' => $newtbname,
'exportcols' => $exportcols,
'headertxt' => $headexplan,
'projetoid' => $projetoid
);
$_SESSION['arrtopass'] = serialize($arrofpass);
echo  "<form name='myform' action='projeto-plantas-grid.php' method='post'>";
  foreach ($arrofpass as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";
} 
else {
echo "<br />ProjetoID não foi definido<br />";
}


?>