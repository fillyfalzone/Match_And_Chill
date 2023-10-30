<?php

namespace App\Entity;

use App\Repository\CommentEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentEventRepository::class)]
class CommentEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $crationDate = null;

    #[ORM\ManyToOne(inversedBy: 'commentEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'commentEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'likedCommentEvents')]
    #[ORM\JoinTable(name:'likeCommentEvent',)]
    private Collection $likeCommentEvent;

    public function __construct()
    {
        $this->likeCommentEvent = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getCrationDate(): ?\DateTimeInterface
    {
        return $this->crationDate;
    }

    public function setCrationDate(\DateTimeInterface $crationDate): static
    {
        $this->crationDate = $crationDate;

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

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLikeCommentEvent(): Collection
    {
        return $this->likeCommentEvent;
    }

    public function addLikeCommentEvent(User $likeCommentEvent): static
    {
        if (!$this->likeCommentEvent->contains($likeCommentEvent)) {
            $this->likeCommentEvent->add($likeCommentEvent);
        }

        return $this;
    }

    public function removeLikeCommentEvent(User $likeCommentEvent): static
    {
        $this->likeCommentEvent->removeElement($likeCommentEvent);

        return $this;
    }
}
