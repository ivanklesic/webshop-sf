<?php


namespace App\Service;

use App\Entity\Product as Product;
use App\Entity\User as User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function getCart()
    {
        return $this->session->get('cart', null);
    }

    public function setCart($cartArray)
    {
        $this->session->set('cart', $cartArray);
    }

    public function getCartSize(){
        if($this->getCart() != null){
            return count($this->getCart());
        }
        return null;
    }

    public function addAction(Product $product,User $user,int $quantity)
    {
        $conflict = false;

        foreach($product->getConditions() as $condition){
            if($user->getConditions()->contains($condition)){
                $conflict = true;
                break;
            }
        }

        foreach($product->getDiets() as $diet){
            if($user->getActiveDiet() === $diet){
                $conflict = true;
                break;
            }
        }

        if($product && $quantity){
            $itemArray = array('id' => $product->getId(), 'name'=>$product->getName(), 'description'=>$product->getDescription(), 'quantity'=>$quantity, 'price'=>$product->getPrice(), 'image'=>$product->getImage(),  'conflict'=>$conflict);

            $cartArray = $this->getCart();

            $found = false;

            if($cartArray){
                foreach($cartArray as $k => $v){
                    if($product->getId() === $v['id']){
                        $found = true;
                        $cartArray[$k]['quantity'] += $quantity;

                        if($product->getQuantity() < $cartArray[$k]['quantity']){
                            $cartArray[$k]['quantity'] = $product->getQuantity();
                        }
                    }
                }

                if (!$found){
                    $cartArray[] = $itemArray;
                }
            }
            else{
                $cartArray = array();
                $cartArray[] = $itemArray;
            }
            $this->setCart($cartArray);

            return 200;

        }
        return 400;
    }

    public function removeAction(Product $product)
    {
        $message = [];
        $cartArray = $this->getCart();
        if($product){

            if($cartArray){
                foreach($cartArray as $k => $v){
                    if($product->getId() == $v['id']){
                        unset($cartArray[$k]);
                        $message['status'] = 'success';
                        $message['userMessage'] = 'Product removed from cart';

                        $this->setCart($cartArray);

                    }
                }
            }
            else{
                $message['status'] = 'warning';
                $message['userMessage'] = 'Cart is empty';
            }
        }
        else{
            $message['status'] = 'warning';
            $message['userMessage'] = 'Product not found';
        }

        return $message;
    }

    public function clearAction()
    {
        $message = [];
        $this->setCart(null);
        $message['status'] = 'success';
        $message['userMessage'] = 'Cart cleared';
        return $message;
    }

}