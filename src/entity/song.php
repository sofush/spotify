<?php

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'songs')]
class Song
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'string')]
    private string $title;

    #[ORM\Column(type: 'string')]
    private string $duration;

    #[ORM\Column(type: 'string')]
    private string $imageUrl;

    #[ORM\Column(type: 'string', nullable: true)]
    private string|null $songUrl;

    /**
     * @var Collection<int, Artist>
     */
    #[ORM\ManyToMany(targetEntity: Artist::class, inversedBy: 'songs')]
    #[ORM\JoinTable(name: 'songs_artists')]
    private Collection $artists;

    public function __construct($title, $duration, $imageUrl, $songUrl, $artists)
    {
        $this->title = $title;
        $this->duration = $duration;
        $this->imageUrl = $imageUrl;
        $this->songUrl = $songUrl;
        $this->artists = new ArrayCollection($artists ?? []);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    public function getSongUrl()
    {
        return $this->songUrl;
    }

    public function setSongUrl($songUrl)
    {
        $this->songUrl = $songUrl;
    }

    public function getArtists()
    {
        return $this->artists;
    }
}
