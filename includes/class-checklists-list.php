<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class Gravity_Flow_Checklists_List {

	/**
	 * @param Gravity_Flow_Checklist[] $checklists
	 * @param WP_User $user
	 */
	public static function display( $checklists, $user ) {
		if ( $user->ID !== get_current_user_id() ) {
			?>
			<h2>
			<span class="dashicons dashicons-admin-users"></span> <a href="<?php echo admin_url( 'users.php' ); ?>"><?php esc_html_e( 'Users', 'gravityflowchecklists' ); ?></a> <i class="fa fa-long-arrow-right" style="color:silver"></i>
			<?php
			echo  $user->display_name;
			?>
			</h2>
			<?php
		}
		?>

		<div class="gravityflowchecklists-checklist-wrapper">
			<?php
			/**
			 * Fires before a checklist is rendered.
			 *
			 * @since 1.0.1
			 *
			 * @param Gravity_Flow_Checklist[] $checklists The checklist to be rendered.
			 * @param WP_User                  $args       The args for display
			 */
			do_action( 'gravityflowchecklists_list_pre_render', $checklists, $user );
			?>
			<ul>
				<?php
				foreach ( $checklists as $checklist ) {
					if ( ! $checklist->user_has_permission( $user->ID ) ) {
						gravity_flow_checklists()->log_debug( __METHOD__ . '(): the current user does not have permission for this checklist: ' . $checklist->get_name() );
						continue;
					}
					echo '<li>';
					$detail_url = add_query_arg( 'checklist', $checklist->get_id() );
					$detail_url = esc_url( $detail_url );
					?>
					<a href="<?php echo $detail_url; ?>">

						<div class="gravityflowchecklists-checklist-container">
							<div>
								<?php $checklist->icon( 5 ); ?>
							</div>
							<div>
								<?php
								echo $checklist->get_name();
								?>
							</div>
						</div>

					</a>

					<?php
					echo '</li>';
				}
				?>
			</ul>
		</div>
		<?php
	}
}
