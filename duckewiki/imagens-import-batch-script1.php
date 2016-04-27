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

//GET POST
$ppost = $_SESSION['imgpost'];
@extract($ppost);
//echopre($_SESSION['imgpost']);

unset($_SESSION['quaisarquivos']);

function limpaimgnome($string) { 
	$theval = RemoveAcentos($string);
	$theval = str_replace("(","",$theval);
	$theval = str_replace(")","",$theval);
	$theval = str_replace(",","",$theval);
	$theval = str_replace("  ","",$theval);
	return($theval);
}

//echopre($ppost);
/*DEFINE PASTA TEMPORARIA ONDE SAO ARMAZENADOS OS ARQUIVOS DURANTE A IMPORTACAO (HA REFERENCIA A ESTA PASTA EM imagesupload-doit.php)*/
$tbn = "uploadDir_". $_SESSION['userid'];
$dir = "uploads/batch_images/".$tbn;



//CRIE A TABELA IMAGENS SE NAO EXISTIR
$qq = "CREATE TABLE IF NOT EXISTS Imagens ( ImageID INT(10) unsigned NOT NULL auto_increment, FileName VARCHAR(100), DateTimeOriginal VARCHAR(100), DateOriginal DATE, TimeOriginal TIME, Latitude VARCHAR(10), Longitude VARCHAR(10), Altitude VARCHAR(10), GPSMapDatum VARCHAR(10), Autores VARCHAR(20), Camera INT(10), GPSPointID INT(10), Deleted DATE, HabitatPhoto INT(1), AddedBy INT(10), AddedDate DATE, PRIMARY KEY (ImageID)) CHARACTER SET utf8 ENGINE INNODB";
@mysql_query($qq,$conn);
$qq = "ALTER TABLE Imagens ADD COLUMN TraitID INT(10)";
@mysql_query($qq,$conn);


//PEGA O NOME DAS IMAGENS
$imgs_nomes = scandir($dir);
unset($imgs_nomes[0]);
unset($imgs_nomes[1]);
$imgs_nomes = array_values($imgs_nomes);

//CRIA UMA TABELA E INSERE ESSES VALORES
$qd = "DROP TABLE temp_imgimport_".$uuid;
@mysql_query($qd,$conn);

$qq = "CREATE TABLE IF NOT EXISTS temp_imgimport_".$uuid."  ( TempImageID INT(10) unsigned NOT NULL auto_increment, FileName VARCHAR(100), EspecimenID INT(10), PlantaID INT(10),  PRIMARY KEY (TempImageID)) CHARACTER SET utf8 ENGINE INNODB";
@mysql_query($qq,$conn);
$erro=0;
$ii=1;
$nimg = count($imgs_nomes);
foreach ($imgs_nomes as $imgnome) {
	$qu = "INSERT INTO temp_imgimport_".$uuid." (FileName, EspecimenID,PlantaID) VALUES ('".$imgnome."', getspec_imgname('".$imgnome."','".$fnpattern_sep."',".($fnpattern+0)."),getplanta_imgname('".$imgnome."','".$fnpattern_seppl."',".($fnpattern_pl+0).",".($filtro+0).") )";
	//echo $qu."<br >";
	$ru = mysql_query($qu,$conn);
		if (!$ru) {
			$erro++;
		}
	$perc = round((($ii/$nimg)/2)*100);
	$qnu = "UPDATE `temp_imgprogress".$uuid ."` SET percentage=".$perc; 
	mysql_query($qnu,$conn);
	session_write_close();
}
$qnu = "UPDATE `temp_imgprogress".$uuid ."` SET percentage=50"; 
mysql_query($qnu,$conn);
session_write_close();

////////IMPORTA AS IMAGENS
//PARA CADA IMAGEM IMPORTA PARA A BASE E RELACIONA, SE FOR O CASO
$ii = 1;
$qu = "SELECT * FROM temp_imgimport_".$uuid;
if ($linkposterior!=1) {
	$qu .= " WHERE EspecimenID>0 OR PlantaID>0";
}
//echo $qu."<br />";
$rn = mysql_query($qu, $conn);
$nii = mysql_numrows($rn);
$counter_plantas =0;
$counter_specs = 0;
$counter_nolink = 0;
$nao_importou = 0;
$quaisarquivos = array();
while ($kw = mysql_fetch_assoc($rn)) {
//echopre($kw);
//foreach($imgs_nomes as $kimg => $vimg) {
	$vimg = $kw['FileName'];
	$inputfile = $dir."/".$vimg;
	//A IMAGEM SERÁ IMPORTADA?
	$importa=0;
	//echopre($ppost);
	if ($ppost['linkposterior']==1) {
		$importa=1;
	} 
	else {
		$specid = $kw['EspecimenID']+0;
		$pltid = $kw['PlantaID']+0;
		if ($specid >0) {
					$importa=1;
		} else {
				if ($pltid>0) {
						$importa=1;
				}
		}
	}
	//NAO IMPORTA SE NAO ENCONTROU E APAGA
	//echo "importa".$importa."<br />";
	if ($importa==0) {
		//echo "importa 000:".$importa."<br />";
		@unlink($inputfile);
		$nao_importou = $nao_importou+1;
		$perc = round(($ii/$nii)*100);
	} 
	else {
		//echo "aqui aqui importa:".$importa."<br />";
		//echo $qq."<br />";
		//LE INFORMACOES DO ARQUIVO DE IMAGEM
		$metadata = @read_exif_data($inputfile);
		$DateTimeOriginal =$metadata['DateTimeOriginal'];
		$dattt = explode(" ",$DateTimeOriginal);
		$dateoriginal = $dattt[0];
		$timeoriginal = $dattt[1];
		$tt = explode(":",$timeoriginal);
		$ttsec = (((($tt[0]*60)+$tt[1])*60)+$tt[2]);
		$dd = str_replace(":","-",$dateoriginal);
		//$dd = new DateTime($dd);
		//$dateoriginal = $dd->format("Y-m-d");
		//echopre($metadata);
		//echo $dd."<br />";
		$dateoriginal = $dd;
		if (!$dateoriginal) { $dateoriginal = $_SESSION['sessiondate'];}

		
		//CASO ESTEJA GEOREFERNCIANDO IMAGENS
		$latitude = '';
		$longitude = '';
		$altitude = '';
		$datum = '';
		$gpsptid = '';
		if (!empty($gpsunit) && $gpsunit>0) { //se tiver selecionado a opção de georeferenciar
			$qq = "SELECT * FROM GPS_DATA WHERE DateOriginal='".$dateoriginal."' AND GPSName='".$gpsunit."'";
				$res = mysql_query($qq,$conn);
				$nres = mysql_numrows($res);
				if ($nres>0) {
					$diffs = array();
					$ii=0;
					while ($row = mysql_fetch_assoc($res)) {
						$testt = $row['TimeOriginal'];
						$ptid = "ptid_".$row['PointID'];
						$ttt = explode(":",$testt);
						$tttest = (((($ttt[0]*60)+$ttt[1])*60)+$ttt[2]);
						$timediff = abs($tttest-$ttsec);
						if ($timediff<=$tolerancia) {
							$tdiffarr = array($ptid => $timediff+0);
							$diffs = array_merge((array)$diffs,(array)$tdiffarr);
						}
					}
					if (count($diffs)>0) {
						$val= $tolerancia*2;
						foreach ($diffs as $key => $vv) {
							if ($vv<$val) {
								$val=$vv;
								$pontokey = $key;
							}
						}
						$kk = explode("_",$pontokey);
						$ptid = $kk[1];
						$qq = "SELECT * FROM GPS_DATA WHERE PointID='".$ptid."'";
						$resu = mysql_query($qq,$conn);
						$rw = mysql_fetch_assoc($resu);
						$latitude = $rw['Latitude'];
						$longitude = $rw['Longitude'];
						$altitude = $rw['Altitude'];
						$datum = $rw['GPSMapDatum'];
						$gpsptid = $rw['PointID'];
					}
				} 
			}

		//CADASTRA A IMAGEM
		$newvimg = limpaimgnome($vimg);
		$newname = $dateoriginal."_".$newvimg;
		
		
		//echo "nome: ".$newname;
		//echo "DateTimeOriginal: ".$DateTimeOriginal;
		
		$arrayofvalues = array(
'FileName' => $newname,
'DateTimeOriginal' => $DateTimeOriginal,
'DateOriginal' => $dateoriginal,
'TimeOriginal' => $timeoriginal,
'Latitude' => $latitude,
'Longitude' => $longitude,
'Altitude' => $altitude,
'GPSMapDatum' => $datum,
'GPSPointID' => $gpsptid,
'Autores' => $addcolvalue,
'TraitID' => $ppost['traitid']);
		//echopre($arrayofvalues);
		//echo "estamos aqui<br />";
		//echopre($ppost);
		
		//verifica se o registro ja existe
		if ($ppost['traitid']>0) {
			$qq = "SELECT * FROM Imagens WHERE FileName LIKE '%".$newvimg."' AND DateTimeOriginal='".$DateTimeOriginal."' AND TraitID='".$ppost['traitid']."'";
		} 
		else {
			$qq = "SELECT * FROM Imagens WHERE FileName LIKE '%".$newvimg."' AND DateTimeOriginal='".$DateTimeOriginal."'";
		}
		$resul = mysql_query($qq,$conn);
		$nresul = mysql_numrows($resul);
		//echo $qq."<br />";
		//echopre($arrayofvalues);
		//echo "nresul".$nresul."<br />";
		//SE NAO EXISTE A IMAGEM, ENTAO CADASTRA NO BANCO DE DADOS
		if ($nresul==0) { 
				$newimg = InsertIntoTable($arrayofvalues,'ImageID','Imagens',$conn);
				if (!$newimg) {
					$erro++;
				}
				else {
					$quaisarquivos[] = $newname;
					//SE CADASTROU CORRETAMENTE, COPIA O ARQUIVO PARA A PASTA DE IMAGENS
					$copiado = copy($inputfile,"img/originais/".$newname);
					//SE ARQUIVO FOI COPIADO
					if ($copiado) {
						@unlink($inputfile);
						//FAZ A RELACAO COM PLANTA OU ESPECIMENE
						if ($ppost['linkposterior']!=1 || !isset($ppost['linkposterior'])) {
							if ($pltid>0) {
									$sql = "SELECT * FROM Traits_variation WHERE PlantaID='".$pltid."' AND TraitID='".$ppost['traitid']."'";
									$field = 'PlantaID';
									$iddam = $pltid;
									$counter_plantas = $counter_plantas+1;
							} else {
								if ($specid>0) {
									$sql = "SELECT * FROM Traits_variation WHERE EspecimenID='".$specid."' AND TraitID='".$ppost['traitid']."'";
									$field = 'EspecimenID';
									$iddam = $specid;
									$counter_specs = $counter_specs+1;
								}
							}
							$rws = mysql_query($sql,$conn);
							$nrws = mysql_numrows($rws);
							if ($nrws==1) {
								$rww = mysql_fetch_assoc($rws);
								$trvarid = $rww['TraitVariationID'];
								$imgarr = explode(";",$rww['TraitVariation']);
								$imgarr = array_merge((array)$imgarr,(array)$newimg);
								$imagens = implode(";",$imgarr);
								$arrayofvalues = array('TraitVariation' => $imagens, 'TraitID' => $ppost['traitid'], $field => $iddam);
						CreateorUpdateTableofChanges($trvarid,'TraitVariationID','Traits_variation',$conn);
								$newimgvar = UpdateTable($trvarid,$arrayofvalues,'TraitVariationID','Traits_variation',$conn);
								
							} elseif ($nrws==0) {
										$arrayofvalues = array('TraitVariation' => $newimg, 'TraitID' => $ppost['traitid'], $field  => $iddam);
										$newimgvar =  InsertIntoTable($arrayofvalues,'TraitVariationID','Traits_variation',$conn);
								}
						} else {
							$newimgvar=1;
							$counter_nolink = $counter_nolink+1;
						}
					}
					
				}
		}
		}
		$perc = round((($ii/$nii)/2)*100)+50;
		$qnu = "UPDATE `temp_imgprogress".$uuid ."` SET percentage=".$perc; 
		mysql_query($qnu,$conn);
		session_write_close();
		$ii++;
	}

/////FINALIZANDO
$txt = "<table align='center' style='font-size: 1em' ><tr><td><b>CONCLUIDO</b>. Foram inseridos:</td></tr>";
if ($counter_specs>0) {
	 $txt .= "<tr><td>".$counter_specs." imagens de especimenes</td></tr>";
	$nao_importou = $nimg-$counter_specs;

}
if ($counter_plantas>0) {
	 $txt .=  "<tr><td>".$counter_plantas." imagens de plantas marcadas </td></tr>";
	$nao_importou = $nimg-$counter_plantas;
}
if ($counter_nolink>0) {
	$nao_importou = $nimg-$counter_plantas;
	 $txt .=  "<tr><td>".$counter_nolink." imagens sem relacionamento</td></tr>";
}
if ($nao_importou>0) {
	 $txt .=  "<tr><td>".$nao_importou." imagens NAO foram importadas&nbsp;<input style='font-size: 1.2em; padding: 5px; cursor: pointer;' value='VER LOG DO ERRO' type='button' onclick=\"javascript:small_window('imagens-import-batch-log.php?linkposterior=".$ppost['linkposterior']."',400,300,'');\" ></td></tr>";
}
if ($counter_specs>0 || $counter_plantas>0) {
	 $txt .=  "<tr><td><input style='font-size: 1.2em; padding: 5px; cursor: pointer;' value='GERAR THUMBNAILS' type='button' onclick=\"javascript:small_window('images_checkthumbs.php?arquivos=quaisarquivos',400,300,'');\" ></td></tr>";
}
$txt .=  "<tr><td><input style='font-size: 1.2em; padding: 5px; cursor: pointer;' value='Fechar' type='button' onclick=\"javascript:window.close();\" ></td></tr>";
$txt .= "</table>";
$_SESSION['quaisarquivos'] = serialize($quaisarquivos);
$qnu = "UPDATE `temp_imgprogress".$uuid ."` SET percentage=100";
echo $txt;
mysql_query($qnu,$conn);

session_write_close();
?>