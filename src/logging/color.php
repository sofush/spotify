<?php

declare(strict_types=1);

enum ColorCode: int
{
    case Black = 30;
    case Red = 31;
    case Green = 32;
    case Yellow = 33;
    case Blue = 34;
    case Magenta = 35;
    case Cyan = 36;
    case White = 37;
    case Reset = 39;
    case BrightBlack = 90;
    case BrightRed = 91;
    case BrightGreen = 92;
    case BrightYellow = 93;
    case BrightBlue = 94;
    case BrightMagenta = 95;
    case BrightCyan = 96;
    case BrightWhite = 97;
}

final class Color
{
    private string|null $seq = null;
    private ColorCode $code = ColorCode::Reset;
    private int $boldness = 0;

    private function __construct(ColorCode $code)
    {
        $this->code = $code;
    }

    public static function new(ColorCode $code = ColorCode::Reset): Color
    {
        return new Color($code);
    }

    public function code(ColorCode $code): Color
    {
        $this->seq = null;
        $this->code = $code;
        return $this;
    }

    public function bold(): Color
    {
        $this->seq = null;
        $this->boldness = 1;
        return $this;
    }

    public function dim(): Color
    {
        $this->seq = null;
        $this->boldness = 2;
        return $this;
    }

    public function seq(): string
    {
        if ($this->seq == null) {
            $this->seq = sprintf(
                "\x1b[%d;%dm",
                $this->boldness,
                $this->code->value,
            );
        }

        return $this->seq;
    }
}

final class Colorizer
{
    private string $output = "";
    private Color $reset;
    private int $len = 0;

    private function __construct()
    {
        $this->reset = Color::new();
    }

    public static function builder(): Colorizer
    {
        return new Colorizer();
    }

    public function push(mixed $obj, Color|null $color = null, string $end = ''): Colorizer
    {
        $str = strval($obj);
        $this->len += mb_strlen($str) + mb_strlen($end);
        $color ??= Color::new();
        $this->output .= sprintf(
            '%s%s%s%s',
            $color->seq(),
            $str,
            $end ?? '',
            $this->reset->seq(),
        );
        return $this;
    }

    public function len(): int
    {
        return $this->len;
    }

    public function build(): string
    {
        return $this->output;
    }
}
