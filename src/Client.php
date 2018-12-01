<?php

namespace Pilipinews\Website\Sunstar;

use Pilipinews\Common\Client as CurlClient;

/**
 * Sunstar cURL Client
 *
 * @package Pilipinews
 * @author  Rougin Gutib <rougingutib@gmail.com>
 */
class Client extends CurlClient
{
    /**
     * @var \Pilipinews\Website\Sunstar\Script
     */
    protected $evaluator;

    /**
     * Initializes the cURL session.
     */
    public function __construct()
    {
        parent::__construct();

        $this->evaluator = new Script;
    }

    /**
     * Performs the HTTP request based on the given URL.
     *
     * @param  string $url
     * @return string
     */
    public static function request($url)
    {
        $self = new static;

        $self->set(CURLOPT_SSL_VERIFYPEER, 0);

        $self->url($url);

        $result = $self->execute(false);

        if ($self->redirected($result))
        {
            $pattern = '/<script>(.*?)<\/script>/i';

            preg_match($pattern, $result, $matches);

            $cookie = $self->cookie($matches[1]);

            $self->set(CURLOPT_COOKIE, $cookie);
        }

        return $self->execute();
    }

    /**
     * Returns the cookie value based on given script.
     *
     * @param  string $result
     * @return string
     */
    protected function cookie($result)
    {
        $script = str_replace('e(r);', 'r', $result);

        $eval = $this->evaluator->evaluate($script);

        $search = array('document.cookie=', 'location.reload()');

        $script = str_replace($search, array('x=', 'x'), $eval);

        return $this->evaluator->evaluate((string) $script);
    }

    /**
     * Checks if the result is being redirected.
     *
     * @param  string $result
     * @return boolean
     */
    protected function redirected($result)
    {
        return strpos($result, 'You are being redirected') !== false;
    }
}
