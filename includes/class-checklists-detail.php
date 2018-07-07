<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class Gravity_Flow_Checklists_Detail {
	public static function display( Gravity_Flow_Checklist $checklist, $args = array() ) {

		$defaults = array(
			'breadbrumbs' => true,
		);

		$args = array_merge( $defaults, $args );

		$is_user_admin = $checklist->user->ID !== get_current_user_id();

		if ( ! $is_user_admin && ! $checklist->user_has_permission( $checklist->user->ID ) && ! gravity_flow_checklists()->current_user_can_any( 'gravityflowchecklists_user_admin' ) ) {
			esc_html_e( "You don't have permission to view this checklist", 'gravityflowchecklists' );
			return;
		}

		if ( $args['breadcrumbs'] ) {

			$query_args = array( 'checklist', 'id', 'gf_token', 'lid', 'view' );
			if ( ! is_admin() ) {
				$query_args[] = 'page';
			}
			$list_url = remove_query_arg( $query_args );

		?>
		<h2>
			<?php
			if ( $is_user_admin ) {
				?>
				<span class="dashicons dashicons-admin-users"></span> <a href="<?php echo admin_url( 'users.php' ); ?>"><?php esc_html_e( 'Users', 'gravityflowchecklists' ); ?></a> <i class="fa fa-long-arrow-right" style="color:silver"></i>
				<?php
				$checklists_name = $checklist->user->display_name;
			} else {
				$checklists_name = esc_html__( 'Checklists', 'gravityflowchecklists' );
			}
			?>
			<i class="fa fa-check-square-o"></i> <a href="<?php echo esc_url( $list_url ); ?>"><?php echo $checklists_name; ?></a> <i class="fa fa-long-arrow-right" style="color:silver"></i> <?php $checklist->icon(); ?>

			<?php
			echo $checklist->get_name();
			?>
		</h2>
			<?php } ?>
		<div class="gravityflowchecklists-checklist-detail-wrapper <?php echo $checklist->get_type(); ?>">
			<?php
			/**
			 * Fires before a checklist is rendered.
			 *
			 * @since 1.0.1
			 *
			 * @param Gravity_Flow_Checklist $checklist The checklist to be rendered.
			 * @param array                  $args      The args for display
			 */
			do_action( 'gravityflowchecklists_checklist_pre_render', $checklist, $args );
			$checklist->render( $args );
			?>
		</div>
		<?php
	}
}
