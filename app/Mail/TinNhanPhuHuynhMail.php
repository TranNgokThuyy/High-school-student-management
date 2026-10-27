<?php
namespace App\Mail;

use App\Models\TinNhanNguoiNhan;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TinNhanPhuHuynhMail extends Mailable
{
    public function __construct(public TinNhanNguoiNhan $nguoiNhan) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.ten_truong') . '] ' . $this->nguoiNhan->tinNhan->tieu_de,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.tin-nhan-phu-huynh');
    }
}
