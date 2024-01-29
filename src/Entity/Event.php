<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column]
    private ?int $numberOfPlaces = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 100)]
    private ?string $adress = null;

    #[ORM\Column(length: 100)]
    private ?string $city = null;

    #[ORM\Column(length: 20)]
    private ?string $zipCode = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryEvent $category = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $isLocked = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'favoriteEvents')]
    #[ORM\JoinTable(name:'favoriteEvent',)]
    private Collection $usersFavorite;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'paticipateEvents')]
    #[ORM\JoinTable(name:'participate',)]
    private Collection $usersParticipate;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Picture::class)]
    private Collection $pictures;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: CommentEvent::class, orphanRemoval: true)]
    private Collection $commentEvents;

    #[ORM\Column(length: 10)]
    private ?string $matchID = null;

    public function __construct()
    {
        $this->usersFavorite = new ArrayCollection();
        $this->usersParticipate = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->commentEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getNumberOfPlaces(): ?int
    {
        return $this->numberOfPlaces;
    }

    public function setNumberOfPlaces(int $numberOfPlaces): static
    {
        $this->numberOfPlaces = $numberOfPlaces;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCategory(): ?CategoryEvent
    {
        return $this->category;
    }

    public function setCategory(?CategoryEvent $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isIsLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): static
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsersFavorite(): Collection
    {
        return $this->usersFavorite;
    }

    public function addUserFavorite(User $userFavorite): static
    {
        if (!$this->usersFavorite->contains($userFavorite)) {
            $this->usersFavorite->add($userFavorite);
        }

        return $this;
    }

    public function removeUserFavorite(User $userFavorite): static
    {
        $this->usersFavorite->removeElement($userFavorite);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsersParticipate(): Collection
    {
        return $this->usersParticipate;
    }

    public function addUserParticipate(User $userParticipate): static
    {
        if (!$this->usersParticipate->contains($userParticipate)) {
            $this->usersParticipate->add($userParticipate);
        }

        return $this;
    }

    public function removeUserParticipate(User $userParticipate): static
    {
        $this->usersParticipate->removeElement($userParticipate);

        return $this;
    }

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): static
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
            $picture->setEvent($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): static
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getEvent() === $this) {
                $picture->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommentEvent>
     */
    public function getCommentEvents(): Collection
    {
        return $this->commentEvents;
    }

    public function addCommentEvent(CommentEvent $commentEvent): static
    {
        if (!$this->commentEvents->contains($commentEvent)) {
            $this->commentEvents->add($commentEvent);
            $commentEvent->setEvent($this);
        }

        return $this;
    }

    public function removeCommentEvent(CommentEvent $commentEvent): static
    {
        if ($this->commentEvents->removeElement($commentEvent)) {
            // set the owning side to null (unless already changed)
            if ($commentEvent->getEvent() === $this) {
                $commentEvent->setEvent(null);
            }
        }

        return $this;
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
}
