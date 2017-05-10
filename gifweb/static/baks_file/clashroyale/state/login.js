function do_login_ptbus() {
	if(window.top != window) {
		try{
			window.top.showDiv();
			return true;
		}
		catch(e){return false;}
	}
	return false;
}

function do_quit_ptbus() {
	var logout_api = 'http://i.ptbus.com/uc_api/logout?callback=?';
	$.getJSON(logout_api,function(data){
		if(data.status=='ok'){
			window.top.location.reload();
		}else if(data.status=='fail'){
			
		}
	});
	return true;
}