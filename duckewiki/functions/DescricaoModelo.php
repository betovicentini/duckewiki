<?php
function printItem($tipo, $traitname, $tempid,$temppid, $cls, $clss, $cordiv, $txtengl, $txtport, $nformat, $negrito, $italico, $sublinhado, $unidade, $case, $isgrupo, $grupo, $simbolo) {
//echo $tipo."<br />";
if ($negrito=='on') { $negrito = 'checked'; }
if ($italico=='on') { $italico = 'checked'; }
if ($sublinhado=='on') {$sublinhado = 'checked';}
if ($unidade=='on') {$unidade = 'checked';}
if ($case=='uppercase') {$uppercase = 'checked';}
if ($case=='lowercase') {$lowercase = 'checked';}
if ($case=='nochange') {$nochangecase = 'checked';}
if ($nformat=='media') {$mean = 'checked';}
if ($nformat=='range') {$range = 'checked';}
if ($nformat=='max') {$max = 'checked';}
if ($nformat=='min') {$min = 'checked';}
if ($simbolo==1) {$simbolo1 = 'checked';}
if ($simbolo==2) {$simbolo2 = 'checked';}
if ($simbolo==3) {$simbolo3 = 'checked';}
if ($simbolo==4) {$simbolo4 = 'checked';}
if ($simbolo==5) {$simbolo5 = 'checked';}
if ($nformat=='min') {$min = 'checked';}
if ($tipo=='Quantitativo') {
//QUANTITATIVO
$template = "<div data-traitid=\"".$tempid."\" class=\"".$cls."\"  style=\"border: solid 1px gray; border-radius: 5px; background-color: ".$cordiv."; padding: 3px; margin: 5px 0 5px 0; font-size: 0.8em; cursor: move\" ><span onmouseover=\"Tip('".$traitname."');\">".$traitname."</span>&nbsp;<span onmouseover=\"Tip(\'Defina o formato para os valores\');\">[N-formato:<input  type=\"radio\"  ".$mean."  value=\"media\"  class=\"".$clss."numformat\" name=\"nf".$temppid."\">média&nbsp;<input  type=\"radio\"  ".$range." value=\"range\" class=\"".$clss."numformat\" name=\"nf".$temppid."\">range&nbsp;<input  type=\"radio\" ".$min."  value=\"min\" class=\"".$clss."numformat\" name=\"nf".$temppid."\">min&nbsp;<input  type=\"radio\"  ".$max."  value=\"max\" class=\"".$clss."numformat\" name=\"nf".$temppid."\">max]</span>&nbsp;&nbsp;<span onmouseover=\"Tip('Defina o formato do texto');\">[Txt-formato:<input type=\"checkbox\" class=\"".$clss."negrito\" ".$negrito." /><b>N</b>&nbsp;<input type=\"checkbox\" class=\"".$clss."sublinhado\" ".$sublinhado." /><u>S</u>&nbsp;<input type=\"checkbox\" class=\"".$clss."italico\" ".$italico." /><i>I</i>]</span>&nbsp;&nbsp;<input type=\"checkbox\" class=\"".$clss."unidade\" ".$unidade." />adicionar unidade &nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli('".$temppid."');\" onmouseover=\"Tip('remove o item');\"></div>"; 
}
//CATEGORICO
if ($tipo=='Categoria') {
	if (!empty($grupo)) {
		$chgp = 'checked';
	}
	if ($isgrupo) {
		$gpby = '';
	}
	else {
		$gpby = "&nbsp;&nbsp;<input id=\"groupby".$temppid."\" type=\"checkbox\"  onclick=\"javascript: groupby('".$temppid."','".$traitname."');\" ".$chgp." />AgruparPor";
	}
	$template =  "<div data-traitid=\"".$tempid."\" class=\"".$cls."\"  style=\"border: solid 1px gray; border-radius: 5px; background-color:  ".$cordiv."; padding: 3px; margin: 5px 0 5px 0; font-size: 0.8em; cursor: move\" ><span onmouseover=\"Tip('".$traitname."');\">".$traitname."</span>&nbsp;<span onmouseover=\"Tip(\'Defina o formato do texto\');\">[Txt-formato:<input type=\"checkbox\" class=\"".$clss."negrito\" ".$negrito." /><b>N</b>&nbsp;<input type=\"checkbox\" class=\"".$clss."sublinhado\" ".$sublinhado." /><u>S</u>&nbsp;<input type=\"checkbox\" class=\"".$clss."italico\" ".$italico." /><i>I</i>&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$uppercase." value=\"uppercase\" name=\"cba".$temppid."\">CALTA&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$lowercase." value=\"lowercase\" name=\"cba".$temppid."\">cbaixa
&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$nochangecase." value=\"nochange\" name=\"cba".$temppid."\">comoEscrito]</span>&nbsp;".$gpby."&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli('".$temppid."');\" onmouseover=\"Tip('remove o item');\"> <span class=\"grupo\" id=\"grupo_".$temppid."\">".$grupo."</span></div>"; 
}

//TEXTO
if ($tipo=='texto') {
	$template = "<div data-textid=\"".$tempid."\" class=\"".$cls."\" style=\"padding: 5px; margin: 5px 0 5px 0; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color:  ".$cordiv."; border: solid thin gray; cursor: move;\"  onmouseover=\"Tip('Caixa para texto livre');\"><img height=\"16\" src=\"icons/BrasilFlagicon.png\">&nbsp;<input class=\"".$clss."port\" size=\"60%\" type=\"text\" value='".$txtport."'><br /><img height=\"16\" src=\"icons/usFlagicon.png\">&nbsp;<input class=\"".$clss."engl\" size=\"60%\" type=\"text\" value='".$txtengl."'>&nbsp;<span onmouseover=\"Tip('Defina o formato do texto');\">[Txt-formato:<input type=\"checkbox\" class=\"".$clss."negrito\" ".$negrito." /><b>N</b>&nbsp;<input type=\"checkbox\" class=\"".$clss."sublinhado\" ".$sublinhado." /><u>S</u>&nbsp;<input type=\"checkbox\" class=\"".$clss."italico\" ".$italico." /><i>I</i>&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$uppercase." value=\"uppercase\" name=\"cba".$temppid."\">CALTA&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$lowercase." value=\"lowercase\" name=\"cba".$temppid."\">cbaixa&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$nochangecase." value=\"nochange\" name=\"cba".$temppid."\">comoEscrito]</span>&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli('".$temppid."');\" onmouseover=\"Tip('remove o item');\"></div>";
}

//SYMBOLO
if ($tipo=='simbolo') {
	$template = "<div data-symbolid=\"".$tempid."\"  class=\"".$cls."\" style=\"padding: 5px; margin: 5px 0 5px 0; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color:  ".$cordiv."; border: #cccccc solid thin; cursor: move;\"  onmouseover=\"Tip('Defina símbolo');\"><span>Qual símbolo?&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo1." value=1 name=\"symb_".$temppid."\">Tipo1&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo2." value=2 name=\"symb_".$temppid."\">Tipo2&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo3." value=3 name=\"symb_".$temppid."\">Tipo3&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\"  ".$simbolo4." value=4 name=\"symb_".$temppid."\">Tipo4&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo5." value=5 name=\"symb_".$temppid."\">Tipo5</span>&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli('".$temppid."');\" onmouseover=\"Tip('remove o item');\"></div>";
}

$template = "<li class=\"".$clss."\"  id=\"".$temppid."\" >".$template."</li>";
return($template);
}
function printModelo($modelo,$conn) {
	$mymodel  = '';
	foreach ($modelo -> items as $item) {
		//echopre($item);
		$trid = ($item -> traitid)+0;
		//IF IS A TRAIT
		if ($trid>0) {
			$filtro ="SELECT * FROM `Traits` WHERE `TraitID`=".$trid;
			$res = mysql_query($filtro,$conn);
			$aa = mysql_fetch_assoc($res);
			$PathName = $aa['PathName'];
			$tipo = $aa['TraitTipo'];
			$name = $aa['TraitName'];
			$tp = explode("|",$tipo);
			$tp = $tp[1];
		} 
		else {
			if (substr($item->itemid,0,4)=='symb') {
				$trid = 'textsymbol';
				$tp = 'simbolo';
			} 
			else {
				$trid = 'textbox';
				$tp = 'texto';
			}
		}
		$gp = $item -> grupo;
		//echopre($gp);
		//define as variaveis do grupo
		if (count($gp)>0) {
			$thisgrupo = ''; 
			foreach ($gp as $subitem) {
				//echopre($subitem);
				$strid = ($subitem -> traitid)+0;
				//IF IS A TRAIT
				if ($strid>0) {
					$sfiltro ="SELECT * FROM `Traits` WHERE `TraitID`=".$strid;
					$sres = mysql_query($sfiltro,$conn);
					$saa = mysql_fetch_assoc($sres);
					$sPathName = $saa['PathName'];
					$stipo = $saa['TraitTipo'];
					$sname = $saa['TraitName'];
					$stp = explode("|",$stipo);
					$stp = $stp[1];
				} else {
					if (substr($subitem->itemid,0,4)=='symb') {
						$stp = 'simbolo';
					} else {
						$stp = 'texto';
					}
					$sname = '';
				}
				$subi = printItem($stp, $sname, $strid,$subitem -> itemid, $subitem -> cls, $subitem -> clss, $subitem -> divcolor, $subitem -> txtEnglish,$subitem -> txtPortugues, $subitem -> numberFormat, $subitem -> textformat -> bold, $subitem -> textformat -> italics, $subitem -> textformat -> underscore,$subitem -> unidade, $subitem -> textformat -> caso, 1, '', $subitem -> simbolo);
				$thisgrupo .= "
".$subi."
";
			}
			$iid = $item -> itemid;
			$ttt = "<br /><input id=\"gp".$iid."\" type=\"button\" value=\"Esconder grupo\" onclick=\"javascript:mostraresconder('".$iid."');\" ><div id=\"grupo".$iid."\"><ol data-onde=\"subgp".$iid."\" class=\"subgrupo\" type=\"a\" style=\"height: 150px; background-color: #FFFFCC;\">".$thisgrupo."</ol></div>";
			//echo "Este é o grupo".$thisgrupo."<br >";
		} 
		else {
			$ttt = '';
		}
		$itt = printItem($tp, $name, $trid,$item -> itemid, $item -> cls, $item -> clss, $item -> divcolor, $item -> txtEnglish,$item -> txtPortugues, $item -> numberFormat, $item -> textformat -> bold, $item -> textformat -> italics, $item -> textformat -> underscore,$item -> unidade, $item -> textformat -> caso, 0, $ttt, $item -> simbolo);
$mymodel .= "
".$itt."
";
	}
	return($mymodel);
}

function criaTabelaParaDescrever($monografiaid,$tbname,$unidade='mm',$includetaxa=FALSE,$conn) {
	$qu = "DROP TABLE ".$tbname;
	@mysql_query($qu,$conn);
	$qu = "CREATE TABLE ".$tbname."  SELECT tr.TraitVariationID,tr.TraitID,tr.TraitVariation,tr.TraitUnit,tr.EspecimenID, idd.FamiliaID,idd.GeneroID,idd.EspecieID,idd.InfraEspecieID,gettaxonname2(spp.DetID,0,0,0,0,1,0) as taxanome, traitvalueonly(tr.TraitVariation,tr.TraitID,tr.TraitID,'".$unidade."',0,0) as valor FROM Traits_variation AS tr JOIN MonografiaEspecs as mono ON mono.EspecimenID=tr.EspecimenID LEFT JOIN Especimenes as spp ON spp.EspecimenID=mono.EspecimenID LEFT JOIN Identidade as idd ON idd.DetID=spp.DetID WHERE mono.Monografiaid=".$monografiaid;
	//echo $qu."<br />";
	$ru = mysql_query($qu,$conn);
	if ($ru) {
		$qu = "CREATE INDEX taxanome ON ".$tbname." (taxanome(100)) USING BTREE";
		@mysql_query($qu,$conn);
		$qu = "CREATE INDEX TraitID ON ".$tbname." (TraitID) USING BTREE";
		@mysql_query($qu,$conn);
		if ($includetaxa) {
				$queries = array(
"InfraEspecieID" => "gettaxonname2(0,0,0,0,tr.InfraEspecieID,1,0)",
"EspecieID" => "gettaxonname2(0,0,0,tr.EspecieID,0,1,0)",
"GeneroID" => "gettaxonname2(0,0,tr.GeneroID,0,0,1,0)",
"FamiliaID" => "gettaxonname2(0,tr.FamiliaID,0,0,0,1,0)"
				);
				foreach ($queries as $fieldname => $vv) {
					$sql = "DROP TABLE `".$tbname."_t1`";
					@mysql_query($sql,$conn);
					$sql = "CREATE TABLE `".$tbname."_t1`  SELECT DISTINCT `". $fieldname."` FROM `".$tbname."` WHERE `". $fieldname."`>0";
					$rsql = @mysql_query($sql,$conn);
					if ($rsql) {
						$qu = "INSERT INTO ".$tbname."  (TraitVariationID,TraitID,TraitVariation,TraitUnit,".$fieldname.", taxanome,valor) (SELECT tr.TraitVariationID,tr.TraitID,tr.TraitVariation,tr.TraitUnit,tr.".$fieldname.",".$vv." as taxanome,traitvalueonly(tr.TraitVariation,tr.TraitID,tr.TraitID,'".$unidade."',0,0) as valor  FROM Traits_variation AS tr, `".$tbname."_t1` as mono WHERE  mono.".$fieldname."=tr.".$fieldname." AND tr.".$fieldname.">0)";
						$rssql = @mysql_query($sql,$conn);
						if ($rssql) {
							return(TRUE);
						}
					}
				}
		} else {
			return(TRUE);
		}
	} else {
		return(FALSE);
	}
}

function imprimeITEM($tipo, $arrayvalores, $otexto, $nformat, $negrito, $italico, $sublinhado, $unidade, $case, $simbolo, $taxanome,$tbname,$decimals,$theunit, $includeN, $outtype, $simbolos) {
$valorfinal = "";
//PEGA O VALOR SE FOR O CASO
$vals = $arrayvalores;
//echopre($vals);
//echo "nformat ".$nformat."<br />";
if ($tipo=='Quantitativo') {
			$media = array_sum($vals);
			$sdd =  standard_deviation($vals,FALSE);
			$sdd = round($sdd,$decimals);
			$N = count($vals);
			$media = round(($media/$N),$decimals);
			if (abs($media)>0) {
				sort($vals); 
    	        $min = round($vals[0],$decimals);
        	    rsort($vals); 
            	$max = round($vals[0],$decimals);
				if ($nformat=='media') {
					if ($outtype=='html') {
						if ($N>1) {
							$valorfinal = $media."&plusmn;".$sdd;
						} else {
							$valorfinal = $media;
						}
					} else {
						if ($N>1) {
							$valorfinal = $media."\pm".$sdd;
						} else {
							$valorfinal = $media;
						}
					}
				}
				if ($nformat=='range') {
					if ($N>1 && $min!=$max) {
						$valorfinal = $min."-".$max;
					} else {
						$valorfinal = $min;
					}
				}
				if ($nformat=='max') {$valorfinal = $max;}
				if ($nformat=='min') {$valorfinal = $min;}
				if ($unidade=='on') {$valorfinal = $valorfinal." ".$theunit;}
				if ($includeN) {$valorfinal = $valorfinal." (N=".$N.")";}
			}
	}
if ($tipo=='Categoria') {
			$thevals = $vals;
			foreach ($vals as $thekk => $theval) {
				$thevals[$thekk] = trim($theval);
			}
		   $unicos = array_count_values($thevals);
		   foreach($unicos as $ku => $vu) {
				if ($vu>0 && !empty($ku)) {
					if ($case=='uppercase') { $ku = strtupperacentos($ku); }
					if ($case=='lowercase') { $ku = strtloweracentos($ku); }
					if ($includeN) { $curval =  $ku." (N=".$vu.")";} else {  $curval = $ku; }
					$valorfinal = $valorfinal." ".$curval;
				}
		   }
	}
if ($tipo=='texto') {
	$valorfinal = $otexto;
	if ($case=='uppercase') { $valorfinal = strtupperacentos($valorfinal); }
	if ($case=='lowercase') { $valorfinal = strtloweracentos($valorfinal); }
}
if ($tipo=='simbolo' & is_array($simbolos)) {
		if ($simbolo==1) { $valorfinal = $simbolos[0];}
		if ($simbolo==2) { $valorfinal = $simbolos[1];}
		if ($simbolo==3) { $valorfinal = $simbolos[2];}
		if ($simbolo==4) { $valorfinal = $simbolos[3];}
		if ($simbolo==5) { $valorfinal = $simbolos[4];}
}
if ($outtype=='html' && !empty($valorfinal)) {
	if ($negrito=='on') { 
		$valorfinal = "<b>".$valorfinal."</b>";
	}
	if ($italico=='on') { 
		$valorfinal = "<i>".$valorfinal."</i>";
	}
	if ($sublinhado=='on') {
		$valorfinal = "<u>".$valorfinal."</u>";
	}
} 
elseif (!empty($valorfinal)) {
	if ($negrito=='on') { 
		$valorfinal = "\\textbf{".$valorfinal."}";
	}
	if ($italico=='on') { 
		$valorfinal = "\\textit{".$valorfinal."}";
	}
	if ($sublinhado=='on') {
		$valorfinal = "\\underline{".$valorfinal."}";
	}
}
//echo "aqui ".$valorfinal."<br />";
return($valorfinal);
}

////////
function sumarizaVALORES($modelo,$tbname, $taxanome, $decimals,$theunit, $includeN, $outtype,$simbolos, $withigroupseparator, $groupseparator, $groupclassseparator,$language, $groupclassseinitial, $conn) {
	$mymodel  = '';
	foreach ($modelo -> items as $item) {
		//echopre($item);
		$trid = ($item -> traitid)+0;
		//SE O ITEM DO MODELO FOR UM TRAIT, PEGA O VALOR
		if ($trid>0) {
			$gpsep = ' ';
			$filtro ="SELECT * FROM `Traits` WHERE `TraitID`=".$trid;
			$res = mysql_query($filtro,$conn);
			$aa = mysql_fetch_assoc($res);
			$PathName = $aa['PathName'];
			$tipo = $aa['TraitTipo'];
			$name = $aa['TraitName'];
			$tp = explode("|",$tipo);
			$tp = $tp[1];
		} 
		else {
			$gpsep = $groupseparator;
			if (substr($item->itemid,0,4)=='symb') {
				$tp = 'simbolo';
			} 
			else {
				$tp = 'texto';
				if ($language=='english') {
					$ootexto = $item -> txtEnglish;
				} else {
					$ootexto = $item -> txtPortugues;
				}
			}
		}
		$gp = $item -> grupo;
		//SE FOR UM GRUPO PEGA AS VARIAVEIS DO GRUPO
		$thisgrupo = "";
		if (count($gp)>0) {
			///PEGA OS ESTADOS DE VARIACAO DO TRAIT
			$filtro ="SELECT * FROM `Traits` WHERE `ParentID`=".$trid."  ORDER BY `TraitName`";
			$ress = mysql_query($filtro,$conn);
			while ($aaa = mysql_fetch_assoc($ress)) {
				$onome = $aaa['TraitName'];
				$stateid = $aaa['TraitID'];
				$tbbname = $tbname."_".$stateid;
				$qdel = "DROP TABLE ".$tbbname;
				mysql_query($qdel,$conn);
				//echo $qdel."<br />";
				$qselcs = "CREATE TABLE ".$tbbname." SELECT EspecimenID FROM ".$tbname."  as tr WHERE tr.taxanome='".$taxanome."' AND tr.TraitID='".$trid."'  AND UPPER(valor) LIKE '%".strtoupper($onome)."%'";
				$rvselcs = mysql_query($qselcs,$conn);
				if ($rvselcs) {
					$qu = "ALTER TABLE ".$tbbname." ADD PRIMARY KEY (EspecimenID)";
					@mysql_query($qu,$conn);
					//echo $qselcs."<br />";
				}
				$ssql = "SELECT COUNT(*) AS ids FROM ".$tbbname;
//				echo $ssql."<br />";
				$ssrs = mysql_query($ssql,$conn);
				$nsrw = mysql_numrows($ssrs);
				//echo "criou tabela com ".$nsrw." registros<br />";
//							echo "<br />valor ".$ssrw['ids'];
//				}
			
				//FORMATA O NOME DO ESTADO DE ACORDO COM O ESPECIFICADO
				$bold = $item -> textformat -> bold;
				$italics = $item -> textformat -> italics;
				$underscore -> textformat -> underscore;
				$ocaso = $item -> textformat -> caso;
				if ($ocaso=='uppercase') { $onome = strtupperacentos($onome); }
				if ($ocaso=='lowercase') { $onome = strtloweracentos($onome); }
				if ($outtype=='html') {
					if ($bold=='on') { 
						$onome = "<b>".$onome."</b>";
					}
					if ($italics=='on') { 
						$onome = "<i>".$onome."</i>";
					}
					if ($underscore=='on') {
						$onome = "<u>".$onome."</u>";
					}
				} 
				else {
					if ($bold=='on') { 
						$onome = "\\textbf{".$onome."}";
					}
					if ($italics=='on') { 
						$onome = "\\textit{".$onome."}";
					}
					if ($underscore=='on') {
						$onome = "\\underline{".$onome."}";
					}
				}
				$onomevalue = $onome.$groupclassseinitial;
				$temvalor = 0;
				$ositem = array();
				$ositemidx = array();
				$gpclassep = array();
				foreach ($gp as $idx => $subitem) {
					//ID DA VARIAVEL A IMPRIMIR
					$strid = ($subitem -> traitid)+0;
					//IF IS A TRAIT
					if ($strid>0) {
						$gpclassep[] = " ";
						$sfiltro ="SELECT * FROM `Traits` WHERE `TraitID`=".$strid;
						$sres = mysql_query($sfiltro,$conn);
						$saa = mysql_fetch_assoc($sres);
						$stipo = $saa['TraitTipo'];
						$stp = explode("|",$stipo);
						$stp = $stp[1];

						//PEGA OS VALORES DO GRUPO
						$theqv = "SELECT GROUP_CONCAT(`valor` SEPARATOR ';') AS osvalor FROM `".$tbname."` as tr, `".$tbbname."` as tbr WHERE tr.EspecimenID=tbr.EspecimenID AND tr.TraitID=".$strid;
						//echo "AQUI HOOOOO  ".$theqv."<br />";
						unset($thervi);
						$thervi = mysql_query($theqv,$conn);
						$therviw = mysql_fetch_assoc($thervi);
						//echopre($therviw);
						if (!empty($therviw['osvalor'])) {
							//PEGA O ARRAY DE VALORES
							$svals = explode(";",$therviw['osvalor']);
						} else {
							$svals =  NULL;
						}
						//echopre($svals);
					} 
					else {
						$gpclassep[] = $withigroupseparator;
						$svals = NULL;
						if (substr($subitem->itemid,0,4)=='symb') {
							$stp = 'simbolo';
						}
						 else {
						//$temvalor = 0;
						$stp = 'texto';
						if ($language=='english') {
								$otexto = $subitem -> txtEnglish;
							} 
							else {
								$otexto = $subitem -> txtPortugues;
							}
						}
					}
					if (count($svals)>0) {
						$temvalor++;
						$ositemidx[] = $idx;
					}
					$subi = imprimeITEM($stp, $svals, $otexto, $subitem -> numberFormat, $subitem -> textformat -> bold, $subitem -> textformat -> italics, $subitem -> textformat -> underscore, $subitem -> unidade,$subitem -> textformat -> caso, $subitem -> simbolo, $taxanome,$tbname,$decimals,$theunit,$includeN, $outtype, $simbolos);
					$ositem[]  = $subi;
				}
				if ($temvalor>0) {
					rsort($ositemidx);
					//echopre($gpclassep);
					$kidx = $ositemidx[0];
					//echo $kidx."<br />";
					for($id=0;$id<=$kidx;$id++) {
						$thev = $ositem[$id];
						if ($onomevalue==$onome.$groupclassseinitial) {
							$onomevalue .= $thev;
						} 
						else {
							$onomevalue .= $gpclassep[$id].$thev;
						}
					}
					if ($onomevalue==$onome.$groupclassseinitial || empty($thisgrupo)) {
						$thisgrupo .=   $onomevalue;
					} 
					else {
						$thisgrupo .=  $groupclassseparator.$onomevalue;
					}
				}
			}
		} 
		if (count($gp)==0) {
			if ($trid>0) {
				$qvv = "SELECT GROUP_CONCAT(valor SEPARATOR ';') AS valores FROM ".$tbname."  as tr WHERE  tr.taxanome='".$taxanome."'  AND tr.TraitID='".$trid."'";
				//echo $qvv."<br />";
				$rvv = mysql_query($qvv,$conn);
				$nrvv = mysql_numrows($rvv);
				if ($nrvv>0) {
					$rvww = mysql_fetch_assoc($rvv);
					//PEGA O ARRAY DE VALORES
					//echo "AQUI FUNCIONA: ".$rvww['valores']."<BR />";
					$osvalores = explode(";",$rvww['valores']);
				}
			} 
			else {
				$osvalores = array();
			}
			$itt = imprimeITEM($tp, $osvalores, $ootexto, $item -> numberFormat, $item -> textformat -> bold, $item -> textformat -> italics, $item -> textformat -> underscore, $item -> unidade,$item -> textformat -> caso, $item -> simbolo, $taxanome,$tbname,$decimals,$theunit,$includeN, $outtype, $simbolos);
			$runitem = $itt;
		} 
		else {
			$runitem = $thisgrupo;
		}
		if (empty($mymodel)) {
			$mymodel .= $runitem;
		} else {
			$mymodel .= $gpsep.$runitem;
		}

	}
	return($mymodel);
}




//////LISTA DE ESPECIMENES///
function printItemLista($tipo,$traitname, $tempid,$temppid, $cls, $clss, $cordiv, $txtengl, $txtport, $nformat, $negrito, $italico, $sublinhado,$case,$simbolo) {
if ($negrito=='on') { $negrito = 'checked'; }
if ($italico=='on') { $italico = 'checked'; }
if ($sublinhado=='on') {$sublinhado = 'checked';}
if ($case=='uppercase') {$uppercase = 'checked';}
if ($case=='lowercase') {$lowercase = 'checked';}
if ($case=='nochange') {$nochangecase = 'checked';}
if ($nformat=='decimaldg') {$decimaldg = 'checked';}
if ($nformat=='ddmmss') {$ddmmss = 'checked';}
if ($nformat=='lastname') {$lastname = 'checked';}
if ($nformat=='abreviacao') {$abreviacao = 'checked';}
if ($simbolo==1) {$simbolo1 = 'checked';}
if ($simbolo==2) {$simbolo2 = 'checked';}
if ($simbolo==3) {$simbolo3 = 'checked';}
if ($simbolo==4) {$simbolo4 = 'checked';}
if ($simbolo==5) {$simbolo5 = 'checked';}
if ($tipo=='latitude' || $tipo=='longitude') {
//QUANTITATIVO
$template = "<div data-value=\"".$tempid."\" class=\"".$cls."\"  style=\"border: solid 1px gray; border-radius: 5px; background-color: ".$cordiv."; padding: 3px; margin: 5px 0 5px 0; font-size: 0.8em; cursor: move\" ><span onmouseover=\"Tip('".$traitname."');\">".$traitname."</span>&nbsp;<span onmouseover=\"Tip(\'Defina o formato para os valores\');\">[N-formato:<input  type=\"radio\"  ".$decimaldg."  value=\"decimaldg\"  class=\"".$clss."numformat\" name=\"nf".$temppid."\">Decimal degree&nbsp;<input  type=\"radio\"  ".$ddmmss." value=\"ddmmss\" class=\"".$clss."numformat\" name=\"nf".$temppid."\">Degree Minutes Seconds]</span>&nbsp;&nbsp;<span onmouseover=\"Tip('Defina o formato do texto');\">[Txt-formato:<input type=\"checkbox\" class=\"".$clss."negrito\" ".$negrito." /><b>N</b>&nbsp;<input type=\"checkbox\" class=\"".$clss."sublinhado\" ".$sublinhado." /><u>S</u>&nbsp;<input type=\"checkbox\" class=\"".$clss."italico\" ".$italico." /><i>I</i>]</span>&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli('".$temppid."');\" onmouseover=\"Tip('remove o item');\"></div>"; 
}
//CATEGORICO
if ($tipo!='latitude' && $tipo!='longitude' && $tipo!='texto' && $tipo!='simbolo') {
if ($tipo=='coletorid') {
$coletortipo = "&nbsp;<span onmouseover=\"Tip(\'Defina o tipo\');\">[Formato:<input  type=\"radio\"  ".$lastname."  value=\"lastname\"  class=\"".$clss."numformat\" name=\"nf".$temppid."\">sobrenome&nbsp;<input  type=\"radio\"  ".$abreviacao." value=\"abreviacao\" class=\"".$clss."numformat\" name=\"nf".$temppid."\">abreviação]</span>&nbsp;";
} else {
$coletortipo = '';
}
	$template =  "<div data-value=\"".$tempid."\" class=\"".$cls."\"  style=\"border: solid 1px gray; border-radius: 5px; background-color:  ".$cordiv."; padding: 3px; margin: 5px 0 5px 0; font-size: 0.8em; cursor: move\" ><span onmouseover=\"Tip('".$traitname."');\">".$traitname."</span>".$coletortipo."&nbsp;<span onmouseover=\"Tip(\'Defina o formato do texto\');\">[Txt-formato:<input type=\"checkbox\" class=\"".$clss."negrito\" ".$negrito." /><b>N</b>&nbsp;<input type=\"checkbox\" class=\"".$clss."sublinhado\" ".$sublinhado." /><u>S</u>&nbsp;<input type=\"checkbox\" class=\"".$clss."italico\" ".$italico." /><i>I</i>&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$uppercase." value=\"uppercase\" name=\"cba".$temppid."\">CALTA&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$lowercase." value=\"lowercase\" name=\"cba".$temppid."\">cbaixa
&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$nochangecase." value=\"nochange\" name=\"cba".$temppid."\">comoEscrito]</span>&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli('".$temppid."');\" onmouseover=\"Tip('remove o item');\"></div>"; 
}

//TEXTO
if ($tipo=='texto') {
	$template = "<div data-textid=\"".$tempid."\" class=\"".$cls."\" style=\"padding: 5px; margin: 5px 0 5px 0; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color:  ".$cordiv."; border: solid thin gray; cursor: move;\"  onmouseover=\"Tip('Caixa para texto livre');\"><img height=\"16\" src=\"icons/BrasilFlagicon.png\">&nbsp;<input class=\"".$clss."port\" size=\"60%\" type=\"text\" value='".$txtport."'><br /><img height=\"16\" src=\"icons/usFlagicon.png\">&nbsp;<input class=\"".$clss."engl\" size=\"60%\" type=\"text\" value='".$txtengl."'>&nbsp;<span onmouseover=\"Tip('Defina o formato do texto');\">[Txt-formato:<input type=\"checkbox\" class=\"".$clss."negrito\" ".$negrito." /><b>N</b>&nbsp;<input type=\"checkbox\" class=\"".$clss."sublinhado\" ".$sublinhado." /><u>S</u>&nbsp;<input type=\"checkbox\" class=\"".$clss."italico\" ".$italico." /><i>I</i>&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$uppercase." value=\"uppercase\" name=\"cba".$temppid."\">CALTA&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$lowercase." value=\"lowercase\" name=\"cba".$temppid."\">cbaixa&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$nochangecase." value=\"nochange\" name=\"cba".$temppid."\">comoEscrito]</span>&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli('".$temppid."');\" onmouseover=\"Tip('remove o item');\"></div>";
}

//SYMBOLO
if ($tipo=='simbolo') {
	$template = "<div data-symbolid=\"".$tempid."\"  class=\"".$cls."\" style=\"padding: 5px; margin: 5px 0 5px 0; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color:  ".$cordiv."; border: #cccccc solid thin; cursor: move;\"  onmouseover=\"Tip('Defina símbolo');\"><span>Qual símbolo?&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo1." value=1 name=\"symb_".$temppid."\">Tipo1&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo2." value=2 name=\"symb_".$temppid."\">Tipo2&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo3." value=3 name=\"symb_".$temppid."\">Tipo3&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\"  ".$simbolo4." value=4 name=\"symb_".$temppid."\">Tipo4&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo5." value=5 name=\"symb_".$temppid."\">Tipo5</span>&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli('".$temppid."');\" onmouseover=\"Tip('remove o item');\"></div>";
}

$template = "<li class=\"".$clss."\"  id=\"".$temppid."\" >".$template."</li>";
return($template);
}
function printModeloLista($modelo,$arrayoffields,$conn) {
	$mymodel  = '';
	foreach ($modelo -> items as $item) {
			$trid = '';
			$tp = '';
			if (substr($item->itemid,0,4)=='symb') {
				$trid = 'textsymbol';
				$tp = 'simbolo';
			} 
			else {
				$ti = substr($item->itemid,0,3);
				if (substr($item->itemid,0,3)=='txt') {
					$trid = 'textbox';
					$tp = 'texto';
				} else {
					$trid = $item->itemid;
					$name = $arrayoffields[$trid];
					$tp = $trid;
			 }
			}
			//$itt = "<li>tipo: ".$tp."  trid:".$trid."  nome:".$name."   ti:".$ti."   ".$vv."</li>";
			$itt = printItemLista($tp, $name, $trid,$item -> itemid, $item -> cls, $item -> clss, $item -> divcolor, $item -> txtEnglish,$item -> txtPortugues, $item -> numberFormat, $item -> textformat -> bold, $item -> textformat -> italics, $item -> textformat -> underscore,$item -> textformat -> caso, $item -> simbolo);
$mymodel .= "
".$itt."
";
	}
	return($mymodel);
}

function criaTabelaParaLista($monografiaid,$tbname,$herbariumsigla,$conn) {
	$qu = "DROP TABLE ".$tbname;
	@mysql_query($qu,$conn);
	$qu = "CREATE TABLE ".$tbname."  SELECT
gettaxonname2(pltb.DetID,0,0,0,0,1,0) as taxanome,
colpessoa.Sobrenome as lastname, 
colpessoa.Abreviacao as coletor, 
pltb.AddColIDS as outroscoletores,
pltb.Number as number,
if(CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day)<>'0000-00-00',CONCAT(pltb.Ano,'-',pltb.Mes,'-',pltb.Day),'FALTA') as datacol,
pltb.Herbaria,
if(pltb.INPA_ID>0,pltb.INPA_ID+0,NULL) as ".$herbariumsigla.",
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'COUNTRY') as country,  
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'MAJORAREA') as majorarea,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'MINORAREA') as minorarea,
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'GAZfirstPARENT') as pargazetteer, 
localidadefields(pltb.GazetteerID, pltb.GPSPointID, pltb.MunicipioID, pltb.ProvinceID, pltb.CountryID, 'GAZETTEER') as gazetteer,
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 0) as longitude,
getlatlong(pltb.Latitude , pltb.Longitude, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 1) as latitude,
IF(ABS(pltb.Longitude)>0,pltb.Altitude+0,IF(pltb.GPSPointID>0,gpspt.Altitude+0,IF(ABS(gaz.Longitude)>0,gaz.Altitude+0,NULL))) as altitude
 FROM Especimenes as pltb
JOIN MonografiaEspecs as mono  ON mono.EspecimenID=pltb.EspecimenID
LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID
LEFT JOIN Gazetteer as gaz ON gaz.GazetteerID=pltb.GazetteerID  
LEFT JOIN Municipio as muni ON gaz.MunicipioID=muni.MunicipioID  
LEFT JOIN Province as provgaz ON muni.ProvinceID=provgaz.ProvinceID  
LEFT JOIN Country  as countrygaz ON provgaz.CountryID=countrygaz.CountryID  
LEFT JOIN GPS_DATA as gpspt ON gpspt.PointID=pltb.GPSPointID  
LEFT JOIN Gazetteer as gazgps ON gpspt.GazetteerID=gazgps.GazetteerID  
LEFT JOIN Municipio as munigps ON gazgps.MunicipioID=munigps.MunicipioID 
LEFT JOIN Province as provigps ON munigps.ProvinceID=provigps.ProvinceID  
LEFT JOIN Country  as countrygps ON provigps.CountryID=countrygps.CountryID
WHERE mono.MonografiaID='".$monografiaid."'";
	//echo $qu."<br />";
	$ru = mysql_query($qu,$conn);
	if ($ru) {
		$qu = "CREATE INDEX taxanome ON ".$tbname." (taxanome(100)) USING BTREE";
		@mysql_query($qu,$conn);
		$qu = "CREATE INDEX country ON ".$tbname." (country(100)) USING BTREE";
		@mysql_query($qu,$conn);
		$qu = "CREATE INDEX majorarea ON ".$tbname." (majorarea(100)) USING BTREE";
		@mysql_query($qu,$conn);
		$qu = "CREATE INDEX minorarea ON ".$tbname." (minorarea(100)) USING BTREE";
		@mysql_query($qu,$conn);
		$qu = "CREATE INDEX pargazetteer ON ".$tbname." (pargazetteer(100)) USING BTREE";
		@mysql_query($qu,$conn);
		$qu = "CREATE INDEX gazetteer ON ".$tbname." (gazetteer(100)) USING BTREE";
		@mysql_query($qu,$conn);
		$qu = "CREATE INDEX coletor ON ".$tbname." (coletor(100)) USING BTREE";
		@mysql_query($qu,$conn);
		$qu = "CREATE INDEX number ON ".$tbname." (number(100)) USING BTREE";
		@mysql_query($qu,$conn);
		return(TRUE);
	} else {
		return(FALSE);
	}
}

function formataITEMLISTA($tipo, $valor, $nformat, $negrito, $italico, $sublinhado, $case, $decimals,$outtype, $herbariaseparator,$addcoll) {
if ($outtype=='html') {
	$degree = "&deg";
} else {
	$degree = "\\textdegree";
}
$valorfinal = $valor;
//PEGA O VALOR SE FOR O CASO
//echopre($vals);
//echo "nformat ".$nformat."<br />";
if ($tipo=='latitude' || $tipo=='longitude') {
	if ($nformat=='decimaldg') {
		$valorfinal = round($valor,$decimals);
		$valorfinal = $valorfinal.$degree;
	} 
	else {
			$vl = abs($valor);
			$dg = floor($vl);
			$mm = $vl-$dg;
			$minutos = floor($mm);
			$ss = $mm-$minutos;
			$segundos = round($ss,2);
			if ($segundos==0) {
				$segundos = "00";
			}
			if ($minutos == 0) {
				$minutos = "00";
			}
			$valorfinal = $dg.$degree." ".$minutos."' ".$segundos."\" ";
			if ($valor<0 && $tipo=='longitude') {
				$valorfinal = $valorfinal." W";
			} 
			if ($valor>0 && $tipo=='longitude') {
				$valorfinal = $valorfinal." E";
			} 
			if ($valor<0 && $tipo=='latitude') {
				$valorfinal = $valorfinal." S";
			} 
			if ($valor>0 && $tipo=='latitude') {
				$valorfinal = $valorfinal." N";
			} 
		}
}

if ($tipo=='herbaria') {
	$valorfinal = str_replace(";"," ",$valorfinal);
	$valorfinal = str_replace(","," ",$valorfinal);
	$valorfinal = str_replace("  "," ",$valorfinal);
	$valorfinal = str_replace("  "," ",$valorfinal);
	$valorfinal = str_replace(" ",$herbariaseparator,$valorfinal);
}
if ($case=='uppercase') { $valorfinal = strtupperacentos($valorfinal); }
if ($case=='lowercase') { $valorfinal = strtloweracentos($valorfinal); }
if ($tipo=='coletorid' && !empty($addcoll)) {
		$addcoll  = explode(";",$addcoll);
		if (count($addcoll)>0) {
			$valorfinal .=  " et al.";
		}
}
if ($outtype=='html' && !empty($valorfinal)) {
	if ($negrito=='on') { 
		$valorfinal = "<b>".$valorfinal."</b>";
	}
	if ($italico=='on') { 
		$valorfinal = "<i>".$valorfinal."</i>";
	}
	if ($sublinhado=='on') {
		$valorfinal = "<u>".$valorfinal."</u>";
	}
} 
elseif (!empty($valorfinal)) {
	if ($negrito=='on') { 
		$valorfinal = "\\textbf{".$valorfinal."}";
	}
	if ($italico=='on') { 
		$valorfinal = "\\textit{".$valorfinal."}";
	}
	if ($sublinhado=='on') {
		$valorfinal = "\\underline{".$valorfinal."}";
	}
}
return($valorfinal);
}



function printLISTA($arrayoffields,$modelo,$printpais,$printprovince,$printmunicipio,$printpargaz,$printgaz,$decimals, $outtype,$simbolos, $countryseparator, $provinceseparator, $municipioseparator,$specimenseparator, $gazetteerseparator,$variableseparator,$herbariaseparator) {
	$modelpart = '';
	$previous = '';
	foreach ($modelo -> items as $item) {
		$kid = $item -> itemid;
		$val = $arrayoffields[$kid];
		//echo "AQUI  ".$kid."  =>  ".$val."  ".$tb."<br />";
		$tp = '';
		if ($previous=='symb' || substr($item->itemid,0,4)=='symb') {
			$variableseparator = '';
		}
		$previous = substr($item->itemid,0,4);
		if (substr($item->itemid,0,4)=='symb') {
			$tp = 'simbolo';
			$vid = ($item -> simbolo)-1;
			$val = $simbolos[$vid];
		} 
		else {
			if (substr($item->itemid,0,3)=='txt') {
				$trid = 'textbox';
				$tp = 'texto';
				if ($language=='english') {
					$val = $item -> txtEnglish;
				} else {
					$val = $item -> txtPortugues;
				}
			} else {
				$trid = $item->itemid;
				$tp = $kid;
				$nfor = $item -> numberFormat;
				if ($tp=='coletorid') {
					if ($nfor=='lastname') {
					$val = $arrayoffields['lastname'];
					}
				}
			 }
		}
		if (!empty($val)) {
			 $runitem = formataITEMLISTA($tp, $val,  $item -> numberFormat, $item -> textformat -> bold, $item -> textformat -> italics, $item -> textformat -> underscore, $item -> textformat -> caso, $decimals,$outtype,$herbariaseparator, $arrayoffields['outroscoletores']);
			//$modelpart .= " ".$runitem;
			//$runitem =  "AQUI  ".$kid."  =>  ".$val."  ".$tp."<br />";
			//$runitem =  "p:".$printpais." mj".$printprovince." minor".$printmunicipio." pargaz".$printpargaz."  gaz".$printgaz."<br />".$runitem."<br />";
			 if ($tp=='country' && $printpais>0) {
			 	$modelpart .= " ".$runitem.$countryseparator;
			 } 
			 if ($tp=='majorarea' && $printprovince>0) {
					 	$modelpart .= " ".$runitem.$provinceseparator;
			} 
			if ($tp=='minorarea' && $printmunicipio>0) {
					$modelpart .= " ".$runitem.$municipioseparator;
			}
			if ($tp=='pargazetteer' && $printpargaz>0) {
				 	$modelpart .= " ".$runitem.$gazetteerseparator;
			}
			if ($tp=='gazetteer' && $printgaz>0) {
					 $modelpart .= " ".$runitem.$gazetteerseparator;
			}
			if ($tp!='country' && $tp!='majorarea' && $tp!='minorarea' && $tp!='pargazetteer' && $tp!='gazetteer' ) {
				$modelpart  .= $variableseparator.$runitem;
			}
			
		}
	}
	return($modelpart);
}


function sumarizaLISTA($modelo,$tbname, $taxanome, $decimals, $outtype,$simbolos, $countryseparator, $provinceseparator, $municipioseparator,$specimenseparator, $gazetteerseparator, $variableseparator,$herbariaseparator, $conn) {
	//echopre($model);
	$mymodel  = '';
	$qz = "SELECT * FROM ".$tbname."  WHERE taxanome='".$taxanome."'  ORDER BY country,majorarea,minorarea,pargazetteer,gazetteer,coletor,number LIMIT 0,10";
	//echo $qz."<br />";
	$rz = mysql_query($qz,$conn);
	$nrz = mysql_numrows($rz);
	if ($nrz>0) {
		$pais = '';
		$province = '';
		$municipio = '';
		$gazpar = '';
		$gaztot = '';
		while($rzw = mysql_fetch_assoc($rz)) { 
			$printprovince= 0;
			$printpais= 0;
			$printmunicipio = 0;
			$printpargaz= 0;
			$printgaz = 0;
			if ($rzw['country']!=$pais) {
				$printpais=1;
				$pais = $rzw['country'];
			} 
			if ($rzw['majorarea']!=$province) {
				$printprovince=1;
				$province = $rzw['majorarea'];
			} 
			if ($rzw['minorarea']!=$municipio) {
				$printmunicipio=1;
				$municipio = $rzw['minorarea'];
			} 
			if ($rzw['pargazetteer']!=$gazpar) {
				$printpargaz=1;
				$gazpar = $rzw['pargazetteer'];
			} 
			if ($rzw['gazetteer']!=$gaztot) {
				$printgaz=1;
				$gaztot = $rzw['gazetteer'];
			} 
				$arrayoffields = array(
'lastname' => $rzw['lastname'],
'outroscoletores' => $rzw['outroscoletores'],
'coletorid' => $rzw['coletor'],
'number' => $rzw['number'],
'datacol' => $rzw['datacol'],
'herbaria' => $rzw['Herbaria'],
'country' => $rzw['country'],
'majorarea' => $rzw['majorarea'],
'minorarea' => $rzw['minorarea'],
'pargazetteer' => $rzw['pargazetteer'],
'gazetteer' => $rzw['gazetteer'],
'longitude' => $rzw['longitude'],
'latitude' => $rzw['latitude']);
				//echopre($rzw);
				$runitem2 = printLISTA($arrayoffields,$modelo,$printpais,$printprovince,$printmunicipio,$printpargaz,$printgaz,$decimals, $outtype,$simbolos, $countryseparator, $provinceseparator, $municipioseparator,$specimenseparator,$gazetteerseparator,$variableseparator,$herbariaseparator);
				//echo "aqui".$runitem2."<br />";
				$ruintem2 = trim($runitem2);
				$ruintem2 = rtrim($runitem2);
				if (!empty($runitem2)) {
					if (empty($mymodel)) {
						$mymodel .= $runitem2;
					} else {
						$mymodel .= $specimenseparator.$runitem2;
					}
					$mymodel = trim($mymodel);
				}
		}
	}
	return($mymodel);
}


//////////

/**
*Function that calculate average or mean value of array
*@param (array) $arr
*@return float
*/
function average($arr)
{
if (!is_array($arr)) return 0;
return array_sum($arr)/count($arr);
}
/**
* Calculate variance of array
* @param (array) $aValues
*@return float
*/
function variance($aValues, $bSample = false){
$fMean = array_sum($aValues) / count($aValues);
$fVariance = 0.0;
foreach ($aValues as $i)
{
$fVariance += pow($i - $fMean, 2);
}
$fVariance /= ( $bSample ? count($aValues) - 1 : count($aValues) );
return $fVariance;
}
/**
* Calculate standard deviation of array, by definition it is square root of variance
* @param (array) $aValues
* @return float
*/
function standard_deviation($aValues, $bSample = false)
{
$fVariance = variance($aValues, $bSample);
return (float) sqrt($fVariance);
}

?>