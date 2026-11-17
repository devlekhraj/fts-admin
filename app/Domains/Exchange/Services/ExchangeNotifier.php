<?php

declare(strict_types=1);

namespace App\Domains\Exchange\Services;

use App\Domains\Exchange\Models\ExchangeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails about trade-in requests: one digest to the team for new requests, and a message to the customer
 * when a request is approved, rejected or completed. Email problems are logged and never stop the admin's work.
 */
final class ExchangeNotifier
{
    private const MAX_PER_DIGEST = 25;

    /** Where new-request emails go: EXCHANGE_NOTIFY_EMAIL, otherwise the company mailbox the site already sends from. */
    public function teamAddress(): ?string
    {
        $address = (string) (config('services.exchange.notify_email') ?: config('mail.from.address'));

        return filter_var($address, FILTER_VALIDATE_EMAIL) ? $address : null;
    }

    /** Emails the team about requests it has not been told about yet. Returns how many were included. */
    public function notifyTeamOfNewRequests(): int
    {
        $to = $this->teamAddress();
        if ($to === null) {
            Log::warning('Exchange: no valid team email address, new requests were not announced.');

            return 0;
        }

        $rows = ExchangeRequest::query()
            ->select(ExchangeRequest::READABLE)
            ->whereNull('team_notified_at')
            ->where('status', ExchangeRequest::STATUS_PENDING)
            ->orderBy('id')
            ->limit(self::MAX_PER_DIGEST)
            ->get();

        if ($rows->isEmpty()) {
            return 0;
        }

        $lines = [];
        foreach ($rows as $r) {
            $manual = (bool) data_get($r->estimate_breakdown, 'needs_manual_review', true);
            $lines[] = '<li><strong>' . e($r->reference) . '</strong> &ndash; ' . e($r->device_name)
                . ' (IMEI ' . e($r->serial_number) . ')<br>'
                . e($r->customer_name) . ', ' . e($r->customer_phone) . '<br>'
                . 'Customer estimate: Rs ' . number_format((float) $r->customer_estimate)
                . ($manual ? ' &ndash; <em>check this phone in person</em>' : '') . '<br>'
                . ($r->target_name ? 'Wants to buy: ' . e($r->target_name) . '<br>' : '')
                . '<a href="' . e($this->adminLink($r->id)) . '">Open the request</a></li>';
        }

        $count = $rows->count();
        $subject = $count === 1
            ? 'New exchange request ' . $rows->first()->reference . ' - ' . $rows->first()->device_name
            : $count . ' new exchange requests';
        $html = '<p>' . ($count === 1 ? 'A customer sent a new exchange request.' : 'Customers sent ' . $count . ' new exchange requests.')
            . '</p><ul>' . implode('', $lines) . '</ul>'
            . '<p>Review them in the admin under Exchange Requests.</p>';

        try {
            Mail::send([], [], function ($message) use ($to, $subject, $html): void {
                $message->to($to)->subject($subject)->html($html);
            });
        } catch (Throwable $e) {
            // team_notified_at stays empty, so the next run tries again.
            Log::error('Exchange: could not email the team about new requests.', ['error' => $e->getMessage()]);

            return 0;
        }

        ExchangeRequest::query()->whereIn('id', $rows->pluck('id'))->update(['team_notified_at' => now()]);

        return $count;
    }

    /**
     * Tells the customer about the decision. Only approved, rejected and completed are worth an email.
     * $toOverride is for tests, so a real customer is never emailed by mistake.
     */
    public function notifyCustomerOfDecision(int $requestId, string $status, ?string $toOverride = null): bool
    {
        if (! in_array($status, [ExchangeRequest::STATUS_APPROVED, ExchangeRequest::STATUS_REJECTED, ExchangeRequest::STATUS_COMPLETED], true)) {
            return false;
        }

        try {
            $r = ExchangeRequest::query()->select(ExchangeRequest::READABLE)->find($requestId);
            if (! $r) {
                return false;
            }

            $to = $toOverride ?? ($r->user_id ? DB::table('users')->where('id', $r->user_id)->value('email') : null);
            if (! is_string($to) || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            $name = e(trim((string) $r->customer_name) ?: 'there');
            $ref = e($r->reference);
            $device = e($r->device_name);
            $note = trim((string) $r->customer_note);
            $noteHtml = $note !== '' ? '<p>' . nl2br(e($note)) . '</p>' : '';

            if ($status === ExchangeRequest::STATUS_APPROVED) {
                $subject = "Your exchange request {$r->reference} is approved";
                $offer = $r->admin_offer !== null ? '<p>Our offer for your ' . $device . ' is <strong>Rs ' . number_format((float) $r->admin_offer) . '</strong>.</p>' : '';
                $body = "<p>Hello {$name},</p><p>Good news: your exchange request <strong>{$ref}</strong> has been approved.</p>{$offer}{$noteHtml}"
                    . '<p>Our team will contact you to arrange the next step.</p>';
            } elseif ($status === ExchangeRequest::STATUS_REJECTED) {
                $subject = "About your exchange request {$r->reference}";
                $body = "<p>Hello {$name},</p><p>We are sorry, we could not go ahead with your exchange request <strong>{$ref}</strong> for the {$device}.</p>"
                    . ($noteHtml !== '' ? '<p>Reason from our team:</p>' . $noteHtml : '')
                    . '<p>You are welcome to contact us or send a new request.</p>';
            } else {
                $subject = "Your exchange {$r->reference} is complete";
                $body = "<p>Hello {$name},</p><p>Your exchange <strong>{$ref}</strong> is complete. Thank you for choosing Fatafat Sewa.</p>";
            }

            Mail::send([], [], function ($message) use ($to, $subject, $body): void {
                $message->to($to)->subject($subject)->html($body . '<p>Fatafat Sewa</p>');
            });

            return true;
        } catch (Throwable $e) {
            Log::error('Exchange: could not email the customer about the decision.', ['exchange_request_id' => $requestId, 'error' => $e->getMessage()]);

            return false;
        }
    }

    private function adminLink(int $id): string
    {
        return rtrim((string) config('app.url'), '/') . '/admin/exchange-requests/' . $id;
    }
}
