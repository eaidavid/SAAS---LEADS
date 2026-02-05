<section class="glass panel" data-reveal>
  <div class="section-title text-2xl">New Import</div>
  <p class="text-slate-300 text-sm mt-1">Project: <?= htmlspecialchars((string) ($project["name"] ?? ""), ENT_QUOTES) ?></p>
  <p class="text-slate-300 text-sm">Niche inherited: <?= htmlspecialchars((string) ($project["niche"] ?? "—"), ENT_QUOTES) ?></p>

  <form method="post" action="/imports" enctype="multipart/form-data" class="mt-6 space-y-4">
    <input type="hidden" name="project_id" value="<?= (int) ($project["id"] ?? 0) ?>" />
    <div>
      <label class="text-xs text-slate-400">Import name (subfolder)</label>
      <input type="text" name="name" class="input mt-2" placeholder="Ex: Clinicas Goiânia 02-2026" />
    </div>
    <div>
      <label class="text-xs text-slate-400">CSV file</label>
      <input type="file" name="csv" accept=".csv" class="mt-2 text-sm" required />
    </div>
    <div class="text-xs text-slate-400">
      Columns used: B (Maps URL), F (Address), I (Phone), K (Mobile), O (Category), Q (Comments), R (Rating), S (Website).
    </div>
    <div class="flex gap-3">
      <button class="btn btn-primary">Import</button>
      <a href="/projects/show?id=<?= (int) ($project["id"] ?? 0) ?>" class="btn btn-secondary">Back</a>
    </div>
  </form>
</section>
