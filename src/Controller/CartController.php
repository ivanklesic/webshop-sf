<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartController extends AbstractController
{

    /**
     * @Route("/cart/add/", name="cart_add")
     * @param Request $request
     * @param SessionInterface $session
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */

    public function addToCart(Request $request, SessionInterface $session, EntityManagerInterface $entityManager){

        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');

        $productID = $request->request->get('productID');
        $quantity = $request->request->get('quantity');

        $product = $entityManager->getRepository('App:Product')->find($productID);

        if($product && $quantity){
            $itemArray = array($productID => array('name'=>$product->getName(), 'quantity'=>$quantity, 'price'=>$product->getPrice(), 'image'=>$product->getImage()));

            $cartArray = $session->get('cart', null);

            if($cartArray){
                if(in_array($productID, array_keys($cartArray))){
                    foreach($cartArray as $k => $v){
                        if($productID == $k){
                            if(empty($cartArray[$k]['quantity'])){
                                $cartArray[$k]['quantity'] = 0;
                            }

                            $cartArray[$k]['quantity'] += $quantity;

                            if($product->getQuantity() < $cartArray[$k]['quantity']){
                                $cartArray[$k]['quantity'] = $product->getQuantity();
                            }
                        }
                    }
                }
                else{
                    $cartArray = array_merge($cartArray, $itemArray);
                }
            }
            else{
                $cartArray = $itemArray;
            }
            $session->set('cart', $cartArray);

            return new JsonResponse(['msg' => $session->get('cart', false) ], 200);

        }
    }


}