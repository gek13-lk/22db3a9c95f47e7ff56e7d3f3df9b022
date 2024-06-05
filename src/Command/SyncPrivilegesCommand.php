<?php

namespace App\Command;

use App\Entity\Privilege;
use App\Voter\PrivilegeGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsCommand(
    name: 'app:sync:privileges',
    description: 'Синхронизация привилегий',
)]
final class SyncPrivilegesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        /** @var PrivilegeGroupInterface[] */
        #[TaggedIterator(tag: 'voter.privilege.groups')]
        private readonly iterable $groups
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->groups as $group) {
            foreach ($group->getPrivileges() as $code => $name) {
                if(!$entity = $this->entityManager->getRepository(Privilege::class)->findOneBy(['code' => $code])) {
                    $entity = new Privilege();
                    $entity->setCode($code);
                }

                $entity->setName($name);

                $this->entityManager->persist($entity);
            }
        }

        $this->entityManager->flush();

        $io->success('Выполнено.');

        return Command::SUCCESS;
    }
}
