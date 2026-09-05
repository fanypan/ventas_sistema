<?php

namespace App\Services\Media;

class EncodedImage
{
    public function __construct(
        public readonly string $path,
        public readonly string $binary,
        public readonly string $mime,
    ) {}
}
