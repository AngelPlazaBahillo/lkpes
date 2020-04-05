<?php

namespace App\Command;

use App\Post\Forum;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class LokosDelProCommand extends Command
{
    /**
     * @var Forum
     */
    protected $forum;

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    public function __construct(string $name = null, Forum $forum, ParameterBagInterface $params)
    {
        parent::__construct($name);

        $this->forum = $forum;
        $this->params = $params;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return Forum|null
     * @throws \Exception
     */
    protected function connectForum(InputInterface $input, OutputInterface $output): ?Forum
    {
        $io = new SymfonyStyle($input, $output);
        $forum = $this->forum;
        $crawler = $forum->connect();
        $errorMessage = $crawler->filter(".msg");

        if ($errorMessage->last() === null) {
            $io->error("User/Password incorrecto");
            return null;
        }

        $io->comment(
            sprintf(
                "Connected to forum %s successfully",
                $forum->getForumUrl()
            )
        );

        return $forum;
    }
}
