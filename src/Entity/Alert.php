<?php

namespace App\Entity;

use App\Repository\AlertRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use http\Message;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AlertRepository::class)
 */
class Alert
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */

    #[Assert\NotBlank(
        message: 'Merci de renseigner l\'objet de votre alerte.',
    )]

    #[Assert\Length(
        min: 2,
        max: 150,
        minMessage: 'L\'objet de votre alerte doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'L\'objet de votre alerte ne peut dépasser {{ limit }} caractères.',
    )]
    private $object;

    /**
     * @ORM\Column(type="text")
     */

    #[Assert\NotBlank(
        message: 'Merci de renseigner un contenu pour votre alerte.'
    )]

    #[Assert\Length(
        min: 10,
        max: 50000,
        minMessage: 'Le contenu de votre alerte doit comporter au moins {{ limit }} caractères.',
        maxMessage: 'Le contenu de votre alerte ne peut dépasser {{ limit }} caractères.',
    )]
    private $content;

    /**
     * @ORM\Column(type="string")
     */

    #[Assert\NotBlank(
        message: 'Merci de renseigner le nom de la ville concernée.',
    )]

    #[Assert\Length(
        max: 50,
        maxMessage: 'La ville renseignée comporte plus de {{ limit }} caractères.',
    )]

    #[Assert\Regex(
        pattern: "/^[a-z,A-Z,.'-]+$/",
        message: 'La ville renseignée ne peut comporter uniquement des lettres et/ou tirets',
    )]
    private $city;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */

    #[Assert\Length(
        exactly: 5,
        exactMessage: 'Le code postal doit comporter 5 chiffres.'
    )]

    #[Assert\Regex(
        pattern: '/^[0-9]+$/',
        message: 'Le code postal ne peut comporter que des chiffres.',
    )]
    private $postcode;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */

    #[Assert\Length(
        max: 150,
        maxMessage: 'Le premier champ d\'adresse ne peut dépasser {{ limit }} caractères.'
    )]

    #[Assert\Regex(
        pattern: '/^[A-Za-z0-9\ \-\_]+$/',
        message: 'Le premier champ d\'adresse ne peut comporter que des caractères, chiffres, espaces et tirets.',
    )]
    private $address1;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */

    #[Assert\Length(
        max: 100,
        maxMessage: 'Le deuxième champ d\'adresse ne peut dépasser {{ limit }} caractères.'
    )]

    #[Assert\Regex(
        pattern: '/^[A-Za-z0-9\ \-\_]+$/',
        message: 'Le deuxieme champ d\'adresse ne peut comporter que des caractères, chiffres, espaces et tirets.',
    )]
    private $address2;

    /** --SLUG--
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"object"})
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="alerts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity=Media::class, mappedBy="alert")
     */
    private $media;

    /**
     * @ORM\OneToMany(targetEntity=AlertMessage::class, mappedBy="alert")
     */
    private $alertmessage;

    /**
     * @ORM\OneToOne(targetEntity=AlertView::class, cascade={"persist", "remove"})
     */
    private $alertview;

    public function __construct()
    {
        $this->media = new ArrayCollection();
        $this->alertmessage = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(string $object): self
    {
        $this->object = $object;

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

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1(?string $address1): self
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

    public function getViewedDate(): ?\DateTimeInterface
    {
        return $this->viewed_date;
    }

    public function setViewedDate(?\DateTimeInterface $viewed_date): self
    {
        $this->viewed_date = $viewed_date;

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
            $medium->setAlert($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): self
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getAlert() === $this) {
                $medium->setAlert(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AlertMessage[]
     */
    public function getAlertMessage(): Collection
    {
        return $this->alertmessage;
    }

    public function addAlertMessage(AlertMessage $alertmessage): self
    {
        if (!$this->alertmessage->contains($alertmessage)) {
            $this->alertmessage[] = $alertmessage;
            $alertmessage->setAlert($this);
        }

        return $this;
    }

    public function removeAlertMessage(AlertMessage $alertmessage): self
    {
        if ($this->alertmessage->removeElement($alertmessage)) {
            // set the owning side to null (unless already changed)
            if ($alertmessage->getAlert() === $this) {
                $alertmessage->setAlert(null);
            }
        }

        return $this;
    }

    public function getAlertView(): ?AlertView
    {
        return $this->alertview;
    }

    public function setAlertView(?AlertView $alertview): self
    {
        $this->alertview = $alertview;

        return $this;
    }
}
