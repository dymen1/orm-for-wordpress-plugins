<?php
namespace Dorans\Competition\Util;

/**
 * {@inheritDoc}
 */
class DateTime extends \DateTime
{
    const MYSQL_W_SECONDS = 'Y-m-d H:i:s';
    const DATETIME_LOCAL = 'Y-m-d\TH:i';

    const DATETIME_LOCAL_PLAINTEXT = 'd-m-Y H:i';
}