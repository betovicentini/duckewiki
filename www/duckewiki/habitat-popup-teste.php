<?php
set_time_limit(0);
session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";
//include_once("functions/class.Numerical.php") ;

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || (trim($uuid)=='')) {
//if ($uuid!=1) {
	header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);


//if (count($_FILES)>0) { echopre($_FILES); }

if (!empty($elementidtxt)) { 
	$body= GetLangVar('namehabitat');
	$title = GetLangVar('namenovo')." ".GetLangVar('namehabitat');
	PopupHeader($title,$body);
} 
else {
	$body = '';
	$title = GetLangVar('namehabitat');
	$which_css = array(
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/geral.css\" />",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/cssmenu.css\" />",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/autosuggest.css\" />",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/Stickman.MultiUpload.css\" />",
"<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"magiczoomplus/magiczoomplus/magiczoomplus.css\" />"
);
	$which_java = array(
"<script type=\"text/javascript\" src=\"javascript/sorttable/common.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/sorttable/css.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/sorttable/standardista-table-sorting.js\"></script>",
"<script type=\"text/javascript\" src=\"css/cssmenuCore.js\"></script>",
"<script type=\"text/javascript\" src=\"css/cssmenuAddOns.js\"></script>",
"<script type=\"text/javascript\" src=\"css/cssmenuAddOnsItemBullet.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/ajax_framework.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/mootools.js\"></script>",
"<script type=\"text/javascript\" src=\"javascript/Stickman.MultiUpload.js\"></script>",
"<script type=\"text/javascript\" src=\"magiczoomplus/magiczoomplus/magiczoomplus.js\"></script>"
);
	newheader($title,$body,$which_css,$which_java,TRUE);
}
/////////////////
if ($justselect==1 && $pophabitatid>0) {
$habitatdesc = describehabitat($pophabitatid,$img=FALSE,$conn);
echo "
<form name='myform' >
  <script language=\"JavaScript\">
      setTimeout(
          function() {
            var element = self.opener.document.getElementById('".$elementidtxt."');
            element.innerHTML = '".($habitatdesc)."';
            var destination = self.opener.document.getElementById('".$elementidval."');
            destination.value = '$pophabitatid';
            window.close();
            } ,0.0001);
    </script>
</form>
";
} 
else {

////pega as variáveis recém enviadas////
if (isset($finnal) || isset($imgdone)) {
 	if (!isset($imgdone) && $habitattipo=='Local') {
	$arval = $_POST;
	$othervars = array();
	foreach ($arval as $kk => $arv) {
		if (substr($kk,0,5)!='trait' && substr($kk,0,8)!='imgtodel') {
			$othervars[$kk]=$arv;
			unset($arval[$kk]);
		} 
	}
	unset($arval['traitsinenglish']);
	if (isset($_SESSION['habitatvariation'])) {
		$variaveis = unserialize($_SESSION['habitatvariation']);
	} 
	else {
		$variaveis = array();
	}

	//COMBINA ESTADOS DE VARIACAO DE CATEGORIA EM UMA UNICA STRING PARA ARMAZENAMENTO

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

	//junta os novos valores para o array de resultados e imagens se estas tiverem sido postas
	$arval = array_merge((array)$arval,(array)$result,(array)$_FILES);

	//echopre($variaveis);
	//armazena os dados novos e do novo relatorio a variavel de sessao variation....
	$newimagefile = array();
	//echopre($arval);
	foreach ($arval as $key => $value) {	//para cada variavel no array
		$ttt = explode("_",$key);
		if (!is_array($value) && $ttt[0]!='traitimg' && $ttt[0]!='traitmulti' && $ttt[0]!='traitimgautor' && $ttt[0]!='imagid' && $ttt[0]!= 'traitimgold' && $ttt[0]!= 'traitimgautortxt' && $ttt[0]!='imgtodel' ) { //se nao e uma imagem ou array com info de imagem
			if (@array_key_exists($key,$variaveis)) { //se ela ja existe na variavel de sessao atualiza se diferente
				$vv = $variaveis[$key];
				if ($vv!=$value) {
					$variaveis[$key]=$value;
				}
			} else { //ou entao adiciona no array
				$nar = array($key => $value);
				$variaveis = array_merge((array)$variaveis,(array)$nar);
			}
		} 
		else { //se for uma imagem entao e um array que contem as info das images
			$fname = trim($value['name']);

			if (!empty($fname) && $ttt[0]!='traitimg' && $value['error']==0 && is_array($value)) {
				$ak = "traitimgautor_".$ttt[1];
				//echo $ak."<br/>";
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
				$inputfile = "img/temp/$fva";
				if ($imgext=='JPG' || $imgext=='TIFF' || $imgext=='TIF' || $imgext=='JPEG') {
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
					$copiado = copy($inputfile,"img/originais/".$fva);
					if ($copiado) {
						unlink($inputfile);
						$newimagefile[] = $fva;
					} else {
						echo "copy($inputfile,img/originais/.$fva)";
					}
					$kkk = 'trait_'.$ccid[1];
					if (array_key_exists($kkk,$variaveis)) { //se ela ja existe na variavel de sessao atualiza se diferente
						$imgarrs = explode(";",$variaveis[$kkk]);
						foreach ($imgarrs as $mykk => $myvv) {
							$mtv = $myvv+0;
							if ($mtv==0) {
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
		} 
			elseif ($ttt[0]=='traitimg') {
				$ttid  = $ttt[1]+0;
				$olimgvals = $variaveis['trait_'.$ttid];
				if (!empty($olimgvals)) {
					$valoresvelhos = explode(";",$variaveis['trait_'.$ttid]);
					//echopre($valoresvelhos);
					$novosvalores = array();
					foreach ($valoresvelhos as $kvel => $vvimg) {
						$vvimg = $vvimg+0;
						if ($vvimg>0) {
							$imgtodel = $arval["imgtodel_".$ttid."_".$vvimg];
							if ($imgtodel==1) {
								//unset($valoresvelhos[$kvel]);
								//echo "apagando imagem";
								$dataa = date("Y-m-d");
								$fieldsaskeyofvaluearray = array('Deleted' => $dataa);
								CreateorUpdateTableofChanges($vvimg,'ImageID','Imagens',$conn);
								UpdateTable($vvimg,$fieldsaskeyofvaluearray,'ImageID','Imagens',$conn);
								unset($arval["imgtodel_".$ttid."_".$vvimg]);
								//unset($arval["imagid_".$ttid."_".$vvimg]);
							} else {
								$novosvalores = array_merge((array)$novosvalores,(array)array($vvimg));
							}
						}
					}
					//echopre($novosvalores);
					$variaveis['trait_'.$ttid] = implode(";",$novosvalores);
				}

			}
		}
	}
		unset( $_SESSION['habitatvariation']);


	///////////////////////////////////////
		//EXTRAI MULTISTATES//
		foreach ($variaveis as $key => $value) {
		$arraykey = explode("_",$key); 
		$charid = $arraykey[1];
		$varorunit = $arraykey[0];
		if ($varorunit=='traitvar') {
			$qq = "SELECT * FROM Traits WHERE TraitID=".$charid;
			$nch = mysql_query($qq,$conn);
			$rwch = mysql_fetch_assoc($nch);
			if ($rwch['TraitTipo']=='Variavel|Categoria' && strtoupper($rwch['MultiSelect'])=='SIM') { //se for um estado de variacao de uma variavel categorica
				if (!empty($value)) {
					$arrstates = explode(";",$value);
					//echo "states array<br/>";
					//echopre($arrstates);
					foreach ($arrstates as $stateval) {
							$stv = $stateval+0;
							if ($stv>0) {
								$keystate = "traitmulti_".$charid."_".$stv;
								$nar = array($keystate => $stv);
								if (array_key_exists($keystate,$variaveis)) {
									$variaveis[$keystate]= $stv;
								} else {
									$variaveis = array_merge((array)$variaveis,(array)$nar);
								}
							}
					}
				}
			}
		} //end for each
	}

	/////////////////////
		$_SESSION['habitatvariation'] = serialize($variaveis);
		if (count($newimagefile)>0) {
			$_SESSION['newimagfiles'] = serialize($newimagefile);
			$_SESSION['outrasvars'] =  serialize($othervars);
			$zz = explode("/",$_SERVER['SCRIPT_NAME']);
			$serv = $_SERVER['SERVER_NAME'];
			$returnto = $serv."/".$zz[1]."/habitat-popup-teste.php";
			header("location: http://".$serv."/cgi-local/imagick_function.php?returnto=".$returnto."&folder=".$zz[1]."&returnvar=imgdone");
			//echo "location: http://".$serv."/cgi-local/imagick_function.php?returnto=".$returnto."&amp;folder=".$zz[1]."&amp;returnvar=imgdone";
		}
		@extract($variaveis);
	} 
	elseif (isset($imgdone)) { //if imagedone
		unset($_SESSION['newimagfiles']);
		$othervars = unserialize($_SESSION['outrasvars']);
		@extract($othervars);
		$variaveis = unserialize($_SESSION['habitatvariation']);
		$finnal=3;
		@extract($variaveis);
	}
}
////SE SALVANDO CHECA SE OS REQUISITOS MINIMOS FORAM CUMPRIDOS
$erro =0;
if ($finnal>0 && $finnal<3 && (!isset($classnameok) || empty($classnameok))) { 
	//se for uma classe
	if ($habitattipo=='Class') {
		if (empty($habitatname)) {
			$erro++;
echo "
<br/>
<table class='erro' align='center' cellpadding='7'>
  <tr><td >".GetLangVar('erro1')."</td></tr>
  <tr><td >".GetLangVar('namenome')." ".GetLangVar('namehabitat')."</td></tr>

</table>
<br/>";
		}
		if ($pophabitatid==0 || !isset($pophabitatid)) {
			$habitawords = explode(" ",$habitatname);
			$qq = "SELECT CONCAT(IF(child.HabitatTipo='Local',parent.PathName,UPPER(child.PathName)),IF(child.GPSPointID>0 OR child.LocalityID>0,CONCAT('  (',IF(child.GPSPointID>0,CONCAT(gpsgaz.PathName,'  GPS-Pt-',gps.Name),gaz.PathName),')'),''))  as nome,child.HabitatID, child.HabitatTipo FROM Habitat as child LEFT JOIN Gazetteer as gaz ON LocalityID=gaz.GazetteerID LEFT JOIN Habitat as parent ON parent.HabitatID=child.ParentID LEFT JOIN GPS_DATA as gps ON child.GPSPointID=gps.PointID LEFT JOIN Gazetteer as gpsgaz ON gps.GazetteerID=gpsgaz.GazetteerID";
			$j=0;
			foreach ($habitawords as $vv) {
				$vv = trim(strtolower($vv));
				if ($j==0) {
					$qq .= "WHERE child.Habitat LIKE '%".$vv."%'";
				} else {
					$qq .= " OR child.Habitat LIKE '%".$vv."%'";
				}
				$j++;
			}
			$similarhabt = mysql_query($qq,$conn);
			$nsimilar = @mysql_numrows($similarhabt);
			if ($nsimilar>0) {
				$erro++;
				echo "
<br/>
<table class='erro' align='center' cellpadding='7'>
  <tr><td >As seguintes CLASSES de hábitat já cadastradas contém partes do nome da sua nova classe.</td></tr>";
				while ($sr = mysql_fetch_assoc($similarhabt)) {
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "  <tr bgcolor ='".$bgcolor."'><td >".$sr['nome']."</td></tr>";
  }
				if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
				echo "
  <tr bgcolor ='".$bgcolor."'><td  colspan='100%' align='center' ><input type='button' class='bred' onclick=\"javascript:document.myformvar.classnameok.value=1; document.myformvar.submit();\" value='Ignorar e proceder com o cadastro'></td></tr>
</table>
<br/>";
			}
		}
	} 
	elseif ($habitattipo=='Local') {
		$ppid = $parentid+0;
		$locid = $gpspointid+$gazetteerid+0;
		//CAMPOS OBRIGATÓRIOS
		if ($ppid==0 || $locid==0) {
			$erro++;
	echo "
<br/>
<table class='erro' align='center' cellpadding='7'>
  <tr><td>".GetLangVar('erro1')."</td></tr>";
	if ($ppid==0) {
		echo "
  <tr><td class='tdformnotes'>".mb_strtolower(GetLangVar('habitatclasse'))."</td><tr>";
	}
	if ($locid==0) {
		echo "
  <tr><td class='tdformnotes'>".mb_strtolower(GetLangVar('namelocalidade'))."</td><tr>";
	}
	echo "
</table>
<br/>";
		}
		$hbid = $pophabitatid+0;
		if ($hbid==0 && $locid>0) {
			if (($gpspointid+0)>0) {
				$qq = "SELECT * FROM `Habitat` WHERE `GPSPointID`='".$gpspointid."'";
			} 
			elseif (($gazetteerid+0)>0) {
				$qq = "SELECT * FROM `Habitat` WHERE `LocalityID`='".$gazetteerid."'";
			}
			$teste = mysql_query($qq,$conn);
			$rdo = mysql_numrows($teste);
			if ($rdo>0) {
				if ($rdo==1) {
				$rtw = mysql_fetch_assoc($teste);
				$pophabitatid = $rtw['HabitatID'];
				}
				else {
				unset($pophabitatid);
				}
				$erro++;
				unset($_SESSION['habitatvariation']);
				unset($_SESSION['othervars']);
				unset($_SESSION['newimagfiles']);
				echo "
<br/>
<form action='habitat-popup-teste.php' method='post'>
    <input type='hidden' name='elementidval' value='$elementidval' />
    <input type='hidden' name='elementidtxt' value='$elementidtxt' />
    <input type='hidden' name='pophabitatid' value='$pophabitatid' />
    <input type='hidden' name='opening' value='1' />
<table class='erro' align='center' cellpadding='7' width='50%'>
  <tr><td>Já existe um hábitat para a localidade selecionada.<BR/>Cada localidade só pode ter 1 hábitat local.<br/>Você deve EDITAR para adicionar variáveis</td></tr>
  <tr><td align='center'><input type='submit' value='".GetLangVar('nameeditar')."' class='breset' /></td></tr>
</table>
</form>
<br/>";
			}
		}
	} 
}

///SE FOR EDITAR OR ESTIVER CRIANDO
if (!isset($finnal) || $finnal==3 || empty($finnal) || $erro>0) {
	if (!isset($finnal) && !isset($imgdone)) {
		unset($_SESSION['habitatvariation']);
		unset($_SESSION['othervars']);
		unset($_SESSION['newimagfiles']);
	}
	#pega dados antigos se houver
	if (is_numeric($pophabitatid) && $pophabitatid>0 && !isset($finnal)) {
		$qq = "SELECT  CONCAT(IF(child.HabitatTipo='Local',parent.PathName,UPPER(child.PathName)),' ',IF(child.GPSPointID>0,CONCAT(gpsgaz.PathName,'  GPS-Pt-',gps.Name),
IF(child.LocalityID>0,gaz.PathName,''))) as nome,child.HabitatID, child.HabitatTipo,child.ParentID,child.LocalityID,child.Descricao,child.Habitat,child.GPSPointID,child.EspeciesIds FROM Habitat as child LEFT JOIN Gazetteer as gaz ON child.LocalityID=gaz.GazetteerID LEFT JOIN Habitat as parent ON parent.HabitatID=child.ParentID LEFT JOIN GPS_DATA as gps ON child.GPSPointID=gps.PointID LEFT JOIN Gazetteer as gpsgaz ON gps.GazetteerID=gpsgaz.GazetteerID WHERE child.HabitatID='".$pophabitatid."'";
		$teste = mysql_query($qq,$conn);
		$rrr = mysql_fetch_assoc($teste);
		$habitattipo = $rrr['HabitatTipo'];
		$habitatname  = $rrr['Habitat'];
		$parentid  = $rrr['ParentID'];
		$habitatdefinicao  = $rrr['Descricao'];
		$specieslistids = $rrr['EspeciesIds'];
		//echo "aqui:".$rrr['HabitatID']."  specieslistids:".$specieslistids;
		$gazetteerid = $rrr['LocalityID'];
		$gpspointid  = $rrr['GPSPointID'];
		if ($gpspointid>0) {
			$gpspt  = $rrr['nome'];
		}
		if ($gazetteerid>0) {
			$locality  = $rrr['nome'];
		}
		if ($habitattipo=='Class') { $habitatn = $habitatname;} else { $habitatn=  $rrr['nome'];} 
		$oldvals = getoriginalhabitat($pophabitatid,$conn);
		unset(
			$oldvals['habitattipo'],
			$oldvals['parentid'],
			$oldvals['habitatname'],
			$oldvals['habitatdefinicao'],
			$oldvals['gazetteerid'],
			$oldvals['gpspointid'],
			$oldvals['specieslistids']);
		@extract($oldvals);
		$_SESSION['habitatvariation'] = serialize($oldvals);
		//echopre($oldvals);
	} 
	if (!empty($specieslistids)) {
		$specieslist = strip_tags(describetaxacomposition($specieslistids,$conn,$includeheadings=TRUE));
	}
if ((!isset($justselect) || ($justselect==2 && $pophabitatid==0)) && (!isset($habitattipo) || empty($habitattipo) || $opening==1)) {
if (!empty($elementidtxt)) {   
	$hed = 'Selecione ou edite um hábitat cadastrado';
} 
else {
	$hed = 'Editar um hábitat cadastrado';
}
echo "
<br/>
<table class='myformtable' align='left' cellpadding='7'>
<thead>
<tr>
  <td colspan='100%'>".GetLangVar('namehabitat')."&nbsp;<img height=\"15\" src=\"icons/icon_question.gif\" ";
  $help = "Um registro de HABITAT permite cadastrar classes de habitat ou associar à uma localidade um conjunto de variáveis ecológicas. Assim, um registro de HABITAT pode ser associado a 1 ou mais amostras e/ou plantas marcadas.";
echo " onclick=\"javascript:alert('$help');\" alt=\"Leia-me\" /></td>
</tr>
<tr class='subhead'><td align='center'>$hed</td></tr>
</thead>
<tbody>
<tr>
  <td >
<form name='finalform' action='habitat-popup-teste.php' method='post'>
  <input type='hidden' name='elementidval' value='$elementidval' />
  <input type='hidden' name='elementidtxt' value='$elementidtxt' />
    <input type='hidden' name='justselect' value='' />
    <table align='left' cellpadding=\"3\" cellspacing=\"0\" class='tdformnotes'>
      <tr>
        <td class='tdformnotes'>"; 
          if (empty($habitatn)) { $habitatn="Digite aqui para buscar";}
          autosuggestfieldval3('search-habitat.php','habitatn',$habitatn,'habitatres','pophabitatid',$pophabitatid,true,60); 
          
echo "
          <input type='hidden' name='final' value='' />
        </td>";
if (!empty($elementidtxt)) {        
echo "
        <td align='center' ><input type='submit' value='".GetLangVar('nameselecionar')."' class='bsubmit' onclick=\"javascript:document.finalform.justselect.value=1\" /></td>";
}    
echo "
        <td align='center' ><input type='submit' value='".GetLangVar('nameeditar')."' class='borange' onclick=\"javascript:document.finalform.justselect.value=2\" /></td>
      </tr>
    </table>
</form>    
  </td>
</tr>
<tr class='subhead'><td>Cadastrar um novo habitat</td></tr>
<tr>
  <td >
<form action='habitat-popup-teste.php' method='post'>
  <input type='hidden' name='elementidval' value='$elementidval' />
  <input type='hidden' name='elementidtxt' value='$elementidtxt' />  
    <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameselect')."</td>
        <td>
          <table>
            <tr>
              <td ><input type='radio'  name='habitattipo' value='Class' onchange='this.form.submit()' />&nbsp;".GetLangVar('habitatclasse')."&nbsp;<img height=\"15\" src=\"icons/icon_question.gif\" ";
			$help =  "Uma CLASSE de habitat é uma categoria que define um tipo de ambiente, seja uma categoria formal de um sistema de classificação como por exemplo Floresta Ombrófila Densa e suas subdivisões, ou uma categoria informal como Terra Firme.";
			echo " onclick=\"javascript:alert('$help');\" alt=\"Leia-me\" /></td>
			  <td>&nbsp;&nbsp;</td>
              <td ><input type='radio' name='habitattipo' value='Local' onchange='this.form.submit()' />&nbsp;".GetLangVar('habitatlocal');
			echo "&nbsp;<img height=\"15\" src=\"icons/icon_question.gif\" ";
			 $help = "Um HABITAT LOCAL é definido por um conjunto de variáveis ecológicas associadas à uma localidades específica, seja um ponto no espaço geográfico ou uma localidade nominal como por exemplo uma parcela, uma clareira, etc.";
			echo " onclick=\"javascript:alert('$help');\"  alt=\"Leia-me\" /></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
</form>
  </td>
</tr>
</tbody>
</table>
";
} 
else {
echo "
<br/>
<form id=\"varform2\" name=\"myformvar\" method=\"post\" enctype=\"multipart/form-data\" action=\"habitat-popup-teste.php\" >
	<input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
	<input type='hidden' name='pophabitatid' value='".$pophabitatid."' />
    <input type='hidden' name='habitattipo' value='".$habitattipo."' />
	<input type='hidden' name='elementidval' value='".$elementidval."' />
	<input type='hidden' name='elementidtxt' value='".$elementidtxt."' />
	<input type='hidden' name='classnameok' value='' />
<table class='myformtable' align='left' cellpadding='7'>
<thead>	
<tr>
  <td>";
  if ($habitattipo=='Local') {
    if ($justselect==2 || $pophabitatid>0) {
      $btn = GetLangVar('nameeditando');
    } else {
      $btn = GetLangVar('namenovo');
    }
    echo $btn." ".GetLangVar('habitatlocal')."&nbsp;<img height=\"15\" src=\"icons/icon_question.gif\" ";
      $help = "Um HABITAT LOCAL é definido por um conjunto de variáveis ecológicas associadas à uma localidades específica, seja um ponto no espaço geográfico ou uma localidade nominal como por exemplo uma parcela, uma clareira, etc.";
      echo " onclick=\"javascript:alert('$help');\" alt=\"Leia-me\" />";
  } 
  elseif ($habitattipo=='Class') {
    if ($justselect==2) {
      $btn = GetLangVar('nameeditando');
    } else {
      $btn = GetLangVar('namenova');
    }
    echo $btn." ".GetLangVar('habitatclasse')."&nbsp;<img height=\"15\" src=\"icons/icon_question.gif\" ";
    $help = "Uma CLASSE de habitat é uma categoria que define um tipo de ambiente, seja uma categoria formal de um sistema de classificação como por exemplo Floresta Ombrófila Densa e suas subdivisões, ou uma categoria informal como Terra Firme.";
    echo " onclick=\"javascript:alert('$help');\" alt=\"Leia-me\" />";
  }    
echo "</td>
</tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor ='".$bgcolor."'>";
    if ($habitattipo=='Class') {
echo "
    <td colspan='100%'>
      <table>
        <tr>
          <td class='tdformleft' style=\"color: #990000;\">".GetLangVar('namenome')."*</td>
          <td><input type='text' name='habitatname' size=30 value='$habitatname'></td>
        </tr>
      </table>
    </td>
      ";
      } 
      else {

echo "
    <td colspan='100%'>
      <table>
        <tr>
          <td class='bold' >".GetLangVar('nameformulario')."</td>
          <td >
            <select name='formid' onchange=\"javascript:document.getElementById('finnal').value=3; this.form.submit();\">";
					if (!empty($formid)) {
					$qq = "SELECT * FROM Formularios WHERE FormID='".$formid."'";
					$rr = mysql_query($qq,$conn);
					$row= mysql_fetch_assoc($rr);
					echo "
              <option selected='selected' value='".$row['FormID']."'>".$row['FormName']." (".$row['AddedDate'].")</option>";
		} else {
			echo "
              <option value=''>".GetLangVar('nameselect')."</option>";
		}
		//formularios usuario
		$qq = "SELECT * FROM Formularios WHERE (AddedBy=".$_SESSION['userid']." OR Shared=1) AND HabitatForm=1 ORDER BY FormName ASC";
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
  </td>";      
      }
echo  "
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
  <tr bgcolor ='".$bgcolor."'>
    <td colspan='100%'>
    <table>
      <tr>
        <td class='tdformleft' style=\"color: #990000;\">".GetLangVar('messagepertenceaclasse')."*</td>
        <td>
          <select name='parentid'>";
				if ($parentid>0) {
					$qq = "SELECT * FROM Habitat WHERE HabitatID='".$parentid."'";
					$wr = mysql_query($qq,$conn);
					$ww = mysql_fetch_assoc($wr);
					echo "
              <option  selected='selected' value='".$ww['HabitatID']."'>".$ww['Habitat']."</option>";
				} 
					echo "
              <option  value=''>".GetLangVar('nameselect')."</option>";
				$qq = "SELECT * FROM Habitat WHERE HabitatTipo NOT LIKE '%Local%' ORDER BY PathName";
				$res = mysql_query($qq,$conn);
				while ($aa = mysql_fetch_assoc($res)){
					$PathName = $aa['PathName'];
					$level = $aa['MenuLevel'];
					if ($level==1) {
						$espaco = "&nbsp;";
						echo "
              <option class='optselectdowlight' value='".$aa['HabitatID']."'>".$espaco."".strtoupper($aa['Habitat'])."</option>";
					} else {
						$espaco = str_repeat('&nbsp;',$level).str_repeat('-',$level-1);
						echo "
              <option value='".$aa['HabitatID']."'>".$espaco.$aa['Habitat']."</option>";
					}
				}
				echo "
      </select>
    </td>
  </tr>
</table>
</td>
</tr>";
if ($habitattipo=='Class') {
echo "
<tr>
  <td colspan='100%'>
    <table>
      <tr>
        <td class='tdformleft'>".GetLangVar('namedefinicao')."</td>
        <td><textarea name='habitatdefinicao' cols='60%' rows=5>".$habitatdefinicao."</textarea></td>
      </tr>
    </table>
  </td>
</tr>
";
} 
 else {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
if ($locality=='') { 
	$locality='Digite p/ buscar..';
	$localtxt = '';
} else {
	$localtxt = $locality;
}
if ($gpspt=='') { 
	$gpspt='Digite p/ buscar..';
	$localtxt = '';
} else {
	$localtxt = $gpspt;
}
if (!isset($nolocalshow) || $nolocalshow>0) {

$hbi = $pophabitatid+0;
if ((($pophabitatid>0 && $_SESSION['accesslevel']=='admin') || $hbi==0)) {
echo "
<tr bgcolor ='".$bgcolor."'>
  <td colspan='100%'>
    <table>
      <tr>
        <td style=\"color: #336600; font-size: 1.2em; text-align: center\" colspan='100%'>$localtxt</td>
      </tr>
      <tr>
        <td class='tdformleft' style=\"color: #990000;\">".GetLangVar('namelocalidade')."*</td>
        <td class='tdformnotes'>"; autosuggestfieldval3('search-gazetteer.php','locality',$locality,'gazres','gazetteerid',$gazetteerid,true,80); 
echo "</td>
      </tr>
      <tr>
        <td colspan='2' align='left' class='tdformnotes' style=\"color: #990000;\">&nbsp;OU então,&nbsp;</td>
      </tr>
      <tr>
        <td class='tdformleft' style=\"color: #990000;\">Ponto do GPS*</td>
        <td class='tdformnotes' >"; autosuggestfieldval3('search-gpspoint.php','gpspt',$gpspt,'gpsres','gpspointid',$gpspointid,true,80); 
	echo "</td>
      </tr>
    </table>
  </td>
</tr>";
} 
else {
echo "
<input type='hidden' name='locality' value='$locality' />
<input type='hidden' name='gpspt' value='$gpspt' />
<input type='hidden' name='gpspointid' value='$gpspointid' />
<input type='hidden' name='gazetteerid' value='$gazetteerid' />
<tr bgcolor ='".$bgcolor."'>
  <td colspan='100%'>
    <table>";
if (!empty($locality)) {
echo "<tr><td class='tdformleft' style=\"color: #990000;\">".GetLangVar('namelocalidade')."</td><td>$locality</td></tr>";
} else {
echo "<tr><td class='tdformleft' style=\"color: #990000;\">Ponto do GPS</td><td>$gpspt</td></tr>";
}
echo "
      </tr>
    </table>
  </td>
</tr>"; 

}
} 
else {
echo "
	<input type='hidden' name='gpspointid' value='".$gpspointid."' />
	<input type='hidden' name='nolocalshow' value='".$nolocalshow."' />";

}
if ($formid>0) {
	if ($traitsinenglish==1) {
		$flag = "brasilFlagicon.png";
		$flagval = 0;
	} else {
		$flag = "usFlagicon.png";
		$flagval = 1;
	}
echo "
<tr>
  <td  colspan='100%' align='right' >
    <input type='hidden' id='traitlang' name='traitsinenglish' value='' />
    <a onclick=\"javascript:document.getElementById('traitlang').value=".$flagval."; this.form.submit();\">
      <img height='30' src=\"icons/".$flag."\" alt='Mudar idioma' />
    </a>
  </td>
</tr>
<tr>
  <td  colspan='100%' align='center' >";
  include "variacao-traitsformnew.php";
echo "</td>
</tr>";
if ($habitattipo!='Class' && $formid>0) {
	echo "
<tr><td class='tdformnotes'><b>".GetLangVar('nameobs')."</b>:    ".GetLangVar('messagemultiplevalues')."</td></tr>";
}
}
echo "
<tr>
<td colspan='100%' class='tabsubhead' >".GetLangVar('habitatoutrostaxa')."</td>
</tr>
<tr>
<td colspan='100%'>
  <table align='left' width='100%' class='clean'>
    <tr>
        <td>
          <input type='hidden' name='specieslistids' value='$specieslistids' />
          <textarea cols='95' rows='2' name='specieslist' readonly='readonly'>$specieslist</textarea>
        </td>
        <td align='left'>
          <input type='button' value='".GetLangVar('nameselect')."' class='bsubmit' ";
			$myurl ="selectspeciespopup.php?formname=myformvar&amp;elementname=specieslistids&amp;destlistlist=".$specieslistids;
			echo " onclick = \"javascript:small_window('$myurl',500,400,'SelectSpecies');\" />
		</td>
    </tr>
  </table>
</td>
</tr>";
}
echo "
<tr>
  <td colspan='100%' align='center'>
    <table align='center'>
      <tr>
        <td align='center' >
          <input type='hidden' id='finnal' name='finnal' value='' />
          <input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.getElementById('finnal').value=1\" />
        </td>";
        if (!empty($elementidtxt)) { 
			echo "
        <td align='center'>
          <input type='submit'  value='".GetLangVar('nameconcluir')."' class='bblue' onclick=\"javascript:document.getElementById('finnal').value=2\" />
        </td>";
		}
		echo "  
        <td align='left'>
          <input type='button' value='".GetLangVar('namereset')."' class='breset' onclick=\"javascript:document.getElementById('resetform').submit();\" />
        </td>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>
</form>
<form action='habitat-popup-teste.php' method='post' id='resetform'>
  <input type='hidden' name='elementidval' value='$elementidval' />
  <input type='hidden' name='elementidtxt' value='$elementidtxt' />
</form>
"; 
} 
//fecha tabela do formulario
} 
//CASO CONTRARIO ESTARA SALVANDO
else {
if ($habitattipo=='Class') {
	$wdhab = str_replace("-"," ",$habitatname);
	$wdhab = str_replace("_"," ",$wdhab);
	$wdhab = str_replace("."," ",$wdhab);
	$wdhab = str_replace("  "," ",$wdhab);
	$wdarr = explode(" ",$wdhab);
	$j=0;
	$newwd = array();
	foreach ($wdarr as $wd) {
		$wdd = trim($wd);
		if (!empty($wdd)) {
			$wdd = ucfirst(strtolower($wdd));
			$newwd[] = $wdd;
		}
	}
	$newname = implode(" ",$newwd);
	if (!isset($habitatdefinicao)) {$habitatdefinicao='';}
	$fieldsaskeyofvaluearray = array(
			'Habitat' => $newname,
			'HabitatTipo' => $habitattipo,
			'Descricao' => $habitatdefinicao,
			'ParentID' => $parentid);
	$hbid = $pophabitatid+0;
	if ($hbid==0) {
		  $newhabitatid = InsertIntoTable($fieldsaskeyofvaluearray,'HabitatID','Habitat',$conn);
		  if (!$newhabitatid) {
					$erro++;
		  } else {
					updatehabitatpath($newhabitatid,$conn);
		}
	} 
	elseif ($hbid>0) { //if editing
		$qq = "SELECT * FROM `Habitat` WHERE `HabitatID`='".$hbid."'";
		$teste = mysql_query($qq,$conn);
		$rrr = mysql_fetch_assoc($teste);
		$oldval = $rrr['Habitat'];
		$oldids  = $rrr['EspeciesIds'];
		$oldef  = $rrr['Descricao'];
		$olparid  = $rrr['ParentID'];
		if ($newname!=$oldval || $specieslistids!=$oldids || $oldef!=$habitatdefinicao || $parentid!=$olparid) { 
				CreateorUpdateTableofChanges($hbid,'HabitatID','Habitat',$conn);
				$newhabitatid = UpdateTable($hbid,$fieldsaskeyofvaluearray,'HabitatID','Habitat',$conn);
				if (!$newhabitatid) {
					$erro++;
				} else {
					updatehabitatpath($newhabitatid,$conn);
				}
		}
	} //end editing
}
elseif ($habitattipo=='Local') {
	$gpid  = $gpspointid+0;
	$gzid  = $gazetteerid+0;
	if ($gpid>0) {
		$gztid = ''; 
		$gpsfid = $gpid;
	} elseif ($gzid>0) {
		$gztid = $gzid;
		$gpsfid = '';
	}
	$fieldsaskeyofvaluearray = array(
		'EspeciesIds' => $specieslistids,
		'HabitatTipo' => $habitattipo,
		'LocalityID' => $gztid,
		'GPSPointID' => $gpsfid,
		'ParentID' => $parentid);
	$hbid = $pophabitatid+0;
	if ($hbid==0) {
		$newhabitatid = InsertIntoTable($fieldsaskeyofvaluearray,'HabitatID','Habitat',$conn);
		if (!$newhabitatid) {
			$erro++;
		} else {
				updatehabitatpath($newhabitatid,$conn);
		}
	} 
	elseif ($hbid>0) { //if editing
			$qq = "SELECT * FROM Habitat WHERE HabitatID='".$hbid."'";
			$teste = mysql_query($qq,$conn);
			$rrr = mysql_fetch_assoc($teste);
			$oldids  = $rrr['EspeciesIds'];
			$oldgaz  = $rrr['LocalityID'];
			$oldgps  = $rrr['GPSPointID'];
			if ($gztid!=$oldgaz || $gpsfid!=$oldgps || 	$specieslistids!=$oldids) {
				CreateorUpdateTableofChanges($hbid,'HabitatID','Habitat',$conn);
				$newhabitatid = UpdateTable($hbid,$fieldsaskeyofvaluearray,'HabitatID','Habitat',$conn);
				if (!$newhabitatid) {
					$erro++;
				} else {
					updatehabitatpath($newhabitatid,$conn);
				}
			}
	}
	if ($erro==0) { //se nao houve erro cadastra variaveis
		if ($hbid==0 && $newhabitatid>0) {
			$hbid = $newhabitatid;
		}
		if (count($variaveis)>0) {
			$resultado = updatetraits($variaveis,$hbid,'HabitatID',$bibtex_id,$conn);
			if (!$resultado) {
				$erro++;
			} 
		} 
	}
}

if ($erro>0) {
	echo "
<br/>
<table cellpadding=\"7\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>Houve um erro, não foi possível fazer o cadastro!</td></tr>
</table>
<br/>";
} 
else {
	$hbid = $pophabitatid+0;
	if ($hbid==0 && $newhabitatid>0) {
		$hbid = $newhabitatid;
	}
	////////////////////////////////
		$habitatdesc = describehabitat($hbid,$img=FALSE,$conn);
		if ($finnal==1) {
echo "
<br/>
<table class='success' align='center'>
  <tr><td>O cadastro foi realizado com sucesso!</td></tr>";
if (!empty($elementidtxt)) {   
echo "<tr><td><input type=button value=".GetLangVar('nameconcluir')." class='bsubmit'  onclick=\"javascript:
  var element = self.opener.document.getElementById('".$elementidtxt."');
  element.innerHTML = '".($habitatdesc)."';
  var destination = self.opener.document.getElementById('".$elementidval."');
  destination.value = '$hbid';
  window.close();\"></td></tr>";
}
echo "
</table>
<br/>
";
		} 
		if ($finnal==2) {
echo "
<form>
  <script language=\"JavaScript\">
      setTimeout(
          function() {
            var element = self.opener.document.getElementById('".$elementidtxt."');
            element.innerHTML = '".($habitatdesc)."';
            var destination = self.opener.document.getElementById('".$elementidval."');
            destination.value = '$hbid';
            window.close();
            } ,0.0001);
    </script>
</form>";
		}
	}
} //se cadastrar


}

if (!empty($elementidtxt)) { 
	PopupTrailers();
} else {
	HTMLtrailers();
}
?>