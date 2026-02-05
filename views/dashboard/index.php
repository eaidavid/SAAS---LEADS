<section class="glass panel" data-reveal>
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <div class="section-title text-2xl">Pipeline Snapshot</div>
      <p class="text-sm text-slate-300 mt-1">Live metrics for today.</p>
    </div>
    <span class="badge">CRM Core</span>
  </div>

  <div class="grid md:grid-cols-3 gap-4 mt-6">
    <div class="stat-card">
      <div class="text-xs text-slate-400 uppercase tracking-wide">Total Leads</div>
      <div class="text-3xl font-semibold mt-2"><?= (int)($metrics["leads"] ?? 0) ?></div>
    </div>
    <div class="stat-card">
      <div class="text-xs text-slate-400 uppercase tracking-wide">Proposals</div>
      <div class="text-3xl font-semibold mt-2"><?= (int)($metrics["proposals"] ?? 0) ?></div>
    </div>
    <div class="stat-card">
      <div class="text-xs text-slate-400 uppercase tracking-wide">Conversion</div>
      <div class="text-3xl font-semibold mt-2"><?= (int)($metrics["conversion"] ?? 0) ?>%</div>
    </div>
  </div>
</section>

<section class="glass panel mt-8" data-reveal>
  <div class="section-title text-xl">Welcome</div>
  <p class="text-slate-300 mt-2">Base structure ready. Next: wire database, auth, and Google Maps integration.</p>
  <div class="mt-4 flex flex-wrap gap-3 text-sm">
    <span class="badge">Lead tracking</span>
    <span class="badge">Proposals</span>
    <span class="badge">Automation ready</span>
  </div>
</section>
