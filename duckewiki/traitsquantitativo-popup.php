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
$title = 'Nova categoria de variação';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$query = "SELECT DISTINCT `".$orgcol."` FROM `".$tbname."`  WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND (`".$colname."` LIKE 'ERRO' OR `".$colname."` LIKE '')";
$erro=0;
if ($final==1) {
	$res = mysql_query($query,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0) {
		$n=1;
		while ($row = mysql_fetch_assoc($res)) {
			$idx = 'idx_'.$n;
			$original = $row[$orgcol];
			$novo = $valoresnovos[$idx];
			//quando selecionou um ja existente para substituir
			if ($novo<>$original) {
					$qq = "UPDATE `".$tbname."` SET `".$orgcol."`='".$novo."' WHERE `".$orgcol."`='".$original."'";
					$up = mysql_query($qq,$conn);
					if (!$up) {
						$erro++;
					}
			}
			$n++;
		}
		$qq = "UPDATE `".$tbname."` SET `".$colname."`=checkquantitativecolumn(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND (`".$colname."` LIKE 'ERRO' OR `".$colname."` LIKE '')";
		$erro=0;
		mysql_query($qq,$conn);
	}
}

$res = mysql_query($query,$conn);
$nres = mysql_numrows($res);
if ($nres>0) {
	if ($nres<100) {
	echo "<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
	<tr><td colspan='100%'>Tem valores não numéricos na variável quantitativa $tname</td></tr>
	<tr class='subhead'>
		<td>Valor original no arquivo</td>
		<td style='color:#990000'>Corrigir aqui*</td>
	</tr>
</thead>
<tbody>
<form action='traitsquantitativo-popup.php' method='post'>
  <input type='hidden' name='buttonidx' value='".$buttonidx."' />
  <input type='hidden' name='parentid' value='".$parentid."' />
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='tname' value='".$tname."' />
  <input type='hidden' name='colname' value='".$colname."' />
  <input type='hidden' name='orgcol' value='".$orgcol."' />
  <input type='hidden' name='final' value='1' />";
  		$n=1;
		while ($row = mysql_fetch_assoc($res)) {
		$idx = 'idx_'.$n;
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>".$row[$orgcol]."</td>
  <td><input type='text' name='valoresnovos[$idx]' value='".$row[$orgcol]."' /></td>
</tr>";
			$n++;
		}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td align='center' class='tdformnotes' colspan='100%'>*multiplas medições separadas por ponto e vírgula são válidas, mas os valores individualmente devem ser numéricos</td></tr>
</tbody>
</table>
</form>";
	} else {
echo "
<br />
<table cellpadding=\"5\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Há mais de 100 resgistros com problemas na colunar ".$orgcol.". Corrigir no arquivo original e reinicar a importação! O registro pode ter multiplos valores no mesmo campo desde que separados por ponto e vírgula</td></tr>
  <tr><td align='center'><input type='button' value='".GetLangVar('namefechar')."' class='bsubmit' onclick=\"javascript:window.close();\" /></td></tr>
</table>
<br />";
	}
} else {
	//concluido
	echo "
  <form >
    <script language=\"JavaScript\">
      setTimeout( function() { 
        var element = window.opener.document.getElementById('".$buttonidx."');
        element.value = 'CORRIGIDO';
        this.window.close();
      }
      },0.0001);
    </script>
  </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
