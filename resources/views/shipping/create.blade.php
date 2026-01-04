<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QLS Shipping Label Builder</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    @php($control = 'block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/20')
    @php($lines = old('lines', $defaults['lines']))
    @php($nextIndex = is_array($lines) ? count($lines) : 0)

    <div
        class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8"
        x-data="shippingLabelPage({ nextIndex: {{ $nextIndex }}, showErrors: {{ $errors->any() ? 'true' : 'false' }} })">
        <header class="mb-8">
            <h1 class="text-3xl font-semibold tracking-tight">Shipment + Packing Slip</h1>
            <p class="mt-2 text-sm text-slate-600">
                Configure the order, create shipment via QLS, merge label + packing slip into one PDF.
            </p>
        </header>

        @if ($errors->any())
        <section
            id="form-errors"
            class="mb-6 break-words rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800"
            role="alert"
            x-show="showErrors">
            <div class="font-semibold">Fix the highlighted fields.</div>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </section>
        @endif

        <form
            method="POST"
            action="{{ route('shipping-label.store') }}"
            class="space-y-10 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8"
            @submit="setSubmitting()">
            @csrf

            <section aria-labelledby="shipment-details-title">
                <div class="mb-4">
                    <h2 id="shipment-details-title" class="text-lg font-semibold">Shipment details</h2>
                    <p class="mt-1 text-sm text-slate-600">Choose the product combination and provide package details.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-form.input
                        label="Order number"
                        name="order_number"
                        :value="old('order_number', $defaults['order_number'])"
                        required
                        autocomplete="off" />

                    <x-form.input
                        label="Weight (grams)"
                        name="weight"
                        type="number"
                        :value="old('weight', $defaults['weight'])"
                        required
                        min="1"
                        step="1"
                        inputmode="numeric" />

                    <div class="md:col-span-2">
                        <x-form.select
                            label="Product combination"
                            name="product_combination_id"
                            required>
                            <option value="">Select a shipping product</option>
                            @foreach($products as $combination)
                            <option value="{{ $combination->id }}" @selected(old('product_combination_id', $defaults['product_combination_id'])==$combination->id)>
                                {{ $combination->name }}
                            </option>
                            @endforeach
                        </x-form.select>
                    </div>
                </div>
            </section>

            <section aria-labelledby="addresses-title">
                <div class="mb-4">
                    <h2 id="addresses-title" class="text-lg font-semibold">Addresses</h2>
                    <p class="mt-1 text-sm text-slate-600">Sender and receiver details used for shipment creation and packing slip.</p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    @foreach (['billing' => 'Sender (billing)', 'delivery' => 'Receiver (delivery)'] as $key => $legend)
                    @php($data = old($key, $defaults[$key]))

                    <fieldset class="rounded-lg border border-slate-200 p-4">
                        <legend class="px-2 text-sm font-semibold text-slate-900">{{ $legend }}</legend>

                        <div class="mt-3 space-y-4">
                            <x-form.input
                                label="Company"
                                name="{{ $key }}[companyname]"
                                :value="$data['companyname'] ?? ''"
                                autocomplete="organization" />

                            <x-form.input
                                label="Name"
                                name="{{ $key }}[name]"
                                :value="$data['name'] ?? ''"
                                required
                                autocomplete="name" />

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <x-form.input
                                        label="Street"
                                        name="{{ $key }}[street]"
                                        :value="$data['street'] ?? ''"
                                        required
                                        autocomplete="address-line1" />
                                </div>

                                <x-form.input
                                    label="No."
                                    name="{{ $key }}[housenumber]"
                                    :value="$data['housenumber'] ?? ''"
                                    required />
                            </div>

                            <x-form.input
                                label="Address line 2"
                                name="{{ $key }}[address2]"
                                :value="$data['address2'] ?? ''" />

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <x-form.input
                                    label="Postal code"
                                    name="{{ $key }}[postalcode]"
                                    :value="$data['postalcode'] ?? ''"
                                    required
                                    autocomplete="postal-code" />

                                <div class="sm:col-span-2">
                                    <x-form.input
                                        label="City"
                                        name="{{ $key }}[city]"
                                        :value="$data['city'] ?? ''"
                                        required
                                        autocomplete="address-level2" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <x-form.input
                                    label="Country"
                                    name="{{ $key }}[country]"
                                    :value="$data['country'] ?? ''"
                                    required
                                    autocomplete="country" />

                                <div class="sm:col-span-2">
                                    <x-form.input
                                        label="Email"
                                        name="{{ $key }}[email]"
                                        type="email"
                                        :value="$data['email'] ?? ''"
                                        autocomplete="email" />
                                </div>
                            </div>

                            <x-form.input
                                label="Phone"
                                name="{{ $key }}[phone]"
                                type="tel"
                                :value="$data['phone'] ?? ''"
                                autocomplete="tel" />
                        </div>
                    </fieldset>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="order-lines-title">
                <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 id="order-lines-title" class="text-lg font-semibold">Order lines</h2>
                        <p class="mt-1 text-sm text-slate-600">At least one line is required.</p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        @click="addLine()">
                        Add line
                    </button>
                </div>

                <div class="hidden gap-3 text-xs font-semibold uppercase tracking-wide text-slate-500 sm:grid sm:grid-cols-12">
                    <div class="sm:col-span-4">Name</div>
                    <div class="sm:col-span-3">SKU</div>
                    <div class="sm:col-span-2">Qty</div>
                    <div class="sm:col-span-2">EAN</div>
                    <div class="sm:col-span-1"></div>
                </div>

                <div class="mt-3 space-y-3" x-ref="lines" @click="onLinesClick($event)">
                    @foreach($lines as $index => $line)
                    <div class="order-line grid grid-cols-1 gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 sm:grid-cols-12 sm:items-end">
                        <div class="sm:col-span-4">
                            <x-form.input
                                name="lines[{{ $index }}][name]"
                                :value="$line['name'] ?? ''"
                                required
                                placeholder="Name"
                                aria-label="Name" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-form.input
                                name="lines[{{ $index }}][sku]"
                                :value="$line['sku'] ?? ''"
                                required
                                placeholder="SKU"
                                aria-label="SKU"
                                autocapitalize="off"
                                spellcheck="false" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-form.input
                                name="lines[{{ $index }}][quantity]"
                                type="number"
                                :value="$line['quantity'] ?? 1"
                                required
                                min="1"
                                step="1"
                                inputmode="numeric"
                                placeholder="Qty"
                                aria-label="Quantity" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-form.input
                                name="lines[{{ $index }}][ean]"
                                :value="$line['ean'] ?? ''"
                                placeholder="EAN"
                                aria-label="EAN"
                                autocapitalize="off"
                                spellcheck="false" />
                        </div>

                        <div class="sm:col-span-1 sm:flex sm:justify-end">
                            <button
                                type="button"
                                class="inline-flex w-full items-center justify-center rounded-md px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 sm:w-auto"
                                data-remove-line
                                aria-label="Remove line">
                                X
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <template x-ref="lineTemplate">
                    <div class="order-line grid grid-cols-1 gap-3 rounded-md border border-slate-200 bg-slate-50 p-4 sm:grid-cols-12 sm:items-end">
                        <div class="sm:col-span-4">
                            <input required placeholder="Name" aria-label="Name" class="{{ $control }}" data-field="name">
                        </div>

                        <div class="sm:col-span-3">
                            <input required placeholder="SKU" aria-label="SKU" autocapitalize="off" spellcheck="false" class="{{ $control }}" data-field="sku">
                        </div>

                        <div class="sm:col-span-2">
                            <input type="number" required min="1" step="1" inputmode="numeric" value="1" placeholder="Qty" aria-label="Quantity" class="{{ $control }}" data-field="quantity">
                        </div>

                        <div class="sm:col-span-2">
                            <input placeholder="EAN" aria-label="EAN" autocapitalize="off" spellcheck="false" class="{{ $control }}" data-field="ean">
                        </div>

                        <div class="sm:col-span-1 sm:flex sm:justify-end">
                            <button
                                type="button"
                                class="inline-flex w-full items-center justify-center rounded-md px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 sm:w-auto"
                                data-remove-line
                                aria-label="Remove line">
                                ✕
                            </button>
                        </div>
                    </div>
                </template>
            </section>

            <div class="flex justify-end">
                <button
                    type="submit"
                    x-ref="submit"
                    :disabled="submitting"
                    :aria-busy="submitting ? 'true' : 'false'"
                    :class="submitting ? 'opacity-70 cursor-wait' : 'cursor-pointer'"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <span x-text="submitting ? 'Generating PDF...' : submitLabel"></span>
                </button>
            </div>
        </form>
    </div>
</body>

</html>