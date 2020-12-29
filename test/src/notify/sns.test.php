<?php
/**
 * sns : definition of communication to notification service
 */

// TODO: consider the appropriate test setup.
// connect to different SNS Topic? or stub out calls so they no-op? or provide choice?

// TODO: determine the proper way, in this project, to get the libs i need
require 'vendor/autoload.php';
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

/**
 * build a client
 *
 * @return SnSclient
 */
function snsClient() {

    // credentials can be obtained from ENV vars like AWS_ACCESS_KEY_ID
    // or credentials can be obtained from a single file on the host like $HOME/.aws/credentials
    // for now ill assume a credentials file with profile: buttonweavers
    // https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html

    $SnSclient = new SnsClient([
        'profile' => 'buttonweavers',
        'region' => 'us-east-1',
        'version' => '2010-03-31'
    ]);

    return $SnSclient;
}
