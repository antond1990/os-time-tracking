<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create:admin';

    /** @var EntityManagerInterface */
    protected $entityManager;
    /** @var UserPasswordEncoderInterface  */
    protected $passwordEncoder;

    /** @var User */
    private $user;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, string $name = null)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a new user with ROLE_ADMIN')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // ask for user email
        $question = new Question('email: ');
        $question->setValidator(function ($answer) {
            if (!preg_match('/^\S+@\S+\.\S+$/', $answer) ) {
                throw new \RuntimeException(
                    'Invalid eMail address.'
                );
            }
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $answer]);
            if($user instanceof User) {
                throw new \RuntimeException(
                    'This eMail address is already registered.'
                );
            }
            return $answer;
        });
        $email = $helper->ask($input, $output, $question);

        // ask for user password
        $question = new Question('password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $question);

        // ask to repeat the password
        $question = new Question('repeat password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $question->setValidator(function ($answer) use ($password) {
            if ($answer !== $password) {
                throw new \RuntimeException(
                    'The passwords are not equal.'
                );
            }
            return $answer;
        });
        $helper->ask($input, $output, $question);

        // create user
        $this->user = new User();
        $this->user
            ->setEmail($email)
            ->setPassword($this->passwordEncoder->encodePassword($this->user, $password))
            ->setRoles(['ROLE_ADMIN'])
            ->setCreatedAt(new \DateTime())
            ->setEnabled(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager->persist($this->user);

        $this->entityManager->flush();

        $io = new SymfonyStyle($input, $output);
        $io->success('User "' . $this->user->getEmail() . '" created.');
    }
}
