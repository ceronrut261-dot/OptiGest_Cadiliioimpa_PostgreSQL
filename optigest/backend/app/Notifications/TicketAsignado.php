<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Correo que recibe un técnico cuando se le asigna un ticket
 * (ya sea al crearlo o al reasignarlo desde uno existente).
 * Se envía de forma inmediata (no encolada) para no depender
 * de tener un worker de colas corriendo.
 */
class TicketAsignado extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket->loadMissing('cliente');

        return (new MailMessage)
            ->subject("Nuevo ticket asignado: {$ticket->codigo}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Se te ha asignado el ticket **{$ticket->codigo}**.")
            ->line("Cliente: {$ticket->cliente->nombre}")
            ->line("Prioridad: ".ucfirst($ticket->prioridad))
            ->line("Descripción: {$ticket->descripcion}")
            ->when($ticket->fecha_programada, function ($mail) use ($ticket) {
                return $mail->line("Fecha programada: ".$ticket->fecha_programada->format('d/m/Y H:i'));
            })
            ->action('Ver ticket', route('tickets.show', $ticket))
            ->line('Ingresa a OptiGest para más detalles.');
    }
}