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
	if (!is_array($expeditosids)) {
		header("location: export-expedito-form.php");
		exit();
	}
	$todoexpeditosids = $expeditosids;
	$createfile = 1;
	$nofptsoriginal = count($expeditosids);
	$nofpts = $nofptsoriginal-1;
	$_SESSION['originalexpeditosids'] = serialize($todoexpeditosids);
} else {
	$todoexpeditosids = unserialize($_SESSION['expeditosids']);
	$createfile = 0;
	$nofpts = count($todoexpeditosids)-1;
}
$tod = $todoexpeditosids[0];
unset($todoexpeditosids[0]);
$todoexpeditosids = array_values($todoexpeditosids);

$export_filename = "exportExpeditodata_".$_SESSION['userlastname']."_".$_SESSION['sessiondate'].".csv";
if ($createfile==1) {
	$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
	$header = array("WikiExpeditoID","DATA_LEVANTAMENTO","MINORAREA","LOCALIDADE","LOCALIDADE_ESPECIFICA","NOME_PONTOGPS","LONGITUDE","LATITUDE","TESTEMUNHO","IDENTIFICACAO","FAMILIA","GENERO","ESPECIE","ESPECIEAUTHOR","INFRAESPECIENIVEL","INFRAESPECIE","INFRAESPECIEAUTOR","CF", "OBSERVADOR","INTERVALOTEMPO");
	$hh = implode("\t",$header);
	$header = $hh."\n";
	fwrite($fh, $header);
} else {
	$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
}


//selecionando dados do ponto sendo exportado
$qq = "SELECT MetodoExpedito.ExpeditoID as WikiExpeditoID, DataColeta";
//$qq .= ", IF(GPSPointID>0,gpsmuni.Municipio,'') as MINORAREA, gpsgaz.PathName as LOCALIDADE, IF(GPSPointID>0,CONCAT(gpsgaz.GazetteerTIPOtxt,' ',gpsgaz.Gazetteer),'') as GAZETTEER2, IF(GPSPointID>0 AND gps.Name<>'',gps.Name,'') as PontoGPS";
$qq .= ", IF(GPSPointID>0,gpsmuni.Municipio,'') as MINORAREA, gpsgaz.PathName as LOCALIDADE, IF(GPSPointID>0,gpsgaz.Gazetteer,'') as GAZETTEER2, IF(GPSPointID>0 AND gps.Name<>'',gps.Name,'') as PontoGPS";
$qq .= ", IF(GPSPointID>0,gps.Longitude,'') as LONGITUDE, 
IF(GPSPointID>0,gps.Latitude,'') as LATITUDE FROM MetodoExpedito LEFT JOIN GPS_DATA as gps ON gps.PointID=MetodoExpedito.GPSPointID LEFT JOIN Gazetteer as gpsgaz ON gps.GazetteerID=gpsgaz.GazetteerID LEFT JOIN Municipio as gpsmuni ON gpsmuni.MunicipioID=gpsgaz.MunicipioID WHERE MetodoExpedito.ExpeditoID='".$tod."'";
$res = mysql_query($qq, $conn);
$pointvars = mysql_fetch_assoc($res);

$qu = "SELECT *,addcolldescr(PessoasIDs) as addcolls, taxavalues(TaxonomiaIDs) as identificadores FROM MetodoExpeditoPlantas WHERE ExpeditoID='".$tod."' ORDER BY  IntervaloTempo";
$rr = mysql_query($qu, $conn);
//echo $qu."<br />";
while ($row = mysql_fetch_assoc($rr)) {
	if ($row['EspecimenIDs']>0) {
		$qu = "SELECT CONCAT(colpessoa.SobreNome,'_',pltb.Number) as TESTEMUNHO, 
		IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as IDENTIFICACAO,
		famtb.Familia as FAMILY, gentb.Genero as GENUS, sptb.Especie as ESPECIE, sptb.EspecieAutor as AUTHOR1, infsptb.InfraEspecieNivel as INFRAESPECIENIVEL, infsptb.InfraEspecie as INFRAESPECIE, infsptb.InfraEspecieAutor as INFRAESPECIEAUTOR, iddet.DetModifier as CF ";
		$qu .= "FROM Especimenes as pltb LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID LEFT JOIN Identidade as iddet USING(DetID) LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID WHERE pltb.EspecimenID='".$row['EspecimenIDs']."'";
		$ru =  mysql_query($qu, $conn);
		$identificacoes = mysql_fetch_assoc($ru);
	} elseif (!empty($row['identificadores'])) {
		$ids = explode("$$$", $row['identificadores']);
		$idd1 = $ids[1];
		$xdd =  array("TESTEMUNHO" =>  'NA', "IDENTIFICACAO"=> $idd1);
		$idd2 = explode("|", $ids[0]);
		$idkks = array('FAMILY','GENUS','ESPECIE','AUTHOR1','INFRAESPECIENIVEL','INFRAESPECIE','INFRAESPECIEAUTOR');
		$idd = array_combine($idkks,$idd2);
		//echopre($idkks);
		//echopre($idd2);
		$identificacoes = array_merge((array)$xdd,(array)$idd,(array)array("CF" => ' '));
	}	
	//save results
	$cols = explode(";",$row['addcolls']);

	$resularr = array();
	foreach ($cols as $coletor) {
		$coletc = trim($coletor);
		$colarr = array("Observador"=> $coletc, "Intervalo"=> $row['IntervaloTempo']);
		$singlerec = array_merge((array)$pointvars,(array)$identificacoes,(array)$colarr);
		$hh = implode("\t",$singlerec);
		$singlerec = $hh."\n";
		fwrite($fh, $singlerec);
	}
}
fclose($fh);

//$nofpts=0;
if ($nofpts>0) {
	$_SESSION['expeditosids'] = serialize($todoexpeditosids);
	$title = 'Exportar Expedito';
	$body = '';
	FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<form action='export-expedito-exec.php' name='myform' method='post'>
  <input type='hidden' value='$ispopup' name='ispopup' />
  <input type='hidden' name='prepared' value='1' />
  <input type='hidden' name='nofptsoriginal' value='".$nofptsoriginal."' />
<br />
<table align='center' cellpadding='5' width='50%' class='erro'>
  <tr><td>Levantamento expedito ".($nofptsoriginal-$nofpts)."/".$nofptsoriginal." sendo exportado</td></tr>
</table>
</form>
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.00001);</script>";
	$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
	FazFooter($which_java,$calendar=FALSE,$footer=$menu);
} else {
	$metkk = array("WikiExpeditoID","DATA_LEVANTAMENTO","MINORAREA","LOCALIDADE","LOCALIDADE_ESPECIFICA","NOME_PONTOGPS","LONGITUDE","LATITUDE","TESTEMUNHO","IDENTIFICACAO","FAMILIA","GENERO","ESPECIE","ESPECIEAUTHOR","INFRAESPECIENIVEL","INFRAESPECIE","INFRAESPECIEAUTOR","CF", "OBSERVADOR","INTERVALOTEMPO");
	$metvv = array("Identificador do Registro no Wiki","Data do levantamento em campo","Municipio","Localidade abaixo de município, hierárquica","Localidade mais específica do ponto","Nome do ponto do GPS quando houver (DEPRECATED)","Longitude ponto inicial em décimos de grau","Latitude ponto inicial  em décimos de grau","Coletor e Numero de coleta identificando o material testemunho da identificação","A identificação do material testemunho no melhor nível","Familia","Genero","Espécie","Autor da espécie","Categoria Infraespecífica, se houver","Infra espécie se houver","Autor da infra espécie, se houver","CF - se a determinação de menor nível tem algum modificador associado","Nome da pessoa que registrou essa observação","Intervalo de tempo em que a observação foi feita");
	$metadados = array_combine($metkk,$metvv);
	$export_meta = "exportExpeditodata_".$_SESSION['userlastname']."_".$_SESSION['sessiondate']."_EXPLAINCOLS.csv";
	echopre($metadados);
	$fh = fopen("temp/".$export_meta, 'w') or die("nao foi possivel gerar o arquivo");
	$sgrec = "NOME_COLUNA\tDEFINICAO\n";
	fwrite($fh, $sgrec);
	foreach ($metadados as $kk => $vv) {
		$sgrec = $kk."\t".$vv."\n";
		fwrite($fh, $sgrec);
	}
	fclose($fh);
	if (file_exists("temp/".$export_filename)) {
		header("location: export-expedito-gpstracks-exec.php?ispopup=1");
	} else {
		header("location: export-expedito-form.php?ispopup=1");
	}
}
?>