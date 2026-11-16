<x-layouts.site :title="$album->name.' — Chorale La Touraline'" :description="$album->description">
  <article class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-linear-180 from-brand-100 via-brand-50 to-brand-50"></div>

    <div class="mx-auto max-w-7xl px-6 pt-12 pb-16 sm:pt-16 lg:px-8 lg:pb-24">
      <a href="{{ route('home') }}#photos" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-500">
        <svg class="size-4 shrink-0 rotate-180 fill-current" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8a.75.75 0 0 1 .75-.75h6.638L8.23 5.29a.75.75 0 1 1 1.04-1.08l3.5 3.25a.75.75 0 0 1 0 1.08l-3.5 3.25a.75.75 0 1 1-1.04-1.08l2.158-1.96H3.75A.75.75 0 0 1 3 8z" /></svg>
        Tous nos albums
      </a>

      <div class="max-w-2xl">
        <p class="mt-8 text-sm font-semibold tracking-wide text-brand-600">En images</p>
        <h1 class="mt-3 font-display text-4xl font-semibold tracking-tight text-balance text-stone-900 sm:text-5xl">
          {{ $album->name }}
        </h1>
        @if (filled($album->description))
          <p class="mt-6 text-lg text-pretty text-stone-600">{{ $album->description }}</p>
        @endif
      </div>

      <ul role="list" class="mt-12 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
        @foreach ($album->photo_urls as $photoUrl)
          <li>
            <a href="{{ $photoUrl }}" target="_blank" rel="noopener" class="block overflow-hidden rounded-2xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
              <img src="{{ $photoUrl }}" alt="{{ $album->name }} — photo {{ $loop->iteration }}" loading="lazy" class="aspect-square w-full object-cover outline-1 -outline-offset-1 outline-black/10 transition hover:scale-105" />
            </a>
          </li>
        @endforeach
      </ul>
    </div>
  </article>
</x-layouts.site>
