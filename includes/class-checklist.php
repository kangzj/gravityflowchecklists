<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

abstract class Gravity_Flow_Checklist {

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var array
	 */
	protected $config = array();

	/**
	 * @var WP_User
	 */
	public $user;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $type;

	public function __construct( $config, WP_User $user = null ) {
		$this->id = $config['id'];
		$this->name = $config['name'];

		if ( empty( $user ) ) {
			$user = wp_get_current_user();
		}
		$this->user = $user;
		$this->config = $config;
	}

	public function icon( $echo = true ) {
		$icon = '<i class="fa fa-checklist-o fa-3x"></i>';
		if ( $echo ) {
			echo $icon;
		}
		return $icon;
	}

	public function get_name() {
		return $this->name;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_type() {
		return $this->type;
	}

	public function add_entry( $entry_id ) {
		$key = $this->get_meta_key();
		gform_update_meta( $entry_id, $key, time() );
	}

	public function remove_entry( $entry_id ) {
		$key = $this->get_meta_key();
		gform_delete_meta( $entry_id, $key );
	}

	public function get_meta_key() {
		return 'workflow_checklist_' . $this->get_id();
	}

	public function render( $args = array() ) {
	}

	public function user_has_permission( $user_id = 0 ) {

		$config = $this->config;

		$permissions = rgar( $config, 'permissions' );

		if ( empty( $permissions ) || $permissions == 'all' ) {
			return true;
		}

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$assignees = rgar( $config, 'assignees' );

		if ( array_search( 'user_id|' . $user_id, $assignees ) !== false ) {
			return true;
		}

		foreach ( gravity_flow()->get_user_roles() as $role ) {
			if ( array_search( 'role|' . $role, $assignees ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
