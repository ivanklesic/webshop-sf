<?php


namespace App\Controller;

use App\Entity\Diet;
use Doctrine\ORM\EntityManagerInterface;
use GraphAware\Neo4j\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class DietController extends AbstractController
{
    /**
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route("/diet/list", name="diet_list")
     */
    public function listAction(EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $diets = $entityManager->getRepository('App\Entity\Diet')->findAll();

        return $this->render(
            'diet/list.html.twig', [
            'diets' => $diets
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ClientInterface $client
     * @return Response
     * @Route ("/diet/create", name="diet_create")
     */
    public function createAction(Request $request, EntityManagerInterface $entityManager, ClientInterface $client)
    {

        $diet = new Diet();

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm('App\Form\DietType', $diet, [
            'diet' => $diet,
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($diet);
            $entityManager->flush();

            $parameters = [ 'dietID' => $diet->getId(),
                            'name' => $diet->getName(),
                            'description' => $diet->getDescription(),
                            'proteinPercent' => $diet->getProteinPercent(),
                            'lipidPercent' => $diet->getLipidPercent(),
                            'carbohydratePercent' => $diet->getCarbohydratePercent(),
            ];

            $query = 'CREATE (diet:Diet {dietID: {dietID}, name: {name}, description: {description}, proteinPercent: {proteinPercent}, lipidPercent: {lipidPercent}, carbohydratePercent: {carbohydratePercent}}) 
                      RETURN diet';
            $client->run($query, $parameters);

            $this->addFlash(
                'success',
                'Diet created successfully!'
            );

            return $this->redirectToRoute('diet_list');
        }


        return $this->render(
            'diet/create.html.twig', [
            'form' => $form->createView(),
            'diet' => $diet,
            'edit' => false
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param Diet $diet
     * @param ClientInterface $client
     * @return Response
     * @Route ("/diet/edit/{id}", name="diet_edit")
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, Diet $diet, ClientInterface $client)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm('App\Form\DietType', $diet, [
            'diet' => $diet,
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($diet);
            $entityManager->flush();

            $parameters = [ 'dietID' => $diet->getId(),
                            'name' => $diet->getName(),
                            'description' => $diet->getDescription(),
                            'proteinPercent' => $diet->getProteinPercent(),
                            'lipidPercent' => $diet->getLipidPercent(),
                            'carbohydratePercent' => $diet->getCarbohydratePercent(),
            ];

            $query = 'MATCH (diet:Diet {dietID: {dietID}}) 
                      SET diet.name = {name} 
                      SET diet.description = {description} 
                      SET diet.proteinPercent = {proteinPercent} 
                      SET diet.lipidPercent = {lipidPercent} 
                      SET diet.carbohydratePercent = {carbohydratePercent} 
                      RETURN diet';

            $client->run($query, $parameters);

            $this->addFlash(
                'success',
                'Diet edited successfully!'
            );
            return $this->redirectToRoute('diet_list');
        }


        return $this->render(
            'diet/create.html.twig', [
            'form' => $form->createView(),
            'diet' => $diet,
            'edit' => true
        ]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Diet $diet
     * @param ClientInterface $client
     * @return Response
     * @Route ("/diet/delete/{id}", name="diet_delete")
     */
    public function deleteAction(EntityManagerInterface $entityManager, Diet $diet, ClientInterface $client)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if($diet->getProducts()->isEmpty() && $diet->getUsers()->isEmpty())
        {
            $parameters = ['dietID' => $diet->getId()];
            $entityManager->remove($diet);
            $entityManager->flush();

            $query = 'MATCH (diet:Diet {dietID: {dietID}}) 
                      DETACH DELETE diet ';


            $client->run($query, $parameters);



            $this->addFlash(
                'success',
                'Diet deleted successfully!'
            );

            return $this->redirectToRoute('diet_list');
        }

        $this->addFlash(
            'warning',
            'The diet you are trying to delete still has products and/or users attached to it.'
        );


        return $this->redirectToRoute('diet_list');
    }

}