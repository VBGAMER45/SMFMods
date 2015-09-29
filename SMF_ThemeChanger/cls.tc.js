  function cls_switch_theme_top(){
	cls_changer_top = document.getElementById('clicksafe_changer_top');
	var t_id = cls_changer_top.options[cls_changer_top.selectedIndex].value;
	var t_page = new String(window.location);
	var t_cls_reg = new RegExp("theme=([0-9]+);?");
	var t_hash = "";
	t_page = t_page.replace(t_cls_reg, "");
	if(t_page.search("#") != -1){t_hash = t_page.substr(t_page.search("#")); t_page = t_page.substr(0, t_page.search("#")); }
	while (t_page.charAt(t_page.length-1) == "?" || t_page.charAt(t_page.length-1) == ";")
		t_page = t_page.substr(0, t_page.length - 1);
	   if(t_page.search("/index.php") < (t_page.length - 10) && t_page.search("/index.php") != -1)
		  window.location = t_page + ";theme=" + t_id + t_hash;
	      else if(t_page.search("/index.php") == (t_page.length - 10))
	       	window.location = t_page + "?theme=" + t_id + t_hash;
	          else if(t_page.charAt(t_page.length-1) == "/")
		          window.location = t_page + "index.php?theme=" + t_id + t_hash;
	   else
		    window.location = t_page + "/index.php?theme=" + t_id + t_hash;
	return false;
} 

  function cls_switch_theme_bot(){

	cls_changer_bot = document.getElementById('clicksafe_changer_bot');
	var t_id = cls_changer_bot.options[cls_changer_bot.selectedIndex].value;
	var t_page = new String(window.location);
	var t_cls_reg = new RegExp("theme=([0-9]+);?");
	var t_hash = "";
	t_page = t_page.replace(t_cls_reg, "");
	if(t_page.search("#") != -1){t_hash = t_page.substr(t_page.search("#")); t_page = t_page.substr(0, t_page.search("#")); }
	while (t_page.charAt(t_page.length-1) == "?" || t_page.charAt(t_page.length-1) == ";")
		t_page = t_page.substr(0, t_page.length - 1);
	   if(t_page.search("/index.php") < (t_page.length - 10) && t_page.search("/index.php") != -1)
		  window.location = t_page + ";theme=" + t_id + t_hash;
	      else if(t_page.search("/index.php") == (t_page.length - 10))
	       	window.location = t_page + "?theme=" + t_id + t_hash;
	          else if(t_page.charAt(t_page.length-1) == "/")
		          window.location = t_page + "index.php?theme=" + t_id + t_hash;
	   else
		    window.location = t_page + "/index.php?theme=" + t_id + t_hash;
	return false;
}