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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Classe de hábitat';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($habitatidd!='newid') {
unset($parentid);
unset($habitatdefinicao);
echo "
<br />
<table align='center' class='myformtable' cellpadding='3'>
<thead>
<tr >
<td colspan='2'>".GetLangVar('namenovo')." ".strtolower(GetLangVar('namecadastro'))."</td></tr>
</thead>
<tbody>
<tr>
 <td>
   <table>
     <form action=habitatclasse-popup.php method='post'>
       <input type='hidden' name='habitat_val' value='".$habitat_val."' />
       <input type='hidden' name='habitatidd' value='newid' />
<tr>
  <td class='tdsmallbold'>".GetLangVar('namenome')."</td>
  <td><input type='text' name='habitatname' size=30 value='$habitatname'></td>
</tr>
<tr>
  <td class='tdsmallbold'>".GetLangVar('messagepertenceaclasse')."</td>
  <td>
    <select name='parentid' value='$parentid'>
      <option  value=''>".GetLangVar('nameselect')."</option>";
      $qq = "SELECT * FROM Habitat WHERE HabitatTipo LIKE 'Class' ORDER BY PathName";
      $res = mysql_query($qq,$conn);
      while ($aa = mysql_fetch_assoc($res)){
        $PathName = $aa['PathName'];
        $level = $aa['MenuLevel'];
        if ($level==1) {
        	$espaco = '';
          echo "
      <option class='optselectdowlight' value='".$aa['HabitatID']."'>".$espaco."<i>".$aa['Habitat']."</i></option>";
          } else {
            $espaco = str_repeat('&nbsp;',2).str_repeat('-',$level-1);
            echo "
      <option value='".$aa['HabitatID']."'>".$espaco.$aa['Habitat']."</option>";
        }
      }
      echo "
    </select>
  </td>
</tr>
<tr>
    <td class='tdsmallbold'>".GetLangVar('namedefinicao')."</td>
    <td><textarea name='habitatdefinicao' cols='60%' rows='5'>".$habitatdefinicao."</textarea></td>
</tr>
<tr>
      <td colspan='2' align='center'><input type='submit' class='bsubmit' value=".GetLangVar('namesalvar')." /></td>
    </tr>
</form>
</tbody>
</table>";
}
else {
    $pps = str_replace("."," ",$habitatname);
	$pps = str_replace(","," ",$pps);
	$pps = str_replace("-"," ",$pps);
	$pps = str_replace("_"," ",$pps);
	$pps = str_replace("&"," ",$pps);
	$pps = str_replace("  "," ",$pps);
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
					$vr = strtolower($vv);
				} else {
					$vr = ucfirst(strtolower($vv));
				}
				$newn[] = $vr;
				$i++;
			}
		}
		$habitatname = implode(" ",$newn);
	} else {
		$nn = trim($pps);
		$habitatname = ucfirst(strtolower($nn));
	}
	if (empty($habitatname) ) {
echo "
<br />
<form action='habitatclasse-popup.php' method='post'>";
 unset($_POST['habitatidd']);
 foreach ($_POST as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
		}
	}
		echo "
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>
  <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenome')."</td></tr>
  <tr><td align='left'><input type='submit' value='".GetLangVar('namevoltar')."' class='bblue' /></td></tr>
</table>
</form>
";
	} else {
	//checar se o habitat ja esta cadastrado
	$qq = "SELECT * FROM Habitat WHERE Habitat='".$habitatname."'";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	if ($nres==1) {
			echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro3')."</td></tr>
</table>
<br />";
	$erro++;
	$rp = mysql_fetch_assoc($res);
	$vernaid = $rp['HabitatID'];
	$vernatxt = $rp['Habitat'];
echo "
<form >
  <input type='hidden' id='newhabitatidd' value='$vernaid' />
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        passnewidandtxtoselectfield('".$habitat_val."','newhabitatidd','".$vernatxt."','');
      }
      ,0.0001);
</script>
</form>";
	} else {
		//formata o nome vulgar
		$fieldsaskeyofvaluearray = array(
			'Habitat' => $habitatname,
			'HabitatTipo' => 'Class',
			'Descricao' => $habitatdefinicao,
			'ParentID' => $parentid);
		$newhabitatid = InsertIntoTable($fieldsaskeyofvaluearray,'HabitatID','Habitat',$conn);
		if (!$newhabitatid) {
			$erro++;
		} else {
			updatehabitatpath($newhabitatid,$conn);
			$ok++;
echo "
<form >
  <input type='hidden' id='newhabitatidd' value='$newhabitatid' />
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        passnewidandtxtoselectfield('".$habitat_val."','newhabitatidd','".$habitatname."','');
      }
      ,0.0001);
</script>
</form>";
		}
	}
} 
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>