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
                            Module 1: LlamaIndex + Groq &bull; Module 2: CrewAI Agent &bull; Module 3: ChromaDB Matching &bull; Module 4: LangGraph 8-Week Path &bull; Module 5: Resume ATS &bull; Module 6: Readiness Scorecard &bull; Module 7: Career Intelligence &bull; Module 8: CIA Memory Layer
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

            <!-- ChromaDB Semantic Internship Matching Section (W9D4) -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm" id="chromaMatchingSection">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between pb-4 border-b border-gray-100 mb-6 gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-teal-600 text-white">
                                Module 3: ChromaDB Engine
                            </span>
                            <h4 class="font-bold text-base text-gray-900">
                                AI Semantic Internship Matching Engine
                            </h4>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Vector $k$-NN cosine distance matching across 20 curated opportunities using pseudonymous candidate embeddings
                        </p>
                    </div>

                    <!-- Sync Profile CTA & Status -->
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <span class="text-[11px] font-semibold text-gray-500 block">Vector Collection:</span>
                            <span id="syncCollectionBadge" class="text-xs font-bold text-teal-700">cynaris_intern_profiles</span>
                        </div>
                        <button type="button" id="syncVectorBtn" onclick="runVectorProfileSync();" class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-600/20 transition flex items-center justify-center gap-2">
                            <svg id="syncVectorSpinner" class="hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span id="syncVectorBtnText">Sync Vector Profile</span>
                        </button>
                    </div>
                </div>

                <!-- Sync Notification Banner -->
                <div id="syncAlertBanner" class="hidden mb-6 p-3 rounded-xl bg-teal-50 border border-teal-200 text-teal-900 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-teal-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span id="syncAlertMessage">Candidate profile synchronized to ChromaDB collection cynaris_intern_profiles.</span>
                    </div>
                    <span id="syncTimestampBadge" class="text-[11px] text-teal-700 font-semibold"></span>
                </div>

                <!-- Filters Control Bar -->
                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-200 mb-6">
                    <form id="matchingFilterForm" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end" onsubmit="event.preventDefault(); fetchSemanticMatches();">
                        <div>
                            <label for="filter_domain" class="block text-[11px] uppercase font-bold text-gray-500 mb-1">Domain</label>
                            <select id="filter_domain" class="w-full rounded-lg border-gray-300 text-xs font-semibold text-gray-800 focus:border-teal-500 focus:ring-teal-500">
                                <option value="all">All Domains</option>
                                <option value="backend">Backend & API</option>
                                <option value="frontend">Frontend & UI/UX</option>
                                <option value="fullstack">Full-Stack</option>
                                <option value="devops">Cloud & DevOps</option>
                                <option value="ai">AI & LLM Integration</option>
                                <option value="database">Database & SQL</option>
                                <option value="security">Security & AppSec</option>
                            </select>
                        </div>

                        <div>
                            <label for="filter_location" class="block text-[11px] uppercase font-bold text-gray-500 mb-1">Workplace Mode</label>
                            <select id="filter_location" class="w-full rounded-lg border-gray-300 text-xs font-semibold text-gray-800 focus:border-teal-500 focus:ring-teal-500">
                                <option value="all">All Locations</option>
                                <option value="remote">Remote Only</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="onsite">On-Site</option>
                            </select>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label for="filter_min_affinity" class="text-[11px] uppercase font-bold text-gray-500">Min Affinity</label>
                                <span id="affinityValueLabel" class="text-xs font-bold text-teal-700">0%</span>
                            </div>
                            <input type="range" id="filter_min_affinity" min="0" max="95" step="5" value="0" class="w-full accent-teal-600" oninput="document.getElementById('affinityValueLabel').innerText = this.value + '%'">
                        </div>

                        <div>
                            <button type="submit" id="filterMatchesBtn" class="w-full px-4 py-2 rounded-lg bg-gray-900 hover:bg-black text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                                <svg id="filterSpinner" class="hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span id="filterBtnText">Apply Vector Filters &rarr;</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Matches Grid -->
                <div id="semanticMatchesContainer" class="space-y-4">
                    @forelse ($initialMatches as $match)
                        <div class="p-5 rounded-xl border border-gray-200 bg-white hover:border-teal-300 hover:shadow-md transition flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-2 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-teal-100 text-teal-800">
                                        {{ $match['domain'] }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-gray-100 text-gray-700">
                                        {{ $match['location_type'] }}
                                    </span>
                                    <span class="text-xs text-gray-400">&bull; {{ $match['location'] }}</span>
                                </div>

                                <h5 class="text-base font-bold text-gray-900">
                                    {{ $match['title'] }}
                                </h5>

                                <p class="text-xs text-gray-500 font-medium">
                                    {{ $match['company_pseudonym'] }} &bull; Stipend: <strong class="text-gray-700">{{ $match['salary_range'] }}</strong>
                                </p>

                                <!-- Skill overlap pills -->
                                <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                    <span class="text-[11px] font-bold text-gray-500 mr-1">Matching:</span>
                                    @forelse ($match['matching_skills'] as $ms)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ✓ {{ $ms }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-gray-400 italic">Foundational competencies aligned</span>
                                    @endforelse

                                    @if (!empty($match['missing_skills']))
                                        <span class="text-[11px] font-bold text-gray-400 ml-2 mr-1">Delta:</span>
                                        @foreach (array_slice($match['missing_skills'], 0, 3) as $gapSkill)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                + {{ $gapSkill }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <!-- Right Column: Affinity & Action -->
                            <div class="flex md:flex-col items-center md:items-end justify-between md:justify-center gap-3 shrink-0 border-t md:border-t-0 pt-3 md:pt-0 border-gray-100">
                                <div class="text-right">
                                    @php
                                        $affinity = $match['affinity_percentage'];
                                        $affClass = $affinity >= 80 ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : ($affinity >= 65 ? 'text-indigo-600 bg-indigo-50 border-indigo-200' : 'text-amber-600 bg-amber-50 border-amber-200');
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-sm font-black border {{ $affClass }} inline-block">
                                        {{ $affinity }}% Match
                                    </span>
                                    <span class="block text-[10px] text-gray-400 mt-1 font-mono">
                                        cosine dist: {{ $match['vector_distance'] }}
                                    </span>
                                </div>

                                @if (!empty($match['application_url']))
                                    <a href="{{ $match['application_url'] }}" class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition inline-flex items-center gap-1">
                                        Quick Apply &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 text-xs bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            No internship roles matched the selected criteria. Adjust filters or click "Sync Vector Profile".
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- LangGraph 8-Week Learning Path Generator Section (W9D5) -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm" id="langgraphSection">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between pb-4 border-b border-gray-100 mb-6 gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-rose-600 text-white">
                                Module 4: LangGraph Engine
                            </span>
                            <h4 class="font-bold text-base text-gray-900">
                                Eight-Week Personalized Learning Path Generator
                            </h4>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Autonomous five-node StateGraph workflow (<span class="font-mono text-rose-700">AssessState &rarr; IdentifyGaps &rarr; BuildPlan &rarr; ValidatePlan &rarr; OutputPlan</span>) synthesizing candidate competencies into a rigorous 8-week curriculum
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <span class="text-[11px] font-semibold text-gray-500 block">Workflow Engine:</span>
                            <span id="pathEngineBadge" class="text-xs font-bold text-rose-700">LangGraph StateGraph</span>
                        </div>
                        <button type="button" id="generatePathBtn" onclick="runLearningPathGenerator();" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition flex items-center justify-center gap-2">
                            <svg id="generatePathSpinner" class="hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span id="generatePathBtnText">Generate 8-Week Curriculum &rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- Learning Path Metrics Summary Bar -->
                <div id="pathMetricsBar" class="hidden grid grid-cols-2 sm:grid-cols-4 gap-3 bg-rose-50/50 p-4 rounded-xl border border-rose-100 mb-6">
                    <div>
                        <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider block">Duration</span>
                        <span id="pathTotalWeeks" class="text-lg font-black text-rose-950">8 Weeks</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider block">Total Learning Hours</span>
                        <span id="pathTotalHours" class="text-lg font-black text-rose-950">96 Hours</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider block">Baseline Mastery</span>
                        <span id="pathBaselineTier" class="text-lg font-black text-rose-950">Intermediate</span>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider block">StateGraph Nodes</span>
                        <span id="pathNodesExecuted" class="text-xs font-bold text-rose-900 block mt-1">5 / 5 Traversed</span>
                    </div>
                </div>

                <!-- 8-Week Schedule Container -->
                <div id="learningPathContainer" class="space-y-4">
                    <div class="text-center py-10 text-gray-400 text-xs bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        Click "Generate 8-Week Curriculum" to initiate the five-node LangGraph StateGraph workflow based on your verified skills and target role.
                    </div>
                </div>
            </div>

            <!-- Module 5: Resume Intelligence & Groq ATS Scoring (W10D1) -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-violet-600 text-white">
                                Module 5
                            </span>
                            <span class="text-xs font-bold text-violet-700">Groq ATS Resume Intelligence</span>
                        </div>
                        <h4 class="text-xl font-black text-gray-900 mt-1">
                            PDF Resume Upload & 4-Category ATS Scoring Engine
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Upload your engineering resume (PDF, max 10MB) for automated text extraction, keyword alignment, formatting compliance, and Groq LLM ATS evaluation.
                        </p>
                    </div>
                    <div class="shrink-0 flex items-center gap-2">
                        <span id="atsEngineBadge" class="px-3 py-1 rounded-full text-xs font-black bg-violet-100 text-violet-800 border border-violet-200">
                            GROQ LLAMA-3 ATS
                        </span>
                    </div>
                </div>

                <!-- Upload Form -->
                <form id="resumeScanForm" onsubmit="event.preventDefault(); runResumeScan();" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs uppercase font-bold text-gray-600 mb-1.5">
                                Select Candidate PDF Resume (Max 10MB)
                            </label>
                            <input type="file" id="resumeFileInput" accept="application/pdf,.pdf" required
                                class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 border border-gray-300 rounded-xl cursor-pointer bg-gray-50/50" />
                        </div>
                        <div>
                            <label class="block text-xs uppercase font-bold text-gray-600 mb-1.5">
                                Target Role Benchmark
                            </label>
                            <input type="text" id="resumeTargetRoleInput" value="Junior Backend Laravel Engineer" placeholder="e.g. Junior Backend Laravel Engineer"
                                class="w-full rounded-xl border-gray-300 text-xs font-semibold text-gray-800 focus:border-violet-500 focus:ring-violet-500" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-[11px] text-gray-400">
                            Powered by PyMuPDF / pdfplumber parser + native stream fallback.
                        </span>
                        <button type="submit" id="scanResumeBtn"
                            class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs shadow-sm transition inline-flex items-center gap-2">
                            <svg id="scanResumeSpinner" class="animate-spin h-3.5 w-3.5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span id="scanResumeBtnText">Extract Text & Score ATS &rarr;</span>
                        </button>
                    </div>
                </form>

                <!-- Scan Results Panel (Hidden until scan completed) -->
                <div id="resumeResultsPanel" class="hidden space-y-6 pt-4 border-t border-gray-100">
                    <!-- Top Metrics Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                        <!-- Overall Score Card -->
                        <div class="p-5 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-700 text-white shadow-md flex flex-col justify-between">
                            <div>
                                <span class="text-xs uppercase font-bold tracking-wider text-violet-200">Overall ATS Score</span>
                                <div class="flex items-baseline gap-2 mt-2">
                                    <span id="overallAtsScore" class="text-4xl font-black">0</span>
                                    <span class="text-lg font-bold text-violet-200">/ 100</span>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-white/20">
                                <span id="atsReadinessBadge" class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-white/20 text-white">
                                    Evaluation Ready
                                </span>
                            </div>
                        </div>

                        <!-- 4 Category Progress Bars -->
                        <div class="lg:col-span-3 p-5 rounded-2xl border border-gray-200 bg-gray-50/50 space-y-3.5">
                            <h5 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                4-Category Weighted ATS Breakdown
                            </h5>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Keyword Matching (35%) -->
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-bold text-gray-700">Keyword Matching (35%)</span>
                                        <span id="scoreKeywordVal" class="font-bold text-violet-600">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div id="scoreKeywordBar" class="bg-violet-600 h-2 rounded-full" style="width: 0%"></div>
                                    </div>
                                </div>

                                <!-- Technical Skills (35%) -->
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-bold text-gray-700">Technical Skills (35%)</span>
                                        <span id="scoreTechVal" class="font-bold text-indigo-600">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div id="scoreTechBar" class="bg-indigo-600 h-2 rounded-full" style="width: 0%"></div>
                                    </div>
                                </div>

                                <!-- Work Experience (20%) -->
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-bold text-gray-700">Work Experience (20%)</span>
                                        <span id="scoreExpVal" class="font-bold text-blue-600">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div id="scoreExpBar" class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                                    </div>
                                </div>

                                <!-- Formatting (10%) -->
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-bold text-gray-700">Formatting & Structure (10%)</span>
                                        <span id="scoreFormatVal" class="font-bold text-emerald-600">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div id="scoreFormatBar" class="bg-emerald-600 h-2 rounded-full" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Matched & Missing Keywords -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40 space-y-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-900 block">
                                ✓ Matched Target Keywords
                            </span>
                            <div id="matchedKeywordsContainer" class="flex flex-wrap gap-1.5 pt-1"></div>
                        </div>

                        <div class="p-4 rounded-xl border border-rose-200 bg-rose-50/40 space-y-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-rose-900 block">
                                + Missing / High-Priority Keywords
                            </span>
                            <div id="missingKeywordsContainer" class="flex flex-wrap gap-1.5 pt-1"></div>
                        </div>
                    </div>

                    <!-- Improvement Suggestions & Section Rewrites -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Suggestions -->
                        <div class="p-5 rounded-xl border border-gray-200 bg-white space-y-3">
                            <h5 class="text-xs font-black uppercase tracking-wider text-gray-700">
                                💡 Actionable ATS Improvement Suggestions
                            </h5>
                            <ul id="improvementSuggestionsList" class="space-y-2 text-xs text-gray-600"></ul>
                        </div>

                        <!-- Rewrites -->
                        <div class="p-5 rounded-xl border border-gray-200 bg-white space-y-3">
                            <h5 class="text-xs font-black uppercase tracking-wider text-gray-700">
                                ✍️ Section Rewrite Recommendations
                            </h5>
                            <div id="sectionRewritesContainer" class="space-y-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module 6: Placement & Interview Readiness Scorecard (W10D2) -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-600 text-white">
                                Module 6
                            </span>
                            <span class="text-xs font-bold text-emerald-700">Cynaris Readiness Index</span>
                        </div>
                        <h4 class="text-xl font-black text-gray-900 mt-1">
                            Placement & Interview Readiness Scorecard
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Multi-metric weighted scoring synthesising project completion, quiz assessments, platform telemetry, viva examination, and code review rigor.
                        </p>
                    </div>
                    <div class="shrink-0 flex items-center gap-2">
                        <button onclick="recalculateReadiness()" id="recalculateReadinessBtn"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition inline-flex items-center gap-2">
                            <svg id="readinessSpinner" class="animate-spin h-3.5 w-3.5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span id="readinessBtnText">Recalculate Scores &#x21bb;</span>
                        </button>
                    </div>
                </div>

                <!-- Dual Scorecard Gauges -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Placement Readiness Card -->
                    <div class="p-6 rounded-2xl border-2 border-emerald-500/30 bg-gradient-to-br from-emerald-50/50 via-white to-teal-50/40 shadow-sm space-y-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs uppercase font-extrabold tracking-wider text-emerald-800">Primary Benchmark</span>
                                <h5 class="text-lg font-black text-gray-900">Placement Readiness Score</h5>
                                <p class="text-[11px] text-gray-500 font-mono mt-0.5">(Project 40% + Quiz 30% + CIA 10% + Docs 20%)</p>
                            </div>
                            <div class="text-right">
                                <span id="placementScoreDisplay" class="text-4xl font-black text-emerald-600">
                                    {{ $latestReadiness ? round($latestReadiness->placement_readiness_score) : 75 }}%
                                </span>
                                <span id="placementTierDisplay" class="block text-[11px] font-bold text-emerald-800 mt-1">
                                    {{ $latestReadiness ? ($latestReadiness->breakdown['placement']['status_tier'] ?? 'Placement Ready') : 'Placement Ready' }}
                                </span>
                            </div>
                        </div>

                        <!-- Breakdown Bars -->
                        <div class="space-y-3 pt-2 border-t border-emerald-100">
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 font-medium">Project Completion (40% Weight)</span>
                                    <span id="mProjectScore" class="font-bold text-gray-900">{{ $latestReadiness ? $latestReadiness->project_completion_score : 80 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="mProjectBar" class="bg-emerald-500 h-2 rounded-full" style="width: {{ $latestReadiness ? $latestReadiness->project_completion_score : 80 }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 font-medium">Assessment / Quizzes (30% Weight)</span>
                                    <span id="mAssessmentScore" class="font-bold text-gray-900">{{ $latestReadiness ? $latestReadiness->assessment_score : 70 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="mAssessmentBar" class="bg-emerald-600 h-2 rounded-full" style="width: {{ $latestReadiness ? $latestReadiness->assessment_score : 70 }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 font-medium">CIA Platform Usage (10% Weight)</span>
                                    <span id="mCiaScore" class="font-bold text-gray-900">{{ $latestReadiness ? $latestReadiness->cia_usage_score : 70 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="mCiaBar" class="bg-teal-500 h-2 rounded-full" style="width: {{ $latestReadiness ? $latestReadiness->cia_usage_score : 70 }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 font-medium">Documentation & Portfolio (20% Weight)</span>
                                    <span id="mDocScore" class="font-bold text-gray-900">{{ $latestReadiness ? $latestReadiness->documentation_score : 80 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="mDocBar" class="bg-teal-600 h-2 rounded-full" style="width: {{ $latestReadiness ? $latestReadiness->documentation_score : 80 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interview Readiness Card -->
                    <div class="p-6 rounded-2xl border-2 border-indigo-500/30 bg-gradient-to-br from-indigo-50/50 via-white to-sky-50/40 shadow-sm space-y-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs uppercase font-extrabold tracking-wider text-indigo-800">Evaluator Benchmark</span>
                                <h5 class="text-lg font-black text-gray-900">Interview Readiness Score</h5>
                                <p class="text-[11px] text-gray-500 font-mono mt-0.5">(Viva 40% + Review 30% + Demo 20% + Git 10%)</p>
                            </div>
                            <div class="text-right">
                                <span id="interviewScoreDisplay" class="text-4xl font-black text-indigo-600">
                                    {{ $latestReadiness ? round($latestReadiness->interview_readiness_score) : 79 }}%
                                </span>
                                <span id="interviewTierDisplay" class="block text-[11px] font-bold text-indigo-800 mt-1">
                                    {{ $latestReadiness ? ($latestReadiness->breakdown['interview']['status_tier'] ?? 'Interview Ready') : 'Interview Ready' }}
                                </span>
                            </div>
                        </div>

                        <!-- Breakdown Bars -->
                        <div class="space-y-3 pt-2 border-t border-indigo-100">
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 font-medium">Technical Viva (40% Weight)</span>
                                    <span id="mVivaScore" class="font-bold text-gray-900">{{ $latestReadiness ? $latestReadiness->viva_score : 75 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="mVivaBar" class="bg-indigo-500 h-2 rounded-full" style="width: {{ $latestReadiness ? $latestReadiness->viva_score : 75 }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 font-medium">Code Review Rigor (30% Weight)</span>
                                    <span id="mReviewScore" class="font-bold text-gray-900">{{ $latestReadiness ? $latestReadiness->code_review_score : 82 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="mReviewBar" class="bg-indigo-600 h-2 rounded-full" style="width: {{ $latestReadiness ? $latestReadiness->code_review_score : 82 }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 font-medium">Demo Clarity (20% Weight)</span>
                                    <span id="mDemoScore" class="font-bold text-gray-900">{{ $latestReadiness ? $latestReadiness->demo_clarity_score : 78 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="mDemoBar" class="bg-sky-500 h-2 rounded-full" style="width: {{ $latestReadiness ? $latestReadiness->demo_clarity_score : 78 }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 font-medium">Git Quality & Commit Hygiene (10% Weight)</span>
                                    <span id="mGitScore" class="font-bold text-gray-900">{{ $latestReadiness ? $latestReadiness->git_quality_score : 85 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="mGitBar" class="bg-sky-600 h-2 rounded-full" style="width: {{ $latestReadiness ? $latestReadiness->git_quality_score : 85 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module 7: Career Intelligence Report & 6 Engineering Archetypes (W10D3) -->
            <div id="module7ReportSection" class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-blue-600 text-white">
                                Module 7
                            </span>
                            <span class="text-xs font-bold text-blue-700">Groq Career Intelligence & DomPDF Export</span>
                        </div>
                        <h4 class="text-xl font-black text-gray-900 mt-1">
                            Career Intelligence Report & 6 Engineering Archetypes
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Holistic candidate profiling across 6 engineering archetypes, explainable employability score (0-100), AI narrative analysis, and downloadable PDF report.
                        </p>
                    </div>
                    <div class="shrink-0 flex items-center gap-2">
                        <button type="button" id="generateReportBtn" onclick="generateCareerReport()"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition inline-flex items-center gap-2">
                            <svg id="reportSpinner" class="animate-spin h-3.5 w-3.5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span id="reportBtnText">Generate Full Report &circlearrowright;</span>
                        </button>

                        <a id="downloadPdfBtn"
                            href="{{ $latestReport ? route('cia.career.report.download', $latestReport) : '#' }}"
                            class="px-4 py-2 rounded-xl border border-blue-300 text-blue-700 hover:bg-blue-50 font-bold text-xs transition inline-flex items-center gap-1.5 {{ $latestReport ? '' : 'opacity-50 pointer-events-none' }}"
                            target="_blank">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Download Official PDF</span>
                        </a>
                    </div>
                </div>

                <!-- Top Employability + Archetype Summary Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Employability Card -->
                    <div class="p-6 rounded-2xl bg-gradient-to-br from-blue-700 via-indigo-700 to-slate-900 text-white shadow-md flex flex-col justify-between">
                        <div>
                            <span class="text-xs uppercase font-extrabold tracking-wider text-blue-200">Compound Employability</span>
                            <div class="flex items-baseline gap-2 mt-2">
                                <span id="reportEmployabilityDisplay" class="text-5xl font-black text-white">
                                    {{ $latestReport ? round($latestReport->employability_score, 1) : 82.5 }}%
                                </span>
                            </div>
                            <span id="reportStatusTierDisplay" class="inline-block px-2.5 py-1 rounded-md text-xs font-black uppercase tracking-wider bg-white/20 text-white mt-2">
                                {{ $latestReport ? ($latestReport->breakdown['employability']['status_tier'] ?? 'Placement Ready') : 'Placement Ready' }}
                            </span>
                            <p class="text-xs text-blue-100/80 mt-3 leading-relaxed">
                                Weighted synthesis: Primary Archetype (25%) + Placement Readiness (35%) + Interview Readiness (25%) + Skill Verification (15%).
                            </p>
                        </div>
                        <div class="pt-4 border-t border-white/10 mt-4 text-[11px] text-blue-200 font-mono">
                            Status: <span class="text-emerald-300 font-bold">100% Explainable Metrics</span>
                        </div>
                    </div>

                    <!-- Primary Archetype Focus -->
                    <div class="p-6 rounded-2xl bg-blue-50/60 border border-blue-100 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs uppercase font-bold text-blue-600">Primary Engineering Archetype</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-200 text-blue-800">DOMINANT</span>
                            </div>
                            <h5 id="reportPrimaryArchetype" class="text-2xl font-black text-gray-900 mt-2">
                                {{ $latestReport ? $latestReport->primary_archetype : 'Builder' }}
                            </h5>
                            <p id="reportPrimaryTagline" class="text-xs font-semibold text-blue-700 mt-0.5">
                                Systems Architect & Craftsperson
                            </p>
                            <p id="reportPrimaryDesc" class="text-xs text-gray-600 mt-3 leading-relaxed">
                                Focused on robust system architecture, database schema design, high-throughput APIs, and production engineering.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-blue-100 mt-4 flex items-center justify-between text-xs font-bold text-gray-700">
                            <span>Score Rating:</span>
                            <span id="reportPrimaryScore" class="text-blue-700 font-black text-sm">
                                {{ $latestReport ? ($latestReport->archetype_scores[$latestReport->primary_archetype] ?? 85) : 85 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Secondary Archetype Focus -->
                    <div class="p-6 rounded-2xl bg-purple-50/60 border border-purple-100 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs uppercase font-bold text-purple-600">Secondary Archetype</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-purple-200 text-purple-800">COMPLEMENTARY</span>
                            </div>
                            <h5 id="reportSecondaryArchetype" class="text-2xl font-black text-gray-900 mt-2">
                                {{ $latestReport ? ($latestReport->secondary_archetype ?? 'Analyst') : 'Analyst' }}
                            </h5>
                            <p id="reportSecondaryTagline" class="text-xs font-semibold text-purple-700 mt-0.5">
                                Data-Driven & Algorithmic Problem Solver
                            </p>
                            <p id="reportSecondaryDesc" class="text-xs text-gray-600 mt-3 leading-relaxed">
                                Excels in methodical decomposition, automated testing rigor, performance profiling, and structural debugging.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-purple-100 mt-4 flex items-center justify-between text-xs font-bold text-gray-700">
                            <span>Score Rating:</span>
                            <span id="reportSecondaryScore" class="text-purple-700 font-black text-sm">
                                {{ $latestReport ? ($latestReport->archetype_scores[$latestReport->secondary_archetype ?? 'Analyst'] ?? 78) : 78 }}%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 6 Archetypes Grid Meters -->
                <div class="space-y-3">
                    <h5 class="text-xs uppercase font-black tracking-wider text-gray-500">6 Engineering Archetype Breakdown</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="archetypeCardsContainer">
                        @php
                            $scores = $latestReport ? $latestReport->archetype_scores : [
                                'Builder' => 85.0,
                                'Analyst' => 78.0,
                                'Leader' => 72.0,
                                'Innovator' => 68.0,
                                'Specialist' => 82.0,
                                'Generalist' => 75.0,
                            ];
                        @endphp
                        @foreach(['Builder', 'Analyst', 'Leader', 'Innovator', 'Specialist', 'Generalist'] as $arch)
                            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/70 space-y-2">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="text-gray-800">{{ $arch }}</span>
                                    <span id="archScore_{{ $arch }}" class="font-mono text-blue-600">{{ $scores[$arch] ?? 70 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="archBar_{{ $arch }}" class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: {{ $scores[$arch] ?? 70 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- AI Executive Narrative Card -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase font-extrabold tracking-wider text-slate-700">Executive Intelligence Narrative</span>
                        <span id="reportEngineBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-800">
                            {{ $latestReport ? ($latestReport->metadata['engine'] ?? 'Groq LLaMA-3.3') : 'Groq LLaMA-3.3' }}
                        </span>
                    </div>
                    <p id="reportNarrativeText" class="text-xs text-slate-700 leading-relaxed">
                        {{ $latestReport ? $latestReport->narrative : 'Generate the report to produce a personalized, LLM-synthesized narrative evaluating your engineering archetypes, benchmark readiness, and recruiter positioning.' }}
                    </p>
                </div>

                <!-- Recruiter Hiring Pitch -->
                <div class="p-4 rounded-xl bg-blue-50/80 border border-blue-200/80 text-xs text-blue-900 space-y-1">
                    <span class="font-black uppercase tracking-wider text-[10px] text-blue-700 block">Recruiter Pitch:</span>
                    <p id="reportHiringPitch" class="italic font-medium leading-relaxed">
                        {{ $latestReport ? ($latestReport->metadata['hiring_pitch'] ?? 'Candidate is a proven engineering talent ready to deliver immediate value.') : 'Candidate is a proven engineering talent ready to deliver immediate value.' }}
                    </p>
                </div>
            </div>

            <!-- Module 8: CIA Memory Layer - ChromaDB Vector Role Profile Matching (W10D4) -->
            <div id="module8MemorySection" class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-sm space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-teal-600 text-white">
                                Module 8
                            </span>
                            <span class="text-xs font-bold text-teal-700">ChromaDB Vector Role Memory Layer</span>
                        </div>
                        <h4 class="text-xl font-black text-gray-900 mt-1">
                            CIA Memory Layer &bull; 20 Technical Role Semantic Matching
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Real-time vector retrieval in high-dimensional cosine space matching candidate skills against 20 persistent technical role profiles with exact affinity percentages and skill gaps.
                        </p>
                    </div>
                    <div class="shrink-0 flex items-center gap-2">
                        <button type="button" id="queryMemoryBtn" onclick="queryMemoryLayer()"
                            class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition inline-flex items-center gap-2">
                            <svg id="memorySpinner" class="animate-spin h-3.5 w-3.5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span id="memoryBtnText">Query Memory Layer &circlearrowright;</span>
                        </button>

                        <button type="button" id="seedMemoryBtn" onclick="seedMemoryRoles()"
                            class="px-3.5 py-2 rounded-xl border border-teal-300 text-teal-700 hover:bg-teal-50 font-bold text-xs transition inline-flex items-center gap-1.5">
                            <span id="seedBtnText">Sync 20 Roles</span>
                        </button>
                    </div>
                </div>

                <!-- Top 5 Ranked Role Cards -->
                <div class="space-y-4" id="memoryCardsContainer">
                    @forelse($initialMemoryMatches as $idx => $match)
                        <div class="p-5 rounded-2xl border border-gray-100 bg-gradient-to-r from-gray-50/80 to-white hover:border-teal-200 transition shadow-sm space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <span class="w-7 h-7 rounded-lg bg-teal-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                                        #{{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <h5 class="text-base font-black text-gray-900">{{ $match['role'] }}</h5>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-teal-100 text-teal-800">
                                                {{ ucfirst($match['domain']) }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                                {{ $match['experience_level'] }}
                                            </span>
                                            <span class="text-[11px] text-gray-400 font-mono">
                                                Distance: {{ $match['vector_distance'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-black text-teal-600">
                                        {{ $match['affinity_percentage'] }}%
                                    </div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700">Cosine Affinity</span>
                                </div>
                            </div>

                            <p class="text-xs text-gray-600 italic">
                                &ldquo;{{ $match['match_criteria'] }}&rdquo;
                            </p>

                            <!-- Skills Overlap Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-gray-100 text-xs">
                                <div>
                                    <span class="font-bold text-emerald-700 block mb-1">
                                        &check; Matching Competencies ({{ count($match['matching_skills'] ?? []) }}):
                                    </span>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($match['matching_skills'] ?? [] as $mSkill)
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-medium">
                                                {{ $mSkill }}
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-gray-400 italic">No direct keyword overlap yet</span>
                                        @endforelse
                                    </div>
                                </div>

                                <div>
                                    <span class="font-bold text-amber-700 block mb-1">
                                        &Delta; Missing Gaps / Next Steps ({{ count($match['missing_skills'] ?? []) }}):
                                    </span>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($match['missing_skills'] ?? [] as $gap)
                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-medium">
                                                {{ $gap }}
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-emerald-600 font-bold">&starf; 100% Core Competency Match</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <p class="text-xs text-gray-500 font-medium">No memory matches queried yet.</p>
                            <button type="button" onclick="queryMemoryLayer()" class="mt-2 text-xs text-teal-600 hover:text-teal-700 font-bold">
                                Click to Query Memory Layer &rarr;
                            </button>
                        </div>
                    @endforelse
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

        async function runVectorProfileSync() {
            const btn = document.getElementById('syncVectorBtn');
            const spinner = document.getElementById('syncVectorSpinner');
            const btnText = document.getElementById('syncVectorBtnText');

            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.innerText = 'Synchronizing...';

            try {
                const response = await fetch("{{ route('cia.career.profile.sync', [], false) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    body: JSON.stringify({
                        force_refresh: true,
                        sync_source: 'user_manual_trigger'
                    })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Profile synchronization failed.');
                }

                const banner = document.getElementById('syncAlertBanner');
                const alertMsg = document.getElementById('syncAlertMessage');
                const timeBadge = document.getElementById('syncTimestampBadge');

                banner.classList.remove('hidden');
                alertMsg.innerText = `Candidate vector profile synchronized to collection ${data.data.target_collection} (Candidate ID: ${data.data.candidate_id})`;
                timeBadge.innerText = new Date(data.data.last_synced_at).toLocaleTimeString();

                fetchSemanticMatches();
            } catch (err) {
                alert('Vector Sync Error: ' + err.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.innerText = 'Sync Vector Profile';
            }
        }

        async function fetchSemanticMatches() {
            const domain = document.getElementById('filter_domain').value;
            const locationType = document.getElementById('filter_location').value;
            const minAffinity = document.getElementById('filter_min_affinity').value;

            const btn = document.getElementById('filterMatchesBtn');
            const spinner = document.getElementById('filterSpinner');
            const btnText = document.getElementById('filterBtnText');

            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.innerText = 'Filtering...';

            const params = new URLSearchParams({
                domain: domain,
                location_type: locationType,
                min_affinity: minAffinity,
                limit: '5'
            });

            try {
                const response = await fetch(`{{ route('cia.career.matches', [], false) }}?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to retrieve semantic matches.');
                }

                renderSemanticMatches(data.data || []);
            } catch (err) {
                alert('Matching Error: ' + err.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.innerText = 'Apply Vector Filters \u2192';
            }
        }

        function renderSemanticMatches(matches) {
            const container = document.getElementById('semanticMatchesContainer');
            if (!matches || matches.length === 0) {
                container.innerHTML = '<div class="text-center py-8 text-gray-400 text-xs bg-gray-50 rounded-xl border border-dashed border-gray-200">No internship roles matched the selected criteria. Adjust filters or click "Sync Vector Profile".</div>';
                return;
            }

            container.innerHTML = matches.map(m => {
                const affinity = m.affinity_percentage;
                const affClass = affinity >= 80 ? 'text-emerald-600 bg-emerald-50 border-emerald-200' :
                                 (affinity >= 65 ? 'text-indigo-600 bg-indigo-50 border-indigo-200' : 'text-amber-600 bg-amber-50 border-amber-200');

                const matchingPills = (m.matching_skills && m.matching_skills.length > 0)
                    ? m.matching_skills.map(s => `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">✓ ${escapeHtml(s)}</span>`).join(' ')
                    : '<span class="text-[11px] text-gray-400 italic">Foundational competencies aligned</span>';

                const missingPills = (m.missing_skills && m.missing_skills.length > 0)
                    ? `<span class="text-[11px] font-bold text-gray-400 ml-2 mr-1">Delta:</span>` + m.missing_skills.slice(0, 3).map(s => `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">+ ${escapeHtml(s)}</span>`).join(' ')
                    : '';

                const applyBtn = m.application_url
                    ? `<a href="${m.application_url}" class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition inline-flex items-center gap-1">Quick Apply &rarr;</a>`
                    : '';

                return `
                    <div class="p-5 rounded-xl border border-gray-200 bg-white hover:border-teal-300 hover:shadow-md transition flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-2 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-teal-100 text-teal-800">
                                    ${escapeHtml(m.domain)}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-gray-100 text-gray-700">
                                    ${escapeHtml(m.location_type)}
                                </span>
                                <span class="text-xs text-gray-400">&bull; ${escapeHtml(m.location)}</span>
                            </div>

                            <h5 class="text-base font-bold text-gray-900">
                                ${escapeHtml(m.title)}
                            </h5>

                            <p class="text-xs text-gray-500 font-medium">
                                ${escapeHtml(m.company_pseudonym || m.company)} &bull; Stipend: <strong class="text-gray-700">${escapeHtml(m.salary_range || 'Competitive')}</strong>
                            </p>

                            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                <span class="text-[11px] font-bold text-gray-500 mr-1">Matching:</span>
                                ${matchingPills}
                                ${missingPills}
                            </div>
                        </div>

                        <div class="flex md:flex-col items-center md:items-end justify-between md:justify-center gap-3 shrink-0 border-t md:border-t-0 pt-3 md:pt-0 border-gray-100">
                            <div class="text-right">
                                <span class="px-3 py-1 rounded-full text-sm font-black border ${affClass} inline-block">
                                    ${affinity}% Match
                                </span>
                                <span class="block text-[10px] text-gray-400 mt-1 font-mono">
                                    cosine dist: ${m.vector_distance}
                                </span>
                            </div>
                            ${applyBtn}
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function runLearningPathGenerator() {
            const roleSelect = document.getElementById('target_role_select').value;
            let targetRole = roleSelect === 'custom' ? (document.getElementById('custom_role_input').value.trim() || 'Junior Backend Laravel Engineer') : roleSelect;

            const btn = document.getElementById('generatePathBtn');
            const spinner = document.getElementById('generatePathSpinner');
            const btnText = document.getElementById('generatePathBtnText');

            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.innerText = 'LangGraph Orchestrating...';

            try {
                const response = await fetch("{{ route('cia.career.learning-path', [], false) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    body: JSON.stringify({
                        target_role: targetRole,
                    })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Learning path generation failed.');
                }

                renderLearningPath(data);
            } catch (err) {
                alert('Learning Path Error: ' + err.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.innerText = 'Generate 8-Week Curriculum \u2192';
            }
        }

        function renderLearningPath(data) {
            const metricsBar = document.getElementById('pathMetricsBar');
            metricsBar.classList.remove('hidden');

            document.getElementById('pathTotalWeeks').innerText = `${data.total_weeks} Weeks`;
            document.getElementById('pathTotalHours').innerText = `${data.total_hours} Hours`;
            document.getElementById('pathBaselineTier').innerText = data.baseline_tier || 'Intermediate';

            const nodes = data.workflow_metadata?.nodes_executed || [];
            document.getElementById('pathNodesExecuted').innerText = `${nodes.length} Nodes Completed`;

            const engineName = data.workflow_metadata?.engine || 'LangGraph';
            document.getElementById('pathEngineBadge').innerText = engineName.toUpperCase();

            const container = document.getElementById('learningPathContainer');
            const schedule = data.schedule || [];

            if (schedule.length === 0) {
                container.innerHTML = '<div class="text-center py-8 text-gray-400 text-xs bg-gray-50 rounded-xl border border-dashed border-gray-200">No learning schedule available.</div>';
                return;
            }

            container.innerHTML = schedule.map(w => {
                const skillsHtml = (w.focus_skills || []).map(s => `
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                        ${escapeHtml(s)}
                    </span>
                `).join(' ');

                const objectivesHtml = (w.learning_objectives || []).map(obj => `
                    <li class="flex items-start gap-1.5 text-xs text-gray-700">
                        <span class="text-rose-500 font-bold shrink-0">&bull;</span>
                        <span>${escapeHtml(obj)}</span>
                    </li>
                `).join('');

                const tasksHtml = (w.actionable_tasks || []).map(task => `
                    <li class="flex items-start gap-1.5 text-xs text-gray-600">
                        <span class="text-emerald-500 font-bold shrink-0">✓</span>
                        <span>${escapeHtml(task)}</span>
                    </li>
                `).join('');

                const resourcesHtml = (w.recommended_resources || []).map(res => `
                    <span class="inline-block text-[11px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded font-medium">
                        📖 ${escapeHtml(res)}
                    </span>
                `).join(' ');

                return `
                    <div class="p-5 rounded-2xl border border-gray-200 bg-gradient-to-r from-white via-rose-50/20 to-white shadow-sm hover:shadow-md transition space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-gray-100 gap-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-rose-600 text-white">
                                    Week #${w.week_number}
                                </span>
                                <h5 class="font-bold text-sm sm:text-base text-gray-900">
                                    ${escapeHtml(w.theme)}
                                </h5>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-200 self-start sm:self-auto shrink-0">
                                ~${w.estimated_hours}h Estimated
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[11px] font-bold text-gray-400 mr-1 uppercase">Target Competencies:</span>
                            ${skillsHtml}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                            <div class="bg-white p-3.5 rounded-xl border border-gray-100 space-y-2">
                                <span class="text-[11px] font-bold text-rose-900 uppercase block tracking-wider">Learning Objectives:</span>
                                <ul class="space-y-1">
                                    ${objectivesHtml}
                                </ul>
                            </div>

                            <div class="bg-white p-3.5 rounded-xl border border-gray-100 space-y-2">
                                <span class="text-[11px] font-bold text-emerald-900 uppercase block tracking-wider">Actionable Practice Tasks:</span>
                                <ul class="space-y-1">
                                    ${tasksHtml}
                                </ul>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pt-2 border-t border-gray-100 text-xs gap-2">
                            <div class="flex flex-wrap items-center gap-1.5">
                                ${resourcesHtml}
                            </div>
                            <div class="flex items-center gap-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-200 shrink-0">
                                <span>🎯 Milestone Checkpoint:</span>
                                <span>${escapeHtml(w.checkpoint_quiz)}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function runResumeScan() {
            const fileInput = document.getElementById('resumeFileInput');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Please select a valid candidate PDF resume to scan.');
                return;
            }

            const file = fileInput.files[0];
            if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                alert('Only PDF files (.pdf) are supported.');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                alert('File size exceeds 10MB limit.');
                return;
            }

            const targetRole = document.getElementById('resumeTargetRoleInput').value.trim() || 'Junior Backend Laravel Engineer';
            const btn = document.getElementById('scanResumeBtn');
            const spinner = document.getElementById('scanResumeSpinner');
            const btnText = document.getElementById('scanResumeBtnText');

            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.innerText = 'Extracting & Scoring ATS...';

            const formData = new FormData();
            formData.append('resume', file);
            formData.append('target_role', targetRole);

            try {
                const response = await fetch("{{ route('cia.career.resume.scan', [], false) }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    body: formData,
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Resume scan failed.');
                }

                renderAtsResults(data);
            } catch (err) {
                alert('Resume ATS Error: ' + err.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.innerText = 'Extract Text & Score ATS \u2192';
            }
        }

        function renderAtsResults(data) {
            const panel = document.getElementById('resumeResultsPanel');
            panel.classList.remove('hidden');

            const overall = Math.round(data.overall_ats_score || 0);
            document.getElementById('overallAtsScore').innerText = overall;

            const badge = document.getElementById('atsReadinessBadge');
            if (overall >= 80) {
                badge.innerText = 'Top Candidate Match';
                badge.className = 'inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-400/30 text-white';
            } else if (overall >= 60) {
                badge.innerText = 'Competitive Profile';
                badge.className = 'inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-amber-400/30 text-white';
            } else {
                badge.innerText = 'Improvement Recommended';
                badge.className = 'inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-rose-400/30 text-white';
            }

            const engine = data.engine || 'GROQ_LLAMA3';
            document.getElementById('atsEngineBadge').innerText = engine.toUpperCase();

            // 4 Category scores
            const scores = data.scores || {};
            const kw = Math.round(scores.keyword_matching || 0);
            const tech = Math.round(scores.technical_skills || 0);
            const exp = Math.round(scores.work_experience || 0);
            const fmt = Math.round(scores.formatting || 0);

            document.getElementById('scoreKeywordVal').innerText = `${kw}%`;
            document.getElementById('scoreKeywordBar').style.width = `${kw}%`;

            document.getElementById('scoreTechVal').innerText = `${tech}%`;
            document.getElementById('scoreTechBar').style.width = `${tech}%`;

            document.getElementById('scoreExpVal').innerText = `${exp}%`;
            document.getElementById('scoreExpBar').style.width = `${exp}%`;

            document.getElementById('scoreFormatVal').innerText = `${fmt}%`;
            document.getElementById('scoreFormatBar').style.width = `${fmt}%`;

            // Matched keywords
            const matchedContainer = document.getElementById('matchedKeywordsContainer');
            matchedContainer.innerHTML = (data.matched_keywords || []).map(k => `
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    ✓ ${escapeHtml(k)}
                </span>
            `).join('') || '<span class="text-xs text-gray-400 italic">No matched keywords</span>';

            // Missing keywords
            const missingContainer = document.getElementById('missingKeywordsContainer');
            missingContainer.innerHTML = (data.missing_keywords || []).map(k => `
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                    + ${escapeHtml(k)}
                </span>
            `).join('') || '<span class="text-xs text-gray-400 italic">No critical keywords missing</span>';

            // Improvement suggestions
            const suggestionsList = document.getElementById('improvementSuggestionsList');
            suggestionsList.innerHTML = (data.improvement_suggestions || []).map(s => `
                <li class="flex items-start gap-2">
                    <span class="text-violet-500 font-bold shrink-0">&bull;</span>
                    <span>${escapeHtml(s)}</span>
                </li>
            `).join('') || '<li class="text-gray-400 italic">No suggestions provided.</li>';

            // Section rewrites
            const rewritesContainer = document.getElementById('sectionRewritesContainer');
            rewritesContainer.innerHTML = (data.section_rewrites || []).map(r => `
                <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/50 space-y-1.5 text-xs">
                    <span class="font-bold text-gray-800 block">${escapeHtml(r.section || 'Experience')}</span>
                    <p class="text-rose-700 bg-rose-50/60 p-2 rounded-lg border border-rose-100">
                        <strong class="font-semibold text-rose-900">Current Weakness:</strong> ${escapeHtml(r.current_weakness)}
                    </p>
                    <p class="text-emerald-800 bg-emerald-50/60 p-2 rounded-lg border border-emerald-100">
                        <strong class="font-semibold text-emerald-900">Suggested Action Statement:</strong> ${escapeHtml(r.suggested_rewrite)}
                    </p>
                </div>
            `).join('') || '<div class="text-xs text-gray-400 italic">No rewrite recommendations needed.</div>';

            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        async function recalculateReadiness() {
            const btn = document.getElementById('recalculateReadinessBtn');
            const spinner = document.getElementById('readinessSpinner');
            const btnText = document.getElementById('readinessBtnText');

            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.innerText = 'Evaluating...';

            try {
                const response = await fetch("{{ route('cia.career.readiness.calculate', [], false) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    body: JSON.stringify({}),
                });

                const json = await response.json();
                if (!response.ok || !json.success) {
                    throw new Error(json.message || 'Failed to recalculate readiness.');
                }

                renderReadinessScorecard(json.data);
            } catch (err) {
                alert('Readiness Error: ' + err.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.innerText = 'Recalculate Scores \u21bb';
            }
        }

        function renderReadinessScorecard(data) {
            const pScore = Math.round(data.placement_readiness_score || 0);
            const iScore = Math.round(data.interview_readiness_score || 0);

            document.getElementById('placementScoreDisplay').innerText = `${pScore}%`;
            document.getElementById('interviewScoreDisplay').innerText = `${iScore}%`;

            if (data.breakdown?.placement?.status_tier) {
                document.getElementById('placementTierDisplay').innerText = data.breakdown.placement.status_tier;
            }
            if (data.breakdown?.interview?.status_tier) {
                document.getElementById('interviewTierDisplay').innerText = data.breakdown.interview.status_tier;
            }

            // Placement sub-metrics
            const proj = Math.round(data.project_completion_score || 0);
            const asmt = Math.round(data.assessment_score || 0);
            const cia = Math.round(data.cia_usage_score || 0);
            const doc = Math.round(data.documentation_score || 0);

            document.getElementById('mProjectScore').innerText = `${proj}%`;
            document.getElementById('mProjectBar').style.width = `${proj}%`;
            document.getElementById('mAssessmentScore').innerText = `${asmt}%`;
            document.getElementById('mAssessmentBar').style.width = `${asmt}%`;
            document.getElementById('mCiaScore').innerText = `${cia}%`;
            document.getElementById('mCiaBar').style.width = `${cia}%`;
            document.getElementById('mDocScore').innerText = `${doc}%`;
            document.getElementById('mDocBar').style.width = `${doc}%`;

            // Interview sub-metrics
            const viva = Math.round(data.viva_score || 0);
            const rev = Math.round(data.code_review_score || 0);
            const demo = Math.round(data.demo_clarity_score || 0);
            const git = Math.round(data.git_quality_score || 0);

            document.getElementById('mVivaScore').innerText = `${viva}%`;
            document.getElementById('mVivaBar').style.width = `${viva}%`;
            document.getElementById('mReviewScore').innerText = `${rev}%`;
            document.getElementById('mReviewBar').style.width = `${rev}%`;
            document.getElementById('mDemoScore').innerText = `${demo}%`;
            document.getElementById('mDemoBar').style.width = `${demo}%`;
            document.getElementById('mGitScore').innerText = `${git}%`;
            document.getElementById('mGitBar').style.width = `${git}%`;
        }

        async function generateCareerReport() {
            const btn = document.getElementById('generateReportBtn');
            const spinner = document.getElementById('reportSpinner');
            const btnText = document.getElementById('reportBtnText');

            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.innerText = 'Analyzing Archetypes...';

            try {
                const response = await fetch("{{ route('cia.career.report.generate', [], false) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    body: JSON.stringify({}),
                });

                const json = await response.json();
                if (!response.ok || !json.success) {
                    throw new Error(json.message || 'Failed to generate career report.');
                }

                renderCareerReport(json.data);
            } catch (err) {
                alert('Career Report Error: ' + err.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.innerText = 'Generate Full Report \u21bb';
            }
        }

        function renderCareerReport(report) {
            const empScore = Math.round(report.employability_score || 0);
            document.getElementById('reportEmployabilityDisplay').innerText = `${empScore}%`;

            if (report.breakdown?.employability?.status_tier) {
                document.getElementById('reportStatusTierDisplay').innerText = report.breakdown.employability.status_tier;
            }

            // Primary & Secondary Archetypes
            if (report.primary_archetype) {
                document.getElementById('reportPrimaryArchetype').innerText = report.primary_archetype;
                const pScore = Math.round(report.archetype_scores?.[report.primary_archetype] || 0);
                document.getElementById('reportPrimaryScore').innerText = `${pScore}%`;
            }
            if (report.secondary_archetype) {
                document.getElementById('reportSecondaryArchetype').innerText = report.secondary_archetype;
                const sScore = Math.round(report.archetype_scores?.[report.secondary_archetype] || 0);
                document.getElementById('reportSecondaryScore').innerText = `${sScore}%`;
            }

            // 6 Archetype meters
            if (report.archetype_scores) {
                for (const [arch, score] of Object.entries(report.archetype_scores)) {
                    const elScore = document.getElementById(`archScore_${arch}`);
                    const elBar = document.getElementById(`archBar_${arch}`);
                    if (elScore) elScore.innerText = `${Math.round(score)}%`;
                    if (elBar) elBar.style.width = `${Math.round(score)}%`;
                }
            }

            // Narrative & Pitch
            if (report.narrative) {
                document.getElementById('reportNarrativeText').innerText = report.narrative;
            }
            if (report.metadata?.hiring_pitch) {
                document.getElementById('reportHiringPitch').innerText = report.metadata.hiring_pitch;
            }
            if (report.metadata?.engine) {
                document.getElementById('reportEngineBadge').innerText = report.metadata.engine;
            }

            // Update PDF download link
            const downloadBtn = document.getElementById('downloadPdfBtn');
            if (downloadBtn && report.id) {
                downloadBtn.href = `/candidate/cia/career/report/${report.id}/download`;
                downloadBtn.classList.remove('opacity-50', 'pointer-events-none');
            }
        }

        async function queryMemoryLayer() {
            const btn = document.getElementById('queryMemoryBtn');
            const spinner = document.getElementById('memorySpinner');
            const btnText = document.getElementById('memoryBtnText');

            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.innerText = 'Searching Vectors...';

            try {
                const response = await fetch("{{ route('cia.career.memory.match', [], false) }}?limit=5", {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const json = await response.json();
                if (!response.ok || !json.success) {
                    throw new Error(json.message || 'Failed to query memory layer.');
                }

                renderMemoryCards(json.data);
            } catch (err) {
                alert('Memory Layer Error: ' + err.message);
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.innerText = 'Query Memory Layer \u21bb';
            }
        }

        async function seedMemoryRoles() {
            const btn = document.getElementById('seedMemoryBtn');
            const text = document.getElementById('seedBtnText');

            btn.disabled = true;
            text.innerText = 'Syncing...';

            try {
                const response = await fetch("{{ route('cia.career.memory.seed', [], false) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    body: JSON.stringify({}),
                });

                const json = await response.json();
                if (!response.ok || !json.success) {
                    throw new Error(json.message || 'Failed to sync roles.');
                }

                alert('Success: 20 technical role profiles synchronized to ChromaDB memory layer.');
                queryMemoryLayer();
            } catch (err) {
                alert('Seed Error: ' + err.message);
            } finally {
                btn.disabled = false;
                text.innerText = 'Sync 20 Roles';
            }
        }

        function renderMemoryCards(matches) {
            const container = document.getElementById('memoryCardsContainer');
            if (!container || !matches || !matches.length) return;

            let html = '';
            matches.forEach((m, idx) => {
                const matchingPills = (m.matching_skills || []).map(s => `
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-medium">
                        ${s}
                    </span>
                `).join('') || '<span class="text-[11px] text-gray-400 italic">No direct keyword overlap yet</span>';

                const missingPills = (m.missing_skills || []).map(s => `
                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-medium">
                        ${s}
                    </span>
                `).join('') || '<span class="text-[11px] text-emerald-600 font-bold">&starf; 100% Core Competency Match</span>';

                html += `
                    <div class="p-5 rounded-2xl border border-gray-100 bg-gradient-to-r from-gray-50/80 to-white hover:border-teal-200 transition shadow-sm space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-lg bg-teal-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                                    #${idx + 1}
                                </span>
                                <div>
                                    <h5 class="text-base font-black text-gray-900">${m.role}</h5>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-teal-100 text-teal-800">
                                            ${m.domain || 'backend'}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                            ${m.experience_level || 'Mid-Level'}
                                        </span>
                                        <span class="text-[11px] text-gray-400 font-mono">
                                            Distance: ${m.vector_distance}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-black text-teal-600">
                                    ${m.affinity_percentage}%
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700">Cosine Affinity</span>
                            </div>
                        </div>

                        <p class="text-xs text-gray-600 italic">
                            &ldquo;${m.match_criteria}&rdquo;
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-gray-100 text-xs">
                            <div>
                                <span class="font-bold text-emerald-700 block mb-1">
                                    &check; Matching Competencies (${(m.matching_skills || []).length}):
                                </span>
                                <div class="flex flex-wrap gap-1">
                                    ${matchingPills}
                                </div>
                            </div>

                            <div>
                                <span class="font-bold text-amber-700 block mb-1">
                                    &Delta; Missing Gaps / Next Steps (${(m.missing_skills || []).length}):
                                </span>
                                <div class="flex flex-wrap gap-1">
                                    ${missingPills}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }
    </script>
</x-app-layout>
