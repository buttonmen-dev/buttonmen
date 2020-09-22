<?php
/**
 * BMEmail: an e-mail message sent by buttonmen
 *
 * @author chaos
 */

/**
 * This class contains all logic needed to construct any e-mail message.
 *
 * Note that there are no generic message construction or sending
 * functions, intentionally.  Keep all the syntax needed to construct
 * any e-mail message inside this class, so we can keep it all in sync.
 *
 * @property-read string $subject                Message subject header
 * @property-read string $recipient              Message recipient address
 * @property-read string $bodypars               Paragraphs to be placed in the message body
 * @property-read string $isTest                 Is this BMEmail object part of a test?
 *
 */
class BMEmail {

    // properties

    /**
     * Subject line
     *
     * @var string
     */
    private $subject;

    /**
     * Email address of recipient
     *
     * @var string
     */
    private $recipient;

    /**
     * Array containing lines to be used as the body of the message
     *
     * @var array
     */
    private $bodypars;

    /**
     * Constructor
     *
     * @param string $recipient
     * @param bool $isTest
     */
    public function __construct($recipient, $isTest) {
        $this->recipient = $recipient;
        $this->bodypars = array();
        $this->isTest = $isTest;
    }

    /**
     * Send an e-mail verification link to a player
     *
     * @param int $playerId
     * @param string $username
     * @param string $playerKey
     */
    public function send_verification_link($playerId, $username, $playerKey) {
        $this->subject = 'Please verify your Button Men account';
        $this->bodypars[] = 'Welcome to Button Men, ' . $username . '!';
        $this->bodypars[] = 'Please confirm that you requested this account by browsing to:';
        $this->bodypars[] = $this->encoded_verification_link($playerId, $playerKey);
        $this->bodypars[] = 'If you did not request a Button Men account, please ignore this e-mail.';
        $this->send_message();
    }

    /**
     * Send an e-mail reset verification link to a player
     *
     * @param int $playerId
     * @param string $username
     * @param string $playerKey
     */
    public function send_reset_verification_link($playerId, $username, $playerKey) {
        $this->subject = 'Password reset link for Button Men';
        $this->bodypars[] = 'Hi, ' . $username . '!';
        $this->bodypars[] = 'Someone requested a password reset for your account.';
        $this->bodypars[] = 'If it was you, browse to this reset link and enter a new password:';
        $this->bodypars[] = $this->encoded_reset_verification_link($playerId, $playerKey);
        $this->bodypars[] = 'If you did not request a password reset, please ignore this e-mail.';
        $this->send_message();
    }

    /**
     * This function actually sends the message, and should only be called from within this class
     */
    protected function send_message() {
        $headers = array();
        $headers[] = "From: site-notifications@buttonweavers.com";
        $headers[] = "Reply-To: help@buttonweavers.com";
        $headers[] = "Auto-Submitted: auto-generated";
        $headers[] = "Precedence: bulk";

        if (!($this->isTest)) {
            mail(
                $this->recipient,
                $this->subject,
                implode("\r\n\r\n", $this->bodypars) . "\r\n",
                implode("\r\n", $headers)
            );
        }
    }

    /**
     * URL to be used to verify player on first login
     *
     * @param int $playerId
     * @param string $playerKey
     * @return string
     */
    protected function encoded_verification_link($playerId, $playerKey) {
        $link = $this->get_server_url_prefix() . 'ui/verify.html?' .
                'id=' . urlencode($playerId) . '&' .
                'key=' . urlencode($playerKey);
        return $link;
    }

    /**
     * URL to be used to verify player on password reset request
     *
     * @param int $playerId
     * @param string $playerKey
     * @return string
     */
    protected function encoded_reset_verification_link($playerId, $playerKey) {
        $link = $this->get_server_url_prefix() . 'ui/verify_reset.html?' .
                'id=' . urlencode($playerId) . '&' .
                'key=' . urlencode($playerKey);
        return $link;
    }

    /**
     * What does the site believe its own full URL is?
     *
     * @return string
     */
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

    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if (property_exists($this, $property)) {
            switch ($property) {
                default:
                    return $this->$property;
            }
        }
    }

    /**
     * Setter
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        switch ($property) {
            default:
                $this->$property = $value;
        }
    }

    /**
     * Define behaviour of isset()
     *
     * @param string $property
     * @return bool
     */
    public function __isset($property) {
        return isset($this->$property);
    }
}
