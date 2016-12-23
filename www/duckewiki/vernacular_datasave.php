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
if (!isset($ispopup)) {
	$ispopup=1;
}
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
$title = 'Listar espécies';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$nome = ucfirst(strtolower($nome));
$lingua2=trim($lingua2);
if (!empty($lingua2) && $lingua2!=0 && $lingua2!=GetLangVar('nameselect')) {
	$lingua=$lingua2;
}
if (empty($nome)) {
		echo "
br /><table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
			if (empty($nome)) {
				echo "
<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenome')."</td></tr>";
			}
			echo "
</table>
<br />";
			$erro++;
} else {
	//checar se o coletor ja esta cadastrado
	$qq = "SELECT * FROM Vernacular WHERE Vernacular='".$nome."'";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0 && $_SESSION['editando']!=1) {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro3')."</td></tr>
</table><br />";
			$erro++;
	} 
	else {
		$arrayofvalues = array(
			'Vernacular' => $nome,
			'Language' => $lingua,
			'Definition' => $definicao,
			'TaxonomyIDS' => $taxonomyids,
			'Notes' => $obs,
			'Reference' => $referencia
			);
		if ($_SESSION['editando']) {
				//compara valores antigos
				$changed = CompareOldWithNewValues('Vernacular','VernacularID',$vernacularid,$arrayofvalues,$conn);
				if ($changed>0 && !empty($changed)) { //se mudou atualiza
					CreateorUpdateTableofChanges($vernacularid,'VernacularID','Vernacular',$conn);
					$updatespecid = UpdateTable($vernacularid,$arrayofvalues,'VernacularID','Vernacular',$conn);
					if (!$updatespecid) {
						$erro++;
					} else {
					echo "
<br />
<table cellpadding=\"1\" width='60%' align='center' class='success'>
<tr><td align='center'>".GetLangVar('sucesso1')."</td></tr>";
if ($ispopup==1) {
echo "<tr><td><input type=button value=".GetLangVar('namefechar')." onClick=\"window.close();\" />"; 
}
echo "
</table>";
						unset($_SESSION['editando']);
					}
				} else { //nao mudou nada
					echo "
<br />
<table cellpadding=\"1\" width='60%' align='center' class='erro'>
<tr><td align='center'>".GetLangVar('messagenochange')."</td></tr>";
if ($ispopup==1) {
echo "<tr><td><input type=button value=".GetLangVar('namefechar')." onClick=\"window.close();\" />"; 
}
echo "
</table>";
					unset($_SESSION['editando']);
				}
		} else { //se novo
			$newspec = InsertIntoTable($arrayofvalues,'VernacularID','Vernacular',$conn);
			if (!$newspec) {
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>";
if ($ispopup==1) {
echo "<tr><td><input type=button value=".GetLangVar('namefechar')." onClick=\"window.close();\" />"; 
}
echo "
</table>
<br />
				";
				$erro++;
			} else {
				unset($_SESSION['editando']);
				echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
<tr><td>".GetLangVar('sucesso1')."</td></tr>";
if ($ispopup==1) {
echo "<tr><td><input type=button value=".GetLangVar('namefechar')." onClick=\"window.close();\" />"; 
}
echo "
</table>
<br />";
			}
		}
	}
} 
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>