/*
SMF Garage (http://www.smfgarage.com)
Version 2.0
smfg_ajax.js
*/

 $(document).ready(function(){
 
     var rebuild_parent = $("#rebuild_parent");
     var rebuild_status = $("#rebuild_images_status");
     var rebuild_form = $("#rebuild_form_div");
     
     rebuild_parent.hide();
 
     // form submitted   
     $('#smfg_rebuild_images_form').submit(function() {
     
        rebuild_parent.show();
        rebuild_form.prepend('<br />');
     
        var started = 'Regeneration starting...';
        var complete = 'Regeneration completed';
        var progress = 0;
        var progressbar = $("#progressbar");
 
        progressbar.progressbar({ value: 0 });
                
        rebuild_status.html(started);
        
        // fire the ajax
        $.ajax({
            type: "GET",
            url: "index.php?action=admin;area=garagemanagement;sa=images_xml",
            dataType: "xml",
            async: true,
            success: function(xml) {
            
                var totalNodes = $(xml).find('image').length;
                var count = 0;
                var regen = 0;
                var missing = 0;
     
                // for each image, delete and rebuild it
                $(xml).find('image').each(function(){
                    
                    count++;
                    
                    var id = $(this).attr('id');
                    var filename = $(this).attr('filename');
                    var file = $(this).attr('file');
                    var thumbname = $(this).attr('thumbname');    
                    var remote = $(this).attr('remote');                    
                    
                    $.ajax({
                         type: "GET",
                         url: "index.php?action=admin;area=garagemanagement;sa=jquery_rebuild",
                         data: "id="+id+"&filename="+filename+"&file="+file+"&thumbname="+thumbname+"&remote="+remote,
                         async: false,
                         success: function(data){
                         
                            rebuild_status.html(data);
                            
                            if(data == 0) missing++;
                                else regen++;
                            
                         }
                     });
                     
                     currentProgress = (count / totalNodes) * 100;
                     progressbar.progressbar({ value: currentProgress });
                     
                     if(totalNodes == count) {
                        rebuild_status.html('<b>'+complete+'</b>');
                        rebuild_status.append('<br />'+regen+' of '+totalNodes+' image(s) regenerated with '+missing+' missing file(s).');
                        progressbar.progressbar("destroy");
                     }
                    
                }); // end each
     
            } // end success
            
        }); // end form ajax
        
        return false;
    
    }); // end submit event
      
 }); // end dom event