<?php

namespace App\Services;

use App\Models\DocumentSequence;
use App\Support\Settings;
use Illuminate\Support\Carbon;

class DocumentNumberService
{
    public function next(string $prefix, ?Carbon $date = null): string
    {
        $date ??= now();
        $year = (int) $date->format('Y');
        $month = (int) $date->format('m');

        $sequence = DocumentSequence::query()->firstOrCreate(
            ['prefix' => $prefix, 'year' => $year, 'month' => $month],
            ['last_number' => 0]
        );

        $sequence = DocumentSequence::query()
            ->whereKey($sequence->id)
            ->lockForUpdate()
            ->first();

        $sequence->last_number++;
        $sequence->save();

        $format = (string) Settings::get('number_format', config('atkabt.number_format'));

        return strtr($format, [
            '{YYYY}' => sprintf('%04d', $year),
            '{MM}' => sprintf('%02d', $month),
            '{SEQ}' => sprintf('%04d', $sequence->last_number),
        ]);
    }

    public function nextSimple(string $prefix, ?Carbon $date = null): string
    {
        $date ??= now();
        $year = (int) $date->format('Y');
        $month = (int) $date->format('m');

        $sequence = DocumentSequence::query()->firstOrCreate(
            ['prefix' => $prefix, 'year' => $year, 'month' => $month],
            ['last_number' => 0]
        );

        $sequence = DocumentSequence::query()
            ->whereKey($sequence->id)
            ->lockForUpdate()
            ->first();

        $sequence->last_number++;
        $sequence->save();

        return sprintf('%s/%04d/%02d/%04d', $prefix, $year, $month, $sequence->last_number);
    }
}
