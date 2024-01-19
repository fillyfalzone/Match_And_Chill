<?php

namespace App\Entity;

use App\Repository\FavoriteMatchRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteMatchRepository::class)]
class FavoriteMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $matchID = null;

    #[ORM\Column(length: 255)]
    private ?string $userID = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatchID(): ?string
    {
        return $this->matchID;
    }

    public function setMatchID(string $matchID): static
    {
        $this->matchID = $matchID;

        return $this;
    }

    public function getUserID(): ?string
    {
        return $this->userID;
    }

    public function setUserID(string $userID): static
    {
        $this->userID = $userID;

        return $this;
    }
}
