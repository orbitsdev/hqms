# Laravel Reverb Setup Guide

A step-by-step guide to set up Laravel Reverb (WebSockets) for real-time features in local development.

---

## 1. Installation

```bash
# Install Reverb
php artisan install:broadcasting

# This will:
# - Install laravel/reverb package
# - Create config/reverb.php
# - Create routes/channels.php
# - Install frontend dependencies (laravel-echo, pusher-js)
```

Or manually:

```bash
composer require laravel/reverb
php artisan reverb:install
npm install --save-dev laravel-echo pusher-js
```

---

## 2. Environment Configuration (.env)

```env
# Broadcasting driver
BROADCAST_CONNECTION=reverb

# Laravel Reverb (WebSockets)
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

# Vite (Frontend) - These are exposed to JavaScript
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="127.0.0.1"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

**Important Notes:**
- Use `127.0.0.1` instead of `localhost` to avoid DNS issues
- `VITE_*` variables are exposed to frontend JavaScript
- After changing `.env`, restart Vite (`npm run dev`)

---

## 3. Frontend Setup (resources/js/echo.js)

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

Import in `resources/js/app.js`:

```javascript
import './echo';
```

---

## 4. Create a Broadcast Event

```bash
php artisan make:event OrderUpdated
```

```php
<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $action = 'updated'
    ) {}

    // Public channel (anyone can listen)
    public function broadcastOn(): array
    {
        return [
            new Channel('orders'),
        ];
    }

    // Or Private channel (requires auth)
    // public function broadcastOn(): array
    // {
    //     return [
    //         new PrivateChannel('orders.' . $this->order->user_id),
    //     ];
    // }

    // Data to broadcast
    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'order' => [
                'id' => $this->order->id,
                'status' => $this->order->status,
            ],
        ];
    }

    // Custom event name (optional)
    public function broadcastAs(): string
    {
        return 'order.updated';
    }
}
```

**Important:** Avoid naming properties `$queue` - it conflicts with Laravel's job queue system.

---

## 5. Define Channel Authorization (routes/channels.php)

```php
<?php

use Illuminate\Support\Facades\Broadcast;

// Public channel - return true
Broadcast::channel('orders', function () {
    return true;
});

// Private channel - check user authorization
Broadcast::channel('orders.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Private channel with role check
Broadcast::channel('admin.notifications', function ($user) {
    return $user->hasRole('admin');
});
```

---

## 6. Dispatch Events

```php
// In a Controller or anywhere
use App\Events\OrderUpdated;

// Dispatch the event
event(new OrderUpdated($order, 'created'));

// Or use broadcast() helper
broadcast(new OrderUpdated($order, 'updated'));

// Broadcast to others (exclude current user)
broadcast(new OrderUpdated($order))->toOthers();
```

---

## 7. Listen in Livewire Components

```php
<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class OrderList extends Component
{
    // Listen to public channel
    #[On('echo:orders,order.updated')]
    public function handleOrderUpdated($event): void
    {
        // $event contains broadcastWith() data
        // Component will re-render automatically
    }

    // Listen to private channel
    #[On('echo-private:orders.{userId},order.updated')]
    public function handlePrivateOrderUpdated($event): void
    {
        // Handle private channel event
    }

    // With dynamic channel parameter from component property
    public int $userId;

    #[On('echo-private:orders.{userId},order.updated')]
    public function handleUserOrder($event): void
    {
        // Listens to orders.{$this->userId}
    }
}
```

---

## 8. Listen in JavaScript (Non-Livewire)

```javascript
// Public channel
Echo.channel('orders')
    .listen('.order.updated', (event) => {
        console.log('Order updated:', event);
    });

// Private channel (requires auth)
Echo.private('orders.1')
    .listen('.order.updated', (event) => {
        console.log('Private order updated:', event);
    });

// Presence channel (shows who's online)
Echo.join('chat.room.1')
    .here((users) => {
        console.log('Users in room:', users);
    })
    .joining((user) => {
        console.log('User joined:', user);
    })
    .leaving((user) => {
        console.log('User left:', user);
    });
```

---

## 9. Running Reverb (Development)

```bash
# Terminal 1: Laravel app (or use Herd/Valet)
php artisan serve

# Terminal 2: Queue worker (processes broadcast jobs)
php artisan queue:listen

# Terminal 3: Reverb WebSocket server
php artisan reverb:start

# Terminal 4: Vite dev server
npm run dev
```

Or use a single command with `composer.json`:

```json
{
    "scripts": {
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"php artisan reverb:start\" \"npm run dev\" --names=server,queue,reverb,vite"
        ]
    }
}
```

Then just run:
```bash
composer run dev
```

---

## 10. Reverb with Debug Output

```bash
# Show debug info
php artisan reverb:start --debug

# Custom host/port
php artisan reverb:start --host=0.0.0.0 --port=8080
```

---

## 11. Troubleshooting

### WebSocket Connection Failed

1. **Check Reverb is running:**
   ```bash
   php artisan reverb:start
   ```
   Should show: `Starting server on 127.0.0.1:8080`

2. **Check .env configuration:**
   ```env
   BROADCAST_CONNECTION=reverb
   REVERB_HOST=127.0.0.1
   VITE_REVERB_HOST="127.0.0.1"
   ```

3. **Clear config cache:**
   ```bash
   php artisan config:clear
   ```

4. **Restart Vite** (required after .env changes):
   ```bash
   # Stop npm run dev, then restart
   npm run dev
   ```

### Events Not Broadcasting

1. **Check queue is running:**
   ```bash
   php artisan queue:listen
   ```

2. **Check event implements ShouldBroadcast:**
   ```php
   class MyEvent implements ShouldBroadcast
   ```

3. **Check channel authorization** in `routes/channels.php`

4. **Check browser console** for WebSocket errors

### Property Name Conflict

Don't name event properties `$queue` - it conflicts with Laravel's job queue:

```php
// BAD - causes issues
public Queue $queue;

// GOOD - rename it
public Queue $queueEntry;
```

---

## 12. Production Deployment

For production, use a process manager like Supervisor:

```ini
# /etc/supervisor/conf.d/reverb.conf
[program:reverb]
command=php /var/www/html/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/var/www/html
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/reverb.log
```

Update `.env` for production:
```env
REVERB_HOST=your-domain.com
REVERB_SCHEME=https
VITE_REVERB_HOST="your-domain.com"
VITE_REVERB_SCHEME="https"
```

---

## Quick Reference

| Command | Purpose |
|---------|---------|
| `php artisan reverb:start` | Start WebSocket server |
| `php artisan reverb:start --debug` | Start with debug output |
| `php artisan queue:listen` | Process broadcast jobs |
| `php artisan config:clear` | Clear cached config |
| `php artisan make:event EventName` | Create new event |

| Channel Type | Syntax | Use Case |
|--------------|--------|----------|
| Public | `new Channel('name')` | Anyone can listen |
| Private | `new PrivateChannel('name')` | Auth required |
| Presence | `new PresenceChannel('name')` | Track who's online |

---

## Resources

- [Laravel Reverb Docs](https://laravel.com/docs/reverb)
- [Laravel Broadcasting Docs](https://laravel.com/docs/broadcasting)
- [Livewire Events](https://livewire.laravel.com/docs/events)
