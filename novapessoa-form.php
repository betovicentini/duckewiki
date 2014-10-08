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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Pessoa';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
echo "
<br />
<table align='center' class='myformtable' cellpadding='3'>";
if (empty($submitted)) {
echo "
<thead>
<tr >
<td colspan='100%'>".GetLangVar('namecadastro')." ".GetLangVar('namepessoa')."</td>
</tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<form action='novapessoa-form.php' method='post'>
  <input type='hidden' value='editando' name='submitted' />
  <input type='hidden' value='$ispopup' name='ispopup' />
  <td class='tdformnotes'>
    <select name='pessoaid' onchange='this.form.submit()';>";
			if (!isset($pessoaid)) {
				echo "
      <option value=''>".GetLangVar('nameselect')." ".strtolower(GetLangVar('nameeditar'))."</option>";
			} else {
				$wr = getpessoa($pessoaid,$abb=FALSE,$conn);
				$ww = mysql_fetch_assoc($wr);
				echo "
      <option  selected value='".$ww['PessoaID']."'>".$aa['Abreviacao']." (".$aa['Prenome'].") </option>";
			}
			echo "
      <option value=''>---- </option>";
			$wrr = getpessoa('',$abb=TRUE,$conn);
			while ($aa = mysql_fetch_assoc($wrr)){
				echo "
      <option value='".$aa['PessoaID']."'>".$aa['Abreviacao']." (".$aa['Prenome'].") </option>";
			}
	echo "
    </select>
  </td>
</form>
<form action='novapessoa-form.php' method='post'>
  <input type='hidden' value='$ispopup' name='ispopup' />
  <input type='hidden' value='novo' name='submitted' />
  <td class='tdformnotes' align='center'><input type='submit' value='".GetLangVar('namenovo')." ".GetLangVar('namecadastro')."' class='bsubmit' /></td>
</form>
</tr>";
} else {

echo "
<thead>
<tr ><td colspan='100%'>";
if ($submitted=='editando') {
	$_SESSION['editando']=1;
	$wr = getpessoa($pessoaid,$abb=FALSE,$conn);
	$ww = mysql_fetch_assoc($wr);
	$quem = $ww['Prenome']." ".$ww['Sobrenome'];
	$nome = $ww['Prenome'];
	$segnome = $ww['SegundoNome'];
	$sobrenome = $ww['Sobrenome'];
	$email = $ww['Email'];
	$obs = $ww['Notes'];
	$abrv = $ww['Abreviacao'];
	echo GetLangVar('nameeditando')." ".strtolower(GetLangVar('namecadastro'))." ".$quem;
} elseif ($submitted=='novo') {
	unset($_SESSION['editando']);
	echo GetLangVar('namenovo')." ".strtolower(GetLangVar('namecadastro'));
}
echo "</td></tr>
</thead>
<tbody>
<tr>
  <td>
    <table>
      <form action='novapessoa-exec.php' method='post'>
        <input type='hidden' value='$pessoaid' name='pessoaid' />
        <input type='hidden' value='$ispopup' name='ispopup' />

";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
      <tr bgcolor = '".$bgcolor."'>
        <td class='tdsmallbold' align='right'>".GetLangVar('namenome')."*</td>
        <td class='tdformleft' colspan='2'><input type='text' name='nome' size='30%' value='$nome' /></td>
      </tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
      <tr bgcolor = '".$bgcolor."'>
        <td class='tdsmallbold' align='right'>".GetLangVar('namesegnome')."</td>
        <td class='tdformleft' colspan='2'><input type='text' name='segnome' size='30%' value='$segnome' /></td>
      </tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
      <tr bgcolor = '".$bgcolor."'>
        <td class='tdsmallbold' align='right'>".GetLangVar('namelastname')."*</td>
        <td class='tdformleft' colspan='2'><input type='text' name='sobrenome' size='30%' value='$sobrenome' /></td>
      </tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
      <tr bgcolor = '".$bgcolor."'>
        <td class='tdsmallbold' align='right'>Nome para coletor</td>
        <td class='tdformleft' colspan='2'><input type='text' name='abrv' size='30%' value='$abrv' /></td>
      </tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
      <tr bgcolor = '".$bgcolor."'>
        <td class='tdsmallbold' align='right'>".GetLangVar('nameemail')."</td>
        <td class='tdformleft' colspan='2'><input type='text' name='email' size='30%' value='$email' /></td>
      </tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('nameobs')."</td>
  <td class='tdformleft' colspan='2'><textarea name='obs' cols=40 rows='5' wrap=SOFT>$obs</textarea></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
      <tr bgcolor = '".$bgcolor."'>
        <td>&nbsp;</td>
        <td align='right'><input type='submit' class='bsubmit' value=".GetLangVar('namesalvar')." /></td>
</form>
<form action='novapessoa-form.php' method='post'>  
        <input type='hidden' value='$ispopup' name='ispopup' />
        <td align='left'><input type='submit' class='breset' value=".GetLangVar('namevoltar')." /></td>
</form>
      </tr>
    </table>
  </td>
</tr>
";
} //else if !empty($pessoaid)

echo "
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
