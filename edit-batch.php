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
$title = 'Editando várias amostras';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<table class='myformtable' align='center' cellpadding=\"7\">
<thead>
<tr >
  <td colspan='100%'>".GetLangVar('batcheditar')."&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('batchedit_help');
	echo " onclick=\"javascript:alert('$help');\"></td>
</tr>
</thead>
<form method='post' name='finalform' action='edit-batch-exec.php'>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
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
//variaveis basicas
$vararr = 
array('Coletor',
		'Numero',
		'Taxonomia (completo)',
		'DataCol',
		'Localidade',
		'Habitat',
		'Vernacular',
		'Coletores Adicionais',
		'Projeto'); 

$variablesarr = array('coletor',
		'colnum',
		'taxonomy',
		'datacol',
		'localidade',
		'habitat',
		'vernacular',
		'addcoll',
		'projeto'); 
$nvars = count($vararr);
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>".GetLangVar('namevariaveis')." ".strtolower(GetLangVar('namecoleta')."s")."</td>
  <td>
    <select name='basicvariables[]' multiple=20>";
	$i=0;
	foreach ($vararr as $kk => $vv) {
		$value = $variablesarr[$kk];
		echo "
      <option ";
		//if ($i==3) { echo " selected ";}
		echo " value='".$value."'>".ucfirst(strtolower($vv))."</option>";	
		$i++;
	}
	echo "
    </select>
  </td>
</tr>";
//formulario variaveis
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".strtolower(GetLangVar('namevariaveis'));
		echo "&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = 'Selecione um formulário com as variáveis que deseja editar';
		echo " onclick=\"javascript:alert('$help');\"></td>
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
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdformnotes'><input type='text' align='right' size=5 name='nspec_perpage' value='20'>&nbsp;Número&nbsp;de&nbsp;registros/página</td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center'>
      <tr>
        <td><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit'></td>
</form>
<form method='post' action='edit-batch.php'>
        <td><input type='submit' value='".GetLangVar('namereset')."' class='breset'></td>
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