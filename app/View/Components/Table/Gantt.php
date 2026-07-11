<?php

namespace App\View\Components\Table;

use Carbon\Carbon;
use Illuminate\View\Component;

/**
 * <x-table.gantt :tasks="$tasks" month="2026-06" />
 *
 * $tasks format (array/Collection of array):
 *   [
 *     'label'     => 'Netra UI Design System',
 *     'pic'       => 'Eka N.',
 *     'start'     => '2026-06-01',   // apa saja yg bisa di-parse Carbon::parse()
 *     'end'       => '2026-06-20',
 *     'progress'  => 85,             // opsional, 0-100
 *     'color'     => 'indigo',       // sesuai warna nt-gantt-bar-{color} di netra-tables.css
 *     'milestone' => false,          // true = tampil sbg diamond di 1 hari (pakai 'start' sbg tanggalnya)
 *   ]
 */
class Gantt extends Component
{
    /** Lebar 1 kolom hari dalam px — HARUS sama dgn .gcell-day di netra-tables.css */
    protected const DAY_WIDTH = 28;

    protected const MONTHS_ID = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
    ];

    /** @var string[] daftar 'Y-m' yang dirender (utk navigasi prev/next tanpa reload) */
    public array $months;

    public string $activeMonth;

    public function __construct(
        public iterable $tasks,
        ?string $month = null,
        public int $monthsAround = 0,
    ) {
        $this->activeMonth = $month ?? Carbon::now()->format('Y-m');

        $start = Carbon::createFromFormat('Y-m', $this->activeMonth)->subMonths($monthsAround);

        $this->months = collect(range(0, $monthsAround * 2))
            ->map(fn($i) => $start->copy()->addMonths($i)->format('Y-m'))
            ->all();
    }

    /** Data siap-pakai (label bulan, grid hari, baris task) utk satu bulan tertentu. */
    public function monthData(string $ym): array
    {
        $date = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
        $daysInMonth = $date->daysInMonth;
        $today = Carbon::now();
        $isCurrentMonth = $today->format('Y-m') === $ym;

        $days = collect(range(1, $daysInMonth))->map(function ($d) use ($date, $isCurrentMonth, $today) {
            $current = $date->copy()->day($d);

            return [
                'day'     => $d,
                'weekend' => $current->isWeekend(),
                'today'   => $isCurrentMonth && $today->day === $d,
            ];
        });

        return [
            'label'       => self::MONTHS_ID[$date->month - 1] . ' ' . $date->year,
            'daysInMonth' => $daysInMonth,
            'days'        => $days,
            'todayDay'    => $isCurrentMonth ? $today->day : 0,
            'rows'        => collect($this->tasks)
                ->map(fn($task) => $this->taskRow($task, $date))
                ->all(),
        ];
    }

    protected function taskRow(array $task, Carbon $monthDate): array
    {
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();

        $start = Carbon::parse($task['start']);
        $end = Carbon::parse($task['end'] ?? $task['start']);
        $color = $task['color'] ?? 'indigo';

        $bar = null;
        $milestoneDay = null;

        if (!empty($task['milestone'])) {
            if ($start->between($monthStart, $monthEnd)) {
                $milestoneDay = $start->day;
            }
        } elseif ($end->gte($monthStart) && $start->lte($monthEnd)) {
            $visibleStart = $start->lt($monthStart) ? $monthStart : $start;
            $visibleEnd = $end->gt($monthEnd) ? $monthEnd : $end;
            $spanDays = $visibleStart->diffInDays($visibleEnd) + 1;

            $bar = [
                'startDay' => $visibleStart->day,
                'widthPx'  => max(0, $spanDays * self::DAY_WIDTH - 6),
                'color'    => $color,
                'label'    => isset($task['progress'])
                    ? "{$task['progress']}% · {$task['label']}"
                    : $task['label'],
            ];
        }

        return [
            'label'        => $task['label'],
            'pic'          => $task['pic'] ?? null,
            'progress'     => $task['progress'] ?? null,
            'color'        => $color,
            'bar'          => $bar,
            'milestoneDay' => $milestoneDay,
        ];
    }

    public function render()
    {
        return view('components.table.gantt');
    }
}
