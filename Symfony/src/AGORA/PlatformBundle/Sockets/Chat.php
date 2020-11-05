<?php
// AGORA\PlatformBundle\Sockets\Chat.php;

namespace AGORA\PlatformBundle\Sockets;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;//sockets clients
    protected $histTchat;//Historique du tchat

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->histTchat = array();
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);
        if ($data->type == "connect") {
            $ret = array("type" => "tchat", "tchat" => $this->histTchat);
            $ret = json_encode($ret);
            $from->send($ret);
        } else if ($data->type == "message") {
            $countMsg = count($this->histTchat);
            //On sauvegarde le message dans l'historique, on n'en garde que 20 max
            if ($countMsg == 20) {
                array_shift($this->histTchat);
                --$countMsg;
            }
            $this->histTchat[$countMsg] = $msg;
            //On envoie le message à tout le monde
            foreach ($this->clients as $client) {
                if ($client != $from) {
                    $client->send($msg);
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}