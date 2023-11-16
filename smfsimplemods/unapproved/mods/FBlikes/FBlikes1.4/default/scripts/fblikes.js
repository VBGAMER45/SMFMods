function fblikejs(data)
{	
	var dataJson = eval(data);	
	$.ajax({
		type: "POST",
		async:true,
		url: dataJson.sourcedir+"?action=fblike",
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
		data: "topic="+dataJson.topic+"&"+dataJson.sesvar+"="+dataJson.sesid
	})		
	.done(function(data) {
	$('#fblike').fadeIn(3000, function(){
			$('#fblike').html(data+'<span class="contador"></span>');
			mostrarContador(dataJson.tiemporesta)
		});
	
	});
}
function mostrarContador(segundos){
    if (segundos > 0){
        $('#fblike .contador').text(segundos);
        segundos--;    
        setTimeout(function(){mostrarContador(segundos)}, 1000);               
    }
    else{
        location.reload();       
    }
}

