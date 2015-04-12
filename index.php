<?php
/**
 * @desc simple! sunset-sunrise iCalendar generator to be used in Google Calendar, your phone and elsewhere
 * @since 2015-04-12
 * @author Allan Laal <allan@permanent.ee>
 * @example http://sun.is.permanent.ee/?latitude=59.4388618469&longitude=24.7544727325&title=sunrise,sunset,length&label_sunrise=↑&label_sunset=↓&start=-100&end=365
 * @link https://github.com/allanlaal/sunrise-calendar-feed
 */
$version = '20150410T000000Z'; // modify this when you make changes in the code!

// include config:
require_once('./config.php');

// get and set timezone:
$latitude = param('latitude', $config['default_latitude']);
$longitude = param('longitude', $config['default_longitude']);
		
// get and set timezone by latitude and longitude
$url = "https://maps.googleapis.com/maps/api/timezone/json?location=$latitude,$longitude&timestamp=".time()."&key=".$config['google_timezone_api_key'];
$json = file_get_contents($url);
$timezone = json_decode($json);

if ($timezone->status != 'OK')
{
	die("ERROR! Cannot detect a timezone\n");
}
// else:

date_default_timezone_set($timezone->timeZoneId);


// buffer output so if anything fails, it wont display a partial calendar
$out = "BEGIN:VCALENDAR\r\n";
$out .= "PRODID:-//Permanent Solutions Ltd//Sunrise Sunset Calendar//EN\r\n";
$out .= "VERSION:5.1.4\r\n";
$out .= "CALSCALE:GREGORIAN\r\n";
$out .= "METHOD:PUBLISH\r\n";
$out .= "X-WR-TIMEZONE:".$timezone->timeZoneId."\r\n";
$out .= "URL:https://github.com/allanlaal/sunrise-calendar-feed\r\n";
$out .= "X-WR-CALNAME:Sunrise-Sunset\r\n";
$out .= "X-WR-CALDESC:Display sunset and sunrise times as an all day event from a constantly updating vcalendar/ICS calendar in Google Calendar, your phone or elsewhere.\r\n";
$out .= "X-LOTUS-CHARSET:UTF-8\r\n";

//$out .= "X-PUBLISHED-TTL:".(30*24*60*60)."\r\n"; // check back in 1 month
//$out .= "REFRESH-INTERVAL\r\n";



$now = date('Y-m-d', time());
for ($day=param('start', 0); $day<=param('end', 365); $day++)
{
	$out .= "BEGIN:VEVENT\r\n";
	$out .= "DTSTART;VALUE=DATE:".date('Ymd', strtotime($now.' +'.$day.' days'))."\r\n";
	$out .= "DTEND;VALUE=DATE:".date('Ymd', strtotime($now.' +'.($day+1).' days'))."\r\n";
	$out .= "DTSTAMP:".date('Ymd\THis\Z')."\r\n";
	$out .= "UID:Permanent-Sunrise-".date('Ymd', strtotime($now.' +'.$day.' days'))."-$version\r\n";
	$out .= "CLASS:PUBLIC\r\n";
	$out .= "CREATED:$version\r\n";
	$out .= "GEO:$latitude;$longitude\r\n"; //@see http://tools.ietf.org/html/rfc2445

	$sun_info = date_sun_info(strtotime($now.' +'.$day.' days'), $latitude, $longitude);

	$out .= 'DESCRIPTION:';
		$out .= date('H:i', $sun_info['astronomical_twilight_begin']).	' Start of astronomical twilight\n';
		$out .= date('H:i', $sun_info['nautical_twilight_begin']).		' Start of nautical twilight\n';
		$out .= date('H:i', $sun_info['civil_twilight_begin']).			' Start of civil twilight\n';
		$out .= date('H:i', $sun_info['sunrise']).						' Sunrise\n';
		$out .= date('H:i', $sun_info['transit']).						' Noon\n';
		$out .= date('H:i', $sun_info['sunset']).						' Sunset\n';
		$out .= date('H:i', $sun_info['civil_twilight_end']).			' End of civil twilight\n';
		$out .= date('H:i', $sun_info['nautical_twilight_end']).		' End of nautical twilight\n';
		$out .= date('H:i', $sun_info['astronomical_twilight_end']).	' End of astronomical twilight\n';
		$out .= '\n';
		$out .= calc_day_length($sun_info['sunset'], $sun_info['sunrise']).											' Day length from Sunrise until Sunset\n';
		$out .= calc_day_length($sun_info['civil_twilight_end'], $sun_info['civil_twilight_begin']).				' Day length for civil twilight\n';
		$out .= calc_day_length($sun_info['nautical_twilight_end'], $sun_info['nautical_twilight_begin']).			' Day length for nautical twilight\n';
		$out .= calc_day_length($sun_info['astronomical_twilight_end'], $sun_info['astronomical_twilight_begin']).	' Day length for astronomical twilight\n';
	$out .= "\r\n";

	$out .= "LAST-MODIFIED:$version\r\n";
	$out .= "SEQUENCE:0\r\n";
	$out .= "STATUS:CONFIRMED\r\n";
	
	$out .= "SUMMARY:";
		foreach (explode(',', param('title', 'sunrise,sunset,length')) as $title)
		{
			if (strpos($title, 'length') !== FALSE)
			{
				if ($title !== 'length')
				{
					$type = str_replace('length_', '', $title);
					$length = calc_day_length($sun_info[$type.'_twilight_end'], $sun_info[$type.'_twilight_begin']);
				}
				else
				{
					$length = calc_day_length($sun_info['sunset'], $sun_info['sunrise']);
				}
				
				$out .= param('label_'.$title, "").$length;
			}
			else
			{
				$out .= param('label_'.$title).date('H:i', $sun_info[$title]);
			}
			$out .= ' ';
		}
	$out .= "\r\n";
	
	$out .= "TRANSP:OPAQUE\r\n";
	$out .= "END:VEVENT\r\n";
	
}

$out .= 'END:VCALENDAR';


header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename='.param('filename'));
echo $out;


/**
 * @param int $sunset
 * @param int $sunrise
 * @return string
 * @example 14h28
 */
function calc_day_length($sunset, $sunrise)
{
	$day_length = $sunset - $sunrise;
	$day_length_h = intval($day_length/60/60);
	$day_length_min = round(($day_length - $day_length_h*60*60)/60, 0);
	$length = "{$day_length_h}h".str_pad($day_length_min, 2, '0', STR_PAD_LEFT);
	
	return $length;
}


/**
 * @param string $name
 * @param string $default
 * @return string
 * @desc GET an URL parameter
 */
function param($name, $default='')
{
//	echo "&$name=$default"; // builds URL parameters with the default values
	
	if (
		isset($_GET[$name])
		&& 
		!empty($_GET[$name])
	)
	{
		$out = filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING);
	}
	else
	{
		$out = $default;
	}
	
	return $out;
}