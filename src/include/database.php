<?php

   require_once("../libs/ADODB/adodb.inc.php");
   
   class DB {
   
      private $dbDriver = null;
      private $adodb = null;
      
      function __construct($driver) {
      
         $this->dbDriver = $driver;
         $this->adodb = &ADONewConnection($this->dbDriver);
         
      }
      
      function connect($params) {
      
         if(!is_array($params) || count($params) < 4) {
         
            return false;
         
         } else {
         
            if(!$this->adodb->Connect($params[0], $params[1], $params[2], $params[3])) {
            
               die($this->adodb->MetaErrMsg());
               
            }
            
         }
         
      }  

   }

?>