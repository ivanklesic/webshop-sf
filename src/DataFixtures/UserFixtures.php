<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\User as User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{

    private $passwordEncoder;


    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {

        $user = new User();
        $user->setUsername("user1");

        $user->setPassword($this->passwordEncoder->encodePassword(
                 $user,
                 'pass1'
             ));
        $user->setFirstname("marko");
        $user->setRoles(array('ROLE_USER'));
        $user->setLastname("markic");
        $user->setDiet("aaaaa");

        $manager->persist($user);
        $manager->flush();
    }
}
