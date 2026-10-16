<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // /media/{id} keeps decoded images on disk. Each test starts with a fresh
        // database whose ids restart at 1, so clear copies from earlier tests.
        File::deleteDirectory(storage_path('framework/cache/media'));
    }
}
