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
		if (empty($formularionome)) {
			echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Precisa dar um nome ao formulário!</td></tr>
  </table>";
		} 
		else {
		//COLOCA A DATA NO NOME DO FORMULARIO
		$curdate = date("Y-m-d");
		$formularionome = strtoupper(trim($formularionome))."_".$curdate;
		if ((empty($tipodeuso) || !isset($tipodeuso)) && ($filtroid+0)==0) {
			$tipodeuso = 1;
		}
	  	$qs = "SELECT * FROM Formularios WHERE UPPER(FormName)='".$formularionome."'";
  		$rss= mysql_query($qs,$conn);
  		$nrss = mysql_numrows($rss);
  		if ($nrss>0)  {
  			while($rsw = mysql_fetch_assoc($rss)) {
	  			$formid = $rsw['FormID'];
	  			$formnome = "formid_".$formid;
				$qq = "UPDATE `Traits` SET `FormulariosIDS`=removeformularioidfromtraits(`FormulariosIDS`,'".$formnome."') WHERE `FormulariosIDS` LIKE '%formid_".$formid."' OR `FormulariosIDS` LIKE '%formid_".$formid.";%'";
				$nr = mysql_query($qq,$conn);
				$sql = "DELETE FROM `FormulariosIDS` WHERE `FormID`='".$formid."'";
				mysql_query($sql,$conn);
				$sql = "DELETE FROM `FormulariosTraitsList` WHERE `FormID`='".$formid."'";
				mysql_query($sql,$conn);
			}
		}
		if ($habitatform!=1) { $habitatform=0;} 
		$arrayofvals = array(
		'FormName' => $formularionome,
		'Shared' => $tipodeuso,
		'HabitatForm' => $habitatform
		);
		$formid = InsertIntoTable($arrayofvals,'FormID','Formularios',$conn);
		$formnome = "formid_".$formid;
		if ($formid) {
			$qu = "SET SESSION group_concat_max_len =1000000";
			$ru = mysql_query($qu,$conn);
			$qu= "UPDATE Formularios SET FormFieldsIDS=(SELECT GROUP_CONCAT(DISTINCT TraitID SEPARATOR ';') FROM `".$tbname."`  WHERE Marcado>0) WHERE FormID='".$formid."'";
			$ru = mysql_query($qu,$conn);

			$qu= "SELECT GROUP_CONCAT(DISTINCT TraitID SEPARATOR ';') as traitlist FROM `".$tbname."`  WHERE Marcado>0";
			$ru = mysql_query($qu,$conn);
			$rwu = mysql_fetch_assoc($ru);
			$traitarr = explode(";",$rwu['traitlist']);
			$n=1;
			foreach ($traitarr as $value) {
					$vv = $value+0;
					$sql = "UPDATE `Traits` SET Traits.`FormulariosIDS`=IF(Traits.`FormulariosIDS`<>'',CONCAT(Traits.`FormulariosIDS`,';','".$formnome."'),'".$formnome."') WHERE Traits.`TraitID`='".$vv."'";
					$upsql = mysql_query($sql,$conn);
					$qz = "INSERT INTO FormulariosTraitsList (FormID,TraitID,Ordem) SELECT ".$formid.",".$value.",".$n;
					$rr = mysql_query($qz,$conn);
					$n++;
			}
			echo "
<br />
  <table align='center' style='background-color: lightblue; color: black; font: 1.5em bold;' cellpadding=\"7\" >
    <tr><td align='center' >".GetLangVar('sucesso1')."</td></tr>
    <tr><td align='center' ><input type='button' value='".GetLangVar('nameconcluir')."' onclick=\"javascript:window.close();\" class='bsubmit'></td></tr>
  </table>";
	}
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
$title = 'Salvando Formulário';
//$body = ' onload=\"javascript:getvaluefromparent();\" '
FazHeader($title,$body,$which_css,$which_java,$menu);

$qu= "SELECT COUNT(*)  as marcado FROM `".$tbname."`  WHERE Marcado>0";
$ru = mysql_query($qu,$conn);
$rwu = mysql_fetch_assoc($ru);
if ($rwu['marcado']==0) {

			echo "
<br />
  <table align='center' class='erro' cellpadding=\"5\" >
    <tr><td align='center' >Precisa marcar ao menos 1 variável para salvar um formulário!</td></tr>
    <tr><td align='center' ><input type='button' value='".GetLangVar('nameconcluir')."' onclick=\"javascript:window.close();\" class='bsubmit'></td></tr>
  </table>";


} 
else {

//echopre($gget);
//echopre($ppost);
//  <input type='hidden' id='counter' name='tempids' size='100' >

echo "
<form name='finalform' action='traits_definition_saveForm.php' method='post'>
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden'  name='saving' value='1'  >
<br />
<table class='myformtable' align='center' border=0 cellpadding=\"5\" cellspacing=\"0\" >
<thead>
  <tr><td colspan=2 >".GetLangVar('namesalvar')."&nbsp;".GetLangVar('nameformulario')."</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldleft'>Nome para o formulario</td>
  <td><input type='text' name='formularionome' value='".$formularionome."'  size='60' style='height: 20px; color: red;' /></td>
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
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldleft'>Compartilhamento</td>
  <td>
    <table>
      <tr>
        <td class='tdsmallbold'>Formulário de hábitat?</td>
        <td><input type='checkbox' name='habitatform' value='1' /></td>
        <td align='left'><img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Selecione esta opção caso deseje utilizar as variáveis organizadas neste formulário para o cadastro de variáveis associadas a uma localidade (um HABITAT LOCAL)";
	echo " onclick=\"javascript:alert('$help');\" /></td>
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
";
	}
//onclick='javascript: getparval();' 
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
}

?>