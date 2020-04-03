<?php

namespace App\Command;

use App\Post\Forum;
use App\Post\Post;
use App\Post\PostRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LokosDelProMarketFreePlayerNewCommand extends Command
{
    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    protected static $defaultName = 'lokos-del-pro:market:free-player:new';

    public function __construct(string $name = null, Forum $forum, ParameterBagInterface $params)
    {
        parent::__construct($name);

        $this->forum = $forum;
        $this->params = $params;
    }

    protected function configure()
    {
        $this
            ->setDescription('Crea un POST registrando un jugador libre');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $forum = $this->forum;
        $crawler = $forum->connect();
        $errorMessage = $crawler->filter(".msg");

        if ($errorMessage->last() === null) {
            $io->error("User/Password incorrecto");
            return false;
        }

        $io->comment(
            sprintf(
                "Connected to forum %s successfully",
                $forum->getForumUrl()
            )
        );

        $forumName = $this->params->get("forum_market_category_name");

        try {
            $link = $crawler->selectLink($forumName)->link();
            $crawler = $forum->getBrowser()->request($link->getMethod(), $link->getUri());
        } catch (\InvalidArgumentException $ex) {
            $io->error(
                "No existe el foro seleccionado ($forumName)");
        }

        $yourTeam = $io->ask("Mi equipo");
        $playerTeam = $io->ask("Pago la cláusula a");
        $targetPlayer = $io->ask("Jugador que clausulo");
        $targetPlayerClause = $io->ask("Cláusula del jugador objetivo");
        $targetPlayerSalary = $io->ask("Salario del jugador objetivo");
        $total = $targetPlayerSalary + $targetPlayerClause;

        $postMessage =
                        "- EQUIPO: $yourTeam\n"
                        ."- PAGO CLAUSULA AL EQUIPO: $playerTeam\n"
                        ."- JUGADOR QUE CLAUSULO: $targetPlayer\n"
                        ."- CLAUSULA DEL JUGADOR: {$targetPlayerClause}M\n"
                        ."- SALARIO DEL JUGADOR: {$targetPlayerSalary}M\n"
                        ."- TOTAL: {$total}M";

        $confirmation = $io->confirm("Will be post the next text (write \"yes\" or \"no\":");

        if ($confirmation) {
            $topicName = $this->params->get("market_topic_name");

            try {
                $link = $crawler->selectLink($topicName)->link();
                $crawler = $forum->getBrowser()->request($link->getMethod(), $link->getUri());
                $postRepo = new PostRepository();
                $post = Post::create($forum->getBrowser(), $crawler, $postMessage);
                $postRepo->save($post);
            } catch (\InvalidArgumentException $ex) {
                $io->error("No se puede postear");
            }

            $io->success('POST INCLUDED!!');
        }

        return 0;
    }
}
