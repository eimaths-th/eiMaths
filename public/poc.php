<?php

// Tableau API endpoint URL for creating a personal access token
$apiUrl = "https://prod-apnortheast-a.online.tableau.com/api/3.21/auth/signin";

// JSON payload
$jsonPayload = json_encode([
    "credentials" => [
        "personalAccessTokenName" => "poc",
        "personalAccessTokenSecret" => "blSvagtWSxClIO/qeVRKrw==:O7IPFXRckilttDSbeddCxfSi2UdltPzT",
        "site" => [
            "contentUrl" => "EmailPerformancebyCampaign"
        ]
    ]
]);

// cURL initialization
$ch = curl_init();

// cURL options for a POST request
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Execute cURL session and get the response
echo $response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

// Close cURL session
curl_close($ch);

// Decode and print the API response (assuming it's in JSON format)
$data = json_decode($response, true);
print_r($data);

?>

<script type='module' src='https://prod-apnortheast-a.online.tableau.com/javascripts/api/tableau.embedding.3.latest.min.js'></script>
<tableau-viz id='tableau-viz' src='https://prod-apnortheast-a.online.tableau.com/t/reicpoc/views/poc/EmailPerformancebyCampaign'
width='1536' height='786' hide-tabs toolbar='bottom'></tableau-viz>
