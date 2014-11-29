<?php
//Este script checa se alguns campos que se referem a localidades e ve se estas ja estao cadastradas

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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
//, "<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Importar Locais - Passo 3a - Checa major Area';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

///municipio, provincia, pais

//extrai variaveis recebidas

$clnl = $tbprefix."CountryID";
$clnl2 = $tbprefix."ProvinceID";

//echopre($ppost);
if (!empty($provincia) && !empty($pais)  && !isset($provinciasdone)) {
		$colcol = $provincia;
			$clnl2 = $tbprefix."ProvinceID";
			if (!isset($provincedone)) {
				$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$clnl2." INT(10) DEFAULT 0";
				@mysql_query($qq,$conn);
				$qq = "UPDATE ".$tbname." as tb, Province as pl set tb.".$clnl2."=pl.ProvinceID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.Province) AND pl.CountryID=tb.".$clnl;
				mysql_query($qq,$conn);
			} else {
				if (isset($provinciaid) && count($provinciaid)>0 && is_array($provinciaid)) {
					foreach ($provinciaid as $kk => $vv) {
						$vvv = explode("_",$vv);
						$v1 = $vvv[0]+0;
						$v2 = $vvv[1]+0;
						if ($v1>0) {
							$qq = "UPDATE `".$tbname."` SET `".$clnl2."`= ".$v1." WHERE `".$colcol."`='".$kk."' AND `".$clnl."`='".$v2."'";
							mysql_query($qq,$conn);
							flush();
						}
					}
				}
			}
			$qq = "SELECT DISTINCT ".$colcol." as missgen,".$clnl." FROM ".$tbname." WHERE ".$clnl2."=0";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			unset($ppost['provinceid']);
			if ($nr>0) {
			echo "
<br />
<table align='center' class='myformtable' cellpadding='7'>
  <thead>
    <tr><td colspan='3'>MajorArea (".$colcol.") não encontrados no Wiki</td></tr>
    <tr class='subhead'>
      <td>Nome</td>
      <td>Pode ser um desses</td>
      <td>Cadastre novo</td>
    </tr>
  </thead>
  <tbody>
  <form action='import-locais-step3a.php' method='post'>";
			unset($ppost['provincedone']);
			foreach ($ppost as $kk => $vv) {
						if (!empty($vv)) {
							echo "
            <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
				}
					echo "
            <input type='hidden' name='provincedone' value='1' />";
					while ($rw = mysql_fetch_assoc($rr)) {
						$gk = $rw['missgen'];
						$gen = strtolower(trim($rw['missgen']));
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$coutid = trim($rw[$clnl]);
						$lab = $gk;
						if ($coutid>0) {
							$qq = "SELECT  Country FROM Country WHERE CountryID=".$coutid;
							$rcou = mysql_query($qq,$conn);
							$rww = mysql_fetch_assoc($rcou);
							$crt = $rww['Country'];
							$lab = $gk." (".$crt.")";
						}
						$nc = strlen($gen);
						$n1 = floor($nc/2)-1;
						$n2 = ceil($nc/2)-1;
						$g1 = substr($gen,0,$n1);
						$g2 = substr($gen,$n1+1,$n2);
						$qq = "SELECT * FROM Province WHERE (LOWER(Province) LIKE '%".$g1."%' OR LOWER(Province) LIKE '%".$g2."%'";
							if (count($gggen)>1) {
								$qqq = "";
								foreach ($gggen as $gg) {
									$gg = trim($gg);
									if (!empty($gg) && strlen($gg)>2) {
										$qqq .= " OR LOWER(Province) LIKE '%".$gg."%'";
									}
								}
							}
						$qq .= $qqq.") AND CountryID=".$rw[$clnl]." ORDER BY Province";
						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
            <tr>
              <td>".$lab."</td>
              <td>
              <select id=\"province_".$ggen."\" name=\"provinciaid[".$gk."]\">
                <option value=''>".GetLangVar('nameselect')."</option>";
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
										echo "
                <option value='".$row['ProvinceID']."_".$row['CountryID']."'>".$row['Province']."</option>";
									}
								} else {
									echo "
                <option selected value=''>Não se parece com nada, cadastre novo!</option>";
								}
						echo "
              </select></td>
              <td align='center'>
                <img src='icons/list-add.png' height=15 ";
								$myurl ="province-popup.php?province_val=province_".$ggen."&countryid=".$coutid."&nome=".$gen;
								echo " onclick = \"javascript:small_window('$myurl',500,350,'Novo MajorArea');\">
              </td>
            </tr>";

				}
					echo "
            <tr><td align='center' colspan='3'><input style='cursor: pointer'  type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
    </form>
</tbody>
</table>
";
		} else {
			$provinciasdone = 1;
			}
	} 
	elseif (empty($provinciasdone)) { 
		$provinciasdone = 1;
}
if ($provinciasdone==1) {
echo "
  <form name='myform' action='import-locais-step3b.php' method='post'>";
	foreach ($ppost as $kk => $vv) {
		if (!empty($vv)) {
			echo "
          <input type='hidden' name='".$kk."' value='".$vv."' />"; 
		}
	}
echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
