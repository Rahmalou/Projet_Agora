<?php

namespace AGORA\PlatformBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AGORA\PlatformBundle\Entity\Contact;
use AGORA\PlatformBundle\Form\ContactType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use FOS\UserBundle\Model\UserInterface;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $gameInfoRepository = $em->getRepository('AGORAPlatformBundle:GameInfo');

        $allGameInfo = $gameInfoRepository->findAll();

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

        $user = $this->getUser();
        if ($user != null && $user->hasRole('ROLE_ADMIN')) {
            return $this->render('AGORAPlatformBundle:Accueil:moderation.html.twig', array(
                "gamesSQP" => $gamesSQP,
                "users" => $users,
                "games" => $games,
                "playersSQP" => $playersSQP,
                "players" => $players));
        }

        return $this->render('AGORAPlatformBundle:Accueil:accueil.html.twig', array(
            "gameList" => $allGameInfo));
    }

    public function theProjectAction()
    {
        return $this->render('AGORAPlatformBundle:Accueil:theProject.html.twig');
    }

    public function contactAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $gameInfoRepository = $em->getRepository('AGORAPlatformBundle:GameInfo');
        $allGameInfo = $gameInfoRepository->findAll();
        $Contact = new Contact();
        $form = $this->createForm(ContactType::class, $Contact);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $message = \Swift_Message::newInstance()
                ->setSubject('Demande de contact')
                ->setFrom('agora.dev.test@gmail.com')   
                ->setTo('agora.dev.test@gmail.com')   //adresse qui receptionnera la demande de contact
                ->setBody($this->renderView('AGORAPlatformBundle:mail:contactEmail.txt.twig', array('contact' => $Contact)));
                $this->get('mailer')->send($message);
                $this->addFlash('contact-notice', 'Votre demande de contact a été correctement envoyé. Merci !');
                return $this->redirect($this->generateUrl('agora_platform_contact'));
            }
        }
        return $this->render('AGORAPlatformBundle:Accueil:contact.html.twig',
            array('form' => $form->createView(),
                'gameList' => $allGameInfo)
        );
    }

    public function leaderboardAction($game = null) {
        $em = $this->getDoctrine()->getManager();
        $leaderboardRepository = $em->getRepository('AGORAPlatformBundle:Leaderboard');
        $gameInfoRepository = $em->getRepository('AGORAPlatformBundle:GameInfo');
        $allGameInfo = $gameInfoRepository->findAll();
        if ($game == "*") {
            return $this->render('AGORAPlatformBundle:Accueil:listLeaderboard.html.twig',array(
                "gameList" => $allGameInfo));
        }
        
        $gameInfo = $gameInfoRepository->find($game);
        $leaderboard = $leaderboardRepository->getLeaderboardWithUser($game);
        $users = $em->getRepository('AGORAUserBundle:User')->findAll();

        return $this->render('AGORAPlatformBundle:Accueil:leaderboard.html.twig',
            array(
				"users" => $users,
                "gameInfo" => $gameInfo,
                "leaderboard" => $leaderboard,
                "gameList" => $allGameInfo));

    }

    public function setAdminAction() {
        $user = $this->getUser();
        $user->addRole('ROLE_ADMIN');

        $em = $this->getDoctrine()->getEntityManager();

        $em->persist($user);
        $em->flush();

        return $this->render('AGORAPlatformBundle:Accueil:acceuil.html.twig');

    }
    
    public function gameListAction($game = null) {
        $em = $this->getDoctrine()->getManager();
        $gameInfoRepository = $em->getRepository('AGORAPlatformBundle:GameInfo');

        if (isset($game) && $game != "*") {
            $gameInfo = $gameInfoRepository->find($game);

            if ($gameInfo == null) {
                throw $this->createNotFoundException('La page demandée n\'existe pas ! ');
            }
            return $this->render('AGORAPlatformBundle:Accueil:gameDetails.html.twig',array(
                "gameInfo" => $gameInfo));   
        } else {
            $allGameInfo = $gameInfoRepository->findAll();
            return $this->render('AGORAPlatformBundle:Game:gameList.html.twig',array(
                "gameList" => $allGameInfo));
        }

	}

    public function gameListCreateAction($game = null) {
        $em = $this->getDoctrine()->getManager();
        $gameInfoRepository = $em->getRepository('AGORAPlatformBundle:GameInfo');

        if (isset($game) && $game != "*") {
            $gameInfo = $gameInfoRepository->find($game);

            if ($gameInfo == null) {
                throw $this->createNotFoundException('La page demandée n\'existe pas ! ');
            }
            return $this->render('AGORAPlatformBundle:Accueil:gameDetails.html.twig',array(
                "gameInfo" => $gameInfo));
        } else {
            $allGameInfo = $gameInfoRepository->findAll();
            return $this->render('AGORAPlatformBundle:Game:gameListCreate.html.twig',array(
                "gameList" => $allGameInfo));


        }

    }

    public function profileAction() {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $gameInfoRepository = $em->getRepository('AGORAPlatformBundle:GameInfo');
        $allGameInfo = $gameInfoRepository->findAll();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('AGORAPlatformBundle:Profile:profile.html.twig', array(
            'user' => $user,
            'gameList' => $allGameInfo
        ));

    }


    public function createGameAction($gameId = null) {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé, l\'utilisateur n\'est pas connecté.');
        }
        $em = $this->getDoctrine()->getManager();
        $gameInfoRepository = $em->getRepository('AGORAPlatformBundle:GameInfo');
        $gameInfo = null;
        if (isset($gameId) && $gameId != "*") {
            $gameInfo = $gameInfoRepository->find($gameId);
            if ($gameInfo == null) {
                throw $this->createNotFoundException('La page demandée n\'existe pas ! ');
            }
        } else {
            throw $this->createNotFoundException('La page demandée n\'existe pas ! ');
        }
        $allGameInfo = $gameInfoRepository->findAll();
        return $this->render('AGORAPlatformBundle:Accueil:createGame.html.twig', array(
            "gameInfo" => $gameInfo,
            "user" => $user,
            "gameList" => $allGameInfo
        ));
    }
    
    public function joinGameAction() {
		$em = $this->getDoctrine()->getManager();
        $playersSQPRepository = $em->getRepository('AGORAGameSQPBundle:SQPPlayer');
        $gamesSQPRepository = $em->getRepository('AGORAGameSQPBundle:SQPGame');
        $gamesSQP = $gamesSQPRepository->findAll();
        $gamesRepository = $em->getRepository('AGORAGameGameBundle:Game');
        $games = $gamesRepository->findAll();
        $gameInfoRepository = $em->getRepository('AGORAPlatformBundle:GameInfo');
        $allGameInfo = $gameInfoRepository->findAll();
        $usersRepository = $em->getRepository('AGORAUserBundle:User');
        $users = $usersRepository->findAll();
        $playersSQP = array();
        foreach ($gamesSQP as $game) {
            $playersSQP[''.$game->getId()] = $playersSQPRepository->getAllPlayersFromLobby($game->getId());
        }
        $players = array();
        /** @var AveCesarService $service */
        $serviceSpldr = $this->container->get('agora_game.splendor');



        $gameServices['avc'] = $this->container->get('agora_game.ave_cesar');
        $gameServices['aug'] = $this->container->get('agora_game.augustus');
        foreach ($games as $game) {
            foreach ($gameServices as $code => $service) {
                if ($game->getGameInfoId()->getGameCode() == $code) {
                    $players[$code][''.$game->getId()] = $service->getPlayers($game->getGameId());
                    break;
                }
            }
            if ($game->getGameInfoId()->getGameCode() == "spldr") {
                $players['spldr'][''.$game->getId()] = $serviceSpldr->getAllPlayers($game->getGameId());
            }
        }


		return $this->render('AGORAPlatformBundle:Accueil:joinGame.html.twig' ,array(
			"gamesSQP" => $gamesSQP,
			"users" => $users,
            "games" => $games,
            "playersSQP" => $playersSQP,
            "players" => $players,
            "gameList" => $allGameInfo
		));
	}

    public function moderationAction()
    {
        $em = $this->getDoctrine()->getManager();
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


    public function playerPartiesAction($userId) {
        $em = $this->getDoctrine()->getManager();
        $playersSQPRepository = $em->getRepository('AGORAGameSQPBundle:SQPPlayer');
        $gamesSQPRepository = $em->getRepository('AGORAGameSQPBundle:SQPGame');
        $gamesSQP = $gamesSQPRepository->findAll();
        $gamesRepository = $em->getRepository('AGORAGameGameBundle:Game');
        $games = $gamesRepository->findAll();
        $gameInfoRepository = $em->getRepository('AGORAPlatformBundle:GameInfo');
        $allGameInfo = $gameInfoRepository->findAll();
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

        return $this->render('AGORAPlatformBundle:Accueil:playerParties.html.twig' ,array(
            "gamesSQP" => $gamesSQP,
            "users" => $users,
            "games" => $games,
            "playersSQP" => $playersSQP,
            "players" => $players,
            "userId" => $userId,
            "gameList" => $allGameInfo
        ));
    }
}
