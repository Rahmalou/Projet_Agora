<?php


namespace AGORA\Game\SplendorBundle\Socket;


use AGORA\Game\SplendorBundle\Service\SplendorService;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class SplendorSocket implements MessageComponentInterface {

    protected $service;
    protected $clients;


    public function __construct(SplendorService $srvc) {
        $this->service = $srvc;
        $this->clients = [];
    }

    /**
     * When a new connection is opened it will be passed to this method
     * @param ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn) {
        if (!in_array($conn, $this->clients)) {
            array_push($this->clients, $conn);
        }
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn) {
        $k = array_search($conn, $this->clients);
        array_splice($this->clients, $k, 1);
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param ConnectionInterface $conn
     * @param \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    /**
     * Triggered when a client sends data through the socket
     * @param \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param string $msg The message received
     * @throws \Exception
     */
    function onMessage(ConnectionInterface $from, $msg) {
        $content = json_decode($msg);
        echo "test : " . $msg;
        switch ($content->type) {
            case "takeTokens":
                $tokens = $this->service->getTokens(intval($content->gameId), intval($content->userId)
                    , explode(",", $content->tokens));
                if ($tokens !== null) {
                    $tokensPlayer = $tokens[0]; $tokensGame = $tokens[1];
                    $data = array("type" => $content->type, "userId" => $content->userId, "tokensPlayer" => $tokensPlayer
                    , "tokensGame" => $tokensGame);
                    $data = json_encode($data);
                    foreach ($this->clients as $client) {
                        $client->send($data);
                    }
                }
                break;
            case "reserveCard":
                $tab = $this->service->reserveCard(intval($content->gameId), intval($content->userId), intval($content->cardId));
                if ($tab !== null) {
                    $data = array("type" => $content->type, "userId" => $content->userId, "oldCard" => $content->cardId
                        ,"newCard" => $tab[0], "joker" => $tab[1]);
                    $data = json_encode($data);
                    foreach ($this->clients as $client) {
                        $client->send($data);
                    }
                }
                break;
            case "buyCard":
                $tab = $this->service->buyCard(intval($content->gameId), intval($content->userId), intval($content->cardId));
                if ($tab !== null) {
                    $data = array("type" => $content->type, "userId" => $content->userId, "oldCard" => $content->cardId
                        ,"newCard" => $tab[0], "tokens" => $tab[1], "prestige" => $tab[2], 'gameTokens' => $tab[3]);
                    $data = json_encode($data);
                    echo count($this->clients);
                    foreach ($this->clients as $client) {
                        $client->send($data);
                    }
                }
                break;
            case "reserveRandomCard":
                $card = $this->service->getRandomCard(intval($content->gameId), intval($content->level));
                $this->service->addHiddenCard(intval($content->gameId), intval($content->userId), intval($card));
                $tab = $this->service->reserveCard(intval($content->gameId), intval($content->userId), intval($card));
                if ($tab !== null) {
                    $data = array("type" => $content->type, "userId" => $content->userId, "oldCard" => $card
                    , "joker" => $tab[1]);
                    $data = json_encode($data);
                    foreach ($this->clients as $client) {
                        $client->send($data);
                    }
                }
                break;
            case "canVisitNoble":
                $nobles = $this->service->canVisitNoble(intval($content->gameId), intval($content->userId));
                if ($nobles !== null) {
                    if (count($nobles) == 1) {
                        $prestige = $this->service->visitNoble($content->gameId, $content->userId, $nobles[0]);
                        if ($prestige != null) {
                            $data = array("type" => "visitNoble", "userId" => $content->userId
                            , "nobleId" => $nobles[0], "prestige" => $prestige);
                            $data = json_encode($data);
                            foreach ($this->clients as $client) {
                                $client->send($data);
                            }
                        }
                    } else if (count($nobles) > 1) {
                        $data = array("type" => $content->type, "nobles" => implode(",", $nobles));
                        $data = json_encode($data);
                        $from->send($data);
                    } else {
                        $end = $this->service->endTurn($content->gameId, $content->userId);
                        if ($end !== null) {
                            if (count($end) > 2) {
                                $data = array("type" => "moreTenToken", "userId" => $content->userId, "tokens" => $end[1]
                                , "total" => $end[2]);
                                $data = json_encode($data);
                                $from->send($data);
                            } else if ($end[0] === false) {
                                $data = array("type" => "endTurn", "userId" => $content->userId, "next" => $end[1]);
                                $data = json_encode($data);
                                foreach ($this->clients as $client) {
                                    $client->send($data);
                                }
                            } else {
                                $data = array("type" => "gameWin", "userId" => $content->userId, "winner" => $end[1]);
                                $data = json_encode($data);
                                foreach ($this->clients as $client) {
                                    $client->send($data);
                                }
                            }
                        }
                    }
                }
                break;
            case "visitNoble":
                $prestige = $this->service->visitNoble($content->gameId, $content->userId, $content->nobleId);
                if ($prestige !== null) {
                    $data = array("type" => "visitNoble", "userId" => $content->userId
                        , "nobleId" => $content->nobleId, "prestige" => $prestige);
                    $data = json_encode($data);
                    foreach ($this->clients as $client) {
                        $client->send($data);
                    }
                }
                break;
            case "endTurn":
                $end = $this->service->endTurn($content->gameId, $content->userId);
                if ($end !== null) {
                    if (count($end) > 2) {
                        $data = array("type" => "moreTenToken", "userId" => $content->userId, "tokens" => $end[1]
                            , "total" => $end[2]);
                        $data = json_encode($data);
                        $from->send($data);
                    } else if ($end[0] === false) {
                        $data = array("type" => "endTurn", "userId" => $content->userId, "next" => $end[1]);
                        $data = json_encode($data);
                        foreach ($this->clients as $client) {
                            $client->send($data);
                        }
                    } else {
                        $data = array("type" => "gameWin", "userId" => $content->userId, "winner" => $end[1]);
                        $data = json_encode($data);
                        foreach ($this->clients as $client) {
                            $client->send($data);
                        }
                    }
                }
                break;
            case "removeTokens":
                $tokens = $this->service->removeTokens($content->gameId, $content->userId, explode(",", $content->tokens));
                if ($tokens != null) {
                    $data = array("type" => $content->type, "userId" => $content->userId, "tokens" => $tokens);
                    $data = json_encode($data);
                    foreach ($this->clients as $client) {
                        $client->send($data);
                    }
                }
                break;
        }

    }

}