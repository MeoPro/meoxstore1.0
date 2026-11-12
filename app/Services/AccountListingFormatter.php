<?php

namespace App\Services;

use App\Models\ProductCurrencyRate;

class AccountListingFormatter
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array{idr_per_myr: float|int|string, idr_per_usd: float|int|string}  $rates
     */
    public function plainText(array $data, array $rates, ?string $productUrl = null): string
    {
        return html_entity_decode(
            strip_tags($this->telegramHtml($data, $rates, $productUrl)),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array{idr_per_myr: float|int|string, idr_per_usd: float|int|string}  $rates
     */
    public function telegramHtml(array $data, array $rates, ?string $productUrl = null): string
    {
        $items = $data['items'] ?? [];
        $headlineItems = array_map(fn (array $item): string => $this->escape($item['name']), $items);
        $headline = $headlineItems === [] ? $this->escape($data['username']) : implode(' + ', $headlineItems);
        $price = (float) $data['price_idr'];
        $priceInThousands = number_format($price / 1000, 2, '.', ',');
        $priceInThousands = rtrim(rtrim($priceInThousands, '0'), '.');
        $myrPrice = number_format(
            ProductCurrencyRate::roundToNearestFiveCents($price / (float) $rates['idr_per_myr']),
            2,
        );
        $usdPrice = number_format(
            ProductCurrencyRate::roundToNearestFiveCents(($price + 10_000) / (float) $rates['idr_per_usd']),
            2,
        );
        $lines = [
            '<b>#'.$this->escape($data['status_label'] ?? 'READY✅').'</b>',
            '',
            '&gt; <b>'.$headline.'</b>',
            '',
            '💵<b>𝙋𝙍𝙄𝘾𝙀 : '.$priceInThousands.'K | RM '.$myrPrice.' | $'.$usdPrice.'</b>',
            '',
            '⚙️<b>𝙎𝙏𝘼𝙏𝙐𝙎 : 🛡'.$this->escape($data['account_status'] ?? '100% SAFE ANTI HB, NO VERIFIKASI').'</b>',
            '❌<b>𝙉𝙊𝙍𝙀𝙁𝙁</b>',
            '👀<b>𝙉𝘼𝙈𝙀 : '.$this->escape($data['username']).'</b>',
            '➡️<b>𝙇𝙀𝙑𝙀𝙇 : '.$this->escape($data['level']).'</b>',
        ];

        foreach ($this->itemsByCategory($items) as $category => $categoryItems) {
            $lines[] = '➡️<b>'.mb_strtoupper($this->escape($category)).' : '.implode(', ', $categoryItems).'</b>';
        }

        $lines = array_merge($lines, [
            '➡️<b>𝘽𝙀𝙇𝙇𝙔 : '.$this->escape($data['belly'] ?? '-').'</b>',
            '➡️<b>𝙁𝙍𝘼𝙂𝙈𝙀𝙉𝙏 : '.$this->escape($data['fragment'] ?? '-').'</b>',
            '➡️<b>𝙃𝘼𝙆𝙄 : '.$this->escape($data['haki'] ?? 'SKY JUMP, BODY, FLASH STEP').'</b>',
            '➡️<b>𝙍𝘼𝘾𝙀 : '.$this->escape($data['race'] ?? 'V3 HUMAN').'</b>',
            '➡️<b>𝘼𝙒𝙀𝙆𝙀𝙉 : '.$this->escape($data['awaken'] ?? '-').'</b>',
            '➡️<b>𝙋𝙀𝙍𝙈𝘼𝙉𝙀𝙉𝙏 : '.$this->escape($data['permanent'] ?? '-').'</b>',
            '➡️<b>𝙂𝘼𝙈𝙀𝙋𝘼𝙎𝙎 : '.$this->escape($data['gamepass'] ?? '-').'</b>',
            '➡️<b>𝙎𝙏𝘼𝙏 𝙍𝙀𝙁𝙐𝙉𝘿 : '.(int) ($data['stat_refund'] ?? 2).' (KODE)</b>',
        ]);

        if (($data['post_chat'] ?? '') !== '') {
            $lines[] = '🌐<b>CHAT : '.$this->escape($data['post_chat']).'</b>';
        }

        if (($data['post_instagram'] ?? '') !== '') {
            $lines[] = '📷<b>'.$this->escape($data['post_instagram']).'</b>';
        }

        if ($productUrl !== null) {
            $lines[] = '';
            $lines[] = '🛒 <a href="'.$this->escape($productUrl).'">Lihat di website</a>';
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<int, array{category: string, name: string}>  $items
     * @return array<string, array<int, string>>
     */
    private function itemsByCategory(array $items): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $grouped[$item['category']][] = $this->escape($item['name']);
        }

        return $grouped;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
