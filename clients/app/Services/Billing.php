<?php

namespace App\Services;

use App\Config\Env;
use DateTimeZone;

/**
 * Turns a project document into everything a bill needs: totals, tax, the
 * part-payment ledger with a running balance, and document numbers.
 *
 * Money is stored on the project as the agreed project value (subtotal). Tax is
 * an optional percentage; part payments are recorded against the grand total.
 */
final class Billing
{
    /** Company block printed at the top of every bill. */
    public static function company(): array
    {
        return [
            'name' => (string) Env::get('BILLING_NAME', (string) Env::get('APP_NAME', 'Unnat Technology Services')),
            'address' => (string) Env::get('BILLING_ADDRESS', 'Buddhi Vihar, Delhi Road, Moradabad, Uttar Pradesh 244001, India'),
            'email' => (string) Env::get('BILLING_EMAIL', (string) Env::get('MAIL_FROM', 'unnattechnologyservices@gmail.com')),
            'phone' => (string) Env::get('BILLING_PHONE', '+91 96908 05228'),
            'website' => (string) Env::get('BILLING_WEBSITE', 'https://unnattechnologyservices.com'),
            'tax_id' => (string) Env::get('BILLING_TAX_ID', ''),
            'tax_label' => (string) Env::get('BILLING_TAX_LABEL', 'GST'),
            'bank' => (string) Env::get('BILLING_BANK_DETAILS', ''),
            'terms' => (string) Env::get('BILLING_TERMS', 'Payment is due within 7 days of the invoice date. Part payments are receipted individually.'),
        ];
    }

    public static function currency(): string
    {
        return (string) Env::get('BILLING_CURRENCY_SYMBOL', '₹');
    }

    public static function money(float $amount): string
    {
        return self::currency() . ' ' . number_format($amount, 2);
    }

    /**
     * Full financial picture for one project.
     *
     * @return array{
     *   subtotal:float, tax_percent:float, tax_amount:float, grand_total:float,
     *   paid:float, balance:float, payments:array<int,array<string,mixed>>,
     *   is_settled:bool, invoice_number:string, invoice_date:string
     * }
     */
    public static function summary(object $project): array
    {
        $subtotal = (float) ($project->total_payment ?? 0);
        $taxPercent = (float) ($project->tax_percent ?? 0);
        $taxAmount = round($subtotal * $taxPercent / 100, 2);
        $grandTotal = round($subtotal + $taxAmount, 2);

        $payments = self::payments($project);
        $paid = 0.0;
        foreach ($payments as $payment) {
            $paid += (float) $payment['amount'];
        }
        $paid = round($paid, 2);

        return [
            'subtotal' => $subtotal,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'paid' => $paid,
            'balance' => round(max(0, $grandTotal - $paid), 2),
            'payments' => $payments,
            'is_settled' => $grandTotal > 0 && $paid >= $grandTotal,
            'invoice_number' => self::invoiceNumber($project),
            'invoice_date' => date('d M Y'),
        ];
    }

    /**
     * Normalised part payments, oldest first, each with its receipt number and
     * the balance that remained after it was applied.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function payments(object $project): array
    {
        $rows = [];
        foreach ((array) ($project->part_payments ?? []) as $index => $payment) {
            $get = static function (string $key, mixed $default = '') use ($payment): mixed {
                if (is_object($payment)) {
                    return $payment->{$key} ?? $default;
                }
                return $payment[$key] ?? $default;
            };

            $paidAt = $get('payment_at', null);
            $rows[] = [
                'id' => (string) $get('id', 'p' . $index),
                'sequence' => $index + 1,
                'amount' => (float) $get('amount', 0),
                'paid_at' => $paidAt,
                'paid_at_text' => $paidAt ? self::formatDate($paidAt, 'd M Y, h:i A') : '—',
                'paid_on_text' => $paidAt ? self::formatDate($paidAt, 'd M Y') : '—',
                'statement' => (string) $get('statement', ''),
                'method' => (string) $get('method', 'Bank transfer'),
                'reference' => (string) $get('reference', ''),
            ];
        }

        /* Oldest payment first so receipt numbers stay chronological. */
        usort($rows, static function (array $a, array $b): int {
            $left = $a['paid_at'] ? $a['paid_at']->toDateTime()->getTimestamp() : 0;
            $right = $b['paid_at'] ? $b['paid_at']->toDateTime()->getTimestamp() : 0;
            return $left <=> $right ?: $a['sequence'] <=> $b['sequence'];
        });

        $running = 0.0;
        $grandTotal = round((float) ($project->total_payment ?? 0) * (1 + (float) ($project->tax_percent ?? 0) / 100), 2);
        foreach ($rows as $position => $row) {
            $running += $row['amount'];
            $rows[$position]['receipt_number'] = self::receiptNumber($project, $position + 1);
            $rows[$position]['paid_to_date'] = round($running, 2);
            $rows[$position]['balance_after'] = round(max(0, $grandTotal - $running), 2);
            $rows[$position]['installment'] = $position + 1;
        }

        return $rows;
    }

    /** Finds one payment by its stable id. */
    public static function payment(object $project, string $paymentId): ?array
    {
        foreach (self::payments($project) as $payment) {
            if ($payment['id'] === $paymentId) {
                return $payment;
            }
        }

        return null;
    }

    public static function invoiceNumber(object $project): string
    {
        $created = isset($project->created_at) && $project->created_at
            ? $project->created_at->toDateTime()->format('Ym')
            : date('Ym');

        return sprintf('UTS-%s-%s', $created, strtoupper(substr((string) $project->_id, -6)));
    }

    public static function receiptNumber(object $project, int $installment): string
    {
        return self::invoiceNumber($project) . '-R' . str_pad((string) $installment, 2, '0', STR_PAD_LEFT);
    }

    /** Bill-to block, taken from the client access users assigned to the project. */
    public static function billTo(array $clientUsers, object $project): array
    {
        $client = $clientUsers[0] ?? null;
        if (!$client) {
            return [
                'name' => (string) ($project->name ?? 'Client'),
                'email' => '',
                'phone' => '',
                'address' => '',
                'is_placeholder' => true,
            ];
        }

        return [
            'name' => (string) ($client->name ?? 'Client'),
            'email' => (string) ($client->email ?? ''),
            'phone' => (string) ($client->mobile_phone ?? ''),
            'address' => (string) ($client->address ?? ''),
            'is_placeholder' => false,
        ];
    }

    /** Words are expected on Indian invoices — "₹ 12,500.00" becomes readable text. */
    public static function amountInWords(float $amount): string
    {
        $units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $convert = static function (int $number) use (&$convert, $units, $tens): string {
            if ($number < 20) {
                return $units[$number];
            }
            if ($number < 100) {
                return trim($tens[intdiv($number, 10)] . ' ' . $units[$number % 10]);
            }
            if ($number < 1000) {
                return trim($units[intdiv($number, 100)] . ' Hundred ' . $convert($number % 100));
            }
            if ($number < 100000) {
                return trim($convert(intdiv($number, 1000)) . ' Thousand ' . $convert($number % 1000));
            }
            if ($number < 10000000) {
                return trim($convert(intdiv($number, 100000)) . ' Lakh ' . $convert($number % 100000));
            }
            return trim($convert(intdiv($number, 10000000)) . ' Crore ' . $convert($number % 10000000));
        };

        $whole = (int) floor($amount);
        $paise = (int) round(($amount - $whole) * 100);

        $words = $whole > 0 ? $convert($whole) . ' Rupees' : 'Zero Rupees';
        if ($paise > 0) {
            $words .= ' and ' . $convert($paise) . ' Paise';
        }

        return $words . ' only';
    }

    private static function formatDate(object $value, string $format): string
    {
        return $value->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()))->format($format);
    }
}
