<?php

declare(strict_types=1);

namespace App\Domain\Wallet;

use App\Domain\DomainException\DomainRecordNotFoundException;

class WalletNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'The wallet you requested does not exist.';
}
