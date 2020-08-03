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
            'condition' => $condition,
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($condition);
            $entityManager->flush();

            $client = $this->get('neo4j.client');
            $client->run('CREATE (con:Condition {id: '. $condition->getId() .', name: '. $condition->getName() .'})');

            $this->addFlash(
                'success',
                'Condition created successfully!'
            );

            return $this->redirectToRoute('homepage');
        }


        return $this->render(
            'condition/create.html.twig', [
            'form' => $form->createView(),
            'condition' => $condition,
            'edit' => false
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Condition $condition
     * @return Response
     * @Route ("/con/edit/{id}", name="con_edit")
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, Condition $condition)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm('AppBundle\Form\ConditionType', $condition, [
            'condition' => $condition,
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager->persist($condition);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Condition edited successfully!'
            );
            return $this->redirectToRoute('homepage');
        }


        return $this->render(
            'product/create.html.twig', [
            'form' => $form->createView(),
            'product' => $condition,
            'edit' => true
        ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Condition $condition
     * @return Response
     * @Route ("/con/delete/{id}", name="con_delete")
     */
    public function deleteAction(EntityManagerInterface $entityManager, Condition $condition)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($condition);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Condition deleted successfully!'
        );

        return $this->redirectToRoute('con_list');
    }

}