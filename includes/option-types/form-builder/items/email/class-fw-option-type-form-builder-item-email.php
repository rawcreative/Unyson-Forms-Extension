<?php if (!defined('FW')) die('Forbidden');

class FW_Option_Type_Form_Builder_Item_Email extends FW_Option_Type_Form_Builder_Item
{
	public function get_type()
	{
		return 'email';
	}

	private function get_uri( $append = '' ) {
		return fw_get_framework_directory_uri('/extensions/forms/includes/option-types/'. $this->get_builder_type() .'/items/'. $this->get_type() . $append);
	}

	public function get_thumbnails()
	{
		return array(
			array(
				'html' =>
					'<div class="item-type-icon-title">'.
						'<div class="item-type-icon">'.
							'<img src="'. esc_attr($this->get_uri('/static/images/icon.png')) .'" />'.
						'</div>'.
						'<div class="item-type-title">'. __('Email', 'fw') .'</div>'.
					'</div>'
			)
		);
	}

	public function enqueue_static()
	{
		wp_enqueue_style(
			'fw-builder-'. $this->get_builder_type() .'-item-'. $this->get_type(),
			$this->get_uri( '/static/css/styles.css' )
		);

		wp_enqueue_script(
			'fw-builder-'. $this->get_builder_type() .'-item-'. $this->get_type(),
			$this->get_uri( '/static/js/scripts.js' ),
			array(
				'fw-events',
			),
			false,
			true
		);

		wp_localize_script(
			'fw-builder-'. $this->get_builder_type() .'-item-'. $this->get_type(),
			'fw_form_builder_item_type_'. $this->get_type(),
			array(
				'l10n' => array(
					'item_title'        => __('Email', 'fw'),
					'label'             => __('Label', 'fw'),
					'toggle_required'   => __('Toggle mandatory field', 'fw'),
					'edit'              => __('Edit', 'fw'),
					'delete'            => __('Delete', 'fw'),
					'edit_label'        => __('Edit Label', 'fw'),
				),
				'options'  => $this->get_options(),
				'defaults' => array(
					'type'    => $this->get_type(),
					'options' => fw_get_options_values_from_input($this->get_options(), array())
				)
			)
		);

		fw()->backend->enqueue_options_static($this->get_options());
	}

	private function get_options()
	{
		return array(
			array(
				'g1' => array(
					'type' => 'group',
					'options' => array(
						array(
							'label' => array(
								'type'  => 'text',
								'label' => __('Label', 'fw'),
								'desc'  => __('The label of the field that will be displayed to the users', 'fw'),
								'value' => __('Email', 'fw'),
							)
						),
						array(
							'required' => array(
								'type'  => 'switch',
								'label' => __('Mandatory Field?', 'fw'),
								'desc'  => __('If this field is mandatory for the user', 'fw'),
								'value' => true,
							)
						),
					)
				)
			),
			array(
				'g2' => array(
					'type' => 'group',
					'options' => array(
						array(
							'placeholder' => array(
								'type'  => 'text',
								'label' => __('Placeholder', 'fw'),
							)
						),
						array(
							'default_value' => array(
								'type'  => 'text',
								'label' => __('Default Value', 'fw'),
							)
						)
					)
				)
			),
			array(
				'g4' => array(
					'type' => 'group',
					'options' => array(
						array(
							'info' => array(
								'type'  => 'textarea',
								'label' => __('Instructions for Users', 'fw'),
								'desc'  => __('The users will see this instructions in the tooltip near the field', 'fw'),
							)
						),
					)
				)
			),
		);
	}

	protected function get_fixed_attributes($attributes)
	{
		// do not allow sub items
		unset($attributes['_items']);

		$default_attributes = array(
			'type'      => $this->get_type(),
			'shortcode' => false, // the builder will generate new shortcode if this value will be empty()
			'width'     => '',
			'options'   => array()
		);

		// remove unknown attributes
		$attributes = array_intersect_key($attributes, $default_attributes);

		$attributes = array_merge($default_attributes, $attributes);

		$attributes['options'] = fw_get_options_values_from_input(
			$this->get_options(),
			$attributes['options']
		);

		return $attributes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_value_from_attributes($attributes)
	{
		return $this->get_fixed_attributes($attributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function frontend_render(array $item, $input_value)
	{
		$options = $item['options'];

		// prepare attributes
		{
			$attr = array(
				'type'  => 'text',
				'name'  => $item['shortcode'],
				'placeholder'  => $options['placeholder'],
				'value' => is_null($input_value) ? $options['default_value'] : $input_value,
				'id'    => 'id-'. fw_unique_increment(),
			);

			if ($options['required']) {
				$attr['required'] = 'required';
			}
		}

		return fw_render_view(
			$this->locate_path('/views/view.php', dirname(__FILE__) .'/view.php'),
			array(
				'item' => $item,
				'attr' => $attr,
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function frontend_validate(array $item, $input_value)
	{
		$options = $item['options'];

		$messages = array(
			'required' => str_replace(
				array('{label}'),
				array($options['label']),
				__('The {label} field is required', 'fw')
			),
			'incorrect' => str_replace(
				array('{label}'),
				array($options['label']),
				__('The {label} filed must contain a valid email', 'fw')
			),
		);

		if ($options['required'] && !fw_strlen(trim($input_value))) {
			return $messages['required'];
		}

		if (!empty($input_value) && !filter_var($input_value, FILTER_VALIDATE_EMAIL)) {
			return $messages['incorrect'];
		}
	}
}

FW_Option_Type_Builder::register_item_type('FW_Option_Type_Form_Builder_Item_Email');
