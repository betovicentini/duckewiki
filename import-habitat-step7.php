<?php
//este script finaliza a importacao de dados ecológicos
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
$title = 'Importar Habitat 07';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (empty($arraysubmited)) {
	$qq = "ALTER TABLE `".$tbname."` ADD COLUMN ".$tbprefix."CRMODIHABITAIDS INT(1) DEFAULT 0, ADD COLUMN ".$tbprefix."HabitatID INT(1) DEFAULT 0";
 	@mysql_query($qq,$conn);
 	$qq = "SHOW COLUMNS FROM `".$tbname."` LIKE '".$tbprefix."%'";
 	$rq = mysql_query($qq,$conn);
 	$fieldschecked = array();
 	$arrayoffields = array();
 	$monitorvars = array();
 	$estaticvars = array();
 	while ($rw = mysql_fetch_assoc($rq)) {
		$rz = $rw['Field'];
		$rzz = str_replace($tbprefix,"",$rz);
		$arrayoffields['allfield'][$rzz] = $rz;
		$rt = substr($rzz,0,8);
		if ($rt=='ESTATIC_') {
			$rz1 = str_replace('ESTATIC_',"",$rzz);
			$estaticvars[$rz1] = $rz;
			$arrayoffields['estaticvars'][$rz1] = $rz;
		}
		$rt = substr($rzz,0,14);
		if ($rt=='MONITORAMENTO_') {
			$rz1 = str_replace('MONITORAMENTO_',"",$rzz);
			$monitorvars[$rz1] = $rz;
			$arrayoffields['monitorvars'][$rz1] = $rz;
		}
 	}
 
 	$qq = "SELECT COUNT(*) as ntotal FROM `".$tbname."`";
	$rq = mysql_query($qq,$conn);
	$rw = mysql_fetch_assoc($rq);
	$ntotal = $rw['ntotal'];
	$arrayoffields['ntotal'] = $ntotal;
	$nrecstart = 0;
	$nrecs = 100;
	$arrayoffields['plins'] = 0;
	$arrayoffields['plup'] = 0;
	$arrayoffields['spins'] = 0;
	$arrayoffields['spup'] = 0;
	$arrayoffields['idsins'] = 0;
	$arrayoffields['idsups'] = 0;
	$arrayoffields['moni'] = 0;
	$arrayoffields['esta'] = 0;
	$arrayoffields['idx'] = 0;
	$arrayoffields['crmodi_habitatids'] = array();
	$arrayoffields['crmodi_especimenid'] = array();
	$arrayoffields['traitidsmoni'] = array();
	$arrayoffields['traitidsestatic'] = array();
} 
else {
	$nrecs = 100;
	$arrayoffields = unserialize($arraysubmited);
	$nrecstart = $arrayoffields['reclimit']+$nrecs;
	$ntotal = $arrayoffields['ntotal'];
}
	$arrayoffields['reclimit'] = $nrecstart;
	$ff = implode(",", $arrayoffields['allfield']);
	$qq = "SELECT ImportID,$ff FROM `".$tbname."` LIMIT $nrecstart,$nrecs";
	$rq = mysql_query($qq,$conn);
	$numrecs = mysql_numrows($rq);
	if ($numrecs>0) {
	while ($rw = mysql_fetch_assoc($rq)) {
		unset($upvars);
		$plkk = array_keys($rw);
		$plvv = array_values($rw);
		$localgazid= $rw[$tbprefix.'GazetteerID'];
		$newhabitatid= $rw[$tbprefix.'HabitatID'];
		if ($newhabitatid==0) {
		    if (!empty($rw[$tbprefix.'ParentID'])) {
		    	$parid = $rw[$tbprefix.'ParentID'];
		    } else {
			    $parid = 0;
		    }
			$arrayofvalues = array(
				'HabitatTipo' => 'Local',
				'ParentID' => $parid,
				'LocalityID' => $localgazid
			);
			$newhabitatid = InsertIntoTable($arrayofvalues,'HabitatID','Habitat',$conn);
			if ($newhabitatid>0) {
				$arrayoffields['plins']++;
				$qq = "UPDATE `".$tbname."` SET `".$tbprefix."HabitatID`='$newhabitatid' WHERE ImportID=".$rw['ImportID'];
				mysql_query($qq,$conn);
			}
		}
		if (!isset($updaterecs) && !isset($upvars)) { $upvars='';} elseif (isset($updaterecs)) { $upvars = $updaterecs;}
		//cadastra primeiro o habitat
		if (count($arrayoffields['monitorvars'])>0 && $newhabitatid>0) {
			$kk = array_intersect($plkk,$arrayoffields['monitorvars']);
			$valores = array_intersect_key($plvv,$kk);
			$tokk = array_keys($arrayoffields['monitorvars']);
			$arrayofvalues = array_combine($tokk, $valores);
			$trup = updatetraits_monitoramento_onimport_habitat($arrayofvalues,$newhabitatid,$upvars,$conn);
			if (is_array($trup)) {
				$arrayoffields['moni']++;
				$arrayoffields['traitidsmoni']  = array_merge($arrayoffields['traitidsmoni'],$trup);
			}
		}
		if (count($arrayoffields['estaticvars'])>0 && $newhabitatid>0) {
			$kk = array_intersect($plkk,$arrayoffields['estaticvars']);
			$valores = array_intersect_key($plvv,$kk);
			$tokk = array_keys($arrayoffields['estaticvars']);
			$arrayofvalues = array_combine($tokk, $valores);
			$linktype ='HabitatID';
			$linkid = $newhabitatid; 
		 	$staticup = updatetraits_estatic_onimport_habitat($arrayofvalues,$linktype,$linkid,$upvars,$conn);
		 	if (is_array($staticup)) {
		 		$nst = count($staticup);
				$arrayoffields['esta'] = $arrayoffields['esta']+$nst;
				$arrayoffields['traitidsestatic']  = array_merge($arrayoffields['traitidsestatic'],$staticup);
			} 
		}
		echo "&nbsp;";
		flush();
	}

echo "
<br />
  <table class='success' cellpadding=\"3\" align='center'>
    <tr><td align='center'>".($nrecstart+$nrecs)." registros de $ntotal importados!</td></tr>
  </table>
<br />";
	flush();
echo "
  <form name='myform' action='import-habitat-step7.php' method='post'>";
	$zz = unserialize($_SESSION['firstdefinitions']);
	foreach ($zz as $kk => $vv) {
		if (!empty($vv)) {
			echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />";
        }
	}
	echo "
     <input type='hidden' name='arraysubmited' value='".serialize($arrayoffields)."' />
    <script language=\"JavaScript\">setTimeout('document.myform.submit()',1000);</script>
</form>
  ";
  flush();
} 
	else {
	$tbb = str_replace("temp_","",$tbname);
	$filtronome = $tbb."_Importado_".$_SESSION['sessiondate'];

	if (count($arrayoffields['traitidsestatic'])>0) {
		$trs = array_unique($arrayoffields['traitidsestatic']);
		$trstr =  implode(";",$trs);
		$fomrestatic = "varEsta_".$tbb."_".$_SESSION['sessiondate'];
		$fieldsaskeyofvaluearray = array(
		'FormName' => $fomrestatic,
		'FormFieldsIDS' => $trstr,
		'Shared'=> 0
		);
		$formid = InsertIntoTable($fieldsaskeyofvaluearray,'FormID','Formularios',$conn);
		$formnome = "formid_".$formid;
		foreach ($trs as $value) {
			$sql = "UPDATE Traits SET Traits.FormulariosIDS=IF(Traits.FormulariosIDS<>'',CONCAT(Traits.FormulariosIDS,';','".$formnome."'),'".$formnome."') WHERE Traits.TraitID=".$value."";
			mysql_query($sql,$conn);
		}
	}
	if (count($arrayoffields['traitidsmoni'])>0) {
		$trs = array_unique($arrayoffields['traitidsmoni']);
		$trstr =  implode(";",$trs);
		$formmonitor = "varMoni_".$tbb."_".$_SESSION['sessiondate'];
		$fieldsaskeyofvaluearray = array(
		'FormName' => $formmonitor,
		'FormFieldsIDS' => $trstr,
		'Shared'=> 0
		);
		$formid = InsertIntoTable($fieldsaskeyofvaluearray,'FormID','Formularios',$conn);
		$formnome = "formid_".$formid;
		foreach ($trs as $value) {
			$sql = "UPDATE `Traits` SET Traits.`FormulariosIDS`=IF(Traits.`FormulariosIDS`<>'',CONCAT(Traits.`FormulariosIDS`,';','".$formnome."'),'".$formnome."') WHERE Traits.`TraitID`='".$value."'";
			mysql_query($sql,$conn);
		}
	}

echo "
<br />
<table cellpadding='5' class='myformtable' align='center'>
<thead>
 <tr><td colspan='100%'>Atenção!</td></tr>
</thead>
<tbody>";
if ($arrayoffields['plins']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  colspan='100%'>Foram inseridos ".$arrayoffields['plins']." de HABITAT LOCAL</td></tr>";
}
if ($arrayoffields['moni']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  colspan='100%'>Foram atualizadas/inseridas ".$arrayoffields['moni']." registros para variáveis de monitoramento</td></tr>";
}
if ($arrayoffields['esta']>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  colspan='100%'>Foram atualizadas/inseridas ".$arrayoffields['esta']." registros para variáveis estáticas</td></tr>";
}
if (count($arrayoffields['traitidsestatic'])>0) {
	$trs = array_unique($arrayoffields['traitidsestatic']);
	$ntr = count($trs);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
  <tr bgcolor = '".$bgcolor."'><td  colspan='100%'>$ntr variáveis estáticas foram importadas e estão agrupadas no formulário <b>$fomrestatic</b></td></tr>";
}
if (count($arrayoffields['traitidsmoni'])>0) {
	$trs = array_unique($arrayoffields['traitidsmoni']);
	$ntr = count($trs);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
  <tr bgcolor = '".$bgcolor."'><td  colspan='100%'>$ntr variáveis de monitoramento foram importadas e estão agrupadas no formulário <b>$formmonitor</b></td></tr>";
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <form action='index.php' method='post'>
  <td align='center'><input type='submit' value='".GetLangVar('nameconcluir')."' class='bsubmit' /></td>
  </form>
</tr>
</tbody>
</table>";
//LocalitySimple($gspoints=false,$all=TRUE,$conn);
//LocalitySimple($gspoints=false,$all=FALSE,$conn);
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>