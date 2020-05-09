<?php


namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;



/**
 * @OGM\Node(label="User")
 */

class User implements UserInterface
{

    /**
     * @var int
     * @OGM\GraphId()
     */
    protected $id;

    /**
     * @var string
     * @OGM\Property(type="string")
     */
    protected $username;

    /**
     * @var string
     * @OGM\Property(type="string")
     *
     */
    protected $password;

    /**
     * @var string
     * @OGM\Property(type="string")
     */
    protected $role;

    /**
     * @var string
     * @OGM\Property(type="string")
     *
     */
    protected $fullname;

    /**
     * @var string
     * @OGM\Property(type="string")
     *
     */
    protected $diet;

    /**
     * @var ArrayCollection
     * @OGM\Property(type="array")
     */
    protected $conditions;

    /**
     * @var Collection
     *
     * @OGM\Relationship(relationshipEntity="Bought", type="BOUGHT", direction="OUTGOING", collection=true, mappedBy="user")
     */
    protected $itemsBought;

    /**
     * @var Collection
     *
     * @OGM\Relationship(relationshipEntity="Viewed", type="VIEWED", direction="OUTGOING", collection=true, mappedBy="user")
     */
    protected $itemsViewed;

    /**
     * @var Collection
     *
     * @OGM\Relationship(relationshipEntity="Rating", type="RATED", direction="OUTGOING", collection=true, mappedBy="user")
     */
    protected $ratings;

    /**
     * @var Product[]Collection
     *
     * @OGM\Relationship(type="SELLING", direction="OUTGOING", collection=true, mappedBy="seller", targetEntity="Product")
     */
    protected $itemsOnSale;

    /**
     * Get id.
     *
     * @return int
     */

    public function getId()
    {
        return $this->id;
    }

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->itemsBought = new Collection();
        $this->itemsViewed = new Collection();
        $this->ratings = new Collection();
        $this->conditions = new ArrayCollection();
        $this->itemsOnSale = new Collection();
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function addBought(Product $product)
    {
        $this->itemsBought[] = $product;

        return $this;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function removeBought(Product $product)
    {
        return $this->itemsBought->removeElement($product);
    }

    /**
     * @return Product[]|Collection
     */
    public function getBought()
    {
        return $this->itemsBought;
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function addViewed(Product $product)
    {
        $this->itemsViewed[] = $product;

        return $this;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function removeViewed(Product $product)
    {
        return $this->itemsViewed->removeElement($product);
    }

    /**
     * @return Product[]|Collection
     */
    public function getViewed()
    {
        return $this->itemsViewed;
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function addRating(Product $product)
    {
        $this->ratings[] = $product;

        return $this;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function removeRating(Product $product)
    {
        return $this->ratings->removeElement($product);
    }

    /**
     * @return Collection
     *
     */

    public function getRatings()
    {
        return $this->ratings;
    }

    /**
     * @param $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return [$this->role];
    }

    /**
     * @param $diet
     * @return $this
     */
    public function setDiet($diet)
    {
        $this->diet = $diet;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiet()
    {
        return $this->diet;
    }

    /**
     * @param $fullname
     * @return $this
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * @param $condition
     * @return $this
     */
    public function addCondition($condition)
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * @param $condition
     * @return bool
     */
    public function removeCondition($condition)
    {
        return $this->conditions->removeElement($condition);
    }

    /**
     * @return ArrayCollection
     */
    public function getConditions()
    {
        return $this->conditions;
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


    public function rateProductWithScore(Product $product, $score)
    {
        $rating = new Rating($this, $product, (int) $score);
        $this->getRatings()->add($rating);
        $product->getRatings()->add($rating);
    }
}