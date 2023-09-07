<?php
require_once 'vendor/autoload.php';

use Facebook\Facebook;

session_start();

$url = "http://vps-3c609307.vps.ovh.net/absilone-ads-dashboard/"
  
$fb = new Facebook([
  'app_id' => 'YOUR_APP_ID',
  'app_secret' => 'YOUR_APP_SECRET',
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
