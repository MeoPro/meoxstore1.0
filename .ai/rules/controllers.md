---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Retain QR images referenced by orders
Orders snapshot the public QR image path in `payment_details`. Keep replaced/deleted payment QR files available so existing pending and historical orders can still display their original payment instructions.
