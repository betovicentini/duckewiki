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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' href='javascript/magiczoomplus/magiczoomplus/magiczoomplus.css' type='text/css' media='screen' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script src='javascript/magiczoomplus/magiczoomplus/magiczoomplus.js' type='text/javascript'></script>"
);

$title = 'Mostrar Imagens';
$body = "bgcolor='#4C4646'";
FazHeader($title,$body,$which_css,$which_java,$menu);

$path = "img/copias_baixa_resolucao/";

$files = explode(";",$fn);
echo "
<table width=80% align='center' class='myformtable' cellspacing='5'>
<thead>
<tr><td colspan='2'>".GetLangVar('nameimagens')."</td></tr>
</thead>
<tbody>";
foreach ($files as $kkk => $vv) {
	$vv= trim($vv);
	if (!empty($vv)) {
			$qq = "SELECT * FROM Imagens WHERE ImageID='$vv'";
			$rt = mysql_query($qq,$conn);
			$rtw = mysql_fetch_assoc($rt);
			$filename = trim($rtw['FileName']);

			$autor = $rtw['Autores'];
			$autorarr = explode(";",$autor);
			if (count($autorarr)>0) {
			$j=1;
			foreach ($autorarr as $aut) {
				$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$aut."'";
				$res = mysql_query($qq,$conn);
				$rwr = mysql_fetch_assoc($res);
				if ($j==1) {
					$autotxt = 	$rwr['Abreviacao'];
				} else {
					$autotxt = $autotxt."; ".$rwr['Abreviacao'];
				}
				$j++;
			}
			} 
			$fotodata = $rtw['DateOriginal'];

	$pthumb = "img/thumbnails/";


	$fn = explode("_",$filename);
	unset($fn[0]);
	unset($fn[1]);
	$fn = implode("_",$fn);


	$fntxt = $fn."  <br /> [";
	if (!empty($autotxt)) { $fntxt = $fntxt." ".GetLangVar('namefotografo').": ".$autotxt." - ".$fotodata."]";} else {
		$fntxt = $fntxt.$fotodata."]";
	}
	//echo "<tr><td>$fn</td><tr>";
echo "
<tr class='tdthinborder2'>
  <td align='center'>
    <a href=\"".$path.$filename."\" class='MagicZoomPlus' rel=\"zoom-position:right;zoom-width:400px; zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" ><img height=\"90\" src=\"".$pthumb.$filename."\" /></a>
  </td>
  <td class='tdformnotes' align='left'>
    <table>
      <tr><td><--- Passe o mouse sobre a imagen</td></tr>
      <tr><td>".$fntxt."</td></tr>
      <tr><td><a  style='vertical-align: bottom; align: left; font-size: 0.9em;' href='img/originais/".$filename."' target='_new'>Imagem de melhor resolução</a></td></tr>
    </table>
  </td>
</tr>";
	}
}
echo "
</tbody>
</table>
";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>