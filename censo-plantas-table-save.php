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


if ($censoid>0) {

//CRIA A TABELA TEMPORARIA DE FILTRAGEM E MARCACAO DOS REGISTROS PARA INCLUIR NA MONOGRAFIA
$temptb = 'temp_censosplantas_'.$censoid.'_'.$uuid;
//$qc = "DROP TABLE ".$temptb;
//@mysql_query($qc);
//$qc = "CREATE TABLE ".$temptb." SELECT IF(CensoID=".$censoid.",1,0) as Incluido, pltb.PlantaID,pltb.TAG,pltb.TAGtxt,pltb.FAMILIA,pltb.NOME,pltb.PAIS,pltb.ESTADO,pltb.MUNICIPIO,pltb.LOCAL,pltb.LOCALSIMPLES,pltb.PROJETOstr,moni.MonitoramentoID,tr.TraitName,tr.PathName as TraitPathName,TRIM(REPLACE('Variavel|','',tr.TraitTipo)) AS TraitTipo,moni.TraitVariation,moni.DataObs as TraitDataObs,moni.TraitUnit FROM `Monitoramento` AS moni
//LEFT JOIN checklist_pllist AS pltb ON moni.PlantaID=pltb.PlantaID LEFT JOIN Traits AS tr ON moni.TraitID=tr.TraitID WHERE moni.CensoID=0 OR moni.CensoID IS NULL OR moni.CensoID=".$censoid;
////echo $qc."<br >";
//@mysql_query($qc);
//$qq = "ALTER TABLE ".$temptb." ADD PRIMARY KEY(MonitoramentoID)";
//@mysql_query($qq,$conn);
//$sql = "CREATE INDEX COLETOR ON ".$temptb."  (PlantaTag)";
//@mysql_query($sql,$conn);
//$sql = "CREATE INDEX NOME ON ".$temptb."  (NOME)";
//@mysql_query($sql,$conn);
//$sql = "CREATE INDEX PAIS ON ".$temptb."  (PAIS)";
//@mysql_query($sql,$conn);
//$sql = "CREATE INDEX ESTADO ON ".$temptb."  (ESTADO)";
//@mysql_query($sql,$conn);
//$sql = "CREATE INDEX MUNICIPIO ON ".$temptb."  (MUNICIPIO)";
//@mysql_query($sql,$conn);
//$sql = "CREATE INDEX LOCAL ON ".$temptb."  (LOCAL)";
//@mysql_query($sql,$conn);
//$sql = "CREATE INDEX LOCALSIMPLES ON ".$temptb."  (LOCALSIMPLES)";
//@mysql_query($sql,$conn);
//$sql = "CREATE INDEX TraitName ON ".$temptb."  (TraitName)";
//@mysql_query($sql,$conn);
//$sql = "CREATE INDEX TraitPathName ON ".$temptb."  (TraitPathName)";
//@mysql_query($sql,$conn);
//
//COMECA A PREPARACAO DO DHTMLX GRID
$headd = array(
'CensoID',
"Incluido",
"PlantaID",
"TAG",
"TAGtxt",
"FAMILIA",
"NOME",
"PAIS", 
"ESTADO", 
"MUNICIPIO", 
"LOCAL", 
"LOCALSIMPLES",
"PROJETOstr",
"MonitoramentoID",
"TraitName",
"TraitPathName",
"TraitTipo",
"TraitVariation",
"TraitDataObs",
"TraitUnit"
);

$colw = array(
'CensoID' => 0,
"Incluido" => 80,
"PlantaID" => 0,
"TAG" => 80,
"TAGtxt" => 0,
"FAMILIA" => 80,
"NOME" => 200,
"PAIS" => 40,
"ESTADO" => 60,
"MUNICIPIO" => 50,
"LOCAL" => 100,
"LOCALSIMPLES" => 90,
"PROJETOstr" => 80,
"MonitoramentoID" => 0,
"TraitName" => 80,
"TraitPathName" => 100,
"TraitTipo" => 80,
"TraitVariation" => 100,
"TraitDataObs" => 80,
"TraitUnit" => 20
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
//MUDA  COLUNA Incluido PARA EDITAVEL CHECKBOX
$coltipos[1] = 'ch';
$colalign[1]  = 'center';

//DEFINE MAIS ALGUNS ATRIBUTOS PARA O GRID
//COPIA O CABECALHO EM VISIVEL, FILTROS
$listvisible = $headd;
$filt = $headd;
$filt2 = $headd;
//DEFINE FILTRO, IMAGEM E FILTROS NUMERICOS
$nofilter = array();
$imgfields = array();


$numericfilter = array("CensoID", "Incluido","PlantaID","TAG","MonitoramentoID","MonitoramentoID");
//DEFINE COLUNAS PARA ESCONDER
if(!isset($uuid) || (trim($uuid)=='') || $acclevel=='visitor') {
	$hidefields = array("CensoID","PlantaID", "TAGtxt", "PAIS", "ESTADO", "MUNICIPIO", "LOCALSIMPLES", "PROJETOstr", "MonitoramentoID","TraitPathName","TraitUnit","TraitVariation");
	}
	else {
	$hidefields = array("CensoID", "PlantaID", "TAGtxt", "PAIS", "ESTADO", "MUNICIPIO", "LOCALSIMPLES", "PROJETOstr", "MonitoramentoID","TraitPathName","TraitUnit","TraitVariation");
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
				//$filt[$kk] = '#connector_text_filter';
				$filt[$kk] = "#text_filter";
				//$filt2[$kk] = "connector";
				$filt2[$kk] = "server";
			} else {
				$filt[$kk] = "#text_filter";
				$filt2[$kk] = "server";
			}
		} else {
				$filt[$kk] = '';
				$filt2[$kk] = "server";
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
	//$filt2[1] ='';
	//$filt3 = $colvalid;
	//$filt3[0] = '#master_checkbox';
	$hdd = implode(",",$headd);
	$ffilt = implode(",",$filt);
	$ffilt2 = implode(",",$filt2);
	//$ffilt3 = implode(",",$filt3);
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
//$fh2 = fopen("temp/".$fnn2, 'w');
//$stringData = "<?php
//session_start();
//require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");
//include \"../functions/MyPhpFunctions.php\";
//\$monitoramentoid = \$_GET['monitoramentoid'];
//\$varincluido = \$_GET['varincluido'];
//\$rr =  mysql_query(\"SELECT * FROM `Monitoramento` WHERE MonitoramentoID=\$monitoramentoid  AND CensoID=".$censoid." \");
//\$nrr = mysql_numrows(\$rr);
//if (\$nrr>0 && \$varincluido==0) {
//  mysql_query(\"UPDATE  `Monitoramento` SET `CensoID`=NULL  WHERE `MonitoramentoID`=\$monitoramentoid  AND `CensoID`=".$censoid." \");
//} elseif (\$nrr==0 && \$varincluido==1) {
//  mysql_query(\"UPDATE  `Monitoramento` SET `CensoID`=".$censoid."   WHERE `MonitoramentoID`=\$monitoramentoid\");
//}
//mysql_query(\"UPDATE  `".$temptb."` SET `Incluido`=\$varincluido  WHERE `MonitoramentoID`=\$monitoramentoid\");
//echo \"done\";
///?//>";
//fwrite($fh2, $stringData);
//fclose($fh2);

//O SQL QUE SERÁ FEITO
$sql = "SELECT moni.CensoID, 0 as Incluido, pltb.PlantaID,pltb.TAG,pltb.TAGtxt,pltb.FAMILIA,pltb.NOME,pltb.PAIS,pltb.ESTADO,pltb.MUNICIPIO,pltb.LOCAL,pltb.LOCALSIMPLES,pltb.PROJETOstr,moni.MonitoramentoID,tr.TraitName,tr.PathName as TraitPathName,TRIM(REPLACE(tr.TraitTipo,'Variavel|','')) AS TraitTipo,moni.TraitVariation,moni.DataObs as TraitDataObs,moni.TraitUnit FROM `Monitoramento` AS moni LEFT JOIN checklist_pllist AS pltb ON moni.PlantaID=pltb.PlantaID LEFT JOIN Traits AS tr ON moni.TraitID=tr.TraitID WHERE ((moni.CensoID IS NULL) OR (moni.CensoID='".$censoid."')) ";


$fh = fopen("temp/".$fnn, 'w');
$stringData = "<?php
session_start();
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");
include \"../functions/MyPhpFunctions.php\";
\$uuid = \$_SESSION['userid'];
\$sql = \"".$sql."\";
\$colnforfilter = array( \"moni.CensoID\", \"moni.CensoID\", \"pltb.PlantaID\", \"pltb.TAG\", \"pltb.TAGtxt\", \"pltb.FAMILIA\", \"pltb.NOME\", \"pltb.PAIS\", \"pltb.ESTADO\", \"pltb.MUNICIPIO\", \"pltb.LOCAL\", \"pltb.LOCALSIMPLES\", \"pltb.PROJETOstr\", \"moni.MonitoramentoID\", \"tr.TraitName\", \"tr.PathName\", \"tr.TraitTipo\", \"moni.TraitVariation\", \"moni.DataObs\", \"moni.TraitUnit \");
\$columns = array(";
$ii = 0;
foreach($headd as $hh) {
if ($ii==0) {
	$stringData .= "\"".$hh."\"";
} else {
	$stringData .= ", \"".$hh."\"";
}
$ii++;
}
$stringData .= ");
if(isset(\$_GET[\"filtrando\"])){
\$ff = \$_GET;
unset(\$ff['filtrando']);
\$nc = count(\$ff);
if (\$nc>0) {
   foreach(\$ff as \$kk => \$vv) {
      \$idx = str_replace(\"colidx_\",\"\",\$kk);
      \$idx = \$idx+0;
      \$cln = trim(\$colnforfilter[\$idx]);
      if (substr(\$vv,0,1)=='>') {
          \$filter_by = \$vv;
          \$cll = \" (\".\$cln .\"+0)\";
      } else {
        if (substr(\$vv,0,1)=='<') {
            \$filter_by = \$vv;
            \$cll = \" (\".\$cln.\"+0)\";
        } else {
          if (substr(\$vv,0,1)=='=') {
              \$v1 = trim(str_replace(\"=\",\"\",\$vv));
              \$v1 = \$v1+0;
              if (\$v1>0) {
                \$filter_by = \$vv;
                \$cll = \" (\".\$cln.\"+0)\";
              } else {
                \$v2 = trim(str_replace(\"=\",\"\",\$vv));
                \$filter_by = \"=LOWER('\".\$v2.\"')\";
                \$cll = \" LOWER(\".\$cln.\")\";
              }
          } else {
              if (substr(\$vv,0,1)=='!') {
                 \$condicao = ' NOT LIKE ';
                 \$vv = trim(str_replace(\"!\",\"\",\$vv));
              } else {
                 \$condicao = '  LIKE ';
              }
              \$vv = strtolower(\$vv);
              \$filter_by = \$condicao.\" '%\".\$vv.\"%' \";
              \$cll = \" LOWER(\".\$cln.\")\";
          }
        }
    }
    \$sql .=  \" AND \".\$cll.\$filter_by;
   }
}
}
if(isset(\$_GET[\"orderby\"])){
     if(\$_GET[\"direct\"]=='des') {
          \$direct = \"DESC\";
     }
     else   {
          \$direct = \"ASC\";
     }
     \$sql.= \" ORDER BY \".\$columns[\$_GET[\"orderby\"]].\" \".\$direct;
} else {
  \$sql .= \" ORDER BY (moni.CensoID+0) DESC\";
}

function myUpdate(\$action,\$res){
         \$varincluido = \$action->get_value('Incluido');
         \$monitoramentoid = \$action->get_id();
         \$rr =  mysql_query(\"SELECT * FROM `Monitoramento` WHERE MonitoramentoID=\$monitoramentoid  AND CensoID=".$censoid." \");
         \$nrr = mysql_numrows(\$rr);
         if (\$nrr>0 && \$varincluido==0) {
            mysql_query(\"UPDATE  `Monitoramento` SET `CensoID`=NULL  WHERE `MonitoramentoID`=\$monitoramentoid  AND `CensoID`=".$censoid." \");
        } elseif (\$nrr==0 && \$varincluido==1) {
            mysql_query(\"UPDATE  `Monitoramento` SET `CensoID`=".$censoid."   WHERE `MonitoramentoID`=\$monitoramentoid\");
        }
        \$action->success();
}
function custom_format_spec(\$data){
    \$ccid = (\$data->get_value(\"CensoID\"))+0;
    if (\$ccid==".$censoid.") {
        \$data->set_value(\"Incluido\",1);
    }
}
function removeoperators(\$data){
  \$val = str_replace('>','',\$data);
  \$val = str_replace('<','',\$val);
  \$val = str_replace('=','',\$val);  
  \$val = str_replace('!','',\$val);  
  return \$val+0;
}
\$grid = new GridConnector(\$res);
\$grid ->event->attach(\"beforeRender\",\"custom_format_spec\");
\$grid ->dynamic_loading(500);
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid ->render_sql(\$sql,\"MonitoramentoID\",\"".$hdd."\");
?>";
//\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
//\$grid ->render_sql(\"SELECT * FROM `".$temptb."`\",\"MonitoramentoID\",\"".$hdd."\");
//\$grid ->event->attach(\"beforeRender\",\"custom_format_spec\");
//\$grid ->render_table(\"".$tbname."\",\"BiblioRefs\",\"".$hdd."\");
//\$grid->set_options(\"Fert\",".$options.");

//function custom_filter(\$filter_by){
//";
//if (count($numericfilter)>0) {
//$i=1;
//$idxx = '
//$idxss = array('; 
//foreach ($numericfilter as $nuvar) {
//	$stringData .= "
//    \$index".$i." = \$filter_by->index('".$nuvar."');";
//	if ($i==1) {
//    	$idxx .= "\$index".$i;
//	} else {
//    	$idxx .= ",\$index".$i;
//	}
//	$i++;
//}
//$idxx .= ");";
//	$stringData .= $idxx;
//} else {
//	$stringData .= '\$idxss = array();';
//}
//$stringData .= "
//   foreach (\$idxss as \$idx) {
//		if (\$idx!==false) {
//			\$vv =  \$filter_by->rules[\$idx][\"value\"];
//			if (substr(\$vv,0,2)=='!=') {
//				\$filter_by->rules[\$idx][\"operation\"]=\"!=\";
//			} else {
//
//			if (substr(\$vv,0,1)=='>') {
//				\$filter_by->rules[\$idx][\"operation\"]=\">\";
//			}
//			if (substr(\$vv,0,1)=='<') {
//				\$filter_by->rules[\$idx][\"operation\"]=\"<\";
//			}
//			if (substr(\$vv,0,1)=='=') {
//				\$filter_by->rules[\$idx][\"operation\"]=\"=\";
//			}
//			}
//			\$filter_by->rules[\$idx][\"value\"] = removeoperators(\$filter_by->rules[\$idx][\"value\"]);
//		}
//	}
//}

fwrite($fh, $stringData);
fclose($fh);

//echo $colidx;
//\
//attach("beforeProcessing","custom_fields");
$arrofpass = array(
'ffields'   => $hdd,
'filtros'  => $ffilt,   
'filtros2'  => $ffilt2,   
//'filtros3' => $ffilt3,
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
'censoid' => $censoid,
'temptb' => $temptb
);
$_SESSION['arrtopass'] = serialize($arrofpass);
echo "
<form name='myform' action='censo-plantas-table-grid.php' method='post'>";
  foreach ($arrofpass as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
 }
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script></form>";
//echo 'CONCLUIDO';
} 
else {
echo "Tá faltando o CensoID. Não foi definido";
}


?>