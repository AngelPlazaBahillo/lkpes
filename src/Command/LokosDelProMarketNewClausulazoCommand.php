<?php

namespace App\Command;

use App\Post\Forum;
use App\Post\Post;
use App\Post\PostRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LokosDelProMarketNewClausulazoCommand extends LokosDelProCommand
{
    protected static $defaultName = 'lokos-del-pro:market:clausulazo:new';

    public function __construct(string $name = null, Forum $forum, ParameterBagInterface $params)
    {
        parent::__construct($name, $forum, $params);
    }

    protected function configure()
    {
        $this
            ->setDescription('Crea un POST registrando un clausulazo');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $forum = $this->connectForum($input, $output);
        $forumName = $this->params->get("forum_market_category_name");
        $io = new SymfonyStyle($input, $output);

        try {
            $crawler = $forum->getLoggedHomePage();
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
        $targetPlayerSalaryFloat = floatval(str_replace(",", ".", $targetPlayerSalary));
        $targetPlayerClauseFloat = floatval(str_replace(",", ".", $targetPlayerClause));
        $total = $targetPlayerSalaryFloat + $targetPlayerClauseFloat;

        $postMessage =
                        "- EQUIPO: $yourTeam\n"
                        ."- PAGO CLAUSULA AL EQUIPO: $playerTeam\n"
                        ."- JUGADOR QUE CLAUSULO: $targetPlayer\n"
                        ."- CLAUSULA DEL JUGADOR: {$targetPlayerClause}M\n"
                        ."- SALARIO DEL JUGADOR: {$targetPlayerSalary}M\n"
                        ."- TOTAL: {$total}M";

        $confirmation = $io->confirm("Will be post the next text\n $postMessage \n Continue?:");

        if ($confirmation) {
            $date = new \DateTime($io->ask("Fecha y hora de publicación (Ej." . date("Y-m-d H:i:s") . ")"));
            $now = new \DateTime('now', new \DateTimeZone("+0400"));
            $diff = $date->getTimestamp() - $now->getTimestamp();

            sleep($diff);

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
        }

        return 0;
    }
}
