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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Compara Imagens';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<table class='myformtable' align='left' cellpadding=\"6\">
<thead>
  <tr ><td colspan='100%'>Ver imagens</td></tr>
</thead>
<form method='post' name='finalform' action='display-image-byspecies-exec.php'>
<input type='hidden' name='ispopup' value='".$ispopup."' />
<input type='hidden' name='submitted' value='1' />";

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallboldleft' >".GetLangVar('namenomecientifico')." 1</td>
        <td>"; autosuggestfieldval3('search-name-simple-search.php','nomesearch',$nomesearch,'nomeres','nomesciid',$nomesciid,true,60); echo "</td>
        <td class='tdformnotes' >*".GetLangVar('autosuggesttoselect')."</td>
      </tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "

<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallboldleft' >".GetLangVar('namenomecientifico')." 2</td>
        <td>"; autosuggestfieldval3('search-name-simple-search.php','nomesearch2',$nomesearch2,'nomeres2','nomesciid2',$nomesciid2,true,60); echo "</td>
        <td class='tdformnotes' >*".GetLangVar('autosuggesttoselect')."</td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' align='center'>
    <input type='submit' value='".GetLangVar('namebuscar')."' class='bsubmit' onclick=\"javascript:document.finalform.final.value='1'\" />
  </td>
</tr>
</form>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td colspan='100%'>*Selecione dois nomes quando quiser comparar</td></tr>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>