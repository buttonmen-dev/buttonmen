<?php

/**
 * BMEmail: an e-mail message sent by buttonmen
 *
 * @author cgolubi1
 *
 * @property-read string $subject                Message subject header
 * @property-read string $recipient              Message recipient address
 * @property-read string $bodypars               Paragraphs to be placed in the message body
 * @property-read string $isTest                 Is this BMEmail object part of a test?
 *
 * Note that there are no generic message construction or sending
 * functions, intentionally.  Keep all the syntax needed to construct
 * any e-mail message inside this class, so we can keep it all in sync.
 */
class BMEmail {

    // properties
    private $subject;
    private $recipient;
    private $bodypars;

    // constructor
    public function __construct($recipient, $isTest) {
        $this->recipient = $recipient;
        $this->bodypars = array();
        $this->isTest = $isTest;
    }

    // send an e-mail verification link to a player
    public function send_verification_link($playerId, $username, $playerKey) {
        $this->subject = 'Please verify your ButtonMen account';
        $this->bodypars[] = 'Welcome to ButtonMen, ' . $username . '!';
        $this->bodypars[] = 'Please confirm that you requested this account by browsing to:';
        $this->bodypars[] = $this->encoded_verification_link($playerId, $playerKey);
        $this->bodypars[] = 'If you did not request a ButtonMen account, please ignore this e-mail.';
        $this->send_message();
    }

    // this function actually sends the message, and should only be called from within this class
    protected function send_message() {
        $headers = array();
        $headers[] = "From: site-notifications@buttonweavers.com";
        $headers[] = "Reply-To: help@buttonweavers.com";
        $headers[] = "Auto-Submitted: auto-generated";
        $headers[] = "Precedence: bulk";

$this->recipient = 'chaos';
        if (!($this->isTest)) {
            mail(
                $this->recipient,
                $this->subject,
                implode("\r\n\r\n", $this->bodypars) . "\r\n",
                implode("\r\n", $headers)
            );
        }
    }

    protected function encoded_verification_link($playerId, $playerKey) {
        $link = $this->get_server_url_prefix() . 'ui/verify.html?' .
                'id=' . urlencode($playerId) . '&' .
                'key=' . urlencode($playerKey);
        return $link;
    }

    // What does the site believe its own full URL is?
    protected function get_server_url_prefix() {
        if (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)) {
            $port = '';
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $protocol = 'https';
                if ($_SERVER['SERVER_PORT'] != '443') {
                    $port = ':' . $_SERVER['SERVER_PORT'];
                }
            } else {
                $protocol = 'http';
                if ($_SERVER['SERVER_PORT'] != '80') {
                    $port = ':' . $_SERVER['SERVER_PORT'];
                }
            }
            if (isset($_SERVER['HTTP_HOST'])) {
                $host = $_SERVER['HTTP_HOST'];
            } else {
                $host = $_SERVER['SERVER_NAME'] . $port;
            }
            $validCallers = array('api/responder');
            $uri = str_replace($validCallers, '', $_SERVER['REQUEST_URI']);
            return ($protocol . '://' . $host . $uri);
        } else {
            return 'http://localhost/';
        }
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            switch ($property) {
                default:
                    return $this->$property;
            }
        }
    }

    public function __set($property, $value) {
        switch ($property) {
            default:
                $this->$property = $value;
        }
    }
}
