<?php
function printItem($tipo, $traitname, $tempid,$temppid, $cls, $clss, $cordiv, $txtengl, $txtport, $nformat, $negrito, $italico, $sublinhado, $unidade, $case, $isgrupo, $grupo, $simbolo) {
//echo $tipo."<br />";
if ($negrito=='on') { $negrito = 'checked'; }
if ($italico=='on') { $italico = 'checked'; }
if ($sublinhado=='on') {$sublinhado = 'checked';}
if ($unidade=='on') {$unidade = 'checked';}
if ($case=='uppercase') {$uppercase = 'checked';}
if ($case=='lowercase') {$lowercase = 'checked';}
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
	$template =  "<div data-traitid=\"".$tempid."\" class=\"".$cls."\"  style=\"border: solid 1px gray; border-radius: 5px; background-color:  ".$cordiv."; padding: 3px; margin: 5px 0 5px 0; font-size: 0.8em; cursor: move\" ><span onmouseover=\"Tip('".$traitname."');\">".$traitname."</span>&nbsp;<span onmouseover=\"Tip(\'Defina o formato do texto\');\">[Txt-formato:<input type=\"checkbox\" class=\"".$clss."negrito\" ".$negrito." /><b>N</b>&nbsp;<input type=\"checkbox\" class=\"".$clss."sublinhado\" ".$sublinhado." /><u>S</u>&nbsp;<input type=\"checkbox\" class=\"".$clss."italico\" ".$italico." /><i>I</i>&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$uppercase." value=\"uppercase\" name=\"cba".$temppid."\">CA&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$lowercase." value=\"lowercase\" name=\"cba".$temppid."\">cb]</span>&nbsp;".$gpby."&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli('".$temppid."');\" onmouseover=\"Tip('remove o item');\"> <span class=\"grupo\" id=\"grupo_".$temppid."\">".$grupo."</span></div>"; 
}

//TEXTO
if ($tipo=='texto') {
	$template = "<div class=\"".$cls."\" style=\"padding: 5px; margin: 5px 0 5px 0; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color:  ".$cordiv."; border: solid thin gray; cursor: move;\"  onmouseover=\"Tip('Caixa para texto livre');\"><img height=\"16\" src=\"icons/BrasilFlagicon.png\">&nbsp;<input class=\"".$clss."port\" size=\"60%\" type=\"text\" value='".$txtport."'><br /><img height=\"16\" src=\"icons/usFlagicon.png\">&nbsp;<input class=\"".$clss."engl\" size=\"60%\" type=\"text\" value='".$txtengl."'>&nbsp;<span onmouseover=\"Tip('Defina o formato do texto');\">[Txt-formato:<input type=\"checkbox\" class=\"".$clss."negrito\" ".$negrito." /><b>N</b>&nbsp;<input type=\"checkbox\" class=\"".$clss."sublinhado\" ".$sublinhado." /><u>S</u>&nbsp;<input type=\"checkbox\" class=\"".$clss."italico\" ".$italico." /><i>I</i>&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$uppercase." value=\"uppercase\" name=\"cba".$temppid."\">CA&nbsp;<input type=\"radio\" class=\"".$clss."lettercase\" ".$lowercase." value=\"lowercase\" name=\"cba".$temppid."\">cb]</span>&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli('".$temppid."');\" onmouseover=\"Tip('remove o item');\"></div>";
}

//SYMBOLO
if ($tipo=='simbolo') {
	$template = "<div class=\"".$cls."\" style=\"padding: 5px; margin: 5px 0 5px 0; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color:  ".$cordiv."; border: #cccccc solid thin; cursor: move;\"  onmouseover=\"Tip('Defina símbolo');\"><span>Qual símbolo?&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo1." value=1 name=\"symb_".$temppid."\">Tipo1&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo2." value=2 name=\"symb_".$temppid."\">Tipo2&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo3." value=3 name=\"symb_".$temppid."\">Tipo3&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\"  ".$simbolo4." value=4 name=\"symb_".$temppid."\">Tipo4&nbsp;<input type=\"radio\" class=\"".$clss."simbolo\" ".$simbolo5." value=5 name=\"symb_".$temppid."\">Tipo5</span>&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli('".$temppid."');\" onmouseover=\"Tip('remove o item');\"></div>";
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
		} else {
			if (substr($item->itemid,0,4)=='symb') {
				$tp = 'simbolo';
			} else {
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
		} else {
			$ttt = '';
		}
		$itt = printItem($tp, $name, $trid,$item -> itemid, $item -> cls, $item -> clss, $item -> divcolor, $item -> txtEnglish,$item -> txtPortugues, $item -> numberFormat, $item -> textformat -> bold, $item -> textformat -> italics, $item -> textformat -> underscore,$item -> unidade, $item -> textformat -> caso, 0, $ttt, $item -> simbolo);
$mymodel .= "
".$itt."
";
	}
	return($mymodel);
}

?>