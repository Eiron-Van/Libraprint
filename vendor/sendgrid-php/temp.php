<?php
require 'vendor/autoload.php'; // If you're using Composer (recommended)
// comment out the above line if not using Composer
// require("./sendgrid-php.php"); 
// If not using Composer, uncomment the above line


$apiKey = getenv('SENDGRID_API_KEY');
$sg = new \SendGrid($apiKey);

$response = $sg->client->senders()->get();
print $response->statusCode() . "\n";
print $response->body() . "\n";
print_r($response->headers());

$request_body = json_decode('{
    "categories": [
      "spring line"
    ],
    "custom_unsubscribe_url": "",
    "html_content": "<html><head><title></title></head><body><div style=\"width:100%\">Check out our spring line!</div></body></html>",
    "plain_content": "Check out our spring line!",
    "sender_id": 148501,
    "subject": "New Products for Spring!",
    "suppression_group_id": 42,
    "title": "March Newsletter"
  }');
  $response = $sg->client->campaigns()->post($request_body);
  print $response->statusCode() . "\n";
  print $response->body() . "\n";
  print_r($response->headers());

