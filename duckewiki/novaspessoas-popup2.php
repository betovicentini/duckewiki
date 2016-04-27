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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array();
$title = 'Novas pessoas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($final==1) {
	if (count($inputs)>0) {
		foreach ($inputs as $abrv => $pessid) {
			if ($pessid>0) {
				$qq = "UPDATE ".$tbname." SET `".$colname."`='".$pessid."' WHERE `".$abreviacao."`='".$abrv."' AND `".$colname."`='ERRO'";
				mysql_query($qq,$conn);
			}
		}
	}
	//$qq = "UPDATE `".$tbname."` SET `".$colname."`=checarpessoaimport(`".$abreviacao."`,`".$prenome."`,`".$sobrenome."`) WHERE `".$colname."`='ERRO'  OR  (`".$colname."` IS NULL)";
	//mysql_query($qq,$conn);
}

$qq = "SELECT DISTINCT  `".$abreviacao."`,`".$prenome."`,`".$sobrenome."`, `".$segundonome."` FROM `".$tbname."` WHERE `".$colname."`='ERRO'";
//echo $qq."<br />";
$res = @mysql_query($qq,$conn);
$nres = mysql_numrows($res);
$erro=0;
if ($nres>0) {
echo "
<br />
<table align='center' class='myformtable' cellpadding='7' >
<thead>
  <tr><td colspan='3'>As seguintes pessoas parecem já estar cadastradas</td>
  </tr>
  <tr class='subhead'>
    <td >Registro original no arquivo</td>
    <td align='center' ><b>Pode ser?</b></td>
    <td align='center' ><b>Novo</b></td>
  </tr>
</thead>
<tbody>
<form action='novaspessoas-popup2.php' method='post'>
<input type='hidden' name='tbname' value='".$tbname."' />
<input type='hidden' name='colname' value='".$colname."' />
<input type='hidden' name='abreviacao' value='".$abreviacao."' />
<input type='hidden' name='prenome' value='".$prenome."' />
<input type='hidden' name='sobrenome' value='".$sobrenome."' />
<input type='hidden' name='segundonome' value='".$segundonome."' />
<input type='hidden' name='buttonidx' value='".$buttonidx."' />
<input type='hidden' name='final' value='1' />";
$idxpess = 1;
while ($row = mysql_fetch_assoc($res)) {
	$pess = $row[$abreviacao];
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' >".$pess."</td>
  <td align='left'>";
  $lastname = strtoupper(removeacentos($row[$sobrenome]));
  $firstname = strtoupper(removeacentos($row[$prenome]));
  $parece = "SELECT PessoaID,Abreviacao,Prenome,SegundoNome,Sobrenome FROM Pessoas WHERE UPPER(acentostosemacentos(Sobrenome)) LIKE '".$lastname."' AND LEFT(UPPER(acentostosemacentos(Prenome)),1) LIKE LEFT('".$firstname."',1) ORDER BY Abreviacao";
  //echo $parece."<br />";
  echo "
<select id='pessoaid_".$idxpess."' name='inputs[".$pess."]'>
<option value=''>Pode ser um desses?</option>";
	$rss = mysql_query($parece,$conn);
	$nrss = mysql_numrows($rss);
	if ($nrss>0) {
		while ($rww = mysql_fetch_assoc($rss)) {
		echo "
            <option value=".$rww['PessoaID'].">".$rww['Abreviacao']." [".$rww['Prenome']."  ".$rww['SegundoNome']."]</option>";
		}
	} else {
		echo "
            <option selected value=''>Não se parece com nada, cadastre novo!</option>";
	}
echo "
</select>
<td  align='center' ><input type='button' class='bblue' value='".GetLangVar('namenova')."' ";
$myurl ="novapessoa-form-popup.php?nome=".$row[$prenome]."&sobrenome=".$row[$sobrenome]."&segnome=".$row[$segundonome]."&abrv=".trim($pess)."&pessoaid_val=pessoaid_".$idxpess; 
echo " onclick = \"javascript:small_window('".$myurl."',800,400,'Nova Pessoa');\" /></td>
      </tr>";
		$idxpess++;
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td align='center' colspan='3'><input style='cursor: pointer'  type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></td></tr>
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

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>