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
$ispopup=1;
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
$title = 'Pessoas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
if ($pessoaid!='newid') {
echo "
<br />
<table align='center' class='myformtable' cellpadding='3'>
<thead>
<tr >
<td colspan='100%'>";
unset($_SESSION['editando']);
echo GetLangVar('namenovo')." ".strtolower(GetLangVar('namecadastro'));
echo "</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<form action=novapessoa-form-popup.php method='post'>
  <input type='hidden' name='pessoaid_val' value='$pessoaid_val' />
  <input type='hidden' name='secondid_val' value='$secondid_val' />
  <input type='hidden' value='newid' name='pessoaid' />
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
      <td class='tdformleft' colspan='2'><textarea name='obs' cols='40' rows='5' wrap=SOFT>$obs</textarea></td>
  </tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
      <td align='right'><input type='submit' class='bsubmit' value='".GetLangVar('nameconcluir')."' /></td>
  </form>    
    <form action=novapessoa-form-popup.php method='post'>
    <input type='hidden' name='pessoaid_val' value='$pessoaid_val' />
    <input type='hidden' name='secondid_val' value='$secondid_val' />
        <td align='left'><input type='submit' class='breset' value='".GetLangVar('namevoltar')."' /></td>
  </form>
  </tr>
</tbody>
</table>";
} 
else 
{
	//$nome = ucfirst(strtolower($nome));
	//$sobrenome = ucfirst(strtolower($_POST['sobrenome']));

	if (empty($nome) || empty($sobrenome)) {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
			if (empty($nome)) {
				echo "
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenome')."</td></tr>";
			}
			if ( empty($sobrenome) ) {
				echo "
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namelastname')."</td></tr>";
			}
			$erro++;
echo "
</table>
<br />";
	} 
	else {
	//checar se o coletor ja esta cadastrado
	$qq = "SELECT * FROM Pessoas WHERE LOWER(Prenome)='".strtolower($nome)."' ";
	if (!empty($segnome)) {
		$qq .= " AND LOWER(SegundoNome)='".strtolower($segnome)."'";
	}
	$qq .= " AND LOWER(Sobrenome)='".strtolower($sobrenome)."'";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0) {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro3')."</td></tr>
</table>
<br />";
			$erro++;
			$rp = mysql_fetch_assoc($res);
			$newspec = $rp['PessoaID'];
			$abreviacao = $rp['Abreviacao'];
			echo "
  <form >
  <input type='hidden' id='pessoaid' value='$newspec' />
  <input type='hidden' id='abreviacao' value='$abreviacao' />
  <script language=\"JavaScript\">
  setTimeout(
    function() {
      passnewidandtxtoselectfield('".$pessoaid_val."','pessoaid','".$abreviacao."','".$secondid_val."');
      }
      ,0.0001);
  </script>
  </form>";
	} else {
		if (empty($abrv)) {
		//cria abreviacao
			$tres = NULL;
			$segundo = NULL;
		if (!empty($segnome)) {
			$tt = explode(" ",$segnome);
			$tn = count($tt);

			foreach ($tt as $secn) {
				$tres = $tres.strtoupper(substr($secn,0,1)).".";
				$segundo = $segundo.ucfirst(strtolower($secn))." ";
			}
		}
			$abreviacao = $sobrenome.", ".strtoupper(substr($nome,0,1)).".".$tres;
		} else {
			$abreviacao = $abrv;
		}

		$arrayofvalues = array(
			'Prenome' => $nome,
			'Sobrenome' => $sobrenome,
			'SegundoNome' => $segnome,
			'Email' => $email,
			'Abreviacao' => $abreviacao,
			'Notes' => $obs
			);

		$newspec = InsertIntoTable($arrayofvalues,'PessoaID','Pessoas',$conn);
		if (!$newspec) {
			$erro++;
		} else {
			$ok++;

			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
  <tr class='tdsmallbold' ><td align='center'>Cadastro realizado</td></tr>
<form >
  <input type='hidden' id='pessoaid' value='$newspec' />
  <input type='hidden' id='abreviacao' value='$abreviacao' />
  <script language=\"JavaScript\">
  setTimeout(
    function() {
      passnewidandtxtoselectfield('".$pessoaid_val."','pessoaid','".$abreviacao."','".$secondid_val."');
      }
      ,0.0001);
  </script>";
//<tr bgcolor = '".$bgcolor."'><td align='center' ><input type='submit' value='Fechar' class='bsubmit' onclick=\"javascript:window.close();\" /></td></tr>
echo "
</form>
</table>
<br />
  ";
		} 
	}
} 
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>