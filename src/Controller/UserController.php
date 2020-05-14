<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
        $users = $entityManager->getRepository('App\Entity\User')->findCustomersAndSellers();
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
     * @return Response
     */
    public function createAction(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
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
            $entityManager->flush();

            $this->addFlash(
                'success',
                'User created successfully!'
            );
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/create.html.twig', array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $encoder
     * @param User $user
     * @return Response
     * @Route ("/user/edit/{id}", name="user_edit")
     */

    public function editAction(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder, User $user)
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
                $entityManager->flush();
                $this->addFlash(
                    'user_edit',
                    'Profile edited successfully!'
                );

                return $this->redirectToRoute('homepage');
            }

            return $this->render('user/create.html.twig', array(
                'form' => $form->createView(),
                'user' => $user
            ));
        }

        $this->addFlash(
            'warning',
            'You can only edit your own profile!'
        );

        return $this->redirectToRoute('homepage');


    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param User $user
     * @Route ("/user/delete/{id}", name="user_delete")
     * @return Response
     * @throws Exception
     */
    public function deleteAction(User $user, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if($user === $this->getUser())
        {
            return $this->redirectToRoute('user_list');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('user_list');
    }
}
