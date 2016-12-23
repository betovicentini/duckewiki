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

//echopre($ppost);

$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Duplica Formulário';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$erro=0;
$oerro = "";
if ($enviado=='1' && !empty($otraitname)) {
	//PEGA DEFINICOES DA VARIAVEL E FAZ O NOVO CADASTRO
	$qq = "SELECT * FROM `Traits` WHERE `TraitID`='".$otraitid."'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$oldname = $row['TraitName'];
	if (strtoupper($oldname)==strtoupper($otraitname)) { 
		$erro++;
		$oerro =  "<span align='left' style='color: red; font-size: 1.2em; background-color: yellow;' >Já existe uma variável com esse nome</span>";
	}
	if ($erro==0) {
	$fieldsaskeyofvaluearray = array(
		'ParentID' => $row['ParentID'],
		'TraitName' => $otraitname,
		'TraitName_English'  => $otraitname_english,
		'TraitTipo' => $row['TraitTipo'],
		'TraitDefinicao' => $row['TraitDefinicao'],
		'TraitDefinicao_English' => $row['TraitDefinicao_English'],
		'TraitUnit' => $row['TraitUnit'],
		'TraitIcone' => $row['TraitIcone'],
		'MultiSelect' => $row['MultiSelect']
	);
		$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
		if (!$newtrait) {
			$erro++;
		} else {
			updatesingletraitpath($newtrait,$conn);
		}
	}
	
	if ($erro>0) {
		$oerro =  "<span align='left' style='color: red; font-size: 1.2em; background-color: yellow;' >Houve erro no cadastro da variável</span>";
	} 
	else {
	//PEGA CATEGORIAS DE VARIACAO E DUPLICA
	$qq = "SELECT * FROM `Traits` WHERE `ParentID`='".$otraitid."'";
	$rr = mysql_query($qq,$conn);
    while($row = mysql_fetch_assoc($rr)) {
    	$fieldsaskeyofvaluearray = array(
			'ParentID' => $newtrait,
			'TraitName' => $row['TraitName'],
			'TraitName_English'  => $row['TraitName_English'],
			'TraitTipo' => $row['TraitTipo'],
			'TraitDefinicao' => $row['TraitDefinicao'],
			'TraitDefinicao_English' => $row['TraitDefinicao_English'],
			'TraitIcone' => $row['TraitIcone']
			);
		$newstate = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
		if (!$newstate) {
			$erro++;
		} else {
			updatesingletraitpath($newstate,$conn);
		}
    }
		if ($erro>0) {
			$oerro =  "<span align='left' style='color: red; font-size: 1.2em; background-color: yellow;' >Houve erro no cadastro das categorias de variação</span>";
		} 
	}

	if ($erro==0) {
echo "
  <table align='left' class='success' cellpadding=\"5\" cellspacing=0 width='50%'>
    <tr><td>A variável foi duplicada</td></tr>
    <form >
    <tr><td align='center'><input type='submit' value=".GetLangVar('nameconcluir')." class='bsubmit' onclick=\"javascript:window.close();\" /></td></tr>
    </form>
  </table>
<br />";
	} 
	else {
		echo $oerro;
	}
}


if ((!isset($enviado) || $erro>0) && $otraitid>0) {
$qq = "SELECT * FROM `Traits` WHERE `TraitID`='".$otraitid."'";
//echo $qq."<br >";
$rr = mysql_query($qq,$conn);
$row = mysql_fetch_assoc($rr);
$otraitname = $row['TraitName'];
$otraitname_english = $row['TraitName_English'];
echo "
<br />
<form method='post' name='sourcelistform' action='traits-duplicate.php'>
  <input type='hidden' name='otraitid' value='".$otraitid."'>
  <input type='hidden' name='enviado' value='1'>
<table class='myformtable' align='left' cellpadding=\"7\" >
<thead>
  <tr ><td colspan='1' style='padding: 8px;' >Duplica variável categórica ".$otraitname."</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='1' style='padding: 5px;'>
    <table>
      <tr>
        <td class='tdsmallbold' >Nome da nova variável</td>
        <td class='tdformleft'  style='padding: 5px;' ><input type='text' size='40' name='otraitname' id='otraitname' value='".$otraitname."_copia'></td>
      </tr>
      <tr>
        <td class='tdsmallbold' >Nome da nova variável ENGLISH</td>
        <td class='tdformleft'  style='padding: 5px;' ><input type='text' size='40' name='otraitname_english' id='otraitname_english' value='".$otraitname_english."_copy'></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
<td align='center'><input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit'  /></td>
</tr>
</tbody>
</table>
</form>
";
} 
elseif (!isset($otraitid) || empty($otraitid)) {
	echo "<span align='left' style='color: red; font-size: 1.2em; background-color: yellow;' >Primeiro selecione a variável que deseja duplicar.</span>";
}
$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>