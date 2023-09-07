<?php
require_once 'vendor/autoload.php';

use Facebook\Facebook;

session_start();

if (!isset($_SESSION['fb_access_token'])) {
    header('Location: index.php');
    exit;
}

$fb = new Facebook([
  'app_id' => 'YOUR_APP_ID',
  'app_secret' => 'YOUR_APP_SECRET',
  'default_graph_version' => 'v12.0',
]);

try {
    $fb->setDefaultAccessToken($_SESSION['fb_access_token']);

    $response = $fb->get('/me/adaccounts');
    $adAccounts = $response->getDecodedBody()['data'];

    echo '<div style="margin: 20px;">';
    
    foreach ($adAccounts as $adAccount) {
        echo '<h2>Ad Account ID: ' . $adAccount['id'] . '</h2>';
        
        $response = $fb->get('/' . $adAccount['id'] . '/campaigns');
        $campaigns = $response->getDecodedBody()['data'];

        foreach ($campaigns as $campaign) {
            $response = $fb->get('/' . $campaign['id'], ['fields' => 'name,status,objective,start_time,end_time,daily_budget,insights.limit(1){spend,cost_per_result,results}']);
            $campaignDetails = $response->getDecodedBody();

            echo '<table border="1" style="width: 100%; margin-bottom: 20px;">';
            echo '<tr><th>Name</th><th>Status</th><th>Objective</th><th>Start Time</th><th>End Time</th><th>Daily Budget</th><th>Spend</th><th>Cost per Result</th><th>Results</th></tr>';
            echo '<tr style="background-color: #f0f0f0;"><td>' . $campaignDetails['name'] . '</td><td>' . $campaignDetails['status'] . '</td><td>' . $campaignDetails['objective'] . '</td><td>' . $campaignDetails['start_time'] . '</td><td>' . $campaignDetails['end_time'] . '</td><td>' . (isset($campaignDetails['daily_budget']) ? $campaignDetails['daily_budget'] : 'Not Set') . '</td><td>' . $campaignDetails['insights']['data'][0]['spend'] . '</td><td>' . $campaignDetails['insights']['data'][0]['cost_per_result'] . '</td><td>' . $campaignDetails['insights']['data'][0]['results'] . '</td></tr>';
            
            // Get Ad Sets of the campaign
            $response = $fb->get('/' . $campaign['id'] . '/adsets', ['fields' => 'name,status,daily_budget,start_time,end_time,insights.limit(1){spend,cost_per_result,results}']);
            $adSets = $response->getDecodedBody()['data'];

            foreach ($adSets as $adSet) {
                $insights = isset($adSet['insights']) ? $adSet['insights']['data'][0] : null;

                echo '<tr><td style="padding-left: 20px;">' . $adSet['name'] . '</td><td>' . $adSet['status'] . '</td><td> - </td><td>' . $adSet['start_time'] . '</td><td>' . $adSet['end_time'] . '</td><td>' . (isset($adSet['daily_budget']) ? $adSet['daily_budget'] : 'Not Set') . '</td><td>' . ($insights ? $insights['spend'] : 'Not Available') . '</td><td>' . ($insights && isset($insights['cost_per_result']) ? $insights['cost_per_result'] : 'Not Available') . '</td><td>' . ($insights && isset($insights['results']) ? $insights['results'] : 'Not Available') . '</td></tr>';
            }
            
            echo '</table>';
        }
    }

    echo '</div>';
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
?>
