<section class="glass panel" data-reveal>
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <div class="section-title text-2xl"><?= htmlspecialchars((string) ($project["name"] ?? ""), ENT_QUOTES) ?></div>
      <p class="text-slate-300 text-sm mt-1">Niche: <?= htmlspecialchars((string) ($project["niche"] ?? "--"), ENT_QUOTES) ?></p>
    </div>
    <div class="flex gap-2">
      <a href="/projects" class="btn btn-secondary text-sm">Back</a>
      <a href="/imports/new?project_id=<?= (int) ($project["id"] ?? 0) ?>" class="btn btn-primary text-sm">New Import</a>
    </div>
  </div>

  <?php if (!empty($project["description"])): ?>
    <p class="text-slate-300 text-sm mt-4"><?= htmlspecialchars((string) $project["description"], ENT_QUOTES) ?></p>
  <?php endif; ?>
</section>

<section class="glass panel mt-6" data-reveal>
  <div class="section-title text-xl">Imports (Subfolders)</div>
  <p class="text-slate-300 text-sm mt-1">Each import is a subfolder with its own leads.</p>

  <div class="mt-4 overflow-auto rounded-2xl border border-slate-800">
    <table class="min-w-full text-sm table-spaced">
      <thead class="text-slate-400 bg-slate-900/60">
        <tr>
          <th class="text-left py-2">Name</th>
          <th class="text-left py-2">Source</th>
          <th class="text-left py-2">Imported</th>
          <th class="text-left py-2">Rows</th>
          <th class="text-left py-2">Leads</th>
          <th class="text-left py-2">Created</th>
          <th class="text-left py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($imports ?? []) as $import): ?>
          <tr class="border-t border-slate-800 hover:bg-slate-900/50">
            <td class="py-2"><?= htmlspecialchars((string) ($import["name"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2"><?= htmlspecialchars((string) ($import["source_filename"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2"><?= htmlspecialchars((string) ($import["imported_at"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2"><?= (int) ($import["imported_rows"] ?? 0) ?>/<?= (int) ($import["total_rows"] ?? 0) ?></td>
            <td class="py-2"><?= (int) ($import["lead_count"] ?? 0) ?></td>
            <td class="py-2"><?= htmlspecialchars((string) ($import["created_at"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2">
              <div class="flex gap-2">
                <a href="/leads?project_id=<?= (int) ($project["id"] ?? 0) ?>&import_id=<?= (int) ($import["id"] ?? 0) ?>" class="text-blue-300 hover:text-blue-200">View</a>
                <a href="/imports/edit?id=<?= (int) ($import["id"] ?? 0) ?>" class="text-slate-300 hover:text-white">Edit</a>
                <form method="post" action="/imports/archive" onsubmit="return confirm('Archive this import?');">
                  <input type="hidden" name="id" value="<?= (int) ($import["id"] ?? 0) ?>" />
                  <button class="text-amber-300 hover:text-amber-200">Archive</button>
                </form>
                <form method="post" action="/imports/delete" onsubmit="return confirm('Delete this import?');">
                  <input type="hidden" name="id" value="<?= (int) ($import["id"] ?? 0) ?>" />
                  <button class="text-red-300 hover:text-red-200">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($imports)): ?>
          <tr class="border-t border-slate-800">
            <td class="py-4 text-slate-400" colspan="7">No imports yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php if (!empty($archivedImports)): ?>
  <section class="glass panel mt-6" data-reveal>
    <div class="section-title text-xl">Archived Imports</div>
    <p class="text-slate-300 text-sm mt-1">Restore archived subfolders.</p>

    <div class="mt-4 overflow-auto rounded-2xl border border-slate-800">
      <table class="min-w-full text-sm table-spaced">
        <thead class="text-slate-400 bg-slate-900/60">
          <tr>
            <th class="text-left">Name</th>
            <th class="text-left">Source</th>
            <th class="text-left">Archived</th>
            <th class="text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($archivedImports as $import): ?>
            <tr class="border-t border-slate-800 hover:bg-slate-900/50">
              <td class="py-2"><?= htmlspecialchars((string) ($import["name"] ?? ""), ENT_QUOTES) ?></td>
              <td class="py-2"><?= htmlspecialchars((string) ($import["source_filename"] ?? ""), ENT_QUOTES) ?></td>
              <td class="py-2"><?= htmlspecialchars((string) ($import["archived_at"] ?? ""), ENT_QUOTES) ?></td>
              <td class="py-2">
                <div class="flex gap-2">
                  <form method="post" action="/imports/restore">
                    <input type="hidden" name="id" value="<?= (int) ($import["id"] ?? 0) ?>" />
                    <button class="text-emerald-300 hover:text-emerald-200">Restore</button>
                  </form>
                  <form method="post" action="/imports/delete" onsubmit="return confirm('Delete this import permanently?');">
                    <input type="hidden" name="id" value="<?= (int) ($import["id"] ?? 0) ?>" />
                    <button class="text-red-300 hover:text-red-200">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>
