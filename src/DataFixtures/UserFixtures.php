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
        $user->setUsername("admin");
        $user->setPassword($this->passwordEncoder->encodePassword(
                 $user,
                 'adminpass'
             ));
        $user->setFirstname("Admin");
        $user->setRoles(array('ROLE_USER', 'ROLE_ADMIN'));
        $user->setLastname("Admin");
        $user->setDiet(null);

        $manager->persist($user);
        $manager->flush();
    }
}
