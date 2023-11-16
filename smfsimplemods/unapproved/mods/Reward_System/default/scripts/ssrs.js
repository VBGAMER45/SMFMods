function ssrs_points(data,points)
{
	var dataSsrs = eval(data);
	var points = points;
	var ss = jQuery.noConflict();
	ss.ajax({
		type: "POST",
		async:false,
		url: dataSsrs.sourcedir+"?action=ssrspergoodpost",
		error:function(x,e){
			if(x.status==0){
			alert('You are offline!!n Please Check Your Network.');
			}else if(x.status==404){
			alert('Requested URL not found.');
			}else if(x.status==500){
			alert('Internel Server Error.');
			}else if(e=='parsererror'){
			alert('Error.nParsing JSON Request failed.');
			}else if(e=='timeout'){
			alert('Request Time out.');
			}else {
			alert('Unknow Error.n'+x.responseText);
			}
		},
		data: "topic="+dataSsrs.topic+"&to="+dataSsrs.id_start+"&points="+points+"&"+dataSsrs.sesvar+"="+dataSsrs.sesid,
		dataType: 'json',
		success: function(data){
			ss('#total_points').fadeIn(3000, function(){
				ss('#total_points').html(data.total);
			});
			ss('#give_point_space').fadeIn(3000, function(){
				ss('#give_point_space').html(data.message);
			});
			ss('#show_givers').fadeIn(3000, function(){
				ss('#show_givers').html(data.whogives);
			});
		}
	});
}