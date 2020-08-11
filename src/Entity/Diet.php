<?php

namespace App\Entity;

use App\Repository\DietRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DietRepository::class)
 * @CustomAssert\RatioConstraint()
 */
class Diet
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
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=Product::class, mappedBy="diets")
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="activeDiet")
     */
    private $users;

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
    private $lipidPercent;

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

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->users = new ArrayCollection();
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

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setActiveDiet($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getActiveDiet() === $this) {
                $user->setActiveDiet(null);
            }
        }

        return $this;
    }

    public function __toString() {
        return $this->name;
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

    public function getLipidPercent(): ?int
    {
        return $this->lipidPercent;
    }

    public function setLipidPercent(int $lipidPercent): self
    {
        $this->lipidPercent = $lipidPercent;

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
}
