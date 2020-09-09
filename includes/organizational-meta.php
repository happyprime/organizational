<?php

class Organizational_Meta {
	/**
	 * Setup hooks and filters.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 11, 1 );
		add_action( 'save_post', array( $this, 'save_object_url' ), 12, 2 );
		add_action( 'save_post', array( $this, 'save_person_information' ), 12, 2 );
		add_action( 'save_post', array( $this, 'save_project_information' ), 12, 2 );
	}

	/**
	 * Add meta boxes used to capture pieces of information.
	 *
	 * @param string $post_type
	 */
	public function add_meta_boxes( $post_type ) {
		if ( ! in_array( $post_type, organizational_get_object_type_slugs(), true ) ) {
			return;
		}

		if ( organizational_get_object_type_slug( 'people' ) === $post_type && true === apply_filters( 'organizational_display_person_information_meta_box', true ) ) {
			add_meta_box( 'organizational_person_info', 'Information', array( $this, 'display_person_information_meta_box' ), null, 'normal', 'default' );
		}

		if ( organizational_get_object_type_slug( 'project' ) === $post_type && true === apply_filters( 'organizational_display_project_information_metabox', true ) ) {
			add_meta_box( 'organizational_project_info', 'Information', array( $this, 'display_project_information_meta_box' ) , null, 'normal', 'default' );
		}
	}

	/**
	 * Display a meta box to capture the URL for an object.
	 *
	 * @param WP_Post $post
	 */
	private function display_object_url_meta_box( $post ) {
		$object_url = get_post_meta( $post->ID, '_organizational_object_url', true );

		if ( ! empty( $object_url ) ) {
			$object_url = esc_url( $object_url );
		} else {
			$object_url = '';
		}

		wp_nonce_field( 'save_object_url', '_oranizational_object_url_nonce' );
		?>
		<label for="organizational-object-url">URL:</label>
		<input type="text" class="widefat" id="organizational-object-url" name="organizational_object_url" value="<?php echo esc_attr( $object_url ); ?>" />
		<p class="description">Enter a URL to be displayed to guide visitors toward more information.</p>
		<?php
	}

	/**
	 * Display a meta box to capture meta information for a project. This will include things
	 * such as project ID.
	 *
	 * @param WP_Post $post The full post object being edited.
	 */
	public function display_project_information_meta_box( $post ) {
		$project_id = get_post_meta( $post->ID, '_organizational_project_id', true );

		wp_nonce_field( 'save_project_information', '_oranizational_project_information_nonce' );
		?>
		<div id="capture-project-information">
			<p class="description">All information here will be publicly available on this project's page.</p>
			<div class="project-information-id">
				<label for="organizational-project-id">Project ID:</label>
				<input type="text" id="organizational-project-id" name="organizational_project_id" value="<?php echo esc_attr( $project_id ); ?>" />
			</div>
			<div class="project-information-url">
				<?php $this->display_object_url_meta_box( $post ); ?>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}

	/**
	 * Display a meta box to capture meta information for a person. This will include things
	 * such as first name, last name, phone number, and office.
	 *
	 * @todo Should themes be able to disable certain meta capture points?
	 * @todo Should themes be able to add their own meta capture points?
	 *
	 * @param WP_Post $post
	 */
	public function display_person_information_meta_box( $post ) {
		$person_prefix = get_post_meta( $post->ID, '_organizational_person_prefix', true );
		$person_first_name = get_post_meta( $post->ID, '_organizational_person_first_name', true );
		$person_last_name = get_post_meta( $post->ID, '_organizational_person_last_name', true );
		$person_suffix = get_post_meta( $post->ID, '_organizational_person_suffix', true );
		$person_title = get_post_meta( $post->ID, '_organizational_person_title', true );
		$person_title_secondary = get_post_meta( $post->ID, '_organizational_person_title_secondary', true );
		$person_office = get_post_meta( $post->ID, '_organizational_person_office', true );
		$person_email = get_post_meta( $post->ID, '_organizational_person_email', true );
		$person_phone = get_post_meta( $post->ID, '_organizational_person_phone', true );

		wp_nonce_field( 'save_person_information', '_oranizational_person_information_nonce' );
		?>
		<div id="capture-person-information">
			<p class="description">All information here will be publicly available on this person's profile page.</p>
			<div class="person-information-name">
				<div class="pi-prefix">
					<label for="organizational-person-prefix">Prefix:</label>
					<input type="text" id="organizational-person-prefix" name="organizational_person_prefix" value="<?php echo esc_attr( $person_prefix ); ?>" />
				</div>
				<div class="pi-first">
					<label for="organizational-person-first-name">First Name:</label>
					<input type="text" id="organizational-person-first-name" name="organizational_person_first_name" value="<?php echo esc_attr( $person_first_name ); ?>" />
				</div>
				<div class="pi-last">
					<label for="organizational-person-last-name">Last Name:</label>
					<input type="text" id="organizational-person-last-name" name="organizational_person_last_name" value="<?php echo esc_attr( $person_last_name ); ?>" />
				</div>
				<div class="pi-suffix">
					<label for="organizational-person-suffix">Suffix:</label>
					<input type="text" id="organizational-person-suffix" name="organizational_person_suffix" value="<?php echo esc_attr( $person_suffix ); ?>" />
				</div>
			</div>
			<div class="clear"></div>
			<div class="person-information">
				<label for="organizational-person-title">Title:</label>
				<input type="text" id="organizational-person-title" name="organizational_person_title" value="<?php echo esc_attr( $person_title ); ?>" />

				<label for="organizational-person-title-secondary">Secondary Title:</label>
				<input type="text" id="organizational-person-title-secondary" name="organizational_person_title_secondary" value="<?php echo esc_attr( $person_title_secondary ); ?>" />

				<label for="organizational-person-office">Office:</label>
				<input type="text" id="organizational-person-office" name="organizational_person_office" value="<?php echo esc_attr( $person_office ); ?>" />

				<label for="organizational-person-email">Email:</label>
				<input type="text" id="organizational-person-email" name="organizational_person_email" value="<?php echo esc_attr( $person_email ); ?>" />

				<label for="organizational-person-phone">Phone Number:</label>
				<input type="text" id="organizational-person-phone" name="organizational_person_phone" value="<?php echo esc_attr( $person_phone ); ?>" />

				<?php $this->display_object_url_meta_box( $post ); ?>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}

	/**
	 * Assign a URL to an object when saved through the object's meta box.
	 *
	 * @param int     $post_id The ID of the post being saved.
	 */
	public function save_object_url( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		if ( ! isset( $_POST['_oranizational_object_url_nonce'] ) || false === wp_verify_nonce( $_POST['_oranizational_object_url_nonce'], 'save_object_url' ) ) {
			return;
		}

		if ( isset( $_POST['organizational_object_url'] ) ) {
			if ( empty( trim( $_POST['organizational_object_url'] ) ) ) {
				delete_post_meta( $post_id, '_organizational_object_url' );
			} else {
				update_post_meta( $post_id, '_organizational_object_url', esc_url_raw( $_POST['organizational_object_url'] ) );
			}
		}
	}

	/**
	 * Save a project's meta information after entry.
	 *
	 * @param int     $post_id ID of the post being saved.
	 * @param WP_Post $post    Full post object being saved.
	 */
	public function save_project_information( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( organizational_get_object_type_slug( 'project' ) !== $post->post_type ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		if ( true !== apply_filters( 'organizational_display_project_information_metabox', true ) ) {
			return;
		}

		if ( ! isset( $_POST['_oranizational_project_information_nonce'] ) || false === wp_verify_nonce( $_POST['_oranizational_project_information_nonce'], 'save_project_information' ) ) {
			return;
		}

		if ( isset( $_POST['organizational_project_id'] ) ) {
			if ( empty( trim( $_POST['organizational_project_id'] ) ) ) {
				delete_post_meta( $post_id, '_organizational_project_id' );
			} else {
				update_post_meta( $post_id, '_organizational_project_id', sanitize_text_field( $_POST['organizational_project_id'] ) );
			}
		}
	}

	/**
	 * Save a person's meta information after entry.
	 *
	 * @param int     $post_id ID of the post being saved.
	 * @param WP_Post $post    The full post object being saved.
	 */
	public function save_person_information( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( organizational_get_object_type_slug( 'people' ) !== $post->post_type ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		if ( true !== apply_filters( 'organizational_display_person_information_meta_box', true ) ) {
			return;
		}

		if ( ! isset( $_POST['_oranizational_person_information_nonce'] ) || false === wp_verify_nonce( $_POST['_oranizational_person_information_nonce'], 'save_person_information' ) ) {
			return;
		}

		if ( isset( $_POST['organizational_person_prefix'] ) ) {
			if ( empty( trim( $_POST['organizational_person_prefix'] ) ) ) {
				delete_post_meta( $post_id, '_organizational_person_prefix' );
			} else {
				update_post_meta( $post_id, '_organizational_person_prefix', sanitize_text_field( $_POST['organizational_person_prefix'] ) );
			}
		}

		if ( isset( $_POST['organizational_person_first_name'] ) ) {
			if ( empty( trim( $_POST['organizational_person_first_name'] ) ) ) {
				delete_post_meta( $post_id, '_organizational_person_first_name' );
			} else {
				update_post_meta( $post_id, '_organizational_person_first_name', sanitize_text_field( $_POST['organizational_person_first_name'] ) );
			}
		}

		if ( isset( $_POST['organizational_person_last_name'] ) ) {
			if ( empty( trim( $_POST['organizational_person_last_name'] ) ) ) {
				delete_post_meta( $post_id, '_organizational_person_last_name' );
			} else {
				update_post_meta( $post_id, '_organizational_person_last_name', sanitize_text_field( $_POST['organizational_person_last_name'] ) );
			}
		}

		if ( isset( $_POST['organizational_person_suffix'] ) ) {
			if ( empty( trim( $_POST['organizational_person_suffix'] ) ) ) {
				delete_post_meta( $post_id, '_organizational_person_suffix' );
			} else {
				update_post_meta( $post_id, '_organizational_person_suffix', sanitize_text_field( $_POST['organizational_person_suffix'] ) );
			}
		}

		if ( isset( $_POST['organizational_person_title'] ) ) {
			if ( empty( trim( $_POST['organizational_person_title'] ) ) ) {
				delete_post_meta( $post_id, '_organizational_person_title' );
			} else {
				update_post_meta( $post_id, '_organizational_person_title', sanitize_text_field( $_POST['organizational_person_title'] ) );
			}
		}

		if ( isset( $_POST['organizational_person_title_secondary'] ) ) {
			if ( empty( trim( $_POST['organizational_person_title_secondary'] ) ) ) {
				delete_post_meta( $post_id, '_organizational_person_title_secondary' );
			} else {
				update_post_meta( $post_id, '_organizational_person_title_secondary', sanitize_text_field( $_POST['organizational_person_title_secondary'] ) );
			}
		}

		if ( isset( $_POST['organizational_person_office'] ) ) {
			if ( empty( trim( $_POST['organizational_person_office'] ) ) ) {
				delete_post_meta( $post_id, '_organizational_person_office' );
			} else {
				update_post_meta( $post_id, '_organizational_person_office', sanitize_text_field( $_POST['organizational_person_office'] ) );
			}
		}

		if ( isset( $_POST['organizational_person_email'] ) ) {
			if ( empty( trim( $_POST['organizational_person_email'] ) ) ) {
				delete_post_meta( $post_id, '_organizational_person_email' );
			} else {
				update_post_meta( $post_id, '_organizational_person_email', sanitize_email( $_POST['organizational_person_email'] ) );
			}
		}

		if ( isset( $_POST['organizational_person_phone'] ) ) {
			if ( empty( trim( $_POST['organizational_person_phone'] ) ) ) {
				delete_post_meta( $post_id, '_organizational_person_phone' );
			} else {
				update_post_meta( $post_id, '_organizational_person_phone', sanitize_text_field( $_POST['organizational_person_phone'] ) );
			}
		}
	}

	/**
	 * Provide the meta value for a specific key associated with people data.
	 *
	 * @param int    $post_id ID of the person.
	 * @param string $field   Friendly field name for the meta being requested.
	 *
	 * @return bool|mixed Requested metadata if available, false if not.
	 */
	public function get_meta( $post_id, $field ) {
		if ( 0 === absint( $post_id ) ) {
			return false;
		}

		$supported_fields = array( 'prefix', 'first_name', 'last_name', 'suffix', 'title', 'title_secondary', 'office', 'email', 'phone' );

		if ( ! in_array( $field, $supported_fields, true ) ) {
			return false;
		}

		$data = get_post_meta( $post_id, '_organizational_person_' . $field, true );

		return $data;
	}
}
$organizational = new Organizational_Meta();

/**
 * Provides a helper function for grabbing the meta data stored for people.
 *
 * @param int    $post_id ID of the person.
 * @param string $field   Friendly field name for the meta being requested.
 *
 * @return bool|mixed
 */
function organizational_get_meta( $post_id, $field ) {
	global $organizational;
	return $organizational->get_meta( $post_id, $field );
}
