<?php


namespace App\Controller;


use App\Entity\Product;
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
        $quantity = (int)$request->request->get('quantity');

        $product = $entityManager->getRepository('App:Product')->find($productID);

        if($product && $quantity){
            $conditionArray = array();
            foreach ($product->getConditions() as $condition){
                $conditionArray []= $condition->getId();
            }
            $itemArray = array('id' => $productID, 'name'=>$product->getName(), 'quantity'=>$quantity, 'price'=>$product->getPrice(), 'image'=>$product->getImage(), 'conditions'=>$conditionArray);

            $cartArray = $session->get('cart', null);

            $found = false;

            if($cartArray){
                    foreach($cartArray as $k => $v){
                        if($productID === $v['id']){
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
            $session->set('cart', $cartArray);

            return new JsonResponse(['msg' => $session->get('cart', false) ], 200);

        }
        return new JsonResponse(['msg' => 'There was an error.' ], 400);
    }

    /**
     * @Route("/cart/remove/{id}", name="cart_remove")
     * @param SessionInterface $session
     * @param Product $product
     * @return Response
     */
    public function removeFromCart(SessionInterface $session, Product $product){

        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');
        $cartArray = $session->get('cart', null);
        if($product){
            $productID = $product->getId();

            if($cartArray){
                    foreach($cartArray as $k => $v){
                        if($productID == $v['id']){
                            unset($cartArray[$k]);
                            $this->addFlash(
                                'success',
                                'Product removed from cart'
                            );
                            $session->set('cart', $cartArray);

                        }
                    }
            }
            else{
                $this->addFlash(
                    'warning',
                    'Cart is empty'
                );
            }
        }
        else{
            $this->addFlash(
                'warning',
                'Product not found'
            );
        }
        return $this->render(
            'cart/cart.html.twig', [
            'cart' => $cartArray
        ]);

    }

    /**
     * @Route("/cart/clear/", name="cart_clear")
     * @param SessionInterface $session
     * @return Response
     */
    public function clearCart(SessionInterface $session){

        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');
        $session->set('cart', null);
        $this->addFlash(
            'success',
            'Cart cleared'
        );
        return $this->render(
            'cart/cart.html.twig', [
            'cart' => null
        ]);
    }

    /**
     * @Route("/cart/list/", name="cart_list")
     * @param SessionInterface $session
     * @return Response
     */
    public function listCart(SessionInterface $session){

        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');

        $cartArray = $session->get('cart', null);

        return $this->render(
            'cart/cart.html.twig', [
                'cart' => $cartArray
            ]);

    }


}