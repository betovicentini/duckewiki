<?php
//este script checa por uma coluna contendo uma classe de habitat
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
$title = 'Importar Dados Passo 13 HABITAT';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$fields = unserialize($_SESSION['fieldsign']);
if (in_array('HABITAT',$fields)) {
	$cln = $tbprefix."HabitatID";
	$za = array_keys($fields, "HABITAT");
	$habitatcol = $za[0];
	if (!isset($inputs)) {
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln." INT(10) DEFAULT 0";
		@mysql_query($qq,$conn);
	} else {
		$qq = "SELECT DISTINCT `".$habitatcol."` FROM `".$tbname."`  WHERE `".$habitatcol."`<>'' AND `".$habitatcol."` IS NOT NULL AND `".$cln."`=0";
		//echo $qq."<br />";
		$res = mysql_query($qq,$conn);
		$idxhab=1;
		while ($row = mysql_fetch_assoc($res)) {
			$newhabid = $inputs["habitatid_".$idxhab]+0;
			$olval = $row[$habitatcol];
			$qq = "UPDATE ".$tbname." SET `".$cln."`='".$newhabid."' WHERE `".$habitatcol."`='".$olval."'";
			mysql_query($qq,$conn);
			$idxhab++;
		}
	}
	$qq = "UPDATE ".$tbname." as tb, Habitat as hab SET tb.`".$cln."`=hab.HabitatID where tb.`".$habitatcol."` IS NOT NULL AND tb.`".$habitatcol."`<>'' AND LOWER(hab.Habitat)=LOWER(tb.`".$habitatcol."`) AND `".$cln."`=0";
	mysql_query($qq,$conn);
	$qq = "SELECT DISTINCT `".$habitatcol."` FROM `".$tbname."`  WHERE `".$habitatcol."`<>'' AND `".$habitatcol."` IS NOT NULL AND `".$cln."`=0";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	//habitats não encontrados
	if ($nres>0) {
echo "<br />
<table align='left' class='myformtable' cellpadding='7'>
<thead>
  <tr><td colspan='100%'>A coluna $habitatcol contém classes de habitat que não estão cadastradas</td></tr>
  <tr class='subhead'>
    <td width=\"40%\">Nomes não cadastradoso</td>
    <td width=\"40%\" align='center'>Pode ser?</td>    
    <td align='center' width=\"20%\">Novo</td>
  </tr>
</thead>
<tbody>
<form action='import-data-step13.php' method='post'>
 <input name='tbname' value='".$tbname."' type='hidden' />
 <input name='tbprefix' value='".$tbprefix."' type='hidden' />
 <input name='tbprefix' value='".$ispopup."' type='ispopup' />";

$idxhab=1;
while ($rrow = mysql_fetch_assoc($res)) {
	$habvalor = trim($rrow[$habitatcol]);
	$qq = "SELECT * FROM Habitat WHERE (";
	$pps = str_replace("."," ",$habvalor);
	$pps = str_replace(","," ",$pps);
	$pps = str_replace("-"," ",$pps);
	$pps = str_replace("  "," ",$pps);
	$pps = str_replace("  "," ",$pps);
	$colarr = explode(" ",$pps);
	if (count($colarr)>1) {
		$i=0;
		foreach ($colarr as $cc) {
			$cc = mb_strtolower(trim($cc));
			if (strlen($cc)>2) {
				$cc = substr($cc,0,1);
				if ($i>0) {
					$qq .= " OR";
				}
				$qq .= " LOWER(Habitat) LIKE '".$cc."%'";
				$i++;
			} 
		}
	} 
	else {
		$cc = mb_strtolower(trim($pps));
		$cc = substr($cc,0,3);
		$qq .= " LOWER(Habitat) LIKE '".$cc."%'";
	}
	$qq .= ") AND HabitatTipo='Class' ORDER BY PathName";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold' width=\"40%\">".$habvalor."</td>
  <td width=\"40%\">
    <select id='habitatid_".$idxhab."' name='inputs[habitatid_".$idxhab."]' />
      <option value=''>Pode ser um desses?</option>";
		$rss = mysql_query($qq,$conn);
		$nrss = mysql_numrows($rss);
		if ($nrss>0) {
			while ($aa = mysql_fetch_assoc($rss)){
          		$PathName = $aa['PathName'];
              echo "
            <option value='".$aa['HabitatID']."'>".$espaco.$aa['PathName']."</option>";
        	}
	} else {
echo "
            <option selected value=''>Não se parece com nada!</option>";
        }
echo "
            <option value=''>-----------------------</option>";
      $qq = "SELECT * FROM Habitat WHERE HabitatTipo LIKE 'Class' ORDER BY PathName";
      $res = mysql_query($qq,$conn);
      while ($aaa = mysql_fetch_assoc($res)){
        $PathName = $aa['PathName'];
        $level = $aaa['MenuLevel'];
        if ($level==1) {
        	$espaco = '';
          echo "
            <option class='optselectdowlight' value='".$aaa['HabitatID']."'>".$espaco."<i>".$aaa['Habitat']."</i></option>";
          } else {
            $espaco = str_repeat('&nbsp;',2).str_repeat('-',$level-1);
            echo "
            <option value='".$aaa['HabitatID']."'>".$espaco.$aaa['Habitat']."</option>";
        }
      }
echo "
          </select>
        </td>
        <td  align='center' width=\"20%\" >
          <input type='button' class='bblue' value='".GetLangVar('namenova')."' ";
			$myurl ="habitatclasse-popup.php?habitat_val=habitatid_".$idxhab."&habitatname=".$habvalor;
		echo " onclick = \"javascript:small_window('$myurl',600,400,'Novo Vernacular');\" /></td>
      </tr>";
      $idxhab++;

    }
      if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</tbody>
</table>
</form>";      
} else {
		$habitatchecked=TRUE;
	}
} else {
	$habitatchecked=TRUE;
}

if ($habitatchecked) {
	$steps = unserialize($_SESSION['importacaostep']);
	unset($steps[0]);
	$stt = array_values($steps);
	$_SESSION['importacaostep'] = serialize($stt);
echo "
  <form name='myform' action='import-data-hub.php' method='post'>
    <input type='hidden' name='ispopup' value='".$ispopup."' />    
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
<!---
<tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
--->
</tbody>
</table>
  </form>";
}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>