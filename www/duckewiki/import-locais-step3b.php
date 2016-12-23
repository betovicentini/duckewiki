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


//echopre($ppost);
//echopre($gget);

//CABECALHO
 $ispopup=1; $menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'Importar Locais - Passo 3b - Checa Minor Area';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$clnl = $tbprefix."CountryID";
$clnl2 = $tbprefix."ProvinceID";
$clnl3 = $tbprefix."MunicipioID";
//echopre($ppost);



if (!empty($municipio) && !empty($provincia) && !isset($municipiosdone)) 
{
			$colcol = $municipio;
			$clnl3 = $tbprefix."MunicipioID";
			if (!isset($municipiodone)) {
				$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$clnl3." INT(10) DEFAULT 0";
				@mysql_query($qq,$conn);
				$qq = "UPDATE ".$tbname." as tb, Municipio as pl set tb.".$clnl3."=pl.MunicipioID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.Municipio) AND pl.ProvinceID=tb.".$clnl2;
				mysql_query($qq,$conn);
			} else {
				if (count($municipioid)>0) {
					foreach ($municipioid as $kk => $vv) {
						$kkk = explode("_",$kk);
						$kkk = $kkk[0];
						$vvv = explode("_",$vv);
						$v1 = $vvv[0]+0;
						$v2 = $vvv[1]+0;
						if ($v1>0) {
							$qq = "UPDATE `".$tbname."` SET `".$clnl3."`= '".$v1."' WHERE `".$colcol."`='".$kkk."' AND `".$clnl2."`='".$v2."'";
							echo $qq."<br />";
							mysql_query($qq,$conn);
							flush();
						}
					}
				}
			}
			$qq = "SELECT DISTINCT `".$colcol."` as missgen,`".$clnl2."` FROM `".$tbname."` WHERE `".$clnl3."`=0";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			unset($ppost['municipioid']);
			if ($nr>0) {
			echo "
<br />
<table align='left' class='myformtable' cellpadding='5'>
  <thead>
    <tr><td colspan='100%'>MinorArea não encontrados no Wiki</td>
    </tr>
    <tr class='subhead'>
    <td>Nome</td>
    <td>Pode ser um desses</td>
    <td>Cadastre novo</td>
    </tr>
  </thead>
  <tbody>
  <form action='import-locais-step3b.php' method='post'>";
					unset($ppost['municipiodone']);
					foreach ($ppost as $kk => $vv) {
						if (!empty($vv)) {
							echo "
        <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					echo "
        <input type='hidden' name='municipiodone' value='1' />";
        			$idd=0;
					while ($rw = mysql_fetch_assoc($rr)) {
						$idd++;
						$gen = mb_strtolower(trim($rw['missgen']));
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$gk = $rw['missgen'];
						$coutid = trim($rw[$clnl2]);
						$lab = $gk;
						if ($coutid>0) {
							$qq = "SELECT  Province,Country FROM Province JOIN Country USING(CountryID) WHERE ProvinceID=".$coutid;
							$rcou = mysql_query($qq,$conn);
							$rww = mysql_fetch_assoc($rcou);
							$crt = $rww['Province']." ".$rww['Country'];
							$lab = $gk." (".$crt.")";
						}
						$nc = strlen($gen);
						$n1 = floor($nc/2)-1;
						$n2 = ceil($nc/2)-1;
						$g1 = substr($gen,0,$n1);
						$g2 = substr($gen,$n1+1,$n2);
						$qq = "SELECT * FROM Municipio WHERE (LOWER(Municipio) LIKE '%".$g1."%' OR LOWER(Municipio) LIKE '%".$g2."%'
						";
							if (count($gggen)>1) {
								$qqq = '';
								foreach ($gggen as $gg) {
									$gg = trim($gg);
									if (!empty($gg) && strlen($gg)>2) {
										$qqq .= " OR LOWER(Municipio) LIKE '%".$gg."%'";
									}
								}
							}
						$qq .= $qqq.") AND ProvinceID=".$coutid." ORDER BY Municipio";
						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
        <tr>
          <td>".$lab."</td>
          <td>
          <select id=\"municipioid_".$ggen.$idd."\" name=\"municipioid[".$gk."_".$idd."]\">
            <option value=''>".GetLangVar('nameselect')."</option>";
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
										echo "<option value='".$row['MunicipioID']."_".$row['ProvinceID']."'>".$row['Municipio']."</option>";
									}
								} else {
									echo "
            <option selected value=''>Não se parece com nada, cadastre novo!</option>";
								}
						echo "
          </select>
          </td>
          <td align='center'>
            <img src='icons/list-add.png' height=15 ";
								$myurl ="municipio-popup.php?municipioid_val=municipioid_".$ggen.$idd."&provinceid=".$coutid."&nome=".$gk; 
								echo " onclick = \"javascript:small_window('$myurl',500,350,'Novo Pais');\"></td></tr>";

				}
					echo "
        <tr><td align='center' colspan='3'><input style='cursor: pointer'  type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
    </form>
</tbody>
</table>
";
		} else {
			$municipiosdone = 1;
		}
	} elseif (empty($municipio)) { $municipiosdone = 1;}

if ($municipiosdone==1) {
echo "
  <form name='myform' action='import-locais-step4.php' method='post'>";
	foreach ($ppost as $kk => $vv) {
		if (!empty($vv)) {
			echo "
          <input type='hidden' name='".$kk."' value='".$vv."' />"; 
		}
	}
echo "
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";
//<input style='cursor: pointer'  type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit'  />


}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
