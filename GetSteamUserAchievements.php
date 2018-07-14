<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Steam User Achievements</title>
<style>
a { color: white; }
body, table { background-color: #001018; }
#Main, #User
 {
    background-color: #023;
	 padding: 0.5em;
	 color: #acb2b8;
	 max-width: 65em;
	 margin: auto;
}
#ReviewText, #ReviewTitle {
    background-color: #001018;
	 padding: 0.5em;
	 color: #acb2b8;
	 max-width: 50em;
}
#ReviewTitle img { padding-right: 0.5em }
</style>
</head>

<body>
<div id="Main">
<?php
//=== Get Steam User Achievements v1.1 / By ManuTOO

function Show($String)
{
	global $Debug;

	if ($Debug)
		echo $String."<br />\n";
}

	//=== Init
	$PublisherKey = 'EnterYourWebApiKeyHere';		// Set your Key here ; doc : https://partner.steamgames.com/doc/webapi_overview/auth ; API doc : https://partner.steamgames.com/doc/webapi

	$GameList = array(													// Set your game list here
							array('Id' => 346470, 'Name' => 'Tennis Elbow 2013'),
							array('Id' => 760640, 'Name' => 'Tennis Elbow 4'),
							array('Id' => 528110, 'Name' => 'Tennis Elbow Manager'),
							array('Id' => 760630, 'Name' => 'Tennis Elbow Manager 2'),
							array('Id' => 409450, 'Name' => 'The Fall of the Dungeon Guardians'),
							//array('Id' => , 'Name' => ),
							);

	//=== Get Parameters
	$Debug = isset($_REQUEST['Debug']);

	if ($Debug)
		error_reporting(E_ALL);

	$AppID = isset($_REQUEST['AppID']) ? $_REQUEST['AppID'] : false;
	$User = isset($_REQUEST['User']) ? $_REQUEST['User'] : false;

	$Custom = isset($_REQUEST['CustomAppID']) && $_REQUEST['CustomAppID'] != 0;
	if ($Custom)
		$AppID = $_REQUEST['CustomAppID'];

	$SortAchievement = (isset($_REQUEST['SortAchievement']) && $_REQUEST['SortAchievement']) || !$AppID;

	//=== Check for Review to extract AppID
	$Index = strpos($User, '/recommended/');
	$CleanedUser = false;
	$UserFromReview = false;

	if ($Index !== false)
	{
		$AppID = str_replace('/', '', substr($User, $Index + strlen('/recommended/')));
		$User = substr($User, 0, $Index);
		$UserFromReview = true;
	}

?>
	<h1>Get Steam User Achievements</h1>
	<form action="GetSteamUserAchievements.php" method="post" name="form1" id="form1" onsubmit="return DoSubmit();">
		<fieldset>
			<p>
				<label for="AppID">Game:</label>
				<select name="AppID" id="AppID">
				<option value='0' <?php if ($AppID == 0) echo 'selected'; ?> >Auto</option>
					<?php
						$GameFound = false;

						foreach ($GameList as $Game)
						{
							echo "<option value='{$Game['Id']}' ";
							if ($Game['Id'] == $AppID)
							{
								echo 'selected';
								$GameFound = true;
							}
							echo ">{$Game['Name']}</option>\n";
						}

						if ($Custom)
							echo "<option value='-1' selected>Custom AppID</option>\n";
					?>
				</select>
			</p>
			<p>
				<label for="CustomAppID">Custom AppID:</label>
				<input name="CustomAppID" type="text" size="10" <?php if (!$GameFound && $AppID != 0) echo ' value="'.$AppID.'"'; ?> />
			</p>
			<p><label for="User">User:</label>
			<input name="User" type="text" size="60" <?php if ($User !== false) echo ' value="'.$User.'"'; ?> />
			<br />
			(SteamID ; or URL to User Profile : https://steamcommunity.com/id/&lt;NickName&gt; or https://steamcommunity.com/profiles/&lt;SteamID&gt; ; or URL to Review)</p>
			<p><input name="SortAchievement" type="checkbox" value="SortAchievement" <?php if ($SortAchievement) echo ' checked'; ?> /> Sort Achievements by Unlock Time</p>
			<p><input name="Debug" type="checkbox" value="Debug" <?php if ($Debug) echo ' checked'; ?> /> Debug</p>
			<p><input type="submit" value="Request"></p>
		</fieldset>
	</form>
</div>

<div id="User">
<?php
	if ($User !== false)
	{
		Show('Checking User');

		//=== Check for : https://steamcommunity.com/id/
		$Index = strpos($User, '/id/');
		$VanityID = false;

		if ($Index !== false)	// Vanity ID
		{
			$VanityID = $User = substr($User, $Index + strlen('/id/'));

			$Index = strpos($User, '/');

			if ($Index !== false)		// Need to remove trailing ?
				$VanityID = $User = substr($User, 0, $Index);

			$Url = 'http://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/?key='.$PublisherKey.'&vanityurl='.urlencode($User);
			$Steam = file_get_contents($Url);
			Show($Steam);

			$json_output = json_decode($Steam, true);

			if ($json_output['response']['success'] == 1)
			{
				$SteamID = $json_output['response']['steamid'];
				Show($SteamID);
			}
		}
		//=== Check for : https://steamcommunity.com/profiles/
		else
		{
			$Index = strpos($User, '/profiles/');

			if ($Index !== false)
				$User = substr($User, $Index + strlen('/profiles/'));

			$Index = strpos($User, '/');

			if ($Index !== false)		// Need to remove trailing ?
				$User = substr($User, 0, $Index);

			$SteamID = $User;
		}

		//===
		$Found = false;

		if ($SteamID !== false)
		{
			//=== Display User Name, ID & Avatar
			$SteamID = trim($SteamID);
			$SteamUrl = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key='.$PublisherKey.'&steamids='.$SteamID;

			Show('<br />'.$SteamUrl);

			$json_object = file_get_contents($SteamUrl);
			$json_decoded = json_decode($json_object);

			if (count($json_decoded->response->players) > 0)
			{
				foreach ($json_decoded->response->players as $player)
				{
					/*echo "<br/>Player ID: $player->steamid
						<br/>Player Name: $player->personaname
						<br/>Profile URL: $player->profileurl
						<br/>SmallAvatar: <img src='$player->avatar'/>
						<br/>MediumAvatar: <img src='$player->avatarmedium'/>
						<br/>LargeAvatar: <img src='$player->avatarfull'/>
						";*/

					echo "<h2><a href='$player->profileurl'>$player->personaname - $player->steamid</h2>"
						."<img src='$player->avatarfull' /></a>"
						.'<br />Last log off : '.date('Y/m/d H:i:s', $player->lastlogoff)
						."<br /><a href='{$player->profileurl}reviews'>All Reviews"
						."<br />\n";


					$Found = true;
				}
			}
			
			//=== Show App Infos
			if ($AppID == 0)	// Check all apps ?
			{
				//=== Get all owner Apps
				$SteamUrl = 'http://api.steampowered.com/ISteamUser/GetPublisherAppOwnership/v2/?key='.$PublisherKey.'&steamid='.$SteamID;
				
				Show('<br />'.$SteamUrl);
		
				$json_object = file_get_contents($SteamUrl);
				$json_decoded = json_decode($json_object);
				
				//echo $json_object."<br />\n";		// Returns only ownership for games associated to Web API Key
				
				foreach ($json_decoded->appownership->apps as $Ownership)
				{
					//echo $Ownership->appid.'<br />';
					
					if ($Ownership->ownsapp)
					{
						CheckApp($Ownership->appid, true);
					}
				}
			}
			else	// Check Selected App
				CheckApp($AppID);
		}
		
		if (!$Found)
		{
			echo 'Wrong User<br>';
		}
	}
	
function CompareAchievement($a, $b)
{
	return $a->unlocktime > $b->unlocktime;
}
	
function CheckApp($AppID, $SkipIfNoName = false)
{
	global $PublisherKey, $SteamID, $VanityID, $UserFromReview, $SortAchievement;
	
	//=== Grab Achievements Details
	$SteamUrl = 'http://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key='.$PublisherKey.'&appid='.$AppID;

	$json_object = file_get_contents($SteamUrl);
	$json_decoded = json_decode($json_object);
	
	$GameName = $json_decoded->game->gameName;
	
	if ($GameName == '')		// If there's no achievement, then GetSchemaForGame doesn't return the game name
	{
		global $GameList;
		
		foreach ($GameList as $Game)
		{
			if ($Game['Id'] == $AppID)
			{
				$GameName = $Game['Name'];
				break;
			}
		}
	}
	
	if ($SkipIfNoName && $GameName == '')
		return;
	
	$AchievementDesc = $json_decoded->game->availableGameStats->achievements;
	$SchemaForGame = $json_decoded;
	
	Show('<br />'.$SteamUrl);
	//echo '<br />Achievements:'.$json_object.'<br />';
	
	//=== Check App Ownership
	$SteamUrl = 'http://api.steampowered.com/ISteamUser/CheckAppOwnership/v2/?key='.$PublisherKey.'&steamid='.$SteamID.'&appid='.$AppID;
	
	Show('<br />'.$SteamUrl);

	$json_object = file_get_contents($SteamUrl);
	$json_decoded = json_decode($json_object);
	
	//=== Game Name & Review
	echo "<h2><a href='http://store.steampowered.com/app/$AppID'>$GameName</a></h2>\n";
	
	if (!$UserFromReview)			// Show Review link only if search wasn't done from Review URL
	{
		$ReviewURL = 'https://steamcommunity.com/'
						. ($VanityID !== false
							? "id/$VanityID/recommended/$AppID/"
							: "profiles/$SteamID/recommended/$AppID/");
							
		$fp = fopen($ReviewURL, 'rb');
		$result = stream_get_contents($fp);
		fclose($fp);
	
		if ($result !== false
			&&	strstr($http_response_header[0], '200') !== false)
		{
			echo "<h3><a href='$ReviewURL'>Review</a></h3>\n";
			
			//=== Get Review Summary
			$Index = strpos($result, '<div class="ratingSummaryBlock" id="ReviewTitle">');
			
			if ($Index !== false)
			{
				$IndexEnd = strpos($result, '<br clear="all" />', $Index);
				
				if ($IndexEnd !== false)
				{
					echo substr($result, $Index, $IndexEnd - $Index);
					
					//=== Get Review Text
					$Index = strpos($result, '<div id="ReviewText">');
					
					if ($Index !== false)
					{
						$IndexEnd = strpos($result, '</div>', $Index);
						
						if ($IndexEnd !== false)
						{
							echo substr($result, $Index, $IndexEnd + strlen('</div>') - $Index);
						}
					}
				}
			}
		}
		else
			echo '<h3>Review</h3><p>N/A</p>'."\n";			// No Review			
				
		//print_r($http_response_header);
	}
	
	//=== Show Ownership details
	echo '<h3>App Ownership:</h3>'."\n";
	
	foreach ($json_decoded->appownership as $Key => $Value)
	{
		if ($Key == 'ownersteamid' && $Value != $SteamID && $Value != '0')
			echo "&nbsp;&nbsp;&nbsp;$Key: <a href='https://steamcommunity.com/profiles/$Value'>$Value</a><br />\n";		
		else
			echo "&nbsp;&nbsp;&nbsp;$Key: $Value<br />\n";
	}
	
	//=== Show all Achievements
	$SteamUrl = 'http://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v1/?key='.$PublisherKey.'&steamid='.$SteamID.'&appid='.$AppID;

	$json_object = file_get_contents($SteamUrl);
	$AchievementList = json_decode($json_object);
	
	Show('<br />'.$SteamUrl);
	Show('NbUserAchiev: '.count($AchievementList->playerstats->achievements));
	Show('NbAchievDesc: '.count($AchievementDesc));
	
	if ($SortAchievement)
	{
		usort($AchievementList->playerstats->achievements, 'CompareAchievement');
	}
	
	for ($Achieved = 1; $Achieved >= 0; --$Achieved)
	{
		echo '<br /><div><table border="1" cellspacing="0" cellpadding="5">'."\n";
		
		foreach ($AchievementList->playerstats->achievements as $UserAchiev)
		{
			//echo $UserAchiev->apiname.'<br />';
			
			if ($UserAchiev->achieved == $Achieved)
			{
				foreach ($AchievementDesc as $Desc)
				{
					if ($Desc->name == $UserAchiev->apiname)
					{
						echo '<tr><td>';
						
						$Icon = $Achieved ? $Desc->icon : $Desc->icongray;
						echo "<img src='$Icon' />";								
						echo '</td><td>';
						
						echo $Desc->displayName;
						echo '</td><td>';
						
						if ($Achieved)
						{
							echo date('Y/m/d H:i:s', $UserAchiev->unlocktime);
							echo '</td><td>';
						}
							
						echo $Desc->description;
						echo '</td></tr>'."\n";
						
						break;
					}
				}
			}
		}
		
		echo '</table></div>'."\n";
	}
	
	//=== Show all Stats
	$SteamUrl = 'http://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v2/?key='.$PublisherKey.'&steamid='.$SteamID.'&appid='.$AppID;

	$json_object = file_get_contents($SteamUrl);
	$StatList = json_decode($json_object);
	
	$StatDesc = $SchemaForGame->game->availableGameStats->stats;
	
	Show('<br />'.$SteamUrl);
	Show('NbUserStat: '.count($StatList->playerstats->stats));
	
	echo "<br />\n".'<div><table border="1" cellspacing="0" cellpadding="5">'."\n";
	
	foreach ($StatList->playerstats->stats as $UserStat)
	{
		//echo $UserStat->name.'<br />';
		$Found = false;
		
		foreach ($StatDesc as $Desc)
		{
			if ($Desc->name == $UserStat->name)
			{
				echo '<tr><td>';
				
				echo $Desc->displayName;
				echo '</td><td>';
				
				echo $UserStat->value;
				echo '</td></tr>'."\n";
				
				$Found = true;
				
				break;
			}
		}
		
		if (!$Found)		// Only needed when adding a new Stat and Steam has still a cached older version
		{
			echo '<tr><td>';
				
			echo $UserStat->name;
			echo '</td><td>';
			
			echo $UserStat->value;
			echo '</td></tr>'."\n";
		}
	}
	
	echo '</table></div>'."\n";
}
?>
</div>
</body>
</html>