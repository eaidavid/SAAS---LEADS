<section class="glass panel" data-reveal>
  <?php
    $projectId = (int) ($lead["project_id"] ?? 0);
    $importId = (int) ($lead["import_id"] ?? 0);
    if ($importId > 0) {
      $query = http_build_query([
        "project_id" => $projectId > 0 ? $projectId : null,
        "import_id" => $importId,
      ]);
      $backUrl = "/leads" . ($query !== "" ? "?" . $query : "");
    } else {
      $backUrl = $projectId > 0 ? "/projects/show?id=" . $projectId : "/leads";
    }
    $statusKey = strtolower((string) ($lead["status"] ?? ""));
    $statusClass = preg_replace("/[^a-z0-9_-]+/", "", $statusKey);
  ?>
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <div class="section-title text-2xl"><?= htmlspecialchars((string) ($lead["name"] ?? ""), ENT_QUOTES) ?></div>
      <div class="text-sm text-slate-400">
        <span class="text-slate-400">Status:</span>
        <span class="badge status-<?= htmlspecialchars((string) $statusClass, ENT_QUOTES) ?>">
          <?= htmlspecialchars((string) (($statuses[$lead["status"] ?? ""] ?? $lead["status"] ?? "")), ENT_QUOTES) ?>
        </span>
      </div>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES) ?>" class="btn btn-secondary text-sm">Back</a>
      <a href="/leads/edit?id=<?= (int) ($lead["id"] ?? 0) ?>" class="btn btn-secondary text-sm">Edit</a>
      <form method="post" action="/leads/delete" onsubmit="return confirm('Delete this lead?');">
        <input type="hidden" name="id" value="<?= (int) ($lead["id"] ?? 0) ?>" />
        <button class="btn btn-danger text-sm">Delete</button>
      </form>
    </div>
  </div>

  <div class="grid md:grid-cols-3 gap-6 mt-6">
    <div class="stat-card">
      <div class="text-xs text-slate-400">Contact</div>
      <div class="mt-2 space-y-1 text-sm">
        <?php $waPhone = (string) ($lead["whatsapp_phone"] ?? ""); ?>
        <div><span class="text-slate-400">Phone:</span> <?= htmlspecialchars((string) ($lead["phone"] ?? ""), ENT_QUOTES) ?></div>
        <div><span class="text-slate-400">Mobile:</span> <?= htmlspecialchars((string) ($lead["mobile"] ?? ""), ENT_QUOTES) ?></div>
        <?php if ($waPhone !== ""): ?>
          <div>
            <span class="text-slate-400">WhatsApp:</span>
            <a href="https://wa.me/<?= htmlspecialchars($waPhone, ENT_QUOTES) ?>" class="text-blue-300 hover:text-blue-200" target="_blank" rel="noopener">Open</a>
          </div>
        <?php endif; ?>
        <div><span class="text-slate-400">Email:</span> <?= htmlspecialchars((string) ($lead["email"] ?? ""), ENT_QUOTES) ?></div>
        <div>
          <span class="text-slate-400">Website:</span>
          <?php if (!empty($lead["website"])): ?>
            <a href="<?= htmlspecialchars((string) $lead["website"], ENT_QUOTES) ?>" class="text-blue-300 hover:text-blue-200" target="_blank" rel="noopener">Open</a>
          <?php else: ?>
            —
          <?php endif; ?>
        </div>
        <?php if (!empty($lead["google_maps_url"])): ?>
          <div>
            <span class="text-slate-400">Maps:</span>
            <a href="<?= htmlspecialchars((string) $lead["google_maps_url"], ENT_QUOTES) ?>" class="text-blue-300 hover:text-blue-200" target="_blank" rel="noopener">Open</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="stat-card">
      <div class="text-xs text-slate-400">Location</div>
      <div class="mt-2 space-y-1 text-sm">
        <div><span class="text-slate-400">Address:</span> <?= htmlspecialchars((string) ($lead["address"] ?? ""), ENT_QUOTES) ?></div>
        <div><span class="text-slate-400">City:</span> <?= htmlspecialchars((string) ($lead["city"] ?? ""), ENT_QUOTES) ?></div>
        <div><span class="text-slate-400">State:</span> <?= htmlspecialchars((string) ($lead["state"] ?? ""), ENT_QUOTES) ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="text-xs text-slate-400">Business</div>
      <div class="mt-2 space-y-1 text-sm">
        <div><span class="text-slate-400">Category:</span> <span class="clamp-2"><?= htmlspecialchars((string) ($lead["category"] ?? ""), ENT_QUOTES) ?></span></div>
        <div><span class="text-slate-400">Rating:</span> <?= htmlspecialchars((string) ($lead["rating"] ?? ""), ENT_QUOTES) ?></div>
        <div><span class="text-slate-400">Reviews:</span> <?= htmlspecialchars((string) ($lead["reviews_count"] ?? ""), ENT_QUOTES) ?></div>
        <div><span class="text-slate-400">Score:</span> <?= htmlspecialchars((string) ($lead["score"] ?? ""), ENT_QUOTES) ?></div>
      </div>
    </div>
  </div>

  <div class="grid md:grid-cols-2 gap-6 mt-6">
    <div class="stat-card">
      <div class="text-sm font-semibold">Tags</div>
      <div class="text-sm text-slate-300 mt-2"><?= htmlspecialchars((string) ($lead["tags"] ?? ""), ENT_QUOTES) ?: "No tags" ?></div>
    </div>
    <div class="stat-card">
      <div class="text-sm font-semibold">Notes</div>
      <div class="text-sm text-slate-300 mt-2 whitespace-pre-line"><?= htmlspecialchars((string) ($lead["notes"] ?? ""), ENT_QUOTES) ?: "No notes" ?></div>
    </div>
  </div>
</section>

<section class="glass panel mt-6" data-reveal>
  <div class="section-title text-xl">Tags & Notes</div>
  <p class="text-slate-300 text-sm mt-1">Update tags and notes quickly.</p>
  <form method="post" action="/leads/notes" class="mt-4 grid md:grid-cols-2 gap-4">
    <input type="hidden" name="id" value="<?= (int) ($lead["id"] ?? 0) ?>" />
    <div>
      <label class="text-xs text-slate-400">Tags (comma separated)</label>
      <input type="text" name="tags" class="input mt-2" value="<?= htmlspecialchars((string) ($lead["tags"] ?? ""), ENT_QUOTES) ?>" />
    </div>
    <div>
      <label class="text-xs text-slate-400">Notes</label>
      <textarea name="notes" rows="3" class="textarea mt-2"><?= htmlspecialchars((string) ($lead["notes"] ?? ""), ENT_QUOTES) ?></textarea>
    </div>
    <div>
      <button class="btn btn-secondary text-sm">Save</button>
    </div>
  </form>
</section>

<section class="glass panel mt-6" data-reveal>
  <div class="section-title text-xl">WhatsApp Message</div>
  <p class="text-slate-300 text-sm mt-1">Send a template or write a custom message.</p>
  <form method="post" action="/leads/whatsapp" class="mt-4 grid md:grid-cols-2 gap-4" data-whatsapp-form>
    <input type="hidden" name="lead_id" value="<?= (int) ($lead["id"] ?? 0) ?>" />
    <div>
      <label class="text-xs text-slate-400">Template (optional)</label>
      <select name="template_id" class="select mt-2" data-template-select>
        <option value="">Select template</option>
        <?php foreach (($templates ?? []) as $template): ?>
          <?php $templateContent = str_replace(["\r\n", "\r"], "\n", (string) ($template["content"] ?? "")); ?>
          <option value="<?= (int) ($template["id"] ?? 0) ?>" data-content="<?= htmlspecialchars($templateContent, ENT_QUOTES) ?>">
            <?= htmlspecialchars((string) ($template["name"] ?? ""), ENT_QUOTES) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="button" class="btn btn-secondary text-sm mt-3" data-fill-template>Use template</button>
    </div>
    <div>
      <label class="text-xs text-slate-400">Custom message (optional)</label>
      <textarea name="message" rows="5" class="textarea mt-2" placeholder="Use {nome}, {cidade}, {categoria}" data-message></textarea>
      <div class="text-xs text-slate-400 mt-2">If the message is empty, the selected template is used.</div>
    </div>
    <div>
      <label class="text-xs text-slate-400">Phone (optional)</label>
      <input type="text" name="phone" class="input mt-2" value="<?= htmlspecialchars((string) ($lead["mobile"] ?? $lead["phone"] ?? ""), ENT_QUOTES) ?>" placeholder="55 11 99999-0000" />
      <div class="text-xs text-slate-400 mt-2">If empty, we use the lead phone.</div>
    </div>
    <div class="flex items-end">
      <button class="btn btn-primary text-sm">Open WhatsApp</button>
    </div>
  </form>
</section>

<?php if (!empty($lead["comments"])): ?>
  <section class="glass panel mt-6" data-reveal>
    <div class="section-title text-xl">Imported Comments</div>
    <div class="text-sm text-slate-300 mt-2 whitespace-pre-line"><?= htmlspecialchars((string) $lead["comments"], ENT_QUOTES) ?></div>
  </section>
<?php endif; ?>

<section class="glass panel mt-6" data-reveal>
  <div class="section-title text-xl">Activities</div>
  <p class="text-slate-300 text-sm mt-1">Log attempts, messages, calls, and status changes.</p>

  <form method="post" action="/leads/interaction" class="mt-4 grid md:grid-cols-4 gap-3">
    <input type="hidden" name="lead_id" value="<?= (int) ($lead["id"] ?? 0) ?>" />
    <div>
      <label class="text-xs text-slate-400">Type</label>
      <select name="type" class="select mt-2 text-sm">
        <?php foreach (($types ?? []) as $value => $label): ?>
          <option value="<?= htmlspecialchars($value, ENT_QUOTES) ?>"><?= htmlspecialchars($label, ENT_QUOTES) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs text-slate-400">Date</label>
      <input type="datetime-local" name="date" class="input mt-2 text-sm" />
    </div>
    <div class="md:col-span-2">
      <label class="text-xs text-slate-400">Message</label>
      <input type="text" name="message" class="input mt-2 text-sm" />
    </div>
    <div>
      <button class="btn btn-primary text-sm">Add Interaction</button>
    </div>
  </form>

  <div class="mt-6 timeline">
    <?php foreach (($interactions ?? []) as $interaction): ?>
      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <div class="timeline-card">
          <div class="flex items-center justify-between text-xs text-slate-400">
            <div><?= htmlspecialchars((string) (($types[$interaction["type"] ?? ""] ?? $interaction["type"] ?? "")), ENT_QUOTES) ?></div>
            <div><?= htmlspecialchars((string) ($interaction["date"] ?? $interaction["created_at"] ?? ""), ENT_QUOTES) ?></div>
          </div>
          <div class="text-sm text-slate-200 mt-2"><?= htmlspecialchars((string) ($interaction["message"] ?? ""), ENT_QUOTES) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($interactions)): ?>
      <div class="text-sm text-slate-400">No activities yet.</div>
    <?php endif; ?>
  </div>
</section>

<script>
  (function () {
    const form = document.querySelector("[data-whatsapp-form]");
    if (!form) return;

    const select = form.querySelector("[data-template-select]");
    const textarea = form.querySelector("[data-message]");
    const fillBtn = form.querySelector("[data-fill-template]");

    if (!select || !textarea || !fillBtn) return;

    fillBtn.addEventListener("click", function (event) {
      event.preventDefault();
      const option = select.options[select.selectedIndex];
      const content = option ? option.getAttribute("data-content") : "";
      if (content) {
        textarea.value = content;
        textarea.focus();
      }
    });
  })();
</script>
