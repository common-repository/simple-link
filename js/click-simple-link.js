function addSimpleLink(hlink,title) {
	var ed,tag,target="";
	if(document.getElementById('sl_target').checked){
		target='target="_blank"';
	}
	if ( typeof tinyMCE != "undefined" ){
		ed= tinyMCE.activeEditor;
		if(ed){
			if(document.all) {
   				tag = ed.selection.getContent();
			}
			else {
   				tag = ed.selection.getContent();
			}
			if(tag==""){
				tag=title;
				ed.focus();	
			}
			ed.selection.setContent('<a href="'+hlink+'"'+target+'>'+tag+'</a>');
		}
	}
	else if(typeof FCKeditor != "undefined"){
		ed=FCKeditorAPI.GetInstance("content");
		if(ed){
			if(document.all) {
   				tag = ed.Selection.GetSelection().createRange().text;
			}
			else {
   				tag = ed.Selection.GetSelection();
			}
			if(tag==""){
				tag=title;	
			}
			ed.InsertHtml('<a href="'+hlink+'"'+target+'>'+tag+'</a>');
		}
	}
}
