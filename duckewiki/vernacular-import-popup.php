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
$ispopup =1;


$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$body= '';
$title = GetLangVar('namenovo')." ".GetLangVar('namevernacular');
FazHeader($title,$body,$which_css,$which_java,$menu);
if ($final==1) {
	foreach ($inputs as $orvaluefield => $vernas) {
		$arrayvelho = explode(";",$orvaluefield);
		foreach ($vernas as $peop => $novos) {
				$pessid = $novos['vernacularid']+0;
				$pesstxt = trim($novos['replacement']);
				$pessclean = $novos['esvaziar']+0;
				if ($pessid>0) {
					$qq = "SELECT * FROM Vernacular WHERE VernacularID='".$pessid."'";
					$res = mysql_query($qq,$conn);
					$rw = mysql_fetch_assoc($res);
					$abrv = trim($rw['Vernacular']);
					$ky = array_search($peop,$arrayvelho);
					$arrayvelho[$ky] =  $abrv;
				} else {
					if (!empty($pesstxt)) {
						$ky = array_search($peop,$arrayvelho);
						$arrayvelho[$ky] =  $pesstxt;
					} elseif ($pessclean==1) {
						$ky = array_search($peop,$arrayvelho);
						$arrayvelho[$ky] =  ' ';
					}
				}

		}
		$novovalor = implode(";",$arrayvelho);

		if ($novovalor<>$orvaluefield) {
			$qq = "UPDATE ".$tbname." SET `".$orgcol."`='".$novovalor."' WHERE `".$orgcol."`='".$orvaluefield."'";
			mysql_query($qq,$conn);
		}
	}
	$qq = "UPDATE ".$tbname." set `".$colname."`=vernacularschecks(`".$orgcol."`) WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."`='ERRO'";
	mysql_query($qq,$conn);
}

$query = "SELECT DISTINCT `".$orgcol."` FROM ".$tbname."  WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND `".$colname."`='ERRO'";
$res = mysql_query($query,$conn);
$nres = mysql_numrows($res);
$erro=0;
if ($nres>0) {
echo "<br />
<table align='center' class='myformtable' cellpadding='7' width=\"100%\">
<thead>
  <tr><td colspan='100%'>Os seguintes vernaculars na coluna $orgcol não estão cadastrados</td>
  </tr>
    <tr class='subhead'>
    <td width=\"15%\">Registro original no arquivo</td>
    <td width=\"85%\">
      <table cellpadding='7' align='left' width=\"100%\">
      <tr >
        <td  width=\"40%\" align='center'><b>Nomes não cadastrados</b></td>    
        <td  width=\"30%\" align='center'><b>Pode ser?</b></td>    
        <td  width=\"10%\" align='center'><b>Novo</b></td>
        <td  width=\"20%\" align='center'><b>Substitua por</b></td>
        <td  width=\"10%\" align='center'><b>Substituir por vazio</b></td>
      </tr>
      </table>
    </td>
  </tr>
</thead>
<tbody>
<form action='vernacular-import-popup.php' method='post'>
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='colname' value='".$colname."' />
  <input type='hidden' name='orgcol' value='".$orgcol."' />
  <input type='hidden' name='buttonidx' value='".$buttonidx."' />
  <input type='hidden' name='final' value='1' />
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  ";

$idxpess = 1;
while ($row = mysql_fetch_assoc($res)) {
	$vernacu = $row[$orgcol];
	$qu = "SELECT vernacularschecks('".$vernacu."') as verna";
	$ru = mysql_query($qu,$conn);
	$rw = mysql_fetch_assoc($ru);
	if ($rw['verna']=='ERRO') {
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' width=\"15%\">".$vernacu."</td>
  <td width=\"85%\">
    <table cellpadding='7' align='left' width=\"100%\">";
	$vernaarr = explode(";",$vernacu);
	$idxps = 1;
	foreach ($vernaarr as $ps) {
		if (!isset($inputs)) {
			$inputs = array("'".$vernacu."'" => array("'".$ps."'"));
		} else {
			$ipt = array("'".$vernacu."'" => array("'".$ps."'"));
			$inputs = array_merge((array)$inputs,(array)$ipt);
		}
		$qu = "SELECT vernacularschecks('".$ps."') as peop";
		$ru = mysql_query($qu,$conn);
		$rw = mysql_fetch_assoc($ru);
		if ($rw['peop']=='ERRO') {
	echo "
      <tr>
        <td  width=\"40%\" align='right'>".$ps."</td>";
			$qq = "SELECT * FROM Vernacular WHERE";
			$pps = str_replace("."," ",$ps);
			$pps = str_replace(","," ",$pps);
			$pps = str_replace("-"," ",$pps);
			$pps = str_replace("  "," ",$pps);
			$pps = str_replace("  "," ",$pps);
			$colarr = explode(" ",$pps);
			if (count($colarr)>1) {
				$i=0;
				foreach ($colarr as $cc) {
					$cc = strtolower(trim($cc));
					if (strlen($cc)>2) {
						$cc = substr($cc,0,1);
						if ($i>0) {
							$qq .= " OR";
						}
						$qq .= " LOWER(Vernacular) LIKE '".$cc."%'";
						$i++;
					} 
				}
			} 
			else {
				$cc = strtolower(trim($pps));
				$cc = substr($cc,0,5);
				$qq .= " LOWER(Vernacular) LIKE '".$cc."%'";
			}
			$qq .= " ORDER BY Vernacular ";

echo "
        <td  width=\"30%\" align='left'>
          <select id='vernacularid_".$idxpess."_".$idxps."' name='inputs[".$vernacu."][".$ps."][vernacularid]'>
            <option value=''>Pode ser um desses?</option>";
				$rss = mysql_query($qq,$conn);
				$nrss = mysql_numrows($rss);
				if ($nrss>0) {
					while ($rww = mysql_fetch_assoc($rss)) {
echo "
            <option value=".$rww['VernacularID'].">".$rww['Vernacular']."</option>";            
					}
echo "
            <option value=''>-----------------------</option>";
            	$qq = "SELECT * FROM Vernacular ORDER BY Vernacular ASC";
				$rss = mysql_query($qq,$conn);
				$nrss = mysql_numrows($rss);
				if ($nrss>0) {
					while ($rww = mysql_fetch_assoc($rss)) {
echo "
            <option value=".$rww['VernacularID'].">".$rww['Vernacular']."</option>";
					}            
				}
				} else {
echo "
            <option selected value=''>Não se parece com nada!</option>
            <option value=''>-----------------------</option>";
            $qq = "SELECT * FROM Vernacular ORDER BY Vernacular ASC";
				$rss = mysql_query($qq,$conn);
				$nrss = mysql_numrows($rss);
					if ($nrss>0) {
						while ($rww = mysql_fetch_assoc($rss)) {
echo "
            <option value=".$rww['VernacularID'].">".$rww['Vernacular']."</option>";
						}            
					}
				}
echo "
          </select>
        </td>
        <td  width=\"10%\" align='center' >
          <input type='button' class='bblue' value='".GetLangVar('namenova')."' ";
			$myurl ="vernacular-import-novo.php?ispopup=1&vernacular_val=vernacularid_".$idxpess."_".$idxps."&nome=".$ps;
		echo " onclick = \"javascript:small_window('$myurl',500,400,'Novo Vernacular');\" /></td>
		<td   width=\"20%\" align='center'>
          <input type='text' value='' name='inputs[".$vernacu."][".$ps."][replacement]' />
        </td>
		<td   width=\"10%\" align='center'>
          <input type='checkbox' value='1' name='inputs[".$vernacu."][".$ps."][esvaziar]' />
        </td>

      </tr>";
			$idxps++;
		}
	}
echo "
    </table>
  </td>
</tr>";
	}
	$idxpess++;
}

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></td></tr>
</tbody>
</table>
</form>";

} else {
	echo "
  <form >
      <script language=\"JavaScript\">
      setTimeout( function(){
       var element = window.opener.document.getElementById('".$buttonidx."');
       element.value = 'Foram Corrigidos';
       window.close();
       },0.0001);
    </script>
  </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>