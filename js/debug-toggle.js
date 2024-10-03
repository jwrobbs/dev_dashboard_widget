jQuery(document).ready(function ($) {
	$("#debugging-toggle").click(function () {
		// Make an AJAX request to execute PHP
		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: {
				action: "toggle_debugging",
				nonce: ajax_object.nonce,
			},
			success: function (response) {
				// Reload the main admin page
				location.reload();
			},
			error: function (xhr, status, error) {
				// Handle errors
				console.error(xhr.responseText);
				console.error({status});
				console.error({ error });
			},
		});
	});
});
