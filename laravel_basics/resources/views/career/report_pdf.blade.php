<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CIA Career Intelligence Report - {{ $candidate->name }}</title>
    <style>
        @page {
            margin: 25mm 20mm 25mm 20mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-title {
            font-size: 20pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
        }
        .header-sub {
            font-size: 10pt;
            color: #64748b;
            margin: 3px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-meta {
            text-align: right;
            font-size: 9pt;
            color: #475569;
        }
        .banner {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 5px solid #2563eb;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .score-box {
            width: 100%;
            border-collapse: collapse;
        }
        .score-val {
            font-size: 32pt;
            font-weight: bold;
            color: #1d4ed8;
            line-height: 1;
        }
        .score-label {
            font-size: 9pt;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
        }
        .score-tier {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            font-size: 9pt;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 4px;
            margin-top: 4px;
        }
        h2 {
            font-size: 13pt;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-top: 18px;
            margin-bottom: 10px;
        }
        .archetype-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .archetype-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 9pt;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
        }
        .archetype-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 9.5pt;
        }
        .bar-container {
            background-color: #e2e8f0;
            height: 10px;
            border-radius: 3px;
            width: 100%;
            overflow: hidden;
        }
        .bar-fill {
            background-color: #2563eb;
            height: 10px;
        }
        .narrative-block {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 12px 14px;
            margin-bottom: 15px;
            font-size: 10pt;
            line-height: 1.6;
        }
        .narrative-block p {
            margin: 0 0 8px 0;
        }
        .narrative-block p:last-child {
            margin-bottom: 0;
        }
        .columns-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .columns-table td {
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        .columns-table td:last-child {
            padding-right: 0;
            padding-left: 10px;
        }
        .card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            min-height: 120px;
        }
        .card h3 {
            margin: 0 0 6px 0;
            font-size: 10.5pt;
            color: #1e3a8a;
        }
        ul {
            margin: 0;
            padding-left: 18px;
            font-size: 9.5pt;
        }
        li {
            margin-bottom: 4px;
        }
        .roadmap-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 15px;
        }
        .roadmap-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 9pt;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        .roadmap-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9pt;
            vertical-align: top;
        }
        .pitch-box {
            background-color: #eff6ff;
            border: 1px dashed #3b82f6;
            padding: 10px 14px;
            margin-top: 10px;
            font-style: italic;
            font-size: 9.5pt;
            color: #1e40af;
        }
        .footer {
            margin-top: 25px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 8pt;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <h1 class="header-title">CYNARIS INTELLIGENCE AGENT</h1>
                    <div class="header-sub">Career Intelligence Executive Report &bull; W10D3</div>
                </td>
                <td class="header-meta">
                    <strong>Candidate:</strong> {{ $candidate->name }}<br>
                    <strong>Email:</strong> {{ $candidate->email }}<br>
                    <strong>Generated:</strong> {{ $generatedDate }}<br>
                    <strong>Report ID:</strong> CIA-REP-{{ str_pad($report->id, 5, '0', STR_PAD_LEFT) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Executive Employability Banner -->
    <div class="banner">
        <table class="score-box">
            <tr>
                <td style="width: 25%; vertical-align: middle; border-right: 1px solid #cbd5e1; padding-right: 15px;">
                    <div class="score-label">Employability Score</div>
                    <div class="score-val">{{ round($report->employability_score, 1) }}%</div>
                    <div class="score-tier">{{ $breakdown['employability']['status_tier'] ?? 'Tier 1 Priority' }}</div>
                </td>
                <td style="width: 75%; padding-left: 15px; vertical-align: middle;">
                    <div style="font-size: 11pt; font-weight: bold; color: #0f172a; margin-bottom: 4px;">
                        Primary Archetype: <span style="color: #2563eb;">{{ $report->primary_archetype }}</span>
                        @if($report->secondary_archetype)
                            &bull; Secondary: <span style="color: #7c3aed;">{{ $report->secondary_archetype }}</span>
                        @endif
                    </div>
                    <div style="font-size: 9.5pt; color: #475569; line-height: 1.4;">
                        {{ $archetypes[$report->primary_archetype]['description'] ?? '' }}
                    </div>
                    <div style="font-size: 8.5pt; color: #64748b; margin-top: 6px;">
                        <strong>Weighting Model:</strong> Archetype (25%) + Placement Readiness (35%) + Interview Readiness (25%) + Skill Verification (15%)
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Archetype Radar / Comparative Breakdown -->
    <h2>Engineering Archetype Profile</h2>
    <table class="archetype-table">
        <thead>
            <tr>
                <th style="width: 20%;">Archetype</th>
                <th style="width: 35%;">Competency Focus</th>
                <th style="width: 33%;">Score Meter</th>
                <th style="width: 12%; text-align: right;">Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->archetype_scores as $archetype => $score)
                @php
                    $meta = $archetypes[$archetype] ?? ['tagline' => $archetype, 'description' => ''];
                    $isPrimary = $archetype === $report->primary_archetype;
                    $isSecondary = $archetype === $report->secondary_archetype;
                @endphp
                <tr style="{{ $isPrimary ? 'background-color: #f0fdf4;' : ($isSecondary ? 'background-color: #faf5ff;' : '') }}">
                    <td>
                        <strong>{{ $archetype }}</strong>
                        @if($isPrimary)
                            <span style="font-size: 7.5pt; background: #16a34a; color: white; padding: 1px 4px; border-radius: 2px;">PRIMARY</span>
                        @elseif($isSecondary)
                            <span style="font-size: 7.5pt; background: #7c3aed; color: white; padding: 1px 4px; border-radius: 2px;">SECONDARY</span>
                        @endif
                    </td>
                    <td style="color: #475569; font-size: 8.5pt;">{{ $meta['tagline'] }}</td>
                    <td>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: {{ $score }}%; {{ $isPrimary ? 'background-color: #16a34a;' : ($isSecondary ? 'background-color: #7c3aed;' : '') }}"></div>
                        </div>
                    </td>
                    <td style="text-align: right; font-weight: bold;">{{ round($score, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Executive Narrative -->
    <h2>Executive Intelligence Narrative</h2>
    <div class="narrative-block">
        <p>{{ $breakdown['narrative_details']['executive_summary'] ?? $report->narrative }}</p>
        @if(!empty($breakdown['narrative_details']['archetype_analysis']))
            <p>{{ $breakdown['narrative_details']['archetype_analysis'] }}</p>
        @endif
    </div>

    <!-- Strengths & Growth Areas Side by Side -->
    <table class="columns-table">
        <tr>
            <td>
                <div class="card" style="border-left: 3px solid #16a34a;">
                    <h3 style="color: #166534;">Standout Strengths</h3>
                    <ul>
                        @foreach($breakdown['narrative_details']['strengths'] ?? [] as $strength)
                            <li>{{ $strength }}</li>
                        @endforeach
                    </ul>
                </div>
            </td>
            <td>
                <div class="card" style="border-left: 3px solid #d97706;">
                    <h3 style="color: #92400e;">Strategic Growth Areas</h3>
                    <ul>
                        @foreach($breakdown['narrative_details']['growth_areas'] ?? [] as $growth)
                            <li>{{ $growth }}</li>
                        @endforeach
                    </ul>
                </div>
            </td>
        </tr>
    </table>

    <!-- 90-Day Actionable Roadmap -->
    @if(!empty($breakdown['narrative_details']['roadmap_90_days']))
        <h2>90-Day Career Acceleration Roadmap</h2>
        <table class="roadmap-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Phase / Timeframe</th>
                    <th style="width: 35%;">Core Objective</th>
                    <th style="width: 40%;">Key Deliverables</th>
                </tr>
            </thead>
            <tbody>
                @foreach($breakdown['narrative_details']['roadmap_90_days'] as $phase)
                    <tr>
                        <td>
                            <strong>{{ $phase['phase'] ?? '' }}</strong><br>
                            <span style="font-size: 8pt; color: #64748b;">{{ $phase['timeframe'] ?? '' }}</span>
                        </td>
                        <td>{{ $phase['objective'] ?? '' }}</td>
                        <td>
                            <ul style="margin: 0; padding-left: 14px; font-size: 8.5pt;">
                                @foreach($phase['key_actions'] ?? [] as $action)
                                    <li>{{ $action }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Hiring Pitch Callout -->
    @if(!empty($breakdown['narrative_details']['hiring_pitch']))
        <h2>Hiring Manager Pitch</h2>
        <div class="pitch-box">
            "{{ $breakdown['narrative_details']['hiring_pitch'] }}"
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Cynaris Internship Career Intelligence Agent &bull; Confidential Candidate Assessment &bull; Powered by Cynaris Portal AI Engine &bull; Page 1 of 1
    </div>
</body>
</html>
