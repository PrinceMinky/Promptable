<?php

declare(strict_types=1);

namespace Princeminky\Promptable\Traits;

use Flux\Flux;
use Throwable;

/**
 * Trait Promptable
 *
 * Provides a reusable confirmation prompt mechanism that halts execution on the
 * first call (similar to `$this->validate()`) and then resumes the original
 * Livewire action after the user confirms.
 */
trait Promptable
{
    /**
     * Default prompt content values. Needed on reset.
     */
    protected string $promptDefaultQuestion = 'Are you sure you wish to proceed?';

    protected ?string $promptDefaultText = null;

    protected string $promptDefaultCancelText = 'Cancel';

    protected string $promptDefaultConfirmText = 'Delete';

    /**
     * The question displayed to the user in the confirmation modal.
     */
    public string $promptQuestion = 'Are you sure you wish to proceed?';

    /**
     * Optional body text displayed under the heading in the modal.
     */
    public ?string $promptText = "Changes channot be undone.";

    /**
     * Button labels.
     */
    public string $promptCancelText = 'Cancel';

    public string $promptConfirmText = 'Delete';

    /**
     * Optional confirmation text that a user will need to type
     * Otherwise confirm button is disabled.
     */
    public ?string $promptConfirmWord = null;

    /**
     * The user's current input for confirmation when a confirm word is required.
     * Bound to the input field when the modal is open.
     */
    public ?string $promptConfirmation = null;

    /**
     * Pending action metadata captured at the time the prompt is opened.
     *
     * Array shape: ['method' => string|null, 'args' => array]
     *
     * @var array<string, mixed>|null
     */
    public ?array $promptPendingAction = null;

    /**
     * Internal flag indicating that the original method is being resumed after
     * a confirmation. Used to prevent re-opening the prompt on resume.
     */
    public bool $promptResuming = false;

    /**
     * The Flux modal name that should be used for the confirmation dialog.
     */
    protected string $promptModalName = 'promptable';

    /**
     * Opens the confirmation prompt. On the first call, this method throws a
     * lightweight internal exception to halt the current Livewire action and
     * show the modal. When the action is resumed (after the user confirms),
     * calling this method will immediately return without re-opening the modal.
     *
     * Only the bare-call halting syntax is supported:
     * `$this->prompt('Are you sure?');`.
     *
     * @param string $question The question to present to the user.
     * @param string|null $text Optional body text under the heading.
     * @param string|null $cancelText Optional cancel button label.
     * @param string|null $confirmText Optional confirm/submit button label.
     * @param string|null $confirmWord Optionally confirm word
     */
    public function prompt(string $question, ?string $text = null, ?string $cancelText = null, ?string $confirmText = null, ?string $confirmWord = null): void
    {
        $this->promptQuestion = $question;
        if ($text !== null) {
            $this->promptText = $text;
        }
        if ($cancelText !== null) {
            $this->promptCancelText = $cancelText;
        }
        if ($confirmText !== null) {
            $this->promptConfirmText = $confirmText;
        }
        if ($confirmWord !== null) {
            $this->promptConfirmWord = $confirmWord;
        }

        // Always reset the user's input when opening the prompt
        $this->promptConfirmation = null;

        // If we're resuming the original method after confirmation, do nothing.
        if ($this->promptResuming) {
            $this->promptResuming = false;

            return;
        }

        // Capture caller method and its arguments.
        $trace = debug_backtrace(0, 2);
        $caller = $trace[1] ?? [];
        $method = $caller['function'] ?? null;
        $resolvedArgs = $caller['args'] ?? [];

        $this->promptPendingAction = [
            'method' => $method,
            'args' => $resolvedArgs,
        ];

        Flux::modal($this->promptModalName)->show();

        // Throw a specialized exception to halt execution. This is intercepted
        // by Livewire via the lifecycle `exception` hook below.
        throw new PromptHaltException('Prompt opened; halting action until user responds.');
    }

    /**
     * Called when the user confirms the prompt.
     */
    public function promptConfirm(): void
    {
        Flux::modal($this->promptModalName)->close();

        if (! $this->promptPendingAction) {
            $this->resetPromptState();

            return;
        }

        $action = $this->promptPendingAction;
        $this->promptPendingAction = null;
        $this->resetPromptState();

        $method = $action['method'];
        $args = $action['args'];

        if ($method && is_callable([$this, $method])) {
            $this->promptResuming = true;
            $this->{$method}(...$args);
            $this->promptResuming = false;
        }
    }

    /**
     * Reset prompt values back to defaults.
     */
    protected function resetPromptState(): void
    {
        $this->promptQuestion = $this->promptDefaultQuestion;
        $this->promptText = $this->promptDefaultText;
        $this->promptCancelText = $this->promptDefaultCancelText;
        $this->promptConfirmText = $this->promptDefaultConfirmText;
        $this->promptConfirmWord = null;
        $this->promptConfirmation = null;
    }

    /**
     * Livewire lifecycle hook to intercept the internal halt exception and stop
     * propagation without surfacing an error to the UI.
     *
     * @param  callable(Throwable): void  $stopPropagation
     */
    public function exception(Throwable $e, callable $stopPropagation): void
    {
        if ($e instanceof PromptHaltException) {
            $stopPropagation();
        }
    }
}

/**
 * Internal exception used to halt a Livewire action when opening a prompt.
 */
class PromptHaltException extends \RuntimeException {}
