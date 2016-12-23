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


//apaga arquivo de progresso
$qqz = "DROP TABLE `temp_".$tbname."`";
@mysql_query($qqz,$conn);


//SAVE RESULTS IN A FILE
$numericfilters = array();
$colalign = array();
$colw = array();
$headd = array();
$coltipos = array();
$filtroarr = array();
$colsort = array();
$listvisible = array();
$hidemenu = array();
$collist = array();
$colvalid = array();
$colvalidtorf = array();
//$selectboxes = array();
$variablestoupdate = array();


$headd[] = "Marcado";
$coltipos[] = 'ch';
$colw[]  = 75;
$colalign[] = 'center';
$filtroarr[] =  '#connector_text_filter';
$hidemenu[] = 'false';
$listvisible[] = 'false';
$colsort[] = 'connector';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$iddx = 1;
$collist[] = $iddx;
$iddx++;
$numericfilters[] = "Marcado";
$variablestoupdate["Marcado"] = "Marcado";


$headd[] = "EDIT";
$coltipos[] = 'ro';
$colw[]  = 120;
$colalign[] = 'center';
$filtroarr[] = '';
$hidemenu[] = 'false';
$listvisible[] = 'false';
$colsort[] = 'connector';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$collist[] = $iddx;
$iddx++;

$headd[] = "TraitID"; 
$coltipos[] = 'ro';
$colw[]  = 0;
$colalign[] = 'right';
$filtroarr[] = '';
$colsort[] = 'connector';
$listvisible[] = 'true';
$hidemenu[] = 'false';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';
$numericfilters[] = "TraitID";


$headd[] = "ParentID"; 
$coltipos[] = 'ro';
$colw[]  = 0;
$colalign[] = 'right';
$filtroarr[] = '';
$colsort[] = 'connector';
$listvisible[] = 'true';
$hidemenu[] = 'false';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';
$numericfilters[] = "ParentID";


$headd[] = "TraitTipo"; 
$coltipos[] = 'rotxt';
$colw[]  = 0;
$colalign[] = 'left';
$filtroarr[] =  '#connector_text_filter';
$colsort[] = 'connector';
$listvisible[] = 'false';
$hidemenu[] = 'false';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';

$headd[] = "CLASSE";
$coltipos[] = 'co';
$colw[]  = 120;
$colalign[] = 'left';
$filtroarr[] =  '#connector_select_filter';
$hidemenu[] = 'false';
$listvisible[] = 'false';
$colsort[] = 'connector';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$collist[] = $iddx;
$iddx++;
$variablestoupdate["CLASSE"] = "ParentID";

//$qst = "SELECT GROUP_CONCAT(DISTINCT TRIM(TraitName) ORDER BY TraitName SEPARATOR ',')  as vars FROM Traits  WHERE TraitTipo='Classe'";
//$resst = mysql_query($qst,$conn);
//$rowst = mysql_fetch_assoc($resst);
//$selectboxes["CLASSE"] = $rowst['vars'];



$headd[] = "VARIAVEL";
$coltipos[] = 'ed';
$colw[]  = 120;
$colalign[] = 'left';
$filtroarr[] = '#connector_text_filter';
$colsort[] = 'connector';
$listvisible[] = 'false';
$hidemenu[] = 'false';
$collist[] = $iddx;
$colvalid[] = '';
$colvalidtorf[] = 'false';
$iddx++;
$variablestoupdate["VARIAVEL"] = "TraitName";


$headd[] = "VARIAVEL_ENG";
$coltipos[] = 'ed';
$colw[]  = 120;
$colalign[] = 'left';
$filtroarr[] = '#connector_text_filter';
$colsort[] = 'connector';
$listvisible[] = 'false';
$hidemenu[] = 'false';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';
$variablestoupdate["VARIAVEL_ENG"] = "TraitName_English";

$headd[] = "DEFINICAO";
$coltipos[] = 'txttxt';
$colw[]  = 200;
$colalign[] = 'left';
$filtroarr[] = '#connector_text_filter';
$colsort[] = 'connector';
$listvisible[] = 'false';
$hidemenu[] = 'false';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';
$variablestoupdate["DEFINICAO"] = "TraitDefinicao";

$headd[] = "DEFINICAO_ENG";
$coltipos[] = 'txttxt';
$colw[]  = 200;
$colalign[] = 'left';
$filtroarr[] = '#connector_text_filter';
$colsort[] = 'connector';
$listvisible[] = 'false';
$hidemenu[] = 'false';
$collist[] = $iddx;
$iddx++;
$colvalid[] = '';
$colvalidtorf[] = 'false';
$variablestoupdate["DEFINICAO_ENG"] = "TraitDefinicao_English";

//$qu = "SELECT GROUP_CONCAT(DISTINCT TRIM(Traits.TraitUnit) ORDER BY Traits.TraitUnit SEPARATOR ',')  as unit FROM Traits WHERE TRIM(Traits.TraitUnit)<>'' AND Traits.TraitUnit IS NOT NULL ";
//$resu = mysql_query($qu,$conn);
//$rowu = mysql_fetch_assoc($resu);
//$units = $rowu['unit'];
//$selectboxes["UNIDADE"] = $units;


$headd[] = "UNIDADE";
$coltipos[] = 'co';
$colw[]  = 80;
$colalign[]  = 'center';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$filtroarr[] = '#connector_text_filter';
$colsort[] = 'connector';
$listvisible[] = 'false';
$hidemenu[] = 'false';
$collist[] = $iddx;
$iddx++;
$variablestoupdate["UNIDADE"] = "TraitUnit";

$headd[] = "CATEGORIAS";
$coltipos[] = 'ro';
$colw[]  = 100;
$colalign[] = 'center';
$filtroarr[] = '';
$hidemenu[] = 'false';
$listvisible[] = 'false';
$colsort[] = 'connector';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$collist[] = $iddx;
$iddx++;

$headd[] = "IMAGEM";
$coltipos[] = 'ro';
$colw[]  = 80;
$colalign[] = 'center';
$filtroarr[] = '';
$hidemenu[] = 'false';
$listvisible[] = 'false';
$colsort[] = 'connector';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$collist[] = $iddx;
$iddx++;

$headd[] = "TAXA";
$coltipos[] = 'ro';
$colw[]  = 150;
$colalign[] = 'left';
$filtroarr[] = '#connector_text_filter';
$hidemenu[] = 'false';
$listvisible[] = 'false';
$colsort[] = 'connector';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$collist[] = $iddx;
$iddx++;

$headd[] = "MultiSelect";
$coltipos[] = 'ch';
$colw[]  = 80;
$colalign[] = 'center';
$filtroarr[] =  '#connector_text_filter';
$hidemenu[] = 'false';
$listvisible[] = 'false';
$colsort[] = 'connector';
$colvalid[] = '';
$colvalidtorf[] = 'false';
$collist[] = $iddx;
$iddx++;
$numericfilters[] = "MultiSelect";
$variablestoupdate["MultiSelect"] = "MultiSelect";


$hdd = implode(",",$headd);
$ffilt = implode(",",$filtroarr);
$ffilt2 = implode(",",$colsort);
$collist = implode(",",$collist);
$colw = implode(",",$colw);
$coltipos = implode(",",$coltipos);
$listvisible = implode(",",$listvisible);
$colalign = implode(",",$colalign);
$hidemenu = implode(",",$hidemenu);
$colvalid = implode(",",$colvalid);
$colvalidtorf = implode(",",$colvalidtorf);

$qq = "SELECT count(*) as nrecs FROM ".$tbname;
$rr = @mysql_query($qq,$conn);
$row = @mysql_fetch_assoc($rr);
$nrecs = $row['nrecs'];



$url = $_SERVER['HTTP_REFERER'];
$uu = explode("/",$url);
$nu = count($uu)-1;
unset($uu[$nu]);
$url = implode("/",$uu);

////CREATE TEMPORARY TABLE FOR TRAIT CLASSES
$qq = "DROP TABLE `temp_traitsclasses_".$uuid."`";
@mysql_query($qq,$conn);
$qq = "CREATE TABLE `temp_traitsclasses_".$uuid."` SELECT tbb.TraitID, TRIM(tbb.TraitName) as TraitName FROM (SELECT tr.TraitID, CONCAT(IF(tr2.TraitName IS NOT NULL,tr2.TraitName,''),' ',tr.TraitName) as TraitName FROM Traits as tr LEFT JOIN Traits as tr2 ON tr.ParentID=tr2.TraitID WHERE tr.TraitTipo='Classe'  ORDER BY CONCAT(tr2.TraitName,tr.TraitName)) AS tbb";
@mysql_query($qq,$conn);
$qq = "ALTER TABLE `temp_traitsclasses_".$uuid."` ADD PRIMARY KEY(TraitID)";
@mysql_query($qq,$conn);


//////SALVA ARQUIVO PHP PARA SER LIDO PELO GRID
$fnn = $tbname.".php";
$fh = fopen("temp/".$fnn, 'w');
//include \"../functions/MyPhpFunctions.php\";
//session_start();
$stringData = "<?php
require_once(\"../dhtmlxconnector/dhtmlxConnector_php/codebase/grid_connector.php\");
require_once(\"../".$relativepathtoroot.$databaseconnection_clean."\");
";
$stringData .= "
function myUpdate(\$action){
\$id = \$action->get_id();";
foreach ($variablestoupdate as $kk => $vv) {
$stringData .= "
\$colvalue = \$action->get_value('".$kk."');
\$colname = '".$vv."';
\$skip=0;
";
if ($kk=='UNIDADE') {
$stringData .= "
\$tipo = \$action->get_value('TraitTipo');
if (\$tipo!='Variavel|Quantitativo') {
  \$skip =1;
}";
}
if ($kk=='MultiSelect') {
$stringData .= "
\$msel = \$action->get_value('MultiSelect');
\$tipo = \$action->get_value('TraitTipo');
if (\$tipo=='Variavel|Categoria') {
  if (\$msel==1) {
    \$colvalue='Sim';
  } elseif (\$colvalue==0) {
    \$colvalue='Nao';
  }
} else {
   \$skip=1;
}";
} 
if ($kk=='Marcado') {
$stringData .= "
mysql_query(\"UPDATE `".$tbname."` SET `".$kk."`='{\$action->get_value('".$kk."')}' WHERE TraitID='{\$action->get_id()}'\");
";
} else {
$stringData .= "
if (!empty(\$colvalue) && \$skip==0) {
mysql_query(\"UPDATE `".$tbname."` SET `".$kk."`='{\$action->get_value('".$kk."')}' WHERE TraitID='{\$action->get_id()}'\");
\$rt = mysql_query(\"SELECT `\$colname` FROM Traits WHERE TraitID='{\$action->get_id()}'\");
\$rtw = mysql_fetch_assoc(\$rt);
if (\$rtw[\$colname]!=\$colvalue) {
\$rtt = mysql_query(\"SELECT GROUP_CONCAT(COLUMN_NAME SEPARATOR ', ') AS tt FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name='Traits'\");
\$rttw = mysql_fetch_assoc(\$rtt);
\$rch  = mysql_query(\"INSERT INTO ChangeTraits (\".\$rttw['tt'].\", ChangedBy,ChangedDate) SELECT Traits.*, '".$uuid."' as ChangedBy, CURDATE() as ChangedDate FROM Traits WHERE TraitID='{\$action->get_id()}'\");
if (\$rch) {
mysql_query(\"UPDATE Traits SET `\".\$colname.\"`='\".\$colvalue.\"', AddedBy='".$uuid."', AddedDate=CURDATE() WHERE TraitID='{\$action->get_id()}'\");
}
}
}
";
}
}
$stringData .= "
        \$action->success();
}";
$stringData .= "
function custom_format(\$data){
\$imagen= \"<img style='cursor:pointer;' src='icons/edit-icon.png' height='20' onclick=\\\"javascript:small_window('".$url."/traitsvar-exec.php?ispopup=1&traitid=\".\$data->get_id().\"&traitkind=Variavel',800,500,'Editando a variável');\\\" onmouseover=\\\"Tip('Editar o registro da variável');\\\" title=''>\";

\$trname = \$data->get_value(\"VARIAVEL\");
\$trtipo = \$data->get_value(\"TraitTipo\");


if (\$trtipo=='Variavel|Categoria') {
\$imgtr= \"<img style='cursor:pointer;' src='icons/categories.png' height='20' onclick=\\\"javascript:small_window('".$url."/traits_definition_states_script.php?traitid=\".\$data->get_id().\"',900,500,'Editando os estados de variação da variável variável');\\\" onmouseover=\\\"Tip('Editar CATEGORIAS/CLASSES DA VARIÁVEL \".\$trname.\" ');\\\" title=''>\";
\$data->set_value(\"CATEGORIAS\",\$imgtr);
} else {
\$imgtr = '';
}";
$idxtaxa = array_search('TAXA',$headd);
$stringData .= "
\$oldtxt = \$data->get_value(\"TAXA\");
\$imgtr2= \"<img style='cursor:pointer;' src='icons/diversity.png' height='20' onclick=\\\"javascript:    small_window('traits_definition_taxonomy.php?rowid=\".\$data->get_index().\"&traitid=\".\$data->get_id().\"&clidx=".$idxtaxa."',800,500,'Indicando taxa associados com a variável');\\\" onmouseover=\\\"Tip('Associar TAXA com a variável \".\$trname.\" ');\\\" title=''>\";
\$oldtxt = \$oldtxt.'<br />'.\$imgtr2;
\$data->set_value(\"TAXA\",\$oldtxt);

\$imgname = \$data->get_value(\"IMAGEM\");
if (!empty(\$imgname)) {
\$img = \"img/traits_icons/\".\$imgname;
\$imgtr3= \"<img style='cursor:pointer;' src='\".\$img.\"' height='60' onclick=\\\"javascript:small_window('".$url."/traitsvar-exec.php?ispopup=1&traitid=\".\$data->get_id().\"',800,500,'Editando a variável');\\\" onmouseover=\\\"Tip('Editar o registro da variável \".\$trname.\" ');\\\" title=''>\";

} else {
\$imgtr3 = ' ';
}
\$data->set_value(\"IMAGEM\",\$imgtr3);

\$imagen = \$imagen.\$imgtr.\$imgtr2;
\$data->set_value(\"EDIT\",\$imagen);

}
";
//DEFINE COLUNAS NUMERICAS E FILTRO CONSIDERANDO OPERADORES
$stringData .= "
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


//////CONECTA O GRID AOS DADOS USANDO MYSQL E APLICANDO OS FORMATOS DEFINIDOS
$stringData .= "
\$grid = new GridConnector(\$res);
\$grid ->event->attach(\"beforeRender\",\"custom_format\");
\$grid ->event->attach(\"beforeFilter\",\"custom_filter\");
\$grid ->dynamic_loading(".$nrecs.");
";

////////////////DEFINE OPTIONS FOR COLUNA CLASSES
$stringData .= "
\$options = new OptionsConnector(\$res);
\$options->render_sql(\"SELECT TraitID AS value, TraitName as label FROM `temp_traitsclasses_".$uuid."` ORDER BY TraitName ASC\",\"TraitID\",\"TraitID(value),TraitName(label)\");
\$grid->set_options(\"CLASSE\",\$options);
 ";
 
 

 
 
 
 
 
//$qu = "SELECT GROUP_CONCAT(DISTINCT TRIM(Traits.TraitUnit) ORDER BY Traits.TraitUnit SEPARATOR ',')  as unit FROM Traits WHERE TRIM(Traits.TraitUnit)<>'' AND Traits.TraitUnit IS NOT NULL ";
//$resu = mysql_query($qu,$conn);
//$rowu = mysql_fetch_assoc($resu);
//$units = $rowu['unit'];
//$selectboxes["UNIDADE"] = $units;

////////////////DEFINE OPTIONS FOR COLUNA UNIDADE
$stringData .= "
\$options2 = new OptionsConnector(\$res);
\$options2->render_sql(\"SELECT DISTINCT TraitUnit as value, TraitUnit as label FROM Traits WHERE TraitTipo='Variavel|Quantitativo'  AND TraitUnit IS NOT NULL AND TRIM(TraitUnit)<>'' ORDER BY TraitUnit\",\"TraitID\",\"TraitUnit(value),TraitUnit(label)\");
\$grid->set_options(\"UNIDADE\",\$options2);
 ";
 //if (count($selectboxes)>0) {
//foreach ($selectboxes as $kk => $vv) {
//	$stringData .= "
//\$grid->set_options(\"".$kk."\",array(";
//	$i = 0;
//	$stvals = explode(",",$vv);
//	//$stvals = array_unique($stvals);
//	foreach ($stvals as $kk => $vv) {
//		if ($i==0) {
//			$stringData .= "'".$vv."' => '".$vv."'";
//		} else {
//			$stringData .= ", '".$vv."' =>  '".$vv."'";
//		}
//		$i++;
//	}
//	$stringData .= "));
//";
//	}
//}


/////EXECUTA O GRID
$stringData .= "
\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
\$grid ->render_table(\"".$tbname."\",\"TraitID\",\"".$hdd."\");
?>";

//\$grid->set_encoding(\"utf8\");
//echo \"<?xml version='1.0' encoding='UTF-8'\";
//\$grid->set_encoding(\"iso-8859-1\");
//
//\$grid->event->attach(\"beforeUpdate\",\"myUpdate\");
//\$grid->set_options(\"Fert\",".$options.");
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
'colalign' => $colalign,
'colvalid' => $colvalid,
'colvalidtorf' => $colvalidtorf,
'hidemenu' => $hidemenu,
'nrecs' => $nrecs,
'ispopup'  => $ispopup,   
'tbname' => $tbname,
'fname' => $fnn,
'sampletype' => $sampletype,
'formid' => $formid,
'trclasses' => count($traitclasses)
);

$_SESSION['arrtopass'] = serialize($arrofpass);
echo "
  <form name='myform' action='traits_definition_view.php' method='post'>";
  foreach ($arrofpass as $kk => $vv) {
echo "
    <input type='hidden' name='".$kk."' value='".$vv."'>";
  }
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>";
echo "</form>";

?>