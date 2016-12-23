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
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/geral.css\" />",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/cssmenu.css\" />"
);
$which_java = array(
"<script type=\"text/javascript\" src=\"css/cssmenuCore.js\"></script>",
"<script type=\"text/javascript\" src=\"css/cssmenuAddOns.js\"></script>",
"<script type=\"text/javascript\" src=\"css/cssmenuAddOnsItemBullet.js\"></script>"
);
$title = 'Identifica por Imagem';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<table class='myformtable' align='left' cellpadding=\"6\">
<thead>
<tr >
<td colspan='100%'>".GetLangVar('identifybyimages')."</td>
</tr>
</thead>
<form method='post' name='finalform' action='identifybyimg-exec.php'>
<input type='hidden' value='$ispopup' name='ispopup' />";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td colspan='100%'>
  <table>
    <tr>
      <td class='tdsmallbold'>".GetLangVar('nameselect')." ".GetLangVar('namefiltro')."&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('filtroid_help'));
	echo " onclick=\"javascript:alert('$help');\"></td>
      <td><select name='filtro' onchange=\"javascript:this.form.submit();\">";
		if (!empty($filtro)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
            <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
		}
			echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}
	echo "
      </select></td>
    </tr>
  </table>
</td>
</tr>
<tr><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' ></td></tr>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>