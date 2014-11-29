<?php
//CHECA FAMILIA
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


$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Importar Especialistas Passo 04';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//$fields = unserialize($_SESSION['fieldsign']);
$collfam = $tbprefix.'FamiliaID';
if (!isset($familydone)) {
$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$collfam." INT(10) DEFAULT 0";
//echo $qq."<br />";
@mysql_query($qq,$conn);
$qq = "UPDATE ".$tbname." as tb, Tax_Familias as pl set tb.".$collfam."=pl.FamiliaID where LOWER(TRIM(tb.".$familiacol."))=LOWER(pl.Familia) AND pl.Valid=1";
mysql_query($qq,$conn);
} else {
	if (count($familianovo)>0) {
		foreach ($familianovo as $kk => $vv) {
			$vv = trim($vv);
			if (!empty($vv)) {
				$val = str_replace("'","",$kk);
				$qq = "UPDATE ".$tbname." as tb set tb.".$familiacol."= '".$vv."' where tb.".$familiacol."='".$val."'";
				mysql_query($qq,$conn);
				flush();
			}
		}
		$qq = "UPDATE ".$tbname." as tb, Tax_Familias as pl set tb.".$collfam."=pl.FamiliaID where LOWER(TRIM(tb.".$familiacol."))=LOWER(pl.Familia) AND pl.Valid=1 AND tb.".$collfam."=0";
		mysql_query($qq,$conn);
	}
	if (count($familiaid)>0) {
		foreach ($familiaid as $kk => $vv) {
			$vv = $vv+0;
			if ($vv>0) {
				$qq = "UPDATE ".$tbname." as tb set tb.".$collfam."= ".$vv." where tb.".$familiacol."='".$kk."'";
				mysql_query($qq,$conn);
				flush();
			}
		}
	}
}
$qq = "SELECT DISTINCT `".$familiacol."` as missgen FROM `".$tbname."` WHERE `".$familiacol."`<>'' AND `".$familiacol."` IS NOT NULL AND `".$collfam."`=0";
$rr = mysql_query($qq,$conn);
$nr = mysql_numrows($rr);
if ($nr>0) {
	echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
 <tr><td colspan='4'>Famílias não encontradas no Wiki</td></tr>
 <tr class='subhead'>
   <td>Nome</td>
   <td>Pode ser uma dessas</td>
   <td>Cadastre nova</td>
   <td>Substitua por</td>
 </tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td class='tdformnotes' colspan='4'>
Se algum registro não tiver identificação no nível de família digite \"Indet\" no campo de substituição.</td></tr>
<form action='import-especialistas-step4.php' method='post'>";
					foreach ($_POST as $kk => $vv) {
						$fm = explode("_",$kk);
						if ($fm!='familianovo' && !empty($vv)) {
							echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					echo "
  <input type='hidden' name='familydone' value='1' />";
					while ($rw = mysql_fetch_assoc($rr)) {
						$gen = strtolower(trim($rw['missgen']));
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$gk = $rw['missgen'];
						$nc = strlen($gen);
						if ($nc>=10) {
							$n1 = floor($nc/2)-1;
							$n2 = ceil($nc/2)-1;
							$g1 = substr($gen,0,$n1);
						} else {
							$g1 = $gen;
						}
						$qq = "SELECT * FROM Tax_Familias WHERE (LOWER(Familia) LIKE '%".$g1."%'";
							if (count($gggen)>1) {
								$qqq = "";
								foreach ($gggen as $gg) {
									$gg = trim($gg);
									if (!empty($gg) && strlen($gg)>2) {
										$qqq .= " OR LOWER(Familia) LIKE '%".$gg."%'";
									}
								}
							}
						$qq .= $qqq.") AND Valid=1 ORDER BY Familia";

						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
<tr bgcolor = '".$bgcolor."'>
  <td><font color='red'>".$gk."</font>  </td>
  <td>
    <select id=\"familia_".$ggen."\" name=\"familiaid[".$gk."]\">
      <option value=''>".GetLangVar('nameselect')."</option>";
      $qzz = "SELECT * FROM Tax_Familias WHERE LOWER(Familia) LIKE '%".$gen."%' AND Valid=0 ORDER BY Familia";
		$rzz = mysql_query($qzz,$conn);
		$nrzz = mysql_numrows($rzz);
		if ($nrzz>0) {
			while($rzwz = mysql_fetch_assoc($rzz)) {
				$qz= "SELECT * FROM Tax_Familias WHERE (Sinonimos LIKE '%familia|".$rzwz['FamiliaID'].";%' OR Sinonimos LIKE '%familia|".$rzwz['FamiliaID']."') AND Valid=1 ORDER BY Familia";
				$rz = mysql_query($qz,$conn);
				while($rzw = mysql_fetch_assoc($rz)) {
						echo "
      <option value=".$rzw['FamiliaID'].">".$rzw['Familia']."</option>";
				}
			}
		} 
		$res = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		if ($nres>0) {
			while ($row = mysql_fetch_assoc($res)) {
				echo "
      <option value=".$row['FamiliaID'].">".$row['Familia']."</option>";
			}
		} 
		echo "
      <option value=''>-------</option>";
		if ($nres==0 && $nrzz==0) {
			echo "
      <option value=''>Nada parecido. Procure abaixo ou cadastre nova!</option>";
	  }
	   $qz= "SELECT * FROM Tax_Familias WHERE Valid=1 ORDER BY Familia";
	  $rz = mysql_query($qz,$conn);
	  while($rzw = mysql_fetch_assoc($rz)) {
        echo "
      <option value=".$rzw['FamiliaID'].">".$rzw['Familia']."</option>";
	  }
						echo "
      </select>
        </td>
        <td align='center'>
        <img style='cursor:pointer;'  src='icons/list-add.png' height='15' ";
		$myurl ="familia-popup.php?familiafieldid=familia_".$ggen."&spnome=".$gen;
		echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Familia');\">
        </td>
        <td align='center'><input type='text' value='' name=\"familianovo[".$gk."]\" /></td>
      </tr>";
}
					if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
					echo "
      <tr bgcolor = '".$bgcolor."'><td align='center' colspan='4'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
    </form>
</tbody>
</table>";
} else {
echo "
  <form name='myform' action='import-especialistas-step5.php' method='post'>";
foreach ($_POST as $kk => $vv) {
							echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
}
echo "
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form> ";
  
}
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>