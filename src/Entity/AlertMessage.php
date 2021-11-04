<?php

namespace App\Entity;

use App\Repository\AlertMessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AlertMessageRepository::class)
 */
class AlertMessage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */

    #[Assert\NotBlank(
        message: 'Merci de renseigner un contenu pour votre message.'
    )]

    #[Assert\Length(
        min: 5,
        max: 2500,
        minMessage: 'Le contenu de votre message doit comporter au moins {{ limit }} caractères.',
        maxMessage: 'Le contenu de votre message ne peut dépasser {{ limit }} caractères.',
    )]
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Alert::class, inversedBy="alertmessage")
     */
    private $alert;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="alertMessages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getAlert(): ?Alert
    {
        return $this->alert;
    }

    public function setAlert(?Alert $alert): self
    {
        $this->alert = $alert;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
