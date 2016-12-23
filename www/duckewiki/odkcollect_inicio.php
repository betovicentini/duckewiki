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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'ODK Collect Formulário';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "
<br />
<form method='post' name='finalform' action='odkcollect_exec.php'>
<table class='myformtable' align='left' cellpadding='10' >
<thead>
<tr >
<td>ODK Collect Formulário&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
		$help = 'Prepare um formulário para o ODK Collect';
		echo " onclick=\"javascript:alert('$help');\" />
</td></tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td>
          <select name='odkformid' onchange=\"this.form.submit();\" >
            <option selected value=''>".GetLangVar('nameselect')."</option>
            <option  value='criar'>----------Criar novo----------</option>";
            if ($acclevel !='admin') {
            	//$qw = ' WHERE AddedBy='.$uuid;
            } 
            $qq = "SELECT * FROM ODKforms ".$qw." ORDER BY AddedDate";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['ODKformid']."'>".$rr['FormName']." [versão: ".$rr["AddedDate"]."]</option>";
			}
echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
</form>
";

$which_java = array(
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
