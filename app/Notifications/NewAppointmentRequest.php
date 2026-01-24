<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewAppointmentRequest extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
      public function __construct(public Appointment $appointment) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
   public function via($notifiable): array
    {
        return ['database', 'broadcast']; // database saves, broadcast realtime
    }

    /**
     * Get the mail representation of the notification.
     */


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'type' => $this->appointment->consultation_type_id,
            'date' => $this->appointment->appointment_date,
            'patient' => $this->appointment->patient_first_name.' '.$this->appointment->patient_last_name,
            'status' => $this->appointment->status,
        ];
    }   
}
