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
     * @ORM\OneToMany(targetEntity=UserProductView::class, mappedBy="user", orphanRemoval=true)
     */
    private $productsViewed;

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

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="user", orphanRemoval=true)
     */
    private $orders;

    /**
     * @ORM\ManyToOne(targetEntity=Diet::class, inversedBy="users")
     */
    private $activeDiet;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="sender")
     */
    private $messagesSent;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="recipient")
     */
    private $messagesReceived;

    public function __construct()
    {
        $this->conditions = new ArrayCollection();
        $this->productsViewed = new ArrayCollection();
        $this->productsRated = new ArrayCollection();
        $this->productsOnSale = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->messagesSent = new ArrayCollection();
        $this->messagesReceived = new ArrayCollection();

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
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    public function getActiveDiet(): ?Diet
    {
        return $this->activeDiet;
    }

    public function setActiveDiet(?Diet $activeDiet): self
    {
        $this->activeDiet = $activeDiet;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessagesSent(): Collection
    {
        return $this->messagesSent;
    }

    public function addMessagesSent(Message $messagesSent): self
    {
        if (!$this->messagesSent->contains($messagesSent)) {
            $this->messagesSent[] = $messagesSent;
            $messagesSent->setSender($this);
        }

        return $this;
    }

    public function removeMessagesSent(Message $messagesSent): self
    {
        if ($this->messagesSent->contains($messagesSent)) {
            $this->messagesSent->removeElement($messagesSent);
            // set the owning side to null (unless already changed)
            if ($messagesSent->getSender() === $this) {
                $messagesSent->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessagesReceived(): Collection
    {
        return $this->messagesReceived;
    }

    public function addMessagesReceived(Message $messagesReceived): self
    {
        if (!$this->messagesReceived->contains($messagesReceived)) {
            $this->messagesReceived[] = $messagesReceived;
            $messagesReceived->setRecipient($this);
        }

        return $this;
    }

    public function removeMessagesReceived(Message $messagesReceived): self
    {
        if ($this->messagesReceived->contains($messagesReceived)) {
            $this->messagesReceived->removeElement($messagesReceived);
            // set the owning side to null (unless already changed)
            if ($messagesReceived->getRecipient() === $this) {
                $messagesReceived->setRecipient(null);
            }
        }

        return $this;
    }


}
