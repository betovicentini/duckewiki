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
$which_java = array(
);
$title = 'Ajustar Formularios';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$qq = "SELECT * FROM Formularios";
$res = mysql_query($qq,$conn);
$nres = mysql_numrows($res);
if ($nres>0) {
echo "
<br><table cellpadding='7' class='myformtable' align='left'>
<thead>
 <tr><td colspan='100%'>Atualização de Formulários</td></tr>
 <tr class='subhead'><td>Nome</td><td>N variáveis</td></tr>
</thead>
<tbody>";
while ($row = mysql_fetch_assoc($res)) {
	$traitarr = explode(";",$row['FormFieldsIDS']);
	$traitarr = array_unique($traitarr);
	$fnome = $row['FormName'];
	$formid = $row['FormID'];
	$formnome = 'formid_'.$row['FormID'];
	$qq = "UPDATE `Traits` SET `FormulariosIDS`=removeformularioidfromtraits(`FormulariosIDS`,'".$formnome."') WHERE `FormulariosIDS` LIKE '%".$formnome."' OR `FormulariosIDS` LIKE '%".$formnome.";%'";
	$nr = mysql_query($qq,$conn);
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
		echo "
  <tr bgcolor = $bgcolor><td  align='center'>$fnome</td><td align='center'>".count($traitarr)."</td></tr>";
	//checa 
	$qn = "SELECT * FROM FormulariosTraitsList WHERE FormID='".$formid."'";
	$rn = mysql_query($qn,$conn);
	$count = mysql_num_rows($rn);
	$count2 = $count;	
	$nz = count($traitarr);
	if ($count!=$nz && $nz>0) {
		foreach ($traitarr as $tri) {
			$tri = $tri+0;
			if ($tri>0) {
				$qnn = "SELECT * FROM FormulariosTraitsList WHERE FormID='".$formid."' AND TraitID=".$tri;
				$rnn = mysql_query($qnn,$conn);
				$nrnn = mysql_num_rows($rnn);
				if ($nrnn==0) {
					$qz = "INSERT INTO FormulariosTraitsList (FormID,TraitID,Ordem) SELECT ".$formid.",TraitID,".$count2." FROM Traits WHERE TraitID=".$tri;
					$rr = mysql_query($qz,$conn);
					if ($rr) {
						$count2++;
					}
				}
			}
		}
	}
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <form action='index.php' method='post'>
  <td align='center' colspan='100%'><input type='submit' value='".GetLangVar('nameconcluir')."' class='bsubmit'></td>
  </form>
</tr>
</tbody>
</table>";
}



$which_java = array();
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>