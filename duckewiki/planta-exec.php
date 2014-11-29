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
if (!empty($_POST['detset'])) {
	$detset = $_POST['detset'];
	unset($_POST['detset']);
}
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
if (!empty($_GET['detset'])) {
	$detset = $_GET['detset'];
	unset($_GET['detset']);
}
$gget = cleangetpost($_GET,$conn);
@extract($gget);

$body='';

if (!empty($plantatag) && (empty($plantaid) || $plantaid=='plantaid')) {
	$qq = "SELECT PlantaID FROM Plantas WHERE PlantaTag+0=".$plantatag."";
	$rq = mysql_query($qq,$conn);
	$nrq = mysql_numrows($rq);
	if ($nrq!=1) {
		header("location: planta-form.php");
	} else {
		$rqw = mysql_fetch_assoc($rq);
		$plantaid = $rqw['PlantaID'];
	}
}

//coordenadas
if (!isset($coord)) {
	$coord = @coordinates('','',$latgrad,$longgrad,$latminu,$longminu,$latsec,$longsec,$latnors,$longwore);
	@extract($coord);
}

//se for edicao extrair info antiga
if ($submeteu=='editando') {
	unset($_SESSION['oldspecids']);
	unset($_SESSION['variation']);
	$_SESSION['editando']=1;
	//if (!empty($plantaid)) {
	$qq = "SELECT * FROM Plantas WHERE PlantaID='$plantaid'";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);

	//determinacao antiga
	$detid = $row['DetID']+0;

	$detid = $row['DetID']+0;
	$xyref = $row['Referencia'];
	$plpos_dist = $row['Distancia']+0;
	$plpos_angle = $row['Angulo']+0;
	$plpos_x = $row['X']+0;
	$plpos_y = $row['Y']+0;

	$dettaxa = getdet($detid,$conn);
	$detnome = $dettaxa[0];
	$detdetby = trim($dettaxa[1]);
	$familia = strtoupper(trim($dettaxa[2]));
	$dettext = $familia."  ".$detnome;
	if (!empty($detdetby)) { $dettext =$dettext." <br><b>Det</b>: ".$detdetby;}


	$nosample = $row['NoSample'];

	$plantnum = $row['PlantaTag'];
	$latdec = $row['Latitude'];
	$longdec = $row['Longitude'];
	$coord = @coordinates($latdec,$longdec,'','','','','','','','');
	@extract($coord);
	$altitude = $row['Altitude'];

	$procedenciaid = $row['ProcedenciaID'];
	$procedenciagps = $row['ProcGPSID'];

	//echo $procedenciagps." aqui veja voce";
/// jb manaus
	$inexsitu = $row['InSituExSitu'];

///
	$qq = "SELECT EspecimenID FROM Especimenes WHERE PlantaID='$plantaid'";
	$rss = mysql_query($qq,$conn);
	while ($rww = @mysql_fetch_assoc($rss)) {
			if (empty($especimensids)) { $especimensids = $rww['EspecimenID'];} else {
			$especimensids = $especimensids.";".$rww['EspecimenID'];}
	}
	if (empty($especimensids)) {$nosample='no';} else {
		$_SESSION['oldspecids']=$especimensids;
	}

	$gazetteerid = $row['GazetteerID'];
	$gpspointid = $row['GPSPointID'];
	$habitatid = $row['HabitatID'];

	$tempids ='';
	$oldvals = storeoriginaldatatopost($plantaid,'PlantaID',$formid,$conn,$tempids);
	$traitarray = $oldvals;
	$datacol = $row['TaggedDate'];
	$addcolvalue = $row['TaggedBy'];
	
	$qu = "SELECT monitoramentostring(".$plantaid.",0,1,1) as monidesc";
	$rs = @mysql_query($qu,$conn);
	if ($rs) {
		$rw = @mysql_fetch_assoc($rs);
		$monidesc = $rw['monidesc'];
	}
	$_SESSION['variation'] = serialize($oldvals);
}

	if (empty($addcoltxt) && !empty($addcolvalue)) {
	$addcolarr = explode(";",$addcolvalue);
	$addcoltxt = '';
	$j=1;
	foreach ($addcolarr as $kk => $val) {
		$qq = "SELECT * FROM Pessoas WHERE PessoaID='$val'";
		$res = mysql_query($qq,$conn);
		$row = mysql_fetch_assoc($res);
		if ($j==1) {
			$addcoltxt = 	$row['Abreviacao'];
		} else {
			$addcoltxt = $addcoltxt."; ".$row['Abreviacao'];
		}
		$j++;
	}
	}


	$vernacularvalue = $row['VernacularIDS'];
	$vernarr = explode(";",$vernacularvalue);
	$vernaculartxt = '';
	$j=1;
	foreach ($vernarr as $kk => $val) {
		$qq = "SELECT * FROM Vernacular WHERE VernacularID='$val'";
		$res = mysql_query($qq,$conn);
		$rrw = mysql_fetch_assoc($res);
		if ($j==1) {
			$vernaculartxt = 	$rrw['Vernacular'];
			if (!empty($rrw['Language'])) { $vernaculartxt=$vernaculartxt." (".$rrw['Language'].")";}
		} else {
			if (!empty($rrw['Language'])) { $vtxt= $rrw['Vernacular']." (".$rrw['Language'].")";} else {$vtxt=$rrw['Vernacular'];}
			$vernaculartxt = $vernaculartxt."; ".$vtxt;
		}
		$j++;
	}

//se for indicado coletas entao, tem amostra!
	if ($nosample=='no' && !empty($especimensids)) {
		unset($nosample);
	}
//

HTMLheaders($body);

/////////////////////////////////
/////////////////////////////////
//////inicio do cadastro/////////
/////////////////////////////////
/////////////////////////////////
$erro =0;
if (($final>0 && $nosample=='no') || ($final>0 && !empty($especimensids))) {

	//checa se ja existe uma arvore com esse numero para essa localidade
	if (!isset($plantaid) || empty($plantaid)) {
		$qq = "SELECT * FROM Plantas WHERE PlantaTag='$plantnum'";
		if (empty($gazetteerid) && !empty($gpspointid)) { 
			$qq= $qq." AND GazetteerID='$gaztteerid'";
		} elseif (!empty($gpspointid)) {
			$qq= $qq." AND GPSPointID='$gpspointid'";
		}
		$res = mysql_query($qq,$conn);
		$nres = @mysql_numrows($res);
		if ($nres>0) {
			echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro3')."</td></tr>
</table>
<br>
";
				$erro++;
		} 
	}

	if ($nosample=='no') {
		if ($gpspointid>0 && $gazetteerid>0 ) { $gazetteerid=0;} //prioriza a coordenada do ponto de GPS

		//checa por campos obrigatorios
		if (empty($plantnum)) {
		echo "
<br>
  <table cellpadding=\"1\" width='50%' align='center' class='erro'>
    <tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>";
			if (empty($datacol)) {
				echo "
    <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namedata')."</td></tr>";
			}
			if (empty($plantnum)) {
				echo "
    <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namenumber')."</td></tr>";
			}
			if (empty($addcolvalue)) {
				echo "
   <tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('nametaggedby')."</td></tr>";
			}
			echo "
  </table>
<br>";
			$erro++;
		} 

		//localidade
		if (empty($gazetteerid) && empty($gpspointid)) {
			echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro1')."</td></tr>
  <tr><td class='tdsmallnotes' align='center'>".GetLangVar('namelocalidade')." ".GetLangVar('nameor')." ".GetLangVar('namecoordenadas')."</td></tr>
</table>
";
			$erro++;
		}

		//checa se coordenadas estao ok
		if (!empty($latdec) && !empty($longdec) && (abs($latdec)>90 || abs($longdec)>180)) {
			echo "
<table align='center' class='erro'>
  <tr><td>".GetLangVar('namelatitude')." > 90 || ".GetLangVar('namelongitude')." > 180</td></tr>
</table>";
			$erro++;
		}
		$changedtraits=0;
		//faz o cadastro das variaveis se houver
		if (!empty($_SESSION['variation'])) {
			if ($_SESSION['editando']==1) {
				$tempids = '';
				$oldtraitids = 	storeoriginaldatatopost($plantaid,'PlantaID',$formid,$conn,$tempids);
				$newtraitids = unserialize($_SESSION['variation']);
				//compare arrays
				foreach ($newtraitids as $key => $val) {
					$oldval = trim($oldtraitids[$key]);
					$vv = trim($val);
					if ($vv!='imagem' && $vv!='none' && !empty($vv) && ($vv!=$oldval || empty($oldval))) {
						$changedtraits++;
					}
				}
				if ($changedtraits==0 && !empty($_SESSION['variation'])) {
						$changedtraits++;
				}
			}
		}//se empty $traitids
		//se os campos obrigatorios estao ok e se a arvore nao existe
		//cria registro novo e obtem plantaid
		if ($procedenciagps>0 && $procedenciaid>0 ) { $procedenciaid=0;}

	} else {
		$procedenciaid=0;
		$procedenciagps=0;
		$vernacularvalue=' ';
		$latdec=' ';
		$longdec=' ';
		$altitude=' ';
		$gazetteerid=0;
		$gpspointid=0;
		$habitatid=0;
		$nosample=' ';
	}//end if $nosample=='no'

	if ($erro==0) {
		$arrayofvalues = array(
			'PlantaTag' => $plantnum,
			'VernacularIDS' => $vernacularvalue,
			'Latitude' => $latdec,
			'Longitude' => $longdec,
			'Altitude' => $altitude,
			'GazetteerID' => $gazetteerid,
			'GPSPointID' => $gpspointid,
			'ProcedenciaID' => $procedenciaid,
			'ProcGPSID' => $procedenciagps,
			'InSituExSitu' => $inexsitu,
			'HabitatID' => $habitatid,
			'NoSample' => $nosample,
			'TaggedBy' => $addcolvalue,
			'TaggedDate' => $datacol,
			'X' => $plpos_x,
			'Y' => $plpos_y,
			'Distancia' => $plpos_dist,
			'Referencia' => $xyref,
			'Angulo' => $plpos_angle
			);

			$updated =0;
			//echopre($arrayofvalues);
			if (empty($plantaid) && empty($_SESSION['editando'])) { //se nao editando insere valores novos

				$newspec = InsertIntoTable($arrayofvalues,'PlantaID','Plantas',$conn);
				if (!$newspec) {
					echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')." 1</td></tr>
</table>
<br>
";
					$erro++;
				} 
			} else { //caso contrario faz um update dos valores
				$upp = CompareOldWithNewValues('Plantas','PlantaID',$plantaid,$arrayofvalues,$conn);
				if (!empty($upp) && $upp>0) { //if new values differ from old, then update
					$updated++;
					CreateorUpdateTableofChanges($plantaid,'PlantaID','Plantas',$conn);
					$updatespecid = UpdateTable($plantaid,$arrayofvalues,'PlantaID','Plantas',$conn);
					if (!$updatespecid) {
						$erro++;
					} else {
	
					}
	
				} 
			}
	}
	//

	$er=0;
	//cadastro da identificacao
	if ($erro==0 && $nosample=='no') { 
		//seleciona a identidade antiga e indica o que deve ser feito
		if ($_SESSION['editando'] && $nosample=='no') { //editando
			$qq = "SELECT Identidade.* FROM Plantas JOIN Identidade USING(DetID) WHERE PlantaID='$plantaid'";
			$res = mysql_query($qq,$conn);
			$row = mysql_fetch_assoc($res);
			$olddetid = $row['DetID'];

			if (!empty($detset)) {
				$arrayofvalues = unserialize($detset);
				$detchanged = CompareOldWithNewValues('Identidade','DetID',$olddetid,$arrayofvalues,$conn);
			}
			if ($detchanged==0 || empty($detchanged)) { //se for identifico nesse campos nao faz nada
				$detchange = 'naomudou';
			} else {
				$detchange = 'mudou';
			}
		}
		//se mudou ou se e nova, insere nova determinacao
		if (empty($detchange) || $detchange=='mudou') {
			$arrayofvalues = unserialize($detset);
			$newdetid = InsertIntoTable($arrayofvalues,'DetID','Identidade',$conn);
			if (!$newdetid) {
				$er++;
				echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
</table>
<br>";
			} 
		} 
	} //se erro==0

   		//echo $plantaid." = aqui";

	if (!empty($plantaid) && $detchange=='mudou' && (empty($upp) || $upp==0)) {
		CreateorUpdateTableofChanges($plantaid,'PlantaID','Plantas',$conn);
	} elseif (empty($plantaid)) {
		$plantaid=$newspec;
	}

	//update identification
	if ($er==0 && !empty($newdetid)) { 
		$arrayofvalues = array('DetID' => $newdetid);
		$newupdate = UpdateTable($plantaid,$arrayofvalues,'PlantaID','Plantas',$conn);
		if (!$newupdate) {
			$erro++;
			echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')." 4</td></tr>
</table>
<br>";
		} else {
			$updated++;
		}
	}

	//faz o cadastro das variaveis se houver
	if ((($_SESSION['editando']==1 && $changedtraits>0 && !empty($_SESSION['variation'])) || 
   		(empty($_SESSION['editando']) && !empty($_SESSION['variation']))) && $erro==0 && $nosample=='no') {
   			$traitarray = unserialize($_SESSION['variation']);
   		if (count($traitarray)>0) {
			$resultado = updatetraits($traitarray,$plantaid,'PlantaID',$conn);
			if (!$resultado) {
				$erro++;
				echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')." 5</td></tr>
</table>
<br>";
			} else {
				$updated++;
			}
		}
	}//se empty $traitids
	$ppspecid=0;
	if (!empty($especimensids)) {
		$ids = explode(";",$especimensids);
		foreach($ids as $specid) {
			$qq = "UPDATE Especimenes SET PlantaID='$plantaid' WHERE EspecimenID='$specid'";
			$tt = mysql_query($qq,$conn);
			if (!$tt) { $ppspecid++;}
		}
	}
	if ($erro==0) {
		if ($_SESSION['editando']==1 && empty($updated) && $detchange=='naomudou' && $final!=2) {
				echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('messagenochange')."</td></tr>
</table>
<br>";
		} elseif ((empty($_SESSION['editando']) || $updated>0 || $detchange='mudou') && $final!=2) {
			echo "
<br>
<form action=planta-form.php method='post'>
<table cellpadding=\"5\" align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('sucesso1')."</td></tr>
  <tr><td align='center'><input type='submit' value='".GetLangVar('nameconcluir')."' class='bsubmit'></td></tr>
</table>
<br>
</form>";
		}
		if ($ppspecid>0) {
			echo "
<br>
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'><b>$ppspecid</b> amostras j&aacute; pertencem a outra planta marcada.</td></tr>
</table>
<br>";
				$erro++;
		}
	} //se erro==0



//limpa variaveis da memoria
if ($final==2 && $erro==0) {
 	unset($altitude,   $altmax,  $altmin,  $latgrad,  $latminu,  $latnors,  $latsec,  $longgrad,  $longminu,  $longsec,  $longwore,  $plantaid, $plantnum, $dettext, $detset, $traitids,  $arrayofvars,  $cooraction,  $localaction,  $habaction,  $detaction,  $especimensids,  $especimenstxt,  $plantnum);
 	unset($_SESSION['variation']);
	unset($_SESSION['editando']);
	unset($_SESSION['oldspecids']);
	$submeteu='nova';
} elseif ($erro==0) {
	unset($detdoubt, $nosample, $addcoltxt, $gpspointid, $addcolvalue,  $altitude,  $altmax,  $altmin,  $colnum,  $datacol,  $datadet,  $determinadorid,  $famid,  $gazetteerid,  $genusid,  $habitatid,  $infraspid,  $latgrad,  $latminu,  $latnors,  $latsec,  $longgrad,  $longminu,  $longsec,  $longwore,  $plantaid,  $speciesid,  $traitids,  $arrayofvars,  $cooraction,  $localaction,  $habaction,  $detaction,  $especimensids,  $especimenstxt,  $plantnum,  $inexsitu);
	unset($_SESSION['variation']);
	unset($_SESSION['editando']);
	unset($_SESSION['oldspecids']);
}

} 

/////////////////////////////////////////
/////////////////////////////////////////
///////////termina o cadastro////////////
/////////////////////////////////////////
/////////////////////////////////////////
if (!isset($final) || $final==2) {

if ($submeteu=='nova') {
	unset($_SESSION['variation']);
	unset($_SESSION['editando']);
	unset($_SESSION['oldspecids']);
}


//neste caso apagou referencia a amostras coletadas
if (!empty($_SESSION['oldspecids']) && empty($especimensids) && $_SESSION['editando']==1) { $nosample='no'; }

//variaveis dos formularios
$arrayofvars = array("nosample" => $nosample, "detdoubt" => $detdoubt, "addcoltxt" => $addcoltxt, "addcolvalue" => $addcolvalue, "altitude" => $altitude, "colnum" => $colnum, "datacol" => $datacol, "datadet" => $datadet, "determinadorid" => $determinadorid, "famid" => $famid, "gazetteerid" => $gazetteerid, "genusid" => $genusid, "habitatid" => $habitatid, "infraspid" => $infraspid, "latgrad" => $latgrad, "latminu" => $latminu, "latnors" => $latnors, "latsec" => $latsec, "longgrad" => $longgrad, "longminu" => $longminu, "longsec" => $longsec, "longwore" => $longwore, 'latdec' => $latdec, 'longdec' => $longdec, "plantaid" => $plantaid, "speciesid" => $speciesid, "traitids" => $traitids, 'arrayofvars' => $arrayofvars, 'cooraction' => $cooraction, 'localaction' => $localaction, 'habaction' => $habaction, 'detaction' => $detaction, 'especimensids' => $especimensids, 'especimenstxt' => $especimenstxt, 'plantnum' => $plantnum, 'inexsitu' => $inexsitu,'detid' => $detid, 'procedenciaid' => $procedenciaid, 'gpspointid'=> $gpspointid, 'procedenciagps'=> $procedenciagps );

//Checa por conflito entre amostras selecionadas
if (!empty($especimensids) && empty($_SESSION['oldspecids']) && $_SESSION['editando']==1) {
//checa por conflito em especimensids
	$ok = CheckSamplesConflictforPlants($gazetteerid,$habitatid,$latdec,$longdec,$altitude,$altmin,$altmax,$especimensids,$conn);
	if ($ok && $plantaid>0) {
		$zz = explode(";",$especimensids);
		$especimenid = $zz[0];
		$res = CompareSampleWithTaggedTreeSample($especimenid,$conn, $action='planta-exec.php', $arrayofvars=$arrayofvars);
		@extract($res);
	}
}

//extrair informacao de localidade
if ($gpspointid>0) {
	$locality = getGPSlocality($gpspointid,$name=FALSE,$conn);
} 
elseif ($gazetteerid>0) {
	$locality = getlocality($gazetteerid,$coord=TRUE,$conn);
}

//extrair dados de habitat
if (!empty($habitatid)) {
	$habitat = describehabitat($habitatid,$img=TRUE,$conn);
}

if (!empty($traitarray)) {
	$traitids = describetraits($traitarray,$img=TRUE,$conn);
}

//procedencia
if ($procedenciagps>0) {
	$procedencia = getGPSlocality($procedenciagps,$name=FALSE,$conn);
} 
elseif ($procedenciaid>0) {
	$procedencia = getlocality($procedenciaid,$coord=TRUE,$conn);
}

//abre tabela do formulario
if ($_SESSION['editando']==1) {
	$ed = GetLangVar('nameeditando');
} 
else {
	$ed = GetLangVar('namenova');
}

echo "
<br>
<table class='myformtable' align='center' cellpadding='4' width=90%>
<thead>
  <tr ><td colspan='100%'>$ed ".strtolower(GetLangVar('nametaggedplant'))."</td>
</tr>
</thead>
<tbody>
";
//numero da planta
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td class='tdsmallboldright'>".GetLangVar('nametagnumber')."</td>
    <form name='especimenesform' action=planta-exec.php method='post' >";
    //hidden input tags for most variables
	$ll = $arrayofvars;
	unset($ll['arryofvars'],$ll['plantnum'],$ll['inexsitu'],$ll['especimensids'],$ll['especimenstxt'],$ll['nosample']);
	@hiddeninputs($ll);  
echo "
  <td >
    <table>
      <tr>
        <td><input id='tagplantnum'  type='text' name='plantnum' value='$plantnum'></td>";

////// in situ ex situ, jardim botanico de manaus option, inicio
if (empty($_SESSION['editando'])) {
echo "
        <td class='tdsmallboldcenter' colspan=2>
          <table>
           <tr><td><input type='radio' name='inexsitu' value='Insitu' onselect=\"javascript:changevaluebyid('JB-N-','tagplantnum');\">&nbsp;<i>In Situ</i></td></tr>
           <tr><td><input type='radio' name='inexsitu' value='Exsitu' onselect=\"javascript:changevaluebyid('JB-X-','tagplantnum');\">&nbsp;<i>Ex Situ</i></td></tr>
           </table>
        </td>";
} 
/////////// in situ ex situ, jardim botanico de manaus option, fim
echo "
      </tr>
    </table>
  </td>
</tr>";

//seleciona coletas desta planta marcada se houver
if (!empty($especimensids)) {
		$ids = explode(";",$especimensids);
		$qq = "SELECT CONCAT(addcolldescr(espc.ColetorID),' ',espc.Number) as colref FROM Especimenes as espc WHERE ";
		if (count($ids)>1) {
			$ii=0;
			foreach($ids as $val) {
				if ($ii>0) {
					$qq= $qq." OR ";
				}
				$qq = $qq." EspecimenID='".$val."'";
				$ii++;
			}
		} else {
				$qq = $qq." EspecimenID='".$especimensids."'";
		}
		$rr = mysql_query($qq,$conn);
		$ii=0;
		while ($row = mysql_fetch_assoc($rr)) {
			if ($ii==0) {
				$especimenstxt = $row['colref']; 
			} else {
				$especimenstxt = $especimenstxt."; ".$row['colref']; 
			}
			$ii++;
		}
}


if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td class='tdsmallboldright'>".GetLangVar('nameamostra')."s ".GetLangVar('namecoletada')."s</td>
  <td >
    <table>
      <tr>";
			if ($_SESSION['editando']) {
				$bt = "Adicionar"; 
			} else {
				$bt = "Editar";
			}
			echo "
        <td class='tdformnotes' >
          <input type='hidden' name='plantaid' value='$plantaid'>
          <input type='hidden' name='especimensids' value='$especimensids'>
          <input type='text' name='especimenstxt' value='$especimenstxt' readonly size=50%>
        </td>
        <td><input type=button value=\"".$bt."\" class='bsubmit' ";
			$myurl ="coletaspopup.php?getespecimensids=$especimensids&formname=especimenesform"; 
			echo " onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\"></td>
        <td class='tdsmallboldright'>";
			if (empty($especimensids)) {
				echo "<input type='checkbox' name='nosample' value='no' ";
						if ($nosample=='no') { echo 'checked';}
							echo " onchange='this.form.submit();'>&nbsp;
				".GetLangVar('namenao')." ".strtolower(GetLangVar('namecoletada'));
			}
	echo "</td>
      </tr>
    </table>
  </td>
</tr>
</form>
";
if (!empty($plantnum)) {
echo "
<form name='coletaform' action=planta-exec.php method='post' >
  <input type='hidden' name='plantnum' value='$plantnum'>
  <input type='hidden' name='inexsitu' value='$inexsitu'>
  <input type='hidden' name='plantaid' value='$plantaid'>
  <input type='hidden' name='nosample' value='$nosample'>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
	echo "
  <tr bgcolor = $bgcolor>
    <td class='tdsmallboldright'>".GetLangVar('nametaggedby')."</td>
    <td >
      <table>
        <tr>
          <td class='tdformnotes' ><input type='text' size='30%' name='addcoltxt' value='$addcoltxt' readonly></td>
          <input type='hidden' name='addcolvalue' value='$addcolvalue'>
          <td><input type=button value=\"+\" class='bsubmit' ";
			$myurl ="addcollpopup.php?getaddcollids=$addcolvalue&formname=coletaform"; 
			echo " onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\"></td>
          </td>
          <td class='tdsmallboldright'>".GetLangVar('namedata')."</td>";
		if ((empty($datacol) && empty($_SESSION['editando'])) || $final==2 || $datacol==0) {
			echo "
          <td>
            <input name=\"datacol\" value=\"$datacol\" size=\"11\" readonly >
            <a onclick=\"if(self.gfPop)gfPop.fPopCalendar(document.forms['coletaform'].datacol);return false;\" ><img name=\"popcal\" align=\"absmiddle\" src=\"calendar/calbtn.gif\" width=\"34\" height=\"22\" border=\"0\" alt=\"\"></a>
          </td>";
		} else {
			echo "
          <td><input class='selectedval' type='text'  value='$datacol' name='datacol' readonly></td>";
		}
	echo "
        </tr>
      </table>
    </td>
</tr>"; 

//taxonomia
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = $bgcolor>
    <td class='tdsmallboldright'>".GetLangVar('nametaxonomy')."</td>
    <td >
      <table >
        <tr >
          <td id='dettexto'>$dettext</td>
          <input type='hidden' id='detsetcode' name='detset' value='$detset' >";
			if (empty($dettext)) {
				$butname = GetLangVar('nameselect');
			} else {
				$butname = GetLangVar('nameeditar');
			} 
		echo "
          <td>&nbsp;&nbsp;&nbsp;</td>
          <td><input type=button value='$butname' class='bsubmit' ";
			$myurl ="taxonomia-popup.php?detid=$detid&dettextid=dettexto&detsetid=detsetcode"; 
			echo " onclick = \"javascript:small_window('$myurl',800,300,'Taxonomia Popup');\"></td>";
		if ($_SESSION['editando']) {
			echo "
          <td><input type=button value='DetHistory' class='bblue' ";
			$myurl ="detchangespopup.php?plantaid=$plantaid"; 
			echo " onclick = \"javascript:small_window('$myurl',800,300,'Det History');\"></td>";
		}
		echo "
        </tr>
      </table>
    </td>
  </tr>
";

//dados de nome vulgar
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
	echo "
  <tr bgcolor = $bgcolor>
    <input type='hidden' name='vernacularvalue' value='$vernacularvalue'>
    <td class='tdsmallboldright'>".GetLangVar('namevernacular')."</td>
    <td  >
      <table>
        <tr>
          <td class='tdformnotes' ><input size=60% type='text' name='vernaculartxt' value='$vernaculartxt' readonly></td>
          <td><input type=button value=\"+\" class='bsubmit' ";
			$myurl ="vernacular-popup.php?getvernacularids=$vernacularvalue&formname=coletaform"; 
			echo " onclick = \"javascript:small_window('$myurl',350,280,'Add_from_Src_to_Dest');\"></td>
        </tr>
      </table>
    </td>
  </tr>"; 

//dados de localidade
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else{$bgcolor = $linecolor1 ;} $bgi++;

	if (empty($locality)) {
		$butname = GetLangVar('nameselect');
	} else {
		$butname = GetLangVar('nameeditar');
	} 

	echo "
  <tr bgcolor = $bgcolor>
    <td class='tdsmallboldright'>".GetLangVar('namelocalidade')."&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('localidadetipos');
	echo " onclick=\"javascript:alert('$help');\"></td>
    <td>
      <table >
        <tr>
          <input type='hidden' id='gazetteerid'  name='gazetteerid' value='$gazetteerid'>
          <td colspan='100%' id='locality' class='tdformnotes'>$locality</td>
        </tr>
        <tr>
          <td colspan='100%'>
            <table >
              <tr>
                <td align='center'><input type=button value='$butname' class='bsubmit' onclick = \"javascript:small_window('localidade-popup.php?gaztag=gazetteerid&localtag=locality&gazetteerid=$gazetteerid',850,150,'LocalidadePopUp');\"></td>
                <td class='tdsmallboldright' align='center'>".strtolower(GetLangVar('nameor')." ".GetLangVar('nameselect'))."   ponto GPS</td>
                <td align='center'>
                  <select name='gpspointid'>";
					if ($gpspointid>0) {
						$qqq = "SELECT * FROM GPS_DATA WHERE PointID='".$gpspointid."'";
						$rs = mysql_query($qqq,$conn);
						$rw = mysql_fetch_assoc($rs);
					echo "
                    <option class='optselectdowlight' selected value=".$rw['PointID'].">".$rw['Name']."</option>";
				}  else {
					echo "
                    <option  selected value=''>".GetLangVar('nameselect')."</option>";
				}
				echo "
                    <option  value=''>---------</option>";
					$res =  listgpswaypoinds($municipioid,$provinciaid,$gazetteerid,$countryid,$conn);
					$pais = "";
					$provincia = "";
					$municipio = '';
					$gazter = '';
					$date = "";
					$space = "&nbsp;&nbsp;";
					while ($row = mysql_fetch_assoc($res)) {
						if ($pais!=$row['Country']) {
							$pais = $row['Country'];
							echo "
                    <option class='optselectdowlight' value=''>".strtoupper($row['Country'])."</option>";
						}
						if ($provincia!=$row['Province']) {
							$provincia = $row['Province'];
						echo "
                    <option class='optselectdowlight' value=''>".$space.$row['Province']."</option>";
						}
						if ($municipio!=$row['Municipio']) {
							$municipio = $row['Municipio'];
						echo "
                    <option class='optselectdowlight' value=''>".$space.$space.$row['Municipio']."</option>";
						}
						if ($gazter!=$row['Gazetteer']) {
							$PathName = $row['PathName'];
							$level = $row['MenuLevel'];
							$gaztipo = $row['GazTipo'];
							$espaco = $space.$space.$space.str_repeat($space,$level);
						echo "
                    <option class='redtext' value=''>$espaco".$gaztipo." ".$row['Gazetteer']."</option>";
							$gazter = $row['Gazetteer'];
						}
						$spc = $space.$espaco."--";
						$date = $row['DateOriginal'];
						echo "
                    <option value=".$row['PointID'].">".$spc.$row['Name']." (".$date.")</option>";
					}
				echo "
                  </select>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class='tdsmallboldright'>&nbsp;&nbsp;X:</td>
          <td><input type='text'  style='text-align:right' value='".$plpos_x."' name='plpos_x' size='5'>&nbsp;m</td>
          <td class='tdsmallboldright'>&nbsp;&nbsp;Y:</td>
          <td><input type='text' style='text-align:right' value='".$plpos_y."'  name='plpos_y' size='5'>&nbsp;m</td>
          <td class='tdsmallboldright'>&nbsp;&nbsp;Referência:</td>
          <td>
            <select name='xyref'>";
					if (!empty($xyref)) {
						if ($xyref=='xyref00') { $xyreftxt = 'Vértice Esquerdo-Inferior (0,0 ou SW';}
						if ($xyref=='xyref01') { $xyreftxt = 'Vértice Esquerdo-Superior (0,1 ou NW)';}
						if ($xyref=='xyref11') { $xyreftxt = 'Vértice Direito-Superior (1,1 ou NE)';}
						if ($xyref=='xyref10') { $xyreftxt = 'Vértice Direito-Inferior (1,0 ou SE)';}
						echo "
              <option selected value='".$xyref."'>$xyreftxt</option>
              <option value=' '>----------</option>";
					} else {
						echo "
            <option value=''>".GetLangVar('nameselect')."</option>";
					}
					echo "
            <option value='xyref00'>Vértice Esquerdo-Inferior (0,0)</option>
            <option value='xyref01'>Vértice Esquerdo-Superior (0,1)</option>
            <option value='xyref11'>Vértice Direito-Superior (1,1)</option>
            <option value='xyref10'>Vértice Direito-Inferior (1,0)</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class='tdsmallboldright'>&nbsp;&nbsp;Distância:</td>
        <td><input type='text'  style='text-align:right'  value='".$plpos_dist."' name='plpos_dist' size='5'>&nbsp;m</td>
        <td class='tdsmallboldright'>&nbsp;&nbsp;Bússola:</td>
        <td><input type='text'  style='text-align:right' value='".$plpos_angle."' name='plpos_angle' size='5'>&nbsp;dg&nbsp;N</td>
      </tr>
    </table>
  </td>
</tr>";
//habitat descricao
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td class='tdsmallboldright'>".GetLangVar('namehabitat')."</td>
  <td >
    <table align='left' cellpadding=\"7\" cellspacing=\"0\" class='tdformnotes'>
      <input type='hidden' id='habitatidfield'  name='habitatid' value='$habitatid'>
      <tr>
        <td id='habitatfield' class='tdformnotes'>$habitat</td>";
		if (empty($habitatid)) {
			$buthab = GetLangVar('nameselect');
		} else {
			$buthab = GetLangVar('nameeditar');
		} 
		echo "
        <td align='center'><input type=button value='$buthab' class='bsubmit' onclick = \"javascript:small_window('habitat-popup-teste.php?pophabitatid=$habitatid&elementidval=habitatidfield&elementidtxt=habitatfield&opening=1',850,400,'Selecione um habitat');\"></td>
      </tr>
    </table>
  </td>
</tr>";

if ($inexsitu=='Exsitu') {
//dados de procedencia
if (empty($procedencia)) {
		$butname = GetLangVar('nameselect');
	} 
	else {
		$butname = GetLangVar('nameeditar');
	} 
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td class='tdsmallboldright'>".GetLangVar('nameprocedencia')."&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
	$help = GetLangVar('messageexplainprocedencia');
	echo " onclick=\"javascript:alert('$help');\"></td>
  <td>
    <table >
      <tr>
        <input type='hidden' id='procedenciaid'  name='procedenciaid' value='$procedenciaid'>
        <td colspan='100%' id='procedencia' class='tdformnotes'>$procedencia</td>
      </tr>
      <tr>
        <td>
          <table >
            <tr>
              <td align='center'><input type=button value='$butname' class='bsubmit' onclick = \"javascript:small_window('localidade-popup.php?gaztag=procedenciaid&localtag=procedencia&gazetteerid=$procedenciaid',850,150,'LocalidadePopUp');\"></td>
              <td class='tdsmallboldright' align='center'>".strtolower(GetLangVar('nameor')." ".GetLangVar('nameselect'))."   ponto GPS</td>
              <td align='center'>
                <select name='procedenciagps'>";
				if ($procedenciagps>0) {
					$qqq = "SELECT * FROM GPS_DATA WHERE PointID='".$procedenciagps."'";
					$rs = mysql_query($qqq,$conn);
					$rw = mysql_fetch_assoc($rs);
					echo "
                  <option class='optselectdowlight' value=".$rw['PointID'].">".$rw['Name']."</option>";
				}  else {
					echo "
                  <option  value=''>".GetLangVar('nameselect')."</option>";
				}
				echo "
                  <option  value=''>---------</option>";
				$qq = "SELECT * FROM GPS_DATA WHERE Type='Waypoint' Order by GPSName,DateOriginal,Name ASC";
				$res = mysql_query($qq,$conn);
				$gps = "nenhum";
				$date = "1900-10-04";
				while ($row = mysql_fetch_assoc($res)) {
					if ($gps!=$row['GPSName']) {
						$gps = $row['GPSName'];
						echo "
                  <option class='optselectdowlight' value=''>".$row['GPSName']."------</option>";
					}
					if ($date!=$row['DateOriginal']) {
						$date = $row['DateOriginal'];
						echo "
                  <option class='redtext' value=''>&nbsp;&nbsp;".$row['DateOriginal']."</option>";
					}
					echo "
                  <option value=".$row['PointID'].">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$row['Name']."</option>";
				}
				echo "
                </select>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";


}

///coordenadas da planta
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td align='right'>
    <table>
      <tr>
        <td class='tdsmallboldright'>".GetLangVar('namecoordenadas')."</td><td align='right'><img height=13 src=\"icons/icon_question.gif\" ";
				$help = GetLangVar('messageexplaincoordenadas');
				echo " onclick=\"javascript:alert('$help');\"></td>
      </tr>
    </table>
  </td>
  <td>
    <table>
      <tr class='tdformnotes'>
        <td align='right'><i>Latitude</i></td>
        <td >
          <table border=0 cellpadding=\"3\">
            <tr class='tdformnotes'>
              <td ><input type='text' size=6 name='latgrad' value='$latgrad' ></td>
              <td align='left'><sup>o</sup></td>
              <td ><input type='text' size=3 name='latminu' value='$latminu' ></td>
              <td align='left'>'</td>
              <td ><input type='text' size=3 name='latsec' value='$latsec' ></td>
              <td align='left'>\"</td>
              <td align='right'><input type='radio' name='latnors' "; 
					if ($latnors=='N') { echo "checked";}
					echo " value='N'></td>
              <td align='left'>N</td>
              <td align='right'><input type='radio' name='latnors' "; 
					if ($latnors=='S') { echo "checked";}
					echo "  value='S'></td>
              <td align='left'>S</td>
            <tr>
          </table>
        </td>
      </tr>
      <tr>
        <td align='right'><i>Longitude</i></td>
        <td >
          <table border=0 cellpadding=\"3\">
            <tr class='tdformnotes'>
              <td align='center'><input type='text' size=6 name='longgrad' value='$longgrad' ></td>
              <td align='left'><sup>o</sup></td>
              <td align='left'><input type='text' size=3 name='longminu' value='$longminu' ></td>
              <td align='left'>'</td>
              <td align='left'><input type='text' size=3 name='longsec' value='$longsec' ></td>
              <td align='left'>\"</td>
              <td align='left'>
              <td align='right'><input type='radio' name='longwore' "; 
					if ($longwore=='W') { echo "checked";}
					echo " value='W'></td>
              <td align='left'>W</td>
              <td align='right'><input type='radio' name='longwore' "; 
					if ($longwore=='E') { echo "checked";}
					echo "  value='E'></td>
              <td align='left'>E</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td align='right'><i>Altitude</i></td>
        <td >
          <table border=0 cellpadding=\"3\">
            <tr class='tdformnotes'>
              <td align='center'><input type='text' size=6 name='altitude' value='$altitude'></td>
              <td align='left'>m</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td class='tdsmallboldright'>".GetLangVar('nameobs')."s</td>
  <td >
    <table  align='left' border=0 cellpadding=\"3\" cellspacing=\"0\">
      <tr>
        <td id='traitids' class='tdformnotes'>$traitids</td>
        <td align='left'><input  type=button value=\"".GetLangVar('nameselect')."\" class='bsubmit' onclick = \"javascript:small_window('variacao-popup.php?&elementid=traitids&working=work',700,500,'EntrarVariacao');\"></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  
	else{$bgcolor = $linecolor1 ;}
	$bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td class='tdsmallboldright'>Dados de Monitoramento</td>
  <td >
    <table  align='left' border=0 cellpadding=\"3\" cellspacing=\"0\">
      <tr>
        <td id='traitids' class='tdformnotes'>$monidesc</td>
        <td align='left'>
        <input  type=button value=\"".GetLangVar('nameselect')."\" class='bsubmit'></td>
      </tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td colspan=100%>
    <table align='center' >
      <tr>
        <input type='hidden' name='final' value='1'>
        <td align='center' ><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit'></td>
</form>
<form action=planta-exec.php method='post'>";
	$ll = $arrayofvars;
	$ll['arryofvars'] = NULL;
	@hiddeninputs($ll);   //hidden values for most variables
echo "
  <input type='hidden' name='final' value='2'>
  <td align='left'><input type='submit' value='".GetLangVar('messagesalvareduplicar')."' class='bblue'></td>
</form>
<form action=planta-form.php method='post'>
  <td align='left'><input type='submit' value='".GetLangVar('namevoltar')."' class='breset'></td>
</form>
      </tr>
    </table>
  </td>
</tr>
";
}

echo "
</tbody>
</table>";
}

HTMLtrailers();

?>