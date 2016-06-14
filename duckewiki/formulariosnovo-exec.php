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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Editar/Criar Formulários';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
$erro=0;
if ($enviado=='1' && !empty($formulario_nome)) {
		$traitarr = explode(";",$traitsidsvalue);
		$traitarr = array_unique($traitarr);
		//echopre($traitarr);
		$result = implode(";",$traitarr);
		if ($habitatform!=1) { $habitatform=0;} 
		$fieldsaskeyofvaluearray = array(
		'FormName' => $formulario_nome,
		'FormFieldsIDS' => $result,
		'Shared' => $tipodeuso,
		'HabitatForm' => $habitatform
		);
		$formid = $formid+0;
		if ($formid>0) { //then update
			CreateorUpdateTableofChanges($formid,'FormID','Formularios',$conn);
			$updateform = UpdateTable($formid,$fieldsaskeyofvaluearray,'FormID','Formularios',$conn);
			if (!$updateform) {
				$erro++;
				echo " erro 1<br />";
			} else {
				$formnome = "formid_".$formid;
				$qq = "UPDATE `Traits` SET `FormulariosIDS`=removeformularioidfromtraits(`FormulariosIDS`,'".$formnome."') WHERE `FormulariosIDS` LIKE '%formid_".$formid."' OR `FormulariosIDS` LIKE '%formid_".$formid.";%'";
				$nr = mysql_query($qq,$conn);
				if ($nr) {
					$updated=0;
					foreach ($traitarr as $value) {
							$vv = $value+0;
							$sql = "UPDATE `Traits` SET Traits.`FormulariosIDS`=IF(Traits.`FormulariosIDS`<>'',CONCAT(Traits.`FormulariosIDS`,';','".$formnome."'),'".$formnome."') WHERE Traits.`TraitID`='".$vv."'";
							$upsql = mysql_query($sql,$conn);
							if ($upsql) {
								$updated++;
							}
					}
					if ($updated>0) {
					//refreshparent('".$parentform."');
				echo "
<br />
  <table align='center' class='success' cellpadding=\"5\" cellspacing=0 width='50%'>
    <tr><td>".GetLangVar('sucesso1')."</td></tr>
    <form >
    <tr><td align='center'><input type='submit' value=".GetLangVar('nameconcluir')." class='bsubmit' onclick=\"javascript:window.close();\" /></td></tr>
    </form>
  </table>
<br />";
					} else {
						echo " erro 2<br />";
					}
				} else {
					echo " erro 3<br />";
				}
			} 
		}
		else { //else insert
			$formid = InsertIntoTable($fieldsaskeyofvaluearray,'FormID','Formularios',$conn);
			if (!$formid) {
				$erro++;
				echo " erro 4<br />";
			} 
			else {
				$formnome = "formid_".$formid;
				$updated=0;
				//$traitarr = explode(";",$details);
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
				if ($updated>0 && $updated==count($traitarr)) {
				//refreshparent('".$parentform."');
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
				else {
					$erro++;
					echo " erro 5<br />";
				}
			}
		} //end insert or update
		if ($erro==0) {
			$qq = "CREATE TABLE IF NOT EXISTS FormulariosTraitsList (
FormID INT(10),
TraitID INT(10),
Ordem INT(10))
CHARACTER SET utf8";
 		@mysql_query($qq,$conn);
		$qn = "DELETE FROM FormulariosTraitsList WHERE FormID='".$formid."'";
		@mysql_query($qn,$conn);
		//$trarr = explode(";",$trids);
		//echopre($traitarr);
		$nz = count($traitarr);
		$count = 0;
		foreach ($traitarr as $tri) {
			$tri = $tri+0;
			if ($tri>0) {
			$qz = "INSERT INTO FormulariosTraitsList (FormID,TraitID,Ordem) SELECT ".$formid.",TraitID,".$count." FROM Traits WHERE TraitID='".$tri."'";
			$rr = mysql_query($qz,$conn);
			if ($rr) {
				$count++;
			}
			}
		}
		}
} 
if (!isset($enviado) || $erro>0) {
//pegando os dados no caso de edicao
if (!empty($formid) && is_numeric($formid)) {
	$qq = "SELECT * FROM `Formularios` WHERE `FormID`='".$formid."'";
	//echo $qq."<br>";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	//echopre($row);
	$formulario_nome = $row['FormName'];
	$tipodeuso = $row['Shared'];
	$traitsids = $row['FormFieldsIDS'];
	//if (empty($traitids)) {
		$qt = "SELECT * FROM FormulariosTraitsList WHERE `FormID`='".$formid."' ORDER BY Ordem";
		//echo $qt."<br />";
		$rrr = mysql_query($qt,$conn);
		$numbertraits = mysql_numrows($rrr);
		if ($numbertraits==0) {
			$trids = explode(";",$traitsids);
			$numbertraits= count($trids);
		}
		$traitsidstxt = $numbertraits." variáveis incluídas";
		//$trids = array();
		//while($rww = mysql_fetch_assoc($rrr)) {
			//$trids[] = $rww['TraitID'];
		//}
		//$traitids = implode(";",$trids);
	//}
	$habitatform = $row['HabitatForm'];
	$txt = GetLangVar('nameeditar')." ";
} 
else {
	$txt = GetLangVar('namenovo')." ";
}
echo "
<form method='post' action='formulariosnovo-exec.php' name='coletaform'>
  <input type='hidden' name='enviado' value='1' />
  <input type='hidden' name='formid' value='$formid' />
<br />
<table class='myformtable' align='center' cellpadding=\"0\" cellspacing=\"3\">
<thead>
  <tr ><td style='padding: 8px;' >".$txt.strtolower(GetLangVar('nameformulario'))."</td></tr>
</thead>
<tbody>
    ";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td style='padding: 5px;'>
    <table>
      <tr>
        <td class='tdsmallbold' >".GetLangVar('namenome')." do formulário</td>
        <td class='tdformleft'  style='padding: 5px;' ><input type='text' size='40' name='formulario_nome' value='".$formulario_nome."'></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold' >Variáveis do formulário</td>
        <td class='tdformnotes' >
        <input type='hidden' id='traitsidsvalue'  name='traitsidsvalue' value='".$traitsids."' />
        <span id='traitsidstxt' ' name='traitsidstxt' style='padding: 7px; font-size: 1.2em;' >".$traitsidstxt."</span>
        </td>
        <td><input type=button value=\"+\" class='bsubmit'  ";
		$myurl = "selector_de_variaveisnovo.php?valuevar=traitsidsvalue&valuetxt=traitsidstxt&formname=coletaform&traitids=".$traitsids; 
		echo " onclick = \"javascript:small_window('$myurl',800,500,'Seleciona Variáveis');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
if ($tipodeuso==0 || !isset($tipodeuso)) {
	$slc1 = 'checked';
	$slc2 = '';
}
if ($tipodeuso==1) {
	$slc1 ='';
	$slc2 = 'checked';
}

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td style='padding: 5px;'>
    <table>
      <tr>
        <td class='tdsmallbold'>Tipo de uso</td>
        <td><input type='radio' name='tipodeuso' $slc1 value='0' />&nbspPessoal</td>
        <td><input type='radio' name='tipodeuso' $slc2 value='1' />&nbspCompartilhado</td>
";
if ($habitatform==1) {
  	$habtxt = 'checked';
} else {
	$habtxt = '';
}
echo "
        <td class='tdsmallbold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td class='tdsmallbold'>Formulário de hábitat?</td>
        <td><input type='checkbox' name='habitatform' $habtxt value='1' /></td>
        <td align='left'><img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Selecione esta opção caso deseje utilizar as variáveis organizadas neste formulário para o cadastro de variáveis associadas a uma localidade (um HABITAT LOCAL)";
	echo " onclick=\"javascript:alert('$help');\" /></td>
      </tr>
    </table>
  </td>
</tr>  
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td style='padding: 5px;'>
    <table align='center' cellpadding='7px' >
      <tr>
      <td align='center' ><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></form></td>
<form method='post' action='formularios-form.php'>
  <input type='hidden' name='ispopup' value='$ispopup' />
      <td align='center' ><input type='submit' value='".GetLangVar('namevoltar')."' class='bblue' /></td>
</form>
    </tr>
  </table>
</td>
</tr>
</tbody>
</table>
";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>