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
$title = 'Importar de filtro para projeto';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($gget);
//echopre($ppost);
if (empty($filtroid)) {
echo "
<br />
<table class='myformtable' align='left' cellpadding='7'>
<thead>
<tr>
  <td >Selecione o filtro</td>
</tr>
</thead>
<tbody>
<form method='post' name='finalform' action='projeto-fromfiltro.php'>";
foreach($gget as $kk => $vv) {
	echo "
<input type='hidden'  value='".$vv."' name='".$kk."'  >";
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namefiltro')."</td>
        <td>
          <select name='filtroid' >";
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
  <td  align='center'>
    <input style='cursor: pointer' type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' />
  </td>
</tr>
</tbody>
</table>
</form>
";

} else {
	$filtrocode = "filtroid_".$filtroid;
	//echo $saoplantas."  aqui 1";
	if (!isset($saoplantas) || $saoplantas!=1) {
		echo $saoplantas."  aqui 2";
		$qt = "SELECT EspecimenID FROM Especimenes WHERE FiltrosIDS LIKE '%".$filtrocode."' OR FiltrosIDS LIKE '%".$filtrocode.";%'";
		$rup = mysql_query($qt,$conn);
		$rwu = mysql_numrows($rup);
		if ($rwu>0) {
			$inserido=0;
			while ($row = mysql_fetch_assoc($rup)) {
				$qs = "SELECT * FROM ProjetosEspecs WHERE EspecimenID=".$row['EspecimenID']." AND ProjetoID=".$projetoid;
				$rsp = mysql_query($qs,$conn);
				$nsp = mysql_numrows($rsp);
				if ($nsp==0) {
				    $qins = "INSERT INTO  `ProjetosEspecs` (`EspecimenID`,`ProjetoID`,`AddedBy`,`AddedDate`) VALUES (".$row['EspecimenID'].",".$projetoid.", ".$uuid.", CURRENT_DATE())";
			    //echo $qins."<br />";
				    $rp = mysql_query($qins);
				    if ($rp) {
			    		$inserido++;
				    }
				}
			}
		}
	} elseif ($saoplantas==1) {
		$qt = "SELECT pltb.PlantaID,".$projetoid.", ".$uuid.", CURRENT_DATE() FROM Plantas as pltb LEFT JOIN ProjetosEspecs as prj ON pltb.PlantaID=prj.PlantaID WHERE (pltb.FiltrosIDS LIKE '%".$filtrocode."' OR pltb.FiltrosIDS LIKE '%".$filtrocode.";%') AND (prj.PlantaID IS NULL)";
		//echo $qt."<br >";
		$rup = mysql_query($qt);
		$rwu = mysql_numrows($rup);
		if ($rwu>0) {
			$qins = "INSERT INTO  `ProjetosEspecs` (`PlantaID`,`ProjetoID`,`AddedBy`,`AddedDate`)  (".$qt.")";
			//echo $qins."<br>";
			$rp = mysql_query($qins);
			if ($rp) {
				$inserido = $rwu;
			}
		}
	}

echo "
<br />
  <table class='success' align='center' cellpadding=\"5\" >
    <tr><td>$inserido registros foram importados para o projeto com sucesso</td></tr>
    <tr><td><input style='cursor: pointer'  type='button' value='".GetLangVar('nameconcluir')."' onclick=\"javascript:window.close();\" class='bsubmit'></td></tr>
  </table>";

} 

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>