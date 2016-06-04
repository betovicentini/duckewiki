<?php
//este script checa se os registros do arquivo importado devem ser atualizados ou inseridos
//modificado por AV em 27 de Junho de 2011
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
$which_java = array();
$title = 'Importar Dados Passo 02a';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$qq = "SELECT count(*) as ntotal FROM ".$tbname;
$res = mysql_query($qq,$conn);
$rr = mysql_fetch_assoc($res);
$ntotal = $rr['ntotal'];
$specupdate = 0;
$plupdate = 0;
$plinsert =0;
$specinsert =0;
if ($coletas==1 || $coletas==3) {
		$qq = "SELECT count(*) as plupdate FROM ".$tbname." WHERE ".$tbprefix."PlantaID>0";
		$res = mysql_query($qq,$conn);
		$rr = mysql_fetch_assoc($res);
		$plupdate = $rr['plupdate'];
		$qq = "SELECT count(*) as plinsert FROM ".$tbname." WHERE ".$tbprefix."PlantaID=0";
		$res = mysql_query($qq,$conn);
		$rr = mysql_fetch_assoc($res);
		$plinsert = $rr['plinsert'];
}
if ($coletas==2 || $coletas==3) {
		$qq = "SELECT count(*) as specupdate FROM ".$tbname." WHERE ".$tbprefix."EspecimenID>0";
		$res = mysql_query($qq,$conn);
		$rr = mysql_fetch_assoc($res);
		$specupdate = $rr['specupdate'];
		$qq = "SELECT count(*) as specinsert FROM ".$tbname." WHERE ".$tbprefix."EspecimenID=0";
		$res = mysql_query($qq,$conn);
		$rr = mysql_fetch_assoc($res);
		$specinsert = $rr['specinsert'];
}

if (($specupdate+$plupdate+$plinsert+$specinsert)>0) {
echo "
<br />
<table cellpadding='5' class='myformtable' align='center'>
<thead>
 <tr><td colspan='2'>Atenção!</td></tr>
</thead>
<tbody>";
if ($plupdate>0) {
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  colspan='2'>O arquivo a ser importado contém $ntotal registros</td></tr>";
}
if ($plupdate>0) {
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
  <tr bgcolor = '".$bgcolor."'><td  colspan='2'>$plupdate registros são plantas marcadas JÁ cadastradas no banco de dados</td></tr>";
}
if ($specupdate>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'><td  colspan='2'>$specupdate registros são exsicatas JÁ cadastradas no banco de dados</td></tr>";

}
$notregisters = $plinsert+$specinsert;
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
if ($coletas==3) { 
	$nrg = $specinsert." novas exsicatas de plantas marcadas serão cadastradas";
}
if ($coletas==2) {
	$nrg = $specinsert." novas exsicatas serão cadastradas";
}
if ($coletas==1) {
	$nrg = $plinsert." novas plantas marcadas serão cadastradas";
}
echo "
<tr bgcolor = '".$bgcolor."'><td  colspan='2'>$nrg</td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<form action='import-data-step2b.php' method='post'>";
foreach ($_POST as $kk => $vv) {
	if (!empty($vv)) {
	 echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
}

if ($plupdate>0 || $specupdate>0) { 
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
</tbody>
<thead>
<tr class='subhead'><td  colspan='2'>O que fazer com campos a serem importados que já estão cadastrados?</td></tr>
</thead>
<tbody>
<tr>
<td>
  <input type='radio' selected value='adicionar' name='updaterecs' />&nbsp;Adicionar as novas informações aos campos que já contém informação.</td>
<td>
  <input type='radio' value='substituir' name='updaterecs' />&nbsp;Substituir os campos existentes com as novas informações.</td>
</tr>";

}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td align='center'><input style='cursor: pointer' type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td>
</form>
  <td align='center'>
<form action='import-data-form.php' method='post'>
<input type='hidden' name='ispopup' value='".$ispopup."' />
<input style='cursor: pointer' type='submit' value='".GetLangVar('namevoltar')."' class='bblue' />
</form></td></tr>
</tbody>
</table>";
} 
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>