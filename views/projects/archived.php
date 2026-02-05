<section class="glass panel" data-reveal>
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <div class="section-title text-2xl">Archived Projects</div>
      <p class="text-slate-300 text-sm">Restore or permanently delete archived projects.</p>
    </div>
    <a href="/projects" class="btn btn-secondary text-sm">Back</a>
  </div>

  <div class="mt-6 overflow-auto rounded-2xl border border-slate-800">
    <table class="min-w-full text-sm table-spaced">
      <thead class="text-slate-400 bg-slate-900/60">
        <tr>
          <th class="text-left">Name</th>
          <th class="text-left">Niche</th>
          <th class="text-left">Imports</th>
          <th class="text-left">Leads</th>
          <th class="text-left">Archived</th>
          <th class="text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($projects ?? []) as $project): ?>
          <tr class="border-t border-slate-800 hover:bg-slate-900/50">
            <td class="py-2"><?= htmlspecialchars((string) ($project["name"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2"><?= htmlspecialchars((string) ($project["niche"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2"><?= (int) ($project["imports_count"] ?? 0) ?></td>
            <td class="py-2"><?= (int) ($project["leads_count"] ?? 0) ?></td>
            <td class="py-2"><?= htmlspecialchars((string) ($project["archived_at"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2">
              <div class="flex gap-2">
                <form method="post" action="/projects/restore">
                  <input type="hidden" name="id" value="<?= (int) ($project["id"] ?? 0) ?>" />
                  <button class="text-emerald-300 hover:text-emerald-200">Restore</button>
                </form>
                <form method="post" action="/projects/delete" onsubmit="return confirm('Delete this project permanently?');">
                  <input type="hidden" name="id" value="<?= (int) ($project["id"] ?? 0) ?>" />
                  <button class="text-red-300 hover:text-red-200">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($projects)): ?>
          <tr class="border-t border-slate-800">
            <td class="py-4 text-slate-400" colspan="6">No archived projects.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
