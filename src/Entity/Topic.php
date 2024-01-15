<?php

namespace App\Entity;

use App\Repository\TopicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TopicRepository::class)]
class Topic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column]
    private ?bool $isLocked = null;

    #[ORM\ManyToOne(inversedBy: 'topics')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryForum $categoryForum = null;

    #[ORM\ManyToOne(inversedBy: 'topics')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favoriteTopic')]
    #[ORM\JoinTable(name:'favoriteTopic')]
    private Collection $usersFavorite;
    
    #[ORM\OneToMany(mappedBy: 'topic', targetEntity: CommentTopic::class, orphanRemoval: true)]
    private Collection $commentTopics;

    public function __construct()
    {
        $this->usersFavorite = new ArrayCollection();
        $this->commentTopics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function isIsLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setIsLocked(bool $isLocked): static
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    public function getCategoryForum(): ?CategoryForum
    {
        return $this->categoryForum;
    }

    public function setCategoryForum(?CategoryForum $categoryForum): static
    {
        $this->categoryForum = $categoryForum;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsersFavorite(): Collection
    {
        return $this->usersFavorite;
    }

    public function addUsersFavorite(User $usersFavorite): static
    {
        if (!$this->usersFavorite->contains($usersFavorite)) {
            $this->usersFavorite->add($usersFavorite);
            $usersFavorite->addFavoriteTopic($this);
        }

        return $this;
    }

    public function removeUsersFavorite(User $usersFavorite): static
    {
        if ($this->usersFavorite->removeElement($usersFavorite)) {
            $usersFavorite->removeFavoriteTopic($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, CommentTopic>
     */
    public function getCommentTopics(): Collection
    {
        return $this->commentTopics;
    }

    public function addCommentTopic(CommentTopic $commentTopic): static
    {
        if (!$this->commentTopics->contains($commentTopic)) {
            $this->commentTopics->add($commentTopic);
            $commentTopic->setTopic($this);
        }

        return $this;
    }

    public function removeCommentTopic(CommentTopic $commentTopic): static
    {
        if ($this->commentTopics->removeElement($commentTopic)) {
            // set the owning side to null (unless already changed)
            if ($commentTopic->getTopic() === $this) {
                $commentTopic->setTopic(null);
            }
        }

        return $this;
    }
}
