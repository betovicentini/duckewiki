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
$title = 'Monitoramento Série';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<table class='myformtable' align='left' cellpadding=\"6\">
<thead>
<tr ><td colspan='100%'>".GetLangVar('namemonitoramento')."</td></tr>
</thead>
<form method='post' name='finalform' action='monitoramento-batch-exec.php'>";
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
			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
			$res = @mysql_query($qq,$conn);
			$rr = @mysql_fetch_assoc($res);
			echo "
            <option selected value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
		}
			echo "
            <option selected value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE PlantasIDS<>'' AND (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}
	echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>
";
//formulario variaveis
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".mb_strtolower(GetLangVar('namevariaveis'));
		echo "&nbsp;<img height='14' src=\"icons/icon_question.gif\" ";
		$help = 'none';
		echo " onclick=\"javascript:alert('$help');\" />
        </td>
        <td class='tdformnotes'>
          <select name='formid'>";
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

//taxonomia
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallbold'>Editar taxonomia</td>
        <td class='tdformnotes'><input type='radio' name='includetaxonomia' value='1' />&nbsp;Rápida</td>
        <td class='tdformnotes'><input type='radio' name='includetaxonomia' value='2' />&nbsp;Detalhada</td>
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
        <td class='tdsmallbold'>Censo</td>
        <td class='tdformnotes'>
          <select name='censo'>
            <option value=''>".GetLangVar('nameselect')."</option>";
		foreach (range(1, 100, 1) as $number) {
			echo "
            <option value='".$number."'>".$number."</option>";
		}
		echo "
          </select>
        </td>
        <td>* se editando dados</td>
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
        <td class='tdsmallbold'>".GetLangVar('namedata')." OBS</td>
        <td><input name=\"datanew\" value=\"$datanew\" size=\"11\" readonly /></td>
        <td>
          <a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['finalform'].datanew);return false;\" >
            <img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\">
          </a>
        </td>
        <td>* se entrando dados</td>
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
        <td class='tdsmallbold'>Número registros por pag.</td>
        <td class='tdformnotes'><input type='text' size='5' name='nspec_perpage' value='20' /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center'>
      <tr>
        <td>
          <input type='hidden' name='submitted' value='1' />
          <input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' />
        </td>
    </form>
    <form method='post' action='monitoramento-batch-form.php'>
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
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>