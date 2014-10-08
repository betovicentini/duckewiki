<?php

session_start();
//Check whether the session variable
include "functions/HeaderFooter.php";
include "functions/SelectOptions.php";

$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} 

$ppost = cleangetpost($_POST,$conn);
$arval = $ppost;
@extract($ppost);
$gget = cleangetpost($_GET,$conn);
@extract($gget);
PopupHeader($title,$body);


$erro=0;
if ($final==1) {
	//checar por campos obrigatorios
	if (empty($traitname) || empty($traittipo) || empty($parenttraitid) || empty($traitdefinicao)) {
		echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
			<tr class='tdsmallbold' >
			<td align='center'>".GetLangVar('erro1')."</td>
			</tr>";
			if (empty($traitname)) {
				echo "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenome')."</td></tr>";
			}
			if (empty($traittipo)) {
				echo "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('nametipo')."</td></tr>";
			}
			if (empty($parenttraitid)) {
				echo "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('messagepertenceaclasse')."</td></tr>";
			}
			if (empty($traitdefinicao)) {
				echo "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namedefinicao')."</td></tr>";
			}
			echo " </table><br>";
			$erro++;
	} 
	if ($traittipo=='Variavel|Categoria' && empty($traitmulval)) {
			echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
			<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro15')."</td></tr>
			</table><br>
			";
			$erro++;
	}
	if ($traittipo=='Variavel|Quantitativo' && empty($traitunit) && empty($traitunitnew)) {
			echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
			<tr class='tdsmallbold' >
			<td align='center'>".GetLangVar('erro1')."</td>
			</tr>";
			if (empty($traitunit)) {
				echo "<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('nameunidade')."</td></tr>";
			}
			echo "</table><br>
			";
			$erro++;
	}

	//checar nomes duplicados
	$dupsnome = TraitNameCheck($traitname,$ttttipo,TRUE,$conn,$traitid,$parenttraitid);
	if (count($dupsnome)>0) {
			echo "<br><table cellpadding=\"1\" width='50%' align='center' class='tablethinborder'>
			<tr class='tdthinborder2'><td class='erro' align='center'>".GetLangVar('erro9')."</td></tr>
			";
			foreach ($dupsnome as $tid => $tn){
				echo "<tr><td class='tdformnotes'><i>$tn</i></td></tr>";
			}
			echo "</table><br>";
			$erro++;
	}
	
	//checar se nao tem variaveis semelhantes que pode ser a mesma
	if (!isset($namechecked)) {
		$wordsinname = explode(" ",$traitname);
		$ferro=0;
		foreach ($wordsinname as $key => $value) {
			$value = trim(strtolower($value));			
			if (strlen($value)>3) {
				$qq = "SELECT * FROM Traits WHERE LOWER(TraitName) LIKE '%".$value."%' AND TraitTipo='".$traittipo."'";				
				$rr = mysql_query($qq,$conn);
				$nr = mysql_numrows($rr);
				if ($nr>0) {
					$ferro++;
					$erro++;
				}
			}
		}
		if ($ferro>0) {
			echo "<br><table cellpadding=\"5\" align='center' class='myformtable'><thead>";
			echo "<tr ><td colspan=100% >".GetLangVar('erro13')."</td></tr>";
			echo "<tr class='subhead'>
					<td class='tdsmallbold'>".GetLangVar('nameclasse')."</td>
					<td class='tdsmallbold'>".GetLangVar('namenome')."</td>
					<td class='tdsmallbold'>".GetLangVar('namedefinicao')."</td>
				</tr></thead><tbody>";
					foreach ($wordsinname as $key => $value) {
							$value = trim(strtolower($value));
							if (strlen($value)>3) {
								//echo $traitid;
								$qq = "SELECT * FROM Traits WHERE LOWER(TraitName) LIKE '%".$value."%' AND TraitTipo='".$traittipo."'";
								$rr = mysql_query($qq,$conn);
								$nr = mysql_numrows($rr);
								if ($nr>0) {
									while ($row = mysql_fetch_assoc($rr)) {
										if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;}$bgi++;
										echo "<tr bgcolor = $bgcolor>
												<td class='tdformnotes'>".$row['PathName']."</td>
												<td class='tdformnotes'><b>".$row['TraitName']."</b></td>
												<td class='tdformnotes'>".$row['TraitDefinicao']."</td>
										</tr>";
								}
							}
					}
		}
		echo "<tr >
				<form action=traitsnew-popup.php method='post'>
					<input type='hidden' name='traittipo' value='$traittipo'>
					<input type='hidden' name='traitunit' value='$traitunit'>
					<input type='hidden' name='traitname' value='$traitname'>
					<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
					<input type='hidden' name='parenttraitid' value='$parenttraitid'>
					<input type='hidden' name='namechecked' value='1'>
					<input type='hidden' name='final' value='1'>
					<input type='hidden' name='traitmulval' value='".$traitmulval."'>
					<input type='hidden' name='traitname_val' value='$traitname_val'>
					<input type='hidden' name='traitid_val' value='$traitid_val'>
					<input type='hidden' name='fname' value='$fname'>
					<td colspan=2 align='center'>
					<input type='submit' value='".GetLangVar('nameconfirmar')."' class='bsubmit'>
					</td>
				</form>";

				echo "<form action=traitsnew-popup.php method='post'>
					<input type='hidden' name='traittipo' value='$traittipo'>
					<input type='hidden' name='traitunit' value='$traitunit'>
					<input type='hidden' name='traitname' value='$traitname'>
					<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
					<input type='hidden' name='parenttraitid' value='$parenttraitid'>
					<input type='hidden' name='traitmulval' value='$traitmulval'>
					<input type='hidden' name='tbname' value='$tbname'>
					<input type='hidden' name='traitname_val' value='$traitname_val'>
					<input type='hidden' name='traitid_val' value='$traitid_val'>
					<td colspan=2 align='center'>
					<input type='submit' value='".GetLangVar('namecorrigir')."' class='breset'>
					</td>
				</form>
				</tr>
				</tbody></table><br>";			
		}
	} 
	
	if ($erro==0) {
		//faz o cadastro propriamente dito
		$traitname = trim($traitname);
		$traitname = ucfirst(strtolower($traitname));
		
		if ($traittipo=='Variavel|Categoria') {
			$fieldsaskeyofvaluearray = array(
				'ParentID' => $parenttraitid,
				'TraitName' => $traitname,
				'TraitTipo' => $traittipo,
				'TraitDefinicao' => $traitdefinicao,
				'MultiSelect' => $traitmulval
				);
		} 
		if ($traittipo=='Variavel|Quantitativo') {
			if (empty($traitunit) && !empty($traitunitnew)) {
				$traitunit = $traitunitnew;
			}
			$fieldsaskeyofvaluearray = array(
				'ParentID' => $parenttraitid,
				'TraitName' => $traitname,
				'TraitTipo' => $traittipo,
				'TraitDefinicao' => $traitdefinicao,
				'TraitUnit' => $traitunit);
		}
		if ($traittipo=='Variavel|Texto') {
			$fieldsaskeyofvaluearray = array(
				'ParentID' => $parenttraitid,
				'TraitName' => $traitname,
				'TraitTipo' => $traittipo,
				'TraitDefinicao' => $traitdefinicao);
		}

		$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
		if (!$newtrait) {
					$erro++;
			} else {
				$dn = updatesingletraitpath($newtrait,$conn);
				if (!$dn) {
					$erro++;
				}
		}
		if ($erro==0) {
				$done = 'ok';
				$zz = explode("|",$traittipo);
				$txt = $traitname." (".$zz[1].")";
		 		echo "
				<form >
				<input type='hidden' id='ttid' value='$newtrait' >			
				<input type='hidden' id='txtid' value='$txt' >			

				<script language=\"JavaScript\">
				setTimeout(
					function() {
						passnewidandtxtoinputfield('ttid','txtid','".$traitid_val."','".$traitname_val."')
						}
						,0.0001);
				</script>
				</form>";	
		}
	}
}

//pergunta se é quantitativo ou categorico ou texto (apenas essas tres opcoes na importacao)
if ($erro==0 && !isset($done)) {
echo "
<br>
<form name='traitnewform' action=traitsnew-popup.php method='post'>";
//echo "<input type='hidden' name='traitfieldcol' value='$traitfieldcol'>";
//echo "<input type='hidden' name='tbname' value='$tbname'>";
//echo "<input type='hidden' name='statecol' value='$statecol'>";
echo "<input type='hidden' name='traitname_val' value='$traitname_val'>";
echo "<input type='hidden' name='traitid_val' value='$traitid_val'>";
echo "<input type='hidden' name='fname' value='$fname'>";


echo "<table class='myformtable' cellpadding=\"5\" align='center'>
<thead>";
if (isset($traittipo)) {
	$zz = explode("|",$traittipo);
	$tip = "(".$zz[1].")";
}
if (isset($fname)) {
	$tip .= " para a coluna ".$fname;

}
if (!isset($traitname)) {
	$traitname = $fname;
	
}
echo "<tr><td colspan=2>".GetLangVar('namecadastrar')." ".strtolower(GetLangVar('namevariavel'))." ".strtolower($tip)."</td></tr>
</thead>
<tbody>";

if (!isset($traittipo)) {
	echo "<input type='hidden' name='traitname' value='$traitname'>";

	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "<tr bgcolor = $bgcolor>	
	<td class='tdsmallbold'>".GetLangVar('nameselect')." ".strtolower(GetLangVar('nametipo'))."</td>
	<td><select name='traittipo' onchange='this.form.submit();'>
		<option value=''>".GetLangVar('nameselect')."</option>
		<option value='Variavel|Categoria' >Categórica</option>
		<option value='Variavel|Quantitativo' >Quantitativa</option>
		<option value='Variavel|Texto'>Texto</option>
	</select>
	</td>
	</tr>";
} else {

echo "<input type='hidden' name='traittipo' value='$traittipo'>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "<tr bgcolor = $bgcolor>		
			<td class='tdsmallbold' style='color:#990000'>".GetLangVar('namenome')."*&nbsp;
			<img src=\"icons/icon_question.gif\" ";
			$help = "Procure usar nomes cursos, mas que defina bem o significa a variável. Se possível uma única palavra. Variáveis podem ser utilizadas por outras pessoas para armazenar seus dados, o nome e a definição devem ser portantos bem pensados";
			echo	" onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;</td>
			<td class='tdsmallbold'><input type='text' size=50 name='traitname' value='$traitname'></td>
		</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "<tr bgcolor = $bgcolor>	
			<td class='tdsmallbold' style='color:#990000'>".GetLangVar('messagepertenceaclasse')." *&nbsp;
			<img src=\"icons/icon_question.gif\" ";
			$help = "Colocar a variável numa classe facilita a estruturação de de dados e descrições. Por exemplo, uma classe Folha pode conter todas as variáveis relacionadas a folhas";
			echo	" onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;
			</td>
			<td class='tdformleft'>
				<select name='parenttraitid'>";
						if (empty($parenttraitid)) {
							echo "<option selected value=''>".GetLangVar('nameselect')."</option>";
						} else {
							$qq = "SELECT * FROM Traits WHERE TraitID='$parenttraitid'";
							$rr = mysql_query($qq,$conn);
							$row = mysql_fetch_assoc($rr);
							echo "<option selected value=".$row['TraitID'].">".$row['TraitName']."</option>";
							echo "<option value=''>----</option>";
						}
						$filtro = "SELECT * FROM Traits WHERE TraitTipo='Classe' ORDER BY PathName,TraitName";
						$res = mysql_query($filtro,$conn);
						while ($aa = mysql_fetch_assoc($res)){
							$PathName = $aa['PathName'];
							$level = $aa['MenuLevel'];
							if ($level==1) {
								$espaco='';
								echo "<option class='optselectdowlight' value=".$aa['TraitID'].">".$aa['TraitName']."</option>";				
							} else {
								$espaco = str_repeat('&nbsp;&nbsp;&nbsp;',$level);
								$espaco = $espaco.str_repeat('-',$level-1);
								echo "<option value=".$aa['TraitID'].">$espaco".$aa['TraitName']."</option>";				
							}
						}
			echo "</select></td></tr>";	
			
			
if ($traittipo=='Variavel|Quantitativo') { //se for quantitativo
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "<tr bgcolor = $bgcolor>
			<td colspan='100%'>
				<table align='left' >
					<tr>
						<td class='tdsmallbold' style='color:#990000'>".GetLangVar('nameunidade')." *&nbsp;
							<img src=\"icons/icon_question.gif\" ";
							$help = strip_tags(GetLangVar('traitsunidadehelp'));
							echo	" onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;
						</td>";
						$qq = "SELECT unit FROM ((SELECT DISTINCT ".$lang." as unit FROM VarLang WHERE VariableName LIKE '%traitunit%') 
UNION (SELECT DISTINCT TraitUnit as unit FROM Traits WHERE TraitUnit IS NOT NULL AND TraitUnit!='') ) as mytab ORDER BY unit";
						$res = mysql_query($qq,$conn);
						$varn = array();
						while ($row=mysql_fetch_assoc($res)) {
							$varname = $row['unit'];
							echo "<td class='tdformnotes'>
								<input type='radio' name='traitunit' ";
									if ($traitunit==$varname) {echo " checked ";}
									echo "value='".$varname."'>&nbsp;".$varname."&nbsp;";		
							echo "</td>";
						}
						echo "<td class='tdformnotes'>
						<input class='tdformnotes' type='text' size=6 value='".$traitunitnew."' name='traitunitnew'>
						&nbsp;&nbsp;".strtolower(GetLangVar('namenova'))."&nbsp;&nbsp;</td>
				</tr></table>
			</td></tr>";
}

if ($traittipo=='Variavel|Categoria') { //se for categoria
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "<tr bgcolor = $bgcolor>
			<td class='tdsmallbold'  style='color:#990000'>".GetLangVar('messageallowmultival')." *&nbsp;
			<img src=\"icons/icon_question.gif\" ";
			$help = strip_tags(GetLangVar('messageallowmultival_help'));
			echo	" onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;
			</td>
			<td>
			<table align='left' >
				<tr>
			
			<td class='tdformnotes'>
			<input type='radio' name='traitmulval' ";
			if ($traitmulval=='Sim') { echo " checked ";}
			echo "value='Sim'>&nbsp;".GetLangVar('namesim')."</td>
			<td class='tdformnotes'><input type='radio' name='traitmulval' ";
			if ($traitmulval=='Nao') { echo " checked ";}
			echo "value='Nao'>&nbsp;".GetLangVar('namenao')."</td>
			</tr>
			</table>
			</td></tr>
			";
 } 

//trait definicao
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
	<td class='tdsmallbold'  style='color:#990000'>".GetLangVar('namedefinicao')."&nbsp;
			<img src=\"icons/icon_question.gif\" ";
			$help = "A definição de uma variável explica o que ela significa e aparece quanto o ? ao lado do nome da variável em formulários é clicado. Seja bem específico";
			echo	" onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;</td>
	<td class='tdsmallbold'>
		<textarea name='traitdefinicao' cols=40 rows=4 wrap=SOFT>$traitdefinicao</textarea>
	</td>
</td></tr>";	

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "<tr bgcolor = $bgcolor>
			<input type='hidden' name='final' value=''>
			<td align='center' colspan='100%'>
			<input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.traitnewform.final.value=1\">
			</td>
	</tr>";
} //end if traittipo isset

echo "</tbody></table></form>";

} 




PopupTrailers();

?>
