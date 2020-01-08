<?php
class Omnivo_Calendar_Event extends Omnivo_Calendar_Post
{

    protected static function GetDefaults()
    {
        $omnivo_calendar_events_settings = omnivo_calendar_events_settings();
        $defaults = array(
            'post_type'			=> $omnivo_calendar_events_settings['slug'],
        );
        $parent_defaults = parent::GetDefaults();
        $defaults += $parent_defaults;
        return $defaults;
    }

    public static function GetDefaultFetchArgs()
    {
        $omnivo_calendar_events_settings = omnivo_calendar_events_settings();
        $defaults = array(
            'post_type' => $omnivo_calendar_events_settings['slug'],
        );
        $parent_defaults = parent::GetDefaultFetchArgs();
        $defaults += $parent_defaults;
        return $defaults;
    }

    protected static function GetDefaultCreateArgs()
    {
        $omnivo_calendar_events_settings = omnivo_calendar_events_settings();
        $defaults = array(
            'post_type' => $omnivo_calendar_events_settings['slug'],
        );
        $parent_defaults = parent::GetDefaultCreateArgs();
        $defaults += $parent_defaults;
        return $defaults;
    }

}