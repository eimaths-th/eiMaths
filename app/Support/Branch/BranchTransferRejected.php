<?php

namespace App\Support\Branch;

final class BranchTransferRejected extends \RuntimeException
{
    public function __construct(
        public readonly array $record_errors,
        public readonly int $http_status,
    ) {
        parent::__construct(__('centre-management.transfers.bulk.failed'));
    }
}
