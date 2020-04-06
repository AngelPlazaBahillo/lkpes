<?php
namespace App\Command;

use App\Post\Forum;
use App\Post\Post;
use App\Post\PostRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LokosDelProMarketNewFreePlayerCommand extends LokosDelProCommand
{
    protected static $defaultName = 'lokos-del-pro:market:free-player:new';

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

        $isFirstFreePlayer = $io->confirm("Is your first player?");
        $yourTeam = $io->ask("Mi equipo");
        $player = $io->ask("Jugador libre");
        $playerOldTeam = $io->ask("Equipo de procedencia");

        $postMessage =
                        "- EQUIPO: $yourTeam\n"
                        ."- JUGADOR LIBRE: $player\n"
                        ."- EQUIPO PROCEDENCIA: $playerOldTeam\n"
                        ."- GASTO: 5M\n";

        $confirmation = $io->confirm("Will be post the next text\n$postMessage \n Continue?:");

        if ($confirmation) {
            $date = new \DateTime($io->ask("Fecha y hora de publicación (Ej." . date("Y-m-d H:i:s") . ")"));
            $now = new \DateTime('now', new \DateTimeZone("+0400"));
            $diff = $date->getTimestamp() - $now->getTimestamp();

            sleep($diff);

            if ($confirmation) {
                $topicName = $isFirstFreePlayer
                    ? $this->params->get("first_free_player_topic_name")
                    : $this->params->get("second_free_player_topic_name");

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
