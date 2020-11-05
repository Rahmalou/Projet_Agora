<?php

namespace AGORA\Game\SplendorBundle\Controller;

use AGORA\Game\AveCesarBundle\Service\AveCesarService;
use AGORA\Game\GameBundle\Entity\Game;
use AGORA\Game\SplendorBundle\Entity\SplendorGame;
use AGORA\Game\SplendorBundle\Service\SplendorService;
use AGORA\PlatformBundle\Entity\Leaderboard;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;

class DefaultController extends Controller
{
    public function indexAction($gameId)
    {
        $user = $this->getUser();

        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé, l\'utilisateur n\'est pas connecté.');
        }

        $service = $this->container->get('agora_game.splendor');
        $game = $service->getGame($gameId);
        $players = $service->getAllPlayers($gameId);

        return $this->render('AGORAGameSplendorBundle:Default:index.html.twig', array(
            'user' => $user,
            'game' => $game,
            'players' => $players
        ));
    }

    public function createAction() {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé, l\'utilisateur n\'est pas connecté.');
        }

        $private = 0;
        $password = "";

        if (isset($_POST['private']) && isset($_POST['password']) && $_POST['password']!="" && $_POST['private'] == "on") {
            $private = 1;
            $password = $_POST['password'];

        }

        //Recuperation du service
        //Creation de la partie à partir du service
        $service = $this->container->get('agora_game.splendor');
        $gameId = $service->createGame($_POST['lobbyName'], $_POST['nbPlayers'], $private, $password, $user->getId());
//        $service->createCards($gameId);

        return $this->redirect($this->generateUrl('agora_game_join_spldr', array('gameId' => $gameId)));

    }

    public function joinAction($gameId) {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé, l\'utilisateur n\'est pas connecté.');
        }

        ///initialise le joueur dans le classement Elo si c'est ça première partie
        $em = $this->getDoctrine()->getManager();
        if ($em->getRepository('AGORAPlatformBundle:Leaderboard')->findOneBy(array('idPlayer' => $user->getId(), 'idGame' => 4)) == null) {
            $lb = new Leaderboard;

            $g = $em->getRepository('AGORAPlatformBundle:GameInfo')->find(4); //splendor est le quatrieme jeu de gameinfo
            $lb -> setIdGame($g);

            $lb -> setIdPlayer($user);
            $lb -> setELO(2000);
            $lb -> setNbVic(0);
            $lb -> setNbDef(0);

            $em->persist($lb);
            $em->flush();
        }

        $nbPlayers = $em->getRepository('AGORAGameGameBundle:Game')->findOneBy(array('gameId' => $gameId, 'gameInfoId' => 4))->getNbPlayers();
        $players = $em->getRepository('AGORAGameSplendorBundle:SplendorPlayer')->findBy(array('gameId' => $gameId));
        $service = $this->container->get('agora_game.splendor');
        $plr = $em->getRepository('AGORAGameSplendorBundle:SplendorPlayer')->findBy(array('gameId' => $gameId, 'idUser' => $user->getId()));


        if (count($players) < $nbPlayers && $user && $plr == null) {
            $service->createPlayer($gameId, $user);
            if(count($players) + 1 == $nbPlayers) {
                $game = $em->getRepository('AGORAGameGameBundle:Game')->findOneBy(array('gameId' => $gameId, 'gameInfoId' => 4));
                $game->setState("started");
                $em->persist($game);
                $em->flush();
                return $this->redirect($this->generateUrl('agora_game_splendor_homepage', array('gameId' => $gameId)));
            }
            return $this->redirect($this->generateUrl('agora_platform_joingame'));
        }

        return $this->redirect($this->generateUrl('agora_platform_joingame'));


    }

    public function deleteAction($gameId) {
        $em = $this->getDoctrine()->getManager();
        $game = $em->getRepository('AGORAGameSplendorBundle:SplendorGame')->find($gameId);
        $players = $em->getRepository('AGORAGameSplendorBundle:SplendorPlayer')->findBy(array('gameId' => $gameId));
        foreach ($players as $player) {
            $em->remove($player);
            $em->flush($player);
        }

//        $cards = $em->getRepository('AGORAGameSplendorBundle:SplendorCard')->findBy(array('gameId' => $gameId));
//        foreach ($cards as $card) {
//            $em->remove($card);
//            $em->flush($card);
//        }

        $g = $em->getRepository('AGORAGameGameBundle:Game')->findOneBy(array('gameId' => $gameId, 'gameInfoId' => 4));
        $em->remove($g);
        $em->flush($g);

        $em->remove($game);
        $em->flush($game);

        // retourne le page
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
                $players['spldr'][''.$game->getId()] = $serviceSpldr->getAllPlayers($gameId);
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
