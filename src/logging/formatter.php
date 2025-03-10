<?php

declare(strict_types=1);

require_once __DIR__ . '/color.php';

use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\LogRecord;

final class ConsoleFormatter implements FormatterInterface
{
    private Color $datetimeColor;
    private Color $channelColor;
    private Color $contextColor;
    private Color $contextKeyColor;
    private Color $contextValueColor;

    private Color $debugLevelColor;
    private Color $infoLevelColor;
    private Color $noticeLevelColor;
    private Color $warningLevelColor;
    private Color $errorLevelColor;

    public function __construct()
    {
        $this->datetimeColor = Color::new(ColorCode::Green)->dim();
        $this->channelColor = Color::new(ColorCode::BrightYellow)->dim();
        $this->contextColor = Color::new(ColorCode::BrightBlack);
        $this->contextKeyColor = Color::new(ColorCode::BrightMagenta);
        $this->contextValueColor = Color::new(ColorCode::BrightBlue);

        $this->debugLevelColor = Color::new(ColorCode::BrightBlack);
        $this->infoLevelColor = Color::new(ColorCode::BrightBlue);
        $this->noticeLevelColor = Color::new(ColorCode::BrightGreen)->bold();
        $this->warningLevelColor = Color::new(ColorCode::BrightYellow)->bold();
        $this->errorLevelColor = Color::new(ColorCode::Red)->bold();
    }

    public function format(LogRecord $record)
    {
        $datetime = $record->datetime->format('Y-m-d H:i:s.v');
        $level = mb_strtolower($record->level->name);
        $level = "($level)";
        $level = mb_str_pad($level, 11, ' ', STR_PAD_LEFT);
        $levelColor = match ($record->level) {
            Level::Debug => $this->debugLevelColor,
            Level::Info => $this->infoLevelColor,
            Level::Notice => $this->noticeLevelColor,
            Level::Error => $this->errorLevelColor,
            Level::Critical => $this->errorLevelColor,
            Level::Alert => $this->errorLevelColor,
            Level::Emergency => $this->errorLevelColor,
            default => Color::new(ColorCode::Reset),
        };

        $prefix = Colorizer::builder()
            ->push($datetime, $this->datetimeColor, ' ')
            ->push($level, $levelColor, ' ')
            ->push($record->channel, $this->channelColor);

        $final = Colorizer::builder()
            ->push($prefix->build())
            ->push(':  ')
            ->push($record->message, null, PHP_EOL);

        if (count($record->context) > 0) {
            $plen = $prefix->len();
            $padded = mb_str_pad("context", $plen, ' ', STR_PAD_LEFT);
            $final->push($padded, $this->contextColor, ':  ');

            foreach ($record->context as $key => $value) {
                $keystr = $this->stringify($key);
                $valuestr = $this->stringify($value);

                $final
                    ->push($keystr, $this->contextKeyColor)
                    ->push(' => ')
                    ->push($valuestr, $this->contextValueColor);

                if ($key !== array_key_last($record->context)) {
                    $final->push(', ');
                }
            }

            $final
                ->push(' ' . PHP_EOL, $this->contextColor);
        }

        return $final->build();
    }

    public function formatBatch(array $records)
    {
        return array_map($this::format, $records);
    }

    private function stringify(mixed $obj)
    {
        try {
            if ($obj === null) {
                return '<null>';
            }

            if ($obj === true) {
                return '<true>';
            }

            if ($obj === false) {
                return '<false>';
            }

            if (is_array($obj)) {
                return '[' . implode(', ', $obj) . ']';
            }

            return strval($obj);
        } catch (Throwable $e) {
            return '<cannot convert to string>';
        }
    }
}
