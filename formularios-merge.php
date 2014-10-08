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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array(
);
$title = 'Juntar formulários';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (empty($formulario)) {
echo "
<br />
<table class='myformtable' align='center' cellpadding='7'>
<thead>
<tr>
  <td >Junta formulários</td>
</tr>
</thead>
<tbody>
<form method='post' name='finalform' action='formularios-merge.php'>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namefiltro')."s</td>
        <td>
          <select name='formulario[]' multiple size='20'>";
          $qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY Formularios.FormName ASC";
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
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>Nome para o formulário&nbsp;<img height='13' src=\"icons/icon_question.gif\" ";
				$help = 'Se não informado será usado o primeiro da lista a unir';
				echo " onclick=\"javascript:alert('$help');\" /></td>
        <td><input  type='text' size='30' name='formularionome' /></td>
      </tr>
      </table>
    </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td  align='center'>
    <input style='cursor: pointer' type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' />
  </td>
</tr>
</tbody>
</table>
</form>
";
} 
else {
	//echopre($ppost);
	$nf = count($formulario);
	if ($nf>1) {
		//pega o formulario a manter
		$ftokeep = $formulario[0];
		unset($formulario[0]);
		$resto = array_values($formulario);
		$rn = mysql_query("SELECT * FROM Formularios WHERE FormID=".$ftokeep);
		$rwn = mysql_fetch_assoc($rn);
		if (empty($formularionome)) {
			$formularionome = $rwn['FormName'];
		} 
		$traitsarr = explode(";",$rwn['FormFieldsIDS']);
		//PEGA OS SPECS IDS DOS DEMAIS FILTROS
		foreach($resto  as $fi) {
			$rn2 = mysql_query("SELECT * FROM Formularios WHERE FormID=".$fi);
			$rwn2 = mysql_fetch_assoc($rn2);
			//echopre($rwn2);
			$traitsarr2 = explode(";",$rwn2['FormFieldsIDS']);
			if (count($specsarr2)) {
				$traitsarr = array_merge((array)$traitsarr,(array)$traitsarr2);
			}
		}
		$traitsarr = array_unique($traitsarr);
		if (count($traitsarr)>0) {
			$traitsarrids = implode(";",$traitsarr);
			$arrayofvals = array('FormName' => $formularionome, 'FormFieldsIDS' => $traitsarrids);
		} else {
			$arrayofvals = array('FormName' => $formularionome);
		}
		CreateorUpdateTableofChanges($ftokeep,'FormID','Formularios',$conn);
		$newfiltro = UpdateTable($ftokeep,$arrayofvals,'FormID','Formularios',$conn); 
		if ($newfiltro>0) {
			$formnewcode = "formid_".$ftokeep;
			$erro=0;
			$succ =0;
			foreach($resto  as $ff) {
				$formcode = "formid_".$ff;
				$sql = "UPDATE `Traits` SET `FormulariosIDS`=removeformularioidfromtraits(`FormulariosIDS`,'".$formcode."') WHERE `FormulariosIDS` LIKE '%formid_".$ff."' OR `FormulariosIDS` LIKE '%formid_".$ff.";%'";
				$rr = mysql_query($sql,$conn);
				//echo $sql."<br />";
				if (!$rr) {
					$erro++;
				}
				$sql = "DELETE FROM FormulariosTraitsList WHERE FormID=".$ff;
				$rr = mysql_query($sql,$conn);
				if (!$rr) {
					$erro++;
				}
				if ($erro==0) {
					$sql = "DELETE FROM Formularios WHERE FormID=".$ff;
					mysql_query($sql,$conn);
					$succ++;
				}
			}
			$sql = "DELETE FROM FormulariosTraitsList WHERE FormID=".$ftokeep;
			@mysql_query($sql,$conn);
			if (count($traitsarr)>0) {
					$count = 1;
					foreach ($traitsarr as $tri) {
						$tri = $tri+0;
						if ($tri>0) {
							$qz = "INSERT INTO FormulariosTraitsList (FormID,TraitID,Ordem) VALUES (".$ftokeep.",".$tri.",".$count.")";
							$rr = mysql_query($qz,$conn);
							if ($rr) {
								$count++;
							}
						}
					}
					echo $count." traits cadastrados para o formulario<br />";
			}
			if ($succ>0) {
					echo "
<br />
  <table class='success' align='center' cellpadding=\"5\" >
    <tr><td>$succ formulários foram unidos com sucesso!</td></tr>
    <tr><td><input style='cursor: pointer'  type='button' value='".GetLangVar('nameconcluir')."' onclick=\"javascript:window.close();\" class='bsubmit'></td></tr>
  </table>";
			}
			if ($succ!=($nf-1)) {
			echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Dos $nf formulários indicados apenas $succ foram apagados com sucesso!</td></tr>
  </table>";
			}
		} else {
				echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Houve um erro no cadastro do formulário alterado</td></tr>
  </table>";
		}
	} else {
				echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Precisa indicar pelo menos dois formulários para juntar!</td></tr>
  </table>";
	}
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>