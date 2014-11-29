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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
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
AddedBy INT(10),
AddedDate DATE,
PRIMARY KEY (CensoID)) CHARACTER SET utf8 ENGINE = InnoDB";
@mysql_query($qq,$conn);

$update = "ALTER TABLE Monitoramento ADD COLUMN CensoID INT(10) DEFAULT 0 AFTER DataOBS";
@mysql_query($update,$conn);
$qq = "ALTER TABLE `Monitoramento` ADD INDEX `data` (`DataObs`)";
@mysql_query($qq,$conn);


//SE ESTIVER SALVANDO
if ($salvando>0 && trim($CensoNome)!='') {
	$arrayofvalues = array(
		'CensoNome' => $CensoNome,
		'ResponsavelID' => $ResponsavelID
	);
	$censoset=0;
	$upp = 0;
	$traitarr = explode(";",$traitids);
	if ($censoid>0) {
		$upp = CompareOldWithNewValues('Censos','CensoID',$censoid,$arrayofvalues,$conn);
		if (($upp+0)>0) { //if new values differ from old, then update
			$updated++;
			//faz o log do registro atual
			CreateorUpdateTableofChanges($censoid,'CensoID','Censos',$conn);
			//atualiza o registro
			$censoid = UpdateTable($censoid,$arrayofvalues,'CensoID','Censos',$conn);
		} 
	} 
	else {
		$censoid = InsertIntoTable($arrayofvalues,'CensoID','Censos',$conn);
	}
}
///SE ESTIVER EDITANDO, PEGA OS DADOS VELHOS
if ($censoid>0) {
	$qz = "SELECT cc.*,FirstName,LastName,Email FROM Censos as cc LEFT JOIN Users ON Users.UserID=cc.ResponsavelID WHERE CensoID='".$censoid."'";
	$rr = mysql_query($qz,$conn);
	$rw = mysql_fetch_assoc($rr);
	@extract($rw);
}
if ($novo==1) {
	$txt = 'Cadastrando um novo censo';
} else {
	$txt = 'Editando '.$CensoNome;
}
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
<input type=button style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='MEDIÇÕES'  onmouseover=\"Tip('Visualizar, incluir e excluir plantas do censo');\" ";
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
        <option value=''>".GetLangVar('nameselect')." ".strtolower(GetLangVar('nameeditar'))."</option>";
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
				echo "<td>".$ww['FirstName']." ".$ww['LastName']."  você não tem autorização para mudar isso</td>";
    }
echo "
</tr>
";
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

$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
);
FazFooter($which_java,$calendar=TRUE,$footer=$menu);

?>