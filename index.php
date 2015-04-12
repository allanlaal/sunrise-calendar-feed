<?php
/**
 * @desc simple! sunset-sunrise VCALENDAR calendar generator to be used in Google Calendar, your phone and elsewhere
 * @since 2015-04-12
 * @author Allan Laal <allan@permanent.ee>
 * @example http://sun.is.permanent.ee/?latitude=59.4388618469&longitude=24.7544727325&title=sunrise,sunset,length&label_sunrise=↑&label_sunset=↓&start=-100&end=365
 * @link https://github.com/allanlaal/sunrise
 */
$version = '20150401T000000Z'; // modify this when you make changes in the code!
$default_latitude = 59.4388618469;
$default_longitude = 24.7544727325;

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


// buffer output so if anything fails, it wont display a partial calendar
$out = "BEGIN:VCALENDAR\r\n";
$out .= "PRODID:-//Permanent Solutions Ltd//Sunrise Sunset Calendar//EN\r\n";
$out .= "VERSION:5.1.2\r\n";
$out .= "CALSCALE:GREGORIAN\r\n";
$out .= "METHOD:PUBLISH\r\n";
$out .= "X-WR-TIMEZONE:UTC\r\n";

$now = date('Y-m-d', time());
for ($day=param('start', 0); $day<=param('end', 365); $day++)
{
	$out .= "BEGIN:VEVENT\r\n";
	$out .= "DTSTART;VALUE=DATE:".date('Ymd', strtotime($now.' +'.$day.' days'))."\r\n";
	$out .= "DTEND;VALUE=DATE:".date('Ymd', strtotime($now.' +'.($day+1).' days'))."\r\n";
	$out .= "DTSTAMP:".date('Ymd\THis\Z')."\r\n";
	$out .= "UID:Permanent-Sunrise-".date('Ymd', strtotime($now.' +'.$day.' days'))."\r\n";
	$out .= "CLASS:PUBLIC\r\n";
	$out .= "CREATED:$version\r\n";
	$out .= "GEO:".param('latitude', $default_latitude).';'.param('longitude', $default_longitude)."\r\n"; //@see http://tools.ietf.org/html/rfc2445

	$sun_info = date_sun_info(strtotime($now.' +'.$day.' days'), param('latitude', $default_latitude), param('longitude', $default_longitude));

	$out .= 'DESCRIPTION:';
		$out .= date('H:i', $sun_info['astronomical_twilight_begin']).	' Start of astronomical twilight\r\n\r\n';
		$out .= date('H:i', $sun_info['nautical_twilight_begin']).		' Start of nautical twilight\r\n\r\n';
		$out .= date('H:i', $sun_info['civil_twilight_begin']).		' Start of civil twilight\r\n\r\n';
		$out .= date('H:i', $sun_info['sunrise']).						' Sunrise\r\n\r\n';
		$out .= date('H:i', $sun_info['transit']).						' Noon\r\n\r\n';
		$out .= date('H:i', $sun_info['sunset']).						' Sunset\r\n\r\n';
		$out .= date('H:i', $sun_info['civil_twilight_end']).			' End of civil twilight\r\n\r\n';
		$out .= date('H:i', $sun_info['nautical_twilight_end']).		' End of nautical twilight\r\n\r\n';
		$out .= date('H:i', $sun_info['astronomical_twilight_end']).	' End of astronomical twilight\r\n\r\n';
	$out .= "\r\n";

	$out .= "LAST-MODIFIED:$version\r\n";
	$out .= "SEQUENCE:0\r\n";
	$out .= "STATUS:CONFIRMED\r\n";
	
	$out .= "SUMMARY:";
		foreach (explode(',', param('title', 'sunrise,sunset,length')) as $title)
		{
			if ($title == 'length')
			{
				$day_length = $sun_info['sunset'] - $sun_info['sunrise'];
				$day_length_h = intval($day_length/60/60);
				$day_length_min = round(($day_length - $day_length_h*60*60)/60, 0);

				$out .= param('label_length', "")."{$day_length_h}h";
				$out .= str_pad($day_length_min, 2, '0', STR_PAD_LEFT);
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
header('Content-Disposition: inline; filename=sunrise.ics');
echo $out;
