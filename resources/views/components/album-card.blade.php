@props(['album'])

@php($photoCount = count($album->photos))

<article class="group relative flex h-full flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-stone-900/5 transition hover:shadow-md hover:ring-brand-500/30">
  @if ($album->cover_url)
    <img src="{{ $album->cover_url }}" alt="" loading="lazy" class="aspect-[3/2] w-full object-cover outline-1 -outline-offset-1 outline-black/10" />
  @else
    <img src="{{ asset('assets/img/choir-group.jpg') }}" alt="" loading="lazy" class="aspect-[3/2] w-full object-cover outline-1 -outline-offset-1 outline-black/10" />
  @endif
  <div class="flex flex-1 flex-col p-6">
    <p class="text-sm font-medium text-brand-700">{{ $photoCount }} photo{{ $photoCount > 1 ? 's' : '' }}</p>
    <h3 class="mt-1 font-display text-lg font-semibold text-balance text-stone-900">
      <a href="{{ route('albums.show', $album) }}" class="focus-visible:outline-none">
        <span class="absolute inset-0" aria-hidden="true"></span>
        {{ $album->name }}
      </a>
    </h3>
    @if (filled($album->description))
      <p class="mt-2 text-sm text-pretty text-stone-600">{{ $album->description }}</p>
    @endif
    <span class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-semibold text-brand-700">Voir l'album
      <svg class="size-4 shrink-0 fill-current transition group-hover:translate-x-0.5" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8a.75.75 0 0 1 .75-.75h6.638L8.23 5.29a.75.75 0 1 1 1.04-1.08l3.5 3.25a.75.75 0 0 1 0 1.08l-3.5 3.25a.75.75 0 1 1-1.04-1.08l2.158-1.96H3.75A.75.75 0 0 1 3 8z" /></svg>
    </span>
  </div>
</article>
