<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//FUNCION LIMPA NOME
function limpanome($string) { 
	$theval = RemoveAcentos($string);
	$theval = str_replace("(","",$theval);
	$theval = str_replace(")","",$theval);
	$theval = str_replace(",","",$theval);
	$theval = str_replace(".","",$theval);
	$theval = str_replace("_"," ",$theval);
	$theval = str_replace("-"," ",$theval);
	$theval = str_replace("  "," ",$theval);
	$theval = str_replace("  "," ",$theval);
	$theval = str_replace("  "," ",$theval);
	$artheval = explode(" ",$theval);
	$novo = "";
	foreach($artheval as $aa) {
		$aa = trim($aa);
		if (strlen($aa)>2) {
			$aa = ucfirst(strtolower($aa));
			$novo .= $aa;
		}
	}
	return($novo);
}


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

//echopre($ppost);
//CABECALHO
$menu = FALSE;

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'Opções de Formulario ODK';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$qq = "SELECT * FROM ODKforms WHERE ODKformid=".$odkformid;
//echo $qq."<br />";
$res = mysql_query($qq,$conn);
$row =  mysql_fetch_assoc($res);
$defs= unserialize($row['Definitions']);
//echopre($defs);
$versao = str_replace("-","",$row['AddedDate']);
$instancecode = "duckewiki".$odkformid.$row["AddedBy"];
$formname = $row["FormName"];
//$formlimpo = limpanome($formname);
$odkxmlformid = "duckewiki-".$odkformid;
$odkformversion = $versao;

//PEGA OS COLETORES A INSERIR
$cols = explode(";",$defs['addcolvalue']);
$i=0;
$addcols = array();
$colvals = array();
$collabs = array();
foreach($cols as $kk => $vv) {
 $qt = "SELECT * FROM Pessoas WHERE PessoaID=".$vv;
 $rz = mysql_query($qt,$conn);
 $rzw = mysql_fetch_assoc($rz);
 $theval = RemoveAcentos($rzw["Abreviacao"]);
 $theval = str_replace(" ","",$theval);
 $theval = str_replace(",","_",$theval);
 $theval = str_replace(".","",$theval);
 if ($i==0) {
	$collector_def = $theval;	
 } else {
    $addcols[] = $theval;
 }
 $i++;
 $colvals[] =  $theval;
 $collabs[] = $rzw['Abreviacao'];
}
$collectors = array_combine($colvals,$collabs);
$addcoll_def = implode(" ",$addcols);

$coordenadas_cell = $defs["coordenadas_cell"]+0;
$coordenadas_gps = $defs["coordenadas_gps"]+0;
$gpsids = $defs["gpsunits"];
$gpsdefid = $defs["gpsunit_def"]+0;
$gpsids[]= $gpsdefid;
$gpsvals = array();
$gpslabs = array();
$nomegpsdef = "";
foreach($gpsids as $kk => $vv) {
 $qq = "SELECT * FROM Equipamentos WHERE EquipamentoID=".$vv;
 $res = mysql_query($qq,$conn);
 $row =  mysql_fetch_assoc($res);
 $valn = "gpsunit".$row['EquipamentoID'];
 $labn = $row['Name'];
 if (($gpsdefid+0)==($vv+0)) {
   $nomegpsdef = $valn;
 }
 $gpsvals[] = $valn;
 $gpslabs[] = $labn;
}
$gpsunits = array_combine($gpsvals,$gpslabs);
$addhabitatclass  = $defs["addhabitatclass"]+0;
if ($addhabitatclass==1) {
	$qhab = "SELECT * FROM Habitat WHERE HabitatTipo LIKE 'Class' ORDER BY PathName";
	$rh = mysql_query($qhab,$conn);
	$hablab = array();
	$habval = array();
        while($rhw = mysql_fetch_assoc($rh)) {
		$hablab[] = $rhw["PathName"];
		$habval[] = "habitat".$rhw["HabitatID"];
	}
	$habitatclass = array_combine($habval,$hablab);
}

$imgids = $defs["varimgsids"];
$qhab = "SELECT * FROM Traits WHERE TraitTipo LIKE '%imag%' ORDER BY TraitName";
$rh = mysql_query($qhab,$conn);
$imglabs = array();
$imgvals = array();
while($rhw = mysql_fetch_assoc($rh)) {
 if (in_array($rhw["TraitID"],$imgids)) {
  $imglabs[] = $rhw["TraitName"];
  $imgvals[] = "imagem".$rhw["TraitID"];
 }
}
if (count($imgvals)>0) {
  $varimgs = array_combine($imgvals,$imglabs);
}

$formids  = explode(";",$defs["varvalues"]);
$varids = array();
foreach($formids as $ffid) {
	$qt = "SELECT TraitID FROM `FormulariosTraitsList`  WHERE FormID=".$ffid." ORDER BY Ordem";
	$rqt = mysql_query($qt,$conn);
	while($rqtw = mysql_fetch_assoc($rqt)) {
		$varids[]= $rqtw['TraitID'];
	}
}
//$varids = explode(";",$defs["varvalues"]);
$varlabs = array();
$varvals = array();
$vartipo = array();
$varcateg = array();

foreach($varids as $vv) {
 $qhab = "SELECT * FROM Traits WHERE TraitID=".($vv+0);
 $rh = mysql_query($qhab,$conn);
 $rhw = mysql_fetch_assoc($rh);
 $ismulti = $rhw["MultiSelect"];
 $unit =  $rhw["TraitUnit"];
 if (!empty($unit)) {
   $varlabs[] = $rhw["TraitName"]." (".$unit.")";
 } else {
   $varlabs[] = $rhw["TraitName"];
 }
 $nomelimpo = limpanome($rhw["TraitName"]);
 $varvals[] = "trait".$rhw["TraitID"]."_".$nomelimpo;
 $theid = "trait".$rhw["TraitID"]."_".$nomelimpo; 
 $tp = explode("|",$rhw["TraitTipo"]);
 $tp = $tp[1];
 if ($tp=='Categoria') {
  $qu = "SELECT * FROM `Traits` WHERE `ParentID`='".$rhw['TraitID']."'";
  $ru = mysql_query($qu,$conn);
  $estadosval = array();
  $estadoslab = array();
  while ($rwu = mysql_fetch_assoc($ru)) {
    $estadoslab[] = $rwu["TraitName"];
    $subnomelimpo = limpanome($rwu["TraitName"]);
    $estadosval[] =  "subtrait".$rwu["TraitID"]."_".$subnomelimpo;
  }
  $osestados = array_combine($estadosval,$estadoslab);
  if ($ismulti=="Sim") {
   $vartipo[$theid] = "select"; 
  } else {
   $vartipo[$theid] = "select1"; 
  }
 } else {
     $osestados = array();
     if ($tp=="Quantitativo") {	
       $vartipo[$theid] = "decimal";
     } else {
       $vartipo[$theid] = "string";
     }
 }
 $varcateg[$theid] = $osestados; 
}
$uservariables =  array_combine($varvals,$varlabs);	
$uservariables_tipo =$vartipo; 
$uservariables_cat = $varcateg;

if ($lixo==678594) {
echo "<br >formname ".$formname;

echo "<br >instancecode ".$instancecode;
echo "<br >odkxmlformid ".$odkxmlformid;
echo "<br >odkformversion ".$odkformversion;
echo "<br >collector_def".$collector_def ;
echo "<br >addcoll_def ".$addcoll_def;
echo "<br >collectors";
echopre($collectors);
echo "<br >coordenadas_cell ".$coordenadas_cell;
echo "<br >coordenadas_gps ".$coordenadas_gps;
echo "<br >nomegps_def ".$nomegps_def;
echo "<br >addhabitatclass ".$addhabitatclass;
echo "<br >USERVARIABLES";
echopre($uservariables);
echo "<br >uservariables_tipo";
echopre($uservariables_tipo);
echo "<br >uservariables_cat";
echopre($uservariables_cat);
echo "<br >habitatclass";
echopre($habitatclass);
echo "<br >varimgs";
echopre($varimgs);
echo "<br >gpsunits";
echopre($gpsunits);
}

include("odkcollect_template.php");
$export_filename = $odkxmlformid.".xml";
$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
fwrite($fh, $txt);
fclose($fh);

echo "<br />";
echo "<p ><a href='temp/".$export_filename."' download >Baixar o arquivo ".$export_filename."!</a></p>
<br />
";
echo "
<form action='odkcollect_inicio.php' method='post' >
<input type='hidden'  value=".$odkformid." name='odkformid' >
<input type='submit' value='Voltar' style=\"color:#4E889C; font-size: 0.8em; padding: 4px; cursor:pointer;\"  />
</form>";

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
