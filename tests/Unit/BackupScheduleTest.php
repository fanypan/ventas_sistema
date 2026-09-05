<?php

namespace Tests\Unit;

use App\Support\BackupSchedule;
use Tests\TestCase;

class BackupScheduleTest extends TestCase
{
    public function test_parses_single_time(): void
    {
        $this->assertSame(['02:30'], BackupSchedule::times('02:30'));
    }

    public function test_parses_comma_separated_and_normalizes(): void
    {
        $this->assertSame(['09:00', '17:00', '20:00'], BackupSchedule::times('9:00, 17:00,20:00'));
    }

    public function test_empty_disables_schedule(): void
    {
        $this->assertSame([], BackupSchedule::times(''));
        $this->assertSame([], BackupSchedule::times('  , '));
    }

    public function test_skips_invalid_and_duplicates(): void
    {
        $this->assertSame(['02:30'], BackupSchedule::times('02:30,nope,25:00,02:30'));
    }
}
