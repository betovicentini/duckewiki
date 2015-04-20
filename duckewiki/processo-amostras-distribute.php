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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
//"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
"<script type='text/javascript'>
function getandplaceval(openerid,placeid){
var valor =  window.opener.document.getElementById(openerid).value;
var element = document.getElementById(placeid);
element.innerHTML = valor;
}
</script>"
);
$title = 'Distribuindo amostras físicas à herbários';
if (!isset($final)) {
$body = "onload=getandplaceval('herbariumlista','herbariaplaceid') ";
} else {
	$body = '';
}
FazHeader($title,$body,$which_css,$which_java,$menu);
//echopre($ppost);
if (!isset($final)) {
	$tt = "Distribuindo amostras do processo ";
	$qq = "SELECT pcs.*,LastName,FirstName FROM ProcessosEspecs as pcs JOIN Users as us ON UserID=CreatedBy WHERE ProcessoID=".$processoid;
	$re = mysql_query($qq,$conn);
	$rwe = mysql_fetch_assoc($re);
	$nome = $rwe['Name'];
	$datainicio = $rwe['Inicio'];
	$status = $rwe['Status'];
	if ($status==1) {
		$txcon = ' [ CONCLUIDO ] ';
	}
	$quem = $rwe['FirstName']."  ".$rwe['LastName'];
	$createdby = $rwe['CreatedBy'];
	$dataultimo = $rwe['AddedDate'];
	$herbaria = $rwe['Herbaria'];
	$tt .= $nome."  ".$txcon;
	$bgi=1;

echo "
<br />
<table class='myformtable' cellpadding='7' align='center' >
<thead>
  <tr><td colspan='2'>$tt</td></tr>
</thead>
<tbody>
  <form name='coletaform' action='processo-amostras-distribute.php' method='post'>
  <input type='hidden' name='ispopup' value='$ispopup' >
  <input type='hidden' name='processoid' value='".$processoid."' />";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td colspan='2' class='tdformnotes'>Esta função irá distribuir as duplicatas para os diferentes herbários informados no processo, seguindo a preferência da ordem indicada</td>
  </tr>";
  if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td ><b>Lista de Herbários</b></td><td><textarea id='herbariaplaceid'  name=\"herbariatxt\" rows='2' cols='50'></textarea></td>
  </tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td ><b>Importante</b></td><td class='tdformnotes'>Se você usar a palavra ESPECIALISTA na lista de herbários, esta palavra será substituida pelo herbário do especialista da família de cada amostra, se existir. Se não existir a palavra será ignorada. Portanto, se deseja enviar amostras a especialistas, certifique-se que os especialistas e seus herbários estão cadastrados na base antes de fazer a distribuição.<br /> Herbários já indicados para duplicatas  serão considerados! Apenas amostras marcadas como EXISTE serão consideradas! &nbsp;<input  type='button'  style=\"color:#339933; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"   value='Especialistas'  onmouseover=\"Tip('Ver/editar Especialistas');\" onclick = \"javascript:small_window('especialista-gridsave.php?processoid=".$processoid."&ispopup=1',900,600,'Especialistas');\" /></td>
  </tr>";
  ////////////////////////////////////////////////////////////////////////////////////////////////////
 ///OPCAO TEMPORARIA A SER ELIMINADA////////////
if ($traitfertid>0 && $lixofert>0) {
	$qn = "SELECT * FROM Traits WHERE TraitID=".$traitfertid;
	$rn = mysql_query($qn,$conn);
	$rw = mysql_fetch_assoc($rn);
	$tn = $rw['TraitName'];
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td >
      <table>
        <tr>
          <td>Categorias de <b>".$tn."</b> para <b>EXCLUIR</b></td>
          <td>
            <select name='ferttoexcl' multiple='5'>
    ";
	$qt = "SELECT * FROM Traits WHERE ParentID=".$traitfertid;
    $rt = mysql_query($qt,$conn);
	while ($rtw = mysql_fetch_assoc($rt)) {
		echo "
              <option value='".$rtw['TraitName']."'>".$rtw['TraitName']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>";
	}
    echo "
            </select>
          </td>
        </tr>
      </table>
    </td>
  </tr>";

}
//TERMINA OPCAO TEMPORARIA A SER ELIMINADA
////////////////////////////////////////////////////////////////////////////////////////////////////
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2'>
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value='' />
        <td align='center' ><input style='cursor: pointer'  type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\" /> </td>
      </tr>
    </table>
  </td>
</tr>
</form>
</tbody>
</table>
";
} 
else {
	$tbname = 'processo_'.$processoid;
	$herb = explode(";",$herbariatxt);
	$herbcl = array();
	 foreach ($herb as $hh) {
	 	$hv =  trim($hh);
		if (!empty($hv)) {
	 		$herbcl[] =  trim(strtoupper($hh));
	 	}
	 }
	if (!empty($ferttoexcl)) {
		$qwhere = " AND Fert NOT LIKE '%".$ferttoexcl."%' AND Fert<>'' AND Fert IS NOT NULL";
	} 
	else {
		$qwhere = "";
	}
	$qq = "SELECT tb.*,idd.FamiliaID,idd.GeneroID,gg.EspecialistaID FROM ".$tbname." AS tb LEFT JOIN Identidade as idd USING(DetID) LEFT JOIN Tax_Generos as gg ON gg.GeneroID=idd.GeneroID WHERE  EXISTE=1".$qwhere;
	$re = mysql_query($qq,$conn);
	$succ = 0;
	//echo $qq.'<br />';
		while($rwe = mysql_fetch_assoc($re)) {
			$specid = $rwe['EspecimenID'];
			$ndups = $rwe['NDuplic'];
			$thefamid = $rwe['FamiliaID'];
			$theespecialistaid = $rwe['EspecialistaID']+0;

			//PEGA O HERBARIO DO ESPECIALISTA SE HOUVER
			unset($qe);
			if ($theespecialistaid>0) {
			    //CASO HAJA PARA O GENERO
				$qe =  "SELECT * FROM Especialistas WHERE EspecialistaID='".$theespecialistaid."' AND Herbarium<>'' AND Herbarium IS NOT NULL";
			} else {
			    //CASO HAJA PARA A FAMILIA
				$qe = "SELECT * FROM Especialistas WHERE FamiliaID='".$thefamid."' AND Herbarium<>''  AND Herbarium IS NOT NULL LIMIT 0,1";
			}
			$rqe = @mysql_query($qe,$conn);
			//CASO HAJA PARA GENERO OU FAMILIA
			if ($rqe) {
				$rwqe = @mysql_fetch_assoc($rqe);
				$theespherb = trim(strtoupper($rwqe['Herbarium']));
			} else {
				$theespherb = NULL;
			}
			
			$oldherb = explode(',', $rwe['Herbaria']);
			$oldherbcl = array();
			 foreach ($oldherb as $hh) {
			 	$hv =  trim($hh);
			 	if (!empty($hv)) {
				 	$oldherbcl[] = $hv;
			 	}
			}
			$moreherb = array_diff($herbcl,$oldherbcl);
			$allherbs = array_merge((array)$oldherbcl,(array)$moreherb);
			//echopre($oldherbcl);
			//echopre($herbcl);
			$theherb = array();
			$iend = $ndups-1;
			if ($ndups>0) {
				$kz2 =0;
				$kz = array_search('ESPECIALISTA',$allherbs);
				if ($kz>0 && !empty($theespherb)) {
					$kz2 = array_search($theespherb,$allherbs);
					If ($kz2>0) {
						$allherbs[$kz] = $allherbs[$kz2];
						unset($allherbs[$kz2]);
					} else {
						$allherbs[$kz] = $theespherb;
					}
				} elseif ($kz>0) {
					unset($allherbs[$kz]);
				}
				$allherbs = array_values($allherbs);
			    for($i=0;$i<=$iend;$i++) {
					$curh = $allherbs[$i];
					$theherb[] = $curh;
				}
				$newherb = implode(",",$theherb);
				$qu = "UPDATE ".$tbname." SET Herbaria='".$newherb."'  WHERE EspecimenID=".$specid;
				//echo $thefam."  vai para ".$newherb."<br />";
				$rs = mysql_query($qu,$conn);
				$qu = "UPDATE `ProcessosLIST` SET Herbaria='".$newherb."'  WHERE EspecimenID=".$specid;
				$rs = mysql_query($qu,$conn);
				//atualizar o banco corretamente
				$arrayofvalues = array('Herbaria' => $newherb);
				CreateorUpdateTableofChanges($specid,'EspecimenID','Especimenes',$conn);
				$updatespecid = UpdateTable($specid,$arrayofvalues,'EspecimenID','Especimenes',$conn);
				if ($rs) {  $succ++;}
			}
			//echo $thefam."  ".$theespherb."<br />";
			session_write_close();
	  }
	
}
if ($succ>0) {
	echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>Foram registrados herbários para $succ amostras!</td></tr>
  <tr><td class='tdsmallbold' align='center'><input style='cursor:pointer' type='button'  class='bsubmit'  value='Fechar'  onclick='javascript: window.close();' ></td></tr>  
</table>
<br />";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//, "<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>