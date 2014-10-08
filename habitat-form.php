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


if ($option=='2') {
	unset($arval['MAX_FILE_SIZE' ],  $arval['habitatname' ],  $arval['habitattipo'],  $arval['locality' ],  $arval['specieslistids'],  $arval['option' ],  $arval['teste' ],  $arval['teste2' ],  $arval['specieslist']);
	$result = array();
	foreach ($arval as $key => $value) {	
		$arraykey = explode("_",$key); 
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		if ($varorunit=='traitmulti' && !empty($value)) {
			$nno = "traitvar_".$charid;
			if (array_key_exists($nno,$result)) {
				$result[$nno] = $result[$nno]."; ".$value;
			} else {
				$nar = array($nno => $value);
				$result = array_merge((array)$result,(array)$nar);
			}
		}
	}
}

$arval = array_merge((array)$arval,(array)$result);


if ($option=='2') {
$erro =0;
//se for uma classe
if ($habitattipo=='Class') {
	if (empty($habitatname)) {
	$erro++;
	echo "<p align='center' class='erro'>".GetLangVar('erro1')."<br>
	".GetLangVar('namenome')." ".GetLangVar('namehabitat').",&nbsp; 
	</p>";
	} else {
	$qq = "SELECT * FROM Habitat WHERE Habitat LIKE '$habitatname'";
	$teste = mysql_query($qq,$conn);
	$update = @mysql_numrows($teste);
	$habitatname = ucfirst(strtolower($habitatname));
	if (!isset($habitatdefinicao)) {$habitatdefinicao='';}	
		$fieldsaskeyofvaluearray = array(
			'Habitat' => $habitatname,
			'HabitatTipo' => $habitattipo,
			'Descricao' => $habitatdefinicao,
			'ParentID' => $parentid);
		if ($update==0 && (empty($habitatid) || $habitatid==GetLangVar('nameselect'))) {
			$newhabitatid = InsertIntoTable($fieldsaskeyofvaluearray,'HabitatID','Habitat',$conn);
			if (!$newhabitatid) {
				$erro++;
			} else {
					updatehabitatpath($newhabitatid,$conn);
			}
		} else { //if editing
			if (!empty($habitatid) && $habitatid!=GetLangVar('nameselect')) {
			//echo "here $habitatid";
			$qq = "SELECT * FROM Habitat WHERE HabitatID='$habitatid'";
			$teste = mysql_query($qq,$conn);
			$rrr = mysql_fetch_assoc($teste);
			$oldval = $rrr['Habitat'];
			$oldids  = $rrr['EspeciesIds'];
			$oldef  = $rrr['Descricao'];			
			$olparid  = $rrr['ParentID'];			

			if ($habitatname!=$oldval || $specieslistids!=$oldids || $oldef!=$habitatdefinicao || $parentid!=$olparid) { 
				//update if newvalue is different from old value
				CreateorUpdateTableofChanges($habitatid,'HabitatID','Habitat',$conn);
				$newhabitatid = UpdateTable($habitatid,$fieldsaskeyofvaluearray,'HabitatID','Habitat',$conn);
				if (!$newhabitatid) {
					$erro++;
				} else {
					updatehabitatpath($newhabitatid,$conn);
				}
			} else {
				$erro++;
				echo "<p>Ja existe um habitat com esse nome</p>";
			}		
		} //end editing
	}
	}///if empty habitatname
} elseif ($habitattipo=='Local') {


if ($parentid==GetLangVar('nameselect')) {$parentid='';}

if (empty($habitatname) || empty($parentid)) {
	$erro++;
	echo "<table cellpadding='2' cellspacing=0 class='erro' width='50%' align='center'>
	<tr><td class='tdformnotes'><b>".GetLangVar('erro1')."</b></td></tr>";
	if (empty($habitatname)) {
		echo "<tr><td class='tdformnotes'><i>".GetLangVar('namenome')."</i></td><tr>";
	}
	if (empty($parentid)) {
		echo "<tr><td class='tdformnotes'><i>".strtolower(GetLangVar('habitatclasse'))."</i></td><tr>";
	}
	echo "</table>";
} else {
	$qq = "SELECT * FROM Habitat WHERE Habitat LIKE '$habitatname' AND LocalityID='$gazetteerid'";
	//echo $qq;
	$teste = mysql_query($qq,$conn);
	$update = @mysql_numrows($teste);
		$fieldsaskeyofvaluearray = array(
			'Habitat' => $habitatname,
			'EspeciesIds' => $specieslistids,
			'HabitatTipo' => $habitattipo,
			'LocalityID' => $gazetteerid,
			'GPSPointID' => $gpspointid,
			'ParentID' => $parentid);
	if ($update==0 && (empty($habitatid) || $habitatid==GetLangVar('nameselect'))) {
			$newhabitatid = InsertIntoTable($fieldsaskeyofvaluearray,'HabitatID','Habitat',$conn);
			if (!$newhabitatid) {
				$erro++;
			} else {
					updatehabitatpath($newhabitatid,$conn);
			}
	} else { //if editing
		if (!empty($habitatid) && $habitatid!=GetLangVar('nameselect')) {
		$qq = "SELECT * FROM Habitat WHERE HabitatID='$habitatid'";
		$teste = mysql_query($qq,$conn);
		$rrr = mysql_fetch_assoc($teste);
		$oldval = $rrr['Habitat'];
		$oldids  = $rrr['EspeciesIds'];
		$oldgaz  = $rrr['LocalityID'];
		if ($habitatname!=$oldval || $specieslistids!=$oldids || $gazetteerid!=$oldgaz) { 
				//update if newvalue is different from old value
				CreateorUpdateTableofChanges($habitatid,'HabitatID','Habitat',$conn);
				$newhabitatid = UpdateTable($habitatid,$fieldsaskeyofvaluearray,'HabitatID','Habitat',$conn);
			if (!$newhabitatid) {
					$erro++;
			} else {
					updatehabitatpath($newhabitatid,$conn);
			}
		}
		} else {
			$erro++;
			echo "<table cellpadding='3' cellspacing=0 class='erro' width='50%' align='center'>
				<tr>
			<td class='tdformnotes'>".GetLangVar('erro18')."</td></tr></table><br>";
		}
	}
	if ($erro==0) { //se nao houve erro na primeira parte faz a segunda
	if ((empty($habitatid) || $habitatid==GetLangVar('nameselect')) && !empty($newhabitatid)) {
		$habitatid = $newhabitatid;
	}
	//echopre($arval);
	foreach ($arval as $key => $value) {	
		$arraykey = explode("_",$key);
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		if ($varorunit=='traitvar' && !empty($value)) {
			if (count($value)>1) {$value = implode(";",$value);} 
		}
		///////////////////////////////////////////////////////////////////////
		//echo $key."   ".$value." AQUI POXA<br>";		
		if ($varorunit=='traitimg' && $value=='imagem') {
			$string = 'traitimgold_'.$charid;
			$valvel  = eval("return $". $string . ";");			
			$valvel = explode(";",$valvel);
			if (count($valvel)>0) { $valoresvelhos = $valvel;} else { $valoresvelhos= array($valvel);}			
			$myfile = $_FILES['trait_'.$charid.'_0']['name'];
			if ($myfile) {
				$vvimgar = $valvel;
				$fotografo = $arval["traitimgautor_".$charid]; //checar isso
				foreach ($_FILES as $key => $val) {
						$cid = explode("_",$key);
						if ($cid[1]==$charid) {
							$basename = $_FILES[$key]['name'];
							if (!empty($basename)) {
								$filedate = date("Y-m-d");			
								$meufile = $filedate."_charid".$charid."_".$fotografo."_".$basename;
								move_uploaded_file($_FILES[$key]["tmp_name"],"img/temp/$meufile");

								$ext = explode(".",$basename);
								$ll = count($ext)-1;
								$imgext = strtoupper($ext[$ll]);
								if ($imgext=='JPG' || $imgext=='TIFF' || $imgext=='TIF' || $imgext=='JPEG') {
									
									$inputfile = "img/temp/$meufile";
									$metadata = @read_exif_data($inputfile);
				
									$DateTimeOriginal =$metadata['DateTimeOriginal'];
									$dattt = explode(" ",$DateTimeOriginal);
				
									$dateoriginal = $dattt[0];
									$timeoriginal = $dattt[1];
				
									$tt = explode(":",$timeoriginal);
									$ttsec = (((($tt[0]*60)+$tt[1])*60)+$tt[2]);
				
									$dd = str_replace(":","-",$dateoriginal);
									$dd = new DateTime($dd);
									$dateoriginal = $dd->format("Y-m-d");

									$imgarray =  array(
										'FileName' => $meufile,
										'DateTimeOriginal' => $DateTimeOriginal,
										'DateOriginal' => $dateoriginal,
										'TimeOriginal' => $timeoriginal,
										'Autores' => $fotografo,
										'HabitatPhoto' => 1);
								} else {
									$imgarray =  array(
										'FileName' => $meufile,
										'Autores' => $fotografo,
										'HabitatPhoto' => 1);
								}
								$qq = "SELECT * FROM Imagens WHERE FileName='".$meufile."' AND HabitatPhoto=1";
								$resul = mysql_query($qq,$conn);
								$nresul = mysql_numrows($resul);
								if ($nresul==0) { //se ja nao existe
									$newimg = InsertIntoTable($imgarray,'ImageID','Imagens',$conn);
									if ($newimg) {
										$copiado = copy($inputfile,"img/originais/".$meufile);
										if ($copiado) {
											unlink($inputfile);
										}
										$nar = array($newimg);
										$vvimgar = array_merge((array)$vvimgar,(array)$nar);
									}
								} 
							}
					}
				}
			} else { //se tem o registro, mas nao tem o arquivo da imagem, entao talvez e para apagar
				if ((count($valoresvelhos)>0 && $valoresvelhos[0]!='valoresvelhos')) {
					foreach ($valoresvelhos as $oldkey => $vvimg) {
						$string= "imgtodel_".$charid."_".$vvimg;
						$imgtodel  = eval("return $". $string . ";");
						if ($imgtodel==1) {
							$dataa = date("Y-m-d");
							$fieldsaskeyofvaluearray = array('Deleted' => $dataa);
							CreateorUpdateTableofChanges($vvimg,'ImageID','Imagens',$conn);
							UpdateTable($vvimg,$fieldsaskeyofvaluearray,'ImageID','Imagens',$conn);
						} else {
							$nar = array($vvimg);
							$vvimgar = array_merge((array)$vvimgar,(array)$nar);
						}
					}
				}
			}
				if (count($vvimgar)>0) {
					foreach ($vvimgar as $rv) {
						$ttr = trim($rv);
						if (!empty($ttr)) {
							$rvalores = array_merge((array)$rvalores,(array)$ttr);
							}
					}
					if (count($rvalores)>0) {
						$value = implode(";",$rvalores);
					} else {
						$value = ' ';
					}
				} else {
					$value = ' ';
				}
		}
		
		
		
		
		//////////////////////////////////////////////////////////////////////
		if ($varorunit=='traitunit' && !empty($value)) {
			$ttunidade = $value;
			$value ='';
			$fieldsaskeyofvaluearray = array(
				'TraitID' => $charid,
				'HabitatID' => $habitatid,
				'TraitUnit' => $ttunidade);
		} else {$ttunidade='';}
		
		if ($varorunit!='traitunit') {
			$fieldsaskeyofvaluearray = array(
			'TraitID' => $charid,
			'HabitatID' => $habitatid,
			'HabitatVariation' => $value);
			//echo $charid." ".$varorunit." ".$value."<br>";
		} 
		if ((!empty($value) || !empty($ttunidade))) {
			$qq = "SELECT * FROM Habitat_Variation WHERE TraitID='$charid' AND HabitatID='$habitatid'";
			
			$teste = @mysql_query($qq,$conn);
			$update = @mysql_numrows($teste);
			
			//faz o cadastro ou atualiza variacao
			if ($charid>0) {
			$tvaltrim = trim($value);
			if ($value!='none' && $value!='imagem') {
			if ((empty($update) || $update==0) && $erro==0 && empty($ttunidade) && !empty($tvaltrim)) {
				$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,'HabitatVariationID','Habitat_Variation',$conn);
				if (!$newtrait) {
					$erro++;
				}
			} elseif ($varorunit!='traitimgautor') {
				$rrr = @mysql_fetch_assoc($teste);
				$oldval = $rrr['HabitatVariation'];
				$tuni = $rrr['TraitUnit'];
				$oldid  = $rrr['HabitatVariationID'];
				
				if ($tuni!=$ttunidade || $oldval!=$value) {
					//echopre($fieldsaskeyofvaluearray);
					if (!empty($value)) {
						CreateorUpdateTableofChanges($oldid,'HabitatVariationID','Habitat_Variation',$conn);
					}
					$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,'HabitatVariationID','Habitat_Variation',$conn);
					if (!$newupdate) {
						$erro++;
						//echo "aqui sera".$erro;
						//echopre($fieldsaskeyofvaluearray);
					}
				}
			}
			}
		}
		}
	} //endfor each
	}
} //end if habitatname is empty
} //if habitattipo=local
	if ($erro>0) {
		echo "<table cellpadding='2' cellspacing=0 class='erro' width='50%' align='center'><tr>
		<td class='tdformnotes'>".GetLangVar('erro2')."</td></tr></table>";
	} else {
	////////////////////////////////
		$habitat = describehabitat($habitatid,$img=TRUE,true,$conn);		
			echo "<br>
			<table class='sucessosmall'  align='center' cellpadding='5'>
			<tr class='success' ><td >".GetLangVar('sucesso1')."</td></tr>			
			<tr ><td align='left'>$habitat</td></tr></table><br>";
	///////////////////////////////
	}	
} //end if option1=2



//get old values if editing
if (!empty($habitatid) && is_numeric($habitatid)) {
	$oldvals = getoriginalhabitat($habitatid,$conn);
	@extract($oldvals);
} 

$specieslist = strip_tags(describetaxacomposition($specieslistids,$conn,$includeheadings=TRUE));

echo "<br><table class='myformtable' align='center' cellpadding='4' align='center'>";

//echo "here: $habitatid $habitattipo $justselect";
if (!empty($justselect)) {
	$habitat = describehabitat($habitatid,$img=FALSE,true,$conn);
}

if (!isset($habitattipo) || empty($habitattipo)) {

echo "<form action='habitat-form.php' method='post'>
<thead>
	<tr >
		<td >".GetLangVar('namehabitat')."</td>	
		</tr>
</thead>
<tbody>
<tr>
	<td >
		<table align='left' cellpadding=\"3\" cellspacing=\"0\" class='tdformnotes'>
			<tr>
			<td >
			<select id='habitatid' name='habitatid' onchange='this.form.submit();'>";
			if (empty($habitatid)) {
				echo "<option value=''>".GetLangVar('nameselect')."</option>";
			} else {
				$qq = "SELECT * FROM Habitat WHERE HabitatID='$habitatid'";
				$wr = mysql_query($qq,$conn);
				$ww = mysql_fetch_assoc($wr);
				echo "<option  selected value='".$ww['HabitatID']."'>".$ww['Habitat']."</option>";
			}
		$nn = listhabitatnew($conn);
		$uuid="";
		$localnn="";
		while ($aa = mysql_fetch_assoc($nn)){
		//formularios usuario
			$uid = $aa['PathName'];
			$tipo = $aa['HabitatTipo'];
			$gazpath = $aa['GazPath'];
			$gaztipo =  $aa['GazTipo'];
			$gazter =  $aa['Gazetteer'];
			$localn = $gazpath;
			$level = $aa['MenuLevel'];
			if ($level==1) {
				$espaco='';
			} else {
				$espaco = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;',$level);
			}
			if ($uuid!=$uid && $tipo=='Class') {
				echo "<option class='optselectdowlight' value='".$aa['HabitatID']."'>".$espaco.$aa['Habitat']."</option>";		
			} else {
				if ($localnn!=localn) {
					//echo "<option value=''>$espaco---".$localn."---</option>";		
				}
				echo "<option";
				if ($tipo=='Class') { echo " class='optselectdowlight' ";}
				echo " value='".$aa['HabitatID']."'>".$espaco.$aa['Habitat'];
				if ($tipo!='Class' && !empty($localn)) { echo " (".$localn.")";}
				echo "</option>";		
			}
			echo "</option>";
			$uuid = $uid;
			$localnn = $localn;
		}
			echo  "</select></td>
			</tr>";
			if (!empty($justselect)) {
				echo "
				<input type='hidden' id='sendid' value=\"$habitat\">
				<script language=\"JavaScript\">
				setTimeout(
					function() {
						sendval_innerHTML('sendid','habitat');
						sendvalclosewin('habitatid','$habitatid');
					}
				,0.0001);
			</script>";	

		} 
		echo "</table>	
	</td>
	</tr>
</form>
	";
}

if ($justselect!='1' || empty($justselect)) {
echo "
<thead>
	<tr>
<td >";
if (isset($habitattipo) && $habitattipo=='Local') {
	echo " ".GetLangVar('habitatlocal')."&nbsp;&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('messageexplainnovotaxa');
	echo	" onclick=\"javascript:alert('$help');\">";
} elseif ($habitattipo=='Class') {
	echo " ".GetLangVar('habitatclasse');
}  else {
	echo GetLangVar('namenovo')." ".GetLangVar('namehabitat');
}
echo "</td></tr>
</thead>
<tbody>
";

if (!isset($habitattipo) || empty($habitattipo)) {

echo "
<tr>
<td >
	<form action='habitat-form.php' method='post'>
	<input type=hidden name='habitatid' value='$habitatid'>
	<input type=hidden name='formname' value='$formname'>
	<input type=hidden name='elementname' value='$elementname'>
	<table>
		<tr>
		<td class='tdformleft'>".GetLangVar('nameselect')."</td>
		<td class='tdformnotes'>
			<input type='radio'  name='habitattipo' value='Class' onchange='this.form.submit()'>&nbsp;&nbsp;".GetLangVar('habitatclasse');
			echo "&nbsp;&nbsp;
			<img height=13 src=\"icons/icon_question.gif\" ";
				$help = GetLangVar('habitatclasse_desc');
			echo	" onclick=\"javascript:alert('$help');\">
		</td>
		<td class='tdformnotes'>
			<input type='radio' name='habitattipo' value='Local' onchange='this.form.submit()'>&nbsp;&nbsp;".GetLangVar('habitatlocal');
			echo "&nbsp;&nbsp;<img height=13 src=\"icons/icon_question.gif\" ";
			$help = GetLangVar('habitatlocal_desc');
			echo	" onclick=\"javascript:alert('$help');\">";
echo "</td>
		</tr>
	</table>
	</form>
</td>
</tr>
";
} else {

//IF FORMULARIO E LINK SELECIONADOS
echo "
<tr>
<td  align='center' >
	<form id='varform2' method='POST' enctype='multipart/form-data' action='habitat-form.php' name='specieslistform'>
				<input type='hidden' name='formid' value='$formid'>
				<input type='hidden' name='option' value='2'>
				<input type=hidden name='habitatid' value='$habitatid'>
				<input type=hidden name='habitattipo' value='$habitattipo'>
				<input type=hidden name='formname' value='$formname'>
				<input type=hidden name='elementname' value='$elementname'>
<table>
	<tr>
	<td><table><tr>
		<td>
			<table>
				<tr>
					<td class='tdformleft'>".GetLangVar('namenome')."</td>
					<td><input type='text' name='habitatname' size=30 value='$habitatname'></td>
				</tr>
			</table>
		</td>
		<td>
			<table>
			<tr>
				<td class='tdformleft'>".GetLangVar('messagepertenceaclasse')."</td>
				<td>";	
				echo "<select name='parentid' value='$parentid'>";
				if (empty($parentid)) {
					echo "<option selected>".GetLangVar('nameselect')."</option>";
				} else {
					$qq = "SELECT * FROM Habitat WHERE HabitatID='$parentid'";
					$wr = mysql_query($qq,$conn);
					$ww = mysql_fetch_assoc($wr);
					echo "<option  selected value='".$ww['HabitatID']."'>".$ww['Habitat']."</option>";
				}
				$res = listhabitatnew($conn);
				while ($aa = mysql_fetch_assoc($res)){
					$PathName = $aa['PathName'];
					$level = $aa['MenuLevel'];
					$tipo = $aa['HabitatTipo'];
					if ($level==1) {
						$espaco='';
					} else {
						$espaco = str_repeat('&nbsp;&nbsp;&nbsp;',$level);
					}
					if ($tipo=='Class') {
					if ($level==1) {
						echo "<option class='optselectdowlight' value='".$aa['HabitatID']."'>$espaco<i>".$aa['Habitat']."</i></option>";
					} else {
						$espaco = $espaco.str_repeat('- ',$level-1);
						echo "<option value='".$aa['HabitatID']."'>$espaco".$aa['Habitat']."</option>";
					}
					}
			}
			echo "</select>
			</td>
			</tr>
		</table>
	</td></tr>
</table></td></tr>

";
if ($habitattipo=='Class') {

echo "</table></td></tr>
<tr>
<td>
<table>
	<tr>
		<td class='tdformleft'>".GetLangVar('namedefinicao')."</td>
		<td><textarea name='habitatdefinicao' cols='60%' rows=5>$habitatdefinicao</textarea></td>
	</tr>
</table>
</td>
</tr>";

} else {

echo "<tr>
<td colspan=100%><table><tr>
		<td class='tdsmallboldright'>".GetLangVar('namelocalidade')."&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = GetLangVar('localidadetipos');
		echo	" onclick=\"javascript:alert('$help');\"></td>
	<td>
	<table >
		<tr>
			<input type='hidden' id='gazetteerid'  name='gazetteerid' value='$gazetteerid'>
			<td colspan=3 id='locality' class='tdformnotes'>$locality</td>
		</tr>
		<tr><td>
			<table >
			<tr>
				<td align='center'>
					<input type=button value='".GetLangVar('nameselect')."' class='bsubmit' 
					onclick = \"javascript:small_window('localidade-popup.php?gaztag=gazetteerid&localtag=locality&gazetteerid=$gazetteerid',850,150,'LocalidadePopUp');\">
				</td>
				<td class='tdsmallboldright' align='center'>".strtolower(GetLangVar('nameor')." ".GetLangVar('nameselect'))."   ponto GPS</td>
				<td align='center'>
				<select name='gpspointid'>";
				if ($gpspointid>0) {
					$qqq = "SELECT * FROM GPS_DATA WHERE PointID='".$gpspointid."'";
					$rs = mysql_query($qqq,$conn);
					$rw = mysql_fetch_assoc($rs);
					echo "<option class='optselectdowlight' selected value=".$rw['PointID'].">".$rw['Name']."</option>";
				}  else {
					echo "<option  selected value=''>".GetLangVar('nameselect')."</option>";
				}
				echo "<option  value=''>---------</option>";
				//$qq = "SELECT * FROM GPS_DATA WHERE Type='Waypoint' Order by GPSName,DateOriginal,Name ASC";
				//$res = mysql_query($qq,$conn);

				$res =  listgpswaypoinds($municipioid,$provinciaid,$gazetteerid,$countryid,$conn);
				$pais = "";
				$provincia = "";
				$municipio = '';
				$gazter = '';
				$date = "";
				$space = "&nbsp;&nbsp;";
				while ($row = mysql_fetch_assoc($res)) {
					if ($pais!=$row['Country']) {
						$pais = $row['Country'];
						echo "<option class='optselectdowlight' value=''>".strtoupper($row['Country'])."</option>";
					}
					if ($provincia!=$row['Province']) {
						$provincia = $row['Province'];
						echo "<option class='optselectdowlight' value=''>".$space.$row['Province']."</option>";
					}
					if ($municipio!=$row['Municipio']) {
						$municipio = $row['Municipio'];
						echo "<option class='optselectdowlight' value=''>".$space.$space.$row['Municipio']."</option>";
					}
					if ($gazter!=$row['Gazetteer']) {
						$PathName = $row['PathName'];
						$level = $row['MenuLevel'];
						$gaztipo = $row['GazTipo'];
						$espaco = $space.$space.$space.str_repeat($space,$level);
						
						echo "<option class='redtext' value=''>$espaco".$gaztipo." ".$row['Gazetteer']."</option>";
						$gazter = $row['Gazetteer'];
					}
					$spc = $space.$espaco."--";
					$date = $row['DateOriginal'];
					echo "<option value=".$row['PointID'].">".$spc.$row['Name']." (".$date.")</option>";
				}
				echo "</select>";
				echo "</td>
			</tr>
			</table>
		</td></tr>
	</table>
</td></tr></table></td>

<tr><td colspan=2>
<table class='sortable autostripe' cellspacing='0' cellpadding='3' align='center' width='100%'>
<thead >
<tr>
<th align='center'>".GetLangVar('nametraits')."</th>
<th align='center'>".GetLangVar('namevariacao')."</th>
</tr>
</thead>
<tbody>
";
//pega todas as variaveis do formulario 
		$qq = "SELECT * FROM Formularios WHERE FormName='Habitat'";
		$rr = mysql_query($qq);
		$row= mysql_fetch_assoc($rr);
		$fieldids = explode(";",$row['FormFieldsIDS']);
		$qq = "SELECT * FROM Traits WHERE ";
		$i=0;
		foreach ($fieldids as $key => $value) {
				if ($i==0) {
					$qq = $qq." TraitID='".$value."'";
				} else {
					$qq = $qq." OR TraitID='".$value."'";
				}
				$i++;
		}
		$qq = $qq." ORDER BY PathName";
		$rr = mysql_query($qq);
		$nvar = mysql_numrows($rr);

		while ($row= mysql_fetch_assoc($rr)) { //para cada variavel no relatorio
				$zz = explode("-",$row['PathName']);
				$trclass = trim($zz[0]);				
			    echo "<tr>
				<td ><table class='clean'><tr class='cl'>
								<td class='cl'>".$row['TraitName']."</td>
								<td class='cl' align='left'><img height=12 src=\"icons/icon_question.gif\" ";
									$help = $row['TraitDefinicao'];
									echo	" onclick=\"javascript:alert('$help');\">
								</td>
							</tr>
					</table>
				</td>
				<td><table class='clean'>
				";
				//se categoria
				
				if ($row['TraitTipo']=='Variavel|Categoria') {
					//opcoes de variaves categoricas
					echo "<tr class='cl'>";
					if ($row['MultiSelect']!='Sim') {
						$tname = "traitvar_".$row['TraitID'];
						$val = eval('return $'.$tname.';');			
						$val = trim($val);
						if (empty($val) || $val=='none') {
								echo "<td class='cl'><input type='radio' checked name='traitvar_".$row['TraitID']."' value='none'>
								".GetLangVar('messagenoneopt')."</td>";
						} else {
								echo "<td class='cl'><input type='radio' name='traitvar_".$row['TraitID']."' value='none'>
								".GetLangVar('messagenoneopt')."</td>";
						}					
					} else {
						$tname = "traitvar_".$row['TraitID'];
						echo "<input type='hidden' name=$tname value=' '>";
					}

					echo "<td class='cl'>";

					$qq = "SELECT * FROM Traits WHERE ParentID='".$row['TraitID']."' ORDER BY TraitName";					
					$res = mysql_query($qq,$conn);
					$nres = mysql_numrows($res);					
					
					echo "
						<table class='clean'>"; 
					$cr = 0;
					while ($rw= mysql_fetch_assoc($res)) { //para cada estado de variacao
						if ($row['MultiSelect']=='Sim') {
								$typein = 'checkbox';
								$tname = "traitmulti_".$row['TraitID']."_".$rw['TraitID'];
								$valor = eval('return $'.$tname.';');
								
						} else {
								$typein='radio';
								$tname = "traitvar_".$row['TraitID'];
								$valor = eval('return $'.$tname.';');
						}
						
						if ($cr % 6 == 0 || $cr==0) {
							echo "<tr class='cl'>";
					    }
						//$val = trim($val);
						$tid = $rw['TraitID'];
					    echo "<td class='cl'>
						    <table class='clean'> 
							<tr class='cl'>
							<td class='cl' align='right'><input type='".$typein."' name='$tname' ";
							if ($valor==$rw['TraitID']) {echo "checked ";}
							echo "value='".$rw['TraitID']."' ></td>
							<td class='cl' align='left'>".$rw['TraitName']."</td>
							<td class='cl' align='left'><img height=12 src=\"icons/icon_question.gif\" ";
							$help = $rw['TraitDefinicao'];
							echo	" onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;&nbsp;
							</td>
							</tr>
							</table> 
							</td>";
						$cr++;
						if ($cr % 6 == 0 || $cr==$nres) {
							echo "</tr>";
					    }
					} 
					echo "</table> 
					</td></tr>";
				}
				
				
				//se quantitativo
				if ($row['TraitTipo']=='Variavel|Quantitativo') {
					$string = 'traitvar_'.$row['TraitID'];
					if (!isset($_POST[$string])) {
						$val = eval('return $'. $string . ';');
					} else {
						$val= $_POST[$string];
					}
					echo "<tr class='cl'><td class='cl'>
							<input name='traitvar_".$row['TraitID']."' value='$val'>";
						echo "</td>
						<td class='cl'>
							<select name='traitunit_".$row['TraitID']."'>";
						$string = 'traitunit_'.$row['TraitID'];
						$val = eval('return $'. $string . ';');
						if (empty($val) && !empty($row['TraitUnit'])) {
								echo "<option selected value='".$row['TraitUnit']."'>".$row['TraitUnit']."</option>";
						} elseif (!empty($val)) {
								echo "<option selected value='".$val."'>".$val."</option>";
						}
						$qq = "SELECT * FROM db_users.VarLang WHERE VariableName LIKE '%traitunit%' ORDER BY '$lang' ASC";
						$res = mysql_query($qq,$conn);
						if ($res) {
						while ($rwu=mysql_fetch_assoc($res)) {
							$varname = $rwu['VariableName'];
							$zz = explode("_",$varname);
							if ($zz[1]!='desc') {
								$subsname = 'traitunit'.$menugrp;
								echo "<option value='".GetLangVar($varname)."'>".GetLangVar($varname)."</option>";
							}
						}
						}
					echo "</select>
					</td></tr>";
				}
				
				
				//se imagem
				if ($row['TraitTipo']=='Variavel|Imagem') {					
					$string = 'trait_'.$row['TraitID'];
					$imgfile = 'traitimg_'.$row['TraitID'];
					$val = explode(";",eval('return $'. $string . ';'));
					
					$oldimgvals = eval('return $'. $string . ';');

					if (count($val)>0) {
							echo "<input type=hidden name ='traitimgold_".$row['TraitID']."' value='".$oldimgvals."'>";
							foreach ($val as $kk => $vv) {
								$vv = trim($vv);
								if (!empty($vv)) {
								$qq = "SELECT * FROM Imagens WHERE ImageID='$vv'";
								$rt = mysql_query($qq,$conn);
								$rtw = mysql_fetch_assoc($rt);
								$path = "img/originais/";
								$imagid = $rtw['ImageID'];
								$filename = trim($rtw['FileName']);
								
								$autor = $rtw['Autores'];
								//echo 'fotografo  2 = '.$autor;
								$autorarr = explode(";",$autor);
								if (count($autorarr)>0) {
									$j=1;
									foreach ($autorarr as $aut) {
										$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$aut."'";
											$res = mysql_query($qq,$conn);
											$rwr = mysql_fetch_assoc($res);
										if ($j==1) {
											$autotxt = 	$rwr['Abreviacao'];
										} else {
											$autotxt = $autotxt."; ".$rwr['Abreviacao'];
										}
										$j++;
									}
								} 
								//echo '<br>fotografo  3 = '.$autotxt."<br>";

								$fotodata = $rtw['DateOriginal'];
								
								
								
								if (file_exists($path.$filename)) {
										$pthumb = "img/thumbnails/";
										//echo $path.$fn;
										if (!file_exists($pthumb.$filename)) {
											createthumb($path.$filename,$pthumb.$filename,80,80);
										}
										$imgbres = "img/copias_baixa_resolucao/";	
										if (!file_exists($imgbres.$filename)) {
											$zz = getimagesize($path.$filename);
											$width=$zz[0];
											$height = $zz[1];
											if ($width>1200 || $height>1200) {
												createthumb($path.$filename,$imgbres.$filename,1200,1200);
											} else {
												createthumb($path.$filename,$imgbres.$filename,$width,$height);
											}
										}
									
									$fn = explode("_",$filename);
									unset($fn[0]);
									unset($fn[1]);
									$fn = implode("_",$fn);
									
									
									$fntxt = $fn."   [";
									if (!empty($autotxt)) { $fntxt = $fntxt." ".GetLangVar('namefotografo').": ".$autotxt." - ".$fotodata."]";} else {
										$fntxt = $fntxt.$fotodata."]";
									}
									
									echo "<tr class='cl'>
									<td class='cl' colspan=2><table class='clean'>
									<tr class='cl' >
									<td class='cl' >
									<a href=\"".$imgbres.$filename."\" class='MagicZoomPlus'  rel=\"zoom-position:right;zoom-height:200px; zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" >
									<img width=\"40\" src=\"".$pthumb.$filename."\"/>
									</a></td><td class='cl' >&nbsp;</td>
									<td class='tinny' id='fname_".$row['TraitID']."_".$imagid."'  class='tdformnotes'>$fntxt</td>";
									$fndeleted = "<STRIKE>$fn</STRIKE>";
									echo "<input type='hidden' id='fnamedeleted_".$row['TraitID']."_".$imagid."' value='$fndeleted'>";
									echo "<input type='hidden' id='imgtodel_".$row['TraitID']."_".$imagid."' name='imgtodel_".$row['TraitID']."_".$imagid."' value=''>";
									echo "<input type='hidden' id='imagid_".$row['TraitID']."_".$imagid."' name='imagid_".$row['TraitID']."_".$imagid."' value='$imagid'>";
									echo "<input type='hidden' id='fnameundeleted".$row['TraitID']."_".$imagid."' value='$fn'>";

									echo "<td class='cl' ><img height=14 src=\"icons/application-exit.png\"";
									echo	" onclick=\"javascript:deletimage('fnamedeleted_".$row['TraitID']."_".$imagid."','fname_".$row['TraitID']."_".$imagid."','imgtodel_".$row['TraitID']."_".$imagid."',1);\">
									</td>
									<td class='cl' ><img height=14 src=\"icons/list-add.png\"";
									echo	" onclick=\"javascript:deletimage('fnameundeleted".$row['TraitID']."_".$imagid."','fname_".$row['TraitID']."_".$imagid."','imgtodel_".$row['TraitID']."_".$imagid."',0);\">
									</td>
									</tr>
									</table>
									</td></tr>";
								} else {
									$refname = 'traitimg_'.$row['TraitID'];
									$val = eval('unset($'.$refname.');');
								}
								}
								
							}
					}
					echo	"<tr class='cl'>
							<td class='cl'>";
								$varname = 'trait_'.$row['TraitID'];
								echo "<input type=\"file\" name=\"$varname\">
											<script type=\"text/javascript\">
												window.addEvent('domready', function(){
												new MultiUpload($( 'varform2' ).$varname );});
											</script>
								<input type=hidden name='traitimg_".$row['TraitID']."' value='imagem'>		
							</td>
							<td class='cl'>
							<select name='traitimgautor_".$row['TraitID']."'>";
								echo "<option value=''>".GetLangVar('namefotografo')."</option>";
								echo "<option value=''>----</option>";
								$wrr = getpessoa('',$abb=TRUE,$conn);
								while ($aa = mysql_fetch_assoc($wrr)){
									echo "<option value='".$aa['PessoaID']."'>".$aa['Abreviacao']."</option>";
								}
							echo "</select>
							</td>					
							</tr>";
				}
				
				//se texto
				if ($row['TraitTipo']=='Variavel|Texto') {
					echo "<input type=hidden name='traitnone_".$row['TraitID']."' value='none'>";
					$string = 'traitvar_'.$row['TraitID'];
					if (!isset($_POST[$string])) {
						$val = eval('return $'. $string . ';');
					} else {
						$val= $_POST[$string];
					}
					//tem um problema aqui quando apaga os dados
					echo "<tr class='cl'><td class='cl'><textarea name='traitvar_".$row['TraitID']."' cols='80' rows='2' >".$val."</textarea></td></tr>";
				}

				echo "</table> 
				</td></tr>";
		}//end of loop de cada variavel relatorio				

echo "</tbody>
</table> 
</td></tr>
<tr>
<td colspan=2 class='tabsubhead' >".GetLangVar('habitatoutrostaxa')."</td></tr>
<tr>
<td colspan=2 >
<table align='left' width='100%' class=clena><tr>
	<input type='hidden' name='specieslistids' value='$specieslistids'>
	<td class='tdsmalldescription'>
		<textarea cols=90 rows=2 name='specieslist' readonly>$specieslist</textarea>
	</td>
	<td align='left'>
		<input type='button' value='".GetLangVar('nameselect')."' class='bsubmit' ";
		$myurl ="selectspeciespopup.php?formname=specieslistform&elementname=specieslistids&destlistlist=".$specieslistids;
		echo "	onclick = \"javascript:small_window('$myurl',500,400,'SelectSpecies');\">
	</td>
</tr>
</table></td></tr>
</table> 

</td></tr>"; //fech tabela para conteudo do formulario

} //end if habitat is not class

echo "<tr><td >
				<table align='center'>
				<tr>
				<td align='center' >
					<input type=submit value='".GetLangVar('namesalvar')."' class='bsubmit'>
				</td>
			</form>
				<form action=habitat-form.php method='post'>
					<input type=hidden name='formname' value='$formname'>
					<input type=hidden name='elementname' value='$elementname'>
					<input type=hidden name='habitatid' value='$habitatid'>
					<input type=hidden name='habitattipo' value='$habitattipo'>

				<td align='left'><input type='submit' value='".GetLangVar('namereset')."' class='breset'></td>
				</form>	
			</tr>
		</table></td>
	</tr>";
	if ($habitattipo!='Class') {
	 echo "<tr><td class='tdformnotes'><b>".GetLangVar('nameobs')."</b>: ".GetLangVar('messagemultiplevalues')."</td></tr>";	
	}	
} 

}
echo "</tbody></table>"; //fecha tabela do formulario
HTMLtrailers();


?>

