<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class Page_Builder_Contact_Form_Item extends Page_Builder_Item {
	private $restricted_types = array( 'contact-form' );

	public function get_type() {
		return 'contact-form';
	}

	private function get_shortcode_options() {
		$shortcode_instance = fw()->extensions->get( 'shortcodes' )->get_shortcode( 'contact_form' );

		return $shortcode_instance->get_options();
	}

	public function enqueue_static() {
		$cf_shortcode = fw()->extensions->get( 'shortcodes' )->get_shortcode( 'contact_form' );
		wp_enqueue_style(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			$cf_shortcode->locate_URI( '/includes/page-builder-contact-form-item/static/css/styles.css' ),
			array(),
			fw()->theme->manifest->get_version()
		);
		wp_enqueue_script(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			$cf_shortcode->locate_URI( '/includes/page-builder-contact-form-item/static/js/scripts.js' ),
			array( 'fw-events', 'underscore', 'jquery' ),
			fw()->theme->manifest->get_version(),
			true
		);
		wp_localize_script(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			str_replace( '-', '_', $this->get_builder_type() ) . '_item_type_contact_form_data',
			$this->get_item_data()
		);
	}

	private function get_item_data() {
		$cf_shortcode = fw_ext( 'shortcodes' )->get_shortcode( 'contact_form' );
		$data = array(
			'title' => __( 'Contact Form', 'fw' ),
			'mailer' => fw_ext_mailer_is_configured(),
			'configureMailer' => __( 'Configure Mailer', 'fw' ),
			'restrictedTypes' => $this->restricted_types,
			'image' => $cf_shortcode->locate_URI( "/includes/page-builder-contact-form-item/static/img/page_builder.png" )
		);

		$options = $this->get_shortcode_options();
		if ( $options ) {
			fw()->backend->enqueue_options_static( $options );
			$data['options'] = $this->transform_options( $options );
		}

		$data['popup_size'] = 'large';

		return $data;
	}

	/*
	 * Puts each option into a separate array
	 * to keep it's order inside the modal dialog
	 */
	private function transform_options( $options ) {
		$transformed_options = array();
		foreach ( $options as $id => $option ) {
			if (is_int($id)) {
				/**
				 * this happens when in options array are loaded external options using fw()->theme->get_options()
				 * and the array looks like this
				 * array(
				 *    'hello' => array('type' => 'text'), // this has string key
				 *    array('hi' => array('type' => 'text')) // this has int key
				 * )
				 */
				$transformed_options[] = $option;
			} else {
				$transformed_options[] = array($id => $option);
			}
		}

		return $transformed_options;
	}

	protected function get_thumbnails_data() {
		$cf_shortcode = fw_ext( 'shortcodes' )->get_shortcode( 'contact_form' );

		$cf_thumbnail = array(
			array(
				'tab'         => __( 'Content Elements', 'fw' ),
				'title'       => __( 'Contact form', 'fw' ),
				'description' => __( "Add a Contact Form", 'fw' ),
				'image'       => $cf_shortcode->locate_URI( "/includes/page-builder-contact-form-item/static/img/page_builder.png" ),
			)
		);
		return $cf_thumbnail;
	}

	public function get_value_from_attributes( $attributes ) {
		$attributes['type'] = $this->get_type();

		$options = $this->get_shortcode_options();
		if ( ! empty( $options ) ) {
			if (empty($attributes['atts'])) {
				/**
				 * The options popup was never opened and there are no attributes.
				 * Extract options default values.
				 */
				$attributes['atts'] = fw_get_options_values_from_input(
					$options, array()
				);
			} else {
				/**
				 * There are saved attributes.
				 * But we need to execute the _get_value_from_input() method for all options,
				 * because some of them may be (need to be) changed (auto-generated) https://github.com/ThemeFuse/Unyson/issues/275
				 * Add the values to $option['value']
				 */
				$options = fw_extract_only_options($options);

				foreach ($attributes['atts'] as $option_id => $option_value) {
					if (isset($options[$option_id])) {
						$options[$option_id]['value'] = $option_value;
					}
				}

				$attributes['atts'] = fw_get_options_values_from_input(
					$options, array()
				);
			}
		}

		return $attributes;
	}

	public function get_shortcode_data( $atts = array() ) {

		$default_width = fw_ext_builder_get_item_width($this->get_builder_type());
		end($default_width); // move to the last width (usually it's the biggest)
		$default_width = key($default_width);

		$return_atts = array(
			'width' => $default_width
		);
		if ( isset( $atts['atts'] ) ) {
			$return_atts = array_merge( $return_atts, $atts['atts'] );
		}

		return array(
			'tag'  => str_replace( '-', '_', $this->get_type() ),
			'atts' => $return_atts
		);
	}
}

FW_Option_Type_Builder::register_item_type( 'Page_Builder_Contact_Form_Item' );
