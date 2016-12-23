<?php
////////contains the form for the variables being edited/////////
///must be included inside a form named varform2///
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
$qq = "SELECT tr.* FROM FormulariosTraitsList as ff JOIN Traits AS tr USING(TraitID) WHERE ff.FormID=".$formid."  ORDER BY ff.Ordem";
$rr = mysql_query($qq,$conn);
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
    <table class='clean'>
      <tr class='cl'>
        <td class='cl'>".$tnomen."</td>
        <td class='cl' align='left'><img height=12 src=\"icons/icon_question.gif\" ";
			$help = $tdefien;
			echo  " onclick=\"javascript:alert('$help');\" alt='Explica a variavel' /></td>
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
        <td class='cl'><input type='radio' checked name='traitvar_".$row['TraitID']."' value='none'>".$noneword."</td>";
		} else {
			echo "
        <td class='cl'><input type='radio' name='traitvar_".$row['TraitID']."' value='none'>".$noneword."</td>";
		}
	} else {
		$tname = "traitvar_".$row['TraitID'];
		echo "
          <input type='hidden' name=$tname value=' '>";
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
		if ($cr % 6 == 0 || $cr==0) {
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
		
	    echo "
                <td class='cl'>
                  <table class='clean'>
                    <tr class='cl'>
                      <td class='cl' align='right'>
                        <input type='".$typein."' name='$tname' ";
						if ($valor==$rw['TraitID']) {echo "checked ";}
						echo " value='".$rw['TraitID']."' ></td>
                      <td class='cl' align='left'>".$stnomen."</td>
                      <td class='cl' align='left'><img height=12 src=\"icons/icon_question.gif\" ";
						$help = $stdefien;
			echo " onclick=\"javascript:alert('$help');\" alt='Explica a variavel' />&nbsp;</td>
                    </tr>
                  </table>
                </td>";
		$cr++;
		if ($cr % 6 == 0 || $cr==$nres) {
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
      <td class='cl'><input name='traitvar_".$row['TraitID']."' value='$val'></td>
      <td class='cl'>
        <select name='traitunit_".$row['TraitID']."'>";
			$string = 'traitunit_'.$row['TraitID'];
			$val = eval('return $'. $string . ';');
			if (empty($val) && !empty($row['TraitUnit'])) {
				echo "
          <option selected value='".$row['TraitUnit']."'>".$row['TraitUnit']."</option>";
		} elseif (!empty($val)) {
				echo "
          <option selected value='".$val."'>".$val."</option>";
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

	$val = explode(";",eval('return $'. $string . ';'));
	$oldimgvals = eval('return $'. $string . ';');

	$str = 'traitimgautor_'.$row['TraitID'];
	$valautors = eval('return $'. $str . ';');

	//echopre($val);
	//echopre($oldimgvals);
	if (count($val)>0) {
		echo "
          <input type=hidden name ='traitimgold_".$row['TraitID']."' value='".$oldimgvals."'>";
		foreach ($val as $kk => $vv) {
			$vv = $vv+0;
			if ($vv>0) {
				$qq = "SELECT * FROM Imagens WHERE ImageID='$vv'";
				$rt = mysql_query($qq,$conn);
				$rtw = mysql_fetch_assoc($rt);

				//diretorios das imagens
				$pthumb = 'img/thumbnails/';
				$imgbres = 'img/lowres/';
				$path = 'img/copias_baixa_resolucao/';

				$imagid = $rtw['ImageID'];
				$filename = trim($rtw['FileName']);

				$autor = $rtw['Autores'];
				//echo 'fotografo  2 = '.$autor;
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
				//echo '<br>fotografo  3 = '.$autotxt."<br>";

				$fotodata = $rtw['DateOriginal'];

				if (file_exists($path.$filename)) {
					$fn = explode("_",$filename);
					unset($fn[0]);
					unset($fn[1]);
					$fn = implode("_",$fn);

					$fntxt = $fn."   [";
					if (!empty($autotxt)) { $fntxt = $fntxt." ".GetLangVar('namefotografo').": ".$autotxt." - ".$fotodata."]";} else {
						$fntxt = $fntxt.$fotodata."]";
					}

					echo "
    <tr class='cl'>
      <td class='cl' colspan=2>
        <table class='clean'>
          <tr class='cl' >
            <td class='cl' ><a href=\"".$imgbres.$filename."\" class='MagicZoomPlus'  rel=\"zoom-position:right;zoom-height:200px; zoom-fade:true; smoothing-speed:17;opacity-reverse:true;\" ><img width=\"40\" src=\"".$pthumb.$filename."\" alt='Imagem'/></a></td>
            <td class='cl' >&nbsp;</td>
            <td class='tinny' id='fname_".$row['TraitID']."_".$imagid."'  class='tdformnotes'>$fntxt</td>";
			$fndeleted = "<STRIKE>$fn</STRIKE>";
			echo "
              <input type='hidden' id='fnamedeleted_".$row['TraitID']."_".$imagid."' value='$fndeleted'>
              <input type='hidden' id='imgtodel_".$row['TraitID']."_".$imagid."' name='imgtodel_".$row['TraitID']."_".$imagid."' value=''>
              <input type='hidden' id='imagid_".$row['TraitID']."_".$imagid."' name='imagid_".$row['TraitID']."_".$imagid."' value='$imagid'>
              <input type='hidden' id='fnameundeleted".$row['TraitID']."_".$imagid."' value='$fn'>
            <td class='cl' ><img height=\"14\" src=\"icons/application-exit.png\" onclick=\"javascript:deletimage('fnamedeleted_".$row['TraitID']."_".$imagid."','fname_".$row['TraitID']."_".$imagid."','imgtodel_".$row['TraitID']."_".$imagid."',1);\" alt='Apaga a Imagem' /></td>
            <td class='cl' ><img height=\"14\" src=\"icons/list-add.png\" onclick=\"javascript:deletimage('fnameundeleted".$row['TraitID']."_".$imagid."','fname_".$row['TraitID']."_".$imagid."','imgtodel_".$row['TraitID']."_".$imagid."',0);\" alt='Inclui Imagem' /></td>
          </tr>
        </table>
      </td>
    </tr>";
				} else {
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
        <input type=\"file\" name=\"$varname\">
        <script type=\"text/javascript\">
          window.addEvent('domready', function(){ new MultiUpload($( 'varform2' ).$varname );});
        </script>
        <input type=hidden name='traitimg_".$row['TraitID']."' value='imagem'>
      </td>
      <td class='cl'>
        <table class='dettable'>
          <tr>
            <td class='cl'>".GetLangVar('namefotografo')."s</td>
              <input type='hidden' name='traitimgautor_".$row['TraitID']."' value='".$valautors."'>
              <td><input type='text' name='traitimgautortxt_".$row['TraitID']."' value='".$addcoltxt."' readonly></td>
              <td><input type=button value=\"".GetLangVar('nameselect')."\" class='bsubmit' ";
				$valuevar = "traitimgautor_".$row['TraitID'];
				$valuetxt = "traitimgautortxt_".$row['TraitID'];
				$myurl ="addcollpopup.php?getaddcollids=$valautors&valuevar=$valuevar&valuetxt=$valuetxt&formname=varform2"; 
				echo " onclick = \"javascript:small_window('$myurl',400,400,'Add_from_Src_to_Dest');\"></td>
            </tr>
          </table>
        </td>
    </tr>";
}

//se texto
if ($row['TraitTipo']=='Variavel|Texto') {
	echo "
    <input type=hidden name='traitnone_".$row['TraitID']."' value='none'>";
	$string = 'traitvar_'.$row['TraitID'];
	if (!isset($_POST[$string])) {
		$val = eval('return $'. $string . ';');
	} else {
		$val= $_POST[$string];
	}
	//tem um problema aqui quando apaga os dados
	echo "
    <tr class='cl'><td class='cl'><textarea name='traitvar_".$row['TraitID']."' cols='80' rows='2' >".$val."</textarea></td></tr>";
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