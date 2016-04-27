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

//UPDATE USER TABLE
$qq = "ALTER TABLE `Users`  ADD `Email` CHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `PessoaID`";
@mysql_query($qq,$conn);

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Usuário';
$body = '';
$menu = FALSE;
FazHeader($title,$body,$which_css,$which_java,$menu);

$qq = "ALTER TABLE `Users`  ADD `Valid` TINYINT(1) NOT NULL DEFAULT '1' AFTER `Email`";
$res = @mysql_query($qq,$conn);


echo "
<table align='center' class='myformtable' cellpadding='7'>";
if (empty($submitted)) {
echo "
<thead>
<tr><td colspan='2'>".GetLangVar('namecadastrar')." ".strtolower(GetLangVar('nameor'))." ".strtolower(GetLangVar('nameeditar'))." ".strtolower(GetLangVar('nameuser'))."</td></tr>
</thead>
<tbody>
<tr>
  <td class='tdformnotes'>
<form action='usuario-form.php' method='post'>
  <input type='hidden' value='editando' name='submitted' />
  <input type='hidden' value='$ispopup' name='ispopup'/>
    <select name='usuarioid' onchange='this.form.submit()';>";
			if (!isset($usuarioid)) {
				echo "
      <option value=''>".GetLangVar('nameselect')." ".strtolower(GetLangVar('nameeditar'))."</option>";
			} else {
				$wr = getuser($usuarioid,$conn);
				$ww = mysql_fetch_assoc($wr);
				echo "
      <option  selected value='".$ww['UserID']."'>".$ww['FirstName']." ".$ww['LastName']."</option>";
			}
			echo "
      <option value=''>----</option>";
			$wrr = getuser('',$conn);
			while ($aa = mysql_fetch_assoc($wrr)){
				echo "
      <option value='".$aa['UserID']."'>".$aa['FirstName']." ".$aa['LastName']."</option>";
			}
	echo "
    </select>
</form>
  </td>
  <td class='tdformnotes' align='center'>
  <form action='usuario-form.php' method='post'>
    <input type='hidden' value='novo' name='submitted' />
    <input type='hidden' value='$ispopup' name='ispopup'/>
    <input type='submit' value='".GetLangVar('namenovo')." ".GetLangVar('namecadastro')."' class='bsubmit' />
  </form>
  </td>
</tr>";
} else {

echo "
<thead>
<tr>
<td colspan='3'>";
if ($submitted=='editando') {
	$_SESSION['editando']=1;
	$wr = getuser($usuarioid,$conn);
	$ww = mysql_fetch_assoc($wr);
	$quem = $ww['FirstName']." ".$ww['LastName'];
	$nome = $ww['FirstName'];
	$segnome = $ww['Segundonome'];
	$sobrenome = $ww['LastName'];
	$login = $ww['Login'];
	$valid = $ww['Valid'];
	//$senha = $ww['Passwd'];
	$acessonivel = $ww['AccessLevel'];
	$email = $ww['Email'];
	echo GetLangVar('nameeditando')." ".strtolower(GetLangVar('namecadastro'))." ".$quem;
} elseif ($submitted=='novo') {
	unset($_SESSION['editando']);
	echo GetLangVar('namenovo')." ".strtolower(GetLangVar('namecadastro'));
}
echo "</td></tr>
</thead>
<tbody>
<form action='usuario-exec.php' method='post'>
  <input type='hidden' value='$usuarioid' name='usuarioid' />
  <input type='hidden' value='$ispopup' name='ispopup'/>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namenome')."*</td>
  <td class='tdformleft' colspan='2'><input type='text' name='nome' size='30%' value='$nome' /></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('namelastname')."*</td>
  <td class='tdformleft' colspan='2'><input type='text' name='sobrenome' size='30%' value='$sobrenome' /></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>".GetLangVar('messagenameforlogin')."*</td>
  <td class='tdformleft' colspan='2'><input type='text' name='login' size='30%' value='$login' /></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='right'>";
			if ($_SESSION['editando']==1) {
			  	echo  GetLangVar('namenova')." ".strtolower(GetLangVar('namepwd'))."&nbsp;
			  <img height=12 src=\"icons/icon_question.gif\" ";
				$help = GetLangVar('messageuserpwd');
				echo " onclick=\"javascript:alert('$help');\" />";
			} else {
				 echo  GetLangVar('namepwd')."*";
			}
		 echo "</td>  
  <td class='tdformleft' colspan='2'><input type='text' name='senha' size='30%' value='$senha' /></td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
      <td class='tdsmallbold' align='right'>".GetLangVar('messageaccesslevel')."*</td>
      <td class='tdformnotes' colspan='2'>
        <input type='radio' name='acessonivel'";
		  	if (!empty($acessonivel) && $acessonivel=='user') { echo " checked ";}
			  echo "value='user'>".GetLangVar('namesimples')."&nbsp;<img height=12 src=\"icons/icon_question.gif\" ";
				$help = GetLangVar('messageusersimple');
				echo " onclick=\"javascript:alert('$help');\" />
        <input type='radio' name='acessonivel'";
			if (!empty($acessonivel) && $acessonivel=='manager') { echo " checked ";}
			echo "value='manager'>".GetLangVar('namecompleto')."&nbsp;<img height=12 src=\"icons/icon_question.gif\" ";
				$help = GetLangVar('messageusermanager');
				echo " onclick=\"javascript:alert('$help');\" />
      </td>
    </tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
      <tr bgcolor = '".$bgcolor."'>
        <td class='tdsmallbold' align='right'>".GetLangVar('nameemail')."</td>
        <td class='tdformleft' colspan='2'><input type='text' name='email' size='60' value='".$email."' /></td>
      </tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
      <tr bgcolor = '".$bgcolor."'>
        <td class='tdsmallbold' align='right'>Válido</td>";
        if ($valid==1) {
        	$txt  = 'checked';
        } else {
        	$txt = '';
        }
        echo "
        <td class='tdformleft' colspan='2'><input type='checkbox'  name='valid' size='60' ".$txt." value='1' /></td>
      </tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
      <td>&nbsp;</td>
      <td align='right'><input type='submit' class='bsubmit' value=".GetLangVar('namesalvar')." /></td>
</form>    
      <td align='left'>
<form action=usuario-form.php method='post'>
  <input type='hidden' value='$ispopup' name='ispopup'/>
      <input type='submit' class='breset' value=".GetLangVar('namevoltar')." />
</form></td>
</tr>
";
} //else if !empty($usuarioid)
echo "
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>