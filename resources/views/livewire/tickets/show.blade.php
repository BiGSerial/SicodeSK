@php
    use Illuminate\Support\Str;
@endphp

<div class="text-zinc-100">
    @section('breadcrumb')
        <a href="{{ route('tickets.index') }}" class="hover:underline" wire:navigate>Tickets</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-400">{{ $ticket->code ?? 'Detalhe' }}</span>
    @endsection

    <div class="py-3">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('tickets.index') }}" class="text-zinc-100 hover:text-white" wire:navigate>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-lg font-semibold">{{ $ticket->title }}</h1>
                    <p class="text-xs text-zinc-400">{{ $ticket->code }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $priority = $ticket->priority;
                    $prioritySlug = $priority?->slug;
                    $priorityName = $priority?->name ?? '—';
                    $priorityClass = match ($prioritySlug) {
                        'low' => 'bg-emerald-700/30 text-emerald-300 border-emerald-700/60',
                        'medium' => 'bg-sky-700/30 text-sky-300 border-sky-700/60',
                        'high' => 'bg-amber-700/30 text-amber-300 border-amber-700/60',
                        'urgent' => 'bg-rose-700/30 text-rose-300 border-rose-700/60',
                        default => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                    };
                    $statusClass = match ($ticket->status) {
                        'open' => 'bg-sky-700/30 text-sky-300 border-sky-700/60',
                        'in_progress' => 'bg-indigo-700/30 text-indigo-300 border-indigo-700/60',
                        'paused' => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                        'resolved' => 'bg-emerald-700/30 text-emerald-300 border-emerald-700/60',
                        'closed' => 'bg-zinc-800/60 text-zinc-400 border-zinc-700/60',
                        default => 'bg-zinc-700/30 text-zinc-200 border-zinc-700/60',
                    };
                @endphp
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs {{ $priorityClass }}">
                    Prioridade: {{ $priorityName }}
                </span>
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs {{ $statusClass }}">
                    Status: {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                </span>
                @if ($ticket->is_late)
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs bg-red-900/50 border border-red-700 text-red-200">
                        SLA vencido
                    </span>
                @endif
            </div>
        </div>
    </div>

    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-10 space-y-6">
        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg">
            <h2 class="text-base font-semibold mb-4">Resumo</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-zinc-400">Área</p>
                    <p class="text-zinc-100">{{ $ticket->area->name ?? '—' }}@if ($ticket->area?->sigla)
                            <span class="text-xs text-zinc-400"> ({{ $ticket->area->sigla }})</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-zinc-400">Tipo</p>
                    <p class="text-zinc-100">{{ $ticket->type->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-400">Categoria</p>
                    <p class="text-zinc-100">{{ $ticket->category->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-400">Subcategoria</p>
                    <p class="text-zinc-100">{{ $ticket->subcategory->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-400">Workflow</p>
                    <p class="text-zinc-100">{{ $ticket->workflow->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-400">Etapa atual</p>
                    <p class="text-zinc-100">{{ $ticket->step->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-zinc-400">SLA estimado</p>
                    <p class="text-zinc-100">
                        @if ($ticket->sla_due_at)
                            {{ $ticket->sla_due_at->diffForHumans() }}
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-zinc-400">Atualizado em</p>
                    <p class="text-zinc-100">{{ $ticket->updated_at?->diffForHumans() }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg">
            <h2 class="text-base font-semibold mb-4">Detalhes do Ticket</h2>
            <article class="space-y-4 text-sm">
                <div>
                    <p class="text-zinc-400 uppercase tracking-wide text-xs">Descrição</p>
                    <p class="mt-1 leading-relaxed text-zinc-100 whitespace-pre-line">{{ $ticket->description ?? '—' }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-zinc-400 uppercase tracking-wide text-xs">Solicitante</p>
                        <p class="mt-1 text-zinc-100">{{ data_get($participants, 'requester.name', '—') }}</p>
                        <p class="text-xs text-zinc-400">{{ data_get($participants, 'requester.email', '') }}</p>
                    </div>
                    <div>
                        <p class="text-zinc-400 uppercase tracking-wide text-xs">Executor</p>
                        <p class="mt-1 text-zinc-100">{{ data_get($participants, 'executor.name', 'Não atribuído') }}</p>
                        <p class="text-xs text-zinc-400">{{ data_get($participants, 'executor.email', '') }}</p>
                    </div>
                    <div>
                        <p class="text-zinc-400 uppercase tracking-wide text-xs">Gestor</p>
                        <p class="mt-1 text-zinc-100">{{ data_get($participants, 'manager.name', '—') }}</p>
                        <p class="text-xs text-zinc-400">{{ data_get($participants, 'manager.email', '') }}</p>
                    </div>
                </div>
            </article>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-[2fr,1fr] gap-6">
            <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg" x-data="{ view: 'summary' }">
                @php
                    $timelineCollection = collect($timeline);
                    $timelineSummary = $timelineCollection
                        ->groupBy('type')
                        ->map(function ($group) {
                            $latest = $group->first();

                            return [
                                'label' => $latest['label'],
                                'count' => $group->count(),
                                'latest' => $latest['happened_at'],
                                'actors' => $group
                                    ->pluck('actor.name')
                                    ->filter()
                                    ->unique()
                                    ->take(3)
                                    ->implode(', '),
                            ];
                        });

                    $activityIcons = [
                        'created' => ['bg' => 'bg-emerald-500/20 text-emerald-200', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>'],
                        'status_changed' => ['bg' => 'bg-sky-500/20 text-sky-200', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 8.293a1 1 0 00-1.414 1.414l2 2A1 1 0 0010 12h4a1 1 0 100-2h-3.586l-1.707-1.707z" clip-rule="evenodd" /></svg>'],
                        'comment_added' => ['bg' => 'bg-purple-500/20 text-purple-200', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h2v3l4-3h6a2 2 0 002-2z" /></svg>'],
                        'attachment_added' => ['bg' => 'bg-amber-500/20 text-amber-200', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v6a5 5 0 1010 0V7a1 1 0 10-2 0v6a3 3 0 11-6 0V7a1 1 0 012 0v6a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" /></svg>'],
                        'assigned' => ['bg' => 'bg-indigo-500/20 text-indigo-200', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13 7H7v6h6V7z" /><path fill-rule="evenodd" d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm8 4a1 1 0 011 1v6a1 1 0 01-1 1H7a1 1 0 01-1-1V8a1 1 0 011-1h6z" clip-rule="evenodd" /></svg>'],
                    ];
                @endphp

                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold">Linha do tempo</h2>
                    <div class="flex items-center gap-2 rounded-lg border border-[#2b3649] bg-[#0f172a] p-1 text-xs">
                        <button type="button" @click="view = 'summary'"
                            :class="view === 'summary' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400'"
                            class="rounded-md px-3 py-1 transition">Resumo</button>
                        <button type="button" @click="view = 'full'"
                            :class="view === 'full' ? 'bg-edp-iceblue-100/20 text-edp-iceblue-100' : 'text-zinc-400'"
                            class="rounded-md px-3 py-1 transition">Completo</button>
                    </div>
                </div>

                <div class="mt-4 space-y-4" x-show="view === 'summary'" x-transition x-cloak>
                    @if ($timelineSummary->isEmpty())
                        <p class="text-sm text-zinc-500">Nenhum evento registrado.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($timelineSummary as $type => $summary)
                                @php
                                    $icon = $activityIcons[$type] ?? ['bg' => 'bg-zinc-500/20 text-zinc-200', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-5.586L6.707 11.12a1 1 0 00-1.414 1.415l3.25 3.25a1 1 0 001.414 0l5.25-5.25a1 1 0 00-1.414-1.415L9 12.414z" clip-rule="evenodd" /></svg>'];
                                @endphp
                                <li class="flex items-start gap-3 rounded-lg border border-[#2b3649] bg-[#0f172a] p-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-full {{ $icon['bg'] }}">
                                        {!! $icon['svg'] !!}
                                    </span>
                                    <div class="flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-medium text-zinc-100">{{ $summary['label'] }}</p>
                                            <span class="rounded-full border border-[#2b3649] bg-[#121a2a] px-2 py-0.5 text-[11px] text-zinc-400">
                                                {{ $summary['count'] }} registro(s)
                                            </span>
                                        </div>
                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-zinc-400">
                                            <span>{{ $summary['latest'] }}</span>
                                            @if ($summary['actors'])
                                                <span class="text-zinc-600">•</span>
                                                <span>{{ $summary['actors'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button" @click="view = 'full'"
                                        class="inline-flex items-center gap-1 rounded-lg border border-[#2b3649] px-2.5 py-1 text-[11px] text-edp-iceblue-100 hover:border-edp-iceblue-100/60">
                                        Detalhes
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="mt-4 space-y-4" x-show="view === 'full'" x-transition x-cloak>
                    @if (empty($timeline))
                        <p class="text-sm text-zinc-500">Nenhum evento registrado.</p>
                    @else
                        <ul class="space-y-3 text-sm">
                            @foreach ($timeline as $event)
                                @php
                                    $actor = $event['actor'];
                                    $meta = $event['meta'] ?? [];
                                @endphp

                                <li class="rounded-lg border border-[#2b3649] bg-[#0f172a] p-3">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div class="flex items-start gap-3">
                                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-edp-iceblue-100 shadow"></span>
                                            <div>
                                                <p class="font-medium text-zinc-100">{{ $event['label'] }}</p>
                                                @if ($actor)
                                                    <p class="text-xs text-zinc-400">
                                                        {{ $actor['name'] }}
                                                        @if ($actor['email'])
                                                            <span class="text-zinc-600">•</span> {{ $actor['email'] }}
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="text-xs text-zinc-400 whitespace-nowrap">{{ $event['happened_at'] }}</span>
                                    </div>

                                    <div class="mt-3 text-xs text-zinc-300">
                                        @switch($event['type'])
                                            @case('status_changed')
                                                <div class="flex flex-wrap gap-2">
                                                    <span class="inline-flex items-center gap-1 rounded-full border border-[#2b3649] bg-[#121a2a] px-2 py-0.5">
                                                        <span class="text-[10px] uppercase tracking-wide text-zinc-500">De</span>
                                                        <strong class="text-zinc-100">{{ $meta['Status Anterior'] ?? '—' }}</strong>
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 rounded-full border border-[#2b3649] bg-[#121a2a] px-2 py-0.5">
                                                        <span class="text-[10px] uppercase tracking-wide text-zinc-500">Para</span>
                                                        <strong class="text-zinc-100">{{ $meta['Novo Status'] ?? '—' }}</strong>
                                                    </span>
                                                </div>
                                                @break

                                            @case('attachment_added')
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="inline-flex items-center gap-1 rounded-full border border-amber-500/40 bg-amber-500/10 px-2 py-0.5 text-amber-200">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v6a5 5 0 1010 0V7a1 1 0 10-2 0v6a3 3 0 11-6 0V7a1 1 0 012 0v6a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span>{{ Str::limit($meta['Nome Original'] ?? $meta['Arquivo'] ?? 'Anexo', 40) }}</span>
                                                    </span>
                                                    <a href="#attachments" class="text-edp-iceblue-100 hover:underline text-[11px]">
                                                        Ver todos os anexos
                                                    </a>
                                                </div>
                                                @break

                                            @case('assigned')
                                            @case('reassigned')
                                                <div class="flex flex-wrap gap-2">
                                                    @if (isset($meta['Atribuído Para']))
                                                        <span class="inline-flex items-center gap-1 rounded-full border border-[#2b3649] bg-[#121a2a] px-2 py-0.5">
                                                            <span class="text-[10px] uppercase tracking-wide text-zinc-500">Responsável</span>
                                                            <strong class="text-zinc-100">{{ $meta['Atribuído Para'] }}</strong>
                                                        </span>
                                                    @endif
                                                    @if (isset($meta['Atribuído Por']))
                                                        <span class="inline-flex items-center gap-1 rounded-full border border-[#2b3649] bg-[#121a2a] px-2 py-0.5">
                                                            <span class="text-[10px] uppercase tracking-wide text-zinc-500">Por</span>
                                                            <strong class="text-zinc-100">{{ $meta['Atribuído Por'] }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                                @break

                                            @default
                                                @if (!empty($meta))
                                                    <div class="grid gap-2 sm:grid-cols-2">
                                                        @foreach ($meta as $key => $value)
                                                            <div class="rounded-lg border border-[#2b3649] bg-[#121a2a] px-2.5 py-2">
                                                                <dt class="text-[10px] uppercase tracking-wide text-zinc-500">{{ $key }}</dt>
                                                                <dd class="mt-1 text-[11px] text-zinc-100 truncate" title="{{ is_scalar($value) ? $value : json_encode($value) }}">
                                                                    {{ is_scalar($value) ? Str::limit($value, 60) : json_encode($value) }}
                                                                </dd>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                        @endswitch
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <div class="space-y-6">
                <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg">
                    <h2 class="text-base font-semibold mb-3">Comentários</h2>
                    <ul class="space-y-4 text-sm">
                        @forelse ($comments as $comment)
                            <li class="rounded border border-[#2b3649] bg-[#0f172a] p-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-zinc-100">{{ $comment['author']['name'] ?? 'Sistema' }}</span>
                                    <span class="text-xs text-zinc-400">{{ $comment['created_at'] }}</span>
                                </div>
                                <p class="mt-2 text-zinc-200 whitespace-pre-line">{{ $comment['body'] }}</p>
                            </li>
                        @empty
                            <li class="text-zinc-400 text-sm">Nenhum comentário ainda.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="rounded-xl border border-[#2b3649] bg-[#1b2535] p-6 shadow-lg"
                    x-data="{ imageModal: { open: false, src: null, title: '' } }">
                    <div class="flex items-center justify-between mb-3 gap-3">
                        <h2 class="text-base font-semibold">Anexos</h2>
                        <button type="button" wire:click="downloadSelectedAttachments"
                            class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors border-edp-iceblue-100 text-edp-iceblue-100 hover:text-white hover:bg-edp-iceblue-100/20"
                            wire:loading.attr="disabled" wire:target="downloadSelectedAttachments">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M16 12l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Baixar seleção (.zip)
                        </button>
                    </div>

                    @if ($imageAttachments)
                        <div class="mb-4">
                            <h3 class="text-xs uppercase tracking-wide text-zinc-400 mb-2">Imagens</h3>
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                @foreach ($imageAttachments as $attachment)
                                    <div class="relative border border-[#2b3649] rounded-lg bg-[#0f172a] overflow-hidden group">
                                        <label class="absolute top-2 left-2 z-20 flex items-center justify-center">
                                            <input type="checkbox" value="{{ $attachment['id'] }}" wire:model="selectedAttachments"
                                                class="h-4 w-4 rounded border-[#2b3649] bg-[#0f172a]/80 text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                                        </label>
                                        @if ($attachment['url'])
                                            <button type="button"
                                                @click="imageModal.open = true; imageModal.src = '{{ $attachment['url'] }}'; imageModal.title = '{{ $attachment['filename'] }}'"
                                                class="block w-full">
                                                <img src="{{ $attachment['url'] }}" alt="{{ $attachment['filename'] }}"
                                                    class="w-full h-24 object-cover transition-transform group-hover:scale-[1.02]">
                                            </button>
                                        @endif
                                        <div class="p-2">
                                            <p class="text-xs text-zinc-100 truncate" title="{{ $attachment['filename'] }}">{{ $attachment['filename'] }}</p>
                                            <p class="text-[10px] text-zinc-500 space-x-1">
                                                <span>{{ $attachment['uploaded_at'] }}</span>
                                                @if ($attachment['size_label'])
                                                    <span>• {{ $attachment['size_label'] }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($fileAttachments)
                        <div>
                            <h3 class="text-xs uppercase tracking-wide text-zinc-400 mb-2">Documentos</h3>
                            <ul class="space-y-3 text-sm">
                                @foreach ($fileAttachments as $attachment)
                                    <li class="flex items-start justify-between gap-3 border border-[#2b3649] rounded-lg bg-[#0f172a] px-3 py-2">
                                        <label class="flex items-start gap-3 cursor-pointer select-none">
                                            <input type="checkbox" value="{{ $attachment['id'] }}" wire:model="selectedAttachments"
                                                class="mt-1 h-4 w-4 rounded border-[#2b3649] text-edp-iceblue-100 focus:ring-edp-iceblue-100">
                                            <div>
                                                <p class="text-zinc-100 truncate" title="{{ $attachment['filename'] }}">{{ $attachment['filename'] }}</p>
                                                <p class="text-xs text-zinc-500 space-x-1">
                                                    <span>{{ $attachment['uploaded_at'] }}</span>
                                                    @if ($attachment['size_label'])
                                                        <span>• {{ $attachment['size_label'] }}</span>
                                                    @endif
                                                    @if ($attachment['uploader'])
                                                        <span>• {{ $attachment['uploader']['name'] }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </label>
                                        @if ($attachment['url'])
                                            <a href="{{ $attachment['url'] }}" download
                                                class="text-xs text-zinc-300 hover:text-edp-iceblue-100 whitespace-nowrap">Download</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (!$imageAttachments && !$fileAttachments)
                        <p class="text-zinc-400 text-sm">Nenhum anexo disponível.</p>
                    @endif

                    <div x-show="imageModal.open" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-black/70 p-4"
                        @keydown.escape.window="imageModal.open = false"
                        @click.self="imageModal.open = false">
                        <div class="relative max-w-4xl w-full">
                            <button type="button" class="absolute -top-3 -right-3 h-8 w-8 rounded-full bg-[#0f172a] text-white flex items-center justify-center"
                                @click="imageModal.open = false">
                                ×
                            </button>
                            <img :src="imageModal.src" :alt="imageModal.title" class="w-full rounded-lg border border-[#2b3649]" />
                            <p class="mt-2 text-sm text-zinc-300" x-text="imageModal.title"></p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>
