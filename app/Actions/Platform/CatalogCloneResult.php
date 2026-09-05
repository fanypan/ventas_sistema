<?php

namespace App\Actions\Platform;

final readonly class CatalogCloneResult
{
    public function __construct(
        public int $categories = 0,
        public int $brands = 0,
        public int $products = 0,
        public int $skipped = 0,
        public int $images = 0,
    ) {}

    public function message(): string
    {
        if ($this->products === 0 && $this->skipped > 0) {
            return 'No había productos nuevos: '.$this->skipped.' ya estaban (mismo código).';
        }

        $text = $this->products.' producto'.($this->products === 1 ? '' : 's')
            .', '.$this->categories.' categoría'.($this->categories === 1 ? '' : 's')
            .' y '.$this->brands.' marca'.($this->brands === 1 ? '' : 's')
            .'. El stock quedó en 0.';

        if ($this->skipped > 0) {
            $text .= ' '.$this->skipped.' ya estaban (mismo código).';
        }

        return $text;
    }
}
