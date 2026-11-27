<?php

namespace App\Services;

use App\Models\ProductCurrencyRate;

class AccountListingFormatter
{
    /**
     * @var array<string, string>
     */
    private const STYLIZED_UPPERCASE_LETTERS = [
        'A' => '𝘼',
        'B' => '𝘽',
        'C' => '𝘾',
        'D' => '𝘿',
        'E' => '𝙀',
        'F' => '𝙁',
        'G' => '𝙂',
        'H' => '𝙃',
        'I' => '𝙄',
        'J' => '𝙅',
        'K' => '𝙆',
        'L' => '𝙇',
        'M' => '𝙈',
        'N' => '𝙉',
        'O' => '𝙊',
        'P' => '𝙋',
        'Q' => '𝙌',
        'R' => '𝙍',
        'S' => '𝙎',
        'T' => '𝙏',
        'U' => '𝙐',
        'V' => '𝙑',
        'W' => '𝙒',
        'X' => '𝙓',
        'Y' => '𝙔',
        'Z' => '𝙕',
    ];

    /**
     * @param  array<string, mixed>  $data
     * @param  array{idr_per_myr: float|int|string, idr_per_usd: float|int|string}  $rates
     */
    public function plainText(array $data, array $rates, ?string $productUrl = null): string
    {
        return html_entity_decode(
            strip_tags($this->renderHtml($data, $rates, $productUrl)),
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
        return $this->renderHtml($data, $rates, $productUrl);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array{idr_per_myr: float|int|string, idr_per_usd: float|int|string}  $rates
     */
    private function renderHtml(array $data, array $rates, ?string $productUrl): string
    {
        if (($data['account_template'] ?? 'blox_fruit') === 'generic') {
            return $this->genericTelegramHtml($data, $rates, $productUrl);
        }

        $items = $data['items'] ?? [];
        $headlineItems = array_map(fn (array $item): string => $this->escape($item['name']), $items);
        $headline = $headlineItems === [] ? $this->escape($data['username']) : implode(' + ', $headlineItems);
        $race = (string) ($data['race'] ?? 'V3 HUMAN');
        foreach ($items as $item) {
            if (mb_strtolower(trim($item['category'])) === 'race') {
                if ($race === 'V3 HUMAN') {
                    $race = $item['name'];
                }

                break;
            }
        }

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
            '💵<b>𝙋𝙍𝙄𝘾𝙀 :</b> '.$priceInThousands.'K | RM '.$myrPrice.' | $'.$usdPrice,
            '',
            '⚙️<b>𝙎𝙏𝘼𝙏𝙐𝙎 :</b> 🛡'.$this->escape($data['account_status'] ?? '100% SAFE ANTI HB, NO VERIFIKASI'),
            '❌<b>𝙉𝙊𝙍𝙀𝙁𝙁</b>',
            '👀<b>𝙉𝘼𝙈𝙀 :</b> '.$this->escape($data['username']),
            '➡️<b>𝙇𝙀𝙑𝙀𝙇 :</b> '.$this->escape($data['level']),
        ];

        foreach ($this->itemsByCategory($items) as $category => $categoryItems) {
            if (mb_strtolower(trim($category)) === 'race') {
                continue;
            }

            $categoryLabel = $this->stylizeCategoryName($category);
            $lines[] = '➡️<b>'.$this->escape($categoryLabel).' :</b> '.implode(', ', $categoryItems);
        }

        $lines = array_merge($lines, [
            '➡️<b>𝘽𝙀𝙇𝙇𝙔 :</b> '.$this->escape($data['belly'] ?? '-'),
            '➡️<b>𝙁𝙍𝘼𝙂𝙈𝙀𝙉𝙏 :</b> '.$this->escape($data['fragment'] ?? '-'),
            '➡️<b>𝙃𝘼𝙆𝙄 :</b> '.$this->escape($data['haki'] ?? 'SKY JUMP, BODY, FLASH STEP'),
            '➡️<b>𝙍𝘼𝘾𝙀 :</b> '.$this->escape($race),
            '➡️<b>𝘼𝙒𝙀𝙆𝙀𝙉 :</b> '.$this->escape($data['awaken'] ?? '-'),
            '➡️<b>𝙋𝙀𝙍𝙈𝘼𝙉𝙀𝙉𝙏 :</b> '.$this->escape($data['permanent'] ?? '-'),
            '➡️<b>𝙂𝘼𝙈𝙀𝙋𝘼𝙎𝙎 :</b> '.$this->escape($data['gamepass'] ?? '-'),
            '➡️<b>𝙎𝙏𝘼𝙏 𝙍𝙀𝙁𝙐𝙉𝘿 :</b> '.(int) ($data['stat_refund'] ?? 2).' (KODE)',
        ]);

        if (($data['post_chat'] ?? '') !== '') {
            $lines[] = '🌐<b>CHAT :</b> '.$this->escape($data['post_chat']);
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

    public function stylizeCategoryName(string $category): string
    {
        return strtr(mb_strtoupper($category), self::STYLIZED_UPPERCASE_LETTERS);
    }

    public function stylizeAccountCategoryLabels(string $description): string
    {
        $lines = explode("\n", $description);

        foreach ($lines as &$line) {
            if (preg_match('/^➡️([A-Z][A-Z0-9 &_-]*)( : )/u', $line, $matches) !== 1) {
                continue;
            }

            $line = '➡️'.$this->stylizeCategoryName($matches[1]).$matches[2].substr($line, strlen($matches[0]));
        }

        unset($line);

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array{idr_per_myr: float|int|string, idr_per_usd: float|int|string}  $rates
     */
    private function genericTelegramHtml(array $data, array $rates, ?string $productUrl): string
    {
        $gameName = (string) ($data['game_category_name'] ?? 'Game');
        $websiteTitle = (string) ($data['website_title'] ?? $gameName.' Account - '.($data['username'] ?? ''));
        $price = (float) ($data['price_idr'] ?? 0);
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
            '<b>'.$this->escape($gameName).' ACCOUNT</b>',
            '',
            '➡️<b>'.$this->escape($websiteTitle).'</b>',
            '👤<b>ACCOUNT : '.$this->escape((string) ($data['username'] ?? '')).'</b>',
            '📝<b>DETAIL : '.$this->escape((string) ($data['details'] ?? '-')).'</b>',
            '',
            '💵<b>𝙋𝙍𝙄𝘾𝙀 : '.$priceInThousands.'K | RM '.$myrPrice.' | $'.$usdPrice.'</b>',
        ];

        if (($data['post_chat'] ?? '') !== '') {
            $lines[] = '🌐<b>CHAT : '.$this->escape((string) $data['post_chat']).'</b>';
        }

        if (($data['post_instagram'] ?? '') !== '') {
            $lines[] = '📷<b>'.$this->escape((string) $data['post_instagram']).'</b>';
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
