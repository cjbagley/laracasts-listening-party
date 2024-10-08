<div class="flex-1 min-w-0">
    <p class="text-[0.9rem] text-base font-semibold truncate text-slate-500">{{ $listeningParty->name }}</p>
    <p class="text-sm truncate text-slate-500 max-w-xs">{{ $listeningParty->episode->title }}</p>
    <p class="text-slate-400 uppercase tracking-tight text-xs">{{ $listeningParty->podcast->title }}</p>
    <p class="mt-1 text-xs">{{ $listeningParty->start_time }}</p>
</div>
