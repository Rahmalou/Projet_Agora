<?php

namespace AGORA\Game\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Game
 *
 * @ORM\Table(name="game")
 * @ORM\Entity(repositoryClass="AGORA\Game\GameBundle\Repository\GameRepository")
 */
class Game
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
     * @var int
     *
     * @ORM\Column(name="game_id", type="integer")
     */
    private $gameId;


    // ATTENTION ceci n'est pas un int, mais un objet de type GameInfo (et pas juste l'id)
    // Bravo agora7...
    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="AGORA\PlatformBundle\Entity\GameInfo", cascade={"persist"})
     * @ORM\JoinColumn(name="game_info_id", referencedColumnName="id")
     */
    private $gameInfoId;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_players", type="integer")
     */
    private $nbPlayers;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255)
     */
    private $state;

    /**
     * @var bool
     *
     * @ORM\Column(name="private", type="boolean")
     */
    private $private;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var datetime
     *
     * @ORM\Column(name="dateCrea", type="datetime", nullable=true)
     */
    private $dateCrea;

    /**
     * @var int
     *
     * @ORM\Column(name="id_host", type="integer")
     */
    private $idHost;
    
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
     * Set gameId.
     *
     * @param int $gameId
     *
     * @return Game
     */
    public function setGameId($gameId)
    {
        $this->gameId = $gameId;

        return $this;
    }

    /**
     * Get gameId.
     *
     * @return int
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * Set gameInfoId.
     *
     * @param int $gameInfoId
     *
     * @return Game
     */
    public function setGameInfoId($gameInfoId)
    {
        $this->gameInfoId = $gameInfoId;

        return $this;
    }

    /**
     * Get gameInfoId.
     *
     * @return int
     */
    public function getGameInfoId()
    {
        return $this->gameInfoId;
    }

    /**
     * Set nbPlayers.
     *
     * @param int $nbPlayers
     *
     * @return Game
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
     * Set state.
     *
     * @param string $state
     *
     * @return Game
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set private.
     *
     * @param bool $private
     *
     * @return Game
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get private.
     *
     * @return bool
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
     * @return Game
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
     * Set name.
     *
     * @param string $name
     *
     * @return Game
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
     * Set dateCrea.
     *
     * @param \DateTime|null $dateCrea
     *
     * @return Game
     */
    public function setDateCrea($dateCrea = null)
    {
        $this->dateCrea = $dateCrea;

        return $this;
    }

    /**
     * Get dateCrea.
     *
     * @return \DateTime|null
     */
    public function getDateCrea()
    {
        return $this->dateCrea;
    }

    /**
     * Set idHost.
     *
     * @param int $idHost
     *
     * @return Game
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
}
