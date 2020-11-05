<?php

namespace AGORA\Game\SQPBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AGORA\Game\SQPBundle\Entity\SQPGame;
use AGORA\Game\SQPBundle\Entity\SQPPlayer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AGORA\PlatformBundle\Entity\Leaderboard;

class DefaultController extends Controller
{
    //Création de la partie
    public function createLobbyAction() {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé, l\'utilisateur n\'est pas connecté.');
        }

        $game = new SQPGame();
        //intialisation
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
    	$game->setNbPlayers($_POST['nbPlayers']);
        $game->setDateCrea(new \DateTime("now"));
    	$idHost = $user->getId();
    	$game->setIdHost($idHost);
    	$game->setTurn(1);
    	$private = 0;
    	if (isset($_POST['private']) && $_POST['private'] == "on") {
    	    $private = 1;
        }
    	$game->setPrivate($private);
    	$pass = "";
    	if (isset($_POST['password'])) {

            $pass = crypt($_POST['password'],password_hash($_POST['password'], PASSWORD_DEFAULT));
        }
    	$game->setPassword($pass);
    	$game->setName($_POST['lobbyName']);
    	$em = $this->getDoctrine()->getManager();
    	$em->persist($game);
    	$em->flush();
    	$player = new SQPPlayer();
        //initialisation du joueur
    	$player->setIdUser($user);
    	$player->setHand(",,,,,,,,,");
    	$player->setScore(0);
    	$player->setIdGame($game);
    	$player->setOrderTurn(0);
    	$em->persist($player);
    	$em->flush();
    	//On redirige sur l'action rejoindre la partie
    	return $this->redirect($this->generateUrl('agora_game_join_sqp' ,array(
    	    "idGame" => $game->getId()
        )));

    }

    //Permet de rejoindre une partie
    public function joinLobbyAction($idGame) {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé, l\'utilisateur n\'est pas connecté.');
        }
		
        $em = $this->getDoctrine()->getManager();

		//initialise le joueur dans le classement Elo si c'est ça première partie
        if ($em->getRepository('AGORAPlatformBundle:Leaderboard')->findOneBy(array('idPlayer' => $user->getId(), 'idGame' => 2)) == null) {
			$lb = new LeaderBoard;

            $g = $em->getRepository('AGORAPlatformBundle:GameInfo')->find(2); //6qp est le deuxieme jeu de gameinfo
            $lb -> setIdGame($g);

			$lb -> setIdPlayer($user);
			$lb -> setELO(2000);
			$lb -> setNbVic(0);
			$lb -> setNbDef(0);

			$em = $this->getDoctrine()->getManager();
			$em->persist($lb);
			$em-> flush();
		}
        
        $game = $em->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        if ($game == null) {
            throw $this->createNotFoundException("Cette partie n'existe pas !");
        }
        $player = $em->getRepository('AGORAGameSQPBundle:SQPPlayer')->findOneBy(array('idGame' => $idGame, 'idUser' => $this->getUser()->getId()));
        $playersRep = $em->getRepository('AGORAGameSQPBundle:SQPPlayer');
        $players = $playersRep->getAllPlayersFromLobby($idGame);
        $started = false;
        if (count($players) == $game->getNbPlayers()) {
            $started = true;
        }
        if ($game->getPrivate() == 1 && $game->getIdHost() != $user->getId()) {
            $pass = $_POST['pass'];
            if (!hash_equals($game->getPassword(), crypt($pass, $game->getPassword()))) {
                throw $this->createNotFoundException("Mauvais mot de passe !");
            }
        }
        //On initialise le joueur s'il n'existe pas et qu'il manque des joueurs à la partie
        if ($player == null && $started != true) {
            $player = new SQPPlayer();
            $player->setHand(",,,,,,,,,");
            $player->setScore(0);
            $player->setIdGame($game);
            $player->setIdUser($user);
            $player->setOrderTurn(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($player);
            $em->flush();
        } else if ($player == null) {//Le joueur n'est pas crée et la partie est pleine
			return $this->render('AGORAPlatformBundle:Accueil:accueil.html.twig', array(
			));
            //return $this->redirect($this->generateUrl('agora_platform_joingame'));
        }
        $players = $playersRep->getAllPlayersFromLobby($idGame);

        /*$nbPlayer = count($players);
        $maxPlayer = $game->getNbPlayers();

        if ($maxPlayer != $nbPlayer) {
            return $this->redirect($this->generateUrl('agora_platform_joingame'));
        }*/

        return $this->render('AGORAGameSQPBundle:Default:index.html.twig' ,array(
            "game" => $game,
            "player" => $player,
            "players" => $players,
            "started" => $started
        ));
    }

    //supprime une partie
    public function deleteAction($idGame) {
        $em = $this->getDoctrine()->getManager();
        $game = $em->getRepository('AGORAGameSQPBundle:SQPGame')->find($idGame);
        $players = $em->getRepository('AGORAGameSQPBundle:SQPPlayer')->findBy(array('idGame' => $idGame));
        foreach ($players as $player) {
            $em->remove($player);
            $em->flush($player);
        }
        $em->remove($game);
        $em->flush($game);

        // retpurne le page
        $playersSQPRepository = $em->getRepository('AGORAGameSQPBundle:SQPPlayer');
        $gamesSQPRepository = $em->getRepository('AGORAGameSQPBundle:SQPGame');
        $gamesSQP = $gamesSQPRepository->findAll();
        $gamesRepository = $em->getRepository('AGORAGameGameBundle:Game');
        $games = $gamesRepository->findAll();
        $usersRepository = $em->getRepository('AGORAUserBundle:User');
        $users = $usersRepository->findAll();
        $playersSQP = array();
        foreach ($gamesSQP as $game) {
            $playersSQP[''.$game->getId()] = $playersSQPRepository->getAllPlayersFromLobby($game->getId());
        }
        $players = array();
        /** @var AveCesarService $service */
        $service = $this->container->get('agora_game.ave_cesar');
        $serviceSpldr = $this->container->get('agora_game.splendor');
        foreach ($games as $game) {
            if ($game->getGameInfoId()->getGameCode() == "avc") {
                $players['avc'][''.$game->getId()] = $service->getAllPlayers($game->getId());
            }
            if ($game->getGameInfoId()->getGameCode() == "spldr") {
                $players['spldr'][''.$game->getId()] = $serviceSpldr->getAllPlayers($game->getGameId());
            }
        }

        return $this->render('AGORAPlatformBundle:Accueil:moderation.html.twig' ,array(
            "gamesSQP" => $gamesSQP,
            "users" => $users,
            "games" => $games,
            "playersSQP" => $playersSQP,
            "players" => $players
        ));

    }

    private function shuffle($var) {

        for ($i = 103; $i > 0; $i--) {
            $j = floor(random() * ($i + 1));
            $x = $var[$i];
            $var[$i] = $var[$j];
            $var[$j] = $x;
        }
        return $var;
    }
    public function getManagerExt() {
        return $this->getDoctrine()->getManager();
    }
}
