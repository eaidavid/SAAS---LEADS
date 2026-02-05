<section class="glass panel" data-reveal>
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <div class="section-title text-2xl">WhatsApp Templates</div>
      <p class="text-slate-300 text-sm mt-1">Create templates and send to leads with one click.</p>
    </div>
    <a href="/templates/whatsapp?seed=1" class="btn btn-secondary text-sm">Add defaults</a>
  </div>

  <div class="grid lg:grid-cols-2 gap-6 mt-6">
    <div class="stat-card">
      <div class="text-sm font-semibold">New Template</div>
      <form method="post" action="/templates/whatsapp" class="mt-4 space-y-4">
        <div>
          <label class="text-xs text-slate-400">Name</label>
          <input type="text" name="name" class="input mt-2" placeholder="Ex: Landing Page" />
        </div>
        <div>
          <label class="text-xs text-slate-400">Message</label>
          <textarea name="content" rows="5" class="textarea mt-2" placeholder="Use {nome}, {cidade}, {categoria}"></textarea>
        </div>
        <button class="btn btn-primary text-sm">Save Template</button>
      </form>
    </div>

    <div class="stat-card">
      <div class="text-sm font-semibold">Send Template</div>
      <form method="post" action="/templates/whatsapp/send" class="mt-4 space-y-4">
        <div>
          <label class="text-xs text-slate-400">Template</label>
          <select name="template_id" class="select mt-2">
            <?php foreach (($templates ?? []) as $template): ?>
              <option value="<?= (int) ($template["id"] ?? 0) ?>">
                <?= htmlspecialchars((string) ($template["name"] ?? ""), ENT_QUOTES) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-400">Lead (optional)</label>
          <select name="lead_id" class="select mt-2">
            <option value="">Select lead</option>
            <?php foreach (($leads ?? []) as $lead): ?>
              <option value="<?= (int) ($lead["id"] ?? 0) ?>">
                <?= htmlspecialchars((string) ($lead["name"] ?? ""), ENT_QUOTES) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-xs text-slate-400">Phone (optional)</label>
          <input type="text" name="phone" class="input mt-2" placeholder="55 11 99999-0000" />
        </div>
        <div class="text-xs text-slate-400">If lead is selected, phone is auto-filled from lead.</div>
        <button class="btn btn-secondary text-sm">Open WhatsApp</button>
      </form>
    </div>
  </div>

  <div class="mt-8 overflow-auto rounded-2xl border border-slate-800">
    <table class="min-w-full text-sm table-spaced">
      <thead class="text-slate-400 bg-slate-900/60">
        <tr>
          <th class="text-left">Name</th>
          <th class="text-left">Preview</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($templates ?? []) as $template): ?>
          <tr class="border-t border-slate-800 hover:bg-slate-900/50">
            <td class="font-semibold"><?= htmlspecialchars((string) ($template["name"] ?? ""), ENT_QUOTES) ?></td>
            <td class="text-slate-300 clamp-2"><?= htmlspecialchars((string) ($template["content"] ?? ""), ENT_QUOTES) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($templates)): ?>
          <tr class="border-t border-slate-800">
            <td class="py-4 text-slate-400" colspan="2">No templates yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
