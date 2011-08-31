<?php

   require_once("../libs/XTPL/xtemplate.class.php");
   
   class XTPL {
   
      private $default_theme = null;
      private $theme_path = null;
      private $xtpl = null;
  
      function __construct($theme = '') {
      
         if(!empty($theme)) { $this->default_theme = $theme; } else { $this->default_theme = autodetect; }
         chdir("../");
         $this->theme_path = getcwd() . "/themes/" . $this->default_theme . "/";
      
      }

      function buildTemplate($params = '', $template) {
      
         $this->xtpl = new XTemplate($this->theme_path . $template);
         $this->xtpl->assign("THEME", $this->default_theme);
         $tp = substr("../" . getRelativePath(getcwd(),$this->theme_path),0,-1);
         $rp = substr("../" . getRelativePath(getcwd(),getcwd() . "/"),0,-1);
         $this->xtpl->assign("THEME_PATH", $tp);
         $this->xtpl->assign("SCRIPT_PATH", $rp);
         $this->xtpl->assign("JS", "
      <script type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js\"></script>
      <script type=\"text/javascript\" src=\"https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js\"></script>
      <script type=\"text/javascript\" src=\"" . $rp . "/js/lib-ajax.js\"></script>
      <script type=\"text/javascript\">
     
         var THEME = \"\"; var THEME_PATH = \"\"; var SCRIPT_PATH = \"\"; var ajax = \"\"; var notifier = \"\";
     
         $(document).ready(function() {
         
            window.THEME = \"" . $this->default_theme . "\";
            window.THEME_PATH = \"" . $tp . "\";
            window.SCRIPT_PATH = \"" . $rp . "\";
            window.ajax = new Ajax();
            window.notifier = new AjaxNotifier();

         });

      </script>");

   
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