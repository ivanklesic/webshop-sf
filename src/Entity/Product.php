<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @CustomAssert\RatioConstraint()
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
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Product name cannot be longer than {{ limit }} characters"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(
     *      max = 500,
     *      maxMessage = "Product description cannot be longer than {{ limit }} characters"
     * )
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *      min = 0,
     *      max = 9999,
     *      minMessage = "Quantity cannot be less than {{ limit }}",
     *      maxMessage = "Quantity cannot be bigger than {{ limit }}"
     * )
     */
    private $quantity;

    /**
     * @ORM\Column(type="float")
     * @Assert\Range(
     *      min = 0,
     *      max = 9999,
     *      minMessage = "Price cannot be less than {{ limit }}",
     *      maxMessage = "Price cannot be bigger than {{ limit }}"
     * )
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity=UserProductView::class, mappedBy="product", orphanRemoval=true)
     */
    private $viewedBy;


    /**
     * @ORM\OneToMany(targetEntity=UserProductRating::class, mappedBy="product", orphanRemoval=true)
     */
    private $ratedBy;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="productsOnSale")
     * @ORM\JoinColumn(nullable=false)
     */
    private $seller;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Category;

    /**
     * @ORM\ManyToMany(targetEntity=Condition::class, inversedBy="products")
     */
    private $conditions;


    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *      min = 0,
     *      max = 100,
     *      minMessage = "Percent cannot be less than {{ limit }}",
     *      maxMessage = "Percent cannot be bigger than {{ limit }}"
     * )
     */
    private $proteinPercent;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *      min = 0,
     *      max = 100,
     *      minMessage = "Percent cannot be less than {{ limit }}",
     *      maxMessage = "Percent cannot be bigger than {{ limit }}"
     * )
     */
    private $carbohydratePercent;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(
     *      min = 0,
     *      max = 100,
     *      minMessage = "Percent cannot be less than {{ limit }}",
     *      maxMessage = "Percent cannot be bigger than {{ limit }}"
     * )
     */
    private $lipidPercent;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(
     *      min = 0,
     *      max = 60,
     *      minMessage = "Emission cannot be less than {{ limit }}",
     *      maxMessage = "Emission cannot be bigger than {{ limit }}"
     * )
     */
    private $gasEmission;

    /**
     * @ORM\Column(type="datetime")
     */

    private $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity=Order::class, mappedBy="products")
     */
    private $orders;

    /**
     * @ORM\ManyToMany(targetEntity=Diet::class, mappedBy="products")
     */
    private $diets;

    public function __construct()
    {
        $this->viewedBy = new ArrayCollection();
        $this->ratedBy = new ArrayCollection();
        $this->conditions = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->diets = new ArrayCollection();
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

    public function getCategory(): ?Category
    {
        return $this->Category;
    }

    public function setCategory(?Category $Category): self
    {
        $this->Category = $Category;

        return $this;
    }

    /**
     * @return Collection|Condition[]
     */
    public function getConditions(): Collection
    {
        return $this->conditions;
    }

    public function addCondition(Condition $condition): self
    {
        if (!$this->conditions->contains($condition)) {
            $this->conditions[] = $condition;
        }

        return $this;
    }

    public function removeCondition(Condition $condition): self
    {
        if ($this->conditions->contains($condition)) {
            $this->conditions->removeElement($condition);
        }

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getProteinPercent(): ?int
    {
        return $this->proteinPercent;
    }

    public function setProteinPercent(int $proteinPercent): self
    {
        $this->proteinPercent = $proteinPercent;

        return $this;
    }

    public function getCarbohydratePercent(): ?int
    {
        return $this->carbohydratePercent;
    }

    public function setCarbohydratePercent(int $carbohydratePercent): self
    {
        $this->carbohydratePercent = $carbohydratePercent;

        return $this;
    }

    public function getLipidPercent(): ?int
    {
        return $this->lipidPercent;
    }

    public function setLipidPercent(int $lipidPercent): self
    {
        $this->lipidPercent = $lipidPercent;

        return $this;
    }

    public function getGasEmission(): ?int
    {
        return $this->gasEmission;
    }

    public function setGasEmission(int $gasEmission): self
    {
        $this->gasEmission = $gasEmission;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->addProduct($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            $order->removeProduct($this);
        }

        return $this;
    }

    /**
     * @return Collection|Diet[]
     */
    public function getDiets(): Collection
    {
        return $this->diets;
    }

    public function addDiet(Diet $diet): self
    {
        if (!$this->diets->contains($diet)) {
            $this->diets[] = $diet;
            $diet->addProduct($this);
        }

        return $this;
    }

    public function removeDiet(Diet $diet): self
    {
        if ($this->diets->contains($diet)) {
            $this->diets->removeElement($diet);
            $diet->removeProduct($this);
        }

        return $this;
    }


}
