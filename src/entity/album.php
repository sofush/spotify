<?php

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'albums')]
class Album
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $title;

    #[ORM\OneToOne(targetEntity: Artist::class)]
    #[ORM\JoinColumn(name: 'artist_id', referencedColumnName: 'id')]
    private Artist $artist;

    #[ORM\Column(type: 'string')]
    private string $imageUrl;

    /**
     * @var Collection<int, Song>
     */
    #[ORM\JoinTable(name: 'albums_songs')]
    #[ORM\JoinColumn(name: 'album_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'song_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Song::class)]
    private Collection $songs;

    public function __construct($title, $imageUrl, $artist, $songs)
    {
        $this->title = $title;
        $this->imageUrl = $imageUrl;
        $this->artist = $artist;
        $this->songs = new ArrayCollection($songs ?? []);
    }

    public function getArtist()
    {
        return $this->artist;
    }

    public function setArtist($artist)
    {
        $this->artist = $artist;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    public function getSongs()
    {
        return $this->songs;
    }
}
