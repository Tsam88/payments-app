<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected const JSON_HEADERS = ['Content-Type' => 'application/json'];

    /**
     * @var TestHelper;
     */
    protected $helper;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->helper = new TestHelper();
    }
}
