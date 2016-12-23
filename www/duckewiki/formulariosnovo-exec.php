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
		$result = implode(";",$traitarr);
		if ($habitatform!=1) { $habitatform=0;} 
		$fieldsaskeyofvaluearray = array(
		'FormName' => $formulario_nome,
		//'FormFieldsIDS' => $result,
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
			} 
			$sql = "SELECT GROUP_CONCAT(DISTINCT TraitID  ORDER BY Ordem SEPARATOR \";\") AS trr FROM FormulariosTraitsList WHERE FormID='".$formid."'";
			$rr = mysql_query($sql,$conn);
			$rww = mysql_fetch_assoc($rr);
			$oldtraitarr = explode(";",$rww['trr']);
			//$new_arr = array_map(function($piece){return (string) $piece;}, $traitarr);
			foreach($oldtraitarr  as $trid) {
				//se nao estiver no novo apaga (in_array no working, gambiarra aqui)
				$nao=1;
				foreach($traitarr as $tr2) {
					if ($trid==$tr2) {
						$nao=0;
					}
				}
				if ($nao==1) {
					$qn = "DELETE FROM FormulariosTraitsList WHERE FormID='".$formid."' AND TraitID=".$trid;
					//echo $qn."<br />";
					@mysql_query($qn,$conn);
				}
			}
		}
		else { //else insert
			$formid = InsertIntoTable($fieldsaskeyofvaluearray,'FormID','Formularios',$conn);
			if (!$formid) {
				$erro++;
				echo " erro 4<br />";
			} 
		} //end insert or update
		if ($erro==0) {
			$qq = "CREATE TABLE IF NOT EXISTS FormulariosTraitsList (
FormID INT(10),
TraitID INT(10),
Ordem INT(10))
CHARACTER SET utf8";
 		@mysql_query($qq,$conn);
		$nz = count($traitarr);
		$count = 1;
		foreach ($traitarr as $tri) {
			$tri = $tri+0;
			if ($tri>0) {
				$qz = "SELECT * FROM FormulariosTraitsList WHERE FormID=".$formid." AND TraitID=".$tri;
				$rr = mysql_query($qz,$conn);
				$nrr = mysql_numrows($rr);
				if ($nrr==0) {
					$qz = "INSERT INTO FormulariosTraitsList (FormID,TraitID,Ordem) VALUES (".$formid.",".$tri.",".$count.")";
					$rr = mysql_query($qz,$conn);
				} else {
					$qz = "UPDATE FormulariosTraitsList SET Ordem='".$count."' WHERE TraitID='".$tri."' AND FormID='".$formid."'";
					mysql_query($qz,$conn);
				}
				//echo $qz."<br />";
				$count++;
			}
		}
	}
}  elseif ($enviado=='1') {
	echo "
<span style='font-size: 1em; color: red; padding: 5px; width: 100px;'>
Precisa dar um nome ao formulário</span>";
}
//if (!isset($enviado) || $erro>0) {
//pegando os dados no caso de edicao
if (!empty($formid) && $formid>0) {
	$qq = "SELECT * FROM `Formularios` WHERE `FormID`='".$formid."'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$formulario_nome = $row['FormName'];
	$tipodeuso = $row['Shared'];
	$traitsids = $row['FormFieldsIDS'];
	$sql = "SELECT GROUP_CONCAT(DISTINCT TraitID ORDER BY Ordem SEPARATOR \";\") AS trr FROM FormulariosTraitsList WHERE FormID='".$formid."'";
	//echo $sql."<br />";
	$rr = mysql_query($sql,$conn);
	$rww = mysql_fetch_assoc($rr);
	$traitsids = trim($rww['trr']);
	//echo $traitsids."<br />";
	if (!empty($traitsids)) {
		$trids = explode(";",$traitsids);
		$numbertraits= count($trids);
	} else {
		$numbertraits = 0;
	}
	$traitsidstxt = $numbertraits." variáveis incluídas";
	$habitatform = $row['HabitatForm'];
	$txt = GetLangVar('nameeditar')." ";
} 
else {
	$txt = GetLangVar('namenovo')." ";
}
if (empty($formid) || !isset($formid)) {
echo "
<br />
<form action='formulariosnovo-exec.php' method='post' name='formform'>
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
else {
if ($formid=='novo') { unset($formid);}
echo "
<form method='post' action='formulariosnovo-exec.php' name='coletaform'>
  <input type='hidden' name='enviado' value='1' />
  <input type='hidden' name='formid' value='$formid' />
<br />
<table class='myformtable' cellpadding=\"5\" cellspacing=\"3\">
<thead>
  <tr ><td  >".$txt.strtolower(GetLangVar('nameformulario'))."</td></tr>
</thead>
<tbody>
    ";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold' >".GetLangVar('namenome')." do formulário</td>
        <td class='tdformleft'> <input type='text' size='40' name='formulario_nome' value='".$formulario_nome."' /></td>
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
        <span id='traitsidstxt'  style='padding: 7px; font-size: 1.2em;' >".$traitsidstxt."</span>
        </td>
        <td><input type='button' value=\"Editar\" class='bsubmit'  ";
		$myurl = "selector_de_variaveisnovo.php?valuevar=traitsidsvalue&amp;valuetxt=traitsidstxt&amp;formname=coletaform&amp;traitids=".$traitsids; 
		echo " onclick = \"javascript:small_window('$myurl',800,500,'Seleciona variáveis');\" /></td>";
		if ($formid=='novo' || !isset($formid)) {
echo "<td><input type='button' value=\"Extrair variáveis de um filtro\" class='bblue'  ";
		$myurl = "formularios_varsfromfilter.php?valuevar=traitsidsvalue&amp;valuetxt=traitsidstxt&amp;formname=coletaform"; 
		echo " onclick = \"javascript:small_window('$myurl',800,500,'Extrai Variáveis');\" /></td>";
		}
echo "
      </tr>
    </table>
  </td>
</tr>
";
$trr = trim($traitsids);
if (!empty($trr)) {
	$trarr = explode(";",$traitsids);
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold' >Formatação para descrição<img height='15' src=\"icons/icon_question.gif\" ";
	$help = "Altere a formatação do formulário para gerar descrições dinâmicas";
	echo " onclick=\"javascript:alert('$help');\" /></td>
        <td class='tdformnotes' >
        <span id='desctraitsidsvalue' style='padding: 7px; font-size: 1.2em;' >".$desctraitsidsvalue."</span>
        </td>
        <td><input type='button' value=\"Editar\" class='bsubmit'  ";
		$myurl = "formularios_descricao.php?valuevar=traitsidsvalue&amp;valuetxt=desctraitsidsvalue&amp;formname=coletaform&amp;formid=".$formid; 
		echo " onclick = \"javascript:small_window('".$myurl."',1000,600,'Versão do formulário para gerar descrições');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
}
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
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Tipo de uso</td>
        <td><input type='radio' name='tipodeuso' $slc1 value='0' />&nbsp;Pessoal</td>
        <td><input type='radio' name='tipodeuso' $slc2 value='1' />&nbsp;Compartilhado</td>
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
        <td align='left'><img height='15' src=\"icons/icon_question.gif\" ";
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
  <td align='center'>
    <table align='center' >
      <tr>
      <td align='center' ><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></form></td>
<form method='post' action='formularios-form.php'>
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