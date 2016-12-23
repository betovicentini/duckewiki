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


//echopre($ppost);
//CABECALHO
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Projeto';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

//Create table if not exists
$qq = "CREATE TABLE IF NOT EXISTS Projetos (
ProjetoID INT(10) unsigned NOT NULL auto_increment,
ProjetoNome VARCHAR(100),
ProjetoURL VARCHAR(100),
Financiamento VARCHAR(500),
Processos VARCHAR(500),
LogoFile VARCHAR(100),
Equipe VARCHAR(200),
AddedBy INT(10),
AddedDate DATE,
PRIMARY KEY (ProjetoID))";

		@mysql_query($qq,$conn);

		//print_r($_FILES);
		$myfile = $_FILES['iconfile']['name'];
		if ($myfile) {
			$fn = $_FILES['iconfile']['name'];
			$importdate = date("Y-m-d");
			$newfilename = $importdate."_".$fn;
			if (!file_exists("img/logos/".$newfilename)) {
				move_uploaded_file($_FILES["iconfile"]["tmp_name"],"img/logos/".$newfilename);
			}
			$myfile = "img/logos/".$newfilename;
		}

		$finan = array();
		$proc = array();
		$ii=0;
		foreach ($agencia as $key => $val) {
			$vvv = trim($val);
			$prc = trim($processo[$key]);
			if (!empty($vvv)) {
				$arv = array($val);
				$process = array($prc);
				$finan= array_merge((array)$finan,(array)$arv);
				$proc = array_merge((array)$proc,(array)$process);
			}
			$ii++;
		}
		$financiamento = implode(";",$finan);
		$processos = implode(";",$proc);

		if (empty($myfile)) { $myfile = $logofile;}
		if (!empty($projformidmorfo)) { $pmf=implode(";",$projformidmorfo); } else { $pmf="";}
		$arrayofvalues = array(
					'ProjetoNome' => $projetonome,
					'ProjetoURL' => $projetourl,
					'Financiamento' => $financiamento,
					'Processos' => $processos,
					'LogoFile' => $myfile,
					'Equipe'=> $addcolvalue,
					'MorformsIDs' => $pmf,
					'HabitatFormID'=> $projformidhab+0
					);
					//'MorfoFormID'=> $projformidmorfo,

		/////////////////////
			if ($projetoid>0) { //se editando
				$changed = CompareOldWithNewValues('Projetos','ProjetoID',$projetoid,$arrayofvalues,$conn);
				if ($changed>0) {
					CreateorUpdateTableofChanges($projetoid,'ProjetoID','Projetos',$conn);
					$updated = UpdateTable($projetoid,$arrayofvalues,'ProjetoID','Projetos',$conn);
					if (!$updated) {
						$erro++;
					} else {
						$updated++;
					}
				}
			} else {
				//echopre($arrayofvalues);
				$sqtt = "SELECT * FROM Projetos WHERE LOWER(ProjetoNome)=".mb_strtolower($projetonome);
				$restt = mysql_query($sqtt);
				$nrestt = mysql_numrows($restt);
				if ($nrestt==0) {
				$newproject = InsertIntoTable($arrayofvalues,'ProjetoID','Projetos',$conn);
				if (!$newproject) {
					$erro++;
				} else {
					$inserted++;
				}
				} else {
					$erro++;
					$jaexiste=1;
				}
			}

			if ($erro>0) {
				if($jaexiste==1) {
				  $oerro = "Já existe um projeto com esse nome";
				}else {
					$oerro = GetLangVar('erro2');
				}				
				echo "
<br />
  <table cellpadding=\"5\" width='50%' align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>".$oerro."</td></tr>
  </table>
<br />";
				
			} 
			else {
				if ($projetoid>0) {$projid = $projetoid;} else {$projid= $newproject;}
				$erro=0;
				if (!empty($addcolvalue)) {
					$userids = explode(";",$addcolvalue);
					$qu = "SELECT GROUP_CONCAT(UserID SEPARATOR ';') as userids FROM ProjetosUsers WHERE ProjetoID=".$projetoid;
					//echo $qu."<br >";
					$ru = mysql_query($qu,$conn);
					$ruw = mysql_fetch_assoc($ru);
					$olduserids = explode(";",$ruw['userids']);
					//Echo $ruw['userids']."   nesta linha<br >";
					$toinsert = array();
					foreach($userids as $vv) { 
						$tem=0;
						foreach($olduserids as $oldvv) {
							if ($vv==$oldvv) {
								$tem++;
							}
						}
						if ($tem==0) {
							$toinsert[] = $vv;
						}
					}
					$todel = array();
					foreach($olduserids as $vv) { 
						$tem=0;
						foreach($userids as $oldvv) {
							if ($vv==$oldvv) {
								$tem++;
							}
						}
						if ($tem==0) {
							$todel[] = $vv;
						}
					}
					//echopre($userids);
					//echopre($olduserids);
					//echopre($toinsert);
					//echopre($todel);
					foreach($toinsert as $uu) {
						$uu = trim($uu);
						$qu = "INSERT INTO ProjetosUsers (ProjetoID,UserID) VALUES (".$projetoid.", ".$uu.")";
						//echo $qu."<br >";
						@mysql_query($qu,$conn);
					}
					foreach($todel as $uu) {
						$uu = trim($uu);
						$qu = "DELETE FROM ProjetosUsers WHERE ProjetoID=".$projetoid." AND UserID=".$uu;
						//echo $qu."<br >";
						@mysql_query($qu,$conn);
					}

				}
				if (!empty($especimenesids) || !empty($oldespecimensids)) {
					$oldspecids = explode(";",$oldespecimensids);
					$specids = explode(";",$especimenesids);
					foreach ($specids as $val) {
						$inold = in_array($val,$oldspecids);
						if (!$inold) {
							$qq = "SELECT * FROM Especimenes WHERE EspecimenID='$val'";
							$rrr = mysql_query($qq,$conn);
							$row = mysql_fetch_assoc($rrr);
							$oldprojetoid = trim($row['ProjetoID']);
							if ($oldprojetoid==0 || empty($oldprojetoid) || ($oldprojetoid>0 && $projid==0)) {
								$arrayofvalues = array('ProjetoID' => $projid);
								CreateorUpdateTableofChanges($val,'EspecimenID','Especimenes',$conn);
								UpdateTable($val,$arrayofvalues,'EspecimenID','Especimenes',$conn);
							}
						}
					    echo "&nbsp;";
					    flush();
					}
					if (!empty($oldespecimensids)) {
						foreach ($oldspecids as $val) {
							$inold = in_array($val,$specids);
							if (!$inold) {
								$arrayofvalues = array('ProjetoID' => 0);
								CreateorUpdateTableofChanges($val,'EspecimenID','Especimenes',$conn);
								UpdateTable($val,$arrayofvalues,'EspecimenID','Especimenes',$conn);
							}
						    echo "&nbsp;";
						    flush();
						}
					}
				}

				if (!empty($filtro) && $filtro>0) {
					//store old value into change table
					$sql = "SELECT * FROM Especimenes LIMIT 0,1";
					$res = mysql_query($sql,$conn);
					$row = mysql_fetch_assoc($res);
					$qqq = "INSERT INTO ChangeEspecimenes (";
					foreach($row as $key => $val) {
							$qqq = $qqq." ".$key.",";
					}
					$qqq = $qqq." ChangedBy, ChangedDate) SELECT Especimenes.*, ".$_SESSION['userid'].", ".$_SESSION['sessiondate']."  FROM Especimenes JOIN FiltrosSpecs as fl ON Especimenes.EspecimenID=fl.EspecimenID WHERE fl.FiltroID=".$filtro;
					$rq = mysql_query($qqq,$conn);
					if ($rq) {
						$qu = "UPDATE Especimenes JOIN FiltrosSpecs as fl ON Especimenes.EspecimenID=fl.EspecimenID SET Especimenes.ProjetoID=".$projid." WHERE fl.FiltroID=".$filtro;
						$rqq = mysql_query($qu,$conn);
						if (!$rqq) {
							$erro++;
						}
					} else {
						$erro++;
					}
					//store old value into change table
					$sql = "SELECT * FROM Plantas LIMIT 0,1";
					$res = mysql_query($sql,$conn);
					$row = mysql_fetch_assoc($res);
					$qqq = "INSERT INTO ChangePlantas (";
					foreach($row as $key => $val) {
							$qqq = $qqq." ".$key.",";
					}
					$qqq = $qqq." ChangedBy, ChangedDate) SELECT Plantas.*, ".$_SESSION['userid'].", ".$_SESSION['sessiondate']."  FROM Plantas JOIN FiltrosSpecs as fl ON Plantas.PlantaID=fl.PlantaID WHERE fl.FiltroID=".$filtro;
					$rq = mysql_query($qqq,$conn);
					if ($rq) {
						$qu = "UPDATE Plantas  JOIN FiltrosSpecs as fl ON Plantas.PlantaID=fl.PlantaID SET Plantas.ProjetoID=".$projid." WHERE fl.FiltroID=".$filtro;
						$rqq = mysql_query($qu,$conn);
						if (!$rqq) {
							$erro++;
						}
					} else {
						$erro++;
					}

				}

				if ($erro==0) {
				echo "
<br />
<table cellpadding=\"5\" align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('sucesso1')."</td></tr>
  <tr><td class='tdsmallbold' align='center'>
  <form action='projeto-form.php' method='post'>
  <input type='hidden'  value='editando' name='submitted'>
  <input type='hidden'  value='".$projetoid."' name='projetoid'> 
    <input type='submit'  value='Voltar' style='cursor: pointer;' > 
  </form></td></tr>
</table>


<br />";
				} else {
					echo "
<br />
<table cellpadding=\"5\" align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro2')."</td></tr>
  <tr><td class='tdsmallbold' align='center'>
  <form action='projeto-form.php' method='post'>
  <input type='hidden'  value='editando' name='submitted'>
  <input type='hidden'  value='".$projetoid."' name='projetoid'> 
    <input type='submit'  value='Voltar' style='cursor: pointer;' > 
  </form></td></tr>
</table>
<br />";
				}
			}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>