<?php

namespace Tests\Unit;

use App\Services\Media\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    public function test_it_resizes_and_encodes_jpeg(): void
    {
        $file = UploadedFile::fake()->image('foto.jpg', 1600, 1200);

        $encoded = app(ImageOptimizer::class)->encode($file);

        $this->assertStringEndsWith('.jpg', $encoded->path);
        $this->assertStringStartsWith('products/', $encoded->path);
        $this->assertSame('image/jpeg', $encoded->mime);
        $this->assertNotSame('', $encoded->binary);

        $info = getimagesizefromstring($encoded->binary);
        $this->assertIsArray($info);
        $this->assertLessThanOrEqual(1000, max($info[0], $info[1]));
    }

    public function test_it_encodes_branding_png_and_keeps_it_under_max_edge(): void
    {
        $file = UploadedFile::fake()->image('logo.png', 1200, 400);

        $encoded = app(ImageOptimizer::class)->encodePng($file);

        $this->assertStringEndsWith('.png', $encoded->path);
        $this->assertStringStartsWith('branding/', $encoded->path);
        $this->assertSame('image/png', $encoded->mime);

        $info = getimagesizefromstring($encoded->binary);
        $this->assertIsArray($info);
        $this->assertLessThanOrEqual(800, max($info[0], $info[1]));
    }

    public function test_it_builds_a_square_png_icon(): void
    {
        $file = UploadedFile::fake()->image('logo.png', 80, 40);
        $binary = file_get_contents($file->getRealPath());

        $png = app(ImageOptimizer::class)->squarePng($binary, 192);

        $info = getimagesizefromstring($png);
        $this->assertIsArray($info);
        $this->assertSame(192, $info[0]);
        $this->assertSame(192, $info[1]);
        $this->assertSame('image/png', $info['mime']);
    }

    public function test_it_builds_a_square_png_without_source(): void
    {
        $png = app(ImageOptimizer::class)->squarePng(null, 512);

        $info = getimagesizefromstring($png);
        $this->assertIsArray($info);
        $this->assertSame(512, $info[0]);
        $this->assertSame(512, $info[1]);
    }
}
