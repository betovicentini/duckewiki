<?php
//Este script checa se alguns campos de data
//Modificado por AV em 27 de julho de 2011. 
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
$title = 'Importar Dados Passo 03';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


//@extract($_POST);

//apaga variaveis que ja foram extraidas e nao precisam ser passadas adiante
//se existem colunas de datas nos campos indicados por voce, checa que seguem um formato esperado

	$fields = unserialize($_SESSION['fieldsign']);

if (!isset($newdata)) {
	$datafields = array('COLLYY','COLLDD','COLLMM','DATACOL','DETDATE','DETYY','DETDD','DETMM','TAGGEDDATE','DATA_MONI','REFDETDATE');
	$newwikifields = array('Ano','Day','Mes','DataColeta','DetDate','DetDateYY','DetDateDD','DetDateMM','TaggedDate','DATA_MONI','RefDetDate'); 
	$newdatafields = array();
	foreach ($fields as $kk => $vv) {
    	if (in_array($vv,$datafields)) {
			$ak = array_search($vv,$datafields);
			$newdatafields[$kk] = array($tbprefix.$newwikifields[$ak],$vv);
		}
	}

} else {
	$newdatafields = unserialize($newdata);
}
	$dataproblems = array();
	foreach ($newdatafields as $orgcol => $novascols) {
		$cll = $novascols[0];
		$brcoln = $novascols[1];
		if ($brcoln=='DATA_MONI') {
			$cll = $tbprefix.$orgcol;
		}
		if ($brcoln=='COLLYY' || $brcoln=='DETYY') {
			$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." INT(4) DEFAULT 0";
			@mysql_query($qq,$conn);
			$qq = "UPDATE `".$tbname."` SET `".$cll."`=date_check_ano(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."`=0";
			mysql_query($qq,$conn);
			$qq = "SELECT * FROM `".$tbname."` WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."`=0";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			if ($nr>0) {
				$dataproblems[$orgcol] = array("$nr registros não tem um valor valido para Ano!",'ano');
			}
		}
		if ($brcoln=='COLLDD' || $brcoln=='DETDD') {
			$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." INT(2) DEFAULT 0";
			@mysql_query($qq,$conn);
			$qq = "UPDATE `".$tbname."` SET `".$cll."`=date_check_dd(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."`=0";
			mysql_query($qq,$conn);
			$qq = "SELECT * FROM `".$tbname."` WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."`=0";
			$rr = mysql_query($qq,$conn);

			$nr = mysql_numrows($rr);
			if ($nr>0) {
				$dataproblems[$orgcol] = array("$nr registros não tem um valor valido para Dia!",'dia');
			}
		}
    	if ($brcoln=='COLLMM' || $brcoln=='DETMM') {
	    	$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." INT(2) DEFAULT 0";
			@mysql_query($qq,$conn);
			$qq = "UPDATE `".$tbname."` SET `".$cll."`=date_check_mm(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."`=0";
			$rr = mysql_query($qq,$conn);
			$qq = "SELECT * FROM `".$tbname."` WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."`=0";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			if ($nr>0) {
				$dataproblems[$orgcol] = array("$nr registros não tem um valor valido para Mes!",'mes');
			}
		}
		if ($brcoln=='DATACOL' || $brcoln=='DETDATE' || $brcoln=='REFDETDATE' || $brcoln=='TAGGEDDATE' || $brcoln=='DATA_MONI') {
			$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." DATE DEFAULT NULL";
			@mysql_query($qq,$conn);
			$qq = "UPDATE `".$tbname."` SET `".$cll."`=date_check(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."` IS NULL";
			$rr = mysql_query($qq,$conn);
			$qq = "SELECT * FROM `".$tbname."` WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$cll."` IS NULL";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			if ($nr>0) {
				$dataproblems[$orgcol] = array("$nr registros não tem um valor valido para Data!",'data');
			} elseif ($brcoln=='DATACOL' && !in_array('COLLYY',$fields) && !in_array('COLLDD',$fields) && !in_array('COLLMM',$fields)) {
				$qq = "ALTER TABLE ".$tbname." ADD COLUMN `".$tbprefix."Ano` INT(4) DEFAULT 0, ADD COLUMN `".$tbprefix."Day` INT(2) DEFAULT 0, ADD COLUMN `".$tbprefix."Mes`  INT(2) DEFAULT 0";
				@mysql_query($qq,$conn);
				$qq = "UPDATE `".$tbname."` SET `".$tbprefix."Ano`=YEAR(`".$cll."`), `".$tbprefix."Mes`=MONTH(`".$cll."`),`".$tbprefix."Day`=DAY(`".$cll."`) WHERE `".$cll."` IS NOT NULL";
				mysql_query($qq,$conn);
			}
		}
	}

//tem problemas em colunas avisa e interrompe a importacao
if (count($dataproblems)>0) {
echo "
<br />
<form action='import-data-step3.php' method='post'>
<table align='center' class='myformtable' cellpadding='7'>
<thead>
 <tr><td align='center' colspan='100%'>Os seguintes erros em colunas com Datas foram encontrados</td></tr>
 <tr class='subhead'>
  <td>Coluna</td>
  <td>Erro</td>
  <td>O que fazer?</td>
 </tr>
</thead>
<tbody>
  <input type='hidden' name='newdata' value='".serialize($newdatafields)."' />";
  unset($_POST['newdata']);
	foreach ($_POST as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
}
   $idx  = 1;
 	foreach ($dataproblems as $orgcol => $vv) {
	 	$datmm = $newdatafields[$orgcol][1];
	 	if ($datmm=='DATA_MONI') {
			$cln = $tbprefix.$orgcol;
		} else {
			$cln = $newdatafields[$orgcol][0];
		}
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
<tr bgcolor = '".$bgcolor."'>
  <td>$orgcol</td>
  <td>".$vv[0]."</td>
  <td align='center'><input id='butidx".$idx."' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em\" value='Corrigir' ";
$myurl ="checkdatas-popup.php?colname=".$cln."&orgcol=".$orgcol."&tbname=".$tbname."&buttonidx=butidx".$idx."&datatipo=".$vv[1]; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Corrigir campos de datas');\" /></td>
</tr>"; 
		$idx++;
	 }

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</tbody>
</table>
</form>";
	} 
	else {
		$datachecked= TRUE;
	}

if ($datachecked==TRUE) {
	$steps = unserialize($_SESSION['importacaostep']);
	unset($steps[0]);
	$stt = array_values($steps);
	$_SESSION['importacaostep'] = serialize($stt);
	echo "
<form name='myform' action='import-data-step3a.php' method='post'>";
//coloca as variaveis anteriores
	foreach ($_POST as $kk => $vv) {
	echo "
  	<input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
