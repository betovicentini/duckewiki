<?php
//Start session
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//apaga arquivo de progresso
$qqz = "DROP TABLE `temp_progspec".$tbname."`";
@mysql_query($qqz,$conn);

//GENERAL TABLE
//if (empty($tbname)) { $tbname = 'checklist_speclist'; }
$tbname = 'checklist_speclist';
$uuid = cleanQuery($_SESSION['userid'],$conn);
if ($uuid>0) {
	$newfilename = $tbname."_".$uuid;
	$acceslevel = cleanQuery($_SESSION['accesslevel'],$conn);
} else {
	$newfilename = $tbname."_".substr(session_id(),0,10);
	$acceslevel ='public';
}

$qwhere = ''; 
#CASO SEJA PARA MOSTRAR AMOSTRAS DE UM TAXON $detid SERA MAIOR QUE ZERO
$detid =0;
$detid = $famid+$genid+$specid+$infspecid+$detid;
//SE ESTIVER VIZUALIZANDO ESPECIMENES PARA UM DETERMINADO TAXA CRIA UMA TABELA PARA ISSO
if ($detid>0 && !empty($tbname)) {
	$newfilename = $newfilename."_".$detid;
    $qwhere = " WHERE isdetvalid(tb.DetID, ".($famid+0).",".($genid+0).", ".($specid+0).", ".($infspecid+0).")>0";
    
    //$newtbname = $tbname.$detid;
    //$newtbsql = "CREATE TABLE ".$newtbname." SELECT * FROM `".$tbname."` as tb WHERE isdetvalid(tb.DetID, ".($famid+0).",".($genid+0).", ".($specid+0).", ".($infspecid+0).")>0";
	//$newtbsql  .= $qwhere;
	//$nrr = 0;
    //$newtbsql = "SELECT 0 as Marcado,tb.* FROM `".$tbname."` as tb WHERE isdetvalid(tb.DetID, ".($famid+0).",".($genid+0).", ".($specid+0).", ".($infspecid+0).")>0";
}
#CASO SEJA PARA MOSTRAR AMOSTRAS DE UMA LOCALIDADE OU REGIAO
if ($idd>0 && !empty($tableref)) {
	$newfilename = $newfilename."_".$tableref."_".$idd;
	$qwhere = " WHERE isvalidlocal(tb.GazetteerID,tb.GPSPointID, ".$idd.", '".$tableref."')"; 
	//$nrr = 0;
}

#CASO SEJA PARA MOSTRAR AMOSTRAS DE UMA PLANTA MARCADA
if ($plantaid>0 && !empty($tbname)) {
	$qwhere = " WHERE tb.PlantaID=".$plantaid; 
}


//if ($uuid>0 && $detid==0 && ($idd+0)==0) {
//$newtbname = 'tempSpec_'.$uuid;
//$qq = "SELECT date_format(CREATE_TIME,'%Y-%m-%d') AS data1, date_format(CURRENT_DATE(),'%Y-%m-%d') AS data2, 
//DATEDIFF(CREATE_TIME,CURRENT_DATE()) AS DIFFS FROM information_schema.tables WHERE table_schema = '".$dbname."' AND table_name = '".$newtbname."'";
////echo $qq;
//$res = mysql_query($qq,$conn);
//$rww = mysql_fetch_assoc($res);
//$nrr = mysql_numrows($res);
//$newtbsql =  "CREATE TABLE ".$newtbname." SELECT list.Marcado,tb.* FROM `".$tbname."` as tb LEFT JOIN (SELECT * FROM `".$tbname."UserLists` WHERE UserID=".$uuid.") AS list ON  list.EspecimenID=tb.EspecimenID";
//} 
//else {
//	if ($detid==0 && ($idd+0)==0) {
//		$newtbname = 'tempSpec_'.substr(session_id(),0,10);
//		$nrr = 0;
//		$newtbsql = "CREATE TABLE ".$newtbname." SELECT 0 as Marcado,tb.* FROM `".$tbname."` as tb";
//	}
//}





////SE A TABELA TEM 10 DIAS, ATUALIZA A TABELA DO USUARIO OU GERA SE NAO TIVER USUARIO
//if ($rww['DIFFS']>10 || $nrr==0) {
//	//RETRIEVE TAGS AND  PERSONAL TABLE
//	$qq = "DROP TABLE ".$newtbname;
//	mysql_query($qq,$conn);
//
//	mysql_query($newtbsql,$conn);
//	//echo $newtbsql;
//	$qq = "ALTER TABLE `".$newtbname."`  ENGINE = InnoDB";
//	mysql_query($qq,$conn);
//	$qq = "ALTER TABLE ".$newtbname." ADD PRIMARY KEY (EspecimenID)";
//	mysql_query($qq,$conn);
//}
//
//$qq = "ALTER TABLE ".$newtbname."  CHANGE `TempID` `TempID` INT( 10 ) NOT NULL AUTO_INCREMENT ";
//mysql_query($qq,$conn);

//COMECA A PREPARA O ARQUIVO QUE MOSTRA O GRID
$numericfilters = array();
//$numericfilters[] = "Marcado";
$numericfilters[]  = 'PlantaTag'; 
$numericfilters[]  = $herbariumsigla; 
$numericfilters[]  = 'NUMERO'; 
$headd = array("Marcado", "EspecimenID","DetID","EDIT","COLETOR", "NUMERO", "PlantaTag","PlantaID","DATA", $herbariumsigla, "HERBARIA", "FAMILIA", "NOME", "NOME_AUTOR","DETBY","DETYY","MORFOTIPO", "PAIS", "ESTADO", "MUNICIPIO", "LOCAL", "LOCALSIMPLES");
$headexplan = array("Marcar ou desmarcar o registro", "Identificador do ESPECIME em Especimenes","Identificador da determinação em Identidade","Links para edição do registro e dados associados","Nome do coletor","Número de coleta","Código da placa da árvore","Identificador da planta associada à amostra","Data de coleta do ESPECIMENE", "Numero de registro do herbário ".$herbariumsigla, "Sigla dos herbários onde a amostra está depositada","Familia","Nome da identificação da planta sem autor","Nome da identificação da planta com autor","Quem identificou","Ano de identificação", "Se o nome é um morfotipo spp indica no nivel de espécie e infspp no nível de infraespécie", "Pais  do local", "Estado do local", "Municipio do local", "Localidade completa","Localidade mais especifica");
$exportcols = array("false","true","false","false","true","true","true", "true","true", "true","true","true","true","true","true","true","true","true","true","true","true","true");
$colw = array(
"Marcado" => 70,
"EspecimenID" => 0,
"DetID" => 0,
"EDIT" => 130,
"COLETOR" => 110,
"NUMERO" => 70,
"PlantaTag" => 0,
"PlantaID" => 0,
"DATA" => 70,
$herbariumsigla => 50,
"HERBARIA" => 50,
"FAMILIA" => 80,
"NOME" => 150,
"NOME_AUTOR" => 150,
"DETBY" => 0,
"DETYY" => 0,
"MORFOTIPO" => 100,
"PAIS" => 50,
"ESTADO" => 70,
"MUNICIPIO" => 150,
"LOCAL" => 220,
"LOCALSIMPLES" => 100);
	if ($daptraitid>0) {
		$headd[] = 'DAPmm';
		$colw = array_merge((array)$colw,(array)array('DAPmm' => 60));
		$numericfilters[]  = 'DAPmm'; 
		$exportcols[] = "true";
		$headexplan[] = 'Máximo dos DAPs associados ao ESPECIMENE em mm';

	}
	if ($alturatraitid>0) {
		$headd[] = 'ALTURA';
		$numericfilters[]  = 'ALTURA'; 
		$colw = array_merge((array)$colw,(array)array('ALTURA' => 65));
		$exportcols[] = "true";
		$headexplan[] = 'Máximo das alturas associadas ao ESPECIMENE em metros';
	}
	if ($habitotraitid>0) {
		$headd[] = 'HABITO';
		$colw = array_merge((array)$colw,(array)array('HABITO' => 100));
		$exportcols[] = "true";
		$headexplan[] = 'Hábito da planta';

	}
	if ($traitfertid>0) {
		$headd[] = 'FERTILIDADE';
		$colw = array_merge((array)$colw,(array)array('FERTILIDADE' => 100));
		$exportcols[] = "true";
		$headexplan[] = 'O estado de fertilidade da amostra coletada';
	}
$headd = array_merge((array)$headd,(array)array("LONGITUDE", "LATITUDE", "ALTITUDE", "DUPS","MAP","OBS","HABT","HABT_CLASSE","IMG","NIRSpectra","PROJETOstr"));
$numericfilters[]  = 'NIRSpectra'; 
$headexplan = array_merge((array)$headexplan,(array)array("Longitude em décimos de grau","Latitude em décimos de grau", "Altitude em metros","Número de duplicatas","Visualizar o ESPECIMENE num mapa","Visualizar ou baixar em PDF etiqueta para o ESPECIMENE incluindo todas as observações associadas a ele","Descreve o hábitat da amostra","Classe de hábitat da amostra","Visualiza imagens associadas à amostra","Visualiza dados NIR associados ao especímene","Nome do projeto de Pesquisa à qual a planta está associada"));
$exportcols = array_merge((array)$exportcols,(array)array("true", "true", "true", "false","false","false","false","true","false","true","false","true"));
$colw = array_merge((array)$colw,(array)array("LONGITUDE" => 40,
"LATITUDE" => 0,
"ALTITUDE" => 0,
"DUPS" => 0,
"MAP" => 45,
"OBS" => 60,
"HABT" => 60,
"HABT_CLASSE" => 100,
"IMG" => 40,
"NIRSpectra" => 70,
"PROJETOstr" => 0
));
	$listvisible = $headd;
	$filt = $headd;
	$filt2 = $headd;
	$coltipos = $headd;
	$nofilter = array("Marcado", "OBS", "IMG", "EDIT", "HABT","MAP");
	$imgfields = array("OBS", "IMG", "EDIT", "HABT","MAP","NIRSpectra");
	//$numericfilter = array("DAPmm","ALTURA");
	if(!isset($uuid) || (trim($uuid)=='') || $acceslevel=='visitor' || $uuid==0) {
		$hidefields = array("EspecimenID","LONGITUDE", "LATITUDE", "ALTITUDE", "DUPS","EDIT","DetID","GazetteerID","GPSPointID","PlantaTag","PlantaID","NOME_AUTOR","DETBY",
"DETYY","MORFOTIPO",'FERTILIDADE',"NIRSpectra","HERBARIA");
	} else {
		$hidefields = array("EspecimenID", "LONGITUDE", "LATITUDE", "ALTITUDE", "DUPS","DetID","GazetteerID","GPSPointID","PlantaTag","PlantaID","NOME_AUTOR","DETBY",
"DETYY","MORFOTIPO", "FERTILIDADE","NIRSpectra","HABT_CLASSE","HERBARIA");
	}
	$i=1;
	$ncl = count($headd)-count($imgfields)-count($hidefields);
	$nimg = count($imgfields);
	$nimg = $nimg*50;
	$cl = floor((900-$nimg)/$ncl);
	$colidx = array();
	$collist = array();
	$coltipos = array();
	$colalign = $headd;
	$hidemenu = array();
	//mygrid.setColAlign("right,left,left,right,center,left,center,center");
	//mygrid.setColTypes("dyn,edtxt,ed,price,ch,co,ra,ro");
	foreach ($headd as $kk => $vv) {
		$qqr = "SELECT 0 as Marcado,tb.* FROM ".$tbname." as tb PROCEDURE ANALYSE() WHERE Field_name LIKE '%".$tbname.".".$vv."%'";
		$rr = @mysql_query($qqr,$conn);
		$row = @mysql_fetch_assoc($rr);
		if (!in_array($vv,$nofilter)) {
			if (@in_array($vv,$numericfilter)) {
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
			if (@in_array($vv,$numericfilter)) {
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
	IF ($uuid>0) {
		$fnn = $newfilename.".php";
	} else {
		$fnn = $newfilename.".php";
	}
	$fh = fopen("temp/".$fnn, 'w');
	$stringData = "<?php
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");
include \"../functions/MyPhpFunctions.php\";";
$stringData .= "    
function myUpdate(\$action){
    \$status = \$action->get_value('Marcado');
    \$idsp = \$action->get_id();";
    if ($uuid>0) {
$stringData .= "    
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_speclistUserLists` WHERE EspecimenID=\".\$idsp.\"  AND UserID='".$uuid."'\");";
} else {
$stringData .= "    
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_speclistUserLists` WHERE EspecimenID=\".\$idsp.\"  AND SessionID='".$sesid."'\");";
}
$stringData .= "  
    \$nru = mysql_numrows(\$ru);
    if (\$nru!=\$status) {
       if (\$status==1) {";
       if ($uuid>0) {
$stringData .= "    
     \$qinn = \"INSERT INTO  `checklist_speclistUserLists` (`Marcado`,`EspecimenID`,`UserID`) VALUES ('1' ,'\".\$idsp.\"','".$uuid."')\";";
} else {
$stringData .= "  
     \$qinn = \"INSERT INTO  `checklist_speclistUserLists` (`Marcado`,`EspecimenID`,`SessionID`) VALUES ('1' ,'\".\$idsp.\"','".$sesid."')\";";
}
$stringData .= "  
     }  else {";
       if ($uuid>0) {
$stringData .= "    
     \$qinn = \"DELETE FROM  `checklist_speclistUserLists`  WHERE EspecimenID='\".\$idsp.\"' AND UserID='".$uuid."'\";";
} else {
$stringData .= "  
     \$qinn = \"DELETE FROM  `checklist_allUserLists`  WHERE EspecimenID='\".\$idsp.\"' AND SessionID='".$sesid."'\";";
}
$stringData .= "  
     }  
     \$ru = mysql_query(\$qinn);
   }          
   \$action->success();
}
function custom_format_spec(\$data){
  \$pltag = \$data->get_value(\"COLETOR\").\" \".\$data->get_value(\"NUMERO\");
  \$thedetid = \$data->get_value(\"DetID\");
  \$thespecimenid = \$data->get_value(\"EspecimenID\");
  \$mark = \$data->get_value(\"Marcado\");
  \$recid = \$data->get_id();";
if ($uuid>0) {
$stringData .= "    
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_speclistUserLists` WHERE EspecimenID=\".\$recid.\"  AND UserID='".$uuid."'\");";
} else {
$stringData .= "    
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_speclistUserLists` WHERE EspecimenID=\".\$recid.\"  AND SessionID='".$sesid."'\");";
}
$stringData .= "    
    \$ruw = mysql_fetch_assoc(\$ru);
    \$data->set_value(\"Marcado\", \$ruw['Marcado']);
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
    \$data->set_value(\"OBS\",\$imagen);";
if ($uuid>0 && $acceslevel!='visitor') {
$stringData .= "
    \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"EDIT\").\"' height='20' onclick=\\\"javascript:small_window('".$url."/especimenes_dataform.php?ispopup=1&especimenid=\".\$data->get_value(\"EspecimenID\").\"',1000,600,'Editar registro');\\\" onmouseover=\\\"Tip('Editar o especímene # \".\$pltag.\"');\\\" >\";
    \$imagen2=\"<img style='cursor:pointer;' src='icons/rednameicon.png' height='20' onclick=\\\"javascript:small_window('".$url."/taxonomia-popup.php?updatechecklist=1&ispopup=1&saveit=true&detid=\".\$data->get_value(\"DetID\").\"&especimenid=\".\$data->get_value(\"EspecimenID\").\"',800,400,'Editar Identificação');\\\" onmouseover=\\\"Tip('Editar Identificação da amostra # \".\$pltag.\"');\\\" >\";
    \$imgg3 =\"<img style='cursor:pointer;' src='icons/nota-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/traits_coletorvariacao.php?apagavarsess=1&saveit=1&formid=".$formnotes."&especimenid=\".\$data->get_value(\"EspecimenID\").\"',800,800,'Editando notas');\\\"  onmouseover=\\\"Tip('Edita notas da amostra # \".\$pltag.\"');\\\" >\";
    \$imgg4 =\"<img style='cursor:pointer;' src='icons/samples.png' height='20' onclick=\\\"javascript:small_window('".$url."/adiciona_processo.php?especimenid=\".\$data->get_value(\"EspecimenID\").\"',700,400,'Adicionando ao processo');\\\"  onmouseover=\\\"Tip('Adiciona a amostra # \".\$pltag.\" a um processo');\\\" >\";
    
     \$ruu = mysql_query(\"SELECT TraitID FROM Traits WHERE ParentID='".$traitsilica."' AND LOWER(TraitName) LIKE '%silica%'\");
     \$ruuw = mysql_fetch_assoc(\$ruu);
     \$silicavar = \$ruuw['TraitID'];
     \$rnn = mysql_query(\"SELECT * FROM Traits_variation WHERE TraitID='".$traitsilica."' AND EspecimenID=\".\$data->get_value(\"EspecimenID\").\" AND (TraitVariation LIKE '\".\$silicavar.\"%'  OR TraitVariation LIKE 
     '%;\".\$silicavar.\"%' )\");
     \$nrnn = mysql_numrows(\$rnn);
     if (\$nrnn==0) {
     \$imgg5 =\"<img style='cursor:pointer;' src='icons/dna.png' height='20' onclick=\\\"javascript:amostrasilica(\".\$data->get_value(\"EspecimenID\").\",0);\\\"  onmouseover=\\\"Tip('Marca que tem amostra em silica');\\\" >\";
    } else {
     \$imgg5 =\"<img style='cursor:pointer;' src='icons/dna_ok.png' height='20' onclick=\\\"javascript:alert('Já tem amostra de sílica marcada para esta coleta');\\\"  onmouseover=\\\"Tip('Já tem amostra de sílica marcada para esta coleta');\\\" >\";
    }
    \$imagen = \$imagen.\"&nbsp;\".\$imagen2.\"&nbsp;\".\$imgg3.\"&nbsp;\".\$imgg4.\"&nbsp;\".\$imgg5;
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
      \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"MAP\").\"' height='18' onclick=\\\"javascript:small_window('".$url."/mapasKML.php?ispopup=1&specimenid=\".\$data->get_value(\"EspecimenID\").\"',600,500,'Notas');\\\" onmouseover=\\\"Tip('Mapear a amostra # \".\$pltag.\"');\\\" >\";
    } else {
      \$imagen= '';
      //\"<img style='cursor:pointer;' src='icons/question-red.png' height='18' onclick=\\\"javascript:alert('Latitude & Longitude Faltando');\\\" onmouseover=\\\"Tip('Latitude e Longitude faltando para amostra # \".\$pltag.\"');\\\"  >\";
    }
    \$data->set_value(\"MAP\",\$imagen);
    \$nir = \$data->get_value(\"NIRSpectra\");
    if (\$nir>0) {
          \$imagen=  \"<sup>  \".\$nir.\"</sup>&nbsp;<img style='  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;' src='icons/nirspectra.png' height='16' onmouseover=\\\"Tip('Existem \$nir espectros associados esse ESPECIMENE');\\\" title=''>\";
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
\$grid ->event->attach(\"beforeRender\",\"custom_format_spec\");
\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
\$grid ->dynamic_loading(100);
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid ->render_sql(\"SELECT 0 as Marcado,tb.* FROM ".$tbname." as tb  ".$qwhere."\",\"EspecimenID\",\"".$hdd."\");
?>";
//$mysql = 
//\$grid ->render_sql(\"SELECT * FROM `".$newtbname."`\",\"EspecimenID\",\"".$hdd."\");
//\$grid ->render_table(\"".$tbname."\",\"EspecimenID\",\"".$hdd."\")
fwrite($fh, $stringData);
fclose($fh);

$qq = "CREATE TABLE IF NOT EXISTS `".$tbname."UserLists` (
Marcado TINYINT(1),
ListID INT(10) unsigned NOT NULL auto_increment,
EspecimenID INT(10),
UserID INT(10),
SessionID CHAR(255),
PRIMARY KEY (ListID)) CHARACTER SET utf8 ENGINE = InnoDB";
@mysql_query($qq,$conn);



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
'usertbname' => $newtbname,
'exportcols' => $exportcols,
'headertxt' => $headexplan
);

//$_SESSION['checklistarray']['specimens'] = serialize($arrofpass);
if ($detid>0 || $idd>0 || $plantaid>0) {
$_SESSION['arrofpass'] = serialize($arrofpass);
//echopre($arrofpass);
//echopre($hdd);
//echo "SELECT 0 as Marcado,tb.* FROM ".$tbname." as tb  ".$qwhere;
header("location: checklist_view_generic.php");
} 
$_SESSION['checklist_specimens'] = serialize($arrofpass);
echo "CONCLUIDO";
?>