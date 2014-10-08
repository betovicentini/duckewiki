<?php

session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

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
$body= '';
$title = GetLangVar('nameeditar')." ".GetLangVar('namevariacao');


if ($resetar=='1') {
		$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
		$rr = mysql_query($qq,$conn);
		$row= mysql_fetch_assoc($rr);
		$fieldids = explode(";",$row['FormFieldsIDS']);
		$i=0;
		$valores = unserialize($_SESSION[$elementid]);
		if ($valores) {
		foreach ($valores as $kk => $vv) {
			$tt = explode("_",$kk);
			if (in_array($tt[1],$fieldids)) {
				if ($tt[0]=='trait') { //se image, apaga primeiro as imagens importadas temporariamente
					$filearr = explode(";",$vv);
					foreach ($filearr as $kyk => $vyv) {
						$fildel = trim($vyv);
						$qqq = "SELECT * FROM Imagens WHERE ImageID='".$fildel."'";
						$rr = mysql_query($qqq,$conn);
						$nrr = mysql_numrows($rr);
						if ($nrr>0) {
							$rw = mysql_fetch_assoc($rr);
							$fname = $rw['FileName'];
							@unlink("img/originais/".$fname);
							@unlink("img/thumbnails/".$fname);
							@unlink("img/copias_baixa_resolucao/".$fname);
							$qq = "DELETE FROM Imagens WHERE ImageID='".$fildel."'";
							mysql_query($qq,$conn);
						}
					}
				}
				unset($valores[$kk]);
			}
		}
		}
		$_SESSION[$elementid] = serialize($valores);
		@extract($valores);
}


$aa = unserialize($_SESSION[$elementid]);
@extract($aa);


///process submition to parent sending the whole array of values as arraynotes
if ($option1=='2' || isset($imgdone)) {

 if (!isset($imgdone)) {
	$arval = $_POST;

	unset($arval['MAX_FILE_SIZE'],  
	$arval['formid' ],  $arval['option1'],  
	$arval['especimenid'],  
	$arval['plantid'],  
	$arval['infraspid' ],  
	$arval['famid'],  
	$arval['genusid'],  
	$arval['speciesid'],
	$arval['formname'],  
	$arval['elementid'],  
	$arval['traitids'],
	$arval['final']
	);

	if (isset($_SESSION[$elementid])) {
		$variaveis = unserialize($_SESSION[$elementid]);
	} else {
		$variaveis = array();
	}

	//COMBINA ESTADOS DE VARIACAO DE CATEGORIA EM UMA UNICA STRING PARA ARMAZENAMENTO

	$result = array();
	//$_SESSION[$elementid];
	foreach ($arval as $key => $value) {
		//echo $key."  ".$value."<br />";
		$arraykey = explode("_",$key); 
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		$nno = "traitvar_".$charid;
		//se for um estado de variacao de uma variavel categorica
		if ($varorunit=='traitmulti') {
			if (!empty($value)) {
				//se ja houver um valor para $nno entao adiciona estado de variacao
				if (array_key_exists($nno,$result) && $result[$nno]!='none') {
					$rr = trim($result[$nno]);
					if (!empty($rr)) {
						$result[$nno] = $result[$nno].";".$value;
					} else {
						$result[$nno] = $value;
					}
				} else { //senao insere no array result o valor para $nno
					$nar = array($nno => $value);
					$result = array_merge((array)$result,(array)$nar);
				}
			} 
		}
	} //end for each

	//junta os novos valores para o array de resultados e imagens se estas tiverem sido postas
	$arval = array_merge((array)$arval,(array)$result,(array)$_FILES);

	foreach ($variaveis as $kk => $vv) {
		$arraykey = explode("_",$kk); 
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		if ($varorunit=='traitmulti') {
			if (!array_key_exists($kk,$arval)) {
				$variaveis[$kk] = NULL;
			} 
		}
	}
	$newarr = array();
	foreach ($variaveis as $kk => $vv) {
		$arraykey = explode("_",$kk); 
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		if ($varorunit=='traitmulti') {
			if (empty($newarr[$charid])) {
				$newarr[$charid]=1;
			} 
			if (!empty($variaveis[$kk])) {
				$newarr[$charid]++;
			} 
		}
	}

	if (!empty($newarr)) {
		foreach ($newarr as $kk => $vv) {
			if ($vv<=1) {
				$tname = 'traitvar_'.$kk;
				$variaveis[$tname]=' ';
			}
		}
	}

	//armazena os dados novos e do novo relatorio a variavel de sessao variation....
	$newimagefile = array();
	//echopre($arval);
	foreach ($arval as $key => $value) {	//para cada variavel no array
		$ttt = explode("_",$key);
		if (!is_array($value) && $ttt[0]!='traitimg' && $ttt[0]!='traitmulti' && $ttt[0]!='traitimgautor' && $ttt[0]!='imagid' && $ttt[0]!= 'traitimgold' && $ttt[0]!= 'traitimgautortxt' && $ttt[0]!='imgtodel' ) { //se nao e uma imagem ou array com info de imagem
			if (@array_key_exists($key,$variaveis)) { //se ela ja existe na variavel de sessao atualiza se diferente
				$vv = $variaveis[$key];
				//if ($vv!=$value) {
					//echo old.$vv." new ".$value."<br />";
					$variaveis[$key]=$value;
				//}
			} else { //ou entao adiciona no array
				$nar = array($key => $value);
				$variaveis = array_merge((array)$variaveis,(array)$nar);
			}
		} else { //se for uma imagem entao e um array que contem as info das images
			$fname = trim($value['name']);

			if (!empty($fname) && $ttt[0]!='traitimg' && $values['error']==0 && is_array($value)) {
				$ak = "traitimgautor_".$ttt[1];
				//echo $ak."<br />";
				$fotografo = $arval[$ak];
				//echo "fotografo ".$fotografo;

				$ccid = explode("_",$key); //extrai o numero do caractere
				$filedate = $_SESSION['sessiondate'];	//a data de hoje
				$fva = $filedate."_charid".$ccid[1]."_".$value['name']; //pega o nome do arquivo no diretorio temporario

				$qq = "SELECT * FROM Imagens WHERE Name='$fva' AND Deleted=0";
				$qr = @mysql_query($qq,$conn);
				$nqr = @mysql_numrows($qr);

			if ($nqr==0) {
				move_uploaded_file($value["tmp_name"],"img/temp/$fva");  //move o arquivo para a pasta final MAS AQUI PODERIA SER TEMPORARIO PORQUE AINDA NAO GRAVOU OS DADOS
				/////////////////////////////
				$ext = explode(".",$value['name']);
				$ll = count($ext)-1;
				$imgext = strtoupper($ext[$ll]);
				if ($imgext=='JPG' || $imgext=='TIFF' || $imgext=='TIF' || $imgext=='JPEG') {
						$inputfile = "img/temp/$fva";
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
									'FileName' => $fva,
									'DateTimeOriginal' => $DateTimeOriginal,
									'DateOriginal' => $dateoriginal,
									'TimeOriginal' => $timeoriginal,
									'Autores' => $fotografo);
				} else {
						$imgarray =  array(
									'FileName' => $fva,
									'Autores' => $fotografo);
				}
				//echopre($imgarray);
				$newimg = InsertIntoTable($imgarray,'ImageID','Imagens',$conn);
				if ($newimg) {
					$copiado = @copy($inputfile,"img/originais/".$fva);
					if ($copiado) {
						unlink($inputfile);
						$newimagefile[] = $fva;

					}
					$kkk = 'trait_'.$ccid[1];
					if (array_key_exists($kkk,$variaveis)) { //se ela ja existe na variavel de sessao atualiza se diferente
						$imgarrs = explode(";",$variaveis[$kkk]);
						foreach ($imgarrs as $mykk => $myvv) {
							$mtvv = trim($myvv);
							if (empty($mtvv)) {
								unset($imgarrs[$mykk]);
							}
						}
						if (count($imgarrs)>0) {
							$imgarrs = array_merge((array)$imgarrs,(array)$newimg);
						} else {
							$imgarrs = array($newimg);
						}
						$imgarrs = array_unique($imgarrs);
						if (count($imgarrs)==1) { 
							$valor = $imgarrs[0];
						} else {
							$valor = implode(";",$imgarrs);
						}
						$variaveis[$kkk] = $valor;
					} else {
						$nar = array($kkk => $newimg);
						$variaveis = array_merge((array)$variaveis,(array)$nar);
					}
				}
			}
		} elseif ($ttt[0]=='traitimg') {
				$ttid  = $ttt[1];
				$olimgvals = trim($variaveis['trait_'.$ttid]);
				//echo "<br />aqui entao ".$variaveis['trait_'.$ttid];
				if (!empty($olimgvals)) {
					$valoresvelhos = explode(";",$variaveis['trait_'.$ttid]);

					foreach ($valoresvelhos as $kvel => $vvimg) {
						$vimg = trim($vvimg);
						if ($vimg>0 && !empty($vimg)) {
						$imgtodel = $arval["imgtodel_".$ttid."_".$vvimg];

						if ($imgtodel==1) {
							unset($valoresvelhos[$kvel]);
							$dataa = date("Y-m-d");
							$fieldsaskeyofvaluearray = array('Deleted' => $dataa);
							CreateorUpdateTableofChanges($vimg,'ImageID','Imagens',$conn);
							UpdateTable($vimg,$fieldsaskeyofvaluearray,'ImageID','Imagens',$conn);
							unset($arval["imgtodel_".$ttid."_".$vvimg]);
							unset($arval["imagid_".$ttid."_".$vvimg]);
						} 
						}
					}
					$variaveis['trait_'.$ttid] = implode(";",$valoresvelhos);
				}

			}
		}
	}

		unset( $_SESSION[$elementid]);
		$_SESSION[$elementid] = serialize($variaveis);

		if (count($newimagefile)>0) {
			$_SESSION['newimagfiles'] = serialize($newimagefile);
			$_SESSION['othervars'] = array(
			'elementid2' => $_POST['elementid2'],
			'elementid' => $_POST['elementid'],
			'formid' => $_POST['formid']);

					$zz = explode("/",$_SERVER['SCRIPT_NAME']);
					$serv = $_SERVER['SERVER_NAME'];
					$returnto = $serv."/".$zz[1]."/variacao-popup-batch.php";

					header("location: http://".$serv."/cgi-local/imagick_function.php?returnto=".$returnto."&folder=".$zz[1]."&returnvar=imgdone");
				} 
	} else { //if imagedone
		unset($_SESSION['newimagfiles']);
		extract($_SESSION['othervars']);
		$variaveis = unserialize($_SESSION[$elementid]);
	}

	$elementid2txt = describetraits($variaveis,$img=FALSE,$conn);

	//echo $elementid2txt;
	unset( $_SESSION[$elementid]);

	$_SESSION[$elementid] = serialize($variaveis);
	PopupHeader($title,$body);

	if ($final==1) {
	echo "<br /><table class='sucessosmall' align='center'>
			<input type='hidden' id='sendid' value=\"$elementid2txt\">
			<tr><td >".GetLangVar('messagevariationset')."</td></tr>
			<tr>
			<td>
			<input type=button value=".GetLangVar('nameconcluir')." class='bsubmit'  onclick=\"javascript:sendval_innerHTML('sendid','".$elementid."');\">
				</td>
			</tr>
		</table>
		<br />
	";
	} 
	if ($final==2) {
		echo "
		<form >
			<input type='hidden' id='sendid' value=\"$elementid2txt\">
			<script language=\"JavaScript\">
			setTimeout(
				function() {
					sendval_innerHTML('sendid','".$elementid."');
				}
				,0.0001);
			</script>
		</form>";
	}

} else {
	PopupHeader($title,$body);
}
if ($final!='2') {
if (isset($_SESSION[$elementid])) {
	$variaveis = unserialize($_SESSION[$elementid]);
	//EXTRAI MULTISTATES
	foreach ($variaveis as $key => $value) {
		//echo $key."  ".$value."<br />";
		$arraykey = explode("_",$key); 
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		if ($varorunit=='traitvar') {
			$qq = "SELECT * FROM Traits WHERE TraitID='$charid'";
			$nch = mysql_query($qq,$conn);
			$rwch = mysql_fetch_assoc($nch);
			if ($rwch['TraitTipo']=='Variavel|Categoria' && strtoupper($rwch['MultiSelect'])=='SIM') {//se for um estado de variacao de uma variavel categorica
				if (!empty($value)) {
					$arrstates = explode(";",$value);
					//echo "states array<br />";
					//echopre($arrstates);
					foreach ($arrstates as $stateval) {
							$keystate = "traitmulti_".$charid."_".$stateval;
							$nar = array($keystate => $stateval);
							if (array_key_exists($keystate,$variaveis)) {
								$variaveis[$keystate]= $stateval;
							} else {
								$nar = array($keystate => $stateval);
								$variaveis = array_merge((array)$variaveis,(array)$nar);
							}
					}
				}
			}
		} //end for each
	}
	@extract($variaveis);
	//echo "variaveis: <br />";
	//echopre($variaveis);
	//echo "-----------------------------------------";
} 

echo "
<table class='myformtable' align='center' cellpadding='5' cellspacing='0'> 
<thead>
<tr>
<td>".GetLangVar('namenova')." ".GetLangVar('namevariacao')."</td></tr>
</thead>
<tbody>
<tr>
<td>
	<table>
	<tr>
		<td class='bold'>".GetLangVar('nameformulario')."</td>
		<td class='bold'>
			<form action='variacao-popup-batch.php' method='post'  >
				<input type='hidden' name='elementid2' value='$elementid2'>
				<input type='hidden' name='elementid' value='$elementid'>
				<select name='formid' onchange='this.form.submit();'>";
				if (!empty($formid)) {
					$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
					$rr = mysql_query($qq,$conn);
					$row= mysql_fetch_assoc($rr);
					echo "<option selected value='".$row['FormID']."'>".$row['FormName']."</option>";
				} else {
					echo "<option value=''>".GetLangVar('nameselect')."</option>";
				}
				//formularios usuario
				$qq = "SELECT * FROM Formularios WHERE FormName!='Habitat' ORDER BY FormName ASC";
				$rr = mysql_query($qq,$conn);
				while ($row= mysql_fetch_assoc($rr)) {
					echo "<option value='".$row['FormID']."'>".$row['FormName']."</option>";
				}
			echo "
			</select>
			</form>
		</td></tr>
	</table>
</td>
</tr>";
//PRINT SELECT LINK LEVEL
if (!empty($formid)) {
echo "
<tr>
<td align='center' >
<form id='varform2' method='post' enctype='multipart/form-data' action='variacao-popup-batch.php' >
				<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
				<input type='hidden' name='formid' value='$formid'>
				<input type='hidden' name='elementid2' value='$elementid2'>
				<input type='hidden' name='elementid' value='$elementid'>
				<input type='hidden' name='option1' value='2'>";

		include "variacao-form2.php";
		echo "</td></tr>
		<tr><td>
	<table align='center'>
				<tr>
				<input type='hidden' id='final' name='final' value=''>
				<td align='center' >
					<input type=submit value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.getElementById('final').value=1\">
				</td>
				<td align='center'>
					<input type=submit value='".GetLangVar('nameconcluir')."' class='bblue' onclick=\"javascript:document.getElementById('final').value=2\">
				</td>
</form>
		<form action=variacao-popup.php method='post'>
				<input type='hidden' name='formid' value='$formid'>
				<input type='hidden' name='elementid2' value='$elementid2'>
				<input type='hidden' name='elementid' value='$elementid'>
				<input type='hidden' name='resetar' value='1'>
				<td align='left'><input type='submit' value='".GetLangVar('namereset')."' class='breset' /></td>
			</form>
		</tr>
</table>
</td>
</tr>
<tr><td class='tdformnotes'><b>".GetLangVar('nameobs')."</b>: ".GetLangVar('messagemultiplevalues')."</td></tr>";
}

echo "</tbody></table>"; //fecha tabela do formulario

}

PopupTrailers();
?>