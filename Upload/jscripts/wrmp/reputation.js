/*
 * Plugin Name: Who Repped My Post? for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * reloads the page after a successful rep
 */

!function($, m) {
	var $super = m.submitReputation;

	function submitReputation(uid, pid, del)
	{
		// Get form, serialize it and send it
		var datastring = $(".reputation_"+uid+"_"+pid).serialize();

		if(del == 1)
			datastring = datastring + '&delete=1';

		$.ajax({
			type: "POST",
			url: "reputation.php?modal=1",
			data: datastring,
			dataType: "html",
			success: function(data) {
				var wl = window.location.href,
					p = wl.split('#');

				window.location.assign(p[0]+"#pid"+pid);
				window.location.reload();
			},
			error: function(){
				  alert(lang.unknown_error);
			}
		});

		return false;
	}

	m.submitReputation = submitReputation;
}(jQuery, MyBB);