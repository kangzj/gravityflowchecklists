<?php

/**
 * Provides REST API access to Checklists functionality.
 *
 *
 * @since 1.0-beta-2
 *
 * @copyright   Copyright (c) 2015-2018, Steven Henty S.L.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 * Class Gravity_Flow_REST_API
 */
class Gravity_Flow_Checklists_REST_API {

	public function init() {
		add_action( 'rest_api_init', array( $this, 'action_rest_api_init' ) );
	}

	public function action_rest_api_init() {
		register_rest_route( 'gf/v2', '/checklists/(?P<checklist_id>[\w-]+)/users/(?P<user_id>\d+)/forms/(?P<form_id>\d+)/exemptions', array(
			'methods' => 'POST',
			'callback' => array( $this, 'update_user_exemption' ),
			'permission_callback' => array( $this, 'update_user_exemption_permission_check' ),
		) );
		register_rest_route( 'gf/v2', '/checklists/(?P<checklist_id>[\w-]+)/users/(?P<user_id>\d+)/forms/(?P<form_id>\d+)/exemptions', array(
			'methods' => WP_REST_Server::DELETABLE,
			'callback' => array( $this, 'update_user_exemption' ),
			'permission_callback' => array( $this, 'update_user_exemption_permission_check' ),
		) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 */
	public function update_user_exemption_permission_check( $request ) {

		/**
		 * Filters the capability required to update user exemptions via the REST API.
		 *
		 * @since 1.0-beta-2
		 */
		$capability = apply_filters( 'graityflowchecklists_rest_api_capability_update_user_exemption', 'gravityflowchecklists_user_admin', $request );

		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|string|WP_REST_Response
	 */
	public function update_user_exemption( $request ) {

		$checklist_id = sanitize_text_field( $request['checklist_id'] );

		if ( empty( $checklist_id ) ) {
			return new WP_Error( 'missing_checklist_id', __( 'Missing Checklist ID', 'gravityflowchecklists' ) );
		}

		$user_id = absint( $request['user_id'] );

		if ( empty( $user_id ) ) {
			return new WP_Error( 'missing_user_id', __( 'Missing User ID', 'gravityflowchecklists' ) );
		}

		$form_id = absint( $request['form_id'] );

		if ( empty( $form_id ) ) {
			return new WP_Error( 'missing_form_id', __( 'Missing Form ID', 'gravityflowchecklists' ) );
		}

		wp_cache_flush();

		$exemptions = get_user_meta( $user_id, 'gravityflowchecklists_exemptions', true );

		$original_exemptions = $exemptions;

		if ( empty( $exemptions ) ) {
			$exemptions = array();
		}

		$method = $request->get_method();

		if ( $method == 'DELETE' ) {
			if ( isset( $exemptions[ $form_id ] ) ) {
				$exemption = $exemptions[ $form_id ];
				unset( $exemptions[ $form_id ] );
				update_user_meta( $user_id, 'gravityflowchecklists_exemptions', $exemptions, $original_exemptions );

				/**
				 * Fires after an exemption has been removed.
				 *
				 * @since 1.1
				 *
				 * @param int $user_id The user for which the exemption removed.
				 * @param int $form_id The ID of the form for which the exemption was removed.
				 * @param string $checklist_id The ID of the checklist for which the exemption was removed.
				 * @param array $exemption The details of the exemption including the user ID of the user who added the exemption and the timestamp.
				 */
				do_action( 'gravityflowchecklists_post_remove_exemption', $user_id, $form_id, $checklist_id, $exemption );
			} else {
				return new WP_Error( 'nothing_to_delete',  __( 'Nothing to delete', 'gravityflowchecklists' ) );
			}
		} else {
			$exemption = array(
				'exempted_by_user_id' => get_current_user_id(),
				'timestamp' => time(),
				'checklist' => $checklist_id,
			);
			$exemptions[ $form_id ] = $exemption;
			update_user_meta( $user_id, 'gravityflowchecklists_exemptions', $exemptions, $original_exemptions );

			/**
			 * Fires after an exemption has been added.
			 *
			 * @since 1.1
			 *
			 * @param int $user_id The user for which the exemption added.
			 * @param int $form_id The ID of the form for which the exemption was added.
			 * @param string $checklist_id The ID of the checklist for which the exemption was added.
			 * @param array $exemption The details of the exemption including the user ID of the user who added the exemption and the timestamp.
			 */
			do_action( 'gravityflowchecklists_post_add_exemption', $user_id, $form_id, $checklist_id, $exemption );
		}

		$response = rest_ensure_response( $exemption );
		return $response;
	}
}
