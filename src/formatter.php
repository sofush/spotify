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

    private Color $debugLevelColor;
    private Color $infoLevelColor;
    private Color $noticeLevelColor;
    private Color $warningLevelColor;
    private Color $errorLevelColor;

    public function __construct()
    {
        $this->datetimeColor = Color::new(ColorCode::Green)->dim();
        $this->channelColor = Color::new(ColorCode::BrightYellow)->dim();

        $this->debugLevelColor = Color::new(ColorCode::BrightBlue)->bold();
        $this->infoLevelColor = Color::new(ColorCode::Blue)->bold();
        $this->noticeLevelColor = Color::new(ColorCode::BrightGreen)->bold();
        $this->warningLevelColor = Color::new(ColorCode::BrightYellow)->bold();
        $this->errorLevelColor = Color::new(ColorCode::Red)->bold();
    }

    public function format(LogRecord $record)
    {
        $datetime = $record->datetime->format('Y-m-d H:i:s.v');
        $level = $record->level->getName();
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

        $output = Colorizer::builder()
            ->push($datetime, $this->datetimeColor, ' ')
            ->push("[$level]", $levelColor, ' ')
            ->push($record->channel, $this->infoLevelColor)
            ->push(':  ', Color::new());

        return $output->finalize();

        // $message = sprintf(
        //     '%s %s%s%s',
        //     $prefix,
        //         $this::ORANGE,
        //     $record->message,
        //     PHP_EOL
        // );

        // $spaces = str_repeat(' ', mb_strlen($prefix));

        // if (count($record->context) > 0) {
        //     $message = sprintf(
        //         '%s%s%scontext = [%s]%s',
        //         $message,
        //         PHP_EOL,
        //         $spaces,
        //         implode(', ', $record->context),
        //     );
        // }

        // if (count($record->extra) > 0) {
        //     $message = sprintf(
        //         '%s%s%scontext = [%s]%s',
        //         $message,
        //         PHP_EOL,
        //         $spaces,
        //         implode(', ', $record->extra),
        //         PHP_EOL,
        //     );
        // }

        // return $message;
    }

    public function formatBatch(array $records)
    {
        return array_map($this::format, $records);
    }
}
