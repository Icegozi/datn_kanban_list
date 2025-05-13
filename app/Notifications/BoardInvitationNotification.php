<?php

namespace App\Notifications;

use App\Models\BoardInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use URL;

class BoardInvitationNotification extends Notification
{
    use Queueable;

    public BoardInvitation $invitation;

    /**
     * Create a new notification instance.
     */
    public function __construct(BoardInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $acceptUrl = URL::temporarySignedRoute(
            'invitations.accept',
            now()->addDays(7),
            ['token' => $this->invitation->token]
        );
        $this->invitation->loadMissing(['board', 'inviter']);

        $boardName = $this->invitation->board->name;
        $inviterName = $this->invitation->inviter->name;
        $roleDisplayName = $this->invitation->role_permission_name
            ? ucfirst(str_replace('_', ' ', $this->invitation->role_permission_name))
            : 'Member';

        return (new MailMessage)
            ->subject("Lời mời tham gia bảng: {$boardName}")
            ->greeting("Chào bạn,")
            ->line("{$inviterName} đã mời bạn tham gia vào bảng '{$boardName}' với vai trò '{$roleDisplayName}'.")
            ->action('Chấp nhận lời mời', $acceptUrl)
            ->line("Nếu bạn không thực hiện yêu cầu này, bạn có thể bỏ qua email này.")
            ->salutation("Trân trọng,\n" . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'board_id' => $this->invitation->board_id,
            'board_name' => $this->invitation->board->name,
            'message' => "Bạn có lời mời tham gia bảng {$this->invitation->board->name}."
        ];
    }
}
