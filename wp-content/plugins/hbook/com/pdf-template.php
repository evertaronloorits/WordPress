<?php
$output = '
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Calendar</title>
		<style>
			@font-face {
				font-family: "Lato";
				font-style: normal;
				font-weight: normal;
				src: url("' . plugin_dir_url(__FILE__) . 'assets/fonts/Lato-Regular.ttf") format("truetype");
			  }
			  @font-face {
				font-family: "Lato";
				font-style: normal;
				font-weight: bold;
				src: url("' . plugin_dir_url(__FILE__) . 'assets/fonts/Lato-Bold.ttf") format("truetype");
			  }
			.omnivo_calendar_clearfix:after
			{
				font-size: 0px;
				content: ".";
				display: block;
				height: 0px;
				visibility: hidden;
				clear: both;
			}
			div.rtl 
			{
				direction: rtl;
			}
			.omnivo_calendar_hide
			{
				display: none;
			}

			.omnivo_calendar.small
			{
				font-size: 13px;
			}
			.omnivo_calendar.small .box_header
			{
				font-size: 16px;
				font-weight: 700;
				color: #34495E;
				font-family: "Lato";
                ';
if($pdf_font=='dejavusans')
{
    $output .= 'font-family: "DejaVu Sans";';
}
$output .=       '
				margin: 30px 0 0 0;
			}
			.rtl .omnivo_calendar.small .box_header
			{
				text-align: right;
			}
			/* --- items list --- */
			.omnivo_calendar_items_list
			{
				padding: 0;
				margin: 0;
				list-style: none;
			}
			.omnivo_calendar_items_list li
			{
				padding: 12px 0;
				margin: 0;
				list-style: none;
				border-bottom: 1px solid #E0E0E0;
				background-position: left center;
				background-repeat: no-repeat;
				line-height: 120%;
			}
			.rtl .omnivo_calendar_items_list li 
			{
				background-position: left center;
			}
			.omnivo_calendar_items_list .event_container
			{
				float: left;
				width: 302px;
				padding: 0;
				float: left;
			}
			.rtl .omnivo_calendar_items_list .event_container
			{
				float: right;
				text-align: right;
			}
			.omnivo_calendar_items_list a,
			.omnivo_calendar_items_list span
			{
				font-family: "Lato";
                ';
if($pdf_font=='dejavusans')
{
    $output .= 'font-family: "DejaVu Sans";';
}
$output .= 
                '
				color: #34495E;
				text-decoration: none;
				outline: none;
				font-weight: normal;
			}
			.omnivo_calendar_items_list span.event_description
			{
				color: #6E7A87;
				display: block;
				margin-top: 10px;
			}
			.omnivo_calendar_items_list span.available_slots
			{
				display: block;
				margin-top: 9px;
			}
			.omnivo_calendar_items_list span.available_slots,
			.omnivo_calendar_items_list span.available_slots span.count
			{
				color: #FF8400;
			}
			.omnivo_calendar_items_list .value
			{
				float: right;
				text-align: right;
				color: #34495E;
			}
			.rtl .omnivo_calendar_items_list .value 
			{
				float: left;
			}
			.omnivo_calendar.small .omnivo_calendar_items_list .value a.event_hour_booking
			{
				display: none;
			}
		</style>
	</head>
	<body>
		<div class="omnivo_calendar_responsive">
			' . $omnivo_calendar_pdf_html_content . '
		</div>
	</body>
</html>';
return $output;
