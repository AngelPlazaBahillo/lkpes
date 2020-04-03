<?php

namespace App\Command;

use App\Post\Forum;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LokosDelProRegisteredPlayersCommand extends Command
{
    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    protected static $defaultName = 'lokos-del-pro:info:players:registered';

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

        $players = [];
        $playerOrd = 1;

        $io->writeln("To find if players are registered, please write all our names now. ");

        while ($playerToFind = $io->ask("Name of player $playerOrd (To find players, write nothing)")) {
            $players[] = [
                'player' => $playerToFind,
                'founded' => false
            ];
            $playerOrd++;
        };

        $forumName = "Jugadores inscritos";
        $topicName = "JUGADORES INSCRITOS";

        try {
            $link = $crawler->selectLink($forumName)->link();
            $crawler = $forum->getBrowser()->request($link->getMethod(), $link->getUri());
        } catch (\InvalidArgumentException $ex) {
            $io->error(
                "No existe el foro seleccionado ($forumName)");
        }

        try {
            $link = $crawler->selectLink($topicName)->link();
            $crawler = $forum->getBrowser()->request($link->getMethod(), $link->getUri());
            $html = $crawler->html();

            foreach ($players as $player) {
                $playerName = mb_strtoupper($player['player'], "UTF-8");
                $coincidences = mb_substr_count($html, "$playerName");

                if ($coincidences > 0) {
                    $io->error("Player ".$playerName." FOUNDED ($coincidences coincidences)");
                } else {
                    $io->success("Player \"$playerName\" NOT FOUNDED");
                }

            }

        } catch (\InvalidArgumentException $ex) {
            $io->error("No se ha podido realizar la búsqueda");
        }

        return 0;
    }
}
