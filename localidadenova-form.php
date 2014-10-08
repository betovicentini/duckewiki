<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

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
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css'>",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
newheader($title,$body,$which_css,$which_java,$menu);


unset($_SESSION['editando']);
echo "
<br>
<table align='center' class='myformtable' cellpadding=\"5\">
<thead>
<tr>
<td colspan=100%>".GetLangVar('nameeditar')." ".strtolower(GetLangVar('nameor')." ".GetLangVar('namecadastrar')." ".GetLangVar('namegazetteer'))."</td>
</tr>
</thead>
<tbody>
<form action='localidadenova-exec.php' method='post' onsubmit=\"target_popup(this)\">";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td class='tdsmallboldright'>Localidade cadastrada:</td>
  <td>"; 
autosuggestfieldval3('search-gazetteer-new.php','locality',$locality,'localres','gazetteerid',$gazetteerid,true,60);
echo "
  </td>
  <td> <input style='cursor:pointer;' type=submit value='".GetLangVar('nameeditar')."' class='borange'></td>
</tr>
</form>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td colspan=100% align='right'><input type=button class='bblue' value='".GetLangVar('namenova')." Localidade'  onclick =\"javascript:small_window('localidadenova-exec.php',900,400,'Nova localidade');\"></td>
</tr>
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/popupform.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
newfooter($which_java,$calendar=FALSE,$footer=$menu);

?>