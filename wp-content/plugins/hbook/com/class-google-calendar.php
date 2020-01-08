<?php

class Omnivo_Calendar_Google_Calendar
{
    public $options;
    public $id;
    public $service_account;
    public $service_account_encoded;
    public $token;
    public $token_expiration;
    public $events_mapping;
    public $current_day;
    public $timezone;
    public $events_data;

    public function GetToken()
    {
        if (!($this->token && $this->token_expiration && $this->token_expiration > time()))
            $this->token = $this->GenerateToken();
        return $this->token;
    }

    public function GetCalendarEventId($event_id)
    {
        if (isset($this->events_mapping[$this->id])
            && isset($this->events_mapping[$this->id][$event_id])) {
            return $this->events_mapping[$this->id][$event_id];
        } else
            return null;
    }

    function SetDefaultOptions()
    {
        $this->options = array(
            'id' => '',
            'service_account_encoded' => '',
            'token' => '',
            'token_expiration' => 0,
            'events_data' => '',
            'events_mapping' => '',
        );
        update_option('omnivo_calendar_google_calendar', $this->options);
    }

    function LoadOptions()
    {
        $this->options = get_option('omnivo_calendar_google_calendar');
        if ($this->options === false)
            $this->SetDefaultOptions();

        $this->id = $this->options['id'];
        $this->service_account_encoded = $this->options['service_account_encoded'];
        $this->service_account = json_decode($this->service_account_encoded);
        $this->token = $this->options['token'];
        $this->token_expiration = $this->options['token_expiration'];
        $this->events_data = $this->options['events_data'];
        $this->events_mapping = $this->options['events_mapping'];
    }

    public function SaveOptions()
    {
        $this->options = array(
            'id' => $this->id,
            'service_account_encoded' => $this->service_account_encoded,
            'token' => $this->token,
            'token_expiration' => $this->token_expiration,
            'events_data' => $this->events_data,
            'events_mapping' => $this->events_mapping,
        );
        update_option('omnivo_calendar_google_calendar', $this->options);
    }

    public function __construct()
    {
        $this->LoadOptions();
        $this->current_day = date('N');
        $this->timezone = new DateTimeZone($this->GetWPTimezone());
    }

    function ExportEvents($eventsHours, $weekdays)
    {
        if (!($this->id != '' && !is_null($this->service_account)))
            return false;

        if (!$eventsHours)
            return false;

        foreach ($eventsHours as $eventHour) {
            $calendarEventDetails = $this->prepareCalendarEvent($eventHour, $weekdays);

            if ($calendarEventId = $this->GetCalendarEventId($eventHour->event_hours_id)) {
                $result = $this->UpdateCalendarEvent($calendarEventId, $calendarEventDetails);
            } else {
                $result = $this->InsertCalendarEvent($calendarEventDetails);
                if ($result != false) {
                    $this->AddCalendarEvent($eventHour->event_hours_id, $result->id);
                }
            }
        }

        return true;
    }

    public function ImportEvents($calendar_event, $weekdays)
    {
        if (!($this->id != '' && $this->service_account != ''))
            return false;

        $calendarEvents = $this->events_data;
        if (!($calendarEvents && $calendarEvents->items))
            return false;

        foreach ($calendarEvents->items as $item) {
            if (!in_array($item->summary, $calendar_event))
                continue;

            $event = Omnivo_Calendar_Event::FetchOne(array(
                'title' => $item->summary,
            ));

            if (is_null($event)) {
                $event = new Omnivo_Calendar_Event();
                $event->post_title = $item->summary;
                $result = Omnivo_Calendar_Event::Insert($event);
                if ($result > 0) {
                    $event = Omnivo_Calendar_Event::FetchOneById($result);
                } else
                    continue;
            }

            $eventHourDetails = $this->prepareEventHourDetails($event, $item, $weekdays);
            $eventHourId = array_search($item->id, $this->events_mapping[$this->id]);
            if ($eventHourId && Omnivo_Calendar_Event_Hour::Exists($eventHourId)) {
                $eventHour = Omnivo_Calendar_Event_Hour::FetchById($eventHourId);
                $eventHour->Set($eventHourDetails);
                $result = Omnivo_Calendar_Event_Hour::Update($eventHour);
            } else {
                $eventHour = new Omnivo_Calendar_Event_Hour($eventHourDetails);
                $result = Omnivo_Calendar_Event_Hour::Insert($eventHour);
                if ($result) {
                    $this->addCalendarEvent($result, $item->id);
                }
            }
        }
        return true;
    }

    function getEvent($calendarEventId)
    {
        $token = $this->getToken();
        if (!$token)
            return false;

        $r = wp_remote_get(
            'https://www.googleapis.com/calendar/v3/calendars/' . $this->id . '/events/' . $calendarEventId . '?access_token=' . $token,
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                )));

        if (is_wp_error($r)) {
            return false;
        }

        try {
            $responseDecoded = json_decode($r['body']);
            if (is_object($responseDecoded)
                && property_exists($responseDecoded, 'kind')
                && $responseDecoded->kind == 'calendar#event'
            ) {
                return $responseDecoded;
            }
        } catch (Exception $ex) {
            //
        }

        return false;
    }

    function ListEvents()
    {
        $token = $this->getToken();
        if (!$token)
            return false;

        $r = wp_remote_get(
            'https://www.googleapis.com/calendar/v3/calendars/' . $this->id . '/events?access_token=' . $token,
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                )));

        if (is_wp_error($r)) {
            return false;
        }

        try {
            $responseDecoded = json_decode($r['body']);
            if (is_object($responseDecoded)
                && property_exists($responseDecoded, 'kind')
                && $responseDecoded->kind == 'calendar#events'
            ) {
                return $responseDecoded;
            }
        } catch (Exception $ex) {
            //
        }

        return false;
    }

    function UniqueCalendarEvents()
    {
        $events = $this->events_data;

        if (!($events && $events->items))
            return false;

        $uniqueCalendarEvents = array();
        foreach ($events->items as $item) {
            if (!in_array($item->summary, $uniqueCalendarEvents))
                $uniqueCalendarEvents[] = $item->summary;
        }
        return $uniqueCalendarEvents;
    }

    function LoadEventsData()
    {
        $this->events_data = $this->ListEvents();
        $this->SaveOptions();
    }

    function InsertCalendarEvent($eventDetails)
    {
        $token = $this->getToken();

        if (!$token)
            return false;

        $r = wp_remote_post( 'https://www.googleapis.com/calendar/v3/calendars/' . $this->id . '/events?access_token=' . $token, array(
            'body'    => $eventDetails,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ) );

        if (is_wp_error($r)) {
            return false;
        }

        try {
            $responseDecoded = json_decode($r['body']);
            if (is_object($responseDecoded)
                && property_exists($responseDecoded, 'kind')
                && $responseDecoded->kind == 'calendar#event'
            ) {
                return $responseDecoded;
            }
        } catch (Exception $ex) {
            //
        }

        return false;
    }

    function UpdateCalendarEvent($calendarEventId, $eventDetails)
    {
        $token = $this->getToken();
        if (!$token)
            return false;

        $r = wp_remote_request( 'http://test.com/test', array(
            'method' => 'PUT',
            'body'    => $eventDetails,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($r)) {
            return false;
        }

        try {
            $responseDecoded = json_decode($r['body']);
            if (is_object($responseDecoded)
                && property_exists($responseDecoded, 'kind')
                && $responseDecoded->kind == 'calendar#event'
            ) {
                return true;
            }
        } catch (Exception $ex) {
            //
        }

        return false;
    }

    function GenerateToken()
    {
        if (!property_exists($this->service_account, 'client_email'))
            return false;

        $header = '{"alg":"RS256","typ":"JWT"}';
        $headerEncoded = $this->base64URLEncode($header);

        $assertionTime = time();
        $expirationTime = $assertionTime + 3600;
        $claimSet = '{
		  "iss":"' . $this->service_account->client_email . '",
		  "scope":"https://www.googleapis.com/auth/calendar",
		  "aud":"https://www.googleapis.com/oauth2/v4/token",
		  "exp":' . $expirationTime . ',
		  "iat":' . $assertionTime . '
		}';
        $claimSetEncoded = $this->base64URLEncode($claimSet);

        $signature = '';
        openssl_sign($headerEncoded . '.' . $claimSetEncoded, $signature, $this->service_account->private_key, 'SHA256');

        $signatureEncoded = $this->base64URLEncode($signature);
        $assertion = $headerEncoded . '.' . $claimSetEncoded . '.' . $signatureEncoded;


        $r = wp_remote_post( 'http://test.com/test', array(
            'content'    => 'grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer&assertion=' . $assertion,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
        ));

        if (is_wp_error($r)) {
            return false;
        }

        try {
            $responseDecoded = json_decode($r['body']);
            if (is_object($responseDecoded)
                && property_exists($responseDecoded, 'access_token')
            ) {
                $this->token = $responseDecoded->access_token;
                $this->token_expiration = $expirationTime;
                $this->SaveOptions();
                return $this->token;
            }
        } catch (Exception $ex) {
            //
        }

        return false;
    }

    protected function base64URLEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function prepareEventHourDetails(Omnivo_Calendar_Event $event, $calendarEvent, $weekdays)
    {
        if (property_exists($calendarEvent->start, 'timeZone'))
            $startTimeZone = new DateTimeZone($calendarEvent->end->timeZone);
        else
            $startTimeZone = new DateTimeZone('+0000');

        if (property_exists($calendarEvent->end, 'timeZone'))
            $endTimeZone = new DateTimeZone($calendarEvent->end->timeZone);
        else
            $endTimeZone = new DateTimeZone('+0000');

        $startDate = new DateTime($calendarEvent->start->dateTime, $startTimeZone);
        $endDate = new DateTime($calendarEvent->end->dateTime, $endTimeZone);
        $dayNum = $startDate->format('N') - 1;

        $weekday = Omnivo_Calendar_Weekday::FetchOne(array(
            'name' => $weekdays[$dayNum],
        ));

        if (is_null($weekday))
            return false;

        $beforeHourText = (property_exists($calendarEvent, 'description') ? $calendarEvent->description : '');

        $eventHourDetails = array(
            'event_id' => $event->ID,
            'weekday_id' => $weekday->ID,
            'start' => $startDate->format('H:i'),
            'end' => $endDate->format('H:i'),
            'before_hour_text' => $beforeHourText,
        );
        return $eventHourDetails;
    }

    protected function prepareCalendarEvent(Omnivo_Calendar_Event_Hour $eventHour, $weekdays)
    {
        $dayNum = array_search(urldecode($eventHour->weekday_name), $weekdays) + 1;
        $offset = $dayNum - $this->current_day;
        $time = strtotime(($offset != 0 ? $offset . ' days' : 'now'));
        $startTimeStr = date('Y-m-d', $time) . ' ' . $eventHour->start;
        $endTimeStr = date('Y-m-d', $time) . ' ' . $eventHour->end;
        $startDate = new DateTime($startTimeStr, $this->timezone);
        $endDate = new DateTime($endTimeStr, $this->timezone);

        $calendarEventDetails = array
        (
            'summary' => $eventHour->event_title,
            'description' => $eventHour->before_hour_text,
            'start' => array
            (
                'dateTime' => $startDate->format(DateTime::RFC3339),
                'timeZone' => $this->timezone->getName(),
            ),
            'end' => array
            (
                'dateTime' => $endDate->format(DateTime::RFC3339),
                'timeZone' => $this->timezone->getName(),
            ),
            'recurrence' => array
            (
                'RRULE:FREQ=WEEKLY;',
            ),
        );
        return $calendarEventDetails;
    }

    protected function AddCalendarEvent($eventHourId, $calendarEventId)
    {
        if (!isset($this->events_mapping[$this->id]))
            $this->events_mapping[$this->id] = array();

        foreach ($this->events_mapping[$this->id] as $key => $val) {
            if ($val == $calendarEventId)
                unset($this->events_mapping[$this->id][$key]);
        }
        $this->events_mapping[$this->id][$eventHourId] = $calendarEventId;
        $this->SaveOptions();
    }

    function GetWPTimezone()
    {
        $timezone_string = get_option('timezone_string');
        if (!$timezone_string) {
            $gmt_offset = get_option('gmt_offset');
            $timezone_string = timezone_name_from_abbr('', $gmt_offset * 3600, false);
            if ($timezone_string === false)
                $timezone_string = timezone_name_from_abbr('', 0, false);
        }
        return $timezone_string;
    }
}
