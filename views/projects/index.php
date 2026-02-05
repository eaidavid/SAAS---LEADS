<section class="glass panel" data-reveal>
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <div class="section-title text-2xl">Projects</div>
      <p class="text-slate-300 text-sm">Organize imports into projects and subfolders.</p>
    </div>
    <div class="flex gap-2">
      <a href="/" class="btn btn-secondary text-sm">Back</a>
      <a href="/projects/archived" class="btn btn-secondary text-sm">Archived</a>
      <a href="/projects/new" class="btn btn-primary text-sm">New Project</a>
    </div>
  </div>

  <div class="mt-6 overflow-auto rounded-2xl border border-slate-800">
    <table class="min-w-full text-sm table-spaced">
      <thead class="text-slate-400 bg-slate-900/60">
        <tr>
          <th class="text-left py-2">Name</th>
          <th class="text-left py-2">Niche</th>
          <th class="text-left py-2">Imports</th>
          <th class="text-left py-2">Leads</th>
          <th class="text-left py-2">Created</th>
          <th class="text-left py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($projects ?? []) as $project): ?>
          <tr class="border-t border-slate-800 hover:bg-slate-900/50">
            <td class="py-2"><?= htmlspecialchars((string) ($project["name"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2"><?= htmlspecialchars((string) ($project["niche"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2"><?= (int) ($project["imports_count"] ?? 0) ?></td>
            <td class="py-2"><?= (int) ($project["leads_count"] ?? 0) ?></td>
            <td class="py-2"><?= htmlspecialchars((string) ($project["created_at"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2">
              <div class="flex gap-2">
                <a href="/projects/show?id=<?= (int) ($project["id"] ?? 0) ?>" class="text-blue-300 hover:text-blue-200">Open</a>
                <a href="/projects/edit?id=<?= (int) ($project["id"] ?? 0) ?>" class="text-slate-300 hover:text-white">Edit</a>
                <form method="post" action="/projects/archive" onsubmit="return confirm('Archive this project?');">
                  <input type="hidden" name="id" value="<?= (int) ($project["id"] ?? 0) ?>" />
                  <button class="text-amber-300 hover:text-amber-200">Archive</button>
                </form>
                <form method="post" action="/projects/delete" onsubmit="return confirm('Delete this project?');">
                  <input type="hidden" name="id" value="<?= (int) ($project["id"] ?? 0) ?>" />
                  <button class="text-red-300 hover:text-red-200">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($projects)): ?>
          <tr class="border-t border-slate-800">
            <td class="py-4 text-slate-400" colspan="6">No projects yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
