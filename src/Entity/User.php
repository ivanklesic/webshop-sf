<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
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
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $diet;


    /**
     * @ORM\OneToMany(targetEntity=UserProductView::class, mappedBy="user", orphanRemoval=true)
     */
    private $productsViewed;

    /**
     * @ORM\OneToMany(targetEntity=UserProductPurchase::class, mappedBy="user", orphanRemoval=true)
     */
    private $productsBought;

    /**
     * @ORM\OneToMany(targetEntity=UserProductRating::class, mappedBy="user", orphanRemoval=true)
     */
    private $productsRated;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="seller", orphanRemoval=true)
     */
    private $productsOnSale;

    /**
     * @ORM\ManyToMany(targetEntity=Condition::class, mappedBy="users")
     */
    private $conditions;




    public function __construct()
    {
        $this->conditions = new ArrayCollection();
        $this->productsViewed = new ArrayCollection();
        $this->productsBought = new ArrayCollection();
        $this->productsRated = new ArrayCollection();
        $this->productsOnSale = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return [$this->role];
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getDiet(): ?string
    {
        return $this->diet;
    }

    public function setDiet(?string $diet): self
    {
        $this->diet = $diet;

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

    public function eraseCredentials()
    {
        $this->password = null;
        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
        return null;
    }

    /**
     * @return Collection|UserProductView[]
     */
    public function getProductsViewed(): Collection
    {
        return $this->productsViewed;
    }

    public function addProductsViewed(UserProductView $productsViewed): self
    {
        if (!$this->productsViewed->contains($productsViewed)) {
            $this->productsViewed[] = $productsViewed;
            $productsViewed->setUser($this);
        }

        return $this;
    }

    public function removeProductsViewed(UserProductView $productsViewed): self
    {
        if ($this->productsViewed->contains($productsViewed)) {
            $this->productsViewed->removeElement($productsViewed);
            // set the owning side to null (unless already changed)
            if ($productsViewed->getUser() === $this) {
                $productsViewed->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserProductPurchase[]
     */
    public function getProductsBought(): Collection
    {
        return $this->productsBought;
    }

    public function addProductsBought(UserProductPurchase $productsBought): self
    {
        if (!$this->productsBought->contains($productsBought)) {
            $this->productsBought[] = $productsBought;
            $productsBought->setUser($this);
        }

        return $this;
    }

    public function removeProductsBought(UserProductPurchase $productsBought): self
    {
        if ($this->productsBought->contains($productsBought)) {
            $this->productsBought->removeElement($productsBought);
            // set the owning side to null (unless already changed)
            if ($productsBought->getUser() === $this) {
                $productsBought->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserProductRating[]
     */
    public function getProductsRated(): Collection
    {
        return $this->productsRated;
    }

    public function addProductsRated(UserProductRating $productsRated): self
    {
        if (!$this->productsRated->contains($productsRated)) {
            $this->productsRated[] = $productsRated;
            $productsRated->setUser($this);
        }

        return $this;
    }

    public function removeProductsRated(UserProductRating $productsRated): self
    {
        if ($this->productsRated->contains($productsRated)) {
            $this->productsRated->removeElement($productsRated);
            // set the owning side to null (unless already changed)
            if ($productsRated->getUser() === $this) {
                $productsRated->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProductsOnSale(): Collection
    {
        return $this->productsOnSale;
    }

    public function addProductsOnSale(Product $productsOnSale): self
    {
        if (!$this->productsOnSale->contains($productsOnSale)) {
            $this->productsOnSale[] = $productsOnSale;
            $productsOnSale->setSeller($this);
        }

        return $this;
    }

    public function removeProductsOnSale(Product $productsOnSale): self
    {
        if ($this->productsOnSale->contains($productsOnSale)) {
            $this->productsOnSale->removeElement($productsOnSale);
            // set the owning side to null (unless already changed)
            if ($productsOnSale->getSeller() === $this) {
                $productsOnSale->setSeller(null);
            }
        }

        return $this;
    }



}
