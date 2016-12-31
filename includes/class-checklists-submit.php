<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class Gravity_Flow_Checklists_Submit {
	/**
	 * @param $form_id
	 * @param Gravity_Flow_Checklist $checklist
	 * @param array $args
	 */
	public static function render_form( $form_id, $checklist, $args ) {
		$list_url = remove_query_arg( 'checklist' );
		$checklist_url = remove_query_arg( 'id' );
		$defaults = array(
			'breadcrumbs' => true,
		);

		$args = array_merge( $defaults, $args );

		if ( $args['breadcrumbs'] ) {
			?>
			<h2>
				<i class="fa fa-check-square-o"></i>
				<a href="<?php echo esc_url( $list_url ); ?>">Checklists</a>
				<i class="fa fa-long-arrow-right" style="color:silver"></i>
				<?php $checklist->icon(); ?>
				<a href="<?php echo esc_url( $checklist_url ); ?>"><?php echo $checklist->get_name(); ?></a>

			</h2>
			<?php
		}
		gravity_form_enqueue_scripts( $form_id );
		gravity_form( $form_id );
	}
}
