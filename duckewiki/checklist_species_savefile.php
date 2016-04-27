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
//GENERAL TABLE
//if (empty($tbname)) { $tbname = 'checklist_all'; }

//$qn = "DROP TABLE ".$newtbname;
//@mysql_query($qn,$conn);

//apaga arquivo de progresso
$qqz = "DROP TABLE `temp_progspps".$tbname."`";
@mysql_query($qqz,$conn);

//GENERAL TABLE
//if (empty($tbname)) { $tbname = 'checklist_speclist'; }
$tbname = 'checklist_all';
$uuid = cleanQuery($_SESSION['userid'],$conn);
if ($uuid>0) {
	$newfilename = $tbname."_".$uuid;
	$acceslevel = cleanQuery($_SESSION['accesslevel'],$conn);
} else {
	$newfilename = $tbname."_".substr(session_id(),0,10);
	$acceslevel ='public';
}
$spectbname = 'checklist_speclist';
$plantastbname = 'checklist_pllist';



//USER COPY - PARA PODER MARCAR REGISTROS
//if ($uuid>0 && ($idd+0)==0) {
//$newtbname = 'tempSpp_'.$uuid;
//$newtbname2 = 'tempSpec_'.$uuid;
//$newtbname3 = 'tempPlantas_'.$uuid;
//$qq = "SELECT date_format(CREATE_TIME,'%Y-%m-%d') AS data1, date_format(CURRENT_DATE(),'%Y-%m-%d') AS data2, 
//DATEDIFF(CREATE_TIME,CURRENT_DATE()) AS DIFFS FROM information_schema.tables WHERE table_schema = '".$dbname."' AND table_name = '".$newtbname."'";
////echo $qq;
//$res = mysql_query($qq,$conn);
//$rww = mysql_fetch_assoc($res);
//$nrr = mysql_numrows($res);
//$newtbsql = "CREATE TABLE ".$newtbname." SELECT list.Marcado,tb.* FROM `".$tbname."` as tb LEFT JOIN (SELECT * FROM `".$tbname."UserLists` WHERE UserID=".$uuid.") AS list ON  list.SpptabIDS=makesppid(tb.FamiliaID, tb.GeneroID, tb.EspecieID, tb.InfraEspecieID)";
//} else {
//	if (($idd+0)==0) {
//		$newtbname = 'tempSpp_'.substr(session_id(),0,10);
//		$newtbname2 = 'tempSpec_'.substr(session_id(),0,10);
//		$newtbname3 = 'tempPlantas_'.substr(session_id(),0,10);
//		$nrr = 0;
//		$newtbsql = "CREATE TABLE ".$newtbname." SELECT 0 as Marcado,tb.* FROM `".$tbname."` as tb";
//	} 
//}
//if (($idd+0)>0) {
//	$newtbname = $tbname;
//	$nrr=1;
//}
//

//echo $newtbsql."<br >";
////SE A TABELA TEM 10 DIAS, ATUALIZA A TABELA DO USUARIO
//if ($rww['DIFFS']>10 || $nrr==0) {
//	//RETRIEVE TAGS AND  PERSONAL TABLE
//	$qq = "DROP TABLE ".$newtbname;
//	mysql_query($qq,$conn);
//	echo $qq."<br >";
//	mysql_query($newtbsql,$conn);
//	$qq = "ALTER TABLE `".$newtbname."`  ENGINE = InnoDB";
//	mysql_query($qq,$conn);
//	$qq = "ALTER TABLE ".$newtbname." ADD PRIMARY KEY (TempID)";
//	mysql_query($qq,$conn);
//	$qq = "ALTER TABLE ".$newtbname."  CHANGE `TempID` `TempID` INT( 10 ) NOT NULL AUTO_INCREMENT ";
//	mysql_query($qq,$conn);
//}
//

//CABEÇALHO DA TABELA
$headd = array("Marcado", "EDIT" , "DetID","GeneroID", "FamiliaID", "InfraEspecieID", "EspecieID",  "DetNivel", "FAMILIA", "NOME", "NOME_AUTOR", "MORFOTIPO", "ESPECIMENES", "PLANTAS", "PLOTS", "MAP", "OBS", "HABT", "IMG","NIRSpectra",  "SILICA", "FLORES", "FRUTOS", "VEG_CHARS", "FERT_CHARS", "FOLHA_IMG", "FLOR_IMG", "FRUTO_IMG", "EXSICATA_IMG");

$headexplan = array("Marcar ou desmarcar o registro","Editar e link para Tropicos.org","Uma das determinações nesse nível","Identificador do Genero em Tax_Generos","Identificador da Familia em Tax_Familias","Identificador da InfraEspécie em Tax_InfraEspecies","Identificador da Espécie em Tax_Especies","Nível da determinação","Familia","Nome da identificação sem autor","Nome da identificação com autor","Se o nome é um morfotipo spp indica no nivel de espécie e infspp no nível de infraespécie", "Número de especímenes com a identificação em NOME","Número de plantas marcadas com a identificação em NOME","Número de parcelas com árvores marcadas identificadas com esse NOME","Visualizar os ESPECIMENES num mapa ou baixar um arquivo KML para visualizar local","Notas associados à ESPECIMENES e PLANTAS com a identificação em NOME - a ser implementado como um resumo da variação associada","Mapear HABITATS LOCAIS associados à ESPECIMENES e PLANTAS com a identificação em NOME","Imagens associadas à ESPECIMENES e PLANTAS com a identificação em NOME","Espectros de InfraVermelho Próximo de Folhas Secas associados à ESPECIMENES e PLANTAS com a identificação em NOME","Número de amostras em silica","Número de amostras com flores","Número de amostras com frutos","Informacao de caracteres vegetativos no formulario Familia_VEGCHARS para 3 individuos do taxon correspondentes. Se 1 signfica que todos os 3 individuos tem informacao para todos os caracteres no formulario se existir", "Informacao de caracteres reprodutivos no formulario Familia_FERCHARS para 3 individuos do taxon correspondentes. Se 1 signfica que todos os 3 individuos tem informacao para todos os caracteres no formulario se existir", "Se existem imagens de folhas frescas", "Se existem imagens de flores", "Se existem imagens de frutos", "Se existem imagens de exsicata");
$exportcols = array("false","false","false","false","false","false","false","true","true","true","true","true","true","true","false","false","false","true","true","true","true","true","true",'false','false','false','false','false','false');
//LARGURA INICIAL DAS COLNUAS
$colw = array(
"Marcado" => 70,
"EDIT" => 80,
"DetID" => 0,
"GeneroID" => 0,
 "FamiliaID" => 0,
 "InfraEspecieID" => 0,
 "EspecieID" => 0,
 "DetNivel" => 0,
 "FAMILIA" => 150,
 "NOME" => 300,
 "NOME_AUTOR"  => 300,
 "MORFOTIPO" => 100,
 "ESPECIMENES" => 100,
 "PLANTAS" => 80,
 "PLOTS" => 65,
 "MAP" => 80,
 "OBS" => 60,
 "HABT" => 60,
 "IMG" => 60,
  "NIRSpectra" => 70,
  "SILICA" => 70,
  "FLORES" => 70,
  "FRUTOS" => 70,
    "VEG_CHARS" => 70,
  "FERT_CHARS" => 70,
  "FOLHA_IMG" => 70,
    "FLOR_IMG" => 70,
  "FRUTO_IMG" => 70,
  "EXSICATA_IMG" => 70
  );

//copia cabecalho para gerar ARRAYS PARA ATRIBUIR FORMATO (atribuido adiante)
$listvisible = $headd;
$filt = $headd; //define colunas com filtro
$filt2 = $headd;
$coltipos = $headd; //define tipo das colunas

$numericfilter = array();
//$numericfilter[] = "Marcado";
$numericfilter[]  = 'ESPECIMENES'; 
$numericfilter[]  = 'PLOTS'; 
$numericfilter[]  = 'PLANTAS'; 
$numericfilter[]  = 'NIRSpectra'; 
$numericfilter[]  = 'SILICA'; 
$numericfilter[]  = 'FLORES'; 
$numericfilter[]  = 'FRUTOS'; 
$numericfilter[]  = 'VEG_CHARS'; 
$numericfilter[]  = 'FERT_CHARS'; 
$numericfilter[]  = 'FOLHA_IMG'; 
$numericfilter[]  = 'FLOR_IMG'; 
$numericfilter[]  = 'FRUTO_IMG'; 
$numericfilter[]  = 'EXSICATA_IMG'; 
//FAZ UM LOOP PARA CADA COLUNA E DEFINE OS ARRAYS DE FORMATO
	//COLUNAS SEM FILTRO
	$nofilter = array("Marcado", "MAP", "OBS", "HABT","IMG");
	//COLUNAS QUE SAO IMAGENS
	$imgfields = array("EDIT","ESPECIMENES", "PLANTAS", "MAP", "OBS", "HABT","IMG","PLOTS","NIRSpectra","VEG_CHARS", "FERT_CHARS", "FOLHA_IMG", "FLOR_IMG", "FRUTO_IMG", "EXSICATA_IMG");
	//COLUNAS QUE NAO DEVEM APARECER
	$hidefields = array("GeneroID", "FamiliaID", "InfraEspecieID", "EspecieID", "OBS", "DetID",  "DetNivel", "NOME_AUTOR","MORFOTIPO","SILICA", "FLORES", "FRUTOS","VEG_CHARS", "FERT_CHARS", "FOLHA_IMG", "FLOR_IMG", "FRUTO_IMG", "EXSICATA_IMG");
	$i=1;
	$colidx = array();
	$collist = array();
	$coltipos = array();
	$colalign = $headd;
	$hidemenu = array();
	//mygrid.setColAlign("right,left,left,right,center,left,center,center");
	//mygrid.setColTypes("dyn,edtxt,ed,price,ch,co,ra,ro");
	foreach ($headd as $kk => $vv) {
		$qqr = "SELECT 0 as Marcado, tb.*  FROM ".$tbname." as tb PROCEDURE ANALYSE() WHERE Field_name LIKE '%".$tbname.".".$vv."%'";
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
$sesid = substr(session_id(),0,10);

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
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_allUserLists` WHERE TempID=\".\$idsp.\"  AND UserID='".$uuid."'\");";
} else {
$stringData .= "    
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_allUserLists` WHERE TempID=\".\$idsp.\"  AND SessionID='".$sesid."'\");";
}
$stringData .= "  
    \$nru = mysql_numrows(\$ru);
    if (\$nru!=\$status) {
       if (\$status==1) {";
       if ($uuid>0) {
$stringData .= "    
     \$qinn = \"INSERT INTO  `checklist_allUserLists` (`Marcado`,`TempID`,`UserID`) VALUES ('1' ,'\".\$idsp.\"','".$uuid."')\";";
} else {
$stringData .= "  
     \$qinn = \"INSERT INTO  `checklist_allUserLists` (`Marcado`,`TempID`,`SessionID`) VALUES ('1' ,'\".\$idsp.\"','".$sesid."')\";";
}
$stringData .= "  
     }  else {";
       if ($uuid>0) {
$stringData .= "    
     \$qinn = \"DELETE FROM  `checklist_allUserLists`  WHERE TempID='\".\$idsp.\"' AND UserID='".$uuid."'\";";
} else {
$stringData .= "  
     \$qinn = \"DELETE FROM  `checklist_allUserLists`  WHERE TempID='\".\$idsp.\"' AND SessionID='".$sesid."'\";";
}
$stringData .= "  
     }  
     \$ru = mysql_query(\$qinn);
   }          
   \$action->success();
}
function custom_format_list(\$data){
    \$famid = (\$data->get_value(\"FamiliaID\"))+0;
    \$genid = (\$data->get_value(\"GeneroID\"))+0;
    \$specid = (\$data->get_value(\"EspecieID\"))+0;
    \$infspecid = (\$data->get_value(\"InfraEspecieID\"))+0;
    
    \$nomesciid = '';
    if (\$infspecid>0) {
        \$nomesciid = 'infspid_'.\$infraspecid;
    } else {
        if (\$specid>0) {
            \$nomesciid = 'speciesid_'.\$specid;
        } else {
            if (\$genid>0) {
                \$nomesciid = 'genusid_'.\$genid;
            } else {
                if (\$famid>0) { \$nomesciid = 'famid_'.\$famid; }
            }
        }
    }
    \$mark = \$data->get_value(\"Marcado\");
    \$recid = \$data->get_id();";
if ($uuid>0) {
$stringData .= "    
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_allUserLists` WHERE TempID=\".\$recid.\"  AND UserID='".$uuid."'\");";
} else {
$stringData .= "    
    \$ru = mysql_query(\"SELECT Marcado FROM `checklist_allUserLists` WHERE TempID=\".\$recid.\"  AND SessionID='".$sesid."'\");";
}
$stringData .= "    
    \$ruw = mysql_fetch_assoc(\$ru);
    \$data->set_value(\"Marcado\", \$ruw['Marcado']);
    if ((\$data->get_value(\"ESPECIMENES\"))>0) {
      \$imagen= \"<sup>\".\$data->get_value(\"ESPECIMENES\").\"</sup><img style='cursor:pointer;' src='icons/specimen-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/checklist_specimens_save.php?tbname=".$spectbname."&famid=\".\$famid.\"&genid=\".\$genid.\"&specid=\".\$specid.\"&infspecid=\".\$infspecid.\"',950,500,'Visualizar amostras');\\\" onmouseover=\\\"Tip('Visualizar amostras');\\\" />\";
	 } else {
	 	 	\$imagen = \" \";
	 }
    \$data->set_value(\"ESPECIMENES\",\$imagen);

///////////////
\$famnome = \$data->get_value(\"FAMILIA\");
\$ruv = mysql_query(\"SELECT checkformtaxastatus('\".\$famnome.\"_VEGCHARS', \".\$famid.\", \".\$genid.\", \".\$specid.\", \".\$infspecid.\", 3) AS vegechars\");
//\$rutxt = \"SELECT checkformtaxastatus('\".\$famnome.\"_VEGCHARS', \".\$famid.\", \".\$genid.\", \".\$specid.\", \".\$infspecid.\", 3) AS vegechars\";
\$ruwv = mysql_fetch_assoc(\$ruv);
\$nvegc = \$ruwv['vegechars'];
if (\$nvegc>0) {
if (\$nvegc<=0.33) {
\$cimg = \"icons/redcircle.png\";
}
if (\$nvegc>0.33 && \$nvegc<=0.66) {
\$cimg = \"icons/orangecircle.png\";
}
if (\$nvegc>0.66) {
\$cimg = \"icons/greencircle.png\";
}
\$vegcimg = \"<sup>\".\$nvegc.\"</sup><img style='cursor:pointer;' src='\".\$cimg.\"' height='20'  onmouseover=\\\"Tip('Proporção de Caracteres vegetativos Mínimos para o taxon');\\\" />\";
} else {
  \$vegcimg = \"\";
}
\$data->set_value(\"VEG_CHARS\", \$vegcimg);

\$ruv = mysql_query(\"SELECT checkformtaxastatus('\".\$famnome.\"_FERTCHARS'), \".\$famid.\", \".\$genid.\", \".\$specid.\", \".\$infspecid.\", 3) AS fertchars\");
\$ruwv = mysql_fetch_assoc(\$ruv);
\$nfertc = \$ruwv['fertchars'];
if (\$nfertc>0) {
if (\$nfertc<=0.33) {
\$cimg = \"icons/redcircle.png\";
}
if (\$nfertc>0.33 && \$nvegc<=0.66) {
\$cimg = \"icons/orangecircle.png\";
}
if (\$nfertc>0.66) {
\$cimg = \"icons/greencircle.png\";
}
\$fertcimg = \"<sup>\".\$nfertc.\"</sup><img style='cursor:pointer;' src='\".\$cimg.\"' height='20'  onmouseover=\\\"Tip('Proporção de Caracteres reprodutivos mínimos para o taxon');\\\" />\";
} else {
\$fertcimg = \"\";
}
\$data->set_value(\"FERT_CHARS\", \$fertcimg);



\$ruv =mysql_query(\"SELECT checktaxaimg(\".\$famid.\", \".\$genid.\", \".\$specid.\", \".\$infspecid.\",".$folhaimgtraitid.") AS folimg\");
\$ruwv = mysql_fetch_assoc(\$ruv);
\$nfolimg = \$ruwv['folimg'];
if (\$nfolimg>0) {
\$cimg = \"icons/greencircle.png\";
\$folimg = \"<sup>\".\$nfolimg.\"</sup>&nbsp;<img style='cursor:pointer;' src='\".\$cimg.\"' height='20'  onmouseover=\\\"Tip('Tem pelo menos 1 imagem de folha fresca para o taxon');\\\"  alt=''  title='' >\";
} else {
\$folimg =\$nfolimg;
}
\$data->set_value(\"FOLHA_IMG\", \$folimg);

\$ruv =mysql_query(\"SELECT checktaxaimg(\".\$famid.\", \".\$genid.\", \".\$specid.\", \".\$infspecid.\",".$florimgtraitid.") AS florimg\");
\$ruwv = mysql_fetch_assoc(\$ruv);
\$nflorimg = \$ruwv['florimg'];
if (\$nflorimg>0) {
\$cimg = \"icons/greencircle.png\";
\$florimg = \"<sup>\".\$nflorimg.\"</sup><img style='cursor:pointer;' src='\".\$cimg.\"' height='20'  onmouseover=\\\"Tip('Tem pelo menos 1 imagem de flores para o taxon');\\\" />\";
} else {
\$florimg =\$nflorimg;
}
\$data->set_value(\"FLOR_IMG\", \$florimg);

\$ruv =mysql_query(\"SELECT checktaxaimg(\".\$famid.\", \".\$genid.\", \".\$specid.\", \".\$infspecid.\",".$frutoimgtraitid.") AS frutoimg\");
\$ruwv = mysql_fetch_assoc(\$ruv);
\$nfrutoimg = \$ruwv['frutoimg'];
if (\$nfrutoimg>0) {
\$cimg = \"icons/greencircle.png\";
\$frutoimg = \"<sup>\".\$nfrutoimg.\"</sup><img style='cursor:pointer;' src='\".\$cimg.\"' height='20'  onmouseover=\\\"Tip('Tem pelo menos 1 imagem de frutos para o taxon');\\\" />\";
} else {
\$frutoimg =\$nfrutoimg;
}
\$data->set_value(\"FRUTO_IMG\", \$frutoimg);

\$ruv =mysql_query(\"SELECT checktaxaimg(\".\$famid.\", \".\$genid.\", \".\$specid.\", \".\$infspecid.\",".$exsicatatrait.") AS exsicataimg\");
\$ruwv = mysql_fetch_assoc(\$ruv);
\$nexsicataimg = \$ruwv['exsicataimg'];
if (\$nexsicataimg>0) {
\$cimg = \"icons/greencircle.png\";
\$exsicataimg = \"<sup>\".\$nexsicataimg.\"</sup><img style='cursor:pointer;' src='\".\$cimg.\"' height='20'  onmouseover=\\\"Tip('Tem pelo menos 1 imagem de exsicatas para o taxon');\\\" />\";
} else {
\$exsicataimg =\$nexsicataimg;
}
\$data->set_value(\"EXSICATA_IMG\", \$exsicataimg);
\$plnumb = \$data->get_value(\"PLANTAS\");
";
if ($listsarepublic['plantas'] == 'on' || $uuid>0) {
	$pltlktxt =   "\$imagen= \"<sup>  \".\$data->get_value(\"PLANTAS\").\"</sup><img style='cursor:pointer;' src='icons/tree-icon.png' height='20'  onclick=\\\"javascript:small_window('".$url."/checkllist_plantas_save.php?tbname=".$plantastbname."&famid=\".\$famid.\"&genid=\".\$genid.\"&specid=\".\$specid.\"&infspecid=\".\$infspecid.\"',950,500,'Plantas');\\\"  onmouseover=\\\"Tip('Visualizar plantas desse taxon');\\\" />\";";
} else {
	$pltlktxt =   "\$imagen= \"<sup>  \".\$data->get_value(\"PLANTAS\").\"</sup><img style='cursor:pointer;' src='icons/tree-icon.png' height='20'  onmouseover=\\\"Tip('Este taxon tem árvores marcadas \\\n mas você não tem permissão para ver esses dados');\\\" alt=\\\"\\\" />\";";
}
$stringData .= " if (\$plnumb>0) {
".$pltlktxt."
} else {
  \$imagen = \" \";
}
\$data->set_value(\"PLANTAS\",\$imagen);
if ((\$data->get_value(\"PLOTS\"))>0) {
		\$nomee = \$data->get_value(\"NOME\");
		\$imagen=\"<sup>  \".\$data->get_value(\"PLOTS\").\"</sup><img style='cursor:pointer;' src='icons/icon_plot.png' height='20' onclick=\\\"javascript:small_window('".$url."/plantasINplots-popup.php?titulo=\".\$nomee.\"&ispopup=1&famid=\".\$famid.\"&genid=\".\$genid.\"&specid=\".\$specid.\"&infspecid=\".\$infspecid.\"',1000,800,'Mapas de parcelas');\\\" onmouseover=\\\"Tip('Visualizar parcelas com plantas desse taxon');\\\" />\";
	} else {
		\$imagen = \" \";
	}
	\$data->set_value(\"PLOTS\",\$imagen);
	
	\$tropicos = (\$data->get_value(\"EDIT\"));
	\$imagen = \" \";
	\$imagen2 = \"\";
	\$imagen3 = \"\";
	unset(\$nameedit);
	if (!empty(\$tropicos)) {
		\$imagen=\"<img style='cursor:pointer;' src='icons/mobot.png' height='18' onclick=\\\"javascript:small_window('http://www.tropicos.org/NameSearch.aspx?name=\".\$tropicos.\"',1000,800,'Tropicos');\\\" onmouseover=\\\"Tip('Ver registro do nome \".\$tropicos.\" em tropicos.org');\\\" />\";
	}
	\$nameedit=  (\$data->get_value(\"NOME\"));";
if ($uuid>0 && $acceslevel!='visitor') {
$stringData .= "
	\$imagen2 = \"<img style='cursor:pointer;' src='icons/diversity.png' height='18' onclick=\\\"javascript:small_window('".$url."/taxa-form.php?final=1&nomesciid=nomesciid&famid=\".\$famid.\"&genusid=\".\$genid.\"&speciesid=\".\$specid.\"&infraspid=\".\$infspecid.\"&ispopup=1',700,500,'Editando nome');\\\" onmouseover=\\\"Tip('Editando a taxonomia de \".\$nameedit.\"');\\\" alt='' />\";";
	if ($acceslevel!='user') {
$stringData .= "
	if (\$specid>0 || \$infspecid>0) {
	\$imagen3 = \"<img style='cursor:pointer;' src='icons/changename.png' height='18' onclick=\\\"javascript:small_window('".$url."/identifybyname_all.php?nomesearch=\".\$nameedit.\"&speciesid=\".\$specid.\"&infraspid=\".\$infspecid.\"&tempid=\".\$recid.\"&tbname=".$tbname."',700,500,'Substituindo o nome \".\$nameedit.\" por outro');\\\" onmouseover=\\\"Tip('Substituindo o nome \".\$nameedit.\" por outro');\\\" alt='' />\";
	}";
	} 
$stringData .= "   \$imgg33 =\"<img style='cursor:pointer;' src='icons/nota-icon.png' height='16' onclick=\\\"javascript:small_window('".$url."/traits_coletorvariacao.php?nomesciid=\".\$nomesciid.\"&taxavariacao=1',800,800,'Editando notas');\\\"  onmouseover=\\\"Tip('Edita notas da amostra # \".\$pltag.\"');\\\" >\";";
$stringData .= "
	\$imagen = \$imagen2.\"&nbsp;\".\$imagen.\"&nbsp;\".\$imagen3.\"&nbsp;\".\$imgg33;
	";
} 
$stringData .= "
	\$data->set_value(\"EDIT\",\$imagen);

	\$idds = \$famid+\$genid+\$specid+\$infspecid;
	\$myhabitat = (\$data->get_value(\"HABT\"))+0;
    if (\$myhabitat>0) {
	 	 \$imagen=\"<img style='cursor:pointer;' src='icons/environment_icon.png' height='17' onclick=\\\"javascript:small_window('".$url."/plothabitat_createkml_byspecies_form.php?famid=\".\$famid.\"&genid=\".\$genid.\"&specid=\".\$specid.\"&infspecid=\".\$infspecid.\"&ispopup=1',700,500,'Habitats');\\\" onmouseover=\\\"Tip('Mapear os habitats');\\\">\";
	} else {
		\$imagen = \" \";
	}
    \$data->set_value(\"HABT\",\$imagen);
        

    \$idds = \$famid+\$genid+\$specid+\$infspecid;
    if (\$idds>0) {
	 \$imagen=\"<img style='cursor:pointer;' src='icons/\".\$data->get_value(\"MAP\").\"' height='17' onclick=\\\"javascript:small_window('".$url."/mapasKML.php?famid=\".\$famid.\"&genid=\".\$genid.\"&specid=\".\$specid.\"&infspecid=\".\$infspecid.\"',800,500,'Mapa');\\\" onmouseover=\\\"Tip('Ver em mapa');\\\">\";
	 \$imagen2=\"<img style='cursor:pointer;' src='icons/map-download.png' height='18' onclick=\\\"javascript:small_window('".$url."/mapasKML.php?download=1&famid=\".\$famid.\"&genid=\".\$genid.\"&specid=\".\$specid.\"&infspecid=\".\$infspecid.\"',200,200,'Download map');\\\" onmouseover=\\\"Tip('Baixar arquivo KML');\\\" >\";
	 \$imagen = \$imagen.\"&nbsp;\".\$imagen2;
	} else {
	 \$imagen=\"<img style='cursor:pointer;' src='icons/question-red.png' height='18' title='Não dá para mapear, faltam coordenadas' onmouseover=\\\"Tip('Não dá para mapear, faltam coordenadas');\\\">\";
	}
    \$data->set_value(\"MAP\",\$imagen);
    
    
    \$imgs = (\$data->get_value(\"IMG\"))+0;
    if (\$imgs>0) {
	 \$imagen=\"<img style='cursor:pointer;' src='icons/camera.png' height='18' onclick=\\\"javascript:small_window('".$url."/showimage_taxa.php?famid=\".\$famid.\"&genid=\".\$genid.\"&specid=\".\$specid.\"&infspecid=\".\$infspecid.\"',1000,600,'Imagens de Taxa');\\\"  onmouseover=\\\"Tip('Visualizar imagens para esse taxon');\\\" />\";
	} else {
		\$imagen = \" \";
	}
    \$data->set_value(\"IMG\",\$imagen);
    
    \$nir = \$data->get_value(\"NIRSpectra\");
    if (\$nir>0) {
      \$imagen=  \"<sup>  \".\$nir.\"</sup>&nbsp;<img style='  -webkit-box-shadow:inset 0 0 6px #cccccc; -moz-box-shadow: inset 0 0 6px #cccccc; cursor: pointer;' src='icons/nirspectra.png' height='16' onmouseover=\\\"Tip('Existem \$nir espectros associados as plantas ou especimenes que tem esse NOME. Clique para ver!');\\\" onclick=\\\"javascript:small_window('".$url."/export-nir-data-form.php?famid=\".\$famid.\"&genid=\".\$genid.\"&specid=\".\$specid.\"&infspecid=\".\$infspecid.\"&checklist=1',800,600,'Exporta dados NIR');\\\"   alt=''  title=''>\";
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

//".($nrecs+1)."

//////CONECTA O GRID AOS DADOS USANDO MYSQL E APLICANDO OS FORMATOS DEFINIDOS
$stringData .= "
\$grid = new GridConnector(\$res);
\$grid ->event->attach(\"beforeRender\",\"custom_format_list\");
\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
\$grid ->dynamic_loading(100);
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid ->render_sql(\"SELECT 0 as Marcado, tb.* FROM `".$tbname."` as tb\",\"TempID\",\"".$hdd."\");
?>";
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
//$qq = "ALTER TABLE `".$tbname."UserLists`  ";
//@mysql_query($qq,$conn);

//\$grid ->render_sql(\"SELECT tb.*,list.SpptabIDS FROM `".$tbname."` as tb LEFT JOIN UserRecList as list ON list.SpptabIDS=CONCAT(tb.FamiliaID,'_',tb.GeneroID,'_',tb.EspecieID,'_',tb.InfraEspecieID) WHERE list.UserID='".$uiid."'\",\"TempID\",\"".$hdd."\");


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
'usertbname' => $tbname,
'exportcols' => $exportcols,
'headertxt' => $headexplan
);
//$_SESSION['checklistarray']['taxonomic'] = serialize($arrofpass);
if ($seepop==1) {
$_SESSION['arrofpass'] = serialize($arrofpass);
//echopre($arrofpass);
header("location: checklist_view_generic.php");
} else {
$_SESSION['checklist_species'] = serialize($arrofpass);
}
echo "CONCLUIDO";
?>