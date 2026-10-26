<?php

namespace Tests;

use Closure;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\AssertionFailedError;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests must never reach a real external API (Paystack, Cloudinary, ...).
        // Any HTTP call that a test has not faked with Http::fake() throws.
        Http::preventStrayRequests();
    }

    /**
     * Run a test that describes correct behaviour which is currently broken.
     *
     * While the bug exists the test is reported as "incomplete" so the suite
     * stays green. Once the bug is fixed the test fails with a reminder to
     * remove this wrapper, so the fix PR turns it into a normal passing test.
     *
     * @param  string  $issue  e.g. 'Known issue #2 in CLAUDE.md'
     */
    protected function knownIssue(string $issue, Closure $test): void
    {
        try {
            $test();
        } catch (AssertionFailedError $e) {
            $this->markTestIncomplete($issue.' — '.$e->getMessage());
        }

        $this->fail($issue.' now passes. Remove the knownIssue() wrapper from this test.');
    }

    /**
     * Fake Paystack's "initialize transaction" endpoint.
     */
    protected function fakePaystackInitialize(string $reference = 'test_ref_123'): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'message' => 'Authorization URL created',
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/'.$reference,
                    'access_code' => 'access_'.$reference,
                    'reference' => $reference,
                ],
            ]),
        ]);
    }

    /**
     * Fake Paystack's "verify transaction" endpoint.
     *
     * @param  float  $amountInNaira  what the customer actually paid (sent to us in kobo)
     * @param  string  $status  Paystack's payment status: success, failed, abandoned
     */
    protected function fakePaystackVerify(string $reference, float $amountInNaira, string $status = 'success'): void
    {
        Http::fake([
            'api.paystack.co/transaction/verify/'.$reference => Http::response([
                'status' => true,
                'message' => 'Verification successful',
                'data' => [
                    'status' => $status,
                    'reference' => $reference,
                    'amount' => (int) round($amountInNaira * 100),
                    'currency' => 'NGN',
                ],
            ]),
        ]);
    }
}
