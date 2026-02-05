<section class="glass panel" data-reveal>
  <div class="section-title text-2xl">Google Maps Search</div>
  <p class="text-slate-300 text-sm mt-1">Find leads by keyword, city, and radius.</p>

  <?php if (!empty($error)): ?>
    <div class="mt-4 text-sm text-red-200 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3">
      <?= htmlspecialchars((string) $error, ENT_QUOTES) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="/leads/search" class="mt-6 grid md:grid-cols-4 gap-4">
    <div class="md:col-span-2">
      <label class="text-xs text-slate-400">Keyword</label>
      <input type="text" name="keyword" value="<?= htmlspecialchars((string) ($filters["keyword"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" placeholder="Ex: restaurante, dentista, barbearia" />
    </div>
    <div class="md:col-span-2">
      <label class="text-xs text-slate-400">Location</label>
      <input type="text" name="location" value="<?= htmlspecialchars((string) ($filters["location"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" placeholder="Ex: São Paulo, SP" />
    </div>
    <div>
      <label class="text-xs text-slate-400">Radius (meters)</label>
      <input type="number" name="radius" value="<?= htmlspecialchars((string) ($filters["radius"] ?? "5000"), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-xs text-slate-400">Type (optional)</label>
      <input type="text" name="type" value="<?= htmlspecialchars((string) ($filters["type"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" placeholder="restaurant, dentist" />
    </div>
    <div class="md:col-span-4">
      <button class="btn btn-primary">Search</button>
    </div>
  </form>
</section>

<section class="glass panel mt-6" data-reveal>
  <div class="flex items-center justify-between">
    <div class="section-title text-xl">Results</div>
    <span class="text-sm text-slate-400"><?= count($results ?? []) ?> found</span>
  </div>

  <form method="post" action="/leads/save-google" class="mt-4">
    <div class="overflow-auto rounded-2xl border border-slate-800">
      <table class="min-w-full text-sm">
        <thead class="text-slate-400 bg-slate-900/60">
          <tr>
            <th class="text-left py-2">Select</th>
            <th class="text-left py-2">Name</th>
            <th class="text-left py-2">Address</th>
            <th class="text-left py-2">Rating</th>
            <th class="text-left py-2">Reviews</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (($results ?? []) as $row): ?>
            <tr class="border-t border-slate-800 hover:bg-slate-900/50">
              <td class="py-2">
                <input type="checkbox" name="selected[]" value="<?= htmlspecialchars((string) ($row["place_id"] ?? ""), ENT_QUOTES) ?>" />
              </td>
              <td class="py-2">
                <div class="font-semibold"><?= htmlspecialchars((string) ($row["name"] ?? ""), ENT_QUOTES) ?></div>
                <div class="text-xs text-slate-400"><?= htmlspecialchars((string) ($row["category"] ?? ""), ENT_QUOTES) ?></div>
              </td>
              <td class="py-2"><?= htmlspecialchars((string) ($row["address"] ?? ""), ENT_QUOTES) ?></td>
              <td class="py-2"><?= htmlspecialchars((string) ($row["rating"] ?? ""), ENT_QUOTES) ?></td>
              <td class="py-2"><?= htmlspecialchars((string) ($row["reviews_count"] ?? ""), ENT_QUOTES) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($results)): ?>
            <tr class="border-t border-slate-800">
              <td class="py-4 text-slate-400" colspan="5">No results yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if (!empty($results)): ?>
      <div class="mt-4">
        <button class="btn btn-secondary text-sm">Save selected leads</button>
      </div>
    <?php endif; ?>
  </form>
</section>
