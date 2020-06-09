<?php


namespace App\Controller;


use App\Entity\Category;
use App\Entity\Condition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConditionController extends AbstractController
{
    /**
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route("/con/list", name="con_list")
     */
    public function listAction(EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $conditions = $entityManager->getRepository('AppBundle:Condition')->findAll();

        return $this->render(
            'condition/list.html.twig', [
            'conditions' => $conditions
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route ("/con/create", name="con_create")
     */
    public function createAction(Request $request, EntityManagerInterface $entityManager)
    {

        $condition = new Condition();

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm('App\Form\CategoryType', $condition, [
            'entityManager' => $entityManager,
            'condition' => $condition,
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($condition);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Condition created successfully!'
            );

            return $this->redirectToRoute('homepage');
        }


        return $this->render(
            'product/create.html.twig', [
            'form' => $form->createView(),
            'condition' => $condition,
            'edit' => false
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
            'product' => $category,
            'edit' => true
        ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Category $category
     * @return Response
     * @Route ("/cat/delete/{id}", name="cat_delete")
     */
    public function deleteAction(EntityManagerInterface $entityManager, Category $category)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Category deleted successfully!'
        );

        return $this->redirectToRoute('product_list');
    }

}