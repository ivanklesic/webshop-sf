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
use App\Service\Cart;

class CartController extends AbstractController
{

    /**
     * @Route("/cart/add/", name="cart_add")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Cart $cart
     * @return JsonResponse
     */

    public function addToCart(Request $request, EntityManagerInterface $entityManager, Cart $cart): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');
        $productID = $request->request->get('productID');
        $quantity = (int)$request->request->get('quantity');

        $product = $entityManager->getRepository('App:Product')->find($productID);
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $status = null;
        if($product && $quantity){
            $status = $cart->addAction($product, $currentUser, $quantity);
        }
        return new JsonResponse(['size' => $cart->getCartSize()],$status);
    }

    /**
     * @Route("/cart/remove/{id}", name="cart_remove")
     * @param Product $product
     * @param Cart $cart
     * @return Response
     */
    public function removeFromCart(Product $product, Cart $cart): Response
    {

        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');
        $message = $cart->removeAction($product);
        if($message)
        {
            $this->addFlash(
                $message['status'],
                $message['userMessage']
            );
        }
        return $this->redirectToRoute('cart_list');
    }

    /**
     * @Route("/cart/clear/", name="cart_clear")
     * @param Cart $cart
     * @return Response
     */
    public function clearCart(Cart $cart): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');
        $message = $cart->clearAction();
        if($message)
        {
            $this->addFlash(
                $message['status'],
                $message['userMessage']
            );
        }
        return $this->redirectToRoute('cart_list');
    }

    /**
     * @Route("/cart/list/", name="cart_list")
     * @param Cart $cart
     * @return Response
     */
    public function listCart(Cart $cart): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');
        $cartArray = $cart->getCart();
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
        }
        return $this->render(
            'cart/cart.html.twig', [
                'cart' => $cartArray,
                'cartSize' => $cart->getCartSize()
            ]);

    }
}