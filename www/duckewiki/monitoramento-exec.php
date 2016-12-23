<?php
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
include_once("functions/class.Numerical.php") ;
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

$arval=$_POST;

if ($option1=='2') {
	unset($arval['MAX_FILE_SIZE'],  $arval['formid' ],  $arval['option1'],  $arval['plantaid'], $arval['dataobs']);
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


$erro=0;
if (empty($dataobs)) {
	echo "<br>
		<table cellpadding=\"1\" width='50%' align='center' class='erro'>
		  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
	echo "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('messagedatadaobs')."</td></tr>";
	echo "<form action=monitoramento-form.php method='post'>";
				@hiddeninputs($oldvals);	//hidden values for most variables
				
	echo "		<input type='hidden' name='option1' value='1'>
			<tr><td align='center'><input type='submit' value='".GetLangVar('namevoltar')."' class='breset'></td></tr>
		</form>";
	echo "</table>";
	$erro++;
}


if ($option1=='2' && $erro==0) {
	//echopre($_FILES);
	
	$arraryofvalue = $arval;
	$erro =0;
	foreach ($arraryofvalue as $key => $value) {
		$ttunidade = NULL;
		$arraykey = explode("_",$key);
		//print_r($arraykey);
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		$update = 0;
		$vv = trim($value);		
		if (empty($vv)) {$value=' ';}

	
	
	if ($varorunit!='traitimgautor' && $varorunit!='imagid' && $traitimgold!= 'traitimgold' && $varorunit!='imgtodel' && $charid>0 && $charid!='FILE'
	) {
		if ($varorunit=='traitvar' && !empty($value)) {
			if (is_array($value)) {
				$value = implode(";",$value);
			} else {
				$value = trim($value);
			}
		}
		if ($varorunit=='traitimg' && $value=='imagem') {
			$string = 'traitimgold_'.$charid;
			$valoresvelhos  = explode(";",eval('return $'. $string . ';'));
			//check to see if image exists and if so transfer it to the proper place
			$myfile = $_FILES['trait_'.$charid.'_0']['name'];
			//echo $myfile." AQUI MYFILE<br>";
			$rvals= array();
			if ($myfile) {
				$fotoautor = $arraryofvalue["traitimgautor_".$charid];
				if (is_array($fotoautor) && count($fotoautor)>0) {
					$fotografos = implode(";",$fotoautor);
				} elseif (!is_array($fotoautor)) {
					$fotografos = trim($fotoautor);					
				}					foreach ($_FILES as $key => $val) {
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
									'Autores' => $fotografos);
							} else {
								$imgarray =  array(
									'FileName' => $meufile,
									'Autores' => $fotografos);
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
									}
									if (empty($rvals)) {
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
			if ($valoresvelhos[0]=='traitimgold') { unset($valoresvelhos);}
			if (count($rvals)>0 || count($valoresvelhos)>0) {
				$rvals = array_merge((array)$rvals,(array)$valoresvelhos);
				$rvals = array_unique($rvals);
				$value = implode(";",$rvals);
			} else {
				$value = ' ';
			}
		}
		
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
			if (!empty($plantaid)) {
					$fieldsaskeyofvaluearray = array('PlantaID' => $plantaid);
					$qq = "SELECT * FROM Monitoramento WHERE TraitID='$charid' AND PlantaID='$plantaid' AND DataObs='$dataobs'";
					$teste = mysql_query($qq,$conn);
					$update = @mysql_numrows($teste);
			} 
			$zz= 	array('TraitID' => $charid, 'TraitVariation' => $value, 'TraitUnit' => $ttunidade);
			$fieldsaskeyofvaluearray = array_merge((array)$fieldsaskeyofvaluearray,(array)$zz);
		}
	
		//echo "VARORUNIT: ".$varorunit." CHARID".$charid;
		//echopre($fieldsaskeyofvaluearray);
		//faz o cadastro o variacao
		$vv = trim($value);
		if (!empty($vv) && $vv!='none' && $update==0 && $varorunit!='traitmulti' && $varorunit!='traitimgdel') {
			$zz= 	array('DataObs' => $dataobs);
			$fieldsaskeyofvaluearray = array_merge((array)$fieldsaskeyofvaluearray,(array)$zz);		
			$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,'MonitoramentoID','Monitoramento',$conn);
			if (!$newtrait) {
				echo "foi aqui";
				$err++;
			}
			//echo "/width date";
			//echopre($fieldsaskeyofvaluearray);

		}
		
		//atualiza o cadastro
		if ($update>0 && $varorunit!='traitmulti' && $value!='none') {	
			$rrr = @mysql_fetch_assoc($teste);
			$oldval = trim($rrr['TraitVariation']);			
			$oldid  = $rrr['MonitoramentoID'];			
			if ($value!=$oldval || $varorunit=='traitunit') { //update if newvalue is different from old value
				CreateorUpdateTableofChanges($oldid,'MonitoramentoID','Monitoramento',$conn);
				$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,'MonitoramentoID','Monitoramento',$conn);
				if (!$newupdate) {
					$err++;
					}
				
			}
		}
	}

} //end for each  variable

if ($err>0) {
	echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
		  <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro2')."</td></tr>";
	echo "<form action=monitoramento-form.php method='post'>";
			@hiddeninputs($oldvals);
	echo "<input type='hidden' name='option1' value='1'>
		<tr><td align='center'><input type='submit' value='".GetLangVar('namevoltar')."' class='breset'></td></tr>
		</form>";
} else {
	echo "<br><table width=30% align='center' class='sucessosmall'><tr><td>".GetLangVar('sucesso1')."</td></tr>
			<form action=monitoramento-form.php method='post'>
				<input type='hidden' name='formid' value='$formid'>
				<tr><td align='center'><input type='submit' value='".GetLangVar('nameconcluir')."' class='bsubmit'></td></tr>
				</form>	
		</table>";


	}
} //end if option1=2

HTMLtrailers();

?>

