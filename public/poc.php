<?php

// Tableau API endpoint URL for creating a personal access token
$apiUrl = "https://prod-apnortheast-a.online.tableau.com/api/3.21/auth/signin";

// JSON payload
$jsonPayload = json_encode([
    "credentials" => [
        "personalAccessTokenName" => "poc",
        "personalAccessTokenSecret" => "blSvagtWSxClIO/qeVRKrw==:O7IPFXRckilttDSbeddCxfSi2UdltPzT",
        "site" => [
            "contentUrl" => "reicpoc"
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
<?php

// Tableau API endpoint URL
//$apiUrl = "https://<server>/api/<version>/sites/reicpoc/projects";
$apiUrl = "https://prod-apnortheast-a.online.tableau.com/api/3.4/sites/reicpoc/projects";

// Personal Access Token
$pat = "7kgyqujPT3q9C9HIHa3sNA|N03ZXWFQ3FyTHrhAZl3jrrbUk6ThEngg|63a7b254-a3ed-4e93-aa40-cf3ed3fb1964";

// cURL initialization
$ch = curl_init();

// cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json",
    "X-Tableau-Auth: Bearer $pat"
));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
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
