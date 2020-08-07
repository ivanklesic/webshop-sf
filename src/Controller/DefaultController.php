<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function indexAction(Request $request, EntityManagerInterface $entityManager)
    {
        if ($this->isGranted('ROLE_CUSTOMER')){

            $products = $entityManager->getRepository('App:Product')->findAll();
            $categories = $entityManager->getRepository('App:Category')->findAll();

            return $this->render('index.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                'products' => $products,
                'categories' => $categories
            ]);
        }
        else if ($this->isGranted('ROLE_SELLER')){

            return $this->redirectToRoute('product_seller_list');
        }
        return $this->render('index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR
        ]);
    }

    /**
     * @Route("/category/{id}", name="category")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Category $category
     * @return Response
     */
    public function categoryAction(Request $request, EntityManagerInterface $entityManager, Category $category)
    {
        if ($this->isGranted('ROLE_CUSTOMER')){

            $products = $entityManager->getRepository('App:Product')->getProductsOfCategory($category->getName());
            $categories = $entityManager->getRepository('App:Category')->findAll();
            return $this->render('index.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                'products' => $products,
                'category' => $category,
                'categories' => $categories
            ]);
        }

        return $this->render('index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR
        ]);
    }

}