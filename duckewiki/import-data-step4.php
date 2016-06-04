<?php
//ESTE SCRIPT CHECA OS CAMPOS QUE CONTEM NOMES DE PESSOAS
//SE TUDO ESTIVER OK VOLTA PARA O HUB
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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Importar Dados Passo 04';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);



$fieldsign = $_SESSION['fieldsign'];
$fields = unserialize($_SESSION['fieldsign']);
if (!isset($pessoasvars)) {
		$pessoasvars = array();
		$colnames = array();
		if (in_array('COLETOR',$fields)) {
			$za = array_keys($fields, "COLETOR");
			if (count($za)==1) {
				$colnames[] = $tbprefix."ColetorID";
				$pessoasvars[] = $za[0];
			}
		}
		if (in_array('ADDCOLL',$fields)) {
			$za = array_keys($fields, "ADDCOLL");
			if (count($za)==1) {
				$colnames[] = $tbprefix."AddColIDS";
				$pessoasvars[] = $za[0];
			}

		}

		if (in_array('REFCOLETOR',$fields)) {
			$za = array_keys($fields, "REFCOLETOR");
			if (count($za)==1) {
				$colnames[] = $tbprefix."RefColetor";
				$pessoasvars[] = $za[0];
			}
		}
		if (in_array('DETBY',$fields)) {
			$za = array_keys($fields, "DETBY");
			if (count($za)==1) {
				$colnames[] = $tbprefix."DetbyID";
				$pessoasvars[] = $za[0];
			}
		}
		if (in_array('REFDETBY',$fields)) {
			$za = array_keys($fields, "REFDETBY");
			if (count($za)==1) {
				$colnames[] = $tbprefix."RefDetby";
				$pessoasvars[] = $za[0];
			}
		}
		$start=1;
} else {
	$pessoasvars = unserialize($peoplevars);
}
if (count($pessoasvars)>0) {
	$oldpessoasvars = $pessoasvars;
	$idx=1;
	foreach ($pessoasvars as $peskk => $pesvv) {
		$cln = $colnames[$peskk];
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln." VARCHAR(100) DEFAULT ''";
		@mysql_query($qq,$conn);
		$qq = "UPDATE ".$tbname." SET `".$cln."`=checkpessoas(`".$pesvv."`) where `".$pesvv."`<>'' AND `".$pesvv."` IS NOT NULL";
		mysql_query($qq,$conn);
		$qq = "SELECT DISTINCT `".$pesvv."` FROM `".$tbname."`  WHERE `".$pesvv."`<>'' AND `".$pesvv."` IS NOT NULL AND `".$cln."`='ERRO'";
		$res = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		if ($nres>0) {
			if ($nused==0) {
echo "<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='3'>ATENÇÃO! Sobre colunas com nomes de pessoas</td></tr>
  <tr class='subhead'>
    <td>Nome da coluna</td>
    <td>Problema encontrado</td>
    <td>O que fazer?</td>
  </tr>
</thead>
<tbody>";
$nused=1;
			}
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'>
    <td>".$pesvv."</td>";
echo "
    <td>".$nres." registros tem pessoas que não foram encontrados no wiki!</td>";
    
echo "
    <td align='center'>
      <input id='butidx_".$idx."' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em\"
 value='Corrigir' ";
$myurl ="novaspessoas-popup.php?colname=".$cln."&orgcol=".$pesvv."&tbname=".$tbname."&buttonidx=butidx_".$idx; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Corrigir valores de nomes de pessoas');\" />
    </td>    
    
  </tr>";

		} 
		else {
			unset($oldpessoasvars[$peskk]);
		}
		$idx++;
	}
	if ($nused>0) {
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			if (count($oldpessoasvars)>0) {
echo "
<form action='import-data-step4.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />  
";
echo "
  <input name='peoplevars' value='".serialize($oldpessoasvars)."' type='hidden' />";
echo "
  <input name='tbname' value='".$tbname."' type='hidden' />
  <input type='hidden' name='tbprefix' value='".$tbprefix."'>
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='3'>
    <input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' />
  </td></tr>
</form>";
			} else {
				echo "
<form action='import-data-step4.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />  
  <input type='hidden' name='tbprefix' value='".$tbprefix."' />
  <input name='tbname' value='".$tbname."' type='hidden' />
  <input name='var_moni_ok' value='1' type='hidden' />
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='3'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</form>";
			}
echo "
</tbody>
</table>";
		} else {
			$done=TRUE;
		}
} else {
	$done=TRUE;
}

if ($done) {
	$steps = unserialize($_SESSION['importacaostep']);
	unset($steps[0]);
	$stt = array_values($steps);
	$_SESSION['importacaostep'] = serialize($stt);
echo "
  <form name='myform' action='import-data-hub.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />    
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>