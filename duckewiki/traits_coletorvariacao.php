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

//echopre($ppost);
//echo "ANTES<br />";
//$aa = unserialize($_SESSION['variation']);
//echo "FILES AQUI:";
//echopre($_FILES);

if ($apagavarsess==1 && (($especimenid>0 && $saveit>0)  ||  !empty($nomesciid))) {
		$typeid = '';
		$idd=0;
		if ($especimenid>0) {
			$typeid = 'EspecimenID';
			$idd= $especimenid;
		} else {
			list($famid,$genusid,$speciesid,$infraspid) = gettaxaids($nomesciid,$conn);
			if ($infraspid>0) {
				$typeid = 'InfraEspecieID';
				$idd = $infraspid;
			} else {
				if ($speciesid>0) {
					$typeid = 'EspecieID';
					$idd = $speciesid;
				} else {
					if ($genusid>0) {
						$typeid = 'GeneroID';
						$idd = $genusid;
					} elseif ($famid>0) {
						$typeid = 'FamiliaID';
						$idd = $famid;
					}
				}
			}
		}
	unset($_SESSION['variation']);
	$oldvals = storeoriginaldatatopost($idd,$typeid,0,$conn,'');
	$_SESSION['variation'] = serialize($oldvals);
	
	
}

//CABECALHO

$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' href='javascript/magiczoomplus/magiczoomplus/magiczoomplus.css' type='text/css' media='screen' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);

//"<link rel='stylesheet' type='text/css' media='screen' href='css/Stickman.MultiUpload.css' />",
//"<script type='text/javascript' src='javascript/mootools.js'></script>",
//"<script type='text/javascript' src='javascript/Stickman.MultiUpload.js'></script>",


//UPLOAD IMAGENS FIELD - para subir + de uma imagem individualmente em campos
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type=\"text/javascript\" src=\"javascript/sorttable/common.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/sorttable/css.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/sorttable/standardista-table-sorting.js\"></script>",
"<script src='javascript/magiczoomplus/magiczoomplus/magiczoomplus.js' type='text/javascript'></script>"

);
$title = GetLangVar('nameeditar')." ".GetLangVar('namevariacao');
$body = '';

//if ($submeteu==1) {
	//unset($_SESSION['monitorvar']);
//}
//echopre($_FILES);
//echopre($trait_1422);
if ($resetar=='1') {
	$qf = "SELECT GROUP_CONCAT(TraitID SEPARATOR ';') AS TraitLIST FROM FormulariosTraitsList WHERE FormID=".$formid;
	$rr = mysql_query($qf,$conn);
	$row= mysql_fetch_assoc($rr);
	$fieldids = explode(";",$row['TraitLIST']);
	$i=0;
	$valores = unserialize($_SESSION['variation']);
	if ($valores) {
		foreach ($valores as $kk => $vv) {
			$tt = explode("_",$kk);
			if (in_array($tt[1],$fieldids)) {
				if ($tt[0]=='trait') { //se image, apaga primeiro as figuras
					$filearr = explode(";",$vv);
					foreach ($filearr as $kyk => $vyv) {
						$fildel = trim($vyv);
						if (!empty($especimenid)) {
								$qq = "AND SpecimenID='$especimenid'";
							} else {
								if (!empty($plantid)) {
									$qq = "AND PlantaID='$plantid'";
								} else {
									if (!empty($infraspid)) {
										$qq = "AND InfraEspecieID='$infraspid'";
									} else {
										if (!empty($speciesid)) {
											$qq = "AND EspecieID='$speciesid'";
										} else {
											if (!empty($genusid)) {
												$qq = "AND GeneroID='$genusid'";
											} else {
												$qq = "AND FamiliaID='$famid'";
											}
										}
									}
								}
						}
						$qqq = "SELECT TraitVariation FROM Traits_variation WHERE TraitID='".$tt[1]."' ".$qq;
						//echo $qqq."<br>";
						$rr = mysql_query($qqq,$conn);
						$nrr = mysql_numrows($rr);
						if ($nrr>0) {
								$rw = mysql_fetch_assoc($rr);
								$oldarr = explode(";",$rw['TraitVariation']);
								if (!in_array($vyv,$oldarr)) {
									//echo $vyv."<br>";
									@unlink("img/traits_states/".$fildel);
								}
						} else {
							@unlink("img/traits_states/".$fildel);
						}
					}
				}
				unset($valores[$kk]);
			}
		}
	}
	$_SESSION['variation'] = serialize($valores);
	@extract($valores);
}

$aa = unserialize($_SESSION['variation']);
@extract($aa);
//echopre($aa);
///process submition to parent sending the whole array of values as arraynotes
if ($option1=='2' || isset($imgdone)) {
	if (!isset($imgdone)) {
	   $arval = $ppost;
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
			$arval['final'],
			$arval['addcoltxt'],
			$arval['specieslist'],
			$arval['saveit'],
			$arval['taxavariacao'],
			$arval['nomesciid']
		);
		//echopre($arval);
		if (isset($_SESSION['variation'])) {
			$variaveis = unserialize($_SESSION['variation']);
		} 
		else {
			$variaveis = array();
		}
	
		//COMBINA ESTADOS DE VARIACAO DE CATEGORIA EM UMA UNICA STRING PARA ARMAZENAMENTO
	
		$result = array();
		//$_SESSION['variation'];
		foreach ($arval as $key => $value) {
			//echo $key."  ".$value."<br>";
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
		//echopre($_FILES);
		$arval = array_merge((array)$arval,(array)$result,(array)$_FILES);
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
		foreach ($arval as $key => $value) {  //para cada variavel no array
			$ttt = explode("_",$key);
			if (!is_array($value) && $ttt[0]!='traitimg' && $ttt[0]!='traitmulti' && $ttt[0]!='traitimgautor' && $ttt[0]!='imagid' && $ttt[0]!= 'traitimgold' && $ttt[0]!= 'traitimgautortxt' && $ttt[0]!='imgtodel' ) { //se nao e uma imagem ou array com info de imagem
				if (@array_key_exists($key,$variaveis)) { //se ela ja existe na variavel de sessao atualiza se diferente
					$vv = $variaveis[$key];
					//if ($vv!=$value) {
						//echo old.$vv." new ".$value."<br>";
						$variaveis[$key]=$value;
					//}
				} else { //ou entao adiciona no array
					$nar = array($key => $value);
					$variaveis = array_merge((array)$variaveis,(array)$nar);
				}
			} 
			else  {
				//se for uma imagem entao e um array que contem as info das images
				//////////////////////////////////////////
				//echo  "AQUI O VALOR DA IMAGEM? <br />";
				//echo $key." <br />".$ttt[0];
				//echopre($value);
				$fname = trim($value['name']);
				$quantasnome = count($value['name']);
				if ($quantasnome>0 && $ttt[0]!='traitimg' && is_array($value)) {
					//echo  "E entrei aqui!<br />";
					foreach($value['name'] as $kimg => $avimg) {
						//&& $value['error']==0 
						$ak = "traitimgautor_".$ttt[1];
						//echo $ak."<br>";
						$fotografo = $arval[$ak];
						//echo "fotografo ".$fotografo."<br>";
	
						$ccid = explode("_",$key); //extrai o numero do caractere
						$filedate = $_SESSION['sessiondate']; //a data de hoje
					
					
						$fva = $filedate."_charid".$ccid[1]."_".$avimg; //pega o nome do arquivo no diretorio temporario
	
						$qq = "SELECT * FROM Imagens WHERE FileName='".$fva."' AND Deleted=0";
						//echo $qq."<br />";
						$qr = mysql_query($qq,$conn);
						$nqr = @mysql_numrows($qr);
						$tmpimg = $value["tmp_name"][$kimg];
						$tmperro = $value["error"][$kimg];
						//echo $tmpimg."   tmperro: ".$tmperro."<br />NQR:".$nqr."<br />    img/temp/".$fva."<br \>";
						if ($nqr==0) {
							if ($tmperro==0) {
							//echo "entrou<br />";
							move_uploaded_file($tmpimg,"img/temp/".$fva);  
							//move o arquivo para a pasta final MAS AQUI PODERIA SER TEMPORARIO PORQUE AINDA NAO GRAVOU OS DADOS
							/////////////////////////////
							$ext = explode(".",$avimg);
							$ll = count($ext)-1;
							$imgext = strtoupper($ext[$ll]);
							$imgext = 'lixo';
							$inputfile = "img/temp/".$fva;
							//echo $inputfile."  ".$imgext."<br />";
							if ($imgext=='JPG' || $imgext=='TIFF' || $imgext=='TIF' || $imgext=='JPEG') {
								$metadata = exif_read_data($inputfile);
								if (count($metadata)>1) {
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
							} 
							else {
									$imgarray =  array(
										'FileName' => $fva,
										'Autores' => $fotografo);
							}
							//echo "<br />imgarray AQUI:";
							//echopre($imgarray);
							$newimg = InsertIntoTable($imgarray,'ImageID','Imagens',$conn);
							if ($newimg) {
								//echo "MOVENDO: <br>";
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
							else {  
								if (!empty($fva)) { //echo "<span  style='color: red; font-size: 1.5em; background-color: yellow;'>Houve erro na importação da imagen: ".$tmperro." ".$fva."</span><br />";
								}
							}
						} else {
							if (!empty($fva))  {
								echo "<span  style='color: red; font-size: 1.5em; background-color: yellow;'>Já existe um arquivo com este nome na base de dados</span><br />";
							}
						}
					}
				} 
				elseif ($ttt[0]=='traitimg') {
				    //NO CASO DE APAGAR UMA IMAGEM
					$ttid  = $ttt[1];
					$olimgvals = trim($variaveis['trait_'.$ttid]);
					//echo "key".$key;
					//echopre($value);
					//echo "<br>aqui entao:<br>".$variaveis['trait_'.$ttid];
					if (!empty($olimgvals)) {
						$valoresvelhos = explode(";",$variaveis['trait_'.$ttid]);
						foreach ($valoresvelhos as $kvel => $vvimg) {
							$vimg = trim($vvimg);
							if ($vimg>0 && !empty($vimg)) {
							$imgtodel = $arval["imgtodel_".$ttid."_".$vvimg];
							if ($imgtodel==1) {
								//print_r($valoresvelhos);
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
						//echopre($variaveis);
						//echo " <br />aqui valores novos ".$variaveis['trait_'.$ttid]."<br>";
					}
					//echo "<hr>";
					
				}
				
				
				////////////////
			}
		}
			unset( $_SESSION['variation']);
			$_SESSION['variation'] = serialize($variaveis);
			if (count($newimagefile)>0) {
				$_SESSION['newimagfiles'] = serialize($newimagefile);
				$_SESSION['othervars'] = array(
				'elementid2' => $ppost['elementid2'],
				'elementid' => $ppost['elementid'],
				'formid' => $ppost['formid'],
				'especimenid' => $ppost['especimenid'],
				'traitsinenglish' => $ppost['traitsinenglish'],
				'saveit' => $ppost['saveit']
				);
				if ($taxavariacao==1) { 
				$_SESSION['othervars'] = array(
				'elementid2' => $ppost['elementid2'],
				'elementid' => $ppost['elementid'],
				'formid' => $ppost['formid'],
				'especimenid' => $ppost['especimenid'],
				'traitsinenglish' => $ppost['traitsinenglish'],
				'saveit' => $ppost['saveit'],
				'taxavariacao' => $ppost['taxavariacao'],
				'nomesciid' => $ppost['nomesciid'],
				'typeid' => $ppost['typeid'],
				'idd' => $ppost['idd']
					);
				}
				$zz = explode("/",$_SERVER['SCRIPT_NAME']);
				$serv = $_SERVER['SERVER_NAME'];
				$returnto = $serv."/".$zz[1]."/traits_coletorvariacao.php";
				header("location: http://".$serv."/cgi-local/imagick_function.php?returnto=".$returnto."&folder=".$zz[1]."&returnvar=imgdone");
					} 
		} 
		else { //if imagedone
			unset($_SESSION['newimagfiles']);
			unset($_SESSION['addcoltxt']);
			extract($_SESSION['othervars']);
			$variaveis = unserialize($_SESSION['variation']);
		}
		$elementid2txt = describetraits($variaveis,$img=FALSE,$conn);
		FazHeader($title,$body,$which_css,$which_java,$menu);
		if ($final==1) {
			if (($saveit==1 && $especimenid>0) || ($taxavariacao==1 && !empty($nomesciid))) {
						$typeid = '';
						$idd=0;
						if ($especimenid>0) {
							$typeid = 'EspecimenID';
							$idd= $especimenid;
						} else {
							list($famid,$genusid,$speciesid,$infraspid) = gettaxaids($nomesciid,$conn);
							if ($infraspid>0) {
								$typeid = 'InfraEspecieID';
								$idd = $infraspid;
							} else {
								if ($speciesid>0) {
									$typeid = 'EspecieID';
									$idd = $speciesid;
								} else {
									if ($genusid>0) {
										$typeid = 'GeneroID';
										$idd = $genusid;
									} elseif ($famid>0) {
										$typeid = 'FamiliaID';
										$idd = $famid;
									}
								}
							}
						}
						$changedtraits=0;
						//faz o cadastro das variaveis se houver
						if (!empty($_SESSION['variation'])) {
									$oldtraitids = storeoriginaldatatopost($idd,$typeid,0,$conn,'');
									$newtraitids = unserialize($_SESSION['variation']);
									//compare arrays
									foreach ($newtraitids as $key => $val) {
										$oldval = trim($oldtraitids[$key]);
										$vv = trim($val);
										if ($vv!='imagem' && $vv!='none' && (!empty($vv) || (!empty($oldval) && $vv!=$oldval)) && ($vv!=$oldval || empty($oldval))) {
											$changedtraits++;
										}
									}
						}
						if ($changedtraits>0) {
								$traitarray = unserialize($_SESSION['variation']);
								if (count($traitarray)>0) {
									//echo "ATUALIZANDO<BR> bibtex:".$bibtex_id;
									//echopre($traitarray);
									$resultado = updatetraits($traitarray,$idd,$typeid,$bibtex_id,$conn);
								}
						}
			echo "
<br>
  <table class='sucessosmall' align='center' cellpadding='7'>
  <tr><td >Variação foi salva com sucesso!</td></tr>
  <tr><td><input style='cursor: pointer'  type='button' value='Fechar' class='bsubmit'  onclick=\"javascript:window.close();\" /></td></tr>
</table>
<br>
	";
			} 
			else {
			echo "
<br>
  <table class='sucessosmall' align='center' cellpadding='7'>
  <input type='hidden' id='sendid' value=\"$elementid2txt\" />
  <tr><td >".GetLangVar('messagevariationset')."</td></tr>
  <tr><td><input style='cursor: pointer'  type='button' value='".GetLangVar('nameconcluir')."' class='bsubmit'  onclick=\"javascript:sendval_innerHTML('sendid','".$elementid."');\" /></td></tr>
</table>
<br>
	";
			}
		} 
		if ($final==2) {
			echo "
<form >
  <input type='hidden' id='sendid' value=\"$elementid2txt\" />
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        sendval_innerHTML('sendid','$elementid');
      }
      ,0.0001);
  </script>
</form>";
		}
} 
else {
	FazHeader($title,$body,$which_css,$which_java,$menu);
}
///////////////////////////////////////
//echo "DEPOIS<br />";
//$aa = unserialize($_SESSION['variation']);
//echopre($aa);

if (isset($_SESSION['variation'])) {
	$variaveis = unserialize($_SESSION['variation']);
	//EXTRAI MULTISTATES
	foreach ($variaveis as $key => $value) {
		//echo $key."  ".$value."<br>";
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
					//echo "states array<br>";
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
	//echo "variaveis: <br>";
	//echopre($variaveis);
	//echo "-----------------------------------------";
} 

if ($especimenid>0) {
	$qu = "SELECT acentosPorHTML(colpessoa.Abreviacao) as COLETOR,  pltb.Number as NUMERO FROM Especimenes AS pltb JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID WHERE EspecimenID='".$especimenid."'";
	$ru = mysql_query($qu,$conn);
	$rwu = mysql_fetch_assoc($ru);
	$titlef = $rwu['COLETOR']."  ".$rwu['NUMERO'];
} 
else {
	if ($taxavariacao==1) {
	$idtxt = explode("_",$nomesciid);
	//echo  $nomesciid."<br >";
	$qu = "SELECT getnamewithautorone(".$idtxt[1].",'".$idtxt[0]."', 0, 0) as nn";
	//echo $qu."<br />";
	$ru = mysql_query($qu,$conn);
	$nru = mysql_numrows($ru);
		if ($nru>0) { 
		$nn = mysql_fetch_assoc($ru);
		$ntxt = $nn['nn'];
		$titlef =  "Entrando variação para ".$ntxt;
		}
	} 
	else {
	$titlef = GetLangVar('namenova')." ".GetLangVar('namevariacao'); 
	}
}
echo "
<table class='myformtable' align='center' cellpadding='7' > 
<thead>
<tr><td >$titlef </td></tr>
</thead>
<tbody>
<form action='traits_coletorvariacao.php' method='post'  >
  <input type='hidden' name='elementid2' value='".$elementid2."' />
  <input type='hidden' name='elementid' value='".$elementid."' />
  <input type='hidden' name='traitsinenglish' value='".$traitsinenglish."' />
  <input type='hidden' name='saveit' value='".$saveit."' />
  <input type='hidden' name='especimenid' value='".$especimenid."' />";
if ($taxavariacao==1) { 
echo " 
  <input type='hidden' name='taxavariacao' value='".$taxavariacao."' />
  <input type='hidden' name='nomesciid' value='".$nomesciid."' />
  <input type='hidden' name='typeid' value='".$typeid."' />
  <input type='hidden' name='idd' value='".$idd."' />  
";
}
echo "
<tr>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')."</td>
        <td class='tdsmallbold'>
          <select name='formid' onchange='this.form.submit();'>";
				if (!empty($formid)) {
					$qq = "SELECT * FROM Formularios WHERE FormID='$formid'";
					$rr = mysql_query($qq,$conn);
					$row= mysql_fetch_assoc($rr);
					echo "
            <option selected value='".$row['FormID']."'>".$row['FormName']."</option>";
				} else {
					echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
				}
				//formularios usuario
				if ($formidhabitat>0) {
					$txthab = "FormID<>".$formidhabitat."  AND ";
				} else {
					$txthab = "";
				}
				$qq = "SELECT * FROM Formularios WHERE ".$txthab." (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FormName ASC";
				$rr = mysql_query($qq,$conn);
				while ($row= mysql_fetch_assoc($rr)) {
					echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
				}
			echo "
          </select>
      </td>
    </tr>
  </table>
  </td>
</tr>
</form>";
//PRINT SELECT LINK LEVEL
if (($formid+0)>0) {
	if ($traitsinenglish==1) {
		$flag = "brasilFlagicon.png";
		$flagval = 0;
	} else {
		$flag = "usFlagicon.png";
		$flagval = 1;
	}
echo "
<tr>
<td  align='right' ><a onclick=\"javascript:document.getElementById('traitlang').value=".$flagval.";document.getElementById('varform2').submit();\"><img height='30' src=\"icons/".$flag."\" alt='Mudar idioma' style='cursor: pointer;' /></a></td></tr>";

//echopre($ppost);
//echopre(unserialize($_SESSION['variation']));

echo "
<tr>
<td>
  <form id='varform2' name='varform2' method='post' enctype='multipart/form-data' action='traits_coletorvariacao.php' >
  <input type='hidden' id='traitlang' name='traitsinenglish' value='' />
  <input type='hidden' name='MAX_FILE_SIZE' value='50000000' />
  <input type='hidden' name='formid' value='".$formid."' />
  <input type='hidden' name='elementid2' value='".$elementid2."' />
  <input type='hidden' name='elementid' value='".$elementid."' />
  <input type='hidden' name='saveit' value='".$saveit."' />
  <input type='hidden' name='especimenid' value='".$especimenid."' />
  <input type='hidden' name='option1' value='2' />";
	 if ($taxavariacao==1) { 
	$qbib = "SELECT DISTINCT BibtexIDS FROM Traits_variation as tr LEFT JOIN FormulariosTraitsList as lista ON lista.TraitID=tr.TraitID WHERE tr.".$typeid."=".$idd." AND lista.FormID=".$formid;
	//echo $qbib."<br />";
	$rbib = @mysql_query($qbib,$conn);
	$bibids = array();
	if ($rbib) {
	while($rwbib = mysql_fetch_assoc($rbib)) {
		$bb = $rwbib['BibtexIDS'];
		$bz = explode(";",$bb);
		$bibids = array_merge((array)$bibids,(array)$bz);
	}
	$bibids = array_unique($bibids);
	}
	$bibtexts = array();
	if (count($bibids)>0) {
		$bibtex_id = implode(";",$bibids);
		foreach($bibids as $bib) {
			$qb = "SELECT BibKey FROM BiblioRefs WHERE BibID=".$bib;
			$rb = mysql_query($qb,$conn);
			$rwb = mysql_fetch_assoc($rb);
			$bibtexts[] = $rwb['BibKey'];
		}
	} else {
		$bibtex_id = '';
	}
	if (count($bibtexts)>0) {
		$bibtex_txt = implode(";",$bibtexts);
	} else {
		$bibtex_txt = '';
	}
	
echo " 
  <input type='hidden' name='taxavariacao' value='".$taxavariacao."' />
  <input type='hidden' name='nomesciid' value='".$nomesciid."' />
  <input type='hidden' name='typeid' value='".$typeid."' />
  <input type='hidden' name='idd' value='".$idd."' />
";
echo "
    <table>
      <tr>
      <td class='tdsmallboldright'>Referência FONTE&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
$help = "Indique referência(s) bibliográfica(s) de onde a informação das variáveis está sendo extraída";
echo " onclick=\"javascript:alert('$help');\" />&nbsp;&nbsp;
      </td>
      <td ><span id='bibtex_txt'>".$bibtex_txt."</span><input type='hidden' id='bibtex_id' name='bibtex_id'  value='".$bibtex_id."'></td>
      <td><input type=button style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Bibliografia'  onmouseover=\"Tip('Indique referência(s) bibliográfica(s) de onde a informação das variáveis está sendo extraída');\" ";
		$myurl = "bibtext-gridsave.php?bibtex_txt=bibtex_txt&bibtex_id=bibtex_id&bibids=".$bibtex_id;
		echo " onclick = \"javascript:small_window('".$myurl."',800,600,'Referências Bibliográficas');\" /></td>
      </tr>
    </table>";
	}
echo "
  </td>
</tr>";
echo "
<tr>
  <td align='center' >";
  include "traits_generalform2.php";
echo "
  </td>
</tr>
<tr>
  <td>
    <table align='center'>
      <tr>
        <input type='hidden' id='final' name='final' value='' />
        <td align='center' ><input style='cursor: pointer'  type=submit value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.getElementById('final').value=1\" /></td>
        <!---<td align='center'><input style='cursor: pointer'  type=submit value='".GetLangVar('nameconcluir')."' class='bblue' onclick=\"javascript:document.getElementById('final').value=2\" /></td>--->
</form>
<form action='traits_coletorvariacao.php' method='post'>
  <input type='hidden' name='formid' value='".$formid."' />
  <input type='hidden' name='elementid2' value='".$elementid2."' />
  <input type='hidden' name='elementid' value='".$elementid."' />
  <input type='hidden' name='saveit' value='".$saveit."' />
  <input type='hidden' name='especimenid' value='".$especimenid."' />";
	if ($taxavariacao==1) { 
echo " 
  <input type='hidden' name='taxavariacao' value='".$taxavariacao."' />
  <input type='hidden' name='nomesciid' value='".$nomesciid."' />
  <input type='hidden' name='typeid' value='".$typeid."' />
  <input type='hidden' name='idd' value='".$idd."' />

";
	}  
echo "
  <input type='hidden' name='resetar' value='1' />
        <td align='left'><input style='cursor: pointer' type='submit' value='".GetLangVar('namereset')."' class='breset' /></td>
</form>
      </tr>
    </table>
  </td>
</tr>
<tr><td class='tdformnotes'><b>".GetLangVar('nameobs')."</b>: ".GetLangVar('messagemultiplevalues')."</td></tr>";
}
echo "
</tbody>
</table>"; //fecha tabela do formulario
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//, "<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>