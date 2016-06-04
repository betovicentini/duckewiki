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
$menu = FALSE;
$title = '';
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >");
$which_java = array();
$body= '';
$title = "Corrigindo valores de bibtext no arquivo a ser importado";
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($final==1) {
	foreach ($inputs as $orvaluefield => $novovalor) {
		$nv = $novovalor;
		$nvv = trim($orvaluefield);
		if ($nv!==$nvv && !empty($nv)) {
			$qq = "UPDATE `".$tbname."` SET `".$orgcol."`='".$novovalor."' WHERE `".$orgcol."`='".$orvaluefield."'";
			//echo $qq."<br />";
			mysql_query($qq,$conn);
		}
	}
	$qq = "UPDATE `".$tbname."` SET `".$colname."`=bib_check(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND (`".$orgcol."` IS NOT NULL) AND (`".$colname."` IS NULL)";
	mysql_query($qq,$conn);
}
$query = "SELECT `".$orgcol."`, COUNT(*) AS nn FROM `".$tbname."`  WHERE `".$orgcol."`<>'' AND (`".$orgcol."` IS NOT NULL) AND (`".$colname."` IS NULL) GROUP BY `".$orgcol."` LIMIT 0,30";
$res = mysql_query($query,$conn);
$nres = mysql_numrows($res);
$erro=0;
if ($nres>0) {
echo "
<br />
<table align='center' class='myformtable' cellpadding='7'>
<thead>
  <tr><td colspan='3'>Os seguintes valores da coluna ".$orgcol." tem erros</td>
  </tr>
    <tr class='subhead'>
    <td >Número de registros</td>
    <td >Valor original</td>
    <td >Corrigir para</td>
    </td>
  </tr>
</thead>
<tbody>
<form action='checkbib-popup.php' method='post'>
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='colname' value='".$colname."' />
  <input type='hidden' name='orgcol' value='".$orgcol."' />
  <input type='hidden' name='buttonidx' value='".$buttonidx."' />
  <input type='hidden' name='final' value='1' />";
while ($row = mysql_fetch_assoc($res)) {
	$data= $row[$orgcol];
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
		echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' align='center'>".$row['nn']."</td>
  <td class='tdsmallbold' align='center'>".$data."</td>
  <td class='tdformnotes' >";
echo "
    <select name='inputs[".$data."]' >";
				echo "
      <option value='' >Selecione</option>";
			$qb = "SELECT * FROM BiblioRefs WHERE BibID ORDER BY FirstAuthor, Year";
			$rb = mysql_query($qb,$conn);
			while($rwb = mysql_fetch_assoc($rb)) {
				echo "
      <option value='".$rwb['BibKey']."' >".ucfirst($rwb['FirstAuthor'])." ".$rwb['Year']." - ".substr($rwb['Title'],0,40)."</option>";
			}
echo "
    </select>
  </td>
  </tr>
";
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'>
  <td align='center' colspan='3'><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></td></tr>
</tbody>
</table>
</form>";
} else {
echo "
  <form >
    <script language=\"JavaScript\">
      setTimeout( function(){
       var element = window.opener.document.getElementById('".$buttonidx."');
       element.value = 'Foram cadastrados';
       window.close();
       },0.0001);
    </script>
  </form>";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>