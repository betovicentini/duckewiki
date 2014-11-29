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
$title = 'Nova Pessoa';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echo "aqui".$_SESSION['editando'];
$nome = $nome;
$sobrenome = $ppost['sobrenome'];

if (empty($nome) || empty($sobrenome)) {
//|| empty($email)) {
echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
			if (empty($nome)) {
				echo "
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenome')."</td></tr>";
			}
			if ( empty($sobrenome) ) {
				echo 
  "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namelastname')."</td></tr>";
			}
			//if (empty($email)) {
				//echo "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>Email</td></tr>";
			//}
echo "
</table>
<br />";
			$erro++;
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
	if ($nres>0 && $_SESSION['editando']!=1) {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro3')."</td></tr>
</table><br />";
			$erro++;
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
		if ($_SESSION['editando']) {
				//compara valores antigos
				$changed = CompareOldWithNewValues('Pessoas','PessoaID',$pessoaid,$arrayofvalues,$conn);
				if ($changed>0 && !empty($changed)) { //se mudou atualiza
					CreateorUpdateTableofChanges($pessoaid,'PessoaID','Pessoas',$conn);
					$updatespecid = UpdateTable($pessoaid,$arrayofvalues,'PessoaID','Pessoas',$conn);
					if (!$updatespecid) {
						$erro++;
					} else {
						$ok++;
						unset($_SESSION['editando']);
					}
				} else { //nao mudou nada
					unset($_SESSION['editando']);
				}
		} else { //se novo
			$newspec = InsertIntoTable($arrayofvalues,'PessoaID','Pessoas',$conn);
			if (!$newspec) {
				$erro++;
			} else {
				$ok++;
			}
		}
		if ($ok>0) {
				echo "<p class='success'>".GetLangVar('sucesso1')."</p>";
				unset($_SESSION['editando']);
		} elseif ($erro>0) {
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table>
<br />
";
		}
	}
} 

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>