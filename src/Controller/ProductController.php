<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        $products = $entityManager->getRepository('AppBundle:Product')->findAll();

        return $this->render(
            'product/list.html.twig', [
            'products' => $products,
            'user' => $this->getUser()
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     * @return Response
     * @Route ("/product/create", name="product_create")
     */
    public function createAction(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader)
    {

        $product = new Product();

        $this->denyAccessUnlessGranted('create', $product);

        $form = $this->createForm('App\Form\ProductType', $product, [
            'entityManager' => $entityManager,
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
            $product->setCreatedAt(new \DateTime());

            $entityManager->persist($product);
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
     * @throws \Exception
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, Product $product)
    {
        $this->denyAccessUnlessGranted('edit', $product);

        $form = $this->createForm('AppBundle\Form\ProductType', $product, [
            'entityManager' => $entityManager,
            'product' => $product,
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product->setCreatedAt(new \DateTime());
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
        return $this->render(
            'product/details.html.twig', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Product $product
     * @return Response
     * @Route ("/product/delete/{id}", name="product_delete")
     * @throws \Exception
     */
    public function deleteAction(EntityManagerInterface $entityManager, Product $product)
    {
        $this->denyAccessUnlessGranted('delete', $product);

        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Product deleted successfully!'
        );

        return $this->redirectToRoute('product_list');
    }
}
