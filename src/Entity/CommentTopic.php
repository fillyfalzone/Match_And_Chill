<?php

namespace App\Entity;

use App\Repository\CommentTopicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentTopicRepository::class)]
class CommentTopic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\ManyToOne(inversedBy: 'commentTopics')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $commentator = null;

    #[ORM\ManyToOne(inversedBy: 'commentTopics')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Topic $topic = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'likedCommentTopics')]
    #[ORM\JoinTable(name:'likeCommentTopic',)]
    private Collection $likeCommentTopic;

    public function __construct()
    {
        $this->likeCommentTopic = new ArrayCollection();
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

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getCommentator(): ?User
    {
        return $this->commentator;
    }

    public function setCommentator(?User $commentator): static
    {
        $this->commentator = $commentator;

        return $this;
    }

    public function getTopic(): ?Topic
    {
        return $this->topic;
    }

    public function setTopic(?Topic $topic): static
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLikeCommentTopic(): Collection
    {
        return $this->likeCommentTopic;
    }

    public function addLikeCommentTopic(User $likeCommentTopic): static
    {
        if (!$this->likeCommentTopic->contains($likeCommentTopic)) {
            $this->likeCommentTopic->add($likeCommentTopic);
        }

        return $this;
    }

    public function removeLikeCommentTopic(User $likeCommentTopic): static
    {
        $this->likeCommentTopic->removeElement($likeCommentTopic);

        return $this;
    }
}
