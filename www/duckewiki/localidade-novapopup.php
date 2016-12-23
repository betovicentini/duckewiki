<?php

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
PopupHeader($title,$body);
$erros=0;
//faz o cadastro de fato
if ($_POST['enviado']=='final') {
	$fieldsaskeyofvaluearray = array(
	'ParentID' => $parentgazid,
	'Gazetteer' => $gazetteer,
	'MunicipioID' => $municipioid,
	//'GazetteerTIPOtxt' => $gazetteertipo,
	'Notas' => trim($gazetteernota),
	'Latitude' => $latdec,
	'Longitude' => $longdec,
	 'Altitude' => $altitude);
	
	$coord = coordinates($latdec,$longdec,$latgrad,$longgrad,$latminu,$longminu,$latsec,$longsec,$latnors,$longwore);
	@extract($coord);
	 if ($_SESSION['editando']!=1)  {
			//$check = "SELECT * FROM Gazetteer WHERE CONCAT(GazetteerTIPOtxt,' ',Gazetteer)='".$gazetteertipo." ".$gazetteer."' AND ParentID='$parentgazid' AND MunicipioID='".$municipioid."'";
			$check = "SELECT * FROM Gazetteer WHERE LOWER(Gazetteer)=LOWER('".$gazetteer."') AND ParentID='".$parentgazid."' AND MunicipioID='".$municipioid."'";
			$res = @mysql_query($check,$conn);
			$nres = mysql_numrows($res);
			if ($nres>0) { //se ja tem um com esse nome
			 	if (isset($gazetteer_val)) {
					$rr = mysql_fetch_assoc($res);
					//$gazz = $gazetteertipo." ".$gazetteer;
					$gazz = $gazetteer;
					$gazid = $rr['GazetteerID']."_".$rr['MunicipioID'];
					echo "
					<form >
						<input type='hidden' id='gazid' value='".$gazid."' >
						<input type='hidden' id='gazetteer' value='$gazz'>
				
						<script language=\"JavaScript\">
						setTimeout(
							function() {
								passnewidandtxtoselectfield('".$gazetteer_val."','gazid','".$gazz."','');
							}
							,0.001);
					</script>
					</form>";	
				} else {
					echo "<table align='center' class='erro' cellpadding=\"5\" cellspacing=0 width='90%'>
				<tr><td>".GetLangVar('erro3')."</td></tr></table>";
				}
				
				
				
				
				
			} else {
				//echopre($fieldsaskeyofvaluearray);
				$newgazid = InsertIntoTable($fieldsaskeyofvaluearray,'GazetteerID','Gazetteer',$conn);					
				if ($newgazid) {
				UpdateGazetteerPath($newgazid,$conn);
				$gazetteerid = $newgazid;
				 echo "<table align='center' class='success' cellpadding=\"5\" cellspacing=0 width='90%'><tr>
					<td>".GetLangVar('sucesso1')."</td></tr></table>";
					UpdataLocalitySimple($gazetteerid,$paisid,$conn);
				} else {
					$erros++;
				 echo "<table align='center' class='erro' cellpadding=\"5\" cellspacing=0 width='90%'><tr>
					<td>Erro!</td></tr></table>";
			
				}

			}
	} 
	else {
			//update if newvalue is different from old value
			$check = "SELECT * FROM Gazetteer WHERE GazetteerID='$gazetteerid'";
			$ch = mysql_query($check,$conn);
			$chh = mysql_fetch_assoc($ch);
			$update=0;
			foreach ($fieldsaskeyofvaluearray as $kj => $vj) {
				if ($chh[$kj]!=$vj) {
					$update++;
				}
			}
			if ($update>0) {
				CreateorUpdateTableofChanges($gazetteerid,'GazetteerID','Gazetteer',$conn);
				$newgazid = UpdateTable($gazetteerid,$fieldsaskeyofvaluearray,'GazetteerID','Gazetteer',$conn);
				if (!$newgazid) {
					$erros++;
				} else {
					UpdateGazetteerPath($newgazid,$conn);
				}
			}
			
	}
		if ($erros==0) {
			if (isset($gazetteer_val)) {
				$qq = "SELECT * FROM Gazetteer WHERE GazetteerID='$gazetteerid'";
				$res = mysql_query($qq,$conn);
				$rr = mysql_fetch_assoc($res);
				//$gazz = $gazetteertipo." ".$gazetteer;
				$gazz = $gazetteer;
				$gazid = $rr['GazetteerID']."_".$rr['MunicipioID'];
				echo "
				<form >
				<input type='hidden' id='gazid' value='".$gazid."' >
				<input type='hidden' id='gazetteer' value='$gazz'>
				
				<script language=\"JavaScript\">
				setTimeout(
					function() {
						passnewidandtxtoselectfield('".$gazetteer_val."','gazid','".$gazz."','');
						}
						,0.001);
				</script>
				</form>";	
			
			
			} else {
				echo "<form name='myform'>						
						<script language=\"JavaScript\">setTimeout('window.close()',0.0001);</script>
					</form>";
			}
		} else {
				echo "<table align='center' class='erro' cellpadding=\"5\" cellspacing=0 width='90%'><tr>
				<td>Erro!</td></tr></table><br>";
		}

} 
else {

if ($_POST['enviado']=='1') {
		echo "<table  align='center' class='erro' border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"80%\">";
//	if ($gazetteertipo=='digite aqui um novo tipo') {
//		unset($gazetteertipo);
//	}
//	if (empty($gazetteertipo) && !empty($normalizedtipo)) {
//		$ggtipo = $normalizedtipo;
//	} elseif (!empty($gazetteertipo)) {
//		$ggtipo = $gazetteertipo;
//	}
//
	//if (empty($ggtipo) || empty($gazetteer)) {
	if (empty($gazetteer)) {
		echo "<tr><td colspan=2>".GetLangVar('nameobrigatorio').": <i>".GetLangVar('namenome')."</i></td></tr>";
		//echo "<tr><td colspan=2>".GetLangVar('nameobrigatorio').": <i>".GetLangVar('namenome')." & ".GetLangVar('nametipo')."</i></td></tr>";
		$erros++;
	} 
	$coord = coordinates('','',$latgrad,$longgrad,$latminu,$longminu,$latsec,$longsec,$latnors,$longwore);
	@extract($coord);
	if ($longsec>60 || $latminu>60 || abs($latgrad)>180 || $longsec>60 || $longminu>60 || abs($longgrad)>180) {
		echo "<tr><td colspan=2>".GetLangVar('namecoordenadas')." > 60 </td></tr>";	
		$erros++;
	}
	if (!empty($latdec) && !empty($longdec) && empty($_POST['coordenadasok']) && empty($gazetteerid)) {
		$latd1 = $latdec-0.1;
		$latd2 = $latdec+0.1;
		$longd1 = $longdec-0.1;
		$longd2 = $longdec+0.1;		
		$qq = "SELECT * FROM Gazetteer WHERE ABS(Latitude)>'$latd1' AND ABS(Latitude)<'$latd2' AND ABS(Longitude)>'$longd1' AND ABS(Longitude)<'$longd2'";
		$res  = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		if ($nres>0) {
			echo "<tr><td colspan=2>".GetLangVar('erro6')."</td></tr>";
			echo "<tr><td colspan=2 >
				<table width=\"70%\" align='center' style=\"background-color:#F0F0F0\" border=\"1\" cellpadding=\"3\" cellspacing=\"0\">
					<tr><td><b>".GetLangVar('namenome')."</b></td><td><b>Latitude</b></td><td><b>Longitude</b></td></tr>";
					//<tr><td><b>".GetLangVar('nametipo')."</b></td><td><b>".GetLangVar('namenome')."</b></td><td><b>Latitude</b></td><td><b>Longitude</b></td></tr>";
					while ($rwo = mysql_fetch_assoc($res)) {
						//echo "<tr><td>".$rrw['GazetteerTIPOtxt']."</td><td>".$rwo['Gazetteer']."</td><td>".$rwo['Latitude']."</td><td>".$rwo['Longitude']."</td></tr>";
						echo "<tr><td>".$rwo['Gazetteer']."</td><td>".$rwo['Latitude']."</td><td>".$rwo['Longitude']."</td></tr>";
					}
					//						<input type='hidden' name='gazetteertipo' value='$gazetteertipo'>
			echo "</table>
			</td></tr>
				<tr><td colspan=2>&nbsp;</td></tr>
				<tr>
					<form action=localidade-novapopup.php method='post'>	
						<input type='hidden' name='paisid' value='$paisid'>
						<input type='hidden' name='provinciaid' value='$provinciaid'>
						<input type='hidden' name='municipioid' value='$municipioid'>
						<input type='hidden' name='gazetteer' value='$gazetteer'>
						<input type='hidden' name='gazetteerid' value='$gazetteerid'>
						<input type='hidden' name='gazetteernota' value='$gazetteernota'>
						<input type='hidden' name='parentgazid' value='$parentgazid'>
						<input type='hidden' name='altitude' value='$altitude'>
						<input type='hidden' name='longdec' value='$longdec'>
						<input type='hidden' name='latdec' value='$latdec'>
						<input type='hidden' name='doitnocoord' value='".$_POST['doitnocoord']."'>
						<input type='hidden' name='coordenadasok' value='1'>
						<input type='hidden' name='enviado' value='1'>
						<input type='hidden' name='gazetteer_val' value='$gazetteer_val' >
						<td align='right'><input type='submit' value=".GetLangVar('nameconfirma')." class='bsubmit'>&nbsp;</td>
					</form>
					<form method='post'>	
						<td align='left'><input type='submit' value=".GetLangVar('namecancel')." class='breset' onclick=\"this.window.close();\"></td>
					</form>
				</tr>";
				$erros++;
//		      			<input type='hidden' name='normalizedtipo' value='$normalizedtipo'>
		}
	} else {
		if (empty($_POST['doitnocoord']) && empty($latdec) && empty($longdec)) {
//						<input type='hidden' name='gazetteertipo' value='$gazetteertipo'>
//		      			<input type='hidden' name='normalizedtipo' value='$normalizedtipo'>


				echo "<form action=localidade-novapopup.php method='post'>	
					<tr><td colspan=2>".GetLangVar('erro7')."
						<input type='hidden' name='gazetteer_val' value='$gazetteer_val' >

						<input type='hidden' name='paisid' value='$paisid'>
						<input type='hidden' name='provinciaid' value='$provinciaid'>
						<input type='hidden' name='municipioid' value='$municipioid'>
						<input type='hidden' name='gazetteer' value='$gazetteer'>
						<input type='hidden' name='gazetteernota' value='$gazetteernota'>
						<input type='hidden' name='parentgazid' value='$parentgazid'>

						<input type='hidden' name='latgrad' value='$latgrad'>
						<input type='hidden' name='latminu' value='$latminu'>
						<input type='hidden' name='latsec' value='$latsec'>
						<input type='hidden' name='latnors' value='$latnors'>
						<input type='hidden' name='gazetteerid' value='$gazetteerid'>

						<input type='hidden' name='longgrad' value='$longgrad'>
						<input type='hidden' name='longminu' value='$longminu'>
						<input type='hidden' name='longsec' value='$longsec'>
						<input type='hidden' name='longwore' value='$longwore'>
						<input type='hidden' name='altitude' value='$altitude'>
				
						<input type='hidden' name='coordenadasok' value='".$_POST['coordenadasok']."'>
						<input type='hidden' name='doitnocoord' value='1'>
						<input type='hidden' name='enviado' value='1'>
					
				&nbsp;<input type='submit' value='ok!' class='bsubmit'></td></tr>
				</form>";
				$erros++;
		}
	}
echo "</table><br>";
	//faz o cadastro dos dados no banco de dados
	if ($erros==0) {
	
		if ($_SESSION['editando']!=1) {
		echo "<table align='left' class='myformtable' >
		<thead>
		<tr ><td>".GetLangVar('namecadastrar')." </td></tr>
		</thead>
		<tbody>
		<tr><td>
		<table  align='center' border=1 cellpadding=3 cellspacing=0>";
		$rr = getpais($paisid,$conn);
		$row = mysql_fetch_assoc($rr);
		echo "<tr><td align='right'><b>Pais</b></td><td align='left'><i>".$row['Country']."</i></td></tr>";

		$rr = getprovincia($provinciaid,$paisid,$conn);			
		$row = mysql_fetch_assoc($rr);
		echo "<tr><td align='right'><b>Province</b></td><td align='left'><i>".$row['Province']."</i></td></tr>";

		$rr = getmunicipio($municipioid,$provinciaid,$conn);			
		$row = mysql_fetch_assoc($rr);
		echo "<tr><td align='right'><b>Municipio</b></td><td align='left'><i>".$row['Municipio']."</i></td></tr>";
		
		$rr = getgazetteer($parentgazid,$municipioid,$conn);
		$row = mysql_fetch_assoc($rr);
		//echo "<tr><td align='right'><b>".GetLangVar('namegazetteer')." ".GetLangVar('nameparent')."</b></td><td align='left'><i>".$row['GazetteerTIPOtxt']." ".$row['Gazetteer']."</i></td></tr>";
		echo "<tr><td align='right'><b>".GetLangVar('namegazetteer')." ".GetLangVar('nameparent')."</b></td><td align='left'><i>".$row['Gazetteer']."</i></td></tr>";
		//echo "<tr><td align='right' ><b>".GetLangVar('namegazetteer')." <i>".GetLangVar('nametipo')."</i></b></td><td align='left'><i>".$gazetteertipo."</i></td></tr>";
		echo "<tr class='orangebg'><td align='right'><b>".GetLangVar('namegazetteer')." <i>".GetLangVar('namenome')."</i></b></td><td align='left'><i>$gazetteer</i></td></tr>";
		echo "<tr><td align='right' ><b>".GetLangVar('namegazetteer')." <i>".GetLangVar('nameobs')."</i></b></td><td align='left'><i>$gazetteernota</i></td></tr>";
		echo "<tr><td align='right'><b>Latitude</b></td><td align='left'><i>$latdec</i></td></tr>";
		echo "<tr><td align='right'><b>Longitude</b></td><td align='left'><i>$longdec</i></td></tr>";
		echo "<tr><td align='right'><b>Altitude</b></td><td align='left'><i>$altitude</i></td></tr>
		</table></td></tr><tr><td align='center'>";
//						<input type='hidden' name='gazetteertipo' value='$gazetteertipo'>
		echo "<form action=localidade-novapopup.php method='post'>	
		<input type='hidden' name='gazetteer_val' value='$gazetteer_val' >

						<input type='hidden' name='paisid' value='$paisid'>
						<input type='hidden' name='provinciaid' value='$provinciaid'>
						<input type='hidden' name='municipioid' value='$municipioid'>
						<input type='hidden' name='gazetteer' value='$gazetteer'>
						<input type='hidden' name='gazetteernota' value='$gazetteernota'>
						<input type='hidden' name='parentgazid' value='$parentgazid'>
						<input type='hidden' name='altitude' value='$altitude'>
          				<input type='hidden' name='gazetteerid' value='$gazetteerid'>
						<input type='hidden' name='latdec' value='$latdec'>
						<input type='hidden' name='longdec' value='$longdec'>
						<input type='hidden' name='enviado' value='final'>
						<input type='submit' value=".GetLangVar('nameconfirma')." class='bsubmit'></td></tr>
				</form>
				</td>
			</tr>
		</tbody>
		</table><br>";
//		      			<input type='hidden' name='normalizedtipo' value='$normalizedtipo'>

		} else {
			echo "<form name='myform' action=localidade-novapopup.php method='post'>
						<input type='hidden' name='gazetteer_val' value='$gazetteer_val' >

						<input type='hidden' name='paisid' value='$paisid'>
						<input type='hidden' name='provinciaid' value='$provinciaid'>
						<input type='hidden' name='municipioid' value='$municipioid'>
						<input type='hidden' name='gazetteer' value='$gazetteer'>
						<input type='hidden' name='gazetteernota' value='$gazetteernota'>
						<input type='hidden' name='parentgazid' value='$parentgazid'>
						<input type='hidden' name='altitude' value='$altitude'>
          				<input type='hidden' name='gazetteerid' value='$gazetteerid'>
						<input type='hidden' name='latdec' value='$latdec'>
						<input type='hidden' name='longdec' value='$longdec'>
						<input type='hidden' name='enviado' value='final'>
						<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
				</form>";
//		      			<input type='hidden' name='normalizedtipo' value='$normalizedtipo'>
				//						<input type='hidden' name='gazetteertipo' value='$gazetteertipo'>

		}
	}
	
} else {

unset($_POST['coordenadasok']);
unset($_POST['doitnocoord']);
unset($_SESSION['editando']);

} //terminou a atualizacao dos dados

echo "<br><table align='left' class='myformtable' cellpadding=\"4\">
<thead>
<tr>
<td colspan=100%>";

if (!empty($gazetteerid) && $gazetteerid!=GetLangVar('messageselecttoedit')) {
		$qq = "SELECT * FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) WHERE GazetteerID='$gazetteerid'";
		$res = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($res);
		$municipioid = $row['MunicipioID'];
		$provinciaid = $row['ProvinceID'];
		$paisid = $row['CountryID'];
		echo GetLangVar('nameeditar')." ".GetLangVar('namegazetteer');
} 
else {
	echo GetLangVar('namecadastrar')." ".mb_strtolower(GetLangVar('namenova')." ".GetLangVar('namegazetteer'));
}
echo "
</td>
</tr></thead>
";

if ($formsubmited!='editar') {
echo "<thead>
<tr class='subhead'>
<td colspan=100% >".GetLangVar('namegeopolitical')."</td>
</tr>
</thead>
<tbody>
<tr>
<form action=localidade-novapopup.php method='post'>
	<input type='hidden' name='gazetteer_val' value='$gazetteer_val' >
	<input type='hidden' name='gazetteerid' value='$gazetteerid'>
	<td class='tdformright'>".GetLangVar('namepais')."</td>	
<td >
	<select name='paisid' onchange='this.form.submit();'>";
			if (empty($paisid)) {
				$paisid=30; //Brasil				
			} 
			$rr = getpais($paisid,$conn);
			$row = mysql_fetch_assoc($rr);
			echo "<option selected value=".$row['CountryID'].">".$row['Country']."</option>";
			echo "<option value=''>---</option>";
			$rrr = getpais('',$conn);
			while ($row = mysql_fetch_assoc($rrr)) {
				echo "<option value=".$row['CountryID'].">".$row['Country']."</option>";
			}
echo "</select>
</td>
</form>
<form action=localidade-novapopup.php method='post'>	
	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='gazetteer_val' value='$gazetteer_val' >
	<input type='hidden' name='gazetteerid' value='$gazetteerid'>

<td class='tdformright'>".GetLangVar('namemajorarea')."</td>	
<td >
<select name='provinciaid' onchange='this.form.submit();'>";
			if (empty($provinciaid)) {
				echo "<option  value=''>".GetLangVar('nameselect')."</option>";
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

<form action=localidade-novapopup.php method='post'>
	<input type='hidden' name='gazetteer_val' value='$gazetteer_val' >

	<input type='hidden' name='paisid' value='$paisid'>
	<input type='hidden' name='provinciaid' value='$provinciaid'>
	<input type='hidden' name='gazetteerid' value='$gazetteerid'>

<td class='tdformright'>".GetLangVar('nameminorarea')."</td>	
<td >
	<select name='municipioid' onchange='this.form.submit();'>";
			if (empty($municipioid)) {
				echo "<option value=''>".GetLangVar('nameselect')."</option>";
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
</tr></tbody>";
} //end if formsubmitted!='editar'


if (!empty($gazetteerid) && $formsubmited=='editar') { //se editando
	$_SESSION['editando'] =1;
	$qq = "SELECT * FROM Gazetteer WHERE GazetteerID='$gazetteerid'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$parentgazid = $row['ParentID'];
	//$gazetteertipo = $row['GazetteerTIPOtxt'];
	//$normalizedtipo = $row['Tipo'];
	$gazetteernota = trim($row['Notas']);
	$gazetteer = $row['Gazetteer'];
	$altitude = $row['Altitude'];	
	$longdec = trim($row['Longitude']);
	$latdec = trim($row['Latitude']);
	$coord = coordinates($latdec,$longdec,$latgrad,$longgrad,$latminu,$longminu,$latsec,$longsec,$latnors,$longwore);
	@extract($coord);
}



if (!empty($municipioid)) {

echo "<form enctype='multipart/form-data' action='localidade-novapopup.php' method='post'>
				<input type='hidden' name='gazetteer_val' value='$gazetteer_val' >

				<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
				<input type='hidden' name='paisid' value='$paisid'>
				<input type='hidden' name='provinciaid' value='$provinciaid'>
				<input type='hidden' name='municipioid' value='$municipioid'>
				<input type='hidden' name='formsubmitted' value='$formsubmitted'>
				<input type='hidden' name='doitnocoord' value='$doitnocoord'>
				<input type='hidden' name='coordenadasok' value='$coordenadasok'>
				<input type='hidden' name='gazetteerid' value='$gazetteerid'>
      			<input type='hidden' name='enviado' value='1'>
      			";
//	   			<input type='hidden' name='normalizedtipo' value='$normalizedtipo'>

echo "<thead>
<tr class='subhead'>
<td colspan=100% >".GetLangVar('namegazetteer')." ".GetLangVar('namedefinicao')."</td>
</tr>
</thead>
<tbody>";

//if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
//echo "<tr bgcolor = $bgcolor>
//<td class='tdsmallbold' align=right>".GetLangVar('nametipo')."</td>";
//echo "
//<td colspan='5'>
//	<table>
//	<tr><td>";
//	echo "<select id='gaztipotxt' onchange=\"javascript:getselectoptionsendtoinput('gaztipotxt','gazetteertipo');\">";
//		echo"<option value=''>".GetLangVar('nameselect')."</option>";	
//		echo"<option value=''>------</option>";
//		$qqq = "SELECT DISTINCT GazetteerTIPOtxt FROM Gazetteer ORDER BY GazetteerTIPOtxt";
//		$sql = mysql_query($qqq,$conn);
//		while ($aa = mysql_fetch_assoc($sql)){
//			echo "<option value=".$aa['GazetteerTIPOtxt'].">".$aa['GazetteerTIPOtxt']."</option>";
//		}
//		if (empty($gazetteertipo)) {
//			$gazetteertipo = 'digite aqui um novo tipo';	
//		}
//		echo "</select>
//		</td>
//			<td class='tdsmallbold' align=center>".mb_strtolower(GetLangVar('nameor'))."</td>		
//		<td colspan=3 align='left'>
//			<input type='text' size='15' id='gazetteertipo' name='gazetteertipo' value='$gazetteertipo'>";
//echo "</td></tr>
//	</table>
//</td></tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
<td class='tdsmallbold' align='right'>".GetLangVar('namenome')."</td>	
<td colspan=5>
	<input type='text' name='gazetteer' value='$gazetteer'>
</td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
<td class='tdsmallbold' align='right'>".GetLangVar('messagepertencea')." ".GetLangVar('namelocalidade')."</td>	
<td colspan=5>
	<select name='parentgazid'>";
			if (empty($parentgazid)) {
					echo "<option value=''>".GetLangVar('novagazetteer2')."</option>";
			} else {
				$rr = getgazetteer($parentgazid,$municipioid,$conn);
				$row = mysql_fetch_assoc($rr);
				//echo "<option selected value='".$row['GazetteerID']."'>".$row['GazetteerTIPOtxt']." ".$row['Gazetteer']."</option>";
				echo "<option selected value='".$row['GazetteerID']."'>".$row['Gazetteer']."</option>";
			}
			echo "<option value=''>----</option>";
			$rr = getgazetteer('',$municipioid,$conn);
			$level = '';
			while ($aa = mysql_fetch_assoc($rr)){
					$PathName = $aa['PathName'];
					//$gaztipo = $aa['GazetteerTIPOtxt'];
					if ($level!=$PathName) {
						//echo "<option value='".$aa['GazetteerID']."'>".$gaztipo." ".$aa['Gazetteer']."</option>";
						echo "<option value='".$aa['GazetteerID']."'>".$aa['Gazetteer']."</option>";
					} else {
						$espaco = $espaco.'&nbsp;';
						//echo "<option value='".$aa['GazetteerID']."'>$espaco".$gaztipo." ".$aa['Gazetteer']."</option>";
						echo "<option value='".$aa['GazetteerID']."'>".$espaco." ".$aa['Gazetteer']."</option>";
					}
					$level=$PathName;
			}
echo "</select>
</td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
<td class='tdsmallbold' align='right'>".GetLangVar('nameobs')."</td>	
<td colspan=5>";
$gaz = trim($gazetteernota);
if (empty($gaz)) {$gazetteernota='';}
echo "<textarea cols=80 rows=2 name='gazetteernota'>".trim($gazetteernota)."</textarea></td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor><td class='tdsmallbold' align='right'>".GetLangVar('namecoordenadas')."</td>
<td colspan=5>
		<table><tr class='tdformnotes'><td align='right'><i>Latitude*</i></td>
		<td >
			<table border=0 cellpadding=\"3\">
			<tr class='tdformnotes'>
				
				<td ><input type='text' size=6 name='latgrad' value='$latgrad' ></td>
				<td align='left'><sup>o</sup></td>
				<td ><input type='text' size=3 name='latminu' value='$latminu' ></td>
				<td align='left'>'</td>
				<td ><input type='text' size=3 name='latsec' value='$latsec' ></td>
				<td align='left'>\"</td>
				<td align='right'><input type='radio' name='latnors' "; 
					if ($latnors=='N') { echo "checked";}
					echo " value='N'></td>
				<td align='left'>N
				</td>
				<td align='right'><input type='radio' name='latnors' "; 
					if ($latnors=='S') { echo "checked";}
					echo "  value='S'></td>
				<td align='left'>S</td>	
			<tr>
			</table>
		</td></tr>
		<tr>
			<td align='right'><i>Longitude*</i></td>
		<td >
			<table border=0 cellpadding=\"3\">
			<tr class='tdformnotes'>	
				<td align='center'><input type='text' size=6 name='longgrad' value='$longgrad' ></td>
				<td align='left'><sup>o</sup></td>
				<td align='left'><input type='text' size=3 name='longminu' value='$longminu' ></td>
				<td align='left'>'</td>
				<td align='left'><input type='text' size=3 name='longsec' value='$longsec' ></td>
				<td align='left'>\"</td>
				<td align='left'>		
				<td align='right'><input type='radio' name='longwore' "; 
					if ($longwore=='W') { echo "checked";}
					echo " value='W'></td>
				<td align='left'>W</td>
				<td align='right'><input type='radio' name='longwore' "; 
					if ($longwore=='E') { echo "checked";}
					echo "  value='E'></td>
				<td align='left'>E</td>	
			</tr>
			</table>
		</td></tr><tr><td align='right'><i>Altitude</i></td>
		<td >
			<table border=0 cellpadding=\"3\">
			<tr class='tdformnotes'>	
				
				<td align='center'><input type='text' size=6 name='altitude' value='$altitude'></td>
				<td align='left'>m</td>
			</tr>			
			</table>	
		</td>
		</tr>
	</table>
<tr>
	<td colspan=6 align='left' class='tdformnotes'>&nbsp;&nbsp;*&nbsp;".GetLangVar('namegeocoordnote')."</td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
<td colspan=100% align='center'><table><tr><td>
			<input type='submit' value=".GetLangVar('nameenviar')." class='bsubmit'>
</td>
</form>
<form action=localidade-novapopup.php method='post'>
		<input type='hidden' name='gazetteer_val' value='$gazetteer_val' >
<td>
		<input type='submit' value=".GetLangVar('namereset')." class='breset'>
</td>
</tr></table></td>
</form>
</tr>";	

}

echo "<tbody>
</table>";
}
PopupTrailers();

?>