<section class="glass panel" data-reveal>
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <div class="section-title text-2xl">Leads</div>
      <p class="text-slate-300 text-sm">Manage pipeline, notes, and interactions.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="/projects" class="btn btn-secondary text-sm">Back</a>
      <a href="/leads/search" class="btn btn-secondary text-sm">Google Maps</a>
      <a href="/leads/new" class="btn btn-primary text-sm">New Lead</a>
      <?php
        $query = http_build_query([
          "status" => $filters["status"] ?? "",
          "q" => $filters["q"] ?? "",
        ]);
        $exportUrl = "/leads/export" . ($query !== "" ? "?" . $query : "");
      ?>
      <a href="<?= htmlspecialchars($exportUrl, ENT_QUOTES) ?>" class="btn btn-secondary text-sm">Export CSV</a>
    </div>
  </div>

  <div class="grid md:grid-cols-4 gap-4 mt-6">
    <?php foreach (($statuses ?? []) as $key => $label): ?>
      <?php
        $statusKey = strtolower((string) $key);
        $statusClass = preg_replace("/[^a-z0-9_-]+/", "", $statusKey);
      ?>
      <div class="stat-card status-card status-<?= htmlspecialchars((string) $statusClass, ENT_QUOTES) ?>">
        <div class="text-xs text-slate-400"><?= htmlspecialchars($label, ENT_QUOTES) ?></div>
        <div class="text-xl font-semibold mt-2"><?= (int) ($counts[$key] ?? 0) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-6 flex flex-wrap gap-2">
    <?php foreach (($projects ?? []) as $project): ?>
      <?php
        $active = (string) ($filters["project_id"] ?? "") === (string) ($project["id"] ?? "");
        $params = [
          "project_id" => $project["id"] ?? "",
        ];
        $link = "/leads?" . http_build_query($params);
      ?>
      <a href="<?= htmlspecialchars($link, ENT_QUOTES) ?>" class="chip <?= $active ? "active" : "" ?>">
        <?= htmlspecialchars((string) ($project["name"] ?? ""), ENT_QUOTES) ?>
      </a>
    <?php endforeach; ?>
    <?php if (!empty($filters["project_id"])): ?>
      <a href="/leads" class="chip">Clear</a>
    <?php endif; ?>
  </div>

  <?php if (!empty($filters["project_id"])): ?>
    <div class="mt-3 flex flex-wrap gap-2">
      <?php foreach (($imports ?? []) as $import): ?>
        <?php
          $active = (string) ($filters["import_id"] ?? "") === (string) ($import["id"] ?? "");
          $params = [
            "project_id" => $filters["project_id"],
            "import_id" => $import["id"] ?? "",
          ];
          $link = "/leads?" . http_build_query($params);
        ?>
        <a href="<?= htmlspecialchars($link, ENT_QUOTES) ?>" class="chip <?= $active ? "active" : "" ?>">
          <?= htmlspecialchars((string) ($import["name"] ?? ""), ENT_QUOTES) ?>
        </a>
      <?php endforeach; ?>
      <?php if (!empty($filters["import_id"])): ?>
        <a href="/leads?project_id=<?= htmlspecialchars((string) $filters["project_id"], ENT_QUOTES) ?>" class="chip">Clear import</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <form method="get" action="/leads" class="mt-6 flex flex-wrap gap-3 items-end">
    <?php if (!empty($filters["project_id"])): ?>
      <input type="hidden" name="project_id" value="<?= htmlspecialchars((string) $filters["project_id"], ENT_QUOTES) ?>" />
    <?php endif; ?>
    <?php if (!empty($filters["import_id"])): ?>
      <input type="hidden" name="import_id" value="<?= htmlspecialchars((string) $filters["import_id"], ENT_QUOTES) ?>" />
    <?php endif; ?>
    <div>
      <label class="text-xs text-slate-400">Search</label>
      <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters["q"] ?? ""), ENT_QUOTES) ?>" class="input mt-2 text-sm" />
    </div>
    <div>
      <label class="text-xs text-slate-400">Status</label>
      <select name="status" class="select mt-2 text-sm">
        <option value="">All</option>
        <?php foreach (($statuses ?? []) as $value => $label): ?>
          <option value="<?= htmlspecialchars($value, ENT_QUOTES) ?>" <?= ($filters["status"] ?? "") === $value ? "selected" : "" ?>>
            <?= htmlspecialchars($label, ENT_QUOTES) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs text-slate-400">Category</label>
      <input type="text" name="category" value="<?= htmlspecialchars((string) ($filters["category"] ?? ""), ENT_QUOTES) ?>" class="input mt-2 text-sm" />
    </div>
    <div>
      <label class="text-xs text-slate-400">Min rating</label>
      <input type="number" step="0.1" name="min_rating" value="<?= htmlspecialchars((string) ($filters["min_rating"] ?? ""), ENT_QUOTES) ?>" class="input mt-2 text-sm" />
    </div>
    <div>
      <label class="text-xs text-slate-400">Order by</label>
      <select name="order_by" class="select mt-2 text-sm">
        <?php
          $orders = [
            "created_at" => "Created",
            "imported_at" => "Imported",
            "name" => "Name",
            "category" => "Category",
            "rating" => "Rating",
            "reviews_count" => "Reviews",
            "city" => "City",
            "state" => "State",
          ];
        ?>
        <?php foreach ($orders as $value => $label): ?>
          <option value="<?= $value ?>" <?= ($filters["order_by"] ?? "created_at") === $value ? "selected" : "" ?>>
            <?= htmlspecialchars($label, ENT_QUOTES) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs text-slate-400">Direction</label>
      <select name="direction" class="select mt-2 text-sm">
        <option value="DESC" <?= strtoupper((string) ($filters["direction"] ?? "DESC")) === "DESC" ? "selected" : "" ?>>Desc</option>
        <option value="ASC" <?= strtoupper((string) ($filters["direction"] ?? "DESC")) === "ASC" ? "selected" : "" ?>>Asc</option>
      </select>
    </div>
    <button class="btn btn-secondary text-sm">Filter</button>
  </form>

  <div class="mt-6 overflow-auto rounded-2xl border border-slate-800">
    <table class="min-w-full text-sm table-spaced">
      <thead class="text-slate-400 bg-slate-900/60">
        <tr>
          <th class="text-left py-2">Name</th>
          <th class="text-left py-2">Rating</th>
          <th class="text-left py-2">Status</th>
          <th class="text-left py-2">Score</th>
          <th class="text-left py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($leads ?? []) as $lead): ?>
          <?php
            $waPhone = (string) ($lead["whatsapp_phone"] ?? "");
            $waLink = $waPhone !== "" ? "https://wa.me/" . $waPhone : "";
            $siteLink = (string) ($lead["website"] ?? "");
            $statusKey = strtolower((string) ($lead["status"] ?? ""));
            $statusClass = preg_replace("/[^a-z0-9_-]+/", "", $statusKey);
          ?>
          <tr class="border-t border-slate-800 hover:bg-slate-900/50">
            <td class="py-2"><?= htmlspecialchars((string) ($lead["name"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2"><?= htmlspecialchars((string) ($lead["rating"] ?? ""), ENT_QUOTES) ?></td>
            <td class="py-2">
              <span class="badge status-<?= htmlspecialchars((string) $statusClass, ENT_QUOTES) ?>">
                <?= htmlspecialchars((string) (($statuses[$lead["status"] ?? ""] ?? $lead["status"] ?? "")), ENT_QUOTES) ?>
              </span>
            </td>
            <td class="py-2"><?= (int) ($lead["score"] ?? 0) ?></td>
            <td class="py-2">
              <div class="flex gap-2">
                <a href="<?= htmlspecialchars($waLink, ENT_QUOTES) ?>" class="icon-btn whatsapp <?= $waLink === "" ? "disabled" : "" ?>" target="_blank" rel="noopener" title="WhatsApp">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20.5 3.5A10 10 0 0 0 3.9 19.3L3 22l2.8-.9A10 10 0 1 0 20.5 3.5Zm-8.5 16a8 8 0 0 1-4-1.1l-.3-.2-2 .7.6-2-.2-.3A8 8 0 1 1 12 19.5Zm4.5-5.1c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.6.1-.2.2-.6.8-.8.9-.1.1-.3.2-.5.1a6.6 6.6 0 0 1-2-1.3 7.5 7.5 0 0 1-1.4-1.7c-.1-.2 0-.4.1-.5l.4-.5c.1-.2.1-.3.2-.5 0-.2 0-.4-.1-.5l-.7-1.6c-.2-.4-.4-.3-.6-.3h-.5c-.2 0-.5.1-.7.3-.2.2-1 1-1 2.4 0 1.4 1 2.8 1.2 3 .2.2 2 3.1 5 4.4.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.6-.1 1.4-.6 1.6-1.1.2-.5.2-1 .1-1.1-.1-.1-.2-.2-.4-.3Z"/>
                  </svg>
                </a>
                <a href="<?= htmlspecialchars($siteLink, ENT_QUOTES) ?>" class="icon-btn site <?= $siteLink === "" ? "disabled" : "" ?>" target="_blank" rel="noopener" title="Website">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm6.9 6h-3.1a15 15 0 0 0-1.3-3.3A8 8 0 0 1 18.9 8ZM12 4.1c.6.8 1.2 2 1.6 3.9H10.4c.4-1.9 1-3.1 1.6-3.9ZM4.9 8a8 8 0 0 1 4.4-3.3A15 15 0 0 0 8 8H4.9Zm0 8H8c.3 1.3.7 2.4 1.3 3.3A8 8 0 0 1 4.9 16Zm3.1-2h-3.5a8 8 0 0 1 0-4H8c-.1.7-.1 1.3-.1 2s0 1.3.1 2Zm4 6c-.6-.8-1.2-2-1.6-3.9h3.2c-.4 1.9-1 3.1-1.6 3.9Zm2-6H9.9c-.1-.7-.1-1.3-.1-2s0-1.3.1-2H14c.1.7.1 1.3.1 2s0 1.3-.1 2Zm.7 5.3c.6-.9 1-2 1.3-3.3h3.1a8 8 0 0 1-4.4 3.3ZM16 8c-.3-1.3-.7-2.4-1.3-3.3A8 8 0 0 1 19.1 8H16Zm4.1 2a8 8 0 0 1 0 4H16c.1-.7.1-1.3.1-2s0-1.3-.1-2h4.1Z"/>
                  </svg>
                </a>
                <a href="/leads/show?id=<?= (int) ($lead["id"] ?? 0) ?>" class="icon-btn view" title="View">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 5c5.5 0 9.6 4 10.8 6.1.2.3.2.7 0 1C21.6 14 17.5 18 12 18S2.4 14 1.2 12.1c-.2-.3-.2-.7 0-1C2.4 9 6.5 5 12 5Zm0 2c-4.2 0-7.6 2.9-8.8 5 1.2 2.1 4.6 5 8.8 5s7.6-2.9 8.8-5C19.6 9.9 16.2 7 12 7Zm0 2.2A2.8 2.8 0 1 1 9.2 12 2.8 2.8 0 0 1 12 9.2Z"/>
                  </svg>
                </a>
                <a href="/leads/edit?id=<?= (int) ($lead["id"] ?? 0) ?>" class="icon-btn edit" title="Edit">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 15.5V20h4.5L19 9.5l-4.5-4.5L4 15.5Zm16.7-9.3a1 1 0 0 0 0-1.4l-1.5-1.5a1 1 0 0 0-1.4 0l-1.3 1.3L19.4 7l1.3-1.3Z"/>
                  </svg>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($leads)): ?>
          <tr class="border-t border-slate-800">
            <td class="py-4 text-slate-400" colspan="5">No leads found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
