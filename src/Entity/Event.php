<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */

    #[Assert\NotBlank(
        message: 'Merci de renseigner un titre à votre évènement.'
    )]

    #[Assert\Length(
        min: 2,
        max: 150,
        minMessage: 'Le titre de votre évènement doit comporter au minimum {{ limit }} caractères.',
        maxMessage: 'Le titre de votre évènement ne doit dépasser {{ limit }} caractères.'
    )]
    private $title;

    /**
     * @ORM\Column(type="text")
     */

    #[Assert\NotBlank(
        message: 'Merci de renseigner une description à votre évènement.'
    )]

    #[Assert\Length(
        min: 10,
        max: 50000,
        minMessage: 'La description de votre évènement doit comporter au minimum {{ limit }} caractères.',
        maxMessage: 'La description de votre évènement ne doit dépasser {{ limit }} caractères.'
    )]
    private $content;

    /**
     * @ORM\Column(type="string", length=50)
     */

    #[Assert\NotBlank(
        message: 'Merci de renseigner une ville à votre évènement.'
    )]

    #[Assert\Length(
        max: 50,
        maxMessage: 'La ville renseignée ne doit comporter plus de {{ limit }} caractères.',
    )]

    #[Assert\Regex(
        pattern: "/^[a-z ,.'-]+/",
        message: 'La ville renseignée ne peut comporter uniquement des lettres et/ou tirets',
    )]
    private $city;

    /**
     * @ORM\Column(type="string", length=15)
     */

    #[Assert\NotBlank(
        message: 'Le code postal ne peut être vide.',
    )]

    #[Assert\Length(
        exactly: 5,
        exactMessage: 'Le code postal doit comporter 5 chiffres.'
    )]

    #[Assert\Regex(
        pattern: '/[0-9]/',
        message: 'Le code postal ne peut comporter que des chiffres.',
    )]
    private $postcode;

    /**
     * @ORM\Column(type="string", length=150)
     */

    #[Assert\NotBlank(
        message: 'Le premier champ d\'adresse ne peut être vide.',
    )]

    #[Assert\Length(
        max: 50,
        maxMessage: 'Le premier champ d\'adresse ne peut dépasser {{ limit }} caractères.'
    )]

    #[Assert\Regex(
        pattern: '/[A-Za-z0-9\ \-\_]/',
        message: 'Le premier champ d\'adresse ne peut comporter que des caractères, chiffres, espaces et tirets.',
    )]
    private $address1;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */

    #[Assert\Length(
        max: 50,
        maxMessage: 'Le deuxième champ d\'adresse ne peut dépasser {{ limit }} caractères.'
    )]

    #[Assert\Regex(
        pattern: '/[A-Za-z0-9\ \-\_]/',
        message: 'Le deuxieme champ d\'adresse ne peut comporter que des caractères, chiffres, espaces et tirets.',
    )]
    private $address2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */

    #[Assert\Url(
        message: 'Le champ doit contenir une URL uniquement',
        protocols: ['https'],
    )]
    private $map;

    /** --SLUG--
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"title"})
     */
    private $slug;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity=Media::class, mappedBy="event")
     */
    private $media;

    public function __construct()
    {
        $this->media = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1(string $address1): self
    {
        $this->address1 = $address1;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getMap(): ?string
    {
        return $this->map;
    }

    public function setMap(?string $map): self
    {
        $this->map = $map;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
            $medium->setEvent($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): self
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getEvent() === $this) {
                $medium->setEvent(null);
            }
        }

        return $this;
    }
}
