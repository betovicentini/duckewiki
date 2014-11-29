<?php
//Start session
session_start();
//Check whether the session variable
if(!isset($_SESSION['userid']) || 
	(trim($_SESSION['userid'])=='')) {
		header("location: access-denied.php");
	exit();
} 

include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
require_once( 'javascript/PogProgressBar.php' );

//progress bar
$objBar1 = new PogProgressBar( 'pb1' );
// set themes
$objBar1->setTheme( 'blue' );


@extract($_POST);
@extract($_GET);

$relativepath = 'temp/';
$filename = "Filtro_ID-".$filtro.".json";

if (file_exists($relativepath.$filename) && empty($updatefiltros)) {
	$fn = $relativepath.$filename;
} else { $fn='';}
HTMLheadersMap($fn);

	$lang = $_SESSION['lang'];
	$dbname = $_SESSION['dbname'];
	$conn = ConectaDB($dbname);


	if (!empty($filtro)) { 
		$filename = "Filtro_ID-".$filtro.".json";
		$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
		$res = @mysql_query($qq,$conn);
		$rr = @mysql_fetch_assoc($res);
		$especimenesids= $rr['EspecimenesIDS'];
	
		$specarr = explode(";",$especimenesids);
		$nspec = count($specarr);

		$qq = "DROP TABLE Temp_Map";
		@mysql_query($qq,$conn);
		$qq = "CREATE TABLE Temp_Map LIKE Especimenes";
		mysql_query($qq,$conn);
		foreach($specarr as $vv) {
			$qq = "INSERT INTO Temp_Map SELECT * FROM Especimenes WHERE EspecimenID='".$vv."'";
			mysql_query($qq,$conn);
		}

	
		$qq = "SELECT * FROM Temp_Map JOIN Pessoas ON ColetorID=PessoaID ORDER BY Abreviacao,Number";
		$rrr = mysql_query($qq,$conn);
		while ($vvv = mysql_fetch_assoc($rrr)) {	
			$gazetteerid = $vvv['GazetteerID'];
			$gpspointid = $vvv['GPSPointID'];
			$lat = $vvv['Latitude'];
			$lat = $vvv['Longitude'];
			$spid = $vvv['EspecimenID'];
			$mylatlng = getLatLong($gpspointid,$gazetteerid,$conn);
			if ($mylatlng['Latitude']+$mylatlng['Longitude']<>0 && $lat+$long==0) {
				$qq = "UPDATE Temp_Map SET Latitude='".$mylatlng['Latitude']."', Longitude='".$mylatlng['Longitude']."' WHERE EspecimenID='".$spid."'";
				mysql_query($qq,$conn);
			} elseif ($lat+$long==0) {
				$qq = "DELETE FROM Temp_Map WHERE EspecimenID='".$spid."'";
				mysql_query($qq,$conn);
			
			
			}
		}
		
		$qq = "SELECT * FROM Temp_Map JOIN Pessoas ON ColetorID=PessoaID ORDER BY Abreviacao,Number";
		$res = mysql_query($qq,$conn);
		$npoints = mysql_numrows($res);
		
		$qq = "UPDATE Filtros SET NsamplesCannMap='".$npoints."' WHERE FiltroID='".$filtro."'";
		mysql_query($qq,$conn);
		
		$l=0;
		$texttowrite = "var data = { \"count\": ".$npoints.",\n \"mypoints\": [";
		WriteToTXTFile($filename,$texttowrite,$relativepath,$append=FALSE);
		$virg=0;
		$blngarr = array();
		$blatgarr = array();
		

		//echo "<table align=\"center\" cellpadding=\"0\" cellspacing=\"20\" border=\"0\">
		//	<tr><td>";
		//		$objBar1->draw();
		//	echo "</td></tr></table>";
		$intCount = 0;		
		while ($rw = mysql_fetch_assoc($res)) {	
			$results = array();
			
			$objBar1->setProgress( $intCount * 100 / $npoints );
			$intCount++;
			usleep(100);
			$numcol = $rw['Number'];
			$mes = getmonthstring($rw['Mes'],$abbre=TRUE);
			$datacol = $rw['Day']."-".$mes."-".$rw['Ano'];
			$dd = array('Data' => $datacol);
			$results = array_merge((array)$results,(array)$dd);
		
			$detid = $rw['DetID'];
			$dettaxa = getdet($detid,$conn);
			$detnome = $dettaxa[0];
			
			$detbyanddate = trim($dettaxa[1]);
			$detmodifier = trim($dettaxa[3]);
			$dd = array('Determinacao' => $detxt.$detbyanddate);
			$results = array_merge((array)$results,(array)$dd);
		
			$familia = strtoupper(trim($dettaxa[2]));
			$dd = array('Taxon' => $detnome.' ('.$familia.')');
			$results = array_merge((array)$results,(array)$dd);
		
			$detnomenoaut = getdetnoautor($detid,$conn);
			$dd = array('TaxaNoAutor' => $detnomenoaut.' ('.$familia.')');
			$results = array_merge((array)$results,(array)$dd);
			
			$herbnum = $rw['INPA_ID'];
			$projetoid = $rw['ProjetoID'];
			
			if ($projetoid>0) {
				$qq = "SELECT * FROM Projetos WHERE ProjetoID='$projetoid'";
				$rs = mysql_query($qq,$conn);
				$rwo = mysql_fetch_assoc($rs);
				$pp = strtoupper(GetLangVar('nameprojeto')." ".$rwo['ProjetoNome']);
				
				$agencia = explode(";",$rwo['Financiamento']);
				$process = explode(";",$rwo['Processos']);
				
				$logofile = $rwo['LogoFile'];
				$prjurl = $rwo['ProjetoURL'];
				
				if (!empty($prjurl)) {
					$pp = $pp." <a href='".$prjurl."'>".$prjurl."</a>.";
				}
				
				$agpc = array();
				foreach ($agencia as $kkk => $vvv) {
					if (!empty($vvv) && $vvv!='Array') {
						$pc = $process[$kkk];
						$ag = array($vvv." (No. ".$pc.")");
						$agpc = array_merge((array)$agpc,(array)$ag);
					}
				}
				if (count($agpc)>0) {
					$finan = implode("; ",$agpc);
					$pp = $pp." Financiado por: ".$finan.".";
				}
				$projeto= "<i>".$pp."</i>";
			}
			$dd = array('Projeto' => $projeto);
			$results = array_merge((array)$results,(array)$dd);
		
			$gazetteerid = $rw['GazetteerID'];
			$gpspointid = $rw['GPSPointID'];
			if ($gpspointid>0) {
				$locality = getGPSlocality($gpspointid,$name=FALSE,$conn);
			} elseif ($gazetteerid>0) {
				$locality = getlocality($gazetteerid,$coord=TRUE,$conn);
			}
			$dd = array('Localidade' => $locality);
			$results = array_merge((array)$results,(array)$dd);
		
			//$latlongarr = getLatLong($gpspointid,$gazetteerid,$conn);
			$latlongarr = array('Latitude' => $rw['Latitude'], 'Longitude' => $rw['Longitude']);
			$results = array_merge((array)$results,(array)$latlongarr);
			$blatarr = array_merge((array)$blatarr,(array)$latlongarr['Latitude']);
			$blngarr = array_merge((array)$blngarr,(array)$latlongarr['Longitude']);
		
		
			$ps =  getpessoa($rw['ColetorID'],$abb=TRUE,$conn);
			$pess = mysql_fetch_assoc($ps);
			$coletor = $pess['Abreviacao']." ".$numcol;
			$specid = $rw['EspecimenID'];
			$dd = array('botam_id' => $specid,'Coletor' => $coletor);
			$results = array_merge((array)$dd,(array)$results);
			
			if ($virg>0 && $virg<$npoints) { $tt = " }\n,\n{"; }
			if ($virg==0 || $vig==$npoints) { $tt = "\n{";}
				$j=0;
				$nres = count($results)-1;
				foreach ($results as $kk => $val) {
					if (is_numeric($val)) {
						$tt = $tt." \"".$kk."\": ".$val;
					} else {
						$vv = str_replace("\"","",$val);
						$tt = $tt." \"".$kk."\": \"".$vv."\"";
					}
					if ($j<$nres) {
						$tt = $tt.", ";
					}
				$j++;
				}
				$virg++;
				WriteToTXTFile($filename,$tt,$relativepath,$append=TRUE);
			}
		
		//map boundaries
		$maxlat = max($blatarr)+1;
		$maxlong = max($blngarr)+1;
		
		$minlat = min($blatarr)-2;
		$minlong = min($blngarr)-2;
		
		
		$centerlat = ($maxlat+ $minlat)/2;
		$centerlong = ($maxlong+ $minlong)/2;
		
		
		$boundaries = array($centerlat, $maxlat, $minlat, $centerlong, $maxlong, $minlong);
		//echopre($boundaries);
		$boundaries = implode("|",$boundaries);
		$texttowrite = "}\n],\n\"boundaries\": \"".$boundaries."\"}";
		WriteToTXTFile($filename,$texttowrite,$relativepath,$append=TRUE);	
		
		//$href = curPageURL()."/botam/$relativepath".$filename;
		//echo "<p >O arquivo gerado foi salvo temporariamente  <a  href='".$href."' target='_blank'>aqui</a> </p>";
		echo "
		<form id='myform' method='post' action='mapping.php'>
			<input type=\"hidden\" name=\"filtro\" value=\"".$filtro."\">
			<script language=\"JavaScript\">
			setTimeout(
				function() {
					document.getElementById('myform').submit();				
				}
				,0);
			</script>
		</form>";	
	}

HTMLtrailers();



?>

