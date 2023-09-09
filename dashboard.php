<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="./css/common.css">
<link rel="stylesheet" href="./css/lightandairy.css">
<!--<link rel="stylesheet" href="nightmode.css">
<link rel="stylesheet" href="modernfancy.css">-->
<script src="./js/script.js"></script>
<title>Absilone / Ad Campaigns Dashboard</title>
</head>

<body>

<div style="margin: 20px;">
<div><center><h2>Suivi des campagnes sur les 7 derniers jours</h2></center><div>

<div class="filter-section">
    <input type="text" id="keyword-input" placeholder="Enter keyword">
    <button id="filter-button">Filtrer</button>
</div>



<?php

/*
// --- 
// doc 
// ---
objectives :

PAGE_LIKES
VIDEO_VIEWS 
CONVERSIONS 
OUTCOME_TRAFFIC 
OUTCOME_AWARENESS 
OUTCOME_ENGAGEMENT
OUTCOME_SALES 

*/


// -------------------------------
// Setup, Parametres, Constantes
// -------------------------------

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);

require_once __DIR__ . '/vendor/autoload.php';

$LIMIT = 50;
$filterAccounts=1;
$vide="&nbsp;";

//$TIMEFRAME="lifetime";
$TIMEFRAME="this_year";
//$TIMEFRAME="last_7d"


use Facebook\Facebook;

session_start();

if (!isset($_SESSION['fb_access_token'])) {
header('Location: index.php');
exit;
}

$fb = new Facebook([
  'app_id' => 'APP_ID',
  'app_secret' => 'SECRET',
  'default_graph_version' => 'v17.0',
]);





// -------------------------------
// Encapsulation des fonctions d'appel API Facebook 
// but : rajouter une temporisation dès que l'on atteint un quota d'appels API
// et rejouer automatiquement
// -------------------------------

function fb_get_with_retry($fb, $endpoint, $retryLimit = 5, $sleepDuration = 60) {
    $retryCount = 0;

    while ($retryCount < $retryLimit) {
        try {
            // Attempt to make the API call
            $response = $fb->get($endpoint);
            
            // If the API call is successful, return the response
            return $response;
        } catch (Exception $e) {
            // If a rate limit error occurs, increment the retry counter and sleep for a specified duration
            if (strpos($e->getMessage(), 'User request limit reached') !== false) {
                $retryCount++;
                sleep($sleepDuration);
            } else {
                // If it's an error other than rate limiting, throw the exception
                throw $e;
            }
        }
    }

    // If the retry limit is reached, throw an exception
    throw new Exception('Retry limit reached');
}

/*
// Usage example
try {
    $response = fb_get_with_retry($fb, '/'.$adAccount['id'].'/campaigns?filtering=[...]');
    // Process the response
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
*/

// -------------------------------







// -------------------------------
// MAIN 
// -------------------------------


try {
	
	$fb->setDefaultAccessToken($_SESSION['fb_access_token']);

	
	// appel API Meta FB
	//$response = $fb->get('/me/adaccounts?limit='.$LIMIT.'&fields=name,id,account_id');
	try {
		$response = fb_get_with_retry($fb, '/me/adaccounts?limit='.$LIMIT.'&fields=name,id,account_id');
	} catch (Exception $e) {
		echo "An error occurred: " . $e->getMessage();
	}

	$adAccounts = $response->getDecodedBody()['data'];
	
	if ($filterAccounts == 1) {
		$filteredAdAccounts = array_filter($adAccounts, function($adAccount) {
			$name = $adAccount['name'];
			$keywords = ['Absilone', 'VL P','Murgier','Tiwiza','Moonlight','Dessolas'];  // replace with your keywords
			foreach ($keywords as $keyword) {
				if (strpos($name, $keyword) !== false) {
					return true;
				}
			}
			return false;
		});
	} else {
		$filteredAdAccounts=$adAccounts;
	}

	echo '<h2>Suivi des campagnes</h2>';
	echo '<div style="margin: 20px;">';


	if (!empty($filteredAdAccounts)) {

		foreach ($filteredAdAccounts as $adAccount) {
			
			echo '<div class="adaccount"><h3><button class="toggle-button">▼</button>Compte pub: ' . $adAccount['name'] . ' ('. $adAccount['id'] .')</h3>';			
			
			
			// appel API Meta FB (Campaigns)
			//$response = $fb->get('/'.$adAccount['id'].'/campaigns?filtering=[{"field":"effective_status","operator":"IN","value":["ACTIVE","WITH_ISSUES"]}]&fields=id,name,daily_budget,start_time');
			//$response = $fb->get('/'.$adAccount['id'].'/campaigns?fields=id,name,daily_budget,start_time');
			try {
				$response = fb_get_with_retry($fb, '/'.$adAccount['id'].'/campaigns?filtering=[{"field":"effective_status","operator":"IN","value":["ACTIVE","WITH_ISSUES"]}]&fields=id,name,daily_budget,start_time,lifetime_budget,objective,budget_remaining,spend_cap');
			} catch (Exception $e) {
				echo "An error occurred: " . $e->getMessage();
			}
			
			
			$campaigns = $response->getDecodedBody()['data'];
			

			if (!empty($campaigns)) {

				foreach ($campaigns as $campaign) {
					
					/*echo '<table border="1" style="width: 100%; margin-bottom: 20px;">';
					echo '<tr><th>name</th><th>id</th><th>start time</th><th>end time</th><th>max budget</th><th>daily budget</th><th>spent</th><th>resultat</th><th>cout par resultat</th></tr>';
					
					echo "<tr class='campagne'><td>".$campaign['name']."</td><td>".$campaign['id']."</td><td>".$campaign['start_time']."</td><td>&nbsp;</td><td>".$campaign['lifetime_budget']."</td><td>".$campaign['daily_budget']."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
					
					echo "</table>";*/
					
					echo "<div class='adcampaign-section'>";
					echo "<h4 class='adcampaign'><button class='toggle-button'>▼</button>".$campaign['name']." | id : ". $campaign['id']." | objectif : ".$campaign['objective']." | max budget :".$campaign['lifetime_budget']."</h4>";
				
					// Get Ad Sets of the campaign										
					//$response = $fb->get('/' . $campaign['id'] . '/adsets?fields=name,status,daily_budget,start_time,end_time,lifetime_budget');
					
					$response = $fb->get('/' . $campaign['id'] . '/adsets?filtering=[{"field":"effective_status","operator":"IN","value":["ACTIVE","WITH_ISSUES"]}]&fields=id,name,effective_status,daily_budget,start_time,end_time,lifetime_budget');

					
					/*
					// appel API Meta FB (AdSets)
					try {
						$response = fb_get_with_retry($fb, '/' . $campaign['id'] . '/adsets?filtering=[{"field":"effective_status","operator":"IN","value":["ACTIVE","WITH_ISSUES"]}]				&fields=id,name,status,daily_budget,start_time,end_time,lifetime_budget');
					} catch (Exception $e) {
						echo "An error occurred: " . $e->getMessage();
					}*/


					
					$adSets = $response->getDecodedBody()['data'];					
					
					echo '<table border="1" class="adsets-table" style="width: 100%; margin-bottom: 20px;">';
					
					
					if (!empty($adSets)) {
						
						
						echo '<tr>
						<th>name</th>
						<th>id</th>
						<th>status</th>
						<th>start time</th>
						<th>end time</th>
						<th>max budget</th>
						<th>daily budget</th>
						<th>spent</th>
						<th>resultat</th>
						<th>cout par resultat</th></tr>';

						foreach ($adSets as $adSet) {
							
							
														
							$depense=-999;
							$nbResultats=-999;
							$coutParResultat=-999;
							$nbclicks=-999;
							$coutParClic=-999;
							$conversions=-999;
							$coutParConversion=-999;
							$page_likes=-999;
							$cost_per_page_like=-999;
							
							
							
							// Get insights for each adset
							try {
								
								$response = $fb->get('/' . $adSet['id'] . '/insights?fields=spend,cost_per_conversion,conversions,cpc,cpm,ctr,actions,clicks&date_preset='.$TIMEFRAME);
							
								//$response = $fb->get('/' . $adSet['id'] . '/insights?fields=spend,cost_per_conversion,conversions,cpc,cpm,ctr&since=2020-01-01&until=2023-07-31');


								// appel API Meta FB (AdSets insights)
								//try {
								//	$response = fb_get_with_retry($fb, '/' . $adSet['id'] . '/insights?fields=spend,cost_per_conversion,conversions,cpc,cpm,ctr&date_preset=this_year');
								//} catch (Exception $e) {
									//echo "An error occurred: " . $e->getMessage();
								//}
					
					
					
								$insights = $response->getDecodedBody()['data'];
								
								
								if (!empty($insights)) {
									foreach ($insights as $insight) {
										$depense=$insight['spend']." €";
										$nbclicks=$insight['clicks'];
										$coutParClic=(isset($insight['cpc']) ? $insight['cpc'] : "N/A");
										$conversions=$insight['conversions'];
										$coutParConversion=$insight['cost_per_conversion'];
													
							
										$page_likes = 0;
										if(isset($insight['actions'])){
											foreach ($insight['actions'] as $action) {
												if ($action['action_type'] == 'like') {
													$page_likes = $action['value'];
												}
											}
										}
										$cost_per_page_like = $page_likes != 0 ? $insight['spend'] / $page_likes : "N/A";
										
					
									}
								 
								}
							} catch (Exception $e) {
								echo "Error fetching insights: " . $e->getMessage() . "<br>";
							}
							
					
							$objectif=$campaign['objective'];
							if ($objectif == "PAGE_LIKES") {
								$nbResultats=$page_likes;
								$coutParResultat=$cost_per_page_like;
								
								// alerte si cout trop élevé
								// si nom adset contient pays3
									// si cout par resultat > XXX alors classe = rouge
									// si cout par resulat < YYY alors class = vert
								
								
							} else if ($objectif == "CONVERSIONS") {
								$nbResultats=$conversions;
								$coutParResultat=$coutParConversion;
							} else if ($objectif == "OUTCOME_TRAFFIC") {
								$nbResultats=$nbclicks;
								$coutParResultat=$coutParClic;
							} else {
								$nbResultats=$objectif;
								$coutParResultat=$objectif;
							}
							
							$time = isset($adSet['start_time']) ? $adSet['start_time'] : null;
							if ($time) {
								$dateDebut = DateTime::createFromFormat(DateTime::ISO8601, $adSet['start_time']);
								$dateDebut=$dateDebut->format('Y-d-m');
							} else {
								$dateDebut=$vide;
							}
							
							$time = isset($adSet['end_time']) ? $adSet['end_time'] : null;
							if ($time) {
								$dateFin = DateTime::createFromFormat(DateTime::ISO8601, $adSet['end_time']);
								$dateFin=$dateFin->format('Y-d-m');
							} else {
								$dateFin=$vide;
							}
							
							$daily_budget = isset($adSet['daily_budget']) ? $adSet['daily_budget'] : null;
							if ($daily_budget !== null) {
								$daily_budget_in_currency = $daily_budget / 100;
								$daily_budget_in_currency=number_format($daily_budget_in_currency, 2) . " €"; 
							}

							$lifetime_budget = isset($adSet['lifetime_budget']) ? $adSet['lifetime_budget'] : null;
							if ($lifetime_budget !== null) {
								$lifetime_budget_in_currency = $lifetime_budget / 100;
								$lifetime_budget_in_currency=number_format($lifetime_budget_in_currency, 2) . " €"; 
							}


							$shortAdsetName=substr($adSet['name'], 0, 35); // pas utilisé 
							
							echo "<tr class='campagne'>
							<td>".$adSet['name']."</td>
							<td>".$adSet['id']."</td>
							<td>".$adSet['effective_status']."</td>
							<td>".$dateDebut."</td>
							<td>".$dateFin."</td>
							<td>".$lifetime_budget_in_currency."</td>
							<td>".$daily_budget_in_currency."</td>
							<td>".$depense."</td>
							<td>".$nbResultats."</td>
							<td>".$coutParResultat."</td>
							</tr>";
							
							
						}
						
						echo '</table>';
					
						// calcul budget total depuis lifetime
						$maxBudget = isset($campaign['spend_cap']) ? $campaign['spend_cap'] : '<span style=\'background-color:red;color:white\'>Aucun budget max défini sur la campagne !!</span>';
						
						/*$alreadySpent = 80;
						$reste = 20;*/
						
						echo "<div class='budgetcampaign'>
						Max budget défini = ".$maxBudget." €"; 
						// echo "| Déjà dépensé = ".$alreadySpent." € | reste = ".$reste." €";
						echo "</div>";

					} else {
						
						echo '</table>';
						echo "<div class='budgetcampaign'>Aucun ens. de pub actif à afficher.</div>"; 
						
					} // end if !adsets

					
					
					echo "</div>"; // end du div section campagne
					
						
				}  // end for each campaign
			
				
			
			} else {
				echo "<div class='adcampaign-section'>Aucune campagne active à afficher.</div>"; // end if !campaigns
			}
			
			echo "</div>"; // end du div section compte pub
			
		} // end for each accounts

		


	} else {
		echo "ERREUR : Pas de comptes pub à afficher"; 
	} // end if !accounts 
	
	echo '</div>';
	
	
// end try
} catch (Facebook\Exceptions\FacebookResponseException $e) {
	echo 'Graph returned an error: ' . $e->getMessage();
	exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
	exit;
}



?>



