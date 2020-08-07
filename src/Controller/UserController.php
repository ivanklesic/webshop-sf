<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use GraphAware\Neo4j\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserController extends AbstractController
{
    /**
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @Route("/user/list", name="user_list")
     */
    public function listAction(EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $users = $entityManager->getRepository('App\Entity\User')->findByRole('ROLE_SELLER', 'ROLE_CUSTOMER');
        $currentUser = $this->getUser();

        return $this->render('user/list.html.twig', [
            'users' => $users,
            'currentUser' => $currentUser
        ]);
    }

    /**
     * @Route("/signup", name="user_signup")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $encoder
     * @param ClientInterface $client
     * @return Response
     */
    public function createAction(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder, ClientInterface $client)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_ANONYMOUSLY');
        if($this->getUser()){
            return $this->redirectToRoute('app_logout');
        }
        $user = new User();

        $form = $this->createForm(UserType::class, $user, array(
            'requiredPassword' => true,
            'user' => $user
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $user->getPassword();
            $encodedPassword = $encoder->encodePassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $entityManager->persist($user);

            if(in_array('ROLE_CUSTOMER', $user->getRoles())){

                    $parameters = [ 'userID' => $user->getId(),
                        'firstname' => $user->getFirstname(),
                        'lastname' => $user->getLastname(),
                    ];


                    $query = 'CREATE (user:User {id: {userID}, firstName: {firstname}, lastName: {lastname}}) 
                               WITH user';

                    if($user->getActiveDiet()){
                        $query .= 'MATCH (diet:Diet {id: {dietID}}) ';
                        $parameters['dietID'] = $user->getActiveDiet()->getId();
                        $query .= 'CREATE (user)-[rel:IS_USING]->(diet) ';
                    }

                    $query .= 'WITH user ';

                    foreach ($user->getConditions() as $index => $condition){
                        $query .= 'MATCH (condition'.$index.':Condition {id: {condition'.$index.'ID}}) 
                              CREATE (user)-[rel:HAS_PROBLEMS_WITH]->(condition'.$index.') 
                              WITH user 
                              ';
                        $parameters['condition'.$index.'ID'] = $condition->getId();
                    }

                    $query .= 'RETURN user';
                    $client->run($query, $parameters);

            }



            $entityManager->flush();

            $this->addFlash(
                'success',
                'Profile created successfully!'
            );
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/create.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
            'edit' => false
        ));
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $encoder
     * @param User $user
     * @param ClientInterface $client
     * @return Response
     * @Route ("/user/edit/{id}", name="user_edit")
     */

    public function editAction(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder, User $user, ClientInterface $client)
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        if($user === $this->getUser()){

            $oldPassword = $user->getPassword();
            $form = $this->createForm(UserType::class, $user, array(
                'requiredPassword' => false,
                'user' => $user
            ));

            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {

                $plainPassword = $form["password"]->getData();
                if ($plainPassword != null) {
                    $encodedPassword = $encoder->encodePassword($user, $plainPassword);
                    $user->setPassword($encodedPassword);
                }
                else{
                    $user->setPassword($oldPassword);
                }
                $entityManager->persist($user);

                if(in_array('ROLE_CUSTOMER', $user->getRoles())){

                    $parameters = [ 'userID' => $user->getId(),
                        'firstname' => $user->getFirstname(),
                        'lastname' => $user->getLastname(),
                    ];

                    $query = 'MATCH (user:User {id: {userID}}) 
                              MATCH (user)-[relDiet:IS_USING]->() 
                              MATCH (user)-[relCondition:HAS_PROBLEMS_WITH]->() 
                              DELETE relDiet 
                              DELETE relCondition ';

                    if($user->getActiveDiet()){
                        $query .= 'MATCH (diet:Diet {id: {dietID}}) ';
                        $parameters['dietID'] = $user->getActiveDiet()->getId();
                        $query .= 'CREATE (user)-[rel:IS_USING]->(diet) ';
                    }

                    foreach ($user->getConditions() as $index => $condition){
                        $query .= 'MATCH (condition'.$index.':Condition {id: {condition'.$index.'ID}}) 
                              CREATE (user)-[rel:HAS_PROBLEMS_WITH]->(condition'.$index.') 
                              WITH user 
                              ';
                        $parameters['condition'.$index.'ID'] = $condition->getId();
                    }

                    $query .= 'RETURN user';
                    $client->run($query, $parameters);

                    $query = 'MATCH (user:User {id: {userID}}) 
                              SET user.';

                    $client->run($query, ['userID' => $user->getId()]);

                }

                $entityManager->flush();
                $this->addFlash(
                    'success',
                    'Profile edited successfully!'
                );

                return $this->redirectToRoute('homepage');
            }

            return $this->render('user/create.html.twig', array(
                'form' => $form->createView(),
                'user' => $user,
                'edit' => true
            ));
        }
        else{

            $this->addFlash(
                'warning',
                'You can only edit your own profile!'
            );

            return $this->redirectToRoute('homepage');

        }


    }

    /**
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @param ClientInterface $client
     * @return Response
     * @Route ("/user/delete/{id}", name="user_delete")
     */
    public function deleteAction(User $user, EntityManagerInterface $entityManager, ClientInterface $client)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if($user === $this->getUser())
        {
            return $this->redirectToRoute('user_list');
        }

        if(in_array('ROLE_CUSTOMER', $user->getRoles())){

            $query = 'MATCH (user:User {id: {userID}}) 
                      DETACH DELETE user';

            $client->run($query, ['userID' => $user->getId()]);

        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('user_list');
    }
}
