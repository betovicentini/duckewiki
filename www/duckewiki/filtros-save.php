<?php
//SALVA O FILTRO REALIZADO
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

//echopre($gget);
//CABECALHO
$ispopup=1;
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = "Salvar filtro";
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


	
if (!isset($saving)) {
	$qq = "SELECT COUNT(DISTINCT PlantaID) as NPlantas FROM ".$tbname."  WHERE PlantaID>0 AND PlantaID IS NOT NULL";
	$rs = mysql_query($qq,$conn);
	$spec = @mysql_fetch_assoc($rs);
	$nplt = $spec['NPlantas']+0;

	$qq = "SELECT COUNT(DISTINCT EspecimenID) as NEspecimenes FROM ".$tbname."  WHERE EspecimenID>0 AND EspecimenID IS NOT NULL";
	$rs = mysql_query($qq,$conn);
	$spec = @mysql_fetch_assoc($rs);
	$nspecs = $spec['NEspecimenes']+0;
	
echo "
<br />
<table class='myformtable' align='left' cellpadding='10'>
<thead>
  <tr><td colspan='2'>".GetLangVar('nameresultado')."s </td></tr>
</thead>
<tbody>";
if ($nspecs>0) {
echo "
<tr>
  <td><b>".$nspecs."</b> ".mb_strtolower(GetLangVar('nameamostra'))."s (especimenes)</td>
    <td>";
//echo  "<form action='checklist_specimens.php' method='post' target='_blank' />
//    <input type=hidden name='ispopup' value='".$ispopup."' />
//  <input type='hidden' name='quicktbname'  value='".$tbname5."' />
//  <input type='hidden' name='quickview'  value='1' />
//  <input type=submit value='visualizar' class='bblue' />
//  </form>
//  
echo "
    </td>
 </tr>";
 }
 if ($nplt>0) {
echo "
<tr> 
  <td><b>".$nplt."</b> ".mb_strtolower(GetLangVar('nameplanta'))."s marcadas</td>
  <td></td>";
//echo  "
//  <form action='checkllist_plantas.php' method='post' target='_blank' />
//  <input type=hidden name='ispopup' value='".$ispopup."' />
//  <input type='hidden' name='quicktbname'  value='".$tbname5."' />
//  <input type='hidden' name='quickview'  value='1' />
//  <input type=submit value='visualizar' class='bblue' />
//  </form>
echo "
    </td>
 </tr>";
}
echo "
  <tr>
  <td colspan='2' align='center'>
    <form name='finalform' action=filtros-save.php method='post'>
      <input type='hidden'  name='tbname' value='".$tbname."'  >
      <input type='hidden'  name='ispopup' value='".$ispopup."'  >
      <input type='hidden'  name='saving' value='1'  >
      <input type='submit' value='".GetLangVar('namesalvar')." ".mb_strtolower(GetLangVar('namefiltro'))."' class='bsubmit'  />
  </form>
  </td>
  </tr>
</tbody>
</table>
";

} else {

$qq = "SELECT COUNT(DISTINCT PlantaID) as NPlantas FROM ".$tbname." WHERE Ntimes=Ncriteria AND PlantaID>0";
$rs = mysql_query($qq,$conn);
$spec = mysql_fetch_assoc($rs);

$nplt = $spec['NPlantas'];
$qq = "SELECT COUNT(DISTINCT EspecimenID) as NEspecimenes FROM ".$tbname." WHERE Ntimes=Ncriteria AND EspecimenID>0";
$rs = mysql_query($qq,$conn);
$spec = mysql_fetch_assoc($rs);
$nspecs = $spec['NEspecimenes'];

$qq = "ALTER TABLE ".$tbname."  ADD PRIMARY KEY (`tempid`)"; 
@mysql_query($qq,$conn);

if ($final!='1' && !isset($filtroid)) {
echo "
<br />
<table class='myformtable' align='left' border=0 cellpadding=\"5\" cellspacing=\"0\" >
<thead>
  <tr><td colspan='2'>".GetLangVar('namesalvar')."&nbsp;".GetLangVar('namefiltro')."</td></tr>
</thead>
<tbody>
<form name='finalform' action=filtros-save.php method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden'  name='saving' value='1'  >
  <input type='hidden' name='final' value='1' />";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldleft'>OPÇÃO&nbsp;1:&nbsp;entre&nbsp;o&nbsp;nome&nbsp;para&nbsp;um&nbsp;novo&nbsp;filtro</td>
  <td><input type='text' name='filtronome' value='$filtronome' /></td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldleft'>OPÇÃO&nbsp;2:&nbsp;adicione&nbsp;ou&nbsp;substitua&nbsp;registros&nbsp;do&nbsp;filtro</td>
  <td>
    <table>
      <tr>
        <td colspan='3'>
          <select name='filtroid'>
            <option selected value=''>".GetLangVar('nameselect')."</option>";
				$qq = "SELECT * FROM Filtros WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FiltroName";
				$res = @mysql_query($qq,$conn);
				while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']."</option>";
					}
				echo "
          </select>
        </td>
      </tr>
      <tr>
        <td><input type='radio' name='fitroadd' value='1' />&nbspadicionar</td>
        <td><input type='radio' name='fitroadd' value='2' />&nbspsubstituir</td>
        <td><input type='radio' name='fitroadd' value='3' />&nbsptirar do filtro</td>
      </tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldleft'>Qual o tipo de uso desse filtro?</td>
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
  <td colspan='100%' align='center'><input style='cursor: pointer' type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit'></td>
</tr>
</table>
</form>
";

} 
else {
//echopre($ppost);
//echo $filtroid."<br />";
$nres=0;
if (isset($filtronome) && ($filtroid+0)==0) {
	$qq = "SELECT * FROM Filtros WHERE LOWER(FiltroName)='".mb_strtolower($filtronome)."' AND AddedBy=".$_SESSION['userid'];
	$oldres = @mysql_query($qq,$conn);
	$nres = @mysql_numrows($oldres);
	$old = @mysql_fetch_assoc($oldres);
	$oldfiltroid = $old['FiltroID'];
} 
elseif ($filtroid>0) {
	$qq = "SELECT * FROM Filtros WHERE FiltroID=".$filtroid;
	$oldres = @mysql_query($qq,$conn);
}
if ($nres==1 && ($filtroid+0)==0 && empty($decided)) {
echo "
<br />
<form action='filtros-save.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
<input type='hidden' name='tbname' value='".$tbname."' />
<input type='hidden' name='filtro' value='$filtro' />
<input type='hidden' name='filtroid' value='$oldfiltroid' />
<input type='hidden' name='filtronome' value='$filtronome' />
<input type='hidden' name='tipodeuso' value='$tipodeuso' />
<input type='hidden' name='final' value='1' />
<input type='hidden' name='decided' value='1' />
<input type='hidden' name='saving' value='1' />  
<table class='erro' align='center' cellpadding=\"5\" >
  <tr><td colspan='3'>Já existe um filtro com esse nome</td></tr>
  <tr>
    <td class='tdformnotes'><input type='radio' name='fitroadd' value=1 onchange='this.form.submit()' />Adiciona</td>
    <td class='tdformnotes'><input type='radio' name='fitroadd' value=2 onchange='this.form.submit()' />Substitui</td>
    <td class='tdformnotes'><input type='radio' name='fitroadd' value=3 onchange='this.form.submit()' />Tira do filtro</td>
  </tr>
</table>
</form>";
}  
else {
	if ($fitroadd==1 && $filtroid>0) {
		$qz = "CREATE  TABLE  `".$tbname."_add` (  `tempid` int( 10  )  unsigned NOT  NULL DEFAULT  '0', `EspecimenID` int( 10  )  DEFAULT NULL , `PlantaID` int( 10  )  DEFAULT NULL , `Ntimes` int( 10  )  DEFAULT NULL , `NCriteria` int( 10  )  DEFAULT NULL , PRIMARY  KEY (  `tempid`  ) , KEY  `EspecimenID` (  `EspecimenID`  ) , KEY  `PlantaID` (  `PlantaID`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8";
		msql_query($qz);
		$qz = "INSERT INTO `".$tbname."_add` SELECT * FROM `".$tbname."`";
		msql_query($qz);
		$qz = "INSERT INTO ".$tbname."_add (EspecimenID,PlantaID) (SELECT EspecimenID,PlantaID FROM Especimenes  
JOIN FiltrosSpecs as fl ON fl.EspecimenID=Especimenes.EspecimenID WHERE fl.FiltroID=".$filtroid.")";
		msql_query($qz);
		$qz = "INSERT INTO ".$tbname."_add (PlantaID) (SELECT PlantaID FROM Plantas  JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE fl.FiltroID=".$filtroid.")";
		msql_query($qz);
		$qu = "DROP TABLE ".$tbname;
		msql_query($qu);
		$qz = "CREATE  TABLE  `".$tbname."` (  `tempid` int( 10  )  unsigned NOT  NULL DEFAULT  '0', `EspecimenID` int( 10  )  DEFAULT NULL , `PlantaID` int( 10  )  DEFAULT NULL , `Ntimes` int( 10  )  DEFAULT NULL , `NCriteria` int( 10  )  DEFAULT NULL , PRIMARY  KEY (  `tempid`  ) , KEY  `EspecimenID` (  `EspecimenID`  ) , KEY  `PlantaID` (  `PlantaID`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8";
		msql_query($qz);
		$qz = "INSERT INTO `".$tbname."` (EspecimenID,PlantaID) (SELECT DISTINCT EspecimenID,PlantaID) FROM `".$tbname."_add`";
		msql_query($qz);
	}
	$qu = "SELECT COUNT(DISTINCT PlantaID) as NPL FROM ".$tbname." WHERE PlantaID>0";
	$ru = mysql_query($qu,$conn);
	$rz = mysql_fetch_assoc($ru);
	$npl = $rz['NPL'];
	$qu = "SELECT COUNT(DISTINCT EspecimenID) as NPL FROM ".$tbname." WHERE EspecimenID>0";
	$ru = mysql_query($qu,$conn);
	$rz = mysql_fetch_assoc($ru);
	$nspecs = $rz['NPL'];
	
	if ((empty($tipodeuso) || !isset($tipodeuso)) && ($filtroid+0)==0) {
		$tipodeuso = 1;
	}
	if ($filtroid>0) {
		$oldres = @mysql_query($qq,$conn);
		$oldresw = mysql_fetch_assoc($oldres);
		if (empty($filtronome)) {
			$filtronome = $oldresw['FiltroName'];
		} 
		if (empty($tipodeuso)) {
			$tipodeuso = $oldresw['Shared'];
		} 
		$arrayofvals = array('Shared' => $tipodeuso,'FiltroName' => $filtronome);
		//echopre($arrayofvals);
		$newfiltro = UpdateTable($filtroid,$arrayofvals,'FiltroID','Filtros',$conn); 
		$newfiltro = $filtroid;
		
	} 
	else {
		$arrayofvals = array('FiltroName' => $filtronome,  'FiltroDefinitions' => $_SESSION['searchcriteria'], 'Shared' => $tipodeuso);
		$newfiltro = InsertIntoTable($arrayofvals,'FiltroID','Filtros',$conn);
		//$filtronome = $newfiltro;
	}
	if ($newfiltro) {
		$qu = "SET SESSION group_concat_max_len =1000000";
		$ru = mysql_query($qu,$conn);
		if ($npl>0) {
			if ($fitroadd==3) {
					$sql = "DELETE FiltrosSpecs
FROM FiltrosSpecs JOIN ".$tbname."  as tb ON FiltrosSpecs.PlantaID=tb.PlantaID  WHERE FiltrosSpecs.FiltroID=".$newfiltro;
					mysql_query($sql,$conn);
				echo '&nbsp;';
				flush();
			} else {
				//$qu= "UPDATE Filtros SET PlantasIDS=(SELECT GROUP_CONCAT(DISTINCT PlantaID SEPARATOR ';') FROM ".$tbname." WHERE (PlantaID+0)>0) WHERE FiltroID='".$newfiltro."'";

  				if ($fitroadd==2 || !isset($fitroadd)) {
					$sql = "DELETE FROM FiltrosSpecs WHERE FiltroID=".$newfiltro." AND PlantaID>0 AND (EspecimenID=0 OR EspecimenID IS NULL)";
					mysql_query($sql,$conn);
					echo '&nbsp;';
					flush();
				} 
				$qu = "INSERT INTO FiltrosSpecs (PlantaID,FiltroID) (SELECT DISTINCT PlantaID,".$newfiltro." FROM ".$tbname." WHERE (PlantaID+0)>0)";
				$ru = mysql_query($qu,$conn);
				if (!$ru) {
					$updatedpls++;
				}
				echo '&nbsp;';
				flush();
			}
			
  		}
  		if ($nspecs>0) {
  			if ($fitroadd==2 || !isset($fitroadd)) {
  					$sql = "DELETE FROM FiltrosSpecs WHERE FiltroID=".$newfiltro." AND EspecimenID>0 AND (PlantaID=0 OR PlantaID IS NULL)";
					mysql_query($sql,$conn);
			} 
  			$qu = "INSERT INTO FiltrosSpecs (EspecimenID,FiltroID) (SELECT DISTINCT EspecimenID,".$newfiltro." FROM ".$tbname." WHERE (EspecimenID+0)>0)";
		   $ru = mysql_query($qu,$conn);
			if (!$ru) {
				$updatedspecs++;
			}
		}
		if ($updatedspecs || $updatedpls) {
			echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>".GetLangVar('erro2')."</td></tr>
  </table>";
		} 
		else {
			//Delete temp tables
			$qq = "DROP TABLE ".$tbname;
			mysql_query($qq,$conn);
			
			$qq=  "CREATE TABLE ".$tbname." SELECT DISTINCT FiltroID,EspecimenID,PlantaID FROM FiltrosSpecs WHERE FiltroID=".$newfiltro;
			//echo $qq."<br >";
			mysql_query($qq,$conn);
			$sql = "DELETE FROM FiltrosSpecs WHERE FiltroID=".$newfiltro;
			mysql_query($sql,$conn);
			//echo $sql."<br >";
			
			$qu = "INSERT INTO FiltrosSpecs (FiltroID,EspecimenID,PlantaID) (SELECT DISTINCT * FROM ".$tbname.")";
			mysql_query($qu,$conn);
//echo $qu."<br >";
			
			//$qq = "DROP TABLE ".$tbname;
			//mysql_query($qq,$conn);
			echo "
<br />
  <table class='success' align='center' cellpadding=\"5\" >
    <tr><td>".GetLangVar('sucesso1')."</td></tr>
    <tr><td><input type='button' value='".GetLangVar('nameconcluir')."' onclick=\"javascript:window.close();\" class='bsubmit'></td></tr>
  </table>";
		}
	} 
	else {
echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Erro!!!!</td></tr>
  </table>";
	}
}
}

}


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>