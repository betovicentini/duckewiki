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
<table class='myformtable' align='left' cellpadding='8px'>
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
          <select name='formulario[]' multiple size='10'>";
          $qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY Formularios.FormName ASC";
          $rr = mysql_query($qq,$conn);
          while ($row= mysql_fetch_assoc($rr)) {
              echo "
            <option style='width: 300px;' value='".$row['FormID']."'>".$row['FormName']."</option>";
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
	$erro=0;
	if ($nf>1) {
		//PEGA AS DEFINICOES DE 1 DOS FORMULARIOS
		$ftokeep = $formulario[0];
		$rn = mysql_query("SELECT * FROM Formularios WHERE FormID=".$ftokeep);
		$rwn = mysql_fetch_assoc($rn);
		if (empty($formularionome)) {
			$formularionome = $rwn['FormName']."_merge";
		} 
		//PEGA AS VARIAVEIS DOS FORMULÁRIOS SELECIONADOS
		$qwhere = "WHERE (";
		$i =1;
		foreach($formulario as $ffid) {
			if ($i==1) {
				$qwhere .= "FormID='".$ffid."'";
			} else {
				$qwhere .= " OR FormID='".$ffid."'";
			}
			$i++;
		}
		$qwhere .= ")";
		$sql = "SELECT GROUP_CONCAT(DISTINCT TraitID SEPARATOR \";\") AS trr FROM FormulariosTraitsList ".$qwhere;
		$rr = mysql_query($sql,$conn);
		$rww = mysql_fetch_assoc($rr);
		$traitsarrids = $rww['trr'];
		$fieldsaskeyofvaluearray = array(
		'FormName' => $formularionome,
		//'FormFieldsIDS' => $traitsarrids,
		'Shared' =>  $rwn['Shared']
		);
		$newformid = InsertIntoTable($fieldsaskeyofvaluearray,'FormID','Formularios',$conn);
		if ($newformid>0) {
			$traitsarr = explode(";",$traitsarrids);
			if (count($traitsarr)>0) {
					$count = 1;
					foreach ($traitsarr as $tri) {
						$tri = $tri+0;
						if ($tri>0) {
							$qz = "INSERT INTO FormulariosTraitsList (FormID,TraitID,Ordem) VALUES (".$newformid.",".$tri.",".$count.")";
							$rr = mysql_query($qz,$conn);
							if ($rr) {
								$count++;
							} else {
								$erro++;
							}
						}
					}
			}
		} else {
			$erro++;
		}
		if ($erro==0) {
					echo "
<br />
  <table class='success' align='center' cellpadding=\"5\" >
    <tr><td>$nf formulários foram unidos com sucesso!</td></tr>
    <tr><td><input style='cursor: pointer'  type='button' value='".GetLangVar('nameconcluir')."' onclick=\"javascript:window.close();\" class='bsubmit'></td></tr>
  </table>";
		} else {
			echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Houve um erro na fusão</td></tr>
  </table>";
		}
	} 
	else {
			echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Precisa selecionar pelo menos 2 formulários para unir</td></tr>
  </table>";
 }
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>