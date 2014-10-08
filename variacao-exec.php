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

//$arval=$_POST;

//echopre($_POST);

if ($option1=='2') {
	unset($arval['MAX_FILE_SIZE'],  $arval['formid' ],  $arval['option1'],  $arval['especimenid'],  $arval['plantaid'],  $arval['infraspid' ],  $arval['famid'],  $arval['genusid'],  $arval['speciesid'],
	$arval['final']);
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
				//echo "<br>aqui $nno e $value<br>";
				$result = array_merge((array)$result,(array)$nar);
			}
		}
	}

$arval = array_merge((array)$arval,(array)$result);
unset($arval['linkto']);
unset($arval['dataobs']);
unset($arval['plantasarray']);
unset($arval['ffrom']);
unset($arval['plto']);

$arraryofvalue = $arval;
$erro =0;
$newimagefile = array();
foreach ($arraryofvalue as $key => $value) {
	
	$arraykey = explode("_",$key);
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		$update = 0;
		$vv = trim($value);
	
	
		
	if ($varorunit!='plantaid' && $varorunit!='linkto' && $varorunit!='traitimgautor' && $varorunit!='imagid' && $varorunit!= 'traitimgold' && $varorunit!='imgtodel' && $varorunit!='traitimgautortxt') {
	//se vazio coloca um valor em branco para apagar registros
		if (empty($vv)) {$value=' ';}
		
		//echo $key." => ".$value."<br>";

		//echo $value." ".$varorunit." ";
		//pega os valores quando n„o for vazio
		if ($varorunit=='traitvar' && !empty($value) && $value!=' ') {
			if (is_array($value)) {
				$value = implode(";",$value);
			} 
		}
		
		if ($varorunit=='traitimg' && $value=='imagem') {
			$string = 'traitimgold_'.$charid;
			$valoresvelhos  = explode(";",eval('return $'. $string . ';'));

			//check to see if image exists and if so transfer it to the proper place
			$myfile = $_FILES['trait_'.$charid.'_0']['name'];
			$rvals= array();
			if ($myfile) {
				$fotografo = $arraryofvalue["traitimgautor_".$charid];

				//echo "<pre>".print_r($_FILES)."</pre>";
					foreach ($_FILES as $key => $val) {
						$cid = explode("_",$key);
						if ($cid[1]==$charid) {
							$basename = $_FILES[$key]['name'];
							if (!empty($basename)) {
								$filedate = date("Y-m-d");			
								$meufile = $filedate."_charid".$charid."_".$basename;
								move_uploaded_file($_FILES[$key]["tmp_name"],"img/temp/$meufile");
							}
							$ext = explode(".",$basename);
							$ll = count($ext)-1;
							$imgext = strtoupper($ext[$ll]);
							if ($imgext=='JPG' || $imgext=='TIFF' || $imgext=='TIF' || $imgext=='JPEG') {
								//echo "<br>Extens„o = ".$imgext."<br>";
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
									'Autores' => $fotografo);
							} else {
								$imgarray =  array(
									'FileName' => $meufile,
									'Autores' => $fotografo);
							}
							$qq = "SELECT * FROM Imagens WHERE FileName='".$meufile."'";
							$resul = mysql_query($qq,$conn);
							$nresul = mysql_numrows($resul);
							if ($nresul==0) { //se ja nao existe
								$newimg = InsertIntoTable($imgarray,'ImageID','Imagens',$conn);
								if ($newimg) {
									$copiado = copy($inputfile,"img/originais/".$meufile);
									if ($copiado) {
										unlink($inputfile);
										$newimagefile[] = $meufile;
									}
									if ((count($rvals)==1 && empty($rvals[0])) || empty($rvals)) {
										$rvals[0] = $newimg;
									} else {
										$rvals = array_merge((array)$rvals,(array)$newimg);
									}
								}
							}
					}
				}
			} else { //se tem o registro, mas nao tem o arquivo da imagem, entao talvez e para apagar
				
				if (count($valoresvelhos)>0 && $valoresvelhos[0]!='valoresvelhos') {
					foreach ($valoresvelhos as $vvimg) {
						$imgtodel = $arraryofvalue["imgtodel_".$charid."_".$vvimg];		
						if ($imgtodel==1) {
							$imgid = $arraryofvalue["imagid_".$charid."_".$vvimg];
							$key = array_search($imgid, $valoresvelhos);
							unset($valoresvelhos[$key]);
							$dataa = date("Y-m-d");
							$fieldsaskeyofvaluearray = array('Deleted' => $dataa);
							CreateorUpdateTableofChanges($imgid,'ImageID','Imagens',$conn);
							UpdateTable($imgid,$fieldsaskeyofvaluearray,'ImageID','Imagens',$conn);
						}
					}
				}
			
			
			}
			//echopre($valoresvelhos);
			if ($valoresvelhos[0]=='traitimgold') { unset($valoresvelhos);}
			if (count($rvals)>0 || count($valoresvelhos)>0) {
				$rvals = array_merge((array)$rvals,(array)$valoresvelhos);
				$rvals = array_unique($rvals);
				$value = implode(";",$rvals);
			} else {
				$value = ' ';
			}
			//echo "aqui ".$charid." ".$especimenid." ".$value;
			//echo "aqui o valor final".$value."<br>";
		}
		
	
	
		//quantitativos
		if ($varorunit=='traitunit' && !empty($value)) {
			$ttunidade = $value;
			$tt = $arraryofvalue['traitvar_'.$charid];
			if ($tt) {
					$value = $arraryofvalue['traitvar_'.$charid];
			} else {
					$value = ' ';
			}
		} 
		
		if ($varorunit=='traitnone' && $value=='none') {
				$tt = $arraryofvalue['traitvar_'.$charid];
				if ($tt) {
					$value = $arraryofvalue['traitvar_'.$charid];
				} else {
					$value = ' ';
				}
		}
		

		if (!empty($value) && $varorunit!='traitmulti') {

		if (!empty($especimenid)) {
			$fieldsaskeyofvaluearray = array('EspecimenID' => $especimenid);
			$qq = "SELECT * FROM Traits_variation WHERE TraitID='$charid' AND EspecimenID='$especimenid'";
			$teste = mysql_query($qq,$conn);
			$update = @mysql_numrows($teste);
		} else {
			if (!empty($plantaid)) {
				$fieldsaskeyofvaluearray = array('PlantaID' => $plantaid);
				$qq = "SELECT * FROM Traits_variation WHERE TraitID='$charid' AND PlantaID='$plantaid'";
				$teste = mysql_query($qq,$conn);	
				$update = @mysql_numrows($teste);
			} else {
				if (!empty($infraspid)) {
					$fieldsaskeyofvaluearray = array('InfraEspecieID' => $infraspid);
					$qq = "SELECT * FROM Traits_variation WHERE TraitID='$charid' AND InfraEspecieID='$infraspid'";
					$teste = mysql_query($qq,$conn);
					$update = @mysql_numrows($teste);
				} else {
					if (!empty($speciesid)) {
						$fieldsaskeyofvaluearray = array('EspecieID' => $speciesid);
						$qq = "SELECT * FROM Traits_variation WHERE TraitID='$charid' AND EspecieID='$speciesid'";
						$teste = mysql_query($qq,$conn);
						$update = @mysql_numrows($teste);
					} else {
						if (!empty($genusid)) {
						$fieldsaskeyofvaluearray = array('GeneroID' => $genusid);
							$qq = "SELECT * FROM Traits_variation WHERE TraitID='$charid' AND GeneroID='$genusid'";
							$teste = mysql_query($qq,$conn);
							$update = @mysql_numrows($teste);
						} else {
							$fieldsaskeyofvaluearray = array('FamiliaID' => $famid);
							$qq = "SELECT * FROM Traits_variation WHERE TraitID='$charid' AND FamiliaID='$famid'";
							$teste = mysql_query($qq,$conn);
							$update = @mysql_numrows($teste);
						}
					}		
				}
			}
		}
			if ($value=='none') {$value=' ';}
		
			$zz= 	array('TraitID' => $charid, 'TraitVariation' => $value, 'TraitUnit' => $ttunidade);
			$fieldsaskeyofvaluearray = array_merge((array)$fieldsaskeyofvaluearray,(array)$zz);
		}

	//faz o cadastro ou atualiza variacao
	$vv = trim($value);
	if (!empty($vv) && $update==0 && $varorunit!='traitmulti' && $varorunit!='traitimgdel') {
			$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,'TraitVariationID','Traits_variation',$conn);
			if (!$newtrait) {
				$erro++;
			}
	}

		if ($update>0 && $varorunit!='traitmulti') {	
			$rrr = @mysql_fetch_assoc($teste);
			$oldval = trim($rrr['TraitVariation']);
			$oldid  = $rrr['TraitVariationID'];
			$tv = trim($value);
			if (empty($oldval) && empty($tv)) { $need=FALSE;} else { $need=TRUE;}
			if (empty($charid) || $charid==0) { $need=FALSE;}
			if ($need && ($value!=$oldval || $varorunit=='traitunit')) { //update if newvalue is different from old value
				CreateorUpdateTableofChanges($oldid,'TraitVariationID','Traits_variation',$conn);
				//echo "<br>aqui".$value." ".$oldval." ".$need;
				//echo "<br>should update";
				//echopre($fieldsaskeyofvaluearray);
				$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,'TraitVariationID','Traits_variation',$conn);
				if (!$newupdate) {
					$erro++;
					}
				
			}
	 	}
	
		} //end if varorunit has some specific values
	} //end for each  variable

} 

if (count($newimagefile)>0) {
		$_SESSION['newimagfiles'] = serialize($newimagefile);
		$_SESSION['othervars'] = array(
		'especimenid' => $_POST['especimenid'],
		'infraspid' => $_POST['infraspid'],
		'speciesid' => $_POST['speciesid'],
		'genusid' => $_POST['genusid'],
		'plantaid' => $_POST['plantaid'],
		'linkto' => $_POST['linkto'],
		'formid' => $_POST['formid']);
		
		$zz = explode("/",$_SERVER['SCRIPT_NAME']);
		$serv = $_SERVER['SERVER_NAME'];
		$returnto = $serv."/".$zz[1]."/variacao-exec.php";

		header("location: http://".$serv."/cgi-local/imagick_function.php?returnto=".$returnto."&folder=".$zz[1]."&returnvar=imgdone");
} else {
	if (!isset($imgdone)) {
		unset($_SESSION['othervars']);
		unset($_SESSION['newimagfiles']);
		$imgdone=1;
	}
}
	
if ($imgdone>0 ) {
	HTMLheaders('');

	if ($erro>0) {
		echo "<p>houve um erro no cadastro</p>";
	} else {
		if ($imgdone>1) {
			@extract($_SESSION['othervars']);
		}
		if ($imgdone==3) {
			echo "<br><p class='erro'>Não foi possível gerar thumbnails para as imagens</p>";
		}
		echo "<br><table width=30% align='center' class='sucessosmall'><tr><td>".GetLangVar('sucesso1')."</td></tr>
			<form action=variacao-form.php method='post'>
				<input type='hidden' name='famid' value='$famid'>
				<input type='hidden' name='genusid' value='$genusid'>
				<input type='hidden' name='formid' value='$formid'>
				<input type='hidden' name='speciesid' value='$speciesid'>
				<input type='hidden' name='infraspid' value='$infraspid'>
				<input type='hidden' name='especimenid' value='$especimenid'>
				<input type='hidden' name='plantaid' value='$plantaid'>
				<input type='hidden' name='linkto' value='$linkto'>
				<input type='hidden' name='option1' value='1'>
				
				<tr><td align='center'><input type='submit' value='".GetLangVar('nameconcluir')."' class='bsubmit'></td></tr>
				</form>	
		</table>";


		}
	}

HTMLtrailers();

?>

