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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'Monografia';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$kv = 'selec_'.$_SESSION['userid'];
unset($_SESSION['monospecids'],$_SESSION['comentarios'],$_SESSION[$kv]);
$kv = 'traitssel'.$_SESSION['userid'];
unset($_SESSION[$kv]);

echo "
<br />
<form method='post' name='finalform' action='monografia-exec.php'>
 <input type='hidden' name='ispopup' value='".$ispopup."' />
<table class='myformtable' align='left' cellpadding='10' >
<thead>
<tr >
<td>Monografia&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
		$help = 'Defina o conteúdo e estrutura de um Tratamento Taxonômico, no formato word!';
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
        <td class='tdsmallbold'>Editar monografia</td>
        <td>
          <select name='monografiaid' onchange=\"this.form.submit();\" >
            <option selected value=''>".GetLangVar('nameselect')."</option>
            <option  value='criar'>----------Criar novo----------</option>";
            if ($acclevel !='admin') {
            	//$qw = ' WHERE AddedBy='.$uuid;
            } 
            $qq = "SELECT * FROM Monografias ".$qw." ORDER BY Titulo";
			$res = @mysql_query($qq,$conn);
			while ($rr = @mysql_fetch_assoc($res)) {
				echo "
            <option value='".$rr['MonografiaID']."'>".$rr['Titulo']."</option>";
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