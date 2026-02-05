<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SaaS Leads</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/app.css" />
</head>
<body class="min-h-screen text-slate-100 antialiased">
  <div class="fixed inset-0 bg-grid"></div>
  <div class="fixed -top-20 -right-20 w-[420px] h-[420px] rounded-full bg-emerald-500/20 blur-3xl"></div>
  <div class="fixed bottom-0 -left-10 w-[360px] h-[360px] rounded-full bg-blue-500/20 blur-3xl"></div>

  <div class="relative min-h-screen">
    <div class="lg:grid lg:grid-cols-[260px_1fr] min-h-screen">
      <aside class="hidden lg:flex flex-col justify-between px-6 py-8">
        <div class="glass panel">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-blue-500/20 flex items-center justify-center text-blue-200 font-semibold">SL</div>
            <div>
              <div class="text-lg font-semibold section-title">SaaS Leads</div>
              <div class="text-xs text-slate-400">Prospecting OS</div>
            </div>
          </div>

          <nav class="mt-6 space-y-2 text-sm">
            <a href="/" class="nav-link">Dashboard</a>
            <a href="/projects" class="nav-link">Projects</a>
            <a href="/leads" class="nav-link">Leads</a>
            <a href="/templates/whatsapp" class="nav-link">Templates</a>
            <a href="/leads/search" class="nav-link">Maps Search</a>
            <a href="/services" class="nav-link">Services</a>
            <a href="/proposals" class="nav-link">Proposals</a>
          </nav>
        </div>

        <div class="glass panel text-sm text-slate-300">
          <div class="text-xs text-slate-400">Today</div>
          <div class="text-lg font-semibold mt-1">Keep pipeline moving.</div>
          <a href="/leads/new" class="btn btn-primary mt-4 text-sm">New Lead</a>
        </div>
      </aside>

      <main class="px-6 py-8 lg:px-10 lg:py-10">
        <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
          <div>
            <div class="section-title text-2xl">SaaS Leads</div>
            <p class="text-sm text-slate-400">Prospect, manage, and close faster.</p>
          </div>
          <div class="flex flex-wrap gap-2">
            <a href="/leads/new" class="btn btn-primary text-sm">New Lead</a>
            <a href="/leads" class="btn btn-secondary text-sm">Open CRM</a>
          </div>
        </header>

        <?= $content ?? "" ?>

        <nav class="lg:hidden mt-8 grid grid-cols-2 gap-3 text-sm">
          <a href="/" class="nav-link glass">Dashboard</a>
          <a href="/projects" class="nav-link glass">Projects</a>
          <a href="/leads" class="nav-link glass">Leads</a>
          <a href="/templates/whatsapp" class="nav-link glass">Templates</a>
          <a href="/leads/search" class="nav-link glass">Maps Search</a>
          <a href="/services" class="nav-link glass">Services</a>
          <a href="/proposals" class="nav-link glass">Proposals</a>
        </nav>
      </main>
    </div>
  </div>
  <script src="/assets/app.js"></script>
</body>
</html>
