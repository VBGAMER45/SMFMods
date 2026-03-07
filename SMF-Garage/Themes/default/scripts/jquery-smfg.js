/*
SMF Garage (http://www.smfgarage.com)
Version 2.2
jquery-smfg.js
*/
 
 $(document).ready(function(){
    
    /* ------------------------------*/

    // On Click Event
    $(".imageGallery").click(function() {
        $("#mainImageContainer").find("a").attr("href", $(this).attr("href")); // link to current image
        $("#mainImageContainer").find("img").attr("src", $(this).attr("href")).fadeIn(); // change image source
        
        Shadowbox.setup();// must do this since we changed the image sources
        
        return false;
    });
    
    /* ------------------------------*/
    
    $(function(){
        $(".smfg_imageTitle").tipTip({maxWidth: "auto", edgeOffset: 10, defaultPosition: "right", fadeIn: 50});
        $(".smfg_videoTitle").tipTip({maxWidth: "auto", edgeOffset: 10, defaultPosition: "right", fadeIn: 50});
    });
    
    /* ------------------------------*/
    
    $('.editin').editable('?action=garage;sa=update_text', {
         cancel    : 'Cancel',
         submit    : 'OK',
         indicator : 'Saving...',
         tooltip   : 'Click to edit...'
     });
    
    /* ------------------------------*/
      
 });