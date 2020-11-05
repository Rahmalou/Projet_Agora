<?php

namespace AGORA\Game\SQPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SQPGame
 *
 * @ORM\Table(name="sqp_game")
 * @ORM\Entity(repositoryClass="AGORA\Game\SQPBundle\Repository\GameRepository")
 */
class SQPGame
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="deck", type="string", length=500)
     */
    private $deck;

    /**
     * @var string
     *
     * @ORM\Column(name="board", type="string", length=128)
     */
    private $board;

    /**
     * @var int
     *
     * @ORM\Column(name="id_host", type="integer")
     */
    private $idHost;

    /**
     * @var int
     *
     * @ORM\Column(name="nbPlayers", type="integer")
     */
    private $nbPlayers;

    /**
     * @var int
     *
     * @ORM\Column(name="private", type="integer")
     */
    private $private;

    /**
     * @var int
     *
     * @ORM\Column(name="turn", type="integer")
     */
    private $turn;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=128)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128)
     */
    private $name;

    /**
     * @var datetime
     *
     * @ORM\Column(name="dateCrea", type="datetime", nullable=true)
     */
    private $dateCrea;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set deck.
     *
     * @param string $deck
     *
     * @return SQPGame
     */
    public function setDeck($deck)
    {
        $this->deck = $deck;

        return $this;
    }

    /**
     * Get deck.
     *
     * @return string
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * Set board.
     *
     * @param string $board
     *
     * @return SQPGame
     */
    public function setBoard($board)
    {
        $this->board = $board;

        return $this;
    }

    /**
     * Get board.
     *
     * @return string
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Set idHost.
     *
     * @param int $idHost
     *
     * @return SQPGame
     */
    public function setIdHost($idHost)
    {
        $this->idHost = $idHost;

        return $this;
    }

    /**
     * Get idHost.
     *
     * @return int
     */
    public function getIdHost()
    {
        return $this->idHost;
    }

    /**
     * Set nbPlayers.
     *
     * @param int $nbPlayers
     *
     * @return SQPGame
     */
    public function setNbPlayers($nbPlayers)
    {
        $this->nbPlayers = $nbPlayers;

        return $this;
    }

    /**
     * Get nbPlayers.
     *
     * @return int
     */
    public function getNbPlayers()
    {
        return $this->nbPlayers;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return SQPGame
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set private.
     *
     * @param int $private
     *
     * @return SQPGame
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get private.
     *
     * @return int
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return SQPGame
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set turn.
     *
     * @param int $turn
     *
     * @return SQPGame
     */
    public function setTurn($turn)
    {
        $this->turn = $turn;

        return $this;
    }

    /**
     * Get turn.
     *
     * @return int
     */
    public function getTurn()
    {
        return $this->turn;
    }

    /**
     * Set dateCrea.
     *
     * @param \DateTime $dateCrea
     *
     * @return SQPGame
     */
    public function setDateCrea($dateCrea)
    {
        $this->dateCrea = $dateCrea;

        return $this;
    }

    /**
     * Get dateCrea.
     *
     * @return \DateTime
     */
    public function getDateCrea()
    {
        return $this->dateCrea;
    }
}
