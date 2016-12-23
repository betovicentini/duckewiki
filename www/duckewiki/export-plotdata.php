<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include_once("functions/class.Numerical.php") ;

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || (trim($uuid)=='')) {
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
} 
else {
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
//echopre($ppost);
//echopre($_SESSION);
if (!isset($prepared) && $gazetteerid>0 && $daptraitid>0) {
	$export_filename = "dadosParcela_".$gazetteerid.".csv";
	$export_filename_metadados = "dadosParcela_".$gazetteerid."_metadados.csv";
	unset($_SESSION['sqlplot']);
	unset($_SESSION['plotmetadados']);
	$metadados = array();
	$qz = "SELECT DISTINCT moni.CensoID FROM Monitoramento as moni JOIN Plantas AS pl ON moni.PlantaID=pl.PlantaID LEFT JOIN Gazetteer as gaz ON pl.GazetteerID=gaz.GazetteerID LEFT JOIN GPS_DATA as gps ON pl.GPSPointID=gps.PointID WHERE 
(pl.GazetteerID='".$gazetteerid."' OR gps.GazetteerID='".$gazetteerid."' OR gaz.ParentID='".$gazetteerid."') AND
moni.CensoID>0 AND moni.TraitID='".$daptraitid."'";
	$rz = @mysql_query($qz,$conn);
	$ncensos = @mysql_numrows($rz);
	if ($ncensos>0) {
		$sql = "SELECT pltb.PlantaID AS WikiPlantaID, pltb.PlantaTag as TAG, getidentidade(DetID,1,0,1,0,0) AS Familia, getidentidade(DetID,1,0,0,1,0) AS Genero, getidentidade(DetID,1,0,0,0,1) AS Nome";
		$idx=0;
		$metadados['idx'.$idx][0] = 'WikiPlantaID';
		$metadados['idx'.$idx][1] = 'Id de referência da planta na base de dados';
		$idx++;
		$metadados['idx'.$idx][0] = 'TAG';
		$metadados['idx'.$idx][1] = 'Número da placa da árvore no campo';
		$idx++;
		$metadados['idx'.$idx][0] = 'Familia';
		$metadados['idx'.$idx][1] = 'Familia taxonômica, se vazio a planta não está identificada neste nível';
		$idx++;
		$metadados['idx'.$idx][0] = 'Genero';
		$metadados['idx'.$idx][1] = 'Gênero taxonômico, se vazio a planta não está identificada neste nível';
		$idx++;
		$metadados['idx'.$idx][0] = 'Nome';
		$metadados['idx'.$idx][1] = 'Identificação completa da planta (sem autores), no nível que estiver identificada';
		$idx++;
		while ($rr = @mysql_fetch_assoc($rz)) {
			$qz = "SELECT * FROM Censos WHERE CensoID='".$rr['CensoID']."'";
			$ruz = mysql_query($qz,$conn);
			$rwz = mysql_fetch_assoc($ruz);
			$cso = substr($rwz['DataFim'],0,4);
			$coln = "DAP".$cso;
			$coln2 = "DAP".$cso."unit";
			$coln3 = "DAP".$cso."date";
			$sql .= ", censotrait(".$daptraitid.",pltb.PlantaID, 1, 0,0 ) AS ".$coln.", censotrait(".$daptraitid.",pltb.PlantaID, 1, 0,1 ) AS ".$coln2.", censotrait(".$daptraitid.",pltb.PlantaID, 1, 1,0 ) AS ".$coln3;
			$metadados['idx'.$idx][0] = $coln;
			$metadados['idx'.$idx][1] = "DAP da planta segundo o censo ".$rwz['CensoNome'].", que compreende o período de ".$rwz['DataInicio']." à ".$rwz['DataFim'];
			$idx++;
			$metadados['idx'.$idx][0] = $coln2;
			$metadados['idx'.$idx][1] = "Unidade de medida do DAP da planta para censo ".$rwz['CensoNome'];
			$idx++;
			$metadados['idx'.$idx][0] = $coln3;
			$metadados['idx'.$idx][1] = "Data exata da medição DAP para censo ".$rwz['CensoNome'];
			$idx++;
		}
		if ($statustraitid>0) {
			$sql .= ", censotrait(".$statustraitid.",pltb.PlantaID, 1, 0,0 ) AS STATUS, censotrait(".$statustraitid.",pltb.PlantaID, 1, 1,0 ) AS STATUSdata";
			$metadados['idx'.$idx][0] = 'STATUS';
			$metadados['idx'.$idx][1] = "Status da planta, se viva ou morta, se vazio indica que a planta está viva";
			$idx++;
			$metadados['idx'.$idx][0] = 'STATUSdata';
			$metadados['idx'.$idx][1] = "Data de observação do status da planta";
			$idx++;
		}
		if ($alturatraitid>0) {
			$sql .= ", censotrait(".$alturatraitid.",pltb.PlantaID, 1, 0,0 ) AS ALTURA, censotrait(".$alturatraitid.",pltb.PlantaID, 1, 0,1 ) AS ALTURAunit, censotrait(".$alturatraitid.",pltb.PlantaID, 1, 1,0 ) AS ALTURAdata";
			$metadados['idx'.$idx][0] = 'ALTURA';
			$metadados['idx'.$idx][1] = "Altura da planta";
			$idx++;
			$metadados['idx'.$idx][0] = 'ALTURAunit';
			$metadados['idx'.$idx][1] = "Unidade de medida da altura da planta";
			$idx++;
			$metadados['idx'.$idx][0] = 'ALTURAdata';
			$metadados['idx'.$idx][1] = "Data de medição da altura da planta";
			$idx++;
		}
		$sql .= " FROM Plantas AS pltb LEFT JOIN Gazetteer as gaz ON pltb.GazetteerID=gaz.GazetteerID LEFT JOIN GPS_DATA as gps ON pltb.GPSPointID=gps.PointID WHERE (pltb.GazetteerID='".$gazetteerid."' OR gps.GazetteerID='".$gazetteerid."' OR gaz.ParentID='".$gazetteerid."')";
		$qz = "SELECT COUNT(DISTINCT moni.PlantaID) as ntrees FROM Monitoramento as moni JOIN Plantas AS pl ON moni.PlantaID=pl.PlantaID LEFT JOIN Gazetteer as gaz ON pl.GazetteerID=gaz.GazetteerID LEFT JOIN GPS_DATA as gps ON pl.GPSPointID=gps.PointID WHERE (pl.GazetteerID='".$gazetteerid."' OR gps.GazetteerID='".$gazetteerid."' OR gaz.ParentID='".$gazetteerid."')";
		$rz = mysql_query($qz,$conn);
		$rzw = mysql_fetch_assoc($rz);
		$nrz = $rzw['ntrees'];
		$stepsize = 1000;
		if ($nrz<$stepsize) {
			$nsteps=1;
		} else {
			$nsteps = ceil($nrz/$stepsize);
		}
		$_SESSION['sqlplot'] = $sql;
		$_SESSION['ntrees_'.$gazetteerid] = $nrz;
		$_SESSION['plotmetadados'] = serialize($metadados);
		$prepared = 1;
	} 
	else {
		$title = 'Exportar dados de parcela';
		$body = '';
		FazHeader($title,$body,$which_css,$which_java,$menu);
	echo "
<br />
<table class='erro' align='center' cellpadding='5' cellspacing='3'>
<tr><td>Desculpe, mas não há dados para exportar dessa parcela; ou você não definiu a variável DAP (obrigatório), em configurações; ou não definiu nenhum censo para essa parcela.</td></tr>
<tr><td><input type='button' value='Fechar' onclick='javascript: window.close();'</td></tr>
</table>";
	$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
	FazFooter($which_java,$calendar=FALSE,$footer=$menu);
	}
}
//INICIO DO PREPARO DO ARQUIVO. FAZ UM LOOP, ATUALIZANDO (REFRESHING) A PÁGINA EM CADA PASSO DO LOOP, POR UMA LIMITACAO DE MEMORIA (ALTERNATIVAS DEVEM EXISTIR)! O NUMERO DE REGISTRO EM CADA PASSO É DEFINIDO PELA VARIÁVEL $nsteps, DEFINIDA NO FINAL DO IF {}  ACIMA
if ($prepared==1 && $step<=$nsteps) {
	$sql = $_SESSION['sqlplot'];
	$justloop=0;
	if (!isset ($step)) {
		$step=-1;
		$justloop=1;
	} 
	else {
		if ($step==0) {
			$st1=0;
		} else {
			$st1 = $st1+$stepsize+1;
		}
		$qqq = $sql." LIMIT $st1,$stepsize";
		$res = mysql_query($qqq,$conn);
	}
	if ($res) {
		if ($step==0) {
			$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
			$count = mysql_num_fields($res);
			$header = '';
			for ($i = 0; $i < $count; $i++){
				$header .= mysql_field_name($res, $i)."\t";
			}
			$header .= "\n";
			fwrite($fh, $header);
		} 
		else {
			$fh = fopen("temp/".$export_filename, 'a') or die("nao foi possivel abrir o arquivo");
		}
		while($rsw = mysql_fetch_row($res)){
				$line = '';
				foreach($rsw as $value){
					if ($value=='0000-00-00') {
						$value='';
					}
					if(!isset($value) || $value == ""){
						$value = "\t";
					}
					else
						{
						//important to escape any quotes to preserve them in the data.
						$value = str_replace('"', '""', $value);
						//needed to encapsulate data in quotes because some data might be multi line.
						//the good news is that numbers remain numbers in Excel even though quoted.
						$value = '"' . $value . '"' . "\t";
					}
					$line .= $value;
				}
				$lin = trim($line)."\n";
				fwrite($fh, $lin);
		}
		fclose($fh);
		$justloop=1;
	}
	if ($justloop==1) {
		$ntrees = $_SESSION['ntrees_'.$gazetteerid];
		$jaforam = floor($step*$stepsize);
		$title = 'Exportar dados de parcela';
		$body = '';
		FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<form action='export-plotdata.php' name='myform' method='post'>
  <input type='hidden' name='prepared' value='".$prepared."'>
  <input type='hidden' name='ispopup' value='".$ispopup."'>
  <input type='hidden' name='nsteps' value='".$nsteps."'>
  <input type='hidden' name='step' value='".($step+1)."'>
  <input type='hidden' name='stepsize' value='".$stepsize."'>
  <input type='hidden' name='st1' value='".($st1-1)."'>
  <input type='hidden' name='gazetteerid' value='".$gazetteerid."'>
  <input type='hidden' name='export_filename' value='".$export_filename."'>
  <input type='hidden' name='export_filename_metadados' value='".$export_filename_metadados."'>";
echo "<br />
<table align='center' cellpadding='5' width='40%' class='success'>
  <tr><td style='font-size: 1em;'>
  Exportando para arquivo de texto os dados dessa parcela (inclui tag, identificação, e dap para todos os censos disponíveis, altura se houver e status).
  <tr><td class='tdsmallbold' align='center'>Passo ".($step+1)." de ".($nsteps+1)."</td></tr>";
if ($step>=0) {  
echo  "<tr><td class='tdsmallbold' align='center'>".$jaforam." de ".$ntrees." árvores processadas.</td></tr>";
}
echo "
</table>
</form>
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.00001);</script>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
	}
} // termina de produzir o arquivo de dados
//se tiver terminado, então salva metadados e vai para a página de download
if ($step>$nsteps && isset($step)) {
	$metadados = unserialize($_SESSION['plotmetadados']);
	$fh = fopen("temp/".$export_filename_metadados, 'w') or die("nao foi possivel gerar o arquivo");
	$stringData = "COLUNA\tDEFINICAO"; 
	foreach ($metadados as $kk => $vv) {
		$stringData = $stringData."\n".$vv[0]."\t".$vv[1];
	}
	fwrite($fh, $stringData);
	fclose($fh);
	if (file_exists("temp/".$export_filename) && file_exists("temp/".$export_filename_metadados)) {
		header("location: export-plotdata-save.php?ispopup=1&gazetteerid=".$gazetteerid);
	} else {
		echo "<script language=\"JavaScript\">setTimeout('window.close();',0.00001);</script>";
	}
} 
?>