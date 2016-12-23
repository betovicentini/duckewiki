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
);

if (empty($herbariumsigla)) {
	$herbariumsigla = 'HERB_NO';
}


$title = 'Registrar No. '.$herbariumsigla;
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($final)) {
	$tt = $title;
echo "
<br />
<table class='myformtable' cellpadding='7' align='center'  width='80%'>
<thead>
  <tr><td >$tt</td></tr>
</thead>
<tbody>
  <form name='coletaform' action='processo-amostras-ninpa.php' method='post'>
  <input type='hidden' name='ispopup' value='$ispopup' >
  <input type='hidden' name='processoid' value='".$processoid."' />";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td >Esta função irá atribuir o No. da coleção do herbário $herbariumsigla as amostras do processo que ainda não tem número!</td>
  </tr>";
  
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;
  echo "
  <tr bgcolor = '".$bgcolor."'>
    <td >Apenas amostras marcadas como EXISTE serão consideradas!</td>
  </tr>";
  if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
    <table>
      <tr>
        <td>Digite o primeiro número a ser usado</td>
        <td ><input type=text name='numinicial' value='$numinicial' /></td>
      </tr>
    </table>
  </td>
</tr>";
if ($traitfertid>0) {
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
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td >
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
} else {
	$tbname = 'processo_'.$processoid;
	$erro = array();
	$count = 0;
	//echopre($ppost);
	//if ($lixo==100000000) 	{
	if (($numinicial+0)>0) {
		$qq = "SELECT * FROM Especimenes WHERE  INPA_ID=".$numinicial;
		$re = mysql_query($qq,$conn);
		$nre = mysql_numrows($re);
		$qq = "SELECT * FROM ProcessosLIST WHERE  ".$herbariumsigla."=".$numinicial;
		$re = mysql_query($qq,$conn);
		$nre2 = mysql_numrows($re);
		$nre = $nre+$nre2;
		if ($nre>0) {
			$erro[] = "O número inicial informado já existe na base!";
		} else {
			//FAZ O CADASTRO APENAS NA TABELA TEMPORARIA
			if (!empty($ferttoexcl)) {
				$qwhere = " AND Fert NOT LIKE '%".$ferttoexcl."%' AND Fert<>'' AND Fert IS NOT NULL";
			} else {
				$qwhere = "";
			}
			$qq = "SELECT * FROM ".$tbname." WHERE  EXISTE=1 AND (".$herbariumsigla."=0 OR ".$herbariumsigla." IS NULL)".$qwhere;
			$re2 = mysql_query($qq,$conn);
			$nre2 = mysql_numrows($re2);
			if ($nre2>0) {
				$it = $numinicial;
				while ($rew = mysql_fetch_assoc($re2)) {
						$qu = "UPDATE  ".$tbname." SET ".$herbariumsigla."=".$it."  WHERE EspecimenID=".$rew['EspecimenID'];
						$ru = mysql_query($qu,$conn);
						if ($ru) {
							$qu = "UPDATE ProcessosLIST SET ".$herbariumsigla."=".$it."  WHERE EspecimenID=".$rew['EspecimenID'];
							$ruu = mysql_query($qu,$conn);
							if ($ruu) {
								$count++;
								$it++;
							}
						}
				}
			} else {
				$erro[] = 'Não há registros anotados como EXISTE e que AINDA NÃO TEM NÚMERO '.$herbariumsigla;
			}
		}
	} else {
		$erro[] = 'Precisa informar o número inicial';
	}
	if (count($erro)>0) {
	echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='erro'>";
foreach($erro as $er) {
echo " <tr><td class='tdsmallbold' align='center'>".$er."</td></tr>";
}
echo "
  <tr><td class='tdsmallbold' align='center'><input style='cursor:pointer' type='button'  class='bsubmit'  value='Fechar'  onclick='javascript: window.close();' ></td></tr>  
</table>
<br />";
	}
	if ($count>0) {
	echo "
<br />
<table cellpadding=\"7\" width='50%' align='center' class='success'>
  <tr><td  style=\"color:#ffffff; font-size: 1em; font-weight:bold;\" align='center'>Foram registrados número <b>".$herbariumsigla."</b> para <b>".$count."</b> amostras!</td></tr>
  <tr><td  style=\"color:#E00000 ; font-size: 1.3em; font-weight:bold;\" align='center'>O último número usado foi <b>".($it-1)."</b></td></tr>
  <tr><td  align='center'><input style='cursor:pointer' type='button'  class='bsubmit'  value='Fechar'  onclick='javascript: window.close();' ></td></tr>  
</table>
<br />";
	}
	
	//} //if lixo
	
}



$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//, "<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>