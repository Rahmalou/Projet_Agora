<?php

namespace AGORA\Game\AveCesarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AveCesarPlayer
 *
 * @ORM\Table(name="ave_cesar_player")
 * @ORM\Entity(repositoryClass="AGORA\Game\AveCesarBundle\Repository\AveCesarPlayerRepository")
 */
class AveCesarPlayer
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
    private $game_id;

    /**
     * @var string
     *
     * @ORM\Column(name="hand", type="string", length=255)
     */
    private $hand;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=255)
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="lap", type="integer")
     */
    private $lap;

    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="AGORA\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     */
    private $user_id;

    /**
     * @var bool
     *
     * @ORM\Column(name="cesar", type="boolean")
     */
    private $cesar;

    /**
     * @var int
     *
     * @ORM\Column(name="finish", type="integer")
     */
    private $finish;

    /**
     * @var string
     *
     * @ORM\Column(name="deck", type="string", length=255)
     */
    private $deck;

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
     * @param int $game_id
     *
     * @return AveCesarPlayer
     */
    public function setGameId($game_id)
    {
        $this->game_id = $game_id;

        return $this;
    }

    /**
     * Get gameId.
     *
     * @return int
     */
    public function getGameId()
    {
        return $this->game_id;
    }

    /**
     * Set hand.
     *
     * @param string $hand
     *
     * @return AveCesarPlayer
     */
    public function setHand($hand)
    {
        $this->hand = $hand;

        return $this;
    }

    /**
     * Get hand.
     *
     * @return string
     */
    public function getHand()
    {
        return $this->hand;
    }

    /**
     * Set position.
     *
     * @param string $position
     *
     * @return AveCesarPlayer
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set lap.
     *
     * @param int $lap
     *
     * @return AveCesarPlayer
     */
    public function setLap($lap)
    {
        $this->lap = $lap;

        return $this;
    }

    /**
     * Get lap.
     *
     * @return int
     */
    public function getLap()
    {
        return $this->lap;
    }

    /**
     * Set idUser.
     *
     * @param int $user_id
     *
     * @return AveCesarPlayer
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get idUser.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set cesar.
     *
     * @param bool $cesar
     *
     * @return AveCesarPlayer
     */
    public function setCesar($cesar)
    {
        $this->cesar = $cesar;

        return $this;
    }

    /**
     * Get cesar.
     *
     * @return bool
     */
    public function getCesar()
    {
        return $this->cesar;
    }

    /**
     * Set deck.
     *
     * @param string $deck
     *
     * @return AveCesarPlayer
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
     * Set finish.
     *
     * @param int $finish
     *
     */
    public function setFinish($finish)
    {
        $this->finish = $finish;

    }

    /**
     * Get finish.
     *
     * @return int
     */
    public function getFinish()
    {
        return $this->finish;
    }

}
