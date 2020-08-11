<?php


namespace App\Controller;



use App\Entity\Condition;
use Doctrine\ORM\EntityManagerInterface;
use GraphAware\Neo4j\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConditionController extends AbstractController
{
    /**
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route("/condition/list", name="con_list")
     */
    public function listAction(EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $conditions = $entityManager->getRepository('App\Entity\Condition')->findAll();

        return $this->render(
            'condition/list.html.twig', [
            'conditions' => $conditions
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ClientInterface $client
     * @return Response
     * @Route ("/condition/create", name="con_create")
     */
    public function createAction(Request $request, EntityManagerInterface $entityManager, ClientInterface $client)
    {

        $condition = new Condition();

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm('App\Form\ConditionType', $condition, [
            'condition' => $condition,
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($condition);
            $entityManager->flush();

            $query = 'CREATE (con:Condition {conditionID: {id}, name: {name}}) 
                      RETURN con';
            $client->run($query, ['id' => $condition->getId(), 'name' => $condition->getName(), 'description' => $condition->getDescription()]);

            $this->addFlash(
                'success',
                'Condition created successfully!'
            );

            return $this->redirectToRoute('con_list');
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
     * @param ClientInterface $client
     * @return Response
     * @Route ("/condition/edit/{id}", name="con_edit")
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, Condition $condition, ClientInterface $client)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm('App\Form\ConditionType', $condition, [
            'condition' => $condition,
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager->persist($condition);
            $entityManager->flush();

            $parameters = [ 'conditionID' => $condition->getId(),
                            'name' => $condition->getName(),
                            'description' => $condition->getDescription(),
            ];

            $query = 'MATCH (condition:Condition {conditionID: {conditionID}}) 
                      SET condition.name = {name} 
                      SET condition.description = {description} 
                      RETURN condition';

            $client->run($query, $parameters);


            $this->addFlash(
                'success',
                'Condition edited successfully!'
            );
            return $this->redirectToRoute('con_list');
        }


        return $this->render(
            'condition/create.html.twig', [
            'form' => $form->createView(),
            'condition' => $condition,
            'edit' => true
        ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Condition $condition
     * @param ClientInterface $client
     * @return Response
     * @Route ("/condition/delete/{id}", name="con_delete")
     */
    public function deleteAction(EntityManagerInterface $entityManager, Condition $condition, ClientInterface $client)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if($condition->getProducts()->isEmpty() && $condition->getUsers()->isEmpty())
        {

            $parameters = ['conditionID' => $condition->getId()];

            $entityManager->remove($condition);
            $entityManager->flush();

            $query = 'MATCH (condition:Condition {conditionID: {conditionID}}) 
                      DETACH DELETE condition';

            $client->run($query, $parameters);



            $this->addFlash(
                'success',
                'Condition deleted successfully!'
            );

            return $this->redirectToRoute('con_list');
        }

        $this->addFlash(
            'warning',
            'The condition you are trying to delete still has products and/or users attached to it.'
        );


        return $this->redirectToRoute('con_list');
    }

}