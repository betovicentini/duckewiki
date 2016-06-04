<?php
//Este script checa se alguns campos de data
//Modificado por AV em 27 de julho de 2011. 
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
$title = 'Importar Dados Passo 03b';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$fields = unserialize($_SESSION['fieldsign']);
if (!isset($bibliovars)) {
	$datafields = array('BIBKEY');
	$newbibfields = array();
	foreach ($fields as $kk => $vv) {
    	if (in_array($vv,$datafields)) {
			$ak = array_search($vv,$datafields);
			$newbibfields[$kk] = array($tbprefix.$kk,$vv);
		}
	}
} else {
	$newbibfields = unserialize($bibliovars);
}
$bibproblems = array();
foreach ($newbibfields as $orgcol => $novascols) {
		$cll = $novascols[0];
		$brcoln = $novascols[1];
		if ($brcoln=='BIBKEY') {
			$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." CHAR(100) DEFAULT NULL";
			@mysql_query($qq,$conn);
			$qq = "UPDATE `".$tbname."` SET `".$cll."`=bib_check(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND (`".$orgcol."` IS NOT NULL) AND (`".$cll."` IS NULL)";
			$rr = mysql_query($qq,$conn);
			$qq = "SELECT * FROM `".$tbname."` WHERE `".$orgcol."`<>'' AND (`".$orgcol."` IS NOT NULL) AND (`".$cll."` IS NULL)";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			if ($nr>0) {
				$bibproblems[$orgcol] = array("$nr registros não tem um valor valido para BibKey!");
			} 
		}
}

//tem problemas em colunas avisa e interrompe a importacao
if (count($bibproblems)>0) {
echo "
<br />
<form action='import-data-step3b.php' method='post'>
<table align='center' class='myformtable' cellpadding='7'>
<thead>
 <tr><td align='center' colspan='3'>Os seguintes erros em colunas com Bibkeys foram encontrados</td></tr>
 <tr class='subhead'>
  <td>Coluna</td>
  <td>Erro</td>
  <td>O que fazer?</td>
 </tr>
</thead>
<tbody>
  <input type='hidden' name='newdata' value='".serialize($newbibfields)."' />";
  unset($_POST['newdata']);
	foreach ($_POST as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
}
   $idx  = 1;
 	foreach ($bibproblems as $orgcol => $vv) {
		$cln = $newbibfields[$orgcol][0];
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
<tr bgcolor = '".$bgcolor."'>
  <td>$orgcol</td>
  <td>".$vv[0]."</td>
  <td align='center'><input id='butidx".$idx."' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em\" value='Corrigir' ";
$myurl ="checkbib-popup.php?colname=".$cln."&orgcol=".$orgcol."&tbname=".$tbname."&buttonidx=butidx".$idx; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Corrigir colunas com bibkeys');\" /></td>
</tr>"; 
		$idx++;
	 }

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td align='center' colspan='3'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</tbody>
</table>
</form>";
	} 
	else {
		$bibchecked= TRUE;
	}

if ($bibchecked==TRUE) {
	echo "
<form name='myform' action='import-data-hub.php' method='post'>";
//coloca as variaveis anteriores
	foreach ($ppost as $kk => $vv) {
	echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
echo "
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
</form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
