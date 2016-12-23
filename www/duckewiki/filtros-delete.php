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
$title = 'Apaga Filtros';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
if (empty($filtro)) {
echo "
<br />
<table class='myformtable' align='left' cellpadding='7'>
<thead>
<tr>
  <td >Apaga filtros</td>
</tr>
</thead>
<tbody>
<form method='post' name='finalform' action='filtros-delete.php'>
<input type='hidden' name='ispopup' value='".$ispopup."' />";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namefiltro')."s</td>
        <td>
          <select name='filtro[]' multiple size=20>";
			$qq = "SELECT * FROM Filtros WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY AddedDate";
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
    <table align='center'>
        <tr>
          <td><input style='cursor: pointer' type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit' /></td>
        </tr>
      </table>
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
	if ($nf>0) {
		$succ=0;
		foreach($filtro as $ff) {
			$erro=0;
			$sql = "DELETE FROM FiltrosSpecs WHERE FiltroID=".$ff;
			$rr = mysql_query($sql,$conn);
			if (!$rr) {
				$erro++;
			}
			if ($erro==0) {
				$sql = "DELETE FROM Filtros WHERE FiltroID=".$ff;
				mysql_query($sql,$conn);
				$succ++;
				echo "Filtro ".$ff."<br />";
				session_write_close();
			}
			mysql_free_result($rr);
			
		}
		if ($succ>0) {
					echo "
<br />
  <table class='success' align='center' cellpadding=\"5\" >
    <tr><td>$succ filtros foram apagados com sucesso!</td></tr>
    <tr><td><input style='cursor: pointer'  type='button' value='".GetLangVar('nameconcluir')."' onclick=\"javascript:window.close();\" class='bsubmit'></td></tr>
  </table>";
		}
		if ($succ!=$nf) {
		echo "
<br />
  <table class='erro' align='center' cellpadding=\"5\" >
    <tr><td>Dos $nf filtros indicados apenas $succ foram apagados com sucesso!</td></tr>
  </table>";
		}
	}

} //end if filtro
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>