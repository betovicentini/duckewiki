<?php
set_time_limit(0);
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

HTMLheaders($body);


if (empty($expeditoid) || $expeditoid==0 || !isset($expeditoid)) {
//cria tabela se nao existe
	$qq = "CREATE TABLE IF NOT EXISTS MetodoExpedito (
				ExpeditoID INT(10) unsigned NOT NULL auto_increment,
				DataColeta DATE,
				GPSpointID INT(10),
				HabitatID INT(10),
				PessoasIDs VARCHAR(200),
				GPSunitNames VARCHAR(300),
				Time_Starts TIME,
				Time_Ends TIME,
				AddedBy INT(10),
				AddedDate DATE,
				PRIMARY KEY (ExpeditoID)) CHARACTER SET utf8";
	mysql_query($qq,$conn);
} 

//filtra os arrays eliminando registros vazios
$pesa = unserialize($pessoasids);
$pesarr = array_filter($pesa);
$pessoasids = implode(";",$pesarr);

$gpsa = unserialize($gpsunitnames);
$gpsarr = array_filter($gpsa);
$gpsunitnames = implode(";",$gpsarr);

$erro =0;
$tin = explode(":",$tempo_ini);
$tf = explode(":",$tempo_fim);


$semmudanca = 0;
$erro=0;
$sucesso =0;
$jaexiste =0;

if (($tempo_ini!='HH:MM:SS' && $tempo_fim!='HH:MM:SS' && count($tin)!=3 && count($tf)!=3) OR $tempo_ini=='HH:MM:SS' OR $tempo_fim=='HH:MM:SS') {
	$erro++;
	echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Os horários de inicio e fim não estão no formato correto</td></tr>
</table>
<br>";
}

//faz o cadastro ou atualiza o ponto
if ($erro==0) {
	$arrayofvalues = array(
		'DataColeta' => $datacol,
		'GPSpointID' => $gpspointid,
		'HabitatID' => $habitatid,
		'PessoasIDs' => $pessoasids,
		'GPSunitNames' => $gpsunitnames,
		'Time_Starts' => $tempo_ini,
		'Time_Ends' => $tempo_fim);
		/////////////////////
		if (empty($expeditoid) || $expeditoid==0) {
						//echopre($arrayofvalues);


			$qq = "SELECT * FROM MetodoExpedito WHERE DataColeta='".$datacol."' AND  GPSpointID='".$gpspointid."'";
			$resul = mysql_query($qq,$conn);
			$nresul = mysql_numrows($resul);
			if ($nresul==0) {
				//echopre($arrayofvalues);
				$expeditoid = InsertIntoTable($arrayofvalues,'ExpeditoID','MetodoExpedito',$conn);
					if (!$expeditoid) {
					echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table>
<br>";	
						$erro++;
					} else {
						$sucesso++;
					}
			} else {
			 $jaexiste++;
		    }
		} else {
			if (empty($gpspointid)) {
					unset($arrayofvalues['GPSpointID']);
			}
			//echopre($arrayofvalues);
			$upp = CompareOldWithNewValues('MetodoExpedito','ExpeditoID',$expeditoid,$arrayofvalues,$conn);
			if (!empty($upp) && $upp>0) { 
				CreateorUpdateTableofChanges($expeditoid,'ExpeditoID','MetodoExpedito',$conn);
				$expeditoid = UpdateTable($expeditoid,$arrayofvalues,'ExpeditoID','MetodoExpedito',$conn);
				if (!$expeditoid) {
					$erro++;
				} else {
					$sucesso++;
				}
			} else {
				$semmudanca++;
			}
		} 
}


//se o cadastro foi feito ou atualizado, entao checa se tem dados de GPS que precisam ser marcados
if ($sucesso>0 || $semmudanca>0) {
	//checar se existem dados de tracks the GPS para essas pessoas e gps na data especificada. Se houver anota, se nao houver avisa
	$gpstrackcheck = array();
	$gpsptcheck = array();
	if (count($gpsarr)>0) {
		foreach ($gpsarr as $kk => $gps) {
		$pessoaid = $pesarr[$kk];
		//adiciona 30 segundos de tolerancia na hora para marcar os tracks
		$h =  strtotime($tempo_ini);
		$convert = strtotime("-30 seconds", $h);
		$tini = date('H:i:s', $convert);
	
		$h =  strtotime($tempo_fim);
		$convert = strtotime("+30 seconds", $h);
		$tfim = date('H:i:s', $convert);
	
		//checa o numero de pontos marcados no intervalo de tempo
		$qq = "SELECT * FROM GPS_DATA WHERE GPSName=".$gps." AND DateOriginal='".$datacol."' AND Type='Waypoint'
		AND TimeOriginal>'".$tini."' AND TimeOriginal<'".$tfim."'";
		//echo $qq."<br>";
		$res = mysql_query($qq,$conn);
		$nr = mysql_numrows($res);
		if ($nr>0) { //entao tem 8 pontos marcados conforme esperado
			$qq = "UPDATE GPS_DATA SET ExpeditoID='".$expeditoid."' WHERE GPSName=".$gps." AND DateOriginal='".$datacol."' AND Type='Waypoint' AND TimeOriginal>'".$tini."' AND TimeOriginal<'".$tfim."'";
			//echo $qq."<br>";
			mysql_query($qq,$conn);
			
		}
		$gpsptcheck["pssid_".$pessoaid] = $nr;
		$qq = "SELECT * FROM GPS_DATA WHERE GPSName=".$gps." AND DateOriginal='".$datacol."' AND Type='Trackpoint'
		AND TimeOriginal>'".$tini."' AND TimeOriginal<'".$tfim."'";
		$res = mysql_query($qq,$conn);
		$nr = mysql_numrows($res);
		if ($nr>0) {	
			$qq = "UPDATE GPS_DATA SET ExpeditoID='".$expeditoid."' WHERE GPSName=".$gps." AND DateOriginal='".$datacol."' AND Type='Trackpoint' AND TimeOriginal>'".$tini."' AND TimeOriginal<'".$tfim."'";
			mysql_query($qq,$conn);
		}
		//echo $qq."<br>";
		$gpstrackcheck["pssid_".$pessoaid] = $nr;
	}
	}

echo "
<br>
<table cellpadding=\"7\" align='center' class='myformtable'>
<thead>
  <tr>
    <td colspan='100%'>Resumo dos dados de GPS encontrados para o intervalo $tini a $tfim em $datacol</td>
  </tr>
  <tr class='subhead'>
    <td>".GetLangVar('namepessoa')."</td>
    <td>Número de trackspoints de GPS</td>
    <td>Número de waypoints de GPS</td>
  </tr>
</thead>
<tbody>";
foreach ($gpstrackcheck as $kk => $vv) {
	$rres = explode("_",$kk);
	$ptvv = $gpsptcheck[$kk];
	$peid = $rres[1];
	$rrr = getpessoa($peid,$abb=TRUE,$conn);
	$row = mysql_fetch_assoc($rrr);
	$pessoa = $row['Abreviacao'];
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
  <tr bgcolor = $bgcolor><td class='tdsmallboldright'>".$pessoa."</td><td align='center'>".$vv."</td><td align='center'>".$ptvv."</td></tr>";
}
echo "
  </tbody>
</table>
<br>";

}

if ($sucesso>0) {
echo "
<br>
<table cellpadding=\"5\" align='center' class='success'>
  <tr>
    <td class='tdsmallbold' align='center'>".GetLangVar('sucesso1')."</td>
  </tr>
  <form action='expedito-exec.php' method=post>
  <input type=hidden name='expeditoid' value='$expeditoid'>
  <tr>
    <td><input type=submit class='bsubmit' value=".GetLangVar('namecontinuar')."></td>
  <tr>
  </form>
</table>
<br>";
} 

if ($semmudanca>0) {
echo "
<br>
<table cellpadding=\"5\" align='center' class='erro'>
  <tr>
    <td class='tdsmallbold' align='center'>Não foi feita nenhuma mudança no registro do ponto!</td>
  </tr>
  <form action='expedito-exec.php' method=post>
  <input type=hidden name='expeditoid' value='$expeditoid'>
  <tr>
    <td><input type=submit class='bsubmit' value=".GetLangVar('namecontinuar')."></td>
  <tr>
  </form>
</table>
<br>";
} 

if ($jaexiste>0 || $erro>0) {
	if ($jaexiste>0) {
		$tt = "Já existe um ponto cadastrado para essa data e localidade";
	} else {
		$tt = "Houve erro! Corrigir!";
	}
echo "
<br>
<table cellpadding=\"5\" align='center' class='erro'>
  <tr>
    <td class='tdsmallbold' align='center'>$tt</td>
  </tr>
  <form action='novoptratter-form.php' method=post>
  <input type='hidden' name='expeditoid'  value='".$expeditoid."'>
  <input type='hidden' name='pessoasids'  value='".serialize($pessoasids)."'>
  <input type='hidden' name='gpsunitnames'  value='".serialize($gpsunitnames)."'>
  <input type='hidden' name='gpspointid'  value='".$gpspointid."'>
  <input type='hidden' name='tempo_ini'  value='".$tempo_ini."'>
  <input type='hidden' name='tempo_fim'  value='".$tempo_fim."'>
  <input type='hidden' name='datacol'  value='".$datacol."'>  
  <input type='hidden' name='final'  value='3'>  
  <tr>
    <td><input type=submit class='bsubmit' value=".GetLangVar('namevoltar')."></td>
  <tr>
  </form>
</table>
<br>";

} 



HTMLtrailers();

?>