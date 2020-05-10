<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var ArrayCollection
     * @ORM\Column(type="array", nullable=false)
     */
    private $conditions;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\OneToMany(targetEntity=UserProductView::class, mappedBy="product", orphanRemoval=true)
     */
    private $viewedBy;

    /**
     * @ORM\OneToMany(targetEntity=UserProductPurchase::class, mappedBy="product", orphanRemoval=true)
     */
    private $boughtBy;

    /**
     * @ORM\OneToMany(targetEntity=UserProductRating::class, mappedBy="product", orphanRemoval=true)
     */
    private $ratedBy;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="productsOnSale")
     * @ORM\JoinColumn(nullable=false)
     */
    private $seller;



    public function __construct()
    {
        $this->viewedBy = new ArrayCollection();
        $this->boughtBy = new ArrayCollection();
        $this->ratedBy = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function addCondition(string $condition){
        $this->conditions->add($condition);
    }

    public function removeCondition(string $condition){
        $this->conditions->removeElement($condition);
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return Collection|UserProductView[]
     */
    public function getViewedBy(): Collection
    {
        return $this->viewedBy;
    }

    public function addViewedBy(UserProductView $viewedBy): self
    {
        if (!$this->viewedBy->contains($viewedBy)) {
            $this->viewedBy[] = $viewedBy;
            $viewedBy->setProduct($this);
        }

        return $this;
    }

    public function removeViewedBy(UserProductView $viewedBy): self
    {
        if ($this->viewedBy->contains($viewedBy)) {
            $this->viewedBy->removeElement($viewedBy);
            // set the owning side to null (unless already changed)
            if ($viewedBy->getProduct() === $this) {
                $viewedBy->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserProductPurchase[]
     */
    public function getBoughtBy(): Collection
    {
        return $this->boughtBy;
    }

    public function addBoughtBy(UserProductPurchase $boughtBy): self
    {
        if (!$this->boughtBy->contains($boughtBy)) {
            $this->boughtBy[] = $boughtBy;
            $boughtBy->setProduct($this);
        }

        return $this;
    }

    public function removeBoughtBy(UserProductPurchase $boughtBy): self
    {
        if ($this->boughtBy->contains($boughtBy)) {
            $this->boughtBy->removeElement($boughtBy);
            // set the owning side to null (unless already changed)
            if ($boughtBy->getProduct() === $this) {
                $boughtBy->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserProductRating[]
     */
    public function getRatedBy(): Collection
    {
        return $this->ratedBy;
    }

    public function addRatedBy(UserProductRating $ratedBy): self
    {
        if (!$this->ratedBy->contains($ratedBy)) {
            $this->ratedBy[] = $ratedBy;
            $ratedBy->setProduct($this);
        }

        return $this;
    }

    public function removeRatedBy(UserProductRating $ratedBy): self
    {
        if ($this->ratedBy->contains($ratedBy)) {
            $this->ratedBy->removeElement($ratedBy);
            // set the owning side to null (unless already changed)
            if ($ratedBy->getProduct() === $this) {
                $ratedBy->setProduct(null);
            }
        }

        return $this;
    }

    public function getSeller(): ?User
    {
        return $this->seller;
    }

    public function setSeller(?User $seller): self
    {
        $this->seller = $seller;

        return $this;
    }


}
