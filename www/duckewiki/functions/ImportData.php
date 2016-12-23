<?php
function url_exists($url) {
      $handle = @fopen($url,'r');
      if($handle !== false){
	      $res= true;
      } else{
	      $res=false;
      }
      return($res);
} 

//checar se esta conectado a internet
function taconectado() {
    $connected = @fsockopen("www.google.com",80, $errno, $errstr,10);
    if ($connected){
        $is_conn = true;
        fclose($connected);
    } else {
    	//codigo abaixo nao funcionou
    	//$ip = "proxy.inpa.gov.br"; // proxy IP, change this according to your proxy setting in your IE or NS
    	//$port = 3128; // proxy port, change this according to your proxy setting in your IE or NS
    	//$fp = fsockopen($ip,$port); // connect to proxy
    	//$rr = fputs($fp, "GET <a href='http://www.google.com/' title='http://www.google.com/'>http://www.google.com/</a> HTTP/1.0\r\nHost:www.google.com:80\r\n\r\n");
		//if ($rr) {$is_conn = TRUE;} else {$is_conn=FALSE;}
		$is_conn=FALSE;
    }
    return $is_conn;
}//end is_connected function 


function curl_get_file_contents($URL,$proxy)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);
        if ($contents) return $contents;
            else return FALSE;
}
    


function GetUrlData($url, $proxy=''){

$ch=curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_PROXY, $proxy);
$str=curl_exec($ch);
curl_close($ch);
return $str;

}


function getmycobankdata($genero,$spnome,$familia,$infspnome) {

$infspnome = trim($infspnome);
$infspnome = mb_strtolower($infspnome);
$spnome = trim($spnome);
$spnome = mb_strtolower($spnome);
$genero = trim($genero);
$genero = mb_strtolower($genero);
$genero = ucfirst($genero);
if (!empty($infspnome)) {
	$epiteto= $infspnome;
	$namesearch1 = $genero." ".$spnome." ".$infspnome;
	$fieldname = "InfraEspecie";
	$namesearch = "name CONTAINS \"".$genero."\" AND name CONTAINS \"".$spnome."\"  AND name CONTAINS \"".$infspnome."\"";
} else {
	if (!empty($spnome)) {
		$epiteto= $spnome;
		$namesearch1 = $genero." ".$spnome;
		$fieldname = "Especie";
		$namesearch = "name CONTAINS \"".$namesearch1."\"";
	} else {
		if (!empty($genero)) {
			$epiteto= $genero;
			$fieldname = "Genero";
			$namesearch1 = $genero;
			$namesearch = "name CONTAINS \"".$genero."\"";
		} elseif (!empty($familia)) {
			$epiteto= $familia;
			$namesearch1 = $familia;
			$namesearch = "name CONTAINS \"".$familia."\"";
			$fieldname = "Familia";
		}
	
	}
}
if (!empty($epiteto) && !empty($namesearch)) {
$ipnurl = "http://www.mycobank.org/Services/Generic/SearchService.svc/rest/xml?layout=14682616000000161&filter=".$namesearch;

//echo $ipnurl."<br >";

$url = $ipnurl;
$sxml = simplexml_load_file($url);
$json = json_encode($sxml);
$array = json_decode($json,TRUE);
//echopre($array);
//echo "<br><hr><br>";

if ($fieldname=='InfraEspecie') {
	$aa = $array;
} else {
	$aa = $array['Taxon'];
}
//$aa = $array;
//echopre($aa);
//echo "<br><hr><br>";
$temid = $aa["_id"];
//echo "tem id = ".$temid."<br >";
if (!empty($temid) && ($temid+0)>0) {
	$otaxon = array($aa);
	$validtaxon = array($aa);
} else {
//echo "<br><hr><br>";
//echo "a conta é ".count($aa)."<br >";
$otaxon = array();
$valitdtaxon = array();
foreach($aa as $kk => $vv) {
	//echo "<hr>".$kk."<br >";
	//echopre($vv);
	//echopre($vv);
	$nn = $vv['name'];
	$status = $vv['namestatus_'];
	$opeiteto = $vv['epithet_'];
	if ($fieldname=='InfraEspecie') {
		$nnn = explode(" ",$vv['name']);
		unset($nnn[2]);
		$nn = implode(" ",$nnn);
		$nn = trim($nn);
		//echo "aqui ".$nn."  vs. ".$namesearch1."<br >";
	}
    if ($nn==$namesearch1 && $opeiteto==$epiteto) {
		$otaxon[] = $vv;
		if ($status=='Legitimate' || $status=='Valid') {
				$valitdtaxon[]  = $vv;
		}
    }
}
}


//echopre($otaxon);
//echo "<br><hr><br>";
//echo "a conta é ".count($otaxon)."<br >";
//echo "simple é".$simple."<br >";


if (count($otaxon)>1) {
	$otaxon = $valitdtaxon;
}
$quantos = count($otaxon);
if ($quantos==1) {
	$otaxon = $otaxon[0];
	$resposta = "Encontrado um registro do nome em Mycobank";
	$status = $otaxon['namestatus_'];
	//PEGA O NOME VÁLIDO SE FOR UM NOME INVÁLIDO
	if ($status=='Illegitimate' || $status=='Invalid') {
		$acceptednameid = $otaxon['currentname_pt_']['TargetRecord']['Id'];
		$ipnurl2 = "http://www.mycobank.org/Services/Generic/SearchService.svc/rest/xml?layout=14682616000000161&filter=_id=\"".$acceptednameid."\"";
		$url2 = $ipnurl2;
		$sxml2 = simplexml_load_file($url2);
		$json2 = json_encode($sxml2);
		$array2 = json_decode($json2,TRUE);
		$otaxon = $array2['Taxon'];
		$resposta .= "mas o nome é sinônimo de ".$otaxon['currentname_pt_']['TargetRecord']['Name']." para os quais as informações foram extraídas.";
	}
	if ($fieldname=='InfraEspecie') {
		$ifi = explode(" ",$otaxon['name']);
		$infrasppnivel = $ifi[2];
	} else {
		$infrasppnivel = "";
	}
	
	
	//EXPORTA OS DADOS
	$e3787 = explode(",",$otaxon['e3787']);
	unset($e3787[0]);
	$txt2 = implode(",",$e3787);
	$txt2 = explode("[",$txt2);
	$txt2 = $txt2[0];
	$pubref = $txt2;
	$fieldsaskeyofvaluearray = array(
'spnome' => trim($otaxon['epithet_']),
'autor' => trim($otaxon['authorsabbrev_']),
'pubrevista' => trim($pubref),
'pubano' => trim($otaxon['nameyear_']),
'mycobankid' => trim($otaxon['mycobanknr_']),
'subvar' => trim($infrasppnivel)
	);
	
	//echopre($otaxon);
	$retorno =  array("dados" => $fieldsaskeyofvaluearray,"resposta" => $resposta);
} 
	else {
	$retorno =  "Foram encontrados ".$quantos."  registros para ".$namesearch1;
}
}  
else {
	$retorno =  "Houve um erro. Nome não especificado";
}
return $retorno;
}




function StoreData($family,$genus,$filename,$res,$conn) {
    set_time_limit(1000);
	  $relativepath = 'uploads/';
		$texttowrite = $res;
		WriteToTXTFile($filename,$texttowrite,$relativepath);
		////////////////////
		$numfam = 0;
    $numgen = 0;
    $numsp = 0;
    $numsubsp = 0;

    $userid = $_SESSION['userid'];
    $sessiondate = $_SESSION['sessiondate'];

//Cria tabela vazia para armazenar dados do IPNI
    $qq = "DROP TABLE temp";
    mysql_query($qq,$conn);
    $qq = "SELECT * FROM `IPNIextended` WHERE Need='1'";
    $rr = mysql_query($qq,$conn);
    $nn = mysql_numrows($rr);
    $tt = "CREATE TABLE IF NOT EXISTS temp (";
    $i=0;
    $validFields = array();
    $colnomes = array();
while ($row = mysql_fetch_assoc($rr)) {
	$validFields[$i] = $row['ArrayIndex'];
	$colnomes[$i] = $row['NewColnames'];  
	if ($i!==($nn-1)) {
		$tt = $tt.$row['NewColnames']." ".$row['ColType'].", ";
	} else {
		$tt = $tt.$row['NewColnames']." ".$row['ColType'].")";
	}
	$i++;
}
mysql_query($tt,$conn);

//Pega dados ja importados do IPNI e insere na tabela acima
if (empty($genus)) {
	$filename = $family.".txt";
} else {
	$filename = $genus.".txt";
}
$relativepath = 'uploads/';
$fop = fopen($relativepath.$filename, 'r');

$nn = count($validFields);
$j=0;
while (($data = fgetcsv($fop, 0, "%%")) !== FALSE) {
	if ($j>0) {
		$spinsert = "INSERT INTO temp (";
		for ($i = 0; $i < $nn; $i++) {
			//echo $colnomes[$i]." = ".$data[$validFields[$i]]."<br>";
			if ($i!==($nn-1)) {
				$spinsert = $spinsert.$colnomes[$i].", ";
			} else {
				$spinsert = $spinsert.$colnomes[$i].")";
			}
		}
		$spinsert = $spinsert." VALUES (";
		for ($i = 0; $i < $nn; $i++) {
			if ($i!==($nn-1)) {
				$spinsert = $spinsert."'".$data[$validFields[$i]]."',";
			} else {
				$spinsert = $spinsert."'".$data[$validFields[$i]]."')";
			}
		} 
	}
	mysql_query($spinsert,$conn);
	$j++;
}

if (!empty($genus)) {
	$qq = "SELECT DISTINCT Family FROM temp";
	$res = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($res);
	$family = $row['Family'];
} 


//agora separa os dados nas tabelas correspondentes
//pega o ID da familia ou insere se nao existir
$qq = "SELECT * FROM Tax_Familias WHERE Familia='$family'";
$rr = mysql_query($qq,$conn);
$nn = mysql_numrows($rr);
if ($nn==1) {
	$row = mysql_fetch_assoc($rr);
	$familiaID = $row['FamiliaID'];
	$numfam++;
} elseif ($nn==0) {
	$qq = "INSERT INTO Tax_Familias (Familia,AddedBy,AddedDate) VALUES ('$family','$userid','$sessiondate')";
	mysql_query($qq,$conn);
	$qq = "SELECT * FROM Tax_Familias WHERE Familia='$family'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$familiaID = $row['FamiliaID'];
	$numfam++;
}

//seleciona os generos que contem especies
$qq = "SELECT DISTINCT Genus FROM temp WHERE Rank='spec.'";
$rr = mysql_query($qq,$conn);
while ($row = mysql_fetch_assoc($rr)) {
	$gen = $row['Genus'];
	$qq = "SELECT * FROM Tax_Generos WHERE Genero='$gen'";
	$res = mysql_query($qq,$conn);
	$nn = mysql_numrows($res);
	if ($nn==0) {
		$qq = "SELECT * FROM temp WHERE Genus='$gen' AND Rank='gen.' ORDER BY PubYEAR ASC";
		$resul = mysql_query($qq,$conn);
		$numgen = mysql_numrows($resul);
		if ($numgen>1) {
			$qq = "SELECT * FROM temp WHERE Genus='$gen' AND Rank='gen.' AND PubYEAR>0";
			$result = mysql_query($qq,$conn);
			$nnn = mysql_numrows($result);
			if ($nnn>1) {
				//echo "Registro para o genero ".$gen." duplicados:<br>";
				//while ($rrr = mysql_fetch_assoc($result)) {				
					//echo $rrr['PublishingAuthor']." ".$rrr['PubYEAR']." ".$rrr['Collation']."<br>";
				//}
				$qq = "SELECT * FROM temp WHERE Genus='$gen' AND Rank='gen.' AND PubYEAR>0 ORDER BY PubYEAR ASC LIMIT 1";
				$result = mysql_query($qq,$conn);
				$genres = mysql_fetch_assoc($result);
			} else {
				$genres = mysql_fetch_assoc($result);
			}
		} else {
		$genres = mysql_fetch_assoc($resul);		
		}
	if ($genres!==NULL) {
		$basionymAut = $genres['BasionymAuthor'];
		$basionym = $genres['Basionym'];
		$pubautor = $genres['PublishingAuthor'];
		$publication = $genres['Publication'];
		$collation = $genres['Collation'];
		$PubYEAR  = $genres['PubYEAR'];
		$Synonym  = $genres['Synonym'];
		$IpniID  = $genres['IpniID'];		
		$qq = "INSERT INTO Tax_Generos (Genero,FamiliaID,GeneroAutor,Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,IpniID,AddedBy,AddedDate) 
		VALUES ('$gen','$familiaID','$pubautor','$basionym','$basionymAut','$publication','$collation','$PubYEAR','$Synonym','$IpniID',
		'$userid','$sessiondate')";
		mysql_query($qq,$conn);
		$numgen++;
	}
	$genres=NULL;
	}
	
}

//seleciona as especies 
$qq = "SELECT DISTINCT Genus,Species FROM temp WHERE Species>''";
$rr = mysql_query($qq,$conn);
while ($row = mysql_fetch_assoc($rr)) {
	$gen = $row['Genus'];
	$spec = $row['Species'];
	//get genusid
		$qq = "SELECT * FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE Genero='$gen' AND Familia='$family'";	
		$res = mysql_query($qq,$conn);
		$rrw = mysql_fetch_assoc($res);
		$genid = $rrw['GeneroID'];
	
	//checar se ja esta registrado
		$qq = "SELECT * FROM Tax_Especies WHERE Especie='$spec' AND GeneroID='$genid'";
		$rrr = mysql_query($qq,$conn);
		$numspec = mysql_numrows($rrr);
		if ($numspec==0) {
			//checar registros duplicados
			$qq = "SELECT * FROM temp WHERE Species='$spec' AND Reference>'' AND Genus='$gen'";
			$rrr = mysql_query($qq,$conn);
			$numinf = mysql_numrows($rrr);
			//se estiver duplicado seleciona aquele com data de publicacao
			if ($numinf>1) {
				/////////////////				
				$qq = "SELECT * FROM temp WHERE Species='$spec' AND Genus='$gen' AND Reference>'' AND PubYEAR>0";
				$result = mysql_query($qq,$conn);
				$nnn = mysql_numrows($result);
				if ($nnn>1) {
					$erro = array();
					$h = 0;
					while ($rrr = mysql_fetch_assoc($result)) {
						$erro[$h] = $rrr['PublishingAuthor']."_".$rrr['PubYEAR'];
						$h++;
					}
					$erro = array_unique($erro);
					$cerr = count($erro);
					if ($cerr>1) {
						//echo "Registro para a especie ".$gen." ".$spec." duplicados:<br>";
						$qq = "SELECT * FROM temp WHERE Species='$spec' AND Genus='$gen' AND Reference>'' AND PubYEAR>0 ORDER BY PubYEAR ASC LIMIT 1";
						$re = mysql_query($qq,$conn);
						$genres=mysql_fetch_assoc($re);
					} elseif ($cerr==1) {
						$qq = "SELECT * FROM temp WHERE Species='$spec' AND Genus='$gen' AND Reference>'' AND PubYEAR>0 LIMIT 1";
						$novores = mysql_query($qq,$conn);
						$genres = mysql_fetch_assoc($novores);
					}
				} else {
					$genres = mysql_fetch_assoc($result);
				}
				//////////////////
			} elseif ($numinf==1) {
				$genres = mysql_fetch_assoc($rrr);
			}
			if ($genres!==NULL) {
				$basionymAut = $genres['BasionymAuthor'];
				$basionym = $genres['Basionym'];
				$pubautor = $genres['PublishingAuthor'];
				$specautor = $genres['SpeciesAuthor'];
				if (!empty($specautor)) {
					$pubautor= $specautor;
				}
				$publication = $genres['Publication'];
				$collation = $genres['Collation'];
				$PubYEAR  = $genres['PubYEAR'];
				$geodist = $genres['GeoDistribution'];
				$Synonym  = $genres['Synonym'];
				$Synonym = str_replace($family, "", $Synonym);
				$IpniID  = $genres['IpniID'];
				$qq = "INSERT INTO Tax_Especies (Especie,GeneroID,EspecieAutor,Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,IpniID,GeoDistribution,AddedBy,AddedDate) 
				VALUES ('$spec','$genid','$pubautor','$basionym','$basionymAut','$publication','$collation','$PubYEAR','$Synonym','$IpniID', '$geodist',
				'$userid','$sessiondate')";
				//echo $qq."<br>";
				mysql_query($qq,$conn);
				$numsp++;
			}
		} //endif se ja registrado
		$genres=NULL;
		
}



//categorias infraespecificas
$qq = "SELECT DISTINCT Genus,Species,InfraSpecies FROM temp WHERE InfraSpecies>'' AND Reference>''";
$rr = mysql_query($qq,$conn);
$ninf = mysql_numrows($rr);
while ($row = mysql_fetch_assoc($rr)) {
	$gen = $row['Genus'];
	$spec = $row['Species'];
	$infraspec = $row['InfraSpecies'];
	//get especieID
		$qq = "SELECT * FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE Especie='$spec' AND Genero='$gen' AND Familia='$family'";	
		$res = mysql_query($qq,$conn);
		$rrw = mysql_fetch_assoc($res);
		$specid = $rrw['EspecieID'];
	//checar se o registro ja existe nao duplica
		$qq = "SELECT * FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE InfraEspecie='$infraspec'  AND Especie='$spec' AND Genero='$gen'";	
		$res = mysql_query($qq,$conn);
		$numinfraspec = mysql_numrows($res);
	if ($numinfraspec==0) {	
	//checar registros duplicados
	$qq = "SELECT * FROM temp WHERE InfraSpecies='$infraspec' AND Reference>'' AND Genus='$gen' AND Species='$spec'";
	//echo $qq."<br>";
	$rrr = mysql_query($qq,$conn);
	$numinf = mysql_numrows($rrr);
	if ($numinf>1) {
		$qq = "SELECT * FROM temp WHERE InfraSpecies='$infraspec' AND Reference>'' AND Genus='$gen' AND Species='$spec' AND PubYEAR>0 ORDER BY PubYEAR ASC LIMIT 1";
		$rrrr = mysql_query($qq,$conn);
		$numsubspecies = mysql_numrows($rrrr);
		if ($numsubspecies==1) {
			$genres = mysql_fetch_assoc($rrrr);
			//echo $gen." ".$spec." ".$infraspec." duplicated NOT IMPORTED!<br>";	
		} else {$genres= NULL;}
	} elseif ($numinf==1) {
		$genres = mysql_fetch_assoc($rrr);
	}
	if ($genres!==NULL) {
			$rank = $genres['Rank'];
			$basionymAut = $genres['BasionymAuthor'];
			$basionym = $genres['Basionym'];
			$pubautor = $genres['PublishingAuthor'];
			$publication = $genres['Publication'];
			$collation = $genres['Collation'];
			$PubYEAR  = $genres['PubYEAR'];
			$geodist = $genres['GeoDistribution'];
			$Synonym  = $genres['Synonym'];
			$Synonym = str_replace($family, "", $Synonym);
			$IpniID  = $genres['IpniID'];
			$qq = "INSERT INTO Tax_InfraEspecies (InfraEspecie,EspecieID,InfraEspecieNivel,InfraEspecieAutor,Basionym,BasionymAutor,PubRevista,PubVolume,PubAno,Sinonimos,IpniID,GeoDistribution,AddedBy,AddedDate) 
			VALUES ('$infraspec','$specid','$rank','$pubautor','$basionym','$basionymAut','$publication','$collation','$PubYEAR','$Synonym','$IpniID', '$geodist',
			'$userid','$sessiondate')";
			//echo $qq."<br>";
			mysql_query($qq,$conn);
			$numsubsp++;
	}
	} //end if ja registrado
	$genres=NULL;
}	

$qq = "DROP TABLE temp";
mysql_query($qq,$conn);
		/////////////////////////////
		

$qq = "DELETE FROM lixogen WHERE Genero='".$genus."'";
$rrq = mysql_query($qq,$conn);

if ($rrq) {$apagado='OK';} else {$apagado='Not OK';}

echo "<p class='success'>$genus ($apagado)</p>";
unlink($relativepath.$filename);

}

?>