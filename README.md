# sunrise
**Simple!** sunset-sunrise VCALENDAR calendar generator to be used in Google Calendar, your phone and elsewhere

## usage

Example calendar URL:
http://sun.is.permanent.ee/?latitude=59.4388618469&longitude=24.7544727325&title=sunrise,sunset,length&label_sunrise=↑&label_sunset=↓&start=-100&end=365

Modify the parameters in the URL according to your needs. You will atleast need to modify latitude and longitude.

### latitude
The latitude of your location, in degrees.

	latitude=59.4388618469

### longitude
The longitude of your location, in degrees.

	longitude=24.7544727325

### title
This sets what the calendar events title will be. You can use any of the below variables in any order:

variable						| description
:---------------				| :---------
astronomical_twilight_begin		| Start of astronomical twilight
nautical_twilight_begin			| Start of nautical twilight
civil_twilight_begin			| Start of civil twilight
sunrise							| Sunrise
transit							| Noon
sunset							| Sunset
civil_twilight_end				| End of civil twilight
nautical_twilight_end			| End of nautical twilight
astronomical_twilight_end		| End of astronomical twilight
length							| Length of time from Sunrise until Sunset

	title=sunrise,sunset,length

### labels
Normally the values of the variables in the calendar event titles are not prefixed with any text to conserve space, but you can change that by adding an URL parameter with the variable name prefixed with 'label_'.

	label_astronomical_twilight_end=End%20of%20twilight

### start
What day to start the calendar on from today. 

	start=-100

### end
What day to end the calendar on from today.

	end=365
