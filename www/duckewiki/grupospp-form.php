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
$title = 'Grupos de Espécies';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<table align='center' cellpadding='6' class='myformtable' >
<thead>
<tr >
  <td colspan='3'>".GetLangVar('namespeciesgroups')."&nbsp;<img src=\"icons/icon_question.gif\" ";
		$help = GetLangVar("namespeciesgroups_help");
		echo " onclick=\"javascript:alert('$help');\" /></td>
</tr>
</thead>
<tbody>
<form action='grupospp-exec.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='editing' value='yes' />
<tr>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameeditar')." ".GetLangVar('namespeciesgroups')."</td>
        <td class='tdformnotes'>
            <select name='groupid' onchange='this.form.submit();'>";
		if (!empty($groupid)) {
				$query = "SELECT * FROM  Tax_SpeciesGroups WHERE GroupID='$groupid'";
				$res = mysql_query($query,$conn);	
				$row = mysql_fetch_assoc($res);
				echo "
              <option value ='".$row['GroupID']."'><i>".$row['GroupName']."</i></option>";
		} else {
				echo "
              <option value=''>".GetLangVar('nameselect')."</option>";
		}
				$query = "SELECT * FROM  Tax_SpeciesGroups ORDER BY GroupName";
				$rs = mysql_query($query,$conn);	
				while ($rww = mysql_fetch_assoc($rs)) {
					echo "
              <option value = ".$rww['GroupID']."><i>".$rww['GroupName']."</i></option>";
				}
	echo "
            </select>
        </td>
      </tr>
    </table>
  </td>
  <td>&nbsp;</td>
</form>
<form action='grupospp-exec.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <td><input type='submit' value='".GetLangVar('namenovo')." ".GetLangVar('namespeciesgroups')."' class='bsubmit' /></td>
</tr>
</form>
<form action='grupospp_resumo.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
<tr>
  <td colspan='3'><input type='submit' value='Exporta resumo' class='bblue' /></td>
</tr>
</form>

</tbody>
</table>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>