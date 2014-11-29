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

HTMLheaders('');



//print_r($_POST);
echo "<br>";
//transferencia de nova imagem para folder
if (!empty($_POST['newimage'])) {	
	$myfile = $_FILES['novaimg']['name'];
	if ($myfile) {
		$basename = explode(".",$_FILES['novaimg']['name']);
		$basename = $basename[0];
		list ($ftypename, $ftype) = explode("/",$_FILES['novaimg']['type']);
		$filedate = date("Y-m-d");			
		$myfile = $filedate."_".$basename.".".trim($ftype);
		move_uploaded_file($_FILES["novaimg"]["tmp_name"],"img/traits_icons/$myfile");
		$traiticone = trim($myfile);
	}
}


//process results
if (!empty($_POST['enviado'])) {
	if (empty($traittipo)) {$traittipo=$traitkind;}
	$erro=0;
	
	//mensagens de erro por dados incompletos
	if ($traitkind=='Classe') { //se classe
		//required	
		if (empty($traitname) || empty($traitdefinicao)) {
			echo "<br><table cellpadding=\"3\" width='50%' align='center' class='erro'>
			<tr><td colspan=1 ><b>".GetLangVar('erro1')."</b></td></tr>
			<tr><td>".GetLangVar('namedefinicao').",  ".GetLangVar('namenome')."</td></tr>
			</table><br>
			";
			$erro++;
		}
		if ($parenttraitid=='padrao') {unset($parenttraitid);}
	}
	$tt = explode("|",$traittipo);
	if ($tt[0]=='Variavel') { //se caractere
		if (empty($traitname) || empty($traitdefinicao) || empty($traittipo) || empty($parenttraitid)) {
			echo "<br><table cellpadding=\"3\" width='50%'  align='center' class='erro'>
			<tr><td><b>".GetLangVar('erro1')."</b></td></tr>
			<tr><td>".GetLangVar('namenome').",  ".GetLangVar('messagepertenceaclasse').", ".GetLangVar('nametipo').",  ".GetLangVar('namedefinicao')."</td></tr>
			</table><br>
			";
			$erro++;
		}
	}
	
//habilite abaixo para exigir escala da imagem
//	if (!empty($traittipo)) {
//				$qq = "SELECT * FROM VarLang WHERE $lang='".$_POST['traittipo']."'";
//				$res = mysql_query($qq,$conn);
//				$rr=mysql_fetch_assoc($res);
//				$quanti = $rr['VariableName'];
//				if ($quanti=='traittipo4' && empty($traitimgscale)) { //if trait é uma imagem e a escala esta faltando
//					echo "<br><table cellpadding=\"3\" width='50%'  align='center' class='erro'>
//					<tr><td><b>".GetLangVar('erro10')."</b></td></tr>
//					<tr><td>".GetLangVar('messageaumentode')."</td></tr>
//					</table><br>";
//					$erro++;
//				}
//	}
	if ($traitkind=='Estado') { //se estado		
		if (empty($traitname) || empty($traitdefinicao) || empty($parenttraitid)) {
			echo "<br><table cellpadding=\"3\" width='50%' align='center' class='erro'>
			<tr><td><b>".GetLangVar('erro1')."</b></td></tr>
			<tr><td>".GetLangVar('namenome').",  ".GetLangVar('messagepertenceaclasse').",  ".GetLangVar('namedefinicao')."</td></tr>
			</table><br>
			";
			$erro++;
		}
	}
	
	//checar se o nome nao foi duplicado
	$dupsnome = TraitNameCheck($traitname,$traittipo,TRUE,$conn,$traitid,$parenttraitid);
	if (count($dupsnome)>0) {
			echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
			<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro9')."</td></tr>
			";
			foreach ($dupsnome as $tid => $tn){
				echo "<tr><td class='tdformnotes'><i>$tn</i></td></tr>";
			}
			echo "</table><br>";
			$erro++;
	}
	
	$qq= "SELECT * FROM Traits WHERE TraitID='$parenttraitid'";
	$res = mysql_query($qq,$conn);
	$rr=mysql_fetch_assoc($res);
	$tipo = $rr['TraitTipo'];
	//se parentid for categoria e trait nao for estado nao permitir o cadastro
	if ($tipo=='Variavel|Categoria' && $traitkind!='Estado') { 
			echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
			<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro12')."</td></tr>
			</table><br>
			";
			$erro++;
	}
	
	//se for estado e parent nao for categoria, nao permitir
	if ($traitkind=='Estado' && $tipo!='Variavel|Categoria') { 
			echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
			<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro14')."</td></tr>
			</table><br>
			";
			$erro++;
	}
	//checar se um  registro semelhante ja nao existe e solicitar confirmacao do cadastro
	if (!isset($namechecked)) {
		$wordsinname = explode(" ",$traitname);
		$ferro=0;
		foreach ($wordsinname as $key => $value) {
			$value = trim($value);
			if (strlen($value)>3) {
				if (!empty($traitid)) {
					$qq = "SELECT * FROM Traits WHERE TraitName LIKE '%$value%' AND TraitTipo='$traittipo' AND TraitID!='$traitid'";
				} else {
					$qq = "SELECT * FROM Traits WHERE TraitName LIKE '%$value%' AND TraitTipo='$traittipo'";				
				}
				$rr = mysql_query($qq,$conn);
				$nr = mysql_numrows($rr);
				if ($nr>0) {
					$ferro++;
					$erro++;
				}
			}
		}
		if ($ferro>0) {
			echo "<br><table cellpadding=\"3\" width='60%' align='center' class='erro'>";
			echo "<tr><td colspan=2 class='tdsmallbold'>".GetLangVar('erro13')."</td></tr>";
			echo "<tr class='tdformnotes'><td>".GetLangVar('namenome')."</td><td>".GetLangVar('namedefinicao')."</td></tr>";
			foreach ($wordsinname as $key => $value) {
				$value = trim($value);
				if (strlen($value)>3) {
					if (!empty($traitid)) {
						$qq = "SELECT * FROM Traits WHERE TraitName LIKE '%$value%' AND TraitID!='$traitid'";
					} else {
						$qq = "SELECT * FROM Traits WHERE TraitName LIKE '%$value%'";				
					}
					$rr = mysql_query($qq,$conn);
					$nr = mysql_numrows($rr);
					if ($nr>0) {
						while ($row = mysql_fetch_assoc($rr)) {
							echo "<tr><td class='tdformnotes'><i>".$row['TraitName']."</td><td>".$row['TraitDefinicao']."</i></td></tr>";
						}
					}
				}
			}
		echo "<tr><td colspan=2 align='center'>
				<form action=traits-exec.php method='post'>
					<input type='hidden' name='traitkind' value='$traitkind'>
					<input type='hidden' name='traittipo' value='$traittipo'>
					<input type='hidden' name='traitunit' value='$traitunit'>
					<input type='hidden' name='traitname' value='$traitname'>
					<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
					<input type='hidden' name='parenttraitid' value='$parenttraitid'>
					<input type='hidden' name='traiticone' value='$traiticone'>
					<input type = 'hidden' name='enviado' value='1'>
					<input type='hidden' name='traitunit' value='$traitunit'>
					<input type='hidden' name='parentform' value='$parentform'>
					<input type='hidden' name='traitimgscale' value='$traitimgscale'>	
					<input type='hidden' name='namechecked' value='1'>
					<input type='hidden' name='traitid' value='$traitid'>
					<input type='hidden' name='traitmulval' value='$traitmulval'>

					<input type='submit' value='".GetLangVar('nameconfirmar')."'>
				</form>
				</td>
				<td colspan=2 align='center'>
				<form action=traits-exec.php method='post'>
					<input type='hidden' name='traitkind' value='$traitkind'>
					<input type='hidden' name='traittipo' value='$traittipo'>
					<input type='hidden' name='traitunit' value='$traitunit'>
					<input type='hidden' name='traitname' value='$traitname'>
					<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
					<input type='hidden' name='parenttraitid' value='$parenttraitid'>
					<input type='hidden' name='traiticone' value='$traiticone'>
					<input type='hidden' name='traitunit' value='$traitunit'>
					<input type='hidden' name='parentform' value='$parentform'>
					<input type='hidden' name='traitimgscale' value='$traitimgscale'>	
					<input type='hidden' name='traitid' value='$traitid'>
					<input type='hidden' name='traitmulval' value='$traitmulval'>

					<input type='submit' value='".GetLangVar('namecorrigir')."'>
				</form>
				</td>
				
				</tr></table><br>";			
		}
	} //end if name is checked!
	
	if ($traittipo=='Variavel|Categoria' && empty($traitmulval)) {
			echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
			<tr><td class='tdsmallbold' align='center'>".GetLangVar('erro15')."</td></tr>
			</table><br>
			";
			$erro++;
	}
	
//proceder para cadastro
if ($erro==0) {
	//if ($traitkind=='Variavel') {$traittipo=$traitkind."|".$traittipo;}
	if ($traittipo=='Variavel|Categoria') {
		$fieldsaskeyofvaluearray = array(
			'ParentID' => $parenttraitid,
			'TraitName' => $traitname,
			'TraitTipo' => $traittipo,
			'TraitDefinicao' => $traitdefinicao,
			'TraitUnit' => $traitunit,
			'TraitIcone' => $traiticone,
			'MultiSelect' => $traitmulval
			);
	} else {
		$fieldsaskeyofvaluearray = array(
			'ParentID' => $parenttraitid,
			'TraitName' => $traitname,
			'TraitTipo' => $traittipo,
			'TraitDefinicao' => $traitdefinicao,
			'TraitUnit' => $traitunit,
			'TraitIcone' => $traiticone
			);
	}
	//se editando
	if (!empty($traitid) && $traitid!=GetLangVar('nameselect')) {
			CreateorUpdateTableofChanges($traitid,'TraitID','Traits',$conn);
			$newtrait = UpdateTable($traitid,$fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
			if (!$newtrait) {
				$erro++;
			}
			
	//se criando		
	} else {
		$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
	}
	//confirmacao de cadastro ou erro
	if ($newtrait) {
		echo "<br><table align='center' class='success' cellpadding=\"5\">
				<tr><td>".GetLangVar('sucesso1')."</td></tr>
				</table>
			<br>";
	} else {
		echo "<br><table align='center' class='erro' cellpadding=\"5\">
				<tr><td>".GetLangVar('erro2')."</td></tr>
				</table>
			<br>";
	}
} //end if erro=0

} //end if post enviado  TERMINOU O CADASTRO DE FATO

//SE EDITANDO, COLETA DADOS ARMAZENADOS
if (!empty($traitid) && $traitid!=GetLangVar('nameselect') && $formsubmitted=='submitted') {
	$qq = "SELECT * FROM Traits WHERE TraitID='$traitid'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$parenttraitid = $row['ParentID'];
	$traitname = $row['TraitName'];
	$traittipo = $row['TraitTipo'];
	$traitdefinicao = $row['TraitDefinicao'];
	$traitunit = $row['TraitUnit'];
	$traiticone = $row['TraitIcone'];
	$traitmulval = $row['MultiSelect'];
	if ($traittipo=='Classe') {
		$traitkind = 'Classe';
	} else {
		if ($traittipo=='Estado') {
			$traitkind = 'Estado';
		} else {
			$tt = explode("|",$traittipo);
			$traitkind = $tt[0];
		}
	}
	if(($traitname=='Habitat' || $traitname=='LocalidadeTipo') && $traittipo=='Classe') {
			echo "
				<table class='erro' align='center'>
				<tr class='tdsmallbold'><td>".GetLangVar('erro15')."</td></tr><tr>
				<td align='center'>
				<form action=traits-form.php method='post'>
					<input type='submit' class='bsubmit' value='".GetLangVar('namevoltar')."'>
				</form>
				</td>
				</tr></table><br>";
			exit;
	} 	
}

if (!empty($traitid) && $traitid!=GetLangVar('nameselect')) {	
	echo "<table width='80%' class='tableform' cellpadding=\"3\" align='center'>";
	echo "<tr class='tabhead'><td colspan=2>".GetLangVar('nameeditar')." ".strtolower($traitkind);
	if ($traitkind!=$traittipo) { echo " (".strtolower($traittipo).")";}
	echo "</td></tr>";
} else {
	echo "<table width='80%' class='tableform' cellpadding=\"3\" align='center'>";
	echo "<tr class='tabhead'><td colspan=2>".GetLangVar('namecadastrar')." ".strtolower($traitkind)."</td></tr>";
}

//COMECA O FORMULARIO 
echo "<form action=traits-exec.php method='post'>
		<input type='hidden' name='traitkind' value='$traitkind'>
		<input type='hidden' name='traittipo' value='$traittipo'>
		<input type='hidden' name='traitunit' value='$traitunit'>
		<input type='hidden' name='traitid' value='$traitid'>
		<input type='hidden' name='parenttraitid' value='$parenttraitid'>
		<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
		<input type='hidden' name='traiticone' value='$traiticone'>
		<input type='hidden' name='traitimgscale' value='$traitimgscale'>
		<input type='hidden' name='parentform' value='$parentform'>
		<input type='hidden' name='traitmulval' value='$traitmulval'>

<tr><td><table align='left' >
<tr>
	<td class='tdsmallbold'>".GetLangVar('namenome')."</td>
	<td class='tdsmallbold'><input type='text' size=50 name='traitname' value='$traitname' onchange='this.form.submit();'></td>
</tr>
</table></td></tr>
</form>";

echo "
<form action=traits-exec.php method='post'>
		<input type='hidden' name='traitkind' value='$traitkind'>
		<input type='hidden' name='traitname' value='$traitname'>
		<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
		<input type='hidden' name='parenttraitid' value='$parenttraitid'>
		<input type='hidden' name='traiticone' value='$traiticone'>
		<input type='hidden' name='traitunit' value='$traitunit'>
		<input type='hidden' name='traittipo' value='$traittipo'>
		<input type='hidden' name='traitimgscale' value='$traitimgscale'>
		<input type='hidden' name='parentform' value='$parentform'>
		<input type='hidden' name='traitid' value='$traitid'>
		<input type='hidden' name='traitmulval' value='$traitmulval'>

<tr><td><table align='left'>
		<tr >";
		
		
//a quem pertence
if ($traitkind=='Classe') {
		echo "<td class='tdsmallbold'>".GetLangVar('messagepertenceasubclasse')."</td>";
}
if ($traitkind=='Variavel') {
		echo "<td class='tdsmallbold'>".GetLangVar('messagepertenceaclasse')."</td>";
}
if ($traitkind=='Estado') {
		echo "<td class='tdsmallbold'>".GetLangVar('messagepertenceaocaractere')."</td>";
}
echo "
		<td class='tdformleft'>
		<select name='parenttraitid' onchange='this.form.submit();'>";
			if ($parenttraitid=='padrao') {
				echo "<option  selected value='padrao'>".GetLangVar('messagenenhumgrupo')."</option>";
			} elseif ($traitkind=='Classe') {
					echo "<option value='padrao'>".GetLangVar('messagenenhumgrupo')."</option>";
			} 
			if (empty($parenttraitid)) {
				echo "<option selected>".GetLangVar('nameselect')."</option>";
			} elseif ($parenttraitid!='padrao') {
				$qq = "SELECT * FROM Traits WHERE TraitID='$parenttraitid'";
				$rr = mysql_query($qq,$conn);
				$row = mysql_fetch_assoc($rr);
				echo "<option selected value=".$row['TraitID'].">".$row['TraitName']."</option>";
				echo "<option value='padrao'>".GetLangVar('messagenaopertence')."</option>";
			}
			echo "<option>----</option>";
			if ($traitkind=='Classe') {
					$filtro = "SELECT * FROM Traits WHERE TraitTipo='Classe'";			
			} 
			if ($traitkind=='Variavel') {
					$filtro = "SELECT * FROM Traits WHERE TraitTipo='Classe'";			
			} 
			if ($traitkind=='Estado') {
					$filtro = "SELECT * FROM Traits WHERE TraitTipo='Variavel|Categoria' OR TraitTipo='Classe'";			
			} 
			$res = listtraits($filtro,$conn);
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
echo "</select>
	</td>
	</tr>
</table></td></tr>
</form>
";		

if (empty($traitid) || $traitid==GetLangVar('nameselect')) { //start if editing tipo change option not allowed
if ($traitkind=='Variavel') {
//tipo do novo caractere
	echo "
	<form action=traits-exec.php method='post'>
		<input type='hidden' name='traitkind' value='$traitkind'>
		<input type='hidden' name='traitname' value='$traitname'>
		<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
		<input type='hidden' name='parenttraitid' value='$parenttraitid'>
		<input type='hidden' name='traiticone' value='$traiticone'>
		<input type='hidden' name='parentform' value='$parentform'>
		<input type='hidden' name='traitid' value='$traitid'>

	<tr><td><table align='left' >
	<tr>
	<td class='tdsmallbold'>".GetLangVar('nametipo')."</td>";
	echo "<td class='tdformnotes'>
		<input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Categoria') {echo " checked ";}
				echo " value='Variavel|Categoria' onchange='this.form.submit();' >&nbsp;".GetLangVar('traittipo1')."&nbsp;		
				<img src=\"icons/icon_question.gif\" ";
				$help = "traittipo1_desc";
				$myurl ="explainpopup.php?explanation=$help";		
				echo	" onclick=\"javascript:small_window('$myurl',400,120,'Ajuda_Termo');\">
				</td>";
	echo "<td class='tdformnotes'>
		<input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Quantitativo') {echo " checked ";}
				echo " value='Variavel|Quantitativo' onchange='this.form.submit();' >&nbsp;".GetLangVar('traittipo2')."&nbsp;		
				<img src=\"icons/icon_question.gif\" ";
				$help = "traittipo2_desc";
				$myurl ="explainpopup.php?explanation=$help";		
				echo	" onclick=\"javascript:small_window('$myurl',400,120,'Ajuda_Termo');\">
				</td>";

		echo "<td class='tdformnotes'>
				<input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|SemiQuantitativo') {echo " checked ";}
				echo " value='Variavel|SemiQuantitativo' onchange='this.form.submit();' >&nbsp;".GetLangVar('traittipo3')."&nbsp;		
				<img src=\"icons/icon_question.gif\" ";
				$help = "traittipo3_desc";
				$myurl ="explainpopup.php?explanation=$help";		
				echo	" onclick=\"javascript:small_window('$myurl',400,120,'Ajuda_Termo');\">
				</td>";
		echo "<td class='tdformnotes'>
				<input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Imagem') {echo " checked ";}
				echo " value='Variavel|Imagem' onchange='this.form.submit();' >&nbsp;".GetLangVar('traittipo4')."&nbsp;		
				<img src=\"icons/icon_question.gif\" ";
				$help = "traittipo3_desc";
				$myurl ="explainpopup.php?explanation=$help";		
				echo	" onclick=\"javascript:small_window('$myurl',400,120,'Ajuda_Termo');\">
				</td>";
		echo "<td class='tdformnotes'>
				<input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Texto') {echo " checked ";}
				echo " value='Variavel|Texto' onchange='this.form.submit();' >&nbsp;".GetLangVar('traittipo5')."&nbsp;		
				<img src=\"icons/icon_question.gif\" ";
				$help = "traittipo5_desc";
				$myurl ="explainpopup.php?explanation=$help";		
				echo	" onclick=\"javascript:small_window('$myurl',400,120,'Ajuda_Termo');\">
				</td>";


	echo "</tr>
	</table></td></tr></form>";

}

} //end if editing tipo change option not allowed

if (isset($traittipo)) {
	if ($traittipo=='Variavel|Quantitativo') { //se for quantitativo
		echo "	<form action=traits-exec.php method='post'>
				<input type='hidden' name='traitkind' value='$traitkind'>
				<input type='hidden' name='traittipo' value='$traittipo'>
				<input type='hidden' name='traitname' value='$traitname'>
				<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
				<input type='hidden' name='parenttraitid' value='$parenttraitid'>
				<input type='hidden' name='traiticone' value='$traiticone'>
				<input type='hidden' name='parentform' value='$parentform'>
				<input type='hidden' name='traitid' value='$traitid'>
				<tr><td><table align='left' >
				<tr>
				<td class='tdsmallbold'>".GetLangVar('nameunidade')."&nbsp;
				<img src=\"icons/icon_question.gif\" ";
				$help = "traitsunidadehelp";
				$myurl ="explainpopup.php?explanation=$help";		
				echo	" onclick=\"javascript:small_window('$myurl',400,120,'Ajuda_Termo');\">&nbsp;&nbsp;
				</td>";
				$qq = "SELECT * FROM VarLang WHERE VariableName LIKE '%traitunit%' ORDER BY '$lang' ASC";
				$res = mysql_query($qq,$conn);
				if ($res) {
					while ($row=mysql_fetch_assoc($res)) {
						$varname = $row['VariableName'];
						$zz = explode("_",$varname);
						if ($zz[1]!='desc') {
							$subsname = 'traitunit'.$menugrp;
							echo "<td class='tdformnotes'>
							<input type='radio' name='traitunit' ";
							if ($traitunit==GetLangVar($varname)) {echo " checked ";}
							echo "value='".GetLangVar($varname)."' onchange='this.form.submit();'>&nbsp;".GetLangVar($varname)."&nbsp;";		
							//<img src=\"icons/icon_question.gif\" ";
							//$help = $varname."_desc";
							//$myurl ="explainpopup.php?explanation=$help";		
							//echo	" onclick=\"javascript:small_window('$myurl',400,120,'Ajuda_Termo');\">
							echo "</td>";
						}
					}
				}
			echo "</tr></table></td></tr></form>";
	 }
	 if ($traittipo=='Variavel|Imagem') { //se for uma imagem
		echo "	<form action=traits-exec.php method='post'>
				<input type='hidden' name='traitkind' value='$traitkind'>
				<input type='hidden' name='traittipo' value='$traittipo'>
				<input type='hidden' name='traitname' value='$traitname'>
				<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
				<input type='hidden' name='parenttraitid' value='$parenttraitid'>
				<input type='hidden' name='traiticone' value='$traiticone'>
				<input type='hidden' name='parentform' value='$parentform'>
				<input type='hidden' name='traitid' value='$traitid'>
				<tr><td>
				<table align='left' >
				<tr>
				<td class='tdsmallbold'>".GetLangVar('messageaumentode')."</td>
				<td><input type='text' name='traitimgscale' value='$traitimgscale' onchange='this.form.submit();'>&nbsp;X</td>
				</tr>
				</table>
				</td></tr>
				</form>";
	 } 
	  if ($traittipo=='Variavel|Categoria') { //se for categoria
		echo "	<form action=traits-exec.php method='post'>
				<input type='hidden' name='traitkind' value='$traitkind'>
				<input type='hidden' name='traittipo' value='$traittipo'>
				<input type='hidden' name='traitname' value='$traitname'>
				<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
				<input type='hidden' name='parenttraitid' value='$parenttraitid'>
				<input type='hidden' name='traiticone' value='$traiticone'>
				<input type='hidden' name='parentform' value='$parentform'>
				<input type='hidden' name='traitid' value='$traitid'>
				<input type='hidden' name='traitimgscale' value='$traitimgscale'>

				<tr><td>
				<table align='left' >
				<tr>
				<td class='tdsmallbold'>".GetLangVar('messageallowmultival')."&nbsp;
				<img src=\"icons/icon_question.gif\" ";
				$help = "messageallowmultival_help";
				$myurl ="explainpopup.php?explanation=$help";		
				echo	" onclick=\"javascript:small_window('$myurl',400,120,'Ajuda_Termo');\">&nbsp;&nbsp;
				</td>
				<td class='tdformnotes'>
				<input type='radio' name='traitmulval' ";
				if ($traitmulval=='Sim') { echo " checked ";}
				echo "value='Sim' onchange='this.form.submit();'>&nbsp;".GetLangVar('namesim')."</td>
				<td class='tdformnotes'><input type='radio' name='traitmulval' ";
				if ($traitmulval=='Nao') { echo " checked ";}
				echo "value='Nao' onchange='this.form.submit();'>&nbsp;".GetLangVar('namenao')."</td>
				</tr>
				</table>
				</td></tr>
				</form>";
	 } 
}


//trait definicao
echo "<form action=traits-exec.php method='post'>
				<input type='hidden' name='traitkind' value='$traitkind'>
				<input type='hidden' name='traittipo' value='$traittipo'>
				<input type='hidden' name='traitunit' value='$traitunit'>
				<input type='hidden' name='traitname' value='$traitname'>
				<input type='hidden' name='parenttraitid' value='$parenttraitid'>
				<input type='hidden' name='traiticone' value='$traiticone'>
				<input type='hidden' name='traitunit' value='$traitunit'>
				<input type='hidden' name='traitimgscale' value='$traitimgscale'>
				<input type='hidden' name='parentform' value='$parentform'>
				<input type='hidden' name='traitid' value='$traitid'>
				<input type='hidden' name='traitmulval' value='$traitmulval'>

<tr><td colspan=100%>ID = $traitid</td></tr>
<tr><td><table align='left' >
<tr>
	<td class='tdsmallbold'>".GetLangVar('namedefinicao')."</td>
	<td class='tdsmallbold'>
		<textarea name='traitdefinicao' cols=40 rows=4 wrap=SOFT onchange='this.form.submit();'>$traitdefinicao</textarea>
	</td>
</tr></table></td></tr></form>";	

//trait icone

echo "<form enctype='multipart/form-data' action=traits-exec.php method='post'>
				<input type='hidden' name='traitkind' value='$traitkind'>
				<input type='hidden' name='traittipo' value='$traittipo'>
				<input type='hidden' name='traitunit' value='$traitunit'>
				<input type='hidden' name='traitname' value='$traitname'>
				<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
				<input type='hidden' name='parenttraitid' value='$parenttraitid'>
				<input type='hidden' name='traitunit' value='$traitunit'>
				<input type='hidden' name='traitimgscale' value='$traitimgscale'>
				<input type='hidden' name='parentform' value='$parentform'>
					<input type='hidden' name='traitid' value='$traitid'>
					<input type='hidden' name='traitmulval' value='$traitmulval'>

<tr><td><table align='left' >
<tr>
<td class='tdsmallbold'>".GetLangVar('messagetraiticon')."</td>";
				if (!empty($traiticone)) {
			    	echo "<td align='right'>
			    	<img src='img/traits_icons/$traiticone' height=100 border=0></td>";
					$fileinfo = getimagesize("img/traits_icons/".$traiticone);
					//echo "<table border=1 cellspacing=\"5\" align='center'><tr>";
		    		$filename = "<b>".GetLangVar('namefile')."</b>: <i>$traiticone</i> <br>";
		    		$filename = $filename." "."<b>".GetLangVar('namesize')."</b>: ".@round(filesize("img/traits_icons/".
		    		$traiticone))." bytes (".$fileinfo[0]."x".$fileinfo[1].")<br>";
		    		$filename = "<td class='tdformnotes' align='left'>".$filename."<b>".GetLangVar('namefolder')."</b> img/traits_icons/</td></tr>";
					echo $filename;	
				}
		echo "
		<input type='hidden' name='traiticone' value='$traiticone'>
		<input type='hidden' name='newimage' value='1'>
		<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
		<td>&nbsp;</td><td colspan=2 ><input type='file' size=20 name='novaimg' onchange='this.form.submit();'></td>";
echo	"
</tr>
</table>
</td></tr>
</form>";

echo "<tr><td><table align='center'><tr>
<form action=traits-exec.php method='post'>
		<input type='hidden' name='traitkind' value='$traitkind'>
		<input type='hidden' name='traittipo' value='$traittipo'>
		<input type='hidden' name='traitunit' value='$traitunit'>
		<input type='hidden' name='traitname' value='$traitname'>
		<input type='hidden' name='traitdefinicao' value='$traitdefinicao'>
		<input type='hidden' name='parenttraitid' value='$parenttraitid'>
		<input type='hidden' name='traiticone' value='$traiticone'>
		<input type = 'hidden' name='enviado' value='1'>
		<input type='hidden' name='traitunit' value='$traitunit'>
		<input type='hidden' name='parentform' value='$parentform'>
		<input type='hidden' name='traitimgscale' value='$traitimgscale'>
		<input type='hidden' name='traitid' value='$traitid'>
				<input type='hidden' name='traitmulval' value='$traitmulval'>

	<td align='center'><input type = 'submit'  class='bsubmit' value=".GetLangVar('namecadastrar')."></td>
</form>
<form action=traits-form.php method='post'>
		<td align='center'><input type = 'submit'  class='breset' value=".GetLangVar('namereset')."></td> 
</form> 
</tr></td></tr></table>

</td></tr></table>
";

echo "</td></tr></table>";

HTMLtrailers();

?>
