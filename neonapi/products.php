<?php 

  $url = $_SERVER['REQUEST_URI'];

  //echo "Request: ", $url, " ";

  $link_array = explode('/',$url);
  $productCode = end($link_array);

  if($productCode == "")
  {
    echo "Request NOT supported: requested all products!";
    exit;
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

   $sql = $db->prepare("SELECT site FROM datasets WHERE productCode = :productCode");
   $sql->bindValue(':productCode', $productCode, SQLITE3_TEXT);

   $ret = $sql->execute();

   if(!$ret) {
      echo $db->lastErrorMsg();
   } 

   $siteCodes=array();

   while($row = $ret->fetchArray(SQLITE3_ASSOC) ) {
     
     $site = $row['site'];

     $sqlsite = $db->prepare("SELECT * FROM datasets WHERE productCode = :productCode AND site = :site");
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
       
       array_push($availMonths, $year."_".$month);
       array_push($availUrls, "&server=".urlencode($server)."&dataset=".urlencode($dataset));
     }

     array_push($siteCodes, array('siteCode' => $site, 'availableMonths' => array_values($availMonths), 'availableDataUrls' => array_values($availUrls)));
     
   }

   $db->close();

   $reply = array('data' => array('productCode' => $productCode, 'siteCodes' => array_values($siteCodes)));

   echo "<pre>";
   echo json_encode($reply, JSON_PRETTY_PRINT);
   echo "</pre>";
   
   //{"data":
   //{"productCode":"DP3.30010.001","0":"siteCodes","1":[{"siteCode":"ORNL","availableMonths":["2016_2"],"availableDataUrls":["&server=https%3A%2F%2Fdataportal.sci.utah.edu&dataset=P3.30010.001-D07-2016_ORNL_2-L3-Camera-Mosaic-V1"]},{"siteCode":"TALL","availableMonths":["2019_5","2018_4","2017_3"],
?>

