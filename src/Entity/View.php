<?php


namespace App\Entity;

use GraphAware\Neo4j\OGM\Annotations as OGM;


/**
 *
 * @OGM\RelationshipEntity(type="VIEWED")
 */
class View
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
     * @var string
     *
     * @OGM\Property(type="string")
     */
    protected $time;

    public function __construct(User $user, Product $product, $time)
    {
        $this->user = $user;
        $this->product = $product;
        $this->time = $time;
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
     * @param $time
     */
    public function updateTime($time){
        $this->time = $time;
    }




}