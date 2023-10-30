<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 30)]
    private ?string $pseudo = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $registrationDate = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $avatar = null;

    #[ORM\Column]
    private ?bool $isBanned = null;



    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Topic::class)]
    private Collection $topics;

    #[ORM\ManyToMany(targetEntity: Topic::class, inversedBy: 'usersFavorite')]
    #[ORM\JoinTable(name:'favoriteTopic')]
    private Collection $favoriteTopic;

    #[ORM\OneToMany(mappedBy: 'commentator', targetEntity: CommentTopic::class)]
    private Collection $commentTopics;

    #[ORM\ManyToMany(targetEntity: CommentTopic::class, mappedBy: 'likeCommentTopic')]
    #[ORM\JoinTable(name:'likeCommentTopic',)]
    private Collection $likedCommentTopics;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class)]
    private Collection $sendMessages;

    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Message::class)]
    private Collection $receiveMessages;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Event::class)]
    private Collection $events;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'userFavorite')]
    #[ORM\JoinTable(name:'favoriteEvent',)]
    private Collection $favoriteEvents;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'usersParticipate')]
    #[ORM\JoinTable(name:'participate',)]
    private Collection $paticipateEvents;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CommentEvent::class)]
    private Collection $commentEvents;

    #[ORM\ManyToMany(targetEntity: CommentEvent::class, mappedBy: 'likeCommentEvent')]
    #[ORM\JoinTable(name:'likeCommentEvent',)]
    private Collection $likedCommentEvents;

   
    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->favoriteTopic = new ArrayCollection();
        $this->commentTopics = new ArrayCollection();
        $this->likedCommentTopics = new ArrayCollection();
        $this->sendMessages = new ArrayCollection();
        $this->receiveMessages = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->favoriteEvents = new ArrayCollection();
        $this->paticipateEvents = new ArrayCollection();
        $this->commentEvents = new ArrayCollection();
        $this->likedCommentEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeInterface $registrationDate): static
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function isIsBanned(): ?bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): static
    {
        $this->isBanned = $isBanned;

        return $this;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }


    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Topic>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function addTopic(Topic $topic): static
    {
        if (!$this->topics->contains($topic)) {
            $this->topics->add($topic);
            $topic->setCreator($this);
        }

        return $this;
    }

    public function removeTopic(Topic $topic): static
    {
        if ($this->topics->removeElement($topic)) {
            // set the owning side to null (unless already changed)
            if ($topic->getCreator() === $this) {
                $topic->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Topic>
     */
    public function getFavoriteTopic(): Collection
    {
        return $this->favoriteTopic;
    }

    public function addFavoriteTopic(Topic $favoriteTopic): static
    {
        if (!$this->favoriteTopic->contains($favoriteTopic)) {
            $this->favoriteTopic->add($favoriteTopic);
        }

        return $this;
    }

    public function removeFavoriteTopic(Topic $favoriteTopic): static
    {
        $this->favoriteTopic->removeElement($favoriteTopic);

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
            $commentTopic->setCommentator($this);
        }

        return $this;
    }

    public function removeCommentTopic(CommentTopic $commentTopic): static
    {
        if ($this->commentTopics->removeElement($commentTopic)) {
            // set the owning side to null (unless already changed)
            if ($commentTopic->getCommentator() === $this) {
                $commentTopic->setCommentator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommentTopic>
     */
    public function getLikedCommentTopics(): Collection
    {
        return $this->likedCommentTopics;
    }

    public function addLikedCommentTopic(CommentTopic $likedCommentTopic): static
    {
        if (!$this->likedCommentTopics->contains($likedCommentTopic)) {
            $this->likedCommentTopics->add($likedCommentTopic);
            $likedCommentTopic->addLikeCommentTopic($this);
        }

        return $this;
    }

    public function removeLikedCommentTopic(CommentTopic $likedCommentTopic): static
    {
        if ($this->likedCommentTopics->removeElement($likedCommentTopic)) {
            $likedCommentTopic->removeLikeCommentTopic($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getSendMessages(): Collection
    {
        return $this->sendMessages;
    }

    public function addSendMessage(Message $sendMessage): static
    {
        if (!$this->sendMessages->contains($sendMessage)) {
            $this->sendMessages->add($sendMessage);
            $sendMessage->setSender($this);
        }

        return $this;
    }

    public function removeSendMessage(Message $sendMessage): static
    {
        if ($this->sendMessages->removeElement($sendMessage)) {
            // set the owning side to null (unless already changed)
            if ($sendMessage->getSender() === $this) {
                $sendMessage->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getReceiveMessages(): Collection
    {
        return $this->receiveMessages;
    }

    public function addReceiveMessage(Message $receiveMessage): static
    {
        if (!$this->receiveMessages->contains($receiveMessage)) {
            $this->receiveMessages->add($receiveMessage);
            $receiveMessage->setReceiver($this);
        }

        return $this;
    }

    public function removeReceiveMessage(Message $receiveMessage): static
    {
        if ($this->receiveMessages->removeElement($receiveMessage)) {
            // set the owning side to null (unless already changed)
            if ($receiveMessage->getReceiver() === $this) {
                $receiveMessage->setReceiver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setCreator($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getCreator() === $this) {
                $event->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getFavoriteEvents(): Collection
    {
        return $this->favoriteEvents;
    }

    public function addFavoriteEvent(Event $favoriteEvent): static
    {
        if (!$this->favoriteEvents->contains($favoriteEvent)) {
            $this->favoriteEvents->add($favoriteEvent);
            $favoriteEvent->addUserFavorite($this);
        }

        return $this;
    }

    public function removeFavoriteEvent(Event $favoriteEvent): static
    {
        if ($this->favoriteEvents->removeElement($favoriteEvent)) {
            $favoriteEvent->removeUserFavorite($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getPaticipateEvents(): Collection
    {
        return $this->paticipateEvents;
    }

    public function addPaticipateEvent(Event $paticipateEvent): static
    {
        if (!$this->paticipateEvents->contains($paticipateEvent)) {
            $this->paticipateEvents->add($paticipateEvent);
            $paticipateEvent->addUserParticipate($this);
        }

        return $this;
    }

    public function removePaticipateEvent(Event $paticipateEvent): static
    {
        if ($this->paticipateEvents->removeElement($paticipateEvent)) {
            $paticipateEvent->removeUserParticipate($this);
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
            $commentEvent->setUser($this);
        }

        return $this;
    }

    public function removeCommentEvent(CommentEvent $commentEvent): static
    {
        if ($this->commentEvents->removeElement($commentEvent)) {
            // set the owning side to null (unless already changed)
            if ($commentEvent->getUser() === $this) {
                $commentEvent->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommentEvent>
     */
    public function getLikedCommentEvents(): Collection
    {
        return $this->likedCommentEvents;
    }

    public function addLikedCommentEvent(CommentEvent $likedCommentEvent): static
    {
        if (!$this->likedCommentEvents->contains($likedCommentEvent)) {
            $this->likedCommentEvents->add($likedCommentEvent);
            $likedCommentEvent->addLikeCommentEvent($this);
        }

        return $this;
    }

    public function removeLikedCommentEvent(CommentEvent $likedCommentEvent): static
    {
        if ($this->likedCommentEvents->removeElement($likedCommentEvent)) {
            $likedCommentEvent->removeLikeCommentEvent($this);
        }

        return $this;
    }

  
}
