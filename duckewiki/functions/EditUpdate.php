<?php

//create table that track changes to any table in the database
function CreateorUpdateTableofChanges($id,$idcolname,$table,$dbname,$conn) {
	
	$dbname = $_SESSION['dbname'];
	$userid = $_SESSION['userid'];
	$sessiondate = $_SESSION['sessiondate'];
	//$table is any table in the database
	//$idcolname is the name of the field in $table that reference its items (PRIMARY KEY OF TABLE)
	//$id is the id in the $idcolname that is being changed and will be added to the change table, as a new record or appended if table has been already created 
	//get userid and date to store who is making the change
	//create table if not exist and add change fields (add word 'Changes' to start of table name as default pattern for change tables
	$changetable = "Change".$table;
	$qq = "USE $dbname";
	mysql_query($qq,$conn);	
	$qq = "CREATE TABLE IF NOT EXISTS $changetable LIKE $table";
	$rr = mysql_query($qq,$conn);	
	if ($rr) {
		//add fields that track who made changes
		$qq="ALTER TABLE ".$dbname.".".$changetable."  ADD COLUMN ChangeID INT(10) NOT NULL, ADD COLUMN ChangedBy INT(10), ADD COLUMN ChangedDate DATE";
		mysql_query($qq,$conn);	
		$qq = "ALTER TABLE $changetable CHANGE $idcolname $idcolname INT( 10 ) UNSIGNED NOT NULL";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE ".$dbname.".".$changetable." DROP PRIMARY KEY, ADD PRIMARY KEY (ChangeID)";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE ".$dbname.".".$changetable."  CHANGE `ChangeID` `ChangeID` INT( 10 ) NOT NULL AUTO_INCREMENT ";
		mysql_query($qq,$conn);
	}
	
	//store old value into change table
	$sql = "SELECT * FROM $table WHERE $idcolname='$id'";
	$res = mysql_query($sql,$conn);
	$row = mysql_fetch_assoc($res);
	//print_r($row);
	$qqq = "INSERT INTO $changetable (";
	foreach($row as $key => $val) {
		$qqq = $qqq." ".$key.",";
	}
	$qqq = $qqq." ChangedBy, ChangedDate) VALUES (";
	foreach($row as $key => $val) {
		$qqq = $qqq." '".$val."',";
	}
	$qqq = $qqq." '$userid', '$sessiondate')";
	//echo "<br>here $qqq";
	mysql_query($qqq,$conn);
}

function UpdateTable($id,$fieldsaskeyofvaluearray,$idcolname,$table,$conn) {
	$dbname = $_SESSION['dbname'];
	$userid = $_SESSION['userid'];
	$sessiondate = $_SESSION['sessiondate'];
	$forkeyoff = "SET FOREIGN_KEY_CHECKS=0";
		mysql_query($forkeyoff,$conn);
	$qqq = "UPDATE db_$dbname.$table SET";
		foreach($fieldsaskeyofvaluearray as $key => $val) {
			$qqq = $qqq." ".$key."= '".$val."', ";
		}
		$qqq = $qqq." AddedBy='$userid', AddedDate='$sessiondate' WHERE $idcolname='$id'";
	$res = mysql_query($qqq,$conn);
	$forkeyonn = "SET FOREIGN_KEY_CHECKS=1";
	mysql_query($forkeyoff,$conn);
	return $res;
}	

function table_exists($tablename,$conn) {
    $database = $_SESSION['dbname'];
    $qq = "SELECT COUNT(*) AS count FROM information_schema.tables WHERE table_schema = '$database' AND table_name = '$tablename'";
    //echo $qq;
    $res = mysql_query($qq,$conn);
    $nr = mysql_numrows($res);
    return $nr;
}

function ShowChangesinTable($id,$idcolname,$table,$conn) {
	$dbname = $_SESSION['dbname'];
	$changetable = "Change".$table;
	$qq = "USE $dbname";
	mysql_query($qq,$conn);	
	$rr = table_exists($changetable,$conn);
	if ($rr==0) {
		echo "Este registro nunca foi modificado!";
	} else {
		//display each change in table
		$sql = "SELECT * FROM $changetable WHERE $idcolname='$id' ORDER BY ChangedDate DESC";
		$res = mysql_query($sql,$conn);
		if (!$res) {
			$nres = -1;
		} else {
			$nres = mysql_numrows($res);
		}
		if ($nres<0) {
			echo "Este registro nunca foi modificado!";
		} else {
			//table with current values
			$query = "SELECT * FROM $table WHERE $idcolname='$id'";
			$result = mysql_query($query,$conn);
			$currentvalues  = mysql_fetch_assoc($result); 
			//for each change, compare with 
				//for ($i = 0 ; $i <=  $nres-1; $i++) {
				$i = 0;
				unset($pr);
				while ($changes = mysql_fetch_assoc($res)) {
					//$changes = mysql_fetch_assoc($res);
					$colnames = array_keys($changes);
					$oldervalues = array_values($changes);
					$ncols = count($colnames);
					$changeddate = $changes['ChangedDate'];
					$changedby = $changes['ChangedBy'];
					if ($i==0) {
						$newervalues = @array_values($currentvalues);
						$_SESSION['newervalues']= $oldervalues;
					} else {
						$newervalues=$_SESSION['newervalues'];
						$_SESSION['newervalues']= $oldervalues;
					}
					$i++;
					for ($j = 0 ; $j <= $ncols - 1; $j++)  {
						$colname = $colnames[$j];
						$oldervalue = $oldervalues[$j];
						$newervalue = $newervalues[$j];
							$query = "SELECT * FROM db_users.Users WHERE UserID='$changedby'";
							$rr = mysql_query($query);
							$rrr  = mysql_fetch_assoc($rr);
							$usuario = $rrr['UserName'];
						//echo "<br>Colname = $colname";
						//echo "<br><b>Colname = $colname $newervalue  $oldervalue </b>";

						if (trim($newervalue)!==trim($oldervalue)) {
							if (strtoupper($colname)=='CHANGEDBY' || strtoupper($colname)=='CHANGEDDATE' || strtoupper($colname)=='CHANGEID'
							|| strtoupper($colname)=='ADDEDBY' || strtoupper($colname)=='ADDEDDATE') {} else {
								if (empty($pr)) {
								echo "<b>Modifica&ccedil;&otilde;es recentes deste registro</b>:<br>
									<table><tr id='tabheadresult'>
										<td>Campo</td><td>Mudou de</td><td>Para</td><td>Data</td><td>Usu&aacute;rio</td></tr>";
										$pr=1;
								}
								echo "<tr id='tdresult'><td>&nbsp;&nbsp;".strtoupper($colname)."&nbsp;&nbsp;</td><td>&nbsp;&nbsp;$oldervalue&nbsp;&nbsp;</td><td>&nbsp;&nbsp;$newervalue&nbsp;&nbsp;</td><td>&nbsp;&nbsp;$changeddate&nbsp;&nbsp;</td><td>&nbsp;&nbsp;$usuario&nbsp;&nbsp;</td></tr>";
							}
						}
					}
				}
				if (!empty($pr)) {
					echo "</table>";
				} else {
							echo "Este registro nunca foi modificado!";
				}
			}
		}
}

?>

