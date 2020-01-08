<div class="wrap omnivo_calendar_settings_section first">
	<h2><?php _e("Email configuration", "omnivo_calendar"); ?></h2>
</div>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" class="email_config ">
	<input type="hidden" name="action" value="save" />
	<div id="omnivo_calendar_configuration_tabs" class="omnivo_calendar_hide">
		<ul class="nav-tabs">
			<li class="nav-tab">
				<a href="#tab-admin-email">
					<?php _e('Admin email', 'omnivo_calendar'); ?>
				</a>
			</li>
			<li class="nav-tab">
				<a href="#tab-admin-smtp">
					<?php _e('Admin SMTP (optional)', 'omnivo_calendar'); ?>
				</a>
			</li>
			<li class="nav-tab">
				<a href="#tab-email-template-client">
					<?php _e('Email template for client', 'omnivo_calendar'); ?>
				</a>
			</li>
			<li class="nav-tab">
				<a href="#tab-email-template-admin">
					<?php _e('Email template for admin', 'omnivo_calendar'); ?>
				</a>
			</li>
		</ul>
		<div id="tab-admin-email">
			<table class="omnivo_calendar_table form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="admin_name">
								<?php _e("Name", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="<?php echo esc_attr($omnivo_calendar_contact_form_options["admin_name"]); ?>" id="admin_name" name="admin_name">
						</td>
						<td>
							<span class="description"></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="admin_email">
								<?php _e("Email", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="<?php echo esc_attr($omnivo_calendar_contact_form_options["admin_email"]); ?>" id="admin_email" name="admin_email">
						</td>
						<td>
							<span class="description"></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="mail_debug">
								<?php _e("Debug", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="mail_debug" id="mail_debug">
								<option value="no" <?php echo ($omnivo_calendar_contact_form_options["mail_debug"]=="no" ? "selected='selected'" : "") ?>><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="yes" <?php echo ($omnivo_calendar_contact_form_options["mail_debug"]=="yes" ? "selected='selected'" : "") ?>><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e('If debug is enabled, then additional information will be displayed in the booking pop-up.', 'omnivo_calendar'); ?></span>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<th colspan="3">
							<input type="submit" value="Save Options" class="button-primary" name="Submit">
						</th>
					</tr>
				</tbody>
			</table>			
		</div>
		<div id="tab-admin-smtp">
			<table class="omnivo_calendar_table form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="smtp_host">
								<?php _e("Host", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="<?php echo esc_attr($omnivo_calendar_contact_form_options["smtp_host"]); ?>" id="smtp_host" name="smtp_host">
						</td>
						<td>
							<span class="description"></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="smtp_username">
								<?php _e("Username", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="<?php echo esc_attr($omnivo_calendar_contact_form_options["smtp_username"]); ?>" id="smtp_username" name="smtp_username">
						</td>
						<td>
							<span class="description"></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="smtp_password">
								<?php _e("Password", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="password" class="regular-text" value="<?php echo esc_attr($omnivo_calendar_contact_form_options["smtp_password"]); ?>" id="smtp_password" name="smtp_password">
						</td>
						<td>
							<span class="description"></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="smtp_port">
								<?php _e("Port", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="<?php echo esc_attr($omnivo_calendar_contact_form_options["smtp_port"]); ?>" id="smtp_port" name="smtp_port">
						</td>
						<td>
							<span class="description"></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="smtp_secure">
								<?php _e("SMTP Secure", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="smtp_secure" id="smtp_secure">
								<option value="">-</option>
								<option value="ssl" <?php echo ($omnivo_calendar_contact_form_options["smtp_secure"]=="ssl" ? "selected='selected'" : "") ?>><?php _e("ssl", "omnivo_calendar"); ?></option>
								<option value="tls" <?php echo ($omnivo_calendar_contact_form_options["smtp_secure"]=="tls" ? "selected='selected'" : "") ?>><?php _e("tls", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"></span>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<th colspan="3">
							<input type="submit" value="Save Options" class="button-primary" name="Submit">
						</th>
					</tr>
				</tbody>
			</table>			
		</div>
		<div id="tab-email-template-client">
			<table class="omnivo_calendar_table form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="email_subject_client">
								<?php _e("Email subject", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="<?php echo esc_attr($omnivo_calendar_contact_form_options["email_subject_client"]); ?>" id="email_subject_client" name="email_subject_client">
						</td>
						<td>
							<span class="description"></span>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<td colspan="3">
							<?php _e("Available placeholders:", 'omnivo_calendar'); ?>
							<br />
							<strong>{event_title} {column_title} {event_start} {event_end} {event_description_1} {event_description_2} {slots_number} {booking_datetime} {user_name} {user_email} {user_phone} {user_message} {cancel_booking}</strong>
						</td>
					</tr>
					<tr valign="top">
						<td colspan="3">
							<?php wp_editor($omnivo_calendar_contact_form_options["template_client"], "template_client", array("editor_height" => 250, "tinymce" => false));?>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<th colspan="3">
							<input type="submit" value="Save Options" class="button-primary" name="Submit">
						</th>
					</tr>
				</tbody>
			</table>			
		</div>
		<div id="tab-email-template-admin">
			<table class="omnivo_calendar_table form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="email_subject_admin">
								<?php _e("Email subject", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="<?php echo esc_attr($omnivo_calendar_contact_form_options["email_subject_admin"]); ?>" id="email_subject_admin" name="email_subject_admin">
						</td>
						<td>
							<span class="description"></span>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<td colspan="3">
							<?php _e("Available placeholders:", 'omnivo_calendar'); ?>
							<br>
							<strong>{event_title} {column_title} {event_start} {event_end} {event_description_1} {event_description_2} {slots_number} {booking_datetime} {user_name} {user_email} {user_phone} {user_message} {cancel_booking}</strong>
						</td>
					</tr>
					<tr valign="top">
						<td colspan="3">
							<?php wp_editor($omnivo_calendar_contact_form_options["template_admin"], "template_admin", array("editor_height" => 250, "tinymce" => false));?>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<th colspan="3">
							<input type="submit" value="Save Options" class="button-primary" name="Submit">
						</th>
					</tr>
				</tbody>
			</table>			
		</div>
	</div>
</form>