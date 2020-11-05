<?php

namespace AGORA\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GameInfo
 *
 * @ORM\Table(name="game_info")
 * @ORM\Entity(repositoryClass="AGORA\PlatformBundle\Repository\GameInfoRepository")
 */
class GameInfo
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
     * @var int|null
     *
     * @ORM\Column(name="minJoueurs", type="integer")
     */
    private $minJoueurs;

    /**
     * @var int|null
     *
     * @ORM\Column(name="maxJoueurs", type="integer")
     */
    private $maxJoueurs;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="imageURL", type="string", length=255, nullable=true)
     */
    private $imageURL;

    /**
     * @var string|null
     *
     * @ORM\Column(name="reglesURL", type="string", length=255, nullable=true, unique=false)
     */
    private $reglesURL;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255, unique=true)
     */
    private $nom;

    /**
     * @var string|null
     *
     * @ORM\Column(name="gameCode", type="string", length=255, unique=true)
     */
    private $gameCode;


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
     * Set minJoueurs.
     *
     * @param integer|null $minJoueurs
     *
     * @return GameInfo
     */
    public function setMinJoueurs($minJoueurs = null)
    {
        $this->minJoueurs = $minJoueurs;

        return $this;
    }

    /**
     * Get minJoueurs.
     *
     * @return int
     */
    public function getMinJoueurs()
    {
        return $this->minJoueurs;
    }
    
    /**
     * Set maxJoueurs.
     *
     * @param integer|null $maxJoueurs
     *
     * @return GameInfo
     */
    public function setMaxJoueurs($maxJoueurs = null)
    {
        $this->maxJoueurs = $maxJoueurs;

        return $this;
    }

    /**
     * Get maxJoueurs.
     *
     * @return int
     */
    public function getMaxJoueurs()
    {
        return $this->maxJoueurs;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return GameInfo
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set imageURL.
     *
     * @param string|null $imageURL
     *
     * @return GameInfo
     */
    public function setimageURL($imageURL = null)
    {
        $this->imageURL = $imageURL;

        return $this;
    }

    /**
     * Get imageURL.
     *
     * @return string|null
     */
    public function getimageURL()
    {
        return $this->imageURL;
    }

    /**
     * Set reglesURL.
     *
     * @param string|null $reglesURL
     *
     * @return GameInfo
     */
    public function setreglesURL($reglesURL = null)
    {
        $this->reglesURL = $reglesURL;

        return $this;
    }

    /**
     * Get reglesURL.
     *
     * @return string|null
     */
    public function getreglesURL()
    {
        return $this->reglesURL;
    }

    /**
     * Set nom.
     *
     * @param string $nom
     *
     * @return GameInfo
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set gameCode.
     *
     * @param string $gameCode
     *
     * @return GameInfo
     */
    public function setGameCode($gameCode)
    {
        $this->gameCode = $gameCode;

        return $this;
    }

    /**
     * Get gameCode.
     *
     * @return string
     */
    public function getGameCode()
    {
        return $this->gameCode;
    }
}
