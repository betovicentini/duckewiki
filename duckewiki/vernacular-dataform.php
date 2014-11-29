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
if (!isset($ispopup)) {
	$ispopup=1;
}
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
$title = GetLangVar('namenovo')." ".GetLangVar('namevernacular');
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//SE ESTIVER EDITANDO
if (!isset($submitted)) {
echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
<tr><td colspan='100%'>".GetLangVar('namecadastrar')." ".strtolower(GetLangVar('namevernacular'))."</td></tr>
</thead>
<tbody>
<form action='vernacular_dataform.php' method='post'>
  <input type='hidden' value='editando' name='submitted' />
<tr>
<td class='tdformnotes'>
  <select name='vernacularid' onchange='this.form.submit()';>";
			if (!isset($vernacularid)) {
				echo "
    <option value=''>".GetLangVar('nameselect')." ".strtolower(GetLangVar('nameeditar'))."</option>";
			} else {
				$wr = getvernacular($vernacularid,$conn);
				$ww = mysql_fetch_assoc($wr);
				echo "
    <option  selected value='".$ww['VernacularID']."'>".$ww['Vernacular']."</option>";
			}
			echo "
    <option value=''>----</option>";
			$wrr = getvernacular('',$conn);
			while ($aa = mysql_fetch_assoc($wrr)){
				echo "
    <option value='".$aa['VernacularID']."'>".$aa['Vernacular']."</option>";
			}
	echo "
  </select>
</td>
</form>
<form action='vernacular-dataform.php' method='post'>
  <input type='hidden' value='novo' name='submitted' />
<td class='tdformnotes' align='center'><input type='submit' value='".GetLangVar('namenovo')." ".GetLangVar('namecadastro')."' class='bsubmit' /></td>
</form>
</tr>
</tbody>
</table>
<br />
";
} 
else {
	if ($submitted=='editando') {
		$_SESSION['editando']=1;
		$wr = getvernacular($vernacularid,$conn);
		$ww = mysql_fetch_assoc($wr);
		$nome = $ww['Vernacular'];
		$lingua = $ww['Language'];
		$definicao = $ww['Definition'];
		$referencia = $ww['Reference'];
		$taxonomyids = $ww['TaxonomyIDS'];
		$obs = $ww['Notes'];
		$ttxt =  GetLangVar('nameeditando')." ".strtolower(GetLangVar('namecadastro'))." ".$nome;
	} 
	elseif ($submitted=='novo') {
		unset($_SESSION['editando']);
		$ttxt = GetLangVar('namenovo')." ".strtolower(GetLangVar('namecadastro'));
	}
	if (!empty($taxonomyids)) {
		$specieslist = describetaxacomposition($taxonomyids,$conn,$includeheadings=TRUE);
	}
echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
<tr><td colspan='100%'>".$ttxt."</td></tr>
</thead>
<tbody>
<form action='vernacular_datasave.php' method='post'>
  <input type='hidden' value='$vernacularid' name='vernacularid' />
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namenome')."*</td>
  <td class='tdformnotes'><input type='text' name='nome' size='30%' value='$nome' /></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namelanguage')."</td>
  <td class='tdsmallbold' >
    <input type='text' name='lingua' size='20%' value='$lingua' /> ".strtolower(GetLangVar('nameor'))."&nbsp;<select name='lingua2'>";
			if (!empty($lingua)) {
				echo "
        <option value=''>".GetLangVar('nameselect')."</option>";
			} else {
				echo "
        <option value='".$lingua."'>".$lingua."</option>";
			}
			$qq = "SELECT DISTINCT Language FROM Vernacular ORDER BY Language";
			$rr = mysql_query($qq,$conn);
			while ($row = mysql_fetch_assoc($rr)) {
				echo "
        <option value='".$row['Language']."'>".$row['Language']."</option>";
			}
	echo "
      </select>
    </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namesignificado')."</td>
  <td class='tdformnotes' ><textarea name='definicao' cols=40 rows='2' wrap=SOFT>$definicao</textarea></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namereference')."</td>
  <td class='tdformnotes' colspan='2'><input type='text' name='referencia' size='30%' value='$referencia' /></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namespecies')."</td>
  <td >
    <table>
      <tr>
        <td class='tdsmalldescription'><input type='hidden' id='specieslistids' name='taxonomyids' value='$taxonomyids'>";
		if (empty($specieslist)) {
			echo "<textarea rows='2' cols='50' id='specieslist' name='specieslist' readonly>$specieslist</textarea>";
		} else {
			echo "$specieslist<input type='hidden' id='specieslist' name='specieslist' value='$specieslist'></td>";
		}
		echo 
		"</td>
        <td><input type='button' value='<<' class='bsubmit' ";
			$myurl ="selectspeciespopup.php?formname=vernacularform&elementname=specieslistids&destlistlist=".$taxonomyids;
			echo " onclick = \"javascript:small_window('$myurl',500,400,'SelectSpecies');\" /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('nameobs')."</td>
  <td class='tdformnotes' ><textarea name='obs' cols='40' rows='5'>$obs</textarea></td>
</tr>
";



if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' align='center'><input type='submit' class='bsubmit' value=".GetLangVar('namesalvar')." /></td>
</tr>
</form>
</tbody>
</table>
";
} // else submitted

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>