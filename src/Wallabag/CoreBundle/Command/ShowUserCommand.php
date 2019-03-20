<?php

namespace Wallabag\CoreBundle\Command;

use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wallabag\UserBundle\Entity\User;

class ShowUserCommand extends ContainerAwareCommand
{
    /** @var SymfonyStyle */
    protected $io;

    protected function configure()
    {
        $this
            ->setName('wallabag:user:show')
            ->setDescription('Show user details')
            ->setHelp('This command shows the details for an user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'User to show details for'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');

        try {
            $user = $this->getUser($username);
            $this->showUser($user);
        } catch (NoResultException $e) {
            $this->io->error(sprintf('User "%s" not found.', $username));

            return 1;
        }

        return 0;
    }

    /**
     * @param User $user
     */
    

    /**
     * Fetches a user from its username.
     *
     * @param string $username
     *
     * @return \Wallabag\UserBundle\Entity\User
     */
    
}
