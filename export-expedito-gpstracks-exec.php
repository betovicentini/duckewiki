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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
if (!isset($prepared)) {
	$todoexpeditosids = unserialize($_SESSION['originalexpeditosids']);
	$createfile = 1;
	$nofptsoriginal = count($todoexpeditosids);
	$nofpts = $nofptsoriginal-1;
} else {
	$todoexpeditosids = unserialize($_SESSION['expeditosids']);
	$createfile = 0;
	$nofpts = count($todoexpeditosids)-1;
}
$tod = $todoexpeditosids[0];
unset($todoexpeditosids[0]);
$todoexpeditosids = array_values($todoexpeditosids);

$export_filename = "exportExpeditoGPSdata_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
if ($createfile==1) {
	$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
	$header = array("WikiExpeditoID","DATA_LEVANTAMENTO","MINORAREA","LOCALIDADE","LOCALIDADE_ESPECIFICA","LEVANTAMENTO_START","LEVANTAMENTO_END","OBSERVADOR","INTERVALOTEMPO","NOME_GPS","TIPO_GPSREC","TIME_GPSREC","LONGITUDE_GPSREC","LATITUDE_GPSREC","ALTITUDE_GPSREC","ARQUIVO_GPSRECS");
	$hh = implode("\t",$header);
	$header = $hh."\n";
	fwrite($fh, $header);
} else {
	$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
}


//selecionando dados do ponto sendo exportado
$qq = "SELECT MetodoExpedito.ExpeditoID as WikiExpeditoID, MetodoExpedito.DataColeta as DATA_LEVANTAMENTO";
//$qq .= ", IF(GPSPointID>0,gpsmuni.Municipio,'') as MINORAREA, gpsgaz.PathName as LOCALIDADE, IF(GPSPointID>0,CONCAT(gpsgaz.GazetteerTIPOtxt,' ',gpsgaz.Gazetteer),'') as LOCALIDADE_ESPECIFICA,
$qq .= ", IF(GPSPointID>0,gpsmuni.Municipio,'') as MINORAREA, gpsgaz.PathName as LOCALIDADE, IF(GPSPointID>0,gpsgaz.Gazetteer,'') as LOCALIDADE_ESPECIFICA,
addcolldescr(PessoasIDs) as observadores, MetodoExpedito.Time_Starts, MetodoExpedito.Time_Ends, GPSunitNames  FROM MetodoExpedito LEFT JOIN GPS_DATA as gps ON gps.PointID=MetodoExpedito.GPSPointID LEFT JOIN Gazetteer as gpsgaz ON gps.GazetteerID=gpsgaz.GazetteerID LEFT JOIN Municipio as gpsmuni ON gpsmuni.MunicipioID=gpsgaz.MunicipioID WHERE MetodoExpedito.ExpeditoID='".$tod."'";
$res = mysql_query($qq, $conn);
$pointvars = mysql_fetch_assoc($res);


$obss = explode(";",$pointvars['observadores']);

$h1 =  strtotime($pointvars['Time_Starts']);
$convert = strtotime("-30 seconds", $h1);
$tini = date('H:i:s', $convert);

$h2 =  strtotime($pointvars['Time_Ends']);
$convert2 = strtotime("+30 seconds", $h2);
$tfim = date('H:i:s', $convert2);

$intervals = array(0,1,2,3);
$tn = $h1;
$tn2 = $h2;
$inn = array();
foreach ($intervals as $vv) {
	if ($vv==3) {
		$step= $tn2;
	} else {
		$step = strtotime("+15 minutes", $tn);
	}
	$inn[$vv] = array(date('H:i:s', $tn),date('H:i:s', $step));
	$tn = $step;
}

$ptvars = $pointvars;
unset($ptvars['observadores'], $ptvars['GPSunitNames']);		
$gpss = explode(";",$pointvars['GPSunitNames']);
foreach ($gpss as $kgp => $gps) {
	$obs = $obss[$kgp];
	foreach ($inn as $kkinn => $interv) {
		$ttn = $pointvars['DATA_LEVANTAMENTO'].' '.$interv[0];
		$ttn2 =$pointvars['DATA_LEVANTAMENTO'].' '.$interv[1];
		$qq = "SELECT equip.Name as NOME_GPS,GPS_DATA.Type,GPS_DATA.TimeOriginal,GPS_DATA.Longitude,GPS_DATA.Latitude,GPS_DATA.Altitude,GPS_DATA.FileName FROM `GPS_DATA` JOIN Equipamentos as equip ON `GPSName`=`EquipamentoID` WHERE `GPSName`='".$gps."' AND 
		UNIX_TIMESTAMP(CONCAT(`DateOriginal`,' ',`TimeOriginal`))> UNIX_TIMESTAMP('".$ttn."')
		AND UNIX_TIMESTAMP(CONCAT(`DateOriginal`,' ',`TimeOriginal`)) < UNIX_TIMESTAMP('".$ttn2."') ";
		$rr = mysql_query($qq,$conn);	
		$nrr = mysql_numrows($rr);	
		if ($nrr>0) {
			while ($rw = mysql_fetch_assoc($rr)) {
				$result = array_merge((array)$ptvars,(array)array($obs,$kkinn),(array)$rw);
				$hh = implode("\t",$result);
				$singlerec = $hh."\n";
				fwrite($fh, $singlerec);
			}
		}	
	}
}

fclose($fh);

//$nofpts=0;
if ($nofpts>0) {
	$_SESSION['expeditosids'] = serialize($todoexpeditosids);
$title = 'Expedito GPSTracks';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<form action='export-expedito-gpstracks-exec.php' name='myform' method='post'>
  <input type='hidden' value='$ispopup' name='ispopup' />
  <input type='hidden' name='prepared' value='1' />
  <input type='hidden' name='nofptsoriginal' value='".$nofptsoriginal."' />
<br />
<table align='center' cellpadding='5' width='50%' class='success'>
 <tr><td>Levantamento expedito ".($nofptsoriginal-$nofpts)."/".$nofptsoriginal." exportando dados de GPS...</td></tr></table>
</form>
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.00001);</script>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
} else {
	$metkk = array("WikiExpeditoID","DATA_LEVANTAMENTO","MINORAREA","LOCALIDADE","LOCALIDADE_ESPECIFICA","LEVANTAMENTO_START","LEVANTAMENTO_END","OBSERVADOR","INTERVALOTEMPO","NOME_GPS","TIPO_GPSREC","TIME_GPSREC","LONGITUDE_GPSREC","LATITUDE_GPSREC","ALTITUDE_GPSREC","ARQUIVO_GPSRECS");
	$metvv = array("Identificador do Registro no Wiki", "Data do levantamento em campo", "Municipio", "Localidade abaixo de município, hierárquica", "Localidade mais específica do ponto", "Hora de início do levantamento", "Hora do término do levantamento", "Nome do coletor de dados em campo", "Identificador do intervalo de coleta de 15 minutos, de 1 a 4", "Nome do aparelho de GPS que leu o dado", "Tipo de registro de coordenada (trackpoint ou waypoint)", "Hora de registro da coordenada", "Longitude do registro em décimos de grau", "Latitude do registro em décimos de grau", "Altitude do registro em metros", "Nome do arquivo que foi importado contendo os dados de GPS");
	$metadados = array_combine($metkk,$metvv);
	$export_meta = "exportExpeditoGPSdata_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_EXPLAINCOLS.csv";
	$fh = fopen("temp/".$export_meta, 'w') or die("nao foi possivel gerar o arquivo");
	$sgrec = "NOME_COLUNA\tDEFINICAO\n";
	fwrite($fh, $sgrec);
	foreach ($metadados as $kk => $vv) {
		$sgrec = $kk."\t".$vv."\n";
		fwrite($fh, $sgrec);
	}
	fclose($fh);
	if (file_exists("temp/".$export_filename)) {
		header("location: export-expedito-save.php?ispopup=1");
	} else {
		header("location: export-expedito-form.php?ispopup=1");
	}
}
?>