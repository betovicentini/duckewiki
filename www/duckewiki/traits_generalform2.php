<?php
////////contains the form for the variables being edited/////////
///PRECISA ESTAR INCLUIDO NUM <form TAG com nome em $myformname
if (!isset($myformname)) {
	$myformname = 'varform2';
}
echo "
<table class='sortable autostripe' cellspacing='0' cellpadding='3' align='center' width='100%'>
<thead >
<tr>
  <th align='center'>".GetLangVar('nameclasse')."</th>
  <th align='center'>".GetLangVar('nametraits')."</th>
  <th align='center'>".GetLangVar('namevariacao')."</th>
</tr>
</thead>
<tbody>
";
//pega todas as variaveis do formulario 
//$qq = "SELECT * FROM Formularios WHERE FormID='".$formid."'";
//$rr = mysql_query($qq);
//$row= mysql_fetch_assoc($rr);
//$fieldids = explode(";",$row['FormFieldsIDS']);
$qf = "SELECT tr.* FROM FormulariosTraitsList as ff JOIN Traits AS tr USING(TraitID) WHERE ff.FormID=".$formid."  ORDER BY ff.Ordem";
$rr = mysql_query($qf,$conn);
$nvar = mysql_numrows($rr);
while ($row= mysql_fetch_assoc($rr)) { //para cada variavel no relatorio
	$qzpt = "SELECT * FROM Traits WHERE TraitID='".$row['ParentID']."'";
	$rrpt = @mysql_query($qzpt,$conn);
	$rwpt= @mysql_fetch_assoc($rrpt);
	//$zz = explode("-",$row['PathName']);
	$tname = $row['TraitName'];
	if ($traitsinenglish==1) {
		$tnomen = $row['TraitName_English'];
		$tdefien = $row['TraitDefinicao_English'];
		$trclass = $rwpt['TraitName_English'];
		$noneword = 'none';
	} else {
		$tnomen = $row['TraitName'];
		$tdefien = $row['TraitDefinicao'];
		$noneword = 'nenhum';
		$trclass = $rwpt['TraitName'];
	}
	//$trclass = trim($zz[0]);
    echo "
<tr>
  <td >".$trclass."</td>
  <td >
    <table class='clean' align='right'>
      <tr class='cl'>
        <td class='cl' >".$tnomen."</td>
        <td class='cl' align='left'><img height='12' src=\"icons/icon_question.gif\" ";
			$help = $tdefien;
			echo  " onclick=\"javascript:alert('$help');\" alt='Explica variável' /></td>
      </tr>
    </table>
  </td>
  <td>
    <table class='clean'>";
	//se categoria
		if ($row['TraitTipo']=='Variavel|Categoria') {
			//opcoes de variaves categoricas
			echo "
      <tr class='cl'>";
	if ($row['MultiSelect']!='Sim') {
		$tname = "traitvar_".$row['TraitID'];
		$val = eval('return $'.$tname.';');
		$val = trim($val);
		if (empty($val) || $val=='none') {
			echo "
        <td class='cl'><input type='radio' checked name='traitvar_".$row['TraitID']."' value='none' />".$noneword."</td>";
		} else {
			echo "
        <td class='cl'><input type='radio' name='traitvar_".$row['TraitID']."' value='none' />".$noneword."</td>";
		}
	} else {
		$tname = "traitvar_".$row['TraitID'];
		echo "
          <td class='cl'><input type='hidden' name='".$tname."' value=' ' /></td>";
	}
	echo "
          <td class='cl'>";
	$qq = "SELECT * FROM Traits WHERE ParentID='".$row['TraitID']."' ORDER BY TraitName";
	$res = mysql_query($qq,$conn);
	$nres = mysql_numrows($res);
	echo "
            <table class='clean'>"; 
	$cr = 0;
	while ($rw= mysql_fetch_assoc($res)) { //para cada estado de variacao
		if ($row['MultiSelect']=='Sim') {
				$typein = 'checkbox';
				$tname = "traitmulti_".$row['TraitID']."_".$rw['TraitID'];
				$valor = eval('return $'.$tname.';');
		} else {
				$typein='radio';
				$tname = "traitvar_".$row['TraitID'];
				$valor = eval('return $'.$tname.';');
		}
		if ($cr % 4 == 0 || $cr==0) {
			echo "
              <tr class='cl'>";
	    }
		//$val = trim($val);
		$tid = $rw['TraitID'];
		if ($traitsinenglish==1) {
			$stnomen = $rw['TraitName_English'];
			$stdefien = $rw['TraitDefinicao_English'];
		} else {
			$stnomen = $rw['TraitName'];
			$stdefien = $rw['TraitDefinicao'];
		}
		$stnomen = str_replace(" ","&nbsp;",$stnomen);
	    echo "
                <td class='cl'>
                  <table class='clean'>
                    <tr class='cl'>
                      <td class='cl' align='right'>
                        <input type='".$typein."' name='$tname' ";
						if ($valor==$rw['TraitID']) {echo " checked='checked' ";}
						echo " value='".$rw['TraitID']."'  /></td>
                      <td class='cl' align='left'>".$stnomen."</td>
                      <td class='cl' align='left'><img height='12' src=\"icons/icon_question.gif\" ";
						$help = $stdefien;
			echo " onclick=\"javascript:alert('$help');\" alt='Explica variável' />&nbsp;</td>
                    </tr>
                  </table>
                </td>";
		$cr++;
		if ($cr % 4 == 0 || $cr==$nres) {
			echo "
              </tr>";
	    }
	} 
	echo "
            </table>
        </td>
    </tr>";
}


//se quantitativo
if ($row['TraitTipo']=='Variavel|Quantitativo') {
	$string = 'traitvar_'.$row['TraitID'];
	if (!isset($_POST[$string])) {
		$val = eval('return $'. $string . ';');
	} else {
		$val= $_POST[$string];
	}
	echo "
    <tr class='cl'>
      <td class='cl'><input name='traitvar_".$row['TraitID']."' value='$val' /></td>
      <td class='cl'>
        <select name='traitunit_".$row['TraitID']."'>";
			$string = 'traitunit_'.$row['TraitID'];
			$val = eval('return $'. $string . ';');
			if (empty($val) && !empty($row['TraitUnit'])) {
				echo "
          <option selected='selected' value='".$row['TraitUnit']."'>".$row['TraitUnit']."</option>";
		} elseif (!empty($val)) {
				echo "
          <option selected='selected' value='".$val."'>".$val."</option>";
		}
		$qq = "SELECT * FROM VarLang WHERE VariableName LIKE '%traitunit%' ORDER BY '$lang' ASC";
		$res = mysql_query($qq,$conn);
		if ($res) {
		while ($rwu=mysql_fetch_assoc($res)) {
			$varname = $rwu['VariableName'];
			$zz = explode("_",$varname);
			if ($zz[1]!='desc') {
				$subsname = 'traitunit'.$menugrp;
				echo "
          <option value='".GetLangVar($varname)."'>".GetLangVar($varname)."</option>";
			}
		}
		}
	echo "
        </select>
      </td>
    </tr>";
}
//se imagem
if ($row['TraitTipo']=='Variavel|Imagem') {
	$string = 'trait_'.$row['TraitID'];
	$imgfile = 'traitimg_'.$row['TraitID'];
	$vval = eval('return $'.$string.';');
	$val = explode(";",$vval);
	$oldimgvals = eval('return $'.$string.';');
	$str = 'traitimgautor_'.$row['TraitID'];
	$valautors = eval('return $'.$str .';');
	if (count($val)>0) {
		echo "
    <tr class='cl'>
      <td class='cl' colspan='2'>
          <input type='hidden' name ='traitimgold_".$row['TraitID']."' value='".$oldimgvals."' />
      </td>
    </tr>";
		foreach ($val as $kk => $vv) {
			$vv = $vv+0;
			if ($vv>0) {
				$qq = "SELECT * FROM Imagens WHERE ImageID='".$vv."'";
				$rt = mysql_query($qq,$conn);
				$rtw = mysql_fetch_assoc($rt);

				//diretorios das imagens
				$pthumb = 'img/thumbnails/';
				$imgbres = 'img/lowres/';
				$path = 'img/copias_baixa_resolucao/';
				$orgpath = 'img/originais/';

				$imagid = $rtw['ImageID'];
				$filename = trim($rtw['FileName']);

				$autor = $rtw['Autores'];
				$autorarr = explode(";",$autor);
				if (count($autorarr)>0) {
					$j=1;
					foreach ($autorarr as $aut) {
						$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$aut."'";
							$res = mysql_query($qq,$conn);
							$rwr = mysql_fetch_assoc($res);
						if ($j==1) {
							$autotxt = 	$rwr['Abreviacao'];
						} else {
							$autotxt = $autotxt."; ".$rwr['Abreviacao'];
						}
						$j++;
					}
				} 

				$fotodata = $rtw['DateOriginal'];
				$fnl = trim($filename);
				if (file_exists($orgpath.$filename) && $fnl!='') {
					$fn = explode("_",$filename);
					unset($fn[0]);
					unset($fn[1]);
					$fntxt = '';
					$fn = implode("_",$fn);
					if (!empty($autotxt)) { 
						$fntxt = $fn." [".GetLangVar('namefotografo').": ".$autotxt;
					}
					if (!empty($fotodata)) {
						if ($fntxt!='') {
							$fntxt .= " - ".$fotodata."]  ";
						} else {
							$fntxt = $fn." [".$fotodata."]  ";
						}
					} elseif ($fntxt!='') {
						$fntxt .= "]  ";
					}

					if ($fntxt=='') {
						$fntxt = $fn;
					} 

					echo "
    <tr class='cl'>
      <td class='cl' colspan='2'>
        <table class='clean'>
          <tr class='cl' >
            <td class='cl' ><a href=\"".$imgbres.$filename."\" class='MagicZoomPlus'  rel=\"zoom-position:right;zoom-height:200px; zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" ><img width=\"40\" src=\"".$pthumb.$filename."\" alt='Imagem' /></a></td>
            <td class='cl' >&nbsp;</td>
            <td class='tinny' id='fname_".$row['TraitID']."_".$imagid."'  class='tdformnotes'>$fntxt";
			$fndeleted = "<STRIKE>$fn</STRIKE>";
			echo "
            </td>  
            <td class='cl' ><img height='15' src=\"icons/application-exit.png\" onclick=\"javascript:deletimage('fnamedeleted_".$row['TraitID']."_".$imagid."','fname_".$row['TraitID']."_".$imagid."','imgtodel_".$row['TraitID']."_".$imagid."',1);\" /></td>
            <td class='cl' ><img height='15' src=\"icons/list-add.png\" onclick=\"javascript:showimage('fnameundeleted".$row['TraitID']."_".$imagid."','fname_".$row['TraitID']."_".$imagid."','imgtodel_".$row['TraitID']."_".$imagid."',0);\" /></td>
            <td class='cl' >
              <input type='hidden' id='fnamedeleted_".$row['TraitID']."_".$imagid."' value='$fndeleted' />
              <input type='hidden' id='imgtodel_".$row['TraitID']."_".$imagid."' name='imgtodel_".$row['TraitID']."_".$imagid."' value='' />
              <input type='hidden' id='imagid_".$row['TraitID']."_".$imagid."' name='imagid_".$row['TraitID']."_".$imagid."' value='$imagid' />
              <input type='hidden' id='fnameundeleted".$row['TraitID']."_".$imagid."' value='$fntxt' />
            </td>
          </tr>
        </table>
      </td>
    </tr>";
				} 
				else {
					$refname = 'traitimg_'.$row['TraitID'];
					$val = eval('unset($'.$refname.');');
				}
			}
		}
	}
	echo "
    <tr class='cl'>
      <td class='cl'>";
		$varname = 'trait_'.$row['TraitID'];
		echo "
        <input name=\"".$varname."[]\" id=\"$varname\" type=\"file\" multiple />
        <input type='hidden' name='traitimg_".$row['TraitID']."' value='imagem' />
      </td>
      <td class='cl'>
        <table class='dettable'>
          <tr>
            <td class='cl'>".GetLangVar('namefotografo')."s</td>
              <td>
                <input type='hidden' name='traitimgautor_".$row['TraitID']."' value='".$valautors."' />
                <input type='text' name='traitimgautortxt_".$row['TraitID']."' value='".$addcoltxt."' readonly='readonly' />
              </td>
              <td><input type='button' value=\"".GetLangVar('nameselect')."\" class='bsubmit' ";
				$valuevar = "traitimgautor_".$row['TraitID'];
				$valuetxt = "traitimgautortxt_".$row['TraitID'];
				$myurl ="addcollpopup.php?getaddcollids=$valautors&amp;valuevar=$valuevar&amp;valuetxt=$valuetxt&amp;formname=varform2"; 
				echo " onclick = \"javascript:small_window('$myurl',400,400,'Add_from_Src_to_Dest');\" /></td>
            </tr>
          </table>
        </td>
    </tr>";
}

//se texto
if ($row['TraitTipo']=='Variavel|Texto') {
	echo "
    ";
	$string = 'traitvar_'.$row['TraitID'];
	if (!isset($_POST[$string])) {
		$val = eval('return $'. $string . ';');
	} else {
		$val= $_POST[$string];
	}
	//tem um problema aqui quando apaga os dados
	echo "
    <tr class='cl'>
      <td class='cl'>
        <input type='hidden' name='traitnone_".$row['TraitID']."' value='none' />
        <textarea name='traitvar_".$row['TraitID']."' cols='80' rows='2' >".$val."</textarea>
      </td>
    </tr>";
}

//se taxonomia
if ($row['TraitTipo']=='Variavel|Taxonomy') {
	echo "
    ";
	$string = 'traitvar_'.$row['TraitID'];
	if (!isset($_POST[$string])) {
		$val = eval('return $'. $string . ';');
	} else {
		$val= $_POST[$string];
	}
	if (!empty($val)) {
		$specieslist = strip_tags(describetaxacomposition($val,$conn,$includeheadings=TRUE));
	}
	//tem um problema aqui quando apaga os dados
	echo "
    <tr class='cl'>
      <td class='cl'>
        <table>
        <tr>
        <td>
        <input type='hidden' name='traitvar_".$row['TraitID']."' value='".$val."' />
          <textarea cols='60' rows='2' name='specieslist' readonly='readonly'>".$specieslist."</textarea>
          </td>
         <td align='left'>
          <input type='button' value='".GetLangVar('nameselect')."' class='bsubmit' ";
          $myurl ="selectspeciespopup.php?formname=varform2&amp;elementname=traitvar_".$row['TraitID']."&amp;destlistlist=".$val;
          echo " onclick = \"javascript:small_window('$myurl',500,400,'Seleciona Taxa');\" />
        </td>
        </tr>
        </table>
      </td>
    </tr>";
}

//se taxonomia
if ($row['TraitTipo']=='Variavel|LinkEspecimenes') {
	echo "
    ";
	$string = 'traitvar_'.$row['TraitID'];
	if (!isset($_POST[$string])) {
		$val = eval('return $'. $string . ';');
	} else {
		$val= $_POST[$string];
	}
	$specvar2 = "specrestxt".$row['TraitID'];
	if (!empty($val)) {
		$qsp = "SELECT CONCAT(pess.Abreviacao,' ',spec.Number,'    -  ', if (gettaxonname(spec.DetID,1,0) IS NULL,'',gettaxonname(spec.DetID,1,0))) as nome, spec.EspecimenID, CONCAT(spec.Ano,'-',spec.Mes,'-',spec.Day) as datacol  FROM Especimenes as spec JOIN Pessoas as pess ON spec.ColetorID=pess.PessoaID WHERE spec.EspecimenID=".$val;
		$rsp = mysql_query($qsp,$conn);
		$rwsp = mysql_fetch_assoc($rsp);
		$$specvar2  = $rwsp['nome'];
	}
	//tem um problema aqui quando apaga os dados
	echo "
    <tr class='cl'>
      <td class='cl'>
        <table>
        <tr>
        <td>";
        $idres = "specres".$row['TraitID'];
        $var2 = $$specvar2;
        autosuggestfieldval5('search-specimen.php',$specvar2,$var2,$idres,$string,$val,true,60,'Selecione por COLETOR e/ou NUMERO');
echo "
          </td>
        </tr>
        </table>
      </td>
    </tr>";

}
//SE PESSOAS
if ($row['TraitTipo']=='Variavel|Pessoa') {
	echo "
    ";
	$string = 'traitvar_'.$row['TraitID'];
	if (!isset($_POST[$string])) {
		$val = eval('return $'. $string . ';');
	} else {
		$val= $_POST[$string];
	}
	//echo $string."  ".$val;
	if (!empty($val)) {
		$addcolarr = explode(";",$val);
		$addcoltxt = '';
		$j=1;
		foreach ($addcolarr as $kk => $vl) {
			$qq = "SELECT * FROM Pessoas WHERE PessoaID='".$vl."'";
			$res = mysql_query($qq,$conn);
			$rrw = mysql_fetch_assoc($res);
			if ($j==1) {
				$addcoltxt = $rrw['Abreviacao'];
			} else {
				$addcoltxt = $addcoltxt."; ".$rrw['Abreviacao'];
			}
			$j++;
		}
	}
	//tem um problema aqui quando apaga os dados
	echo "
    <tr class='cl'>
      <td class='cl'>
        <table>
        <tr>
        <td>
        <input type='hidden' id='traitvar_".$row['TraitID']."'  name='traitvar_".$row['TraitID']."' value='".$val."' />
          <textarea cols='60' rows='2' name='addcoltxt".$row['TraitID']."' readonly='readonly'>".$addcoltxt."  </textarea>
          </td>
             <td><input type=button value=\"Selecione\" class='bsubmit'  ";
		$myurl ="addcollpopup.php?valuevar=traitvar_".$row['TraitID']."&valuetxt=addcoltxt".$row['TraitID']."&getaddcollids=$val&formname=varform2"; 
		echo " onclick = \"javascript:small_window('$myurl',800,500,'Seleciona Pessoas');\" /></td>
        </tr>
        </table>
      </td>
    </tr>";
}
echo "
  </table>
 </td>
</tr>";
}//end of loop de cada variavel relatorio
echo "
</tbody>
</table>";
?>