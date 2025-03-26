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

    /**
     * @var Collection<int, Artist>
     */
    #[ORM\ManyToMany(targetEntity: Artist::class, inversedBy: 'songs')]
    #[ORM\JoinTable(name: 'songs_artists')]
    private Collection $artists;

    public function __construct($title, $artists)
    {
        $this->title = $title;
        $this->groups = $artists ?? new ArrayCollection();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getArtists()
    {
        return $this->artists;
    }
}
