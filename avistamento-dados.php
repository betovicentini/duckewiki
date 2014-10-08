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
$ispopup =1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$body='';
$title = 'Dados Avistamento 01';
FazHeader($title,$body,$which_css,$which_java,$menu);
if (!isset($viagemid)) {
echo "
<br />
<table class='myformtable' align='center' cellpadding='7' width='50%'>
<thead><tr ><td colspan='100%'>Selecione uma trilha</td></tr>
</thead>
<tbody>
<form action=avistamento-dados-form.php method='post'>
<tr>
  <td  colspan='100%'>
    <select name='trilhaid' onchange='this.form.submit()'>
      <option value=''>".GetLangVar('nameselect')."</option>
      <option value=''>------------</option>";
		$qq = "SELECT expd.Name,trl.Data,trl.Name as Trilha,trl.TrilhaID FROM Expedicao_Trilhas as trl JOIN Expedicoes as expd ON trl.ViagemID=expd.ViagemID  ORDER BY trl.Name,expd.Name,trl.Data";
		$rrr = mysql_query($qq,$conn);
		while ($row = mysql_fetch_assoc($rrr)) {
			$val = $row['TrilhaID'];
			echo "
      <option value='".$val."'>".$row['Trilha']." [".$row['Name']." - ".$row['Data']."]</option>";
		}
	echo "
    </select>
    </td>
</tr>
</tbody>
</table>
</form>	";
} 

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>