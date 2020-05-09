<?php


namespace App\Entity;

use GraphAware\Neo4j\OGM\Annotations as OGM;


/**
 *
 * @OGM\RelationshipEntity(type="RATED")
 */
class Rating
{
    /**
     * @var int
     *
     * @OGM\GraphId()
     */
    protected $id;

    /**
     * @var User
     *
     * @OGM\StartNode(targetEntity="User")
     */
    protected $user;

    /**
     * @var Product
     *
     * @OGM\EndNode(targetEntity="Product")
     */
    protected $product;

    /**
     * @var int
     *
     * @OGM\Property(type="int")
     */
    protected $score;

    public function __construct(User $user, Product $product, $score)
    {
        $this->user = $user;
        $this->product = $product;
        $this->score = $score;
    }

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
     * @return User
     */
    public function getUser(){
        return $this->user;
    }

    /**
     * @return Product
     */
    public function getProduct(){
        return $this->product;
    }

    /**
     * @param $score
     */

    public function updateScore($score){
        $this->score = $score;
    }


}