<?php


namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Service\Cart;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use GraphAware\Neo4j\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class OrderController extends AbstractController
{
    /**
     * @Route("/cart/order/", name="cart_order")
     * @param EntityManagerInterface $entityManager
     * @param ClientInterface $client
     * @param Cart $cart
     * @return Response
     */
    public function addOrder(EntityManagerInterface $entityManager, ClientInterface $client, Cart $cart): Response
    {

        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');
        $cartArray = $cart->getCart();

        /**
         * Order $order
         */
        $order = new Order();
        $currentTime = new DateTime();
        $order->setDate(new DateTime());
        $orderPrice = 0;

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $order->setUser($currentUser);

        $productArray = [];
        if($cartArray != null){
            foreach ($cartArray as $item){
                $product = $entityManager->getRepository('App:Product')->find($item["id"]);
                $product->setQuantity($product->getQuantity() - $item["quantity"]);
                $productArray[]= ['name' => $item['name'], 'price' => $item['price'], 'quantity' => $item['quantity']];
                $entityManager->persist($product);
                $orderPrice += $item['price'] * $item['quantity'];

                $parameters = ['userID' => $currentUser->getId(), 'productID' => $product->getId(), 'dateTime' => $currentTime->format('Y-m-d H:i:s')];
                $query = 'MATCH (product:Product {productID: {productID}}) 
                      MATCH (user:User {userID: {userID}}) 
                      MERGE (user)-[rel:BOUGHT]->(product) 
                      SET rel.datetime = {dateTime} 
                      ';
                $client->run($query, $parameters);
            }
        }
        $order->setPrice($orderPrice);
        $order->setProducts($productArray);
        $cart->clearAction();
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

    /**
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route ("/orders", name="order_list")
     */

    public function listAction(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $orders = $entityManager->getRepository('App:Order')->findBy(['user' => $currentUser ]);

        return $this->render(
            'orders/list.html.twig', [
            'orders' => $orders,
        ]);

    }

    /**
     * @param Order $order
     * @return Response
     * @Route ("/orders/{id}", name="order_details")
     */

    public function detailsAction(Order $order): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if($order->getUser() !== $currentUser){
            return $this->redirectToRoute('order_list');
        }

        return $this->render(
            'orders/details.html.twig', [
            'products' => $order->getProducts(),
                'order' => $order
        ]);

    }

}