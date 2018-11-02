# Google Calendar Api Microservice

Application uses OAuth2 to authorize calendar.

# configure
1. Verify your domain at https://www.google.com/webmasters/tools/home?hl=pl&pli=1
2. Create project at https://console.cloud.google.com/apis/dashboard
3. Go to credentials and add Api Key and OAuth credentials as web application
4. Add redirect url to ***vrified.domain/authorize***
5. Download credentials as JSON and put it to ***config/credentials.json***
5. Enable Google Calendar Api at https://console.cloud.google.com/apis/library/calendar-json.googleapis.com

# adding event
1. At first verify your email address at  **/authorize/your@address.email**
2. Add strategy of event data in ***src/Application/Event/Strategy/...Event.php***
3. Post your data to ***.../event/{eventStrategy}/{verifiedCalendarId}*** to add event to calendar