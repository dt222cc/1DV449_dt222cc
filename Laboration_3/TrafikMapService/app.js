$(document).ready(function() {

	addHiddenIncidentDetails();

	function addHiddenIncidentDetails() {
		// Hide the incident details
		$('.incident-details').hide();

		// Show incident details when clicking an incident title
		$('.incident').on('click', function(event) {
			event.preventDefault();
			$('.incident-details').hide();
			var details = $(this).next();
			details.slideDown('fast');
		});
	}
});
