<?php 
/* Feeds a database with the content of a bibtex file parsed with bibtexbrowser
 * See: http://www.monperrus.net/martin/feeding-mysql-database-with-bibtexbrowser
 * Author: Martin Monperrus
 * Last Modification Date: July 2013
 * Creation Date: Feb 2012
 * MODIFICADO BETO NOV 2013
 */

// if exists, should contain define('DB_PASSWORD', 'dssizyrekzbqsf');, etc.
//@include('conf.local.php');

/** MySQL database username */
///@define('DB_USER', 'root');

/** MySQL database password */
//@define('DB_PASSWORD', 'dssizyrekzbqsf');

/** MySQL hostname */
///@define('DB_HOST', 'localhost');

/** The name of the database */
//@define('DB_NAME', 'bibliography');

/** The name of the table */
//@define('BIBTEX_TABLE', 'bibliography');

/** returns a BibDatabase object created from the content of $bibtex_file */
function init_bibtexbrowser($bibtex_file) {
  $_GET['bib'] = $bibtex_file;
  $_GET['library'] = 1;
  include('bibtexbrowser.php');
  setDB();
  $database = $_GET[Q_DB]; 
  return $database;
}

/** returns the list of fields used in the BibDatabase object $bibdb */
function get_field_list($bibdb) {
  $entries = $bibdb->bibdb;
  $result = array();
  foreach($entries as $entry) {
    foreach($entry->getFields() as $k => $v) {
      @$result[$k]++;
    }
  }
  return array_keys($result);
}

/** converts a Bibtex field name into a valid MySQL column name */
function convert_column_name($field) {
  return str_replace('-','_',$field);
}

/** sets the schema of the mysql DB based on $field_list and BIBTEX_TABLE */
function init_db($field_list, $conn) {
  //mysql_connect(DB_HOST, DB_USER , DB_PASSWORD) or die('Could not connect: ' . mysql_error());
  //mysql_select_db(DB_NAME) or die('Could not select database');
  
  // introspection
  $query = 'show tables;';
  $result = mysql_query($query, $conn) or die('Query failed: ' . mysql_error().' '.$query);
  $found = false;
  while ($line = mysql_fetch_row($result)) { 
    if ($line[0] === BIBTEX_TABLE) {
      $found = true;
    }
  }
  
  // we create the table if it does not exist
  if (!$found) {
    $query = 'CREATE TABLE '.BIBTEX_TABLE.' (bibtexkey VARCHAR(255), PRIMARY KEY (bibtexkey)) ENGINE = MyISAM DEFAULT CHARSET=UTF8;';
    $result = mysql_query($query, $conn) or die('Query failed: ' . mysql_error().' '.$query);
  }

  // introspection 2
  $query = 'show columns from '.BIBTEX_TABLE.';';
  $result = mysql_query($query, $conn) or die('Query failed: ' . mysql_error().' '.$query);
  $columns = array();
  while ($line = mysql_fetch_row($result)) { 
    $columns[] = $line[0];
  }
  
  // altering table to add missing columns
  foreach($field_list as $rfield) {
    // some fields require special naming
    $field = convert_column_name($rfield);
    if (!in_array($field,$columns) && mb_strtolower($field)!='key') {
       // altering the table
       $query = 'alter table '.BIBTEX_TABLE.' add `'.$field.'` TEXT NULL;';
       $result = mysql_query($query, $conn) or die('Query failed: ' . mysql_error().' '.$query);    
    }
  }
}

/** adds escape and quotes around an HTML string; the string is also converted to UTF-8 */
function create_mysql_string_from_bibtexbrowser_value($f) {
  return "'".mysql_real_escape_string(html_entity_decode($f,ENT_NOQUOTES,'UTF-8'))."'";
}

/** feeds a MySQL database using the content of the BibDatabase object $bibdb.
 *
 * The MySQL schema is usually created using function init_db
 */
function feed_database($bibtex_db, $conn) {

  //mysql_connect(DB_HOST, DB_USER , DB_PASSWORD) or die('Could not connect: ' . mysql_error());
  //mysql_select_db(DB_NAME) or die('Could not select database');

  //print_r($bibtex_db->bibdb);
  
  foreach($bibtex_db->bibdb as $key=>$entry) {
    // do we have an entry ?
    $query = 'select * from '.BIBTEX_TABLE.' where bibtexkey=\''.$entry->getKey().'\';';
    //echo $query;
    $result = mysql_query($query, $conn) or die('Query failed: ' . mysql_error().' '.$query);    
    //print_r($result);
    if (mysql_fetch_assoc($result) !==FALSE) {
      // updating the entry
      $fields = $entry->fields;
      $updates = array ();
      foreach ($fields as $k=>$v) { 
        if ($k!='key') {
          $column = convert_column_name($k);
          $updates[] = $column.'='.create_mysql_string_from_bibtexbrowser_value($v);
        }
      }
      echo 'updating '.$entry->getKey().'<br/>';
      $query = 'update '.BIBTEX_TABLE.' set '.implode(',',$updates).' where bibtexkey=\''.$entry->getKey().'\';';
      //echo $query;
      $result = mysql_query($query, $conn) or die('Query failed: ' . mysql_error().' '.$query);        
    } else {
      // no such key
      $fields = $entry->fields;
      $keys = array ();
      foreach (array_keys($fields) as $f) { 
        if ($f!='key') {$keys[] = convert_column_name($f);}
        else {$keys[] = 'bibtexkey';}
      }

      $values = array ();
      foreach (array_values($fields) as $f) { 
        $values[] = create_mysql_string_from_bibtexbrowser_value($f);
      }
      
      echo 'adding '.$entry->getKey().'<br/>';
      $query = 'insert into  '.BIBTEX_TABLE.'('.implode(',',$keys).')       values ('.implode(',',$values).');';
      //echo $query;
      $result = mysql_query($query, $conn) or die('Query failed: ' . mysql_error().' '.$query);    
    }
  
  } // end foreach
} // end function 

?>