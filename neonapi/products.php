<?php 

  $request = $_SERVER['REQUEST_URI'];

  echo "Request: ", $request, " ";
  
  class MyDB extends SQLite3 {
      function __construct() {
         $this->open('../db/neon_idx.db');
      }
   }
   
   $db = new MyDB();
   if(!$db){
      echo $db->lastErrorMsg();
   }

   $sql =<<<EOF
      SELECT * FROM datasets;
EOF;

   $ret = $db->query($sql);
   
   while($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
     $dataset = $row['dataset'];
     echo $dataset, " ";
   }

   $ret = $db->exec($sql);
   if(!$ret) {
      echo $db->lastErrorMsg();
   } 
   $db->close();
   
?>

