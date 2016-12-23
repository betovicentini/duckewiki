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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$body='';
$title = 'GPS Avistamento';
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($enviado!=1) {
echo	
"<br />
<table align='left' class='myformtable' cellpadding=3 cellspacing=0 width=50%>
<thead>
<tr >
<td colspan=100%>Passo I - ".GetLangVar('gpsdataimport')."&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
$help = GetLangVar('gpsdataimport_msg');
echo	" onclick=\"javascript:alert('$help');\" />
</td>
</tr>
</thead>
<tbody>
<form enctype='multipart/form-data' action='avistamento_trilhas.php' method='POST'>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
<td class='tdsmallboldright'>".GetLangVar('namefile')."</td><td align='left'>
  <input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
  <input type='hidden' name='enviado' value='1' />
  <input name='uploadfile' type='file' />
</td>
</tr>";

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
<td class='tdsmallboldright'>GPS ".GetLangVar('namenome')."</td>
<td>
  <select name='gpsunit' >
    <option value=''>".GetLangVar('nameselect')."</option>";
	$qq = "SELECT * FROM Equipamentos WHERE Type='gps' ORDER BY Name ASC";
	$res = mysql_query($qq,$conn);
	while ($row =  mysql_fetch_assoc($res)) {
		echo "
    <option value='".$row['EquipamentoID']."' >".$row['Name']."</option>";
	}
echo "
  </select>
</td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>".GetLangVar('namelocalidade')."</td>
  <td >
    <table  align='left' border=0 cellpadding=\"3\" cellspacing=\"0\">
      <tr>
        <td id='locality' class='tdformnotes'>$locality</td>
        <td align='left'>
          <input type='hidden' id='gazetteerid'  name='gazetteerid' value='$gazetteerid' />
          <input type=button value='".GetLangVar('nameselect')."' class='bsubmit' onclick = \"javascript:small_window('localidade-popup.php?gaztag=gazetteerid&localtag=locality&gazetteerid=$gazetteerid',850,150,'LocalidadePopUp');\" />
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
<td class='small' align='left' colspan='100%'>
<br />
<b>Importante</b>: As coordenadas no arquivo GPX <b>devem estar graus</b> (minutos e segundos em d&eacute;cimos de grau, e S e O valores negativos). Para isso, basta ajustar as op&ccedil;&otilde;es no seu software de GPS quando gerar o arquivo.<br />
</td>
</tr>";

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
<td align='center' colspan='100%'><input type='submit' class='bsubmit' value='Importar os dados' /></td>
</tr>
</form>
</tbody>
</table>
<br />
";
} 
else { //se continuar foi apertado
//fazer importação
	if ($gpsunit>0 && $gpsunit!=GetLangVar('nameselect') && $gazetteerid>0 ) {
		//Create table if not exists
		$qq = "ALTER TABLE GPS_DATA ADD COLUMN TrilhaID INT(10)";
		@mysql_query($qq,$conn);
		$myfile = $_FILES['uploadfile']['name'];
		if ($myfile) {
			$fn = $_FILES['uploadfile']['name'];
			$ext = explode(".",$fn);
			$ll = count($ext)-1;
			$extension = strtoupper($ext[$ll]);
			if ($extension=='GPX') {
				$importdate = date("Y-m-d");
				$newfilename = $importdate."_".$fn;
				if (!file_exists("uploads/gps_files/".$newfilename)) {
					move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/gps_files/".$newfilename);
				}
				$myfile = "uploads/gps_files/".$newfilename;
				$file = file_get_contents($myfile);
				$xml = new SimpleXMLElement($file);
				$erropt=0;
				$sucessopt=0;
				$jaexistept=0;
				$linked=0;
				$suces=0;
				////////extrai os waipoints
				foreach ($xml->wpt as $wpt) {
					$coord = $wpt->attributes();
					$long = $coord[1];
					$lat = $coord[0];
					$alt = $wpt->ele;
					$time = $wpt->cmt;
					$dattt = explode(" ",$time);
					$dateoriginal = $dattt[0];
					$timeoriginal = $dattt[1];
					//testa se a data esta em portugues e se estiver converte
					$teste = array('FEV','ABR','MAI','AGO','SET','OUT','DEZ');
					$dd = explode('-',$dateoriginal);
					$ddm = trim($dd[1]);
					$por =0;
					foreach ($teste as $tt) {
						preg_match("/".$tt."/", $ddm,$matches);
						if (count($matches)>0) {
							$por++;
						}
					}
					if ($por>0) {
						$patterns = array('/JAN/','/FEV/','/MAR/','/ABR/','/MAI/','/JUN/','/JUL/','/AGO/','/SET/','/OUT/','/NOV/','/DEZ/');
						$replacements = array('JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
						$ddm = preg_replace($patterns,$replacements,$ddm);
						$dd[1] = $ddm;
					}
					$dateoriginal = implode("-",$dd);
					$dd = new DateTime($dateoriginal);
					$dateoriginal = $dd->format("Y-m-d");
					$name = $wpt->name;
					$arrayofvalues = array(
						'Name' => $name,
						'Type' => 'Waypoint',
						'DateTimeOriginal' => $time,
						'DateOriginal' => $dateoriginal,
						'TimeOriginal' => $timeoriginal,
						'Latitude' => $lat,
						'Longitude' => $long,
						'Altitude' => $alt,
						'GPSName' =>$gpsunit,
						'GazetteerID' => $gazetteerid,
						'FileName' => $newfilename);
					/////////////////////
					$qq = "SELECT * FROM GPS_DATA WHERE Name='".$name."' AND  DateTimeOriginal='".$time."' AND Type='Waypoint'";
					$resul = mysql_query($qq,$conn);
					$nresul = mysql_numrows($resul);
					if ($nresul==0) {
						$newpoint = InsertIntoTable($arrayofvalues,'PointID','GPS_DATA',$conn);
						if (!$newpoint) {
							echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
  </table>
<br />
";
							$erropt++;
						} else {
							if ($pessoaid>0) {  //se um coletor foi indicado
									$ptnum = RemoveLetras($name);
									$qq = "SELECT * FROM Especimenes WHERE ColetorID='".$pessoaid."' AND Number='".$ptnum."' AND (GPSPointID='0' OR GPSPointID IS NULL)";
									$ptff = mysql_query($qq,$conn);
									$tem = mysql_numrows($ptff);
									if ($tem>0) {
										$rpt = mysql_fetch_assoc($ptff);
										$specptid = $rpt['EspecimenID'];
										$arrayofvalues = array('GazetteerID' => $gazetteerid, 'GPSPointID' => $newpoint);
										CreateorUpdateTableofChanges($specptid,'EspecimenID','Especimenes',$conn);
										$updatespecid = UpdateTable($specptid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
										$linked++;
									}
							} //end if pessoaid
							$sucessopt++;
						}
					} else {
						$jaexistept++;
					}
					echo "&nbsp;";
					flush();
				}
				//extrai os trajetos
				$errotrackpt=0;
				$sucessotrackpt=0;
				$jaexistetrackpt=0;
				foreach ($xml->trk as $tracksegs) {
					$trakname = $tracksegs->name;
					//pegar os pontos do track
					$trck=0;
					foreach($tracksegs->trkseg->trkpt as $trpt) {
						$coord = $trpt->attributes();
						$long = $coord[1];
						$lat = $coord[0];
						$alt = $trpt->ele;
						$time = $trpt->time;
						$name= $trakname."_".$trck;
						$trck++;
						$dattt = explode("T",$time);
						$dateoriginal = $dattt[0];
						$timeoriginal = $dattt[1];
						$timeoriginal = str_replace("Z","",$timeoriginal);
						//testa se a data esta em portugues e se estiver converte
						$teste = array('FEV','ABR','MAI','AGO','SET','OUT','DEZ');
						$dd = explode('-',$dateoriginal);
						$ddm = trim($dd[1]);
						$por =0;
						foreach ($teste as $tt) {
							preg_match("/".$tt."/", $ddm,$matches);
							if (count($matches)>0) {
								$por++;
							}
						}
						if ($por>0) {
							$patterns = array('/JAN/','/FEV/','/MAR/','/ABR/','/MAI/','/JUN/','/JUL/','/AGO/','/SET/','/OUT/','/NOV/','/DEZ/');
							$replacements = array('JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
							$ddm = preg_replace($patterns,$replacements,$ddm);
							$dd[1] = $ddm;
						}
						$dateoriginal = implode("-",$dd);
						$dd = new DateTime($dateoriginal);
						$dateoriginal = $dd->format("Y-m-d");
						//$dd = new DateTime($dateoriginal);
						//$dateoriginal = $dd->format("Y-m-d");
						$arrayofvalues = array(
							'TrackName' => $trakname,
							'Name' => $name,
							'Type' => 'Trackpoint',
							'DateTimeOriginal' => $time,
							'DateOriginal' => $dateoriginal,
							'TimeOriginal' => $timeoriginal,
							'Latitude' => $lat,
							'Longitude' => $long,
							'Altitude' => $alt,
							'GPSName' =>$gpsunit,
							'GazetteerID' => $gazetteerid,
							'FileName' => $myfile);
						$qq = "SELECT * FROM GPS_DATA WHERE Name='".$name."' AND  DateTimeOriginal='".$time."' AND Type='Trackpoint'";
						$resul = mysql_query($qq,$conn);
						$nresul = mysql_numrows($resul);
						if ($nresul==0) {
							$newpoint = InsertIntoTable($arrayofvalues,'PointID','GPS_DATA',$conn);
							if (!$newpoint) {
								$errotrackpt++;
							} else {
								$sucessotrackpt++;
							}
						} else {
							$jaexistetrackpt++;
						}
					}
					echo "&nbsp;";
					flush();
				}
			} else {
				$extensaoerrada= 1;
			}// if it is not the right extension
			echo "&nbsp;";
			flush();
		}
		if ($extensaoerrada==1) {
			echo "
<br />
  <table cellpadding=\"1\" width='80%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>Arquivo n&atilde;o &eacute; GPX, mas $extension</td></tr>
</table>
<br />";
		}
		if ($errotrackpt>0 || $erropt>0) {
			echo "
<br />
  <table cellpadding=\"1\" width='80%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>$errotrackpt Tracks e $erropt Waypoints não puderam ser cadastrados</td></tr>
  </table>
<br />";
		}
		if ($sucessotrackpt>0 || $sucessopt>0) {
			echo "
<br />
<table cellpadding=\"1\" width='80%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('sucesso1')." (".$sucessotrackpt." TrackPoints e $sucessopt WayPoints)</td></tr>
</table>
<br />";
			$suces++;
		}
		
		if ($jaexistetrackpt>0 || $jaexistept>0) {
			echo "
<br />
<table cellpadding=\"1\" width='80%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>$jaexistetrackpt Tracks e $jaexistept Waypoints já estavam cadastrados</td></tr>
</table>";
			$suces++;
		}
		if ($suces) {
			echo "
<form  name='myform' action='avistamento_trilhas.php' method='post'>
  <input type='hidden' value='".$myfile."' name='filename' />
  <input type='hidden' value='".$gpsunit."' name='gpsunit' />
  <input type='hidden' value='1' name='fromgps' />
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',1);</script>
</form>
";
		}
	} 
	else {
		echo "
<br />
<table cellpadding='2' cellspacing=0 class='erro' width='80%' align='center'>
  <tr><td class='tdformnotes'><b>".GetLangVar('erro1')."</b></td></tr>";
		if ($gazetteerid==0 || empty($gazetteerid)) {
			echo "
  <tr><td class='tdformnotes'><i>".GetLangVar('namegazetteer')."</i></td><tr>";
		}
		if ($gpsunit==0 || empty($gpsunit)) {
			echo "
  <tr><td class='tdformnotes'><i>GPS ".GetLangVar('namenome')."</i></td><tr>";
		}
		echo "
</table>
";
	}
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>