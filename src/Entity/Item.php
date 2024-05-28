<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $tags = null;

    #[ORM\ManyToOne(inversedBy: 'item')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ItemCollection $itemCollection = null;

    #[ORM\OneToOne(mappedBy: 'Item', cascade: ['persist', 'remove'])]
    private ?Like $likes = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'Item', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, Integer>
     */
    #[ORM\OneToMany(targetEntity: Integer::class, mappedBy: 'Item', orphanRemoval: true)]
    private Collection $integers;

    /**
     * @var Collection<int, StringField>
     */
    #[ORM\OneToMany(targetEntity: StringField::class, mappedBy: 'Item', orphanRemoval: true)]
    private Collection $stringFields;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->integers = new ArrayCollection();
        $this->stringFields = new ArrayCollection();
    }

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

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(string $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function getItemCollection(): ?ItemCollection
    {
        return $this->itemCollection;
    }

    public function setItemCollection(?ItemCollection $itemCollection): static
    {
        $this->itemCollection = $itemCollection;

        return $this;
    }

    public function getLikes(): ?Like
    {
        return $this->likes;
    }

    public function setLikes(Like $likes): static
    {
        // set the owning side of the relation if necessary
        if ($likes->getItem() !== $this) {
            $likes->setItem($this);
        }

        $this->likes = $likes;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setItem($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getItem() === $this) {
                $comment->setItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Integer>
     */
    public function getIntegers(): Collection
    {
        return $this->integers;
    }

    public function addInteger(Integer $integer): static
    {
        if (!$this->integers->contains($integer)) {
            $this->integers->add($integer);
            $integer->setItem($this);
        }

        return $this;
    }

    public function removeInteger(Integer $integer): static
    {
        if ($this->integers->removeElement($integer)) {
            // set the owning side to null (unless already changed)
            if ($integer->getItem() === $this) {
                $integer->setItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StringField>
     */
    public function getStringFields(): Collection
    {
        return $this->stringFields;
    }

    public function addStringField(StringField $stringField): static
    {
        if (!$this->stringFields->contains($stringField)) {
            $this->stringFields->add($stringField);
            $stringField->setItem($this);
        }

        return $this;
    }

    public function removeStringField(StringField $stringField): static
    {
        if ($this->stringFields->removeElement($stringField)) {
            // set the owning side to null (unless already changed)
            if ($stringField->getItem() === $this) {
                $stringField->setItem(null);
            }
        }

        return $this;
    }
}
