<?php
// needto remove this- security issue...
// eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZSI6WyJhbGxlZ3JvOmFwaTpzYWxlOnNldHRpbmdzOnJlYWQiLCJhbGxlZ3JvOmFwaTpzYWxlOm9mZmVyczpyZWFkIl0sImFsbGVncm9fYXBpIjp0cnVlLCJleHAiOjE2NzIzMzM0NjEsImp0aSI6IjM0Yzg1YmM0LWEwNGYtNDcyZC1hYzc2LWJlZDZlYzc4ZGE4NCIsImNsaWVudF9pZCI6ImFmNDM3NGU5Mzc5NDQ0NjViMGQzNGRhMThjMjIyOTE3In0.VBV8TfB_ZX3u5-YZhRW0A0ipoGeSDAgmPaTEbXeJMYfaDQOPG6WE7NiEp_lT6QTYGh7bxlFrZWNSVjte3jf3wuoBCpqupyztt8F3emYtfZiD-qIMK1y4Th8D4G-LjM67NYfDgSQuW1qQ67nss07abQI4oHHSDNGJGfpyV3o00kUHzdLgFHiWC9nVzRs_c5RaowTgiczqn_OblLUZk95rU5utuBsJc908CHRpzWrR-lQ9C5vrHSdj7e9mbZdSwbGH5kLTwtO4FxQHtB__jnSmuD1TsyZakYuGZqLew82zzzq-F4QD8HyFR_k1GmhKrZ7IxnCZPZghHYJCmKFe3c8Amfpl6026juDgBxkiYyXdRZFvPbxEsPWT-y4k6E0UD-OnZyX-husufY56kjXjPJyliCazGB4IJuE98L209_OAf_k-BBNDtltVZNQII5HAXdiqrW8Y4Qlz0X1yIaERiSXbMi_IdUZXIgieOEbcnPc-aVs0s69sHixPvc5fJPDDsTTxT9dPilS7_xjxEQUdCoooWFGyM7O41BgqiH0Qk7GAm-BNBmpHM-MbFsUeWzeHrSIob6k2guKk6SSBh9MIDEhccqoaeQzUM5OQbk0B0KqadzIrGv-UErAYx_LCz1aDXFcZWMrDNQNGBN_MjfAtjJnvFf3ZM54Zmjr1poL58NkgrOQ

define('CLIENT_ID', 'af4374e937944465b0d34da18c222917'); // enter the Client_ID of the application
define('CLIENT_SECRET', 'JJbbFTXcfTUhlktKLqDXb1trhETrHGS8ueTsxNwifrm7M24SEK4iCnuXQBtckVOR'); // enter the Client_Secret of the application

function getCurl($headers, $url, $content = null) {
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true
    ));
    if ($content !== null) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    }
    return $ch;
}

function getAccessToken() {
    $authorization = base64_encode(CLIENT_ID.':'.CLIENT_SECRET);
    $headers = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
    $content = "grant_type=client_credentials";
    $url = "https://allegro.pl.allegrosandbox.pl/auth/oauth/token";
    $ch = getCurl($headers, $url, $content);
    $tokenResult = curl_exec($ch);
    $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($tokenResult === false || $resultCode !== 200) {
        exit ("Something went wrong");
    }
    return json_decode($tokenResult)->access_token;
}

function main()
{
    echo "access_token = ", getAccessToken();
}

main();
