<?php
// \src\AGORA\SQPGame\SQPBundle\Sockets\SQP.php;

namespace AGORA\Game\SQPBundle\Sockets;

use AGORA\Game\SQPBundle\Controller\DefaultController;
use AGORA\Game\SQPBundle\Model\SQPAPI;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use AGORA\Game\SQPBundle\Entity\SQPGame;
use AGORA\Game\SQPBundle\Entity\SQPPlayer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SQP implements MessageComponentInterface {
    protected $clients;
    protected $container;
    protected $sqpapi;
    //Association partie(idGame) -> nombre de joueurs
    protected $nbPlayersPerGame;
    //Double association partie(idGame) -> joueur(idPlayer) -> socket
    protected $playersPerGame;
    protected $tchatPerGame;

    public function __construct(SQPAPI $sqp) {
        $this->sqpapi = $sqp;
        $this->clients = new \SplObjectStorage;
        $this->nbPlayersPerGame = array();
        $this->playersPerGame = array();
        $this->tchatPerGame = array();
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }
    //Le coeur du comportement du serveur
    public function onMessage(ConnectionInterface $from, $msg) {
        $content = json_decode($msg);
        //Quand un joueur se connecte
        if ($content->type == "ready") {
            //On incrémente le nombre de joueurs
            if (isset($this->nbPlayersPerGame[''.$content->idGame])) {
                $this->nbPlayersPerGame[''.$content->idGame] += 1;

                echo "Players for game ".$content->idGame." are ".$this->nbPlayersPerGame[''.$content->idGame]."\n";
            } else {
                $this->nbPlayersPerGame[''.$content->idGame] = 1;
                echo "Players for game ".$content->idGame." are ".$this->nbPlayersPerGame[''.$content->idGame]."\n";
            }
            //On associe à la partie le nouveau joueur et au joueur sa socket
            if (!isset($this->playersPerGame[''.$content->idGame])) {
                $this->playersPerGame[''.$content->idGame] = array();
            }

            $this->playersPerGame[''.$content->idGame][''.$content->idPlayer] = $from;
            //On envoie l'historique du tchat (les 20 derniers messages)
            if (!isset($this->tchatPerGame[''.$content->idGame])) {
                $this->tchatPerGame[''.$content->idGame] = array();
            }
            $ret = array('type' => "tchatLoad", 'tchat' => $this->tchatPerGame[''.$content->idGame]);
            $ret = json_encode($ret);
            $from->send($ret);

            echo $content->idPlayer." is ready !"."\n";

            //Si la partie est prête à commencer
            if ($this->sqpapi->isReadyToBegin($content->idGame, $this->nbPlayersPerGame[''.$content->idGame])) {
                echo "SQPGame ready to begin !"."\n";
                $players = $this->sqpapi->getPlayersFromLobbyInOrder($content->idGame);
                $ret = array('type' => "begin", 'players' => $players);
                $ret = json_encode($ret);
                foreach ($this->playersPerGame[''.$content->idGame] as $client) {
                    echo "Informing person ".$client->resourceId."\n";
                    $client->send($ret);
                }
            } else {
                $players = $this->sqpapi->getPlayersFromLobbyInOrder($content->idGame);
                $ret = array('type' => "newPlayer", 'players' => $players);
                $ret = json_encode($ret);
                foreach ($this->playersPerGame[''.$content->idGame] as $client) {
                    if ($client != $from) {
                        echo "Informing person ".$client->resourceId." of new arrival\n";
                        $client->send($ret);
                    }

                }
            }
        //Si l'host envoie le signal de lancement de la partie
        } else if ($content->type == "begin") {
            echo "Début de la partie\n";
            $this->sqpapi->setupBoard($content->idGame);
            $hands = $this->sqpapi->distributeToEveryone($content->idGame);
            $players = $this->sqpapi->getPlayersFromLobbyInOrder($content->idGame);
            $board = $this->sqpapi->getBoard($content->idGame);
            foreach ($hands as $key => $hand) {
                $ret = array('type' => "refreshAll", 'hand' => $hand, 'players' => $players,
                    'board' => $board);
                $ret = json_encode($ret);
                $this->playersPerGame[$content->idGame][$key]->send($ret);
            }
        //Lorsqu'un joueur veut déposer une carte sur le plateau
        } else if ($content->type == "card") {
            echo "SQPPlayer ".$content->idPlayer." of SQPGame ".$content->idGame
                ." wants to place the card ".$content->card." in the row ".$content->row."\n";
            $board = ";;;;";
            $turn = 0;
            $hand = $this->sqpapi->getHand($content->idPlayer);
            $msg ="";
            $board = $this->sqpapi->addCardToBoard($content->idPlayer, $content->idGame, $content->card, $content->row, $msg);
            //Si une erreur survient lors du placement de la carte
            if ($board == false) {
                 $this->sqpapi->addCardToHand($content->idPlayer, $content->card);
                 $ret = array('type' => "error", 'msg' => "Vous ne pouvez pas placer cette carte !");
                 $ret = json_encode($ret);
                 $from->send($ret);
            //Si le joueur doit se prendre une ligne
            } else if ($msg == "takerowneeded") {
                $board = $this->sqpapi->takeRow($content->idGame, $content->idPlayer, $content->row);
            }
            $handAsArray = preg_split("/,/",$hand, -1, PREG_SPLIT_NO_EMPTY);
            //Si la main du joueur est vide après avoir joué
            if (count($handAsArray) == 0) {
                //On check si c'est la fin de la partie
                if ($this->sqpapi->checkEndGame($content->idGame, $content->idPlayer)) {
                    $ret = array('type' => "END", 'players' => $this->sqpapi->getPlayersFromLobbyInOrder($content->idGame));
                    $ret = json_encode($ret);
                    foreach ($this->playersPerGame[''.$content->idGame] as $conn) {
                        $conn->send($ret);
                    }
                    $ret = array('type' => "refreshHand", 'hand' => $hand);
                    $ret = json_encode($ret);
                    $from->send($ret);
                    $ret = array('type' => "refreshBoard", 'board' => $board, 'turn' => $turn, 'justPlayed' => $content->idPlayer,
                        'players' => $this->sqpapi->getPlayersFromLobbyInOrder($content->idGame));
                    $ret = json_encode($ret);
                    foreach ($this->playersPerGame[''.$content->idGame] as $conn) {
                        $conn->send($ret);
                    }
                    //ELO
                    $players = $this->sqpapi->getPlayersFromLobbyInOrder($content->idGame);
					foreach ($players as $player) {
						$this->sqpapi->computeELO($player['idAccount'] , $content->idGame);
					} 
                    //
                    
                    $this->sqpapi->supressGame($content->idGame);

                    return;
                //On check si c'est le dernier tour
                } else if ($this->sqpapi->checkLastPlayer($content->idGame,$content->idPlayer)) {
                    echo "End of round\n";
                    $this->sqpapi->resetDeckAndBoard($content->idGame);
                    $board = $this->sqpapi->setupBoard($content->idGame);
                    $this->sqpapi->distributeToEveryone($content->idGame);
                }

            }
            //Incrémentation du tour de jeu
            $turn = $this->sqpapi->increaseOrderTurn($content->idGame, $content->idPlayer);
            echo "Card added to board !\n";
            $ret = array('type' => "refreshHand", 'hand' => $hand);
            $ret = json_encode($ret);
            $from->send($ret);
            $ret = array('type' => "refreshBoard", 'board' => $board, 'turn' => $turn, 'justPlayed' => $content->idPlayer,
                'players' => $this->sqpapi->getPlayersFromLobbyInOrder($content->idGame));
            $ret = json_encode($ret);
            foreach ($this->playersPerGame[''.$content->idGame] as $conn) {
                $conn->send($ret);
            }
        //Un joueur séléectionne une carte
        } else if ($content->type == "readyCard") {
            $this->sqpapi->readyCard($content->idGame, $content->idPlayer, $content->card);
            if ($this->sqpapi->arePlayersCardReady($content->idGame)) {
                $this->sqpapi->setOrderTurn($content->idGame);
                $ret = array('type' => "READY",
                    'players' => $this->sqpapi->getPlayersFromLobbyInOrder($content->idGame));
                $ret  = json_encode($ret);
                foreach ($this->playersPerGame[''.$content->idGame] as $idPlayer => $client) {
                    $client->send($ret);
                }
            } else {
                $ret = array('type' => "readyCard", 'idPlayer' => $content->idPlayer);
                $ret = json_encode($ret);
                foreach ($this->playersPerGame[''.$content->idGame] as $client) {
                    if ($client != $from) {
                        $client->send($ret);
                    }
                }
            }
        } else if ($content->type == "message") {
            echo "Message received by $content->username : $content->message\n
                    Sending it right now\n";
            //On sauvegarde le message dans la socket
            $countMsg = count($this->tchatPerGame[''.$content->idGame]);
            if ($countMsg == 20) {
                array_shift($this->tchatPerGame[''.$content->idGame]);
                --$countMsg;
            }
            $this->tchatPerGame[''.$content->idGame][$countMsg] = $msg;
            //On envoie le message à tout le monde
            foreach ($this->playersPerGame[''.$content->idGame] as $idPlayer => $client) {
                if ($client != $from) {
                    $client->send($msg);
                }
            }
        }



    }
    //Lorsqu'un joueur quitte la partie, on décrémente le nombre de joueurs connectés
    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        foreach ($this->playersPerGame as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if ($value2 == $conn) {
                    unset($this->playersPerGame[''.$key][''.$key2]);
                    $this->nbPlayersPerGame[''.$key] -= 1;
                    echo "Players for game $key are ".$this->nbPlayersPerGame[''.$key]."\n";
                }
            }
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }


}
