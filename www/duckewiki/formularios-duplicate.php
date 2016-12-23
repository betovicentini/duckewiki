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
//		'FormFieldsIDS' => $row['FormFieldsIDS'],
		'Shared' =>  $row['Shared']
		);
	$newformid = InsertIntoTable($fieldsaskeyofvaluearray,'FormID','Formularios',$conn);
	if ($newformid>0) {
		$sql = "SELECT * FROM FormulariosTraitsList WHERE FormID='".$formid."'";
		$rer = mysql_query($sql,$conn);
		while($rww = mysql_fetch_assoc($rer)) {
			//echopre($rww);
			$rww['FormID'] = $newformid;
			$i=1;
			$q2 = "";
			$q1 = "";
			foreach($rww as $key => $vv) {
				$vv = trim($vv);
				if (!empty($vv)) {
					if (empty($q1) && empty($q2)) {
						$q1 = $q1." ".$key;
						$q2 = $q2." '".$vv."'";
					} else {
						$q1 = $q1.", ".$key;
						$q2 = $q2.", '".$vv."'";
					}
				}
			}
			$sqf = "INSERT INTO FormulariosTraitsList (".$q1.") VALUES (".$q2.")";
			$rsf = @mysql_query($sqf,$conn);
			if (!$rsf) {
			$erro++;
				echo $sqf."<br >";
				echo " erro 3<br />";
			} 
		} 
	} else {
		$erro++;
		echo " erro 4<br />";
	} 
	if ($erro==0) {
					echo "
<br />
  <table align='left' class='success' cellpadding=\"5\" cellspacing=0 width='50%'>
    <tr><td>".GetLangVar('sucesso1')."</td></tr>
    <form >
    <tr><td align='center'><input type='submit' value=".GetLangVar('nameconcluir')." class='bsubmit' onclick=\"javascript:window.close();\" /></td></tr>
    </form>
  </table>
<br />";
	}
} 

if ((!isset($enviado) || $erro>0) && $formid>0) {
$qq = "SELECT * FROM `Formularios` WHERE `FormID`='".$formid."'";
$rr = mysql_query($qq,$conn);
$row = mysql_fetch_assoc($rr);
$formulario = $row['FormName'];
echo "
<br />
<form method='post' name='sourcelistform' action='formularios-duplicate.php'>
  <input type='hidden' name='formid' value='".$formid."'>
  <input type='hidden' name='enviado' value='1'>
<table class='myformtable' align='left' cellpadding=\"7\" >
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
<td align='center'><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit'  /></td>
</tr>
</tbody>
</table>
</form>
";
} 
elseif (!isset($formid) || empty($formid)) {
echo "
<br />
<form action='formularios-duplicate.php' method='post' name='formform'>
<table align='left' cellpadding='7' class='myformtable'>
<thead>
  <tr ><td colspan='3'>".GetLangVar('nameformulario')."</td></tr>
</thead>
<tbody>
<tr>
  <td>
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')."</td>
        <td class='tdformnotes'><select name='formid' onchange=\"javascript: this.form.submit();\">";
echo "
            <option value='' >".GetLangVar('nameselect')."</option>";
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
  </tr>
</tbody>
</table>  
</form
";
}
$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>