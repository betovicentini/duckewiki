<?php
//Start session
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
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);


if(isset($expeditoid) && $expeditoid=='criar') {
	header("location: novoptratter-form.php");
	exit();
} 
if (empty($expeditoid)) {
	header("location: expedito-form.php");
	exit();
}

HTMLheaders($body);

$qq = "SELECT exp.PessoasIDs,exp.DataColeta,gps.Name,gaz.PathName FROM MetodoExpedito as exp LEFT JOIN GPS_DATA as gps ON exp.GPSpointID=gps.PointID LEFT JOIN Gazetteer as gaz ON gps.GazetteerID=gaz.GazetteerID WHERE exp.ExpeditoID='".$expeditoid."'";
$res = @mysql_query($qq,$conn);
$row = mysql_fetch_assoc($res);
$pessoasids = explode(";",$row['PessoasIDs']);
$datacol = $row['DataColeta'];
$gpsname = $row['Name'];
$gazname = $row['PathName'];
$titulo = "Levantamento Expedito do Ponto <b>".strtoupper($gpsname)."</b> (".$gazname.") - ".$datacol;



$errosarr = array();
if ($final==1) { //se enviado para salvar
	$qq = "CREATE TABLE IF NOT EXISTS MetodoExpeditoPlantas (
				PlantaExpID INT(10) unsigned NOT NULL auto_increment,
				ExpeditoID INT(10),
				EspecimenIDs INT(10),
				TaxonomiaIDs VARCHAR(100),
				PessoasIDs VARCHAR(200),
				IntervaloTempo INT(2),
				AddedBy INT(10),
				AddedDate DATE,
				PRIMARY KEY (PlantaExpID))";

	mysql_query($qq,$conn);
	
	$sucesso=0;
	$atualizados=0;
	$semmudanca=0;
	
	$erro=0;
	$errosarr = array();
	foreach ($linhas as $ii) {
			$tmpid = trim($tempoid[$ii]);
			$specid = $especimenids[$ii]+0;
			$oldid = $editando[$ii]+0;
			$tz = "nomesciid_".$ii;
			$taxid = trim($$tz);
			$peopleid = array();
			foreach ($pessoasids as $val) {
				$key = "k_".$ii;
				$pess = "pessoasaw_".$val;
				$pessoa = $$pess;
				$pess = trim($pessoa[$key]);
				if ($pess=='on') {
					$peo = array($val);
					$peopleid = array_merge((array)$peopleid,(array)$peo);
				} 
			}
			if (empty($tmpid) || ($specid==0 && empty($taxid)) || count($peopleid)==0) {
				if (!empty($tmpid) || $specid>0 || !empty($taxid) || count($peopleid)>0) {
					$erro++;
					$errosarr["k_".$ii] = 1;
				}
			}
	}
	//se nao faltam dados entao cadastrar valores
	if (count($errosarr)==0) {
		foreach ($linhas as $ii) {
			$tmpid = trim($tempoid[$ii]);
			$specid = $especimenids[$ii]+0;
			$oldid = $editando[$ii]+0;
			$tz = "nomesciid_".$ii;
			$taxid = trim($$tz);
			$peopleid = array();
			foreach ($pessoasids as $val) {
				$key = "k_".$ii;
				$pess = "pessoasaw_".$val;
				$pessoa = $$pess;
				$pess = trim($pessoa[$key]);
				if ($pess=='on') {
					$peo = array($val);
					$peopleid = array_merge((array)$peopleid,(array)$peo);
				} 
			}
			if (!empty($tmpid) && ($specid>0 || !empty($taxid)) && count($peopleid)>0) {
				$coletores = implode(";",$peopleid);
				$arrayofvalues = array(
							'ExpeditoID' => $expeditoid,
							'EspecimenIDs' => $specid,
							'TaxonomiaIDs' => $taxid,
							'IntervaloTempo' => $tmpid,
							'PessoasIDs' => $coletores);
				//entao editando
				if ($oldid>0) {
					$idd = $editando[$ii]+0;
					$upp = CompareOldWithNewValues('MetodoExpeditoPlantas','PlantaExpID',$idd,$arrayofvalues,$conn);
					if (!empty($upp) && $upp>0) {
						CreateorUpdateTableofChanges($idd,'PlantaExpID','MetodoExpeditoPlantas',$conn);
						$newplanta = UpdateTable($idd,$arrayofvalues,'PlantaExpID','MetodoExpeditoPlantas',$conn);
						if (!$newplanta) {
							$erro++;
						} else {
							$atualizados++;
						}
					} else {
							$semmudanca++;
					}
				} else { //entao inserindo
					$newplanta = InsertIntoTable($arrayofvalues,'PlantaExpID','MetodoExpeditoPlantas',$conn);
					if (!$newplanta) {
						$erro++;
					} else {
						$sucesso++;
					}
				}
			}
		$ii++;
		}
	}
	$nlinhas=$ns;
	if ($sucesso>0) {
		echo "
<br>
<table class='success' align='center' width=60%>
  <tr><td colspan='100%'>$sucesso registros novos foram cadastrados.</td><tr>
</table>
<br>";
	}
	if ($atualizados>0) {
		echo "
<table class='success' align='center' width=60%>
  <tr><td>$atualizados registros foram atualizados.</td><tr>
</table>
<br>";
	}
	if ($semmudanca>0) {
		echo "
<table class='erro' align='center' width=60%>
<tr><td>$semmudanca registros existentes n&atilde;o foram alterados.</td><tr>
</table>
<br>";
	}
} 

if (empty($final) && empty($ns)) {
	$qq = "SELECT * FROM MetodoExpeditoPlantas WHERE ExpeditoID=".$expeditoid;
	@$res = mysql_query($qq,$conn);
	@$nres = mysql_numrows($res);
	if ($nres>0) {  //se ja existem registros
		$nlinhas = $nres;
		$tempoid = array();
		$especimenids = array();
		$taxonomias = array();
		$editando = array();
		foreach ($pessoasids as $val) {
			$vv = trim($val);
			$pess = "pessoasaw_".$vv;
			$$pess = array();
			
		}
		$j=0;
		while ($rw = mysql_fetch_assoc($res)) {
			$tempoid[$j] = $rw['IntervaloTempo'];
			$especimenids[$j] = $rw['EspecimenIDs'];
			$peopleid = explode(";",$rw['PessoasIDs']);
			$taxonomias[$j] = $rw['TaxonomiaIDs'];
			$editando[$j] = $rw['PlantaExpID'];
			foreach ($peopleid as $val) {
					$key = "k_".$j;
					$vv = trim($val);
					$pess = "pessoasaw_".$val;
					$arr = array($key => 'on');
					$$pess = array_merge((array)$$pess,(array)$arr);
			}
			$j++;
		}
	}
} 

if (empty($ns) && !isset($nlinhas)) { $nlinhas=5;} 
if ($final==2) {
	$nlinhas=$ns+10;
}

if ($final!=1 || count($errosarr)>0) {
echo "<br>
<form name='coletaform' action='expedito-exec.php' method='post'>
<table class='myformtable' cellpadding='10' align='center'>
<thead>
  <tr class='subhead'><td colspan='100%' >$titulo</td></tr>
</thead>
<tbody>
  <tr>
    <td colspan='100%' >
      <table class='myformtable' cellspacing='0' cellpadding='7' align='center' colspan='90%'>
        <thead >
        <tr>
          <td align='center'>Intervalo de Tempo</td>
          <td align='center'>".GetLangVar('nametestemunho')."</td>
          <td align='center'>".GetLangVar('nametaxonomy')."*</td>
          ";
          
			foreach ($pessoasids as $vv) {
				$vv = $vv+0;
				$rrr = getpessoa($vv,$abb=FALSE,$conn);
				$row = mysql_fetch_assoc($rrr);
				//$un = substr($row['Prenome'],0,1);
				$un = $row['Prenome'];
				$dois = substr($row['SegundoNome'],0,1);
				$tres = substr($row['Sobrenome'],0,1);	
				$abr = $un.$dois.$tres;
				echo "
          <td align='center'>".$abr."</td>";
			}
		if (count($errosarr)>0) {
			echo "
          <td align='center'>&nbsp;</td>";
        }
			echo "
        </tr>
        </thead>
      <tbody>";
for ($i=0;$i<$nlinhas;$i++) {
	$errtotag = $errosarr["k_".$i]+0;
	echo "<input type=hidden name='linhas[]' value='".$i."'>
	<input type=hidden name='editando[]' value='".$editando[$i]."'>";
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
	echo "
          <tr bgcolor = $bgcolor>
            <td align='center'>
              <select name='tempoid[]'>
";

	if (!empty($tempoid[$i])) {
		$tid = $tempoid[$i];
		echo "
              <option selected value='".$tid."'>Tempo_".$tid."</option>";
    } else {
		echo "
              <option value=''>".GetLangVar('nameselect')."</option>";
    }
		echo "
              <option  value='1'>Tempo_1</option>
              <option  value='2'>Tempo_2</option>
              <option  value='3'>Tempo_3</option>
              <option  value='4'>Tempo_4</option>
            </select>
          </td>";
	echo "
          <td align='center'>
            <select name='especimenids[]'>
";
if (!empty($especimenids[$i])) {
	$spid = $especimenids[$i];
	$qq = "SELECT EspecimenID,specimen_optiontag(ColetorID,DetID,Number) as opt FROM Especimenes JOIN Pessoas ON ColetorID=PessoaID WHERE EspecimenID=".$spid;
	$res = mysql_query($qq,$conn);
	$rw = mysql_fetch_assoc($res);
	$nometxt = $rw['opt'];
	echo "
              <option selected value='".$rw['EspecimenID']."'>".$rw['opt']."</option>";
} else {
	echo "
              <option value=''>".GetLangVar('nameselect')."</option>";
}


//para mostras apenas amostras de datas proximas a da coleta (15 dias)
$h =  strtotime($datacol);
$convert = strtotime("-15 days", $h);
$datainicial = date('Y-m-d', $convert);

$h =  strtotime($datacol);
$convert = strtotime("+15 days", $h);
$datafinal = date('Y-m-d', $convert);

$qqq = "SELECT EspecimenID,specimen_optiontag(ColetorID,DetID,Number) as opt FROM Especimenes WHERE 
date_format(str_to_date(CONCAT(Ano,'-',Mes,'-',Day),'%Y-%m-%d'), '%Y-%m-%d')>'".$datainicial."' AND date_format(str_to_date(CONCAT(Ano,'-',Mes,'-',Day),'%Y-%m-%d'), '%Y-%m-%d')<='".$datafinal."' ORDER BY date_format(str_to_date(CONCAT(Ano,'-',Mes,'-',Day),'%Y-%m-%d'), '%Y-%m-%d'),opt ASC";

$rr = mysql_query($qqq,$conn);
while ($row = mysql_fetch_assoc($rr)) {
	echo "
              <option value='".$row['EspecimenID']."'>".$row['opt']." ".$row['Number']."</option>";
}
echo "
            </select>
          </td>";
if (empty($especimenids[$i])) {
		$zz  = "nomesciid_".$i;
		$nomesci ='';
		if (isset($taxonomias[$i])) {
			$zz  = "nomesci_".$i;
			$nomesci = strip_tags(gettaxatxt($taxonomias[$i],$conn));
			$zz  = "nomesciid_".$i;
			$$zz = $taxonomias[$i];
		} elseif (!empty($$zz)) {
			$zzz  = "nomesci_".$i;
			$nomesci = strip_tags(gettaxatxt($$zz,$conn));
		}
		$nsci  = "nomesciid_".$i;
 		echo "<td style='border: 0px'>"; 
					autosuggestfieldval2("search-name-simple.php","nomesci_".$i, $nomesci,"nomeres_".$i,"nomesciid_".$i,$$nsci,true); 
		echo "</td>";          
} else {
 		echo "<td class='tdformnotes'>$nometxt</td>";          
}
          
foreach ($pessoasids as $vv) {
	$key = "k_".$i;
	$pess = "pessoasaw_".$vv;
	$pessoa = $$pess;
	$pess = $pessoa[$key];
	echo "
          <td align='center'>
            <input type='checkbox' ";
			if ($pess=='on') {
					echo "checked";
			}
			echo " name='pessoasaw_".$vv."[$key]' >
          </td>";
}

if ($errtotag==1) {
echo "
          <td align='center'>ERRO!</td>";
}


echo "
        </tr>";
}

echo "</form>

        </table>
      </td>
    </tr>
    <tr>
      <input type='hidden' name='ns' value='$nlinhas'>
      <input type='hidden' name='expeditoid' value='$expeditoid'>
      <input type='hidden' name='final' value=''>
      <td align='center' colspan='50%'>
        <input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' onclick=\"javascript:document.coletaform.final.value=1\">
      </td>
      <td align='center' colspan='50%'>
        <input type='submit' value='Adicionar linhas' class='bblue' onclick=\"javascript:document.coletaform.final.value=2\">
      </td>
    </tr>
    <tr><td colspan='100%' class='tdformnotes'>*Apenas se não houver material testemunho</td>
    </tr>
  </tbody>
</table>
";

}

HTMLtrailers();

?>