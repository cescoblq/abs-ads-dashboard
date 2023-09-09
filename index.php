<?php

/*
This script does the following:

Initializes a new Facebook SDK instance with your app details.
Gets the RedirectLoginHelper to handle the OAuth 2.0 login flow.
Attempts to get an access token from the helper.
If no access token is found (i.e., the user is not logged in), it generates a login URL with the necessary permissions and displays a "Log in with Facebook!" link.
If an access token is found (i.e., the user is logged in), it makes an API call to get a list of ad accounts for the logged-in user.
Next steps would include:

Implement a callback script (fb-callback.php) to handle the OAuth 2.0 callback and store the access token in the session.
Making additional API calls to get detailed information on each campaign in each ad account.
Remember to replace 'YOUR_APP_ID', 'YOUR_APP_SECRET', and 'https://yourwebsite.com/fb-callback.php' with your actual app ID, app secret, and the URL of your callback script.

For a production app, you'll also want to handle errors more gracefully and implement logging and possibly a user interface to display the campaign data.
Remember to always adhere to Meta's terms of service and ensure your use of the API complies with all policies and regulations. You'll find more details and information in the official Meta Marketing API documentation.
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
error_reporting(E_ALL & ~E_DEPRECATED);


require_once __DIR__ . '/vendor/autoload.php';


use Facebook\Facebook;

session_start();


/*
$url = "https://byteblast.ovh/absilone-ads-dashboard/";
$url .= "fb-callback.php";
*/


  
$fb = new Facebook([
  'app_id' => 'APP_ID',
  'app_secret' => 'SECRET',
  'default_graph_version' => 'v17.0',
]);

$helper = $fb->getRedirectLoginHelper();

try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (! isset($accessToken)) {
  //$permissions = ['ads_management'];
  //$permissions = ['public_profile']; 
  //$permissions = ['public_profile', 'ads_read', 'auth_type' => 'rerequest'];  
  $permissions = ['public_profile', 'ads_read'];
  $permissions = ['ads_read'];
  //  $loginUrl = $helper->getLoginUrl('https://byteblast.ovh/absilone-ads-dashboard/fb-callback.php', $permissions, ['auth_type' => 'rerequest']);
  $loginUrl = $helper->getLoginUrl('https://byteblast.ovh/absilone-ads-dashboard/fb-callback.php', $permissions);
	
  // echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
 body {
	margin: 0;
	font-family: 'Arial, sans-serif';
	background-color: #f0f0f0;
	color: #333;
}
.container {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100vh;
	text-align: center;
}
.container h2 {
	margin-bottom: 20px;
}
.container img {
	margin-bottom: 40px;
	//max-width: 100%;
	max-width:300px;
	height: auto;
}
.fb-button {
	display: inline-block;
	padding: 10px 20px;
	color: #fff;
	background-color: #3b5998;
	border: none;
	border-radius: 5px;
	text-decoration: none;
	font-size: 16px;
}
.fb-button:hover {
	background-color: #304d6d;
}
  
</style>
</head>
<body>

<div class="container">
  <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRlcPuxDyPObLugtYO4dc4E1Kks2s71hKNypI7CvvM8&s" alt="Logo" />
  <h2>Absilone dashboard: veuillez vous connecter</h2>
  <a href="<?php echo htmlspecialchars($loginUrl); ?>" class="fb-button">Se connecter avec Facebook</a>
</div>

</body>
</html>

<?php
} else {
  try {
    // Get the FacebookAds\Object\AdAccount object for the current user
    $response = $fb->get('/me/adaccounts', $accessToken->getValue());
  } catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
  }
  
  $graphNode = $response->getGraphNode();
  
  // Now you have a GraphNode object that you can iterate over to get your ad accounts
  // From here, you'd make additional API calls to get the details of each campaign in each ad account
}
?>
