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
$title = 'Duplica Formulário';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$erro=0;
if ($enviado=='1' && !empty($formulario)) {
	$qq = "SELECT * FROM `Formularios` WHERE `FormID`='".$formid."'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	if ($row['FormName']==$formulario) {
		$formulario = $row['FormName'].'_copy';
	}
	$fieldsaskeyofvaluearray = array(
		'FormName' => $formulario,
		'FormFieldsIDS' => $row['FormFieldsIDS'],
		'Shared' =>  $row['Shared']
		);
	$newformid = InsertIntoTable($fieldsaskeyofvaluearray,'FormID','Formularios',$conn);
	$qn = "SELECT GROUP_CONCAT(DISTINCT TraitID SEPARATOR ';') as traits FROM FormulariosTraitsList WHERE FormID='".$formid."'";
	$rer = mysql_query($qn,$conn);
	$rww = mysql_fetch_assoc($rer);
	$traitarr = explode(";",$rww['traits']);
	$traitarr = array_unique($traitarr);
	$result = implode(";",$traitarr);
	if (!$newformid) {
		$erro++;
		echo " erro 4<br />";
	} 
	else {
		$formnome = "formid_".$newformid;
		$updated=0;
		//DEPRECATE (MAS AINDA IMPEMENTADO , SALVA COMO FIELD A LISTA DE TRAITS
		if (count($traitarr)>0) {
			foreach ($traitarr as $value) {
					$vv = $value+0;
					$sql = "UPDATE `Traits` SET Traits.`FormulariosIDS`=IF(Traits.`FormulariosIDS`<>'',CONCAT(Traits.`FormulariosIDS`,';','".$formnome."'),'".$formnome."') WHERE Traits.`TraitID`='".$vv."'";
					$upsql = mysql_query($sql,$conn);
					if ($upsql) {
						$updated++;
					}
			}
		}
		if ($updated>0 && $updated==count($traitarr)) {} 
			else {
					$erro++;
					//echo " erro 5<br />";
				}
	}

	if ($erro==0) {
		$qn = "DELETE FROM FormulariosTraitsList WHERE FormID='".$newformid."'";
		@mysql_query($qn,$conn);
		$nz = count($traitarr);
		$count = 0;
		foreach ($traitarr as $tri) {
			$tri = $tri+0;
			if ($tri>0) {
			$qz = "INSERT INTO FormulariosTraitsList (FormID,TraitID,Ordem) SELECT ".$newformid.",TraitID,".$count." FROM Traits WHERE TraitID='".$tri."'";
			$rr = mysql_query($qz,$conn);
			if ($rr) {
				$count++;
			}
			}
		}
	}
	if ($erro==0) {
					echo "
<br />
  <table align='center' class='success' cellpadding=\"5\" cellspacing=0 width='50%'>
    <tr><td>".GetLangVar('sucesso1')."</td></tr>
    <form >
    <tr><td align='center'><input type='submit' value=".GetLangVar('nameconcluir')." class='bsubmit' onclick=\"javascript:window.close();\" /></td></tr>
    </form>
  </table>
<br />";
	}
} 

if (!isset($enviado) || $erro>0) {
//pegando os dados no caso de edicao
if (!empty($formid) && is_numeric($formid)) {
	$qq = "SELECT * FROM `Formularios` WHERE `FormID`='".$formid."'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$formulario = $row['FormName'];
} 
echo "
<br />
<form method='post' name='sourcelistform' action='formularios-duplicate.php'>
  <input type='hidden' name='formid' value='".$formid."'>
  <input type='hidden' name='enviado' value='1'>
<table class='myformtable' align='center' cellpadding=\"0\" cellspacing=\"3\">
<thead>
  <tr ><td colspan='1' style='padding: 8px;' >Duplica formulário</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='1' style='padding: 5px;'>
    <table>
      <tr>
        <td class='tdsmallbold' >Nome do formulário</td>
        <td class='tdformleft'  style='padding: 5px;' ><input type='text' size='40' name='formulario' id='formulario' value='".$formulario."_copia'></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
<td><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit'  /></td>
</tr>
</tbody>
</table>
</form>
";
}
$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>