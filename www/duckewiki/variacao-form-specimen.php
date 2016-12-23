<?php

session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

if ($especimenid>0) {
	$qq = "SELECT * FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE EspecimenID='".$especimenid."'";
	$rro = @mysql_query($qq,$conn);
	$rwo= @mysql_fetch_assoc($rro);
	$specname = $rwo['Abreviacao']." ".$rwo['Number'];
} else {
	$specname = '';
}


HTMLheaders('');
echo "
<br>
<table align='left' class='myformtable' cellpadding='7'>
<thead>
  <tr><td colspan=100%>".GetLangVar('messageentrarvariacao')."&nbsp;<i>".GetLangVar('nameexsicata')."</i></td></tr>
</thead>
<tbody>
<tr>
  <td>
    <table>
      <tr>
        <td class='bold'>".GetLangVar('nameformulario')."</td>
<form action='variacao-form-specimen.php' method='post'>
        <td >
          <select name='formid'>";
		if (!empty($formid)) {
			$qq = "SELECT * FROM Formularios WHERE FormID='".$formid."'";
			$rr = mysql_query($qq,$conn);
			$row= mysql_fetch_assoc($rr);
			echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
		} else {
			echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
		}
		//formularios usuario
		$qq = "SELECT * FROM Formularios WHERE AddedBy=".$_SESSION['userid']." OR Shared=1 ORDER BY FormName ASC";
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
        <td class='bold'>".GetLangVar('namecolecao')."</td>
          <td class='tdformnotes'>"; autosuggestfieldval2('search-specimen.php','specname',$specname,'specnameres','especimenid',$especimenid,true); echo "</td>
          <td><input type=submit value='".GetLangVar('nameenviar')."' class='bsubmit'></td> 
</form>
        </tr>
      </table>
    </td>
  </tr>";
//IF FORMULARIO E LINK SELECIONADOS
if (!empty($formid) && ($especimenid+0)>0) {
	if ($traitsinenglish==1) {
		$flag = "brasilFlagicon.png";
		$flagval = 0;
	} else {
		$flag = "usFlagicon.png";
		$flagval = 1;
	}
  echo "
<thead>
  <tr class='subhead'>
  <td colspan=100%>
    <table cellpadding='2' align='center'>
      <tr>
        <td >".GetLangVar('messageentrandodadospara')."&nbsp;&nbsp;&nbsp;&nbsp;</td>";
      $oldvals = EnteringVarFor($especimenid,$plantaid,$infraspid,$speciesid,$genusid,$famid,$conn);
      @extract($oldvals);
  echo "
      </tr>
    </table>
  </td>
</tr>
</thead>
<tbody>
<form name='variationform' action='variacao-form-specimen.php' method='post'>
  <input type='hidden' name='formid' value='$formid'>
  <input type='hidden' name='especimenid' value='$especimenid'>
  <input type='hidden' name='traitsinenglish' value=''>
<tr>
<td  colspan=100% align='right' ><input type='image' height='30' src=\"icons/".$flag."\" onclick=\"javascript:document.variationform.traitsinenglish.value=".$flagval."\"></td>
</tr>
</form>
";
	$actiontofile = 'variacao-exec-new.php';
	$actionfilereset = 'variacao-form-specimen.php';
	echo "
    <form id='varform2' method='POST' enctype='multipart/form-data' action='".$actiontofile."' >
      <input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
      <input type='hidden' name='formid' value='$formid'>
      <input type='hidden' name='especimenid' value='$especimenid'>
      <input type='hidden' name='linkto' value='coletas'>
      <input type='hidden' name='option1' value='2'>
      <input type='hidden' name='actionfilereset' value='".$actionfilereset."'>
      <input type='hidden' name='traitsinenglish' value='".$traitsinenglish."'>
    <tr>
      <td  colspan=100% align='center' >";
	  include "variacao-traitsform.php";
	echo "
      </td>
    </tr>"; //fecha tabela para conteudo do formulario
	echo "
    <tr>
      <td  colspan='100%' >
        <table align='center'>
          <tr>
            <td align='center' ><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' ></td>
    </form>
    <form action='$actionfilereset' method='post' >
      <input type='hidden' name='formid' value='$formid'>
      <input type='hidden' name='especimenid' value='$especimenid'>
            <td align='left'><input type='submit' value='".GetLangVar('namereset')."' class='bblue' ></td>
          </tr>
        </table>
      </td>
    </tr>
  </form>
    <tr><td  colspan='100%' class='tdformnotes'><b>".GetLangVar('nameobs')."</b>: ".GetLangVar('messagemultiplevalues')."</td></tr>";
}

echo "
</tbody>
</table>
"; //fecha tabela do formulario
HTMLtrailers();

?>
