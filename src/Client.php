<?php

namespace Pilipinews\Website\Sunstar;

use Nacmartin\PhpExecJs\PhpExecJs;
use Nacmartin\PhpExecJs\Runtime\ExternalRuntime;

class Client
{
    const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36';

    /**
     * @var \Nacmartin\PhpExecJs\PhpExecJs
     */
    protected $executor;

    public function __construct()
    {
        $binaries = array('node', 'nodejs');

        $runtime = new ExternalRuntime(null, $binaries);

        $this->executor = new PhpExecJs($runtime);
    }

    /**
     * Performs the HTTP request based on the given URL.
     *
     * @param  string $link
     * @return string
     */
    public static function request($link)
    {
        $self = new static;

        $result = $self->execute($link);

        $result = preg_match('/<script>(.*?)<\/script>/i', $result, $matches);

        echo $result . PHP_EOL;

        echo json_encode($matches) . PHP_EOL;

        $script = str_replace('e(r);', 'r', $matches[1]);

        $result = $self->executor->evalJs((string) $script);

        $result = str_replace('document.cookie=', 'x=', $result);

        $script = str_replace('location.reload()', 'x', $result);

        $cookie = $self->executor->evalJs($script);

        return $self->execute($link, (string) $cookie);
    }

    protected function execute($link, $cookie = null)
    {
        $curl = curl_init();

        if ($cookie !== null) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }

        curl_setopt($curl, CURLOPT_URL, (string) $link);

        curl_setopt($curl, CURLOPT_ENCODING, '');

        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        ($response = curl_exec($curl)) && curl_close($curl);

        return (string) $response;
    }
}
