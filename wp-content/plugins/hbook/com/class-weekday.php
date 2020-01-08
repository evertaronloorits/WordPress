<?php
class Omnivo_Calendar_Weekday extends Omnivo_Calendar_Post
{
    public static function GetDefaultFetchArgs()
    {
        $defaults = array(
            'post_type' => 'omnivo_weekdays',
        );
        $parent_defaults = parent::GetDefaultFetchArgs();
        $defaults += $parent_defaults;
        return $defaults;
    }

    protected static function GetDefaultCreateArgs()
    {
        $defaults = array(
            'post_type' => 'omnivo_weekdays',
        );
        $parent_defaults = parent::GetDefaultCreateArgs();
        $defaults += $parent_defaults;
        return $defaults;
    }
}