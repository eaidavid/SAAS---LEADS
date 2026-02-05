<section class="glass panel" data-reveal>
  <div class="section-title text-2xl">Edit Import</div>
  <p class="text-slate-300 text-sm mt-1">Project: <?= htmlspecialchars((string) ($project["name"] ?? ""), ENT_QUOTES) ?></p>

  <form method="post" action="/imports/update" class="mt-6 space-y-4">
    <input type="hidden" name="id" value="<?= (int) ($import["id"] ?? 0) ?>" />
    <div>
      <label class="text-xs text-slate-400">Import name</label>
      <input type="text" name="name" class="input mt-2" required value="<?= htmlspecialchars((string) ($import["name"] ?? ""), ENT_QUOTES) ?>" />
    </div>
    <div class="flex gap-3">
      <button class="btn btn-primary">Save</button>
      <a href="/projects/show?id=<?= (int) ($project["id"] ?? 0) ?>" class="btn btn-secondary">Back</a>
    </div>
  </form>
</section>
