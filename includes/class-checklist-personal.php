<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class Gravity_Flow_Checklist_Personal extends Gravity_Flow_Checklist {

	/**
	 * @var string
	 */
	protected $type = 'personal';

	/**
	 * @var array
	 */
	protected $entry_ids = null;

	/**
	 * @var bool
	 */
	protected $sequential;

	/**
	 * @var array
	 */
	protected $nodes = array();

	public function __construct( $config, WP_User $user = null ) {
		parent::__construct( $config, $user );
		$this->sequential = (bool) rgar( $config, 'sequential' );

		if ( isset( $config['nodes'] ) ) {
			$this->nodes = $config['nodes'];
		}
	}

	public function get_entries() {

		if ( isset( $this->entries ) ) {
			return $this->entries;
		}

		$search_criteria = array(
			'field_filters' => array(
				array(
					'key'      => 'created_by',
					'value'    => $this->user->ID,
					'operator' => '=',
				),
			),
		);

		$form_ids = array();

		foreach ( $this->nodes as $node ) {
			$form_ids[] = $node['form_id'];
		}

		$entries = GFAPI::get_entries( $form_ids, $search_criteria );

		$this->entries = $entries;
		return $this->entries;
	}

	public function get_entry_ids() {
		global $wpdb;

		if ( isset( $this->entry_ids ) ) {
			return $this->entry_ids;
		}

		$entry_table = $this->get_entry_table_name();
		$clauses = array();

		foreach ( $this->nodes as $node ) {
			$form_id = $node['form_id'];
			$clause = $wpdb->prepare( "(SELECT group_concat(id ORDER BY id DESC SEPARATOR ',' ) from $entry_table WHERE form_id=%d AND created_by=%d AND status='active') as form_%d", $form_id, $this->user->ID, $form_id );
			$clauses[] = $clause;
		}

		$sql = 'SELECT ' . join( ', ', $clauses );

		$results = $wpdb->get_results( $sql );

		$ids = isset( $results[0] ) ? $results[0] : array();

		foreach ( $this->nodes as $node ) {
			$form_id                     = $node['form_id'];
			$key                         = 'form_' . $form_id;
			$this->entry_ids[ $form_id ] = isset( $ids->{$key} ) ? explode( ',', $ids->{$key} ) : null;
		}

		return $this->entry_ids;
	}

	public function get_entry_ids_for_form( $form_id ) {
		$entry_ids = $this->get_entry_ids();
		return $entry_ids[ $form_id ];
	}

	public function get_entries_for_form( $form_id ) {
		$entries_for_form = array();
		$entries = $this->get_entries();
		foreach ( $entries as $entry ) {
			if ( $entry['form_id'] == $form_id ) {
				$entries_for_form[] = $entry;
			}
		}
		return $entries_for_form;
	}

	public function render( $args = array() ) {
		$can_submit = true;

		$items = array();

		$previous_entry = null;

		foreach ( $this->nodes as $node ) {
			$form_id = $node['form_id'];
			$form      = GFAPI::get_form( $form_id );
			$entries = $this->get_entries_for_form( $form_id );

			$has_workflow      = false;
			$workflow_complete = false;

			$exempt = false;

			if ( empty( $entries ) ) {

				// No entries yet so the form may be ready to submit

				$exempt = $this->is_exempt( $form_id );

				$icon_classes = $exempt ? 'gravityflowchecklists-icon-complete fa-check-square-o' : 'gravityflowchecklists-icon-incomplete fa-square-o';

				$icon = sprintf( '<i class="gravityflowchecklists-icon %s fa fa-fw" data-checklist="%s" data-user_id="%d" data-form_id="%d" data-exempt="%d"></i>', $icon_classes, $this->get_id(), $this->user->ID, $form_id, $exempt );

				if ( $can_submit && ! $exempt ) {
					$url = add_query_arg( array( 'id' => $form_id ) );

					/**
					 * Allows the URL to the form to be modified.
					 *
					 * @since 1.0-beta-2
					 *
					 * @param string $url The URL of the form.
					 * @param array $forms The Form array.
					 * @param array $entries The entries submitted by the current user for this form.
					 * @param bool $exempt Whether the user is exempt from submitting this form.
					 */
					$url = apply_filters( 'gravityflowchecklists_form_url', $url, $form, $entries, $exempt );

					$form_title = $icon . ' ' . sprintf( '<a href="%s">%s</a>',  esc_url( $url ), esc_html( $form['title'] ) );

					$item = $form_title;
				} else {
					$item = $icon . ' ' . sprintf( '<span class="gravityflowchecklists-disabled">%s</span>', esc_html( $form['title'] ) );
				}
			} else {

				// The form has been submitted already

				$steps = gravity_flow()->get_steps( $form_id );

				$has_workflow = ! empty( $steps );

				$entry = $entries[0];

				$workflow_complete = false;

				if ( $has_workflow && isset( $entry['workflow_final_status'] ) && $entry['workflow_final_status'] != 'pending' ) {
					$workflow_complete = true;
				}

				$icon = '<i class="gravityflowchecklists-icon-complete fa fa-fw fa-check-square-o"></i>';

				$url = add_query_arg( array(
					'lid'  => $entry['id'],
					'page' => 'gravityflow-inbox',
					'view' => 'entry',
				) );

				/**
				 * Allows the URL to the entry to be modified.
				 *
				 * @since 1.0-beta-2
				 *
				 * @param string $url The URL of the entry.
				 * @param array $forms The Form array.
				 * @param array $entries All the entries submitted by the current user for this form.
				 * @param bool $exempt Whether the user is exempt from submitting this form.
				 */
				$url = apply_filters( 'gravityflowchecklists_entry_url', $url, $form, $entries, $exempt );

				$form_title = esc_html( $form['title'] );

				if ( ! isset( $node['linkToEntry'] ) || ( isset( $node['linkToEntry'] ) && $node['linkToEntry'] ) ){
					$form_title = sprintf( '<a href="%s">%s</a>', esc_url( $url ), $form_title );
				}

				$item = $icon . ' ' . $form_title;

				if ( $has_workflow && ! $workflow_complete ) {
					$item .= ' ' . sprintf( '<span class="gravityflowchecklists-processing">%s</span>', esc_html__( '(Processing)', 'gravityflow' ) );
				} else {
					$date = $entry['date_created'];
					if ( ! empty( $entry['workflow_timestamp'] ) ) {
						$last_updated = date( 'Y-m-d H:i:s', $entry['workflow_timestamp'] );
						if ( $entry['date_created'] != $last_updated ) {
							$date = $last_updated;
						}
					}

					$item .= ' ' . sprintf( '<span class="gravityflowchecklists-date">%s</span>', esc_html( GFCommon::format_date( $date, true, 'Y/m/d' ) ) );
				}
			} // End if().

			$items[] = sprintf( '<li>%s</li>', $item );

			$wait_for_workflow_complete = (bool) rgar( $node, 'waitForWorkflowComplete' );

			if ( $this->sequential && ( empty( $entries ) || ( $has_workflow && $wait_for_workflow_complete && ! $workflow_complete ) ) && ! $exempt) {
				$can_submit = false;
			}
		} // End foreach().

		$list = '<ul>%s</ul>';

		printf( $list, join( "\n", $items ) );
	}

	public function is_complete() {
		$is_complete = true;
		$entries = array();
		$node = array();
		foreach ( $this->nodes as $node ) {
			$form_id = $node['form_id'];
			$entries = $this->get_entries_for_form( $form_id );
			if ( empty( $entries ) ) {
				$is_complete = false;
				break;
			}
		}

		if ( rgar( $node, 'waitForWorkflowComplete' ) ) {
			$last_entry = $entries[0];
			if ( isset( $last_entry['workflow_final_status'] ) && $last_entry['workflow_final_status'] != 'pending' ) {
				$is_complete = false;
			}
		}

		return $is_complete;
	}

	public function get_entry_table_name() {
		return gravity_flow_checklists()->get_entry_table_name();
	}

	public function is_exempt( $form_id ) {
		$exemptions = get_user_meta( $this->user->ID, 'gravityflowchecklists_exemptions', true );
		return isset( $exemptions[ $form_id ] );
	}
}
