<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MediaRepository::class)
 */
class Media
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */

    #[Assert\NotBlank(
        message: 'L\'url du média ne peut être vide.'
    )]
    
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity=Article::class, inversedBy="media")
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class, inversedBy="media")
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity=Alert::class, inversedBy="media")
     */
    private $alert;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
