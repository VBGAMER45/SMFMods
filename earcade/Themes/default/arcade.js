var rate_url = smf_scripturl + "?action=arcade;sa=rate;xml";
var favorite_url = smf_scripturl + "?action=arcade;sa=favorite;xml"
var comment_url = smf_scripturl + "?action=arcade;sa=comment;xml"
var search_url = smf_scripturl + "?action=arcade;sa=search;xml"
var tour_url = smf_scripturl + "?action=arcade;sa=tour_games;xml"
var waiting = false;
var imgfav = '';
var divrate = '';

function textToEntities(text)
{
	var entities = "";
	for (var i = 0; i < text.length; i++)
	{
		if (text.charCodeAt(i) > 127)
			entities += "&#" + text.charCodeAt(i) + ";";
		else
			entities += text.charAt(i);
	}

	return entities;
}

function popup(path,w,h)
{
	arcadepopup=window.open(path,'name','height='+ h +',width='+ w +',left = 200,top = 200,resizable = 0');
	if (window.focus) {arcadepopup.focus()}
}

function arcade_tour_games(no)
{
	var i, x = new Array();
	
	if (waiting)
		return alert('Please wait before new action!');
	
	waiting = true;
		
	x[0] = "rounds=" + parseInt(no);	
	ajax_indicator(true);
	
	sendXMLDocument(tour_url, x.join("&"), on_arcade_tour_games);
}


function on_arcade_tour_games(XMLDoc)
{	
	waiting = false;
		
	var div;
	div = "tourgames";
	setInnerHTML(document.getElementById(div), XMLDoc.getElementsByTagName("txt")[0].firstChild.nodeValue);
	ajax_indicator(false);
}

// Rating

function arcade_rate(rating, game)
{
	var i, x = new Array();
	
	if (waiting)
		return alert('Please wait before new action!');
	
	waiting = true;
	x[0] = "game=" + parseInt(game);
	x[1] = "rate=" + parseInt(rating);

	ajax_indicator(true);		
	sendXMLDocument(rate_url, x.join("&"), onArcadeRate);
}

function onArcadeRate(XMLDoc)
{
	alert(XMLDoc.getElementsByTagName("txt")[0].firstChild.nodeValue);	
	
	var rating = XMLDoc.getElementsByTagName("rating")[0].firstChild.nodeValue;
	var i = 0;
	
	for (i = 1; i <= 5; i++)
	{
		if (i <= rating)
			document.getElementById('imgrate' + i).src = smf_images_url + '/arc_icons/star.gif';
		else
			document.getElementById('imgrate' + i).src = smf_images_url + '/arc_icons/star2.gif';			
	}
	
	waiting = false;
	ajax_indicator(false);
}

// Favorite

function arcade_favorite(game)
{
	var i, x = new Array();
	
	if (waiting)
		return alert('Please wait before new action!');
	
	waiting = true;
		
	x[0] = "game=" + parseInt(game);
	imgfav = 'favgame' + parseInt(game);
	
	ajax_indicator(true);
	
	sendXMLDocument(favorite_url, x.join("&"), onArcadeFavorite);
}


function onArcadeFavorite(XMLDoc)
{
	
	if (parseInt(XMLDoc.getElementsByTagName("state")[0].firstChild.nodeValue) == 0)
		document.getElementById(imgfav).src = smf_images_url + '/arc_icons/favorite.gif';

	else
		document.getElementById(imgfav).src = smf_images_url + '/arc_icons/favorite2.gif';
	
	ajax_indicator(false);
	waiting = false;
}

// Comment

var editing = false;
var editscore = 0;

function arcadeCommentEdit(score, game, save)
{
	var divcomment = "comment" + parseInt(score);
	var divedit = "edit" + parseInt(score);
	
	editscore = score;
	
	if (editing || save == 1)
		arcadeCommentSave(score)
		
	editing = true;
	
	document.getElementById(divcomment).style.display = 'none';
	document.getElementById(divedit).style.display = 'block';
}

function arcadeCommentSave(score, game)
{
	var x = new Array();
	var textbox = "c"  + parseInt(score); 
	var comment = document.getElementById(textbox).value;
	
	if (waiting)
		return alert('Please wait before new action!');
	
	waiting = true;
		
	x[0] = "game=" + parseInt(game);
	x[1] = "score=" + parseInt(score);
	x[2] = "comment=" + escape(textToEntities(comment.replace(/&#/g, "&#38;#"))).replace(/\+/g, "%2B");
			
	ajax_indicator(true);
	
	sendXMLDocument(comment_url, x.join("&"), onArcadeCommentSave);
}

function onArcadeCommentSave(XMLDoc)
{
	ajax_indicator(false);
	waiting = false;
	editing = false;
	
	var divcomment = "comment" + parseInt(editscore);
	var divedit = "edit" + parseInt(editscore);
	
	setInnerHTML(document.getElementById(divcomment), XMLDoc.getElementsByTagName("txt")[0].firstChild.nodeValue);
	document.getElementById(divcomment).style.display = 'block';
	document.getElementById(divedit).style.display = 'none';
}

// Search

var results;
var search_can = true;
var search_wait = false;
var search_div = 'quick_div';
var search_textbox = 'quick_name';

function ArcadeQuickSearch()
{

	if (search_wait) // Wait before new search.
	{
		setTimeout("ArcadeQuickSearch();", 800);
		return 1;
	}
	
	search_wait = true;
	setTimeout("resetWait();", 800);
	
	var i, x = new Array();
	
	var n = document.getElementById(search_textbox).value;
	
	x[0] = "name=" + escape(textToEntities(n.replace(/&#/g, "&#38;#"))).replace(/\+/g, "%2B");
	
	sendXMLDocument(search_url, x.join("&"), onArcadeSearch);
	
	ajax_indicator(true);	
}

function resetWait()
{
	search_wait = false;
}

function onArcadeSearch(XMLDoc)
{
	if (!XMLDoc)
		document.getElementById(search_div).innerHtml = 'Error';
	
	search_wait = false;
		
	var i;
	var html = '';

	var games = XMLDoc.getElementsByTagName("game");
	var more = XMLDoc.getElementsByTagName("more")[0];
	
	for (i = 0; i < games.length; i++)
	{
		html += "<div><a href=\"" + games[i].getElementsByTagName("url")[0].firstChild.nodeValue + "\">" + games[i].getElementsByTagName("name")[0].firstChild.nodeValue + "</a></div>";
	}
	
	if (more.getElementsByTagName("is")[0].firstChild.nodeValue == 1)
		html += "<div><a href=\"" + more.getElementsByTagName("url")[0].firstChild.nodeValue  + "\">Show all</a></div>";
	
	setInnerHTML(document.getElementById(search_div), html);
	ajax_indicator(false);	
}

function QactionChange()
{
	document.getElementById('qcategory').style.display = 'none';
	document.getElementById('qset').style.display = 'none';
	
	if (document.getElementById('qaction').value == 'change')
	{
		document.getElementById('qcategory').style.display = '';
		document.getElementById('qset').style.display = '';
	}
	else if (document.getElementById('qaction').value == 'clear_scores')
	{
		document.getElementById('qset').style.display = '';
	}
	else
	{
		
	}
	
		
}