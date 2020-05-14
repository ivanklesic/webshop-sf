<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("username")
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
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Length(
     *      min = 4,
     *      max = 50,
     *      minMessage = "Your first name must be at least {{ limit }} characters long",
     *      maxMessage = "Your first name cannot be longer than {{ limit }} characters"
     * )
     * @Assert\NotBlank()
     *
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * * @Assert\Length(
     *      min = 4,
     *      max = 50,
     *      minMessage = "Your password must be at least {{ limit }} characters long",
     *      maxMessage = "Your password cannot be longer than {{ limit }} characters"
     * )
     *
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex(
     *     pattern="/^([a-zA-Z]+\s)*[a-zA-Z]+$/",
     *     message="Name must start with a letter and can only contain letters and spaces"
     * )
     * @Assert\NotBlank()
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex(
     *     pattern="/^([a-zA-Z]+\s)*[a-zA-Z]+$/",
     *     message="Name must start with a letter and can only contain letters and spaces"
     * )
     * @Assert\NotBlank()
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
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
     * @ORM\ManyToMany(targetEntity=Condition::class, inversedBy="users")
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
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

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $condition->addUser($this);
        }

        return $this;
    }

    public function removeCondition(Condition $condition): self
    {
        if ($this->conditions->contains($condition)) {
            $this->conditions->removeElement($condition);
            $condition->removeUser($this);
        }

        return $this;
    }


}
