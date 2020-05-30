<?php


namespace App\Controller;


use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route("/cat/list", name="cat_list")
     */
    public function listAction(EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categories = $entityManager->getRepository('AppBundle:Category')->findAll();

        return $this->render(
            'category/list.html.twig', [
            'categories' => $categories
            ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route ("/cat/create", name="cat_create")
     */
    public function createAction(Request $request, EntityManagerInterface $entityManager)
    {

        $category = new Category();

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm('App\Form\CategoryType', $category, [
            'entityManager' => $entityManager,
            'category' => $category,
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Category created successfully!'
            );


            return $this->redirectToRoute('homepage');
        }


        return $this->render(
            'product/create.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Category $category
     * @return Response
     * @Route ("/cat/edit/{id}", name="cat_edit")
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, Category $category)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm('AppBundle\Form\CategoryType', $category, [
            'entityManager' => $entityManager,
            'category' => $category,
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Category edited successfully!'
            );
            return $this->redirectToRoute('homepage');
        }


        return $this->render(
            'product/create.html.twig', [
            'form' => $form->createView(),
            'product' => $category
        ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Category $category
     * @return Response
     * @Route ("/product/delete/{id}", name="product_delete")
     */
    public function deleteAction(EntityManagerInterface $entityManager, Category $category)
    {
        $this->denyAccessUnlessGranted('delete', $category);

        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Category deleted successfully!'
        );

        return $this->redirectToRoute('product_list');
    }


}