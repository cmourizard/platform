<?php

namespace Oro\Bundle\EmailBundle\Exception;

use Zend\Mail\Storage\Exception\RuntimeException;

/**
 * Exception that triggers in case if the sync of origin was failed because of wrong credentials.
 */
class InvalidCredentialsException extends RuntimeException
{
}
