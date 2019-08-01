<?php 
  /*
 * Copyright (c) 2019 University of Utah 
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of the copyright holder nor the names of its
 *    contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */
  
  $url = $_SERVER['REQUEST_URI'];

  //echo "Request: ", $url, " ";

  $link_array = explode('/',$url);
  $productCode = end($link_array);

  if($productCode == "" or $productCode == "products.php" or $productCode == "products")
  {
    $reply = array('data' => array("DP3.30010.001"));
    echo json_encode($reply);
    die();
  } else if($productCode != "DP3.30010.001"){ // TODO check DB before giving a 404 answer!
    http_response_code(404);
    //include('my_404.php'); // provide your own HTML for the error page
    die();
  }
  
  class MyDB extends SQLite3 {
      function __construct() {
         $this->open('../db/neon_idx.db');
      }
   }
   
   $db = new MyDB();
   if(!$db){
      echo $db->lastErrorMsg();
   }

   $sql = $db->prepare("SELECT DISTINCT site FROM datasets WHERE productCode = :productCode AND publish = 1 ORDER BY site ASC, year ASC, month ASC;");
   $sql->bindValue(':productCode', $productCode, SQLITE3_TEXT);

   $ret = $sql->execute();

   if(!$ret) {
      echo $db->lastErrorMsg();
   } 

   $siteCodes=array();

   while($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
     
     $site = $row['site'];

     $sqlsite = $db->prepare("SELECT * FROM datasets WHERE productCode = :productCode AND site = :site AND publish = 1");
     $sqlsite->bindValue(':productCode', $productCode, SQLITE3_TEXT);
     $sqlsite->bindValue(':site', $site, SQLITE3_TEXT);

     $retsite = $sqlsite->execute();

     if(!$retsite) {
        echo $db->lastErrorMsg();
     } 

     $availMonths=array();
     $availUrls=array();

     while($rowsite = $retsite->fetchArray(SQLITE3_ASSOC) ) {
       $dataset = $rowsite['dataset'];
       $server = $rowsite['server'];
       $year = $rowsite['year'];
       $month = $rowsite['month'];
       $pc = $rowsite['productCode'];
       
       array_push($availMonths, $year."-".sprintf('%02d', $month));
       array_push($availUrls, "server=".urlencode($server)."&dataset=".urlencode($dataset));
     }

     array_push($siteCodes, array('siteCode' => $site, 'availableMonths' => array_values($availMonths), 'availableDataUrls' => array_values($availUrls)));
     
   }

   $db->close();

   $reply = array('data' => array('productCode' => $productCode, 'siteCodes' => array_values($siteCodes)));
/*
   // hardcoded example for testing (ignoring database content)
   $reply = array('data' => array('productCode' => $productCode, 'siteCodes' => array(
   	    array( 'siteCode' => 'MOAB', 
	         'availableMonths' => array('2017-09','2018-08'), 
		 'availableDataUrls' => array('server=https%3A%2F%2Fmolniya.sci.utah.edu%2Fmod_visus%3F&dataset=neon_moab_2017_2018&time=2017', 'server=https%3A%2F%2Fmolniya.sci.utah.edu%2Fmod_visus%3F&dataset=neon_moab_2017_2018&time=2018')
		 ) ,
            array( 'siteCode' => 'ABBY', 
	         'availableMonths' => array('2018-07'), 
		 'availableDataUrls' => array('server=https%3A%2F%2Fmolniya.sci.utah.edu%2Fmod_visus%3F&dataset=neon_abba&time=0')
		 ) 
		 )
            ) );
   */

   header("Access-Control-Allow-Origin: *");
   header('Content-Type: application/json');
   echo json_encode($reply);

?>

