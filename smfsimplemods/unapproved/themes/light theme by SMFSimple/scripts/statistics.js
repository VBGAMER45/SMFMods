$(document).ready(function() {


//Set default open/close settings
$('.acc_container').hide(); //Hide/close all containers
$('.title_barIC:first').addClass('active').next().show(); //Add "active" class to first trigger, then show/open the immediate next container

//On Click
$('.title_barIC').click(function(){
	if( $(this).next().is(':hidden') ) { //If immediate next container is closed...
		$('.title_barIC').removeClass('active').next().slideUp(); //Remove all "active" state and slide up the immediate next container
		$(this).toggleClass('active').next().slideDown(); //Add "active" state to clicked trigger and slide down the immediate next container
	}
	return false; //Prevent the browser jump to the link anchor
});    

});