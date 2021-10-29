<?php

namespace App\Entity;

use App\Repository\AlertViewRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AlertViewRepository::class)
 */
class AlertView
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="alertViews")
     */
    private $viewed_by;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $viewed_date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getViewedBy(): ?User
    {
        return $this->viewed_by;
    }

    public function setViewedBy(?User $viewed_by): self
    {
        $this->viewed_by = $viewed_by;

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
}
