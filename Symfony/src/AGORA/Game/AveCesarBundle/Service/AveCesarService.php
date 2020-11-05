<?php
/**
 * Created by PhpStorm.
 * User: anthony
 * Date: 21/04/18
 * Time: 22:21
 */

namespace AGORA\Game\AveCesarBundle\Service;


use AGORA\Game\AveCesarBundle\Entity\AveCesarGame;
use AGORA\Game\AveCesarBundle\Entity\AveCesarPlayer;
use AGORA\Game\GameBundle\Entity\Game;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AveCesarService {

    protected $manager;
    //On construit notre api avec un entity manager permettant l'accès à la base de données
    public function __construct(EntityManager $em) {
        $this->manager = $em;
    }

    public function createRoom($name, $playersNb, $private, $password = "", $userId) {
        $avcGame = new AveCesarGame();
        $avcGame->setNextplayer(0);
        $avcGame->setFirstplayer(0);
        $this->manager->persist($avcGame);
        $this->manager->flush();

        $game = new Game();
        $game->setGameId($avcGame->getId());
        $gameInfoManager = $this->manager->getRepository('AGORAPlatformBundle:GameInfo');
        $gameInfo = $gameInfoManager->findOneBy(array('gameCode' => "avc"));
        $game->setGameInfoId($gameInfo);
        $game->setName($name);
        $game->setNbPlayers($playersNb);
        $game->setIdHost($userId);
        // TODO hachage password
        $game->setPassword($password);
        $game->setPrivate($private);
        $game->setState("waiting");
        $game->setDateCrea(new \DateTime("now"));
        $this->manager->persist($game);
        $this->manager->flush();

        return $game->getId();
    }

    public function createPlayer($user, $gameId) {
        $avcgame = $this->manager->getRepository('AGORAGameAveCesarBundle:AveCesarGame')->find($gameId);
        if ($avcgame == null) {
            throw new \Exception();
        }

        $game = $this->manager->getRepository('AGORAGameGameBundle:Game')
            ->findOneBy(array('gameId' => $gameId));

        $players = $this->manager->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
            ->findBy(array('game_id' => $gameId));

        $nbPlayer = count($players);

        if ($nbPlayer >= $game->getNbPlayers()) {
            return -1;
        }

        $player = new AveCesarPlayer();
        $player->setGameId($gameId);
        $player->setHand("");


        // Génération de la prochaine position de départ

        $player->setPosition("0". chr(ord('b') + $nbPlayer));
        $player->setLap(1);
        $player->setUserId($user);
        $player->setCesar(false);
        //$player->setDeck($this->newDeck());
        $player->setFinish(0);

        $deck = preg_split("/,/", $this->newDeck());
        $hand = array_splice($deck, -3);
        $player->setHand($this->arrayToString($hand));
        $player->setDeck($this->arrayToString($deck));

        $this->manager->persist($player);
        $this->manager->flush();
        $this->setFirstPlayer($player->getId(), $gameId);

        if (!$this->getNextPlayer($gameId)) {
            $this->setNextPlayer($gameId, $player->getId());
        }

        if ($nbPlayer + 1 == $game->getNbPlayers()) {
            $this->initPlayers($gameId);
        }
        $this->flush();
        return $player->getId();
    }

    public function getHand($playerId) {
        $player = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
            ->find($playerId);
        $hand = preg_split("/,/", $player->getHand());
        return $hand;
    }

    public function getPlayerFromUserId($gameId, $userId) {
        $player = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
            ->findOneBy(array('game_id' => $gameId, 'user_id' => $userId));
        return $player;
    }

    public function getPlayer($gameId, $playerId) {
        $player = $this->manager
                ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
                ->findOneBy(array('game_id' => $gameId, 'id' => $playerId));
        return $player;
    }

    public function getPlayerName($playerId) {
        $player = $this->manager
                ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
                ->find($playerId);
        return $player->getUserId()->getUsername();
    }

    public function getAllPlayers($gameId) {
        $this->flush();
        $players = $this->manager
                ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
                ->findBy(array('game_id' => $gameId));
        return $players;
    }

    public function getPlayers($gameId) {
        return $this->getAllPlayers($gameId);
    }

    public function playerAlreadyCreated($gameId, $userId) {
        $player = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
            ->findOneBy(array('game_id' => $gameId, 'user_id' => $userId));
        return $player != null;
    }

    public function getMaxPlayer($gameId) {
        $game = $this->manager
            ->getRepository('AGORAGameGameBundle:Game')
            ->findOneBy(array('gameId' => $gameId));
        return $game->getNbPlayers();
    }

    public function getGame($gameId) {
        $game = $this->manager
                ->getRepository('AGORAGameAveCesarBundle:AveCesarGame')
                ->find($gameId);
        return $game;
    }

    public function getGameName($gameId) {
        $gameInfo = $this->manager->getRepository('AGORAPlatformBundle:GameInfo')
            ->findOneBy(array('gameCode' => "avc"));

        $game = $this->manager->getRepository('AGORAGameGameBundle:Game')
            ->findOneBy(array('gameId' => $gameId, 'gameInfoId' => $gameInfo->getId()));

        return $game->getName();
    }

    public function movePlayer($playerId, $position, $card) {
        $player = $this->manager
                ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
                ->find($playerId);
        $oldPosition = $player->getPosition();
        $deck = preg_split("/,/", $player->getDeck());
        $hand = preg_split("/,/", $player->getHand());
        $player->setPosition($position);
        if (count($deck) == 0) {
            return -1;
        }
        if ($player->getLap() < 3 && !$player->getCesar()) {
            if ($this->isCesarWay($oldPosition, $position, $card)) {
                $player->setCesar(true);
            }
        }
        if ($this->isNextLap($oldPosition, $position)) {
            $lap = $player->getLap();
            $player->setLap($lap + 1);
        }
        $drawnCard = array_splice($deck, -1)[0];
        $hand[array_search($card, $hand)] = $drawnCard;
        $player->setHand($this->arrayToString($hand));
        $player->setDeck($this->arrayToString($deck));
        $this->manager->persist($player);
        $this->manager->flush();
        return intval($drawnCard);
    }

    public function initPlayers($gameId) {
        $players = $this->manager
                ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
                ->findBy(array('game_id' => $gameId));
        //$i = 'a';
        foreach ($players as $p) {
            /*$deck = preg_split("/,/", $p->getDeck());
            $hand = array_splice($deck, -3);
            $p->setHand($this->arrayToString($hand));
            $p->setDeck($this->arrayToString($deck));*/
            //$p->setPosition('1' . $i);
            $this->manager->persist($p);
            $this->manager->flush();
            //++$i;
        }
        $this->manager->flush();
    }

    public function setNextPlayer($gameId, $playerId) {
        $game = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarGame')
            ->find($gameId);
        $game->setNextplayer($playerId);
        $this->manager->persist($game);
        $this->manager->flush();
    }

    public function getNextPlayer($gameId) {
        $game = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarGame')
            ->find($gameId);
        $player = $game->getNextplayer();
        return $player;
    }

    private function newDeck() {
        $deck = array();
        $k = 0;
        for ($i = 1; $i <= 6; ++$i) {
            for ($j = 0; $j < 4; ++$j) {
                $deck[$k] = $i;
                ++$k;
            }
        }
        for ($i = 23; $i > 0; $i--) {
            $j = rand(0, 23);
            $x = $deck[$i];
            $deck[$i] = $deck[$j];
            $deck[$j] = $x;
        }
        return $this->arrayToString($deck);
    }

    public function isFirstPlayer($playerId, $gameId) {
        $game = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarGame')
            ->find($gameId);
        $player = $game->getFirstplayer();
        return $player == $playerId;
    }

    public function setFirstPlayer($playerId, $gameId) {
        $game = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarGame')
            ->find($gameId);
        $game->setFirstplayer($playerId);
        $this->manager->persist($game);
        $this->manager->flush();
    }

    public function getFirstPlayer($gameId) {
        $game = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarGame')
            ->find($gameId);
        $firstPlayerId = $game->getFirstplayer();
        return $this->getPlayer($gameId, $firstPlayerId);
    }

    private function arrayToString($cards) {
        $string = "";
        foreach ($cards as $c) {
            $string .= $c . ",";
        }
        return trim($string, ", ");
    }

    private function isNextLap($oldPosition, $newPosition) {
        $oldPosition = substr($oldPosition, 0, -1);
        $newPosition = substr($newPosition, 0, -1);
        $result = false;
        if ($oldPosition > 25 && $newPosition < 7) {
            $result = true;
        }
        return $result;
    }

    private function isCesarWay($oldPosition, $newPosition, $moveCount) {
        $cesarWay = array("30a", "31a", "0a", "1a", "2a");
        if (in_array($newPosition, $cesarWay)) {
            return true;
        }
        if ($oldPosition == "29a" && $newPosition == "3a" && $moveCount == 6) {
            return true;
        }
        return false;
    }

    public function finishPlayer($playerId, $gameId) {
        $player = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
            ->findOneBy(array('game_id' => $gameId, 'id' => $playerId));
        $player->setFinish(true);
        $this->manager->persist($player);
        $this->manager->flush();
    }

    public function setGameState($gameId, $state) {
        $gameInfo = $this->manager->getRepository('AGORAPlatformBundle:GameInfo')
            ->findOneBy(array('gameCode' => "avc"));
        $game = $this->manager->getRepository('AGORAGameGameBundle:Game')
            ->findOneBy(array('gameId' => $gameId, 'gameInfoId' => $gameInfo->getId()));
        $game->setState($state);
        $this->manager->persist($game);
        $this->manager->flush();
    }

    public function getGameState($gameId) {
        $gameInfo = $this->manager->getRepository('AGORAPlatformBundle:GameInfo')
            ->findOneBy(array('gameCode' => "avc"));
        $game = $this->manager->getRepository('AGORAGameGameBundle:Game')
            ->findOneBy(array('gameId' => $gameId, 'gameInfoId' => $gameInfo->getId()));
        return $game->getState();
    }

    public function finishGame($gameId) {
        $gameInfo = $this->manager->getRepository('AGORAPlatformBundle:GameInfo')
            ->findOneBy(array('gameCode' => "avc"));
        $game = $this->manager->getRepository('AGORAGameGameBundle:Game')
            ->findOneBy(array('gameId' => $gameId, 'gameInfoId' => $gameInfo->getId()));
        $players = $this->getAllPlayers($gameId);

        //on modifie le ELO
        foreach ($players as $player) {
            $this->computeELO($player->getUserId(), $gameId);
        }

        for ($i = 0; $i < count($players); $i++) {
            $this->manager->remove($players[$i]);
        }
        $this->manager->remove($game);
        $this->manager->flush();
    }

    public function isFinishPlayer($gameId, $playerId) {
        return $this->getPlayer($gameId, $playerId)->getFinish() != 0;
    }

    public function isFinishGame($gameId) {
        $gameInfo = $this->manager->getRepository('AGORAPlatformBundle:GameInfo')
            ->findOneBy(array('gameCode' => "avc"));
        $game = $this->manager->getRepository('AGORAGameGameBundle:Game')
            ->findOneBy(array('gameId' => $gameId, 'gameInfoId' => $gameInfo->getId()));

        return $game->getState() == "finish";
    }

    public function getRanking($gameId) {
        $players = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
            ->findBy(array('game_id' => $gameId), array('finish' => 'ASC'));
        return array_map(function ($p) {return array($p->getUserId()->getUserName());}, $players);
    }

    public function computeELO($idUser, $idGame) {
        $players = $this->manager->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')->findBy(array('game_id' => $idGame));
        $winner = null;
        $player = null;

        foreach ($players as $p) {
            //trouve le winner
            if($winner == null || $p->getFinish() > $winner->getFinish()) { // A changer en fonction du jeu
                $winner = $p;
            }

            //set le joueur courant
            if($p->getUserId() == $idUser) {
                $player = $p;
            }
        }
        
        $playerLead = $this->manager->getRepository('AGORAPlatformBundle:Leaderboard')->findOneBy(array('idPlayer' => $idUser, 'idGame' => 1));
        $actualElo = $playerLead->getElo();
        
        //Définit un coefficient de manière arbitraire en fonction de l'ELO actuel, 
        //Plus l'ELO est grande, plus le coeff est petit
        if ($actualElo < 2000) {
            $coeff = 80;
        } else if ($actualElo < 4000) {
            $coeff = 50;
        } else if ($actualElo < 4800) {
            $coeff = 30;
        } else {
            $coeff = 20;
        }

        //Modifie l'ELO
        foreach ($players as $p) {
            $resultat = 0;
            if ($p != $player) {
                if($player->getFinish() > $p->getFinish()) { // A changer en fonction du jeu
                    $resultat = 1;
                } else {
                    $resultat = 0;
                }
                $pLead = $this->manager->getRepository('AGORAPlatformBundle:Leaderboard')->findOneBy(array('idPlayer' => $p->getUserId(), 'idGame' => 1));
                $esti = 1 / (1 + pow(10,($pLead->getELO() - $actualElo)/400));
                $actualElo = $actualElo + $coeff*($resultat - $esti);
            }
        }

        $playerLead->setELO($actualElo);

        if($player == $winner) {
            $playerLead->setNbVic($playerLead->getNbVic() + 1);
        } else {
            $playerLead->setNbDef($playerLead->getNbDef() + 1);
        }

        $this->manager->persist($playerLead);
        $this->manager->flush();
    }

    public function isCesar($gameId, $playerId) {
        $player = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
            ->findOneBy(array('game_id' => $gameId, 'id' => $playerId));
        return $player->getCesar();
    }

    public function flush() {
        $this->manager->flush();
    }

    public function getLap($gameId, $playerId) {
        $player = $this->manager
            ->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')
            ->findOneBy(array('game_id' => $gameId, 'id' => $playerId));
        return $player->getLap();
    }
}