<?php

class Gravity_Flow_Checklists_Page {
	public static function render( $args ) {

		$defaults = array(
			'user_id' => absint( rgget( 'user_id' ) ),
			'checklist'  => sanitize_text_field( rgget( 'checklist' ) ),
			'form_id' => absint( rgget( 'id' ) ),
		);

		$args = array_merge( $defaults, $args );

		if ( ! empty( $args['user_id'] ) ) {
			$user_id = $args['user_id'];
			$user    = get_user_by( 'ID', $user_id );

		} else {
			$user = wp_get_current_user();
		}

		if ( ! empty( $args['checklist'] ) ) {
			$checklist_id = $args['checklist'];

			$checklist = gravity_flow_checklists()->get_checklist( $checklist_id, $user );
			if ( $checklist ) {
				$form_id = $args['form_id'];
				if ( empty( $form_id ) ) {
					require_once( gravity_flow_checklists()->get_base_path() . '/includes/class-checklists-detail.php' );
					Gravity_Flow_Checklists_Detail::display( $checklist, $args );
				} else {
					require_once( gravity_flow_checklists()->get_base_path() . '/includes/class-checklists-submit.php' );
					Gravity_Flow_Checklists_Submit::render_form( $form_id, $checklist, $args );
				}
			} else {
				esc_html_e( 'Checklist not found.', 'gravityflow' );
			}
		} else {
			require_once( gravity_flow_checklists()->get_base_path() . '/includes/class-checklists-list.php' );
			$checklists = gravity_flow_checklists()->get_checklists( $user );
			if ( $checklists ) {
				Gravity_Flow_Checklists_List::display( $checklists, $user );
			} else {
				esc_html_e( "You don't have any checklists configured.", 'gravityflow' );
			}
		}
	}
}
