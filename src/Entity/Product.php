<?php


namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;
use PhpParser\Node\Expr\Cast\Object_;


/**
 * @OGM\Node(label="Product")
 */
class Product
{
    /**
     * @var int
     * @OGM\GraphId()
     */
    protected $id;

    /**
     * @var string
     * @OGM\Property(type="string")
     *
     */
    protected $name;

    /**
     * @var string
     * @OGM\Property(type="string")
     *
     */
    protected $category;

    /**
     * @var string
     * @OGM\Property(type="string")
     */
    protected $description;

    /**
     * @var string
     * @OGM\Property(type="string")
     */
    protected $image;

    /**
     * @var ArrayCollection
     * @OGM\Property(type="array")
     */
    protected $conditions;


    /**
     * @var int
     * @OGM\Property(type="int")
     */
    protected $quantity;

    /**
     * @var User
     *
     * @OGM\Relationship(type="SELLING", direction="INCOMING", collection=false, mappedBy="itemsOnSale", targetEntity="User")
     */
    protected $seller;

    /**
     * @var Collection
     *
     * @OGM\Relationship(relationshipEntity="Rating", type="RATED", direction="INCOMING", collection=true, mappedBy="product")
     */
    protected $ratings;


    /**
     * @var Collection
     *
     * @OGM\Relationship(relationshipEntity="Purchase", type="BOUGHT", direction="INCOMING", collection=true, mappedBy="product")
     */
    protected $buyers;

    /**
     * @var Collection
     *
     * @OGM\Relationship(relationshipEntity="View", type="VIEWED", direction="INCOMING", collection=true, mappedBy="product")
     */
    protected $viewers;


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
        $this->conditions = new ArrayCollection();
        $this->ratings = new Collection();
        $this->buyers = new Collection();
        $this->viewers = new Collection();
    }


    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
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


    /**
     * @return Collection
     */

    public function getRatings()
    {
        return $this->ratings;
    }

    /**
     * @param User $user
     */
    public function setSeller(User $user){
        $this->seller = $user;
    }

    /**
     * @return User
     */
    public function getSeller(){
        return $this->seller;
    }





}