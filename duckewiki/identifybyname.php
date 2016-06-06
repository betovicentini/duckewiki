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
$ispopup==1;
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Substituir um nome';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!empty($_POST['detset'])) {
	$detset = $_POST['detset'];
	unset($_POST['detset']);
}
if (!empty($_GET['detset'])) {
	$detset = $_GET['detset'];
	unset($_GET['detset']);
}
echo "
<br />
<table class='myformtable' align='center' cellpadding=\"5\">
<thead>
<tr >
<td colspan='100%'>
".GetLangVar('identifybyname')."&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('identifybyname_help'));
	echo " onclick=\"javascript:alert('$help');\" />
</td>
</tr>
</thead>
<tbody>
<form method='post' name='finalform' action='identifybyname-exec.php'>
<input type='hidden' name='ispopup' value='$ispopup' >
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameselect')." ".GetLangVar('namefiltro')."&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('filtroid_help'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td>
          <select name='filtro'>";
		if (!empty($filtro)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
            <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
		}
			echo "
            <option selected value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1  ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}

	echo "
          </select>
        </td>
        <!--- 
        <td><input type='checkbox' name='tipoid[]' value='especimes' /></td>
        <td>".GetLangVar('nameamostra')."s</td>
        <td><input type='checkbox' name='tipoid[]' value='plantas' /></td>
        <td>".GetLangVar('nameplanta')."s ".strtolower(GetLangVar('namemarcada'))."s</td>
        --->
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallboldleft' >".GetLangVar('nametoreplace')."&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('nametoreplace_help'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td>"; autosuggestfieldval3('search-name-simple-search.php','nomesearch',$nomesearch,'nomeres','nomesciid',$nomesciid,true,60); echo "</td>
        <td class='tdformnotes' >*".GetLangVar('autosuggesttoselect')."</td>
      </tr>
    </table>
  </td>
</tr>";
//taxonomia
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallboldleft'>".GetLangVar('namenova')." ".GetLangVar('nameidentificacao')."</td>
        <td  align='left'>
          <table >
            <tr >
              <td class='tdformnotes' id='dettexto'>$dettext</td>";
		if (empty($dettext)) {
				$butname = GetLangVar('nameselect');
			} else {
				$butname = GetLangVar('nameeditar');
		} 
		echo "
              <td>
                <input type='hidden' id='detsetcode' name='detset' value='$detset' />
                <input type=button value='$butname' class='bsubmit' ";
			$myurl ="taxonomia-popup.php?ispopup=1&detid=$detid&dettextid=dettexto&detsetid=detsetcode"; 
			echo " onclick = \"javascript:small_window('$myurl',800,450,'TaxonomyPopup');\" /></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>
<tr>
  <td colspan='100%'>
    <table align='center'>
      <tr>
        <td><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</form>
<form method='post' action='identifybyname.php'>
<input type='hidden' name='ispopup' value='$ispopup' >
        <td><input type='submit' value='".GetLangVar('namereset')."' class='breset' /></td>
</form>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>