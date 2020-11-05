<?php

namespace AGORA\Game\SQPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SQPLeaderBoard
 *
 * @ORM\Table(name="s_q_p_leader_board")
 * @ORM\Entity(repositoryClass="AGORA\Game\SQPBundle\Repository\SQPLeaderBoardRepository")
 */
class SQPLeaderBoard
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
     * @ORM\OneToOne(targetEntity="AGORA\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="id_player", referencedColumnName="id")
     */
    private $idPlayer;

    /**
     * @var int
     *
     * @ORM\Column(name="ELO", type="integer")
     */
    private $eLO;

    /**
     * @var int
     *
     * @ORM\Column(name="nbVic", type="integer")
     */
    private $nbVic;

    /**
     * @var int
     *
     * @ORM\Column(name="nbDef", type="integer")
     */
    private $nbDef;


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
     * Set idPlayer.
     *
     * @param int $idPlayer
     *
     * @return SQPLeaderBoard
     */
    public function setIdPlayer($idPlayer)
    {
        $this->idPlayer = $idPlayer;

        return $this;
    }

    /**
     * Get idPlayer.
     *
     * @return int
     */
    public function getIdPlayer()
    {
        return $this->idPlayer;
    }

    /**
     * Set eLO.
     *
     * @param int $eLO
     *
     * @return SQPLeaderBoard
     */
    public function setELO($eLO)
    {
        $this->eLO = $eLO;

        return $this;
    }

    /**
     * Get eLO.
     *
     * @return int
     */
    public function getELO()
    {
        return $this->eLO;
    }

    /**
     * Set nbVic.
     *
     * @param int $nbVic
     *
     * @return SQPLeaderBoard
     */
    public function setNbVic($nbVic)
    {
        $this->nbVic = $nbVic;

        return $this;
    }

    /**
     * Get nbVic.
     *
     * @return int
     */
    public function getNbVic()
    {
        return $this->nbVic;
    }

    /**
     * Set nbDef.
     *
     * @param int $nbDef
     *
     * @return SQPLeaderBoard
     */
    public function setNbDef($nbDef)
    {
        $this->nbDef = $nbDef;

        return $this;
    }

    /**
     * Get nbDef.
     *
     * @return int
     */
    public function getNbDef()
    {
        return $this->nbDef;
    }
}
