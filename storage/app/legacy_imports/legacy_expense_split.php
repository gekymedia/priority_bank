<?php
/**
 * Split monthly expenditure text into 4 system buckets (keyword + line context).
 * Remainder (expected monthly total − allocated) goes to personal_ceo.
 */

/**
 * @return array{personal_ceo: float, gekymedia: float, priority_admissions: float, priority_agriculture: float, parsed_sum: float}
 */
function legacySplitExpenseText(string $text, float $expectedExpenseTotal): array
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $lines = explode("\n", $text);

    $buckets = [
        'personal_ceo' => 0.0,
        'gekymedia' => 0.0,
        'priority_admissions' => 0.0,
        'priority_agriculture' => 0.0,
    ];

    $inIncomeBlock = false;
    $prev = '';

    foreach ($lines as $line) {
        $raw = $line;
        $line = trim($line);

        // Toggle income sections (skip amounts that are revenue, not spend)
        if (preg_match('/^income\s*$/i', $line) || preg_match('/^incomes\s*$/i', $line)) {
            $inIncomeBlock = true;
            $prev = $raw;
            continue;
        }
        // New day / month section often resets context
        if (preg_match('/^\d{1,2}(st|nd|rd|th)?\b/i', $line)
            || preg_match('/^(january|february|march|april|may|june|july|august|september|october|november|december)\b/i', $line)
            || preg_match('/^(monday|tuesday|wednesday|thursday|friday|saturday|sunday)[,\s]/i', $line)) {
            $inIncomeBlock = false;
        }
        if (preg_match('/^expenditure\s*$/i', $line) || preg_match('/^expenses?\s*$/i', $line)) {
            $inIncomeBlock = false;
        }

        $context = trim($prev."\n".$line);
        $prev = $raw;

        if ($line === '' || str_starts_with($line, '//')) {
            continue;
        }

        // Skip pure income lines (common patterns in your diary)
        if ($inIncomeBlock || legacyLineLooksLikeIncome($line)) {
            continue;
        }

        if (! preg_match_all('/GHC\s*([\d,]+(?:\.\d+)?)/i', $line, $matches)) {
            continue;
        }

        $bucket = legacyClassifyBucket($context, $line);
        foreach ($matches[1] as $num) {
            $buckets[$bucket] += (float) str_replace(',', '', $num);
        }
    }

    $parsed = array_sum($buckets);
    $remainder = round($expectedExpenseTotal - $parsed, 2);
    if (abs($remainder) > 0.009) {
        $buckets['personal_ceo'] = round($buckets['personal_ceo'] + $remainder, 2);
    }

    $buckets['parsed_sum'] = round(array_sum([
        $buckets['personal_ceo'],
        $buckets['gekymedia'],
        $buckets['priority_admissions'],
        $buckets['priority_agriculture'],
    ]), 2);

    return $buckets;
}

/**
 * Same classification rules as legacySplitExpenseText, but returns one row per amount (for audits / curated JSON).
 *
 * @return list<array{line: string, amount: float, bucket: string}>
 */
function legacyExtractExpenseLineItems(string $text): array
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $lines = explode("\n", $text);

    $items = [];
    $inIncomeBlock = false;
    $prev = '';

    foreach ($lines as $line) {
        $raw = $line;
        $line = trim($line);

        if (preg_match('/^income\s*$/i', $line) || preg_match('/^incomes\s*$/i', $line)) {
            $inIncomeBlock = true;
            $prev = $raw;
            continue;
        }
        if (preg_match('/^\d{1,2}(st|nd|rd|th)?\b/i', $line)
            || preg_match('/^(january|february|march|april|may|june|july|august|september|october|november|december)\b/i', $line)
            || preg_match('/^(monday|tuesday|wednesday|thursday|friday|saturday|sunday)[,\s]/i', $line)) {
            $inIncomeBlock = false;
        }
        if (preg_match('/^expenditure\s*$/i', $line) || preg_match('/^expenses?\s*$/i', $line)) {
            $inIncomeBlock = false;
        }

        $context = trim($prev."\n".$line);
        $prev = $raw;

        if ($line === '' || str_starts_with($line, '//')) {
            continue;
        }

        if ($inIncomeBlock || legacyLineLooksLikeIncome($line)) {
            continue;
        }

        if (! preg_match_all('/GHC\s*([\d,]+(?:\.\d+)?)/i', $line, $matches)) {
            continue;
        }

        $bucket = legacyClassifyBucket($context, $line);
        foreach ($matches[1] as $num) {
            $items[] = [
                'line' => $line,
                'amount' => (float) str_replace(',', '', $num),
                'bucket' => $bucket,
            ];
        }
    }

    return $items;
}

function legacyLineLooksLikeIncome(string $line): bool
{
    // Lines that are clearly revenue / inflows (not cash-out)
    if (preg_match('/^\*\*\*.*(Income|Salary|Bonus|Appreciation)/i', $line)) {
        return true;
    }
    if (preg_match('/Key Income Sources/i', $line)) {
        return true;
    }
    if (preg_match('/^(Income|Incomes)\s*:/i', $line)) {
        return true;
    }
    if (preg_match('/Loan Paid:|Loan\/\/.*Paid|repaid|Received Back|Profit:|#Sash|#Church Event|#Graduation|#Ofa|#Bra Frank|Incomes\s*$/i', $line)) {
        return true;
    }
    if (preg_match('/^\s*-\s*(6\s+Regular|Undergraduate|Masters|Access Fees|PDF|Forms?\s*\(|Profit from|Salary Payment|MoMo interest|Gift from|Sash Income|Interest from|Addition from|Patrick Pay website|Delivery Business|Document Editing)/i', $line)) {
        return true;
    }
    if (preg_match('/No Income/i', $line)) {
        return false;
    }
    if (preg_match('/\bForms?\s*\(\d+\)|Undergraduate\s*Forms|Masters\s*Forms|Access Fees Payment|Access Course fees/i', $line) && ! preg_match('/Expenditure|Fuel|Food|Lunch/i', $line)) {
        // Likely income line if it mentions form sales without expense words
        if (preg_match('/:\s*Ghc?\s*[\d,]+/i', $line) && ! preg_match('/Expenditure|Mechanic|Fuel for|Food for|Domain|Market/i', $line)) {
            return true;
        }
    }

    return false;
}

function legacyClassifyBucket(string $context, string $line): string
{
    $c = strtolower($context."\n".$line);

    // 1) Geky Media — production / brand / gear (tighten so generic "data" stays CEO)
    if (preg_match('/geky\s*media|gekymedia|geky\s*domain|reelshort|tiktok ads|english assembly|flyer design for english|memory card|energizer|graduation booking|church event coverage|sash making|ofe kofi wedding|bra frank dad funeral|camera batteries|strap|delivery fee.*geky/i', $c)) {
        return 'gekymedia';
    }
    if (preg_match('/\bchatgpt\b|chat gpt subscription|api for chat gpt/i', $c)) {
        return 'gekymedia';
    }

    // 2) Priority Admissions — CUG / forms / access stack
    if (preg_match('/\bcug\b|buying of cug domain|priority admissions|hubtel|ussd|vps server|arkesel|bulk sms|waec results checking|access fee|undergraduate|masters|discount form|pdf application|cems faculty|toner|a4 sheet|a4 box|printing work|tip for pounds.*admissions/i', $c)) {
        return 'priority_admissions';
    }
    if (preg_match('/\bflyers?\b|biz\s*\/\/\s*flyer|business\s*\/\/\s*flyer|school flyers/i', $c) && ! preg_match('/flyer design for english/i', $c)) {
        return 'priority_admissions';
    }

    // 3) Priority Agriculture — farms, poultry, chicks, land used for agri (not Nov real-estate-only — still agr)
    if (preg_match('/agriculture|agric\/\/|agric\/|priority farms|prioritynova|broiler|layer starter|layer grower|poultry|chick|fertilizer|weedicide|farm\b|farms\b|day old|investment\/\/.*chick|investment\/\/.*poultry|investment\/\/.*pourty|investment\/\/.*layer|sammy farming|farming project|harvest|debeaking|penicillin for chicks|feed for.*chick|new chicks|sand.*trip|cement.*bag|water tank|block mason|foundation digging|land purchase|weeding.*house|delivery bike business|delivery motor|delivery of.*bike/i', $c)) {
        return 'priority_agriculture';
    }

    return 'personal_ceo';
}
