<?php

namespace App\Entity;

use App\Repository\ItemCollectionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemCollectionRepository::class)]
class ItemCollection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 100)]
    private ?string $category = null;

    #[ORM\ManyToOne(inversedBy: 'itemCollectionId')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $integers = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $strings = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $texts = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $bools = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $dates = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
    
    public function getIntegers(): ?array
    {
        return $this->integers;
    }

    public function setIntegers(?array $integers): static
    {
        $this->integers = $integers;

        return $this;
    }

    public function getStrings(): ?array
    {
        return $this->strings;
    }

    public function setStrings(?array $strings): static
    {
        $this->strings = $strings;

        return $this;
    }

    public function getTexts(): ?array
    {
        return $this->texts;
    }

    public function setTexts(?array $texts): static
    {
        $this->texts = $texts;

        return $this;
    }

    public function getBools(): ?array
    {
        return $this->bools;
    }

    public function setBools(?array $bools): static
    {
        $this->bools = $bools;

        return $this;
    }

    public function getDates(): ?array
    {
        return $this->dates;
    }

    public function setDates(?array $dates): static
    {
        $this->dates = $dates;

        return $this;
    }
}
