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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Importar Expedito 09';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$nnv = $_SESSION['fieldsign'];
$newv = unserialize($nnv);
$localspecif = trim($newv["LOCALIDADE_ESPECIFICA"]);
$pontoname = trim($newv["PTID"]);
$cll=  $tbprefix."GazetteerID";
$cll2=  $tbprefix."GPSPointID";
if (!empty($localspecif)) {
	if (count($gazetteerid)>0) {
			foreach ($gazetteerid as $kk => $vv) {
				$vv = $vv+0;
				if ($vv>0) {
					$qq = "UPDATE `".$tbname."` as tb set tb.`".$cll."`=".$vv." where tb.`".$localspecif."`=".$kk;
					mysql_query($qq,$conn);
				}
			}
	} 
	else {
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." INT(1) DEFAULT 0,  ADD COLUMN ".$cll2." INT(10) DEFAULT 0";
		@mysql_query($qq,$conn);
		$qq = "UPDATE `".$tbname."` SET `".$cll."`=checkgazetteer(".$localspecif.",0,0,0,0) WHERE `".$localspecif."`<>'' AND `".$localspecif."` IS NOT NULL AND `".$cll."`=0";
		mysql_query($qq,$conn);
	}
	$qq = "SELECT DISTINCT `".$localspecif."` as missgen FROM `".$tbname."`  WHERE `".$localspecif."`<>'' AND `".$localspecif."` IS NOT NULL AND `".$cll."`=0 LIMIT 0,20";
	$rr = mysql_query($qq,$conn);
	$nres = @mysql_numrows($rr);
	//tem gaz para cadastrar
	if ($nres>0) {
		unset($ppost['gazetteerid']);
echo "
<br />
    <table align='center' class='myformtable' cellpadding='5'>
  <thead>
    <tr><td colspan='100%'>Localidades de pontos não encontradas no Wiki</td>
    </tr>
    <tr class='subhead'>
    <td>Localidade</td>
    <td>Pode ser uma dessas</td>
    <td>Cadastre nova</td>
    </tr>
  </thead>
  <tbody>
    <form action='import-expedito-step09.php' method='post'>";
		foreach ($ppost as $kk => $vv) {
						if (!empty($vv)) {
							echo "
        <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					$i=1;
					while ($rw = mysql_fetch_assoc($rr)) {
						$gen = strtolower(trim($rw['missgen']));
						$gk = $rw['missgen'];
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$nc = strlen($gen);
						$n1 = floor($nc/2)-1;
						$n2 = ceil($nc/2)-1;
						$g1 = substr($gen,0,2);
						$g2 = substr($gen,$n2-2,$n2);
						//$qq = "SELECT GazetteerID,GazetteerTIPOtxt,Gazetteer,Municipio,Gazetteer.MunicipioID,Gazetteer.ParentID,Province,Municipio.ProvinceID,Province.CountryID FROM Gazetteer LEFT JOIN Municipio USING(MunicipioID) LEFT JOIN Province USING(ProvinceID) WHERE (LOWER(CONCAT(GazetteerTIPOtxt,' ',Gazetteer)) LIKE '".$g1."%' OR LOWER(CONCAT(GazetteerTIPOtxt,' ',Gazetteer)) LIKE '%".$g2."'";
						$qq = "SELECT GazetteerID,Gazetteer,Municipio,Gazetteer.MunicipioID,Gazetteer.ParentID,Province,Municipio.ProvinceID,Province.CountryID FROM Gazetteer LEFT JOIN Municipio USING(MunicipioID) LEFT JOIN Province USING(ProvinceID) WHERE (LOWER(Gazetteer) LIKE '".$g1."%' OR LOWER(Gazetteer) LIKE '%".$g2."'";
							if (count($gggen)>1) {
								$qqq ="";
								foreach ($gggen as $gg) {
									$gg = trim($gg);
									if (!empty($gg) && strlen($gg)>2) {
										//$qqq .= " OR LOWER(CONCAT(GazetteerTIPOtxt,' ',Gazetteer)) LIKE '%".$gg."%'";
										$qqq .= " OR LOWER(Gazetteer) LIKE '%".$gg."%'";
									}
								}
							}
						$qq .= $qqq; 
						if (!empty($localid)) {
							$qq .= ") AND Gazetteer.ParentID=".$localid;
						} else {
							$qq .=")";
						}
						//$qq .= " ORDER BY GazetteerTIPOtxt,Gazetteer";
						$qq .= " ORDER BY Gazetteer";
						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
        <tr>
          <td>".$gen."</td>
          <td><select id=\"gazetteer_".$i."\" name=\"gazetteerid[".$gk."]\">
            <option value=''>".GetLangVar('nameselect')."</option>";
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
										echo "
            <option value='".$row['GazetteerID']."'>".$row['Gazetteer']." [".$row['Municipio']." - ".$row['Province']."]</option>";
//            <option value='".$row['GazetteerID']."'>".$row['GazetteerTIPOtxt']." ".$row['Gazetteer']." [".$row['Municipio']." - ".$row['Province']."]</option>";

									}
								} else {
									echo "
            <option selected value=''>Não se parece com nada, cadastre novo!</option>";
								}
						echo "
          </select></td>
          <td align='center'><img src='icons/list-add.png' height=15 ";
							$myurl = "localidade-novapopup.php?gazetteer_val=gazetteer_".$i."&gazetteer=".$gk."&municipioid=$munid&paisid=$coid&provinciaid=$provid&parentgazid=$parentgazid";
							echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Localidade');\"></td></tr>";
						$i++;
				}
					echo "
            <tr><td colspan='100%'><table align='center'><tr>
            <td align='center'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td>";
            if (!empty($localid)) {
                $qu = array($localspecif,$tbname,$cll);
                $qu = serialize($qu);
            $myurl = "localidade-novapopup_batch.php?plantagazfield=$localspecif&tbname=$tbname&colname=$cll&municipioid=$munid&paisid=$coid&provinciaid=$provid&parentgazid=$parentgazid&buttonidx=nowlocais";
            echo "
            <td align='center'><input id='nowlocais' type='button'  value='Cadastrar todas como novas' class='bblue' onclick = \"javascript:small_window('$myurl',500,350,'Novas Localidades');\"></td>";
            } else {
                        echo "
            <td align='center' colspan='50%'>&nbsp;</td>";
            }
            
            echo "
            </tr></table></td>
            </tr>
        </form>
    	</tbody>
		</table>";

		} 
	else {
		$plgaz=TRUE;
	}
} 
else {
	$plgaz=TRUE;
}

$_SESSION['fieldsign'] = serialize($newv);

if ($plgaz) {
echo "
<form name='myform' action='import-expedito-step10.php' method='post'>
";
//coloca as variaveis anteriores
	foreach ($ppost as $kk => $vv) {
	echo "
  	<input type='hidden' name='".$kk."' value='".$vv."' />"; 
	}
//echo "<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
echo "
  <table cellpadding=\"1\" width='50%' align='center'>
    <tr><td class='tdsmallbold' align='center'><input type='submit' value='continuar' class='bsubmit' /></td></tr>
  </table> 
 </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>