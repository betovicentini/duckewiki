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
$title = 'Trilhas de Avistamento';
FazHeader($title,$body,$which_css,$which_java,$menu);
if (!isset($viagemid)) {

echo "
<br />
<table class='myformtable' align='left' cellpadding='7' width='50%'>
<thead>
  <tr ><td colspan='100%'>Definir ou editar trilhas de busca</td></tr>
</thead>
<tbody>
<form action=avistamento_trilhas.php method='post'>
<tr>
  <td  colspan='100%'>
    <select name='viagemid' onchange='this.form.submit()'>
      <option value=''>".GetLangVar('nameselect')."</option>
      <option value=''>------------</option>";
	$qq = "SELECT * FROM Expedicoes ORDER BY DateStart DESC";
	$rrr = @mysql_query($qq,$conn);
	while ($row = mysql_fetch_assoc($rrr)) {
			echo "
      <option value=".$row['ViagemID'].">".$row['Name']." [".$row['DateStart']." a ".$row['DateEnd']."]</option>";
		}
	echo "
    </select>
    </td>
</tr>
</tbody>
</table>
</form>";
} 
else {


if ($final==1) {
	$qq = "ALTER TABLE GPS_DATA ADD COLUMN TrilhaID INT(10)";
	@mysql_query($qq,$conn);
	$erro=0;
	$sucesso=0;
	$newtrilhas = $trilhas;
	$sempontos = 0;
	foreach ($trilhas as $kk => $vv) {
		$nn = trim($vv['Name']);
		$ts = $vv['TimeStart'];
		$te = $vv['TimeEnd'];
		$det = $vv['Data'];
		if ($nn=='' || $ts=='00:00:00' || $te=='00:00:00' || $ts=='' || $te=''|| $det='') {
		
		} else {
			$qq = "SELECT * FROM GPS_DATA as gps WHERE gps.DateOriginal='".$vv['Data']."' AND gps.TimeOriginal>='".$vv['TimeStart']."' AND gps.TimeOriginal<='".$vv['TimeEnd']."' AND gps.Type='Trackpoint' AND GPSName='".$vv['GPSName']."'";
			$resul = mysql_query($qq,$conn);
			$ntracks = mysql_numrows($resul);
			$qq = "SELECT * FROM GPS_DATA as gps WHERE gps.DateOriginal='".$vv['Data']."' AND gps.TimeOriginal>='".$vv['TimeStart']."' AND gps.TimeOriginal<='".$vv['TimeEnd']."' AND gps.Type='Waypoint ' AND GPSName='".$vv['GPSName']."'";
			$resul = mysql_query($qq,$conn);
			$nwaypts = mysql_numrows($resul);
			$ntt = $ntracks+$nwaypts;
			if ($ntt>0) {
				if ($vv['TrilhaID']==0) {
					$arrayofvalues = array('Name' => $vv['Name'],'ViagemID' => $viagemid, 'Data' => $vv['Data'], 'TimeStart'=> $vv['TimeStart'],'TimeEnd' => $vv['TimeEnd'],'GPSName' => $vv['GPSName']);
					$trid = InsertIntoTable($arrayofvalues,'TrilhaID','Expedicao_Trilhas',$conn);
					if (!$viagemid) {
						$erro++;
					} else {
						$newtrilhas[$kk]['TrilhaID'] = $trid;
						$aa = array('N_Waypoints'=> $nwaypts, 'N_Trackpoints' => $ntracks);
						$newtrilhas[$kk] = array_merge((array)$newtrilhas[$kk],(array)$aa);
						$sucesso++;
						$qq = "UPDATE GPS_DATA SET TrilhaID='".$trid."' WHERE DateOriginal='".$vv['Data']."' AND TimeOriginal>='".$vv['TimeStart']."' AND TimeOriginal<='".$vv['TimeEnd']."' AND GPSName='".$vv['GPSName']."'";
						mysql_query($qq,$conn);
					}
			} 
			else {
				$qq = "UPDATE GPS_DATA SET TrilhaID='0' TrilhaID='".$vv['TrilhaID']."' AND GPSName='".$vv['GPSName']."'";
				mysql_query($qq,$conn);
				$arrayofvalues = array('Name' => $vv['Name'], 'Data' => $vv['Data'], 'GPSName' => $vv['GPSName'], 'TimeStart'=> $vv['TimeStart'],'TimeEnd' => $vv['TimeEnd']);
				$upp = CompareOldWithNewValues('Expedicao_Trilhas','TrilhaID',$vv['TrilhaID'],$arrayofvalues,$conn);
				if (!empty($upp) && $upp>0) { 
					CreateorUpdateTableofChanges($vv['TrilhaID'],'TrilhaID','Expedicao_Trilhas',$conn);
					$trid = UpdateTable($vv['TrilhaID'],$arrayofvalues,'TrilhaID','Expedicao_Trilhas',$conn);
					if (!$trid) {
						$erro++;
					} else {
						$sucesso++;
						$aa = array('N_Waypoints'=> $nwaypts, 'N_Trackpoints' => $ntracks);
						$newtrilhas[$kk] = array_merge((array)$newtrilhas[$kk],(array)$aa);
						$qq = "UPDATE GPS_DATA SET TrilhaID='".$trid."' WHERE DateOriginal='".$vv['Data']."' AND TimeOriginal>='".$vv['TimeStart']."' AND TimeOriginal<='".$vv['TimeEnd']."' AND GPSName='".$vv['GPSName']."'";
						mysql_query($qq,$conn);
					}
				}
			}
		} 
		else {
			$sempontos++;
			$aa = array('N_Waypoints'=> 'NAO ENCONTRADO', 'N_Trackpoints' => 'NAO ENCONTRADO');
			$newtrilhas[$kk] = array_merge((array)$newtrilhas[$kk],(array)$aa);
		}
	}
}
	if ($erro==0 && $sucesso>0) {
	$trilhas = $newtrilhas;
echo "
<br />
<table cellpadding=\"7\" align='center' class='success'>
  <tr>
    <td class='tdsmallbold' align='center'>".GetLangVar('sucesso1')."</td>
  </tr>
</table>
<br />";
	} 
	else {
		if ($erro==0 && $sempontos==0) {
			$txt = 'Houve um erro de cadastro. Contate o administrados do site';
		}
		if ($sempontos>0) {
			$txt1 = 'Não foram encontrados pontos para '.$sempontos.' trilhas informadas';
		}
echo "
<br />
<table cellpadding=\"7\" align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>$txt</td></tr>
  <tr><td class='tdsmallbold' align='center'>$txt1</td></tr>
</table>
<br />
";
	}
} //if final

if ($viagemid>0 && !isset($trilhaids) && count($trilhas)==0) {
	$qq = "SELECT * FROM Expedicoes WHERE ViagemID='".$viagemid."'";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	$datestart = $row['DateStart'];
	$dateend = $row['DateEnd'];
	$expedicao = $row['Name'];
	//checa se existem dados espaciais para a data dessa viagem
	$qq = "SELECT DISTINCT gps.DateOriginal,equi.Name,gps.GPSName FROM GPS_DATA as gps LEFT JOIN Equipamentos as equi ON gps.GPSName=equi.EquipamentoID  WHERE gps.DateOriginal>='".$datestart."' AND gps.DateOriginal<='".$dateend."' AND gps.Type='Trackpoint' ORDER BY gps.DateOriginal";
	$resul = mysql_query($qq,$conn);
	$dates = mysql_numrows($resul);
	if ($dates==0) {
		echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Não existem dados de GPS para a data da expedição. Precisa importar primeiro para poder definir as trilhas</td></tr>
</table>
<br />";
		$erro++;
	} 
	else {
		$datas = array();
		$gpsss = array();
		$j = 0;
		while ($rwul = mysql_fetch_assoc($resul)) {
			$datas = array_merge((array)$datas,(array)$rwul['DateOriginal']);
			$gpsss = array_merge((array)$gpsss,(array)array($j."_".$rwul['GPSName'] => $rwul['Name']));
			$j++;
		}
		$gpsss = array_unique($gpsss);
		$qq = "SELECT TrilhaID,Name,GPSName,Data,TimeStart,TimeEnd FROM Expedicao_Trilhas WHERE ViagemID='".$viagemid."' ORDER BY Name";
		$ru = mysql_query($qq,$conn);
		$nru = @mysql_numrows($ru);
		$trilhas = array();
		$j=0;
		if ($nru>0) {
			while ($rwu = mysql_fetch_assoc($ru)) {
				$trilhas[$viagemid."_".$j] = $rwu;
				$qq = "SELECT * FROM GPS_DATA as gps WHERE gps.DateOriginal='".$rwu['Data']."' AND gps.TimeOriginal>='".$rwu['TimeStart']."' AND gps.TimeOriginal<='".$rwu['TimeEnd']."' AND gps.Type='Trackpoint' AND GPSName='".$rwu['GPSName']."'";
				$rtr = mysql_query($qq,$conn);
				$ntracks = mysql_numrows($rtr);
				$qq = "SELECT * FROM GPS_DATA as gps WHERE gps.DateOriginal='".$rwu['Data']."' AND gps.TimeOriginal>='".$rwu['TimeStart']."' AND gps.TimeOriginal<='".$rwu['TimeEnd']."' AND gps.Type='Waypoint ' AND GPSName='".$rwu['GPSName']."'";
				$rwp = mysql_query($qq,$conn);
				$nwaypts = mysql_numrows($rwp);
				$aa = array('N_Waypoints'=> $nwaypts, 'N_Trackpoints' => $ntracks);
				$trilhas[$viagemid."_".$j] = array_merge((array)$trilhas[$viagemid."_".$j],(array)$aa);
				$j++;
			}
		} 
		$nt = count($trilhas);
		if ($nt==0) {
			$ni = 0;
		} else {
			$ni = $nt+1;
		}
		$ntt = $ni+5;
		for ($j=$ni;$j<=$ntt;$j++) {
			$trilhas[$viagemid."_".$j] = array('TrilhaID' => 0,'Data' => 'YYYY-MM-DD', 'GPSName' => 0, 'Name' => '','TimeStart'=> '00:00:00','TimeEnd' => '00:00:00','N_Waypoints' => '','N_Trackpoints' => '');
		}
	}
}
if (count($trilhas)>0) {
	$qq = "SELECT * FROM Expedicoes WHERE ViagemID='".$viagemid."'";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	$datestart = $row['DateStart'];
	$dateend = $row['DateEnd'];
	$expedicao = $row['Name'];
	$qq = "SELECT DISTINCT gps.DateOriginal,equi.Name,gps.GPSName FROM GPS_DATA as gps LEFT JOIN Equipamentos as equi ON gps.GPSName=equi.EquipamentoID  WHERE gps.DateOriginal>='".$datestart."' AND gps.DateOriginal<='".$dateend."' AND gps.Type='Trackpoint' ORDER BY gps.DateOriginal";
	$resul = mysql_query($qq,$conn);
	$datas = array();
	$gpsss = array();
	$j = 0;
	while ($rwul = mysql_fetch_assoc($resul)) {
		$datas = array_merge((array)$datas,(array)$rwul['DateOriginal']);
		$gpsss = array_merge((array)$gpsss,(array)array($j."_".$rwul['GPSName'] => $rwul['Name']));
		$j++;
	}
	$gpsss = array_unique($gpsss);
	//Create table if not exists
	$qq = "CREATE TABLE IF NOT EXISTS Expedicao_Trilhas (
 TrilhaID INT(10) unsigned NOT NULL auto_increment,
 ViagemID INT(10),
 Name VARCHAR(100),
 GPSName INT(10),
 Data DATE,
 TimeStart TIME,
 TimeEnd TIME,
 AddedBy INT(10),
 AddedDate DATE,
 PRIMARY KEY (TrilhaID)) CHARACTER SET utf8";
	@mysql_query($qq,$conn);
	echo "
<br />
<table align='left' class='myformtable' cellpadding='7'>
<thead>
<tr ><td colspan=100%>Definindo as trilhas da expedição $expedicao de $datestart a $dateend</td></tr>
<tr class='subhead'>
<td>Data</td>
<td>GPS</td>
<td>Nome da Trilha</td>
<td>Horario Início</td>
<td>Horario Fim </td>
<td>N Waypoints </td>
<td>N Trackpoints </td>
</tr>
</thead>
<tbody>
<form name='coletaform' action='avistamento_trilhas.php' method='POST'>
<input type='hidden' name=\"viagemid\" value='".$viagemid."' />";
$bgi=1;
foreach($trilhas as $kk => $vv) {
//echopre($vv);
  if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  if ($vv['TrilhaID']==0) {
    $cltxt = '';
  } else {
  	$cltxt = '';
  }
  echo "
  <tr bgcolor = '".$bgcolor."' $cltxt>
    <td>
      <input type='hidden' name=\"trilhas[".$kk."][TrilhaID]\" value='".$vv['TrilhaID']."' />
      <select name=\"trilhas[".$kk."][Data]\">
        <option value=''>".GetLangVar('nameselect')."</option>";
      $dat = $vv['Data'];
      $jj = 0;
      foreach ($datas as $ddat) {
		if ($ddat==$dat) {
			$seltxt ='selected';
		} else {
			$seltxt = '';
		}
		echo "
      <option ".$seltxt." value='".$ddat."'>".$ddat."</option>";
      }
echo "</select>
    </td>
    <td>
      <select name=\"trilhas[".$kk."][GPSName]\">
        <option value=''>".GetLangVar('nameselect')."</option>";
      $dat = $vv['GPSName'];
      $jj = 0;
      foreach ($gpsss as $ky => $gps) {
        $zk = explode("_",$ky);
        $kz = $zk[1];
		if ($kz==$dat || count($gpsss)==1) {
			$seltxt ='selected';
		} else {
			$seltxt = '';
		}
		echo "
      <option ".$seltxt." value='".$kz."'>".$gps."</option>";
      }
echo "</select>
    </td>
    <td><input type='text' name=\"trilhas[".$kk."][Name]\" value='".$vv['Name']."' /></td>
    <td><input size=10 type='text' name=\"trilhas[".$kk."][TimeStart]\" value='".$vv['TimeStart']."' /></td>
    <td><input size=10 type='text' name=\"trilhas[".$kk."][TimeEnd]\" value='".$vv['TimeEnd']."' /></td>
    <td><input size=6 type='text' name=\"trilhas[".$kk."][N_Waypoints]\" value='".$vv['N_Waypoints']."' readonly /></td>
    <td><input size=6 type='text' name=\"trilhas[".$kk."][N_Trackpoints]\" value='".$vv['N_Trackpoints']."' readonly /></td>
  </tr>";
}
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan=100%>
    <table align='center' >
      <tr>
        <td align='center' >
          <input type='hidden' name='final' value='' />
          <input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\" /> 
        </td>
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>";
}
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>