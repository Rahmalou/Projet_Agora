<?php

namespace AGORA\Game\AveCesarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AveCesarGame
 *
 * @ORM\Table(name="ave_cesar_game")
 * @ORM\Entity(repositoryClass="AGORA\Game\AveCesarBundle\Repository\AveCesarGameRepository")
 */
class AveCesarGame
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
     * @ORM\Column(name="nextplayer", type="integer")
     */
    private $nextplayer;


    /**
     * @var int
     *
     * @ORM\Column(name="firstplayer", type="integer")
     */
    private $firstplayer;


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
     * Set nextplayer.
     *
     * @param int $nextplayer
     *
     * @return AveCesarGame
     */
    public function setNextplayer($nextplayer)
    {
        $this->nextplayer = $nextplayer;

        return $this;
    }

    /**
     * Get nextplayer.
     *
     * @return int
     */
    public function getNextplayer()
    {
        return $this->nextplayer;
    }

    /**
     * Set firstplayer.
     *
     * @param int $firstplayer
     */
    public function setFirstplayer($firstplayer)
    {
        $this->firstplayer = $firstplayer;
    }

    /**
     * Get firstplayer.
     *
     * @return int
     */
    public function getFirstplayer()
    {
        return $this->firstplayer;
    }
}
