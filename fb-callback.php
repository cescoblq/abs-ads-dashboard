<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);


/* This script does the following:

Initializes a new instance of the Facebook SDK and the RedirectLoginHelper.
Tries to get the access token from the redirect callback.
If an access token is obtained, it validates the access token, checks the app ID, and ensures the access token hasn't expired.
If the access token is short-lived, it exchanges it for a long-lived access token.
Stores the access token in the PHP session.
Redirects the user to a dashboard page (your-dashboard-page.php).
Note that you need to replace 'YOUR_APP_ID', 'YOUR_APP_SECRET', and 'your-dashboard-page.php' with your actual app ID, app secret, 
and the URL of your dashboard script.

Remember that in a production environment, you would want to handle errors more gracefully, potentially showing user-friendly error messages
and logging errors for your records.

Also, make sure that your Facebook SDK is correctly required at the start of your script (adjust the path to where your vendor/autoload.php is located). 
You should set up the SDK via Composer to manage dependencies and autoload the necessary classes.

Once the access token is in the session, you can use it in your other scripts to make authenticated API calls on behalf of the user. 
You can find more details in the official Facebook PHP SDK documentation.


When an error occurs, a message is logged to a file (using file_put_contents) for future reference.
The user is redirected to a generic error page (error-page.php) where a user-friendly message can be displayed.

You would create an error-page.php file with a message informing the user that an error occurred and offering potential next steps, 
such as retrying the operation or contacting support.


*/


require_once __DIR__ . '/vendor/autoload.php';


use Facebook\Facebook;

session_start();

$fb = new Facebook([
  'app_id' => 'APP_ID',
  'app_secret' => 'SECRET',
  'default_graph_version' => 'v17.0',
]);

$helper = $fb->getRedirectLoginHelper();



$helper = $fb->getRedirectLoginHelper();

//try {
  $accessToken = $helper->getAccessToken();
  if (! isset($accessToken)) {
	//file_put_contents('./logs/error_log.txt', 'Graph returned an error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
	file_put_contents('./logs/error_log.txt', 'Exception | No access token', FILE_APPEND);
	exit();
  }
  if (!$accessToken) {
    if ($helper->getError()) {
        //header('HTTP/1.0 401 Unauthorized');
        $msg = "Error: " . $helper->getError() . "\n";
        $msg = $msg."Error Code: " . $helper->getErrorCode() . "\n";
        $msg = $msg."Error Reason: " . $helper->getErrorReason() . "\n";
        $msg = $msg."Error Description: " . $helper->getErrorDescription() . "\n";
		file_put_contents('./logs/error_log.txt', $msg, FILE_APPEND);
    } else {
        //header('HTTP/1.0 400 Bad Request');
        file_put_contents('./logs/error_log.txt', 'Bad request', FILE_APPEND);
    }
    exit;
  }

  
  // The OAuth 2.0 client handler helps us manage access tokens
  $oAuth2Client = $fb->getOAuth2Client();
  
  // Get the access token metadata from /debug_token
  $tokenMetadata = $oAuth2Client->debugToken($accessToken);
  
  // Validation (these will throw FacebookSDKException's when they fail)
  $tokenMetadata->validateAppId('254341613637000'); 
  // If you know the user ID this access token belongs to, you can validate it here
  //$tokenMetadata->validateUserId('123');
  $tokenMetadata->validateExpiration(); 
  
  if (! $accessToken->isLongLived()) {
    // Exchanges a short-lived access token for a long-lived one
    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
  }
/*} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  file_put_contents('./logs/error_log.txt', 'Graph returned an error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  file_put_contents('./logs/error_log.txt', 'Facebook SDK returned an error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
  exit;
} catch(Exception $e) {
  // Any other error
  file_put_contents('./logs/error_log.txt', 'Facebook SDK other error: ', FILE_APPEND);
  exit;
}*/

// Log in
$_SESSION['fb_access_token'] = (string) $accessToken;

// User is logged in with a long-lived access token.
// You can redirect them to another page.
header('Location: dashboard.php');
?>
