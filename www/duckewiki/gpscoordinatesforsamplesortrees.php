<?php
//Start session
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);

HTMLheaders('');

if (empty($municipioid) || !empty($gazetteerid)) {
	$rr = getgazetteer($gazetteerid,$municipioID,$conn);
	$row = mysql_fetch_assoc($rr);
	$municipioid = $row['MunicipioID'];
	$rr = getmunicipio($municipioid,$provinciaid,$conn);
	$row = mysql_fetch_assoc($rr);
	$provinciaid = $row['ProvinceID'];
	$rr = getprovincia($provinciaid,$paisid,$conn);
	$row = mysql_fetch_assoc($rr);
	$paisid = $row['CountryID'];
}

//se atualiza coordenadas de arvore que sao diferentes
if ($atualcoord=='1') {
	 if ($datatype=='amostracoletada')  {
		$colid = 'EspecimenID';
		$table = 'Especimenes';
	 }
	 if ($datatype=='planta')  {
			$colid = 'PlantaID';
			$table = 'Plantas';
	 }
	 $aarvals = $_SESSION['differentrecords2'];
	 foreach ($aarvals as $kk => $vv) {
		$valores = $vv;
		$recid = $vv['recid'];
		unset($valores['recid']);
		//print_r($valores);
		//echo "  $recid<br>";
		CreateorUpdateTableofChanges($recid,$colid ,$table,$conn);
		$updatespecid = UpdateTable($recid,$valores,$colid,$table,$conn);
		if (!$updatespecid) {
			$err++;
		}
	}
	if (empty($err)) {
			echo "<p class='success'>".GetLangVar('sucesso1')."</p>";
	}
	unset($_SESSION['differentrecords2']);
}

if ($final==1) {
$erro=0;
if (($datatype=='planta' && empty($gazetteerid)) || ($datatype=='amostracoletada' && empty($pessoaid))) {
		echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
			<tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
			if ($datatype=='planta' && empty($gazetteerid)) {
				echo "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namegazetteer')."</td></tr>";
			}
			if ($datatype=='amostracoletada' && empty($pessoaid)) {
				echo "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namecoletor')."</td></tr>";
			}
			echo " </table><br>";
			$erro++;
} 
	
if ($erro==0) {
//processa se os dados foram importados
	$myfile = $_FILES['uploadfile']['name'];
	if ($myfile) {
			$basename = explode(".",$_FILES['uploadfile']['name']);
			$basename = $basename[0];
			list ($ftypename, $ftype) = explode("/",$_FILES['uploadfile']['type']);
			$importdate = date("Y-m-d");			
			$newfilename = $importdate."_".$basename.".".trim($ftype);
			if (!file_exists('uploads/$newfilename')) {
				move_uploaded_file($_FILES["uploadfile"]["tmp_name"],"uploads/$newfilename");
			}
		
		$fop = fopen('uploads/'.$newfilename, 'r');
		$i=0;
		while (($data = fgetcsv($fop, 10000, "\t")) !== FALSE) {
		  	//$num = count($data);
		   //echo "<br>".$num;
		   		//print_r($data);
		   if ($i!=0) {
				$ptnome = trim($data[1]);
				$ptdata = trim($data[2]);
				$ptlatlong = trim($data[4]);
				$ptalt = trim($data[5]);
		
				$ptdata = explode(" ",$ptdata);
				$data = $ptdata[0];
				$time = $ptdata[1];
		
				$nn = explode(" ",$ptlatlong);
				$ptlat = $nn[0];
				$ptlatnors = substr($ptlat,0,1);
				$ptlat = substr($ptlat,1,20);
				if ($ptlatnors=='S') {
					$ptlat = $ptlat*(-1);
				}
				$ptlong = $nn[1];
				$ptlongwore = substr($ptlong,0,1);
				$ptlong = substr($ptlong,1,20);
				if ($ptlongwore=='W') {
					$ptlong = $ptlong*(-1);
				}
		
				
				$alt = explode(" ",$ptalt);
				$ptalt = $alt[0];
				//echo $ptlatlong;
				 $arrayofvalues = array(
					 'Latitude' => $ptlat,
					 'Altitude' => $ptalt,
					 'Longitude' => $ptlong
				 );
				 if ($datatype=='amostracoletada')  {
					$check = "SELECT * FROM Especimenes WHERE ColetorID='$pessoaid' AND Number='$ptnome'";
				 }
				 if ($datatype=='planta')  {
					$check = "SELECT * FROM Plantas WHERE PlantaTag='$ptnome' AND (GazetteerID='$gazetteerid'";
					$gazid= $gazetteerid;
					while (isset($gazid)) {
						$qq = "SELECT * FROM Gazetteer WHERE ParentID='$gazid'";
						//echo $qq;
						$res = @mysql_query($qq,$conn);
						if ($res) {
							$rrw = mysql_fetch_assoc($res);
							$gazid = $rrw['GazetteerID'];
							$gazids = array_merge((array)$gazids,(array)$gazid);				
						} else {
							unset($gazid);
						}
					}					
					if (count($gazids)>0) {
						foreach ($gazids as $kk => $vv) {
							$check = $check." OR GazetteerID='$vv'";
						}
					}
					$check = $check.")";
				 }
				//echo $check;
				$res = @mysql_query($check,$conn);
				$nres = @mysql_numrows($res);
				if ($res && $nres==1) {
						$rwr= mysql_fetch_assoc($res);
						$otlat = $rwr['Latitude'];
						$otlong = $rwr['Longitude'];
						$otalt = $rwr['Altitude'];	

						 if ($datatype=='amostracoletada')  {
								$recid = $rwr['EspecimenID'];	
								$colid = 'EspecimenID';
								$table = 'Especimenes';
						 }
						 if ($datatype=='planta')  {
								$recid = $rwr['PlantaID'];	
								$colid = 'PlantaID';
								$table = 'Plantas';

						 }
						if ($otlat==$ptlat && $otlong==$ptlong && $otalt==$ptalt) {
								$identicalrecords = array_merge((array)$identicalrecords,(array)$ptnome);
						} else {
							if (empty($otlat) && empty($otlong) && empty($otalt)) {
								$emptyrecords = array_merge((array)$emptyrecords,(array)$ptnome);
								CreateorUpdateTableofChanges($recid,$colid ,$table,$conn);
								$updatespecid = UpdateTable($recid,$arrayofvalues,$colid,$table,$conn);
							} else {
								if ($otlat!=$ptlat && $otlong!=$ptlong && $otalt!=$ptalt) {
									$aar = array('recid' => $recid,
											'Latitude' => $ptlat,
											'Altitude' => $ptalt,
											'Longitude' => $ptlong
											);
									$zz = array($ptnome => $aar);
									$differentrecords = array_merge((array)$differentrecords,(array)$ptnome);
									$differentrecords2 = array_merge((array)$differentrecords2,(array)$zz);
								}
							}
						}
					} else { //record does not exist or is in a different gazetteer
						$norecords = array_merge((array)$norecords,(array)$ptnome);
					}
				}	
			$i++;
		} //end while

		$norec = @count($norecords);
		$difrec = @count($differentrecords);
		$idenrec = @count($identicalrecords);
		$okrec = @count($emptyrecords);
		

		if ($norec>0 || $difrec>0) {		
			echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>";
				if ($difrec>0) {
					echo "<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro27')."</td></tr>
					<tr><td class='formnotes'>";
					foreach ($differentrecords as $kk => $vv) {
						echo $vv."<br>";
					}
					$_SESSION['differentrecords2'] = $differentrecords2;
					echo "</td></tr>
					<tr><td>
						<form action='gpscoordinatesforsamplesortrees.php' method='POST'>
								<input type='hidden' name='paisid' value='$paisid'>
								<input type='hidden' name='provinciaid' value='$provinciaid'>
								<input type='hidden' name='gazetteerid' value='$gazetteerid'>
								<input type='hidden' name='datatype' value='$datatype'>
								<input type='hidden' name='pessoaid' value='$pessoaid'>
								<input type='hidden' name='atualcoord' value='1'>
								<input type='submit' value='".GetLangVar('nameatualizar')."' class='bsubmit'>
					</form>
					</td></tr>
					";
				} 	
				if ($norec>0) {
					echo "
					<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro28')."</td></tr>
					<tr><td class='formnotes'>";
					foreach ($norecords as $kk => $vv) {
						echo $vv."<br>";
					}
					echo "</td></tr>";
				} 	
				echo "</table><br>";
		} elseif ($okrec>0) {
				echo "<p class='success'>".GetLangVar('sucesso1')."</p>";
		}
		if ($idenrec>0) {
				echo "<br>
				<table cellpadding=\"1\" width='50%' align='center' class='erro'>
				<tr class='tdsmallbold'><td  class='tdthinborder2'align='center'>".GetLangVar('erro29')."</td></tr>
				<tr class='formnotes'><td  class='tdthinborder2'>";
				foreach ($identicalrecords as $kk => $vv) {
					echo $vv."<br>";
				}
				echo "</td></tr></table><br>";
		}
	} //end if $myfile
} //end if $erro==0

} //if final
if ($datatype=='planta') { $title= GetLangVar('namecoordenadas')." ".mb_strtolower(GetLangVar('nametaggedplant'));}
if ($datatype=='amostracoletada') { $title= GetLangVar('namecoordenadas')." ".mb_strtolower(GetLangVar('nameamostra'))."s ".mb_strtolower(GetLangVar('namecoletada'))."s";}

echo "<br>
<table align='left' class='myformtable' cellpadding=\"5\">
<thead>
<tr>
<td colspan=100% >".$title."</td>
</tr>
</thead><tbody>
";

if ($datatype=='planta') {
echo "<tr>
<form name='paisform' action=gpscoordinatesforsamplesortrees.php method='post'>
	<input type='hidden' name='datatype' value='$datatype'>
	<td class='tdsmallbold' align='right'>".GetLangVar('namepais')."</td>	
<td >
	<select name='paisid' onchange='this.form.submit();'>";
			if (empty($paisid)) {
				$paisid=30; //Brasil				
			}	
			$rr = getpais($paisid,$conn);
			$row = mysql_fetch_assoc($rr);
			echo "<option selected value=".$row['CountryID'].">".$row['Country']."</option>";

			$rrr = getpais('',$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value=".$row['CountryID'].">".$row['Country']."</option>";
			}

echo "</select>
</td>
</form>
<form name='provinciaform' action=gpscoordinatesforsamplesortrees.php method='post'>	
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='datatype' value='$datatype'>

<td class='tdsmallbold' align='right'>".GetLangVar('namemajorarea')."</td>	
<td >
<select name='provinciaid' onchange='this.form.submit();'>";
			if (empty($provinciaid)) {
				echo "<option>".GetLangVar('nameselect')."</option>";
			} else {
				$rr = getprovincia($provinciaid,$paisid,$conn);			
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['ProvinceID'].">".$row['Province']."</option>";
			}
			$newrr = getprovincia('',$paisid,$conn);
			while ($row = mysql_fetch_assoc($newrr)) {
				echo "<option value=".$row['ProvinceID'].">".$row['Province']."</option>";
			}
echo "</select>
</td>
</form>

<form name='municipioform' action=gpscoordinatesforsamplesortrees.php method='post'>	
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='provinciaid' value='$provinciaid'>
		<input type='hidden' name='datatype' value='$datatype'>

<td class='tdsmallbold' align='right'>".GetLangVar('nameminorarea')."</td>	
<td >
	<select name='municipioid' onchange='this.form.submit();'>";
			if (empty($municipioid)) {
				echo "<option>".GetLangVar('nameselect')."</option>";
			} else {
				$rr = getmunicipio($municipioid,$provinciaid,$conn);			
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['MunicipioID'].">".$row['Municipio']."</option>";
			}
			$newrr = getmunicipio('',$provinciaid,$conn);
			while ($row = mysql_fetch_assoc($newrr)) {
				echo "<option value=".$row['MunicipioID'].">".$row['Municipio']."</option>";
			}
echo "</select>
</td>
</form>
</tr>
<tr>
<form action=gpscoordinatesforsamplesortrees.php method='post'>
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='provinciaid' value='$provinciaid'>
	<input type='hidden' name='municipioid' value='$municipioid'>
		<input type='hidden' name='datatype' value='$datatype'>

	<td class='tdsmallbold' align='right'>".GetLangVar('messagepertencea')." ".GetLangVar('namelocalidade')."</td>	
	<td colspan=5>
	<select name='gazetteerid' onchange='this.form.submit();'>";
			if (empty($gazetteerid)) {
					echo "<option>".GetLangVar('messageselecttoedit')."</option>";
			} else {
					//$qq = "SELECT GazetteerID,GazetteerTIPOtxt as GazTipo,Gazetteer FROM Gazetteer WHERE GazetteerID='$gazetteerid'";
					$qq = "SELECT GazetteerID,Gazetteer FROM Gazetteer WHERE GazetteerID='$gazetteerid'";
					$rr = mysql_query($qq,$conn);
					$rw = mysql_fetch_assoc($rr);	
					echo "<option selected value='$gazetteerid'>".$rw['Gazetteer']."</option>";
//					echo "<option selected value='$gazetteerid'>".$rw['GazTipo']." ".$rw['Gazetteer']."</option>";

			}		
			$res = listgazetteer($municipioid,$provinciaid,$conn);
			///
			while ($aa = mysql_fetch_assoc($res)){
					$PathName = $aa['PathName'];
					$level = $aa['MenuLevel'];
					//$gaztipo = $aa['GazTipo'];
					if ($level==1) {
						$espaco='';
						//echo "<option class='optselectdowlight' value=".$aa['GazetteerID'].">$espaco".$gaztipo." ".$aa['Gazetteer']."</option>";
						echo "<option class='optselectdowlight' value=".$aa['GazetteerID'].">".$espaco." ".$aa['Gazetteer']."</option>";
					} else {
						$espaco = str_repeat('&nbsp;&nbsp;',$level);
						$espaco = $espaco.str_repeat('-',$level-1);
						//echo "<option value=".$aa['GazetteerID'].">$espaco".$gaztipo." ".$aa['Gazetteer']."</option>";
						echo "<option value=".$aa['GazetteerID'].">".$espaco." ".$aa['Gazetteer']."</option>";
					}
			}
			echo "</select>
</td>
</form>
</tr>";

} //if datatype=='planta'

echo "
<form enctype='multipart/form-data' action='gpscoordinatesforsamplesortrees.php' method='POST'>
		<input type='hidden' name='paisid' value='$paisid'>
		<input type='hidden' name='provinciaid' value='$provinciaid'>
		<input type='hidden' id='gazetteerid' name='gazetteerid' value='$gazetteerid'>
			<input type='hidden' name='datatype' value='$datatype'>
			<input type='hidden' name='final' value='1'>";

if ($datatype=='amostracoletada') {
echo "<tr>
	<td class='tdsmallbold' align='right'>".GetLangVar('namecoletor')."</td>	
	<td colspan=5 class='tdformnotes'>
		<select name='pessoaid'>";
			echo "<option value=''>".GetLangVar('nameselect')."</option>";
			$rrr = getpessoa('',$abb=FALSE,$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value=".$row['PessoaID'].">".$row['Prenome']." ".$row['Sobrenome']."</option>";
			}
		echo "</select>
	</td>
	</tr>";
}

echo "
<tr>
<td class='tdsmallbold' align='right'>".GetLangVar('messageimportgeofile')."</td>	
<td colspan=5>	
		<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
		<input name='uploadfile' type='file'> 
</td>
</tr>";

echo "<tr>
	<td class='tdsmallbold' align='right'>".GetLangVar('namewarning')."</td>	
	<td colspan=5 class='tdorangebg'>";
if ($datatype=='planta') {
	echo GetLangVar('messageplantanumberonly');
} elseif ($datatype=='amostracoletada') {
	echo GetLangVar('messagesamplenumberonly');
}
echo "</td>
	</tr>";
echo "
<tr>
<td colspan=6 align='center'>	    
	    <input type='submit' value='".GetLangVar('nameenviar')."' class='bsubmit'>
</td>
</form>
</tr>
<tr class='tabsubhead'><td colspan=6>".GetLangVar('messageformatoarquivo')."
	&nbsp;<img height=12 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('messageexplaingps');
	echo	" onclick=\"javascript:alert('$help');\">";
echo "</td></tr>
<tr>
<td colspan=6 align='center'><table cellpadding='5' align='center' cellspacing=1>	    
<tr class='tdsmallbold'>
<td class='tdthinborder2'>Header</td>
<td class='tdthinborder2'>Name</td>
<td class='tdthinborder2'>Description</td>
<td class='tdthinborder2'>Type</td>
<td class='tdthinborder2'>Position</td>
<td class='tdthinborder2'>Altitude</td>
</tr>
<tr class='tdformnotes'>
<td class='tdthinborder2'>Waypoint</td>
<td class='tdthinborder2'>075</td>
<td class='tdthinborder2'>01-OUT-09 8:45:05</td>
<td class='tdthinborder2'>User Waypoint</td>
<td class='tdthinborder2'>S3.00689 W59.94006</td>
<td class='tdthinborder2'>150 m</td>
</tr></table>
</td></tr></tbody>
</table>";


HTMLtrailers();

?>

