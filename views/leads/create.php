<section class="glass panel" data-reveal>
  <div class="section-title text-2xl">New Lead</div>
  <p class="text-slate-300 text-sm mt-1">Fill in the lead details.</p>

  <div class="mt-6">
    <?php
      $action = "/leads";
      $submitLabel = "Create Lead";
      require __DIR__ . "/form.php";
    ?>
  </div>
  <div class="mt-4">
    <a href="/leads" class="btn btn-secondary text-sm">Back</a>
  </div>
</section>
