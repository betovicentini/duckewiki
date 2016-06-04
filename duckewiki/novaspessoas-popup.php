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

$ispopup = 1;
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Novas pessoas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


//echopre($ppost);
if ($final==1) {
	foreach ($inputs as $orvaluefield => $pessoas) {
		$arrayvelho = explode(";",$orvaluefield);
		foreach ($pessoas as $peop => $novos) {
				$pessid = $novos['pessoaid']+0;
				if ($pessid>0) {
					$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$pessid."'";
					$res = mysql_query($qq,$conn);
					$rw = mysql_fetch_assoc($res);
					$abrv = trim($rw['Abreviacao']);
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
	$qq = "UPDATE ".$tbname." set ".$colname."=checkpessoas(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."`='ERRO'";
	mysql_query($qq,$conn);
}
$query = "SELECT DISTINCT `".$orgcol."` FROM `".$tbname."`  WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."`='ERRO' ORDER BY `".$orgcol."`";
$res = mysql_query($query,$conn);
$nres = mysql_numrows($res);
$erro=0;
if ($nres>0) {
echo "
<br />
<table align='center' class='myformtable' cellpadding='5' width=\"100%\">
<thead>
  <tr><td colspan='2'>As seguintes pessoas na coluna $orgcol não estão cadastradas</td>
  </tr>
    <tr class='subhead'>
    <td width=\"25%\">Registro original no arquivo</td>
    <td width=\"75%\">
      <table cellpadding='5' align='left' width=\"100%\">
      <tr >
        <td align='center' width=\"40%\"><b>Nomes não cadastrados</b></td>
        <td align='center' width=\"40%\"><b>Pode ser?</b></td>
        <td align='center' width=\"20%\"><b>Novo</b></td>
      </tr>
      </table>
    </td>
  </tr>
</thead>
<tbody>
<form action='novaspessoas-popup.php' method='post'>
<input type='hidden' name='tbname' value='".$tbname."' />
<input type='hidden' name='colname' value='".$colname."' />
<input type='hidden' name='orgcol' value='".$orgcol."' />
<input type='hidden' name='buttonidx' value='".$buttonidx."' />
<input type='hidden' name='final' value='1' />";
$idxpess = 1;
while ($row = mysql_fetch_assoc($res)) {
	$pess = $row[$orgcol];
	//echo $pess." aqui ho<br >";
	$qu = "SELECT checkpessoas('".$pess."') as peop";
	$ru = mysql_query($qu,$conn);
	$rw = mysql_fetch_assoc($ru);
	if ($rw['peop']=='ERRO') {
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' width=\"25%\">".$pess."</td>
  <td width=\"75%\">
    <table cellpadding='5' align='left' width=\"100%\">";
	$pessarr = explode(";",$pess);
	$idxps = 1;
	foreach ($pessarr as $ps) {
		if (!isset($inputs)) {
			$inputs = array("'".$pess."'" => array("'".$ps."'"));
		} else {
			$ipt = array("'".$pess."'" => array("'".$ps."'"));
			$inputs = array_merge((array)$inputs,(array)$ipt);
		}
		$qu = "SELECT checkpessoas('".$ps."') as peop";
		$ru = mysql_query($qu,$conn);
		$rw = mysql_fetch_assoc($ru);
		if ($rw['peop']=='ERRO') {
	echo "
      <tr>
        <td align='right' width=\"40%\">".$ps."</td>";
			$qq = "SELECT * FROM Pessoas WHERE";
			$pps = str_replace("."," ",$ps);
			$pps = str_replace(","," ",$pps);
			$colarr = explode(" ",$pps);
			if (count($colarr)>1) {
				$i=0;
				$idxhigh = 0;
				$runid =0 ;
				foreach ($colarr as $kc => $cc) {
					$ln = strlen($cc);
					if ($ln>$runid) {
						$idxhigh = $kc;
						$runid=$ln;
					}
				}
				$getstr = $colarr[$idxhigh];
				$getstr = strtolower(trim($getstr));
				$qq .= " LOWER(Sobrenome) LIKE '".$getstr."%'";
			}
			$qq .= " ORDER BY Abreviacao ";
echo "
        <td width=\"40%\" align='left'>
          <select id='pessoaid_".$idxpess."_".$idxps."' name='inputs[".$pess."][".$ps."][pessoaid]'>
            <option value=''>Pode ser um desses?</option>";
				$rss = mysql_query($qq,$conn);
				$nrss = mysql_numrows($rss);
				if ($nrss>0) {
					while ($rww = mysql_fetch_assoc($rss)) {
		echo "
            <option value=".$rww['PessoaID'].">".$rww['Abreviacao']." [".$rww['Prenome']."]</option>";
					}
				} else {
		echo "
            <option selected value=''>Não se parece com nada, cadastre novo!</option>";
				}
				$qq = "SELECT * FROM Pessoas ORDER BY Abreviacao";
				$rss = mysql_query($qq,$conn);
		echo "
            <option value=''>-------------</option>";
				while ($rww = mysql_fetch_assoc($rss)) {
		echo "
            <option value=".$rww['PessoaID'].">".$rww['Abreviacao']." [".$rww['Prenome']."]</option>";
				}
echo "
          </select>
        </td>
        <td  align='center' width=\"20%\" ><input type='button' class='bblue' value='".GetLangVar('namenova')."' ";
		$myurl ="novapessoa-form-popup.php?abrv=".trim($ps)."&pessoaid_val=pessoaid_".$idxpess."_".$idxps; 
		echo " onclick = \"javascript:small_window('".$myurl."',800,400,'Nova Pessoa');\" /></td>
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