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
$title = 'Expedição';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


if (!isset($viagemid)) {
echo "
<br />
<table class='myformtable' align='left' cellpadding='7' width='50%'>
<thead>
<tr ><td colspan='100%'>Editar/criar expedição</td></tr>
</thead>
<tbody>
<form action='expedicao-form.php' method='post'>
<input type='hidden' name='ispopup' value='$ispopup' >
";
echo "
<tr>
  <td  colspan='100%'>
    <select name='viagemid' onchange='this.form.submit()'>
      <option value=''>".GetLangVar('nameselect')."</option>
      <option value=''>------------</option>
      <option value='criar'>Criar nova</option>
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

if ($viagemid=='criar') {
	$tt = 'Nova expedição';
	unset($viagemid);
} else {
	$tt = "Editando expedição";
	if (!isset($final)) {
		$qq = "SELECT * FROM Expedicoes WHERE ViagemID=".$viagemid;
		$re = mysql_query($qq,$conn);
		if ($re) {
			$rwe = mysql_fetch_assoc($re);
			$datastart = $rwe['DateStart'];
			$dataend = $rwe['DateEnd'];
			$titulo = $rwe['Name'];
		}
	}
}

$erro=0;
if ($final==1 && (empty($datastart) || empty($dataend) || empty($titulo))) {
	echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Precisa informar todos os campos</td></tr>
</table>
<br />";
	$erro++;
}
if (!isset($final) || $erro>0) {
$bgi=1;
echo "
<br />
<table class='myformtable' cellpadding='7' align='center' >
<thead>
  <tr><td colspan='100%'>$tt</td></tr>
</thead>
<tbody>
  <form name='coletaform' action='expedicao-form.php' method='post'>
  <input type='hidden' name='ispopup' value='$ispopup' >
  <input type='hidden' name='viagemid' value='".$viagemid."' />
  ";
    if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td colspan='100%'>
      <table cellpadding='3'>
        <tr>
          <td class='tdformright'>Título</td>
          <td>
            <input type='text' name=\"titulo\" value=\"$titulo\" size='40' />
          </td>
        </tr>
      </table>
    </td>
  </tr>";
  
  if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td colspan='100%'>
      <table cellpadding='3'>
        <tr>
          <td class='tdformright'>Data de início</td>
          <td>
            <input name=\"datastart\" value=\"$datastart\" size=\"11\" readonly />
            <a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['coletaform'].datastart);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\" /></a>
          </td>
        </tr>
        <tr>
          <td class='tdformright'>Data de fim</td>
          <td>
            <input name=\"dataend\" value=\"$dataend\" size=\"11\" readonly />
            <a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['coletaform'].dataend);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\" /></a>
          </td>
        </tr>
      </table>
    </td>
  </tr>";
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value='' />
        <td align='center' ><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\" /> </td>
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>
";

} 
elseif ($final==1) {

//Create table if not exists
$qq = "CREATE TABLE IF NOT EXISTS Expedicoes (
 ViagemID INT(10) unsigned NOT NULL auto_increment,
 Name VARCHAR(200),
 DateStart DATE,
 DateEnd DATE,
 AddedBy INT(10),
 AddedDate DATE,
 PRIMARY KEY (ViagemID)) CHARACTER SET utf8";
mysql_query($qq,$conn);

$arrayofvalues = array(
		'Name' => $titulo,
		'DateStart' => $datastart,
		'DateEnd' => $dataend);

$erro=0;
$sucesso=0;
if (empty($viagemid) || $viagemid==0) {
	$viagemid = InsertIntoTable($arrayofvalues,'ViagemID','Expedicoes',$conn);
	if (!$viagemid) {
			$erro++;
		} else {
			$sucesso++;
	}
} else {
	$upp = CompareOldWithNewValues('Expedicoes','ViagemID',$viagemid,$arrayofvalues,$conn);
	if (!empty($upp) && $upp>0) { 
		CreateorUpdateTableofChanges($viagemid,'ViagemID','Expedicoes',$conn);
		$viagemid = UpdateTable($viagemid,$arrayofvalues,'ViagemID','Expedicoes',$conn);
		if (!$viagemid) {
			$erro++;
		} else {
			$sucesso++;
		}
	}
}
if ($sucesso>0) {
echo "
<br />
<table cellpadding=\"7\" align='center' class='success'>
  <tr>
    <td class='tdsmallbold' align='center'>".GetLangVar('sucesso1')."</td>
  </tr>
</table>
<table cellpadding=\"7\" align='center' >
  <form action='avistamento_trilhas.php' method=post>
  <input type='hidden' name='ispopup' value='$ispopup' >
  <input type=hidden name='viagemid' value='$viagemid'>
  <tr>
    <td><input type=submit class='bsubmit' value='Definir/editar trilhas de busca'></td>
  <tr>
  </form>
</table>
<br />";
}
}
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>