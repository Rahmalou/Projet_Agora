<?php

namespace AGORA\Game\SQPBundle\Model;

use Doctrine\ORM\EntityManager;
//Une petite collection de fonctions agissant sur la base de données
class SQPAPI {

    protected $manager;
    //On construit notre api avec un entity manager permettant l'accès à la base de données
    public function __construct(EntityManager $em) {
        $this->manager = $em;
    }

    public function supressGame($idGame) {
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $players = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->findBy(array('idGame' => $idGame));
        foreach ($players as $player) {
            $this->manager->remove($player);
            $this->manager->flush($player);
        }
        $this->manager->remove($game);
        $this->manager->flush($game);
    }

    //Explicite, indique si la partie peut commencer
    public function isReadyToBegin($idGame, $count) {
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        if ($count >= $game->getNbPlayers()) {
            return true;
        }
        return false;
    }

    //Enregistre la selection de la carte dans la base de données
    public function readyCard($idGame, $idPlayer, $card) {
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $player = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->find($idPlayer);
        $player->setLastCardPlayed($card);
        $this->manager->persist($player);
        $this->manager->flush();
        $this->removeCardFromHand($idPlayer, $card);
    }

    //Inqique si tous les joueurs ont sélectionné leur carte
    public function arePlayersCardReady($idGame) {
        $players = $this->getPlayersFromLobbyInOrder($idGame);
        $nb = 0;
        foreach ($players as $player) {
            if ($player['lastPlayedCard'] != null) {
                ++$nb;
            }
        }

        return $nb == count($players);
    }

    //Distribue les cartes à un joueur
    public function distribute($idGame, $idPlayer) {
        $player = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->find($idPlayer);
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $deck = $game->getDeck();
        $deckAsArray = preg_split("/,/",$deck);

        //On récupère les 10 dernières cartes et on les enlève du deck
        $handAsArray = array_splice($deckAsArray, count($deckAsArray) - 11);
        $hand = $this->arrayOfCardToString($handAsArray);
        echo "main de l'utilisateur d'id ". $player->getIdUser() ." : ".$hand."\n";
        $player->setHand($hand);
        $this->manager->persist($player);
        $this->manager->flush();
        //prendre les 10 dernière cartes, les effacer, mettre à jour dans la base de données la main du joueur, l'envoyer au joueur

        $deck = $this->arrayOfCardToString($deckAsArray);
        $game->setDeck($deck);
        $this->manager->persist($game);
        $this->manager->flush();
        return $hand;

    }


    //Distribue les cartes à tous le monde
    public function distributeToEveryone($idGame) {
        $players = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->findBy(array('idGame' => $idGame));
        $ret = array();
        foreach ($players as $player) {
            $id = $player->getId();
            $hand = $this->distribute($idGame, $id);
            $ret[''.$id] = $hand;
        }
        return $ret;
    }


    //Ajoute une carte au board
    public function addCardToBoard($idPlayer = 0, $idGame, $card, $row, &$msg = "") {
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $boardString = $game->getBoard();
        $board = preg_split("/;/",$boardString);
        $r = preg_split("/,/", $board[$row]);
        $size = count($r);
        if($size == 0 || $size < 6) {
            $r[$size] = $card;
        }

        $rString = $this->arrayOfCardToString($r);
        $board[$row] = $rString;
        $boardAsString = "".$board[0];
        for($i = 1; $i < 4; ++$i) {
            $boardAsString .= ";".$board[$i];
        }
        if ($idPlayer != 0) {
            $player = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->find($idPlayer);
            $player->setLastCardPlayed(null);
            $this->manager->persist($player);
            $this->manager->flush();
        }
        $game->setBoard($boardAsString);
        $this->manager->persist($game);
        $this->manager->flush();
        if (($r[$size - 1] > $card || $size + 1 >= 6) && $idPlayer != 0) {
            $msg = "takerowneeded";
        }
        return $boardAsString;

    }
    //Prépare le plateau pour un nouveau round
    public function setupBoard($idGame) {
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $deck = $game->getDeck();
        $deck = preg_split("/,/", $deck);
        $cards = array_splice($deck, count($deck) - 5);
        $board = "";
        for ($i = 0; $i < 4; ++$i) {
            $board = $this->addCardToBoard(0, $idGame, $cards[$i], $i);
        }
        $deck = $this->arrayOfCardToString($deck);
        $game->setDeck($deck);
        $this->manager->persist($game);
        $this->manager->flush();
        return $board;

    }

    //Transforme un array de cartes en une chaine de caractères pour la BDD
    public function arrayOfCardToString($array) {
        $ret = "";
        foreach ($array as $entry) {
            if ($entry != "" && $entry != null) {
                $ret .= $entry . ",";
            }
        }
        return rtrim($ret,", ");
    }

    //Explicite, retire une carte de la main du joueur
    public function removeCardFromHand($idPlayer, $card) {
        $player = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->find($idPlayer);
        $hand = $player->getHand();
        $handAsArray = preg_split("/,/",$hand);
        $i = 0;
        foreach ($handAsArray as $c) {
            if ($c == "".$card) {
                array_splice($handAsArray, $i, 1);
                $h = $this->arrayOfCardToString($handAsArray);
                $player->setHand($h);
                $this->manager->persist($player);
                $this->manager->flush();
                return $h;
            }
            ++$i;
        }
        return false;

    }

    //Retourne le board
    public function getBoard($idGame) {
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $board = $game->getBoard();
        return $board;
    }

    //Ajoute une carte à la main du joueur
    public function addCardToHand($idPlayer, $card) {
        $player = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->find($idPlayer);
        $hand = $player->getHand();
        $handAsArray = preg_split("/,/",$hand);
        $size = count($handAsArray);
        $handAsArray[$size] = $card;
        $h = $this->arrayOfCardToString($handAsArray);
        $player->setHand($h);
        $this->manager->persist($player);
        $this->manager->flush();
    }

    //Retourne toutes les infos utiles sur les joueurs d'une partie
    public function getPlayersFromLobbyInOrder($idGame) {
        $players = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->findBy(array('idGame' => $idGame));
        $i = 0;
        $ret = array();
        foreach ($players as $player) {
            $ret[$i]['idUser'] = $player->getIdUser()->getUsername();
            $ret[$i]['score'] = $player->getScore();
            $ret[$i]['id'] = $player->getId();
            $ret[$i]['orderTurn'] = $player->getOrderTurn();
            $ret[$i]['lastPlayedCard'] = $player->getLastCardPlayed();
            $ret[$i]['hand'] = $player->getHand();
            $ret[$i]['idAccount'] = $player->getIdUser();
            ++$i;
        }

        return $ret;
    }

    //Initialise l'ordre des joueurs lorsqu'ils ont selectionné leur carte
    public function setOrderTurn($idGame) {
        $players = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->findBy(array('idGame' => $idGame));
        $orders = array();
        foreach ($players as $player) {
            $orders[''.$player->getId()] = $player->getLastCardPlayed();
        }
        asort($orders, SORT_NUMERIC);
        foreach ($players as $player) {
            $i = 1;
            foreach ($orders as $key => $order) {
                if ($player->getId() == $key) {
                    $player->setOrderTurn($i);
                    $this->manager->persist($player);
                    $this->manager->flush();
                }
                ++$i;
            }

        }
    }

    //Incrémentation de l'ordre de tour
    public function increaseOrderTurn($idGame, $idPlayer) {
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $player = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->find($idPlayer);
        $turn = ($game->getTurn() + 1) % ($game->getNbPlayers() + 1);
        if ($turn == 0) {
            $turn = 1;
        }
        $player->setOrderTurn(0);
        $game->setTurn($turn);
        $this->manager->persist($game);
        $this->manager->flush();
        $this->manager->persist($player);
        $this->manager->flush();
        return $turn;

    }

    //Explicite, retourne une main
    public function getHand($idPlayer) {
        $player = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->find($idPlayer);
        return $player->getHand();
    }

    //Fonction prenant une ligne du plateau, ses points et les ajoute au score du joueur
    public function takeRow($idGame, $idPlayer, $row) {
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $player = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->find($idPlayer);
        $boardAsString = $game->getBoard();
        $board = preg_split("/;/",$boardAsString);
        $r = preg_split("/,/", $board[$row]);
        $size = count($r);
        $card = $r[$size - 1];
        $score = $player->getScore();
        //On compte le nombre de têtes de boeuf
        for ($i = 0 ; $i < $size - 1; ++$i) {
            $score += $this->getNbBeef($r[$i]);

        }
        //On modifie la ligne
        $r = array();
        $r[0] = $card;
        $r = $this->arrayOfCardToString($r);
        $board[$row] = $r;
        $boardAsString = "".$board[0];
        for($i = 1; $i < 4; ++$i) {
            $boardAsString .= ";".$board[$i];
        }
        $game->setBoard($boardAsString);
        $player->setScore($score);
        $this->manager->persist($game);
        $this->manager->flush();
        $this->manager->persist($player);
        $this->manager->flush();
        return $boardAsString;



    }


    //Retourne le nombre de têtes de boeuf d'une carte
    public function getNbBeef($card) {
        $nbeef = 1;
        if ($card%10 == 0) {
            $nbeef = 3;
        } else if ($card%5 == 0) {
            if ($card == 55) {
               $nbeef = 7;
            } else {
                $nbeef = 2;
            }

        } else if ($card%11 == 0) {
            $nbeef = 5;
        }
        return $nbeef;
    }

    //Indique si le joueur est le dernier à passer
    public function checkLastPlayer($idGame, $idPlayer) {
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $player = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->find($idPlayer);
        return $player->getOrderTurn() == $game->getNbPlayers();
    }

    //Indique si la partie est terminée
    public function checkEndGame($idGame, $idPlayer) {
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $player = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->find($idPlayer);
        $players = $this->getPlayersFromLobbyInOrder($idGame);
        $hand = preg_split("/,/", $player->getHand(), -1, PREG_SPLIT_NO_EMPTY);
        if ($player->getOrderTurn() == $game->getNbPlayers() && count($hand) == 0) {
            foreach ($players as $p) {
                if ($p['score'] >= 66) {
                    return true;
                }
            }
        } else {
            return false;
        }
        return false;
    }

    //reset le deck et le plateau pour un nouveau round
    public function resetDeckAndBoard($idGame) {
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $game->setBoard(";;;");
        $deck = array();
        for ($i = 1; $i <= 104; ++$i) {
            $deck[$i - 1] = $i;
        }
        shuffle($deck);
        $deckToString = "";
        for ($i = 0; $i < 104; ++$i) {
            $deckToString .= intval($deck[$i]).",";
        }
        $game->setDeck($deckToString);
        $this->manager->persist($game);
        $this->manager->flush();

    }

    //Mélange un tableau d'entiers, pour le deck
    public function shuffle($var) {

        for ($i = 103; $i > 0; $i--) {
            $j = floor(random() * ($i + 1));
            $x = $var[$i];
            $var[$i] = $var[$j];
            $var[$j] = $x;
        }
        return $var;
    }
    
    
    public function computeELO($idUser, $idGame) {
		$players = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->findBy(array('idGame' => $idGame));
		$winner = null;
		$player = null;

        foreach ($players as $p) {
            //trouve le winner
            if($winner == null || $winner->getScore() > $p -> getScore()) {
                $winner = $p;
            }
            //set le joueur courant
            if($p->getIdUser() == $idUser) {
                $player = $p;
            }
        }
 
		$playerLead = $this->manager->getRepository('AGORAPlatformBundle:Leaderboard')->findOneBy(array('idPlayer' => $idUser, 'idGame' => 2));
		$actualElo = $playerLead->getELO();
		
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
		        if($player->getScore() <= $p->getScore()) {
		            $resultat = 1;
                } else {
		            $resultat = 0;
                }
                $pLead = $this->manager->getRepository('AGORAPlatformBundle:Leaderboard')->findOneBy(array('idPlayer' => $p->getIdUser(), 'idGame' => 2));
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
}

