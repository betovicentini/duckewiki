<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include_once("functions/class.Numerical.php") ;

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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Listar espécies';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$formula = trim($formula);
$search = array("(",")","/","+","*","-");
$replace = array("_","_","_","_","_","_");
$formu =  str_replace($search,$replace,$formula);
$formu =  str_replace("__","_",$formu);
$formu = explode("_",$formu);
$ff = array();
foreach ($formu as $vv) {
	$vv = trim($vv);
	$v = substr($vv,0,1);
	if ($v=="&") {
		$nv = strlen($vv)-2;
		$nvi = substr($vv,1,$nv);
		$ff[] = $nvi+0;
	}
}
$traitsinformula = array_unique($ff);

//unidade da variavel de armazenamento
$qq = "SELECT * FROM Traits WHERE TraitID='".$traitid."'";
$res = mysql_query($qq,$conn);
$rr = mysql_fetch_assoc($res);
$finalunit = $rr['TraitUnit'];
mysql_free_result($res);

//check traits units in general 
$tunits = array();
foreach ($traitsinformula as $tif) {
	$qq = "SELECT * FROM Traits WHERE TraitID=".$tif;
	$res = mysql_query($qq,$conn);
	$rr = mysql_fetch_assoc($res);
	$variation = $rr['TraitUnit'];
	$aa = array("id_".$tif => $variation);
	$tunits = array_merge((array)$tunits,(array)$aa);
}
mysql_free_result($res);
$az = array_values($tunits);
$az = array_unique($az);
if (count($az)>1) {
	echo "<p class='erro'>
	Variáveis na fórmula tem unidades de medida diferentes. Isso não foi considerado. Você pode incluir esse cálculo na fórmula se for o caso.</p>";
}

//get the data
if (!empty($filtro)) { 
	$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
	$res = mysql_query($qq);
	$rr = mysql_fetch_assoc($res);
	$especimenesids= $rr['EspecimenesIDS'];
	mysql_free_result($res);
}

//testa para ver se o campo está vazio
$isfilled=0;
if (!empty($especimenesids) && $traitid>0 && empty($tocontinue)) {
	$specarr = explode(";",$especimenesids);
	foreach ($specarr as $vv) {
		$spid = trim($vv)+0;
		$qq = "SELECT * FROM Traits_variation WHERE TraitID=".$traitid." AND EspecimenID='".$spid."'";
		$res = mysql_query($qq,$conn);
		$nres = mysql_numrows($res);
		if ($nres>0) {
			$isfilled++;
		}
	}
	if ($isfilled>0) {
		$nsp = count($specarr);
		$qq = "SELECT * FROM Traits WHERE TraitID=".$traitid;
		$res = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($res);
		$tt = $row['PathName'];
	echo "
<br /><table class='erro' width=60% align='center'>
<tr>
  <td colspan='100%'>
  <b>Atenção</b>: A variável <b>$tt</b> já contém informação para $isfilled amostras das $nsp existentes no filtro.
  Você tem certeza de que deseja armazenar o resultado calculado nessa variável?
  </td>
</tr>
<tr>
<td>
<form action='traits-calculate-exec.php' method='post'>
  <input type='hidden' name='traitid' value=".$traitid." />
  <input type='hidden' name='filtro' value=".$filtro." />
  <input type='hidden' name='formula' value=".$formula." />
  <input type='hidden' name='tocontinue' value='1' />
  <input type='submit' value='".GetLangVar('surecontinue')."' class='bsubmit' />
</form>
</td>
<td>
<form action='traits-calculate-form.php' method='post'>
  <input type='hidden' name='filtro' value=".$filtro." />
  <input type='hidden' name='formula' value=".$formula." />
  <input type='submit' value='".GetLangVar('nameselect')." ".mb_strtolower(GetLangVar('nameoutra'))."' class='bblue' />
</form>
</td>
</tr>
</table>";
	}

}


if (!empty($especimenesids) && $traitid>0 && count($traitsinformula)>0 && $isfilled==0) {
	$specarr = explode(";",$especimenesids);
	$nspecs = count($specarr);

	//echopre($specarr);
	$temerro=array();
	$ok=0;
	foreach ($specarr as $vv) {
		$spid = trim($vv)+0;
		//echo $spid."<br />";

		//getindividual values
		$ft = 0;
		$valarr = array();
		$localunit = array();
		foreach ($traitsinformula as $tif) {
			$qq = "SELECT * FROM Traits_variation WHERE TraitID=".$tif." AND EspecimenID='".$spid."'";
			$res = mysql_query($qq,$conn);
			$nres = mysql_numrows($res);
			if ($nres>0) {
				$ft++;
				$row = mysql_fetch_assoc($res);
				$variation = $row['TraitVariation'];
				$aa = array("id_".$tif => $variation);
				$valarr = array_merge((array)$valarr,(array)$aa);

				$luni = $row['TraitUnit'];
				$aa = array("id_".$tif => $luni);
				$localunit = array_merge((array)$localunit,(array)$aa);
			}
		}

		//da para fazer o calculo para a amostra?
		if (count($traitsinformula)==$ft) {

				//test whether individual values allow individual calculation
				$vrr = array();
				foreach ($traitsinformula as $tif) {
					$var = $valarr["id_".$tif];
					$vv = explode(";",$var);
					$aa = array("id_".$tif => count($vv));
					$vrr = array_merge((array)$vrr,(array)$aa);
				}
				$nvars = array_unique($vrr);
				$alow = count($nvars);
				$resul = array();

				//then alow individual calculations
				if ($alow==1) { 
					$t1 = array_values($valarr);
					$t1 = $t1[0];
					$tt = explode(";",$t1);
					$zt = count($tt);
					for ($i=0; $i<$zt;$i++) {
						$theform = $formula;
						foreach ($traitsinformula as $tif) {
							$var = $valarr["id_".$tif];
							$vv = explode(";",$var);
							$tidval = $vv[$i];
							$uni = $localunit["id_".$tif];
							$qq = "SELECT * FROM Traits WHERE TraitID=".$tif;
							$rse = mysql_query($qq,$conn);
							$rss = mysql_fetch_assoc($rse);
							$defuni = $rss['TraitUnit'];
							if ($finalunit=='metros' || $finalunit=='mm' || $finalunit=='cm') {
								$defuni = $finalunit;
							}
							if ($uni!=$defuni) {
								if ($defuni=='metros') {
									if ($uni=='cm') {
											$tdv = $tidval/100;
									} elseif ($uni=='mm') {
											$tdv = $tidval/1000;
									}
								}
								if ($defuni=='cm') {
									if ($uni=='metros') {
											$tdv = $tidval*100;
									} elseif ($uni=='mm') {
											$tdv = $tidval/10;
									}
								}
								if ($defuni=='mm') {
									if ($uni=='metros') {
											$tdv = $tidval*1000;
									} elseif ($uni=='cm') {
											$tdv = $tidval*10;
									}
								}
							} else {
								$tdv = $tidval;
							}
							///////
							$tifcode = "&".$tif."&";
							$theform = str_replace($tifcode,$tdv,$theform);
						}
						$oldf = $theform;
						@eval("\$theform = $theform;");
						//echo $spid." ".$theform." ".$oldf."<br />";
						if ($oldf!=$theform) {
							$theform = round($theform,6);
							$aa = array($theform);
							$resul = array_merge((array)$resul,(array)$aa);
						} else {
							$temerro = array_merge((array)$temerro,(array)$spid);
						}
					}
				} else { //calculate only a mean value
					//echo "do not aloow<br />";
					$theform = $formula;
					foreach ($traitsinformula as $tif) {
							$var = $valarr["id_".$tif];
							$vv = explode(";",$var);
							$mean = Numerical::mean($vv);

							$uni = $localunit["id_".$tif];
							$qq = "SELECT * FROM Traits WHERE TraitID=".$tif;
							$rse = mysql_query($qq,$conn);
							$rss = mysql_fetch_assoc($rse);
							$defuni = $rss['TraitUnit'];
							if ($finalunit=='metros' || $finalunit=='mm' || $finalunit=='cm') {
								$defuni = $finalunit;
							}
							if ($uni!=$defuni) {
								if ($defuni=='metros') {
									if ($uni=='cm') {
											$tdv = $tidval/100;
									} elseif ($uni=='mm') {
											$tdv = $tidval/1000;
									}
								}
								if ($defuni=='cm') {
									if ($uni=='metros') {
											$tdv = $tidval*100;
									} elseif ($uni=='mm') {
											$tdv = $tidval/10;
									}
								}
								if ($defuni=='mm') {
									if ($uni=='metros') {
											$tdv = $tidval*1000;
									} elseif ($uni=='cm') {
											$tdv = $tidval*10;
									}
								}
							} else {
								$tdv = $tidval;
							}
							$tifcode = "&".$tif."&";
							$theform = str_replace($tifcode,$mean,$theform);
					}
					$oldf = $theform;
					//echo $oldf;
					@eval("\$theform = $theform;");
					if ($oldf!=$theform) {
						$theform = round($theform,6);
						$aa = array($theform);
						$resul = array_merge((array)$resul,(array)$aa);
					} else {
						$temerro = array_merge((array)$temerro,(array)$spid);
					}
				}
				if (count($resul)>0) {
					//salva os resultados na nova variavel
					$variation = implode(";",$resul);
					$qq = "SELECT * FROM Traits_variation WHERE TraitID='".$traitid."' AND EspecimenID='".$spid."'";
					$teste = mysql_query($qq,$conn);
					$update = @mysql_numrows($teste);
					$fieldsaskeyofvaluearray= array('TraitID' => $traitid, 'TraitVariation' => $variation, 'TraitUnit' => $tunit, 'EspecimenID' => $spid);
					//echopre($fieldsaskeyofvaluearray);
					if ($update>0) {
						$oldr = mysql_fetch_assoc($teste);
						$oldid = $oldr['TraitVariationID'];
						CreateorUpdateTableofChanges($oldid,'TraitVariationID','Traits_variation',$conn);
						$newupdate = UpdateTable($oldid,$fieldsaskeyofvaluearray,'TraitVariationID','Traits_variation',$conn);
						if ($newupdate) {
							$ok++;
						}
					} else {
						$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,'TraitVariationID','Traits_variation',$conn);
						if ($newtrait) {
							$ok++;
						}
					}
				}

		}
	}
	$temerro = array_unique($temerro);
	if ($ok>0) {
		echo "
<br />
<table class='success' width=60% align='center'>
  <tr><td>$ok registros foram calculados com sucesso!</td></tr>
</table>";
	}
	if (count($temerro)>0) {
		$tt = count($temerro);
		echo "
<br />
<table class='erro' width=60% align='center'>
  <tr>
    <td colspan='100%'>Houve erro para $tt dos $nspecs registros existentes no filtro.<br />O erro pode ser porque há valores estranhos nas variáveis da fórmula para essas amostras, ou  a fórmula está mal escrita.<br /> Checar a variação individualmente e depois calcular novamente a nova variável ou simplesmente verificar a formula.</td>
  </tr>";
			$especimenesids = implode(";",$temerro);
			$basicvariables = implode(";",array("taxajustname"));
			$traitids = implode(";",$traitsinformula);
			echo "
<tr>
<td>
<form action='edit-batch-exec.php' method='post'>
  <input type='hidden' name='especimenesids' value=".$especimenesids." />
  <input type='hidden' name='basicvariables' value=".$basicvariables." />
  <input type='hidden' name='traitids' value=".$traitids." />
  <input type='submit' value='".GetLangVar('nameeditar')." ".mb_strtolower(GetLangVar('namedata'))."' class='bsubmit' />
</form>
</td>
<td>
<form action='traits-calculate-form.php' method='post'>
  <input type='hidden' name='filtro' value=".$filtro." />
  <input type='hidden' name='formula' value=".$formula." />
  <input type='submit' value='".GetLangVar('nameeditar')." ".mb_strtolower(GetLangVar('nameformula'))."' class='bblue' />
</form>
</td>
</tr>
</table>";
		}
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
