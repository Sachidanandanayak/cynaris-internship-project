<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-indigo-600 text-white">
                        CIA Career Mode
                    </span>
                    <span class="text-xs text-gray-500 font-medium">Cynaris Career Intelligence</span>
                </div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight mt-1">
                    {{ __('Skill Gap Analysis & Career Intelligence Cockpit') }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    <span class="w-2 h-2 mr-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    Assessment Sync: Active
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Include Chart.js via CDN for Radar Visualisation -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Hero Banner -->
            <div class="p-6 sm:p-8 bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-2xl text-white shadow-xl border border-indigo-800/40">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-indigo-200 border border-white/15 mb-2">
                            Module 1: LlamaIndex + Groq Pipeline &bull; Module 2: CrewAI Career Engine
                        </span>
                        <h3 class="text-2xl sm:text-3xl font-black">
                            Career Readiness Cockpit &mdash; {{ $candidate->name }}
                        </h3>
                        <p class="mt-2 text-indigo-100/90 text-sm leading-relaxed">
                            Compare your verified coursework, lesson milestones, and assessment quiz scores against target industry job benchmarks. Detect competency deltas, view real-time radar metrics, and unlock personalized AI recommendations.
                        </p>
                    </div>

                    <!-- Candidate Quick Stats -->
                    <div class="grid grid-cols-3 gap-3 bg-white/5 p-4 rounded-xl border border-white/10 shrink-0">
                        <div class="text-center px-2">
                            <span class="block text-2xl font-black text-emerald-400">
                                {{ $completedLessons->count() }}
                            </span>
                            <span class="text-[11px] uppercase tracking-wider text-indigo-200 font-semibold">Lessons</span>
                        </div>
                        <div class="text-center px-2 border-x border-white/10">
                            <span class="block text-2xl font-black text-indigo-300">
                                {{ $quizScores->count() }}
                            </span>
                            <span class="text-[11px] uppercase tracking-wider text-indigo-200 font-semibold">Quizzes</span>
                        </div>
                        <div class="text-center px-2">
                            <span class="block text-2xl font-black text-amber-300">
                                {{ count($skillProfile) }}
                            </span>
                            <span class="text-[11px] uppercase tracking-wider text-indigo-200 font-semibold">Skills</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Target Role Selection & Action Bar -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <form id="skillGapForm" class="flex flex-col md:flex-row items-end gap-4" onsubmit="event.preventDefault(); runSkillGapAnalysis();">
                    <div class="flex-1 w-full">
                        <label for="target_role_select" class="block text-xs uppercase font-bold text-gray-500 mb-1">
                            Select Benchmark Target Role
                        </label>
                        <select id="target_role_select" class="w-full rounded-xl border-gray-300 text-sm font-semibold text-gray-800 focus:border-indigo-500 focus:ring-indigo-500" onchange="onRoleSelectChange(this.value)">
                            <option value="Junior Backend Laravel Engineer" selected>Junior Backend Laravel Engineer</option>
                            <option value="Full Stack Developer (PHP & Vue/React)">Full Stack Developer (PHP & Vue/React)</option>
                            <option value="Cloud & DevOps Intern">Cloud & DevOps Intern</option>
                            <option value="Frontend UI/UX Specialist">Frontend UI/UX Specialist</option>
                            <option value="custom">Custom Role Title...</option>
                        </select>
                    </div>

                    <div id="custom_role_container" class="flex-1 w-full hidden">
                        <label for="custom_role_input" class="block text-xs uppercase font-bold text-gray-500 mb-1">
                            Enter Custom Role Title
                        </label>
                        <input type="text" id="custom_role_input" placeholder="e.g. AI Integration Specialist" class="w-full rounded-xl border-gray-300 text-sm font-semibold text-gray-800 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <button type="submit" id="analyzeBtn" class="w-full md:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                        <svg id="analyzeSpinner" class="hidden animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span id="analyzeBtnText">Analyze Skill Gaps &rarr;</span>
                    </button>
                </form>
            </div>

            <!-- Two-Column Main Content: Radar Chart & Readiness Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Column 1: Radar Chart (7 cols) -->
                <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                            <div>
                                <h4 class="font-bold text-base text-gray-900">
                                    Competency Radar: Candidate vs. Benchmark
                                </h4>
                                <p class="text-xs text-gray-500">
                                    Visualizing multi-dimensional skill proficiencies against target role requirements
                                </p>
                            </div>
                            <span id="radarEngineBadge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ strtoupper($initialAnalysis['engine'] ?? 'Pipeline') }}
                            </span>
                        </div>

                        <!-- Radar Canvas Container -->
                        <div class="relative w-full aspect-square max-h-[380px] flex items-center justify-center">
                            <canvas id="skillRadarChart"></canvas>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Candidate Proficiency
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-indigo-500 border border-dashed border-indigo-700"></span> Target Benchmark
                            </span>
                        </div>
                        <span id="activeRoleLabel" class="font-semibold text-gray-700">{{ $defaultRole }}</span>
                    </div>
                </div>

                <!-- Column 2: Readiness Score & Overview (5 cols) -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Score Card -->
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col items-center justify-center text-center">
                        <span class="text-xs uppercase font-bold text-gray-400 tracking-wider">Overall Role Readiness</span>
                        <div class="my-4 relative flex items-center justify-center">
                            <div class="w-32 h-32 rounded-full border-8 border-indigo-100 flex items-center justify-center">
                                <div class="text-center">
                                    <span id="readinessScore" class="text-4xl font-black text-indigo-600">
                                        {{ $initialAnalysis['score'] ?? 0 }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div id="readinessTierBadge">
                            @php
                                $score = $initialAnalysis['score'] ?? 0;
                                $tier = match(true) {
                                    $score >= 80 => ['text' => 'High Readiness &bull; Ready for Placement', 'class' => 'bg-emerald-100 text-emerald-800'],
                                    $score >= 60 => ['text' => 'Moderate Readiness &bull; Priority Gaps Identified', 'class' => 'bg-amber-100 text-amber-800'],
                                    default => ['text' => 'Foundational Stage &bull; Accelerated Learning Required', 'class' => 'bg-red-100 text-red-800'],
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $tier['class'] }}">
                                {!! $tier['text'] !!}
                            </span>
                        </div>
                    </div>

                    <!-- Verified Candidate Skill Matrix Snippet -->
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
                            <h4 class="font-bold text-sm text-gray-900">Candidate Verified Skills</h4>
                            <span class="text-xs text-gray-400">{{ count($skillProfile) }} tracked</span>
                        </div>
                        <div class="space-y-2.5 max-h-[220px] overflow-y-auto pr-1">
                            @forelse ($skillProfile as $sk)
                                <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-gray-50 hover:bg-gray-100 transition">
                                    <div class="font-bold text-gray-800">{{ $sk['skill'] }}</div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500">{{ $sk['proficiency_score'] }}%</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $sk['proficiency_level'] === 'Advanced' ? 'bg-emerald-100 text-emerald-800' : ($sk['proficiency_level'] === 'Intermediate' ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-gray-700') }}">
                                            {{ $sk['proficiency_level'] }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-xs text-gray-400 py-4">No completed coursework recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gaps & Priority Learning Recommendations Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Identified Gaps Card -->
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <h4 class="font-bold text-base text-gray-900 flex items-center gap-2">
                            <span>Identified Competency Gaps</span>
                            <span id="gapsCountBadge" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                {{ count($initialAnalysis['gaps'] ?? []) }}
                            </span>
                        </h4>
                        <span class="text-xs text-gray-400">Severity Prioritized</span>
                    </div>

                    <div id="gapsContainer" class="space-y-3 max-h-[360px] overflow-y-auto pr-1">
                        @forelse ($initialAnalysis['gaps'] ?? [] as $gap)
                            <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-sm text-gray-900">{{ $gap['skill'] }}</span>
                                    @php
                                        $impactClass = match(strtolower($gap['impact'] ?? '')) {
                                            'high' => 'bg-red-100 text-red-800 border-red-200',
                                            'medium' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            default => 'bg-blue-100 text-blue-800 border-blue-200',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $impactClass }}">
                                        {{ $gap['impact'] ?? 'Medium' }} Impact
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 flex items-center gap-2">
                                    <span>Current: <strong>{{ $gap['candidate_level'] ?? 'Novice' }}</strong></span>
                                    <span>&bull;</span>
                                    <span>Target: <strong>{{ $gap['required_level'] ?? 'Intermediate' }}</strong></span>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed pt-1">
                                    {{ $gap['description'] ?? 'Deficit noted against benchmark.' }}
                                </p>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400 text-xs">
                                No critical skill gaps identified for the active role.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Priority Learning Pathways Card -->
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <h4 class="font-bold text-base text-gray-900 flex items-center gap-2">
                            <span>Priority Learning Recommendations</span>
                            <span id="learningCountBadge" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                {{ count($initialAnalysis['priority_learning'] ?? []) }}
                            </span>
                        </h4>
                        <span class="text-xs text-gray-400">Curated by AI</span>
                    </div>

                    <div id="learningContainer" class="space-y-3 max-h-[360px] overflow-y-auto pr-1">
                        @forelse ($initialAnalysis['priority_learning'] ?? [] as $item)
                            <div class="p-4 rounded-xl border border-indigo-100 bg-indigo-50/30 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-sm text-indigo-950">{{ $item['module'] }}</span>
                                    <span class="text-[11px] font-bold text-indigo-700 bg-white px-2 py-0.5 rounded border border-indigo-200">
                                        ~{{ $item['estimated_hours'] }}h
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed">
                                    {{ $item['actionable_steps'] }}
                                </p>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400 text-xs">
                                Candidate competency meets target benchmarks. No immediate learning pathways required.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Career Recommendations Section (CrewAI Agent - W9D3) -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm" id="crewaiCareerSection">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-100 mb-4 gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-purple-600 text-white">
                                Module 2: CrewAI Agent
                            </span>
                            <h4 class="font-bold text-base text-gray-900">
                                Three Recommended Career Paths
                            </h4>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Multi-agent cognitive career advisor synthesizing candidate strengths, gaps, and industry trajectories
                        </p>
                    </div>

                    <button type="button" id="recommendBtn" onclick="runCareerRecommendations();" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-600/20 transition flex items-center justify-center gap-2 self-start sm:self-auto">
                        <svg id="recommendSpinner" class="hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span id="recommendBtnText">Generate 3 Recommendations &rarr;</span>
                    </button>
                </div>

                <!-- 3 Career Recommendation Cards Container -->
                <div id="recommendationsContainer" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="col-span-3 text-center py-8 text-gray-400 text-xs bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        Click "Generate 3 Recommendations" to query the CrewAI CareerAdvisorAgent based on current skill gaps.
                    </div>
                </div>
            </div>

            <!-- Recent Analysis Log History -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
                    <h4 class="font-bold text-sm text-gray-900">Recent CIA Search Logs (`cia_search_logs`)</h4>
                    <span class="text-xs text-gray-400">Database Persistent</span>
                </div>
                <div class="divide-y divide-gray-100 text-xs">
                    @forelse ($recentLogs as $log)
                        <div class="py-2.5 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-gray-800">{{ $log->target_role }}</span>
                                <span class="text-gray-400 ml-2">&bull; {{ $log->query_type }} &bull; {{ $log->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-indigo-600">{{ $log->readiness_score }}%</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">
                                    {{ $log->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-xs text-gray-400 py-3">No persistent search logs recorded yet.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- Client-side Radar Chart & AJAX Script -->
    <script>
        const initialRadarData = @json($radarData);
        let radarChartInstance = null;

        document.addEventListener('DOMContentLoaded', function () {
            initRadarChart(initialRadarData);
        });

        function onRoleSelectChange(val) {
            const customContainer = document.getElementById('custom_role_container');
            if (val === 'custom') {
                customContainer.classList.remove('hidden');
                document.getElementById('custom_role_input').focus();
            } else {
                customContainer.classList.add('hidden');
            }
        }

        function initRadarChart(data) {
            const ctx = document.getElementById('skillRadarChart').getContext('2d');
            if (radarChartInstance) {
                radarChartInstance.destroy();
            }

            radarChartInstance = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: data.labels || [],
                    datasets: [
                        {
                            label: 'Candidate Proficiency (%)',
                            data: data.candidate_scores || [],
                            backgroundColor: 'rgba(16, 185, 129, 0.25)',
                            borderColor: 'rgb(16, 185, 129)',
                            pointBackgroundColor: 'rgb(16, 185, 129)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(16, 185, 129)',
                            borderWidth: 2,
                        },
                        {
                            label: 'Benchmark Requirement (%)',
                            data: data.required_scores || [],
                            backgroundColor: 'rgba(99, 102, 241, 0.15)',
                            borderColor: 'rgb(99, 102, 241)',
                            borderDash: [5, 5],
                            pointBackgroundColor: 'rgb(99, 102, 241)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgb(99, 102, 241)',
                            borderWidth: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            min: 0,
                            max: 100,
                            ticks: {
                                stepSize: 20,
                                font: { size: 10 }
                            },
                            pointLabels: {
                                font: { size: 11, weight: 'bold' },
                                color: '#374151'
                            },
                            grid: {
                                color: 'rgba(156, 163, 175, 0.2)'
                            },
                            angleLines: {
                                color: 'rgba(156, 163, 175, 0.2)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                font: { size: 11, weight: 'bold' }
                            }
                        }
                    }
                }
            });
        }

        async function runSkillGapAnalysis() {
            const roleSelect = document.getElementById('target_role_select').value;
            let targetRole = roleSelect;
            if (roleSelect === 'custom') {
                targetRole = document.getElementById('custom_role_input').value.trim();
                if (!targetRole) {
                    alert('Please enter a custom role title.');
                    return;
                }
            }

            const btn = document.getElementById('analyzeBtn');
            const spinner = document.getElementById('analyzeSpinner');
            const btnText = document.getElementById('analyzeBtnText');

            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.innerText = 'Analyzing with AI...';

            try {
                const response = await fetch("{{ route('cia.career.skill-gap') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    body: JSON.stringify({ target_role: targetRole })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Skill gap analysis failed.');
                }

                // Update UI with response
                document.getElementById('activeRoleLabel').innerText = data.target_role;
                document.getElementById('readinessScore').innerText = Math.round(data.score) + '%';
                document.getElementById('radarEngineBadge').innerText = (data.engine || 'PIPELINE').toUpperCase();

                // Update score tier badge
                const score = Number(data.score);
                const tierContainer = document.getElementById('readinessTierBadge');
                if (score >= 80) {
                    tierContainer.innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">High Readiness &bull; Ready for Placement</span>';
                } else if (score >= 60) {
                    tierContainer.innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Moderate Readiness &bull; Priority Gaps Identified</span>';
                } else {
                    tierContainer.innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">Foundational Stage &bull; Accelerated Learning Required</span>';
                }

                // Update Radar Chart
                if (data.radar) {
                    initRadarChart(data.radar);
                }

                // Render Gaps
                renderGaps(data.gaps || []);

                // Render Priority Learning
                renderPriorityLearning(data.priority_learning || []);

            } catch (err) {
                alert('Analysis Error: ' + err.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.innerText = 'Analyze Skill Gaps \u2192';
            }
        }

        function renderGaps(gaps) {
            const container = document.getElementById('gapsContainer');
            document.getElementById('gapsCountBadge').innerText = gaps.length;

            if (gaps.length === 0) {
                container.innerHTML = '<div class="text-center py-8 text-gray-400 text-xs">No critical skill gaps identified for the active role.</div>';
                return;
            }

            container.innerHTML = gaps.map(g => {
                const impact = (g.impact || 'Medium').toLowerCase();
                const impactClass = impact === 'high' ? 'bg-red-100 text-red-800 border-red-200' :
                                   (impact === 'medium' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-blue-100 text-blue-800 border-blue-200');
                return `
                    <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-gray-900">${escapeHtml(g.skill)}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border ${impactClass}">
                                ${escapeHtml(g.impact || 'Medium')} Impact
                            </span>
                        </div>
                        <div class="text-xs text-gray-600 flex items-center gap-2">
                            <span>Current: <strong>${escapeHtml(g.candidate_level || 'Novice')}</strong></span>
                            <span>&bull;</span>
                            <span>Target: <strong>${escapeHtml(g.required_level || 'Intermediate')}</strong></span>
                        </div>
                        <p class="text-xs text-gray-500 leading-relaxed pt-1">
                            ${escapeHtml(g.description || '')}
                        </p>
                    </div>
                `;
            }).join('');
        }

        function renderPriorityLearning(learning) {
            const container = document.getElementById('learningContainer');
            document.getElementById('learningCountBadge').innerText = learning.length;

            if (learning.length === 0) {
                container.innerHTML = '<div class="text-center py-8 text-gray-400 text-xs">Candidate competency meets target benchmarks. No immediate learning pathways required.</div>';
                return;
            }

            container.innerHTML = learning.map(item => `
                <div class="p-4 rounded-xl border border-indigo-100 bg-indigo-50/30 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm text-indigo-950">${escapeHtml(item.module)}</span>
                        <span class="text-[11px] font-bold text-indigo-700 bg-white px-2 py-0.5 rounded border border-indigo-200">
                            ~${item.estimated_hours}h
                        </span>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        ${escapeHtml(item.actionable_steps || '')}
                    </p>
                </div>
            `).join('');
        }

        async function runCareerRecommendations() {
            const roleSelect = document.getElementById('target_role_select').value;
            let targetRole = roleSelect === 'custom' ? (document.getElementById('custom_role_input').value.trim() || 'Software Engineer') : roleSelect;

            const btn = document.getElementById('recommendBtn');
            const spinner = document.getElementById('recommendSpinner');
            const btnText = document.getElementById('recommendBtnText');

            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.innerText = 'CrewAI Reasoning...';

            try {
                const response = await fetch("{{ route('cia.career.recommendations', [], false) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    body: JSON.stringify({ target_role: targetRole })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Career recommendations failed.');
                }

                renderRecommendations(data.recommendations || []);
            } catch (err) {
                alert('Recommendation Error: ' + err.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.innerText = 'Generate 3 Recommendations \u2192';
            }
        }

        function renderRecommendations(recs) {
            const container = document.getElementById('recommendationsContainer');
            if (!recs || recs.length === 0) {
                container.innerHTML = '<div class="col-span-3 text-center py-6 text-gray-400 text-xs">No career path recommendations returned.</div>';
                return;
            }

            container.innerHTML = recs.map((r, idx) => `
                <div class="p-5 rounded-xl border border-purple-200 bg-gradient-to-b from-purple-50/40 to-white flex flex-col justify-between space-y-3 shadow-sm hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800">
                                Path #${idx + 1}
                            </span>
                            <span class="text-xs font-black text-purple-700">
                                ${r.fit_percentage || 85}% Match
                            </span>
                        </div>
                        <h5 class="font-black text-base text-gray-900">${escapeHtml(r.title || 'Role Recommendation')}</h5>
                        <p class="text-xs text-gray-600 mt-2 leading-relaxed">${escapeHtml(r.rationale || '')}</p>
                    </div>
                    <div class="pt-3 border-t border-purple-100 text-[11px] text-gray-500">
                        <span class="font-bold text-purple-900 block mb-1">Key Pathway Milestones:</span>
                        <p class="leading-relaxed text-gray-600">${escapeHtml(r.roadmap || '')}</p>
                    </div>
                </div>
            `).join('');
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</x-app-layout>
