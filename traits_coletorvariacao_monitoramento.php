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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Monitoramento Form';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "
<br />
<form id='varform2' name='monitorform' method='post' enctype='multipart/form-data' action='".$actiontofile."'>
  <input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
  <input type='hidden' name='dataobs' value='".$dataobs."' />
  <input type='hidden' name='formid' value='".$formid."' />
  <input type='hidden' name='plantaid' value='".$plantaid."' />
  <input type='hidden' name='plantatag' value='".$plantatag."' />  
  <input type='hidden' name='ispopup' value='".$ispopup."' />
<table align='center' class='myformtable' cellpadding='6' >
<thead>
<tr ><td colspan='100%'>".GetLangVar('namemonitoramento')."</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td>
          <table>
            <tr>
              <td class='bold'>".GetLangVar('nameformulario')."</td>
              <td >
                <select name='formid' onchange='this.form.submit();'>";
				if (!empty($formid)) {
					$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
					$rr = mysql_query($qq,$conn);
					$row= mysql_fetch_assoc($rr);
					echo "
                  <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
				} else {
					echo "
                  <option value=''>".GetLangVar('nameselect')."</option>";
				}
				//formularios usuario
				$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1  ORDER BY FormName ASC";
				$rr = mysql_query($qq,$conn);
				while ($row= mysql_fetch_assoc($rr)) {
					echo "
                  <option value='".$row['FormID']."'>".$row['FormName']."</option>";
				}
			echo "
                </select>
              </td>
            </tr>
          </table>
        </td>
        <td>
          <table>
            <tr>
              <td class='bold'>".GetLangVar('namedata')." OBS</td>
              <td><input name=\"dataobs\" value=\"$dataobs\" size=\"11\" readonly /></td>
              <td>
                <a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['monitorform'].dataobs);return false;\" >
                  <img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\" />
                </a>
              </td><td>&nbsp;</td>
              <td align='right' >
                <input type='submit' value='Atualizar Data' class='bblue' />
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";
//IF FORMULARIO E LINK SELECIONADOS
if (!empty($formid) && !empty($dataobs)) {

	$actiontofile = 'monitoramento-exec.php';
	$actionfilereset = 'monitoramento-form.php';
	echo "
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td  colspan='100%' align='center' >
";
	  include "traits_generalform.php";
echo "
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td  colspan='100%' >
    <table align='center'>
      <tr>
         <td align='center' ><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></td>
</form>
<form action='$actionfilereset' method='post' >
         <td align='center'>
             <input type='hidden' name='formid' value='$formid' />
             <input type='submit' value='".GetLangVar('namereset')."' class='bblue' />
         </td>
</form>
      </tr>
      <tr>
        <td  colspan='100%' class='tdformnotes'><b>".GetLangVar('nameobs')."</b>: ".GetLangVar('messagemultiplevalues')."</td>
      </tr>
    </table>
  </td>
</tr>
";


}
echo "
</tbody>
</table>
"; //fecha tabela do formulario
if ($option1=='1' && $formid>0) {
	tableofmonitortraits($plantaid,$plantatag,$conn);
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>
