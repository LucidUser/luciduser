function Ajax() {

   this.get = function(container, url, data, notifierType, callback) {
      
      var notify = false;
      if(notifierType != "") { notify = true; } else { notify = false; }
      if(notify) { window.notifier.notify(notifierType); }
      $.get(url, data, function(data) {
      
         $(container).html(data);
         if(notify) { window.notifier.clear(); }
         if(callback != "") { callback(); }
         
      })
      .error(function() { if(notify) { window.notifier.clear(); } alert("Critical Error"); });
      
   };
   
   this.post = function(container, url, data, notifierType) {
   
      var notify = false;
      if(notifierType != null) { notify = true; } else { notify = false; }
      if(notify) { window.notifier.notify(notifierType); }
      $.post(url, data, function(data) {
      
         $(container).html(data);
         if(notify) { window.notifier.clear(); }
         
      })
      .error(function() { if(notify) { window.notifier.clear(); } alert("Critical Error"); });
      
   };
   
}

function AjaxNotifier() {

   if(window.THEME == "install") {
   
      this.path = "../themes/classic/images/ajax/";
      
   } else {
   
      this.path = window.THEME_PATH + "/images/ajax/";
      
   }

   this.el = '#ajax-notifier';
   this.notify = function(type) {
   
      switch(type) {
      
         case 'loading': 
         
            $(this.el).html('<img src="' + this.path + 'loading.gif" alt="Loading..." title="Loading..."><br>Loading...');
            $(this.el).addClass('notifier-loading');
            
         break;
         
         case 'processing':
         
            $(this.el).html('<img src="' + this.path + 'loading.gif" alt="Processing..." title="Processing..."><br>Processing...');
            $(this.el).addClass('notifier-loading');
            
         break;
         
      }
      
      $(this.el).show();
      
   };
   
   this.clear = function() {
   
      $(this.el).fadeOut('fast', function() {
         $(this.el).empty().removeClass();
      });
      
   };
   
}

function AjaxChecker(checks, checkBtn, cntBtn, errCnt, req2cnt, checkIndCnt, checkResCnt, checkResCntDef) {

   this.imgPath = "";
   if(window.THEME == "install") {
   
      this.imgPath = "../themes/classic";
      
   } else {
   
      this.imgPath = window.THEME_PATH;
      
   }
   this.checkPass = "<img src=\"" + this.imgPath + "/images/ajax/green-check.png\">";
   this.checkFail = "<img src=\"" + this.imgPath + "/images/ajax/red-x.png\">";
   this.checkNum = 0;
   this.stepNum = 0;
   this.numErr = 0;
   var parentThis = this;
   
   $(checkBtn).button();
   if(req2cnt == true) { $(cntBtn).button().button("disable"); } else { $(cntBtn).button(); }
   
   this.reset = function() {
   
      parentThis.checkNum = 0;
      parentThis.stepNum = 0;
      parentThis.numErr = 0;
      if(errCnt != "") { $(errCnt).hide("fast"); }
      $(checkBtn).button("disable");
      if(cntBtn != "") { if(req2cnt == true) { $(cntBtn).button("disable"); } }
      for(var i = 1; i < checks.length + 1; i++) {
      
         $(checkIndCnt + i).empty();
         $(checkResCnt + i).html(checkResCntDef);
         
      }
      
   };
   
   this.check = function() {

      if(parentThis.checkNum == 0) { window.notifier.notify("processing"); }
      if(parentThis.checkNum == checks.length) { 
      
         window.notifier.clear();
         $(checkBtn).button("enable"); 
         if(parentThis.numErr == 0) {
            
            if(req2cnt) {
            
               $(cntBtn).button("enable");
               
            }
            
         }
         else {
         
            $(errCnt).show("fast");
            
         }
         
         return; 
         
      } else {
      
         parentThis.stepNum = checks[parentThis.checkNum][0];
         $(checkIndCnt + parentThis.stepNum).html("<img src=\"" + parentThis.imgPath + "/images/ajax/progress.gif\">");
         window.ajax.get(checkResCnt + parentThis.stepNum, checks[parentThis.checkNum][1], "", "", parentThis.checkResult);
         
      }
    
   };
   
   this.checkResult = function() {
   
      var checkStr = "";
      if(checks[parentThis.checkNum][2] != "") {
         var ss = checks[parentThis.checkNum][2].split(",");
         checkStr = $(checkResCnt + parentThis.stepNum).html().substr(ss[0],ss[1]);
      } 
      else { 
         checkStr = $(checkResCnt + parentThis.stepNum).html();
      }
      if(checkStr == checks[parentThis.checkNum][3]) {
         
         $(checkIndCnt + parentThis.stepNum).html(parentThis.checkPass);
            
      } else {
         
         $(checkIndCnt + parentThis.stepNum).html(parentThis.checkFail);
         parentThis.numErr++;
            
      }
      
      parentThis.checkNum++;
      parentThis.check();
      
   };

}