<?php

namespace App\Entity;

use App\Repository\DateFieldRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DateFieldRepository::class)]
class DateField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $value = null;

    #[ORM\ManyToOne(inversedBy: 'dateFields')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $Item = null;

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

    public function getValue(): ?\DateTimeImmutable
    {
        return $this->value;
    }

    public function getStringValue(): ?string
    {
        return $this->value->format('d-m-y');
    }

    public function setValue(\DateTimeImmutable $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->Item;
    }

    public function setItem(?Item $Item): static
    {
        $this->Item = $Item;

        return $this;
    }
}
