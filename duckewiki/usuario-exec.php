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
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array(
);
$title = 'Cadastro Usuários';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echo "aqui".$_SESSION['editando'];
//echopre($ppost);
$nome = trim($nome);
$nome = ucfirst(strtolower($nome));
$sn = trim($ppost['sobrenome']);
$sobrenome = ucfirst(strtolower($sn));
$login = trim($ppost['login']);

if (!isset($valid) || empty($valid)) {
	$valid = 0;
}


if (empty($nome) || empty($sobrenome) || empty($login) || (empty($senha) && $_SESSION['editando']!=1)) {
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
			if (empty($login)) {
				echo "
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('messagenameforlogin')."</td></tr>";
			}
			if (empty($senha)) {
				echo "
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namepwd')."</td></tr>";
			}
			echo "
</table><br />";
			$erro++;
} else {
	//checar se o usuario ja esta cadastrado
	$qq = "SELECT * FROM Users WHERE FirstName LIKE '$nome' AND LastName LIKE '$sobrenome'";
	//echo $qq;
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0 && $_SESSION['editando']!=1) {
			echo "
<br /><table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro3')."</td></tr>
</table><br />";
			$erro++;
	} else {
		if (empty($senha)) {
			$arrayofvalues = array(
				'FirstName' => $nome,
				'LastName' => $sobrenome,
				'Login' => $login,
				'AccessLevel' => $acessonivel,
				'Valid'  => $valid,
				'Email' => $email);
		} else {
			$senha = md5($senha);
			$arrayofvalues = array(
				'FirstName' => $nome,
				'LastName' => $sobrenome,
				'Login' => $login,
				'Passwd' => $senha,
				'AccessLevel' => $acessonivel,
				'Valid'  => $valid,
				'Email' => $email
				);
		}
		//echopre($arrayofvalues);
		if ($_SESSION['editando']) {
				//compara valores antigos
				$changed = CompareOldWithNewValues('Users','UserID',$usuarioid,$arrayofvalues,$conn);
				//echo $changed;
				if ($changed>0 && !empty($changed)) { //se mudou atualiza
					CreateorUpdateTableofChanges($usuarioid,'UserID','Users',$conn);
					$updatespecid = UpdateTable($usuarioid,$arrayofvalues,'UserID','Users',$conn);
					if (!$updatespecid) {
						$erro++;
					} else {
						echo "<p class='success' style='width: 50%;'>".GetLangVar('sucesso4')."</p>";
						unset($_SESSION['editando']);
						echo "<form action=usuario-form.php method='post' ><input type='submit' class='bblue' value='Outro usuário' /></form>";
					}
				} else { //nao mudou nada
					unset($_SESSION['editando']);
					echo "<p class='erro' style='width: 50%;'>Não mudou nada</p>";
					echo "<form action=usuario-form.php method='post' ><input type='submit' class='bblue' value='Outro usuário' /></form>";
				}
		} else { //se novo
			$newspec = InsertIntoTable($arrayofvalues,'UserID','Users',$conn);
			if (!$newspec) {
				echo "
<br /><table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table><br />
				";
				$erro++;
			} else {
				echo "<p width='50%' class='success'>".GetLangVar('sucesso1')."</p>";
				unset($_SESSION['editando']);
				echo "<form action=usuario-form.php method='post' ><input type='submit' class='bblue' value='Outro usuário' /></form>";
			}
		}
	}
} 
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>