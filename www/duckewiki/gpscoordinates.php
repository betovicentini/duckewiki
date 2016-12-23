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

//echo "<pre>";
//print_r($_GET);
//print_r($_POST);
//print_r($_FILES);
//echo "<pre>";


//echo "gaz".$gazetteerid;
//echo "muni".$municipioid;

if (empty($municipioid) && !empty($gazetteerid)) {
	$rr = getgazetteer($gazetteerid,$municipioid,$conn);
	$row = mysql_fetch_assoc($rr);
	$municipioid = $row['MunicipioID'];
}
//echo "gaz".$gazetteerid;
//echo "muni".$municipioid;
//processa se os dados foram importados
$myfile = $_FILES['uploadfile']['name'];
if ($myfile && !empty($gazetteerid)) {
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
$nomeok = array();
while (($data = fgetcsv($fop, 10000, "\t")) !== FALSE) {
  	//$num = count($data);
   //echo "<br>".$num;
   		//print_r($data);
   if ($i!=0) {
		$ptnn = trim($data[1]);
		$ptnome = strtoupper($ptnn);
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
			'Longitude' => $ptlong,
			'Gazetteer' => $ptnome,
			'Altitude' => $ptalt,
			'ParentID' => $gazetteerid,
			'GazetteerTIPO' => 277,
			//'GazetteerTIPOtxt' => 'Ponto de coleta',
			'MunicipioID' => $municipioid
			);
		 if (!empty($gazetteerid) && !empty($ptnome))  {
			$check = "SELECT * FROM Gazetteer WHERE Gazetteer='$ptnome' AND ParentID='$gazetteerid'";
			$res = @mysql_query($check,$conn);
			$nres = mysql_numrows($res);
			if ($nres>0) { //se ja tem um com esse nome
				$nrr = mysql_fetch_assoc($res);
				$llat = $nrr['Latitude'];
				$gazid = $nrr['GazetteerID'];
				$llong = $nrr['Longitude'];
				$llalt = $nrr['Altitude'];
				if (empty($llat) || empty($llong) || empty($llalt)) {
					$arrayofvalues = array(
						'Latitude' => $ptlat,
						'Longitude' => $ptlong,
						'Altitude' => $ptalt);
					$updategaz = UpdateTable($gazid,$arrayofvalues,'GazetteerID','Gazetteer',$conn);
				} else { $updategaz=TRUE;}
				if (!$updategaz) {
				 	$nomeok = array_merge((array)$nomeok,(array)$ptnome);
				}
			} else {
				$newgazid = InsertIntoTable($arrayofvalues,'GazetteerID','Gazetteer',$conn);					
				if (!$newgazid) {
					$nomeok = array_merge((array)$nomeok,(array)$ptnome);
				} 
			}
		}	
	}
	$i++;
}
//print_r($nomeok);
$rr = listgazetteer('','',$conn);
$ng = count($nomeok);
if ($ng>0) {
	echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
		<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro26')."</td></tr>";
		foreach ($nomeok as $k => $v) {
			echo "<tr><td class='tdformnotes' align='center'>".$v."</td></tr>";
		}
		echo "</table><br>";
} else {
		echo "<p class='success'>".GetLangVar('sucesso1')."</p>";
}
} //end if $myfile

echo "
<br>
<table align='left' class='myformtable' cellpadding=\"5\">
<thead>
<tr>
<td colspan=100% >".GetLangVar('nameimportar')."  ".GetLangVar('namegazetteer')."s (GPS)</td>
</tr>
</thead>
<tbody>
<tr>
<form name='paisform' action=gpscoordinates.php method='post'>
	<td class='tdsmallbold'>".GetLangVar('namepais')."</td>	
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
<form name='provinciaform' action=gpscoordinates.php method='post'>	
	<input type='hidden' name='paisid' value='$paisid'>
<td class='tdsmallbold'>".GetLangVar('namemajorarea')."</td>	
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

<form name='municipioform' action=gpscoordinates.php method='post'>	
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='provinciaid' value='$provinciaid'>
<td class='tdsmallbold'>".GetLangVar('nameminorarea')."</td>	
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
<form  enctype='multipart/form-data' action=gpscoordinates.php method='post'>
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='provinciaid' value='$provinciaid'>
	<input type='hidden' name='municipioid' value='$municipioid'>
	<td class='tdsmallbold'>".GetLangVar('messagepertencea')." ".GetLangVar('namelocalidade')."</td>	
	<td colspan=5>
	<select name='gazetteerid'>";
			if (empty($gazetteerid)) {
					echo "<option>".GetLangVar('messageselecttoedit')."</option>";
			} else {
					//$qq = "SELECT GazetteerID,GazetteerTIPOtxt as GazTipo,Gazetteer FROM Gazetteer WHERE GazetteerID='$gazetteerid'";
					$qq = "SELECT GazetteerID,Gazetteer FROM Gazetteer WHERE GazetteerID='$gazetteerid'";
					$rr = mysql_query($qq,$conn);
					$rw = mysql_fetch_assoc($rr);
					//echo "<option selected value='$gazetteerid'>".$rw['GazTipo']." ".$rw['Gazetteer']."</option>";
					echo "<option selected value='$gazetteerid'>".$rw['Gazetteer']."</option>";
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
						echo "<option value=".$aa['GazetteerID'].">".$espaco." ".$aa['Gazetteer']."</option>";
//						echo "<option value=".$aa['GazetteerID'].">$espaco".$gaztipo." ".$aa['Gazetteer']."</option>";
					}
			}
	
	$myurl = "localidade-novapopup.php?municipioid=$municipioid&paisid=$paisid&provinciaid=$provinciaid";
echo "</select>&nbsp;
	<input type=button 
	value='".GetLangVar('namenova')." ".mb_strtolower(GetLangVar('namegazetteer'))."' 
	onclick =\"javascript:small_window('$myurl',900,300,'NovaGazetteer');\">
</td>
</tr>";

echo "
<tr>
<td class='tdsmallbold'>".GetLangVar('messageimportgeofile')."</td>	
<td colspan=5>	
		<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
		<input name='uploadfile' type='file'> 
</td>
</tr>
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

