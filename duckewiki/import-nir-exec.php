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
"<link href='css/geral.css' rel='stylesheet' type='text/css' >");
$which_java = array(
);
$title ='Importando spectros NIR';
$body='';
FazHeader($title,$body,$which_css,$which_java,$menu);

$uuuuserid = $_SESSION['userid'];
$tbn ="uploads/nir/temp_".$uuuuserid;
$dir = scandir($tbn);
unset($dir[0]);
unset($dir[1]);
$dir = array_values($dir);
$filecount = count($dir);
//echopre($dir);

$qq = "CREATE TABLE IF NOT EXISTS NirSpectra (
SpectrumID INT(10) unsigned NOT NULL auto_increment,
EspecimenID INT(10),
PlantaID INT(10),
Folha INT(10),
Face CHAR(20),
Leitura INT(10),
FileName CHAR(150),
AddedBy INT(10),
AddedDate DATE,
PRIMARY KEY (SpectrumID)) CHARACTER SET utf8 ENGINE InnoDB";
@mysql_query($qq,$conn);


//echo "Você subiu ".$filecount." arquivos<br >";
$fieldaskkvals_batch = array();
$erro = 0;
foreach ($dir as $kk => $vv) {
		$vvv = explode(".",$vv);
		$nz = count($vvv)-1;
		unset($vvv[$nz]);
		$vvv = implode("",$vvv);
		//echo $vvv."<br >";
		$vls = explode("_",$vvv);
		$fvals = array();
		$fvals["EspecimenID"]  = 0;
		$fvals["PlantaID"]  = 0;
		if (count($vls)==5) {
			//echopre($vls);
			$wikid = $vls[0];
			$ident = $vls[1];
			$folha = $vls[2];
			$face = $vls[3];
			$leitura = $vls[4];
			$ww = explode("-",$wikid);
			$spec = strtolower($ww[0]);
			if ($spec=='sample spec' || $spec=='spec') {
				$fvals["EspecimenID"] = $ww[1];
			} elseif ($spec=='sample planta' || $spec=='planta') {
				$fvals["PlantaID"] = $ww[1];
			}
		} 
		else {
			$ident = $vls[0];
			$zz = explode("-",$ident);
			if (count($zz)>1) {
				$nzz = count($zz)-1;
				$num = $zz[$nzz];
				unset($zz[$nzz]);
				$colec = implode("-",$zz);
				$colec = strtolower($colec);
				$qq = "SELECT EspecimenID FROM  Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE LOWER(Abreviacao) LIKE '".$colec."%'  AND Number='".$num."'";
				$rq = mysql_query($qq,$conn);
				$nrq = mysql_numrows($rq);
				if ($nrq==1) {
					$rqw = mysql_fetch_assoc($rq);
					$fvals["EspecimenID"] = $rqw['EspecimenID'];
				}  else {
					$fvals["EspecimenID"] = 'ERRO: '.$nrq.' amostras com esse numero';
					$erro++;
				}
			} else {
				$qq = "SELECT PlantaID FROM  Plantas WHERE PlantaTag='".$ident."'";
				$rq = mysql_query($qq,$conn);
				$nrq = mysql_numrows($rq);
				if ($nrq==1) {
					$rqw = mysql_fetch_assoc($rq);
					$fvals["PlantaID"] = $rqw['PlantaID'];
				} else {
					$fvals["PlantaID"] = 'ERRO: '.$nrq.' plantas com esse numero';
					$erro++;
				}
			}
			$folha = $vls[1];
			$face = $vls[2];
			$leitura = $vls[3];
		}
		$fcc = strtolower($face);
		$pattern = '/abaxial/i';
		if (preg_match($pattern, $fcc)) {
			$face = 'abaxial';
		} 
		$pattern = '/adaxial/i';
		if (preg_match($pattern, $fcc)) {
			$face = 'adaxial';
		} 
		if ($face!='adaxial' && $face!='abaxial') {
			$face = "ERRO: face não especificada";
		}
		$ww = explode("-",$fcc);
		if (($ww[1]+0)>0) {
			$leitura = $ww[1]+0;
		}
		$fol = strtolower($folha);
		$pattern = '/folha/i';
		//echo $fol."   ".$pattern;
		//PREG_OFFSET_CAPTURE
		if (preg_match($pattern, $fol)) {
			//echo "a palavra folha existe na coluna folha";
			$fl = str_replace("folha-","",$fol);
			//$fl = str_replace("[-_]","",$fl);
			$fl = trim($fl);
			//echo $fol."   e ".$fl."<br />";
			$fl = $fl+0;
		} else {
			$fl = $fol+0;
		}
		if ($fl<=0) {
			$fl =  "ERRO: folha não especificada";
			$erro++;
		}
		$fvals["Folha"] = $fl;
		$fvals["Face"] = $face;
		$fvals["Leitura"] = $leitura;
		$fvals["FileName"] = $vv;
		//echopre($fvals);
		$fieldaskkvals_batch[] = $fvals;
}
//apaga os arquivos avisa e manda fazer de novo!
if ($erro>0) {
echo "
<br />
 <br />
<table class='myformtable' align='center' cellpadding=\"5\" >
<thead>
<tr ><td colspan='4'>O nome de arquivos não permite a importação!</td></tr>
<tr class='subhead'>
  <td>Arquivo</td>
  <td>Identificador</td>
  <td>Folha</td>
  <td>Face</td>
</tr>
</thead>
<tbody>";
foreach ($fieldaskkvals_batch as $kk => $vals) {
		$fln = $tbn."/".$vals['FileName'];
		@unlink($fln);
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
$ident = "";
if (($vals['EspecimenID']+0)>0 || !empty($vals['EspecimenID'])) {
	$ident = $vals['EspecimenID'];
} elseif (($vals['PlantaID']+0)>0 || !empty($vals['PlantaID'])) {
		$ident = $vals['PlantaID'];
}

echo "
<tr bgcolor = '".$bgcolor."'>
  <td>".$vals['FileName'] ."</td>
  <td>".$ident ."</td>
  <td>".$vals['Folha'] ."</td>
  <td>".$vals['Face'] ."</td>
</tr>";

}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='4' align='center'>
     <input type='button' style='cursor: pointer' value='Fechar' class='bsubmit' onclick=\"javascript:window.close();\" />
  </td>
</tr>
</tbody>
</table>
";
} 
else {
	$specid = array();
	$plid = array();
	foreach ($fieldaskkvals_batch as $kk => $vals) {
			//echopre($vals);
			//checa para outros registros e folhas, se já houver pega o valor máximo de folha e incrementa
			$newspectrum = InsertIntoTable($vals,'SpectrumID','NirSpectra',$conn);
			$oldf = "uploads/nir/temp_".$uuuuserid."/".$vals['FileName'];
			//echo $oldf."<br >";
			if (( $vals['EspecimenID']+0)>0) {
			$specid[] = $vals['EspecimenID'];
			}
			if (( $vals['PlantaID']+0)>0) {
			$plid[] = $vals['PlantaID'];
			}
			$nname = $newspectrum."_".$vals['FileName'];
			//echo $nname."<br >";
			$arrayofvalues = array( "FileName" => $nname);
			$updatespecid = UpdateTable($newspectrum,$arrayofvalues,'SpectrumID','NirSpectra',$conn);
			@copy($oldf,"uploads/nir/".$nname);
			@unlink($oldf);
			$nf++;
	}
	$nspecs = array_unique($specid);
	$nplantas = array_unique($plid);
	$nsp = count($nspecs);
	$npl = count($nplantas);
	echo "
<br />
 <br />
<table class='myformtable' align='center' cellpadding=\"5\" >
<thead>
<tr ><td >Importação concluida</td></tr>
</thead>
<tbody>";
echo "<tr><td>$nf arquivos foram importados com sucesso!</td></tr>";
if ($npl>0) {
echo "<tr><td>Esses arquivos correspondem a $npl plantas marcadas!</td></tr>";
}
if ($nsp>0) {
echo "<tr><td>Esses arquivos correspondem a $nsp especimenes!</td></tr>";
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='4' align='center'>
     <input type='button' style='cursor: pointer' value='Fechar' class='bsubmit' onclick=\"javascript:window.close();\" />
  </td>
</tr>
</tbody>
</table>
";
	
}

//echo "
 //  <form action='import-nir-exec.php' method='post' name='autorform'>
  //   <input type='hidden' name='ispopup' value='".$ispopup."' />
   //  <input type='hidden' name='final' value='' />
    // <input type='submit' style='cursor: pointer' value='Refresh' class='bsubmit' onclick=\"javascript:document.autorform.final.value=1\" />
   // </form>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>