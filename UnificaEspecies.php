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
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
//"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Unifica espécies';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($fazer)) {
	$qq = "SELECT DISTINCT spp.GeneroID,spp.EspecieID,Especie,EspecieAutor FROM Identidade as idd JOIN Tax_Especies as spp ON idd.EspecieID=spp.EspecieID  JOIN Tax_Generos as gg ON gg.GeneroID=spp.GeneroID JOIN Tax_Familias as ff ON ff.FamiliaID=gg.FamiliaID WHERE spp.Morfotipo='1' ORDER BY ff.Familia,gg.Genero";
	$res = mysql_query($qq,$conn);
	$resul = array();
	$resulfim = array();

	while($row = mysql_fetch_assoc($res)) {
			$spid = $row['EspecieID'];
			$genid = $row['GeneroID'];
			$nm = $row['Especie'];
			$naut = $row['EspecieAutor'];
			$nm2 = str_replace ('msp.', 'sp.', $nm);
			$nm2 = str_replace ('sp.p.', 'sp.', $nm2);
			$nm2 = str_replace ('  ', ' ', $nm2);			
			$nm2 = str_replace ('  ', ' ', $nm2);			
			$nm2 = str_replace ('  ', ' ', $nm2);			
			$nm2 = str_replace ('  ', ' ', $nm2);			
			$nmU = strtoupper($nm2);
			$nm1 = '';
			if (preg_match('/FITO/i', $nmU) || preg_match('/FITO/i', $naut)) {
				if (preg_match('/FITO/i', $nmU)) {
					$nm2 = str_replace("FITO","",$nm2);
					$nm2 = trim($nm2); 
				}
				$nadd = "FITO";
			} else {
				if (preg_match('/PFRD/i', $nmU) || preg_match('/PFRD/i', $naut) || preg_match('/FLORA/i', $nmU)) {
					if (preg_match('/PFRD/i', $nmU)) {
						$nm2 = str_replace("(PFRD)","",$nm2);
						$nm2 = str_replace("PFRD","",$nm2);
						$nm2 = trim($nm2); 
					}
					$nadd = "PFRD";
				} else {
					if (preg_match('/PP/i', $nmU)) {
						if (preg_match('/PP/i', $nmU)) {
							$nm2 = str_replace("(PP)","",$nm2);
							$nm2 = str_replace("PP","",$nm2);
							$nm2 = trim($nm2); 
						}
						$nadd = "PP";
					} else {
						$nnn = explode(",",$naut);
						$nadd = strtoupper($nnn[0]);
					}
				}
			}
			$nm2 = str_replace ('  ', ' ', $nm2);
			$nm2 = str_replace ('  ', ' ', $nm2);
			$nm2 = str_replace (' ', '.', $nm2);
			$nm2 = str_replace ('..', '.', $nm2);
			$nm2 = str_replace ('..', '.', $nm2);
			$nm2 = str_replace ('..', '.', $nm2);
			$nm2 = str_replace ('..', '.', $nm2);
			$resul[$spid] = strtolower($nm2)."_".$genid;
			$nm2 = strtolower($nm2).".".$nadd;
			$nm2 = str_replace ('..', '.', $nm2);
			$nm2 = str_replace ('..', '.', $nm2);
			$resulfim[$spid] = $nm2;
			//echo "tem ".$nadd."     ".$nm."  <i>".$naut."</i>   <b>".$nm2."</b><br />";
	}
	$resun = array_unique($resul);
	$ns = count($resul);
	$nss = count($resun);
//echo $ns."   e ".$nss."\n\n\n";
	$sptomerge = array();
	$sptocorrect = array();
	foreach ($resun as $kk => $vv) {
	//$rs = array_search($vv,$resul);
		$rs = array_keys ( $resul, $search_value = $vv);
		if (count($rs)>1) {
			$rtxt = implode("-",$rs);
			$spnovo = $resulfim[$rs[0]];
			$sptomerge[] = array('nomenovo' => $spnovo, 'nomeclean' => $vv, 'spidstomerge' => $rs);
			//echo $vv." ".$rtxt."<br />";
		} else {
			$spnovo = $resulfim[$rs[0]];
			$sptocorrect[] = array('nomenovo' => $spnovo, 'nomeclean' => $vv, 'spidstomerge' => $rs[0]);
		}
	}
	$_SESSION['joinspecies'] = serialize(array($sptomerge,$sptocorrect,$resulfim,$resul));
	$fazer = 0;
	//echopre($sptomerge);

	if (count($sptocorrect)>0) {
		//echo " corrigir ".count($sptocorrect);
		foreach ($sptocorrect as $vvv) {
			$idd = $vvv['spidstomerge'];
			//atualiza o nome da especie que fica
			//CreateorUpdateTableofChanges($idd,'EspecieID','Tax_Especies',$conn);
			//$spnovo = $vvv['nomenovo'];
			//print_r($spnovo);
			//echo "<br />";
			//echo $idd."   ".$spnovo."<br >".
			$arrayofvalues = array('Especie' => $spnovo);
			//echopre($arrayofvalues);
			//$updatespec = UpdateTable($idd,$arrayofvalues,'EspecieID','Tax_Especies',$conn);
			if ($updatespec) {
			//echo $spnovo." foi atualizado o nome";
			}
		}
	}
}
//corrige nomes


if ($fazer>=0) {
	$ddd = unserialize($_SESSION['joinspecies']);
	$sptomm = $ddd[0];
	$resulfim = $ddd[2];
	if ($final==1 && $spidtokeep>0) {
		//entao precisa fazer um cadastro
		//echo " UNIR ";
		$ff = $fazer-1;
		$fff = $sptomm[$ff];
		$orids =  $fff['spidstomerge'];
		//echopre($ppost);
		//echopre($orids);
		$idstodel = array_diff($orids,array($spidtokeep));
		//echopre($idstodel);
		$spnovo = $resulfim[$spidtokeep];
		foreach($idstodel as $iddel) {
			$qid = "SELECT count(*) as nn FROM Identidade WHERE EspecieID='".$iddel."'";
			$ree1 = mysql_query($qid,$conn);
			$reew = mysql_fetch_assoc($ree1);
			$qid = "SELECT count(*) as nn FROM Identidade WHERE EspecieID='".$iddel."' AND InfraEspecieID=0";
			$ree2 = mysql_query($qid,$conn);
			$reew2 = mysql_fetch_assoc($ree2);
			if ($reew2['nn']==$reew['nn']) {
				//echo $reew['nn']." registros de identidade para atualizar<br >";
				$qid = "SELECT DetID,Especie FROM Identidade JOIN Tax_Especies as spp USING(EspecieID) WHERE spp.EspecieID='".$iddel."'";
				$ree3 = mysql_query($qid,$conn);
				$apagados = 0;
				while ($reew3 = mysql_fetch_assoc($ree3)) {
					CreateorUpdateTableofChanges($reew3['DetID'],'DetID','Identidade',$conn);
					$arrayofvalues = array('EspecieID' => $spidtokeep);
					$updatedet = UpdateTable($reew3['DetID'],$arrayofvalues,'DetID','Identidade',$conn);
					if (!$updatedet) {
						//echo $reew3['DetID']."    nao foi possivel substituir  ".$reew3['Especie']."  por ".$spnovo."<br />";
					} else {
						//echo $reew3['DetID']."   foi  substituido ".$reew3['Especie']."  por ".$spnovo."<br />";
						$apagados++;
					}
				}
				if ($apagados==$reew['nn']) {
					//echo $apagados." registros foram atualizados<br />";
					$qdel = "DELETE FROM Tax_InfraEspecies WHERE EspecieID='".$iddel."'";
					$ree3 = mysql_query($qdel,$conn);
					if ($ree3) {
						//echo "havia subespecies que foram apagadas<br />";
					}
					$qdel = "DELETE FROM Tax_Especies WHERE EspecieID='".$iddel."'";
					$ree4 = mysql_query($qdel,$conn);
					if ($ree3) {
						//echo "a especies foi apagada<br />";
					}
				} else {
					//echo $reew['nn']." registros encontrados mas apenas $apagados foram apagados.<br />A especie continua na base<br />";
				}
			} else {
				echo ($reew2['nn']-$reew['nn'])." registros tem subespecies, a mudança não foi feita<br >";
			}
		}
		//atualiza o nome da especie que fica
		CreateorUpdateTableofChanges($spidtokeep,'EspecieID','Tax_Especies',$conn);
		$arrayofvalues = array('Especie' => $spnovo);
		//echopre($arrayofvalues);
		$updatespec = UpdateTable($spidtokeep,$arrayofvalues,'EspecieID','Tax_Especies',$conn);
		if ($updatespec) {
			//echo $spnovo." foi atualizado o nome";
		}
	}
	if ($final==1 && ($spidtokeep+0)==0) {
		//entao precisa fazer um cadastro
		echo " faltou ";
		$fazer = $fazer-1;
	} 
	$ddtest = $sptomm[$fazer];
	$nomenovo = $ddtest['nomenovo'];
	$nomeclean = $ddtest['nomeclean'];
	$spids = $ddtest['spidstomerge'];

echo "
<br />
<form  action='UnificaEspecies.php' method='post' name='coletaform'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
    <input type='hidden' name='fazer' value='".($fazer+1)."' />  
    <input type='hidden' name='final' value='' />  
<table align='center' class='myformtable' cellpadding=\"7\">
<thead>
<tr>
<td colspan='9' class='tabhead' >Unifica registros de espécies que são parecidos</td>
</tr>";
	if (count($spids)>0) {
echo "
<tr class='subhead'>
  <td align='center'> </td>
  <td align='center'>Familia</td>
  <td align='center'>Genero</td>
  <td align='center'>Especie</td>
  <td align='center'>EspecieAutor</td>
  <td align='center'>NPlantasMarcadas</td>
  <td align='center'>NEspecimenes</td>
  <td align='center'>NovoNome</td>
  <td align='center'>MANTEM</td>
</tr>
</thead>
<tbody>";

$i=1;
foreach ($spids as $idd) {
	$spnovo = $resulfim[$idd];
	$qq = "SELECT Familia,Genero,Especie,EspecieAutor FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE EspecieID='".$idd."'";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);

	$qq = "SELECT COUNT(*) as pls FROM Plantas JOIN Identidade USING(DetID) WHERE EspecieID='".$idd."'";
	$rss = mysql_query($qq,$conn);
	$rw = mysql_fetch_assoc($rss);
	$qq = "SELECT COUNT(*) as pls FROM ChangePlantas JOIN Identidade USING(DetID) WHERE EspecieID='".$idd."'";
	$rss = mysql_query($qq,$conn);
	$rw2 = mysql_fetch_assoc($rss);
	$plantas = $rw['pls']+$rw2['pls'];

	$qq = "SELECT COUNT(*) as pls FROM Especimenes JOIN Identidade USING(DetID) WHERE EspecieID='".$idd."'";
	$rsss = mysql_query($qq,$conn);
	$rww = mysql_fetch_assoc($rsss);
	$qq = "SELECT COUNT(*) as pls FROM ChangeEspecimenes JOIN Identidade USING(DetID) WHERE EspecieID='".$idd."'";
	$rss = mysql_query($qq,$conn);
	$rww2 = mysql_fetch_assoc($rss);
	$specs = $rww['pls']+$rww2['pls'];
	//get the first species to test
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'>Registro #".$i."</td>
  <td align='center'>".$row['Familia']."</td>
  <td align='center'>".$row['Genero']."</td>
  <td align='center'>".$row['Especie']."</td>
  <td align='center'>".$row['EspecieAutor']."</td>
  <td align='center'>".$plantas."</td>
  <td align='center'>".$specs."</td>
  <td align='center'><b>".$spnovo."</b></td>
  <td align='center'><input type='radio' name='spidtokeep' value='".$idd."'></td>
</tr>";
	$i++;
}
//get the first species to test
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='5' align='center'><input type='submit' value='Unificar' class='bsubmit' onclick=\"javascript:document.coletaform.final.value='1'\" /></td>
  <td colspan='4' align='center'><input type='submit' value='Pular' class='bblue' onclick=\"javascript:document.coletaform.final.value='2'\" /></td>
</tr>";
} else {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='8' align='center'>Nao há + registros para unificar!</td>
  <td colspan='8' align='center'><input type='button' value='Unificar' class='bsubmit' onclick=\"javascript:window.close();\" /></td>
</tr>";
}
echo "
</tbody>
</table>
</form>
";

}


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,
//"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
