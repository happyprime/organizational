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
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Register plugin settings fields.
	 */
	public static function register_settings(): void {
		register_setting(
			'general',
			'organizational_names',
			array( __CLASS__, 'sanitize_names' )
		);

		add_settings_field(
			'organizational-names',
			'Organizational Names',
			array( __CLASS__, 'general_settings_names' ),
			'general',
			'default',
			array(
				'label_for' => 'organizational_names',
			)
		);
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

		$supported_post_types = array();

		if ( isset( $organizational->projects ) ) {
			$supported_post_types[] = $organizational->projects->post_type;
		}

		if ( isset( $organizational->people ) ) {
			$supported_post_types[] = $organizational->people->post_type;
		}

		if ( isset( $organizational->entities ) ) {
			$supported_post_types[] = $organizational->entities->post_type;
		}

		if ( isset( $organizational->publications ) ) {
			$supported_post_types[] = $organizational->publications->post_type;
		}

		$clean_names = array();
		foreach ( $names as $name => $data ) {
			if ( ! in_array( $name, $supported_post_types, true ) ) {
				continue;
			}

			$clean_names[ $name ]['singular'] = sanitize_text_field( $data['singular'] );
			$clean_names[ $name ]['plural']   = sanitize_text_field( $data['plural'] );
		}

		wp_schedule_single_event( time() + 1, 'organizational_flush_rewrite_rules' );

		return $clean_names;
	}

	/**
	 * Get the name attribute for a settings field.
	 *
	 * @param string $post_type Post type.
	 * @param string $name Field name.
	 *
	 * @return string Name attribute.
	 */
	public static function get_name( $post_type, $name ): string {
		return 'organizational_names[' . $post_type . '][' . $name . ']';
	}

	/**
	 * Get the ID attribute for a settings field.
	 *
	 * @param string $post_type Post type.
	 * @param string $name Field name.
	 *
	 * @return string ID attribute.
	 */
	public static function get_id( $post_type, $name ): string {
		return 'organizational_names_' . $post_type . '_' . $name;
	}

	/**
	 * Render a settings field for a content type's singular and plural names.
	 *
	 * @param string $post_type Post type.
	 * @param string $label Label.
	 * @param string $value Value.
	 * @param string $type Type.
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
	 * Output the settings fields for the Organizational plugin.
	 */
	public static function general_settings_names(): void {
		global $organizational;

		if ( ! isset( $organizational ) || ! is_object( $organizational ) ) {
			return;
		}

		$names = get_option( 'organizational_names', false );

		$display_names = array();

		if ( isset( $organizational->projects ) && ! isset( $names[ $organizational->projects->post_type ] ) ) {
			$names[ $organizational->projects->post_type ] = array();
		}

		if ( isset( $organizational->people ) && ! isset( $names[ $organizational->people->post_type ] ) ) {
			$names[ $organizational->people->post_type ] = array();
		}

		if ( isset( $organizational->entities ) && ! isset( $names[ $organizational->entities->post_type ] ) ) {
			$names[ $organizational->entities->post_type ] = array();
		}

		if ( isset( $organizational->publications ) && ! isset( $names[ $organizational->publications->post_type ] ) ) {
			$names[ $organizational->publications->post_type ] = array();
		}

		if ( isset( $organizational->projects ) ) {
			$display_names[ $organizational->projects->post_type ] = wp_parse_args(
				$names[ $organizational->projects->post_type ],
				array(
					'singular' => $organizational->projects->singular_name,
					'plural'   => $organizational->projects->plural_name,
				)
			);
		}

		if ( isset( $organizational->people ) ) {
			$display_names[ $organizational->people->post_type ] = wp_parse_args(
				$names[ $organizational->people->post_type ],
				array(
					'singular' => $organizational->people->singular_name,
					'plural'   => $organizational->people->plural_name,
				)
			);
		}

		if ( isset( $organizational->entities ) ) {
			$display_names[ $organizational->entities->post_type ] = wp_parse_args(
				$names[ $organizational->entities->post_type ],
				array(
					'singular' => $organizational->entities->singular_name,
					'plural'   => $organizational->entities->plural_name,
				)
			);
		}

		if ( isset( $organizational->publications ) ) {
			$display_names[ $organizational->publications->post_type ] = wp_parse_args(
				$names[ $organizational->publications->post_type ],
				array(
					'singular' => $organizational->publications->singular_name,
					'plural'   => $organizational->publications->plural_name,
				)
			);
		}
		?>
		<div class="organizational-settings-names">
			<p>Changing the settings here will override the default labels for the content types provided by the Organizational plugin. The default labels are listed to the left of each field. The <strong>singular</strong> label will also be used as a slug in URLs.</p>

			<?php
			if ( isset( $organizational->projects ) ) {
				self::render_content_type_field( $organizational->projects->post_type, 'Project (Singular)', $display_names[ $organizational->projects->post_type ]['singular'], 'singular' );
				self::render_content_type_field( $organizational->projects->post_type, 'Projects (Plural)', $display_names[ $organizational->projects->post_type ]['plural'], 'plural' );
			}

			if ( isset( $organizational->people ) ) {
				self::render_content_type_field( $organizational->people->post_type, 'Person (Singular)', $display_names[ $organizational->people->post_type ]['singular'], 'singular' );
				self::render_content_type_field( $organizational->people->post_type, 'People (Plural)', $display_names[ $organizational->people->post_type ]['plural'], 'plural' );
			}

			if ( isset( $organizational->entities ) ) {
				self::render_content_type_field( $organizational->entities->post_type, 'Entity (Singular)', $display_names[ $organizational->entities->post_type ]['singular'], 'singular' );
				self::render_content_type_field( $organizational->entities->post_type, 'Entities (Plural)', $display_names[ $organizational->entities->post_type ]['plural'], 'plural' );
			}

			if ( isset( $organizational->publications ) ) {
				self::render_content_type_field( $organizational->publications->post_type, 'Publication (Singular)', $display_names[ $organizational->publications->post_type ]['singular'], 'singular' );
				self::render_content_type_field( $organizational->publications->post_type, 'Publications (Plural)', $display_names[ $organizational->publications->post_type ]['plural'], 'plural' );
			}
			?>
		</div>
		<?php
	}
}
