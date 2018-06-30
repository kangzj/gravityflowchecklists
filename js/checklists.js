(function (GravityFlowChecklists, $) {
	var strings = gravityflowchecklists_checklists_strings,
		vars = strings.vars;


	$(document).ready(function () {
		$('.gravityflowchecklists-icon').click( function() {
			var $this = $(this),
				method, checklistId, userId, formId, exempt, canExempt;
			canExempt = $this.data('can_exempt');
			if ( ! canExempt ){
				return;
			}
			exempt = $this.data('exempt');
			method = exempt ? 'DELETE' : 'POST';
			checklistId = $this.data('checklist');
			userId = $this.data('user_id');
			formId = $this.data('form_id');

			$.ajax({
				method: method,
				url: vars.root + 'gf/v2/checklists/' + checklistId + '/users/' + userId + '/forms/' + formId + '/exemptions',
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', vars.nonce );
					$this.removeClass('fa-square-o');
					$this.removeClass('fa-check-square-o');
					$this.addClass( 'fa-spinner fa-spin');
				},
				success : function( response ) {
					$this.removeClass( 'fa-spinner fa-spin');
					if ( exempt ) {
						$this.data('exempt', 0);
						$this.addClass('fa-square-o');
					} else {
						$this.data('exempt', 1);
						$this.addClass('fa-check-square-o');

					}
				},
				fail : function( response ) {
					$this.removeClass( 'fa-spinner fa-spin');
					if ( $this.hasClass( 'gravityflowchecklists-icon-incomplete') ) {
						$this.addClass('fa-square-o');
					} else {
						$this.addClass('fa-check-square-o');
					}
				}

			});
		})
	});

}(window.GravityFlowChecklists = window.GravityFlowChecklists || {}, jQuery));
