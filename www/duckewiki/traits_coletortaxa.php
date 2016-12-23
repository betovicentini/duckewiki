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

if (isset($nomesciid)) {
	list($famid,$genusid,$speciesid,$infraspid) = gettaxaids($nomesciid,$conn);
} else {
	if ($infraspid>0) {
			$nomesciid = 'infspid_'.$genusid;
		} else {
			if ($speciesid>0) {
				$nomesciid = 'speciesid_'.$speciesid;
			} else {
				if ($genusid>0) {
					$nomesciid = 'genusid_'.$genusid;
				} elseif ($famid>0) {
					$nomesciid = 'famid_'.$famid;
				}
			}
	}
}
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

if (!isset($taxanome) || empty($taxanome)) {
$sql = "SELECT taxanome(".($famid+0).",".($genusid+0).",".($speciesid+0).",".($infraspid+0).", 'onome') as onome";
$res = mysql_query($sql,$conn);
$rw = mysql_fetch_assoc($res);
$taxanome = $rw['onome'];
}

//CABECALHO
$ispopup=1;
$menu = FALSE;

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/Stickman.MultiUpload.css' />",
"<link rel='stylesheet' href='javascript/magiczoomplus/magiczoomplus/magiczoomplus.css' type='text/css' media='screen' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);

//UPLOAD IMAGENS FIELD - para subir + de uma imagem individualmente em campos
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type=\"text/javascript\" src=\"javascript/sorttable/common.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/sorttable/css.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/sorttable/standardista-table-sorting.js\"></script>",
"<script type='text/javascript' src='javascript/mootools.js'></script>",
"<script type='text/javascript' src='javascript/Stickman.MultiUpload.js'></script>",
"<script src='javascript/magiczoomplus/magiczoomplus/magiczoomplus.js' type='text/javascript'></script>"

);
$title = 'Variáveis relacionadas à um táxon';
$body = '';
$erro=0;

//adiciona modificações na estrutura do banco para acomodar o novo referenciamento bibkey
$sql = "ALTER TABLE `Traits_variation`  ADD `BibkeyID` INT(10) NOT NULL AFTER `BibtexIDS`";
@mysql_query($sql,$conn);
$sql = "ALTER TABLE `ChangeTraits_variation`  ADD `BibtextIDS` CHAR(255) ADD `BibkeyID` INT(10) NOT NULL AFTER `GruposppID`";
@mysql_query($sql,$conn);

if ($concluir==1) {
	$atualizou =0;
	$oldvvv =  unserialize($_SESSION['taxatraitsvar']);
	//echopre($oldvvv);
	foreach ($oldvvv as $obibkey => $valores) {
		$traitarray = $valores;
		if (count($traitarray)>0 && !empty($obibkey) && !empty($typeid) && $idd>0) {
			$obib = explode("_",$obibkey);
			//echo "aqui o bibkey  ".$obibkey."<br />";
			$resultado = updatetaxatraits($traitarray,$obib[1],$typeid, $idd,$conn);
			//$resultado = updatemonitoramento($traitarray,$ddobs,$plantaid,$conn);
			if (!$resultado) {
				$erro++;
			} else {
				$atualizou++;
			}
		}
	}
    FazHeader($title,$body,$which_css,$which_java,$menu);
	if ($erro>0) {
echo "
<br />
<table cellpadding=\"1\" width='50%' align='left' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')." 5</td></tr>
</table>
<br />";
	} else {
		echo "
<form >
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        window.close();
      }
      ,0.0001);
  </script>
</form>";
	}
	if ($atualizou>0 && $erro>0) {
	echo "
<br />
<table cellpadding=\"1\" width='50%' align='left' class='sucess'>
  <tr><td class='tdsmallbold' align='center'>Variação foi atualizada cadastrada!</td></tr>
</table>
<br />";
	}
	$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
	FazFooter($which_java,$calendar=TRUE,$footer=$menu);
	} 
if (!isset($concluir) || $erro>0) {

if (isset($nomesciid)) {
	//echo "entrei aqui???";
	unset($_SESSION['taxatraitsvar']);
	//GET OLD DATA FOR THE PLANT
	$oldbibkeyids = array();
	$qq = "SELECT DISTINCT BibkeyID FROM Traits_variation WHERE ".$typeid."='".$idd."'";
	//echo $qq."<br >";
	$qu  = mysql_query($qq,$conn);
	$bibkeyidsofmonitor = array();
	while ($rw= mysql_fetch_assoc($qu)) {
		$bibkeyid = $rw['BibkeyID'];
		$bibkeyidsofmonitor[] = $bibkeyid;
		$oldvals = GetTaxaData($typeid,$idd,$bibkeyid,$formid,$conn);
		//echopre($oldvals);
		$oldbibkeyids["bibkey_".$bibkeyid] = $oldvals;
	}
	if (count($oldbibkeyids)>0) {
		$_SESSION['taxatraitsvar'] = serialize($oldbibkeyids);
	}
	//echopre($oldbibkeyids);
} else {
	$bibkeyidsofmonitor = unserialize($arrofbibkeyids);
}

//SE RESETAR LIMPAR IMAGENS SALVAS TEMPORARIAMENTE
if ($resetar=='1') {
	$qq = "SELECT GROUP_CONCAT(DISTINCT TraitID ORDER BY Ordem SEPARATOR ';') as traitids FROM FormulariosTraitsList WHERE FormID='".$formid."'";
	$res = mysql_query($qq,$conn);
	$rw = mysql_fetch_assoc($res);
	$fieldids = explode(";",$rw['traitids']);
	$mjvals = unserialize($_SESSION['taxatraitsvar']);
	$newmjvals =$mjvals;
	$i=0;
	foreach ($mjvals as $tk => $tv) {
		$valores = $tv;
		if ($valores) {
		foreach ($valores as $kk => $vv) {
			$tt = explode("_",$kk);
			//SE A VARIAVEL ESTIVER NO FORMULARIO
			if (in_array($tt[1],$fieldids)) {
				//SE A VARIAVEL É UMA IMAGEM APAGA AS FIGURAS SALVAS TEMPORARIAMENTE
				if ($tt[0]=='trait') { 
					$filearr = explode(";",$vv);
					foreach ($filearr as $kyk => $vyv) {
						$fildel = trim($vyv);
						$qqq = "SELECT TraitVariation FROM Traits_variation WHERE TraitID='".$tt[1]."' AND ".$typeid."='".$idd."' AND BibkeyID='".$bibkey."'";
						$rr = mysql_query($qqq,$conn);
						$nrr = mysql_numrows($rr);
						if ($nrr>0) {
							$rw = mysql_fetch_assoc($rr);
							$oldarr = explode(";",$rw['TraitVariation']);
							if (!in_array($vyv,$oldarr)) {
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
		$newmjvals[$tk] = $valores;
	}
	$_SESSION['taxatraitsvar'] = serialize($newmjvals);
	$valores = $newmjvals["bibkey_".$bibkey];
	@extract($valores);
} 
$aa = unserialize($_SESSION['taxatraitsvar']);
$aa = $aa["bibkey_".$bibkey];
@extract($aa);
///SE ESTIVER SALVANDO OU JÁ CADASTROU AS IMAGENS DEPOIS DE SALVAR
if ($option1=='2' || isset($imgdone)) {
	//echo "ESTOU ENTRANDO AQUI<br/ >";
	//SE NAO IMPORTOU AINDA IMAGENS, ENTAO FAZ ISSO
	if (!isset($imgdone)) {
		$arval = $_POST;
		unset($arval['MAX_FILE_SIZE'],  
			$arval['formid' ],  
			$arval['option1'],  
			$arval['typeid'],  
			$arval['idd'], 
			$arval['taxanome'], 
			$arval['formname'],  
			$arval['traitids'],
			$arval['final'],
			$arval['traitsinenglish'],
			$arval['bibkey'],
			$arval['elementid2'],
			$arval['arrofbibkeyids'],
			$arval['taxanome']
			);
		if (isset($_SESSION['taxatraitsvar'])) {
			$vars = unserialize($_SESSION['taxatraitsvar']);
			$variaveis = $vars["bibkey_".$bibkey];
		} else {
			$variaveis = array();
		}
		//COMBINA ESTADOS DE VARIACAO DE VARIAVEL CATEGORICA EM UM UNICO STRING PARA ARMAZENAMENTO (CONCATENA)
		$result = array();
		foreach ($arval as $key => $value) {
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
	
		//JUNTA OS VALORES CONCATENADOS PARA O ARRAY DE RESULTADOS  E IMAGENS NOVAS ENVIADAS SE FOR O CASO
		$arval = array_merge((array)$arval,(array)$result,(array)$_FILES);
		if (count($variaveis)>0) {
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
		}
		//echopre($variaveis);
		//armazena os dados novos e do novo relatorio a variavel de sessao variation....
		$newimagefile = array();
		//para cada variavel no array
		foreach ($arval as $key => $value) {
			$ttt = explode("_",$key);
			//se nao É uma imagem ou um array com info de imagem, simplesmente pega o valor e adiciona ao array
			if (!is_array($value) && $ttt[0]!='traitimg' && $ttt[0]!='traitmulti' && $ttt[0]!='traitimgautor' && $ttt[0]!='imagid' && $ttt[0]!= 'traitimgold' && $ttt[0]!= 'traitimgautortxt' && $ttt[0]!='imgtodel' ) { 
				//se ela ja existe na variavel de sessao atualiza
				if (@array_key_exists($key,$variaveis)) { 
					$vv = $variaveis[$key];
					$variaveis[$key]=$value;
				} 
				//ou entao adiciona no array
				else { 
					$nar = array($key => $value);
					$variaveis = array_merge((array)$variaveis,(array)$nar);
				}
			} 
			else { //se for uma imagem entao e um array que contem as info das images
				$fname = trim($value['name']);
				if (!empty($fname) && $ttt[0]!='traitimg' && $value['error']==0 && is_array($value)) {
					$ak = "traitimgautor_".$ttt[1];
					$fotografo = $arval[$ak];
	
					$ccid = explode("_",$key); //extrai o numero da variavel
					$filedate = $_SESSION['sessiondate']; //a data de hoje
					$fva = $filedate."_charid".$ccid[1]."_".$value['name']; //pega o nome do arquivo no diretorio temporario
	
					//CHECA SE A IMAGEM JA NAO EXISTE (CASO NAO ESTEJA APAGADA)
					$qq = "SELECT * FROM Imagens WHERE Name='".$fva."' AND Deleted=0";
					$qr = @mysql_query($qq,$conn);
					$nqr = @mysql_numrows($qr);
					if ($nqr==0) {
						move_uploaded_file($value["tmp_name"],"img/temp/$fva");  //move o arquivo para a pasta final MAS AQUI PODERIA SER TEMPORARIO PORQUE AINDA NAO GRAVOU OS DADOS
						/////////////////////////////
						$ext = explode(".",$value['name']);
						$ll = count($ext)-1;
						$imgext = strtoupper($ext[$ll]);
						$inputfile = "img/temp/$fva";
						//TIPOS DE IMAGEM ACEITOS (VERIFICAR OUTRAS POSSIBILIDADES)
						if ($imgext=='JPG' || $imgext=='TIFF' || $imgext=='TIF' || $imgext=='JPEG' || $imgext=='PNG') {
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
						} 
						else {
							$nar = array($kkk => $newimg);
							$variaveis = array_merge((array)$variaveis,(array)$nar);
						}
					}
				}
			} 
			elseif ($ttt[0]=='traitimg') {
					$ttid  = $ttt[1];
					$olimgvals = trim($variaveis['trait_'.$ttid]);
					//echo "<br>aqui entao ".$variaveis['trait_'.$ttid];
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
						//echo "    aqui valores novos ".$variaveis['trait_'.$ttid]."<br>";
					}
	
				}
			}
		}
		//echopre($variaveis);
		$oldvvv =  @unserialize($_SESSION['taxatraitsvar']);
		$akk = @array_keys($oldvvv);
		if (@in_array($bibkey,$akk)) {
			$oldvvv["bibkey_".$bibkey] = $variaveis;
		} else {
			$oldvvv = array_merge((array)$oldvvv,(array)array("bibkey_".$bibkey => $variaveis));
			$bibkeyidsofmonitor[] = $bibkey;
		}
		$_SESSION['taxatraitsvar'] = serialize($oldvvv);
			//SE SUBIU IMAGENS, GERA OS THUMNAILS E RETORNA PARA ESTA PÁGINA
			if (count($newimagefile)>0) {
				$_SESSION['taxa_newimagfiles'] = serialize($newimagefile);
				$_SESSION['taxa_othervars'] = array(
				'formid' => $_POST['formid'],
				'typeid' => $_POST['typeid'],
				'idd' => $_POST['idd'],
				'taxanome' => $_POST['taxanome']
				);
				$zz = explode("/",$_SERVER['SCRIPT_NAME']);
				$serv = $_SERVER['SERVER_NAME'];
				$returnto = $serv."/".$zz[1]."/traits_coletortaxa.php";
				header("location: http://".$serv."/cgi-local/imagick_function.php?returnto=".$returnto."&folder=".$zz[1]."&returnvar=imgdone");
				} 
		} 
		//SE JA CHECOU IMAGENS, ENTAO APENAS EXTRAI ELAS E OUTRAS VARIAVEIS
		else { //if imagedone
			unset($_SESSION['taxa_newimagfiles']);
			extract($_SESSION['taxa_othervars']);
			$oldvvv = unserialize($_SESSION['taxatraitsvar']);
			$variaveis =$oldvvv["bibkey_".$bibkey];
		}
		//ACHAR 
		FazHeader($title,$body,$which_css,$which_java,$menu);
		
		if ($final==1) {
			echo "
<form action='traits_coletortaxa.php'  method='post'>
  <input type='hidden' name='typeid' value='".$typeid."' />
  <input type='hidden' name='idd' value='".$idd."' />
  <input type='hidden' name='taxanome' value='".$taxanome."' />
  <input type='hidden' name='concluir' value='1' />
<br><table class='sucessosmall' align='center' cellpadding='7'>
  <tr><td >A variação entrada para a referência ".$bibkey." foi salva temporariamente. </td></tr>
  <tr><td><input type='submit' value='Salvar e fechar a janela' class='bsubmit' /></td></tr>
</table>
</form>
<br>
	";
		} 
		if ($final==2) {
			echo "
<form action='traits_coletortaxa.php'  method='post'>
  <input type='hidden' name='typeid' value='".$typeid."' />
  <input type='hidden' name='idd' value='".$idd."' />
  <input type='hidden' name='taxanome' value='".$taxanome."' />
  <input type='hidden' name='concluir' value='1' />
  <script language=\"JavaScript\">
    setTimeout(
      function() {
        this.form.submit();
      }
      ,0.0001);
  </script>
</form>";
		}
} 
//CASO ESTEJA EDITANDO
else {
	FazHeader($title,$body,$which_css,$which_java,$menu);
}
///////////////////////////////////////
//EXTRAI MULTISTATES - SE HÁ VARIAVEIS CATEGORICAS PARA AS QUAIS MULTIPLOS ESTADOS DE VARIACAO SAO ACEITOS, ENTAO EXTRAIR OS VALORES DE CADA ESTADO SELECIONADO
if (isset($_SESSION['taxatraitsvar']) && !empty($bibkey)) {
	//echopre($_SESSION['taxatraitsvar']);
	$oldvvv = unserialize($_SESSION['taxatraitsvar']);
	$variaveis =$oldvvv["bibkey_".$bibkey];
	if (count($variaveis)>0) {
	foreach ($variaveis as $key => $value) {
		//echo $key."  ".$value."<br>";
		$arraykey = explode("_",$key); 
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		if ($varorunit=='traitvar') {
			$qq = "SELECT * FROM Traits WHERE TraitID='".$charid."'";
			$nch = mysql_query($qq,$conn);
			$rwch = mysql_fetch_assoc($nch);
			//se for um estado de variacao de uma variavel categorica
			if ($rwch['TraitTipo']=='Variavel|Categoria' && strtoupper($rwch['MultiSelect'])=='SIM') {
				if (!empty($value)) {
					$arrstates = explode(";",$value);
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
	}
} 
//MOSTRA O FORMULÁRIO
//echo "aqui";
//echopre($bibkeyidsofmonitor);
echo "
<table class='myformtable' align='left' cellpadding='7' cellspacing='0'> 
<thead>
<tr><td >Variáveis de usuário associadas ao Taxon ".$taxanome."</td></tr>
</thead>
<tbody>
<form name='inicioform' action='traits_coletortaxa.php' method='post'  >
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='traitsinenglish' value='".$traitsinenglish."' />
  <input type='hidden' name='typeid' value='".$typeid."' />
  <input type='hidden' name='idd' value='".$idd."' />
  <input type='hidden' name='taxanome' value='".$taxanome."' />
  <input type='hidden' name='arrofbibkeyids' value='".serialize($bibkeyidsofmonitor)."' />
<tr>
  <td >
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameformulario')."</td>
        <td class='tdsmallbold' colspan=2>
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
				$qq = "SELECT * FROM Formularios WHERE FormName!='Habitat' AND (AddedBy=".$_SESSION['userid']." OR Shared=1) ORDER BY FormName ASC";
				$rr = mysql_query($qq,$conn);
				while ($row= mysql_fetch_assoc($rr)) {
					echo "
            <option value='".$row['FormID']."'>".$row['FormName']."</option>";
				}
			echo "
          </select>
      </td>
    </tr>
    <tr>
            <td class='tdsmallbold'>Referência bibliográfica</td>
            <td>
            <select name='bibkey' onchange=\"this.form.submit();\" >";
            if (!isset($bibkey) || $bibkey==0) {
echo "
                <option value='' >".GetLangVar('nameselect')."</option>";
            }
            $sql  = "SELECT BibID,FirstAuthor,Year,Title FROM `BiblioRefs` ORDER BY FirstAuthor,Year";
			$rsql = mysql_query($sql,$conn);
			while($rwsql = mysql_fetch_assoc($rsql)) {
				$bibid = $rwsql['BibID'];
				if ($bibid==$bibkey) {
					$txt = "selected";
				} else {
					$txt = "";
				}
				$bibtxt = ucfirst($rwsql['FirstAuthor'])." ".$rwsql['Year']." - ".$rwsql['Title'];
				$bibtxt = substr($bibtxt,0,40);
				if (in_array($bibid, $bibkeyidsofmonitor, true)) {
					$estilo = "style=\"background-color: yellow;\"";
					$bibtxt = "**".$bibtxt;
				} else {
					$estilo = "";
				}
				echo "
                <option  ".$txt." value='".$bibid."'  ".$estilo." >".$bibtxt."</option>";
            }
echo "
            </select>
            </td>
            <td class='tdformnotes' >** referência com dados para ".$taxanome."</td> 
          </tr>
  </table>
  </td>
</tr>
</form>";
//PRINT SELECT LINK LEVEL



if (!empty($formid) && !empty($bibkey)) {
	if ($traitsinenglish==1) {
		$flag = "brasilFlagicon.png";
		$flagval = 0;
	} else {
		$flag = "usFlagicon.png";
		$flagval = 1;
	}
echo "
<form name='variationform' action='traits_coletortaxa.php' method='post'>
  <input type='hidden' name='formid' value='".$formid."' />
  <input type='hidden' name='bibkey' value='".$bibkey."' />
  <input type='hidden' name='typeid' value='".$typeid."' />
  <input type='hidden' name='idd' value='".$idd."' />
  <input type='hidden' name='taxanome' value='".$taxanome."' />  
  <input type='hidden' name='arrofbibkeyids' value='".serialize($bibkeyidsofmonitor)."' />
  <input id='chglang' type='hidden' name='traitsinenglish' value='' />
<tr><td  align='right' ><input type='image' height='30' src=\"icons/".$flag."\" 
onclick=\"javascript:document.getElementById('chglang').value=".$flagval."\" /></td></tr>
</form>
<tr>
  <td align='center' >
<form id='varform2' method='post' enctype='multipart/form-data' action='traits_coletortaxa.php' >
  <input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
  <input type='hidden' name='formid' value='".$formid."' />
  <input type='hidden' name='bibkey' value='".$bibkey."' />
  <input type='hidden' name='traitsinenglish' value='".$traitsinenglish."' />
  <input type='hidden' name='arrofbibkeyids' value='".serialize($bibkeyidsofmonitor)."' />
  <input type='hidden' name='option1' value='2' />
  <input type='hidden' name='typeid' value='".$typeid."' />
  <input type='hidden' name='idd' value='".$idd."' />
  <input type='hidden' name='taxanome' value='".$taxanome."' />
  ";
  include "traits_generalform.php";
echo "
  </td>
</tr>
<tr>
  <td>
    <table align='center'>
      <tr>
        <input type='hidden' id='final' name='final' value='' />
        <td align='center' ><input type=submit value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.getElementById('final').value='1'\" /></td>
        <!---<td align='center'><input type=submit value='".GetLangVar('nameconcluir')."' class='bblue' onclick=\"javascript:document.getElementById('final').value=2\" /></td>--->
</form>
<form action='traits_coletortaxa.php' method='post'>
  <input type='hidden' name='formid' value='".$formid."' />
  <input type='hidden' name='bibkey' value='".$bibkey."' />
  <input type='hidden' name='resetar' value='1' />
  <input type='hidden' name='typeid' value='".$typeid."' />
  <input type='hidden' name='idd' value='".$idd."' />
  <input type='hidden' name='taxanome' value='".$taxanome."' />
  <input type='hidden' name='traitsinenglish' value='".$traitsinenglish."' />
  <input type='hidden' name='arrofbibkeyids' value='".serialize($bibkeyidsofmonitor)."' />
<td align='left'><input type='submit' value='".GetLangVar('namereset')."' class='breset' /></td>
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
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
}
?>