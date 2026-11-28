---
paths:
  - 'app/Console/Commands/**'
---

# Commands

## Keep shared-hosting queue workers bounded
For shared-hosting deployments, drain queues through the Laravel scheduler rather than a persistent daemon or an unbounded web request. Keep the queue command bounded with stop-when-empty, max-jobs, and max-time; the admin action processes at most one job. Configure cPanel Cron to run `php artisan schedule:run` once per minute.
