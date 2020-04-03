<?php

namespace App\Post;

/**
 * Class PostRepository
 * @package App\Post
 */
class PostRepository
{
    public function save(Post $post): void
    {
        $postForm = $post->getTopic()->selectButton("Enviar")->form();
        $postForm['message'] = $post->getMessage();
        $crawler = $post->getBrowser()->submit($postForm);
        $post->saved($crawler);
    }
}
