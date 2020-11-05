<?php


namespace AGORA\Game\SQPBundle\Tests\Model;
use AGORA\Game\GameBundle\Entity\Game;
use AGORA\Game\SQPBundle\Model\SQPAPI;
use AGORA\UserBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class SQPAPITest extends TestCase {
    public function testSupressGame() {
        //GIVEN
        $sqpapi = new SQPAPI();
        $game = $this->newGame();
        $player = $this->newPlayer($game);
        $this->manager->persist($game);
        $this->manager->flush();
        $this->manager->persist($player);
        $this->manager->flush();
        //WHEN
       $sqpapi->supressGame($game->getId());
        //THEN
        $game = $this->manager->getRepository('AGORAGameSQPBundle:SQPGame')->find($game->getId());
        $players = $this->manager->getRepository('AGORAGameSQPBundle:SQPPlayer')->findBy(array('idGame' => $game->getId()));
        $this->assert(0, count($players));
        $this->assert(null, $game);
        /*$gameRepository = $this->createMock(ObjectRepository::class);
        $gameRepository->expects($this->any())
            ->method('find')
            ->willReturn($game);

        $playerRepository = $this->createMock(ObjectRepository::class);
        $playerRepository->expects($this->any())
            ->method('find')
            ->willReturn($player);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($gameRepository);
        */
    }

    public function testNbBeef54() {
        //GIVEN
        $card = 54;
        $entityManager = $this->createMock(EntityManager::class);
        $sqpapi = new SQPAPI($entityManager);
        //WHEN
        $nb = $sqpapi->getNbBeef($card);
        //THEN
        $this->assertEquals(1, $nb);
    }

    public function nbBeefTest55() {
        //GIVEN
        $card = 55;
    }

    public function nbBeefTest11() {
        //GIVEN
        $card = 11;
    }

    public function nbBeefTest45() {
        //GIVEN
        $card = 45;
    }

    public function nbBeefTest10() {
        //GIVEN
        $card = 10;
    }

    private function newGame() {
        $game = new SQPGame();
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
        $game->setNbPlayers(2);
        $game->setDateCrea(new \DateTime("now"));
        $idHost = $this->newUser();
        $game->setIdHost($idHost);
        $game->setTurn(1);
        $private = 0;
        $game->setPrivate($private);
        $pass = "";
        $game->setPassword($pass);
        $game->setName("name");
        return $game;
    }
    private function newPlayer($game) {
        $player = new SQPPlayer();
        //initialisation du joueur
        $player->setIdUser($game->getIdHost());
        $player->setHand(",,,,,,,,,");
        $player->setScore(0);
        $player->setIdGame($game);
        $player->setOrderTurn(0);
        return $player;
    }

    private function newUser() {
        $user = new User();
        return $user;
    }


}
