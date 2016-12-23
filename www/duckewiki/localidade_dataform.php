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
} 

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$ispopup=1;
$menu = FALSE;
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />");
$which_java = array("<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'Localidade';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
unset($_SESSION['editando']);
echo "
<br />
<table align='left' class='myformtable' cellpadding=\"5\">
<thead>
<tr>
<td colspan='3'>".GetLangVar('nameeditar')." ".mb_strtolower(GetLangVar('nameor')." ".GetLangVar('namecadastrar')." ".GetLangVar('namegazetteer'))."</td>
</tr>
</thead>
<tbody>
<form action='localidade_dataexec.php' method='post' onsubmit=\"target_popup(this)\">";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>Localidade cadastrada:</td>
  <td>"; 
autosuggestfieldval3('search-gazetteer-new.php','locality',$locality,'localres','gazetteerid',$gazetteerid,true,60);
echo "
  </td>
  <td> <input style='cursor:pointer;' type='submit' value='".GetLangVar('nameeditar')."' class='borange' /></td>
</tr>
</form>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='3' align='right'>
  <input style='cursor:pointer;'  type='button' class='bblue' value='Nova Localidade'  onclick = \"javascript:small_window('localidade_dataexec.php?ispopup=1',900,500,'Adicionar Localidade');\">
  </td>
</tr>
</tbody>
</table>";
//  //onclick =\"javascript:small_window('localidade_dataexec.php',900,400,'Nova localidade');\" />

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>