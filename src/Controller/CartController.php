<?php


namespace App\Controller;


use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use DateTime;
use GraphAware\Neo4j\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
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

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $conflict = false;

        foreach($product->getConditions() as $condition){
            if($currentUser->getConditions()->contains($condition)){
                $conflict = true;
                break;
            }
        }

        foreach($product->getDiets() as $diet){
            if($currentUser->getActiveDiet() === $diet){
                $conflict = true;
                break;
            }
        }

        if($product && $quantity){
            $itemArray = array('id' => $productID, 'name'=>$product->getName(), 'description'=>$product->getDescription(), 'quantity'=>$quantity, 'price'=>$product->getPrice(), 'image'=>$product->getImage(),  'conflict'=>$conflict);

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



            return new JsonResponse(['msg' => "Added product to cart", 'size' => count($cartArray) ], 200);

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
            'cart' => $cartArray,
            'cartSize' => count($cartArray)
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
            'cart' => null,
            'cartSize' => 0
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


        $cartSize = null;

        if($cartArray){
            foreach($cartArray as $item){
                if($item['conflict']){
                    $this->addFlash(
                        'warning',
                        'Your cart contains product(s) that conflict with Your diet and/or medical condition profile. Please review products in the cart before proceeding to checkout.'
                    );
                    break;
                }
            }
            $cartSize = count($cartArray);
        }

        return $this->render(
            'cart/cart.html.twig', [
                'cart' => $cartArray,
                'cartSize' => $cartSize
            ]);

    }

    /**
     * @Route("/cart/order/", name="cart_order")
     * @param SessionInterface $session
     * @param EntityManagerInterface $entityManager
     * @param ClientInterface $client
     * @return Response
     */
    public function addOrder(SessionInterface $session, EntityManagerInterface $entityManager, ClientInterface $client){

        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');
        $cartArray = $session->get('cart', null);
        $order = new Order();
        $currentTime = new DateTime();
        $order->setDate(new DateTime());

        /** @var User $currentUser */
        $currentUser = $this->getUser();


        $order->setUser($currentUser);
        if($cartArray != null){

            foreach ($cartArray as $item){
                $product = $entityManager->getRepository('App:Product')->find($item["id"]);
                $product->setQuantity($product->getQuantity() - $item["quantity"]);
                $order->addProduct($product);
                $entityManager->persist($product);

                $parameters = ['userID' => $currentUser->getId(), 'productID' => $product->getId(), 'dateTime' => $currentTime->format('Y-m-d H:i:s')];

                $query = 'MATCH (product:Product {productID: {productID}}) 
                      MATCH (user:User {userID: {userID}}) 
                      MERGE (user)-[rel:BOUGHT]->(product) 
                      SET rel.datetime = {dateTime} 
                      ';

                $client->run($query, $parameters);

            }
        }
        $session->set('cart', null);
        $entityManager->persist($order);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Your purchase was completed successfully and your cart has been cleared'
        );
        return $this->render(
            'cart/cart.html.twig', [
            'cart' => null,
            'cartSize' => 0
        ]);

    }


}