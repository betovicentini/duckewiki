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
$title = 'Importar Expedito 10';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$nnv = $_SESSION['fieldsign'];
$newv = unserialize($nnv);
//cadastrando cada ponto primeiro
if (!isset($pontoexpedito)) {
	$colsign = array(
	"LOCALIDADE_ESPECIFICA",
	"LONGITUDE_PONTOGPS",
	"LATITUDE_PONTOGPS");
	$pontoexpedito = array();
	foreach ($colsign as $kk) {
		$datalev = trim($newv[$kk]);
		if (!empty($datalev)) {
			$pontoexpedito[$kk] = $datalev;
		}
	}
} 
$cadastrado=0;
$totalpontos=0;
$jaexiste=0;

if (count($pontoexpedito)>0) {



} else {
	$qu = "SHOW FIELDS FROM `".$tbname."` WHERE Field LIKE '".$tbprefix."EspecimenID'";
	$ru = mysql_query($qu,$conn);
	$nru = mysql_numrows($ru);

	$datalev = trim($newv["PTID"]);
	$cll=  $tbprefix."ExpeditoID";
	$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." INT(10) DEFAULT 0";
	@mysql_query($qq,$conn);

	//echopre($newv);
	if (!empty($datalev)) {
		//cadastrar ponto buscando localidade da primeira coleta de cada ponto
		$qq = "SELECT DISTINCT ".$datalev." as pt, ".$tbprefix."DataColeta as ddat FROM `".$tbname."`";
		$rr = mysql_query($qq,$conn);
		$totalpontos = mysql_numrows($rr);
		while ($row = mysql_fetch_assoc($rr)) {
			$gpspointid =0;
			$gazid = 0;
			if ($nru>0) {
				$qu = "SELECT DISTINCT sp.GPSPointID,sp.GazetteerID FROM `".$tbname."` as tb JOIN Especimenes as sp ON tb.".$tbprefix."EspecimenID=sp.EspecimenID WHERE ".$datalev."='".$row['pt']."'";
				$res = mysql_query($qu,$conn);
				$gpspointid = array();
				$gazid = array();
				while ($rw = mysql_fetch_assoc($res)) {
					$gpid = $rw['GPSPointID']+0;
					$gaid = $rw['GazetteerID']+0;
					if ($gpid>0) {
						$gpspointid[] = $gpid;
					} elseif ($gaid>0) {
						$gazid[] = $gaid;
					}
				}
				$gpid = array_unique($gpspointid);
				$gaid = array_unique($gazid);
				$gpspointid =0;
				$gazid = 0;
				if (count($gpid)>0) {
					$gpspointid = $gpid[0];
				} elseif (count($gaid)>0) {
						$gazid = $gaid[0];
				}
			} 
				else {
				$qa = "SELECT gps.PointID as gpspoint  FROM ".$tbname." as tb JOIN GPS_DATA as gps ON gps.Name=tb.".$datalev." WHERE tb.".$datalev."='".$row['pt']."'";
				$rra = mysql_query($qa,$conn);
				$gpsrow = mysql_fetch_assoc($rra);
				$gpspointid = $gpsrow['gpspoint']+0;
				$gazid=0;
			}



			$cl = $tbprefix."OBSERVADOR";
			$qu = "SELECT DISTINCT $cl FROM `".$tbname."` as tb WHERE tb.".$datalev."='".$row['pt']."'";

			//echo $qu."<br />";
			$rres = mysql_query($qu,$conn);
			$pessoas = array();
  			while ($rwr = mysql_fetch_assoc($rres)) {
  				$pessoas[] = $rwr[$cl];
  			}
			$pessoasids = implode(";",$pessoas);
			if (empty($tempo_ini)) {
				$tempo_ini = "00:00:00";
			}
			if (empty($tempo_fim)) {
				$tempo_fim = "01:00:00";
			}
			$datacol = $row['ddat'];
			$arrayofvalues = array(
					'DataColeta' => $datacol,
					'GPSpointID' => $gpspointid,
					'GazetteerID' => $gazid,
					'PessoasIDs' => $pessoasids,
					'Time_Starts' => $tempo_ini,
					'Time_Ends' => $tempo_fim);
			if ($gazid>0) {
				$qq = "SELECT * FROM MetodoExpedito WHERE DataColeta='".$datacol."' AND  GazetteerID='".$gazid."'";
			} elseif ($gpspointid>0) {
				$qq = "SELECT * FROM MetodoExpedito WHERE DataColeta='".$datacol."' AND  GPSpointID='".$gpspointid."'";
			}
			$resul = mysql_query($qq,$conn);
			$nresul = mysql_numrows($resul);
			$expeditoid=0;
			if ($nresul==0) {
				$expeditoid = InsertIntoTable($arrayofvalues,'ExpeditoID','MetodoExpedito',$conn);
			} elseif ($nresul==1) {
				 $expd = mysql_fetch_assoc($resul);
				 $expeditoid = $expd['ExpeditoID']+0;
				 $jaexiste++;
		    }
		    if ($expeditoid>0) {
			    $cll=  $tbprefix."ExpeditoID";
				$qq = "UPDATE ".$tbname." as tb SET tb.".$cll."='".$expeditoid."' WHERE tb.".$datalev."='".$row['pt']."'";
		   		$expres = mysql_query($qq,$conn);
		   		if ($expres) {
		   			$cadastrado++;
		   		}
		    }
		}
	}
}
$_SESSION['fieldsign'] = serialize($newv);

if ($cadastrado==$totalpontos && $cadastrado>0) {
$_SESSION['fieldsign'] = serialize($newv);
echo "
<form name='myform' action='import-expedito-step11.php' method='post'>
";
//coloca as variaveis anteriores
	foreach ($ppost as $kk => $vv) {
	echo "
  	<input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
//echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
echo "
  <table cellpadding=\"1\" width='50%' align='center'>
    <tr><td class='tdsmallbold' align='center'><input type='submit' value='continuar' class='bsubmit' /></td></tr>
  </table> 
 </form>";

} else {
//unset($_SESSION['fieldsign']);
echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>NÃO FOI POSSIVEL CADASTRAR PONTOS!</td></tr>
</thead>
<tbody>
<form >";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='100%'>Os dados não foram importados!</td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='100%'><input type='submit' value='Fechar' class='bsubmit' onclick='javascript:window.close();'/></td></tr>
</form>
</tbody>
</table>";
}



$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>