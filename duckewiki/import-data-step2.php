<?php
//Este script checa se identificadores de plantas marcadas e/ou amostras coletadas foram selecionados e pede definição para todas as colunas no arquivo importando, buscando automaticamente campos que tenham os mesmos nome da coluna BRAHMS dat tabela Import_Fields (note que nem todas as colunas desta tabela estão sendo usadas, mas acrescentar novas linhas permite adicionar novas definições automátcias//Modificado por AV em 25 de jun 2011
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
$which_java = array(
);
$title = 'Importar Dados Passo 02';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$erro=0;
if ($refdefined=='1') {
if (($coletas==1 || $coletas==3) && empty($plantaidfield) && empty($tagnumfield) ) {
	echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>Faltou definir um identificador das plantas marcadas</td></tr>
  </table>
<br />";
		$erro++;
}
if (($coletas==1 || $coletas==3) && empty($plantaidfield) && !empty($tagnumfield) && empty($localid) && empty($plantagazfield) && empty($plantagazidfield)) {
	echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>Planta marcada sem identificação de localidade! Precisa definir localidade ou coluna no arquivo que contém localidade</td></tr>
  </table>
<br />";
		$erro++;
}
if (!empty($coletorfield) && !empty($numcolfield)) { $speref=TRUE; } else { $speref=FALSE;}

if (($coletas==2 || $coletas==3) && empty($specimenidfield) && !$speref) {
	echo "
<br />
  <table cellpadding=\"1\" width='50%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>Faltou um identificador para as amostras coletadas</td></tr>
  </table>
  <br />";
	$erro++;
}

if ($erro>0) {
echo "
<form action='import-data-step1.php' method='post' name='impprepform'>
    <input type='hidden' name='coletas' value='".$coletas."' /> 
    <input type='hidden' name='imported' value='1' /> 
    <input type='hidden' name='tbname' value='".$tbname."' />
    <input type='hidden' name='tbprefix' value='".$tbprefix."' />
    <input type='hidden' name='ispopup' value='".$ispopup."' />    
    <br />
    <table cellpadding=\"1\" width='50%' align='center' class='erro'>
        <tr>
            <td colspan='100%' align='center'><input type='submit' value='".GetLangVar('namevoltar')."' /></td>
        </tr>
    </table>
</form>
";
} 
unset($_POST['refdefined']);
}

if (!isset($idschecked) && $erro==0) {
	if (($coletas==1 || $coletas==3) && !isset($plidschecked)) {
		//checa gazetteer se for o caso
		$cln = $tbprefix."PlantaID";
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln." INT(10) DEFAULT 0";
		@mysql_query($qq,$conn);
		if (!empty($plantaidfield)) {
			$qq = "UPDATE `".$tbname."` as tb, `Plantas` as pl set tb.`".$cln."`= pl.`PlantaID` WHERE tb.`".$plantaidfield."`= pl.`PlantaID`";
			$res = mysql_query($qq,$conn);
			$qq = "SELECT ImportID FROM `".$tbname."` WHERE `".$cln."`=0 OR (`".$cln."` IS NULL)";
			$rs = mysql_query($qq,$conn);
			$nres = mysql_numrows($rs);
			if ($nres>0) {
			   $qz = "SELECT GROUP_CONCAT(`".$cln."`, '; ') as ids FROM `".$tbname."` WHERE `".$cln."`=0 OR (`".$cln."` IS NULL)";
				$rz = mysql_query($qz,$conn);
				$rnz = mysql_fetch_assoc($rz);
				$nzzz = $rnz['ids'];
				$erro++;
echo "
<form action='import-data-form.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'><b>$nres</b> PlantaID do wiki indicado por você não foram encontrados no banco de dados!</td></tr>
  <tr><td><textarea>$nzzz</textarea></td></tr>
  <tr><td align='center'><input type='submit' value='".GetLangVar('namevoltar')."' class='bblue' /></td></tr>
</table>
<br /> 
</form>";
			} else {
				$plidschecked=TRUE;
			}
		}
		if (!empty($tagnumfield) && !isset($plidschecked)) { //senao checa se o numero ja existe, verificando tagnum+localidade
			if (!isset($plgaz)) {
			$colname = $tbprefix."PlGazetteerID";
			$coln = $tbprefix."PlantaTag";
			if (count($gazetteerid)>0) {
					foreach ($gazetteerid as $kk => $vv) {
						$vv = $vv+0;
						if ($vv>0) {
							$qq = "UPDATE `".$tbname."` as tb set tb.`".$colname."`=".$vv." where tb.`".$plantagazfield."`='".$kk."'";
							mysql_query($qq,$conn);
						}
					}
			} else {
				$qq = "ALTER TABLE `".$tbname."` ADD COLUMN `".$colname."` INT(10) 	DEFAULT 0";
				@mysql_query($qq,$conn);
				$qq = "ALTER TABLE `".$tbname."` ADD COLUMN `".$coln."` VARCHAR(20) DEFAULT ''";
				@mysql_query($qq,$conn);
				$qq = "UPDATE `".$tbname."` SET `".$coln."`=TRIM(`".$tagnumfield."`) WHERE `".$tagnumfield."`<>'' AND (`".$tagnumfield."` IS NOT NULL)";
				@mysql_query($qq,$conn);
			}
			if (!empty($plantagazidfield)) {
				$qq = "UPDATE `".$tbname."` SET `".$colname."`=`".$plantagazidfield."` WHERE `".$plantagazidfield."`<>'' AND (`".$plantagazidfield."` IS NOT NULL)";
				echo $qq."<br >";
				$rgaz = @mysql_query($qq,$conn);
				if ($rgaz) {
					$plgaz=TRUE;
				}
				
			}
			if (!$plgaz) {
			if (empty($plantagazfield) ) {
					$qq = "UPDATE `".$tbname."` SET `".$colname."`=".$localid." WHERE `".$tagnumfield."`<>'' AND (`".$tagnumfield."` IS NOT NULL)";
					$uplocal = mysql_query($qq,$conn);
					if ($uplocal) { $plgaz=TRUE;}
				} 
			else {
					if (empty($localid)) {
						$qq = "UPDATE `".$tbname."` SET `".$colname."`=checkgazetteer(`".$plantagazfield."`,0,0,0,0) WHERE `".$plantagazfield."`<>'' AND (`".$plantagazfield."` IS NOT NULL) AND (`".$colname."`=0 OR `".$colname."` IS NULL)";
					}  
					else  {
						//$qq = "SELECT GazetteerID,GazetteerTIPOtxt,Gazetteer,Municipio,Gazetteer.MunicipioID,Gazetteer.ParentID,Province,Municipio.ProvinceID,Province.CountryID FROM Gazetteer LEFT JOIN Municipio USING(MunicipioID) LEFT JOIN Province USING(ProvinceID) WHERE Gazetteer.GazetteerID='".$localid."'";
						$qq = "SELECT GazetteerID,Gazetteer,Municipio,Gazetteer.MunicipioID,Gazetteer.ParentID,Province,Municipio.ProvinceID,Province.CountryID FROM Gazetteer LEFT JOIN Municipio USING(MunicipioID) LEFT JOIN Province USING(ProvinceID) WHERE Gazetteer.GazetteerID='".$localid."'";
						$rloc = mysql_query($qq,$conn);
						$rlocw = mysql_fetch_assoc($rloc);
						$provid = $rlocw['ProvinceID'];
						$coid = $rlocw['CountryID'];
						$munid = $rlocw['MunicipioID'];
						$parentgazid = $rlocw['GazetteerID'];
						$qq = "UPDATE `".$tbname."` SET `".$colname."`=checkgazetteer(`".$plantagazfield."`,".$localid.",0,0,0) WHERE `".$plantagazfield."`<>'' AND (`".$plantagazfield."` IS NOT NULL) AND (`".$colname."`=0 OR `".$colname."` IS NULL)";
						//echo $qq."<br />";

					}
					mysql_query($qq,$conn);
					echo "&nbsp;";
					flush();
					$qq = "SELECT DISTINCT `".$plantagazfield."` as missgen FROM `".$tbname."`  WHERE `".$plantagazfield."`<>'' AND (`".$plantagazfield."` IS NOT NULL) AND (`".$colname."`=0 OR `".$colname."` IS NULL) LIMIT 0,20";
					$rr = mysql_query($qq,$conn);
					$nres = @mysql_numrows($rr);
					//tem gaz para cadastrar
					if ($nres>0) {
						unset($_POST['gazetteerid']);
						echo "
<br />
    <table align='center' class='myformtable' cellpadding='5'>
  <thead>
    <tr><td colspan='100%'>Localidades de árvores não encontrados no Wiki</td>
    </tr>
    <tr class='subhead'>
    <td>Localidade</td>
    <td>Pode ser uma dessas</td>
    <td>Cadastre nova</td>
    </tr>
  </thead>
  <tbody>
    <form action='import-data-step2.php' method='post'>
      <input type='hidden' name='ispopup' value='".$ispopup."' /> ";
		foreach ($ppost as $kk => $vv) {
						if (!empty($vv)) {
							echo "
        <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}


					$i=1;
					while ($rw = mysql_fetch_assoc($rr)) {
						$gen = (trim($rw['missgen']));
						$gk = $rw['missgen'];
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$nc = strlen($gen);
						$n1 = floor($nc/2)-1;
						$n2 = ceil($nc/2)-1;
						$g1 = substr($gen,0,2);
						$g2 = substr($gen,$n2-2,$n2);
						//$qq = "SELECT GazetteerID,GazetteerTIPOtxt,Gazetteer,Municipio,Gazetteer.MunicipioID,Gazetteer.ParentID,Province,Municipio.ProvinceID,Province.CountryID FROM Gazetteer LEFT JOIN Municipio USING(MunicipioID) LEFT JOIN Province USING(ProvinceID) WHERE ((CONCAT(GazetteerTIPOtxt,' ',Gazetteer)) LIKE '".$g1."%' OR (CONCAT(GazetteerTIPOtxt,' ',Gazetteer)) LIKE '%".$g2."'";
						$qq = "SELECT GazetteerID,Gazetteer,Municipio,Gazetteer.MunicipioID,Gazetteer.ParentID,Province,Municipio.ProvinceID,Province.CountryID FROM Gazetteer LEFT JOIN Municipio USING(MunicipioID) LEFT JOIN Province USING(ProvinceID) WHERE ((Gazetteer) LIKE '".$g1."%' OR (Gazetteer) LIKE '%".$g2."'";
							if (count($gggen)>1) {
								$qqq ="";
								foreach ($gggen as $gg) {
									$gg = trim($gg);
									if (!empty($gg) && strlen($gg)>2) {
										$qqq .= " OR (Gazetteer) LIKE '%".$gg."%'";
//										$qqq .= " OR (CONCAT(GazetteerTIPOtxt,' ',Gazetteer)) LIKE '%".$gg."%'";

									}
								}
							}
						$qq .= $qqq; 
						if (!empty($localid)) {
							$qq .= ") AND Gazetteer.ParentID=".$localid;
						} else {
							$qq .=")";
						}
						$qq .= " ORDER BY Gazetteer";
//						$qq .= " ORDER BY GazetteerTIPOtxt,Gazetteer";

						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
        <tr>
          <td>".$gen."</td>
          <td><select id=\"gazetteer_".$i."\" name=\"gazetteerid[".$gk."]\">
            <option value=''>".GetLangVar('nameselect')."</option>";
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
										echo "
            <option value='".$row['GazetteerID']."'>".$row['Gazetteer']." [".$row['Municipio']." - ".$row['Province']."]</option>";
//            <option value='".$row['GazetteerID']."'>".$row['GazetteerTIPOtxt']." ".$row['Gazetteer']." [".$row['Municipio']." - ".$row['Province']."]</option>";

									}
								} else {
									echo "
            <option selected value=''>Não se parece com nada, cadastre novo!</option>";
								}
						echo "
          </select></td>
          <td align='center'><img style='cursor: pointer'  src='icons/list-add.png' height='15' ";
          
          //localidade-novapopup.php?gazetteer_val=gazetteer_1&gazetteer=Quadrante%201&municipioid=127&paisid=30&provinciaid=3&parentgazid=313052
          
							//$myurl = "localidade-novapopup.php?gazetteer_val=gazetteer_".$i."&gazetteer=".$gk."&municipioid=$munid&paisid=$coid&provinciaid=$provid&parentgazid=$parentgazid";
							$myurl = "localidade_dataexec.php?gazetteer_val=gazetteer_".$i."&gazetteer=".$gk."&municipioid=$munid&paisid=$coid&provinciaid=$provid&parentgazid=$parentgazid";
							echo " onclick = \"javascript:small_window('$myurl',500,350,'Nova Localidade');\" /></td></tr>";
						$i++;
				}
					echo "
            <tr><td colspan='100%'><table align='center'><tr>
            <td align='center'><input style='cursor: pointer' type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td>";
            //if (!empty($localid)) {
                //$qu = array($plantagazfield,$tbname,$colname);
                //$qu = serialize($qu);
            //$myurl = "localidade-novapopup_batch.php?plantagazfield=$plantagazfield&tbname=$tbname&colname=$colname&municipioid=$munid&paisid=$coid&provinciaid=$provid&parentgazid=$parentgazid&buttonidx=nowlocais";
            //echo "<td align='center'><input id='nowlocais' type='button'  value='Cadastrar todas como novas' class='bblue' onclick = \"javascript:small_window('$myurl',500,350,'Novas Localidades');\" /></td>";
            //} else {
              //          echo "
            //<td align='center' colspan='50%'>&nbsp;</td>";
            //}
            echo "
            </tr></table></td>
            
            
            </tr>
        </form>
    	</tbody>
		</table>";

		} 
		else {
			$plgaz=TRUE;
				}
			} 
			} 
		}
		if ($plgaz) {
				$cll = $tbprefix."PlantaID";
				$cl2 = $tbprefix."PlGazetteerID";
				$qq = "UPDATE `".$tbname."` as tb, Plantas as pl, Gazetteer as gaz set tb.`".$cll."`= pl.`PlantaID` WHERE tb.`".$tagnumfield."`= pl.`PlantaTag` AND pl.`GazetteerID`=tb.`".$cl2."`";
				$res = mysql_query($qq,$conn);
				if ($res) {
					$plidschecked=TRUE;
				}
			}
		}
	} elseif ($coletas!=1) { $plidschecked=TRUE;}
	if (($coletas==2 || $coletas==3) && !isset($clidschecked) && $plidschecked) {
		$erro=0;
		$cll = $tbprefix."EspecimenID";
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cll." INT(10) DEFAULT 0";
		@mysql_query($qq,$conn);
		//se as plantas ja tiverem cadastro no wiki
		if (!empty($specimenidfield)) {
			$qq = "UPDATE `".$tbname."` as tb, `Especimenes` as pl SET tb.`".$cll."`= pl.`EspecimenID` where tb.`".$specimenidfield."`= pl.`EspecimenID`";
			mysql_query($qq,$conn);
			//echo $qq."<br />";
			$qq = "SELECT tb.ImportID FROM `".$tbname."` as tb WHERE tb.`".$cll."`=0 OR (tb.`".$cll."` IS NULL)";
			//echo $qq."<br />";
			$res = mysql_query($qq,$conn);
			$nres = mysql_numrows($res);
			if ($nres>0) {
				$erro++;
echo "
<form action='import-data-form.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'><b>$nres</b> EspecimenID do wiki indicado por você não foram encontrados no banco de dados!</td></tr>
  <tr><td align='center'><input type='submit' value='".GetLangVar('namevoltar')."' class='bblue'></td></tr>
</table>
<br /> 
</form>";
			} else {
				$clidschecked=TRUE;
			}
		}
		if (!isset($clidschecked) && !empty($coletorfield) && !empty($numcolfield)) {
	  		if (!isset($colpessoas)) {
		$cln = $tbprefix."ColetorID";
		$cln2 = $tbprefix."Number";
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln." VARCHAR(100) DEFAULT ''";
		@mysql_query($qq,$conn);
		$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$cln2." CHAR(10) DEFAULT ''";
		@mysql_query($qq,$conn);
		$qq = "UPDATE ".$tbname." SET `".$cln2."`=TRIM(`".$numcolfield."`) where (`".$numcolfield."` IS NOT NULL) AND `".$numcolfield."`<>''";
		mysql_query($qq,$conn);
		$qq = "UPDATE ".$tbname." SET `".$cln."`=checkpessoas(`".$coletorfield."`) where (`".$coletorfield."` IS NOT NULL) AND `".$coletorfield."`<>''";
		mysql_query($qq,$conn);
		$qq = "SELECT DISTINCT `".$coletorfield."` FROM `".$tbname."`  WHERE `".$coletorfield."`<>'' AND (`".$coletorfield."` IS NOT NULL) AND `".$cln."`='ERRO'";
		$res = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		if ($nres>0) {
			echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>Sobre a coluna $coletorfield</td></tr>
  <tr class='subhead'>
    <td>Nome da coluna</td>
    <td>Problema encontrado</td>
    <td>O que fazer?</td>
  </tr>
</thead>
<tbody>
<tr bgcolor = '".$bgcolor."'>
    <td>".$coletorfield."</td>
    <td>".$nres." registros tem pessoas que não foram encontrados no wiki!</td>
    <td align='center'><input id='butidx' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em\"
 value='Corrigir' ";
$myurl ="novaspessoas-popup.php?colname=".$cln."&orgcol=".$coletorfield."&tbname=".$tbname."&buttonidx=butidx"; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Corrigir valores de nomes de pessoas');\" /></td></tr>
<form action='import-data-step2.php' method='post'>";
foreach ($_POST as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
		}
	}
echo "
  <input type='hidden' name='plidschecked' value='".$plidschecked."' />
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'>
    <input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' />
  </td></tr>
</form>
</tbody>
</table>";
		} else {
			$colpessoas =TRUE;
		}
	}
			if ($colpessoas) {
				$cll1 = $tbprefix."EspecimenID";
				$cll2 = $tbprefix."ColetorID";
				$qq = "UPDATE `".$tbname."` as tb, `Especimenes` as pl SET tb.`".$cll1."`= pl.`EspecimenID` where tb.`".$cll2."`= pl.ColetorID AND pl.`Number`=tb.`".$numcolfield."`";
				$res = mysql_query($qq,$conn);
				if ($res) {
					$clidschecked=TRUE;
				}
			}
		} 
	} elseif ($coletas!=2 && $coletas!=3) {
		$clidschecked=TRUE;
	}
	if ($clidschecked && $plidschecked) {
		$idschecked =TRUE;
	}
}
if ($idschecked) {
echo "
  <form name='myform' action='import-data-step2a.php' method='post'>";
  foreach ($_POST as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
		}
	}
echo"
<script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
   <!--- <input type='submit' value='Continuar' class='bsubmit' />--->
  </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>