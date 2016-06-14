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
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Formulários de variáveis';
$body = '';
if ($final>0 && $final!=2 && (!isset($formid) || ($formid+0)==0)) {
echo "<br /><span width='80%' style='font-size: 1em; color: red; font-weight: bold; border: thin gray; background-color: yellow; padding: 10px;' >Você precisa selecionar um formulário para duplicar</span><br /><br />";
}
if (!isset($final) || ((!isset($formid) || ($formid+0)==0) && $final!=2)) {
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<form action='formularios-form.php' method='post' name='formform'>
<table align='left' cellpadding='7' class='myformtable'>
<thead>
  <tr ><td colspan='3'>".GetLangVar('nameformulario')."&nbsp;&nbsp;<img height=15 style='cursor:pointer;' src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('formulario_help'));
	echo " onclick=\"javascript:alert('$help');\" /></td></tr>
</thead>
<tbody>
<tr>
  <td>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')."</td>
        <td class='tdformnotes'><select name='formid' >";
	if (!empty($formid)) {
		$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
		$rr = mysql_query($qq,$conn);
		$row= mysql_fetch_assoc($rr);
		echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
	} else {
		echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
	}
	//formularios usuario
		if ($acclevel=='admin') {
	$qq = "SELECT * FROM Formularios ORDER BY Formularios.FormName ASC";
	} else {
	$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY Formularios.FormName ASC";
	}
	$rr = mysql_query($qq,$conn);
	while ($row= mysql_fetch_assoc($rr)) {
		echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
	}
echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
  <td>
  <input type='hidden' value='' name='final' /> 
  <input type='submit' value='".GetLangVar('nameeditar')."' style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   onclick=\"javascript:document.formform.final.value=1\" /> </td>
  <td><input  type=submit value='".GetLangVar('namenovo')."' style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  onclick=\"javascript:document.formform.final.value=2\" /> </td>
  <!---<td><input  type=submit value='Organizar variáveis' style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  onclick=\"javascript:document.formform.final.value=3\" /> </td>--->
</tr>
<tr>
<td align='center'><input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Apagar formulários'  onclick = \"javascript:small_window('formularios-delete.php?&ispopup=1',800,400,'Apagar formulários');\" ></td>
<td align='center'><input  type='submit'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Duplicar formulário'  onclick=\"javascript:document.formform.final.value=4\"></td>
<td align='center'><input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Unir formulários'  onclick = \"javascript:small_window('formularios-merge.php',800,600,'Unir formulários');\" ></td>

</tr>
</tbody>
</table>
</form>
";
} else {
 if ($final==1) {
 	header("location: formulariosnovo-exec.php?ispopup=$ispopup&formid=".$formid);
 }
 if ($final==2) {
 	header("location: formulariosnovo-exec.php?ispopup=$ispopup");
 }
 if ($final==3) {
 	header("location: formularios-batchedit.php?ispopup=$ispopup&formid=".$formid);
 }
 if ($final==4) {
 	header("location: formularios-duplicate.php?formid=".$formid);
 }
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>