<?php

use Livewire\Component;

new class extends Component {
    /** @var array<int, array{id:int,manufacturer:string,device:string,title:string,lines:string,prefix:string,price:string}> */
    public array $signs = [];

    public function mount(array $signs): void
    {
        // Schlüssel = ID des Eintrags, nicht mehr 0, 1, 2 ...
        $this->signs = collect($signs)->keyBy('id')->all();
    }

    public function remove(int $id): void
    {
        unset($this->signs[$id]);

        if (empty($this->signs)) {
            $this->dispatch('sign-gallery-empty');
        }
    }
};
?>

<div x-data="{
    init() { document.documentElement.classList.add('overflow-hidden'); },
        destroy() { document.documentElement.classList.remove('overflow-hidden'); }
}" x-on:keydown.escape.window="$wire.$parent.set('showSignGallery', false)">

    <style>
        .sign {
            position: relative;
            width: 105mm;
            height: 148mm;
            background: #fff;
            color: #3d3d3d;
            font-family: 'Montserrat', sans-serif;
            overflow: hidden;
            box-sizing: border-box;
        }

        .sign-logo {
            position: absolute;
            top: 7mm;
            right: 7mm;
            width: 35mm;
            height: 20mm;
            object-fit: contain;
            object-position: top right;
        }

        .sign-device {
            position: absolute;
            left: 6.5mm;
            right: 6.5mm;
            top: 30mm;
            font-size: 9mm;
            font-weight: 700;
            line-height: 1.15;
            white-space: pre-line;
        }

        .sign-spec {
            position: absolute;
            left: 6.5mm;
            right: 6.5mm;
            top: 57mm;
        }

        .sign-title {
            font-size: 6.2mm;
            font-weight: 600;
            margin: 0;
        }

        .sign-rule {
            width: 24mm;
            height: .7mm;
            background: #b9d4d5;
            margin: 1mm 0 1.5mm;
        }

        .sign-lines {
            font-size: 6.2mm;
            font-weight: 300;
            line-height: 1.3;
            white-space: pre-line;
            margin: 0;
        }

        .sign-price {
            position: absolute;
            left: 6.5mm;
            right: 6.5mm;
            bottom: 7mm;
            height: 31mm;
            background: #b9d4d5;
            padding: 2.5mm 3mm;
            box-sizing: border-box;
        }

        .sign-prefix {
            font-size: 6.5mm;
            font-weight: 600;
        }

        .sign-amount {
            font-size: 15mm;
            font-weight: 700;
            line-height: 1;
            margin-top: 1mm;
        }

        .print-root {
            display: none;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {

            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                width: 210mm !important;
                height: auto !important;
                overflow: visible !important;
            }

            body>*:not(.print-root) {
                display: none !important;
            }

            .print-root {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .print-sheet {
                width: 210mm;
                height: 297mm;
                display: grid;
                grid-template-columns: 105mm 105mm;
                grid-template-rows: 148mm 148mm;
                gap: 0;
                overflow: hidden;
                break-after: page;
                page-break-after: always;
            }

            .print-sheet:last-child {
                break-after: auto;
                page-break-after: auto;
            }

            .print-sheet .sign {
                width: 105mm;
                height: 148mm;
                margin: 0;
                overflow: hidden;
                box-sizing: border-box;
                break-inside: avoid;
            }
        }
    </style>

    <div class="fixed inset-0 z-60 flex flex-col bg-gray-100 print:hidden">

        <div class="flex items-center justify-between border-b bg-white px-6 py-3 shadow-sm">
            <h2 class="text-lg font-bold">A6-Schilder ({{ count($signs) }})</h2>
            <div class="flex gap-2">
                <button type="button" wire:click="$parent.set('showSignGallery', false)"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100">
                    Schließen
                </button>
                <button type="button" x-on:click="window.print()"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    🖨️ Drucken
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto overscroll-contain p-6">
            <div class="mx-auto grid max-w-6xl gap-6 md:grid-cols-2">
                @foreach ($signs as $id => $sign)
                    <div wire:key="card-{{ $sign['id'] }}" class="flex gap-4 rounded-xl bg-white p-4 shadow">

                        <div class="shrink-0 border border-gray-300" style="zoom: .5">
                            @include('sign-gallery.sign', ['sign' => $sign])
                        </div>

                        <div class="flex min-w-0 flex-1 flex-col gap-2 text-sm">
                            <div class="flex flex-row gap-2">
                                <input type="text" placeholder="Hersteller"
                                    wire:model.live.debounce.300ms="signs.{{ $id }}.manufacturer"
                                    class="w-24 shrink-0 self-start rounded border border-gray-300 px-2 py-1">

                                <textarea rows="2" wrap="off" placeholder="Gerätetitel (Enter = neue Zeile)"
                                    wire:model.live.debounce.300ms="signs.{{ $id }}.device"
                                    class="min-w-0 flex-1 whitespace-pre rounded border border-gray-300 px-2 py-1"></textarea>
                            </div>

                            <input type="text" wire:model.live.debounce.300ms="signs.{{ $id }}.title"
                                class="rounded border border-gray-300 px-2 py-1">

                            <textarea rows="5" wire:model.live.debounce.300ms="signs.{{ $id }}.lines"
                                class="rounded border border-gray-300 px-2 py-1"></textarea>

                            <div class="flex gap-2">
                                <input type="text" wire:model.live.debounce.300ms="signs.{{ $id }}.prefix"
                                    class="w-16 rounded border border-gray-300 px-2 py-1">
                                <input type="text" wire:model.live.debounce.300ms="signs.{{ $id }}.price"
                                    class="min-w-0 flex-1 rounded border border-gray-300 px-2 py-1">
                            </div>

                            <button type="button" wire:click="remove({{ $id }})"
                                class="mt-auto inline-flex items-center gap-1.5 self-start rounded-lg
                                       border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold
                                       text-red-700 transition hover:border-red-300 hover:bg-red-100
                                       focus:outline-none focus:ring-2 focus:ring-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Entfernen
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div class="print-root">
            @foreach (collect($signs)->values()->chunk(4) as $sheet)
                <div class="print-sheet">
                    @foreach ($sheet as $sign)
                        @include('sign-gallery.sign', ['sign' => $sign])
                    @endforeach
                </div>
            @endforeach
        </div>
    </template>
</div>
