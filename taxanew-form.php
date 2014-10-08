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
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css'>",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Nova Taxonomia';
$body= '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<br />
<table class='myformtable' align='center' cellpadding='5' cellspacing='3' width='50%'>
<thead>
<tr><td>Selecione uma das opções abaixo</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	$myurl = 'especie-popup.php?naoeimportacao=1&ispopup=1'; 
	echo "
<tr bgcolor = '".$bgcolor."'><td><input name='nada'  type='radio' value='1' 
 onclick = \"javascript:small_window('$myurl',550,500,'Nova espécie');\" />&nbsp;Nova espécie (nome publicado ou morfotipo)</td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	$myurl = 'infraespecie-popup.php?naoeimportacao=1&ispopup=1'; 
	echo "
<tr bgcolor = '".$bgcolor."'><td><input name='nada' type='radio' value='1' 
 onclick = \"javascript:small_window('$myurl',550,500,'Nova infra-espécie');\" />&nbsp;Nova infra-espécie (nome publicado ou morfotipo)</td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	$myurl = 'genero-popup.php?naoeimportacao=1&ispopup=1'; 
	echo "
<tr bgcolor = '".$bgcolor."'><td><input name='nada' type='radio' value='1' 
 onclick = \"javascript:small_window('$myurl',550,500,'Novo gênero');\" />&nbsp;Novo gênero</td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	$myurl = 'familia-popup.php?naoeimportacao=1&ispopup=1'; 
	echo "
<tr bgcolor = '".$bgcolor."'><td><input name='nada' type='radio' value='1' 
 onclick = \"javascript:small_window('$myurl',700,500,'Nova Familia');\" />&nbsp;Nova familia</td></tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
<td colpsan='100%' style='font-size: 1em; color: darkred'>Atenção: Todos os nomes válidos para o Brasil e de ocorrência na Região Norte foram cadastrados de acordo com a lista das espécies do Brasil.
Portanto, se pretende cadastrar um nome publicado, certifique-se primeiro que ele não tem um sinônimo válido já cadastrado.
Verificar a sinonimia em <a href='http://floradobrasil.jbrj.gov.br/2010/' target='_new'>floradobrasil</a>
</td></tr>
</tbody>
</table>
";
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>