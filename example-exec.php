<?php

$curlError = fopen('php://temp', 'rw+');
$options[CURLOPT_STDERR] = $curlError;

$ch = curl_init();
curl_setopt_array( $ch, $options );
$responseText = urldecode( curl_exec( $ch ) );

//display curl http conversation
rewind( $curlError );
$errorDump = stream_get_contents( $curlError );
fclose( $curlError );
$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
curl_close( $ch );

echo PHP_EOL;
echo PHP_EOL . '//------------------------------------------------------------------------------' . PHP_EOL;
echo 'IPP CAD Reply:' . PHP_EOL . PHP_EOL;
echo $responseText;

echo PHP_EOL;
echo PHP_EOL . '//------------------------------------------------------------------------------' . PHP_EOL;
echo 'Full Connection Conversation: ' . PHP_EOL . PHP_EOL;
echo $errorDump;
echo PHP_EOL;

