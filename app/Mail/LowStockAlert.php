<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockAlert extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Items grouped by warehouse name.
     * Format: ['Gudang X' => [item1, item2], ...]
     */
    public Collection $groupedItems;

    /**
     * Total number of low stock items.
     */
    public int $totalItems;

    /**
     * Date of the report.
     */
    public string $reportDate;

    /**
     * Create a new message instance.
     */
    public function __construct(Collection $groupedItems, int $totalItems)
    {
        $this->groupedItems = $groupedItems;
        $this->totalItems = $totalItems;
        $this->reportDate = now()->translatedFormat('l, d F Y');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠️ Laporan Stok Rendah - {$this->reportDate}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.low-stock-alert',
        );
    }
}
