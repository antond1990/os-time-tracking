<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BackendUserFixtures extends Fixture
{

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $adminUser = new User();
        $adminUser
            ->setEnabled(true)
            ->setCreatedAt(new \DateTime())
            ->setEmail('admin@wwnbg.de')
            ->setRoles(['ROLE_ADMIN'])
        ;

        $password = $this->encoder->encodePassword($adminUser, 'admin');
        $adminUser->setPassword($password);

        $manager->persist($adminUser);

        $user = new User();
        $user
            ->setEnabled(true)
            ->setCreatedAt(new \DateTime())
            ->setEmail('user@wwnbg.de')
            ->setRoles(['ROLE_USER'])
        ;

        $password = $this->encoder->encodePassword($user, 'user');
        $user->setPassword($password);

        $manager->persist($user);

        $manager->flush();
    }
}
