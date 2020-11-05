<?php

namespace AGORA\Game\AveCesarBundle\Controller;

use AGORA\Game\AveCesarBundle\Service\AveCesarService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use AGORA\PlatformBundle\Entity\Leaderboard;

class GameController extends Controller
{
    public function indexAction($gameId) {

        $user = $this->getUser();

        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé, l\'utilisateur n\'est pas connecté.');
        }

        /** @var AveCesarService $service */
        $service = $this->container->get('agora_game.ave_cesar');
        $avcGame = $service->getGame($gameId);
        $player = $service->getPlayerFromUserId($gameId, $user->getId());
        $players = $service->getAllPlayers($gameId);
        $gameName = $service->getGameName($gameId);
        $firstPlayer = $service->getFirstPlayer($gameId);
        $nextPlayer = $service->getNextPlayer($gameId);
        //$service->initPlayers($gameId);

        return $this->render('AGORAGameAveCesarBundle:Default:game.html.twig',
            array(
                'user' => $user,
                'game' => $avcGame,
                'player' => $player,
                'players' => $players,
                'gameName' => $gameName,
                'firstPlayer' => $firstPlayer,
                'nextPlayer' => $nextPlayer
            )
        );
    }

    //Création de la partie
    public function createLobbyAction() {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé, l\'utilisateur n\'est pas connecté.');
        }
        $service = $this->container->get('agora_game.ave_cesar');

        $private = 0;
        $password = "";

        if (isset($_POST['private']) && isset($_POST['password']) && $_POST['password']!="" && $_POST['private'] == "on") {
            $private = 1;
            $password = $_POST['password'];

        }

        $gameId = $service->createRoom($_POST['lobbyName'], $_POST['nbPlayers'], $private, $password, $user->getId());

        return $this->redirect($this->generateUrl('agora_game_join_avc' ,array(
            "gameId" => $gameId
        )));
    }

    public function joinLobbyAction(SessionInterface $session, $gameId) {
        //echo "Un autre game id : ".$gameId."\n";
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé, l\'utilisateur n\'est pas connecté.');
        }

        ///initialise le joueur dans le classement Elo si c'est ça première partie
        $em = $this->getDoctrine()->getManager();
        if ($em->getRepository('AGORAPlatformBundle:Leaderboard')->findOneBy(array('idPlayer' => $user->getId(), 'idGame' => 1)) == null) {
            $lb = new Leaderboard;

            $g = $em->getRepository('AGORAPlatformBundle:GameInfo')->find(1); //avecesar est le premier jeu de gameinfo
            $lb -> setIdGame($g);

            $lb -> setIdPlayer($user);
            $lb -> setELO(2000);
            $lb -> setNbVic(0);
            $lb -> setNbDef(0);

            $em->persist($lb);
            $em->flush();
        }

        $service = $this->container->get('agora_game.ave_cesar');

        if (!$service->playerAlreadyCreated($gameId, $user->getId())) {
            $result = $service->createPlayer($user, $gameId);
        } else {
            return $this->redirect($this->generateUrl('agora_game_ave_cesar_homepage' ,array(
                "gameId" => $gameId
            )));
        }

        // Game Full
        if ($result == -1) {
            $this->redirect($this->generateUrl('agora_platform_joingame'));
        }

        return $this->redirect($this->generateUrl('agora_game_ave_cesar_homepage' ,array(
            "gameId" => $gameId
        )));
    }

    //supprime une partie
    public function deleteAction($idGame) {
        $em = $this->getDoctrine()->getManager();
        $game = $em->getRepository('AGORAGameAveCesarBundle:AveCesarGame')->find($idGame);
        $players = $em->getRepository('AGORAGameAveCesarBundle:AveCesarPlayer')->findBy(array('game_id' => $idGame));
        foreach ($players as $player) {
            $em->remove($player);
            $em->flush($player);
        }

        $g = $em->getRepository('AGORAGameGameBundle:Game')->findOneBy(array('gameId' => $idGame, 'gameInfoId' => 1));
        $em->remove($g);
        $em->flush($g);

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
        foreach ($games as $game) {
            if ($game->getGameInfoId()->getGameCode() == "avc") {
                $players['avc'][''.$game->getId()] = $service->getAllPlayers($game->getId());
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
}
