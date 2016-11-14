jQuery(document).ready(function(){
	jQuery("#messagepreview")
		.hide()
		.before(jQuery(".caumaMsgLock"));

	jQuery(".vermesmoassim").click(function() {
		jQuery(this).hide();
		jQuery("#messagepreview").show();
		return false;
	});

	jQuery(".remover").click(function() {
		if(rcmail.is_framed() === true){
			parent.rcmail.command("delete");
		}else{
			rcmail.command("delete");
		}
		return false;
	});
});