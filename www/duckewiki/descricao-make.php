<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
//$lang="BR";


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
	//echo "<hr>".$otipo;	
	//echo "<br />".$sql2."<br />".$sql3;
} else {
	$sql2  = "SELECT mudaunidade(trvar.TraitVariation,'".$otipo."', trvar.TraitUnit,'".$newunit."') as vv FROM Traits_variation as trvar JOIN Especimenes as amos ON amos.EspecimenID=trvar.EspecimenID JOIN Identidade as idd ON idd.DetID=amos.DetID JOIN ProjetosEspecs as prj ON prj.EspecimenID=trvar.EspecimenID WHERE TraitID=".$otraitid." AND prj.ProjetoID=".$oprojetoid." AND idd.".$taxondetcol."=".$taxonid;
	$sql3  = "SELECT mudaunidade(trvar.TraitVariation,'".$otipo."', trvar.TraitUnit,'".$newunit."') as vv FROM Traits_variation as trvar JOIN Plantas as amos ON amos.PlantaID=trvar.PlantaID JOIN Identidade as idd ON idd.DetID=amos.DetID JOIN ProjetosEspecs as prj ON prj.PlantaID=trvar.PlantaID WHERE TraitID=".$otraitid." AND (trvar.EspecimenID=0 OR trvar.EspecimenID IS NULL) AND prj.ProjetoID=".$oprojetoid." AND idd.".$taxondetcol."=".$taxonid;
}


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
			$valor = $media."&plusmn;".$odesvio;
		}
		if ($oformato =='meansdrange') {
			$valor = $media."&plusmn;".$odesvio." (".$minimo."-".$maximo.")";
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
echo $sql;
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
echopre($thetraits);


$descricao = "";
$valorprevio = NULL;
$oprevioprefixo = "";
$ntotal = $steps;
$idx =1;

foreach($thetraits as $kk => $linha) {
	//checa se é grupo
	$tt = explode("_",$kk);
	if ($tt[0]=='grupo') {
		foreach($linha as $kl => $bystatelinha) {
			$subidx =0;
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
					$alinha = $theprefixo." ".$statename;
					$descricao .= " ".$alinha;
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
		//$valor="";
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
$descricao = str_replace(",;",";",$descricao);

	
$fh = fopen("temp/".$pgfilename, 'w');
$perc = 100;
fwrite($fh, $perc);
fclose($fh);
//session_write_close();
//sleep(2);

$savetoword=1;
if ($savetoword) {
require_once 'vsword/VsWord.php';
VsWord::autoLoad();

$doc = new VsWord();
$parser = new HtmlParser($doc);
$parser->parse("<div>".$descricao."</div>");
//$parser->parse($html);
echo '<pre>'.($doc->getDocument()->getBody()->look()).'</pre>';
$doc->saveAs('temp/htmlparser.docx');
}

echo "<div>".$descricao."</div>";

?>
