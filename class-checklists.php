<?php
/**
 * Gravity Flow Checklists
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Extension
 * @copyright   Copyright (c) 2015-2018, Steven Henty S.L.
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0
 */

// Make sure Gravity Forms is active and already loaded.
if ( class_exists( 'GFForms' ) ) {

	class Gravity_Flow_Checklists extends Gravity_Flow_Extension {

		private static $_instance = null;

		public $_version = GRAVITY_FLOW_CHECKLISTS_VERSION;

		public $edd_item_name = GRAVITY_FLOW_CHECKLISTS_EDD_ITEM_NAME;

		/**
		 * The item name used by Easy Digital Downloads.
		 *
		 * @since 1.1.1
		 *
		 * @var string
		 */
		public $edd_item_id = GRAVITY_FLOW_CHECKLISTS_EDD_ITEM_ID;

		/**
		 * Holds the license key for the current installation.
		 *
		 * Set with a constant e.g. GRAVITY_FLOW_EXTENSION_LICENSE_KEY in the extension bootstrap file.
		 *
		 * @since 1.1.1
		 *
		 * @var string
		 */
		public $license_key = '';

		// The Framework will display an appropriate message on the plugins page if necessary
		protected $_min_gravityforms_version = '1.9.10';

		protected $_slug = 'gravityflowchecklists';

		protected $_path = 'gravityflowchecklists/checklists.php';

		protected $_full_path = __FILE__;

		// Title of the plugin to be used on the settings page, form settings and plugins page.
		protected $_title = 'Checklists Extension';

		// Short version of the plugin title to be used on menus and other places where a less verbose string is useful.
		protected $_short_title = 'Checklists';

		protected $_capabilities = array(
			'gravityflowchecklists_checklists',
			'gravityflowchecklists_uninstall',
			'gravityflowchecklists_settings',
			'gravityflowchecklists_user_admin',
		);

		protected $_capabilities_app_settings = 'gravityflowchecklists_settings';
		protected $_capabilities_uninstall = 'gravityflowchecklists_uninstall';

		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new Gravity_Flow_Checklists();
			}

			return self::$_instance;
		}

		private function __clone() {
		} /* do nothing */


		public function init() {
			parent::init();

			add_action( 'gravityflow_enqueue_frontend_scripts', array(
				$this,
				'action_gravityflow_enqueue_frontend_scripts'
			) );
			add_action( 'gform_incomplete_submission_post_save', array( $this, 'save_uuid' ), 10, 4 );
		}

		public function init_frontend() {
			parent::init_frontend();
			add_filter( 'gravityflow_shortcode_checklists', array( $this, 'shortcode' ), 10, 2 );
			add_filter( 'gravityflow_enqueue_frontend_scripts', array(
				$this,
				'action_gravityflow_enqueue_frontend_scripts'
			), 10 );
		}

		public function init_admin() {
			parent::init_admin();
			if ( $this->current_user_can_any( 'gravityflowchecklists_user_admin' ) ) {
				add_filter( 'user_row_actions', array( $this, 'filter_user_row_actions' ), 10, 2 );
			}
		}

		public function upgrade( $previous_version ) {
			if ( version_compare( $previous_version, '1.0-beta-2', '<' ) ) {
				$this->upgrade_10beta2();
			}
		}

		public function upgrade_10beta2() {
			$settings_dirty    = false;
			$app_settings      = $this->get_app_settings();
			$checklist_configs = $this->get_checklist_configs();
			foreach ( $checklist_configs as &$checklist_config ) {
				$nodes = rgar( $checklist_config, 'nodes' );
				if ( ! empty( $nodes ) ) {
					foreach ( $nodes as &$node ) {
						if ( ! isset( $node['linkToEntry'] ) ) {
							$node['linkToEntry'] = true;
						}
						$node['waitForWorkflowComplete'] = false;
					}
					$checklist_config['nodes'] = $nodes;
					$settings_dirty            = true;
				}
			}

			if ( $settings_dirty ) {
				if ( ! is_array( $app_settings ) ) {
					$app_settings = array();
				}
				$app_settings['checklists'] = $checklist_configs;
				$this->update_app_settings( $app_settings );
			}
		}

		public function scripts() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

			if ( $this->is_settings_page() ) {
				$forms = GFFormsModel::get_forms();

				$form_choices = array(
					array(
						'value' => '',
						'label' => __( 'Select a form', 'gravityflowchecklists' )
					)
				);
				foreach ( $forms as $form ) {
					$steps          = gravity_flow()->get_steps( $form->id );
					$has_workflow   = ! empty( $steps );
					$form_choices[] = array(
						'value'       => $form->id,
						'label'       => $form->title,
						'hasWorkflow' => $has_workflow,
					);
				}

				$user_choices = $this->get_users_as_choices();
				$scripts[]    = array(
					'handle'  => 'gravityflowchecklists_settings_js',
					'src'     => $this->get_base_url() . "/js/checklist-settings-build{$min}.js",
					'version' => $this->_version,
					'deps'    => array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-tabs' ),
					'enqueue' => array(
						array( 'query' => 'page=gravityflow_settings&view=gravityflowchecklists' ),
					),
					'strings' => array(
						'vars'                    => array(
							'forms'       => $form_choices,
							'userChoices' => $user_choices,
						),
						'checklistName'           => __( 'Name', 'gravityflowchecklists' ),
						'customLabel'             => __( 'Custom Label', 'gravityflowchecklists' ),
						'forms'                   => __( 'Forms', 'gravityflowchecklists' ),
						'form'                    => __( 'Form', 'gravityflowchecklists' ),
						'personal'                => __( 'Personal', 'gravityflowchecklists' ),
						'shared'                  => __( 'Shared', 'gravityflowchecklists' ),
						'sequential'              => __( 'Sequential', 'gravityflowchecklists' ),
						'options'                 => __( 'Options', 'gravityflowchecklists' ),
						'waitForWorkflowComplete' => __( 'Block sequence until Workflow is complete', 'gravityflowchecklists' ),
						'noItems'                 => __( "You don't have any checklists.", 'graviytflowchecklists' ),
						'addOne'                  => __( "Let's add one", 'graviytflowchecklists' ),
						'areYouSure'              => __( 'This item will be deleted. Are you sure?', 'graviytflowchecklists' ),
						'defaultChecklistName'    => __( 'New Checklist', 'graviytflowchecklists' ),
						'allUsers'                => __( 'All Users', 'gravityflowchecklists' ),
						'selectUsers'             => __( 'Select Users', 'gravityflowchecklists' ),
						'newChecklistItem'        => __( 'New Checklist Item', 'gravityflow' ),
						'linkToEntry'             => __( 'Link to entry', 'gravityflow' ),
						'permissions'             => __( 'Permissions', 'gravityflow' ),
					),
				);
			}

			$scripts[] = array(
				'handle'  => 'gravityflow_status_list',
				'src'     => gravity_flow()->get_base_url() . "/js/status-list{$min}.js",
				'deps'    => array( 'jquery', 'gform_field_filter' ),
				'version' => $this->_version,
				'enqueue' => array(
					array(
						'query' => 'page=gravityflow-checklists&checklist=_notempty_',
					),
				),
				'strings' => array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ),
			);

			$scripts[] = array(
				'handle'  => 'gravityflowchecklists_checklists',
				'src'     => $this->get_base_url() . "/js/checklists{$min}.js",
				'deps'    => array( 'jquery' ),
				'version' => $this->_version,
				'enqueue' => array(
					array(
						'query' => 'page=gravityflow-checklists&checklist=_notempty_',
					),
				),
				'strings' => array(
					'vars' => array(
						'root'  => esc_url_raw( rest_url() ),
						'nonce' => wp_create_nonce( 'wp_rest' ),
					),
				),
			);

			return array_merge( parent::scripts(), $scripts );
		}

		public function styles() {
			$min    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
			$styles = array(
				array(
					'handle'  => 'gravityflowchecklists_settings_css',
					'src'     => $this->get_base_url() . "/css/settings{$min}.css",
					'version' => $this->_version,
					'enqueue' => array(
						array( 'query' => 'page=gravityflow_settings&view=gravityflowchecklists' ),
					),
				),
				array(
					'handle'  => 'gform_admin',
					'src'     => GFCommon::get_base_url() . "/css/admin{$min}.css",
					'version' => GFForms::$version,
					'enqueue' => array(
						array(
							'query' => 'page=gravityflow-checklists',
						),
					),
				),
				array(
					'handle'  => 'gravityflowchecklists_checklists',
					'src'     => $this->get_base_url() . "/themes/default/style{$min}.css",
					'version' => GFForms::$version,
					'enqueue' => array(
						array(
							'query' => 'page=gravityflow-checklists',
						),
					),
				),
				array(
					'handle'  => 'gravityflow_status',
					'src'     => gravity_flow()->get_base_url() . "/css/status{$min}.css",
					'version' => $this->_version,
					'enqueue' => array(
						array(
							'query' => 'page=gravityflow-checklists&checklist=_notempty_',
						),
					),
				),
			);

			return array_merge( parent::styles(), $styles );
		}

		public function app_settings_fields() {
			$settings = parent::app_settings_fields();

			$settings[] = array(
				'title'  => esc_html__( 'Configuration', 'gravityflowchecklists' ),
				'fields' => array(
					array(
						'name'  => 'checklists',
						'label' => esc_html__( 'Checklists', 'gravityflowchecklists' ),
						'type'  => 'checklists',
					),
				),
			);

			return $settings;
		}

		public function get_checklist_configs() {
			$settings           = $this->get_app_settings();
			$checklist_settings = isset( $settings['checklists'] ) ? $settings['checklists'] : array();

			return $checklist_settings;
		}

		public function settings_checklists() {
			$hidden_field = array(
				'name'          => 'checklists',
				'default_value' => '[]',
			);
			$this->settings_hidden( $hidden_field );
			?>
			<div id="gravityflowchecklists-checklists-settings-ui">
				<!-- placeholder for custom fields UI -->
			</div>
			<?php
		}

		public function menu_items( $menu_items ) {
			$checklists_menu = array(
				'name'       => 'gravityflow-checklists',
				'label'      => esc_html__( 'Checklists', 'gravityflowchecklists' ),
				'permission' => 'gravityflowchecklists_checklists',
				'callback'   => array( $this, 'checklists' ),
			);

			$index = 3;

			$first_bit = array_slice( $menu_items, 0, $index, true );

			$last_bit = array_slice( $menu_items, $index, count( $menu_items ) - $index, true );

			$menu_items = array_merge( $first_bit, array( $checklists_menu ), $last_bit );

			return $menu_items;
		}

		public function toolbar_menu_items( $menu_items ) {

			$active_class     = 'gf_toolbar_active';
			$not_active_class = '';

			$menu_items['checklists'] = array(
				'label'        => esc_html__( 'Checklists', 'gravityflowchecklists' ),
				'icon'         => '<i class="fa fa fa-check-square-o fa-lg"></i>',
				'title'        => __( 'Checklists', 'gravityflow' ),
				'url'          => '?page=gravityflow-checklists',
				'menu_class'   => 'gf_form_toolbar_settings',
				'link_class'   => ( rgget( 'page' ) == 'gravityflow-checklists' ) ? $active_class : $not_active_class,
				'capabilities' => 'gravityflowchecklists_checklists',
				'priority'     => 850,
			);

			return $menu_items;
		}

		public function checklists() {
			$args = array(
				'display_header' => true,
			);
			$this->checklists_page( $args );
		}

		public function checklists_page( $args ) {
			$defaults = array(
				'display_header' => true,
				'breadcrumbs'    => true,
				'theme'          => 'default',
				'single_page'    => false
			);
			$args     = array_merge( $defaults, $args );
			?>
			<div class="wrap gf_entry_wrap gravityflow_workflow_wrap gravityflow_workflow_submit gravityflowchecklists-<?php echo $args['theme']; ?>">
				<?php if ( $args['display_header'] ) : ?>
					<h2 class="gf_admin_page_title">
						<img width="45" height="22"
							 src="<?php echo gravity_flow()->get_base_url(); ?>/images/gravityflow-icon-blue-grad.svg"
							 style="margin-right:5px;"/>

						<span><?php esc_html_e( 'Checklists', 'gravityflow' ); ?></span>

					</h2>
					<?php
					$this->toolbar();
				endif;

				require_once( $this->get_base_path() . '/includes/class-checklists-page.php' );
				Gravity_Flow_Checklists_Page::render( $args );
				?>
			</div>
			<?php
		}

		public function toolbar() {
			gravity_flow()->toolbar();
		}

		/**
		 * @param WP_User|null $user
		 *
		 * @return Gravity_Flow_Checklist[]
		 */
		public function get_checklists( WP_User $user = null ) {


			$checklist_configs = $this->get_checklist_configs();

			/**
			 * Allows the checklists to be modified.
			 *
			 * @since 1.0
			 *
			 * @param array        $checklist_configs The array of ckecklist configs.
			 * @param WP_User|null $user              The user.
			 */
			$checklist_configs = apply_filters( 'gravityflowchecklists_checklists', $checklist_configs, $user );

			$checklists = array();

			$checklist = null;

			foreach ( $checklist_configs as $checklist_config ) {
				$checklist = new Gravity_Flow_Checklist_Personal( $checklist_config, $user );

				if ( ! $user || $checklist->user_has_permission( $user->ID ) ) {
					$checklists[] = $checklist;
				}
			}

			return $checklists;
		}

		/**
		 * Get Checklist by ID or Name.
		 *
		 * @param string $checklist_id
		 * @param WP_User @user
		 *
		 * @return bool|Gravity_Flow_Checklist
		 */
		public function get_checklist( $checklist_id, WP_User $user = null ) {
			$checklists = $this->get_checklists( $user );

			foreach ( $checklists as $checklist ) {
				if ( $checklist->get_id() == $checklist_id || strtolower( $checklist->get_name() ) == strtolower( $checklist_id ) ) {
					return $checklist;
				}
			}

			return false;
		}

		/**
		 * Adds the Checklists action item to the User actions.
		 *
		 * @param array $actions An array of action links to be displayed.
		 *                             Default 'Edit', 'Delete' for single site, and
		 *                             'Edit', 'Remove' for Multisite.
		 * @param WP_User $user_object WP_User object for the currently-listed user.
		 *
		 * @return array $actions
		 */
		public function filter_user_row_actions( $actions, $user_object ) {

			$url = admin_url( 'admin.php?page=gravityflow-checklists&user_id=' . $user_object->ID );
			$url = esc_url_raw( $url );

			$url = apply_filters( 'gravityflowchecklists_user_admin_checklists_url', $url, $actions, $user_object );

			$new_actions['workflow_checklists'] = "<a href='" . $url . "'>" . esc_html__( 'Checklists' ) . '</a>';

			return array_merge( $new_actions, $actions );
		}

		public function shortcode( $html, $atts ) {

			$a = gravity_flow()->get_shortcode_atts( $atts );

			$a['checklist']   = isset( $atts['checklist'] ) ? $atts['checklist'] : '';
			$a['theme']       = isset( $atts['theme'] ) ? $atts['theme'] : '';
			$a['single_page'] = isset( $atts['single_page'] ) ? $atts['single_page'] : false;

			// Enqueue theme stylesheet when the shortcode specifies one with "theme" attribute
			if ( ! empty( $a['theme'] ) && wp_style_is( "gravityflowchecklists_checklists_{$a['theme']}", 'registered' ) ) {
				wp_enqueue_style( "gravityflowchecklists_checklists_{$a['theme']}" );
			} else {
				$a['theme'] = 'default';
				wp_enqueue_style( 'gravityflowchecklists_checklists_default' );
			}

			if ( rgget( 'view' ) ) {
				wp_enqueue_script( 'gravityflow_entry_detail' );

				if ( $a['single_page'] ) {
					$html .= $this->get_shortcode_checklists_page( $a );
				}

				$html .= $this->get_shortcode_checklists_page_entry_detail( $a );
			} else {
				$html .= $this->get_shortcode_checklists_page( $a );
			}

			return $html;
		}

		/**
		 * Returns the markup for the checklists page.
		 *
		 * @param $a
		 *
		 * @return string
		 */
		public function get_shortcode_checklists_page( $a ) {
			if ( ! class_exists( 'WP_Screen' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/screen.php' );
			}
			require_once( ABSPATH . 'wp-admin/includes/template.php' );

			$check_permissions = true;

			if ( $a['allow_anonymous'] || $a['display_all'] ) {
				$check_permissions = false;
			}

			$detail_base_url = add_query_arg( array( 'page' => 'gravityflow-inbox', 'view' => 'entry' ) );

			$args = array(
				'base_url'           => remove_query_arg( array(
					'entry-id',
					'form-id',
					'start-date',
					'end-date',
					'_wpnonce',
					'_wp_http_referer',
					'action',
					'action2',
					'o',
					'f',
					't',
					'v',
					'gravityflow-print-page-break',
					'gravityflow-print-timelines',
				) ),
				'detail_base_url'    => $detail_base_url,
				'display_header'     => false,
				'action_url'         => 'http' . ( isset( $_SERVER['HTTPS'] ) ? 's' : '' ) . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?",
				'constraint_filters' => array(
					'form_id' => $a['form'],
				),
				'field_ids'          => $a['fields'] ? explode( ',', $a['fields'] ) : '',
				'display_all'        => $a['display_all'],
				'last_updated'       => $a['last_updated'],
				'step_status'        => $a['step_status'],
				'workflow_info'      => $a['workflow_info'],
				'sidebar'            => $a['sidebar'],
				'check_permissions'  => $check_permissions,
			);

			$checklist = sanitize_text_field( rgget( 'checklist' ) );

			if ( empty( $checklist ) ) {
				$checklist = rgar( $a, 'checklist' );
			}

			$args['checklist'] = $checklist;

			if ( ! empty( $a['checklist'] ) ) {
				$args['breadcrumbs'] = false;
			}

			if ( ! empty( $a['theme'] ) ) {
				$args['theme'] = $a['theme'];
			}

			if ( ! empty( $a['single_page'] ) ) {
				$args['single_page'] = $a['single_page'];
			}

			wp_enqueue_script( 'gravityflow_status_list' );
			ob_start();
			$this->checklists_page( $args );
			$html = ob_get_clean();

			return $html;
		}

		/**
		 * Returns the markup for the checklists shortcode detail page.
		 *
		 * @param $a
		 *
		 * @return string
		 */
		public function get_shortcode_checklists_page_entry_detail( $a ) {


			$check_permissions = true;

			if ( $a['allow_anonymous'] || $a['display_all'] ) {
				$check_permissions = false;
			}

			$args = array(
				'show_header'       => false,
				'detail_base_url'   => add_query_arg( array( 'page' => 'gravityflow-inbox', 'view' => 'entry' ) ),
				'check_permissions' => $check_permissions,
				'timeline'          => $a['timeline'],
				'sidebar'           => $a['sidebar'],
				'last_updated'      => $a['last_updated'],
				'step_status'       => $a['step_status'],
				'workflow_info'     => $a['workflow_info'],
			);

			ob_start();
			gravity_flow()->inbox_page( $args );
			$html = ob_get_clean();

			return $html;
		}

		public function get_users_as_choices() {
			$editable_roles = array_reverse( get_editable_roles() );

			$role_choices = array();
			foreach ( $editable_roles as $role => $details ) {
				$name           = translate_user_role( $details['name'] );
				$role_choices[] = array( 'value' => 'role|' . $role, 'label' => $name );
			}

			$args            = apply_filters( 'gravityflow_get_users_args', array( 'orderby' => 'display_name' ) );
			$accounts        = get_users( $args );
			$account_choices = array();
			foreach ( $accounts as $account ) {
				$account_choices[] = array( 'value' => 'user_id|' . $account->ID, 'label' => $account->display_name );
			}

			$choices = array(
				array(
					'label'   => __( 'Users', 'gravityflow' ),
					'choices' => $account_choices,
				),
				array(
					'label'   => __( 'Roles', 'gravityflow' ),
					'choices' => $role_choices,
				),
			);

			return $choices;
		}

		public function is_settings_page() {
			return is_admin() && rgget( 'page' ) == 'gravityflow_settings' && rgget( 'view' ) == 'gravityflowchecklists';
		}

		public function action_gravityflow_enqueue_frontend_scripts() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
			wp_enqueue_script( 'gravityflowchecklists_checklists', $this->get_base_url() . "/js/checklists{$min}.js", array( 'jquery' ), $this->_version );
			wp_localize_script( 'gravityflowchecklists_checklists', 'gravityflowchecklists_checklists_strings', array(
					'vars' => array(
						'root'  => esc_url_raw( rest_url() ),
						'nonce' => wp_create_nonce( 'wp_rest' ),
					),
				)
			);

			// Themes come with Gravity Flow Checklists can be overridden
			$path    = $this->get_base_path() . '/themes';
			$results = scandir( $path );
			foreach ( $results as $result ) {
				if ( '.' == $result[0] ) {
					continue;
				}

				if ( is_dir( $path . '/' . $result ) ) {
					// Register theme styles
					$file               = "/{$result}/style.css";
					$child_theme_style  = get_stylesheet_directory() . '/gravityflow/checklists' . $file;
					$parent_theme_style = get_template_directory() . '/gravityflow/checklists' . $file;

					// Look in the child theme directory first, followed by the parent theme, followed by the Checklists core theme directory
					if ( is_child_theme() && file_exists( $child_theme_style ) ) {
						$url = get_stylesheet_directory_uri() . '/gravityflow/checklists' . $file;
					} elseif ( file_exists( $parent_theme_style ) ) {
						$url = get_template_directory_uri() . '/gravityflow/checklists' . $file;
					} else {
						$url = $this->get_base_url() . "/themes/{$result}/style{$min}.css";
					}
					// Register style, but not enqueue yet
					wp_register_style( 'gravityflowchecklists_checklists_' . $result, $url, null, $this->_version );
				}
			}

			$current_theme_results = array();

			// Scan gravityflow/checklists folder in the current theme to see if there are custom theme
			$current_theme_path    = get_stylesheet_directory() . '/gravityflow/checklists';
			if ( is_dir( $current_theme_path ) ) {
				$current_theme_results = scandir( $current_theme_path );
				foreach ( $current_theme_results as $result ) {
					if ( '.' == $result[0] || in_array( $result, $results ) ) { // skip if it's an overridden stylesheet
						continue;
					}

					if ( is_dir( $current_theme_path . '/' . $result ) ) {
						$file        = "/{$result}/style.css";
						$theme_style = $current_theme_path . $file;

						if ( file_exists( $theme_style ) ) {
							$url = get_stylesheet_directory_uri() . '/gravityflow/checklists' . $file;
							// Register style, but not enqueue yet
							wp_register_style( 'gravityflowchecklists_checklists_' . $result, $url, null, $this->_version );
						}
					}
				}
			}

			// If the current theme is a child theme, get Checklists styles in the parent theme registered as well
			if ( is_child_theme() ) {
				$parent_theme_path    = get_template_directory() . '/gravityflow/checklists';
				if ( is_dir( $parent_theme_path ) ) {
					$parent_theme_results = scandir( $parent_theme_path );

					foreach ( $parent_theme_results as $result ) {
						if ( '.' == $result[0] || in_array( $result, $results ) || in_array( $result, $current_theme_results ) ) { // skip if it's an overridden stylesheet
							continue;
						}

						if ( is_dir( $parent_theme_path . '/' . $result ) ) {
							$file               = "/{$result}/style.css";
							$parent_theme_style = $parent_theme_path . $file;

							if ( file_exists( $parent_theme_style ) ) {
								$url = get_template_directory_uri() . '/gravityflow/checklists' . $file;
								// Register style, but not enqueue yet
								wp_register_style( 'gravityflowchecklists_checklists_' . $result, $url, null, $this->_version );
							}
						}
					}
				}
			}
		}

		public static function get_entry_table_name() {
			return version_compare( self::get_gravityforms_db_version(), '2.3-dev-1', '<' ) ? GFFormsModel::get_lead_table_name() : GFFormsModel::get_entry_table_name();
		}

		public static function get_gravityforms_db_version() {

			if ( method_exists( 'GFFormsModel', 'get_database_version' ) ) {
				$db_version = GFFormsModel::get_database_version();
			} else {
				$db_version = GFForms::$version;
			}

			return $db_version;
		}

		public function save_uuid( $submission, $resume_token, $form, $entry ) {
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				$uuids   = get_user_meta( $user_id, 'gravityflowchecklists_draft_uuids', true );
				if ( ! $uuids ) {
					$uuids = array();
				}

				$uuids[ $form['id'] ] = $resume_token;
				update_user_meta( $user_id, 'gravityflowchecklists_draft_uuids', $uuids );
			}
		}

	}
}
