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

if ($saving==1) {
   $filtroid = $filtroid+0;
   $newfiltro = 0;
	if ($filtroid==0) {
		if (empty($filtronome)) {
			echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Precisa dar um nome ao filtro!</td></tr>
  </table>";
		} 
		else {
		//COLOCA A DATA NO NOME DO FILTRO
		$curdate = date("Y-m-d");
		$filtronome = strtoupper(trim($filtronome))."_".$curdate;
		if ((empty($tipodeuso) || !isset($tipodeuso)) && ($filtroid+0)==0) {
			$tipodeuso = 1;
		}
	  	$qs = "SELECT * FROM Filtros WHERE FiltroName='".$filtronome."'";
  		$rss= mysql_query($qs,$conn);
  		$nrss = mysql_numrows($rss);
  		if ($nrss>0)  {
  			while($rsw = mysql_fetch_assoc($rss)) {
	  			$fid = $rsw['FiltroID'];
				$sql = "DELETE FROM Filtros WHERE FiltroID='".$fid."'";
				mysql_query($sql,$conn);
				$sql = "DELETE FROM FiltrosSpecs WHERE FiltroID='".$fid."'";
				mysql_query($sql,$conn);
			}
		}
		$arrayofvals = array('FiltroName' => $filtronome,  'Shared' => $tipodeuso);
		$newfiltro = InsertIntoTable($arrayofvals,'FiltroID','Filtros',$conn);
		} 
	} else {
		$newfiltro = $filtroid;
	}
	//$filtrocode = "filtroid_".$newfiltro;
	if ($newfiltro>0) {
			$qu = "SET SESSION group_concat_max_len =1000000";
			$ru = mysql_query($qu,$conn);
			if ($tbname=='checklist_pllist') {
				$sql = "DELETE FROM FiltrosSpecs WHERE FiltroID='".$newfiltro."'";
				@mysql_query($sql,$conn);
				
				$sql = "INSERT INTO FiltrosSpecs (PlantaID,FiltroID) (SELECT PlantaID,".$newfiltro." FROM `checklist_pllistUserLists` WHERE `checklist_pllistUserLists`.UserID='".$uuid."')";
				$res = mysql_query($sql,$conn);
				echo '&nbsp;';
				flush();
  			}
	  		if ($tbname=='checklist_speclist') {
		  		$sql = "DELETE FROM FiltrosSpecs WHERE FiltroID='".$newfiltro."'";
				@mysql_query($sql,$conn);
				$sql = "INSERT INTO FiltrosSpecs (EspecimenID,FiltroID) (SELECT EspecimenID,".$newfiltro." FROM `checklist_speclistUserLists` WHERE `checklist_speclistUserLists`.UserID='".$uuid."')";
				echo $sql."<br >";
				$res = mysql_query($sql,$conn);
				//echo '&nbsp;';
				flush();
			}
			//Delete temp tables
			echo "
<br />
  <table align='center' style='background-color: lightblue; color: black; font: 1.5em bold;' cellpadding=\"7\" >
    <tr><td align='center' >".GetLangVar('sucesso1')."</td></tr>
    <tr><td align='center' ><input type='button' value='".GetLangVar('nameconcluir')."' onclick=\"javascript:window.close();\" class='bsubmit'></td></tr>
  </table>";
		} 
}
else {

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
);
$which_java = array(
//"<script type='text/javascript'>
//      function getparval() {
//            var el = self.opener.window.document.getElementById('passing_ids');
//            document.getElementById('counter').value = el.innerHTML;
//      }
//</script>"      
);
$title = 'Salva um filtro';
//$body = ' onload=\"javascript:getvaluefromparent();\" '
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($gget);
//echopre($ppost);
//  <input type='hidden' id='counter' name='tempids' size='100' >

echo "
<div style='width: 50%'>
<form name='finalform' action='checklist_filtro.php' method='post'>
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='usertbname' value='".$usertbname."' />
  <input type='hidden'  name='saving' value='1'  >
<br />
<table class='myformtable' align='left' border=0 cellpadding=\"5\" cellspacing=\"0\" >
<thead>
  <tr><td colspan=2 >".GetLangVar('namesalvar')."&nbsp;".GetLangVar('namefiltro')."</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
   <td class='tdsmallboldleft'>Substituir Filtro Existente</td>
        <td>
          <select name='filtroid' >";
			echo "
            <option selected value=''>".GetLangVar('nameselect')."</option>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']."  ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
			}
	echo "
          </select>
    </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldleft'>Nome para NOVO filtro</td>
  <td><input type='text' name='filtronome' value='".$filtronome."'  size='60' style='height: 20px; color: red;' /></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldleft'>Compartilhamento</td>
  <td>
    <table>
      <tr>
        <td><input type='radio' name='tipodeuso' value='0' />&nbspPessoal</td>
        <td><input type='radio' name='tipodeuso' value='1' />&nbspCompartilhado</td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan=2  align='center'>
  <input style='cursor: pointer' type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' ></td>
</tr>
</table>
</form>
</div>
";
//onclick='javascript: getparval();' 
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
}

?>