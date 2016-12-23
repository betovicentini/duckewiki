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
$body= '';
$title = GetLangVar('namenovo')." ".GetLangVar('namevernacular');

PopupHeader($title,$body);

if ($vernacularid!='newid') {
echo "
<br>
<table align='left' class='myformtable' cellpadding='3'>
<thead>
<tr >
<td colspan=100%>";
unset($_SESSION['editando']);
echo GetLangVar('namenovo')." ".mb_strtolower(GetLangVar('namecadastro'));
echo "</td></tr>
</thead>
<tbody>
<tr>
 <td>
   <table>
     <form action=vernacularnovo-popup.php method='post'>
       <input type='hidden' name='vernacular_val' value='$vernacular_val' >
       <input type='hidden' name='vernacularid' value='newid' >
    <tr>
      <td class='tdsmallbold' align='right'>".GetLangVar('namenome')."*</td>
      <td class='tdformnotes' colspan=2><input type='text' name='nome' size='30%' value='$nome'></td>
    </tr>
    <tr>
      <td class='tdsmallbold' align='right'>".GetLangVar('namelanguage')."</td>
      <td class='tdsmallbold' colspan=2>
        <input type='text' name='lingua' size='20%' value='$lingua'> 
        ".mb_strtolower(GetLangVar('nameor'))."
        <select name='lingua2'>";
			if (!empty($lingua)) {
				echo "
          <option>".GetLangVar('nameselect')."</option>";
			} else {
				echo "
        <option value=$lingua>".$lingua."</option>";
			}
			$qq = "SELECT DISTINCT Language FROM Vernacular ORDER BY Language";
			$rr = mysql_query($qq,$conn);
			while ($row = mysql_fetch_assoc($rr)) {
				echo "
        <option value=".$row['Language'].">".$row['Language']."</option>";
			}
	echo "
        </select>
      </td>
    </tr>
    <tr>
      <td class='tdsmallbold' align='right'>".GetLangVar('namesignificado')."</td>  
      <td class='tdformnotes' colspan=2><textarea name='definicao' cols=40 rows=2 wrap=SOFT>$definicao</textarea></td>
    </tr>
    <tr>
      <td class='tdsmallbold' align='right'>".GetLangVar('namereference')."</td>
      <td class='tdformnotes' colspan=2><input type='text' name='referencia' size='30%' value='$referencia'></td>
    </tr>
    <tr>
      <td class='tdsmallbold' align='right'>".GetLangVar('nameobs')."</td>
      <td class='tdformnotes' colspan=2><textarea name='obs' cols=40 rows=5 wrap=SOFT>$obs</textarea></td>
    </tr>
    <tr>
      <td colspan=3 align='center'><input type='submit' class='bsubmit' value=".GetLangVar('namesalvar')."></td>
    </tr>
</form>
  </table>
</td>
</tr>
</tbody>
</table>";
}
else {
    $pps = str_replace("."," ",$nome);
	$pps = str_replace(","," ",$pps);
	$pps = str_replace("-"," ",$pps);
	$pps = str_replace("_"," ",$pps);
	$pps = str_replace("&"," ",$pps);
	$pps = str_replace("  "," ",$pps);
	$pps = str_replace("  "," ",$pps);
	$newn = array();
	$narr = explode(" ",$pps);
	if (count($narr)>1) {
		$i=1;
		foreach ($narr as $vv) {
			$vv = trim($vv);
			if (!empty($vv)) {
				$nc = strlen($vv);
				if ($nc<=3 && $i>1) {
					$vr = mb_strtolower($vv);
				} else {
					$vr = ucfirst(strtolower($vv));
				}
				$newn[] = $vr;
				$i++;
			}
		}
		$nome = implode(" ",$newn);
	} else {
		$nn = trim($pps);
		$nome = ucfirst(strtolower($nn));
	}
	if (empty($nome)) {

echo "
<br>
<form action='vernacularnovo-popup.php' method='POST' name='impprepform'>";
 unset($_POST['vernacularid']);
 foreach ($_POST as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."'>"; 
		}
	}
		echo "
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenome')."</td></tr>
  <tr><td align='left'><input type='submit' value='".GetLangVar('namevoltar')."' class='bblue'></td></tr>
</table>
</form>
";
	} else {
	//checar se o vernacular ja esta cadastrado
	$qq = "SELECT * FROM Vernacular WHERE Vernacular='".$nome."'";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0) {
			echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro3')."</td></tr>
</table>
<br>";
	$erro++;
	$rp = mysql_fetch_assoc($res);
	$vernaid = $rp['VernacularID'];
	$vernatxt = $rp['Vernacular'];
echo "
<form >
  <input type='hidden' id='newvernacularid' value='$vernaid' >
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        passnewidandtxtoselectfield('".$vernacular_val."','newvernacularid','".$vernatxt."','');
      }
      ,0.0001);
</script>
</form>";
	} else {
		//formata o nome vulgar
		$arrayofvalues = array(
			'Vernacular' => $nome,
			'Language' => $lingua,
			'Definition' => $definicao,
			'Notes' => $obs,
			'Reference' => $referencia
			);
		$newspec = InsertIntoTable($arrayofvalues,'VernacularID','Vernacular',$conn);
		if (!$newspec) {
			$erro++;
		} else {
			$ok++;
echo "
<form >
  <input type='hidden' id='newvernacularid' value='$newspec' >
  <script language=\"JavaScript\">
    setTimeout(
      function() {passnewidandtxtoselectfield('".$vernacular_val."','newvernacularid','".$nome."','');}
      ,0.0001);
</script>
</form>";
		}
	}
} 
}

PopupTrailers();

?>