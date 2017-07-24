<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

		<div class="wrap">
			<div id="pirate-forms-main">
				<h1><?php esc_html_e( 'Pirate Forms', 'pirate-forms' ); ?></h1>

				<div class="pirate-options">
					<ul class="pirate-forms-nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#0" aria-controls="how_to_use" role="tab"
																  data-toggle="tab"><?php esc_html_e( 'How to use', 'pirate-forms' ); ?></a>
						</li>
						<li role="presentation"><a href="#1" aria-controls="options" role="tab"
												   data-toggle="tab"><?php esc_html_e( 'Options', 'pirate-forms' ); ?></a>
						</li>
						<li role="presentation"><a href="#2" aria-controls="fields" role="tab"
												   data-toggle="tab"><?php esc_html_e( 'Fields Settings', 'pirate-forms' ); ?></a>
						</li>
						<li role="presentation"><a href="#3" aria-controls="labels" role="tab"
												   data-toggle="tab"><?php esc_html_e( 'Fields Labels', 'pirate-forms' ); ?></a>
						</li>
						<li role="presentation"><a href="#4" aria-controls="messages" role="tab"
												   data-toggle="tab"><?php esc_html_e( 'Alert Messages', 'pirate-forms' ); ?></a>
						</li>
						<li role="presentation"><a href="#5" aria-controls="smtp" role="tab"
												   data-toggle="tab"><?php esc_html_e( 'SMTP', 'pirate-forms' ); ?></a></li>
					</ul>

					<div class="pirate-forms-tab-content">

						<div id="0" class="pirate-forms-tab-pane active">

							<h2 class="pirate_forms_welcome_text"><?php esc_html_e( 'Welcome to Pirate Forms!', 'pirate-forms' ); ?></h2>
							<p class="pirate_forms_subheading"><?php esc_html_e( 'To get started, just ', 'pirate-forms' ); ?>
								<b><?php esc_html_e( 'configure all the options ', 'pirate-forms' ); ?></b><?php esc_html_e( 'you need, hit save and start using the created form.', 'pirate-forms' ); ?>
							</p>

							<hr>

							<p><?php esc_html_e( 'There are 3 ways of using the newly created form:', 'pirate-forms' ); ?></p>
							<ol>
								<li><?php esc_html_e( 'Add a ', 'pirate-forms' ); ?><strong><a
												href="<?php echo admin_url( 'widgets.php' ); ?>"><?php esc_html_e( 'widget', 'pirate-forms' ); ?></a></strong>
								</li>
								<li><?php esc_html_e( 'Use the shortcode ', 'pirate-forms' ); ?>
									<strong><code>[pirate_forms]</code></strong><?php esc_html_e( ' in any page or post.', 'pirate-forms' ); ?>
								</li>
								<li><?php esc_html_e( 'Use the shortcode ', 'pirate-forms' ); ?><strong><code>&lt;?php echo
											do_shortcode( '[pirate_forms]' )
											?&gt;</code></strong><?php esc_html_e( ' in the theme\'s files.', 'pirate-forms' ); ?>
								</li>
							</ol>

							<hr>

							<div class="rate_plugin_invite">

								<h4><?php esc_html_e( 'Are you enjoying Pirate Forms?', 'pirate-forms' ); ?></h4>

								<p class="review-link">
									<?php
									/* translators: link to WordPress.org repo for PirateForms */
									echo sprintf( esc_html__( 'Rate our plugin on %1$s WordPress.org %2$s. We\'d really appreciate it!', 'pirate-forms' ), '<a href="https://wordpress.org/support/view/plugin-reviews/pirate-forms" target="_blank" rel="nofollow"> ', '</a>' );
									?>
								</p>

								<p><span class="dashicons dashicons-star-filled"></span><span
											class="dashicons dashicons-star-filled"></span><span
											class="dashicons dashicons-star-filled"></span><span
											class="dashicons dashicons-star-filled"></span><span
											class="dashicons dashicons-star-filled"></span></p>

								<p>
									<small>
										<?php
										/* translators: link to blog article about contact form plugins */
										echo sprintf( esc_html__( 'If you want a more complex Contact Form Plugin please check %1$s this link %2$s.', 'pirate-forms' ), '<a href="http://www.codeinwp.com/blog/best-contact-form-plugins-wordpress/" target="_blank" >', '</a>' );
										?>
									</small>
								</p>
							</div>

						</div>

						<?php
						$pirate_forms_nr_tabs = 1;
						foreach ( $plugin_options as $plugin_options_tab ) :
							echo '<div id="' . $pirate_forms_nr_tabs . '" class="pirate-forms-tab-pane">';
						?>
						<form method="post" class="pirate_forms_contact_settings">

							<?php
							$pirate_forms_nr_tabs ++;
							foreach ( $plugin_options_tab as $key => $value ) :
								/* Label */
								if ( ! empty( $value[0] ) ) :
									$opt_name = $value[0];
								endif;
								/* ID */
								$opt_id = $key;
								/* Description */
								if ( ! empty( $value[1] ) ) :
									$opt_desc = $value[1];
								else :
									$opt_desc = '';
								endif;
								/* Input type */
								if ( ! empty( $value[2] ) ) :
									$opt_type = $value[2];
								else :
									$opt_type = '';
								endif;
								/* Default value */
								if ( ! empty( $value[3] ) ) :
									$opt_default = $value[3];
								else :
									$opt_default = '';
								endif;
								/* Value */
								$opt_val = isset( $pirate_forms_options[ $opt_id ] ) ? $pirate_forms_options[ $opt_id ] : $opt_default;
								/* Options if checkbox, select, or radio */
								$opt_options = empty( $value[4] ) ? array() : $value[4];
								switch ( $opt_type ) {
									case 'title':
										if ( ! empty( $opt_name ) ) :
											echo '<h3 class="title">' . $opt_name . '</h3><hr />';
										endif;
										break;
									case 'text':
										/* Display recaptcha secret key and site key only if the Add a reCAPTCHA option is checked */
										$pirateformsopt_recaptcha_field = PirateForms_Util::get_option( 'pirateformsopt_recaptcha_field' );
										if ( ! empty( $opt_id ) && ( ( $opt_id != 'pirateformsopt_recaptcha_sitekey' ) && ( $opt_id != 'pirateformsopt_recaptcha_secretkey' ) ) || ( ! empty( $pirateformsopt_recaptcha_field ) && ( $pirateformsopt_recaptcha_field == 'yes' ) && ( ( $opt_id == 'pirateformsopt_recaptcha_sitekey' ) || ( $opt_id == 'pirateformsopt_recaptcha_secretkey' ) ) ) ) {
											$pirate_forms_is_hidden_class = '';
										} else {
											$pirate_forms_is_hidden_class = 'pirate-forms-hidden';
										}
										?>

										<div class="pirate-forms-grouped <?php echo $pirate_forms_is_hidden_class; ?>">

											<label for="<?php echo $opt_id ?>"><?php echo $opt_name;
											if ( ! empty( $opt_desc ) ) {
												if ( ( $opt_id == 'pirateformsopt_email' ) || ( $opt_id == 'pirateformsopt_email_recipients' ) || ( $opt_id == 'pirateformsopt_confirm_email' ) ) {
													echo '<span class="dashicons dashicons-editor-help"></span>';

												}
												echo '<div class="pirate_forms_option_description">' . $opt_desc . '</div>';
											} ?>

											</label>

											<input name="<?php echo $opt_id; ?>" id="<?php echo $opt_id ?>"
												   type="<?php echo $opt_type; ?>"
												   value="<?php echo stripslashes( $opt_val ); ?>" class="widefat">
										</div>

										<?php
										break;
									case 'textarea':
										?>

										<div class="pirate-forms-grouped">

											<label for="<?php echo $opt_id ?>"><?php echo $opt_name;
											if ( ! empty( $opt_desc ) ) {
												if ( ( $opt_id == 'pirateformsopt_confirm_email' ) ) {
													echo '<span class="dashicons dashicons-editor-help"></span>';

												}
												echo '<div class="pirate_forms_option_description">' . $opt_desc . '</div>';
											} ?>

											</label>

											<textarea name="<?php echo $opt_id; ?>" id="<?php echo $opt_id ?>"
													  type="<?php echo $opt_type; ?>" rows="5"
													  cols="30"><?php echo stripslashes( $opt_val ); ?></textarea>
										</div>

										<?php
										break;
									case 'select':
										?>
										<div class="pirate-forms-grouped">

											<label for="<?php echo $opt_id ?>"><?php echo $opt_name;
											if ( ! empty( $opt_desc ) ) {
												if ( ( $opt_id == 'pirateformsopt_thank_you_url' ) ) {
													echo '<span class="dashicons dashicons-editor-help"></span>';

												}
												echo '<div class="pirate_forms_option_description">' . $opt_desc . '</div>';

											} ?>

											</label>

											<select name="<?php echo $opt_id ?>" id="<?php echo $opt_id; ?>">
												<?php
												foreach ( $opt_options as $key => $val ) :
													$selected = '';
													if ( $opt_val == $key ) {
														$selected = 'selected';
													}
													?>
													<option value="<?php echo $key ?>" <?php echo $selected; ?>><?php echo $val; ?></option>
												<?php endforeach; ?>
											</select>

										</div>

										<?php
										break;
									case 'radio':
										if ( ! is_array( $value[3] ) ) {
											break;
										}
										?>
										<div class="pirate-forms-grouped">
											<label for="<?php echo $opt_id ?>"><?php echo $opt_name;
											if ( ! empty( $opt_desc ) ) {
												if ( ( $opt_id == 'pirateformsopt_store' ) || ( $opt_id == 'pirateformsopt_nonce' ) ) {
													echo '<span class="dashicons dashicons-editor-help"></span>';

												}
												echo '<div class="pirate_forms_option_description">' . $opt_desc . '</div>';
											} ?>

											</label>

											<?php
												$index_radio  = 0;
											foreach ( $value[3] as $key1 => $label1 ) {
												$checked    = $opt_val == $key1 ? 'checked' : '';
												if ( $index_radio++ == 0 ) {
													$checked    = 'checked';
												}
											?>
											<input type="radio" value="<?php echo $key1;?>" name="<?php echo $opt_id; ?>"
										   id="<?php echo $opt_id; ?><?php echo $key1 ?>" <?php echo $checked; ?>><?php echo $label1;?>
											&nbsp;

											<?php
											}
											?>
										</div>
										<?php
										break;
									case 'checkbox':
										?>
										<div class="pirate-forms-grouped">

											<label for="<?php echo $opt_id ?>"><?php echo $opt_name;
											if ( ! empty( $opt_desc ) ) {
												if ( ( $opt_id == 'pirateformsopt_store' ) || ( $opt_id == 'pirateformsopt_nonce' ) ) {
													echo '<span class="dashicons dashicons-editor-help"></span>';

												}
												echo '<div class="pirate_forms_option_description">' . $opt_desc . '</div>';
											} ?>

											</label>

											<?php
											$checked = '';
											if ( ( $opt_val == 'yes' ) ) {
												$checked = 'checked';
											}
											?>

											<input type="checkbox" value="yes" name="<?php echo $opt_id; ?>"
												   id="<?php echo $opt_id; ?>" <?php echo $checked; ?>>Yes

										</div>

										<?php
										break;
								}// End switch().
							endforeach;
							?>
							<input name="save" type="submit" value="<?php _e( 'Save changes', 'pirate-forms' ) ?>"
								   class="button-primary pirate-forms-save-button">
							<input type="hidden" name="action" value="save">
							<input type="hidden" name="proper_nonce"
								   value="<?php echo wp_create_nonce( $current_user->user_email ) ?>">

						</form><!-- .pirate_forms_contact_settings -->
						<div class="ajaxAnimation"></div>
					</div><!-- .pirate-forms-tab-pane -->

					<?php endforeach; ?>

				</div><!-- .pirate-forms-tab-content -->
			</div><!-- .pirate-options -->
		</div><!-- .pirate-options -->

		<div id="pirate-forms-sidebar">
			<?php do_action( 'pirate_forms_load_sidebar' ); ?>
		</div>

		<div class="clear"></div>
		</div><!-- .wrap -->
