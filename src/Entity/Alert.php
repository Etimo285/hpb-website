<?php

namespace App\Entity;

use App\Repository\AlertRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @ORM\Column(type="string", length=100)
     */
    private $object;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $postcode;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $address1;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $viewed_date;

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

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
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
    public function getAlertmessage(): Collection
    {
        return $this->alertmessage;
    }

    public function addAlertmessage(AlertMessage $alertmessage): self
    {
        if (!$this->alertmessage->contains($alertmessage)) {
            $this->alertmessage[] = $alertmessage;
            $alertmessage->setAlert($this);
        }

        return $this;
    }

    public function removeAlertmessage(AlertMessage $alertmessage): self
    {
        if ($this->alertmessage->removeElement($alertmessage)) {
            // set the owning side to null (unless already changed)
            if ($alertmessage->getAlert() === $this) {
                $alertmessage->setAlert(null);
            }
        }

        return $this;
    }
}
