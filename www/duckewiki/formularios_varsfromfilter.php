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
$ispopup=1;
$menu = FALSE;
$title = '';

if (empty($valuevar)) { $valuevar = 'addcolvalue';}
if (empty($valuetxt)) { $valuetxt = 'addcoltxt';}
if (empty($formname)) { $formname = 'coletaform';}
if (!empty($traitids)) {
	$arrayoftraists = explode(";",$traitids);
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
);
$which_java = array(
"<script type='text/javascript' >
    function SetValueInParent(formName,tagvalue,tagtxt)
    {
    varparent = self.opener.window.document.forms[formName].elements[tagvalue];
    //varparenttxt = self.opener.window.document.forms[formName].elements[tagtxt];
    var varparenttxt = self.opener.window.document.getElementById(tagtxt);
    varparenttxt.innerHTML =  document.forms['finalform'].elements['resultadotxt'].value;
    var temp = document.forms['finalform'].elements['resultado'].value;
    //alert(temp);
    varparent.value =  document.forms['finalform'].elements['resultado'].value;
    window.close();
    }    
</script>"
);
$body='';
$title = 'Seleciona variáveis de um filtro';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
if ($filtro>0) {
		$ostraits = array();
		$qwheresp =   " JOIN FiltrosSpecs as fl ON pltb.EspecimenID=fl.EspecimenID WHERE fl.FiltroID=".$filtro;
		$qwherepl =   " JOIN FiltrosSpecs as fl ON pltb.PlantaID=fl.PlantaID WHERE fl.FiltroID=".$filtro;
		if ($formdohabitat!=1 || !isset($formdohabitat)) {
		$sql1= "SELECT DISTINCT EspecimenID,TraitID FROM `Traits_variation` as pltb ".$qwheresp;
		$rz = mysql_query($sql1,$conn);
		while($rwz = mysql_fetch_assoc($rz)) {
			$ostraits[] = $rwz['TraitID']+0;
	   }
		$sql1 = "SELECT DISTINCT PlantaID,TraitID FROM `Traits_variation` as pltb ".$qwherepl;
		$rz = mysql_query($sql1,$conn);
		while($rwz = mysql_fetch_assoc($rz)) {
			$ostraits[] = $rwz['TraitID']+0;
	   }
		$sql1 = "SELECT DISTINCT PlantaID,TraitID FROM `Monitoramento` as pltb ".$qwherepl;
		$rz = mysql_query($sql1,$conn);
		while($rwz = mysql_fetch_assoc($rz)) {
				$ostraits[] = $rwz['TraitID']+0;
	   	}
		} 
		else {
			$sql1= "SELECT  DISTINCT pltb.EspecimenID,hab.TraitID FROM Especimenes as pltb JOIN Habitat_Variation  as hab USING(HabitatID) ".$qwheresp;
			//echo $sql1."<br >";
			$rz = mysql_query($sql1,$conn);
			while($rwz = mysql_fetch_assoc($rz)) {
				$ostraits[] = $rwz['TraitID']+0;
			}	
			
			@mysql_free_result($rz);
			$sql1 = "SELECT  DISTINCT pltb.EspecimenID,habvar.TraitID FROM Especimenes as pltb JOIN Habitat as hab ON hab.LocalityID=pltb.GazetteerID JOIN Habitat_Variation AS habvar ON hab.HabitatID=habvar.HabitatID  ".$qwheresp;
			//echo $sql1."<br />";
			$rz = mysql_query($sql1,$conn);
			while($rwzz = mysql_fetch_assoc($rz)) {
				$ostraits[] = $rwzz['TraitID']+0;
	   		}
	   		
			//$ostraits .= ";".$rwz['traitsids'];
			@mysql_free_result($rz);
			$sql1= "SELECT  DISTINCT pltb.PlantaID,hab.TraitID FROM Plantas as pltb JOIN Habitat_Variation as hab USING(HabitatID) ".$qwherepl;
			//echo $sql1."<br >";
			$rz = mysql_query($sql1,$conn);
			while($rwz = mysql_fetch_assoc($rz)) {
				$ostraits[] = $rwz['TraitID']+0;
			}	
	   		
	   		@mysql_free_result($rz);
			$sql1 = "SELECT  habvar.TraitID FROM Plantas as pltb JOIN Habitat as hab ON hab.LocalityID=pltb.GazetteerID JOIN Habitat_Variation AS habvar ON hab.HabitatID=habvar.HabitatID  ".$qwherepl;
			//echo $sql1."<br />";
			$rz = mysql_query($sql1,$conn);
			while($rwzz = mysql_fetch_assoc($rz)) {
				$ostraits[] = $rwzz['TraitID']+0;
	   		}
		}

		//$ostrarr = explode(";",$ostraits);
		$ostrarr = $ostraits;
		//$ostrarr = array_filter($ostrarr);
		//echopre($ostrarr);
		$ostrarr = array_count_values($ostrarr);
		arsort($ostrarr);
echo "
<form method='post' action='formularios_varsfromfilter.php'>
  <input type='hidden' name='valuevar'  value='".$valuevar."' />
  <input type='hidden' name='valuetxt'  value='".$valuetxt."' />
  <input type='hidden' name='formname'  value='".$formname."' />
  <input type='hidden' name='final'  value='1' />
<table class='myformtable' align='left' cellpadding=\"5\" width='80%' >
<thead>
<tr >
<td colspan=4>As seguintes variáveis foram encontradas</td>
</tr>
<tr class='subhead' >
<td >Variável</td><td>Caminho</td><td>Frequencia Filtro</td><td>Incluir?</td>
</tr>

</thead>
<tbody>
";
foreach($ostrarr as $traitid =>$count) {
	$sql = "SELECT TraitName, TraitTipo, PathName FROM Traits WHERE TraitID='".$traitid."'";
	$rz = mysql_query($sql,$conn);
	$rwz = mysql_fetch_assoc($rz);
	$ttipo = $rwz['TraitTipo'];
	$tp = explode("|",$ttipo);
	if ($tp[1]=='Categoria') { $bgcol = "#99CCFF";} 
	if ($tp[1]=='Quantitativo') {$bgcol = "#D6FFD6";}
	if ($tp[1]=='Texto') {$bgcol = "#D3D3D3";}
	if ($tp[1]=='Imagem') {$bgcol = "#FFE4E1";}
	$linha = "<tr style=\"background-color: $bgcol; font-size: 0.8em;\" ><td>".$rwz['TraitName']."</td><td>".$rwz['PathName']."</td>";
	$linha .= "<td>".$count."</td><td><input type='checkbox'  checked value='".$traitid."' name='novotraitids[]' ></td></tr>";
	echo $linha;
}
echo "
<tr>
  <td  colspan=4 align='center'>
          <input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' />
  </td>
</tr>
</tbody>
</table>
</form>
";
}  
elseif (!isset($final)) {
echo "

<table class='myformtable' align='left' cellpadding=\"7\">
<thead>
<tr >
<td >Extrair variáveis usadas por amostras/plantas</td>
</tr>
</thead>
<form method='post' action='formularios_varsfromfilter.php'>
  <input type='hidden' name='valuevar'  value='".$valuevar."' />
  <input type='hidden' name='valuetxt'  value='".$valuetxt."' />
  <input type='hidden' name='formname'  value='".$formname."' />
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
    <tr><td >Extraír de habitat associados</td><td><input type='checkbox'   value='1' name='formdohabitat' ></td></tr>
  </table>
</td>
</tr>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='center'>
      <tr>
        <td>
          <input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' />
        </td>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
</form>
";
}
if ($final==1) {
	$traitsfinal = implode(";",$novotraitids);
	$count = count($novotraitids);
echo "<form name='finalform' method='post' >
<input type='hidden' id='resultado'  value='".$traitsfinal."' />
<input type='hidden' id='resultadotxt' value='".$count." variáveis selecionadas' />
</form>
<span style='font-size: 1em; color: red; padding: 5px; width: 100px;'>
Precisa salvar o formulário antes de poder editar as variáveis incluídas!<br>
<input type='button' style='cursor: pointer;' value='".GetLangVar('nameconcluir')."' 
onclick=\"javascript:SetValueInParent('".$formname."','".$valuevar."','".$valuetxt."');\" />
</span>
";

}

$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>