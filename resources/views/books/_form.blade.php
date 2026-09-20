@props(['book', 'categories', 'isEdit' => false])

<div class="space-y-6">
    {{-- Validation Errors Summary (if any) --}}
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50/50 p-4 dark:border-red-900/50 dark:bg-red-950/20">
            <div class="flex items-start gap-3">
                <flux:icon name="exclamation-triangle" class="mt-0.5 text-red-600 dark:text-red-400" />
                <div>
                    <h4 class="text-sm font-semibold text-red-800 dark:text-red-300">
                        {{ __('Please correct the errors below:') }}
                    </h4>
                    <ul class="mt-1.5 list-inside list-disc text-xs text-red-700 dark:text-red-400 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {{-- Book Title --}}
        <div class="md:col-span-2">
            <flux:field>
                <flux:label required>{{ __('Book Title') }}</flux:label>
                <flux:input
                    name="title"
                    value="{{ old('title', $book->title ?? '') }}"
                    placeholder="{{ __('e.g. Attack on Titan') }}"
                    required
                />
                <flux:error name="title" />
            </flux:field>
        </div>

        {{-- Author --}}
        <div>
            <flux:field>
                <flux:label>{{ __('Author / Creator') }}</flux:label>
                <flux:input
                    name="author"
                    value="{{ old('author', $book->author ?? '') }}"
                    placeholder="{{ __('e.g. Hajime Isayama') }}"
                />
                <flux:error name="author" />
            </flux:field>
        </div>

        {{-- Book Code --}}
        <div>
            <flux:field>
                <flux:label required>{{ __('Book Code / Catalog ID') }}</flux:label>
                <flux:input
                    name="book_code"
                    value="{{ old('book_code', $book->book_code ?? '') }}"
                    placeholder="{{ __('e.g. BK026') }}"
                    required
                />
                <flux:description>{{ __('Unique identifier used in library cataloging.') }}</flux:description>
                <flux:error name="book_code" />
            </flux:field>
        </div>

        {{-- Published Year --}}
        <div>
            <flux:field>
                <flux:label>{{ __('Published Year') }}</flux:label>
                <flux:input
                    type="number"
                    name="published_year"
                    value="{{ old('published_year', $book->published_year ?? '') }}"
                    placeholder="{{ __('e.g. 2009') }}"
                    min="1000"
                    max="{{ date('Y') + 5 }}"
                />
                <flux:error name="published_year" />
            </flux:field>
        </div>

        {{-- Status --}}
        <div>
            <flux:field>
                <flux:label required>{{ __('Availability Status') }}</flux:label>
                <flux:select name="status">
                    <option value="available" @selected(old('status', $book->status ?? 'available') === 'available')>
                        {{ __('Available for Borrowing') }}
                    </option>
                    <option value="unavailable" @selected(old('status', $book->status ?? '') === 'unavailable')>
                        {{ __('Currently Unavailable / On Loan') }}
                    </option>
                </flux:select>
                <flux:error name="status" />
            </flux:field>
        </div>

        {{-- Cover Image Upload --}}
        <div class="md:col-span-2">
            <flux:field>
                <flux:label>{{ __('Book Cover Image') }}</flux:label>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    @if ($isEdit && $book->cover)
                        <div class="relative shrink-0 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800">
                            <img
                                src="{{ $book->cover_url }}"
                                alt="{{ $book->title }}"
                                class="h-32 w-24 object-cover"
                            >
                            <span class="absolute bottom-0 inset-x-0 bg-black/60 py-0.5 text-center text-[10px] text-white backdrop-blur-xs">
                                {{ __('Current Cover') }}
                            </span>
                        </div>
                    @endif

                    <div class="flex-1">
                        <input
                            type="file"
                            name="cover"
                            id="cover"
                            accept="image/png,image/jpeg,image/jpg,image/webp,image/gif"
                            class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-200 dark:hover:file:bg-zinc-700 cursor-pointer"
                        />
                        <flux:description class="mt-1">
                            {{ __('Supported formats: PNG, JPG, WebP, GIF. Max file size: 2MB.') }}
                            @if ($isEdit && $book->cover)
                                {{ __('Upload a new image to replace the current one.') }}
                            @endif
                        </flux:description>
                        <flux:error name="cover" />
                    </div>
                </div>
            </flux:field>
        </div>

        {{-- Categories / Genres (Checkboxes Grid) --}}
        <div class="md:col-span-2">
            <flux:field>
                <flux:label>{{ __('Categories & Genres') }}</flux:label>
                <flux:description class="mb-3">
                    {{ __('Select all genres that describe this manga or light novel.') }}
                </flux:description>

                @php
                    $selectedCategories = collect(old('categories', $book->categories->pluck('id')->all() ?? []))->map(fn($val) => (int)$val)->all();
                @endphp

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700/80 bg-zinc-50/50 dark:bg-zinc-900/50 max-h-60 overflow-y-auto">
                    @foreach ($categories as $category)
                        <label class="flex items-center gap-2 text-xs text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="categories[]"
                                value="{{ $category->id }}"
                                @checked(in_array($category->id, $selectedCategories))
                                class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-950 dark:border-zinc-700 dark:bg-zinc-800 dark:checked:bg-zinc-100 dark:focus:ring-zinc-300 cursor-pointer"
                            >
                            <span>{{ $category->name }}</span>
                        </label>
                    @endforeach
                </div>
                <flux:error name="categories" />
            </flux:field>
        </div>

        {{-- Synopsis --}}
        <div class="md:col-span-2">
            <flux:field>
                <flux:label>{{ __('Synopsis / Description') }}</flux:label>
                <flux:textarea
                    name="synopsis"
                    rows="5"
                    placeholder="{{ __('Enter the plot overview, background summary, or story premise...') }}"
                >{{ old('synopsis', $book->synopsis ?? '') }}</flux:textarea>
                <flux:error name="synopsis" />
            </flux:field>
        </div>
    </div>

    {{-- Form Action Buttons --}}
    <div class="flex items-center justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700/80">
        <flux:button :href="$isEdit ? route('books.show', $book) : route('books.index')" variant="ghost">
            {{ __('Cancel') }}
        </flux:button>

        <flux:button type="submit" variant="primary">
            {{ $isEdit ? __('Update Book') : __('Save Book') }}
        </flux:button>
    </div>
</div>

