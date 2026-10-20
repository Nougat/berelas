<?php

use App\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    // Übersicht
    public string $search = '';
    public string $statusFilter = 'active';

    private const STATUSES = [
        'open' => 'Angenommen',
        'in_progress' => 'In Bearbeitung',
        'waiting' => 'Warten',
        'completed' => 'Abgeschlossen',
    ];

    // Modal
    public bool $showEditor = false;
    public ?int $editingId = null;
    public bool $showPassword = false;

    // Formularfelder
    public string $status = 'open';
    public string $customerName = '';
    public string $customerPhone = '';
    public string $device = '';
    public string $receivedAt = '';
    public string $receivedBy = '';
    public string $assignedTo = '';
    public string $purchaseDate = '';
    public string $description = '';
    public string $condition = '';
    public string $password = '';
    public bool $powerSupply = false;
    public string $damageFixed = '';
    public bool $customerNotified = false;
    public string $cost = '';
    public string $internalNotes = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function orders()
    {
        return ServiceOrder::query()
            ->when($this->statusFilter === 'active', fn(Builder $query) => $query->where('status', '!=', 'completed'))
            ->when(!in_array($this->statusFilter, ['active', 'all'], true), fn(Builder $query) => $query->where('status', $this->statusFilter))
            ->when(trim($this->search) !== '', function (Builder $query) {
                $search = trim($this->search);

                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('device', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $query->orWhere('id', (int) $search);
                    }
                });
            })
            ->latest('id')
            ->paginate(20);
    }

    public function statusOptions(): array
    {
        return self::STATUSES;
    }

    public function statusLabel(?string $status): string
    {
        return self::STATUSES[$status ?? ''] ?? 'Unbekannt';
    }

    public function create(): void
    {
        $this->resetEditor();
        $this->resetValidation();

        $this->editingId = null;
        $this->receivedAt = now()->format('Y-m-d');
        $this->showEditor = true;
    }

    public function edit(int $id): void
    {
        $order = ServiceOrder::findOrFail($id);

        $this->resetEditor();
        $this->resetValidation();

        $this->editingId = $order->id;
        $this->status = $order->status ?? 'open';
        $this->customerName = $order->customer_name;
        $this->customerPhone = $order->customer_phone ?? '';
        $this->device = $order->device ?? '';
        $this->receivedAt = $order->received_at?->format('Y-m-d') ?? '';
        $this->receivedBy = $order->received_by ?? '';
        $this->assignedTo = $order->assigned_to ?? '';
        $this->purchaseDate = $order->purchase_date?->format('Y-m-d') ?? '';
        $this->description = $order->description ?? '';
        $this->condition = $order->condition ?? '';
        $this->internalNotes = $order->internal_notes ?? '';

        $this->password = $order->password ?? '';
        $this->showPassword = false;

        $this->powerSupply = (bool) $order->power_supply;
        $this->damageFixed = match ($order->damage_fixed) {
            true => 'yes',
            false => 'no',
            default => '',
        };
        $this->customerNotified = (bool) $order->customer_notified;
        $rawCost = $order->getRawOriginal('cost');

        $this->cost = $rawCost !== null && $rawCost !== '' ? (string) $rawCost : '';

        $this->showEditor = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'customerName' => ['required', 'string', 'max:255'],
            'customerPhone' => ['nullable', 'string', 'max:50'],
            'device' => ['nullable', 'string', 'max:255'],
            'receivedAt' => ['required', 'date'],
            'receivedBy' => ['nullable', 'string', 'max:255'],
            'assignedTo' => ['nullable', 'string', 'max:255'],
            'purchaseDate' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'condition' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'max:255'],
            'powerSupply' => ['boolean'],
            'damageFixed' => ['nullable', Rule::in(['yes', 'no'])],
            'customerNotified' => ['boolean'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'internalNotes' => ['nullable', 'string', 'max:10000'],
        ]);

        $data = [
            'status' => $validated['status'],
            'customer_name' => $validated['customerName'],
            'customer_phone' => $validated['customerPhone'] ?: null,
            'device' => $validated['device'] ?: null,
            'received_at' => $validated['receivedAt'],
            'received_by' => $validated['receivedBy'] ?: null,
            'assigned_to' => $validated['assignedTo'] ?: null,
            'purchase_date' => $validated['purchaseDate'] ?: null,
            'description' => $validated['description'] ?: null,
            'condition' => $validated['condition'] ?: null,
            'power_supply' => $validated['powerSupply'],
            'damage_fixed' => match ($validated['damageFixed'] ?? null) {
                'yes' => true,
                'no' => false,
                default => null,
            },
            'customer_notified' => $validated['customerNotified'],
            'cost' => filled($validated['cost'] ?? null) ? $validated['cost'] : null,
            'internal_notes' => $validated['internalNotes'] ?: null,
        ];

        if ($this->editingId !== null) {
            $order = ServiceOrder::findOrFail($this->editingId);

            $wasCompleted = $order->status === 'completed';
            $willBeCompleted = $validated['status'] === 'completed';

            $data['completed_at'] = $willBeCompleted ? ($wasCompleted ? $order->completed_at ?? now() : now()) : null;

            $order->fill($data);

            // Ein leeres Passwortfeld lässt das bisherige Passwort bestehen.
            if (filled($validated['password'] ?? null)) {
                $order->password = $validated['password'];
            }

            $order->save();

            session()->flash('message', 'Serviceauftrag wurde aktualisiert.');
        } else {
            $data['completed_at'] = $validated['status'] === 'completed' ? now() : null;

            $data['password'] = filled($validated['password'] ?? null) ? $validated['password'] : null;

            ServiceOrder::create($data);

            session()->flash('message', 'Serviceauftrag wurde angelegt.');
        }

        $this->closeEditor();
        unset($this->orders);
    }

    public function complete(int $id): void
    {
        $order = ServiceOrder::findOrFail($id);

        if ($order->status !== 'completed') {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        session()->flash('message', 'Serviceauftrag wurde abgeschlossen.');

        unset($this->orders);
    }

    public function reopen(int $id): void
    {
        $order = ServiceOrder::findOrFail($id);

        $order->update([
            'status' => 'open',
            'completed_at' => null,
        ]);

        session()->flash('message', 'Serviceauftrag wurde wieder geöffnet.');

        unset($this->orders);
    }

    public function delete(int $id): void
    {
        $order = ServiceOrder::findOrFail($id);

        $order->delete();

        $this->closeEditor();
        unset($this->orders);

        session()->flash('message', "Serviceauftrag #{$id} wurde gelöscht.");
    }

    public function closeEditor(): void
    {
        $this->showEditor = false;

        $this->resetEditor();
        $this->resetValidation();
    }

    private function resetEditor(): void
    {
        $this->reset(['editingId', 'status', 'customerName', 'customerPhone', 'device', 'receivedAt', 'receivedBy', 'assignedTo', 'purchaseDate', 'description', 'condition', 'password', 'showPassword', 'powerSupply', 'damageFixed', 'customerNotified', 'cost', 'internalNotes']);
        $this->powerSupply = false;
        $this->customerNotified = false;
        $this->damageFixed = '';
    }
};
?>

<div x-data class="min-h-screen bg-gray-50">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

        {{-- Seitenkopf --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">
                    Berelas IT
                </p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-gray-900">
                    Serviceaufträge
                </h1>
                <p class="mt-2 text-sm text-gray-500">
                    Aufträge verwalten und Reparaturstatus im Blick behalten.
                </p>
            </div>

            <button x-ref="newOrderButton" type="button" wire:click="create"
                class="inline-flex items-center justify-center gap-2 rounded-lg
                       bg-amber-500 px-5 py-3 font-semibold text-white shadow-sm
                       transition hover:bg-amber-600 focus:outline-none
                       focus:ring-4 focus:ring-amber-200">
                <span class="text-xl leading-none">+</span>
                Neuer Auftrag
            </button>
        </div>

        {{-- Rückmeldungen --}}
        @if (session()->has('message'))
            <div role="status"
                class="rounded-lg border border-green-200 bg-green-50 px-4 py-3
                       text-sm text-green-800">
                {{ session('message') }}
            </div>
        @endif

        {{-- Suche und Filter --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="mb-3">
                <h2 class="font-semibold text-gray-900">Aufträge finden</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Suche nach Auftragsnummer, Kunde oder Gerät.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="flex-1">
                    <label for="order-search" class="sr-only">
                        Aufträge durchsuchen
                    </label>

                    <input id="order-search" type="search" wire:model.live.debounce.300ms="search"
                        placeholder="Suchbegriff eingeben …"
                        class="w-full rounded-lg border-2 border-gray-200 bg-gray-50
                               px-4 py-3 text-sm text-gray-900 shadow-sm
                               placeholder:text-gray-400 focus:border-amber-500
                               focus:bg-white focus:outline-none focus:ring-4
                               focus:ring-amber-100">
                </div>

                <div class="sm:w-60">
                    <label for="status-filter" class="sr-only">
                        Status filtern
                    </label>

                    <select id="status-filter" wire:model.live="statusFilter"
                        class="w-full rounded-lg border-2 border-gray-200 bg-gray-50
           px-4 py-3 text-sm text-gray-900 shadow-sm
           focus:border-amber-500 focus:bg-white focus:outline-none
           focus:ring-4 focus:ring-amber-100">
                        <option value="active">Alle offenen Aufträge</option>

                        @foreach ($this->statusOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach

                        <option value="all">Alle Aufträge</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Tabelle --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="border-b border-gray-200 bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Auftrag
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Kunde / Gerät
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Eingang
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Status
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                Aktionen
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($this->orders as $order)
                            <tr wire:key="service-order-{{ $order->id }}" wire:click="edit({{ $order->id }})"
                                class="cursor-pointer transition-colors hover:bg-amber-50"
                                title="Auftrag #{{ $order->id }} bearbeiten">
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-gray-700">
                                    #{{ $order->id }}
                                </td>

                                <td class="px-4 py-4">
                                    <div class="font-medium text-gray-900">
                                        {{ $order->customer_name }}
                                    </div>

                                    <div class="mt-0.5 text-sm text-gray-500">
                                        {{ $order->device ?: 'Kein Gerät angegeben' }}
                                    </div>

                                    @if ($order->customer_phone)
                                        <div class="mt-0.5 text-xs text-gray-400">
                                            {{ $order->customer_phone }}
                                        </div>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600">
                                    {{ $order->received_at?->format('d.m.Y') ?? '–' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4">
                                    @php
                                        $statusClasses = match ($order->status) {
                                            'completed' => 'bg-green-100 text-green-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'waiting' => 'bg-cyan-100 text-cyan-800',
                                            default => 'bg-amber-100 text-amber-800',
                                        };
                                    @endphp

                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                                        {{ $this->statusLabel($order->status) }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" wire:click.stop="edit({{ $order->id }})"
                                            class="text-sm font-semibold text-gray-700
                                                   hover:text-amber-700">
                                            Bearbeiten
                                        </button>

                                        @if ($order->status === 'completed')
                                            <button type="button" wire:click.stop="reopen({{ $order->id }})"
                                                wire:confirm="Möchtest du diesen Auftrag wieder öffnen?"
                                                class="text-sm font-semibold text-amber-700
                                                       hover:text-amber-800">
                                                Wieder öffnen
                                            </button>
                                        @else
                                            <button type="button" wire:click.stop="complete({{ $order->id }})"
                                                wire:confirm="Möchtest du diesen Auftrag abschließen?"
                                                class="text-sm font-semibold text-green-700
                                                       hover:text-green-800">
                                                Abschließen
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <p class="font-medium text-gray-700">
                                        Keine passenden Aufträge gefunden
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Ändere deine Suche oder lege einen neuen Auftrag an.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3">
                {{ $this->orders->links() }}
            </div>
        </div>
    </div>

    {{-- Gemeinsames Erstellen-/Bearbeiten-Modal --}}
    @if ($showEditor)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto
                   bg-gray-950/60 p-3 backdrop-blur-sm sm:items-center sm:p-6"
            x-on:keydown.escape.window="$wire.closeEditor().then(() => $nextTick(() => $refs.newOrderButton.focus()))"
            x-on:click.self="$wire.closeEditor().then(() => $nextTick(() => $refs.newOrderButton.focus()))">
            <div class="my-3 w-full max-w-4xl overflow-hidden rounded-2xl
                       border border-gray-200 bg-white shadow-2xl"
                x-on:click.stop>
                {{-- Modal-Kopf --}}
                <div class="flex items-center justify-between border-b border-gray-200 bg-white px-5 py-4 sm:px-7">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-amber-600">
                            Berelas IT
                        </p>

                        <h2 class="mt-1 text-xl font-bold text-gray-900">
                            {{ $editingId ? 'Serviceauftrag bearbeiten' : 'Neuen Serviceauftrag anlegen' }}
                        </h2>

                        @if ($editingId)
                            <p class="mt-1 text-sm text-gray-500">
                                Auftragsnummer #{{ $editingId }}
                            </p>
                        @endif
                    </div>

                    <button type="button" aria-label="Modal schließen" wire:click="closeEditor"
                        x-on:click="$nextTick(() => $refs.newOrderButton.focus())"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100
                               hover:text-gray-700 focus:outline-none focus:ring-4
                               focus:ring-gray-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" autocomplete="off" aria-autocomplete="none">
                    <div class="max-h-[min(70vh,720px)] space-y-7 overflow-y-auto bg-white px-5 py-6 sm:px-7">

                        {{-- Kunde und Gerät --}}
                        <section>
                            <h3
                                class="mb-4 border-b border-gray-100 pb-2 text-sm font-bold uppercase tracking-wide text-gray-700">
                                01 · Kunde und Gerät
                            </h3>

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="customerName"
                                        class="mb-1.5 block text-sm font-semibold text-gray-800">
                                        Kundenname <span class="text-amber-600">*</span>
                                    </label>

                                    <input id="customerName" type="text" autocomplete="off"
                                        aria-autocomplete="none" wire:model="customerName" required
                                        class="w-full rounded-lg border-2 border-gray-300 bg-gray-50
                                               px-3 py-2.5 text-sm text-gray-900 shadow-sm
                                               placeholder:text-gray-400 focus:border-amber-500
                                               focus:bg-white focus:outline-none focus:ring-4
                                               focus:ring-amber-100">
                                    @error('customerName')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="customerPhone"
                                        class="mb-1.5 block text-sm font-semibold text-gray-800">
                                        Telefonnummer
                                    </label>

                                    <input id="customerPhone" type="tel" wire:model="customerPhone"
                                        autocomplete="off"
                                        class="w-full rounded-lg border-2 border-gray-300 bg-gray-50
                                               px-3 py-2.5 text-sm text-gray-900 shadow-sm
                                               placeholder:text-gray-400 focus:border-amber-500
                                               focus:bg-white focus:outline-none focus:ring-4
                                               focus:ring-amber-100">
                                    @error('customerPhone')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="device" class="mb-1.5 block text-sm font-semibold text-gray-800">
                                        Gerät / Modell
                                    </label>

                                    <input id="device" type="text" wire:model="device" autocomplete="off"
                                        class="w-full rounded-lg border-2 border-gray-300 bg-gray-50
                                               px-3 py-2.5 text-sm text-gray-900 shadow-sm
                                               focus:border-amber-500 focus:bg-white focus:outline-none
                                               focus:ring-4 focus:ring-amber-100">
                                    @error('device')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="purchaseDate"
                                        class="mb-1.5 block text-sm font-semibold text-gray-800">
                                        Kaufdatum
                                    </label>

                                    <input id="purchaseDate" type="date" wire:model="purchaseDate"
                                        autocomplete="off"
                                        class="w-full rounded-lg border-2 border-gray-300 bg-gray-50
                                               px-3 py-2.5 text-sm text-gray-900 shadow-sm
                                               focus:border-amber-500 focus:bg-white focus:outline-none
                                               focus:ring-4 focus:ring-amber-100">
                                    @error('purchaseDate')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        {{-- Annahme --}}
                        <section>
                            <h3
                                class="mb-4 border-b border-gray-100 pb-2 text-sm font-bold uppercase tracking-wide text-gray-700">
                                02 · Auftragsannahme
                            </h3>

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="receivedAt" class="mb-1.5 block text-sm font-semibold text-gray-800">
                                        Eingangsdatum <span class="text-amber-600">*</span>
                                    </label>

                                    <input id="receivedAt" type="date" wire:model="receivedAt" required
                                        autocomplete="off"
                                        class="w-full rounded-lg border-2 border-gray-300 bg-gray-50
                                               px-3 py-2.5 text-sm text-gray-900 shadow-sm
                                               focus:border-amber-500 focus:bg-white focus:outline-none
                                               focus:ring-4 focus:ring-amber-100">
                                    @error('receivedAt')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="receivedBy" class="mb-1.5 block text-sm font-semibold text-gray-800">
                                        Angenommen von
                                    </label>

                                    <input id="receivedBy" type="text" wire:model="receivedBy" autocomplete="off"
                                        class="w-full rounded-lg border-2 border-gray-300 bg-gray-50
                                               px-3 py-2.5 text-sm text-gray-900 shadow-sm
                                               focus:border-amber-500 focus:bg-white focus:outline-none
                                               focus:ring-4 focus:ring-amber-100">
                                    @error('receivedBy')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="condition" class="mb-1.5 block text-sm font-semibold text-gray-800">
                                        Zustand bei Annahme
                                    </label>

                                    <textarea id="condition" wire:model="condition" rows="2" autocomplete="off"
                                        class="w-full resize-y rounded-lg border-2 border-gray-300 bg-gray-50
                                               px-3 py-2.5 text-sm text-gray-900 shadow-sm
                                               focus:border-amber-500 focus:bg-white focus:outline-none
                                               focus:ring-4 focus:ring-amber-100"></textarea>
                                    @error('condition')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-3 rounded-lg border border-gray-200 bg-gray-50 p-4 sm:col-span-2">
                                    <label
                                        class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-800">
                                        <input type="checkbox" wire:model="powerSupply"
                                            class="h-4 w-4 rounded border-gray-400 text-amber-500
                                                   focus:ring-amber-500">
                                        Netzteil wurde mit abgegeben
                                    </label>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="description" class="mb-1.5 block text-sm font-semibold text-gray-800">
                                        Fehlerbeschreibung / Kundenanliegen
                                    </label>

                                    <textarea id="description" wire:model="description" rows="10" autocomplete="off"
                                        class="w-full resize-y rounded-lg border-2 border-gray-300 bg-gray-50
                                               px-3 py-2.5 text-sm text-gray-900 shadow-sm
                                               focus:border-amber-500 focus:bg-white focus:outline-none
                                               focus:ring-4 focus:ring-amber-100"></textarea>
                                    @error('description')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        {{-- Interne Angaben --}}
                        <section>
                            <h3
                                class="mb-4 border-b border-gray-100 pb-2 text-sm font-bold uppercase tracking-wide text-gray-700">
                                03 · Interne Angaben
                            </h3>

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                                <div>
                                    <label for="status" class="mb-1.5 block text-sm font-semibold text-gray-800">
                                        Auftragsstatus
                                    </label>

                                    <select id="status" wire:model="status"
                                        class="w-full rounded-lg border-2 border-gray-300 bg-gray-50
                                            px-3 py-2.5 text-sm text-gray-900 shadow-sm
                                            focus:border-amber-500 focus:bg-white focus:outline-none
                                            focus:ring-4 focus:ring-amber-100">
                                        @foreach ($this->statusOptions() as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>

                                    @error('status')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="password" class="mb-1.5 block text-sm font-semibold text-gray-700">
                                        Gerätepasswort
                                    </label>

                                    <div class="flex gap-2">
                                        <input id="password" type="{{ $showPassword ? 'text' : 'password' }}"
                                            wire:model="password" autocomplete="new-passwort"
                                            class="min-w-0 flex-1 rounded-xl border-2 border-gray-300
                   bg-gray-50 px-4 py-2.5 text-sm
                   focus:border-amber-400 focus:outline-none
                   focus:ring-2 focus:ring-amber-100">

                                        <button type="button" wire:click="$toggle('showPassword')"
                                            class="shrink-0 rounded-xl border-2 border-gray-300
                   px-3 py-2 text-sm font-medium text-gray-700
                   transition hover:bg-gray-100"
                                            aria-label="{{ $showPassword ? 'Passwort verbergen' : 'Passwort anzeigen' }}">
                                            @if ($showPassword)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 3l18 18" />
                                                    <path d="M10.6 10.6a2 2 0 002.8 2.8" />
                                                    <path
                                                        d="M9.9 5.2a10.8 10.8 0 00-5.8 3.1A12.8 12.8 0 002 12s3.6 7 10 7a10.8 10.8 0 004.1-.8" />
                                                    <path
                                                        d="M6.6 6.6A10.8 10.8 0 0112 5c6.4 0 10 7 10 7a12.8 12.8 0 01-3.1 4.1" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            @endif
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label for="cost" class="mb-1.5 block text-sm font-semibold text-gray-800">
                                        Kosten (€)
                                    </label>

                                    <input id="cost" type="number" min="0" step="0.01"
                                        wire:model="cost"
                                        class="w-full rounded-lg border-2 border-gray-300 bg-gray-50
                                               px-3 py-2.5 text-sm text-gray-900 shadow-sm
                                               focus:border-amber-500 focus:bg-white focus:outline-none
                                               focus:ring-4 focus:ring-amber-100">
                                    @error('cost')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>


                                <div>
                                    <label for="assignedTo" class="mb-1.5 block text-sm font-semibold text-gray-700">
                                        Bearbeitender Mitarbeiter
                                    </label>

                                    <input id="assignedTo" type="text" wire:model="assignedTo"
                                        placeholder="Name des Mitarbeiters"
                                        class="w-full rounded-xl border-2 border-gray-300
                                    bg-gray-50 px-4 py-2.5 text-sm
                                    focus:border-amber-400 focus:outline-none
                                    focus:ring-2 focus:ring-amber-100">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="internalNotes"
                                        class="mb-1.5 block text-sm font-semibold text-gray-800">
                                        Interne Notizen / Anweisungen
                                    </label>

                                    <textarea id="internalNotes" wire:model="internalNotes" rows="4" maxlength="10000"
                                        placeholder="Interne Hinweise, Anweisungen oder noch offene Arbeitsschritte ..."
                                        class="w-full resize-y rounded-lg border-2 border-gray-300
               bg-gray-50 px-3 py-2.5 text-sm text-gray-900 shadow-sm
               placeholder:text-gray-400
               focus:border-amber-500 focus:bg-white focus:outline-none
               focus:ring-4 focus:ring-amber-100"></textarea>

                                    @error('internalNotes')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="space-y-3 rounded-lg border border-gray-200 bg-gray-50 p-4 sm:col-span-2">
                                    <label
                                        class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-800">
                                        <input type="checkbox" wire:model="customerNotified"
                                            class="h-4 w-4 rounded border-gray-400 text-amber-500
                                                   focus:ring-amber-500">
                                        Kunde wurde benachrichtigt
                                    </label>
                                </div>

                                <fieldset class="sm:col-span-2">
                                    <legend class="mb-2 text-sm font-semibold text-gray-800">
                                        Fehler behoben?
                                    </legend>

                                    <div class="flex flex-wrap gap-3">
                                        @foreach ([
        'yes' => 'Ja',
        'no' => 'Nein',
        '' => 'Noch offen',
    ] as $value => $label)
                                            <label
                                                class="flex cursor-pointer items-center gap-2 rounded-lg
                                                          border-2 border-gray-200 bg-gray-50 px-4 py-2.5
                                                          text-sm font-medium text-gray-700 transition
                                                          hover:border-amber-300 has-[:checked]:border-amber-500
                                                          has-[:checked]:bg-amber-50 has-[:checked]:text-amber-900">
                                                <input type="radio" wire:model="damageFixed"
                                                    value="{{ $value }}"
                                                    class="h-4 w-4 border-gray-400 text-amber-500
                                                           focus:ring-amber-500">
                                                {{ $label }}
                                            </label>
                                        @endforeach
                                    </div>

                                    @error('damageFixed')
                                        <p class="mt-1.5 text-sm font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </fieldset>
                            </div>
                        </section>
                    </div>

                    {{-- Modal-Fuß --}}
                    <div
                        class="flex flex-col-reverse gap-3 border-t border-gray-200
                                bg-gray-50 px-5 py-4 sm:flex-row sm:justify-between sm:px-7">
                        <p class="hidden items-center text-xs text-gray-500 sm:flex">
                            Änderungen werden erst nach dem Speichern übernommen.
                        </p>

                        <div
                            class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                            {{-- Löschen nur bei bestehenden Aufträgen --}}
                            @if ($editingId !== null)
                                <button type="button" wire:click="delete({{ $editingId }})"
                                    wire:confirm="Möchtest du diesen Serviceauftrag wirklich endgültig löschen?"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl
                   border border-red-200 px-4 py-2.5 text-sm font-semibold
                   text-red-700 transition hover:border-red-300
                   hover:bg-red-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3" />
                                    </svg>
                                    Auftrag löschen
                                </button>
                            @else
                                <div></div>
                            @endif

                            <div class="flex justify-end gap-3">
                                <button type="button" wire:click="closeEditor"
                                    class="rounded-xl border border-gray-300 px-4 py-2.5
                   text-sm font-semibold text-gray-700
                   transition hover:bg-gray-100">
                                    Abbrechen
                                </button>

                                <button type="submit" wire:loading.attr="disabled"
                                    class="rounded-xl bg-amber-500 px-5 py-2.5
                   text-sm font-semibold text-gray-950
                   transition hover:bg-amber-400
                   disabled:cursor-not-allowed disabled:opacity-60">
                                    <span wire:loading.remove wire:target="save">
                                        Speichern
                                    </span>
                                    <span wire:loading wire:target="save">
                                        Speichert …
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
