<div class="wrap omnivo_calendar_settings_section first">
	<h2><?php _e("Calendar Shortcode Generator", "omnivo_calendar"); ?></h2>
</div>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="omnivo_calendar_shortcodes">
	<div class="omnivo_calendar_settings">
		<table>
			<tr>
				<td>
					<label for="edit_omnivo_calendar_shortcode_id"><?php _e("Choose shortcode id: ", "omnivo_calendar"); ?></label>
				</td>
				<td>
					<select id="edit_omnivo_calendar_shortcode_id" autocomplete="off">
						<option value="-1"><?php _e("choose...", "omnivo_calendar"); ?></option>
							<?php
								$omnivo_calendar_shortcodes_list = get_option("omnivo_calendar_shortcodes_list");
								if(!empty($omnivo_calendar_shortcodes_list))
								{
									foreach($omnivo_calendar_shortcodes_list as $key=>$val)
									{
										echo "<option value='{$key}'>{$key}</option>";
									}
								}
							?>
					</select>
					<span class="spinner" style="float: none; margin: 0 10px;"></span>
					<img style="display: none; cursor: pointer; margin: 0 10px;" id="shortcode_delete" src="<?php echo OMNIVO_CALENDAR_URL; ?>assets/admin/images/delete.png" alt="del" title="<?php _e("Delete this shortcode", "omnivo_calendar"); ?>">
				</td>
			</tr>
			<tr>
				<td>
					<label for="omnivo_calendar_shortcode_id"><?php _e("Or type new shortcode id *", "omnivo_calendar"); ?></label>
				</td>
				<td>
					<input type="text" class="regular-text" id="omnivo_calendar_shortcode_id" value="" pattern="[a-zA-z0-9_-]+" title="<?php _e("Please use only listed characters: letters, numbers, hyphen(-) and underscore(_)", "omnivo_calendar"); ?>" autocomplete="off"/>
					<span class="description"><?php _e("Unique identifier for Calendar shortcode.", "omnivo_calendar"); ?></span>
				</td>
			</tr>
		</table>
	</div>
</form>
<div class="omnivo_calendar_shortcode_container">
	<input style="width: 580px;" type="text" class="regular-text omnivo_calendar_shortcode" value="[omnivo_calendar]" data-default="[omnivo_calendar]" name="shortcode">
	<button id="copy_to_clipboard1" class="button button-primary" data-clipboard-target=".omnivo_calendar_shortcode"><?php _e("Copy to Clipboard", "omnivo_calendar"); ?></button>
	
	<a href="#" id="omnivo_calendar_shortcode_save1" class="button button-primary"><?php _e("Save", "omnivo_calendar"); ?></a>
	<span class="copy_info"><?php _e("Shortcode has been copied to clipboard!", "omnivo_calendar"); ?></span>
	<div class="shortcode_info"></div>
</div>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="omnivo_calendar_settings">
	<div id="omnivo_calendar_configuration_tabs" class="omnivo_calendar_hide">
		<ul class="nav-tabs">
			<li class="nav-tab">
				<a href="#tab-main">
					<?php _e('Main configuration', "omnivo_calendar"); ?>
				</a>
			</li>
			<li class="nav-tab">
				<a href="#tab-colors">
					<?php _e('Colors', "omnivo_calendar"); echo (!omnivo_calendar_is_licensed() ? ' <span style="color:red">PRO</span>': ''); ?>
				</a>
			</li>
			<li class="nav-tab">
				<a href="#tab-fonts">
					<?php _e('Fonts', "omnivo_calendar"); echo (!omnivo_calendar_is_licensed() ? ' <span style="color:red">PRO</span>': ''); ?>
				</a>
			</li>
			<li class="nav-tab">
				<a href="#tab-booking">
					<?php _e('Booking', "omnivo_calendar"); ?>
				</a>
			</li>
			<li class="nav-tab">
				<a href="#tab-custom-css">
					<?php _e('Custom CSS', "omnivo_calendar"); echo (!omnivo_calendar_is_licensed() ? ' <span style="color:red">PRO</span>': ''); ?>
				</a>
			</li>
		</ul>
		<div id="tab-main">
			<table class="omnivo_calendar_table form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="event">
								<?php _e("Events", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="event" id="event" multiple="multiple">
								<?php echo $events_select_list; ?>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Select the events that are to be displayed in calendar. Hold the CTRL key to select multiple items.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="event">
								<?php _e("Event categories", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="event_category" id="event_category" multiple="multiple">
								<?php echo $events_categories_list ?>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Select the events categories that are to be displayed in calendar. Hold the CTRL key to select multiple items.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hour_category">
								<?php _e("Hour categories", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="hour_category" id="hour_category" multiple="multiple">
								<?php
								foreach($hour_categories as $hour_category)
									echo '<option value="' . $hour_category->category . '">' . $hour_category->category . '</option>';
								?>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Select the hour categories (if defined for existing event hours) for events that are to be displayed in calendar. Hold the CTRL key to select multiple items.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="weekdays">
								<?php _e("Columns", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="weekday" id="weekday" multiple="multiple">
								<?php echo $weekdays_select_list; ?>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Select the columns that are to be displayed in calendar. Hold the CTRL key to select multiple items.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="measure">
								<?php _e("Hour measure", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="measure" id="measure">
								<option value="1"><?php _e("Hour (1h)", "omnivo_calendar"); ?></option>
								<option value="0.5"><?php _e("Half hour (30min)", "omnivo_calendar"); ?></option>
								<option value="0.25"><?php _e("Quarter hour (15min)", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Choose hour measure for event hours.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="filter_style">
								<?php _e("Filter style", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="filter_style" id="filter_style">
								<option value="dropdown_list"><?php _e("Dropdown list", "omnivo_calendar"); ?></option>
								<option value="tabs"><?php _e("Tabs", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Choose between dropdown menu and tabs for event filtering.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="filter_kind">
								<?php _e("Filter kind", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="filter_kind" id="filter_kind">
								<option value="event"><?php _e("By event", "omnivo_calendar"); ?></option>
								<option value="event_category"><?php _e("By event category", "omnivo_calendar"); ?></option>
								<option value="event_and_event_category"><?php _e("By event and event category", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Choose between filtering by events or events categories.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="filter_label">
								<?php _e("Filter label", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="All Events" id="filter_label" name="filter_label">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for all events.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top" class="filter_label_2 omnivo_calendar_hide">
						<th scope="row">
							<label for="filter_label_2">
								<?php _e("Filter label 2", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="All Events Categories" id="filter_label_2" name="filter_label_2">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for all events categories.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="time_format">
								<?php _e("Time format", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e("Time format", "omnivo_calendar"); ?></span></legend>
								<label title="H.i">
									<input type="radio" checked="checked" value="H.i" name="time_format"> 
									<span>09.03</span>
								</label>
								<br>
								<label title="H:i">
									<input type="radio" value="H:i" name="time_format"> 
									<span>09:03</span>
								</label>
								<br>
								<label title="g:i a">
									<input type="radio" value="g:i a" name="time_format"> 
									<span>9:03 am</span>
								</label>
								<br>
								<label title="g:i A">
									<input type="radio" value="g:i A" name="time_format"> 
									<span>9:03 AM</span>
								</label>
								<br>
								<label>
									<input type="radio" value="custom" id="time_format_custom_radio" name="time_format"> 
									<?php _e("Custom: ", "omnivo_calendar"); ?>
								</label>
								<input type="text" class="small-text" value="H.i" name="time_format_custom" id="time_format"> 
								<span class="example"> 9:03 am</span> 
								<span class="spinner"></span>
							</fieldset>
						</td>
						<td></td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hide_all_events_view">
								<?php _e('Hide \'All Events\' view', 'omnivo_calendar'); ?>
							</label>
						</th>
						<td>
							<select name="hide_all_events_view" id="hide_all_events_view">
								<option value="0"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="1"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to Yes to hide All Events view.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hide_hours_column">
								<?php _e("Hide first (hours) column", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="hide_hours_column" id="hide_hours_column">
								<option value="0"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="1"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to Yes to hide calendar column with hours.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="show_end_hour">
								<?php _e("Show end hour in first (hours) column", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="show_end_hour" id="show_end_hour">
								<option value="0"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="1"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to Yes to show both start and end hour in calendar column with hours.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="event_layout">
								<?php _e("Event block layout", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="event_layout" id="event_layout">
								<option value="1"><?php _e("Type 1", "omnivo_calendar"); ?></option>
								<option value="2"><?php _e("Type 2", "omnivo_calendar"); ?></option>
								<option value="3"><?php _e("Type 3", "omnivo_calendar"); ?></option>
								<option value="4"><?php _e("Type 4", "omnivo_calendar"); ?></option>
								<option value="5"><?php _e("Type 5", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Select one of the available event block layouts.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hide_empty">
								<?php _e("Hide empty rows", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="hide_empty" id="hide_empty">
								<option value="0"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="1"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to Yes to hide calendar rows without events.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="disable_event_url">
								<?php _e("Disable event url", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="disable_event_url" id="disable_event_url">
								<option value="0"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="1"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to Yes for nonclickable event blocks.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="text_align">
								<?php _e("Text align", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="text_align" id="text_align">
								<option value="center"><?php _e("center", "omnivo_calendar"); ?></option>
								<option value="left"><?php _e("left", "omnivo_calendar"); ?></option>
								<option value="right"><?php _e("right", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Specify text align in calendar event block.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="row_height">
								<?php _e("Id", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="" id="id" name="id">
						</td>
						<td>
							<span class="description"><?php _e("Assign a unique identifier to a calendar if you use more than one table on a single page. Otherwise, leave this field blank.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="row_height">
								<?php _e("Row height (in px)", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="31" id="row_height" name="row_height">
						</td>
						<td>
							<span class="description"><?php _e("Specify calendar row height in pixels.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="desktop_list_view">
								<?php _e("Display list view on desktop", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="desktop_list_view" id="desktop_list_view">
								<option value="0"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="1"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to Yes to display list view in desktop mode.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="responsive">
								<?php _e("Responsive", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="responsive" id="responsive">
								<option value="1"><?php _e("Yes", "omnivo_calendar"); ?></option>
								<option value="0"><?php _e("No", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to Yes to adjust calendar to mobile devices.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="event_description_responsive">
								<?php _e("Event description in responsive mode", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="event_description_responsive" id="event_description_responsive">
								<option value="none"><?php _e("None", "omnivo_calendar"); ?></option>
								<option value="description-1"><?php _e("Only Description 1", "omnivo_calendar"); ?></option>
								<option value="description-2"><?php _e("Only Description 2", "omnivo_calendar"); ?></option>
								<option value="description-1-and-description-2"><?php _e("Description 1 and Description 2", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Specify if you want to display event description in mobile mode.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="collapse_event_hours_responsive">
								<?php _e("Collapse event hours in responsive mode", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="collapse_event_hours_responsive" id="collapse_event_hours_responsive">
								<option value="0"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="1"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to Yes to collapse event hours in responsive mode, can be expanded on click.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="colors_responsive_mode">
								<?php _e("Use colors in responsive mode", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="colors_responsive_mode" id="colors_responsive_mode">
								<option value="0"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="1"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to Yes to use colors defined in shortcode and in event settings while in responsive mode.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="export_to_pdf_button">
								<?php _e("Export to PDF button", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="export_to_pdf_button" id="export_to_pdf_button">
								<option value="0"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="1"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to Yes to show 'Generate PDF' button. This option also requires enabling responsive mode.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<th scope="row">
							<label for="generate_pdf_label">
								<?php _e("Generate PDF label", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="Generate PDF" id="generate_pdf_label" name="generate_pdf_label">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for 'Generate PDF' button.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<th scope="row">
							<label for="pdf_font">
								<?php _e("PDF Font", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="pdf_font" id="pdf_font">
								<option value="lato"><?php _e("Lato", "omnivo_calendar"); ?></option>
								<option value="dejavusans"><?php _e("DejaVu Sans", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Select font used for PDF files. DejaVu Sans font supports extended characters.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<?php
					/*
					<tr valign="top">
						<th scope="row">
							<label for="direction">
								<?php _e("Direction", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="direction" id="direction">
								<option value="ltr"><?php _e("LTR (Left to Right)", "omnivo_calendar"); ?></option>
								<option value="rtl"><?php _e("RTL (Right to Left)", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Change calendar mode between LTR and RTL", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					*/
					?>
				</tbody>
			</table>
		</div>
		<div id="tab-colors" <?php if(!omnivo_calendar_is_licensed()) { echo 'class="omnivo-pro-section-wrap"'; } ?>>
            <?php if(!omnivo_calendar_is_licensed()){ ?>
                <div class="omnivo-pro-section">
                    <p>This is a PRO Feature</p>
                    <a class="omnivo-pro-button" href="https://pluginjungle.com/downloads/booking-calendar/" target="_blank" >Purchase</a>
                </div>
            <?php } ?>
			<table class="omnivo_calendar_table form-table">

				<tbody>
					<tr>
						<th scope="row">
							<label for="box_bg_color">
								<?php _e('Calendar box background color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #00A27C"></span>
							<input class="regular-text color" type="text" id="box_bg_color" name="box_bg_color" value="00A27C" data-default-color="00A27C" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="box_hover_bg_color">
								<?php _e('Calendar box hover background color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #1F736A"></span>
							<input class="regular-text color" type="text" id="box_hover_bg_color" name="box_hover_bg_color" value="1F736A" data-default-color="1F736A" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="box_txt_color">
								<?php _e('Calendar box text color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #FFFFFF"></span>
							<input class="regular-text color" type="text" id="box_txt_color" name="box_txt_color" value="FFFFFF" data-default-color="FFFFFF" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="box_hover_txt_color">
								<?php _e('Calendar box hover text color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #FFFFFF"></span>
							<input class="regular-text color" type="text" id="box_hover_txt_color" name="box_hover_txt_color" value="FFFFFF" data-default-color="FFFFFF" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="box_hours_txt_color">
								<?php _e('Calendar box hours text color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #FFFFFF"></span>
							<input class="regular-text color" type="text" id="box_hours_txt_color" name="box_hours_txt_color" value="FFFFFF" data-default-color="FFFFFF" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="box_hours_hover_txt_color">
								<?php _e('Calendar box hours hover text color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #FFFFFF"></span>
							<input class="regular-text color" type="text" id="box_hours_hover_txt_color" name="box_hours_hover_txt_color" value="FFFFFF" data-default-color="FFFFFF" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="filter_color">
								<?php _e('Filter control background color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #00A27C"></span>
							<input class="regular-text color" type="text" id="filter_color" name="filter_color" value="00A27C" data-default-color="00A27C" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="row1_color">
								<?php _e('Row 1 style background color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #F0F0F0"></span>
							<input class="regular-text color" type="text" id="row1_color" name="row1_color" value="F0F0F0" data-default-color="F0F0F0" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="row2_color">
								<?php _e('Row 2 style background color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: transparent"></span>
							<input class="regular-text color" type="text" id="row2_color" name="row2_color" value="" data-default-color="transparent" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="generate_pdf_text_color">
								<?php _e('Generate PDF button text color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #FFFFFF"></span>
							<input class="regular-text color" type="text" id="generate_pdf_text_color" name="generate_pdf_text_color" value="FFFFFF" data-default-color="FFFFFF" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="generate_pdf_bg_color">
								<?php _e('Generate PDF button background color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #00A27C"></span>
							<input class="regular-text color" type="text" id="generate_pdf_bg_color" name="generate_pdf_bg_color" value="00A27C" data-default-color="00A27C" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="generate_pdf_hover_text_color">
								<?php _e('Generate PDF button hover text color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #FFFFFF"></span>
							<input class="regular-text color" type="text" id="generate_pdf_hover_text_color" name="generate_pdf_hover_text_color" value="FFFFFF" data-default-color="FFFFFF" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="generate_pdf_hover_bg_color">
								<?php _e('Generate PDF button hover background color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #1F736A"></span>
							<input class="regular-text color" type="text" id="generate_pdf_hover_bg_color" name="generate_pdf_hover_bg_color" value="1F736A" data-default-color="1F736A" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="booking_text_color">
								<?php _e('Booking button text color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #FFFFFF"></span>
							<input class="regular-text color" type="text" id="booking_text_color" name="booking_text_color" value="FFFFFF" data-default-color="FFFFFF" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="booking_bg_color">
								<?php _e('Booking button background color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #05BB90"></span>
							<input class="regular-text color" type="text" id="booking_bg_color" name="booking_bg_color" value="05BB90" data-default-color="05BB90" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="booking_hover_text_color">
								<?php _e('Booking button hover text color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #FFFFFF"></span>
							<input class="regular-text color" type="text" id="booking_hover_text_color" name="booking_hover_text_color" value="FFFFFF" data-default-color="FFFFFF" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="booking_hover_bg_color">
								<?php _e('Booking button hover background color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #07B38A"></span>
							<input class="regular-text color" type="text" id="booking_hover_bg_color" name="booking_hover_bg_color" value="07B38A" data-default-color="07B38A" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="booked_text_color">
								<?php _e('Booked button text color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #AAAAAA"></span>
							<input class="regular-text color" type="text" id="booked_text_color" name="booked_text_color" value="AAAAAA" data-default-color="AAAAAA" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="booked_bg_color">
								<?php _e('Booked button background color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #EEEEEE"></span>
							<input class="regular-text color" type="text" id="booked_bg_color" name="booked_bg_color" value="EEEEEE" data-default-color="EEEEEE" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="unavailable_text_color">
								<?php _e('Unavailable button text color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #AAAAAA"></span>
							<input class="regular-text color" type="text" id="unavailable_text_color" name="unavailable_text_color" value="AAAAAA" data-default-color="AAAAAA" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="unavailable_bg_color">
								<?php _e('Unavailable button background color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #EEEEEE"></span>
							<input class="regular-text color" type="text" id="unavailable_bg_color" name="unavailable_bg_color" value="EEEEEE" data-default-color="EEEEEE" />
						</td>
					</tr>
					<tr class="no-border">
						<th scope="row">
							<label for="available_slots_color">
								<?php _e('Available slots color', "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<span class="color_preview" style="background-color: #FFD544"></span>
							<input class="regular-text color" type="text" id="available_slots_color" name="available_slots_color" value="FFD544" data-default-color="FFD544" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="tab-fonts" <?php if(!omnivo_calendar_is_licensed()) { echo 'class="omnivo-pro-section-wrap"'; } ?>>
            <?php if(!omnivo_calendar_is_licensed()){ ?>
                <div class="omnivo-pro-section">
                    <p>This is a PRO Feature</p>
                    <a class="omnivo-pro-button" href="https://pluginjungle.com/downloads/booking-calendar/" target="_blank" >Purchase</a>
                </div>
            <?php } ?>
			<table class="omnivo_calendar_table form-table">
				<tbody>
					<!--<tr valign="top">
						<th scope="row" class="header_row" colspan="2">
							<label>
								<?php _e("Table header font", "omnivo_calendar"); ?>
							</label>
						</th>
					</tr>-->
					<tr valign="top">
						<th scope="row">
							<label for="omnivo_calendar_font_custom"><?php _e("Enter font name", "omnivo_calendar"); ?></label>
						</th>
						<td>
							<input type="text" class="regular-text" value="" id="omnivo_calendar_font_custom" name="omnivo_calendar_font_custom">
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="omnivo_calendar_font"><?php _e("or choose Google font", "omnivo_calendar"); ?></label>
						</th>
						<td>
							<select name="omnivo_calendar_font" id="omnivo_calendar_font" class="google_font_chooser">
								<option value=""><?php _e("Default", "omnivo_calendar"); ?></option>
								<?php
									echo $fontsHtml;
								?>
							</select>
							<span class="spinner"></span>
						</td>
					</tr>
					<tr valign="top" class="fontSubsetRow">
						<th scope="row">
							<label for="omnivo_calendar_font_subset"><?php _e("Google font subset", "omnivo_calendar"); ?></label>
						</th>
						<td>
							<select name="omnivo_calendar_font_subset[]" id="omnivo_calendar_font_subset" class="fontSubset" multiple="multiple"></select>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<th scope="row">
							<label for="omnivo_calendar_font_size"><?php _e("Font size (in px)", "omnivo_calendar"); ?></label>
						</th>
						<td>
							<input type="text" class="regular-text" value="" id="omnivo_calendar_font_size" name="omnivo_calendar_font_size">
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="tab-booking" >
			<table class="omnivo_calendar_table form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="show_booking_button_button">
								<?php _e("Show booking button", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="show_booking_button" id="show_booking_button">
								<option value="no"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="always"><?php _e("Always", "omnivo_calendar"); ?></option>
								<option value="on_hover"><?php _e("On hover", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Specify if the 'Book now' button should be displayed.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="show_available_slots">
								<?php _e("Show available slots", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="show_available_slots" id="show_available_slots">
								<option value="no"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="always"><?php _e("Always", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Specify if the 'available slots' information should be displayed.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="available_slots_singular_label">
								<?php _e("Available slots singular label", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="{number_available}/{number_total} slot available" id="available_slots_singular_label" name="available_slots_singular_label">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for 'slot available' information (singular). Available placeholders: {number_available}, {number_taken}, {number_total}.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="available_slots_plural_label">
								<?php _e("Available slots plural label", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="{number_available}/{number_total} slots available" id="available_slots_plural_label" name="available_slots_plural_label">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for 'slots available' information (plural). Available placeholders: {number_available}, {number_taken}, {number_total}.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="default_booking_view">
								<?php _e("Default booking view", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="default_booking_view" id="default_booking_view">
								<option value="user"><?php _e("User", "omnivo_calendar"); ?></option>
								<option value="guest"><?php _e("Guest", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Specify which booking view should be visible by default.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr id="allow_user_booking_wrapper" class="omnivo_calendar_hide" valign="top">
						<th scope="row">
							<label for="allow_user_booking">
								<?php _e("Allow user booking", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="allow_user_booking" id="allow_user_booking">
								<option value="yes"><?php _e("Yes", "omnivo_calendar"); ?></option>
								<option value="no"><?php _e("No", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to 'Yes' if you want to allow logged in users to make a booking.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr id="allow_guest_booking_wrapper" valign="top">
						<th scope="row">
							<label for="allow_guest_booking">
								<?php _e("Allow guest booking", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="allow_guest_booking" id="allow_guest_booking">
								<option value="no"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="yes"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to 'Yes' if you want to allow guests to make a booking.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top" class="show_guest_name_field omnivo_calendar_hide">
						<th scope="row">
							<label for="show_guest_name_field">
								<?php _e("Show guest name field", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="show_guest_name_field" id="show_guest_name_field">
								<option value="yes"><?php _e("Yes", "omnivo_calendar"); ?></option>
								<option value="no"><?php _e("No", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to 'Yes' if you want to show 'Name' field in guest booking form.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top" class="guest_name_field_required omnivo_calendar_hide">
						<th scope="row">
							<label for="guest_name_field_required">
								<?php _e("Guest name field required", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="guest_name_field_required" id="guest_name_field_required">
								<option value="yes"><?php _e("Yes", "omnivo_calendar"); ?></option>
								<option value="no"><?php _e("No", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to 'Yes' if the 'Name' field should be required.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top" class="show_guest_phone_field omnivo_calendar_hide">
						<th scope="row">
							<label for="show_guest_phone_field">
								<?php _e("Show guest phone field", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="show_guest_phone_field" id="show_guest_phone_field">
								<option value="no"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="yes"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to 'Yes' if you want to show 'Phone' field in guest booking form.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top" class="guest_phone_field_required omnivo_calendar_hide">
						<th scope="row">
							<label for="guest_phone_field_required">
								<?php _e("Guest phone field required", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="guest_phone_field_required" id="guest_phone_field_required">
								<option value="no"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="yes"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to 'Yes' if the 'Phone' field should be required.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top" class="show_guest_message_field omnivo_calendar_hide">
						<th scope="row">
							<label for="show_guest_message_field">
								<?php _e("Show guest message field", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="show_guest_message_field" id="show_guest_message_field">
								<option value="no"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="yes"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to 'Message' if you want to show 'Phone' field in guest booking form.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top" class="guest_message_field_required omnivo_calendar_hide">
						<th scope="row">
							<label for="guest_message_field_required">
								<?php _e("Guest message field required", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="guest_message_field_required" id="guest_message_field_required">
								<option value="no"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="yes"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to 'Yes' if the 'Message' field should be required.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="booking_label">
								<?php _e("Booking label", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="Book now" id="booking_label" name="booking_label">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for booking button.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="booked_label">
								<?php _e("Booked label", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="Booked" id="booked_label" name="booked_label">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for already booked event.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="unavailable_label">
								<?php _e("Unavailable label", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="Unavailable" id="unavailable_label" name="unavailable_label">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for unavailable event.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="booking_popup_label">
								<?php _e("Popup booking label", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="Book now" id="booking_popup_label" name="booking_popup_label">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for booking button in the popup window.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="login_popup_label">
								<?php _e("Popup login label", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="Log in" id="login_popup_label" name="login_popup_label">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for login button in the popup window.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="cancel_popup_label">
								<?php _e("Popup cancel label", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="Cancel" id="cancel_popup_label" name="cancel_popup_label">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for cancel button in the popup window.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="continue_popup_label">
								<?php _e("Popup continue label", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="Continue" id="continue_popup_label" name="continue_popup_label">
						</td>
						<td>
							<span class="description"><?php _e("Specify text label for continue button in the popup window.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="terms_checkbox">
								<?php _e("Terms and conditions checkbox", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<select name="terms_checkbox" id="terms_checkbox">
								<option value="no"><?php _e("No", "omnivo_calendar"); ?></option>
								<option value="yes"><?php _e("Yes", "omnivo_calendar"); ?></option>
							</select>
						</td>
						<td>
							<span class="description"><?php _e("Set to 'Yes' if you want to display 'Terms and conditions' checkbox.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="terms_message">
								<?php _e("Terms and conditions message", "omnivo_calendar"); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" value="<?php esc_attr_e("Please accept terms and conditions", "omnivo_calendar"); ?>" id="terms_message" name="terms_message">
						</td>
						<td>
							<span class="description"><?php _e("Specify text for 'Terms and conditions' checkbox.", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<th scope="row">
							<label for="booking_popup_message">
								<?php _e("Booking pop-up message", "omnivo_calendar"); ?>
							</label>
						</th>
						<td colspan="2">
							<span class="description long"><?php _e("Specify text that will appear in pop-up window. Available placeholders: {event_title} {column_title} {event_start} {event_end} {event_description_1} {event_description_2} {user_name} {user_email} {omnivo_calendar_btn_book} {omnivo_calendar_btn_cancel} {omnivo_calendar_btn_continue}", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<td colspan="3">
							<?php wp_editor(BOOKING_POPUP_MESSAGE, "booking_popup_message", array("tinymce" => false));?>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<th scope="row">
							<label for="booking_popup_thank_you_message">
								<?php _e("Booking pop-up thank you message", "omnivo_calendar"); ?>
							</label>
						</th>
						<td colspan="2">
							<span class="description long"><?php _e("Specify text that will appear in pop-up window. Available placeholders: {event_title} {column_title} {event_start} {event_end} {event_description_1} {event_description_2} {user_name} {user_email} {omnivo_calendar_btn_continue}", "omnivo_calendar"); ?></span>
						</td>
					</tr>
					<tr valign="top" class="no-border">
						<td colspan="3">
							<?php wp_editor(BOOKING_POPUP_THANK_YOU_MESSAGE, "booking_popup_thank_you_message", array("tinymce" => false));?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="tab-custom-css" <?php if(!omnivo_calendar_is_licensed()) { echo 'class="omnivo-pro-section-wrap"'; } ?>>
            <?php if(!omnivo_calendar_is_licensed()){ ?>
                <div class="omnivo-pro-section">
                    <p>This is a PRO Feature</p>
                    <a class="omnivo-pro-button" href="https://pluginjungle.com/downloads/booking-calendar/" target="_blank" >Purchase</a>
                </div>
            <?php } ?>

			<table class="omnivo_calendar_table form-table">
				<tbody>
					<tr valign="top" class="no-border">
						<th scope="row">
							<label for="omnivo_calendar_custom_css"><?php _e("Custom CSS", "omnivo_calendar"); ?></label>
						</th>
						<td>
							<textarea id="omnivo_calendar_custom_css" name="omnivo_calendar_custom_css" style="width: 540px; height: 200px;"></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</form>
<div class="omnivo_calendar_shortcode_container">
	<input style="width: 580px;" type="text" class="regular-text omnivo_calendar_shortcode" value="[omnivo_calendar]" data-default="[omnivo_calendar]" name="shortcode">
	<button href="#" id="copy_to_clipboard2" class="button button-primary" data-clipboard-target=".omnivo_calendar_shortcode"><?php _e("Copy to Clipboard", "omnivo_calendar"); ?></button>
	<a href="#" id="omnivo_calendar_shortcode_save2" class="button button-primary"><?php _e("Save", "omnivo_calendar"); ?></a>
	<span class="copy_info"><?php _e("Shortcode has been copied to clipboard!", "omnivo_calendar"); ?></span>
	<div class="shortcode_info"></div>
</div>
