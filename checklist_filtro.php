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
  				$fcode = "filtroid_".$fid;
  				$sql = "UPDATE Plantas SET Plantas.FiltrosIDS=removefromfiltrosids(Plantas.FiltrosIDS,'".$fcode."') WHERE Plantas.FiltrosIDS LIKE '%".$fcode."' OR Plantas.FiltrosIDS LIKE '%".$fcode.";%'";
				mysql_query($sql,$conn);
				$sql = "UPDATE Especimenes SET Especimenes.FiltrosIDS=removefromfiltrosids(Especimenes.FiltrosIDS,'".$fcode."') WHERE FiltrosIDS LIKE '%".$fcode."' OR FiltrosIDS LIKE '%".$fcode.";%'";
				mysql_query($sql,$conn);
				$sql = "DELETE FROM Filtros WHERE FiltroID='".$fid."'";
				mysql_query($sql,$conn);
			}
		}
		$arrayofvals = array('FiltroName' => $filtronome,  'Shared' => $tipodeuso);
		$newfiltro = InsertIntoTable($arrayofvals,'FiltroID','Filtros',$conn);
		} 
	} else {
		$newfiltro = $filtroid;
	}
	$filtrocode = "filtroid_".$newfiltro;
	if ($newfiltro>0) {
			$qu = "SET SESSION group_concat_max_len =1000000";
			$ru = mysql_query($qu,$conn);
			if ($tbname=='checklist_pllist') {
				$qu= "UPDATE Filtros SET PlantasIDS=(SELECT GROUP_CONCAT(DISTINCT PlantaID SEPARATOR ';') FROM `checklist_pllistUserLists` WHERE (PlantaID+0)>0 AND UserID='".$uuid."') WHERE FiltroID='".$newfiltro."'";
  				$ru = mysql_query($qu,$conn);
				$sql = "UPDATE Plantas SET Plantas.FiltrosIDS=removefromfiltrosids(Plantas.FiltrosIDS,'".$filtrocode."') WHERE Plantas.FiltrosIDS LIKE '%".$filtrocode."' OR Plantas.FiltrosIDS LIKE '%".$filtrocode.";%'";
				mysql_query($sql,$conn);
				$sql = "UPDATE `Plantas`,`checklist_pllistUserLists` SET Plantas.FiltrosIDS=updatefiltrotag(`Plantas`.FiltrosIDS,'".$filtrocode."') WHERE `Plantas`.PlantaID=`checklist_pllistUserLists`.PlantaID AND `checklist_pllistUserLists`.UserID='".$uuid."'";
				$res = mysql_query($sql,$conn);
				echo '&nbsp;';
				flush();
  			}
	  		if ($tbname=='checklist_speclist') {
				$qu= "UPDATE Filtros SET EspecimenesIDS=(SELECT GROUP_CONCAT(DISTINCT EspecimenID SEPARATOR ';') FROM `checklist_speclistUserLists`  WHERE (EspecimenID+0)>0 AND UserID='".$uuid."') WHERE FiltroID='".$newfiltro."'";
				//echo $qu."<br >";
  				$ru = mysql_query($qu,$conn);
				$sql = "UPDATE Especimenes SET Especimenes.FiltrosIDS=removefromfiltrosids(Especimenes.FiltrosIDS,'".$filtrocode."') WHERE FiltrosIDS LIKE '%".$filtrocode."' OR FiltrosIDS LIKE '%".$filtrocode.";%'";
				//echo $sql."<br >";
				mysql_query($sql,$conn);
				$sql = "UPDATE `Especimenes`,`checklist_speclistUserLists` SET `Especimenes`.FiltrosIDS=updatefiltrotag(`Especimenes`.FiltrosIDS,'".$filtrocode."')  WHERE `Especimenes`.EspecimenID =`checklist_speclistUserLists`.EspecimenID AND `checklist_speclistUserLists`.UserID='".$uuid."'";
				//echo $sql."<br >";
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
<table class='myformtable' align='center' border=0 cellpadding=\"5\" cellspacing=\"0\" >
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