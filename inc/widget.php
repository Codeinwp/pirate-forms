<?php
/**
 * Template for new WordPress widget
 *
 * @see WP_Widget::widget()
 */
class pirate_forms_contact_widget extends WP_Widget {

	/**
	 *  Widget constructor
	 */
	function pirate_forms_contact_widget() {

		/* Widget settings. */
		$widget_ops = array( 'classname'   => __FUNCTION__, 'description' => __( 'Pirate Forms','pirate-forms') );

		/* Create the widget. */
		$this->WP_Widget( 'pirate_forms_contact_widget', __( 'Pirate Forms','pirate-forms' ), $widget_ops );

		$this->widget_fields = array(
			array(
				'label'       => __( 'Title','pirate-forms' ),
				'type'        => 'text',
				'id'          => 'pirate_forms_widget_title',
				'description' => '',
				'default'     => ''
			),
			array(
				'label'       => __( 'Text above form','pirate-forms' ),
				'type'        => 'textarea',
				'id'          => 'pirate_forms_widget_subtext',
				'description' => '',
				'default'     => ''
			),
		);
	}

	/**
	 * Widget logic and display
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {

		// Pulling out all settings
		extract( $args );
		extract( $instance );

		// Output all wrappers
		echo $before_widget . '<div class="pirate-forms-contact-widget">';

			if ( ! empty( $pirate_forms_widget_title ) )
				echo $before_title . $pirate_forms_widget_title . $after_title;

			if ( ! empty( $pirate_forms_widget_subtext ) )
				echo wpautop( stripslashes( $pirate_forms_widget_subtext ) );

			echo do_shortcode('[pirate_forms]');

		echo '</div>' . $after_widget;

	}

	/**
	 * Used to update widget settings
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Storing widget title as inputted option or category name
		$instance['pirate_forms_widget_title'] = apply_filters( 'widget_title', sanitize_text_field( $new_instance['pirate_forms_widget_title'] ) );

		$instance['pirate_forms_widget_subtext'] = $new_instance['pirate_forms_widget_subtext'];

		return $instance;
	}

	/**
	 * Used to generate the widget admin view
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	function form( $instance ) {

		for ( $i = 0; $i < count( $this->widget_fields ); $i ++ ) :
			$field_id                              = $this->widget_fields[$i]['id'];
			$this->widget_fields[$i]['field_id']   = $this->get_field_id( $field_id );
			$this->widget_fields[$i]['field_name'] = $this->get_field_name( $field_id );
		endfor;

		pirate_forms_output_widget_fields( $this->widget_fields, $instance );

	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget("pirate_forms_contact_widget");' ) );

/**
 * Builds all widget admin forms
 *
 * @see WP_Widget::widget()
 *
 * @param array  $fields
 * @param object $instance
 */
function pirate_forms_output_widget_fields( $fields, $instance ) {

	foreach ( $fields as $field ) :

		echo '<p>';

		switch ( $field['type'] ) :

			case 'text':
			case 'email':
			case 'url':
			case 'number':
				?>

				<label for="<?php echo $field['field_id'] ?>"><?php echo $field['label'] ?></label>
				<input type="<?php echo $field['type'] ?>" id="<?php echo $field['field_id'] ?>" name="<?php echo $field['field_name'] ?>" value="<?php echo isset( $instance[$field['id']] ) ? $instance[$field['id']] : $field['default'] ?>" class="widefat"/>
				<?php echo ! empty( $field['description'] ) ? '<span class="description">' . $field['description'] . '</span>' : '' ?>

				<?php
				break;

			case 'textarea':
				?>

				<label for="<?php echo $field['field_id'] ?>"><?php echo $field['label'] ?></label>
				<textarea id="<?php echo $field['field_id'] ?>" name="<?php echo $field['field_name'] ?>" style="display: block; width: 100%"><?php echo isset( $instance[$field['id']] ) ? $instance[$field['id']] : $field['default'] ?></textarea>
				<?php echo ! empty( $field['description'] ) ? '<span class="description">' .  $field['description'] . '</span>' : '' ?>

				<?php
				break;

			case 'checkbox':
				?>

				<input type="checkbox" id="<?php echo $field['field_id'] ?>" name="<?php echo $field['field_name'] ?>" value="1" <?php if ( isset( $instance[$field['id']] ) && $instance[$field['id']] == 1 ) echo 'checked' ?>/>
				<label for="<?php echo $field['field_id'] ?>" style="font-weight: bold"><?php echo $field['label'] ?></label>
				<?php echo ! empty( $field['description'] ) ? '<span class="description">' . $field['description'] . '</span>' : '' ?>

				<?php
				break;

			case 'select':
				if ( is_array( $field['options'] ) ) :
					?>
					<label for="<?php echo $field['field_id'] ?>"><?php echo $field['label'] ?></label>
					<select id="<?php echo $field['field_id'] ?>" name="<?php echo $field['field_name'] ?>" style="display: block; width: 100%">
						<?php
						foreach ( $field['options'] as $key => $val ) :
							// Selecting the current set option
							$checked = isset( $instance[$field['id']] ) && $instance[$field['id']] == $key ? ' selected' : '';
							?>
							<option value="<?php echo $key ?>"<?php echo $checked ?>><?php echo $val ?></option>
						<?php
						endforeach;
						?>
					</select>
				<?php
				endif;
				break;

		endswitch;

		echo '</p>';

	endforeach;

}
