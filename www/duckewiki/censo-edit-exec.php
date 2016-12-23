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
$ispopup=1;
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/colorbuttons.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type='text/javascript' >
function openuser(){
	var usid = document.getElementById('responsaveltag').value;
	if (usid!='') {
		//alert(usid);
         small_window('usuario-form.php?usuarioid='+usid+'&ispopup=1&submitted=editando',600,500,'Editando usuário');
	} else {
		alert('Precisa selecionar um responsável');
	}
}
</script>"

);
$title = 'Editar Censos';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
	
///CREATE OR UPDATE TABLES SE FOR O CASO!
$qq = "CREATE TABLE IF NOT EXISTS Censos (
CensoID INT(10) unsigned NOT NULL auto_increment,
CensoNome VARCHAR(200),
ResponsavelID INT(10) NULL DEFAULT NULL COMMENT 'UserID',
CensoAcesso INT(10) NULL DEFAULT 1,
DapAcesso DOUBLE NULL DEFAULT 0,
MetaDados TEXT NULL DEFAULT '',
AddedBy INT(10),
AddedDate DATE,
PRIMARY KEY (CensoID)) CHARACTER SET utf8 ENGINE = InnoDB";
@mysql_query($qq,$conn);

$update = "ALTER TABLE `Monitoramento` ADD  `CensoID` INT(10) DEFAULT '0' AFTER `DataOBS`";
@mysql_query($update,$conn);
$qq = "ALTER TABLE `Monitoramento` ADD INDEX `data` (`DataObs`)";
@mysql_query($qq,$conn);
$update = "ALTER TABLE `ChangeMonitoramento` ADD `CensoID` INT(10) DEFAULT '0' AFTER `DataOBS`";
@mysql_query($update,$conn);
$qq = "ALTER TABLE `ChangeMonitoramento` ADD INDEX `data` (`DataObs`)";
@mysql_query($qq,$conn);

$update  = "ALTER TABLE `Censos`  ADD `CensoAcesso` INT(10) NULL DEFAULT '1' AFTER `ResponsavelID`";
@mysql_query($update,$conn);
$update  = "ALTER TABLE `Censos`  ADD `DapAcesso` DOUBLE NULL DEFAULT NULL AFTER `CensoAcesso`";
@mysql_query($update,$conn);
$update  = "ALTER TABLE `Censos`  ADD `MetaDados` TEXT NULL DEFAULT '' AFTER `DapAcesso`";
@mysql_query($update,$conn);
$update  = "ALTER TABLE `Censos`  ADD `MetaCensoID` INT(10) NULL DEFAULT NULL AFTER `MetaDados`";
@mysql_query($update,$conn);
$update  = "ALTER TABLE `Censos`  ADD `EquipePessoaID` CHAR(255) NULL DEFAULT NULL AFTER `MetaCensoID`";
@mysql_query($update,$conn);
$update  = "ALTER TABLE `Censos`  ADD `DataPolicy` TEXT NULL DEFAULT '' AFTER `EquipePessoaID`";
@mysql_query($update,$conn);


$update  = "ALTER TABLE `ChangeCensos`  ADD `CensoAcesso` INT(10) NULL DEFAULT '1' AFTER `ResponsavelID`";
@mysql_query($update,$conn);
$update  = "ALTER TABLE `ChangeCensos`  ADD `DapAcesso` DOUBLE NULL DEFAULT NULL AFTER `CensoAcesso`";
@mysql_query($update,$conn);
$update  = "ALTER TABLE `ChangeCensos`  ADD `MetaDados` TEXT NULL DEFAULT '' AFTER `DapAcesso`";
@mysql_query($update,$conn);
$update  = "ALTER TABLE `ChangeCensos`  ADD `MetaCensoID` INT(10) NULL DEFAULT NULL AFTER `MetaDados`";
@mysql_query($update,$conn);
$update  = "ALTER TABLE `ChangeCensos`  ADD `EquipePessoaID` CHAR(255) NULL DEFAULT NULL AFTER `MetaCensoID`";
@mysql_query($update,$conn);
$update  = "ALTER TABLE `ChangeCensos`  ADD `DataPolicy` TEXT NULL DEFAULT '' AFTER `EquipePessoaID`";
@mysql_query($update,$conn);

//SE ESTIVER SALVANDO
if ($salvando>0 && trim($CensoNome)!='') {
	$arrayofvalues = array(
		'CensoNome' => $CensoNome,
		'ResponsavelID' => $ResponsavelID+0,
		'CensoAcesso'  => $CensoAcesso+0,
		'DapAcesso'  => $DapAcesso+0,
		'MetaDados'  => $MetaDados,
		'EquipePessoaID' => $addcolvalue,
		'MetaCensoID' => $MetaCensoID+0,
		'DataPolicy' => $DataPolicy
	);
	$censoset=0;
	$upp = 0;
	$traitarr = explode(";",$traitids);
	$tt = 0;
	if ($censoid>0) {
		$upp = CompareOldWithNewValues('Censos','CensoID',$censoid,$arrayofvalues,$conn);
		if (($upp+0)>0) { //if new values differ from old, then update
			$updated++;
			//faz o log do registro atual
			CreateorUpdateTableofChanges($censoid,'CensoID','Censos',$conn);
			//atualiza o registro
			$tt =  UpdateTable($censoid,$arrayofvalues,'CensoID','Censos',$conn);
		} 
	} 
	else {
		echopre($arrayofvalues);
		$tt =  InsertIntoTable($arrayofvalues,'CensoID','Censos',$conn);
	}
	if ($tt) {
		echo "<div style='text-align=center; color: red; font-style: bold'  align='center'>O CENSO FOI ATUALIZADO COM SUCESSO</div>";
	
	}
}
///SE ESTIVER EDITANDO, PEGA OS DADOS VELHOS
if ($censoid>0) {
	$qz = "SELECT cc.*,FirstName,LastName,Email FROM Censos as cc LEFT JOIN Users ON Users.UserID=cc.ResponsavelID WHERE CensoID='".$censoid."'";
	$rr = mysql_query($qz,$conn);
	$rw = mysql_fetch_assoc($rr);
	@extract($rw);
	
			
	$addcolvalue = $EquipePessoaID;
	$addcolarr = explode(";",$addcolvalue);
	$addcoltxt = '';
	$j=1;
	foreach ($addcolarr as $kk => $val) {
		$qq = "SELECT * FROM Pessoas WHERE PessoaID='$val'";
		$res = mysql_query($qq,$conn);
		$rrw = mysql_fetch_assoc($res);
		if ($j==1) {
			$addcoltxt = 	$rrw['Abreviacao'];
		} else {
			$addcoltxt = $addcoltxt."; ".$rrw['Abreviacao'];
		}
		$j++;
	}

	//echopre($rw);
}
if ($novo==1) {
	$txt = 'Cadastrando um novo censo';
} else {
	$txt = 'Editando '.$CensoNome;
}
//echo $uuid."  ".$ResponsavelID."  ".$acclevel;
echo "
<br />
<form action='censo-edit-exec.php' name='finalform' method='post'>
<table align='center' cellspacing='0' cellpadding='7' class='myformtable' width='70%'>
<thead>
  <tr><td colspan='2'>".$txt."</td></tr>
</thead>
<tbody>
<tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'> 
        <td class='tdsmallboldright'>Nome para o censo</td>
        <td >
        <input type='hidden'   value='".$censoid."' name='censoid' size='60'/>
        <input type='text' value='".$CensoNome."' name='CensoNome' size='60'/></td>
</tr>";
if ($censoid>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'> 
        <td class='tdsmallboldright' >Medicoes do censo&nbsp;<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
				$help = "Indicar aqui as medições (planta+variável) que fazem parte do censo. Filtre os dados como quiser e inclua ou exclua as medicoes filtradas. Apenas medições ainda não atribuidas a um censo estarão disponíveis para inclusão";
				echo " onclick=\"javascript:alert('$help');\" /></td>";
		if ($censoid>0) {
			$qu = "SELECT COUNT(DISTINCT PlantaID) as NPlantas, COUNT(DISTINCT TraitID) AS NTraits FROM Monitoramento WHERE CensoID=".$censoid;
			$rr = mysql_query($qu,$conn);
			$rww = mysql_fetch_assoc($rr);
			@extract($rww);
			$traitstxt = "Fazem parte do censo ".$rww['NPlantas']."  plantas e ".$rww['NTraits']." variáveis ";
		} else {
			$traitstxt = '';
		}

echo "<td ><span id='nplantastxt'>".$traitstxt." </span>
&nbsp;
<input type=button style=\"cursor:pointer;\"  value='MEDIÇÕES'  onmouseover=\"Tip('Visualizar, incluir e excluir plantas do censo');\" ";
		$myurl = "censo-plantas-table-save.php?censoid=".($censoid+0);
		echo " onclick = \"javascript:small_window('".$myurl."',800,500,'Plantas do Censo');\" />
</td>
</tr>
";
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'> 
        <td class='tdsmallboldright'>Responsável pelos dados&nbsp;<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
				$help = "O email dessa pessoa será usado para contato de usuários solicitando dados do censo";
				echo " onclick=\"javascript:alert('$help');\" /></td>";
		if (($ResponsavelID+0)==0 || $ResponsavelID==$uuid || $acclevel=='admin') {
echo  "
        <td>
          <select id='responsaveltag'  name='ResponsavelID' >";
		 	if (!isset($ResponsavelID)) {
				echo "
        <option value=''>".GetLangVar('nameselect')." ".mb_strtolower(GetLangVar('nameeditar'))."</option>";
			} else {
				$wr = mysql_query("SELECT * FROM Users WHERE UserID=".$ResponsavelID,$conn);
				$ww = mysql_fetch_assoc($wr);
				echo "
        <option  selected value='".$ww['UserID']."'>".$ww['FirstName']." ".$ww['LastName']."</option>";
			}
			echo "
        <option value=''>----</option>";
			$wrr = mysql_query("SELECT * FROM Users ORDER BY FirstName,LastName",$conn);
			while ($aa = mysql_fetch_assoc($wrr)){
				echo "
        <option value='".$aa['UserID']."'>".$aa['FirstName']." ".$aa['LastName']."</option>";
			}
	echo "
    </select>
    &nbsp;<input type='button'  onclick='javascript: openuser();'  value='Ver registro do usuário'>
    </td>
";
    } elseif ($ResponsavelID>0) {
				$wr = mysql_query("SELECT * FROM Users WHERE UserID=".$ResponsavelID,$conn);
				$ww = mysql_fetch_assoc($wr);
				echo "<td><b>".$ww['FirstName']." ".$ww['LastName']."</b>  Você não tem autorização para alterar!</td>";
    }
echo "
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td class='tdsmallboldright'>Equipe participante</td>
  <td >
    <table>
      <tr>
        <td class='tdformnotes' >
          <input type='hidden' id='addcolvalue'  name='addcolvalue' value='$addcolvalue' />
        <textarea name='addcoltxt' id='addcoltxt'  cols='40' rows=2 readonly>".$addcoltxt."</textarea></td>
        <td><input type=button value=\"Selecione ou altere equipe\" class='bsubmit'  ";
		$myurl ="addcollpopup.php?valuevar=addcolvalue&addcoltxt=addcoltxt&getaddcollids=".$addcolvalue."&formname=finalform"; 
		echo " onclick = \"javascript:small_window('$myurl',800,500,'Seleciona Equipe');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'> 
        <td class='tdsmallboldright'>Metadados & Política de Acesso</td>
   <td>
    <table>
      <tr><td class='tdsmallboldright'>OPÇÃO&nbsp;1:</td><td><select name='MetaCensoID'>
            <option value=''>Usar o mesmo definido para outro censo</option>";
				$qk = "SELECT * FROM Censos";
				$rk = @mysql_query($qk,$conn);
				while ($rwk = @mysql_fetch_assoc($rk)) {
				if ($MetaCensoID==$rwk['CensoID']) {
					$optsel = "selected";
				} else {
					$optsel = '';
				}
				echo "
            <option ".$optsel." value='".$rwk['CensoID']."'>".$rwk['CensoNome']."</option>";
				}
	echo "
          </select>
        </td></tr>
      <tr><td class='tdsmallboldright'>OPÇÃO&nbsp;2:</td>
      <td><table>
        <tr><td>Metadados&nbsp;<img style='cursor: pointer;' height=\"12\" src=\"icons/icon_question.gif\" ";
$help = "Detalhe aqui metadados adicionais sobre este censo OU indique outro censo que contém essa informação, a qual será adicionada ao arquivo de metadados na exportação de dados de censos. Dados de localidade, datas, pessoas responsáveis, NÃO precisam ser incluidas aqui"; 
echo " onclick=\"javascript:alert('$help');\" /><br /><textarea name='MetaDados' cols='60' rows='3'>".$MetaDados."</textarea></td></tr>
        <tr><td>Política&nbsp;de&nbsp;acesso&nbsp;<img style='cursor: pointer;' height=\"12\" src=\"icons/icon_question.gif\" ";
$help = "Detalhe aqui a sua política de acesso a esses dados desse censo!"; 
echo " onclick=\"javascript:alert('$help');\" /><br /><textarea name='DataPolicy' cols='60' rows='3'>".$DataPolicy."</textarea></td></tr>
          </table>
        </td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'> 
  <td class='tdsmallboldright'>Acesso aos dados&nbsp;<img style='cursor: pointer;' height=\"12\" src=\"icons/icon_question.gif\" ";
   $CensoAcessoch1 = 0;
   $CensoAcessoch2 = 0;
   $CensoAcessoch3 = 0;
  if ($CensoAcesso==1) {
     $CensoAcessoch1 = 'checked';
     $txtpub = "Usuários cadastrados tem acesso";
  }
  if ($CensoAcesso==2) {
     $CensoAcessoch2 = 'checked';
     $txtpub = "Acesso é público e aberto";
  }
  if ($CensoAcesso==3) {
     $CensoAcessoch3 = 'checked';
     $txtpub = "Acesso é público e aberto, mas o nome das espécies e dos gênero são restritos";
  }
    if (($ResponsavelID+0)==0 || $ResponsavelID==$uuid || $acclevel=='admin') {
$help = "Define o tipo de acesso aos dados deste censo. Mesmo se o dado for aberto, o responsável pelos dados receberá um email informando quando alguém baixar os dados do site. DADOS ESTARÃO ABERTOS APENAS SE A PLANILHA PLOT&LOCALIDADES ESTIVER ABERTA"; 
echo " onclick=\"javascript:alert('$help');\" /></td>
  <td >
    <table>
      <tr><td><input type='radio' ".$CensoAcessoch1." value=1  name='CensoAcesso'>&nbsp;Usuários cadastrados tem acesso</td></tr>
      <tr><td><input type='radio' ".$CensoAcessoch2." value=2 name='CensoAcesso'>&nbsp;Acesso é público e aberto</td></tr>
      <tr><td><input type='radio' ".$CensoAcessoch3." value=3 name='CensoAcesso'>&nbsp;Acesso é público e aberto, mas o nome das espécies e dos gênero são restritos</td></tr>
      <tr><td>DAPmin&nbsp;<img style='cursor: pointer;' height=\"12\" src=\"icons/icon_question.gif\" ";
	$help = "Defina aqui um DAP mínimo (em cm) para a versão dos dados de acesso aberto e público, caso queira incluir esta restrição"; echo " onclick=\"javascript:alert('$help');\" />&nbsp;&nbsp;<input type='text' value='".$DapAcesso."' name='DapAcesso'></td></tr>
    </table>";
	} elseif ($ResponsavelID>0) {
$help = "APENAS O RESPONSÁVEL OU ADMINISTRADOR PODE ALTERAR OPÇÕES DE ACESSO -Define o tipo de acesso aos dados deste censo. Mesmo se o dado for aberto, o responsável pelos dados receberá um email informando quando alguém baixar os dados do site"; 
echo " onclick=\"javascript:alert('$help');\" /></td>
  <td>
    <table>
      <tr><td><b>".strtoupper($txtpub)."</td></tr>
      <tr><td>DAPmin&nbsp;<img style='cursor: pointer;' height=\"12\" src=\"icons/icon_question.gif\" ";
	$help = "Defina aqui um DAP mínimo (em cm) para a versão dos dados de acesso aberto e público, caso queira incluir esta restrição"; echo " onclick=\"javascript:alert('$help');\" />&nbsp;&nbsp;<input type='text' value='".$DapAcesso."' name='DapAcesso' readonly></td></tr>
    </table>";
	}
echo "
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' align='center'>
  <input type='hidden' value='1' name='salvando'  />
  <input type='submit' value='Enviar' class='bsubmit'  /></td>
</tr>";
echo "
</tbody>
</table>
</form>
";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>