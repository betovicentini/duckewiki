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
//echopre($ppost);
//echopre($gget);
//CABECALHO
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array(
);
$title = 'Novos gêneros';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

#$collfam = $tbprefix.'FamiliaID';

//echopre($ppost);
if ($final==1) {
	foreach ($inputs as $orvaluefield => $generos) {
		$arrayvelho = explode(";",$orvaluefield);
		foreach ($generos as $peop => $novos) {
				$geneid = $novos['generoid']+0;
				if ($geneid>0) {
					$qq = "SELECT * FROM Tax_Generos WHERE GeneroID='".$geneid."'";
					$res = mysql_query($qq,$conn);
					$rw = mysql_fetch_assoc($res);
					$abrv = trim($rw['Genero']);
					$ky = array_search($peop,$arrayvelho);
					$arrayvelho[$ky] =  $abrv;
				}
		}
		$novovalor = implode(";",$arrayvelho);
		//echo $novovalor."  ".$orvaluefield;
		if ($novovalor<>$orvaluefield) {
			$qq = "UPDATE ".$tbname." SET `".$orgcol."`='".$novovalor."' WHERE `".$orgcol."`='".$orvaluefield."'";
			mysql_query($qq,$conn);
		}
	}
	$qq = "UPDATE ".$tbname." set ".$colname."=checkgeneros(`".$orgcol."`,".$famcolname.") WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."`='ERRO'";
	mysql_query($qq,$conn);
}

$query = "SELECT DISTINCT `".$orgcol."`,`".$famcolname."`  FROM `".$tbname."`  WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."`='ERRO' ORDER BY `".$orgcol."`";
$res = mysql_query($query,$conn);
$nres = mysql_numrows($res);
$erro=0;
if ($nres>0) {
echo "
<br />
<table align='center' class='myformtable' cellpadding='7' >
<thead>
  <tr><td colspan='2'>Os seguintes gêneros em ".$orgcol." não estão cadastrados</td>
  </tr>
    <tr class='subhead'>
    <td width=\"25%\">Registro original no arquivo</td>
    <td width=\"75%\">
      <table cellpadding='5' align='left' >
      <tr >
        <td align='center' width=\"40%\"><b>Nomes não cadastrados</b></td>
        <td align='center' width=\"40%\"><b>Selecione?</b></td>
        <td align='center' width=\"20%\"><b>Novo</b></td>
      </tr>
      </table>
    </td>
  </tr>
</thead>
<tbody>
<form action='novosgeneros-popup.php' method='post'>
<input type='hidden' name='tbname' value='".$tbname."' />
<input type='hidden' name='colname' value='".$colname."' />
<input type='hidden' name='famcolname' value='".$famcolname."' />
<input type='hidden' name='orgcol' value='".$orgcol."' />
<input type='hidden' name='buttonidx' value='".$buttonidx."' />
<input type='hidden' name='final' value='1' />";
$idxpess = 1;
while ($row = mysql_fetch_assoc($res)) {
	$pess = $row[$orgcol];
	$famid = $row[$famcolname];
	//echo $pess." aqui ho<br >";
	$qu = "SELECT checkgeneros('".$pess."', ".$famid.") as peop";
	$ru = mysql_query($qu,$conn);
	$rw = mysql_fetch_assoc($ru);
	if ($rw['peop']=='ERRO') {
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' width=\"25%\">".$pess."</td>
  <td width=\"75%\">
    <table cellpadding='5' align='left' >";
	$pessarr = explode(";",$pess);
	$idxps = 1;
	foreach ($pessarr as $ps) {
		if (!isset($inputs)) {
			$inputs = array("'".$pess."'" => array("'".$ps."'"));
		} else {
			$ipt = array("'".$pess."'" => array("'".$ps."'"));
			$inputs = array_merge((array)$inputs,(array)$ipt);
		}
		$qu = "SELECT checkgeneros('".$ps."', ".$famid.") as peop";
		$ru = mysql_query($qu,$conn);
		$rw = mysql_fetch_assoc($ru);
		if ($rw['peop']=='ERRO') {
	echo "
      <tr>
        <td align='right' width=\"40%\">".$ps."</td>";
			$qq = "SELECT * FROM Tax_Generos WHERE FamiliaID=".$famid." ORDER BY Genero";
echo "
        <td width=\"40%\" align='left'>
          <select id='generoid_".$idxpess."_".$idxps."' name='inputs[".$pess."][".$ps."][generoid]'>
            <option value=''>Pode ser um desses?</option>";
				$rss = mysql_query($qq,$conn);
				$nrss = mysql_numrows($rss);
				if ($nrss>0) {
					while ($rww = mysql_fetch_assoc($rss)) {
		echo "
            <option value=".$rww['GeneroID'].">".$rww['Genero']."</option>";
					}
				} else {
		echo "
            <option selected value=''>Não se parece com nada, cadastre novo!</option>";
				}
echo "
          </select>
        </td>
        <td  align='center' width=\"20%\" ><input type='button' class='bblue' value='".GetLangVar('namenova')."' ";
		$myurl ="genero-popup.php?spnome=".$ps."&generoid_val=generoid_".$idxpess."_".$idxps; 
		echo " onclick = \"javascript:small_window('".$myurl."',800,400,'Novo Genero');\" /></td>
      </tr>";
		$idxps++;
		}
	}
echo "
    </table>
  </td>
</tr>";

	}
	$idxpess++;
}

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td align='center' colspan='2'><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></td></tr>
</tbody>
</table>
</form>";

} 
else {
//flush();
	echo "
<form >
  <script language=\"JavaScript\">
    setTimeout( function() { changebutton('".$buttonidx."','Corrigido');},0.0001);
  </script>
</form>";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>