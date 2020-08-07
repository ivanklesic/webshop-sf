<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProductView;
use DateTime;
use Exception;
use GraphAware\Neo4j\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;



class ProductController extends AbstractController
{


    /**
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route("/product/list", name="product_list")
     */
    public function listAction(EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $products = $entityManager->getRepository('App\Entity\Product')->findAll();

        return $this->render(
            'product/list.html.twig', [
            'products' => $products,
            'user' => $this->getUser()
        ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route("/myproducts/list", name="product_seller_list")
     */
    public function listSellerAction(EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_SELLER');

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $products = $entityManager->getRepository('App\Entity\Product')->getProductsofSeller($currentUser);

        return $this->render(
            'product/list.html.twig', [
            'products' => $products,
            'user' => $currentUser
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @param ClientInterface $client
     * @return Response
     * @Route ("/product/create", name="product_create")
     */
    public function createAction(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader, ClientInterface $client)
    {

        $product = new Product();

        $this->denyAccessUnlessGranted('create', $product);

        $form = $this->createForm('App\Form\ProductType', $product, [
            'product' => $product,
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $imageFile */
            $imageFile = $form['image']->getData();
            if ($imageFile) {
                $imageFileName = $fileUploader->upload($imageFile);
                $product->setImage($imageFileName);
            }

            /** @var User $currentUser */
            $currentUser = $this->getUser();

            $product->setSeller($currentUser);
            $product->setCreatedAt(new DateTime());

            $entityManager->persist($product);


            $parameters = [ 'productID' => $product->getId(),
                            'categoryID' => $product->getCategory()->getId(),
                            'name' => $product->getName(),
                            'proteinPercent' => $product->getProteinPercent(),
                            'lipidPercent' => $product->getLipidPercent(),
                            'carbohydratePercent' => $product->getCarbohydratePercent() ];

            $query = 'MATCH (category:Category {id: {categoryID}}) 
                      CREATE (product:Product {id: {productID}, name: {name}, proteinPercent: {proteinPercent}, lipidPercent: {lipidPercent}, carbohydratePercent: {carbohydratePercent}}) 
                      CREATE (product)-[rel:BELONGS_TO]->(category) 
                      WITH product 
                      ';

            foreach ($product->getConditions() as $index => $condition){
                $query .= 'MATCH (condition'.$index.':Condition {id: {condition'.$index.'ID}}) 
                          CREATE (product)-[rel:IS_DANGEROUS_TO]->(condition'.$index.') 
                          WITH product 
                          ';
                $parameters['condition'.$index.'ID'] = $condition->getId();
            }

            $query .= 'RETURN product';
            $client->run($query, $parameters);

            $entityManager->flush();
            $this->addFlash(
                'success',
                'Product created successfully!'
            );


            return $this->redirectToRoute('homepage');
        }


        return $this->render(
            'product/create.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'edit' => false

        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Product $product
     * @return Response
     * @Route ("/product/edit/{id}", name="product_edit")
     * @throws Exception
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, Product $product)
    {
        $this->denyAccessUnlessGranted('edit', $product);

        $form = $this->createForm('App\Form\ProductType', $product, [
            'product' => $product,
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product->setCreatedAt(new DateTime());
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Product edited successfully!'
            );
            return $this->redirectToRoute('product_details', ['id' => $product->getId()]);
        }


        return $this->render(
            'product/create.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'edit' => true

        ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Product $product
     * @return Response
     * @Route ("/product/details/{id}", name="product_details")
     */
    public function detailsAction(EntityManagerInterface $entityManager, Product $product)
    {

        $categories = $entityManager->getRepository('App:Category')->findAll();
        if($this->isGranted('ROLE_CUSTOMER'))
        {

            /** @var User $user */
            $user = $this->getUser();

            $previousView = $entityManager->getRepository('App:UserProductView')->findOneBy(['user' => $user, 'product' => $product]);
            if($previousView)
            {
                $previousView->setTime(new DateTime());
                $entityManager->persist($previousView);
            }
            else
            {
                $view = new UserProductView();
                $view->setTime(new DateTime());
                $product->addViewedBy($view);
                $user->addProductsViewed($view);
                $entityManager->persist($view);
                $entityManager->persist($product);
            }
            $entityManager->flush();
        }
        return $this->render(
            'product/details.html.twig', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Product $product
     * @param ClientInterface $client
     * @return Response
     * @Route ("/product/delete/{id}", name="product_delete")
     */
    public function deleteAction(EntityManagerInterface $entityManager, Product $product, ClientInterface $client)
    {
        $this->denyAccessUnlessGranted('delete', $product);

        $entityManager->remove($product);

        $query = 'MATCH (product:Product {id: {productID}}) 
                      DETACH DELETE product';

        $client->run($query, ['productID' => $product->getId()]);

        $entityManager->flush();

        $this->addFlash(
            'success',
            'Product deleted successfully!'
        );

        return $this->redirectToRoute('product_list');
    }
}
