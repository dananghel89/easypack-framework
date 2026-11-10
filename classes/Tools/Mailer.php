<?php

/**
 * Epack Mailer Class
 *
 * @see https://github.com/PHPMailer/PHPMailer
 */

namespace Epack\Tools;

class Mailer extends \PHPMailer\PHPMailer\PHPMailer
{
    // Mailer constructor.
    public function __construct($exceptions = null)
    {
        parent::__construct($exceptions);

        $vars = \Epack\Config::getVars();

        if (!isset($vars->mail)) {
            throw new \Exception('Mail config is not set!');
        }

        $config = $vars->mail;
        extract($config, EXTR_SKIP);

        $this->isHTML(false);
        $this->setFrom($address, $name);

        $this->CharSet    = self::CHARSET_UTF8;
        $this->Mailer     = $method;
        $this->Host       = $host;
        $this->Port       = $port;
        $this->SMTPSecure = $smtp_secure;
        $this->SMTPAuth   = $smtp_auth;
        $this->Username   = $username;
        $this->Password   = $password;

        $this->Debugoutput = function ($str, $level) {
            echo "Debug level $level; message: $str\n";
        };
    }

}
