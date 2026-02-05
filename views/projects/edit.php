<section class="glass panel" data-reveal>
  <div class="section-title text-2xl">Edit Project</div>
  <p class="text-slate-300 text-sm mt-1">Update project details.</p>

  <form method="post" action="/projects/update" class="mt-6 space-y-4">
    <input type="hidden" name="id" value="<?= (int) ($project["id"] ?? 0) ?>" />
    <div>
      <label class="text-xs text-slate-400">Project name</label>
      <input type="text" name="name" class="input mt-2" required value="<?= htmlspecialchars((string) ($project["name"] ?? ""), ENT_QUOTES) ?>" />
    </div>
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="text-xs text-slate-400">Niche (preset)</label>
        <select name="niche" class="select mt-2">
          <option value="">Select</option>
          <?php foreach (($niches ?? []) as $niche): ?>
            <option value="<?= htmlspecialchars((string) $niche, ENT_QUOTES) ?>" <?= ($project["niche"] ?? "") === $niche ? "selected" : "" ?>>
              <?= htmlspecialchars((string) $niche, ENT_QUOTES) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-400">Or create new niche</label>
        <input type="text" name="niche_custom" class="input mt-2" />
      </div>
    </div>
    <div>
      <label class="text-xs text-slate-400">Description</label>
      <textarea name="description" rows="3" class="textarea mt-2"><?= htmlspecialchars((string) ($project["description"] ?? ""), ENT_QUOTES) ?></textarea>
    </div>
    <div class="flex gap-3">
      <button class="btn btn-primary">Save</button>
      <a href="/projects/show?id=<?= (int) ($project["id"] ?? 0) ?>" class="btn btn-secondary">Back</a>
    </div>
  </form>
</section>
