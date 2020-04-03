<?php

namespace App\Post;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;

final class Post
{
    /**
     * @var HttpBrowser
     */
    private $browser;

    /**
     * @var Crawler
     */
    private $topic;

    /**
     * @var string
     */
    private $message;

    /**
     * @var Crawler|null
     */
    private $topicAfterSubmit;

    /**
     * @param HttpBrowser $browser
     * @param Crawler $topic
     * @param string $message
     * @return Post
     */
    public static function create(HttpBrowser $browser, Crawler $topic, string $message): self
    {
        return new self($browser, $topic, $message);
    }

    /**
     * Post constructor.
     * @param HttpBrowser $browser
     * @param Crawler $topic
     * @param string $message
     */
    private function __construct(HttpBrowser $browser, Crawler $topic, string $message)
    {
        $this->browser = $browser;
        $this->topic = $topic;
        $this->message = $message;
    }

    /**
     * @return HttpBrowser
     */
    public function getBrowser(): HttpBrowser
    {
        return $this->browser;
    }

    /**
     * @return Crawler
     */
    public function getTopic(): Crawler
    {
        return $this->topic;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return Crawler|null
     */
    public function getTopicAfterSubmit(): ?Crawler
    {
        return $this->topicAfterSubmit;
    }

    public function saved(Crawler $topicAfterSubmit): void
    {
        $this->topicAfterSubmit = $topicAfterSubmit;
    }
}
