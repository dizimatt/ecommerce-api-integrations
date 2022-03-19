<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Logger;
use Monolog\Handler\StreamHandler;

class IndexController extends Controller
{
    public function handle(Request $request, string $topic)
    {
        $logger = new Logger('App_Http_Controllers_Webhook_IndexController_handle');
        $loggerFilename = storage_path(
            "logs/App_Http_Controllers_Webhook_IndexController_handle.log"
        );
        $logger->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);


        $logger2 = new Logger('App_Http_Controllers_Webhook_IndexController_handle');
        $loggerFilename = storage_path(
            "logs/App_Http_Controllers_Webhook_Return_Capture.log"
        );
        $logger2->pushHandler(new StreamHandler($loggerFilename), Logger::INFO);

        $postData = $request->post();

        $topicEvent = str_replace(['/', '_'], ' ', $topic);
        $topicEvent = ucwords($topicEvent);
        $topicEvent = str_replace(' ', '', $topicEvent);

        $eventClass = "\App\Events\Shopify\\{$topicEvent}";

        if (strstr($topicEvent , 'refunds') !== false)
        {
            $logger2->log("Payload", $postData);
            $logger2->log(print_r( $postData, true));
        }

        \Event::dispatch(new $eventClass($postData));

        $debugObj = [
            'store' => store()->toArray(),
            'topic' => $topic,
            'event' => $eventClass,
            'payload' => $postData
        ];
        $logger->debug('Webhook Controller Hit', $debugObj);

        return;
    }
}