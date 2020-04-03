<?php

namespace App\Post;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

final class Forum
{
    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var HttpBrowser
     */
    private $browser;

    /**
     * @var string
     */
    private $forumUrl;

    /**
     * @var Crawler|null
     */
    private $loggedHomePage;

    /**
     * Forum constructor.
     * @param string $user
     * @param string $password
     * @param string $forumUrl
     */
    public function __construct(string $user, string $password, string $forumUrl)
    {
        $this->browser = new HttpBrowser(HttpClient::create());
        $this->user = $user;
        $this->password = $password;
        $this->forumUrl = $forumUrl;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return HttpBrowser
     */
    public function getBrowser(): HttpBrowser
    {
        return $this->browser;
    }

    /**
     * @return string
     */
    public function getForumUrl(): string
    {
        return $this->forumUrl;
    }

    /**
     * @return Crawler|null
     */
    public function getLoggedHomePage(): ?Crawler
    {
        return $this->loggedHomePage;
    }

    /**
     * @return Crawler $loggedHomePage
     */
    public function connect(): Crawler
    {
        $loginUrl = $this->forumUrl."/login?connexion";
        $crawler = $this->browser->request('GET', $loginUrl);

        $form = $crawler->selectButton('Conectarse')->form();
        $form['username'] = $this->user;
        $form['password'] = $this->password;

        // submits the given form
        $this->loggedHomePage = $this->browser->submit($form);

        return $this->loggedHomePage;
    }
}
