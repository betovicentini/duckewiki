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
$title = 'Juntar filtro';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (empty($filtro)) {
echo "
<br />
<table class='myformtable' align='center' cellpadding='7'>
<thead>
<tr>
  <td >Junta filtros</td>
</tr>
</thead>
<tbody>
<form method='post' name='finalform' action='filtros-merge.php'>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namefiltro')."s</td>
        <td>
          <select name='filtro[]' multiple='30'>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FiltroName";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['FiltroID']."'>".$rr['FiltroName']." [".$rr['AddedDate'].".]</option>";
			}
			mysql_free_result($res);
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
        <td class='tdsmallbold'>Nome para o filtro&nbsp;<img height='13' src=\"icons/icon_question.gif\" ";
				$help = 'Se não informado será usado o primeiro da lista a unir';
				echo " onclick=\"javascript:alert('$help');\" /></td>
        <td><input  type='text' size='30' name='filtronome' /></td>
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
	$nf = count($filtro);
	if ($nf>1) {
		//pega o filtro a manter
		$ftokeep = $filtro[0];
		unset($filtro[0]);
		$resto = array_values($filtro);
		$rn = mysql_query("SELECT * FROM Filtros WHERE FiltroID=".$ftokeep);
		$rwn = mysql_fetch_assoc($rn);
		if (empty($filtronome)) {
			$filtronome = $rwn['FiltroName'];
		} 
		$shared = 0;
		$specsarr = explode(";",$rwn['EspecimenesIDS']);
		$plantasarr = explode(";",$rwn['PlantasIDS']);
		//echo count($specsarr)."   antes <br />";
		//PEGA OS SPECS IDS DOS DEMAIS FILTROS
		foreach($resto  as $fi) {
			$rn2 = mysql_query("SELECT * FROM Filtros WHERE FiltroID=".$fi);
			$rwn2 = mysql_fetch_assoc($rn2);
			//echopre($rwn2);
			$specsarr2 = explode(";",$rwn2['EspecimenesIDS']);
			$plantasarr2 = explode(";",$rwn2['PlantasIDS']);
			if (count($specsarr2)) {
				$specsarr = array_merge((array)$specsarr,(array)$specsarr2);
			}
			if (count($plantasarr2)) {
				$plantasarr = array_merge((array)$plantasarr,(array)$plantasarr2);
			}
		}
		//echo count($specsarr)."   depois 1 <br />";
		$specsarr = array_unique($specsarr);
		//echo count($specsarr)."   depois 2 <br />";
		$plantasarr = array_unique($plantasarr);
		if (count($specsarr)>0) {
			$specids = implode(";",$specsarr);
			$arrayofvals = array('Shared' => $shared,'FiltroName' => $filtronome, 'EspecimenesIDS' => $specids);
		} else {
			$arrayofvals = array('Shared' => $shared,'FiltroName' => $filtronome);
		}
		if (count($plantasids)>0) {
			$plantasids = implode(";",$plantasarr);
			$arrayofvals = array_merge((array)$arrayofvals,(array)array('PlantasIDS' => $plantasids));
			
		}
		$newfiltro = UpdateTable($ftokeep,$arrayofvals,'FiltroID','Filtros',$conn); 
		if ($newfiltro>0) {
			$filtronewcode = "filtroid_".$ftokeep;
			$erro=0;
			$succ =0;
			foreach($resto  as $ff) {
				$filtrocode = "filtroid_".$ff;
				$sql = "UPDATE Especimenes SET Especimenes.FiltrosIDS=replacefiltrosids(Especimenes.FiltrosIDS,'".$filtrocode."',' ".$filtronewcode."') WHERE FiltrosIDS LIKE '%".$filtrocode."' OR FiltrosIDS LIKE '%".$filtrocode.";%'";
				$rr = mysql_query($sql,$conn);
				//echo $sql."<br />";
				if (!$rr) {
					$erro++;
				}
				$sql = "UPDATE Plantas SET Plantas.FiltrosIDS=replacefiltrosids(Plantas.FiltrosIDS,'".$filtrocode."',' ".$filtronewcode."') WHERE Plantas.FiltrosIDS LIKE '%".$filtrocode."' OR Plantas.FiltrosIDS LIKE '%".$filtrocode.";%'";
				//echo $sql."<br />";
				$rr = mysql_query($sql,$conn);
				if (!$rr) {
					$erro++;
				}
				if ($erro==0) {
					$sql = "DELETE FROM Filtros WHERE FiltroID=".$ff;
 					//echo $sql."<br />";

					mysql_query($sql,$conn);
					$succ++;
				}
			}
			if ($succ>0) {
					echo "
<br />
  <table class='success' align='center' cellpadding=\"5\" >
    <tr><td>$succ filtros foram unidos com sucesso!</td></tr>
    <tr><td><input style='cursor: pointer'  type='button' value='".GetLangVar('nameconcluir')."' onclick=\"javascript:window.close();\" class='bsubmit'></td></tr>
  </table>";
			}
			if ($succ!=($nf-1)) {
			echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Dos $nf filtros indicados apenas $succ foram apagados com sucesso!</td></tr>
  </table>";
			}
		} else {
				echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Houve um erro no cadastro do filtro</td></tr>
  </table>";
		}
	} else {
				echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Precisa indicar pelo menos dois filtros para juntar!</td></tr>
  </table>";
	}//end if filtro
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>