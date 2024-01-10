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
//print_r($data);

?>


 <!-- <script type='module' src='https://prod-apnortheast-a.online.tableau.com/javascripts/api/tableau.embedding.3.latest.min.js'></script>
<tableau-viz id='tableau-viz' src='https://prod-apnortheast-a.online.tableau.com/t/reicpoc/views/poc/EmailPerformancebyCampaign'
width='1536' height='786' hide-tabs toolbar='bottom' token='eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlzcyI6ImY3YTI2N2MxLWE2MTYtNGYwZS1hMWJkLTM2MTg4NjNkZGY2YSIsImtpZCI6IjRhYzJlZjU4LTNjYTMtNDNiZC04MWE2LTQ1MTgzNThjYWVjNiJ9.eyJzdWIiOiJtYW5vbjIwMjlAZ21haWwuY29tIiwiYXVkIjoidGFibGVhdSIsIm5iZiI6MTcwNDkwOTY3MSwianRpIjoiMTcwNDkwOTc3MDUwMSIsImlzcyI6ImY3YTI2N2MxLWE2MTYtNGYwZS1hMWJkLTM2MTg4NjNkZGY2YSIsInNjcCI6WyJ0YWJsZWF1OnZpZXdzOmVtYmVkIl0sImV4cCI6MTcwNDkwOTg3MX0.gNCcLGvmGdiKQcjTE-_PaezvCf9tGP5JOe0d5DzTkXo'></tableau-viz>
<!DOCTYPE html>
<html>
<head>
    <title>Tableau Embed Example</title>
    <script type='module' src='https://prod-apnortheast-a.online.tableau.com/javascripts/api/tableau.embedding.3.latest.min.js'></script>
</head>
<body>

<script>
    // Replace with your actual Personal Access Token (PAT)
    var pat = "blSvagtWSxClIO/qeVRKrw==:O7IPFXRckilttDSbeddCxfSi2UdltPzT";

    // Tableau view URL with embedded PAT
    var viewUrl = "https://prod-apnortheast-a.online.tableau.com/t/reicpoc/views/poc/EmailPerformancebyCampaign?:showAppBanner=false&:embed=yes&:toolbar=top&:tabs=no&:showVizHome=no&:accessToken=" + pat;

    // Create Tableau Viz
    var containerDiv = document.getElementById("tableauViz");
    var options = {
        hideTabs: true,
        onFirstInteractive: function () {
            console.log("Tableau viz has loaded.");
        }
    };
    var viz = new tableau.Viz(containerDiv, viewUrl, options);
</script>

<div id="tableauViz"></div>

</body>
</html> -->
