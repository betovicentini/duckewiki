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
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
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
$title = 'Gerar gráficos para variáveis';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


unset($_SESSION['aYVal'],$_SESSION['aXVal'],$_SESSION['gvcateg'],$_SESSION['gvqt'],$_SESSION['resultado'],$_SESSION['gvtids'],$_SESSION['vouchersids']);
echo "
<br />
<table class='myformtable' align='left' cellpadding='5'>
<thead>
<tr >
<td colspan='100%'>".GetLangVar('variaveis_comparativas')."&nbsp;&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('variaveis_comparativas_help'));
	echo " onclick=\"javascript:alert('$help');\" /></td>
</tr>
</thead>
<tbody>
<form method='post' name='finalform' action='graph-variables-exec.php'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='destfilename' value='graph-variables-exec.php' />";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td colspan='100%'>
  <table>
    <tr>
      <td class='tdsmallbold'>".GetLangVar('nameselect')." ".GetLangVar('namefiltro')."</td>
      <td>
        <select name='filtro'>";
		if (!empty($filtro)) {
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
          <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			mysql_free_result($res);
		}
			echo "
          <option value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
          <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}
			mysql_free_result($res);
	echo "
        </select>
      </td>
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
        <td class='tdsmallbold'>".GetLangVar('nameformulario')."</td>
        <td class='tdformnotes'>
          <select name='formid' >";
		if (!empty($formid)) {
			$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
		} else {
			echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
		}
	//formularios usuario
	$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName,Formularios.AddedDate ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
	echo "
          </select>
        </td>
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
        <td><input type='radio' name='especimes' value='1' /></td>
        <td>".GetLangVar('nameamostra')."s</td>
        <td><input type='radio' name='plantas' value='1' /></td>
        <td>".GetLangVar('nameplanta')."s ".mb_strtolower(GetLangVar('namemarcada'))."s</td>
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
<form method='post' action='graph-variables-form.php'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
        <td><input type='submit' value='".GetLangVar('namereset')."' class='breset' /></td>
</form>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>