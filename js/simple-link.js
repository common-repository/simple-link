var cat,keyword,tag,ajaxLink,isShown;
/*if (window.ActiveXObject){
		var ua = navigator.userAgent.toLowerCase();
		if(parseInt(ua.match(/msie ([\d.]+)/)[1])<7){
			jQuery("#clicklink_div").addClass("div_ie6");
		}
	}*/
jQuery(document).ready(function() {
  initialize();
  jQuery("a#sl_refresh").click(sl_refresh_handler);
	jQuery("a#clicklinks").click(sl_clicklinks_handler);
	
	jQuery("a#close_clicklinks").click(sl_close_clicklinks_handler);

	jQuery("#sl_filters .sl_filter_input").each(function(){
		jQuery(this).keypress(function(e){
			var key = window.event ? e.keyCode : e.which;
			if(key.toString() == "13"){
				if(isShown)jQuery("a#sl_refresh").trigger("click");
				else jQuery("a#clicklinks").trigger("click");
				return false;
			}
		});
	}); 
});

function sl_refresh_handler(){
  refreshButtons();
}

function sl_clicklinks_handler(){
  getAjaxLink();
  showButtons();
	return false;
}

function sl_close_clicklinks_handler(){
  hideButtons();
	return false;
}

function initialize(){
	jQuery('.postarea').prepend('<div id="clicklink_div"><h3 id="clicklink">Click Links</h3><div id="clicklink_container"></div></div>');
	jQuery("h3#clicklink").prepend('<div style="float:right;"><a href="#click_links_refresh" id="sl_refresh">'+refresh_text+'</a> <a href="#click_links" id="clicklinks">'+show_clicklink+'</a><a href="#click_links" id="close_clicklinks">'+hide_clicklink+'</a></div>');
	jQuery("#sl_filters").insertAfter("#clicklink_div h3#clicklink");
	jQuery("a#sl_refresh").hide();
	jQuery("a#close_clicklinks").hide();
	jQuery("#clicklink_container").hide();
	isShown=false;
}
function getAjaxLink(){
	cat=document.getElementById('sl_cat').value;
	keyword=document.getElementById('sl_keyword').value;
	tag=document.getElementById('sl_tag').value;
	ajaxLink=site_clicklink + '/wp-admin/admin.php?sl_ajax_action=click_links';
	if(tag){
		ajaxLink+='&sl_tag='+tag;
	}
	if(keyword){
		ajaxLink+='&sl_keyword='+keyword;
	}
	if(cat){
		ajaxLink+='&sl_cat='+cat;
	}
	return ajaxLink;
}
function showHideLink(){
	jQuery("a#clicklinks").hide();
	jQuery("a#close_clicklinks").show(); 
	jQuery("a#sl_refresh").show();
	isShown=true;
}
function showShowLink(){
	jQuery("a#clicklinks").show();
	jQuery("a#sl_refresh").hide();
	jQuery("a#close_clicklinks").hide();
	isShown=false;
}
function showButtons(){
	jQuery("#clicklink_container")
		.fadeIn('slow')
		.load(ajaxLink, function(){
			jQuery("#clicklink_container .localpost").click(function() { addSimpleLink(this.id,this.value); });
			showHideLink();
		});
}
function hideButtons(){
	jQuery("#clicklink_container").fadeOut('slow', function() {
		showShowLink();
	});
}
function refreshButtons(){
	getAjaxLink();
	jQuery("#clicklink_container")
		.load(ajaxLink,function(){
			jQuery("#clicklink_container .localpost").click(function() { addSimpleLink(this.id,this.value); });
		});
}
