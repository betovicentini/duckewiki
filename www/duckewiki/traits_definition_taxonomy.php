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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/filterlist.js'></script>",
"<script>
        function pegadado() {
            var el = self.opener.window.document.getElementById('taxahtm_".$traitid."');
            el.innerHTML = 'Mudando';
            }
        function CallParentWindowFunction() {
          var txt = document.getElementById('result').value;
          window.opener.ChangeTaxa(".$rowid.",".$clidx.",".$traitid.",txt);
          window.close(); 
        } 
</script>"
);

$title = 'Seleciona taxa';
$body = "onload='pegadado();' ";
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
//echopre($gget);

//SE ENVIOU SALVA OS RESULTADOS
if ($final==1) {
	//OLD
	$qu = "SELECT GROUP_CONCAT(nomeid SEPARATOR ';')  as ids FROM TraitsLinkedTaxa WHERE TraitID='".$traitid."'";
	$resu = mysql_query($qu,$conn);
	$rowu = mysql_fetch_assoc($resu);
	$arridsold = explode(";",$rowu['ids']);

	$arrids = explode(";",$nomeids_selected);

	//TRIM VALORES
	function test_alter(&$item1) {
	   $item1 = trim($item1); 
    }
   array_walk($arrids, 'test_alter');

	//REGISTROS PARA APAGAR
	$arridff = array_diff ( $arridsold, $arrids);
	$count =0;
	foreach ($arridff as $namid) {
		$namid = trim($namid);
		CreateorUpdateTableofChanges($namid,'TraitLinkID','TraitsLinkedTaxa',$conn);
		$qz = "DELETE FROM TraitsLinkedTaxa WHERE nomeid='".$namid."' AND TraitID='".$traitid."'";
		mysql_query($qz,$conn);
		$count++;
	}
	
	//REGISTROS PARA INSERIR
	$arrins = array_diff ( $arrids, $arridsold);
	foreach ($arrins as $vv) {
			$vv = trim($vv);
			$qz = "SELECT * FROM TaxonomySimple WHERE nomeid='".$vv."'";
			$rz = mysql_query($qz,$conn);
			$rw = mysql_fetch_assoc($rz);
			$arrvv = array(
					'TraitID' => $traitid,
					'FamiliaID' => $rw['FamiliaID'],
					'GeneroID' => $rw['GeneroID'],
					'nomeid' => $vv
				);
			$nid = InsertIntoTable($arrvv,'TraitLinkID','TraitsLinkedTaxa',$conn);
			if ($nid) {
				$count++;
			} else {
				echo "não inseriu ".$vv."<br />";
			}
	}
	
	$qu = "SELECT gettraittaxa(pltb.TraitID) AS TAXA FROM Traits as pltb WHERE pltb.TraitID='".$traitid."'";
	$ru = mysql_query($qu,$conn);
	$rwu = mysql_fetch_assoc($ru);
	$taxa = $rwu['TAXA'];
	
	$tbname = 'temp_TraitsEditDefinitions_'.$uuid;
	$qu = "UPDATE ".$tbname." SET TAXA='".$taxa ."' WHERE TraitID='".$traitid."'";
	$ru = mysql_query($qu,$conn);
	//echo $taxa."      aqui né po<br >";
	echo "
<br>
<input type='hidden' id='result'  value='".$taxa."' >
<table align='center' class='success' cellpadding=\"5\">
<tr><td>Foram salvas ".$count." modificações com sucesso!</td></tr>
<tr>
<td>
    <input type=\"button\" class=\"bsubmit\" value=\"Fechar\"  onclick='javascript: CallParentWindowFunction();' />
</td></tr>
</table>
<br>
";
	
} 
else { //SE NAO ENVIOU ABRE SELECIONADOR
//echo "<input type=\"button\" class=\"bsubmit\" value=\"Testar\"  onclick=\"javascript: CallParentWindowFunction('esta familia');\" />
echo "
<br />
<form method='post' name='labelform'>";
echo "
<table class='myformtable' align='left' cellpadding=\"5\">
<thead>
<tr ><td colspan='3'>Seleciona famílias ou gêneros</td></tr>
</thead>
<tbody>
<tr class='tabsubhead'>
  <td>".GetLangVar('namedisponivel')."</td>
  <td>&nbsp;</td>
  <td>".GetLangVar('nameselecionado')."</td>
</tr>
<tr>
  <td>
        <select name='srcList' multiple size='10' style=\"width:400px;\">";
		$filtro ="SELECT * FROM `TaxonomySimple` WHERE EspecieID IS NULL AND ((`FamiliaID`+0)>0 OR (`GeneroID`+0)>0) AND `Familia`<>'' ORDER BY `nome` ASC";
		$res = mysql_query($filtro,$conn);
		while ($aa = mysql_fetch_assoc($res)){
			$nid = trim($aa['nomeid']);
			$genid = $aa['GeneroID'];
			if ($genid>0) {
				$cor='background-color: #D6FFD6; ';
				$nn = "".$aa['nome']." [".($aa['Familia'])."]";
			} else {
				$cor='background-color: #99CCFF; ';
				$nn = ($aa['nome']);
			}
			
				echo "          
        <option style='".$cor." font-size: 1.8em;' value='".$nid."' >".$nn."</option>";
	}
echo "
        </select>
  </td>
  <td align='center'>
    <input type='button' value=' >> ' class='breset' onClick=\"javascript:addSrcToDestList('labelform');\">
    <br />";
//if ($_SESSION['editando']!=1) {
	echo "
  <br />
  <input type='button' value=' << ' class='breset' onclick=\"javascript:deleteFromDestList('labelform');\">";
//}
echo "
  </td>
  <td>
        <select name='destList' multiple size='10' style=\"max-width:300px;\">";
		If ($traitid>0) {
				$filtro = "SELECT * FROM  `TaxonomySimple` as tb JOIN `TraitsLinkedTaxa` as lktb ON tb.nomeid=lktb.nomeid WHERE lktb.TraitID='".$traitid."' ORDER BY `nome` ASC";
		$res = mysql_query($filtro,$conn);
		while ($aa = mysql_fetch_assoc($res)){
			$nid = $aa['nomeid'];
			$genid = $aa['GeneroID'];
			if ($genid>0) {
				$cor='background-color: #D6FFD6; ';
				$nn = "".$aa['nome']." [".($aa['Familia'])."]";
			} else {
				$cor='background-color: #99CCFF; ';
				$nn = ($aa['nome']);
			}
			
				echo "          
        <option style='".$cor." font-size: 1.8em;' selected value='".$nid."' >".$nn."</option>";
			}
		}
echo "
        </select>
</td>
</tr>
<script type=\"text/javascript\">
<!--
var myfilter = new filterlist(document.labelform.srcList);
//-->
</script>    
    <tr>
      <td colspan='3' >
        <table cellpadding='5'>
          <tr>
            <td>Filtrar:</td>
            <td><input name='regexp' onKeyUp=\"myfilter.set(this.value);\" /></td>
            <td><input type='button' onclick=\"myfilter.set(this.form.regexp.value)\" value=\"Filtrar\" /></td>
            <td><input type='button' onclick=\"myfilter.reset();this.form.regexp.value=''\" value=\"Limpar\" /></td>
          </tr>
          <tr><td colspan='4'><input type='checkbox' name=\"toLowerCase\" onclick=\"myfilter.set_ignore_case(!this.checked);\" />&nbsp;Case sensitive</td></tr>
         </table>
     </td>
    </tr>
</form>
<form method='post' name='finalform' action='traits_definition_taxonomy.php'>
  <input type='hidden' name='nomeids_selected' value='".$nomeids_selected."' />
  <input type='hidden' name='elementtxtid' value='".$elementtxtid."' />
  <input type='hidden' name='final' value='1' />";
  foreach ($gget as $kk => $vv) {
echo "
<input type='hidden' name='".$kk."'  value='".$vv."'  >";
}  
echo "
</form>
<tr>
  <td colspan='3'>
    <table align='center'>
      <tr>
        <td><input type='button' value='".GetLangVar('nameenviar')."' class='bsubmit' onClick = \"javascript:sendarrayatoself('labelform','destList','finalform','nomeids_selected');\" /></td>
      </tr>
    </table>
   </td>
</tr>
</table>
";
//echo $filtro."<br >";
}
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>

