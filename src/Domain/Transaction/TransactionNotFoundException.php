<?php

declare(strict_types=1);

namespace App\Domain\Transaction;

use App\Domain\DomainException\DomainRecordNotFoundException;

class TransactionNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'The transaction you requested does not exist.';
}
