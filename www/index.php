<?php
$env = parse_ini_file('../local.env');
if(!$env) {
    error_log('No local.env file found');
    die('No local.env file found');
}

$accessTokenUrl = "https://id.twitch.tv/oauth2/token";
$clientId = $env["TWITCH_CLIENT_ID"];
$clientSecret = $env["TWITCH_CLIENT_SECRET"];
$grantType = "client_credentials";

$accessTokenParams = array(
    "client_id" => $clientId,
    "client_secret" => $clientSecret,
    "grant_type" => $grantType
);

$accessTokenOptions = array(
    "http" => array(
        "header" => "Content-type: application/x-www-form-urlencoded\r\n",
        "method" => "POST",
        "content" => http_build_query($accessTokenParams)
    )
);

$accessTokenContext = stream_context_create($accessTokenOptions);
$accessTokenResponse = file_get_contents($accessTokenUrl, false, $accessTokenContext);

if ($accessTokenResponse !== false) {
    $accessTokenData = json_decode($accessTokenResponse, true);
    $accessToken = $accessTokenData["access_token"];
    $url = 'https://api.twitch.tv/helix/games?name='.$env['TWITCH_GAME'];
    $options = array(
        "http" => array(
            "header"  => "Client-ID: " . $clientId . "\r\n" .
            "Authorization: Bearer " . $accessToken . "\r\n",
            "method"  => "GET"
        )
    );
    $context  = stream_context_create($options);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Client-ID: " . $clientId,
        "Authorization: Bearer " . $accessToken
    ));
    $response = curl_exec($ch);
    curl_close($ch);
    if($response) {
        $data = json_decode($response, true);
        $gameId = $data["data"][0]["id"];
        $url = 'https://api.twitch.tv/helix/streams?&game_id='. $gameId;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Client-ID: " . $clientId,
            "Authorization: Bearer " . $accessToken
        ));
        $response = curl_exec($ch);
        $json = json_decode(curl_exec($ch));
        curl_close($ch);
        $postedVids = 0;
        foreach ($json->data as $streamNum => $stream) {

            if ($postedVids == 0) {
                $webhookUrl = $env["WEBHOOK_URL"];
                $thumbUrl = str_replace('%{height}', 200, str_replace('%{width}', 400, $stream->thumbnail_url));
                $thumbUrl = str_replace('{height}', 200, str_replace('{width}', 400, $stream->thumbnail_url));
                $streamUrl = (isset($stream->url) ? $stream->url : 'https://www.twitch.tv/' . $stream->user_login);
                $message = '@StreamPings **' . $stream->user_name . '** has started streaming '.$stream->game_name.' on Twitch, [join them](' . $streamUrl . ')!';

                $data = array(
                    "username" => $env["DISCORD_BOT_NAME"],
                    "avatar_url" => (isset($env['DISCORD_BOT_AVATAR_URL'])? $env['DISCORD_BOT_AVATAR_URL']:''),
                    "content" => $message,
                    "embeds" => [
                        [
                            "title" => $stream->title,
                            "url" => $streamUrl,
                            "image" => ["url" => $thumbUrl]
                        ]
                    ],
                    "components" => [
                        [
                            "type" => 1,
                            "components" => [
                                [
                                    "type" => 2,
                                    "label" => "Watch",
                                    "style" => 5,
                                    "url" => $streamUrl,
                                ]
                            ]
                        ]
                    ]
                );

                $options = array(
                    "http" => array(
                        "header"  => "Content-type: application/json",
                        "method"  => "POST",
                        "content" => json_encode($data)
                    )
                );
                echo json_encode($data);
                $context  = stream_context_create($options);
                $result = file_get_contents($webhookUrl, false, $context);

                if ($result === false) {
                    // Handle error
                } else {
                    print_r($result);
                    $postedVids++;
                    // Message sent successfully
                }
            }
        }
    }    
} else {
    // Handle error
    error_log('Handle error');
    die('Handle error');
}



// $data = array(
//     "content" => $message
// );

// $options = array(
//     "http" => array(
//         "header"  => "Content-type: application/json",
//         "method"  => "POST",
//         "content" => json_encode($data)
//     )
// );

// $context  = stream_context_create($options);
// $result = file_get_contents($webhookUrl, false, $context);

// if ($result === false) {
//     // Handle error
// } else {
//     // Message sent successfully
// }