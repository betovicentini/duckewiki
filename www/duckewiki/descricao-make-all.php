<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
//$lang="BR";

function phytotaxalista($projetoid,$taxondetcol,$taxonid,$traitfertid) {
$qq = "SELECT *  FROM (SELECT 
pltb.EspecimenID AS WikiEspecimenID, 
IF(pltb.AddColIDS IS NOT NULL,CONCAT(pltb.ColetorID,';',pltb.AddColIDS),pltb.ColetorID) as COLETOR,
pltb.Number as NUMBER,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID,'COUNTRY')  as COUNTRY,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'MAJORAREA')  as MAJORAREA,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'MINORAREA')  as MINORAREA, 
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'GAZfirstPARENT')  as GAZETTEER_CURTO,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'GAZETTEER')  as GAZETTEER_COMPLETA,
localidadefields(pltb.GazetteerID, pltb.GPSPointID,pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 'GAZETTEER_SPEC')  as GAZETTEER_SPECIFIC,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID, CountryID, 1,6)  as LATITUDE,
getlatlongdms(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, pltb.MunicipioID, pltb.ProvinceID,pltb.CountryID, 0,6)  as LONGITUDE,
ROUND(getaltitude(pltb.Altitude+0, pltb.GPSPointID,pltb.GazetteerID),0) as ALTITUDE,
IF (pltb.Day>0,pltb.Day,'')  as COLLDD, 
IF (pltb.Mes>0,MONTHNAME(STR_TO_DATE(pltb.Mes, '%m')),'')  as COLLMM, 
IF (pltb.Ano>0,pltb.Ano,'')  as COLLYY, 
pltb.Herbaria";
if ($traitfertid>0) {
	$qq .=", traitvaluespecs(".$traitfertid.",pltb.PlantaID,pltb.EspecimenID,'m',0,0) as FERTILIDADE";
}
$qq .= " FROM ProjetosEspecs as oprj JOIN Especimenes as pltb ON pltb.EspecimenID=oprj.EspecimenID";
$qq .= " LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID";
$qq .= " WHERE oprj.ProjetoID=".$projetoid;
$qq .= " AND iddet.".$taxondetcol."=".$taxonid.") as tb ORDER BY tb.COUNTRY,tb.MAJORAREA,tb.MINORAREA,tb.GAZETTEER_COMPLETA,tb.COLETOR,tb.NUMBER";
//echo $qq."<br>";
$rsql = mysql_query($qq);
//echo "aqui:".mysql_num_rows($rsql);
if ($rsql) {
	//echo $qq."<br>";
	$lista = "";
	$ppais = "";
	$pprov = "";
	$pmuni = "";
	$pgaz = "";
	while($row = mysql_fetch_assoc($rsql)) {
		//echopre($row);
		$pais = strtoupper($row["COUNTRY"]);
		$prov = strtoupper($row["MAJORAREA"]);
		$muni = $row["MINORAREA"];
		$gaz = str_replace(" - ",", ",$row["GAZETTEER_COMPLETA"]);	
		$lat = $row["LATITUDE"];
		$long = $row["LONGITUDE"];
		$al = $row["ALTITUDE"];
		if ($al>0) { $al = $al."m";} else {unset($al);}
		$data = $row["COLLDD"]." ".$row["COLLMM"]." ".$row["COLLYY"];
		$coletor = $row["COLETOR"];
		$sqq = "SELECT getpessoas('".$coletor."') as cll";
		//echo $sqq."<br >";
		$rqq = mysql_query($sqq);
		$rqqw = mysql_fetch_assoc($rqq);
		$coletor = $rqqw["cll"];
		$nn = $row["NUMBER"];
		$hh = str_replace(",",", ",$row["Herbaria"]);
		
		$ff = explode(";",$row["FERTILIDADE"]);
		$ofert = array();
		foreach($ff as $fert) {
			$of = substr($fert,0,2);
			$ofert[] = mb_strtolower($of);
		}				
		$ofert = implode(",",$ofert);
		if ($ppais!=$pais) {
			$lista .= $pais.". ";
			unset($pprov,$pmuni,$pgaz);
		}
		if ($pprov!=$prov) {
			$lista .= $prov.". ";
			unset($pmuni,$pgaz);
		}
		if ($pmuni!=$muni) {
			$lista .= $muni.": ";
			unset($pgaz);
		}
		if ($pgaz!=$gaz) {
			$lista .= $gaz;
		}
		if (!empty($lat) & !empty($long)) {
			$lista .= ", ".$lat.", ".$long;
		}
		if (!empty($al)) {
			$lista .= ", ".$al;		
		}
		if (!empty($ofert)) {
			$lista .= ", ".$ofert;		
		}
		if (!empty($data)) {
			$lista .= ", ".$data;		
		}
		$lista .= ", <i>".$coletor." ".$nn."</i> ";
		if (!empty($hh)) {
			$lista .= "(".$hh."); ";		
		} else {
			$lista .= ";";		
		}	
		$ppais = $pais;
		$pprov = $prov;
		$pmuni = $muni;
		$pgaz = $gaz;
	}
}
//echo $lista;
$lista = trim($lista);
$lista = str_replace(".,",",",$lista);
$lista = str_replace(" .",".",$lista);
$lista = str_replace("..",".",$lista);
$lista = str_replace("..",".",$lista);
$lista = str_replace(",",", ",$lista);
$lista = str_replace(" ,",",",$lista);
$lista = str_replace(" ;",";",$lista);
$lista = str_replace(" :",":",$lista);
$lista = str_replace(",,",",",$lista);
$lista = str_replace(".;","; ",$lista);
$lista = str_replace(";.",".",$lista);
$lista = str_replace(".,",",",$lista);
$lista = str_replace(";","; ",$lista);
$lista = str_replace("  "," ",$lista);
$lista = str_replace("  "," ",$lista);
$lista = str_replace("  "," ",$lista);
$lista = str_replace(";,",";",$lista);
$lista = str_replace(":,",":",$lista);
$lista = str_replace(": ,",":",$lista);
$lista = str_replace(" ;",";",$lista);
$lista = str_replace(";,",";",$lista);
return($lista);
}

function simpleTraitName($parentname,$traitname) {
	$tascol = $parentname."_".$traitname;
	$val =  RemoveAcentos($tascol);
	$val = str_replace("  ", " ", $val);
	$val = str_replace("  ", " ", $val);
	$val = str_replace("  ", " ", $val);
	$val = str_replace("  ", " ", $val);		
	$symb = array(".",'/',"-",")","(",";");
	$val  = str_replace($symb, "", $val);
	//$val = str_replace(" ", "", $val);
	$val = str_replace(" ", "-", $val);	
	$tascol = strtoupper($val);
	return($tascol);		
}


function descricaoValor($otraitid,$otipo,$newunit,$oprojetoid,$taxondetcol,$taxonid,$traitnamecol,$oformato,$comunidade,$trcategpont,$trcatetxt,$groupby,$groubyState, $odecimal) {
$avariacao = array();		
//pega valores de especimenes ligados ligados ao projeto e ao taxon
if ($groupby>0 && $groubyState>0) {
	$basegroup = "SELECT DISTINCT tv.EspecimenID FROM Traits_variation  as tv JOIN ProjetosEspecs as oprj ON tv.EspecimenID=oprj.EspecimenID WHERE TraitID=".$groupby."  AND (TraitVariation LIKE '".$groubyState."' OR TraitVariation LIKE '%;".$groubyState.";%' OR TraitVariation LIKE '%;".$groubyState."') AND oprj.ProjetoID=".$oprojetoid;
	$sql2  = "SELECT mudaunidade(trvar.TraitVariation,'".$otipo."', trvar.TraitUnit,'".$newunit."') as vv FROM Traits_variation as trvar JOIN Especimenes as amos ON amos.EspecimenID=trvar.EspecimenID JOIN Identidade as idd ON idd.DetID=amos.DetID JOIN (".$basegroup.") as prj ON prj.EspecimenID=trvar.EspecimenID WHERE trvar.TraitID=".$otraitid." AND idd.".$taxondetcol."=".$taxonid;
	$basegroup = "SELECT DISTINCT tv.PlantaID FROM Traits_variation as tv JOIN ProjetosEspecs as oprj ON tv.PlantaID=oprj.PlantaID WHERE tv.TraitID=".$groupby."  AND (TraitVariation LIKE ".$groubyState." OR TraitVariation LIKE '%;".$groubyState.";%' OR TraitVariation LIKE '%;".$groubyState."') AND oprj.ProjetoID=".$oprojetoid." AND (tv.EspecimenID=0 OR tv.EspecimenID IS NULL)";
	$sql3  = "SELECT mudaunidade(trvar.TraitVariation,'".$otipo."', trvar.TraitUnit,'".$newunit."') as vv FROM Traits_variation as trvar JOIN Plantas as amos ON amos.PlantaID=trvar.PlantaID JOIN Identidade as idd ON idd.DetID=amos.DetID JOIN (".$basegroup.")  as prj ON prj.PlantaID=trvar.PlantaID WHERE trvar.TraitID=".$otraitid." AND (trvar.EspecimenID=0 OR trvar.EspecimenID IS NULL) AND idd.".$taxondetcol."=".$taxonid;
	
} else {
	$sql2  = "SELECT mudaunidade(trvar.TraitVariation,'".$otipo."', trvar.TraitUnit,'".$newunit."') as vv FROM Traits_variation as trvar JOIN Especimenes as amos ON amos.EspecimenID=trvar.EspecimenID JOIN Identidade as idd ON idd.DetID=amos.DetID JOIN ProjetosEspecs as prj ON prj.EspecimenID=trvar.EspecimenID WHERE TraitID=".$otraitid." AND prj.ProjetoID=".$oprojetoid." AND idd.".$taxondetcol."=".$taxonid;
	$sql3  = "SELECT mudaunidade(trvar.TraitVariation,'".$otipo."', trvar.TraitUnit,'".$newunit."') as vv FROM Traits_variation as trvar JOIN Plantas as amos ON amos.PlantaID=trvar.PlantaID JOIN Identidade as idd ON idd.DetID=amos.DetID JOIN ProjetosEspecs as prj ON prj.PlantaID=trvar.PlantaID WHERE TraitID=".$otraitid." AND (trvar.EspecimenID=0 OR trvar.EspecimenID IS NULL) AND prj.ProjetoID=".$oprojetoid." AND idd.".$taxondetcol."=".$taxonid;
}
//echo "<hr>".$otipo;	
//echo "<br />".$sql2."<br />".$sql3;

$lixao=1;
if ($lixao){
$res1 = mysql_query($sql2);
while($row1 = mysql_fetch_assoc($res1)) {
	$osval = explode(";",$row1['vv']);
	foreach($osval as $ov) {
		$avariacao[] = $ov;
	}		
}	

//pega valores e plantas ligados ao projeto e taxon e que nao sao especimenes	
$res2 = mysql_query($sql3);
while($row2 = mysql_fetch_assoc($res2)) {
	$osval = explode(";",$row2['vv']);
	foreach($osval as $ov) {
		$avariacao[] = $ov;
	}		
}		
$v1 = $avariacao;	
//print_r($avariacao);
//se for uma variavel quantitativa
if ($otipo=='Variavel|Quantitativo') {
	$odecimal = $odecimal+0;
	if (count($v1)>1) {
		//echopre($v1);
		$media = round(array_sum($v1)/count($v1),$odecimal);
		$somadesviso= 0;
		foreach ($v1 as $i) {
			$somadesviso += pow($i - $media, 2);
		}
		$odesvio = round(sqrt($somadesviso/(count($v1)-1)),$odecimal);
		$minimo = round(min($v1),1);
	   $maximo = round(max($v1),1);
		if ($oformato=='range') {
			$valor = $minimo."-".$maximo;
	   }
		if ($oformato=='meansd') {
			$valor = $media."+/-".$odesvio;
		}
		if ($oformato =='meansdrange') {
			$valor = $minimo."-".$maximo." (".$media."+/-".$odesvio.")";
		}
		if ($valor=="") {
			$valor = implode(";",$v1);
		}
	} else {
		$valor = round($v1[0],$odecimal);
	}
	if ($comunidade=="on" && !empty($valor)) {
		$valor = $valor." ".$newunit;
	}
} 

if ($otipo=='Variavel|Categoria') {
	$estados = array();
	foreach($v1 as $i) {
		$sq = "SELECT TraitName FROM Traits WHERE TraitID=".$i;
		//echo $sq."</br>";
		$rsq = mysql_query($sq);
		$rwq = mysql_fetch_assoc($rsq);
		$stn = mb_strtolower($rwq[$traitnamecol]);
		if (empty($stn)) {strtolower($rwq["TraitName"]);}
		$stn = strtloweracentos($stn);
		$estados[] = $stn;
	}
	//echo $traitnamecol;
	//echopre($estados);
	$estados = array_unique($estados);
	$estadoscount = array_count_values($estados);
	//echopre($estadoscount);
		
	arsort($estadoscount);
	$oskk = array_keys($estadoscount);
	//$oskk = $estados;
	//echopre($oskk);
	//quantos?
	$nst = count($oskk);
	if ($nst>2) { #se for maior que dois coloca categtxt entre os dois últimos
		$arr1 = $oskk;
		unset($arr1[($nst-1)]);
		$arr1 = array_values($arr1);
		$pont1 = trim($trcategpont);
		$tt1 = implode($pont1." ", $arr1);
		$valor = $tt1." ".trim($trcatetxt)."  ".trim($oskk[($nst-1)]);
	} else {
		if ($nst==2) {
			$sep = "  ".trim($trcatetxt)." ";
			$valor = implode($sep, $oskk);
		} else {
			$valor = implode("",$oskk);
		}
	}
} 

}
return($valor);
}


//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
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

//echopre($gget);
//CRIA UM ARQUIVO PARA SALVAR O PROGRESSO

$fh = fopen("temp/".$pgfilename, 'w');
fwrite($fh, '0');
fclose($fh);
//session_write_close();
//flush();



 
//PEGA OS TRAITS E COLOCA DENTRO DE UM ARRAY. SE HOUVER GRUPO COM SUBARRAYS
$sql = "SELECT tr.TraitID, tr.TraitTipo, tr.TraitName, intr.TraitName as ParentName, prefixo , prefixoeng, prefixoprevio,ucfirstvalue, sufixo , sufixoeng, formato , unitformat , unitinclude ,namostral ,categpontuacao, categtxt, categtxteng, groupby, quantdec FROM FormulariosTraitsList  as ff JOIN Traits as tr USING(TraitID) LEFT JOIN Traits as intr ON intr.TraitID=tr.ParentID WHERE FormID=".$formid." ORDER BY Ordem";
//echo $sql;mysql_set_charset('latin1',$conn);
mysql_set_charset('latin1',$conn);

$res = mysql_query($sql);
$thetraits = array();
$steps = 0;
while($row = mysql_fetch_assoc($res)) {
	$linha = $row;
	if ($row['groupby']>0) {
		if ($row['groupby']==$row['TraitID']) {
			//pega estados
			$thetraits['grupo_'.$row['groupby']]	= array();
			$sql2 = "SELECT TraitID FROM Traits WHERE ParentID=".$row['TraitID']."  ORDER BY TraitName";
			//echo $sql2;
			$res2 = mysql_query($sql2);			
			while($row2 = mysql_fetch_assoc($res2)) {
				$thetraits['grupo_'.$row['groupby']][$row2['TraitID']][$row['TraitID']]= $row;
				$steps++;
				
			}
		} else {
			$arr = $thetraits['grupo_'.$row['groupby']];
			foreach($arr as $kk => $vv) {
				$thetraits['grupo_'.$row['groupby']][$kk][$row['TraitID']] = $row;
				$steps++;
			}
		}		
	} else {
		$thetraits['trait_'.$row['TraitID']]= $row;
		$steps++;
	}
}
//echopre($thetraits);

$ostaxons = explode(";",$ostaxids);
$nspp = count($ostaxons);
$ntotal = $steps*$nspp;
$todas = "";
$idx =1;
foreach($ostaxons as $txidd) {
$txid = explode("-",$txidd);
if ($txid[1]>0) {
	$taxonid = $txid[1];
	$taxondetcol = "InfraEspecieID";
	$sq = "SELECT Genero,Especie,EspecieAutor,InfraEspecieNivel,InfraEspecie,InfraEspecieAutor FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE InfraEspecieID=".$taxonid;	
	$rq = mysql_query($sq);
	$rw = mysql_fetch_assoc($rq);
	$txnome = "<i>".$rw['Genero']." ".$rw["Especie"]."</i>  ".$rw['EspecieAutor']." ".$rw["InfraEspecieNivel"]." <i>".$rw['InfraEspecie']."</i> ".$rw['InfraEspecieAutor'];
} else {

	$taxonid = $txid[0];
	$taxondetcol = "EspecieID";
	$sq = "SELECT Genero,Especie,EspecieAutor FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE EspecieID=".$taxonid;	
	$rq = mysql_query($sq);
	$rw = mysql_fetch_assoc($rq);
	$txnome = "<i>".$rw['Genero']." ".$rw["Especie"]."</i>  ".$rw['EspecieAutor'];
}
//echo $taxonid." ".$taxondetcol."<br >";

$descricao = "<hr><br>".$txnome."<br>";
$valorprevio = NULL;
$oprevioprefixo = "";
foreach($thetraits as $kk => $linha) {
	//checa se é grupo
	$tt = explode("_",$kk);
	if ($tt[0]=='grupo') {
		foreach($linha as $kl => $bystatelinha) {
			$subidx =0;
			$subgrupo = "";
			$temgrp = 0;
			foreach($bystatelinha as $kt => $sublinha) {
				$newunit = $sublinha['unitformat'];
				$thedecimal = $sublinha['quantdec'];
				$comunidade = $sublinha['unitinclude'];
				$otipo  = $sublinha['TraitTipo'];
				$ucfirstvalue = $sublinha['ucfirstvalue'];
				$otraitid = $sublinha['TraitID'];
				$oformato = $sublinha['formato'];
				$groupby = $sublinha['groupby'];
				$groubyState = $kl;
				$traitn = $sublinha['TraitName'];
				$parentn = $sublinha['ParentName'];
				if ($lang=="BR") { 
					$traitnamecol= 'TraitName'; 
					$trcatetxt = $sublinha['categtxt'];
					$theprefixo = $sublinha['prefixo'];
					$thesufixo = $sublinha['sufixo'];		
				} else { 
					$traitnamecol= 'TraitName_English'; 
					$trcatetxt = $sublinha['categtxteng'];
					$theprefixo = $sublinha['prefixoeng'];
					$thesufixo = $sublinha['sufixoeng'];
				}
				$trcategpont =  $sublinha['categpontuacao'];
				if ($subidx>0) {
					$valor = "";
					$valor = descricaoValor($otraitid,$otipo,$newunit,$oprojetoid,$taxondetcol,$taxonid,$traitnamecol,$oformato,$comunidade,$trcategpont,$trcatetxt,$groupby,$groubyState, $thedecimal);
					//echo "SUBLINHA:";
					//echopre($sublinha);
					//echo "<br />getn:".$getn;		
					//echo "<br/>valor:".$valor."  falta:".$falta."<br />";	
					if ((empty($valor) || $valor==0) && $falta==1) { 
						$getn = simpleTraitName($parentn,$traitn);	
						$valor="<span style='color: red'>FALTA-".$getn."</span>";									
					}
 					if (!empty($valor)) {
 						if (($otipo=='Variavel|Texto' || $otipo=='Variavel|Categoria')) {
							$valor = trim($valor);
							if ($ucfirstvalue!="on") {
								$valor = ucfirst($valor);
							} else {
								$valor = mb_strtolower($valor);
								$valor = strtloweracentos($stn);
							}
						}
						$pega = "";
						if ($prefixoprevio=="on" && empty($valorprevio)) {
							$pega = trim($oprefixoprevio." ".$theprefixo);
							$oprefixoprevio = $pega;
						} else {
							$pega = trim($theprefixo);
							$oprefixoprevio = $theprefixo;
						}
			
						$alinha = $pega." ".$valor." ".$thesufixo;
						$alinha = trim($alinha);
						if (empty($subgrupo)) { $alinha = ucfirst($alinha);}
							$subgrupo .= " ".$alinha;
							$temgrp++;
						} else {
						if ($prefixoprevio=="on") {
							$pega = trim($oprefixoprevio." ".$theprefixo);
							$oprefixoprevio = $pega;
						} else {
							$pega = trim($theprefixo);
							$oprefixoprevio = $theprefixo;
						}
					}
					$valorprevio = $valor;
				} else {
					//cabeca de grupo
					$sqln = "SELECT TraitName FROM Traits WHERE TraitID=".$kl;
					$res4 = mysql_query($sqln);			
					$row4 = mysql_fetch_assoc($res4);
					if ($ucfirstvalue!="on") {
						$statename = ucfirst($row4['TraitName']);
					} else {
						$statename = mb_strtolower($row4['TraitName']);
						$statename = strtloweracentos($statename);
					}
					$alinha = $theprefixo." ".$statename.": ";
					$subgrupo .= " ".$alinha;
					$oprefixoprevio = '';
					$valorprevio = "";					
				}
				$subidx++;
				$fh = fopen("temp/".$pgfilename, 'w');
				$perc = round(($idx/$ntotal)*99,0);
				fwrite($fh, $perc);
				fclose($fh);
				//session_write_close();
				//flush();	
				$idx++;
			}
			if ($temgrp>0) { $descricao .= $subgrupo; }
		}				
	} else {		
		$thedecimal = $linha['quantdec'];
		$newunit = $linha['unitformat'];
		$comunidade = $linha['unitinclude'];
		$otipo  = $linha['TraitTipo'];
		$ucfirstvalue = $linha['ucfirstvalue'];
		$otraitid = $linha['TraitID'];
		$oformato = $linha['formato'];
		if ($lang=="BR") { 
			$traitnamecol= 'TraitName'; 
			$trcatetxt = $linha['categtxt'];
			$theprefixo = $linha['prefixo'];
			$thesufixo = $linha['sufixo'];		
		} else { 
			$traitnamecol= 'TraitNameEnglish'; 
			$trcatetxt = $linha['categtxteng'];
			$theprefixo = $linha['prefixoeng'];
			$thesufixo = $linha['sufixoeng'];		
		}
		$trcategpont =  $linha['categpontuacao'];
		$valor="";
		$valor = descricaoValor($otraitid,$otipo,$newunit,$oprojetoid,$taxondetcol,$taxonid,$traitnamecol,$oformato,$comunidade,$trcategpont,$trcatetxt,0,0,$thedecimal);
		//$valor='testando';$taxonid;	
		//echo 'valor:'.$valor;		
		$prefixoprevio = $linha['prefixoprevio'];
		$traitn = $linha['TraitName'];
		$parentn = $linha['ParentName'];
		//echo "LINHA:";
		//echopre($linha);
		//
		//echo "<br />getn:".$getn;		
		//echo "<br/>valor:".$valor."  falta:".$falta."<br />";

		if ((empty($valor) || $valor==0) && $falta==1) { 
			$getn = simpleTraitName($parentn,$traitn);
			$valor="<span style='color: red'>FALTA-".$getn."</span>";
		}
		if (!empty($valor)) {
			if (($otipo=='Variavel|Texto' || $otipo=='Variavel|Categoria')) {
				$valor = trim($valor);
				if ($ucfirstvalue!="on") {
					$valor = ucfirst($valor);
				} else {
					$valor = mb_strtolower($valor);
					$valor = strtloweracentos($valor);
				}
			}
			$pega = "";
			if ($prefixoprevio=="on" && empty($valorprevio)) {
				$pega = trim($oprefixoprevio." ".$theprefixo);
				$oprefixoprevio = $pega;
			} else {
				$pega = trim($theprefixo);
				$oprefixoprevio = $theprefixo;
			}

			$alinha = $pega." ".$valor." ".$thesufixo;
			$alinha = trim($alinha);
			if (empty($descricao)) { $alinha = ucfirst($alinha);}
			$descricao .= " ".$alinha;
	} else {
		if ($prefixoprevio=="on") {
				$pega = trim($oprefixoprevio." ".$theprefixo);
				$oprefixoprevio = $pega;
		} else {
				$pega = trim($theprefixo);
				$oprefixoprevio = $theprefixo;
		}
	}
	$valorprevio = $valor;			
	
	$fh = fopen("temp/".$pgfilename, 'w');
	$perc = round(($idx/$ntotal)*99,0);
	fwrite($fh, $perc);
	fclose($fh);
	//session_write_close();
	//flush();	
	$idx++;		
	
	}
	//ENTER PROGRESS HERE			
	

}

$descricao = trim($descricao);
$descricao = str_replace(".,",",",$descricao);
$descricao = str_replace("  "," ",$descricao);
$descricao = str_replace("  "," ",$descricao);
$descricao = str_replace("  "," ",$descricao);
$descricao = str_replace(" .",".",$descricao);
$descricao = str_replace("..",".",$descricao);
$descricao = str_replace("..",".",$descricao);
$descricao = str_replace(",",", ",$descricao);
$descricao = str_replace(" ,",",",$descricao);
$descricao = str_replace(" ;",";",$descricao);
$descricao = str_replace(" :",":",$descricao);
$descricao = str_replace(",,",",",$descricao);
$descricao = str_replace(".;",";",$descricao);
$descricao = str_replace(".,",",",$descricao);
//echo $descricao;

$listaspecs = phytotaxalista($oprojetoid,$taxondetcol,$taxonid,$traitfertid);
$todas .= $descricao."<br ><br >EXAMINED MATERIAL:<br />".$listaspecs;


}

$fh = fopen("temp/".$pgfilename, 'w');
$perc = 100;
fwrite($fh, $perc);
fclose($fh);
//session_write_close();
//sleep(2);
//require_once 'vsword/VsWord.php';
//VsWord::autoLoad();

$fname = "temp/monografiaspp_".$oprojetoid.".php";
//$fh = fopen("temp/".$fname, 'w');
$arq = "<?php
	header(\"Content-type: application/vnd.ms-word\");
	header(\"Content-Disposition: attachment; Filename=monografiaspp_".$oprojetoid.".doc\");
?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">
<title>".$fname."</title>
</head>
<body>".$todas."
</body>
</html>";

file_put_contents($fname,$arq);

//$doc = new VsWord();
//$parser = new HtmlParser($doc);
//$parser->parse($todas);
//$parser->parse($html);
//echo '<pre>'.($doc->getDocument()->getBody()->look()).'</pre>';
//$doc->saveAs($fname);

echo "<a href=\'".$fname."\'>Monografia.doc [".date ("F d Y H:i:s.", filemtime($fname))."]</a>";
?>
