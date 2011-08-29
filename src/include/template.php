<?php

   require_once("../libs/XTPL/xtemplate.class.php");
   
   class XTPL {
   
      private $default_theme = null;
      private $theme_path = null;
      private $xtpl = null;
  
      function __construct($theme = '') {

         // TODO: auto-detect theme from db if none is specified      

         if(!empty($theme)) { $this->default_theme = $theme; } else { $this->default_theme = autodetect; }
         chdir("../");
         $this->theme_path = getcwd() . "/themes/" . $this->default_theme . "/";
      
      }

      function buildTemplate($params = '', $template) {
    
         $this->xtpl = new XTemplate($this->theme_path . $template);
         $this->xtpl->assign("THEME", $this->default_theme);
   
         if(!empty($params)) {
   
            foreach($params as $key => $value) {
      
               foreach($params[$key] as $key1 => $value1) {
            
                  if(is_array($value1)) {
                  
                     for($i = 0; $i < count($value1); $i++) {
                     
                        $xtpl->assign($key1,$value1[$i]);
                  
                     }      
                     
                  }
            
                  if(substr($value1, -4, -3) == "." || substr($value1, -5, -4) == ".") {
                  
                     $this->xtpl->assign_file($key1, $this->theme_path . $value1);
                  
                  } else { 
                     
                     $this->xtpl->assign($key1, $value1);
                     
                  }
            
               }  
               
               if($key == "main") { $block = "main"; } else { $block = "main." . $key; }
               $this->xtpl->parse($block);
        
            }
            
         } else {

            $this->xtpl->parse("main");
            
         }
         
         $this->xtpl->out("main");
      
      }
      
   }
   
?>