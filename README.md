# GetSteamUserAchievements
Lists an User Steam Achievements for games you have published on Steam

====

Since the latest privacy update, achievements of most users aren't visible anymore, so we are forced to use the WebAPI to check them.

Creating your own .php to do so might require at least a couple of hours if you're not used to the WebAPI, and possibly even more if you're not used to .php .

## So here a ready-to-use .php to put on your website or on your PC if you have a local html+php server :
- Sample : https://github.com/manutoo/GetSteamUserAchievements/blob/master/GetSteamUserAchievements.jpg

## Features :
- Show achievement & stat lists from any user profile URL, for all your games or a selected one
- Show only the related game if entering a review URL
- Show the user review if not searching from the review URL
- Show ownership details

## To use this .php, you have 2 things to do, just under "//=== Init" :
- set your API Key in $PublisherKey (HowTo find it : https://partner.steamgames.com/doc/webapi_overview/auth )
- set your game list in $GameList (optional)

## And 1 optional thing to do if you put this .php on a public server :
- rename the .php and update "form action=" accordingly, and/or put it in a password protected folder

## Quick Search with Firefox
With Firefox, you can create a custom search like this https://support.mozilla.org/en-US/kb/how-search-from-address-bar , which allows to very quickly get the achievement list when reading a review or checking a user profile (eg, just entering in the URL bar: "u <ReviewURL | User Profile URL>")
## If Valve wants to put this .php in the WebAPI (or link to it), it's alright with me ! :+1:
