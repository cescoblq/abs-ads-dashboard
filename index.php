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

require_once 'vendor/autoload.php';

use Facebook\Facebook;

session_start();

$url = "http://vps-3c609307.vps.ovh.net/absilone-ads-dashboard/"
  
$fb = new Facebook([
  'app_id' => '832712925137681',
  'app_secret' => 'ff994fa6ce341fd37c75707419ae4650',
  'default_graph_version' => 'v12.0',
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
  $permissions = ['ads_management']; 
  $loginUrl = $helper->getLoginUrl($url.'fb-callback.php', $permissions);
  echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
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
