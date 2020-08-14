<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProductRating;
use App\Entity\UserProductView;
use DateTime;
use GraphAware\Neo4j\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            'user' => $currentUser,
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
            $entityManager->flush();

            $parameters = [ 'productID' => $product->getId(),
                            'categoryID' => $product->getCategory()->getId(),
                            'name' => $product->getName(),
                            'proteinPercent' => $product->getProteinPercent(),
                            'lipidPercent' => $product->getLipidPercent(),
                            'carbohydratePercent' => $product->getCarbohydratePercent(),
                            'emission' => $product->getGasEmission()
            ];

            $query = 'MATCH (category:Category {categoryID: {categoryID}}) 
                      CREATE (product:Product {productID: {productID}, name: {name}, proteinPercent: {proteinPercent}, lipidPercent: {lipidPercent}, carbohydratePercent: {carbohydratePercent}, emission: {emission}}) 
                      CREATE (product)-[rel:BELONGS_TO]->(category) 
                      WITH product ';

            foreach ($product->getConditions() as $index => $condition){
                $query .= 'MATCH (condition'.$index.':Condition {conditionID: {condition'.$index.'ID}}) 
                          CREATE (product)-[rel:IS_DANGEROUS_TO]->(condition'.$index.') 
                          WITH product 
                          ';
                $parameters['condition'.$index.'ID'] = $condition->getId();
            }

            foreach ($product->getDiets() as $index => $diet){
                $query .= 'MATCH (diet'.$index.':Diet {dietID: {diet'.$index.'ID}}) 
                          CREATE (product)-[rel:IS_EXCLUDED_FROM]->(diet'.$index.') 
                          WITH product ';
                $parameters['diet'.$index.'ID'] = $diet->getId();
            }

            $query .= 'RETURN product';
            $client->run($query, $parameters);

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
     * @param ClientInterface $client
     * @return Response
     * @Route ("/product/edit/{id}", name="product_edit")
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, Product $product, ClientInterface $client)
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



            $parameters = [ 'productID' => $product->getId(),
                            'categoryID' => $product->getCategory()->getId(),
                            'name' => $product->getName(),
                            'proteinPercent' => $product->getProteinPercent(),
                            'lipidPercent' => $product->getLipidPercent(),
                            'carbohydratePercent' => $product->getCarbohydratePercent(),
                            'emission' => $product->getGasEmission()];

            $query = 'MATCH (product:Product {productID: {productID}}) 
                      MATCH (product)-[relCategory:BELONGS_TO]->() 
                      DELETE relCategory 
                      WITH product 
                      MATCH (product)-[relCondition:IS_DANGEROUS_TO]->() 
                      DELETE relCondition 
                      WITH product 
                      MATCH (product)-[relDiet:IS_EXCLUDED_FROM]->() 
                      DELETE relDiet 
                      ';

            $client->run($query, $parameters);

            $query =  'MATCH (product:Product {productID: {productID}}) 
                       SET product.lipidPercent = {lipidPercent} 
                       SET product.name = {name} 
                       SET product.proteinPercent = {proteinPercent} 
                       SET product.carbohydratePercent = {carbohydratePercent} 
                       SET product.emission = {emission}
                       ';

            $client->run($query, $parameters);

            $query =  'MATCH (product:Product {productID: {productID}}) 
                       MATCH (category:Category {categoryID: {categoryID}}) 
                       CREATE (product)-[rel:BELONGS_TO]->(category) 
                       ';

            $client->run($query, $parameters);

            foreach ($product->getConditions() as $condition){
                $query = 'MATCH (condition:Condition {conditionID: {conditionID}}) 
                          MATCH (product:Product {productID: {productID}}) 
                          CREATE (product)-[rel:IS_DANGEROUS_TO]->(condition) 
                           ';
                $parameters['conditionID'] = $condition->getId();
                $client->run($query, $parameters);
            }

            foreach ($product->getDiets() as $diet){
                $query = 'MATCH (diet:Diet {dietID: {dietID}}) 
                          MATCH (product:Product {productID: {productID}}) 
                          CREATE (product)-[rel:IS_EXCLUDED_FROM]->(diet) 
                          ';
                $parameters['dietID'] = $diet->getId();
                $client->run($query, $parameters);
            }

            $this->addFlash(
                'success',
                'Product edited successfully!'
            );
            return $this->redirectToRoute('homepage');
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
     * @param ClientInterface $client
     * @return Response
     * @Route ("/product/details/{id}", name="product_details")
     */
    public function detailsAction(EntityManagerInterface $entityManager, Product $product, ClientInterface $client)
    {

        $categories = $entityManager->getRepository('App:Category')->findAll();

        $boughtByUser = false;
        $previousRatingByUser = null;
        $previousRatingsForProduct = null;
        $sumRating = 0;
        $averageRating = null;
        $userRating = null;

        if($this->isGranted('ROLE_CUSTOMER'))
        {

            /** @var User $user */
            $user = $this->getUser();

            $currentTime = new DateTime();

            $previousView = $entityManager->getRepository('App:UserProductView')->findOneBy(['user' => $user, 'product' => $product]);
            if($previousView)
            {
                $previousView->setTime($currentTime);
                $entityManager->persist($previousView);
            }
            else
            {
                $view = new UserProductView();
                $view->setTime($currentTime);
                $product->addViewedBy($view);
                $user->addProductsViewed($view);
                $entityManager->persist($view);
            }
            $entityManager->flush();

            $parameters = ['userID' => $user->getId(), 'productID' => $product->getId(), 'dateTime' => $currentTime->format('Y-m-d H:i:s')];

            $query = 'MATCH (product:Product {productID: {productID}}) 
                      MATCH (user:User {userID: {userID}}) 
                      MERGE (user)-[rel:VIEWED]->(product) 
                      SET rel.datetime = {dateTime} 
                      ';

            $client->run($query, $parameters);



            foreach ($user->getOrders() as $userOrder){
                foreach($userOrder->getProducts() as $boughtProduct){
                    if($product === $boughtProduct){
                        $boughtByUser = true;
                        break;
                    }
                }
            }

            $previousRatingByUser = $entityManager->getRepository('App:UserProductRating')->findOneBy(['user' => $user, 'product' => $product]);
            $previousRatingsForProduct = $entityManager->getRepository('App:UserProductRating')->findBy(['product' => $product]);

            foreach($previousRatingsForProduct as $productRating){
                $sumRating += $productRating->getRating();
            }

            if(count($previousRatingsForProduct)){
                $averageRating = $sumRating/count($previousRatingsForProduct);
            }

            if($previousRatingByUser){
                $userRating = $previousRatingByUser->getRating();
            }


        }
        return $this->render(
            'product/details.html.twig', [
            'product' => $product,
            'categories' => $categories,
            'bought' => $boughtByUser,
            'userRating' => $userRating,
            'averageRating' => $averageRating
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


        $parameters = ['productID' => $product->getId()];

        $entityManager->remove($product);
        $entityManager->flush();

        $query = 'MATCH (product:Product {productID: {productID}}) 
                  DETACH DELETE product';

        $client->run($query, $parameters);

        $this->addFlash(
            'success',
            'Product deleted successfully!'
        );

        return $this->redirectToRoute('homepage');
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ClientInterface $client
     * @return JsonResponse
     * @Route ("/product/rate", name="product_rate")
     */
    public function rateProduct(Request $request, EntityManagerInterface $entityManager, ClientInterface $client)
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');

        $productID = $request->request->get('productID');
        $rating = $request->request->get('rating');

        $product = $entityManager->getRepository('App:Product')->find($productID);

        if($product && $rating){

            /** @var User $user */
            $user = $this->getUser();

            $previousRating = $entityManager->getRepository('App:UserProductRating')->findOneBy(['user' => $user, 'product' => $product]);

            if($previousRating)
            {
                $previousRating->setRating($rating);
                $entityManager->persist($previousRating);
            }
            else
            {
                $userRating = new UserProductRating();
                $product->addRatedBy($userRating);
                $userRating->setRating($rating);
                $user->addProductsRated($userRating);
                $entityManager->persist($userRating);
            }
            $entityManager->flush();

            $parameters = ['userID' => $user->getId(), 'productID' => $product->getId(), 'rating' => $rating];

            $query = 'MATCH (product:Product {productID: {productID}}) 
                      MATCH (user:User {userID: {userID}}) 
                      MERGE (user)-[rel:RATED]->(product) 
                      SET rel.rating = {rating} 
                      ';

            $client->run($query, $parameters);

            return new JsonResponse(['msg' => "Your rating was saved" ], 200);

        }
        return new JsonResponse(['msg' => 'There was an error.' ], 400);

    }


}
