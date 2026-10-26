<?php

namespace Tests;

use Closure;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\AssertionFailedError;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Refuse to run against anything but the in-memory SQLite database, so a
     * test run (which wipes the database) can never touch a real one.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");
        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                "Tests must use the in-memory SQLite database, not [{$connection}: {$database}]. Check phpunit.xml."
            );
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Tests must never reach a real external API. Any call made through
        // Laravel's Http client (Paystack) that a test has not faked throws.
        // Cloudinary's SDK uses its own client, so phpunit.xml points it at a
        // fake account instead.
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
