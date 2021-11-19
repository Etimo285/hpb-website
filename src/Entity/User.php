<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Cette adresse email est déjà utilisée !")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */

    #[Assert\NotBlank(
        message: 'Merci de renseigner un email.',
    )]

    #[Assert\Length(
        min: 2,
        max: 180,
        minMessage: 'L\'adresse email renseignée doit faire au minimum {{ limit }} caractères.',
        maxMessage: 'L\'adresse email renseignée ne peut dépasser {{ limit }} caractères.',
    )]

    #[Assert\Email(
        message: 'L\'adresse email {{ value }} n\'est pas une adresse email valide',
    )]
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */

    #[Assert\Length(
        max: 50,
        maxMessage: 'Votre pseudo doit faire au maximum {{ limit }} caractères.',
    )]

    #[Assert\Regex(
        pattern: "/[a-zA-Z0-9 -]/",
    )]
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=50)
     */

    #[Assert\NotBlank(
        message: 'Merci de renseigner un prénom.',
    )]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le prénom doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Le prénom ne peut dépasser {{ limit }} caractères.',
    )]
    #[Assert\Regex(
        pattern: "/[a-zA-Z ,.'-]/",
        message: 'Le prénom ne peut contenir que des lettres et/ou tirets.',
    )]
    private $first_name;

    /**
     * @ORM\Column(type="string", length=50)
     */

    #[Assert\NotBlank(
        message: 'Merci de renseigner un nom.',
    )]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le nom doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut dépasser {{ limit }} caractères.',
    )]

    #[Assert\Regex(
        pattern: "/[a-zA-Z ,.'-]/",
        message: 'Le nom ne peut contenir que des lettres.',
    )]
    private $last_name;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */

    #[Assert\Length(
        min: 10,
        max: 15,
        minMessage: 'Le numéro de téléphone doit contenir {{ limit }} chiffres.',
        maxMessage: 'Le numéro de téléphone ne peut dépasser {{ limit }} chiffres.',
    )]

    #[Assert\Regex(
        pattern: '/[0-9]/',
        message: 'Le numéro de téléphone ne peut contenir que des chiffres.',
    )]
    private $phone;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Article::class, mappedBy="author")
     */
    private $articles;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="author")
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity=Alert::class, mappedBy="author")
     */
    private $alerts;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\OneToMany(targetEntity=AlertView::class, mappedBy="viewed_by")
     */
    private $alertViews;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="author")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=AlertMessage::class, mappedBy="author")
     */
    private $alertMessages;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isMember;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $functionTitle;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->alerts = new ArrayCollection();
        $this->alertViews = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->alertMessages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
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
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
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

    public function setRoles(array $roles): self
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

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setAuthor($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getAuthor() === $this) {
                $event->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Alert[]
     */
    public function getAlerts(): Collection
    {
        return $this->alerts;
    }

    public function addAlert(Alert $alert): self
    {
        if (!$this->alerts->contains($alert)) {
            $this->alerts[] = $alert;
            $alert->setAuthor($this);
        }

        return $this;
    }

    public function removeAlert(Alert $alert): self
    {
        if ($this->alerts->removeElement($alert)) {
            // set the owning side to null (unless already changed)
            if ($alert->getAuthor() === $this) {
                $alert->setAuthor(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection|AlertView[]
     */
    public function getAlertViews(): Collection
    {
        return $this->alertViews;
    }

    public function addAlertView(AlertView $alertView): self
    {
        if (!$this->alertViews->contains($alertView)) {
            $this->alertViews[] = $alertView;
            $alertView->setViewedBy($this);
        }

        return $this;
    }

    public function removeAlertView(AlertView $alertView): self
    {
        if ($this->alertViews->removeElement($alertView)) {
            // set the owning side to null (unless already changed)
            if ($alertView->getViewedBy() === $this) {
                $alertView->setViewedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AlertMessage[]
     */
    public function getAlertMessages(): Collection
    {
        return $this->alertMessages;
    }

    public function addAlertMessage(AlertMessage $alertMessage): self
    {
        if (!$this->alertMessages->contains($alertMessage)) {
            $this->alertMessages[] = $alertMessage;
            $alertMessage->setAuthor($this);
        }

        return $this;
    }

    public function removeAlertMessage(AlertMessage $alertMessage): self
    {
        if ($this->alertMessages->removeElement($alertMessage)) {
            // set the owning side to null (unless already changed)
            if ($alertMessage->getAuthor() === $this) {
                $alertMessage->setAuthor(null);
            }
        }

        return $this;
    }

    public function getIsMember(): ?bool
    {
        return $this->isMember;
    }

    public function setIsMember(bool $isMember): self
    {
        $this->isMember = $isMember;

        return $this;
    }

    public function getFunctionTitle(): ?string
    {
        return $this->functionTitle;
    }

    public function setFunctionTitle(?string $functionTitle): self
    {
        $this->functionTitle = $functionTitle;

        return $this;
    }
}
