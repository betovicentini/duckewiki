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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array("<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'GPS Editar';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<table class='myformtable' align='left' cellpadding='7' >
<thead>
<tr><td >Editar ou criar novo ponto de GPS</td></tr>
</thead>
<tbody>
<form name='coletaform' action='gps-ponto-exec.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td class='tdformright' align='center'>Ponto GPS</td>
        <td>
          <table>
            <tr>
              <td class='tdformnotes'>$locality</td>
            </tr>
            <tr>
              <td class='tdformnotes'>"; 
              autosuggestfieldval3('search-gpspoint.php','gpspt',$gpspt,'gpsres','gpspointid',$gpspointid,true,50); 
        echo "</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table align='center' >
      <tr>
        <td align='center' >
          <input type='hidden' name='final' value='' />
          <input type='submit' value='".GetLangVar('nameeditar')."' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\" /> 
        </td>
        <td align='center' ><input type='submit' value='".GetLangVar('namenovo')."' class='bblue' onclick=\"javascript:document.coletaform.final.value=2\"> </td>
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>