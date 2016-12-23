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

$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array();
$title = 'Exportar especímenes';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!empty($especimenesids) && empty($especimenestxt)) {
	$aa = explode(";",$especimenesids);
	$naa = count($aa);
	$especimenestxt = $naa." ".mb_strtolower(GetLangVar('nameregistro'))."s ".mb_strtolower(GetLangVar('nameselecionado'))."s";

}
unset($_SESSION['metadados']);
unset($_SESSION['destvararray']);
unset($_SESSION['qq']);
unset($_SESSION['exportnresult']);
//////////////
echo "
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr >
<td >".GetLangVar('namexportar')." ".GetLangVar('namedado')."s&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('exportardados_help').".");
	echo " onclick=\"javascript:alert('$help');\"></td>
</tr>
</thead>
<form method='post' name='finalform' action='export-especimendata-form.php'>
  <input type='hidden' name='ispopup' value='".$ispopup."'>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td >
  <table>
    <tr>
      <td class='tdsmallbold'>".GetLangVar('namefiltro')."</td>
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
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FiltroName";
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
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".mb_strtolower(GetLangVar('nameobs')."s");
		echo "&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('exportformularionotasform_help'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formnotas' >";
		if (!empty($formid)) {
			$qq = "SELECT * FROM Formularios WHERE FormID=".$formid;
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
		} else {
			echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
		}
	//formularios usuario
	if ($_SESSION['accesslevel']=='admin') {
		$where = "";
	} else {
		$where = "WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ";
	}
	$qq = "SELECT * FROM Formularios ".$where." ORDER BY FormName,Formularios.AddedDate ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
	echo "
          </select>
        </td>
      </tr>
      <tr><td>Usar modelo de descrição&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Neste caso a descrição gerada será feita utilizando o modelo descritivo do formulário. O formulário precisa ter um modelo para usar esta opção";
		echo " onclick=\"javascript:alert('$help');\" /></td><td><input type='checkbox' value='1' name='usarmodelo' /></td>
		</tr>
    </table>
  </td>
</tr>";

//formulario variaveis

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".mb_strtolower(GetLangVar('namevariavel')."s");
		echo "&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('exportformulariovariaveis_help'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes'>
          <select name='formvariables' >";
		if (!empty($formid)) {
			$qq = "SELECT * FROM Formularios WHERE FormID=".$formid;
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
        <td><input type='checkbox' value='1' name='formvarmean' />".GetLangVar('usemeanvalues')."&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('usemeanvalues_help'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
      </tr>
    </table>
  </td>
</tr>";

//habitat
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')." ".mb_strtolower(GetLangVar('namevariaveis')." ".GetLangVar('namehabitat'))."</td>	
        <td class='tdformnotes'>
          <select name='formhabitat' >";
		if (!empty($formhabitat)) {
			$qq = "SELECT * FROM Formularios WHERE FormID='".$formhabitat;
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']."</option>";
		} else {
			echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
		}
	//formularios usuario
	$qq = "SELECT * FROM Formularios  WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName,Formularios.AddedDate ASC";
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
	echo "
          </select>
        </td>
        <td><input type='checkbox' value='1' name='habmean' />".GetLangVar('usemeanvalues')."&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('usemeanvalues_help'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
//variaveis basicas
$vararr = 
array(  'Data de coleta',
'Genero+especie+infraespecifico (sem autor)',
'Genero+especie+infraespecifico (com autor)',
'Taxonomia (campos separados)',
'Localidade',
'Geo-coordenadas',
'Classe de habitat',
'Outros coletores',
'Herbários',
'No. registro do INPA',
'Vernacular',
'Projeto'); 
//'Habitat','Marcado por', 'Vernacular',

$variablesarr = array(
'datacol',
'nomenoautor',
'nomeautor',
'taxacompleto',
'localidade',
'gps',
'habitat',
'addcoll',
'herbarios',
'registroINPA',
'Vernacular',
'projeto'); 
$nvars = count($vararr);

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='center'>
      <tr><td class='tdsmallbold'>".GetLangVar('namevariaveis')." ".mb_strtolower(GetLangVar('namecoleta')."s")."</td>
      <td>
        <select name='basicvariables[]' multiple size=13>";
	foreach ($vararr as $kk => $vv) {
		$value = $variablesarr[$kk];
		echo "
          <option value='".$value."'>".ucfirst(mb_strtolower($vv))."</option>";
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
  <td >
    <table align='center'>
      <tr>
        <td>
          <input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' />
        </td>
    </form>
    <form method='post' action='export-especimenes-form.php'>
      <input type='hidden' name='ispopup' value='".$ispopup."'>
        <td><input type='submit' value='".GetLangVar('namereset')."' class='breset' /></td>
    </form>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
