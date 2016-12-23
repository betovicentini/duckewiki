<?php
//Este script checa se alguns campos que se referem a localidades e ve se estas ja estao cadastradas
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include_once("functions/class.Numerical.php") ;

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
$title = 'Importar Habitat 01b';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$erro=0;
if (empty($gazetteeridfield) && empty($gazetteerfield)) {
$erro++;
	echo "
<br />
<table cellpadding=\"5\" align='center' class='erro'>
  <tr>
    <td align='center' colspan='100%' >Precisa selecionar uma das duas opções</td>
  </tr>
</table>
";
} 
if (!empty($gazetteerfield) && ($parentgazetteerid+0)==0) {
$erro++;
echo "
<br />
<table cellpadding=\"5\" align='center' class='erro'>
   <tr>
      <td align='center' colspan='100%' >Indique a localidade geral na OPÇÃO 2</td>
    </tr>
  </table>
";
} 
if ($erro>0) {
	echo "
<br />
<form action='import-habitat-step1.php' method='post'>";
foreach ($_POST as $kk => $vv) {
echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
}  
echo "
  <table cellpadding=\"5\" align='center'>
    <tr><td align='left'><input type='submit' value='Voltar' class='bblue' /></td></tr>
  </table>
</form>
";
}
if (!empty($gazetteerfield) && $erro==0 && !isset($localidadesdone)) {
		$colname = $tbprefix."GazetteerID";
		$colcol = $gazetteerfield;
			if (!isset($gazdone)) {
				$qq = "ALTER TABLE `".$tbname."` ADD COLUMN `".$colname."` INT(10) 	DEFAULT 0";
				@mysql_query($qq,$conn);
			} 
			else {
				if (count($gazetteerid)>0) {
					foreach ($gazetteerid as $kk => $vv) {
						$vv = $vv+0;
						if ($vv>0) {
							$qq = "UPDATE ".$tbname." as tb SET tb.".$colname."= ".$vv." WHERE tb.".$colcol."='".$kk."'  AND tb.".$colcol."<>'' AND tb.".$colcol." IS NOT NULL AND (tb.".$colname."=0  OR tb.".$colname." IS NULL)";
							mysql_query($qq,$conn);
						}
					}
				} 
			}
			$parid = $parentgazetteerid;
			$muid=0;
			$prid=0;
			$crtid=0;
			$qq = "UPDATE ".$tbname." SET ".$colname."=checkgazetteer(".$colcol.",".$parid.",".$muid.",".$prid.",".$crtid.") WHERE ".$colcol."<>'' AND ".$colcol." IS NOT NULL AND (".$colname."=0 OR ".$colname." IS NULL)";
			mysql_query($qq,$conn);
			$qq = "SELECT DISTINCT `".$colcol."` as missgen";
			$qq .= " FROM `".$tbname."`  WHERE `".$colcol."`<>'' AND `".$colcol."` IS NOT NULL AND (`".$colname."`=0 OR `".$colname."` IS NULL)";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			unset($_POST['gazetteerid']);
			if ($nr>0) {
			echo "
<br />
<table align='left' class='myformtable' cellpadding='7'>
<thead>
  <tr><td colspan='100%'>Localidades não encontradas no Wiki</td></tr>
  <tr class='subhead'>
    <td>Nome</td>
    <td>Pode ser uma dessas</td>
    <td>Cadastre nova</td>
  </tr>
</thead>
<tbody>
<form action=\"import-habitat-step1b.php\" method=\"post\">";
					unset($_POST['gazdone']);
					foreach ($_POST as $kk => $vv) {
						if (!empty($vv)) {
							echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					echo "
  <input type='hidden' name='gazdone' value='1' />";
					$i=1;
					while ($rw = mysql_fetch_assoc($rr)) {
						$gen = mb_strtolower(trim($rw['missgen']));
						$gk = $rw['missgen'];
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$lab = $gk;
						$nc = strlen($gen);
						$n1 = floor($nc/2)-1;
						$n2 = ceil($nc/2)-1;
						$g1 = substr($gen,0,$n1);
						$g2 = substr($gen,$n1+1,$n2);
						//$qq = "SELECT GazetteerID,GazetteerTIPOtxt,Gazetteer,Municipio,Gazetteer.MunicipioID,Province FROM Gazetteer LEFT JOIN Municipio USING(MunicipioID) LEFT JOIN Province USING(ProvinceID) WHERE (LOWER(CONCAT(GazetteerTIPOtxt,' ',Gazetteer)) LIKE '%".$g1."%' OR LOWER(CONCAT(GazetteerTIPOtxt,' ',Gazetteer)) LIKE '%".$g2."%'";
						$qq = "SELECT GazetteerID,Gazetteer,Municipio,Gazetteer.MunicipioID,Province FROM Gazetteer LEFT JOIN Municipio USING(MunicipioID) LEFT JOIN Province USING(ProvinceID) WHERE (LOWER(Gazetteer) LIKE '%".$g1."%' OR LOWER(Gazetteer) LIKE '%".$g2."%'";
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
						if ($parentgazetteerid>0) {
							$qq .= ") AND Gazetteer.ParentID='".$parentgazetteerid."'"; 
						} else {
							$qq .= ") ";
						}
						//$qq .= " ORDER BY GazetteerTIPOtxt,Gazetteer";
						$qq .= " ORDER BY Gazetteer";
						//echo $qq."<br />";
						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
  <tr>
    <td>".$lab."</td>
    <td>
      <select id=\"gazetteer_".$i."\" name=\"gazetteerid[".$gk."]\">
        <option value=''>".GetLangVar('nameselect')."</option>";
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
										echo "
        <option value='".$row['GazetteerID']."'>".$row['Gazetteer']." [".$row['Municipio']." - ".$row['Province']."]</option>";
        //<option value='".$row['GazetteerID']."'>".$row['GazetteerTIPOtxt']." ".$row['Gazetteer']." [".$row['Municipio']." - ".$row['Province']."]</option>";
									}
								} else {
									echo "
        <option selected value=''>Não se parece com nada, cadastre novo!</option>";
								}
						echo "
      </select>
    </td>
    <td align='center'><img src='icons/list-add.png' height=15 ";
							$myurl = "localidade-novapopup.php?gazetteer_val=gazetteer_".$i."&gazetteer=".$gen;
							echo " onclick = \"javascript:small_window('$myurl',500,350,'Novo Pais');\"></td></tr>";
						$i++;
				}
					echo "
  <tr><td align='center' colspan='100%'><input type='button' value='Continuar' class='bsubmit' onclick='this.form.submit();' /></td></tr>
</form>
</tbody>
</table>
			";
		} 
		else {
			$localidadesdone = 1;
		}
} 
if ($localidadesdone==1) {
echo "
<form name='myform' action='import-habitat-step2.php' method='post'>";
//coloca as variaveis anteriores
foreach ($_POST as $kk => $vv) {
echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
}  
echo "
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
</form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
