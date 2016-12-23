<?php
//Este script importa o arquivo CSV ou TXT selecionado para uma tabela temporaria mysql
//Depois sao perguntados quais colunas indicam amostras coletadas ou plantas marcadas
//Ultima atualizacao: 25 jun 2011 - AV
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

if (count($gget)>count($ppost)) {
	$ppost = $gget;
}

//CABECALHO
 $ispopup=1; $menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />");
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>");
$title = 'Importar locais passo 06';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);
//echopre($runzz);

//OBJETOS VAZIOS PARA SALVAR ERROS
$erro =0;
$errotxt = array();

//FAZ DOS DIFERENTES NIVEIS DE LOCALIDADES A IMPORTAR
$zz = preg_grep ('/localidade/' , $fieldsign);
//ORDENA EM ORDEM CRESCENTE
asort($zz);
//SE AINDA NADA FOI FEITO, DEFINE UM OBJETO PARA ITERAR OS DIFERENTES NIVEIS DE LOCALIDADES
if (!isset($runzz)) {
	$runzz = $zz;
}
$oldrunzz = $runzz;
//PEGA OS NOMES DAS COLUNAS REFERENTES ÀS LOCALIDADES
$wkk = array_keys($runzz);

//PEGA COLUNA SENDO ITERADA
$colstep = $wkk[0];

//PEGA O NIVEL DESSA LOCALIDADE 
$wvv = array_values($runzz);
$wstep = $wvv[0];
$tt = explode("_",$wstep);
//NIVEL É
$idd = $tt[1];

//SE FOR A PRIMEIRA LOCALIDADE ENTAO DEFINE ALGUMAS COLUNAS (SE JA NAO DEFINIDAS)
//define valores de municipioid e parentid do primeiro nivel
$clnl2 = $tbprefix."GazetteerID";
if (count($zz)==count($runzz) && !isset($isrunning)) {
		$qq = "ALTER TABLE `".$tbname."` ADD COLUMN `".$clnl2."` INT(10) DEFAULT 0";
		@mysql_query($qq,$conn);
		$colnn = $tbprefix."ParentID";
		$qq = "ALTER TABLE `".$tbname."` ADD COLUMN `".$colnn."` INT(10) DEFAULT 0";
		@mysql_query($qq,$conn);
		$colnn = $tbprefix."MunicipioID";
		$qq = "ALTER TABLE `".$tbname."` ADD COLUMN `".$colnn."` INT(10) DEFAULT 0";
		@mysql_query($qq,$conn);
		unset($muid);
		unset($parid);
		if ($pparentid>0) {
			$parid = $pparentid;
			unset($pparentid);
			unset($ppost['pparentid']);
			$qn = "SELECT MunicipioID FROM Gazetteer WHERE GazetteerID='".$parid."'";
			$rn = mysql_query($qn,$conn);
			$rwnn = mysql_fetch_assoc($rn);
			$muid = $rwnn['MunicipioID'];
			$qz = "UPDATE ".$tbname."  SET ".$tbprefix."MunicipioID=".$muid;
			mysql_query($qz,$conn);
		} else {
			if ($municipioid>0) {
				$muid = $municipioid;
				$parid = 0;
			} else {
				if (!empty($municipio)) {
					$muid = $tbprefix."MunicipioID";
					$parid=0;
				} 
			}
		}
}

$oldparid = $parid;

//FAZ A INSERCAO APOS USUARIO ESPECIFICAR O QUE FAZER COM UMA LOCALIDADE NO ARQUIVO QUE PARECE JÁ ESTAR CADASTRADA!
//echopre($localstep);
if (isset($localstep)) {
	foreach ($localstep as $kll => $local) {
		$vals = $existem[$kll];
		if ($local=='novo') {
				$gaznome = formatgaznome($kll);
				$fieldsaskeyofvaluearray = array(
'ParentID' => ($vals['parentid']+0),
'Gazetteer' => $gaznome,
//'GazetteerTIPOtxt' => '',
'MunicipioID' =>  ($vals['municipioid']+0),
'DimX' =>  ($vals['dimx']+0),
'DimY' =>  ($vals['dimy']+0),
'StartX' =>  ($vals['startx']+0),
'StartY' =>  ($vals['starty']+0),
'Latitude' =>  ($vals['latdec']+0),
'Longitude' =>  ($vals['longdec']+0));
				//echo "INSERIR COMO NOVO APOS USUARIO SELECIONAR ISSO";
				//echopre($fieldsaskeyofvaluearray);
				$newgazid = InsertIntoTable($fieldsaskeyofvaluearray,'GazetteerID','Gazetteer',$conn);
				UpdateGazetteerPath($newgazid,$conn);
				$qn= "UPDATE ".$tbname." SET `".$tbprefix."GazetteerID`='".$newgazid."' WHERE `".$colstep."`='".$kll."'    AND `".$tbprefix."ParentID`=".($vals['parentid']+0)."  AND ".$tbprefix."MunicipioID=". ($vals['municipioid']+0);
				mysql_query($qn,$conn);
		} 
		else {
			$vals2 = $localstep_dados[$kll];
			if ($vals2==1) {
				$fieldsaskeyofvaluearray = array(
'DimX' =>  ($vals['dimx']+0),
'DimY' =>  ($vals['dimy']+0),
'StartX' =>  ($vals['startx']+0),
'StartY' =>  ($vals['starty']+0),
'Latitude' =>  ($vals['latdec']+0),
'Longitude' =>  ($vals['longdec']+0));
				foreach ($fieldsaskeyofvaluearray as $field => $value) {
					if ($value<>0) {
						$qnz= "UPDATE Gazetteer SET `".$field."`='".$value."' WHERE GazetteerID='".$local."'";
						mysql_query($qnz,$conn);
					}
				}
			
			}
			//echo "PEGA O GazetteerID da LOCALIDADE CORRESPONDENTE INFORMADA PELO USUARIO";
				$qn= "UPDATE ".$tbname." SET `".$tbprefix."GazetteerID`='".$local."' WHERE `".$colstep."`='".$kll."'  AND `".$tbprefix."ParentID`=".($vals['parentid']+0)."  AND ".$tbprefix."MunicipioID=". ($vals['municipioid']+0);
				mysql_query($qn,$conn);
		}
		unset($existem[$kll]);
	}
	//unset($localstep);
}

//echo "muid ".$muid."  parid ".$parid."<br >";

//CHECA SE PARA O NIVEL DE LOCALIDADE SENDO PROCESSADO HA DADOS ADICIONAS (Coordenadas, Dimensão e Posicao)
//SE OUVER SELECIONA
$colnames = "";
//echo "wstep ".$wstep."  colstep ".$colstep."   idd:".$idd."<br >";
if (in_array('dimx_'.$idd,$fieldsign) && in_array('dimy_'.$idd,$fieldsign)) {
		$kx =  array_search('dimx_'.$idd,$fieldsign);
		$ky = array_search('dimy_'.$idd,$fieldsign);
		$colnames .=  $kx." as DimX, ".$ky."  as DimY, ";
}
if (in_array('posx_'.$idd,$fieldsign) && in_array('posy_'.$idd,$fieldsign)) {
		$kx =  array_search('posx_'.$idd,$fieldsign);
		$ky = array_search('posy_'.$idd,$fieldsign);
		$colnames .=  $kx." as StartX, ".$ky."  as StartY, ";
}
if (in_array('latitude_'.$idd,$fieldsign) && in_array('longitude_'.$idd,$fieldsign)) {
				$kx =  array_search('longitude_'.$idd,$fieldsign);
				$ky = array_search('latitude_'.$idd,$fieldsign);
				$colnames .=  $kx." as Longitude, ".$ky."  as Latitude, ";
}

//PARA O NIVEL SENDO EXECUTADO, PEGAS OS VALORES ÚNICOS DA COLUNA $colstep E COMPARA COM A BASE PARA SABER SE JA EXISTE
//SE EXISTE PERGUNTA AO USUARIO
//SE NAO EXISTE INSERE
	
if (isset($muid) && isset($parid)) {

		//VALORES DISTINTOS DA COLUNA $colstep QUE AINDA NAO TEM UM GazetteerID DEFINIDO
		$qq = "SELECT DISTINCT ".$colstep."  as teste, checkgazetteer_import(".$colstep.",".$parid.",".$muid.",0,0)  as testecheck, ".$tbprefix."ParentID, ".$tbprefix."MunicipioID  FROM ".$tbname."  WHERE ".$colstep."<>'' ";
//WHERE ".$tbprefix."GazetteerID=0
		///echo $qq."<br />";
		$rr = mysql_query($qq,$conn);
		$nrr = mysql_numrows($rr);
		//SE HOUVER ITERA, INSERINDO, OU CRIANDO UM ARRAY $existem PARA PERGUNTAR AO USUARIO
		//echo "aqui:";
		//echopre($localstep);
		//echo "até aqui:";
		if ($nrr>0 && !isset($localstep)) {
				//echo "entrei aqui, hahahaha<br >";
				$existem = array();
				while ($rww = mysql_fetch_assoc($rr)) {
							$existe = $rww['testecheck'];
							$theparid = $rww[$tbprefix."ParentID"];
							$themuniid = $rww[$tbprefix."MunicipioID"];
							//SE HOUVER DADOS ADICIONAIS ASSOCIADOS À LOCALIDADE, SELECIONA APENAS OS VALORES DO PRIMEIRO REGISTRO
							$qn= "SELECT ".$colnames." ".$tbprefix."ParentID, ".$tbprefix."MunicipioID FROM ".$tbname." WHERE `".$colstep."`='".$rww['teste']."' AND ".$tbprefix."ParentID=".$theparid." AND ".$tbprefix."MunicipioID=".$themuniid."  LIMIT 0,1";
							//echo $qn."   <br />^aqui o!";
							$rqn = mysql_query($qn,$conn);
							$rqnw =  mysql_fetch_assoc($rqn);
							$gaznome = formatgaznome($rww['teste']);
							$foundmatch = array();
									if (isset($rqnw['DimX'])) {
											$dimx = $rqnw['DimX'];
											$dimy = $rqnw['DimY'];
											$foundmatch['dimx'] = $dimx;
											$foundmatch['dimy'] = $dimy;
									}
									if (isset($rqnw['StartX'])) {
											$startx = $rqnw['StartX'];
											$starty = $rqnw['StartY'];
											$foundmatch['startx'] = $startx;
											$foundmatch['starty'] = $starty;
									}
									if (isset($rqnw['Longitude'])) {
											$longdec = $rqnw['Longitude'];
											$latdec = $rqnw['Latitude'];
											$foundmatch['longdec'] = $longdec;
											$foundmatch['latdec'] = $latdec;
									}
									#$mtest = $muid+0;
							if ($rww [$tbprefix."MunicipioID"]>0)  {
								$muid = $rww [$tbprefix."MunicipioID"];
							}
							if ( $rww[$tbprefix.'ParentID']>0) {
								$pparid = $rww[$tbprefix.'ParentID'];
							} else {
								$pparid = $parid;
							}
							//SE JÁ EXISTE, ENTAO ADICIONA AO ARRY
							if ($existe>0) {
							  		echo "encontrei".$exite."<br >";
									$foundmatch['gazmatch'] = $existe;
									$foundmatch['parentid'] = $pparid;
									$foundmatch['municipioid']  = $muid; 
									$existem[$rww['teste']] = $foundmatch;
							} else {
									//CASO CONTRARIO, INSERE NA BASE
										$fieldsaskeyofvaluearray = array(
'ParentID' => $pparid,
'Gazetteer' => $gaznome,
//'GazetteerTIPOtxt' => '',
'MunicipioID' => $muid,
'DimX' => $dimx,
'DimY' => $dimy,
'StartX' => $startx,
'StartY' => $starty,
'Latitude' => $latdec,
'Longitude' => $longdec);
										//echo "INSERIR $gaznome COMO NOVO 01<br />";
										//echopre($fieldsaskeyofvaluearray);
										$newgazid = InsertIntoTable($fieldsaskeyofvaluearray,'GazetteerID','Gazetteer',$conn);
										UpdateGazetteerPath($newgazid,$conn);

										//ATUALIZA A TABELA IMPORTACAO
										$qn= "UPDATE ".$tbname." SET `".$tbprefix."GazetteerID`='".$newgazid."' WHERE `".$colstep."`='".$rww['teste']."'  AND ".$tbprefix."ParentID=".$pparid."  AND ".$tbprefix."MunicipioID=".$muid;
										//echo $qn."<br ><br >";
										mysql_query($qn,$conn);
							}
				}
			} 
			//iIF IS NOT SET LOCALSTEP
				//SE ENCONTROU LOCALIDADES VALIDAS, ENTAO PERGUNTA SE É NOVA OU SE SUBSTITUI POR LOCALIDADE JA CADASTRADA
		//echopre($existem);
		if (count($existem)>0) {
					echo "
<form action='import-locais-step6.php' method='post' >";
					unset($ppost['fieldsign']);
					unset($ppost['muid']);
					unset($ppost['parid']);
					unset($ppost['runzz']);
					unset($ppost['existem']);
					foreach ($ppost as $kk => $vv) {
						if (!empty($vv)) {
							echo "
            <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					foreach ($fieldsign as $kk => $vv) {
						if (!empty($vv)) {
							echo "
            <input type='hidden' name='fieldsign[".$kk."]' value='".$vv."' />"; 
						}
					}
					foreach ($runzz as $kk => $vv) {
						if (!empty($vv)) {
							echo "
            <input type='hidden' name='runzz[".$kk."]' value='".$vv."' />"; 
						}
					}
					foreach ($existem as $kk => $vv) {
						foreach ($vv as $knn => $vnn) {
							if (!empty($vnn)) {
								echo "
            <input type='hidden' name='existem[".$kk."][".$knn."]' value='".$vnn."' />"; 
							}
						}
					}
					
//echo "<input type='text' name='muid' value='muid ".$muid."' /> <input type='text' name='parid' value='parid ".$parid."' />";
echo "
            <input type='hidden' name='muid' value='".$muid."' /> 
            <input type='hidden' name='parid' value='".$parid."' />
            <input type='hidden' name='isrunning' value='".$isrunning."' />
<br />
<table align='left' class='myformtable' cellpadding='7'>
  <thead>
    <tr><td colspan='3'>Parece que algumas localidades na coluna $colstep já existem!</td></tr>
  <tr class='subhead'>
      <td>Localidade</td>
      <td>É o mesmo que!</td>
      <td>Obs</td>
    </tr>
  </thead>
  <tbody>";
	//PARA CADA LOCALIDADE QUE APARENTEMENTE JA EXISTE, SELECIONA OS VALORES PARECIDOS
	foreach ($existem as $kk => $vv) {
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>".$kk."</td>
  <td>
    <select name='localstep[".$kk."]' >
      <option value='novo' >Inserir como nova</option>
      <option value='' >------------------------------</option>"; 
        if  ($vv['parentid']>0) {
        	$wwhere = "ParentID='".$vv['parentid']."'";
        } elseif ($vv['municipioid']>0) {
        	$wwhere = "MunicipioID='".$vv['municipioid']."'";
        }
		//$qz = "SELECT DISTINCT GazetteerID, GazetteerTIPOtxt,Gazetteer, PathName FROM Gazetteer WHERE ".$wwhere." AND LOWER(CONCAT(GazetteerTIPOtxt,' ',Gazetteer)) LIKE CONCAT('%',LOWER('".$kk."'),'%')";
		$qz = "SELECT DISTINCT GazetteerID,Gazetteer, PathName FROM Gazetteer WHERE ".$wwhere." AND LOWER(Gazetteer) LIKE CONCAT('%',LOWER('".$kk."'),'%')";
		$rz = mysql_query($qz, $conn);
		$nrrz = mysql_numrows($rz);
		if ($nrrz==1) {
			$txt = 'selected';
		} else {
			$txt = '';
		}
		while ($rwz = mysql_fetch_assoc($rz)) {
			echo "
      <option ".$txt." value='".$rwz['GazetteerID']."' >".$rwz['PathName']."</option>";
		}
		$dados = ($vv['dimx']+0)+($vv['dimy']+0)+($vv['startx']+0)+($vv['starty']+0)+($vv['latdec']+0)+($vv['longdec']+0);
echo "
    </select>
  </td>
  <td>";
  //echo "DADOS: ".$dados."<br />";
  if (abs($dados)>0) {
echo "
  <input type='checkbox' name='localstep_dados[".$kk."]'  value='1'  />&nbsp;Atualizar dados<img height='14' src=\"icons/icon_question.gif\" ";
$help = "Se escolher uma localidade existente, selecione aqui caso queira atualizar os dados associados a essa localidade que existem no arquivo sendo importado!";
echo " onclick=\"javascript:alert('$help');\" />";
	} else {
echo "nada associado";
	}
echo "
  </td>
</tr>";
			}
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>&nbsp;</td>
  <td align='center'><input class='bsubmit'  style='cursor: pointer' type='submit' value='Continuar' /></td>
  <td align='center'><input class='bblue'  style='cursor: pointer' type='button' value='Cancelar' onclick='javascript: window.close();' /></td>
 </tr>
</tbody>
</table>
</form>
";
//echo $qz."<br >";
				} 
			else  {
			$nextsetp=1;
		}
}
	//CASO TENHA TERMINADO UMA ETAPA (NIVEL DE LOCALIDADE), PASSA PARA O NIVEL INFERIOR

//echo "aqui xxx";
//echopre($runzz);
    if (count($runzz)>1 && $nextsetp==1) {
				unset($localstep);
				//PASSA PARA O NIVEL INFERIOR
				
				unset($runzz[$colstep]);
				//echo "aqui para novo passo:";
				//echopre($runzz);
				//$runzz = array_values($runzz);
				//ATUALIZA OS VALORES NAS COLUNAS ParentID e GazetteerID
					$qn= "UPDATE ".$tbname." SET `".$tbprefix."ParentID`=`".$tbprefix."GazetteerID`";
					//echo $qn."<br />";
					mysql_query($qn,$conn);
					$qn= "UPDATE ".$tbname." SET `".$tbprefix."ParentID`=`".$tbprefix."GazetteerID`";

					$parid = $tbprefix."ParentID";
					echo "
<form action='import-locais-step6.php' method='post' name='myform'>";
					unset($ppost['fieldsign']);
					unset($ppost['localstep']);

					foreach ($ppost as $kk => $vv) {
						if (!empty($vv)) {
							echo "
            <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					foreach ($fieldsign as $kk => $vv) {
						if (!empty($vv)) {
							echo "
            <input type='hidden' name='fieldsign[".$kk."]' value='".$vv."' />"; 
						}
					}
					foreach ($runzz as $kk => $vv) {
						if (!empty($vv)) {
							echo "
            <input type='hidden' name='runzz[".$kk."]' value='".$vv."' />"; 
						}
					}
echo "
            <input type='hidden' name='muid' value='".$muid."' /> 
            <input type='hidden' name='parid' value='".$parid."' />
            <input type='hidden' name='nextsetp' value='".$nextsetp."' />
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
</form>";
//    <input type='submit' value='Próximo nível!' class='bsubmit' style='cursor: pointer'>
			} 
	elseif ($nextsetp==1) {
		//echo "TERMINOU";
		//PREPARA ARQUIVO PARA EXPORTACAO
		$qn = "SELECT DISTINCT gaz.GazetteerID AS Wiki_GazetteerID, gaz.Gazetteer AS LocalEspecifico, gaz.PathName AS LocalCaminho, Municipio.Municipio, prov.Province, crt.Country, IF(gaz.DimX>0,CONCAT(gaz.DimX,'x',gaz.DimY),'') as Parcela, gaz.DimX, gaz.DimY, gaz.StartX, gaz.StartY,gaz.Latitude, gaz.Longitude FROM ".$tbname."  JOIN Gazetteer AS gaz  on ". $tbname.".".$tbprefix."GazetteerID=gaz.GazetteerID LEFT JOIN Municipio ON gaz.MunicipioID=Municipio.MunicipioID LEFT JOIN Province prov ON  prov.ProvinceID=Municipio.ProvinceID LEFT JOIN Country as crt ON crt.CountryID=prov.CountryID WHERE ".$tbname.".".$tbprefix."GazetteerID>0";
		//echo $qn."<br />";
	$rz = mysql_query($qn,$conn);
	$ngazz = mysql_numrows($rz);
	$idx = 0;
	$export_filename = "Locais_Importados_de_".$tbname.".csv";
	while ($gazres = mysql_fetch_assoc($rz)) {
			//SALVA OS DADOS NO ARQUIVO TXT
				if ($idx==0) {
					$fh = fopen("temp/".$export_filename, 'w') or die("Não foi possivel gerar o arquivo");
					$count = mysql_num_fields($rz);
					$header = '';
					for ($i = 0; $i < $count; $i++){
						$header .= mysql_field_name($rz, $i)."\t";
					}
					$header .= "\n";
					fwrite($fh, $header);
				} 
				else {
					$fh = fopen("temp/".$export_filename, 'a') or die("Não foi possivel abrir o arquivo");
				}
				//PARA CADA LINHA
				$line = '';
					//INCLUI OS VALORES DE CADA COLUNA
				foreach($gazres as $value){
						//SE O VALOR FOR UMA DATA VAZIA SUBSTITUI POR NADA
						if ($value=='0000-00-00') {
							$value='';
						}
						//INCLUI O VALOR E O SEPARADOR
						if(!isset($value) || $value == ""){
							$value = "\t";
						}
						else
							{
							//important to escape any quotes to preserve them in the data.
							$value = str_replace('"', '""', $value);
							//needed to encapsulate data in quotes because some data might be multi line.
							//the good news is that numbers remain numbers in Excel even though quoted.
							$value = '"' . $value . '"' . "\t";
						}
						$line .= $value;
					}
				$lin = trim($line)."\n";
				fwrite($fh, $lin);
				fclose($fh);
		$idx++;
	}
echo "
<br />
<table class='myformtable' cellpadding='5' align='center' width='90%'>
<thead>
<tr><td>Dados de Locais Importados</td></tr>
</thead>
<tbody>
<tr><td class='tdsmallbold'>$ngazz registros de localidades foram importados</td></tr>
<tr><td class='tdformnotes'>O arquivo abaixo corresponde a essas localidades da forma como estão na base!</td></tr>
<tr><td><a href=\"download.php?file=temp/".$export_filename."\">Baixar dados</a></td></tr>
<tr><td><hr></td></tr>
<tr><td class='tdformnotes'>*Os arquivos estão separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td></tr>
</tr>
</tbody>
</table>";
		//echo $qn."<br />";
	}

echo "  <form action='import-locais-step6.php' method='post' id='confirmform'>";
			unset($ppost['fieldsign']);
			unset($ppost['runzz']);
			unset($ppost['muid']);
			unset($ppost['parid']);
			unset($ppost['existem']);
			foreach ($ppost as $kk => $vv) {
						if (!empty($vv)) {
							echo "
            <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
				}
				foreach ($fieldsign as $kk => $vv) {
						if (!empty($vv)) {
							echo "
            <input type='hidden' name='fieldsign[".$kk."]' value='".$vv."' />"; 
						}
				}
				foreach ($oldrunzz as $kk => $vv) {
					if (!empty($vv)) {
							echo "
            <input type='hidden' name='runzz[".$kk."]' value='".$vv."' />"; 
						}
					}
echo "
            <input type='hidden' name='muid' value='".$muid."' /> 
            <input type='hidden' name='parid' value='".$oldparid."' />
            <input type='hidden' name='isrunning' value='".$isrunning."' />
</form>";
//            <input type='submit' value='refresh' />

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
