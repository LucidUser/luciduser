<?php

   include("../include/template.php");
   include("../include/functions.php");
   $xtpl = new XTPL("install");
   $params = array("header" => array(
                                       "TITLE" => "LucidUser Setup"
                                    ),
                   "main"   => array(
                                       "BLOCKLEFT" => "block-left.html"
                                    )
             );
   $xtpl->buildTemplate($params, "header.html");
   $xtpl->buildTemplate("", "footer.html"); 
   
?>