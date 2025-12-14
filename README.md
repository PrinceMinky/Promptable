# Promptable

One line of code is all you need to add safe, user-friendly confirmation prompts to your Livewire actions — with beautiful Flux UI modals.

`princeminky/promptable` provides a small `Promptable` trait you drop into any Livewire v3 component. Call `$this->prompt(...)` inside an action to halt execution, show a confirmation modal, and then automatically resume the original action after the user confirms.

---

## Requirements

- PHP >= 8.3
- Laravel 12
- Livewire 3
- Flux UI (free or Pro) v2 — for the modal and buttons used by the included view
- Tailwind CSS v4

These match the constraints declared in `composer.json`.

---

## Installation

### 1) Install the package

If published to Packagist:

```bash
composer require princeminky/promptable
```

If using this package locally via a path repository (monorepo / workbench):

1) In your application `composer.json`, add a path repository (adjust the path to your environment):

```json
{
  "repositories": [
    { "type": "path", "url": "packages/princeminky/promptable" }
  ]
}
```

2) Require the package:

```bash
composer require princeminky/promptable:* --dev
```

Auto-discovery will register the service provider and alias.

### 2) Include the modal once in your app layout

Add the Blade directive to your base layout so the modal HTML is present on every page. Put this near the end of the `<body>`:

```blade
{{-- resources/views/components/layouts/app.blade.php --}}
@promptable
```

The directive renders the bundled Flux modal view `promptable::prompt`.

### 3) (Optional) Publish the view for customization

```bash
php artisan vendor:publish --tag=promptable-views --no-interaction
```

This will copy:

```
resources/views/vendor/promptable/prompt.blade.php
```

Edit the published view to fully customize the modal.

---

## Quick Start

1) Use the trait in a Livewire component.
2) Call `$this->prompt(...)` inside any action. Execution halts, the modal opens, and when the user confirms, the original action resumes from the next line.

```php
<?php

namespace App\Livewire;

use Flux\Flux;
use Livewire\Component;
use Princeminky\Promptable\Traits\Promptable;

class DeletePost extends Component
{
    use Promptable;

    public function destroy(int $postId): void
    {
        // Open a confirmation first — this halts the action here
        $this->prompt(
            question: 'Delete this post?',
            text: "This action cannot be undone.",
            cancelText: 'Cancel',
            confirmText: 'Delete',
        );

        // When the user confirms, execution resumes here
        // Post::findOrFail($postId)->delete();
        Flux::toast('Post deleted');
    }

    public function render()
    {
        return view('livewire.delete-post');
    }
}
```

---

## Options

`$this->prompt(...)` supports the following named parameters:

```php
$this->prompt(
    question: 'Are you sure?',          // required
    text: 'Optional help text',          // optional
    cancelText: 'Cancel',                // optional
    confirmText: 'Proceed',              // optional
    confirmWord: 'DELETE',               // optional — require typing this word
);
```

If `confirmWord` is provided, the confirm button is disabled until the user types the exact word (case-sensitive by default) into the input field.

---

## Real-world examples

### Basic delete confirmation

```php
public function delete(int $id): void
{
    $this->prompt(
        question: 'Are you sure you wish to delete this record?',
        text: 'All data will be permanently removed.',
        confirmText: 'Yes, delete',
    );

    // Delete, then notify
    // Model::findOrFail($id)->delete();
    Flux::toast('Deleted!');
}
```

### Destructive action requiring a typed confirmation

```php
public function wipe(): void
{
    $this->prompt(
        question: 'Wipe all demo data?',
        text: "This cannot be undone.",
        confirmText: 'Wipe data',
        confirmWord: 'WIPE',
    );

    // do the destructive work...
    Flux::toast('All demo data wiped');
}
```

### Customizing the modal name

If you need a separate modal instance or want to avoid name collisions, override the protected property from the trait in your component:

```php
protected string $promptModalName = 'my-custom-prompt';
```

Be sure to publish and update the view if you want the directive-rendered modal to reference the same name.

---

## How it works

- The `Promptable` trait stores the calling method and arguments using `debug_backtrace()` when you call `$this->prompt(...)`.
- It opens a Flux modal and throws a lightweight internal exception to halt the Livewire lifecycle without surfacing an error.
- When the user clicks confirm, the trait closes the modal and re-invokes the original method with the captured arguments, skipping the prompt the second time and resuming after your `$this->prompt(...)` line.

This pattern is similar to `$this->validate()` in that it can short-circuit the first run of the method.

---

## Blade directive

A Blade directive `@promptable` is registered by the service provider and simply renders the package view:

```php
Blade::directive('promptable', fn () => "<?php echo view('promptable::prompt')->render(); ?>");
```

You should include `@promptable` once per page, typically in your base layout.

---

## Styling & UI

- The bundled view uses Flux UI components: `<flux:modal>`, `<flux:button>`, `<flux:heading>`, `<flux:input>`, `<flux:text>`.
- Ensure Flux UI is installed and available in your project.
- Tailwind CSS v4 is expected and used by Flux.

If you don’t see the modal or styles:
- Confirm `@promptable` exists in your layout.
- Make sure your frontend is built: `npm run dev` or `npm run build`.

---

## Publishing

Currently only the views are publishable:

```bash
php artisan vendor:publish --tag=promptable-views --no-interaction
```

---

## Troubleshooting

- Modal not showing: verify `@promptable` is present in the rendered HTML and Livewire is working.
- Confirm button disabled: if you set `confirmWord`, the user must type the exact word.
- Vite/Tailwind assets missing: run `npm run dev` (or `npm run build`) and reload.

---

## License

MIT License. See the `LICENSE` file if present.

---

## Credits

Built by Prince Minky.
