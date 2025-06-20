require dirname(__DIR__) . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class NotificationServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "WebSocket server started.\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $conn->section = null;
        $conn->role = null;
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if ($data['event'] === 'auth') {
            $from->section = $data['section'];
            $from->role = $data['role'];
            return;
        }

        // Example: handle server-originated push messages
        if ($data['event'] === 'broadcast') {
            foreach ($this->clients as $client) {
                if ($client->section === $data['target'] || $client->role === $data['target']) {
                    $client->send(json_encode($data));
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = Ratchet\Server\IoServer::factory(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new NotificationServer()
        )
    ),
    8080
);

$server->run();
