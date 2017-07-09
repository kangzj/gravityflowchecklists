<?php
/*
Plugin Name: Gravity Flow Checklists
Plugin URI: http://gravityflow.io
Description: Checklists Extension for Gravity Flow.
Version: 0.1-beta-2-dev
Author: Steve Henty
Author URI: http://gravityflow.io
License: GPL-3.0+

------------------------------------------------------------------------
Copyright 2016 Steven Henty

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'GRAVITY_FLOW_CHECKLISTS_VERSION', '0.1-beta-2-dev' );
define( 'GRAVITY_FLOW_CHECKLISTS_EDD_ITEM_NAME', 'Checklists' );

add_action( 'gravityflow_loaded', array( 'Gravity_Flow_Checklists_Bootstrap', 'load' ), 1 );

class Gravity_Flow_Checklists_Bootstrap {

	public static function load() {
		require_once( 'includes/class-checklist.php' );
		require_once( 'includes/class-checklist-personal.php' );

		require_once( 'class-checklists.php' );

		gravity_flow_checklists();
	}
}

function gravity_flow_checklists() {
	if ( class_exists( 'Gravity_Flow_Checklists' ) ) {
		return Gravity_Flow_Checklists::get_instance();
	}
}


//add_action( 'admin_init', 'gravityflow_checklists_edd_plugin_updater', 0 );

function gravityflow_checklists_edd_plugin_updater() {

	if ( ! function_exists( 'gravity_flow_checklists' ) ) {
		return;
	}

	$gravity_flow_checklists = gravity_flow_checklists();
	if ( $gravity_flow_checklists ) {
		$settings = $gravity_flow_checklists->get_app_settings();

		$license_key = trim( rgar( $settings, 'license_key' ) );

		$edd_updater = new Gravity_Flow_EDD_SL_Plugin_Updater( GRAVITY_FLOW_EDD_STORE_URL, __FILE__, array(
			'version'   => GRAVITY_FLOW_CHECKLISTS_VERSION,
			'license'   => $license_key,
			'item_name' => GRAVITY_FLOW_CHECKLISTS_EDD_ITEM_NAME,
			'author'    => 'Steven Henty',
		) );
	}

}
