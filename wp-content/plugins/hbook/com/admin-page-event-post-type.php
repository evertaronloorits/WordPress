<div class="wrap omnivo_calendar_settings_section first">
	<h2><?php _e("Events post type configuration", "omnivo_calendar"); ?></h2>
</div>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="omnivo_calendar_events_settings">
	<div>
		<table class="omnivo_calendar_table form-table">
			<tr valign="top">
				<th>
					<label for="omnivo_calendar_events_settings_slug"><?php _e("Event slug: ", "omnivo_calendar"); ?></label>
				</th>
				<td>
					<input type="text" class="regular-text" name="omnivo_calendar_events_settings_slug" id="omnivo_calendar_events_settings_slug" value="<?php echo $omnivo_calendar_events_settings["slug"];?>" autocomplete="off" />
				</td>
			</tr>
			<tr valign="top">
				<th>
					<label for="omnivo_calendar_events_settings_label_singular"><?php _e("Event label singular: ", "omnivo_calendar"); ?></label>
				</th>
				<td>
					<input type="text" class="regular-text" name="omnivo_calendar_events_settings_label_singular" id="omnivo_calendar_events_settings_label_singular" value="<?php echo $omnivo_calendar_events_settings["label_singular"];?>" autocomplete="off" />
				</td>
			</tr>
			<tr valign="top">
				<th>
					<label for="omnivo_calendar_events_settings_label_plural"><?php _e("Event label plural: ", "omnivo_calendar"); ?></label>
				</th>
				<td>
					<input type="text" class="regular-text" name="omnivo_calendar_events_settings_label_plural" id="omnivo_calendar_events_settings_label_plural" value="<?php echo $omnivo_calendar_events_settings["label_plural"];?>" autocomplete="off" />

				</td>
			</tr>
			<tr valign="top" class="no-border">
				<td colspan="3">
					<input type="submit" class="button button-primary" name="omnivo_calendar_events_settings_save" id="omnivo_calendar_events_settings_save" value="<?php _e('Save', 'omnivo_calendar'); ?>" />
					<span class="spinner" style="float: none; margin: 0 10px;"></span>
				</td>
			</tr>
			<tr valign="top" class="omnivo_calendar_hide no-border">
				<td colspan="3">
					<div id="event_slug_info"></div>
				</td>
			</tr>
		</table>
	</div>
</form>