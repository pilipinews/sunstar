<?php

namespace Pilipinews\Website\Sunstar;

use Nacmartin\PhpExecJs\PhpExecJs;
use Nacmartin\PhpExecJs\Runtime\ExternalRuntime;
use Pilipinews\Common\Client as CurlClient;

class Client
{
    /**
     * @var \Nacmartin\PhpExecJs\PhpExecJs
     */
    protected $executor;

    /**
     * Initializes the cURL session.
     */
    public function __construct()
    {
        $binaries = array('node', 'nodejs');

        $runtime = new ExternalRuntime(null, $binaries);

        $this->executor = new PhpExecJs($runtime);
    }

    /**
     * Performs the HTTP request based on the given URL.
     *
     * @param  string $url
     * @return string
     */
    public static function request($url)
    {
        $client = new CurlClient;

        $self = new static;

        $client->url($url);

        $result = $client->execute(false);

        echo json_encode($result) . PHP_EOL;

        if ($self->redirected($result)) {
            $cookie = $client->cookie($matches[1]);

            $client->set(CURLOPT_COOKIE, $cookie);
        }

        return $client->execute();
    }

    /**
     * Returns the cookie value based on given script.
     *
     * @param  string $result
     * @return string
     */
    protected function cookie($result)
    {
        $pattern = '/<script>(.*?)<\/script>/i';

        preg_match($pattern, $result, $matches);

        echo json_encode($matches) . PHP_EOL;

        $script = str_replace('e(r);', 'r', $result);

        $eval = $this->executor->evalJs($script);

        $search = array('document.cookie=', 'location.reload()');

        $script = str_replace($search, array('x=', 'x'), $eval);

        return $this->executor->evalJs((string) $script);
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
