<?php
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

//CABECALHO
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Formulários Organizar e Atualizar Variáveis';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echopre($ppost);

if (!empty($formid) && is_numeric($formid) && !isset($final) && !isset($orderchange)) {
	$qq = "SELECT * FROM Formularios WHERE FormID='".$formid."'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$traitids = explode(";",$row['FormFieldsIDS']);
} else {
	$traitids = unserialize($runtraitids);
}
if (empty($final) && $orderchange==1) {
	$arvals = $ppost;
	unset($arvals['orderchange'],$arvals['formid'],$arvals['final']);
	//echopre($arvals);
	$newtraitids = array();
	//MUDA A ORDEM
	//QUEM MUDA?
	$olid = 0;
	$orgorder=1;
	unset($neworder);
	foreach($arvals as $kk => $vv) {
		$zz = explode("_",$kk);
		$tid = $zz[1]+0;
		if ($zz[0]=='traitorder') {
			if ($vv!=$orgorder) {
				$neworder= $vv-1;
				$traichanging = $tid;
			}
			$orgorder++;
		}
	}
	if (isset($neworder)) {
		$aa = array_search($traichanging,$traitids);
		$tridd = $traitids;
		unset($tridd[$aa]);
		$tridd = array_values($tridd);
		$newtraitids = array();
		if ($neworder==0) {
			$newtraitids = array_merge((array)array($traichanging),(array)$tridd);
		} else {
			$nt = count($tridd);
			$idx=0;
			for($tt=0;$tt<=$nt;$tt++) {
				$curtr = $traitids[$idx];
				if ($curtr==$traichanging) {
					$idx = $idx+1;
					$curtr = $traitids[$idx];
				}
				if ($tt==$neworder) {
					$curtr = $traichanging;
				} else {
					$idx++;
				}
				$newtraitids[$tt] = $curtr;
			}
		}
		$newtraitids = array_values($newtraitids);

//		$newtraitids[$neworder] = $traichanging;
//		$nt=0;
//		foreach ($traitids as $cid) {
//			if ($cid!=$traichanging) {
//				if ($nt==$neworder) { $nt++;}
//				$newtraitids[$nt] = $cid;
//				$nt++;
//			}
//		}
//		ksort($newtraitids);
		$traitids = $newtraitids;
	}	
}
if (empty($final)) {
$runtraitids = serialize($traitids);
echo "
<br />
<form action=formularios-batchexec.php name='formulario' method='post'>
  <input type='hidden' name='ispopup' value='$ispopup' />
  <input type='hidden' value='1' name='orderchange' />
  <input type='hidden' value='".$formid."' name='formid' />
  <input type='hidden' value='".$runtraitids."' name='runtraitids' />
<table align='center' cellpadding='5' class='myformtable' width='70%' />
<thead>
<tr>
  <td>".GetLangVar('nameclasse')."</td>
  <td>".GetLangVar('namevariavel')."</td>
  <td>".GetLangVar('namedefinicao')."</td>
<!---  <td>NameEnglish</td>
  <td>Definition</td>
--->  
  <td>".GetLangVar('nameorder')."</td>
</tr>
</thead>
<tbody>";
$nt = count($traitids);
$i=1;
foreach($traitids as $tid) {
	$tid = $tid+0;
	$qq = "SELECT * FROM Traits WHERE TraitID='".$tid."'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$oldparentid = $row['ParentID'];
	$tname = $row['TraitName'];
	$definicao = $row['TraitDefinicao'];
	$tnameeng = $row['TraitName_English'];
	$definicaoeng = $row['TraitDefinicao_English'];
	if ($arvals["traitdefinicao_".$tid]!=$definicao && !empty($arvals["traitdefinicao_".$tid])) {
		$definicao = $arvals["traitdefinicao_".$tid];
	}
	
	if ($arvals["parentid_".$tid]>0) {
		$oldparentid = $arvals["parentid_".$tid];
	}
	if ($arvals["traitname_".$tid]!=$tname && !empty($arvals["traitname_".$tid])) {
		$tname = $arvals["traitname_".$tid];
	}

	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++; 
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>
    <select name=\"parentid_".$tid."\">";
			if (empty($oldparentid)) {
				echo "
      <option value=''>".GetLangVar('nameselect')."</option>";
			} else {
				$qq = "SELECT * FROM Traits WHERE TraitID='$oldparentid'";
				$rr = mysql_query($qq,$conn);
				$rw = mysql_fetch_assoc($rr);
				echo "
      <option selected value='".$rw['TraitID']."'>".$rw['TraitName']."</option>";
			}
			echo "
      <option value=''>----</option>";
			$filtro ="SELECT * FROM Traits WHERE TraitTipo='Classe' ORDER BY PathName,TraitName";
			$res = mysql_query($filtro,$conn);
			while ($aa = mysql_fetch_assoc($res)){
				$PathName = $aa['PathName'];
				$level = $aa['MenuLevel'];
				if ($level==1) {
					$espaco='';
				} else {
						$espaco = str_repeat('&nbsp;&nbsp;&nbsp;',$level);
				}
				echo "
      <option class='optselectdowlight' value='".$aa['TraitID']."'>$espaco<i>".$aa['TraitName']."</i></option>";
			}
echo "
    </select>
  </td>
  <td><input type='text' size='40' name=\"traitname_".$tid."\" value='".$tname."' readonly=T/></td>
  <td><textarea name=\"traitdefinicao_".$tid."\" cols='40' rows='3' readonly=T>$definicao</textarea></td>
<!---  <td><input type='text' size='40' name=\"traitnameeng_".$tid."\" value='".$tnameeng."' /></td>
  <td><textarea name=\"traitdefinicaoeng_".$tid."\" cols='40' rows='3'>$definicaoeng</textarea></td>
  --->
  <td>
    <select name=\"traitorder_".$tid."\" onchange='this.form.submit();'>
      <option selected value='".$i."'>$i</option>";
				for ($n=1;$n<=$nt;$n++) {
					echo "
      <option value='".$n."'>$n</option>";
				}
		echo "
    </select>
  </td>
</tr>";
	$i++;
}
echo "
<tr>
  <td colspan='6' align='center'>
    <input type='hidden' name='final' value='' />
    <input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.formulario.final.value=1\" />
  </td>
</tr>
</tbody>
</table>
</form>
";
} 
else { //processa
	$arvals = $ppost;
	unset($arvals['orderchange'],$arvals['formid'],$arvals['final']); 
	$newt = array();
	foreach($arvals as $kk => $vv) {
		$zz = explode("_",$kk);
		$tid = $zz[1]+0;
		if ($zz[0]=='traitorder') {
			$newt[] = $tid;
		}
	}
	//atualiza variaveis
	$identical=0;
	$updated=0;
	$erro=0;
///NAO ATUALIZA VARIAVEIS POR ESSE CAMINHO	
if ($lixo=10000) {
	foreach($newt as $tid) {
		$newparentid = $arvals["parentid_".$tid];
		$newtname = $arvals["traitname_".$tid];
		$newdefinicao = $arvals["traitdefinicao_".$tid];
		$newdefinicaoeng = $arvals["traitdefinicaoeng_".$tid];
		$newnameeng = $arvals["traitnameeng_".$tid];

		$arrayofvalues = array(
			'ParentID' => $newparentid,
			'TraitName' => $newtname,
			'TraitDefinicao' => $newdefinicao,
			'TraitName_English' => $newnameeng,
			'TraitDefinicao_English' => $newdefinicaoeng);
		$upp = CompareOldWithNewValues('Traits','TraitID',$tid,$arrayofvalues,$conn);
		if (!empty($upp) && $upp>0) { //if new values differ from old, then update
			CreateorUpdateTableofChanges($tid,'TraitID','Traits',$conn);
			$updatetrait = UpdateTable($tid,$arrayofvalues,'TraitID','Traits',$conn);
			if (!$updatetrait) {
					$erro++;
			} else {
				$updated++;
			}
		} else {
			$identical++;
 		}
	}
	if ($updated>0 || $identical>0) {
			if ($updated>0) {
				listtraits('',$conn);
			}
		echo "
<table class='success' width='50%' align='center'>
  <tr><td>$updated variáveis atualizadas</td></tr>
  <tr><td>$identical variáveis estavam identicas</td></tr>
</table>";
	}
	if ($erro>0) {
		echo "
<br />
<table class='erro' width='50%' align='center'>
  <tr><td>$erro variáveis modificadas NAO foi possivel atualizar</td></tr>
</table>";
	}
}	
	
	//update formulario
	$formtraits = implode(";",$newt);
	$traitarr = $newt;
	$arrayofvalues = array(
		'FormFieldsIDS' => $formtraits);
	$ufp = CompareOldWithNewValues('Formularios','FormID',$formid,$arrayofvalues,$conn);
	if (!empty($ufp) && $ufp>0) { 
		CreateorUpdateTableofChanges($formid,'FormID','Formularios',$conn);
		$updateform = UpdateTable($formid,$arrayofvalues,'FormID','Formularios',$conn);
		if ($updateform) {
				$formnome = "formid_".$formid;
				$qq = "UPDATE Traits SET FormulariosIDS=removeformularioidfromtraits(FormulariosIDS,'".$formnome."') WHERE FormulariosIDS LIKE '%formid_".$formid."' OR FormulariosIDS LIKE '%formid_".$formid.";%'";
				$nr = mysql_query($qq,$conn);
				if ($nr) {
					$updated=0;
					foreach ($traitarr as $value) {
							$sql = "UPDATE Traits SET Traits.FormulariosIDS=IF(Traits.FormulariosIDS<>'',CONCAT(Traits.FormulariosIDS,';','".$formnome."'),'".$formnome."') WHERE Traits.TraitID=".$value."";
							$upsql = mysql_query($sql,$conn);
					}
				}
		$qn = "DELETE FROM FormulariosTraitsList WHERE FormID='".$formid."'";
		mysql_query($qn,$conn);
		$trarr = explode(";",$formtraits);
		$nz = count($trarr);
		$count = 0;
		foreach ($trarr as $tri) {
			$tri = $tri+0;
			if ($tri>0) {
			$qz = "INSERT INTO FormulariosTraitsList (FormID,TraitID,Ordem) SELECT ".$formid.",TraitID,".$count." FROM Traits WHERE TraitID='".$tri."'";
			$rr = mysql_query($qz,$conn);
			if ($rr) {
				$count++;
			}
			}
		}
				echo "
<br />
<table class='success' width='50%' align='center'>
  <tr><td>Formulario Atualizado</td></tr>
</table>";
		}
	}
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
