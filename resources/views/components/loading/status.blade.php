@props([
    'target' => null, // string|array|null
    'text' => 'Estamos preparando tudo...<br>aguarde um instante.',
])

@php
    $targetAttr = $target ? (is_array($target) ? implode(',', $target) : $target) : null;
@endphp

@once
    @push('css')
        <style>
            :root {
                --ld-bg: rgba(15, 23, 42, 0.92);
                --ld-border: rgba(148, 163, 184, 0.22);
                --ld-shadow: 0 18px 45px rgba(9, 17, 38, 0.55);
                --ld-text: #e2e8f0;
                --ld-sub: #94a3b8;
                --ld-a1: var(--edp-primary);
                --ld-a2: var(--edp-iceblue-100);
                --ld-a3: var(--edp-verde-100);
                --ld-a4: var(--edp-cobaltblue-100);
                --ld-pill-bg: rgba(13, 25, 44, 0.75);
                --ld-pill-border: rgba(148, 163, 184, 0.2);
                --ld-bar-bg: rgba(148, 163, 184, 0.25);
                --ld-bar-sheen: linear-gradient(
                    90deg,
                    transparent 0%,
                    rgba(148, 163, 184, 0.4) 25%,
                    rgba(148, 163, 184, 0.9) 50%,
                    rgba(148, 163, 184, 0.4) 75%,
                    transparent 100%
                );
            }

            .loading-aurora {
                position: fixed;
                inset: auto auto 24px 24px;
                z-index: 9999;
                width: 280px;
                border-radius: 16px;
                background: var(--ld-bg);
                border: 1px solid var(--ld-border);
                box-shadow: var(--ld-shadow);
                backdrop-filter: blur(12px);
                color: var(--ld-text);
                overflow: hidden;
                pointer-events: none;
            }

            .loading-aurora::before {
                content: "";
                position: absolute;
                inset: -60% -60% auto auto;
                width: 280%;
                height: 280%;
                background: conic-gradient(from 0deg,
                        var(--ld-a1),
                        var(--ld-a2),
                        var(--ld-a3),
                        var(--ld-a4),
                        var(--ld-a1));
                filter: blur(28px) saturate(115%);
                opacity: 0.3;
                animation: ld-rotate 14s linear infinite;
            }

            .ld-inner {
                position: relative;
                display: grid;
                grid-template-columns: 60px 1fr;
                gap: 12px;
                padding: 16px 18px 12px;
                align-items: center;
            }

            .ld-gauge {
                position: relative;
                width: 48px;
                height: 48px;
            }

            .ld-ring {
                position: absolute;
                inset: 0;
                border-radius: 50%;
                background: conic-gradient(from 0deg, var(--ld-a2) 0 20%, transparent 20% 100%);
                -webkit-mask: radial-gradient(farthest-side, transparent 62%, #000 63%);
                mask: radial-gradient(farthest-side, transparent 62%, #000 63%);
                animation: ld-spin 1.2s linear infinite;
                box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.08) inset;
            }

            .ld-dot {
                position: absolute;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: var(--ld-a3);
                top: 2px;
                left: 50%;
                transform: translate(-50%, 0);
                filter: drop-shadow(0 0 8px rgba(40, 255, 82, 0.5));
                animation: ld-dot 1.2s ease-in-out infinite;
            }

            .ld-center {
                position: absolute;
                inset: 0;
                display: grid;
                place-items: center;
            }

            .ld-pill {
                position: absolute;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background: var(--ld-pill-bg);
                border: 1px solid var(--ld-pill-border);
                backdrop-filter: blur(2px);
                transform: translateZ(0);
            }

            .ld-counter {
                position: relative;
                z-index: 1;
                font-variant-numeric: tabular-nums;
                font-weight: 700;
                font-size: 1rem;
                color: #f8fafc;
                text-shadow: 0 1px 2px rgba(8, 15, 28, 0.45);
            }

            .ld-text {
                line-height: 1.25;
                font-size: 0.95rem;
                font-weight: 600;
            }

            .ld-text strong {
                color: var(--ld-a2);
            }

            .ld-sub {
                margin-top: 4px;
                font-size: 0.8rem;
                color: var(--ld-sub);
            }

            .ld-bar {
                position: relative;
                height: 4px;
                border-radius: 999px;
                overflow: hidden;
                margin: 10px 18px 16px;
                background: var(--ld-bar-bg);
            }

            .ld-bar::before {
                content: "";
                position: absolute;
                inset: 0;
                background: var(--ld-bar-sheen);
                width: 140%;
                transform: translateX(-100%);
                animation: ld-sweep 1.6s ease-in-out infinite;
            }

            .ld-dots {
                display: inline-flex;
                gap: 2px;
                margin-left: 2px;
                vertical-align: baseline;
                color: currentColor;
            }

            .ld-dots span {
                width: 5px;
                height: 5px;
                border-radius: 50%;
                background: currentColor;
                opacity: 0.35;
                animation: ld-bounce 1.1s infinite;
            }

            .ld-dots span:nth-child(2) {
                animation-delay: 0.15s;
            }

            .ld-dots span:nth-child(3) {
                animation-delay: 0.3s;
            }

            @keyframes ld-spin {
                to {
                    transform: rotate(360deg);
                }
            }

            @keyframes ld-rotate {
                to {
                    transform: rotate(360deg);
                }
            }

            @keyframes ld-sweep {
                0% {
                    transform: translateX(-100%);
                }

                50% {
                    transform: translateX(10%);
                }

                100% {
                    transform: translateX(100%);
                }
            }

            @keyframes ld-bounce {
                0%,
                80%,
                100% {
                    transform: translateY(0);
                    opacity: 0.35;
                }

                40% {
                    transform: translateY(-3px);
                    opacity: 1;
                }
            }

            @keyframes ld-dot {
                0%,
                100% {
                    transform: translate(-50%, 0) scale(1);
                }

                50% {
                    transform: translate(-50%, 0) scale(1.15);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .loading-aurora::before,
                .ld-ring,
                .ld-bar::before,
                .ld-dots span,
                .ld-dot {
                    animation: none !important;
                }
            }

            @media (max-width: 640px) {
                .loading-aurora {
                    left: 16px;
                    bottom: 16px;
                    width: 240px;
                }

                .ld-inner {
                    grid-template-columns: 48px 1fr;
                    padding: 14px 16px 10px;
                }
            }
        </style>
    @endpush
@endonce

<div class="loading-aurora" x-data="loader()" x-init="init()" role="status" aria-live="polite" aria-busy="true"
    wire:loading.delay @if ($targetAttr) wire:target="{{ $targetAttr }}" @endif>
    <div class="ld-inner">
        <div class="ld-gauge">
            <div class="ld-ring"><span class="ld-dot"></span></div>
            <div class="ld-center">
                <div class="ld-pill"></div>
                <div class="ld-counter" x-text="percent.toFixed(0) + '%'">0%</div>
            </div>
        </div>

        <div>
            <div class="ld-text">
                {!! $text !!}
                <span class="ld-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            </div>
            <div class="ld-sub">Processando sua solicitação…</div>
        </div>
    </div>

    <div class="ld-bar"></div>
</div>

@once
    @push('scripts')
        <script>
            window.__LD = window.__LD || {
                active: 0
            };

            function loader() {
                return {
                    el: null,
                    percent: 0,
                    running: false,
                    rafId: null,
                    trickleId: null,

                    init() {
                        this.el = this.$el;

                        const compute = () => {
                            const visible = this.isVisible();
                            if (visible && !this.running) this.start();
                            if (!visible && this.running) this.stop(true);
                        };
                        compute();

                        const mo = new MutationObserver(compute);
                        mo.observe(this.el, {
                            attributes: true,
                            attributeFilter: ['style', 'class']
                        });

                        if (window.Livewire && Livewire.hook) {
                            Livewire.hook('message.sent', () => setTimeout(compute, 0));
                            Livewire.hook('message.processed', () => setTimeout(compute, 0));
                        }

                        window.addEventListener('beforeunload', () => this.cleanup());
                    },

                    isVisible() {
                        const style = window.getComputedStyle(this.el);
                        return style.display !== 'none' && style.visibility !== 'hidden';
                    },

                    start() {
                        this.running = true;
                        window.__LD.active++;
                        this.percent = 0;

                        this.trickleId = setInterval(() => {
                            if (!this.running) return;
                            let inc = 0;
                            if (this.percent < 25) inc = Math.random() * 6 + 3;
                            else if (this.percent < 65) inc = Math.random() * 4 + 2;
                            else if (this.percent < 85) inc = Math.random() * 2 + 1;
                            else if (this.percent < 97) inc = Math.random();
                            this.percent = Math.min(this.percent + inc, 99);
                        }, 150);

                        const breathe = () => {
                            if (!this.running) return;
                            this.rafId = requestAnimationFrame(breathe);
                        };
                        this.rafId = requestAnimationFrame(breathe);
                    },

                    stop(finish = false) {
                        if (!this.running) return;
                        this.running = false;

                        if (finish) {
                            const start = this.percent;
                            const dur = 260;
                            const t0 = performance.now();
                            const step = (t) => {
                                const k = Math.min(1, (t - t0) / dur);
                                const eased = 1 - Math.pow(1 - k, 2);
                                this.percent = start + (100 - start) * eased;
                                if (k < 1) this.rafId = requestAnimationFrame(step);
                                else this.cleanup();
                            };
                            this.rafId = requestAnimationFrame(step);
                        } else {
                            this.cleanup();
                        }
                    },

                    cleanup() {
                        if (this.trickleId) {
                            clearInterval(this.trickleId);
                            this.trickleId = null;
                        }
                        if (this.rafId) {
                            cancelAnimationFrame(this.rafId);
                            this.rafId = null;
                        }
                        this.percent = 0;
                        window.__LD.active = Math.max(0, window.__LD.active - 1);
                    },
                }
            }
        </script>
    @endpush
@endonce
