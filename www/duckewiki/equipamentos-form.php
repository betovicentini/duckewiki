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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
FazHeader($title,$body,$which_css,$which_java,$menu);


echo	
"
<br />
<table align='left' class='myformtable' cellpadding='2' cellspacing='0'>
<thead>
  <tr ><td colspan='100%'>".GetLangVar('namequipamento')."s</td></tr>
</thead>
<tbody>
<tr>
<td colspan='100%' align='center'>
<form action='equipamentos-exec.php' method='post' >
<input type=hidden name='ispopup' value='$ispopup' />
  <select name='equipamentoid' onchange=\"this.form.submit();\">
    <option value=''>".GetLangVar('nameselect')."</option>";
	$qq = "SELECT * FROM Equipamentos ORDER BY Type,Name ASC";
	$res = mysql_query($qq,$conn);
	while ($row =  mysql_fetch_assoc($res)) {
		echo "
      <option value='".$row['EquipamentoID']."' >".$row['Type']." ".$row['Name']."</option>";
	}
echo "
    </select>
</form>
</td>
</tr>
<tr>
<td colspan='100%' align='center'>
<form action='equipamentos-exec.php' method='post' >
<input type=hidden name='ispopup' value='$ispopup' />
<input type='submit' class='bsubmit' value=' ".GetLangVar('namenovo')." ' />
</form>
</td>
</tr>
</tbody>
</table>
<br />";



$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>