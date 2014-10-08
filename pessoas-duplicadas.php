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
extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
extract($gget);

//CABECALHO
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Novas pessoas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($idstocheckser)) {
	$qq = "SELECT GROUP_CONCAT(PessoaID SEPARATOR ';') as ids, count(PessoaID) as cnt FROM  Pessoas 
GROUP BY CONCAT(UPPER(SUBSTRING(Prenome,1,1)),UPPER(acentostosemacentos(Sobrenome))) HAVING cnt>1";
	$res = mysql_query($qq,$conn);
	$idstocheck = array();
	while($row = mysql_fetch_assoc($res)) {
		$idstocheck[] = $row['ids'];
	}
} else {
	$idstocheck = unserialize($idstocheckser);
	///CORRIGI SE FOR O CASO
	if ($final==2) {
		foreach($mesmoque as $kk => $vv) {
			if ($vv!=$kk && $vv>0) {
				echo $kk." por ".$vv."<br />";
				//EM ESPECIMENS
				$qu = "UPDATE Especimenes SET ColetorID=".$vv." WHERE ColetorID=".$kk;
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ChangeEspecimenes SET ColetorID=".$vv." WHERE ColetorID=".$kk;
				$ru = mysql_query($qu,$conn);

				$qu = "UPDATE Especimenes SET AddColIDS=pessoaduplicata(AddColIDS,".$kk.",".$vv.")  WHERE pessoainfield(AddColIDS,".$kk.")>0";
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ChangeEspecimenes SET AddColIDS=pessoaduplicata(AddColIDS,".$kk.",".$vv.")  WHERE pessoainfield(AddColIDS,".$kk.")>0";
				$ru = mysql_query($qu,$conn);

				//IDENTIDADES
				$qu = "UPDATE Identidade SET DetbyID=".$vv." WHERE DetbyID=".$kk;
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE Identidade SET RefColetor=".$vv." WHERE RefColetor=".$kk;
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE Identidade SET RefDetby=".$vv." WHERE RefDetby=".$kk;
				$ru = mysql_query($qu,$conn);
				//IMAGENS
				$qu = "UPDATE Imagens SET Autores=pessoaduplicata(Autores,".$kk.",".$vv.") WHERE pessoainfield(AddColIDS,".$kk.")>0";
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ChangeImagens SET Autores=pessoaduplicata(Autores,".$kk.",".$vv.") WHERE pessoainfield(AddColIDS,".$kk.")>0";
				$ru = mysql_query($qu,$conn);


				//EXPEDITO
				$qu = "UPDATE MetodoExpedito SET PessoasIDs=pessoaduplicata(PessoasIDs,".$kk.",".$vv.") WHERE pessoainfield(PessoasIDs,".$kk.")>0";
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ChangeMetodoExpedito SET PessoasIDs=pessoaduplicata(PessoasIDs,".$kk.",".$vv.") WHERE pessoainfield(PessoasIDs,".$kk.")>0";
				$ru = mysql_query($qu,$conn);

				$qu = "UPDATE MetodoExpeditoPlantas SET PessoasIDs=pessoaduplicata(PessoasIDs,".$kk.",".$vv.") WHERE pessoainfield(PessoasIDs,".$kk.")>0";
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ChangeMetodoExpeditoPlantas SET PessoasIDs=pessoaduplicata(PessoasIDs,".$kk.",".$vv.") WHERE pessoainfield(PessoasIDs,".$kk.")>0";
				$ru = mysql_query($qu,$conn);

				//PLANTAS
				$qu = "UPDATE Plantas SET TaggedBy=pessoaduplicata(TaggedBy,".$kk.",".$vv.") WHERE pessoainfield(TaggedBy,".$kk.")>0";
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ChangePlantas SET TaggedBy=pessoaduplicata(TaggedBy,".$kk.",".$vv.") WHERE pessoainfield(TaggedBy,".$kk.")>0";
				$ru = mysql_query($qu,$conn);

				//PROJETOS
				$qu = "UPDATE Projetos SET Equipe=pessoaduplicata(Equipe,".$kk.",".$vv.") WHERE pessoainfield(Equipe,".$kk.")>0";
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ChangeProjetos SET Equipe=pessoaduplicata(Equipe,".$kk.",".$vv.") WHERE pessoainfield(Equipe,".$kk.")>0";
				$ru = mysql_query($qu,$conn);

				//ESPECIALISTAS
				$qu = "UPDATE Especialistas SET Especialista=".$vv." WHERE Especialista=".$kk;
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ChangeEspecialistas SET Especialista=".$vv." WHERE Especialista=".$kk;
				$ru = mysql_query($qu,$conn);

				//GRUPOS DE ESPECIES
				$qu = "UPDATE Tax_SpeciesGroups SET PessoaID=".$vv." WHERE PessoaID=".$kk;
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ChangeTax_SpeciesGroups SET PessoaID=".$vv." WHERE PessoaID=".$kk;
				$ru = mysql_query($qu,$conn);

				//USUARIOS QUE TAMBEM SAO PESSOAS
				$qu = "UPDATE Users SET PessoaID=".$vv." WHERE PessoaID=".$kk;
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ChangeUsers SET PessoaID=".$vv." WHERE PessoaID=".$kk;
				$ru = mysql_query($qu,$conn);

				//EQUIPAMENTOS
				$qu = "UPDATE Equipamentos SET PessoaID=".$vv." WHERE PessoaID=".$kk;
				$ru = mysql_query($qu,$conn);
				$qu = "UPDATE ChangeEquipamentos SET PessoaID=".$vv." WHERE PessoaID=".$kk;
				$ru = mysql_query($qu,$conn);
			}
		}
		foreach($mesmoque as $kk => $vv) {
			$nrr=0;
			if ($vv!=$kk && $vv>0) {
				echo $kk." por ".$vv." apagando agora!<br />";
				//EM ESPECIMENS
				$qu = "SELECT * FROM  Especimenes WHERE pessoainfield(AddColIDS,".$kk.")>0 OR ColetorID=".$kk;
				$ru = @mysql_query($qu,$conn);
				$nru = @mysql_numrows($ru);
				if ($nru>0) {
					echo 'especimenes<br />';
					$nrr = $nrr+$nru;
				}
				//IDENTIDADES
				$qu = "SELECT * FROM  Identidade WHERE  DetbyID=".$kk." OR RefColetor=".$kk." OR RefDetby=".$kk;
				$ru = @mysql_query($qu,$conn);
				$nru = @mysql_numrows($ru);
				if ($nru>0) {
					$nrr = $nrr+$nru;
					echo 'identidade<br />';
				}

				//IMAGENS
				$qu = "SELECT * FROM   Imagens WHERE pessoainfield(AddColIDS,".$kk.")>0";
				$ru = @mysql_query($qu,$conn);
				$nru = @mysql_numrows($ru);
				if ($nru>0) {
					$nrr = $nrr+$nru;
					echo 'Imagens<br />';
				}
				//EXPEDITO
				$qu = "SELECT * FROM    MetodoExpedito WHERE pessoainfield(PessoasIDs,".$kk.")>0";
				$ru = @mysql_query($qu,$conn);
				$nru = @mysql_numrows($ru);
				if ($nru>0) {
					$nrr = $nrr+$nru;
					echo 'MetodoExpedito<br />';
				}

				$qu = "SELECT * FROM  MetodoExpeditoPlantas WHERE pessoainfield(PessoasIDs,".$kk.")>0";
				$ru = @mysql_query($qu,$conn);
				$nru = @mysql_numrows($ru);
				if ($nru>0) {
					$nrr = $nrr+$nru;
					echo 'MetodoExpeditoPlantas<br />';
					
				}


				//PLANTAS
				$qu = "SELECT * FROM  Plantas WHERE pessoainfield(TaggedBy,".$kk.")>0";
				$ru = @mysql_query($qu,$conn);
				$nru = @mysql_numrows($ru);
				if ($nru>0) {
					$nrr = $nrr+$nru;
					echo 'Plantas<br />';
				}

				//PROJETOS
				$qu = "SELECT * FROM Projetos WHERE pessoainfield(Equipe,".$kk.")>0";
				$ru = @mysql_query($qu,$conn);
				$nru = @mysql_numrows($ru);
				if ($nru>0) {
					$nrr = $nrr+$nru;
					echo 'Projetos<br />';
				}


				//ESPECIALISTAS
				$qu = "SELECT * FROM Especialistas WHERE Especialista=".$kk;
				$ru = @mysql_query($qu,$conn);
				$nru = @mysql_numrows($ru);
				if ($nru>0) {
					$nrr = $nrr+$nru;
					echo 'Especialistas<br />';
				}

				//GRUPOS DE ESPECIES
				$qu = "SELECT * FROM Tax_SpeciesGroups WHERE PessoaID=".$kk;
				$ru = @mysql_query($qu,$conn);
				$nru = @mysql_numrows($ru);
				if ($nru>0) {
					$nrr = $nrr+$nru;
					echo 'Tax_SpeciesGroups<br />';
				}

				//USUARIOS QUE TAMBEM SAO PESSOAS
				$qu = "SELECT * FROM Users WHERE PessoaID=".$kk;
				$ru = @mysql_query($qu,$conn);
				$nru = @mysql_numrows($ru);
				if ($nru>0) {
					$nrr = $nrr+$nru;
					echo 'Users<br />';
				}

				//EQUIPAMENTOS
				$qu = "SELECT * FROM Equipamentos WHERE PessoaID=".$kk;
				$ru = @mysql_query($qu,$conn);
				$nru = @mysql_numrows($ru);
				if ($nru>0) {
					$nrr = $nrr+$nru;
					echo 'Equipamentos<br />';
				}
				if ($nrr==0) {
					CreateorUpdateTableofChanges($kk,'PessoaID','Pessoas',$conn);
					$qdel = "DELETE FROM Pessoas WHERE PessoaID=".$kk;
					//echo $qdel."<br />";
					$rd = mysql_query($qdel,$conn);
					if ($rd) {
						echo "pessoa ".$kk." apagada<br />";
					} else {
						echo $qdel."<br />";
					}
				}
			}
		}
	}
	//echopre($ppost);
	unset($idstocheck[0]);
	$idstocheck = array_values($idstocheck);
}
$currid = $idstocheck[0];
$curids = explode(";",$currid);
$nc = count($curids);
echo "
<form action='pessoas-duplicadas.php' method='post' name='coletaform' >
<input type='hidden'  name='idstocheckser'  value='".serialize($idstocheck)."' >
<table align='center' cellpadding='7' class='myformtable'>
<thead>
<tr><td colspan='2'>".$nc." pessoas parecem ser a mesma</td></tr>
<tr class='subhead'><td>Nome cadastrado</td><td>Juntar com</td></tr>
</thead>
<tbody>";
if (count($curids)>0) {
$ii=0;
foreach ($curids as $cid) {
	$cid = $cid+0;
	if ($cid>0) {
	$q = "SELECT * FROM Pessoas WHERE PessoaID=".$cid;
	$m = mysql_query($q,$conn);
	$r = mysql_fetch_assoc($m);
	$nome = trim($r['Prenome']." ".$r['SegundoNome']);
	$nome .= " ".$r['Sobrenome'];
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td>".$nome." [".$r['Abreviacao']."] </td>
<td>
<select name=\"mesmoque[".$cid."]\" >
<option value='' >Selecione o nome válido</option>";

foreach ($curids as $cid2) {
	$qp = "SELECT * FROM Pessoas WHERE PessoaID=".$cid2;
	$mp = mysql_query($qp,$conn);
	$rp = mysql_fetch_assoc($mp);
	$nome = trim($rp['Prenome']." ".$rp['SegundoNome']);
	echo "
<option value='".$rp['PessoaID']."' >".$nome." [".$rp['Abreviacao']."]</option>";
}
echo "
</select>
</td></tr>
";
	$ii++;
	}
}
}
if ($ii>0) {
echo "
<tr><td align='center'>
<input type='hidden' name='final' value='' />
<input style='cursor: pointer'  type='submit' value='Não são os mesmos - pular' class='bsubmit' onclick=\"javascript:document.coletaform.final.value='1'\" /></td>
<td align='center'>
<input style='cursor: pointer'  type='submit' value='Salvar mudanças indicadas' class='bblue' onclick=\"javascript:document.coletaform.final.value='2'\" /></td>
</tr>
";
} else {
echo "
<tr><td align='center' colspan='2'>
<input style='cursor: pointer'  type='button' value='Fechar nada mais encontrado!' class='bsubmit' onclick=\"javascript:window.close();\" /></td>
</tr>";
}
echo "
</table>
</form>
<br />";


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>