<?php
/**
 * Define the Admin class.
 *
 * @package organizational
 */

namespace HappyPrime\Organizational;

/**
 * Define the Admin class.
 */
class Admin {

	/**
	 * Initialize customizations in the WordPress admin.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Add the settings page to the WordPress admin menu.
	 */
	public static function add_settings_page(): void {
		add_options_page(
			'Organizational Settings',
			'Organizational',
			'manage_options',
			'organizational-settings',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings fields.
	 */
	public static function register_settings(): void {
		global $organizational;

		register_setting(
			'organizational_settings',
			'organizational_names',
			array( __CLASS__, 'sanitize_names' )
		);

		add_settings_section(
			'organizational_names_section',
			'Content Type Names',
			array( __CLASS__, 'render_names_section' ),
			'organizational-settings'
		);

		$content_types = $organizational->get_content_types();

		foreach ( $content_types as $content_type ) {
			add_settings_field(
				'organizational-names-' . $content_type->post_type,
				$content_type->default_name,
				array( __CLASS__, 'general_settings_names' ),
				'organizational-settings',
				'organizational_names_section',
				array(
					'content_type' => $content_type,
				)
			);
		}
	}

	/**
	 * Render the settings page.
	 */
	public static function render_settings_page(): void {
		?>
		<div class="wrap">
			<h1>Organizational Settings</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'organizational_settings' );
				do_settings_sections( 'organizational-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the content type names section.
	 */
	public static function render_names_section(): void {
		?>
		<p>Changing the settings here will override the default labels for the content types
			provided by the Organizational plugin. The default labels are listed to the left
			of each field. The <strong>singular</strong> label will also be used as a slug
			in URLs.</p>
		<style>
			.organizational-settings-names {

				.og-content-type-section {
					margin-bottom: 1rem;

					div {
						display: flex;
						margin-bottom: 0.5rem;

						* {
							flex-basis: 150px;
						}
					}
				}

				.checkbox:not(:checked) ~ label ~ .og-taxonomy-section {
					display: none;
				}

				.checkbox:checked ~ label ~ .og-taxonomy-section {
					display: block;
					margin-top: 1rem;
				}
			}
		</style>
		<?php
	}

	/**
	 * Sanitize the names assigned to object types before saving to the database.
	 *
	 * @param array $names Names being saved.
	 *
	 * @return array Clean data.
	 */
	public static function sanitize_names( $names ): array {
		global $organizational;

		$content_types        = $organizational->get_content_types();
		$supported_post_types = array();

		foreach ( $content_types as $content_type ) {
			$supported_post_types[] = $content_type->post_type;
		}

		$clean_names = array();
		foreach ( $names as $name => $data ) {
			if ( ! in_array( $name, $supported_post_types, true ) ) {
				continue;
			}

			$clean_names[ $name ]['singular']          = sanitize_text_field( $data['singular'] );
			$clean_names[ $name ]['plural']            = sanitize_text_field( $data['plural'] );
			$clean_names[ $name ]['taxonomy_singular'] = sanitize_text_field( $data['taxonomy_singular'] );
			$clean_names[ $name ]['taxonomy_plural']   = sanitize_text_field( $data['taxonomy_plural'] );

			if ( isset( $data['taxonomy-toggle'] ) ) {
				$clean_names[ $name ]['taxonomy_toggle'] = 1;
			}
		}

		wp_schedule_single_event( time() + 1, 'organizational_flush_rewrite_rules' );

		return $clean_names;
	}

	/**
	 * Get the name attribute for a settings field.
	 *
	 * @param string $post_type The content type's post type.
	 * @param string $name      The field's name.
	 *
	 * @return string Name attribute.
	 */
	public static function get_name( $post_type, $name ): string {
		return 'organizational_names[' . $post_type . '][' . $name . ']';
	}

	/**
	 * Get the ID attribute for a settings field.
	 *
	 * @param string $post_type The content type's post type.
	 * @param string $name      The field's name.
	 *
	 * @return string ID attribute.
	 */
	public static function get_id( $post_type, $name ): string {
		return 'organizational_names_' . $post_type . '_' . $name;
	}

	/**
	 * Render a settings field for a content type's singular and plural names.
	 *
	 * @param string $post_type The content type's post type.
	 * @param string $label     The field's label.
	 * @param string $value     The field's current value.
	 * @param string $type      Whether the field is for the content type's singular or plural name.
	 */
	public static function render_content_type_field( string $post_type, string $label, string $value, string $type ): void {
		?>
			<div>
				<label for="<?php echo esc_attr( self::get_id( $post_type, $type ) ); ?>"><?php echo esc_html( $label ); ?></label>
				<input
					id="<?php echo esc_attr( self::get_id( $post_type, $type ) ); ?>"
					name="<?php echo esc_attr( self::get_name( $post_type, $type ) ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					type="text"
					class="regular-text"
				/>
			</div>
		<?php
	}

	/**
	 * Render a settings field to toggle whether an additional taxonomy is
	 * registered for a content type.
	 *
	 * @param string $post_type The content type's post type.
	 * @param string $label     The label text for the toggle.
	 * @param int    $value     Whether the toggle is checked.
	 */
	public static function render_taxonomy_toggle( string $post_type, string $label, int $value ): void {
		?>
		<input
			id="<?php echo esc_attr( self::get_id( $post_type, 'taxonomy-toggle' ) ); ?>"
			name="<?php echo esc_attr( self::get_name( $post_type, 'taxonomy-toggle' ) ); ?>"
			type="checkbox"
			value="1"
			class="checkbox"
			<?php checked( $value, 1 ); ?>
		/>
		<label for="<?php echo esc_attr( self::get_id( $post_type, 'taxonomy-toggle' ) ); ?>"><?php echo esc_html( $label ); ?></label>
		<?php
	}

	/**
	 * Render a settings field to capture the name of an additional taxonomy
	 * for a content type.
	 *
	 * @param string $post_type The content type's post type.
	 * @param string $label     The field's label.
	 * @param string $value     The field's current value.
	 * @param string $type      Whether the field is for the taxonomy's singular or plural name.
	 */
	public static function render_taxonomy_field( string $post_type, string $label, string $value, string $type ): void {
		?>
			<div>
				<label for="<?php echo esc_attr( self::get_id( $post_type, $type ) ); ?>"><?php echo esc_html( $label ); ?></label>
				<input
					id="<?php echo esc_attr( self::get_id( $post_type, $type ) ); ?>"
					name="<?php echo esc_attr( self::get_name( $post_type, $type ) ); ?>"
					type="text"
					class="regular-text"
					value="<?php echo esc_attr( $value ); ?>"
				/>
			</div>
		<?php
	}

	/**
	 * Output the settings fields for the Organizational plugin.
	 *
	 * @param array $args Arguments passed to the callback function.
	 */
	public static function general_settings_names( $args ): void {
		if ( ! isset( $args['content_type'] ) ) {
			return;
		}

		$content_type  = $args['content_type'];
		$names         = get_option( 'organizational_names', false );
		$display_names = array();

		if ( ! isset( $names[ $content_type->post_type ] ) ) {
			$names[ $content_type->post_type ] = array();
		}

		$display_names[ $content_type->post_type ] = wp_parse_args(
			$names[ $content_type->post_type ],
			array(
				'singular'          => $content_type->singular_name,
				'plural'            => $content_type->plural_name,
				'taxonomy_singular' => $content_type->taxonomy_singular_name,
				'taxonomy_plural'   => $content_type->taxonomy_plural_name,
			)
		);

		$taxonomy_toggle = isset( $display_names[ $content_type->post_type ]['taxonomy_toggle'] ) ? (int) $display_names[ $content_type->post_type ]['taxonomy_toggle'] : 0;

		?>
		<div class="organizational-settings-names">
			<div class="og-content-type-section">
				<?php
				self::render_content_type_field( $content_type->post_type, 'Singular', $display_names[ $content_type->post_type ]['singular'], 'singular' );
				self::render_content_type_field( $content_type->post_type, 'Plural', $display_names[ $content_type->post_type ]['plural'], 'plural' );
				self::render_taxonomy_toggle( $content_type->post_type, 'Enable additional taxonomy for ' . $content_type->default_name, $taxonomy_toggle );
				?>
				<div class="og-taxonomy-section">
					<?php
					self::render_taxonomy_field( $content_type->post_type, 'Singular', $display_names[ $content_type->post_type ]['taxonomy_singular'], 'taxonomy_singular' );
					self::render_taxonomy_field( $content_type->post_type, 'Plural', $display_names[ $content_type->post_type ]['taxonomy_plural'], 'taxonomy_plural' );
					?>
				</div>
			</div>
		</div>
		<?php
	}
}
