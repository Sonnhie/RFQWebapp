<?php 
    namespace WebSocket;
    require_once __DIR__ . '/../../vendor/autoload.php';

    use WebSocket\Client;

   class WebSocketNotifier {
        private $wsUrl;

        public function __construct($wsUrl = "ws://192.168.101.49:8080") {
            $this->wsUrl = $wsUrl;
        }

        public function send($event, $data, $target = null){
            try{
                $client = new Client($this->wsUrl);
                $payload = [
                    'event' => $event,
                    'data' => $data,
                    'target' => $target
                ];

                if ($target !== null) {
                    $payload['target'] = $target;
                }

                $client->send(json_encode($payload));
                $client->close();
            }catch(Exception $e){
                echo "WebSocket error: " . $e->getMessage() . "\n";
            }
        }
   }

?>