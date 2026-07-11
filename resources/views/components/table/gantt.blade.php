<div class="nt-gantt-wrap" data-nt-gantt data-active-month="{{ $activeMonth }}">
    @foreach ($months as $ym)
        @php($m = $monthData($ym))
        <table
            class="nt-gantt nt-gantt-month"
            data-month="{{ $ym }}"
            data-today-day="{{ $m['todayDay'] }}"
            @if ($ym !== $activeMonth) hidden @endif
        >
            <thead>
                <tr>
                    <th class="gcol-info gcol-info-th" rowspan="2">Proyek</th>
                    <th class="gcol-info gcol-info-pic-th" rowspan="2">PIC</th>
                    <th class="gcol-info gcol-info-last gcol-info-progress-th" rowspan="2">Progress</th>
                    <th colspan="{{ $m['daysInMonth'] }}" class="gcell-month">{{ $m['label'] }}</th>
                </tr>
                <tr>
                    @foreach ($m['days'] as $day)
                        <th class="gcell-day
                            @if ($day['weekend']) weekend @endif
                            @if ($day['today']) today gcell-today-marker @endif
                        ">{{ $day['day'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($m['rows'] as $row)
                    <tr>
                        <td class="gcol-info gcol-info-project">{{ $row['label'] }}</td>
                        <td class="gcol-info gcol-info-pic">{{ $row['pic'] }}</td>
                        <td class="gcol-info gcol-info-last gcol-info-progress">
                            @if ($row['progress'] !== null)
                                <div class="nt-gantt-progress-cell">
                                    <div class="nt-gantt-progress-track">
                                        <div class="nt-gantt-progress-fill nt-gantt-bar-fill-{{ $row['color'] }}"
                                             style="width:{{ $row['progress'] }}%"></div>
                                    </div>
                                    <span class="nt-gantt-progress-label">{{ $row['progress'] }}%</span>
                                </div>
                            @endif
                        </td>

                        @foreach ($m['days'] as $day)
                            @php
                                $isBarStart = $row['bar'] && $row['bar']['startDay'] === $day['day'];
                                $isMilestone = $row['milestoneDay'] === $day['day'];
                            @endphp
                            <td class="gcell-bar
                                @if ($day['weekend']) weekend @endif
                                @if ($day['today']) gcell-today-marker @endif
                                gcell-bar-pos
                            ">
                                @if ($isBarStart)
                                    <div class="nt-gantt-bar nt-gantt-bar-{{ $row['bar']['color'] }} nt-gantt-bar-abs"
                                         style="width:{{ $row['bar']['widthPx'] }}px">
                                        <div class="nt-gantt-bar-fill nt-gantt-bar-fill-{{ $row['bar']['color'] }}"
                                             style="width:{{ $row['progress'] ?? 0 }}%"></div>
                                        <span class="nt-gantt-bar-label">{{ $row['bar']['label'] }}</span>
                                    </div>
                                @elseif ($isMilestone)
                                    <div class="nt-milestone-wrap"><div class="nt-milestone"></div></div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>
