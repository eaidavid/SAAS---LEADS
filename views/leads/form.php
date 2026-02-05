<form method="post" action="<?= htmlspecialchars($action ?? "/leads", ENT_QUOTES) ?>" class="space-y-6">
  <?php if (!empty($lead["id"])): ?>
    <input type="hidden" name="id" value="<?= (int) $lead["id"] ?>" />
  <?php endif; ?>

  <div class="grid md:grid-cols-2 gap-4">
    <div>
      <label class="text-sm text-slate-400">Name</label>
      <input type="text" name="name" required value="<?= htmlspecialchars((string) ($lead["name"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-sm text-slate-400">Status</label>
      <select name="status" class="select mt-2">
        <?php foreach (($statuses ?? []) as $value => $label): ?>
          <option value="<?= htmlspecialchars($value, ENT_QUOTES) ?>" <?= ($lead["status"] ?? "new") === $value ? "selected" : "" ?>>
            <?= htmlspecialchars($label, ENT_QUOTES) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-sm text-slate-400">Phone</label>
      <input type="text" name="phone" value="<?= htmlspecialchars((string) ($lead["phone"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-sm text-slate-400">Mobile</label>
      <input type="text" name="mobile" value="<?= htmlspecialchars((string) ($lead["mobile"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-sm text-slate-400">Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars((string) ($lead["email"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-sm text-slate-400">Website</label>
      <input type="text" name="website" value="<?= htmlspecialchars((string) ($lead["website"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-sm text-slate-400">Category</label>
      <input type="text" name="category" value="<?= htmlspecialchars((string) ($lead["category"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div class="md:col-span-2">
      <label class="text-sm text-slate-400">Address</label>
      <input type="text" name="address" value="<?= htmlspecialchars((string) ($lead["address"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-sm text-slate-400">City</label>
      <input type="text" name="city" value="<?= htmlspecialchars((string) ($lead["city"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-sm text-slate-400">State</label>
      <input type="text" name="state" value="<?= htmlspecialchars((string) ($lead["state"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-sm text-slate-400">Rating</label>
      <input type="number" step="0.01" name="rating" value="<?= htmlspecialchars((string) ($lead["rating"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-sm text-slate-400">Reviews</label>
      <input type="number" name="reviews_count" value="<?= htmlspecialchars((string) ($lead["reviews_count"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-sm text-slate-400">Score</label>
      <input type="number" name="score" value="<?= htmlspecialchars((string) ($lead["score"] ?? "0"), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
    <div>
      <label class="text-sm text-slate-400">Tags (comma separated)</label>
      <input type="text" name="tags" value="<?= htmlspecialchars((string) ($lead["tags"] ?? ""), ENT_QUOTES) ?>" class="input mt-2" />
    </div>
  </div>

  <div>
    <label class="text-sm text-slate-400">Notes</label>
    <textarea name="notes" rows="4" class="textarea mt-2"><?= htmlspecialchars((string) ($lead["notes"] ?? ""), ENT_QUOTES) ?></textarea>
  </div>

  <div class="flex items-center gap-3">
    <button class="btn btn-primary">
      <?= htmlspecialchars($submitLabel ?? "Save", ENT_QUOTES) ?>
    </button>
    <a href="/leads" class="text-slate-300 hover:text-blue-300 text-sm">Back</a>
  </div>
</form>
