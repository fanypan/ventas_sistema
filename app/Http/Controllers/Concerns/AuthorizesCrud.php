<?php

namespace App\Http\Controllers\Concerns;

trait AuthorizesCrud
{
    protected function authorizeCrud(
        string $resource,
        array $extraRead = [],
        array $extraCreate = [],
        array $extraUpdate = [],
        array $extraDelete = []
    ): void {
        $this->middleware("permission:read {$resource}")->only(array_merge(['index', 'show'], $extraRead));
        $this->middleware("permission:create {$resource}")->only(array_merge(['create', 'store'], $extraCreate));
        $this->middleware("permission:update {$resource}")->only(array_merge(['edit', 'update'], $extraUpdate));
        $this->middleware("permission:delete {$resource}")->only(array_merge(['destroy'], $extraDelete));
    }
}
