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
$title = 'Identificar várias árvores';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!empty($detset)) {
	$dettext = describetaxa($detset,$conn);
}
echo "
<br />
<table class='myformtable' align='left' cellpadding=\"7\">
<thead>
<tr >
  <td colspan='2'>".GetLangVar('namidentify')." ".GetLangVar('nameplanta')."s&nbsp;&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('batchidentify_help'));
	echo " onclick=\"javascript:alert('$help');\" /></td>
</tr>
</thead>
<tbody>
<form method='post' name='finalform' action='identify-batch-trees-save.php'>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
        <td class='tdsmallbold'>".strtoupper(GetLangVar('namefiltro'))."</td>
        <td>
          <select name='filtro'>
            <option selected value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE (PlantasIDS IS NOT NULL) AND (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}
	echo "
          </select>
        </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
        <td class='tdsmallbold'>NÚMERO DAS PLACAS&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('entertaghelp'));
		echo " onclick=\"javascript:alert('$help');\"></td>
        <td><textarea cols='60' rows='3' name='tagnumbers'>Digite aqui o número das placas separados por ;</textarea></td>
</tr>
";
//taxonomia
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>NOVA IDENTIFICAÇÃO</td>
  <td>
    <table >
      <tr >
        <td class='tdformnotes' id='dettexto'>$dettext</td>
        <input type='hidden' id='detsetcode' name='detset' value='$detset' />
        ";
		if (empty($dettext)) {
				$butname = GetLangVar('nameselect');
			} else {
				$butname = GetLangVar('nameeditar');
		} 
		echo "
        <td><input type=button value='$butname' class='bblue' ";
			$myurl ="taxonomia-popup.php?ispopup=1&detid=$detid&dettextid=dettexto&detsetid=detsetcode"; 
			echo " onclick = \"javascript:small_window('$myurl',800,450,'TaxonomyPopup');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
//taxonomia
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2'>
    <table align='center'>
      <tr>
        <td><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</form>
<form method='post' action='identfily-batch.php'>
        <td><input type='submit' value='".GetLangVar('namereset')."' class='breset' /></td>
</form>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
