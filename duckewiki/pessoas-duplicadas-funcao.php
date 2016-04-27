<?php

function table_exist($table, $conn){
    $sql = "show tables like '".$table."'";
    $res = mysql_query($sql,$conn);
    $nrw = mysql_numrows($res);
    return ($nrw > 0);
}

function mergepessoa($idtoreplace,$idofreplace, $conn) {

//EM ESPECIMENS
$qu = "UPDATE Especimenes SET ColetorID=".$idofreplace." WHERE ColetorID=".$idtoreplace;
$ru = mysql_query($qu,$conn);
$qu = "UPDATE ChangeEspecimenes SET ColetorID=".$idofreplace." WHERE ColetorID=".$idtoreplace;
$ru = mysql_query($qu,$conn);

$qu = "UPDATE Especimenes SET AddColIDS=pessoaduplicata(AddColIDS,".$idtoreplace.",".$idofreplace.")  WHERE pessoainfield(AddColIDS,".$idtoreplace.")>0";
$ru = mysql_query($qu,$conn);
$qu = "UPDATE ChangeEspecimenes SET AddColIDS=pessoaduplicata(AddColIDS,".$idtoreplace.",".$idofreplace.")  WHERE pessoainfield(AddColIDS,".$idtoreplace.")>0";
$ru = mysql_query($qu,$conn);

//IDENTIDADES
$qu = "UPDATE Identidade SET DetbyID=".$idofreplace." WHERE DetbyID=".$idtoreplace;
$ru = mysql_query($qu,$conn);
$qu = "UPDATE Identidade SET RefColetor=".$idofreplace." WHERE RefColetor=".$idtoreplace;
$ru = mysql_query($qu,$conn);
$qu = "UPDATE Identidade SET RefDetby=".$idofreplace." WHERE RefDetby=".$idtoreplace;
$ru = mysql_query($qu,$conn);

//IMAGENS
$qu = "UPDATE Imagens SET Autores=pessoaduplicata(Autores,".$idtoreplace.",".$idofreplace.") WHERE pessoainfield(Autores,".$idtoreplace.")>0";
$ru = mysql_query($qu,$conn);
$qu = "UPDATE ChangeImagens SET Autores=pessoaduplicata(Autores,".$idtoreplace.",".$idofreplace.") WHERE pessoainfield(Autores,".$idtoreplace.")>0";
$ru = mysql_query($qu,$conn);

//if (table_exist("MetodoExpedito", $conn)) {
//EXPEDITO
$qu = "UPDATE MetodoExpedito SET PessoasIDs=pessoaduplicata(PessoasIDs,".$idtoreplace.",".$idofreplace.") WHERE pessoainfield(PessoasIDs,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);
$qu = "UPDATE ChangeMetodoExpedito SET PessoasIDs=pessoaduplicata(PessoasIDs,".$idtoreplace.",".$idofreplace.") WHERE pessoainfield(PessoasIDs,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);
//}

$qu = "UPDATE MetodoExpeditoPlantas SET PessoasIDs=pessoaduplicata(PessoasIDs,".$idtoreplace.",".$idofreplace.") WHERE pessoainfield(PessoasIDs,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);
$qu = "UPDATE ChangeMetodoExpeditoPlantas SET PessoasIDs=pessoaduplicata(PessoasIDs,".$idtoreplace.",".$idofreplace.") WHERE pessoainfield(PessoasIDs,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);

//PLANTAS
$qu = "UPDATE Plantas SET TaggedBy=pessoaduplicata(TaggedBy,".$idtoreplace.",".$idofreplace.") WHERE pessoainfield(TaggedBy,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);
$qu = "UPDATE ChangePlantas SET TaggedBy=pessoaduplicata(TaggedBy,".$idtoreplace.",".$idofreplace.") WHERE pessoainfield(TaggedBy,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);

//PROJETOS
$qu = "UPDATE Projetos SET Equipe=pessoaduplicata(Equipe,".$idtoreplace.",".$idofreplace.") WHERE pessoainfield(Equipe,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);
$qu = "UPDATE ChangeProjetos SET Equipe=pessoaduplicata(Equipe,".$idtoreplace.",".$idofreplace.") WHERE pessoainfield(Equipe,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);

//ESPECIALISTAS
$qu = "UPDATE Especialistas SET Especialista=".$idofreplace." WHERE Especialista=".$idtoreplace;
$ru = @mysql_query($qu,$conn);
$qu = "UPDATE ChangeEspecialistas SET Especialista=".$idofreplace." WHERE Especialista=".$idtoreplace;
$ru = @mysql_query($qu,$conn);

//GRUPOS DE ESPECIES
$qu = "UPDATE Tax_SpeciesGroups SET PessoaID=".$idofreplace." WHERE PessoaID=".$idtoreplace;
$ru = @mysql_query($qu,$conn);
$qu = "UPDATE ChangeTax_SpeciesGroups SET PessoaID=".$idofreplace." WHERE PessoaID=".$idtoreplace;
$ru = @mysql_query($qu,$conn);

//USUARIOS QUE TAMBEM SAO PESSOAS
$qu = "UPDATE Users SET PessoaID=".$idofreplace." WHERE PessoaID=".$idtoreplace;
$ru = @mysql_query($qu,$conn);
$qu = "UPDATE ChangeUsers SET PessoaID=".$idofreplace." WHERE PessoaID=".$idtoreplace;
$ru = @mysql_query($qu,$conn);

//EQUIPAMENTOS
$qu = "UPDATE Equipamentos SET PessoaID=".$idofreplace." WHERE PessoaID=".$idtoreplace;
$ru = mysql_query($qu,$conn);
$qu = "UPDATE ChangeEquipamentos SET PessoaID=".$idofreplace." WHERE PessoaID=".$idtoreplace;
$ru = mysql_query($qu,$conn);


//APAGA O REGISTRO ENTAO SE NAO HOUVER MAIS
//EM ESPECIMENS
$qu = "SELECT * FROM  Especimenes WHERE pessoainfield(AddColIDS,".$idtoreplace.")>0 OR ColetorID=".$idtoreplace;
$ru = @mysql_query($qu,$conn);
$nru = @mysql_numrows($ru);
$nrr = 0;
if ($nru>0) {
	#echo 'especimenes<br />';
	$nrr = $nrr+$nru;
}
//IDENTIDADES
$qu = "SELECT * FROM  Identidade WHERE  DetbyID=".$idtoreplace." OR RefColetor=".$idtoreplace." OR RefDetby=".$idtoreplace;
$ru = @mysql_query($qu,$conn);
$nru = @mysql_numrows($ru);
if ($nru>0) {
	$nrr = $nrr+$nru;
	//echo 'identidade<br />';
}
//IMAGENS
$qu = "SELECT * FROM   Imagens WHERE pessoainfield(AddColIDS,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);
$nru = @mysql_numrows($ru);
if ($nru>0) {
	$nrr = $nrr+$nru;
	//echo 'Imagens<br />';
}
//EXPEDITO
$qu = "SELECT * FROM    MetodoExpedito WHERE pessoainfield(PessoasIDs,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);
$nru = @mysql_numrows($ru);
if ($nru>0) {
	$nrr = $nrr+$nru;
	//echo 'MetodoExpedito<br />';
}

$qu = "SELECT * FROM  MetodoExpeditoPlantas WHERE pessoainfield(PessoasIDs,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);
$nru = @mysql_numrows($ru);
if ($nru>0) {
	$nrr = $nrr+$nru;
	//echo 'MetodoExpeditoPlantas<br />';
}


//PLANTAS
$qu = "SELECT * FROM  Plantas WHERE pessoainfield(TaggedBy,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);
$nru = @mysql_numrows($ru);
if ($nru>0) {
	$nrr = $nrr+$nru;
	//echo 'Plantas<br />';
}

//PROJETOS
$qu = "SELECT * FROM Projetos WHERE pessoainfield(Equipe,".$idtoreplace.")>0";
$ru = @mysql_query($qu,$conn);
$nru = @mysql_numrows($ru);
if ($nru>0) {
	$nrr = $nrr+$nru;
	//echo 'Projetos<br />';
}


//ESPECIALISTAS
$qu = "SELECT * FROM Especialistas WHERE Especialista=".$idtoreplace;
$ru = @mysql_query($qu,$conn);
$nru = @mysql_numrows($ru);
if ($nru>0) {
	$nrr = $nrr+$nru;
	//echo 'Especialistas<br />';
}

//GRUPOS DE ESPECIES
$qu = "SELECT * FROM Tax_SpeciesGroups WHERE PessoaID=".$idtoreplace;
$ru = @mysql_query($qu,$conn);
$nru = @mysql_numrows($ru);
if ($nru>0) {
	$nrr = $nrr+$nru;
	//echo 'Tax_SpeciesGroups<br />';
}

//USUARIOS QUE TAMBEM SAO PESSOAS
$qu = "SELECT * FROM Users WHERE PessoaID=".$idtoreplace;
$ru = @mysql_query($qu,$conn);
$nru = @mysql_numrows($ru);
if ($nru>0) {
	$nrr = $nrr+$nru;
	//echo 'Users<br />';
}

//EQUIPAMENTOS
$qu = "SELECT * FROM Equipamentos WHERE PessoaID=".$idtoreplace;
$ru = @mysql_query($qu,$conn);
$nru = @mysql_numrows($ru);
if ($nru>0) {
	$nrr = $nrr+$nru;
	//echo 'Equipamentos<br />';
}
if ($nrr==0) {
	CreateorUpdateTableofChanges($idtoreplace,'PessoaID','Pessoas',$conn);
	$qdel = "DELETE FROM Pessoas WHERE PessoaID=".$idtoreplace;
	//echo $qdel."<br />";
	$rd = mysql_query($qdel,$conn);
	if ($rd) {
		return(1);
	} else {
		return(0);
	}
} else {
	return(0);
}

}
?>