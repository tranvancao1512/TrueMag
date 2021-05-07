<?php

if ( ! class_exists( 'GFForms' ) ) {
	return;
}

class GF_Field_VS_Categories extends GF_Field {

	public $type = 'vs_categories';

	public function get_form_editor_field_title() {
		return __( 'Post Categories', 'cactusthemes' );
	}
    
    /**
	 * Returns the button for the form editor. The array contains two elements:
	 * 'group' => 'standard_fields' // or  'advanced_fields', 'post_fields', 'pricing_fields'
	 * 'text'  => 'Button text'
	 *
	 * Built-in fields don't need to implement this because the buttons are added in sequence in GFFormDetail
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'tm_submission_fields',
			'text'  => $this->get_form_editor_field_title()
		);
	}

	function validate( $value, $form ) {
		if ( $this->isRequired ) {
			$field_id = $this->id;

			if ( empty($value) ) {
				$this->failed_validation  = true;
				$this->validation_message = empty( $this->errorMessage ) ? __( 'This field is required. Please choose at least one category', 'cactusthemes' ) : $this->errorMessage;
			}
		}
	}
    
    /**
	 * Used to determine the required validation result.
	 *
	 * @param int $form_id
	 *
	 * @return bool
	 */
	public function is_value_submission_empty( $form_id ){
        $values = $this->get_value_submission(null);
        
        if(empty($values)) return true;
        
        return false;
	}

	function get_form_editor_field_settings() {
            return array(
			'error_message_setting',
			'label_setting',
			'admin_label_setting',
			'label_placement_setting',
			'sub_label_placement_setting',
			'rules_setting',
			'description_setting',
			'css_class_setting',
		);
	}

	public function get_value_submission( $field_values, $get_from_post_global_var = true ) {
        $form_id = $this->formId;
		if ( ! empty( $_POST[ 'is_submit_' . $form_id ] ) && $get_from_post_global_var ) {
			$values = rgpost( 'cat' );
        }
        
        return $values;
	}

	public function get_field_input( $form, $value = '', $entry = null ) {

		$form_id         = $form['id'];
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$id            = $this->id;
		$field_id      = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
		$disabled_text = $is_form_editor ? 'disabled="disabled"' : '';

		return sprintf( "<div class='ginput_container'><ul class='gfield_checkbox' id='%s'>%s</ul></div>", $field_id, $this->get_checkbox_choices( $value, $disabled_text, $form_id ) );
	}
    
    public function get_checkbox_choices( $value, $disabled_text, $form_id = 0 ) {
		$html = '';

		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();
        
        $cargs = array(
            'hide_empty'    => false, 
            'exclude'       => explode( ",", ot_get_option('user_submit_cat_exclude', '') )
        ); 
        
        $cats = get_terms( 'category', $cargs );
        
        if($cats){
			$choice_number = 1;
			$count         = 1;
            
            $cat_field_type = ot_get_option('user_submit_cat_radio','off');
            if($cat_field_type == 'on'){
                $choice_type = 'radio';
            } else {
                $choice_type = 'checkbox';
            }
            
            $html .= '<div class="wpcf7-form-control-wrap cat"><div class="row wpcf7-form-control wpcf7-checkbox">';
			foreach ( $cats as $cat ) {
                
				$input_id = $this->id . '_' . $cat->term_id;

				if ( $is_entry_detail || $is_form_editor || $form_id == 0 ){
					$id = $input_id;
				} else {
					$id = $form_id . '_' . $input_id;
				}

				$checked = '';

				$logic_event = $this->get_conditional_logic_event( 'click' );

				$tabindex     = $this->get_tabindex();
                
				$choice_value = esc_attr( $cat->term_id );
                $choice_text = esc_html( $cat->name );
                
                $html .= "<label class='col-md-4 wpcf7-list-item' for='choice_{$id}'><input name='cat[]' id='choice_{$id}' type='{$choice_type}' $logic_event value='{$choice_value}' {$checked} {$tabindex} {$disabled_text} />
								{$choice_text}</label>";

				$is_entry_detail = $this->is_entry_detail();
				$is_form_editor  = $this->is_form_editor();
				$is_admin = $is_entry_detail || $is_form_editor;

				if ( $is_admin && RG_CURRENT_VIEW != 'entry' && $count >= 5 ) {
					break;
				}

				$count ++;
			}
            
            $html .= '</div>';

			$total = sizeof( $cats );
			if ( $count < $total ) {
				$html .= "<span class='gchoice_total'>" . sprintf( __( '%d of %d items shown', 'cactusthemes' ), $count, $total ) . '</span>';
			}
		}

		return $html;
	}

	public function get_input_property( $input_id, $property_name ) {
		$input = GFFormsModel::get_input( $this, $input_id );

		return rgar( $input, $property_name );
	}
}

GF_Fields::register( new GF_Field_VS_Categories() );
