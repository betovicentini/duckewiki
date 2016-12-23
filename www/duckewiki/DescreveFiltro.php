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
"<link rel='stylesheet' type='text/css' media='screen' href='javascript/tabber/tabber.css' >",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);


$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type='text/javascript' src='javascript/jquery-latest.js'></script>",
"<script type='text/javascript'> $(document).ready(function(){ $('.toggle_container').hide(); $('h2.trigger').click(function(){ $(this).toggleClass('active').next().slideToggle('slow'); }); });</script>",
"<script type='text/javascript' src='javascript/filterlist.js'></script>"
);
$title = 'Descreve Filtro';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
if (empty($filtro)) {
echo "
<br />
<table class='myformtable' align='left' cellpadding='7'>
<thead>
<tr>
  <td colspan='100%'>".GetLangVar('namedescribe')." ".GetLangVar('namefiltro')."&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('describefilter_help');
	echo " onclick=\"javascript:alert('$help');\"></td>
</tr>
</thead>
<tbody>
<form method='post' name='finalform' action='DescreveFiltro.php'>
<input type='hidden' name='ispopup' value='".$ispopup."' />";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' >
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
			mysql_free_result($res);
		}
			echo "
            <option selected value=''>".GetLangVar('nameselect')."</option>";
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
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='left'>
        <tr>
          <td><input type='checkbox' name='especimes' value='1' /></td>
          <td class='tdsmallbold'>".GetLangVar('nameamostra')."s</td>
          <td><input type='checkbox' name='plantas' value='1' /></td>
          <td class='tdsmallbold'>".GetLangVar('nameplanta')."s ".mb_strtolower(GetLangVar('namemarcada'))."s</td>
        </tr>
      </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center'>
        <tr>
          <td><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
</form>
<form method='post' action='DescreveFiltro.php'>
<input type='hidden' name='ispopup' value='".$ispopup."' />
          <td><input type='submit' value='".GetLangVar('namereset')."' class='breset' /></td>
</form>
        </tr>
      </table>
  </td>
</tr>
</tbody>
</table>";
} 
else {
if (!isset($salvar)) {
	echo "estamos aqui";
	if ($especimes=='1') {
		$qq = "SELECT COUNT(*) as numspecs FROM FiltrosSpecs WHERE FiltroID=".$filtro." AND EspecimenID>0";
		$res = mysql_query($qq);
		$rr = mysql_fetch_assoc($res);
		$numspec = $rr['numspecs']+0;
		mysql_free_result($res);
	} else { $numspec=0;}
	echo $qq."<br >";
	if ($plantas=='1') {
		$qq = "SELECT COUNT(*) as numpl FROM FiltrosSpecs WHERE FiltroID=".$filtro." AND PlantaID>0";
		$res = mysql_query($qq);
		$rr = mysql_fetch_assoc($res);
		$numpl = $rr['numpl']+0;
		mysql_free_result($res);
		//echo $qq;
	} else { $numpl=0;}
	
	if ($numspec>0) {	
		$qq = "SELECT DISTINCT Familia FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID) JOIN FiltrosSpecs as fl ON fl.EspecimenID=Especimenes.EspecimenID WHERE FiltroID=".$filtro." AND (Familia NOT LIKE 'Indet%'  AND Familia NOT LIKE 'INDET%')  AND Identidade.FamiliaID>0";
		$res = mysql_query($qq);
		$numfamilias_spec = mysql_numrows($res);
		mysql_free_result($res);
	
		$qq = "SELECT DISTINCT Familia FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID) JOIN FiltrosSpecs as fl ON fl.EspecimenID=Especimenes.EspecimenID WHERE FiltroID=".$filtro." AND (Familia LIKE 'Indet%'  OR Familia LIKE 'INDET%') AND Identidade.FamiliaID>0";
		$res = mysql_query($qq);
		$nfamindet_spec = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT DISTINCT Familia,Genero FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Identidade.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.EspecimenID=Especimenes.EspecimenID WHERE FiltroID=".$filtro." AND (Genero NOT LIKE 'Indet%'  AND Genero NOT LIKE 'INDET%')  AND Identidade.GeneroID>0";
		$res = mysql_query($qq);
		$numgeneros_spec = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT DISTINCT Familia,Genero FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Identidade.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.EspecimenID=Especimenes.EspecimenID WHERE FiltroID=".$filtro." AND (Genero LIKE 'Indet%'  OR Genero LIKE 'INDET%') AND Identidade.GeneroID>0";
		$res = mysql_query($qq);
		$ngenindet_spec = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT DISTINCT Familia,Genero,Especie FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.EspecimenID=Especimenes.EspecimenID WHERE FiltroID=".$filtro." AND (Especie NOT LIKE 'sp.%' AND Especie NOT LIKE 'sp %' AND Especie NOT LIKE '%PP%' AND Especie NOT LIKE '%FITO%' ) AND Identidade.EspecieID>0";
		$res = mysql_query($qq);
		$numespecies_spec = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT DISTINCT Familia,Genero,Especie FROM Especimenes JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.EspecimenID=Especimenes.EspecimenID WHERE FiltroID=".$filtro." AND (Especie LIKE 'sp.%' OR Especie LIKE 'sp %' OR Especie LIKE '%PP%' OR Especie LIKE '%FITO%' ) AND Identidade.EspecieID>0";
		$res = mysql_query($qq);
		$numespecies_spec_morfos = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT DISTINCT Familia,Genero,Especie,InfraEspecie FROM Especimenes JOIN Identidade USING(DetID) LEFT JOIN Tax_InfraEspecies  ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.EspecimenID=Especimenes.EspecimenID WHERE FiltroID=".$filtro." AND (InfraEspecie NOT LIKE 'sp.%' AND InfraEspecie NOT LIKE 'sp %' AND InfraEspecie NOT LIKE '%PP%' AND InfraEspecie NOT LIKE '%FITO%') AND Identidade.InfraEspecieID>0";
		$res = mysql_query($qq);
		$numsubespecies_spec = mysql_numrows($res);
		mysql_free_result($res);	
	
		$qq = "SELECT DISTINCT Familia,Genero,Especie,InfraEspecie FROM Especimenes JOIN Identidade USING(DetID) LEFT JOIN Tax_InfraEspecies  ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.EspecimenID=Especimenes.EspecimenID WHERE FiltroID=".$filtro." AND (InfraEspecie LIKE 'sp.%' OR InfraEspecie LIKE 'sp %' OR InfraEspecie LIKE '%PP%' OR InfraEspecie LIKE '%FITO%' OR InfraEspecieNivel LIKE 'morf%')  AND Identidade.InfraEspecieID>0";
		$res = mysql_query($qq);
		$numsubespecies_spec_morfos = mysql_numrows($res);
		mysql_free_result($res);	
	}	
	
	if ($numpl>0) {	
		$qq = "SELECT DISTINCT Familia FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID) JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro." AND (Familia NOT LIKE 'Indet%'  AND Familia NOT LIKE 'INDET%')  AND Identidade.FamiliaID>0";
		$res = mysql_query($qq);
		$numfamilias_pl = mysql_numrows($res);
		mysql_free_result($res);
	
		$qq = "SELECT DISTINCT Familia FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID)  JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro." AND (Familia LIKE 'Indet%'  OR Familia LIKE 'INDET%') AND Identidade.FamiliaID>0";
		$res = mysql_query($qq);
		$nfamindet = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT DISTINCT Familia,Genero FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Identidade.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro." AND (Genero NOT LIKE 'Indet%'  AND Genero NOT LIKE 'INDET%')  AND Identidade.GeneroID>0";
		$res = mysql_query($qq);
		$numgeneros_pl = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT DISTINCT Familia,Genero FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Identidade.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND (Genero LIKE 'Indet%'  OR Genero LIKE 'INDET%') AND Identidade.GeneroID>0";
		$res = mysql_query($qq);
		$ngenindet = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT DISTINCT Familia,Genero,Especie FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND (Especie NOT LIKE 'sp.%' AND Especie NOT LIKE 'sp %' AND Especie NOT LIKE '%PP%' AND Especie NOT LIKE '%FITO%' ) AND Identidade.EspecieID>0";
		$res = mysql_query($qq);
		$numespecies_pl = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT DISTINCT Familia,Genero,Especie FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND (Especie LIKE 'sp.%' OR Especie LIKE 'sp %' OR Especie LIKE '%PP%' OR Especie LIKE '%FITO%' ) AND Identidade.EspecieID>0";
		$res = mysql_query($qq);
		$numespecies_pl_morfos = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT DISTINCT Familia,Genero,Especie,InfraEspecie FROM Plantas JOIN Identidade USING(DetID) LEFT JOIN Tax_InfraEspecies  ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND (InfraEspecie NOT LIKE 'sp.%' AND InfraEspecie NOT LIKE 'sp %' AND InfraEspecie NOT LIKE '%PP%' AND InfraEspecie NOT LIKE '%FITO%') AND Identidade.InfraEspecieID>0";
		$res = mysql_query($qq);
		$numsubespecies_pl = mysql_numrows($res);
		mysql_free_result($res);	
	
		$qq = "SELECT DISTINCT Familia,Genero,Especie,InfraEspecie FROM Plantas JOIN Identidade USING(DetID) LEFT JOIN Tax_InfraEspecies  ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND (InfraEspecie LIKE 'sp.%' OR InfraEspecie LIKE 'sp %' OR InfraEspecie LIKE '%PP%' OR InfraEspecie LIKE '%FITO%' OR InfraEspecieNivel LIKE 'morf%')  AND Identidade.InfraEspecieID>0";
		$res = mysql_query($qq);
		$numsubespecies_pl_morfos = mysql_numrows($res);
		mysql_free_result($res);
		
		//count the number of trees with det not checked
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID=0";
		$res = mysql_query($qq);
		$nummissdet = mysql_numrows($res);
		mysql_free_result($res);
				
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID) JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID=0  AND (Familia NOT LIKE 'Indet%'  AND Familia NOT LIKE 'INDET%')  AND Identidade.FamiliaID>0 AND Identidade.GeneroID=0";
		$res = mysql_query($qq);
		$missdetvalidfam = mysql_numrows($res);
		mysql_free_result($res);
	
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID) JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID=0  AND (Familia LIKE 'Indet%'  OR Familia LIKE 'INDET%') AND Identidade.FamiliaID>0 AND Identidade.GeneroID=0";
		$res = mysql_query($qq);
		$missdetmorfofam = mysql_numrows($res);
		mysql_free_result($res);
				
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Identidade.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID=0   AND (Genero NOT LIKE 'Indet%'  AND Genero NOT LIKE 'INDET%')  AND Identidade.GeneroID>0 AND Identidade.EspecieID=0";
		$res = mysql_query($qq);
		$missdetvalidgenus = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Identidade.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID=0  AND (Genero LIKE 'Indet%'  OR Genero LIKE 'INDET%') AND Identidade.GeneroID>0 AND Identidade.EspecieID=0";
		$res = mysql_query($qq);
		$missdetmorfogenus = mysql_numrows($res);
		mysql_free_result($res);
				
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Especies ON Identidade.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID=0  AND (Especie LIKE 'sp.%' OR Especie LIKE 'sp %' OR Especie LIKE '%PP%' OR Especie LIKE '%FITO%' ) AND Identidade.EspecieID>0";
		$res = mysql_query($qq);
		$missdetmorfospecies = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Especies ON Identidade.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID=0  AND (Especie NOT LIKE 'sp.%' AND Especie NOT LIKE 'sp %' AND Especie NOT LIKE '%PP%' AND Especie NOT LIKE '%FITO%' ) AND Identidade.EspecieID>0";
		$res = mysql_query($qq);
		$missdetvalidspecies = mysql_numrows($res);
		mysql_free_result($res);
				
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) LEFT JOIN Tax_InfraEspecies  ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID=0  AND (InfraEspecie NOT LIKE 'sp.%' AND InfraEspecie NOT LIKE 'sp %' AND InfraEspecie NOT LIKE '%PP%' AND InfraEspecie NOT LIKE '%FITO%') AND Identidade.InfraEspecieID>0";
		$res = mysql_query($qq);
		$missdetvalidsubspecies = mysql_numrows($res);
		mysql_free_result($res);	
	
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) LEFT JOIN Tax_InfraEspecies  ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID=0  AND (InfraEspecie LIKE 'sp.%' OR InfraEspecie LIKE 'sp %' OR InfraEspecie LIKE '%PP%' OR InfraEspecie LIKE '%FITO%' OR InfraEspecieNivel LIKE 'morf%')  AND Identidade.InfraEspecieID>0";
		$res = mysql_query($qq);
		$missdetmorfosubspecies = mysql_numrows($res);
		mysql_free_result($res);
		
		/////////////////with det checked
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID>0";  
		$res = mysql_query($qq);
		$nwithdet = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID) JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID>0 AND (Familia NOT LIKE 'Indet%'  AND Familia NOT LIKE 'INDET%')  AND Identidade.FamiliaID>0 AND Identidade.GeneroID=0";
		$res = mysql_query($qq);
		$nwithdetvalidfam = mysql_numrows($res);
		mysql_free_result($res);
	
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Familias USING(FamiliaID) JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID>0 AND (Familia LIKE 'Indet%'  OR Familia LIKE 'INDET%') AND Identidade.FamiliaID>0 AND Identidade.GeneroID=0";
		$res = mysql_query($qq);
		$nwithdetmorfofam = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Identidade.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID>0 AND (Genero NOT LIKE 'Indet%'  AND Genero NOT LIKE 'INDET%')  AND Identidade.GeneroID>0 AND Identidade.EspecieID=0";
		$res = mysql_query($qq);
		$nwithdetvalidgenuslevel = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias ON Identidade.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID>0 AND (Genero LIKE 'Indet%'  OR Genero LIKE 'INDET%') AND Identidade.GeneroID>0 AND Identidade.EspecieID=0";
		$res = mysql_query($qq);
		$nwithdetmorfogenuslevel = mysql_numrows($res);
		mysql_free_result($res);	
		
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Especies ON Identidade.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID>0 AND (Especie LIKE 'sp.%' OR Especie LIKE 'sp %' OR Especie LIKE '%PP%' OR Especie LIKE '%FITO%' ) AND Identidade.EspecieID>0";
		$res = mysql_query($qq);
		$nwidthdetmorfospecies = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Especies ON Identidade.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID>0 AND (Especie NOT LIKE 'sp.%' AND Especie NOT LIKE 'sp %' AND Especie NOT LIKE '%PP%' AND Especie NOT LIKE '%FITO%' ) AND Identidade.EspecieID>0";
		$res = mysql_query($qq);
		$nwidthdetvalidspecies = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) LEFT JOIN Tax_InfraEspecies  ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID>0 AND (InfraEspecie NOT LIKE 'sp.%' AND InfraEspecie NOT LIKE 'sp %' AND InfraEspecie NOT LIKE '%PP%' AND InfraEspecie NOT LIKE '%FITO%') AND Identidade.InfraEspecieID>0";
		$res = mysql_query($qq);
		$nwidthvalidsubspecies = mysql_numrows($res);
		mysql_free_result($res);	
	
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) LEFT JOIN Tax_InfraEspecies  ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Tax_InfraEspecies.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Tax_Especies.GeneroID=Tax_Generos.GeneroID JOIN Tax_Familias ON Tax_Generos.FamiliaID=Tax_Familias.FamiliaID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND DetbyID>0 AND (InfraEspecie LIKE 'sp.%' OR InfraEspecie LIKE 'sp %' OR InfraEspecie LIKE '%PP%' OR InfraEspecie LIKE '%FITO%' OR InfraEspecieNivel LIKE 'morf%')  AND Identidade.InfraEspecieID>0";
		$res = mysql_query($qq);
		$nwidthmorfosubspecies = mysql_numrows($res);
		mysql_free_result($res);
				
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_Especies ON Identidade.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro." AND  Tax_Especies.Morfotipo=1 AND Identidade.EspecieID>0";
		$res = mysql_query($qq);
		$numspecieslevel_nomorfos = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID)  JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro." AND Identidade.InfraEspecieID>0";
		$res = mysql_query($qq);
		$numsubspecieslevel = mysql_numrows($res);
		mysql_free_result($res);
		
		$qq = "SELECT PlantaID FROM Plantas JOIN Identidade USING(DetID) JOIN Tax_InfraEspecies ON Identidade.InfraEspecieID=Tax_InfraEspecies.InfraEspecieID JOIN Tax_Especies ON Identidade.EspecieID=Tax_Especies.EspecieID  JOIN Tax_Generos ON Identidade.GeneroID=Tax_Generos.GeneroID  JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro." AND Tax_InfraEspecies.InfraEspecieNivel='morfossp' AND Identidade.InfraEspecieID>0";
		$res = mysql_query($qq);
		$numsubspecieslevel_nomorfos = mysql_numrows($res);
		mysql_free_result($res);
		
		//count the number of trees with dbh not entered
		$qq = "SELECT DISTINCT PlantaID FROM Plantas LEFT JOIN Monitoramento USING(PlantaID)  JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro." AND TraitID='".$daptraitid."' AND (TraitVariation IS NULL OR TraitVariation=0)";
		$res = mysql_query($qq);
		$nummissdbh = mysql_numrows($res);
		mysql_free_result($res);
	}
	if ($numspec>0) {
	echo "
<br />
<h2 class='trigger'><a href='#'>Especímenes - Resumo Taxonômico</a></h2>
<div class='toggle_container'>
<div class='block'>
  <table class='myformtable' align='left' cellpadding='5'>
    <tbody>  
    <tr><td>
      <table class='myformtable' cellpadding='10'>
        <tbody>  
        <tr class='dottedborder'>
          <td >&nbsp;</td>
          <td >Famílias</td>
          <td >Gêneros</td>
          <td >Espécies</td>
          <td >Infra-espécies</td>
        </tr>
        <tr>
          <td>Nomes válidos</td>
          <td align='center'>$numfamilias_spec</td>
          <td align='center'>$numgeneros_spec</td>
          <td align='center'>$numespecies_spec</td>
          <td align='center'>$numsubespecies_spec</td>
        </tr>
        <tr>
          <td>Morfotipos</td>
          <td align='center'>$nfamindet_spec</td>
          <td align='center'>$ngenindet_spec</td>
          <td align='center'>$numespecies_spec_morfos</td>
          <td align='center'>$numsubespecies_spec_morfos</td>
        </tr>
        <tr class='dottedborder'>
          <td><b>Total</b>
          </td><td align='center'><b>".($numfamilias_spec+$nfamindet_spec)."</b></td>
          <td align='center'><b>".($numgeneros_spec+$ngenindet_spec)."</b></td>
          <td align='center'><b>".($numespecies_spec+$numespecies_spec_morfos)."</b></td>
          <td align='center'><b>".($numsubespecies_spec+$numsubespecies_spec_morfos)."</b></td>
        <tr>
        </tbody>
    </table>
  </td></tr>
  </tbody>
  </table>
</div>
</div>  
  ";
	}
	if ($numpl>0) {
		echo "
<h2 class='trigger'><a href='#'>Plantas marcadas - Resumo Taxonômico</a></h2>
<div class='toggle_container'>
<div class='block'>
      <table class='myformtable' cellpadding='10'>
        <tbody>  
        <tr class='dottedborder'>
          <td >&nbsp;</td>
          <td >Famílias</td>
          <td >Gêneros</td>
          <td >Espécies</td>
          <td >Infra-espécies</td>
        </tr>
        <tr>
          <td>Nomes válidos</td>
          <td align='center'>$numfamilias_pl</td>
          <td align='center'>$numgeneros_pl</td>
          <td align='center'>$numespecies_pl</td>
          <td align='center'>$numsubespecies_pl</td>
        </tr>
        <tr>
          <td>Morfotipos</td>
          <td align='center'>$nfamindet</td>
          <td align='center'>$ngenindet</td>
          <td align='center'>$numespecies_pl_morfos</td>
          <td align='center'>$numsubespecies_pl_morfos</td>
        </tr>
        <tr class='dottedborder'>
          <td><b>Total</b>
          </td><td align='center'><b>".($numfamilias_pl+$nfamindet)."</b></td>
          <td align='center'><b>".($numgeneros_pl+$ngenindet)."</b></td>
          <td align='center'><b>".($numespecies_pl+$numespecies_pl_morfos)."</b></td>
          <td align='center'><b>".($numsubespecies_pl+$numsubespecies_pl_morfos)."</b></td>
        <tr>
        </tbody>
    </table>
</div>
</div>
<h2 class='trigger'><a href='#'>Plantas marcadas - Nível de Identificação</a></h2>
<div class='toggle_container'>
<div class='block'>
    <table class='myformtable' cellpadding='10'>
      <tbody >  
      <tr class='dottedborder'><td align='center' colspan='5'>Número total de plantas no filtro: $numpl</td></tr>
      <tr class='dottedborder'>
        <td colspan='5'>Amostras examinadas [".($numpl-$nummissdet)." (".round((($numpl-$nummissdet)/$numpl)*100,0)."%)]</td>
      </tr>
      <tr>
        <td >Nível&nbsp;de&nbsp;Identificação</td>
        <td align='center'>Só&nbsp;família</td>
        <td align='center'>Só&nbsp;gênero</td>
        <td align='center'>Até&nbsp;espécie</td>
        <td align='center'>Até&nbsp;infra-espécie</td>
      </tr>
      <tr>
        <td>Nomes válidos</td>
        <td align='center'>$nwithdetvalidfam</td>
        <td align='center'>$nwithdetvalidgenuslevel</td>
        <td align='center'>$nwidthdetvalidspecies</td>
        <td align='center'>$nwidthvalidsubspecies</td>
      </tr>
      <tr>
        <td>Morfotipos</td>
        <td align='center'>$nwithdetmorfofam</td>
        <td align='center'>$nwithdetmorfogenuslevel</td>
        <td align='center'>$nwidthdetmorfospecies</td>
        <td align='center'>$nwidthmorfosubspecies</td>
      </tr>
      <tr class='dottedborder'>
        <td colspan='5'>Plantas sem determinador [".($nummissdet)." (".round(($nummissdet/$numpl)*100,0)."%)]</td></tr>
      <tr>
        <td >Nível&nbsp;de&nbsp;Identificação</td>
        <td align='center'>Só&nbsp;família</td>
        <td align='center'>Só&nbsp;gênero</td>
        <td align='center'>Até&nbsp;espécie</td>
        <td align='center'>Até&nbsp;infraespécie</td>
      </tr>
      <tr>
        <td>Nomes válidos</td>    
        <td align='center'>$missdetvalidfam</td>
        <td align='center'>$missdetvalidgenus</td>
        <td align='center'>$missdetvalidspecies</td>
        <td align='center'>$missdetvalidsubspecies</td>
      </tr>
      <tr>
        <td>Morfotipos</td>    
        <td align='center'>$missdetmorfofam</td>
        <td align='center'>$missdetmorfogenus</td>
        <td align='center'>$missdetmorfospecies</td>
        <td align='center'>$missdetmorfosubspecies</td>
      </tr>";
			$famtotal = $nwithdetvalidfam+$nwithdetmorfofam+$missdetvalidfam+$missdetmorfofam;
			$gentotal = $nwithdetvalidgenuslevel+$nwithdetmorfogenuslevel+$missdetvalidgenus+$missdetmorfogenus;
			$speciestotal = $nwidthdetvalidspecies+$nwidthdetmorfospecies+$missdetvalidspecies+$missdetmorfospecies;
			$subspeciestotal = $nwidthvalidsubspecies+$nwidthmorfosubspecies+$missdetvalidsubspecies+$missdetmorfosubspecies;
		echo "
      <tr class='dottedborder'>
        <td>Total</td>
        <td align='center'>".($famtotal)." (".round(($famtotal/$numpl)*100,0)."%)</td>
        <td align='center'>".($gentotal)." (".round(($gentotal/$numpl)*100,0)."%)</td>
        <td align='center'>".($speciestotal)." (".round(($speciestotal/$numpl)*100,0)."%)</td>
        <td align='center'>".($subspeciestotal)." (".round(($subspeciestotal/$numpl)*100,0)."%)</td>
      </tr>
    </tbody>
  </table>
</div>
</div>
<h2 class='trigger'><a href='#'>Plantas marcadas - Dados de Monitoramento</a></h2>
<div class='toggle_container'>
<div class='block'>
<table class='myformtable' align='left' cellpadding='5'>
<tbody>
  <tr class='dottedborder'><td colspan='100%'>Número de plantas faltando DAP: $nummissdbh</td></tr>
  <tr>
    <td colspan='100%'>
      <table align='center'>
        <tr>
          <td>
            <form method='post' action='DescreveFiltro.php'>
              <input type='hidden' name='ispopup' value='".$ispopup."' />
              <input type='hidden' name='filtro' value='".$filtro."' />
              <input type='hidden' name='salvar' value='1' />
              <input type='submit' value='Salvar' class='bsubmit'/>&nbsp;filtros para PP: miss_dap + miss_identificacao?
            </form>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</tbody>
</table>
</div>
</div>
";
	}
} 
else {
		$qq = "DROP TABLE tempPPdescribefilter_".$_SESSION['userid'];
		@mysql_query($qq,$conn);
		
		$qq = "DELETE FROM Filtros WHERE FiltroName LIKE '%_missdata%'";
		mysql_query($qq,$conn);
		
		$qq = "CREATE TABLE tempPPdescribefilter_".$_SESSION['userid']." (SELECT PlantaID,PlantaTag,'dbh' as MissData FROM Plantas LEFT JOIN Monitoramento USING(PlantaID) JOIN Gazetteer USING(GazetteerID)  JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro." AND TraitID='".$daptraitid."' AND (TraitVariation IS NULL OR TraitVariation=0 OR TraitVariation='')) UNION        (SELECT PlantaID,PlantaTag,'dataobs' as MissData FROM Plantas LEFT JOIN Monitoramento USING(PlantaID) JOIN Gazetteer USING(GazetteerID)  JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro." AND TraitID=".$daptraitid." AND (DataObs IS NULL OR DataObs=0)) UNION (SELECT PlantaID,PlantaTag,'det' as MissData FROM Plantas LEFT JOIN Identidade USING(DetID)  JOIN FiltrosSpecs as fl ON fl.PlantaID=Plantas.PlantaID WHERE FiltroID=".$filtro."  AND Identidade.EspecieID=0)";
		mysql_query($qq,$conn);
		//echo $qq."<br />";
		$qu = "ALTER TABLE tempPPdescribefilter_".$_SESSION['userid']." ADD COLUMN tempid INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
  		mysql_query($qu,$conn);
  	
		$qq = "SELECT SUM(LENGTH(PlantaID) + 1) - 1 as nlength, count(*) as nrecs FROM tempPPdescribefilter_".$_SESSION['userid'];
  		$res = mysql_query($qq,$conn);
	  	$rr = mysql_fetch_assoc($res);
  		$steps_needed = ceil(($rr['nlength']+1)/1024)+1;
	  	$total_recs = $rr['nrecs'];
		$nrecs_per_step = ceil($total_recs/$steps_needed);
		$start = 0;
		$end = $nrecs_per_step;
		$i=0;
		$last = $total_recs+$nrecs_per_step;
		while ($end<=$last) {
			if ($i>0) {
				$qqq .= ", "; 
			} else {
				$qqq = '';
			}
			$i++;
			$qqq .= " GROUP_CONCAT(IF(tempid BETWEEN ".$start." AND ".$end.", CONCAT(';', PlantaID), '') SEPARATOR '')";
			$start = $end+1;
			$end = $start+$nrecs_per_step;
		}
		$qq = "select CONCAT(".$qqq.") as specids from  tempPPdescribefilter_".$_SESSION['userid']." WHERE PlantaID>0 AND PlantaID IS NOT NULL";
		$res = mysql_query($qq,$conn);
		$rr = mysql_fetch_assoc($res);
		$newplantasids = substr($rr['specids'],1,strlen($rr['specids']));
		$filtronome = $_SESSION['userlastname']."_missdata";
		$arrayofvals = array('FiltroName' => $filtronome, 'PlantasIDS' => $newplantasids);
		$filtroid = InsertIntoTable($arrayofvals,'FiltroID','Filtros',$conn);
		if ($filtroid>0) {
			$sql = "INSERT INTO FiltrosSpecs (PlantaID,FiltroID) (SELECT PlantaID,".$filtroid." FROM tempPPdescribefilter_".$_SESSION['userid'].")";
			$updatedpls = mysql_query($sql,$conn);
			if ($updatedpls) {
				$salvo++;
			}
		}
		if ($salvo>0) {
			echo "
<br />
  <table align='center' class='success'>
    <tr>
      <td>O filtro $filtronome foi salvo para árvores sem determinação ou sem DAP anotado</td>
    </tr>
  </table>";
	}
} //end if salvar
} //end if filtro
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>