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
$title = 'Exportar especímenes';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


if (!empty($especimenesids) && empty($especimenestxt)) {
	$aa = explode(";",$especimenesids);
	$naa = count($aa);
	$especimenestxt = $naa." ".strtolower(GetLangVar('nameregistro'))."s ".strtolower(GetLangVar('nameselecionado'))."s";

}

//////////////
echo "
<br />
<table class='myformtable' align='center' cellpadding=\"5\">
<thead>
<tr >
<td colspan='100%'>".GetLangVar('namexportar')." ".GetLangVar('namedado')."s&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('exportardados_help').".");
	echo " onclick=\"javascript:alert('$help');\" /></td>
</tr>
</thead>
<form method='post' name='finalform' action='progress-bar.php'>
  <input type='hidden' name='destfilename' value='export-exec.php' />";

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namecoleta')."s</td>
        <td id='especimenestxt'>$especimenestxt</td>";
			$myurl = "selectespecimene-popup.php?elementid=especimenesids&elementtxtid=especimenestxt";
			if (empty($especimenesids)) {
				$butname = GetLangVar('nameselect');
			} else {
				$butname = GetLangVar('nameeditar');
			} 
			echo "
        <td>
          <input type='hidden' id='especimenesids' name='especimenesids' value='$especimenesids' />
          <input type=button value='$butname' class='bsubmit' onclick = \"javascript:small_window('$myurl',850,400,'HabitatPopUp');\" />
        </td>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td class='tdsmallbold'>".strtolower(GetLangVar('nameor')." ".GetLangVar('nameselect')." ".GetLangVar('namefiltro'))."</td>
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
			$qq = "SELECT * FROM Filtros ORDER BY FiltroName";
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
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".strtolower(GetLangVar('nameobs')."s");
		echo "&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('exportformularionotasform_help'));
		echo " onclick=\"javascript:alert('$help');\" />
        </td>
        <td class='tdformnotes'>
          <select name='formnotes' >";
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
	$qq = "SELECT * FROM Formularios ORDER BY FormName,Formularios.AddedDate ASC";
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
//formulario variaveis
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".strtolower(GetLangVar('namevariavel')."s");
		echo "&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('exportformulariovariaveis_help'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formvariables' >";
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
	$qq = "SELECT * FROM Formularios ORDER BY FormName,Formularios.AddedDate ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
	echo "
          </select>
        </td>
        <td><input type='checkbox' value='1' name='meanvalues' />".GetLangVar('usemeanvalues')."&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('usemeanvalues_help'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
      </tr>
    </table>
  </td>
</tr>";

//variaveis basicas
$exportbasicvariables = GetLangVar('exportbasicvariables');
$exportbasicvariables_vars = GetLangVar('exportbasicvariables_vars');
$variablesarr = explode(";",$exportbasicvariables_vars);
$vararr = explode(";",$exportbasicvariables);
$nvars = count($vararr);

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>".GetLangVar('namevariaveis')." ".strtolower(GetLangVar('namecoleta')."s")."</td>
  <td>
    <select name='basicvariables[]' multiple=10>";
	foreach ($vararr as $kk => $vv) {
		$value = $variablesarr[$kk];
		echo "
      <option selected value='".$value."'>".ucfirst(strtolower($vv))."</option>";
	}
	echo "
    </select>
  </td>
</tr>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center'>
      <tr>
        <td><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
    </form>
    <form method='post' action='export-form.php'>
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