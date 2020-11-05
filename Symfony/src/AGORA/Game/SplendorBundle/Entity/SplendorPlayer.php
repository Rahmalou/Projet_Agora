<?php

namespace AGORA\Game\SplendorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SplendorPlayer
 *
 * @ORM\Table(name="splendor_player")
 * @ORM\Entity(repositoryClass="AGORA\Game\SplendorBundle\Repository\SplendorPlayerRepository")
 */
class SplendorPlayer
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
     * @ORM\Column(name="gameId", type="integer")
     */
    private $gameId;

    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="AGORA\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     */
    private $idUser;

    /**
     * @var int
     *
     * @ORM\Column(name="prestige", type="integer")
     */
    private $prestige;

    /**
     * @var string
     *
     * [emeraldTokens, sapphireTokens, rubyTokens, diamondTokens, onyxTokens, JokerGoldTokens]
     *
     * @ORM\Column(name="listTokens", type="string", length=50)
     */
    private $listTokens;

    /**
     * @var string
     *
     * @ORM\Column(name="reservedCards", type="string", length=50)
     */
    private $reservedCards;

    /**
     * @var string
     *
     * @ORM\Column(name="buyedCards", type="string", length=500)
     */
    private $buyedCards;

    /**
     * @var string
     *
     * @ORM\Column(name="hiddenCards", type="string", length=50)
     */
    private $hiddenCards;


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
     * @return SplendorPlayer
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
     * Set idUser.
     *
     * @param int $idUser
     *
     * @return SplendorPlayer
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
     * Set prestige.
     *
     * @param int $prestige
     *
     * @return SplendorPlayer
     */
    public function setPrestige($prestige)
    {
        $this->prestige = $prestige;

        return $this;
    }

    /**
     * Get prestige.
     *
     * @return int
     */
    public function getPrestige()
    {
        return $this->prestige;
    }

    /**
     * Set listTokens.
     *
     * @param string $listTokens
     *
     * @return SplendorPlayer
     */
    public function setListTokens($listTokens)
    {
        $this->listTokens = $listTokens;

        return $this;
    }

    /**
     * Get listTokens.
     * [emeraldTokens, sapphireTokens, rubyTokens, diamondTokens, onyxTokens, JokerGoldTokens]
     *
     * @return array
     */
    public function getListTokens()
    {
        return array_map('intval', explode(',', $this->listTokens));

    }

    /**
     * Set reservedCards.
     *
     * @param string $reservedCards
     *
     * @return SplendorPlayer
     */
    public function setReservedCards($reservedCards)
    {
        $this->reservedCards = $reservedCards;

        return $this;
    }

    /**
     * Get reservedCards.
     *
     * @return array
     */
    public function getReservedCards()
    {
//        if ($this->reservedCards == "") {
//            return [];
//        }
        return array_map('intval', explode(',', $this->reservedCards));

    }

    /**
     * Set buyedCards.
     *
     * @param string $buyedCards
     *
     * @return SplendorPlayer
     */
    public function setBuyedCards($buyedCards)
    {
        $this->buyedCards = $buyedCards;

        return $this;
    }

    /**
     * Get buyedCards.
     *
     * @return array
     */
    public function getBuyedCards()
    {
//        if ($this->getBuyedCards() == "") {
//            return [];
//        }
        return array_map('intval', explode(',', $this->buyedCards));
    }

    /**
     * @param string $hiddenCards
     */
    public function setHiddenCards($hiddenCards)
    {
        $this->hiddenCards = $hiddenCards;
    }

    /**
     * @return string
     */
    public function getHiddenCards()
    {
        return array_map('intval', explode(',', $this->hiddenCards));
    }
}
