<?php

namespace AGORA\Game\SQPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SQPPlayer
 *
 * @ORM\Table(name="sqp_player")
 * @ORM\Entity(repositoryClass="AGORA\Game\SQPBundle\Repository\PlayerRepository")
 */
class SQPPlayer
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
     * @ORM\ManyToOne(targetEntity="AGORA\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     */
    private $idUser;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="AGORA\Game\SQPBundle\Entity\SQPGame", cascade={"persist"})
     * @ORM\JoinColumn(name="id_game", referencedColumnName="id")
     */
    private $idGame;

    /**
     * @var string
     *
     * @ORM\Column(name="hand", type="string", length=50)
     */
    private $hand;

    /**
     * @var int|null
     *
     * @ORM\Column(name="last_card_played", type="integer", nullable=true)
     */
    private $lastCardPlayed;

    /**
     * @var int
     *
     * @ORM\Column(name="score", type="integer")
     */
    private $score;

    /**
     * @var int
     *
     * @ORM\Column(name="order_turn", type="integer")
     */
    private $orderTurn;


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
     * Set hand.
     *
     * @param string $hand
     *
     * @return SQPPlayer
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
     * Set lastCardPlayed.
     *
     * @param int|null $lastCardPlayed
     *
     * @return SQPPlayer
     */
    public function setLastCardPlayed($lastCardPlayed = null)
    {
        $this->lastCardPlayed = $lastCardPlayed;

        return $this;
    }

    /**
     * Get lastCardPlayed.
     *
     * @return int|null
     */
    public function getLastCardPlayed()
    {
        return $this->lastCardPlayed;
    }

    /**
     * Set score.
     *
     * @param int $score
     *
     * @return SQPPlayer
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score.
     *
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set idUser.
     *
     * @param int $idUser
     *
     * @return SQPPlayer
     */
    public function setIdUser($idUser)
    {
        $this->idUser = $idUser;

        return $this;
    }

    /**
     * Get idUser.
     *
     * @return int
     */
    public function getIdUser()
    {
        return $this->idUser;
    }

    /**
     * Set idGame.
     *
     * @param int $idGame
     *
     * @return SQPPlayer
     */
    public function setIdGame($idGame)
    {
        $this->idGame = $idGame;

        return $this;
    }

    /**
     * Get idGame.
     *
     * @return int
     */
    public function getIdGame()
    {
        return $this->idGame;
    }
    

    /**
     * Set orderTurn.
     *
     * @param int $orderTurn
     *
     * @return SQPPlayer
     */
    public function setOrderTurn($orderTurn)
    {
        $this->orderTurn = $orderTurn;

        return $this;
    }

    /**
     * Get orderTurn.
     *
     * @return int
     */
    public function getOrderTurn()
    {
        return $this->orderTurn;
    }
}
