<?php

namespace App\Services;

use App\Models\Transaction;

/**
 * Outcome of PaymentConfirmationService::confirm().
 */
class PaymentConfirmationResult
{
    /** The payment was confirmed just now. */
    public const CONFIRMED = 'confirmed';

    /** The payment had already been confirmed earlier; nothing was changed. */
    public const ALREADY_CONFIRMED = 'already_confirmed';

    /** Paystack's amount or currency did not match; the transaction was marked failed. */
    public const MISMATCH = 'mismatch';

    /** Paystack does not report the payment as successful; nothing was changed. */
    public const NOT_SUCCESSFUL = 'not_successful';

    /** No transaction exists for the reference. */
    public const NOT_FOUND = 'not_found';

    public function __construct(
        public readonly string $status,
        public readonly ?Transaction $transaction = null,
    ) {}

    /**
     * Whether the customer has paid for their order (now or earlier).
     */
    public function isPaid(): bool
    {
        return in_array($this->status, [self::CONFIRMED, self::ALREADY_CONFIRMED], true);
    }
}
