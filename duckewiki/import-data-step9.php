<?php
//este script checa a taxonomia de um arquivo a ser importado e adiciona as colunas correspondentes na tabela temporaria criada durante a importacao
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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array();
$title = 'Importar Dados Passo 09';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$fields = unserialize($_SESSION['fieldsign']);

if (!isset($taxafields)) {
	$basicarr = array('FAMILY' => 'FAMILY','GENUS' => 'GENUS', 'SP1' => 'SP1','SP2' => 'SP2');
	$fields = unserialize($_SESSION['fieldsign']);
	$taxafields = array_intersect($basicarr,$fields);
	$taxafields = array_values($taxafields);
} 
else {
	$tax = unserialize($taxafields);
	$taxafields = array_values($tax);
}

//echopre($taxafields);
//echopre($fields);


$collfam = $tbprefix.'FamiliaID';
$collgen = $tbprefix.'GeneroID';
$collspp = $tbprefix.'EspecieID';
$collinfspp = $tbprefix.'InfraEspecieID';
if (count($taxafields)>0) {
	//unset($taxafields[0]);
	//$taxafields = array_values($taxafields);
	$fiel = trim($taxafields[0]);
	if ($fiel=='GENUS' && !in_array('FAMILY',$fields)) {
		//echo $fiel." aqui 2<br >";
		$colcol = array_search('GENUS',$fields);
		if (count($colcol)>1) {
			$erro = '<br />Tem mais de uma coluna que define generos??';
		} else {
			if (!isset($gendone)) {
				$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$collgen." INT(10) DEFAULT 0, ADD COLUMN ".$collfam." INT(10) DEFAULT 0";
				@mysql_query($qq,$conn);
				$qq = "UPDATE ".$tbname." as tb, Tax_Generos as pl set tb.".$collgen."=pl.GeneroID, tb.".$collfam."=pl.FamiliaID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.Genero)";
				mysql_query($qq,$conn);
			} else {
				if (count($generonovo)>0) {
					foreach ($generonovo as $kk => $vv) {
						$vv = trim($vv);
						if (!empty($vv)) {
							$qq = "UPDATE ".$tbname." as tb set tb.".$colcol."='".$vv."' where tb.".$colcol."='".$kk."'";
							mysql_query($qq,$conn);
							flush();
						} 
					}
					$qq = "UPDATE ".$tbname." as tb, Tax_Generos as pl set tb.".$collgen."=pl.GeneroID, tb.Co_FamiliaID=pl.FamiliaID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.Genero) AND tb.".$collgen."=0";
					mysql_query($qq,$conn);
				}
				if (count($generoid)>0) {
					foreach ($generoid as $kk => $vv) {
						$vv = $vv+0;
						if ($vv>0) {
							$qu = "SELECT * FROM Tax_Generos WHERE GeneroID='".$vv."'";
							$ru = mysql_query($qu,$conn);
							$gg = mysql_fetch_assoc($ru);
							$famid = $gg['FamiliaID'];
							$qq = "UPDATE ".$tbname." as tb set tb.".$collgen."=".$vv.", tb.".$collfam."=".$famid." where tb.".$colcol."='".$kk."'";
							mysql_query($qq,$conn);
							flush();
						} 
					}
				}
			}
			$qq = "SELECT DISTINCT `".$colcol."` as missgen FROM `".$tbname."` WHERE `".$collgen."`=0 AND `".$colcol."` IS NOT NULL AND `".$colcol."`<>'' ORDER BY `".$colcol."`";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			//echo $qq."<br >";
			unset($_POST['generoid']);
			if ($nr>0) {
			echo "<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>Gêneros não encontrados no Wiki</td></tr>
  <tr class='subhead'>
    <td>Nome</td>
    <td>Pode ser um desses</td>
    <td>Cadastre novo</td>
    <td>Substitua por</td>
  </tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td class='tdformnotes' colspan='100%'>
Se algum registro não tiver identificação no nível de gênero digite \"Indet\" no campo de substituição. Não cadastre um novo valor se não for de fato um nome publicado ou um nome que represente um morfotipo!</td></tr>
<form action='import-data-step9.php' method='post'>";
					foreach ($_POST as $kk => $vv) {
						$fm = explode("_",$kk);
						if ($fm!='generonovo' && !empty($vv)) {
							echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					echo "
  <input type='hidden' name='gendone' value='1' />
  <input type='hidden' name='taxafields' value='".serialize($taxafields)."' />";
					while ($rw = mysql_fetch_assoc($rr)) {
						$gen = strtolower(trim($rw['missgen']));
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$gk = $rw['missgen'];
						$nc = strlen($gen);

						$n1 = floor($nc/2)-1;
						$n2 = ceil($nc/2)-1;
						$g1 = substr($gen,0,$n1);
						$g2 = substr($gen,$n1+1,$n2);

						$qq = "SELECT * FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE (LOWER(Genero) LIKE '".$g1."%' OR LOWER(Genero) LIKE '%".$g2."' OR LOWER(Genero) LIKE '%".$gen."%'";
							if (count($gggen)>1) {
								$qqq = "";
								foreach ($gggen as $gg) {
									$gg = trim($gg);
									if (!empty($gg) && strlen($gg)>2) {
										$qqq .= " OR LOWER(Genero) LIKE '%".$gg."%'";
									}
								}
							}
						$qq .= $qqq.") AND Tax_Generos.Valid=1 ORDER BY Genero";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
    <td><font color='red'>".$gk."</font></td>
    <td>
      <select id=\"genero_".$ggen."\" name=\"generoid[".$gk."]\">
        <option value=''>".GetLangVar('nameselect')."</option>";
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
										echo "<option value=".$row['GeneroID'].">".$row['Genero']." [".$row['Familia']."]</option>";
									}
								} else {
									echo "
        <option value=''>Nada parecido. Procure abaixo ou cadastre nova!</option>";
								}
									echo "
        <option value=''>----------</option>";
									$qq = "SELECT * FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) ORDER BY Genero,Familia";
									$res = mysql_query($qq,$conn);
									$nres = mysql_numrows($res);
									if ($nres>0) {
										while ($row = mysql_fetch_assoc($res)) {
											echo "
        <option value=".$row['GeneroID'].">".$row['Genero']." [".$row['Familia']."]</option>";
										}
									}
						echo "
      </select>
    </td>
    <td align='center'><img style='cursor:pointer;'  src='icons/list-add.png' height=15 ";
	$myurl ="genero-popup.php?generofieldid=genero_".$gen."&spnome=".$gk; 
	echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Genero');\"></td>
	<td align='center'><input type='text' value='' name=\"generonovo[".$gk."]\" /></td>
  </tr>";

									}
					echo "
  <tr><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</form>
</tbody>
</table>
";
		} 
			else {
				unset($taxafields[0]);
				$taxafields = array_values($taxafields);
				$fiel = $taxafields[0];
				//echo "genus fiel".$fiel;
				//echopre($taxafields);
			}
		}
	} 
	if ($fiel=='FAMILY' && in_array('GENUS',$taxafields)) {
		$colcol = array_search('FAMILY',$fields);
		if (count($colcol)>1) {
			$erro = '<br />Tem mais de uma coluna que define familia??';
		} else {
			if (!isset($familydone)) {
				$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$collfam." INT(10) DEFAULT 0";
				@mysql_query($qq,$conn);
				$qq = "UPDATE ".$tbname." as tb, Tax_Familias as pl set tb.".$collfam."=pl.FamiliaID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.Familia) AND pl.Valid=1";
				mysql_query($qq,$conn);
			} else {
				if (count($familianovo)>0) {
					foreach ($familianovo as $kk => $vv) {
						$vv = trim($vv);
						if (!empty($vv)) {
							$val = str_replace("'","",$kk);
							$qq = "UPDATE ".$tbname." as tb set tb.".$colcol."= '".$vv."' where tb.".$colcol."='".$val."'";
							mysql_query($qq,$conn);
							flush();
						}
					}
					$qq = "UPDATE ".$tbname." as tb, Tax_Familias as pl set tb.".$collfam."=pl.FamiliaID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.Familia) AND pl.Valid=1 AND tb.".$collfam."=0";
					mysql_query($qq,$conn);
				}
				if (count($familiaid)>0) {
					foreach ($familiaid as $kk => $vv) {
						$vv = $vv+0;
						if ($vv>0) {
							$qq = "UPDATE ".$tbname." as tb set tb.".$collfam."= ".$vv." where tb.".$colcol."='".$kk."'";
							mysql_query($qq,$conn);
							flush();
						}
					}
				}


			}
			$qq = "SELECT DISTINCT `".$colcol."` as missgen FROM `".$tbname."` WHERE `".$colcol."`<>'' AND `".$colcol."` IS NOT NULL AND `".$collfam."`=0";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			unset($_POST['familiaid']);
			if ($nr>0) {
			echo "<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
 <tr><td colspan='100%'>Famílias não encontradas no Wiki</td></tr>
 <tr class='subhead'>
   <td>Nome</td>
   <td>Pode ser uma dessas</td>
   <td>Cadastre nova</td>
   <td>Substitua por</td>
 </tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td class='tdformnotes' colspan='100%'>
Se algum registro não tiver identificação no nível de família digite \"Indet\" no campo de substituição.</td></tr>
  <form action='import-data-step9.php' method='post'>";
					foreach ($_POST as $kk => $vv) {
						$fm = explode("_",$kk);
						if ($fm!='familianovo' && !empty($vv)) {
							echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					echo "
  <input type='hidden' name='familydone' value='1' />
  <input type='hidden' name='taxafields' value='".serialize($taxafields)."' />";
					while ($rw = mysql_fetch_assoc($rr)) {
						$gen = strtolower(trim($rw['missgen']));
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$gk = $rw['missgen'];
						$nc = strlen($gen);
						if ($nc>=10) {
							$n1 = floor($nc/2)-1;
							$n2 = ceil($nc/2)-1;
							$g1 = substr($gen,0,$n1);
						} else {
							$g1 = $gen;
						}


						$qq = "SELECT * FROM Tax_Familias WHERE (LOWER(Familia) LIKE '%".$g1."%'";
							if (count($gggen)>1) {
								$qqq = "";
								foreach ($gggen as $gg) {
									$gg = trim($gg);
									if (!empty($gg) && strlen($gg)>2) {
										$qqq .= " OR LOWER(Familia) LIKE '%".$gg."%'";
									}
								}
							}
						$qq .= $qqq.") AND Valid=1 ORDER BY Familia";

						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
<tr bgcolor = '".$bgcolor."'>
  <td><font color='red'>".$gk."</font>  </td>
  <td>
    <select id=\"familia_".$ggen."\" name=\"familiaid[".$gk."]\">
      <option value=''>".GetLangVar('nameselect')."</option>";

								$qzz = "SELECT * FROM Tax_Familias WHERE LOWER(Familia) LIKE '%".$gen."%' AND Valid=0 ORDER BY Familia";
								$rzz = mysql_query($qzz,$conn);
								$nrzz = mysql_numrows($rzz);
								if ($nrzz>0) {
									while($rzwz = mysql_fetch_assoc($rzz)) {
										$qz= "SELECT * FROM Tax_Familias WHERE (Sinonimos LIKE '%familia|".$rzwz['FamiliaID'].";%' OR Sinonimos LIKE '%familia|".$rzwz['FamiliaID']."') AND Valid=1 ORDER BY Familia";
										$rz = mysql_query($qz,$conn);
										while($rzw = mysql_fetch_assoc($rz)) {
											echo "
      <option value=".$rzw['FamiliaID'].">".$rzw['Familia']."</option>";
										}
									}
								} 
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
											echo "
      <option value=".$row['FamiliaID'].">".$row['Familia']."</option>";
									}
								} 
							echo "
      <option value=''>-------</option>";
								if ($nres==0 && $nrzz==0) {
									echo "
      <option value=''>Nada parecido. Procure abaixo ou cadastre nova!</option>";
     
	  }
	   $qz= "SELECT * FROM Tax_Familias WHERE Valid=1 ORDER BY Familia";
	  $rz = mysql_query($qz,$conn);
	  while($rzw = mysql_fetch_assoc($rz)) {
        echo "
      <option value=".$rzw['FamiliaID'].">".$rzw['Familia']."</option>";
	  }
						echo "
      </select>
        </td>
        <td align='center'>
        <img style='cursor:pointer;'  src='icons/list-add.png' height='15' ";
		$myurl ="familia-popup.php?familiafieldid=familia_".$gen."&spnome=".$gen;
		echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Familia');\">
        </td>
        <td align='center'><input type='text' value='' name=\"familianovo[".$gk."]\" /></td>
      </tr>";

				}
					if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
					echo "
      <tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
    </form>
</tbody>
</table>";
		} else {
			$familiasdone = 1;
			unset($taxafields[0]);
			$taxafields = array_values($taxafields);
			$fiel = $taxafields[0];
		}
		}
	}
	if ($fiel=='GENUS' && in_array('FAMILY',$fields) && !in_array('FAMILY',$taxafields)) {
		$colcol = array_search('GENUS',$fields);
		if (count($colcol)>1) {
			$erro = '<br />Tem mais de uma coluna que define generos??';
		} 
		else {
			if (!isset($generodone)) {
				$qq = "ALTER TABLE `".$tbname."` ADD COLUMN `".$collgen."` INT(10) DEFAULT 0";
				@mysql_query($qq,$conn);
				$qq = "UPDATE ".$tbname." as tb, Tax_Generos as pl set tb.".$collgen."=pl.GeneroID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.Genero) AND tb.".$collfam."=pl.FamiliaID";
				mysql_query($qq,$conn);
			} 
			else {
				if (count($generonovo)>0) {
					foreach ($generonovo as $kk => $vv) {
						$vv = trim($vv);
						if (!empty($vv)) {
							$za = explode("_",$kk);
							$nza = count($za)-1;
							$fid = $za[$nza];
							unset($za[$nza]);
							$val = implode("_",$za);
							$val = str_replace("'","",$val);
							$fid = str_replace("'","",$fid);
							$qq = "UPDATE ".$tbname." as tb set tb.".$colcol."= '".$vv."' where tb.".$collfam."=".$fid."  AND tb.".$colcol."='".$val."'";
							mysql_query($qq,$conn);
							flush();
						} 
					}
					$qq = "UPDATE ".$tbname." as tb, Tax_Generos as pl set tb.".$collgen."=pl.GeneroID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.Genero) AND tb.".$collgen."=0 AND tb.".$collfam."=pl.FamiliaID";
					mysql_query($qq,$conn);
				}
				if (count($generoid)>0) {
					foreach ($generoid as $kk => $vv) {
						$vv = $vv+0;
						if ($vv>0) {
							$qu = "SELECT * FROM Tax_Generos WHERE GeneroID='".$vv."'";
							$ru = mysql_query($qu,$conn);
							$gg = mysql_fetch_assoc($ru);
							$famid = $gg['FamiliaID'];
							$qq = "UPDATE ".$tbname." as tb set tb.".$collgen."= ".$vv.", tb.".$collfam."=".$famid." where tb.".$colcol."='".$kk."'";
							//echo $qq."<br />";
							mysql_query($qq,$conn);
							flush();
						}
					}
				}
			}
			$qq = "SELECT DISTINCT tb.".$colcol." as missgen,fam.Familia as familia,fam.FamiliaID  FROM ".$tbname." as tb JOIN Tax_Familias as fam ON tb.".$collfam."=fam.FamiliaID WHERE tb.".$collgen."=0 AND tb.".$colcol." IS NOT NULL AND tb.".$colcol."<>'' ORDER BY tb.".$colcol."  LIMIT 0,20";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			unset($_POST['generoid']);
			if ($nr>0) {
			echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>Os seguintes gêneros não foram encontrados no Wiki</td></tr>
  <tr class='subhead'>
    <td>Nome</td>
    <td>Pode ser um desses?</td>
    <td>Cadastre novo</td>
    <td>Substitua por</td>
  </tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td class='tdformnotes' colspan='100%'>
Se algum registro não tiver identificação no nível de gênero digite \"Indet\" no campo de substituição. Não cadastre um novo valor se não for de fato um nome publicado ou um nome que represente um morfotipo!</td></tr>
<form action='import-data-step9.php' method='post'>";
					foreach ($_POST as $kk => $vv) {
						$fm = explode("_",$kk);
						if ($fm!='generonovo' && !empty($vv)) {
							echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					echo "
  <input type='hidden' name='generodone' value='1' />
  <input type='hidden' name='taxafields' value='".serialize($taxafields)."' />";

					while ($rw = mysql_fetch_assoc($rr)) {
						$gen = strtolower(trim($rw['missgen']));
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$gk = $rw['missgen'];
						$famid = $rw['FamiliaID'];
						$famtxt = $rw['familia'];
						$nc = strlen($gen);
						$n1 = floor($nc/2)-1;
						$n2 = ceil($nc/2)-1;

						$g1 = substr($gen,0,$n1);
						$g2 = substr($gen,$n1+1,$n2);

						$qq = "SELECT * FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE (LOWER(Genero) LIKE '".$g1."%' OR LOWER(Genero) LIKE '%".$g2."' OR LOWER(Genero) LIKE '%".$gen."%'";
							if (count($gggen)>1) {
								$qqq = "";
								foreach ($gggen as $gg) {
									$gg = trim($gg);
									if (!empty($gg) && strlen($gg)>2) {
										$qqq .= " OR LOWER(Genero) LIKE '%".$gg."%'";
									}
								}
							}
						$qq .= $qqq.") AND Tax_Generos.Valid=1 ";
						if ($famid>0) {
							//$qq .= " AND Tax_Generos.FamiliaID='".$famid."'";
						}
						$qq .= " ORDER BY Genero,Familia";



						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
  <tr bgcolor = '".$bgcolor."'>
    <td><font color='red'>".$gk."</font>  [".$famtxt."]</td>
    <td>
      <select id=\"genero_".$ggen."\" name=\"generoid[".$gk."]\">
        <option value=''>".GetLangVar('nameselect')."</option>";
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
										echo "
        <option value=".$row['GeneroID'].">".$row['Genero']." [".$row['Familia']."]</option>";
									}
								} else {
									echo "
        <option value=''>Nada parecido. Procure abaixo ou cadastre nova!</option>";
								}
        echo "
        <option value=''>-------</option>";
	  $qz= "SELECT * FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE Tax_Generos.Valid=1 ORDER BY Genero,Familia";
	  $rz = mysql_query($qz,$conn);
	  while($rzw = mysql_fetch_assoc($rz)) {
        echo "
        <option value=".$rzw['GeneroID'].">".$rzw['Genero']." [".$rzw['Familia']."]</option>";
	  }
						echo "
      </select>
    </td>
    <td align='center'>
      <img style='cursor:pointer;' src='icons/list-add.png' height=15 ";
		$myurl ="genero-popup.php?generofieldid=genero_".$gen."&spnome=".$gk."&famid=".$famid; 
		echo " onclick = \"javascript:small_window('$myurl',600,600,'Nova Familia');\">
	</td>
	<td><input type='text' value='' name=\"generonovo[".$gk."_".$famid."]\" /></td>
</tr>";

				}
					echo "
<tr><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</form>
</tbody>
</table>
";
		} else {
			$generosdone = 1;
			unset($taxafields[0]);
			$taxafields = array_values($taxafields);
			$fiel = $taxafields[0];
		}
		}
}
	//echo $fiel." aqui 2<br >";
	if ($fiel=='SP1' && in_array('GENUS',$fields) && !in_array('GENUS',$taxafields)) {
		//echopre($fields);
		$colcol = array_search('SP1',$fields);
		if (count($colcol)>1) {
			$erro = '<br />Tem mais de uma coluna que define especies??';
		} else {
			if (!isset($spdone)) {
				$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$collspp." INT(10) DEFAULT 0";
				@mysql_query($qq,$conn);
				$qq = "UPDATE ".$tbname." as tb, Tax_Especies as pl set tb.".$collspp."=pl.EspecieID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.Especie) AND tb.".$collgen."=pl.GeneroID";
				mysql_query($qq,$conn);
			} else {
				if (count($especieidsnovo)>0) {
					foreach ($especieidsnovo as $kk => $vv) {
						$vv = trim($vv);
						if (!empty($vv)) {
							$za = explode("_",$kk);
							$nza = count($za)-1;
							$fid = $za[$nza];
							unset($za[$nza]);
							$val = implode("_",$za);
							$val = str_replace("'","",$val);
							if (strtolower($vv)=='indet') { $vv='';}
							$fid = str_replace("'","",$fid);
							$qq = "UPDATE ".$tbname." as tb set tb.".$colcol."= '".$vv."' where tb.".$collgen."=".$fid."  AND tb.".$colcol."='".$val."'";
							mysql_query($qq,$conn);
							flush();
						} 
					}
					$qq = "UPDATE ".$tbname." as tb, Tax_Especies as pl set tb.".$collspp."=pl.EspecieID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.Especie) AND tb.".$collgen."=pl.GeneroID AND tb.".$collspp."=0";
					mysql_query($qq,$conn);
				}
				if (count($especieids)>0) {
					foreach ($especieids as $kk => $vv) {
						$vv = $vv+0;
						if ($vv>0) {
							$oldgenid = $oldgeneroids[$kk]+0;

							$vv = trim($vv);
							$za = explode("_",$kk);
							$nza = count($za)-1;
							$genid = $za[$nza];
							$oldgenid = $za[$nza];
							unset($za[$nza]);
							$kkk = implode("_",$za);

							$qu = "SELECT * FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE EspecieID='".$vv."'";
							$ru = mysql_query($qu,$conn);
							$gg = mysql_fetch_assoc($ru);
							$genid = $gg['GeneroID'];
							$famid = $gg['FamiliaID'];
							$qq = "UPDATE ".$tbname." as tb set tb.".$collspp."= ".$vv.", tb.".$collgen."=".$genid.", tb.".$collfam."=".$famid." where tb.".$colcol."='".$kkk."' AND tb.".$collgen."='".$oldgenid."'";
							mysql_query($qq,$conn);
							flush();
						}
					}
				}
			}
			$qq = "SELECT DISTINCT gg.Genero,tb.".$colcol." as missgen,tb.".$collgen." as oldgenid,gg.FamiliaID as famid, ff.Familia";
			if (in_array('AUTHOR1',$fields)) {
				$colautor = array_search('AUTHOR1',$fields);
				$qq .= ", ".$colautor." as spautor";
			}
			$qq .=" FROM ".$tbname." as tb JOIN Tax_Generos as gg ON tb.".$collgen."=gg.GeneroID JOIN Tax_Familias AS ff ON gg.FamiliaID=ff.FamiliaID WHERE tb.".$collspp."=0  AND tb.".$colcol." IS NOT NULL AND tb.".$colcol."<>'' ORDER BY tb.".$colcol."  LIMIT 0,20";
			//echo $qq."<br />";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			unset($_POST['especieids']);
			unset($_POST['oldgeneroids']);
			unset($especieids);
			unset($oldgeneroids);
			if ($nr>0) {
			echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>Epítetos não encontrados no Wiki</td></tr>
  <tr class='subhead'>
    <td>Nome</td>
    <td>Pode ser um desses</td>
    <td>Cadastre novo</td>
    <td>Substitua por</td>
  </tr>
</thead>
<tbody>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td class='tdformnotes' colspan='100%'>
Se algum registro não tiver identificação no nível de espécie digite \"Indet\" no campo de substituição.\nProcure não registrar valores sem significado como \"sp.\". Não cadastre um novo valor se não for de fato um nome publicado ou um nome que represente um morfotipo! Modificadores de nomes como cf. aff. vel aff. devem estar em campos separados no arquivo original.</td>
</tr>
<form action='import-data-step9.php' method='post'>";
					foreach ($_POST as $kk => $vv) {
						$fm = explode("_",$kk);
						if ($fm!='especieidsnovo' && !empty($vv)) {
							echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					echo "
  <input type='hidden' name='spdone' value='1' />
  <input type='hidden' name='taxafields' value='".serialize($taxafields)."' />";

					$iir=0;
					while ($rw = mysql_fetch_assoc($rr)) {
						$iir++;
						$gen = strtolower(trim($rw['missgen']));
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$gk = $rw['missgen'];
						$genid = $rw['oldgenid'];
						$spautor = $rw['spautor'];
						$genero = $rw['Genero'];
						$famid = $rw['famid'];
						$afff = $rw['Familia'];

						$nc = strlen($gen);
						if ($nc>10) {
						$n1 = floor($nc/2)-1;
						$n2 = ceil($nc/2)-1;
						$g1 = substr($gen,0,$n1);
						$g2 = substr($gen,$n1+1,$n2);
						} else {
							$g1 = $gen;
							$g2 = $gen;
						}
						echo "
  <input type='hidden' name=\"oldgeneroids[".$gk."]\" value='".$genid."' />";


						$qq = "SELECT * FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE (LOWER(Especie) LIKE '".$g1."%' OR LOWER(Especie) LIKE '%".$g2."' OR LOWER(Especie) LIKE '%".$gen."%'";
							if (count($gggen)>1) {
								$qqq = "";
								foreach ($gggen as $gg) {
									$gg = trim($gg);
									if (!empty($gg) && strlen($gg)>2) {
										$qqq .= " OR LOWER(Especie) LIKE '%".$gg."%'";
									}
								}
							}
						$qq .= $qqq.") AND Tax_Especies.Valid=1 ORDER BY Genero,Especie";
						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
<tr bgcolor = '".$bgcolor."'>
    <td>".$genero." <font color='red'>".$gk."</font> [".$afff."]</td>
    <td>
      <select id=\"especie_".$iir."\" name=\"especieids[".$gk."_".$genid."]\">
        <option value=''>".GetLangVar('nameselect')."</option>";
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
										echo "
        <option value=".$row['EspecieID'].">".$row['Genero']." ".$row['Especie']." ".$row['EspecieAutor']." [".$row['Familia']."]</option>";
									}
								} 
echo "
        <option value=''>-------------------------</option>";

						$qq = "SELECT * FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE GeneroID='".$genid."'";
						$qq .= " AND Tax_Especies.Valid=1 ORDER BY Genero,Especie";
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
										echo "
        <option value=".$row['EspecieID'].">".$row['Genero']." ".$row['Especie']." ".$row['EspecieAutor']." [".$row['Familia']."]</option>";
									}
								} 

						echo "
      </select>
      </td>
      <td align='center'>
        <img style='cursor:pointer;' src='icons/list-add.png' height=15 ";
			$myurl ="especie-popup.php?especiefieldid=especie_".$iir."&spnome=".$gk."&genusid=".$genid."&autor=".$spautor."&genero=".$genero; 
			echo " onclick = \"javascript:small_window('$myurl',800,600,'Nova Familia');\">
      </td>
      <td><input type='text' value='' name=\"especieidsnovo[".$gk."_".$genid."]\" /></td>
    </tr>
";
		}
					echo "
  <tr><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</form>
</tbody>
</table>
";
		} else {
			$especiesdone = 1;
			unset($taxafields[0]);
			$taxafields = array_values($taxafields);
			$fiel = $taxafields[0];
			//echo "----------";
			//echopre($taxafields);
			
		}
		}
}
	if ($fiel=='SP2' && in_array('SP1',$fields) &&  !in_array('SP1',$taxafields)) {
		$colcol = array_search('SP2',$fields);
		if (count($colcol)>1) {
			$erro = '<br />Tem mais de uma coluna que define especies??';
		} else {
			if (!isset($infspdone)) {
				$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$collinfspp." INT(10) DEFAULT 0";
				@mysql_query($qq,$conn);
				$qq = "UPDATE ".$tbname." as tb, Tax_InfraEspecies as pl set tb.".$collinfspp."=pl.InfraEspecieID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.InfraEspecie) AND tb.".$collspp."=pl.EspecieID";
				mysql_query($qq,$conn);
			} else {
				if (count($infraespeciesidsnovo)>0) {
					foreach ($infraespeciesidsnovo as $kk => $vv) {
						$vv = trim($vv);
						if (!empty($vv)) {
							$za = explode("_",$kk);
							$nza = count($za)-1;
							$fid = $za[$nza];
							unset($za[$nza]);
							$val = implode("_",$za);
							$val = str_replace("'","",$val);
							if (strtolower($vv)=='indet') { $vv='';}
							$fid = str_replace("'","",$fid);
							$qq = "UPDATE ".$tbname." as tb set tb.".$colcol."= '".$vv."' where tb.".$collspp."='".$fid."' AND tb.".$colcol."='".$val."'";
							mysql_query($qq,$conn);
							flush();
						} 
					}
					$qq = "UPDATE ".$tbname." as tb, Tax_InfraEspecies as pl set tb.".$collinfspp."=pl.InfraEspecieID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.InfraEspecie) AND tb.".$collspp."=pl.EspecieID AND tb.".$collinfspp."=0";
					mysql_query($qq,$conn);
				}
				if (count($infraespeciesids)>0) {

					foreach ($infraespeciesids as $kk => $vv) {
						//echo $vv."    ".$kk."<br />";
						$vv = $vv+0;
						if ($vv>0) {
							$vv = trim($vv);
							$za = explode("_",$kk);
							$nza = count($za)-1;
							$oldspid = $za[$nza];
							$oldgenid = $za[$nza];
							unset($za[$nza]);
							$kkk = implode("_",$za);

							$qu = "SELECT * FROM Tax_InfraEspecies WHERE InfraEspecieID='".$vv."'";
							$ru = mysql_query($qu,$conn);
							$gg = mysql_fetch_assoc($ru);
							$spid = $gg['EspecieID'];
							$qq = "UPDATE ".$tbname." as tb set tb.".$collinfspp."='".$vv."', tb.".$collspp."='".$spid."' where tb.".$colcol."='".$kkk."' AND tb.".$collspp."='".$oldspid."'";
							//echo $qq."<br /><br />";
							mysql_query($qq,$conn);
							flush();
						}
					}
				}
			}
			$qq = "SELECT DISTINCT gg.Genero,spp.Especie,tb.".$colcol." as missgen,tb.".$collspp." as oldspid,tb.".$collgen." as genid,gg.FamiliaID as famid, ff.Familia as FAMILIA";
			if (in_array('AUTHOR2',$fields)) {
				$colautor = array_search('AUTHOR2',$fields);
				$qq .= ", ".$colautor." as spautor";
			}
			if (in_array('RANK1',$fields)) {
				$colrank = array_search('RANK1',$fields);
				$qq .= ", ".$colrank." as infsplevel";
			}

			$qq .=" FROM ".$tbname." as tb JOIN Tax_Especies as spp ON ".$collspp."=spp.EspecieID JOIN Tax_Generos as gg ON spp.GeneroID=gg.GeneroID JOIN Tax_Familias as ff ON gg.FamiliaID=ff.FamiliaID WHERE tb.".$collinfspp."=0  AND tb.".$colcol." IS NOT NULL AND tb.".$colcol."<>'' ORDER BY tb.".$colcol;
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			unset($_POST['infraespeciesids']);
			unset($_POST['oldspeciesids']);
			unset($infraespeciesids);
			unset($oldspeciesids);
			if ($nr>0) {
			echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>Epítetos infraespecíficos não encontrados no Wiki</td></tr>
  <tr class='subhead'>
    <td>Nome</td>
    <td>Pode ser um desses</td>
    <td>Cadastre novo</td>
    <td>Substitua por</td>
  </tr>
</thead>
<tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdformnotes' colspan='4'>Se algum registro não tiver identificação no nível infra-espécie digite \"Indet\" no campo de substituição.\nProcure não registrar valores sem significado como \"sp.\".  Não cadastre um novo valor se não for de fato um nome publicado ou um nome que represente um morfotipo! Modificadores de nomes como cf. aff. vel aff. e categorias infra-específicas como f. var. subsp. devem estar em um campo separado no arquivo original.
  <form action='import-data-step9.php' method='post'>";
					foreach ($_POST as $kk => $vv) {
						$fm = explode("_",$kk);
						if ($fm!='infraespeciesidsnovo' &&!empty($vv)) {
							echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					echo "
  <input type='hidden' name='infspdone' value='1' />
  <input type='hidden' name='taxafields' value='".serialize($taxafields)."' />
  </td>
</tr>
";
					$infii=0;
					while ($rw = mysql_fetch_assoc($rr)) {
						$infii++;
						$gen = strtolower(trim($rw['missgen']));
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$gk = $rw['missgen'];
						$spid = $rw['oldspid'];
						$spautor = $rw['spautor'];
						$genero = $rw['Genero'];
						$famid = $rw['famid'];
						$FAMILIA = $rw['FAMILIA'];
						$sppp = $rw['Especie'];
						$genid = $rw['genid'];

						$nc = strlen($gen);
						$n1 = floor($nc/2)-1;
						$n2 = ceil($nc/2)-1;
						$g1 = substr($gen,0,$n1);
						$g2 = substr($gen,$n1+1,$n2);




						$qq = "SELECT * FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE (LOWER(InfraEspecie) LIKE '".$g1."%' OR LOWER(InfraEspecie) LIKE '%".$g2."' OR LOWER(InfraEspecie) LIKE '%".$gen."%'";
							if (count($gggen)>1) {
								$qqq = "";
								foreach ($gggen as $gg) {
									$gg = trim($gg);
									if (!empty($gg) && strlen($gg)>2) {
										$qqq .= " OR LOWER(InfraEspecie) LIKE '%".$gg."%'";
									}
								}
							}
						$qq .= $qqq.") AND Tax_InfraEspecies.Valid=1 AND Tax_Generos.FamiliaID='".$famid."' ORDER BY Genero,Especie,InfraEspecie";

						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
  <tr bgcolor = '".$bgcolor."'>
    <td>".$genero." ".$sppp." <font color='red'>".$gk."</font> [".$FAMILIA."]</td>
    <td>
      <input type='hidden' name=\"oldspeciesids[".$gk."]\" value='".$spid."' />
      <select id=\"infsppid_".$infii."\" name=\"infraespeciesids[".$gk."_".$spid."]\">
        <option value=''>".GetLangVar('nameselect')."</option>";
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
										echo "
        <option value='".$row['InfraEspecieID']."'>".$row['Genero']." ".$row['Especie']." ".$row['InfraEspecie']." [".$row['Familia']."]</option>";
									}
								} 
						echo "
      </select>
    </td>
    <td align='center'>
      <img style='cursor:pointer;' src='icons/list-add.png' height=15 ";
		$myurl ="infraespecie-popup.php?infraespeciefieldid=infsppid_".$infii."&spnome=".$gk."&speciesid=".$spid."&autor=".$spautor."&genusid=".$genid."&genero=".$genero."&especie=".$sppp; 
		echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova InfraEspecie');\">
	</td>
	<td><input type='text' value='' name=\"infraespeciesidsnovo[".$gk."_".$spid."]\" /></td>
  </tr>";

				}
					echo "
 <tr><td align='center' colspan='4'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
</form>
</tbody>
</table>
";
		} else {
			$infraespeciesdone = 1;
			unset($taxafields[0]);
			$taxafields = array_values($taxafields);
		}
		}
} 

} 

//echo count($taxafields)."  aqui ";
//echo "<form action='import-data-step9.php' method='post'>"; foreach ($ppost as $kk => $vv) { echo "  <input type='hidden' name='".$kk."' value='".$vv."' />      "; }
//echo " <input type='hidden' name='ispopup' value='".$ispopup."' /><input type='hidden' name='tbname' value='temp_dadosAnibaAlexandra_1' />      
//<input type='submit' value='Continuar' class='bsubmit' /></form>";

if (count($taxafields)==0)  {
//echo count($taxafields)."  aqui ";
	$steps = unserialize($_SESSION['importacaostep']);
	unset($steps[0]);
	$stt = array_values($steps);
	$_SESSION['importacaostep'] = serialize($stt);
echo "
  <form name='myform' action='import-data-hub.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />    
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>