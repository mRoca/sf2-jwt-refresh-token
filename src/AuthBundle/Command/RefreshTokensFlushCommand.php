<?php

namespace AuthBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshTokensFlushCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('auth:jwt:flush')
            ->setDescription('Revoke all refresh tokens');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->getContainer()->get('auth.services.jwt_refresh_manager')->flushAll()) {
            $output->writeln('<error>Unable to rewoke refresh tokens.</error>');

            return -1;
        }

        $output->writeln('<success>All refresh tokens are now revoked.</success>');

        return;
    }
}
