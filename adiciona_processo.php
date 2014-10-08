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


$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
);
$which_java = array(
);
$title = 'Adiciona Amostra ao Processo';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($processoid)) {
echo "
<br />
<form action='adiciona_processo.php' method='post'>
<table class='myformtable' align='center' cellpadding='7' width='50%'>
<thead><tr ><td >Adiciona Amostra a Processo</td></tr></thead>
<tbody>
<tr>
  <td>
    <input type='hidden' name='especimenid' value='".$especimenid."' >
    <select name='processoid' onchange='this.form.submit()'>
      <option value=''>".GetLangVar('nameselect')."</option>
      <option value=''>------------</option>";
      $qq = "SELECT * FROM ProcessosEspecs ORDER BY AddedDate DESC";
      $rrr = @mysql_query($qq,$conn);
      while ($row = @mysql_fetch_assoc($rrr)) {
			echo "
      <option value=".$row['ProcessoID'].">".$row['Name']." </option>";
		}
	echo "
    </select>
    </td>
</tr>
</tbody>
</table>
</form>";
} else {
	//echopre($ppost);
	$qn = "SELECT * FROM ProcessosLIST WHERE ProcessoID=".$processoid." AND EspecimenID=".$especimenid;
	$tem = mysql_query($qn,$conn);
	$ntem = mysql_numrows($tem);
	if ($ntem>0) {
			echo "<b>O registro já estava no processo</b>";
	} else {
		$qz = "INSERT INTO ProcessosLIST (ProcessoID,EspecimenID,EXISTE, Herbaria, ".$herbariumsigla." ) (SELECT ".$processoid.", pltb.EspecimenID,1 as EXISTE, pltb.Herbaria, pltb.INPA_ID FROM Especimenes as pltb LEFT JOIN ProcessosLIST as proc USING(EspecimenID) WHERE pltb.EspecimenID=".$especimenid.")";
		$inseriu = mysql_query($qz,$conn);
		if ($inseriu) {
			echo "<b>Inseriu o registro corretamente no processo!<br >Para visualizar regerar a tabela no Processo!<br >O registro foi marcado como existe</b>";
		} else {
				echo $qz."<br >";
				echo "houve um erro";
		}
	}
	echo "<br ><input type='button' onclick='javascript: window.close();'  value='Fechar' >";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//, "<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>