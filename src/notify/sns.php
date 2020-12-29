<?php
/**
 * sns : definition of communication to notification service
 *
 * @author danlangford
 */

// TODO: consider the requirements and recommendations
// https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/getting-started_requirements.html

// TODO: consider installing dependency via Composer, phar, or zip file
// TODO: determine the proper way, in this project, to get the libs i need
require '/path/to/aws.phar'; // OOR require '/path/to/vendor/autoload.php'; OR require '/path/to/aws-autoloader.php';
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
        'version' => 'latest'
    ]);

    return $SnSclient;
}
