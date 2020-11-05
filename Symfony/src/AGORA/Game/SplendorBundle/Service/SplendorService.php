<?php


namespace AGORA\Game\SplendorBundle\Service;


use AGORA\Game\GameBundle\Entity\Game;
use AGORA\Game\SplendorBundle\Entity\SplendorCard;
use AGORA\Game\SplendorBundle\Entity\SplendorGame;
use AGORA\Game\SplendorBundle\Entity\SplendorPlayer;
use Doctrine\ORM\EntityManager;

class SplendorService
{

    protected $manager;
    private $nbTurn;
    private $end;
    private $winner;

//    private $cardsValues = [[0, 1, 1, 1, 1], [0, 1, 1, 1, 2], [0, 1, 2, 0, 2], [1, 3, 0, 1, 0], [0, 1, 0, 2, 0],
//        [0, 2, 2, 0, 0], [0, 0, 3, 0, 0], [0, 0, 0, 0, 4],/**/ [1, 0, 1, 1, 1], [1, 0, 2, 1, 1], [2, 0, 2, 1, 0],
//        [3, 1, 1, 0, 0], [0, 0, 0, 1, 2], [2, 0, 0, 0, 2], [0, 0, 0, 0, 3], [0, 0, 4, 0, 0],/**/ [1, 1, 0, 1, 1],
//        [1, 1, 0, 2, 1], [1, 0, 0, 2, 2], [0, 0, 1, 1, 3], [1, 2, 0, 0, 0], [0, 0, 2, 2, 1], [0, 0, 0, 3, 0],
//        [0, 0, 0, 4, 0],/**/ [1, 1, 1, 0, 1], [2, 1, 1, 0, 1], [2, 2, 0, 0, 1], [0, 1, 0, 3, 1], [0, 0, 2, 0, 1],
//        [0, 2, 0, 0, 2], [0, 3, 0, 0, 0], [4, 0, 0, 0, 0],/**/ [1, 1, 1, 1, 0], [1, 2, 1, 1, 0], [0, 2, 1, 2, 0],
//        [1, 0, 3, 0, 1], [2, 0, 1, 0, 0], [2, 0, 0, 2, 0], [3, 0, 0, 0, 0], [0, 4, 0, 0, 0],/**/ [2, 0, 3, 3, 0],
//        [0, 3, 0, 2, 2], [0, 2, 0, 4, 1], [3, 5, 0, 0, 0], [5, 0, 0, 0, 0], [6, 0, 0, 0, 0],/**/ [2, 2, 3, 0, 0],
//        [3, 2, 0, 0, 3], [0, 3, 0, 5, 0], [0, 0, 1, 2, 4], [0, 5, 0, 0, 0], [0, 6, 0, 0, 0],/**/ [0, 0, 2, 2, 3],
//        [0, 3, 2, 0, 3], [2, 4, 0, 1, 0], [0, 0, 0, 3, 5], [0, 0, 0, 0, 5], [0, 0, 6, 0, 0],/**/ [3, 0, 2, 0, 2],
//        [0, 3, 3, 2, 0], [1, 0, 4, 0, 2], [0, 0, 5, 0, 3], [0, 0, 5, 0, 0], [0, 0, 0, 6, 0],/**/ [2, 2, 0, 3, 1],
//        [3, 0, 0, 3, 2], [4, 1, 2, 0, 0], [5, 0, 3, 0, 0], [0, 0, 0, 5, 0], [0, 0, 0, 0, 6],/**/ [0, 3, 3, 5, 3],
//        [0, 7, 0, 0, 0], [3, 6, 0, 3, 0], [3, 7, 0, 0, 0],/**/ [3, 0, 3, 3, 5], [0, 0, 0, 7, 0], [0, 3, 0, 6, 3],
//        [0, 3, 0, 7, 0],/**/ [3, 5, 0, 3, 3], [7, 0, 0, 0, 0], [6, 3, 3, 0, 0], [7, 0, 3, 0, 0],/**/ [3, 3, 5, 0, 3],
//        [0, 0, 0, 0, 7], [0, 0, 3, 3, 6], [0, 0, 0, 3, 7],/**/ [5, 3, 3, 3, 0], [0, 0, 7, 0, 0], [3, 0, 6, 0, 3],
//        [0, 0, 7, 0, 3],/**/ [4, 0, 4, 0, 0], [0, 0, 3, 3, 3], [0, 4, 0, 4, 0], [0, 0, 0, 4, 4], [4, 4, 0, 0, 0],
//        [3, 3, 3, 0, 0], [3, 3, 0, 3, 0], [0, 0, 4, 0, 4], [0, 3, 0, 3, 3], [3, 0, 3, 0, 3]];
//
//    private $cardBonus = ["Green", "Blue", "Red", "White", "Black", "Noble"];

    public function __construct(EntityManager $em)
    {
        $this->manager = $em;
        $this->nbTurn = 1;
        $this->end = false;
        $this->winner = null;
    }


    /*public function createCards($idGame)
    {
        $level = 1;
        $i = 0;
        $j = 0;
        for ($k = 0; $k < 100; $k++) {
            $cards = new SplendorCard();
            $prestige = 0;
            if ($k >= 40) {
                $prestige = 1;
            }
            if ($k >= 70) {
                $prestige = 3;
            }
            if ($k == 40) {
                $level = 2;
                $j = -1;
            }
            if ($k == 70) {
                $level = 3;
                $j = -1;
            }
            if ($k == 90) {
                $level = 0;
                $j = 5;
            }
            if (($level == 1 && $i >= 8) || ($level == 2 && $i >= 6) || ($level == 3 && $i >= 4)) {
                $i = 0;
                $j++;
            }
            if ($level == 1 && $i == 7) {
                $prestige = 1;
            }
            if ($level == 2 && $i >= 2) {
                $prestige = 2;
            }
            if ($level == 2 && $i == 5) {
                $prestige = 3;
            }
            if ($level == 3 && $i >= 1) {
                $prestige = 4;
            }
            if ($level == 3 && $i == 3) {
                $prestige = 5;
            }
            if ($j < 0) {
                $j = 0;
            }
            $cards->setGameId($idGame);
            $cards->setLevel($level);
            $cards->setBonus($this->cardBonus[$j]);
            $cards->setPrestige($prestige);
            $cards->setEmeraldTokens($this->cardsValues[$k][0]);
            $cards->setSapphireTokens($this->cardsValues[$k][1]);
            $cards->setRubyTokens($this->cardsValues[$k][2]);
            $cards->setDiamondTokens($this->cardsValues[$k][3]);
            $cards->setOnyxTokens($this->cardsValues[$k][4]);
            $this->manager->persist($cards);
            $this->manager->flush();

            $i++;
        }

    }*/

    public function getRandomCard($gameId, $level) {
        $x = 1; $y = 40;
        switch ($level) {
            case 2: $x = 41; $y = 70;
            break;
            case 3: $x = 71; $y = 90;
            break;
            default: break;
        }
        $id = rand($x, $y);
        if ($gameId >= 0 && $gameId != null) {
            $game = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorGame');
            $boardCards = $game->find($gameId)->getIdCards();
            $allPlayers = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer');
            $players = $allPlayers->findBy(array('gameId' => $gameId));
            $playersCards = [];
            foreach ($players as $p) {
                $playersCards = array_merge($playersCards, $p->getBuyedCards(), $p->getReservedCards());
            }
            $cards = array_merge($playersCards, $boardCards);
            while (in_array($id, $cards)) {
                $id = rand($x, $y);
            }
        }
        return $id;

    }

    public function getTwelveRandomCards() {
        $cards = [];
        $x = 1; $y = 40;
        for ($k = 0; $k < 12; $k++) {
            if ($k >= 4) {
                $x = 41; $y = 70;
            }
            if ($k >= 8) {
                $x = 71; $y = 90;
            }
            do {
                $id = rand($x, $y);
            } while (in_array($id, $cards));
            array_push($cards, $id);
        }
        return $cards;
    }

    public function getRandomNobles($nb) {
        $ids = [];
        for ($k = 0; $k < $nb; $k++) {
            do {
                $id = rand(91, 100);
            } while (in_array($id, $ids));
            array_push($ids, $id);
        }
        return $ids;
    }

    public function createGame($name, $playersNb, $private, $password = "", $userId) {
        $spldrGame = new SplendorGame();
        $cardsId = $this->getTwelveRandomCards();
        $spldrGame->setIdCards(implode(",", $cardsId));
        $nobles = $this->getRandomNobles($playersNb + 1);
        $spldrGame->setIdNobles(implode(",", $nobles));

        switch ($playersNb) {
            case 4: $spldrGame->setListTokens("7,7,7,7,7,5");
            break;
            case 3: $spldrGame->setListTokens("5,5,5,5,5,5");
            break;
            case 2: $spldrGame->setListTokens("4,4,4,4,4,5");
            break;
        }
//        $user = $this->manager->getRepository('AGORAUserBundle:User')->find($userId);
        $spldrGame->setIdUserTurn($userId);
        $this->manager->persist($spldrGame);
        $this->manager->flush();

        $game = new Game();
        $game->setGameId($spldrGame->getId());
        $gameInfoManager = $this->manager->getRepository('AGORAPlatformBundle:GameInfo');
        $gameInfo = $gameInfoManager->findOneBy(array('gameCode' => "spldr"));
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

        return $game->getGameId();

    }

    public function createPlayer($gameId, $userId) {
        $player = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
            ->findOneBy(array('gameId' => $gameId, 'idUser' => $userId));

        if ($player == null) {
            $spldrPlayer = new SplendorPlayer();
            $spldrPlayer->setGameId($gameId);
            $spldrPlayer->setIdUser($userId);
            $spldrPlayer->setPrestige(0);
            $spldrPlayer->setListTokens("0,0,0,0,0,0");
            $spldrPlayer->setBuyedCards("");
            $spldrPlayer->setReservedCards("");
            $spldrPlayer->setHiddenCards("");
            $this->manager->persist($spldrPlayer);
            $this->manager->flush();
        }
    }

    public function getAllPlayers($gameId) {
        $this->manager->flush();
        $players = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
            ->findBy(array('gameId' => $gameId));
        return $players;

    }

    public function getGame($gameId) {
        $this->manager->flush();
        $game = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorGame')->find($gameId);
        return $game;
    }

    public function reserveCard($gameId, $userId, $cardId) {
        $game = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorGame')->find($gameId);
        if ($game->getIdUserTurn() != $userId) {
            return null;
        }
        $cards = $game->getIdCards();
        $player = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
            ->findOneBy(array('gameId' => $gameId, 'idUser' => $userId));
        $i = array_search($cardId, $cards);
        $playerCard = $player->getReservedCards();
        //Si la carte est sur le plateau et que le joueur a moins de 3 cartes deja reservées
        if (is_numeric($i) && count($playerCard) < 4) {
            //On calcul le niveau de la carte à piocher
            $level = (1 + intval($i / 4));
            //On pioche la carte qui va remplacer la carte reservée
            $newCard = $this->getRandomCard($gameId, $level);
            //On met la carte piochée à la place de celle réservée
            $cards[$i] = $newCard;
            $game->setIdCards(implode(",", $cards));
            $gameTokens = $game->getListTokens();
            $gold = 0;
            //Si il reste des jetons or(joker) sur le plateau
            if ($gameTokens[5] > 0) {
                //Le joueur en prend un
                $tokens = $player->getListTokens();
                $tokens[5] += 1;
                $gameTokens[5] -= 1;
                $game->setListTokens(implode(",", $gameTokens));
                $player->setListTokens(implode(",", $tokens));
                $gold = 1;
            }

            $this->manager->persist($game);
            $this->manager->flush();

            //On ajoute la carte réservée dans la main du joueur
            array_push($playerCard, $cardId);
            $player->setReservedCards(implode(",", $playerCard));

            $this->manager->persist($player);
            $this->manager->flush();

            return array($newCard, $gold);
        }

        if (count($playerCard) < 4) {
            $gold = 0;
            $gameTokens = $game->getListTokens();
            //Si il reste des jetons or(joker) sur le plateau
            if ($gameTokens[5] > 0) {
                //Le joueur en prend un
                $tokens = $player->getListTokens();
                $tokens[5] += 1;
                $gameTokens[5] -= 1;
                $game->setListTokens(implode(",", $gameTokens));
                $player->setListTokens(implode(",", $tokens));
                $gold = 1;
                $this->manager->persist($game);
                $this->manager->flush();
            }
            //On ajoute la carte réservée dans la main du joueur
            array_push($playerCard, $cardId);
            $player->setReservedCards(implode(",", $playerCard));
            $this->manager->persist($player);
            $this->manager->flush();
            $newCard = 0;
            return array($newCard, $gold);
        }

        return null;
    }

    public function buyCard($gameId, $userId, $cardId) {
        $game = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorGame')->find($gameId);
        if ($game->getIdUserTurn() != $userId) {
            return null;
        }
        $player = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
            ->findOneBy(array('gameId' => $gameId, 'idUser' => $userId));
        $cardsGame = $game->getIdCards();
        $cardsReserved = $player->getReservedCards();
        $i = array_search($cardId, $cardsGame);
        $j = array_search($cardId, $cardsReserved);
        $playerCard = $player->getBuyedCards();
        $cardTable = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorCard')->find($cardId);
        $playerTokens = $player->getListTokens();
        $gameTokens = $game->getListTokens();

        $bonus = [0,0,0,0,0];
        //On calcul les bonus du joueur
        foreach ($player->getBuyedCards() as $id) {
            if ($id != 0) {
                $buyedCard = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorCard')->find($id);
                switch ($buyedCard->getBonus()) {
                    case "Green":
                        $bonus[0] += 1;
                        break;
                    case "Blue":
                        $bonus[1] += 1;
                        break;
                    case "Red":
                        $bonus[2] += 1;
                        break;
                    case "White":
                        $bonus[3] += 1;
                        break;
                    case "Black":
                        $bonus[4] += 1;
                        break;
                }
            }
        }

        $jokerNeed = 0;
        //On verifie si le joueur a les ressources necessaires
        for ($k = 0; $k < 5; $k++) {
            $jok = 0;
            $tok = $cardTable->getTokens($k);
            if ($tok > $playerTokens[$k] + $bonus[$k]) {
                $jokerNeed += ($tok - ($playerTokens[$k] + $bonus[$k]));
                $jok = ($tok - ($playerTokens[$k] + $bonus[$k]));
            }
            //else {
            $reste = $tok - $bonus[$k] - $jok;
            if ($bonus[$k] >= $tok) {
                $reste = 0;
            }
            //On en profite pour mettre à jour les ressources du joueur
            $playerTokens[$k] = $playerTokens[$k] - $reste;
            //Et pour mettre a jour les ressources du plateau
            $gameTokens[$k] = $gameTokens[$k] + $reste;
            //}
        }

        //Si la carte est sur le plateau ou dans les carte réservé du joueur
        // et que le joueur a les ressources necessaires
        if ((is_numeric($i) || is_numeric($j)) && $jokerNeed <= $playerTokens[5]) {
            $newCard = null;
            //Si la carte est sur le plateau
            if (is_numeric($i)) {
                //On calcul le niveau de la carte à piocher
                $level = (1 + intval($i / 4));
                //On pioche la carte qui va remplacer la carte achetée
                $newCard = $this->getRandomCard($gameId, $level);
                //On met la carte piochée à la place de celle achetée
                $cardsGame[$i] = $newCard;
                $game->setIdCards(implode(",", $cardsGame));
            } else {
                //Si la carte est dans les cartes réservées du joueur
                //On la retire des cartes réservées
                array_splice($cardsReserved, $j, 1);
                $player->setReservedCards(implode(",", $cardsReserved));
            }



            //On ajoute la carte achetée dans la main du joueur
            array_push($playerCard, $cardId);
            $player->setBuyedCards(implode(",", $playerCard));
            //On retire les ressources joker necessaire pour l'achat
            $playerTokens[5] = $playerTokens[5] - $jokerNeed;
            //Et on les ajoute au plateau
            $gameTokens[5] = $gameTokens[5] + $jokerNeed;
            $player->setListTokens(implode(",", $playerTokens));
            //On ajoute le prestige de la carte au joueur
            $prestige = $player->getPrestige() + $cardTable->getPrestige();
            $player->setPrestige($prestige);
            $this->manager->persist($player);
            $this->manager->flush();

            //On met la table splendor_game à jour
            $game->setListTokens(implode(",", $gameTokens));
            $this->manager->persist($game);
            $this->manager->flush();

            return array($newCard, implode(",", $playerTokens), $prestige, implode(",", $gameTokens));
        }

        return null;
    }

    public function getTokens($gameId, $userId, $tokens) {
        $game = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorGame')->find($gameId);
        if ($game->getIdUserTurn() != $userId) {
            return null;
        }
        $player = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
            ->findOneBy(array('gameId' => $gameId, 'idUser' => $userId));
        $gameTokens = $game->getListTokens();
        $playerTokens = $player->getListTokens();
        $nbOne = 0;
        $nbTwo = 0;
        //On verifie que le joueur a le droit de prendre ces jetons
        for ($k = 0; $k < 5; $k++) {
            //Si il n'a pas le droit on return
            if ($tokens[$k] > $gameTokens[$k] || $tokens[$k] > 2 || ($tokens[$k] == 2 && $gameTokens[$k] < 4)
                || ($tokens[$k] == 2 && ($nbTwo + $nbOne) != 0) || ($tokens[$k] == 1 && ($nbTwo != 0 || $nbOne > 2))) {
                return null;
            }
            if ($tokens[$k] == 1) {
                $nbOne++;
            }
            if ($tokens[$k] == 2) {
                $nbTwo++;
            }
            //Sinon on lui donne les jetons
            $playerTokens[$k] += $tokens[$k];
            //Et on les retire du plateau
            $gameTokens[$k] -= $tokens[$k];
        }

        $game->setListTokens(implode(",", $gameTokens));
        $this->manager->persist($game);
        $this->manager->flush();

        $player->setListTokens(implode(",", $playerTokens));
        $this->manager->persist($player);
        $this->manager->flush();

        return array(implode(",", $playerTokens), implode(",", $gameTokens));
    }


    //Retourne les ids des cartes nobles qui peuvent visiter le joueur sous forme de tableau
    public function canVisitNoble($gameId, $userId) {
        $game = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorGame')->find($gameId);
        if ($game->getIdUserTurn() != $userId) {
            return null;
        }
        $player = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
            ->findOneBy(array('gameId' => $gameId, 'idUser' => $userId));
        $nobles = $game->getIdNobles();
        $bonus = [0,0,0,0,0];
        //On calcul les bonus du joueur
        foreach ($player->getBuyedCards() as $id) {
            if ($id != 0) {
                $buyedCard = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorCard')->find($id);
                switch ($buyedCard->getBonus()) {
                    case "Green":
                        $bonus[0] += 1;
                        break;
                    case "Blue":
                        $bonus[1] += 1;
                        break;
                    case "Red":
                        $bonus[2] += 1;
                        break;
                    case "White":
                        $bonus[3] += 1;
                        break;
                    case "Black":
                        $bonus[4] += 1;
                        break;
                }
            }
        }
        $result = [];
        //Pour chaque noble sur le plateau
        foreach ($nobles as $id) {
            $cardNoble = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorCard')->find($id);
            //Si le joueur a les bonus necessaires alors on ajoute le noble dans le tableau retourné
            if ($bonus[0] >= $cardNoble->getEmeraldTokens() && $bonus[1] >= $cardNoble->getSapphireTokens()
                && $bonus[2] >= $cardNoble->getRubyTokens() && $bonus[3] >= $cardNoble->getDiamondTokens()
                && $bonus[4] >= $cardNoble->getOnyxTokens()) {
                array_push($result, $id);
            }
        }

        return $result;
    }

    public function visitNoble($gameId, $userId, $idNoble) {
        $game = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorGame')->find($gameId);
        if ($game->getIdUserTurn() != $userId) {
            return null;
        }
        $player = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
            ->findOneBy(array('gameId' => $gameId, 'idUser' => $userId));
        $gameNobles = $game->getIdNobles();
        //On retire la carte noble du plateau
        $k = array_search($idNoble, $gameNobles);
        array_splice($gameNobles, $k, 1);
        $game->setIdNobles(implode(",", $gameNobles));
        $this->manager->persist($game);
        $this->manager->flush();
        //On recupere la carte Noble correspondant à l'id
        $cardNoble = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorCard')->find($idNoble);
        //On ajoute le prestige de la carte Noble au prestige du joueur
        $prestige = $player->getPrestige() + $cardNoble->getPrestige();
        $player->setPrestige($prestige);
        $this->manager->persist($player);
        $this->manager->flush();
        return $prestige;
    }

    public function endTurn($gameId, $userId) {
        $game = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorGame')->find($gameId);
        if ($game->getIdUserTurn() != $userId) {
            return null;
        }
        $players = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
            ->findBy(array('gameId' => $gameId));
        $player = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
            ->findOneBy(array('gameId' => $gameId, 'idUser' => $userId));
        //On calcul la somme des jetons du joueur
        $total = 0;
        foreach ($player->getListTokens() as $token) {
            $total += intval($token);
        }
        //Si le joueur a plus de 10 jetons on retourne False
        if ($total > 10) {
            return array(false, implode(",", $player->getListTokens()), $total);
        }

        //Sinon on cherche quel est le prochain joueur
        for ($k = 0; $k < count($players); $k++) {
            if ($players[$k]->getIdUser()->getId() == $userId) {
                break;
            }
        }
        $newPlayer = (($k + 1) % count($players) == 0 ? $players[($k - (count($players) - 1))] : $players[$k + 1]);
        //Et on change le tour du joueur
        $game->setIdUserTurn($newPlayer->getIdUser()->getId());
        $this->manager->persist($game);
        $this->manager->flush();

        if ($player->getPrestige() >= 15) {
            $this->end = true;
            $user = $this->manager->getRepository('AGORAUserBundle:User')->find($userId);
            $this->winner = $user->getUsername();
        }

        if ($this->end && ($this->nbTurn % count($players)) == 0) {
            //FIN DE LA PARTIE
            $players = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
                ->findBy(array('gameId' => $gameId));
            foreach ($players as $player) {
                $this->manager->remove($player);
                $this->manager->flush($player);
            }
            $game = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorGame')->find($gameId);
            $this->manager->remove($game);
            $this->manager->flush($game);
            $g = $this->manager->getRepository('AGORAGameGameBundle:Game')
                ->findOneBy(array('gameId' => $gameId, 'gameInfoId' => 4));
            $this->manager->remove($g);
            $this->manager->flush($g);

            return array(true, $this->winner);
        }

        $this->nbTurn += 1;
        return array(false, $newPlayer->getIdUser()->getId());
    }

    public function removeTokens($gameId, $userId, $tokens) {
        $game = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorGame')->find($gameId);
        if ($game->getIdUserTurn() != $userId) {
            return null;
        }
        $player = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
            ->findOneBy(array('gameId' => $gameId, 'idUser' => $userId));
        $tokensPlayer = $player->getListTokens();
        for ($k = 0; $k < count($tokensPlayer); $k++) {
            $tokensPlayer[$k] = $tokens[$k];
        }
        $player->setListTokens(implode(",", $tokensPlayer));
        $this->manager->persist($player);
        $this->manager->flush();

        return implode(",", $tokensPlayer);
    }

    public function  addHiddenCard($gameId, $userId, $cardId) {
        $player = $this->manager->getRepository('AGORAGameSplendorBundle:SplendorPlayer')
            ->findOneBy(array('gameId' => $gameId, 'idUser' => $userId));
        $hide = $player->getHiddenCards();
        array_push($hide, $cardId);
        $player->setHiddenCards(implode(",", $hide));
        $this->manager->persist($player);
        $this->manager->flush();
    }



}