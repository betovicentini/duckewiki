<?php
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 
if (!empty($_POST['detset'])) {
	$detset = $_POST['detset'];
	unset($_POST['detset']);
}
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
if (!empty($_GET['detset'])) {
	$detset = $_GET['detset'];
	unset($_GET['detset']);
}
$gget = cleangetpost($_GET,$conn);
@extract($gget);


HTMLheaders($body);


if (!empty($detset)) {
	$dettext = describetaxa($detset,$conn);
}
echo "
<br>
<table class='myformtable' align='left' cellpadding=\"7\">
<thead>
<tr >
<td colspan=100%>
".GetLangVar('batchidentify')."&nbsp;&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('batchidentify_help'));
	echo " onclick=\"javascript:alert('$help');\">
</td>
</tr>
</thead>
<tbody>
<form method='post' name='finalform' action='identify-batch-exec.php'>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td class='tdsmallbold'>OPÇÃO 1</td>
  <td>
    <table>
      <tr>
        <input type='hidden' id='especimenesids' name='especimenesids' value='$especimenesids'>
        <td id='especimenestxt'>$especimenestxt</td>";
			$myurl = "selectespecimene-popup.php?elementid=especimenesids&elementtxtid=especimenestxt";
				$butname = GetLangVar('nameselect')." ".mb_strtolower(GetLangVar('namecoleta'))."s";
		echo "
        <td><input type=button value='$butname' class='bblue' onclick = \"javascript:small_window('$myurl',850,400,'Select specimens');\"></td>
        <td class='tdsmallbold' align='center'>&nbsp;&nbsp;".mb_strtolower(GetLangVar('nameor'))."&nbsp;&nbsp;</td>
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
            <option selected value=''>".mb_strtolower(GetLangVar('nameselect')." ".GetLangVar('namefiltro'))."</option>";
			$qq = "SELECT * FROM Filtros WHERE EspecimenesIDS>0 AND (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FiltroName";
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
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td class='tdsmallbold'>OPÇÃO 2</td>
  <td>
    <table>
      <tr>
        <td>
          <select name='coletorid'>
            <option selected value=''>".GetLangVar('nameselect')." ".mb_strtolower(GetLangVar('namecoletor'))."</option>";
			$qq = "SELECT * FROM Pessoas ORDER BY SobreNome,PreNome";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['PessoaID']."'>".$rr['Abreviacao']." (".$rr['Prenome'].")</option>";
			}
echo "
          </select>
        </td>
        <td class='tdsmallbold' align='center'>&nbsp;&nbsp;e&nbsp;&nbsp;</td>
        <td><textarea name='colnumbers'>Digite aqui os números de coleta separados por ;</textarea></td>
      </tr>
    </table>
  </td>
</tr>
";
//taxonomia
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td class='tdsmallbold'>NOVA IDENTIFICAÇÃO</td>
  <td colspan=2>
          <table >
            <tr >
              <td class='tdformnotes' id='dettexto'>$dettext</td>
              <input type='hidden' id='detsetcode' name='detset' value='$detset' >
";
		if (empty($dettext)) {
				$butname = GetLangVar('nameselect');
			} else {
				$butname = GetLangVar('nameeditar');
		} 
		echo "
              <td><input type=button value='$butname' class='bblue' ";
			$myurl ="taxonomia-popup.php?detid=$detid&dettextid=dettexto&detsetid=detsetcode"; 
			echo " onclick = \"javascript:small_window('$myurl',800,450,'TaxonomyPopup');\"></td>
            </tr>
          </table>
        </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td colspan='100%'>
    <table align='center'>
      <tr>
        <td ><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit'></td>
</form>
<form method='post' action='identify-batch.php'>
        <td ><input type='submit' value='".GetLangVar('namereset')."' class='breset'></td>
</form>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>";

HTMLtrailers();
?>
