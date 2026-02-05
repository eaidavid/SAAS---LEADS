<section class="glass panel" data-reveal>
  <div class="flex items-center justify-between">
    <div>
      <div class="section-title text-2xl">Edit Lead</div>
      <p class="text-slate-300 text-sm mt-1">Update lead details and status.</p>
    </div>
    <a href="/leads/show?id=<?= (int) ($lead["id"] ?? 0) ?>" class="text-sm text-blue-300 hover:text-blue-200">View</a>
  </div>

  <div class="mt-6">
    <?php
      $action = "/leads/update";
      $submitLabel = "Save Changes";
      require __DIR__ . "/form.php";
    ?>
  </div>
  <div class="mt-4">
    <a href="/leads/show?id=<?= (int) ($lead["id"] ?? 0) ?>" class="btn btn-secondary text-sm">Back</a>
  </div>
</section>
