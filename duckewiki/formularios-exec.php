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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
//,"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/filterlist.js'></script>",
"<script type='text/javascript'>
   function passvalteste() {
      var selvalue = window.document.forms['sourcelistform'].elements['formulario'].value;
      sourceList = window.document.forms['sourcelistform'].elements['destList'];
      resval = Array(sourceList.options.length);
      for (var i = 0; i < sourceList.options.length; i++) {
                if (sourceList.options[i] != null) {
                     resval[i] = sourceList.options[i].value;
                  }
         }
      resvv = resval.join('; ');
      destinationList = window.document.forms['finalform'].elements['details'];
         destinationList.value = resvv;
        window.document.forms['finalform'].elements['formulario'].value = selvalue;
        window.document.forms['finalform'].submit();
    }
</script>",

);
$title = 'Editar/Criar Formulários';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$erro=0;
if ($enviado=='1' && !empty($formulario)) {
	$arraylist = explode(";",$details);
	if ((count($arraylist)==0 || $apagar==1 || empty($details)) && $formid>0) {
		$formnome = "formid_".$formid;
		$qq = "UPDATE `Traits` SET `FormulariosIDS`=removeformularioidfromtraits(`FormulariosIDS`,'".$formnome."') WHERE `FormulariosIDS` LIKE '%formid_".$formid."' OR `FormulariosIDS` LIKE '%formid_".$formid.";%'";
		$nr = mysql_query($qq,$conn);
		if ($nr) {
			$qq = "DELETE FROM `Formularios` WHERE `FormID`='".$formid."'";
			$deleted = mysql_query($qq,$conn);
			if ($deleted) {
				echo "
<br />
  <table align='center' class='success' cellpadding=\"5\" cellspacing='0' width='50%'>
    <tr><td>O formulário foi apagado com sucesso!</td></tr>
    <form>
    <tr><td align='center'><input type='button' value=".GetLangVar('nameconcluir')." class='bsubmit' onclick='javascript:window.close();' /></td></tr>
    </form>
  </table>
<br />";
			}
		}
	} 
	else {
		$result = explode(";",$details);
		$traitarr = array();
		foreach($result as $trid => $trn) {
			$trarr = explode("|",$trn);
			$traitarr[] = ($trarr[0])+0;
		}
		$traitarr = array_unique($traitarr);
		$result = implode(";",$traitarr);
		if ($habitatform!=1) { $habitatform=0;} 
		$fieldsaskeyofvaluearray = array(
		'FormName' => $formulario,
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
} 
if (!isset($enviado) || $erro>0) {
//pegando os dados no caso de edicao
if (!empty($formid) && is_numeric($formid)) {
	$qq = "SELECT * FROM `Formularios` WHERE `FormID`='".$formid."'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	//echopre($row);
	$formulario = $row['FormName'];
	$tipodeuso = $row['Shared'];
	$traitids = $row['FormFieldsIDS'];
	if (empty($traitids)) {
		$qt = "SELECT * FROM FormulariosTraitsList WHERE `FormID`='".$formid."' ORDER BY Ordem";
		$rrr = mysql_query($qT,$conn);
		$trids = array();
		while($rww = mysql_fetch_assoc($rrr)) {
			$trids[] = $rww['TraitID'];
		}
		$traitids = implode(";",$trids);
	}
	$habitatform = $row['HabitatForm'];
	$txt = GetLangVar('nameeditar')." ";
} 
else {
	$txt = GetLangVar('namenovo')." ";
}
echo "
<br />
<table class='myformtable' align='center' cellpadding=\"0\" cellspacing=\"3\">
<form method='post' name='sourcelistform' action='formularios-exec.php'>
  <input type='hidden' name='formid' value='$formid'>
  <input type='hidden' name='ispopup' value='$ispopup'>
<thead>
  <tr ><td colspan='100%' style='padding: 8px;' >".$txt.strtolower(GetLangVar('nameformulario'))."</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' style='padding: 5px;'>
    <table>
      <tr>
        <td class='tdsmallbold' >".GetLangVar('namenome')." do formulário</td>
        <td class='tdformleft'  style='padding: 5px;' ><input type='text' size='40' name='formulario' id='formulario' value='$formulario'></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table align='center' cellpadding=\"5\" cellspacing=\"1\">
     <tr class='tabsubhead'>
       <td colspan='100%'>".GetLangVar('nameselect')." ".GetLangVar('nametraits')."</td>
     </tr>
     <tr class='tdthinborder2'>
       <td class='tdsmallbold' align='center'>".GetLangVar('namedisponivel')."</td>
       <td>&nbsp;</td>
       <td class='tdsmallbold' align='center'>".GetLangVar('nameselecionado')."</td>
     </tr>
     <tr class='tdthinborder2'>
       <td >
        <select name='srcList' multiple size='10' style=\"width:500px;\">";
		$filtro ="SELECT * FROM `Traits` WHERE `TraitName`<>'' AND TraitTipo<>'Estado' AND TraitTipo<>'Classe' ORDER BY `PathName` ASC";
		$res = mysql_query($filtro,$conn);
		while ($aa = mysql_fetch_assoc($res)){
			$PathName = $aa['PathName'];
			$level = $aa['MenuLevel'];
			$tipo = $aa['TraitTipo'];
			if ($level==1) {
				//$espaco='';
			} else {
				//$espaco = str_repeat('&nbsp;&nbsp;&nbsp;',$level);
			}
			if ($tipo=='Classe') { //if is a class or a state does not allow selection
				echo "          
        <option style='color:  #990000; font-size: 1.3em;' value=''>$espaco<i>".($aa['TraitName'])."</i></option>";
			} 
			else {
				//$espaco = $espaco.str_repeat('- ',$level-1);
				$tp = explode("|",$tipo);
				if ($tp[1]=='Categoria') {
					$qu = "SELECT * FROM `Traits` WHERE `ParentID`='".$aa['TraitID']."'";
					$ru = mysql_query($qu,$conn);
					$ncat = mysql_numrows($ru);
					$std = array();
					while ($rwu = mysql_fetch_assoc($ru)) {
						$std[] = strtolower($rwu['TraitName']);
					}
					$stads = implode(";",$std);
					$stlen = strlen($stads);
					if ($stlen>30) {
						$stt = substr($stads,0,50);
						$stt = $stt."....";
					} else {
						$stt = $stads;
					}
					$nn = $aa['PathName']." [Categorias: $stt]";
					$bgcol = "#E0FFFF";
				} 
				else {
					$nn = $aa['PathName']." [".$tp[1]."]";
					if ($tp[1]=='Quantitativo') {
						$bgcol = "#FAFAD2";
					}
					if ($tp[1]=='Texto') {
						$bgcol = "#D3D3D3";
					}
					if ($tp[1]=='Imagem') {
						$bgcol = "#FFE4E1";
					}
				}
				echo "
        <option style='background: ".$bgcol.";' value='".$aa['TraitID']."|".$aa['PathName']."' alt=\"$nn\" >".$espaco.$nn."</option>";
				
			}
	}
echo "
        </select>
      </td>
      <td width='30' align='center'>
        <input type='button' value=\" >> \" class='breset' onClick=\"javascript:addSrcToDestListTraits('sourcelistform');\" />
        <br /><br />
        <input type='button' value=\" << \" class='breset' onclick=\"javascript:deleteFromDestList('sourcelistform');\" />
      </td>
      <td>
        <select name='destList' multiple size='10' style=\"max-width:300px;\">";
	if ($formid>0) {
		$arrayoftraists = explode(";",$traitids);
		foreach ($arrayoftraists as $thetrait) {
			$val = $thetrait+0;
			$qq = "SELECT * FROM `Traits` WHERE `TraitID`='".$val."'";
			$rr = mysql_query($qq,$conn);
			$rwt = mysql_fetch_assoc($rr);
	echo "
          <option selected value='".$rwt['TraitID']."|".$rwt['PathName']."'>".$rwt['PathName']."</option>";
		}
	}
echo "
        </select>
      </td>
    </tr>
<script type=\"text/javascript\">
<!--
var myfilter = new filterlist(document.sourcelistform.srcList);
//-->
</script>    
    <tr>
      <td colspan='100%'>
        <table>
          <tr>
            <td>Filtra variáveis por expressão regular:</td>
            <td><input name='regexp' onKeyUp=\"myfilter.set(this.value);\" /></td>
            <td><input type='button' onclick=\"myfilter.set(this.form.regexp.value)\" value=\"Filtrar\" /></td>
            <td><input type='button' onclick=\"myfilter.reset();this.form.regexp.value=''\" value=\"Limpar\" /></td>
          </tr>
          <tr><td colspan='100%'><input type='checkbox' name=\"toLowerCase\" onclick=\"myfilter.set_ignore_case(!this.checked);\" />&nbsp;Case sensitive</td></tr>
         </table>
     </td>
    </tr>
</form>
  </table>
</td>
</tr>
<form method='post' name='finalform' action='formularios-exec.php'>
  <input type='hidden' name='formulario' value='$formulario' />
  <input type='hidden' name='details' value='$details' />
  <input type='hidden' name='enviado' value='1' />
  <input type='hidden' name='formid' value='$formid' />
  <input type='hidden' name='traitsids' value='$traitsids' />
  <input type='hidden' name='ispopup' value='$ispopup' />
    ";

  if ($tipodeuso==0 || !isset($tipodeuso)) {
  	$slc1 = 'checked';
  	$slc2 = '';
  }
  if ($tipodeuso==1) {
  	$slc2 = 'checked';
  	$slc1 ='';
  }
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' style='padding: 5px;'>
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
 </form>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%' style='padding: 5px;'>
    <table align='center'>
      <tr>
      <td><input type='button' value='".GetLangVar('nameenviar')."' class='bsubmit' onClick = \"javascript:passvalteste();\" /></td>
      <td>&nbsp;&nbsp;&nbsp;</td>
<form method='post' action='formularios-form.php'>
  <input type='hidden' name='ispopup' value='$ispopup' />
      <td><input type='submit' value='".GetLangVar('namevoltar')."' class='bblue' /></td>
</form>
<form method='post' action='formularios-exec.php'>
      <input type='hidden' name='ispopup' value='$ispopup' />
      <input type='hidden' name='formid' value='$formid' />
      <input type='hidden' name='apagar' value='1' />
      <input type='hidden' name='enviado' value='1' />
      <input type='hidden' name='formulario' value='$formulario' />
      <input type='hidden' name='details' value='$details' />
      <td>&nbsp;&nbsp;&nbsp;</td>
      <td><input type='submit' value='".GetLangVar('nameapagar')."' class='borange' /></td>
</form>
    </tr>
  </table>
</td>
</tr>
</tbody>
</table>
";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>