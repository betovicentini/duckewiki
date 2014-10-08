<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//CABECALHO
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'Login';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
omenudeicons($quais, $vertical=FALSE, $position='right' , $iconwidth='30', $iconheight='30' );

if ($blockacess>0) {
echo "
<br>
<br>
<br>
<br>
<p align='center' style='margin-left: 50px; color: red; font-size: 1.5em; width: 50%;' >EM MANUTENÇÃO DE ATUALIZAÇÃO! POR FAVOR, VOLTE MAIS TARDE!</p>
<br>
";
}
echo "
<br>
<br>
<br />
<br />
<br />
<br />
<form id='loginForm' name='loginForm' method='post' action='login-exec.php'>
  <table class='myformtable' align='center' cellpadding='5'>
  <thead><tr><td colspan='2'>Login</td></tr>
  </thead>
  <tbody>
    <tr>
      <td class='tdformright'>".GetLangVar('nameuser')."</td>
      <td class='tdformleft'><input name='login' type='text' id='login' focus() /></td>
    </tr>
    <tr>
      <td class='tdformright'>".GetLangVar('namepwd')."</td>
      <td class='tdformleft'><input name='password' type='password' id='password' /></td>
    </tr>
    <tr>
      <td colspan='2' align='center'><input style='cursor: pointer' type='submit' class='bsubmit' name='Submit' value=".GetLangVar('login')." /></td>
    </tr>
  </tbody>
  </table>
</form>";

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
