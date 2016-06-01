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
} 

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//echopre($ppost);
//echopre($gget);
//CABECALHO
$ispopup=1;
$menu = FALSE;
$title = '';
$which_css = array(
"<link rel='stylesheet' type='text/css' href='css/geral.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Herbarios';
$body= '';
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";


$qq = "CREATE TABLE IF NOT EXISTS Herbaria (
 HerbariaID INT(10) unsigned NOT NULL auto_increment,
 Sigla CHAR(50),
 IdxHerbIrn INT(10),
 FullName VARCHAR(100),
 Address VARCHAR(200),
 Phone CHAR(50),
 Email CHAR(100),
 Contact CHAR(100),
 AddedBy INT(10),
 AddedDate DATE,
 PRIMARY KEY (HerbariaID)) CHARACTER SET utf8";
 @mysql_query($qq,$conn);



$qq = "CREATE TABLE IF NOT EXISTS HerbariaEspecs (
 HerbariaEspecsID INT(10) unsigned NOT NULL auto_increment,
 HerbariaID INT(10),
 EspecimenID INT(10),
 Type CHAR(100),
 Tombamento CHAR(100),
 AddedBy INT(10),
 AddedDate DATE,
 PRIMARY KEY (HerbariaEspecsID)) CHARACTER SET utf8";
 @mysql_query($qq,$conn);

if (!isset($acao)) {
echo "
<br />
<form name='iniform' action=herbaria.php method='post'>
<input type='hidden' name='acao' value='' />
<table class='myformtable' align='center' cellpadding='5'>
<thead>
  <tr><td colspan=2>Editar ou Adicionar Herbario</td></tr>
</thead>
<tbody>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan=2>
    <select name='herbariumid' >
    <option value='' >Selecione</option>";
	$sql = "SELECT Sigla, HerbariaID FROM Herbaria ORDER BY Sigla";
	$rsql = mysql_query($sql,$conn);
	while($row = mysql_fetch_assoc($rsql)) {
		echo "
<option value='".$row['HerbariaID']."' >".$row['Sigla']."</option>";
	}
	echo "    
    </select>
   </td>
</tr>  
<tr>
      <td align='center' >
        <input type='submit' value='Atualiza cadastro' class='bsubmit' onclick=\"javascript:document.iniform.acao.value=1\" />&nbsp;<img height='13' src='icons/icon_question.gif'";
	$help = "O registro do herbário selecionado será atualizado do Index Herbariorum";
	echo " onclick=\"javascript:alert('$help');\" />
      </td>
      <td align='left'>
        <input type='submit' value='Cadastrar novo' class='bblue' onclick=\"javascript:document.iniform.acao.value=2\" />
      </td>
    </tr>
  </table>
  </td>
</tr>
</tbody>
</table>
</form>
";
} else {
	//echopre($ppost);
	$idxtxt = "";
	//CRIA NOVO REGISTRO SE FOR OPCAO
	if (!isset($saveit) && $acao==2) {
		echo "
<br />
<form name='fillform' action=herbaria.php method='post'>
<input type='hidden' name='acao' value='".$acao."' />
<input type='hidden' name='saveit' value='1' />
<input type='hidden' name='herbariumid' value='".$herbariumid."' />
<table class='myformtable' align='center' cellpadding='5'>
<thead>
  <tr><td colspan=2>Adicionar Herbario</td></tr>
</thead>
<tbody>";
//if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
//	echo "
//<tr bgcolor = '".$bgcolor."'>
// <td class='tdsmallbold'>Sigla do herbário*</td>
//  <td><input style='height: 20px; width: 200px; font-weight: bold; font-size: 1.1em;' name='sigla' value='".$sigla."' ></td>
//</tr>";  
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallbold'>Index herbariorum ID*&nbsp;<img height='12' src=\"icons/icon_question.gif\" ";
	$help = "O valor da variável IRN do registro do herbario no index herbariorum. Procure pela sua sigla e encontre o caminho do registro. Por exemplo, para o INPA o irn é 124921, que você encontra no final da url endereço http://sweetgum.nybg.org/science/ih/herbarium_details.php?irn=124921";
	echo "onclick=\"javascript:alert('$help');\" /></td>
  <td><input style='height: 20px; width: 200px;' name='IdxHerbIrn' value='".$IdxHerbIrn."' >$idxtxt</td>
</tr>
<tr>
  <td align='center' colspan=2 ><input type='submit' value='Salvar' class='bsubmit' /></td>
</tr>
</tbody>
</table>
</form>
";
	} else {
			if ($acao==1 && $herbariumid>0) {
				$sql = "SELECT * FROM Herbaria WHERE HerbariaID=".$herbariumid;
				$rsql = mysql_query($sql,$conn);
				$rsqlr = mysql_fetch_assoc($rsql);
				$sigla = $rsqlr['Sigla'];
				$IdxHerbIrn = $rsqlr['IdxHerbIrn'];
			}
			//$idxtxt = "<br /><a href=\"http://sweetgum.nybg.org/science/ih/herbarium_details.php?irn=".$IdxHerbIrn."\">Link para Index Herbariorum</a>";
			//echo $idxtxt."<br>";
			//set_time_limit(0); // unlimited max execution time
			$urliherb= 'http://sweetgum.nybg.org/science/ih/herbarium.txt.php?irn='.$IdxHerbIrn;
			$homepage = file_get_contents($urliherb);
			$xxx = explode("\n",$homepage);
			$xxx = array_values($xxx);
			$kzadd = 0;
			$herbtitle = $xxx[0];
			$arrtosave = array("Sigla" => "", "IdxHerbIrn" => $IdxHerbIrn, "FullName" => $herbtitle, "Address" => "", "Phone" => "", "Email"=> "", "Contact" => "");
			 foreach($xxx as $kk => $vv) {
			 		$vvv = explode(":",$vv);
					if ($vvv[0]=="Phone") {
						$phone = $vvv[1];
						$kzadd = $kk-1;
						 $endereco = array();
						 for($k=1;$k<=$kzadd;$k++) { 
							 	$vt = trim($xxx[$k]);
							 	if (!empty($vt)) {
				 				$endereco[] = $vt;
						 	}
						 }
						 $arrtosave["Phone"] = $phone;
						//echo "phone:".$phone."<br >";
						$addr = implode("\n",$endereco);
						$arrtosave["Address"] = $addr;
						//echo "adress:".$addr."<br >";
					}
					if ($vvv[0]=="Email") {
						$email = trim($vvv[1]);
						//echo "email:".$vvv[1]."<br >";
						$arrtosave["Email"] = $email;
						
					}
					if ($vvv[0]=="Herbarium Code") {
						$asigla = trim($vvv[1]);
						//echo "asigla:".$asigla."<br >";
						$arrtosave["Sigla"] = $asigla;
					}
					if ($vvv[0]=="Correspondent(s)") {
						$oct = explode(",",$vvv[1]);
						if (count($oct)>1 && count($oct)==3) {
							$contato = trim($oct[0]);
							//echo $contato."<br >";
							$arrtosave["Contact"] = $contato;
							if (empty($email)) {
								$email = trim($oct[2]);
								$arrtosave["Email"] = $email;
								//echo "email:".$email."<br >";
							}
						}
					}
			 }
			 $temsigla = trim($arrtosave['Sigla']);
			 if (!empty($temsigla)) {
			 		//se houve na base trata com atualização
				 	$sql = "SELECT * FROM Herbaria WHERE IdxHerbIrn=".$IdxHerbIrn;
					$rsql = mysql_query($sql,$conn);
					$rsqlr = mysql_numrows($rsql);
					if ($rsqlr>0 && (!isset($herbariumid) || $herbariumid==0 || empty($herbariumid))) {
						$rws = mysql_fetch_assoc($rsql);
						$herbariumid = $rws['HerbariaID'];
					}
					
					
			  //se novo
			if (!isset($herbariumid) || $herbariumid==0 || empty($herbariumid)) {  
				$newspec = InsertIntoTable($arrtosave,'HerbariaID','Herbaria',$conn);
				if (!$newspec) {
					echo "<br><table cellpadding=\"1\" width='50%' align='center' class='erro'>
				<tr><td class='tdsmallbold' align='center'>O cadastro de ".$asigla." ".GetLangVar('erro2')."</td></tr>
				</table><br>
				";
					$erro++;
				} else {
					echo "<p class='success'>O cadastro de ".$asigla." ".GetLangVar('sucesso1')."</p>";
				}
			}  else {
				$changed = CompareOldWithNewValues('Herbaria','HerbariaID',$herbariumid,$arrtosave,$conn);
				if ($changed>0 && !empty($changed)) { //se mudou atualiza
					CreateorUpdateTableofChanges($herbariumid,'HerbariaID','Herbaria',$conn);
					$updatespecid = UpdateTable($herbariumid,$arrtosave,'HerbariaID','Herbaria',$conn);
					if (!$updatespecid) {
						$erro++;
					} else {
						echo "<p class='success'>O cadastro de ".$asigla." ".GetLangVar('sucesso1')."</p>";
					}
				} else { //nao mudou nada
					echo "<br><table cellpadding=\"1\" width='60%' align='center' class='erro'>
				<tr><td align='center'>O cadastro de ".$asigla." ".GetLangVar('messagenochange')."</td></tr>
				</table>";
				}
			
			}
			} else {
					echo "<br><table cellpadding=\"1\" width='60%' align='center' class='erro'>
				<tr><td align='center'>O código informado parece não ser válido</td></tr>
				</table>";
			}
		   echo "<br><table cellpadding=\"1\" width='60%' align='center' >
<tr><td align='center'><form action='herbaria.php' method='post'><input class='bsubmit' type='submit' value='Voltar' ></form></td><td><input class='bblue' type='button'  onclick='javascript:window.close();'  value='Fechar'></td></tr></table>";

	}

}
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>