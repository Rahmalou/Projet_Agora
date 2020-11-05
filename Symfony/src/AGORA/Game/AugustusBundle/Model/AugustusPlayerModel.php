<?php

namespace AGORA\Game\AugustusBundle\Model;

use AGORA\Game\AugustusBundle\Entity\AugustusGame;
use AGORA\Game\AugustusBundle\Entity\AugustusPlayer;
use AGORA\Game\AugustusBundle\Entity\AugustusCard;
use AGORA\Game\AugustusBundle\Entity\AugustusToken;
use AGORA\Game\AugustusBundle\Entity\AugustusPower;
use AGORA\Game\AugustusBundle\Entity\AugustusColor;

use AGORA\Game\GameBundle\Entity\Game;
use Doctrine\ORM\EntityManager;

//Fonction agissant sur AugustusPlayer
class AugustusPlayerModel {

    protected $manager;
    private $cardModel;
    
    //On construit notre api avec un entity manager permettant l'accès à la base de données
    public function __construct(EntityManager $em) {
        $this->manager = $em;

        $this->cardModel = new AugustusCardModel($em);
    }

    public function createPlayer($userId, $gameId) {
        $augGame = $this->manager->getRepository('AugustusBundle:AugustusGame')->find($gameId);
        if ($augGame == null) {
            throw new \Exception();
        }

        $game = $this->manager->getRepository('AGORAGameGameBundle:Game')
            ->findOneBy(array('gameId' => $gameId));

        $players = $this->manager->getRepository('AugustusBundle:AugustusPlayer')
            ->findBy(array('game' => $gameId));

        $nbPlayer = count($players);
        if ($nbPlayer >= $game->getNbPlayers()) {
            return -1;
        }

        $player = new AugustusPlayer();
        $player->setGame($augGame);

        $user = $this->manager->getRepository('AGORAUserBundle:User')
            ->findOneBy(array('id' => $userId));
        
        $player->setUserId($user->getId());
        $player->setUserName($user->getUserName());

        $this->manager->persist($player);
        $this->manager->flush();

        $this->manager->flush();
        return $player->getId();
    }

    //Mets une légion sur la carte et retire le jeton de la carte correspondant au jeton du tour en cours.
    public function putLegionOnCard($idPlayer, $idCard, $token) {
        $players = $this->manager->getRepository('AugustusBundle:AugustusPlayer');
        $cards = $this->manager->getRepository('AugustusBundle:AugustusCard');

        $player = $players->findOneById($idPlayer);
        $card = $cards->findOneById($idCard);
        $this->cardModel->captureToken($idCard, $token);
        
        $player->setLegion($player->getLegion() - 1);

        $this->manager->flush();
    }

    //Enleve une légion sur la carte et ajoute le jeton de la carte correspondant au jeton du tour en cours.
    public function removeLegionFromCard($idPlayer, $idCard, $token) {
        $players = $this->manager->getRepository('AugustusBundle:AugustusPlayer');
        $cards = $this->manager->getRepository('AugustusBundle:AugustusCard');

        $player = $players->findOneById($idPlayer);
        $card = $cards->findOneById($idCard);

        $this->cardModel->getBackToken($idCard, $token);

        $player->setLegion($player->getLegion() + 1);
        
        $this->manager->flush();
    }

    //Mets une legion de la carte Source à la carte dest.
    public function putLegionFromSourceToDest($idPlayer, $idCardSource, $idCardDest, $tokenSource, $tokenDest) {
        $players = $this->manager->getRepository('AugustusBundle:AugustusPlayer');
        $cards = $this->manager->getRepository('AugustusBundle:AugustusCard');

        $player = $players->findOneById($idPlayer);

        $this->cardModel->getBackToken($idCardSource, $tokenSource);
        $this->cardModel->captureToken($idCardDest, $tokenDest);
        
        $this->manager->flush();
    }


    //La carte d'id idCard passe de currObj à ctrlObj si tout les tokens de la cartes sont contrôlés.
    public function captureCard($idPlayer, $idCard) {
        $players = $this->manager->getRepository('AugustusBundle:AugustusPlayer');
        $cards = $this->manager->getRepository('AugustusBundle:AugustusCard');

        $player = $players->findOneById($idPlayer);
        $card = $cards->findOneById($idCard);

        switch($card->getResource()) {
            case "wheat": 
                $player->setWheat($player->getWheat() + 1);
                break;
            case "gold": 
                $player->setGold($player->getGold() + 1);
                break;
            case "both": 
                $player->setWheat($player->getWheat() + 1);
                $player->setGold($player->getGold() + 1);
                break;
        }
        $player->setLegion($player->getLegion() + count($card->getTokens()));
        $player->addCtrlCard($card);
        $player->removeCard($card);
        $this->manager->flush();
    }

    public function deleteCtrlCard($idPlayer) {
        $players = $this->manager->getRepository('AugustusBundle:AugustusPlayer');

        $player = $players->findOneById($idPlayer);

        $ctrlCards = $player->getCtrlCards()->toArray();
        shuffle($ctrlCards);
        $card = array_pop($ctrlCards);
        if ($card) {
            $card->setPlayerCtrl(null);
        }

        $this->manager->flush();
    }
    
    public function completeCard($idCard) {
        $cards = $this->manager->getRepository('AugustusBundle:AugustusCard');

        $card = $cards->findOneById($idCard);
        $tok = $card->getTokens();
        foreach($tok as $t) {
            $this->cardModel->captureToken($idCard, $t);
        }

        $this->manager->flush();
    }

    public function getCardByNumber($idPlayer, $number) {
        $players = $this->manager->getRepository('AugustusBundle:AugustusPlayer');

        $player = $players->findOneById($idPlayer);

        $cards = $player->getCards();

        foreach($cards as $c) {
            if ($c != null && $c->getNumber() == $number) {
                return $c;
            }
        }

        return null;
    }

    public function getNbOfCardColor($idPlayer, $color) {
        $players = $this->manager->getRepository('AugustusBundle:AugustusPlayer');

        $player = $players->findOneById($idPlayer);

        $ctrlCards = $player->getCtrlCards();

        $res = 0;

        foreach($ctrlCards as $c) {
            if ($c -> getColor() == $color) {
                $res = $res + 1;
            }
        }

        return $res;
    }

    public function getNbOfToken($idPlayer, $token) {
        $players = $this->manager->getRepository('AugustusBundle:AugustusPlayer');

        $player = $players->findOneById($idPlayer);

        $ctrlCards = $player->getCtrlCards();

        $res = 0;

        foreach($ctrlCards as $c) {
            $tokens = $c->getTokens();
            foreach($tokens as $t) {
                if ($t == $token) {
                    $res = $res + 1;
                }
            }
        }
        return $res;
    }

    public function haveOneCardOfEach($idPlayer) {
        $players = $this->manager->getRepository('AugustusBundle:AugustusPlayer');

        $player = $players->findOneById($idPlayer);

        $ctrlCards = $player->getCtrlCards();

        $pink = false;
        $green = false;
        $senator = false;
        $orange = false;

        foreach($ctrlCards as $c) {
            switch($c -> getColor()) {
                case AugustusColor::PINK : {
                    $pink = true;
                    break;
                }
                case AugustusColor::GREEN : {
                    $green = true;
                    break;
                }
                case AugustusColor::ORANGE : {
                    $orange = true;
                    break;
                }
                case AugustusColor::SENATOR : {
                    $senator = true;
                    break;
                }
            }
        }
        return $pink && $green && $senator && $orange;
    }

    public function getNbOfRedPower($idPlayer) {
        $players = $this->manager->getRepository('AugustusBundle:AugustusPlayer');

        $player = $players->findOneById($idPlayer);

        $ctrlCards = $player->getCtrlCards();

        $res = 0;

        foreach($ctrlCards as $c) {
            switch($c -> getPower()) {
                case AugustusPower::REMOVEONELEGION : {
                    $res = $res + 1;
                    break;
                }
                case AugustusPower::REMOVETWOLEGION : {
                    $res = $res + 1;                    
                    break;
                }
                case AugustusPower::REMOVEALLLEGION : {
                    $res = $res + 1;                    
                    break;
                }
                case AugustusPower::REMOVEONECARD : {
                    $res = $res + 1;
                    break;
                }
            }
        }
        return $res;
    }
}

